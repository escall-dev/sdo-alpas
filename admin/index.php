<?php
/**
 * Admin Dashboard
 * SDO ALPAS - Role-aware dashboard for admins and employees
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';
require_once __DIR__ . '/../models/PassSlip.php';

$lsModel = new LocatorSlip();
$atModel = new AuthorityToTravel();
$psModelDash = new PassSlip();

// Get current user's ID for personal stats
$userId = $auth->getUserId();
// Use effective role ID/Name which accounts for OIC delegation
$currentRoleId = $auth->getEffectiveRoleId();
$currentRoleName = $auth->getEffectiveRoleName();
$currentRoleDisplayName = $auth->getEffectiveRoleDisplayName();
$isActingAsOIC = $auth->isActingAsOIC();

// Get statistics based on role
if ($auth->isGuard()) {
    // Guard sees pass slip stats for today
    $guardTodayStats = $psModelDash->getGuardTodayStats();
    $guardTodaySlips = $psModelDash->getForGuardDashboard(['date' => date('Y-m-d'), 'status' => 'approved'], 10, 0);
} elseif ($auth->isEmployee()) {
    // Employee sees only their own stats
    $myLsStats = $lsModel->getStatistics($userId);
    $myAtStats = $atModel->getMyStatistics($userId);
    $myPsStats = $psModelDash->getStatistics($userId);
    $recentLS = $lsModel->getRecent(5, $userId);
    $recentAT = $atModel->getRecent(5, $userId);
} elseif ($auth->isUnitHead()) {
    // Unit heads (or OICs acting as unit heads) see stats about requests FROM THEIR UNIT
    $myLsStats = $lsModel->getStatistics($userId); // Their own LS if any
    $myAtStats = $atModel->getUnitStatistics($currentRoleId); // Stats from their supervised offices
    $myPsStats = $psModelDash->getVisibleStatistics($currentRoleId, $userId);
    $pendingLS = $lsModel->getPending(5);
    $pendingAT = $atModel->getPending(5, $currentRoleId, $currentRoleName);
    $pendingPS = $psModelDash->getPendingForApprover($currentRoleId, $userId, 5);
    $queueCount = $atModel->getPendingCountForRole($currentRoleName, $currentRoleId);
} else {
    // ASDS/Superadmin see all stats
    $myLsStats = $lsModel->getStatistics();
    $myAtStats = $atModel->getStatistics();
    $myPsStats = $psModelDash->getStatistics();
    $pendingLS = $lsModel->getPending(5);
    $pendingAT = $atModel->getPending(5, $currentRoleId, $currentRoleName);
    $pendingPS = $psModelDash->getPendingForApprover($currentRoleId, $userId, 5);
    $queueCount = ($lsModel->getStatistics()['pending'] ?? 0) + ($atModel->getPendingCountForRole($currentRoleName, $currentRoleId));
}
?>

<?php if ($isActingAsOIC): ?>
    <!-- OIC Notice Banner -->
    <div class="alert"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; margin-bottom: 20px;">
        <i class="fas fa-user-shield"></i>
        <strong>Acting as OIC:</strong> You are currently serving as Officer-In-Charge
        (<?php echo htmlspecialchars($currentRoleDisplayName); ?>).
        You can process requests on behalf of the unit head.
    </div>
<?php endif; ?>

<?php if ($auth->isGuard()): ?>
    <!-- ==================== GUARD DASHBOARD ==================== -->
    <div class="dashboard-grid">
        <!-- Today's Summary Stats -->
        <div class="stats-row" style="grid-template-columns: repeat(4, 1fr);">
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success-bg); color: #047857;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $guardTodayStats['approved'] ?? 0; ?></span>
                    <span class="stat-label">Approved Today</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: linear-gradient(135deg, #667eea, #764ba2); color: white;">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $guardTodayStats['departed'] ?? 0; ?></span>
                    <span class="stat-label">Departed</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: #d1fae5; color: #047857;">
                    <i class="fas fa-sign-in-alt"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $guardTodayStats['returned'] ?? 0; ?></span>
                    <span class="stat-label">Returned</span>
                </div>
            </div>
            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning-bg); color: #b45309;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span class="stat-value"><?php echo $guardTodayStats['pending'] ?? 0; ?></span>
                    <span class="stat-label">Pending</span>
                </div>
            </div>
        </div>

        <!-- Quick Action -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-shield-alt"></i> Today's Approved Pass Slips</h2>
                <a href="<?php echo navUrl('/pass-slips.php'); ?>" class="btn btn-sm btn-primary">
                    <i class="fas fa-list"></i> View All Pass Slips
                </a>
            </div>
            <div class="card-body">
                <?php if (empty($guardTodaySlips)): ?>
                    <div class="empty-state small">
                        <span class="empty-icon"><i class="fas fa-shield-alt"></i></span>
                        <h3>No approved pass slips today</h3>
                        <p>Approved pass slips will appear here for gate monitoring</p>
                    </div>
                <?php else: ?>
                    <div class="table-responsive">
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Employee</th>
                                    <th>Destination</th>
                                    <th>IDT / IAT</th>
                                    <th>Departure</th>
                                    <th>Arrival</th>
                                    <th>Action</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($guardTodaySlips as $gps): ?>
                                    <tr>
                                        <td>
                                            <div class="cell-primary"><?php echo htmlspecialchars($gps['employee_name']); ?></div>
                                            <div class="cell-secondary"><?php echo htmlspecialchars($gps['employee_office'] ?? ''); ?></div>
                                        </td>
                                        <td><?php echo htmlspecialchars($gps['destination']); ?></td>
                                        <td>
                                            <div class="cell-primary"><?php echo date('g:i A', strtotime($gps['idt'])); ?></div>
                                            <div class="cell-secondary"><?php echo date('g:i A', strtotime($gps['iat'])); ?></div>
                                        </td>
                                        <td>
                                            <?php if (!empty($gps['actual_departure_time'])): ?>
                                                <span class="guard-badge guard-badge-departed"><?php echo date('g:i A', strtotime($gps['actual_departure_time'])); ?></span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (!empty($gps['actual_arrival_time'])): ?>
                                                <span class="guard-badge guard-badge-arrived"><?php echo date('g:i A', strtotime($gps['actual_arrival_time'])); ?></span>
                                            <?php elseif (!empty($gps['actual_departure_time'])): ?>
                                                <span style="color: var(--warning);">Out</span>
                                            <?php else: ?>
                                                <span style="color: var(--text-muted);">—</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <?php if (empty($gps['actual_departure_time'])): ?>
                                                <form method="POST" action="<?php echo navUrl('/pass-slips.php'); ?>" style="display:inline;">
                                                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                                    <input type="hidden" name="action" value="guard_depart">
                                                    <input type="hidden" name="id" value="<?php echo $gps['id']; ?>">
                                                    <button type="submit" class="btn btn-guard-depart btn-sm" data-confirm="Mark departed: <?php echo htmlspecialchars($gps['employee_name']); ?>?">
                                                        <i class="fas fa-sign-out-alt"></i>
                                                    </button>
                                                </form>
                                            <?php elseif (empty($gps['actual_arrival_time'])): ?>
                                                <form method="POST" action="<?php echo navUrl('/pass-slips.php'); ?>" style="display:inline;">
                                                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                                    <input type="hidden" name="action" value="guard_arrive">
                                                    <input type="hidden" name="id" value="<?php echo $gps['id']; ?>">
                                                    <button type="submit" class="btn btn-guard-arrive btn-sm" data-confirm="Mark arrived: <?php echo htmlspecialchars($gps['employee_name']); ?>?">
                                                        <i class="fas fa-sign-in-alt"></i>
                                                    </button>
                                                </form>
                                            <?php else: ?>
                                                <span style="color: var(--success);"><i class="fas fa-check-double"></i></span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

<?php elseif ($auth->isEmployee()): ?>
    <!-- ==================== EMPLOYEE DASHBOARD ==================== -->
    <div class="dashboard-grid">
        <!-- File New Request Section -->
        <div class="dashboard-card">
            <div class="card-header">
                <h2><i class="fas fa-plus-circle"></i> File New Request</h2>
            </div>
            <div class="card-body">
                <div style="display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 16px;">
                    <!-- Locator Slip -->
                    <a href="<?php echo navUrl('/locator-slips.php?action=new'); ?>" class="request-type-card"
                        style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="fas fa-map-marker-alt" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                        <span style="font-weight: 600; font-size: 1rem;">Locator Slip</span>
                        <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">For local movement</span>
                    </a>

                    <!-- Authority to Travel (single card: Local/International and Official/Personal chosen on form) -->
                    <a href="<?php echo navUrl('/authority-to-travel.php?action=new'); ?>" class="request-type-card"
                        style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #11998e 0%, #38ef7d 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="fas fa-plane" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                        <span style="font-weight: 600; font-size: 1rem;">Authority to Travel</span>
                        <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">Within/Outside Region or International</span>
                    </a>

                    <!-- Pass Slip -->
                    <a href="<?php echo navUrl('/pass-slips.php?action=new'); ?>" class="request-type-card"
                        style="display: flex; flex-direction: column; align-items: center; padding: 24px; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); border-radius: 12px; text-decoration: none; color: white; transition: transform 0.2s, box-shadow 0.2s;">
                        <i class="fas fa-ticket-alt" style="font-size: 2.5rem; margin-bottom: 12px;"></i>
                        <span style="font-weight: 600; font-size: 1rem;">Pass Slip</span>
                        <span style="font-size: 0.8rem; opacity: 0.8; margin-top: 4px;">For short-term travel</span>
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
                    <span
                        class="stat-value"><?php echo ($myLsStats['total'] ?? 0) + ($myAtStats['total'] ?? 0) + ($myPsStats['total'] ?? 0); ?></span>
                    <span class="stat-label">Total Requests</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: var(--warning-bg); color: #b45309;">
                    <i class="fas fa-clock"></i>
                </div>
                <div class="stat-content">
                    <span
                        class="stat-value"><?php echo ($myLsStats['pending'] ?? 0) + ($myAtStats['pending'] ?? 0) + ($myPsStats['pending'] ?? 0); ?></span>
                    <span class="stat-label">Pending</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: var(--success-bg); color: #047857;">
                    <i class="fas fa-check-circle"></i>
                </div>
                <div class="stat-content">
                    <span
                        class="stat-value"><?php echo ($myLsStats['approved'] ?? 0) + ($myAtStats['approved'] ?? 0) + ($myPsStats['approved'] ?? 0); ?></span>
                    <span class="stat-label">Approved</span>
                </div>
            </div>

            <div class="stat-card">
                <div class="stat-icon" style="background: var(--danger-bg); color: #dc2626;">
                    <i class="fas fa-times-circle"></i>
                </div>
                <div class="stat-content">
                    <span
                        class="stat-value"><?php echo ($myLsStats['rejected'] ?? 0) + ($myAtStats['rejected'] ?? 0) + ($myPsStats['rejected'] ?? 0); ?></span>
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
                    <a href="<?php echo navUrl('/my-requests.php?type=ls'); ?>" class="btn btn-sm btn-secondary">View
                        All</a>
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
                    <a href="<?php echo navUrl('/my-requests.php?type=at'); ?>" class="btn btn-sm btn-secondary">View
                        All</a>
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
                    <a href="<?php echo navUrl('/locator-slips.php?status=pending'); ?>"
                        class="btn btn-sm btn-secondary">View All</a>
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
                                        <span
                                            class="complaint-date"><?php echo date('M j, g:i A', strtotime($ls['created_at'])); ?></span>
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
                                    <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $at['id']); ?>"
                                        class="complaint-item">
                                        <div class="complaint-info">
                                            <span
                                                class="complaint-ref"><?php echo htmlspecialchars($at['at_tracking_no']); ?></span>
                                            <span
                                                class="complaint-name"><?php echo htmlspecialchars($at['employee_name']); ?></span>
                                            <span
                                                class="complaint-preview"><?php echo htmlspecialchars($at['destination']); ?></span>
                                        </div>
                                        <div class="complaint-meta">
                                            <span
                                                class="complaint-unit"><?php echo AuthorityToTravel::getTypeLabel($at['travel_category'], $at['travel_scope'], $at['travel_type'] ?? null); ?></span>
                                            <span
                                                class="complaint-date"><?php echo date('M j', strtotime($at['created_at'])); ?></span>
                                        </div>
                                    </a>
                                <?php endforeach; ?>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- Pending Pass Slips -->
                <div class="dashboard-card">
                    <div class="card-header">
                        <h2><i class="fas fa-ticket-alt"></i> Pending Pass Slips</h2>
                        <a href="<?php echo navUrl('/pass-slips.php?status=pending'); ?>"
                            class="btn btn-sm btn-secondary">View All</a>
                    </div>
                    <div class="card-body">
                        <?php if (empty($pendingPS)): ?>
                            <div class="empty-state small">
                                <span class="empty-icon"><i class="fas fa-check-circle"></i></span>
                                <h3>All caught up!</h3>
                                <p>No pending Pass Slips</p>
                            </div>
                        <?php else: ?>
                            <div class="complaints-list">
                                <?php foreach ($pendingPS as $ps): ?>
                                    <a href="<?php echo navUrl('/pass-slips.php?view=' . $ps['id']); ?>" class="complaint-item">
                                        <div class="complaint-info">
                                            <span class="complaint-ref"><?php echo htmlspecialchars($ps['ps_control_no']); ?></span>
                                            <span
                                                class="complaint-name"><?php echo htmlspecialchars($ps['employee_name']); ?></span>
                                            <span
                                                class="complaint-preview"><?php echo htmlspecialchars($ps['destination']); ?></span>
                                        </div>
                                        <div class="complaint-meta">
                                            <span class="complaint-unit">Pass Slip</span>
                                            <span
                                                class="complaint-date"><?php echo date('M j, g:i A', strtotime($ps['created_at'])); ?></span>
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
                            <span class="quick-stat-label">Within Region AT</span>
                            <span class="quick-stat-value"><?php echo $myAtStats['within_region_official'] ?? 0; ?></span>
                        </div>
                        <div class="quick-stat-item">
                            <span class="quick-stat-label">Outside Region AT</span>
                            <span class="quick-stat-value"><?php echo $myAtStats['outside_region_official'] ?? 0; ?></span>
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