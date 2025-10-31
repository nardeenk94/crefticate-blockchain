<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['issuer', 'admin']);

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $recipient_name = sanitize_input($_POST['recipient_name'] ?? '');
        $recipient_id = sanitize_input($_POST['recipient_id'] ?? '');
        $recipient_email = sanitize_input($_POST['recipient_email'] ?? '');
        $title = sanitize_input($_POST['title'] ?? '');
        $field = sanitize_input($_POST['field'] ?? '');
        $description = sanitize_input($_POST['description'] ?? '');
        $issue_date = sanitize_input($_POST['issue_date'] ?? '');
        
        // Validation
        if (empty($recipient_name)) $errors[] = 'Recipient name is required';
        if (empty($recipient_id)) $errors[] = 'Recipient ID is required';
        if (empty($recipient_email) || !filter_var($recipient_email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid recipient email is required';
        }
        if (empty($title)) $errors[] = 'Certificate title is required';
        if (empty($field)) $errors[] = 'Field is required';
        if (empty($issue_date)) $errors[] = 'Issue date is required';
        
        // Handle file upload
        $certificate_file = null;
        if (isset($_FILES['certificate_file']) && $_FILES['certificate_file']['error'] === UPLOAD_ERR_OK) {
            $upload_result = upload_certificate_file($_FILES['certificate_file']);
            if ($upload_result['success']) {
                $certificate_file = $upload_result['filename'];
            } else {
                $errors[] = $upload_result['error'];
            }
        } else {
            $errors[] = 'Certificate file is required';
        }
        
        if (empty($errors)) {
            $certificate_id = generate_certificate_id();
            
            $certificate_data = [
                'certificate_id' => $certificate_id,
                'issuer_id' => $_SESSION['user_id'],
                'recipient_name' => $recipient_name,
                'recipient_id' => $recipient_id,
                'recipient_email' => $recipient_email,
                'title' => $title,
                'field' => $field,
                'description' => $description,
                'issue_date' => $issue_date,
                'certificate_file' => $certificate_file
            ];
            
            if (create_certificate($certificate_data)) {
                $success = "Certificate issued successfully! Certificate ID: $certificate_id";
                
                // Send email notification to recipient
                $message = "<h2>Certificate Issued</h2>";
                $message .= "<p>Dear $recipient_name,</p>";
                $message .= "<p>A certificate has been issued to you.</p>";
                $message .= "<p><strong>Certificate ID:</strong> $certificate_id</p>";
                $message .= "<p><strong>Title:</strong> $title</p>";
                $message .= "<p>You can verify your certificate at: " . SITE_URL . "/verify.php?id=$certificate_id</p>";
                
                send_email_notification($recipient_email, 'Certificate Issued', $message);
                
                // Clear form
                $_POST = [];
            } else {
                $errors[] = 'Failed to issue certificate. Please try again.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issue Certificate - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Issue New Certificate</h1>
            <a href="my-certificates.php" class="btn btn-secondary">View My Certificates</a>
        </div>
        
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <ul>
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <?php if ($success): ?>
            <div class="alert alert-success">
                <?php echo $success; ?>
                <br><a href="my-certificates.php">View all certificates</a>
            </div>
        <?php endif; ?>
        
        <form method="POST" action="" enctype="multipart/form-data" class="certificate-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
            
            <div class="form-section">
                <h2>Recipient Information</h2>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="recipient_name">Full Name *</label>
                        <input type="text" id="recipient_name" name="recipient_name" required
                               value="<?php echo htmlspecialchars($_POST['recipient_name'] ?? ''); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label for="recipient_id">Recipient ID *</label>
                        <input type="text" id="recipient_id" name="recipient_id" required
                               value="<?php echo htmlspecialchars($_POST['recipient_id'] ?? ''); ?>"
                               placeholder="e.g., National ID, Student ID">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="recipient_email">Email Address *</label>
                    <input type="email" id="recipient_email" name="recipient_email" required
                           value="<?php echo htmlspecialchars($_POST['recipient_email'] ?? ''); ?>">
                </div>
            </div>
            
            <div class="form-section">
                <h2>Certificate Details</h2>
                
                <div class="form-group">
                    <label for="title">Certificate Title *</label>
                    <input type="text" id="title" name="title" required
                           value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>"
                           placeholder="e.g., Certificate of Completion">
                </div>
                
                <div class="form-row">
                    <div class="form-group">
                        <label for="field">Field/Category *</label>
                        <select id="field" name="field" required>
                            <option value="">Select Field</option>
                            <option value="Education">Education</option>
                            <option value="Technology">Technology</option>
                            <option value="Healthcare">Healthcare</option>
                            <option value="Business">Business</option>
                            <option value="Engineering">Engineering</option>
                            <option value="Arts">Arts</option>
                            <option value="Science">Science</option>
                            <option value="Other">Other</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label for="issue_date">Issue Date *</label>
                        <input type="date" id="issue_date" name="issue_date" required
                               value="<?php echo htmlspecialchars($_POST['issue_date'] ?? date('Y-m-d')); ?>">
                    </div>
                </div>
                
                <div class="form-group">
                    <label for="description">Description</label>
                    <textarea id="description" name="description" rows="4"
                              placeholder="Brief description of the certificate"><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                </div>
            </div>
            
            <div class="form-section">
                <h2>Certificate File</h2>
                
                <div class="form-group">
                    <label for="certificate_file">Upload Certificate (PDF, JPG, PNG) *</label>
                    <input type="file" id="certificate_file" name="certificate_file" required
                           accept=".pdf,.jpg,.jpeg,.png">
                    <small>Maximum file size: <?php echo MAX_FILE_SIZE / 1048576; ?>MB</small>
                </div>
            </div>
            
            <div class="form-actions">
                <button type="submit" class="btn btn-primary">Issue Certificate</button>
                <a href="dashboard.php" class="btn btn-secondary">Cancel</a>
            </div>
        </form>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>