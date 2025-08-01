<?php
// Simple blog page with no redirection prevention - focus on displaying content
session_start();

// Türkçe tarih formatı için yardımcı fonksiyon
function formatTurkishDate($dateString) {
    $date = new DateTime($dateString);
    $monthsInTurkish = [
        1 => 'Ocak', 2 => 'Şubat', 3 => 'Mart', 4 => 'Nisan', 
        5 => 'Mayıs', 6 => 'Haziran', 7 => 'Temmuz', 8 => 'Ağustos',
        9 => 'Eylül', 10 => 'Ekim', 11 => 'Kasım', 12 => 'Aralık'
    ];
    
    return $date->format('d') . ' ' . $monthsInTurkish[(int)$date->format('m')] . ' ' . $date->format('Y');
}

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında session-check'i include et
try {
    include 'includes/session-check.php';
} catch (Exception $e) {
    // Session check dosyası yoksa devam et
}

// Sayfalama için değişkenler
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6; // Her sayfada 6 blog göster
$offset = ($page - 1) * $per_page;

// Statik blog verilerini kullan
$all_blog_posts = [
    [
        'id' => 1,
        'title' => 'Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler',
        'content' => 'İyi hazırlanmış bir sözleşme, gelecekteki anlaşmazlıkların %90 ını önler. Detaylar şeytanda gizlidir.',
        'excerpt' => 'YSözleşme İmzalamadan Önce Kontrol Listesi',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1450101499163-c8848c66ca85?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'created_at' => '2024-07-01 10:30:00',
        'categories' => 'Yatırım',
        'tags' => 'gayrimenkul,yatırım,2024,trend',
        'blog_file' => 'blog2.php',
        'reading_time' => '5 dk',
        'slug' => 'blog1.php'
    ],
    [
        'id' => 2,
        'title' => 'İstanbul\'da Ev Almak İçin En İyi Bölgeler',
        'content' => 'İstanbul\'un farklı bölgelerinde yaşam kalitesi, ulaşım imkanları ve fiyat avantajları analizi.',
        'excerpt' => 'İstanbul\'da ev almak isteyenler için en uygun bölgelerin detaylı incelemesi.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80',
        'created_at' => '2024-06-25 14:15:00',
        'categories' => 'Bölge Analizi',
        'tags' => 'istanbul,konut,bölge,analiz',
        'blog_file' => 'blog1.php',
        'reading_time' => '7 dk',
        'slug' => 'blog2.php'
    ],
    [
        'id' => 3,
        'title' => '2024 İstanbul Ticari Gayrimenkul Piyasası: Analiz ve Öngörüler',
        'content' => '2024 İstanbul Ticari Gayrimenkul Piyasası: Analiz ve Öngörüler',
        'excerpt' => 'Bölgesel Analiz: Hangi Bölgeler Öne Çıkıyor?',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'created_at' => '2024-06-18 09:45:00',
        'categories' => 'Kiralama',
        'tags' => 'kiralık,ev,seçim,ipucu',
        'blog_file' => 'blog3.php',
        'reading_time' => '4 dk',
        'slug' => 'blog3.php'
    ],
    [
        'id' => 4,
        'title' => 'Ticari Gayrimenkul Yatırım Rehberi',
        'content' => 'Ticari Gayrimenkul Yatırım Rehberi',
        'excerpt' => 'Ticari Gayrimenkul Yatırım Rehberi',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'created_at' => '2024-06-10 16:20:00',
        'categories' => 'Ticari',
        'tags' => 'ofis,kiralama,ticari,işletme',
        'blog_file' => 'blog7.php',
        'reading_time' => '6 dk',
        'slug' => 'blog4.php'
    ],
    [
        'id' => 5,
        'title' => 'Emlak Sektöründe Yaşanan Dijital Dönüşümün Derinlemesine Analizi ve Gelecek Teknolojileri',
        'content' => 'Emlak Sektöründe Yaşanan Dijital Dönüşümün Derinlemesine Analizi ve Gelecek Teknolojileri',
        'excerpt' => 'Dijital dönüşüm, emlak sektöründe sadece bir trend değil, kalıcı bir değişim. ',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2071&q=80',
        'created_at' => '2024-06-05 11:30:00',
        'categories' => 'Değerleme',
        'tags' => 'değerleme,analiz,piyasa,gayrimenkul',
        'blog_file' => 'blog5.php',
        'reading_time' => '5 dk',
        'slug' => 'blog5.php'
    ],
    [
        'id' => 6,
        'title' => 'Gayrimenkul Değerleme Yöntemleri: Kapsamlı Rehber',
        'content' => 'Gayrimenkul Değerleme Yöntemleri: Kapsamlı Rehber',
        'excerpt' => 'Dijital Dönüşümün Temel Dinamikleri',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'https://images.unsplash.com/photo-1584824486509-112e4181ff6b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80',
        'created_at' => '2024-05-28 13:45:00',
        'categories' => 'Endüstriyel',
        'tags' => 'depo,fabrika,endüstriyel,kiralama',
        'blog_file' => 'blog6.php',
        'reading_time' => '8 dk',
        'slug' => 'blog6.php'
    ]
];

// Toplam blog sayısını al
$total_blogs = count($all_blog_posts);
$total_pages = ceil($total_blogs / $per_page);

// Sayfalama için blog postlarını filtrele
$blog_posts = array_slice($all_blog_posts, $offset, $per_page);

// Kategorileri hazırla
$categories = [
    ['name' => 'Yatırım'],
    ['name' => 'Bölge Analizi'],
    ['name' => 'Kiralama'],
    ['name' => 'Ticari'],
    ['name' => 'Değerleme'],
    ['name' => 'Endüstriyel']
];

// Son eklenen blogları göster
$recent_posts = array_slice($all_blog_posts, 0, 3);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul Blog, Emlak Blog, Gayrimenkul Haberleri, İstanbul Emlak">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul Blog: Emlak sektöründen güncel haberler, piyasa analizleri ve uzman görüşleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="blog.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Blog Grid | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/blog-og.png'>
    <!-- For IE -->
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <!-- For Resposive Device -->
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- For Window Tab Color -->
    <!-- Chrome, Firefox OS and Opera -->
    <meta name="theme-color" content="#0D1A1C">
    <!-- Windows Phone -->
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <!-- iOS Safari -->
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Blog Grid | Gökhan Aydınlı Gayrimenkul</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <!-- Main style sheet -->
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <!-- responsive style sheet -->
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Modal CSS -->
    <?php if (file_exists('includes/modal-css.php')) include 'includes/modal-css.php'; ?>

    <!-- Fix Internet Explorer ______________________________________-->
    <!--[if lt IE 9]>
            <script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
            <script src="vendor/html5shiv.js"></script>
            <script src="vendor/respond.js"></script>
        <![endif]-->
</head>

<body>
    <div class="main-page-wrapper">
        <!-- ===================================================
            Loading Transition
        ==================================================== -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
            </div>
        </div>

        <!-- ################### Search Modal ####################### -->
        <!-- Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content d-flex justify-content-center">
                    <form action="#">
                        <input type="text" placeholder="Gayrimenkul Ara, Blog Ara, İlan Ara">
                        <button><i class="fa-light fa-arrow-right-long"></i></button>
                    </form>
                </div>
            </div>
        </div>

        <!-- 
        =============================================
            Theme Main Menu
        ============================================== 
        -->
        <header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="index.php" class="d-flex align-items-center">
                                <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
                            </a>
                        </div>
                        <!-- Header'da Giriş butonu (Navigation'dan önce) -->
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
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="portfoy.php">Portföy</a>
                                    </li>
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div> <!--/.top-header-->
            </div> <!-- /.inner-content -->
        </header> 
        <!-- /.theme-main-menu -->

        <!-- 
        =============================================
            Inner Banner
        ============================================== 
        -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Blog</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li>/</li>
                    <li>Blog</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>
        <!-- /.inner-banner-two -->

        <!--
        =====================================================
            Blog Section Three
        =====================================================
        -->
        <div class="blog-section-three mt-130 xl-mt-100 mb-150 xl-mb-100">
            <div class="container container-large">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="row gx-xxl-5">
                            <?php foreach ($blog_posts as $index => $post): ?>
                            <div class="col-md-6">
                                <article class="blog-meta-two tran3s position-relative z-1 mb-70 lg-mb-40 wow fadeInUp">
                                    <figure class="post-img position-relative m0" style="background-image: url('<?= htmlspecialchars($post['featured_image']) ?>');">
                                        <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="date"><?= formatTurkishDate($post['created_at']) ?></a>
                                    </figure>
                                    <div class="post-data">
                                        <div class="post-info">
                                            <a href="<?= htmlspecialchars($post['blog_file']) ?>"><?= htmlspecialchars($post['author']) ?> .</a> 
                                            <?= htmlspecialchars($post['reading_time']) ?> dk
                                        </div>
                                        <div class="d-flex justify-content-between align-items-sm-center flex-wrap">
                                            <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="blog-title">
                                                <h4><?= htmlspecialchars($post['title']) ?></h4>
                                            </a>
                                            <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="btn-four"><i class="bi bi-arrow-up-right"></i></a>
                                        </div>
                                    </div>
                                    <div class="hover-content tran3s">
                                        <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="date"><?= formatTurkishDate($post['created_at']) ?></a>
                                        <div class="post-data">
                                            <div class="post-info">
                                                <a href="<?= htmlspecialchars($post['blog_file']) ?>"><?= htmlspecialchars($post['author']) ?> .</a> 
                                                <?= htmlspecialchars($post['reading_time']) ?> dk
                                            </div>
                                            <div class="d-flex justify-content-between align-items-sm-center flex-wrap">
                                                <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="blog-title">
                                                    <h4><?= htmlspecialchars($post['title']) ?></h4>
                                                </a>
                                            </div>
                                        </div>
                                        <a href="<?= htmlspecialchars($post['blog_file']) ?>" class="btn-four inverse rounded-circle"><i class="fa-thin fa-arrow-up-right"></i></a>
                                    </div>
                                    <!-- /.hover-content -->
                                </article>
                                <!-- /.blog-meta-two -->
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <?php if ($total_pages > 1): ?>
                        <ul class="pagination-one square d-flex align-items-center style-none pt-30">
                            <?php if ($page > 1): ?>
                            <li class="me-2">
                                <a href="?page=1" class="d-flex align-items-center"><img src="images/icon/icon_45.svg" alt="" class="me-2"> İlk</a>
                            </li>
                            <?php endif; ?>
                            
                            <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                <li <?= $i == $page ? 'class="active"' : '' ?>>
                                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($page < $total_pages): ?>
                            <li class="ms-2">
                                <a href="?page=<?= $total_pages ?>" class="d-flex align-items-center">Son <img src="images/icon/icon_46.svg" alt="" class="ms-2"></a>
                            </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="blog-sidebar dot-bg ms-xxl-5 md-mt-60">
                            <div class="search-form bg-white mb-30">
                                <form action="blog.php" method="GET" class="position-relative">
                                    <input type="text" name="search" placeholder="Arama yapın..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                    <button type="submit"><i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>
                                </form>
                            </div>
                            <!-- /.search-form -->

                            <div class="categories bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">Kategoriler</h5>
                                <ul class="style-none">
                                    <li><a href="blog.php?category=yatirim">Gayrimenkul Yatırımı (3)</a></li>
                                    <li><a href="blog.php?category=satin-alma">Ev Satın Alma (4)</a></li>
                                    <li><a href="blog.php?category=analiz">Piyasa Analizi (2)</a></li>
                                    <li><a href="blog.php?category=danismanlik">Danışmanlık (8)</a></li>
                                    <li><a href="blog.php?category=kiralama">Kiralama (5)</a></li>
                                    <li><a href="blog.php?category=hukuk">Gayrimenkul Hukuku (3)</a></li>
                                </ul>
                            </div>
                            <!-- /.categories -->

                            <div class="recent-news bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">Son Yazılar</h5>
                                <?php foreach ($recent_posts as $recent_post): ?>
                                <div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="<?= htmlspecialchars($recent_post['featured_image']) ?>" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="<?= htmlspecialchars($recent_post['blog_file']) ?>" class="title tran3s"><?= htmlspecialchars(mb_substr($recent_post['title'], 0, 40)) ?><?= mb_strlen($recent_post['title']) > 40 ? '...' : '' ?></a></h4>
                                        <div class="date"><?= formatTurkishDate($recent_post['created_at']) ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            <!-- /.recent-news -->
                            
                            <div class="keyword bg-white bg-wrapper">
                                <h5 class="mb-20">Etiketler</h5>
                                <ul class="style-none d-flex flex-wrap">
                                    <li><a href="#">İstanbul</a></li>
                                    <li><a href="#">Yatırım</a></li>
                                    <li><a href="#">Konut</a></li>
                                    <li><a href="#">Ofis</a></li>
                                    <li><a href="#">Dükkan</a></li>
                                    <li><a href="#">Kiralama</a></li>
                                    <li><a href="#">Satış</a></li>
                                    <li><a href="#">Emlak</a></li>
                                </ul>
                            </div>
                            <!-- /.keyword -->
                        </div>
                        <!-- /.blog-sidebar -->
                    </div>
                </div>
            </div>
        </div>
        <!-- /.blog-section-three -->

        <!--
        =====================================================
            İletişim Banner
        =====================================================
        -->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50 " style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
		
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
                            <h3 class="text-white m0">Ticari <span>gayrimenkul<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> yolculuğunuza başlayın.</h3>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-wrapper me-auto ms-auto me-lg-0">
                            <form action="contact.php" method="POST">
                                <input type="email" name="email" placeholder="E-posta adresiniz" required>
                                <button type="submit">İletişime Geç</button>
                            </form>
                            <div class="fs-16 mt-10 text-white">Sorularınız mı var? <a href="tel:02128016058" class="text-decoration-underline">Hemen Arayın: 0212 801 60 58</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.fancy-banner-two -->

        <!--
        =====================================================
            Footer Four
        =====================================================
        -->
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
        </div> <!-- /.footer-four -->

        <!-- back to top -->
        <button class="back-to-top">
            <i class="bi bi-arrow-up-short"></i>
        </button>

        <!-- Optional JavaScript _____________________________  -->

        <!-- jQuery first, then Bootstrap JS -->
        <!-- jQuery -->
        <script src="vendor/jquery.min.js"></script>
        <!-- Bootstrap JS -->
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <!-- WOW js -->
        <script src="vendor/wow/wow.min.js"></script>
        <!-- Slick Slider -->
        <script src="vendor/slick/slick.min.js"></script>
        <!-- Fancybox -->
        <script src="vendor/fancybox/fancybox.umd.js"></script>
        <!-- Lazy -->
        <script src="vendor/jquery.lazy.min.js"></script>
        <!-- js Counter -->
        <script src="vendor/jquery.counterup.min.js"></script>
        <script src="vendor/jquery.waypoints.min.js"></script>
        <!-- Nice Select -->
        <script src="vendor/nice-select/jquery.nice-select.min.js"></script>
        <!-- validator js -->
        <script src="vendor/validator.js"></script>
        <!-- isotop -->
        <script  src="vendor/isotope.pkgd.min.js"></script>

        <!-- Theme js -->
        <script src="js/theme.js"></script>
        
        <!-- Modal JS -->
        <?php if (file_exists('includes/modal-js.php')) include 'includes/modal-js.php'; ?>

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

        <script>
            function switchToRegister() {
                document.querySelector('[data-bs-target="#fc2"]').click();
            }
            function switchToLogin() {
                document.querySelector('[data-bs-target="#fc1"]').click();
            }
        </script>
    </div> <!-- /.main-page-wrapper -->
</body>
</html>
