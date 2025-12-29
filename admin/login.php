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
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" style="display: flex; align-items: center; text-decoration: none;">
               <img src="../assets/images/logo1.JPG" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
                <span><?php echo SITE_NAME; ?> - Admin</span>
            </a>
        </div>
        
        <div class="navbar-buttons">
            <a href="../index.php" class="btn btn-secondary btn-capsule">Back to Site</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

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
    <footer class="footer">
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
                    <li><a href="login.php">Login</a></li>
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
