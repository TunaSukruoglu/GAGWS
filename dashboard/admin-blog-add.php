<?php
session_start();
include '../db.php';
include 'includes/unsplash-api.php';

// Hata raporlamayı aç
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Admin kontrolü - isAdmin fonksiyonunu kullan
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

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

$user_id = $_SESSION['user_id'];

// Form gönderimi işleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['add_blog'])) {
    try {
        $title = trim($_POST['title']);
        $content = $_POST['content'];
        $excerpt = trim($_POST['excerpt']);
        $status = $_POST['status'];
        $reading_time = (int)$_POST['reading_time'];
        $meta_title = trim($_POST['meta_title']);
        $meta_description = trim($_POST['meta_description']);
        $featured = isset($_POST['featured']) ? 1 : 0;
        
        // Slug oluştur
        $slug = createSlug($title);
        
        // Aynı slug varsa benzersiz yap
        $slug_check = $conn->prepare("SELECT id FROM blogs WHERE slug = ?");
        $slug_check->bind_param("s", $slug);
        $slug_check->execute();
        if ($slug_check->get_result()->num_rows > 0) {
            $slug = $slug . '-' . time();
        }
        
        // Otomatik resim al - Unsplash API
        $unsplash = new UnsplashAPI();
        $image_data = $unsplash->getDemoImage($title); // Demo için
        // Gerçek API için: $image_data = $unsplash->searchPhotoByTitle($title);
        
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
            $title, $slug, $excerpt, $content, $image_data['url'], 
            $status, $reading_time, $meta_title, $meta_description, $featured, $publish_date
        );
        
        if ($insert_blog->execute()) {
            $blog_id = $conn->insert_id;
            
            // Kategorileri kaydet
            if (!empty($_POST['categories'])) {
                $category_stmt = $conn->prepare("INSERT INTO blog_category_relations (blog_id, category_id) VALUES (?, ?)");
                foreach ($_POST['categories'] as $category_id) {
                    $category_stmt->bind_param("ii", $blog_id, $category_id);
                    $category_stmt->execute();
                }
            }
            
            // Etiketleri kaydet
            if (!empty($_POST['tags'])) {
                $tag_stmt = $conn->prepare("INSERT INTO blog_tag_relations (blog_id, tag_id) VALUES (?, ?)");
                foreach ($_POST['tags'] as $tag_id) {
                    $tag_stmt->bind_param("ii", $blog_id, $tag_id);
                    $tag_stmt->execute();
                }
            }
            
            $success_message = "Blog yazısı başarıyla eklendi! Resim otomatik olarak seçildi.";
            
            // Yayınlandıysa blog dosyası oluştur
            if ($status == 'published') {
                createBlogFile($blog_id, $conn);
                $success_message .= " Blog{$blog_id}.php dosyası oluşturuldu.";
            }
        } else {
            throw new Exception("Blog kaydedilirken hata oluştu.");
        }
        
    } catch (Exception $e) {
        $error_message = "Hata: " . $e->getMessage();
    }
}

// Kategorileri al
$categories = $conn->query("SELECT * FROM blog_categories ORDER BY name");

// Etiketleri al
$tags = $conn->query("SELECT * FROM blog_tags ORDER BY name");

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
            $target_path = $upload_dir . $new_filename;
            
            if (move_uploaded_file($_FILES['image_upload']['tmp_name'], $target_path)) {
                $final_image_path = 'images/blog/' . $new_filename;
            }
        }
    }
    // Base64 image from Unsplash or manual upload
    elseif (!empty($featured_image)) {
        // If it's a URL (Unsplash)
        if (filter_var($featured_image, FILTER_VALIDATE_URL)) {
            $final_image_path = $featured_image;
        }
        // If it's base64 data
        elseif (preg_match('/^data:image\/(\w+);base64,/', $featured_image)) {
            $upload_dir = '../images/blog/';
            
            if (!file_exists($upload_dir)) {
                mkdir($upload_dir, 0755, true);
            }
            
            // Extract base64 data
            $image_data = explode(',', $featured_image, 2)[1];
            $image_data = base64_decode($image_data);
            
            // Detect image type
            $finfo = new finfo(FILEINFO_MIME_TYPE);
            $mime_type = $finfo->buffer($image_data);
            
            $extension = '';
            switch ($mime_type) {
                case 'image/jpeg':
                    $extension = 'jpg';
                    break;
                case 'image/png':
                    $extension = 'png';
                    break;
                case 'image/webp':
                    $extension = 'webp';
                    break;
            }
            
            if ($extension) {
                $new_filename = 'blog_' . time() . '_' . rand(1000, 9999) . '.' . $extension;
                $target_path = $upload_dir . $new_filename;
                
                if (file_put_contents($target_path, $image_data)) {
                    $final_image_path = 'images/blog/' . $new_filename;
                }
            }
        }
    }
    
    // Debug: İşlenen veriler
    error_log("Processed data - Title: $title, Content length: " . strlen($content) . ", Image: $final_image_path");
    
    if (!empty($title) && !empty($content)) {
        try {
            // Slug oluştur
            $slug = strtolower(trim(preg_replace('/[^A-Za-z0-9-]+/', '-', $title)));
            $slug = preg_replace('/-+/', '-', $slug);
            $slug = trim($slug, '-');
            
            // Türkçe karakterleri dönüştür
            $turkish = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç');
            $english = array('s','s','i','i','i','g','g','u','u','o','o','c','c');
            $slug = str_replace($turkish, $english, $slug);
            
            // Slug benzersizliği kontrol et
            $slug_check = $conn->prepare("SELECT id FROM blog_posts WHERE slug = ?");
            if (!$slug_check) {
                throw new Exception("Slug kontrolü için hazırlık hatası: " . $conn->error);
            }
            
            $slug_check->bind_param("s", $slug);
            $slug_check->execute();
            if ($slug_check->get_result()->num_rows > 0) {
                $slug .= '-' . time();
            }
            
            $published_at = ($status === 'published') ? date('Y-m-d H:i:s') : null;
            
            $stmt = $conn->prepare("INSERT INTO blog_posts (title, slug, content, excerpt, featured_image, category, tags, status, featured, author_id, published_at) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)");
            if (!$stmt) {
                throw new Exception("Veritabanı hazırlık hatası: " . $conn->error);
            }
            
            $stmt->bind_param("ssssssssiis", $title, $slug, $content, $excerpt, $final_image_path, $category, $tags, $status, $featured, $user_id, $published_at);
            
            if ($stmt->execute()) {
                $success = "Blog yazısı başarıyla eklendi! (ID: " . $conn->insert_id . ")";
                if ($status === 'published') {
                    $success .= " Yazınız yayınlandı.";
                } else {
                    $success .= " Yazınız taslak olarak kaydedildi.";
                }
                error_log("Blog post saved successfully with ID: " . $conn->insert_id);
            } else {
                throw new Exception("Blog yazısı eklenirken hata oluştu: " . $stmt->error);
            }
            
        } catch (Exception $e) {
            $error = "Hata: " . $e->getMessage();
            error_log("Blog add error: " . $e->getMessage());
        }
    } else {
        $error = "Başlık ve içerik alanları zorunludur! (Title: '$title', Content length: " . strlen($content) . ")";
        error_log("Validation failed - Title: '$title', Content length: " . strlen($content));
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni Blog Yazısı Ekle - Admin Dashboard</title>
    
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

        /* Sidebar Styling - Beyaz Tema */
        .dash-aside-navbar {
            width: 280px;
            background: white;
            position: fixed;
            height: 100vh;
            left: 0;
            top: 0;
            z-index: 1000;
            overflow-y: auto;
            transition: all 0.3s ease;
            box-shadow: 2px 0 15px rgba(0,0,0,0.1);
            border-right: 1px solid #e9ecef;
        }

        .dash-aside-navbar::-webkit-scrollbar {
            width: 4px;
        }

        .dash-aside-navbar::-webkit-scrollbar-track {
            background: #f8f9fa;
        }

        .dash-aside-navbar::-webkit-scrollbar-thumb {
            background: #dee2e6;
            border-radius: 2px;
        }

        .logo {
            padding: 30px 20px;
            border-bottom: 1px solid #e9ecef;
        }

        .logo img {
            max-height: 80px;
            max-width: 220px;
            object-fit: contain;
        }

        .plr {
            padding-left: 20px;
            padding-right: 20px;
        }

        .bottom-line {
            border-bottom: 1px solid #e9ecef !important;
        }

        .pt-30 { padding-top: 30px; }
        .pb-30 { padding-bottom: 30px; }
        .pb-35 { padding-bottom: 35px; }
        .lg-pt-20 { padding-top: 20px; }
        .mb-40 { margin-bottom: 40px; }
        .lg-mb-30 { margin-bottom: 30px; }

        .dasboard-main-nav {
            padding: 20px 0;
        }

        .dasboard-main-nav ul {
            list-style: none;
            margin: 0;
            padding: 0;
        }

        .dasboard-main-nav li {
            margin: 3px 0;
        }

        .dasboard-main-nav a {
            display: flex;
            align-items: center;
            padding: 12px 20px;
            color: #6c757d;
            text-decoration: none;
            border-radius: 12px;
            margin: 0 20px;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 15px;
        }

        .dasboard-main-nav a:hover,
        .dasboard-main-nav a.active {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.15);
        }

        .dasboard-main-nav a.active {
            background: rgba(13, 110, 253, 0.15);
            border-left: 3px solid #0d6efd;
            font-weight: 600;
        }

        .dasboard-main-nav a.active i {
            color: #0d6efd !important;
        }

        .nav-title {
            color: #adb5bd;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
            font-weight: 600;
            padding: 20px;
            margin-top: 15px;
        }

        .profile-complete-status {
            padding: 20px;
            border-top: 1px solid #e9ecef;
            border-bottom: 1px solid #e9ecef;
            margin-top: 20px;
            background: #f8f9fa;
        }

        .progress-value {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 16px;
        }

        .progress-line {
            height: 8px;
            border-radius: 4px;
            background: #e9ecef;
            overflow: hidden;
            position: relative;
        }

        .inner-line {
            position: absolute;
            height: 100%;
            left: 0;
            top: 0;
            background: #0d6efd;
            border-radius: 4px;
            transition: width 0.3s ease;
        }

        .profile-complete-status p {
            color: #6c757d;
            margin-top: 8px;
            margin-bottom: 0;
            font-size: 14px;
        }

        .logout-btn {
            color: #6c757d !important;
            margin: 20px;
            padding: 12px 20px !important;
            border-radius: 12px;
            border: 1px solid #e9ecef;
            transition: all 0.3s ease;
            text-decoration: none;
            background: #f8f9fa;
        }

        .logout-btn:hover {
            background: rgba(255, 107, 53, 0.1);
            color: var(--accent-color) !important;
            border-color: rgba(255, 107, 53, 0.3);
            transform: translateX(5px);
        }

        .logout-btn .icon {
            width: 40px;
            height: 40px;
            background: rgba(255, 107, 53, 0.1);
            margin-right: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            border-radius: 50%;
        }

        .close-btn {
            display: none;
            background: none;
            border: none;
            color: #6c757d;
            font-size: 24px;
            padding: 10px;
        }

        /* Dashboard Body */
        .dashboard-body {
            margin-left: 280px;
            flex: 1;
            padding: 0;
            background: var(--light-bg);
        }

        /* Header */
        .dashboard-header {
            background: white;
            padding: 20px 30px;
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
            border-radius: 0 0 var(--border-radius) var(--border-radius);
        }

        .search-form {
            position: relative;
            max-width: 350px;
        }

        .search-form input {
            width: 100%;
            padding: 12px 50px 12px 20px;
            border: 1px solid #E6E6E6;
            border-radius: 50px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .search-form input:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 3px rgba(13, 110, 253, 0.1);
            background: white;
        }

        .search-form button {
            position: absolute;
            right: 5px;
            top: 50%;
            transform: translateY(-50%);
            background: #0d6efd;
            border: none;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            color: white;
            transition: all 0.3s ease;
        }

        .search-form button:hover {
            background: #0b5ed7;
            transform: translateY(-50%) scale(1.1);
        }

        .dash-mobile-nav-toggler {
            display: none;
            background: none;
            border: none;
            padding: 8px;
            cursor: pointer;
        }

        .dash-mobile-nav-toggler span {
            display: block;
            width: 25px;
            height: 3px;
            background: var(--primary-color);
            margin: 5px 0;
            border-radius: 2px;
            transition: all 0.3s ease;
        }

        /* Main Title */
        .main-title {
            font-size: 28px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 30px;
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

        /* Switch Styling */
        .form-switch-wrapper {
            background: rgba(13, 110, 253, 0.05);
            padding: 20px;
            border-radius: 12px;
            border: 1px solid rgba(13, 110, 253, 0.2);
            margin-bottom: 25px;
        }

        .form-check-input {
            width: 50px;
            height: 25px;
            border-radius: 25px;
        }

        .form-check-input:checked {
            background-color: #0d6efd;
            border-color: #0d6efd;
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

        /* Image Upload Styles */
        .image-upload-section {
            border: 2px dashed #ddd;
            border-radius: 12px;
            padding: 20px;
            text-align: center;
            margin-bottom: 15px;
            background: #f8f9fa;
        }

        .upload-option {
            margin-bottom: 20px;
            padding: 15px;
            background: white;
            border-radius: 8px;
            border: 1px solid #e0e0e0;
        }

        .upload-option h6 {
            margin-bottom: 10px;
            color: #333;
            font-weight: 600;
        }

        .btn-upload {
            background: #0d6efd;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 8px;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .btn-upload:hover {
            background: #0b5ed7;
            transform: translateY(-2px);
        }

        .image-preview-container {
            margin-top: 15px;
            text-align: center;
        }

        .unsplash-results {
            margin-top: 15px;
            max-height: 300px;
            overflow-y: auto;
        }

        .image-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
            gap: 10px;
            margin-top: 10px;
        }

        .unsplash-img {
            width: 100%;
            height: 80px;
            object-fit: cover;
            border-radius: 6px;
            cursor: pointer;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .unsplash-img:hover {
            transform: scale(1.05);
            border-color: #0d6efd;
        }

        .selected-img {
            border-color: #0d6efd !important;
            box-shadow: 0 0 10px rgba(13, 110, 253, 0.3);
        }

        /* Responsive */
        .dash-mobile-nav-toggler {
            display: none;
            background: none;
            border: none;
            padding: 10px;
        }

        .dash-mobile-nav-toggler span {
            display: block;
            width: 25px;
            height: 3px;
            background: var(--primary-color);
            margin: 5px 0;
            border-radius: 2px;
        }

        @media (max-width: 1199px) {
            .dash-aside-navbar {
                transform: translateX(-100%);
            }
            
            .dashboard-body {
                margin-left: 0;
            }
        }

        @media (max-width: 767px) {
            .dash-mobile-nav-toggler {
                display: block;
            }
            
            .dashboard-header {
                padding: 15px 20px;
            }
            
            .card-box {
                padding: 20px;
                margin: 0 15px 30px;
            }
            
            .form-actions {
                flex-direction: column;
            }
        }

        /* Breadcrumb */
        .breadcrumb-nav {
            margin-bottom: 20px;
            padding: 15px 0;
        }

        .breadcrumb-nav a {
            color: #0d6efd;
            text-decoration: none;
            margin-right: 10px;
        }

        .breadcrumb-nav a:hover {
            text-decoration: underline;
        }

        .breadcrumb-nav i {
            color: #999;
            margin: 0 8px;
        }

        /* Notification Dropdown */
        .dropdown-menu {
            border: none;
            box-shadow: var(--box-shadow);
            border-radius: 15px;
            padding: 20px;
            min-width: 350px;
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

                    <!-- Breadcrumb -->
                    <div class="breadcrumb-nav">
                        <a href="dashboard-admin.php">
                            <i class="fas fa-home"></i> Dashboard
                        </a>
                        <i class="fas fa-chevron-right"></i>
                        <a href="admin-blog.php">Blog Yazıları</a>
                        <i class="fas fa-chevron-right"></i>
                        <span style="color: #666;">Yeni Yazı</span>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle"></i>
                            <div>
                                <strong>Başarılı!</strong><br>
                                <?= $success ?>
                            </div>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle"></i>
                            <div>
                                <strong>Hata!</strong><br>
                                <?= $error ?>
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
                                        <!-- Debug için görünür textarea ekleyelim -->
                                        <textarea id="content-debug" style="margin-top: 10px; height: 100px; font-size: 12px; background: #f8f9fa;" readonly placeholder="Quill içeriği buraya aktarılacak..."></textarea>
                                        <div class="form-text">
                                            <i class="fas fa-magic"></i>
                                            Zengin metin editörü ile profesyonel içerik oluşturun
                                        </div>
                                    </div>
                                </div>

                                <div class="col-md-4">
                                    <!-- Öne Çıkan Resim -->
                                    <div class="dash-input-wrapper">
                                        <label for="featured_image">Öne Çıkan Resim</label>
                                        <div class="image-upload-section">
                                            <!-- Manual Upload -->
                                            <div class="upload-option">
                                                <h6><i class="fas fa-upload"></i> Manuel Yükleme</h6>
                                                <input type="file" id="image_upload" name="image_upload" accept="image/*" style="display: none;">
                                                <button type="button" class="btn-upload" onclick="document.getElementById('image_upload').click()">
                                                    <i class="fas fa-camera"></i> Resim Seç
                                                </button>
                                            </div>
                                            
                                            <!-- Auto Image Search -->
                                            <div class="upload-option">
                                                <h6><i class="fas fa-search"></i> Otomatik Resim</h6>
                                                <div class="d-flex gap-2">
                                                    <input type="text" id="image_search" placeholder="Arama kelimesi (ör: ev, gayrimenkul)" class="form-control form-control-sm">
                                                    <button type="button" class="btn btn-primary btn-sm" onclick="searchUnsplashImage()">
                                                        <i class="fas fa-search"></i>
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Image Preview -->
                                            <div class="image-preview-container" id="imagePreview" style="display: none;">
                                                <img id="previewImg" src="" alt="Preview" style="max-width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                                                <div class="image-actions mt-2">
                                                    <button type="button" class="btn btn-danger btn-sm" onclick="removeImage()">
                                                        <i class="fas fa-trash"></i> Kaldır
                                                    </button>
                                                </div>
                                            </div>
                                            
                                            <!-- Unsplash Results -->
                                            <div id="unsplashResults" class="unsplash-results" style="display: none;">
                                                <h6>Resim Seçiniz:</h6>
                                                <div class="image-grid" id="imageGrid"></div>
                                            </div>
                                        </div>
                                        <input type="hidden" id="featured_image" name="featured_image" value="">
                                    </div>

                                    <!-- Kategori -->
                                    <div class="dash-input-wrapper">
                                        <label for="category">Kategori</label>
                                        <select id="category" name="category">
                                            <option value="Genel">Genel</option>
                                            <option value="Emlak Haberleri">Emlak Haberleri</option>
                                            <option value="Yatırım Tavsiyeleri">Yatırım Tavsiyeleri</option>
                                            <option value="Piyasa Analizi">Piyasa Analizi</option>
                                            <option value="Yasal Bilgiler">Yasal Bilgiler</option>
                                            <option value="Lifestyle">Lifestyle</option>
                                        </select>
                                    </div>

                                    <!-- Etiketler -->
                                    <div class="dash-input-wrapper">
                                        <label for="tags">Etiketler</label>
                                        <input type="text" id="tags" name="tags" 
                                               placeholder="emlak, yatırım, İstanbul">
                                        <div class="form-text">
                                            <i class="fas fa-hashtag"></i>
                                            Etiketleri virgülle ayırın
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

                                    <!-- Öne Çıkan Yazı -->
                                    <div class="form-switch-wrapper">
                                        <div class="form-check form-switch">
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
                                <button type="submit" name="add_post" class="dash-btn-two" id="submitBtn">
                                    <i class="fas fa-save"></i>
                                    <span>Yazıyı Kaydet</span>
                                </button>
                                
                                <button type="button" class="btn-outline" onclick="previewPost()">
                                    <i class="fas fa-eye"></i>
                                    <span>Önizleme</span>
                                </button>
                                
                                <button type="button" class="btn-outline" onclick="testQuill()" style="background: orange; color: white;">
                                    <i class="fas fa-bug"></i>
                                    <span>Test Quill</span>
                                </button>
                                
                                <a href="admin-blog.php" class="btn-secondary">
                                    <i class="fas fa-arrow-left"></i>
                                    <span>Geri Dön</span>
                                </a>
                            </div>
                        </form>
                    </div>

                    <!-- Preview Card -->
                    <div class="card-box" id="previewCard" style="display: none;">
                        <h4 class="dash-title-three">
                            <i class="fas fa-eye"></i>
                            Yazı Önizlemesi
                        </h4>
                        <div id="previewContent"></div>
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

        // Quill içeriği değiştiğinde debug alanını güncelle
        quill.on('text-change', function() {
            const content = quill.root.innerHTML;
            const debugTextarea = document.getElementById('content-debug');
            if (debugTextarea) {
                debugTextarea.value = content;
            }
            
            // Ana content alanını da güncelle
            document.getElementById('content').value = content;
            
            console.log('Quill content updated:', content.substring(0, 50) + '...');
        });

        // Form submit işlemi - daha güvenilir yaklaşım
        document.getElementById('blogForm').addEventListener('submit', function(e) {
            e.preventDefault(); // Varsayılan submit'i engelle
            
            console.log('Form submit başladı');
            
            // Quill içeriğini al ve kontrol et
            const content = quill.root.innerHTML;
            const title = document.getElementById('title').value.trim();
            
            console.log('Title:', title);
            console.log('Content:', content);
            console.log('Content length:', content.length);
            
            // Validasyon
            if (!title) {
                alert('Başlık alanı zorunludur!');
                return false;
            }
            
            // Quill'in boş içerik kontrolü
            const textContent = quill.getText().trim();
            if (!textContent || textContent.length === 0) {
                alert('İçerik alanı zorunludur!');
                return false;
            }
            
            // İçeriği textarea'ya aktar
            document.getElementById('content').value = content;
            
            // Submit button'ı loading durumuna al
            const submitBtn = document.getElementById('submitBtn');
            if (submitBtn) {
                submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> <span>Kaydediliyor...</span>';
                submitBtn.disabled = true;
            }
            
            // Form'u gerçekten submit et
            setTimeout(() => {
                document.getElementById('blogForm').submit();
            }, 100);
            
            return false;
        });

        // Alternatif: Submit butonuna direkt click event ekle
        document.getElementById('submitBtn').addEventListener('click', function(e) {
            e.preventDefault();
            
            // Quill içeriğini textarea'ya aktar
            const content = quill.root.innerHTML;
            document.getElementById('content').value = content;
            
            console.log('Button clicked, content set:', content.substring(0, 100) + '...');
            
            // Form'u submit et
            document.getElementById('blogForm').submit();
        });

        // Test Quill Function
        function testQuill() {
            const content = quill.root.innerHTML;
            const textContent = quill.getText();
            const title = document.getElementById('title').value;
            
            alert(`
QUILL TEST SONUÇLARI:
===================
Title: "${title}"
Text Content: "${textContent}"
HTML Content: "${content}"
Content Length: ${content.length}
Text Length: ${textContent.length}
            `);
            
            // Manuel olarak content'i textarea'ya aktar
            document.getElementById('content').value = content;
            document.getElementById('content-debug').value = content;
            
            console.log('Manual content transfer completed');
        }

        // Preview Function
        function previewPost() {
            const title = document.getElementById('title').value;
            const content = quill.root.innerHTML;
            const excerpt = document.getElementById('excerpt').value;
            const category = document.getElementById('category').value;
            
            if (!title || !content) {
                alert('Önizleme için başlık ve içerik gereklidir!');
                return;
            }
            
            const previewCard = document.getElementById('previewCard');
            const previewContent = document.getElementById('previewContent');
            
            previewContent.innerHTML = `
                <div style="margin-bottom: 20px;">
                    <span style="background: #0d6efd; color: white; padding: 5px 12px; border-radius: 20px; font-size: 12px;">${category}</span>
                </div>
                <h2 style="color: var(--primary-color); margin-bottom: 15px; font-size: 24px;">${title}</h2>
                ${excerpt ? `<p style="font-style: italic; color: #666; margin-bottom: 20px; font-size: 16px;">${excerpt}</p>` : ''}
                <hr style="margin: 20px 0; border-color: #eee;">
                <div style="line-height: 1.6;">
                    ${content}
                </div>
            `;
            
            previewCard.style.display = 'block';
            previewCard.scrollIntoView({ behavior: 'smooth' });
        }

        // Auto-save functionality
        setInterval(function() {
            const title = document.getElementById('title').value;
            const content = quill.root.innerHTML;
            
            if (title && content) {
                localStorage.setItem('blog_draft', JSON.stringify({
                    title: title,
                    content: content,
                    excerpt: document.getElementById('excerpt').value,
                    category: document.getElementById('category').value,
                    tags: document.getElementById('tags').value,
                    timestamp: Date.now()
                }));
            }
        }, 30000);

        // Load draft on page load
        window.addEventListener('load', function() {
            const draft = localStorage.getItem('blog_draft');
            if (draft) {
                const data = JSON.parse(draft);
                const timeDiff = Date.now() - data.timestamp;
                
                if (timeDiff < 3600000) { // 1 hour
                    if (confirm('Kaydedilmemiş bir taslak bulundu. Yüklemek ister misiniz?')) {
                        document.getElementById('title').value = data.title;
                        quill.root.innerHTML = data.content;
                        document.getElementById('excerpt').value = data.excerpt;
                        document.getElementById('category').value = data.category;
                        document.getElementById('tags').value = data.tags;
                    }
                }
            }
        });

        // Clear draft on successful submit
        <?php if (isset($success)): ?>
            localStorage.removeItem('blog_draft');
        <?php endif; ?>

        // Mobile menu toggle
        document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').style.transform = 'translateX(0)';
        });

        // Close sidebar on mobile
        document.querySelector('.close-btn')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').style.transform = 'translateX(-100%)';
        });

        // Character count for title
        document.getElementById('title').addEventListener('input', function() {
            const maxLength = 100;
            const currentLength = this.value.length;
            
            if (!document.getElementById('titleCounter')) {
                const counter = document.createElement('div');
                counter.id = 'titleCounter';
                counter.className = 'form-text';
                counter.style.textAlign = 'right';
                this.parentNode.appendChild(counter);
            }
            
            const counter = document.getElementById('titleCounter');
            counter.innerHTML = `<i class="fas fa-text-width"></i> ${currentLength}/${maxLength} karakter`;
            counter.style.color = currentLength > maxLength ? 'var(--accent-color)' : '#666';
        });

        // Alert auto dismiss
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            });
        }, 5000);

        // Image Upload Functionality
        document.getElementById('image_upload').addEventListener('change', function(e) {
            const file = e.target.files[0];
            if (file) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    showImagePreview(e.target.result);
                    document.getElementById('featured_image').value = e.target.result;
                };
                reader.readAsDataURL(file);
            }
        });

        function showImagePreview(src) {
            const preview = document.getElementById('imagePreview');
            const img = document.getElementById('previewImg');
            img.src = src;
            preview.style.display = 'block';
            document.getElementById('unsplashResults').style.display = 'none';
        }

        function removeImage() {
            document.getElementById('imagePreview').style.display = 'none';
            document.getElementById('featured_image').value = '';
            document.getElementById('image_upload').value = '';
        }

        // Unsplash API Integration
        const UNSPLASH_ACCESS_KEY = 'YOUR_UNSPLASH_ACCESS_KEY'; // Gerçek projesinde buraya key eklenecek

        async function searchUnsplashImage() {
            const query = document.getElementById('image_search').value.trim();
            if (!query) {
                alert('Lütfen arama kelimesi girin');
                return;
            }

            // Demo için sabit resimler kullanacağız (API key gerektirmez)
            const demoImages = [
                'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=800&h=400&fit=crop',
                'https://images.unsplash.com/photo-1582407947304-fd86f028f716?ixlib=rb-4.0.3&w=800&h=400&fit=crop',
                'https://images.unsplash.com/photo-1570129477492-45c003edd2be?ixlib=rb-4.0.3&w=800&h=400&fit=crop',
                'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&w=800&h=400&fit=crop',
                'https://images.unsplash.com/photo-1558618047-3c8c76ca7d13?ixlib=rb-4.0.3&w=800&h=400&fit=crop',
                'https://images.unsplash.com/photo-1449824913935-59a10b8d2000?ixlib=rb-4.0.3&w=800&h=400&fit=crop'
            ];

            const resultsContainer = document.getElementById('unsplashResults');
            const imageGrid = document.getElementById('imageGrid');
            
            imageGrid.innerHTML = '';
            
            demoImages.forEach((imageUrl, index) => {
                const img = document.createElement('img');
                img.src = imageUrl;
                img.alt = `Image ${index + 1}`;
                img.className = 'unsplash-img';
                img.onclick = () => selectUnsplashImage(imageUrl, img);
                imageGrid.appendChild(img);
            });
            
            resultsContainer.style.display = 'block';
        }

        function selectUnsplashImage(imageUrl, imgElement) {
            // Remove previous selection
            document.querySelectorAll('.unsplash-img').forEach(img => {
                img.classList.remove('selected-img');
            });
            
            // Mark as selected
            imgElement.classList.add('selected-img');
            
            // Set as featured image
            document.getElementById('featured_image').value = imageUrl;
            showImagePreview(imageUrl);
        }

        // Auto-suggest images based on title
        document.getElementById('title').addEventListener('input', function() {
            const title = this.value.toLowerCase();
            let suggestion = '';
            
            if (title.includes('ev') || title.includes('konut')) {
                suggestion = 'ev konut';
            } else if (title.includes('ofis') || title.includes('büro')) {
                suggestion = 'ofis büro';
            } else if (title.includes('villa') || title.includes('lüks')) {
                suggestion = 'villa lüks ev';
            } else if (title.includes('yatırım') || title.includes('finans')) {
                suggestion = 'yatırım finans';
            } else if (title.includes('emlak') || title.includes('gayrimenkul')) {
                suggestion = 'gayrimenkul emlak';
            }
            
            if (suggestion) {
                document.getElementById('image_search').value = suggestion;
            }
        });
    </script>
</body>
</html>