<?php
/**
 * Admin Panel Header
 * SDO ATLAS - Schools Division Office Authority to Travel and Locator Approval System
 */

require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/../models/LocatorSlip.php';
require_once __DIR__ . '/../models/AuthorityToTravel.php';

$auth = auth();
$auth->requireLogin();

$currentUser = $auth->getUser();
$currentToken = $auth->getToken();
$currentPage = basename($_SERVER['PHP_SELF'], '.php');

// Get notification counts for sidebar badges
$notificationCounts = [];
try {
    if ($auth->isApprover() || $auth->isUnitHead()) {
        $lsModel = new LocatorSlip();
        $atModel = new AuthorityToTravel();
        
        // Use effective role for OIC-aware counts
        $effectiveRoleId = $auth->getEffectiveRoleId();
        $effectiveRoleName = $auth->getEffectiveRoleName();
        
        // For unit heads (or OICs acting as unit heads), get filtered counts
        if ($auth->isUnitHead() && !$auth->isApprover()) {
            $notificationCounts['ls_pending'] = 0; // Locator slips may have different routing
            $notificationCounts['at_pending'] = $atModel->getPendingCountForRole(
                $effectiveRoleName, 
                $effectiveRoleId
            );
        } else {
            $notificationCounts['ls_pending'] = $lsModel->getStatistics()['pending'] ?? 0;
            $notificationCounts['at_pending'] = $atModel->getStatistics()['pending'] ?? 0;
        }
        $notificationCounts['total_pending'] = $notificationCounts['ls_pending'] + $notificationCounts['at_pending'];
    }
    
    if ($auth->isSuperAdmin()) {
        require_once __DIR__ . '/../models/AdminUser.php';
        $userModel = new AdminUser();
        $notificationCounts['pending_users'] = $userModel->getPendingRegistrationsCount();
    }
} catch (Exception $e) {
    $notificationCounts = ['ls_pending' => 0, 'at_pending' => 0, 'total_pending' => 0, 'pending_users' => 0];
}

// Get page title
$pageTitles = [
    'index' => 'Dashboard',
    'locator-slips' => 'Locator Slips',
    'authority-to-travel' => 'Authority to Travel',
    'my-requests' => 'My Requests',
    'users' => 'User Management',
    'logs' => 'Activity Logs',
    'profile' => 'My Profile',
    'oic-management' => 'OIC Management'
];

$pageTitle = $pageTitles[$currentPage] ?? 'Admin Panel';

/**
 * Helper to generate URL with token
 */
function navUrl($path) {
    global $currentToken;
    if ($currentToken) {
        $separator = strpos($path, '?') !== false ? '&' : '?';
        return ADMIN_URL . $path . $separator . 'token=' . $currentToken;
    }
    return ADMIN_URL . $path;
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?> - <?php echo ADMIN_TITLE; ?></title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=JetBrains+Mono:wght@500&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.1/css/all.min.css">
    <link href="https://unpkg.com/boxicons@2.1.4/css/boxicons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?php echo ADMIN_URL; ?>/assets/css/admin.css">
</head>
<body>
    <div class="admin-layout">
        <!-- Sidebar -->
        <aside class="sidebar" id="sidebar">
            <div class="sidebar-header">
                <div class="logo">
                    <img src="<?php echo ADMIN_URL; ?>/assets/logos/sdo-logo.jpg" alt="SDO Logo" class="logo-img" style="width: 45px; height: 45px; border-radius: 50%; object-fit: contain; background: transparent; border: none; box-shadow: none;">
                    <div class="logo-text">
                        <span class="logo-title">SDO ATLAS</span>
                        <span class="logo-subtitle">Travel & Locator</span>
                    </div>
                </div>
            </div>
            
            <nav class="sidebar-nav">
                <a href="<?php echo navUrl('/'); ?>" class="nav-item <?php echo $currentPage === 'index' ? 'active' : ''; ?>" data-tooltip="Dashboard">
                    <span class="nav-icon">
                        <i class="fas fa-chart-line"></i>
                        <?php if (($auth->isApprover() || $auth->isUnitHead()) && ($notificationCounts['total_pending'] ?? 0) > 0): ?>
                        <span class="nav-badge"><?php echo $notificationCounts['total_pending'] > 99 ? '99+' : $notificationCounts['total_pending']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Dashboard</span>
                </a>
                
                <?php if ($auth->isEmployee()): ?>
                <!-- Employee-only navigation -->
                <a href="<?php echo navUrl('/my-requests.php'); ?>" class="nav-item <?php echo $currentPage === 'my-requests' ? 'active' : ''; ?>" data-tooltip="My Requests">
                    <span class="nav-icon"><i class="fas fa-file-alt"></i></span>
                    <span class="nav-text">My Requests</span>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->isApprover() || $auth->isUnitHead()): ?>
                <!-- Approver/Unit Head navigation -->
                <a href="<?php echo navUrl('/locator-slips.php'); ?>" class="nav-item <?php echo $currentPage === 'locator-slips' ? 'active' : ''; ?>" data-tooltip="Locator Slips">
                    <span class="nav-icon">
                        <i class="fas fa-map-marker-alt"></i>
                        <?php if (($notificationCounts['ls_pending'] ?? 0) > 0): ?>
                        <span class="nav-badge"><?php echo $notificationCounts['ls_pending'] > 99 ? '99+' : $notificationCounts['ls_pending']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Locator Slips</span>
                </a>
                
                <a href="<?php echo navUrl('/authority-to-travel.php'); ?>" class="nav-item <?php echo $currentPage === 'authority-to-travel' ? 'active' : ''; ?>" data-tooltip="Authority to Travel">
                    <span class="nav-icon">
                        <i class="fas fa-plane"></i>
                        <?php if (($notificationCounts['at_pending'] ?? 0) > 0): ?>
                        <span class="nav-badge"><?php echo $notificationCounts['at_pending'] > 99 ? '99+' : $notificationCounts['at_pending']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Authority to Travel</span>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->isUnitHead()): ?>
                <!-- Unit Head only navigation -->
                <a href="<?php echo navUrl('/oic-management.php'); ?>" class="nav-item <?php echo $currentPage === 'oic-management' ? 'active' : ''; ?>" data-tooltip="OIC Management">
                    <span class="nav-icon"><i class="fas fa-user-shield"></i></span>
                    <span class="nav-text">OIC Management</span>
                </a>
                <?php endif; ?>
                
                <div class="nav-divider"></div>
                
                <?php if ($auth->isSuperAdmin()): ?>
                <a href="<?php echo navUrl('/users.php'); ?>" class="nav-item <?php echo $currentPage === 'users' ? 'active' : ''; ?>" data-tooltip="Users">
                    <span class="nav-icon">
                        <i class="fas fa-users"></i>
                        <?php if (($notificationCounts['pending_users'] ?? 0) > 0): ?>
                        <span class="nav-badge"><?php echo $notificationCounts['pending_users']; ?></span>
                        <?php endif; ?>
                    </span>
                    <span class="nav-text">Users</span>
                </a>
                <?php endif; ?>
                
                <?php if ($auth->isApprover()): ?>
                <a href="<?php echo navUrl('/logs.php'); ?>" class="nav-item <?php echo $currentPage === 'logs' ? 'active' : ''; ?>" data-tooltip="Activity Logs">
                    <span class="nav-icon"><i class="fas fa-history"></i></span>
                    <span class="nav-text">Activity Logs</span>
                </a>
                <?php endif; ?>
                
                <a href="<?php echo navUrl('/profile.php'); ?>" class="nav-item <?php echo $currentPage === 'profile' ? 'active' : ''; ?>" data-tooltip="My Profile">
                    <span class="nav-icon"><i class="fas fa-user-cog"></i></span>
                    <span class="nav-text">My Profile</span>
                </a>
            </nav>
            
            <div class="sidebar-footer">
                <div class="user-info">
                    <div class="user-avatar-placeholder">
                        <?php echo strtoupper(substr($currentUser['full_name'], 0, 1)); ?>
                    </div>
                    <div class="user-details">
                        <span class="user-name"><?php echo htmlspecialchars($currentUser['full_name']); ?></span>
                        <span class="user-role"><?php echo htmlspecialchars($currentUser['role_name']); ?></span>
                    </div>
                </div>
                <a href="<?php echo navUrl('/login.php?logout=1'); ?>" class="logout-btn-new" title="Logout">
                    <i class="bx bx-log-out"></i>
                </a>
            </div>
        </aside>

        <!-- Main Content -->
        <main class="main-content">
            <header class="top-bar">
                <div class="top-bar-left">
                    <button class="mobile-menu-toggle" id="mobileMenuToggle"><i class="fas fa-bars"></i></button>
                    <button class="desktop-sidebar-toggle" id="desktopSidebarToggle" title="Toggle Sidebar">
                        <i class="fas fa-columns"></i>
                    </button>
                    <h1 class="page-title"><?php echo $pageTitle; ?></h1>
                </div>
                <div class="top-bar-right">
                    <span class="current-date"><?php echo date('l, F j, Y'); ?></span>
                </div>
            </header>
            
            <div class="content-wrapper">
