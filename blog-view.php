<?php
session_start();
include 'db.php';

// Blog ID veya slug al
$blog_id = isset($_GET['id']) ? intval($_GET['id']) : null;
$blog_slug = isset($_GET['slug']) ? $_GET['slug'] : null;

if (!$blog_id && !$blog_slug) {
    header("Location: blog.php");
    exit;
}

// Blog yazısını getir
if ($blog_id) {
    $blog_query = $conn->prepare("SELECT * FROM blogs WHERE id = ? AND status = 'published'");
    $blog_query->bind_param("i", $blog_id);
} else {
    $blog_query = $conn->prepare("SELECT * FROM blogs WHERE slug = ? AND status = 'published'");
    $blog_query->bind_param("s", $blog_slug);
}

$blog_query->execute();
$blog_result = $blog_query->get_result();

if ($blog_result->num_rows == 0) {
    header("Location: blog.php");
    exit;
}

$blog = $blog_result->fetch_assoc();

// Görüntülenme sayısını artır
$view_update = $conn->prepare("UPDATE blogs SET views = views + 1 WHERE id = ?");
$view_update->bind_param("i", $blog['id']);
$view_update->execute();

// Kategorileri getir
$categories_query = $conn->prepare("
    SELECT c.name FROM blog_categories c 
    JOIN blog_category_relations bcr ON c.id = bcr.category_id 
    WHERE bcr.blog_id = ?
");
$categories_query->bind_param("i", $blog['id']);
$categories_query->execute();
$categories_result = $categories_query->get_result();
$categories = [];
while ($cat = $categories_result->fetch_assoc()) {
    $categories[] = $cat['name'];
}

// Etiketleri getir
$tags_query = $conn->prepare("
    SELECT t.name FROM blog_tags t 
    JOIN blog_tag_relations btr ON t.id = btr.tag_id 
    WHERE btr.blog_id = ?
");
$tags_query->bind_param("i", $blog['id']);
$tags_query->execute();
$tags_result = $tags_query->get_result();
$tags = [];
while ($tag = $tags_result->fetch_assoc()) {
    $tags[] = $tag['name'];
}

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Tarih formatla
$formatted_date = date('d M Y', strtotime($blog['publish_date'] ?? $blog['created_at']));

// SEO için meta bilgileri
$meta_title = $blog['meta_title'] ?: $blog['title'] . ' - Gökhan Aydınlı Gayrimenkul';
$meta_description = $blog['meta_description'] ?: $blog['excerpt'];
$meta_keywords = $blog['meta_keywords'] ?: implode(', ', $tags);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="<?= htmlspecialchars($meta_keywords) ?>">
    <meta name="description" content="<?= htmlspecialchars($meta_description) ?>">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="<?= htmlspecialchars($blog['title']) ?>">
    <meta property="og:description" content="<?= htmlspecialchars($blog['excerpt']) ?>">
    <meta property="og:image" content="<?= htmlspecialchars($blog['featured_image'] ?: 'images/assets/blog-og.png') ?>">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title><?= htmlspecialchars($meta_title) ?></title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Google Fonts -->
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;500;600;700&display=swap" rel="stylesheet">
    
    <!-- Blog özel stilleri -->
    <style>
        .blog-content {
            font-family: 'Inter', sans-serif;
            font-size: 1.1rem;
            line-height: 1.8;
            color: #2c3e50;
        }
        .blog-content h1, .blog-content h2, .blog-content h3, 
        .blog-content h4, .blog-content h5, .blog-content h6 {
            color: #2c3e50;
            margin-top: 2em;
            margin-bottom: 0.8em;
            font-weight: 600;
        }
        .blog-content h1 { font-size: 2.5em; }
        .blog-content h2 { font-size: 2em; }
        .blog-content h3 { font-size: 1.75em; }
        .blog-content h4 { font-size: 1.5em; }
        .blog-content h5 { font-size: 1.25em; }
        .blog-content h6 { font-size: 1.1em; }
        .blog-content p { margin-bottom: 1.2em; }
        .blog-content ul, .blog-content ol { 
            padding-left: 2em; 
            margin: 1em 0; 
        }
        .blog-content li { margin-bottom: 0.5em; }
        .blog-content blockquote {
            border-left: 4px solid #3498db;
            padding: 1em 1.5em;
            margin: 1.5em 0;
            font-style: italic;
            color: #7f8c8d;
            background: #f8f9fa;
            border-radius: 0 5px 5px 0;
        }
        .blog-content table {
            border-collapse: collapse;
            width: 100%;
            margin: 1.5em 0;
            border: 1px solid #ddd;
        }
        .blog-content th, .blog-content td {
            border: 1px solid #ddd;
            padding: 12px;
            text-align: left;
        }
        .blog-content th {
            background-color: #f8f9fa;
            font-weight: 600;
            color: #2c3e50;
        }
        .blog-content img {
            max-width: 100%;
            height: auto;
            border-radius: 8px;
            margin: 1em 0;
            box-shadow: 0 2px 8px rgba(0,0,0,0.1);
        }
        .blog-content code {
            background: #f4f4f4;
            padding: 2px 6px;
            border-radius: 3px;
            font-family: 'Courier New', monospace;
            font-size: 0.9em;
            color: #e74c3c;
        }
        .blog-content pre {
            background: #f8f9fa;
            padding: 1em;
            border-radius: 5px;
            overflow-x: auto;
            border-left: 4px solid #3498db;
            font-family: 'Courier New', monospace;
        }
        .blog-content mark {
            background: #fff3cd;
            padding: 2px 4px;
            border-radius: 3px;
        }
        .blog-content hr {
            border: none;
            height: 2px;
            background: linear-gradient(to right, #3498db, transparent);
            margin: 2em 0;
        }
        .blog-meta-info {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 10px;
            margin-bottom: 30px;
            border-left: 4px solid #3498db;
        }
        .category-tags {
            margin-top: 30px;
            padding-top: 20px;
            border-top: 1px solid #e9ecef;
        }
        .category-tag, .blog-tag {
            display: inline-block;
            background: #e9ecef;
            color: #495057;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 0.9em;
            margin: 5px 5px 5px 0;
            text-decoration: none;
        }
        .category-tag:hover, .blog-tag:hover {
            background: #3498db;
            color: white;
            text-decoration: none;
        }
        .share-buttons {
            position: sticky;
            top: 100px;
            background: white;
            padding: 20px;
            border-radius: 10px;
            box-shadow: 0 2px 15px rgba(0,0,0,0.1);
        }
        .share-btn {
            display: block;
            width: 100%;
            padding: 10px;
            margin: 5px 0;
            border: none;
            border-radius: 5px;
            text-align: center;
            text-decoration: none;
            color: white;
            font-weight: 500;
        }
        .share-whatsapp { background: #25D366; }
        .share-twitter { background: #1DA1F2; }
        .share-linkedin { background: #0077B5; }
        .share-facebook { background: #4267B2; }
        .blog-stats {
            display: flex;
            gap: 20px;
            align-items: center;
            color: #7f8c8d;
            font-size: 0.9em;
            margin: 15px 0;
        }
        .blog-stat-item {
            display: flex;
            align-items: center;
            gap: 5px;
        }
    </style>
    
    <!-- Modal CSS -->
    <?php if (file_exists('includes/modal-css.php')) include 'includes/modal-css.php'; ?>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Loading Transition -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
            </div>
        </div>

        <!-- Theme Main Menu -->
        <header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="index.php" class="d-flex align-items-center">
                                <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
                            </a>
                        </div>
                        <!-- Header'da Giriş butonu -->
                        <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                    <li class="dropdown">
                                        <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="dashboard.php">Panel</a></li>
                                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
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
                        <nav class="navbar navbar-expand-lg p0 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
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

        <!-- İç Banner -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15"><?= htmlspecialchars($blog['title']) ?></h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="blog.php">Blog</a></li>
                    <li>/</li>
                    <li><?= htmlspecialchars(substr($blog['title'], 0, 30)) ?>...</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Blog Details -->
        <div class="blog-details border-top mt-130 xl-mt-100 pt-100 xl-pt-80 mb-150 xl-mb-100">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <div class="blog-post-meta mb-60 lg-mb-40">
                            <div class="blog-meta-info">
                                <h1 class="blog-title mb-3"><?= htmlspecialchars($blog['title']) ?></h1>
                                <div class="blog-stats">
                                    <div class="blog-stat-item">
                                        <i class="fa-solid fa-user"></i>
                                        <span><?= htmlspecialchars($blog['author'] ?: 'Gökhan Aydınlı') ?></span>
                                    </div>
                                    <div class="blog-stat-item">
                                        <i class="fa-solid fa-calendar"></i>
                                        <span><?= $formatted_date ?></span>
                                    </div>
                                    <div class="blog-stat-item">
                                        <i class="fa-solid fa-clock"></i>
                                        <span><?= $blog['reading_time'] ?> dk okuma</span>
                                    </div>
                                    <div class="blog-stat-item">
                                        <i class="fa-solid fa-eye"></i>
                                        <span><?= number_format($blog['views']) ?> görüntülenme</span>
                                    </div>
                                </div>
                                <p class="excerpt mb-0"><?= htmlspecialchars($blog['excerpt']) ?></p>
                            </div>
                        </div>
                    </div>
                </div>
                
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-post-meta">
                            <?php if ($blog['featured_image']): ?>
                                <figure class="post-img position-relative m0 mb-4">
                                    <img src="<?= htmlspecialchars($blog['featured_image']) ?>" 
                                         alt="<?= htmlspecialchars($blog['title']) ?>" 
                                         class="w-100" style="border-radius: 10px;">
                                </figure>
                            <?php endif; ?>
                            
                            <div class="blog-content">
                                <?= $blog['content'] ?>
                            </div>
                            
                            <?php if (!empty($categories) || !empty($tags)): ?>
                                <div class="category-tags">
                                    <?php if (!empty($categories)): ?>
                                        <h6><i class="fa-solid fa-folder"></i> Kategoriler:</h6>
                                        <?php foreach ($categories as $category): ?>
                                            <a href="blog.php?category=<?= urlencode($category) ?>" class="category-tag">
                                                <?= htmlspecialchars($category) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($tags)): ?>
                                        <h6 class="mt-3"><i class="fa-solid fa-tags"></i> Etiketler:</h6>
                                        <?php foreach ($tags as $tag): ?>
                                            <a href="blog.php?tag=<?= urlencode($tag) ?>" class="blog-tag">
                                                <?= htmlspecialchars($tag) ?>
                                            </a>
                                        <?php endforeach; ?>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                            
                            <div class="bottom-widget d-sm-flex align-items-center justify-content-between mt-4 pt-4" style="border-top: 1px solid #e9ecef;">
                                <ul class="d-flex share-icon align-items-center style-none">
                                    <li><strong>Paylaş:</strong></li>
                                    <li><a href="https://api.whatsapp.com/send?text=<?= urlencode($blog['title'] . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fa-brands fa-x-twitter"></i></a></li>
                                    <li><a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li>
                                    <li><a href="https://www.facebook.com/sharer/sharer.php?u=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" target="_blank"><i class="fa-brands fa-facebook"></i></a></li>
                                </ul>
                                
                                <div class="text-end">
                                    <a href="blog.php" class="btn btn-outline-primary">
                                        <i class="fa-solid fa-arrow-left"></i> Tüm Blog Yazıları
                                    </a>
                                </div>
                            </div>
                        </article>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="blog-sidebar ps-xl-5 md-mt-60">
                            <!-- Share Buttons -->
                            <div class="share-buttons mb-4">
                                <h6 class="mb-3"><i class="fa-solid fa-share-nodes"></i> Paylaş</h6>
                                <a href="https://api.whatsapp.com/send?text=<?= urlencode($blog['title'] . ' - ' . 'https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                   target="_blank" class="share-btn share-whatsapp">
                                    <i class="fa-brands fa-whatsapp"></i> WhatsApp'ta Paylaş
                                </a>
                                <a href="https://twitter.com/intent/tweet?text=<?= urlencode($blog['title']) ?>&url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                   target="_blank" class="share-btn share-twitter">
                                    <i class="fa-brands fa-x-twitter"></i> Twitter'da Paylaş
                                </a>
                                <a href="https://www.linkedin.com/sharing/share-offsite/?url=<?= urlencode('https://' . $_SERVER['HTTP_HOST'] . $_SERVER['REQUEST_URI']) ?>" 
                                   target="_blank" class="share-btn share-linkedin">
                                    <i class="fa-brands fa-linkedin"></i> LinkedIn'de Paylaş
                                </a>
                            </div>
                            
                            <!-- Search -->
                            <div class="blog-sidebar-banner text-center mb-55 md-mb-40">
                                <h4 class="mb-20">Blog Ara</h4>
                                <form action="blog.php" method="GET">
                                    <input type="text" name="search" placeholder="Arama yapın...">
                                    <button type="submit"><i class="bi bi-search"></i></button>
                                </form>
                            </div>
                            
                            <!-- Recent Posts -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">Son Yazılar</h4>
                                <ul class="style-none">
                                    <?php
                                    $recent_query = "SELECT id, title, slug, featured_image, publish_date, created_at 
                                                    FROM blogs 
                                                    WHERE status = 'published' AND id != ? 
                                                    ORDER BY publish_date DESC, created_at DESC 
                                                    LIMIT 5";
                                    $recent_stmt = $conn->prepare($recent_query);
                                    $recent_stmt->bind_param("i", $blog['id']);
                                    $recent_stmt->execute();
                                    $recent_result = $recent_stmt->get_result();
                                    
                                    while ($recent_blog = $recent_result->fetch_assoc()):
                                        $recent_date = date('d M Y', strtotime($recent_blog['publish_date'] ?? $recent_blog['created_at']));
                                        $recent_url = $recent_blog['slug'] ? "blog-view.php?slug=" . $recent_blog['slug'] : "blog-view.php?id=" . $recent_blog['id'];
                                    ?>
                                        <li>
                                            <div class="news-block d-flex align-items-center pt-20 pb-20 border-bottom">
                                                <div>
                                                    <img src="<?= htmlspecialchars($recent_blog['featured_image'] ?: 'images/blog/blog_img_01.jpg') ?>" 
                                                         alt="<?= htmlspecialchars($recent_blog['title']) ?>" class="lazy-img">
                                                </div>
                                                <div class="post-data ps-4">
                                                    <h4><a href="<?= $recent_url ?>"><?= htmlspecialchars($recent_blog['title']) ?></a></h4>
                                                    <div class="date"><?= $recent_date ?></div>
                                                </div>
                                            </div>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                            
                            <!-- Categories -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">Kategoriler</h4>
                                <ul class="style-none">
                                    <?php
                                    $cat_query = "SELECT c.name, COUNT(bcr.blog_id) as count 
                                                 FROM blog_categories c 
                                                 LEFT JOIN blog_category_relations bcr ON c.id = bcr.category_id 
                                                 LEFT JOIN blogs b ON bcr.blog_id = b.id AND b.status = 'published'
                                                 GROUP BY c.id, c.name 
                                                 HAVING count > 0 
                                                 ORDER BY count DESC";
                                    $cat_result = $conn->query($cat_query);
                                    while ($cat = $cat_result->fetch_assoc()):
                                    ?>
                                        <li>
                                            <a href="blog.php?category=<?= urlencode($cat['name']) ?>">
                                                <?= htmlspecialchars($cat['name']) ?> (<?= $cat['count'] ?>)
                                            </a>
                                        </li>
                                    <?php endwhile; ?>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-four position-relative z-1">
            <div class="container container-large">
                <div class="bg-wrapper position-relative z-1">
                    <div class="row">
                        <div class="col-xxl-3 col-lg-4 mb-60">
                            <div class="footer-intro">
                                <div class="logo mb-20">
                                    <a href="index.php">
                                        <img src="images/logoSiyah.png" alt="">
                                    </a>
                                </div>
                                <p class="mb-30 xs-mb-20">Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
                                <a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 ms-auto mb-30">
                            <div class="footer-nav ps-xl-5">
                                <h5 class="footer-title">Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetlerimiz</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="portfoy.php">Ticari Gayrimenkul</a></li>
                                    <li><a href="portfoy.php">Konut Satışı</a></li>
                                    <li><a href="portfoy.php">Ev Kiralama</a></li>
                                    <li><a href="contact.php">Yatırım Danışmanlığı</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>

        <!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="container">
                    <div class="user-data-form modal-content">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                        <div class="form-wrapper m-auto">
                            <ul class="nav nav-tabs w-100" role="tablist">
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş</button>
                                </li>
                                <li class="nav-item" role="presentation">
                                    <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Kayıt</button>
                                </li>
                            </ul>
                            <div class="tab-content mt-30">
                                <div class="tab-pane show active" role="tabpanel" id="fc1">
                                    <div class="text-center mb-20">
                                        <h2>Hoş Geldiniz!</h2>
                                        <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#" onclick="switchToRegister()">Kayıt olun</a></p>
                                    </div>
                                    <form action="login.php" method="POST" id="loginForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta*</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="fc2">
                                    <div class="text-center mb-20">
                                        <h2>Kayıt Ol</h2>
                                        <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="switchToLogin()">Giriş yapın</a></p>
                                    </div>
                                    <form action="register.php" method="POST" id="registerForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Ad Soyad*</label>
                                                    <input type="text" name="fullname" placeholder="Ad Soyadınız" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta*</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="scroll-top">
            <i class="bi bi-arrow-up-short"></i>
        </button>

        <!-- JS -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/main.js"></script>
        
        <!-- Modal JS -->
        <?php if (file_exists('includes/modal-js.php')) include 'includes/modal-js.php'; ?>
        
        <script>
            function switchToRegister() {
                document.querySelector('[data-bs-target="#fc2"]').click();
            }
            function switchToLogin() {
                document.querySelector('[data-bs-target="#fc1"]').click();
            }
        </script>
    </div>
</body>
</html>
