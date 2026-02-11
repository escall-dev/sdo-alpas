<?php
/**
 * Password Reset Management Page
 * SDO ATLAS - Superadmin only
 * 
 * View and manage forgot password rate limits per user.
 * Reset blocked users' request limits.
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

// Auth check before header (for redirect)
require_once __DIR__ . '/../includes/auth.php';
$authCheck = auth();
$authCheck->requireLogin();

if (!$authCheck->isSuperAdmin()) {
    header('Location: ' . ADMIN_URL . '/');
    exit;
}

require_once __DIR__ . '/../models/PasswordReset.php';
$resetModel = new PasswordReset();
$message = '';
$error = '';

// Handle POST actions before header output
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $targetUserId = intval($_POST['user_id'] ?? 0);

    if ($targetUserId > 0) {
        $actionMessages = [
            'reset_limit' => ['method' => 'adminResetLimit', 'log' => 'Superadmin reset forgot password rate limit', 'msg' => 'Password reset rate limit has been cleared.'],
            'reset_resend' => ['method' => 'adminResetResendLimit', 'log' => 'Superadmin reset OTP resend limit', 'msg' => 'OTP resend limit has been reset.'],
            'reset_otp_input' => ['method' => 'adminResetOTPInputAttempts', 'log' => 'Superadmin reset OTP input attempts', 'msg' => 'OTP input attempts have been reset.'],
            'reset_verification' => ['method' => 'adminResetVerificationCount', 'log' => 'Superadmin reset verification access count', 'msg' => 'Verification access count has been reset.'],
        ];

        if (isset($actionMessages[$action])) {
            $cfg = $actionMessages[$action];
            $resetModel->{$cfg['method']}($targetUserId);
            $authCheck->logActivity($action, 'user', $targetUserId, $cfg['log'] . ' for user ID ' . $targetUserId);
            $message = $cfg['msg'];
        }
    }
}

require_once __DIR__ . '/../includes/header.php';
// Get filters
$filters = [];
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}
if (isset($_GET['blocked']) && $_GET['blocked'] !== '') {
    $filters['blocked'] = $_GET['blocked'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$attempts = $resetModel->getAllAttempts($filters, $perPage, $offset);
$totalAttempts = $resetModel->getAttemptsCount($filters);
$totalPages = ceil($totalAttempts / $perPage);
$stats = $resetModel->getAttemptStats();
?>

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

<!-- Stats Cards -->
<div class="stats-row" style="grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); margin-bottom: 24px;">
    <?php
    $statCards = [
        ['icon' => 'fa-users', 'class' => 'stat-total', 'value' => (int) ($stats['total_users_with_attempts'] ?? 0), 'label' => 'Users with Requests'],
        ['icon' => 'fa-ban', 'class' => 'stat-pending', 'value' => (int) ($stats['blocked_users'] ?? 0), 'label' => 'Blocked Users'],
        ['icon' => 'fa-check-circle', 'class' => 'stat-resolved', 'value' => (int) ($stats['active_users'] ?? 0), 'label' => 'Active (Under Limit)'],
        ['icon' => 'fa-envelope', 'class' => 'stat-accepted', 'value' => (int) ($stats['total_attempts'] ?? 0), 'label' => 'Total OTP Requests'],
        ['icon' => 'fa-eye', 'class' => 'stat-progress', 'value' => (int) ($stats['total_verification_accesses'] ?? 0), 'label' => 'Page Accesses'],
        ['icon' => 'fa-redo', 'class' => 'stat-total', 'value' => (int) ($stats['total_resends'] ?? 0), 'label' => 'Total Resends'],
    ];
    foreach ($statCards as $sc): ?>
        <div class="stat-card <?php echo $sc['class']; ?>">
            <div class="stat-icon">
                <i class="fas <?php echo $sc['icon']; ?>"></i>
            </div>
            <div class="stat-content">
                <span class="stat-value"><?php echo $sc['value']; ?></span>
                <span class="stat-label"><?php echo $sc['label']; ?></span>
            </div>
        </div>
    <?php endforeach; ?>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <?php if (!empty($currentToken)): ?>
            <input type="hidden" name="token" value="<?php echo htmlspecialchars($currentToken); ?>">
        <?php endif; ?>
        <div class="filter-group" style="flex: 1; min-width: 200px;">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Search by name or email..."
                value="<?php echo htmlspecialchars($filters['search'] ?? ''); ?>">
        </div>
        <div class="filter-group">
            <label>Status</label>
            <select name="blocked" class="filter-select">
                <option value="">All Status</option>
                <option value="1" <?php echo (isset($filters['blocked']) && $filters['blocked'] === '1') ? 'selected' : ''; ?>>Blocked</option>
                <option value="0" <?php echo (isset($filters['blocked']) && $filters['blocked'] === '0') ? 'selected' : ''; ?>>Active</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-search"></i> Filter</button>
            <a href="<?php echo navUrl('/password-resets.php'); ?>" class="btn btn-secondary btn-sm"><i
                    class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Table -->
<div class="data-card">
    <div class="data-card-header"
        style="display: flex; justify-content: space-between; align-items: center; padding: 16px 20px;">
        <h3 style="margin: 0; font-size: 1rem; color: var(--text-primary);">
            <i class="fas fa-key" style="color: var(--primary); margin-right: 8px;"></i>
            Password Reset Rate Limits
        </h3>
        <span style="font-size: 0.82rem; color: var(--text-muted);">
            Showing <?php echo count($attempts); ?> of <?php echo $totalAttempts; ?> records
        </span>
    </div>

    <?php if (empty($attempts)): ?>
        <div class="empty-state">
            <span class="empty-icon"><i class="fas fa-shield-alt"></i></span>
            <h3>No password reset requests found</h3>
            <p>No users have requested a password reset yet.</p>
        </div>
    <?php else: ?>
        <div class="table-responsive">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>User</th>
                        <th>Role</th>
                        <th style="text-align: center;">Page Visits</th>
                        <th style="text-align: center;">OTP Requests</th>
                        <th style="text-align: center;">OTP Input</th>
                        <th style="text-align: center;">Resends</th>
                        <th style="text-align: center;">Status</th>
                        <th>Last Activity</th>
                        <th style="text-align: center;">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($attempts as $a): ?>
                        <tr>
                            <!-- User -->
                            <td>
                                <div class="cell-primary"><?php echo htmlspecialchars($a['full_name']); ?></div>
                                <div class="cell-secondary"><?php echo htmlspecialchars($a['email']); ?></div>
                            </td>
                            <!-- Role -->
                            <td><span class="cell-secondary"><?php echo htmlspecialchars($a['role_name']); ?></span></td>
                            <!-- Page Visits -->
                            <td style="text-align: center;">
                                <span
                                    style="font-weight: 600; font-size: 0.92rem; color: var(--text-primary);"><?php echo (int) $a['verification_access_count']; ?></span>
                            </td>
                            <!-- OTP Requests (attempt_count / 3) -->
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                    <span
                                        style="font-weight: 700; font-size: 0.92rem; color: <?php echo $a['attempt_count'] >= 3 ? 'var(--danger)' : ($a['attempt_count'] >= 2 ? 'var(--warning)' : 'var(--text-primary)'); ?>;"><?php echo (int) $a['attempt_count']; ?></span>
                                    <span class="cell-secondary">/ 3</span>
                                </div>
                                <div
                                    style="width: 50px; height: 3px; background: var(--bg-secondary); border-radius: 2px; margin: 3px auto 0; overflow: hidden;">
                                    <div
                                        style="height: 100%; width: <?php echo min(100, ($a['attempt_count'] / 3) * 100); ?>%; background: <?php echo $a['attempt_count'] >= 3 ? 'var(--danger)' : ($a['attempt_count'] >= 2 ? 'var(--warning)' : 'var(--success)'); ?>; border-radius: 2px;">
                                    </div>
                                </div>
                            </td>
                            <!-- OTP Input Attempts -->
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                    <span
                                        style="font-weight: 600; font-size: 0.92rem; color: <?php echo $a['otp_input_attempts'] >= 5 ? 'var(--danger)' : ($a['otp_input_attempts'] >= 4 ? 'var(--warning)' : 'var(--text-primary)'); ?>;"><?php echo (int) $a['otp_input_attempts']; ?></span>
                                    <span class="cell-secondary">/ 5</span>
                                </div>
                            </td>
                            <!-- Resends -->
                            <td style="text-align: center;">
                                <div style="display: flex; align-items: center; justify-content: center; gap: 3px;">
                                    <span
                                        style="font-weight: 600; font-size: 0.92rem; color: <?php echo $a['resend_blocked'] ? 'var(--danger)' : ($a['resend_count'] >= 2 ? 'var(--warning)' : 'var(--text-primary)'); ?>;"><?php echo (int) $a['resend_count']; ?></span>
                                    <span class="cell-secondary">/ 3</span>
                                </div>
                                <?php if ($a['resend_blocked']): ?>
                                    <div style="font-size: 0.65rem; color: var(--danger); margin-top: 2px;">blocked</div>
                                <?php elseif ($a['resend_window_start']):
                                    $wEnd = (new DateTime($a['resend_window_start']))->modify('+60 minutes');
                                    $now = new DateTime();
                                    if ($wEnd > $now):
                                        $diff = $now->diff($wEnd);
                                        ?>
                                        <div style="font-size: 0.65rem; color: var(--text-muted); margin-top: 2px;">
                                            <?php echo $diff->i; ?>m left</div>
                                    <?php endif; endif; ?>
                            </td>
                            <!-- Status -->
                            <td style="text-align: center;">
                                <?php if ($a['is_blocked']): ?>
                                    <span class="status-badge status-rejected">
                                        <i class="fas fa-lock" style="font-size: 0.6rem;"></i> Blocked
                                    </span>
                                <?php else: ?>
                                    <span class="status-badge status-approved">
                                        <i class="fas fa-unlock" style="font-size: 0.6rem;"></i> Active
                                    </span>
                                <?php endif; ?>
                            </td>
                            <!-- Last Activity -->
                            <td>
                                <div class="cell-secondary">
                                    <?php
                                    $lastAct = $a['last_attempt_at'] ?? $a['last_request_at'];
                                    if ($lastAct) {
                                        $d = new DateTime($lastAct);
                                        echo $d->format('M j, Y');
                                        echo '<br><span style="font-size: 0.7rem; opacity: 0.7;">' . $d->format('g:i A') . '</span>';
                                    } else {
                                        echo '—';
                                    }
                                    ?>
                                </div>
                            </td>
                            <!-- Actions -->
                            <td style="text-align: center;">
                                <div style="display: flex; flex-direction: column; gap: 4px; align-items: center;">
                                    <?php
                                    $userName = htmlspecialchars($a['full_name'], ENT_QUOTES);
                                    $uid = (int) $a['user_id'];
                                    $actions = [
                                        ['action' => 'reset_limit', 'icon' => 'fa-redo-alt', 'label' => 'OTP Limit', 'confirm' => "Reset the OTP request limit for {$userName}? This restores their ability to use Forgot Password."],
                                        ['action' => 'reset_otp_input', 'icon' => 'fa-keyboard', 'label' => 'Input Tries', 'confirm' => "Reset the OTP input attempts for {$userName}?"],
                                        ['action' => 'reset_resend', 'icon' => 'fa-envelope-open', 'label' => 'Resend Limit', 'confirm' => "Reset the OTP resend limit for {$userName}?"],
                                        ['action' => 'reset_verification', 'icon' => 'fa-eye-slash', 'label' => 'Page Visits', 'confirm' => "Reset the verification page access count for {$userName}?"],
                                    ];
                                    foreach ($actions as $act): ?>
                                        <button type="button" class="btn btn-outline btn-sm prm-action-btn"
                                            data-action="<?php echo $act['action']; ?>" data-user-id="<?php echo $uid; ?>"
                                            data-user-name="<?php echo $userName; ?>"
                                            data-confirm-msg="<?php echo htmlspecialchars($act['confirm'], ENT_QUOTES); ?>"
                                            style="width: 100%; font-size: 0.7rem; padding: 4px 10px;">
                                            <i class="fas <?php echo $act['icon']; ?>" style="font-size: 0.62rem;"></i>
                                            <?php echo $act['label']; ?>
                                        </button>
                                    <?php endforeach; ?>
                                </div>
                            </td>
                        </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>

        <!-- Pagination -->
        <?php if ($totalPages > 1): ?>
            <div class="pagination">
                <span class="pagination-info">Showing <?php echo count($attempts); ?> of <?php echo $totalAttempts; ?>
                    records</span>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo navUrl('/password-resets.php?' . http_build_query(array_merge($filters, ['page' => $page - 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>
                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?php echo navUrl('/password-resets.php?' . http_build_query(array_merge($filters, ['page' => $i]))); ?>"
                            class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>
                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo navUrl('/password-resets.php?' . http_build_query(array_merge($filters, ['page' => $page + 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>

    <?php endif; ?>
</div>

<!-- Confirmation Modal -->
<div class="modal-overlay" id="confirmModal">
    <div class="modal" style="max-width: 440px;">
        <div class="modal-header">
            <h3><i class="fas fa-shield-alt" style="color: var(--primary); margin-right: 8px;"></i> Confirm Action</h3>
            <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
        </div>
        <div class="modal-body" style="overflow-y: auto;">
            <p id="confirmMsg" style="color: var(--text-secondary); font-size: 0.92rem; line-height: 1.6;"></p>
        </div>
        <div class="modal-footer">
            <button class="btn btn-ghost" onclick="closeConfirmModal()"
                style="padding: 8px 20px; font-size: 0.88rem; border: 1px solid var(--border-color); background: transparent; color: var(--text-secondary); border-radius: 8px; cursor: pointer;">Cancel</button>
            <button class="btn btn-primary" id="confirmBtn"
                style="padding: 8px 20px; font-size: 0.88rem; border-radius: 8px; cursor: pointer;">Confirm</button>
        </div>
    </div>
</div>

<!-- Hidden form for submitting actions -->
<form id="actionForm" method="POST" action="" style="display: none;">
    <input type="hidden" name="_token" value="<?php echo htmlspecialchars($currentToken); ?>">
    <input type="hidden" name="action" id="actionField" value="">
    <input type="hidden" name="user_id" id="userIdField" value="">
</form>

<!-- Success notification modal -->
<div class="modal-overlay" id="successModal">
    <div class="modal" style="max-width: 400px;">
        <div class="modal-body" style="text-align: center; padding: 40px 24px; overflow-y: auto;">
            <div
                style="width: 64px; height: 64px; border-radius: 50%; background: rgba(16,185,129,0.15); display: flex; align-items: center; justify-content: center; margin: 0 auto 16px;">
                <i class="fas fa-check-circle" style="font-size: 2rem; color: #10b981;"></i>
            </div>
            <h3 style="color: var(--text-primary); font-size: 1.1rem; margin-bottom: 8px;">Limit Reset Successful</h3>
            <p id="successMsg" style="color: var(--text-secondary); font-size: 0.88rem; line-height: 1.5;"></p>
        </div>
        <div class="modal-footer" style="justify-content: center;">
            <button class="btn btn-primary" onclick="closeSuccessModal()"
                style="padding: 8px 28px; font-size: 0.88rem; border-radius: 8px; cursor: pointer;">OK</button>
        </div>
    </div>
</div>

<script>
    // Confirmation modal logic — replaces browser confirm()
    let pendingAction = null;

    document.querySelectorAll('.prm-action-btn').forEach(btn => {
        btn.addEventListener('click', function () {
            const action = this.dataset.action;
            const userId = this.dataset.userId;
            const confirmMsg = this.dataset.confirmMsg;

            pendingAction = { action, userId };
            document.getElementById('confirmMsg').textContent = confirmMsg;
            document.getElementById('confirmModal').classList.add('active');
        });
    });

    document.getElementById('confirmBtn').addEventListener('click', function () {
        if (!pendingAction) return;
        document.getElementById('actionField').value = pendingAction.action;
        document.getElementById('userIdField').value = pendingAction.userId;
        closeConfirmModal();
        document.getElementById('actionForm').submit();
    });

    function closeConfirmModal() {
        document.getElementById('confirmModal').classList.remove('active');
        pendingAction = null;
    }

    // Close modal on overlay click
    document.getElementById('confirmModal').addEventListener('click', function (e) {
        if (e.target === this) closeConfirmModal();
    });

    // Success modal (shown for POST message if present)
    <?php if ($message): ?>
        document.addEventListener('DOMContentLoaded', function () {
            document.getElementById('successMsg').textContent = <?php echo json_encode($message); ?>;
            document.getElementById('successModal').classList.add('active');
        });
    <?php endif; ?>

    function closeSuccessModal() {
        document.getElementById('successModal').classList.remove('active');
    }

    document.getElementById('successModal').addEventListener('click', function (e) {
        if (e.target === this) closeSuccessModal();
    });

    // Keyboard: Escape closes modals
    document.addEventListener('keydown', function (e) {
        if (e.key === 'Escape') {
            closeConfirmModal();
            closeSuccessModal();
        }
    });
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>