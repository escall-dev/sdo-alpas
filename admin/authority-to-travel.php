<?php
/**
 * Authority to Travel Management Page
 * SDO ALPAS - View, create, and approve AT requests
 * With Unit-Based and Role-Based Routing Logic
 */
ob_start(); // Buffer output to allow header() redirects after HTML output

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';
require_once __DIR__ . '/../services/TrackingService.php';

$atModel = new AuthorityToTravel();
$trackingService = new TrackingService();

$action = $_GET['action'] ?? '';
$viewId = $_GET['view'] ?? '';
$editId = $_GET['edit'] ?? '';
$type = $_GET['type'] ?? 'local'; // local, outside_region, international, personal
$message = $_GET['msg'] ?? '';
$error = '';
$canCreateTravelRequests = !$auth->isSuperAdmin();

if (!$canCreateTravelRequests && $action === 'new') {
    $action = '';
    $error = 'Superadmin accounts cannot file travel requests.';
}

// Get current user info for routing
// Use effective role ID/Name which accounts for OIC delegation
$currentRoleId = $auth->getEffectiveRoleId();
$currentRoleName = $auth->getEffectiveRoleName();
$currentOffice = $currentUser['employee_office'] ?? '';
$currentOfficeId = $currentUser['office_id'] ?? null;
$isActingAsOIC = $auth->isActingAsOIC();

/**
 * Convert technical database errors into plain-language messages
 * so non-technical users can understand what went wrong.
 */
function friendlyDbErrorAT($exceptionMessage)
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
        try {
            $scope = $_POST['travel_scope'] ?? 'local';
            $localType = $_POST['travel_type'] ?? 'within_region';
            // Within Region forces Official; Outside Region and International allow Official/Personal
            $category = ($scope === 'local' && $localType === 'within_region') ? 'official' : ($_POST['travel_category'] ?? 'official');

            // Prepare data for validation
            $data = [
                'employee_name' => $_POST['employee_name'],
                'employee_position' => $_POST['employee_position'],
                'permanent_station' => $_POST['permanent_station'],
                'purpose_of_travel' => $_POST['purpose_of_travel'],
                'host_of_activity' => $_POST['host_of_activity'] ?? null,
                'date_from' => $_POST['date_from'],
                'date_to' => $_POST['date_to'],
                'destination' => $_POST['destination'],
                'fund_source' => $_POST['fund_source'] ?? null,
                'inclusive_dates' => $_POST['inclusive_dates'] ?? null,
                'requesting_employee_name' => $_POST['requesting_employee_name'] ?? $_POST['employee_name'],
                'request_date' => date('Y-m-d'),
                'travel_category' => $category,
                'travel_scope' => $scope,
                'travel_type' => ($scope === 'local') ? $localType : null,
                'user_id' => $auth->getUserId()
            ];

            // Validate submission
            $validation = $atModel->validateSubmission($data, $currentRoleId);

            if ($validation['redirect'] === 'locator_slips') {
                $error = 'Same-day travel should be filed as a Locator Slip. Please use the Locator Slip form instead.';
            } elseif (!$validation['valid']) {
                $error = implode('. ', $validation['errors']);
            } else {
                // Generate tracking number
                $trackingNo = $trackingService->generateATNumber($category, $scope);
                $data['at_tracking_no'] = $trackingNo;

                // Create with routing
                $id = $atModel->create($data, $currentRoleId, $currentOfficeId, $currentOffice);
                $auth->logActivity('CREATE_AT', 'AT', $id, 'Created AT: ' . $trackingNo);

                // Log routing decision with full context
                $createdAt = $atModel->getById($id);
                $routingContext = json_encode([
                    'travel_classification' => $category . '/' . $scope . ($localType ? '/' . $localType : ''),
                    'position' => $currentUser['employee_position'] ?? '',
                    'destination_scope' => $scope,
                    'assigned_recommending_approver' => $createdAt['current_approver_role'] ?? null,
                    'assigned_final_approver' => $createdAt['final_approver_role'] ?? null,
                    'forwarded_to_ro' => !empty($createdAt['forwarded_to_ro']),
                    'routing_stage' => $createdAt['routing_stage'] ?? null
                ]);
                $auth->logActivity('ROUTING_DECISION', 'AT', $id, $routingContext);

                if (!empty($createdAt['forwarded_to_ro'])) {
                    $msg = 'Authority to Travel filed and forwarded to Regional Office. Tracking Number: ' . $trackingNo;
                } else {
                    $msg = 'Authority to Travel filed successfully! Tracking Number: ' . $trackingNo;
                }
                header('Location: ' . navUrl('/authority-to-travel.php') . '&msg=' . urlencode($msg));
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to create Authority to Travel: ' . $e->getMessage();
        }
        }
    }

    // Handle approve action (by Unit Heads, ASDS, or SDS at final stage)
    if ($postAction === 'approve' && ($auth->isUnitHead() || $auth->isASDS() || $auth->isSDS())) {
        $id = $_POST['id'];
        $at = $atModel->getById($id);

        if ($at && in_array($at['status'], ['pending', 'recommended'])) {
            $availableAction = $atModel->getAvailableAction($at, $currentRoleId, $currentRoleName);

            if ($availableAction === 'approve') {
                // Check if this is an OIC approval
                $isOIC = $auth->isActingAsOIC();

                try {
                    $atModel->approve($id, $auth->getUserId(), $currentUser['full_name'], $currentRoleId, $isOIC);

                    // Log with OIC prefix if applicable
                    $actionType = $isOIC ? 'OIC-APPROVAL' : 'APPROVE_AT';
                    $auth->logActivity($actionType, 'AT', $id, 'Approved AT: ' . $at['at_tracking_no']);
                    header('Location: ' . navUrl('/authority-to-travel.php?view=' . $id) . '&msg=' . urlencode('Authority to Travel approved successfully!'));
                    exit;
                } catch (Exception $e) {
                    $error = friendlyDbErrorAT($e->getMessage());
                }
            } else {
                $error = 'You do not have permission to approve this request.';
            }
        }
    }

    // Handle recommend action (by Unit Heads, ASDS, or SDS)
    if ($postAction === 'recommend' && ($auth->isUnitHead() || $auth->isASDS() || $auth->isSDS())) {
        $id = $_POST['id'];
        $at = $atModel->getById($id);

        if ($at && $at['status'] === 'pending') {
            $availableAction = $atModel->getAvailableAction($at, $currentRoleId, $currentRoleName);

            if ($availableAction === 'recommend') {
                // Check if this is an OIC recommendation
                $isOIC = $auth->isActingAsOIC();

                $atModel->recommend($id, $auth->getUserId(), $currentUser['full_name'], $currentRoleId);

                // Log with OIC prefix if applicable
                $actionType = $isOIC ? 'OIC-RECOMMEND' : 'RECOMMEND_AT';
                $auth->logActivity($actionType, 'AT', $id, 'Recommended AT: ' . $at['at_tracking_no']);

                // Check if AT was forwarded to RO after recommendation
                $updatedAt = $atModel->getById($id);
                if (!empty($updatedAt['forwarded_to_ro'])) {
                    $finalRole = $updatedAt['final_approver_role'] ?? 'RD';
                    $externalLabel = ($finalRole === 'DEPED_SEC') ? 'DepEd Secretary' : 'RD';
                    $msg = 'Authority to Travel recommended and forwarded to Regional Office for ' . $externalLabel . ' approval.';
                } else {
                    $msg = 'Authority to Travel recommended for approval.';
                }
                header('Location: ' . navUrl('/authority-to-travel.php?view=' . $id) . '&msg=' . urlencode($msg));
                exit;
            } else {
                $error = 'You do not have permission to recommend this request.';
            }
        }
    }

    // Handle edit action
    if ($postAction === 'edit') {
        try {
            $id = $_POST['id'];
            $at = $atModel->getById($id);

            if (!$at) {
                $error = 'Authority to Travel not found.';
            } elseif (!$atModel->canUserEdit($at, $auth->getUserId())) {
                $error = 'You cannot edit this Authority to Travel.';
            } else {
                $scope = $_POST['travel_scope'] ?? 'local';
                $localType = $_POST['travel_type'] ?? 'within_region';
                // Within Region forces Official; Outside Region and International allow Official/Personal
                $category = ($scope === 'local' && $localType === 'within_region') ? 'official' : ($_POST['travel_category'] ?? 'official');

                $data = [
                    'employee_name' => $_POST['employee_name'],
                    'employee_position' => $_POST['employee_position'],
                    'permanent_station' => $_POST['permanent_station'],
                    'purpose_of_travel' => $_POST['purpose_of_travel'],
                    'host_of_activity' => $_POST['host_of_activity'] ?? null,
                    'date_from' => $_POST['date_from'],
                    'date_to' => $_POST['date_to'],
                    'destination' => $_POST['destination'],
                    'fund_source' => $_POST['fund_source'] ?? null,
                    'travel_category' => $category,
                    'travel_scope' => $scope,
                    'travel_type' => ($scope === 'local') ? $localType : null
                ];

                $atModel->update($id, $data, $auth->getUserId());
                $auth->logActivity('UPDATE_AT', 'AT', $id, 'Updated AT: ' . $at['at_tracking_no']);
                header('Location: ' . navUrl('/authority-to-travel.php?view=' . $id) . '&msg=' . urlencode('Authority to Travel updated successfully!'));
                exit;
            }
        } catch (Exception $e) {
            $error = 'Failed to update Authority to Travel: ' . $e->getMessage();
        }
    }

    // Handle executive approve action (by SDS only, not Superadmin)
    if ($postAction === 'executive_approve' && $auth->isSDS()) {
        $id = $_POST['id'];
        $at = $atModel->getById($id);

        if ($at && !in_array($at['status'], ['approved', 'disapproved'])) {
            try {
                $atModel->executiveApprove($id, $auth->getUserId(), $currentUser['full_name']);
                $auth->logActivity('APPROVE_AT', 'AT', $id, 'Executive approved AT: ' . $at['at_tracking_no']);
                header('Location: ' . navUrl('/authority-to-travel.php?view=' . $id) . '&msg=' . urlencode('Authority to Travel approved by SDS (Executive Override)!'));
                exit;
            } catch (Exception $e) {
                $error = friendlyDbErrorAT($e->getMessage());
            }
        }
    }

    // Handle disapprove action (by any approver)
    if ($postAction === 'disapprove' && $auth->canActOnAT()) {
        $id = $_POST['id'];
        $reason = $_POST['rejection_reason'] ?? null;
        $at = $atModel->getById($id);

        if ($at && !in_array($at['status'], ['approved', 'disapproved'])) {
            // Check if user can act on this AT
            if ($atModel->canUserActOn($at, $currentRoleId, $currentRoleName)) {
                $atModel->disapprove($id, $auth->getUserId(), $reason);
                $auth->logActivity('DISAPPROVE_AT', 'AT', $id, 'Disapproved AT: ' . $at['at_tracking_no']);
                header('Location: ' . navUrl('/authority-to-travel.php?view=' . $id) . '&msg=' . urlencode('Authority to Travel disapproved.'));
                exit;
            } else {
                $error = 'You do not have permission to disapprove this request.';
            }
        }
    }
}

// View single request
$viewData = null;
if ($viewId) {
    $viewData = $atModel->getById($viewId);
    if (!$viewData) {
        $error = 'Authority to Travel not found.';
    } elseif ($auth->isEmployee() && $viewData['user_id'] != $auth->getUserId()) {
        $error = 'You do not have permission to view this request.';
        $viewData = null;
    } elseif ($auth->isUnitHead()) {
        // Unit heads can only view requests from their supervised offices (or their own)
        $supervisedOffices = $atModel->getSupervisedOfficesForRole($currentRoleId);
        $supervisedOfficeIds = $atModel->getSupervisedOfficeIdsForRole($currentRoleId);
        $matchesName = $viewData['requester_office'] && in_array($viewData['requester_office'], $supervisedOffices);
        $matchesId = $viewData['requester_office_id'] && in_array((int) $viewData['requester_office_id'], $supervisedOfficeIds);
        if (
            $viewData['user_id'] != $auth->getUserId() &&
            !$matchesName && !$matchesId &&
            $viewData['current_approver_role'] !== $currentRoleName
        ) {
            $error = 'You do not have permission to view this request.';
            $viewData = null;
        }
    }
}

// Get list data based on role
$filters = [];
if ($auth->isEmployee()) {
    // Regular employees see only their own requests
    $filters['user_id'] = $auth->getUserId();
} elseif ($auth->isUnitHead()) {
    // Unit heads see only requests from their supervised offices
    $supervisedOffices = $atModel->getSupervisedOfficesForRole($currentRoleId);
    $supervisedOfficeIds = $atModel->getSupervisedOfficeIdsForRole($currentRoleId);
    $filters['supervised_offices'] = array_merge($supervisedOffices, $supervisedOfficeIds);
    // Show only pending in their queue by default
    if (empty($_GET['show_all'])) {
        $filters['current_approver_role'] = $currentRoleName;
    }
} elseif ($auth->isASDS() || $auth->isSDS()) {
    // ASDS/SDS sees requests in final stage (their queue) by default
    if (empty($_GET['show_all'])) {
        $filters['current_approver_role'] = $currentRoleName;
    }
}
// Superadmin sees everything (no filters applied)

// Add comprehensive filters
if (!empty($_GET['status'])) {
    $filters['status'] = $_GET['status'];
}
if (!empty($_GET['category'])) {
    $filters['travel_category'] = $_GET['category'];
}
if (!empty($_GET['scope'])) {
    $filters['travel_scope'] = $_GET['scope'];
}
if (!empty($_GET['travel_type'])) {
    $filters['travel_type'] = $_GET['travel_type'];
}
if (!empty($_GET['unit'])) {
    $filters['unit'] = $_GET['unit'];
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
$requests = $atModel->getAll($filters, $perPage, $offset, $currentRoleId, $auth->getUserId());
$totalRequests = $atModel->getCount($filters, $currentRoleId, $auth->getUserId());
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

// Pre-fill form
$formData = [
    'employee_name' => $currentUser['full_name'],
    'employee_position' => $currentUser['employee_position'] ?? '',
    'permanent_station' => 'SDO San Pedro City'
];

// Determine default type for form: scope-first, then category when applicable
$formCategory = 'official';
$formScope = 'local';
$formLocalType = 'within_region';
if ($type === 'outside_region') {
    $formScope = 'local';
    $formLocalType = 'outside_region';
} elseif ($type === 'international') {
    $formScope = 'international';
} elseif ($type === 'personal') {
    $formCategory = 'personal';
    $formScope = 'international';
}
?>

<?php if ($isActingAsOIC): ?>
    <!-- OIC Notice Banner -->
    <div class="alert"
        style="background: linear-gradient(135deg, #1b4a9a 0%, #1b4a9a 100%); color: white; border: none; margin-bottom: 20px;">
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

<?php if ($viewData): ?>
    <!-- View Single Request -->
    <div class="page-header">
        <?php if (isset($_GET['from']) && $_GET['from'] === 'my-requests'): ?>
            <a href="<?php echo navUrl('/my-requests.php'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to My Requests
            </a>
        <?php else: ?>
            <a href="<?php echo navUrl('/authority-to-travel.php'); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to List
            </a>
        <?php endif; ?>
    </div>

    <div class="complaint-detail-grid">
        <div class="complaint-main">
            <!-- Reference Card -->
            <div class="detail-card ref-card">
                <div class="ref-header">
                    <div class="ref-number"><?php echo htmlspecialchars($viewData['at_tracking_no']); ?></div>
                    <div class="ref-date">Filed on <?php echo date('F j, Y - g:i A', strtotime($viewData['created_at'])); ?>
                    </div>
                </div>
                <div class="ref-unit">
                    <?php echo getStatusBadge($viewData['status']); ?>
                    <span
                        class="unit-badge large"><?php echo AuthorityToTravel::getTypeLabel($viewData['travel_category'], $viewData['travel_scope'], $viewData['travel_type'] ?? null); ?></span>
                </div>
            </div>

            <!-- Request Details -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-info-circle"></i> Travel Details</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Employee Name</label>
                            <span><?php echo htmlspecialchars($viewData['employee_name']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Position</label>
                            <span><?php echo htmlspecialchars($viewData['employee_position'] ?: '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Office/Division</label>
                            <span><?php echo htmlspecialchars($viewData['requester_office'] ?: '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Unit</label>
                            <span><?php echo htmlspecialchars($viewData['filed_by_office'] ?: ($viewData['requester_office'] ?: '-')); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Permanent Station</label>
                            <span><?php echo htmlspecialchars($viewData['permanent_station'] ?: '-'); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Destination</label>
                            <span><?php echo htmlspecialchars($viewData['destination']); ?></span>
                        </div>
                        <div class="detail-item">
                            <label>Travel Dates</label>
                            <span><?php echo date('M j, Y', strtotime($viewData['date_from'])); ?> to
                                <?php echo date('M j, Y', strtotime($viewData['date_to'])); ?></span>
                        </div>
                        <?php if ($viewData['travel_category'] === 'official'): ?>
                            <div class="detail-item">
                                <label>Host of Activity</label>
                                <span><?php echo htmlspecialchars($viewData['host_of_activity'] ?: '-'); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Fund Source</label>
                                <span><?php echo htmlspecialchars($viewData['fund_source'] ?: '-'); ?></span>
                            </div>
                        <?php endif; ?>
                        <div class="detail-item">
                            <label>Purpose of Travel</label>
                            <span
                                class="narration-text"><?php echo nl2br(htmlspecialchars($viewData['purpose_of_travel'])); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <?php if ($viewData['status'] !== 'pending' && $viewData['status'] !== 'recommended'): ?>
                <!-- Approval/Disapproval Details -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i
                                class="fas fa-<?php echo $viewData['status'] === 'approved' ? 'check-circle' : 'times-circle'; ?>"></i>
                            <?php echo $viewData['status'] === 'approved' ? 'Approval' : 'Disapproval'; ?> Details
                        </h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-grid">
                            <?php if ($viewData['status'] === 'approved'): ?>
                                <div class="detail-item">
                                    <label>Recommending Authority</label>
                                    <span><?php echo htmlspecialchars($viewData['recommending_authority_name'] ?: '-'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Recommendation Date</label>
                                    <span><?php echo $viewData['recommending_date'] ? date('F j, Y', strtotime($viewData['recommending_date'])) : '-'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Approving Authority</label>
                                    <span><?php echo htmlspecialchars($viewData['approving_authority_name'] ?: '-'); ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Approval Date</label>
                                    <span><?php echo $viewData['approval_date'] ? date('F j, Y', strtotime($viewData['approval_date'])) : '-'; ?></span>
                                </div>
                                <div class="detail-item">
                                    <label>Approval Time</label>
                                    <span><?php echo $viewData['approving_time'] ? date('g:i A', strtotime($viewData['approving_time'])) : '-'; ?></span>
                                </div>
                            <?php else: ?>
                                <div class="detail-item">
                                    <label>Disapproval Reason</label>
                                    <span><?php echo htmlspecialchars($viewData['rejection_reason'] ?: 'No reason provided'); ?></span>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($viewData['status'] === 'recommended'): ?>
                <!-- Recommendation Details (Pending Final Approval) -->
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-thumbs-up"></i> Recommendation Details</h3>
                    </div>
                    <div class="detail-card-body">
                        <div class="detail-grid">
                            <div class="detail-item">
                                <label>Recommending Authority</label>
                                <span><?php echo htmlspecialchars($viewData['recommending_authority_name'] ?: '-'); ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Recommendation Date</label>
                                <span><?php echo $viewData['recommending_date'] ? date('F j, Y', strtotime($viewData['recommending_date'])) : '-'; ?></span>
                            </div>
                            <div class="detail-item">
                                <label>Status</label>
                                <span class="status-badge status-pending">
                                    <?php if ($viewData['current_approver_role']): ?>
                                        Awaiting <?php echo htmlspecialchars($viewData['current_approver_role']); ?> Final Approval
                                    <?php else: ?>
                                        Recommended
                                    <?php endif; ?>
                                </span>
                            </div>
                        </div>
                    </div>
                </div>
            <?php endif; ?>
        </div>

        <div class="complaint-sidebar">
            <?php
            // Determine available action for current user
            $availableAction = $atModel->getAvailableAction($viewData, $currentRoleId, $currentRoleName);
            ?>

            <!-- Actions -->
            <?php if ($availableAction): ?>
                <div class="detail-card action-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-tasks"></i> Actions</h3>
                    </div>
                    <div class="detail-card-body">
                        <?php if ($availableAction === 'recommend'): ?>
                            <button type="button" class="btn btn-primary btn-block" style="margin-bottom: 10px;"
                                onclick="openRecommendModal(<?php echo $viewData['id']; ?>)">
                                <i class="fas fa-thumbs-up"></i> Recommend Approval
                            </button>
                        <?php elseif ($availableAction === 'approve'): ?>
                            <button type="button" class="btn btn-success btn-block" style="margin-bottom: 10px;"
                                onclick="openApproveModal(<?php echo $viewData['id']; ?>)">
                                <i class="fas fa-check"></i> Approve
                            </button>
                        <?php elseif ($availableAction === 'executive_approve'): ?>
                            <button type="button" class="btn btn-success btn-block" style="margin-bottom: 10px;"
                                onclick="openApproveModal(<?php echo $viewData['id']; ?>)">
                                <i class="fas fa-gavel"></i> Executive Approve (SDS)
                            </button>
                        <?php endif; ?>

                        <button type="button" class="btn btn-danger btn-block"
                            onclick="showDisapproveModal(<?php echo $viewData['id']; ?>)">
                            <i class="fas fa-times"></i> Disapprove
                        </button>
                    </div>
                </div>
            <?php endif; ?>

            <?php if ($atModel->canUserEdit($viewData, $auth->getUserId())): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-edit"></i> Edit</h3>
                    </div>
                    <div class="detail-card-body">
                        <a href="<?php echo navUrl('/authority-to-travel.php?edit=' . $viewData['id']); ?>"
                            class="btn btn-primary btn-block">
                            <i class="fas fa-edit"></i> Edit Request
                        </a>
                    </div>
                </div>
            <?php endif; ?>

            <!-- Routing Status -->
            <div class="detail-card">
                <div class="detail-card-header">
                    <h3><i class="fas fa-route"></i> Routing Status</h3>
                </div>
                <div class="detail-card-body">
                    <div class="detail-item">
                        <label>Current Stage</label>
                        <span><?php
                        echo AuthorityToTravel::getStatusLabel(
                            $viewData['status'],
                            $viewData['routing_stage'],
                            $viewData['current_approver_role']
                        );
                        ?></span>
                    </div>
                    <?php if ($viewData['current_approver_role']): ?>
                        <div class="detail-item">
                            <label>Pending With</label>
                            <span
                                class="status-badge"><?php echo htmlspecialchars($viewData['current_approver_role']); ?></span>
                        </div>
                    <?php endif; ?>
                    <?php if (!empty($viewData['forwarded_to_ro'])): ?>
                        <div class="detail-item">
                            <label>RO Forwarding</label>
                            <span class="status-badge"
                                style="background: #e0e7ff; color: #4338ca; white-space: normal; word-break: break-word; display: inline-block; width: 100%;">
                                Forwarded to Regional Office
                                <?php if (($viewData['final_approver_role'] ?? '') === 'DEPED_SEC'): ?>
                                    (for DepEd Secretary approval)
                                <?php endif; ?>
                            </span>
                        </div>
                        <?php if ($viewData['forwarded_to_ro_date']): ?>
                            <div class="detail-item">
                                <label>Forwarded Date</label>
                                <span><?php echo date('F j, Y', strtotime($viewData['forwarded_to_ro_date'])); ?></span>
                            </div>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
            </div>

            <?php if ($viewData['status'] === 'approved'): ?>
                <div class="detail-card">
                    <div class="detail-card-header">
                        <h3><i class="fas fa-download"></i> Download</h3>
                    </div>
                    <div class="detail-card-body">
                        <a href="<?php echo navUrl('/api/generate-docx.php?type=at&id=' . $viewData['id']); ?>"
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
                        <span><?php echo htmlspecialchars($viewData['filed_by_name']); ?></span>
                    </div>
                    <div class="detail-item">
                        <label>Email</label>
                        <span><?php echo htmlspecialchars($viewData['filed_by_email']); ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Approve Modal -->
    <div class="modal-overlay" id="approveModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-check-circle" style="margin-right: 8px; color: var(--success);"></i> Approve Authority
                    to Travel</h3>
                <button class="modal-close" type="button" onclick="closeApproveModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action"
                        value="<?php echo $auth->isSDS() ? 'executive_approve' : 'approve'; ?>">
                    <input type="hidden" name="id" id="approveId" value="">

                    <p style="margin-bottom: 10px;">
                        Are you sure you want to approve this Authority to Travel?
                    </p>
                    <?php if ($auth->isSDS()): ?>
                        <p style="margin-bottom: 10px; color: var(--warning);">
                            <i class="fas fa-exclamation-triangle"></i> This is an Executive Override (SDS approval).
                        </p>
                    <?php endif; ?>
                    <div
                        style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                        <div style="font-weight: 700;" id="approveTrackingNo"></div>
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
                <h3>Disapprove Authority to Travel</h3>
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

    <!-- Recommend Modal (for Unit Heads) -->
    <div class="modal-overlay" id="recommendModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-thumbs-up" style="margin-right: 8px; color: var(--primary);"></i> Recommend Authority
                    to Travel</h3>
                <button class="modal-close" type="button" onclick="closeRecommendModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="recommend">
                    <input type="hidden" name="id" id="recommendId" value="">

                    <p style="margin-bottom: 10px;">
                        Are you sure you want to recommend this Authority to Travel for approval?
                    </p>
                    <p style="margin-bottom: 10px; color: var(--text-muted);">
                        <i class="fas fa-info-circle"></i> After your recommendation, this request will be routed to the
                        designated final approver.
                    </p>
                    <div
                        style="padding: 12px 14px; background: var(--bg-secondary); border-radius: var(--radius-md); border: 1px solid var(--border-light);">
                        <div style="font-weight: 700;" id="recommendTrackingNo"></div>
                        <div style="color: var(--text-muted); font-size: 0.9rem;" id="recommendEmployeeName"></div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" onclick="closeRecommendModal()">Cancel</button>
                    <button type="submit" class="btn btn-primary"><i class="fas fa-thumbs-up"></i> Yes, recommend</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function openApproveModal(id) {
            document.getElementById('approveId').value = id;
            // Pull details from current page if available
            var trackingNoEl = document.querySelector('.ref-number');
            if (trackingNoEl) document.getElementById('approveTrackingNo').textContent = trackingNoEl.textContent.trim();
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

        function openRecommendModal(id) {
            document.getElementById('recommendId').value = id;
            // Pull details from current page if available
            var trackingNoEl = document.querySelector('.ref-number');
            if (trackingNoEl) document.getElementById('recommendTrackingNo').textContent = trackingNoEl.textContent.trim();
            // Find employee name specifically
            var nameLabel = Array.from(document.querySelectorAll('.detail-item label')).find(l => l.textContent.trim() === 'Employee Name');
            if (nameLabel && nameLabel.parentElement) {
                var span = nameLabel.parentElement.querySelector('span');
                if (span) document.getElementById('recommendEmployeeName').textContent = span.textContent.trim();
            }
            document.getElementById('recommendModal').classList.add('active');
        }
        function closeRecommendModal() {
            document.getElementById('recommendModal').classList.remove('active');
        }

        function showDisapproveModal(id) {
            document.getElementById('disapproveId').value = id;
            document.getElementById('disapproveModal').classList.add('active');
        }
        function closeDisapproveModal() {
            document.getElementById('disapproveModal').classList.remove('active');
        }
    </script>

<?php elseif ($editId): ?>
    <!-- Edit Authority to Travel -->
    <?php
    $editData = $atModel->getById($editId);
    if (!$editData || !$atModel->canUserEdit($editData, $auth->getUserId())) {
        $error = 'You cannot edit this Authority to Travel.';
        $editData = null;
    }
    ?>
    <?php if ($editData): ?>
        <div class="page-header">
            <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $editData['id']); ?>" class="back-link">
                <i class="fas fa-arrow-left"></i> Back to View
            </a>
        </div>

        <div class="detail-card">
            <div class="detail-card-header">
                <h3><i class="fas fa-edit"></i> Edit Authority to Travel</h3>
            </div>
            <div class="detail-card-body">
                <form method="POST" action="">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="edit">
                    <input type="hidden" name="id" value="<?php echo $editData['id']; ?>">

                    <!-- Travel Scope (per DepEd Order 043 s. 2022) -->
                    <div class="form-group">
                        <label class="form-label">Travel Scope <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_scope" value="local" <?php echo ($editData['travel_scope'] ?? 'local') === 'local' ? 'checked' : ''; ?> onchange="toggleScopeCategoryEdit()">
                                <span>Local</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_scope" value="international" <?php echo ($editData['travel_scope'] ?? '') === 'international' ? 'checked' : ''; ?>
                                    onchange="toggleScopeCategoryEdit()">
                                <span>International</span>
                            </label>
                        </div>
                    </div>

                    <!-- Local Travel Type (Within Region / Outside Region) — only when Local -->
                    <?php
                    $editLocalType = $editData['travel_type'] ?? 'within_region';
                    $editIsLocal = ($editData['travel_scope'] ?? 'local') === 'local';
                    ?>
                    <div class="form-group" id="localTypeGroupEdit" style="<?php echo $editIsLocal ? '' : 'display:none;'; ?>">
                        <label class="form-label">Travel Type <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_type" value="within_region" <?php echo $editLocalType === 'within_region' ? 'checked' : ''; ?> onchange="toggleScopeCategoryEdit()">
                                <span>Within Region</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_type" value="outside_region" <?php echo $editLocalType === 'outside_region' ? 'checked' : ''; ?> onchange="toggleScopeCategoryEdit()">
                                <span>Outside Region</span>
                            </label>
                        </div>
                    </div>

                    <!-- Travel Category (Official/Personal) — only when Outside Region or International -->
                    <?php
                    $editShowCategory = ($editIsLocal && $editLocalType === 'outside_region') || !$editIsLocal;
                    ?>
                    <div class="form-group" id="categoryGroupEdit"
                        style="<?php echo $editShowCategory ? '' : 'display:none;'; ?>">
                        <label class="form-label">Travel Category <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_category" value="official" <?php echo ($editData['travel_category'] ?? 'official') === 'official' ? 'checked' : ''; ?>
                                    onchange="toggleTravelTypeEdit()">
                                <span>Official</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_category" value="personal" <?php echo ($editData['travel_category'] ?? '') === 'personal' ? 'checked' : ''; ?>
                                    onchange="toggleTravelTypeEdit()">
                                <span>Personal</span>
                            </label>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">

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
                        <label class="form-label">Permanent Station</label>
                        <input type="text" name="permanent_station" class="form-control"
                            value="<?php echo htmlspecialchars($editData['permanent_station'] ?? ''); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Destination <span class="required">*</span></label>
                        <input type="text" name="destination" class="form-control" required
                            value="<?php echo htmlspecialchars($editData['destination']); ?>">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date From <span class="required">*</span></label>
                            <input type="date" name="date_from" class="form-control" required min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo date('Y-m-d', strtotime($editData['date_from'])); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date To <span class="required">*</span></label>
                            <input type="date" name="date_to" class="form-control" required min="<?php echo date('Y-m-d'); ?>"
                                value="<?php echo date('Y-m-d', strtotime($editData['date_to'])); ?>">
                        </div>
                    </div>

                    <div id="officialFields"
                        style="<?php echo (($editData['travel_category'] ?? 'official') === 'official') ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label class="form-label">Host of Activity</label>
                            <input type="text" name="host_of_activity" class="form-control"
                                value="<?php echo htmlspecialchars($editData['host_of_activity'] ?? ''); ?>">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fund Source</label>
                            <input type="text" name="fund_source" class="form-control"
                                value="<?php echo htmlspecialchars($editData['fund_source'] ?? ''); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                        <textarea name="purpose_of_travel" class="form-control" rows="3"
                            required><?php echo htmlspecialchars($editData['purpose_of_travel']); ?></textarea>
                    </div>

                    <div class="form-actions">
                        <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $editData['id']); ?>"
                            class="btn btn-secondary">
                            <i class="fas fa-times"></i> Cancel
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Changes
                        </button>
                    </div>
                </form>
            </div>
        </div>

        <script>
            function toggleScopeCategoryEdit() {
                const scope = document.querySelector('input[name="travel_scope"]:checked')?.value;
                const localTypeGroup = document.getElementById('localTypeGroupEdit');
                const categoryGroup = document.getElementById('categoryGroupEdit');
                const officialFields = document.getElementById('officialFields');
                const categoryOfficial = document.querySelector('input[name="travel_category"][value="official"]');

                if (scope === 'local') {
                    if (localTypeGroup) localTypeGroup.style.display = 'block';
                    const localType = document.querySelector('input[name="travel_type"]:checked')?.value;
                    if (localType === 'within_region') {
                        if (categoryGroup) categoryGroup.style.display = 'none';
                        if (officialFields) officialFields.style.display = 'block';
                        if (categoryOfficial) categoryOfficial.checked = true;
                    } else {
                        if (categoryGroup) categoryGroup.style.display = 'block';
                        const category = document.querySelector('input[name="travel_category"]:checked')?.value;
                        if (officialFields) officialFields.style.display = (category === 'personal') ? 'none' : 'block';
                    }
                } else {
                    if (localTypeGroup) localTypeGroup.style.display = 'none';
                    if (categoryGroup) categoryGroup.style.display = 'block';
                    const category = document.querySelector('input[name="travel_category"]:checked')?.value;
                    if (officialFields) officialFields.style.display = (category === 'personal') ? 'none' : 'block';
                }
            }
            function toggleTravelTypeEdit() {
                const category = document.querySelector('input[name="travel_category"]:checked')?.value;
                const officialFields = document.getElementById('officialFields');

                if (category === 'personal') {
                    if (officialFields) officialFields.style.display = 'none';
                } else {
                    if (officialFields) officialFields.style.display = 'block';
                }
            }
        </script>

    <?php endif; ?>

<?php else: ?>
    <!-- List View -->
    <div class="page-header"
        style="background: #1b4a9a; color: white; padding: 12px 16px; border-radius: var(--radius-lg); margin-bottom: 16px; display: flex; align-items: center; justify-content: space-between; box-shadow: 0 4px 15px rgba(27, 74, 154, 0.2); border: none;">
        <div class="header-content">
            <h2
                style="margin: 0; font-size: 1.15rem; display: flex; align-items: center; gap: 6px; font-weight: 800; letter-spacing: -0.5px; color: white;">
                <i class="fas fa-plane" style="color: rgba(255,255,255,0.8); font-size: 1rem;"></i> Authority to Travel
                Management
            </h2>
            <p style="margin: 2px 0 0 0; color: rgba(255,255,255,0.8); font-size: 0.75rem;">
                <?php echo $totalRequests; ?> Travel Request<?php echo $totalRequests !== 1 ? 's' : ''; ?>
                <?php if ($auth->canActOnAT() && empty($_GET['show_all'])): ?>
                    <span style="color: rgba(255,255,255,0.6);">(In Your Queue)</span>
                <?php endif; ?>
            </p>
        </div>
        <div class="header-actions" style="display: flex; gap: 8px;">
            <?php if ($auth->canActOnAT()): ?>
                <a href="<?php echo navUrl('/authority-to-travel.php' . (empty($_GET['show_all']) ? '?show_all=1' : '')); ?>"
                    class="btn"
                    style="background: rgba(255,255,255,0.1); color: white; border: 1px solid rgba(255,255,255,0.3); font-weight: 600; padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 6px;">
                    <i class="fas fa-<?php echo empty($_GET['show_all']) ? 'list' : 'inbox'; ?>"></i>
                    <?php echo empty($_GET['show_all']) ? 'View All' : 'My Queue'; ?>
                </a>
            <?php endif; ?>
            <?php if ($canCreateTravelRequests): ?>
                <button type="button" class="btn"
                    style="background: white; color: var(--primary-dark); font-weight: 700; border: none; padding: 6px 12px; font-size: 0.8rem; border-radius: var(--radius-md); display: flex; align-items: center; gap: 6px; box-shadow: 0 4px 10px rgba(0,0,0,0.1);"
                    onclick="openNewModal()">
                    <i class="fas fa-plus"></i> New Travel Request
                </button>
            <?php endif; ?>
        </div>
    </div>

    <!-- Filter Bar -->
    <div class="filter-bar"
        style="background: white; padding: 24px; border-radius: var(--radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05);">
        <form class="filter-form" method="GET" action="">
            <input type="hidden" name="token" value="<?php echo $currentToken; ?>">

            <div class="filter-group">
                <label>Search</label>
                <input type="text" name="search" class="filter-input" placeholder="Tracking no, name, destination..."
                    value="<?php echo htmlspecialchars($_GET['search'] ?? ''); ?>">
            </div>

            <div class="filter-group">
                <label>Category</label>
                <select name="category" class="filter-select">
                    <option value="">All Categories</option>
                    <option value="official" <?php echo ($_GET['category'] ?? '') === 'official' ? 'selected' : ''; ?>>
                        Official</option>
                    <option value="personal" <?php echo ($_GET['category'] ?? '') === 'personal' ? 'selected' : ''; ?>>
                        Personal</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Scope</label>
                <select name="scope" class="filter-select">
                    <option value="">All Scope</option>
                    <option value="local" <?php echo ($_GET['scope'] ?? '') === 'local' ? 'selected' : ''; ?>>Local</option>
                    <option value="international" <?php echo ($_GET['scope'] ?? '') === 'international' ? 'selected' : ''; ?>>
                        International</option>
                </select>
            </div>

            <div class="filter-group">
                <label>Travel Type</label>
                <select name="travel_type" class="filter-select">
                    <option value="">All Types</option>
                    <option value="within_region" <?php echo ($_GET['travel_type'] ?? '') === 'within_region' ? 'selected' : ''; ?>>Within Region</option>
                    <option value="outside_region" <?php echo ($_GET['travel_type'] ?? '') === 'outside_region' ? 'selected' : ''; ?>>Outside Region</option>
                </select>
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
                    <option value="recommended" <?php echo ($_GET['status'] ?? '') === 'recommended' ? 'selected' : ''; ?>>
                        Recommended</option>
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
                <a href="<?php echo navUrl('/authority-to-travel.php'); ?>" class="btn btn-secondary btn-sm"><i
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
                        <th>Tracking No.</th>
                        <th>Employee</th>
                        <th>Type</th>
                        <th>Destination</th>
                        <th>Travel Dates</th>
                        <th>Status</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($requests)): ?>
                        <tr>
                            <td colspan="7">
                                <div class="empty-state">
                                    <span class="empty-icon"><i class="fas fa-plane"></i></span>
                                    <h3>No Travel Requests found</h3>
                                    <p>Create a new Authority to Travel to get started</p>
                                </div>
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($requests as $at): ?>
                            <tr style="transition: all 0.2s ease; border-bottom: 1px solid var(--border-light);"
                                onmouseover="this.style.backgroundColor='var(--bg-secondary)'; this.style.transform='scale(1.002)';"
                                onmouseout="this.style.backgroundColor=''; this.style.transform='scale(1)';">
                                <td style="padding: 18px 16px;">
                                    <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $at['id']); ?>" class="ref-link"
                                        style="background: var(--bg-secondary); padding: 6px 12px; border-radius: 6px; font-weight: 700;">
                                        <?php echo htmlspecialchars($at['at_tracking_no']); ?>
                                    </a>
                                </td>
                                <td>
                                    <div class="cell-primary"><?php echo htmlspecialchars($at['employee_name']); ?></div>
                                    <div class="cell-secondary"><?php echo htmlspecialchars($at['employee_position'] ?: ''); ?>
                                    </div>
                                </td>
                                <td>
                                    <span
                                        class="unit-badge"><?php echo AuthorityToTravel::getTypeLabel($at['travel_category'], $at['travel_scope'], $at['travel_type'] ?? null); ?></span>
                                </td>
                                <td>
                                    <div class="cell-primary"><?php echo htmlspecialchars($at['destination']); ?></div>
                                </td>
                                <td>
                                    <div class="cell-primary"><?php echo date('M j', strtotime($at['date_from'])); ?> -
                                        <?php echo date('M j, Y', strtotime($at['date_to'])); ?>
                                    </div>
                                </td>
                                <td>
                                    <?php
                                    // Enhanced status display with routing info
                                    $displayStatus = normalizeRequestStatus($at['status'] ?? 'pending');
                                    $statusClass = $displayStatus;
                                    if ($displayStatus === 'recommended')
                                        $statusClass = 'pending';
                                    ?>
                                    <span class="status-badge status-<?php echo $statusClass; ?>">
                                        <?php
                                        if ($displayStatus === 'pending') {
                                            echo 'Pending';
                                            if ($at['current_approver_role']) {
                                                echo ' (' . htmlspecialchars($at['current_approver_role']) . ')';
                                            }
                                        } elseif ($displayStatus === 'recommended') {
                                            echo 'Recommended';
                                        } elseif ($displayStatus === 'approved') {
                                            echo 'Approved';
                                        } elseif ($displayStatus === 'disapproved') {
                                            echo 'Disapproved';
                                        }
                                        ?>
                                    </span>
                                    <?php if (!empty($at['forwarded_to_ro'])): ?>
                                        <span class="status-badge"
                                            style="background: #e0e7ff; color: #4338ca; font-size: 0.75rem; margin-top: 4px; display: inline-block;">
                                            Forwarded to RO
                                        </span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-buttons" style="display: flex; gap: 8px;">
                                        <a href="<?php echo navUrl('/authority-to-travel.php?view=' . $at['id']); ?>"
                                            class="btn btn-icon" title="View"
                                            style="background: var(--bg-secondary); color: var(--primary-dark); border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <?php if ($atModel->canUserEdit($at, $auth->getUserId())): ?>
                                            <a href="<?php echo navUrl('/authority-to-travel.php?edit=' . $at['id']); ?>"
                                                class="btn btn-icon" title="Edit"
                                                style="background: var(--warning-bg); color: var(--warning); border-radius: 8px; width: 36px; height: 36px; display: inline-flex; align-items: center; justify-content: center; transition: all 0.2s ease;"
                                                onmouseover="this.style.background='var(--warning)'; this.style.color='white';"
                                                onmouseout="this.style.background='var(--warning-bg)'; this.style.color='var(--warning)';">
                                                <i class="fas fa-edit"></i>
                                            </a>
                                        <?php endif; ?>
                                        <?php if ($at['status'] === 'approved'): ?>
                                            <a href="<?php echo navUrl('/api/generate-docx.php?type=at&id=' . $at['id']); ?>"
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
                        <a href="<?php echo navUrl('/authority-to-travel.php?' . http_build_query(array_merge($_GET, ['page' => $page - 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    <?php endif; ?>

                    <?php for ($i = max(1, $page - 2); $i <= min($totalPages, $page + 2); $i++): ?>
                        <a href="<?php echo navUrl('/authority-to-travel.php?' . http_build_query(array_merge($_GET, ['page' => $i]))); ?>"
                            class="page-link <?php echo $i === $page ? 'active' : ''; ?>"><?php echo $i; ?></a>
                    <?php endfor; ?>

                    <?php if ($page < $totalPages): ?>
                        <a href="<?php echo navUrl('/authority-to-travel.php?' . http_build_query(array_merge($_GET, ['page' => $page + 1]))); ?>"
                            class="page-link">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    <?php endif; ?>
                </div>
            </div>
        <?php endif; ?>
    </div>

    <?php if ($canCreateTravelRequests): ?>
    <!-- New AT Modal -->
    <div class="modal-overlay" id="newModal" <?php echo $action === 'new' ? 'class="active"' : ''; ?>>
        <div class="modal modal-lg">
            <div class="modal-header">
                <h3><i class="fas fa-plane"></i> New Authority to Travel</h3>
                <button class="modal-close" onclick="closeNewModal()">&times;</button>
            </div>
            <form method="POST" action="">
                <div class="modal-body">
                    <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                    <input type="hidden" name="action" value="create">

                    <!-- Step 1: Travel Scope (per DepEd Order 043 s. 2022) -->
                    <div class="form-group">
                        <label class="form-label">Travel Scope <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_scope" value="local" <?php echo $formScope === 'local' ? 'checked' : ''; ?> onchange="toggleScopeCategory()">
                                <span>Local</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_scope" value="international" <?php echo $formScope === 'international' ? 'checked' : ''; ?> onchange="toggleScopeCategory()">
                                <span>International</span>
                            </label>
                        </div>
                    </div>

                    <!-- Step 2: Local Travel Type (Within Region / Outside Region) — only when Local -->
                    <div class="form-group" id="localTypeGroup"
                        style="<?php echo $formScope === 'local' ? '' : 'display:none;'; ?>">
                        <label class="form-label">Travel Type <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_type" value="within_region" <?php echo $formLocalType === 'within_region' ? 'checked' : ''; ?> onchange="toggleScopeCategory()">
                                <span>Within Region</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_type" value="outside_region" <?php echo $formLocalType === 'outside_region' ? 'checked' : ''; ?> onchange="toggleScopeCategory()">
                                <span>Outside Region</span>
                            </label>
                        </div>
                        <span class="form-hint">Within Region is Official only. Outside Region may be Official or
                            Personal.</span>
                    </div>

                    <!-- Step 3: Travel Category (Official/Personal) — only when Outside Region or International -->
                    <?php
                    $showCategory = ($formScope === 'local' && $formLocalType === 'outside_region') || $formScope === 'international';
                    ?>
                    <div class="form-group" id="categoryGroup" style="<?php echo $showCategory ? '' : 'display:none;'; ?>">
                        <label class="form-label">Travel Category <span class="required">*</span></label>
                        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_category" value="official" <?php echo $formCategory === 'official' ? 'checked' : ''; ?> onchange="toggleTravelType()">
                                <span>Official</span>
                            </label>
                            <label class="checkbox-label"
                                style="padding: 12px 20px; border: 2px solid var(--border-color); border-radius: 8px; cursor: pointer;">
                                <input type="radio" name="travel_category" value="personal" <?php echo $formCategory === 'personal' ? 'checked' : ''; ?> onchange="toggleTravelType()">
                                <span>Personal</span>
                            </label>
                        </div>
                    </div>

                    <hr style="border: none; border-top: 1px solid var(--border-color); margin: 20px 0;">

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Employee Name <span class="required">*</span></label>
                            <input type="text" name="employee_name" class="form-control" required
                                value="<?php echo htmlspecialchars($formData['employee_name']); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Position</label>
                            <input type="text" name="employee_position" class="form-control"
                                value="<?php echo htmlspecialchars($formData['employee_position']); ?>">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Permanent Station</label>
                        <input type="text" name="permanent_station" class="form-control"
                            value="<?php echo htmlspecialchars($formData['permanent_station']); ?>">
                    </div>

                    <div class="form-group">
                        <label class="form-label">Destination <span class="required">*</span></label>
                        <input type="text" name="destination" class="form-control" required
                            placeholder="Where are you traveling to?">
                    </div>

                    <div class="form-row">
                        <div class="form-group">
                            <label class="form-label">Date From <span class="required">*</span></label>
                            <input type="date" name="date_from" class="form-control" required
                                min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                        <div class="form-group">
                            <label class="form-label">Date To <span class="required">*</span></label>
                            <input type="date" name="date_to" class="form-control" required
                                min="<?php echo date('Y-m-d'); ?>" value="<?php echo date('Y-m-d'); ?>">
                        </div>
                    </div>

                    <div id="officialFields" style="<?php echo ($formCategory === 'official') ? '' : 'display:none;'; ?>">
                        <div class="form-group">
                            <label class="form-label">Host of Activity</label>
                            <input type="text" name="host_of_activity" class="form-control"
                                placeholder="Who is hosting the activity?">
                        </div>

                        <div class="form-group">
                            <label class="form-label">Fund Source</label>
                            <input type="text" name="fund_source" class="form-control"
                                placeholder="e.g., MOOE, Personal, Sponsor">
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label">Purpose of Travel <span class="required">*</span></label>
                        <textarea name="purpose_of_travel" class="form-control" rows="3" required
                            placeholder="Describe the purpose of your travel..."></textarea>
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
            url.searchParams.delete('type');
            window.history.replaceState({}, '', url);
        }

        function toggleScopeCategory() {
            const scope = document.querySelector('input[name="travel_scope"]:checked')?.value;
            const localTypeGroup = document.getElementById('localTypeGroup');
            const categoryGroup = document.getElementById('categoryGroup');
            const officialFields = document.getElementById('officialFields');
            const categoryOfficial = document.querySelector('input[name="travel_category"][value="official"]');

            if (scope === 'local') {
                // Local: show travel type picker (Within Region / Outside Region)
                if (localTypeGroup) localTypeGroup.style.display = 'block';
                const localType = document.querySelector('input[name="travel_type"]:checked')?.value;
                if (localType === 'within_region') {
                    // Within Region: always Official, hide category picker
                    if (categoryGroup) categoryGroup.style.display = 'none';
                    if (officialFields) officialFields.style.display = 'block';
                    if (categoryOfficial) categoryOfficial.checked = true;
                } else {
                    // Outside Region: show Official/Personal picker
                    if (categoryGroup) categoryGroup.style.display = 'block';
                    const category = document.querySelector('input[name="travel_category"]:checked')?.value;
                    if (officialFields) officialFields.style.display = (category === 'personal') ? 'none' : 'block';
                }
            } else {
                // International: hide local type picker, show Official/Personal picker
                if (localTypeGroup) localTypeGroup.style.display = 'none';
                if (categoryGroup) categoryGroup.style.display = 'block';
                const category = document.querySelector('input[name="travel_category"]:checked')?.value;
                if (officialFields) officialFields.style.display = (category === 'personal') ? 'none' : 'block';
            }
        }

        function toggleTravelType() {
            const category = document.querySelector('input[name="travel_category"]:checked')?.value;
            const officialFields = document.getElementById('officialFields');

            if (category === 'personal') {
                if (officialFields) officialFields.style.display = 'none';
            } else {
                if (officialFields) officialFields.style.display = 'block';
            }
        }

        <?php if ($canCreateTravelRequests && $action === 'new'): ?>
            document.addEventListener('DOMContentLoaded', openNewModal);
        <?php endif; ?>
    </script>
    <?php endif; ?>
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

    .input-with-icon i {
        position: absolute;
        left: 14px;
        color: var(--text-muted);
        font-size: 1rem;
        transition: color 0.3s ease;
        pointer-events: none;
    }

    .input-with-icon .form-control {
        padding-left: 42px;
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
        box-shadow: 0 0 0 4px rgba(27, 74, 154, 0.1);
    }

    .input-with-icon .form-control:focus+i,
    .form-control:focus~i {
        color: var(--primary);
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
        box-shadow: 0 4px 12px rgba(27, 74, 154, 0.2);
        border: none;
    }

    .btn-primary:hover {
        transform: translateY(-2px);
        box-shadow: 0 6px 16px rgba(27, 74, 154, 0.3);
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

<?php require_once __DIR__ . '/../includes/footer.php'; ?>