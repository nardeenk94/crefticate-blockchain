<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$cert_id = $_GET['id'] ?? null;

if (!$cert_id) {
    $_SESSION['error'] = 'Invalid certificate ID';
    header('Location: manage-certificates.php');
    exit();
}

// Get certificate details
$pdo = getPDOConnection();
$stmt = $pdo->prepare("
    SELECT c.*, u.name as issuer_name, u.email as issuer_email 
    FROM certificates c 
    JOIN users u ON c.issuer_id = u.id 
    WHERE c.id = ?
");
$stmt->execute([$cert_id]);
$certificate = $stmt->fetch();

if (!$certificate) {
    $_SESSION['error'] = 'Certificate not found';
    header('Location: manage-certificates.php');
    exit();
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Review Certificate - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .review-container {
            max-width: 1000px;
            margin: 2rem auto;
        }

        .review-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .review-header h1 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .status-badges {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        .badge-large {
            padding: 0.75rem 1.5rem;
            border-radius: 8px;
            font-weight: bold;
            font-size: 1.1rem;
        }

        .badge-pending {
            background: #bee3f8;
            color: #2c5282;
        }

        .badge-verified {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-rejected {
            background: #fed7d7;
            color: #742a2a;
        }

        .badge-active {
            background: #c6f6d5;
            color: #22543d;
        }

        .badge-revoked {
            background: #feebc8;
            color: #7c2d12;
        }

        .certificate-preview {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .detail-row {
            display: grid;
            grid-template-columns: 200px 1fr;
            padding: 1rem 0;
            border-bottom: 1px solid #e2e8f0;
        }

        .detail-row:last-child {
            border-bottom: none;
        }

        .detail-label {
            font-weight: 600;
            color: #4a5568;
        }

        .detail-value {
            color: #2d3748;
        }

        .certificate-file-preview {
            margin-top: 2rem;
            text-align: center;
        }

        .certificate-file-preview img {
            max-width: 100%;
            border-radius: 8px;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .action-buttons {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            text-align: center;
        }

        .action-buttons h3 {
            margin-bottom: 1.5rem;
            color: #2d3748;
        }

        .button-group {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-large {
            padding: 1rem 2.5rem;
            font-size: 1.1rem;
            border-radius: 8px;
            text-decoration: none;
            display: inline-block;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-approve {
            background: #48bb78;
            color: white;
        }

        .btn-approve:hover {
            background: #38a169;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(72, 187, 120, 0.4);
        }

        .btn-reject {
            background: #f56565;
            color: white;
        }

        .btn-reject:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(245, 101, 101, 0.4);
        }

        .btn-revoke {
            background: #ed8936;
            color: white;
        }

        .btn-revoke:hover {
            background: #dd6b20;
        }

        .btn-back {
            background: #cbd5e0;
            color: #2d3748;
        }

        .btn-back:hover {
            background: #a0aec0;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<main class="container">
    <div class="review-container">
        <!-- Header -->
        <div class="review-header">
            <h1>📋 Review Certificate</h1>
            <p>Review and approve/reject this certificate</p>
            <div class="status-badges">
                    <span class="badge-large badge-<?php echo $certificate['verification_status']; ?>">
                        <?php echo ucfirst($certificate['verification_status']); ?>
                    </span>
                <span class="badge-large badge-<?php echo $certificate['status']; ?>">
                        <?php echo ucfirst($certificate['status']); ?>
                    </span>
            </div>
        </div>

        <!-- Certificate Details -->
        <div class="certificate-preview">
            <h2>Certificate Details</h2>

            <div class="detail-row">
                <div class="detail-label">Certificate ID:</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($certificate['certificate_id']); ?></strong></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Recipient Name:</div>
                <div class="detail-value"><?php echo htmlspecialchars($certificate['recipient_name']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Recipient ID:</div>
                <div class="detail-value"><?php echo htmlspecialchars($certificate['recipient_id']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Recipient Email:</div>
                <div class="detail-value"><?php echo htmlspecialchars($certificate['recipient_email']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Certificate Title:</div>
                <div class="detail-value"><strong><?php echo htmlspecialchars($certificate['title']); ?></strong></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Field:</div>
                <div class="detail-value"><?php echo htmlspecialchars($certificate['field']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Description:</div>
                <div class="detail-value"><?php echo nl2br(htmlspecialchars($certificate['description'])); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Issue Date:</div>
                <div class="detail-value"><?php echo format_date($certificate['issue_date']); ?></div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Issued By:</div>
                <div class="detail-value">
                    <?php echo htmlspecialchars($certificate['issuer_name']); ?>
                    <br>
                    <small><?php echo htmlspecialchars($certificate['issuer_email']); ?></small>
                </div>
            </div>

            <div class="detail-row">
                <div class="detail-label">Created At:</div>
                <div class="detail-value"><?php echo date('F j, Y g:i A', strtotime($certificate['created_at'])); ?></div>
            </div>

            <?php if ($certificate['ipfs_hash']): ?>
                <div class="detail-row">
                    <div class="detail-label">IPFS Hash:</div>
                    <div class="detail-value"><code><?php echo htmlspecialchars($certificate['ipfs_hash']); ?></code></div>
                </div>
            <?php endif; ?>

            <?php if ($certificate['blockchain_tx_hash']): ?>
                <div class="detail-row">
                    <div class="detail-label">Blockchain TX:</div>
                    <div class="detail-value"><code><?php echo htmlspecialchars($certificate['blockchain_tx_hash']); ?></code></div>
                </div>
            <?php endif; ?>

            <?php if ($certificate['certificate_file']): ?>
                <div class="certificate-file-preview">
                    <h3>Certificate File</h3>
                    <?php
                    $file_ext = strtolower(pathinfo($certificate['certificate_file'], PATHINFO_EXTENSION));
                    $file_path = '../uploads/certificates/' . $certificate['certificate_file'];
                    ?>

                    <?php if (in_array($file_ext, ['jpg', 'jpeg', 'png', 'gif'])): ?>
                        <img src="<?php echo $file_path; ?>" alt="Certificate">
                    <?php else: ?>
                        <a href="<?php echo $file_path; ?>" target="_blank" class="btn btn-primary">
                            📄 View Certificate File (<?php echo strtoupper($file_ext); ?>)
                        </a>
                    <?php endif; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Action Buttons -->
        <div class="action-buttons">
            <h3>Admin Actions</h3>

            <div class="button-group">
                <?php if ($certificate['verification_status'] === 'pending'): ?>
                    <a href="../api/approve-certificate.php?id=<?php echo $certificate['id']; ?>"
                       class="btn-large btn-approve"
                       onclick="return confirm('✅ Approve this certificate?\n\nThis will mark it as VERIFIED.')">
                        ✓ Approve Certificate
                    </a>
                    <a href="../api/reject-certificate.php?id=<?php echo $certificate['id']; ?>"
                       class="btn-large btn-reject"
                       onclick="return confirm('❌ Reject this certificate?\n\nThis action cannot be undone.')">
                        ✕ Reject Certificate
                    </a>
                <?php elseif ($certificate['verification_status'] === 'verified'): ?>
                    <p style="color: #22543d; font-size: 1.1rem;">✅ This certificate is already verified</p>
                <?php elseif ($certificate['verification_status'] === 'rejected'): ?>
                    <p style="color: #742a2a; font-size: 1.1rem;">❌ This certificate has been rejected</p>
                <?php endif; ?>

                <?php if ($certificate['status'] === 'active'): ?>
                    <a href="#"
                       class="btn-large btn-revoke"
                       onclick="return confirm('⚠️ Revoke this certificate?\n\nThis will invalidate the certificate.')">
                        ⚠️ Revoke Certificate
                    </a>
                <?php endif; ?>

                <a href="manage-certificates.php" class="btn-large btn-back">
                    ← Back to List
                </a>
            </div>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script src="../assets/js/main.js"></script>
</body>
</html>