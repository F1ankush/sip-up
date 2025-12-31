<?php
/**
 * Add Contact Messages Table to Existing Database
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';
$DB_NAME = 'b2b_billing_system';

// Connect to MySQL
$connection = new mysqli($DB_HOST, $DB_USER, $DB_PASS, $DB_NAME);

if ($connection->connect_error) {
    die("❌ Connection failed: " . $connection->connect_error);
}

echo "✓ Connected to database<br>";

// Create contact_messages table
$sql = "CREATE TABLE IF NOT EXISTS contact_messages (
    id INT PRIMARY KEY AUTO_INCREMENT,
    name VARCHAR(255) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(15),
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    status ENUM('new', 'read', 'replied', 'closed') DEFAULT 'new',
    admin_reply TEXT,
    replied_by INT,
    replied_date TIMESTAMP NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_message_status (status),
    INDEX idx_message_email (email),
    INDEX idx_message_date (created_at),
    FOREIGN KEY (replied_by) REFERENCES admins(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

if ($connection->query($sql) === TRUE) {
    echo "✓ Contact messages table created successfully<br>";
} else {
    echo "⚠ Table creation result: " . $connection->error . "<br>";
}

$connection->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Setup Contact Messages - B2B Retailer Platform</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            min-height: 100vh;
            margin: 0;
            padding: 20px;
        }
        .container {
            background: white;
            padding: 40px;
            border-radius: 12px;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
            max-width: 600px;
            width: 100%;
        }
        h1 {
            color: #333;
            margin-bottom: 30px;
            text-align: center;
        }
        .status {
            padding: 20px;
            border-radius: 8px;
            margin-bottom: 20px;
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .next-steps {
            background-color: #f8f9fa;
            padding: 20px;
            border-radius: 8px;
            margin-top: 30px;
        }
        .next-steps h3 {
            margin-top: 0;
            color: #333;
        }
        .next-steps ol {
            padding-left: 20px;
        }
        .next-steps li {
            margin-bottom: 10px;
            color: #555;
        }
        .next-steps a {
            color: #667eea;
            text-decoration: none;
            font-weight: 600;
        }
        .next-steps a:hover {
            text-decoration: underline;
        }
        .button {
            display: inline-block;
            background-color: #667eea;
            color: white;
            padding: 12px 30px;
            border-radius: 6px;
            text-decoration: none;
            margin-top: 20px;
            text-align: center;
            width: 100%;
            box-sizing: border-box;
            font-size: 1rem;
            border: none;
            cursor: pointer;
        }
        .button:hover {
            background-color: #764ba2;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Contact Messages Setup</h1>
        
        <div class="status">
            <strong>✓ Setup Complete!</strong> Contact messages table has been added to your database.
        </div>

        <div class="next-steps">
            <h3>What's New?</h3>
            <ul>
                <li><strong>Contact Form:</strong> Visitors can now submit contact messages via <code>/pages/contact.php</code></li>
                <li><strong>Admin Panel:</strong> View and manage messages at <code>/admin/messages.php</code></li>
                <li><strong>Reply System:</strong> Send replies directly to visitors from the admin panel</li>
                <li><strong>Status Tracking:</strong> Track message status (New, Read, Replied, Closed)</li>
                <li><strong>Message History:</strong> Keep complete history of all customer inquiries</li>
            </ul>
        </div>

        <div class="next-steps">
            <h3>Quick Start</h3>
            <ol>
                <li>Login to admin panel: <a href="../admin/login.php">Admin Login</a></li>
                <li>Go to "Messages" in the navigation menu</li>
                <li>View all incoming contact form submissions</li>
                <li>Reply to messages and manage their status</li>
            </ol>
        </div>

        <a href="../index.php" class="button">Return to Homepage</a>
    </div>
</body>
</html>
