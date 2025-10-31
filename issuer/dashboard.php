<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['issuer', 'admin']);

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);

// Get issuer's certificates
$my_certificates = get_certificates_by_issuer($user_id);
$total_issued = count($my_certificates);
$verified_count = count(array_filter($my_certificates, function($cert) {
    return $cert['verification_status'] === 'verified';
}));
$pending_count = count(array_filter($my_certificates, function($cert) {
    return $cert['verification_status'] === 'pending';
}));
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Issuer Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        /* Additional inline styles to ensure proper display */
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
            <h1>Issuer Dashboard</h1>
            <p>Welcome, <?php echo htmlspecialchars($user['name']); ?>!</p>
            
            <!-- Statistics -->
            <div class="stats-grid">
                <div class="stat-card">
                    <h3>Total Issued</h3>
                    <div class="stat-number"><?php echo $total_issued; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Verified</h3>
                    <div class="stat-number"><?php echo $verified_count; ?></div>
                </div>
                
                <div class="stat-card">
                    <h3>Pending</h3>
                    <div class="stat-number"><?php echo $pending_count; ?></div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="issue-certificate.php" class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Issue New Certificate</h3>
                        <p>Create and issue a new certificate</p>
                    </a>
                    
                    <a href="my-certificates.php" class="action-card">
                        <div class="action-icon">📄</div>
                        <h3>My Certificates</h3>
                        <p>View all issued certificates</p>
                    </a>
                    
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
                </div>
            </section>
            
            <!-- Recent Certificates -->
            <section class="recent-certificates">
                <h2>Recent Certificates</h2>
                <?php if (empty($my_certificates)): ?>
                    <div style="text-align: center; padding: 3rem; background: white; border-radius: 8px;">
                        <div style="font-size: 3rem; margin-bottom: 1rem;">📄</div>
                        <h3>No Certificates Yet</h3>
                        <p>You haven't issued any certificates yet.</p>
                        <a href="issue-certificate.php" class="btn btn-primary" style="margin-top: 1rem;">Issue Your First Certificate</a>
                    </div>
                <?php else: ?>
                    <table style="width: 100%; background: white; border-radius: 8px; overflow: hidden; box-shadow: 0 2px 4px rgba(0,0,0,0.1);">
                        <thead style="background: #2563eb; color: white;">
                            <tr>
                                <th style="padding: 1rem; text-align: left;">Certificate ID</th>
                                <th style="padding: 1rem; text-align: left;">Recipient</th>
                                <th style="padding: 1rem; text-align: left;">Title</th>
                                <th style="padding: 1rem; text-align: left;">Date</th>
                                <th style="padding: 1rem; text-align: left;">Status</th>
                                <th style="padding: 1rem; text-align: left;">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php 
                            $recent_certs = array_slice($my_certificates, 0, 5);
                            foreach ($recent_certs as $cert): 
                            ?>
                            <tr style="border-bottom: 1px solid #e2e8f0;">
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                <td style="padding: 1rem;"><?php echo htmlspecialchars($cert['title']); ?></td>
                                <td style="padding: 1rem;"><?php echo format_date($cert['issue_date']); ?></td>
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
                                <td style="padding: 1rem;">
                                    <a href="../verify.php?id=<?php echo $cert['certificate_id']; ?>" 
                                       class="btn" style="padding: 0.4rem 0.8rem; font-size: 0.875rem; background: #3b82f6; color: white;">View</a>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                    
                    <?php if (count($my_certificates) > 5): ?>
                    <div style="text-align: center; margin-top: 1.5rem;">
                        <a href="my-certificates.php" class="btn btn-primary">View All Certificates</a>
                    </div>
                    <?php endif; ?>
                <?php endif; ?>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>