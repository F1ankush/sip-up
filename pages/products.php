<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Products - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" style="display: flex; align-items: center; text-decoration: none;">
               <img src="assets/images/logo1.jpg" alt="<?php echo SITE_NAME; ?>">
                 <!--<span><?php echo SITE_NAME; ?></span> -->
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="../index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li class="navbar-button-item"><a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a></li>
            <li class="navbar-button-item"><a href="login.php" class="btn btn-secondary btn-capsule">Login</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a>
            <a href="login.php" class="btn btn-secondary btn-capsule">Login</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Our Products</h1>
            <p>Browse our complete catalog of quality products</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <div style="background-color: #eff6ff; padding: 1rem; border-radius: 8px; border-left: 4px solid var(--primary-color); margin-bottom: 2rem;">
            <p><strong>Note:</strong> To place orders, please <a href="login.php">login</a> or <a href="apply.php">apply for a new account</a>.</p>
        </div>

        <div class="row">
            <?php if (empty($products)): ?>
                <div class="col-12">
                    <p style="text-align: center; font-size: 1.1rem;">No products available at the moment.</p>
                </div>
            <?php else: ?>
                <?php foreach ($products as $product): ?>
                    <div class="col-4">
                        <div class="product-card">
                            <div class="product-image" style="background-color: var(--light-gray); display: flex; align-items: center; justify-content: center; color: var(--medium-gray); font-size: 3rem;">
                                📦
                            </div>
                            <div class="product-details">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <?php if (!empty($product['description'])): ?>
                                    <p style="font-size: 0.9rem; color: #6b7280; margin-bottom: 0.5rem;">
                                        <?php echo htmlspecialchars(substr($product['description'], 0, 100)); ?>...
                                    </p>
                                <?php endif; ?>
                                <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                                <div class="product-stock">
                                    <span style="background-color: <?php echo $product['quantity_in_stock'] > 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>; color: white; padding: 0.3rem 0.6rem; border-radius: 4px;">
                                        <?php echo $product['quantity_in_stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                    </span>
                                    <small style="display: block; margin-top: 0.5rem;">Available: <?php echo $product['quantity_in_stock']; ?> units</small>
                                </div>
                                <a href="login.php" class="btn btn-primary btn-block" style="margin-top: 1rem;">View & Order</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>
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
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
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
