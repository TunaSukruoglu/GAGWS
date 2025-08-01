<?php
session_start();
include '../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

echo "<h2>🚀 Blog Yazıları Hızlı Yayınlama</h2>";

if (isset($_POST['publish_all'])) {
    // Tüm draft blog yazılarını published yap
    $publish_query = "UPDATE blogs SET status = 'published', publish_date = NOW() WHERE status = 'draft'";
    
    if ($conn->query($publish_query)) {
        $affected = $conn->affected_rows;
        echo "<div style='background: green; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "✅ {$affected} blog yazısı başarıyla yayınlandı!";
        echo "</div>";
        
        // Her yayınlanan blog için dosya oluştur
        $published_blogs = $conn->query("SELECT * FROM blogs WHERE status = 'published'");
        while ($blog = $published_blogs->fetch_assoc()) {
            $blog_id = $blog['id'];
            $filename = "../blog{$blog_id}.php";
            
            if (!file_exists($filename)) {
                $template = generateBlogTemplate($blog);
                file_put_contents($filename, $template);
                echo "<p>📄 blog{$blog_id}.php dosyası oluşturuldu</p>";
            }
        }
        
    } else {
        echo "<div style='background: red; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "❌ Hata oluştu: " . $conn->error;
        echo "</div>";
    }
}

// Mevcut durum
$draft_count = $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'draft'")->fetch_assoc();
$published_count = $conn->query("SELECT COUNT(*) as count FROM blogs WHERE status = 'published'")->fetch_assoc();

echo "<h3>📊 Mevcut Durum:</h3>";
echo "<p>📝 Taslak blog sayısı: <strong>{$draft_count['count']}</strong></p>";
echo "<p>🚀 Yayınlanmış blog sayısı: <strong>{$published_count['count']}</strong></p>";

if ($draft_count['count'] > 0) {
    echo "<form method='POST'>";
    echo "<button type='submit' name='publish_all' style='background: #28a745; color: white; padding: 15px 30px; border: none; border-radius: 5px; font-size: 16px; cursor: pointer;'>";
    echo "🚀 Tüm Taslakları Yayınla ({$draft_count['count']} adet)";
    echo "</button>";
    echo "</form>";
    
    echo "<p style='color: #666; margin-top: 10px;'>";
    echo "⚠️ Bu işlem tüm taslak blog yazılarınızı anında yayınlayacaktır.";
    echo "</p>";
} else {
    echo "<p style='color: green;'>✅ Tüm blog yazıları zaten yayınlanmış!</p>";
}

// Blog template fonksiyonu
function generateBlogTemplate($blog) {
    $formatted_date = date('d M Y', strtotime($blog['publish_date']));
    
    $template = '<?php
session_start();
include \'includes/session-check.php\';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="' . htmlspecialchars($blog['meta_title'] ?? $blog['title']) . '">
    <meta name="description" content="' . htmlspecialchars($blog['meta_description'] ?? $blog['excerpt']) . '">
    <title>' . htmlspecialchars($blog['title']) . ' | Gökhan Aydınlı Gayrimenkul</title>
    
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.min.css">
    <link rel="stylesheet" type="text/css" href="css/responsive.css">
</head>

<body>
    <div class="main-page-wrapper">        
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
                </div>
            </div>
        </div>
    </div>
</body>
</html>';

    return $template;
}

echo "<hr>";
echo "<a href='debug-blogs.php' style='padding: 10px; background: blue; color: white; text-decoration: none; margin: 5px;'>Durumu Kontrol Et</a>";
echo "<a href='admin-blog.php' style='padding: 10px; background: green; color: white; text-decoration: none; margin: 5px;'>Blog Yönetimi</a>";
echo "<a href='../blog.php' target='_blank' style='padding: 10px; background: purple; color: white; text-decoration: none; margin: 5px;'>Halk Blog Sayfası</a>";
?>
