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
global $db;

// Get bills for user
$stmt = $db->prepare("SELECT b.*, o.order_number FROM bills b JOIN orders o ON b.order_id = o.id WHERE b.user_id = ? ORDER BY b.bill_date DESC");
$stmt->bind_param("i", $_SESSION['user_id']);
$stmt->execute();
$bills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Bills - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>My Bills</h1>

        <?php if (empty($bills)): ?>
            <div class="card">
                <p style="text-align: center; font-size: 1.1rem;">No bills generated yet. Complete your orders to receive bills.</p>
                <p style="text-align: center;">
                    <a href="orders.php" class="btn btn-primary">View My Orders</a>
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Bill Number</th>
                            <th>Order Number</th>
                            <th>Bill Date</th>
                            <th>Subtotal</th>
                            <th>GST</th>
                            <th>Total Amount</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($bill['bill_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($bill['order_number']); ?></td>
                                <td><?php echo date('d M Y', strtotime($bill['bill_date'])); ?></td>
                                <td><?php echo formatCurrency($bill['subtotal']); ?></td>
                                <td><?php echo formatCurrency($bill['gst_amount']); ?></td>
                                <td><strong><?php echo formatCurrency($bill['total_amount']); ?></strong></td>
                                <td>
                                    <a href="bill_view.php?id=<?php echo $bill['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem; margin-right: 0.5rem;">View</a>
                                    <a href="bill_download.php?id=<?php echo $bill['id']; ?>" class="btn btn-success" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Download</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>

        <div style="margin-top: 2rem;">
            <a href="dashboard.php" class="btn btn-secondary">← Back to Dashboard</a>
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
