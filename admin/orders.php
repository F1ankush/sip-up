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

// Get all orders
$stmt = $db->prepare("SELECT o.*, u.username, u.email FROM orders o JOIN users u ON o.user_id = u.id ORDER BY o.order_date DESC");
$stmt->execute();
$orders = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Orders Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Orders Management</h1>

        <div style="overflow-x: auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Retailer</th>
                        <th>Amount</th>
                        <th>Payment Method</th>
                        <th>Status</th>
                        <th>Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($orders)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No orders yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($order['username']); ?></td>
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
                                <td><?php echo date('d M Y', strtotime($order['order_date'])); ?></td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">View</a>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
