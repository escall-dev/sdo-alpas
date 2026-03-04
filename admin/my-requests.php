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
$search = $_GET['search'] ?? '';
$dateFrom = $_GET['date_from'] ?? '';
$dateTo = $_GET['date_to'] ?? '';
$approvalDateFrom = $_GET['approval_date_from'] ?? '';
$approvalDateTo = $_GET['approval_date_to'] ?? '';

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;

// Build filters for LS and AT
$filters = ['user_id' => $userId];
if ($status) {
    $filters['status'] = $status;
}
if ($search) {
	$filters['search'] = $search;
}
if ($dateFrom) {
	$filters['date_from'] = $dateFrom;
}
if ($dateTo) {
	$filters['date_to'] = $dateTo;
}
if ($approvalDateFrom) {
	$filters['approval_date_from'] = $approvalDateFrom;
}
if ($approvalDateTo) {
	$filters['approval_date_to'] = $approvalDateTo;
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
    if ($search)
        $psFilters['search'] = $search;
    if ($dateFrom)
        $psFilters['date_from'] = $dateFrom;
    if ($dateTo)
        $psFilters['date_to'] = $dateTo;
    if ($approvalDateFrom)
        $psFilters['approval_date_from'] = $approvalDateFrom;
    if ($approvalDateTo)
        $psFilters['approval_date_to'] = $approvalDateTo;
    if (!empty($_GET['unit']) && ($auth->isApprover() || $auth->isUnitHead())) {
        $psFilters['unit'] = $_GET['unit'];
    }

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
    $totalRequests = $psModelReq->getCount($psFilters);
} else {
    $totalRequests = $type === 'ls' ? $lsModel->getCount($filters) : $atModel->getCount($filters);
}

$totalPages = ceil($totalRequests / $perPage);
?>

<!-- Request Type Tabs -->
<div class="request-tabs">
    <a href="<?php echo navUrl('/my-requests.php?type=all' . ($status ? '&status=' . $status : '')); ?>"
       class="request-tab <?php echo $type === 'all' ? 'active' : ''; ?>">
        <i class="fas fa-layer-group"></i>
        <span>All Requests</span>
    </a>
    <a href="<?php echo navUrl('/my-requests.php?type=ls' . ($status ? '&status=' . $status : '')); ?>"
       class="request-tab <?php echo $type === 'ls' ? 'active' : ''; ?>">
        <i class="fas fa-map-marker-alt"></i>
        <span>Locator Slips</span>
    </a>
    <a href="<?php echo navUrl('/my-requests.php?type=at' . ($status ? '&status=' . $status : '')); ?>"
       class="request-tab <?php echo $type === 'at' ? 'active' : ''; ?>">
        <i class="fas fa-plane"></i>
        <span>Authority to Travel</span>
    </a>
    <a href="<?php echo navUrl('/my-requests.php?type=ps' . ($status ? '&status=' . $status : '')); ?>"
       class="request-tab <?php echo $type === 'ps' ? 'active' : ''; ?>">
        <i class="fas fa-ticket-alt"></i>
        <span>Pass Slips</span>
    </a>
</div>

<div class="page-header" style="margin-top: 0;">
    <div style="display: flex; align-items: center; gap: 16px;">
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--text-secondary);">
            Showing <?php echo count($requests); ?> of <?php echo $totalRequests; ?> requests
        </h2>
    </div>
    <div style="display: flex; gap: 10px; flex-wrap: wrap;">
        <?php if ($type === 'all' || $type === 'ls'): ?>
        <a href="<?php echo navUrl('/locator-slips.php?action=new'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Locator Slip
        </a>
        <?php endif; ?>
        <?php if ($type === 'all' || $type === 'at'): ?>
        <a href="<?php echo navUrl('/authority-to-travel.php?action=new'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Travel Request
        </a>
        <?php endif; ?>
        <?php if ($type === 'all' || $type === 'ps'): ?>
        <a href="<?php echo navUrl('/pass-slips.php?action=new'); ?>" class="btn btn-primary btn-sm">
            <i class="fas fa-plus"></i> New Pass Slip
        </a>
        <?php endif; ?>
    </div>
</div>

<?php
if ($type === 'ps' && ($auth->isEmployee() || $auth->isUnitHead() || $auth->isASDS() || true)) {
    $myAccum = $psModelReq->getAccumulatedHours($auth->getUserId());
    $myProgress = min(100, max(0, ((float) $myAccum['total_hours'] / 8) * 100));
    if ($myAccum['slip_count'] > 0): ?>
        <div class="accumulated-hours-card" style="margin-bottom: 20px;">
            <div class="detail-card">
                <div class="detail-card-body" style="padding: 16px;">
                    <div style="display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 12px;">
                        <div>
                            <strong><i class="fas fa-clock"></i> Your Accumulated Pass Slip Hours:</strong>
                            <span style="font-size: 1.1rem; font-weight: 700; margin-left: 8px;">
                                <?php echo number_format((float) $myAccum['total_hours'], 2); ?> hrs / 8 hrs
                            </span>
                            <span style="margin-left: 8px; color: var(--text-muted); font-size: 0.85rem;">(<?php echo number_format($myProgress, 1); ?>%)</span>
                            <div style="margin-top: 6px; color: var(--text-muted); font-size: 0.85rem;">
                                Lifetime accumulated: <?php echo number_format((float) ($myAccum['lifetime_hours'] ?? 0), 2); ?> hrs • VL credits deducted: <?php echo (int) ($myAccum['vl_credits_deducted'] ?? 0); ?>
                            </div>
                        </div>
                        <div style="flex: 1; min-width: 200px; max-width: 300px;">
                            <div class="accumulated-progress-bar">
                                <div class="accumulated-progress-fill <?php echo $myAccum['total_hours'] >= 8 ? 'danger' : ($myAccum['total_hours'] >= 6 ? 'warning' : ''); ?>" 
                                     style="width: <?php echo $myProgress; ?>%" title="<?php echo number_format($myProgress, 1); ?>% of 8-hour threshold"></div>
                            </div>
                        </div>
                    </div>
                    <?php if (!empty($myAccum['vl_credits_deducted'])): ?>
                        <div class="vl-deduction-warning" style="margin-top: 10px;">
                            <i class="fas fa-exclamation-triangle"></i>
                            8hrs = 1 VL credit. Total deducted from your VL credits: <?php echo (int) $myAccum['vl_credits_deducted']; ?>.
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    <?php endif;
}
?>

<!-- Filter Bar -->
<div class="filter-bar">
    <form class="filter-form" method="GET" action="">
        <input type="hidden" name="token" value="<?php echo $currentToken; ?>">
        <input type="hidden" name="type" value="<?php echo htmlspecialchars($type); ?>">

        <?php if ($type === 'ps'): ?>
        <div class="filter-group">
            <label>Search</label>
            <input type="text" name="search" class="filter-input" placeholder="Control no, name, destination..."
                value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
        </div>

        <?php if ($auth->isApprover() || $auth->isUnitHead()): ?>
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
        <?php endif; ?>
        <?php endif; ?>

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

        <?php if ($type === 'ps'): ?>
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
        <?php endif; ?>

        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm">
                <i class="fas fa-filter"></i> Filter
            </button>
            <a href="<?php echo navUrl('/my-requests.php?type=' . $type); ?>" class="btn btn-secondary btn-sm">
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
                                        $viewUrl = navUrl('/locator-slips.php?view=' . $request['id'] . '&from=my-requests');
                                    } elseif ($request['request_type'] === 'ps') {
                                        $viewUrl = navUrl('/pass-slips.php?view=' . $request['id'] . '&from=my-requests');
                                    } else {
                                        $viewUrl = navUrl('/authority-to-travel.php?view=' . $request['id'] . '&from=my-requests');
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