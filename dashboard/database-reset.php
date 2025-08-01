<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

// Güvenlik kontrolü - Sadece admin erişebilir
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? $_SESSION['user_role'] ?? '') !== 'admin') {
    header('Location: ../login.php?error=admin_required');
    exit();
}

// Database bağlantısı
try {
    include __DIR__ . '/../db.php';
    if (!isset($conn) || !$conn) {
        throw new Exception("Veritabanı bağlantısı kurulamadı");
    }
} catch (Exception $e) {
    die("Database Error: " . $e->getMessage());
}

$message = '';
$message_type = '';

// İşlem kontrolü
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF koruması
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $message = "Güvenlik hatası! Sayfa yeniden yüklenecek.";
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        try {
            switch ($action) {
                case 'reset_blogs':
                    // Blog verilerini sıfırla
                    $conn->query("DELETE FROM blog_tag_relations");
                    $conn->query("DELETE FROM blog_category_relations");
                    $conn->query("DELETE FROM blogs");
                    $conn->query("ALTER TABLE blogs AUTO_INCREMENT = 1");
                    
                    $message = "✅ Blog verileri başarıyla sıfırlandı!";
                    $message_type = 'success';
                    break;
                    
                case 'reset_categories':
                    // Kategori verilerini sıfırla
                    $conn->query("DELETE FROM blog_category_relations");
                    $conn->query("DELETE FROM blog_categories");
                    $conn->query("ALTER TABLE blog_categories AUTO_INCREMENT = 1");
                    
                    $message = "✅ Blog kategorileri başarıyla sıfırlandı!";
                    $message_type = 'success';
                    break;
                    
                case 'reset_tags':
                    // Etiket verilerini sıfırla
                    $conn->query("DELETE FROM blog_tag_relations");
                    $conn->query("DELETE FROM blog_tags");
                    $conn->query("ALTER TABLE blog_tags AUTO_INCREMENT = 1");
                    
                    $message = "✅ Blog etiketleri başarıyla sıfırlandı!";
                    $message_type = 'success';
                    break;
                    
                case 'reset_all_blog':
                    // Tüm blog sistemini sıfırla
                    $conn->query("DELETE FROM blog_tag_relations");
                    $conn->query("DELETE FROM blog_category_relations");
                    $conn->query("DELETE FROM blogs");
                    $conn->query("DELETE FROM blog_categories");
                    $conn->query("DELETE FROM blog_tags");
                    
                    $conn->query("ALTER TABLE blogs AUTO_INCREMENT = 1");
                    $conn->query("ALTER TABLE blog_categories AUTO_INCREMENT = 1");
                    $conn->query("ALTER TABLE blog_tags AUTO_INCREMENT = 1");
                    
                    $message = "✅ Tüm blog sistemi başarıyla sıfırlandı!";
                    $message_type = 'success';
                    break;
                    
                case 'drop_tables':
                    // Tabloları tamamen sil
                    $conn->query("DROP TABLE IF EXISTS blog_tag_relations");
                    $conn->query("DROP TABLE IF EXISTS blog_category_relations");
                    $conn->query("DROP TABLE IF EXISTS blogs");
                    $conn->query("DROP TABLE IF EXISTS blog_categories");
                    $conn->query("DROP TABLE IF EXISTS blog_tags");
                    
                    $message = "⚠️ Tüm blog tabloları silindi! Yeniden oluşturmak için blog sayfasını ziyaret edin.";
                    $message_type = 'warning';
                    break;
                    
                case 'reset_all_database':
                    // TÜM DATABASE'İ SIFIRLA (ÇOK TEHLİKELİ!)
                    if (isset($_POST['confirm_total_reset']) && $_POST['confirm_total_reset'] == 'EVET_HEPSINI_SIL') {
                        
                        // Önce tüm tabloları listele
                        $tables_result = $conn->query("SHOW TABLES");
                        $tables = [];
                        while ($row = $tables_result->fetch_array()) {
                            $tables[] = $row[0];
                        }
                        
                        // Foreign key kontrollerini kapat
                        $conn->query("SET FOREIGN_KEY_CHECKS = 0");
                        
                        // Tüm tabloları sil
                        foreach ($tables as $table) {
                            $conn->query("DROP TABLE IF EXISTS `$table`");
                        }
                        
                        // Foreign key kontrollerini aç
                        $conn->query("SET FOREIGN_KEY_CHECKS = 1");
                        
                        $message = "🔥 TÜM DATABASE SİLİNDİ! Tüm veriler kayboldu.";
                        $message_type = 'danger';
                    } else {
                        $message = "❌ Onay metni yanlış! İşlem iptal edildi.";
                        $message_type = 'warning';
                    }
                    break;
                    
                default:
                    $message = "❌ Geçersiz işlem!";
                    $message_type = 'danger';
            }
            
        } catch (Exception $e) {
            $message = "❌ Hata: " . $e->getMessage();
            $message_type = 'danger';
        }
    }
}

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Mevcut tablo durumunu kontrol et
$table_stats = [];
try {
    $tables = ['blogs', 'blog_categories', 'blog_tags', 'blog_category_relations', 'blog_tag_relations'];
    
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM `$table`");
        if ($result) {
            $row = $result->fetch_assoc();
            $table_stats[$table] = $row['count'];
        } else {
            $table_stats[$table] = 'Tablo yok';
        }
    }
} catch (Exception $e) {
    // Tablolar yoksa
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Database Reset - Admin Panel</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .container { padding: 20px 0; }
        .reset-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            overflow: hidden;
        }
        .card-header { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); 
            color: white; 
            padding: 20px;
        }
        .danger-zone { 
            border: 2px solid #dc3545; 
            border-radius: 10px; 
            background: #f8d7da; 
            padding: 20px; 
            margin: 20px 0;
        }
        .btn-danger { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); border: none; }
        .btn-warning { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); border: none; }
        .btn-success { background: linear-gradient(135deg, #28a745 0%, #20c997 100%); border: none; }
        .stats-card { background: #f8f9fa; border-radius: 10px; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-md-10">
                <div class="reset-card">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-database me-2"></i>Database Reset Panel</h2>
                        <p class="mb-0">⚠️ DİKKAT: Bu işlemler geri alınamaz!</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Geri Dön Butonu -->
                        <div class="text-center mb-4">
                            <a href="admin-blog-add-new.php" class="btn btn-secondary">
                                <i class="fas fa-arrow-left me-1"></i>Blog Admin'e Dön
                            </a>
                            <a href="dashboard-admin.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                        </div>
                        
                        <!-- Alert Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>
                        
                        <!-- Mevcut Durum -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4><i class="fas fa-chart-bar me-2"></i>Mevcut Database Durumu</h4>
                                <div class="row">
                                    <?php foreach ($table_stats as $table => $count): ?>
                                    <div class="col-md-4">
                                        <div class="stats-card text-center">
                                            <h6><?= ucfirst(str_replace(['_', 'blog'], [' ', 'Blog'], $table)) ?></h6>
                                            <h4 class="text-primary"><?= $count ?></h4>
                                        </div>
                                    </div>
                                    <?php endforeach; ?>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Güvenli Reset İşlemleri -->
                        <div class="row">
                            <div class="col-md-6">
                                <h4><i class="fas fa-broom me-2 text-warning"></i>Güvenli Reset İşlemleri</h4>
                                
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="reset_blogs">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h6>Blog Yazılarını Sıfırla</h6>
                                            <p class="small text-muted">Sadece blog yazılarını siler, kategoriler kalır</p>
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-trash-alt me-1"></i>Blog Yazılarını Sil
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="reset_categories">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h6>Kategorileri Sıfırla</h6>
                                            <p class="small text-muted">Blog kategorilerini siler</p>
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-folder me-1"></i>Kategorileri Sil
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="reset_tags">
                                    <div class="card border-warning">
                                        <div class="card-body">
                                            <h6>Etiketleri Sıfırla</h6>
                                            <p class="small text-muted">Blog etiketlerini siler</p>
                                            <button type="submit" class="btn btn-warning btn-sm w-100">
                                                <i class="fas fa-tags me-1"></i>Etiketleri Sil
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            
                            <div class="col-md-6">
                                <h4><i class="fas fa-exclamation-triangle me-2 text-danger"></i>Tehlikeli İşlemler</h4>
                                
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="reset_all_blog">
                                    <div class="card border-danger">
                                        <div class="card-body">
                                            <h6>Tüm Blog Sistemini Sıfırla</h6>
                                            <p class="small text-muted">Blog, kategori, etiket - hepsini siler</p>
                                            <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                    onclick="return confirm('Tüm blog sistemi silinecek! Emin misiniz?')">
                                                <i class="fas fa-bomb me-1"></i>Tüm Blog Sistemini Sil
                                            </button>
                                        </div>
                                    </div>
                                </form>
                                
                                <form method="POST" class="mb-3">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="drop_tables">
                                    <div class="card border-danger">
                                        <div class="card-body">
                                            <h6>Blog Tablolarını Sil</h6>
                                            <p class="small text-muted">Tabloları tamamen kaldırır</p>
                                            <button type="submit" class="btn btn-danger btn-sm w-100" 
                                                    onclick="return confirm('Tablolar tamamen silinecek! Emin misiniz?')">
                                                <i class="fas fa-table me-1"></i>Tabloları Sil
                                            </button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Çok Tehlikeli İşlem -->
                        <div class="danger-zone">
                            <h3 class="text-danger text-center mb-3">
                                <i class="fas fa-skull-crossbones me-2"></i>ULTRA TEHLİKELİ BÖLGE
                            </h3>
                            <div class="text-center">
                                <h5>TÜM DATABASE'İ SİL</h5>
                                <p class="text-danger">⚠️ Bu işlem TÜM VERİTABANINI siler! Kullanıcılar, ayarlar, her şey kaybolur!</p>
                                
                                <form method="POST" id="totalResetForm">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="reset_all_database">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Onay için <strong>"EVET_HEPSINI_SIL"</strong> yazın:</label>
                                        <input type="text" class="form-control text-center" name="confirm_total_reset" 
                                               placeholder="EVET_HEPSINI_SIL" required>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-danger btn-lg" 
                                            onclick="return confirm('SON UYARI: TÜM VERİTABANI SİLİNECEK! Emin misiniz?')">
                                        <i class="fas fa-nuclear me-2"></i>TÜM DATABASE'İ SİL
                                    </button>
                                </form>
                            </div>
                        </div>
                        
                        <!-- Bilgi -->
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Bilgi:</h6>
                            <ul class="mb-0">
                                <li>Güvenli reset işlemleri sadece seçilen bölümü etkiler</li>
                                <li>Tablolar silinse bile, blog sayfasını ziyaret ederek yeniden oluşturabilirsiniz</li>
                                <li>Total reset işlemi tüm sistemi sıfırlar ve geri alınamaz</li>
                                <li>İşlemlerden önce veritabanı yedeği almanız önerilir</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
