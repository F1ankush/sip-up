<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$csrf_token = generateCSRFToken();
$error_message = '';
$success_message = '';

// Check if form is submitted
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Validate CSRF token
    if (!validateCSRFToken($_POST['csrf_token'] ?? '')) {
        $error_message = 'Invalid security token. Please try again.';
    } else {
        // Sanitize inputs
        $name = sanitize($_POST['name'] ?? '');
        $email = sanitizeEmail($_POST['email'] ?? '');
        $phone = sanitizePhone($_POST['phone'] ?? '');
        $address = sanitize($_POST['address'] ?? '');
        $agree = isset($_POST['agree']) ? 1 : 0;

        // Validate inputs
        $validation_errors = [];

        if (empty($name) || strlen($name) < 3) {
            $validation_errors[] = 'Name must be at least 3 characters';
        }

        if (empty($email) || !validateEmail($email)) {
            $validation_errors[] = 'Please enter a valid email address';
        }

        if (empty($phone) || !validatePhone($phone)) {
            $validation_errors[] = 'Please enter a valid 10-digit phone number';
        }

        if (empty($address) || strlen($address) < 10) {
            $validation_errors[] = 'Address must be at least 10 characters';
        }

        if (!$agree) {
            $validation_errors[] = 'You must agree to the terms and conditions';
        }

        if (!empty($validation_errors)) {
            $error_message = implode('<br>', $validation_errors);
        } else {
            global $db;

            // Check if email already exists in applications or users
            $stmt = $db->prepare("SELECT id FROM retailer_applications WHERE email = ? UNION SELECT id FROM users WHERE email = ?");
            $stmt->bind_param("ss", $email, $email);
            $stmt->execute();
            $result = $stmt->get_result();

            if ($result->num_rows > 0) {
                $error_message = 'This email is already registered. Please use a different email or try logging in.';
            } else {
                // Insert application
                $status = 'pending';
                $stmt = $db->prepare("INSERT INTO retailer_applications (name, email, phone, shop_address, status) VALUES (?, ?, ?, ?, ?)");
                $stmt->bind_param("sssss", $name, $email, $phone, $address, $status);

                if ($stmt->execute()) {
                    $_SESSION['success_message'] = 'Your application has been submitted successfully. Our team will review and contact you soon.';
                    header("Location: apply.php");
                    exit();
                } else {
                    $error_message = 'An error occurred while submitting your application. Please try again.';
                }
            }
        }
    }
}

if (isset($_SESSION['success_message'])) {
    $success_message = $_SESSION['success_message'];
    unset($_SESSION['success_message']);
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Apply for Account - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Apply for Retailer Account</h1>
            <p>Join our B2B retailer platform and start ordering</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 600px;">
        <?php if ($success_message): ?>
            <div class="alert alert-success">
                <strong>Success!</strong> <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-error">
                <strong>Error:</strong> <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <h2 style="margin-top: 0;">Application Form</h2>
            <p>Fill in the details below to apply for a retailer account. Our team will review your application and contact you shortly.</p>

            <form method="POST" action="">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">

                <div class="form-group">
                    <label for="name">Full Name <span class="required">*</span></label>
                    <input 
                        type="text" 
                        id="name" 
                        name="name" 
                        value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>"
                        required 
                        minlength="3"
                        placeholder="Enter your full name"
                    >
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="email">Email Address <span class="required">*</span></label>
                    <input 
                        type="email" 
                        id="email" 
                        name="email" 
                        value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>"
                        required 
                        placeholder="Enter your email address"
                    >
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="phone">Mobile Number <span class="required">*</span></label>
                    <input 
                        type="tel" 
                        id="phone" 
                        name="phone" 
                        value="<?php echo htmlspecialchars($_POST['phone'] ?? ''); ?>"
                        required 
                        placeholder="Enter your 10-digit mobile number"
                        pattern="[0-9]{10}"
                    >
                    <small style="color: #6b7280;">10-digit number without country code</small>
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <label for="address">Shop Address <span class="required">*</span></label>
                    <textarea 
                        id="address" 
                        name="address" 
                        required 
                        minlength="10"
                        placeholder="Enter your shop address (Street, City, State, Postal Code)"
                    ><?php echo htmlspecialchars($_POST['address'] ?? ''); ?></textarea>
                    <div class="error-message"></div>
                </div>

                <div class="form-group">
                    <div class="checkbox-group">
                        <input 
                            type="checkbox" 
                            id="agree" 
                            name="agree" 
                            required
                            <?php echo (isset($_POST['agree'])) ? 'checked' : ''; ?>
                        >
                        <label for="agree" style="margin: 0;">
                            I confirm that all the information provided is accurate and I agree to the terms and conditions
                            <span class="required">*</span>
                        </label>
                    </div>
                    <div class="error-message"></div>
                </div>

                <button type="submit" class="btn btn-primary btn-block" style="margin-top: 1.5rem;">Submit Application</button>
            </form>

            <p style="margin-top: 2rem; text-align: center; color: #6b7280;">
                Already have an account? <a href="login.php">Login here</a>
            </p>
        </div>

        <!-- Information Section -->
        <section style="margin-top: 3rem;">
            <h3>What Happens Next?</h3>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <h4 style="margin-top: 0;">1️⃣ Application Review</h4>
                        <p>Our team reviews your application and verifies the information provided.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h4 style="margin-top: 0;">2️⃣ Approval & Contact</h4>
                        <p>Once approved, we'll contact you via email or phone to confirm your account.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h4 style="margin-top: 0;">3️⃣ Password Setup</h4>
                        <p>You'll receive credentials to set up your secure password.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h4 style="margin-top: 0;">4️⃣ Start Ordering</h4>
                        <p>Login to your dashboard and start browsing and ordering products.</p>
                    </div>
                </div>
            </div>
        </section>
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
