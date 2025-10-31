<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['requester', 'admin']);

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);
$stats = get_platform_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Requester Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional inline styles */
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            line-height: 1.6;
            color: #0f172a;
            background-color: #f8fafc;
            margin: 0;
            padding: 0;
        }
        
        .container {
            max-width: 1200px;
            margin: 0 auto;
            padding: 0 20px;
        }
        
        .dashboard {
            padding: 2rem 0;
        }
        
        .welcome-section {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 2rem;
            margin: 2rem 0;
        }
        
        .stat-card {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-align: center;
        }
        
        .stat-number {
            font-size: 3rem;
            font-weight: bold;
            color: #2563eb;
        }
        
        .action-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 1.5rem;
            margin: 2rem 0;
        }
        
        .action-card {
            background: white;
            padding: 1.5rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            text-decoration: none;
            color: #0f172a;
            transition: transform 0.3s;
        }
        
        .action-card:hover {
            transform: translateY(-4px);
            box-shadow: 0 4px 8px rgba(0,0,0,0.15);
        }
        
        .action-icon {
            font-size: 2rem;
            margin-bottom: 0.5rem;
        }
        
        .quick-verify {
            background: white;
            padding: 2rem;
            border-radius: 8px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
            margin: 2rem 0;
        }
        
        .verify-form {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }
        
        .verify-form input {
            flex: 1;
            padding: 0.75rem;
            border: 1px solid #e2e8f0;
            border-radius: 6px;
            font-size: 1rem;
        }
        
        .btn {
            display: inline-block;
            padding: 0.75rem 1.5rem;
            border: none;
            border-radius: 6px;
            font-size: 1rem;
            font-weight: 500;
            cursor: pointer;
            text-decoration: none;
            transition: all 0.3s;
        }
        
        .btn-primary {
            background: #2563eb;
            color: white;
        }
        
        .btn-primary:hover {
            background: #1d4ed8;
        }
    </style>
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <div class="welcome-section">
                <h1>Verification Requester Dashboard</h1>
                <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
                <p>As a verification requester, you can verify certificates and request documentation from official authorities.</p>
            </div>
            
            <!-- Platform Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Certificates</h3>
                    <div class="stat-number"><?php echo $stats['total_certificates']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Verified</h3>
                    <div class="stat-number"><?php echo $stats['verified_certificates']; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Pending</h3>
                    <div class="stat-number"><?php echo $stats['pending_certificates']; ?></div>
                </div>
            </div>
            
            <!-- Quick Certificate Verification -->
            <section class="quick-verify">
                <h2>🔍 Quick Certificate Verification</h2>
                <p>Enter a certificate ID to verify its authenticity</p>
                
                <form method="GET" action="../verify.php" class="verify-form">
                    <input type="text" 
                           name="id" 
                           placeholder="CERT-2025-XXXXXXXXXXXX" 
                           required>
                    <button type="submit" class="btn btn-primary">Verify Certificate</button>
                </form>
            </section>
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="../verify.php" class="action-card">
                        <div class="action-icon">🔍</div>
                        <h3>Verify Certificate</h3>
                        <p>Check certificate authenticity</p>
                    </a>
                    
                    <a href="../dashboard.php" class="action-card">
                        <div class="action-icon">🏠</div>
                        <h3>Main Dashboard</h3>
                        <p>Go to main dashboard</p>
                    </a>
                    
                    <a href="../profile.php" class="action-card">
                        <div class="action-icon">👤</div>
                        <h3>My Profile</h3>
                        <p>Update account settings</p>
                    </a>
                    
                    <a href="request-verification.php" class="action-card">
                        <div class="action-icon">📋</div>
                        <h3>Request Verification</h3>
                        <p>Submit verification request</p>
                    </a>
                </div>
            </section>
            
            <!-- Information Section -->
            <section style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 2rem 0;">
                <h2>What is a Verification Requester?</h2>
                <p>As a verification requester, you have the ability to:</p>
                <ul style="line-height: 2; margin-left: 2rem;">
                    <li>✅ Verify the authenticity of any certificate in the system</li>
                    <li>✅ Request documentation from official authorities</li>
                    <li>✅ Access verification history and reports</li>
                    <li>✅ Submit bulk verification requests</li>
                    <li>✅ Receive verification confirmations via email</li>
                </ul>
                
                <h3 style="margin-top: 2rem;">How to Verify a Certificate</h3>
                <ol style="line-height: 2; margin-left: 2rem;">
                    <li>Enter the certificate ID in the verification form above</li>
                    <li>Click "Verify Certificate"</li>
                    <li>Review the verification results and certificate details</li>
                    <li>Download or print the verification report if needed</li>
                </ol>
            </section>
            
            <!-- Recent Platform Activity -->
            <section style="background: white; padding: 2rem; border-radius: 8px; box-shadow: 0 2px 4px rgba(0,0,0,0.1); margin: 2rem 0;">
                <h2>Recent Platform Activity</h2>
                <?php
                $pdo = getPDOConnection();
                $stmt = $pdo->query("SELECT c.*, u.name as issuer_name FROM certificates c 
                                    JOIN users u ON c.issuer_id = u.id 
                                    ORDER BY c.created_at DESC LIMIT 5");
                $recent_certificates = $stmt->fetchAll();
                
                if (empty($recent_certificates)):
                ?>
                    <p>No recent activity</p>
                <?php else: ?>
                    <table style="width: 100%; border-collapse: collapse; margin-top: 1rem;">
                        <thead style="background: #f8fafc;">
                            <tr>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Certificate ID</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Recipient</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Issuer</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Date</th>
                                <th style="padding: 1rem; text-align: left; border-bottom: 2px solid #e2e8f0;">Status</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach ($recent_certificates as $cert): ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem;">
                                    <a href="../verify.php?id=<?php echo htmlspecialchars($cert['certificate_id']); ?>" 
                                       style="color: #2563eb; text-decoration: none;">
                                        <?php echo htmlspecialchars($cert['certificate_id']); ?>
                                    </a>
                                </td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($cert['issuer_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo format_date($cert['created_at']); ?></td>
                                <td style="padding: 1rem;">
                                    <span style="display: inline-block; padding: 0.25rem 0.75rem; border-radius: 12px; font-size: 0.875rem;
                                        <?php if($cert['verification_status'] === 'verified'): ?>
                                            background: #dcfce7; color: #166534;
                                        <?php elseif($cert['verification_status'] === 'pending'): ?>
                                            background: #fef3c7; color: #92400e;
                                        <?php else: ?>
                                            background: #fee2e2; color: #991b1b;
                                        <?php endif; ?>">
                                        <?php echo ucfirst($cert['verification_status']); ?>
                                    </span>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>