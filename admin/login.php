<?php
/**
 * Admin Login Page
 * SDO ATLAS - Schools Division Office Authority to Travel and Locator Approval System
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();

// Handle logout
if (isset($_GET['logout'])) {
    $auth->logout();
    header('Location: ' . ADMIN_URL . '/login.php?logged_out=1');
    exit;
}

// DO NOT redirect if already logged in - allow multiple accounts
// User can login with different account in new tab

$error = '';
$success = '';
$email = '';

// Check for messages
if (isset($_GET['logged_out'])) {
    $success = '';
}
if (isset($_GET['registered'])) {
    $success = 'Registration successful! Please wait for admin approval before logging in.';
}
if (isset($_GET['verified'])) {
    $success = 'Your email has been verified and your account is ready. You can now log in!';
}
if (isset($_GET['pending'])) {
    $error = 'Your account is pending approval. Please contact the administrator.';
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';
    
    if (empty($email) || empty($password)) {
        $error = 'Please enter both email and password.';
    } else {
        $token = $auth->login($email, $password);
        
        if ($token) {
            // Redirect to dashboard with token
            $redirect = $_GET['redirect'] ?? ADMIN_URL . '/';
            
            // Append token to redirect URL
            $separator = strpos($redirect, '?') !== false ? '&' : '?';
            header('Location: ' . $redirect . $separator . 'token=' . $token);
            exit;
        } else {
            // Check if account exists but is pending
            require_once __DIR__ . '/../models/AdminUser.php';
            $userModel = new AdminUser();
            $user = $userModel->getByEmail($email);
            
            if ($user && $user['status'] === 'pending') {
                $error = 'Your account is pending approval. Please contact the administrator.';
            } elseif ($user && $user['status'] === 'inactive') {
                $error = 'Your account has been deactivated. Please contact the administrator.';
            } else {
                $error = 'Invalid email or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - <?php echo ADMIN_TITLE; ?></title>
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
        
        .login-container {
            width: 100%;
            max-width: 420px;
        }
        
        .login-card {
            background: var(--bg-card);
            border: 1px solid var(--border);
            border-radius: 20px;
            padding: 32px 28px;
            backdrop-filter: blur(20px);
        }
        
        .login-header {
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
        
        .logo-badge::after {
            content: none;
        }
        
        .logo-badge i {
            font-size: 2.5rem;
            color: white;
        }

        .logo-badge img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            border-radius: 50%;
        }
        
        .login-header h1 {
            color: var(--text);
            font-size: 1.5rem;
            font-weight: 700;
            margin-bottom: 6px;
        }
        
        .login-header p {
            color: var(--text-muted);
            font-size: 0.85rem;
            line-height: 1.5;
        }
        
        .form-group {
            margin-bottom: 16px;
        }
        
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
        
        .form-control::placeholder {
            color: var(--text-muted);
        }
        
        .form-control:focus {
            outline: none;
            border-color: var(--primary-light);
            background: rgba(0, 0, 0, 0.4);
        }

        .forgot-password-link {
            text-align: right;
            margin-bottom: 16px;
            margin-top: -8px;
        }

        .forgot-password-link a {
            color: var(--accent);
            font-size: 0.82rem;
            text-decoration: none;
            font-weight: 500;
            transition: color 0.2s ease;
        }

        .forgot-password-link a:hover {
            color: var(--gold);
            text-decoration: underline;
        }
        
        .error-message {
            background: rgba(239, 68, 68, 0.1);
            border: 1px solid rgba(239, 68, 68, 0.3);
            color: #fca5a5;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .success-message {
            background: rgba(16, 185, 129, 0.1);
            border: 1px solid rgba(16, 185, 129, 0.3);
            color: #6ee7b7;
            padding: 12px 16px;
            border-radius: 10px;
            margin-bottom: 20px;
            font-size: 0.9rem;
            display: flex;
            align-items: center;
            gap: 10px;
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
        
        .divider {
            display: flex;
            align-items: center;
            gap: 12px;
            margin: 24px 0;
            color: var(--text-muted);
            font-size: 0.8rem;
        }
        
        .divider::before,
        .divider::after {
            content: '';
            flex: 1;
            height: 1px;
            background: var(--border);
        }
        
        .btn-secondary {
            background: rgba(255, 255, 255, 0.05);
            color: var(--text);
            border: 1px solid var(--border);
        }
        
        .btn-secondary:hover {
            background: rgba(255, 255, 255, 0.1);
            border-color: var(--text-muted);
        }
        
        .login-footer {
            text-align: center;
            margin-top: 24px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
        }
        
        .login-footer p {
            color: var(--text-muted);
            font-size: 0.85rem;
        }
        
        .login-footer a {
            color: var(--accent);
            text-decoration: none;
            font-weight: 500;
        }
        
        .login-footer a:hover {
            text-decoration: underline;
        }
        
        .brand-footer {
            text-align: center;
            margin-top: 20px;
            color: var(--text-muted);
            font-size: 0.75rem;
        }
        
        @media (max-width: 480px) {
            .login-card {
                padding: 24px 20px;
            }
            
            .logo-badge {
                width: 70px;
                height: 70px;
            }
            
            .logo-badge i {
                font-size: 2rem;
            }
        }
    </style>
</head>
<body>
    <div class="login-container">
        <div class="login-card">
            <div class="login-header">
                <div class="logo-badge">
                    <img src="assets/logos/sdo-logo.jpg" alt="SDO Logo">
                </div>
                <h1>SDO ATLAS</h1>
                <p>Authority to Travel and Locator Approval System<br>Schools Division Office of San Pedro City</p>
            </div>

            <?php if ($error): ?>
            <div class="error-message">
                <i class="fas fa-exclamation-triangle"></i>
                <?php echo htmlspecialchars($error); ?>
            </div>
            <?php endif; ?>

            <?php if ($success): ?>
            <div class="success-message">
                <i class="fas fa-check-circle"></i>
                <?php echo htmlspecialchars($success); ?>
            </div>
            <?php endif; ?>

            <form method="POST" action="">
                <div class="form-group">
                    <label class="form-label" for="email">Email Address</label>
                    <input type="email" class="form-control" id="email" name="email" 
                           value="<?php echo htmlspecialchars($email); ?>"
                           placeholder="your.email@deped.gov.ph" required>
                </div>
                
                <div class="form-group">
                    <label class="form-label" for="password">Password</label>
                    <input type="password" class="form-control" id="password" name="password" 
                           placeholder="Enter your password" required>
                </div>

                <div class="forgot-password-link">
                    <a href="<?php echo ADMIN_URL; ?>/forgot-password.php">
                        <i class="fas fa-key"></i> Forgot Password?
                    </a>
                </div>
                
                <button type="submit" class="btn btn-primary">
                    <i class="fas fa-sign-in-alt"></i> Sign In
                </button>
            </form>

            <div class="divider">Don't have an account?</div>
            
            <a href="<?php echo ADMIN_URL; ?>/register.php" class="btn btn-secondary">
                <i class="fas fa-user-plus"></i> Create Account
            </a>

            <div class="login-footer">
                <p>Need help? Contact the IT Office</p>
            </div>
        </div>

        <div class="brand-footer">
            <p>&copy; <?php echo date('Y'); ?> SDO ATLAS - Department of Education<br>
            Schools Division Office of San Pedro City</p>
        </div>
    </div>
</body>
</html>
