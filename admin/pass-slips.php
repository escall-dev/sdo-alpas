<?php
/**
 * Pass Slips Management Page
 * SDO ALPAS - View, create, approve, reject, cancel Pass Slips, and update guard times
 */

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/PassSlip.php';
require_once __DIR__ . '/../services/TrackingService.php';

$psModel = new PassSlip();

// Get current user info for routing and visibility
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
        try {
            $data = [
                'employee_name' => $_POST['employee_name'],
                'employee_position' => $_POST['employee_position'],
                'employee_office' => $_POST['employee_office'],
                'date' => $_POST['date'],
                'destination' => $_POST['destination'],
                'idt' => $_POST['idt'],
                'iat' => $_POST['iat'],
                'purpose' => $_POST['purpose'],
                'requesting_employee_name' => $_POST['requesting_employee_name'] ?? $_POST['employee_name'],
                'request_date' => date('Y-m-d'),
                'user_id' => $auth->getUserId()
            ];

            $validation = $psModel->validateSubmission($data);
            if (!$validation['valid']) {
                $error = implode(' ', $validation['errors']);
            } else {
                $requesterRoleId = $currentUser['role_id'];
                $requesterOffice = $currentUser['employee_office'] ?? $_POST['employee_office'];
                $requesterOfficeId = !empty($currentUser['office_id']) ? (int) $currentUser['office_id'] : null;

                $result = $psModel->create($data, $requesterRoleId, $requesterOffice, $requesterOfficeId);
                $auth->logActivity('create', 'pass_slip', $result['id'], 'Created Pass Slip: ' . $result['control_no']);

                $message = 'Pass Slip filed successfully! Tracking Number: ' . $result['control_no'];
                $action = '';
            }
        } catch (Exception $e) {
            $error = 'Failed to create Pass Slip: ' . $e->getMessage();
        }
    }

    if ($postAction === 'edit') {
        try {
            $id = $_POST['id'];
            $ps = $psModel->getById($id);

            if (!$ps) {
                $error = 'Pass Slip not found.';
            } elseif (!$psModel->canUserEdit($ps, $auth->getUserId())) {
                $error = 'You cannot edit this Pass Slip.';
            } else {
                $data = [
                    'employee_name' => $_POST['employee_name'],
                    'employee_position' => $_POST['employee_position'],
                    'employee_office' => $_POST['employee_office'],
                    'date' => $_POST['date'],
                    'destination' => $_POST['destination'],
                    'idt' => $_POST['idt'],
                    'iat' => $_POST['iat'],
                    'purpose' => $_POST['purpose']
                ];

                $psModel->update($id, $data, $auth->getUserId());
                $auth->logActivity('update', 'pass_slip', $id, 'Updated Pass Slip: ' . $ps['ps_control_no']);
                $message = 'Pass Slip updated successfully!';
            }
        } catch (Exception $e) {
            $error = 'Failed to update Pass Slip: ' . $e->getMessage();
        }
    }

    if ($postAction === 'approve') {
        $id = $_POST['id'];
        $ps = $psModel->getById($id);

        $canApprove = !$auth->isSDS() && (
            ($ps['assigned_approver_user_id'] == $auth->getUserId()) ||
            ($currentRoleId == $ps['assigned_approver_role_id'] && in_array($currentRoleId, UNIT_HEAD_ROLES)) ||
            ($auth->isASDS() && (int) ($ps['assigned_approver_role_id'] ?? 0) === ROLE_ASDS)
        );

        if (!$canApprove && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $ps['assigned_approver_role_id']) {
                $canApprove = true;
            }
        }

        if ($ps && $ps['status'] === 'pending' && $canApprove) {
            $isOIC = $auth->isActingAsOIC();

            $posRaw = trim($currentUser['employee_position'] ?? '');
            $posKey = strtoupper($posRaw);
            $positionMap = [
                'ASDS' => 'Assistant Schools Division Superintendent',
                'AOV' => 'Administrative Officer V',
                'AO V' => 'Administrative Officer V',
                'SDS' => 'Schools Division Superintendent',
                'SUPERADMIN' => 'Superadmin',
                'OSDS_CHIEF' => 'Administrative Officer V',
            ];
            $approverPosition = $positionMap[$posKey] ?? $positionMap[$currentUser['role_name']] ?? ($posRaw ?: $currentUser['role_name'] ?? '');

            $psModel->approve($id, $auth->getUserId(), $currentUser['full_name'], $approverPosition, $isOIC);

            $actionType = $isOIC ? 'OIC-APPROVAL' : 'approve';
            $auth->logActivity($actionType, 'pass_slip', $id, 'Approved Pass Slip: ' . $ps['ps_control_no']);
            $message = 'Pass Slip approved successfully!';
        } else {
            $error = 'You do not have permission to approve this request.';
        }
    }

    if ($postAction === 'reject') {
        $id = $_POST['id'];
        $reason = $_POST['rejection_reason'] ?? null;
        $ps = $psModel->getById($id);

        $canReject = $ps && $ps['status'] === 'pending' &&
            (($ps['assigned_approver_user_id'] == $auth->getUserId()) ||
                ($currentRoleId == $ps['assigned_approver_role_id'] && in_array($currentRoleId, UNIT_HEAD_ROLES)) ||
                ($auth->isASDS() && (int) ($ps['assigned_approver_role_id'] ?? 0) === ROLE_ASDS));
        if (!$canReject && $ps && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $ps['assigned_approver_role_id']) {
                $canReject = true;
            }
        }

        if ($canReject) {
            $psModel->reject($id, $auth->getUserId(), $reason);
            $auth->logActivity('reject', 'pass_slip', $id, 'Rejected Pass Slip: ' . $ps['ps_control_no']);
            $message = 'Pass Slip rejected.';
        } elseif ($ps && $ps['status'] === 'pending') {
            $error = 'You do not have permission to reject this request.';
        }
    }

    if ($postAction === 'cancel') {
        $id = $_POST['id'];
        $ps = $psModel->getById($id);

        if ($ps && $ps['status'] === 'pending' && $ps['user_id'] == $auth->getUserId()) {
            $psModel->cancel($id, $auth->getUserId());
            $auth->logActivity('cancel', 'pass_slip', $id, 'Cancelled Pass Slip: ' . $ps['ps_control_no']);
            $message = 'Pass Slip cancelled.';
        } else {
            $error = 'You cannot cancel this Pass Slip.';
        }
    }

    if ($postAction === 'update_guard_times') {
        $id = $_POST['id'];
        $ps = $psModel->getById($id);

        if ($ps && $ps['status'] === 'approved') {
            $psModel->updateGuardTimes($id, $_POST['actual_departure_time'] ?? null, $_POST['actual_arrival_time'] ?? null);
            $auth->logActivity('update', 'pass_slip', $id, 'Updated guard times for Pass Slip: ' . $ps['ps_control_no']);
            $message = 'Guard times updated successfully!';
            $viewId = $id; // Stay on view
        } else {
            $error = 'Guard times can only be updated for approved Pass Slips.';
        }
    }
}

// View single request
$viewData = null;
if ($viewId) {
    $viewData = $psModel->getById($viewId);
    if (!$viewData) {
        $error = 'Pass Slip not found.';
    } elseif (!$psModel->canUserView($viewData, $currentRoleId, $auth->getUserId())) {
        $error = 'You do not have permission to view this request.';
        $viewData = null;
    }
}

// Get list data with filters
$filters = [];
if (!empty($_GET['status']))
    $filters['status'] = $_GET['status'];
if (!empty($_GET['unit']))
    $filters['unit'] = $_GET['unit'];
if (!empty($_GET['date_from']))
    $filters['date_from'] = $_GET['date_from'];
if (!empty($_GET['date_to']))
    $filters['date_to'] = $_GET['date_to'];
if (!empty($_GET['approval_date_from']))
    $filters['approval_date_from'] = $_GET['approval_date_from'];
if (!empty($_GET['approval_date_to']))
    $filters['approval_date_to'] = $_GET['approval_date_to'];
if (!empty($_GET['approver_id']))
    $filters['approver_id'] = $_GET['approver_id'];
if (!empty($_GET['search']))
    $filters['search'] = $_GET['search'];

$page = max(1, intval($_GET['page'] ?? 1));
$perPage = ITEMS_PER_PAGE;
$offset = ($page - 1) * $perPage;

$requests = $psModel->getAll($filters, $perPage, $offset, $currentRoleId, $auth->getUserId());
$totalRequests = $psModel->getCount($filters, $currentRoleId, $auth->getUserId());
$totalPages = ceil($totalRequests / $perPage);

// Get approvers for filter dropdown
require_once __DIR__ . '/../models/AdminUser.php';
$userModel = new AdminUser();
$allApprovers = [];
if ($auth->isSuperAdmin() || $auth->isASDS() || $auth->isSDS()) {
    $unitHeads = $userModel->getUnitHeads(true);
    foreach ($unitHeads as $uh) {
        $allApprovers[$uh['id']] = $uh['full_name'] . ' (' . $uh['role_name'] . ')';
    }
    $asdsUsers = $userModel->getByRole(ROLE_ASDS, true);
    foreach ($asdsUsers as $au) {
        $allApprovers[$au['id']] = $au['full_name'] . ' (ASDS)';
    }
    $sdsUsers = $userModel->getByRole(ROLE_SDS, true);
    foreach ($sdsUsers as $su) {
        $allApprovers[$su['id']] = $su['full_name'] . ' (SDS)';
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
    <div class="alert"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; margin-bottom: 20px;">
        <i class="fas fa-user-shield"></i>
        <strong>Acting as OIC:</strong> You are currently serving as Officer-In-Charge (
        <?php echo htmlspecialchars($auth->getEffectiveRoleDisplayName()); ?>).
        You can approve requests on behalf of the unit head.
    </div>
<?php endif; ?>

<?php if ($message): ?>
    <div class="alert alert-success">
        <i class="fas fa-check-circle"></i>
        <?php echo htmlspecialchars($message); ?>
    </div>
<?php endif; ?>

<?php if ($error): ?>
    <div class="alert alert-error">
        <i class="fas fa-exclamation-triangle"></i>
        <?php echo htmlspecialchars($error); ?>
    </div>
<?php endif; ?>

<?php if ($editId): ?>
    <!-- Edit Pass Slip -->
    <?php
    $editData = $psModel->getById($editId);
    if (!$editData || !$psModel->canUserEdit($editData, $auth->getUserId())) {
        $error = 'You cannot edit this Pass Slip.';
        $editData = null;
    }
    ?>
    <?php if ($editData): ?>
        <div class="page-header">
            <a href="<?php echo navUrl('/pass-slips.php?view=' . $editData['id']); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to View
            </a>
        </div>

        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-edit"></i> Edit Pass Slip</h3>
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
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo htmlspecialchars($editData['date']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Destination <span class="required">*</span></label>
                            <input type="text" name="destination" class="form-control" required
                                value="<?php echo htmlspecialchars($editData['destination']); ?>">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Intended Time of Departure <span class="required">*</span></label>
                            <input type="time" name="idt" class="form-control" required
                                value="<?php echo htmlspecialchars($editData['idt']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Intended Time of Arrival <span class="required">*</span></label>
                            <input type="time" name="iat" class="form-control" required
                                value="<?php echo htmlspecialchars($editData['iat']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose <span class="required">*</span></label>
                        <textarea name="purpose" class="form-control" rows="3"
                            required><?php echo htmlspecialchars($editData['purpose']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo navUrl('/pass-slips.php?view=' . $editData['id']); ?>" class="btn btn-secondary">
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
    <!-- View Single Pass Slip -->
    <div class="page-header">
        <a href="<?php echo navUrl('/pass-slips.php'); ?>" class="back-link">
            <i class="fas fa-arrow-left"></i> Back to List
        </a>
    </div>

    <div class="complaint-detail-grid">
        <div class="complaint-main">
            <!-- Reference Card -->
            <div class="detail-card ref-card">
                <div class="ref-header">
                    <div class="ref-number">
                        <?php echo htmlspecialchars($viewData['ps_control_no']); ?>
                    </div>
                    <div class="ref-date">Filed on
                        <?php echo date('F j, Y - g:i A', strtotime($viewData['created_at'])); ?>
                    </div>
                </div>
                <div class="ref-unit">
                    <?php echo getStatusBadge($viewData['status']); ?>
                    <span class="unit-badge large">Pass Slip</span>
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
                            <span>
                                <?php echo htmlspecialchars($viewData['employee_name']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Position</label>
                            <span>
                                <?php echo htmlspecialchars($viewData['employee_position'] ?: '-'); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Office/Division</label>
                            <span>
                                <?php echo htmlspecialchars($viewData['employee_office'] ?: '-'); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Date</label>
                            <span>
                                <?php echo date('F j, Y', strtotime($viewData['date'])); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Destination</label>
                            <span>
                                <?php echo htmlspecialchars($viewData['destination']); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Intended Time of Departure</label>
                            <span>
                                <?php echo date('g:i A', strtotime($viewData['idt'])); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Intended Time of Arrival</label>
                            <span>
                                <?php echo date('g:i A', strtotime($viewData['iat'])); ?>
                            </span>
                        </div>
                        <div class="detail-item">
                            <label>Purpose</label>
                            <span class="narration-text">
                                <?php echo nl2br(htmlspecialchars($viewData['purpose'])); ?>
                            </span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($viewData['status'] !== 'pending' && ($viewData['approver_name'] || $viewData['rejection_reason'])): ?>
                <!-- Approval / Rejection Details -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i
                                class="fas fa-<?php echo $viewData['status'] === 'approved' ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $viewData['status'] === 'approved' ? 'Approval' : 'Rejection'; ?> Details
                        </h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-grid">
                            <?php if ($viewData['status'] === 'approved'): ?>
                                <div class="detail-item">
                                    <label>Approved By</label>
                                    <span>
                                        <?php echo htmlspecialchars($viewData['approver_name']); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <label>Position</label>
                                    <span>
                                        <?php echo htmlspecialchars($viewData['approver_position'] ?: '-'); ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <label>Approval Date</label>
                                    <span>
                                        <?php echo $viewData['approval_date'] ? date('F j, Y', strtotime($viewData['approval_date'])) : '-'; ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <label>Approval Time</label>
                                    <span>
                                        <?php echo !empty($viewData['approving_time']) ? date('g:i A', strtotime($viewData['approving_time'])) : '-'; ?>
                                    </span>
                                </div>
                            <?php else: ?>
                                <div class="detail-item">
                                    <label>Rejection Reason</label>
                                    <span>
                                        <?php echo htmlspecialchars($viewData['rejection_reason'] ?: 'No reason provided'); ?>
                                    </span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($viewData['status'] === 'approved'): ?>
                <!-- Guard Section -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-shield-alt"></i> Guard Section (Actual Times)</h3>
                    </div>
                    <div class="detail-card-body">
                        <?php if ($viewData['actual_departure_time'] || $viewData['actual_arrival_time']): ?>
                            <div class="detail-grid">
                                <div class="detail-item">
                                    <label>Actual Time of Departure</label>
                                    <span>
                                        <?php echo $viewData['actual_departure_time'] ? date('g:i A', strtotime($viewData['actual_departure_time'])) : 'Not yet recorded'; ?>
                                    </span>
                                </div>
                                <div class="detail-item">
                                    <label>Actual Time of Arrival</label>
                                    <span>
                                        <?php echo $viewData['actual_arrival_time'] ? date('g:i A', strtotime($viewData['actual_arrival_time'])) : 'Not yet recorded'; ?>
                                    </span>
                                </div>
                            </div>
                        <?php endif; ?>

                        <form method="POST" action="" style="margin-top: 15px;">
                            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                            <input type="hidden" name="action" value="update_guard_times">
                            <input type="hidden" name="id" value="<?php echo $viewData['id']; ?>">
                            <div class="form-row">
                                <div class="form-group">
                                    <label class="form-label">Actual Departure Time</label>
                                    <input type="time" name="actual_departure_time" class="form-control"
                                        value="<?php echo htmlspecialchars($viewData['actual_departure_time'] ?? ''); ?>">
                                </div>
                                <div class="form-group">
                                    <label class="form-label">Actual Arrival Time</label>
                                    <input type="time" name="actual_arrival_time" class="form-control"
                                        value="<?php echo htmlspecialchars($viewData['actual_arrival_time'] ?? ''); ?>">
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary btn-sm">
                                <i class="fas fa-save"></i> Update Guard Times
                            </button>
                        </form>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="complaint-sidebar">
            <!-- Approval Actions -->
            <?php
            $canApprove = !$auth->isSDS() && $viewData['status'] === 'pending' &&
                ($viewData['assigned_approver_user_id'] == $auth->getUserId() ||
                    ($currentRoleId == $viewData['assigned_approver_role_id'] && in_array($currentRoleId, UNIT_HEAD_ROLES)) ||
                    ($auth->isASDS() && (int) ($viewData['assigned_approver_role_id'] ?? 0) === ROLE_ASDS));

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
                            <button type="button" class="btn btn-success btn-block"
                                onclick="openApproveModal(<?php echo $viewData['id']; ?>)">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-block"
                            onclick="showRejectModal(<?php echo $viewData['id']; ?>)">
                            <i class="fas fa-times"></i> Reject
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Cancel Action (only for owner when pending) -->
            <?php if ($viewData['status'] === 'pending' && $viewData['user_id'] == $auth->getUserId()): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-ban"></i> Cancel Request</h3>
                    </div>
                    <div class="detail-card-body">
                        <button type="button" class="btn btn-secondary btn-block"
                            onclick="showCancelModal(<?php echo $viewData['id']; ?>)">
                            <i class="fas fa-ban"></i> Cancel Pass Slip
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($psModel->canUserEdit($viewData, $auth->getUserId())): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-edit"></i> Edit</h3>
                    </div>
                    <div class="detail-card-body">
                        <a href="<?php echo navUrl('/pass-slips.php?edit=' . $viewData['id']); ?>"
                            class="btn btn-primary btn-block">
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
                        <a href="<?php echo navUrl('/api/generate-docx.php?type=ps&id=' . $viewData['id']); ?>"
                            class="btn btn-primary btn-block">
                            <i class="fas fa-file-pdf"></i> Download PDF
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
                        <span>
                            <?php echo htmlspecialchars($viewData['filed_by_name']); ?>
                        </span>
                    </div>
                    <div class="detail-item">
                        <label>Email</label>
                        <span>
                            <?php echo htmlspecialchars($viewData['filed_by_email']); ?>
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="margin-right: 8px; color: var(--success);"></i> Approve Pass Slip
                </h3>
                <button class="modal-close" type="button" onclick="closeApproveModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="approve">
                    <input type="hidden" name="id" id="approveId" value="">

                    <p style="margin-bottom: 10px;">
                        Are you sure you want to approve this Pass Slip?
                    </p>
                    <div
                        style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
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
                <h3>Reject Pass Slip</h3>
                <button class="modal-close" onclick="closeRejectModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="reject">
                    <input type="hidden" name="id" id="rejectId" value="">

                    <div class="form-group">
                        <label class="form-label">Reason for Rejection (Optional)</label>
                        <textarea name="rejection_reason" class="form-control" rows="4"
                            placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRejectModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Reject Request</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Cancel Modal -->
    <div class="modal-overlay" id="cancelModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-ban" style="margin-right: 8px; color: var(--text-muted);"></i> Cancel Pass Slip</h3>
                <button class="modal-close" onclick="closeCancelModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="cancel">
                    <input type="hidden" name="id" id="cancelId" value="">
                    <p>Are you sure you want to cancel this Pass Slip? This action cannot be undone.</p>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeCancelModal()">No, go back</button>
                    <button type="submit" class="btn btn-danger"><i class="fas fa-ban"></i> Yes, cancel it</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApproveModal(id) {
            document.getElementById('approveId').value = id;
            var controlNoEl = document.querySelector('.ref-number');
            if (controlNoEl) document.getElementById('approveControlNo').textContent = controlNoEl.textContent.trim();
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

        function showCancelModal(id) {
            document.getElementById('cancelId').value = id;
            document.getElementById('cancelModal').classList.add('active');
        }
        function closeCancelModal() {
            document.getElementById('cancelModal').classList.remove('active');
        }
    </script>

<?php else: ?>
    <!-- List View -->
    <div class="page-header">
        <div class="result-count">
            <?php echo $totalRequests; ?> Pass Slip
            <?php echo $totalRequests !== 1 ? 's' : ''; ?>
        </div>
        <button type="button" class="btn btn-primary" onclick="openNewModal()">
            <i class="fas fa-plus"></i> New Pass Slip
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
                <label>Status</label>
                <select name="status" class="filter-select">
                    <option value="">All Status</option>
                    <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="approved" <?php echo ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved
                    </option>
                    <option value="rejected" <?php echo ($_GET['status'] ?? '') === 'rejected' ? 'selected' : ''; ?>>Rejected
                    </option>
                    <option value="cancelled" <?php echo ($_GET['status'] ?? '') === 'cancelled' ? 'selected' : ''; ?>>
                        Cancelled</option>
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
                <a href="<?php echo navUrl('/pass-slips.php'); ?>" class="btn btn-secondary btn-sm"><i
                        class="fas fa-times"></i> Clear</a>
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
                        <th>Date</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="6">
                                <div class="empty-state">
                                    <span class="empty-icon"><i class="fas fa-ticket-alt"></i></span>
                                    <h3>No Pass Slips found</h3>
                                    <p>Create a new Pass Slip to get started</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $ps): ?>
                            <tr>
                                <td>
                                    <a href="<?php echo navUrl('/pass-slips.php?view=' . $ps['id']); ?>" class="ref-link">
                                        <?php echo htmlspecialchars($ps['ps_control_no']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="cell-primary">
                                        <?php echo htmlspecialchars($ps['employee_name']); ?>
                                    </div>
                                    <div class="cell-secondary">
                                        <?php echo htmlspecialchars($ps['employee_position'] ?: ''); ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-primary">
                                        <?php echo htmlspecialchars($ps['destination']); ?>
                                    </div>
                                    <div class="cell-secondary">
                                        <?php echo htmlspecialchars(substr($ps['purpose'], 0, 50)); ?>
                                        <?php echo strlen($ps['purpose']) > 50 ? '...' : ''; ?>
                                    </div>
                                </td>
                                <td>
                                    <div class="cell-primary">
                                        <?php echo date('M j, Y', strtotime($ps['date'])); ?>
                                    </div>
                                    <div class="cell-secondary">
                                        <?php echo date('g:i A', strtotime($ps['idt'])); ?> -
                                        <?php echo date('g:i A', strtotime($ps['iat'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php echo getStatusBadge($ps['status']); ?>
                                </td>
                                <td>
                                    <div class="action-buttons">
                                        <a href="<?php echo navUrl('/pass-slips.php?view=' . $ps['id']); ?>" class="btn btn-icon"
                                            title="View">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($psModel->canUserEdit($ps, $auth->getUserId())): ?>
                                            <a href="<?php echo navUrl('/pass-slips.php?edit=' . $ps['id']); ?>" class="btn btn-icon"
                                                title="Edit" style="color: var(--primary);">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ps['status'] === 'approved'): ?>
                                            <a href="<?php echo navUrl('/api/generate-docx.php?type=ps&id=' . $ps['id']); ?>"
                                                class="btn btn-icon" title="Download PDF" style="color: var(--success);">
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
                <div class="pagination-info">Page
                    <?php echo $page; ?> of
                    <?php echo $totalPages; ?>
                </div>
                <div class="pagination-links">
                    <?php if ($page > 1): ?>
                        <a href="<?php echo navUrl('/pass-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?php echo navUrl('/pass-slips.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>"
                            class="page-link <?php echo $i === $page ? 'active' : ''; ?>">
                            <?php echo $i; ?>
                        </a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo navUrl('/pass-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <!-- New Pass Slip Modal -->
    <div class="modal-overlay" id="newModal" <?php echo $action === 'new' ? 'class="active"' : ''; ?>>
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3><i class="fas fa-ticket-alt"></i> New Pass Slip</h3>
                <button class="modal-close" onclick="closeNewModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employee Name <span class="required">*</span></label>
                            <input type="text" name="employee_name" class="form-control" required readonly
                                value="<?php echo htmlspecialchars($formData['employee_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <input type="text" name="employee_position" class="form-control" readonly
                                value="<?php echo htmlspecialchars($formData['employee_position']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Office/Division</label>
                        <select name="employee_office" class="form-control" disabled>
                            <option value="">-- Select Office --</option>
                            <?php foreach (SDO_OFFICES as $code => $name): ?>
                                <option value="<?php echo $code; ?>" <?php echo $formData['employee_office'] === $code ? 'selected' : ''; ?>>
                                    <?php echo htmlspecialchars($name); ?>
                                </option>
                            <?php endforeach; ?>
                        </select>
                        <!-- Hidden input to send the value since disabled selects don't submit -->
                        <input type="hidden" name="employee_office"
                            value="<?php echo htmlspecialchars($formData['employee_office']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date <span class="required">*</span></label>
                            <input type="date" name="date" class="form-control" required min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Destination <span class="required">*</span></label>
                            <input type="text" name="destination" class="form-control" required
                                placeholder="Where are you going?">
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Intended Time of Departure <span class="required">*</span></label>
                            <input type="time" name="idt" class="form-control" required>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Intended Time of Arrival <span class="required">*</span></label>
                            <input type="time" name="iat" class="form-control" required>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose <span class="required">*</span></label>
                        <textarea name="purpose" class="form-control" rows="3" required
                            placeholder="Describe the purpose of your pass..."></textarea>
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