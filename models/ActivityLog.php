<?php
/**
 * ActivityLog Model
 * Handles activity logging for SDO ALPAS
 */

require_once __DIR__ . '/../config/database.php';

class ActivityLog {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Log an activity
     */
    public function log($userId, $actionType, $entityType, $entityId = null, $description = null, $oldValue = null, $newValue = null) {
        $sql = "INSERT INTO activity_logs (user_id, action_type, entity_type, entity_id, description, old_value, new_value, ip_address, user_agent)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $userId,
            $actionType,
            $entityType,
            $entityId,
            $description,
            $oldValue ? json_encode($oldValue) : null,
            $newValue ? json_encode($newValue) : null,
            $_SERVER['REMOTE_ADDR'] ?? null,
            $_SERVER['HTTP_USER_AGENT'] ?? null
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Get activity logs with filters
     */
    public function getLogs($filters = [], $limit = 50, $offset = 0) {
        $sql = "SELECT al.*, au.full_name as user_name, au.email as user_email
                FROM activity_logs al
                LEFT JOIN admin_users au ON al.user_id = au.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action_type'])) {
            $sql .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['entity_id'])) {
            $sql .= " AND al.entity_id = ?";
            $params[] = $filters['entity_id'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (al.description LIKE ? OR au.full_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $sql .= " ORDER BY al.created_at DESC LIMIT ? OFFSET ?";
        $params[] = $limit;
        $params[] = $offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get total count of logs
     */
    public function getLogsCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM activity_logs al 
                LEFT JOIN admin_users au ON al.user_id = au.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['user_id'])) {
            $sql .= " AND al.user_id = ?";
            $params[] = $filters['user_id'];
        }

        if (!empty($filters['action_type'])) {
            $sql .= " AND al.action_type = ?";
            $params[] = $filters['action_type'];
        }

        if (!empty($filters['entity_type'])) {
            $sql .= " AND al.entity_type = ?";
            $params[] = $filters['entity_type'];
        }

        if (!empty($filters['date_from'])) {
            $sql .= " AND al.created_at >= ?";
            $params[] = $filters['date_from'] . ' 00:00:00';
        }

        if (!empty($filters['date_to'])) {
            $sql .= " AND al.created_at <= ?";
            $params[] = $filters['date_to'] . ' 23:59:59';
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (al.description LIKE ? OR au.full_name LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Get logs for a specific entity
     */
    public function getEntityLogs($entityType, $entityId, $limit = 50) {
        return $this->getLogs([
            'entity_type' => $entityType,
            'entity_id' => $entityId
        ], $limit, 0);
    }

    /**
     * Get recent activity for dashboard
     */
    public function getRecentActivity($limit = 10) {
        return $this->getLogs([], $limit, 0);
    }

    /**
     * Get distinct action types for filter dropdown
     */
    public function getActionTypes() {
        $sql = "SELECT DISTINCT action_type FROM activity_logs ORDER BY action_type";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }

    /**
     * Get distinct entity types for filter dropdown
     */
    public function getEntityTypes() {
        $sql = "SELECT DISTINCT entity_type FROM activity_logs WHERE entity_type IS NOT NULL ORDER BY entity_type";
        return $this->db->query($sql)->fetchAll(PDO::FETCH_COLUMN);
    }
}
