<?php
/**
 * Admin Panel Configuration
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

// Set timezone to Manila, Philippines
date_default_timezone_set('Asia/Manila');

// Session configuration
define('ADMIN_SESSION_NAME', 'SDO_ALPAS_ADMIN');
define('ADMIN_SESSION_LIFETIME', 3600 * 8); // 8 hours
define('TOKEN_LIFETIME', 3600 * 8); // 8 hours for session tokens

// Admin panel settings
define('ADMIN_TITLE', 'SDO ALPAS');
define('ADMIN_FULL_TITLE', 'Authority to Travel, Locator and Pass slip Approval System (ALPAS)');
define('ITEMS_PER_PAGE', 15);

// Base URL
define('BASE_URL', '/SDO-alpas');
define('ADMIN_URL', '/SDO-alpas/admin');

// Role IDs - Aligned with SQL admin_roles
define('ROLE_SUPERADMIN', 1);  // System Administrator - Executive Override
define('ROLE_ASDS', 2);        // ASDS - Approves Office Chief locator slips
define('ROLE_OSDS_CHIEF', 3);  // AO V - Recommending for OSDS units
define('ROLE_CID_CHIEF', 4);   // CID Chief - Recommending for CID
define('ROLE_SGOD_CHIEF', 5);  // SGOD Chief - Recommending for SGOD
define('ROLE_USER', 6);        // Regular Employee
define('ROLE_SDS', 7);         // SDS - Final approver for all travel requests
define('ROLE_GUARD', 8);       // Guard on Duty - Pass Slip monitoring

// Unit Head Roles Array
define('UNIT_HEAD_ROLES', [ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF]);

// Office Chief Roles Array (Chiefs that route to ASDS for LS, to SDS for AT)
define('OFFICE_CHIEF_ROLES', [ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF]);

// DIRECT_SDS_POSITIONS removed per DepEd Order 043 s. 2022
// Attorney III, Accountant III, AO V now follow standard chief -> SDS chain

// OSDS Units (under AO V supervision)
// Updated per Routing Directive - applies to local and international travel
// Note: Records is a separate unit from Property and Supply.
define('OSDS_UNITS', [
    'OSDS',
    'Personnel',
    'Property and Supply',
    'Records',
    'Cash',
    'Procurement',
    'General Services',
    'Legal',
    'ICT',
    'Accounting',
    'Budget',
    'Administrative'
]);

// Role to Office Mapping for Routing
// Updated per Routing Directive: OSDS Chief as sole approving authority for all OSDS units
define('ROLE_OFFICE_MAP', [
    'CID' => ROLE_CID_CHIEF,
    // CID units map to CID_CHIEF
    'IM' => ROLE_CID_CHIEF,
    'LRM' => ROLE_CID_CHIEF,
    'ALS' => ROLE_CID_CHIEF,
    'DIS' => ROLE_CID_CHIEF,
    'SGOD' => ROLE_SGOD_CHIEF,
    // SGOD units map to SGOD_CHIEF
    'SMME' => ROLE_SGOD_CHIEF,
    'HRD' => ROLE_SGOD_CHIEF,
    'SMN' => ROLE_SGOD_CHIEF,
    'PR' => ROLE_SGOD_CHIEF,
    'DRRM' => ROLE_SGOD_CHIEF,
    'EF' => ROLE_SGOD_CHIEF,
    'SHN_DENTAL' => ROLE_SGOD_CHIEF,
    'SHN_MEDICAL' => ROLE_SGOD_CHIEF,
    'SHN' => ROLE_SGOD_CHIEF,
    // OSDS units map to OSDS_CHIEF (AO V)
    'OSDS' => ROLE_OSDS_CHIEF,
    'Personnel' => ROLE_OSDS_CHIEF,
    'Property and Supply' => ROLE_OSDS_CHIEF,
    'Records' => ROLE_OSDS_CHIEF,
    'Cash' => ROLE_OSDS_CHIEF,
    'Procurement' => ROLE_OSDS_CHIEF,
    'General Services' => ROLE_OSDS_CHIEF,
    'Legal' => ROLE_OSDS_CHIEF,
    'ICT' => ROLE_OSDS_CHIEF,
    'Accounting' => ROLE_OSDS_CHIEF,
    'Budget' => ROLE_OSDS_CHIEF,
    'Administrative' => ROLE_OSDS_CHIEF
]);

// Offices supervised by each Unit Head Role (reverse mapping)
// Updated per Routing Directive: OSDS Chief supervises all OSDS units
define('UNIT_HEAD_OFFICES', [
    ROLE_CID_CHIEF => ['CID', 'IM', 'LRM', 'ALS', 'DIS'],
    ROLE_SGOD_CHIEF => ['SGOD', 'SMME', 'HRD', 'SMN', 'PR', 'DRRM', 'EF', 'SHN_DENTAL', 'SHN_MEDICAL', 'SHN'],
    ROLE_OSDS_CHIEF => [
        'OSDS',
        'Personnel',
        'Property and Supply',
        'Records',
        'Cash',
        'Procurement',
        'General Services',
        'Legal',
        'ICT',
        'Accounting',
        'Budget',
        'Administrative'
    ]
]);

// Recommending Authority Names by Role
define('RECOMMENDING_AUTHORITY_MAP', [
    ROLE_CID_CHIEF => 'CID Chief',
    ROLE_SGOD_CHIEF => 'SGOD Chief',
    ROLE_OSDS_CHIEF => 'AO V',
    ROLE_ASDS => 'ASDS',
    ROLE_SDS => 'SDS'
]);

// Approving Authority Names
define('APPROVING_AUTHORITY_MAP', [
    ROLE_ASDS => 'ASDS',
    ROLE_SUPERADMIN => 'Superadmin',
    ROLE_SDS => 'SDS',
    ROLE_CID_CHIEF => 'CID Chief',
    ROLE_SGOD_CHIEF => 'SGOD Chief',
    ROLE_OSDS_CHIEF => 'AO V'
]);

// Status configuration for requests
define('STATUS_CONFIG', [
    'pending' => [
        'label' => 'Pending',
        'color' => '#f59e0b',
        'bg' => '#fef3c7',
        'icon' => '<i class="fas fa-clock"></i>'
    ],
    'recommended' => [
        'label' => 'Recommended',
        'color' => '#3b82f6',
        'bg' => '#dbeafe',
        'icon' => '<i class="fas fa-thumbs-up"></i>'
    ],
    'approved' => [
        'label' => 'Approved',
        'color' => '#10b981',
        'bg' => '#d1fae5',
        'icon' => '<i class="fas fa-check-circle"></i>'
    ],
    'rejected' => [
        'label' => 'Rejected',
        'color' => '#ef4444',
        'bg' => '#fee2e2',
        'icon' => '<i class="fas fa-times-circle"></i>'
    ],
    'cancelled' => [
        'label' => 'Cancelled',
        'color' => '#6b7280',
        'bg' => '#f3f4f6',
        'icon' => '<i class="fas fa-ban"></i>'
    ]
]);

// Travel types for Locator Slip
define('TRAVEL_TYPES', [
    'official_business' => 'Official Business',
    'official_time' => 'Official Time'
]);

// Travel categories for Authority to Travel
define('AT_CATEGORIES', [
    'official' => 'Official',
    'personal' => 'Personal'
]);

// Travel scope for Authority to Travel (per DepEd Order 043 s. 2022)
define('AT_SCOPES', [
    'local' => 'Local',
    'international' => 'International'
]);

// Local travel types (within_region / outside_region)
define('AT_LOCAL_TYPES', [
    'within_region' => 'Within Region',
    'outside_region' => 'Outside Region'
]);

// DOCX Templates
define('DOCX_TEMPLATES', [
    'locator_slip' => 'Locator-Slip.docx',
    'pass_slip' => 'Pass-Slip.docx',
    'at_local' => 'AUTHORITY-TO-TRAVEL-SAMPLE.docx',
    'at_outside_region' => 'ANNEX-D-PERSONAL-TRAVEL-AUTHORITY_SAMPLE.docx',
    'at_personal' => 'ANNEX-D-PERSONAL-TRAVEL-AUTHORITY_SAMPLE.docx'
]);

// Template directory
define('TEMPLATE_DIR', __DIR__ . '/../reference-forms/doc-forms/');
define('GENERATED_DIR', __DIR__ . '/../uploads/generated/');

// LibreOffice PDF Conversion (via PowerShell + soffice --headless)
// Path to the PowerShell conversion script
define('PDF_CONVERT_SCRIPT', __DIR__ . '/../scripts/convert-to-pdf.ps1');

// Timeout in seconds for PDF conversion process (default: 120)
define('PDF_CONVERT_TIMEOUT', 120);

// Directory for conversion error logs
define('CONVERSION_LOG_DIR', __DIR__ . '/../logs/');

// Backward-compatible aliases for older references
if (!defined('WORD_CONVERT_SCRIPT'))  define('WORD_CONVERT_SCRIPT', PDF_CONVERT_SCRIPT);
if (!defined('WORD_CONVERT_TIMEOUT')) define('WORD_CONVERT_TIMEOUT', PDF_CONVERT_TIMEOUT);

// Permission definitions
define('PERMISSIONS', [
    'requests.file' => 'File LS/AT requests',
    'requests.own' => 'View own requests',
    'requests.view' => 'View all requests',
    'requests.approve' => 'Approve/Reject requests',
    'users.view' => 'View users',
    'users.create' => 'Create users',
    'users.update' => 'Update users',
    'users.delete' => 'Delete users',
    'users.approve' => 'Approve user registrations',
    'logs.view' => 'View activity logs',
    'analytics.view' => 'View analytics',
    'settings.manage' => 'Manage system settings'
]);

// Offices/Units in SDO
// DEPRECATED: Use getSDOOfficesFromDB() instead
// This constant is kept for backward compatibility only
define('SDO_OFFICES', [
    // Executive Offices
    'SDS' => 'Office of the Schools Division Superintendent',
    'ASDS' => 'Office of the Assistant Schools Division Superintendent',
    // Divisions
    'CID' => 'Curriculum Implementation Division',
    // CID Units (all route to CID Chief)
    'IM' => 'Instructional Management',
    'LRM' => 'Learning Resource Management',
    'ALS' => 'Alternative Learning System',
    'DIS' => 'District Instructional Supervision',
    'SGOD' => 'School Governance and Operations Division',
    // SGOD Units (all route to SGOD Chief)
    'SMME' => 'School Management Monitoring and Evaluation',
    'HRD' => 'Human Resource Development',
    'SMN' => 'Social Mobilization and Networking',
    'PR' => 'Planning and Research',
    'DRRM' => 'Disaster Risk Reduction and Management',
    'EF' => 'Education Facilities',
    'SHN_DENTAL' => 'School Health and Nutrition (Dental)',
    'SHN_MEDICAL' => 'School Health and Nutrition (Medical)',
    'SHN' => 'School Health and Nutrition',
    // OSDS Units (all route to OSDS Chief) - display without extensions
    'OSDS' => 'Office of the Schools Division Superintendent Staff',
    'Personnel' => 'Personnel',
    'Property and Supply' => 'Property and Supply',
    'Records' => 'Records',
    'Cash' => 'Cash',
    'Procurement' => 'Procurement',
    'General Services' => 'General Services',
    'Legal' => 'Legal',
    'ICT' => 'Information and Communication Technology',
    'Accounting' => 'Finance (Accounting)',
    'Budget' => 'Finance (Budget)',
    'Administrative' => 'Administrative'
]);

/**
 * Get all offices from master database table
 * @param bool $activeOnly Return only active offices
 * @return array Array of offices with id, code, and name
 */
function getSDOOfficesFromDB($activeOnly = true)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT id, office_code, office_name, office_type, is_osds_unit, approver_role_id 
                FROM sdo_offices";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY sort_order ASC, office_name ASC";
        return $db->query($sql)->fetchAll();
    } catch (Exception $e) {
        // Fallback to static array if table doesn't exist
        $result = [];
        foreach (SDO_OFFICES as $code => $name) {
            $result[] = [
                'id' => null,
                'office_code' => $code,
                'office_name' => $name,
                'office_type' => 'section',
                'is_osds_unit' => in_array($code, OSDS_UNITS) ? 1 : 0,
                'approver_role_id' => ROLE_OFFICE_MAP[$code] ?? ROLE_OSDS_CHIEF
            ];
        }
        return $result;
    }
}

/**
 * Get office by ID from database
 * @param int $officeId The office ID
 * @return array|null Office data or null
 */
function getOfficeById($officeId)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT * FROM sdo_offices WHERE id = ?";
        return $db->query($sql, [$officeId])->fetch();
    } catch (Exception $e) {
        return null;
    }
}

/**
 * Get approver role ID for an office by office ID
 * @param int $officeId The office ID
 * @return int Role ID of approving authority
 */
function getApproverRoleByOfficeId($officeId)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT approver_role_id FROM sdo_offices WHERE id = ? AND is_active = 1";
        $result = $db->query($sql, [$officeId])->fetch();
        if ($result && $result['approver_role_id']) {
            return (int) $result['approver_role_id'];
        }
    } catch (Exception $e) {
        // Fall through to default
    }
    return ROLE_OSDS_CHIEF; // Default to OSDS Chief
}

/**
 * Get all OSDS unit IDs from database
 * @return array Array of office IDs that are OSDS units
 */
function getOSDSUnitIds()
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT id FROM sdo_offices WHERE is_osds_unit = 1 AND is_active = 1";
        $results = $db->query($sql)->fetchAll();
        return array_column($results, 'id');
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Top-level office codes for the Office dropdown (exactly 3 options).
 * Unit dropdown cascades from the selected office.
 */
define('TOP_OFFICE_CODES', [
    'OSDS' => 'Office of the Schools Division Superintendent Staff (OSDS)',
    'SGOD' => 'School Governance and Operations Division (SGOD)',
    'CID' => 'Curriculum Implementation Division (CID)'
]);

/**
 * Get the 3 options for the Office dropdown.
 * @return array [['code' => 'OSDS', 'name' => '...'], ...]
 */
function getSDOOfficesForOfficeDropdown()
{
    $out = [];
    foreach (TOP_OFFICE_CODES as $code => $name) {
        $out[] = ['code' => $code, 'name' => $name];
    }
    return $out;
}

/**
 * Get units (sdo_offices rows) that belong to the given top-level office code.
 * Returns only child units/sections, excluding the main office itself.
 * OSDS: is_osds_unit=1 but NOT office_code='OSDS'; SGOD/CID: parent_office_id = division id only.
 *
 * @param string $officeCode One of 'OSDS', 'SGOD', 'CID'
 * @return array Array of ['id' => int, 'office_code' => string, 'office_name' => string]
 */
function getSDOUnitsByOfficeCode($officeCode)
{
    try {
        $db = Database::getInstance();
        $code = strtoupper(trim($officeCode));
        if (!defined('TOP_OFFICE_CODES') || !isset(TOP_OFFICE_CODES[$code])) {
            return [];
        }
        if ($code === 'OSDS') {
            $sql = "SELECT id, office_code, office_name FROM sdo_offices 
                    WHERE is_active = 1 AND is_osds_unit = 1 AND office_code NOT IN ('OSDS', 'Finance')
                    ORDER BY sort_order ASC, office_name ASC";
            return $db->query($sql)->fetchAll();
        }
        if ($code === 'SGOD') {
            $sql = "SELECT o.id, o.office_code, o.office_name FROM sdo_offices o 
                    WHERE o.is_active = 1 
                    AND o.parent_office_id = (SELECT id FROM sdo_offices WHERE office_code = 'SGOD' AND is_active = 1 LIMIT 1)
                    AND o.office_code != 'SGOD'
                    ORDER BY o.sort_order ASC, o.office_name ASC";
            return $db->query($sql)->fetchAll();
        }
        if ($code === 'CID') {
            $sql = "SELECT o.id, o.office_code, o.office_name FROM sdo_offices o 
                    WHERE o.is_active = 1 
                    AND o.parent_office_id = (SELECT id FROM sdo_offices WHERE office_code = 'CID' AND is_active = 1 LIMIT 1)
                    AND o.office_code != 'CID'
                    ORDER BY o.sort_order ASC, o.office_name ASC";
            return $db->query($sql)->fetchAll();
        }
        return [];
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get units by office code keyed for JS: { 'OSDS' => [...], 'SGOD' => [...], 'CID' => [...] }.
 * @return array
 */
function getUnitsByOfficeForJs()
{
    $out = [];
    foreach (array_keys(TOP_OFFICE_CODES) as $code) {
        $out[$code] = getSDOUnitsByOfficeCode($code);
    }
    return $out;
}

/**
 * Derive top-level office code (OSDS/SGOD/CID) from a unit's office_id.
 * Used when opening Edit User modal to set the Office dropdown from the user's office_id.
 *
 * @param int|null $unitId sdo_offices.id (user's office_id)
 * @return string '' or 'OSDS'|'SGOD'|'CID'
 */
function getOfficeCodeFromUnitId($unitId)
{
    if (!$unitId) {
        return '';
    }
    $office = getOfficeById($unitId);
    if (!$office) {
        return '';
    }
    $code = $office['office_code'] ?? '';
    if (defined('TOP_OFFICE_CODES') && isset(TOP_OFFICE_CODES[$code])) {
        return $code;
    }
    if (!empty($office['is_osds_unit'])) {
        return 'OSDS';
    }
    $parentId = $office['parent_office_id'] ?? null;
    if ($parentId) {
        $parent = getOfficeById($parentId);
        if ($parent && defined('TOP_OFFICE_CODES') && isset(TOP_OFFICE_CODES[$parent['office_code'] ?? ''])) {
            return $parent['office_code'];
        }
    }
    foreach (['SGOD', 'CID'] as $div) {
        $units = getSDOUnitsByOfficeCode($div);
        foreach ($units as $u) {
            if ((int) ($u['id'] ?? 0) === (int) $unitId) {
                return $div;
            }
        }
    }
    return '';
}

/**
 * Helper function to check if user is a final approver (ASDS or Superadmin)
 */
function isApprover($roleId)
{
    return in_array($roleId, [ROLE_SUPERADMIN, ROLE_ASDS, ROLE_SDS]);
}

/**
 * Helper function to check if user is a unit head (can recommend)
 */
function isUnitHead($roleId)
{
    return in_array($roleId, UNIT_HEAD_ROLES);
}

/**
 * Helper function to check if user is regular employee
 */
function isEmployee($roleId)
{
    return $roleId == ROLE_USER;
}

/**
 * Helper function to check if user is a guard
 */
function isGuard($roleId)
{
    return $roleId == ROLE_GUARD;
}

/**
 * Helper function to determine recommending authority based on office
 */
function getRecommendingRoleForOffice($office)
{
    return ROLE_OFFICE_MAP[$office] ?? ROLE_OSDS_CHIEF; // Default to OSDS Chief
}

/**
 * Helper function to get recommending authority name by role
 */
function getRecommendingAuthorityName($roleId)
{
    return RECOMMENDING_AUTHORITY_MAP[$roleId] ?? null;
}

/**
 * Helper function to get approving authority name by role
 */
function getApprovingAuthorityName($roleId)
{
    return APPROVING_AUTHORITY_MAP[$roleId] ?? 'ASDS';
}

/**
 * Helper function to get status badge HTML
 */
function getStatusBadge($status)
{
    $config = STATUS_CONFIG[$status] ?? STATUS_CONFIG['pending'];
    return '<span class="status-badge status-' . $status . '">' . $config['icon'] . ' ' . $config['label'] . '</span>';
}

/**
 * Helper function to format tracking number display
 */
function formatTrackingNo($trackingNo)
{
    return '<span class="tracking-no">' . htmlspecialchars($trackingNo) . '</span>';
}

/**
 * Get approver role ID from database routing config
 * Falls back to static ROLE_OFFICE_MAP if not found in DB
 * @param string $unitName The unit/office name
 * @param string|null $travelScope Optional travel scope filter (local, international, all)
 * @return int Role ID of the approving authority
 */
function getApproverRoleFromDB($unitName, $travelScope = null)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT approver_role_id FROM unit_routing_config 
                WHERE unit_name = ? AND is_active = 1";
        $params = [$unitName];

        if ($travelScope && $travelScope !== 'all') {
            $sql .= " AND (travel_scope = ? OR travel_scope = 'all')";
            $params[] = $travelScope;
        }

        $sql .= " LIMIT 1";
        $result = $db->query($sql, $params)->fetch();

        if ($result && isset($result['approver_role_id'])) {
            return (int) $result['approver_role_id'];
        }
    } catch (Exception $e) {
        // Fall back to static mapping on DB error
    }

    // Fallback to static mapping
    return ROLE_OFFICE_MAP[$unitName] ?? ROLE_OSDS_CHIEF;
}

/**
 * Get all unit routing configurations from database
 * @param bool $activeOnly Whether to return only active configurations
 * @return array Array of routing configurations
 */
function getAllUnitRoutingConfigs($activeOnly = true)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT urc.*, ar.role_name, ar.description as role_description 
                FROM unit_routing_config urc
                LEFT JOIN admin_roles ar ON urc.approver_role_id = ar.id";

        if ($activeOnly) {
            $sql .= " WHERE urc.is_active = 1";
        }

        $sql .= " ORDER BY urc.sort_order ASC, urc.unit_name ASC";

        return $db->query($sql)->fetchAll();
    } catch (Exception $e) {
        return [];
    }
}

/**
 * Get units supervised by a specific approver role from database
 * @param int $roleId The approver role ID
 * @return array Array of unit names
 */
function getUnitsByApproverRole($roleId)
{
    try {
        $db = Database::getInstance();
        $sql = "SELECT unit_name FROM unit_routing_config 
                WHERE approver_role_id = ? AND is_active = 1 
                ORDER BY sort_order ASC";

        $results = $db->query($sql, [$roleId])->fetchAll();
        return array_column($results, 'unit_name');
    } catch (Exception $e) {
        // Fallback to static mapping
        return UNIT_HEAD_OFFICES[$roleId] ?? [];
    }
}
