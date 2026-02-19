<?php
/**
 * Pass Slip Model
 * SDO ALPAS - Handles CRUD, routing, approval, and statistics for Pass Slips
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../services/TrackingService.php';

class PassSlip
{
    private $db;

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new Pass Slip with auto-routing
     */
    public function create($data, $requesterRoleId = null, $requesterOffice = null, $requesterOfficeId = null)
    {
        $trackingService = new TrackingService();
        $controlNo = $trackingService->generatePSNumber();

        // Determine approver through routing
        $approverRoleId = $this->getApproverRoleForOffice($requesterRoleId, $requesterOffice, $requesterOfficeId);
        $approverUserId = $this->getEffectiveApproverUserId($approverRoleId, $requesterRoleId);

        $sql = "INSERT INTO pass_slips (
            ps_control_no, employee_name, employee_position, employee_office,
            date, destination, idt, iat,
            purpose, user_id, assigned_approver_role_id, assigned_approver_user_id,
            requesting_employee_name, request_date, status
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'pending')";

        $this->db->query($sql, [
            $controlNo,
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['employee_office'] ?? null,
            $data['date'],
            $data['destination'],
            $data['idt'],
            $data['iat'],
            $data['purpose'],
            $data['user_id'],
            $approverRoleId,
            $approverUserId,
            $data['requesting_employee_name'] ?? $data['employee_name'],
            $data['request_date'] ?? date('Y-m-d')
        ]);

        return ['id' => $this->db->lastInsertId(), 'control_no' => $controlNo];
    }

    /**
     * Get Pass Slip by ID with filed-by user info
     */
    public function getById($id)
    {
        $sql = "SELECT ps.*, 
                u.full_name as filed_by_name, 
                u.email as filed_by_email
                FROM pass_slips ps
                LEFT JOIN admin_users u ON ps.user_id = u.id
                WHERE ps.id = ?";

        $stmt = $this->db->query($sql, [$id]);
        return $stmt->fetch();
    }

    /**
     * Get Pass Slip by control number
     */
    public function getByControlNumber($controlNo)
    {
        $sql = "SELECT ps.*, 
                u.full_name as filed_by_name, 
                u.email as filed_by_email
                FROM pass_slips ps
                LEFT JOIN admin_users u ON ps.user_id = u.id
                WHERE ps.ps_control_no = ?";

        $stmt = $this->db->query($sql, [$controlNo]);
        return $stmt->fetch();
    }

    /**
     * Approve a Pass Slip
     */
    public function approve($id, $approverId, $approverName, $approverPosition, $isOIC = false)
    {
        $sql = "UPDATE pass_slips SET 
                status = 'approved',
                approved_by = ?,
                approver_name = ?,
                approver_position = ?,
                approval_date = CURDATE(),
                approving_time = CURTIME(),
                oic_approved = ?,
                oic_approver_name = ?
                WHERE id = ? AND status = 'pending'";

        $this->db->query($sql, [
            $approverId,
            $approverName,
            $approverPosition,
            $isOIC ? 1 : 0,
            $isOIC ? $approverName : null,
            $id
        ]);
    }

    /**
     * Reject a Pass Slip
     */
    public function reject($id, $approverId, $reason = null)
    {
        $sql = "UPDATE pass_slips SET 
                status = 'rejected',
                approved_by = ?,
                rejection_reason = ?
                WHERE id = ? AND status = 'pending'";

        $this->db->query($sql, [$approverId, $reason, $id]);
    }

    /**
     * Cancel a Pass Slip (only by owner, only when pending)
     */
    public function cancel($id, $userId)
    {
        $sql = "UPDATE pass_slips SET 
                status = 'cancelled',
                cancelled_at = NOW(),
                cancelled_by = ?
                WHERE id = ? AND user_id = ? AND status = 'pending'";

        $this->db->query($sql, [$userId, $id, $userId]);
    }

    /**
     * Update guard times (actual departure/arrival) — only when approved
     */
    public function updateGuardTimes($id, $departureTime, $arrivalTime)
    {
        $sql = "UPDATE pass_slips SET 
                actual_departure_time = ?,
                actual_arrival_time = ?
                WHERE id = ? AND status = 'approved'";

        $this->db->query($sql, [$departureTime ?: null, $arrivalTime ?: null, $id]);
    }

    /**
     * Update a pending Pass Slip (only by owner)
     */
    public function update($id, $data, $userId)
    {
        $sql = "UPDATE pass_slips SET 
                employee_name = ?,
                employee_position = ?,
                employee_office = ?,
                date = ?,
                destination = ?,
                idt = ?,
                iat = ?,
                purpose = ?
                WHERE id = ? AND user_id = ? AND status = 'pending'";

        $this->db->query($sql, [
            $data['employee_name'],
            $data['employee_position'] ?? null,
            $data['employee_office'] ?? null,
            $data['date'],
            $data['destination'],
            $data['idt'],
            $data['iat'],
            $data['purpose'],
            $id,
            $userId
        ]);
    }

    /**
     * Delete a pending Pass Slip (only by owner)
     */
    public function delete($id, $userId)
    {
        $sql = "DELETE FROM pass_slips WHERE id = ? AND user_id = ? AND status = 'pending'";
        $this->db->query($sql, [$id, $userId]);
    }

    /**
     * Get all Pass Slips with filters and visibility rules
     */
    public function getAll($filters = [], $limit = 20, $offset = 0, $viewerRoleId = null, $viewerUserId = null)
    {
        $where = [];
        $params = [];

        // Visibility filtering based on role
        if ($viewerRoleId !== null && $viewerUserId !== null) {
            if ($viewerRoleId == ROLE_SUPERADMIN || $viewerRoleId == ROLE_SDS) {
                // Superadmin and SDS can see all
            } elseif ($viewerRoleId == ROLE_ASDS) {
                // ASDS sees own + requests assigned to ASDS role
                $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ?)";
                $params[] = $viewerUserId;
                $params[] = ROLE_ASDS;
            } elseif (in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
                // Unit heads see own + requests assigned to them
                $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ? OR ps.assigned_approver_user_id = ?)";
                $params[] = $viewerUserId;
                $params[] = $viewerRoleId;
                $params[] = $viewerUserId;
            } else {
                // Regular employees see only their own
                $where[] = "ps.user_id = ?";
                $params[] = $viewerUserId;
            }
        }

        // Apply filters
        if (!empty($filters['status'])) {
            $where[] = "ps.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['unit'])) {
            $where[] = "ps.employee_office = ?";
            $params[] = $filters['unit'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "ps.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "ps.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $where[] = "(ps.ps_control_no LIKE ? OR ps.employee_name LIKE ? OR ps.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if (!empty($filters['user_id'])) {
            $where[] = "ps.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['approval_date_from'])) {
            $where[] = "ps.approval_date >= ?";
            $params[] = $filters['approval_date_from'];
        }
        if (!empty($filters['approval_date_to'])) {
            $where[] = "ps.approval_date <= ?";
            $params[] = $filters['approval_date_to'];
        }
        if (!empty($filters['approver_id'])) {
            $where[] = "ps.approved_by = ?";
            $params[] = $filters['approver_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT ps.*, 
                u.full_name as filed_by_name, 
                u.email as filed_by_email
                FROM pass_slips ps
                LEFT JOIN admin_users u ON ps.user_id = u.id
                $whereClause
                ORDER BY ps.created_at DESC
                LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetchAll();
    }

    /**
     * Get count for pagination
     */
    public function getCount($filters = [], $viewerRoleId = null, $viewerUserId = null)
    {
        $where = [];
        $params = [];

        // Same visibility filtering as getAll
        if ($viewerRoleId !== null && $viewerUserId !== null) {
            if ($viewerRoleId == ROLE_SUPERADMIN || $viewerRoleId == ROLE_SDS) {
                // See all
            } elseif ($viewerRoleId == ROLE_ASDS) {
                $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ?)";
                $params[] = $viewerUserId;
                $params[] = ROLE_ASDS;
            } elseif (in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
                $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ? OR ps.assigned_approver_user_id = ?)";
                $params[] = $viewerUserId;
                $params[] = $viewerRoleId;
                $params[] = $viewerUserId;
            } else {
                $where[] = "ps.user_id = ?";
                $params[] = $viewerUserId;
            }
        }

        if (!empty($filters['status'])) {
            $where[] = "ps.status = ?";
            $params[] = $filters['status'];
        }
        if (!empty($filters['unit'])) {
            $where[] = "ps.employee_office = ?";
            $params[] = $filters['unit'];
        }
        if (!empty($filters['date_from'])) {
            $where[] = "ps.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $where[] = "ps.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }
        if (!empty($filters['search'])) {
            $where[] = "(ps.ps_control_no LIKE ? OR ps.employee_name LIKE ? OR ps.destination LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }
        if (!empty($filters['user_id'])) {
            $where[] = "ps.user_id = ?";
            $params[] = $filters['user_id'];
        }
        if (!empty($filters['approval_date_from'])) {
            $where[] = "ps.approval_date >= ?";
            $params[] = $filters['approval_date_from'];
        }
        if (!empty($filters['approval_date_to'])) {
            $where[] = "ps.approval_date <= ?";
            $params[] = $filters['approval_date_to'];
        }
        if (!empty($filters['approver_id'])) {
            $where[] = "ps.approved_by = ?";
            $params[] = $filters['approver_id'];
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT COUNT(*) as total FROM pass_slips ps $whereClause";
        $stmt = $this->db->query($sql, $params);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Get statistics for dashboard
     */
    public function getStatistics($userId = null)
    {
        $where = '';
        $params = [];

        if ($userId) {
            $where = 'WHERE user_id = ?';
            $params = [$userId];
        }

        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM pass_slips $where";

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get statistics visible to a specific viewer role
     */
    public function getVisibleStatistics($viewerRoleId, $viewerUserId)
    {
        $where = [];
        $params = [];

        if ($viewerRoleId == ROLE_SUPERADMIN || $viewerRoleId == ROLE_SDS) {
            // See all
        } elseif ($viewerRoleId == ROLE_ASDS) {
            $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ?)";
            $params[] = $viewerUserId;
            $params[] = ROLE_ASDS;
        } elseif (in_array($viewerRoleId, UNIT_HEAD_ROLES)) {
            $where[] = "(ps.user_id = ? OR ps.assigned_approver_role_id = ? OR ps.assigned_approver_user_id = ?)";
            $params[] = $viewerUserId;
            $params[] = $viewerRoleId;
            $params[] = $viewerUserId;
        } else {
            $where[] = "ps.user_id = ?";
            $params[] = $viewerUserId;
        }

        $whereClause = !empty($where) ? 'WHERE ' . implode(' AND ', $where) : '';

        $sql = "SELECT 
                COUNT(*) as total,
                SUM(CASE WHEN ps.status = 'pending' THEN 1 ELSE 0 END) as pending,
                SUM(CASE WHEN ps.status = 'approved' THEN 1 ELSE 0 END) as approved,
                SUM(CASE WHEN ps.status = 'rejected' THEN 1 ELSE 0 END) as rejected,
                SUM(CASE WHEN ps.status = 'cancelled' THEN 1 ELSE 0 END) as cancelled
                FROM pass_slips ps $whereClause";

        $stmt = $this->db->query($sql, $params);
        return $stmt->fetch();
    }

    /**
     * Get pending Pass Slips assigned to a specific approver
     */
    public function getPendingForApprover($approverRoleId, $approverUserId, $limit = 5)
    {
        $sql = "SELECT ps.*, u.full_name as filed_by_name
                FROM pass_slips ps
                LEFT JOIN admin_users u ON ps.user_id = u.id
                WHERE ps.status = 'pending' 
                AND (ps.assigned_approver_user_id = ? OR ps.assigned_approver_role_id = ?)
                ORDER BY ps.created_at ASC
                LIMIT ?";

        $stmt = $this->db->query($sql, [$approverUserId, $approverRoleId, $limit]);
        return $stmt->fetchAll();
    }

    /**
     * Get count of pending Pass Slips for approver (used for sidebar badges)
     */
    public function getPendingCountForApprover($approverRoleId, $approverUserId)
    {
        $sql = "SELECT COUNT(*) as total 
                FROM pass_slips 
                WHERE status = 'pending' 
                AND (assigned_approver_user_id = ? OR assigned_approver_role_id = ?)";

        $stmt = $this->db->query($sql, [$approverUserId, $approverRoleId]);
        $result = $stmt->fetch();
        return $result['total'];
    }

    /**
     * Check if user can view this Pass Slip
     */
    public function canUserView($ps, $viewerRoleId, $viewerUserId)
    {
        // Owner can always view
        if ($ps['user_id'] == $viewerUserId)
            return true;
        // Superadmin and SDS can view all
        if ($viewerRoleId == ROLE_SUPERADMIN || $viewerRoleId == ROLE_SDS)
            return true;
        // ASDS can view assigned
        if ($viewerRoleId == ROLE_ASDS)
            return true;
        // Assigned approver can view
        if ($ps['assigned_approver_user_id'] == $viewerUserId)
            return true;
        // Unit head for the assigned role
        if (in_array($viewerRoleId, UNIT_HEAD_ROLES) && $ps['assigned_approver_role_id'] == $viewerRoleId)
            return true;

        return false;
    }

    /**
     * Check if user can edit this Pass Slip
     */
    public function canUserEdit($ps, $userId)
    {
        return $ps['user_id'] == $userId && $ps['status'] === 'pending';
    }

    /**
     * Validate submission data
     */
    public function validateSubmission($data)
    {
        $errors = [];

        if (empty($data['date'])) {
            $errors[] = 'Date is required.';
        } else {
            $today = date('Y-m-d');
            if ($data['date'] < $today) {
                $errors[] = 'Date cannot be in the past.';
            }
        }

        if (empty($data['destination'])) {
            $errors[] = 'Destination is required.';
        }
        if (empty($data['idt'])) {
            $errors[] = 'Intended departure time is required.';
        }
        if (empty($data['iat'])) {
            $errors[] = 'Intended arrival time is required.';
        }
        if (empty($data['purpose'])) {
            $errors[] = 'Purpose is required.';
        }

        return ['valid' => empty($errors), 'errors' => $errors];
    }

    // ===== ROUTING LOGIC (mirrored from LocatorSlip) =====

    /**
     * Determine the approver role for a given office
     */
    private function getApproverRoleForOffice($requesterRoleId, $requesterOffice, $requesterOfficeId = null)
    {
        // Office Chiefs route to ASDS
        if (in_array($requesterRoleId, OFFICE_CHIEF_ROLES)) {
            return ROLE_ASDS;
        }

        // Direct-to-SDS positions
        if (in_array($requesterRoleId, [ROLE_ASDS])) {
            return ROLE_ASDS;
        }

        // Use office_id for routing if available (query sdo_offices table)
        if ($requesterOfficeId !== null) {
            return getApproverRoleByOfficeId($requesterOfficeId);
        }

        // Fall back to office name matching
        if ($requesterOffice) {
            $officeUpper = strtoupper(trim($requesterOffice));

            // OSDS units
            if (defined('OSDS_UNITS')) {
                foreach (OSDS_UNITS as $unit) {
                    if (stripos($officeUpper, strtoupper($unit)) !== false) {
                        return ROLE_OSDS_CHIEF;
                    }
                }
            }

            // Check office name patterns
            if (stripos($officeUpper, 'CID') !== false || stripos($officeUpper, 'CURRICULUM') !== false) {
                return ROLE_CID_CHIEF;
            }
            if (stripos($officeUpper, 'SGOD') !== false || stripos($officeUpper, 'GOVERNANCE') !== false) {
                return ROLE_SGOD_CHIEF;
            }
            if (stripos($officeUpper, 'OSDS') !== false || stripos($officeUpper, 'ADMIN') !== false) {
                return ROLE_OSDS_CHIEF;
            }
        }

        // Default to OSDS Chief
        return ROLE_OSDS_CHIEF;
    }

    /**
     * Resolve the actual approver user ID for a given role
     */
    private function getEffectiveApproverUserId($approverRoleId, $requesterRoleId = null)
    {
        try {
            require_once __DIR__ . '/AdminUser.php';
            $userModel = new AdminUser();

            // Check for active OIC delegation first
            if (method_exists($userModel, 'getActiveOICForRole')) {
                $oic = $userModel->getActiveOICForRole($approverRoleId);
                if ($oic) {
                    return $oic['delegate_user_id'];
                }
            }

            // Get users with the approver role
            $users = $userModel->getByRole($approverRoleId, true);
            if (!empty($users)) {
                return $users[0]['id'];
            }
        } catch (Exception $e) {
            // Fall through
        }

        return null;
    }
}
