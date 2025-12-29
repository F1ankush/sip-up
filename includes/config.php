<?php
// Database Configuration
define('DB_HOST', 'localhost');
define('DB_USER', 'u110596290_b2b_billing');
define('DB_PASS', 'B2b@billingsystem');
define('DB_NAME', 'u110596290_b2b_billing');


// Site Configuration
//define('SITE_URL', 'http://localhost/top1/'); //
define('SITE_NAME', 'B2B Retailer Platform');
define('COMPANY_NAME', 'Premium Retail Distribution');
define('COMPANY_GST', '27AABCU1234B2Z5');
define('COMPANY_PHONE', '+91 9876543210');
define('COMPANY_EMAIL', 'support@retailerplatform.com');
define('COMPANY_ADDRESS', 'Bangalore, Karnataka, India');

// Security Configuration
define('SESSION_TIMEOUT', 3600); // 1 hour
define('ADMIN_SETUP_KEY', 'Karan');
define('MAX_FILE_SIZE', 5242880); // 5MB
define('ALLOWED_FILE_TYPES', ['image/jpeg', 'image/png']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png']);

// Pagination
define('ITEMS_PER_PAGE', 10);

// UPI Configuration
define('UPI_MERCHANT_ID', 'YourUPIMerchantID');

// Error Reporting
error_reporting(E_ALL);
ini_set('display_errors', 0);
ini_set('log_errors', 1);
ini_set('error_log', 'error_log.txt');

// Start session if not already started
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}
?>
