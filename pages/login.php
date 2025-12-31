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
    <?php renderUserNavbar(); ?>

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
    <?php renderFooter(); ?>

    <script src="../assets/js/main.js"></script>
</body>
</html>
