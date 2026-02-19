<?php
/**
 * EmailVerification Model
 * Handles registration email verification via OTP
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 *
 * Reuses patterns from PasswordReset model but stores temporary registration data
 * until the email is verified, then creates the real user account.
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';

class EmailVerification {
    private $db;

    const OTP_LENGTH = 6;
    const OTP_EXPIRY_MINUTES = 5;
    const MAX_OTP_INPUT_ATTEMPTS = 5;
    const MAX_OTP_REQUESTS_PER_HOUR = 3;
    const REQUEST_WINDOW_MINUTES = 60;

    public function __construct() {
        $this->db = Database::getInstance();
    }

    // ─── OTP Generation ──────────────────────────────────────────────

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

    // ─── Rate Limiting ───────────────────────────────────────────────

    /**
     * Count OTP requests within the current rate-limit window for an email.
     * Counts ALL records (pending, expired, invalidated, verified) — every button click that
     * generated an OTP counts toward the 3/hr limit.
     * Returns ['allowed' => bool, 'count' => int]
     */
    public function checkRequestLimit($email) {
        $windowStart = date('Y-m-d H:i:s', strtotime('-' . self::REQUEST_WINDOW_MINUTES . ' minutes'));

        $sql = "SELECT COUNT(*) AS cnt FROM email_verifications 
                WHERE email = ? AND created_at >= ?";
        $row = $this->db->query($sql, [$email, $windowStart])->fetch();
        $count = $row ? (int)$row['cnt'] : 0;

        return [
            'allowed' => $count < self::MAX_OTP_REQUESTS_PER_HOUR,
            'count'   => $count
        ];
    }

    // ─── Create / Invalidate ─────────────────────────────────────────

    /**
     * Invalidate all previous pending verifications for an email.
     */
    public function invalidatePrevious($email) {
        $sql = "UPDATE email_verifications SET status = 'invalidated' WHERE email = ? AND status = 'pending'";
        $this->db->query($sql, [$email]);
    }

    /**
     * Store temporary registration data and an OTP.
     *
     * @param array $formData  [email, full_name, password, employee_no, employee_position, office_id]
     * @return array  ['success'=>bool, 'otp'=>string|null, 'verification_id'=>int|null, 'error'=>string|null]
     */
    public function createVerification($formData) {
        $email = $formData['email'];

        // Rate-limit check
        $limit = $this->checkRequestLimit($email);
        if (!$limit['allowed']) {
            return [
                'success' => false,
                'error' => 'rate_limited',
                'message' => 'You have reached the maximum OTP requests (' . self::MAX_OTP_REQUESTS_PER_HOUR . ' per hour). Please try again later.'
            ];
        }

        // Invalidate any pending verifications for this email
        $this->invalidatePrevious($email);

        // Resolve office code from office_id
        $officeId = $formData['office_id'] ?? null;
        $employeeOffice = null;
        if ($officeId) {
            if (function_exists('getOfficeById')) {
                $office = getOfficeById($officeId);
                $employeeOffice = $office ? $office['office_code'] : null;
            }
        }

        // Generate and hash OTP
        $otp = $this->generateOTP();
        $otpHash = password_hash($otp, PASSWORD_DEFAULT);
        $expiresAt = date('Y-m-d H:i:s', strtotime('+' . self::OTP_EXPIRY_MINUTES . ' minutes'));

        // Hash the user's password for temporary storage
        $passwordHash = password_hash($formData['password'], PASSWORD_DEFAULT);

        $sql = "INSERT INTO email_verifications 
                (email, full_name, employee_no, employee_position, employee_office, office_id, password_hash, otp_hash, otp_expires_at, otp_request_window_start, ip_address)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, NOW(), ?)";

        $this->db->query($sql, [
            $email,
            $formData['full_name'],
            $formData['employee_no'] ?? null,
            $formData['employee_position'] ?? null,
            $employeeOffice,
            $officeId,
            $passwordHash,
            $otpHash,
            $expiresAt,
            $_SERVER['REMOTE_ADDR'] ?? null
        ]);

        return [
            'success'         => true,
            'otp'             => $otp,
            'verification_id' => $this->db->lastInsertId(),
            'expires_at'      => $expiresAt
        ];
    }

    // ─── OTP Verification ────────────────────────────────────────────

    /**
     * Get the latest pending (non-expired) verification for an email.
     */
    public function getLatestPending($email) {
        $sql = "SELECT * FROM email_verifications 
                WHERE email = ? AND status = 'pending' AND otp_expires_at > NOW()
                ORDER BY created_at DESC LIMIT 1";
        return $this->db->query($sql, [$email])->fetch();
    }

    /**
     * Verify the OTP code for a registration email.
     *
     * @return array ['success'=>bool, 'verification'=>array|null, 'error'=>string|null, ...]
     */
    public function verifyOTP($email, $otpCode) {
        $record = $this->getLatestPending($email);

        if (!$record) {
            // Check if expired
            $sqlExpired = "SELECT id FROM email_verifications 
                          WHERE email = ? AND status = 'pending' AND otp_expires_at <= NOW()
                          ORDER BY created_at DESC LIMIT 1";
            $expired = $this->db->query($sqlExpired, [$email])->fetch();

            if ($expired) {
                // Mark it expired
                $this->db->query("UPDATE email_verifications SET status = 'expired' WHERE id = ?", [$expired['id']]);
                return ['success' => false, 'error' => 'expired', 'message' => 'Your OTP has expired. Please go back and request a new one.'];
            }
            return ['success' => false, 'error' => 'invalid', 'message' => 'No pending verification found. Please register again.'];
        }

        // Check attempt limit
        if ((int)$record['otp_attempts'] >= self::MAX_OTP_INPUT_ATTEMPTS) {
            $this->db->query("UPDATE email_verifications SET status = 'invalidated' WHERE id = ?", [$record['id']]);
            return [
                'success'  => false,
                'error'    => 'attempts_exhausted',
                'message'  => 'You have exceeded the maximum OTP input attempts. Please register again.',
                'redirect' => true
            ];
        }

        // Verify hash
        if (!password_verify($otpCode, $record['otp_hash'])) {
            // Increment attempt counter
            $newAttempts = (int)$record['otp_attempts'] + 1;
            $this->db->query("UPDATE email_verifications SET otp_attempts = ? WHERE id = ?", [$newAttempts, $record['id']]);

            $remaining = self::MAX_OTP_INPUT_ATTEMPTS - $newAttempts;

            if ($remaining <= 0) {
                $this->db->query("UPDATE email_verifications SET status = 'invalidated' WHERE id = ?", [$record['id']]);
                return [
                    'success'  => false,
                    'error'    => 'attempts_exhausted',
                    'message'  => 'You have exceeded the maximum OTP input attempts (5). Please register again.',
                    'redirect' => true
                ];
            }

            return [
                'success'   => false,
                'error'     => 'wrong_otp',
                'message'   => 'Invalid OTP code. ' . $remaining . ' attempt' . ($remaining !== 1 ? 's' : '') . ' remaining.',
                'remaining' => $remaining
            ];
        }

        // OTP is correct — mark as verified
        $this->db->query("UPDATE email_verifications SET status = 'verified' WHERE id = ?", [$record['id']]);
        $record['status'] = 'verified';

        return [
            'success'      => true,
            'verification' => $record
        ];
    }

    // ─── Account Creation ────────────────────────────────────────────

    /**
     * Create the real user account from the verified temporary record.
     * Bypasses Superadmin manual approval (status = 'active').
     *
     * @param array $verification  A row from email_verifications
     * @return array ['success'=>bool, 'user_id'=>int|null, 'error'=>string|null]
     */
    public function createUserFromVerification($verification) {
        // Double-check status
        if ($verification['status'] !== 'verified') {
            return ['success' => false, 'error' => 'Verification record is not in verified state.'];
        }

        // Check if email already registered as user (race condition guard)
        $existing = $this->db->query("SELECT id FROM admin_users WHERE email = ?", [$verification['email']])->fetch();
        if ($existing) {
            return ['success' => false, 'error' => 'An account with this email already exists.'];
        }

        $sql = "INSERT INTO admin_users 
                (email, password_hash, full_name, employee_no, employee_position, employee_office, office_id, role_id, status, is_active)
                VALUES (?, ?, ?, ?, ?, ?, ?, ?, 'active', 1)";

        $this->db->query($sql, [
            $verification['email'],
            $verification['password_hash'],
            $verification['full_name'],
            $verification['employee_no'],
            $verification['employee_position'],
            $verification['employee_office'],
            $verification['office_id'],
            ROLE_USER
        ]);

        return [
            'success' => true,
            'user_id' => $this->db->lastInsertId()
        ];
    }

    // ─── Cleanup ─────────────────────────────────────────────────────

    /**
     * Expire all overdue pending verifications (housekeeping).
     */
    public function cleanupExpired() {
        $sql = "UPDATE email_verifications SET status = 'expired' WHERE status = 'pending' AND otp_expires_at <= NOW()";
        $this->db->query($sql);
    }
}
