<?php 
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Sıkça Sorulan Sorular, Emlak SSS, Gayrimenkul FAQ, Gökhan Aydınlı">
    <meta name="description" content="Gayrimenkul alım-satım, kiralama ve yatırım konularında sıkça sorulan sorular ve detaylı cevapları.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:title" content="S.S.S - Gökhan Aydınlı Gayrimenkul">
    <meta property="og:description" content="Gayrimenkul alım-satım, kiralama ve yatırım konularında sıkça sorulan sorular ve detaylı cevapları.">
    <meta property="og:type" content="website">
    <meta property="og:image" content="images/assets/ogg.jpg">
    <meta property="og:url" content="https://gokhanaydnli.com/faq.php">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>S.S.S - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Header düzenlemeleri - Contact.php stilinde */
        .inner-banner-one.inner-banner {
            background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;
        }

        .bg-pink {
            background: #ffffff !important;
        }

        .inner-banner h3 {
            color: #495057 !important;
            font-weight: 700;
        }

        .theme-breadcrumb li {
            color: #6c757d !important;
        }

        .theme-breadcrumb li a {
            color: #6c757d !important;
            text-decoration: none;
        }

        .theme-breadcrumb li a:hover {
            color: #495057 !important;
        }

        /* FAQ Page Stilleri */
        .faq-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            margin: 40px 0;
            border: 1px solid #f0f2f5;
        }

        .faq-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f8f9fa;
        }

        .faq-header h1 {
            color: #2c3e50;
            font-size: 3rem;
            font-weight: 700;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .faq-header p {
            font-size: 1.2rem;
            color: #6c757d;
            max-width: 600px;
            margin: 0 auto;
            line-height: 1.6;
        }

        .faq-search-container {
            margin-bottom: 40px;
            position: relative;
        }

        .faq-search {
            width: 100%;
            padding: 15px 25px 15px 50px;
            border: 2px solid #e9ecef;
            border-radius: 50px;
            font-size: 1.1rem;
            transition: all 0.3s ease;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
        }

        .faq-search:focus {
            outline: none;
            border-color: #667eea;
            box-shadow: 0 6px 25px rgba(102, 126, 234, 0.15);
        }

        .search-icon {
            position: absolute;
            left: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6c757d;
            font-size: 1.2rem;
        }

        .faq-filters {
            display: flex;
            justify-content: center;
            gap: 15px;
            margin-bottom: 40px;
            flex-wrap: wrap;
        }

        .filter-btn {
            padding: 10px 25px;
            border: 2px solid #e9ecef;
            background: #ffffff;
            border-radius: 25px;
            color: #6c757d;
            text-decoration: none;
            transition: all 0.3s ease;
            font-weight: 500;
            font-size: 0.95rem;
        }

        .filter-btn:hover,
        .filter-btn.active {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
            border-color: transparent;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(102, 126, 234, 0.3);
            text-decoration: none;
        }

        .faq-item {
            margin-bottom: 20px;
            border-radius: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
            border: 1px solid #e9ecef;
            background: #ffffff;
        }

        .faq-item:hover {
            box-shadow: 0 8px 30px rgba(0, 0, 0, 0.08);
            transform: translateY(-3px);
        }

        .faq-item .card-header {
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border: none;
            padding: 0;
            border-radius: 15px 15px 0 0;
        }

        .faq-item .card-header .faq-button {
            width: 100%;
            text-align: left;
            background: none;
            border: none;
            padding: 25px 30px;
            font-size: 1.1rem;
            font-weight: 600;
            color: #2c3e50;
            position: relative;
            transition: all 0.3s ease;
            text-decoration: none;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        .faq-item .card-header .faq-button:hover {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: #ffffff;
        }

        .faq-item .card-header .faq-button .faq-icon {
            font-size: 1.3rem;
            transition: transform 0.3s ease;
        }

        .faq-item .card-header .faq-button[aria-expanded="true"] .faq-icon {
            transform: rotate(180deg);
        }

        .faq-item .card-body {
            padding: 30px;
            background: #ffffff;
            border-top: 1px solid #e9ecef;
        }

        .faq-item .card-body p {
            margin: 0;
            line-height: 1.8;
            color: #495057;
            font-size: 1rem;
        }

        .faq-item .card-body ul {
            margin: 15px 0;
            padding-left: 25px;
        }

        .faq-item .card-body ul li {
            margin-bottom: 8px;
            color: #495057;
        }

        .no-results {
            text-align: center;
            padding: 60px 20px;
            color: #6c757d;
            font-size: 1.2rem;
        }

        .no-results i {
            font-size: 3rem;
            margin-bottom: 20px;
            color: #dee2e6;
        }

        @media (max-width: 768px) {
            .faq-container {
                padding: 30px 20px;
                margin: 20px 0;
            }

            .faq-header h1 {
                font-size: 2.2rem;
            }

            .faq-header p {
                font-size: 1rem;
            }

            .faq-filters {
                gap: 10px;
            }

            .filter-btn {
                padding: 8px 20px;
                font-size: 0.9rem;
            }

            .faq-item .card-header .faq-button {
                padding: 20px;
                font-size: 1rem;
            }

            .faq-item .card-body {
                padding: 20px;
            }
        }

        /* Responsive düzenlemeler */
        @media (max-width: 991.98px) {
            .faq-container {
                padding: 30px;
            }
        }

        @media (max-width: 575.98px) {
            .faq-container {
                padding: 20px;
            }
            
            .faq-search {
                padding: 12px 20px 12px 45px;
                font-size: 1rem;
            }
            
            .search-icon {
                left: 15px;
                font-size: 1.1rem;
            }
        }

        /* Contact Info Styles */
        .contact-info-section {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            margin-top: 60px;
            color: #ffffff;
            text-align: center;
        }

        .contact-info-section h3 {
            margin-bottom: 25px;
            font-size: 2rem;
            font-weight: 700;
        }

        .contact-info-section p {
            font-size: 1.1rem;
            margin-bottom: 30px;
            opacity: 0.9;
        }

        .contact-methods {
            display: flex;
            justify-content: center;
            gap: 30px;
            flex-wrap: wrap;
        }

        .contact-method {
            display: flex;
            align-items: center;
            gap: 10px;
            background: rgba(255, 255, 255, 0.1);
            padding: 15px 25px;
            border-radius: 50px;
            text-decoration: none;
            color: #ffffff;
            transition: all 0.3s ease;
            backdrop-filter: blur(10px);
        }

        .contact-method:hover {
            background: rgba(255, 255, 255, 0.2);
            transform: translateY(-3px);
            color: #ffffff;
            text-decoration: none;
        }

        .contact-method i {
            font-size: 1.3rem;
        }

        /* Footer düzenlemeleri - Contact.php stilinde */
        .footer-four .footer-title,
        .footer-four .email,
        .footer-four .footer-nav-link li a:hover {
            color: #6c757d !important;
        }

        .footer-four .social-icon li a {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
            border: 1px solid rgba(108, 117, 125, 0.2);
        }

        .footer-four .social-icon li a:hover {
            background: #6c757d;
            color: #ffffff;
        }

        .footer-four {
            background: #ffffff;
        }

        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
        }
        
        .footer-four .footer-intro p {
            color: #6c757d;
            font-size: 0.95rem;
            line-height: 1.6;
        }
        
        .footer-four .footer-nav-link li a {
            color: #6c757d;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
            padding: 5px 0;
            display: block;
        }
        
        .footer-four .footer-nav-link li a:hover {
            color: #495057 !important;
            padding-left: 8px;
        }
        
        .footer-four .footer-title {
            font-size: 1.1rem;
            font-weight: 600;
            margin-bottom: 20px;
            color: #495057 !important;
        }
        
        .footer-four .email {
            color: #6c757d !important;
            text-decoration: none;
            font-size: 0.95rem;
            transition: all 0.3s ease;
        }
        
        .footer-four .email:hover {
            color: #495057 !important;
        }
        
        .footer-four .social-icon {
            list-style: none;
            padding: 0;
            margin: 0;
            gap: 15px;
        }
        
        .footer-four .social-icon li a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 40px;
            height: 40px;
            border-radius: 50%;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .footer-four .bg-wrapper {
            background: #ffffff;
            padding: 60px 0 40px;
        }
        
        /* Preloader'ı gizle */
        #preloader {
            display: none !important;
            visibility: hidden !important;
        }
        .preloader {
            display: none !important;
            visibility: hidden !important;
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Loading Transition - GİZLENDİ -->
        <div id="preloader" style="display: none !important; visibility: hidden !important;">
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
                                    <li class="nav-item dashboard-menu">
                                        <a class="nav-link active" href="faq.php">S.S.S</a>
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
                <h3 class="mb-35 xl-mb-20 pt-15">Sıkça Sorulan Sorular</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>S.S.S</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- FAQ Section -->
        <div class="fancy-feature-fiftyOne position-relative">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12">
                        <div class="faq-container">
                            <div class="faq-header">
                                <h1>Sıkça Sorulan Sorular</h1>
                                <p>Gayrimenkul alım-satım, kiralama ve yatırım konularında merak ettiğiniz tüm soruların cevaplarını burada bulabilirsiniz.</p>
                            </div>

                            <!-- Arama ve Filtreleme -->
                            <div class="faq-search-container">
                                <i class="fas fa-search search-icon"></i>
                                <input type="text" id="faqSearch" class="faq-search" placeholder="Soru ara...">
                            </div>

                            <div class="faq-filters">
                                <a href="#" class="filter-btn active" data-category="all">Tümü</a>
                                <a href="#" class="filter-btn" data-category="buying">Alım</a>
                                <a href="#" class="filter-btn" data-category="selling">Satım</a>
                                <a href="#" class="filter-btn" data-category="rental">Kiralama</a>
                                <a href="#" class="filter-btn" data-category="investment">Yatırım</a>
                                <a href="#" class="filter-btn" data-category="legal">Hukuki</a>
                                <a href="#" class="filter-btn" data-category="finance">Finansal</a>
                            </div>

                            <!-- FAQ Accordion -->
                            <div class="accordion" id="faqAccordion">
                                
                                <!-- Alım Kategorisi -->
                                <div class="faq-item" data-category="buying">
                                    <div class="card-header" id="heading1">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse1" aria-expanded="false" aria-controls="collapse1">
                                            <span>Ev alırken nelere dikkat etmeliyim?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse1" class="collapse" aria-labelledby="heading1" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Ev alırken dikkat edilmesi gereken temel konular:</p>
                                            <ul>
                                                <li><strong>Konum:</strong> Ulaşım imkanları, sosyal tesisler ve gelecek planları</li>
                                                <li><strong>Yapısal durum:</strong> Binanın yaşı, deprem yönetmeliğine uygunluk</li>
                                                <li><strong>Tapu durumu:</strong> Tapu kayıtları ve mülkiyet durumu</li>
                                                <li><strong>Finansal durum:</strong> Kredi imkanları ve ödeme planları</li>
                                                <li><strong>Çevre analizi:</strong> Komşuluk ve gelecek projeler</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="buying">
                                    <div class="card-header" id="heading2">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse2" aria-expanded="false" aria-controls="collapse2">
                                            <span>Tapu işlemleri nasıl yapılır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse2" class="collapse" aria-labelledby="heading2" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Tapu devir işlemleri için gerekli belgeler ve süreç:</p>
                                            <ul>
                                                <li>Satış sözleşmesi</li>
                                                <li>Nüfus cüzdanı veya kimlik kartı</li>
                                                <li>Vergi numarası</li>
                                                <li>Vekaletname (vekil varsa)</li>
                                                <li>Tapu harç bedeli</li>
                                            </ul>
                                            <p>İşlem tapu müdürlüğünde gerçekleştirilir ve genellikle aynı gün tamamlanır.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Satım Kategorisi -->
                                <div class="faq-item" data-category="selling">
                                    <div class="card-header" id="heading3">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse3" aria-expanded="false" aria-controls="collapse3">
                                            <span>Emlak değerleme nasıl yapılır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse3" class="collapse" aria-labelledby="heading3" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Emlak değerleme sürecinde dikkate alınan faktörler:</p>
                                            <ul>
                                                <li>Konumun avantajları ve dezavantajları</li>
                                                <li>Benzer satışlardan örneklem</li>
                                                <li>Mülkün fiziksel durumu</li>
                                                <li>Piyasa koşulları</li>
                                                <li>Gelecek projeler ve planlar</li>
                                            </ul>
                                            <p>Profesyonel değerleme raporu almak satış sürecini hızlandırır.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="selling">
                                    <div class="card-header" id="heading4">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse4" aria-expanded="false" aria-controls="collapse4">
                                            <span>Satış komisyonu ne kadar?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse4" class="collapse" aria-labelledby="heading4" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Emlak komisyon oranları genellikle %2-4 arasında değişir. Gökhan Aydınlı Gayrimenkul olarak rekabetçi komisyon oranları sunuyoruz. Detaylı bilgi için iletişime geçebilirsiniz.</p>
                                            <p>Komisyon kapsamında:</p>
                                            <ul>
                                                <li>Pazar analizi ve değerleme</li>
                                                <li>Profesyonel fotoğraf çekimi</li>
                                                <li>Reklamların yayınlanması</li>
                                                <li>Müşteri bulma ve eşleştirme</li>
                                                <li>Satış sürecinin yönetilmesi</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Kiralama Kategorisi -->
                                <div class="faq-item" data-category="rental">
                                    <div class="card-header" id="heading5">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse5" aria-expanded="false" aria-controls="collapse5">
                                            <span>Kira sözleşmesi nasıl hazırlanır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse5" class="collapse" aria-labelledby="heading5" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Kira sözleşmesinde bulunması gereken temel maddeler:</p>
                                            <ul>
                                                <li>Tarafların kimlik bilgileri</li>
                                                <li>Kiralanan mülkün adresi ve özellikleri</li>
                                                <li>Kira bedeli ve ödeme koşulları</li>
                                                <li>Depozito miktarı</li>
                                                <li>Sözleşme süresi</li>
                                                <li>Tarafların yükümlülükleri</li>
                                                <li>Fesih koşulları</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="rental">
                                    <div class="card-header" id="heading6">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse6" aria-expanded="false" aria-controls="collapse6">
                                            <span>Kiracı haklarım nelerdir?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse6" class="collapse" aria-labelledby="heading6" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>6098 sayılı Türk Borçlar Kanunu'na göre kiracı hakları:</p>
                                            <ul>
                                                <li>Mülkü sözleşmeye uygun şekilde kullanma hakkı</li>
                                                <li>Gerekli onarımları talep etme hakkı</li>
                                                <li>Sözleşme süresince oturma güvencesi</li>
                                                <li>Depozito iadesi hakkı</li>
                                                <li>Makul zamanda kiralama hakkı</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <!-- Yatırım Kategorisi -->
                                <div class="faq-item" data-category="investment">
                                    <div class="card-header" id="heading7">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse7" aria-expanded="false" aria-controls="collapse7">
                                            <span>Gayrimenkul yatırımı nasıl yapılır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse7" class="collapse" aria-labelledby="heading7" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Başarılı gayrimenkul yatırımı için temel stratejiler:</p>
                                            <ul>
                                                <li><strong>Pazar analizi:</strong> Bölgesel fiyat trendlerini takip edin</li>
                                                <li><strong>Konum seçimi:</strong> Gelişen bölgeleri tercih edin</li>
                                                <li><strong>Finansal planlama:</strong> Bütçenizi doğru hesaplayın</li>
                                                <li><strong>Kira geliri:</strong> Aylık getiri oranını hesaplayın</li>
                                                <li><strong>Uzun vadeli perspektif:</strong> En az 5-10 yıllık plan yapın</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="investment">
                                    <div class="card-header" id="heading8">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse8" aria-expanded="false" aria-controls="collapse8">
                                            <span>Hangi bölgeler yatırım için uygun?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse8" class="collapse" aria-labelledby="heading8" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>İstanbul'da yatırım açısından öne çıkan bölgeler:</p>
                                            <ul>
                                                <li><strong>Beylikdüzü:</strong> Yeni gelişen, yüksek potansiyelli</li>
                                                <li><strong>Başakşehir:</strong> Modern yaşam alanları</li>
                                                <li><strong>Büyükçekmece:</strong> Doğa ile iç içe projeler</li>
                                                <li><strong>Esenyurt:</strong> Uygun fiyatlar, gelişim potansiyeli</li>
                                                <li><strong>Küçükçekmece:</strong> Ulaşım avantajları</li>
                                            </ul>
                                            <p>Her bölgenin kendine özgü avantajları vardır. Detaylı analiz için uzmanlarımızdan bilgi alabilirsiniz.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Hukuki Kategorisi -->
                                <div class="faq-item" data-category="legal">
                                    <div class="card-header" id="heading9">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse9" aria-expanded="false" aria-controls="collapse9">
                                            <span>Tapuda durum sorgusu nasıl yapılır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse9" class="collapse" aria-labelledby="heading9" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Tapu durum sorgusu için gerekli bilgiler:</p>
                                            <ul>
                                                <li>İl, ilçe, mahalle bilgisi</li>
                                                <li>Ada ve parsel numarası</li>
                                                <li>Kimlik bilgisi (malik iseniz)</li>
                                            </ul>
                                            <p>Sorgu işlemi tapu müdürlüğünden veya e-Devlet üzerinden yapılabilir. Mülkün üzerindeki haciz, ipotek gibi durumları öğrenebilirsiniz.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="legal">
                                    <div class="card-header" id="heading10">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse10" aria-expanded="false" aria-controls="collapse10">
                                            <span>Gayrimenkul davalarında süreç nasıl işler?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse10" class="collapse" aria-labelledby="heading10" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Gayrimenkul davaları genellikle şu konularda açılır:</p>
                                            <ul>
                                                <li>Tahliye davaları</li>
                                                <li>Kira artış davaları</li>
                                                <li>Mülkiyet davaları</li>
                                                <li>İfraz ve tevhid davaları</li>
                                                <li>Kamulaştırma davaları</li>
                                            </ul>
                                            <p>Hukuki süreçler uzun sürebilir. Profesyonel hukuki destek almanız önerilir.</p>
                                        </div>
                                    </div>
                                </div>

                                <!-- Finansal Kategorisi -->
                                <div class="faq-item" data-category="finance">
                                    <div class="card-header" id="heading11">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse11" aria-expanded="false" aria-controls="collapse11">
                                            <span>Konut kredisi nasıl alınır?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse11" class="collapse" aria-labelledby="heading11" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Konut kredisi başvurusu için gerekli belgeler:</p>
                                            <ul>
                                                <li>Kimlik fotokopisi</li>
                                                <li>Gelir belgesi (maaş bordrosu/SGK belgesi)</li>
                                                <li>Kredi notu raporu</li>
                                                <li>Alınacak konutun değerleme raporu</li>
                                                <li>Sigorta poliçesi</li>
                                            </ul>
                                            <p>Kredi onayı sonrası tapu işlemleri yapılarak mülk bankaya rehin olarak verilir.</p>
                                        </div>
                                    </div>
                                </div>

                                <div class="faq-item" data-category="finance">
                                    <div class="card-header" id="heading12">
                                        <button class="faq-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapse12" aria-expanded="false" aria-controls="collapse12">
                                            <span>Emlak vergileri nelerdir?</span>
                                            <i class="fas fa-chevron-down faq-icon"></i>
                                        </button>
                                    </div>
                                    <div id="collapse12" class="collapse" aria-labelledby="heading12" data-bs-parent="#faqAccordion">
                                        <div class="card-body">
                                            <p>Gayrimenkul ile ilgili vergi türleri:</p>
                                            <ul>
                                                <li><strong>Emlak Vergisi:</strong> Yıllık olarak belediyeye ödenir</li>
                                                <li><strong>Tapu Harcı:</strong> Alım-satım sırasında %4 oranında</li>
                                                <li><strong>KDV:</strong> Yeni konutlarda %20, ticari gayrimenkulde %20</li>
                                                <li><strong>Gelir Vergisi:</strong> Kira geliri üzerinden</li>
                                                <li><strong>Emlak Değer Artış Kazancı:</strong> Satış karı üzerinden</li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                            </div>

                            <!-- Sonuç bulunamadı mesajı -->
                            <div class="no-results" id="noResults" style="display: none;">
                                <i class="fas fa-search"></i>
                                <h3>Sonuç bulunamadı</h3>
                                <p>Aradığınız kriterlere uygun soru bulunamadı. Lütfen farklı anahtar kelimeler deneyin.</p>
                            </div>

                            <!-- İletişim Bilgisi -->
                            <div class="contact-info-section">
                                <h3>Sorunuz mu var?</h3>
                                <p>Aradığınız cevabı bulamadıysanız, bizimle doğrudan iletişime geçebilirsiniz.</p>
                                <div class="contact-methods">
                                    <a href="tel:+905051234567" class="contact-method">
                                        <i class="fas fa-phone"></i>
                                        <span>0505 123 45 67</span>
                                    </a>
                                    <a href="mailto:info@gokhanaydnli.com" class="contact-method">
                                        <i class="fas fa-envelope"></i>
                                        <span>info@gokhanaydnli.com</span>
                                    </a>
                                    <a href="https://wa.me/905051234567" class="contact-method" target="_blank">
                                        <i class="fab fa-whatsapp"></i>
                                        <span>WhatsApp</span>
                                    </a>
                                </div>
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
                                        <img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" style="height: 60px;">
                                    </a>
                                </div>
                                <p class="mb-30 xs-mb-20">Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
                                <a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="https://wa.me/905302037083" target="_blank"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="https://www.instagram.com/gokhanaydinli?igsh=ejRhZmd0eWlpY3c1" target="_blank"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="https://www.linkedin.com/in/g%C3%B6khan-ayd%C4%B1nl%C4%B1-8a186271/?originalSubdomain=tr" target="_blank"><i class="fa-brands fa-linkedin"></i></a></li>
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
                    <p class="m0 text-center fs-16">Copyright @2025 Gökhan Aydınlı Gayrimenkul.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>
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
                                                <label>E-posta</label>
                                                <input type="email" name="email" placeholder="ornek@email.com" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-20">
                                                <label>Şifre</label>
                                                <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                <div>
                                                    <input type="checkbox" id="remember" name="remember">
                                                    <label for="remember">Beni hatırla</label>
                                                </div>
                                                <a href="#" onclick="showForgotPassword()">Şifremi unuttum?</a>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-two w-100 text-uppercase tran3s d-block mt-20">GİRİŞ YAP</button>
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
                                                <input type="text" name="fullname" placeholder="Adınız ve soyadınız" required>
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
                                                <label>Şifre*</label>
                                                <input type="password" name="password" placeholder="En az 6 karakter" required>
                                                <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-25">
                                                <label>Şifre Tekrar*</label>
                                                <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                                <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_60.svg" alt=""></span></span>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                <div>
                                                    <input type="checkbox" name="terms" id="terms" required>
                                                    <label for="terms">Şartlar ve koşulları kabul ediyorum</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-two w-100 text-uppercase tran3s d-block mt-20">KAYIT OL</button>
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

    <!-- jQuery -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery/3.6.0/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <!-- Diğer scriptler -->
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/wow/wow.min.js"></script>
    <script src="vendor/slick/slick.min.js"></script>
    <script src="vendor/fancybox/fancybox.umd.js"></script>
    <script src="vendor/jquery.lazy.min.js"></script>
    <script src="vendor/jquery.counterup.min.js"></script>
    <script src="vendor/waypoints.min.js"></script>
    <script src="vendor/nice-select/jquery.nice-select.min.js"></script>
    <script src="js/theme.js"></script>

    <!-- FAQ Script -->
    <script>
        $(document).ready(function() {
            // Arama fonksiyonu
            $('#faqSearch').on('keyup', function() {
                var searchText = $(this).val().toLowerCase();
                var visibleItems = 0;

                $('.faq-item').each(function() {
                    var questionText = $(this).find('.faq-button span').text().toLowerCase();
                    var answerText = $(this).find('.card-body').text().toLowerCase();
                    
                    if (questionText.includes(searchText) || answerText.includes(searchText)) {
                        $(this).show();
                        visibleItems++;
                    } else {
                        $(this).hide();
                    }
                });

                // Sonuç bulunamadı mesajı
                if (visibleItems === 0 && searchText !== '') {
                    $('#noResults').show();
                } else {
                    $('#noResults').hide();
                }
            });

            // Kategori filtreleme
            $('.filter-btn').on('click', function(e) {
                e.preventDefault();
                
                var category = $(this).data('category');
                
                // Aktif buton
                $('.filter-btn').removeClass('active');
                $(this).addClass('active');
                
                // Filtreleme
                if (category === 'all') {
                    $('.faq-item').show();
                } else {
                    $('.faq-item').each(function() {
                        if ($(this).data('category') === category) {
                            $(this).show();
                        } else {
                            $(this).hide();
                        }
                    });
                }
                
                // Arama kutusunu temizle
                $('#faqSearch').val('');
                $('#noResults').hide();
            });
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
        }
    </script>

</body>
</html>
