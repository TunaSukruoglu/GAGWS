<?php
// Hata ayıklama için
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';
$userRole = $isLoggedIn ? $_SESSION['user_role'] ?? 'user' : '';

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

echo "<!-- DEBUG: index.php başladı -->";

// Hata yakalama
try {
    include 'db.php';
    echo "<!-- DEBUG: db.php yüklendi -->";
    include 'includes/common-functions.php';
    echo "<!-- DEBUG: common-functions.php yüklendi -->";
} catch (Exception $e) {
    error_log("DB connection error: " . $e->getMessage());
    die("Veritabanı bağlantı hatası: " . $e->getMessage());
}

// Kategori isimleri
$category_names = [
    'office' => 'Ofis',
    'shop' => 'Dükkan',
    'warehouse' => 'Depo',
    'land' => 'Arsa',
    'building' => 'Bina',
    'other' => 'Diğer'
];

// Öne çıkan ilanları çek (sadece featured = 1 olanlar) - DÜZELTME
$featured_query = $conn->prepare("
    SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email
    FROM properties p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE p.status IN ('active', 'approved') AND p.featured = 1
    ORDER BY p.created_at DESC 
    LIMIT 12
");
$featured_query->execute();
$featured_properties = $featured_query->get_result()->fetch_all(MYSQLI_ASSOC);

// Eğer featured ilan yoksa, tüm aktif ilanları göster
if (empty($featured_properties)) {
    $featured_query = $conn->prepare("
        SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email
        FROM properties p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.status IN ('active', 'approved')
        ORDER BY p.created_at DESC 
        LIMIT 12
    ");
    $featured_query->execute();
    $featured_properties = $featured_query->get_result()->fetch_all(MYSQLI_ASSOC);
}

// Başarı mesajları
if (isset($_SESSION['register_success'])) {
    $msg = json_encode($_SESSION['register_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_success']);
}

if (isset($_SESSION['login_success'])) {
    $msg = json_encode($_SESSION['login_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_success']);
}

// Hata mesajları
if (isset($_SESSION['register_error'])) {
    $msg = json_encode($_SESSION['register_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['login_error'])) {
    $msg = json_encode($_SESSION['login_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_error']);
}

// Güvenli alan değeri alma
function getAreaValue($property) {
    // Öncelik sırası: size_sqft > area > size
    if (isset($property['size_sqft']) && $property['size_sqft'] > 0) {
        return $property['size_sqft'];
    }
    if (isset($property['area']) && $property['area'] > 0) {
        return $property['area'];
    }
    if (isset($property['size']) && $property['size'] > 0) {
        return $property['size'];
    }
    return 0;
}

?>

<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul, iş yeri, ofis, dükkan, ticari gayrimenkul, kiralık iş yeri, satılık iş yeri">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul: İş yeri, ofis, dükkan ve ticari gayrimenkul alım-satım ve kiralama hizmetleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Gökhan Aydınlı Gayrimenkul | İş Yeri ve Ticari Emlak">
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
    <title>Gökhan Aydınlı Gayrimenkul | İş Yeri ve Ticari Emlak</title>
    <!-- Favicon -->
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <!-- Bootstrap CSS - CDN -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Animate.css -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/animate.css/4.1.1/animate.min.css" rel="stylesheet">

    <!-- Local CSS Files - Kontrol edin -->
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- Akıllı resim sistemi -->
    <link rel="stylesheet" type="text/css" href="css/smart-image-system.css" media="all">

    <!-- Loader sorunu için CSS düzeltmesi -->
    <style>
        /* Loader'ı hızlıca gizle */
        #preloader {
            position: fixed;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            background: #fff;
            z-index: 9999;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        /* Feedback slider düzeltmesi */
        .feedback-slider-four {
            overflow: hidden;
        }

        .feedback-slider-four .item {
            display: inline-block;
            animation: slide-left 20s linear infinite;
        }

        @keyframes slide-left {
            0% { transform: translateX(100%); }
            100% { transform: translateX(-100%); }
        }

        /* Öne Çıkan İlanlar için özel stiller */
        .listing-card-one {
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .listing-card-one:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.1) !important;
        }

        .listing-card-one .img-gallery {
            position: relative;
        }

        .listing-card-one .tag {
            position: absolute;
            top: 20px;
            left: 20px;
            padding: 8px 15px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
        }

        .feature-tag {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 8px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            z-index: 2;
        }

        .property-info .title {
            color: #0D1A1C;
            font-size: 18px;
            font-weight: 600;
            text-decoration: none;
            display: block;
            line-height: 1.4;
        }

        .property-info .title:hover {
            color: #15B97C;
        }

        .address {
            color: #666;
            font-size: 14px;
        }

        .address i {
            color: #15B97C;
            margin-right: 8px;
        }

        .short-description {
            color: #777;
            font-size: 14px;
            line-height: 1.5;
        }

        .feature-list {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .feature-list li {
            margin-bottom: 8px;
            color: #666;
        }

        .feature-list li i {
            color: #15B97C;
            margin-right: 10px;
            width: 16px;
            text-align: center;
        }

        .amenities-tags {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .amenity-tag {
            background: #f8f9fa;
            color: #495057;
            padding: 4px 8px;
            border-radius: 12px;
            font-size: 11px;
            font-weight: 500;
            border: 1px solid #e9ecef;
        }

        .amenity-tag.more {
            background: #15B97C;
            color: white;
            border-color: #15B97C;
        }

        .pl-footer {
            padding-top: 20px;
            border-top: 1px solid #f0f0f0;
        }

        .price {
            color: #15B97C !important;
            font-size: 20px !important;
            font-weight: 700 !important;
        }

        .btn-four {
            background: #15B97C;
            color: white;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            text-decoration: none;
            transition: all 0.3s ease;
        }

        .btn-four:hover {
            background: #0d8c5a;
            transform: scale(1.1);
            color: white;
        }

        .btn-five {
            background: linear-gradient(135deg, #15B97C, #0d8c5a);
            color: white;
            padding: 15px 30px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            display: inline-flex;
            align-items: center;
        }

        .btn-five:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(21, 185, 124, 0.3);
            color: white;
        }

        .no-properties-message {
            padding: 60px 20px;
        }

        /* Default image için CSS placeholder */
        .img-gallery img[src*="default-property.jpg"] {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            position: relative;
        }

        .img-gallery img[src*="default-property.jpg"]:before {
            content: "🏠";
            position: absolute;
            top: 50%;
            left: 50%;
            transform: translate(-50%, -50%);
            font-size: 60px;
            color: #ccc;
        }

        .img-data img {
            border-radius: 12px;
            transition: transform 0.3s ease;
        }

        .listing-card-one:hover .img-data img {
            transform: scale(1.05);
        }

        .property-data {
            padding: 20px;
        }

        .price {
            font-size: 24px;
            font-weight: 700;
            color: #15B97C;
        }

        /* Modal özel stilleri */
.user-data-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    padding: 40px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.user-data-form .modal-content {
    border: none;
    border-radius: 20px;
}

.user-data-form .nav-tabs {
    border: none;
    margin-bottom: 30px;
}

.user-data-form .nav-link {
    border: none;
    border-radius: 15px;
    padding: 10px 20px;
    font-size: 16px;
    font-weight: 500;
    color: #0D1A1C;
    background: #f8f9fa;
    transition: all 0.3s ease;
}

.user-data-form .nav-link.active {
    color: #fff;
    background: #15B97C;
}

.user-data-form .input-group-meta {
    position: relative;
    margin-bottom: 25px;
}

.user-data-form .input-group-meta label {
    font-size: 14px;
    font-weight: 500;
    color: #333;
    margin-bottom: 8px;
    display: block;
}

.user-data-form .input-group-meta input {
    height: 50px;
    padding: 10px 15px;
    font-size: 16px;
    border: 1px solid #e0e0e0;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.user-data-form .input-group-meta input:focus {
    border-color: #15B97C;
    box-shadow: 0 0 5px rgba(21, 185, 124, 0.3);
}

.user-data-form .btn-two {
    background: #15B97C;
    color: #fff;
    padding: 12px 20px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    transition: all 0.3s ease;
}

.user-data-form .btn-two:hover {
    background: #0d8c5a;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(21, 185, 124, 0.3);
}

.user-data-form .agreement-checkbox {
    font-size: 14px;
    color: #666;
}

.user-data-form .agreement-checkbox input {
    margin-right: 10px;
}

.user-data-form .social-use-btn {
    background: #f0f0f0;
    color: #333;
    padding: 10px 15px;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 500;
    text-align: center;
    display: inline-block;
    width: 100%;
    transition: all 0.3s ease;
}

.user-data-form .social-use-btn:hover {
    background: #e0e0e0;
    transform: translateY(-2px);
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.1);
}

.user-data-form .placeholder_icon {
    position: absolute;
    top: 50%;
    right: 15px;
    transform: translateY(-50%);
    pointer-events: none;
}

.user-data-form .passVicon {
    width: 20px;
    height: 20px;
    display: inline-block;
    vertical-align: middle;
    line-height: 20px;
    text-align: center;
    color: #15B97C;
}        .user-data-form .passVicon img {
            max-width: 100%;
            height: auto;
        }

        /* Avatar stilleri - Müşteri yorumları için */
        .avatar {
            width: 60px;
            height: 60px;
            object-fit: cover;
            border: 3px solid #fff;
            box-shadow: 0 4px 12px rgba(0,0,0,0.15);
        }

        .feedback-block-six .avatar {
            width: 50px;
            height: 50px;
        }

        /* Rotating Background Styles */
        .hero-banner-eight {
            position: relative;
            overflow: hidden;
        }

        .rotating-background {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            z-index: 0;
        }

        .background-item {
            position: absolute;
            top: 0;
            left: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            transition: opacity 2s ease-in-out;
        }

        .background-item.active {
            opacity: 1;
        }

        .background-image {
            background-size: cover;
            background-position: center;
            background-repeat: no-repeat;
            width: 100%;
            height: 100%;
        }

        .video-background {
            width: 100%;
            height: 100%;
            position: relative;
        }

        .video-background iframe {
            position: absolute;
            top: 50%;
            left: 50%;
            width: 100vw;
            height: 56.25vw; /* 16:9 aspect ratio */
            min-height: 100vh;
            min-width: 177.78vh; /* 16:9 aspect ratio */
            transform: translate(-50%, -50%);
            object-fit: cover;
        }

        @media (max-width: 768px) {
            .listing-card-one {
                margin-bottom: 25px;
            }
            
            .feature-list {
                display: grid;
                grid-template-columns: 1fr 1fr;
                gap: 8px;
            }
            
            .amenities-tags {
                justify-content: center;
            }

            .video-background iframe {
                height: 100vh;
                width: 177.78vh;
            }
        }
    </style>

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
        <header class="theme-main-menu menu-overlay menu-style-seven white-vr sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="index.php" class="d-flex align-items-center">
                                <img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
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
                    <?php if ($userRole === 'admin'): ?>
                        <li><a class="dropdown-item" href="dashboard/dashboard-admin.php">Admin Panel</a></li>
                    <?php else: ?>
                        <li><a class="dropdown-item" href="dashboard/dashboard-user.php">Kullanıcı Paneli</a></li>
                    <?php endif; ?>
                    <li><a class="dropdown-item" href="dashboard/user-profile.php">Profil</a></li>
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
                                        <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="hakkimizda.php" >Hakkımızda</a>
                                    </li>

                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="portfoy.php" target= >Portföy</a>
                                    </li>

                                    <li class="nav-item dropdown">
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
            Hero Banner
        ============================================== 
        -->
        <div class="hero-banner-eight z-1 pt-250 xl-pt-200 pb-250 xl-pb-150 lg-pb-100 position-relative">
            <!-- Rotating Background -->
            <div class="rotating-background">
                <!-- Background Video (İLK AÇILIŞTA AKTİF) -->
                <div class="background-item active" id="backgroundVideo">
                    <div class="video-background">
                        <iframe
                            id="cloudflareVideo"
                            src=""
                            allow="accelerometer; gyroscope; autoplay; encrypted-media; picture-in-picture;"
                            allowfullscreen="true">
                        </iframe>
                    </div>
                </div>
                
                <!-- Background Image -->
                <div class="background-item" id="backgroundImage">
                    <div class="background-image" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/2a38b5b7-fe9d-49ec-082f-8a5238b34700/public');"></div>
                </div>
            </div>
            
            <!-- Siyah overlay eklendi -->
            <div style="position:absolute; inset:0; background:rgba(0,0,0,0.35); z-index:1;"></div>
            <div class="container position-relative" style="z-index:2; position:relative;">
                <div class="row">
                    <div class="col-xl-9 col-lg-10 col-md-10 m-auto">
                        <h1 class="hero-heading text-white text-center wow fadeInUp">
                            Doğru İş Yeri ve Ticari Emlak Burada
                        </h1>
                        <p class="fs-24 text-white text-center pt-35 wow fadeInUp" data-wow-delay="0.1s">
                            Ofis, dükkan, depo ve tüm ticari gayrimenkul ihtiyaçlarınız için güvenilir çözüm ortağınız.
                        </p>
                    </div>
                </div>
                <div class="search-wrapper-four me-auto ms-auto mt-45 lg-mt-20 position-relative">
                    <nav class="d-flex justify-content-center">
                        <div class="nav nav-tabs border-0" role="tablist">
                            <button class="nav-link active" id="buy-tab" data-bs-toggle="tab" data-bs-target="#buy" type="button" role="tab" aria-controls="buy" aria-selected="true">Kiralık</button>
                            <button class="nav-link" id="rent-tab" data-bs-toggle="tab" data-bs-target="#rent" type="button" role="tab" aria-controls="rent" aria-selected="false">Satılık</button>
                        </div>
                    </nav>
                    <div class="bg-wrapper mt-30">
                        <div class="tab-content">
                            <div class="tab-pane fade show active" id="buy" role="tabpanel" aria-labelledby="buy-tab" tabindex="0">
                                <form action="portfoy.php" method="GET" class="position-relative z-1" onsubmit="console.log('Form submit edildi');">
                                    <input type="text" name="location" placeholder="Bölge veya lokasyon adı yazınız..." required style="width: 80%; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
                                    <input type="hidden" name="type" value="rent">
                                    <button type="submit" class="tran3s" style="width: 18%; padding: 15px; background: #15B97C; color: white; border: none; border-radius: 5px; cursor: pointer;"><img src="images/icon/icon_75.svg" alt="" class="m-auto"></button>
                                </form>
                            </div>
                            <div class="tab-pane fade" id="rent" role="tabpanel" aria-labelledby="rent-tab" tabindex="0">
                                <form action="portfoy.php" method="GET" class="position-relative z-1" onsubmit="console.log('Form submit edildi');">
                                    <input type="text" name="location" placeholder="Bölge veya lokasyon adı yazınız..." required style="width: 80%; padding: 15px; border: 1px solid #ccc; border-radius: 5px;">
                                    <input type="hidden" name="type" value="sale">
                                    <button type="submit" class="tran3s" style="width: 18%; padding: 15px; background: #15B97C; color: white; border: none; border-radius: 5px; cursor: pointer;"><img src="images/icon/icon_75.svg" alt="" class="m-auto"></button>
                                </form>
                            </div>
                        </div>
                        <!-- /.tab-content -->
                    </div>
                </div>
                <!-- /.search-wrapper-four -->
            </div>
        </div>
        <!-- /.hero-banner-eight -->

        <!--
        =====================================================
            BLock Feature One
        =====================================================
        -->
        <div class="block-feature-one mt-150 xl-mt-120">
            <div class="container container-large">
                <div class="title-one text-center mb-60 xl-mb-30 lg-mb-20 wow fadeInUp">
                    <h3>İşiniz İçin Doğru Ticari Gayrimenkulü Bulun</h3>
                    <p class="fs-24">Her ticari işlemde güvenilir çözüm ortağınız.</p>
                </div>
                <!-- /.title-one -->
                
                <div class="row gx-xxl-5">
                    <div class="col-md-4">
                        <div class="card-style-twelve text-center wow fadeInUp mt-20">
                            <div class="icon d-flex align-items-center justify-content-center m-auto tran3s rounded-circle"><img src="images/icon/icon_76.svg" alt=""></div>
                            <h6 class="fs-20 text-uppercase fw-bold">Çeşitli Ticari Portföy</h6>
                            <p class="fs-22 ps-xxl-4 pe-xxl-4">İş yeri, ofis, dükkan ve depo seçenekleriyle geniş portföy.</p>
                        </div>
                        <!-- /.card-style-twelve -->
                    </div>
                    <div class="col-md-4">
                        <div class="card-style-twelve text-center wow fadeInUp mt-20" data-wow-delay="0.1s">
                            <div class="icon d-flex align-items-center justify-content-center m-auto tran3s rounded-circle"><img src="images/icon/icon_77.svg" alt=""></div>
                            <h6 class="fs-20 text-uppercase fw-bold">Kiralık & Satılık</h6>
                            <p class="fs-24 ps-xxl-4 pe-xxl-4">İşinize en uygun ticari gayrimenkulü hızlıca bulun.</p>
                        </div>
                        <!-- /.card-style-twelve -->
                    </div>
                    <div class="col-md-4">
                        <div class="card-style-twelve text-center wow fadeInUp mt-20" data-wow-delay="0.2s">
                            <div class="icon d-flex align-items-center justify-content-center m-auto tran3s rounded-circle"><img src="images/icon/icon_78.svg" alt=""></div>
                            <h6 class="fs-20 text-uppercase fw-bold">Hızlı ve Kolay Süreç</h6>
                            <p class="fs-24 ps-xxl-4 pe-xxl-4">Ticari taşınmaz alım-satım ve kiralamada pratik çözümler.</p>
                        </div>
                        <!-- /.card-style-twelve -->
                    </div>
                </div>
                
                <!-- Ek Resim Bölümü -->
                <div class="row mt-80 lg-mt-60">
                    <div class="col-12">
                        <div class="text-center wow fadeInUp" data-wow-delay="0.3s">
                            <div class="position-relative d-inline-block">
                                <img src="https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/2a38b5b7-fe9d-49ec-082f-8a5238b34700/public" 
                                     alt="Ticari Gayrimenkul" 
                                     class="img-fluid rounded-4 shadow-lg"
                                     style="max-height: 400px; width: auto; object-fit: cover;">
                                <div class="position-absolute top-0 start-0 w-100 h-100 rounded-4" 
                                     style="background: linear-gradient(45deg, rgba(21,185,124,0.1), rgba(13,26,28,0.1));"></div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.block-feature-one -->

        <!-- 
        =============================================
            Property Listing One
        ============================================== 
        -->
        <div class="property-listing-one mt-170 xl-mt-120">
    <div class="container container-large">
        <div class="position-relative">
            <div class="title-one text-center mb-25 lg-mb-10 wow fadeInUp">
                <h3>Öne Çıkan Ticari İlanlar</h3>
                <p class="fs-22 mt-xs">Satılık ve kiralık en iyi iş yeri, ofis ve dükkanları keşfedin.</p>
            </div>
            <!-- /.title-one -->

            <div class="row gx-xxl-5">
                <?php if (!empty($featured_properties)): ?>
                    <?php foreach ($featured_properties as $index => $property): 
                        $delay = $index * 0.1;
                        $listing_type_text = ($property['type'] === 'sale') ? 'SATILIK' : 'KİRALIK';
                        $category_text = $category_names[$property['category']] ?? ucfirst($property['category']);
                        
                        // İlk resmi al - HIZLI DÜZELTME
                        $first_image = 'images/listing/img_20.jpg'; // default fallback
                        
                        if (!empty($property['images'])) {
                            // JSON parse et
                            $images = json_decode($property['images'], true);
                            if (is_array($images) && !empty($images)) {
                                $first_image_name = trim($images[0]);
                                $first_image = "smart-image.php?img=" . urlencode($first_image_name) . "&v=" . time();
                            } else {
                                // JSON değilse virgülle ayır  
                                $split_images = explode(',', $property['images']);
                                if (!empty($split_images[0])) {
                                    $first_image_name = trim($split_images[0]);
                                    $first_image = "smart-image.php?img=" . urlencode($first_image_name) . "&v=" . time();
                                }
                            }
                        }
                    ?>
                    <div class="col-lg-4 col-md-6 mt-40 wow fadeInUp" <?= $delay > 0 ? 'data-wow-delay="'.$delay.'s"' : '' ?>>
                        <div class="listing-card-four auto-rotate overflow-hidden d-flex align-items-end position-relative z-1" 
                             style="background-image: url('<?= htmlspecialchars($first_image) ?>'); background-size: cover; background-position: center; min-height: 300px; image-orientation: from-image;">
        
        <div class="tag fw-500 <?= $property['type'] === 'sale' ? 'bg-success' : 'bg-primary' ?>">
            <?= $listing_type_text ?>
        </div>
                            <div class="property-info tran3s w-100">
                                <div class="d-flex align-items-center justify-content-between">
                                    <div class="pe-3">
                                        <a href="property-details.php?id=<?= $property['id'] ?>" class="title fw-500 tran4s">
                                            <?= htmlspecialchars($property['title']) ?>
                                        </a>
                                        <div class="address tran4s">
                                            <?= htmlspecialchars($property['address']) ?>
                                        </div>
                                        <div class="price fw-bold text-warning mt-2">
                                            <?= formatPrice($property['price']) ?>
                                        </div>
                                    </div>
                                    <div class="d-flex flex-column gap-2">
                                        <button type="button" class="btn btn-sm btn-outline-danger favorite-btn" 
                                                data-property-id="<?= $property['id'] ?>" 
                                                title="Favorilere Ekle"
                                                onclick="event.stopPropagation(); toggleFavorite(<?= $property['id'] ?>)">
                                            <i class="bi bi-heart"></i>
                                        </button>
                                        <a href="property-details.php?id=<?= $property['id'] ?>" class="btn-four inverse">
                                            <i class="bi bi-arrow-up-right"></i>
                                        </a>
                                    </div>
                                </div>
                                
                                <div class="pl-footer tran4s">
                                    <ul class="style-none feature d-flex flex-wrap align-items-center justify-content-between">
                                        <?php 
                                        // Alan bilgisi - area sütununu kullan
                                        if (($property['area'] ?? 0) > 0): 
                                        ?>
                                        <li>
                                            <strong class="color-dark fw-500"><?= $property['area'] ?></strong> 
                                            <span class="fs-16">m²</span>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if (($property['bedrooms'] ?? 0) > 0): ?>
                                        <li>
                                            <strong class="color-dark fw-500"><?= $property['bedrooms'] ?></strong> 
                                            <span class="fs-16">yatak</span>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <?php if (($property['bathrooms'] ?? 0) > 0): ?>
                                        <li>
                                            <strong class="color-dark fw-500"><?= $property['bathrooms'] ?></strong> 
                                            <span class="fs-16">banyo</span>
                                        </li>
                                        <?php endif; ?>
                                        
                                        <li>
                                            <strong class="color-dark fw-500"><?= $category_text ?></strong> 
                                            <span class="fs-16">kategori</span>
                                        </li>
                                    </ul>
                                </div>
                            </div>
                            <!-- /.property-info -->
                        </div>
                        <!-- /.listing-card-four -->
                    </div>
                    <?php endforeach; ?>
                    
                <?php else: ?>
                    <!-- İlan yoksa placeholder göster -->
                    <div class="col-12 text-center">
                        <div class="py-5">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h5 class="text-muted">Henüz öne çıkan ilan bulunmuyor</h5>
                            <p class="text-muted">İlk öne çıkan ilanlarınızı ekleyin</p>
                            <a href="dashboard/add-property.php" class="btn btn-primary">
                                <i class="fas fa-plus me-2"></i>İlan Ekle
                            </a>
                        </div>
                    </div>
                <?php endif; ?>
            </div>

            <div class="text-center mt-100 md-mt-60">
                <a href="listings.php" class="btn-eight">
                    <span>Bütün İlanlar</span> 
                    <i class="bi bi-arrow-up-right"></i>
                </a>
            </div>
        </div>
    </div>
</div>
<!-- /.property-listing-one -->

        <!--
        =====================================================
            Fancy Banner Nine
        =====================================================
        -->
        <div class="fancy-banner-nine mt-110 lg-mt-80">
            <div class="container container-large">
                <div class="row align-items-center">
                    <div class="col-lg-4">
                        <div class="d-flex align-items-center md-mb-30">
                            <img src="images/GAUfak.png" alt="" class="rounded-circle avatar">
                            <div class="ps-3 text">
                                <h6 class="fs-22">Gökhan Aydınlı</h6>
                                <span class="fs-20">Kurucu & Ticari Gayrimenkul Uzmanı</span>
                            </div>
                        </div>
                    </div>
                    <div class="col-xxl-7 col-lg-8">
                        <blockquote>"Yatırımcılarımıza en verimli ticari gayrimenkul fırsatlarını sunmak için sistemli bir süreç izliyoruz."</blockquote>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.fancy-banner-nine -->

        <!--
        =====================================================
            BLock Feature Fourteen
        =====================================================
        -->
        <div class="block-feature-fourteen pt-120 xl-pt-100 pb-140 xl-pb-100 mt-170 xl-mt-120">
            <div class="container container-large">
                <div class="title-one text-center wow fadeInUp">
                    <h3 class="text-white">Neden Bizi Tercih Etmelisiniz?</h3>
                    <p class="fs-24 mt-xs text-white">Ticari gayrimenkulde güvenilir ve uzman çözüm ortağınız.</p>
                </div>
                <!-- /.title-one -->

                <div class="card-bg-wrapper wow fadeInUp mt-70 lg-mt-50">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start pe-xxl-5">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_40.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Taşınmaz Sigortası</h5>
                                        <p>İş yeriniz için güvenli ve kapsamlı sigorta çözümleri.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-style-eight -->
                        </div>
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start pe-xxl-2 ps-xxl-2">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_41.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Altarnatif Seçenekleri</h5>
                                        <p>İhtiyacınıza uygun, alternatif seçenek imkanları.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-style-eight -->
                        </div>
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start ps-xxl-5">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_42.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Hızlı ve Kolay Süreç</h5>
                                        <p>Ticari taşınmaz işlemlerinizde hızlı ve pratik çözümler.</p>
                                    </div>
                                </div>
                            </div>
                            <!-- /.card-style-eight -->
                        </div>
                    </div>
                </div>
                <!-- /.card-bg-wrapper -->
            </div>
        </div>
        <!-- /.block-feature-fourteen -->

        <!-- 
        =============================================
            Category Section Two
        ============================================== 
        -->
        <div class="category-section-two mt-170 xl-mt-120">
            <div class="container container-large">
                <div class="position-relative">
                    <div class="title-one text-center text-lg-start mb-60 xl-mb-40 lg-mb-20 wow fadeInUp">
                        <h3>Popüler Bilgiler</h3>
                    </div>
                    <!-- /.title-one -->
       
                    <div class="wrapper flex-wrap d-flex justify-content-center justify-content-md-between align-items-center">
                        <div class="card-style-seven position-relative z-1 rounded-circle overflow-hidden d-flex align-items-center justify-content-center wow fadeInUp" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/b9cb2efd-0228-45ab-97f6-9a9d5e6c1300/public');">
                            <a href="ofiskiralama.php" class="title stretched-link"><h4 class="text-white tran3s">Ofis</h4></a>
                        </div>
                        <div class="card-style-seven position-relative z-1 rounded-circle overflow-hidden d-flex align-items-center justify-content-center wow fadeInUp" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/9f69605e-5f52-4f72-728b-270e6440f400/public');" data-wow-delay="0.1s">
                            <a href="dukkankiralama.php" class="title stretched-link"><h4 class="text-white tran3s">Dükkan</h4></a>
                        </div>
                        <div class="card-style-seven position-relative z-1 rounded-circle overflow-hidden d-flex align-items-center justify-content-center wow fadeInUp" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/1360d14f-b209-4b77-0018-3f73c2d9d600/public');" data-wow-delay="0.2s">
                            <a href="depokiralama.php" class="title stretched-link"><h4 class="text-white tran3s">Depo</h4></a>
                        </div>
                        <div class="card-style-seven position-relative z-1 rounded-circle overflow-hidden d-flex align-items-center justify-content-center wow fadeInUp" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/21452466-c746-407c-b405-d0ff15648e00/public');" data-wow-delay="0.3s">
                            <a href="arsa.php" class="title stretched-link"><h4 class="text-white tran3s">Ticari Arsa</h4></a>
                        </div>
                    </div>
                    
                    <!-- /.section-btn -->
                </div>
            </div>
        </div>
        <!-- /.category-section-two -->

        <!--
        =====================================================
            BLock Feature Four
        =====================================================
        -->
        <div class="block-feature-four mt-170 xl-mt-130 md-mt-40">
            <div class="container">
                <div class="row">
                    <div class="col-lg-6 d-flex order-lg-last">
                        <div class="ps-xxl-5 ms-xl-4 pt-100 xl-pt-80 pb-45 w-100 h-100 wow fadeInRight">
                            <div class="title-one mb-60 lg-mb-40">
                                <h3>İş Yerinizin <span>Değerini<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Hızlıca Öğrenin</h3>
                                <p class="fs-24 color-dark">Ticari taşınmazınızın değerini öğrenin, piyasaya güvenle ve bilgiyle adım atın.</p>
                            </div>
                            <!-- /.title-one -->
                            <form action="#" class="me-xl-4">
                                <input type="email" placeholder="Email Addresinizi Girebilirsiniz.">
                                <button>Gönder</button>
                            </form>
                            <div class="fs-16 mt-10 opacity-75">*Doğru bilgi için lütfen bizimle  <a href="contact.html" class="fst-italic color-dark text-decoration-underline">iletişime geçiniz.</a></div>
                        </div>
                    </div>
                    <div class="col-lg-6 d-flex">
                        <div class="img-gallery position-relative z-1 w-100 h-100 me-lg-5 wow fadeInLeft">
                            <div class="img-bg" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/f5b1522e-305f-4cc5-238a-26732641e700/public');"></div>
                            <div class="card-one">
                                <div class="text text-center z-1">
                                    <h6>Teklifimiz hazır!</h6>
                                    <h3>10.000.000</h3>
                                </div>
                                <img src="images/lazy.svg" data-src="images/assets/screen_12.png" alt="" class="lazy-img w-100">
                            </div>
                        </div>
                        <!-- /.img-gallery -->
                    </div>
                </div>                                             
            </div>
        </div>
        <!-- /.block-feature-four -->
	
		</div>
        <!--
        =====================================================
            Feedback Section Six (Kısaltılmış)
        =====================================================
        -->
        <div class="feedback-section-six bg-pink-two position-relative z-1 mt-170 xl-mt-120 pt-110 xl-pt-80 pb-120 xl-pb-80">
            <div class="container container-large">
                <div class="title-one text-center mb-80 xl-mb-50 md-mb-30">
                    <h3>Müşteri Yorumları</h3>
                    <p class="fs-20 mt-xs">Ticari gayrimenkulde memnuniyetimizi müşterilerimizin deneyimlerinden öğrenin.</p>
                </div>
                <!-- /.title-one -->
            </div>
            <div class="slider-left">
                <div class="feedback-slider-four">
                    <div class="item">
                        <div class="feedback-block-six rounded-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <ul class="rating style-none d-flex">
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                </ul>
                                <img src="images/icon/icon_29.svg" alt="" class="icon">
                            </div>
                            <blockquote>Hayalimizdeki iş yerini bulduk. Çok başarılı bir ekip!</blockquote>
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fs-20 m0">Ayşe Kaya, <span class="fw-normal opacity-50">İstanbul</span></h6>
                                <img src="https://randomuser.me/api/portraits/women/44.jpg" alt="Ayşe Kaya" class="rounded-circle avatar">
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="feedback-block-six rounded-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <ul class="rating style-none d-flex">
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                </ul>
                                <img src="images/icon/icon_29.svg" alt="" class="icon">
                            </div>
                            <blockquote>İş süreçlerimiz hızlandı, tüm işlemler çok kolaylaştı.</blockquote>
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fs-20 m0">Mehmet Özkan, <span class="fw-normal opacity-50">Ankara</span></h6>
                                <img src="https://randomuser.me/api/portraits/men/32.jpg" alt="Mehmet Özkan" class="rounded-circle avatar">
                            </div>
                        </div>
                    </div>
                    <div class="item">
                        <div class="feedback-block-six rounded-4">
                            <div class="d-flex justify-content-between align-items-center">
                                <ul class="rating style-none d-flex">
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                </ul>
                                <img src="images/icon/icon_29.svg" alt="" class="icon">
                            </div>
                            <blockquote>Profesyonel ve güler yüzlü hizmet, her aşamada destek aldık.</blockquote>
                            <div class="d-flex align-items-center justify-content-between">
                                <h6 class="fs-20 m0">Fatma Demir, <span class="fw-normal opacity-50">İzmir</span></h6>
                                <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Fatma Demir" class="rounded-circle avatar">
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        <!-- /.feedback-section-six -->

        <!-- 
        =============================================
            Footer Four
        ============================================== 
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
                                    <li><a href="dashboard/dashboard.php" target="_blank">Üyelik</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <?php if ($isLoggedIn): ?>
                                        <?php if ($userRole === 'admin'): ?>
                                            <li><a href="dashboard/dashboard-admin.php" target="_blank">Admin Panel</a></li>
                                        <?php else: ?>
                                            <li><a href="dashboard/dashboard-user.php" target="_blank">Kullanıcı Paneli</a></li>
                                        <?php endif; ?>
                                    <?php else: ?>
                                        <li><a href="dashboard/dashboard.php" target="_blank">Panel</a></li>
                                    <?php endif; ?>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Yasal</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="contact.php">Şartlar & Koşullar</a></li>
                                    <li><a href="contact.php">Çerez Politikası</a></li>
                                    <li><a href="contact.php">Gizlilik Politikası</a></li>
                                    <li><a href="contact.php">S.S.S</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetlerimiz</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="portfoy.php">Ticari Gayrimenkul</a></li>
                                    <li><a href="portfoy.php">Konut Satışı</a></li>
                                    <li><a href="portfoy.php">Ev Kiralama</a></li>
                                    <li><a href="portfoy.php">Yatırım Danışmanlığı</a></li>
                                    <li><a href="portfoy.php">Villa Satışı</a></li>
                                    <li><a href="portfoy.php">Ofis Kiralama</a></li>
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

    </div> <!-- /.main-page-wrapper -->

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
                </div>
                <!-- /.form-wrapper -->
            </div>
            <!-- /.user-data-form -->
        </div>
    </div>
</div>

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

		<!-- Theme js -->
		<script src="js/theme.js"></script>
	</div> <!-- /.main-page-wrapper -->

    <script>
    // WOW.js güvenli başlatma
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof WOW !== 'undefined') {
            new WOW().init();
        }
        
        // Preloader'ı kapat
        const preloader = document.getElementById('preloader');
        if (preloader) {
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => preloader.remove(), 500);
            }, 1000);
        }

        // Rotating Background Functionality - VIDEO İLE BAŞLIYOR
        let currentBackground = 1; // Video ile başla (backgroundVideo = index 1)
        const backgrounds = ['backgroundImage', 'backgroundVideo'];
        let videoLoaded = false;
        let videoStarted = false;
        
        // Sayfa yüklendiğinde hemen videoyu başlat
        loadVideo();
        
        function loadVideo() {
            const videoIframe = document.getElementById('cloudflareVideo');
            if (videoIframe && !videoLoaded) {
                // İlk açılışta startTime=4s ile başlat
                let videoSrc = 'https://customer-bl0til6mmugr9zxr.cloudflarestream.com/fb71f2bf335038c0bebf75232a7aed8d/iframe?autoplay=1&loop=1&muted=1&controls=0&preload=metadata';
                
                if (!videoStarted) {
                    videoSrc += '&startTime=4s';
                    videoStarted = true;
                }
                
                videoIframe.src = videoSrc;
                videoLoaded = true;
                console.log('Video yüklendi:', videoStarted ? 'ilk kez (4s\'den başlayarak)' : 'kaldığı yerden');
            }
        }
        
        function pauseVideo() {
            const videoIframe = document.getElementById('cloudflareVideo');
            if (videoIframe && videoLoaded) {
                // Video iframe'ine pause mesajı gönder (Cloudflare Stream API)
                try {
                    videoIframe.contentWindow.postMessage('{"method":"pause"}', '*');
                    console.log('Video duraklatıldı');
                } catch (e) {
                    console.log('Video duraklatma komutu gönderildi');
                }
            }
        }
        
        function playVideo() {
            const videoIframe = document.getElementById('cloudflareVideo');
            if (videoIframe && videoLoaded) {
                // Video iframe'ine play mesajı gönder
                try {
                    videoIframe.contentWindow.postMessage('{"method":"play"}', '*');
                    console.log('Video devam ettiriliyor');
                } catch (e) {
                    console.log('Video oynatma komutu gönderildi');
                }
            }
        }
        
        function rotateBackground() {
            // Mevcut arkaplanı gizle
            const currentElement = document.getElementById(backgrounds[currentBackground]);
            if (currentElement) {
                currentElement.classList.remove('active');
                
                // Eğer videodan çıkıyorsak, videoyu duraklat
                if (backgrounds[currentBackground] === 'backgroundVideo') {
                    pauseVideo();
                }
            }
            
            // Sıradaki arkaplanı göster
            currentBackground = (currentBackground + 1) % backgrounds.length;
            const nextElement = document.getElementById(backgrounds[currentBackground]);
            if (nextElement) {
                nextElement.classList.add('active');
                
                // Eğer videoya geçiyorsak
                if (backgrounds[currentBackground] === 'backgroundVideo') {
                    if (!videoLoaded) {
                        loadVideo(); // İlk kez yükle
                    } else {
                        playVideo(); // Kaldığı yerden devam ettir
                    }
                    console.log('Video arkaplanına geçiliyor');
                } else {
                    console.log('Resim arkaplanına geçiliyor');
                }
            }
        }
        
        // Her 8 saniyede bir arkaplanı değiştir
        setInterval(rotateBackground, 8000);
    });

    // Modal fonksiyonları
function switchToRegister() {
    document.querySelector('#fc1').classList.remove('show', 'active');
    document.querySelector('#fc2').classList.add('show', 'active');
    document.querySelector('[data-bs-target="#fc1"]').classList.remove('active');
    document.querySelector('[data-bs-target="#fc2"]').classList.add('active');
}

function switchToLogin() {
    document.querySelector('#fc2').classList.remove('show', 'active');
    document.querySelector('#fc1').classList.add('show', 'active');
    document.querySelector('[data-bs-target="#fc2"]').classList.remove('active');
    document.querySelector('[data-bs-target="#fc1"]').classList.add('active');
}

function showForgotPassword() {
    alert('Şifre sıfırlama linki e-posta adresinize gönderilecektir.');
    // Burada forgot password işlemi yapılabilir
}

// Form validasyonu
document.addEventListener('DOMContentLoaded', function() {
    // Login form
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
        });
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const fullname = this.querySelector('input[name="fullname"]').value;
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const passwordConfirm = this.querySelector('input[name="password_confirm"]').value;
            const terms = this.querySelector('input[name="terms"]').checked;
            
            if (!fullname || !email || !password || !passwordConfirm) {
                e.preventDefault();
                alert('Lütfen tüm zorunlu alanları doldurun.');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır.');
                return false;
            }
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor.');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Şartlar ve koşulları kabul etmelisiniz.');
                return false;
            }
        });
    }
    
    // Telefon formatlaması
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    formattedValue = value;
                } else if (value.length <= 6) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3);
                } else if (value.length <= 8) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
                } else {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 10) + ' ' + value.slice(10, 14);
                }
            }
            
            e.target.value = formattedValue;
        });
    });
});

// Favori fonksiyonları
function toggleFavorite(propertyId) {
    const btn = document.querySelector(`.favorite-btn[data-property-id="${propertyId}"]`);
    const icon = btn.querySelector('i');
    
    // Animasyon ekle
    btn.style.transform = 'scale(0.8)';
    setTimeout(() => {
        btn.style.transform = 'scale(1)';
    }, 150);
    
    // İkonu değiştir
    if (icon.classList.contains('bi-heart')) {
        icon.classList.remove('bi-heart');
        icon.classList.add('bi-heart-fill');
        btn.classList.remove('btn-outline-danger');
        btn.classList.add('btn-danger');
        btn.title = 'Favorilerden Çıkar';
        
        // Başarı mesajı göster
        showFavoriteMessage('İlan favorilere eklendi!', 'success');
    } else {
        icon.classList.remove('bi-heart-fill');
        icon.classList.add('bi-heart');
        btn.classList.remove('btn-danger');
        btn.classList.add('btn-outline-danger');
        btn.title = 'Favorilere Ekle';
        
        // Bilgi mesajı göster
        showFavoriteMessage('İlan favorilerden çıkarıldı!', 'info');
    }
}

function showFavoriteMessage(message, type) {
    // Toast bildirimi oluştur
    const toast = document.createElement('div');
    toast.className = `toast-message toast-${type}`;
    toast.innerHTML = `
        <i class="bi bi-heart-fill me-2"></i>
        ${message}
    `;
    
    // Sayfaya ekle
    document.body.appendChild(toast);
    
    // Animasyonla göster
    setTimeout(() => {
        toast.classList.add('show');
    }, 100);
    
    // 3 saniye sonra kaldır
    setTimeout(() => {
        toast.classList.remove('show');
        setTimeout(() => {
            document.body.removeChild(toast);
        }, 300);
    }, 3000);
}
    </script>

    <!-- Favori butonu stilleri -->
    <style>
        .favorite-btn {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            transition: all 0.3s ease;
            border: 2px solid #dc3545;
        }

        .favorite-btn:hover {
            transform: scale(1.1);
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.3);
        }

        .favorite-btn.btn-danger {
            background-color: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .favorite-btn.btn-outline-danger {
            background-color: transparent;
            color: #dc3545;
        }

        .favorite-btn i {
            font-size: 16px;
        }

        /* Toast mesajları */
        .toast-message {
            position: fixed;
            top: 20px;
            right: 20px;
            padding: 15px 20px;
            border-radius: 8px;
            color: white;
            font-weight: 500;
            z-index: 9999;
            transform: translateX(100%);
            transition: transform 0.3s ease;
            box-shadow: 0 4px 15px rgba(0,0,0,0.2);
        }

        .toast-message.show {
            transform: translateX(0);
        }

        .toast-success {
            background: linear-gradient(135deg, #28a745, #20c997);
        }

        .toast-info {
            background: linear-gradient(135deg, #17a2b8, #6f42c1);
        }

        /* Ana sayfa ilan kartları için ek stil */
        .listing-card-four .property-info {
            background: rgba(0,0,0,0.85) !important;
            backdrop-filter: blur(10px);
            border-radius: 0 0 15px 15px;
            padding: 20px !important;
        }
        
        .listing-card-four .property-info .title {
            color: #ffffff !important;
            font-size: 16px !important;
            font-weight: 600 !important;
            margin-bottom: 8px !important;
        }
        
        .listing-card-four .property-info .address {
            color: #cccccc !important;
            font-size: 13px !important;
            margin-bottom: 8px !important;
        }
        
        .listing-card-four .property-info .price {
            color: #ffd700 !important;
            font-size: 18px !important;
            font-weight: bold !important;
        }
        
        .listing-card-four .btn-outline-danger {
            background: rgba(255,255,255,0.9) !important;
            border-color: #dc3545 !important;
            color: #dc3545 !important;
        }
        
        .listing-card-four .btn-four {
            background: #15B97C !important;
            color: white !important;
            border: none !important;
            border-radius: 8px !important;
            padding: 8px 12px !important;
        }
        
        .listing-card-four .pl-footer {
            background: rgba(255,255,255,0.95) !important;
            padding: 15px 20px !important;
            margin: 0 -20px -20px -20px !important;
            border-top: 1px solid rgba(255,255,255,0.2) !important;
        }
        
        .listing-card-four .pl-footer .feature li {
            color: #666 !important;
            font-size: 13px !important;
        }
        
        .listing-card-four .pl-footer .feature strong {
            color: #333 !important;
            font-weight: 600 !important;
        }
    </style>
    
    <script>
        // Resim yönlendirme düzeltmesi için JavaScript
        document.addEventListener('DOMContentLoaded', function() {
            // Tüm background image'leri düzelt
            const backgroundImageElements = document.querySelectorAll('.listing-card-four[style*="background-image"]');
            backgroundImageElements.forEach(function(element) {
                element.style.imageOrientation = 'from-image';
            });
            
            // Tüm img elementlerini düzelt
            const imageElements = document.querySelectorAll('img');
            imageElements.forEach(function(img) {
                img.style.imageOrientation = 'from-image';
            });
        });
    </script>
</body>

</html>