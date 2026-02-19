<?php
/**
 * User Profile Page
 * SDO ALPAS - Office/Division and Unit are read-only except for Superadmin
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/AdminUser.php';

$userModel = new AdminUser();
$message = '';
$error = '';

// Resolve office and unit for display (office_id → division name + unit name)
$profileOfficeId = isset($currentUser['office_id']) ? (int) $currentUser['office_id'] : null;
$profileOffice = $profileOfficeId && function_exists('getOfficeById') ? getOfficeById($profileOfficeId) : null;
$profileUnitName = $profileOffice ? ($profileOffice['office_name'] ?? '') : '';
$profileOfficeCode = $profileOfficeId && function_exists('getOfficeCodeFromUnitId') ? getOfficeCodeFromUnitId($profileOfficeId) : '';
$profileDivisionName = ($profileOfficeCode && defined('TOP_OFFICE_CODES') && isset(TOP_OFFICE_CODES[$profileOfficeCode])) ? TOP_OFFICE_CODES[$profileOfficeCode] : '';
// Fallback when no office_id: use employee_office text
if ($profileUnitName === '' && !empty($currentUser['employee_office'])) {
    $profileUnitName = $currentUser['employee_office'];
    if (defined('SDO_OFFICES') && isset(SDO_OFFICES[$currentUser['employee_office']])) {
        $profileUnitName = SDO_OFFICES[$currentUser['employee_office']];
    }
}
$isSuperAdmin = $auth->isSuperAdmin();
$topOffices = function_exists('getSDOOfficesForOfficeDropdown') ? getSDOOfficesForOfficeDropdown() : [];
$unitsByOffice = function_exists('getUnitsByOfficeForJs') ? getUnitsByOfficeForJs() : [];

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'employee_no' => trim($_POST['employee_no'] ?? ''),
        'employee_position' => trim($_POST['employee_position'] ?? ''),
    ];
    // Only Superadmin can change office/unit
    if ($isSuperAdmin) {
        $officeId = !empty($_POST['office_id']) ? (int) $_POST['office_id'] : null;
        $data['office_id'] = $officeId;
        if ($officeId && function_exists('getOfficeById')) {
            $office = getOfficeById($officeId);
            $data['employee_office'] = $office['office_code'] ?? $office['office_name'] ?? null;
        } else {
            $data['employee_office'] = $_POST['employee_office'] ?? '';
        }
    }
    
    // Validate
    if (empty($data['full_name'])) {
        $error = 'Full name is required.';
    } else {
        // Update password if provided
        if (!empty($_POST['new_password'])) {
            if (strlen($_POST['new_password']) < 8) {
                $error = 'Password must be at least 8 characters.';
            } elseif ($_POST['new_password'] !== $_POST['confirm_password']) {
                $error = 'Passwords do not match.';
            } else {
                $data['password'] = $_POST['new_password'];
            }
        }
        
        if (!$error) {
            $userModel->update($auth->getUserId(), $data);
            $auth->logActivity('update_profile', 'user', $auth->getUserId(), 'Updated profile');
            $message = 'Profile updated successfully!';
            
            // Refresh user data and re-resolve office/unit
            $currentUser = $userModel->getById($auth->getUserId());
            $profileOfficeId = isset($currentUser['office_id']) ? (int) $currentUser['office_id'] : null;
            $profileOffice = $profileOfficeId && function_exists('getOfficeById') ? getOfficeById($profileOfficeId) : null;
            $profileUnitName = $profileOffice ? ($profileOffice['office_name'] ?? '') : '';
            $profileOfficeCode = $profileOfficeId && function_exists('getOfficeCodeFromUnitId') ? getOfficeCodeFromUnitId($profileOfficeId) : '';
            $profileDivisionName = ($profileOfficeCode && defined('TOP_OFFICE_CODES') && isset(TOP_OFFICE_CODES[$profileOfficeCode])) ? TOP_OFFICE_CODES[$profileOfficeCode] : '';
            if ($profileUnitName === '' && !empty($currentUser['employee_office'])) {
                $profileUnitName = defined('SDO_OFFICES') && isset(SDO_OFFICES[$currentUser['employee_office']]) ? SDO_OFFICES[$currentUser['employee_office']] : $currentUser['employee_office'];
            }
        }
    }
}
?>

<?php if ($message): ?>
<div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo htmlspecialchars($message); ?></div>
<?php endif; ?>

<?php if ($error): ?>
<div class="alert alert-error"><i class="fas fa-exclamation-triangle"></i> <?php echo htmlspecialchars($error); ?></div>
<?php endif; ?>

<div class="complaint-detail-grid profile-page-grid" style="grid-template-columns: 1fr 350px; align-items: stretch;">
    <!-- Left column: Edit Profile (stretches to match right column) -->
    <div style="display: flex; flex-direction: column; min-height: 0;">
        <!-- Edit Profile -->
        <div class="detail-card" id="profile-edit-card" style="flex: 1; min-height: 0; overflow: hidden; display: flex; flex-direction: column;">
            <div class="detail-card-header" style="flex-shrink: 0;">
                <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
            </div>
            <div class="detail-card-body" id="profile-edit-body" style="overflow-y: auto; min-height: 0; flex: 1; scrollbar-width: none; -ms-overflow-style: none;">
            <form method="POST" action="">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                
                <div class="form-group">
                    <label class="form-label">Full Name <span class="required">*</span></label>
                    <input type="text" name="full_name" class="form-control" required
                           value="<?php echo htmlspecialchars($currentUser['full_name']); ?>">
                </div>
                
                <div class="form-group">
                    <label class="form-label">Email Address</label>
                    <input type="email" class="form-control" disabled
                           value="<?php echo htmlspecialchars($currentUser['email']); ?>">
                    <span class="form-hint">Email cannot be changed</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Employee Number</label>
                        <input type="text" name="employee_no" class="form-control"
                               value="<?php echo htmlspecialchars($currentUser['employee_no'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Position/Designation</label>
                        <input type="text" name="employee_position" class="form-control"
                               value="<?php echo htmlspecialchars($currentUser['employee_position'] ?? ''); ?>">
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Office/Division</label>
                        <?php if ($isSuperAdmin): ?>
                        <select name="office" id="profile_office" class="form-control" aria-label="Select office to enable Unit/Section">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($topOffices as $o): ?>
                            <option value="<?php echo htmlspecialchars($o['code']); ?>" <?php echo $profileOfficeCode === ($o['code'] ?? '') ? 'selected' : ''; ?>><?php echo htmlspecialchars($o['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-hint">OSDS, SGOD, or CID</span>
                        <?php else: ?>
                        <input type="text" class="form-control" readonly disabled
                               value="<?php echo htmlspecialchars($profileDivisionName ?: $profileUnitName ?: ($currentUser['employee_office'] ?? '—')); ?>">
                        <span class="form-hint">Office/Division can only be changed by Superadmin</span>
                        <?php endif; ?>
                    </div>
                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <?php if ($isSuperAdmin): ?>
                        <select name="office_id" id="profile_unit_id" class="form-control" <?php echo empty($profileOfficeCode) ? 'disabled' : ''; ?>>
                            <option value="">-- Select Unit/Section --</option>
                            <?php
                            if ($profileOfficeCode && !empty($unitsByOffice[$profileOfficeCode])) {
                                foreach ($unitsByOffice[$profileOfficeCode] as $u) {
                                    $sel = ($profileOfficeId && (int)$u['id'] === $profileOfficeId) ? ' selected' : '';
                                    echo '<option value="' . (int)$u['id'] . '"' . $sel . '>' . htmlspecialchars($u['office_name'] ?? $u['office_code'] ?? $u['id']) . '</option>';
                                }
                            }
                            ?>
                        </select>
                        <span class="form-hint">Select an Office first</span>
                        <?php else: ?>
                        <input type="text" class="form-control" readonly disabled
                               value="<?php echo htmlspecialchars($profileUnitName ?: '—'); ?>">
                        <span class="form-hint">Unit you belong to</span>
                        <?php endif; ?>
                    </div>
                </div>
                
                <hr style="border: none; border-top: 1px solid var(--border-color); margin: 24px 0;">
                
                <h4 style="margin-bottom: 16px; color: var(--text-secondary);">Change Password</h4>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">New Password</label>
                        <input type="password" name="new_password" class="form-control"
                               placeholder="Leave blank to keep current">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Confirm Password</label>
                        <input type="password" name="confirm_password" class="form-control"
                               placeholder="Re-enter new password">
                    </div>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-save"></i> Save Changes
                </button>
            </form>
            </div>
        </div>
    </div>

    <!-- Right column: Avatar, then Account Info (same height as Edit Profile column) -->
    <div id="profile-sidebar" style="display: flex; flex-direction: column; gap: 20px; min-height: 0;">
        <!-- Avatar (square card) - SDO theme: blue card, orange avatar icon -->
        <div class="detail-card profile-avatar-card" style="flex-shrink: 0; background: var(--primary-gradient); color: white; aspect-ratio: 1; max-height: 350px;">
            <div class="detail-card-body" style="text-align: center; padding: 24px; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 0;">
                <div class="user-avatar-placeholder profile-avatar-icon" style="width: 100px; height: 100px; font-size: 1.85rem; margin: 65px auto 12px; flex-shrink: 0; background:rgb(241, 142, 37); color: white;">
                    <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                </div>
                <div style="width: 100%; text-align: center; margin-top: 0;">
                    <h3 style="margin: 0 0 4px; font-size: 1.3rem; line-height: 1.3; text-align: center;"><?php echo htmlspecialchars($currentUser['full_name']); ?></h3>
                    <p style="opacity: 0.8; margin: 0; font-size: 1rem; text-align: center;"><?php echo htmlspecialchars($currentUser['employee_position'] ?? 'SDO Employee'); ?></p>
                </div>
            </div>
        </div>

        <!-- Account Info (flex to fill remaining space, aligned with Edit Profile) -->
        <div class="detail-card" style="flex: 1; min-height: 0; display: flex; flex-direction: column;">
            <div class="detail-card-header" style="flex-shrink: 0;">
                <h3><i class="fas fa-id-card"></i> Account Info</h3>
            </div>
            <div class="detail-card-body" style="flex: 1; min-height: 0;">
                <div class="detail-item">
                    <label>Role</label>
                    <span class="role-badge role-<?php echo strtolower(str_replace(' ', '-', $currentUser['role_name'])); ?>">
                        <?php echo htmlspecialchars(ucfirst($currentUser['role_name'])); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Account Status</label>
                    <span class="status-badge status-<?php echo $currentUser['status'] === 'active' ? 'approved' : 'pending'; ?>">
                        <?php echo ucfirst($currentUser['status']); ?>
                    </span>
                </div>
                <div class="detail-item">
                    <label>Member Since</label>
                    <span><?php echo date('F j, Y', strtotime($currentUser['created_at'])); ?></span>
                </div>
                <div class="detail-item">
                    <label>Last Login</label>
                    <span><?php echo $currentUser['last_login'] ? date('F j, Y - g:i A', strtotime($currentUser['last_login'])) : 'N/A'; ?></span>
                </div>
            </div>
        </div>
    </div>
</div>

<style>
#profile-edit-body::-webkit-scrollbar {
    display: none;
}
</style>


<?php if ($isSuperAdmin && !empty($unitsByOffice)): ?>
<script>
(function() {
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
    document.addEventListener('DOMContentLoaded', function() {
        var profileOffice = document.getElementById('profile_office');
        var profileUnit = document.getElementById('profile_unit_id');
        if (profileOffice && profileUnit) {
            profileOffice.addEventListener('change', function() {
                var code = profileOffice.value;
                fillUnitSelect(profileUnit, code);
                profileUnit.value = '';
            });
        }
    });
})();
</script>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
