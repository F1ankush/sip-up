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

// Contact Message Functions
function saveContactMessage($name, $email, $phone, $subject, $message) {
    global $db;
    $status = 'new';
    $createdAt = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("INSERT INTO contact_messages (name, email, phone, subject, message, status, created_at) VALUES (?, ?, ?, ?, ?, ?, ?)");
    
    if (!$stmt) {
        return [
            'success' => false,
            'message' => 'Database error: ' . $db->getConnection()->error
        ];
    }
    
    $stmt->bind_param("sssssss", $name, $email, $phone, $subject, $message, $status, $createdAt);
    
    if ($stmt->execute()) {
        return [
            'success' => true,
            'message' => 'Message saved successfully',
            'message_id' => $db->getLastId()
        ];
    } else {
        return [
            'success' => false,
            'message' => 'Error saving message: ' . $stmt->error
        ];
    }
}

function getContactMessages($status = null, $limit = null) {
    global $db;
    
    if ($status) {
        $stmt = $db->prepare("SELECT * FROM contact_messages WHERE status = ? ORDER BY created_at DESC" . ($limit ? " LIMIT $limit" : ""));
        $stmt->bind_param("s", $status);
    } else {
        $stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC" . ($limit ? " LIMIT $limit" : ""));
    }
    
    $stmt->execute();
    return $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
}

function getContactMessage($messageId) {
    global $db;
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $messageId);
    $stmt->execute();
    return $stmt->get_result()->fetch_assoc();
}

function updateMessageStatus($messageId, $status) {
    global $db;
    $updatedAt = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("UPDATE contact_messages SET status = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("ssi", $status, $updatedAt, $messageId);
    
    return $stmt->execute();
}

function replyToMessage($messageId, $adminId, $reply) {
    global $db;
    $repliedDate = date('Y-m-d H:i:s');
    $status = 'replied';
    $updatedAt = date('Y-m-d H:i:s');
    
    $stmt = $db->prepare("UPDATE contact_messages SET admin_reply = ?, replied_by = ?, replied_date = ?, status = ?, updated_at = ? WHERE id = ?");
    $stmt->bind_param("sissi", $reply, $adminId, $repliedDate, $status, $updatedAt, $messageId);
    
    return $stmt->execute();
}

function getNewMessagesCount() {
    global $db;
    $stmt = $db->prepare("SELECT COUNT(*) as count FROM contact_messages WHERE status = 'new'");
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    return $result['count'];
}


```
