<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

// Get product ID
$productId = intval($_GET['id'] ?? 0);

if ($productId <= 0) {
    $_SESSION['error_message'] = 'Invalid product ID';
    header("Location: products.php");
    exit();
}

$product = getProduct($productId);
if (!$product) {
    $_SESSION['error_message'] = 'Product not found';
    header("Location: products.php");
    exit();
}

// Delete the product
if (deleteProduct($productId)) {
    $_SESSION['success_message'] = 'Product deleted successfully!';
} else {
    $_SESSION['error_message'] = 'Failed to delete product. Please try again.';
}

header("Location: products.php");
exit();
?>
