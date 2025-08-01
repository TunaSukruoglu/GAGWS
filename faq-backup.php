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
    <meta property="og:type" content="website">
    <meta property="og:title" content="S.S.S - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/faq-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .faq-header .faq-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
            animation: question-bounce 3s infinite;
        }

        @keyframes question-bounce {
            0%, 100% {
                transform: rotate(0deg) scale(1);
            }
            25% {
                transform: rotate(-5deg) scale(1.05);
            }
            75% {
                transform: rotate(5deg) scale(1.05);
            }
        }

        .faq-header p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .faq-search {
            max-width: 500px;
            margin: 0 auto 30px auto;
            position: relative;
        }

        .faq-search input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .faq-search input:focus {
            border-color: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .faq-search i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 18px;
        }

        .faq-categories {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
        }

        .category-btn {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .category-btn:hover,
        .category-btn.active {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .faq-section {
            margin-bottom: 40px;
        }

        .faq-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }

        .faq-section h2 i {
            margin-right: 15px;
            color: #10b981;
            font-size: 1.5rem;
        }

        .faq-item {
            background: #fafbfc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: #f9fafb;
        }

        .faq-question h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            flex: 1;
            line-height: 1.4;
        }

        .faq-toggle {
            font-size: 1.2rem;
            color: #10b981;
            transition: transform 0.3s ease;
            margin-left: 15px;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 20px;
        }

        .faq-answer p {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin: 0;
        }

        .faq-answer ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .faq-answer li {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .highlight-tip {
            background: linear-gradient(135deg, #10b98110 0%, #05966910 100%);
            border: 1px solid #10b98130;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .highlight-tip h5 {
            color: #059669;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .warning-tip {
            background: linear-gradient(135deg, #f5940510 0%, #d9770410 100%);
            border: 1px solid #f5940530;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .warning-tip h5 {
            color: #d97706;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .contact-cta {
            background: #10b981;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }

        .contact-cta h3 {
            color: white;
            margin-bottom: 15px;
        }

        .contact-cta .btn {
            background: white;
            color: #10b981;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .contact-cta .btn:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: #10b981;
        }

        .stats-row {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            text-align: center;
        }

        .stat-item {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .quick-nav {
            position: sticky;
            top: 100px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .quick-nav h4 {
            color: #10b981;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .quick-nav ul {
            list-style: none;
            padding: 0;
        }

        .quick-nav li {
            margin-bottom: 8px;
        }

        .quick-nav a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: block;
            padding: 5px 0;
        }

        .quick-nav a:hover {
            color: #10b981;
            padding-left: 10px;
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .faq-container {
                padding: 30px 20px;
                margin: 20px 0;
            }
            
            .faq-header h1 {
                font-size: 2rem;
            }
            
            .faq-categories {
                justify-content: center;
            }
            
            .category-btn {
                font-size: 13px;
                padding: 8px 16px;
            }

            .faq-question h4 {
                font-size: 1rem;
            }

            .stats-row {
                flex-direction: column;
                gap: 20px;
            }

            .quick-nav {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 576px) {
            .faq-container {
                padding: 20px 15px;
            }
            
            .faq-header h1 {
                font-size: 1.7rem;
            }
            
            .faq-search input {
                padding: 12px 40px 12px 15px;
                font-size: 14px;
            }

            .faq-question {
                padding: 15px;
            }

            .faq-question h4 {
                font-size: 0.95rem;
            }
        }

        /* Footer Düzenlemeleri */
        .footer-four {
            background: #ffffff !important;
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

        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
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
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15" style="color: #6c757d !important;">Sıkça Sorulan Sorular</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>S.S.S</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- FAQ Content -->
        <div class="container my-5">
            <div class="row">
                <div class="col-lg-3">
                    <!-- Quick Navigation -->
                    <div class="quick-nav d-none d-lg-block">
                        <h4><i class="fas fa-compass"></i> Kategoriler</h4>
                        <ul>
                            <li><a href="#emlak-alim-satim">Emlak Alım-Satım</a></li>
                            <li><a href="#emlak-kiralama">Emlak Kiralama</a></li>
                            <li><a href="#yatirim-danismanligi">Yatırım Danışmanlığı</a></li>
                            <li><a href="#hukuki-islemler">Hukuki İşlemler</a></li>
                            <li><a href="#finansman-kredi">Finansman & Kredi</a></li>
                            <li><a href="#emlak-degerleme">Emlak Değerleme</a></li>
                            <li><a href="#vergi-harç">Vergi & Harç</a></li>
                            <li><a href="#platform-kullanimi">Platform Kullanımı</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="faq-container">
                        
                        <!-- Header -->
                        <div class="faq-header">
                            <div class="faq-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h1>Sıkça Sorulan Sorular</h1>
                            <p>Gayrimenkul alım-satım, kiralama ve yatırım konularında merak ettiğiniz her şey</p>
                            
                            <!-- Search Box -->
                            <div class="faq-search">
                                <input type="text" id="faqSearch" placeholder="Sorunuzu aramak için yazın...">
                                <i class="fas fa-search"></i>
                            </div>

                            <!-- Category Filters -->
                            <div class="faq-categories">
                                <button class="category-btn active" data-category="all">Tümü</button>
                                <button class="category-btn" data-category="alim-satim">Alım-Satım</button>
                                <button class="category-btn" data-category="kiralama">Kiralama</button>
                                <button class="category-btn" data-category="yatirim">Yatırım</button>
                                <button class="category-btn" data-category="hukuki">Hukuki</button>
                                <button class="category-btn" data-category="finansman">Finansman</button>
                                <button class="category-btn" data-category="platform">Platform</button>
                            </div>

                            <!-- Stats -->
                            <div class="stats-row">
                                <div class="stat-item">
                                    <span class="stat-number">500+</span>
                                    <div class="stat-label">Satılan Emlak</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">15</span>
                                    <div class="stat-label">Yıllık Deneyim</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">1000+</span>
                                    <div class="stat-label">Mutlu Müşteri</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">50+</span>
                                    <div class="stat-label">Soru Cevabı</div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Alım-Satım -->
                        <div class="faq-section" id="emlak-alim-satim" data-category="alim-satim">
                            <h2><i class="fas fa-home"></i> Emlak Alım-Satım</h2>
                            
                            <div class="faq-item" data-category="alim-satim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak alım satımında hangi belgeler gereklidir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak alım satımında aşağıdaki belgeler mutlaka hazır olmalıdır:</p>
                                    <ul>
                                        <li><strong>Tapu Senedi:</strong> Emlakın sahiplik durumunu gösteren ana belge</li>
                                        <li><strong>Kimlik Belgeleri:</strong> Satıcı ve alıcının T.C. kimlik kartları</li>
                                        <li><strong>İkametgah Belgesi:</strong> Güncel ikametgah belgesi</li>
                                        <li><strong>Vergi Levhası:</strong> Emlakın vergi durumunu gösteren belge</li>
                                        <li><strong>Kat İrtifakı/Kat Mülkiyeti:</strong> Apartman dairesi ise gerekli</li>
                                        <li><strong>İmar Durumu:</strong> Arsalar için imar durumu belgesi</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-lightbulb"></i> Uzman Tavsiyesi</h5>
                                        <p>Tüm belgelerin güncel ve eksiksiz olduğundan emin olun. Eksik belgeler işlemi geciktirebilir.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Kiralama -->
                        <div class="faq-section" id="emlak-kiralama" data-category="kiralama">
                            <h2><i class="fas fa-key"></i> Emlak Kiralama</h2>
                            
                            <div class="faq-item" data-category="kiralama">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Kira sözleşmesi yaparken nelere dikkat etmeliyim?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Kira sözleşmesinde mutlaka bulunması gereken maddeler:</p>
                                    <ul>
                                        <li><strong>Kira Bedeli:</strong> Aylık kira miktarı ve ödeme tarihi</li>
                                        <li><strong>Depozito:</strong> Güvence bedeli (genellikle 1-3 aylık kira)</li>
                                        <li><strong>Süre:</strong> Kiralama süresi ve yenileme koşulları</li>
                                        <li><strong>Artış Oranı:</strong> Yıllık kira artış oranı (%25 üst sınır)</li>
                                        <li><strong>Sorumluluklar:</strong> Tamir, bakım, aidat sorumluluğu</li>
                                        <li><strong>Kullanım Amacı:</strong> Konut/işyeri kullanım şekli</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-gavel"></i> Yasal Uyarı</h5>
                                        <p>6098 sayılı Borçlar Kanunu'na göre kira artış oranı yıllık %25'i geçemez. Sözleşmenizin yasal çerçevede olduğundan emin olun.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- İletişim CTA -->
                        <div class="contact-cta">
                            <h3><i class="fas fa-question-circle"></i> Sorunuza cevap bulamadınız mı?</h3>
                            <p>Uzman ekibimiz size yardımcı olmaya hazır. Hemen iletişime geçin!</p>
                            <div>
                                <a href="contact.php" class="btn">
                                    <i class="fas fa-envelope"></i> İletişim Formu
                                </a>
                                <a href="tel:+902128016058" class="btn">
                                    <i class="fas fa-phone"></i> Hemen Ara
                                </a>
                                <a href="https://wa.me/905302037083" class="btn">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>
                                    <p>Kiralama komisyonu genellikle şu şekilde alınır:</p>
                                    <ul>
                                        <li><strong>Kiracıdan:</strong> 1 aylık kiranın %50-100'ü</li>
                                        <li><strong>Ev Sahibinden:</strong> 1 aylık kiranın %50-100'ü</li>
                                        <li><strong>Toplam Komisyon:</strong> Genellikle 1 aylık kira tutarı</li>
                                        <li><strong>Özel Durumlar:</strong> Lüks emlaklar için farklı oranlar uygulanabilir</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-handshake"></i> Adil Komisyon</h5>
                                        <p>Komisyon oranları her iki tarafla önceden anlaşılır ve şeffaf şekilde belirtilir.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Yatırım Danışmanlığı -->
                        <div class="faq-section" id="yatirim-danismanligi" data-category="yatirim">
                            <h2><i class="fas fa-chart-line"></i> Yatırım Danışmanlığı</h2>
                            
                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Gayrimenkul yatırımında hangi bölgeler öneriliyor?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>İstanbul'da yatırım açısından öne çıkan bölgeler:</p>
                                    <ul>
                                        <li><strong>Başakşehir:</strong> Yeni havalimanı yakınlığı, gelişen altyapı</li>
                                        <li><strong>Beylikdüzü:</strong> Marina, AVM, ulaşım avantajları</li>
                                        <li><strong>Küçükçekmece:</strong> Uygun fiyatlar, değer artış potansiyeli</li>
                                        <li><strong>Esenyurt:</strong> Yüksek kira getirisi, genç nüfus</li>
                                        <li><strong>Bahçeşehir:</strong> Prestijli konum, kaliteli projeler</li>
                                        <li><strong>Aviasyon:</strong> Havalimanı bağlantısı, ticari potansiyel</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-map-marked-alt"></i> Lokasyon Analizi</h5>
                                        <p>Her bölgenin kendine özel avantajları vardır. Kişisel ihtiyaçlarınıza göre en uygun bölgeyi belirleyelim.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Gayrimenkul yatırımında getiri oranı nasıl hesaplanır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Gayrimenkul getiri hesaplama yöntemleri:</p>
                                    <ul>
                                        <li><strong>Brüt Getiri:</strong> (Yıllık Kira Geliri / Emlak Değeri) x 100</li>
                                        <li><strong>Net Getiri:</strong> (Net Kira Geliri / Toplam Yatırım) x 100</li>
                                        <li><strong>Kapitalizasyon Oranı:</strong> Net işletme geliri / emlak değeri</li>
                                        <li><strong>İRR (İç Verim Oranı):</strong> Karmaşık hesaplama, uzman desteği önerilir</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-calculator"></i> Gider Kalemleri</h5>
                                        <p>Aidat, vergi, sigorta, bakım-onarım giderlerini hesaba katmayı unutmayın.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Yabancı uyruklu yatırımcılar emlak alabilir mi?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Yabancı yatırımcılar için emlak alım koşulları:</p>
                                    <ul>
                                        <li><strong>Genel Kural:</strong> 30 hektara kadar arazi alabilir</li>
                                        <li><strong>Yasaklı Bölgeler:</strong> Askeri, güvenlik bölgeleri yasak</li>
                                        <li><strong>Karşılıklılık İlkesi:</strong> Ülkesinde Türklere tanınan haklar</li>
                                        <li><strong>Şirket Kurma:</strong> Türk şirketi kurarak sınırsız alım</li>
                                        <li><strong>Vatandaşlık:</strong> 400,000 USD üzeri yatırımla vatandaşlık</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-passport"></i> Özel Hizmet</h5>
                                        <p>Yabancı yatırımcılar için özel danışmanlık ve hukuki destek hizmeti sunuyoruz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hukuki İşlemler -->
                        <div class="faq-section" id="hukuki-islemler" data-category="hukuki">
                            <h2><i class="fas fa-gavel"></i> Hukuki İşlemler</h2>
                            
                            <div class="faq-item" data-category="hukuki">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Tapu devri sırasında hangi işlemler yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Tapu devrinde yapılan temel işlemler:</p>
                                    <ul>
                                        <li><strong>Belge Kontrolü:</strong> Tüm evrakların kontrolü ve onayı</li>
                                        <li><strong>Kimlik Teyidi:</strong> Tarafların kimlik doğrulaması</li>
                                        <li><strong>Borç Araştırması:</strong> Emlak üzerindeki borç/haciz kontrolü</li>
                                        <li><strong>Bedel Beyanı:</strong> Satış bedelinin beyan edilmesi</li>
                                        <li><strong>Harç Ödemesi:</strong> Tapu harcının ödenmesi</li>
                                        <li><strong>İmza ve Devir:</strong> Tapu senedinin imzalanması</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-clock"></i> Süreç Süresi</h5>
                                        <p>Normal şartlarda tapu devri 1-2 saat içerisinde tamamlanır. Yoğun dönemlerde daha uzun sürebilir.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="hukuki">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak satışında vekalet verilebilir mi?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak satışında vekalet verme koşulları:</p>
                                    <ul>
                                        <li><strong>Noter Vekaleti:</strong> Mutlaka noter onaylı olmalı</li>
                                        <li><strong>Özel Vekalet:</strong> Belirli emlak için özel yetki</li>
                                        <li><strong>Fiyat Belirtme:</strong> Satış fiyatı vekaletnamede yazılı olmalı</li>
                                        <li><strong>İptal Hakkı:</strong> Vekalet her zaman iptal edilebilir</li>
                                        <li><strong>Sorumluluk:</strong> Vekil yasal sorumluluklara tabidir</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-user-shield"></i> Güvenli Vekalet</h5>
                                        <p>Vekaletname hazırlama ve hukuki danışmanlık için uzman desteği alabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finansman & Kredi -->
                        <div class="faq-section" id="finansman-kredi" data-category="finansman">
                            <h2><i class="fas fa-university"></i> Finansman & Kredi</h2>
                            
                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Konut kredisi için hangi belgeler gerekli?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Konut kredisi başvurusu için gerekli belgeler:</p>
                                    <ul>
                                        <li><strong>Kimlik Belgeleri:</strong> T.C. kimlik kartı, ikametgah belgesi</li>
                                        <li><strong>Gelir Belgeleri:</strong> Maaş bordrosu, SGK hizmet belgesi</li>
                                        <li><strong>Banka Ekstreleri:</strong> Son 3-6 aylık hesap hareketleri</li>
                                        <li><strong>Emlak Belgeleri:</strong> Satış sözleşmesi, tapu fotokopisi</li>
                                        <li><strong>Sigorta Poliçesi:</strong> Emlak ve yaşam sigortası</li>
                                        <li><strong>Ekspertiz Raporu:</strong> Bankanın istediği değerleme raporu</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-percentage"></i> Kredi Oranları</h5>
                                        <p>Mevcut faiz oranları ve kredi koşulları için banka temsilcilerimizle iletişime geçebilirsiniz.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Konut kredisi faiz oranları nasıl belirlenir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Konut kredisi faiz oranını etkileyen faktörler:</p>
                                    <ul>
                                        <li><strong>TCMB Politika Faizi:</strong> Merkez bankası faiz kararları</li>
                                        <li><strong>Kredi Notu:</strong> Müşterinin kredi geçmişi</li>
                                        <li><strong>Kredi/Teminat Oranı:</strong> LTV (Loan to Value) oranı</li>
                                        <li><strong>Vade Süresi:</strong> Geri ödeme süresinin uzunluğu</li>
                                        <li><strong>Gelir Durumu:</strong> Aylık gelir ve istihdam durumu</li>
                                        <li><strong>Banka Politikaları:</strong> Her bankanın kendi stratejisi</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-chart-line"></i> Faiz Değişimi</h5>
                                        <p>Değişken faizli kredilerde oranlar piyasa koşullarına göre güncellenebilir.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Değerleme -->
                        <div class="faq-section" id="emlak-degerleme" data-category="platform">
                            <h2><i class="fas fa-search-dollar"></i> Emlak Değerleme</h2>
                            
                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak değerleme nasıl yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Profesyonel emlak değerleme süreci:</p>
                                    <ul>
                                        <li><strong>Fiziksel İnceleme:</strong> Emlağın yerinde detaylı incelemesi</li>
                                        <li><strong>Piyasa Analizi:</strong> Benzer emlakların satış/kira karşılaştırması</li>
                                        <li><strong>Lokasyon Değerlendirmesi:</strong> Çevre analizi, ulaşım olanakları</li>
                                        <li><strong>Teknik Kontrol:</strong> Yapısal durum, malzeme kalitesi</li>
                                        <li><strong>Yasal Durum:</strong> İmar, ruhsat, tapu durumu kontrolü</li>
                                        <li><strong>Değerleme Raporu:</strong> Detaylı yazılı rapor hazırlanması</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-certificate"></i> Lisanslı Ekspertiz</i>
                                        <p>Değerleme işlemlerimiz SPK lisanslı ekspertiz şirketleri tarafından yapılmaktadır.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vergi & Harç -->
                        <div class="faq-section" id="vergi-harç" data-category="finansman">
                            <h2><i class="fas fa-receipt"></i> Vergi & Harç</h2>
                            
                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak alımında hangi vergiler ödenir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak alımında ödenmesi gereken vergi ve harçlar:</p>
                                    <ul>
                                        <li><strong>Tapu Harcı:</strong> Emlak değerinin %4'ü (alıcı-satıcı eşit paylaşır)</li>
                                        <li><strong>KDV:</strong> İlk satışlarda %1-18 arası (proje tipine göre)</li>
                                        <li><strong>Emlak Vergisi:</strong> Yıllık, emlak değeri üzerinden hesaplanır</li>
                                        <li><strong>Çevre Temizlik Vergisi:</strong> Belediye vergileri</li>
                                        <li><strong>Muhtarlık Payı:</strong> Sembolik ücret</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-calculator"></i> Hesaplama</h5>
                                        <p>Net ödeyeceğiniz vergi tutarı için detaylı hesaplama yapabiliriz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Kullanımı -->
                        <div class="faq-section" id="platform-kullanimi" data-category="platform">
                            <h2><i class="fas fa-laptop"></i> Platform Kullanımı</h2>
                            
                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Web sitesinde emlak arama nasıl yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Gelişmiş arama özelliklerimizi kullanarak:</p>
                                    <ul>
                                        <li><strong>Lokasyon Seçimi:</strong> İl, ilçe, mahalle bazında filtreleme</li>
                                        <li><strong>Fiyat Aralığı:</strong> Minimum ve maksimum bütçe belirleme</li>
                                        <li><strong>Emlak Tipi:</strong> Daire, müstakil, villa, arsa seçenekleri</li>
                                        <li><strong>Özellik Filtreleri:</strong> Oda sayısı, yaş, m² filtreleri</li>
                                        <li><strong>Harita Görünümü:</strong> Emlakları harita üzerinde görüntüleme</li>
                                        <li><strong>Favori Listesi:</strong> Beğendiğiniz emlakları kaydetme</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-bell"></i> Bildirim Servisi</h5>
                                        <p>Arama kriterlerinize uygun yeni emlaklar için e-posta bildirimi alabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Hesap oluşturmanın avantajları neler?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Üye olmak size şu avantajları sağlar:</p>
                                    <ul>
                                        <li><strong>Favori Emlaklar:</strong> Beğendiğiniz emlakları kaydedin</li>
                                        <li><strong>Arama Geçmişi:</strong> Eski aramalarınıza tekrar erişin</li>
                                        <li><strong>Özel Fırsatlar:</strong> Sadece üyelere özel kampanyalar</li>
                                        <li><strong>Hızlı İletişim:</strong> Tek tıkla danışman desteği</li>
                                        <li><strong>Detaylı Raporlar:</strong> Emlak analiz raporlarına erişim</li>
                                        <li><strong>Mobil Uygulama:</strong> Mobil uygulamaya özel özellikler</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-gift"></i> Üyelik Ücretsiz</h5>
                                        <p>Hesap oluşturmak tamamen ücretsizdir ve kişisel verileriniz güvende tutulur.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- İletişim CTA -->
                        <div class="contact-cta">
                            <h3><i class="fas fa-question-circle"></i> Sorunuza cevap bulamadınız mı?</h3>
                            <p>Uzman ekibimiz size yardımcı olmaya hazır. Hemen iletişime geçin!</p>
                            <div>
                                <a href="contact.php" class="btn">
                                    <i class="fas fa-envelope"></i> İletişim Formu
                                </a>
                                <a href="tel:+902128016058" class="btn">
                                    <i class="fas fa-phone"></i> Hemen Ara
                                </a>
                                <a href="https://wa.me/905302037083" class="btn">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
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
                                    <li><a href="https://wa.me/905302037083"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="https://instagram.com/gokhanaydinligayrimenkul"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="https://linkedin.com/in/gokhanaydinli"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="register.php">Üyelik</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="admin/index.php">Admin Panel</a></li>
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
                                    <li><a href="portfoy.php?category=ticari">Ticari Gayrimenkul</a></li>
                                    <li><a href="portfoy.php?transaction_type=satilik">Konut Satışı</a></li>
                                    <li><a href="portfoy.php?transaction_type=kiralik">Ev Kiralama</a></li>
                                    <li><a href="contact.php">Yatırım Danışmanlığı</a></li>
                                    <li><a href="portfoy.php?category=villa">Villa Satışı</a></li>
                                    <li><a href="portfoy.php?category=ofis">Ofis Kiralama</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
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
        document.addEventListener('DOMContentLoaded', function() {
            // Scroll to top button
            window.addEventListener('scroll', function() {
                const scrollTop = document.querySelector('.scroll-top');
                if (window.pageYOffset > 300) {
                    scrollTop.style.display = 'block';
                    scrollTop.style.opacity = '1';
                } else {
                    scrollTop.style.opacity = '0';
                    setTimeout(() => {
                        scrollTop.style.display = 'none';
                    }, 300);
                }
            });

            // Initialize lazy loading
            if (typeof $ !== 'undefined' && $.fn.lazy) {
                $('.lazy-img').lazy();
            }

            // FAQ Search functionality
            const searchInput = document.getElementById('faqSearch');
            const faqItems = document.querySelectorAll('.faq-item');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question h4').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        // Highlight search term
                        if (searchTerm.length > 2) {
                            highlightSearchTerm(item, searchTerm);
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show message if no results
                const visibleItems = Array.from(faqItems).filter(item => item.style.display !== 'none');
                showNoResultsMessage(visibleItems.length === 0 && searchTerm.length > 0);
            });

            // Category filtering
            const categoryButtons = document.querySelectorAll('.category-btn');
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    // Update active button
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter FAQ items
                    faqItems.forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Clear search
                    searchInput.value = '';
                    removeHighlights();
                    hideNoResultsMessage();
                });
            });

            // Smooth scrolling for quick nav links
            document.querySelectorAll('.quick-nav a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Quick nav active state
            window.addEventListener('scroll', function() {
                const sections = document.querySelectorAll('.faq-section[id]');
                const navLinks = document.querySelectorAll('.quick-nav a');
                
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (window.pageYOffset >= sectionTop - 200) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.style.color = '#6b7280';
                    link.style.fontWeight = 'normal';
                    if (link.getAttribute('href') === '#' + current) {
                        link.style.color = '#10b981';
                        link.style.fontWeight = '600';
                    }
                });
            });

            // Animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all FAQ sections
            document.querySelectorAll('.faq-section').forEach(function(section) {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });
        });

        // FAQ Toggle Function
        function toggleFaq(element) {
            const faqItem = element.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-item.active').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current item
            if (isActive) {
                faqItem.classList.remove('active');
            } else {
                faqItem.classList.add('active');
            }
        }

        // Search highlighting function
        function highlightSearchTerm(item, term) {
            removeHighlights();
            
            const question = item.querySelector('.faq-question h4');
            const answer = item.querySelector('.faq-answer p');
            
            if (question) {
                const questionText = question.textContent;
                const regex = new RegExp(`(${term})`, 'gi');
                question.innerHTML = questionText.replace(regex, '<mark style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">$1</mark>');
            }
        }

        function removeHighlights() {
            document.querySelectorAll('mark').forEach(mark => {
                mark.outerHTML = mark.innerHTML;
            });
        }

        function showNoResultsMessage(show) {
            let noResultsMsg = document.getElementById('noResultsMessage');
            
            if (show && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.style.cssText = `
                    text-align: center;
                    padding: 40px;
                    color: #6b7280;
                    font-size: 1.1rem;
                    background: #f9fafb;
                    border-radius: 12px;
                    margin: 20px 0;
                `;
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px; color: #d1d5db;"></i>
                    <h4 style="color: #374151; margin-bottom: 10px;">Aradığınız soru bulunamadı</h4>
                    <p>Farklı kelimeler deneyebilir veya bizimle doğrudan iletişime geçebilirsiniz.</p>
                    <a href="contact.php" class="btn" style="background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 15px; display: inline-block;">Soru Sorun</a>
                `;
                document.querySelector('.faq-container').appendChild(noResultsMsg);
            } else if (!show && noResultsMsg) {
                hideNoResultsMessage();
            }
        }

        function hideNoResultsMessage() {
            const noResultsMsg = document.getElementById('noResultsMessage');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('faqSearch').focus();
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('faqSearch');
                if (searchInput.value) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                }
            }
        });

        // Add search shortcut hint
        document.getElementById('faqSearch').placeholder = 'Sorunuzu aramak için yazın... (Ctrl+K)';
        </script>
    </div>
</body>
</html>
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
    <meta property="og:type" content="website">
    <meta property="og:title" content="S.S.S - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/faq-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
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
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #059669 0%, #10b981 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .faq-header .faq-icon {
            font-size: 4rem;
            color: #10b981;
            margin-bottom: 20px;
            animation: question-bounce 3s infinite;
        }

        @keyframes question-bounce {
            0%, 100% {
                transform: rotate(0deg) scale(1);
            }
            25% {
                transform: rotate(-5deg) scale(1.05);
            }
            75% {
                transform: rotate(5deg) scale(1.05);
            }
        }

        .faq-header p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .faq-search {
            max-width: 500px;
            margin: 0 auto 30px auto;
            position: relative;
        }

        .faq-search input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 25px;
            font-size: 16px;
            outline: none;
            transition: all 0.3s ease;
        }

        .faq-search input:focus {
            border-color: #10b981;
            box-shadow: 0 0 20px rgba(16, 185, 129, 0.2);
        }

        .faq-search i {
            position: absolute;
            right: 20px;
            top: 50%;
            transform: translateY(-50%);
            color: #6b7280;
            font-size: 18px;
        }

        .faq-categories {
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            gap: 15px;
            margin-bottom: 40px;
        }

        .category-btn {
            background: #f3f4f6;
            color: #374151;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .category-btn:hover,
        .category-btn.active {
            background: #10b981;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(16, 185, 129, 0.3);
        }

        .faq-section {
            margin-bottom: 40px;
        }

        .faq-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            border-bottom: 2px solid #f0f2f5;
            padding-bottom: 15px;
        }

        .faq-section h2 i {
            margin-right: 15px;
            color: #10b981;
            font-size: 1.5rem;
        }

        .faq-item {
            background: #fafbfc;
            border: 1px solid #e5e7eb;
            border-radius: 12px;
            margin-bottom: 15px;
            overflow: hidden;
            transition: all 0.3s ease;
        }

        .faq-item:hover {
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.08);
            transform: translateY(-2px);
        }

        .faq-question {
            padding: 20px;
            cursor: pointer;
            display: flex;
            justify-content: space-between;
            align-items: center;
            background: white;
            transition: all 0.3s ease;
        }

        .faq-question:hover {
            background: #f9fafb;
        }

        .faq-question h4 {
            font-size: 1.1rem;
            font-weight: 600;
            color: #2d3748;
            margin: 0;
            flex: 1;
            line-height: 1.4;
        }

        .faq-toggle {
            font-size: 1.2rem;
            color: #10b981;
            transition: transform 0.3s ease;
            margin-left: 15px;
        }

        .faq-item.active .faq-toggle {
            transform: rotate(180deg);
        }

        .faq-answer {
            padding: 0 20px;
            max-height: 0;
            overflow: hidden;
            transition: all 0.3s ease;
            background: #f8f9fa;
        }

        .faq-item.active .faq-answer {
            max-height: 500px;
            padding: 20px;
        }

        .faq-answer p {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin: 0;
        }

        .faq-answer ul {
            margin: 10px 0;
            padding-left: 20px;
        }

        .faq-answer li {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 5px;
        }

        .highlight-tip {
            background: linear-gradient(135deg, #10b98110 0%, #05966910 100%);
            border: 1px solid #10b98130;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .highlight-tip h5 {
            color: #059669;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .warning-tip {
            background: linear-gradient(135deg, #f5940510 0%, #d9770410 100%);
            border: 1px solid #f5940530;
            border-radius: 8px;
            padding: 15px;
            margin: 15px 0;
        }

        .warning-tip h5 {
            color: #d97706;
            font-weight: 600;
            margin-bottom: 8px;
            font-size: 1rem;
        }

        .contact-cta {
            background: #10b981;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }

        .contact-cta h3 {
            color: white;
            margin-bottom: 15px;
        }

        .contact-cta .btn {
            background: white;
            color: #10b981;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .contact-cta .btn:hover {
            background: #f3f4f6;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
            color: #10b981;
        }

        .stats-row {
            display: flex;
            justify-content: space-around;
            margin: 30px 0;
            text-align: center;
        }

        .stat-item {
            flex: 1;
        }

        .stat-number {
            font-size: 2rem;
            font-weight: 700;
            color: #10b981;
            display: block;
        }

        .stat-label {
            font-size: 0.9rem;
            color: #6b7280;
            margin-top: 5px;
        }

        .quick-nav {
            position: sticky;
            top: 100px;
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0, 0, 0, 0.1);
            margin-bottom: 30px;
        }

        .quick-nav h4 {
            color: #10b981;
            margin-bottom: 15px;
            font-size: 1.1rem;
        }

        .quick-nav ul {
            list-style: none;
            padding: 0;
        }

        .quick-nav li {
            margin-bottom: 8px;
        }

        .quick-nav a {
            color: #6b7280;
            text-decoration: none;
            font-size: 0.9rem;
            transition: all 0.3s ease;
            display: block;
            padding: 5px 0;
        }

        .quick-nav a:hover {
            color: #10b981;
            padding-left: 10px;
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #10b981;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #059669;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .faq-container {
                padding: 30px 20px;
                margin: 20px 0;
            }
            
            .faq-header h1 {
                font-size: 2rem;
            }
            
            .faq-categories {
                justify-content: center;
            }
            
            .category-btn {
                font-size: 13px;
                padding: 8px 16px;
            }

            .faq-question h4 {
                font-size: 1rem;
            }

            .stats-row {
                flex-direction: column;
                gap: 20px;
            }

            .quick-nav {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 576px) {
            .faq-container {
                padding: 20px 15px;
            }
            
            .faq-header h1 {
                font-size: 1.7rem;
            }
            
            .faq-search input {
                padding: 12px 40px 12px 15px;
                font-size: 14px;
            }

            .faq-question {
                padding: 15px;
            }

            .faq-question h4 {
                font-size: 0.95rem;
            }
        }

        /* Footer Düzenlemeleri */
        .footer-four {
            background: #ffffff !important;
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

        .bottom-footer {
            border-top: 1px solid #e9ecef !important;
            color: #6c757d !important;
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
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative" style="background: linear-gradient(135deg, #ffffff 0%, #f8f9fa 100%) !important;">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15" style="color: #6c757d !important;">Sıkça Sorulan Sorular</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>S.S.S</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- FAQ Content -->
        <div class="container my-5">
            <div class="row">
                <div class="col-lg-3">
                    <!-- Quick Navigation -->
                    <div class="quick-nav d-none d-lg-block">
                        <h4><i class="fas fa-compass"></i> Kategoriler</h4>
                        <ul>
                            <li><a href="#emlak-alim-satim">Emlak Alım-Satım</a></li>
                            <li><a href="#emlak-kiralama">Emlak Kiralama</a></li>
                            <li><a href="#yatirim-danismanligi">Yatırım Danışmanlığı</a></li>
                            <li><a href="#hukuki-islemler">Hukuki İşlemler</a></li>
                            <li><a href="#finansman-kredi">Finansman & Kredi</a></li>
                            <li><a href="#emlak-degerleme">Emlak Değerleme</a></li>
                            <li><a href="#vergi-harç">Vergi & Harç</a></li>
                            <li><a href="#platform-kullanimi">Platform Kullanımı</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="faq-container">
                        
                        <!-- Header -->
                        <div class="faq-header">
                            <div class="faq-icon">
                                <i class="fas fa-question-circle"></i>
                            </div>
                            <h1>Sıkça Sorulan Sorular</h1>
                            <p>Gayrimenkul alım-satım, kiralama ve yatırım konularında merak ettiğiniz her şey</p>
                            
                            <!-- Search Box -->
                            <div class="faq-search">
                                <input type="text" id="faqSearch" placeholder="Sorunuzu aramak için yazın...">
                                <i class="fas fa-search"></i>
                            </div>

                            <!-- Category Filters -->
                            <div class="faq-categories">
                                <button class="category-btn active" data-category="all">Tümü</button>
                                <button class="category-btn" data-category="alim-satim">Alım-Satım</button>
                                <button class="category-btn" data-category="kiralama">Kiralama</button>
                                <button class="category-btn" data-category="yatirim">Yatırım</button>
                                <button class="category-btn" data-category="hukuki">Hukuki</button>
                                <button class="category-btn" data-category="finansman">Finansman</button>
                                <button class="category-btn" data-category="platform">Platform</button>
                            </div>

                            <!-- Stats -->
                            <div class="stats-row">
                                <div class="stat-item">
                                    <span class="stat-number">500+</span>
                                    <div class="stat-label">Satılan Emlak</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">15</span>
                                    <div class="stat-label">Yıllık Deneyim</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">1000+</span>
                                    <div class="stat-label">Mutlu Müşteri</div>
                                </div>
                                <div class="stat-item">
                                    <span class="stat-number">50+</span>
                                    <div class="stat-label">Soru Cevabı</div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Alım-Satım -->
                        <div class="faq-section" id="emlak-alim-satim" data-category="alim-satim">
                            <h2><i class="fas fa-home"></i> Emlak Alım-Satım</h2>
                            
                            <div class="faq-item" data-category="alim-satim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak alım satımında hangi belgeler gereklidir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak alım satımında aşağıdaki belgeler mutlaka hazır olmalıdır:</p>
                                    <ul>
                                        <li><strong>Tapu Senedi:</strong> Emlakın sahiplik durumunu gösteren ana belge</li>
                                        <li><strong>Kimlik Belgeleri:</strong> Satıcı ve alıcının T.C. kimlik kartları</li>
                                        <li><strong>İkametgah Belgesi:</strong> Güncel ikametgah belgesi</li>
                                        <li><strong>Vergi Levhası:</strong> Emlakın vergi durumunu gösteren belge</li>
                                        <li><strong>Kat İrtifakı/Kat Mülkiyeti:</strong> Apartman dairesi ise gerekli</li>
                                        <li><strong>İmar Durumu:</strong> Arsalar için imar durumu belgesi</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-lightbulb"></i> Uzman Tavsiyesi</h5>
                                        <p>Tüm belgelerin güncel ve eksiksiz olduğundan emin olun. Eksik belgeler işlemi geciktirebilir.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="alim-satim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak satış komisyonu nasıl hesaplanır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak komisyon oranları işlem türüne göre değişkenlik gösterir:</p>
                                    <ul>
                                        <li><strong>Konut Satışı:</strong> %2-4 arası (satış fiyatına göre)</li>
                                        <li><strong>Ticari Emlak:</strong> %3-5 arası</li>
                                        <li><strong>Arsa Satışı:</strong> %3-6 arası</li>
                                        <li><strong>Lüks Emlak:</strong> %1-3 arası (yüksek fiyatlı projeler)</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-info-circle"></i> Önemli Not</h5>
                                        <p>Komisyon oranları emlak değeri, lokasyon ve özel durumlar göz önünde bulundurularak belirlenir. Detaylı bilgi için iletişime geçin.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="alim-satim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak alırken nelere dikkat etmeliyim?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak alımında dikkat edilmesi gereken temel konular:</p>
                                    <ul>
                                        <li><strong>Lokasyon Analizi:</strong> Ulaşım, altyapı, sosyal tesisler</li>
                                        <li><strong>Yapısal Durum:</strong> Binanın yaşı, sağlamlığı, bakım durumu</li>
                                        <li><strong>Hukuki Durum:</strong> Tapu temizliği, imar durumu, haciz varlığı</li>
                                        <li><strong>Finansal Analiz:</strong> Piyasa değeri, gelecek potansiyeli</li>
                                        <li><strong>Çevre Faktörleri:</strong> Komşuluk, gürültü, güvenlik</li>
                                        <li><strong>İnfrastrüktür:</strong> Elektrik, su, doğalgaz, internet</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-user-tie"></i> Uzman Desteği</h5>
                                        <p>Deneyimli emlak danışmanımız ile birlikte bu kriterleri değerlendirerek en doğru kararı verebilirsiniz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Kiralama -->
                        <div class="faq-section" id="emlak-kiralama" data-category="kiralama">
                            <h2><i class="fas fa-key"></i> Emlak Kiralama</h2>
                            
                            <div class="faq-item" data-category="kiralama">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Kira sözleşmesi yaparken nelere dikkat etmeliyim?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Kira sözleşmesinde mutlaka bulunması gereken maddeler:</p>
                                    <ul>
                                        <li><strong>Kira Bedeli:</strong> Aylık kira miktarı ve ödeme tarihi</li>
                                        <li><strong>Depozito:</strong> Güvence bedeli (genellikle 1-3 aylık kira)</li>
                                        <li><strong>Süre:</strong> Kiralama süresi ve yenileme koşulları</li>
                                        <li><strong>Artış Oranı:</strong> Yıllık kira artış oranı (%25 üst sınır)</li>
                                        <li><strong>Sorumluluklar:</strong> Tamir, bakım, aidat sorumluluğu</li>
                                        <li><strong>Kullanım Amacı:</strong> Konut/işyeri kullanım şekli</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-gavel"></i> Yasal Uyarı</h5>
                                        <p>6098 sayılı Borçlar Kanunu'na göre kira artış oranı yıllık %25'i geçemez. Sözleşmenizin yasal çerçevede olduğundan emin olun.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="kiralama">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Kiralama komisyonu ne kadar?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Kiralama komisyonu genellikle şu şekilde alınır:</p>
                                    <ul>
                                        <li><strong>Kiracıdan:</strong> 1 aylık kiranın %50-100'ü</li>
                                        <li><strong>Ev Sahibinden:</strong> 1 aylık kiranın %50-100'ü</li>
                                        <li><strong>Toplam Komisyon:</strong> Genellikle 1 aylık kira tutarı</li>
                                        <li><strong>Özel Durumlar:</strong> Lüks emlaklar için farklı oranlar uygulanabilir</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-handshake"></i> Adil Komisyon</h5>
                                        <p>Komisyon oranları her iki tarafla önceden anlaşılır ve şeffaf şekilde belirtilir.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="kiralama">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Kira ödemelerinde gecikme durumunda ne olur?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Kira ödemelerinde gecikme durumunda yasal süreç:</p>
                                    <ul>
                                        <li><strong>Gecikme Faizi:</strong> Sözleşmede belirtilen oranda faiz uygulanır</li>
                                        <li><strong>İhtarname:</strong> 30 günlük ödeme için noter ihtarnamesi gönderilir</li>
                                        <li><strong>Tahliye Davası:</strong> İhtar sonrası tahliye davası açılabilir</li>
                                        <li><strong>İcra Takibi:</strong> Borç için icra takibi başlatılabilir</li>
                                        <li><strong>Kefil Sorumluluğu:</strong> Kefil varsa kendisinden tahsilat yapılır</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-clock"></i> Zaman Önemli</h5>
                                        <p>Ödeme güçlüğü yaşandığında ev sahibi ile erken iletişime geçmek sorunu çözmeye yardımcı olur.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Yatırım Danışmanlığı -->
                        <div class="faq-section" id="yatirim-danismanligi" data-category="yatirim">
                            <h2><i class="fas fa-chart-line"></i> Yatırım Danışmanlığı</h2>
                            
                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Gayrimenkul yatırımında hangi bölgeler öneriliyor?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>İstanbul'da yatırım açısından öne çıkan bölgeler:</p>
                                    <ul>
                                        <li><strong>Başakşehir:</strong> Yeni havalimanı yakınlığı, gelişen altyapı</li>
                                        <li><strong>Beylikdüzü:</strong> Marina, AVM, ulaşım avantajları</li>
                                        <li><strong>Küçükçekmece:</strong> Uygun fiyatlar, değer artış potansiyeli</li>
                                        <li><strong>Esenyurt:</strong> Yüksek kira getirisi, genç nüfus</li>
                                        <li><strong>Bahçeşehir:</strong> Prestijli konum, kaliteli projeler</li>
                                        <li><strong>Aviasyon:</strong> Havalimanı bağlantısı, ticari potansiyel</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-map-marked-alt"></i> Lokasyon Analizi</h5>
                                        <p>Her bölgenin kendine özel avantajları vardır. Kişisel ihtiyaçlarınıza göre en uygun bölgeyi belirleyelim.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Gayrimenkul yatırımında getiri oranı nasıl hesaplanır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Gayrimenkul getiri hesaplama yöntemleri:</p>
                                    <ul>
                                        <li><strong>Brüt Getiri:</strong> (Yıllık Kira Geliri / Emlak Değeri) x 100</li>
                                        <li><strong>Net Getiri:</strong> (Net Kira Geliri / Toplam Yatırım) x 100</li>
                                        <li><strong>Kapitalizasyon Oranı:</strong> Net işletme geliri / emlak değeri</li>
                                        <li><strong>İRR (İç Verim Oranı):</strong> Karmaşık hesaplama, uzman desteği önerilir</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-calculator"></i> Gider Kalemleri</h5>
                                        <p>Aidat, vergi, sigorta, bakım-onarım giderlerini hesaba katmayı unutmayın.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="yatirim">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Yabancı uyruklu yatırımcılar emlak alabilir mi?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Yabancı yatırımcılar için emlak alım koşulları:</p>
                                    <ul>
                                        <li><strong>Genel Kural:</strong> 30 hektara kadar arazi alabilir</li>
                                        <li><strong>Yasaklı Bölgeler:</strong> Askeri, güvenlik bölgeleri yasak</li>
                                        <li><strong>Karşılıklılık İlkesi:</strong> Ülkesinde Türklere tanınan haklar</li>
                                        <li><strong>Şirket Kurma:</strong> Türk şirketi kurarak sınırsız alım</li>
                                        <li><strong>Vatandaşlık:</strong> 400,000 USD üzeri yatırımla vatandaşlık</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-passport"></i> Özel Hizmet</h5>
                                        <p>Yabancı yatırımcılar için özel danışmanlık ve hukuki destek hizmeti sunuyoruz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Hukuki İşlemler -->
                        <div class="faq-section" id="hukuki-islemler" data-category="hukuki">
                            <h2><i class="fas fa-gavel"></i> Hukuki İşlemler</h2>
                            
                            <div class="faq-item" data-category="hukuki">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Tapu devri sırasında hangi işlemler yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Tapu devrinde yapılan temel işlemler:</p>
                                    <ul>
                                        <li><strong>Belge Kontrolü:</strong> Tüm evrakların kontrolü ve onayı</li>
                                        <li><strong>Kimlik Teyidi:</strong> Tarafların kimlik doğrulaması</li>
                                        <li><strong>Borç Araştırması:</strong> Emlak üzerindeki borç/haciz kontrolü</li>
                                        <li><strong>Bedel Beyanı:</strong> Satış bedelinin beyan edilmesi</li>
                                        <li><strong>Harç Ödemesi:</strong> Tapu harcının ödenmesi</li>
                                        <li><strong>İmza ve Devir:</strong> Tapu senedinin imzalanması</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-clock"></i> Süreç Süresi</h5>
                                        <p>Normal şartlarda tapu devri 1-2 saat içerisinde tamamlanır. Yoğun dönemlerde daha uzun sürebilir.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="hukuki">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak satışında vekalet verilebilir mi?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak satışında vekalet verme koşulları:</p>
                                    <ul>
                                        <li><strong>Noter Vekaleti:</strong> Mutlaka noter onaylı olmalı</li>
                                        <li><strong>Özel Vekalet:</strong> Belirli emlak için özel yetki</li>
                                        <li><strong>Fiyat Belirtme:</strong> Satış fiyatı vekaletnamede yazılı olmalı</li>
                                        <li><strong>İptal Hakkı:</strong> Vekalet her zaman iptal edilebilir</li>
                                        <li><strong>Sorumluluk:</strong> Vekil yasal sorumluluklara tabidir</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-user-shield"></i> Güvenli Vekalet</h5>
                                        <p>Vekaletname hazırlama ve hukuki danışmanlık için uzman desteği alabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Finansman & Kredi -->
                        <div class="faq-section" id="finansman-kredi" data-category="finansman">
                            <h2><i class="fas fa-university"></i> Finansman & Kredi</h2>
                            
                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Konut kredisi için hangi belgeler gerekli?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Konut kredisi başvurusu için gerekli belgeler:</p>
                                    <ul>
                                        <li><strong>Kimlik Belgeleri:</strong> T.C. kimlik kartı, ikametgah belgesi</li>
                                        <li><strong>Gelir Belgeleri:</strong> Maaş bordrosu, SGK hizmet belgesi</li>
                                        <li><strong>Banka Ekstreleri:</strong> Son 3-6 aylık hesap hareketleri</li>
                                        <li><strong>Emlak Belgeleri:</strong> Satış sözleşmesi, tapu fotokopisi</li>
                                        <li><strong>Sigorta Poliçesi:</strong> Emlak ve yaşam sigortası</li>
                                        <li><strong>Ekspertiz Raporu:</strong> Bankanın istediği değerleme raporu</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-percentage"></i> Kredi Oranları</h5>
                                        <p>Mevcut faiz oranları ve kredi koşulları için banka temsilcilerimizle iletişime geçebilirsiniz.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Konut kredisi faiz oranları nasıl belirlenir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Konut kredisi faiz oranını etkileyen faktörler:</p>
                                    <ul>
                                        <li><strong>TCMB Politika Faizi:</strong> Merkez bankası faiz kararları</li>
                                        <li><strong>Kredi Notu:</strong> Müşterinin kredi geçmişi</li>
                                        <li><strong>Kredi/Teminat Oranı:</strong> LTV (Loan to Value) oranı</li>
                                        <li><strong>Vade Süresi:</strong> Geri ödeme süresinin uzunluğu</li>
                                        <li><strong>Gelir Durumu:</strong> Aylık gelir ve istihdam durumu</li>
                                        <li><strong>Banka Politikaları:</strong> Her bankanın kendi stratejisi</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-chart-line"></i> Faiz Değişimi</h5>
                                        <p>Değişken faizli kredilerde oranlar piyasa koşullarına göre güncellenebilir.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Emlak Değerleme -->
                        <div class="faq-section" id="emlak-degerleme" data-category="platform">
                            <h2><i class="fas fa-search-dollar"></i> Emlak Değerleme</h2>
                            
                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak değerleme nasıl yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Profesyonel emlak değerleme süreci:</p>
                                    <ul>
                                        <li><strong>Fiziksel İnceleme:</strong> Emlağın yerinde detaylı incelemesi</li>
                                        <li><strong>Piyasa Analizi:</strong> Benzer emlakların satış/kira karşılaştırması</li>
                                        <li><strong>Lokasyon Değerlendirmesi:</strong> Çevre analizi, ulaşım olanakları</li>
                                        <li><strong>Teknik Kontrol:</strong> Yapısal durum, malzeme kalitesi</li>
                                        <li><strong>Yasal Durum:</strong> İmar, ruhsat, tapu durumu kontrolü</li>
                                        <li><strong>Değerleme Raporu:</strong> Detaylı yazılı rapor hazırlanması</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-certificate"></i> Lisanslı Ekspertiz</h5>
                                        <p>Değerleme işlemlerimiz SPK lisanslı ekspertiz şirketleri tarafından yapılmaktadır.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Vergi & Harç -->
                        <div class="faq-section" id="vergi-harç" data-category="finansman">
                            <h2><i class="fas fa-receipt"></i> Vergi & Harç</h2>
                            
                            <div class="faq-item" data-category="finansman">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Emlak alımında hangi vergiler ödenir?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Emlak alımında ödenmesi gereken vergi ve harçlar:</p>
                                    <ul>
                                        <li><strong>Tapu Harcı:</strong> Emlak değerinin %4'ü (alıcı-satıcı eşit paylaşır)</li>
                                        <li><strong>KDV:</strong> İlk satışlarda %1-18 arası (proje tipine göre)</li>
                                        <li><strong>Emlak Vergisi:</strong> Yıllık, emlak değeri üzerinden hesaplanır</li>
                                        <li><strong>Çevre Temizlik Vergisi:</strong> Belediye vergileri</li>
                                        <li><strong>Muhtarlık Payı:</strong> Sembolik ücret</li>
                                    </ul>
                                    <div class="warning-tip">
                                        <h5><i class="fas fa-calculator"></i> Hesaplama</h5>
                                        <p>Net ödeyeceğiniz vergi tutarı için detaylı hesaplama yapabiliriz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Platform Kullanımı -->
                        <div class="faq-section" id="platform-kullanimi" data-category="platform">
                            <h2><i class="fas fa-laptop"></i> Platform Kullanımı</h2>
                            
                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Web sitesinde emlak arama nasıl yapılır?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Gelişmiş arama özelliklerimizi kullanarak:</p>
                                    <ul>
                                        <li><strong>Lokasyon Seçimi:</strong> İl, ilçe, mahalle bazında filtreleme</li>
                                        <li><strong>Fiyat Aralığı:</strong> Minimum ve maksimum bütçe belirleme</li>
                                        <li><strong>Emlak Tipi:</strong> Daire, müstakil, villa, arsa seçenekleri</li>
                                        <li><strong>Özellik Filtreleri:</strong> Oda sayısı, yaş, m² filtreleri</li>
                                        <li><strong>Harita Görünümü:</strong> Emlakları harita üzerinde görüntüleme</li>
                                        <li><strong>Favori Listesi:</strong> Beğendiğiniz emlakları kaydetme</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-bell"></i> Bildirim Servisi</h5>
                                        <p>Arama kriterlerinize uygun yeni emlaklar için e-posta bildirimi alabilirsiniz.</p>
                                    </div>
                                </div>
                            </div>

                            <div class="faq-item" data-category="platform">
                                <div class="faq-question" onclick="toggleFaq(this)">
                                    <h4>Hesap oluşturmanın avantajları neler?</h4>
                                    <i class="fas fa-chevron-down faq-toggle"></i>
                                </div>
                                <div class="faq-answer">
                                    <p>Üye olmak size şu avantajları sağlar:</p>
                                    <ul>
                                        <li><strong>Favori Emlaklar:</strong> Beğendiğiniz emlakları kaydedin</li>
                                        <li><strong>Arama Geçmişi:</strong> Eski aramalarınıza tekrar erişin</li>
                                        <li><strong>Özel Fırsatlar:</strong> Sadece üyelere özel kampanyalar</li>
                                        <li><strong>Hızlı İletişim:</strong> Tek tıkla danışman desteği</li>
                                        <li><strong>Detaylı Raporlar:</strong> Emlak analiz raporlarına erişim</li>
                                        <li><strong>Mobil Uygulama:</strong> Mobil uygulamaya özel özellikler</li>
                                    </ul>
                                    <div class="highlight-tip">
                                        <h5><i class="fas fa-gift"></i> Üyelik Ücretsiz</h5>
                                        <p>Hesap oluşturmak tamamen ücretsizdir ve kişisel verileriniz güvende tutulur.</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- İletişim CTA -->
                        <div class="contact-cta">
                            <h3><i class="fas fa-question-circle"></i> Sorunuza cevap bulamadınız mı?</h3>
                            <p>Uzman ekibimiz size yardımcı olmaya hazır. Hemen iletişime geçin!</p>
                            <div>
                                <a href="contact.php" class="btn">
                                    <i class="fas fa-envelope"></i> İletişim Formu
                                </a>
                                <a href="tel:+902128016058" class="btn">
                                    <i class="fas fa-phone"></i> Hemen Ara
                                </a>
                                <a href="https://wa.me/905302037083" class="btn">
                                    <i class="fab fa-whatsapp"></i> WhatsApp
                                </a>
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
                                    <li><a href="https://wa.me/905302037083"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="https://instagram.com/gokhanaydinligayrimenkul"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="https://linkedin.com/in/gokhanaydinli"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="register.php">Üyelik</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="admin/index.php">Admin Panel</a></li>
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
                                    <li><a href="portfoy.php?category=ticari">Ticari Gayrimenkul</a></li>
                                    <li><a href="portfoy.php?transaction_type=satilik">Konut Satışı</a></li>
                                    <li><a href="portfoy.php?transaction_type=kiralik">Ev Kiralama</a></li>
                                    <li><a href="contact.php">Yatırım Danışmanlığı</a></li>
                                    <li><a href="portfoy.php?category=villa">Villa Satışı</a></li>
                                    <li><a href="portfoy.php?category=ofis">Ofis Kiralama</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="bottom-footer">
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>

        <!-- Scroll to top button -->
        <button class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
            ↑
        </button>

        <!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="loginModalLabel">
                            <i class="fas fa-lock"></i> Giriş Yap
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="loginForm" action="login_process.php" method="POST">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember_me" name="remember_me">
                                <label class="form-check-label" for="remember_me">
                                    Beni hatırla
                                </label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">
                                <i class="fas fa-sign-in-alt"></i> Giriş Yap
                            </button>
                        </form>
                        <div class="text-center mt-3">
                            <p class="mb-2">
                                <a href="forgot_password.php" class="text-decoration-none">Şifremi unuttum</a>
                            </p>
                            <p class="mb-0">
                                Hesabınız yok mu? 
                                <a href="register.php" class="text-decoration-none">Kayıt olun</a>
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- JavaScript -->
        <script src="https://code.jquery.com/jquery-3.6.0.min.js" crossorigin="anonymous"></script>
        <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js" crossorigin="anonymous"></script>
        <script src="https://cdnjs.cloudflare.com/ajax/libs/jquery.lazy/1.7.11/jquery.lazy.min.js" crossorigin="anonymous"></script>

        <script>
        // Preloader'ı gizle - jQuery ile
        $(document).ready(function() {
            $('#preloader').fadeOut('slow', function() {
                $(this).remove();
            });
        });

        // Alternatif olarak, sayfa tamamen yüklendiğinde
        $(window).on('load', function() {
            $('#preloader').fadeOut('slow', function() {
                $(this).remove();
            });
        });

        // Acil preloader gizleme - hemen çalışır
        window.addEventListener('load', function() {
            const preloaders = document.querySelectorAll('#preloader');
            preloaders.forEach(function(preloader) {
                if (preloader) {
                    preloader.style.display = 'none';
                    preloader.remove();
                }
            });
        });

        // Vanilla JavaScript ile preloader gizleme (jQuery yüklenmezse)
        document.addEventListener('DOMContentLoaded', function() {
            const preloader = document.getElementById('preloader');
            if (preloader) {
                setTimeout(() => {
                    preloader.style.opacity = '0';
                    preloader.style.transition = 'opacity 0.5s ease';
                    setTimeout(() => {
                        preloader.style.display = 'none';
                        preloader.remove();
                    }, 500);
                }, 100);
            }
            // Scroll to top button
            window.addEventListener('scroll', function() {
                const scrollTop = document.querySelector('.scroll-top');
                if (window.pageYOffset > 300) {
                    scrollTop.style.display = 'block';
                    scrollTop.style.opacity = '1';
                } else {
                    scrollTop.style.opacity = '0';
                    setTimeout(() => {
                        scrollTop.style.display = 'none';
                    }, 300);
                }
            });

            // Initialize lazy loading
            if (typeof $ !== 'undefined' && $.fn.lazy) {
                $('.lazy-img').lazy();
            }

            // FAQ Search functionality
            const searchInput = document.getElementById('faqSearch');
            const faqItems = document.querySelectorAll('.faq-item');

            searchInput.addEventListener('input', function() {
                const searchTerm = this.value.toLowerCase();
                
                faqItems.forEach(item => {
                    const question = item.querySelector('.faq-question h4').textContent.toLowerCase();
                    const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                    
                    if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                        item.style.display = 'block';
                        // Highlight search term
                        if (searchTerm.length > 2) {
                            highlightSearchTerm(item, searchTerm);
                        }
                    } else {
                        item.style.display = 'none';
                    }
                });

                // Show message if no results
                const visibleItems = Array.from(faqItems).filter(item => item.style.display !== 'none');
                showNoResultsMessage(visibleItems.length === 0 && searchTerm.length > 0);
            });

            // Category filtering
            const categoryButtons = document.querySelectorAll('.category-btn');
            categoryButtons.forEach(button => {
                button.addEventListener('click', function() {
                    const category = this.dataset.category;
                    
                    // Update active button
                    categoryButtons.forEach(btn => btn.classList.remove('active'));
                    this.classList.add('active');
                    
                    // Filter FAQ items
                    faqItems.forEach(item => {
                        if (category === 'all' || item.dataset.category === category) {
                            item.style.display = 'block';
                        } else {
                            item.style.display = 'none';
                        }
                    });

                    // Clear search
                    searchInput.value = '';
                    removeHighlights();
                    hideNoResultsMessage();
                });
            });

            // Smooth scrolling for quick nav links
            document.querySelectorAll('.quick-nav a[href^="#"]').forEach(anchor => {
                anchor.addEventListener('click', function (e) {
                    e.preventDefault();
                    const target = document.querySelector(this.getAttribute('href'));
                    if (target) {
                        target.scrollIntoView({
                            behavior: 'smooth',
                            block: 'start'
                        });
                    }
                });
            });

            // Quick nav active state
            window.addEventListener('scroll', function() {
                const sections = document.querySelectorAll('.faq-section[id]');
                const navLinks = document.querySelectorAll('.quick-nav a');
                
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    if (window.pageYOffset >= sectionTop - 200) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.style.color = '#6b7280';
                    link.style.fontWeight = 'normal';
                    if (link.getAttribute('href') === '#' + current) {
                        link.style.color = '#10b981';
                        link.style.fontWeight = '600';
                    }
                });
            });

            // Animation on scroll
            const observerOptions = {
                threshold: 0.1,
                rootMargin: '0px 0px -50px 0px'
            };

            const observer = new IntersectionObserver(function(entries) {
                entries.forEach(function(entry) {
                    if (entry.isIntersecting) {
                        entry.target.style.opacity = '1';
                        entry.target.style.transform = 'translateY(0)';
                    }
                });
            }, observerOptions);

            // Observe all FAQ sections
            document.querySelectorAll('.faq-section').forEach(function(section) {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });

            // Login form handling
            const loginForm = document.getElementById('loginForm');
            if (loginForm) {
                loginForm.addEventListener('submit', function(e) {
                    e.preventDefault();
                    
                    const formData = new FormData(this);
                    const submitBtn = this.querySelector('button[type="submit"]');
                    const originalText = submitBtn.innerHTML;
                    
                    // Show loading state
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Giriş yapılıyor...';
                    submitBtn.disabled = true;
                    
                    fetch('login_process.php', {
                        method: 'POST',
                        body: formData
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // Successful login
                            location.reload();
                        } else {
                            // Show error message
                            alert(data.message || 'Giriş başarısız. Lütfen bilgilerinizi kontrol edin.');
                        }
                    })
                    .catch(error => {
                        console.error('Login error:', error);
                        alert('Bir hata oluştu. Lütfen tekrar deneyin.');
                    })
                    .finally(() => {
                        // Reset button state
                        submitBtn.innerHTML = originalText;
                        submitBtn.disabled = false;
                    });
                });
            }
        });

        // FAQ Toggle Function
        function toggleFaq(element) {
            const faqItem = element.closest('.faq-item');
            const isActive = faqItem.classList.contains('active');
            
            // Close all other FAQ items
            document.querySelectorAll('.faq-item.active').forEach(item => {
                if (item !== faqItem) {
                    item.classList.remove('active');
                }
            });
            
            // Toggle current item
            if (isActive) {
                faqItem.classList.remove('active');
            } else {
                faqItem.classList.add('active');
            }
        }

        // Search highlighting function
        function highlightSearchTerm(item, term) {
            removeHighlights();
            
            const question = item.querySelector('.faq-question h4');
            const answer = item.querySelector('.faq-answer p');
            
            if (question) {
                const questionText = question.textContent;
                const regex = new RegExp(`(${term})`, 'gi');
                question.innerHTML = questionText.replace(regex, '<mark style="background: #fef3c7; padding: 2px 4px; border-radius: 3px;">$1</mark>');
            }
        }

        function removeHighlights() {
            document.querySelectorAll('mark').forEach(mark => {
                mark.outerHTML = mark.innerHTML;
            });
        }

        function showNoResultsMessage(show) {
            let noResultsMsg = document.getElementById('noResultsMessage');
            
            if (show && !noResultsMsg) {
                noResultsMsg = document.createElement('div');
                noResultsMsg.id = 'noResultsMessage';
                noResultsMsg.style.cssText = `
                    text-align: center;
                    padding: 40px;
                    color: #6b7280;
                    font-size: 1.1rem;
                    background: #f9fafb;
                    border-radius: 12px;
                    margin: 20px 0;
                `;
                noResultsMsg.innerHTML = `
                    <i class="fas fa-search" style="font-size: 2rem; margin-bottom: 15px; color: #d1d5db;"></i>
                    <h4 style="color: #374151; margin-bottom: 10px;">Aradığınız soru bulunamadı</h4>
                    <p>Farklı kelimeler deneyebilir veya bizimle doğrudan iletişime geçebilirsiniz.</p>
                    <a href="contact.php" class="btn" style="background: #10b981; color: white; padding: 10px 20px; border-radius: 8px; text-decoration: none; margin-top: 15px; display: inline-block;">Soru Sorun</a>
                `;
                document.querySelector('.faq-container').appendChild(noResultsMsg);
            } else if (!show && noResultsMsg) {
                hideNoResultsMessage();
            }
        }

        function hideNoResultsMessage() {
            const noResultsMsg = document.getElementById('noResultsMessage');
            if (noResultsMsg) {
                noResultsMsg.remove();
            }
        }

        // Keyboard shortcuts
        document.addEventListener('keydown', function(e) {
            // Ctrl/Cmd + K for search focus
            if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
                e.preventDefault();
                document.getElementById('faqSearch').focus();
            }
            
            // Escape to clear search
            if (e.key === 'Escape') {
                const searchInput = document.getElementById('faqSearch');
                if (searchInput.value) {
                    searchInput.value = '';
                    searchInput.dispatchEvent(new Event('input'));
                }
            }
        });

        // Add search shortcut hint
        document.getElementById('faqSearch').placeholder = 'Sorunuzu aramak için yazın... (Ctrl+K)';

        // Mobile responsiveness improvements
        function handleMobileView() {
            const isMobile = window.innerWidth <= 768;
            const quickNav = document.querySelector('.quick-nav');
            
            if (isMobile && quickNav) {
                quickNav.style.position = 'relative';
                quickNav.style.top = '0';
            } else if (quickNav) {
                quickNav.style.position = 'sticky';
                quickNav.style.top = '100px';
            }
        }

        // Handle window resize
        window.addEventListener('resize', handleMobileView);
        
        // Initial mobile check
        handleMobileView();

        // Performance optimization - debounce search
        function debounce(func, wait) {
            let timeout;
            return function executedFunction(...args) {
                const later = () => {
                    clearTimeout(timeout);
                    func(...args);
                };
                clearTimeout(timeout);
                timeout = setTimeout(later, wait);
            };
        }

        // Apply debounce to search
        const debouncedSearch = debounce(function(searchTerm) {
            const faqItems = document.querySelectorAll('.faq-item');
            
            faqItems.forEach(item => {
                const question = item.querySelector('.faq-question h4').textContent.toLowerCase();
                const answer = item.querySelector('.faq-answer').textContent.toLowerCase();
                
                if (question.includes(searchTerm) || answer.includes(searchTerm)) {
                    item.style.display = 'block';
                    if (searchTerm.length > 2) {
                        highlightSearchTerm(item, searchTerm);
                    }
                } else {
                    item.style.display = 'none';
                }
            });

            const visibleItems = Array.from(faqItems).filter(item => item.style.display !== 'none');
            showNoResultsMessage(visibleItems.length === 0 && searchTerm.length > 0);
        }, 300);

        // Update search input event listener to use debounced function
        document.getElementById('faqSearch').addEventListener('input', function() {
            const searchTerm = this.value.toLowerCase();
            debouncedSearch(searchTerm);
        });

        // Analytics tracking for FAQ interactions
        function trackFAQInteraction(action, category, question) {
            // Google Analytics tracking (if implemented)
            if (typeof gtag !== 'undefined') {
                gtag('event', action, {
                    event_category: 'FAQ',
                    event_label: category + ' - ' + question
                });
            }
        }

        // Add analytics to FAQ toggles
        document.querySelectorAll('.faq-question').forEach(question => {
            question.addEventListener('click', function() {
                const questionText = this.querySelector('h4').textContent;
                const category = this.closest('.faq-section').getAttribute('data-category') || 'general';
                trackFAQInteraction('faq_toggle', category, questionText);
            });
        });

        // Add analytics to search
        document.getElementById('faqSearch').addEventListener('keypress', function(e) {
            if (e.key === 'Enter' && this.value.length > 2) {
                trackFAQInteraction('faq_search', 'search', this.value);
            }
        });

        // Add print functionality
        function printFAQ() {
            window.print();
        }

        // Add print button if needed (can be added to the header)
        document.addEventListener('keydown', function(e) {
            if ((e.ctrlKey || e.metaKey) && e.key === 'p') {
                e.preventDefault();
                printFAQ();
            }
        });
        </script>

        <!-- Structured Data for SEO -->
        <script type="application/ld+json">
        {
            "@context": "https://schema.org",
            "@type": "FAQPage",
            "mainEntity": [
                {
                    "@type": "Question",
                    "name": "Emlak alım satımında hangi belgeler gereklidir?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Emlak alım satımında tapu senedi, kimlik belgeleri, ikametgah belgesi, vergi levhası, kat irtifakı/kat mülkiyeti ve imar durumu belgesi gereklidir."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Kira sözleşmesi yaparken nelere dikkat etmeliyim?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Kira sözleşmesinde kira bedeli, depozito, süre, artış oranı, sorumluluklar ve kullanım amacı mutlaka belirtilmelidir."
                    }
                },
                {
                    "@type": "Question",
                    "name": "Gayrimenkul yatırımında getiri oranı nasıl hesaplanır?",
                    "acceptedAnswer": {
                        "@type": "Answer",
                        "text": "Gayrimenkul getiri oranı brüt getiri (Yıllık Kira Geliri / Emlak Değeri) x 100 veya net getiri (Net Kira Geliri / Toplam Yatırım) x 100 formülü ile hesaplanır."
                    }
                }
            ]
        }
        </script>
    </div>
</body>
</html>