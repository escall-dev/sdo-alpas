<?php
/**
 * Admin Authentication Helper
 * Token-based session management for multi-account support
 * SDO ATLAS - Schools Division Office Authority to Travel and Locator Approval System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../models/AdminUser.php';
require_once __DIR__ . '/../models/SessionToken.php';
require_once __DIR__ . '/../models/ActivityLog.php';

class AdminAuth {
    private static $instance = null;
    private $user = null;
    private $token = null;
    private $adminUserModel;
    private $sessionTokenModel;
    private $activityLog;

    private function __construct() {
        $this->adminUserModel = new AdminUser();
        $this->sessionTokenModel = new SessionToken();
        $this->activityLog = new ActivityLog();
        $this->loadUserFromToken();
    }

    public static function getInstance() {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        return self::$instance;
    }

    /**
     * Load user from token (URL parameter, header, or cookie)
     */
    private function loadUserFromToken() {
        $token = $this->getTokenFromRequest();
        
        if ($token) {
            $userData = $this->sessionTokenModel->validate($token);
            if ($userData) {
                $this->user = $userData;
                $this->token = $token;
            }
        }
    }

    /**
     * Get token from various sources
     */
    private function getTokenFromRequest() {
        // 1. Check URL parameter
        if (!empty($_GET['token'])) {
            return $_GET['token'];
        }
        
        // 2. Check POST parameter
        if (!empty($_POST['_token'])) {
            return $_POST['_token'];
        }
        
        // 3. Check Authorization header
        $headers = $this->getAuthorizationHeader();
        if ($headers && preg_match('/Bearer\s+(.*)$/i', $headers, $matches)) {
            return $matches[1];
        }
        
        // 4. Check custom header
        if (!empty($_SERVER['HTTP_X_AUTH_TOKEN'])) {
            return $_SERVER['HTTP_X_AUTH_TOKEN'];
        }
        
        // 5. Check cookie (fallback)
        if (!empty($_COOKIE['atlas_token'])) {
            return $_COOKIE['atlas_token'];
        }
        
        return null;
    }

    /**
     * Get Authorization header
     */
    private function getAuthorizationHeader() {
        if (isset($_SERVER['Authorization'])) {
            return trim($_SERVER['Authorization']);
        }
        if (isset($_SERVER['HTTP_AUTHORIZATION'])) {
            return trim($_SERVER['HTTP_AUTHORIZATION']);
        }
        if (function_exists('apache_request_headers')) {
            $headers = apache_request_headers();
            if (isset($headers['Authorization'])) {
                return trim($headers['Authorization']);
            }
        }
        return null;
    }

    /**
     * Login with email and password
     * Returns token on success, false on failure
     */
    public function login($email, $password) {
        $user = $this->adminUserModel->authenticate($email, $password);
        
        if ($user) {
            // Generate new token
            $token = $this->sessionTokenModel->create($user['id']);
            
            // Set cookie for convenience (but token in URL takes precedence)
            setcookie('atlas_token', $token, [
                'expires' => time() + TOKEN_LIFETIME,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
            
            $this->user = $user;
            $this->token = $token;
            
            $this->activityLog->log($user['id'], 'login', 'auth', null, 'User logged in');
            
            return $token;
        }
        
        return false;
    }

    /**
     * Logout user - invalidate current token
     */
    public function logout() {
        if ($this->token) {
            if ($this->user) {
                $this->activityLog->log($this->user['id'], 'logout', 'auth', null, 'User logged out');
            }
            
            $this->sessionTokenModel->delete($this->token);
            
            // Clear cookie
            setcookie('atlas_token', '', [
                'expires' => time() - 3600,
                'path' => '/',
                'secure' => isset($_SERVER['HTTPS']),
                'httponly' => true,
                'samesite' => 'Lax'
            ]);
        }
        
        $this->user = null;
        $this->token = null;
    }

    /**
     * Logout from all devices
     */
    public function logoutAll() {
        if ($this->user) {
            $this->activityLog->log($this->user['id'], 'logout_all', 'auth', null, 'User logged out from all devices');
            $this->sessionTokenModel->deleteAllForUser($this->user['id']);
        }
        
        $this->logout();
    }

    /**
     * Check if user is logged in
     */
    public function isLoggedIn() {
        return $this->user !== null && $this->token !== null;
    }

    /**
     * Get current user
     */
    public function getUser() {
        return $this->user;
    }

    /**
     * Get current user ID
     */
    public function getUserId() {
        return $this->user ? $this->user['id'] : null;
    }

    /**
     * Get current user name
     */
    public function getUserName() {
        return $this->user ? $this->user['full_name'] : null;
    }

    /**
     * Get current token
     */
    public function getToken() {
        return $this->token;
    }

    /**
     * Get URL with token parameter
     */
    public function getUrlWithToken($url) {
        if (!$this->token) {
            return $url;
        }
        
        $separator = strpos($url, '?') !== false ? '&' : '?';
        return $url . $separator . 'token=' . $this->token;
    }

    /**
     * Check if user has permission
     */
    public function hasPermission($permission) {
        if (!$this->user) {
            return false;
        }
        return $this->adminUserModel->hasPermission($this->user, $permission);
    }

    /**
     * Check if current user is Super Admin
     */
    public function isSuperAdmin() {
        if (!$this->user) {
            return false;
        }
        return $this->user['role_id'] == ROLE_SUPERADMIN;
    }

    /**
     * Check if current user is ASDS
     */
    public function isASDS() {
        if (!$this->user) {
            return false;
        }
        return $this->user['role_id'] == ROLE_ASDS;
    }

    /**
     * Check if current user is a unit head (OSDS Chief, CID Chief, SGOD Chief)
     */
    public function isUnitHead() {
        if (!$this->user) {
            return false;
        }
        return isUnitHead($this->user['role_id']);
    }

    /**
     * Check if current user is a final approver (Superadmin or ASDS)
     */
    public function isApprover() {
        if (!$this->user) {
            return false;
        }
        return isApprover($this->user['role_id']);
    }

    /**
     * Check if current user can approve/recommend AT requests
     * Includes unit heads who can recommend
     */
    public function canActOnAT() {
        if (!$this->user) {
            return false;
        }
        return $this->isApprover() || $this->isUnitHead();
    }

    /**
     * Check if current user can approve Locator Slips
     * Includes OSDS_CHIEF in addition to ASDS and Superadmin
     */
    public function canApproveLS() {
        if (!$this->user) {
            return false;
        }
        return $this->isApprover() || $this->user['role_id'] == ROLE_OSDS_CHIEF;
    }

    /**
     * Check if current user is a regular employee
     */
    public function isEmployee() {
        if (!$this->user) {
            return false;
        }
        return isEmployee($this->user['role_id']);
    }

    /**
     * Get the role name for current user
     */
    public function getRoleName() {
        return $this->user ? $this->user['role_name'] : null;
    }

    /**
     * Get the employee office for current user
     */
    public function getEmployeeOffice() {
        return $this->user ? $this->user['employee_office'] : null;
    }

    /**
     * Require login - redirect to login page if not authenticated
     */
    public function requireLogin() {
        if (!$this->isLoggedIn()) {
            $currentUrl = $_SERVER['REQUEST_URI'];
            header('Location: ' . ADMIN_URL . '/login.php?redirect=' . urlencode($currentUrl));
            exit;
        }
    }

    /**
     * Require specific permission
     */
    public function requirePermission($permission) {
        $this->requireLogin();
        
        if (!$this->hasPermission($permission)) {
            header('HTTP/1.1 403 Forbidden');
            include __DIR__ . '/../admin/403.php';
            exit;
        }
    }

    /**
     * Require approver role
     */
    public function requireApprover() {
        $this->requireLogin();
        
        if (!$this->isApprover()) {
            header('HTTP/1.1 403 Forbidden');
            include __DIR__ . '/../admin/403.php';
            exit;
        }
    }

    /**
     * Generate CSRF token (stored per session token)
     */
    public function generateCsrfToken() {
        if (!$this->token) {
            return bin2hex(random_bytes(32));
        }
        
        // Use token + secret as CSRF base
        return hash('sha256', $this->token . 'ATLAS_CSRF_SECRET');
    }

    /**
     * Verify CSRF token
     */
    public function verifyCsrfToken($csrfToken) {
        return hash_equals($this->generateCsrfToken(), $csrfToken);
    }

    /**
     * Log activity
     */
    public function logActivity($actionType, $entityType, $entityId = null, $description = null, $oldValue = null, $newValue = null) {
        return $this->activityLog->log(
            $this->getUserId(),
            $actionType,
            $entityType,
            $entityId,
            $description,
            $oldValue,
            $newValue
        );
    }
}

/**
 * Helper function to get auth instance
 */
function auth() {
    return AdminAuth::getInstance();
}

/**
 * Helper function to get URL with token
 */
function url($path) {
    $auth = auth();
    return $auth->getUrlWithToken(ADMIN_URL . $path);
}/**
 * Helper function to get token for forms
 */
function tokenField() {
    $auth = auth();
    $token = $auth->getToken();
    if ($token) {
        return '<input type="hidden" name="_token" value="' . htmlspecialchars($token) . '">';
    }
    return '';
}