<?php
/**
 * Email OTP Verification Page (Registration)
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 *
 * User lands here after clicking "Verify Email" on register.php.
 * They enter the 6-digit OTP sent to their email.
 * On success, the account is created and a success modal is shown before redirect to login.
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/EmailVerification.php';

$email = trim($_GET['email'] ?? '');
if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
    header('Location: ' . ADMIN_URL . '/register.php?otp_error=invalid');
    exit;
}

// Calculate remaining seconds from the real OTP expiry in the database
$verifyModel = new EmailVerification();
$pendingRecord = $verifyModel->getLatestPending($email);
$otpRemainingSeconds = 0;
if ($pendingRecord) {
    $expiresTs = strtotime($pendingRecord['otp_expires_at']);
    $otpRemainingSeconds = max(0, $expiresTs - time());
} else {
    // No valid pending OTP — might be expired or invalidated
    // Check if there's an expired one to give a better message
    $db = Database::getInstance();
    $anyRecord = $db->query(
        "SELECT status FROM email_verifications WHERE email = ? ORDER BY created_at DESC LIMIT 1",
        [$email]
    )->fetch();
    if (!$anyRecord || in_array($anyRecord['status'], ['expired', 'invalidated'])) {
        header('Location: ' . ADMIN_URL . '/register.php?otp_error=expired');
        exit;
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Email - <?php echo ADMIN_TITLE; ?></title>
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
        .verify-container { width: 100%; max-width: 440px; }
        .verify-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
        }
        .verify-header { text-align: center; margin-bottom: 24px; }
        .verify-header h1 { color: var(--text); font-size: 1.4rem; font-weight: 700; margin-bottom: 8px; }
        .verify-header p { color: var(--text-muted); font-size: 0.85rem; line-height: 1.5; }
        .verify-header .email-display {
            color: var(--accent);
            font-weight: 600;
            word-break: break-all;
        }
        .otp-inputs {
            display: flex;
            gap: 8px;
            justify-content: center;
            margin: 24px 0;
        }
        .otp-inputs input {
            width: 48px;
            height: 56px;
            text-align: center;
            font-size: 1.4rem;
            font-weight: 700;
            font-family: inherit;
            background: rgba(0,0,0,0.3);
            border: 2px solid var(--border);
            border-radius: 10px;
            color: var(--text);
            transition: all 0.2s ease;
            outline: none;
        }
        .otp-inputs input:focus {
            border-color: var(--primary-light);
            background: rgba(0,0,0,0.4);
            box-shadow: 0 0 0 3px rgba(27, 108, 168, 0.2);
        }
        .otp-inputs input.error-border { border-color: var(--error); }
        .otp-inputs input.success-border { border-color: var(--success); }
        .status-msg {
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 16px;
            font-size: 0.85rem;
            display: none;
            align-items: center;
            gap: 10px;
        }
        .status-msg.error {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            display: flex;
        }
        .status-msg.info {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            display: flex;
        }
        .status-msg.success-box {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            display: flex;
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
            margin-top: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, var(--primary-light) 0%, var(--primary) 100%);
            color: white;
        }
        .btn-primary:hover:not(:disabled) {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 76, 117, 0.4);
        }
        .btn-primary:disabled { opacity: 0.6; cursor: not-allowed; transform: none; }
        .btn-secondary {
            background: rgba(255,255,255,0.05);
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 12px;
        }
        .btn-secondary:hover { background: rgba(255,255,255,0.1); }
        .timer { text-align: center; color: var(--text-muted); font-size: 0.8rem; margin-top: 8px; }
        .timer .countdown { color: var(--error); font-weight: 600; }
        .resend-link {
            text-align: center;
            margin-top: 16px;
        }
        .resend-link a {
            color: var(--primary-light);
            text-decoration: none;
            font-size: 0.85rem;
            cursor: pointer;
        }
        .resend-link a:hover { text-decoration: underline; }
        .resend-link a.disabled {
            color: var(--text-muted);
            pointer-events: none;
            cursor: not-allowed;
        }

        /* ── Success Modal ── */
        .modal-overlay {
            display: none;
            position: fixed;
            inset: 0;
            background: rgba(0,0,0,0.7);
            z-index: 1000;
            align-items: center;
            justify-content: center;
        }
        .modal-overlay.active { display: flex; }
        .modal-box {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 40px 32px;
            text-align: center;
            max-width: 400px;
            width: 90%;
            animation: modalIn 0.3s ease;
        }
        @keyframes modalIn {
            from { opacity: 0; transform: scale(0.9) translateY(20px); }
            to   { opacity: 1; transform: scale(1) translateY(0); }
        }
        .modal-icon {
            width: 72px; height: 72px;
            background: rgba(16, 185, 129, 0.15);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            margin: 0 auto 20px;
        }
        .modal-icon i { font-size: 32px; color: var(--success); }
        .modal-box h2 { color: var(--text); font-size: 1.3rem; margin-bottom: 12px; }
        .modal-box p { color: var(--text-muted); font-size: 0.9rem; line-height: 1.6; margin-bottom: 24px; }
        .modal-box .btn { max-width: 240px; margin: 0 auto; }

        @media (max-width: 480px) {
            .otp-inputs input { width: 42px; height: 50px; font-size: 1.2rem; }
            .verify-card { padding: 24px 20px; }
        }
    </style>
</head>
<body>
    <div class="verify-container">
        <div class="verify-card">
            <div class="verify-header">
                <h1><i class="fas fa-envelope-open-text"></i> Verify Your Email</h1>
                <p>
                    We sent a 6-digit OTP to<br>
                    <span class="email-display"><?php echo htmlspecialchars($email); ?></span>
                </p>
            </div>

            <div id="statusMsg" class="status-msg"></div>

            <div class="otp-inputs" id="otpGroup">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]" autocomplete="one-time-code" autofocus>
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
                <input type="text" maxlength="1" inputmode="numeric" pattern="[0-9]">
            </div>

            <div class="timer" id="timerArea">
                OTP expires in <span class="countdown" id="countdown">5:00</span>
            </div>

            <button type="button" id="btnVerify" class="btn btn-primary" onclick="submitOTP()">
                <i class="fas fa-check-circle"></i> Verify OTP
            </button>

            <div class="resend-link">
                <a id="resendLink" href="javascript:void(0)" onclick="resendOTP()" class="disabled">
                    <i class="fas fa-redo"></i> Resend OTP <span id="resendTimer">(wait 30s)</span>
                </a>
            </div>

            <a href="<?php echo ADMIN_URL; ?>/register.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Back to Registration
            </a>
        </div>
    </div>

    <!-- Success Modal -->
    <div class="modal-overlay" id="successModal">
        <div class="modal-box">
            <div class="modal-icon"><i class="fas fa-check"></i></div>
            <h2>Account Created!</h2>
            <p>Your email has been verified and your account is ready. You can now log in.</p>
            <a href="<?php echo ADMIN_URL; ?>/login.php?verified=1" class="btn btn-primary">
                <i class="fas fa-sign-in-alt"></i> Go to Login
            </a>
        </div>
    </div>

<script>
(function() {
    var email = <?php echo json_encode($email); ?>;
    var apiUrl = '<?php echo ADMIN_URL; ?>/api/registration-otp.php';
    var inputs = document.querySelectorAll('#otpGroup input');
    var btnVerify = document.getElementById('btnVerify');
    var resendLink = document.getElementById('resendLink');
    var resendTimerSpan = document.getElementById('resendTimer');
    var countdownEl = document.getElementById('countdown');
    var timerArea = document.getElementById('timerArea');
    var statusMsg = document.getElementById('statusMsg');

    // ── OTP input UX ──
    inputs.forEach(function(inp, idx) {
        inp.addEventListener('input', function() {
            this.value = this.value.replace(/\D/g, '').slice(0, 1);
            if (this.value && idx < inputs.length - 1) inputs[idx + 1].focus();
        });
        inp.addEventListener('keydown', function(e) {
            if (e.key === 'Backspace' && !this.value && idx > 0) {
                inputs[idx - 1].focus();
            }
        });
        inp.addEventListener('paste', function(e) {
            e.preventDefault();
            var paste = (e.clipboardData || window.clipboardData).getData('text').replace(/\D/g, '');
            for (var i = 0; i < Math.min(paste.length, inputs.length); i++) {
                inputs[i].value = paste[i];
            }
            if (paste.length >= inputs.length) inputs[inputs.length - 1].focus();
        });
    });

    function getOTP() {
        var otp = '';
        inputs.forEach(function(inp) { otp += inp.value; });
        return otp;
    }

    function setInputState(state) {
        inputs.forEach(function(inp) {
            inp.classList.remove('error-border', 'success-border');
            if (state) inp.classList.add(state);
        });
    }

    function showMsg(text, type) {
        statusMsg.style.display = 'flex';
        statusMsg.className = 'status-msg ' + type;
        var icon = type === 'error' ? 'exclamation-triangle' : type === 'info' ? 'spinner fa-spin' : 'check-circle';
        statusMsg.innerHTML = '<i class="fas fa-' + icon + '"></i> ' + text;
    }
    function hideMsg() { statusMsg.style.display = 'none'; }

    // ── Countdown timer (from real server-side expiry) ──
    var otpExpiry = <?php echo (int)$otpRemainingSeconds; ?>; // seconds remaining

    // Show correct initial time (not always 5:00)
    if (otpExpiry <= 0) {
        countdownEl.textContent = '0:00';
        timerArea.innerHTML = '<span class="countdown">OTP expired</span>';
        showMsg('Your OTP has expired. Please request a new one or go back to register.', 'error');
        btnVerify.disabled = true;
    } else {
        var im = Math.floor(otpExpiry / 60), is = otpExpiry % 60;
        countdownEl.textContent = im + ':' + (is < 10 ? '0' : '') + is;
    }

    var timerInterval = setInterval(function() {
        otpExpiry--;
        if (otpExpiry <= 0) {
            clearInterval(timerInterval);
            countdownEl.textContent = '0:00';
            timerArea.innerHTML = '<span class="countdown">OTP expired</span>';
            showMsg('Your OTP has expired. Please go back and request a new one.', 'error');
            btnVerify.disabled = true;
            return;
        }
        var m = Math.floor(otpExpiry / 60), s = otpExpiry % 60;
        countdownEl.textContent = m + ':' + (s < 10 ? '0' : '') + s;
    }, 1000);

    // ── Resend cooldown (30 seconds) ──
    var resendCooldown = 30;
    var resendInterval = setInterval(function() {
        resendCooldown--;
        if (resendCooldown <= 0) {
            clearInterval(resendInterval);
            resendLink.classList.remove('disabled');
            resendTimerSpan.textContent = '';
            return;
        }
        resendTimerSpan.textContent = '(wait ' + resendCooldown + 's)';
    }, 1000);

    // ── Submit OTP ──
    window.submitOTP = function() {
        hideMsg();
        var otp = getOTP();
        if (otp.length !== 6 || !/^\d{6}$/.test(otp)) {
            showMsg('Please enter a valid 6-digit OTP.', 'error');
            setInputState('error-border');
            return;
        }

        btnVerify.disabled = true;
        btnVerify.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Verifying...';
        showMsg('Verifying your OTP...', 'info');

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'verify', email: email, otp: otp })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                setInputState('success-border');
                showMsg('Email verified! Creating your account...', 'success-box');
                clearInterval(timerInterval);

                // Show success modal after a brief pause
                setTimeout(function() {
                    document.getElementById('successModal').classList.add('active');
                    // Auto-redirect after 4 seconds
                    setTimeout(function() {
                        window.location.href = '<?php echo ADMIN_URL; ?>/login.php?verified=1';
                    }, 4000);
                }, 800);
            } else {
                btnVerify.disabled = false;
                btnVerify.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';

                if (data.redirect) {
                    // Attempts exhausted — redirect back
                    showMsg(data.message, 'error');
                    setTimeout(function() {
                        window.location.href = '<?php echo ADMIN_URL; ?>/register.php?otp_error=exhausted';
                    }, 2000);
                    return;
                }

                setInputState('error-border');
                showMsg(data.message || 'Invalid OTP.', 'error');
                // Clear inputs for retry
                inputs.forEach(function(inp) { inp.value = ''; });
                inputs[0].focus();
            }
        })
        .catch(function() {
            btnVerify.disabled = false;
            btnVerify.innerHTML = '<i class="fas fa-check-circle"></i> Verify OTP';
            showMsg('Network error. Please try again.', 'error');
        });
    };

    // ── Resend OTP ──
    window.resendOTP = function() {
        if (resendLink.classList.contains('disabled')) return;
        hideMsg();

        resendLink.classList.add('disabled');
        showMsg('Resending OTP...', 'info');

        fetch(apiUrl, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ action: 'resend', email: email })
        })
        .then(function(r) { return r.json(); })
        .then(function(data) {
            if (data.success) {
                showMsg('New OTP sent! Check your email.', 'success-box');
                // Reset countdown
                otpExpiry = 5 * 60;
                btnVerify.disabled = false;
                timerArea.innerHTML = 'OTP expires in <span class="countdown" id="countdown">5:00</span>';
                countdownEl = document.getElementById('countdown');
                // New resend cooldown
                resendCooldown = 30;
                resendTimerSpan.textContent = '(wait 30s)';
                resendInterval = setInterval(function() {
                    resendCooldown--;
                    if (resendCooldown <= 0) {
                        clearInterval(resendInterval);
                        resendLink.classList.remove('disabled');
                        resendTimerSpan.textContent = '';
                        return;
                    }
                    resendTimerSpan.textContent = '(wait ' + resendCooldown + 's)';
                }, 1000);
                // Clear inputs
                inputs.forEach(function(inp) { inp.value = ''; });
                setInputState('');
                inputs[0].focus();
            } else {
                showMsg(data.message || 'Failed to resend OTP.', 'error');
                // Re-enable after a short delay
                setTimeout(function() { resendLink.classList.remove('disabled'); }, 3000);
            }
        })
        .catch(function() {
            showMsg('Network error. Please try again.', 'error');
            setTimeout(function() { resendLink.classList.remove('disabled'); }, 3000);
        });
    };
})();
</script>
</body>
</html>
