<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

$admin = getAdminData($_SESSION['admin_id']);
global $db;

// Get all applications
$stmt = $db->prepare("SELECT * FROM retailer_applications ORDER BY applied_date DESC");
$stmt->execute();
$applications = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Retailer Applications - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="dashboard.php" style="display: flex; align-items: center; text-decoration: none;">
               <img src="../assets/images/logo1.JPG" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
                <span><?php echo SITE_NAME; ?> - Admin</span>
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="applications.php">Applications</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="bills.php">Bills</a></li>
            <li class="navbar-button-item"><a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <span style="margin-right: 1rem;">Admin: <?php echo htmlspecialchars($admin['username']); ?></span>
            <a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Retailer Applications</h1>

        <div style="overflow-x: auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Phone</th>
                        <th>Applied Date</th>
                        <th>Status</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($applications)): ?>
                        <tr>
                            <td colspan="6" style="text-align: center; padding: 2rem;">
                                No applications yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($applications as $app): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($app['name']); ?></strong></td>
                                <td><?php echo htmlspecialchars($app['email']); ?></td>
                                <td><?php echo htmlspecialchars($app['phone']); ?></td>
                                <td><?php echo date('d M Y', strtotime($app['applied_date'])); ?></td>
                                <td>
                                    <span style="background-color: <?php 
                                        if ($app['status'] === 'approved') echo 'var(--success-color)';
                                        elseif ($app['status'] === 'rejected') echo 'var(--danger-color)';
                                        else echo 'var(--warning-color)';
                                    ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?php echo ucfirst($app['status']); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="application_detail.php?id=<?php echo $app['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Review</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
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
