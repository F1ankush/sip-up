<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!isset($_POST['csrf_token']) || !validateCSRFToken($_POST['csrf_token'])) {
        $error_message = 'Security token expired. Please try again.';
    } else {
        // Sanitize inputs
        $name = htmlspecialchars(trim($_POST['name'] ?? ''));
        $email = htmlspecialchars(trim($_POST['email'] ?? ''));
        $phone = htmlspecialchars(trim($_POST['phone'] ?? ''));
        $subject = htmlspecialchars(trim($_POST['subject'] ?? ''));
        $message = htmlspecialchars(trim($_POST['message'] ?? ''));
        
        // Validate inputs
        $errors = [];
        if (empty($name) || strlen($name) < 3) $errors[] = 'Please enter a valid name';
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = 'Please enter a valid email';
        if (empty($subject) || strlen($subject) < 5) $errors[] = 'Please enter a subject';
        if (empty($message) || strlen($message) < 10) $errors[] = 'Message must be at least 10 characters';
        
        if (empty($errors)) {
            // Save message to database
            $saveResult = saveContactMessage($name, $email, $phone, $subject, $message);
            
            if ($saveResult['success']) {
                // Send email to company as well
                $to = COMPANY_EMAIL;
                $email_subject = "New Contact Form Submission: " . $subject;
                $email_body = "New message from contact form:\n\n";
                $email_body .= "Name: $name\n";
                $email_body .= "Email: $email\n";
                $email_body .= "Phone: " . ($phone ? $phone : 'Not provided') . "\n";
                $email_body .= "Subject: $subject\n\n";
                $email_body .= "Message:\n$message\n";
                
                $headers = "From: $email\r\n";
                $headers .= "Reply-To: $email\r\n";
                
                mail($to, $email_subject, $email_body, $headers);
                
                $success_message = 'Thank you for your message! We will get back to you shortly.';
            } else {
                $error_message = 'Failed to save message. Please try again.';
            }
        } else {
            $error_message = implode('<br>', $errors);
        }
    }
}

$csrf_token = generateCSRFToken();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Us - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
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
            <a href="login.php" class="btn btn-secondary btn-capsule">Login</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Page Header -->
    <section class="hero">
        <div class="container">
            <h1>Contact Us</h1>
            <p>Get in touch with our team - we're here to help</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <?php if ($success_message): ?>
            <div class="success-alert" style="background-color: #d4edda; border: 1px solid #c3e6cb; color: #155724; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>
        
        <?php if ($error_message): ?>
            <div class="error-alert" style="background-color: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; padding: 1rem; border-radius: 4px; margin-bottom: 2rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <div class="row">
            <!-- Contact Form -->
            <div class="col-6">
                <div class="card">
                    <h2>Send us a Message</h2>
                    
                    <form method="POST" action="contact.php">
                        <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                        
                        <div class="form-group">
                            <label for="name">Name <span class="required">*</span></label>
                            <input type="text" id="name" name="name" required minlength="3" placeholder="Your full name">
                        </div>
                        
                        <div class="form-group">
                            <label for="email">Email <span class="required">*</span></label>
                            <input type="email" id="email" name="email" required placeholder="your.email@example.com">
                        </div>
                        
                        <div class="form-group">
                            <label for="phone">Phone Number</label>
                            <input type="tel" id="phone" name="phone" placeholder="+91 XXXX XXX XXX">
                        </div>
                        
                        <div class="form-group">
                            <label for="subject">Subject <span class="required">*</span></label>
                            <input type="text" id="subject" name="subject" required minlength="5" placeholder="What is this regarding?">
                        </div>
                        
                        <div class="form-group">
                            <label for="message">Message <span class="required">*</span></label>
                            <textarea id="message" name="message" required minlength="10" rows="6" placeholder="Please describe your inquiry in detail..."></textarea>
                        </div>
                        
                        <button type="submit" class="btn btn-primary btn-block">Send Message</button>
                    </form>
                </div>
            </div>

            <!-- Contact Information -->
            <div class="col-6">
                <h2>Contact Information</h2>
                
                <div class="card">
                    <h3 style="color: var(--primary-color);">📧 Email</h3>
                    <p><a href="mailto:<?php echo COMPANY_EMAIL; ?>" style="color: var(--primary-color); text-decoration: none;"><?php echo COMPANY_EMAIL; ?></a></p>
                </div>
                
                <div class="card">
                    <h3 style="color: var(--primary-color);">📞 Phone</h3>
                    <p><a href="tel:<?php echo COMPANY_PHONE; ?>" style="color: var(--primary-color); text-decoration: none;"><?php echo COMPANY_PHONE; ?></a></p>
                </div>
                
                <div class="card">
                    <h3 style="color: var(--primary-color);">📍 Address</h3>
                    <p><?php echo COMPANY_ADDRESS; ?></p>
                </div>
                
                <div class="card">
                    <h3 style="color: var(--primary-color);">🏢 Business Hours</h3>
                    <p><strong>Monday - Friday:</strong> 9:00 AM - 6:00 PM</p>
                    <p><strong>Saturday:</strong> 10:00 AM - 2:00 PM</p>
                    <p><strong>Sunday:</strong> Closed</p>
                </div>

                <div class="card">
                    <h3 style="color: var(--primary-color);">🔐 GST Information</h3>
                    <p><strong>GST Number:</strong> <?php echo COMPANY_GST; ?></p>
                    <p>For bulk orders and special inquiries, please contact us directly.</p>
                </div>
            </div>
        </div>

        <!-- Location Map Section -->
        <section style="margin-top: 3rem; margin-bottom: 3rem;">
            <h2 style="text-align: center; margin-bottom: 1rem;">Find Us On Map</h2>
            <div style="background-color: var(--light-gray); padding: 0; border-radius: 8px; overflow: hidden; height: 400px;">
                <iframe class="footer-map" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3887.5734261439226!2d77.59717!3d13.051213!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x3bae19bba11ce5dd%3A0xed8c3b0e9bcfd4af!2sBangalore%2C%20Karnataka%2C%20India!5e0!3m2!1sen!2sin!4v1640000000000" style="width: 100%; height: 100%; border: none;" allowfullscreen="" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
            </div>
        </section>
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
