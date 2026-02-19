<?php
/**
 * Employee Self-Registration Page
 * SDO ALPAS - Schools Division Office Authority to Travel, Locator and Pass slip Approval System
 *
 * Flow:
 *  1. User fills out the form.
 *  2. Clicks "Verify Email" → data is sent via AJAX to the registration-otp API.
 *  3. Temporary record + OTP are created; OTP email is sent.
 *  4. User is redirected to verify-email.php to enter the OTP.
 *  5. On success the account is created automatically (active, no admin approval needed).
 */

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");

require_once __DIR__ . '/../config/admin_config.php';
require_once __DIR__ . '/../config/database.php';
require_once __DIR__ . '/../models/AdminUser.php';

$error = '';
$success = '';
$formData = [
    'full_name' => '',
    'email' => '',
    'employee_no' => '',
    'employee_position' => '',
    'office_id' => ''
];

// Get offices from master database table (legacy)
$offices = getSDOOfficesFromDB(true);
// Top-level offices (OSDS, SGOD, CID) and units-by-office for cascading dropdowns
$topOffices = getSDOOfficesForOfficeDropdown();
$unitsByOffice = getUnitsByOfficeForJs();

// Show error if redirected back from OTP page
if (isset($_GET['otp_error'])) {
    $error = match ($_GET['otp_error']) {
        'exhausted' => 'OTP input attempts exhausted. Please fill in the form and verify your email again.',
        'expired'   => 'Your OTP has expired. Please fill in the form and verify your email again.',
        'invalid'   => 'Verification session invalid. Please register again.',
        default     => 'An error occurred. Please try again.'
    };
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo ADMIN_TITLE; ?></title>
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
        
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }
        
        body {
            font-family: 'Plus Jakarta Sans', -apple-system, BlinkMacSystemFont, sans-serif;
            background: var(--bg-dark);
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 20px;
        }
        
        .register-container {
            width: 100%;
            max-width: 480px;
        }
        
        .register-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
        }
        
        .register-header {
            text-align: center;
            margin-bottom: 24px;
        }
        
        .register-header h1 {
            color: var(--text);
            font-size: 1.4rem;
            font-weight: 700;
            margin-bottom: 8px;
        }
        
        .register-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 12px;
        }
        
        .form-label {
            display: block;
            color: var(--text);
            font-size: 0.8rem;
            font-weight: 500;
            margin-bottom: 6px;
        }
        
        .form-label .required {
            color: var(--error);
        }
        
        .form-control {
            width: 100%;
            padding: 10px 12px;
            font-size: 0.9rem;
            font-family: inherit;
            background: rgba(0, 0, 0, 0.3);
            border: 1px solid var(--border);
            border-radius: 8px;
            color: var(--text);
            transition: all 0.2s ease;
        }
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }
        
        select.form-control {
            cursor: pointer;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.85rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .info-box {
            background: rgba(59, 130, 246, 0.1);
            border: 1px solid rgba(59, 130, 246, 0.3);
            color: #93c5fd;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.8rem;
        }
        
        .info-box i {
            margin-right: 8px;
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
        
        .btn-primary:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(15, 76, 117, 0.4);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border: 1px solid var(--border);
            margin-top: 12px;
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
        }
        
        .form-hint {
            font-size: 0.75rem;
            color: var(--text-muted);
            margin-top: 4px;
        }
        
        @media (max-width: 480px) {
            .form-row {
                grid-template-columns: 1fr;
            }
            
            .register-card {
                padding: 24px 20px;
            }
        }
    </style>
</head>
<body>
    <div class="register-container">
        <div class="register-card">
            <div class="register-header">
                <h1><i class="fas fa-user-plus"></i> Create Account</h1>
                <p>Register as an SDO Employee to file travel requests</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <div class="info-box">
                <i class="fas fa-info-circle"></i>
                An OTP will be sent to your email for verification. After verifying, your account will be created and ready to use immediately.
            </div>

            <form id="registerForm" onsubmit="return false;">
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="full_name">Full Name <span class="required">*</span></label>
                        <input type="text" class="form-control" id="full_name" name="full_name" 
                               value="<?php echo htmlspecialchars($formData['full_name']); ?>"
                               placeholder="Juan Dela Cruz" required>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="email">Email <span class="required">*</span></label>
                        <input type="email" class="form-control" id="email" name="email" 
                               value="<?php echo htmlspecialchars($formData['email']); ?>"
                               placeholder="user@deped.gov.ph" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="reg_office">Office</label>
                        <select class="form-control" id="reg_office" name="office" aria-label="Select office to enable Unit/Section">
                            <option value="">-- Select Office --</option>
                            <?php foreach ($topOffices as $o): ?>
                            <option value="<?php echo htmlspecialchars($o['code']); ?>"><?php echo htmlspecialchars($o['name']); ?></option>
                            <?php endforeach; ?>
                        </select>
                        <span class="form-hint">OSDS, SGOD, or CID</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="reg_unit_id">Unit/Section</label>
                        <select class="form-control" id="reg_unit_id" name="office_id" disabled>
                            <option value="">-- Select Unit/Section --</option>
                        </select>
                        <span class="form-hint">Select an Office first</span>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="password">Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="password" name="password" 
                               placeholder="Min. 8 characters" required>
                        <span class="form-hint">Minimum 8 characters</span>
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="password_confirm">Confirm Password <span class="required">*</span></label>
                        <input type="password" class="form-control" id="password_confirm" name="password_confirm" 
                               placeholder="Re-enter password" required>
                    </div>
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label class="form-label" for="employee_no">Employee No. (optional)</label>
                        <input type="text" class="form-control" id="employee_no" name="employee_no" 
                               value="<?php echo htmlspecialchars($formData['employee_no']); ?>"
                               placeholder="E-12345">
                    </div>
                    <div class="form-group">
                        <label class="form-label" for="employee_position">Position (optional)</label>
                        <input type="text" class="form-control" id="employee_position" name="employee_position" 
                               value="<?php echo htmlspecialchars($formData['employee_position']); ?>"
                               placeholder="Teacher I">
                    </div>
                </div>

                <!-- Status area for AJAX feedback -->
                <div id="registerStatus" style="display:none; margin-bottom:12px;"></div>

                <button type="button" id="btnVerifyEmail" class="btn btn-primary" onclick="handleVerifyEmail()">
                    <i class="fas fa-envelope"></i> Verify Email &amp; Register
                </button>
                
                <a href="<?php echo ADMIN_URL; ?>/login.php" class="btn btn-secondary">
                    <i class="fas fa-arrow-left"></i> Back to Login
                </a>
            </form>
        </div>
    </div>
<script>
var unitsByOfficeReg = <?php echo json_encode($unitsByOffice); ?>;
(function() {
    var selOffice = document.getElementById('reg_office');
    var selUnit = document.getElementById('reg_unit_id');
    if (!selOffice || !selUnit) return;
    selOffice.addEventListener('change', function() {
        var code = selOffice.value;
        selUnit.innerHTML = '<option value="">-- Select Unit/Section --</option>';
        selUnit.disabled = true;
        if (code && unitsByOfficeReg[code]) {
            var units = unitsByOfficeReg[code];
            for (var i = 0; i < units.length; i++) {
                var opt = document.createElement('option');
                opt.value = units[i].id;
                opt.textContent = units[i].office_name || units[i].office_code || units[i].id;
                selUnit.appendChild(opt);
            }
            selUnit.disabled = false;
        }
    });
})();

function showStatus(msg, type) {
    var el = document.getElementById('registerStatus');
    el.style.display = 'block';
    el.className = type === 'error' ? 'error-message' : 'info-box';
    el.innerHTML = '<i class="fas fa-' + (type === 'error' ? 'exclamation-triangle' : 'spinner fa-spin') + '"></i> ' + msg;
}
function hideStatus() {
    document.getElementById('registerStatus').style.display = 'none';
}

function handleVerifyEmail() {
    hideStatus();
    var fullName = document.getElementById('full_name').value.trim();
    var email    = document.getElementById('email').value.trim();
    var password = document.getElementById('password').value;
    var passwordConfirm = document.getElementById('password_confirm').value;
    var officeId = document.getElementById('reg_unit_id').value;
    var employeeNo = document.getElementById('employee_no').value.trim();
    var employeePosition = document.getElementById('employee_position').value.trim();

    // Client-side validation
    if (!fullName) { showStatus('Full name is required.', 'error'); return; }
    if (!email) { showStatus('Email address is required.', 'error'); return; }
    if (!/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) { showStatus('Please enter a valid email address.', 'error'); return; }
    if (!password) { showStatus('Password is required.', 'error'); return; }
    if (password.length < 8) { showStatus('Password must be at least 8 characters long.', 'error'); return; }
    if (password !== passwordConfirm) { showStatus('Passwords do not match.', 'error'); return; }

    // Disable button
    var btn = document.getElementById('btnVerifyEmail');
    btn.disabled = true;
    btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Sending OTP...';

    showStatus('Sending verification email...', 'info');

    fetch('<?php echo ADMIN_URL; ?>/api/registration-otp.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
            action: 'request',
            full_name: fullName,
            email: email,
            password: password,
            password_confirm: passwordConfirm,
            office_id: officeId || null,
            employee_no: employeeNo || null,
            employee_position: employeePosition || null
        })
    })
    .then(function(r) { return r.json(); })
    .then(function(data) {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-envelope"></i> Verify Email &amp; Register';

        if (data.success) {
            // Redirect to OTP input page
            window.location.href = '<?php echo ADMIN_URL; ?>/verify-email.php?email=' + encodeURIComponent(email);
        } else {
            showStatus(data.message || 'An error occurred.', 'error');
        }
    })
    .catch(function() {
        btn.disabled = false;
        btn.innerHTML = '<i class="fas fa-envelope"></i> Verify Email &amp; Register';
        showStatus('Network error. Please try again.', 'error');
    });
}
</script>
</body>
</html>
