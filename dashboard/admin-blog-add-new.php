<?php
session_start();

// Session reset işlemi
if (isset($_GET['reset_session'])) {
    session_destroy();
    session_start();
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    header('Location: admin-blog-add-new.php?session_reset=success');
    exit;
}

error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database connection
try {
    include '../db.php';
    if (!isset($conn) || !$conn) {
        throw new Exception("Database connection failed");
    }
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());
    die("Database connection error. Please check your database configuration.");
}

// Admin kontrolü - Daha esnek kontrol
if (!isset($_SESSION['user_id'])) {
    // Session debug bilgisi ekle
    error_log("Admin access denied - No user_id in session. Session data: " . print_r($_SESSION, true));
    
    // Session başlatıp tekrar kontrol et
    session_regenerate_id(true);
    
    if (!isset($_SESSION['user_id'])) {
        header('Location: ../login.php?error=session_expired');
        exit();
    }
}

// Role kontrolü daha esnek - user_role ve role anahtarlarını kontrol et
$user_role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
if ($user_role !== 'admin' && $user_role !== 'editor') {
    error_log("Admin access denied - Role: '$user_role', User ID: " . ($_SESSION['user_id'] ?? 'not set'));
    
    // Session'da user_role varsa role'a kopyala
    if (isset($_SESSION['user_role']) && !isset($_SESSION['role'])) {
        $_SESSION['role'] = $_SESSION['user_role'];
        $user_role = $_SESSION['role'];
        error_log("Role copied from user_role to role: " . $user_role);
    }
    
    // Hâlâ admin değilse yönlendir
    if ($user_role !== 'admin' && $user_role !== 'editor') {
        header('Location: ../login.php?error=admin_required');
        exit();
    }
}

// CSRF Token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    error_log("CSRF token created for user: " . $_SESSION['user_id']);
}

// Sayfa bilgileri
$current_page = 'blog-add';
$page_title = 'Yeni Blog Yazısı Ekle - Admin Dashboard';
$user_name = $_SESSION['user_name'] ?? 'Admin';

// Blog tabloları oluştur
try {
    // Blogs tablosu
    $create_blog_table = "CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT,
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(500),
        status ENUM('draft', 'published', 'scheduled') DEFAULT 'draft',
        reading_time INT DEFAULT 5,
        meta_title VARCHAR(60),
        meta_description VARCHAR(160),
        featured BOOLEAN DEFAULT FALSE,
        publish_date DATETIME NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog kategorileri tablosu
    $create_categories_table = "CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog etiketleri tablosu
    $create_tags_table = "CREATE TABLE IF NOT EXISTS blog_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        slug VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog-kategori ilişki tablosu
    $create_blog_categories_table = "CREATE TABLE IF NOT EXISTS blog_category_relations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        category_id INT NOT NULL,
        FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE,
        UNIQUE KEY unique_blog_category (blog_id, category_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog-etiket ilişki tablosu
    $create_blog_tags_table = "CREATE TABLE IF NOT EXISTS blog_tag_relations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        tag_id INT NOT NULL,
        FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE,
        UNIQUE KEY unique_blog_tag (blog_id, tag_id)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Tabloları oluştur
    $conn->query($create_blog_table);
    $conn->query($create_categories_table);
    $conn->query($create_tags_table);
    $conn->query($create_blog_categories_table);
    $conn->query($create_blog_tags_table);

    // Varsayılan kategorileri ekle
    $default_categories = [
        ['name' => 'Gayrimenkul Yatırımı', 'slug' => 'gayrimenkul-yatirim'],
        ['name' => 'Ev Satın Alma', 'slug' => 'ev-satin-alma'],
        ['name' => 'Piyasa Analizi', 'slug' => 'piyasa-analizi'],
        ['name' => 'Hukuki Süreçler', 'slug' => 'hukuki-surecler'],
        ['name' => 'Kiralama', 'slug' => 'kiralama'],
        ['name' => 'İstanbul Rehberi', 'slug' => 'istanbul-rehberi']
    ];

    foreach ($default_categories as $category) {
        $conn->query("INSERT IGNORE INTO blog_categories (name, slug) VALUES ('{$category['name']}', '{$category['slug']}')");
    }

    // Varsayılan etiketleri ekle
    $default_tags = [
        'İstanbul', 'Yatırım', 'Konut', 'Ofis', 'Dükkan', 'Kiralama', 
        'Satış', 'Emlak', 'Fiyat', 'Analiz', 'Trend', 'Hukuk'
    ];

    foreach ($default_tags as $tag) {
        $slug = strtolower(str_replace(['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'], ['c', 'g', 'i', 'o', 's', 'u'], $tag));
        $conn->query("INSERT IGNORE INTO blog_tags (name, slug) VALUES ('$tag', '$slug')");
    }

} catch (Exception $e) {
    error_log("Blog table creation error: " . $e->getMessage());
}

$user_id = $_SESSION['user_id'];

// Form gönderimi işleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_blog'])) {
    try {
        // CSRF token kontrolü
        if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
            throw new Exception("Geçersiz istek. Sayfa yenilenecek.");
        }
        
        $title = trim($_POST['title']);
        $content = $_POST['content'];
        $excerpt = trim($_POST['excerpt']);
        $status = $_POST['status'];
        $reading_time = (int)($_POST['reading_time'] ?? 5);
        $meta_title = trim($_POST['meta_title'] ?? '');
        $meta_description = trim($_POST['meta_description'] ?? '');
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Temel validasyon
        if (empty($title)) {
            throw new Exception("Başlık alanı zorunludur.");
        }
        
        if (empty($content) || trim(strip_tags($content)) == '') {
            throw new Exception("İçerik alanı zorunludur.");
        }
        
        // Slug oluştur
        $slug = createSlug($title);
        
        // Aynı slug varsa benzersiz yap
        $slug_check = $conn->prepare("SELECT id FROM blogs WHERE slug = ?");
        $slug_check->bind_param("s", $slug);
        $slug_check->execute();
        if ($slug_check->get_result()->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Demo resim URL'si
        $demo_image = 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=800&h=400&fit=crop';
        
        // Publish date belirle
        $publish_date = null;
        if ($status == 'published') {
            $publish_date = date('Y-m-d H:i:s');
        } elseif ($status == 'scheduled' && !empty($_POST['publish_date'])) {
            $publish_date = $_POST['publish_date'];
        }
        
        // Blog kaydet
        $insert_blog = $conn->prepare("
            INSERT INTO blogs (title, slug, excerpt, content, featured_image, status, reading_time, 
                              meta_title, meta_description, featured, publish_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)
        ");
        
        $insert_blog->bind_param("ssssssissis", 
            $title, $slug, $excerpt, $content, $demo_image, 
            $status, $reading_time, $meta_title, $meta_description, $featured, $publish_date
        );
        
        if ($insert_blog->execute()) {
            $blog_id = $conn->insert_id;
            
            // Kategorileri kaydet
            if (!empty($_POST['category'])) {
                $category_stmt = $conn->prepare("INSERT INTO blog_category_relations (blog_id, category_id) VALUES (?, ?)");
                $category_stmt->bind_param("ii", $blog_id, $_POST['category']);
                $category_stmt->execute();
            }
            
            // Etiketleri kaydet
            if (!empty($_POST['tags'])) {
                $tag_stmt = $conn->prepare("INSERT INTO blog_tag_relations (blog_id, tag_id) VALUES (?, ?)");
                foreach ($_POST['tags'] as $tag_id) {
                    $tag_stmt->bind_param("ii", $blog_id, $tag_id);
                    $tag_stmt->execute();
                }
            }
            
            $success_message = "Blog yazısı başarıyla eklendi! (ID: $blog_id)";
            
            if ($status == 'published') {
                $success_message .= " Yazınız yayınlandı.";
            } else {
                $success_message .= " Yazınız taslak olarak kaydedildi.";
            }
        } else {
            throw new Exception("Blog kaydedilirken hata oluştu: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $error_message = "Hata: " . $e->getMessage();
        error_log("Blog add error: " . $e->getMessage());
        
        // Eğer CSRF hatası ise, session yenile
        if (strpos($e->getMessage(), 'Geçersiz istek') !== false) {
            $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
            $error_message .= " <br><small>Session yenilendi, lütfen tekrar deneyin.</small>";
        }
    }
}

// Kategorileri al
$categories = [];
try {
    $categories_result = $conn->query("SELECT * FROM blog_categories ORDER BY name");
    if ($categories_result) {
        while ($cat = $categories_result->fetch_assoc()) {
            $categories[] = $cat;
        }
    }
} catch (Exception $e) {
    error_log("Categories fetch error: " . $e->getMessage());
}

// Etiketleri al
$tags = [];
try {
    $tags_result = $conn->query("SELECT * FROM blog_tags ORDER BY name");
    if ($tags_result) {
        while ($tag = $tags_result->fetch_assoc()) {
            $tags[] = $tag;
        }
    }
} catch (Exception $e) {
    error_log("Tags fetch error: " . $e->getMessage());
}

// Slug oluşturma fonksiyonu
function createSlug($text) {
    $text = strtolower($text);
    $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü'];
    $english = ['c', 'g', 'i', 'o', 's', 'u'];
    $text = str_replace($turkish, $english, $text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    return trim($text, '-');
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <!-- Dashboard Style - Check if exists -->
    <?php if (file_exists('../assets/dashboard-style.css')): ?>
        <link href="../assets/dashboard-style.css" rel="stylesheet">
    <?php else: ?>
        <!-- Fallback minimal styles -->
        <style>
            .sidebar { background: #2c3e50; min-height: 100vh; }
            .main-content { padding: 20px; }
        </style>
    <?php endif; ?>
    
    <!-- Quill.js için -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        .main-content {
            background: #f8f9fa;
        }
        .content-card {
            background: white;
            border-radius: 10px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            padding: 30px;
            margin-bottom: 30px;
        }
        .form-label {
            font-weight: 600;
            color: #333;
            margin-bottom: 8px;
        }
        .btn-primary {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            padding: 12px 30px;
            border-radius: 8px;
            font-weight: 600;
        }
        .alert {
            border-radius: 8px;
            border: none;
        }
        #editor {
            height: 300px;
        }
        .form-check-input:checked {
            background-color: #667eea;
            border-color: #667eea;
        }
        .select2-container .select2-selection--multiple {
            min-height: 42px;
            border: 1px solid #ddd;
            border-radius: 6px;
        }
    </style>
</head>
<body class="bg-light">
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php 
            try {
                if (file_exists('includes/sidebar-admin.php')) {
                    include 'includes/sidebar-admin.php'; 
                } else {
                    // Minimal sidebar fallback
                    echo '<div class="col-md-3">
                        <div class="bg-dark text-white p-3" style="min-height: 100vh;">
                            <h5>Admin Menü</h5>
                            <a href="dashboard-admin.php" class="text-white d-block mb-2">Dashboard</a>
                            <a href="admin-blog-add-new.php" class="text-white d-block mb-2">Yeni Blog</a>
                        </div>
                    </div>';
                }
            } catch (Exception $e) {
                echo '<div class="col-md-3"><div class="alert alert-warning">Sidebar hatası: ' . $e->getMessage() . '</div></div>';
            }
            ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus-circle me-2"></i>Yeni Blog Yazısı Ekle</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="database-reset.php" class="btn btn-danger btn-sm" title="Database Reset">
                                <i class="fas fa-database me-1"></i>DB Reset
                            </a>
                            <a href="session-debug.php" class="btn btn-info btn-sm" target="_blank">
                                <i class="fas fa-bug me-1"></i>Session Debug
                            </a>
                            <a href="?reset_session=1" class="btn btn-warning btn-sm">
                                <i class="fas fa-refresh me-1"></i>Session Reset
                            </a>
                            <a href="dashboard-admin.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Geri Dön
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
                <?php if (isset($_GET['session_reset']) && $_GET['session_reset'] == 'success'): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-refresh me-2"></i>
                        Session başarıyla yenilendi. Artık işlem yapabilirsiniz.
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error_message) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Debug info artık gizlendi - production hazır -->
                <?php if (isset($_GET['debug']) && $_GET['debug'] == '1'): ?>
                <div class="alert alert-warning border-0 shadow-sm">
                    <h5><i class="fas fa-bug me-2"></i>Debug Bilgileri</h5>
                    <div class="row">
                        <div class="col-md-6">
                            <strong>📊 Sayfa Durumu:</strong><br>
                            ✅ Sayfa yüklendi<br>
                            📁 Categories: <?= count($categories) ?> adet<br>
                            🏷️ Tags: <?= count($tags) ?> adet<br>
                            👤 User: <?= htmlspecialchars($user_name) ?> (ID: <?= $_SESSION['user_id'] ?? 'YOK' ?>)<br>
                            🔐 Role: <?= $_SESSION['role'] ?? $_SESSION['user_role'] ?? 'YOK' ?><br>
                            🔒 Session: <?= session_status() == PHP_SESSION_ACTIVE ? 'Aktif' : 'İnaktif' ?><br>
                        </div>
                        <div class="col-md-6">
                            🗂️ Sidebar: <?= file_exists('includes/sidebar-admin.php') ? 'Mevcut' : 'Bulunamadı' ?><br>
                            🎨 CSS: <?= file_exists('../assets/dashboard-style.css') ? 'Mevcut' : 'Bulunamadı' ?><br>
                            🛡️ CSRF Token: <?= isset($_SESSION['csrf_token']) ? 'Oluşturuldu (' . substr($_SESSION['csrf_token'], 0, 10) . '...)' : 'Eksik' ?><br>
                            💾 Session ID: <?= substr(session_id(), 0, 15) ?>...<br>
                            🕒 Time: <?= date('H:i:s') ?><br>
                        </div>
                    </div>
                    <hr>
                    <div class="btn-group" role="group">
                        <a href="simple-session-test.php" class="btn btn-sm btn-warning">
                            <i class="fas fa-vial me-1"></i>Simple Test
                        </a>
                        <a href="session-debug.php" class="btn btn-sm btn-info">
                            <i class="fas fa-search me-1"></i>Session Debug
                        </a>
                        <a href="test-blog-admin.php" class="btn btn-sm btn-secondary">
                            <i class="fas fa-flask me-1"></i>Advanced Test
                        </a>
                        <a href="?reset_session=1" class="btn btn-sm btn-danger">
                            <i class="fas fa-refresh me-1"></i>Reset Session
                        </a>
                    </div>
                </div>
                <?php endif; ?>

                <!-- Blog Ekleme Formu -->
                <div class="content-card">
                    <form method="POST" action="">
                        <!-- CSRF Token -->
                        <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                        
                        <div class="row">
                            <div class="col-lg-8">
                                <!-- Başlık -->
                                <div class="mb-4">
                                    <label for="title" class="form-label">
                                        <i class="fas fa-heading me-2"></i>Başlık *
                                    </label>
                                    <input type="text" 
                                           class="form-control form-control-lg" 
                                           id="title" 
                                           name="title" 
                                           placeholder="Blog yazısının başlığını girin..." 
                                           required
                                           value="<?= htmlspecialchars($_POST['title'] ?? '') ?>">
                                </div>

                                <!-- Özet -->
                                <div class="mb-4">
                                    <label for="excerpt" class="form-label">
                                        <i class="fas fa-align-left me-2"></i>Özet
                                    </label>
                                    <textarea class="form-control" 
                                              id="excerpt" 
                                              name="excerpt" 
                                              rows="3" 
                                              placeholder="Blog yazısının kısa özetini girin..."><?= htmlspecialchars($_POST['excerpt'] ?? '') ?></textarea>
                                </div>

                                <!-- İçerik -->
                                <div class="mb-4">
                                    <label for="content" class="form-label">
                                        <i class="fas fa-edit me-2"></i>İçerik *
                                    </label>
                                    <div id="editor"><?= htmlspecialchars($_POST['content'] ?? '') ?></div>
                                    <input type="hidden" id="content" name="content">
                                </div>

                                <!-- SEO Bilgileri -->
                                <div class="card">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-search me-2"></i>SEO Ayarları</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="meta_title" class="form-label">Meta Başlık</label>
                                            <input type="text" 
                                                   class="form-control" 
                                                   id="meta_title" 
                                                   name="meta_title" 
                                                   placeholder="SEO için özel başlık (boş bırakılırsa blog başlığı kullanılır)"
                                                   value="<?= htmlspecialchars($_POST['meta_title'] ?? '') ?>">
                                        </div>
                                        <div class="mb-3">
                                            <label for="meta_description" class="form-label">Meta Açıklama</label>
                                            <textarea class="form-control" 
                                                      id="meta_description" 
                                                      name="meta_description" 
                                                      rows="3" 
                                                      placeholder="Arama motorları için açıklama"><?= htmlspecialchars($_POST['meta_description'] ?? '') ?></textarea>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Sidebar -->
                            <div class="col-lg-4">
                                <!-- Yayınlama Ayarları -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-cog me-2"></i>Yayınlama Ayarları</h5>
                                    </div>
                                    <div class="card-body">
                                        <div class="mb-3">
                                            <label for="status" class="form-label">Durum</label>
                                            <select class="form-select" id="status" name="status">
                                                <option value="draft" <?= ($_POST['status'] ?? '') == 'draft' ? 'selected' : '' ?>>Taslak</option>
                                                <option value="published" <?= ($_POST['status'] ?? '') == 'published' ? 'selected' : '' ?>>Yayınlandı</option>
                                                <option value="scheduled" <?= ($_POST['status'] ?? '') == 'scheduled' ? 'selected' : '' ?>>Zamanlanmış</option>
                                            </select>
                                        </div>
                                        
                                        <div class="mb-3" id="publish_date_group" style="display: none;">
                                            <label for="publish_date" class="form-label">Yayın Tarihi</label>
                                            <input type="datetime-local" 
                                                   class="form-control" 
                                                   id="publish_date" 
                                                   name="publish_date"
                                                   value="<?= $_POST['publish_date'] ?? '' ?>">
                                        </div>

                                        <div class="mb-3">
                                            <label for="reading_time" class="form-label">Okuma Süresi (dakika)</label>
                                            <input type="number" 
                                                   class="form-control" 
                                                   id="reading_time" 
                                                   name="reading_time" 
                                                   min="1" 
                                                   max="60" 
                                                   value="<?= $_POST['reading_time'] ?? '5' ?>">
                                        </div>

                                        <div class="form-check">
                                            <input class="form-check-input" 
                                                   type="checkbox" 
                                                   id="featured" 
                                                   name="featured" 
                                                   value="1"
                                                   <?= isset($_POST['featured']) ? 'checked' : '' ?>>
                                            <label class="form-check-label" for="featured">
                                                <i class="fas fa-star me-1"></i>Öne Çıkan Yazı
                                            </label>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kategori -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-folder me-2"></i>Kategori</h5>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" name="category">
                                            <option value="">Kategori Seçin</option>
                                            <?php foreach ($categories as $category): ?>
                                                <option value="<?= $category['id'] ?>" 
                                                        <?= ($_POST['category'] ?? '') == $category['id'] ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($category['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                    </div>
                                </div>

                                <!-- Etiketler -->
                                <div class="card mb-4">
                                    <div class="card-header">
                                        <h5 class="mb-0"><i class="fas fa-tags me-2"></i>Etiketler</h5>
                                    </div>
                                    <div class="card-body">
                                        <select class="form-select" name="tags[]" multiple id="tags-select">
                                            <?php foreach ($tags as $tag): ?>
                                                <option value="<?= $tag['id'] ?>"
                                                        <?= in_array($tag['id'], $_POST['tags'] ?? []) ? 'selected' : '' ?>>
                                                    <?= htmlspecialchars($tag['name']) ?>
                                                </option>
                                            <?php endforeach; ?>
                                        </select>
                                        <small class="text-muted">Ctrl tuşuna basarak birden fazla etiket seçebilirsiniz.</small>
                                    </div>
                                </div>

                                <!-- Kaydet Butonu -->
                                <div class="d-grid">
                                    <button type="submit" name="add_blog" class="btn btn-primary btn-lg">
                                        <i class="fas fa-save me-2"></i>Blog Yazısını Kaydet
                                    </button>
                                </div>
                            </div>
                        </div>
                    </form>
                </div>
            </main>
        </div>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <!-- Quill.js -->
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <script>
        // Quill editör başlat
        document.addEventListener('DOMContentLoaded', function() {
            try {
                var quill = new Quill('#editor', {
                    theme: 'snow',
                    modules: {
                        toolbar: [
                            ['bold', 'italic', 'underline', 'strike'],
                            ['blockquote', 'code-block'],
                            [{ 'header': 1 }, { 'header': 2 }],
                            [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                            [{ 'script': 'sub'}, { 'script': 'super' }],
                            [{ 'indent': '-1'}, { 'indent': '+1' }],
                            [{ 'direction': 'rtl' }],
                            [{ 'size': ['small', false, 'large', 'huge'] }],
                            [{ 'header': [1, 2, 3, 4, 5, 6, false] }],
                            [{ 'color': [] }, { 'background': [] }],
                            [{ 'font': [] }],
                            [{ 'align': [] }],
                            ['clean'],
                            ['link', 'image']
                        ]
                    }
                });

                // Form gönderilmeden önce Quill içeriğini hidden input'a aktar
                document.querySelector('form').addEventListener('submit', function() {
                    document.querySelector('#content').value = quill.root.innerHTML;
                });
            } catch (error) {
                console.error('Quill editor error:', error);
                // Fallback: normal textarea göster
                document.getElementById('editor').innerHTML = '<textarea name="content" class="form-control" rows="10" placeholder="İçeriği buraya yazın..."></textarea>';
            }

            // Status değiştiğinde publish date göster/gizle
            var statusElement = document.getElementById('status');
            if (statusElement) {
                statusElement.addEventListener('change', function() {
                    var publishDateGroup = document.getElementById('publish_date_group');
                    if (publishDateGroup) {
                        if (this.value === 'scheduled') {
                            publishDateGroup.style.display = 'block';
                        } else {
                            publishDateGroup.style.display = 'none';
                        }
                    }
                });

                // Sayfa yüklendiğinde durumu kontrol et
                var status = statusElement.value;
                if (status === 'scheduled') {
                    var publishDateGroup = document.getElementById('publish_date_group');
                    if (publishDateGroup) {
                        publishDateGroup.style.display = 'block';
                    }
                }
            }
        });
    </script>
</body>
</html>
