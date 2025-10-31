<?php
require_once __DIR__ . '/../config/config.php';
require_once __DIR__ . '/../config/database.php';

// Security Functions
function sanitize_input($data) {
    $data = trim($data);
    $data = stripslashes($data);
    $data = htmlspecialchars($data);
    return $data;
}

function generate_csrf_token() {
    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }
    return $_SESSION['csrf_token'];
}

function verify_csrf_token($token) {
    return isset($_SESSION['csrf_token']) && hash_equals($_SESSION['csrf_token'], $token);
}

// Session Management
function start_secure_session() {
    if (session_status() === PHP_SESSION_NONE) {
        session_start();
    }
}

function is_logged_in() {
    return isset($_SESSION['user_id']) && isset($_SESSION['user_role']);
}

function require_login() {
    if (!is_logged_in()) {
        header('Location: ' . SITE_URL . '/login.php');
        exit();
    }
}

function require_role($roles) {
    require_login();
    if (!in_array($_SESSION['user_role'], $roles)) {
        header('Location: ' . SITE_URL . '/unauthorized.php');
        exit();
    }
}

// User Functions
function get_user_by_id($user_id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
    $stmt->execute([$user_id]);
    return $stmt->fetch();
}

function get_user_by_email($email) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM users WHERE email = ?");
    $stmt->execute([$email]);
    return $stmt->fetch();
}

function create_user($name, $email, $password, $role = 'individual') {
    $pdo = getPDOConnection();
    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
    
    $stmt = $pdo->prepare("INSERT INTO users (name, email, password, role) VALUES (?, ?, ?, ?)");
    return $stmt->execute([$name, $email, $hashed_password, $role]);
}

function verify_password($email, $password) {
    $user = get_user_by_email($email);
    if ($user && password_verify($password, $user['password'])) {
        return $user;
    }
    return false;
}

// Certificate Functions
function generate_certificate_id() {
    return 'CERT-' . date('Y') . '-' . strtoupper(bin2hex(random_bytes(6)));
}

function get_certificate_by_id($certificate_id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT c.*, u.name as issuer_name, u.email as issuer_email 
                          FROM certificates c 
                          JOIN users u ON c.issuer_id = u.id 
                          WHERE c.certificate_id = ?");
    $stmt->execute([$certificate_id]);
    return $stmt->fetch();
}

function get_certificates_by_issuer($issuer_id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE issuer_id = ? ORDER BY created_at DESC");
    $stmt->execute([$issuer_id]);
    return $stmt->fetchAll();
}

function get_all_certificates() {
    $pdo = getPDOConnection();
    $stmt = $pdo->query("SELECT c.*, u.name as issuer_name 
                        FROM certificates c 
                        JOIN users u ON c.issuer_id = u.id 
                        ORDER BY c.created_at DESC");
    return $stmt->fetchAll();
}

function create_certificate($data) {
    $pdo = getPDOConnection();
    
    $stmt = $pdo->prepare("INSERT INTO certificates 
        (certificate_id, issuer_id, recipient_name, recipient_id, recipient_email, 
         title, field, description, issue_date, certificate_file) 
        VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
    
    return $stmt->execute([
        $data['certificate_id'],
        $data['issuer_id'],
        $data['recipient_name'],
        $data['recipient_id'],
        $data['recipient_email'],
        $data['title'],
        $data['field'],
        $data['description'],
        $data['issue_date'],
        $data['certificate_file']
    ]);
}

function update_certificate_status($certificate_id, $status) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("UPDATE certificates SET verification_status = ? WHERE certificate_id = ?");
    return $stmt->execute([$status, $certificate_id]);
}

function revoke_certificate($certificate_id) {
    $pdo = getPDOConnection();
    $stmt = $pdo->prepare("UPDATE certificates SET status = 'revoked' WHERE certificate_id = ?");
    return $stmt->execute([$certificate_id]);
}

// File Upload Functions
function upload_certificate_file($file) {
    $target_dir = UPLOAD_PATH;
    
    if (!file_exists($target_dir)) {
        mkdir($target_dir, 0777, true);
    }
    
    $file_extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    
    // Validate file
    if ($file['size'] > MAX_FILE_SIZE) {
        return ['success' => false, 'error' => 'File size exceeds limit'];
    }
    
    if (!in_array($file_extension, ALLOWED_EXTENSIONS)) {
        return ['success' => false, 'error' => 'Invalid file type'];
    }
    
    $new_filename = uniqid() . '_' . time() . '.' . $file_extension;
    $target_file = $target_dir . $new_filename;
    
    if (move_uploaded_file($file['tmp_name'], $target_file)) {
        return ['success' => true, 'filename' => $new_filename];
    }
    
    return ['success' => false, 'error' => 'Failed to upload file'];
}

// Notification Functions
function send_email_notification($to, $subject, $message) {
    $headers = "From: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Reply-To: " . ADMIN_EMAIL . "\r\n";
    $headers .= "Content-Type: text/html; charset=UTF-8\r\n";
    
    return mail($to, $subject, $message, $headers);
}

// Utility Functions
function redirect($url) {
    header("Location: $url");
    exit();
}

function format_date($date) {
    return date('F j, Y', strtotime($date));
}

function get_user_role_name($role) {
    $roles = [
        'admin' => 'Administrator',
        'issuer' => 'Certificate Issuer',
        'requester' => 'Verification Requester',
        'individual' => 'Individual User'
    ];
    return $roles[$role] ?? 'Unknown';
}

// Blockchain Functions (to be implemented in Phase IX)
function upload_to_ipfs($file_path) {
    // Placeholder for IPFS upload
    return [
        'success' => false,
        'hash' => null,
        'message' => 'IPFS integration pending'
    ];
}

function store_on_blockchain($certificate_data) {
    // Placeholder for blockchain storage
    return [
        'success' => false,
        'tx_hash' => null,
        'message' => 'Blockchain integration pending'
    ];
}

function verify_blockchain_certificate($tx_hash) {
    // Placeholder for blockchain verification
    return [
        'success' => false,
        'data' => null,
        'message' => 'Blockchain verification pending'
    ];
}

// Statistics Functions
function get_platform_stats() {
    $pdo = getPDOConnection();
    
    $stats = [];
    
    // Total users
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM users");
    $stats['total_users'] = $stmt->fetch()['total'];
    
    // Total certificates
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates");
    $stats['total_certificates'] = $stmt->fetch()['total'];
    
    // Verified certificates
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates WHERE verification_status = 'verified'");
    $stats['verified_certificates'] = $stmt->fetch()['total'];
    
    // Pending certificates
    $stmt = $pdo->query("SELECT COUNT(*) as total FROM certificates WHERE verification_status = 'pending'");
    $stats['pending_certificates'] = $stmt->fetch()['total'];
    
    return $stats;
}
?>