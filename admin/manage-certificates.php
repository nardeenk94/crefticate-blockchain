<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$certificates = get_all_certificates();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Certificates - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Manage Certificates</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <?php if (empty($certificates)): ?>
            <div class="no-data-message">
                <div class="icon">📄</div>
                <h2>No Certificates</h2>
                <p>No certificates have been issued yet.</p>
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
                            <th>Issuer</th>
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
                            <td><?php echo htmlspecialchars($cert['issuer_name']); ?></td>
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
                                <a href="review-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-info">Review</a>
                                <?php if ($cert['verification_status'] === 'pending'): ?>
                                <a href="../api/approve-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-success">Approve</a>
                                <a href="../api/reject-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-danger">Reject</a>
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