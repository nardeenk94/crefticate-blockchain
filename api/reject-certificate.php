<?php
require_once '../config/config.php';
require_once '../includes/functions.php';

start_secure_session();
require_role(['admin']);

$certificate_db_id = $_GET['id'] ?? null;
$certificate_id = $_GET['cert_id'] ?? null;

if (!$certificate_db_id && !$certificate_id) {
    $_SESSION['error'] = 'Invalid certificate ID';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../admin/manage-certificates.php'));
    exit();
}

$pdo = getPDOConnection();

// Get certificate by DB id or certificate_id
if ($certificate_db_id) {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE id = ?");
    $stmt->execute([$certificate_db_id]);
} else {
    $stmt = $pdo->prepare("SELECT * FROM certificates WHERE certificate_id = ?");
    $stmt->execute([$certificate_id]);
}

$certificate = $stmt->fetch();

if (!$certificate) {
    $_SESSION['error'] = 'Certificate not found';
    header('Location: ' . (isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../admin/manage-certificates.php'));
    exit();
}

// Update status to rejected
$stmt = $pdo->prepare("UPDATE certificates SET verification_status = 'rejected' WHERE id = ?");
if ($stmt->execute([$certificate['id']])) {
    // Send notification email
    $message = "<h2>Certificate Verification Rejected</h2>";
    $message .= "<p>Dear {$certificate['recipient_name']},</p>";
    $message .= "<p>Your certificate verification has been rejected.</p>";
    $message .= "<p><strong>Certificate ID:</strong> {$certificate['certificate_id']}</p>";
    $message .= "<p>Please contact support for more information.</p>";
    
    send_email_notification($certificate['recipient_email'], 'Certificate Verification Rejected', $message);
    
    $_SESSION['success'] = 'Certificate rejected';
} else {
    $_SESSION['error'] = 'Failed to reject certificate';
}

// Redirect back to the referring page or admin panel
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../admin/manage-certificates.php';
header('Location: ' . $redirect_url);
exit();
?>