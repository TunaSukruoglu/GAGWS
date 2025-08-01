<?php
// Database Transfer Script
// Local'den ana sunucuya veri aktarımı

session_start();

// Güvenlik kontrolü
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? $_SESSION['user_role'] ?? '') !== 'admin') {
    die('Yetkisiz erişim!');
}

// Ana sunucu database bilgileri
$remote_host = 'localhost';  // Ana sunucuda genellikle localhost kullanılır
$remote_user = 'gokhanay_user';  // Ana sunucu MySQL kullanıcı adı
$remote_password = '113041122839sS?!_';  // Ana sunucu şifresi
$remote_database = 'gokhanay_db';  // Ana sunucu database adı

// Alternatif host adresleri (test için)
$alternative_hosts = [
    'localhost',
    '127.0.0.1',
    'cp24.hosting.sh.com.tr',
    'mysql.cp24.hosting.sh.com.tr'
];

// Database bilgilerini kontrol et
$connection_info = [
    'host' => $remote_host,
    'user' => $remote_user, 
    'database' => $remote_database,
    'password_length' => strlen($remote_password)
];

// Local database
include 'db.php';
$local_conn = $conn;

$message = '';
$message_type = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $action = $_POST['action'] ?? '';
    
    try {
        // Ana sunucu bağlantısı
        $remote_conn = new mysqli($remote_host, $remote_user, $remote_password, $remote_database);
        if ($remote_conn->connect_error) {
            throw new Exception("Ana sunucu bağlantı hatası: " . $remote_conn->connect_error);
        }
        
        switch ($action) {
            case 'test_hosts':
                // Tüm host adreslerini test et
                $test_results = [];
                
                foreach ($alternative_hosts as $host) {
                    try {
                        $test_conn = new mysqli($host, $remote_user, $remote_password, $remote_database);
                        if ($test_conn->connect_error) {
                            $test_results[$host] = "❌ Hata: " . $test_conn->connect_error;
                        } else {
                            $version_result = $test_conn->query("SELECT VERSION() as version");
                            $version = $version_result->fetch_assoc()['version'];
                            $test_results[$host] = "✅ Başarılı! MySQL: $version";
                            $test_conn->close();
                        }
                    } catch (Exception $e) {
                        $test_results[$host] = "❌ Exception: " . $e->getMessage();
                    }
                }
                
                $message = "<strong>Host Test Sonuçları:</strong><br>";
                foreach ($test_results as $host => $result) {
                    $message .= "<br>🔗 <strong>$host:</strong> $result";
                }
                $message_type = 'info';
                break;
                
            case 'test_connection':
                // Ana sunucu bağlantısını test et
                $remote_conn = new mysqli($remote_host, $remote_user, $remote_password, $remote_database);
                if ($remote_conn->connect_error) {
                    throw new Exception("Ana sunucu bağlantı hatası: " . $remote_conn->connect_error);
                }
                
                // Bağlantı başarılı, temel bilgileri al
                $version_result = $remote_conn->query("SELECT VERSION() as version");
                $version = $version_result->fetch_assoc()['version'];
                
                $tables_result = $remote_conn->query("SHOW TABLES");
                $table_count = $tables_result->num_rows;
                
                $remote_conn->close();
                
                $message = "✅ Ana sunucu bağlantısı başarılı!<br>";
                $message .= "📊 MySQL Versiyon: $version<br>";
                $message .= "📋 Tablo Sayısı: $table_count<br>";
                $message .= "🏠 Host: $remote_host<br>";
                $message .= "👤 Kullanıcı: $remote_user";
                $message_type = 'success';
                break;
                
            case 'transfer_blogs':
                // Blog verilerini transfer et
                
                // 1. Ana sunucudaki blog verilerini temizle
                $remote_conn->query("DELETE FROM blog_tag_relations");
                $remote_conn->query("DELETE FROM blog_category_relations");
                $remote_conn->query("DELETE FROM blogs");
                $remote_conn->query("DELETE FROM blog_categories");
                $remote_conn->query("DELETE FROM blog_tags");
                
                // 2. Local'den verileri al ve aktar
                $tables = ['blog_categories', 'blog_tags', 'blogs', 'blog_category_relations', 'blog_tag_relations'];
                
                foreach ($tables as $table) {
                    $result = $local_conn->query("SELECT * FROM `$table`");
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $columns = array_keys($row);
                            $values = array_values($row);
                            
                            // NULL değerleri düzelt
                            $values = array_map(function($value) use ($remote_conn) {
                                return $value === null ? 'NULL' : "'" . $remote_conn->real_escape_string($value) . "'";
                            }, $values);
                            
                            $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                            $remote_conn->query($sql);
                        }
                    }
                }
                
                $message = "✅ Blog verileri başarıyla ana sunucuya aktarıldı!";
                $message_type = 'success';
                break;
                
            case 'transfer_properties':
                // Emlak verilerini transfer et
                
                // Ana sunucudaki emlak verilerini temizle
                $remote_conn->query("DELETE FROM favorites");
                $remote_conn->query("DELETE FROM property_images");
                $remote_conn->query("DELETE FROM properties");
                
                // Local'den verileri aktar
                $tables = ['properties', 'property_images', 'favorites'];
                
                foreach ($tables as $table) {
                    $result = $local_conn->query("SELECT * FROM `$table`");
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $columns = array_keys($row);
                            $values = array_values($row);
                            
                            $values = array_map(function($value) use ($remote_conn) {
                                return $value === null ? 'NULL' : "'" . $remote_conn->real_escape_string($value) . "'";
                            }, $values);
                            
                            $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                            $remote_conn->query($sql);
                        }
                    }
                }
                
                $message = "✅ Emlak verileri başarıyla ana sunucuya aktarıldı!";
                $message_type = 'success';
                break;
                
            case 'transfer_all':
                // Tüm verileri transfer et
                
                // Tablo listesi
                $tables = ['blog_categories', 'blog_tags', 'blogs', 'blog_category_relations', 'blog_tag_relations', 
                          'properties', 'property_images', 'favorites', 'users'];
                
                foreach ($tables as $table) {
                    // Ana sunucuda temizle
                    $remote_conn->query("DELETE FROM `$table`");
                    
                    // Local'den aktar
                    $result = $local_conn->query("SELECT * FROM `$table`");
                    if ($result && $result->num_rows > 0) {
                        while ($row = $result->fetch_assoc()) {
                            $columns = array_keys($row);
                            $values = array_values($row);
                            
                            $values = array_map(function($value) use ($remote_conn) {
                                return $value === null ? 'NULL' : "'" . $remote_conn->real_escape_string($value) . "'";
                            }, $values);
                            
                            $sql = "INSERT INTO `$table` (`" . implode('`, `', $columns) . "`) VALUES (" . implode(', ', $values) . ")";
                            $remote_conn->query($sql);
                        }
                    }
                }
                
                $message = "✅ Tüm veriler başarıyla ana sunucuya aktarıldı!";
                $message_type = 'success';
                break;
        }
        
        $remote_conn->close();
        
    } catch (Exception $e) {
        $message = "❌ Hata: " . $e->getMessage();
        $message_type = 'danger';
    }
}

// CSRF token
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Transfer - Local to Remote</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h3><i class="fas fa-exchange-alt me-2"></i>Database Transfer</h3>
                        <p class="mb-0">Local → Ana Sunucu</p>
                    </div>
                    
                    <div class="card-body">
                        <!-- Alert -->
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Connection Test -->
                        <div class="text-center mb-4">
                            <h5>Ana Sunucu Bağlantı Testi</h5>
                            <div class="btn-group" role="group">
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="test_hosts">
                                    <button type="submit" class="btn btn-outline-info">
                                        <i class="fas fa-search me-1"></i>Host Adreslerini Test Et
                                    </button>
                                </form>
                                
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="test_connection">
                                    <button type="submit" class="btn btn-outline-primary">
                                        <i class="fas fa-plug me-1"></i>Mevcut Host'u Test Et
                                    </button>
                                </form>
                            </div>
                            <small class="d-block text-muted mt-2">
                                Host: <?= $remote_host ?> | User: <?= $remote_user ?> | DB: <?= $remote_database ?>
                            </small>
                        </div>
                        
                        <hr>
                        
                        <!-- Transfer Options -->
                        <div class="row">
                            <div class="col-md-6">
                                <h5>Blog Sistemi Transfer</h5>
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="transfer_blogs">
                                    <button type="submit" class="btn btn-warning w-100" 
                                            onclick="return confirm('Blog verileri ana sunucuya aktarılacak. Emin misiniz?')">
                                        <i class="fas fa-blog me-1"></i>Blog Verilerini Aktar
                                    </button>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h5>Emlak Sistemi Transfer</h5>
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="transfer_properties">
                                    <button type="submit" class="btn btn-success w-100" 
                                            onclick="return confirm('Emlak verileri ana sunucuya aktarılacak. Emin misiniz?')">
                                        <i class="fas fa-home me-1"></i>Emlak Verilerini Aktar
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Complete Transfer -->
                        <div class="text-center mt-4">
                            <h5>Tam Transfer</h5>
                            <form method="POST">
                                <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                <input type="hidden" name="action" value="transfer_all">
                                <button type="submit" class="btn btn-danger btn-lg" 
                                        onclick="return confirm('TÜM VERİLER ana sunucuya aktarılacak! Ana sunucudaki veriler silinecek! Emin misiniz?')">
                                    <i class="fas fa-database me-2"></i>Tüm Verileri Aktar
                                </button>
                            </form>
                        </div>
                        
                        <!-- Info -->
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Önemli Bilgiler:</h6>
                            <ul class="mb-0">
                                <li>Transfer işlemi ana sunucudaki mevcut verileri siler</li>
                                <li>Local database'inizden ana sunucuya veri kopyalar</li>
                                <li>İşlem öncesi ana sunucu verilerini yedeklemeniz önerilir</li>
                                <li>Büyük veriler için işlem biraz zaman alabilir</li>
                            </ul>
                        </div>
                        
                        <div class="text-center">
                            <a href="database-reset-v2.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Database Reset'e Dön
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
