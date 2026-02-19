<?php
/**
 * Logout Handler
 * SDO ALPAS
 */

require_once __DIR__ . '/../includes/auth.php';

$auth = auth();
$auth->logout();

header('Location: ' . ADMIN_URL . '/login.php?logged_out=1');
exit;
