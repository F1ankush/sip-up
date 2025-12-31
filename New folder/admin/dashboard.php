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

// Get statistics
$pending_apps = $db->query("SELECT COUNT(*) as count FROM retailer_applications WHERE status = 'pending'")->fetch_assoc();
$approved_users = $db->query("SELECT COUNT(*) as count FROM users")->fetch_assoc();
$pending_payments = $db->query("SELECT COUNT(*) as count FROM payments WHERE status = 'pending'")->fetch_assoc();
$total_products = $db->query("SELECT COUNT(*) as count FROM products WHERE is_active = 1")->fetch_assoc();
$total_orders = $db->query("SELECT COUNT(*) as count FROM orders")->fetch_assoc();
$total_revenue = $db->query("SELECT COALESCE(SUM(total_amount), 0) as total FROM bills")->fetch_assoc();

// Get recent applications
$stmt = $db->prepare("SELECT * FROM retailer_applications ORDER BY applied_date DESC LIMIT 5");
$stmt->execute();
$recent_apps = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get pending payments
$stmt = $db->prepare("SELECT p.*, o.order_number, u.username FROM payments p JOIN orders o ON p.order_id = o.id JOIN users u ON o.user_id = u.id WHERE p.status = 'pending' ORDER BY p.created_at DESC LIMIT 5");
$stmt->execute();
$pending_pays = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Admin Dashboard</h1>

        <!-- Statistics -->
        <div class="row" style="margin-bottom: 3rem;">
            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; border: none;">
                    <h3 style="margin: 0; color: white; font-size: 2.5rem;"><?php echo $pending_apps['count']; ?></h3>
                    <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">Pending Applications</p>
                    <a href="applications.php" style="color: white; text-decoration: underline; font-size: 0.9rem;">Review →</a>
                </div>
            </div>

            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%); color: white; border: none;">
                    <h3 style="margin: 0; color: white; font-size: 2.5rem;"><?php echo $approved_users['count']; ?></h3>
                    <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">Active Retailers</p>
                    <a href="retailers.php" style="color: white; text-decoration: underline; font-size: 0.9rem;">Manage →</a>
                </div>
            </div>

            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%); color: white; border: none;">
                    <h3 style="margin: 0; color: white; font-size: 2.5rem;"><?php echo $pending_payments['count']; ?></h3>
                    <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">Pending Payments</p>
                    <a href="payments.php" style="color: white; text-decoration: underline; font-size: 0.9rem;">Verify →</a>
                </div>
            </div>

            <div class="col-3">
                <div class="card" style="text-align: center; background: linear-gradient(135deg, #43e97b 0%, #38f9d7 100%); color: white; border: none;">
                    <h3 style="margin: 0; color: white; font-size: 2.5rem;"><?php echo formatCurrency($total_revenue['total']); ?></h3>
                    <p style="color: rgba(255,255,255,0.9); margin: 0.5rem 0 0 0;">Total Revenue</p>
                    <a href="bills.php" style="color: white; text-decoration: underline; font-size: 0.9rem;">View →</a>
                </div>
            </div>
        </div>

        <div class="row">
            <div class="col-3">
                <div class="card" style="text-align: center;">
                    <h3 style="margin-top: 0;"><?php echo $total_products['count']; ?></h3>
                    <p>Active Products</p>
                    <a href="products.php" class="btn btn-primary" style="width: 100%; text-align: center; margin-top: 1rem;">Manage</a>
                </div>
            </div>

            <div class="col-3">
                <div class="card" style="text-align: center;">
                    <h3 style="margin-top: 0;"><?php echo $total_orders['count']; ?></h3>
                    <p>Total Orders</p>
                    <a href="orders.php" class="btn btn-primary" style="width: 100%; text-align: center; margin-top: 1rem;">View</a>
                </div>
            </div>

            <div class="col-6">
                <div class="card">
                    <h3 style="margin-top: 0;">Quick Actions</h3>
                    <div style="display: flex; flex-direction: column; gap: 0.5rem;">
                        <a href="applications.php" class="btn btn-secondary" style="text-align: center;">Approve/Reject Applications</a>
                        <a href="products.php?action=add" class="btn btn-secondary" style="text-align: center;">Add New Product</a>
                        <a href="payments.php" class="btn btn-secondary" style="text-align: center;">Verify Payments</a>
                        <a href="bills_generate.php" class="btn btn-secondary" style="text-align: center;">Generate Bills</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Applications -->
        <section style="margin-top: 3rem;">
            <h2>Recent Applications</h2>
            
            <?php if (empty($recent_apps)): ?>
                <div class="card">
                    <p style="text-align: center;">No pending applications</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Name</th>
                                <th>Email</th>
                                <th>Phone</th>
                                <th>Date Applied</th>
                                <th>Status</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_apps as $app): ?>
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
                                        <a href="application_detail.php?id=<?php echo $app['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">View</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="applications.php" class="btn btn-secondary">View All Applications</a>
                </div>
            <?php endif; ?>
        </section>

        <!-- Pending Payments -->
        <section style="margin-top: 3rem;">
            <h2>Pending Payments</h2>
            
            <?php if (empty($pending_pays)): ?>
                <div class="card">
                    <p style="text-align: center;">All payments verified</p>
                </div>
            <?php else: ?>
                <div style="overflow-x: auto;">
                    <table class="table table-striped">
                        <thead>
                            <tr>
                                <th>Order Number</th>
                                <th>Retailer</th>
                                <th>Amount</th>
                                <th>Method</th>
                                <th>Uploaded</th>
                                <th>Action</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($pending_pays as $pay): ?>
                                <tr>
                                    <td><strong><?php echo htmlspecialchars($pay['order_number']); ?></strong></td>
                                    <td><?php echo htmlspecialchars($pay['username']); ?></td>
                                    <td><?php echo formatCurrency($pay['amount']); ?></td>
                                    <td><?php echo strtoupper($pay['payment_method']); ?></td>
                                    <td><?php echo date('d M Y H:i', strtotime($pay['created_at'])); ?></td>
                                    <td>
                                        <a href="payment_verify.php?id=<?php echo $pay['id']; ?>" class="btn btn-warning" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">Verify</a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>

                <div style="text-align: center; margin-top: 1rem;">
                    <a href="payments.php" class="btn btn-secondary">View All Payments</a>
                </div>
            <?php endif; ?>
        </section>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
