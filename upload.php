<?php
// Web Yükleme Scripti - Sadece kurulum için kullanın, sonra silin!
// Bu dosyayı upload.php olarak kaydedin ve tarayıcıdan açın

// Güvenlik için basit şifre
$admin_password = "gokhan2024upload"; // Bu şifreyi değiştirin

session_start();
$is_logged_in = isset($_SESSION['upload_auth']) && $_SESSION['upload_auth'] === true;

// Giriş kontrolü
if (!$is_logged_in) {
    if (isset($_POST['password']) && $_POST['password'] === $admin_password) {
        $_SESSION['upload_auth'] = true;
        $is_logged_in = true;
    } else {
        ?>
        <!DOCTYPE html>
        <html>
        <head>
            <title>Dosya Yükleme - Giriş</title>
            <meta charset="utf-8">
            <style>
                body { font-family: Arial; background: #f5f5f5; padding: 50px; }
                .container { max-width: 400px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
                input[type="password"] { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 5px; }
                input[type="submit"] { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; }
                .warning { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; border-radius: 5px; margin-bottom: 20px; }
            </style>
        </head>
        <body>
            <div class="container">
                <h2>🔐 Dosya Yükleme Sistemi</h2>
                <div class="warning">
                    <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                    Bu script sadece kurulum için kullanılmalıdır. Kurulum tamamlandıktan sonra silinmelidir.
                </div>
                
                <?php if (isset($_POST['password'])): ?>
                    <p style="color: red;">❌ Yanlış şifre!</p>
                <?php endif; ?>
                
                <form method="POST">
                    <label>Yönetici Şifresi:</label>
                    <input type="password" name="password" required>
                    <input type="submit" value="Giriş Yap">
                </form>
            </div>
        </body>
        </html>
        <?php
        exit;
    }
}

// Ana yükleme arayüzü
if ($is_logged_in):
?>
<!DOCTYPE html>
<html>
<head>
    <title>Dosya Yükleme Sistemi</title>
    <meta charset="utf-8">
    <style>
        body { font-family: Arial; background: #f5f5f5; padding: 20px; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .upload-area { border: 2px dashed #007cba; padding: 40px; text-align: center; margin: 20px 0; border-radius: 10px; }
        .upload-area:hover { background: #f0f8ff; }
        .btn { background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 5px; cursor: pointer; margin: 5px; }
        .btn-danger { background: #dc3545; }
        .status { padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
        .error { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        .info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
        .file-list { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .progress { width: 100%; height: 20px; background: #f0f0f0; border-radius: 10px; overflow: hidden; margin: 10px 0; }
        .progress-bar { height: 100%; background: #007cba; transition: width 0.3s ease; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🏠 Gökhan Aydınlı Gayrimenkul - Dosya Yükleme</h1>
        
        <div class="info status">
            <strong>📋 Kurulum Adımları:</strong><br>
            1. Tüm proje dosyalarını ZIP olarak yükleyin<br>
            2. ZIP dosyasını otomatik olarak açın<br>
            3. Veritabanı kurulum scriptini çalıştırın<br>
            4. Bu dosyayı güvenlik için silin
        </div>

        <!-- Dosya Yükleme -->
        <div class="upload-area" id="uploadArea">
            <h3>📁 Dosya Yükleme</h3>
            <p>ZIP dosyanızı buraya sürükleyin veya seçin</p>
            <input type="file" id="fileInput" accept=".zip" style="display: none;">
            <button class="btn" onclick="document.getElementById('fileInput').click()">Dosya Seç</button>
        </div>

        <div id="uploadStatus"></div>
        <div id="progressContainer" style="display: none;">
            <div class="progress">
                <div class="progress-bar" id="progressBar" style="width: 0%"></div>
            </div>
            <span id="progressText">0%</span>
        </div>

        <!-- Mevcut Dosyalar -->
        <div class="file-list">
            <h3>📂 Mevcut Dosyalar</h3>
            <div id="fileList">
                <?php
                $files = scandir('.');
                foreach ($files as $file) {
                    if ($file != '.' && $file != '..' && $file != 'upload.php') {
                        echo "<div>📄 $file</div>";
                    }
                }
                ?>
            </div>
        </div>

        <!-- Veritabanı Kurulumu -->
        <div style="margin-top: 30px;">
            <h3>🗄️ Veritabanı Kurulumu</h3>
            <p>SQL dosyasını yükledikten sonra veritabanı bağlantısını test edin:</p>
            <button class="btn" onclick="testDatabase()">Veritabanı Bağlantısını Test Et</button>
            <div id="dbStatus"></div>
        </div>

        <!-- Güvenlik -->
        <div style="margin-top: 30px; padding: 20px; background: #fff3cd; border-radius: 5px;">
            <h3>⚠️ Güvenlik</h3>
            <p><strong>ÖNEMLİ:</strong> Kurulum tamamlandıktan sonra bu dosyayı silmeyi unutmayın!</p>
            <button class="btn btn-danger" onclick="deleteSelf()">Bu Dosyayı Sil</button>
        </div>
        
        <div style="margin-top: 20px;">
            <button class="btn btn-danger" onclick="logout()">Çıkış Yap</button>
        </div>
    </div>

    <script>
        // Drag & Drop
        const uploadArea = document.getElementById('uploadArea');
        const fileInput = document.getElementById('fileInput');

        uploadArea.addEventListener('dragover', (e) => {
            e.preventDefault();
            uploadArea.style.background = '#f0f8ff';
        });

        uploadArea.addEventListener('dragleave', () => {
            uploadArea.style.background = '';
        });

        uploadArea.addEventListener('drop', (e) => {
            e.preventDefault();
            uploadArea.style.background = '';
            const files = e.dataTransfer.files;
            if (files.length > 0) {
                uploadFile(files[0]);
            }
        });

        fileInput.addEventListener('change', (e) => {
            if (e.target.files.length > 0) {
                uploadFile(e.target.files[0]);
            }
        });

        function uploadFile(file) {
            if (!file.name.endsWith('.zip')) {
                document.getElementById('uploadStatus').innerHTML = '<div class="error status">❌ Sadece ZIP dosyaları kabul edilir!</div>';
                return;
            }

            const formData = new FormData();
            formData.append('file', file);
            formData.append('action', 'upload');

            document.getElementById('progressContainer').style.display = 'block';
            document.getElementById('uploadStatus').innerHTML = '<div class="info status">📤 Yükleniyor...</div>';

            const xhr = new XMLHttpRequest();
            
            xhr.upload.addEventListener('progress', (e) => {
                if (e.lengthComputable) {
                    const percent = Math.round((e.loaded / e.total) * 100);
                    document.getElementById('progressBar').style.width = percent + '%';
                    document.getElementById('progressText').textContent = percent + '%';
                }
            });

            xhr.addEventListener('load', () => {
                document.getElementById('progressContainer').style.display = 'none';
                if (xhr.status === 200) {
                    const response = JSON.parse(xhr.responseText);
                    if (response.success) {
                        document.getElementById('uploadStatus').innerHTML = '<div class="success status">✅ ' + response.message + '</div>';
                        setTimeout(() => location.reload(), 2000);
                    } else {
                        document.getElementById('uploadStatus').innerHTML = '<div class="error status">❌ ' + response.message + '</div>';
                    }
                } else {
                    document.getElementById('uploadStatus').innerHTML = '<div class="error status">❌ Yükleme hatası!</div>';
                }
            });

            xhr.send(formData);
        }

        function testDatabase() {
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=test_db'
            })
            .then(response => response.json())
            .then(data => {
                const statusClass = data.success ? 'success' : 'error';
                const icon = data.success ? '✅' : '❌';
                document.getElementById('dbStatus').innerHTML = `<div class="${statusClass} status">${icon} ${data.message}</div>`;
            });
        }

        function deleteSelf() {
            if (confirm('Bu dosyayı silmek istediğinizden emin misiniz?')) {
                fetch(window.location.href, {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                    body: 'action=delete_self'
                })
                .then(response => response.json())
                .then(data => {
                    alert(data.message);
                    if (data.success) {
                        window.location.href = './';
                    }
                });
            }
        }

        function logout() {
            window.location.href = '?logout=1';
        }
    </script>
</body>
</html>

<?php
endif;

// İşlemler
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'upload':
            if (isset($_FILES['file'])) {
                $file = $_FILES['file'];
                $uploadDir = './';
                $uploadFile = $uploadDir . basename($file['name']);
                
                if (move_uploaded_file($file['tmp_name'], $uploadFile)) {
                    // ZIP dosyasını aç
                    $zip = new ZipArchive;
                    if ($zip->open($uploadFile) === TRUE) {
                        $zip->extractTo($uploadDir);
                        $zip->close();
                        unlink($uploadFile); // ZIP dosyasını sil
                        echo json_encode(['success' => true, 'message' => 'Dosyalar başarıyla yüklendi ve açıldı!']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'ZIP dosyası açılamadı!']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'Dosya yüklenemedi!']);
                }
            }
            break;
            
        case 'test_db':
            try {
                if (file_exists('./db.php')) {
                    include './db.php';
                    if (isset($conn) && $conn->ping()) {
                        echo json_encode(['success' => true, 'message' => 'Veritabanı bağlantısı başarılı!']);
                    } else {
                        echo json_encode(['success' => false, 'message' => 'Veritabanı bağlantısı başarısız!']);
                    }
                } else {
                    echo json_encode(['success' => false, 'message' => 'db.php dosyası bulunamadı!']);
                }
            } catch (Exception $e) {
                echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
            }
            break;
            
        case 'delete_self':
            if (unlink(__FILE__)) {
                echo json_encode(['success' => true, 'message' => 'Dosya başarıyla silindi!']);
            } else {
                echo json_encode(['success' => false, 'message' => 'Dosya silinemedi!']);
            }
            break;
    }
    exit;
}

// Çıkış
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}
?>
