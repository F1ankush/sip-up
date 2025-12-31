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
    <?php renderUserNavbar(); ?>

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
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
