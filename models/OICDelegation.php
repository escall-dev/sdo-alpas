<?php
/**
 * OIC Delegation Model
 * Handles OIC (Officer-In-Charge) delegation assignments
 * SDO ALPAS
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class OICDelegation {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Create a new OIC delegation
     * Only one active OIC per unit at a time
     */
    public function create($data) {
        // Check if there's already an active OIC for this unit
        $existing = $this->getActiveOICForUnit($data['unit_head_role_id']);
        if ($existing) {
            // Deactivate existing OIC
            $this->deactivate($existing['id']);
        }

        $sql = "INSERT INTO oic_delegations (
            unit_head_user_id, unit_head_role_id, oic_user_id,
            start_date, end_date, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, 1, ?)";
        
        $this->db->query($sql, [
            $data['unit_head_user_id'],
            $data['unit_head_role_id'],
            $data['oic_user_id'],
            $data['start_date'],
            $data['end_date'],
            $data['created_by'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Get active OIC for a unit head role
     */
    public function getActiveOICForUnit($roleId) {
        $sql = "SELECT o.*, 
                       oic.full_name as oic_name, oic.email as oic_email,
                       oic.employee_position as oic_position,
                       uh.full_name as unit_head_name
                FROM oic_delegations o
                JOIN admin_users oic ON o.oic_user_id = oic.id
                JOIN admin_users uh ON o.unit_head_user_id = uh.id
                WHERE o.unit_head_role_id = ? 
                  AND o.is_active = 1 
                  AND CURDATE() BETWEEN o.start_date AND o.end_date
                ORDER BY o.created_at DESC
                LIMIT 1";
        
        return $this->db->query($sql, [$roleId])->fetch();
    }

    /**
     * Get all OIC delegations for a unit head
     */
    public function getByUnitHead($unitHeadUserId, $unitHeadRoleId, $includeInactive = false) {
        $sql = "SELECT o.*, 
                       oic.full_name as oic_name, oic.email as oic_email,
                       oic.employee_position as oic_position,
                       creator.full_name as created_by_name
                FROM oic_delegations o
                JOIN admin_users oic ON o.oic_user_id = oic.id
                LEFT JOIN admin_users creator ON o.created_by = creator.id
                WHERE o.unit_head_user_id = ? AND o.unit_head_role_id = ?";
        
        if (!$includeInactive) {
            $sql .= " AND o.is_active = 1";
        }
        
        $sql .= " ORDER BY o.start_date DESC, o.created_at DESC";
        
        return $this->db->query($sql, [$unitHeadUserId, $unitHeadRoleId])->fetchAll();
    }

    /**
     * Get all active OIC delegations
     */
    public function getAllActive() {
        $sql = "SELECT o.*, 
                       oic.full_name as oic_name, oic.email as oic_email,
                       oic.employee_position as oic_position,
                       uh.full_name as unit_head_name, uh.employee_office as unit_head_office,
                       role.role_name as unit_head_role_name
                FROM oic_delegations o
                JOIN admin_users oic ON o.oic_user_id = oic.id
                JOIN admin_users uh ON o.unit_head_user_id = uh.id
                JOIN admin_roles role ON o.unit_head_role_id = role.id
                WHERE o.is_active = 1 
                  AND CURDATE() BETWEEN o.start_date AND o.end_date
                ORDER BY o.start_date DESC";
        
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Check if a user is currently an active OIC
     */
    public function isActiveOIC($userId) {
        $sql = "SELECT COUNT(*) as count FROM oic_delegations 
                WHERE oic_user_id = ? 
                  AND is_active = 1 
                  AND CURDATE() BETWEEN start_date AND end_date";
        
        $result = $this->db->query($sql, [$userId])->fetch();
        return $result['count'] > 0;
    }

    /**
     * Get the effective approver user ID for a role
     * Returns OIC user ID if active, otherwise returns unit head user ID
     */
    public function getEffectiveApproverUserId($roleId, $unitHeadUserId) {
        $activeOIC = $this->getActiveOICForUnit($roleId);
        
        if ($activeOIC) {
            return $activeOIC['oic_user_id'];
        }
        
        return $unitHeadUserId;
    }

    /**
     * Deactivate an OIC delegation
     */
    public function deactivate($id) {
        $sql = "UPDATE oic_delegations SET is_active = 0 WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Delete an OIC delegation
     */
    public function delete($id) {
        $sql = "DELETE FROM oic_delegations WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Get users eligible to be OIC for a unit
     * Must be users under the unit head's supervision
     */
    public function getEligibleOICUsers($unitHeadRoleId, $unitHeadUserId) {
        if (!isset(UNIT_HEAD_OFFICES[$unitHeadRoleId])) {
            return [];
        }

        $supervisedOffices = UNIT_HEAD_OFFICES[$unitHeadRoleId];
        $placeholders = implode(',', array_fill(0, count($supervisedOffices), '?'));
        
        $sql = "SELECT au.*, ar.role_name 
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.employee_office IN ($placeholders)
                  AND au.id != ?
                  AND au.status = 'active'
                  AND au.is_active = 1
                ORDER BY au.full_name";
        
        $params = array_merge($supervisedOffices, [$unitHeadUserId]);
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Check if dates overlap with existing active OIC
     */
    public function hasDateOverlap($unitHeadRoleId, $startDate, $endDate, $excludeId = null) {
        $sql = "SELECT COUNT(*) as count FROM oic_delegations 
                WHERE unit_head_role_id = ? 
                  AND is_active = 1
                  AND (
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date <= ? AND end_date >= ?) OR
                    (start_date >= ? AND end_date <= ?)
                  )";
        
        $params = [$unitHeadRoleId, $startDate, $startDate, $endDate, $endDate, $startDate, $endDate];
        
        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }
        
        $result = $this->db->query($sql, $params)->fetch();
        return $result['count'] > 0;
    }

    /**
     * Update OIC delegation dates (only if no overlap)
     */
    public function update($id, $data) {
        // Check for date overlap
        $existing = $this->db->query("SELECT * FROM oic_delegations WHERE id = ?", [$id])->fetch();
        if (!$existing) {
            return false;
        }

        if (isset($data['start_date']) && isset($data['end_date'])) {
            if ($this->hasDateOverlap($existing['unit_head_role_id'], $data['start_date'], $data['end_date'], $id)) {
                return false; // Overlap detected
            }
        }

        $fields = [];
        $params = [];

        if (isset($data['start_date'])) {
            $fields[] = "start_date = ?";
            $params[] = $data['start_date'];
        }

        if (isset($data['end_date'])) {
            $fields[] = "end_date = ?";
            $params[] = $data['end_date'];
        }

        if (isset($data['is_active'])) {
            $fields[] = "is_active = ?";
            $params[] = $data['is_active'] ? 1 : 0;
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE oic_delegations SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }
}
