<?php
/**
 * Locator Slips Management Page
 * SDO ATLAS - View, create, and approve Locator Slips
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../services/TrackingService.php';

$lsModel = new LocatorSlip();
$trackingService = new TrackingService();

// Get current user info for routing and visibility
// Use effective role ID/Name which accounts for OIC delegation
$currentRoleId = $auth->getEffectiveRoleId();
$currentRoleName = $auth->getEffectiveRoleName();
$isActingAsOIC = $auth->isActingAsOIC();

$action = $_GET['action'] ?? '';
$viewId = $_GET['view'] ?? '';
$editId = $_GET['edit'] ?? '';
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create') {
        // Create new Locator Slip
        try {
            $controlNo = $trackingService->generateLSNumber();
            
            $data = [
                'ls_control_no' => $controlNo,
                'employee_name' => $_POST['employee_name'],
                'employee_position' => $_POST['employee_position'],
                'employee_office' => $_POST['employee_office'],
                'purpose_of_travel' => $_POST['purpose_of_travel'],
                'travel_type' => $_POST['travel_type'],
                'date_time' => $_POST['date_time'],
                'destination' => $_POST['destination'],
                'requesting_employee_name' => $_POST['requesting_employee_name'] ?? $_POST['employee_name'],
                'request_date' => date('Y-m-d'),
                'user_id' => $auth->getUserId()
            ];
            
            // Get requester info for routing
            $requesterRoleId = $currentUser['role_id'];
            $requesterOffice = $currentUser['employee_office'] ?? $_POST['employee_office'];
            
            $id = $lsModel->create($data, $requesterRoleId, $requesterOffice);
            $auth->logActivity('create', 'locator_slip', $id, 'Created Locator Slip: ' . $controlNo);
            
            $message = 'Locator Slip filed successfully! Tracking Number: ' . $controlNo;
            $action = ''; // Close modal
        } catch (Exception $e) {
            $error = 'Failed to create Locator Slip: ' . $e->getMessage();
        }
    }
    
    if ($postAction === 'edit') {
        // Edit Locator Slip (only if pending)
        try {
            $id = $_POST['id'];
            $ls = $lsModel->getById($id);
            
            if (!$ls) {
                $error = 'Locator Slip not found.';
            } elseif (!$lsModel->canUserEdit($ls, $auth->getUserId())) {
                $error = 'You cannot edit this Locator Slip.';
            } else {
                $data = [
                    'employee_name' => $_POST['employee_name'],
                    'employee_position' => $_POST['employee_position'],
                    'employee_office' => $_POST['employee_office'],
                    'purpose_of_travel' => $_POST['purpose_of_travel'],
                    'travel_type' => $_POST['travel_type'],
                    'date_time' => $_POST['date_time'],
                    'destination' => $_POST['destination']
                ];
                
                $lsModel->update($id, $data, $auth->getUserId());
                $auth->logActivity('update', 'locator_slip', $id, 'Updated Locator Slip: ' . $ls['ls_control_no']);
                $message = 'Locator Slip updated successfully!';
            }
        } catch (Exception $e) {
            $error = 'Failed to update Locator Slip: ' . $e->getMessage();
        }
    }
    
    if ($postAction === 'approve') {
        $id = $_POST['id'];
        $ls = $lsModel->getById($id);
        
        // Check if user can approve:
        // 1. They are the assigned approver
        // 2. They are acting as OIC for the assigned approver's role
        // 3. They are ASDS or superadmin
        $canApprove = ($ls['assigned_approver_user_id'] == $auth->getUserId()) || 
                     $auth->isASDS() || 
                     $auth->isSuperAdmin();
        
        // Also check if user is OIC for the assigned approver's role
        if (!$canApprove && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $ls['assigned_approver_role_id']) {
                $canApprove = true;
            }
        }
        
        if ($ls && $ls['status'] === 'pending' && $canApprove) {
            // Check if this is an OIC approval
            $isOIC = $auth->isActingAsOIC();
            
            // Expand common acronyms to full titles for approver position
            $posRaw = trim($currentUser['employee_position'] ?? '');
            $posKey = strtoupper($posRaw);
            $positionMap = [
                'ASDS' => 'Assistant Schools Division Superintendent',
                'AOV'  => 'Administrative Officer V',
                'AO V' => 'Administrative Officer V',
                'SDS'  => 'Schools Division Superintendent',
                'SUPERADMIN' => 'Superadmin',
                'OSDS_CHIEF' => 'Administrative Officer V',
            ];
            // Also check role_name for position
            $approverPosition = $positionMap[$posKey] ?? $positionMap[$currentUser['role_name']] ?? ($posRaw ?: $currentUser['role_name'] ?? '');

            $lsModel->approve($id, $auth->getUserId(), $currentUser['full_name'], $approverPosition, $isOIC);
            
            // Log with OIC prefix if applicable
            $actionType = $isOIC ? 'OIC-APPROVAL' : 'approve';
            $auth->logActivity($actionType, 'locator_slip', $id, 'Approved Locator Slip: ' . $ls['ls_control_no']);
            $message = 'Locator Slip approved successfully!';
        } else {
            $error = 'You do not have permission to approve this request.';
        }
    }
    
    if ($postAction === 'reject' && $auth->canApproveLS()) {
        $id = $_POST['id'];
        $reason = $_POST['rejection_reason'] ?? null;
        $ls = $lsModel->getById($id);
        
        if ($ls && $ls['status'] === 'pending') {
            $lsModel->reject($id, $auth->getUserId(), $reason);
            $auth->logActivity('reject', 'locator_slip', $id, 'Rejected Locator Slip: ' . $ls['ls_control_no']);
            $message = 'Locator Slip rejected.';
        }
    }
}

// View single request
$viewData = null;
if ($viewId) {
    $viewData = $lsModel->getById($viewId);
    if (!$viewData) {
        $error = 'Locator Slip not found.';
    } elseif (!$lsModel->canUserView($viewData, $currentRoleId, $auth->getUserId())) {
        $error = 'You do not have permission to view this request.';
        $viewData = null;
    }
}

// Get list data with comprehensive filters
$filters = [];

// Add filter parameters
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['unit'])) {
    $filters['unit'] = $_GET['unit'];
}
if (!empty($_GET['travel_type'])) {
    $filters['travel_type'] = $_GET['travel_type'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}
if (!empty($_GET['approval_date_from'])) {
    $filters['approval_date_from'] = $_GET['approval_date_from'];
}
if (!empty($_GET['approval_date_to'])) {
    $filters['approval_date_to'] = $_GET['approval_date_to'];
}
if (!empty($_GET['approver_id'])) {
    $filters['approver_id'] = $_GET['approver_id'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

// Pass viewer info for visibility filtering
$requests = $lsModel->getAll($filters, $perPage, $offset, $currentRoleId, $auth->getUserId());
$totalRequests = $lsModel->getCount($filters, $currentRoleId, $auth->getUserId());
$totalPages = ceil($totalRequests / $perPage);

// Get approvers for filter dropdown
require_once __DIR__ . '/../models/AdminUser.php';
$userModel = new AdminUser();
$allApprovers = [];
if ($auth->isSuperAdmin() || $auth->isASDS()) {
    // Get all unit heads
    $unitHeads = $userModel->getUnitHeads(true);
    foreach ($unitHeads as $uh) {
        $allApprovers[$uh['id']] = $uh['full_name'] . ' (' . $uh['role_name'] . ')';
    }
}

// Pre-fill form with user data
$formData = [
    'employee_name' => $currentUser['full_name'],
    'employee_position' => $currentUser['employee_position'] ?? '',
    'employee_office' => $currentUser['employee_office'] ?? ''
];
?>

<?php if ($isActingAsOIC): ?>
<!-- OIC Notice Banner -->
<div class="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; margin-bottom: 20px;">
    <i class="fas fa-user-shield"></i> 
    <strong>Acting as OIC:</strong> You are currently serving as Officer-In-Charge (<?php echo htmlspecialchars($auth->getEffectiveRoleDisplayName()); ?>). 
    You can approve requests on behalf of the unit head.
</div>
<?php endif; ?>

<?php if ($message): ?>
<div class="alert alert-success">
    <i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?>
</div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error">
    <i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?>
</div>
<?php endif; ?>

<?php if ($editId): ?>
<!-- Edit Locator Slip -->
<?php
$editData = $lsModel->getById($editId);
if (!$editData || !$lsModel->canUserEdit($editData, $auth->getUserId())) {
    $error = 'You cannot edit this Locator Slip.';
    $editData = null;
}
?>
<?php if ($editData): ?>
<div class="page-header">
    <a href="<?php echo navUrl('/locator-slips.php?view=' . $editData['id']); ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to View
    </a>
</div>

<div class="detail-card">
    <div class="detail-card-header">
        <h3><i class="fas fa-edit"></i> Edit Locator Slip</h3>
    </div>
    <div class="detail-card-body">
        <form method="POST" action="">
            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
            <input type="hidden" name="action" value="edit">
            <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Employee Name <span class="required">*</span></label>
                    <input type="text" name="employee_name" class="form-control" required
                           value="<?php echo htmlspecialchars($editData['employee_name']); ?>">
                </div>
                <div class="form-group">
                    <label class="form-label">Position</label>
                    <input type="text" name="employee_position" class="form-control"
                           value="<?php echo htmlspecialchars($editData['employee_position'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Office/Division</label>
                <select name="employee_office" class="form-control">
                    <option value="">-- Select Office --</option>
                    <?php foreach (SDO_OFFICES as $code => $name): ?>
                    <option value="<?php echo $code; ?>" <?php echo ($editData['employee_office'] ?? '') === $code ? 'selected' : ''; ?>>
                        <?php echo htmlspecialchars($name); ?>
                    </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="form-row">
                <div class="form-group">
                    <label class="form-label">Travel Type <span class="required">*</span></label>
                    <select name="travel_type" class="form-control" required>
                        <?php foreach (TRAVEL_TYPES as $code => $label): ?>
                        <option value="<?php echo $code; ?>" <?php echo ($editData['travel_type'] ?? '') === $code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($label); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label">Date & Time <span class="required">*</span></label>
                    <input type="datetime-local" name="date_time" class="form-control" required
                           value="<?php echo date('Y-m-d\TH:i', strtotime($editData['date_time'])); ?>">
                </div>
            </div>
            
            <div class="form-group">
                <label class="form-label">Destination <span class="required">*</span></label>
                <input type="text" name="destination" class="form-control" required
                       value="<?php echo htmlspecialchars($editData['destination']); ?>">
            </div>
            
            <div class="form-group">
                <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                <textarea name="purpose_of_travel" class="form-control" rows="3" required><?php echo htmlspecialchars($editData['purpose_of_travel']); ?></textarea>
            </div>
            
            <div class="form-actions">
                <a href="<?php echo navUrl('/locator-slips.php?view=' . $editData['id']); ?>" class="btn btn-secondary">
                    <i class="fas fa-times"></i> Cancel
                </a>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </div>
        </form>
    </div>
</div>
<?php endif; ?>

<?php elseif ($viewData): ?>
<!-- View Single Request -->
<div class="page-header">
    <a href="<?php echo navUrl('/locator-slips.php'); ?>" class="back-link">
        <i class="fas fa-arrow-left"></i> Back to List
    </a>
</div>

<div class="complaint-detail-grid">
    <div class="complaint-main">
        <!-- Reference Card -->
        <div class="detail-card ref-card">
            <div class="ref-header">
                <div class="ref-number"><?php echo htmlspecialchars($viewData['ls_control_no']); ?></div>
                <div class="ref-date">Filed on <?php echo date('F j, Y - g:i A', strtotime($viewData['created_at'])); ?></div>
            </div>
            <div class="ref-unit">
                <?php echo getStatusBadge($viewData['status']); ?>
                <span class="unit-badge large"><?php echo htmlspecialchars($viewData['travel_type']); ?></span>
            </div>
        </div>
        
        <!-- Request Details -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-info-circle"></i> Request Details</h3>
            </div>
            <div class="detail-card-body">
                <div class="detail-grid">
                    <div class="detail-item">
                        <label>Employee Name</label>
                        <span><?php echo htmlspecialchars($viewData['employee_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Position</label>
                        <span><?php echo htmlspecialchars($viewData['employee_position'] ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Office/Division</label>
                        <span><?php echo htmlspecialchars($viewData['employee_office'] ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Destination</label>
                        <span><?php echo htmlspecialchars($viewData['destination']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Date & Time</label>
                        <span><?php echo date('F j, Y - g:i A', strtotime($viewData['date_time'])); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Purpose of Travel</label>
                        <span class="narration-text"><?php echo nl2br(htmlspecialchars($viewData['purpose_of_travel'])); ?></span>
                    </div>
                </div>
            </div>
        </div>
        
        <?php if ($viewData['status'] !== 'pending' && ($viewData['approver_name'] || $viewData['rejection_reason'])): ?>
        <!-- Approval Details -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-<?php echo $viewData['status'] === 'approved' ? 'check-circle' : 'times-circle'; ?>"></i> 
                    <?php echo $viewData['status'] === 'approved' ? 'Approval' : 'Rejection'; ?> Details
                </h3>
            </div>
            <div class="detail-card-body">
                <div class="detail-grid">
                    <?php if ($viewData['status'] === 'approved'): ?>
                    <div class="detail-item">
                        <label>Approved By</label>
                        <span><?php echo htmlspecialchars($viewData['approver_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Position</label>
                        <span><?php echo htmlspecialchars($viewData['approver_position'] ?: '-'); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Approval Date</label>
                        <span><?php echo $viewData['approval_date'] ? date('F j, Y', strtotime($viewData['approval_date'])) : '-'; ?></span>
                    </div>
                    <?php else: ?>
                    <div class="detail-item">
                        <label>Rejection Reason</label>
                        <span><?php echo htmlspecialchars($viewData['rejection_reason'] ?: 'No reason provided'); ?></span>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
        <?php endif; ?>
    </div>
    
    <div class="complaint-sidebar">
        <!-- Actions -->
        <?php 
        $canApprove = $viewData['status'] === 'pending' && 
                     ($viewData['assigned_approver_user_id'] == $auth->getUserId() || 
                      $auth->isASDS() || 
                      $auth->isSuperAdmin());
        
        // Also check if user is OIC for the assigned approver's role
        if (!$canApprove && $viewData['status'] === 'pending' && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $viewData['assigned_approver_role_id']) {
                $canApprove = true;
            }
        }
        ?>
        <?php if ($canApprove): ?>
        <div class="detail-card action-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-tasks"></i> Actions</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST" action="" style="margin-bottom: 10px;">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="id" value="<?php echo $viewData['id']; ?>">
                    <button type="button" class="btn btn-success btn-block" onclick="openApproveModal(<?php echo $viewData['id']; ?>)">
                        <i class="fas fa-check"></i> Approve
                    </button>
                </form>
                
                <button type="button" class="btn btn-danger btn-block" onclick="showRejectModal(<?php echo $viewData['id']; ?>)">
                    <i class="fas fa-times"></i> Reject
                </button>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($lsModel->canUserEdit($viewData, $auth->getUserId())): ?>
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-edit"></i> Edit</h3>
            </div>
            <div class="detail-card-body">
                <a href="<?php echo navUrl('/locator-slips.php?edit=' . $viewData['id']); ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-edit"></i> Edit Request
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <?php if ($viewData['status'] === 'approved'): ?>
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-download"></i> Download</h3>
            </div>
            <div class="detail-card-body">
                <a href="<?php echo navUrl('/api/generate-docx.php?type=ls&id=' . $viewData['id']); ?>" class="btn btn-primary btn-block">
                    <i class="fas fa-file-word"></i> Download DOCX
                </a>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- Filed By -->
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-user"></i> Filed By</h3>
            </div>
            <div class="detail-card-body">
                <div class="detail-item">
                    <label>Name</label>
                    <span><?php echo htmlspecialchars($viewData['filed_by_name']); ?></span>
                </div>
                <div class="detail-item">
                    <label>Email</label>
                    <span><?php echo htmlspecialchars($viewData['filed_by_email']); ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Approve Modal -->
<div class="modal-overlay" id="approveModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-check-circle" style="margin-right: 8px; color: var(--success);"></i> Approve Locator Slip</h3>
            <button class="modal-close" type="button" onclick="closeApproveModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="approve">
                <input type="hidden" name="id" id="approveId" value="">

                <p style="margin-bottom: 10px;">
                    Are you sure you want to approve this Locator Slip?
                </p>
                <div style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                    <div style="font-weight: 700;" id="approveControlNo"></div>
                    <div style="color: var(--text-muted); font-size: 0.9rem;" id="approveEmployeeName"></div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeApproveModal()">Cancel</button>
                <button type="submit" class="btn btn-success"><i class="fas fa-check"></i> Yes, approve</button>
            </div>
        </form>
    </div>
</div>

<!-- Reject Modal -->
<div class="modal-overlay" id="rejectModal">
    <div class="modal">
        <div class="modal-header">
            <h3>Reject Locator Slip</h3>
            <button class="modal-close" onclick="closeRejectModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="reject">
                <input type="hidden" name="id" id="rejectId" value="">
                
                <div class="form-group">
                    <label class="form-label">Reason for Rejection (Optional)</label>
                    <textarea name="rejection_reason" class="form-control" rows="4" placeholder="Enter reason..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">Reject Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openApproveModal(id) {
    document.getElementById('approveId').value = id;
    // Pull details from current page if available
    var controlNoEl = document.querySelector('.ref-number');
    var employeeNameEl = document.querySelector('.detail-item span');
    if (controlNoEl) document.getElementById('approveControlNo').textContent = controlNoEl.textContent.trim();
    // Find employee name specifically
    var nameLabel = Array.from(document.querySelectorAll('.detail-item label')).find(l => l.textContent.trim() === 'Employee Name');
    if (nameLabel && nameLabel.parentElement) {
        var span = nameLabel.parentElement.querySelector('span');
        if (span) document.getElementById('approveEmployeeName').textContent = span.textContent.trim();
    }
    document.getElementById('approveModal').classList.add('active');
}
function closeApproveModal() {
    document.getElementById('approveModal').classList.remove('active');
}

function showRejectModal(id) {
    document.getElementById('rejectId').value = id;
    document.getElementById('rejectModal').classList.add('active');
}
function closeRejectModal() {
    document.getElementById('rejectModal').classList.remove('active');
}
</script>

<?php else: ?>
<!-- List View -->
<div class="page-header">
    <div class="result-count">
        <?php echo $totalRequests; ?> Locator Slip<?php echo $totalRequests !== 1 ? 's' : ''; ?>
    </div>
    <button type="button" class="btn btn-primary" onclick="openNewModal()">
        <i class="fas fa-plus"></i> New Locator Slip
    </button>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="token" value="<?php echo $currentToken; ?>">
        
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Control no, name, destination..." 
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Unit</label>
            <select name="unit" class="filter-select">
                <option value="">All Units</option>
                <?php foreach (SDO_OFFICES as $code => $name): ?>
                <option value="<?php echo $code; ?>" <?php echo ($_GET['unit'] ?? '') === $code ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Travel Type</label>
            <select name="travel_type" class="filter-select">
                <option value="">All Types</option>
                <?php foreach (TRAVEL_TYPES as $code => $label): ?>
                <option value="<?php echo $code; ?>" <?php echo ($_GET['travel_type'] ?? '') === $code ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($label); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Date Filed From</label>
            <input type="date" name="date_from" class="filter-input" 
                   value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Date Filed To</label>
            <input type="date" name="date_to" class="filter-input" 
                   value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Approval Date From</label>
            <input type="date" name="approval_date_from" class="filter-input" 
                   value="<?php echo htmlspecialchars($_GET['approval_date_from'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Approval Date To</label>
            <input type="date" name="approval_date_to" class="filter-input" 
                   value="<?php echo htmlspecialchars($_GET['approval_date_to'] ?? ''); ?>">
        </div>
        
        <?php if (!empty($allApprovers)): ?>
        <div class="filter-group">
            <label>Approver</label>
            <select name="approver_id" class="filter-select">
                <option value="">All Approvers</option>
                <?php foreach ($allApprovers as $id => $name): ?>
                <option value="<?php echo $id; ?>" <?php echo ($_GET['approver_id'] ?? '') == $id ? 'selected' : ''; ?>>
                    <?php echo htmlspecialchars($name); ?>
                </option>
                <?php endforeach; ?>
            </select>
        </div>
        <?php endif; ?>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo navUrl('/locator-slips.php'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Data Table -->
<div class="data-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Control No.</th>
                    <th>Employee</th>
                    <th>Destination</th>
                    <th>Date/Time</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <span class="empty-icon"><i class="fas fa-map-marker-alt"></i></span>
                            <h3>No Locator Slips found</h3>
                            <p>Create a new Locator Slip to get started</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($requests as $ls): ?>
                <tr>
                    <td>
                        <a href="<?php echo navUrl('/locator-slips.php?view=' . $ls['id']); ?>" class="ref-link">
                            <?php echo htmlspecialchars($ls['ls_control_no']); ?>
                        </a>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($ls['employee_name']); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($ls['employee_position'] ?: ''); ?></div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($ls['destination']); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($ls['travel_type']); ?></div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($ls['date_time'])); ?></div>
                        <div class="cell-secondary"><?php echo date('g:i A', strtotime($ls['date_time'])); ?></div>
                    </td>
                    <td><?php echo getStatusBadge($ls['status']); ?></td>
                    <td>
                        <div class="action-buttons">
                            <a href="<?php echo navUrl('/locator-slips.php?view=' . $ls['id']); ?>" class="btn btn-icon" title="View">
                                <i class="fas fa-eye"></i>
                            </a>
                            <?php if ($lsModel->canUserEdit($ls, $auth->getUserId())): ?>
                            <a href="<?php echo navUrl('/locator-slips.php?edit=' . $ls['id']); ?>" class="btn btn-icon" title="Edit" style="color: var(--primary);">
                                <i class="fas fa-edit"></i>
                            </a>
                            <?php endif; ?>
                            <?php if ($ls['status'] === 'approved'): ?>
                            <a href="<?php echo navUrl('/api/generate-docx.php?type=ls&id=' . $ls['id']); ?>" class="btn btn-icon" title="Download" style="color: var(--success);">
                                <i class="fas fa-download"></i>
                            </a>
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
            <?php if ($page > 1): ?>
            <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>" class="page-link">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>" class="page-link">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<!-- New Locator Slip Modal -->
<div class="modal-overlay" id="newModal" <?php echo $action === 'new' ? 'class="active"' : ''; ?>>
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3><i class="fas fa-map-marker-alt"></i> New Locator Slip</h3>
            <button class="modal-close" onclick="closeNewModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Name <span class="required">*</span></label>
                        <input type="text" name="employee_name" class="form-control" required
                               value="<?php echo htmlspecialchars($formData['employee_name']); ?>">
                    </div>
                    <div class="form-group">
                        <label class="form-label">Position</label>
                        <input type="text" name="employee_position" class="form-control"
                               value="<?php echo htmlspecialchars($formData['employee_position']); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Office/Division</label>
                    <select name="employee_office" class="form-control">
                        <option value="">-- Select Office --</option>
                        <?php foreach (SDO_OFFICES as $code => $name): ?>
                        <option value="<?php echo $code; ?>" <?php echo $formData['employee_office'] === $code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Travel Type <span class="required">*</span></label>
                        <select name="travel_type" class="form-control" required>
                            <?php foreach (TRAVEL_TYPES as $code => $label): ?>
                            <option value="<?php echo $code; ?>"><?php echo htmlspecialchars($label); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Date & Time <span class="required">*</span></label>
                        <input type="datetime-local" name="date_time" class="form-control" required
                               value="<?php echo date('Y-m-d\TH:i'); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Destination <span class="required">*</span></label>
                    <input type="text" name="destination" class="form-control" required
                           placeholder="Where are you going?">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                    <textarea name="purpose_of_travel" class="form-control" rows="3" required
                              placeholder="Describe the purpose of your travel..."></textarea>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeNewModal()">Cancel</button>
                <button type="submit" class="btn btn-primary"><i class="fas fa-paper-plane"></i> Submit Request</button>
            </div>
        </form>
    </div>
</div>

<script>
function openNewModal() {
    document.getElementById('newModal').classList.add('active');
}
function closeNewModal() {
    document.getElementById('newModal').classList.remove('active');
    // Remove action param from URL
    const url = new URL(window.location);
    url.searchParams.delete('action');
    window.history.replaceState({}, '', url);
}

// Auto-open modal if action=new
<?php if ($action === 'new'): ?>
document.addEventListener('DOMContentLoaded', openNewModal);
<?php endif; ?>
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
