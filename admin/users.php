<?php
/**
 * User Management Page
 * SDO ALPAS - Superadmin only
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Superadmin only - check before header.php outputs anything
require_once __DIR__ . '/../includes/auth.php';
$authCheck = auth();
$authCheck->requireLogin();

if (!$authCheck->isSuperAdmin()) {
    header('Location: /');
    exit;
}

require_once __DIR__ . '/../includes/header.php';

require_once __DIR__ . '/../models/AdminUser.php';

$userModel = new AdminUser();
$message = '';
$error = '';

// Get offices from master database table for dropdown (legacy/flat list)
$offices = getSDOOfficesFromDB(true);
// Top-level offices (OSDS, SGOD, CID) and units-by-office for cascading dropdowns
$topOffices = getSDOOfficesForOfficeDropdown();
$unitsByOffice = getUnitsByOfficeForJs();

// Handle actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    // Superadmin hard-gate for ALL mutations (defense-in-depth)
    if (!$auth->isSuperAdmin()) {
        header('HTTP/1.1 403 Forbidden');
        include __DIR__ . '/403.php';
        exit;
    }

    if ($action === 'create') {
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';
        $roleId = intval($_POST['role_id'] ?? 0);
        $employeeNo = trim($_POST['employee_no'] ?? '');
        $employeePosition = trim($_POST['employee_position'] ?? '');
        $officeId = intval($_POST['office_id'] ?? 0) ?: null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        if ($fullName === '' || $email === '' || $password === '' || $roleId <= 0) {
            $error = 'Please fill in full name, email, password, and role.';
        } elseif (strlen($password) < 8) {
            $error = 'Password must be at least 8 characters.';
        } elseif ($password !== $confirmPassword) {
            $error = 'Password and Confirm Password do not match.';
        } else {
            try {
                $newUserId = $userModel->create([
                    'email' => $email,
                    'password' => $password,
                    'full_name' => $fullName,
                    'employee_no' => $employeeNo ?: null,
                    'employee_position' => $employeePosition ?: null,
                    'office_id' => $officeId,
                    'role_id' => $roleId,
                    'status' => $isActive ? 'active' : 'inactive',
                    'is_active' => $isActive,
                ], $auth->getUserId());

                $auth->logActivity('create_user', 'user', $newUserId, 'Created user: ' . $email);
                $message = 'User created successfully!';
            } catch (Exception $e) {
                $error = 'Failed to create user. Email may already exist.';
            }
        }
    }
    
    if ($action === 'approve' && !empty($_POST['id'])) {
        $userModel->approveRegistration($_POST['id']);
        $auth->logActivity('approve_user', 'user', $_POST['id'], 'Approved user registration');
        $message = 'User approved successfully!';
    }

    if ($action === 'update_user' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $fullName = trim($_POST['full_name'] ?? '');
        $email = trim($_POST['email'] ?? '');
        $roleId = intval($_POST['role_id'] ?? 0);
        $employeeNo = trim($_POST['employee_no'] ?? '');
        $employeePosition = trim($_POST['employee_position'] ?? '');
        $officeId = intval($_POST['office_id'] ?? 0) ?: null;
        $isActive = isset($_POST['is_active']) ? 1 : 0;
        $password = $_POST['password'] ?? '';
        $confirmPassword = $_POST['confirm_password'] ?? '';

        if ($fullName === '' || $email === '' || $roleId <= 0) {
            $error = 'Please fill in full name, email, and role.';
        } elseif ($password !== '' && strlen($password) < 8) {
            $error = 'New password must be at least 8 characters.';
        } elseif ($password !== '' && $password !== $confirmPassword) {
            $error = 'New password and Confirm Password do not match.';
        } else {
            $updateData = [
                'full_name' => $fullName,
                'email' => $email,
                'role_id' => $roleId,
                'employee_no' => $employeeNo ?: null,
                'employee_position' => $employeePosition ?: null,
                'office_id' => $officeId,
                'status' => $isActive ? 'active' : 'inactive',
                'is_active' => $isActive,
            ];

            if ($password !== '') {
                $updateData['password'] = $password;
            }

            try {
                $userModel->update($id, $updateData);
                $auth->logActivity('update_user', 'user', $id, 'Updated user: ' . $email);
                $message = 'User updated successfully!';
            } catch (Exception $e) {
                $error = 'Failed to update user. Email may already exist.';
            }
        }
    }
    
    if ($action === 'deactivate' && !empty($_POST['id'])) {
        $userModel->deactivate($_POST['id']);
        $auth->logActivity('deactivate_user', 'user', $_POST['id'], 'Deactivated user');
        $message = 'User deactivated.';
    }

    if ($action === 'activate' && !empty($_POST['id'])) {
        $userModel->activate($_POST['id']);
        $auth->logActivity('activate_user', 'user', $_POST['id'], 'Activated user');
        $message = 'User activated.';
    }
    
    if ($action === 'delete' && !empty($_POST['id'])) {
        $user = $userModel->getById($_POST['id']);
        if ($user && $user['id'] != $auth->getUserId()) {
            $userModel->delete($_POST['id']);
            $auth->logActivity('delete_user', 'user', $_POST['id'], 'Deleted user: ' . $user['email']);
            $message = 'User deleted.';
        } else {
            $error = 'You cannot delete your own account.';
        }
    }
    
    if ($action === 'update_role' && !empty($_POST['id']) && !empty($_POST['role_id'])) {
        $userModel->update($_POST['id'], ['role_id' => $_POST['role_id']]);
        $auth->logActivity('update_role', 'user', $_POST['id'], 'Updated user role');
        $message = 'User role updated.';
    }
}

// Get filters
$filters = [];
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['role_id'])) {
    $filters['role_id'] = $_GET['role_id'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
// If office_id is provided, use that (more specific)
// Otherwise, if office_code is provided, use that (broader filter)
if (!empty($_GET['office_id'])) {
    $filters['office_id'] = intval($_GET['office_id']);
} elseif (!empty($_GET['office_code'])) {
    $filters['office_code'] = $_GET['office_code'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$users = $userModel->getAll($filters, $perPage, $offset);
$totalUsers = $userModel->getCount($filters);
$totalPages = ceil($totalUsers / $perPage);

$roles = $userModel->getRoles();
$pendingCount = $userModel->getPendingRegistrationsCount();
?>

<?php if ($message): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<?php if ($pendingCount > 0): ?>
<div class="alert" style="background: var(--warning-bg); color: #b45309; border: 1px solid #fcd34d;">
    <i class="fas fa-user-clock"></i> 
    <strong><?php echo $pendingCount; ?> pending registration<?php echo $pendingCount > 1 ? 's' : ''; ?></strong> awaiting approval.
    <a href="<?php echo navUrl('/users.php?status=pending'); ?>" style="margin-left: 10px;">View Pending</a>
</div>
<?php endif; ?>

<div class="page-header">
    <div class="result-count"><?php echo $totalUsers; ?> User<?php echo $totalUsers !== 1 ? 's' : ''; ?></div>
    <?php if ($auth->isSuperAdmin()): ?>
    <button type="button" class="btn btn-primary btn-sm" onclick="openAddUserModal()">
        <i class="fas fa-user-plus"></i> Add User
    </button>
    <?php endif; ?>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="token" value="<?php echo $currentToken; ?>">
        
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Name, email..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Role</label>
            <select name="role_id" class="filter-select">
                <option value="">All Roles</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?php echo $role['id']; ?>" <?php echo ($_GET['role_id'] ?? '') == $role['id'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="active" <?php echo ($_GET['status'] ?? '') === 'active' ? 'selected' : ''; ?>>Active</option>
                <option value="inactive" <?php echo ($_GET['status'] ?? '') === 'inactive' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div> 
        
        <div class="filter-group">
            <label>Office</label>
            <select name="office_code" id="filter_office" class="filter-select">
                <option value="">All Offices</option>
                <?php 
                // Determine selected office code - check GET param or derive from office_id
                $selectedOfficeCode = $_GET['office_code'] ?? '';
                if (empty($selectedOfficeCode) && !empty($_GET['office_id'])) {
                    $selectedOfficeCode = getOfficeCodeFromUnitId(intval($_GET['office_id']));
                }
                foreach ($topOffices as $o): 
                ?>
                <option value="<?php echo htmlspecialchars($o['code']); ?>" <?php echo $selectedOfficeCode === $o['code'] ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($o['name']); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Unit/Section</label>
            <select name="office_id" id="filter_unit_id" class="filter-select" <?php echo empty($_GET['office_code']) && empty($_GET['office_id']) ? 'disabled' : ''; ?> data-selected-unit="<?php echo !empty($_GET['office_id']) ? htmlspecialchars($_GET['office_id']) : ''; ?>">
                <option value="">All Units</option>
                <?php 
                // Determine which office code to use for populating units
                $selectedOfficeCode = $_GET['office_code'] ?? '';
                // If office_id is set but office_code is not, look up the office code
                if (empty($selectedOfficeCode) && !empty($_GET['office_id'])) {
                    $selectedOfficeCode = getOfficeCodeFromUnitId(intval($_GET['office_id']));
                }
                
                // If office_code is available, populate units
                if (!empty($selectedOfficeCode)) {
                    $units = getSDOUnitsByOfficeCode($selectedOfficeCode);
                    foreach ($units as $unit) {
                        $selected = (!empty($_GET['office_id']) && $_GET['office_id'] == $unit['id']) ? 'selected' : '';
                        echo '<option value="' . $unit['id'] . '" ' . $selected . '>' . htmlspecialchars($unit['office_name']) . '</option>';
                    }
                }
                ?>
            </select>
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo navUrl('/users.php'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Users Table -->
<div class="data-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>User</th>
                    <th>Employee Info</th>
                    <th>Role</th>
                    <th>Status</th>
                    <th>Registered</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($users)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <span class="empty-icon"><i class="fas fa-users"></i></span>
                            <h3>No users found</h3>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($users as $user): ?>
                <tr>
                    <td>
                        <div class="user-cell">
                            <div class="user-avatar-placeholder-sm">
                                <?php echo strtoupper(substr($user['full_name'], 0, 1)); ?>
                            </div>
                            <div>
                                <div class="cell-primary"><?php echo htmlspecialchars($user['full_name']); ?></div>
                                <div class="cell-secondary"><?php echo htmlspecialchars($user['email']); ?></div>
                            </div>
                        </div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($user['employee_position'] ?: '-'); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($user['employee_office'] ?: ''); ?></div>
                    </td>
                    <td>
                        <span class="role-badge role-<?php echo strtolower(str_replace(' ', '-', $user['role_name'])); ?>">
                            <?php echo htmlspecialchars(ucfirst($user['role_name'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php
                        $statusClass = $user['status'] === 'active' ? 'status-approved' : ($user['status'] === 'pending' ? 'status-pending' : 'status-rejected');
                        ?>
                        <span class="status-badge <?php echo $statusClass; ?>">
                            <?php echo ucfirst($user['status']); ?>
                        </span>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($user['created_at'])); ?></div>
                        <div class="cell-secondary"><?php echo $user['last_login'] ? date('M j, g:i A', strtotime($user['last_login'])) : 'Never'; ?></div>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($user['status'] === 'pending'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                <input type="hidden" name="action" value="approve">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-icon" title="Approve" style="color: var(--success);">
                                    <i class="fas fa-check"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            
                            <button type="button"
                                    class="btn btn-icon"
                                    title="Edit"
                                    onclick="openEditUserModal(this)"
                                    data-user-id="<?php echo $user['id']; ?>"
                                    data-full-name="<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>"
                                    data-role-id="<?php echo $user['role_id']; ?>"
                                    data-employee-no="<?php echo htmlspecialchars($user['employee_no'] ?? '', ENT_QUOTES); ?>"
                                    data-employee-position="<?php echo htmlspecialchars($user['employee_position'] ?? '', ENT_QUOTES); ?>"
                                    data-office-id="<?php echo intval($user['office_id'] ?? 0); ?>"
                                    data-office-code="<?php echo htmlspecialchars(getOfficeCodeFromUnitId($user['office_id'] ?? 0), ENT_QUOTES); ?>"
                                    data-is-active="<?php echo intval($user['is_active']); ?>">
                                <i class="fas fa-edit"></i>
                            </button>

                            <?php if ($user['id'] != $auth->getUserId()): ?>
                            <?php if ($user['status'] === 'active'): ?>
                            <button type="button"
                                    class="btn btn-icon"
                                    title="Deactivate"
                                    onclick="openDeactivateUserModal(this)"
                                    data-user-id="<?php echo $user['id']; ?>"
                                    data-full-name="<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>">
                                <i class="fas fa-ban"></i>
                            </button>
                            <?php elseif ($user['status'] === 'inactive'): ?>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                <input type="hidden" name="action" value="activate">
                                <input type="hidden" name="id" value="<?php echo $user['id']; ?>">
                                <button type="submit" class="btn btn-icon" title="Activate" style="color: var(--success);">
                                    <i class="fas fa-check-circle"></i>
                                </button>
                            </form>
                            <?php endif; ?>

                            <button type="button"
                                    class="btn btn-icon"
                                    title="Delete"
                                    style="color: var(--danger);"
                                    onclick="openDeleteUserModal(this)"
                                    data-user-id="<?php echo $user['id']; ?>"
                                    data-full-name="<?php echo htmlspecialchars($user['full_name'], ENT_QUOTES); ?>"
                                    data-email="<?php echo htmlspecialchars($user['email'], ENT_QUOTES); ?>">
                                <i class="fas fa-trash"></i>
                            </button>
                            <?php endif; ?>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
    
    <?php if ($totalPages > 1): ?>
    <div class="pagination">
        <div class="pagination-info">Page <?php echo $page; ?> of <?php echo $totalPages; ?></div>
        <div class="pagination-links">
            <?php for ($i = 1; $i <= $totalPages; $i++): ?>
            <a href="<?php echo navUrl('/users.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php if ($auth->isSuperAdmin()): ?>
<!-- Add User Modal -->
<div class="modal-overlay" id="addUserModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus" style="margin-right: 8px;"></i> Add User</h3>
            <button class="modal-close" type="button" onclick="closeAddUserModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="create">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" class="form-control" required placeholder="user@deped.gov.ph">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role_id" class="form-control" required>
                            <option value="" disabled selected>-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>">
                                <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Office</label>
                        <select name="office" id="add_office" class="form-control" aria-label="Select office to enable Unit/Section">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($topOffices as $o): ?>
                            <option value="<?php echo htmlspecialchars($o['code']); ?>"><?php echo htmlspecialchars($o['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-hint">OSDS, SGOD, or CID</span>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <select name="office_id" id="add_unit_id" class="form-control" disabled>
                            <option value="">-- Select Unit/Section --</option>
                        </select>
                        <span class="form-hint">Select an Office first</span>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Password <span class="required">*</span></label>
                        <input type="password" name="password" class="form-control" required minlength="8">
                        <span class="form-hint">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm Password <span class="required">*</span></label>
                        <input type="password" name="confirm_password" class="form-control" required minlength="8">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee No. (optional)</label>
                        <input type="text" name="employee_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position (optional)</label>
                        <input type="text" name="employee_position" class="form-control">
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" checked>
                        <span>User is active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeAddUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save User</button>
            </div>
        </form>
    </div>
</div>

<!-- Edit User Modal -->
<div class="modal-overlay" id="editUserModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit" style="margin-right: 8px;"></i> Edit User</h3>
            <button class="modal-close" type="button" onclick="closeEditUserModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="update_user">
                <input type="hidden" name="id" id="edit_user_id" value="">

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Full Name <span class="required">*</span></label>
                        <input type="text" name="full_name" id="edit_full_name" class="form-control" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Email <span class="required">*</span></label>
                        <input type="email" name="email" id="edit_email" class="form-control" required>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Role <span class="required">*</span></label>
                        <select name="role_id" id="edit_role_id" class="form-control" required>
                            <option value="" disabled>-- Select Role --</option>
                            <?php foreach ($roles as $role): ?>
                            <option value="<?php echo $role['id']; ?>">
                                <?php echo htmlspecialchars(ucfirst($role['role_name'])); ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Office</label>
                        <select name="office" id="edit_office" class="form-control" aria-label="Select office to enable Unit/Section">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($topOffices as $o): ?>
                            <option value="<?php echo htmlspecialchars($o['code']); ?>"><?php echo htmlspecialchars($o['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <select name="office_id" id="edit_office_id" class="form-control">
                            <option value="">-- Select Unit/Section --</option>
                        </select>
                    </div>
                    <div class="form-group"></div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee No. (optional)</label>
                        <input type="text" name="employee_no" id="edit_employee_no" class="form-control">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position (optional)</label>
                        <input type="text" name="employee_position" id="edit_employee_position" class="form-control">
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password (optional)</label>
                        <input type="password" name="password" id="edit_password" class="form-control" minlength="8" placeholder="Leave blank to keep current password">
                        <span class="form-hint">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Confirm New Password</label>
                        <input type="password" name="confirm_password" id="edit_confirm_password" class="form-control" minlength="8" placeholder="Repeat new password">
                    </div>
                </div>

                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="edit_is_active">
                        <span>User is active</span>
                    </label>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeEditUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-save"></i> Save Changes</button>
            </div>
        </form>
    </div>
</div>

<!-- Deactivate User Modal (same style as Delete User Modal) -->
<div class="modal-overlay" id="deactivateUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-ban" style="margin-right: 8px;"></i> Deactivate User</h3>
            <button class="modal-close" type="button" onclick="closeDeactivateUserModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="deactivate">
                <input type="hidden" name="id" id="deactivate_user_id" value="">

                <p style="margin-bottom: 10px;">
                    Are you sure you want to deactivate this user?
                </p>
                <div style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                    <div style="font-weight: 700;" id="deactivate_user_name"></div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;" id="deactivate_user_email"></div>
                </div>
                <p style="margin-top: 12px; color: var(--text-muted); font-size: 0.9rem;">
                    The user will no longer be able to sign in. You can reactivate them later from the user list.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeactivateUserModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-ban"></i> Deactivate</button>
            </div>
        </form>
    </div>
</div>

<!-- Delete User Modal -->
<div class="modal-overlay" id="deleteUserModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-trash" style="margin-right: 8px;"></i> Delete User</h3>
            <button class="modal-close" type="button" onclick="closeDeleteUserModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="id" id="delete_user_id" value="">

                <p style="margin-bottom: 10px;">
                    Are you sure you want to delete this account?
                </p>
                <div style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                    <div style="font-weight: 700;" id="delete_user_name"></div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;" id="delete_user_email"></div>
                </div>
                <p style="margin-top: 12px; color: var(--danger); font-weight: 600;">
                    This action cannot be undone.
                </p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteUserModal()">Cancel</button>
                <button type="submit" class="btn btn-danger"><i class="fas fa-trash"></i> Delete</button>
            </div>
        </form>
    </div>
</div>

<script>
var unitsByOffice = <?php echo json_encode($unitsByOffice); ?>;

function fillUnitSelect(selectEl, officeCode) {
    if (!selectEl) return;
    selectEl.innerHTML = '<option value="">-- Select Unit/Section --</option>';
    selectEl.disabled = true;
    if (!officeCode || !unitsByOffice[officeCode]) return;
    var units = unitsByOffice[officeCode];
    for (var i = 0; i < units.length; i++) {
        var opt = document.createElement('option');
        opt.value = units[i].id;
        opt.textContent = units[i].office_name || units[i].office_code || units[i].id;
        selectEl.appendChild(opt);
    }
    selectEl.disabled = false;
}

// Filter bar office/unit cascading
document.addEventListener('DOMContentLoaded', function() {
    var filterOffice = document.getElementById('filter_office');
    var filterUnit = document.getElementById('filter_unit_id');
    if (filterOffice && filterUnit) {
        // Initialize unit dropdown based on selected office (if any)
        var initialOfficeCode = filterOffice.value;
        if (initialOfficeCode) {
            fillUnitSelect(filterUnit, initialOfficeCode);
            // Preserve selected unit if it exists
            var selectedUnitId = filterUnit.getAttribute('data-selected-unit');
            if (selectedUnitId) {
                filterUnit.value = selectedUnitId;
            }
        }
        
        filterOffice.addEventListener('change', function() {
            var code = filterOffice.value;
            if (code) {
                fillUnitSelect(filterUnit, code);
                // Clear unit selection when office changes (unless it's still valid)
                setTimeout(function() {
                    var currentValue = filterUnit.value;
                    var options = Array.from(filterUnit.options).map(opt => opt.value);
                    if (currentValue && !options.includes(currentValue)) {
                        filterUnit.value = '';
                    }
                }, 100);
            } else {
                filterUnit.innerHTML = '<option value="">All Units</option>';
                filterUnit.disabled = true;
            }
        });
    }
});

function openAddUserModal() {
    document.getElementById('add_office').value = '';
    var u = document.getElementById('add_unit_id');
    u.innerHTML = '<option value="">-- Select Unit/Section --</option>';
    u.disabled = true;
    var modal = document.getElementById('addUserModal');
    if (modal) modal.classList.add('active');
}
function closeAddUserModal() {
    var modal = document.getElementById('addUserModal');
    if (modal) modal.classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var addOffice = document.getElementById('add_office');
    var addUnit = document.getElementById('add_unit_id');
    if (addOffice && addUnit) {
        addOffice.addEventListener('change', function() {
            var code = addOffice.value;
            fillUnitSelect(addUnit, code);
        });
    }
    var editOffice = document.getElementById('edit_office');
    var editUnit = document.getElementById('edit_office_id');
    if (editOffice && editUnit) {
        editOffice.addEventListener('change', function() {
            var code = editOffice.value;
            fillUnitSelect(editUnit, code);
            editUnit.value = '';
        });
    }
});

function openEditUserModal(btn) {
    document.getElementById('edit_user_id').value = btn.getAttribute('data-user-id') || '';
    document.getElementById('edit_full_name').value = btn.getAttribute('data-full-name') || '';
    document.getElementById('edit_email').value = btn.getAttribute('data-email') || '';
    document.getElementById('edit_role_id').value = btn.getAttribute('data-role-id') || '';
    document.getElementById('edit_employee_no').value = btn.getAttribute('data-employee-no') || '';
    document.getElementById('edit_employee_position').value = btn.getAttribute('data-employee-position') || '';
    var officeCode = btn.getAttribute('data-office-code') || '';
    var officeId = btn.getAttribute('data-office-id') || '';
    var editOffice = document.getElementById('edit_office');
    var editUnit = document.getElementById('edit_office_id');
    if (editOffice) editOffice.value = officeCode;
    fillUnitSelect(editUnit, officeCode);
    if (editUnit && officeId) editUnit.value = officeId;
    document.getElementById('edit_is_active').checked = (btn.getAttribute('data-is-active') === '1');
    document.getElementById('edit_password').value = '';
    document.getElementById('edit_confirm_password').value = '';

    var modal = document.getElementById('editUserModal');
    if (modal) modal.classList.add('active');
}
function closeEditUserModal() {
    var modal = document.getElementById('editUserModal');
    if (modal) modal.classList.remove('active');
}

function openDeactivateUserModal(btn) {
    document.getElementById('deactivate_user_id').value = btn.getAttribute('data-user-id') || '';
    document.getElementById('deactivate_user_name').textContent = btn.getAttribute('data-full-name') || '';
    document.getElementById('deactivate_user_email').textContent = btn.getAttribute('data-email') || '';

    var modal = document.getElementById('deactivateUserModal');
    if (modal) modal.classList.add('active');
}
function closeDeactivateUserModal() {
    var modal = document.getElementById('deactivateUserModal');
    if (modal) modal.classList.remove('active');
}

function openDeleteUserModal(btn) {
    document.getElementById('delete_user_id').value = btn.getAttribute('data-user-id') || '';
    document.getElementById('delete_user_name').textContent = btn.getAttribute('data-full-name') || '';
    document.getElementById('delete_user_email').textContent = btn.getAttribute('data-email') || '';

    var modal = document.getElementById('deleteUserModal');
    if (modal) modal.classList.add('active');
}
function closeDeleteUserModal() {
    var modal = document.getElementById('deleteUserModal');
    if (modal) modal.classList.remove('active');
}
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
