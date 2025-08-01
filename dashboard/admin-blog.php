<?php
session_start();
include '../db.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini veritabanından çek
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Admin değilse normal dashboard'a yönlendir
if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'root')) {
    // Admin olmayan kullanıcıları bilgilendir
    $_SESSION['error_message'] = "Bu sayfaya erişim yetkiniz yok. Admin yetkisi gereklidir.";
    header("Location: dashboard.php");
    exit;
}

// Yeni blog sistemini kullan - blogs tablosu zaten admin-blog-add-new.php'de oluşturuluyor
// Eski blog_posts tablosunu kaldır, sadece blogs tablosunu kullan

// Sayfa ayarları
$current_page = 'admin-blog';
$page_title = $user['name'] . ' - Blog Yönetimi';
$user_name = $user['name']; // Sidebar için

// Blog yazılarını getir (Yeni sistem - blogs tablosu)
$blog_query = "SELECT b.*, 
               GROUP_CONCAT(DISTINCT c.name SEPARATOR ', ') as categories,
               GROUP_CONCAT(DISTINCT t.name SEPARATOR ', ') as tags
               FROM blogs b 
               LEFT JOIN blog_category_relations bcr ON b.id = bcr.blog_id
               LEFT JOIN blog_categories c ON bcr.category_id = c.id
               LEFT JOIN blog_tag_relations btr ON b.id = btr.blog_id
               LEFT JOIN blog_tags t ON btr.tag_id = t.id
               GROUP BY b.id
               ORDER BY b.created_at DESC";
$blog_result = $conn->query($blog_query);

// Blog yazısı silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $post_id = intval($_GET['id']);
    
    // Blog dosyasını da sil
    $blog_file = "../blog{$post_id}.php";
    if (file_exists($blog_file)) {
        unlink($blog_file);
    }
    
    // Silme işlemi (Yeni sistem - blogs tablosu)
    $delete_query = $conn->prepare("DELETE FROM blogs WHERE id = ?");
    $delete_query->bind_param("i", $post_id);
    
    if ($delete_query->execute()) {
        $success = "Blog yazısı başarıyla silindi!";
    } else {
        $error = "Blog yazısı silinirken bir hata oluştu!";
    }
    
    // Sayfayı yeniden yükle
    header("Location: admin-blog.php");
    exit;
}

// Blog yazısı durumunu değiştirme
if (isset($_GET['action']) && $_GET['action'] == 'change_status' && isset($_GET['id']) && isset($_GET['status'])) {
    $post_id = intval($_GET['id']);
    $new_status = $_GET['status'];
    
    // Geçerli durum kontrolü
    if (in_array($new_status, ['draft', 'published', 'scheduled'])) {
        $publish_date = null;
        if ($new_status == 'published') {
            $publish_date = date('Y-m-d H:i:s');
            
            // Blog dosyası oluştur
            $blog_query = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
            $blog_query->bind_param("i", $post_id);
            $blog_query->execute();
            $blog = $blog_query->get_result()->fetch_assoc();
            
            if ($blog) {
                // Benzersiz dosya ismi oluştur
                $base_slug = $blog['slug'];
                $timestamp = date('Ymd-His');
                $unique_filename = "blog-{$base_slug}-{$post_id}-{$timestamp}.php";
                
                // Eğer dosya zaten varsa, daha benzersiz yap
                $counter = 1;
                while (file_exists("../{$unique_filename}")) {
                    $unique_filename = "blog-{$base_slug}-{$post_id}-{$timestamp}-{$counter}.php";
                    $counter++;
                }
                
                $filename = "../{$unique_filename}";
                $template = generateBlogTemplate($blog, $unique_filename);
                file_put_contents($filename, $template);
                
                // Veritabanında blog dosya ismini güncelle
                $update_filename = $conn->prepare("UPDATE blogs SET blog_file = ? WHERE id = ?");
                $update_filename->bind_param("si", $unique_filename, $post_id);
                $update_filename->execute();
            }
        }
        
        $update_query = $conn->prepare("UPDATE blogs SET status = ?, publish_date = ? WHERE id = ?");
        $update_query->bind_param("ssi", $new_status, $publish_date, $post_id);
        
        if ($update_query->execute()) {
            $success = "Blog yazısı durumu değiştirildi!";
        } else {
            $error = "Durum değiştirilirken bir hata oluştu!";
        }
        
        // Sayfayı yeniden yükle
        header("Location: admin-blog.php");
        exit;
    }
}

// İstatistikler (Yeni sistem - blogs tablosu)
$stats_query = "SELECT 
    COUNT(*) as total_posts,
    SUM(CASE WHEN status = 'published' THEN 1 ELSE 0 END) as published_count,
    SUM(CASE WHEN status = 'draft' THEN 1 ELSE 0 END) as draft_count,
    SUM(CASE WHEN status = 'scheduled' THEN 1 ELSE 0 END) as scheduled_count,
    SUM(views) as total_views,
    SUM(CASE WHEN featured = 1 THEN 1 ELSE 0 END) as featured_count
FROM blogs";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Kategorileri getir (Yeni sistem)
$categories_query = "SELECT c.name as category, COUNT(bcr.blog_id) as count 
                     FROM blog_categories c 
                     LEFT JOIN blog_category_relations bcr ON c.id = bcr.category_id 
                     GROUP BY c.id, c.name 
                     ORDER BY count DESC";
$categories_result = $conn->query($categories_query);

// Blog template oluşturma fonksiyonu
function generateBlogTemplate($blog, $filename = '') {
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
            font-size: 1.1rem;
            line-height: 1.8;
            color: #374151;
        }
        .blog-content h2, .blog-content h3 {
            color: #1f2937;
            margin: 30px 0 20px 0;
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
        
        /* Dosya bilgisi */
        .file-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Navigation buraya gelecek -->
        
        <!-- Blog Details -->
        <div class="blog-details-one pt-180 lg-pt-150 pb-150 xl-pb-120">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-meta-wrapper pe-xxl-5">
                            ' . ($filename ? '<div class="file-info">📄 Dosya: ' . htmlspecialchars($filename) . ' | 🆔 Blog ID: ' . $blog['id'] . '</div>' : '') . '
                            
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
                </div>
            </div>
        </div>
    </div>
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
    <title><?= $page_title ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    <link rel="stylesheet" href="includes/dashboard-common.css">
    
    <style>
        /* Dashboard Admin Blog Specific Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: margin-left 0.3s ease;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .mobile-header {
            display: none;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            align-items: center;
            justify-content: space-between;
            position: sticky;
            top: 0;
            z-index: 999;
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #0066ff;
            cursor: pointer;
        }
        
        .mobile-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin: 0;
        }
        
        .mobile-logout {
            color: #dc3545;
            text-decoration: none;
            font-size: 18px;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #4c9eff 0%, #0066ff 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(76, 158, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
        }
        
        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
            position: relative;
            z-index: 1;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
            border-top: 4px solid #4c9eff;
            position: relative;
            overflow: hidden;
        }

        .stats-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(76, 158, 255, 0.02) 0%, rgba(0, 102, 255, 0.02) 100%);
            pointer-events: none;
        }

        .stats-card:hover {
            box-shadow: 0 15px 45px rgba(76, 158, 255, 0.15);
            transform: translateY(-5px);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, #4c9eff, #0066ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
            box-shadow: 0 8px 25px rgba(76, 158, 255, 0.3);
            position: relative;
            z-index: 1;
        }

        .stats-number {
            font-size: 36px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stats-change {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #28a745;
        }

        /* Blog Cards */
        .blog-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
        }
        
        .blog-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        }
        
        .blog-card.published {
            border-left: 4px solid #0d6efd;
        }
        
        .blog-card.draft {
            border-left: 4px solid #ffc107;
        }
        
        .blog-card.archived {
            border-left: 4px solid #6c757d;
            opacity: 0.8;
        }
        
        .blog-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 10px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .blog-meta {
            color: #6c757d;
            font-size: 14px;
            display: flex;
            align-items: center;
            gap: 15px;
            flex-wrap: wrap;
            margin-bottom: 15px;
        }
        
        .blog-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }
        
        .blog-excerpt {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.6;
            margin-bottom: 20px;
            display: -webkit-box;
            -webkit-line-clamp: 3;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }
        
        .blog-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            margin-bottom: 20px;
        }
        
        .blog-tag {
            background: #f8f9fa;
            color: #6c757d;
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }
        
        .blog-status {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }
        
        .blog-status.published {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }
        
        .blog-status.draft {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }
        
        .blog-status.scheduled {
            background: rgba(255, 193, 7, 0.1);
            color: #e67e22;
        }
        
        .blog-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }
        
        .blog-action-btn {
            padding: 8px 12px;
            border-radius: 8px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #E6E6E6;
            background: white;
            color: #666;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }
        
        .blog-action-btn:hover {
            transform: translateY(-2px);
        }
        
        .blog-action-btn.btn-view:hover {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }
        
        .blog-action-btn.btn-edit:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }
        
        .blog-action-btn.btn-delete:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }
        
        .btn-disabled {
            background: #6c757d !important;
            color: white !important;
            cursor: not-allowed !important;
            opacity: 0.6;
        }
        
        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        
        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            text-align: center;
        }
        
        .empty-state i {
            font-size: 72px;
            color: #ddd;
            margin-bottom: 20px;
        }
        
        .empty-state h4 {
            color: #0d6efd;
            margin-bottom: 15px;
        }
        
        .empty-state p {
            color: #666;
            margin-bottom: 25px;
        }
        
        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Action Cards */
        .action-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            height: 100%;
            display: flex;
            flex-direction: column;
            position: relative;
            overflow: hidden;
        }

        .action-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(76, 158, 255, 0.02) 0%, rgba(0, 102, 255, 0.02) 100%);
            pointer-events: none;
        }

        .action-card:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 50px rgba(76, 158, 255, 0.15);
        }

        .action-card.ai-card {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border: none;
        }

        .action-card.ai-card::before {
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="ai-pattern" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="1" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23ai-pattern)"/></svg>') repeat;
        }

        .action-icon {
            width: 70px;
            height: 70px;
            border-radius: 20px;
            background: linear-gradient(135deg, #4c9eff, #0066ff);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 30px;
            margin-bottom: 25px;
            position: relative;
            z-index: 1;
            box-shadow: 0 10px 30px rgba(76, 158, 255, 0.3);
        }

        .action-icon.ai-icon {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            animation: pulse-ai 2s infinite;
            box-shadow: 0 10px 30px rgba(255, 107, 107, 0.3);
        }

        @keyframes pulse-ai {
            0% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0.4); }
            70% { box-shadow: 0 0 0 10px rgba(255, 107, 107, 0); }
            100% { box-shadow: 0 0 0 0 rgba(255, 107, 107, 0); }
        }

        .action-content {
            flex: 1;
            display: flex;
            flex-direction: column;
            position: relative;
            z-index: 1;
        }

        .action-title {
            font-size: 22px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 15px;
        }

        .ai-card .action-title {
            color: white;
        }

        .action-description {
            color: #6c757d;
            font-size: 15px;
            line-height: 1.6;
            margin-bottom: 20px;
            flex: 1;
        }

        .ai-card .action-description {
            color: rgba(255, 255, 255, 0.9);
        }

        .action-features {
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
            margin-bottom: 25px;
        }

        .feature-badge {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
            padding: 6px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .feature-badge.ai-badge {
            background: rgba(255, 255, 255, 0.2);
            color: white;
        }

        .action-btn {
            background: linear-gradient(135deg, #4c9eff, #0066ff);
            color: white;
            padding: 15px 25px;
            border-radius: 12px;
            font-weight: 600;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            transition: all 0.3s ease;
            margin-top: auto;
            box-shadow: 0 8px 25px rgba(76, 158, 255, 0.3);
        }

        .action-btn:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(76, 158, 255, 0.4);
            color: white;
            text-decoration: none;
        }

        .action-btn.ai-btn {
            background: linear-gradient(135deg, #ff6b6b, #ee5a24);
            box-shadow: 0 8px 25px rgba(255, 107, 107, 0.3);
        }

        .action-btn.ai-btn:hover {
            box-shadow: 0 12px 35px rgba(255, 107, 107, 0.4);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .mobile-header {
                display: flex !important;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .welcome-banner {
                padding: 25px 20px;
                margin-bottom: 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stats-card {
                padding: 20px;
            }
            
            .filter-section {
                padding: 20px;
            }
            
            .blog-card {
                padding: 20px;
            }
            
            .action-card {
                padding: 25px;
                margin-bottom: 20px;
            }
            
            .action-icon {
                width: 60px;
                height: 60px;
                font-size: 24px;
                margin-bottom: 20px;
            }
            
            .action-title {
                font-size: 20px;
            }
            
            .action-features {
                gap: 8px;
            }
            
            .feature-badge {
                font-size: 11px;
                padding: 5px 10px;
            }
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar-admin.php'; ?>
    
    <!-- Mobile Header (Hidden on Desktop) -->
    <div class="mobile-header">
        <button class="mobile-menu-btn" type="button" onclick="toggleSidebar()">
            <i class="fas fa-bars"></i>
        </button>
        <h5 class="mobile-title">Blog Yönetimi</h5>
        <a href="logout.php" class="mobile-logout">
            <i class="fas fa-sign-out-alt"></i>
        </a>
    </div>
    
    <!-- Dashboard Body -->
    <div class="dashboard-body">
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">
                    <i class="fas fa-blog me-3"></i>Blog Yönetimi
                </h2>
                <p class="welcome-subtitle">Blog yazılarınızı yönetin, düzenleyin ve yayınlayın</p>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($success)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $success ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php if (isset($error)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $error ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <!-- Statistics Section -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-newspaper"></i>
                    </div>
                    <div class="stats-number"><?= $stats['total_posts'] ?></div>
                    <div class="stats-label">Toplam Blog</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>Tüm yazılar</span>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stats-number"><?= number_format($stats['total_views']) ?></div>
                    <div class="stats-label">Toplam Görüntüleme</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>Tüm zamanlar</span>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-check-circle"></i>
                    </div>
                    <div class="stats-number"><?= $stats['published_count'] ?></div>
                    <div class="stats-label">Yayında</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>Aktif yazılar</span>
                    </div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-star"></i>
                    </div>
                    <div class="stats-number"><?= $stats['featured_count'] ?></div>
                    <div class="stats-label">Öne Çıkan</div>
                    <div class="stats-change">
                        <i class="fas fa-arrow-up"></i>
                        <span>Seçili yazılar</span>
                    </div>
                </div>
            </div>

            <!-- Actions Section -->
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h3 class="section-title">
                    <i class="fas fa-list"></i>
                    Blog Yazıları
                </h3>
                <a href="admin-blog-add-new.php" class="btn btn-primary">
                    <i class="fas fa-plus me-2"></i>Yeni Blog Ekle
                </a>
            </div>

            <!-- Quick Actions Section -->
            <div class="row mb-4">
                <div class="col-md-6">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus-circle"></i>
                        </div>
                        <div class="action-content">
                            <h4 class="action-title">Yeni Blog Ekle</h4>
                            <p class="action-description">Manuel olarak yeni bir blog yazısı oluşturun. Başlık, içerik, kategori ve diğer tüm detayları kendiniz belirleyin.</p>
                            <div class="action-features">
                                <span class="feature-badge"><i class="fas fa-edit"></i> Manuel Düzenleme</span>
                                <span class="feature-badge"><i class="fas fa-image"></i> Resim Yükleme</span>
                                <span class="feature-badge"><i class="fas fa-tags"></i> Kategori Seçimi</span>
                            </div>
                            <a href="admin-blog-add-new.php" class="action-btn">
                                <i class="fas fa-arrow-right me-2"></i>
                                Yeni Blog Oluştur
                            </a>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="action-card ai-card">
                        <div class="action-icon ai-icon">
                            <i class="fas fa-robot"></i>
                        </div>
                        <div class="action-content">
                            <h4 class="action-title">Yapay Zeka İle Ekle</h4>
                            <p class="action-description">AI destekli blog yazısı oluşturun. Sadece konu başlığını girin, yapay zeka sizin için profesyonel içerik üretsin.</p>
                            <div class="action-features">
                                <span class="feature-badge ai-badge"><i class="fas fa-magic"></i> AI Üretimi</span>
                                <span class="feature-badge ai-badge"><i class="fas fa-bolt"></i> Hızlı Oluşturma</span>
                                <span class="feature-badge ai-badge"><i class="fas fa-brain"></i> Akıllı İçerik</span>
                            </div>
                            <a href="admin-blog-ai.php" class="action-btn ai-btn">
                                <i class="fas fa-magic me-2"></i>
                                AI ile Oluştur
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            
            <!-- Filter Section -->
            <div class="filter-section">
                <div class="d-flex justify-content-between align-items-center mb-3">
                    <h5 class="filter-title mb-0">
                        <i class="fas fa-filter me-2"></i>
                        Filtrele ve Ara
                    </h5>
                </div>
                
                <form method="GET" class="row g-3">
                    <div class="col-md-4">
                        <select name="status" class="form-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="published" <?= isset($_GET['status']) && $_GET['status'] == 'published' ? 'selected' : '' ?>>Yayında</option>
                            <option value="draft" <?= isset($_GET['status']) && $_GET['status'] == 'draft' ? 'selected' : '' ?>>Taslak</option>
                            <option value="scheduled" <?= isset($_GET['status']) && $_GET['status'] == 'scheduled' ? 'selected' : '' ?>>Zamanlanmış</option>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <select name="category" class="form-select">
                            <option value="">Tüm Kategoriler</option>
                            <?php if ($categories_result && $categories_result->num_rows > 0): ?>
                                <?php while ($category = $categories_result->fetch_assoc()): ?>
                                    <option value="<?= htmlspecialchars($category['category']) ?>" <?= isset($_GET['category']) && $_GET['category'] == $category['category'] ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($category['category']) ?> (<?= $category['count'] ?>)
                                    </option>
                                <?php endwhile; ?>
                            <?php endif; ?>
                        </select>
                    </div>
                    <div class="col-md-4">
                        <div class="input-group">
                            <input type="text" name="search" class="form-control" placeholder="Başlık veya içerik ara..." value="<?= isset($_GET['search']) ? htmlspecialchars($_GET['search']) : '' ?>">
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-search"></i>
                            </button>
                        </div>
                    </div>
                </form>
            </div>

            <!-- Blog Posts List -->
            <div class="row">
                <?php if ($blog_result && $blog_result->num_rows > 0): ?>
                    <?php while ($post = $blog_result->fetch_assoc()): ?>
                        <div class="col-lg-6 col-xl-4">
                            <div class="blog-card <?= $post['status'] ?>">
                                <h5 class="blog-title"><?= htmlspecialchars($post['title']) ?></h5>
                                <div class="blog-meta">
                                    <span>
                                        <i class="fas fa-user"></i>
                                        <?= htmlspecialchars($post['author']) ?>
                                    </span>
                                    <span>
                                        <i class="fas fa-calendar"></i>
                                        <?= date('d.m.Y', strtotime($post['created_at'])) ?>
                                    </span>
                                    <span class="blog-status <?= $post['status'] ?>">
                                        <?= $post['status'] == 'published' ? 'Yayında' : ($post['status'] == 'draft' ? 'Taslak' : 'Zamanlanmış') ?>
                                    </span>
                                </div>
                                
                                <div class="blog-excerpt">
                                    <?= htmlspecialchars(substr(strip_tags($post['excerpt'] ?: $post['content']), 0, 150)) ?>...
                                </div>
                                
                                <?php if (!empty($post['tags'])): ?>
                                    <div class="blog-tags">
                                        <?php foreach (explode(',', $post['tags']) as $tag): ?>
                                            <span class="blog-tag"><?= htmlspecialchars(trim($tag)) ?></span>
                                        <?php endforeach; ?>
                                    </div>
                                <?php endif; ?>
                                
                                <div class="blog-actions">
                                    <?php if ($post['status'] == 'published'): ?>
                                        <a href="../blog<?= $post['id'] ?>.php" target="_blank" class="blog-action-btn btn-view">
                                            <i class="fas fa-eye"></i>
                                            Görüntüle
                                        </a>
                                    <?php else: ?>
                                        <span class="blog-action-btn btn-disabled">
                                            <i class="fas fa-eye-slash"></i>
                                            Görüntüle (Yayında değil)
                                        </span>
                                    <?php endif; ?>
                                    <a href="admin-blog-add-new.php?edit=<?= $post['id'] ?>" class="blog-action-btn btn-edit">
                                        <i class="fas fa-edit"></i>
                                        Düzenle
                                    </a>
                                    
                                    <?php if ($post['status'] != 'published'): ?>
                                        <a href="?action=change_status&id=<?= $post['id'] ?>&status=published" 
                                           class="blog-action-btn btn-view"
                                           onclick="return confirm('Bu yazıyı yayınlamak istediğinizden emin misiniz?')">
                                            <i class="fas fa-globe"></i>
                                            Yayınla
                                        </a>
                                    <?php else: ?>
                                        <a href="?action=change_status&id=<?= $post['id'] ?>&status=draft" 
                                           class="blog-action-btn btn-edit"
                                           onclick="return confirm('Bu yazıyı taslağa çevirmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-pencil-alt"></i>
                                            Taslak
                                        </a>
                                    <?php endif; ?>
                                    
                                    <a href="?action=delete&id=<?= $post['id'] ?>" 
                                       class="blog-action-btn btn-delete"
                                       onclick="return confirm('Bu blog yazısını silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                                        <i class="fas fa-trash"></i>
                                        Sil
                                    </a>
                                </div>
                            </div>
                        </div>
                    <?php endwhile; ?>
                <?php else: ?>
                    <div class="col-12">
                        <div class="empty-state">
                            <i class="fas fa-newspaper"></i>
                            <h4>Henüz blog yazısı bulunmuyor</h4>
                            <p>İlk blog yazınızı ekleyerek başlayın!</p>
                            <a href="admin-blog-add-new.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>
                                Yeni Blog Yazısı Ekle
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Auto-dismiss alerts
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (bootstrap.Alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Auto-submit filters on change
        document.querySelectorAll('.filter-section select').forEach(select => {
            select.addEventListener('change', function() {
                this.closest('form').submit();
            });
        });

        // Stats cards animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.opacity = '1';
                    entry.target.style.transform = 'translateY(0)';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stats-card, .blog-card').forEach(card => {
            card.style.opacity = '0';
            card.style.transform = 'translateY(20px)';
            card.style.transition = 'all 0.6s ease';
            observer.observe(card);
        });
    </script>
</body>
</html>
