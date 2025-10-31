<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$pdo = getPDOConnection();

// Get all users
$stmt = $pdo->query("SELECT * FROM users ORDER BY created_at DESC");
$users = $stmt->fetchAll();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Manage Users - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <?php include '../includes/header.php'; ?>
    
    <main class="container">
        <div class="page-header">
            <h1>Manage Users</h1>
            <a href="dashboard.php" class="btn btn-secondary">Back to Dashboard</a>
        </div>
        
        <div class="users-list">
            <table class="data-table">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Name</th>
                        <th>Email</th>
                        <th>Role</th>
                        <th>Wallet Address</th>
                        <th>Status</th>
                        <th>Registered</th>
                        <th>Actions</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($users as $user): ?>
                    <tr>
                        <td><?php echo $user['id']; ?></td>
                        <td><?php echo htmlspecialchars($user['name']); ?></td>
                        <td><?php echo htmlspecialchars($user['email']); ?></td>
                        <td><?php echo get_user_role_name($user['role']); ?></td>
                        <td><?php echo htmlspecialchars($user['wallet_address'] ?? 'Not connected'); ?></td>
                        <td>
                            <span class="badge badge-<?php echo $user['status']; ?>">
                                <?php echo ucfirst($user['status']); ?>
                            </span>
                        </td>
                        <td><?php echo format_date($user['created_at']); ?></td>
                        <td class="actions">
                            <a href="edit-user.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-info">Edit</a>
                            <?php if ($user['id'] != $_SESSION['user_id']): ?>
                            <a href="../api/toggle-user-status.php?id=<?php echo $user['id']; ?>" 
                               class="btn btn-sm btn-warning"
                               onclick="return confirm('Toggle user status?')">
                               <?php echo $user['status'] === 'active' ? 'Deactivate' : 'Activate'; ?>
                            </a>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </main>
    
    <?php include '../includes/footer.php'; ?>
    
    <script src="../assets/js/main.js"></script>
</body>
</html>