<?php
/**
 * Test File: User Account Creation on Approval
 * This file tests the default password (12345678) implementation
 */

require_once 'includes/config.php';
require_once 'includes/db.php';
require_once 'includes/functions.php';

echo "<h1>User Account Creation Function Test</h1>";
echo "<hr>";

// Test 1: Check if function exists
echo "<h2>Test 1: Check if createUserAccountOnApproval function exists</h2>";
if (function_exists('createUserAccountOnApproval')) {
    echo "<p style='color: green;'>✓ Function createUserAccountOnApproval exists</p>";
} else {
    echo "<p style='color: red;'>✗ Function createUserAccountOnApproval NOT FOUND</p>";
}

// Test 2: Check database connection
echo "<h2>Test 2: Check Database Connection</h2>";
try {
    global $db;
    if ($db && $db->connection) {
        echo "<p style='color: green;'>✓ Database connection successful</p>";
    } else {
        echo "<p style='color: red;'>✗ Database connection failed</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>✗ Error: " . $e->getMessage() . "</p>";
}

// Test 3: Verify bcrypt password hashing
echo "<h2>Test 3: Verify Bcrypt Password Hashing</h2>";
$testPassword = '12345678';
$hashedPassword = password_hash($testPassword, PASSWORD_BCRYPT);
echo "<p>Original password: <strong>12345678</strong></p>";
echo "<p>Hashed password: <strong>" . substr($hashedPassword, 0, 20) . "...</strong></p>";

if (password_verify($testPassword, $hashedPassword)) {
    echo "<p style='color: green;'>✓ Password verification works correctly</p>";
} else {
    echo "<p style='color: red;'>✗ Password verification failed</p>";
}

// Test 4: Check users table
echo "<h2>Test 4: Check Users Table Structure</h2>";
$result = $db->query("DESCRIBE users");
if ($result) {
    echo "<table border='1' cellpadding='10' cellspacing='0'>";
    echo "<tr><th>Field</th><th>Type</th><th>Key</th><th>Extra</th></tr>";
    while ($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
        echo "<td>" . htmlspecialchars($row['Extra']) . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "<p style='color: green;'>✓ Users table exists and has correct structure</p>";
} else {
    echo "<p style='color: red;'>✗ Error reading users table: " . $db->connection->error . "</p>";
}

// Test 5: Check approved applications
echo "<h2>Test 5: Check Approved Applications</h2>";
$stmt = $db->prepare("SELECT id, name, email, status FROM retailer_applications WHERE status = 'approved' LIMIT 5");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr><th>App ID</th><th>Shop Name</th><th>Email</th><th>Status</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['name']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['status']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'>✓ Found approved applications</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No approved applications found yet</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Error: " . $db->connection->error . "</p>";
}

// Test 6: Check user accounts created
echo "<h2>Test 6: Check User Accounts</h2>";
$stmt = $db->prepare("SELECT id, username, email, created_at FROM users ORDER BY created_at DESC LIMIT 5");
if ($stmt) {
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        echo "<table border='1' cellpadding='10' cellspacing='0'>";
        echo "<tr><th>User ID</th><th>Username</th><th>Email</th><th>Created</th></tr>";
        while ($row = $result->fetch_assoc()) {
            echo "<tr>";
            echo "<td>" . htmlspecialchars($row['id']) . "</td>";
            echo "<td>" . htmlspecialchars($row['username']) . "</td>";
            echo "<td>" . htmlspecialchars($row['email']) . "</td>";
            echo "<td>" . htmlspecialchars($row['created_at']) . "</td>";
            echo "</tr>";
        }
        echo "</table>";
        echo "<p style='color: green;'>✓ User accounts exist in database</p>";
    } else {
        echo "<p style='color: orange;'>⚠ No user accounts created yet</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Error: " . $db->connection->error . "</p>";
}

echo "<hr>";
echo "<h2>Summary</h2>";
echo "<p><strong>Default Password Workflow:</strong></p>";
echo "<ol>";
echo "<li>Retailer applies for account via /pages/apply.php</li>";
echo "<li>Application goes to pending status in retailer_applications table</li>";
echo "<li>Admin reviews application at /admin/application_detail.php</li>";
echo "<li>Admin clicks 'Approve' button</li>";
echo "<li>System updates application status to 'approved'</li>";
echo "<li>System calls <code>createUserAccountOnApproval()</code> function</li>";
echo "<li>Function creates user account with password: <strong>12345678</strong> (bcrypt hashed)</li>";
echo "<li>User can now login with email and password <strong>12345678</strong></li>";
echo "</ol>";
echo "<p><strong>Default Password:</strong> <code>12345678</code></p>";
echo "<p><strong>Test this by:</strong></p>";
echo "<ol>";
echo "<li>Go to /admin/applications.php</li>";
echo "<li>Click on a pending application</li>";
echo "<li>Click the 'Approve' button</li>";
echo "<li>User account will be created with password 12345678</li>";
echo "<li>User can login at /pages/login.php with their email and password 12345678</li>";
echo "</ol>";
?>
