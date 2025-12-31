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
    <?php renderAdminNavbar(); ?>

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
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
