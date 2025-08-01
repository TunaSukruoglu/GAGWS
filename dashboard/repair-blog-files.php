<?php
session_start();
include '../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || $_SESSION['role'] !== 'admin') {
    header("Location: ../index.php");
    exit;
}

echo "<h2>🔧 Blog Dosyası Toplu Onarım Aracı</h2>";

// Tüm published blog yazılarını al
$published_blogs = $conn->query("SELECT id, title, slug, blog_file, status FROM blogs WHERE status = 'published' ORDER BY id DESC");

if ($published_blogs->num_rows > 0) {
    echo "<h3>📋 Yayınlanmış Blog Yazıları:</h3>";
    
    $missing_files = [];
    $existing_files = [];
    
    while ($blog = $published_blogs->fetch_assoc()) {
        $file_status = "❌ Dosya yok";
        $action_button = "";
        
        if ($blog['blog_file']) {
            $file_path = "../{$blog['blog_file']}";
            if (file_exists($file_path)) {
                $file_status = "✅ Dosya mevcut";
                $existing_files[] = $blog;
                $action_button = "<a href='{$blog['blog_file']}' target='_blank' style='padding: 5px 10px; background: green; color: white; text-decoration: none; border-radius: 3px; font-size: 12px;'>Aç</a>";
            } else {
                $file_status = "❌ Dosya kayıp";
                $missing_files[] = $blog;
                $action_button = "<button onclick='recreateFile({$blog['id']})' style='padding: 5px 10px; background: orange; color: white; border: none; border-radius: 3px; font-size: 12px; cursor: pointer;'>Yeniden Oluştur</button>";
            }
        } else {
            $file_status = "⚠️ Dosya ismi yok";
            $missing_files[] = $blog;
            $action_button = "<button onclick='createFile({$blog['id']})' style='padding: 5px 10px; background: purple; color: white; border: none; border-radius: 3px; font-size: 12px; cursor: pointer;'>Oluştur</button>";
        }
        
        echo "<div style='border: 1px solid #ddd; padding: 10px; margin: 5px 0; border-radius: 5px; display: flex; justify-content: space-between; align-items: center;'>";
        echo "<div>";
        echo "<strong>#{$blog['id']}</strong> - " . htmlspecialchars($blog['title']);
        echo "<br><small>Dosya: " . ($blog['blog_file'] ? $blog['blog_file'] : 'Tanımsız') . "</small>";
        echo "</div>";
        echo "<div style='text-align: right;'>";
        echo "<div>{$file_status}</div>";
        echo "<div style='margin-top: 5px;'>{$action_button}</div>";
        echo "</div>";
        echo "</div>";
    }
    
    // Özet bilgiler
    echo "<hr>";
    echo "<h3>📊 Özet:</h3>";
    echo "<p>✅ <strong>Dosyası mevcut:</strong> " . count($existing_files) . " blog</p>";
    echo "<p>❌ <strong>Dosyası eksik:</strong> " . count($missing_files) . " blog</p>";
    
    if (count($missing_files) > 0) {
        echo "<button onclick='fixAllMissing()' style='padding: 15px 30px; background: #dc3545; color: white; border: none; border-radius: 5px; font-size: 16px; cursor: pointer; margin: 10px 0;'>";
        echo "🔧 Tüm Eksik Dosyaları Oluştur (" . count($missing_files) . " adet)";
        echo "</button>";
    }
    
} else {
    echo "<p>Henüz yayınlanmış blog yazısı yok.</p>";
}

// Toplu işlem yapıldıysa
if (isset($_POST['fix_all'])) {
    $fixed_count = 0;
    $missing_blogs = $conn->query("SELECT * FROM blogs WHERE status = 'published' AND (blog_file IS NULL OR blog_file = '')");
    
    while ($blog = $missing_blogs->fetch_assoc()) {
        // Benzersiz dosya ismi oluştur
        $base_slug = $blog['slug'];
        $timestamp = date('Ymd-His') . '-' . $blog['id'];
        $unique_filename = "blog-{$base_slug}-{$blog['id']}-{$timestamp}.php";
        
        $counter = 1;
        while (file_exists("../{$unique_filename}")) {
            $unique_filename = "blog-{$base_slug}-{$blog['id']}-{$timestamp}-{$counter}.php";
            $counter++;
        }
        
        $filename = "../{$unique_filename}";
        $template = generateBlogTemplate($blog, $unique_filename);
        
        if (file_put_contents($filename, $template)) {
            // Veritabanında güncelle
            $update_stmt = $conn->prepare("UPDATE blogs SET blog_file = ? WHERE id = ?");
            $update_stmt->bind_param("si", $unique_filename, $blog['id']);
            $update_stmt->execute();
            $fixed_count++;
        }
    }
    
    echo "<div style='background: green; color: white; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
    echo "✅ {$fixed_count} blog dosyası başarıyla oluşturuldu!";
    echo "</div>";
    
    // Sayfayı yenile
    echo "<script>setTimeout(() => location.reload(), 2000);</script>";
}

// Blog template oluşturma fonksiyonu
function generateBlogTemplate($blog, $filename = '') {
    $formatted_date = date('d M Y', strtotime($blog['publish_date'] ?? $blog['created_at']));
    
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
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="' . htmlspecialchars($blog['title']) . '">
    <meta property="og:description" content="' . htmlspecialchars($blog['excerpt']) . '">
    <meta property="og:image" content="' . htmlspecialchars($blog['featured_image']) . '">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>' . htmlspecialchars($blog['title']) . ' | Gökhan Aydınlı Gayrimenkul</title>
    
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="css/style.min.css">
    <link rel="stylesheet" type="text/css" href="css/responsive.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .blog-details-one { padding: 120px 0; }
        .blog-meta-wrapper { background: #fff; border-radius: 20px; padding: 40px; box-shadow: 0 10px 30px rgba(0,0,0,0.1); }
        .blog-title h1 { color: #1f2937; font-size: 2.5rem; font-weight: 700; line-height: 1.3; margin-bottom: 20px; }
        .post-date { color: #6b7280; font-size: 16px; margin-bottom: 30px; padding: 10px 0; border-bottom: 2px solid #e5e7eb; }
        .featured-image { width: 100%; height: 400px; object-fit: cover; border-radius: 15px; margin: 30px 0; }
        .blog-content { font-size: 18px; line-height: 1.8; color: #374151; }
        .blog-content h3, .blog-content h4, .blog-content h5 { color: #1f2937; font-weight: 700; margin: 30px 0 15px 0; }
        .blog-content p { margin-bottom: 20px; }
        .blog-content ul, .blog-content ol { margin: 20px 0; padding-left: 30px; }
        .blog-content li { margin-bottom: 10px; }
        .file-info { background: #f8f9fa; padding: 10px; border-radius: 5px; font-size: 12px; color: #6c757d; text-align: center; margin-bottom: 20px; }
    </style>
</head>

<body>
    <div class="main-page-wrapper">        
        <div class="blog-details-one pt-180 lg-pt-150 pb-150 xl-pb-120">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-meta-wrapper pe-xxl-5">
                            ' . ($filename ? '<div class="file-info">📄 Dosya: ' . htmlspecialchars($filename) . ' | 🆔 Blog ID: ' . $blog['id'] . ' | 🔧 Otomatik oluşturuldu</div>' : '') . '
                            
                            <div class="blog-title">
                                <h1>' . htmlspecialchars($blog['title']) . '</h1>
                            </div>
                            <div class="post-date">
                                <strong>Gökhan Aydınlı</strong> • ' . $formatted_date . ' • ' . ($blog['reading_time'] ?? 5) . ' dk okuma
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

<script>
function recreateFile(blogId) {
    if (confirm('Bu blog dosyasını yeniden oluşturmak istediğinizden emin misiniz?')) {
        // AJAX ile dosya oluştur
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'recreate_file=' + blogId
        }).then(() => {
            location.reload();
        });
    }
}

function createFile(blogId) {
    if (confirm('Bu blog için dosya oluşturmak istediğinizden emin misiniz?')) {
        // AJAX ile dosya oluştur
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'create_missing_file=' + blogId
        }).then(() => {
            location.reload();
        });
    }
}

function fixAllMissing() {
    if (confirm('Tüm eksik blog dosyalarını oluşturmak istediğinizden emin misiniz?')) {
        // AJAX ile tümünü oluştur
        fetch('', {
            method: 'POST',
            headers: {'Content-Type': 'application/x-www-form-urlencoded'},
            body: 'fix_all=1'
        }).then(() => {
            location.reload();
        });
    }
}

// Tekil dosya oluşturma işlemleri
if (typeof URLSearchParams !== 'undefined') {
    const urlParams = new URLSearchParams(window.location.search);
    const recreate = urlParams.get('recreate_file');
    const create = urlParams.get('create_missing_file');
    
    if (recreate || create) {
        setTimeout(() => {
            location.href = location.pathname;
        }, 2000);
    }
}
</script>

<hr>
<a href='admin-blog.php' style='padding: 10px; background: blue; color: white; text-decoration: none; margin: 5px;'>Blog Yönetimi</a>
<a href='debug-blogs.php' style='padding: 10px; background: green; color: white; text-decoration: none; margin: 5px;'>Blog Debug</a>
<a href='../blog.php' target='_blank' style='padding: 10px; background: purple; color: white; text-decoration: none; margin: 5px;'>Halk Blog Sayfası</a>
