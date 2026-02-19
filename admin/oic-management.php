<?php
/**
 * OIC Management Page
 * SDO ALPAS - Unit heads can assign and manage OIC delegations
 */

// Check authentication BEFORE header.php outputs anything
require_once __DIR__ . '/../includes/auth.php';
$authCheck = auth();
$authCheck->requireLogin();

$currentUserCheck = $authCheck->getUser();

// Only ACTUAL unit heads can access this page (not OICs acting as unit heads)
// OICs should not be able to assign other OICs - this is reserved for office chiefs only
if ($authCheck->isActingAsOIC() || !isUnitHead($currentUserCheck['role_id'])) {
    header('HTTP/1.1 403 Forbidden');
    include __DIR__ . '/403.php';
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../models/OICDelegation.php';
require_once __DIR__ . '/../models/AdminUser.php';

$oicModel = new OICDelegation();
$userModel = new AdminUser();

$currentRoleId = $currentUser['role_id'];
$currentUserId = $auth->getUserId();
$message = '';
$error = '';

// Handle form submissions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $postAction = $_POST['action'] ?? '';
    
    if ($postAction === 'create') {
        try {
            $startDate = $_POST['start_date'];
            $endDate = $_POST['end_date'];
            
            // Validate dates
            if (strtotime($endDate) < strtotime($startDate)) {
                $error = 'End date cannot be earlier than start date.';
            } elseif (strtotime($startDate) < strtotime('today')) {
                $error = 'Start date cannot be in the past.';
            } else {
                // Check for date overlap
                if ($oicModel->hasDateOverlap($currentRoleId, $startDate, $endDate)) {
                    $error = 'There is already an active OIC delegation for this period. Please deactivate the existing one first.';
                } else {
                    $data = [
                        'unit_head_user_id' => $currentUserId,
                        'unit_head_role_id' => $currentRoleId,
                        'oic_user_id' => $_POST['oic_user_id'],
                        'start_date' => $startDate,
                        'end_date' => $endDate,
                        'created_by' => $currentUserId
                    ];
                    
                    $id = $oicModel->create($data);
                    $auth->logActivity('create_oic', 'oic_delegation', $id, 'Created OIC delegation');
                    $message = 'OIC delegation created successfully!';
                }
            }
        } catch (Exception $e) {
            $error = 'Failed to create OIC delegation: ' . $e->getMessage();
        }
    }
    
    if ($postAction === 'deactivate') {
        $id = $_POST['id'];
        // Verify ownership by checking if it's in the user's delegations
        $userDelegations = $oicModel->getByUnitHead($currentUserId, $currentRoleId, true);
        $oic = null;
        foreach ($userDelegations as $del) {
            if ($del['id'] == $id) {
                $oic = $del;
                break;
            }
        }
        
        if ($oic) {
            $oicModel->deactivate($id);
            $auth->logActivity('deactivate_oic', 'oic_delegation', $id, 'Deactivated OIC delegation');
            $message = 'OIC delegation deactivated successfully!';
        } else {
            $error = 'OIC delegation not found or you do not have permission.';
        }
    }
    
    if ($postAction === 'delete') {
        $id = $_POST['id'];
        // Verify ownership by checking if it's in the user's delegations
        $userDelegations = $oicModel->getByUnitHead($currentUserId, $currentRoleId, true);
        $oic = null;
        foreach ($userDelegations as $del) {
            if ($del['id'] == $id) {
                $oic = $del;
                break;
            }
        }
        
        if ($oic) {
            $oicModel->delete($id);
            $auth->logActivity('delete_oic', 'oic_delegation', $id, 'Deleted OIC delegation');
            $message = 'OIC delegation deleted successfully!';
        } else {
            $error = 'OIC delegation not found or you do not have permission.';
        }
    }
}

// Get current OIC delegations
$oicDelegations = $oicModel->getByUnitHead($currentUserId, $currentRoleId, true);

// Get eligible OIC users (users under this unit head's supervision)
$eligibleUsers = $oicModel->getEligibleOICUsers($currentRoleId, $currentUserId);

// Get active OIC
$activeOIC = $oicModel->getActiveOICForUnit($currentRoleId);
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

<div class="page-header">
    <div>
        <h2 style="margin: 0; font-size: 1.1rem; color: var(--text-secondary);">
            OIC (Officer-In-Charge) Management
        </h2>
        <p style="margin: 4px 0 0; color: var(--text-muted); font-size: 0.9rem;">
            Assign an OIC to handle approvals when you are unavailable
        </p>
    </div>
    <button type="button" class="btn btn-primary" onclick="openNewOICModal()">
        <i class="fas fa-plus"></i> Assign New OIC
    </button>
</div>

<!-- Active OIC Card -->
<?php if ($activeOIC): ?>
<div class="detail-card oic-card">
    <div class="detail-card-header">
        <h3>
            <i class="fas fa-user-check"></i> Currently Active OIC
        </h3>
        <span class="status-badge status-approved">
            <i class="fas fa-check-circle"></i> Active
        </span>
    </div>
    <div class="detail-card-body">
        <div class="detail-grid oic-card__grid">
            <div class="detail-item">
                <label>OIC Name</label>
                <span class="oic-card__primary"><?php echo htmlspecialchars($activeOIC['oic_name']); ?></span>
            </div>
            <div class="detail-item">
                <label>Position</label>
                <span><?php echo htmlspecialchars($activeOIC['oic_position'] ?? '-'); ?></span>
            </div>
            <div class="detail-item">
                <label>Period</label>
                <span>
                    <?php echo date('M j, Y', strtotime($activeOIC['start_date'])); ?> - 
                    <?php echo date('M j, Y', strtotime($activeOIC['end_date'])); ?>
                </span>
            </div>
        </div>
        <form method="POST" action="" class="oic-card__actions" id="deactivateActiveOICForm">
            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
            <input type="hidden" name="action" value="deactivate">
            <input type="hidden" name="id" value="<?php echo $activeOIC['id']; ?>">
            <button type="button" class="btn btn-secondary"
                    onclick="openConfirmModal('deactivateActiveOICForm', 'Deactivate this OIC delegation?')">
                <i class="fas fa-times"></i> Deactivate OIC
            </button>
        </form>
    </div>
</div>
<?php else: ?>
<div class="detail-card" style="border: 2px dashed var(--border-color); background: var(--bg-secondary); margin-bottom: 20px;">
    <div class="detail-card-body" style="text-align: center; padding: 30px;">
        <i class="fas fa-user-slash" style="font-size: 3rem; color: var(--text-muted); margin-bottom: 16px;"></i>
        <h3 style="color: var(--text-secondary); margin-bottom: 8px;">No Active OIC</h3>
        <p style="color: var(--text-muted); margin: 0 0 20px;">You currently do not have an assigned OIC. Assign one to handle approvals during your absence.</p>
        <button type="button" class="btn btn-primary" onclick="openNewOICModal()">
            <i class="fas fa-plus"></i> Assign OIC Now
        </button>
    </div>
</div>
<?php endif; ?>

<!-- OIC Delegations List -->
<div class="data-card">
    <div class="detail-card-header">
        <h3><i class="fas fa-list"></i> OIC Delegation History</h3>
    </div>
    <div class="table-responsive">
        <table class="data-table">
            <thead>
                <tr>
                    <th>OIC Name</th>
                    <th>Position</th>
                    <th>Start Date</th>
                    <th>End Date</th>
                    <th>Status</th>
                    <th>Created</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($oicDelegations)): ?>
                <tr>
                    <td colspan="7">
                        <div class="empty-state">
                            <span class="empty-icon"><i class="fas fa-user-friends"></i></span>
                            <h3>No OIC Delegations</h3>
                            <p>You haven't assigned any OIC delegations yet</p>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($oicDelegations as $oic): ?>
                <tr>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($oic['oic_name']); ?></div>
                        <div class="cell-secondary"><?php echo htmlspecialchars($oic['oic_email'] ?? ''); ?></div>
                    </td>
                    <td><?php echo htmlspecialchars($oic['oic_position'] ?? '-'); ?></td>
                    <td><?php echo date('M j, Y', strtotime($oic['start_date'])); ?></td>
                    <td><?php echo date('M j, Y', strtotime($oic['end_date'])); ?></td>
                    <td>
                        <?php 
                        $isActive = $oic['is_active'] == 1;
                        $isCurrent = strtotime($oic['start_date']) <= time() && strtotime($oic['end_date']) >= time();
                        if ($isActive && $isCurrent) {
                            echo '<span class="status-badge status-approved"><i class="fas fa-check-circle"></i> Active</span>';
                        } elseif ($isActive && !$isCurrent) {
                            echo '<span class="status-badge status-pending"><i class="fas fa-clock"></i> Scheduled</span>';
                        } else {
                            echo '<span class="status-badge status-rejected"><i class="fas fa-times-circle"></i> Inactive</span>';
                        }
                        ?>
                    </td>
                    <td><?php echo date('M j, Y', strtotime($oic['created_at'])); ?></td>
                    <td>
                        <div class="action-buttons">
                            <?php if ($isActive && $isCurrent): ?>
                            <form method="POST" action="" style="display: inline;" id="deactivateOICForm_<?php echo $oic['id']; ?>">
                                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                <input type="hidden" name="action" value="deactivate">
                                <input type="hidden" name="id" value="<?php echo $oic['id']; ?>">
                                <button type="button" class="btn btn-icon" title="Deactivate" 
                                        onclick="openConfirmModal('deactivateOICForm_<?php echo $oic['id']; ?>', 'Deactivate this OIC delegation?')" 
                                        style="color: var(--warning);">
                                    <i class="fas fa-pause"></i>
                                </button>
                            </form>
                            <?php endif; ?>
                            <form method="POST" action="" style="display: inline;" id="deleteOICForm_<?php echo $oic['id']; ?>">
                                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                <input type="hidden" name="action" value="delete">
                                <input type="hidden" name="id" value="<?php echo $oic['id']; ?>">
                                <button type="button" class="btn btn-icon" title="Delete" 
                                        onclick="openConfirmModal('deleteOICForm_<?php echo $oic['id']; ?>', 'Delete this OIC delegation? This action cannot be undone.')" 
                                        style="color: var(--danger);">
                                    <i class="fas fa-trash"></i>
                                </button>
                            </form>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

    <!-- Confirmation Modal -->
    <div class="modal-overlay" id="confirmModal">
        <div class="modal">
            <div class="modal-header">
                <h3><i class="fas fa-question-circle" style="margin-right: 8px;"></i> Confirm Action</h3>
                <button class="modal-close" onclick="closeConfirmModal()">&times;</button>
            </div>
            <div class="modal-body">
                <p id="confirmMessage" style="margin: 0; color: var(--text-secondary);"></p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeConfirmModal()">Cancel</button>
                <button type="button" class="btn btn-primary" onclick="submitConfirmModal()">Confirm</button>
            </div>
        </div>
    </div>

<!-- New OIC Modal -->
<div class="modal-overlay" id="newOICModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Assign New OIC</h3>
            <button class="modal-close" onclick="closeNewOICModal()">&times;</button>
        </div>
        <form method="POST" action="">
            <div class="modal-body">
                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                <input type="hidden" name="action" value="create">
                
                <div class="form-group">
                    <label class="form-label">Select OIC <span class="required">*</span></label>
                    <select name="oic_user_id" class="form-control" required>
                        <option value="">-- Select Personnel --</option>
                        <?php foreach ($eligibleUsers as $user): ?>
                        <option value="<?php echo $user['id']; ?>">
                            <?php echo htmlspecialchars($user['full_name']); ?> 
                            (<?php echo htmlspecialchars($user['employee_position'] ?? $user['role_name']); ?>)
                            <?php if ($user['employee_office']): ?>
                            - <?php echo htmlspecialchars($user['employee_office']); ?>
                            <?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <span class="form-hint">Only personnel under your unit can be assigned as OIC</span>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Start Date <span class="required">*</span></label>
                        <input type="date" name="start_date" class="form-control" required
                               value="<?php echo date('Y-m-d'); ?>" min="<?php echo date('Y-m-d'); ?>">
                        <span class="form-hint">OIC period start date</span>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">End Date <span class="required">*</span></label>
                        <input type="date" name="end_date" class="form-control" required
                               value="<?php echo date('Y-m-d', strtotime('+7 days')); ?>" min="<?php echo date('Y-m-d'); ?>">
                        <span class="form-hint">OIC period end date</span>
                    </div>
                </div>
                
                <div class="alert alert-info" style="margin-top: 16px;">
                    <i class="fas fa-info-circle"></i> 
                    <strong>Note:</strong> Only one active OIC can be assigned at a time. 
                    If you assign a new OIC with overlapping dates, the existing one will be automatically deactivated.
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeNewOICModal()">Cancel</button>
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-check"></i> Assign OIC
                </button>
            </div>
        </form>
    </div>
</div>

<script>
let confirmFormId = null;

function openConfirmModal(formId, message) {
    confirmFormId = formId;
    const messageEl = document.getElementById('confirmMessage');
    if (messageEl) {
        messageEl.textContent = message;
    }
    document.getElementById('confirmModal').classList.add('active');
}

function closeConfirmModal() {
    confirmFormId = null;
    document.getElementById('confirmModal').classList.remove('active');
}

function submitConfirmModal() {
    if (confirmFormId) {
        const targetForm = document.getElementById(confirmFormId);
        if (targetForm) {
            targetForm.submit();
        }
    }
    closeConfirmModal();
}

function openNewOICModal() {
    document.getElementById('newOICModal').classList.add('active');
}
function closeNewOICModal() {
    document.getElementById('newOICModal').classList.remove('active');
}

// Auto-validate end date is after start date
document.querySelector('input[name="start_date"]')?.addEventListener('change', function() {
    const endDateInput = document.querySelector('input[name="end_date"]');
    if (endDateInput && this.value) {
        endDateInput.min = this.value;
        if (endDateInput.value && endDateInput.value < this.value) {
            endDateInput.value = this.value;
        }
    }
});
</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
