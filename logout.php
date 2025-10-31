<?php
require_once 'config/config.php';
require_once 'includes/functions.php';

// Start session
start_secure_session();

// Delete session from database if exists
if (isset($_SESSION['session_token'])) {
    try {
        $pdo = getPDOConnection();
        $stmt = $pdo->prepare("DELETE FROM sessions WHERE session_token = ?");
        $stmt->execute([$_SESSION['session_token']]);
    } catch (Exception $e) {
        // Continue even if database delete fails
    }
}

// Unset all session variables
$_SESSION = array();

// Delete the session cookie
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"],
        $params["domain"],
        $params["secure"],
        $params["httponly"]
    );
}

// Destroy the session
session_destroy();

// Clear any authentication cookies
setcookie('remember_me', '', time() - 3600, '/');

// Prevent caching
header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
header("Cache-Control: post-check=0, pre-check=0", false);
header("Pragma: no-cache");
header("Expires: Sat, 26 Jul 1997 05:00:00 GMT");

// Redirect to home page
header("Location: index.php");
exit();
?>