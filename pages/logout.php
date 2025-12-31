<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    redirectToLogin();
}

// Clear session
global $db;
$stmt = $db->prepare("UPDATE sessions SET is_active = 0 WHERE user_id = ?");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();

session_destroy();
redirectToLogin();
?>
