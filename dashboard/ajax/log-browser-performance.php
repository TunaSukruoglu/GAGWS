<?php
header('Content-Type: application/json');

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $input = file_get_contents('php://input');
    $data = json_decode($input, true);
    
    if ($data) {
        $logFile = __DIR__ . '/../debug.log';
        $timestamp = date('d-M-Y H:i:s T');
        
        $logEntry = "[{$timestamp}] 🚀 BROWSER PERFORMANCE: " .
                    "Load: {$data['loadTime']}ms, " .
                    "DOM: {$data['domContentTime']}ms, " .
                    "Network: {$data['networkTime']}ms, " .
                    "Render: {$data['renderTime']}ms\n";
        
        file_put_contents($logFile, $logEntry, FILE_APPEND | LOCK_EX);
        
        echo json_encode(['status' => 'success']);
    } else {
        echo json_encode(['status' => 'error', 'message' => 'Invalid data']);
    }
} else {
    echo json_encode(['status' => 'error', 'message' => 'Method not allowed']);
}
?>
