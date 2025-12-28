<?php
require_once 'includes/config.php';
require_once 'includes/db.php';

$checks = [];
$db_connected = false;

// Check PHP version
$checks['PHP Version'] = [
    'status' => version_compare(PHP_VERSION, '7.4.0', '>=') ? 'pass' : 'fail',
    'value' => PHP_VERSION,
    'required' => '7.4.0 or higher'
];

// Check MySQLi extension
$checks['MySQLi Extension'] = [
    'status' => extension_loaded('mysqli') ? 'pass' : 'fail',
    'value' => extension_loaded('mysqli') ? 'Installed' : 'Not installed',
    'required' => 'Required for database'
];

// Check Database Connection
try {
    $conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);
    if ($conn->connect_error) {
        $checks['Database Connection'] = [
            'status' => 'fail',
            'value' => 'Error: ' . $conn->connect_error,
            'required' => 'Must be connected'
        ];
    } else {
        $db_connected = true;
        $checks['Database Connection'] = [
            'status' => 'pass',
            'value' => DB_HOST . ' - ' . DB_NAME,
            'required' => 'Connected'
        ];

        // Check database tables
        $result = $conn->query("SELECT COUNT(*) as count FROM information_schema.TABLES WHERE TABLE_SCHEMA = '" . DB_NAME . "'");
        $row = $result->fetch_assoc();
        $checks['Database Tables'] = [
            'status' => $row['count'] >= 10 ? 'pass' : 'fail',
            'value' => $row['count'] . ' tables found',
            'required' => '10 tables required'
        ];

        $conn->close();
    }
} catch (Exception $e) {
    $checks['Database Connection'] = [
        'status' => 'fail',
        'value' => $e->getMessage(),
        'required' => 'Must be connected'
    ];
}

// Check required directories
$dirs = [
    'includes' => 'includes/',
    'assets' => 'assets/',
    'pages' => 'pages/',
    'admin' => 'admin/',
    'uploads' => 'uploads/',
];

foreach ($dirs as $name => $dir) {
    $checks['Directory: ' . $name] = [
        'status' => is_dir($dir) ? 'pass' : 'fail',
        'value' => $dir,
        'required' => 'Must exist'
    ];
}

// Check required files
$files = [
    'config.php' => 'includes/config.php',
    'db.php' => 'includes/db.php',
    'functions.php' => 'includes/functions.php',
    'style.css' => 'assets/css/style.css',
    'main.js' => 'assets/js/main.js',
    'index.php' => 'index.php',
    'database_schema.sql' => 'database_schema.sql',
];

foreach ($files as $name => $file) {
    $checks['File: ' . $name] = [
        'status' => file_exists($file) ? 'pass' : 'fail',
        'value' => $file,
        'required' => 'Must exist'
    ];
}

// Check file permissions
$checks['uploads/ writable'] = [
    'status' => is_writable('uploads/') ? 'pass' : 'fail',
    'value' => is_writable('uploads/') ? 'Writable' : 'Not writable',
    'required' => 'Must be writable'
];

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>System Verification - B2B Platform</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            min-height: 100vh;
            padding: 2rem;
        }

        .container {
            max-width: 900px;
            margin: 0 auto;
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 20px 60px rgba(0, 0, 0, 0.3);
        }

        h1 {
            color: #333;
            margin-bottom: 0.5rem;
            text-align: center;
        }

        .subtitle {
            text-align: center;
            color: #666;
            margin-bottom: 2rem;
            font-size: 0.95rem;
        }

        .checks-table {
            width: 100%;
            border-collapse: collapse;
            margin-bottom: 2rem;
        }

        .checks-table thead {
            background-color: #f3f4f6;
            border-bottom: 2px solid #e5e7eb;
        }

        .checks-table th {
            padding: 1rem;
            text-align: left;
            font-weight: 600;
            color: #374151;
        }

        .checks-table td {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
        }

        .checks-table tr:hover {
            background-color: #f9fafb;
        }

        .status-badge {
            display: inline-block;
            padding: 0.4rem 0.8rem;
            border-radius: 6px;
            font-weight: 600;
            font-size: 0.85rem;
        }

        .status-pass {
            background-color: #d1fae5;
            color: #065f46;
        }

        .status-fail {
            background-color: #fee2e2;
            color: #991b1b;
        }

        .value {
            color: #6b7280;
            font-family: 'Courier New', monospace;
            font-size: 0.9rem;
        }

        .summary {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background-color: #f3f4f6;
            border-radius: 8px;
        }

        .summary-item {
            text-align: center;
        }

        .summary-number {
            font-size: 2rem;
            font-weight: 700;
            color: #333;
        }

        .summary-label {
            color: #6b7280;
            font-size: 0.9rem;
            margin-top: 0.3rem;
        }

        .actions {
            display: grid;
            grid-template-columns: repeat(2, 1fr);
            gap: 1rem;
            margin-top: 2rem;
        }

        .btn {
            padding: 1rem;
            border: none;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 0.5rem;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-primary {
            background-color: #2563eb;
            color: white;
        }

        .btn-primary:hover {
            background-color: #1d4ed8;
        }

        .btn-secondary {
            background-color: #6b7280;
            color: white;
        }

        .btn-secondary:hover {
            background-color: #4b5563;
        }

        .warning {
            background-color: #fffbeb;
            border-left: 4px solid #f59e0b;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            color: #92400e;
        }

        .success {
            background-color: #f0fdf4;
            border-left: 4px solid #10b981;
            padding: 1rem;
            margin-bottom: 1.5rem;
            border-radius: 4px;
            color: #065f46;
        }

        .footer-note {
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 0.9rem;
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔍 B2B Platform - System Verification</h1>
        <p class="subtitle">Checking your system configuration and requirements</p>

        <?php
        $passed = count(array_filter($checks, fn($c) => $c['status'] === 'pass'));
        $failed = count(array_filter($checks, fn($c) => $c['status'] === 'fail'));
        $total = count($checks);
        ?>

        <div class="summary">
            <div class="summary-item">
                <div class="summary-number" style="color: #10b981;"><?php echo $passed; ?></div>
                <div class="summary-label">Passed</div>
            </div>
            <div class="summary-item">
                <div class="summary-number" style="color: #ef4444;"><?php echo $failed; ?></div>
                <div class="summary-label">Failed</div>
            </div>
            <div class="summary-item">
                <div class="summary-number"><?php echo $total; ?></div>
                <div class="summary-label">Total Checks</div>
            </div>
        </div>

        <?php if ($failed > 0): ?>
            <div class="warning">
                <strong>⚠️ Warning:</strong> Some checks failed. Please review and fix the issues before proceeding.
            </div>
        <?php else: ?>
            <div class="success">
                <strong>✓ All checks passed!</strong> Your system is ready to use.
            </div>
        <?php endif; ?>

        <table class="checks-table">
            <thead>
                <tr>
                    <th>Check</th>
                    <th>Status</th>
                    <th>Value</th>
                    <th>Required</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($checks as $name => $check): ?>
                    <tr>
                        <td><strong><?php echo htmlspecialchars($name); ?></strong></td>
                        <td>
                            <span class="status-badge status-<?php echo $check['status']; ?>">
                                <?php echo $check['status'] === 'pass' ? '✓ Pass' : '✗ Fail'; ?>
                            </span>
                        </td>
                        <td><span class="value"><?php echo htmlspecialchars($check['value']); ?></span></td>
                        <td><?php echo htmlspecialchars($check['required']); ?></td>
                    </tr>
                <?php endforeach; ?>
            </tbody>
        </table>

        <div class="actions">
            <a href="index.php" class="btn btn-primary">🏠 Go to Home</a>
            <a href="SETUP_GUIDE.md" class="btn btn-secondary">📖 Setup Guide</a>
        </div>

        <div class="footer-note">
            <p>If all checks pass, your system is ready! If not, follow the SETUP_GUIDE.md for help.</p>
            <p style="margin-top: 1rem; font-size: 0.85rem; color: #9ca3af;">
                Last checked: <?php echo date('d M Y, H:i:s'); ?>
            </p>
        </div>
    </div>
</body>
</html>
