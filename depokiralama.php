<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında session-check'i include et (duplikasyon kaldırıldı)
include 'includes/session-check.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Depo Kiralama, Lojistik Depo, Ticari Depo, Soğuk Hava Deposu">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul depo kiralama hizmetleri: Lojistik, soğuk hava, ticari depo seçenekleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com/depokiralama.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Depo Kiralama | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/ogg.png'>
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
    <title>Depo Kiralama Detayları | Gökhan Aydınlı Gayrimenkul</title>
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
                                <img src="images/logoSiyah.png" alt="">
                            </a>
                        </div>
                        <!-- logo -->
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
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one"><i class="fa-regular fa-lock"></i> <span>Giriş</span></a>
                                    </li>
                                <?php endif; ?>
                                <li class="d-none d-md-inline-block ms-3">
                                    <a href="dashboard/add-property.html" class="btn-two" target="_blank"><span>İlan Ver</span> <i class="fa-thin fa-arrow-up-right"></i></a>
                                </li>
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
                                    <li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logoSiyah.png" alt=""></a></div></li>
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="dashboard/dashboard-index.html" target="_blank">Dashboard</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside" aria-expanded="false">Ana Sayfa
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="index.php" class="dropdown-item"><span>Ana Sayfa</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown mega-dropdown-sm">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">Hizmetlerimiz
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="row gx-1">
                                                <div class="col-lg-4">
                                                    <div class="menu-column">
                                                        <h6 class="mega-menu-title">Kiralama Hizmetleri</h6>
                                                        <ul class="style-none mega-dropdown-list">
                                                            <li><a href="ofiskiralama.php" class="dropdown-item"><span>Ofis Kiralama</span></a></li>
                                                            <li><a href="depokiralama.php" class="dropdown-item"><span>Depo Kiralama</span></a></li>
                                                            <li><a href="dukkankiralama.php" class="dropdown-item"><span>Dükkan Kiralama</span></a></li>
                                                        </ul>
                                                    </div> <!--/.menu-column -->
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="menu-column">
                                                        <h6 class="mega-menu-title">Satış Hizmetleri</h6>
                                                        <ul class="style-none mega-dropdown-list">
                                                            <li><a href="ticari-arsa.php" class="dropdown-item"><span>Ticari Arsa</span></a></li>
                                                            <li><a href="villa-satis.php" class="dropdown-item"><span>Villa Satışı</span></a></li>
                                                            <li><a href="konut-satis.php" class="dropdown-item"><span>Konut Satışı</span></a></li>
                                                        </ul>
                                                    </div> <!--/.menu-column -->
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="menu-column">
                                                        <h6 class="mega-menu-title">Danışmanlık</h6>
                                                        <ul class="style-none mega-dropdown-list">
                                                            <li><a href="yatirim-danismanligi.php" class="dropdown-item"><span>Yatırım Danışmanlığı</span></a></li>
                                                            <li><a href="konsulting.php" class="dropdown-item"><span>Konsülting Hizmeti</span></a></li>
                                                        </ul>
                                                    </div> <!--/.menu-column -->
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown mega-dropdown-sm">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside" aria-expanded="false">Sayfalar
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li class="row gx-1">
                                                <div class="col-lg-4">
                                                    <div class="menu-column">
                                                        <h6 class="mega-menu-title">Kurumsal</h6>
                                                        <ul class="style-none mega-dropdown-list">
                                                            <li><a href="hakkimizda.php" class="dropdown-item"><span>Hakkımızda</span></a></li>
                                                            <li><a href="portfoy.php" class="dropdown-item"><span>Portföyümüz</span></a></li>
                                                        </ul>
                                                    </div> <!--/.menu-column -->
                                                </div>
                                                <div class="col-lg-4">
                                                    <div class="menu-column">
                                                        <h6 class="mega-menu-title">İçerik</h6>
                                                        <ul class="style-none mega-dropdown-list">
                                                            <li><a href="blog.php" class="dropdown-item"><span>Blog</span></a></li>
                                                            <li><a href="contact.php" class="dropdown-item"><span>İletişim</span></a></li>
                                                            <li><a href="faq.html" class="dropdown-item"><span>S.S.S</span></a></li>
                                                        </ul>
                                                    </div> <!--/.menu-column -->
                                                </div>
                                            </li>
                                        </ul>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
                                            data-bs-auto-close="outside" aria-expanded="false">Blog
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="blog.php" class="dropdown-item"><span>Blog Yazıları</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="d-md-none ps-2 pe-2 mt-20">
                                        <a href="dashboard/add-property.html" class="btn-two w-100" target="_blank"><span>İlan Ver</span> <i class="fa-thin fa-arrow-up-right"></i></a>
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
                <h3 class="mb-35 xl-mb-20 pt-15">Depo Kiralama Detayları</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Ana Sayfa</a></li>
                    <li>/</li>
                    <li><a href="hizmetlerimiz.php">Hizmetlerimiz</a></li>
                    <li>/</li>
                    <li>Depo Kiralama</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>
        <!-- /.inner-banner-one -->

        <!--
        =====================================================
            Service Details
        =====================================================
        -->
        <div class="service-details mt-150 xl-mt-100 mb-150 xl-mb-100">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8">
                        <div class="service-post">
                            <div class="btn-line fw-500 text-uppercase">DEPO KİRALAMA</div>
                            <h3 class="mb-30">İşletmenizin depolama ihtiyaçları için en uygun çözümleri sunuyoruz.</h3>
                            <p class="fs-20 lh-lg pb-25">Lojistik depolardan soğuk hava depolarına, ticari depolama alanlarında uzman danışmanlık hizmeti veriyoruz. Modern teknoloji ile donatılmış güvenli depolama alanları ile işletmenizin büyümesine katkı sağlıyoruz.</p>
                            <p class="fs-20 lh-lg">Müşterilerimizin depolama ihtiyaçlarını en iyi şekilde karşılamak için 7/24 hizmet veriyoruz. Güvenlik, temizlik ve teknolojik altyapı konularında hiçbir taviz vermeden, her sektörün ihtiyacına uygun depolama çözümleri sunuyoruz.</p>
                            
                            <div class="img-gallery pt-15 pb-70 lg-pb-50">
                                <div class="row">
                                    <div class="col-8">
                                        <img src="images/lazy.svg" data-src="https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/f8b85d2e-8e9e-4e89-4ed8-3bb4e2e69b00/public" alt="" class="lazy-img w-100 mt-20">
                                    </div>
                                    <div class="col-4">
                                        <img src="images/lazy.svg" data-src="https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/2a38b5b7-fe9d-49ec-082f-8a5238b34700/public" alt="" class="lazy-img w-100 mt-20">
                                    </div>
                                </div>
                            </div>
                            <!-- /.img-gallery -->
                            
                            <h4 class="mb-30">Hızlı İşlem & Güvenli Hizmet</h4>
                            <p class="fs-24">Depo kiralama alanında güvenilir ve uzman çözüm ortağınız. İhtiyacınıza en uygun depo türünü bulmanızda size yardımcı oluyoruz.</p>
                            
                            <div class="feature-wrapper mt-60 lg-mt-40 mb-65 lg-mb-40">
                                <div class="bg-wrapper">
                                    <div class="row">
                                        <div class="col-xl-4 col-lg-6 col-md-4">
                                            <div class="card-style-eleven mt-30">
                                                <div class="icon"><img src="images/lazy.svg" data-src="images/icon/icon_72.svg" alt="" class="lazy-img"></div>
                                                <h5 class="mt-30 mb-20">Güvenlik Sistemleri</h5>
                                                <p>24/7 güvenlik kameraları, alarm sistemleri ve güvenlik görevlisi hizmeti ile deponuz güvende.</p>
                                            </div>
                                            <!-- /.card-style-eleven -->
                                        </div>
                                        <div class="col-xl-4 col-lg-6 col-md-4">
                                            <div class="card-style-eleven mt-30">
                                                <div class="icon"><img src="images/lazy.svg" data-src="images/icon/icon_73.svg" alt="" class="lazy-img"></div>
                                                <h5 class="mt-30 mb-20">Modern Altyapı</h5>
                                                <p>Yüksek tavanlı, geniş koridor ve rampa sistemleri ile modern depo tesisleri sunuyoruz.</p>
                                            </div>
                                            <!-- /.card-style-eleven -->
                                        </div>
                                        <div class="col-xl-4 col-lg-6 col-md-4">
                                            <div class="card-style-eleven mt-30">
                                                <div class="icon"><img src="images/lazy.svg" data-src="images/icon/icon_74.svg" alt="" class="lazy-img"></div>
                                                <h5 class="mt-30 mb-20">Esnek Kiralama</h5>
                                                <p>Kısa ve uzun vadeli kiralama seçenekleri, büyüklük ve lokasyon esnekliği sağlıyoruz.</p>
                                            </div>
                                            <!-- /.card-style-eleven -->
                                        </div>
                                    </div>
                                </div>
                                <!-- /.bg-wrapper -->
                            </div>
                            <!-- /.feature-wrapper -->
                            
                            <h4 class="mb-30">Depo Kiralama Avantajlarınız</h4>
                            <p class="fs-20 lh-lg pb-25">Uzman ekibimiz ile depo kiralama sürecinizde karşılaşabileceğiniz tüm zorluklara çözüm üretiyoruz. Size en uygun depo seçeneklerini sunarak işletmenizin verimliliğini artırıyoruz.</p>
                            <ul class="list-style-one fs-22 color-dark style-none">
                                <li>Lojistik, soğuk hava ve ticari depo seçenekleri</li>
                                <li>50+ aktif depo portföyü ve 100k m² toplam alan</li>
                                <li>7/24 güvenlik ve teknik destek hizmeti</li>
                                <li>Esnek kiralama süreleri ve uygun fiyat seçenekleri</li>
                            </ul>
                            <a href="contact.php" class="btn-two mt-30">Bize Ulaşın</a>
                        </div>
                        <!-- /.service-post -->
                    </div>
                    <div class="col-lg-4">
                        <div class="ms-xl-5">
                            <div class="service-sidebar md-mt-80">
                                <div class="service-category">
                                    <ul class="style-none">
                                        <li><a href="depokiralama.php" class="active">Depo Kiralama</a></li>
                                        <li><a href="ofiskiralama.php">Ofis Kiralama</a></li>
                                        <li><a href="dukkankiralama.php">Dükkan Kiralama</a></li>
                                        <li><a href="yatirim-danismanligi.php">Yatırım Danışmanlığı</a></li>
                                        <li><a href="ticari-arsa.php">Ticari Arsa</a></li>
                                        <li><a href="konsulting.php">Konsülting Hizmeti</a></li>
                                    </ul>
                                </div>
                                <!-- /.service-category -->
                                <div class="contact-banner text-center mt-45">
                                    <h4 class="mb-35 text-white">Sorularınız mı var? <br>Hemen konuşalım</h4>
                                    <a href="contact.php" class="btn-two">İletişime Geç</a>
                                </div>
                                <!-- /.contact-banner -->
                            </div>
                            <!-- /.service-sidebar -->
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.service-details-->

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
                            <h3 class="text-white m0">Depo kiralama <span>yolculuğunuza<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> başlayın.</h3>
                        </div>
                        <!-- /.title-one -->
                    </div>
                    <div class="col-lg-6">
                        <div class="form-wrapper me-auto ms-auto me-lg-0">
                            <form action="contact.php" method="POST">
                                <input type="email" name="email" placeholder="E-posta adresiniz" required>
                                <button type="submit">Başlayın</button>
                            </form>
                            <div class="fs-16 mt-10 text-white">Zaten müşterimiz misiniz? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın.</a></div>
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
                                    <li><a href="dashboard/membership.html" target="_blank">Üyelik</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="dashboard/dashboard-index.html" target="_blank">Dashboard</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Yasal</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="faq.html">Şartlar & Koşullar</a></li>
                                    <li><a href="faq.html">Çerez Politikası</a></li>
                                    <li><a href="faq.html">Gizlilik Politikası</a></li>
                                    <li><a href="faq.html">S.S.S</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetlerimiz</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="ofiskiralama.php">Ofis Kiralama</a></li>
                                    <li><a href="dukkankiralama.php">Dükkan Kiralama</a></li>
                                    <li><a href="depokiralama.php">Depo Kiralama</a></li>
                                    <li><a href="ticari-arsa.php">Ticari Arsa</a></li>
                                    <li><a href="villa-satis.php">Villa Satışı</a></li>
                                    <li><a href="konut-satis.php">Konut Satışı</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <!-- /.bg-wrapper -->
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2025 Gökhan Aydınlı Gayrimenkul.</p>
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
                                        <h2>Hoş Geldiniz!</h2>
                                        <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#">Kayıt olun</a></p>
                                    </div>
                                    <form action="login.php" method="POST">
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
                                                        <input type="checkbox" id="remember">
                                                        <label for="remember">Beni hatırla</label>
                                                    </div>
                                                    <a href="#">Şifremi Unuttum?</a>
                                                </div> <!-- /.agreement-checkbox -->
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
                                        <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#">Giriş yapın</a></p>
                                    </div>
                                    <form action="register.php" method="POST">
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
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="remember2" required>
                                                        <label for="remember2">"Kayıt Ol" butonuna tıklayarak <a href="#">Şartlar & Koşullar</a> ile <a href="#">Gizlilik Politikası</a>'nı kabul ediyorum</label>
                                                    </div>
                                                </div> <!-- /.agreement-checkbox -->
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
                                    <a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                        <img src="images/icon/google.png" alt="">
                                        <span class="ps-3">Google ile Giriş</span>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
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
        <script src="vendor/isotope.pkgd.min.js"></script>

        <!-- Theme js -->
        <script src="js/theme.js"></script>
    </div> <!-- /.main-page-wrapper -->
</body>

</html>