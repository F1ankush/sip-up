<?php
require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

$csrf_token = generateCSRFToken();
$products = getProducts();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Home - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="index.php" style="display: flex; align-items: center; text-decoration: none;">
                <img src="assets/images/logo1.jpg" alt="<?php echo SITE_NAME; ?>">
                 <!--<span><?php echo SITE_NAME; ?></span> --> 
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="index.php">Home</a></li>
            <li><a href="pages/about.php">About</a></li>
            <li><a href="pages/products.php">Products</a></li>
            <li><a href="pages/contact.php">Contact</a></li>
            <li class="navbar-button-item"><a href="pages/apply.php" class="btn btn-primary btn-capsule">Apply for Account</a></li>
            <li class="navbar-button-item"><a href="pages/login.php" class="btn btn-secondary btn-capsule">Login</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <a href="pages/apply.php" class="btn btn-primary btn-capsule">Apply for Account</a>
            <a href="pages/login.php" class="btn btn-secondary btn-capsule">Login</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Hero Section -->
    <section class="hero">
        <div class="container">
            <h1>Welcome to <?php echo COMPANY_NAME; ?></h1>
            <p>Premium B2B Retailer Ordering Platform with GST Billing</p>
            <a href="pages/apply.php" class="btn btn-primary btn-capsule" style="font-size: 1.1rem; padding: 0.8rem 2rem;">Start Your Account Application</a>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <!-- Featured Products Carousel -->
        <section class="carousel" style="margin-bottom: 3rem;">
            <div class="carousel-items">
                <div class="carousel-item" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
                    <div>
                        <h2>Premium Quality Products</h2>
                        <p>Sourced from trusted manufacturers</p>
                    </div>
                </div>
                <div class="carousel-item" style="background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
                    <div>
                        <h2>Competitive Pricing</h2>
                        <p>Best wholesale rates for retailers</p>
                    </div>
                </div>
                <div class="carousel-item" style="background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); display: flex; align-items: center; justify-content: center; color: white; text-align: center;">
                    <div>
                        <h2>Fast Delivery</h2>
                        <p>Quick order processing and shipping</p>
                    </div>
                </div>
            </div>
            
            <button class="carousel-nav prev">❮</button>
            <button class="carousel-nav next">❯</button>
            
            <div class="carousel-controls">
                <span class="carousel-dot active"></span>
                <span class="carousel-dot"></span>
                <span class="carousel-dot"></span>
            </div>
        </section>

        <!-- Products Preview -->
        <section>
            <h2 style="text-align: center; margin-bottom: 2rem;">Featured Products</h2>
            
            <div class="row">
                <?php foreach (array_slice($products, 0, 6) as $product): ?>
                    <div class="col-4">
                        <div class="product-card">
                            <div class="product-image" style="background-color: var(--light-gray); display: flex; align-items: center; justify-content: center; color: var(--medium-gray); font-size: 3rem;">
                                📦
                            </div>
                            <div class="product-details">
                                <h3 class="product-name"><?php echo htmlspecialchars($product['name']); ?></h3>
                                <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                                <div class="product-stock">
                                    Stock: <strong><?php echo $product['quantity_in_stock']; ?></strong> units
                                </div>
                                <a href="pages/login.php" class="btn btn-primary btn-block">View & Order</a>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>

            <div style="text-align: center; margin-top: 2rem;">
                <a href="pages/products.php" class="btn btn-secondary">View All Products</a>
            </div>
        </section>

        <!-- Why Choose Us -->
        <section style="margin-top: 4rem; margin-bottom: 4rem;">
            <h2 style="text-align: center; margin-bottom: 2rem;">Why Choose Us?</h2>
            
            <div class="row">
                <div class="col-3">
                    <div class="card">
                        <h3 style="text-align: center; color: var(--primary-color);">✓ Secure Platform</h3>
                        <p>Advanced security measures protect your business data and transactions.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <h3 style="text-align: center; color: var(--primary-color);">✓ GST Compliant</h3>
                        <p>All bills are fully GST compliant for your accounting needs.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <h3 style="text-align: center; color: var(--primary-color);">✓ 24/7 Support</h3>
                        <p>Our team is always available to assist you with any queries.</p>
                    </div>
                </div>
                <div class="col-3">
                    <div class="card">
                        <h3 style="text-align: center; color: var(--primary-color);">✓ Easy Checkout</h3>
                        <p>Simple and fast payment process with multiple payment options.</p>
                    </div>
                </div>
            </div>
        </section>

        <!-- Call to Action -->
        <section style="background-color: var(--light-gray); padding: 2rem; border-radius: var(--border-radius); text-align: center; margin-bottom: 3rem;">
            <h2>Ready to Start Ordering?</h2>
            <p style="font-size: 1.1rem; margin-bottom: 1.5rem;">Apply for your retailer account today and get access to our complete product catalog.</p>
            <a href="pages/apply.php" class="btn btn-primary" style="padding: 1rem 2rem; font-size: 1.1rem;">Apply for New Account</a>
        </section>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="assets/js/main.js"></script>
</body>
</html>
