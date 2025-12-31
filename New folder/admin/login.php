<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$csrf_token = generateCSRFToken();
$error_message = '';

// Redirect if already logged in
if (isAdminLoggedIn()) {
    redirectToAdminDashboard();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        global $db;
        
        $username = sanitize($_POST['username'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($username) || empty($password)) {
            $error_message = 'Please enter both username and password.';
        } else {
            // Get admin from database
            $stmt = $db->prepare("SELECT * FROM admins WHERE username = ?");
            $stmt->bind_param("s", $username);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $admin = $result->fetch_assoc();

                if (verifyPassword($password, $admin['password_hash'])) {
                    // Set session
                    $_SESSION['admin_id'] = $admin['id'];
                    $_SESSION['admin_username'] = $admin['username'];
                    $_SESSION['admin_email'] = $admin['email'];

                    // Create admin session in database
                    createAdminSession($admin['id']);

                    redirectToAdminDashboard();
                } else {
                    $error_message = 'Invalid username or password.';
                }
            } else {
                $error_message = 'Invalid username or password.';
            }
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--light-gray);">
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 140px);">
        <div style="width: 100%; max-width: 400px; padding: 1rem;">
            <div class="card">
                <h1 style="text-align: center; margin-top: 0; color: var(--danger-color);">Admin Login</h1>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-group">
                        <label for="username">Username <span class="required">*</span></label>
                        <input 
                            type="text" 
                            id="username" 
                            name="username" 
                            required 
                            placeholder="Enter your username"
                            autofocus
                        >
                        <div class="error-message"></div>
                    </div>

                    <div class="form-group">
                        <label for="password">Password <span class="required">*</span></label>
                        <input 
                            type="password" 
                            id="password" 
                            name="password" 
                            required 
                            placeholder="Enter your password"
                        >
                        <div class="error-message"></div>
                    </div>

                    <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem;">Login</button>
                </form>

                <div style="background-color: #fef2f2; padding: 1rem; border-radius: 8px; margin-top: 2rem; font-size: 0.9rem; border-left: 4px solid var(--danger-color);">
                    <p><strong>Admin Only:</strong></p>
                    <p>This portal is for administrators only. If you are a retailer, please <a href="../pages/login.php">login here</a>.</p>
                </div>

                <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 8px; margin-top: 1rem; font-size: 0.9rem;">
                    <p style="margin: 0;"><strong>⚙️ First Time Admin?</strong></p>
                    <p style="margin-top: 0.5rem; margin-bottom: 0;">Visit the <a href="setup.php">admin setup page</a> to create your account.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
