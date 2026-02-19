<?php
/**
 * PasswordReset Model
 * Handles forgot password OTP generation, validation, and attempt tracking
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class PasswordReset {
    private $db;
    const MAX_ATTEMPTS = 3;
    const OTP_EXPIRY_MINUTES = 5;
    const OTP_LENGTH = 6;
    const MAX_OTP_INPUT_ATTEMPTS = 5;
    const MAX_RESEND_PER_HOUR = 3;
    const RESEND_WINDOW_MINUTES = 60;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    /**
     * Get the number of reset attempts for a user
     */
    public function getAttemptCount($userId) {
        $sql = "SELECT attempt_count, is_blocked FROM password_reset_attempts WHERE user_id = ?";
        $row = $this->db->query($sql, [$userId])->fetch();
        if ($row) {
            return (int)$row['attempt_count'];
        }
        return 0;
    }

    /**
     * Check if user is blocked from requesting password resets
     */
    public function isBlocked($userId) {
        $sql = "SELECT is_blocked FROM password_reset_attempts WHERE user_id = ?";
        $row = $this->db->query($sql, [$userId])->fetch();
        return $row && (int)$row['is_blocked'] === 1;
    }

    /**
     * Increment the attempt count for a user
     * Returns the new attempt count
     */
    public function incrementAttempt($userId) {
        // Upsert: insert or update
        // Note: MySQL evaluates SET left-to-right, so attempt_count already holds the
        // new value by the time the IF runs — no extra +1 needed.
        $sql = "INSERT INTO password_reset_attempts (user_id, attempt_count, last_attempt_at)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                    attempt_count = attempt_count + 1,
                    last_attempt_at = NOW(),
                    is_blocked = IF(attempt_count >= ?, 1, 0)";
        $this->db->query($sql, [$userId, self::MAX_ATTEMPTS]);

        return $this->getAttemptCount($userId);
    }

    /**
     * Generate a numeric OTP code
     */
    private function generateOTP() {
        $otp = '';
        for ($i = 0; $i < self::OTP_LENGTH; $i++) {
            $otp .= random_int(0, 9);
        }
        return $otp;
    }

    /**
     * Create a password reset request with OTP
     * Returns the plain OTP on success, false on failure
     */
    public function createRequest($userId, $email) {
        // Check if blocked
        if ($this->isBlocked($userId)) {
            return ['success' => false, 'error' => 'blocked'];
        }

        // Check attempt count before incrementing
        $currentCount = $this->getAttemptCount($userId);
        if ($currentCount >= self::MAX_ATTEMPTS) {
            // Mark as blocked
            $sql = "UPDATE password_reset_attempts SET is_blocked = 1 WHERE user_id = ?";
            $this->db->query($sql, [$userId]);
            return ['success' => false, 'error' => 'blocked'];
        }

        // Invalidate any previous unused OTPs for this user
        $this->invalidatePreviousOTPs($userId);

        // Reset OTP input attempts for the new OTP
        $this->resetOTPInputAttempts($userId);

        // Generate OTP
        $otp = $this->generateOTP();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));

        // Increment attempt count
        $newCount = $this->incrementAttempt($userId);

        // Store the reset request
        $sql = "INSERT INTO password_resets (user_id, email, otp_code, otp_hash, expires_at, request_count, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?)";
        $this->db->query($sql, [
            $userId,
            $email,
            '', // Don't store plain OTP in DB
            $otpHash,
            $expiresAt,
            $newCount,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        $resetId = $this->db->lastInsertId();

        return [
            'success' => true,
            'otp' => $otp,
            'reset_id' => $resetId,
            'expires_at' => $expiresAt,
            'attempts_remaining' => self::MAX_ATTEMPTS - $newCount
        ];
    }

    /**
     * Invalidate all previous unused OTPs for a user
     */
    private function invalidatePreviousOTPs($userId) {
        $sql = "UPDATE password_resets SET is_used = 1 WHERE user_id = ? AND is_used = 0";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Verify an OTP code for a given email
     * Returns user_id on success, false on failure with error info
     */
    public function verifyOTP($email, $otpCode) {
        // Get the latest unused, non-expired reset request for the email
        $sql = "SELECT pr.*, pra.is_blocked 
                FROM password_resets pr
                LEFT JOIN password_reset_attempts pra ON pr.user_id = pra.user_id
                WHERE pr.email = ? 
                  AND pr.is_used = 0 
                  AND pr.expires_at > NOW()
                ORDER BY pr.created_at DESC 
                LIMIT 1";
        $reset = $this->db->query($sql, [$email])->fetch();

        if (!$reset) {
            // Check if there's an expired one
            $sqlExpired = "SELECT id FROM password_resets 
                          WHERE email = ? AND is_used = 0 AND expires_at <= NOW()
                          ORDER BY created_at DESC LIMIT 1";
            $expired = $this->db->query($sqlExpired, [$email])->fetch();
            
            if ($expired) {
                return ['success' => false, 'error' => 'expired', 'message' => 'Your OTP has expired. Please request a new one.'];
            }
            return ['success' => false, 'error' => 'invalid', 'message' => 'Invalid or expired OTP. Please try again.'];
        }

        // Verify OTP hash
        if (!password_verify($otpCode, $reset['otp_hash'])) {
            return ['success' => false, 'error' => 'invalid', 'message' => 'Invalid OTP code. Please check and try again.'];
        }

        // OTP is valid - generate a temporary token for the reset page
        $resetToken = bin2hex(random_bytes(32));
        $resetTokenHash = password_hash($resetToken, PASSWORD_DEFAULT);
        $tokenExpiry = date('Y-m-d H:i:s', strtotime('+10 minutes'));

        // Mark OTP as used and store the reset token
        $sql = "UPDATE password_resets 
                SET is_used = 1, 
                    otp_code = ? 
                WHERE id = ?";
        $this->db->query($sql, [$resetTokenHash, $reset['id']]);

        // Create a new entry for the reset token
        $sql = "INSERT INTO password_resets (user_id, email, otp_code, otp_hash, expires_at, is_used, request_count, ip_address)
                VALUES (?, ?, 'RST_TKN', ?, ?, 0, ?, ?)";
        $this->db->query($sql, [
            $reset['user_id'],
            $email,
            $resetTokenHash,
            $tokenExpiry,
            $reset['request_count'],
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        return [
            'success' => true,
            'user_id' => $reset['user_id'],
            'reset_token' => $resetToken,
            'email' => $email
        ];
    }

    /**
     * Validate a reset token for the password change step
     */
    public function validateResetToken($email, $resetToken) {
        $sql = "SELECT * FROM password_resets 
                WHERE email = ? 
                  AND otp_code = 'RST_TKN'
                  AND is_used = 0 
                  AND expires_at > NOW()
                ORDER BY created_at DESC 
                LIMIT 1";
        $reset = $this->db->query($sql, [$email])->fetch();

        if (!$reset) {
            return false;
        }

        if (!password_verify($resetToken, $reset['otp_hash'])) {
            return false;
        }

        return $reset;
    }

    /**
     * Reset the user's password
     */
    public function resetPassword($email, $resetToken, $newPassword) {
        // Validate the reset token
        $reset = $this->validateResetToken($email, $resetToken);
        if (!$reset) {
            return ['success' => false, 'error' => 'Invalid or expired reset token. Please start over.'];
        }

        // Hash the new password
        $passwordHash = password_hash($newPassword, PASSWORD_DEFAULT);

        // Update the user's password
        $sql = "UPDATE admin_users SET password_hash = ? WHERE id = ? AND email = ?";
        $this->db->query($sql, [$passwordHash, $reset['user_id'], $email]);

        // Invalidate the reset token
        $sql = "UPDATE password_resets SET is_used = 1 WHERE id = ?";
        $this->db->query($sql, [$reset['id']]);

        // Invalidate all other unused tokens for this user
        $this->invalidatePreviousOTPs($reset['user_id']);

        // NOTE: Attempt counter is NOT auto-reset on password change.
        // Only a Superadmin can reset the forgot password rate limit.

        return [
            'success' => true,
            'user_id' => $reset['user_id']
        ];
    }

    /**
     * Record a verification page access (even if no OTP is sent)
     */
    public function recordVerificationAccess($userId) {
        $sql = "INSERT INTO password_reset_attempts (user_id, verification_access_count, last_attempt_at)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                    verification_access_count = verification_access_count + 1,
                    last_attempt_at = NOW()";
        $this->db->query($sql, [$userId]);
        return $this->getVerificationAccessCount($userId);
    }

    /**
     * Get verification access count for a user
     */
    public function getVerificationAccessCount($userId) {
        $sql = "SELECT verification_access_count FROM password_reset_attempts WHERE user_id = ?";
        $row = $this->db->query($sql, [$userId])->fetch();
        return $row ? (int)$row['verification_access_count'] : 0;
    }

    /**
     * Increment OTP input attempt counter
     * Returns ['count' => int, 'exhausted' => bool]
     */
    public function incrementOTPInputAttempt($userId) {
        $sql = "INSERT INTO password_reset_attempts (user_id, otp_input_attempts, last_attempt_at)
                VALUES (?, 1, NOW())
                ON DUPLICATE KEY UPDATE 
                    otp_input_attempts = otp_input_attempts + 1,
                    last_attempt_at = NOW()";
        $this->db->query($sql, [$userId]);

        $count = $this->getOTPInputAttempts($userId);
        $exhausted = $count >= self::MAX_OTP_INPUT_ATTEMPTS;

        if ($exhausted) {
            // Invalidate current OTP
            $this->invalidatePreviousOTPs($userId);
        }

        return ['count' => $count, 'exhausted' => $exhausted, 'remaining' => max(0, self::MAX_OTP_INPUT_ATTEMPTS - $count)];
    }

    /**
     * Get OTP input attempt count for a user
     */
    public function getOTPInputAttempts($userId) {
        $sql = "SELECT otp_input_attempts FROM password_reset_attempts WHERE user_id = ?";
        $row = $this->db->query($sql, [$userId])->fetch();
        return $row ? (int)$row['otp_input_attempts'] : 0;
    }

    /**
     * Reset OTP input attempt counter (called when new OTP is generated)
     */
    public function resetOTPInputAttempts($userId) {
        $sql = "UPDATE password_reset_attempts SET otp_input_attempts = 0 WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
    }

    /**
     * Check if OTP input attempts are exhausted
     */
    public function isOTPInputExhausted($userId) {
        return $this->getOTPInputAttempts($userId) >= self::MAX_OTP_INPUT_ATTEMPTS;
    }

    /**
     * Check and manage OTP resend limits (max 3 per hour)
     * Returns ['allowed' => bool, 'count' => int, 'blocked_until' => string|null]
     */
    public function checkResendLimit($userId) {
        $sql = "SELECT resend_count, resend_window_start, resend_blocked FROM password_reset_attempts WHERE user_id = ?";
        $row = $this->db->query($sql, [$userId])->fetch();

        if (!$row) {
            return ['allowed' => true, 'count' => 0, 'blocked_until' => null];
        }

        // Check if resend window has expired (1 hour) - auto-eligible for reset
        if ($row['resend_window_start']) {
            $windowStart = new \DateTime($row['resend_window_start']);
            $now = new \DateTime();
            $diff = $now->getTimestamp() - $windowStart->getTimestamp();
            
            if ($diff >= self::RESEND_WINDOW_MINUTES * 60) {
                // Window expired — reset resend counters (time eligibility met)
                $sql = "UPDATE password_reset_attempts SET resend_count = 0, resend_blocked = 0, resend_window_start = NULL WHERE user_id = ?";
                $this->db->query($sql, [$userId]);
                return ['allowed' => true, 'count' => 0, 'blocked_until' => null];
            }
        }

        if ((int)$row['resend_blocked'] === 1) {
            $blockedUntil = null;
            if ($row['resend_window_start']) {
                $windowStart = new \DateTime($row['resend_window_start']);
                $windowStart->modify('+' . self::RESEND_WINDOW_MINUTES . ' minutes');
                $blockedUntil = $windowStart->format('Y-m-d H:i:s');
            }
            return ['allowed' => false, 'count' => (int)$row['resend_count'], 'blocked_until' => $blockedUntil];
        }

        if ((int)$row['resend_count'] >= self::MAX_RESEND_PER_HOUR) {
            // Should already be blocked, enforce it
            $sql = "UPDATE password_reset_attempts SET resend_blocked = 1 WHERE user_id = ?";
            $this->db->query($sql, [$userId]);
            $blockedUntil = null;
            if ($row['resend_window_start']) {
                $windowStart = new \DateTime($row['resend_window_start']);
                $windowStart->modify('+' . self::RESEND_WINDOW_MINUTES . ' minutes');
                $blockedUntil = $windowStart->format('Y-m-d H:i:s');
            }
            return ['allowed' => false, 'count' => (int)$row['resend_count'], 'blocked_until' => $blockedUntil];
        }

        return ['allowed' => true, 'count' => (int)$row['resend_count'], 'blocked_until' => null];
    }

    /**
     * Increment the resend counter
     */
    public function incrementResendCount($userId) {
        // Start a new window if none exists
        // Note: MySQL evaluates SET left-to-right, so resend_count already holds the
        // new value by the time the IF runs — no extra +1 needed.
        $sql = "INSERT INTO password_reset_attempts (user_id, resend_count, resend_window_start, last_attempt_at)
                VALUES (?, 1, NOW(), NOW())
                ON DUPLICATE KEY UPDATE 
                    resend_count = resend_count + 1,
                    resend_window_start = IF(resend_window_start IS NULL, NOW(), resend_window_start),
                    last_attempt_at = NOW(),
                    resend_blocked = IF(resend_count >= ?, 1, 0)";
        $this->db->query($sql, [$userId, self::MAX_RESEND_PER_HOUR]);
        
        return $this->checkResendLimit($userId);
    }

    /**
     * Admin reset: OTP resend limit for a user
     */
    public function adminResetResendLimit($userId) {
        $sql = "UPDATE password_reset_attempts SET resend_count = 0, resend_blocked = 0, resend_window_start = NULL WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return true;
    }

    /**
     * Admin reset: OTP input attempts for a user
     */
    public function adminResetOTPInputAttempts($userId) {
        $sql = "UPDATE password_reset_attempts SET otp_input_attempts = 0 WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return true;
    }

    /**
     * Admin reset: Verification access count for a user
     */
    public function adminResetVerificationCount($userId) {
        $sql = "UPDATE password_reset_attempts SET verification_access_count = 0 WHERE user_id = ?";
        $this->db->query($sql, [$userId]);
        return true;
    }

    /**
     * Get all users with their forgot password attempt status
     * For Superadmin management page
     */
    public function getAllAttempts($filters = [], $limit = 15, $offset = 0) {
        $sql = "SELECT pra.*, au.full_name, au.email, au.status as user_status,
                       ar.role_name,
                       pra.verification_access_count, pra.otp_input_attempts,
                       pra.resend_count, pra.resend_blocked, pra.resend_window_start,
                       (SELECT COUNT(*) FROM password_resets pr WHERE pr.user_id = pra.user_id) as total_requests,
                       (SELECT MAX(pr.created_at) FROM password_resets pr WHERE pr.user_id = pra.user_id) as last_request_at
                FROM password_reset_attempts pra
                JOIN admin_users au ON pra.user_id = au.id
                JOIN admin_roles ar ON au.role_id = ar.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (au.full_name LIKE ? OR au.email LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (isset($filters['blocked']) && $filters['blocked'] !== '') {
            $sql .= " AND pra.is_blocked = ?";
            $params[] = (int)$filters['blocked'];
        }

        $sql .= " ORDER BY pra.is_blocked DESC, pra.attempt_count DESC, pra.last_attempt_at DESC";
        $sql .= " LIMIT " . (int)$limit . " OFFSET " . (int)$offset;

        return $this->db->query($sql, $params)->fetchAll();
    }

    /**
     * Count all users with reset attempts (for pagination)
     */
    public function getAttemptsCount($filters = []) {
        $sql = "SELECT COUNT(*) as total
                FROM password_reset_attempts pra
                JOIN admin_users au ON pra.user_id = au.id
                WHERE 1=1";
        $params = [];

        if (!empty($filters['search'])) {
            $sql .= " AND (au.full_name LIKE ? OR au.email LIKE ?)";
            $term = '%' . $filters['search'] . '%';
            $params[] = $term;
            $params[] = $term;
        }

        if (isset($filters['blocked']) && $filters['blocked'] !== '') {
            $sql .= " AND pra.is_blocked = ?";
            $params[] = (int)$filters['blocked'];
        }

        return (int)$this->db->query($sql, $params)->fetch()['total'];
    }

    /**
     * Get summary stats for the management dashboard
     */
    public function getAttemptStats() {
        $sql = "SELECT 
                    COUNT(*) as total_users_with_attempts,
                    SUM(CASE WHEN is_blocked = 1 THEN 1 ELSE 0 END) as blocked_users,
                    SUM(CASE WHEN is_blocked = 0 THEN 1 ELSE 0 END) as active_users,
                    SUM(attempt_count) as total_attempts,
                    SUM(verification_access_count) as total_verification_accesses,
                    SUM(otp_input_attempts) as total_otp_input_attempts,
                    SUM(resend_count) as total_resends,
                    SUM(CASE WHEN resend_blocked = 1 THEN 1 ELSE 0 END) as resend_blocked_users
                FROM password_reset_attempts";
        return $this->db->query($sql)->fetch();
    }

    /**
     * Reset the rate limit for a specific user (Superadmin only)
     * Deletes the attempt record and all used password_resets rows
     */
    public function adminResetLimit($userId) {
        // Delete the attempt tracker
        $sql = "DELETE FROM password_reset_attempts WHERE user_id = ?";
        $this->db->query($sql, [$userId]);

        // Invalidate all pending OTPs
        $this->invalidatePreviousOTPs($userId);

        return true;
    }

    /**
     * Clean up expired OTPs (can be called periodically)
     */
    public function cleanupExpired() {
        $sql = "UPDATE password_resets SET is_used = 1 WHERE expires_at < NOW() AND is_used = 0";
        $this->db->query($sql);
    }
}
