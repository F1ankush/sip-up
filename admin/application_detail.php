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
        $admin_id = $_SESSION['admin_id'];
        
        // Step 1: Update application status
        $stmt = $db->prepare("UPDATE retailer_applications SET status = ?, approval_date = ?, approval_remarks = ?, approved_by = ? WHERE id = ?");
        if (!$stmt) {
            $error_message = 'Database error: ' . $db->error;
            error_log("Approval Error: " . $db->error);
        } else {
            $stmt->bind_param("sssii", $status, $approvalDate, $remarks, $admin_id, $appId);
            
            if ($stmt->execute()) {
                $stmt->close();
                
                // Step 2: Create user account using the dedicated function
                $username = isset($application['name']) ? $application['name'] : 'user';
                $result = createUserAccountOnApproval($appId, $application['email'], $application['phone'], $username, $application['shop_address']);
                
                if ($result['success']) {
                    $success_message = 'Application approved successfully! User account created.';
                    $application['status'] = 'approved';
                } else {
                    $error_message = isset($result['message']) ? $result['message'] : 'Error creating user account';
                }
            } else {
                $error_message = 'Error updating application: ' . $stmt->error;
                error_log("Update Error: " . $stmt->error);
                $stmt->close();
            }
        }
    } elseif ($action === 'reject') {
        // Reject application
        $approvalDate = date('Y-m-d H:i:s');
        $status = 'rejected';
        $admin_id = $_SESSION['admin_id'];
        
        $stmt = $db->prepare("UPDATE retailer_applications SET status = ?, approval_date = ?, approval_remarks = ?, approved_by = ? WHERE id = ?");
        if (!$stmt) {
            $error_message = 'Database error: ' . $db->error;
            error_log("Reject Prepare Error: " . $db->error);
        } else {
            $stmt->bind_param("sssii", $status, $approvalDate, $remarks, $admin_id, $appId);
            
            if ($stmt->execute()) {
                $stmt->close();
                $success_message = 'Application rejected successfully!';
                $application['status'] = 'rejected';
                $application['approval_remarks'] = $remarks;
            } else {
                $error_message = 'Error rejecting application: ' . $stmt->error;
                error_log("Reject Execute Error: " . $stmt->error);
                $stmt->close();
            }
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
                <strong>Success!</strong> <?php echo $success_message; ?>
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
                    <p style="margin: 0; font-weight: 600; font-size: 1.1rem;"><?php echo htmlspecialchars(isset($application['shop_address']) ? $application['shop_address'] : 'N/A'); ?></p>
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
    <?php renderFooter(); ?>

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
