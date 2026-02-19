<?php
/**
 * AdminUser Model
 * Handles admin user authentication and management for SDO ALPAS
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class AdminUser {
    private $db;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Authenticate user with email and password
     */
    public function authenticate($email, $password) {
        $sql = "SELECT au.*, ar.role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.email = ? AND au.status = 'active' AND au.is_active = 1";
        
        $user = $this->db->query($sql, [$email])->fetch();
        
        if ($user && password_verify($password, $user['password_hash'])) {
            $this->updateLastLogin($user['id']);
            return $user;
        }
        
        return false;
    }

    /**
     * Update last login timestamp
     */
    private function updateLastLogin($userId) {
        $sql = "UPDATE admin_users SET last_login = NOW() WHERE id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Get user by ID
     */
    public function getById($id) {
        $sql = "SELECT au.*, ar.role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Get user by email
     */
    public function getByEmail($email) {
        $sql = "SELECT au.*, ar.role_name, ar.permissions as role_permissions
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.email = ?";
        return $this->db->query($sql, [$email])->fetch();
    }

    /**
     * Get all admin users with filters
     */
    public function getAll($filters = [], $limit = 15, $offset = 0) {
        $sql = "SELECT au.*, ar.role_name,
                       creator.full_name as created_by_name
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                LEFT JOIN admin_users creator ON au.created_by = creator.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['role_id'])) {
            $sql .= " AND au.role_id = ?";
            $params[] = $filters['role_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND au.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND au.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (au.full_name LIKE ? OR au.email LIKE ? OR au.employee_no LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter by office_id (specific unit) - takes precedence over office_code
        if (!empty($filters['office_id'])) {
            $sql .= " AND au.office_id = ?";
            $params[] = $filters['office_id'];
        }
        // Filter by office_code (top-level office: OSDS, SGOD, CID) - only if office_id not set
        elseif (!empty($filters['office_code'])) {
            $officeCode = strtoupper(trim($filters['office_code']));
            if (in_array($officeCode, ['OSDS', 'SGOD', 'CID'])) {
                if ($officeCode === 'OSDS') {
                    // OSDS: all units with is_osds_unit = 1
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM sdo_offices o 
                        WHERE o.id = au.office_id 
                        AND o.is_osds_unit = 1 
                        AND o.is_active = 1
                    )";
                } else {
                    // SGOD or CID: units with parent_office_id matching the division
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM sdo_offices o 
                        WHERE o.id = au.office_id 
                        AND o.parent_office_id = (
                            SELECT id FROM sdo_offices 
                            WHERE office_code = ? AND is_active = 1 LIMIT 1
                        )
                        AND o.is_active = 1
                    )";
                    $params[] = $officeCode;
                }
            }
        }

        $sql .= " ORDER BY au.created_at DESC";
        
        if ($limit > 0) {
            $sql .= " LIMIT ? OFFSET ?";
            $params[] = $limit;
            $params[] = $offset;
        }

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get count of users with filters
     */
    public function getCount($filters = []) {
        $sql = "SELECT COUNT(*) as total FROM admin_users au WHERE 1=1";
        $params = [];

        if (!empty($filters['role_id'])) {
            $sql .= " AND au.role_id = ?";
            $params[] = $filters['role_id'];
        }

        if (isset($filters['status']) && $filters['status'] !== '') {
            $sql .= " AND au.status = ?";
            $params[] = $filters['status'];
        }

        if (isset($filters['is_active']) && $filters['is_active'] !== '') {
            $sql .= " AND au.is_active = ?";
            $params[] = $filters['is_active'];
        }

        if (!empty($filters['search'])) {
            $sql .= " AND (au.full_name LIKE ? OR au.email LIKE ? OR au.employee_no LIKE ?)";
            $searchTerm = '%' . $filters['search'] . '%';
            $params[] = $searchTerm;
            $params[] = $searchTerm;
            $params[] = $searchTerm;
        }

        // Filter by office_id (specific unit) - takes precedence over office_code
        if (!empty($filters['office_id'])) {
            $sql .= " AND au.office_id = ?";
            $params[] = $filters['office_id'];
        }
        // Filter by office_code (top-level office: OSDS, SGOD, CID) - only if office_id not set
        elseif (!empty($filters['office_code'])) {
            $officeCode = strtoupper(trim($filters['office_code']));
            if (in_array($officeCode, ['OSDS', 'SGOD', 'CID'])) {
                if ($officeCode === 'OSDS') {
                    // OSDS: all units with is_osds_unit = 1
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM sdo_offices o 
                        WHERE o.id = au.office_id 
                        AND o.is_osds_unit = 1 
                        AND o.is_active = 1
                    )";
                } else {
                    // SGOD or CID: units with parent_office_id matching the division
                    $sql .= " AND EXISTS (
                        SELECT 1 FROM sdo_offices o 
                        WHERE o.id = au.office_id 
                        AND o.parent_office_id = (
                            SELECT id FROM sdo_offices 
                            WHERE office_code = ? AND is_active = 1 LIMIT 1
                        )
                        AND o.is_active = 1
                    )";
                    $params[] = $officeCode;
                }
            }
        }

        $result = $this->db->query($sql, $params)->fetch();
        return $result['total'];
    }

    /**
     * Create new admin user
     */
    public function create($data, $createdBy = null) {
        $passwordHash = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : null;
        
        // Get office_id - either from data directly or lookup from office name
        $officeId = $data['office_id'] ?? null;
        $employeeOffice = $data['employee_office'] ?? null;
        
        // If office_id provided, get office_code for backward compatibility
        if ($officeId && !$employeeOffice) {
            $office = getOfficeById($officeId);
            $employeeOffice = $office ? $office['office_code'] : null;
        }
        
        $sql = "INSERT INTO admin_users (
            email, password_hash, full_name, employee_no, employee_position, 
            employee_office, office_id, role_id, status, is_active, created_by
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";
        
        $this->db->query($sql, [
            $data['email'],
            $passwordHash,
            $data['full_name'],
            $data['employee_no'] ?? null,
            $data['employee_position'] ?? null,
            $employeeOffice,
            $officeId,
            $data['role_id'] ?? ROLE_USER,
            $data['status'] ?? 'pending',
            $data['is_active'] ?? 1,
            $createdBy
        ]);

        return $this->db->lastInsertId();
    }

    /**
     * Register new employee (self-registration)
     */
    public function register($data) {
        // Check if email already exists
        if ($this->emailExists($data['email'])) {
            return ['error' => 'Email already registered'];
        }

        $passwordHash = password_hash($data['password'], PASSWORD_DEFAULT);
        
        // Get office_id and office_code
        $officeId = $data['office_id'] ?? null;
        $employeeOffice = null;
        
        if ($officeId) {
            $office = getOfficeById($officeId);
            $employeeOffice = $office ? $office['office_code'] : null;
        }
        
        $sql = "INSERT INTO admin_users (
            email, password_hash, full_name, employee_no, employee_position, 
            employee_office, office_id, role_id, status, is_active
        ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'pending', 1)";
        
        $this->db->query($sql, [
            $data['email'],
            $passwordHash,
            $data['full_name'],
            $data['employee_no'] ?? null,
            $data['employee_position'] ?? null,
            $employeeOffice,
            $officeId,
            ROLE_USER  // Always register as regular user (role_id = 6)
        ]);

        return ['success' => true, 'id' => $this->db->lastInsertId()];
    }

    /**
     * Get users by role
     */
    public function getByRole($roleId, $activeOnly = true) {
        $sql = "SELECT au.*, ar.role_name 
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.role_id = ?";
        $params = [$roleId];
        
        if ($activeOnly) {
            $sql .= " AND au.status = 'active' AND au.is_active = 1";
        }
        
        $sql .= " ORDER BY au.full_name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get unit heads (OSDS_CHIEF, CID_CHIEF, SGOD_CHIEF)
     */
    public function getUnitHeads($activeOnly = true) {
        $sql = "SELECT au.*, ar.role_name 
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.role_id IN (?, ?, ?)";
        $params = [ROLE_OSDS_CHIEF, ROLE_CID_CHIEF, ROLE_SGOD_CHIEF];
        
        if ($activeOnly) {
            $sql .= " AND au.status = 'active' AND au.is_active = 1";
        }
        
        $sql .= " ORDER BY ar.id, au.full_name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Get approvers (ASDS and Superadmin)
     */
    public function getApprovers($activeOnly = true) {
        $sql = "SELECT au.*, ar.role_name 
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.role_id IN (?, ?)";
        $params = [ROLE_SUPERADMIN, ROLE_ASDS];
        
        if ($activeOnly) {
            $sql .= " AND au.status = 'active' AND au.is_active = 1";
        }
        
        $sql .= " ORDER BY ar.id, au.full_name";
        
        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Update admin user
     */
    public function update($id, $data) {
        $fields = [];
        $params = [];

        $allowedFields = [
            'email', 'full_name', 'employee_no', 'employee_position', 
            'employee_office', 'office_id', 'role_id', 'status', 'is_active', 'avatar_url'
        ];

        foreach ($allowedFields as $field) {
            if (array_key_exists($field, $data)) {
                $fields[] = "$field = ?";
                $params[] = $data[$field];
            }
        }

        if (!empty($data['password'])) {
            $fields[] = "password_hash = ?";
            $params[] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        if (empty($fields)) {
            return false;
        }

        $params[] = $id;
        $sql = "UPDATE admin_users SET " . implode(', ', $fields) . " WHERE id = ?";
        
        return $this->db->query($sql, $params);
    }

    /**
     * Approve user registration
     */
    public function approveRegistration($id) {
        return $this->update($id, ['status' => 'active']);
    }

    /**
     * Reject/Deactivate user
     */
    public function deactivate($id) {
        return $this->update($id, ['status' => 'inactive', 'is_active' => 0]);
    }

    /**
     * Activate admin user (allow sign-in again)
     */
    public function activate($id) {
        return $this->update($id, ['status' => 'active', 'is_active' => 1]);
    }

    /**
     * Delete admin user
     */
    public function delete($id) {
        $sql = "DELETE FROM admin_users WHERE id = ?";
        return $this->db->query($sql, [$id]);
    }

    /**
     * Check if email exists
     */
    public function emailExists($email, $excludeId = null) {
        $sql = "SELECT id FROM admin_users WHERE email = ?";
        $params = [$email];

        if ($excludeId) {
            $sql .= " AND id != ?";
            $params[] = $excludeId;
        }

        return $this->db->query($sql, $params)->fetch() !== false;
    }

    /**
     * Get all roles
     */
    public function getRoles() {
        $sql = "SELECT * FROM admin_roles WHERE is_active = 1 ORDER BY id";
        return $this->db->query($sql)->fetchAll();
    }

    /**
     * Get role by ID
     */
    public function getRoleById($id) {
        $sql = "SELECT * FROM admin_roles WHERE id = ?";
        return $this->db->query($sql, [$id])->fetch();
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($user, $permission) {
        if (!$user || empty($user['role_permissions'])) {
            return false;
        }

        $permissions = json_decode($user['role_permissions'], true);
        
        // Super admin has all permissions
        if (isset($permissions['all']) && $permissions['all'] === true) {
            return true;
        }

        // Check specific permission
        return isset($permissions[$permission]) && $permissions[$permission] === true;
    }

    /**
     * Get pending registrations count
     */
    public function getPendingRegistrationsCount() {
        $sql = "SELECT COUNT(*) as total FROM admin_users WHERE status = 'pending'";
        $result = $this->db->query($sql)->fetch();
        return $result['total'];
    }

    /**
     * Get pending registrations
     */
    public function getPendingRegistrations($limit = 10) {
        $sql = "SELECT au.*, ar.role_name
                FROM admin_users au
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE au.status = 'pending'
                ORDER BY au.created_at DESC
                LIMIT ?";
        return $this->db->query($sql, [$limit])->fetchAll();
    }
}
