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
     * Determine routing for a new AT request per DepEd Order 043 s. 2022
     * Position-aware, travel-type-aware decision tree
     * travel_scope: 'local' or 'international'
     * travel_type: 'within_region' or 'outside_region' (only for local scope)
     * Returns: [current_approver_role, routing_stage, recommending_authority_name,
     *           recommending_date, final_approver_role, forwarded_to_ro]
     */
    public function determineRouting($requesterRoleId, $requesterOfficeId = null, $requesterOffice = null, $travelScope = null, $employeePosition = null, $travelCategory = 'official', $travelType = null) {
        $base = [
            'recommending_authority_name' => null,
            'recommending_date' => null,
        ];

        $isSDS = ($requesterRoleId == ROLE_SDS);
        $isASDS = ($requesterRoleId == ROLE_ASDS);
        $isDivChief = in_array($requesterRoleId, UNIT_HEAD_ROLES);
        $isPersonalOrInternational = ($travelCategory === 'personal' || $travelScope === 'international');
        $isOutsideRegion = ($travelType === 'outside_region');

        // --- SDS filing own AT ---
        if ($isSDS) {
            if ($travelCategory === 'official' && $travelScope === 'local' && !$isOutsideRegion) {
                // SDS within-region local official: self-approved, no RD needed
                return array_merge($base, [
                    'current_approver_role' => null,
                    'routing_stage' => 'completed',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            // SDS personal / outside-region / international: forwarded to RO for RD
            return array_merge($base, [
                'current_approver_role' => null,
                'routing_stage' => 'completed',
                'final_approver_role' => 'RD',
                'forwarded_to_ro' => 1,
            ]);
        }

        // === INTERNATIONAL OFFICIAL: RD/SDS recommends -> DEPED SEC final (both external) ===
        if ($travelScope === 'international' && $travelCategory === 'official') {
            if ($isASDS) {
                // ASDS: RD recommends, DEPED SEC final — both external, auto-forward to RO
                return array_merge($base, [
                    'current_approver_role' => null,
                    'routing_stage' => 'completed',
                    'final_approver_role' => 'DEPED_SEC',
                    'forwarded_to_ro' => 1,
                ]);
            }
            if ($isDivChief) {
                // Div Chief: SDS recommends -> then forwarded to RO (DEPED SEC final)
                return array_merge($base, [
                    'current_approver_role' => 'SDS',
                    'routing_stage' => 'recommending',
                    'final_approver_role' => 'DEPED_SEC',
                    'forwarded_to_ro' => 0,
                ]);
            }
            // Below Division Chief: SDS recommends -> then forwarded to RO (DEPED SEC final)
            return array_merge($base, [
                'current_approver_role' => 'SDS',
                'routing_stage' => 'recommending',
                'final_approver_role' => 'DEPED_SEC',
                'forwarded_to_ro' => 0,
            ]);
        }

        // === PERSONAL (local & international): single approving authority, no recommending step ===
        if ($travelCategory === 'personal') {
            if ($isASDS) {
                return array_merge($base, [
                    'current_approver_role' => 'SDS',
                    'routing_stage' => 'final',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            if ($isDivChief) {
                return array_merge($base, [
                    'current_approver_role' => 'SDS',
                    'routing_stage' => 'final',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            // Below Division Chief — Division Chief is the sole approving authority
            $approverRole = $this->getRecommenderRoleForOffice($requesterOfficeId, $requesterOffice, $travelScope);
            $approverRoleName = $this->getRoleNameById($approverRole);
            return array_merge($base, [
                'current_approver_role' => $approverRoleName,
                'routing_stage' => 'final',
                'final_approver_role' => $approverRoleName,
                'forwarded_to_ro' => 0,
            ]);
        }

        // === LOCAL — Within Region — Official ===
        if ($travelScope === 'local' && !$isOutsideRegion) {
            if ($isASDS) {
                // ASDS local official: SDS is both recommender & approver, single step
                return array_merge($base, [
                    'current_approver_role' => 'SDS',
                    'routing_stage' => 'final',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            if ($isDivChief) {
                // Division Chief local official: ASDS recommends -> SDS final
                return array_merge($base, [
                    'current_approver_role' => 'ASDS',
                    'routing_stage' => 'recommending',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            // Below Division Chief: Division Chief recommends -> SDS final
            $recommenderRole = $this->getRecommenderRoleForOffice($requesterOfficeId, $requesterOffice, $travelScope);
            $recommenderRoleName = $this->getRoleNameById($recommenderRole);
            return array_merge($base, [
                'current_approver_role' => $recommenderRoleName,
                'routing_stage' => 'recommending',
                'final_approver_role' => 'SDS',
                'forwarded_to_ro' => 0,
            ]);
        }

        // === LOCAL — Outside Region — Official ===
        if ($travelScope === 'local' && $isOutsideRegion) {
            if ($isASDS) {
                // ASDS outside region official: SDS recommends -> then forwarded to RO (RD)
                return array_merge($base, [
                    'current_approver_role' => 'SDS',
                    'routing_stage' => 'recommending',
                    'final_approver_role' => 'RD',
                    'forwarded_to_ro' => 0,
                ]);
            }
            if ($isDivChief) {
                // Division Chief outside region official: ASDS recommends -> SDS final
                return array_merge($base, [
                    'current_approver_role' => 'ASDS',
                    'routing_stage' => 'recommending',
                    'final_approver_role' => 'SDS',
                    'forwarded_to_ro' => 0,
                ]);
            }
            // Below Division Chief: Division Chief recommends -> SDS final
            $recommenderRole = $this->getRecommenderRoleForOffice($requesterOfficeId, $requesterOffice, $travelScope);
            $recommenderRoleName = $this->getRoleNameById($recommenderRole);
            return array_merge($base, [
                'current_approver_role' => $recommenderRoleName,
                'routing_stage' => 'recommending',
                'final_approver_role' => 'SDS',
                'forwarded_to_ro' => 0,
            ]);
        }

        // Fallback: cannot resolve routing
        return null;
    }

    /**
     * Get the recommender role ID based on employee office
     * Uses database-driven unit_routing_config table with fallback to static mapping
     * @param string $office Employee office/unit name
     * @param string|null $travelScope Optional travel scope (local, international)
     * @return int Role ID of recommending authority
     */
    private function getRecommenderRoleForOffice($officeId = null, $office = null, $travelScope = null) {
        // Prefer office_id lookups for accuracy
        if ($officeId) {
            $approverRole = $this->getApproverRoleFromRoutingConfigByOfficeId($officeId, $travelScope);
            if ($approverRole !== null) {
                return $approverRole;
            }

            $roleFromOffice = getApproverRoleByOfficeId($officeId);
            if ($roleFromOffice !== null) {
                return $roleFromOffice;
            }
        }

        // Normalize office name for comparison when id is not available
        $office = $office !== null ? trim($office) : '';

        // First, try to get from database routing config by name
        $approverRole = $this->getApproverRoleFromRoutingConfig($office, $travelScope);
        if ($approverRole !== null) {
            return $approverRole;
        }
        
        // Fallback: Check if office is in OSDS units
        if ($office && in_array($office, OSDS_UNITS)) {
            return ROLE_OSDS_CHIEF;
        }
        
        // Fallback: Check specific mappings
        return ROLE_OFFICE_MAP[$office] ?? ROLE_OSDS_CHIEF;
    }

    /**
     * Query unit_routing_config table for approver role using office_id
     */
    private function getApproverRoleFromRoutingConfigByOfficeId($officeId, $travelScope = null) {
        try {
            $sql = "SELECT approver_role_id FROM unit_routing_config 
                    WHERE office_id = ? AND is_active = 1";
            $params = [$officeId];

            if ($travelScope && $travelScope !== 'all') {
                $sql .= " AND (travel_scope = ? OR travel_scope = 'all')";
                $params[] = $travelScope;
            }

            $sql .= " LIMIT 1";
            $result = $this->db->query($sql, $params)->fetch();

            if ($result && isset($result['approver_role_id'])) {
                return (int) $result['approver_role_id'];
            }
        } catch (Exception $e) {
            // Table may not exist yet, return null to use fallback
        }

        return null;
    }
    
    /**
     * Query unit_routing_config table for approver role
     * @param string $unitName The unit/office name
     * @param string|null $travelScope Optional travel scope filter
     * @return int|null Role ID or null if not found
     */
    private function getApproverRoleFromRoutingConfig($unitName, $travelScope = null) {
        try {
            $sql = "SELECT approver_role_id FROM unit_routing_config 
                    WHERE unit_name = ? AND is_active = 1";
            $params = [$unitName];
            
            if ($travelScope && $travelScope !== 'all') {
                $sql .= " AND (travel_scope = ? OR travel_scope = 'all')";
                $params[] = $travelScope;
            }
            
            $sql .= " LIMIT 1";
            $result = $this->db->query($sql, $params)->fetch();
            
            if ($result && isset($result['approver_role_id'])) {
                return (int) $result['approver_role_id'];
            }
        } catch (Exception $e) {
            // Table may not exist yet, return null to use fallback
        }
        
        return null;
    }

    /**
     * Get supervised office IDs for a given approver role
     */
    public function getSupervisedOfficeIdsForRole($roleId) {
        try {
            $sql = "SELECT office_id FROM unit_routing_config 
                    WHERE approver_role_id = ? AND is_active = 1 AND office_id IS NOT NULL";
            $results = $this->db->query($sql, [$roleId])->fetchAll();
            $ids = array_filter(array_column($results, 'office_id'));
            if (!empty($ids)) {
                return array_map('intval', $ids);
            }
        } catch (Exception $e) {
            // Fall through to empty
        }

        return [];
    }

    /**
     * Apply office filter using both IDs and legacy names
     */
    private function applyOfficeFilter(&$sql, &$params, $officeIds = [], $officeNames = [], $alias = 'at.') {
        $clauses = [];

        if (!empty($officeIds)) {
            $placeholders = implode(',', array_fill(0, count($officeIds), '?'));
            $clauses[] = "{$alias}requester_office_id IN ($placeholders)";
            foreach ($officeIds as $id) {
                $params[] = $id;
            }
        }

        if (!empty($officeNames)) {
            $placeholders = implode(',', array_fill(0, count($officeNames), '?'));
            $clauses[] = "{$alias}requester_office IN ($placeholders)";
            $params = array_merge($params, $officeNames);
        }

        if (!empty($clauses)) {
            $sql .= ' AND (' . implode(' OR ', $clauses) . ')';
        }
    }

    /**
     * Get supervised offices for a role from database
     * Used for filtering requests visible to unit heads
     * @param int $roleId The unit head role ID
     * @return array Array of office names supervised by this role
     */
    public function getSupervisedOfficesForRole($roleId) {
        try {
            $sql = "SELECT unit_name FROM unit_routing_config 
                    WHERE approver_role_id = ? AND is_active = 1 
                    ORDER BY sort_order ASC";
            
            $results = $this->db->query($sql, [$roleId])->fetchAll();
            
            if (!empty($results)) {
                return array_column($results, 'unit_name');
            }
        } catch (Exception $e) {
            // Fall back to static mapping
        }
        
        // Fallback to static UNIT_HEAD_OFFICES
        return UNIT_HEAD_OFFICES[$roleId] ?? [];
    }

    /**
     * Get role name by role ID
     */
    private function getRoleNameById($roleId) {
        $roleMap = [
            ROLE_SUPERADMIN => 'SUPERADMIN',
            ROLE_ASDS => 'ASDS',
            ROLE_OSDS_CHIEF => 'AO V - ADMINISTRATIVE',
            ROLE_CID_CHIEF => 'CID CHIEF',
            ROLE_SGOD_CHIEF => 'SGOD CHIEF',
            ROLE_USER => 'USER',
            ROLE_SDS => 'SDS'
        ];
        return $roleMap[$roleId] ?? 'USER';
    }

    /**
     * Get role ID by role name (reverse lookup)
     */
    private function getRoleIdByName($roleName) {
        $nameMap = [
            'SUPERADMIN' => ROLE_SUPERADMIN,
            'ASDS' => ROLE_ASDS,
            'AO V - ADMINISTRATIVE' => ROLE_OSDS_CHIEF,
            'CID CHIEF' => ROLE_CID_CHIEF,
            'SGOD CHIEF' => ROLE_SGOD_CHIEF,
            'USER' => ROLE_USER,
            'SDS' => ROLE_SDS,
            // Legacy mappings for existing records
            'OSDS_CHIEF' => ROLE_OSDS_CHIEF,
            'CID_CHIEF' => ROLE_CID_CHIEF,
            'SGOD_CHIEF' => ROLE_SGOD_CHIEF,
            'AO V' => ROLE_OSDS_CHIEF
        ];
        return $nameMap[$roleName] ?? null;
    }

    /**
     * Create a new Authority to Travel request with routing
     * Supports OIC delegation and DepEd Order 043 routing
     */
    public function create($data, $requesterRoleId, $requesterOfficeId = null, $requesterOffice = null) {
        // Get travel scope, type, and category for routing determination
        $travelScope = $data['travel_scope'] ?? null;
        $travelType = $data['travel_type'] ?? null;
        $travelCategory = $data['travel_category'] ?? 'official';
        
        // Normalize office data
        if ($requesterOfficeId && !$requesterOffice) {
            $office = getOfficeById($requesterOfficeId);
            $requesterOffice = $office['office_code'] ?? $office['office_name'] ?? null;
        }
        $requesterOffice = $requesterOffice ?? ($data['requester_office'] ?? null);
        
        // Determine routing based on role, office, travel scope, travel type, position, and category
        $employeePosition = $data['employee_position'] ?? null;
        $routing = $this->determineRouting($requesterRoleId, $requesterOfficeId, $requesterOffice, $travelScope, $employeePosition, $travelCategory, $travelType);
        
        // Disapprove submission if routing cannot be resolved
        if ($routing === null) {
            throw new \Exception('Unable to determine routing for this travel request. Please verify your travel scope and category.');
        }

        // Determine status based on routing
        $status = 'pending';
        $forwardedToRo = $routing['forwarded_to_ro'] ?? 0;
        $forwardedToRoDate = null;

        // Auto-approve when routing is already completed (SDS self-filing, ASDS intl official, etc.)
        if ($routing['routing_stage'] === 'completed') {
            $status = 'approved';
            $forwardedToRoDate = $forwardedToRo ? date('Y-m-d') : null;
        }
        
        // Get effective approver (may be OIC) for recommending stage
        $assignedApproverUserId = null;
        if ($routing['routing_stage'] === 'recommending' && $routing['current_approver_role']) {
            $approverRoleName = $routing['current_approver_role'];
            // Map role name back to role ID for OIC lookup
            $approverRoleId = $this->getRoleIdByName($approverRoleName);
            if ($approverRoleId) {
                require_once __DIR__ . '/AdminUser.php';
                $userModel = new AdminUser();
                $roleUsers = $userModel->getByRole($approverRoleId, true);
                
                if (!empty($roleUsers)) {
                    $primaryUserId = $roleUsers[0]['id'];
                    require_once __DIR__ . '/OICDelegation.php';
                    $oicModel = new OICDelegation();
                    $assignedApproverUserId = $oicModel->getEffectiveApproverUserId($approverRoleId, $primaryUserId);
                }
            }
        } elseif ($routing['routing_stage'] === 'final' && $routing['current_approver_role']) {
            // For final stage, resolve the assigned approver too
            $approverRoleName = $routing['current_approver_role'];
            $approverRoleId = $this->getRoleIdByName($approverRoleName);
            if ($approverRoleId) {
                require_once __DIR__ . '/AdminUser.php';
                $userModel = new AdminUser();
                $roleUsers = $userModel->getByRole($approverRoleId, true);
                
                if (!empty($roleUsers)) {
                    $primaryUserId = $roleUsers[0]['id'];
                    require_once __DIR__ . '/OICDelegation.php';
                    $oicModel = new OICDelegation();
                    $assignedApproverUserId = $oicModel->getEffectiveApproverUserId($approverRoleId, $primaryUserId);
                }
            }
        }
        
        $sql = "INSERT INTO authority_to_travel (
            at_tracking_no, employee_name, employee_position, permanent_station,
            purpose_of_travel, host_of_activity, date_from, date_to,
            destination, fund_source, inclusive_dates,
            requesting_employee_name, request_date,
            travel_category, travel_scope, travel_type, user_id, status,
            current_approver_role, routing_stage, requester_office, requester_office_id, requester_role_id,
            assigned_approver_user_id, date_filed,
            final_approver_role, forwarded_to_ro, forwarded_to_ro_date
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
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
            $travelCategory,
            $travelScope,
            $travelType,
            $data['user_id'],
            $status,
            $routing['current_approver_role'],
            $routing['routing_stage'],
            $requesterOffice,
            $requesterOfficeId,
            $requesterRoleId,
            $assignedApproverUserId,
            date('Y-m-d'),
            $routing['final_approver_role'] ?? null,
            $forwardedToRo,
            $forwardedToRoDate
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
        $today = strtotime(date('Y-m-d'));

        // Validate: request date cannot be in the past
        if ($dateFrom < $today) {
            $errors[] = 'Date From cannot be in the past. Please select today or a future date.';
        }
        if ($dateTo < $today) {
            $errors[] = 'Date To cannot be in the past. Please select today or a future date.';
        }

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
                       CASE 
                           WHEN at.status = 'rejected' THEN 'disapproved'
                           WHEN (at.status = '' OR at.status IS NULL) AND at.rejection_reason IS NOT NULL AND at.rejection_reason != '' THEN 'disapproved'
                           WHEN at.status = '' OR at.status IS NULL THEN 'pending'
                           ELSE at.status 
                       END as status,
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
                       CASE 
                           WHEN at.status = 'rejected' THEN 'disapproved'
                           WHEN (at.status = '' OR at.status IS NULL) AND at.rejection_reason IS NOT NULL AND at.rejection_reason != '' THEN 'disapproved'
                           WHEN at.status = '' OR at.status IS NULL THEN 'pending'
                           ELSE at.status 
                       END as status,
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
     * Uses database-driven unit_routing_config for office filtering
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
        
        // For unit heads, filter by their supervised offices (from DB or static fallback)
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES)) {
            $officeIds = $this->getSupervisedOfficeIdsForRole($roleId);
            $officeNames = $this->getSupervisedOfficesForRole($roleId);
            $this->applyOfficeFilter($sql, $params, $officeIds, $officeNames, 'at.');
        }
        
        $sql .= " ORDER BY at.created_at DESC, at.id DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get count of pending requests for a role
     * For unit heads, only counts requests from their supervised offices
     * Uses database-driven unit_routing_config for office filtering
     */
    public function getPendingCountForRole($roleName, $roleId = null) {
        $params = [];
        
        $sql = "SELECT COUNT(*) as total FROM authority_to_travel 
                WHERE status IN ('pending', 'recommended') 
                AND current_approver_role = ?";
        $params[] = $roleName;
        
        // For unit heads, filter by their supervised offices (from DB or static fallback)
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES)) {
            $officeIds = $this->getSupervisedOfficeIdsForRole($roleId);
            $officeNames = $this->getSupervisedOfficesForRole($roleId);
            $this->applyOfficeFilter($sql, $params, $officeIds, $officeNames, '');
        }
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Get all Authority to Travel requests with filters
     * Includes visibility filtering based on user role
     * Uses database-driven unit_routing_config for office filtering
     */
    public function getAll($filters = [], $limit = 15, $offset = 0, $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT at.*, 
                       CASE 
                           WHEN at.status = 'rejected' THEN 'disapproved'
                           WHEN (at.status = '' OR at.status IS NULL) AND at.rejection_reason IS NOT NULL AND at.rejection_reason != '' THEN 'disapproved'
                           WHEN at.status = '' OR at.status IS NULL THEN 'pending'
                           ELSE at.status 
                       END as status,
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
            // Unit heads see only requests from their supervised offices (from DB or static fallback)
            $officeIds = $this->getSupervisedOfficeIdsForRole($viewerRoleId);
            $officeNames = $this->getSupervisedOfficesForRole($viewerRoleId);
            $this->applyOfficeFilter($sql, $params, $officeIds, $officeNames, 'at.');
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND at.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'disapproved') {
                $sql .= " AND at.status IN ('disapproved', 'rejected')";
            } else {
                $sql .= " AND at.status = ?";
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['travel_category'])) {
            $sql .= " AND at.travel_category = ?";
            $params[] = $filters['travel_category'];
        }

        if (!empty($filters['travel_scope'])) {
            $sql .= " AND at.travel_scope = ?";
            $params[] = $filters['travel_scope'];
        }

        if (!empty($filters['travel_type'])) {
            $sql .= " AND at.travel_type = ?";
            $params[] = $filters['travel_type'];
        }

        if (!empty($filters['unit'])) {
            if (is_numeric($filters['unit'])) {
                $sql .= " AND at.requester_office_id = ?";
            } else {
                $sql .= " AND at.requester_office = ?";
            }
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
            $filterOffices = $filters['supervised_offices'];
            $ids = array_values(array_filter($filterOffices, 'is_numeric'));
            $names = array_values(array_diff($filterOffices, $ids));
            $this->applyOfficeFilter($sql, $params, $ids, $names, 'at.');
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
     * Uses database-driven unit_routing_config for office filtering
     */
    public function getCount($filters = [], $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM authority_to_travel at WHERE 1=1";
        $params = [];

        // Visibility filtering (same as getAll) - from DB or static fallback
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            $sql .= " AND at.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            $officeIds = $this->getSupervisedOfficeIdsForRole($viewerRoleId);
            $officeNames = $this->getSupervisedOfficesForRole($viewerRoleId);
            $this->applyOfficeFilter($sql, $params, $officeIds, $officeNames, 'at.');
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND at.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            if ($filters['status'] === 'disapproved') {
                $sql .= " AND at.status IN ('disapproved', 'rejected')";
            } else {
                $sql .= " AND at.status = ?";
                $params[] = $filters['status'];
            }
        }

        if (!empty($filters['travel_category'])) {
            $sql .= " AND at.travel_category = ?";
            $params[] = $filters['travel_category'];
        }

        if (!empty($filters['travel_scope'])) {
            $sql .= " AND at.travel_scope = ?";
            $params[] = $filters['travel_scope'];
        }

        if (!empty($filters['travel_type'])) {
            $sql .= " AND at.travel_type = ?";
            $params[] = $filters['travel_type'];
        }

        if (!empty($filters['unit'])) {
            if (is_numeric($filters['unit'])) {
                $sql .= " AND at.requester_office_id = ?";
            } else {
                $sql .= " AND at.requester_office = ?";
            }
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
            $filterOffices = $filters['supervised_offices'];
            $ids = array_values(array_filter($filterOffices, 'is_numeric'));
            $names = array_values(array_diff($filterOffices, $ids));
            $this->applyOfficeFilter($sql, $params, $ids, $names, 'at.');
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
     * Recommend an AT request (by Unit Head, ASDS, or SDS)
     * Uses stored final_approver_role to determine next step
     * If final approver is external (RD or DEPED_SEC), auto-forwards to RO
     */
    public function recommend($id, $recommenderId, $recommenderName, $recommenderRoleId) {
        $at = $this->getById($id);
        $recommenderTitle = getRecommendingAuthorityName($recommenderRoleId);
        $recommenderFullDisplay = $recommenderName . ', ' . $recommenderTitle;
        $finalApproverRole = $at['final_approver_role'] ?? 'SDS';
        $isExternalFinal = in_array($finalApproverRole, ['RD', 'DEPED_SEC']);

        if ($isExternalFinal) {
            // Final approver is external — auto-forward to RO after recommendation
            $sql = "UPDATE authority_to_travel SET 
                    status = 'approved',
                    recommended_by = ?,
                    recommending_authority_name = ?,
                    recommending_date = CURDATE(),
                    current_approver_role = NULL,
                    routing_stage = 'completed',
                    forwarded_to_ro = 1,
                    forwarded_to_ro_date = CURDATE()
                    WHERE id = ? AND routing_stage = 'recommending'";
            return $this->db->query($sql, [$recommenderId, $recommenderFullDisplay, $id]);
        }

        // Route to the designated in-system final approver
        $sql = "UPDATE authority_to_travel SET 
                status = 'recommended',
                recommended_by = ?,
                recommending_authority_name = ?,
                recommending_date = CURDATE(),
                current_approver_role = ?,
                routing_stage = 'final'
                WHERE id = ? AND routing_stage = 'recommending'";
        return $this->db->query($sql, [$recommenderId, $recommenderFullDisplay, $finalApproverRole, $id]);
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
                travel_type = ?,
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
            $data['travel_type'] ?? null,
            $id,
            $userId
        ]);
    }

    /**
     * Approve an AT request (by SDS/ASDS at final stage)
     * SDS approves requests after recommending stage is complete
     * Supports OIC approval
     */
    public function approve($id, $approverId, $approverName, $approverRoleId, $isOIC = false) {
        $at = $this->getById($id);
        
        // SDS/ASDS is approving at final stage - use their actual name
        $approverTitle = getApprovingAuthorityName($approverRoleId);
        $approverFullDisplay = $approverName . ', ' . $approverTitle;
        
        $sql = "UPDATE authority_to_travel SET 
                status = 'approved',
                approved_by = ?,
                approving_authority_name = ?,
                approval_date = CURDATE(),
                approving_time = NOW(),
                current_approver_role = NULL,
                routing_stage = 'completed'
                WHERE id = ?";
        
        return $this->db->query($sql, [$approverId, $approverFullDisplay, $id]);
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
     * Executive override approval (by SDS)
     * Can approve at any stage, but blocked for ATs designated for RD
     */
    public function executiveApprove($id, $approverId, $approverName) {
        $at = $this->getById($id);

        // Block executive override for ATs designated for external approvers (RD or DEPED SEC)
        $finalRole = $at['final_approver_role'] ?? '';
        if (!empty($at['forwarded_to_ro']) || in_array($finalRole, ['RD', 'DEPED_SEC'])) {
            throw new \Exception('This AT is designated for external approval (RD/DepEd Secretary) and cannot be executive-approved by SDS.');
        }
        
        // Use actual approver name with title
        $approverFullDisplay = $approverName . ', SDS';
        
        // If at recommending stage, set recommending authority too
        $sql = "UPDATE authority_to_travel SET 
                status = 'approved',
                approved_by = ?,
                approving_authority_name = ?,
                approval_date = CURDATE(),
                approving_time = NOW(),
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
     * Disapprove an Authority to Travel request
     */
    public function disapprove($id, $disapproverId, $reason = null) {
        $sql = "UPDATE authority_to_travel SET 
                status = 'disapproved',
                approved_by = ?,
                rejection_reason = ?,
                current_approver_role = NULL,
                routing_stage = 'completed'
                WHERE id = ?";
        
        return $this->db->query($sql, [$disapproverId, $reason, $id]);
    }

    /**
     * Check if user can act on this AT request
     * For unit heads, also verifies the request is from their supervised office
     * ASDS can act on Division Chief ATs at recommending stage (no office filter)
     */
    public function canUserActOn($at, $userRoleId, $userRoleName) {
        // Superadmin can VIEW all requests but cannot approve/disapprove/recommend
        if ($userRoleId == ROLE_SUPERADMIN) {
            return false;
        }

        // If already completed (approved/disapproved), no action
        if (in_array($at['status'], ['approved', 'disapproved'])) {
            return false;
        }

        // Check if current approver role matches user's role
        if ($at['current_approver_role'] !== $userRoleName) {
            return false;
        }

        // ASDS recommends for all Division Chiefs regardless of division — no office filter
        if ($userRoleId == ROLE_ASDS) {
            return true;
        }

        // For unit heads, verify the request is from their supervised office
        if (in_array($userRoleId, UNIT_HEAD_ROLES)) {
            $supervisedOffices = UNIT_HEAD_OFFICES[$userRoleId] ?? [];
            $supervisedIds = $this->getSupervisedOfficeIdsForRole($userRoleId);
            $matchesName = $at['requester_office'] && in_array($at['requester_office'], $supervisedOffices);
            $matchesId = $at['requester_office_id'] && in_array((int) $at['requester_office_id'], $supervisedIds);
            if (!$matchesName && !$matchesId) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get the action type available for user on this AT
     * Returns: 'recommend', 'approve', 'executive_approve', or null
     * Per DepEd 043: ASDS can recommend (not approve), SDS approves at final stage
     */
    public function getAvailableAction($at, $userRoleId, $userRoleName) {
        if (!$this->canUserActOn($at, $userRoleId, $userRoleName)) {
            return null;
        }

        // SDS can approve at final stage
        if ($userRoleId == ROLE_SDS && $at['routing_stage'] === 'final') {
            return 'approve';
        }

        // SDS can recommend at recommending stage (for ASDS outside-region official ATs)
        if ($userRoleId == ROLE_SDS && $at['routing_stage'] === 'recommending') {
            return 'recommend';
        }

        // ASDS can RECOMMEND at recommending stage (for Division Chief ATs)
        if ($userRoleId == ROLE_ASDS && $at['routing_stage'] === 'recommending') {
            return 'recommend';
        }

        // Unit heads can RECOMMEND requests from regular users at recommending stage
        if (in_array($userRoleId, UNIT_HEAD_ROLES) && $at['routing_stage'] === 'recommending') {
            return 'recommend';
        }

        // Unit heads can APPROVE requests at final stage when designated as final approver
        if (in_array($userRoleId, UNIT_HEAD_ROLES) && $at['routing_stage'] === 'final') {
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
        } elseif ($roleId && in_array($roleId, UNIT_HEAD_ROLES)) {
            // Unit heads see only their supervised offices (id + legacy name)
            $officeIds = $this->getSupervisedOfficeIdsForRole($roleId);
            $officeNames = $this->getSupervisedOfficesForRole($roleId);
            $this->applyOfficeFilter($baseCondition, $params, $officeIds, $officeNames, '');
        }

        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'recommended' THEN 1 ELSE 0 END) as recommended,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'disapproved' THEN 1 ELSE 0 END) as disapproved,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week
                FROM authority_to_travel WHERE 1=1" . $baseCondition;

        $stats = $this->db->query($sql, $params)->fetch();

        // If user is approver, get their pending queue count
        if ($roleName && in_array($roleId, [ROLE_ASDS, ROLE_SDS, ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF])) {
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
                SUM(CASE WHEN status = 'disapproved' THEN 1 ELSE 0 END) as disapproved,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN travel_category = 'official' AND travel_scope = 'local' AND (travel_type = 'within_region' OR travel_type IS NULL) THEN 1 ELSE 0 END) as within_region_official,
                SUM(CASE WHEN travel_category = 'official' AND travel_scope = 'local' AND travel_type = 'outside_region' THEN 1 ELSE 0 END) as outside_region_official,
                SUM(CASE WHEN travel_category = 'official' AND travel_scope = 'international' THEN 1 ELSE 0 END) as international_official,
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
                'approved' => 0, 'disapproved' => 0, 'today' => 0, 'this_week' => 0,
                'within_region_official' => 0, 'outside_region_official' => 0, 'international_official' => 0, 'personal' => 0
            ];
        }
        
        $offices = UNIT_HEAD_OFFICES[$roleId];
        $roleName = $this->getRoleNameById($roleId);
        
        $officeIds = $this->getSupervisedOfficeIdsForRole($roleId);
        $clauses = [];
        $params = [];

        if (!empty($officeIds)) {
            $idPlaceholders = implode(',', array_fill(0, count($officeIds), '?'));
            $clauses[] = "(at.requester_office_id IN ($idPlaceholders) OR u.office_id IN ($idPlaceholders))";
            $params = array_merge($params, $officeIds, $officeIds);
        }

        if (!empty($offices)) {
            $namePlaceholders = implode(',', array_fill(0, count($offices), '?'));
            $clauses[] = "(at.requester_office IN ($namePlaceholders) OR u.employee_office IN ($namePlaceholders))";
            $params = array_merge($params, $offices, $offices);
        }

        $officeClause = !empty($clauses) ? '(' . implode(' OR ', $clauses) . ')' : '1=1';
        
        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN at.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN at.status = 'recommended' THEN 1 ELSE 0 END) as recommended,
                SUM(CASE WHEN at.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN at.status = 'disapproved' THEN 1 ELSE 0 END) as disapproved,
                SUM(CASE WHEN DATE(at.created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(at.created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week,
                SUM(CASE WHEN at.travel_category = 'official' AND at.travel_scope = 'local' AND (at.travel_type = 'within_region' OR at.travel_type IS NULL) THEN 1 ELSE 0 END) as within_region_official,
                SUM(CASE WHEN at.travel_category = 'official' AND at.travel_scope = 'local' AND at.travel_type = 'outside_region' THEN 1 ELSE 0 END) as outside_region_official,
                SUM(CASE WHEN at.travel_category = 'official' AND at.travel_scope = 'international' THEN 1 ELSE 0 END) as international_official,
                SUM(CASE WHEN at.travel_category = 'personal' THEN 1 ELSE 0 END) as personal
                FROM authority_to_travel at
                LEFT JOIN admin_users u ON at.user_id = u.id
                WHERE ($officeClause 
                       OR at.current_approver_role = ?
                       OR (at.approved_by IS NOT NULL AND at.requester_role_id = ?))";
        
        $params[] = $roleName;
        $params[] = ROLE_USER;

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
        if ($roleId && in_array($roleId, UNIT_HEAD_ROLES)) {
            $sql .= " AND at.current_approver_role = ?";
            $params[] = $roleName;
            
            $officeIds = $this->getSupervisedOfficeIdsForRole($roleId);
            $officeNames = $this->getSupervisedOfficesForRole($roleId);
            $this->applyOfficeFilter($sql, $params, $officeIds, $officeNames, 'at.');
        } 
        // For ASDS, show all ATs routed to them (recommending or final)
        elseif ($roleId == ROLE_ASDS) {
            $sql .= " AND at.current_approver_role = 'ASDS'";
        }
        // Superadmin sees all pending
        
        $sql .= " ORDER BY at.created_at DESC, at.id DESC LIMIT ?";
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
     * Get type label for display (per DepEd Order 043 scopes)
     */
    public static function getTypeLabel($category, $scope = null, $travelType = null) {
        if ($category === 'personal') {
            return 'Personal';
        }
        if ($scope === 'international') {
            return 'Official - International';
        }
        if ($travelType === 'outside_region') {
            return 'Official - Outside Region';
        }
        return 'Official - Within Region';
    }

    /**
     * Get status label with routing info
     */
    public static function getStatusLabel($status, $routingStage = null, $currentApproverRole = null) {
        $labels = [
            'pending' => 'Pending Recommendation',
            'recommended' => 'Pending Final Approval',
            'approved' => 'Approved',
            'disapproved' => 'Disapproved'
        ];
        
        $label = $labels[$status] ?? ucfirst($status);
        
        if ($currentApproverRole && in_array($status, ['pending', 'recommended'])) {
            $label .= " ({$currentApproverRole})";
        }
        
        return $label;
    }
}
