<?php
/**
 * Forgot Password API Endpoint
 * SDO ATLAS - Schools Division Office Authority to Travel and Locator Approval System
 * 
 * Handles three actions:
 *   - request: Validate email, generate OTP, send via email
 *   - verify:  Validate OTP, return reset token
 *   - reset:   Validate reset token, update password
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/admin_config.php';
require_once __DIR__ . '/../../config/mail_config.php';
require_once __DIR__ . '/../../models/AdminUser.php';
require_once __DIR__ . '/../../models/PasswordReset.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

// Only accept POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

// Parse JSON body
$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$action = $input['action'];
$userModel = new AdminUser();
$resetModel = new PasswordReset();
$activityLog = new ActivityLog();

switch ($action) {
    case 'request':
        handleRequestOTP($input, $userModel, $resetModel, $activityLog);
        break;

    case 'verify':
        handleVerifyOTP($input, $userModel, $resetModel, $activityLog);
        break;

    case 'reset':
        handleResetPassword($input, $resetModel, $activityLog);
        break;

    case 'track_access':
        handleTrackAccess($input, $userModel, $resetModel);
        break;

    case 'resend':
        handleResendOTP($input, $userModel, $resetModel, $activityLog);
        break;

    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

/**
 * Handle verification page access tracking
 */
function handleTrackAccess($input, $userModel, $resetModel) {
    $email = trim($input['email'] ?? '');
    
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email.']);
        return;
    }

    $user = $userModel->getByEmail($email);
    if (!$user) {
        echo json_encode(['success' => true, 'count' => 0]);
        return;
    }

    $count = $resetModel->recordVerificationAccess($user['id']);
    echo json_encode(['success' => true, 'count' => $count]);
}

/**
 * Handle OTP request
 */
function handleRequestOTP($input, $userModel, $resetModel, $activityLog) {
    $email = trim($input['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        return;
    }

    // Check if user exists - validate email is registered in the system
    $user = $userModel->getByEmail($email);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'The email address you provided is not registered in the system. Please check and try again.']);
        return;
    }

    // Check if account is active
    if ($user['status'] !== 'active' || !$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'This account is not active. Please contact the administrator.']);
        return;
    }

    // Check if blocked (permanent block until admin reset)
    if ($resetModel->isBlocked($user['id'])) {
        echo json_encode([
            'success' => false,
            'blocked' => true,
            'message' => 'You have reached the maximum number of password reset attempts. Please contact the ICT unit for assistance.'
        ]);
        return;
    }

    // Check 3-per-hour rate limit (shared between initial request and resend)
    $resendCheck = $resetModel->checkResendLimit($user['id']);
    if (!$resendCheck['allowed']) {
        $msg = 'You have reached the maximum OTP requests (3 per hour).';
        if ($resendCheck['blocked_until']) {
            $until = new DateTime($resendCheck['blocked_until']);
            $msg .= ' Try again after ' . $until->format('g:i A') . '.';
        }
        echo json_encode([
            'success' => false,
            'resend_blocked' => true,
            'message' => $msg,
            'blocked_until' => $resendCheck['blocked_until']
        ]);
        return;
    }

    // Create OTP request
    $result = $resetModel->createRequest($user['id'], $email);

    if (!$result['success']) {
        if ($result['error'] === 'blocked') {
            echo json_encode([
                'success' => false,
                'blocked' => true,
                'message' => 'You have reached the maximum number of password reset attempts. Please contact the ICT unit for assistance.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
        }
        return;
    }

    // Increment the 3-per-hour resend counter (counts initial request too)
    $resendResult = $resetModel->incrementResendCount($user['id']);

    // Send OTP via email
    $emailSent = sendOTPEmail($email, $user['full_name'], $result['otp']);

    if (!$emailSent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
        return;
    }

    // Log the action
    $activityLog->log(
        $user['id'],
        'password_reset_request',
        'user',
        $user['id'],
        'Password reset OTP requested. OTP request ' . $resendResult['count'] . '/' . PasswordReset::MAX_RESEND_PER_HOUR . ' this hour.'
    );

    echo json_encode([
        'success' => true,
        'message' => 'OTP has been sent to your email address. Please check your inbox.',
        'attempts_remaining' => $result['attempts_remaining'],
        'otp_requests_remaining' => max(0, PasswordReset::MAX_RESEND_PER_HOUR - $resendResult['count'])
    ]);
}

/**
 * Handle OTP verification
 */
function handleVerifyOTP($input, $userModel, $resetModel, $activityLog) {
    $email = trim($input['email'] ?? '');
    $otp = trim($input['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        return;
    }

    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid 6-digit OTP.']);
        return;
    }

    // Get user for OTP input attempt tracking
    $user = $userModel->getByEmail($email);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'Invalid email.']);
        return;
    }

    // Check if OTP input attempts are already exhausted
    if ($resetModel->isOTPInputExhausted($user['id'])) {
        echo json_encode([
            'success' => false,
            'error' => 'otp_attempts_exhausted',
            'message' => 'You have exceeded the maximum OTP input attempts. Please request a new OTP.',
            'redirect' => true
        ]);
        return;
    }

    $result = $resetModel->verifyOTP($email, $otp);

    if ($result['success']) {
        // Reset OTP input attempts on successful verification
        $resetModel->resetOTPInputAttempts($user['id']);

        // Log the verification
        $activityLog->log(
            $result['user_id'],
            'password_reset_otp_verified',
            'user',
            $result['user_id'],
            'Password reset OTP verified successfully.'
        );

        echo json_encode([
            'success' => true,
            'message' => 'OTP verified successfully.',
            'reset_token' => $result['reset_token']
        ]);
    } else {
        // Increment OTP input attempt on failure
        $attemptResult = $resetModel->incrementOTPInputAttempt($user['id']);
        
        $response = [
            'success' => false,
            'error' => $result['error'],
            'message' => $result['message'],
            'otp_attempts_remaining' => $attemptResult['remaining']
        ];

        if ($attemptResult['exhausted']) {
            $response['error'] = 'otp_attempts_exhausted';
            $response['message'] = 'You have exceeded the maximum OTP input attempts (5). Your current OTP has been invalidated. Please request a new OTP.';
            $response['redirect'] = true;

            $activityLog->log(
                $user['id'],
                'otp_input_attempts_exhausted',
                'user',
                $user['id'],
                'OTP input attempts exhausted (5/5). Current OTP invalidated.'
            );
        } else {
            $response['message'] .= ' (' . $attemptResult['remaining'] . ' attempt' . ($attemptResult['remaining'] !== 1 ? 's' : '') . ' remaining)';
        }

        echo json_encode($response);
    }
}

/**
 * Handle password reset
 */
function handleResetPassword($input, $resetModel, $activityLog) {
    $email = trim($input['email'] ?? '');
    $resetToken = trim($input['reset_token'] ?? '');
    $newPassword = $input['new_password'] ?? '';

    if (empty($email) || empty($resetToken) || empty($newPassword)) {
        echo json_encode(['success' => false, 'message' => 'All fields are required.']);
        return;
    }

    // Validate password strength
    if (strlen($newPassword) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        return;
    }

    if (!preg_match('/[A-Z]/', $newPassword) || !preg_match('/[a-z]/', $newPassword) || !preg_match('/[0-9]/', $newPassword)) {
        echo json_encode(['success' => false, 'message' => 'Password must contain uppercase, lowercase, and a number.']);
        return;
    }

    $result = $resetModel->resetPassword($email, $resetToken, $newPassword);

    if ($result['success']) {
        // Log the successful reset
        $activityLog->log(
            $result['user_id'],
            'password_reset_completed',
            'user',
            $result['user_id'],
            'Password reset completed successfully via forgot password flow.'
        );

        echo json_encode([
            'success' => true,
            'message' => 'Your password has been reset successfully. You can now log in with your new password.'
        ]);
    } else {
        echo json_encode([
            'success' => false,
            'message' => $result['error'] ?? 'Failed to reset password. The reset link may have expired. Please start over.'
        ]);
    }
}

/**
 * Handle OTP resend with rate limiting (max 3 per hour)
 */
function handleResendOTP($input, $userModel, $resetModel, $activityLog) {
    $email = trim($input['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        return;
    }

    $user = $userModel->getByEmail($email);
    if (!$user) {
        echo json_encode(['success' => false, 'message' => 'The email address you provided is not registered in the system.']);
        return;
    }

    if ($user['status'] !== 'active' || !$user['is_active']) {
        echo json_encode(['success' => false, 'message' => 'This account is not active. Please contact the administrator.']);
        return;
    }

    // Check if blocked from password resets entirely
    if ($resetModel->isBlocked($user['id'])) {
        echo json_encode([
            'success' => false,
            'blocked' => true,
            'message' => 'You have reached the maximum number of password reset attempts. Please contact the ICT unit for assistance.'
        ]);
        return;
    }

    // Check resend rate limit
    $resendCheck = $resetModel->checkResendLimit($user['id']);
    if (!$resendCheck['allowed']) {
        $msg = 'You have reached the maximum OTP resend limit (3 per hour).';
        if ($resendCheck['blocked_until']) {
            $until = new DateTime($resendCheck['blocked_until']);
            $msg .= ' Try again after ' . $until->format('g:i A') . '.';
        }
        echo json_encode([
            'success' => false,
            'resend_blocked' => true,
            'message' => $msg,
            'blocked_until' => $resendCheck['blocked_until']
        ]);
        return;
    }

    // Create a new OTP request (this also increments the main attempt counter)
    $result = $resetModel->createRequest($user['id'], $email);

    if (!$result['success']) {
        if ($result['error'] === 'blocked') {
            echo json_encode([
                'success' => false,
                'blocked' => true,
                'message' => 'You have reached the maximum number of password reset attempts. Please contact the ICT unit for assistance.'
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Failed to generate OTP. Please try again.']);
        }
        return;
    }

    // Increment resend counter
    $resendResult = $resetModel->incrementResendCount($user['id']);

    // Send OTP via email
    $emailSent = sendOTPEmail($email, $user['full_name'], $result['otp']);

    if (!$emailSent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
        return;
    }

    $activityLog->log(
        $user['id'],
        'password_reset_resend',
        'user',
        $user['id'],
        'OTP resent. Resend count: ' . $resendResult['count'] . '/' . PasswordReset::MAX_RESEND_PER_HOUR . '. Main attempt ' . (PasswordReset::MAX_ATTEMPTS - $result['attempts_remaining'] + 1) . '/' . PasswordReset::MAX_ATTEMPTS . '.'
    );

    echo json_encode([
        'success' => true,
        'message' => 'New OTP sent! Check your email.',
        'attempts_remaining' => $result['attempts_remaining'],
        'resend_count' => $resendResult['count'],
        'resend_remaining' => max(0, PasswordReset::MAX_RESEND_PER_HOUR - $resendResult['count'])
    ]);
}

/**
 * Send OTP via email using PHPMailer
 */
function sendOTPEmail($email, $fullName, $otp) {
    if (!MAIL_ENABLED) {
        // If mail is disabled, log the OTP (for development only)
        error_log("SDO ATLAS - Password Reset OTP for {$email}: {$otp}");
        return true;
    }

    try {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        // SMTP settings
        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = MAIL_CHARSET;

        // Sender — must match SMTP credentials for Gmail
        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO ?: MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = 'SDO ATLAS - Password Reset OTP';

        // Embed logos as inline attachments for the email
        $sdoLogoPath = __DIR__ . '/../assets/logos/sdo-logo.jpg';
        $bpLogoPath = __DIR__ . '/../assets/logos/bagongpilpinas-logo.png';
        
        if (file_exists($sdoLogoPath)) {
            $mail->addEmbeddedImage($sdoLogoPath, 'sdo-logo', 'sdo-logo.jpg');
        }
        if (file_exists($bpLogoPath)) {
            $mail->addEmbeddedImage($bpLogoPath, 'bp-logo', 'bagongpilpinas-logo.png');
        }

        // Build email body
        $mail->Body = buildOTPEmailHTML($fullName, $otp);
        $mail->AltBody = buildOTPEmailText($fullName, $otp);

        $mail->send();
        return true;

    } catch (Exception $e) {
        error_log("SDO ATLAS - Failed to send password reset email to {$email}: " . $e->getMessage());
        return false;
    }
}

/**
 * Build HTML email body for OTP
 */
function buildOTPEmailHTML($fullName, $otp) {
    $expiry = PasswordReset::OTP_EXPIRY_MINUTES;
    return <<<HTML
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
</head>
<body style="margin:0; padding:0; font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; background-color: #f0f4f8;">
    <table cellpadding="0" cellspacing="0" width="100%" style="max-width: 520px; margin: 20px auto; background: #ffffff; border-radius: 12px; overflow: hidden; box-shadow: 0 2px 12px rgba(0,0,0,0.08);">
        <!-- Header -->
        <tr>
            <td style="background: linear-gradient(135deg, #0f4c75, #1b6ca8); padding: 24px 32px;">
                <table cellpadding="0" cellspacing="0" width="100%" style="border-collapse: collapse;">
                    <tr>
                        <td style="width: 70px; text-align: left; vertical-align: middle;">
                            <img src="cid:sdo-logo" alt="SDO Logo" style="width: 60px; height: 60px; border-radius: 50%; object-fit: cover;">
                        </td>
                        <td style="text-align: center; vertical-align: middle; padding: 0 10px;">
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700;">SDO ATLAS</h1>
                            <p style="margin: 4px 0 0; color: rgba(255,255,255,0.85); font-size: 12px; line-height: 1.4;">
                                The Schools Division Office of San Pedro City<br>
                                Password Reset Request
                            </p>
                        </td>
                        <td style="width: 70px; text-align: right; vertical-align: middle;">
                            <img src="cid:bp-logo" alt="Bagong Pilipinas" style="width: 60px; height: 60px; object-fit: contain;">
                        </td>
                    </tr>
                </table>
            </td>
        </tr>
        <!-- Body -->
        <tr>
            <td style="padding: 32px;">
                <p style="margin: 0 0 16px; color: #333; font-size: 15px;">Hello <strong>{$fullName}</strong>,</p>
                <p style="margin: 0 0 24px; color: #555; font-size: 14px; line-height: 1.6;">
                    We received a request to reset your password for your SDO ATLAS account. Use the OTP code below to complete the process:
                </p>
                <!-- OTP Box -->
                <div style="text-align: center; margin: 24px 0;">
                    <div style="display: inline-block; background: #f0f7ff; border: 2px dashed #1b6ca8; border-radius: 10px; padding: 16px 40px;">
                        <p style="margin: 0 0 4px; color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Your OTP Code</p>
                        <p style="margin: 0; color: #0f4c75; font-size: 32px; font-weight: 800; letter-spacing: 8px;">{$otp}</p>
                    </div>
                </div>
                <p style="margin: 16px 0; color: #ef4444; font-size: 13px; text-align: center; font-weight: 500;">
                    ⏱ This code expires in {$expiry} minutes.
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0;">
                <p style="margin: 0 0 8px; color: #888; font-size: 13px;">
                    <strong>Security Notice:</strong>
                </p>
                <ul style="margin: 0; padding: 0 0 0 20px; color: #888; font-size: 13px; line-height: 1.8;">
                    <li>Do not share this OTP with anyone.</li>
                    <li>If you did not request this, please ignore this email.</li>
                    <li>Your password will not change unless you complete the reset process.</li>
                </ul>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #eee;">
                <p style="margin: 0; color: #999; font-size: 12px;">
                    SDO ATLAS - Department of Education<br>
                    Schools Division Office of San Pedro City<br>
                    <a href="mailto:ict.sanpedrocity@deped.gov.ph" style="color: #1b6ca8;">ict.sanpedrocity@deped.gov.ph</a>
                </p>
            </td>
        </tr>
    </table>
</body>
</html>
HTML;
}

/**
 * Build plain text email body for OTP
 */
function buildOTPEmailText($fullName, $otp) {
    $expiry = PasswordReset::OTP_EXPIRY_MINUTES;
    return <<<TEXT
SDO ATLAS - Password Reset Request

Hello {$fullName},

We received a request to reset your password for your SDO ATLAS account.

Your OTP Code: {$otp}

This code expires in {$expiry} minutes.

SECURITY NOTICE:
- Do not share this OTP with anyone.
- If you did not request this, please ignore this email.
- Your password will not change unless you complete the reset process.

---
SDO ATLAS - Department of Education
Schools Division Office of San Pedro City

TEXT;
}
