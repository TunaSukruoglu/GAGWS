<?php
// Upload debug endpoint
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);
ini_set('error_log', __DIR__ . '/upload-debug.log');

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST, GET, OPTIONS');
header('Access-Control-Allow-Headers: Content-Type');

// Log all requests
$logData = [
    'timestamp' => date('Y-m-d H:i:s'),
    'method' => $_SERVER['REQUEST_METHOD'],
    'files' => $_FILES ?? 'NO_FILES',
    'post' => $_POST ?? 'NO_POST',
    'session_user_id' => $_SESSION['user_id'] ?? 'NOT_SET'
];
error_log("UPLOAD DEBUG: " . json_encode($logData));

if ($_SERVER['REQUEST_METHOD'] === 'OPTIONS') {
    echo json_encode(['status' => 'options_ok']);
    exit(0);
}

session_start();

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Simple file upload test
    if (isset($_FILES['file']) && $_FILES['file']['error'] === UPLOAD_ERR_OK) {
        $tmpName = $_FILES['file']['tmp_name'];
        $originalName = $_FILES['file']['name'];
        $fileSize = $_FILES['file']['size'];
        
        error_log("FILE RECEIVED: $originalName, Size: $fileSize bytes");
        
        // Simulate upload process
        usleep(500000); // 0.5 second delay
        
        echo json_encode([
            'success' => true,
            'debug' => true,
            'filename' => $originalName,
            'size' => $fileSize,
            'test_id' => 'test_' . time() . '_' . rand(1000, 9999),
            'message' => 'Debug upload successful'
        ]);
    } else {
        $error_msg = 'No file or upload error';
        if (isset($_FILES['file'])) {
            $error_msg .= ', Error code: ' . $_FILES['file']['error'];
        }
        error_log("UPLOAD ERROR: $error_msg");
        
        echo json_encode([
            'success' => false,
            'error' => $error_msg,
            'debug' => true,
            'files_received' => $_FILES ?? null
        ]);
    }
} else {
    echo json_encode([
        'error' => 'Only POST method allowed',
        'debug' => true,
        'method_received' => $_SERVER['REQUEST_METHOD']
    ]);
}
?>
