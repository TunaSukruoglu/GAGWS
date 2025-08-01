<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

// PHP bilgilerini topla
$php_info = [
    'PHP Version' => PHP_VERSION,
    'Server Software' => $_SERVER['SERVER_SOFTWARE'] ?? 'undefined',
    'Document Root' => $_SERVER['DOCUMENT_ROOT'] ?? 'undefined',
    'Upload Max Filesize' => ini_get('upload_max_filesize'),
    'Post Max Size' => ini_get('post_max_size'),
    'Max Input Vars' => ini_get('max_input_vars'),
    'Max Input Time' => ini_get('max_input_time'),
    'Max Execution Time' => ini_get('max_execution_time'),
    'Memory Limit' => ini_get('memory_limit'),
    'Session Cookie Lifetime' => ini_get('session.cookie_lifetime'),
    'Session Save Path' => ini_get('session.save_path'),
    'Session Name' => session_name(),
    'Session ID' => session_id(),
    'Error Log' => ini_get('error_log'),
    'Log Errors' => ini_get('log_errors') ? 'Enabled' : 'Disabled',
    'Display Errors' => ini_get('display_errors') ? 'Enabled' : 'Disabled'
];

// Klasör izinlerini kontrol et
$directories_to_check = [
    'uploads' => '../uploads',
    'uploads/properties' => '../uploads/properties',
    'dashboard' => '.',
    'session_path' => session_save_path()
];

$directory_permissions = [];
foreach ($directories_to_check as $name => $path) {
    if (file_exists($path)) {
        $directory_permissions[$name] = [
            'exists' => true,
            'readable' => is_readable($path),
            'writable' => is_writable($path),
            'permissions' => substr(sprintf('%o', fileperms($path)), -4)
        ];
    } else {
        $directory_permissions[$name] = [
            'exists' => false,
            'readable' => false,
            'writable' => false,
            'permissions' => 'N/A'
        ];
    }
}

// Session bilgileri
$session_info = [
    'Session Active' => session_status() === PHP_SESSION_ACTIVE ? 'Yes' : 'No',
    'Session Variables' => array_keys($_SESSION),
    'CSRF Token Set' => isset($_SESSION['csrf_token']) ? 'Yes' : 'No',
    'CSRF Token Length' => isset($_SESSION['csrf_token']) ? strlen($_SESSION['csrf_token']) : 0,
    'User ID' => $_SESSION['user_id'] ?? 'undefined'
];

// Sunucu headers
$important_headers = [
    'HTTP_HOST',
    'SERVER_NAME',
    'REQUEST_METHOD',
    'CONTENT_TYPE',
    'CONTENT_LENGTH',
    'HTTP_USER_AGENT',
    'REMOTE_ADDR'
];

$server_headers = [];
foreach ($important_headers as $header) {
    $server_headers[$header] = $_SERVER[$header] ?? 'undefined';
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistem Bilgileri</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .info-table {
            font-size: 14px;
        }
        .status-ok {
            color: #28a745;
            font-weight: bold;
        }
        .status-warning {
            color: #ffc107;
            font-weight: bold;
        }
        .status-error {
            color: #dc3545;
            font-weight: bold;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header">
                        <h4>Sistem Bilgileri ve Tanılama</h4>
                        <small class="text-muted">CSRF token sorunları için sistem kontrolü</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- PHP Bilgileri -->
                        <h5>PHP Konfigürasyonu</h5>
                        <div class="table-responsive">
                            <table class="table table-sm info-table">
                                <tbody>
                                    <?php foreach ($php_info as $key => $value): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                        <td><?= htmlspecialchars($value) ?></td>
                                        <td>
                                            <?php
                                            // Önemli ayarlar için durum kontrolü
                                            if (strpos($key, 'Max') !== false || strpos($key, 'Limit') !== false) {
                                                $numericValue = (int) preg_replace('/[^0-9]/', '', $value);
                                                if ($numericValue < 10) {
                                                    echo '<span class="status-error">DÜŞÜK</span>';
                                                } elseif ($numericValue < 50) {
                                                    echo '<span class="status-warning">ORTA</span>';
                                                } else {
                                                    echo '<span class="status-ok">İYİ</span>';
                                                }
                                            } elseif ($key === 'Log Errors' && $value === 'Enabled') {
                                                echo '<span class="status-ok">AKTİF</span>';
                                            } elseif ($key === 'Display Errors' && $value === 'Disabled') {
                                                echo '<span class="status-ok">KAPALI</span>';
                                            }
                                            ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Klasör İzinleri -->
                        <h5 class="mt-4">Klasör İzinleri</h5>
                        <div class="table-responsive">
                            <table class="table table-sm info-table">
                                <thead>
                                    <tr>
                                        <th>Klasör</th>
                                        <th>Varlık</th>
                                        <th>Okunabilir</th>
                                        <th>Yazılabilir</th>
                                        <th>İzinler</th>
                                        <th>Durum</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($directory_permissions as $name => $info): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($name) ?></strong></td>
                                        <td><?= $info['exists'] ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                                        <td><?= $info['readable'] ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                                        <td><?= $info['writable'] ? '<span class="status-ok">✓</span>' : '<span class="status-error">✗</span>' ?></td>
                                        <td><?= htmlspecialchars($info['permissions']) ?></td>
                                        <td>
                                            <?php if ($info['exists'] && $info['readable'] && $info['writable']): ?>
                                                <span class="status-ok">TAM ERİŞİM</span>
                                            <?php elseif ($info['exists']): ?>
                                                <span class="status-warning">KISITLI ERİŞİM</span>
                                            <?php else: ?>
                                                <span class="status-error">MEVCUT DEĞİL</span>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Session Bilgileri -->
                        <h5 class="mt-4">Session Bilgileri</h5>
                        <div class="table-responsive">
                            <table class="table table-sm info-table">
                                <tbody>
                                    <?php foreach ($session_info as $key => $value): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                        <td>
                                            <?php if (is_array($value)): ?>
                                                <?= implode(', ', array_map('htmlspecialchars', $value)) ?>
                                            <?php else: ?>
                                                <?= htmlspecialchars($value) ?>
                                            <?php endif; ?>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Sunucu Headers -->
                        <h5 class="mt-4">Sunucu Headers</h5>
                        <div class="table-responsive">
                            <table class="table table-sm info-table">
                                <tbody>
                                    <?php foreach ($server_headers as $key => $value): ?>
                                    <tr>
                                        <td><strong><?= htmlspecialchars($key) ?></strong></td>
                                        <td><?= htmlspecialchars($value) ?></td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                        
                        <!-- Test Butonları -->
                        <div class="mt-4">
                            <h5>Test Araçları</h5>
                            <div class="d-flex gap-2 flex-wrap">
                                <a href="csrf-debug.php" class="btn btn-primary">CSRF Debug Test</a>
                                <a href="view-logs.php" class="btn btn-info">Error Log Görüntüle</a>
                                <a href="add-property.php" class="btn btn-success">İlan Ekleme Formu</a>
                                <button type="button" class="btn btn-warning" onclick="location.reload()">Sayfayı Yenile</button>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
