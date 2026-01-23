<?php
/**
 * User Profile Page
 * SDO ATLAS
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/AdminUser.php';

$userModel = new AdminUser();
$message = '';
$error = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $data = [
        'full_name' => trim($_POST['full_name'] ?? ''),
        'employee_no' => trim($_POST['employee_no'] ?? ''),
        'employee_position' => trim($_POST['employee_position'] ?? ''),
        'employee_office' => $_POST['employee_office'] ?? ''
    ];
    
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
            
            // Refresh user data
            $currentUser = $userModel->getById($auth->getUserId());
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

<div class="complaint-detail-grid" style="grid-template-columns: 1fr 350px;">
    <!-- Profile Form -->
    <div class="detail-card" style="order: 2;">
        <div class="detail-card-header">
            <h3><i class="fas fa-user-edit"></i> Edit Profile</h3>
        </div>
        <div class="detail-card-body" style="order: 2;">
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
                
                <div class="form-group">
                    <label class="form-label">Office/Division</label>
                    <select name="employee_office" class="form-control">
                        <option value="">-- Select Office --</option>
                        <?php foreach (SDO_OFFICES as $code => $name): ?>
                        <option value="<?php echo $code; ?>" <?php echo ($currentUser['employee_office'] ?? '') === $code ? 'selected' : ''; ?>>
                            <?php echo htmlspecialchars($name); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
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
    
    <!-- Account Info -->
    <div style="display: flex; flex-direction: column; gap: 20px; order: 1;">
        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-id-card"></i> Account Info</h3>
            </div>
            <div class="detail-card-body" style="order: 1;">
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
        
        <div class="detail-card" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
            <div class="detail-card-body" style="text-align: center; padding: 30px;">
                <div class="user-avatar-placeholder" style="width: 80px; height: 80px; font-size: 2rem; margin: 0 auto 16px;">
                    <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                </div>
                <h3 style="margin-bottom: 4px;"><?php echo htmlspecialchars($currentUser['full_name']); ?></h3>
                <p style="opacity: 0.8; margin: 0;"><?php echo htmlspecialchars($currentUser['employee_position'] ?? 'SDO Employee'); ?></p>
            </div>
        </div>
    </div>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
