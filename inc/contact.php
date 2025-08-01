<?php 
session_start();

// Mail gönderme fonksiyonu - DÜZELTİLMİŞ
function sendContactEmail($name, $email, $subject, $message) {
    try {
        $to = 'info@gokhanaydinli.com';
        $headers = "From: root@gokhanaydinli.com\r\n" .
                   "Reply-To: $email\r\n" .
                   "Return-Path: root@gokhanaydinli.com\r\n" .
                   'Content-Type: text/html; charset=UTF-8' . "\r\n" .
                   'X-Mailer: PHP/' . phpversion();
        
        $mailBody = "
        <h2>İletişim Formu Mesajı</h2>
        <p><strong>Ad Soyad:</strong> $name</p>
        <p><strong>E-posta:</strong> $email</p>
        <p><strong>Konu:</strong> $subject</p>
        <p><strong>Mesaj:</strong><br>$message</p>
        <p><strong>Tarih:</strong> " . date('Y-m-d H:i:s') . "</p>
        ";
        
        return mail($to, "İletişim: $subject", $mailBody, $headers);
    } catch (Exception $e) {
        error_log("Mail sending failed: " . $e->getMessage());
        return false;
    }
}

// Form işleme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Doğrulama
    $errors = [];
    if (empty($name)) $errors[] = "Ad-Soyad boş olamaz";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin";
    if (empty($subject)) $errors[] = "Konu boş olamaz";
    if (empty($message)) $errors[] = "Mesaj boş olamaz";
    
    if (empty($errors)) {
        // Yerel kayıt
        $log_entry = date('Y-m-d H:i:s') . " | $name | $email | $subject | $message\n";
        file_put_contents('messages.txt', $log_entry, FILE_APPEND | LOCK_EX);
        
        // Mail gönder - DÜZELTİLMİŞ KISIM
        $mailSent = sendContactEmail($name, $email, $subject, $message);
        
        if ($mailSent) {
            $_SESSION['success'] = "✅ Mesajınız başarıyla gönderildi! En kısa sürede size dönüş yapacağız.";
        } else {
            $_SESSION['success'] = "✅ Mesajınız kaydedildi! Mail gönderiminde sorun oldu ama mesajınız bize ulaştı.";
        }
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

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
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Gayrimenkul Blog, İstanbul Emlak, Gökhan Aydınlı">
    <meta name="description" content="Gayrimenkul uzmanı Gökhan Aydınlı'nın blog yazıları">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Gökhan Aydınlı - Gayrimenkul Blog">
    <meta name='og:image' content='images/assets/blog-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>İletişim - Gökhan Aydınlı</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* İletişim sayfası modern stilleri */
        .inner-banner-one.inner-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
            color: white;
        }

        .bg-pink {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%) !important;
            padding: 80px 0;
        }

        /* Modern Form Stilleri */
        .contact-form-modern {
            background: white;
            padding: 40px;
            border-radius: 20px;
            box-shadow: 0 15px 50px rgba(0, 0, 0, 0.1);
            border: 1px solid #e9ecef;
        }

        .form-header {
            border-bottom: 2px solid #f8f9fa;
            padding-bottom: 20px;
            margin-bottom: 30px;
        }

        .form-title {
            color: #495057;
            font-weight: 700;
            margin-bottom: 8px;
            font-size: 1.8rem;
        }

        .form-subtitle {
            color: #6c757d;
            margin-bottom: 0;
            font-size: 1rem;
        }

        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
            font-size: 0.95rem;
        }

        .form-control-modern {
            border: 2px solid #e9ecef;
            border-radius: 12px;
            padding: 14px 16px;
            font-size: 16px;
            transition: all 0.3s ease;
            background: #fafafa;
        }

        .form-control-modern:focus {
            border-color: #667eea;
            box-shadow: 0 0 0 0.2rem rgba(102, 126, 234, 0.15);
            background: white;
            transform: translateY(-1px);
        }

        .form-control-modern:hover {
            border-color: #dee2e6;
            background: white;
        }

        .btn-modern {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 12px;
            padding: 16px 32px;
            font-size: 16px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 1px;
            transition: all 0.3s ease;
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
        }

        .btn-modern:hover {
            transform: translateY(-3px);
            box-shadow: 0 12px 35px rgba(102, 126, 234, 0.4);
            background: linear-gradient(135deg, #764ba2 0%, #667eea 100%);
        }

        /* Animasyonlar */
        .contact-form-modern {
            animation: fadeInUp 0.6s ease-out;
        }

        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* İletişim blokları */
        .block {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.1);
            transition: all 0.3s ease;
            border: 1px solid #f1f1f1;
        }

        .block:hover {
            transform: translateY(-5px);
            box-shadow: 0 20px 50px rgba(0, 0, 0, 0.15);
        }

        .block .icon {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            width: 60px;
            height: 60px;
            margin-right: 20px;
            box-shadow: 0 8px 20px rgba(102, 126, 234, 0.3);
        }

        .block .text p {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }

        .block .text a {
            color: #667eea;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .block .text a:hover {
            color: #764ba2;
            text-decoration: underline;
        }

        /* Modal özel stilleri */
        .user-data-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .modal-header {
            border-bottom: none;
            position: relative;
            justify-content: center;
        }

        .modal-title {
            font-size: 24px;
            font-weight: 700;
            color: #1f2937;
        }

        .close {
            position: absolute;
            top: 15px;
            right: 15px;
            color: #6b7280;
            font-size: 18px;
        }

        .close:hover {
            color: #2563eb;
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-link {
            border: none;
            border-radius: 0;
            color: #6b7280;
            font-weight: 600;
            padding: 15px 25px;
            transition: all 0.3s ease;
        }

        .nav-link.active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
        }

        .tab-content {
            border: none;
        }

        .input-group-meta {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group-meta label {
            position: absolute;
            top: -10px;
            left: 15px;
            font-size: 12px;
            color: #2563eb;
            background: #fff;
            padding: 0 5px;
        }

        .input-group-meta input {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            color: #1f2937;
            transition: all 0.3s ease;
            width: 100%;
        }

        .input-group-meta input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 5px rgba(37, 99, 235, 0.3);
            outline: none;
        }

        .agreement-checkbox {
            margin-bottom: 20px;
        }

        .agreement-checkbox input {
            margin-right: 10px;
        }

        .btn-two {
            background: #2563eb;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
        }

        .btn-two:hover {
            background: #1d4ed8;
        }

        .social-use-btn {
            background: #f3f4f6;
            color: #1f2937;
            padding: 12px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 10px;
        }

        .social-use-btn:hover {
            background: #e5e7eb;
        }

        .placeholder_icon {
            position: absolute;
            top: 50%;
            right: 15px;
            transform: translateY(-50%);
            pointer-events: none;
        }

        .passVicon {
            width: 18px;
            height: 18px;
        }

        /* İletişim sayfası özel stilleri */
        .inner-banner-one.inner-banner {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        }

        .bg-pink {
            background: #ffffff !important;
        }

        .form-style-one h3 {
            color: #6c757d !important;
            font-weight: 700;
        }

        .form-style-one .input-group-meta label {
            color: #6c757d !important;
        }

        .form-style-one .input-group-meta input:focus,
        .form-style-one .input-group-meta textarea:focus {
            border-color: #6c757d !important;
            box-shadow: 0 0 5px rgba(108, 117, 125, 0.15) !important;
        }

        .form-style-one .btn-nine {
            background: #6c757d !important;
            color: #fff !important;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s;
        }

        .form-style-one .btn-nine:hover {
            background: #495057 !important;
            color: #fff !important;
        }

        .address-banner .icon {
            background: #f8f9fa !important;
            border: 2px solid #6c757d !important;
        }

        .address-banner .text a {
            color: #6c757d !important;
            font-weight: 600;
        }

        .address-banner .text a:hover {
            color: #495057 !important;
        }

        .footer-four .footer-title,
        .footer-four .email,
        .footer-four .footer-nav-link li a:hover {
            color: #6c757d !important;
        }

        .footer-four .social-icon li a {
            background: #f8f9fa !important;
            color: #6c757d !important;
            border-radius: 50%;
            transition: all 0.3s;
        }

        .footer-four .social-icon li a:hover {
            background: #6c757d !important;
            color: #fff !important;
        }

        .footer-four {
            background: #ffffff !important;
        }

        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #6c757d;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #495057;
            transform: translateY(-2px);
        }

        /* Success/Error messages */
        .alert {
            border-radius: 12px;
            padding: 20px 25px;
            margin-bottom: 25px;
            font-size: 16px;
            font-weight: 500;
            border: 1px solid transparent;
            animation: slideInDown 0.5s ease;
        }

        .alert-success {
            background-color: #d4edda;
            border-color: #c3e6cb;
            color: #155724;
            box-shadow: 0 4px 15px rgba(21, 185, 124, 0.2);
        }

        .alert-danger {
            background-color: #f8d7da;
            border-color: #f5c6cb;
            color: #721c24;
            box-shadow: 0 4px 15px rgba(220, 53, 69, 0.2);
        }

        @keyframes slideInDown {
            from {
                opacity: 0;
                transform: translateY(-20px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 768px) {
            .inner-banner-one.inner-banner {
                padding-top: 90px !important;
                padding-bottom: 60px !important;
            }
            .form-style-one {
                padding: 20px !important;
            }
        }
    </style>
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
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="porfoy.html">Portföy</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item dropdown">
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
                <h3 class="mb-35 xl-mb-20 pt-15">İletişim</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>İletişim</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Contact Us -->
        <div class="contact-us border-top mt-130 xl-mt-100 pt-80 lg-pt-60">
            <div class="container">
                <div class="row">
                    <div class="col-xxl-9 col-xl-8 col-lg-10 m-auto">
                        <div class="title-one text-center wow fadeInUp">
                            <h3>Sorularınız mı var? Bize mesaj gönderebilirsiniz.</h3>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="address-banner wow fadeInUp mt-60 lg-mt-40">
                <div class="container">
                    <div class="d-flex flex-wrap justify-content-center justify-content-lg-between">
                        <div class="block position-relative z-1 mt-25">
                            <div class="d-xl-flex align-items-center">
                                <div class="icon rounded-circle d-flex align-items-center justify-content-center"><img src="images/lazy.svg" data-src="images/icon/icon_39.svg" alt="" class="lazy-img"></div>
                                <div class="text">
                                    <p class="fs-22">Size yardımcı olmaktan mutluluk duyarız.</p>
                                    <a href="mailto:info@gokhanaydinli.com" class="tran3s">info@gokhanaydinli.com</a>
                                </div>
                            </div>
                        </div>
                        <div class="block position-relative skew-line z-1 mt-25">
                            <div class="d-xl-flex align-items-center">
                                <div class="icon rounded-circle d-flex align-items-center justify-content-center"><img src="images/lazy.svg" data-src="images/icon/icon_39.svg" alt="" class="lazy-img"></div>
                                <div class="text">
                                    <p class="fs-22">Telefon numaramız</p>
                                    <a href="tel:+902128016058" class="tran3s">+90 (212) 801 60 58</a>
                                    <br>
                                    <a href="tel:+905302037083" class="tran3s">+90 (530) 203 70 83</a>
                                </div>
                            </div>
                        </div>
                        <div class="block position-relative z-1 mt-25">
                            <div class="d-xl-flex align-items-center">
                                <div class="icon rounded-circle d-flex align-items-center justify-content-center"><img src="images/lazy.svg" data-src="images/icon/icon_39.svg" alt="" class="lazy-img"></div>
                                <div class="text">
                                    <p class="fs-22">Çalışma Saatleri</p>
                                    <a href="#" class="tran3s">Pzt-Cum: 09:00-19:00, Cmt-Paz: 09:00-14:00</a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="bg-pink mt-150 xl-mt-120 md-mt-80">
                <div class="row">
                    <div class="col-xl-7 col-lg-6">
                        <div class="form-style-one wow fadeInUp">
                            <!-- Başarı/Hata Mesajları -->
                            <?php if (isset($_SESSION['success'])): ?>
                                <div class="alert alert-success">
                                    <?php echo htmlspecialchars($_SESSION['success']); ?>
                                </div>
                                <?php unset($_SESSION['success']); ?>
                            <?php endif; ?>
                            
                            <?php if (isset($_SESSION['error'])): ?>
                                <div class="alert alert-danger">
                                    <?php echo $_SESSION['error']; ?>
                                </div>
                                <?php unset($_SESSION['error']); ?>
                            <?php endif; ?>
                            
                            <form method="POST" class="contact-form-modern">
                                <div class="form-header text-center mb-4">
                                    <h3 class="form-title">Mesaj Gönder</h3>
                                    <p class="form-subtitle">Size yardımcı olmaktan mutluluk duyarız</p>
                                </div>
                                
                                <div class="row">
                                    <div class="col-md-6 mb-3">
                                        <label for="name" class="form-label">Ad Soyad <span class="text-danger">*</span></label>
                                        <input type="text" name="name" id="name" class="form-control form-control-modern" required placeholder="Adınız ve soyadınız">
                                    </div>
                                    <div class="col-md-6 mb-3">
                                        <label for="email" class="form-label">E-posta <span class="text-danger">*</span></label>
                                        <input type="email" name="email" id="email" class="form-control form-control-modern" required placeholder="ornek@email.com">
                                    </div>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="subject" class="form-label">Konu <span class="text-danger">*</span></label>
                                    <input type="text" name="subject" id="subject" class="form-control form-control-modern" required placeholder="Mesajınızın konusu">
                                </div>
                                
                                <div class="mb-4">
                                    <label for="message" class="form-label">Mesajınız <span class="text-danger">*</span></label>
                                    <textarea name="message" id="message" class="form-control form-control-modern" required rows="6" placeholder="Lütfen mesajınızı buraya yazın..."></textarea>
                                </div>
                                
                                <button type="submit" class="btn btn-primary btn-modern w-100">
                                    <i class="fas fa-paper-plane me-2"></i>MESAJ GÖNDER
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="col-xl-5 col-lg-6 d-flex order-lg-first">
                        <div class="contact-map-banner w-100">
                            <div class="gmap_canvas h-100 w-100">
                                <iframe class="gmap_iframe h-100 w-100" src="https://www.google.com/maps/embed?pb=!1m18!1m12!1m3!1d3008.8746!2d28.9147!3d41.0438!2m3!1f0!2f0!3f0!3m2!1i1024!2i768!4f13.1!3m3!1m2!1s0x14cab0a4c1c7a6f3%3A0x1e7b4a4c2d8e5f6a!2sMaltepe%20Mah.%2C%20Eski%20%C3%87%C4%B1rp%C4%B1c%C4%B1%20Yolu%20Cd.%2C%2034140%20Bak%C4%B1rk%C3%B6y%2F%C4%B0stanbul!5e0!3m2!1str!2str!4v1620000000000!5m2!1str!2str" loading="lazy" referrerpolicy="no-referrer-when-downgrade"></iframe>
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
                                    <li><a href="porfoy.html">Ticari Gayrimenkul</a></li>
                                    <li><a href="porfoy.html">Konut Satışı</a></li>
                                    <li><a href="porfoy.html">Ev Kiralama</a></li>
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
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll to top button -->
        <button class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
            ↑
        </button>

        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" crossorigin="anonymous"></script>
        <script src="vendor/slick/slick.min.js"></script>
        <script src="vendor/fancybox/fancybox.umd.js"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.11/jquery.lazy.min.js" crossorigin="anonymous"></script>
        <script src="vendor/jquery.counterup.min.js"></script>
        <script src="vendor/jquery.waypoints.min.js"></script>
        <script src="vendor/nice-select/jquery.nice-select.min.js"></script>
        <script src="vendor/validator.js"></script>
        <script src="vendor/isotope.pkgd.min.js"></script>
        <script src="js/theme.js"></script>

        <script>
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
        }

        function loginWithGoogle() {
            alert('Google ile giriş özelliği yakında aktif olacak.');
        }

        function loginWithFacebook() {
            alert('Facebook ile giriş özelliği yakında aktif olacak.');
        }

        // Form hover efektleri
        document.addEventListener('DOMContentLoaded', function() {
            const inputs = document.querySelectorAll('input, textarea');
            inputs.forEach(input => {
                input.addEventListener('focus', function() {
                    this.style.borderColor = '#6c757d';
                    this.style.boxShadow = '0 0 10px rgba(108, 117, 125, 0.2)';
                });
                
                input.addEventListener('blur', function() {
                    this.style.borderColor = '#e9ecef';
                    this.style.boxShadow = 'none';
                });
            });

            // Submit button hover effect
            const submitBtn = document.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.addEventListener('mouseenter', function() {
                    this.style.background = '#495057';
                    this.style.transform = 'translateY(-2px)';
                });
                
                submitBtn.addEventListener('mouseleave', function() {
                    this.style.background = '#6c757d';
                    this.style.transform = 'translateY(0)';
                });
            }
        });
        </script>
    </div>
</body>
</html>