<?php
require_once 'db.php';

// Authentication Functions
function isLoggedIn() {
    return isset($_SESSION['user_id']);
}

function isAdminLoggedIn() {
    return isset($_SESSION['admin_id']);
}

function redirectToLogin() {
    header("Location: " . SITE_URL . "pages/login.php");
    exit();
}

function redirectToAdminLogin() {
    header("Location: " . SITE_URL . "admin/login.php");
    exit();
}

function redirectToDashboard() {
    header("Location: " . SITE_URL . "pages/dashboard.php");
    exit();
}

function redirectToAdminDashboard() {
    header("Location: " . SITE_URL . "admin/dashboard.php");
    exit();
}

// Session Validation
function validateSession($userId) {
    global $db;
    $stmt = $db->prepare("SELECT session_hash FROM sessions WHERE user_id = ? AND is_active = 1");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $row = $result->fetch_assoc();
        if (hash('sha256', session_id()) === $row['session_hash']) {
            return true;
        }
    }
    return false;
}

// Create new session and invalidate previous ones
function createUserSession($userId) {
    global $db;
    
    // Invalidate previous sessions
    $stmt = $db->prepare("UPDATE sessions SET is_active = 0 WHERE user_id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    
    // Create new session
    $sessionHash = hash('sha256', session_id());
    $stmt = $db->prepare("INSERT INTO sessions (user_id, session_hash, is_active) VALUES (?, ?, 1)");
    $stmt->bind_param("is", $userId, $sessionHash);
    $stmt->execute();
}

function createAdminSession($adminId) {
    global $db;
    
    // Invalidate previous sessions
    $stmt = $db->prepare("UPDATE admin_sessions SET is_active = 0 WHERE admin_id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    
    // Create new session
    $sessionHash = hash('sha256', session_id());
    $stmt = $db->prepare("INSERT INTO admin_sessions (admin_id, session_hash, is_active) VALUES (?, ?, 1)");
    $stmt->bind_param("is", $adminId, $sessionHash);
    $stmt->execute();
}

// Input Sanitization
function sanitize($data) {
    global $db;
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data, ENT_QUOTES, 'UTF-8');
    return $data;
}

function sanitizeEmail($email) {
    return filter_var(trim($email), FILTER_SANITIZE_EMAIL);
}

function sanitizePhone($phone) {
    return preg_replace('/[^0-9]/', '', $phone);
}

// Validation Functions
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

function validatePhone($phone) {
    $phone = sanitizePhone($phone);
    return strlen($phone) === 10 && is_numeric($phone);
}

function validatePassword($password) {
    return strlen($password) >= 8;
}

// Password Functions
function hashPassword($password) {
    return password_hash($password, PASSWORD_BCRYPT, ['cost' => 10]);
}

function verifyPassword($password, $hash) {
    return password_verify($password, $hash);
}

// File Upload Validation
function validateFileUpload($file) {
    if (!isset($file) || $file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload failed'];
    }
    
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'message' => 'File size exceeds limit (5MB)'];
    }
    
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mimeType = finfo_file($finfo, $file['tmp_name']);
    finfo_close($finfo);
    
    if (!in_array($mimeType, ALLOWED_FILE_TYPES)) {
        return ['success' => false, 'message' => 'Only JPG and PNG files are allowed'];
    }
    
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    if (!in_array($ext, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'message' => 'Invalid file extension'];
    }
    
    return ['success' => true];
}

// Generate Unique Filename
function generateUniqueFilename($file) {
    $ext = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    return 'file_' . time() . '_' . bin2hex(random_bytes(4)) . '.' . $ext;
}

// Generate UPI QR Code
function generateUPIQRCode($amount, $orderId) {
    $upiString = "upi://pay?pa=" . UPI_MERCHANT_ID . "&pn=RetailerPlatform&tn=Order" . $orderId . "&am=" . $amount . "&tr=" . $orderId . "&cu=INR";
    $qrCodeUrl = "https://api.qrserver.com/v1/create-qr-code/?size=300x300&data=" . urlencode($upiString);
    return $qrCodeUrl;
}

// Generate unique UPI ID
function generateUPIID() {
    return bin2hex(random_bytes(8)) . '@upi';
}

// Format currency
function formatCurrency($amount) {
    return '₹' . number_format($amount, 2);
}

// Calculate GST
function calculateGST($amount, $gstRate = 18) {
    return ($amount * $gstRate) / 100;
}

// User Functions
function getAdminData($adminId) {
    global $db;
    $stmt = $db->prepare("SELECT id, username, email FROM admins WHERE id = ?");
    $stmt->bind_param("i", $adminId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function getUserData($userId) {
    global $db;
    $stmt = $db->prepare("SELECT id, username, email, phone, shop_address FROM users WHERE id = ?");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Check if user exists
function userExists($email) {
    global $db;
    $stmt = $db->prepare("SELECT id FROM users WHERE email = ?");
    $stmt->bind_param("s", $email);
    $stmt->execute();
    return $stmt->get_result()->num_rows > 0;
}

// Product Functions
function getProducts() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE is_active = 1 ORDER BY name ASC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getAllProducts() {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products ORDER BY name ASC");
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getProduct($productId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM products WHERE id = ?");
    $stmt->bind_param("i", $productId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function addProduct($name, $description, $price, $quantity, $imagePath = null) {
    global $db;
    $isActive = 1;
    $stmt = $db->prepare("INSERT INTO products (name, description, price, quantity_in_stock, image_path, is_active) VALUES (?, ?, ?, ?, ?, ?)");
    $stmt->bind_param("ssidsi", $name, $description, $price, $quantity, $imagePath, $isActive);
    
    if ($stmt->execute()) {
        return $db->getLastId();
    }
    return false;
}

function updateProduct($productId, $name, $description, $price, $quantity, $imagePath = null) {
    global $db;
    if ($imagePath) {
        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity_in_stock = ?, image_path = ? WHERE id = ?");
        $stmt->bind_param("ssidis", $name, $description, $price, $quantity, $imagePath, $productId);
    } else {
        $stmt = $db->prepare("UPDATE products SET name = ?, description = ?, price = ?, quantity_in_stock = ? WHERE id = ?");
        $stmt->bind_param("ssidi", $name, $description, $price, $quantity, $productId);
    }
    
    return $stmt->execute();
}

function deleteProduct($productId) {
    global $db;
    $stmt = $db->prepare("UPDATE products SET is_active = 0 WHERE id = ?");
    $stmt->bind_param("i", $productId);
    return $stmt->execute();
}

// Order Functions
function createOrder($userId, $totalAmount, $paymentMethod) {
    global $db;
    $orderDate = date('Y-m-d H:i:s');
    $status = 'pending_payment';
    
    $stmt = $db->prepare("INSERT INTO orders (user_id, total_amount, payment_method, status, order_date) VALUES (?, ?, ?, ?, ?)");
    $stmt->bind_param("idss", $userId, $totalAmount, $paymentMethod, $status, $orderDate);
    
    if ($stmt->execute()) {
        return $db->getLastId();
    }
    return false;
}

function getOrders($userId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM orders WHERE user_id = ? ORDER BY order_date DESC");
    $stmt->bind_param("i", $userId);
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getOrder($orderId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM orders WHERE id = ?");
    $stmt->bind_param("i", $orderId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

// Bill Functions
function generateBillNumber() {
    return 'BILL' . date('Ymd') . strtoupper(bin2hex(random_bytes(4)));
}

// CSRF Token
function generateCSRFToken() {
    if (empty($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function validateCSRFToken($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Redirect with message
function redirectWithMessage($url, $message, $type = 'success') {
    $_SESSION['message'] = $message;
    $_SESSION['message_type'] = $type;
    header("Location: " . $url);
    exit();
}

// Display message
function displayMessage() {
    if (isset($_SESSION['message'])) {
        $type = $_SESSION['message_type'] ?? 'success';
        $message = $_SESSION['message'];
        unset($_SESSION['message']);
        unset($_SESSION['message_type']);
        return '<div class="alert alert-' . $type . '">' . htmlspecialchars($message) . '</div>';
    }
    return '';
}

// Function to create user account after application approval
function createUserAccountOnApproval($appId, $email, $phone, $username, $shop_address) {
    global $db;
    
    // Default password: 12345678
    $tempPassword = '12345678';
    $passwordHash = password_hash($tempPassword, PASSWORD_BCRYPT);
    
    $stmt = $db->prepare("INSERT INTO users (application_id, email, phone, username, password_hash, shop_address) VALUES (?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: Unable to create user account'
        ];
    }
    
    $stmt->bind_param("isssss", $appId, $email, $phone, $username, $passwordHash, $shop_address);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'password' => $tempPassword,
            'message' => 'User account created successfully with default password: 12345678'
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error creating user account: ' . $stmt->error
        ];
    }
}

// Navbar Functions
function renderUserNavbar() {
    $isLoggedIn = isLoggedIn();
    $user = $isLoggedIn ? getUserData($_SESSION['user_id']) : null;
    ?>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="<?php echo $isLoggedIn ? 'dashboard.php' : '../index.php'; ?>" style="display: flex; align-items: center; text-decoration: none;">
                <img src="<?php echo $isLoggedIn ? 'assets/images/logo1.jpg' : '../assets/images/logo1.jpg'; ?>" alt="Logo" style="height: 40px;">
            </a>
        </div>
        
        <ul class="navbar-menu">
            <?php if ($isLoggedIn): ?>
                <li><a href="dashboard.php">Dashboard</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="orders.php">Orders</a></li>
                <li><a href="bills.php">Bills</a></li>
                <li class="navbar-button-item"><a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a></li>
            <?php else: ?>
                <li><a href="../index.php">Home</a></li>
                <li><a href="about.php">About</a></li>
                <li><a href="products.php">Products</a></li>
                <li><a href="contact.php">Contact</a></li>
                <li class="navbar-button-item"><a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a></li>
                <li class="navbar-button-item"><a href="login.php" class="btn btn-secondary btn-capsule">Login</a></li>
            <?php endif; ?>
        </ul>
        
        <div class="navbar-buttons">
            <?php if ($isLoggedIn): ?>
                <span style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($user['username']); ?></span>
                <a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a>
            <?php else: ?>
                <a href="apply.php" class="btn btn-primary btn-capsule">Apply for Account</a>
                <a href="login.php" class="btn btn-secondary btn-capsule">Login</a>
            <?php endif; ?>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
    <?php
}

function renderAdminNavbar() {
    $isAdminLoggedIn = isAdminLoggedIn();
    $admin = $isAdminLoggedIn ? getAdminData($_SESSION['admin_id']) : null;
    ?>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="<?php echo $isAdminLoggedIn ? 'dashboard.php' : '../index.php'; ?>" style="display: flex; align-items: center; text-decoration: none;">
                <img src="../assets/images/logo1.jpg" alt="Logo" style="height: 40px;">
            </a>
        </div>
        
        <?php if ($isAdminLoggedIn): ?>
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="applications.php">Applications</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="bills.php">Bills</a></li>
            <li class="navbar-button-item"><a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <span style="margin-right: 1rem;">Welcome, <?php echo htmlspecialchars($admin['username']); ?></span>
            <a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a>
        </div>
        <?php else: ?>
        <div class="navbar-buttons">
            <a href="../index.php" class="btn btn-secondary btn-capsule">Back to Site</a>
        </div>
        <?php endif; ?>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>
    <?php
}

