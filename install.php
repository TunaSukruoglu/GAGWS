<?php
/**
 * Gökhan Aydınlı Gayrimenkul Sitesi - Hızlı Kurulum Scripti
 * Bu dosyayı install.php olarak kaydedin ve tarayıcıdan çalıştırın
 * Kurulum tamamlandıktan sonra bu dosyayı SİLİN!
 */

// Hata gösterimini aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Güvenlik için basit şifre
$setup_password = "kurulum2024"; // Bu şifreyi değiştirebilirsiniz

session_start();
$is_authenticated = isset($_SESSION['setup_auth']) && $_SESSION['setup_auth'] === true;

if (!$is_authenticated) {
    if (isset($_POST['password']) && $_POST['password'] === $setup_password) {
        $_SESSION['setup_auth'] = true;
        $is_authenticated = true;
    } else {
        showLoginForm();
        exit;
    }
}

// Ana kurulum fonksiyonu
if (isset($_POST['action'])) {
    header('Content-Type: application/json');
    
    switch ($_POST['action']) {
        case 'setup_database':
            setupDatabase();
            exit;
        case 'test_connection':
            testConnection();
            exit;
    }
}

if (isset($_GET['step'])) {
    switch ($_GET['step']) {
        case 'database':
            setupDatabase();
            break;
        case 'test':
            testConnection();
            break;
        case 'complete':
            completeSetup();
            break;
        default:
            showMainPage();
    }
} else {
    showMainPage();
}

function showLoginForm() {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kurulum - Gökhan Aydınlı Gayrimenkul</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 500px; margin: 50px auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 24px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
            .subtitle { color: #7f8c8d; }
            .form-group { margin-bottom: 20px; }
            label { display: block; margin-bottom: 5px; font-weight: bold; color: #2c3e50; }
            input[type="password"] { width: 100%; padding: 12px; border: 1px solid #ddd; border-radius: 5px; font-size: 16px; }
            .btn { width: 100%; padding: 12px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; }
            .btn:hover { background: #2980b9; }
            .alert { padding: 15px; margin-bottom: 20px; border-radius: 5px; }
            .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
            .alert-danger { background: #f8d7da; border: 1px solid #f5c6cb; color: #721c24; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">🏠 Gökhan Aydınlı Gayrimenkul</div>
                <div class="subtitle">Kurulum Scripti</div>
            </div>
            
            <div class="alert alert-warning">
                <strong>⚠️ Güvenlik Uyarısı:</strong><br>
                Bu script sadece kurulum için kullanılmalıdır. Kurulum tamamlandıktan sonra SİLİNMELİDİR.
            </div>
            
            <?php if (isset($_POST['password'])): ?>
                <div class="alert alert-danger">❌ Yanlış şifre! Şifre: <strong>kurulum2024</strong></div>
            <?php endif; ?>
            
            <form method="POST">
                <div class="form-group">
                    <label>Kurulum Şifresi:</label>
                    <input type="password" name="password" required placeholder="kurulum2024">
                </div>
                <button type="submit" class="btn">🔓 Giriş Yap</button>
            </form>
        </div>
    </body>
    </html>
    <?php
}

function showMainPage() {
    ?>
    <!DOCTYPE html>
    <html lang="tr">
    <head>
        <meta charset="UTF-8">
        <meta name="viewport" content="width=device-width, initial-scale=1.0">
        <title>Kurulum Paneli - Gökhan Aydınlı Gayrimenkul</title>
        <style>
            body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
            .container { max-width: 800px; margin: 0 auto; background: white; padding: 30px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); }
            .header { text-align: center; margin-bottom: 30px; }
            .logo { font-size: 28px; font-weight: bold; color: #2c3e50; margin-bottom: 10px; }
            .subtitle { color: #7f8c8d; font-size: 16px; }
            .step { margin: 20px 0; padding: 20px; border: 2px solid #ecf0f1; border-radius: 8px; }
            .step h3 { margin-top: 0; color: #2c3e50; }
            .btn { padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; margin: 5px; }
            .btn:hover { background: #2980b9; }
            .btn-success { background: #27ae60; }
            .btn-success:hover { background: #229954; }
            .btn-warning { background: #f39c12; }
            .btn-warning:hover { background: #e67e22; }
            .btn-danger { background: #e74c3c; }
            .btn-danger:hover { background: #c0392b; }
            .alert { padding: 15px; margin: 15px 0; border-radius: 5px; }
            .alert-info { background: #d1ecf1; border: 1px solid #bee5eb; color: #0c5460; }
            .alert-success { background: #d4edda; border: 1px solid #c3e6cb; color: #155724; }
            .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; }
            .status { margin: 10px 0; padding: 10px; border-radius: 5px; }
            .status-ok { background: #d4edda; color: #155724; }
            .status-error { background: #f8d7da; color: #721c24; }
            .code { background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; margin: 10px 0; }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="header">
                <div class="logo">🏠 Gökhan Aydınlı Gayrimenkul</div>
                <div class="subtitle">Kurulum Paneli</div>
            </div>
            
            <div class="alert alert-info">
                <strong>📋 Kurulum Adımları:</strong><br>
                1. Veritabanı bilgilerini ayarlayın<br>
                2. Veritabanını kurun<br>
                3. Bağlantıyı test edin<br>
                4. Kurulumu tamamlayın
            </div>
            
            <!-- Adım 1: Sistem Kontrolü -->
            <div class="step">
                <h3>🔧 1. Sistem Kontrolü</h3>
                <?php
                $php_version = phpversion();
                $mysql_available = extension_loaded('mysqli');
                $writable_dirs = [];
                
                $check_dirs = ['images/', 'images/properties/', 'images/blog/', 'dashboard/uploads/'];
                foreach ($check_dirs as $dir) {
                    $writable_dirs[$dir] = is_writable($dir) || mkdir($dir, 0755, true);
                }
                ?>
                
                <div class="status <?php echo version_compare($php_version, '7.4', '>=') ? 'status-ok' : 'status-error'; ?>">
                    PHP Sürümü: <?php echo $php_version; ?> <?php echo version_compare($php_version, '7.4', '>=') ? '✅' : '❌'; ?>
                </div>
                
                <div class="status <?php echo $mysql_available ? 'status-ok' : 'status-error'; ?>">
                    MySQL Desteği: <?php echo $mysql_available ? 'Mevcut ✅' : 'Mevcut Değil ❌'; ?>
                </div>
                
                <?php foreach ($writable_dirs as $dir => $writable): ?>
                <div class="status <?php echo $writable ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $dir; ?> klasörü: <?php echo $writable ? 'Yazılabilir ✅' : 'Yazılamaz ❌'; ?>
                </div>
                <?php endforeach; ?>
            </div>
            
            <!-- Adım 2: Veritabanı Ayarları -->
            <div class="step">
                <h3>🗄️ 2. Veritabanı Ayarları</h3>
                <p>Mevcut veritabanı ayarları:</p>
                
                <?php
                if (file_exists('db.php')) {
                    include 'db.php';
                    echo "<div class='code'>";
                    echo "Host: {$servername}<br>";
                    echo "Kullanıcı: {$username}<br>";
                    echo "Veritabanı: {$dbname}<br>";
                    echo "Şifre: " . (empty($password) ? 'Boş' : '***') . "<br>";
                    echo "</div>";
                } else {
                    echo "<div class='status status-error'>❌ db.php dosyası bulunamadı!</div>";
                }
                ?>
                
                <p><strong>Not:</strong> Veritabanı bilgilerini değiştirmek için <code>db.php</code> dosyasını düzenleyin.</p>
            </div>
            
            <!-- Adım 3: Veritabanı Kurulumu -->
            <div class="step">
                <h3>📊 3. Veritabanı Kurulumu</h3>
                <p>SQL dosyasını kullanarak veritabanını kurun.</p>
                <button onclick="setupDatabase()" class="btn btn-success">🚀 Veritabanını Kur</button>
                <button onclick="testConnection()" class="btn btn-warning">🔍 Bağlantıyı Test Et</button>
                <div id="dbSetupResult"></div>
            </div>
            
            <!-- Adım 4: Kurulum Tamamlama -->
            <div class="step">
                <h3>✅ 4. Kurulum Tamamlama</h3>
                <p>Veritabanı kurulumu tamamlandıktan sonra bu adımı çalıştırın.</p>
                <a href="?step=complete" class="btn btn-success">🎉 Kurulumu Tamamla</a>
                <a href="?step=cleanup" class="btn btn-danger">🗑️ Bu Dosyayı Sil</a>
            </div>
            
            <!-- Güvenlik Uyarısı -->
            <div class="alert alert-warning">
                <strong>⚠️ ÖNEMLİ GÜVENLİK UYARISI:</strong><br>
                Kurulum tamamlandıktan sonra bu dosyayı (install.php) mutlaka silin!<br>
                Bu dosya sistemde kalırsa güvenlik riski oluşturur.
            </div>
            
            <div style="text-align: center; margin-top: 30px;">
                <a href="?logout=1" class="btn btn-danger">🚪 Çıkış Yap</a>
            </div>
        </div>
        
        <script>
        function setupDatabase() {
            const resultDiv = document.getElementById('dbSetupResult');
            resultDiv.innerHTML = '<div class="alert alert-info">📊 Veritabanı kuruluyor, lütfen bekleyin...</div>';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=setup_database'
            })
            .then(response => response.json())
            .then(data => {
                const statusClass = data.success ? 'alert-success' : 'alert-danger';
                const icon = data.success ? '✅' : '❌';
                
                let detailsHtml = '';
                if (data.details) {
                    detailsHtml = `
                        <div style="margin-top: 10px; font-size: 14px;">
                            <strong>Detaylar:</strong><br>
                            Başarılı işlem: ${data.details.success_count}<br>
                            Hata sayısı: ${data.details.error_count}<br>
                            Tablo sayısı: ${data.details.table_count || 'Bilinmiyor'}
                        </div>
                    `;
                    
                    if (data.details.errors && data.details.errors.length > 0) {
                        detailsHtml += `<div style="margin-top: 10px;"><strong>Hatalar:</strong><br>${data.details.errors.join('<br>')}</div>`;
                    }
                }
                
                resultDiv.innerHTML = `<div class="${statusClass}">${icon} ${data.message}${detailsHtml}</div>`;
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert-danger">❌ İstek hatası: ' + error.message + '</div>';
            });
        }
        
        function testConnection() {
            const resultDiv = document.getElementById('dbSetupResult');
            resultDiv.innerHTML = '<div class="alert alert-info">🔍 Bağlantı test ediliyor...</div>';
            
            fetch(window.location.href, {
                method: 'POST',
                headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
                body: 'action=test_connection'
            })
            .then(response => response.json())
            .then(data => {
                const statusClass = data.success ? 'alert-success' : 'alert-danger';
                const icon = data.success ? '✅' : '❌';
                
                let detailsHtml = '';
                if (data.tables) {
                    detailsHtml = `<div style="margin-top: 10px;"><strong>Bulunan tablolar (${data.table_count}):</strong><br>${data.tables.slice(0, 10).join(', ')}</div>`;
                }
                
                resultDiv.innerHTML = `<div class="${statusClass}">${icon} ${data.message}${detailsHtml}</div>`;
            })
            .catch(error => {
                resultDiv.innerHTML = '<div class="alert-danger">❌ İstek hatası: ' + error.message + '</div>';
            });
        }
        </script>
    </body>
    </html>
    <?php
}

function setupDatabase() {
    try {
        // db.php dosyasını include et
        if (!file_exists('db.php')) {
            throw new Exception('db.php dosyası bulunamadı!');
        }
        
        include 'db.php';
        
        // SQL dosyasını oku
        $sql_file = 'sql/gokhanaydinli_db_complete.sql';
        if (!file_exists($sql_file)) {
            throw new Exception('SQL dosyası bulunamadı: ' . $sql_file);
        }
        
        $sql_content = file_get_contents($sql_file);
        
        // Veritabanını oluştur
        $create_db_sql = "CREATE DATABASE IF NOT EXISTS {$dbname} CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci";
        $conn_temp = new mysqli($servername, $username, $password);
        if ($conn_temp->connect_error) {
            throw new Exception("MySQL bağlantısı başarısız: " . $conn_temp->connect_error);
        }
        
        if (!$conn_temp->query($create_db_sql)) {
            throw new Exception("Veritabanı oluşturulamadı: " . $conn_temp->error);
        }
        
        // Veritabanını seç
        $conn_temp->select_db($dbname);
        
        // SQL komutlarını ayır ve çalıştır
        $statements = [];
        $temp_statement = '';
        $lines = explode("\n", $sql_content);
        
        foreach ($lines as $line) {
            $line = trim($line);
            
            // Boş satırları ve yorumları atla
            if (empty($line) || strpos($line, '--') === 0) {
                continue;
            }
            
            $temp_statement .= $line . ' ';
            
            // Statement'i bitiren ';' karakterini bul
            if (substr($line, -1) === ';') {
                $statements[] = trim($temp_statement);
                $temp_statement = '';
            }
        }
        
        $success_count = 0;
        $error_count = 0;
        $errors = [];
        
        foreach ($statements as $statement) {
            if (empty($statement)) continue;
            
            if ($conn_temp->query($statement)) {
                $success_count++;
            } else {
                $error_count++;
                $errors[] = $conn_temp->error;
                // İlk 5 hatayı logla
                if (count($errors) <= 5) {
                    error_log("SQL Error: " . $conn_temp->error . " - Statement: " . substr($statement, 0, 100));
                }
            }
        }
        
        $conn_temp->close();
        
        // Başarılı kurulum sonrası bağlantı testi
        $test_conn = new mysqli($servername, $username, $password, $dbname);
        if ($test_conn->connect_error) {
            throw new Exception("Kurulum sonrası bağlantı testi başarısız!");
        }
        
        // Tabloları say
        $table_result = $test_conn->query("SHOW TABLES");
        $table_count = $table_result->num_rows;
        $test_conn->close();
        
        echo json_encode([
            'success' => true,
            'message' => "Veritabanı başarıyla kuruldu! $success_count işlem başarılı, $error_count hata. $table_count tablo oluşturuldu.",
            'details' => [
                'success_count' => $success_count,
                'error_count' => $error_count,
                'table_count' => $table_count,
                'errors' => array_slice($errors, 0, 5) // İlk 5 hatayı göster
            ]
        ]);
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
}

function testConnection() {
    try {
        if (!file_exists('db.php')) {
            throw new Exception('db.php dosyası bulunamadı!');
        }
        
        include 'db.php';
        
        if ($conn && $conn->ping()) {
            // Tabloları kontrol et
            $tables = [];
            $result = $conn->query("SHOW TABLES");
            while ($row = $result->fetch_array()) {
                $tables[] = $row[0];
            }
            
            echo json_encode([
                'success' => true,
                'message' => 'Veritabanı bağlantısı başarılı!',
                'tables' => $tables,
                'table_count' => count($tables)
            ]);
        } else {
            throw new Exception('Veritabanı bağlantısı başarısız!');
        }
        
    } catch (Exception $e) {
        echo json_encode([
            'success' => false,
            'message' => 'Hata: ' . $e->getMessage()
        ]);
    }
}

function completeSetup() {
    try {
        // Son kontroller
        include 'db.php';
        
        $admin_check = $conn->query("SELECT COUNT(*) as count FROM users WHERE role = 'admin'");
        $admin_count = $admin_check->fetch_assoc()['count'];
        
        $properties_check = $conn->query("SELECT COUNT(*) as count FROM properties");
        $properties_count = $properties_check->fetch_assoc()['count'];
        
        $blogs_check = $conn->query("SELECT COUNT(*) as count FROM blogs");
        $blogs_count = $blogs_check->fetch_assoc()['count'];
        
        // Başarı sayfası göster
        ?>
        <!DOCTYPE html>
        <html lang="tr">
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <title>Kurulum Tamamlandı!</title>
            <style>
                body { font-family: Arial, sans-serif; background: #f5f5f5; margin: 0; padding: 20px; }
                .container { max-width: 600px; margin: 50px auto; background: white; padding: 40px; border-radius: 10px; box-shadow: 0 0 20px rgba(0,0,0,0.1); text-align: center; }
                .success-icon { font-size: 64px; margin-bottom: 20px; }
                .title { font-size: 28px; color: #27ae60; margin-bottom: 20px; }
                .info { background: #f8f9fa; padding: 20px; border-radius: 5px; margin: 20px 0; text-align: left; }
                .btn { padding: 12px 24px; background: #3498db; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; text-decoration: none; display: inline-block; margin: 10px; }
                .btn-success { background: #27ae60; }
                .btn-danger { background: #e74c3c; }
                .alert-warning { background: #fff3cd; border: 1px solid #ffeaa7; color: #856404; padding: 15px; border-radius: 5px; margin: 20px 0; }
            </style>
        </head>
        <body>
            <div class="container">
                <div class="success-icon">🎉</div>
                <div class="title">Kurulum Başarıyla Tamamlandı!</div>
                
                <div class="info">
                    <h4>📊 Kurulum Özeti:</h4>
                    <ul>
                        <li><strong>Admin Kullanıcı:</strong> <?php echo $admin_count; ?> adet</li>
                        <li><strong>Demo İlanlar:</strong> <?php echo $properties_count; ?> adet</li>
                        <li><strong>Blog Yazıları:</strong> <?php echo $blogs_count; ?> adet</li>
                        <li><strong>Veritabanı:</strong> Başarıyla kuruldu</li>
                    </ul>
                </div>
                
                <div class="info">
                    <h4>🔑 Giriş Bilgileri:</h4>
                    <ul>
                        <li><strong>Admin Email:</strong> admin@gokhanaydinli.com</li>
                        <li><strong>Admin Şifre:</strong> admin123</li>
                        <li><strong>Admin Panel:</strong> <a href="dashboard/">dashboard/</a></li>
                    </ul>
                </div>
                
                <div class="alert-warning">
                    <strong>⚠️ ÖNEMLİ:</strong><br>
                    Güvenlik için bu kurulum dosyasını (install.php) hemen silin!
                </div>
                
                <a href="index.php" class="btn btn-success">🏠 Ana Sayfaya Git</a>
                <a href="dashboard/" class="btn btn-success">🎛️ Admin Paneli</a>
                <a href="?cleanup=1" class="btn btn-danger">🗑️ Kurulum Dosyasını Sil</a>
            </div>
        </body>
        </html>
        <?php
        
    } catch (Exception $e) {
        echo "Hata: " . $e->getMessage();
    }
}

// Çıkış işlemi
if (isset($_GET['logout'])) {
    session_destroy();
    header('Location: ' . $_SERVER['PHP_SELF']);
    exit;
}

// Cleanup işlemi
if (isset($_GET['cleanup'])) {
    if (unlink(__FILE__)) {
        echo "Kurulum dosyası başarıyla silindi! Artık sitenizi güvenle kullanabilirsiniz.";
    } else {
        echo "Kurulum dosyası silinemedi. Lütfen manuel olarak silin.";
    }
    echo '<br><a href="index.php">Ana Sayfaya Git</a>';
}
?>
