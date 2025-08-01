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
    <meta name="keywords" content="Çerez Politikası, Cookie Policy, KVKK, Gökhan Aydınlı Gayrimenkul">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul web sitesi çerez (cookie) kullanım politikası. KVKK uyumlu çerez yönetimi.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Çerez Politikası - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/cookie-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Çerez Politikası - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Cookie Policy Stilleri */
        .cookie-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            margin: 40px 0;
            border: 1px solid #f0f2f5;
        }

        .cookie-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f8f9fa;
        }

        .cookie-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #ff6b6b 0%, #ffa726 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .cookie-header .cookie-icon {
            font-size: 4rem;
            color: #ffa726;
            margin-bottom: 20px;
            animation: bounce 2s infinite;
        }

        @keyframes bounce {
            0%, 20%, 50%, 80%, 100% {
                transform: translateY(0);
            }
            40% {
                transform: translateY(-10px);
            }
            60% {
                transform: translateY(-5px);
            }
        }

        .cookie-header p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .last-updated {
            background: #fff3e0;
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
            color: #e65100;
            font-weight: 500;
            border: 2px solid #ffcc80;
        }

        .cookie-section {
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 15px;
            background: #fafbfc;
            border-left: 4px solid #ffa726;
            position: relative;
        }

        .cookie-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .cookie-section h2 i {
            margin-right: 15px;
            color: #ffa726;
            font-size: 1.5rem;
        }

        .cookie-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
            margin: 25px 0 15px 0;
        }

        .cookie-section p {
            font-size: 1rem;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 15px;
            text-align: justify;
        }

        .cookie-section ul {
            margin: 15px 0;
            padding-left: 25px;
        }

        .cookie-section li {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 8px;
        }

        /* Cookie Types Table */
        .cookie-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin: 25px 0;
        }

        .cookie-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .cookie-table th {
            background: linear-gradient(135deg, #ffa726 0%, #ff9800 100%);
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: left;
        }

        .cookie-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: top;
        }

        .cookie-table tr:nth-child(even) {
            background: #fafbfc;
        }

        .cookie-table tr:hover {
            background: #fff3e0;
            transition: all 0.3s ease;
        }

        .cookie-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .cookie-essential {
            background: #e8f5e8;
            color: #2e7d32;
            border: 1px solid #c8e6c9;
        }

        .cookie-functional {
            background: #e3f2fd;
            color: #1565c0;
            border: 1px solid #bbdefb;
        }

        .cookie-analytics {
            background: #fff3e0;
            color: #ef6c00;
            border: 1px solid #ffcc80;
        }

        .cookie-marketing {
            background: #fce4ec;
            color: #c2185b;
            border: 1px solid #f8bbd9;
        }

        .highlight-box {
            background: linear-gradient(135deg, #ffa72610 0%, #ff980010 100%);
            border: 1px solid #ffa72630;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .highlight-box h4 {
            color: #ff9800;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .consent-management {
            background: #ffa726;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }

        .consent-management h3 {
            color: white;
            margin-bottom: 20px;
        }

        .consent-management .btn {
            background: white;
            color: #ffa726;
            border: none;
            padding: 12px 25px;
            border-radius: 25px;
            font-weight: 600;
            margin: 5px;
            transition: all 0.3s ease;
        }

        .consent-management .btn:hover {
            background: #f5f5f5;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(0, 0, 0, 0.2);
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #ffa726;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #ff9800;
            transform: translateY(-2px);
        }

        /* Cookie Banner (Demo) */
        .cookie-banner-demo {
            position: fixed;
            bottom: 0;
            left: 0;
            right: 0;
            background: linear-gradient(135deg, #2d3748 0%, #1a202c 100%);
            color: white;
            padding: 20px;
            box-shadow: 0 -5px 20px rgba(0, 0, 0, 0.2);
            z-index: 1000;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .cookie-banner-demo.show {
            transform: translateY(0);
        }

        .cookie-banner-demo .container {
            display: flex;
            align-items: center;
            justify-content: space-between;
            flex-wrap: wrap;
        }

        .cookie-banner-demo p {
            margin: 0;
            flex: 1;
            min-width: 300px;
        }

        .cookie-banner-demo .btn-group {
            margin-left: 20px;
        }

        .cookie-banner-demo .btn {
            margin: 0 5px;
            padding: 8px 20px;
            border-radius: 20px;
            border: none;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .btn-accept {
            background: #ffa726;
            color: white;
        }

        .btn-decline {
            background: transparent;
            color: white;
            border: 1px solid white;
        }

        .btn-settings {
            background: #4a5568;
            color: white;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .cookie-container {
                padding: 30px 20px;
                margin: 20px 0;
            }
            
            .cookie-header h1 {
                font-size: 2rem;
            }
            
            .cookie-section {
                padding: 20px;
            }
            
            .cookie-section h2 {
                font-size: 1.5rem;
            }

            .cookie-table {
                overflow-x: auto;
            }

            .cookie-banner-demo .container {
                flex-direction: column;
                text-align: center;
            }

            .cookie-banner-demo .btn-group {
                margin-left: 0;
                margin-top: 15px;
            }
        }

        @media (max-width: 576px) {
            .cookie-container {
                padding: 20px 15px;
            }
            
            .cookie-header h1 {
                font-size: 1.7rem;
            }
            
            .cookie-section h2 {
                font-size: 1.3rem;
                flex-direction: column;
                text-align: center;
            }
            
            .cookie-section h2 i {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .cookie-table th,
            .cookie-table td {
                padding: 10px 8px;
                font-size: 0.9rem;
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
                <h3 class="mb-35 xl-mb-20 pt-15" style="color: #6c757d !important;">Çerez Politikası</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>Çerez Politikası</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Cookie Policy Content -->
        <div class="container my-5">
            <div class="cookie-container">
                
                <!-- Header -->
                <div class="cookie-header">
                    <div class="cookie-icon">
                        <i class="fas fa-cookie-bite"></i>
                    </div>
                    <h1>Çerez Politikası</h1>
                    <p>Web sitemizde kullanılan çerezler (cookies) hakkında detaylı bilgi</p>
                    <div class="last-updated">
                        <i class="fas fa-calendar-alt"></i> Son Güncelleme: <?php echo date('d.m.Y'); ?>
                    </div>
                </div>

                <!-- Çerez Nedir -->
                <div class="cookie-section">
                    <h2><i class="fas fa-question-circle"></i> Çerez Nedir?</h2>
                    <p>Çerezler (cookies), web sitemizi ziyaret ettiğinizde tarayıcınızda saklanan küçük metin dosyalarıdır. Bu dosyalar, web sitemizin daha iyi çalışmasını sağlar ve size özelleştirilmiş bir deneyim sunar.</p>
                    
                    <div class="highlight-box">
                        <h4><i class="fas fa-info-circle"></i> Neden Çerez Kullanıyoruz?</h4>
                        <p>Çerezler, web sitemizin işlevselliğini artırmak, kullanım analitikleri elde etmek ve size daha iyi bir gayrimenkul arama deneyimi sunmak için kullanılır.</p>
                    </div>
                </div>

                <!-- Çerez Türleri -->
                <div class="cookie-section">
                    <h2><i class="fas fa-layer-group"></i> Kullandığımız Çerez Türleri</h2>
                    
                    <div class="cookie-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>Çerez Türü</th>
                                    <th>Açıklama</th>
                                    <th>Saklama Süresi</th>
                                    <th>Amacı</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td>
                                        <span class="cookie-type cookie-essential">Zorunlu Çerezler</span>
                                    </td>
                                    <td>Web sitesinin temel işlevlerini sağlar</td>
                                    <td>Oturum boyunca</td>
                                    <td>Güvenlik, giriş işlemleri, sepet yönetimi</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="cookie-type cookie-functional">İşlevsel Çerezler</span>
                                    </td>
                                    <td>Kullanıcı tercihlerini hatırlar</td>
                                    <td>30 gün - 1 yıl</td>
                                    <td>Dil seçimi, emlak filtreleri, favori listeler</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="cookie-type cookie-analytics">Analitik Çerezler</span>
                                    </td>
                                    <td>Site kullanım istatistikleri toplar</td>
                                    <td>2 yıl</td>
                                    <td>Google Analytics, kullanıcı davranış analizi</td>
                                </tr>
                                <tr>
                                    <td>
                                        <span class="cookie-type cookie-marketing">Pazarlama Çerezler</span>
                                    </td>
                                    <td>Kişiselleştirilmiş reklamlar sunar</td>
                                    <td>90 gün - 2 yıl</td>
                                    <td>Hedefli gayrimenkul önerileri, remarketing</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Detaylı Çerez Listesi -->
                <div class="cookie-section">
                    <h2><i class="fas fa-list-ul"></i> Detaylı Çerez Listesi</h2>
                    
                    <h3>🔒 Zorunlu Çerezler</h3>
                    <ul>
                        <li><strong>PHPSESSID:</strong> Kullanıcı oturumu yönetimi</li>
                        <li><strong>csrf_token:</strong> Güvenlik koruması</li>
                        <li><strong>cookie_consent:</strong> Çerez onay durumu</li>
                        <li><strong>remember_token:</strong> "Beni hatırla" işlevi</li>
                    </ul>

                    <h3>⚙️ İşlevsel Çerezler</h3>
                    <ul>
                        <li><strong>user_preferences:</strong> Kullanıcı tercih ayarları</li>
                        <li><strong>search_filters:</strong> Emlak arama filtreleri</li>
                        <li><strong>favorites_list:</strong> Favori emlaklar listesi</li>
                        <li><strong>language_preference:</strong> Dil tercihi</li>
                    </ul>

                    <h3>📊 Analitik Çerezler</h3>
                    <ul>
                        <li><strong>_ga:</strong> Google Analytics ana çerez</li>
                        <li><strong>_ga_*:</strong> Google Analytics 4 çerezleri</li>
                        <li><strong>_gid:</strong> Google Analytics kimlik çerezi</li>
                        <li><strong>_gat:</strong> Google Analytics trafik çerezi</li>
                    </ul>

                    <h3>🎯 Pazarlama Çerezleri</h3>
                    <ul>
                        <li><strong>_fbp:</strong> Facebook Pixel çerezi</li>
                        <li><strong>fr:</strong> Facebook remarketing</li>
                        <li><strong>google_ads:</strong> Google Ads takip çerezleri</li>
                        <li><strong>property_recommendations:</strong> Emlak önerileri</li>
                    </ul>
                </div>

                <!-- Üçüncü Taraf Çerezler -->
                <div class="cookie-section">
                    <h2><i class="fas fa-external-link-alt"></i> Üçüncü Taraf Çerezleri</h2>
                    
                    <p>Web sitemizde aşağıdaki üçüncü taraf hizmetlerin çerezleri kullanılmaktadır:</p>
                    
                    <h3>📈 Google Analytics</h3>
                    <p>Site trafiği ve kullanıcı davranışlarını analiz etmek için Google Analytics kullanıyoruz. Bu servis anonim istatistikler sağlar.</p>
                    
                    <h3>🗺️ Google Maps</h3>
                    <p>Emlak lokasyonlarını göstermek için Google Maps entegrasyonu kullanıyoruz.</p>
                    
                    <h3>💬 Facebook Pixel</h3>
                    <p>Sosyal medya reklamlarının etkinliğini ölçmek için Facebook Pixel kullanıyoruz.</p>
                    
                    <h3>📞 WhatsApp Business</h3>
                    <p>Müşteri iletişimi için WhatsApp Business widget'ı kullanıyoruz.</p>

                    <div class="highlight-box">
                        <h4><i class="fas fa-shield-alt"></i> Gizlilik Garantisi</h4>
                        <p>Üçüncü taraf çerezler, kişisel verilerinizi tanımlanamayacak şekilde işler ve gizlilik politikalarına tabidir.</p>
                    </div>
                </div>

                <!-- Çerez Yönetimi -->
                <div class="cookie-section">
                    <h2><i class="fas fa-cog"></i> Çerez Ayarlarınızı Yönetme</h2>
                    
                    <h3>🌐 Tarayıcı Ayarları</h3>
                    <p>Çoğu web tarayıcısı çerezleri otomatik olarak kabul eder, ancak çerez ayarlarınızı değiştirebilirsiniz:</p>
                    
                    <ul>
                        <li><strong>Chrome:</strong> Ayarlar > Gizlilik ve güvenlik > Çerezler</li>
                        <li><strong>Firefox:</strong> Ayarlar > Gizlilik ve Güvenlik > Çerezler</li>
                        <li><strong>Safari:</strong> Tercihler > Gizlilik > Çerezler</li>
                        <li><strong>Edge:</strong> Ayarlar > Çerezler ve site izinleri</li>
                    </ul>

                    <h3>🎛️ Çerez Tercihleri</h3>
                    <p>Aşağıdaki butona tıklayarak çerez tercihlerinizi istediğiniz zaman değiştirebilirsiniz:</p>
                </div>

                <!-- KVKK Uyumluluk -->
                <div class="cookie-section">
                    <h2><i class="fas fa-balance-scale"></i> KVKK Uyumluluk</h2>
                    
                    <p>6698 sayılı Kişisel Verilerin Korunması Kanunu kapsamında:</p>
                    
                    <ul>
                        <li>Çerez kullanımı için açık rızanız alınmaktadır</li>
                        <li>Hangi çerezlerin ne amaçla kullanıldığı şeffaf şekilde belirtilmektedir</li>
                        <li>İstediğiniz zaman çerez tercihlerinizi değiştirebilirsiniz</li>
                        <li>Kişisel verileriniz üçüncü taraflarla paylaşılmamaktadır</li>
                        <li>Veri minimizasyonu ilkesi gereği sadece gerekli çerezler kullanılmaktadır</li>
                    </ul>

                    <div class="highlight-box">
                        <h4><i class="fas fa-user-shield"></i> Haklarınız</h4>
                        <p>KVKK kapsamında çerezlerle ilgili bilgi talep etme, düzeltme, silme ve işleme itiraz etme haklarınız bulunmaktadır.</p>
                    </div>
                </div>

                <!-- İletişim ve Yardım -->
                <div class="cookie-section">
                    <h2><i class="fas fa-headset"></i> Yardım ve Destek</h2>
                    
                    <p>Çerez politikamız hakkında sorularınız varsa veya çerez ayarlarınızla ilgili yardıma ihtiyacınız varsa:</p>
                    
                    <ul>
                        <li><strong>E-posta:</strong> <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a></li>
                        <li><strong>Telefon:</strong> <a href="tel:+902128016058">+90 (212) 801 60 58</a></li>
                        <li><strong>WhatsApp:</strong> <a href="tel:+905302037083">+90 (530) 203 70 83</a></li>
                        <li><strong>Adres:</strong> Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</li>
                    </ul>

                    <div class="highlight-box">
                        <h4><i class="fas fa-clock"></i> Destek Saatleri</h4>
                        <p>Pazartesi - Cuma: 09:00 - 19:00<br>Cumartesi - Pazar: 09:00 - 14:00</p>
                    </div>
                </div>

                <!-- Çerez Yönetim Merkezi -->
                <div class="consent-management">
                    <h3><i class="fas fa-sliders-h"></i> Çerez Tercih Merkezi</h3>
                    <p>Çerez tercihlerinizi buradan yönetebilirsiniz. Zorunlu çerezler web sitesinin çalışması için gereklidir ve devre dışı bırakılamaz.</p>
                    <div class="btn-group">
                        <button class="btn" onclick="openCookieSettings()">
                            <i class="fas fa-cog"></i> Çerez Ayarları
                        </button>
                        <button class="btn" onclick="acceptAllCookies()">
                            <i class="fas fa-check"></i> Tümünü Kabul Et
                        </button>
                        <button class="btn" onclick="rejectOptionalCookies()">
                            <i class="fas fa-times"></i> İsteğe Bağlı Çerezleri Reddet
                        </button>
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
                    <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
        </div>

        <!-- Demo Cookie Banner -->
        <div class="cookie-banner-demo" id="cookieBanner">
            <div class="container">
                <p>
                    <i class="fas fa-cookie-bite"></i>
                    Bu web sitesi, size daha iyi bir deneyim sunmak için çerezleri kullanır. Çerez kullanımımız hakkında detaylı bilgi için 
                    <a href="cookies.php" style="color: #ffa726; text-decoration: underline;">Çerez Politikası</a>'mızı inceleyebilirsiniz.
                </p>
                <div class="btn-group">
                    <button class="btn btn-accept" onclick="acceptCookies()">Kabul Et</button>
                    <button class="btn btn-settings" onclick="openCookieSettings()">Ayarlar</button>
                    <button class="btn btn-decline" onclick="declineCookies()">Reddet</button>
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
        // Cookie Banner Management
        document.addEventListener('DOMContentLoaded', function() {
            // Check if user has already made a cookie choice
            const cookieConsent = localStorage.getItem('cookieConsent');
            
            if (!cookieConsent) {
                // Show cookie banner after 2 seconds
                setTimeout(() => {
                    document.getElementById('cookieBanner').classList.add('show');
                }, 2000);
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

            // Observe all cookie sections
            document.querySelectorAll('.cookie-section').forEach(function(section) {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });
        });

        // Cookie Banner Functions
        function acceptCookies() {
            localStorage.setItem('cookieConsent', 'accepted');
            localStorage.setItem('cookieSettings', JSON.stringify({
                essential: true,
                functional: true,
                analytics: true,
                marketing: true
            }));
            hideCookieBanner();
            
            // Show success message
            showNotification('Çerez tercihleri kaydedildi! Tüm çerezler kabul edildi.', 'success');
        }

        function declineCookies() {
            localStorage.setItem('cookieConsent', 'declined');
            localStorage.setItem('cookieSettings', JSON.stringify({
                essential: true,
                functional: false,
                analytics: false,
                marketing: false
            }));
            hideCookieBanner();
            
            // Show info message
            showNotification('Sadece zorunlu çerezler kabul edildi. Bu web sitesinin bazı özellikleri çalışmayabilir.', 'info');
        }

        function openCookieSettings() {
            // Cookie settings modal açma işlevi
            // Bu gerçek uygulamada bir modal açar
            const settings = {
                essential: true, // Her zaman true
                functional: confirm('İşlevsel çerezleri kabul ediyor musunuz? (Emlak filtreleri, dil seçimi vb.)'),
                analytics: confirm('Analitik çerezleri kabul ediyor musunuz? (Google Analytics, site kullanım istatistikleri)'),
                marketing: confirm('Pazarlama çerezleri kabul ediyor musunuz? (Kişiselleştirilmiş emlak önerileri)')
            };
            
            localStorage.setItem('cookieConsent', 'customized');
            localStorage.setItem('cookieSettings', JSON.stringify(settings));
            hideCookieBanner();
            
            showNotification('Özel çerez ayarlarınız kaydedildi!', 'success');
        }

        function acceptAllCookies() {
            acceptCookies();
        }

        function rejectOptionalCookies() {
            declineCookies();
        }

        function hideCookieBanner() {
            document.getElementById('cookieBanner').classList.remove('show');
        }

        function showNotification(message, type) {
            // Basit bildirim sistemi
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#4caf50' : type === 'info' ? '#2196f3' : '#ff9800'};
                color: white;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 4px 15px rgba(0,0,0,0.2);
                z-index: 10000;
                font-weight: 500;
                max-width: 400px;
                animation: slideInRight 0.3s ease;
            `;
            notification.textContent = message;
            
            document.body.appendChild(notification);
            
            // Auto remove after 5 seconds
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    document.body.removeChild(notification);
                }, 300);
            }, 5000);
        }

        // CSS animasyonları için style ekle
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideInRight {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOutRight {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);
        
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
    </div>
</body>
</html>