<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

$admin = getAdminData($_SESSION['admin_id']);
$error_message = '';
$success_message = '';

// Get product ID
$productId = intval($_GET['id'] ?? 0);
if ($productId <= 0) {
    redirectToAdminDashboard();
}

$product = getProduct($productId);
if (!$product) {
    $_SESSION['error_message'] = 'Product not found';
    header("Location: products.php");
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize($_POST['name'] ?? '');
    $description = sanitize($_POST['description'] ?? '');
    $price = floatval($_POST['price'] ?? 0);
    $quantity = intval($_POST['quantity'] ?? 0);
    
    // Validate inputs
    $errors = [];
    
    if (empty($name) || strlen($name) < 2) {
        $errors[] = 'Product name must be at least 2 characters';
    }
    
    if (empty($description)) {
        $errors[] = 'Product description is required';
    }
    
    if ($price <= 0) {
        $errors[] = 'Product price must be greater than 0';
    }
    
    if ($quantity < 0) {
        $errors[] = 'Product quantity cannot be negative';
    }
    
    // Handle image upload
    $imagePath = $product['image_path'];
    if (!empty($_FILES['image']['name'])) {
        $uploadResult = validateFileUpload($_FILES['image']);
        if (!$uploadResult['success']) {
            $errors[] = $uploadResult['message'];
        } else {
            // Process file upload
            $uploadDir = '../uploads/products/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }
            
            // Delete old image
            if ($product['image_path'] && file_exists($product['image_path'])) {
                unlink($product['image_path']);
            }
            
            $fileName = time() . '_' . basename($_FILES['image']['name']);
            $filePath = $uploadDir . $fileName;
            
            if (move_uploaded_file($_FILES['image']['tmp_name'], $filePath)) {
                $imagePath = 'uploads/products/' . $fileName;
            } else {
                $errors[] = 'Failed to upload image file';
            }
        }
    }
    
    if (!empty($errors)) {
        $error_message = implode('<br>', $errors);
    } else {
        if (updateProduct($productId, $name, $description, $price, $quantity, $imagePath)) {
            $_SESSION['success_message'] = 'Product updated successfully!';
            header("Location: products.php");
            exit();
        } else {
            $error_message = 'An error occurred while updating the product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit Product - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 600px;">
        <h1 style="margin-bottom: 2rem;">Edit Product</h1>

        <?php if ($error_message): ?>
            <div class="alert alert-danger">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <form method="POST" action="" enctype="multipart/form-data">
                <div class="form-group">
                    <label for="name">Product Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        class="form-control" 
                        value="<?php echo htmlspecialchars($product['name']); ?>" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        rows="4" 
                        required
                    ><?php echo htmlspecialchars($product['description']); ?></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price (₹) <span class="required">*</span></label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        class="form-control" 
                        value="<?php echo $product['price']; ?>" 
                        step="0.01" 
                        min="0" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="quantity">Quantity in Stock <span class="required">*</span></label>
                    <input 
                        type="number" 
                        id="quantity" 
                        name="quantity" 
                        class="form-control" 
                        value="<?php echo $product['quantity_in_stock']; ?>" 
                        min="0" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="image">Product Image (Optional)</label>
                    <?php if ($product['image_path']): ?>
                        <div style="margin-bottom: 1rem;">
                            <img src="../<?php echo htmlspecialchars($product['image_path']); ?>" 
                                 alt="<?php echo htmlspecialchars($product['name']); ?>" 
                                 style="max-width: 200px; max-height: 200px;">
                        </div>
                    <?php endif; ?>
                    <input 
                        type="file" 
                        id="image" 
                        name="image" 
                        class="form-control" 
                        accept="image/jpeg,image/png"
                    >
                    <small>Leave empty to keep current image. Allowed: JPG, PNG. Max: 5MB</small>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Update Product</button>
                    <a href="products.php" class="btn btn-secondary" style="flex: 1; text-align: center;">Cancel</a>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
