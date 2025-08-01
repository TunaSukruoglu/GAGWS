<?php
session_start();
include 'db.php';

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında
include 'includes/session-check.php';

// URL'den slug parametresini al
$slug = $_GET['slug'] ?? null;

if (!$slug) {
    header("Location: blog.php");
    exit;
}

// Blog yazısını veritabanından çek
try {
    $post_query = $conn->prepare("SELECT bp.*, u.name as author_name 
                                 FROM blog_posts bp 
                                 LEFT JOIN users u ON bp.author_id = u.id 
                                 WHERE bp.slug = ? AND bp.status = 'published'");
    $post_query->bind_param("s", $slug);
    $post_query->execute();
    $result = $post_query->get_result();
    $post = $result->fetch_assoc();
    
    if (!$post) {
        header("Location: blog.php");
        exit;
    }
    
    // Görüntülenme sayısını artır
    $update_views = $conn->prepare("UPDATE blog_posts SET views = views + 1 WHERE id = ?");
    $update_views->bind_param("i", $post['id']);
    $update_views->execute();
    
} catch (Exception $e) {
    error_log("Blog detail error: " . $e->getMessage());
    header("Location: blog.php");
    exit;
}

// İlgili blog yazılarını çek (aynı kategori)
try {
    $related_query = $conn->prepare("SELECT bp.*, u.name as author_name 
                                    FROM blog_posts bp 
                                    LEFT JOIN users u ON bp.author_id = u.id 
                                    WHERE bp.category = ? AND bp.id != ? AND bp.status = 'published' 
                                    ORDER BY bp.published_at DESC 
                                    LIMIT 3");
    $related_query->bind_param("si", $post['category'], $post['id']);
    $related_query->execute();
    $related_result = $related_query->get_result();
    $related_posts = $related_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $related_posts = [];
}

// Yorumları çek
try {
    $comments_query = $conn->prepare("SELECT * FROM blog_comments WHERE post_id = ? AND status = 'approved' ORDER BY created_at DESC");
    $comments_query->bind_param("i", $post['id']);
    $comments_query->execute();
    $comments_result = $comments_query->get_result();
    $comments = $comments_result->fetch_all(MYSQLI_ASSOC);
} catch (Exception $e) {
    $comments = [];
}

// Yorum ekleme
if (isset($_POST['add_comment'])) {
    $comment_name = trim($_POST['comment_name'] ?? '');
    $comment_email = trim($_POST['comment_email'] ?? '');
    $comment_content = trim($_POST['comment_content'] ?? '');
    
    if (!empty($comment_name) && !empty($comment_email) && !empty($comment_content)) {
        if (filter_var($comment_email, FILTER_VALIDATE_EMAIL)) {
            try {
                $insert_comment = $conn->prepare("INSERT INTO blog_comments (post_id, author_name, author_email, content, ip_address) VALUES (?, ?, ?, ?, ?)");
                $user_ip = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';
                $insert_comment->bind_param("issss", $post['id'], $comment_name, $comment_email, $comment_content, $user_ip);
                
                if ($insert_comment->execute()) {
                    $comment_success = "Yorumunuz başarıyla gönderildi! Onaylandıktan sonra görünecektir.";
                } else {
                    $comment_error = "Yorum gönderilirken bir hata oluştu.";
                }
            } catch (Exception $e) {
                $comment_error = "Yorum gönderilirken bir hata oluştu.";
            }
        } else {
            $comment_error = "Geçerli bir e-posta adresi girin.";
        }
    } else {
        $comment_error = "Tüm alanları doldurun.";
    }
}

// Tarih formatlama fonksiyonu
function formatTurkishDate($date) {
    $months = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 5 => 'Mayıs', 6 => 'Haziran',
        7 => 'Temmuz', 8 => 'Ağustos', 9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    $year = date('Y', $timestamp);
    return $day . ' ' . $month . ' ' . $year;
}

// Okuma süresi hesaplama fonksiyonu
function calculateReadTime($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Dakikada 200 kelime
    return max(1, $reading_time); // En az 1 dakika
}

$published_date = $post['published_at'] ?? $post['created_at'];
$reading_time = calculateReadTime($post['content']);
$author_name = !empty($post['author_name']) ? $post['author_name'] : 'Gökhan Aydınlı';

// Current URL for sharing
$current_url = (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI'];

// Varsayılan resim eğer özel resim yoksa
$default_image = 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=1200&h=600&fit=crop';
$featured_image = !empty($post['featured_image']) ? $post['featured_image'] : $default_image;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="<?= htmlspecialchars($post['tags'] ?? 'Gayrimenkul Blog, İstanbul Emlak, Gökhan Aydınlı') ?>">
    <meta name="description" content="<?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 160)) ?>">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($post['title']) ?> - Gökhan Aydınlı">
    <meta property="og:description" content="<?= htmlspecialchars($post['excerpt'] ?? substr(strip_tags($post['content']), 0, 160)) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($featured_image) ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title><?= htmlspecialchars($post['title']) ?> - Gökhan Aydınlı</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Blog Detail CSS -->
    <style>
        body {
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, sans-serif;
            line-height: 1.7;
            color: #2c3e50;
            background: linear-gradient(135deg, #f8fafc 0%, #e3f2fd 100%);
        }

        /* Header Styling */
        .theme-main-menu {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(15px);
            box-shadow: 0 2px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
        }

        .theme-main-menu.fixed {
            background: rgba(255, 255, 255, 0.98);
            box-shadow: 0 5px 30px rgba(0,0,0,0.15);
        }

        /* Breadcrumb */
        .breadcrumb-area {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            padding: 40px 0;
            margin-bottom: 0;
        }

        .breadcrumb-nav {
            background: rgba(255,255,255,0.1);
            padding: 15px 25px;
            border-radius: 50px;
            backdrop-filter: blur(10px);
            display: inline-block;
        }

        .breadcrumb-nav a {
            color: rgba(255,255,255,0.8);
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .breadcrumb-nav a:hover {
            color: white;
        }

        .breadcrumb-nav span {
            color: white;
            margin: 0 10px;
        }

        /* Hero Section */
        .blog-detail-hero {
            background: linear-gradient(135deg, rgba(13, 26, 28, 0.95), rgba(44, 62, 80, 0.9)), url('<?= htmlspecialchars($featured_image) ?>');
            background-size: cover;
            background-position: center;
            background-attachment: fixed;
            min-height: 75vh;
            display: flex;
            align-items: center;
            position: relative;
            overflow: hidden;
        }

        .blog-detail-hero::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 20"><defs><linearGradient id="g" x1="0%" y1="0%" x2="0%" y2="100%"><stop offset="0%" style="stop-color:rgba(255,255,255,0.05)"/><stop offset="100%" style="stop-color:rgba(255,255,255,0)"/></linearGradient></defs><rect width="100" height="20" fill="url(%23g)"/></svg>');
            z-index: 1;
            opacity: 0.3;
        }

        .blog-detail-hero .container {
            position: relative;
            z-index: 2;
        }

        .hero-content {
            text-align: center;
            color: white;
            max-width: 900px;
            margin: 0 auto;
            padding: 40px 20px;
        }

        .hero-category {
            display: inline-block;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 12px 25px;
            border-radius: 30px;
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 25px;
            backdrop-filter: blur(10px);
            text-transform: uppercase;
            letter-spacing: 1px;
            box-shadow: 0 8px 32px rgba(0,0,0,0.2);
            transition: transform 0.3s ease;
        }

        .hero-category:hover {
            transform: translateY(-2px);
        }

        .hero-title {
            font-size: clamp(32px, 5vw, 56px);
            font-weight: 800;
            margin-bottom: 25px;
            text-shadow: 0 4px 20px rgba(0,0,0,0.4);
            line-height: 1.2;
            background: linear-gradient(135deg, #fff, #e3f2fd);
            background-clip: text;
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
        }

        .hero-excerpt {
            font-size: 20px;
            color: rgba(255,255,255,0.9);
            margin: 25px 0;
            max-width: 700px;
            margin-left: auto;
            margin-right: auto;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            line-height: 1.6;
        }

        .hero-meta {
            display: flex;
            justify-content: center;
            gap: 30px;
            margin-top: 35px;
            flex-wrap: wrap;
        }

        .hero-meta-item {
            display: flex;
            align-items: center;
            gap: 10px;
            font-size: 16px;
            color: rgba(255,255,255,0.95);
            background: rgba(255,255,255,0.15);
            padding: 12px 20px;
            border-radius: 25px;
            backdrop-filter: blur(15px);
            transition: all 0.3s ease;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .hero-meta-item:hover {
            background: rgba(255,255,255,0.25);
            transform: translateY(-2px);
        }

        .hero-meta-item i {
            font-size: 18px;
            opacity: 0.9;
        }

        /* Content Area */
        .blog-content {
            background: white;
            border-radius: 30px;
            margin-top: -80px;
            position: relative;
            z-index: 3;
            box-shadow: 0 20px 60px rgba(0,0,0,0.1);
            overflow: hidden;
            border: 1px solid rgba(255,255,255,0.2);
        }

        .content-wrapper {
            padding: 80px 60px 60px;
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 60px 30px 40px;
            }
        }

        .content-body {
            font-size: 18px;
            line-height: 1.8;
            color: #34495e;
        }

        .content-body h2 {
            color: #2c3e50;
            font-weight: 700;
            margin: 40px 0 20px;
            font-size: 28px;
            position: relative;
            padding-left: 20px;
        }

        .content-body h2::before {
            content: '';
            position: absolute;
            left: 0;
            top: 0;
            height: 100%;
            width: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .content-body h3 {
            color: #34495e;
            font-weight: 600;
            margin: 30px 0 15px;
            font-size: 24px;
        }

        .content-body p {
            margin-bottom: 20px;
            text-align: justify;
        }

        .content-body ul, .content-body ol {
            margin: 20px 0;
            padding-left: 30px;
        }

        .content-body li {
            margin-bottom: 10px;
            line-height: 1.7;
        }

        .content-body strong {
            color: #2c3e50;
            font-weight: 600;
        }

        /* Tags Section */
        .tags-section {
            background: linear-gradient(135deg, #f8fafc, #e3f2fd);
            padding: 40px;
            border-radius: 20px;
            margin: 40px 0;
            border: 1px solid rgba(103, 126, 234, 0.1);
        }

        .tags-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .tags-list {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
        }

        .tag-item {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 8px 18px;
            border-radius: 20px;
            font-size: 14px;
            font-weight: 500;
            text-decoration: none;
            transition: all 0.3s ease;
            display: inline-block;
        }

        .tag-item:hover {
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(103, 126, 234, 0.3);
            color: white;
        }

        /* Share Buttons */
        .share-section {
            background: white;
            padding: 30px;
            border-radius: 20px;
            margin: 40px 0;
            box-shadow: 0 10px 40px rgba(0,0,0,0.05);
            border: 1px solid rgba(0,0,0,0.05);
        }

        .share-title {
            font-size: 20px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .share-buttons {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }

        .share-btn {
            display: flex;
            align-items: center;
            gap: 8px;
            padding: 12px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            border: 2px solid transparent;
        }

        .share-btn-facebook {
            background: #3b5998;
            color: white;
        }

        .share-btn-twitter {
            background: #1da1f2;
            color: white;
        }

        .share-btn-linkedin {
            background: #0077b5;
            color: white;
        }

        .share-btn-whatsapp {
            background: #25d366;
            color: white;
        }

        .share-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.2);
        }

        /* Related Posts */
        .related-posts-section {
            margin: 60px 0;
        }

        .section-title {
            font-size: 32px;
            font-weight: 700;
            color: #2c3e50;
            text-align: center;
            margin-bottom: 50px;
            position: relative;
        }

        .section-title::after {
            content: '';
            position: absolute;
            bottom: -10px;
            left: 50%;
            transform: translateX(-50%);
            width: 60px;
            height: 4px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            border-radius: 2px;
        }

        .related-post-card {
            background: white;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 15px 50px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            height: 100%;
            border: 1px solid rgba(0,0,0,0.05);
        }

        .related-post-card:hover {
            transform: translateY(-10px);
            box-shadow: 0 25px 60px rgba(0,0,0,0.15);
        }

        .related-post-image {
            height: 200px;
            background-size: cover;
            background-position: center;
            position: relative;
        }

        .related-post-content {
            padding: 25px;
        }

        .related-post-category {
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            padding: 6px 15px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
            margin-bottom: 15px;
            display: inline-block;
        }

        .related-post-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin-bottom: 15px;
            line-height: 1.4;
        }

        .related-post-title a {
            color: inherit;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .related-post-title a:hover {
            color: #667eea;
        }

        .related-post-meta {
            font-size: 14px;
            color: #7f8c8d;
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .related-post-meta i {
            opacity: 0.7;
        }

        /* Responsive Design */
        @media (max-width: 992px) {
            .content-wrapper {
                padding: 60px 40px 40px;
            }
            
            .hero-meta {
                gap: 15px;
            }
            
            .hero-meta-item {
                font-size: 14px;
                padding: 10px 15px;
            }
            
            .share-buttons {
                justify-content: center;
            }
            
            .tags-list {
                justify-content: center;
            }
        }

        @media (max-width: 768px) {
            .content-wrapper {
                padding: 40px 25px 30px;
            }
            
            .hero-title {
                font-size: 28px;
            }
            
            .hero-excerpt {
                font-size: 16px;
            }
            
            .hero-meta {
                flex-direction: column;
                gap: 10px;
            }
            
            .hero-meta-item {
                width: 100%;
                justify-content: center;
            }
            
            .content-body h2 {
                font-size: 24px;
            }
            
            .content-body h3 {
                font-size: 20px;
            }
            
            .content-body p {
                font-size: 16px;
            }
            
            .tags-section,
            .share-section {
                padding: 25px 20px;
            }
            
            .share-buttons {
                gap: 10px;
            }
            
            .share-btn {
                padding: 10px 15px;
                font-size: 14px;
            }
            
            .related-post-content {
                padding: 20px;
            }
            
            .scroll-top {
                bottom: 20px;
                right: 20px;
                width: 45px;
                height: 45px;
            }
        }

        @media (max-width: 576px) {
            .hero-content {
                padding: 20px 15px;
            }
            
            .hero-category {
                padding: 8px 15px;
                font-size: 12px;
            }
            
            .breadcrumb-nav {
                padding: 10px 20px;
                font-size: 14px;
            }
            
            .content-wrapper {
                padding: 30px 20px 25px;
            }
            
            .section-title {
                font-size: 24px;
                margin-bottom: 30px;
            }
            
            .related-post-image {
                height: 180px;
            }
            
            .blog-detail-hero {
                min-height: 60vh;
                background-attachment: scroll;
            }
        }

        /* Animations */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        .blog-content {
            animation: fadeInUp 0.8s ease-out;
        }

        .related-post-card {
            animation: fadeInUp 0.8s ease-out;
        }

        /* Scroll to Top Button */
        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            background: linear-gradient(135deg, #667eea, #764ba2);
            color: white;
            width: 50px;
            height: 50px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            opacity: 0;
            visibility: hidden;
            transition: all 0.3s ease;
            z-index: 1000;
            box-shadow: 0 10px 30px rgba(103, 126, 234, 0.3);
        }

        .scroll-top.show {
            opacity: 1;
            visibility: visible;
        }

        .scroll-top:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(103, 126, 234, 0.4);
        }
    </style>
</head>
<body>
    
    <!-- Header Navigation -->
    <header class="theme-main-menu menu-overlay menu-style-seven white-vr sticky-menu">
        <div class="inner-content gap-one">
            <div class="top-header position-relative">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="logo order-lg-0">
                        <a href="index.php" class="d-flex align-items-center">
                            <img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
                        </a>
                    </div>
                    
                    <!-- Auth Widget -->
                    <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                        <ul class="d-flex align-items-center style-none">
                            <?php if ($isLoggedIn): ?>
                                <li class="dropdown">
                                    <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <li><a class="dropdown-item" href="dashboard/dashboard-admin.php">Panel</a></li>
                                        <li><a class="dropdown-item" href="dashboard/profile.php">Profil</a></li>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one">
                                        <i class="fa-regular fa-lock"></i> <span>Giriş</span>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                    </div>
                    
                    <!-- Navigation -->
                    <nav class="navbar navbar-expand-lg p0 order-lg-2">
                        <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
                            data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                            aria-label="Toggle navigation">
                            <span></span>
                        </button>
                        <div class="collapse navbar-collapse" id="navbarNav">
                            <ul class="navbar-nav align-items-lg-center">
                                <li class="nav-item">
                                    <a class="nav-link" href="index.php">Ana Sayfa</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="portfoy.php">Portföy</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="blog.php">Blog</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="contact.php">İletişim</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>

    <!-- Breadcrumb -->
    <div class="breadcrumb-area">
        <div class="container">
            <div class="text-center">
                <nav class="breadcrumb-nav">
                    <a href="index.php">Anasayfa</a>
                    <span>/</span>
                    <a href="blog.php">Blog</a>
                    <span>/</span>
                    <span><?= htmlspecialchars($post['title']) ?></span>
                </nav>
            </div>
        </div>
    </div>

    <!-- Blog Detail Hero -->
    <div class="blog-detail-hero">
        <div class="container">
            <div class="hero-content">
                <?php if (!empty($post['category'])): ?>
                    <div class="hero-category">
                        <i class="fas fa-folder"></i> <?= htmlspecialchars($post['category']) ?>
                    </div>
                <?php endif; ?>
                
                <h1 class="hero-title"><?= htmlspecialchars($post['title']) ?></h1>
                
                <?php if (!empty($post['excerpt'])): ?>
                    <p class="hero-excerpt">
                        <?= htmlspecialchars($post['excerpt']) ?>
                    </p>
                <?php endif; ?>
                
                <div class="hero-meta">
                    <div class="hero-meta-item">
                        <i class="fas fa-user"></i>
                        <span><?= htmlspecialchars($author_name) ?></span>
                    </div>
                    <div class="hero-meta-item">
                        <i class="fas fa-calendar"></i>
                        <span><?= formatTurkishDate($published_date) ?></span>
                    </div>
                    <div class="hero-meta-item">
                        <i class="fas fa-clock"></i>
                        <span><?= $reading_time ?> dakika</span>
                    </div>
                    <div class="hero-meta-item">
                        <i class="fas fa-eye"></i>
                        <span><?= number_format($post['views']) ?> görüntülenme</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Content -->
    <div class="container">
        <div class="row justify-content-center">
            <div class="col-lg-10">
                <div class="blog-content">
                    <div class="content-wrapper">
                        <!-- Blog Content Body -->
                        <div class="content-body">
                            <?= $post['content'] ?>
                        </div>

                        <!-- Tags Section -->
                        <?php if (!empty($post['tags'])): ?>
                            <div class="tags-section">
                                <div class="tags-title">
                                    <i class="fas fa-tags"></i>
                                    Etiketler:
                                </div>
                                <div class="tags-list">
                                    <?php 
                                    $tags = explode(',', $post['tags']);
                                    foreach ($tags as $tag): 
                                        $tag = trim($tag);
                                        if (!empty($tag)):
                                    ?>
                                        <a href="blog.php?tag=<?= urlencode($tag) ?>" class="tag-item">
                                            <?= htmlspecialchars($tag) ?>
                                        </a>
                                    <?php 
                                        endif;
                                    endforeach; 
                                    ?>
                                </div>
                            </div>
                        <?php endif; ?>

                        <!-- Share Section -->
                        <div class="share-section">
                            <div class="share-title">
                                <i class="fas fa-share-alt"></i>
                                Paylaş:
                            </div>
                            <div class="share-buttons">
                                <a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode($current_url) ?>" 
                                   target="_blank" class="share-btn share-btn-facebook">
                                    <i class="fab fa-facebook-f"></i>
                                    <span>Facebook</span>
                                </a>
                                <a href="https://twitter.com/intent/tweet?url=<?= urlencode($current_url) ?>&text=<?= urlencode($post['title']) ?>" 
                                   target="_blank" class="share-btn share-btn-twitter">
                                    <i class="fab fa-twitter"></i>
                                    <span>Twitter</span>
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode($current_url) ?>" 
                                   target="_blank" class="share-btn share-btn-linkedin">
                                    <i class="fab fa-linkedin-in"></i>
                                    <span>LinkedIn</span>
                                </a>
                                <a href="https://wa.me/?text=<?= urlencode($post['title'] . ' - ' . $current_url) ?>" 
                                   target="_blank" class="share-btn share-btn-whatsapp">
                                    <i class="fab fa-whatsapp"></i>
                                    <span>WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Related Posts Section -->
    <?php if (!empty($related_posts)): ?>
    <div class="related-posts-section">
        <div class="container">
            <h2 class="section-title">İlgili Blog Yazıları</h2>
            <div class="row">
                <?php foreach ($related_posts as $related): 
                    $related_image = !empty($related['featured_image']) ? $related['featured_image'] : $default_image;
                    $related_url = "blog-details.php?slug=" . urlencode($related['slug']);
                ?>
                    <div class="col-lg-4 col-md-6 mb-4">
                        <div class="related-post-card">
                            <div class="related-post-image" style="background-image: url('<?= htmlspecialchars($related_image) ?>');"></div>
                            <div class="related-post-content">
                                <div class="related-post-category">
                                    <?= htmlspecialchars($related['category'] ?? 'Emlak') ?>
                                </div>
                                <h3 class="related-post-title">
                                    <a href="<?= $related_url ?>">
                                        <?= htmlspecialchars($related['title']) ?>
                                    </a>
                                </h3>
                                <div class="related-post-meta">
                                    <i class="fas fa-calendar"></i> <?= formatTurkishDate($related['published_at'] ?? $related['created_at']) ?>
                                    •
                                    <i class="fas fa-clock"></i> <?= calculateReadTime($related['content']) ?> dk
                                </div>
                            </div>
                        </div>
                    </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
    <?php endif; ?>

    <!-- Comments Section -->
    <div class="comments-section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-10">
                    <div class="comments-wrapper">
                        <h2 class="comments-title">
                            <i class="fas fa-comments"></i>
                            Yorumlar (<?= count($comments) ?>)
                        </h2>

                        <?php if (!empty($comments)): ?>
                            <div class="comments-list">
                                <?php foreach ($comments as $comment): ?>
                                    <div class="comment-item">
                                        <div class="comment-avatar">
                                            <i class="fas fa-user"></i>
                                        </div>
                                        <div class="comment-content">
                                            <div class="comment-header">
                                                <h4 class="comment-author"><?= htmlspecialchars($comment['author_name']) ?></h4>
                                                <span class="comment-date"><?= formatTurkishDate($comment['created_at']) ?></span>
                                            </div>
                                            <div class="comment-text">
                                                <?= nl2br(htmlspecialchars($comment['content'])) ?>
                                            </div>
                                        </div>
                                    </div>
                                <?php endforeach; ?>
                            </div>
                        <?php else: ?>
                            <div class="no-comments">
                                <i class="fas fa-comment-slash"></i>
                                <h3>Henüz yorum yapılmamış</h3>
                                <p>Bu yazı hakkındaki düşüncelerinizi paylaşarak ilk yorumu siz yapabilirsiniz.</p>
                            </div>
                        <?php endif; ?>

                        <!-- Comment Form -->
                        <div class="comment-form-wrapper">
                            <h3 class="form-title">
                                <i class="fas fa-edit"></i>
                                Yorum Bırakın
                            </h3>
                            
                            <?php if (isset($comment_success)): ?>
                                <div class="alert alert-success">
                                    <i class="fas fa-check-circle"></i>
                                    <?= $comment_success ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if (isset($comment_error)): ?>
                                <div class="alert alert-danger">
                                    <i class="fas fa-exclamation-circle"></i>
                                    <?= $comment_error ?>
                                </div>
                            <?php endif; ?>

                            <form method="POST" class="comment-form">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="comment_name">Ad Soyad *</label>
                                            <input type="text" id="comment_name" name="comment_name" 
                                                   value="<?= htmlspecialchars($_POST['comment_name'] ?? '') ?>" 
                                                   required placeholder="Adınız ve soyadınız">
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group">
                                            <label for="comment_email">E-posta *</label>
                                            <input type="email" id="comment_email" name="comment_email" 
                                                   value="<?= htmlspecialchars($_POST['comment_email'] ?? '') ?>" 
                                                   required placeholder="E-posta adresiniz">
                                        </div>
                                    </div>
                                </div>
                                <div class="form-group">
                                    <label for="comment_content">Yorumunuz *</label>
                                    <textarea id="comment_content" name="comment_content" rows="5" 
                                              required placeholder="Yazı hakkındaki düşüncelerinizi paylaşın..."><?= htmlspecialchars($_POST['comment_content'] ?? '') ?></textarea>
                                </div>
                                <button type="submit" name="add_comment" class="comment-submit-btn">
                                    <i class="fas fa-paper-plane"></i>
                                    Yorum Gönder
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Login Modal -->
    <?php include 'includes/login-modal.php'; ?>

    <!-- Scroll to Top Button -->
    <div class="scroll-top" id="scrollTop">
        <i class="fas fa-arrow-up"></i>
    </div>

    <!-- Footer -->
    <?php include 'includes/footer.php'; ?>

    <!-- JavaScript -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>

    <script>
        // Smooth scroll for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            });
        });

        // Scroll to top functionality
        const scrollTopBtn = document.getElementById('scrollTop');
        
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollTopBtn.classList.add('show');
            } else {
                scrollTopBtn.classList.remove('show');
            }
        });

        scrollTopBtn.addEventListener('click', function() {
            window.scrollTo({
                top: 0,
                behavior: 'smooth'
            });
        });

        // Copy URL to clipboard
        function copyURL() {
            navigator.clipboard.writeText(window.location.href).then(function() {
                // Create a toast notification
                const toast = document.createElement('div');
                toast.style.cssText = `
                    position: fixed;
                    top: 20px;
                    right: 20px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    color: white;
                    padding: 15px 25px;
                    border-radius: 25px;
                    z-index: 10000;
                    font-weight: 500;
                    box-shadow: 0 10px 30px rgba(0,0,0,0.2);
                    transform: translateX(100%);
                    transition: transform 0.3s ease;
                `;
                toast.textContent = 'URL başarıyla kopyalandı!';
                document.body.appendChild(toast);
                
                // Show toast
                setTimeout(() => {
                    toast.style.transform = 'translateX(0)';
                }, 100);
                
                // Hide toast
                setTimeout(() => {
                    toast.style.transform = 'translateX(100%)';
                    setTimeout(() => {
                        document.body.removeChild(toast);
                    }, 300);
                }, 3000);
            }).catch(function() {
                alert('URL kopyalanamadı!');
            });
        }

        // Enhanced header background on scroll
        let header = document.querySelector('.theme-main-menu');
        if (header) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 100) {
                    header.classList.add('fixed');
                } else {
                    header.classList.remove('fixed');
                }
            });
        }

        // Reading progress bar
        function updateReadingProgress() {
            const content = document.querySelector('.content-body');
            if (!content) return;
            
            const rect = content.getBoundingClientRect();
            const contentHeight = content.offsetHeight;
            const windowHeight = window.innerHeight;
            const scrolled = Math.max(0, -rect.top);
            const progress = Math.min(100, (scrolled / (contentHeight - windowHeight)) * 100);
            
            let progressBar = document.getElementById('readingProgress');
            if (!progressBar) {
                progressBar = document.createElement('div');
                progressBar.id = 'readingProgress';
                progressBar.style.cssText = `
                    position: fixed;
                    top: 0;
                    left: 0;
                    width: 0%;
                    height: 4px;
                    background: linear-gradient(135deg, #667eea, #764ba2);
                    z-index: 10000;
                    transition: width 0.1s ease;
                `;
                document.body.appendChild(progressBar);
            }
            
            progressBar.style.width = progress + '%';
        }

        window.addEventListener('scroll', updateReadingProgress);
        updateReadingProgress();
    </script>
</body>
</html>
