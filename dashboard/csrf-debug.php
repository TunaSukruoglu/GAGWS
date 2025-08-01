<?php
session_start();

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmanız gerekiyor.");
}

// Debug modunda tüm verileri logla
error_reporting(E_ALL);
ini_set('display_errors', 1);

// POST işlemi varsa
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    error_log("=== CSRF DEBUG TEST POST ===");
    error_log("Raw POST data: " . file_get_contents('php://input'));
    error_log("Content-Type: " . ($_SERVER['CONTENT_TYPE'] ?? 'undefined'));
    error_log("Content-Length: " . ($_SERVER['CONTENT_LENGTH'] ?? 'undefined'));
    error_log("POST array: " . print_r($_POST, true));
    error_log("SESSION array: " . print_r($_SESSION, true));
    error_log("FILES array: " . print_r($_FILES, true));
    
    $result = [
        'status' => 'success',
        'post_data' => $_POST,
        'session_token' => $_SESSION['csrf_token'] ?? 'YOK',
        'post_token' => $_POST['csrf_token'] ?? 'YOK',
        'tokens_match' => false,
        'content_type' => $_SERVER['CONTENT_TYPE'] ?? 'undefined',
        'raw_post' => file_get_contents('php://input')
    ];
    
    if (isset($_SESSION['csrf_token']) && isset($_POST['csrf_token'])) {
        $result['tokens_match'] = hash_equals($_SESSION['csrf_token'], $_POST['csrf_token']);
    }
    
    echo "<pre>" . json_encode($result, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE) . "</pre>";
    exit;
}

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

$csrf_token = $_SESSION['csrf_token'];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Token Debug Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        .debug-box {
            background-color: #f8f9fa;
            border: 1px solid #dee2e6;
            border-radius: 0.375rem;
            padding: 1rem;
            margin: 1rem 0;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>CSRF Token Debug Test</h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Debug Bilgileri -->
                        <div class="debug-box">
                            <h6>Debug Bilgileri:</h6>
                            <p><strong>Session Token:</strong> <code><?= htmlspecialchars($csrf_token) ?></code></p>
                            <p><strong>Session ID:</strong> <code><?= session_id() ?></code></p>
                            <p><strong>User ID:</strong> <code><?= $_SESSION['user_id'] ?? 'undefined' ?></code></p>
                            <p><strong>PHP Version:</strong> <code><?= PHP_VERSION ?></code></p>
                            <p><strong>Max POST Size:</strong> <code><?= ini_get('post_max_size') ?></code></p>
                            <p><strong>Max Input Vars:</strong> <code><?= ini_get('max_input_vars') ?></code></p>
                        </div>
                        
                        <!-- Test Form 1: Normal Form -->
                        <div class="mb-4">
                            <h5>Test 1: Normal Form (application/x-www-form-urlencoded)</h5>
                            <form method="POST" action="" id="normalForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" id="normalToken">
                                <div class="mb-3">
                                    <label for="test_input" class="form-label">Test Input</label>
                                    <input type="text" class="form-control" id="test_input" name="test_input" value="Normal form test">
                                </div>
                                <button type="submit" class="btn btn-primary">Normal Form Gönder</button>
                            </form>
                        </div>
                        
                        <!-- Test Form 2: Multipart Form -->
                        <div class="mb-4">
                            <h5>Test 2: Multipart Form (multipart/form-data)</h5>
                            <form method="POST" action="" enctype="multipart/form-data" id="multipartForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" id="multipartToken">
                                <div class="mb-3">
                                    <label for="test_input2" class="form-label">Test Input</label>
                                    <input type="text" class="form-control" id="test_input2" name="test_input2" value="Multipart form test">
                                </div>
                                <div class="mb-3">
                                    <label for="test_file" class="form-label">Test File</label>
                                    <input type="file" class="form-control" id="test_file" name="test_file">
                                </div>
                                <button type="submit" class="btn btn-success">Multipart Form Gönder</button>
                            </form>
                        </div>
                        
                        <!-- Test Form 3: Ajax Form -->
                        <div class="mb-4">
                            <h5>Test 3: Ajax Form</h5>
                            <form id="ajaxForm">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf_token) ?>" id="ajaxToken">
                                <div class="mb-3">
                                    <label for="test_input3" class="form-label">Test Input</label>
                                    <input type="text" class="form-control" id="test_input3" name="test_input3" value="Ajax form test">
                                </div>
                                <button type="button" class="btn btn-warning" onclick="sendAjax()">Ajax Form Gönder</button>
                            </form>
                            <div id="ajaxResult" class="mt-3"></div>
                        </div>
                        
                        <!-- Real-time Token Monitor -->
                        <div class="debug-box">
                            <h6>Real-time Token Monitor:</h6>
                            <p>Normal Form Token: <span id="normalTokenMonitor"></span></p>
                            <p>Multipart Form Token: <span id="multipartTokenMonitor"></span></p>
                            <p>Ajax Form Token: <span id="ajaxTokenMonitor"></span></p>
                            <button type="button" class="btn btn-sm btn-info" onclick="checkTokens()">Token'ları Kontrol Et</button>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        // Token monitörünü güncelle
        function checkTokens() {
            document.getElementById('normalTokenMonitor').textContent = document.getElementById('normalToken').value;
            document.getElementById('multipartTokenMonitor').textContent = document.getElementById('multipartToken').value;
            document.getElementById('ajaxTokenMonitor').textContent = document.getElementById('ajaxToken').value;
        }
        
        // Sayfa yüklendiğinde tokenleri kontrol et
        checkTokens();
        
        // Her 2 saniyede bir tokenleri kontrol et
        setInterval(checkTokens, 2000);
        
        // Ajax form gönderimi
        function sendAjax() {
            const form = document.getElementById('ajaxForm');
            const formData = new FormData(form);
            
            console.log('Ajax gönderiliyor...');
            console.log('FormData entries:');
            for (let [key, value] of formData.entries()) {
                console.log(key, value);
            }
            
            fetch(window.location.href, {
                method: 'POST',
                body: formData
            })
            .then(response => response.text())
            .then(data => {
                document.getElementById('ajaxResult').innerHTML = '<div class="alert alert-info"><pre>' + data + '</pre></div>';
            })
            .catch(error => {
                document.getElementById('ajaxResult').innerHTML = '<div class="alert alert-danger">Hata: ' + error + '</div>';
            });
        }
        
        // Form gönderimlerinde debug
        document.getElementById('normalForm').addEventListener('submit', function(e) {
            console.log('Normal form gönderiliyor...');
            console.log('CSRF Token:', document.getElementById('normalToken').value);
        });
        
        document.getElementById('multipartForm').addEventListener('submit', function(e) {
            console.log('Multipart form gönderiliyor...');
            console.log('CSRF Token:', document.getElementById('multipartToken').value);
        });
    </script>
</body>
</html>
