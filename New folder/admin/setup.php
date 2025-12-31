<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$error_message = '';
$success_message = '';
$admin_exists = false;

global $db;
// Check if admin already exists
$result = $db->query("SELECT id FROM admins LIMIT 1");
if ($result && $result->num_rows > 0) {
    $admin_exists = true;
}

$csrf_token = generateCSRFToken();

// Handle admin creation
if ($_SERVER['REQUEST_METHOD'] === 'POST' && !$admin_exists) {
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token.';
    } else if (($_POST['setup_key'] ?? '') !== ADMIN_SETUP_KEY) {
        $error_message = 'Invalid setup key. Please check the configuration.';
    } else {
        $username = sanitize($_POST['username'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';

        // Validate
        $validation_errors = [];

        if (empty($username) || strlen($username) < 3) {
            $validation_errors[] = 'Username must be at least 3 characters';
        }

        if (empty($email) || !validateEmail($email)) {
            $validation_errors[] = 'Invalid email address';
        }

        if (empty($password) || !validatePassword($password)) {
            $validation_errors[] = 'Password must be at least 8 characters';
        }

        if ($password !== $confirm_password) {
            $validation_errors[] = 'Passwords do not match';
        }

        if (!empty($validation_errors)) {
            $error_message = implode('<br>', $validation_errors);
        } else {
            // Check if username or email already exists
            $stmt = $db->prepare("SELECT id FROM admins WHERE username = ? OR email = ? LIMIT 1");
            $stmt->bind_param("ss", $username, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = 'Username or email already exists.';
            } else {
                // Hash password and insert
                $password_hash = hashPassword($password);
                $stmt = $db->prepare("INSERT INTO admins (username, email, password_hash) VALUES (?, ?, ?)");
                $stmt->bind_param("sss", $username, $email, $password_hash);

                if ($stmt->execute()) {
                    $success_message = 'Admin account created successfully! You can now login.';
                    $admin_exists = true;
                } else {
                    $error_message = 'An error occurred while creating the admin account.';
                }
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
    <title>Admin Setup - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body style="background-color: var(--light-gray);">
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div style="display: flex; align-items: center; justify-content: center; min-height: calc(100vh - 140px);">
        <div style="width: 100%; max-width: 500px; padding: 1rem;">
            <div class="card">
                <h1 style="text-align: center; margin-top: 0; color: var(--danger-color);">Admin Account Setup</h1>

                <?php if ($admin_exists && $success_message): ?>
                    <div class="alert alert-success">
                        <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
                        <p style="margin-top: 1rem;">
                            <a href="login.php" class="btn btn-primary" style="width: 100%; text-align: center;">Go to Login</a>
                        </p>
                    </div>
                <?php elseif ($admin_exists): ?>
                    <div class="alert alert-warning">
                        <strong>Admin account already exists.</strong> Please <a href="login.php">login here</a>.
                    </div>
                <?php else: ?>
                    <?php if ($error_message): ?>
                        <div class="alert alert-error">
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <form method="POST" action="">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                        <p style="color: #6b7280; font-size: 0.95rem;">
                            This page allows you to create the first admin account. It will be disabled after the first admin account is created.
                        </p>

                        <div class="form-group">
                            <label for="username">Admin Username <span class="required">*</span></label>
                            <input 
                                type="text" 
                                id="username" 
                                name="username" 
                                required 
                                minlength="3"
                                placeholder="Create an admin username"
                            >
                            <small style="color: #6b7280;">At least 3 characters</small>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="email">Admin Email <span class="required">*</span></label>
                            <input 
                                type="email" 
                                id="email" 
                                name="email" 
                                required 
                                placeholder="Enter admin email"
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
                                minlength="8"
                                placeholder="Create a strong password"
                            >
                            <small style="color: #6b7280;">At least 8 characters</small>
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="confirm_password">Confirm Password <span class="required">*</span></label>
                            <input 
                                type="password" 
                                id="confirm_password" 
                                name="confirm_password" 
                                required 
                                placeholder="Confirm your password"
                            >
                            <div class="error-message"></div>
                        </div>

                        <div class="form-group">
                            <label for="setup_key">Setup Key <span class="required">*</span></label>
                            <input 
                                type="password" 
                                id="setup_key" 
                                name="setup_key" 
                                required 
                                placeholder="Enter the setup key from configuration"
                            >
                            <small style="color: #6b7280;">Check your config.php for the setup key</small>
                            <div class="error-message"></div>
                        </div>

                        <button type="submit" class="btn btn-primary btn-block" style="padding: 1rem; margin-top: 1rem;">Create Admin Account</button>
                    </form>
                <?php endif; ?>

                <div style="background-color: #fffbeb; padding: 1rem; border-radius: 8px; margin-top: 2rem; font-size: 0.9rem; border-left: 4px solid var(--warning-color);">
                    <p style="margin: 0;"><strong>⚠️ Important:</strong></p>
                    <ul style="margin: 0.5rem 0 0 1.5rem; padding: 0;">
                        <li>This page is for initial setup only</li>
                        <li>Keep your setup key safe</li>
                        <li>Disable this page after creating the first admin (not implemented yet)</li>
                        <li>Use a strong password with uppercase, numbers, and special characters</li>
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
