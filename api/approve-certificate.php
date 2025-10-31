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

// Update status to verified
$stmt = $pdo->prepare("UPDATE certificates SET verification_status = 'verified' WHERE id = ?");
if ($stmt->execute([$certificate['id']])) {
    // Send notification email
    $message = "<h2>Certificate Verified</h2>";
    $message .= "<p>Dear {$certificate['recipient_name']},</p>";
    $message .= "<p>Your certificate has been verified and approved.</p>";
    $message .= "<p><strong>Certificate ID:</strong> {$certificate['certificate_id']}</p>";
    $message .= "<p>You can view your verified certificate at: " . SITE_URL . "/verify.php?id={$certificate['certificate_id']}</p>";
    
    send_email_notification($certificate['recipient_email'], 'Certificate Verified', $message);
    
    $_SESSION['success'] = 'Certificate approved successfully';
} else {
    $_SESSION['error'] = 'Failed to approve certificate';
}

// Redirect back to the referring page or admin panel
$redirect_url = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../admin/manage-certificates.php';
header('Location: ' . $redirect_url);
exit();
?>