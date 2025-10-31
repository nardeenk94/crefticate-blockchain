<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

start_secure_session();

$certificate = null;
$error = '';
$certificate_id = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' || isset($_GET['id'])) {
    $certificate_id = sanitize_input($_POST['certificate_id'] ?? $_GET['id'] ?? '');

    if (!empty($certificate_id)) {
        $certificate = get_certificate_by_id($certificate_id);

        if (!$certificate) {
            $error = 'Certificate not found or invalid';
        }
    } else {
        $error = 'Please enter a certificate ID';
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Verify Certificate - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
    <style>
        /* Enhanced Status Alert Styles */
        .status-alert {
            padding: 2rem;
            border-radius: 12px;
            margin: 2rem 0;
            text-align: center;
            box-shadow: 0 4px 6px rgba(0,0,0,0.1);
        }

        .status-alert.success {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: 3px solid #5a67d8;
        }

        .status-alert.warning {
            background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
            color: white;
            border: 3px solid #e53e3e;
        }

        .status-alert.pending {
            background: linear-gradient(135deg, #4facfe 0%, #00f2fe 100%);
            color: white;
            border: 3px solid #3182ce;
        }

        .status-alert.rejected {
            background: linear-gradient(135deg, #fa709a 0%, #fee140 100%);
            color: #742a2a;
            border: 3px solid #e53e3e;
        }

        .status-icon {
            font-size: 5rem;
            margin-bottom: 1rem;
            display: block;
            animation: bounceIn 0.6s ease-out;
        }

        @keyframes bounceIn {
            0% { transform: scale(0); opacity: 0; }
            50% { transform: scale(1.1); }
            100% { transform: scale(1); opacity: 1; }
        }

        .status-alert h2 {
            margin: 1rem 0;
            font-size: 2.5rem;
            font-weight: bold;
        }

        .status-alert p {
            margin: 0.5rem 0;
            font-size: 1.2rem;
        }

        .admin-actions {
            margin-top: 1.5rem;
            padding-top: 1.5rem;
            border-top: 2px solid rgba(255,255,255,0.3);
        }

        .admin-actions p {
            font-weight: 600;
            margin-bottom: 1rem;
            font-size: 1.1rem;
        }

        .action-buttons {
            display: flex;
            gap: 1rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .btn-approve {
            background: #48bb78;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-approve:hover {
            background: #38a169;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }

        .btn-reject {
            background: #f56565;
            color: white;
            padding: 0.75rem 2rem;
            border-radius: 8px;
            text-decoration: none;
            font-weight: bold;
            transition: all 0.3s;
        }

        .btn-reject:hover {
            background: #e53e3e;
            transform: translateY(-2px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.2);
        }
    </style>
</head>
<body>
<?php include 'includes/header.php'; ?>

<main class="container">
    <div class="verify-container">
        <h1>Verify Certificate</h1>
        <p class="lead">Enter certificate ID to verify authenticity</p>

        <!-- Verification Form -->
        <form method="POST" action="" class="verify-form">
            <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">

            <div class="form-group">
                <label for="certificate_id">Certificate ID</label>
                <input type="text" id="certificate_id" name="certificate_id"
                       placeholder="CERT-2025-XXXXXXXXXXXX" required
                       value="<?php echo htmlspecialchars($certificate_id); ?>">
            </div>

            <button type="submit" class="btn btn-primary">Verify Certificate</button>
        </form>

        <?php if ($error): ?>
            <div class="alert alert-danger">
                <strong>Verification Failed!</strong><br>
                <?php echo $error; ?>
            </div>
        <?php endif; ?>

        <?php if ($certificate): ?>
            <div class="certificate-result">

                <!-- STATUS ALERT - هذا الجزء المهم! -->
                <?php
                // حالة الشهادة
                $status = $certificate['verification_status']; // pending, verified, rejected
                $cert_status = $certificate['status']; // active, revoked
                ?>

                <?php if ($status === 'verified' && $cert_status === 'active'): ?>
                    <!-- ✅ VERIFIED -->
                    <div class="status-alert success">
                        <span class="status-icon">✅</span>
                        <h2>Certificate Verified</h2>
                        <p>This certificate is authentic and valid</p>
                        <p style="margin-top: 1rem; font-size: 0.9rem; opacity: 0.9;">
                            Verified on blockchain • Tamper-proof
                        </p>
                    </div>

                <?php elseif ($status === 'rejected'): ?>
                    <!-- ❌ REJECTED -->
                    <div class="status-alert rejected">
                        <span class="status-icon">❌</span>
                        <h2>Certificate Rejected</h2>
                        <p>This certificate has been rejected and is not valid</p>
                    </div>

                <?php elseif ($cert_status === 'revoked'): ?>
                    <!-- ⚠️ REVOKED -->
                    <div class="status-alert warning">
                        <span class="status-icon">⚠️</span>
                        <h2>Certificate Revoked</h2>
                        <p>This certificate has been revoked and is no longer valid</p>
                    </div>

                <?php else: ?>
                    <!-- ⏳ PENDING -->
                    <div class="status-alert pending">
                        <span class="status-icon">⏳</span>
                        <h2>Verification Pending</h2>
                        <p>This certificate is awaiting admin verification</p>

                        <?php if (is_logged_in() && $_SESSION['user_role'] === 'admin'): ?>
                            <div class="admin-actions">
                                <p>👨‍💼 Admin Actions:</p>
                                <div class="action-buttons">
                                    <a href="api/approve-certificate.php?cert_id=<?php echo urlencode($certificate['certificate_id']); ?>"
                                       class="btn-approve"
                                       onclick="return confirm('✅ Approve this certificate?\n\nThis will mark it as VERIFIED.')">
                                        ✓ Approve Certificate
                                    </a>
                                    <a href="api/reject-certificate.php?cert_id=<?php echo urlencode($certificate['certificate_id']); ?>"
                                       class="btn-reject"
                                       onclick="return confirm('❌ Reject this certificate?\n\nThis action cannot be undone.')">
                                        ✕ Reject Certificate
                                    </a>
                                </div>
                            </div>
                        <?php endif; ?>
                    </div>
                <?php endif; ?>

                <!-- Certificate Details -->
                <div class="certificate-details">
                    <h3>Certificate Details</h3>

                    <div class="detail-grid">
                        <div class="detail-item">
                            <label>Certificate ID:</label>
                            <span><?php echo htmlspecialchars($certificate['certificate_id']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Status:</label>
                            <span>
                                    <?php
                                    if ($status === 'verified' && $cert_status === 'active') {
                                        echo '<span style="color: #48bb78; font-weight: bold;">✅ Verified</span>';
                                    } elseif ($status === 'rejected') {
                                        echo '<span style="color: #f56565; font-weight: bold;">❌ Rejected</span>';
                                    } elseif ($cert_status === 'revoked') {
                                        echo '<span style="color: #ed8936; font-weight: bold;">⚠️ Revoked</span>';
                                    } else {
                                        echo '<span style="color: #4299e1; font-weight: bold;">⏳ Pending</span>';
                                    }
                                    ?>
                                </span>
                        </div>

                        <div class="detail-item">
                            <label>Recipient Name:</label>
                            <span><?php echo htmlspecialchars($certificate['recipient_name']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Recipient ID:</label>
                            <span><?php echo htmlspecialchars($certificate['recipient_id']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Certificate Title:</label>
                            <span><?php echo htmlspecialchars($certificate['title']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Field:</label>
                            <span><?php echo htmlspecialchars($certificate['field']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Issue Date:</label>
                            <span><?php echo format_date($certificate['issue_date']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Issued By:</label>
                            <span><?php echo htmlspecialchars($certificate['issuer_name']); ?></span>
                        </div>

                        <div class="detail-item">
                            <label>Issuer Email:</label>
                            <span><?php echo htmlspecialchars($certificate['issuer_email']); ?></span>
                        </div>

                        <?php if ($certificate['description']): ?>
                            <div class="detail-item full-width">
                                <label>Description:</label>
                                <span><?php echo nl2br(htmlspecialchars($certificate['description'])); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($certificate['ipfs_hash']): ?>
                            <div class="detail-item">
                                <label>IPFS Hash:</label>
                                <span class="hash"><?php echo htmlspecialchars($certificate['ipfs_hash']); ?></span>
                            </div>
                        <?php endif; ?>

                        <?php if ($certificate['blockchain_tx_hash']): ?>
                            <div class="detail-item">
                                <label>Blockchain TX:</label>
                                <span class="hash">
                                    <a href="https://sepolia.etherscan.io/tx/<?php echo htmlspecialchars($certificate['blockchain_tx_hash']); ?>"
                                       target="_blank"
                                       style="color: #4299e1;">
                                        <?php echo htmlspecialchars($certificate['blockchain_tx_hash']); ?>
                                    </a>
                                </span>
                            </div>
                        <?php endif; ?>
                    </div>

                    <?php if ($certificate['certificate_file']): ?>
                        <div class="certificate-file">
                            <a href="uploads/certificates/<?php echo htmlspecialchars($certificate['certificate_file']); ?>"
                               target="_blank" class="btn btn-secondary">
                                📄 View Certificate File
                            </a>
                        </div>
                    <?php endif; ?>

                    <!-- Share & Print Options -->
                    <div class="certificate-actions">
                        <button onclick="window.print()" class="btn btn-outline">🖨️ Print</button>
                        <button onclick="shareVerification()" class="btn btn-outline">🔗 Share</button>
                    </div>
                </div>
            </div>
        <?php endif; ?>

        <!-- How to Verify Section -->
        <section class="how-to-verify">
            <h2>How to Verify a Certificate</h2>
            <ol>
                <li>Locate the unique Certificate ID on your certificate document</li>
                <li>Enter the Certificate ID in the verification form above</li>
                <li>Click "Verify Certificate" to check authenticity</li>
                <li>Review the verification results and certificate details</li>
            </ol>

            <div style="margin-top: 2rem; padding: 1.5rem; background: #f7fafc; border-radius: 8px;">
                <h3>Certificate Status Meanings:</h3>
                <ul style="list-style: none; padding: 0;">
                    <li style="margin: 0.5rem 0;">✅ <strong>Verified</strong> - Certificate is authentic and valid</li>
                    <li style="margin: 0.5rem 0;">⏳ <strong>Pending</strong> - Certificate awaiting admin verification</li>
                    <li style="margin: 0.5rem 0;">❌ <strong>Rejected</strong> - Certificate verification was rejected</li>
                    <li style="margin: 0.5rem 0;">⚠️ <strong>Revoked</strong> - Certificate was revoked by issuer</li>
                </ul>
            </div>
        </section>
    </div>
</main>

<?php include 'includes/footer.php'; ?>

<script src="assets/js/main.js"></script>
<script>
    function shareVerification() {
        const url = window.location.origin + window.location.pathname + '?id=<?php echo $certificate_id; ?>';

        if (navigator.share) {
            navigator.share({
                title: 'Certificate Verification',
                text: 'Verify this certificate on our platform',
                url: url
            }).catch(err => console.log('Error sharing:', err));
        } else {
            // Fallback: copy to clipboard
            if (navigator.clipboard) {
                navigator.clipboard.writeText(url).then(() => {
                    alert('✅ Verification link copied to clipboard!\n\n' + url);
                }).catch(err => {
                    prompt('Copy this link:', url);
                });
            } else {
                prompt('Copy this link:', url);
            }
        }
    }
</script>
</body>
</html>