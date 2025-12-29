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
        if ($uploadResult !== true) {
            $errors[] = $uploadResult;
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
    <footer class="footer" style="margin-top: 4rem;">
        <div class="footer-content">
            <div class="footer-section">
                <img src="../assets/images/logo1.jpg" alt="<?php echo COMPANY_NAME; ?>" class="footer-logo">
                <p>Leading B2B retailer ordering and GST billing platform.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="logout.php">Logout</a></li>
                </ul>
            </div>
            
            <div class="footer-section">
                <h3>Contact Info</h3>
                <p><strong>Email:</strong> <?php echo COMPANY_EMAIL; ?></p>
                <p><strong>Phone:</strong> <?php echo COMPANY_PHONE; ?></p>
                <p><strong>Address:</strong> <?php echo COMPANY_ADDRESS; ?></p>
            </div>
            
            <div class="footer-section">
                <h3>Location</h3>
                <iframe class="footer-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3887.5734261439226!2d77.59717!3d13.051213!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae19bba11ce5dd%3A0xed8c3b0e9bcfd4af!2sBangalore%2C%20Karnataka%2C%20India!5e0!3m2!1sen!2sin!4v1640000000000" allowfullscreen="" loading="lazy"></iframe>
            </div>
        </div>
        
        <div class="footer-bottom">
            <p>&copy; <?php echo date('Y'); ?> <?php echo COMPANY_NAME; ?>. All rights reserved.</p>
        </div>
    </footer>

    <script src="../assets/js/main.js"></script>
</body>
</html>
