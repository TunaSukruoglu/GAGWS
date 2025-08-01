<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

// PHP error log dosyasını bul
$possible_log_files = [
    ini_get('error_log'),
    $_SERVER['DOCUMENT_ROOT'] . '\\error_log',
    $_SERVER['DOCUMENT_ROOT'] . '\\logs\\php_errors.log',
    'C:\\Windows\\Temp\\php-errors.log',
    'C:\\php\\logs\\php_errors.log',
    'C:\\inetpub\\logs\\php_errors.log',
    dirname(__FILE__) . '\\error_log',
    dirname(__FILE__) . '\\..\\error_log',
    '/var/log/php_errors.log',
    '/var/log/apache2/error.log',
    $_SERVER['DOCUMENT_ROOT'] . '/error_log',
    dirname(__FILE__) . '/error_log'
];

$log_content = '';
$log_file_found = '';

foreach ($possible_log_files as $log_file) {
    if ($log_file && file_exists($log_file) && is_readable($log_file)) {
        $log_file_found = $log_file;
        $log_content = file_get_contents($log_file);
        break;
    }
}

// Son 50 satırı al
if ($log_content) {
    $lines = explode("\n", $log_content);
    $recent_lines = array_slice($lines, -50);
    $log_content = implode("\n", $recent_lines);
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>PHP Error Log Viewer</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <div class="card">
            <div class="card-header">
                <h4>PHP Error Log Viewer</h4>
                <div class="d-flex gap-2">
                    <a href="add-property.php" class="btn btn-secondary btn-sm">Ana Forma Dön</a>
                    <a href="?refresh=1" class="btn btn-info btn-sm">Yenile</a>
                </div>
            </div>
            <div class="card-body">
                <?php if ($log_file_found): ?>
                    <div class="alert alert-info">
                        <strong>Log Dosyası:</strong> <?php echo $log_file_found; ?>
                        <br><strong>Son Güncelleme:</strong> <?php echo date('Y-m-d H:i:s', filemtime($log_file_found)); ?>
                    </div>
                    
                    <div class="bg-dark text-light p-3" style="max-height: 500px; overflow-y: auto;">
                        <pre style="color: #00ff00; font-size: 12px;"><?php echo htmlspecialchars($log_content ?: 'Log dosyası boş'); ?></pre>
                    </div>
                <?php else: ?>
                    <div class="alert alert-warning">
                        <strong>Log dosyası bulunamadı.</strong>
                        <br>Aranan konumlar:
                        <ul>
                            <?php foreach ($possible_log_files as $file): ?>
                                <li><?php echo $file ?: '(tanımsız)'; ?></li>
                            <?php endforeach; ?>
                        </ul>
                    </div>
                    
                    <div class="alert alert-info">
                        <strong>PHP Ayarları:</strong>
                        <br>error_log: <?php echo ini_get('error_log') ?: '(ayarlanmamış)'; ?>
                        <br>log_errors: <?php echo ini_get('log_errors') ? 'Açık' : 'Kapalı'; ?>
                        <br>display_errors: <?php echo ini_get('display_errors') ? 'Açık' : 'Kapalı'; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</body>
</html>
