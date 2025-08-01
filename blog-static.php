<?php
// Ana blog sayfası - statik veri ile çalışan güvenilir sürüm
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

// Sayfalama için değişkenler
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$per_page = 6; // Her sayfada 6 blog göster
$offset = ($page - 1) * $per_page;

// Statik blog verilerini kullan
$all_blog_posts = [
    [
        'id' => 1,
        'title' => 'Gayrimenkul Yatırımında 2024 Trendleri',
        'content' => 'Bu yıl gayrimenkul sektöründe yaşanan değişimler ve gelecek dönemde beklenen trendler hakkında detaylı analiz.',
        'excerpt' => 'Gayrimenkul sektöründe 2024 yılında yaşanan değişimler ve yatırım fırsatları.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-1.jpg',
        'created_at' => '2024-07-01 10:30:00',
        'categories' => 'Yatırım',
        'tags' => 'gayrimenkul,yatırım,2024,trend',
        'blog_file' => 'blog1.php',
        'reading_time' => '5 dk',
        'slug' => 'blog1.php'
    ],
    [
        'id' => 2,
        'title' => 'İstanbul\'da Ev Almak İçin En İyi Bölgeler',
        'content' => 'İstanbul\'un farklı bölgelerinde yaşam kalitesi, ulaşım imkanları ve fiyat avantajları analizi.',
        'excerpt' => 'İstanbul\'da ev almak isteyenler için en uygun bölgelerin detaylı incelemesi.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-2.jpg',
        'created_at' => '2024-06-25 14:15:00',
        'categories' => 'Bölge Analizi',
        'tags' => 'istanbul,konut,bölge,analiz',
        'blog_file' => 'blog2.php',
        'reading_time' => '7 dk',
        'slug' => 'blog2.php'
    ],
    [
        'id' => 3,
        'title' => 'Kiralık Ev Seçerken Dikkat Edilmesi Gerekenler',
        'content' => 'Kiralık ev ararken dikkat edilmesi gereken önemli kriterler ve ipuçları.',
        'excerpt' => 'Doğru kiralık evi seçmek için bilinmesi gereken püf noktalar.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-3.jpg',
        'created_at' => '2024-06-18 09:45:00',
        'categories' => 'Kiralama',
        'tags' => 'kiralık,ev,seçim,ipucu',
        'blog_file' => 'blog3.php',
        'reading_time' => '4 dk',
        'slug' => 'blog3.php'
    ],
    [
        'id' => 4,
        'title' => 'Ofis Kiralama Sürecinde Bilinmesi Gerekenler',
        'content' => 'İşletmeler için ofis kiralama sürecinde dikkat edilmesi gereken yasal ve pratik konular.',
        'excerpt' => 'Ofis kiralama sürecindeki önemli adımlar ve dikkat edilmesi gerekenler.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-4.jpg',
        'created_at' => '2024-06-10 16:20:00',
        'categories' => 'Ticari',
        'tags' => 'ofis,kiralama,ticari,işletme',
        'blog_file' => 'blog4.php',
        'reading_time' => '6 dk',
        'slug' => 'blog4.php'
    ],
    [
        'id' => 5,
        'title' => 'Gayrimenkul Değerleme Yöntemleri',
        'content' => 'Profesyonel gayrimenkul değerleme yöntemleri ve piyasa analizi teknikleri.',
        'excerpt' => 'Gayrimenkul değerleme sürecinde kullanılan yöntemler ve değerlendirme kriterleri.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-5.jpg',
        'created_at' => '2024-06-05 11:30:00',
        'categories' => 'Değerleme',
        'tags' => 'değerleme,analiz,piyasa,gayrimenkul',
        'blog_file' => 'blog5.php',
        'reading_time' => '5 dk',
        'slug' => 'blog5.php'
    ],
    [
        'id' => 6,
        'title' => 'Depo ve Fabrika Kiralama Rehberi',
        'content' => 'Endüstriyel amaçlı depo ve fabrika kiralama sürecinde bilinmesi gerekenler.',
        'excerpt' => 'Depo ve fabrika kiralama sürecindeki önemli detaylar ve dikkat edilecek noktalar.',
        'author' => 'Gökhan Aydınlı',
        'featured_image' => 'images/blog/blog-6.jpg',
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
    <meta property="og:title" content="Blog | Gökhan Aydınlı Gayrimenkul">
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
    <title>Blog | Gökhan Aydınlı Gayrimenkul</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <!-- Main style sheet -->
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <!-- responsive style sheet -->
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">

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
        <header class="theme-main-menu menu-overlay menu-style-six sticky-menu">
            <div class="inner-content gap-two">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center">
                        <div class="logo order-lg-0">
                            <a href="index.php" class="d-flex align-items-center">
                                <img src="images/logo/logo_06.svg" alt="Gökhan Aydınlı Gayrimenkul">
                            </a>
                        </div>
                        <!-- logo -->
                        <div class="right-widget ms-auto me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                <li class="d-none d-md-inline-block me-4">
                                    <a href="dashboard/dashboard.php" class="btn-ten rounded-0"><span>Dashboard</span> <i class="bi bi-arrow-up-right"></i></a>
                                </li>
                                <li class="d-none d-md-inline-block me-4">
                                    <span class="text-white">Hoş geldin, <?= htmlspecialchars($userName) ?></span>
                                </li>
                                <li>
                                    <a href="logout.php" class="login-btn-two rounded-circle tran3s d-flex align-items-center justify-content-center" title="Çıkış Yap"><i class="fa-regular fa-sign-out-alt"></i></a>
                                </li>
                                <?php else: ?>
                                <li class="d-none d-md-inline-block me-4">
                                    <a href="dashboard/add-property.php" class="btn-ten rounded-0"><span>İlan Ver</span> <i class="bi bi-arrow-up-right"></i></a>
                                </li>
                                <li>
                                    <a href="login.php" class="login-btn-two rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="fa-regular fa-lock"></i></a>
                                </li>
                                <?php endif; ?>
                                <li>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#searchModal" class="search-btn-one rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="bi bi-search"></i></a>
                                </li>
                            </ul>
                        </div>
                        <nav class="navbar navbar-expand-lg p0 ms-lg-5 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
                                data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
                                aria-label="Toggle navigation">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse ms-xl-5" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logo/logo_06.svg" alt=""></a></div></li>
                                    <?php if ($isLoggedIn): ?>
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="dashboard/dashboard.php">Dashboard</a>
                                    </li>
                                    <?php endif; ?>
                                    <li class="nav-item">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside" aria-expanded="false">İlanlar
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="portfoy.php" class="dropdown-item"><span>Tüm İlanlar</span></a></li>
                                            <li><a href="ofiskiralama.php" class="dropdown-item"><span>Ofis Kiralama</span></a></li>
                                            <li><a href="dukkankiralama.php" class="dropdown-item"><span>Dükkan Kiralama</span></a></li>
                                            <li><a href="depokiralama.php" class="dropdown-item"><span>Depo Kiralama</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside" aria-expanded="false">Kurumsal
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="hakkimizda.php" class="dropdown-item"><span>Hakkımızda</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
                                    </li>
                                    <li class="d-md-none ps-2 pe-2 mt-20">
                                        <a href="dashboard/add-property.php" class="btn-ten w-100 rounded-0"><span>İlan Ver</span> <i class="bi bi-arrow-up-right"></i></a>
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
        <div class="inner-banner-two inner-banner z-1 pt-170 xl-pt-150 md-pt-130 pb-140 xl-pb-100 md-pb-80 position-relative" style="background-image: url(images/media/img_49.jpg);">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <h3 class="mb-45 xl-mb-30 md-mb-20">Blog</h3>
                        <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                            <li><a href="index.php">Ana Sayfa</a></li>
                            <li>/</li>
                            <li>Blog</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <p class="sub-heading">Gayrimenkul sektöründen güncel haberler, piyasa analizleri ve uzman görüşleri!</p>
                    </div>
                </div>
            </div>
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
            Fancy Banner Two
        =====================================================
        -->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
                            <h3 class="text-white m0">Gayrimenkul <span>Yolculuğunuza<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Başlayın.</h3>
                        </div>
                        <!-- /.title-one -->
                    </div>
                    <div class="col-lg-6">
                        <div class="form-wrapper me-auto ms-auto me-lg-0">
                            <form action="contact.php" method="POST">
                                <input type="email" name="email" placeholder="E-posta adresiniz" class="rounded-0" required>
                                <button type="submit" class="rounded-0">Başlayın</button>
                            </form>
                            <div class="fs-16 mt-10 text-white">Zaten üye misiniz? <a href="login.php">Giriş yapın.</a></div>
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
                <div class="footer-top">
                    <div class="row">
                        <div class="col-lg-3 col-md-6 pe-xl-5">
                            <div class="footer-widget-one mb-40">
                                <div class="logo mb-25"><a href="index.php"><img src="images/logo/logo_06.svg" alt=""></a></div>
                                <p class="mb-30">Gökhan Aydınlı Gayrimenkul: 20+ yıldır İstanbul ve çevresinde emlak danışmanlığı hizmeti veriyoruz.</p>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6">
                            <div class="footer-widget-two mb-40 ps-xl-4">
                                <h5 class="footer-title">Sayfalar</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="portfoy.php">Tüm İlanlar</a></li>
                                    <li><a href="ofiskiralama.php">Ofis Kiralama</a></li>
                                    <li><a href="dukkankiralama.php">Dükkan Kiralama</a></li>
                                    <li><a href="depokiralama.php">Depo Kiralama</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 ps-xl-5">
                            <div class="footer-widget-three mb-40">
                                <h5 class="footer-title">İletişim</h5>
                                <p>Bize mesaj gönderin, size hemen dönüş yapalım.</p>
                                <a href="contact.php" class="email">info@gokhanaydinli.com</a>
                                <a href="tel:+2018897652" class="mobile">+90 212 000 0000</a>
                            </div>
                        </div>
                        <div class="col-lg-3 col-md-6 ps-xl-5">
                            <div class="footer-widget-four mb-40">
                                <h5 class="footer-title">Adres</h5>
                                <p>Gökhan Aydınlı Gayrimenkul ve Danışmanlık Hizmetleri</p>
                                <p>Atatürk Bulvarı No: 123<br>Şişli, İstanbul, 34394</p>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.footer-top -->

                <div class="bottom-footer">
                    <div class="row align-items-center">
                        <div class="col-lg-6 order-lg-last mb-15">
                            <ul class="d-flex justify-content-center justify-content-lg-end style-none">
                                <li><a href="#">Kullanım Şartları</a></li>
                                <li><a href="#">Gizlilik Politikası</a></li>
                                <li><a href="#">Çerez Politikası</a></li>
                            </ul>
                        </div>
                        <div class="col-lg-6 mb-15">
                            <div class="copyright text-center text-lg-start">Copyright © 2025 Gökhan Aydınlı - Tüm Hakları Saklıdır.</div>
                        </div>
                    </div>
                </div>
            </div>
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
    </div> <!-- /.main-page-wrapper -->
</body>
</html>
