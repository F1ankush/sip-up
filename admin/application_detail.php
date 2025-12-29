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

// Get application ID
$appId = intval($_GET['id'] ?? 0);
if ($appId <= 0) {
    header("Location: applications.php");
    exit();
}

// Get application details
$stmt = $db->prepare("SELECT * FROM retailer_applications WHERE id = ?");
$stmt->bind_param("i", $appId);
$stmt->execute();
$application = $stmt->get_result()->fetch_assoc();

if (!$application) {
    header("Location: applications.php");
    exit();
}

$success_message = '';
$error_message = '';

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $action = $_POST['action'] ?? '';
    $remarks = sanitize($_POST['remarks'] ?? '');
    
    if ($action === 'approve') {
        // Approve application
        $approvalDate = date('Y-m-d H:i:s');
        $status = 'approved';
        
        // Step 1: Update application status
        $stmt = $db->prepare("UPDATE retailer_applications SET status = ?, approval_date = ?, approval_remarks = ?, approved_by = ? WHERE id = ?");
        if (!$stmt) {
            $error_message = 'Database error: Unable to update application status';
        } else {
            $stmt->bind_param("sssii", $status, $approvalDate, $remarks, $_SESSION['admin_id'], $appId);
            
            if ($stmt->execute()) {
                // Step 2: Create user account using the dedicated function
                $username = $application['name']; // Use shop name as username
                $result = createUserAccountOnApproval($appId, $application['email'], $application['phone'], $username, $application['shop_address']);
                
                if ($result['success']) {
                    $success_message = 'Application approved successfully! User account created with default password: <strong>12345678</strong>';
                    // Refresh application details
                    $application['status'] = 'approved';
                } else {
                    $error_message = $result['message'];
                }
            } else {
                $error_message = 'Error updating application: ' . $stmt->error;
            }
        }
    } elseif ($action === 'reject') {
        // Reject application
        $approvalDate = date('Y-m-d H:i:s');
        $status = 'rejected';
        
        $stmt = $db->prepare("UPDATE retailer_applications SET status = ?, approval_date = ?, approval_remarks = ?, approved_by = ? WHERE id = ?");
        $stmt->bind_param("sssii", $status, $approvalDate, $remarks, $_SESSION['admin_id'], $appId);
        
        if ($stmt->execute()) {
            $success_message = 'Application rejected successfully!';
            // Refresh application details
            $application['status'] = 'rejected';
            $application['approval_remarks'] = $remarks;
        } else {
            $error_message = 'Error rejecting application. Please try again.';
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Application Details - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php renderAdminNavbar(); ?>

    <!-- Main Content -->
    <div class="container" style="margin: 3rem auto; max-width: 800px;">
        <a href="applications.php" style="display: inline-block; margin-bottom: 1.5rem; color: #2563eb;">← Back to Applications</a>
        
        <h1>Application Details</h1>

        <?php if ($success_message): ?>
            <div class="alert alert-success" style="margin-bottom: 2rem;">
                <strong>Success!</strong> <?php echo htmlspecialchars($success_message); ?>
            </div>
        <?php endif; ?>

        <?php if ($error_message): ?>
            <div class="alert alert-danger" style="margin-bottom: 2rem;">
                <strong>Error:</strong> <?php echo htmlspecialchars($error_message); ?>
            </div>
        <?php endif; ?>

        <div class="card">
            <div style="margin-bottom: 2rem;">
                <h3 style="margin: 0 0 1rem 0;">Applicant Information</h3>
                
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 2rem; margin-bottom: 1.5rem;">
                    <div>
                        <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Full Name</p>
                        <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($application['name']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Email Address</p>
                        <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($application['email']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Mobile Number</p>
                        <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($application['phone']); ?></p>
                    </div>
                    <div>
                        <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Application Date</p>
                        <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo date('d M Y, h:i A', strtotime($application['applied_date'])); ?></p>
                    </div>
                </div>

                <div style="margin-bottom: 1.5rem;">
                    <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Shop Address</p>
                    <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars($application['shop_address']); ?></p>
                </div>
            </div>

            <hr style="margin: 2rem 0; border: none; border-top: 1px solid #e5e7eb;">

            <div style="margin-bottom: 2rem;">
                <h3 style="margin: 0 0 1rem 0;">Application Status</h3>
                
                <div>
                    <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Current Status</p>
                    <p style="margin: 0;">
                        <span style="background-color: <?php 
                            if ($application['status'] === 'approved') echo 'var(--success-color)';
                            elseif ($application['status'] === 'rejected') echo 'var(--danger-color)';
                            else echo 'var(--warning-color)';
                        ?>; color: white; padding: 0.4rem 1rem; border-radius: 4px; font-weight: 600; display: inline-block;">
                            <?php echo ucfirst($application['status']); ?>
                        </span>
                    </p>
                </div>

                <?php if ($application['approval_remarks']): ?>
                    <div style="margin-top: 1rem;">
                        <p style="color: #6b7280; margin: 0 0 0.3rem 0;">Approval Remarks</p>
                        <p style="margin: 0; background-color: #f3f4f6; padding: 1rem; border-radius: 4px;">
                            <?php echo htmlspecialchars($application['approval_remarks']); ?>
                        </p>
                    </div>
                <?php endif; ?>
            </div>

            <?php if ($application['status'] === 'pending'): ?>
                <hr style="margin: 2rem 0; border: none; border-top: 1px solid #e5e7eb;">

                <div>
                    <h3 style="margin: 0 0 1.5rem 0;">Take Action</h3>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 1rem; margin-bottom: 1.5rem;">
                        <button type="button" class="btn btn-success" style="padding: 0.8rem;" onclick="openApprovalModal();">
                            ✓ Approve Application
                        </button>
                        <button type="button" class="btn btn-danger" style="padding: 0.8rem;" onclick="openRejectionModal();">
                            ✗ Reject Application
                        </button>
                    </div>
                </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Approval Modal -->
    <div id="approvalModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Approve Application</h2>
                <button type="button" onclick="closeApprovalModal()" style="background: none; border: none; font-size: 2rem; cursor: pointer;">&times;</button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="approve">

                <div class="form-group">
                    <label for="remarks">Approval Remarks (Optional)</label>
                    <textarea 
                        id="remarks" 
                        name="remarks" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Add any remarks or conditions for approval..."
                    ></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-success" style="flex: 1;">Confirm Approval</button>
                    <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="closeApprovalModal();">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Rejection Modal -->
    <div id="rejectionModal" class="modal" style="display: none;">
        <div class="modal-content">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 1.5rem;">
                <h2 style="margin: 0;">Reject Application</h2>
                <button type="button" onclick="closeRejectionModal()" style="background: none; border: none; font-size: 2rem; cursor: pointer;">&times;</button>
            </div>

            <form method="POST" action="">
                <input type="hidden" name="action" value="reject">

                <div class="form-group">
                    <label for="reject_remarks">Rejection Reason <span class="required">*</span></label>
                    <textarea 
                        id="reject_remarks" 
                        name="remarks" 
                        class="form-control" 
                        rows="4" 
                        placeholder="Please explain why this application is being rejected..." 
                        required
                    ></textarea>
                </div>

                <div style="display: flex; gap: 1rem; margin-top: 1.5rem;">
                    <button type="submit" class="btn btn-danger" style="flex: 1;">Confirm Rejection</button>
                    <button type="button" class="btn btn-secondary" style="flex: 1;" onclick="closeRejectionModal();">Cancel</button>
                </div>
            </form>
        </div>
    </div>

    <!-- Footer -->
    <footer class="footer" style="margin-top: 4rem;">
        <div class="footer-content">
            <div class="footer-section">
                <img src="../assets/images/logo1.jpg" alt="<?php echo COMPANY_NAME; ?>" class="footer-logo">
                <p>Leading B2B retailer ordering and GST billing platform.</p>
            </div>
            
            <div class="footer-section">
                <h3>Quick Links</h3>
                <ul>
                    <li><a href="dashboard.php">Dashboard</a></li>
                    <li><a href="applications.php">Applications</a></li>
                    <li><a href="products.php">Products</a></li>
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
    <script>
        function openApprovalModal() {
            document.getElementById('approvalModal').style.display = 'flex';
        }

        function closeApprovalModal() {
            document.getElementById('approvalModal').style.display = 'none';
        }

        function openRejectionModal() {
            document.getElementById('rejectionModal').style.display = 'flex';
        }

        function closeRejectionModal() {
            document.getElementById('rejectionModal').style.display = 'none';
        }

        // Close modal when clicking outside
        document.querySelectorAll('.modal').forEach(modal => {
            modal.addEventListener('click', function(e) {
                if (e.target === this) {
                    this.style.display = 'none';
                }
            });
        });
    </script>
</body>
</html>
