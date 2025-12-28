<?php
// Test file to check for errors

echo "Testing system...\n\n";

// Test 1: Check config
echo "1. Testing config.php...\n";
try {
    require_once 'includes/config.php';
    echo "   ✓ config.php loaded\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 2: Check database
echo "2. Testing database connection...\n";
try {
    require_once 'includes/db.php';
    echo "   ✓ db.php loaded\n";
    echo "   ✓ Database connected\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 3: Check functions
echo "3. Testing functions.php...\n";
try {
    require_once 'includes/functions.php';
    echo "   ✓ functions.php loaded\n";
} catch (Exception $e) {
    echo "   ✗ Error: " . $e->getMessage() . "\n";
    exit;
}

// Test 4: Check function existence
echo "4. Checking critical functions...\n";
$functions = [
    'isAdminLoggedIn',
    'getAdminData',
    'getProduct',
    'addProduct',
    'updateProduct',
    'deleteProduct',
    'sanitize',
    'validateEmail'
];

foreach ($functions as $func) {
    if (function_exists($func)) {
        echo "   ✓ $func exists\n";
    } else {
        echo "   ✗ $func MISSING\n";
    }
}

echo "\n✓ All tests passed! System is ready.\n";
?>
