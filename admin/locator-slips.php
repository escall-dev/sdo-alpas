<?php
/**
 * Locator Slip Management Page
 * SDO ALPAS - View, create, and approve Locator Slip
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
$canCreateTravelRequests = !$auth->isSuperAdmin();

if (!$canCreateTravelRequests && $action === 'new') {
    $action = '';
    $error = 'Superadmin accounts cannot file travel requests.';
}

/**
 * Convert technical database errors into plain-language messages
 * so non-technical users can understand what went wrong.
 */
function friendlyDbErrorLS($exceptionMessage)
{
    if (stripos($exceptionMessage, "employee_no") !== false && stripos($exceptionMessage, "cannot be null") !== false) {
        return "This action could not be completed because the employee's Employee Number is not yet saved in the system. "
            . "Please ask HR or your system administrator to update the employee's profile with their Employee Number, then try again.";
    }
    if (stripos($exceptionMessage, "Duplicate entry") !== false) {
        return "This record was already processed. Please refresh the page and try again.";
    }
    return "Something went wrong while processing your request. Please try again or contact your system administrator if the problem continues.";
}

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';

    if ($postAction === 'create') {
        if (!$canCreateTravelRequests) {
            $error = 'Superadmin accounts cannot file travel requests.';
        } else {
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

            // Validate submission (date cannot be in the past)
            $validation = $lsModel->validateSubmission($data);
            if (!$validation['valid']) {
                $error = implode(' ', $validation['errors']);
            } else {
                // Get requester info for routing (office_id preferred so dentist/SHN etc. route to SGOD Chief)
                $requesterRoleId = $currentUser['role_id'];
                $requesterOffice = $currentUser['employee_office'] ?? $_POST['employee_office'];
                $requesterOfficeId = !empty($currentUser['office_id']) ? (int) $currentUser['office_id'] : null;

                $id = $lsModel->create($data, $requesterRoleId, $requesterOffice, $requesterOfficeId);
                $auth->logActivity('create', 'locator_slip', $id, 'Created Locator Slip: ' . $controlNo);

                $message = 'Locator Slip filed successfully! Tracking Number: ' . $controlNo;
                $action = ''; // Close modal
            }
        } catch (Exception $e) {
            $error = 'Failed to create Locator Slip: ' . $e->getMessage();
        }
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
        // 1. They are the assigned approver (by user ID)
        // 2. Their role matches the assigned approver role (e.g. OSDS Chief for OSDS-routed requests)
        // 3. They are acting as OIC for the assigned approver's role
        // 4. ASDS only when this slip is assigned to ASDS (Office Chief as requestor)
        // Note: Superadmin can VIEW all requests but cannot approve/disapprove
        // SDS is view-only for Locator Slip — cannot approve
        $canApprove = !$auth->isSDS() && (
            ($ls['assigned_approver_user_id'] == $auth->getUserId()) ||
            ($currentRoleId == $ls['assigned_approver_role_id'] && in_array($currentRoleId, UNIT_HEAD_ROLES)) ||
            ($auth->isASDS() && (int) ($ls['assigned_approver_role_id'] ?? 0) === ROLE_ASDS)
        );

        if (!$canApprove && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $ls['assigned_approver_role_id']) {
                $canApprove = true;
            }
        }

        if ($ls && $ls['status'] === 'pending' && $canApprove) {
            // Check if this is an OIC approval
            $isOIC = $auth->isActingAsOIC();

            try {
                // Expand common acronyms to full titles for approver position
                $posRaw = trim($currentUser['employee_position'] ?? '');
                $posKey = strtoupper($posRaw);
                $positionMap = [
                    'ASDS' => 'Assistant Schools Division Superintendent',
                    'AOV' => 'Administrative Officer V',
                    'AO V' => 'Administrative Officer V',
                    'AO V - ADMINISTRATIVE' => 'Administrative Officer V',
                    'SDS' => 'Schools Division Superintendent',
                    'SUPERADMIN' => 'Superadmin',
                ];
                // Also check role_name for position
                $approverPosition = $positionMap[$posKey] ?? $positionMap[$currentUser['role_name']] ?? ($posRaw ?: $currentUser['role_name'] ?? '');

                $lsModel->approve($id, $auth->getUserId(), $currentUser['full_name'], $approverPosition, $isOIC);

                // Log with OIC prefix if applicable
                $actionType = $isOIC ? 'OIC-APPROVAL' : 'approve';
                $auth->logActivity($actionType, 'locator_slip', $id, 'Approved Locator Slip: ' . $ls['ls_control_no']);
                $message = 'Locator Slip approved successfully!';
            } catch (Exception $e) {
                $error = friendlyDbErrorLS($e->getMessage());
            }
        } else {
            $error = 'You do not have permission to approve this request.';
        }
    }

    if ($postAction === 'disapprove') {
        $id = $_POST['id'];
        $reason = $_POST['rejection_reason'] ?? null;
        $ls = $lsModel->getById($id);

        $canDisapprove = $ls && $ls['status'] === 'pending' &&
            (($ls['assigned_approver_user_id'] == $auth->getUserId()) ||
                ($currentRoleId == $ls['assigned_approver_role_id'] && in_array($currentRoleId, UNIT_HEAD_ROLES)) ||
                ($auth->isASDS() && (int) ($ls['assigned_approver_role_id'] ?? 0) === ROLE_ASDS));
        if (!$canDisapprove && $ls && $auth->isActingAsOIC()) {
            $oicInfo = $auth->getActiveOICDelegation();
            if ($oicInfo && $oicInfo['unit_head_role_id'] == $ls['assigned_approver_role_id']) {
                $canDisapprove = true;
            }
        }

        if ($canDisapprove) {
            $lsModel->disapprove($id, $auth->getUserId(), $reason);
            $auth->logActivity('disapprove', 'locator_slip', $id, 'Disapproved Locator Slip: ' . $ls['ls_control_no']);
            $message = 'Locator Slip disapproved.';
        } elseif ($ls && $ls['status'] === 'pending') {
            $error = 'You do not have permission to disapprove this request.';
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
if ($auth->isSuperAdmin() || $auth->isASDS() || $auth->isSDS()) {
    // Get all unit heads
    $unitHeads = $userModel->getUnitHeads(true);
    foreach ($unitHeads as $uh) {
        $allApprovers[$uh['id']] = $uh['full_name'] . ' (' . $uh['role_name'] . ')';
    }
    // Add ASDS and SDS as filter options
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
$employeeUnitSection = '';
if (!empty($currentUser['office_id'])) {
    $officeInfo = getOfficeById((int) $currentUser['office_id']);
    $employeeUnitSection = $officeInfo['office_name'] ?? '';
}
if ($employeeUnitSection === '' && !empty($currentUser['employee_office'])) {
    $employeeUnitSection = SDO_OFFICES[$currentUser['employee_office']] ?? $currentUser['employee_office'];
}

$formData = [
    'employee_name' => $currentUser['full_name'],
    'employee_position' => $currentUser['employee_position'] ?? '',
    'employee_office' => $currentUser['employee_office'] ?? '',
    'employee_unit_section' => $employeeUnitSection
];
?>

<?php if ($isActingAsOIC): ?>
    <!-- OIC Notice Banner -->
    <div class="alert"
        style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none; margin-bottom: 20px;">
        <i class="fas fa-user-shield"></i>
        <strong>Acting as OIC:</strong> You are currently serving as Officer-In-Charge
        (<?php echo htmlspecialchars($auth->getEffectiveRoleDisplayName()); ?>).
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
    <script>
        document.addEventListener('DOMContentLoaded', function () {
            if (typeof showAlertModal === 'function') {
                showAlertModal(<?php echo json_encode($error); ?>, {
                    title: 'Unable to Complete Action',
                    tone: 'error'
                });
            }
        });
    </script>
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

        <div class="detail-card"
            style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 30px rgba(0,0,0,0.04); border-radius: var(--radius-xl); overflow: hidden; margin-top: 24px;">
            <div class="detail-card-header"
                style="padding: 24px 30px; background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%); border-bottom: none;">
                <h3
                    style="color: white; font-size: 1.25rem; font-weight: 700; margin: 0; display: flex; align-items: center; gap: 10px;">
                    <i class="fas fa-edit" style="color: var(--accent);"></i> Edit Locator Slip</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employee Name <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" name="employee_name" class="form-control" required
                                    value="<?php echo htmlspecialchars($editData['employee_name']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <div class="input-with-icon">
                                <input type="text" name="employee_position" class="form-control"
                                    value="<?php echo htmlspecialchars($editData['employee_position'] ?? ''); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <div class="input-with-icon">
                            <select name="employee_office" class="form-control">
                                <option value="">-- Select Unit/Section --</option>
                                <?php foreach (SDO_OFFICES as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo ($editData['employee_office'] ?? '') === $code ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($name); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Travel Type <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <select name="travel_type" class="form-control" required>
                                    <?php foreach (TRAVEL_TYPES as $code => $label): ?>
                                        <option value="<?php echo $code; ?>" <?php echo ($editData['travel_type'] ?? '') === $code ? 'selected' : ''; ?>>
                                            <?php echo htmlspecialchars($label); ?>
                                        </option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date & Time <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="datetime-local" name="date_time" class="form-control" required
                                    min="<?php echo date('Y-m-d\TH:i'); ?>"
                                    value="<?php echo date('Y-m-d\TH:i', strtotime($editData['date_time'])); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Destination <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <input type="text" name="destination" class="form-control" required
                                value="<?php echo htmlspecialchars($editData['destination']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <textarea name="purpose_of_travel" class="form-control" rows="3"
                                required><?php echo htmlspecialchars($editData['purpose_of_travel']); ?></textarea>
                        </div>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo navUrl('/locator-slips.php?view=' . $editData['id']); ?>" class="btn btn-secondary">
                            Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>
    <?php endif; ?>

<?php elseif ($viewData): ?>
    <!-- View Single Request -->
    <div class="page-header">
        <?php if (isset($_GET['from']) && $_GET['from'] === 'my-requests'): ?>
            <a href="<?php echo navUrl('/my-requests.php'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to My Requests
            </a>
        <?php else: ?>
            <a href="<?php echo navUrl('/locator-slips.php'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        <?php endif; ?>
    </div>

    <div class="complaint-detail-grid">
        <div class="complaint-main">
            <!-- Reference Card -->
            <div class="detail-card ref-card"
                style="background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%); border: 1px solid rgba(255,255,255,0.15); box-shadow: 0 15px 35px rgba(15, 76, 117, 0.25); border-radius: var(--radius-xl); overflow: hidden; position: relative;">
                <div
                    style="position: absolute; top: 0; right: 0; bottom: 0; left: 0; background: url('data:image/svg+xml,%3Csvg width=%22100%22 height=%22100%22 xmlns=%22http://www.w3.org/2000/svg%22%3E%3Ccircle cx=%22100%22 cy=%220%22 r=%2280%22 fill=%22rgba(255,255,255,0.05)%22/%3E%3C/svg%3E') no-repeat top right / cover; pointer-events: none;">
                </div>
                <div class="ref-header" style="padding: 30px; border-bottom: 1px solid rgba(255,255,255,0.1);">
                    <div class="ref-number" style="font-size: 1.8rem; text-shadow: 0 2px 4px rgba(0,0,0,0.2);">
                        <?php echo htmlspecialchars($viewData['ls_control_no']); ?></div>
                    <div class="ref-date" style="opacity: 0.9; font-weight: 500;">Filed on
                        <?php echo date('F j, Y - g:i A', strtotime($viewData['created_at'])); ?></div>
                </div>
                <div class="ref-unit"
                    style="padding: 20px 30px; background: rgba(0, 0, 0, 0.2); backdrop-filter: blur(10px);">
                    <?php echo getStatusBadge($viewData['status']); ?>
                    <span class="unit-badge large"
                        style="background: rgba(255, 255, 255, 0.15); border: 1px solid rgba(255,255,255,0.2); box-shadow: 0 2px 8px rgba(0,0,0,0.1);"><?php echo htmlspecialchars($viewData['travel_type']); ?></span>
                </div>
            </div>

            <!-- Request Details -->
            <div class="detail-card"
                style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl); margin-top: 24px;">
                <div class="detail-card-header"
                    style="padding: 20px 24px; background: var(--bg-secondary); border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <h3 style="font-size: 1.1rem; color: var(--primary-dark); font-weight: 700;"><i
                        class="fas fa-info-circle"></i> Request Details</h3>
                </div>
                <div class="detail-card-body" style="padding: 24px;">
                    <div class="detail-grid"
                        style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Employee
                                Name</label>
                            <span
                                style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($viewData['employee_name']); ?></span>
                        </div>
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Position</label>
                            <span
                                style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($viewData['employee_position'] ?: '-'); ?></span>
                        </div>
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Unit/Section</label>
                            <span style="font-weight: 600; font-size: 1.05rem;">
                                <?php
                                $employeeUnitLabel = '-';
                                if (!empty($viewData['employee_office'])) {
                                    $employeeUnitLabel = SDO_OFFICES[$viewData['employee_office']] ?? $viewData['employee_office'];
                                }
                                echo htmlspecialchars($employeeUnitLabel);
                                ?>
                            </span>
                        </div>
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Destination</label>
                            <span
                                style="font-weight: 600; font-size: 1.05rem; color: var(--text-primary);"><?php echo htmlspecialchars($viewData['destination']); ?></span>
                        </div>
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md); grid-column: 1 / -1;">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Date & Time</label>
                            <span style="font-weight: 600; font-size: 1.05rem; color: var(--text-primary);"><i
                                    class="fas fa-calendar-alt"
                                    style="color: var(--primary); margin-right: 6px;"></i><?php echo date('F j, Y - g:i A', strtotime($viewData['date_time'])); ?></span>
                        </div>
                        <div class="detail-item"
                            style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md); grid-column: 1 / -1;">
                            <label style="color: var(--primary); font-weight: 700; margin-bottom: 8px;">Purpose of
                                Travel</label>
                            <span class="narration-text"
                                style="font-weight: 500; font-size: 1.05rem; color: var(--text-primary);"><?php echo nl2br(htmlspecialchars($viewData['purpose_of_travel'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($viewData['status'] !== 'pending' && ($viewData['approver_name'] || $viewData['rejection_reason'])): ?>
                <!-- Approval Details -->
                <div class="detail-card"
                    style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl); margin-top: 24px;">
                    <div class="detail-card-header"
                        style="padding: 20px 24px; background: <?php echo $viewData['status'] === 'approved' ? 'var(--success-bg)' : 'var(--danger-bg)'; ?>; border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <h3
                            style="font-size: 1.1rem; color: <?php echo $viewData['status'] === 'approved' ? 'var(--success)' : 'var(--danger)'; ?>; font-weight: 700;">
                            <i
                                class="fas fa-<?php echo $viewData['status'] === 'approved' ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $viewData['status'] === 'approved' ? 'Approval' : 'Disapproval'; ?> Details
                        </h3>
                    </div>
                    <div class="detail-card-body" style="padding: 24px;">
                        <div class="detail-grid"
                            style="display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 24px;">
                            <?php if ($viewData['status'] === 'approved'): ?>
                                <div class="detail-item"
                                    style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                                    <label style="color: var(--success); font-weight: 700; margin-bottom: 8px;">Approved By</label>
                                    <span
                                        style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($viewData['approver_name']); ?></span>
                                </div>
                                <div class="detail-item"
                                    style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                                    <label style="color: var(--success); font-weight: 700; margin-bottom: 8px;">Position</label>
                                    <span
                                        style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($viewData['approver_position'] ?: '-'); ?></span>
                                </div>
                                <div class="detail-item"
                                    style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                                    <label style="color: var(--success); font-weight: 700; margin-bottom: 8px;">Approval
                                        Date</label>
                                    <span
                                        style="font-weight: 600; font-size: 1.05rem;"><?php echo $viewData['approval_date'] ? date('F j, Y', strtotime($viewData['approval_date'])) : '-'; ?></span>
                                </div>
                                <div class="detail-item"
                                    style="border: none; background: var(--bg-primary); padding: 16px; border-radius: var(--radius-md);">
                                    <label style="color: var(--success); font-weight: 700; margin-bottom: 8px;">Approval
                                        Time</label>
                                    <span style="font-weight: 600; font-size: 1.05rem;"><i class="far fa-clock"
                                            style="margin-right: 4px;"></i><?php echo !empty($viewData['approving_time']) ? date('g:i A', strtotime($viewData['approving_time'])) : '-'; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="detail-item"
                                    style="border: none; background: var(--danger-bg); padding: 16px; border-radius: var(--radius-md); grid-column: 1 / -1;">
                                    <label style="color: var(--danger); font-weight: 700; margin-bottom: 8px;">Disapproval
                                        Reason</label>
                                    <span
                                        style="font-weight: 600; font-size: 1.05rem; color: var(--danger);"><?php echo htmlspecialchars($viewData['rejection_reason'] ?: 'No reason provided'); ?></span>
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
            // SDS is view-only for Locator Slip — cannot approve
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
                <div class="detail-card action-card"
                    style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl);">
                    <div class="detail-card-header"
                        style="padding: 20px 24px; background: var(--bg-secondary); border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <h3 style="font-size: 1.1rem; color: var(--primary-dark); font-weight: 700;"><i class="fas fa-tasks"
                                style="color: var(--accent);"></i> Actions</h3>
                    </div>
                    <div class="detail-card-body" style="padding: 24px;">
                        <form method="POST" action="" style="margin-bottom: 12px;">
                            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                            <input type="hidden" name="action" value="approve">
                            <input type="hidden" name="id" value="<?php echo $viewData['id']; ?>">
                            <button type="button" class="btn btn-success btn-block"
                                style="padding: 14px; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(16, 185, 129, 0.3); transition: all 0.2s;"
                                onmouseover="this.style.transform='translateY(-2px)';"
                                onmouseout="this.style.transform='translateY(0)';"
                                onclick="openApproveModal(<?php echo $viewData['id']; ?>)">
                                <i class="fas fa-check"></i> Approve Request
                            </button>
                        </form>

                        <button type="button" class="btn btn-danger btn-block"
                            style="padding: 14px; font-size: 1.05rem; background: var(--danger-bg); color: var(--danger); border: none; box-shadow: none; transition: all 0.2s;"
                            onmouseover="this.style.background='var(--danger)'; this.style.color='white';"
                            onmouseout="this.style.background='var(--danger-bg)'; this.style.color='var(--danger)';"
                            onclick="showDisapproveModal(<?php echo $viewData['id']; ?>)">
                            <i class="fas fa-times"></i> Disapprove Request
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($lsModel->canUserEdit($viewData, $auth->getUserId())): ?>
                <div class="detail-card"
                    style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl);">
                    <div class="detail-card-header"
                        style="padding: 20px 24px; background: var(--bg-secondary); border-bottom: 1px solid rgba(0,0,0,0.05);">
                        <h3 style="font-size: 1.1rem; color: var(--primary-dark); font-weight: 700;"><i class="fas fa-edit"
                                style="color: var(--accent);"></i> Edit</h3>
                    </div>
                    <div class="detail-card-body" style="padding: 24px;">
                        <a href="<?php echo navUrl('/locator-slips.php?edit=' . $viewData['id']); ?>"
                            class="btn btn-primary btn-block"
                            style="padding: 14px; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(15, 76, 117, 0.2); transition: all 0.2s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='translateY(0)';">
                            <i class="fas fa-edit"></i> Edit Request
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($viewData['status'] === 'approved'): ?>
                <div class="detail-card"
                    style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl);">
                    <div class="detail-card-header"
                        style="padding: 20px 24px; background: var(--bg-secondary); border-bottom: 1px solid rgba(0,0,0,0.05);">
                            <h3 style="font-size: 1.1rem; color: var(--primary-dark); font-weight: 700;"><i class="fas fa-download"></i> Download</h3>
                    </div>
                    <div class="detail-card-body" style="padding: 24px;">
                        <a href="<?php echo navUrl('/api/generate-docx.php?type=ls&id=' . $viewData['id']); ?>"
                            class="btn btn-primary btn-block"
                            style="padding: 14px; font-size: 1.05rem; box-shadow: 0 4px 12px rgba(15, 76, 117, 0.2); transition: all 0.2s;"
                            onmouseover="this.style.transform='translateY(-2px)';"
                            onmouseout="this.style.transform='translateY(0)';">
                            <i class="fas fa-file-pdf"></i> Download PDF
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Filed By -->
            <div class="detail-card"
                style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.04); border-radius: var(--radius-xl);">
                <div class="detail-card-header"
                    style="padding: 20px 24px; background: var(--bg-secondary); border-bottom: 1px solid rgba(0,0,0,0.05);">
                    <h3 style="font-size: 1.1rem; color: var(--primary-dark); font-weight: 700;"><i
                            class="fas fa-user"></i> Filed By</h3>
                </div>
                <div class="detail-card-body" style="padding: 24px;">
                    <div class="detail-item" style="border: none; margin-bottom: 16px;">
                        <label style="color: var(--primary); font-weight: 700; margin-bottom: 4px;">Name</label>
                        <span
                            style="font-weight: 600; font-size: 1.05rem;"><?php echo htmlspecialchars($viewData['filed_by_name']); ?></span>
                    </div>
                    <div class="detail-item" style="border: none;">
                        <label style="color: var(--primary); font-weight: 700; margin-bottom: 4px;">Email</label>
                        <span
                            style="font-weight: 500; font-size: 0.95rem; color: var(--text-secondary);"><?php echo htmlspecialchars($viewData['filed_by_email']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="margin-right: 8px; color: var(--success);"></i> Approve Locator
                    Slip</h3>
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

    <!-- Disapprove Modal -->
    <div class="modal-overlay" id="disapproveModal">
        <div class="modal">
            <div class="modal-header">
                <h3>Disapprove Locator Slip</h3>
                <button class="modal-close" onclick="closeDisapproveModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="disapprove">
                    <input type="hidden" name="id" id="disapproveId" value="">

                    <div class="form-group">
                        <label class="form-label">Reason for Disapproval (Optional)</label>
                        <textarea name="rejection_reason" class="form-control" rows="4"
                            placeholder="Enter reason..."></textarea>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeDisapproveModal()">Cancel</button>
                    <button type="submit" class="btn btn-danger">Disapprove Request</button>
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

        function showDisapproveModal(id) {
            document.getElementById('disapproveId').value = id;
            document.getElementById('disapproveModal').classList.add('active');
        }
        function closeDisapproveModal() {
            document.getElementById('disapproveModal').classList.remove('active');
        }
    </script>

<?php else: ?>
    <!-- List View -->
    <div class="page-header"
        style="background: #164f77; color: white; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(22, 79, 119, 0.2); border: none;">
        <div class="header-content">
            <h2
                style="margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 6px; font-weight: 800; letter-spacing: -0.5px; color: white;">
                <i class="fas fa-map-marker-alt" style="color: rgba(255,255,255,0.8); font-size: 1rem;"></i> Locator Slip
                Management
            </h2>
            <p style="margin: 2px 0 0 0; color: rgba(255,255,255,0.8); font-size: 0.75rem;">
                Track and manage employee locator slip &bull; <?php echo $totalRequests; ?>
                Record<?php echo $totalRequests !== 1 ? 's' : ''; ?>
            </p>
        </div>
        <?php if ($canCreateTravelRequests): ?>
            <button type="button" class="btn"
                style="background: white; color: var(--primary-dark); font-weight: 700; border: none; padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"
                onclick="openNewModal()">
                <i class="fas fa-plus"></i> New Locator Slip
            </button>
        <?php endif; ?>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar"
        style="background: white; padding: 24px; border-radius: var(--radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05);">
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
                    <option value="pending" <?php echo ($_GET['status'] ?? '') === 'pending' ? 'selected' : ''; ?>>Pending
                    </option>
                    <option value="approved" <?php echo ($_GET['status'] ?? '') === 'approved' ? 'selected' : ''; ?>>Approved
                    </option>
                    <option value="disapproved" <?php echo ($_GET['status'] ?? '') === 'disapproved' ? 'selected' : ''; ?>>Disapproved
                    </option>
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
                <a href="<?php echo navUrl('/locator-slips.php'); ?>" class="btn btn-secondary btn-sm"><i
                        class="fas fa-times"></i> Clear</a>
            </div>
        </form>
    </div>

    <!-- Data Table -->
    <div class="data-card"
        style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
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
                                    <h3>No Locator Slip found</h3>
                                    <p>Create a new Locator Slip to get started</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $ls): ?>
                            <tr style="transition: all 0.2s ease; border-bottom: 1px solid var(--border-light);"
                                onmouseover="this.style.backgroundColor='var(--bg-secondary)'; this.style.transform='scale(1.002)';"
                                onmouseout="this.style.backgroundColor=''; this.style.transform='scale(1)';">
                                <td style="padding: 18px 16px;">
                                    <a href="<?php echo navUrl('/locator-slips.php?view=' . $ls['id']); ?>" class="ref-link"
                                        style="background: var(--bg-secondary); padding: 6px 12px; border-radius: 6px; font-weight: 700;">
                                        <?php echo htmlspecialchars($ls['ls_control_no']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="cell-primary"><?php echo htmlspecialchars($ls['employee_name']); ?></div>
                                    <div class="cell-secondary"><?php echo htmlspecialchars($ls['employee_position'] ?: ''); ?>
                                    </div>
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
                                    <div class="action-buttons" style="display: flex; gap: 8px;">
                                        <a href="<?php echo navUrl('/locator-slips.php?view=' . $ls['id']); ?>" class="btn btn-icon"
                                            title="View"
                                            style="background: var(--bg-secondary); color: var(--primary-dark); border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($lsModel->canUserEdit($ls, $auth->getUserId())): ?>
                                            <a href="<?php echo navUrl('/locator-slips.php?edit=' . $ls['id']); ?>" class="btn btn-icon"
                                                title="Edit"
                                                style="background: var(--warning-bg); color: var(--warning); border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;"
                                                onmouseover="this.style.background='var(--warning)'; this.style.color='white';"
                                                onmouseout="this.style.background='var(--warning-bg)'; this.style.color='var(--warning)';">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($ls['status'] === 'approved'): ?>
                                            <a href="<?php echo navUrl('/api/generate-docx.php?type=ls&id=' . $ls['id']); ?>"
                                                class="btn btn-icon" title="Download PDF"
                                                style="background: var(--success-bg); color: var(--success); border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;"
                                                onmouseover="this.style.background='var(--success)'; this.style.color='white';"
                                                onmouseout="this.style.background='var(--success-bg)'; this.style.color='var(--success)';">
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
                        <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>"
                            class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo navUrl('/locator-slips.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($canCreateTravelRequests): ?>
    <!-- New Locator Slip Modal -->
    <div class="modal-overlay" id="newModal" <?php echo $action === 'new' ? 'class="active"' : ''; ?>>
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3>New Locator Slip</h3>
                <button class="modal-close" onclick="closeNewModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="create">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employee Name <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="text" name="employee_name" class="form-control" required
                                    value="<?php echo htmlspecialchars($formData['employee_name']); ?>">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <div class="input-with-icon">
                                <input type="text" name="employee_position" class="form-control"
                                    value="<?php echo htmlspecialchars($formData['employee_position']); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Unit/Section</label>
                        <div class="input-with-icon">
                            <input type="text" class="form-control" readonly
                                value="<?php echo htmlspecialchars($formData['employee_unit_section'] ?: '-'); ?>"
                                style="background: var(--bg-secondary); cursor: not-allowed;">
                        </div>
                        <input type="hidden" name="employee_office"
                            value="<?php echo htmlspecialchars($formData['employee_office']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Travel Type <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <select name="travel_type" class="form-control" required>
                                    <?php foreach (TRAVEL_TYPES as $code => $label): ?>
                                        <option value="<?php echo $code; ?>"><?php echo htmlspecialchars($label); ?></option>
                                    <?php endforeach; ?>
                                </select>
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date & Time <span class="required">*</span></label>
                            <div class="input-with-icon">
                                <input type="datetime-local" name="date_time" class="form-control" required
                                    min="<?php echo date('Y-m-d\TH:i'); ?>" value="<?php echo date('Y-m-d\TH:i'); ?>">
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Destination <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <input type="text" name="destination" class="form-control" required
                                placeholder="Where are you going?">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                        <div class="input-with-icon">
                            <textarea name="purpose_of_travel" class="form-control" rows="3" required
                                placeholder="Describe the purpose of your travel..."></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeNewModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary">Submit Request</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Manila timezone offset (UTC+8)
        const MANILA_OFFSET = 8 * 60; // minutes

        // Get current time in Manila timezone
        function getManilaTime() {
            const now = new Date();
            const utc = now.getTime() + (now.getTimezoneOffset() * 60000);
            return new Date(utc + (MANILA_OFFSET * 60000));
        }

        // Format date for datetime-local input (YYYY-MM-DDTHH:MM)
        function formatDatetimeLocal(date) {
            const year = date.getFullYear();
            const month = String(date.getMonth() + 1).padStart(2, '0');
            const day = String(date.getDate()).padStart(2, '0');
            const hours = String(date.getHours()).padStart(2, '0');
            const minutes = String(date.getMinutes()).padStart(2, '0');
            return `${year}-${month}-${day}T${hours}:${minutes}`;
        }

        // Update datetime input min value and validate
        function updateDateTimeInputs() {
            const manilaTime = getManilaTime();
            const minDateTime = formatDatetimeLocal(manilaTime);

            // Update all datetime-local inputs
            document.querySelectorAll('input[type="datetime-local"][name="date_time"]').forEach(input => {
                input.setAttribute('min', minDateTime);

                // If current value is in the past, update it
                if (input.value && input.value < minDateTime) {
                    input.value = minDateTime;
                }
            });
        }

        // Validate datetime before form submission
        function validateDateTime(form) {
            const dateTimeInput = form.querySelector('input[name="date_time"]');
            if (!dateTimeInput) return true;

            const manilaTime = getManilaTime();
            const minDateTime = formatDatetimeLocal(manilaTime);
            const selectedDateTime = dateTimeInput.value;

            if (selectedDateTime < minDateTime) {
                showAlertModal('The selected date and time is in the past. Please select a current or future time.', {
                    title: 'Invalid Date/Time',
                    tone: 'error'
                });
                dateTimeInput.value = minDateTime;
                dateTimeInput.focus();
                return false;
            }
            return true;
        }

        function openNewModal() {
            document.getElementById('newModal').classList.add('active');
            // Update min datetime when modal opens
            updateDateTimeInputs();
        }
        function closeNewModal() {
            document.getElementById('newModal').classList.remove('active');
            // Remove action param from URL
            const url = new URL(window.location);
            url.searchParams.delete('action');
            window.history.replaceState({}, '', url);
        }

        // Initialize on page load
        document.addEventListener('DOMContentLoaded', function () {
            // Set initial min values
            updateDateTimeInputs();

            // Update min values every minute to prevent past time selection
            setInterval(updateDateTimeInputs, 60000);

            // Add form validation
            document.querySelectorAll('form').forEach(form => {
                form.addEventListener('submit', function (e) {
                    if (!validateDateTime(this)) {
                        e.preventDefault();
                    }
                });
            });
        });

        // Auto-open modal if action=new
        <?php if ($canCreateTravelRequests && $action === 'new'): ?>
            document.addEventListener('DOMContentLoaded', openNewModal);
        <?php endif; ?>
    </script>
    <?php endif; ?>

    <style>
        /* Premium Form Enhancements */
        .modal {
            border-radius: var(--radius-xl);
            box-shadow: 0 25px 50px -12px rgba(0, 0, 0, 0.25);
            background: #ffffff;
            border: none;
            overflow: hidden;
            transform: scale(0.95);
            opacity: 0;
            transition: all 0.3s cubic-bezier(0.175, 0.885, 0.32, 1.275);
        }

        .modal-overlay.active .modal {
            transform: scale(1);
            opacity: 1;
        }

        .modal-header {
            background: linear-gradient(135deg, var(--primary-dark) 0%, var(--primary) 100%);
            color: white;
            padding: 24px 30px;
            border-bottom: none;
        }

        .modal-header h3 {
            color: white;
            font-size: 1.3rem;
            font-weight: 700;
        }

        .modal-close {
            color: rgba(255, 255, 255, 0.8);
            background: rgba(255, 255, 255, 0.1);
            width: 32px;
            height: 32px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            transition: all 0.2s;
        }

        .modal-close:hover {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            transform: rotate(90deg);
        }

        .modal-body {
            padding: 30px;
        }

        .modal-footer {
            padding: 20px 30px;
            background: var(--bg-secondary);
            border-top: 1px solid rgba(0, 0, 0, 0.05);
        }

        .form-label {
            font-weight: 700;
            color: var(--primary-dark);
            margin-bottom: 8px;
            font-size: 0.9rem;
        }

        .input-with-icon {
            position: relative;
            display: flex;
            align-items: center;
        }

        .input-with-icon .form-control {
            padding-left: 16px;
        }

        .form-control {
            border: 2px solid var(--border-color);
            border-radius: var(--radius-md);
            padding: 12px 16px;
            font-size: 0.95rem;
            transition: all 0.2s ease;
            background: var(--bg-primary);
            box-shadow: none;
        }

        .form-control:focus {
            border-color: var(--primary);
            background: #ffffff;
            box-shadow: 0 0 0 4px rgba(15, 76, 117, 0.1);
        }

        .form-control:hover {
            border-color: var(--primary-light);
        }

        /* Beautiful Action Buttons */
        .btn {
            border-radius: var(--radius-md);
            font-weight: 600;
            letter-spacing: 0.3px;
            transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--primary-light) 100%);
            box-shadow: 0 4px 12px rgba(15, 76, 117, 0.2);
            border: none;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 16px rgba(15, 76, 117, 0.3);
        }

        .btn-secondary {
            background: white;
            border: 2px solid var(--border-color);
            color: var(--text-primary);
        }

        .btn-secondary:hover {
            background: var(--bg-secondary);
            border-color: var(--text-muted);
            transform: translateY(-2px);
        }
    </style>
<?php endif; ?>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>