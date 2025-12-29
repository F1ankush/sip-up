<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$csrf_token = generateCSRFToken();
$error_message = '';

// Redirect if already logged in
if (isLoggedIn()) {
    redirectToDashboard();
}

// Handle login
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        global $db;
        
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';

        if (empty($email) || empty($password)) {
            $error_message = 'Please enter both email and password.';
        } else {
            // Get user from database
            $stmt = $db->prepare("SELECT u.*, ra.status FROM users u JOIN retailer_applications ra ON u.application_id = ra.id WHERE u.email = ? AND u.is_active = 1");
            $stmt->bind_param("s", $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $user = $result->fetch_assoc();

                // Check if application is approved
                if ($user['status'] !== 'approved') {
                    $error_message = 'Your account is not yet approved. Please wait for admin approval.';
                } elseif (verifyPassword($password, $user['password_hash'])) {
                    // Set session
                    $_SESSION['user_id'] = $user['id'];
                    $_SESSION['user_email'] = $user['email'];
                    $_SESSION['username'] = $user['username'];

                    // Create user session in database
                    createUserSession($user['id']);

                    redirectToDashboard();
                } else {
                    $error_message = 'Invalid email or password.';
                }
            } else {
                $error_message = 'Invalid email or password.';
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
    <title>Retailer Login - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--light-gray);">
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="../index.php" style="display: flex; align-items: center; text-decoration: none;">
               <img src="../assets/images/logo1.JPG" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="../index.php">Home</a></li>
            <li><a href="about.php">About</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="contact.php">Contact</a></li>
            <li class="navbar-button-item"><a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a></li>
            <li class="navbar-button-item"><a href="login.php" class="btn btn-secondary btn-capsule">Login</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a>
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
                <h1 style="text-align: center; margin-top: 0; color: var(--primary-color);">Retailer Login</h1>

                <?php if ($error_message): ?>
                    <div class="alert alert-error">
                        <?php echo htmlspecialchars($error_message); ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="">
                    <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                    <div class="form-group">
                        <label for="email">Email Address <span class="required">*</span></label>
                        <input 
                            type="email" 
                            id="email" 
                            name="email" 
                            required 
                            placeholder="Enter your email"
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

                <div style="text-align: center; margin-top: 2rem; border-top: 1px solid var(--light-gray); padding-top: 2rem;">
                    <p style="margin-bottom: 1rem;">Don't have an account yet?</p>
                    <a href="apply.php" class="btn btn-secondary btn-block">Apply for New Account</a>
                </div>

                <div style="background-color: var(--light-gray); padding: 1rem; border-radius: 8px; margin-top: 2rem; font-size: 0.9rem;">
                    <p><strong>Security Notice:</strong></p>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>Never share your password with anyone</li>
                        <li>Always logout after using shared devices</li>
                        <li>Only one active login per account is allowed</li>
                    </ul>
                </div>
            </div>
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
                    <li><a href="../index.php">Home</a></li>
                    <li><a href="about.php">About</a></li>
                    <li><a href="products.php">Products</a></li>
                    <li><a href="contact.php">Contact</a></li>
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
