<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

// Clear admin session
global $db;
$stmt = $db->prepare("UPDATE admin_sessions SET is_active = 0 WHERE admin_id = ?");
$stmt->bind_param("i", $_SESSION['admin_id']);
$stmt->execute();

session_destroy();
header("Location: login.php");
exit();
?>
