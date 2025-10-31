<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$stats = get_platform_stats();
$pdo = getPDOConnection();

// Get recent users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC LIMIT 5");
$recent_users = $stmt->fetchAll();

// Get recent certificates
$stmt = $pdo->query("SELECT c.*, u.name as issuer_name FROM certificates c 
                     JOIN users u ON c.issuer_id = u.id 
                     ORDER BY c.created_at DESC LIMIT 5");
$recent_certificates = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Dashboard - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="admin-dashboard">
            <h1>Admin Dashboard</h1>
            
            <!-- Statistics Cards -->
            <div class="stats-grid">
                <div class="stat-card">
                    <div class="stat-icon">👥</div>
                    <div class="stat-info">
                        <h3>Total Users</h3>
                        <div class="stat-number"><?php echo $stats['total_users']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">📄</div>
                    <div class="stat-info">
                        <h3>Total Certificates</h3>
                        <div class="stat-number"><?php echo $stats['total_certificates']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">✓</div>
                    <div class="stat-info">
                        <h3>Verified</h3>
                        <div class="stat-number"><?php echo $stats['verified_certificates']; ?></div>
                    </div>
                </div>
                
                <div class="stat-card">
                    <div class="stat-icon">⏳</div>
                    <div class="stat-info">
                        <h3>Pending</h3>
                        <div class="stat-number"><?php echo $stats['pending_certificates']; ?></div>
                    </div>
                </div>
            </div>
            
            <!-- Quick Actions -->
            <section class="quick-actions">
                <h2>Quick Actions</h2>
                <div class="action-grid">
                    <a href="manage-users.php" class="action-card">
                        <div class="action-icon">👥</div>
                        <h3>Manage Users</h3>
                        <p>View and manage all users</p>
                    </a>
                    
                    <a href="manage-certificates.php" class="action-card">
                        <div class="action-icon">📄</div>
                        <h3>Manage Certificates</h3>
                        <p>Review and approve certificates</p>
                    </a>
                    
                    <a href="platform-settings.php" class="action-card">
                        <div class="action-icon">⚙️</div>
                        <h3>Platform Settings</h3>
                        <p>Configure platform settings</p>
                    </a>
                    
                    <a href="reports.php" class="action-card">
                        <div class="action-icon">📊</div>
                        <h3>Reports</h3>
                        <p>View platform analytics</p>
                    </a>
                </div>
            </section>
            
            <!-- Recent Users -->
            <section class="recent-section">
                <div class="section-header">
                    <h2>Recent Users</h2>
                    <a href="manage-users.php" class="btn btn-secondary">View All</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Name</th>
                            <th>Email</th>
                            <th>Role</th>
                            <th>Status</th>
                            <th>Registered</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_users as $user): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                            <td><?php echo get_user_role_name($user['role']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $user['status']; ?>">
                                    <?php echo ucfirst($user['status']); ?>
                                </span>
                            </td>
                            <td><?php echo format_date($user['created_at']); ?></td>
                            <td class="actions">
                                <a href="edit-user.php?id=<?php echo $user['id']; ?>" 
                                   class="btn btn-sm btn-info">Edit</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
            
            <!-- Recent Certificates -->
            <section class="recent-section">
                <div class="section-header">
                    <h2>Recent Certificates</h2>
                    <a href="manage-certificates.php" class="btn btn-secondary">View All</a>
                </div>
                
                <table class="data-table">
                    <thead>
                        <tr>
                            <th>Certificate ID</th>
                            <th>Recipient</th>
                            <th>Title</th>
                            <th>Issuer</th>
                            <th>Status</th>
                            <th>Actions</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_certificates as $cert): ?>
                        <tr>
                            <td><?php echo htmlspecialchars($cert['certificate_id']); ?></td>
                            <td><?php echo htmlspecialchars($cert['recipient_name']); ?></td>
                            <td><?php echo htmlspecialchars($cert['title']); ?></td>
                            <td><?php echo htmlspecialchars($cert['issuer_name']); ?></td>
                            <td>
                                <span class="badge badge-<?php echo $cert['verification_status']; ?>">
                                    <?php echo ucfirst($cert['verification_status']); ?>
                                </span>
                            </td>
                            <td class="actions">
                                <a href="review-certificate.php?id=<?php echo $cert['id']; ?>" 
                                   class="btn btn-sm btn-info">Review</a>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </section>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>