<?php
header('Content-Type: application/json');
require_once '../config/config.php';
require_once '../includes/functions.php';

// Allow CORS
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Handle preflight
if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    exit(0);
}

$response = [
    'success' => false,
    'verified' => false,
    'certificate' => null,
    'message' => ''
];

try {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $input = json_decode(file_get_contents('php://input'), true);
        $certificate_id = $input['certificate_id'] ?? null;
    } else {
        $certificate_id = $_GET['certificate_id'] ?? null;
    }
    
    if (empty($certificate_id)) {
        $response['message'] = 'Certificate ID is required';
        echo json_encode($response);
        exit;
    }
    
    $certificate = get_certificate_by_id($certificate_id);
    
    if ($certificate) {
        $response['success'] = true;
        $response['certificate'] = $certificate;
        
        if ($certificate['verification_status'] === 'verified' && $certificate['status'] === 'active') {
            $response['verified'] = true;
            $response['message'] = 'Certificate is verified and valid';
        } elseif ($certificate['status'] === 'revoked') {
            $response['message'] = 'Certificate has been revoked';
        } else {
            $response['message'] = 'Certificate verification is pending';
        }
    } else {
        $response['message'] = 'Certificate not found';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>