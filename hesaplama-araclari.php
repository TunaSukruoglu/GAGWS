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
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul, hesaplama araçları, ROI, ticari kredi, gayrimenkul değerleme, nakit akış, kira artışı">
    <meta name="description" content="Ticari gayrimenkul yatırımlarınız için profesyonel hesaplama araçları. ROI analizi, kredi hesaplama, değerleme, nakit akış analizi ve kira artışı hesaplama.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com/hesaplama-araclari.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Hesaplama Araçları | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/ogg.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Hesaplama Araçları | Gökhan Aydınlı Gayrimenkul</title>
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
    
    <style>
/* Hesaplama araçları özel CSS */
.calculator-section {
    padding: 80px 0;
}

.calculator-card {
    background: white;
    border-radius: 20px;
    padding: 40px;
    box-shadow: 0 15px 35px rgba(0,0,0,0.1);
    transition: transform 0.3s ease, box-shadow 0.3s ease;
    margin-bottom: 30px;
    height: 100%;
}

.calculator-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.calculator-header {
    display: flex;
    align-items: center;
    margin-bottom: 30px;
    padding-bottom: 20px;
    border-bottom: 2px solid #f8f9fa;
}

.calculator-icon {
    font-size: 2.5rem;
    margin-right: 20px;
    color: #15B97C;
    width: 60px;
    height: 60px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: rgba(21, 185, 124, 0.1);
    border-radius: 15px;
}

.calculator-title {
    font-size: 1.5rem;
    color: #0D1A1C;
    font-weight: 700;
    margin: 0;
}

.form-group {
    margin-bottom: 25px;
}

.form-group label {
    display: block;
    margin-bottom: 10px;
    font-weight: 600;
    color: #0D1A1C;
    font-size: 1rem;
}

.form-control {
    width: 100%;
    padding: 15px 20px;
    border: 2px solid #e9ecef;
    border-radius: 12px;
    font-size: 1rem;
    transition: all 0.3s ease;
    background: #fff;
}

.form-control:focus {
    outline: none;
    border-color: #15B97C;
    box-shadow: 0 0 0 3px rgba(21, 185, 124, 0.1);
}

.input-group {
    position: relative;
}

.input-addon {
    position: absolute;
    right: 20px;
    top: 50%;
    transform: translateY(-50%);
    color: #6c757d;
    font-weight: 600;
    background: #f8f9fa;
    padding: 5px 10px;
    border-radius: 6px;
    font-size: 0.9rem;
}

.btn-calculate {
    width: 100%;
    background: linear-gradient(45deg, #15B97C, #0D1A1C);
    color: white;
    border: none;
    padding: 18px;
    border-radius: 12px;
    font-size: 1.1rem;
    font-weight: 700;
    cursor: pointer;
    transition: all 0.3s ease;
    margin-top: 20px;
    text-transform: uppercase;
}

.btn-calculate:hover {
    background: linear-gradient(45deg, #0D1A1C, #15B97C);
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(21, 185, 124, 0.3);
}

.result-box {
    margin-top: 30px;
    padding: 25px;
    background: linear-gradient(45deg, #e8f5e8, #d4edda);
    border-radius: 15px;
    border-left: 5px solid #15B97C;
    display: none;
}

.result-box.show {
    display: block;
    animation: slideInUp 0.5s ease;
}

@keyframes slideInUp {
    from {
        opacity: 0;
        transform: translateY(30px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.result-title {
    font-size: 1.3rem;
    font-weight: 700;
    color: #155724;
    margin-bottom: 15px;
}

.result-value {
    font-size: 2.2rem;
    font-weight: 800;
    color: #15B97C;
    margin-bottom: 15px;
}

.result-details {
    font-size: 1rem;
    color: #6c757d;
    line-height: 1.6;
}

.two-column {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 20px;
}

.three-column {
    display: grid;
    grid-template-columns: 1fr 1fr 1fr;
    gap: 15px;
}

.roi-indicator {
    display: inline-block;
    padding: 8px 15px;
    border-radius: 25px;
    font-size: 0.9rem;
    font-weight: 700;
    margin-left: 15px;
}

.roi-excellent { background: #d4edda; color: #155724; }
.roi-good { background: #cce5ff; color: #004085; }
.roi-average { background: #fff3cd; color: #856404; }
.roi-poor { background: #f8d7da; color: #721c24; }

.commercial-intro {
    background: linear-gradient(135deg, #15B97C 0%, #0D1A1C 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 50px;
    color: white;
    text-align: center;
}

.commercial-intro h2 {
    color: white;
    margin-bottom: 20px;
    font-size: 2rem;
}

.commercial-intro p {
    font-size: 1.2rem;
    opacity: 0.9;
    line-height: 1.6;
}

.warning-box {
    background: #fff3cd;
    border: 1px solid #ffeaa7;
    border-radius: 15px;
    padding: 25px;
    margin-top: 40px;
    color: #856404;
    font-size: 0.95rem;
    line-height: 1.6;
}

.update-info {
    background: #e8f4fd;
    border: 1px solid #bee5eb;
    border-radius: 15px;
    padding: 20px;
    margin-top: 20px;
    text-align: center;
}

.btn-refresh {
    background: #17a2b8;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    margin: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-refresh:hover {
    background: #138496;
    transform: translateY(-2px);
}

.btn-info {
    background: #6c757d;
    color: white;
    border: none;
    padding: 12px 25px;
    border-radius: 8px;
    margin: 10px;
    cursor: pointer;
    font-weight: 600;
    transition: all 0.3s ease;
}

.btn-info:hover {
    background: #5a6268;
    transform: translateY(-2px);
}

/* Kira Artışı Hesaplama Özel Stilleri */
.rent-increase-section {
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    border-radius: 20px;
    padding: 40px;
    margin-bottom: 50px;
    color: white;
}

.rent-increase-section .info-box {
    background: linear-gradient(135deg, #74b9ff, #0984e3);
    color: white;
    padding: 20px;
    border-radius: 10px;
    margin-bottom: 30px;
}

.rent-increase-section .info-list {
    list-style: none;
    padding-left: 0;
    margin: 0;
}

.rent-increase-section .info-list li {
    margin-bottom: 8px;
    padding-left: 20px;
    position: relative;
}

.rent-increase-section .info-list li::before {
    content: "✓";
    position: absolute;
    left: 0;
    color: #00b894;
    font-weight: bold;
}

.rent-calculator-grid {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 40px;
    margin-bottom: 40px;
}

.rent-input-group {
    background: rgba(255, 255, 255, 0.1);
    padding: 25px;
    border-radius: 15px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.rent-input-group h5 {
    color: white;
    margin-bottom: 20px;
    display: flex;
    align-items: center;
}

.rent-results-section {
    background: linear-gradient(135deg, #f093fb 0%, #f5576c 100%);
    color: white;
    padding: 30px;
    border-radius: 15px;
    margin-top: 30px;
    display: none;
}

.rent-results-section.show {
    display: block;
    animation: slideInUp 0.5s ease;
}

.rent-results-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin-top: 20px;
}

.rent-result-card {
    background: rgba(255, 255, 255, 0.2);
    padding: 20px;
    border-radius: 10px;
    backdrop-filter: blur(10px);
    border: 1px solid rgba(255, 255, 255, 0.3);
}

.rent-comparison-table {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 5px 15px rgba(0, 0, 0, 0.1);
    margin-top: 30px;
    display: none;
}

.rent-comparison-table.show {
    display: block;
    animation: slideInUp 0.5s ease;
}

.table-header {
    background: linear-gradient(135deg, #2c3e50, #34495e);
    color: white;
    padding: 20px;
    text-align: center;
}

.table-header h3 {
    margin: 0;
    color: white;
    font-size: 1.3rem;
}

.rent-comparison-table table {
    width: 100%;
    border-collapse: collapse;
    margin: 0;
}

.rent-comparison-table th {
    background: #f8f9fa;
    padding: 15px;
    text-align: left;
    border-bottom: 2px solid #dee2e6;
    font-weight: 600;
    color: #2c3e50;
    font-size: 14px;
}

.rent-comparison-table td {
    padding: 15px;
    border-bottom: 1px solid #eee;
    color: #2c3e50;
    font-size: 14px;
}

.rent-comparison-table tr:hover {
    background: #f8f9fa;
}
    color: white;
    padding: 20px;
}

/* Responsive düzenlemeler */
@media (max-width: 992px) {
    .two-column,
    .three-column,
    .rent-calculator-grid {
        grid-template-columns: 1fr;
    }
    
    .calculator-card {
        padding: 30px;
    }
    
    .commercial-intro,
    .rent-increase-section {
        padding: 30px;
    }
    
    .commercial-intro h2 {
        font-size: 1.8rem;
    }
}

@media (max-width: 768px) {
    .calculator-section {
        padding: 50px 0;
    }
    
    .calculator-card,
    .rent-increase-section {
        padding: 25px;
    }
    
    .calculator-header {
        flex-direction: column;
        text-align: center;
    }
    
    .calculator-icon {
        margin-bottom: 15px;
        margin-right: 0;
    }
    
    .commercial-intro h2 {
        font-size: 1.6rem;
    }
    
    .commercial-intro p {
        font-size: 1.1rem;
    }

    .rent-results-grid {
        grid-template-columns: 1fr;
    }
}

.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: #15B97C;
    color: white;
    border: none;
    border-radius: 50%;
    display: none;
    z-index: 9999;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(21, 185, 124, 0.3);
}

.scroll-top:hover {
    background: #0D1A1C;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(13, 26, 28, 0.4);
}

#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.5s ease;
}

/* Modal özel stilleri */
.user-data-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid #ddd;
    padding: 40px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.user-data-form .nav-tabs {
    border-bottom: 2px solid #15B97C;
}

.user-data-form .nav-link {
    color: #15B97C;
    font-weight: 500;
    border: none;
    border-radius: 0;
    padding: 10px 20px;
    margin-right: 10px;
    transition: all 0.3s ease;
}

.user-data-form .nav-link.active {
    color: #fff;
    background: #15B97C;
    border-radius: 20px 20px 0 0;
}

.user-data-form .tab-content {
    border: 1px solid #15B97C;
    border-radius: 0 0 20px 20px;
    padding: 30px;
    background: #fff;
}

.user-data-form .input-group-meta {
    margin-bottom: 25px;
}

.user-data-form label {
    font-weight: 500;
    margin-bottom: 10px;
    display: block;
}

.user-data-form input[type="email"],
.user-data-form input[type="password"],
.user-data-form input[type="text"],
.user-data-form input[type="tel"] {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px 15px;
    font-size: 16px;
    width: 100%;
    transition: all 0.3s ease;
}

.user-data-form input[type="email"]:focus,
.user-data-form input[type="password"]:focus,
.user-data-form input[type="text"]:focus,
.user-data-form input[type="tel"]:focus {
    border-color: #15B97C;
    box-shadow: 0 0 5px rgba(21, 185, 124, 0.5);
    outline: none;
}

.user-data-form .btn-two {
    background: #15B97C;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-data-form .btn-two:hover {
    background: #0D1A1C;
    transform: translateY(-2px);
}

/* Dropdown Styles */
.dropdown-toggle::after {
    margin-left: 0.5em;
}

.dropdown-menu {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    padding: 10px 0;
    margin-top: 5px;
}

.dropdown-item {
    padding: 8px 20px;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #15B97C;
}

.dropdown-divider {
    margin: 5px 0;
    border-color: #e9ecef;
}
</style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- ===================================================
            Yükleniyor Animasyonu
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
                        <!-- Header'da Giriş butonu -->
                        <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                    <li class="dropdown">
                                        <a href="#" class="btn-one white-vr dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="dashboard/dashboard.php">Panel</a></li>
                                            <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one white-vr">
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
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item dashboard-menu">
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

        <!-- ============================
            İç Banner
        ============================ -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Hesaplama Araçları</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>Hesaplama Araçları</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- ============================
            Ana İçerik - Hesaplama Araçları
        ============================ -->
        <div class="calculator-section">
            <div class="container">
                <!-- Giriş Bölümü -->
                <div class="commercial-intro wow fadeInUp">
                    <h2>💼 Ticari Gayrimenkul Hesaplama Araçları</h2>
                    <p>Profesyonel ticari gayrimenkul yatırımlarınız için doğru kararlar alın. ROI analizi, nakit akış hesaplama, kira artışı hesaplama ve yatırım değerlendirmesi araçlarımızla karlı yatırımlar yapın.</p>
                    
                    <?php if ($isLoggedIn): ?>
                        <div class="user-welcome-box" style="background: linear-gradient(135deg, #15B97C 0%, #0D1A1C 100%); color: white; padding: 20px; border-radius: 15px; margin: 20px 0; text-align: center;">
                            <h4 style="margin: 0; color: white;">
                                <i class="fas fa-user-check"></i> Hoş Geldiniz, <?php echo htmlspecialchars($userName); ?>!
                            </h4>
                            <p style="margin: 10px 0 0 0; opacity: 0.9;">
                                Tüm hesaplama araçlarını sınırsız kullanabilir ve sonuçları kaydedebilirsiniz.
                            </p>
                        </div>
                    <?php else: ?>
                        <div class="login-prompt-box" style="background: linear-gradient(135deg, #fff3cd 0%, #ffeaa7 100%); border: 2px solid #ffd32a; color: #856404; padding: 20px; border-radius: 15px; margin: 20px 0; text-align: center;">
                            <h5 style="margin: 0 0 10px 0; color: #856404;">
                                <i class="fas fa-info-circle"></i> Daha İyi Deneyim İçin Giriş Yapın
                            </h5>
                            <p style="margin: 0 0 15px 0;">
                                Hesaplama sonuçlarınızı kaydetmek, karşılaştırmalar yapmak ve kişiselleştirilmiş raporlar almak için ücretsiz hesap oluşturun.
                            </p>
                            <button type="button" class="btn" style="background: #15B97C; color: white; padding: 8px 20px; border: none; border-radius: 8px;" data-bs-toggle="modal" data-bs-target="#loginModal">
                                <i class="fas fa-sign-in-alt"></i> Giriş Yap / Kayıt Ol
                            </button>
                        </div>
                    <?php endif; ?>
                </div>

                <!-- Ticari Kira Artışı Hesaplama Tablosu -->
                <div class="rent-increase-section wow fadeInUp">
                    <h2 style="text-align: center; margin-bottom: 30px;">🏢 Ticari Kira Artışı Hesaplama Tablosu</h2>
                    
                    <div class="info-box">
                        <h5 style="margin-bottom: 15px;">📋 Hesaplama Yöntemleri:</h5>
                        <ul class="info-list">
                            <li><strong>TÜFE Artışı:</strong> Türkiye İstatistik Kurumu tarafından açıklanan Tüketici Fiyatları Endeksi (Otomatik güncellenebilir)</li>
                            <li><strong>ÜFE Artışı:</strong> Üretici Fiyatları Endeksi bazlı artış hesabı (Otomatik güncellenebilir)</li>
                            <li><strong>Sabit Oran:</strong> Sözleşmede belirtilen sabit yıllık artış oranı</li>
                            <li><strong>Karma Yöntem:</strong> TÜFE + sabit oran kombinasyonu</li>
                            <li><strong>Veri Kaynağı:</strong> TÜİK ve TCMB resmi verileri kullanılır</li>
                        </ul>
                        <div class="mt-3">
                            <a href="rate_manager.php" target="_blank" class="btn btn-light btn-sm">
                                <i class="fas fa-cog"></i> Veri Yönetimi
                            </a>
                        </div>
                    </div>

                    <div class="rent-calculator-grid">
                        <div class="rent-input-group">
                            <h5>📊 Kira Bilgileri</h5>
                            <div class="form-group">
                                <label for="currentRent">Mevcut Aylık Kira</label>
                                <div class="input-group">
                                    <input type="number" id="currentRent" class="form-control" placeholder="15.000" value="15000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="contractStartDate">Sözleşme Başlangıç Tarihi</label>
                                <input type="date" id="contractStartDate" class="form-control" value="2024-01-01">
                            </div>
                            <div class="form-group">
                                <label for="contractDuration">Sözleşme Süresi</label>
                                <select id="contractDuration" class="form-control">
                                    <option value="1">1 Yıl</option>
                                    <option value="2">2 Yıl</option>
                                    <option value="3" selected>3 Yıl</option>
                                    <option value="5">5 Yıl</option>
                                    <option value="10">10 Yıl</option>
                                </select>
                            </div>
                        </div>

                        <div class="rent-input-group">
                            <h5>📈 Artış Parametreleri</h5>
                            <div class="form-group">
                                <label for="tufeRate">TÜFE Oranı 
                                    <button type="button" class="btn btn-sm btn-outline-primary ms-2" onclick="loadCurrentRates()">
                                        <i class="fas fa-sync-alt"></i> Güncel Verileri Yükle
                                    </button>
                                </label>
                                <div class="input-group">
                                    <input type="number" id="tufeRate" class="form-control" placeholder="65.5" value="65.5" step="0.1">
                                    <span class="input-addon">%</span>
                                </div>
                                <small class="text-muted">Son güncelleme: <span id="tufeLastUpdate">Manuel</span></small>
                            </div>
                            <div class="form-group">
                                <label for="ufeRate">ÜFE Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="ufeRate" class="form-control" placeholder="42.5" value="42.5" step="0.1">
                                    <span class="input-addon">%</span>
                                </div>
                                <small class="text-muted">Son güncelleme: <span id="ufeLastUpdate">Manuel</span></small>
                            </div>
                            <div class="form-group">
                                <label for="fixedRate">Sabit Artış Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="fixedRate" class="form-control" placeholder="25" value="25" step="0.1">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="mixedRate">Karma Yöntem (TÜFE + %)</label>
                                <div class="input-group">
                                    <input type="number" id="mixedRate" class="form-control" placeholder="10" value="10" step="0.1">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn-calculate" onclick="calculateRentIncreases()" style="margin: 20px auto; display: block; width: 300px;">
                        💰 Kira Artışlarını Hesapla
                    </button>

                    <div class="rent-results-section" id="rentResultsSection">
                        <h3>📈 Hesaplama Sonuçları</h3>
                        <div class="rent-results-grid" id="rentResultsGrid">
                            <!-- Sonuçlar buraya eklenecek -->
                        </div>
                    </div>

                    <div class="rent-comparison-table" id="rentComparisonTable">
                        <div class="table-header">
                            <h3>📊 Yıllık Karşılaştırma Tablosu</h3>
                        </div>
                        <div style="overflow-x: auto;">
                            <table style="width: 100%; border-collapse: collapse;">
                                <thead>
                                    <tr style="background: #f8f9fa;">
                                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50;">Yıl</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50;">TÜFE Artışı</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50;">ÜFE Artışı</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50;">Sabit Oran</th>
                                        <th style="padding: 15px; text-align: left; border-bottom: 1px solid #eee; font-weight: 600; color: #2c3e50;">Karma Yöntem</th>
                                    </tr>
                                </thead>
                                <tbody id="rentComparisonTableBody">
                                    <!-- Tablo satırları buraya eklenecek -->
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Ticari Kredi Hesaplama -->
                <div class="rent-increase-section wow fadeInUp" style="background: linear-gradient(135deg, #2ecc71 0%, #27ae60 100%);">
                    <h2 style="text-align: center; margin-bottom: 30px;">🏦 Ticari Kredi Hesaplama</h2>
                    
                    <div class="info-box">
                        <h5 style="margin-bottom: 15px;">💰 Kredi Bilgileri:</h5>
                        <ul class="info-list">
                            <li><strong>Ticari Kredi:</strong> Ticari gayrimenkul alımı için özel kredi hesaplama</li>
                            <li><strong>Faiz Oranları:</strong> Güncel piyasa oranları ile hesaplama</li>
                            <li><strong>Vade Seçenekleri:</strong> 5-20 yıl arası esnek vade seçenekleri</li>
                            <li><strong>Gayrimenkul Türü:</strong> Ofis, mağaza, depo ve karma kullanım</li>
                        </ul>
                    </div>

                    <div class="rent-calculator-grid">
                        <div class="rent-input-group">
                            <h5>💼 Kredi Detayları</h5>
                            <div class="form-group">
                                <label for="commercialLoanAmount">Kredi Tutarı</label>
                                <div class="input-group">
                                    <input type="number" id="commercialLoanAmount" class="form-control" placeholder="2.000.000" value="2000000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="commercialRate">Yıllık Faiz Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="commercialRate" class="form-control" placeholder="5.75" step="0.1" value="5.75">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="commercialYears">Vade (Yıl)</label>
                                <select id="commercialYears" class="form-control">
                                    <option value="5">5 yıl</option>
                                    <option value="7">7 yıl</option>
                                    <option value="10" selected>10 yıl</option>
                                    <option value="15">15 yıl</option>
                                    <option value="20">20 yıl</option>
                                </select>
                            </div>
                        </div>

                        <div class="rent-input-group">
                            <h5>🏢 Gayrimenkul Bilgileri</h5>
                            <div class="form-group">
                                <label for="propertyType">Gayrimenkul Türü</label>
                                <select id="propertyType" class="form-control">
                                    <option value="office">Ofis</option>
                                    <option value="retail">Mağaza/Dükkan</option>
                                    <option value="warehouse">Depo/Fabrika</option>
                                    <option value="mixed">Karma Kullanım</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="downPayment">Peşinat Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="downPayment" class="form-control" placeholder="30" value="30" min="0" max="50">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="propertyValue">Gayrimenkul Değeri</label>
                                <div class="input-group">
                                    <input type="number" id="propertyValue" class="form-control" placeholder="3.000.000" value="3000000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn-calculate" onclick="calculateCommercialLoan()" style="margin: 20px auto; display: block; width: 300px;">
                        🏦 Ticari Kredi Hesapla
                    </button>

                    <div class="rent-results-section" id="commercialLoanResultSection">
                        <h3>💰 Kredi Hesaplama Sonuçları</h3>
                        <div class="rent-results-grid" id="commercialLoanResultsGrid">
                            <!-- Sonuçlar buraya eklenecek -->
                        </div>
                    </div>
                </div>

                <!-- ROI ve Yatırım Getirisi -->
                <div class="rent-increase-section wow fadeInUp" style="background: linear-gradient(135deg, #3498db 0%, #2980b9 100%);">
                    <h2 style="text-align: center; margin-bottom: 30px;">📈 ROI ve Yatırım Getirisi</h2>
                    
                    <div class="info-box">
                        <h5 style="margin-bottom: 15px;">📊 ROI Analizi:</h5>
                        <ul class="info-list">
                            <li><strong>ROI Hesaplama:</strong> Yatırımınızın yıllık getiri oranı</li>
                            <li><strong>Net Gelir:</strong> Tüm giderler düşülmüş net kira geliri</li>
                            <li><strong>Doluluk Oranı:</strong> Boşluk riski dahil hesaplama</li>
                            <li><strong>Geri Ödeme Süresi:</strong> Yatırımın kendini amorti etme süresi</li>
                        </ul>
                    </div>

                    <div class="rent-calculator-grid">
                        <div class="rent-input-group">
                            <h5>💰 Yatırım Bilgileri</h5>
                            <div class="form-group">
                                <label for="investmentAmount">Toplam Yatırım Tutarı</label>
                                <div class="input-group">
                                    <input type="number" id="investmentAmount" class="form-control" placeholder="3.000.000" value="3000000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="monthlyRentIncome">Aylık Kira Geliri</label>
                                <div class="input-group">
                                    <input type="number" id="monthlyRentIncome" class="form-control" placeholder="25.000" value="25000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="operatingExpenses">Aylık İşletme Giderleri</label>
                                <div class="input-group">
                                    <input type="number" id="operatingExpenses" class="form-control" placeholder="3.000" value="3000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                        </div>

                        <div class="rent-input-group">
                            <h5>📊 Performans Parametreleri</h5>
                            <div class="form-group">
                                <label for="occupancyRate">Doluluk Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="occupancyRate" class="form-control" placeholder="85" value="85" min="0" max="100">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="appreciationRate">Yıllık Değer Artışı</label>
                                <div class="input-group">
                                    <input type="number" id="appreciationRate" class="form-control" placeholder="5" value="5" step="0.1">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="analysisYears">Analiz Süresi</label>
                                <select id="analysisYears" class="form-control">
                                    <option value="1">1 Yıl</option>
                                    <option value="3">3 Yıl</option>
                                    <option value="5" selected>5 Yıl</option>
                                    <option value="10">10 Yıl</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <button class="btn-calculate" onclick="calculateROI()" style="margin: 20px auto; display: block; width: 300px;">
                        📈 ROI Hesapla
                    </button>

                    <div class="rent-results-section" id="roiResultSection">
                        <h3>📊 ROI Analiz Sonuçları</h3>
                        <div class="rent-results-grid" id="roiResultsGrid">
                            <!-- Sonuçlar buraya eklenecek -->
                        </div>
                    </div>
                </div>

                <!-- Ticari Gayrimenkul Değerleme -->
                <div class="rent-increase-section wow fadeInUp" style="background: linear-gradient(135deg, #9b59b6 0%, #8e44ad 100%);">
                    <h2 style="text-align: center; margin-bottom: 30px;">🏢 Ticari Gayrimenkul Değerleme</h2>
                    
                    <div class="info-box">
                        <h5 style="margin-bottom: 15px;">🏗️ Değerleme Kriterleri:</h5>
                        <ul class="info-list">
                            <li><strong>Konum Analizi:</strong> Bölge sınıfı ve erişilebilirlik faktörü</li>
                            <li><strong>Yapı Özellikleri:</strong> Yaş, kat, durum ve metrekare</li>
                            <li><strong>Piyasa Değeri:</strong> Güncel piyasa verileri ile karşılaştırma</li>
                            <li><strong>Yatırım Potansiyeli:</strong> Gelecek değer artış tahmini</li>
                        </ul>
                    </div>

                    <div class="rent-calculator-grid">
                        <div class="rent-input-group">
                            <h5>📐 Fiziksel Özellikler</h5>
                            <div class="form-group">
                                <label for="commercialSize">Metrekare</label>
                                <div class="input-group">
                                    <input type="number" id="commercialSize" class="form-control" placeholder="250" value="250">
                                    <span class="input-addon">m²</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="buildingAge">Bina Yaşı</label>
                                <div class="input-group">
                                    <input type="number" id="buildingAge" class="form-control" placeholder="10" value="10">
                                    <span class="input-addon">yıl</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="floorLevel">Kat Durumu</label>
                                <select id="floorLevel" class="form-control">
                                    <option value="0">Zemin Kat</option>
                                    <option value="1" selected>1. Kat</option>
                                    <option value="2">2-5. Kat</option>
                                    <option value="3">6+ Kat</option>
                                </select>
                            </div>
                        </div>

                        <div class="rent-input-group">
                            <h5>🌟 Kalite ve Konum</h5>
                            <div class="form-group">
                                <label for="locationGrade">Konum Sınıfı</label>
                                <select id="locationGrade" class="form-control">
                                    <option value="1.0">B Sınıfı (Varoş)</option>
                                    <option value="1.3" selected>A Sınıfı (İyi)</option>
                                    <option value="1.6">A+ Sınıfı (Merkezi)</option>
                                    <option value="2.0">Premium (CBD/AVM)</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="commercialCondition">Yapı Durumu</label>
                                <select id="commercialCondition" class="form-control">
                                    <option value="0.8">Tadilat Gerekir</option>
                                    <option value="1.0" selected>İyi Durumda</option>
                                    <option value="1.2">Yenilenmiş</option>
                                    <option value="1.4">Sıfır/Yeni</option>
                                </select>
                            </div>
                            <div class="form-group">
                                <label for="parkingSpaces">Otopark</label>
                                <div class="input-group">
                                    <input type="number" id="parkingSpaces" class="form-control" placeholder="2" value="2" min="0">
                                    <span class="input-addon">adet</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn-calculate" onclick="calculateCommercialValue()" style="margin: 20px auto; display: block; width: 300px;">
                        🏢 Değer Hesapla
                    </button>

                    <div class="rent-results-section" id="commercialValueResultSection">
                        <h3>💎 Değerleme Sonuçları</h3>
                        <div class="rent-results-grid" id="commercialValueResultsGrid">
                            <!-- Sonuçlar buraya eklenecek -->
                        </div>
                    </div>
                </div>

                <!-- Nakit Akış Analizi -->
                <div class="rent-increase-section wow fadeInUp" style="background: linear-gradient(135deg, #e74c3c 0%, #c0392b 100%);">
                    <h2 style="text-align: center; margin-bottom: 30px;">💰 Nakit Akış Analizi</h2>
                    
                    <div class="info-box">
                        <h5 style="margin-bottom: 15px;">💼 Nakit Akış Bileşenleri:</h5>
                        <ul class="info-list">
                            <li><strong>Gelir Analizi:</strong> Kira gelirleri ve diğer gelir kaynakları</li>
                            <li><strong>Gider Analizi:</strong> İşletme, bakım ve kredi giderleri</li>
                            <li><strong>Net Nakit Akış:</strong> Toplam gelir - toplam gider</li>
                            <li><strong>Nakit Akış Oranı:</strong> Yatırım performans göstergesi</li>
                        </ul>
                    </div>

                    <div class="rent-calculator-grid">
                        <div class="rent-input-group">
                            <h5>💵 Gelir Kalemleri</h5>
                            <div class="form-group">
                                <label for="totalRentIncome">Yıllık Kira Geliri</label>
                                <div class="input-group">
                                    <input type="number" id="totalRentIncome" class="form-control" placeholder="300.000" value="300000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="otherIncome">Diğer Gelirler</label>
                                <div class="input-group">
                                    <input type="number" id="otherIncome" class="form-control" placeholder="20.000" value="20000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="occupancyRateCash">Doluluk Oranı</label>
                                <div class="input-group">
                                    <input type="number" id="occupancyRateCash" class="form-control" placeholder="90" value="90" min="0" max="100">
                                    <span class="input-addon">%</span>
                                </div>
                            </div>
                        </div>

                        <div class="rent-input-group">
                            <h5>💸 Gider Kalemleri</h5>
                            <div class="form-group">
                                <label for="maintenanceCost">Yıllık Bakım-Onarım</label>
                                <div class="input-group">
                                    <input type="number" id="maintenanceCost" class="form-control" placeholder="15.000" value="15000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="propertyTax">Emlak Vergisi</label>
                                <div class="input-group">
                                    <input type="number" id="propertyTax" class="form-control" placeholder="8.000" value="8000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="insurance">Sigorta</label>
                                <div class="input-group">
                                    <input type="number" id="insurance" class="form-control" placeholder="5.000" value="5000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="managementFee">Yönetim Ücreti</label>
                                <div class="input-group">
                                    <input type="number" id="managementFee" class="form-control" placeholder="12.000" value="12000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="loanPayment">Yıllık Kredi Ödemesi</label>
                                <div class="input-group">
                                    <input type="number" id="loanPayment" class="form-control" placeholder="180.000" value="180000">
                                    <span class="input-addon">₺</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <button class="btn-calculate" onclick="calculateCashFlow()" style="margin: 20px auto; display: block; width: 300px;">
                        💰 Nakit Akış Hesapla
                    </button>

                    <div class="rent-results-section" id="cashFlowResultSection">
                        <h3>💼 Nakit Akış Analiz Sonuçları</h3>
                        <div class="rent-results-grid" id="cashFlowResultsGrid">
                            <!-- Sonuçlar buraya eklenecek -->
                        </div>
                    </div>
                </div>

                <!-- Otomatik Güncelleme Bilgisi -->
                <div class="update-info wow fadeInUp" data-wow-delay="0.4s">
                    <h5><i class="fa-solid fa-info-circle"></i> Faiz Oranları Bilgisi</h5>
                    <p>Hesapkurdu.com verilerinden her sabah saat 10:00'da otomatik güncellenmektedir.</p>
                    <p><strong>🕐 Son Güncelleme:</strong> <span id="lastUpdateTime">Yükleniyor...</span></p>
                    <p><strong>📈 Piyasa Bilgisi:</strong> <span id="marketInfo">Analiz ediliyor...</span></p>
                    
                    <div class="text-center mt-3">
                        <button class="btn-refresh" onclick="fetchCurrentRates()">
                            <i class="fa-solid fa-refresh"></i> Faiz Oranlarını Yenile
                        </button>
                        <button class="btn-info" onclick="showAutoUpdateInfo()">
                            <i class="fa-solid fa-info"></i> Otomatik Güncelleme Bilgisi
                        </button>
                    </div>
                </div>

                <!-- Uyarı Kutusu -->
                <div class="warning-box wow fadeInUp" data-wow-delay="0.6s">
                    <strong>⚠️ Uyarı:</strong> Bu hesaplamalar tahmini değerlerdir. Ticari gayrimenkul yatırımları için profesyonel değerleme ve hukuki danışmanlık alınması önerilir. 
                    <br><strong>📊 Faiz Oranları:</strong> Hesapkurdu.com verilerinden her sabah saat 10:00'da otomatik güncellenmektedir.
                    <br><strong>💼 Ticari Kredi Bilgisi:</strong> Ticari krediler bireysel kredilerden farklı koşullara sahiptir ve banka değerlendirmesine tabidir.
                </div>
            </div>
        </div>

        <!-- ============================
            İletişim Banner
        ============================ -->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50" style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
                            <h3 class="text-white m0">Ticari <span>gayrimenkul<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> danışmanlığı almak ister misiniz?</h3>
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
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="hesaplama-araclari.php">Hesaplamalar</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
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
                        <div class="col-lg-3 col-sm-4 mb-30">
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
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>

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
        
        <!-- Hesaplama Araçları Özel JS -->
        <script>
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

            // AJAX Form Handling for Login
            document.getElementById('loginForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                submitBtn.textContent = 'GİRİŞ YAPILIYOR...';
                submitBtn.disabled = true;
                
                fetch('login.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('✅ ' + data.message, 'success');
                        setTimeout(() => {
                            window.location.reload();
                        }, 1500);
                    } else {
                        showNotification('❌ ' + data.message, 'error');
                        if (data.needs_verification) {
                            setTimeout(() => {
                                document.querySelector('[data-bs-target="#fc2"]').click();
                            }, 2000);
                        }
                    }
                })
                .catch(error => {
                    showNotification('❌ Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            // AJAX Form Handling for Register
            document.getElementById('registerForm').addEventListener('submit', function(e) {
                e.preventDefault();
                
                const formData = new FormData(this);
                const submitBtn = this.querySelector('button[type="submit"]');
                const originalText = submitBtn.textContent;
                
                submitBtn.textContent = 'KAYIT EDILIYOR...';
                submitBtn.disabled = true;
                
                fetch('register-api.php', {
                    method: 'POST',
                    body: formData
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        showNotification('✅ ' + data.message, 'success');
                        if (data.requires_verification) {
                            setTimeout(() => {
                                const modal = bootstrap.Modal.getInstance(document.getElementById('loginModal'));
                                modal.hide();
                                showEmailVerificationModal();
                            }, 2000);
                        }
                    } else {
                        showNotification('❌ ' + data.message, 'error');
                    }
                })
                .catch(error => {
                    showNotification('❌ Bir hata oluştu. Lütfen tekrar deneyin.', 'error');
                })
                .finally(() => {
                    submitBtn.textContent = originalText;
                    submitBtn.disabled = false;
                });
            });

            // Email verification modal
            function showEmailVerificationModal() {
                const modalHtml = `
                <div class="modal fade" id="verificationModal" tabindex="-1" aria-hidden="true">
                    <div class="modal-dialog modal-dialog-centered">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">📧 E-posta Doğrulama</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body text-center">
                                <div class="mb-4">
                                    <div style="font-size: 4rem; color: #007bff;">📧</div>
                                </div>
                                <h4>E-postanızı Kontrol Edin</h4>
                                <p class="text-muted">
                                    Kayıt işleminiz başarıyla tamamlandı! E-posta adresinize bir doğrulama bağlantısı gönderdik.
                                </p>
                                <p>
                                    <strong>Raspberry Pi Mail Server</strong> üzerinden gönderilen doğrulama e-postasını kontrol edin ve hesabınızı aktif hale getirin.
                                </p>
                                <div class="alert alert-info">
                                    <small>
                                        <i class="fa-solid fa-info-circle"></i>
                                        E-posta gelmedi mi? Spam klasörünü kontrol edin veya birkaç dakika bekleyin.
                                    </small>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-primary" data-bs-dismiss="modal">Tamam</button>
                            </div>
                        </div>
                    </div>
                </div>
                `;
                
                document.body.insertAdjacentHTML('beforeend', modalHtml);
                const verificationModal = new bootstrap.Modal(document.getElementById('verificationModal'));
                verificationModal.show();
                
                // Remove modal after hiding
                document.getElementById('verificationModal').addEventListener('hidden.bs.modal', function() {
                    this.remove();
                });
            }

            // GÜNCEL ORANLARI YÜKLEME FONKSİYONU
            function loadCurrentRates() {
                fetch('rate_manager.php?action=get_current_rates')
                    .then(response => response.json())
                    .then(data => {
                        document.getElementById('tufeRate').value = data.tufe;
                        document.getElementById('ufeRate').value = data.ufe;
                        document.getElementById('tufeLastUpdate').textContent = data.last_updated + ' (' + data.source + ')';
                        document.getElementById('ufeLastUpdate').textContent = data.last_updated + ' (' + data.source + ')';
                        
                        // Başarı mesajı
                        showNotification('✅ Güncel oranlar yüklendi!', 'success');
                    })
                    .catch(error => {
                        console.error('Veri yükleme hatası:', error);
                        showNotification('❌ Güncel veriler yüklenemedi. Manuel değerler kullanılıyor.', 'warning');
                    });
            }

            // BİLDİRİM FONKSİYONU
            function showNotification(message, type = 'info') {
                const notification = document.createElement('div');
                notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
                notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; width: 350px;';
                notification.innerHTML = `
                    ${message}
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                `;
                
                document.body.appendChild(notification);
                
                // 5 saniye sonra otomatik kapat
                setTimeout(() => {
                    if (notification.parentNode) {
                        notification.remove();
                    }
                }, 5000);
            }

            // 💰 KIRA ARTIŞI HESAPLAMA FONKSİYONU
            function calculateRentIncreases() {
                // Form verilerini al
                const currentRent = parseFloat(document.getElementById('currentRent').value) || 0;
                const contractStartDate = document.getElementById('contractStartDate').value;
                const contractDuration = parseInt(document.getElementById('contractDuration').value) || 1;
                const tufeRate = parseFloat(document.getElementById('tufeRate').value) || 0;
                const ufeRate = parseFloat(document.getElementById('ufeRate').value) || 0;
                const fixedRate = parseFloat(document.getElementById('fixedRate').value) || 0;
                const mixedRate = parseFloat(document.getElementById('mixedRate').value) || 0;

                // Validation
                if (currentRent <= 0) {
                    alert('Lütfen geçerli bir kira tutarı girin!');
                    return;
                }

                // Hesaplamaları yap
                const results = [];
                let tufeRent = currentRent;
                let ufeRent = currentRent;
                let fixedRent = currentRent;
                let mixedRent = currentRent;

                // Yıllık hesaplama
                for (let year = 1; year <= contractDuration; year++) {
                    // TÜFE artışı
                    tufeRent = tufeRent * (1 + tufeRate / 100);
                    
                    // ÜFE artışı
                    ufeRent = ufeRent * (1 + ufeRate / 100);
                    
                    // Sabit artış
                    fixedRent = fixedRent * (1 + fixedRate / 100);
                    
                    // Karma artış (TÜFE + sabit)
                    mixedRent = mixedRent * (1 + (tufeRate + mixedRate) / 100);

                    results.push({
                        year: year,
                        tufe: tufeRent,
                        ufe: ufeRent,
                        fixed: fixedRent,
                        mixed: mixedRent
                    });
                }

                // Sonuçları göster
                displayRentResults(results, currentRent);
                displayRentTable(results, currentRent);
            }

            function displayRentResults(results, initialRent) {
                const resultSection = document.getElementById('rentResultsSection');
                const resultsGrid = document.getElementById('rentResultsGrid');
                
                const lastYear = results[results.length - 1];
                
                resultsGrid.innerHTML = `
                    <div class="rent-result-card">
                        <h6>📊 TÜFE Bazlı Artış</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                            ${formatMoney(lastYear.tufe)} ₺
                        </div>
                        <small>Son yıl aylık kira</small>
                        <div style="margin-top: 10px;">
                            <span style="color: #00b894;">+${formatMoney(lastYear.tufe - initialRent)} ₺ artış</span>
                        </div>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>🏭 ÜFE Bazlı Artış</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                            ${formatMoney(lastYear.ufe)} ₺
                        </div>
                        <small>Son yıl aylık kira</small>
                        <div style="margin-top: 10px;">
                            <span style="color: #00b894;">+${formatMoney(lastYear.ufe - initialRent)} ₺ artış</span>
                        </div>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>📈 Sabit Oran Artışı</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                            ${formatMoney(lastYear.fixed)} ₺
                        </div>
                        <small>Son yıl aylık kira</small>
                        <div style="margin-top: 10px;">
                            <span style="color: #00b894;">+${formatMoney(lastYear.fixed - initialRent)} ₺ artış</span>
                        </div>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>🔄 Karma Yöntem</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0;">
                            ${formatMoney(lastYear.mixed)} ₺
                        </div>
                        <small>Son yıl aylık kira</small>
                        <div style="margin-top: 10px;">
                            <span style="color: #00b894;">+${formatMoney(lastYear.mixed - initialRent)} ₺ artış</span>
                        </div>
                    </div>
                `;
                
                resultSection.classList.add('show');
                resultSection.scrollIntoView({ behavior: 'smooth' });
            }

            function displayRentTable(results, initialRent) {
                const tableBody = document.getElementById('rentComparisonTableBody');
                const comparisonTable = document.getElementById('rentComparisonTable');
                
                let tableHTML = `
                    <tr style="background: #e3f2fd;">
                        <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: 600;">Başlangıç</td>
                        <td style="padding: 15px; border-bottom: 1px solid #eee;">${formatMoney(initialRent)} ₺</td>
                        <td style="padding: 15px; border-bottom: 1px solid #eee;">${formatMoney(initialRent)} ₺</td>
                        <td style="padding: 15px; border-bottom: 1px solid #eee;">${formatMoney(initialRent)} ₺</td>
                        <td style="padding: 15px; border-bottom: 1px solid #eee;">${formatMoney(initialRent)} ₺</td>
                    </tr>
                `;
                
                results.forEach((result, index) => {
                    const rowColor = index % 2 === 0 ? '#f8f9fa' : '#ffffff';
                    tableHTML += `
                        <tr style="background: ${rowColor};">
                            <td style="padding: 15px; border-bottom: 1px solid #eee; font-weight: 600;">${result.year}. Yıl</td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; color: #2e7d32; font-weight: 600;">${formatMoney(result.tufe)} ₺</td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; color: #1565c0; font-weight: 600;">${formatMoney(result.ufe)} ₺</td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; color: #e65100; font-weight: 600;">${formatMoney(result.fixed)} ₺</td>
                            <td style="padding: 15px; border-bottom: 1px solid #eee; color: #6a1b9a; font-weight: 600;">${formatMoney(result.mixed)} ₺</td>
                        </tr>
                    `;
                });
                
                tableBody.innerHTML = tableHTML;
                comparisonTable.style.display = 'block';
            }

            // 🏦 TİCARİ KREDİ HESAPLAMA FONKSİYONU
            function calculateCommercialLoan() {
                const loanAmount = parseFloat(document.getElementById('commercialLoanAmount').value) || 0;
                const yearlyRate = parseFloat(document.getElementById('commercialRate').value) || 0;
                const years = parseInt(document.getElementById('commercialYears').value) || 1;
                const propertyValue = parseFloat(document.getElementById('propertyValue').value) || 0;
                const downPaymentPercent = parseFloat(document.getElementById('downPayment').value) || 0;

                if (loanAmount <= 0 || yearlyRate <= 0) {
                    alert('Lütfen geçerli kredi tutarı ve faiz oranı girin!');
                    return;
                }

                // Hesaplamalar
                const monthlyRate = yearlyRate / 100 / 12;
                const totalMonths = years * 12;
                const monthlyPayment = loanAmount * (monthlyRate * Math.pow(1 + monthlyRate, totalMonths)) / (Math.pow(1 + monthlyRate, totalMonths) - 1);
                const totalPayment = monthlyPayment * totalMonths;
                const totalInterest = totalPayment - loanAmount;
                const downPayment = propertyValue * (downPaymentPercent / 100);

                const resultSection = document.getElementById('commercialLoanResultSection');
                const resultsGrid = document.getElementById('commercialLoanResultsGrid');
                
                resultsGrid.innerHTML = `
                    <div class="rent-result-card">
                        <h6>💰 Aylık Taksit</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #e74c3c;">
                            ${formatMoney(monthlyPayment)} ₺
                        </div>
                        <small>${totalMonths} ay boyunca</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>💸 Peşinat Tutarı</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #f39c12;">
                            ${formatMoney(downPayment)} ₺
                        </div>
                        <small>%${downPaymentPercent} peşinat</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>💵 Toplam Ödeme</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #8e44ad;">
                            ${formatMoney(totalPayment)} ₺
                        </div>
                        <small>Kredi + faiz toplamı</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>📊 Toplam Faiz</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #e67e22;">
                            ${formatMoney(totalInterest)} ₺
                        </div>
                        <small>Ödenecek faiz toplamı</small>
                    </div>
                `;
                
                resultSection.classList.add('show');
                resultSection.scrollIntoView({ behavior: 'smooth' });
            }

            // 📈 ROI HESAPLAMA FONKSİYONU
            function calculateROI() {
                const investmentAmount = parseFloat(document.getElementById('investmentAmount').value) || 0;
                const monthlyRentIncome = parseFloat(document.getElementById('monthlyRentIncome').value) || 0;
                const operatingExpenses = parseFloat(document.getElementById('operatingExpenses').value) || 0;
                const occupancyRate = parseFloat(document.getElementById('occupancyRate').value) || 100;
                const appreciationRate = parseFloat(document.getElementById('appreciationRate').value) || 0;
                const analysisYears = parseInt(document.getElementById('analysisYears').value) || 1;

                if (investmentAmount <= 0 || monthlyRentIncome <= 0) {
                    alert('Lütfen geçerli yatırım tutarı ve kira geliri girin!');
                    return;
                }

                // Hesaplamalar
                const effectiveMonthlyIncome = monthlyRentIncome * (occupancyRate / 100);
                const netMonthlyIncome = effectiveMonthlyIncome - operatingExpenses;
                const annualNetIncome = netMonthlyIncome * 12;
                const cashOnCashReturn = (annualNetIncome / investmentAmount) * 100;
                const paybackPeriod = investmentAmount / annualNetIncome;
                const propertyValueAfter = investmentAmount * Math.pow(1 + appreciationRate / 100, analysisYears);
                const totalReturn = (annualNetIncome * analysisYears) + (propertyValueAfter - investmentAmount);
                const totalROI = (totalReturn / investmentAmount) * 100;

                const resultSection = document.getElementById('roiResultSection');
                const resultsGrid = document.getElementById('roiResultsGrid');
                
                resultsGrid.innerHTML = `
                    <div class="rent-result-card">
                        <h6>💰 Net Aylık Gelir</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #27ae60;">
                            ${formatMoney(netMonthlyIncome)} ₺
                        </div>
                        <small>Giderler düşülmüş net</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>📊 Yıllık ROI</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #3498db;">
                            %${cashOnCashReturn.toFixed(2)}
                        </div>
                        <small>Cash-on-Cash getiri</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>⏱️ Geri Ödeme Süresi</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #e74c3c;">
                            ${paybackPeriod.toFixed(1)} yıl
                        </div>
                        <small>Amorti etme süresi</small>
                    </div>
                    
                    <div class="rent-result-card">
                        <h6>🎯 ${analysisYears} Yıllık Toplam ROI</h6>
                        <div style="font-size: 1.8rem; font-weight: bold; margin: 10px 0; color: #9b59b6;">
                            %${totalROI.toFixed(2)}
                        </div>
                        <small>Değer artışı dahil</small>
                    </div>
                `;
                
                resultSection.classList.add('show');
                resultSection.scrollIntoView({ behavior: 'smooth' });
            }

            // PARA FORMATLAMA FONKSİYONU
            function formatMoney(amount) {
                return new Intl.NumberFormat('tr-TR', {
                    minimumFractionDigits: 0,
                    maximumFractionDigits: 0
                }).format(amount);
            }

            // Sayfa yüklendiğinde
            document.addEventListener('DOMContentLoaded', function() {
                console.log('🧮 Hesaplama Araçları JavaScript yüklendi!');
                
                // Sayfa yüklendiğinde güncel oranları otomatik yükle
                loadCurrentRates();
                
                // Preloader'ı kapat
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