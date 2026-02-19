<?php
/**
 * Email Configuration
 * SDO CTS - San Pedro Division Office Complaint Tracking System
 * 
 * Uses PHPMailer with SMTP for all email communications.
 * Configure via environment variables (.env file)
 */

// Load environment variables if not already loaded
if (!function_exists('loadEnvFile')) {
    function loadEnvFile() {
        $envFile = dirname(__DIR__) . DIRECTORY_SEPARATOR . '.env';
        if (is_readable($envFile)) {
            $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (str_starts_with(trim($line), '#')) { continue; }
                $pos = strpos($line, '=');
                if ($pos === false) { continue; }
                $key = trim(substr($line, 0, $pos));
                $val = trim(substr($line, $pos + 1));
                // Remove optional surrounding quotes
                if ((str_starts_with($val, '"') && str_ends_with($val, '"')) || 
                    (str_starts_with($val, "'") && str_ends_with($val, "'"))) {
                    $val = substr($val, 1, -1);
                }
                if (!getenv($key)) {
                    putenv("$key=$val");
                    $_ENV[$key] = $val;
                }
            }
        }
    }
    loadEnvFile();
}

// SMTP Configuration - loaded from environment variables
define('MAIL_ENABLED', filter_var(getenv('MAIL_ENABLED') ?: 'true', FILTER_VALIDATE_BOOLEAN));
define('SMTP_HOST', getenv('SMTP_HOST') ?: 'smtp.gmail.com');
define('SMTP_PORT', intval(getenv('SMTP_PORT') ?: 587));
define('SMTP_USERNAME', getenv('SMTP_USERNAME') ?: 'ict.sanpedrocity@deped.gov.ph');
define('SMTP_PASSWORD', getenv('SMTP_PASSWORD') ?: 'jdsh bccg fbdd vodw');
define('SMTP_ENCRYPTION', getenv('SMTP_ENCRYPTION') ?: 'tls'); // 'tls' or 'ssl'
define('SMTP_AUTH', filter_var(getenv('SMTP_AUTH') ?: 'true', FILTER_VALIDATE_BOOLEAN));

// Sender Configuration
define('MAIL_FROM_ADDRESS', getenv('MAIL_FROM_ADDRESS') ?: 'ict.sanpedrocity@deped.gov.ph');
define('MAIL_FROM_NAME', getenv('MAIL_FROM_NAME') ?: 'SDO ALPAS - The Schools Division Office of San Pedro City Authority to Travel, Locator and Pass slip Approval System');
define('MAIL_REPLY_TO', getenv('MAIL_REPLY_TO') ?: 'ict.sanpedrocity@deped.gov.ph');

// Admin notification recipients (comma-separated emails)
define('ADMIN_EMAIL_RECIPIENTS', getenv('ADMIN_EMAIL_RECIPIENTS') ?: '');

// Email Settings
define('MAIL_CHARSET', 'UTF-8');
define('MAIL_DEBUG', intval(getenv('MAIL_DEBUG') ?: 0)); // 0 = off, 1 = client, 2 = server

// Email Templates Path
define('EMAIL_TEMPLATES_PATH', __DIR__ . '/../services/email/templates/');

// System URLs for email content
define('SYSTEM_BASE_URL', getenv('SYSTEM_BASE_URL') ?: 'http://localhost/SDO-cts');
define('TRACKING_URL', SYSTEM_BASE_URL . '/track.php');
