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

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Get form data
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
    $imagePath = null;
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
        if (addProduct($name, $description, $price, $quantity, $imagePath)) {
            $_SESSION['success_message'] = 'Product added successfully!';
            header("Location: products.php");
            exit();
        } else {
            $error_message = 'An error occurred while adding the product. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Add Product - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 600px;">
        <h1 style="margin-bottom: 2rem;">Add New Product</h1>

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
                        placeholder="Enter product name" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="description">Description <span class="required">*</span></label>
                    <textarea 
                        id="description" 
                        name="description" 
                        class="form-control" 
                        placeholder="Enter product description" 
                        rows="4" 
                        required
                    ></textarea>
                </div>

                <div class="form-group">
                    <label for="price">Price (₹) <span class="required">*</span></label>
                    <input 
                        type="number" 
                        id="price" 
                        name="price" 
                        class="form-control" 
                        placeholder="0.00" 
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
                        placeholder="0" 
                        min="0" 
                        required
                    >
                </div>

                <div class="form-group">
                    <label for="image">Product Image (Optional)</label>
                    <input 
                        type="file" 
                        id="image" 
                        name="image" 
                        class="form-control" 
                        accept="image/jpeg,image/png"
                    >
                    <small>Allowed formats: JPG, PNG. Max size: 5MB</small>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 2rem;">
                    <button type="submit" class="btn btn-primary" style="flex: 1;">Add Product</button>
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
