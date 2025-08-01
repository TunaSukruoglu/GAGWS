<?php
// 🚀 PERFORMANCE LOGGER
$data = json_decode(file_get_contents('php://input'), true);

if ($data && isset($data['loadTime'])) {
    $logEntry = sprintf(
        "[%s] %s: %dms\n",
        date('d-M-Y H:i:s'),
        $data['type'] ?? 'unknown',
        $data['loadTime']
    );
    
    file_put_contents(__DIR__ . '/../performance.log', $logEntry, FILE_APPEND | LOCK_EX);
}

echo json_encode(['status' => 'logged']);
?>
