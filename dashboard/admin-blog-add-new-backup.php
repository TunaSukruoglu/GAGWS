<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db.php';

// Admin kontrolü - güvenli hale getirildi
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User bilgilerini al
$user_query = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Sayfa ayarları
$current_page = 'admin-blog-add';
$page_title = 'Yeni Blog Yazısı Ekle - Admin Dashboard';
$user_name = $user_data['name']; // Sidebar için

// Blog sistem tabloları oluştur
try {
    // Gelişmiş blog tablosu oluştur
    $create_blog_table = "
    CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        slug VARCHAR(255) UNIQUE NOT NULL,
        excerpt TEXT NOT NULL,
        content LONGTEXT NOT NULL,
        featured_image VARCHAR(500) DEFAULT NULL,
        author VARCHAR(100) DEFAULT 'Gökhan Aydınlı',
        status ENUM('draft','published','scheduled') DEFAULT 'draft',
        reading_time INT DEFAULT 5,
        meta_title VARCHAR(255) DEFAULT NULL,
        meta_description TEXT DEFAULT NULL,
        meta_keywords TEXT DEFAULT NULL,
        views INT DEFAULT 0,
        featured BOOLEAN DEFAULT FALSE,
        publish_date DATETIME DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        KEY idx_slug (slug),
        KEY idx_status (status),
        KEY idx_publish_date (publish_date)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog kategorileri tablosu
    $create_categories_table = "
    CREATE TABLE IF NOT EXISTS blog_categories (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        slug VARCHAR(100) UNIQUE NOT NULL,
        description TEXT DEFAULT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog etiketleri tablosu
    $create_tags_table = "
    CREATE TABLE IF NOT EXISTS blog_tags (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(50) NOT NULL,
        slug VARCHAR(50) UNIQUE NOT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog kategori ilişki tablosu
    $create_blog_categories_table = "
    CREATE TABLE IF NOT EXISTS blog_category_relations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        category_id INT NOT NULL,
        UNIQUE KEY unique_blog_category (blog_id, category_id),
        FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
        FOREIGN KEY (category_id) REFERENCES blog_categories(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";

    // Blog etiket ilişki tablosu
    $create_blog_tags_table = "
    CREATE TABLE IF NOT EXISTS blog_tag_relations (
        id INT AUTO_INCREMENT PRIMARY KEY,
        blog_id INT NOT NULL,
        tag_id INT NOT NULL,
        UNIQUE KEY unique_blog_tag (blog_id, tag_id),
        FOREIGN KEY (blog_id) REFERENCES blogs(id) ON DELETE CASCADE,
        FOREIGN KEY (tag_id) REFERENCES blog_tags(id) ON DELETE CASCADE
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
    }
}

// Kategorileri al
$categories_result = $conn->query("SELECT * FROM blog_categories ORDER BY name");
$categories = [];
if ($categories_result) {
    while ($cat = $categories_result->fetch_assoc()) {
        $categories[] = $cat;
    }
}

// Etiketleri al
$tags_result = $conn->query("SELECT * FROM blog_tags ORDER BY name");
$tags = [];
if ($tags_result) {
    while ($tag = $tags_result->fetch_assoc()) {
        $tags[] = $tag;
    }
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

// Blog dosyası oluşturma fonksiyonu (blog1.php formatında)
function createBlogFile($blog_id, $conn) {
    $blog_query = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    $blog_query->bind_param("i", $blog_id);
    $blog_query->execute();
    $blog = $blog_query->get_result()->fetch_assoc();
    
    if ($blog) {
        // blog{id}.php dosyası oluştur
        $filename = "../blog{$blog_id}.php";
        $template = generateBlogTemplate($blog);
        file_put_contents($filename, $template);
    }
}

// Blog template oluşturma (blog1.php stilinde)
function generateBlogTemplate($blog) {
    $formatted_date = date('d M Y', strtotime($blog['publish_date']));
    
    $template = '<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION[\'user_id\']);
$userName = $isLoggedIn ? $_SESSION[\'user_name\'] ?? \'Kullanıcı\' : \'\';

// Sayfanın en başında
include \'includes/session-check.php\';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="' . htmlspecialchars($blog['meta_title'] ?? $blog['title']) . '">
    <meta name="description" content="' . htmlspecialchars($blog['meta_description'] ?? $blog['excerpt']) . '">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="' . htmlspecialchars($blog['title']) . '">
    <meta property="og:description" content="' . htmlspecialchars($blog['excerpt']) . '">
    <meta property="og:image" content="' . htmlspecialchars($blog['featured_image']) . '">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <title>' . htmlspecialchars($blog['title']) . ' | Gökhan Aydınlı Gayrimenkul</title>
    
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Blog1.php stili -->
    <style>
        .blog-details-one {
            padding: 120px 0;
        }
        .blog-meta-wrapper {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .blog-title h1 {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 20px;
        }
        .post-date {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
            padding: 10px 0;
            border-bottom: 2px solid #e5e7eb;
        }
        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin: 30px 0;
        }
        .blog-content {
            font-size: 18px;
            line-height: 1.8;
            color: #374151;
        }
        .blog-content h5 {
            color: #1f2937;
            font-weight: 700;
            margin: 40px 0 20px 0;
            font-size: 1.5rem;
        }
        .blog-content h6 {
            color: #374151;
            font-weight: 600;
            margin: 30px 0 15px 0;
            font-size: 1.2rem;
        }
        .blog-content p {
            margin-bottom: 20px;
        }
        .blog-content ul, .blog-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }
        .blog-content li {
            margin-bottom: 10px;
        }
        .img-meta {
            margin: 40px 0;
            text-align: center;
        }
        .img-meta img {
            border-radius: 15px;
            max-width: 100%;
            height: auto;
        }
        .img-caption {
            font-style: italic;
            color: #6b7280;
            font-size: 14px;
            margin-top: 10px;
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Loading Transition -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
            </div>
        </div>

        <!-- Navigation menü buraya gelecek - blog1.php\'den kopyalanacak -->
        
        <!-- Blog Details -->
        <div class="blog-details-one pt-180 lg-pt-150 pb-150 xl-pb-120">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-meta-wrapper pe-xxl-5">
                            <div class="blog-title">
                                <h1>' . htmlspecialchars($blog['title']) . '</h1>
                            </div>
                            <div class="post-date">
                                <strong>Gökhan Aydınlı</strong> • ' . $formatted_date . ' • ' . $blog['reading_time'] . ' dk okuma
                            </div>
                            
                            <img src="' . htmlspecialchars($blog['featured_image']) . '" 
                                 alt="' . htmlspecialchars($blog['title']) . '" 
                                 class="featured-image">
                            
                            <div class="blog-content">
                                ' . $blog['content'] . '
                            </div>
                        </article>
                    </div>
                    
                    <!-- Sidebar buraya gelecek -->
                    <div class="col-lg-4">
                        <div class="blog-sidebar ps-xl-5 md-mt-60">
                            <!-- Sidebar içeriği -->
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer buraya gelecek -->
    </div>

    <!-- Scripts -->
    <script src="vendor/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="js/theme.js"></script>
</body>
</html>';

    return $template;
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
    <link href="../assets/dashboard-style.css" rel="stylesheet">
    
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
            <?php include 'sidebar-admin.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 main-content">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2"><i class="fas fa-plus-circle me-2"></i>Yeni Blog Yazısı Ekle</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <div class="btn-group me-2">
                            <a href="dashboard-admin.php" class="btn btn-outline-secondary btn-sm">
                                <i class="fas fa-arrow-left me-1"></i>Geri Dön
                            </a>
                        </div>
                    </div>
                </div>

                <!-- Alert Messages -->
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

                <!-- Blog Ekleme Formu -->
                <div class="content-card">
                    <form method="POST" action="">
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

        // Status değiştiğinde publish date göster/gizle
        document.getElementById('status').addEventListener('change', function() {
            var publishDateGroup = document.getElementById('publish_date_group');
            if (this.value === 'scheduled') {
                publishDateGroup.style.display = 'block';
            } else {
                publishDateGroup.style.display = 'none';
            }
        });

        // Sayfa yüklendiğinde durumu kontrol et
        document.addEventListener('DOMContentLoaded', function() {
            var status = document.getElementById('status').value;
            if (status === 'scheduled') {
                document.getElementById('publish_date_group').style.display = 'block';
            }
        });
    </script>
</body>
</html>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Quill Editor -->
    <link href="https://cdn.quilljs.com/1.3.6/quill.snow.css" rel="stylesheet">
    
    <style>
        :root {
            --primary-color: #0D1A1C;
            --secondary-color: #0d6efd;
            --accent-color: #FF6B35;
            --light-bg: #F8F9FA;
            --border-radius: 20px;
            --box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            background-color: var(--light-bg);
            color: #333;
        }

        .main-page-wrapper {
            display: flex;
            min-height: 100vh;
        }

        /* Dashboard Body */
        .dashboard-body {
            margin-left: 280px;
            flex: 1;
            padding: 0;
            background: var(--light-bg);
        }

        /* Card Styling */
        .card-box {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .dash-title-three {
            font-size: 20px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        /* Form Styling */
        .dash-input-wrapper {
            margin-bottom: 25px;
        }

        .dash-input-wrapper label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .dash-input-wrapper input,
        .dash-input-wrapper textarea,
        .dash-input-wrapper select {
            width: 100%;
            padding: 15px 20px;
            border: 1px solid #E6E6E6;
            border-radius: 12px;
            font-size: 15px;
            transition: all 0.3s ease;
            background: white;
        }

        .dash-input-wrapper input:focus,
        .dash-input-wrapper textarea:focus,
        .dash-input-wrapper select:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .dash-input-wrapper textarea {
            min-height: 120px;
            resize: vertical;
        }

        .dash-input-wrapper .form-text {
            font-size: 12px;
            color: #666;
            margin-top: 5px;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .required::after {
            content: '*';
            color: var(--accent-color);
            margin-left: 5px;
        }

        /* Quill Editor Styling */
        .editor-wrapper {
            border: 1px solid #E6E6E6;
            border-radius: 12px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .editor-wrapper:focus-within {
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
        }

        .ql-toolbar {
            border: none !important;
            border-bottom: 1px solid #E6E6E6 !important;
            background: #f8f9fa;
        }

        .ql-container {
            border: none !important;
        }

        .ql-editor {
            min-height: 300px;
            font-size: 15px;
            line-height: 1.6;
        }

        /* Buttons */
        .dash-btn-two {
            background: #0d6efd;
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
        }

        .dash-btn-two:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }

        .btn-outline {
            background: transparent;
            border: 2px solid #0d6efd;
            color: #0d6efd;
            padding: 13px 30px;
            border-radius: 12px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-outline:hover {
            background: #0d6efd;
            color: white;
            transform: translateY(-2px);
        }

        .btn-secondary {
            background: #6c757d;
            color: white;
            padding: 15px 30px;
            border-radius: 12px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            text-decoration: none;
        }

        .btn-secondary:hover {
            background: #545b62;
            color: white;
            transform: translateY(-2px);
        }

        /* Alert Styles */
        .alert {
            border: none;
            border-radius: 15px;
            padding: 15px 20px;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .alert-success {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            border-left: 4px solid #0d6efd;
        }

        .alert-danger {
            background: rgba(255, 107, 53, 0.1);
            color: var(--accent-color);
            border-left: 4px solid var(--accent-color);
        }

        /* Form Actions */
        .form-actions {
            display: flex;
            gap: 15px;
            padding-top: 30px;
            border-top: 1px solid #E6E6E6;
            margin-top: 30px;
            flex-wrap: wrap;
        }

        /* Responsive */
        @media (max-width: 1199px) {
            .dashboard-body {
                margin-left: 0;
            }
        }

        @media (max-width: 767px) {
            .card-box {
                padding: 20px;
                margin: 0 15px 30px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Dashboard Aside Menu -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <!-- Include Header -->
                <?php include 'includes/header.php'; ?>

                <div class="px-4">
                    <h2 class="main-title d-block d-lg-none">Yeni Blog Yazısı Ekle</h2>

                    <!-- Success/Error Messages -->
                    <?php if (isset($success_message)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Başarılı!</strong><br>
                                <?= $success_message ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error_message)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Hata!</strong><br>
                                <?= $error_message ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- Form Card -->
                    <div class="card-box">
                        <h4 class="dash-title-three">
                            <i class="fas fa-plus-circle"></i>
                            Yeni Blog Yazısı Ekle
                        </h4>
                        
                        <form method="POST" id="blogForm" enctype="multipart/form-data">
                            <div class="row">
                                <div class="col-md-8">
                                    <!-- Başlık -->
                                    <div class="dash-input-wrapper">
                                        <label for="title" class="required">Yazı Başlığı</label>
                                        <input type="text" id="title" name="title" required 
                                               placeholder="Blog yazısının başlığını girin...">
                                        <div class="form-text">
                                            <i class="fas fa-lightbulb"></i>
                                            SEO dostu, dikkat çekici bir başlık seçin
                                        </div>
                                    </div>

                                    <!-- Özet -->
                                    <div class="dash-input-wrapper">
                                        <label for="excerpt">Kısa Özet</label>
                                        <textarea id="excerpt" name="excerpt" rows="3" 
                                                  placeholder="Yazınızın kısa bir özetini girin..."></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-info-circle"></i>
                                            Bu özet, liste sayfalarında görünecek
                                        </div>
                                    </div>

                                    <!-- İçerik -->
                                    <div class="dash-input-wrapper">
                                        <label for="content" class="required">Yazı İçeriği</label>
                                        <div class="editor-wrapper">
                                            <div id="editor"></div>
                                        </div>
                                        <textarea id="content" name="content" style="display: none;" required></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-magic"></i>
                                            Zengin metin editörü ile profesyonel içerik oluşturun
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Kategori -->
                                    <div class="dash-input-wrapper">
                                        <label for="category">Kategori</label>
                                        <select id="category" name="category">
                                            <?php while ($cat = $categories->fetch_assoc()): ?>
                                                <option value="<?= $cat['id'] ?>"><?= $cat['name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                    </div>

                                    <!-- Etiketler -->
                                    <div class="dash-input-wrapper">
                                        <label for="tags">Etiketler</label>
                                        <select id="tags" name="tags[]" multiple>
                                            <?php while ($tag = $tags->fetch_assoc()): ?>
                                                <option value="<?= $tag['id'] ?>"><?= $tag['name'] ?></option>
                                            <?php endwhile; ?>
                                        </select>
                                        <div class="form-text">
                                            <i class="fas fa-hashtag"></i>
                                            Ctrl+Click ile birden fazla etiket seçebilirsiniz
                                        </div>
                                    </div>

                                    <!-- Yayın Durumu -->
                                    <div class="dash-input-wrapper">
                                        <label for="status">Yayın Durumu</label>
                                        <select id="status" name="status">
                                            <option value="draft">Taslak Olarak Kaydet</option>
                                            <option value="published">Hemen Yayınla</option>
                                        </select>
                                    </div>

                                    <!-- Okuma Süresi -->
                                    <div class="dash-input-wrapper">
                                        <label for="reading_time">Okuma Süresi (dakika)</label>
                                        <input type="number" id="reading_time" name="reading_time" value="5" min="1" max="60">
                                        <div class="form-text">
                                            <i class="fas fa-clock"></i>
                                            Tahmini okuma süresi
                                        </div>
                                    </div>

                                    <!-- Meta Title -->
                                    <div class="dash-input-wrapper">
                                        <label for="meta_title">SEO Başlık</label>
                                        <input type="text" id="meta_title" name="meta_title" 
                                               placeholder="SEO için özel başlık (opsiyonel)">
                                        <div class="form-text">
                                            <i class="fas fa-search"></i>
                                            Google'da görünecek başlık
                                        </div>
                                    </div>

                                    <!-- Meta Description -->
                                    <div class="dash-input-wrapper">
                                        <label for="meta_description">SEO Açıklama</label>
                                        <textarea id="meta_description" name="meta_description" rows="2" 
                                                  placeholder="Google'da görünecek açıklama"></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-search"></i>
                                            Maksimum 160 karakter
                                        </div>
                                    </div>

                                    <!-- Öne Çıkan Yazı -->
                                    <div class="dash-input-wrapper">
                                        <div class="form-check">
                                            <input class="form-check-input" type="checkbox" id="featured" name="featured">
                                            <label class="form-check-label" for="featured">
                                                <strong>Öne Çıkan Yazı</strong><br>
                                                <small class="text-muted">Bu yazı ana sayfada öne çıkarılacak</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Form Actions -->
                            <div class="form-actions">
                                <button type="submit" name="add_blog" class="dash-btn-two" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    <span>Yazıyı Kaydet</span>
                                </button>
                                
                                <a href="admin-blog.php" class="btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Geri Dön</span>
                                </a>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.quilljs.com/1.3.6/quill.min.js"></script>
    
    <script>
        // Quill Editor
        var quill = new Quill('#editor', {
            theme: 'snow',
            placeholder: 'Yazınızı buraya yazmaya başlayın...',
            modules: {
                toolbar: [
                    [{ 'header': [1, 2, 3, false] }],
                    ['bold', 'italic', 'underline', 'strike'],
                    ['blockquote', 'code-block'],
                    [{ 'list': 'ordered'}, { 'list': 'bullet' }],
                    [{ 'script': 'sub'}, { 'script': 'super' }],
                    [{ 'indent': '-1'}, { 'indent': '+1' }],
                    [{ 'color': [] }, { 'background': [] }],
                    [{ 'align': [] }],
                    ['link', 'image'],
                    ['clean']
                ]
            }
        });

        // Form submit işlemi
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            // Quill içeriğini hidden textarea'ya aktar
            document.getElementById('content').value = quill.root.innerHTML;
            
            // Validasyon
            const title = document.getElementById('title').value.trim();
            const content = quill.getText().trim();
            
            if (!title) {
                e.preventDefault();
                alert('Başlık alanı zorunludur!');
                return false;
            }
            
            if (!content || content.length === 0) {
                e.preventDefault();
                alert('İçerik alanı zorunludur!');
                return false;
            }
            
            // Submit button'ı loading durumuna al
            const submitBtn = document.getElementById('submitBtn');
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Kaydediliyor...</span>';
            submitBtn.disabled = true;
        });

        // Auto-suggest meta fields from title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value;
            if (title && !document.getElementById('meta_title').value) {
                document.getElementById('meta_title').value = title;
            }
        });

        // Character count for meta description
        document.getElementById('meta_description').addEventListener('input', function() {
            const maxLength = 160;
            const currentLength = this.value.length;
            
            if (!document.getElementById('metaCounter')) {
                const counter = document.createElement('div');
                counter.id = 'metaCounter';
                counter.className = 'form-text';
                counter.style.textAlign = 'right';
                this.parentNode.appendChild(counter);
            }
            
            const counter = document.getElementById('metaCounter');
            counter.innerHTML = `${currentLength}/${maxLength} karakter`;
            counter.style.color = currentLength > maxLength ? '#dc3545' : '#6c757d';
        });

        // Alert auto dismiss
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);
    </script>
</body>
</html>
