<?php
/**
 * Admin Dashboard
 * SDO ATLAS - Role-aware dashboard for admins and employees
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';

$lsModel = new LocatorSlip();
$atModel = new AuthorityToTravel();

// Get current user's ID for personal stats
$userId = $auth->getUserId();
// Use effective role ID/Name which accounts for OIC delegation
$currentRoleId = $auth->getEffectiveRoleId();
$currentRoleName = $auth->getEffectiveRoleName();
$currentRoleDisplayName = $auth->getEffectiveRoleDisplayName();
$isActingAsOIC = $auth->isActingAsOIC();

// Get statistics based on role
if ($auth->isEmployee()) {
    // Employee sees only their own stats
    $myLsStats = $lsModel->getStatistics($userId);
    $myAtStats = $atModel->getMyStatistics($userId);
    $recentLS = $lsModel->getRecent(5, $userId);
    $recentAT = $atModel->getRecent(5, $userId);
} elseif ($auth->isUnitHead()) {
    // Unit heads (or OICs acting as unit heads) see stats about requests FROM THEIR UNIT
    $myLsStats = $lsModel->getStatistics($userId); // Their own LS if any
    $myAtStats = $atModel->getUnitStatistics($currentRoleId); // Stats from their supervised offices
    $pendingLS = $lsModel->getPending(5);
    $pendingAT = $atModel->getPending(5, $currentRoleId, $currentRoleName);
    $queueCount = $atModel->getPendingCountForRole($currentRoleName, $currentRoleId);
} else {
    // ASDS/Superadmin see all stats
    $myLsStats = $lsModel->getStatistics();
    $myAtStats = $atModel->getStatistics();
    $pendingLS = $lsModel->getPending(5);
    $pendingAT = $atModel->getPending(5, $currentRoleId, $currentRoleName);
    $queueCount = ($lsModel->getStatistics()['pending'] ?? 0) + ($atModel->getPendingCountForRole($currentRoleName, $currentRoleId));
}
?>

<?php if ($isActingAsOIC): ?>
<!-- OIC Notice Banner -->
<div class="alert" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; margin-bottom: 20px;">
    <i class="fas fa-user-shield"></i> 
    <strong>Acting as OIC:</strong> You are currently serving as Officer-In-Charge (<?php echo htmlspecialchars($currentRoleDisplayName); ?>). 
    You can process requests on behalf of the unit head.
</div>
<?php endif; ?>

<?php if ($auth->isEmployee()): ?>
<!-- ==================== EMPLOYEE DASHBOARD ==================== -->
<div class="dashboard-grid">
    <!-- File New Request Section -->
    <div class="dashboard-card">
        <div class="card-header">
            <h2><i class="fas fa-plus-circle"></i> File New Request</h2>
        </div>
        <div class="card-body">
            <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 16px;">
                <!-- Locator Slip -->
                <a href="<?php echo navUrl('/locator-slips.php?action=new'); ?>" class="request-type-card" style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fas fa-map-marker-alt" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">Locator Slip</span>
                    <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">For local movement</span>
                </a>
                
                <!-- AT - Local Official -->
                <a href="<?php echo navUrl('/authority-to-travel.php?action=new&type=local'); ?>" class="request-type-card" style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fas fa-car" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">AT - Local</span>
                    <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">Official - Within Region</span>
                </a>
                
                <!-- AT - National Official -->
                <a href="<?php echo navUrl('/authority-to-travel.php?action=new&type=national'); ?>" class="request-type-card" style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fas fa-plane" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">AT - National</span>
                    <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">Official - Outside Region</span>
                </a>
                
                <!-- AT - Personal -->
                <a href="<?php echo navUrl('/authority-to-travel.php?action=new&type=personal'); ?>" class="request-type-card" style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #fa709a 0%, #fee140 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                    <i class="fas fa-umbrella-beach" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                    <span style="font-weight: 600; font-size: 1rem;">AT - Personal</span>
                    <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">Non-official travel</span>
                </a>
            </div>
        </div>
    </div>

    <!-- My Statistics -->
    <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                <i class="fas fa-file-alt"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myLsStats['total'] ?? 0) + ($myAtStats['total'] ?? 0); ?></span>
                <span class="stat-label">Total Requests</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--warning-bg); color: #b45309;">
                <i class="fas fa-clock"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myLsStats['pending'] ?? 0) + ($myAtStats['pending'] ?? 0); ?></span>
                <span class="stat-label">Pending</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--success-bg); color: #047857;">
                <i class="fas fa-check-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myLsStats['approved'] ?? 0) + ($myAtStats['approved'] ?? 0); ?></span>
                <span class="stat-label">Approved</span>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: var(--danger-bg); color: #dc2626;">
                <i class="fas fa-times-circle"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myLsStats['rejected'] ?? 0) + ($myAtStats['rejected'] ?? 0); ?></span>
                <span class="stat-label">Rejected</span>
            </div>
        </div>
    </div>

    <!-- Recent Requests -->
    <div class="dashboard-content" style="grid-template-columns: 1fr 1fr;">
        <!-- Recent Locator Slips -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-map-marker-alt"></i> My Recent Locator Slips</h2>
                <a href="<?php echo navUrl('/my-requests.php?type=ls'); ?>" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentLS)): ?>
                <div class="empty-state small">
                    <span class="empty-icon"><i class="fas fa-file-alt"></i></span>
                    <h3>No requests yet</h3>
                    <p>File your first Locator Slip</p>
                </div>
                <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($recentLS as $ls): ?>
                    <div class="complaint-item">
                        <div class="complaint-info">
                            <span class="complaint-ref"><?php echo htmlspecialchars($ls['ls_control_no']); ?></span>
                            <span class="complaint-preview"><?php echo htmlspecialchars($ls['destination']); ?></span>
                        </div>
                        <div class="complaint-meta">
                            <?php echo getStatusBadge($ls['status']); ?>
                            <span class="complaint-date"><?php echo date('M j', strtotime($ls['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <!-- Recent Authority to Travel -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-plane"></i> My Recent Travel Requests</h2>
                <a href="<?php echo navUrl('/my-requests.php?type=at'); ?>" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($recentAT)): ?>
                <div class="empty-state small">
                    <span class="empty-icon"><i class="fas fa-plane"></i></span>
                    <h3>No requests yet</h3>
                    <p>File your first Authority to Travel</p>
                </div>
                <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($recentAT as $at): ?>
                    <div class="complaint-item">
                        <div class="complaint-info">
                            <span class="complaint-ref"><?php echo htmlspecialchars($at['at_tracking_no']); ?></span>
                            <span class="complaint-preview"><?php echo htmlspecialchars($at['destination']); ?></span>
                        </div>
                        <div class="complaint-meta">
                            <?php echo getStatusBadge($at['status']); ?>
                            <span class="complaint-date"><?php echo date('M j', strtotime($at['created_at'])); ?></span>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>

<?php else: ?>
<!-- ==================== ADMIN/APPROVER DASHBOARD ==================== -->
<div class="dashboard-grid">
    <!-- Stats Row -->
    <div class="stats-row">
        <div class="stat-card stat-total">
            <div class="stat-icon">
                <i class="fas fa-file-alt" style="color: white;"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myAtStats['total'] ?? 0); ?></span>
                <span class="stat-label"><?php echo $auth->isUnitHead() ? 'Unit Total' : 'Total Requests'; ?></span>
            </div>
        </div>
        
        <div class="stat-card stat-pending">
            <div class="stat-icon">
                <i class="fas fa-clock" style="color: #b45309;"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myAtStats['pending'] ?? 0); ?></span>
                <span class="stat-label"><?php echo $auth->isUnitHead() ? 'Unit Pending' : 'Pending'; ?></span>
            </div>
        </div>
        
        <div class="stat-card stat-accepted">
            <div class="stat-icon">
                <i class="fas fa-check-circle" style="color: #047857;"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myAtStats['approved'] ?? 0); ?></span>
                <span class="stat-label"><?php echo $auth->isUnitHead() ? 'Unit Approved' : 'Approved'; ?></span>
            </div>
        </div>
        
        <div class="stat-card stat-progress">
            <div class="stat-icon">
                <i class="fas fa-inbox" style="color: #7c3aed;"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $queueCount ?? 0; ?></span>
                <span class="stat-label">In My Queue</span>
            </div>
        </div>
        
        <div class="stat-card stat-resolved">
            <div class="stat-icon">
                <i class="fas fa-times-circle" style="color: #dc2626;"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo ($myAtStats['rejected'] ?? 0); ?></span>
                <span class="stat-label"><?php echo $auth->isUnitHead() ? 'Unit Rejected' : 'Rejected'; ?></span>
            </div>
        </div>
    </div>

    <!-- Pending Requests -->
    <div class="dashboard-content">
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-clock"></i> Pending Locator Slips</h2>
                <a href="<?php echo navUrl('/locator-slips.php?status=pending'); ?>" class="btn btn-sm btn-secondary">View All</a>
            </div>
            <div class="card-body">
                <?php if (empty($pendingLS)): ?>
                <div class="empty-state small">
                    <span class="empty-icon"><i class="fas fa-check-circle"></i></span>
                    <h3>All caught up!</h3>
                    <p>No pending Locator Slips</p>
                </div>
                <?php else: ?>
                <div class="complaints-list">
                    <?php foreach ($pendingLS as $ls): ?>
                    <a href="<?php echo navUrl('/locator-slips.php?view=' . $ls['id']); ?>" class="complaint-item">
                        <div class="complaint-info">
                            <span class="complaint-ref"><?php echo htmlspecialchars($ls['ls_control_no']); ?></span>
                            <span class="complaint-name"><?php echo htmlspecialchars($ls['employee_name']); ?></span>
                            <span class="complaint-preview"><?php echo htmlspecialchars($ls['destination']); ?></span>
                        </div>
                        <div class="complaint-meta">
                            <span class="complaint-unit"><?php echo htmlspecialchars($ls['travel_type']); ?></span>
                            <span class="complaint-date"><?php echo date('M j, g:i A', strtotime($ls['created_at'])); ?></span>
                        </div>
                    </a>
                    <?php endforeach; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
        
        <div class="dashboard-sidebar">
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-plane"></i> Pending Travel Requests</h2>
                </div>
                <div class="card-body">
                    <?php if (empty($pendingAT)): ?>
                    <div class="empty-state small">
                        <span class="empty-icon"><i class="fas fa-check-circle"></i></span>
                        <h3>All caught up!</h3>
                        <p>No pending AT requests</p>
                    </div>
                    <?php else: ?>
                    <div class="complaints-list">
                        <?php foreach ($pendingAT as $at): ?>
                        <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $at['id']); ?>" class="complaint-item">
                            <div class="complaint-info">
                                <span class="complaint-ref"><?php echo htmlspecialchars($at['at_tracking_no']); ?></span>
                                <span class="complaint-name"><?php echo htmlspecialchars($at['employee_name']); ?></span>
                                <span class="complaint-preview"><?php echo htmlspecialchars($at['destination']); ?></span>
                            </div>
                            <div class="complaint-meta">
                                <span class="complaint-unit"><?php echo AuthorityToTravel::getTypeLabel($at['travel_category'], $at['travel_scope']); ?></span>
                                <span class="complaint-date"><?php echo date('M j', strtotime($at['created_at'])); ?></span>
                            </div>
                        </a>
                        <?php endforeach; ?>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
            
            <!-- Quick Stats -->
            <div class="dashboard-card">
                <div class="card-header">
                    <h2><i class="fas fa-chart-bar"></i> My This Week</h2>
                </div>
                <div class="card-body">
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Locator Slips Filed</span>
                        <span class="quick-stat-value"><?php echo $myLsStats['this_week'] ?? 0; ?></span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Travel Requests Filed</span>
                        <span class="quick-stat-value"><?php echo $myAtStats['this_week'] ?? 0; ?></span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">Local Official AT</span>
                        <span class="quick-stat-value"><?php echo $myAtStats['local_official'] ?? 0; ?></span>
                    </div>
                    <div class="quick-stat-item">
                        <span class="quick-stat-label">National Official AT</span>
                        <span class="quick-stat-value"><?php echo $myAtStats['national_official'] ?? 0; ?></span>
                    </div>
                    <div class="quick-stat-item" style="border-bottom: none;">
                        <span class="quick-stat-label">Personal AT</span>
                        <span class="quick-stat-value"><?php echo $myAtStats['personal'] ?? 0; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
<?php endif; ?>

<style>
.request-type-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 8px 25px rgba(0, 0, 0, 0.2);
}
</style>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
