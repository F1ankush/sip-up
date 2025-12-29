<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in
if (!isLoggedIn()) {
    redirectToLogin();
}

// Validate session
if (!validateSession($_SESSION['user_id'])) {
    session_destroy();
    redirectToLogin();
}

$user = getUserData($_SESSION['user_id']);
$csrf_token = generateCSRFToken();
$products = getProducts();
$orders = getOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <!-- Dashboard Stats -->
        <div class="row" style="margin-bottom: 2rem;">
            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white;">
                    <h3 style="margin: 0; color: white;"><?php echo count($orders); ?></h3>
                    <p style="color: rgba(255,255,255,0.9);">Total Orders</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white;">
                    <h3 style="margin: 0; color: white;"><?php 
                        $active_orders = count(array_filter($orders, function($o) { 
                            return $o['status'] !== 'completed'; 
                        }));
                        echo $active_orders;
                    ?></h3>
                    <p style="color: rgba(255,255,255,0.9);">Active Orders</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white;">
                    <h3 style="margin: 0; color: white;"><?php echo count($products); ?></h3>
                    <p style="color: rgba(255,255,255,0.9);">Available Products</p>
                </div>
            </div>
            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white;">
                    <h3 style="margin: 0; color: white;">₹0</h3>
                    <p style="color: rgba(255,255,255,0.9);">Pending Amount</p>
                </div>
            </div>
        </div>

        <!-- Products Section -->
        <section>
            <h2>Available Products</h2>
            
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
                                            <?php echo htmlspecialchars(substr($product['description'], 0, 80)); ?>
                                        </p>
                                    <?php endif; ?>
                                    <div class="product-price"><?php echo formatCurrency($product['price']); ?></div>
                                    <div class="product-stock">
                                        <span style="background-color: <?php echo $product['quantity_in_stock'] > 0 ? 'var(--success-color)' : 'var(--danger-color)'; ?>; color: white; padding: 0.3rem 0.6rem; border-radius: 4px; font-size: 0.85rem;">
                                            <?php echo $product['quantity_in_stock'] > 0 ? 'In Stock' : 'Out of Stock'; ?>
                                        </span>
                                    </div>
                                    
                                    <?php if ($product['quantity_in_stock'] > 0): ?>
                                        <div class="quantity-selector" style="margin-top: 1rem;">
                                            <button type="button">−</button>
                                            <input type="number" value="1" min="1" max="<?php echo $product['quantity_in_stock']; ?>" data-product="<?php echo $product['id']; ?>">
                                            <button type="button">+</button>
                                        </div>
                                        <button type="button" class="btn btn-primary btn-block" style="margin-top: 0.5rem;" onclick="addToCart(<?php echo $product['id']; ?>)">
                                            Add to Cart
                                        </button>
                                    <?php else: ?>
                                        <button type="button" class="btn btn-secondary btn-block" style="margin-top: 1rem; cursor: not-allowed;" disabled>
                                            Out of Stock
                                        </button>
                                    <?php endif; ?>
                                </div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </section>

        <!-- Recent Orders -->
        <section style="margin-top: 4rem;">
            <h2>Recent Orders</h2>
            
            <?php if (empty($orders)): ?>
                <div class="card">
                    <p style="text-align: center;">You haven't placed any orders yet.</p>
                    <p style="text-align: center;">
                        <a href="dashboard.php" class="btn btn-primary">Browse Products</a>
                    </p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Order ID</th>
                                <th>Date</th>
                                <th>Amount</th>
                                <th>Payment Method</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach (array_slice($orders, 0, 5) as $order): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                    <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                    <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                    <td><?php echo strtoupper($order['payment_method']); ?></td>
                                    <td>
                                        <span style="background-color: <?php 
                                            if ($order['status'] === 'completed') echo 'var(--success-color)';
                                            elseif ($order['status'] === 'payment_rejected') echo 'var(--danger-color)';
                                            else echo 'var(--warning-color)';
                                        ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                            <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
                
                <div style="text-align: center; margin-top: 1rem;">
                    <a href="orders.php" class="btn btn-secondary">View All Orders</a>
                </div>
            <?php endif; ?>
        </section>
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
                    <li><a href="orders.php">Orders</a></li>
                    <li><a href="bills.php">Bills</a></li>
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
