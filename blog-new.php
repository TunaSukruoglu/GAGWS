<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

try {
    include 'db.php';
} catch (Exception $e) {
    die("DB Bağlantı Hatası: " . $e->getMessage());
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

// Demo blog verileri - 6 adet blog yazısı
$blog_posts = [
    [
        'id' => 1,
        'title' => 'Gayrimenkul Yatırımında 2024 Trendleri',
        'content' => 'Gayrimenkul sektöründe bu yıl yaşanan gelişmeler ve yatırım fırsatları hakkında detaylı analiz.',
        'excerpt' => 'Gayrimenkul sektöründe bu yıl yaşanan gelişmeler ve yatırım fırsatları.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-15',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 5,
        'slug' => 'gayrimenkul-yatiriminda-2024-trendleri'
    ],
    [
        'id' => 2,
        'title' => 'Ev Alırken Dikkat Edilmesi Gerekenler',
        'content' => 'Ev satın alma sürecinde önemli noktalar ve dikkat edilmesi gereken hususlar.',
        'excerpt' => 'Ev satın alma sürecinde önemli noktalar ve dikkat edilmesi gereken hususlar.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-10',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 7,
        'slug' => 'ev-alirken-dikkat-edilmesi-gerekenler'
    ],
    [
        'id' => 3,
        'title' => 'İstanbul\'da En İyi Yatırım Bölgeleri',
        'content' => 'İstanbul\'un farklı ilçelerindeki yatırım potansiyeli ve gelecek projeksiyonları.',
        'excerpt' => 'İstanbul\'un farklı ilçelerindeki yatırım potansiyeli ve gelecek projeksiyonları.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-05',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 6,
        'slug' => 'istanbul-en-iyi-yatirim-bolgeleri'
    ],
    [
        'id' => 4,
        'title' => 'Konut Kredisi Faiz Oranları Analizi',
        'content' => 'Güncel konut kredisi faiz oranları ve kredi kullanma stratejileri.',
        'excerpt' => 'Güncel konut kredisi faiz oranları ve kredi kullanma stratejileri.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-01',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 4,
        'slug' => 'konut-kredisi-faiz-oranlari-analizi'
    ],
    [
        'id' => 5,
        'title' => 'Ofis Kiralama Rehberi 2024',
        'content' => 'İş yeri kiralama sürecinde dikkat edilmesi gereken noktalar ve ipuçları.',
        'excerpt' => 'İş yeri kiralama sürecinde dikkat edilmesi gereken noktalar ve ipuçları.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2023-12-28',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 8,
        'slug' => 'ofis-kiralama-rehberi-2024'
    ],
    [
        'id' => 6,
        'title' => 'Gayrimenkul Değerleme Yöntemleri',
        'content' => 'Mülk değerleme teknikleri ve piyasa değeri belirleme stratejileri.',
        'excerpt' => 'Mülk değerleme teknikleri ve piyasa değeri belirleme stratejileri.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2023-12-25',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 9,
        'slug' => 'gayrimenkul-degerleme-yontemleri'
    ]
];

function formatTurkishDate($date) {
    $months = [
        '01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan',
        '05' => 'Mayıs', '06' => 'Haziran', '07' => 'Temmuz', '08' => 'Ağustos',
        '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'
    ];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $months[$parts[1]] . ' ' . $parts[0];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul Blog, Emlak Blog, Gayrimenkul Haberleri, İstanbul Emlak">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul Blog: Emlak sektöründen güncel haberler, piyasa analizleri ve uzman görüşleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com/blog.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Blog | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/blog-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Blog | Gökhan Aydınlı Gayrimenkul</title>
    
    <!-- CSS -->
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Modal CSS -->
    <?php if (file_exists('includes/modal-css.php')) include 'includes/modal-css.php'; ?>
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
                        <!-- logo -->
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
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="portfoy.php">Portföy</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div> <!--/.top-header-->
            </div> <!-- /.inner-content -->
        </header> 
        <!-- /.theme-main-menu -->

        <!-- ============================
            İç Banner
        ============================ -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Blog Yazıları</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li>/</li>
                    <li>Blog</li>
                </ul>
                <p class="sub-text mt-20">Gayrimenkul dünyasından güncel haberler ve uzman görüşleri</p>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Blog Content -->
        <div class="blog-section-one mt-150 xl-mt-120 mb-150 xl-mb-120">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <div class="blog-meta-wrapper pe-xxl-5">
                            <div class="row">
                                <?php foreach ($blog_posts as $post): 
                                    $blog_url = "blog" . $post['id'] . ".php";
                                ?>
                                <div class="col-sm-6">
                                    <article class="blog-meta-two color-two mb-50 lg-mb-40">
                                        <figure class="post-img m0">
                                            <a href="<?= $blog_url ?>" class="w-100 d-block">
                                                <img src="<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="lazy-img w-100 tran4s">
                                            </a>
                                        </figure>
                                        <div class="post-data mt-30">
                                            <div class="post-date">
                                                <a href="<?= $blog_url ?>" class="date"><?= formatTurkishDate($post['date']) ?></a>
                                            </div>
                                            <div class="post-info">
                                                <a href="<?= $blog_url ?>"><?= htmlspecialchars($post['author']) ?> .</a> <?= $post['reading_time'] ?> dk okuma
                                            </div>
                                            <div class="blog-title">
                                                <a href="<?= $blog_url ?>" class="blog-title"><h4><?= htmlspecialchars($post['title']) ?></h4></a>
                                                <div class="post-excerpt">
                                                    <p><?= htmlspecialchars($post['excerpt']) ?></p>
                                                </div>
                                                <a href="<?= $blog_url ?>" class="btn-four"><i class="bi bi-arrow-up-right"></i></a>
                                            </div>
                                        </div>
                                    </article>
                                </div>
                                <?php endforeach; ?>
                            </div>
                            
                            <!-- Pagination -->
                            <div class="pagination-one d-flex align-items-center style-none pt-30">
                                <ul class="d-flex align-items-center style-none">
                                    <li class="me-3"><a href="#" class="d-flex align-items-center"><i class="bi bi-chevron-left"></i></a></li>
                                    <li><a href="#" class="active">1</a></li>
                                    <li><a href="#">2</a></li>
                                    <li><a href="#">3</a></li>
                                    <li class="ms-2"><a href="#" class="d-flex align-items-center"><i class="bi bi-chevron-right"></i></a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="blog-sidebar ps-xl-5 md-mt-60">
                            <!-- Search -->
                            <div class="blog-sidebar-banner text-center mb-55 md-mb-40">
                                <h4 class="mb-20">Blog Ara</h4>
                                <form action="blog.php" method="GET">
                                    <input type="text" name="search" placeholder="Arama yapın..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                    <button type="submit"><i class="bi bi-search"></i></button>
                                </form>
                            </div>
                            
                            <!-- Categories -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">Kategoriler</h4>
                                <ul class="style-none">
                                    <li><a href="blog.php?category=yatirim">Gayrimenkul Yatırımı</a></li>
                                    <li><a href="blog.php?category=satin-alma">Ev Satın Alma</a></li>
                                    <li><a href="blog.php?category=analiz">Piyasa Analizi</a></li>
                                    <li><a href="blog.php?category=danismanlik">Danışmanlık</a></li>
                                    <li><a href="blog.php?category=kiralama">Kiralama</a></li>
                                    <li><a href="blog.php?category=hukuk">Gayrimenkul Hukuku</a></li>
                                </ul>
                            </div>
                            
                            <!-- Recent Posts -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">Son Yazılar</h4>
                                <ul class="style-none">
                                    <?php foreach (array_slice($blog_posts, 0, 4) as $recent_post): ?>
                                    <li>
                                        <div class="news-block d-flex align-items-center pt-20 pb-20 border-bottom">
                                            <div><img src="<?= $recent_post['image'] ?>" alt="<?= htmlspecialchars($recent_post['title']) ?>" class="lazy-img"></div>
                                            <div class="post-data ps-4">
                                                <h4><a href="blog<?= $recent_post['id'] ?>.php"><?= htmlspecialchars($recent_post['title']) ?></a></h4>
                                                <div class="date"><?= formatTurkishDate($recent_post['date']) ?></div>
                                            </div>
                                        </div>
                                    </li>
                                    <?php endforeach; ?>
                                </ul>
                            </div>
                            
                            <!-- Tags -->
                            <div class="sidebar-recent-news">
                                <h4 class="sidebar-title">Etiketler</h4>
                                <ul class="tags d-flex flex-wrap style-none">
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
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
                                <!-- logo -->
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
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="hesaplama-araclari.php">Hesaplamalar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Yasal</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="terms.php">Şartlar & Koşullar</a></li>
                                    <li><a href="cookies.php">Çerez Politikası</a></li>
                                    <li><a href="privacy.php">Gizlilik Politikası</a></li>
                                    <li><a href="faq.php">S.S.S</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetlerimiz</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="listing_04.php">Ticari Gayrimenkul</a></li>
                                    <li><a href="listing_01.php">Konut Satışı</a></li>
                                    <li><a href="listing_02.php">Ev Kiralama</a></li>
                                    <li><a href="contact.php">Yatırım Danışmanlığı</a></li>
                                    <li><a href="portfoy.php?type=villa">Villa Satışı</a></li>
                                    <li><a href="portfoy.php?type=ofis">Ofis Kiralama</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.bg-wrapper -->
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div> <!-- /.footer-four -->

        <!-- ################### Login Modal ####################### -->
        <!-- Modal -->
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
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="remember" name="remember">
                                                        <label for="remember">Beni hatırla</label>
                                                    </div>
                                                    <a href="#" onclick="showForgotPassword()">Şifremi Unuttum?</a>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
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
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Telefon</label>
                                                    <input type="tel" name="phone" placeholder="0555 555 55 55">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre Tekrar*</label>
                                                    <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="terms" name="terms" required>
                                                        <label for="terms">"Kayıt Ol" butonuna tıklayarak <a href="terms.php" target="_blank">Şartlar & Koşullar</a> ile <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı kabul ediyorum</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <!-- /.tab-pane -->
                            </div>
                            
                            <div class="d-flex align-items-center mt-30 mb-10">
                                <div class="line"></div>
                                <span class="pe-3 ps-3 fs-6">VEYA</span>
                                <div class="line"></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <a href="#" onclick="loginWithGoogle()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                        <img src="images/icon/google.png" alt="">
                                        <span class="ps-3">Google ile Giriş</span>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="#" onclick="loginWithFacebook()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                        <img src="images/icon/facebook.png" alt="">
                                        <span class="ps-3">Facebook ile Giriş</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                        <!-- /.form-wrapper -->
                    </div>
                    <!-- /.user-data-form -->
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
        
        <!-- Optional: Smooth Scroll -->
        <script>
            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({ behavior: 'smooth' });
                    }
                });
            });

            // Modal switch functions
            function switchToRegister() {
                document.querySelector('[data-bs-target="#fc2"]').click();
            }

            function switchToLogin() {
                document.querySelector('[data-bs-target="#fc1"]').click();
            }

            function showForgotPassword() {
                alert('Şifre sıfırlama özelliği yakında eklenecek.');
            }

            function loginWithGoogle() {
                alert('Google ile giriş özelliği yakında eklenecek.');
            }

            function loginWithFacebook() {
                alert('Facebook ile giriş özelliği yakında eklenecek.');
            }
            
            // Preloader'ı kapat
            document.addEventListener('DOMContentLoaded', function() {
                const preloader = document.getElementById('preloader');
                if (preloader) {
                    console.log('Preloader bulundu, kapatılıyor...');
                    setTimeout(() => {
                        preloader.style.opacity = '0';
                        setTimeout(() => {
                            preloader.style.display = 'none';
                            preloader.remove();
                            console.log('Preloader kaldırıldı');
                        }, 500);
                    }, 1000);
                }
                
                // Acil durum için - 5 saniye sonra zorla kapat
                setTimeout(() => {
                    const preloader = document.getElementById('preloader');
                    if (preloader) {
                        console.log('Acil durum: Preloader zorla kapatılıyor');
                        preloader.style.display = 'none';
                        preloader.remove();
                    }
                }, 5000);
            });
        </script>
    </div> <!-- /.main-page-wrapper -->
</body>
</html>
