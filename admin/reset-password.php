<?php
/**
 * Reset Password Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 * 
 * Step 3: User sets a new password after OTP verification
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../models/PasswordReset.php';

$email = $_GET['email'] ?? '';
$resetToken = $_GET['token'] ?? '';
$error = '';
$success = false;

// Validate that we have the required parameters
if (empty($email) || empty($resetToken)) {
    header('Location: ' . ADMIN_URL . '/forgot-password.php');
    exit;
}

// Validate the reset token
$resetModel = new PasswordReset();
$valid = $resetModel->validateResetToken($email, $resetToken);

if (!$valid) {
    header('Location: ' . ADMIN_URL . '/forgot-password.php?error=invalid_token');
    exit;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Reset Password - <?php echo ADMIN_TITLE; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <style>
        :root {
            --primary: #0f4c75;
            --primary-light: #1b6ca8;
            --primary-dark: #0a2f4a;
            --accent: #bbe1fa;
            --gold: #d4af37;
            --bg-dark: #0a1628;
            --bg-card: #111d2e;
            --text: #e8f1f8;
            --text-muted: #7a9bb8;
            --border: rgba(187, 225, 250, 0.1);
            --error: #ef4444;
            --success: #10b981;
        }

        * { margin: 0; padding: 0; box-sizing: border-box; }

        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }

        .container { width: 100%; max-width: 440px; }

        .card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
            backdrop-filter: blur(20px);
        }

        .card-header {
            text-align: center;
            margin-bottom: 28px;
        }

        .logo-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 90px;
            height: 90px;
            background: transparent;
            border-radius: 50%;
            margin-bottom: 16px;
            position: relative;
            overflow: hidden;
        }

        .logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }

        .icon-badge {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 70px;
            height: 70px;
            background: linear-gradient(135deg, var(--primary-light), var(--primary));
            border-radius: 50%;
            margin-bottom: 16px;
        }

        .icon-badge i {
            font-size: 1.8rem;
            color: white;
        }

        .icon-badge.success {
            background: linear-gradient(135deg, var(--success), #059669);
        }

        .card-header h1 {
            color: var(--text);
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
        }

        .card-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.5;
        }

        .step-indicator {
            display: flex;
            justify-content: center;
            gap: 8px;
            margin-bottom: 24px;
        }

        .step-dot {
            width: 8px;
            height: 8px;
            border-radius: 50%;
            background: var(--border);
        }

        .step-dot.active {
            background: var(--primary-light);
            width: 24px;
            border-radius: 4px;
        }

        .step-dot.completed { background: var(--success); }

        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .input-wrapper {
            position: relative;
        }

        .form-control {
            width: 100%;
            padding: 12px 42px 12px 14px;
            font-size: 0.95rem;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            transition: all 0.2s ease;
        }

        .form-control::placeholder { color: var(--text-muted); }

        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }

        .toggle-password {
            position: absolute;
            right: 12px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: var(--text-muted);
            cursor: pointer;
            padding: 4px;
            font-size: 0.9rem;
        }

        .toggle-password:hover { color: var(--text); }

        .password-requirements {
            margin-top: 8px;
            padding: 10px 12px;
            background: rgba(0, 0, 0, 0.2);
            border-radius: 8px;
        }

        .password-requirements p {
            color: var(--text-muted);
            font-size: 0.75rem;
            margin-bottom: 6px;
            font-weight: 500;
        }

        .req-item {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-bottom: 3px;
        }

        .req-item i { font-size: 0.7rem; }
        .req-item.valid { color: var(--success); }
        .req-item.invalid { color: var(--text-muted); }

        .btn {
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            width: 100%;
            padding: 12px 20px;
            font-size: 0.95rem;
            font-weight: 600;
            font-family: inherit;
            border-radius: 10px;
            cursor: pointer;
            transition: all 0.2s ease;
            border: none;
            text-decoration: none;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 76, 117, 0.4);
        }

        .btn-primary:disabled {
            opacity: 0.6;
            cursor: not-allowed;
            transform: none;
        }

        .btn-success {
            background: linear-gradient(135deg, var(--success), #059669);
            color: white;
            margin-top: 16px;
        }

        .btn-success:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
        }

        .alert {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: flex-start;
            gap: 10px;
        }

        .alert i { margin-top: 2px; flex-shrink: 0; }

        .alert-error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
        }

        .alert-success {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
        }

        .spinner {
            display: none;
            width: 18px;
            height: 18px;
            border: 2px solid rgba(255,255,255,0.3);
            border-top-color: white;
            border-radius: 50%;
            animation: spin 0.6s linear infinite;
        }

        @keyframes spin { to { transform: rotate(360deg); } }

        .hidden { display: none; }

        .success-container {
            text-align: center;
            padding: 20px 0;
        }

        .success-container .checkmark {
            font-size: 3rem;
            color: var(--success);
            margin-bottom: 16px;
        }

        .brand-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.75rem;
        }

        @media (max-width: 480px) {
            .card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Reset Form -->
            <div id="reset-form-section">
                <div class="card-header">
                    <div class="logo-badge">
                        <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                    </div>
                    <h1>SDO ALPAS</h1>
                    <p>Set New Password</p>
                    <p style="margin-top: 6px;">Create a strong new password for your account<br>
                    <strong style="color: var(--accent);"><?php echo htmlspecialchars($email); ?></strong></p>
                </div>

                <div class="step-indicator">
                    <div class="step-dot completed"></div>
                    <div class="step-dot completed"></div>
                    <div class="step-dot active"></div>
                </div>

                <div id="reset-alert" class="hidden"></div>

                <form id="reset-form" onsubmit="return handleResetPassword(event)">
                    <div class="form-group">
                        <label class="form-label" for="new-password">New Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="new-password" name="new_password" 
                                   placeholder="Enter new password" required oninput="checkPasswordStrength()">
                            <button type="button" class="toggle-password" onclick="togglePassword('new-password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div class="password-requirements">
                            <p>Password requirements:</p>
                            <div class="req-item" id="req-length">
                                <i class="fas fa-circle"></i> At least 8 characters
                            </div>
                            <div class="req-item" id="req-upper">
                                <i class="fas fa-circle"></i> One uppercase letter
                            </div>
                            <div class="req-item" id="req-lower">
                                <i class="fas fa-circle"></i> One lowercase letter
                            </div>
                            <div class="req-item" id="req-number">
                                <i class="fas fa-circle"></i> One number
                            </div>
                        </div>
                    </div>

                    <div class="form-group">
                        <label class="form-label" for="confirm-password">Confirm Password</label>
                        <div class="input-wrapper">
                            <input type="password" class="form-control" id="confirm-password" name="confirm_password" 
                                   placeholder="Confirm new password" required oninput="checkPasswordMatch()">
                            <button type="button" class="toggle-password" onclick="togglePassword('confirm-password', this)">
                                <i class="fas fa-eye"></i>
                            </button>
                        </div>
                        <div id="match-status" class="req-item" style="margin-top: 6px; display: none;">
                            <i class="fas fa-circle"></i> <span></span>
                        </div>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-reset">
                        <span class="spinner" id="reset-spinner"></span>
                        <i class="fas fa-save" id="reset-icon"></i>
                        <span id="reset-text">Reset Password</span>
                    </button>
                </form>
            </div>

            <!-- Success State -->
            <div id="success-section" class="hidden">
                <div class="card-header">
                    <div class="logo-badge">
                        <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                    </div>
                    <h1>SDO ALPAS</h1>
                    <p style="color: var(--success); font-weight: 600;">Password Reset Successful</p>
                    <p style="margin-top: 6px;">Your password has been changed successfully. You can now log in with your new password.</p>
                </div>

                <div class="step-indicator">
                    <div class="step-dot completed"></div>
                    <div class="step-dot completed"></div>
                    <div class="step-dot completed"></div>
                </div>

                <a href="<?php echo ADMIN_URL; ?>/login.php" class="btn btn-success">
                    <i class="fas fa-sign-in-alt"></i> Go to Login
                </a>
            </div>
        </div>

        <div class="brand-footer">
            <p>&copy; <?php echo date('Y'); ?> SDO ALPAS - Department of Education<br>
            Schools Division Office of San Pedro City</p>
        </div>
    </div>

    <script>
        const ADMIN_URL = '<?php echo ADMIN_URL; ?>';
        const EMAIL = '<?php echo htmlspecialchars($email); ?>';
        const RESET_TOKEN = '<?php echo htmlspecialchars($resetToken); ?>';

        function togglePassword(inputId, btn) {
            const input = document.getElementById(inputId);
            const icon = btn.querySelector('i');
            if (input.type === 'password') {
                input.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                input.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        function checkPasswordStrength() {
            const pw = document.getElementById('new-password').value;

            updateReq('req-length', pw.length >= 8);
            updateReq('req-upper', /[A-Z]/.test(pw));
            updateReq('req-lower', /[a-z]/.test(pw));
            updateReq('req-number', /[0-9]/.test(pw));

            checkPasswordMatch();
        }

        function updateReq(id, valid) {
            const el = document.getElementById(id);
            el.className = 'req-item ' + (valid ? 'valid' : 'invalid');
            el.querySelector('i').className = valid ? 'fas fa-check-circle' : 'fas fa-circle';
        }

        function checkPasswordMatch() {
            const pw = document.getElementById('new-password').value;
            const confirm = document.getElementById('confirm-password').value;
            const status = document.getElementById('match-status');

            if (!confirm) {
                status.style.display = 'none';
                return;
            }

            status.style.display = 'flex';
            if (pw === confirm) {
                status.className = 'req-item valid';
                status.querySelector('i').className = 'fas fa-check-circle';
                status.querySelector('span').textContent = 'Passwords match';
            } else {
                status.className = 'req-item invalid';
                status.querySelector('i').className = 'fas fa-times-circle';
                status.querySelector('span').textContent = 'Passwords do not match';
            }
        }

        function showAlert(type, message) {
            const alertEl = document.getElementById('reset-alert');
            const icons = { error: 'exclamation-triangle', success: 'check-circle' };
            alertEl.className = 'alert alert-' + type;
            alertEl.innerHTML = '<i class="fas fa-' + icons[type] + '"></i><span>' + message + '</span>';
            alertEl.classList.remove('hidden');
        }

        async function handleResetPassword(e) {
            e.preventDefault();

            const pw = document.getElementById('new-password').value;
            const confirm = document.getElementById('confirm-password').value;

            // Validate
            if (pw.length < 8) {
                showAlert('error', 'Password must be at least 8 characters long.');
                return false;
            }
            if (!/[A-Z]/.test(pw) || !/[a-z]/.test(pw) || !/[0-9]/.test(pw)) {
                showAlert('error', 'Password must contain at least one uppercase letter, one lowercase letter, and one number.');
                return false;
            }
            if (pw !== confirm) {
                showAlert('error', 'Passwords do not match.');
                return false;
            }

            // Submit
            const btn = document.getElementById('btn-reset');
            const spinner = document.getElementById('reset-spinner');
            const icon = document.getElementById('reset-icon');
            const text = document.getElementById('reset-text');

            btn.disabled = true;
            spinner.style.display = 'block';
            icon.style.display = 'none';
            text.textContent = 'Resetting...';

            try {
                const resp = await fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({
                        action: 'reset',
                        email: EMAIL,
                        reset_token: RESET_TOKEN,
                        new_password: pw
                    })
                });

                const data = await resp.json();

                if (data.success) {
                    document.getElementById('reset-form-section').classList.add('hidden');
                    document.getElementById('success-section').classList.remove('hidden');
                } else {
                    showAlert('error', data.message || 'Failed to reset password. Please try again.');
                    btn.disabled = false;
                    spinner.style.display = 'none';
                    icon.style.display = 'inline';
                    text.textContent = 'Reset Password';
                }
            } catch (err) {
                showAlert('error', 'Network error. Please try again.');
                btn.disabled = false;
                spinner.style.display = 'none';
                icon.style.display = 'inline';
                text.textContent = 'Reset Password';
            }

            return false;
        }
    </script>
</body>
</html>
