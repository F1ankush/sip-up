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

$success_message = '';
$error_message = '';

// Get filter from URL
$filter = $_GET['filter'] ?? 'all';
$message_id = intval($_GET['id'] ?? 0);

// Handle message actions
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $msg_id = intval($_POST['message_id'] ?? 0);
    
    if ($action === 'mark_read') {
        $stmt = $db->prepare("UPDATE contact_messages SET status = 'read' WHERE id = ?");
        $stmt->bind_param("i", $msg_id);
        if ($stmt->execute()) {
            $success_message = 'Message marked as read';
        }
    } elseif ($action === 'reply') {
        $reply = sanitize($_POST['reply'] ?? '');
        if (empty($reply) || strlen($reply) < 5) {
            $error_message = 'Reply must be at least 5 characters';
        } else {
            $admin_id = $_SESSION['admin_id'];
            $stmt = $db->prepare("UPDATE contact_messages SET status = 'replied', admin_reply = ?, replied_by = ?, replied_date = NOW() WHERE id = ?");
            $stmt->bind_param("sii", $reply, $admin_id, $msg_id);
            if ($stmt->execute()) {
                $success_message = 'Reply sent successfully';
                $message_id = 0;
            }
        }
    } elseif ($action === 'mark_closed') {
        $stmt = $db->prepare("UPDATE contact_messages SET status = 'closed' WHERE id = ?");
        $stmt->bind_param("i", $msg_id);
        if ($stmt->execute()) {
            $success_message = 'Message marked as closed';
        }
    } elseif ($action === 'delete') {
        $stmt = $db->prepare("DELETE FROM contact_messages WHERE id = ?");
        $stmt->bind_param("i", $msg_id);
        if ($stmt->execute()) {
            $success_message = 'Message deleted successfully';
            $message_id = 0;
        }
    }
}

// Get messages based on filter
if ($filter === 'all') {
    $stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
} elseif ($filter === 'new') {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE status = 'new' ORDER BY created_at DESC");
} elseif ($filter === 'read') {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE status = 'read' ORDER BY created_at DESC");
} elseif ($filter === 'replied') {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE status = 'replied' ORDER BY created_at DESC");
} else {
    $stmt = $db->prepare("SELECT * FROM contact_messages ORDER BY created_at DESC");
}

$stmt->execute();
$messages = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Get message counts by status
$stmt_counts = $db->prepare("SELECT status, COUNT(*) as count FROM contact_messages GROUP BY status");
$stmt_counts->execute();
$status_counts = [];
while ($row = $stmt_counts->get_result()->fetch_assoc()) {
    $status_counts[$row['status']] = $row['count'];
}

// Get single message if viewing details
$selected_message = null;
if ($message_id > 0) {
    $stmt = $db->prepare("SELECT * FROM contact_messages WHERE id = ?");
    $stmt->bind_param("i", $message_id);
    $stmt->execute();
    $selected_message = $stmt->get_result()->fetch_assoc();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Contact Messages - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .message-list {
            max-height: 600px;
            overflow-y: auto;
            border: 1px solid #e5e7eb;
            border-radius: 8px;
        }
        .message-item {
            padding: 1rem;
            border-bottom: 1px solid #e5e7eb;
            cursor: pointer;
            transition: background-color 0.2s;
        }
        .message-item:hover {
            background-color: #f9fafb;
        }
        .message-item.active {
            background-color: #e0e7ff;
            border-left: 4px solid var(--primary-color);
        }
        .message-item.unread {
            background-color: #eff6ff;
            font-weight: 600;
        }
        .status-badge {
            display: inline-block;
            padding: 0.25rem 0.75rem;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 600;
        }
        .status-new {
            background-color: #dbeafe;
            color: #0369a1;
        }
        .status-read {
            background-color: #fef3c7;
            color: #92400e;
        }
        .status-replied {
            background-color: #d1fae5;
            color: #065f46;
        }
        .status-closed {
            background-color: #f3f4f6;
            color: #6b7280;
        }
        .filter-buttons {
            display: flex;
            gap: 0.5rem;
            margin-bottom: 1.5rem;
            flex-wrap: wrap;
        }
        .filter-btn {
            padding: 0.5rem 1rem;
            border: 2px solid #e5e7eb;
            background-color: white;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.2s;
            font-weight: 500;
        }
        .filter-btn.active {
            border-color: var(--primary-color);
            background-color: var(--primary-color);
            color: white;
        }
        .filter-btn:hover {
            border-color: var(--primary-color);
        }
        .message-detail {
            background-color: #f9fafb;
            padding: 1.5rem;
            border-radius: 8px;
        }
        .reply-section {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid #e5e7eb;
        }
    </style>
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto;">
        <h1>Contact Messages</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="margin-bottom: 1.5rem;">
                <?php echo $success_message; ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="margin-bottom: 1.5rem;">
                <?php echo $error_message; ?>
            </div>
        <?php endif; ?>

        <!-- Filter Buttons -->
        <div class="filter-buttons">
            <a href="messages.php?filter=all" class="filter-btn <?php echo $filter === 'all' ? 'active' : ''; ?>">
                All Messages (<?php echo array_sum($status_counts); ?>)
            </a>
            <a href="messages.php?filter=new" class="filter-btn <?php echo $filter === 'new' ? 'active' : ''; ?>">
                New (<?php echo $status_counts['new'] ?? 0; ?>)
            </a>
            <a href="messages.php?filter=read" class="filter-btn <?php echo $filter === 'read' ? 'active' : ''; ?>">
                Read (<?php echo $status_counts['read'] ?? 0; ?>)
            </a>
            <a href="messages.php?filter=replied" class="filter-btn <?php echo $filter === 'replied' ? 'active' : ''; ?>">
                Replied (<?php echo $status_counts['replied'] ?? 0; ?>)
            </a>
        </div>

        <div class="row">
            <!-- Messages List -->
            <div class="col-5">
                <h3>Messages</h3>
                <div class="message-list">
                    <?php if (empty($messages)): ?>
                        <div style="padding: 2rem; text-align: center; color: #6b7280;">
                            No messages found
                        </div>
                    <?php else: ?>
                        <?php foreach ($messages as $msg): ?>
                            <div class="message-item <?php echo $msg['id'] === $message_id ? 'active' : ''; ?> <?php echo $msg['status'] === 'new' ? 'unread' : ''; ?>" onclick="selectMessage(<?php echo $msg['id']; ?>)">
                                <div style="display: flex; justify-content: space-between; align-items: start; margin-bottom: 0.5rem;">
                                    <strong><?php echo htmlspecialchars(substr($msg['name'], 0, 25)); ?></strong>
                                    <span class="status-badge status-<?php echo $msg['status']; ?>">
                                        <?php echo ucfirst($msg['status']); ?>
                                    </span>
                                </div>
                                <p style="margin: 0.5rem 0; color: #6b7280; font-size: 0.9rem;">
                                    <strong><?php echo htmlspecialchars(substr($msg['subject'], 0, 40)); ?></strong>
                                </p>
                                <p style="margin: 0.5rem 0; color: #9ca3af; font-size: 0.85rem;">
                                    <?php echo htmlspecialchars(substr($msg['message'], 0, 60)); ?>...
                                </p>
                                <p style="margin: 0.5rem 0 0 0; color: #9ca3af; font-size: 0.8rem;">
                                    <?php echo date('d M Y, h:i A', strtotime($msg['created_at'])); ?>
                                </p>
                            </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Message Details -->
            <div class="col-7">
                <h3>Message Details</h3>
                <?php if ($selected_message): ?>
                    <form method="POST" action="">
                        <div class="message-detail">
                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                <div>
                                    <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">FROM</p>
                                    <p style="margin: 0; font-weight: 600;"><?php echo htmlspecialchars($selected_message['name']); ?></p>
                                </div>
                                <div>
                                    <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">EMAIL</p>
                                    <p style="margin: 0;">
                                        <a href="mailto:<?php echo htmlspecialchars($selected_message['email']); ?>" style="color: var(--primary-color); text-decoration: none;">
                                            <?php echo htmlspecialchars($selected_message['email']); ?>
                                        </a>
                                    </p>
                                </div>
                                <div>
                                    <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">PHONE</p>
                                    <p style="margin: 0;">
                                        <?php echo !empty($selected_message['phone']) ? htmlspecialchars($selected_message['phone']) : 'Not provided'; ?>
                                    </p>
                                </div>
                                <div>
                                    <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">DATE</p>
                                    <p style="margin: 0;">
                                        <?php echo date('d M Y, h:i A', strtotime($selected_message['created_at'])); ?>
                                    </p>
                                </div>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">SUBJECT</p>
                                <p style="margin: 0; font-weight: 600; font-size: 1.1rem;">
                                    <?php echo htmlspecialchars($selected_message['subject']); ?>
                                </p>
                            </div>

                            <div style="margin-bottom: 1.5rem;">
                                <p style="color: #6b7280; margin: 0 0 0.3rem 0; font-size: 0.9rem;">MESSAGE</p>
                                <div style="background-color: white; padding: 1rem; border-radius: 6px; border: 1px solid #e5e7eb; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($selected_message['message'])); ?>
                                </div>
                            </div>

                            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                                <div>
                                    <span class="status-badge status-<?php echo $selected_message['status']; ?>">
                                        Status: <?php echo ucfirst($selected_message['status']); ?>
                                    </span>
                                </div>
                            </div>

                            <?php if (!empty($selected_message['admin_reply'])): ?>
                            <div style="background-color: #d1fae5; padding: 1rem; border-radius: 6px; margin-bottom: 1.5rem; border-left: 4px solid #10b981;">
                                <p style="color: #065f46; margin: 0 0 0.5rem 0; font-weight: 600;">Your Reply:</p>
                                <div style="color: #047857; line-height: 1.6;">
                                    <?php echo nl2br(htmlspecialchars($selected_message['admin_reply'])); ?>
                                </div>
                                <?php if ($selected_message['replied_date']): ?>
                                <p style="color: #6b7280; font-size: 0.85rem; margin: 0.5rem 0 0 0;">
                                    Replied on <?php echo date('d M Y, h:i A', strtotime($selected_message['replied_date'])); ?>
                                </p>
                                <?php endif; ?>
                            </div>
                            <?php endif; ?>

                            <!-- Action Buttons -->
                            <div style="display: flex; gap: 0.5rem; flex-wrap: wrap;">
                                <?php if ($selected_message['status'] === 'new'): ?>
                                    <button type="submit" name="action" value="mark_read" class="btn btn-primary" style="padding: 0.6rem 1.2rem;">
                                        Mark as Read
                                    </button>
                                <?php endif; ?>
                                
                                <?php if ($selected_message['status'] !== 'closed' && $selected_message['status'] !== 'replied'): ?>
                                    <button type="button" class="btn btn-secondary" style="padding: 0.6rem 1.2rem;" onclick="document.getElementById('reply-form').style.display = 'block';">
                                        Send Reply
                                    </button>
                                <?php endif; ?>

                                <?php if ($selected_message['status'] !== 'closed'): ?>
                                    <button type="submit" name="action" value="mark_closed" class="btn btn-warning" style="padding: 0.6rem 1.2rem;">
                                        Mark as Closed
                                    </button>
                                <?php endif; ?>

                                <button type="submit" name="action" value="delete" class="btn btn-danger" style="padding: 0.6rem 1.2rem;" onclick="return confirm('Are you sure you want to delete this message?');">
                                    Delete
                                </button>
                            </div>

                            <input type="hidden" name="message_id" value="<?php echo $selected_message['id']; ?>">
                        </div>
                    </form>

                    <!-- Reply Form -->
                    <div id="reply-form" style="display: none; margin-top: 1.5rem;">
                        <form method="POST" action="">
                            <div class="reply-section">
                                <h4 style="margin-top: 0;">Send Reply</h4>
                                <div class="form-group">
                                    <textarea name="reply" placeholder="Type your reply here..." rows="6" style="width: 100%; padding: 0.75rem; border: 1px solid #e5e7eb; border-radius: 6px; font-family: inherit;" required></textarea>
                                </div>
                                <div style="display: flex; gap: 0.5rem;">
                                    <button type="submit" name="action" value="reply" class="btn btn-success" style="padding: 0.6rem 1.5rem;">
                                        Send Reply
                                    </button>
                                    <button type="button" class="btn btn-secondary" style="padding: 0.6rem 1.5rem;" onclick="document.getElementById('reply-form').style.display = 'none';">
                                        Cancel
                                    </button>
                                </div>
                            </div>
                            <input type="hidden" name="message_id" value="<?php echo $selected_message['id']; ?>">
                        </form>
                    </div>
                <?php else: ?>
                    <div style="padding: 2rem; text-align: center; color: #6b7280; background-color: #f9fafb; border-radius: 8px;">
                        <p>Select a message to view details</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <?php renderFooter(); ?>

    <script>
        function selectMessage(id) {
            window.location.href = 'messages.php?id=' + id + '&filter=<?php echo $filter; ?>';
        }
    </script>
</body>
</html>
