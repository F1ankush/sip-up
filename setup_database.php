<?php
/**
 * Database Setup Script
 * This script will create the database and all required tables
 * Run this once to initialize your application
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database configuration
$DB_HOST = 'localhost';
$DB_USER = 'root';
$DB_PASS = '';

// First, connect to MySQL without selecting a database
$connection = new mysqli($DB_HOST, $DB_USER, $DB_PASS);

// Check connection
if ($connection->connect_error) {
    die("❌ Connection failed: " . $connection->connect_error);
}

echo "✓ Connected to MySQL<br>";

// Read the SQL schema file
$sqlFile = __DIR__ . '/database_schema.sql';

if (!file_exists($sqlFile)) {
    die("❌ Error: database_schema.sql not found!");
}

$sqlContent = file_get_contents($sqlFile);

// Split the SQL content by semicolons and execute each statement
$sqlStatements = array_filter(array_map('trim', explode(';', $sqlContent)));

$successCount = 0;
$errorCount = 0;

foreach ($sqlStatements as $statement) {
    if (empty($statement)) {
        continue;
    }
    
    if ($connection->query($statement) === TRUE) {
        $successCount++;
    } else {
        echo "⚠ Warning: " . $connection->error . "<br>";
        $errorCount++;
    }
}

$connection->close();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Setup - B2B Retailer Platform</title>
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
        }
        .status.success {
            background-color: #d4edda;
            border: 2px solid #28a745;
            color: #155724;
        }
        .status.error {
            background-color: #f8d7da;
            border: 2px solid #dc3545;
            color: #721c24;
        }
        .status.info {
            background-color: #d1ecf1;
            border: 2px solid #17a2b8;
            color: #0c5460;
        }
        .stats {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 20px;
            margin: 20px 0;
        }
        .stat-box {
            padding: 20px;
            border-radius: 8px;
            text-align: center;
        }
        .stat-box h3 {
            margin: 0 0 10px 0;
            font-size: 0.9rem;
            color: #666;
            text-transform: uppercase;
        }
        .stat-box .number {
            font-size: 2.5rem;
            font-weight: bold;
            color: #333;
        }
        .stat-box.success {
            background-color: #d4edda;
        }
        .stat-box.warning {
            background-color: #fff3cd;
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
        <h1>Database Setup Complete</h1>
        
        <div class="status success">
            <strong>✓ Setup Successful!</strong> Your database has been initialized.
        </div>

        <div class="stats">
            <div class="stat-box success">
                <h3>Executed Statements</h3>
                <div class="number"><?php echo $successCount; ?></div>
            </div>
            <?php if ($errorCount > 0): ?>
            <div class="stat-box warning">
                <h3>Warnings</h3>
                <div class="number"><?php echo $errorCount; ?></div>
            </div>
            <?php endif; ?>
        </div>

        <div class="next-steps">
            <h3>What's Next?</h3>
            <ol>
                <li><strong>Create Admin Account:</strong> Go to <a href="admin/setup.php">Admin Setup Page</a></li>
                <li><strong>Add Products:</strong> Login to admin panel and add your product catalog</li>
                <li><strong>Review Home Page:</strong> Visit <a href="index.php">Homepage</a></li>
                <li><strong>Test Application:</strong> Try the <a href="pages/apply.php">retailer application form</a></li>
            </ol>
        </div>

        <a href="index.php" class="button">Go to Homepage</a>
    </div>
</body>
</html>
