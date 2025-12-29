<?php
require_once '../includes/config.php';
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>About Us - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderUserNavbar(); ?>

    <!-- Page Header -->
    <section class="hero" style="background: linear-gradient(135deg, var(--primary-color) 0%, var(--secondary-color) 100%);">
        <div class="container">
            <h1>About Us</h1>
            <p>Learn more about our mission and values</p>
        </div>
    </section>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 900px;">
        <section>
            <h2>Our Mission</h2>
            <p>
                <?php echo COMPANY_NAME; ?> is dedicated to revolutionizing the B2B retail ordering experience. 
                We provide a secure, transparent, and efficient platform for retailers to source quality products 
                with competitive pricing and GST-compliant billing.
            </p>

            <h2 style="margin-top: 2rem;">Our Vision</h2>
            <p>
                To become the leading digital B2B distribution platform trusted by retailers across India, 
                known for reliability, transparency, and exceptional service.
            </p>

            <h2 style="margin-top: 2rem;">Key Values</h2>
            <div class="row">
                <div class="col-6">
                    <div class="card">
                        <h3>🔒 Security</h3>
                        <p>Your business data is protected with industry-leading security measures.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h3>📊 Transparency</h3>
                        <p>Complete visibility into pricing, inventory, and billing information.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h3>⚡ Efficiency</h3>
                        <p>Streamlined ordering and payment processes to save your time.</p>
                    </div>
                </div>
                <div class="col-6">
                    <div class="card">
                        <h3>💪 Support</h3>
                        <p>Dedicated support team to assist you 24/7 with any questions.</p>
                    </div>
                </div>
            </div>

            <h2 style="margin-top: 2rem;">Why Partner With Us?</h2>
            <ul style="margin-left: 2rem;">
                <li>Wide range of quality products from trusted suppliers</li>
                <li>Competitive wholesale pricing for bulk orders</li>
                <li>Fast and reliable delivery across India</li>
                <li>GST-compliant billing and invoicing</li>
                <li>Secure payment gateway with multiple options</li>
                <li>Real-time order tracking and updates</li>
                <li>Easy account management and order history</li>
                <li>Responsive customer support team</li>
            </ul>

            <h2 style="margin-top: 2rem;">Company Information</h2>
            <div class="card">
                <p><strong>Company Name:</strong> <?php echo COMPANY_NAME; ?></p>
                <p><strong>GST Number:</strong> <?php echo COMPANY_GST; ?></p>
                <p><strong>Email:</strong> <?php echo COMPANY_EMAIL; ?></p>
                <p><strong>Phone:</strong> <?php echo COMPANY_PHONE; ?></p>
                <p><strong>Address:</strong> <?php echo COMPANY_ADDRESS; ?></p>
            </div>
        </section>
    </div>

    <!-- Footer -->
    <footer class="footer" style="margin-top: 4rem;">
        <div class="footer-content">
            <div class="footer-section">
                <h3><?php echo COMPANY_NAME; ?></h3>
                <p>Leading B2B retailer ordering and GST billing platform.</p>
            </div>
            
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
