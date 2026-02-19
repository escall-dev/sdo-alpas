<?php
/**
 * My Requests Page - View of user's own LS, AT, and PS requests
 * SDO ALPAS - Available for all roles to track their submitted requests
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';
require_once __DIR__ . '/../models/PassSlip.php';

$userId = $auth->getUserId();
$lsModel = new LocatorSlip();
$atModel = new AuthorityToTravel();
$psModelReq = new PassSlip();

// Get filter parameters
$type = $_GET['type'] ?? 'all'; // all, ls, at, ps
$status = $_GET['status'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;

// Build filters
$filters = ['user_id' => $userId];
if ($status) {
    $filters['status'] = $status;
}

// Get data based on type
$requests = [];
$totalRequests = 0;

if ($type === 'ls' || $type === 'all') {
    $lsRequests = $lsModel->getAll($filters, $type === 'ls' ? $perPage : 100, 0);
    foreach ($lsRequests as $ls) {
        $ls['request_type'] = 'ls';
        $ls['tracking_no'] = $ls['ls_control_no'];
        $ls['type_label'] = 'Locator Slip';
        $requests[] = $ls;
    }
}

if ($type === 'at' || $type === 'all') {
    $atRequests = $atModel->getAll($filters, $type === 'at' ? $perPage : 100, 0);
    foreach ($atRequests as $at) {
        $at['request_type'] = 'at';
        $at['tracking_no'] = $at['at_tracking_no'];
        $at['type_label'] = AuthorityToTravel::getTypeLabel($at['travel_category'], $at['travel_scope'], $at['travel_type'] ?? null);
        $requests[] = $at;
    }
}

if ($type === 'ps' || $type === 'all') {
    $psFilters = ['user_id' => $userId];
    if ($status)
        $psFilters['status'] = $status;
    $psRequests = $psModelReq->getAll($psFilters, $type === 'ps' ? $perPage : 100, 0, null, null);
    foreach ($psRequests as $ps) {
        $ps['request_type'] = 'ps';
        $ps['tracking_no'] = $ps['ps_control_no'];
        $ps['type_label'] = 'Pass Slip';
        $requests[] = $ps;
    }
}

// Sort by created_at descending
usort($requests, function ($a, $b) {
    return strtotime($b['created_at']) - strtotime($a['created_at']);
});

// Paginate if showing all
if ($type === 'all') {
    $totalRequests = count($requests);
    $requests = array_slice($requests, ($page - 1) * $perPage, $perPage);
} elseif ($type === 'ps') {
    $psFiltersCount = ['user_id' => $userId];
    if ($status)
        $psFiltersCount['status'] = $status;
    $totalRequests = $psModelReq->getCount($psFiltersCount);
} else {
    $totalRequests = $type === 'ls' ? $lsModel->getCount($filters) : $atModel->getCount($filters);
}

$totalPages = ceil($totalRequests / $perPage);
?>

<div class="page-header">
    <div>
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--text-secondary);">
            Showing <?php echo count($requests); ?> of <?php echo $totalRequests; ?> requests
        </h2>
    </div>
    <div style="display: flex; gap: 10px;">
        <a href="<?php echo navUrl('/locator-slips.php?action=new'); ?>" class="btn btn-primary">
            <i class="fas fa-plus"></i> New Locator Slip
        </a>
        <a href="<?php echo navUrl('/authority-to-travel.php?action=new'); ?>" class="btn btn-secondary">
            <i class="fas fa-plus"></i> New Travel Request
        </a>
        <a href="<?php echo navUrl('/pass-slips.php?action=new'); ?>" class="btn btn-secondary">
            <i class="fas fa-plus"></i> New Pass Slip
        </a>
    </div>
</div>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="token" value="<?php echo $currentToken; ?>">

        <div class="filter-group">
            <label>Request Type</label>
            <select name="type" class="filter-select">
                <option value="all" <?php echo $type === 'all' ? 'selected' : ''; ?>>All Types</option>
                <option value="ls" <?php echo $type === 'ls' ? 'selected' : ''; ?>>Locator Slips</option>
                <option value="at" <?php echo $type === 'at' ? 'selected' : ''; ?>>Authority to Travel</option>
                <option value="ps" <?php echo $type === 'ps' ? 'selected' : ''; ?>>Pass Slips</option>
            </select>
        </div>

        <div class="filter-group">
            <label>Status</label>
            <select name="status" class="filter-select">
                <option value="">All Status</option>
                <option value="pending" <?php echo $status === 'pending' ? 'selected' : ''; ?>>Pending</option>
                <option value="approved" <?php echo $status === 'approved' ? 'selected' : ''; ?>>Approved</option>
                <option value="rejected" <?php echo $status === 'rejected' ? 'selected' : ''; ?>>Rejected</option>
                <option value="cancelled" <?php echo $status === 'cancelled' ? 'selected' : ''; ?>>Cancelled</option>
            </select>
        </div>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="<?php echo navUrl('/my-requests.php'); ?>" class="btn btn-secondary btn-sm">
                <i class="fas fa-times"></i> Clear
            </a>
        </div>
    </form>
</div>

<!-- Requests Table -->
<div class="data-card">
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Tracking No.</th>
                    <th>Type</th>
                    <th>Destination</th>
                    <th>Date Filed</th>
                    <th>Status</th>
                    <th>Approver</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($requests)): ?>
                    <tr>
                        <td colspan="7">
                            <div class="empty-state">
                                <span class="empty-icon"><i class="fas fa-file-alt"></i></span>
                                <h3>No requests found</h3>
                                <p>File your first request to get started</p>
                            </div>
                        </td>
                    </tr>
                <?php else: ?>
                    <?php foreach ($requests as $request): ?>
                        <tr>
                            <td>
                                <span class="ref-link"><?php echo htmlspecialchars($request['tracking_no']); ?></span>
                            </td>
                            <td>
                                <span class="unit-badge"><?php echo htmlspecialchars($request['type_label']); ?></span>
                            </td>
                            <td>
                                <div class="cell-primary"><?php echo htmlspecialchars($request['destination']); ?></div>
                                <?php if ($request['request_type'] === 'at' && !empty($request['purpose_of_travel'])): ?>
                                    <div class="cell-secondary">
                                        <?php echo htmlspecialchars(substr($request['purpose_of_travel'], 0, 50)); ?>...</div>
                                <?php elseif ($request['request_type'] === 'ps' && !empty($request['purpose'])): ?>
                                    <div class="cell-secondary">
                                        <?php echo htmlspecialchars(substr($request['purpose'], 0, 50)); ?>...</div>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="cell-primary"><?php echo date('M j, Y', strtotime($request['created_at'])); ?></div>
                                <div class="cell-secondary"><?php echo date('g:i A', strtotime($request['created_at'])); ?>
                                </div>
                            </td>
                            <td>
                                <?php echo getStatusBadge($request['status']); ?>
                            </td>
                            <td>
                                <?php if ($request['status'] === 'approved'): ?>
                                    <div class="cell-primary">
                                        <?php echo htmlspecialchars($request['approver_name'] ?? $request['approving_authority_name'] ?? '-'); ?>
                                    </div>
                                    <div class="cell-secondary">
                                        <?php echo $request['approval_date'] ? date('M j, Y', strtotime($request['approval_date'])) : ''; ?>
                                    </div>
                                <?php elseif ($request['status'] === 'rejected'): ?>
                                    <div class="cell-secondary" style="color: var(--danger);">
                                        <?php echo htmlspecialchars($request['rejection_reason'] ?? 'No reason provided'); ?>
                                    </div>
                                <?php else: ?>
                                    <span class="cell-secondary">Awaiting approval</span>
                                <?php endif; ?>
                            </td>
                            <td>
                                <div class="action-buttons">
                                    <?php
                                    if ($request['request_type'] === 'ls') {
                                        $viewUrl = navUrl('/locator-slips.php?view=' . $request['id']);
                                    } elseif ($request['request_type'] === 'ps') {
                                        $viewUrl = navUrl('/pass-slips.php?view=' . $request['id']);
                                    } else {
                                        $viewUrl = navUrl('/authority-to-travel.php?view=' . $request['id']);
                                    }
                                    ?>
                                    <a href="<?php echo $viewUrl; ?>" class="btn btn-icon" title="View Details">
                                        <i class="fas fa-eye"></i>
                                    </a>

                                    <?php if ($request['status'] === 'approved'): ?>
                                        <?php
                                        if ($request['request_type'] === 'ls') {
                                            $downloadUrl = navUrl('/api/generate-docx.php?type=ls&id=' . $request['id']);
                                        } elseif ($request['request_type'] === 'ps') {
                                            $downloadUrl = navUrl('/api/generate-docx.php?type=ps&id=' . $request['id']);
                                        } else {
                                            $downloadUrl = navUrl('/api/generate-docx.php?type=at&id=' . $request['id']);
                                        }
                                        ?>
                                        <a href="<?php echo $downloadUrl; ?>" class="btn btn-icon" title="Download PDF"
                                            style="color: var(--success);">
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
            <div class="pagination-info">
                Page <?php echo $page; ?> of <?php echo $totalPages; ?>
            </div>
            <div class="pagination-links">
                <?php if ($page > 1): ?>
                    <a href="<?php echo navUrl('/my-requests.php?type=' . $type . '&status=' . $status . '&page=' . ($page - 1)); ?>"
                        class="page-link">
                        <i class="fas fa-chevron-left"></i>
                    </a>
                <?php endif; ?>

                <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                    <a href="<?php echo navUrl('/my-requests.php?type=' . $type . '&status=' . $status . '&page=' . $i); ?>"
                        class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                        <?php echo $i; ?>
                    </a>
                <?php endfor; ?>

                <?php if ($page < $totalPages): ?>
                    <a href="<?php echo navUrl('/my-requests.php?type=' . $type . '&status=' . $status . '&page=' . ($page + 1)); ?>"
                        class="page-link">
                        <i class="fas fa-chevron-right"></i>
                    </a>
                <?php endif; ?>
            </div>
        </div>
    <?php endif; ?>
</div>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>