<?php
/**
 * Unit Routing Configuration Page
 * SDO ALPAS - Superadmin only
 * Manage unit-to-approver mappings for Authority to Travel approval routing
 */

// Superadmin only - check before header.php outputs anything
require_once __DIR__ . '/../includes/auth.php';
$authCheck = auth();
$authCheck->requireLogin();

if (!$authCheck->isSuperAdmin()) {
    header('Location: 403.php');
    exit;
}

require_once __DIR__ . '/../includes/header.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

$db = Database::getInstance();
$message = '';
$error = '';

// Fetch master office list (active only) to keep routing aligned with user dropdowns
$offices = getSDOOfficesFromDB(true);

// Get all roles that can be approvers (unit heads)
$roles = $db->query("SELECT id, role_name, description FROM admin_roles WHERE id IN (?, ?, ?) ORDER BY id", 
    [ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF])->fetchAll();

// Handle form actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';

    if ($action === 'create') {
        $officeId = intval($_POST['office_id'] ?? 0);
        $approverRoleId = intval($_POST['approver_role_id'] ?? 0);
        $travelScope = $_POST['travel_scope'] ?? 'all';
        $appliesTo = in_array($_POST['applies_to'] ?? '', ['authority_to_travel', 'locator_slip', 'both']) ? $_POST['applies_to'] : 'authority_to_travel';
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $office = $officeId ? getOfficeById($officeId) : null;
        $unitName = $office['office_code'] ?? '';
        $unitDisplayName = $office['office_name'] ?? '';

        if (!$office || $unitName === '' || $unitDisplayName === '' || $approverRoleId <= 0) {
            $error = 'Please select an office/unit and an approving authority.';
        } else {
            try {
                // Check for duplicate office assignment
                $existing = $db->query("SELECT id FROM unit_routing_config WHERE (office_id = ? OR unit_name = ?)", [$officeId, $unitName])->fetch();
                if ($existing) {
                    $error = 'A routing configuration for this office/unit already exists.';
                } else {
                    $db->query("INSERT INTO unit_routing_config (unit_name, unit_display_name, office_id, approver_role_id, travel_scope, applies_to, sort_order, is_active) 
                                VALUES (?, ?, ?, ?, ?, ?, ?, ?)", 
                        [$unitName, $unitDisplayName, $officeId, $approverRoleId, $travelScope, $appliesTo, $sortOrder, $isActive]);
                    
                    $auth->logActivity('create_unit_routing', 'unit_routing_config', $db->lastInsertId(), 
                        'Created unit routing: ' . $unitName . ' → Role ID ' . $approverRoleId);
                    $message = 'Unit routing configuration created successfully!';
                }
            } catch (Exception $e) {
                $error = 'Failed to create unit routing configuration.';
            }
        }
    }

    if ($action === 'update' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        $officeId = intval($_POST['office_id'] ?? 0);
        $approverRoleId = intval($_POST['approver_role_id'] ?? 0);
        $travelScope = $_POST['travel_scope'] ?? 'all';
        $appliesTo = in_array($_POST['applies_to'] ?? '', ['authority_to_travel', 'locator_slip', 'both']) ? $_POST['applies_to'] : 'authority_to_travel';
        $sortOrder = intval($_POST['sort_order'] ?? 0);
        $isActive = isset($_POST['is_active']) ? 1 : 0;

        $office = $officeId ? getOfficeById($officeId) : null;
        $unitName = $office['office_code'] ?? '';
        $unitDisplayName = $office['office_name'] ?? '';

        if (!$office || $unitName === '' || $unitDisplayName === '' || $approverRoleId <= 0) {
            $error = 'Please select an office/unit and an approving authority.';
        } else {
            try {
                // Check for duplicate unit_name (excluding current record)
                $existing = $db->query("SELECT id FROM unit_routing_config WHERE (office_id = ? OR unit_name = ?) AND id != ?", [$officeId, $unitName, $id])->fetch();
                if ($existing) {
                    $error = 'A routing configuration for this office/unit already exists.';
                } else {
                    $db->query("UPDATE unit_routing_config 
                                SET unit_name = ?, unit_display_name = ?, office_id = ?, approver_role_id = ?, 
                                    travel_scope = ?, applies_to = ?, sort_order = ?, is_active = ?
                                WHERE id = ?", 
                        [$unitName, $unitDisplayName, $officeId, $approverRoleId, $travelScope, $appliesTo, $sortOrder, $isActive, $id]);
                    
                    $auth->logActivity('update_unit_routing', 'unit_routing_config', $id, 
                        'Updated unit routing: ' . $unitName . ' → Role ID ' . $approverRoleId);
                    $message = 'Unit routing configuration updated successfully!';
                }
            } catch (Exception $e) {
                $error = 'Failed to update unit routing configuration.';
            }
        }
    }

    if ($action === 'delete' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        try {
            $config = $db->query("SELECT unit_name FROM unit_routing_config WHERE id = ?", [$id])->fetch();
            $db->query("DELETE FROM unit_routing_config WHERE id = ?", [$id]);
            
            $auth->logActivity('delete_unit_routing', 'unit_routing_config', $id, 
                'Deleted unit routing: ' . ($config['unit_name'] ?? 'Unknown'));
            $message = 'Unit routing configuration deleted.';
        } catch (Exception $e) {
            $error = 'Failed to delete unit routing configuration.';
        }
    }

    if ($action === 'toggle_status' && !empty($_POST['id'])) {
        $id = intval($_POST['id']);
        try {
            $db->query("UPDATE unit_routing_config SET is_active = NOT is_active WHERE id = ?", [$id]);
            
            $auth->logActivity('toggle_unit_routing', 'unit_routing_config', $id, 'Toggled unit routing status');
            $message = 'Unit routing status updated.';
        } catch (Exception $e) {
            $error = 'Failed to update unit routing status.';
        }
    }
}

// Office filter: OSDS, CID, SGOD (maps to approver_role_id 3, 4, 5)
$roleIdByOffice = [ 'OSDS' => ROLE_OSDS_CHIEF, 'CID' => ROLE_CID_CHIEF, 'SGOD' => ROLE_SGOD_CHIEF ];
$officeByRoleId = [ ROLE_OSDS_CHIEF => 'OSDS', ROLE_CID_CHIEF => 'CID', ROLE_SGOD_CHIEF => 'SGOD' ];
// Use POST filter params after form submit so list stays filtered; otherwise use GET
$getOrPost = function($key) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST' && array_key_exists($key, $_POST)) {
        return trim((string)$_POST[$key]);
    }
    return isset($_GET[$key]) ? trim((string)$_GET[$key]) : '';
};
$filterOffice    = $getOrPost('filter_office');
$filterSearch    = $getOrPost('filter_search');
$filterScope     = $getOrPost('filter_scope');      // applies_to: authority_to_travel, locator_slip, both
$filterApprover  = $getOrPost('filter_approver');   // role id
$filterTravelScope = $getOrPost('filter_travel_scope'); // all, local, international
$filterStatus    = $getOrPost('filter_status');    // '', '1' active, '0' inactive

$filterRoleId = ($filterOffice !== '' && isset($roleIdByOffice[$filterOffice])) ? $roleIdByOffice[$filterOffice] : null;

$sql = "SELECT urc.*, ar.role_name, ar.description as role_description, 
        o.office_code, o.office_name
    FROM unit_routing_config urc
    LEFT JOIN admin_roles ar ON urc.approver_role_id = ar.id
    LEFT JOIN sdo_offices o ON urc.office_id = o.id
    WHERE 1=1";
$params = [];
if ($filterRoleId !== null) {
    $sql .= " AND urc.approver_role_id = ?";
    $params[] = $filterRoleId;
}
if ($filterApprover !== '' && ctype_digit($filterApprover)) {
    $sql .= " AND urc.approver_role_id = ?";
    $params[] = (int)$filterApprover;
}
// Travel scope: inclusive so "Local" shows rows with travel_scope 'all' or 'local', "International" shows 'all' or 'international'
if ($filterTravelScope === 'local') {
    $sql .= " AND (urc.travel_scope = 'local' OR urc.travel_scope = 'all')";
} elseif ($filterTravelScope === 'international') {
    $sql .= " AND (urc.travel_scope = 'international' OR urc.travel_scope = 'all')";
} elseif ($filterTravelScope === 'all') {
    $sql .= " AND (urc.travel_scope = 'all' OR urc.travel_scope = 'local' OR urc.travel_scope = 'international')";
}
if ($filterStatus === '1' || $filterStatus === '0') {
    $sql .= " AND urc.is_active = ?";
    $params[] = (int)$filterStatus;
}
$sql .= " ORDER BY urc.sort_order ASC, urc.unit_name ASC";
$routingConfigs = $db->query($sql, $params)->fetchAll();

// Apply search filter in PHP (unit name, display name, office code/name)
if ($filterSearch !== '') {
    $search = mb_strtolower($filterSearch);
    $routingConfigs = array_filter($routingConfigs, function($c) use ($search) {
        $fields = [
            $c['unit_name'] ?? '',
            $c['unit_display_name'] ?? '',
            $c['office_code'] ?? '',
            $c['office_name'] ?? ''
        ];
        foreach ($fields as $f) {
            if (mb_strpos(mb_strtolower($f), $search) !== false) return true;
        }
        return false;
    });
    $routingConfigs = array_values($routingConfigs);
}

// Scope (applies_to) filter: per OFFICES_UNITS_CHIEFS_ARCHITECTURE.md, the same chiefs (OSDS/CID/SGOD)
// are responsible for both Authority to Travel (recommending) and Locator Slip (sole approver).
// So all unit_routing_config rows apply to both AT and LS—no filtering by scope; show all when any scope is selected.
// (If we later add per-row applies_to for exceptions, we could filter here; for now architecture = all chiefs handle both.)

// Ensure applies_to exists on each row (for DBs that haven't run migration yet)
foreach ($routingConfigs as &$c) {
    if (!array_key_exists('applies_to', $c)) {
        $c['applies_to'] = 'authority_to_travel';
    }
}
unset($c);

// Group by approver role for summary display (from full list for counts; summary respects filter via $routingConfigs)
$configsByRole = [];
foreach ($routingConfigs as $config) {
    $roleId = $config['approver_role_id'];
    if (!isset($configsByRole[$roleId])) {
        $configsByRole[$roleId] = [
            'role_name' => $config['role_name'],
            'role_description' => $config['role_description'],
            'units' => []
        ];
    }
    $configsByRole[$roleId]['units'][] = $config;
}
?>

<div class="page-header">
    <div class="header-content">
       
        <p class="header-subtitle">Manage unit-to-approver mappings for Authority to Travel approval</p>
    </div>
</div>

<!-- Tab Navigation -->
<div class="routing-page-tabs">
    <button class="routing-page-tab active" data-routing-tab="config" onclick="switchRoutingTab('config')">
        <i class="fas fa-cogs"></i> Unit Routing Config
    </button>
    <button class="routing-page-tab" data-routing-tab="reference" onclick="switchRoutingTab('reference')">
        <i class="fas fa-sitemap"></i> Routing Reference
    </button>
</div>

<!-- Tab: Unit Routing Config -->
<div id="tab-config" class="routing-tab-panel">

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

<!-- Filters: same design as Users / Authority to Travel / Locator Slip -->
<div class="filter-bar" style="background: white; padding: 24px; border-radius: var(--radius-lg); box-shadow: 0 4px 15px rgba(0,0,0,0.05); margin-bottom: 24px; border: 1px solid rgba(0,0,0,0.05);">
    <form method="get" class="filter-form">
        <div class="filter-group">
            <label for="filter_office">Office</label>
            <select name="filter_office" id="filter_office" class="filter-select">
                <option value="">All Offices</option>
                <?php foreach (array_keys($roleIdByOffice) as $off): ?>
                <option value="<?php echo htmlspecialchars($off); ?>" <?php echo $filterOffice === $off ? 'selected' : ''; ?>><?php echo htmlspecialchars($off); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_search">Search</label>
            <input type="text" name="filter_search" id="filter_search" class="filter-input" placeholder="Unit name, code..." value="<?php echo htmlspecialchars($filterSearch); ?>">
        </div>
        <div class="filter-group">
            <label for="filter_scope">Scope</label>
            <select name="filter_scope" id="filter_scope" class="filter-select">
                <option value="">All</option>
                <option value="authority_to_travel" <?php echo $filterScope === 'authority_to_travel' ? 'selected' : ''; ?>>Authority to Travel</option>
                <option value="locator_slip" <?php echo $filterScope === 'locator_slip' ? 'selected' : ''; ?>>Locator Slip</option>
                <option value="both" <?php echo $filterScope === 'both' ? 'selected' : ''; ?>>Both</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_approver">Approving Authority</label>
            <select name="filter_approver" id="filter_approver" class="filter-select">
                <option value="">All</option>
                <?php foreach ($roles as $role): ?>
                <option value="<?php echo (int)$role['id']; ?>" <?php echo $filterApprover === (string)$role['id'] ? 'selected' : ''; ?>><?php echo htmlspecialchars($role['role_name']); ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_travel_scope">Travel Scope</label>
            <select name="filter_travel_scope" id="filter_travel_scope" class="filter-select">
                <option value="">All</option>
                <option value="all" <?php echo $filterTravelScope === 'all' ? 'selected' : ''; ?>>All Travel</option>
                <option value="local" <?php echo $filterTravelScope === 'local' ? 'selected' : ''; ?>>Local Only</option>
                <option value="international" <?php echo $filterTravelScope === 'international' ? 'selected' : ''; ?>>International Only</option>
            </select>
        </div>
        <div class="filter-group">
            <label for="filter_status">Status</label>
            <select name="filter_status" id="filter_status" class="filter-select">
                <option value="">All</option>
                <option value="1" <?php echo $filterStatus === '1' ? 'selected' : ''; ?>>Active</option>
                <option value="0" <?php echo $filterStatus === '0' ? 'selected' : ''; ?>>Inactive</option>
            </select>
        </div>
        <div class="filter-actions">
            <button type="submit" class="btn btn-primary btn-sm"><i class="fas fa-filter"></i> Filter</button>
            <a href="<?php echo htmlspecialchars(navUrl('/unit-routing.php')); ?>" class="btn btn-secondary btn-sm"><i class="fas fa-times"></i> Clear</a>
        </div>
    </form>
</div>

<!-- Routing Configuration Table (same design as Users / Authority to Travel / Locator Slip) -->
<div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
    <div class="table-responsive" style="background: white; overflow-x: hidden;">
        <table class="data-table">
            <thead>
                <tr>
                    <th>Office</th>
                    <th>Unit Name</th>
                    <th>Display Name</th>
                    <th>Approving Authority</th>
                    <th>Travel Scope</th>
                    <th>Applies To</th>
                    <th>Status</th>
                    <th>Actions</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($routingConfigs)): ?>
                <tr>
                    <td colspan="8">
                        <div class="empty-state">
                            <span class="empty-icon"><i class="fas fa-route"></i></span>
                            <h3>No unit routing configurations found</h3>
                        </div>
                    </td>
                </tr>
                <?php else: ?>
                <?php foreach ($routingConfigs as $config): 
                    $officeName = $officeByRoleId[$config['approver_role_id']] ?? '-';
                    $scopeLabels = ['all' => 'All Travel', 'local' => 'Local Only', 'international' => 'International Only'];
                    $appliesToLabels = ['authority_to_travel' => 'Authority to Travel', 'locator_slip' => 'Locator Slip', 'both' => 'Both'];
                ?>
                <tr class="<?php echo $config['is_active'] ? '' : 'row-inactive'; ?>">
                    <td><span class="badge-office"><?php echo htmlspecialchars($officeName); ?></span></td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($config['office_code'] ?? $config['unit_name']); ?></div>
                    </td>
                    <td>
                        <div class="cell-primary"><?php echo htmlspecialchars($config['office_name'] ?? $config['unit_display_name']); ?></div>
                    </td>
                    <td>
                        <span class="unit-badge"><?php echo htmlspecialchars($config['role_name']); ?></span>
                    </td>
                    <td><?php echo $scopeLabels[$config['travel_scope']] ?? 'All Travel'; ?></td>
                    <td><?php echo $appliesToLabels[$config['applies_to']] ?? 'Authority to Travel'; ?></td>
                    <td>
                        <?php if ($config['is_active']): ?>
                        <span class="status-badge status-approved">Active</span>
                        <?php else: ?>
                        <span class="status-badge status-rejected">Inactive</span>
                        <?php endif; ?>
                    </td>
                    <td>
                        <div class="action-buttons">
                            <button class="btn btn-sm btn-outline" onclick="editConfig(<?php echo htmlspecialchars(json_encode($config)); ?>)" title="Edit">
                                <i class="fas fa-edit"></i>
                            </button>
                            <form method="POST" style="display: inline;">
                                <input type="hidden" name="action" value="toggle_status">
                                <input type="hidden" name="id" value="<?php echo $config['id']; ?>">
                                <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
                                <input type="hidden" name="filter_office" value="<?php echo htmlspecialchars($filterOffice); ?>">
                                <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filterSearch); ?>">
                                <input type="hidden" name="filter_scope" value="<?php echo htmlspecialchars($filterScope); ?>">
                                <input type="hidden" name="filter_approver" value="<?php echo htmlspecialchars($filterApprover); ?>">
                                <input type="hidden" name="filter_travel_scope" value="<?php echo htmlspecialchars($filterTravelScope); ?>">
                                <input type="hidden" name="filter_status" value="<?php echo htmlspecialchars($filterStatus); ?>">
                                <button type="submit" class="btn btn-sm btn-outline" title="<?php echo $config['is_active'] ? 'Deactivate' : 'Activate'; ?>">
                                    <i class="fas fa-<?php echo $config['is_active'] ? 'toggle-on' : 'toggle-off'; ?>"></i>
                                </button>
                            </form>
                            <button class="btn btn-sm btn-danger" onclick="confirmDelete(<?php echo $config['id']; ?>, '<?php echo htmlspecialchars($config['office_name'] ?? $config['unit_display_name'] ?? $config['unit_name']); ?>')" title="Delete">
                                <i class="fas fa-trash"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<!-- Create/Edit Modal -->
<div class="modal-overlay" id="routingModal">
    <div class="modal modal-lg">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-plus"></i> Add Unit Routing</h3>
            <button class="modal-close" type="button" onclick="closeModal()">&times;</button>
        </div>
        <form method="POST" id="routingForm">
            <input type="hidden" name="action" id="formAction" value="create">
            <input type="hidden" name="id" id="formId" value="">
            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
            <input type="hidden" name="filter_office" value="<?php echo htmlspecialchars($filterOffice); ?>">
            <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filterSearch); ?>">
            <input type="hidden" name="filter_scope" value="<?php echo htmlspecialchars($filterScope); ?>">
            <input type="hidden" name="filter_approver" value="<?php echo htmlspecialchars($filterApprover); ?>">
            <input type="hidden" name="filter_travel_scope" value="<?php echo htmlspecialchars($filterTravelScope); ?>">
            <input type="hidden" name="filter_status" value="<?php echo htmlspecialchars($filterStatus); ?>">
            
            <div class="modal-body">
                <div class="form-group">
                    <label class="form-label">Office / Unit <span class="required">*</span></label>
                    <select class="form-control" name="office_id" id="officeId" required>
                        <option value="">-- Select Office / Unit --</option>
                        <?php foreach ($offices as $office): ?>
                        <option value="<?php echo $office['id']; ?>">
                            <?php echo htmlspecialchars($office['office_name']); ?> (<?php echo htmlspecialchars($office['office_code']); ?>)
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <small class="form-hint">Matches the master office list used in registration and user management.</small>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Approving Authority <span class="required">*</span></label>
                    <select class="form-control" name="approver_role_id" id="approverRoleId" required>
                        <option value="">-- Select Approving Authority --</option>
                        <?php foreach ($roles as $role): ?>
                        <option value="<?php echo $role['id']; ?>">
                            <?php echo htmlspecialchars($role['role_name']); ?> - <?php echo htmlspecialchars($role['description']); ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label">Travel Scope</label>
                        <select class="form-control" name="travel_scope" id="travelScope">
                            <option value="all">All Travel (Local & International)</option>
                            <option value="local">Local Travel Only</option>
                            <option value="international">International Travel Only</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Applies To</label>
                        <select class="form-control" name="applies_to" id="appliesTo">
                            <option value="authority_to_travel">Authority to Travel</option>
                            <option value="locator_slip">Locator Slip</option>
                            <option value="both">Both</option>
                        </select>
                        <small class="form-hint">Whether this routing applies to Authority to Travel, Locator Slip, or both.</small>
                    </div>
                </div>
                
                <div class="form-group">
                    <label class="form-label">Sort Order</label>
                    <input type="number" class="form-control" name="sort_order" id="sortOrder" value="0" min="0">
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" name="is_active" id="isActive" checked>
                        <span>Active</span>
                    </label>
                </div>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeModal()">Cancel</button>
                <button type="submit" class="btn btn-primary" id="submitBtn">
                    <i class="fas fa-save"></i> Save Configuration
                </button>
            </div>
        </form>
    </div>
</div>

</div><!-- /tab-config -->

<!-- Tab: Routing Reference -->
<div id="tab-reference" class="routing-tab-panel" style="display:none;">

<div class="routing-ref-tabs">
                            <button class="routing-ref-tab active" data-ref-filter="at-local-within" onclick="filterRoutingRef(this)">AT — Local (Within Region)</button>
                            <button class="routing-ref-tab" data-ref-filter="at-local-outside" onclick="filterRoutingRef(this)">AT — Local (Outside Region)</button>
                                <button class="routing-ref-tab" data-ref-filter="at-international" onclick="filterRoutingRef(this)">AT — International (Official)</button>
                                <button class="routing-ref-tab" data-ref-filter="at-personal" onclick="filterRoutingRef(this)">AT — International & Local (Personal)</button>
                            <button class="routing-ref-tab" data-ref-filter="ls" onclick="filterRoutingRef(this)">Locator Slip</button>
                            <button class="routing-ref-tab" data-ref-filter="ps" onclick="filterRoutingRef(this)">Pass Slip</button>
</div>

<!-- AT — Official Local (Within Region) -->
<div class="routing-ref-table" data-ref-section="at-local-within">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Authority to Travel — Official Local (Within Region)</div>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Recommending</th>
                        <th>FINAL APPROVER</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>SDS</td><td class="ref-skip">—</td><td>Self-approved</td></tr>
                    <tr><td>ASDS</td><td class="ref-skip">—</td><td>SDS</td></tr>
                    <tr><td>SGOD CHIEF</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>CID CHIEF</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (SGOD units)</td><td>SGOD CHIEF</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (CID units)</td><td>CID CHIEF</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (OSDS units)</td><td>AO V - ADMINISTRATIVE</td><td>SDS</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- AT — Official Local (Outside Region) -->
<div class="routing-ref-table" data-ref-section="at-local-outside" style="display:none;">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Authority to Travel — Official Local (Outside Region)</div>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Recommending</th>
                        <th>Final Approver</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>SDS</td><td class="ref-skip">—</td><td>RD (forwarded to RO)</td></tr>
                    <tr><td>ASDS</td><td>SDS</td><td>RD (forwarded to RO)</td></tr>
                    <tr><td>SGOD CHIEF</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>CID CHIEF</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (SGOD units)</td><td>SGOD CHIEF</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (CID units)</td><td>CID CHIEF</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (OSDS units)</td><td>AO V - ADMINISTRATIVE</td><td>SDS</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- AT — Official International -->
<div class="routing-ref-table" data-ref-section="at-international" style="display:none;">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Authority to Travel — Official International</div>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Recommending</th>
                        <th>Final Approver</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>SDS</td><td>RD</td><td>DEPED SEC</td></tr>
                    <tr><td>ASDS</td><td>RD</td><td>DEPED SEC</td></tr>
                    <tr><td>SGOD CHIEF</td><td>SDS</td><td>DEPED SEC</td></tr>
                    <tr><td>CID CHIEF</td><td>SDS</td><td>DEPED SEC</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>SDS</td><td>DEPED SEC</td></tr>
                    <tr><td>Regular Employee</td><td>SDS</td><td>DEPED SEC</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- AT — Personal (All Scopes) -->
<div class="routing-ref-table" data-ref-section="at-personal" style="display:none;">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Authority to Travel — Personal (All Scopes)</div>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Final Approver</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>SDS</td><td>RD (forwarded to RO)</td></tr>
                    <tr><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>SGOD CHIEF</td><td>SDS</td></tr>
                    <tr><td>CID CHIEF</td><td>SDS</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>SDS</td></tr>
                    <tr><td>Regular Employee (SGOD units)</td><td>SGOD CHIEF</td></tr>
                    <tr><td>Regular Employee (CID units)</td><td>CID CHIEF</td></tr>
                    <tr><td>Regular Employee (OSDS units)</td><td>AO V - ADMINISTRATIVE</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Locator Slip -->
<div class="routing-ref-table" data-ref-section="ls" style="display:none;">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Locator Slip — Routing Chain</div>
        <p class="ref-section-note" style="margin: 0 20px 10px 20px; font-size: 0.85rem; color: var(--text-muted);">Single-step approval, no recommending stage.</p>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Approver</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>SDS</td><td>SDS</td></tr>
                    <tr><td>ASDS</td><td>SDS</td></tr>
                    <tr><td>SGOD CHIEF</td><td>ASDS</td></tr>
                    <tr><td>CID CHIEF</td><td>ASDS</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>ASDS</td></tr>
                    <tr><td>Regular Employee (SGOD units)</td><td>SGOD CHIEF</td></tr>
                    <tr><td>Regular Employee (CID units)</td><td>CID CHIEF</td></tr>
                    <tr><td>Regular Employee (OSDS units)</td><td>AO V - ADMINISTRATIVE</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Pass Slip -->
<div class="routing-ref-table" data-ref-section="ps" style="display:none;">
    <div class="data-card" style="border: 1px solid rgba(0,0,0,0.08); box-shadow: 0 8px 25px rgba(0,0,0,0.08); border-radius: var(--radius-xl); overflow: hidden;">
        <div class="ref-section-title">Pass Slip — Routing Chain</div>
        <p class="ref-section-note" style="margin: 0 20px 10px 20px; font-size: 0.85rem; color: var(--text-muted);">Single-step approval. After approval, GENERAL SERVICES records departure/arrival times.</p>
        <div class="table-responsive" style="background: white; overflow-x: hidden;">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>Requestor</th>
                        <th>Approver</th>
                        <th>Guard Monitoring</th>
                    </tr>
                </thead>
                <tbody>
                    <tr><td>ASDS</td><td>ASDS</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>SGOD CHIEF</td><td>ASDS</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>CID CHIEF</td><td>ASDS</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>AO V - ADMINISTRATIVE</td><td>ASDS</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>Regular Employee (SGOD units)</td><td>SGOD CHIEF</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>Regular Employee (CID units)</td><td>CID CHIEF</td><td>GENERAL SERVICES</td></tr>
                    <tr><td>Regular Employee (OSDS units)</td><td>AO V - ADMINISTRATIVE</td><td>GENERAL SERVICES</td></tr>
                </tbody>
            </table>
        </div>
    </div>
</div>

</div><!-- /tab-reference -->

<!-- Delete Confirmation Modal -->
<div class="modal-overlay" id="deleteModal">
    <div class="modal">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="margin-right: 8px;"></i> Confirm Delete</h3>
            <button class="modal-close" type="button" onclick="closeDeleteModal()">&times;</button>
        </div>
        <form method="POST" id="deleteForm">
            <input type="hidden" name="action" value="delete">
            <input type="hidden" name="id" id="deleteId" value="">
            <input type="hidden" name="_token" value="<?php echo $currentToken; ?>">
            <input type="hidden" name="filter_office" value="<?php echo htmlspecialchars($filterOffice); ?>">
            <input type="hidden" name="filter_search" value="<?php echo htmlspecialchars($filterSearch); ?>">
            <input type="hidden" name="filter_scope" value="<?php echo htmlspecialchars($filterScope); ?>">
            <input type="hidden" name="filter_approver" value="<?php echo htmlspecialchars($filterApprover); ?>">
            <input type="hidden" name="filter_travel_scope" value="<?php echo htmlspecialchars($filterTravelScope); ?>">
            <input type="hidden" name="filter_status" value="<?php echo htmlspecialchars($filterStatus); ?>">
            
            <div class="modal-body">
                <p>Are you sure you want to delete the routing configuration for <strong id="deleteUnitName"></strong>?</p>
                <p class="text-muted">This action cannot be undone.</p>
            </div>
            
            <div class="modal-footer">
                <button type="button" class="btn btn-secondary" onclick="closeDeleteModal()">Cancel</button>
                <button type="submit" class="btn btn-danger">
                    <i class="fas fa-trash"></i> Delete
                </button>
            </div>
        </form>
    </div>
</div>

<style>
.badge-office {
    background: var(--bg-tertiary, #e2e8f0);
    color: var(--text-secondary, #475569);
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 600;
}

.row-inactive {
    opacity: 0.6;
    background: rgba(0, 0, 0, 0.05);
}

.form-row {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 16px;
}

.badge-primary {
    background: linear-gradient(135deg, #0f4c75, #1b6ca8);
    color: white;
    padding: 4px 10px;
    border-radius: 12px;
    font-size: 0.8rem;
    font-weight: 500;
}

.action-buttons {
    display: flex;
    gap: 8px;
}

.btn-sm {
    padding: 6px 10px;
    font-size: 0.8rem;
}

.checkbox-label {
    display: flex;
    align-items: center;
    gap: 8px;
    cursor: pointer;
    color: var(--text, #e8f1f8);
}

.checkbox-label input {
    width: 18px;
    height: 18px;
}

.form-hint {
    font-size: 0.75rem;
    color: var(--text-muted, #7a9bb8);
    margin-top: 4px;
}

.empty-state {
    text-align: center;
    padding: 60px 20px;
    color: var(--text-muted, #7a9bb8);
}

.empty-state i {
    font-size: 3rem;
    margin-bottom: 16px;
    opacity: 0.5;
}

.text-muted {
    color: var(--text-muted, #7a9bb8);
    font-size: 0.9rem;
}

@media (max-width: 600px) {
    .form-row {
        grid-template-columns: 1fr;
    }
}

/* ===== Routing Page Tabs ===== */
.routing-page-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 24px;
    background: var(--card-bg);
    border: 2px solid rgba(0, 0, 0, 0.12);
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    box-shadow: 0 4px 16px rgba(0, 0, 0, 0.15);
}

.routing-page-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    gap: 8px;
    padding: 14px 18px;
    font-weight: 600;
    font-size: 0.9rem;
    color: var(--text-secondary);
    background: transparent;
    border: none;
    border-bottom: 3px solid transparent;
    transition: all 0.2s ease;
    cursor: pointer;
}

.routing-page-tab:hover {
    color: var(--primary);
    background: rgba(99, 102, 241, 0.06);
}

.routing-page-tab.active {
    color: var(--primary);
    background: rgba(99, 102, 241, 0.08);
    border-bottom-color: var(--primary);
}

.routing-page-tab i {
    font-size: 1rem;
}

.routing-page-tab + .routing-page-tab {
    border-left: 1px solid rgba(0, 0, 0, 0.08);
}

@media (max-width: 768px) {
    .routing-page-tabs {
        flex-wrap: wrap;
    }
    .routing-page-tab {
        flex: 1 1 45%;
        padding: 10px 12px;
        font-size: 0.82rem;
    }
}

/* ===== Routing Reference Tab Styles ===== */
.routing-ref-tabs {
    display: flex;
    gap: 0;
    margin-bottom: 20px;
    background: #f8fafc;
    border: 1px solid #dbe3ee;
    border-radius: var(--radius-lg, 12px);
    overflow: hidden;
    box-shadow: 0 2px 10px rgba(15, 23, 42, 0.08);
}

.routing-ref-tab {
    flex: 1;
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 12px 16px;
    text-decoration: none;
    font-weight: 600;
    font-size: 0.85rem;
    color: #334155;
    background: transparent;
    border: none;
    border-right: 1px solid #e2e8f0;
    transition: all 0.2s ease;
    cursor: pointer;
}

.routing-ref-tab:last-child {
    border-right: none;
}

.routing-ref-tab:hover {
    background: #eef2f7;
    color: #0f172a;
}

.routing-ref-tab.active {
    background: #1e293b;
    color: #fff;
}

.ref-section-title {
    padding: 16px 20px 0;
    font-size: 1.1rem;
    font-weight: 800;
    color: #0f172a;
}

.ref-section-note {
    padding: 0 20px;
    margin: 6px 0 0;
    font-size: 0.85rem;
    color: var(--text-secondary, #64748b);
}

.ref-skip {
    opacity: 0.35;
}

/* Make headers bold and more prominent */
.routing-ref-table .data-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #c3ccd8;
}

.routing-ref-table .data-table thead th {
    font-weight: 800 !important;
    font-size: 0.85rem !important;
    text-transform: uppercase;
    color: #0f172a;
    background-color: #d2d8e0;
    letter-spacing: 0.03em;
    border: 1px solid #c3ccd8;
    text-align: left;
    line-height: 1.15;
}

.routing-ref-table .data-table tbody td {
    font-weight: 500;
    color: #1e293b;
    border: 1px solid #c3ccd8;
}

.routing-ref-table .data-table tbody tr:nth-child(even) td {
    background: #f8fafc;
}

.routing-ref-table .table-responsive {
    overflow: hidden !important;
}

#tab-reference {
    overflow: hidden;
}

.routing-ref-table {
    min-height: 540px;
}

.routing-ref-table .data-card {
    height: 100%;
}

.routing-ref-table .data-table {
    table-layout: fixed;
}

.routing-ref-table .data-table th,
.routing-ref-table .data-table td {
    white-space: normal;
    word-break: break-word;
}

@media (max-width: 800px) {
    .routing-ref-tabs {
        flex-wrap: wrap;
    }
    .routing-ref-tab {
        flex: 1 1 33%;
        padding: 10px 12px;
        font-size: 0.76rem;
    }
    .routing-ref-tab + .routing-ref-tab {
        border-right: 1px solid rgba(0, 0, 0, 0.08);
    }
}
</style>

<script>
function showCreateModal() {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-plus"></i> Add Unit Routing';
    document.getElementById('formAction').value = 'create';
    document.getElementById('formId').value = '';
    document.getElementById('officeId').value = '';
    document.getElementById('approverRoleId').value = '';
    document.getElementById('travelScope').value = 'all';
    document.getElementById('appliesTo').value = 'authority_to_travel';
    document.getElementById('sortOrder').value = '0';
    document.getElementById('isActive').checked = true;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Save Configuration';
    document.getElementById('routingModal').classList.add('active');
}

function editConfig(config) {
    document.getElementById('modalTitle').innerHTML = '<i class="fas fa-edit"></i> Edit Unit Routing';
    document.getElementById('formAction').value = 'update';
    document.getElementById('formId').value = config.id;
    document.getElementById('officeId').value = config.office_id || '';
    document.getElementById('approverRoleId').value = config.approver_role_id;
    document.getElementById('travelScope').value = config.travel_scope || 'all';
    document.getElementById('appliesTo').value = config.applies_to || 'authority_to_travel';
    document.getElementById('sortOrder').value = config.sort_order || 0;
    document.getElementById('isActive').checked = config.is_active == 1;
    document.getElementById('submitBtn').innerHTML = '<i class="fas fa-save"></i> Update Configuration';
    document.getElementById('routingModal').classList.add('active');
}

function closeModal() {
    document.getElementById('routingModal').classList.remove('active');
}

function confirmDelete(id, unitName) {
    document.getElementById('deleteId').value = id;
    document.getElementById('deleteUnitName').textContent = unitName;
    document.getElementById('deleteModal').classList.add('active');
}

function closeDeleteModal() {
    document.getElementById('deleteModal').classList.remove('active');
}

document.addEventListener('DOMContentLoaded', function() {
    var routingModal = document.getElementById('routingModal');
    var deleteModal = document.getElementById('deleteModal');

    if (routingModal) {
        routingModal.addEventListener('click', function(e) {
            if (e.target === routingModal) {
                closeModal();
            }
        });
    }

    if (deleteModal) {
        deleteModal.addEventListener('click', function(e) {
            if (e.target === deleteModal) {
                closeDeleteModal();
            }
        });
    }
});

// Close modal on Escape key
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') {
        closeModal();
        closeDeleteModal();
    }
});

// ===== Routing Tab Switching =====
function switchRoutingTab(tab) {
    // Toggle tab buttons
    document.querySelectorAll('.routing-page-tab[data-routing-tab]').forEach(function(btn) {
        btn.classList.toggle('active', btn.getAttribute('data-routing-tab') === tab);
    });
    // Toggle panels
    document.getElementById('tab-config').style.display = (tab === 'config') ? '' : 'none';
    document.getElementById('tab-reference').style.display = (tab === 'reference') ? '' : 'none';
}

// ===== Routing Reference Filter =====
function filterRoutingRef(btn) {
    var filter = btn.getAttribute('data-ref-filter');
    // Toggle active filter button
    document.querySelectorAll('.routing-ref-tab').forEach(function(b) {
        b.classList.toggle('active', b === btn);
    });
    document.querySelectorAll('.routing-ref-table').forEach(function(sec) {
        sec.style.display = (sec.getAttribute('data-ref-section') === filter) ? '' : 'none';
    });
}

// Submit filter form on Enter (search/filter without clicking Filter)
(function() {
    var form = document.querySelector('.filter-bar .filter-form');
    if (form) {
        form.addEventListener('keydown', function(e) {
            if (e.key === 'Enter') {
                e.preventDefault();
                form.submit();
            }
        });
    }
})();

</script>

<?php require_once __DIR__ . '/../includes/footer.php'; ?>
