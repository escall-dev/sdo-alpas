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
     * Determine the approver role ID based on employee office
     * CID staff → cid_chief
     * SGOD staff → sgod_chief
     * OSDS units (Supply, Records, HR, etc.) → osds_chief
     */
    public function getApproverRoleForOffice($office) {
        $office = trim($office);
        
        if ($office === 'CID') {
            return ROLE_CID_CHIEF;
        } elseif ($office === 'SGOD') {
            return ROLE_SGOD_CHIEF;
        } elseif (in_array($office, OSDS_UNITS)) {
            return ROLE_OSDS_CHIEF;
        }
        
        // Default to OSDS Chief
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
     * Create a new Locator Slip request with unit-based routing
     */
    public function create($data, $requesterRoleId = null, $requesterOffice = null) {
        // Determine approver based on office
        $approverRoleId = null;
        $assignedApproverUserId = null;
        
        if ($requesterOffice) {
            $approverRoleId = $this->getApproverRoleForOffice($requesterOffice);
            
            // Get the unit head user for this role
            require_once __DIR__ . '/AdminUser.php';
            $userModel = new AdminUser();
            $unitHeads = $userModel->getByRole($approverRoleId, true);
            
            if (!empty($unitHeads)) {
                $unitHeadUserId = $unitHeads[0]['id'];
                // Check if there's an active OIC
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
                       u.full_name as filed_by_name,
                       a.full_name as approved_by_name
                FROM locator_slips ls
                LEFT JOIN admin_users u ON ls.user_id = u.id
                LEFT JOIN admin_users a ON ls.approved_by = a.id
                WHERE ls.ls_control_no = ?";
        return $this->db->query($sql, [$controlNo])->fetch();
    }

    /**
     * Get all Locator Slips with filters
     * Includes visibility filtering based on user role
     */
    public function getAll($filters = [], $limit = 15, $offset = 0, $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT ls.*, 
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

        // Visibility filtering
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            // Regular employees see only their own requests
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            // Unit heads see only requests assigned to them (or their OIC)
            $sql .= " AND (ls.assigned_approver_user_id = ? OR ls.assigned_approver_role_id = ?)";
            $params[] = $viewerUserId;
            $params[] = $viewerRoleId;
        } elseif ($viewerRoleId == ROLE_ASDS) {
            // ASDS sees all requests
            // No additional filter
        } elseif ($viewerRoleId == ROLE_SUPERADMIN) {
            // Superadmin sees all requests
            // No additional filter
        } elseif ($viewerUserId) {
            // Default: users see only their own requests
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ls.status = ?";
            $params[] = $filters['status'];
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
     * Get count of Locator Slips with filters
     * Includes visibility filtering based on user role
     */
    public function getCount($filters = [], $viewerRoleId = null, $viewerUserId = null) {
        $sql = "SELECT COUNT(*) as total FROM locator_slips ls WHERE 1=1";
        $params = [];

        // Visibility filtering (same as getAll)
        if ($viewerRoleId == ROLE_USER && $viewerUserId) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        } elseif ($viewerRoleId && in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            $sql .= " AND (ls.assigned_approver_user_id = ? OR ls.assigned_approver_role_id = ?)";
            $params[] = $viewerUserId;
            $params[] = $viewerRoleId;
        } elseif ($viewerUserId && !in_array($viewerRoleId, [ROLE_ASDS, ROLE_SUPERADMIN])) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $viewerUserId;
        }

        if (!empty($filters['user_id'])) {
            $sql .= " AND ls.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['status'])) {
            $sql .= " AND ls.status = ?";
            $params[] = $filters['status'];
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
     */
    public function approve($id, $approverId, $approverName, $approverPosition, $isOIC = false) {
        $sql = "UPDATE locator_slips SET 
                status = 'approved',
                approved_by = ?,
                approver_name = ?,
                approver_position = ?,
                approval_date = CURDATE()
                WHERE id = ?";
        
        return $this->db->query($sql, [$approverId, $approverName, $approverPosition, $id]);
    }

    /**
     * Check if user can view this Locator Slip
     */
    public function canUserView($ls, $viewerRoleId, $viewerUserId) {
        // Requestor can always view
        if ($ls['user_id'] == $viewerUserId) {
            return true;
        }

        // Superadmin and ASDS can view all
        if (in_array($viewerRoleId, [ROLE_SUPERADMIN, ROLE_ASDS])) {
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
     * Reject a Locator Slip
     */
    public function reject($id, $approverId, $reason = null) {
        $sql = "UPDATE locator_slips SET 
                status = 'rejected',
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
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today,
                SUM(CASE WHEN YEARWEEK(created_at) = YEARWEEK(CURDATE()) THEN 1 ELSE 0 END) as this_week
                FROM locator_slips WHERE 1=1" . $userCondition;

        return $this->db->query($sql, $params)->fetch();
    }

    /**
     * Get recent Locator Slips for dashboard
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
