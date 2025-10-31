<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$user_id = $_GET['id'] ?? null;
$errors = [];
$success = '';

if (!$user_id) {
    $_SESSION['error'] = 'Invalid user ID';
    header('Location: manage-users.php');
    exit();
}

$pdo = getPDOConnection();

// Get user details
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$user_id]);
$user = $stmt->fetch();

if (!$user) {
    $_SESSION['error'] = 'User not found';
    header('Location: manage-users.php');
    exit();
}

// Handle form submission
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = sanitize_input($_POST['name'] ?? '');
    $email = sanitize_input($_POST['email'] ?? '');
    $role = sanitize_input($_POST['role'] ?? '');
    $status = sanitize_input($_POST['status'] ?? '');
    $new_password = $_POST['new_password'] ?? '';

    // Validation
    if (empty($name)) {
        $errors[] = 'Name is required';
    }

    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $errors[] = 'Valid email is required';
    }

    if (!in_array($role, ['admin', 'issuer', 'requester'])) {
        $errors[] = 'Invalid role';
    }

    if (!in_array($status, ['active', 'inactive'])) {
        $errors[] = 'Invalid status';
    }

    // Check if email is already taken by another user
    $stmt = $pdo->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
    $stmt->execute([$email, $user_id]);
    if ($stmt->fetch()) {
        $errors[] = 'Email is already taken by another user';
    }

    if (empty($errors)) {
        try {
            // Update user
            if (!empty($new_password)) {
                // Update with new password
                $password_hash = password_hash($new_password, PASSWORD_DEFAULT);
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, password = ?, role = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $password_hash, $role, $status, $user_id]);
            } else {
                // Update without changing password
                $stmt = $pdo->prepare("
                    UPDATE users 
                    SET name = ?, email = ?, role = ?, status = ?, updated_at = NOW()
                    WHERE id = ?
                ");
                $stmt->execute([$name, $email, $role, $status, $user_id]);
            }

            $_SESSION['success'] = 'User updated successfully';
            header('Location: manage-users.php');
            exit();

        } catch (Exception $e) {
            $errors[] = 'Error updating user: ' . $e->getMessage();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Edit User - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="../assets/css/style.css">
    <style>
        .edit-user-container {
            max-width: 800px;
            margin: 2rem auto;
        }

        .page-header {
            background: white;
            padding: 2rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
            margin-bottom: 2rem;
        }

        .page-header h1 {
            color: #2d3748;
            margin-bottom: 0.5rem;
        }

        .user-info-badge {
            display: inline-block;
            padding: 0.5rem 1rem;
            background: #e6fffa;
            border-radius: 6px;
            margin-top: 1rem;
        }

        .form-container {
            background: white;
            padding: 2.5rem;
            border-radius: 12px;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            margin-bottom: 0.5rem;
            color: #2d3748;
        }

        .form-group .label-hint {
            font-weight: normal;
            color: #718096;
            font-size: 0.9rem;
        }

        .form-group input,
        .form-group select {
            width: 100%;
            padding: 0.75rem;
            border: 2px solid #e2e8f0;
            border-radius: 8px;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .form-group input:focus,
        .form-group select:focus {
            outline: none;
            border-color: #4299e1;
            box-shadow: 0 0 0 3px rgba(66, 153, 225, 0.1);
        }

        .form-actions {
            display: flex;
            gap: 1rem;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 2px solid #e2e8f0;
        }

        .btn {
            padding: 0.75rem 2rem;
            border-radius: 8px;
            font-weight: 600;
            text-decoration: none;
            display: inline-block;
            cursor: pointer;
            border: none;
            font-size: 1rem;
            transition: all 0.3s;
        }

        .btn-primary {
            background: #4299e1;
            color: white;
        }

        .btn-primary:hover {
            background: #3182ce;
            transform: translateY(-2px);
            box-shadow: 0 4px 12px rgba(66, 153, 225, 0.4);
        }

        .btn-secondary {
            background: #cbd5e0;
            color: #2d3748;
        }

        .btn-secondary:hover {
            background: #a0aec0;
        }

        .btn-danger {
            background: #f56565;
            color: white;
        }

        .btn-danger:hover {
            background: #e53e3e;
        }

        .alert {
            padding: 1rem;
            border-radius: 8px;
            margin-bottom: 1.5rem;
        }

        .alert-danger {
            background: #fed7d7;
            color: #742a2a;
            border-left: 4px solid #f56565;
        }

        .alert-success {
            background: #c6f6d5;
            color: #22543d;
            border-left: 4px solid #48bb78;
        }

        .password-hint {
            font-size: 0.85rem;
            color: #718096;
            margin-top: 0.25rem;
        }

        .role-description {
            font-size: 0.9rem;
            color: #718096;
            margin-top: 0.5rem;
            padding: 0.75rem;
            background: #f7fafc;
            border-radius: 6px;
        }

        .danger-zone {
            margin-top: 3rem;
            padding: 2rem;
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 12px;
        }

        .danger-zone h3 {
            color: #c53030;
            margin-bottom: 1rem;
        }
    </style>
</head>
<body>
<?php include '../includes/header.php'; ?>

<main class="container">
    <div class="edit-user-container">
        <!-- Page Header -->
        <div class="page-header">
            <h1>✏️ Edit User</h1>
            <div class="user-info-badge">
                Editing: <strong><?php echo htmlspecialchars($user['name']); ?></strong>
                (<?php echo htmlspecialchars($user['email']); ?>)
            </div>
        </div>

        <!-- Error Messages -->
        <?php if (!empty($errors)): ?>
            <div class="alert alert-danger">
                <strong>Error!</strong>
                <ul style="margin: 0.5rem 0 0 1.5rem;">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>

        <!-- Edit Form -->
        <div class="form-container">
            <form method="POST" action="">
                <div class="form-group">
                    <label for="name">Full Name</label>
                    <input type="text"
                           id="name"
                           name="name"
                           value="<?php echo htmlspecialchars($user['name']); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="email">Email Address</label>
                    <input type="email"
                           id="email"
                           name="email"
                           value="<?php echo htmlspecialchars($user['email']); ?>"
                           required>
                </div>

                <div class="form-group">
                    <label for="role">
                        User Role
                        <span class="label-hint">(defines access permissions)</span>
                    </label>
                    <select id="role" name="role" required>
                        <option value="requester" <?php echo $user['role'] === 'requester' ? 'selected' : ''; ?>>
                            Requester
                        </option>
                        <option value="issuer" <?php echo $user['role'] === 'issuer' ? 'selected' : ''; ?>>
                            Issuer
                        </option>
                        <option value="admin" <?php echo $user['role'] === 'admin' ? 'selected' : ''; ?>>
                            Administrator
                        </option>
                    </select>
                    <div class="role-description">
                        <strong>Requester:</strong> Can request certificate verification<br>
                        <strong>Issuer:</strong> Can issue and manage certificates<br>
                        <strong>Admin:</strong> Full access to all features
                    </div>
                </div>

                <div class="form-group">
                    <label for="status">Account Status</label>
                    <select id="status" name="status" required>
                        <option value="active" <?php echo $user['status'] === 'active' ? 'selected' : ''; ?>>
                            ✅ Active
                        </option>
                        <option value="inactive" <?php echo $user['status'] === 'inactive' ? 'selected' : ''; ?>>
                            ⛔ Inactive
                        </option>
                    </select>
                </div>

                <div class="form-group">
                    <label for="new_password">
                        New Password
                        <span class="label-hint">(leave blank to keep current password)</span>
                    </label>
                    <input type="password"
                           id="new_password"
                           name="new_password"
                           placeholder="Enter new password or leave blank">
                    <div class="password-hint">
                        💡 Password must be at least <?php echo PASSWORD_MIN_LENGTH; ?> characters
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        💾 Save Changes
                    </button>
                    <a href="manage-users.php" class="btn btn-secondary">
                        ← Cancel
                    </a>
                </div>
            </form>
        </div>

        <!-- Danger Zone -->
        <div class="danger-zone">
            <h3>⚠️ Danger Zone</h3>
            <p>Delete this user account permanently. This action cannot be undone.</p>
            <a href="delete-user.php?id=<?php echo $user['id']; ?>"
               class="btn btn-danger"
               onclick="return confirm('⚠️ DELETE USER?\n\nUser: <?php echo htmlspecialchars($user['name']); ?>\nEmail: <?php echo htmlspecialchars($user['email']); ?>\n\nThis will permanently delete:\n- User account\n- All associated data\n\nThis action CANNOT be undone!\n\nAre you absolutely sure?')">
                🗑️ Delete User
            </a>
        </div>
    </div>
</main>

<?php include '../includes/footer.php'; ?>

<script src="../assets/js/main.js"></script>
<script>
    // Show password strength indicator (optional)
    document.getElementById('new_password').addEventListener('input', function() {
        const password = this.value;
        if (password.length > 0 && password.length < <?php echo PASSWORD_MIN_LENGTH; ?>) {
            this.style.borderColor = '#f56565';
        } else if (password.length >= <?php echo PASSWORD_MIN_LENGTH; ?>) {
            this.style.borderColor = '#48bb78';
        } else {
            this.style.borderColor = '#e2e8f0';
        }
    });
</script>
</body>
</html>