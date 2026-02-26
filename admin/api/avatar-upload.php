<?php
/**
 * Avatar Upload API Endpoint
 * SDO ALPAS - Profile Avatar Upload (1 user = 1 avatar)
 * 
 * Accepts multipart/form-data POST with 'avatar' file field.
 * Validates image type/size, deletes old avatar, saves new one.
 * Returns JSON with success status and new avatar URL.
 */

header('Content-Type: application/json');

require_once __DIR__ . '/../../config/database.php';
require_once __DIR__ . '/../../config/admin_config.php';
require_once __DIR__ . '/../../includes/auth.php';
require_once __DIR__ . '/../../models/AdminUser.php';

// Constants
define('AVATAR_MAX_SIZE', 5 * 1024 * 1024); // 5 MB
define('AVATAR_UPLOAD_DIR', __DIR__ . '/../../uploads/avatars/');
define('AVATAR_ALLOWED_TYPES', ['image/jpeg', 'image/png']);
define('AVATAR_ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Require authentication
$auth = auth();
$auth->requireLogin();

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

try {
    $userId = $auth->getUserId();
    $currentUser = $auth->getUser();
    $userModel = new AdminUser();

    // Check if this is a remove request
    $action = $_POST['action'] ?? 'upload';

    if ($action === 'remove') {
        // Remove current avatar
        $existingAvatar = $currentUser['avatar_url'] ?? null;
        if ($existingAvatar) {
            $oldFilePath = AVATAR_UPLOAD_DIR . $existingAvatar;
            if (file_exists($oldFilePath)) {
                unlink($oldFilePath);
            }
        }

        // Clear avatar in database
        $userModel->update($userId, [
            'avatar_url' => null,
            'avatar_updated_at' => date('Y-m-d H:i:s')
        ]);

        $auth->logActivity('avatar_remove', 'user', $userId, 'Removed profile avatar');

        echo json_encode([
            'success' => true,
            'message' => 'Avatar removed successfully.',
            'avatar_url' => null
        ]);
        exit;
    }

    // === Upload flow ===

    // Validate file is present
    if (!isset($_FILES['avatar']) || $_FILES['avatar']['error'] === UPLOAD_ERR_NO_FILE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'No file uploaded.']);
        exit;
    }

    $file = $_FILES['avatar'];

    // Check for upload errors
    if ($file['error'] !== UPLOAD_ERR_OK) {
        $errorMessages = [
            UPLOAD_ERR_INI_SIZE   => 'File exceeds server maximum upload size.',
            UPLOAD_ERR_FORM_SIZE  => 'File exceeds form maximum upload size.',
            UPLOAD_ERR_PARTIAL    => 'File was only partially uploaded.',
            UPLOAD_ERR_NO_TMP_DIR => 'Missing temporary folder.',
            UPLOAD_ERR_CANT_WRITE => 'Failed to write file to disk.',
            UPLOAD_ERR_EXTENSION  => 'Upload stopped by a PHP extension.',
        ];
        $msg = $errorMessages[$file['error']] ?? 'Unknown upload error.';
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => $msg]);
        exit;
    }

    // Validate file size (5 MB max)
    if ($file['size'] > AVATAR_MAX_SIZE) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'File size exceeds 5 MB limit.']);
        exit;
    }

    // Validate file extension
    $originalName = $file['name'];
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    if (!in_array($extension, AVATAR_ALLOWED_EXTENSIONS)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid file type. Only JPG, JPEG, and PNG are allowed.']);
        exit;
    }

    // Server-side MIME validation using finfo
    $finfo = new finfo(FILEINFO_MIME_TYPE);
    $mimeType = $finfo->file($file['tmp_name']);
    if (!in_array($mimeType, AVATAR_ALLOWED_TYPES)) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Invalid image file. Only JPEG and PNG images are allowed.']);
        exit;
    }

    // Additional validation: verify it's a real image
    $imageInfo = getimagesize($file['tmp_name']);
    if ($imageInfo === false) {
        http_response_code(400);
        echo json_encode(['success' => false, 'message' => 'Uploaded file is not a valid image.']);
        exit;
    }

    // Ensure upload directory exists
    if (!is_dir(AVATAR_UPLOAD_DIR)) {
        mkdir(AVATAR_UPLOAD_DIR, 0755, true);
    }

    // Generate sanitized filename: {sanitized_name}_{YYYYMMDD_HHMMSS}.{ext}
    $sanitizedName = sanitizeFilename($currentUser['full_name']);
    $timestamp = date('Ymd_His');
    $newFilename = $sanitizedName . '_' . $timestamp . '.' . $extension;

    // Prevent directory traversal
    $newFilename = basename($newFilename);
    $newFilePath = AVATAR_UPLOAD_DIR . $newFilename;

    // Delete old avatar file (enforce 1 user = 1 avatar)
    $existingAvatar = $currentUser['avatar_url'] ?? null;
    if ($existingAvatar) {
        $oldFilePath = AVATAR_UPLOAD_DIR . basename($existingAvatar);
        if (file_exists($oldFilePath)) {
            unlink($oldFilePath);
        }
    }

    // Save new file
    if (!move_uploaded_file($file['tmp_name'], $newFilePath)) {
        http_response_code(500);
        echo json_encode(['success' => false, 'message' => 'Failed to save uploaded file.']);
        exit;
    }

    // Update database with new filename and timestamp
    $userModel->update($userId, [
        'avatar_url' => $newFilename,
        'avatar_updated_at' => date('Y-m-d H:i:s')
    ]);

    // Log activity
    $auth->logActivity('avatar_upload', 'user', $userId, 'Updated profile avatar');

    // Return success with avatar URL
    $avatarUrl = BASE_URL . '/uploads/avatars/' . $newFilename;
    echo json_encode([
        'success' => true,
        'message' => 'Avatar updated successfully.',
        'avatar_url' => $avatarUrl
    ]);

} catch (Exception $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'An error occurred while uploading avatar.']);
}

/**
 * Sanitize a name for use as a filename.
 * Converts to lowercase, replaces spaces and special chars with underscores,
 * removes consecutive underscores, and trims.
 *
 * @param string $name The name to sanitize
 * @return string Sanitized filename-safe string
 */
function sanitizeFilename($name) {
    // Convert to lowercase
    $name = strtolower($name);
    // Replace common special characters and spaces with underscores
    $name = preg_replace('/[^a-z0-9]+/', '_', $name);
    // Remove leading/trailing underscores
    $name = trim($name, '_');
    // Collapse multiple underscores
    $name = preg_replace('/_+/', '_', $name);
    // Limit length to avoid filesystem issues
    if (strlen($name) > 50) {
        $name = substr($name, 0, 50);
        $name = rtrim($name, '_');
    }
    // Fallback if name is empty after sanitization
    if (empty($name)) {
        $name = 'user';
    }
    return $name;
}
