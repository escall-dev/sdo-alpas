<?php
/**
 * AuthorityToTravel Model
 * Handles CRUD operations for Authority to Travel requests
 * With Unit-Based and Role-Based Routing Logic
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class AuthorityToTravel {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Determine routing for a new AT request based on requester's role and office
     * Returns: [current_approver_role, routing_stage, recommending_authority_name]
     */
    public function determineRouting($requesterRoleId, $requesterOffice) {
        // Unit heads skip recommending stage and go directly to ASDS
        if (in_array($requesterRoleId, UNIT_HEAD_ROLES)) {
            return [
                'current_approver_role' => 'ASDS',
                'routing_stage' => 'final',
                'recommending_authority_name' => null,
                'recommending_date' => null
            ];
        }

        // Regular employees route to their unit head first
        $recommenderRole = $this->getRecommenderRoleForOffice($requesterOffice);
        $recommenderRoleName = $this->getRoleNameById($recommenderRole);

        return [
            'current_approver_role' => $recommenderRoleName,
            'routing_stage' => 'recommending',
            'recommending_authority_name' => null, // Set when recommended
            'recommending_date' => null
        ];
    }

    /**
     * Get the recommender role ID based on employee office
     */
    private function getRecommenderRoleForOffice($office) {
        // Normalize office name for comparison
        $office = trim($office);
        
        // Check if office is in OSDS units
        if (in_array($office, OSDS_UNITS)) {
            return ROLE_OSDS_CHIEF;
        }
        
        // Check specific mappings
        return ROLE_OFFICE_MAP[$office] ?? ROLE_OSDS_CHIEF;
    }

    /**
     * Get role name by role ID
     */
    private function getRoleNameById($roleId) {
        $roleMap = [
            ROLE_SUPERADMIN => 'SUPERADMIN',
            ROLE_ASDS => 'ASDS',
            ROLE_OSDS_CHIEF => 'OSDS_CHIEF',
            ROLE_CID_CHIEF => 'CID_CHIEF',
            ROLE_SGOD_CHIEF => 'SGOD_CHIEF',
            ROLE_USER => 'USER'
        ];
        return $roleMap[$roleId] ?? 'USER';
    }

    /**
     * Create a new Authority to Travel request with routing
     * Supports OIC delegation
     */
    public function create($data, $requesterRoleId, $requesterOffice) {
        // Determine routing based on role and office
        $routing = $this->determineRouting($requesterRoleId, $requesterOffice);
        
        // Get effective approver (may be OIC)
        $assignedApproverUserId = null;
        if ($routing['routing_stage'] === 'recommending') {
            // Get unit head for this office
            $recommenderRole = $this->getRecommenderRoleForOffice($requesterOffice);
            require_once __DIR__ . '/AdminUser.php';
            $userModel = new AdminUser();
            $unitHeads = $userModel->getByRole($recommenderRole, true);
            
            if (!empty($unitHeads)) {
                $unitHeadUserId = $unitHeads[0]['id'];
                // Check for active OIC
                require_once __DIR__ . '/OICDelegation.php';
                $oicModel = new OICDelegation();
                $assignedApproverUserId = $oicModel->getEffectiveApproverUserId($recommenderRole, $unitHeadUserId);
            }
        }
        
        $sql = "INSERT INTO authority_to_travel (
            at_tracking_no, employee_name, employee_position, permanent_station,
            purpose_of_travel, host_of_activity, date_from, date_to,
            destination, fund_source, inclusive_dates,
            requesting_employee_name, request_date,
            travel_category, travel_scope, user_id, status,
            current_approver_role, routing_stage, requester_office, requester_role_id,
            assigned_approver_user_id, date_filed
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending', ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['at_tracking_no'],
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['permanent_station'] ?? null,
            $data['purpose_of_travel'],
            $data['host_of_activity'] ?? null,
            $data['date_from'],
            $data['date_to'],
            $data['destination'],
            $data['fund_source'] ?? null,
            $data['inclusive_dates'] ?? null,
            $data['requesting_employee_name'] ?? $data['employee_name'],
            $data['request_date'] ?? date('Y-m-d'),
            $data['travel_category'] ?? 'official',
            $data['travel_scope'] ?? null,
            $data['user_id'],
            $routing['current_approver_role'],
            $routing['routing_stage'],
            $requesterOffice,
            $requesterRoleId,
            $assignedApproverUserId,
            date('Y-m-d')
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Validate AT request before submission
     * Returns: ['valid' => bool, 'errors' => array, 'redirect' => string|null]
     */
    public function validateSubmission($data, $requesterRoleId) {
        $errors = [];
        $redirect = null;

        // Date validation
        $dateFrom = strtotime($data['date_from']);
        $dateTo = strtotime($data['date_to']);

        if ($dateTo < $dateFrom) {
            $errors[] = 'Date To cannot be earlier than Date From';
        }

        // If same day travel, redirect to Locator Slip
        if ($dateFrom === $dateTo) {
            $redirect = 'locator_slips';
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

        return [
            'valid' => empty($errors) && !$redirect,
            'errors' => $errors,
            'redirect' => $redirect
        ];
    }

    /**
     * Get Authority to Travel by ID
     */
    public function getById($id) {
        $sql = "SELECT at.*, 
                       u.full_name as filed_by_name, u.email as filed_by_email,
                       u.employee_office as filed_by_office, u.role_id as filed_by_role,
                       a.full_name as approved_by_name,
                       r.full_name as recommended_by_name
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                LEFT JOIN admin_users a ON at.approved_by = a.id
                LEFT JOIN admin_users r ON at.recommended_by = r.id
                WHERE at.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get Authority to Travel by tracking number
     */
    public function getByTrackingNo($trackingNo) {
        $sql = "SELECT at.*, 
                       u.full_name as filed_by_name,
                       a.full_name as approved_by_name
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                LEFT JOIN admin_users a ON at.approved_by = a.id
                WHERE at.at_tracking_no = ?";
        return $this->db->query($sql, [$trackingNo])->fetch();
    }

    /**
     * Get requests pending for a specific approver role
     * For unit heads, only shows requests from their supervised offices
     */
    public function getPendingForRole($roleName, $roleId = null, $limit = 50, $offset = 0) {
        $params = [];
        
        $sql = "SELECT at.*, 
                       u.full_name as filed_by_name, u.email as filed_by_email,
                       u.employee_office as filed_by_office
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                WHERE at.status IN ('pending', 'recommended')
                  AND at.current_approver_role = ?";
        $params[] = $roleName;
        
        // For unit heads, filter by their supervised offices
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES) && isset(UNIT_HEAD_OFFICES[$roleId])) {
            $offices = UNIT_HEAD_OFFICES[$roleId];
            $placeholders = implode(',', array_fill(0, count($offices), '?'));
            $sql .= " AND at.requester_office IN ($placeholders)";
            $params = array_merge($params, $offices);
        }
        
        $sql .= " ORDER BY at.created_at ASC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get count of pending requests for a role
     * For unit heads, only counts requests from their supervised offices
     */
    public function getPendingCountForRole($roleName, $roleId = null) {
        $params = [];
        
        $sql = "SELECT COUNT(*) as total FROM authority_to_travel 
                WHERE status IN ('pending', 'recommended') 
                AND current_approver_role = ?";
        $params[] = $roleName;
        
        // For unit heads, filter by their supervised offices
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES) && isset(UNIT_HEAD_OFFICES[$roleId])) {
            $offices = UNIT_HEAD_OFFICES[$roleId];
            $placeholders = implode(',', array_fill(0, count($offices), '?'));
            $sql .= " AND requester_office IN ($placeholders)";
            $params = array_merge($params, $offices);
        }
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Get all Authority to Travel requests with filters
     * Includes visibility filtering based on user role
     */
    public function getAll($filters = [], $limit = 15, $offset = 0, $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT at.*, 
                       u.full_name as filed_by_name, u.email as filed_by_email,
                       u.employee_office as filed_by_office,
                       a.full_name as approved_by_name,
                       approver.full_name as assigned_approver_name,
                       approver.employee_position as assigned_approver_position
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                LEFT JOIN admin_users a ON at.approved_by = a.id
                LEFT JOIN admin_users approver ON at.assigned_approver_user_id = approver.id
                WHERE 1=1";
        $params = [];

        // Visibility filtering
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            $sql .= " AND at.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            // Unit heads see only requests from their supervised offices
            if (isset(UNIT_HEAD_OFFICES[$viewerRoleId])) {
                $offices = UNIT_HEAD_OFFICES[$viewerRoleId];
                $placeholders = implode(',', array_fill(0, count($offices), '?'));
                $sql .= " AND at.requester_office IN ($placeholders)";
                $params = array_merge($params, $offices);
            }
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND at.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND at.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['travel_category'])) {
            $sql .= " AND at.travel_category = ?";
            $params[] = $filters['travel_category'];
        }

        if (!empty($filters['travel_scope'])) {
            $sql .= " AND at.travel_scope = ?";
            $params[] = $filters['travel_scope'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND at.requester_office = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['current_approver_role'])) {
            $sql .= " AND at.current_approver_role = ?";
            $params[] = $filters['current_approver_role'];
        }

        if (!empty($filters['approver_id'])) {
            $sql .= " AND at.assigned_approver_user_id = ?";
            $params[] = $filters['approver_id'];
        }

        // Filter by supervised offices for unit heads
        if (!empty($filters['supervised_offices']) && is_array($filters['supervised_offices'])) {
            $placeholders = implode(',', array_fill(0, count($filters['supervised_offices']), '?'));
            $sql .= " AND at.requester_office IN ($placeholders)";
            $params = array_merge($params, $filters['supervised_offices']);
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(at.date_filed) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(at.date_filed) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['approval_date_from'])) {
            $sql .= " AND DATE(at.approval_date) >= ?";
            $params[] = $filters['approval_date_from'];
        }

        if (!empty($filters['approval_date_to'])) {
            $sql .= " AND DATE(at.approval_date) <= ?";
            $params[] = $filters['approval_date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (at.at_tracking_no LIKE ? OR at.employee_name LIKE ? OR at.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY at.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get count of Authority to Travel requests with filters
     * Includes visibility filtering based on user role
     */
    public function getCount($filters = [], $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM authority_to_travel at WHERE 1=1";
        $params = [];

        // Visibility filtering (same as getAll)
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            $sql .= " AND at.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            if (isset(UNIT_HEAD_OFFICES[$viewerRoleId])) {
                $offices = UNIT_HEAD_OFFICES[$viewerRoleId];
                $placeholders = implode(',', array_fill(0, count($offices), '?'));
                $sql .= " AND at.requester_office IN ($placeholders)";
                $params = array_merge($params, $offices);
            }
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND at.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND at.status = ?";
            $params[] = $filters['status'];
        }

        if (!empty($filters['travel_category'])) {
            $sql .= " AND at.travel_category = ?";
            $params[] = $filters['travel_category'];
        }

        if (!empty($filters['travel_scope'])) {
            $sql .= " AND at.travel_scope = ?";
            $params[] = $filters['travel_scope'];
        }

        if (!empty($filters['unit'])) {
            $sql .= " AND at.requester_office = ?";
            $params[] = $filters['unit'];
        }

        if (!empty($filters['current_approver_role'])) {
            $sql .= " AND at.current_approver_role = ?";
            $params[] = $filters['current_approver_role'];
        }

        if (!empty($filters['approver_id'])) {
            $sql .= " AND at.assigned_approver_user_id = ?";
            $params[] = $filters['approver_id'];
        }

        // Filter by supervised offices for unit heads
        if (!empty($filters['supervised_offices']) && is_array($filters['supervised_offices'])) {
            $placeholders = implode(',', array_fill(0, count($filters['supervised_offices']), '?'));
            $sql .= " AND at.requester_office IN ($placeholders)";
            $params = array_merge($params, $filters['supervised_offices']);
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND DATE(at.date_filed) >= ?";
            $params[] = $filters['date_from'];
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND DATE(at.date_filed) <= ?";
            $params[] = $filters['date_to'];
        }

        if (!empty($filters['approval_date_from'])) {
            $sql .= " AND DATE(at.approval_date) >= ?";
            $params[] = $filters['approval_date_from'];
        }

        if (!empty($filters['approval_date_to'])) {
            $sql .= " AND DATE(at.approval_date) <= ?";
            $params[] = $filters['approval_date_to'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (at.at_tracking_no LIKE ? OR at.employee_name LIKE ? OR at.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Recommend an AT request (by Unit Head)
     * Moves request from recommending stage to final stage (ASDS)
     */
    public function recommend($id, $recommenderId, $recommenderName, $recommenderRoleId) {
        $recommenderTitle = getRecommendingAuthorityName($recommenderRoleId);
        
        $sql = "UPDATE authority_to_travel SET 
                status = 'recommended',
                recommended_by = ?,
                recommending_authority_name = ?,
                recommending_date = CURDATE(),
                current_approver_role = 'ASDS',
                routing_stage = 'final'
                WHERE id = ? AND routing_stage = 'recommending'";
        
        return $this->db->query($sql, [$recommenderId, $recommenderTitle ?: $recommenderName, $id]);
    }

    /**
     * Check if user can edit this AT request
     * Only requestor can edit, and only when status is pending
     */
    public function canUserEdit($at, $viewerUserId) {
        return $at['user_id'] == $viewerUserId && in_array($at['status'], ['pending', 'recommended']);
    }

    /**
     * Update an AT request (only by requestor when pending/recommended)
     */
    public function update($id, $data, $userId) {
        // Verify user can edit
        $at = $this->getById($id);
        if (!$this->canUserEdit($at, $userId)) {
            return false;
        }

        $sql = "UPDATE authority_to_travel SET 
                employee_name = ?,
                employee_position = ?,
                permanent_station = ?,
                purpose_of_travel = ?,
                host_of_activity = ?,
                date_from = ?,
                date_to = ?,
                destination = ?,
                fund_source = ?,
                travel_category = ?,
                travel_scope = ?,
                updated_at = NOW()
                WHERE id = ? AND user_id = ? AND status IN ('pending', 'recommended')";
        
        return $this->db->query($sql, [
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['permanent_station'] ?? null,
            $data['purpose_of_travel'],
            $data['host_of_activity'] ?? null,
            $data['date_from'],
            $data['date_to'],
            $data['destination'],
            $data['fund_source'] ?? null,
            $data['travel_category'] ?? 'official',
            $data['travel_scope'] ?? null,
            $id,
            $userId
        ]);
    }

    /**
     * Approve an AT request (by Unit Head or ASDS)
     * Unit heads approve requests from regular users
     * ASDS approves requests from unit heads
     * Supports OIC approval
     */
    public function approve($id, $approverId, $approverName, $approverRoleId, $isOIC = false) {
        $at = $this->getById($id);
        
        // Determine authority names based on who is approving
        if (in_array($approverRoleId, UNIT_HEAD_ROLES)) {
            // Unit head is approving - use their actual name
            // Format: "Name (Title)" e.g., "John Doe, CID Chief"
            $approverTitle = getRecommendingAuthorityName($approverRoleId);
            $approverFullDisplay = $approverName . ', ' . $approverTitle;
            
            $sql = "UPDATE authority_to_travel SET 
                    status = 'approved',
                    approved_by = ?,
                    recommending_authority_name = ?,
                    recommending_date = CURDATE(),
                    recommended_by = ?,
                    approving_authority_name = ?,
                    approval_date = CURDATE(),
                    current_approver_role = NULL,
                    routing_stage = 'completed'
                    WHERE id = ?";
            
            return $this->db->query($sql, [$approverId, $approverFullDisplay, $approverId, $approverFullDisplay, $id]);
        } else {
            // ASDS is approving - use their actual name
            $approverTitle = getApprovingAuthorityName($approverRoleId);
            $approverFullDisplay = $approverName . ', ' . $approverTitle;
            
            $sql = "UPDATE authority_to_travel SET 
                    status = 'approved',
                    approved_by = ?,
                    approving_authority_name = ?,
                    approval_date = CURDATE(),
                    current_approver_role = NULL,
                    routing_stage = 'completed'
                    WHERE id = ?";
            
            return $this->db->query($sql, [$approverId, $approverFullDisplay, $id]);
        }
    }

    /**
     * Get the effective approver user ID for a role (supports OIC)
     */
    public function getEffectiveApproverUserId($roleId, $unitHeadUserId = null) {
        require_once __DIR__ . '/OICDelegation.php';
        $oicModel = new OICDelegation();
        return $oicModel->getEffectiveApproverUserId($roleId, $unitHeadUserId);
    }

    /**
     * Executive override approval (by Superadmin/SDS)
     * Can approve at any stage
     */
    public function executiveApprove($id, $approverId, $approverName) {
        $at = $this->getById($id);
        
        // Use actual approver name with title
        $approverFullDisplay = $approverName . ', SDS';
        
        // If at recommending stage, set recommending authority too
        $sql = "UPDATE authority_to_travel SET 
                status = 'approved',
                approved_by = ?,
                approving_authority_name = ?,
                approval_date = CURDATE(),
                current_approver_role = NULL,
                routing_stage = 'completed'";
        
        // If was at recommending stage and no recommender yet, skip that
        if ($at['routing_stage'] === 'recommending' && empty($at['recommending_authority_name'])) {
            $sql .= ", recommending_authority_name = ?, recommending_date = CURDATE()";
        }
        
        $sql .= " WHERE id = ?";
        
        $params = [$approverId, $approverFullDisplay];
        if ($at['routing_stage'] === 'recommending' && empty($at['recommending_authority_name'])) {
            $params[] = $approverFullDisplay;
        }
        $params[] = $id;
        
        return $this->db->query($sql, $params);
    }

    /**
     * Reject an Authority to Travel request
     */
    public function reject($id, $rejecterId, $reason = null) {
        $sql = "UPDATE authority_to_travel SET 
                status = 'rejected',
                approved_by = ?,
                rejection_reason = ?,
                current_approver_role = NULL,
                routing_stage = 'completed'
                WHERE id = ?";
        
        return $this->db->query($sql, [$rejecterId, $reason, $id]);
    }

    /**
     * Check if user can act on this AT request
     * For unit heads, also verifies the request is from their supervised office
     */
    public function canUserActOn($at, $userRoleId, $userRoleName) {
        // Superadmin can act on anything
        if ($userRoleId == ROLE_SUPERADMIN) {
            return true;
        }

        // If already completed (approved/rejected), no action
        if (in_array($at['status'], ['approved', 'rejected'])) {
            return false;
        }

        // Check if current approver role matches user's role
        if ($at['current_approver_role'] !== $userRoleName) {
            return false;
        }

        // For unit heads, verify the request is from their supervised office
        if (in_array($userRoleId, UNIT_HEAD_ROLES)) {
            if (isset(UNIT_HEAD_OFFICES[$userRoleId])) {
                $supervisedOffices = UNIT_HEAD_OFFICES[$userRoleId];
                if (!in_array($at['requester_office'], $supervisedOffices)) {
                    return false;
                }
            }
        }

        return true;
    }

    /**
     * Get the action type available for user on this AT
     * Returns: 'approve', 'executive_approve', or null
     */
    public function getAvailableAction($at, $userRoleId, $userRoleName) {
        if (!$this->canUserActOn($at, $userRoleId, $userRoleName)) {
            return null;
        }

        // Superadmin has executive override
        if ($userRoleId == ROLE_SUPERADMIN) {
            return 'executive_approve';
        }

        // ASDS can approve at final stage
        if ($userRoleId == ROLE_ASDS && $at['routing_stage'] === 'final') {
            return 'approve';
        }

        // Unit heads can approve requests from regular users at recommending stage
        if (in_array($userRoleId, UNIT_HEAD_ROLES) && $at['routing_stage'] === 'recommending') {
            return 'approve';
        }

        return null;
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics($userId = null, $roleId = null, $roleName = null) {
        $params = [];
        $baseCondition = '';
        
        if ($userId && $roleId == ROLE_USER) {
            // Regular users see only their own
            $baseCondition = ' AND user_id = ?';
            $params[] = $userId;
        } elseif ($roleId && in_array($roleId, UNIT_HEAD_ROLES) && isset(UNIT_HEAD_OFFICES[$roleId])) {
            // Unit heads see only their supervised offices
            $offices = UNIT_HEAD_OFFICES[$roleId];
            $placeholders = implode(',', array_fill(0, count($offices), '?'));
            $baseCondition = " AND requester_office IN ($placeholders)";
            $params = array_merge($params, $offices);
        }

        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'recommended' THEN 1 ELSE 0 END) as recommended,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week
                FROM authority_to_travel WHERE 1=1" . $baseCondition;

        $stats = $this->db->query($sql, $params)->fetch();

        // If user is approver, get their pending queue count
        if ($roleName && in_array($roleId, [ROLE_ASDS, ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF])) {
            $stats['my_queue'] = $this->getPendingCountForRole($roleName, $roleId);
        }

        return $stats;
    }

    /**
     * Get a specific user's OWN statistics (requests they personally filed)
     * This is always filtered by user_id regardless of role
     */
    public function getMyStatistics($userId) {
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'recommended' THEN 1 ELSE 0 END) as recommended,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN travel_category = 'official' AND travel_scope = 'local' THEN 1 ELSE 0 END) as local_official,
                SUM(CASE WHEN travel_category = 'official' AND travel_scope = 'national' THEN 1 ELSE 0 END) as national_official,
                SUM(CASE WHEN travel_category = 'personal' THEN 1 ELSE 0 END) as personal
                FROM authority_to_travel 
                WHERE user_id = ?";

        return $this->db->query($sql, [$userId])->fetch();
    }

    /**
     * Get statistics for Unit Heads - shows requests FROM THEIR SUPERVISED OFFICES
     * This shows what the unit head is responsible for approving
     * Also includes requests that are routed to them (current_approver_role)
     */
    public function getUnitStatistics($roleId) {
        if (!isset(UNIT_HEAD_OFFICES[$roleId])) {
            return [
                'total' => 0, 'pending' => 0, 'recommended' => 0, 
                'approved' => 0, 'rejected' => 0, 'today' => 0, 'this_week' => 0,
                'local_official' => 0, 'national_official' => 0, 'personal' => 0
            ];
        }
        
        $offices = UNIT_HEAD_OFFICES[$roleId];
        $roleName = $this->getRoleNameById($roleId);
        
        // Build query that matches either requester_office OR current_approver_role
        // This handles both new records (with requester_office) and old records (routed by role)
        $placeholders = implode(',', array_fill(0, count($offices), '?'));
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN at.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN at.status = 'recommended' THEN 1 ELSE 0 END) as recommended,
                SUM(CASE WHEN at.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN at.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN DATE(at.created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(at.created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN at.travel_category = 'official' AND at.travel_scope = 'local' THEN 1 ELSE 0 END) as local_official,
                SUM(CASE WHEN at.travel_category = 'official' AND at.travel_scope = 'national' THEN 1 ELSE 0 END) as national_official,
                SUM(CASE WHEN at.travel_category = 'personal' THEN 1 ELSE 0 END) as personal
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                WHERE (at.requester_office IN ($placeholders) 
                       OR u.employee_office IN ($placeholders)
                       OR at.current_approver_role = ?
                       OR (at.approved_by IS NOT NULL AND at.requester_role_id = ?))";
        
        // Params: offices twice (for requester_office and employee_office), plus roleName, plus roleId
        $params = array_merge($offices, $offices, [$roleName, ROLE_USER]);

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get recent Authority to Travel requests for dashboard
     */
    public function getRecent($limit = 5, $userId = null) {
        $sql = "SELECT at.*, u.full_name as filed_by_name
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                WHERE 1=1";
        $params = [];

        if ($userId) {
            $sql .= " AND at.user_id = ?";
            $params[] = $userId;
        }

        $sql .= " ORDER BY at.created_at DESC LIMIT ?";
        $params[] = $limit;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get pending requests for approvers, filtered by role/office
     */
    public function getPending($limit = 10, $roleId = null, $roleName = null) {
        $params = [];
        
        $sql = "SELECT at.*, u.full_name as filed_by_name, u.email as filed_by_email
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                WHERE at.status IN ('pending', 'recommended')";
        
        // For unit heads, filter by their role assignment AND supervised offices
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES) && isset(UNIT_HEAD_OFFICES[$roleId])) {
            $sql .= " AND at.current_approver_role = ?";
            $params[] = $roleName;
            
            $offices = UNIT_HEAD_OFFICES[$roleId];
            $placeholders = implode(',', array_fill(0, count($offices), '?'));
            $sql .= " AND at.requester_office IN ($placeholders)";
            $params = array_merge($params, $offices);
        } 
        // For ASDS, filter by final stage
        elseif ($roleId == ROLE_ASDS) {
            $sql .= " AND at.current_approver_role = 'ASDS' AND at.routing_stage = 'final'";
        }
        // Superadmin sees all pending
        
        $sql .= " ORDER BY at.created_at ASC LIMIT ?";
        $params[] = $limit;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Delete an Authority to Travel request (admin only)
     */
    public function delete($id) {
        $sql = "DELETE FROM authority_to_travel WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Get type label for display
     */
    public static function getTypeLabel($category, $scope = null) {
        if ($category === 'personal') {
            return 'Personal';
        }
        if ($scope === 'national') {
            return 'Official - National';
        }
        return 'Official - Local';
    }

    /**
     * Get status label with routing info
     */
    public static function getStatusLabel($status, $routingStage = null, $currentApproverRole = null) {
        $labels = [
            'pending' => 'Pending Recommendation',
            'recommended' => 'Pending Final Approval',
            'approved' => 'Approved',
            'rejected' => 'Rejected'
        ];
        
        $label = $labels[$status] ?? ucfirst($status);
        
        if ($currentApproverRole && in_array($status, ['pending', 'recommended'])) {
            $label .= " ({$currentApproverRole})";
        }
        
        return $label;
    }
}
