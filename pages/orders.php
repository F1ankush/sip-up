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
$orders = getOrders($_SESSION['user_id']);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Orders - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>My Orders</h1>

        <?php if (empty($orders)): ?>
            <div class="card">
                <p style="text-align: center; font-size: 1.1rem;">You haven't placed any orders yet.</p>
                <p style="text-align: center;">
                    <a href="dashboard.php" class="btn btn-primary">Browse Products</a>
                </p>
            </div>
        <?php else: ?>
            <div style="overflow-x: auto;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>Order Number</th>
                            <th>Date</th>
                            <th>Total Amount</th>
                            <th>Payment Method</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($orders as $order): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($order['order_number']); ?></strong></td>
                                <td><?php echo date('d M Y H:i', strtotime($order['order_date'])); ?></td>
                                <td><?php echo formatCurrency($order['total_amount']); ?></td>
                                <td>
                                    <span style="background-color: var(--light-gray); padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?php echo strtoupper($order['payment_method']); ?>
                                    </span>
                                </td>
                                <td>
                                    <span style="background-color: <?php 
                                        if ($order['status'] === 'completed') echo 'var(--success-color)';
                                        elseif ($order['status'] === 'payment_rejected') echo 'var(--danger-color)';
                                        elseif ($order['status'] === 'bill_generated') echo 'var(--info-color)';
                                        else echo 'var(--warning-color)';
                                    ?>; color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?php echo ucwords(str_replace('_', ' ', $order['status'])); ?>
                                    </span>
                                </td>
                                <td>
                                    <a href="order_detail.php?id=<?php echo $order['id']; ?>" class="btn btn-primary" style="padding: 0.5rem 1rem; font-size: 0.9rem;">View Details</a>
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
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
