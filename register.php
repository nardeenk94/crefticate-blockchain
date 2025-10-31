<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

start_secure_session();

// Redirect if already logged in
if (is_logged_in()) {
    redirect('dashboard.php');
}

$errors = [];
$success = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Verify CSRF token
    if (!verify_csrf_token($_POST['csrf_token'] ?? '')) {
        $errors[] = 'Invalid request';
    } else {
        $name = sanitize_input($_POST['name'] ?? '');
        $email = sanitize_input($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $confirm_password = $_POST['confirm_password'] ?? '';
        $role = sanitize_input($_POST['role'] ?? 'individual');
        
        // Validation
        if (empty($name)) {
            $errors[] = 'Name is required';
        }
        
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $errors[] = 'Valid email is required';
        }
        
        if (strlen($password) < PASSWORD_MIN_LENGTH) {
            $errors[] = 'Password must be at least ' . PASSWORD_MIN_LENGTH . ' characters';
        }
        
        if ($password !== $confirm_password) {
            $errors[] = 'Passwords do not match';
        }
        
        // Check if email already exists
        if (get_user_by_email($email)) {
            $errors[] = 'Email already registered';
        }
        
        // Register user if no errors
        if (empty($errors)) {
            if (create_user($name, $email, $password, $role)) {
                $success = 'Registration successful! You can now login.';
                
                // Send welcome email
                $message = "<h2>Welcome to " . SITE_NAME . "</h2>";
                $message .= "<p>Dear $name,</p>";
                $message .= "<p>Thank you for registering on our platform.</p>";
                $message .= "<p>Your account has been created successfully.</p>";
                
                send_email_notification($email, 'Welcome to ' . SITE_NAME, $message);
            } else {
                $errors[] = 'Registration failed. Please try again.';
            }
        }
    }
}

$csrf_token = generate_csrf_token();
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Register - <?php echo SITE_NAME; ?></title>
    <link rel="stylesheet" href="assets/css/style.css">
</head>
<body>
    <?php include 'includes/header.php'; ?>
    
    <main class="container">
        <div class="form-container">
            <h1>Create Account</h1>
            
            <?php if (!empty($errors)): ?>
                <div class="alert alert-danger">
                    <ul>
                        <?php foreach ($errors as $error): ?>
                            <li><?php echo $error; ?></li>
                        <?php endforeach; ?>
                    </ul>
                </div>
            <?php endif; ?>
            
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <?php echo $success; ?>
                    <a href="login.php">Click here to login</a>
                </div>
            <?php endif; ?>
            
            <form method="POST" action="" class="registration-form">
                <input type="hidden" name="csrf_token" value="<?php echo $csrf_token; ?>">
                
                <div class="form-group">
                    <label for="name">Full Name *</label>
                    <input type="text" id="name" name="name" required 
                           value="<?php echo htmlspecialchars($_POST['name'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="email">Email Address *</label>
                    <input type="email" id="email" name="email" required 
                           value="<?php echo htmlspecialchars($_POST['email'] ?? ''); ?>">
                </div>
                
                <div class="form-group">
                    <label for="role">Account Type *</label>
                    <select id="role" name="role" required>
                        <option value="individual" <?php echo (($_POST['role'] ?? '') === 'individual') ? 'selected' : ''; ?>>
                            Individual
                        </option>
                        <option value="issuer" <?php echo (($_POST['role'] ?? '') === 'issuer') ? 'selected' : ''; ?>>
                            Certificate Issuer
                        </option>
                        <option value="requester" <?php echo (($_POST['role'] ?? '') === 'requester') ? 'selected' : ''; ?>>
                            Verification Requester
                        </option>
                    </select>
                    <small>Choose the type that best describes your role</small>
                </div>
                
                <div class="form-group">
                    <label for="password">Password *</label>
                    <input type="password" id="password" name="password" required 
                           minlength="<?php echo PASSWORD_MIN_LENGTH; ?>">
                    <small>Minimum <?php echo PASSWORD_MIN_LENGTH; ?> characters</small>
                </div>
                
                <div class="form-group">
                    <label for="confirm_password">Confirm Password *</label>
                    <input type="password" id="confirm_password" name="confirm_password" required>
                </div>
                
                <div class="form-group">
                    <label class="checkbox-label">
                        <input type="checkbox" required>
                        I agree to the Terms of Service and Privacy Policy
                    </label>
                </div>
                
                <button type="submit" class="btn btn-primary btn-block">Register</button>
            </form>
            
            <div class="form-footer">
                <p>Already have an account? <a href="login.php">Login here</a></p>
            </div>
        </div>
    </main>
    
    <?php include 'includes/footer.php'; ?>
    
    <script src="assets/js/main.js"></script>
</body>
</html>