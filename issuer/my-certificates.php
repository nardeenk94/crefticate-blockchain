<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['issuer', 'admin']);

$user_id = $_SESSION['user_id'];
$certificates = get_certificates_by_issuer($user_id);
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>My Certificates - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>My Issued Certificates</h1>
            <a href="issue-certificate.php" class="btn btn-primary">Issue New Certificate</a>
        </div>
        
        <?php if (empty($certificates)): ?>
            <div class="no-data-message">
                <div class="icon">📄</div>
                <h2>No Certificates Yet</h2>
                <p>You haven't issued any certificates yet.</p>
                <a href="issue-certificate.php" class="btn btn-primary">Issue Your First Certificate</a>
            </div>
        <?php else: ?>
            <div class="certificates-list">
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Certificate ID</th>
                            <th>Recipient</th>
                            <th>Title</th>
                            <th>Field</th>
                            <th>Issue Date</th>
                            <th>Status</th>
                            <th>Verification</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($certificates as $cert): ?>
                        <tr>
                            <td>
                                <a href="../verify.php?id=<?php echo htmlspecialchars($cert['certificate_id']); ?>">
                                    <?php echo htmlspecialchars($cert['certificate_id']); ?>
                                </a>
                            </td>
                            <td>
                                <div><?php echo htmlspecialchars($cert['recipient_name']); ?></div>
                                <small><?php echo htmlspecialchars($cert['recipient_email']); ?></small>
                            </td>
                            <td><?php echo htmlspecialchars($cert['title']); ?></td>
                            <td><?php echo htmlspecialchars($cert['field']); ?></td>
                            <td><?php echo format_date($cert['issue_date']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $cert['status']; ?>">
                                    <?php echo ucfirst($cert['status']); ?>
                                </span>
                            </td>
                            <td>
                                <span class="badge badge-<?php echo $cert['verification_status']; ?>">
                                    <?php echo ucfirst($cert['verification_status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="view-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-info" title="View">👁️</a>
                                <a href="../verify.php?id=<?php echo htmlspecialchars($cert['certificate_id']); ?>" 
                                   class="btn btn-sm btn-success" title="Verify">✓</a>
                                <?php if ($cert['status'] === 'active'): ?>
                                <a href="revoke-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-danger" title="Revoke"
                                   onclick="return confirm('Are you sure you want to revoke this certificate?')">🚫</a>
                                <?php endif; ?>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
        <?php endif; ?>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>