<?php
/**
 * Activity Logs Page
 * SDO ALPAS - View system activity logs
 */

require_once __DIR__ . '/../includes/header.php';

// Approvers only
if (!$auth->isApprover()) {
    header('Location: ' . navUrl('/'));
    exit;
}

require_once __DIR__ . '/../models/ActivityLog.php';

$logModel = new ActivityLog();

// Get filters
$filters = [];
if (!empty($_GET['action_type'])) {
    $filters['action_type'] = $_GET['action_type'];
}
if (!empty($_GET['entity_type'])) {
    $filters['entity_type'] = $_GET['entity_type'];
}
if (!empty($_GET['date_from'])) {
    $filters['date_from'] = $_GET['date_from'];
}
if (!empty($_GET['date_to'])) {
    $filters['date_to'] = $_GET['date_to'];
}
if (!empty($_GET['search'])) {
    $filters['search'] = $_GET['search'];
}

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$logs = $logModel->getLogs($filters, $perPage, $offset);
$totalLogs = $logModel->getLogsCount($filters);
$totalPages = ceil($totalLogs / $perPage);
?>

<div class="page-header">
    <div class="result-count"><?php echo $totalLogs; ?> Log Entr<?php echo $totalLogs !== 1 ? 'ies' : 'y'; ?></div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="token" value="<?php echo $currentToken; ?>">
        
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Description, user..."
                   value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Action</label>
            <select name="action_type" class="filter-select">
                <option value="">All Actions</option>
                <option value="login" <?php echo ($_GET['action_type'] ?? '') === 'login' ? 'selected' : ''; ?>>Login</option>
                <option value="logout" <?php echo ($_GET['action_type'] ?? '') === 'logout' ? 'selected' : ''; ?>>Logout</option>
                <option value="create" <?php echo ($_GET['action_type'] ?? '') === 'create' ? 'selected' : ''; ?>>Create</option>
                <option value="approve" <?php echo ($_GET['action_type'] ?? '') === 'approve' ? 'selected' : ''; ?>>Approve</option>
                <option value="reject" <?php echo ($_GET['action_type'] ?? '') === 'reject' ? 'selected' : ''; ?>>Reject</option>
                <option value="download" <?php echo ($_GET['action_type'] ?? '') === 'download' ? 'selected' : ''; ?>>Download</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Entity Type</label>
            <select name="entity_type" class="filter-select">
                <option value="">All Types</option>
                <option value="locator_slip" <?php echo ($_GET['entity_type'] ?? '') === 'locator_slip' ? 'selected' : ''; ?>>Locator Slip</option>
                <option value="authority_to_travel" <?php echo ($_GET['entity_type'] ?? '') === 'authority_to_travel' ? 'selected' : ''; ?>>Authority to Travel</option>
                <option value="auth" <?php echo ($_GET['entity_type'] ?? '') === 'auth' ? 'selected' : ''; ?>>Authentication</option>
                <option value="user" <?php echo ($_GET['entity_type'] ?? '') === 'user' ? 'selected' : ''; ?>>User</option>
            </select>
        </div>
        
        <div class="filter-group">
            <label>Date From</label>
            <input type="date" name="date_from" class="filter-input" value="<?php echo htmlspecialchars($_GET['date_from'] ?? ''); ?>">
        </div>
        
        <div class="filter-group">
            <label>Date To</label>
            <input type="date" name="date_to" class="filter-input" value="<?php echo htmlspecialchars($_GET['date_to'] ?? ''); ?>">
        </div>
        
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo navUrl('/logs.php'); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Logs Table -->
<div class="data-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Timestamp</th>
                    <th>User</th>
                    <th>Action</th>
                    <th>Entity</th>
                    <th>Description</th>
                    <th>IP Address</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($logs)): ?>
                <tr>
                    <td colspan="6">
                        <div class="empty-state">
                            <span class="empty-icon"><i class="fas fa-history"></i></span>
                            <h3>No logs found</h3>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($logs as $log): ?>
                <tr>
                    <td>
                        <div class="cell-primary"><?php echo date('M j, Y', strtotime($log['created_at'])); ?></div>
                        <div class="cell-secondary"><?php echo date('g:i:s A', strtotime($log['created_at'])); ?></div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($log['user_name'] ?? 'System'); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($log['user_email'] ?? ''); ?></div>
                    </td>
                    <td>
                        <span class="action-badge action-<?php echo htmlspecialchars($log['action_type']); ?>">
                            <?php echo ucfirst(str_replace('_', ' ', $log['action_type'])); ?>
                        </span>
                    </td>
                    <td>
                        <?php if ($log['entity_type']): ?>
                        <span class="entity-type"><?php echo ucfirst(str_replace('_', ' ', $log['entity_type'])); ?></span>
                        <?php if ($log['entity_id']): ?>
                        <span class="entity-id">#<?php echo $log['entity_id']; ?></span>
                        <?php endif; ?>
                        <?php else: ?>
                        <span class="cell-secondary">-</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="log-description"><?php echo htmlspecialchars($log['description'] ?? '-'); ?></div>
                    </td>
                    <td>
                        <span class="ip-address"><?php echo htmlspecialchars($log['ip_address'] ?? '-'); ?></span>
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
            <a href="<?php echo navUrl('/logs.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>" class="page-link">
                <i class="fas fa-chevron-left"></i>
            </a>
            <?php endif; ?>
            
            <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
            <a href="<?php echo navUrl('/logs.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>" 
               class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
            <?php endfor; ?>
            
            <?php if ($page < $totalPages): ?>
            <a href="<?php echo navUrl('/logs.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>" class="page-link">
                <i class="fas fa-chevron-right"></i>
            </a>
            <?php endif; ?>
        </div>
    </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
