<?php
/**
 * Registration OTP API Endpoint
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 *
 * Handles three actions:
 *   - request : Validate form data, store temp registration, generate & send OTP
 *   - verify  : Validate OTP, create user account on success
 *   - resend  : Invalidate old OTP, generate & send a new one
 *
 * Reuses the email infrastructure from the forgot-password flow
 * but with a registration-specific email template.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/admin_config.php';
require_once __DIR__ . '/../../config/mail_config.php';
require_once __DIR__ . '/../../models/AdminUser.php';
require_once __DIR__ . '/../../models/EmailVerification.php';
require_once __DIR__ . '/../../models/ActivityLog.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);

if (!$input || empty($input['action'])) {
    http_response_code(400);
    echo json_encode(['success' => false, 'message' => 'Invalid request.']);
    exit;
}

$action       = $input['action'];
$userModel    = new AdminUser();
$verifyModel  = new EmailVerification();
$activityLog  = new ActivityLog();

switch ($action) {
    case 'request':
        handleRequest($input, $userModel, $verifyModel, $activityLog);
        break;
    case 'verify':
        handleVerify($input, $userModel, $verifyModel, $activityLog);
        break;
    case 'resend':
        handleResend($input, $userModel, $verifyModel, $activityLog);
        break;
    default:
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid action.']);
        break;
}

// ─── Action Handlers ─────────────────────────────────────────────

function handleRequest($input, $userModel, $verifyModel, $activityLog) {
    $fullName        = trim($input['full_name'] ?? '');
    $email           = trim($input['email'] ?? '');
    $password        = $input['password'] ?? '';
    $passwordConfirm = $input['password_confirm'] ?? '';
    $officeId        = $input['office_id'] ?? null;
    $employeeNo      = trim($input['employee_no'] ?? '');
    $employeePosition = trim($input['employee_position'] ?? '');

    // ── Validation ──
    if (empty($fullName)) {
        echo json_encode(['success' => false, 'message' => 'Full name is required.']);
        return;
    }
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid email address.']);
        return;
    }
    if (empty($password) || strlen($password) < 8) {
        echo json_encode(['success' => false, 'message' => 'Password must be at least 8 characters long.']);
        return;
    }
    if ($password !== $passwordConfirm) {
        echo json_encode(['success' => false, 'message' => 'Passwords do not match.']);
        return;
    }

    // Check if email already registered as an active user
    if ($userModel->emailExists($email)) {
        echo json_encode(['success' => false, 'message' => 'This email is already registered. Please log in or use forgot password.']);
        return;
    }

    // Create temp verification + OTP (rate-limiting is inside the model)
    $result = $verifyModel->createVerification([
        'email'             => $email,
        'full_name'         => $fullName,
        'password'          => $password,
        'office_id'         => $officeId,
        'employee_no'       => $employeeNo,
        'employee_position' => $employeePosition
    ]);

    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to create verification.']);
        return;
    }

    // Send OTP email
    $emailSent = sendRegistrationOTPEmail($email, $fullName, $result['otp']);
    if (!$emailSent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send verification email. Please try again later.']);
        return;
    }

    // Log OTP generation
    $activityLog->log(
        null,
        'registration_otp_sent',
        'email_verification',
        $result['verification_id'],
        "Registration OTP sent to {$email} (Name: {$fullName})."
    );

    echo json_encode([
        'success'    => true,
        'message'    => 'OTP has been sent to your email. Please check your inbox.',
        'expires_at' => $result['expires_at']
    ]);
}

function handleVerify($input, $userModel, $verifyModel, $activityLog) {
    $email = trim($input['email'] ?? '');
    $otp   = trim($input['otp'] ?? '');

    if (empty($email) || empty($otp)) {
        echo json_encode(['success' => false, 'message' => 'Email and OTP are required.']);
        return;
    }
    if (strlen($otp) !== 6 || !ctype_digit($otp)) {
        echo json_encode(['success' => false, 'message' => 'Please enter a valid 6-digit OTP.']);
        return;
    }

    $result = $verifyModel->verifyOTP($email, $otp);

    if (!$result['success']) {
        $response = [
            'success' => false,
            'error'   => $result['error'],
            'message' => $result['message']
        ];
        if (!empty($result['redirect'])) $response['redirect'] = true;

        // Log failed attempt
        $activityLog->log(
            null,
            'registration_otp_failed',
            'email_verification',
            null,
            "Registration OTP verification failed for {$email}: {$result['error']}"
        );

        echo json_encode($response);
        return;
    }

    // OTP verified — create the user account
    $verification = $result['verification'];
    $createResult = $verifyModel->createUserFromVerification($verification);

    if (!$createResult['success']) {
        echo json_encode(['success' => false, 'message' => $createResult['error'] ?? 'Failed to create account.']);
        return;
    }

    // Log successful registration
    $activityLog->log(
        $createResult['user_id'],
        'registration_completed',
        'user',
        $createResult['user_id'],
        "Account created via email verification for {$email}."
    );

    echo json_encode([
        'success' => true,
        'message' => 'Email verified and account created successfully!'
    ]);
}

function handleResend($input, $userModel, $verifyModel, $activityLog) {
    $email = trim($input['email'] ?? '');

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        echo json_encode(['success' => false, 'message' => 'Invalid email address.']);
        return;
    }

    // Rate-limit check
    $limit = $verifyModel->checkRequestLimit($email);
    if (!$limit['allowed']) {
        echo json_encode([
            'success' => false,
            'message' => 'You have reached the maximum OTP requests (' . EmailVerification::MAX_OTP_REQUESTS_PER_HOUR . ' per hour). Please try again later.'
        ]);
        return;
    }

    // We need to re-create a verification from the latest record's data
    // Get the latest record (any status) for this email to recover form data
    $db = Database::getInstance();
    $latest = $db->query(
        "SELECT * FROM email_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1",
        [$email]
    )->fetch();

    if (!$latest) {
        echo json_encode(['success' => false, 'message' => 'No registration found for this email. Please register again.']);
        return;
    }

    // Invalidate old records and create fresh OTP using existing form data
    $result = $verifyModel->createVerification([
        'email'             => $latest['email'],
        'full_name'         => $latest['full_name'],
        'password'          => '__REUSE__', // placeholder — won't be hashed again
        'office_id'         => $latest['office_id'],
        'employee_no'       => $latest['employee_no'],
        'employee_position' => $latest['employee_position']
    ]);

    // Since createVerification hashes the password, we need to overwrite with the original hash
    if ($result['success']) {
        $db->query(
            "UPDATE email_verifications SET password_hash = ? WHERE id = ?",
            [$latest['password_hash'], $result['verification_id']]
        );
    }

    if (!$result['success']) {
        echo json_encode(['success' => false, 'message' => $result['message'] ?? 'Failed to resend OTP.']);
        return;
    }

    $emailSent = sendRegistrationOTPEmail($email, $latest['full_name'], $result['otp']);
    if (!$emailSent) {
        echo json_encode(['success' => false, 'message' => 'Failed to send OTP email. Please try again later.']);
        return;
    }

    $activityLog->log(
        null,
        'registration_otp_resent',
        'email_verification',
        $result['verification_id'],
        "Registration OTP resent to {$email}."
    );

    echo json_encode([
        'success'    => true,
        'message'    => 'New OTP sent! Check your email.',
        'expires_at' => $result['expires_at']
    ]);
}

// ─── Email Sending ───────────────────────────────────────────────

/**
 * Send registration OTP email using PHPMailer (reuses SMTP config from mail_config.php)
 */
function sendRegistrationOTPEmail($email, $fullName, $otp) {
    if (!MAIL_ENABLED) {
        error_log("SDO ALPAS - Registration OTP for {$email}: {$otp}");
        return true;
    }

    try {
        require_once __DIR__ . '/../../vendor/autoload.php';

        $mail = new PHPMailer\PHPMailer\PHPMailer(true);

        $mail->isSMTP();
        $mail->Host       = SMTP_HOST;
        $mail->SMTPAuth   = SMTP_AUTH;
        $mail->Username   = SMTP_USERNAME;
        $mail->Password   = SMTP_PASSWORD;
        $mail->SMTPSecure = SMTP_ENCRYPTION;
        $mail->Port       = SMTP_PORT;
        $mail->CharSet    = MAIL_CHARSET;

        $mail->setFrom(MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addReplyTo(MAIL_REPLY_TO ?: MAIL_FROM_ADDRESS, MAIL_FROM_NAME);
        $mail->addAddress($email, $fullName);

        $mail->isHTML(true);
        $mail->Subject = 'SDO ALPAS - Email Verification OTP';

        // Embed logos
        $sdoLogoPath = __DIR__ . '/../assets/logos/sdo-logo.jpg';
        $bpLogoPath  = __DIR__ . '/../assets/logos/bagongpilpinas-logo.png';
        if (file_exists($sdoLogoPath)) $mail->addEmbeddedImage($sdoLogoPath, 'sdo-logo', 'sdo-logo.jpg');
        if (file_exists($bpLogoPath))  $mail->addEmbeddedImage($bpLogoPath, 'bp-logo', 'bagongpilpinas-logo.png');

        $mail->Body    = buildRegistrationOTPEmailHTML($fullName, $otp);
        $mail->AltBody = buildRegistrationOTPEmailText($fullName, $otp);

        $mail->send();
        return true;
    } catch (Exception $e) {
        error_log("SDO ALPAS - Failed to send registration OTP email to {$email}: " . $e->getMessage());
        return false;
    }
}

// ─── Email Templates ─────────────────────────────────────────────

function buildRegistrationOTPEmailHTML($fullName, $otp) {
    $expiry = EmailVerification::OTP_EXPIRY_MINUTES;
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
                            <h1 style="margin: 0; color: #ffffff; font-size: 22px; font-weight: 700;">SDO ALPAS</h1>
                            <p style="margin: 4px 0 0; color: rgba(255,255,255,0.85); font-size: 12px; line-height: 1.4;">
                                The Schools Division Office of San Pedro City<br>
                                Account Registration Verification
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
                    Thank you for registering with SDO ALPAS! To complete your account registration, please use the One-Time Password (OTP) below to verify your email address:
                </p>
                <!-- OTP Box -->
                <div style="text-align: center; margin: 24px 0;">
                    <div style="display: inline-block; background: #f0f7ff; border: 2px dashed #1b6ca8; border-radius: 10px; padding: 16px 40px;">
                        <p style="margin: 0 0 4px; color: #666; font-size: 12px; text-transform: uppercase; letter-spacing: 1px;">Your Verification Code</p>
                        <p style="margin: 0; color: #0f4c75; font-size: 32px; font-weight: 800; letter-spacing: 8px;">{$otp}</p>
                    </div>
                </div>
                <p style="margin: 16px 0; color: #ef4444; font-size: 13px; text-align: center; font-weight: 500;">
                    ⏱ This code expires in {$expiry} minutes.
                </p>
                <hr style="border: none; border-top: 1px solid #eee; margin: 24px 0;">
                <p style="margin: 0 0 8px; color: #888; font-size: 13px;">
                    <strong>Important:</strong>
                </p>
                <ul style="margin: 0; padding: 0 0 0 20px; color: #888; font-size: 13px; line-height: 1.8;">
                    <li>Do not share this OTP with anyone.</li>
                    <li>If you did not register for an SDO ALPAS account, please ignore this email.</li>
                    <li>You have up to 5 attempts to enter the correct OTP.</li>
                    <li>After successful verification, your account will be activated immediately.</li>
                </ul>
            </td>
        </tr>
        <!-- Footer -->
        <tr>
            <td style="background: #f8fafc; padding: 20px 32px; text-align: center; border-top: 1px solid #eee;">
                <p style="margin: 0; color: #999; font-size: 12px;">
                    SDO ALPAS - Department of Education<br>
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

function buildRegistrationOTPEmailText($fullName, $otp) {
    $expiry = EmailVerification::OTP_EXPIRY_MINUTES;
    return <<<TEXT
SDO ALPAS - Account Registration Verification

Hello {$fullName},

Thank you for registering with SDO ALPAS! To complete your account registration, please use the One-Time Password (OTP) below to verify your email address:

Your Verification Code: {$otp}

This code expires in {$expiry} minutes.

IMPORTANT:
- Do not share this OTP with anyone.
- If you did not register for an SDO ALPAS account, please ignore this email.
- You have up to 5 attempts to enter the correct OTP.
- After successful verification, your account will be activated immediately.

---
SDO ALPAS - Department of Education
Schools Division Office of San Pedro City
ict.sanpedrocity@deped.gov.ph
TEXT;
}
