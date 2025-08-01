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

        /* Footer düzenlemeleri */
        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
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
                            <a href="index.php" class="d-block">
                                <img src="images/logo/logo_01.svg" alt="" width="129">
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
                                            <li><a class="dropdown-item" href="logout.php">Çıkış</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li><a href="login.php" class="btn-one"><i class="fa-solid fa-user"></i> <span>Giriş</span></a></li>
                                <?php endif; ?>
                            </ul>
                        </div>
                        <nav class="navbar navbar-expand-lg p0 ms-lg-5 ms-3 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse ms-xl-5" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="d-block d-lg-none">
                                        <div class="logo">
                                            <a href="index.php" class="d-block">
                                                <img src="images/logo/logo_01.svg" alt="" width="100">
                                            </a>
                                        </div>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item dropdown">
                                        <a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
                                            Listelerimiz
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a href="listing_01.php" class="dropdown-item"><span>Satılık</span></a></li>
                                            <li><a href="listing_02.php" class="dropdown-item"><span>Kiralık</span></a></li>
                                            <li><a href="listing_03.php" class="dropdown-item"><span>Günlük Kiralık</span></a></li>
                                            <li><a href="listing_04.php" class="dropdown-item"><span>Ticari</span></a></li>
                                            <li><a href="listing_05.php" class="dropdown-item"><span>Arsa</span></a></li>
                                        </ul>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="hesaplama-araclari.php">Hesaplama Araçları</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="contact.php">İletişim</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="blog.php">Blog</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link active" href="faq.php">S.S.S</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                    </div>
                </div>
            </div>
        </header>

        <!-- FAQ Section -->
        <div class="fancy-feature-fiftyOne position-relative mt-200">
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
        <div class="footer-style-four space-fix-one theme-basic-footer">
            <div class="container">
                <div class="inner-wrapper">
                    <div class="subscribe-area">
                        <div class="row align-items-center">
                            <div class="col-md-6">
                                <div class="title-style-four">
                                    <h4 class="main-title fw-500 text-white">E-Bülten Aboneliği</h4>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="subscribe-form">
                                    <form action="#" class="d-flex align-items-center">
                                        <input type="email" placeholder="Email adresiniz..">
                                        <button type="submit">Abone Ol</button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bottom-footer">
                        <div class="d-lg-flex align-items-center justify-content-between">
                            <ul class="order-lg-1 pb-15 d-flex justify-content-center footer-nav style-none">
                                <li><a href="index.php">Ana Sayfa</a></li>
                                <li><a href="listing_01.php">İlanlar</a></li>
                                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                <li><a href="blog.php">Blog</a></li>
                                <li><a href="contact.php">İletişim</a></li>
                                <li><a href="faq.php">S.S.S</a></li>
                            </ul>
                            <p class="copyright text-center order-lg-0 pb-15">© 2024 Gökhan Aydınlı Gayrimenkul</p>
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
    </script>

</body>
</html>
