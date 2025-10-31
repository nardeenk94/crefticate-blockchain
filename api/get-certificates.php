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
    'data' => null,
    'message' => ''
];

try {
    $certificate_id = $_GET['id'] ?? null;
    
    if ($certificate_id) {
        // Get specific certificate
        $certificate = get_certificate_by_id($certificate_id);
        
        if ($certificate) {
            $response['success'] = true;
            $response['data'] = $certificate;
            $response['message'] = 'Certificate found';
        } else {
            $response['message'] = 'Certificate not found';
        }
    } else {
        // Get all certificates
        $certificates = get_all_certificates();
        
        $response['success'] = true;
        $response['data'] = $certificates;
        $response['message'] = 'Certificates retrieved successfully';
    }
} catch (Exception $e) {
    $response['message'] = 'Error: ' . $e->getMessage();
}

echo json_encode($response);
?>