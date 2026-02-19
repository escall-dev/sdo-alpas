<?php
/**
 * Forgot Password Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 * 
 * Step 1: User enters email to request OTP
 * Step 2: OTP verification (shown after successful request)
 */

require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../models/AdminUser.php';
require_once __DIR__ . '/../models/PasswordReset.php';

$step = $_GET['step'] ?? 'request'; // request, verify
$email = $_GET['email'] ?? '';
$error = '';
$success = '';
$blocked = false;

// If email is provided, check if blocked
if ($email) {
    $userModel = new AdminUser();
    $user = $userModel->getByEmail($email);
    if ($user) {
        $resetModel = new PasswordReset();
        $blocked = $resetModel->isBlocked($user['id']);
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Forgot Password - <?php echo ADMIN_TITLE; ?></title>
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
            --warning: #f59e0b;
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

        .form-group { margin-bottom: 16px; }

        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }

        .form-control {
            width: 100%;
            padding: 12px 14px;
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

        .otp-input-group {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 16px 0;
        }

        .otp-input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            transition: all 0.2s ease;
        }

        .otp-input:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }

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
            box-shadow: none;
        }

        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 12px;
        }

        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-muted);
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

        .alert-warning {
            background: rgba(245, 158, 11, 0.1);
            border: 1px solid rgba(245, 158, 11, 0.3);
            color: #fcd34d;
        }

        .alert-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
        }

        .blocked-container {
            text-align: center;
            padding: 20px 0;
        }

        .blocked-container .blocked-icon {
            font-size: 3rem;
            color: var(--error);
            margin-bottom: 16px;
        }

        .blocked-container h2 {
            color: var(--text);
            font-size: 1.2rem;
            margin-bottom: 12px;
        }

        .blocked-container p {
            color: var(--text-muted);
            font-size: 0.9rem;
            line-height: 1.6;
            margin-bottom: 8px;
        }

        .blocked-container .contact-info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.2);
            border-radius: 10px;
            padding: 16px;
            margin-top: 16px;
        }

        .blocked-container .contact-info p {
            color: #93c5fd;
            font-size: 0.85rem;
            margin-bottom: 4px;
        }

        .timer {
            text-align: center;
            color: var(--text-muted);
            font-size: 0.82rem;
            margin-top: 8px;
        }

        .timer span { color: var(--warning); font-weight: 600; }

        .resend-link {
            text-align: center;
            margin-top: 12px;
        }

        .resend-link a {
            color: var(--accent);
            font-size: 0.85rem;
            text-decoration: none;
            cursor: pointer;
        }

        .resend-link a:hover { text-decoration: underline; }

        .resend-link a.disabled {
            color: var(--text-muted);
            pointer-events: none;
        }

        .back-link {
            text-align: center;
            margin-top: 20px;
        }

        .back-link a {
            color: var(--text-muted);
            font-size: 0.85rem;
            text-decoration: none;
        }

        .back-link a:hover { color: var(--accent); }

        .brand-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.75rem;
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

        @keyframes spin {
            to { transform: rotate(360deg); }
        }

        .hidden { display: none; }

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
            transition: all 0.3s ease;
        }

        .step-dot.active {
            background: var(--primary-light);
            width: 24px;
            border-radius: 4px;
        }

        .step-dot.completed { background: var(--success); }

        @media (max-width: 480px) {
            .card { padding: 24px 20px; }
            .otp-input { width: 42px; height: 50px; font-size: 1.2rem; }
        }
    </style>
</head>
<body>
    <div class="container">
        <div class="card">
            <!-- Step 1: Request OTP -->
            <div id="step-request" class="<?php echo ($step !== 'request' || $blocked) ? 'hidden' : ''; ?>">
                <div class="card-header">
                    <div class="logo-badge">
                        <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                    </div>
                    <h1>SDO ALPAS</h1>
                    <p>Forgot Password</p>
                    <p style="margin-top: 6px;">Enter your registered email address and we'll send you a one-time password (OTP) to reset your password.</p>
                </div>

                <div class="step-indicator">
                    <div class="step-dot active"></div>
                    <div class="step-dot"></div>
                    <div class="step-dot"></div>
                </div>

                <div id="request-alert" class="hidden"></div>

                <form id="request-form" onsubmit="return handleRequestOTP(event)">
                    <div class="form-group">
                        <label class="form-label" for="email">Email Address</label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($email); ?>"
                               placeholder="your.email@deped.gov.ph" required>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-request">
                        <span class="spinner" id="request-spinner"></span>
                        <i class="fas fa-paper-plane" id="request-icon"></i>
                        <span id="request-text">Send OTP</span>
                    </button>
                </form>

                <div class="back-link">
                    <a href="<?php echo ADMIN_URL; ?>/login.php">
                        <i class="fas fa-arrow-left"></i> Back to Login
                    </a>
                </div>
            </div>

            <!-- Step 2: Verify OTP -->
            <div id="step-verify" class="<?php echo $step !== 'verify' ? 'hidden' : ''; ?>">
                <div class="card-header">
                    <div class="logo-badge">
                        <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                    </div>
                    <h1>SDO ALPAS</h1>
                    <p>Verify OTP</p>
                    <p style="margin-top: 6px;">Enter the 6-digit code sent to your email address.</p>
                </div>

                <div class="step-indicator">
                    <div class="step-dot completed"></div>
                    <div class="step-dot active"></div>
                    <div class="step-dot"></div>
                </div>

                <div id="verify-alert" class="hidden"></div>

                <form id="verify-form" onsubmit="return handleVerifyOTP(event)">
                    <input type="hidden" id="verify-email" value="<?php echo htmlspecialchars($email); ?>">

                    <div class="otp-input-group">
                        <input type="text" class="otp-input" maxlength="1" data-index="0" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="1" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="2" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="3" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="4" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                        <input type="text" class="otp-input" maxlength="1" data-index="5" inputmode="numeric" pattern="[0-9]" autocomplete="off">
                    </div>

                    <div class="timer" id="otp-timer">
                        OTP expires in <span id="timer-countdown">5:00</span>
                    </div>

                    <button type="submit" class="btn btn-primary" id="btn-verify" style="margin-top: 16px;">
                        <span class="spinner" id="verify-spinner"></span>
                        <i class="fas fa-check-circle" id="verify-icon"></i>
                        <span id="verify-text">Verify OTP</span>
                    </button>
                </form>

                <div class="resend-link">
                    <a href="#" id="resend-link" class="disabled" onclick="return handleResendOTP(event)">
                        Resend OTP <span id="resend-timer">(wait 60s)</span>
                    </a>
                </div>

                <div class="back-link">
                    <a href="#" onclick="showStep('request'); return false;">
                        <i class="fas fa-arrow-left"></i> Change email
                    </a>
                </div>
            </div>

            <!-- Blocked State -->
            <div id="step-blocked" class="<?php echo !$blocked ? 'hidden' : ''; ?>">
                <div class="card-header">
                    <div class="logo-badge">
                        <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                    </div>
                    <h1>SDO ALPAS</h1>
                    <p>Request Limit Reached</p>
                </div>

                <div class="blocked-container">
                    <p>You have reached the maximum number of password reset attempts (5 requests).</p>
                    <p>For security reasons, further requests have been disabled for your account.</p>

                    <div class="contact-info">
                        <p><strong><i class="fas fa-headset"></i> Contact ICT Unit for Assistance</strong></p>
                        <p>Please reach out to the ICT unit to manually reset your password.</p>
                        <p style="margin-top: 8px;"><i class="fas fa-envelope"></i> ict.sanpedrocity@deped.gov.ph</p>
                    </div>
                </div>

                <a href="<?php echo ADMIN_URL; ?>/login.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Login
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
        let otpTimerInterval = null;
        let resendTimerInterval = null;
        let otpExpiresAt = null;
        let currentEmail = '<?php echo htmlspecialchars($email, ENT_QUOTES); ?>';

        // Track verification page access on load
        (function() {
            if (currentEmail) {
                fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'track_access', email: currentEmail })
                }).catch(() => {});
            }
        })();

        // OTP input auto-focus and navigation
        document.querySelectorAll('.otp-input').forEach((input, index, inputs) => {
            input.addEventListener('input', (e) => {
                const val = e.target.value.replace(/[^0-9]/g, '');
                e.target.value = val;
                if (val && index < inputs.length - 1) {
                    inputs[index + 1].focus();
                }
            });

            input.addEventListener('keydown', (e) => {
                if (e.key === 'Backspace' && !e.target.value && index > 0) {
                    inputs[index - 1].focus();
                }
            });

            input.addEventListener('paste', (e) => {
                e.preventDefault();
                const paste = (e.clipboardData || window.clipboardData).getData('text').replace(/[^0-9]/g, '');
                for (let i = 0; i < Math.min(paste.length, inputs.length); i++) {
                    inputs[i].value = paste[i];
                }
                const nextIndex = Math.min(paste.length, inputs.length - 1);
                inputs[nextIndex].focus();
            });
        });

        function getOTPValue() {
            return Array.from(document.querySelectorAll('.otp-input')).map(i => i.value).join('');
        }

        function clearOTPInputs() {
            document.querySelectorAll('.otp-input').forEach(i => { i.value = ''; });
            document.querySelector('.otp-input').focus();
        }

        function showStep(step) {
            document.getElementById('step-request').classList.add('hidden');
            document.getElementById('step-verify').classList.add('hidden');
            document.getElementById('step-blocked').classList.add('hidden');
            document.getElementById('step-' + step).classList.remove('hidden');

            // Track access when returning to the request step (page revisit)
            if (step === 'request' && currentEmail) {
                fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'track_access', email: currentEmail })
                }).catch(() => {});
            }
        }

        function showAlert(containerId, type, message) {
            const alertEl = document.getElementById(containerId);
            const icons = { error: 'exclamation-triangle', success: 'check-circle', warning: 'exclamation-circle', info: 'info-circle' };
            alertEl.className = 'alert alert-' + type;
            alertEl.innerHTML = '<i class="fas fa-' + icons[type] + '"></i><span>' + message + '</span>';
            alertEl.classList.remove('hidden');
        }

        function hideAlert(containerId) {
            document.getElementById(containerId).classList.add('hidden');
        }

        function setButtonLoading(btnId, loading) {
            const btn = document.getElementById(btnId);
            const spinner = document.getElementById(btnId.replace('btn-', '') + '-spinner');
            const icon = document.getElementById(btnId.replace('btn-', '') + '-icon');
            const text = document.getElementById(btnId.replace('btn-', '') + '-text');

            if (loading) {
                btn.disabled = true;
                spinner.style.display = 'block';
                if (icon) icon.style.display = 'none';
                text.textContent = 'Please wait...';
            } else {
                btn.disabled = false;
                spinner.style.display = 'none';
                if (icon) icon.style.display = 'inline';
            }
        }

        function startOTPTimer(seconds) {
            if (otpTimerInterval) clearInterval(otpTimerInterval);
            let remaining = seconds;
            const timerEl = document.getElementById('timer-countdown');

            otpTimerInterval = setInterval(() => {
                remaining--;
                const mins = Math.floor(remaining / 60);
                const secs = remaining % 60;
                timerEl.textContent = mins + ':' + secs.toString().padStart(2, '0');

                if (remaining <= 0) {
                    clearInterval(otpTimerInterval);
                    timerEl.textContent = 'Expired';
                    timerEl.style.color = '#ef4444';
                    showAlert('verify-alert', 'error', 'Your OTP has expired. Please request a new one.');
                }
            }, 1000);
        }

        function startResendTimer() {
            if (resendTimerInterval) clearInterval(resendTimerInterval);
            let remaining = 60;
            const link = document.getElementById('resend-link');
            const timerSpan = document.getElementById('resend-timer');

            link.classList.add('disabled');
            timerSpan.textContent = '(wait ' + remaining + 's)';

            resendTimerInterval = setInterval(() => {
                remaining--;
                timerSpan.textContent = '(wait ' + remaining + 's)';

                if (remaining <= 0) {
                    clearInterval(resendTimerInterval);
                    link.classList.remove('disabled');
                    timerSpan.textContent = '';
                }
            }, 1000);
        }

        async function handleRequestOTP(e) {
            e.preventDefault();
            hideAlert('request-alert');

            const email = document.getElementById('email').value.trim();
            if (!email) {
                showAlert('request-alert', 'error', 'Please enter your email address.');
                return false;
            }

            // Track access
            currentEmail = email;
            fetch(ADMIN_URL + '/api/forgot-password.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'track_access', email: email })
            }).catch(() => {});

            setButtonLoading('btn-request', true);
            document.getElementById('request-text').textContent = 'Sending OTP...';

            try {
                const resp = await fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'request', email: email })
                });

                const data = await resp.json();

                if (data.success) {
                    document.getElementById('verify-email').value = email;
                    showStep('verify');
                    showAlert('verify-alert', 'success', data.message || 'OTP sent to your email. Check your inbox.');
                    startOTPTimer(300); // 5 minutes
                    startResendTimer();
                    document.querySelector('.otp-input').focus();
                } else if (data.blocked) {
                    showStep('blocked');
                } else {
                    showAlert('request-alert', 'error', data.message || 'Failed to send OTP. Please try again.');
                }
            } catch (err) {
                showAlert('request-alert', 'error', 'Network error. Please try again.');
            } finally {
                setButtonLoading('btn-request', false);
                document.getElementById('request-text').textContent = 'Send OTP';
                document.getElementById('request-icon').style.display = 'inline';
            }

            return false;
        }

        async function handleVerifyOTP(e) {
            e.preventDefault();
            hideAlert('verify-alert');

            const otp = getOTPValue();
            const email = document.getElementById('verify-email').value;

            if (otp.length !== 6) {
                showAlert('verify-alert', 'error', 'Please enter the complete 6-digit OTP.');
                return false;
            }

            setButtonLoading('btn-verify', true);
            document.getElementById('verify-text').textContent = 'Verifying...';

            try {
                const resp = await fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'verify', email: email, otp: otp })
                });

                const data = await resp.json();

                if (data.success) {
                    if (otpTimerInterval) clearInterval(otpTimerInterval);
                    // Redirect to reset password page
                    window.location.href = ADMIN_URL + '/reset-password.php?email=' + encodeURIComponent(email) + '&token=' + encodeURIComponent(data.reset_token);
                } else {
                    // Check if OTP input attempts exhausted
                    if (data.error === 'otp_attempts_exhausted' || data.redirect) {
                        showAlert('verify-alert', 'error', data.message);
                        clearOTPInputs();
                        // Redirect back to email step after 3 seconds
                        setTimeout(() => {
                            if (otpTimerInterval) clearInterval(otpTimerInterval);
                            if (resendTimerInterval) clearInterval(resendTimerInterval);
                            showStep('request');
                            showAlert('request-alert', 'warning', 'Your OTP input attempts were exhausted. Please request a new OTP.');
                        }, 3000);
                    } else {
                        let msg = data.message || 'Invalid OTP. Please try again.';
                        if (data.otp_attempts_remaining !== undefined) {
                            msg += '';
                        }
                        showAlert('verify-alert', 'error', msg);
                        if (data.error === 'expired') {
                            clearOTPInputs();
                        }
                    }
                }
            } catch (err) {
                showAlert('verify-alert', 'error', 'Network error. Please try again.');
            } finally {
                setButtonLoading('btn-verify', false);
                document.getElementById('verify-text').textContent = 'Verify OTP';
                document.getElementById('verify-icon').style.display = 'inline';
            }

            return false;
        }

        async function handleResendOTP(e) {
            e.preventDefault();
            const link = document.getElementById('resend-link');
            if (link.classList.contains('disabled')) return false;

            hideAlert('verify-alert');

            const email = document.getElementById('verify-email').value;

            try {
                const resp = await fetch(ADMIN_URL + '/api/forgot-password.php', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify({ action: 'resend', email: email })
                });

                const data = await resp.json();

                if (data.success) {
                    let msg = 'New OTP sent! Check your email.';
                    if (data.resend_remaining !== undefined) {
                        msg += ' (Resend remaining: ' + data.resend_remaining + ')';
                    }
                    if (data.attempts_remaining !== undefined) {
                        msg += ' — Reset attempts remaining: ' + data.attempts_remaining;
                    }
                    showAlert('verify-alert', 'success', msg);
                    clearOTPInputs();
                    startOTPTimer(300);
                    startResendTimer();
                } else if (data.blocked) {
                    showStep('blocked');
                } else if (data.resend_blocked) {
                    showAlert('verify-alert', 'warning', data.message || 'OTP resend limit reached. Please try again later.');
                    // Disable the resend link
                    link.classList.add('disabled');
                    document.getElementById('resend-timer').textContent = '(limit reached)';
                } else {
                    showAlert('verify-alert', 'error', data.message || 'Failed to resend OTP.');
                }
            } catch (err) {
                showAlert('verify-alert', 'error', 'Network error. Please try again.');
            }

            return false;
        }
    </script>
</body>
</html>
