<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

start_secure_session();
require_login();

$user_id = $_SESSION['user_id'];
$user = get_user_by_id($user_id);
$stats = get_platform_stats();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="dashboard">
            <h1>Welcome, <?php echo htmlspecialchars($user['name']); ?></h1>
            <p class="user-role">Account Type: <?php echo get_user_role_name($user['role']); ?></p>
            
            <!-- Quick Stats -->
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
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="verify.php" class="action-card">
                        <div class="action-icon">🔍</div>
                        <h3>Verify Certificate</h3>
                        <p>Check certificate authenticity</p>
                    </a>
                    
                    <?php if (in_array($user['role'], ['issuer', 'admin'])): ?>
                    <a href="issuer/issue-certificate.php" class="action-card">
                        <div class="action-icon">📝</div>
                        <h3>Issue Certificate</h3>
                        <p>Create new certificate</p>
                    </a>
                    
                    <a href="issuer/my-certificates.php" class="action-card">
                        <div class="action-icon">📄</div>
                        <h3>My Certificates</h3>
                        <p>View issued certificates</p>
                    </a>
                    <?php endif; ?>
                    
                    <a href="profile.php" class="action-card">
                        <div class="action-icon">👤</div>
                        <h3>My Profile</h3>
                        <p>Update account settings</p>
                    </a>
                    
                    <?php if ($user['role'] === 'admin'): ?>
                    <a href="admin/dashboard.php" class="action-card">
                        <div class="action-icon">⚙️</div>
                        <h3>Admin Panel</h3>
                        <p>Manage platform</p>
                    </a>
                    <?php endif; ?>
                </div>
            </section>
            
            <!-- Recent Activity -->
            <section class="recent-activity">
                <h2>Recent Activity</h2>
                <div class="activity-list">
                    <?php
                    $pdo = getPDOConnection();
                    $stmt = $pdo->prepare("SELECT * FROM certificates ORDER BY created_at DESC LIMIT 5");
                    $stmt->execute();
                    $recent_certs = $stmt->fetchAll();
                    
                    if (empty($recent_certs)):
                    ?>
                        <p class="no-data">No recent activity</p>
                    <?php else: ?>
                        <table class="data-table">
                            <thead>
                                <tr>
                                    <th>Certificate ID</th>
                                    <th>Recipient</th>
                                    <th>Title</th>
                                    <th>Status</th>
                                    <th>Date</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recent_certs as $cert): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                                    <td><?php echo htmlspecialchars($cert['title']); ?></td>
                                    <td>
                                        <span class="badge badge-<?php echo $cert['verification_status']; ?>">
                                            <?php echo ucfirst($cert['verification_status']); ?>
                                        </span>
                                    </td>
                                    <td><?php echo format_date($cert['created_at']); ?></td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    <?php endif; ?>
                </div>
            </section>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>