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

// Get all bills
$stmt = $db->prepare("SELECT b.*, o.order_number, u.username FROM bills b JOIN orders o ON b.order_id = o.id JOIN users u ON b.user_id = u.id ORDER BY b.bill_date DESC");
$stmt->execute();
$bills = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Bills Management - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Bills Management</h1>

        <div style="overflow-x: auto;">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>Bill Number</th>
                        <th>Order Number</th>
                        <th>Retailer</th>
                        <th>Total Amount</th>
                        <th>GST</th>
                        <th>Bill Date</th>
                        <th>Action</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($bills)): ?>
                        <tr>
                            <td colspan="7" style="text-align: center; padding: 2rem;">
                                No bills generated yet
                            </td>
                        </tr>
                    <?php else: ?>
                        <?php foreach ($bills as $bill): ?>
                            <tr>
                                <td><strong><?php echo htmlspecialchars($bill['bill_number']); ?></strong></td>
                                <td><?php echo htmlspecialchars($bill['order_number']); ?></td>
                                <td><?php echo htmlspecialchars($bill['username']); ?></td>
                                <td><?php echo formatCurrency($bill['total_amount']); ?></td>
                                <td><?php echo formatCurrency($bill['gst_amount']); ?></td>
                                <td><?php echo date('d M Y', strtotime($bill['bill_date'])); ?></td>
                                <td>
                                    <a href="bill_view.php?id=<?php echo $bill['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">View</a>
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
