<?php
/**
 * LocatorSlip Model
 * Handles CRUD operations for Locator Slip requests
 * With Unit-Based Routing Logic
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class LocatorSlip {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Determine the approver role ID (Office Chief) based on employee office/unit.
     * Supports: (1) office code (SHN_DENTAL, IM, etc.), (2) office display name (e.g. "School Health and Nutrition (Dental)"),
     * (3) ROLE_OFFICE_MAP. Ensures dentists/SHN route to SGOD Chief, not OSDS.
     */
    public function getApproverRoleForOffice($office) {
        $office = trim($office);
        if ($office === '') {
            return ROLE_OSDS_CHIEF;
        }
        $officeLower = strtolower($office);
        // 1) Exact code in ROLE_OFFICE_MAP (SHN_DENTAL, SHN_MEDICAL, IM, SMME, etc.)
        if (defined('ROLE_OFFICE_MAP') && isset(ROLE_OFFICE_MAP[$office])) {
            return ROLE_OFFICE_MAP[$office];
        }
        // 2) Match by exact display name (e.g. "School Health and Nutrition (Dental)" → SHN_DENTAL → SGOD Chief)
        if (defined('SDO_OFFICES')) {
            foreach (SDO_OFFICES as $code => $name) {
                $name = trim($name);
                if (strcasecmp($name, $office) === 0 && isset(ROLE_OFFICE_MAP[$code])) {
                    return ROLE_OFFICE_MAP[$code];
                }
            }
        }
        // 3) Aliases for SHN/Dental/Medical (often stored in profile) → SGOD Chief
        $sgodAliases = ['dental', 'medical', 'shn', 'school health and nutrition', 'school health'];
        if (in_array($officeLower, $sgodAliases, true) || preg_match('/\b(dental|medical|shn)\b/i', $office)) {
            return ROLE_SGOD_CHIEF;
        }
        if ($office === 'CID') {
            return ROLE_CID_CHIEF;
        }
        if ($office === 'SGOD') {
            return ROLE_SGOD_CHIEF;
        }
        if (in_array($office, OSDS_UNITS)) {
            return ROLE_OSDS_CHIEF;
        }
        return ROLE_OSDS_CHIEF;
    }

    /**
     * Get the active OIC user ID for a unit head role, if any
     * Returns the OIC user ID if there's an active delegation, otherwise returns null
     */
    public function getActiveOICForRole($roleId) {
        $sql = "SELECT oic_user_id FROM oic_delegations 
                WHERE unit_head_role_id = ? 
                  AND is_active = 1 
                  AND CURDATE() BETWEEN start_date AND end_date
                ORDER BY created_at DESC
                LIMIT 1";
        
        $result = $this->db->query($sql, [$roleId])->fetch();
        return $result ? $result['oic_user_id'] : null;
    }

    /**
     * Get the effective approver user ID for a role
     * If there's an active OIC, returns OIC user ID, otherwise returns the unit head user ID
     */
    public function getEffectiveApproverUserId($roleId, $unitHeadUserId = null) {
        $oicUserId = $this->getActiveOICForRole($roleId);
        
        if ($oicUserId) {
            return $oicUserId;
        }
        
        return $unitHeadUserId;
    }

    /**
     * Create a new Locator Slip request with routing
     * - If requestor is an Office Chief (OSDS, CID, SGOD): route to ASDS for approval.
     * - If requestor is ASDS or SDS: route to SDS for approval.
     * - CID employees: route to CID Chief for approval.
     * - SGOD employees: route to SGOD Chief for approval.
     * - OSDS employees / default: route to OSDS Chief (AO V) for approval.
     */
    public function create($data, $requesterRoleId = null, $requesterOffice = null, $requesterOfficeId = null) {
        $approverRoleId = null;
        $assignedApproverUserId = null;

        require_once __DIR__ . '/AdminUser.php';
        $userModel = new AdminUser();

        // Check if requestor position routes directly to SDS (Attorney III, Accountant III, AO V)
        $employeePosition = $data['employee_position'] ?? null;
        $isDirectSDS = false;
        if ($employeePosition && defined('DIRECT_SDS_POSITIONS')) {
            $posNormalized = trim($employeePosition);
            foreach (DIRECT_SDS_POSITIONS as $directPos) {
                if (strcasecmp($posNormalized, $directPos) === 0) {
                    $isDirectSDS = true;
                    break;
                }
            }
        }

        // Office Chief (OSDS, CID, SGOD) as requestor: route to ASDS
        if ($requesterRoleId && in_array($requesterRoleId, UNIT_HEAD_ROLES)) {
            $approverRoleId = ROLE_ASDS;
            $asdsUsers = $userModel->getByRole(ROLE_ASDS, true);
            if (!empty($asdsUsers)) {
                $primaryAsdsUserId = null;
                foreach ($asdsUsers as $asdsUser) {
                    if ((int)$asdsUser['id'] !== (int)$data['user_id']) {
                        $primaryAsdsUserId = $asdsUser['id'];
                        break;
                    }
                }
                if ($primaryAsdsUserId === null) {
                    $primaryAsdsUserId = $asdsUsers[0]['id'];
                }
                $assignedApproverUserId = $this->getEffectiveApproverUserId(ROLE_ASDS, $primaryAsdsUserId);
            }
        }
        // ASDS / SDS as requestor: route to SDS
        elseif ($requesterRoleId && ($requesterRoleId == ROLE_ASDS || $requesterRoleId == ROLE_SDS)) {
            $approverRoleId = ROLE_SDS;
            $sdsUsers = $userModel->getByRole(ROLE_SDS, true);
            if (!empty($sdsUsers)) {
                $primarySdsUserId = null;
                foreach ($sdsUsers as $sdsUser) {
                    if ((int)$sdsUser['id'] !== (int)$data['user_id']) {
                        $primarySdsUserId = $sdsUser['id'];
                        break;
                    }
                }
                if ($primarySdsUserId === null) {
                    $primarySdsUserId = $sdsUsers[0]['id'];
                }
                $assignedApproverUserId = $this->getEffectiveApproverUserId(ROLE_SDS, $primarySdsUserId);
            }
        }
        // Specific positions route directly to SDS
        elseif ($isDirectSDS) {
            $approverRoleId = ROLE_SDS;
            $sdsUsers = $userModel->getByRole(ROLE_SDS, true);
            if (!empty($sdsUsers)) {
                $assignedApproverUserId = $this->getEffectiveApproverUserId(ROLE_SDS, $sdsUsers[0]['id']);
            }
        }
        // All other employees: route based on office (CID→CID Chief, SGOD→SGOD Chief, OSDS→AO V)
        else {
            // Use office_id for DB-based routing if available
            if ($requesterOfficeId !== null) {
                $approverRoleId = getApproverRoleByOfficeId($requesterOfficeId);
            }
            // Fall back to office name matching
            elseif ($requesterOffice) {
                $approverRoleId = $this->getApproverRoleForOffice($requesterOffice);
            }
            // Default fallback
            else {
                $approverRoleId = ROLE_OSDS_CHIEF;
            }

            $unitHeads = $userModel->getByRole($approverRoleId, true);
            if (!empty($unitHeads)) {
                $unitHeadUserId = $unitHeads[0]['id'];
                require_once __DIR__ . '/OICDelegation.php';
                $oicModel = new OICDelegation();
                $assignedApproverUserId = $oicModel->getEffectiveApproverUserId($approverRoleId, $unitHeadUserId);
            }
        }
        
        $sql = "INSERT INTO locator_slips (
            ls_control_no, employee_name, employee_position, employee_office,
            purpose_of_travel, travel_type, date_time, destination,
            requesting_employee_name, request_date, user_id, status,
            assigned_approver_role_id, assigned_approver_user_id,
            requester_office, requester_role_id, date_filed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['ls_control_no'],
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['employee_office'] ?? null,
            $data['purpose_of_travel'],
            $data['travel_type'],
            $data['date_time'],
            $data['destination'],
            $data['requesting_employee_name'] ?? $data['employee_name'],
            $data['request_date'] ?? date('Y-m-d'),
            $data['user_id'],
            $approverRoleId,
            $assignedApproverUserId,
            $requesterOffice,
            $requesterRoleId,
            date('Y-m-d')
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Get Locator Slip by ID
     */
    public function getById($id) {
        $sql = "SELECT ls.*, 
                       CASE 
                           WHEN ls.status = 'rejected' THEN 'disapproved'
                           WHEN (ls.status = '' OR ls.status IS NULL) AND ls.rejection_reason IS NOT NULL AND ls.rejection_reason != '' THEN 'disapproved'
                           WHEN ls.status = '' OR ls.status IS NULL THEN 'pending'
                           ELSE ls.status 
                       END as status,
                       u.full_name as filed_by_name, u.email as filed_by_email,
                       u.employee_office as filed_by_office, u.role_id as filed_by_role,
                       a.full_name as approved_by_name,
                       approver.full_name as assigned_approver_name,
                       approver.employee_position as assigned_approver_position,
                       approver_role.role_name as assigned_approver_role_name
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                LEFT JOIN admin_users a ON ls.approved_by = a.id
                LEFT JOIN admin_users approver ON ls.assigned_approver_user_id = approver.id
                LEFT JOIN admin_roles approver_role ON ls.assigned_approver_role_id = approver_role.id
                WHERE ls.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get Locator Slip by control number
     */
    public function getByControlNo($controlNo) {
        $sql = "SELECT ls.*, 
                       CASE 
                           WHEN ls.status = 'rejected' THEN 'disapproved'
                           WHEN (ls.status = '' OR ls.status IS NULL) AND ls.rejection_reason IS NOT NULL AND ls.rejection_reason != '' THEN 'disapproved'
                           WHEN ls.status = '' OR ls.status IS NULL THEN 'pending'
                           ELSE ls.status 
                       END as status,
                       u.full_name as filed_by_name,
                       a.full_name as approved_by_name
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                LEFT JOIN admin_users a ON ls.approved_by = a.id
                WHERE ls.ls_control_no = ?";
        return $this->db->query($sql, [$controlNo])->fetch();
    }

    /**
     * Get all Locator Slip with filters
     * Includes visibility filtering based on user role
     */
    public function getAll($filters = [], $limit = 15, $offset = 0, $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT ls.*, 
                       CASE 
                           WHEN ls.status = 'rejected' THEN 'disapproved'
                           WHEN (ls.status = '' OR ls.status IS NULL) AND ls.rejection_reason IS NOT NULL AND ls.rejection_reason != '' THEN 'disapproved'
                           WHEN ls.status = '' OR ls.status IS NULL THEN 'pending'
                           ELSE ls.status 
                       END as status,
                       u.full_name as filed_by_name, u.email as filed_by_email,
                       u.employee_office as filed_by_office,
                       a.full_name as approved_by_name,
                       approver.full_name as assigned_approver_name,
                       approver.employee_position as assigned_approver_position,
                       approver_role.role_name as assigned_approver_role_name
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                LEFT JOIN admin_users a ON ls.approved_by = a.id
                LEFT JOIN admin_users approver ON ls.assigned_approver_user_id = approver.id
                LEFT JOIN admin_roles approver_role ON ls.assigned_approver_role_id = approver_role.id
                WHERE 1=1";
        $params = [];

        // Visibility filtering - skip role-based filtering if user_id filter is explicitly provided (e.g., in My Requests page)
        if (!empty($filters['user_id'])) {
            // Explicitly filtering by user_id - show only that user's requests regardless of role
            $sql .= " AND ls.user_id = ?";
            $params[] = $filters['user_id'];
        } elseif ($viewerRoleId == ROLE_USER && $viewerUserId) {
            // Regular employees see only their own requests
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId == ROLE_OSDS_CHIEF) {
            // OSDS Chief sees only requests assigned to them for approval (regular employee requests)
            // Their own submissions are routed to ASDS and appear only in My Requests page
            $sql .= " AND (ls.assigned_approver_user_id = ? OR ls.assigned_approver_role_id = ?)";
            $params[] = $viewerUserId;
            $params[] = ROLE_OSDS_CHIEF;
        } elseif ($viewerRoleId && in_array($viewerRoleId, [ROLE_CID_CHIEF, ROLE_SGOD_CHIEF])) {
            // CID/SGOD Chiefs see locator slip assigned to them for approval (from their unit employees)
            // Their own submissions route to ASDS and appear in My Requests page
            $sql .= " AND (ls.assigned_approver_user_id = ? OR ls.assigned_approver_role_id = ?)";
            $params[] = $viewerUserId;
            $params[] = $viewerRoleId;
        } elseif ($viewerRoleId == ROLE_ASDS) {
            // ASDS sees only Locator Slip assigned to ASDS (i.e. requests filed by Office Chiefs)
            $sql .= " AND ls.assigned_approver_role_id = ?";
            $params[] = ROLE_ASDS;
        } elseif ($viewerRoleId == ROLE_SDS) {
            // SDS sees all Locator Slip (view-only, no approval actions)
            // No additional filter — show everything
        } elseif ($viewerRoleId == ROLE_SUPERADMIN) {
            // Superadmin sees all requests
            // No additional filter
        } elseif ($viewerUserId) {
            // Default: users see only their own requests
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        }

        // Additional filters (user_id already handled above in visibility filtering)

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'disapproved') {
                $sql .= " AND ls.status IN ('disapproved', 'rejected')";
            } else {
                $sql .= " AND ls.status = ?";
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND ls.requester_office = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['travel_type'])) {
            $sql .= " AND ls.travel_type = ?";
            $params[] = $filters['travel_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(ls.date_filed) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(ls.date_filed) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['approval_date_from'])) {
            $sql .= " AND DATE(ls.approval_date) >= ?";
            $params[] = $filters['approval_date_from'];
        }

        if (!empty($filters['approval_date_to'])) {
            $sql .= " AND DATE(ls.approval_date) <= ?";
            $params[] = $filters['approval_date_to'];
        }

        if (!empty($filters['approver_id'])) {
            $sql .= " AND ls.assigned_approver_user_id = ?";
            $params[] = $filters['approver_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ls.ls_control_no LIKE ? OR ls.employee_name LIKE ? OR ls.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY ls.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get count of Locator Slip with filters
     * Includes visibility filtering based on user role
     */
    public function getCount($filters = [], $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM locator_slips ls WHERE 1=1";
        $params = [];

        // Visibility filtering (same as getAll)
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId == ROLE_OSDS_CHIEF) {
            // OSDS Chief sees requests assigned to them (all non-chief requests) plus their own submissions
            $sql .= " AND (ls.assigned_approver_user_id = ? OR ls.assigned_approver_role_id = ? OR ls.user_id = ?)";
            $params[] = $viewerUserId;
            $params[] = ROLE_OSDS_CHIEF;
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, [ROLE_CID_CHIEF, ROLE_SGOD_CHIEF])) {
            // CID/SGOD Chiefs see only their own submissions (no one is assigned to them anymore)
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId == ROLE_ASDS) {
            $sql .= " AND ls.assigned_approver_role_id = ?";
            $params[] = ROLE_ASDS;
        } elseif ($viewerRoleId == ROLE_SDS) {
            // SDS sees all Locator Slip (view-only) — no additional filter
        } elseif ($viewerUserId && !in_array($viewerRoleId, [ROLE_ASDS, ROLE_SDS, ROLE_SUPERADMIN])) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'disapproved') {
                $sql .= " AND ls.status IN ('disapproved', 'rejected')";
            } else {
                $sql .= " AND ls.status = ?";
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND ls.requester_office = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['travel_type'])) {
            $sql .= " AND ls.travel_type = ?";
            $params[] = $filters['travel_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(ls.date_filed) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(ls.date_filed) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['approval_date_from'])) {
            $sql .= " AND DATE(ls.approval_date) >= ?";
            $params[] = $filters['approval_date_from'];
        }

        if (!empty($filters['approval_date_to'])) {
            $sql .= " AND DATE(ls.approval_date) <= ?";
            $params[] = $filters['approval_date_to'];
        }

        if (!empty($filters['approver_id'])) {
            $sql .= " AND ls.assigned_approver_user_id = ?";
            $params[] = $filters['approver_id'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (ls.ls_control_no LIKE ? OR ls.employee_name LIKE ? OR ls.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Approve a Locator Slip
     * Supports OIC approval logging
     * Sets approving_time to current timestamp on final approval
     */
    public function approve($id, $approverId, $approverName, $approverPosition, $isOIC = false) {
        $sql = "UPDATE locator_slips SET 
                status = 'approved',
                approved_by = ?,
                approver_name = ?,
                approver_position = ?,
                approval_date = CURDATE(),
                approving_time = NOW()
                WHERE id = ?";
        
        return $this->db->query($sql, [$approverId, $approverName, $approverPosition, $id]);
    }

    /**
     * Validate Locator Slip request before submission
     * Returns: ['valid' => bool, 'errors' => array]
     */
    public function validateSubmission($data) {
        $errors = [];

        // Ensure Manila timezone is used
        date_default_timezone_set('Asia/Manila');
        
        // Date/Time validation: cannot be in the past (including time check)
        $requestDateTime = strtotime($data['date_time']);
        $now = time();
        
        // Check if the full datetime (date + time) is in the past
        // Allow a 1-minute grace period to account for form submission delay
        if ($requestDateTime < ($now - 60)) {
            $errors[] = 'Request date and time cannot be in the past. Please select a current or future time.';
        }

        // Required fields
        if (empty($data['employee_name'])) {
            $errors[] = 'Employee name is required';
        }
        if (empty($data['purpose_of_travel'])) {
            $errors[] = 'Purpose of travel is required';
        }
        if (empty($data['destination'])) {
            $errors[] = 'Destination is required';
        }
        if (empty($data['travel_type'])) {
            $errors[] = 'Travel type is required';
        }

        return [
            'valid' => empty($errors),
            'errors' => $errors
        ];
    }

    /**
     * Check if user can view this Locator Slip
     */
    public function canUserView($ls, $viewerRoleId, $viewerUserId) {
        // Requestor can always view
        if ($ls['user_id'] == $viewerUserId) {
            return true;
        }

        // Superadmin can view all; ASDS can view only slips assigned to ASDS
        if ($viewerRoleId == ROLE_SUPERADMIN) {
            return true;
        }
        if ($viewerRoleId == ROLE_ASDS) {
            return (int) ($ls['assigned_approver_role_id'] ?? 0) === ROLE_ASDS;
        }
        // SDS can view all locator slip (view-only)
        if ($viewerRoleId == ROLE_SDS) {
            return true;
        }

        // Unit heads can view if assigned to them
        if (in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            return $ls['assigned_approver_user_id'] == $viewerUserId || 
                   $ls['assigned_approver_role_id'] == $viewerRoleId;
        }

        return false;
    }

    /**
     * Check if user can edit this Locator Slip
     * Only requestor can edit, and only when status is pending
     */
    public function canUserEdit($ls, $viewerUserId) {
        return $ls['user_id'] == $viewerUserId && $ls['status'] === 'pending';
    }

    /**
     * Update a Locator Slip (only by requestor when pending)
     */
    public function update($id, $data, $userId) {
        // Verify user can edit
        $ls = $this->getById($id);
        if (!$this->canUserEdit($ls, $userId)) {
            return false;
        }

        $sql = "UPDATE locator_slips SET 
                employee_name = ?,
                employee_position = ?,
                employee_office = ?,
                purpose_of_travel = ?,
                travel_type = ?,
                date_time = ?,
                destination = ?,
                updated_at = NOW()
                WHERE id = ? AND user_id = ? AND status = 'pending'";
        
        return $this->db->query($sql, [
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['employee_office'] ?? null,
            $data['purpose_of_travel'],
            $data['travel_type'],
            $data['date_time'],
            $data['destination'],
            $id,
            $userId
        ]);
    }

    /**
     * Disapprove a Locator Slip
     */
    public function disapprove($id, $approverId, $reason = null) {
        $sql = "UPDATE locator_slips SET 
                status = 'disapproved',
                approved_by = ?,
                rejection_reason = ?
                WHERE id = ?";
        
        return $this->db->query($sql, [$approverId, $reason, $id]);
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics($userId = null) {
        $params = [];
        $userCondition = '';
        
        if ($userId) {
            $userCondition = ' AND user_id = ?';
            $params[] = $userId;
        }

        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'disapproved' THEN 1 ELSE 0 END) as disapproved,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week
                FROM locator_slips WHERE 1=1" . $userCondition;

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get pending LS count assigned to a specific approver (by user ID)
     */
    public function getPendingCountForApprover($approverUserId) {
        $sql = "SELECT COUNT(*) as pending FROM locator_slips 
                WHERE status = 'pending' AND assigned_approver_user_id = ?";
        $result = $this->db->query($sql, [$approverUserId])->fetch();
        return (int)($result['pending'] ?? 0);
    }

    /**
     * Get recent Locator Slip for dashboard
     */
    public function getRecent($limit = 5, $userId = null) {
        $sql = "SELECT ls.*, u.full_name as filed_by_name
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($userId) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY ls.created_at DESC LIMIT ?";
        $params[] = $limit;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get pending requests for approvers
     * Filtered by assigned approver (supports OIC)
     */
    public function getPending($limit = 10, $approverUserId = null, $approverRoleId = null) {
        $sql = "SELECT ls.*, u.full_name as filed_by_name, u.email as filed_by_email
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                WHERE ls.status = 'pending'";
        
        $params = [];
        
        if ($approverUserId && $approverRoleId) {
            // Get effective approver (may be OIC)
            $effectiveApproverId = $this->getEffectiveApproverUserId($approverRoleId, $approverUserId);
            $sql .= " AND ls.assigned_approver_user_id = ?";
            $params[] = $effectiveApproverId;
        }
        
        $sql .= " ORDER BY ls.created_at ASC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Delete a Locator Slip (admin only)
     */
    public function delete($id) {
        $sql = "DELETE FROM locator_slips WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }
}
