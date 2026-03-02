<?php
/**
 * Comprehensive Routing Test Script
 * Tests all form types (AT, LS, PS) with all role/office/travel type combinations
 * Verifies role name changes (AO V - ADMINISTRATIVE, CID CHIEF, SGOD CHIEF, GENERAL SERVICES) work correctly
 * 
 * Run: php tests/test_routing.php
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../models/PassSlip.php';

// =============================
//   Helpers
// =============================
$passed = 0;
$failed = 0;
$errors = [];

function test($label, $condition, $detail = '') {
    global $passed, $failed, $errors;
    if ($condition) {
        $passed++;
        echo "  [PASS] $label\n";
    } else {
        $failed++;
        $msg = "  [FAIL] $label" . ($detail ? " — $detail" : "");
        echo $msg . "\n";
        $errors[] = $msg;
    }
}

function section($title) {
    echo "\n" . str_repeat('=', 70) . "\n";
    echo "  $title\n";
    echo str_repeat('=', 70) . "\n";
}

// =============================
//   1. ROLE CONSTANTS STILL CORRECT
// =============================
section("1. Role Constants Integrity");

test("ROLE_SUPERADMIN = 1", ROLE_SUPERADMIN === 1);
test("ROLE_ASDS = 2", ROLE_ASDS === 2);
test("ROLE_OSDS_CHIEF = 3", ROLE_OSDS_CHIEF === 3);
test("ROLE_CID_CHIEF = 4", ROLE_CID_CHIEF === 4);
test("ROLE_SGOD_CHIEF = 5", ROLE_SGOD_CHIEF === 5);
test("ROLE_USER = 6", ROLE_USER === 6);
test("ROLE_SDS = 7", ROLE_SDS === 7);
test("ROLE_GUARD = 8", ROLE_GUARD === 8);
test("UNIT_HEAD_ROLES contains 3,4,5", UNIT_HEAD_ROLES === [3, 4, 5]);
test("OFFICE_CHIEF_ROLES contains 3,4,5", OFFICE_CHIEF_ROLES === [3, 4, 5]);
test("isGuard(8) = true", isGuard(8) === true);
test("isGuard(3) = false", isGuard(3) === false);

// =============================
//   2. DB ROLE NAMES MATCH CODE
// =============================
section("2. Database Role Names Match Code Expectations");

$db = Database::getInstance();
$roles = $db->query("SELECT id, role_name FROM admin_roles ORDER BY id")->fetchAll();
$roleMap = [];
foreach ($roles as $r) $roleMap[$r['id']] = $r['role_name'];

test("DB role 3 = 'AO V - ADMINISTRATIVE'", ($roleMap[3] ?? '') === 'AO V - ADMINISTRATIVE', "Got: " . ($roleMap[3] ?? 'MISSING'));
test("DB role 4 = 'CID CHIEF'", ($roleMap[4] ?? '') === 'CID CHIEF', "Got: " . ($roleMap[4] ?? 'MISSING'));
test("DB role 5 = 'SGOD CHIEF'", ($roleMap[5] ?? '') === 'SGOD CHIEF', "Got: " . ($roleMap[5] ?? 'MISSING'));
test("DB role 8 = 'GENERAL SERVICES'", ($roleMap[8] ?? '') === 'GENERAL SERVICES', "Got: " . ($roleMap[8] ?? 'MISSING'));
test("DB role 1 = 'SUPERADMIN'", ($roleMap[1] ?? '') === 'SUPERADMIN', "Got: " . ($roleMap[1] ?? 'MISSING'));
test("DB role 2 = 'ASDS'", ($roleMap[2] ?? '') === 'ASDS', "Got: " . ($roleMap[2] ?? 'MISSING'));
test("DB role 6 = 'USER'", ($roleMap[6] ?? '') === 'USER', "Got: " . ($roleMap[6] ?? 'MISSING'));
test("DB role 7 = 'SDS'", ($roleMap[7] ?? '') === 'SDS', "Got: " . ($roleMap[7] ?? 'MISSING'));

// =============================
//   3. AT getRoleNameById / getRoleIdByName via reflection
// =============================
section("3. AT Model — getRoleNameById / getRoleIdByName");

$atModel = new AuthorityToTravel();

// Use reflection to access private methods
$refGetName = new ReflectionMethod('AuthorityToTravel', 'getRoleNameById');
$refGetName->setAccessible(true);
$refGetId = new ReflectionMethod('AuthorityToTravel', 'getRoleIdByName');
$refGetId->setAccessible(true);

test("getRoleNameById(3) = 'AO V - ADMINISTRATIVE'", $refGetName->invoke($atModel, 3) === 'AO V - ADMINISTRATIVE', "Got: " . $refGetName->invoke($atModel, 3));
test("getRoleNameById(4) = 'CID CHIEF'", $refGetName->invoke($atModel, 4) === 'CID CHIEF', "Got: " . $refGetName->invoke($atModel, 4));
test("getRoleNameById(5) = 'SGOD CHIEF'", $refGetName->invoke($atModel, 5) === 'SGOD CHIEF', "Got: " . $refGetName->invoke($atModel, 5));
test("getRoleNameById(7) = 'SDS'", $refGetName->invoke($atModel, 7) === 'SDS');
test("getRoleNameById(2) = 'ASDS'", $refGetName->invoke($atModel, 2) === 'ASDS');
test("getRoleNameById(999) = 'USER' (fallback)", $refGetName->invoke($atModel, 999) === 'USER');

// Forward lookups (new names)
test("getRoleIdByName('AO V - ADMINISTRATIVE') = 3", $refGetId->invoke($atModel, 'AO V - ADMINISTRATIVE') === 3);
test("getRoleIdByName('CID CHIEF') = 4", $refGetId->invoke($atModel, 'CID CHIEF') === 4);
test("getRoleIdByName('SGOD CHIEF') = 5", $refGetId->invoke($atModel, 'SGOD CHIEF') === 5);
test("getRoleIdByName('SDS') = 7", $refGetId->invoke($atModel, 'SDS') === 7);
test("getRoleIdByName('ASDS') = 2", $refGetId->invoke($atModel, 'ASDS') === 2);

// Legacy lookups still work
test("getRoleIdByName('OSDS_CHIEF') = 3 (legacy)", $refGetId->invoke($atModel, 'OSDS_CHIEF') === 3);
test("getRoleIdByName('CID_CHIEF') = 4 (legacy)", $refGetId->invoke($atModel, 'CID_CHIEF') === 4);
test("getRoleIdByName('SGOD_CHIEF') = 5 (legacy)", $refGetId->invoke($atModel, 'SGOD_CHIEF') === 5);
test("getRoleIdByName('AO V') = 3 (legacy)", $refGetId->invoke($atModel, 'AO V') === 3);

// Roundtrip: name -> id -> name
test("Roundtrip 'AO V - ADMINISTRATIVE'", $refGetName->invoke($atModel, $refGetId->invoke($atModel, 'AO V - ADMINISTRATIVE')) === 'AO V - ADMINISTRATIVE');
test("Roundtrip 'CID CHIEF'", $refGetName->invoke($atModel, $refGetId->invoke($atModel, 'CID CHIEF')) === 'CID CHIEF');
test("Roundtrip 'SGOD CHIEF'", $refGetName->invoke($atModel, $refGetId->invoke($atModel, 'SGOD CHIEF')) === 'SGOD CHIEF');

// =============================
//   4. AT ROUTING — determineRouting() all travel types
// =============================
section("4. AT Routing — determineRouting() All Travel Types");

// Office IDs for testing
$osdsOfficeId = 17;  // ICT -> OSDS Chief
$cidOfficeId = 28;   // IM -> CID Chief
$sgodOfficeId = 21;  // SMME -> SGOD Chief

// --- 4a. PERSONAL travel (all roles) ---
echo "\n  --- Personal Travel ---\n";

// USER from OSDS office
$r = $atModel->determineRouting(ROLE_USER, $osdsOfficeId, 'ICT', null, null, 'personal');
test("USER/OSDS personal → approver = 'AO V - ADMINISTRATIVE'", $r['current_approver_role'] === 'AO V - ADMINISTRATIVE', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/OSDS personal → stage = final", $r['routing_stage'] === 'final');
test("USER/OSDS personal → final = 'AO V - ADMINISTRATIVE'", $r['final_approver_role'] === 'AO V - ADMINISTRATIVE');

// USER from CID office
$r = $atModel->determineRouting(ROLE_USER, $cidOfficeId, 'IM', null, null, 'personal');
test("USER/CID personal → approver = 'CID CHIEF'", $r['current_approver_role'] === 'CID CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/CID personal → stage = final", $r['routing_stage'] === 'final');

// USER from SGOD office
$r = $atModel->determineRouting(ROLE_USER, $sgodOfficeId, 'SMME', null, null, 'personal');
test("USER/SGOD personal → approver = 'SGOD CHIEF'", $r['current_approver_role'] === 'SGOD CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/SGOD personal → stage = final", $r['routing_stage'] === 'final');

// Division chiefs personal
$r = $atModel->determineRouting(ROLE_OSDS_CHIEF, null, null, null, null, 'personal');
test("AO V personal → approver = SDS", $r['current_approver_role'] === 'SDS');
test("AO V personal → stage = final", $r['routing_stage'] === 'final');

$r = $atModel->determineRouting(ROLE_CID_CHIEF, null, null, null, null, 'personal');
test("CID CHIEF personal → approver = SDS", $r['current_approver_role'] === 'SDS');

$r = $atModel->determineRouting(ROLE_SGOD_CHIEF, null, null, null, null, 'personal');
test("SGOD CHIEF personal → approver = SDS", $r['current_approver_role'] === 'SDS');

// ASDS personal
$r = $atModel->determineRouting(ROLE_ASDS, null, null, null, null, 'personal');
test("ASDS personal → approver = SDS", $r['current_approver_role'] === 'SDS');

// SDS personal
$r = $atModel->determineRouting(ROLE_SDS, null, null, null, null, 'personal');
test("SDS personal → completed, forwarded to RO", $r['routing_stage'] === 'completed' && $r['forwarded_to_ro'] === 1);

// --- 4b. LOCAL WITHIN-REGION OFFICIAL ---
echo "\n  --- Local Within-Region Official ---\n";

$r = $atModel->determineRouting(ROLE_USER, $osdsOfficeId, 'ICT', 'local', null, 'official', 'within_region');
test("USER/OSDS local-wr official → approver = 'AO V - ADMINISTRATIVE'", $r['current_approver_role'] === 'AO V - ADMINISTRATIVE', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/OSDS local-wr official → stage = recommending", $r['routing_stage'] === 'recommending');
test("USER/OSDS local-wr official → final = SDS", $r['final_approver_role'] === 'SDS');

$r = $atModel->determineRouting(ROLE_USER, $cidOfficeId, 'IM', 'local', null, 'official', 'within_region');
test("USER/CID local-wr official → approver = 'CID CHIEF'", $r['current_approver_role'] === 'CID CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/CID local-wr official → stage = recommending", $r['routing_stage'] === 'recommending');

$r = $atModel->determineRouting(ROLE_USER, $sgodOfficeId, 'SMME', 'local', null, 'official', 'within_region');
test("USER/SGOD local-wr official → approver = 'SGOD CHIEF'", $r['current_approver_role'] === 'SGOD CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));

// Division Chief within-region: ASDS recommends -> SDS final
$r = $atModel->determineRouting(ROLE_OSDS_CHIEF, null, null, 'local', null, 'official', 'within_region');
test("AO V local-wr official → ASDS recommends", $r['current_approver_role'] === 'ASDS');
test("AO V local-wr official → final = SDS", $r['final_approver_role'] === 'SDS');

$r = $atModel->determineRouting(ROLE_CID_CHIEF, null, null, 'local', null, 'official', 'within_region');
test("CID CHIEF local-wr official → ASDS recommends", $r['current_approver_role'] === 'ASDS');

$r = $atModel->determineRouting(ROLE_SGOD_CHIEF, null, null, 'local', null, 'official', 'within_region');
test("SGOD CHIEF local-wr official → ASDS recommends", $r['current_approver_role'] === 'ASDS');

// ASDS within-region: SDS is both
$r = $atModel->determineRouting(ROLE_ASDS, null, null, 'local', null, 'official', 'within_region');
test("ASDS local-wr official → SDS", $r['current_approver_role'] === 'SDS');

// SDS within-region: self-approved
$r = $atModel->determineRouting(ROLE_SDS, null, null, 'local', null, 'official', 'within_region');
test("SDS local-wr official → completed, no RO", $r['routing_stage'] === 'completed' && $r['forwarded_to_ro'] === 0);

// --- 4c. LOCAL OUTSIDE-REGION OFFICIAL ---
echo "\n  --- Local Outside-Region Official ---\n";

$r = $atModel->determineRouting(ROLE_USER, $osdsOfficeId, 'ICT', 'local', null, 'official', 'outside_region');
test("USER/OSDS local-or official → approver = 'AO V - ADMINISTRATIVE'", $r['current_approver_role'] === 'AO V - ADMINISTRATIVE', "Got: " . ($r['current_approver_role'] ?? 'NULL'));
test("USER/OSDS local-or official → stage = recommending", $r['routing_stage'] === 'recommending');
test("USER/OSDS local-or official → final = SDS", $r['final_approver_role'] === 'SDS');

$r = $atModel->determineRouting(ROLE_USER, $cidOfficeId, 'IM', 'local', null, 'official', 'outside_region');
test("USER/CID local-or official → approver = 'CID CHIEF'", $r['current_approver_role'] === 'CID CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));

$r = $atModel->determineRouting(ROLE_USER, $sgodOfficeId, 'SMME', 'local', null, 'official', 'outside_region');
test("USER/SGOD local-or official → approver = 'SGOD CHIEF'", $r['current_approver_role'] === 'SGOD CHIEF', "Got: " . ($r['current_approver_role'] ?? 'NULL'));

// Division Chief outside-region: ASDS recommends -> SDS final
$r = $atModel->determineRouting(ROLE_CID_CHIEF, null, null, 'local', null, 'official', 'outside_region');
test("CID CHIEF local-or official → ASDS recommends", $r['current_approver_role'] === 'ASDS');

// ASDS outside-region: SDS recommends -> RD
$r = $atModel->determineRouting(ROLE_ASDS, null, null, 'local', null, 'official', 'outside_region');
test("ASDS local-or official → SDS recommends -> RD", $r['current_approver_role'] === 'SDS' && $r['final_approver_role'] === 'RD');

// --- 4d. INTERNATIONAL OFFICIAL ---
echo "\n  --- International Official ---\n";

$r = $atModel->determineRouting(ROLE_USER, $osdsOfficeId, 'ICT', 'international', null, 'official');
test("USER intl official → SDS recommends", $r['current_approver_role'] === 'SDS');
test("USER intl official → final = DEPED_SEC", $r['final_approver_role'] === 'DEPED_SEC');

$r = $atModel->determineRouting(ROLE_CID_CHIEF, null, null, 'international', null, 'official');
test("CID CHIEF intl official → SDS recommends", $r['current_approver_role'] === 'SDS');
test("CID CHIEF intl official → final = DEPED_SEC", $r['final_approver_role'] === 'DEPED_SEC');

$r = $atModel->determineRouting(ROLE_ASDS, null, null, 'international', null, 'official');
test("ASDS intl official → completed, forwarded to RO", $r['routing_stage'] === 'completed' && $r['forwarded_to_ro'] === 1);

// =============================
//   5. AT canUserActOn() — role name matching
// =============================
section("5. AT canUserActOn() — Role Name Matching");

// Simulated AT records with new role names
$atRecord = [
    'status' => 'pending',
    'current_approver_role' => 'AO V - ADMINISTRATIVE',
    'routing_stage' => 'recommending',
    'requester_office' => 'ICT',
    'requester_office_id' => 17,
];

test("AO V can act on AT with 'AO V - ADMINISTRATIVE'", $atModel->canUserActOn($atRecord, ROLE_OSDS_CHIEF, 'AO V - ADMINISTRATIVE'));
test("CID CHIEF cannot act on AT with 'AO V - ADMINISTRATIVE'", !$atModel->canUserActOn($atRecord, ROLE_CID_CHIEF, 'CID CHIEF'));

$atRecord2 = [
    'status' => 'pending',
    'current_approver_role' => 'CID CHIEF',
    'routing_stage' => 'recommending',
    'requester_office' => 'IM',
    'requester_office_id' => 28,
];
test("CID CHIEF can act on AT with 'CID CHIEF'", $atModel->canUserActOn($atRecord2, ROLE_CID_CHIEF, 'CID CHIEF'));
test("AO V cannot act on AT with 'CID CHIEF'", !$atModel->canUserActOn($atRecord2, ROLE_OSDS_CHIEF, 'AO V - ADMINISTRATIVE'));

$atRecord3 = [
    'status' => 'pending',
    'current_approver_role' => 'SGOD CHIEF',
    'routing_stage' => 'recommending',
    'requester_office' => 'SMME',
    'requester_office_id' => 21,
];
test("SGOD CHIEF can act on AT with 'SGOD CHIEF'", $atModel->canUserActOn($atRecord3, ROLE_SGOD_CHIEF, 'SGOD CHIEF'));

$atRecordSDS = [
    'status' => 'recommended',
    'current_approver_role' => 'SDS',
    'routing_stage' => 'final',
    'requester_office' => 'ICT',
    'requester_office_id' => 17,
];
test("SDS can act on recommended AT", $atModel->canUserActOn($atRecordSDS, ROLE_SDS, 'SDS'));

$atRecordASDS = [
    'status' => 'pending',
    'current_approver_role' => 'ASDS',
    'routing_stage' => 'recommending',
    'requester_office' => null,
    'requester_office_id' => null,
];
test("ASDS can act on AT with 'ASDS'", $atModel->canUserActOn($atRecordASDS, ROLE_ASDS, 'ASDS'));

// Completed AT - nobody can act
$atCompleted = [
    'status' => 'approved',
    'current_approver_role' => null,
    'routing_stage' => 'completed',
    'requester_office' => 'ICT',
    'requester_office_id' => 17,
];
test("No action on approved AT", !$atModel->canUserActOn($atCompleted, ROLE_SDS, 'SDS'));

// =============================
//   6. AT getAvailableAction() 
// =============================
section("6. AT getAvailableAction()");

test("AO V recommending stage → 'recommend'", $atModel->getAvailableAction($atRecord, ROLE_OSDS_CHIEF, 'AO V - ADMINISTRATIVE') === 'recommend');
test("CID CHIEF recommending stage → 'recommend'", $atModel->getAvailableAction($atRecord2, ROLE_CID_CHIEF, 'CID CHIEF') === 'recommend');
test("SDS final stage → 'approve'", $atModel->getAvailableAction($atRecordSDS, ROLE_SDS, 'SDS') === 'approve');
test("ASDS recommending stage → 'recommend'", $atModel->getAvailableAction($atRecordASDS, ROLE_ASDS, 'ASDS') === 'recommend');

// =============================
//   7. AT getPendingCountForRole() — uses role name string
// =============================
section("7. AT getPendingCountForRole() — Role Name Matching");

// These should not throw errors — just verify they return a number
$count = $atModel->getPendingCountForRole('AO V - ADMINISTRATIVE', ROLE_OSDS_CHIEF);
test("getPendingCountForRole('AO V - ADMINISTRATIVE') returns int", is_numeric($count), "Got: $count");

$count = $atModel->getPendingCountForRole('CID CHIEF', ROLE_CID_CHIEF);
test("getPendingCountForRole('CID CHIEF') returns int", is_numeric($count), "Got: $count");

$count = $atModel->getPendingCountForRole('SGOD CHIEF', ROLE_SGOD_CHIEF);
test("getPendingCountForRole('SGOD CHIEF') returns int", is_numeric($count), "Got: $count");

$count = $atModel->getPendingCountForRole('SDS', ROLE_SDS);
test("getPendingCountForRole('SDS') returns int", is_numeric($count), "Got: $count");

$count = $atModel->getPendingCountForRole('ASDS', ROLE_ASDS);
test("getPendingCountForRole('ASDS') returns int", is_numeric($count), "Got: $count");

// =============================
//   8. AT — No old role names in DB
// =============================
section("8. AT Table — No Legacy Role Names in DB");

$oldNames = $db->query("SELECT COUNT(*) as cnt FROM authority_to_travel WHERE current_approver_role IN ('OSDS_CHIEF','CID_CHIEF','SGOD_CHIEF','GUARD')")->fetch();
test("No old role names in current_approver_role", (int)$oldNames['cnt'] === 0, "Found: " . $oldNames['cnt']);

$oldNames2 = $db->query("SELECT COUNT(*) as cnt FROM authority_to_travel WHERE final_approver_role IN ('OSDS_CHIEF','CID_CHIEF','SGOD_CHIEF','GUARD')")->fetch();
test("No old role names in final_approver_role", (int)$oldNames2['cnt'] === 0, "Found: " . $oldNames2['cnt']);

// Check no plain 'AO V' without ' - ADMINISTRATIVE' remains  
$plainAoV = $db->query("SELECT COUNT(*) as cnt FROM authority_to_travel WHERE current_approver_role = 'AO V'")->fetch();
test("No plain 'AO V' (without ADMINISTRATIVE) in current_approver_role", (int)$plainAoV['cnt'] === 0, "Found: " . $plainAoV['cnt']);

$plainAoV2 = $db->query("SELECT COUNT(*) as cnt FROM authority_to_travel WHERE final_approver_role = 'AO V'")->fetch();
test("No plain 'AO V' (without ADMINISTRATIVE) in final_approver_role", (int)$plainAoV2['cnt'] === 0, "Found: " . $plainAoV2['cnt']);

// =============================
//   9. LOCATOR SLIP — routing uses numeric constants (no string role names)
// =============================
section("9. Locator Slip Routing — Office-Based");

$lsModel = new LocatorSlip();

// Test getApproverRoleForOffice returns numeric role IDs
test("LS: ICT → ROLE_OSDS_CHIEF (3)", $lsModel->getApproverRoleForOffice('ICT') === ROLE_OSDS_CHIEF);
test("LS: Personnel → ROLE_OSDS_CHIEF (3)", $lsModel->getApproverRoleForOffice('Personnel') === ROLE_OSDS_CHIEF);
test("LS: CID → ROLE_CID_CHIEF (4)", $lsModel->getApproverRoleForOffice('CID') === ROLE_CID_CHIEF);
test("LS: IM → ROLE_CID_CHIEF (4)", $lsModel->getApproverRoleForOffice('IM') === ROLE_CID_CHIEF);
test("LS: SGOD → ROLE_SGOD_CHIEF (5)", $lsModel->getApproverRoleForOffice('SGOD') === ROLE_SGOD_CHIEF);
test("LS: SMME → ROLE_SGOD_CHIEF (5)", $lsModel->getApproverRoleForOffice('SMME') === ROLE_SGOD_CHIEF);
test("LS: HRD → ROLE_SGOD_CHIEF (5)", $lsModel->getApproverRoleForOffice('HRD') === ROLE_SGOD_CHIEF);
test("LS: Accounting → ROLE_OSDS_CHIEF (3)", $lsModel->getApproverRoleForOffice('Accounting') === ROLE_OSDS_CHIEF);
test("LS: Budget → ROLE_OSDS_CHIEF (3)", $lsModel->getApproverRoleForOffice('Budget') === ROLE_OSDS_CHIEF);
test("LS: Dental → ROLE_SGOD_CHIEF (5)", $lsModel->getApproverRoleForOffice('Dental') === ROLE_SGOD_CHIEF);

// =============================
//   10. PASS SLIP — routing uses numeric constants
// =============================
section("10. Pass Slip Routing — Office-Based");

$psModel = new PassSlip();

// Use reflection to access private method
$refPS = new ReflectionMethod('PassSlip', 'getApproverRoleForOffice');
$refPS->setAccessible(true);

// Office Chiefs route to ASDS
test("PS: AO V (OSDS Chief) → ROLE_ASDS", $refPS->invoke($psModel, ROLE_OSDS_CHIEF, 'OSDS', null) === ROLE_ASDS);
test("PS: CID Chief → ROLE_ASDS", $refPS->invoke($psModel, ROLE_CID_CHIEF, 'CID', null) === ROLE_ASDS);
test("PS: SGOD Chief → ROLE_ASDS", $refPS->invoke($psModel, ROLE_SGOD_CHIEF, 'SGOD', null) === ROLE_ASDS);

// Regular users — by office_id (DB-based routing)
test("PS: USER from ICT (id=17) → ROLE_OSDS_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'ICT', 17) === ROLE_OSDS_CHIEF);
test("PS: USER from IM (id=28) → ROLE_CID_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'IM', 28) === ROLE_CID_CHIEF);
test("PS: USER from SMME (id=21) → ROLE_SGOD_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'SMME', 21) === ROLE_SGOD_CHIEF);

// Regular users — by office name (fallback)
test("PS: USER from 'CID' (name) → ROLE_CID_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'CID', null) === ROLE_CID_CHIEF);
test("PS: USER from 'SGOD' (name) → ROLE_SGOD_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'SGOD', null) === ROLE_SGOD_CHIEF);
test("PS: USER from 'OSDS' (name) → ROLE_OSDS_CHIEF", $refPS->invoke($psModel, ROLE_USER, 'OSDS', null) === ROLE_OSDS_CHIEF);

// =============================
//   11. DISPLAY MAPS — RECOMMENDING/APPROVING AUTHORITY
// =============================
section("11. Display Maps — Recommending/Approving Authority Names");

test("RECOMMENDING_AUTHORITY_MAP[3] = 'AO V - Administrative'", RECOMMENDING_AUTHORITY_MAP[ROLE_OSDS_CHIEF] === 'AO V - Administrative');
test("RECOMMENDING_AUTHORITY_MAP[4] = 'CID Chief'", RECOMMENDING_AUTHORITY_MAP[ROLE_CID_CHIEF] === 'CID Chief');
test("RECOMMENDING_AUTHORITY_MAP[5] = 'SGOD Chief'", RECOMMENDING_AUTHORITY_MAP[ROLE_SGOD_CHIEF] === 'SGOD Chief');
test("APPROVING_AUTHORITY_MAP[3] = 'AO V - Administrative'", APPROVING_AUTHORITY_MAP[ROLE_OSDS_CHIEF] === 'AO V - Administrative');
test("APPROVING_AUTHORITY_MAP[4] = 'CID Chief'", APPROVING_AUTHORITY_MAP[ROLE_CID_CHIEF] === 'CID Chief');
test("APPROVING_AUTHORITY_MAP[5] = 'SGOD Chief'", APPROVING_AUTHORITY_MAP[ROLE_SGOD_CHIEF] === 'SGOD Chief');

// =============================
//   12. UNIT_HEAD_OFFICES lookups
// =============================
section("12. UNIT_HEAD_OFFICES Config");

test("UNIT_HEAD_OFFICES[3] has OSDS units", is_array(UNIT_HEAD_OFFICES[ROLE_OSDS_CHIEF]) && in_array('ICT', UNIT_HEAD_OFFICES[ROLE_OSDS_CHIEF]));
test("UNIT_HEAD_OFFICES[4] has CID units", is_array(UNIT_HEAD_OFFICES[ROLE_CID_CHIEF]) && in_array('CID', UNIT_HEAD_OFFICES[ROLE_CID_CHIEF]));
test("UNIT_HEAD_OFFICES[5] has SGOD units", is_array(UNIT_HEAD_OFFICES[ROLE_SGOD_CHIEF]) && in_array('SGOD', UNIT_HEAD_OFFICES[ROLE_SGOD_CHIEF]));

// =============================
//   13. CROSS-CHECK: DB role_name matches code getRoleNameById
// =============================
section("13. Cross-Check: DB role_name == Code getRoleNameById");

$codeRoleName3 = $refGetName->invoke($atModel, 3);
test("DB role_name[3] matches getRoleNameById(3)", $roleMap[3] === $codeRoleName3, "DB='{$roleMap[3]}' vs Code='$codeRoleName3'");

$codeRoleName4 = $refGetName->invoke($atModel, 4);
test("DB role_name[4] matches getRoleNameById(4)", $roleMap[4] === $codeRoleName4, "DB='{$roleMap[4]}' vs Code='$codeRoleName4'");

$codeRoleName5 = $refGetName->invoke($atModel, 5);
test("DB role_name[5] matches getRoleNameById(5)", $roleMap[5] === $codeRoleName5, "DB='{$roleMap[5]}' vs Code='$codeRoleName5'");

$codeRoleName7 = $refGetName->invoke($atModel, 7);
test("DB role_name[7] matches getRoleNameById(7)", $roleMap[7] === $codeRoleName7, "DB='{$roleMap[7]}' vs Code='$codeRoleName7'");

$codeRoleName2 = $refGetName->invoke($atModel, 2);
test("DB role_name[2] matches getRoleNameById(2)", $roleMap[2] === $codeRoleName2, "DB='{$roleMap[2]}' vs Code='$codeRoleName2'");

// =============================
//   SUMMARY
// =============================
echo "\n" . str_repeat('=', 70) . "\n";
echo "  TEST SUMMARY\n";
echo str_repeat('=', 70) . "\n";
echo "  Passed: $passed\n";
echo "  Failed: $failed\n";
echo "  Total:  " . ($passed + $failed) . "\n";

if ($failed > 0) {
    echo "\n  FAILURES:\n";
    foreach ($errors as $e) echo "  $e\n";
    echo "\n";
    exit(1);
} else {
    echo "\n  ALL TESTS PASSED!\n\n";
    exit(0);
}
