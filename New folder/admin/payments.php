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

// Get all payments
$stmt = $db->prepare("SELECT p.*, o.order_number, u.username FROM payments p JOIN orders o ON p.order_id = o.id JOIN users u ON o.user_id = u.id ORDER BY p.created_at DESC");
$stmt->execute();
$payments = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Payments Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Payments Management</h1>

        <div style="overflow-x: auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Order Number</th>
                        <th>Retailer</th>
                        <th>Amount</th>
                        <th>Method</th>
                        <th>Status</th>
                        <th>Uploaded</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($payments)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No payments yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($payments as $payment): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($payment['order_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($payment['username']); ?></td>
                                <td><?php echo formatCurrency($payment['amount']); ?></td>
                                <td><?php echo strtoupper($payment['payment_method']); ?></td>
                                <td>
                                    <span style="background-color: <?php 
                                        if ($payment['status'] === 'verified') echo 'var(--success-color)';
                                        elseif ($payment['status'] === 'rejected') echo 'var(--danger-color)';
                                        else echo 'var(--warning-color)';
                                    ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                        <?php echo ucfirst($payment['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo date('d M Y H:i', strtotime($payment['created_at'])); ?></td>
                                <td>
                                    <a href="payment_verify.php?id=<?php echo $payment['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                        <?php echo $payment['status'] === 'pending' ? 'Verify' : 'View'; ?>
                                    </a>
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
