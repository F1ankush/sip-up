<?php
require_once '../includes/config.php';
require_once '../includes/db.php';
require_once '../includes/functions.php';

// Check if logged in as admin
if (!isAdminLoggedIn()) {
    redirectToAdminLogin();
}

$admin = getAdminData($_SESSION['admin_id']);
global $db;

// Get filter parameter
$filter = $_GET['filter'] ?? 'all';
$messageId = $_GET['id'] ?? null;

// Get messages based on filter
if ($filter === 'new') {
    $messages = getContactMessages('new');
} elseif ($filter === 'replied') {
    $messages = getContactMessages('replied');
} elseif ($filter === 'closed') {
    $messages = getContactMessages('closed');
} else {
    $messages = getContactMessages();
}

// Get single message if viewing detail
$currentMessage = null;
if ($messageId) {
    $currentMessage = getContactMessage($messageId);
    
    // Mark as read if not already
    if ($currentMessage && $currentMessage['status'] === 'new') {
        updateMessageStatus($messageId, 'read');
    }
}

// Handle reply submission
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['reply_message'])) {
    if (isAdminLoggedIn() && $messageId) {
        $reply = sanitize($_POST['reply_message']);
        
        if (!empty($reply) && strlen($reply) >= 5) {
            replyToMessage($messageId, $_SESSION['admin_id'], $reply);
            
            // Redirect to refresh the page
            header("Location: messages.php?id=$messageId&success=1");
            exit();
        }
    }
}

// Handle status update
if (isset($_GET['action']) && isset($_GET['msg_id'])) {
    $action = $_GET['action'];
    $msg_id = $_GET['msg_id'];
    
    if ($action === 'close') {
        updateMessageStatus($msg_id, 'closed');
        header("Location: messages.php?success=1");
        exit();
    }
}

$newMessagesCount = getNewMessagesCount();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <!-- Navigation Bar -->
    <nav class="navbar">
        <div class="navbar-brand">
            <a href="dashboard.php" style="display: flex; align-items: center; text-decoration: none;">
               <img src="../assets/images/logo1.JPG" alt="<?php echo SITE_NAME; ?>" class="navbar-logo">
                <span><?php echo SITE_NAME; ?> - Admin</span>
            </a>
        </div>
        
        <ul class="navbar-menu">
            <li><a href="dashboard.php">Dashboard</a></li>
            <li><a href="applications.php">Applications</a></li>
            <li><a href="products.php">Products</a></li>
            <li><a href="orders.php">Orders</a></li>
            <li><a href="payments.php">Payments</a></li>
            <li><a href="messages.php">Messages</a></li>
            <li class="navbar-button-item"><a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a></li>
        </ul>
        
        <div class="navbar-buttons">
            <span style="margin-right: 1rem;">Admin: <?php echo htmlspecialchars($admin['username']); ?></span>
            <a href="logout.php" class="btn btn-secondary btn-capsule">Logout</a>
        </div>
        
        <div class="hamburger">
            <span></span>
            <span></span>
            <span></span>
        </div>
    </nav>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <?php if (isset($_GET['success'])): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>Success!</strong> Operation completed successfully.
            </div>
        <?php endif; ?>

        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 2rem;">
            <h1 style="margin: 0;">Contact Messages</h1>
            <span style="background-color: var(--danger-color); color: white; padding: 0.5rem 1rem; border-radius: 20px; font-size: 0.9rem;">
                <?php echo $newMessagesCount; ?> New
            </span>
        </div>

        <div style="display: flex; gap: 1rem; margin-bottom: 2rem;">
            <a href="messages.php?filter=all" class="btn <?php echo $filter === 'all' ? 'btn-primary' : 'btn-secondary'; ?>">All Messages</a>
            <a href="messages.php?filter=new" class="btn <?php echo $filter === 'new' ? 'btn-primary' : 'btn-secondary'; ?>">New</a>
            <a href="messages.php?filter=replied" class="btn <?php echo $filter === 'replied' ? 'btn-primary' : 'btn-secondary'; ?>">Replied</a>
            <a href="messages.php?filter=closed" class="btn <?php echo $filter === 'closed' ? 'btn-primary' : 'btn-secondary'; ?>">Closed</a>
        </div>

        <?php if ($currentMessage): ?>
            <!-- Message Detail View -->
            <div class="row">
                <div class="col-8">
                    <div class="card">
                        <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem; padding-bottom: 1rem; border-bottom: 2px solid var(--light-gray);">
                            <div>
                                <h2 style="margin: 0 0 0.5rem 0;"><?php echo htmlspecialchars($currentMessage['subject']); ?></h2>
                                <p style="margin: 0; color: var(--medium-gray); font-size: 0.9rem;">
                                    From: <strong><?php echo htmlspecialchars($currentMessage['name']); ?></strong> (<?php echo htmlspecialchars($currentMessage['email']); ?>)
                                </p>
                            </div>
                            <span style="background-color: <?php 
                                if ($currentMessage['status'] === 'new') echo 'var(--danger-color)';
                                elseif ($currentMessage['status'] === 'replied') echo 'var(--success-color)';
                                elseif ($currentMessage['status'] === 'read') echo 'var(--warning-color)';
                                else echo 'var(--medium-gray)';
                            ?>; color: white; padding: 0.4rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                <?php echo ucfirst($currentMessage['status']); ?>
                            </span>
                        </div>

                        <div style="margin-bottom: 2rem;">
                            <p style="color: var(--medium-gray); font-size: 0.9rem; margin: 0 0 0.5rem 0;">
                                <strong>Received:</strong> <?php echo date('d M Y, H:i', strtotime($currentMessage['created_at'])); ?>
                            </p>
                            <?php if ($currentMessage['phone']): ?>
                                <p style="color: var(--medium-gray); font-size: 0.9rem; margin: 0;">
                                    <strong>Phone:</strong> <?php echo htmlspecialchars($currentMessage['phone']); ?>
                                </p>
                            <?php endif; ?>
                        </div>

                        <div style="background-color: var(--light-gray); padding: 1.5rem; border-radius: 8px; margin-bottom: 2rem;">
                            <p style="margin: 0; white-space: pre-wrap;">
                                <?php echo htmlspecialchars($currentMessage['message']); ?>
                            </p>
                        </div>

                        <?php if ($currentMessage['admin_reply']): ?>
                            <div style="background-color: #e3f2fd; padding: 1.5rem; border-left: 4px solid var(--primary-color); border-radius: 4px; margin-bottom: 2rem;">
                                <h4 style="margin-top: 0;">Admin Reply:</h4>
                                <p style="margin: 0; white-space: pre-wrap;">
                                    <?php echo htmlspecialchars($currentMessage['admin_reply']); ?>
                                </p>
                                <p style="margin: 1rem 0 0 0; color: var(--medium-gray); font-size: 0.85rem;">
                                    Replied by Admin on <?php echo date('d M Y, H:i', strtotime($currentMessage['replied_date'])); ?>
                                </p>
                            </div>
                        <?php endif; ?>

                        <?php if ($currentMessage['status'] !== 'closed'): ?>
                            <form method="POST" action="">
                                <div class="form-group">
                                    <label for="reply_message">Send Reply</label>
                                    <textarea id="reply_message" name="reply_message" rows="5" placeholder="Type your reply here..." minlength="5" required style="margin-bottom: 1rem;"></textarea>
                                </div>
                                <div style="display: flex; gap: 1rem;">
                                    <button type="submit" class="btn btn-primary">Send Reply</button>
                                    <a href="messages.php?action=close&msg_id=<?php echo $currentMessage['id']; ?>" class="btn btn-secondary" onclick="return confirm('Are you sure you want to close this message?')">Close Message</a>
                                </div>
                            </form>
                        <?php else: ?>
                            <div style="background-color: #f0f0f0; padding: 1rem; border-radius: 4px; text-align: center;">
                                <p style="margin: 0; color: var(--medium-gray);">This message has been closed.</p>
                            </div>
                        <?php endif; ?>

                        <div style="margin-top: 2rem; padding-top: 1rem; border-top: 1px solid var(--light-gray);">
                            <a href="messages.php" class="btn btn-secondary">← Back to Messages</a>
                        </div>
                    </div>
                </div>

                <div class="col-4">
                    <div class="card" style="background-color: var(--light-gray);">
                        <h4 style="margin-top: 0;">Message Info</h4>
                        <div style="font-size: 0.9rem;">
                            <p>
                                <strong>From:</strong><br>
                                <?php echo htmlspecialchars($currentMessage['name']); ?><br>
                                <a href="mailto:<?php echo htmlspecialchars($currentMessage['email']); ?>" style="color: var(--primary-color);">
                                    <?php echo htmlspecialchars($currentMessage['email']); ?>
                                </a>
                            </p>
                            <?php if ($currentMessage['phone']): ?>
                                <p>
                                    <strong>Phone:</strong><br>
                                    <a href="tel:<?php echo htmlspecialchars($currentMessage['phone']); ?>" style="color: var(--primary-color);">
                                        <?php echo htmlspecialchars($currentMessage['phone']); ?>
                                    </a>
                                </p>
                            <?php endif; ?>
                            <p>
                                <strong>Status:</strong><br>
                                <span style="background-color: <?php 
                                    if ($currentMessage['status'] === 'new') echo 'var(--danger-color)';
                                    elseif ($currentMessage['status'] === 'replied') echo 'var(--success-color)';
                                    elseif ($currentMessage['status'] === 'read') echo 'var(--warning-color)';
                                    else echo 'var(--medium-gray)';
                                ?>; color: white; padding: 0.2rem 0.6rem; border-radius: 3px;">
                                    <?php echo ucfirst($currentMessage['status']); ?>
                                </span>
                            </p>
                            <p>
                                <strong>Received:</strong><br>
                                <?php echo date('d M Y, H:i', strtotime($currentMessage['created_at'])); ?>
                            </p>
                        </div>
                    </div>
                </div>
            </div>

        <?php else: ?>
            <!-- Messages List View -->
            <div style="overflow-x: auto;">
                <table class="table table-striped">
                    <thead>
                        <tr>
                            <th>From</th>
                            <th>Subject</th>
                            <th>Email</th>
                            <th>Date</th>
                            <th>Status</th>
                            <th>Action</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if (empty($messages)): ?>
                            <tr>
                                <td colspan="6" style="text-align: center; padding: 2rem;">
                                    No messages found
                                </td>
                            </tr>
                        <?php else: ?>
                            <?php foreach ($messages as $msg): ?>
                                <tr style="<?php echo $msg['status'] === 'new' ? 'background-color: #fff3cd;' : ''; ?>">
                                    <td><strong><?php echo htmlspecialchars($msg['name']); ?></strong></td>
                                    <td><?php echo htmlspecialchars(substr($msg['subject'], 0, 40)); ?></td>
                                    <td style="font-size: 0.9rem;">
                                        <a href="mailto:<?php echo htmlspecialchars($msg['email']); ?>" style="color: var(--primary-color);">
                                            <?php echo htmlspecialchars($msg['email']); ?>
                                        </a>
                                    </td>
                                    <td style="font-size: 0.9rem;">
                                        <?php echo date('d M Y', strtotime($msg['created_at'])); ?>
                                    </td>
                                    <td>
                                        <span style="background-color: <?php 
                                            if ($msg['status'] === 'new') echo 'var(--danger-color)';
                                            elseif ($msg['status'] === 'replied') echo 'var(--success-color)';
                                            elseif ($msg['status'] === 'read') echo 'var(--warning-color)';
                                            else echo 'var(--medium-gray)';
                                        ?>; color: white; padding: 0.3rem 0.8rem; border-radius: 4px; font-size: 0.85rem;">
                                            <?php echo ucfirst($msg['status']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <a href="messages.php?id=<?php echo $msg['id']; ?>" class="btn btn-primary" style="padding: 0.4rem 0.8rem; font-size: 0.85rem;">
                                            View
                                        </a>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </div>

    <!-- Footer -->
    <footer class="footer" style="margin-top: 4rem;">
        <div class="footer-content">
            <div class="footer-section">
                <img src="../assets/images/logo1.JPG" alt="<?php echo COMPANY_NAME; ?>" class="footer-logo">
                <p>Leading B2B retailer ordering and GST billing platform.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="messages.php">Messages</a></li>
                    <li><a href="logout.php">Logout</a></li>
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
