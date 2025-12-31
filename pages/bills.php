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
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
