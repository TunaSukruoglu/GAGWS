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
    <meta name="keywords" content="Gizlilik Politikası, KVKK, Kişisel Veri, Gökhan Aydınlı Gayrimenkul">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul kişisel veri işleme ve gizlilik politikası. KVKK uyumlu veri koruma prensipleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Gizlilik Politikası - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/privacy-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Gizlilik Politikası - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Privacy Policy Stilleri */
        .privacy-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            margin: 40px 0;
            border: 1px solid #f0f2f5;
        }

        .privacy-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f8f9fa;
        }

        .privacy-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #4c51bf 0%, #667eea 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .privacy-header .privacy-icon {
            font-size: 4rem;
            color: #4c51bf;
            margin-bottom: 20px;
            animation: shield-pulse 3s infinite;
        }

        @keyframes shield-pulse {
            0%, 100% {
                transform: scale(1);
                opacity: 1;
            }
            50% {
                transform: scale(1.05);
                opacity: 0.8;
            }
        }

        .privacy-header p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .last-updated {
            background: #eef2ff;
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
            color: #3730a3;
            font-weight: 500;
            border: 2px solid #c7d2fe;
        }

        .privacy-section {
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 15px;
            background: #fafbfc;
            border-left: 4px solid #4c51bf;
            position: relative;
        }

        .privacy-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .privacy-section h2 i {
            margin-right: 15px;
            color: #4c51bf;
            font-size: 1.5rem;
        }

        .privacy-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
            margin: 25px 0 15px 0;
        }

        .privacy-section p {
            font-size: 1rem;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 15px;
            text-align: justify;
        }

        .privacy-section ul {
            margin: 15px 0;
            padding-left: 25px;
        }

        .privacy-section li {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 8px;
        }

        /* KVKK Haklar Tablosu */
        .rights-table {
            background: white;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0, 0, 0, 0.05);
            margin: 25px 0;
        }

        .rights-table table {
            width: 100%;
            border-collapse: collapse;
        }

        .rights-table th {
            background: linear-gradient(135deg, #4c51bf 0%, #667eea 100%);
            color: white;
            padding: 15px;
            font-weight: 600;
            text-align: left;
        }

        .rights-table td {
            padding: 15px;
            border-bottom: 1px solid #f0f2f5;
            vertical-align: top;
        }

        .rights-table tr:nth-child(even) {
            background: #fafbfc;
        }

        .rights-table tr:hover {
            background: #eef2ff;
            transition: all 0.3s ease;
        }

        .data-type {
            display: inline-block;
            padding: 5px 12px;
            border-radius: 20px;
            font-size: 0.85rem;
            font-weight: 500;
            margin-bottom: 5px;
        }

        .data-personal {
            background: #fee2e2;
            color: #dc2626;
            border: 1px solid #fecaca;
        }

        .data-contact {
            background: #dbeafe;
            color: #2563eb;
            border: 1px solid #bfdbfe;
        }

        .data-technical {
            background: #f3e8ff;
            color: #7c3aed;
            border: 1px solid #ddd6fe;
        }

        .data-property {
            background: #d1fae5;
            color: #059669;
            border: 1px solid #a7f3d0;
        }

        .highlight-box {
            background: linear-gradient(135deg, #4c51bf10 0%, #667eea10 100%);
            border: 1px solid #4c51bf30;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .highlight-box h4 {
            color: #4c51bf;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .warning-box {
            background: linear-gradient(135deg, #fbbf2410 0%, #f5940510 100%);
            border: 1px solid #fbbf2430;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .warning-box h4 {
            color: #d97706;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .contact-info {
            background: #4c51bf;
            color: white;
            padding: 30px;
            border-radius: 15px;
            margin-top: 40px;
            text-align: center;
        }

        .contact-info h3 {
            color: white;
            margin-bottom: 20px;
        }

        .contact-info a {
            color: #e0e7ff;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info a:hover {
            color: white;
            text-decoration: underline;
        }

        .consent-status {
            background: #f0fff4;
            border: 2px solid #34d399;
            border-radius: 12px;
            padding: 20px;
            margin: 25px 0;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .consent-status .status-text {
            display: flex;
            align-items: center;
            color: #059669;
            font-weight: 600;
        }

        .consent-status .status-text i {
            margin-right: 10px;
            font-size: 1.2rem;
        }

        .consent-status .manage-btn {
            background: #059669;
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 20px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .consent-status .manage-btn:hover {
            background: #047857;
            transform: translateY(-1px);
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #4c51bf;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #3730a3;
            transform: translateY(-2px);
        }

        /* Quick Links Navigation */
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
            color: #4c51bf;
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
        }

        .quick-nav a:hover {
            color: #4c51bf;
            padding-left: 10px;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .privacy-container {
                padding: 30px 20px;
                margin: 20px 0;
            }
            
            .privacy-header h1 {
                font-size: 2rem;
            }
            
            .privacy-section {
                padding: 20px;
            }
            
            .privacy-section h2 {
                font-size: 1.5rem;
            }

            .rights-table {
                overflow-x: auto;
            }

            .consent-status {
                flex-direction: column;
                text-align: center;
            }

            .consent-status .manage-btn {
                margin-top: 15px;
            }

            .quick-nav {
                position: relative;
                top: 0;
            }
        }

        @media (max-width: 576px) {
            .privacy-container {
                padding: 20px 15px;
            }
            
            .privacy-header h1 {
                font-size: 1.7rem;
            }
            
            .privacy-section h2 {
                font-size: 1.3rem;
                flex-direction: column;
                text-align: center;
            }
            
            .privacy-section h2 i {
                margin-right: 0;
                margin-bottom: 10px;
            }

            .rights-table th,
            .rights-table td {
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
                <h3 class="mb-35 xl-mb-20 pt-15" style="color: #6c757d !important;">Gizlilik Politikası</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>Gizlilik Politikası</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Privacy Policy Content -->
        <div class="container my-5">
            <div class="row">
                <div class="col-lg-3">
                    <!-- Quick Navigation -->
                    <div class="quick-nav d-none d-lg-block">
                        <h4><i class="fas fa-list"></i> Hızlı Erişim</h4>
                        <ul>
                            <li><a href="#veri-sorumlusu">Veri Sorumlusu</a></li>
                            <li><a href="#toplanan-veriler">Toplanan Veriler</a></li>
                            <li><a href="#toplama-amaci">Toplama Amacı</a></li>
                            <li><a href="#veri-paylasimi">Veri Paylaşımı</a></li>
                            <li><a href="#veri-guvenligi">Veri Güvenliği</a></li>
                            <li><a href="#kvkk-haklariniz">KVKK Haklarınız</a></li>
                            <li><a href="#cerezler">Çerezler</a></li>
                            <li><a href="#iletisim">İletişim</a></li>
                        </ul>
                    </div>
                </div>
                
                <div class="col-lg-9">
                    <div class="privacy-container">
                        
                        <!-- Header -->
                        <div class="privacy-header">
                            <div class="privacy-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <h1>Gizlilik Politikası</h1>
                            <p>Kişisel verilerinizin korunması bizim için önceliktir. KVKK uyumlu veri işleme politikamız.</p>
                            <div class="last-updated">
                                <i class="fas fa-calendar-alt"></i> Son Güncelleme: <?php echo date('d.m.Y'); ?>
                            </div>
                        </div>

                        <!-- Onay Durumu -->
                        <div class="consent-status">
                            <div class="status-text">
                                <i class="fas fa-check-circle"></i>
                                Gizlilik ayarlarınız aktif ve güncel
                            </div>
                            <button class="manage-btn" onclick="openPrivacySettings()">
                                Ayarları Yönet
                            </button>
                        </div>

                        <!-- Veri Sorumlusu -->
                        <div class="privacy-section" id="veri-sorumlusu">
                            <h2><i class="fas fa-building"></i> Veri Sorumlusu</h2>
                            <p><strong>Gökhan Aydınlı Gayrimenkul</strong> olarak, 6698 sayılı Kişisel Verilerin Korunması Kanunu ("KVKK") kapsamında veri sorumlusu sıfatıyla hareket etmekteyiz.</p>
                            
                            <div class="highlight-box">
                                <h4><i class="fas fa-info-circle"></i> İletişim Bilgilerimiz</h4>
                                <p>
                                    <strong>Unvan:</strong> Gökhan Aydınlı Gayrimenkul<br>
                                    <strong>Adres:</strong> Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul<br>
                                    <strong>E-posta:</strong> <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a><br>
                                    <strong>Telefon:</strong> <a href="tel:+902128016058">+90 (212) 801 60 58</a>
                                </p>
                            </div>
                        </div>

                        <!-- Toplanan Kişisel Veriler -->
                        <div class="privacy-section" id="toplanan-veriler">
                            <h2><i class="fas fa-database"></i> Toplanan Kişisel Veriler</h2>
                            
                            <p>Gayrimenkul hizmetlerimizi sunabilmek için aşağıdaki kategorilerde kişisel verilerinizi işlemekteyiz:</p>

                            <div class="rights-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Veri Kategorisi</th>
                                            <th>Veri Türleri</th>
                                            <th>Toplama Yöntemi</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td>
                                                <span class="data-type data-personal">Kimlik Bilgileri</span>
                                            </td>
                                            <td>Ad, soyad, T.C. kimlik numarası, doğum tarihi</td>
                                            <td>Form doldurma, sözleşme imzalama</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="data-type data-contact">İletişim Bilgileri</span>
                                            </td>
                                            <td>E-posta, telefon, adres bilgileri</td>
                                            <td>Kayıt formu, iletişim formu</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="data-type data-technical">Teknik Veriler</span>
                                            </td>
                                            <td>IP adresi, çerez verileri, cihaz bilgileri</td>
                                            <td>Web sitesi kullanımı, analitik araçlar</td>
                                        </tr>
                                        <tr>
                                            <td>
                                                <span class="data-type data-property">Emlak Tercihleri</span>
                                            </td>
                                            <td>Arama kriterleri, favori emlaklar, bütçe</td>
                                            <td>Platform kullanımı, arama geçmişi</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>

                        <!-- Veri Toplama Amaçları -->
                        <div class="privacy-section" id="toplama-amaci">
                            <h2><i class="fas fa-target"></i> Kişisel Veri İşleme Amaçları</h2>
                            
                            <h3>🏠 Gayrimenkul Hizmetleri</h3>
                            <ul>
                                <li>Emlak alım-satım ve kiralama danışmanlığı</li>
                                <li>Yatırım danışmanlığı hizmetleri</li>
                                <li>Emlak değerleme ve pazar analizi</li>
                                <li>Gayrimenkul yönetim hizmetleri</li>
                            </ul>

                            <h3>📞 İletişim ve Müşteri Hizmetleri</h3>
                            <ul>
                                <li>Müşteri destek hizmetleri sunma</li>
                                <li>Talep ve şikayetlerin değerlendirilmesi</li>
                                <li>Bilgilendirme ve duyuru gönderimi</li>
                                <li>Müşteri memnuniyeti araştırmaları</li>
                            </ul>

                            <h3>⚖️ Yasal Yükümlülükler</h3>
                            <ul>
                                <li>Emlak mevzuatından kaynaklanan yükümlülükler</li>
                                <li>Mali mevzuat gereği saklama zorunlulukları</li>
                                <li>Denetim ve raporlama yükümlülükleri</li>
                                <li>Kara para aklanması önleme tedbirleri</li>
                            </ul>

                            <div class="warning-box">
                                <h4><i class="fas fa-exclamation-triangle"></i> Pazarlama Faaliyetleri</h4>
                                <p>Pazarlama amaçlı veri işleme sadece açık rızanız dahilinde gerçekleştirilir. Bu rızayı istediğiniz zaman geri çekebilirsiniz.</p>
                            </div>
                        </div>

                        <!-- Veri Paylaşımı -->
                        <div class="privacy-section" id="veri-paylasimi">
                            <h2><i class="fas fa-share-alt"></i> Kişisel Veri Paylaşımı</h2>
                            
                            <p>Kişisel verileriniz aşağıdaki durumlarda ve taraflarla paylaşılabilir:</p>

                            <h3>🏢 İş Ortakları</h3>
                            <ul>
                                <li><strong>Emlak Ekspertiz Şirketleri:</strong> Değerleme hizmetleri için</li>
                                <li><strong>Sigorta Şirketleri:</strong> Emlak sigortası işlemleri için</li>
                                <li><strong>Bankalar:</strong> Kredi ve finansman işlemleri için</li>
                                <li><strong>Hukuk Büroları:</strong> Hukuki danışmanlık için</li>
                            </ul>

                            <h3>🔧 Teknik Hizmet Sağlayıcılar</h3>
                            <ul>
                                <li><strong>Hosting Şirketleri:</strong> Web sitesi barındırma</li>
                                <li><strong>E-posta Servisleri:</strong> İletişim yönetimi</li>
                                <li><strong>Analitik Araçlar:</strong> Google Analytics, Facebook Pixel</li>
                                <li><strong>Bulut Depolama:</strong> Veri saklama ve yedekleme</li>
                            </ul>

                            <h3>⚖️ Resmi Kurumlar</h3>
                            <ul>
                                <li><strong>Adalet Bakanlığı:</strong> Hukuki süreçler</li>
                                <li><strong>Maliye Bakanlığı:</strong> Vergi mevzuatı</li>
                                <li><strong>MASAK:</strong> Mali suçlarla mücadele</li>
                                <li><strong>Mahkemeler:</strong> Hukuki talep halinde</li>
                            </ul>

                            <div class="highlight-box">
                                <h4><i class="fas fa-lock"></i> Veri Güvenliği Garantisi</h4>
                                <p>Veri paylaşımı sadece yasal dayanaklar çerçevesinde ve veri güvenliği sözleşmeleri ile korumalı şekilde yapılır.</p>
                            </div>
                        </div>

                        <!-- Veri Güvenliği -->
                        <div class="privacy-section" id="veri-guvenligi">
                            <h2><i class="fas fa-shield-virus"></i> Veri Güvenliği Önlemleri</h2>
                            
                            <p>Kişisel verilerinizin güvenliğini sağlamak için çok katmanlı güvenlik önlemleri uygulamaktayız:</p>

                            <h3>🔐 Teknik Güvenlik Önlemleri</h3>
                            <ul>
                                <li><strong>SSL Şifreleme:</strong> Tüm veri transferleri şifrelenmiştir</li>
                                <li><strong>Firewall Koruması:</strong> Gelişmiş güvenlik duvarı sistemi</li>
                                <li><strong>Anti-Malware:</strong> Sürekli güvenlik taraması</li>
                                <li><strong>Yedekleme Sistemi:</strong> Düzenli veri yedekleme</li>
                                <li><strong>Erişim Kontrolü:</strong> Yetki bazlı erişim sistemi</li>
                            </ul>

                            <h3>👥 İdari Güvenlik Önlemleri</h3>
                            <ul>
                                <li><strong>Personel Eğitimi:</strong> KVKK ve veri güvenliği eğitimleri</li>
                                <li><strong>Gizlilik Sözleşmeleri:</strong> Tüm personel ile imzalanmıştır</li>
                                <li><strong>Erişim Yetkilendirmesi:</strong> İhtiyaç esasına dayalı erişim</li>
                                <li><strong>Denetim ve Kontrol:</strong> Düzenli güvenlik denetimleri</li>
                            </ul>

                            <h3>🏢 Fiziksel Güvenlik Önlemleri</h3>
                            <ul>
                                <li><strong>Güvenli Ofis:</strong> Kamera sistemi ve erişim kontrolü</li>
                                <li><strong>Kilitli Dolaplar:</strong> Fiziksel belge güvenliği</li>
                                <li><strong>Temiz Masa Politikası:</strong> Çalışma alanı güvenliği</li>
                                <li><strong>Güvenli İmha:</strong> Gereksiz belgelerin güvenli imhası</li>
                            </ul>
                        </div>

                        <!-- KVKK Haklarınız -->
                        <div class="privacy-section" id="kvkk-haklariniz">
                            <h2><i class="fas fa-balance-scale"></i> KVKK Kapsamındaki Haklarınız</h2>
                            
                            <p>6698 sayılı KVKK'nın 11. maddesi uyarınca sahip olduğunuz haklar:</p>

                            <div class="rights-table">
                                <table>
                                    <thead>
                                        <tr>
                                            <th>Hakkınız</th>
                                            <th>Açıklama</th>
                                            <th>Nasıl Kullanılır</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr>
                                            <td><strong>Bilgi Talep Etme</strong></td>
                                            <td>Kişisel verilerinizin işlenip işlenmediğini öğrenme</td>
                                            <td>Yazılı başvuru ile</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Bilgilendirme Talep Etme</strong></td>
                                            <td>İşleme amacını ve sonuçlarını öğrenme</td>
                                            <td>E-posta veya dilekçe ile</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Düzeltme Talep Etme</strong></td>
                                            <td>Hatalı verilerin düzeltilmesini isteme</td>
                                            <td>Gerekli belgeler ile</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Silme Talep Etme</strong></td>
                                            <td>Verilerinizin silinmesini isteme</td>
                                            <td>Yazılı başvuru ile</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Aktarım Talep Etme</strong></td>
                                            <td>Verilerinizin başka veri sorumlusuna aktarılması</td>
                                            <td>Teknik olarak mümkünse</td>
                                        </tr>
                                        <tr>
                                            <td><strong>İtiraz Etme</strong></td>
                                            <td>İşlemeye karşı itiraz etme</td>
                                            <td>Haklı gerekçe ile</td>
                                        </tr>
                                        <tr>
                                            <td><strong>Zararın Giderilmesi</strong></td>
                                            <td>KVKK'ya aykırı işlemeden doğan zararın tazmini</td>
                                            <td>Hukuki başvuru ile</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>

                            <div class="warning-box">
                                <h4><i class="fas fa-clock"></i> Başvuru Süreci</h4>
                                <p>Başvurularınız 30 gün içerisinde ücretsiz olarak sonuçlandırılır. Karmaşık başvurular 60 güne kadar uzayabilir.</p>
                            </div>
                        </div>

                        <!-- Çerez Politikası -->
                        <div class="privacy-section" id="cerezler">
                            <h2><i class="fas fa-cookie-bite"></i> Çerezler ve Web Analitikleri</h2>
                            
                            <p>Web sitemizde kullanılan çerezler hakkında detaylı bilgi için <a href="cookies.php" target="_blank">Çerez Politikamızı</a> inceleyebilirsiniz.</p>

                            <h3>🍪 Çerez Türleri</h3>
                            <ul>
                                <li><strong>Zorunlu Çerezler:</strong> Web sitesi işlevselliği için gerekli</li>
                                <li><strong>İşlevsel Çerezler:</strong> Kullanıcı deneyimini iyileştirme</li>
                                <li><strong>Analitik Çerezler:</strong> Site kullanım analizi</li>
                                <li><strong>Pazarlama Çerezleri:</strong> Kişiselleştirilmiş reklamlar</li>
                            </ul>

                            <h3>📊 Web Analitik Araçları</h3>
                            <ul>
                                <li><strong>Google Analytics:</strong> Site trafiği analizi</li>
                                <li><strong>Facebook Pixel:</strong> Sosyal medya analitikleri</li>
                                <li><strong>Hotjar:</strong> Kullanıcı davranış analizi</li>
                                <li><strong>Google Tag Manager:</strong> Etiket yönetimi</li>
                            </ul>

                            <div class="highlight-box">
                                <h4><i class="fas fa-cog"></i> Çerez Yönetimi</h4>
                                <p>Çerez tercihlerinizi istediğiniz zaman <button onclick="openCookieSettings()" style="color: #4c51bf; background: none; border: none; text-decoration: underline;">buradan</button> yönetebilirsiniz.</p>
                            </div>
                        </div>

                        <!-- Veri Saklama Süreleri -->
                        <div class="privacy-section">
                            <h2><i class="fas fa-hourglass-half"></i> Veri Saklama Süreleri</h2>
                            
                            <p>Kişisel verileriniz, işleme amacının gerektirdiği süre kadar saklanır:</p>

                            <h3>📋 Saklama Süreleri</h3>
                            <ul>
                                <li><strong>Müşteri Bilgileri:</strong> Sözleşme sona erdikten sonra 10 yıl</li>
                                <li><strong>Muhasebe Kayıtları:</strong> Vergi Usul Kanunu gereği 5 yıl</li>
                                <li><strong>İletişim Kayıtları:</strong> Talep çözümü sonrası 3 yıl</li>
                                <li><strong>Web Sitesi Logları:</strong> Güvenlik amacıyla 1 yıl</li>
                                <li><strong>Pazarlama Verileri:</strong> Rıza geri çekilene kadar</li>
                            </ul>

                            <h3>🗑️ Veri İmhası</h3>
                            <p>Saklama süresi sona eren veriler güvenli şekilde imha edilir:</p>
                            <ul>
                                <li>Dijital veriler: Güvenli silme algoritmaları</li>
                                <li>Fiziksel belgeler: Güvenli imha yöntemleri</li>
                                <li>Yedek kopyalar: Tüm kopyalar dahil edilir</li>
                                <li>Üçüncü taraflar: İmha talimatı verilir</li>
                            </ul>
                        </div>

                        <!-- Veri İhlali Bildirimi -->
                        <div class="privacy-section">
                            <h2><i class="fas fa-exclamation-triangle"></i> Veri İhlali Politikası</h2>
                            
                            <p>Kişisel veri güvenliği ihlali durumunda takip ettiğimiz prosedürler:</p>

                            <h3>⚡ Acil Müdahale</h3>
                            <ul>
                                <li><strong>İlk 1 Saat:</strong> İhlalin tespit ve kayıt altına alınması</li>
                                <li><strong>İlk 24 Saat:</strong> İhlalin kapsamının belirlenmesi</li>
                                <li><strong>72 Saat:</strong> Veri Koruma Kurulu'na bildirim</li>
                                <li><strong>Gerekirse:</strong> İlgili kişilere bildirim</li>
                            </ul>

                            <h3>📢 Bildirim Kriterleri</h3>
                            <p>Aşağıdaki durumlarda kişilere bildirim yapılır:</p>
                            <ul>
                                <li>Yüksek risk oluşturan ihlaller</li>
                                <li>Kimlik veya finansal bilgi sızıntıları</li>
                                <li>Yasal yükümlülük gerektiren durumlar</li>
                                <li>Kişinin haklarını etkileyebilecek ihlaller</li>
                            </ul>
                        </div>

                        <!-- Çocukların Verilerinin Korunması -->
                        <div class="privacy-section">
                            <h2><i class="fas fa-child"></i> Çocukların Kişisel Verilerinin Korunması</h2>
                            
                            <p>18 yaşından küçük çocukların kişisel verilerinin korunması için özel önlemler:</p>

                            <div class="warning-box">
                                <h4><i class="fas fa-shield-alt"></i> Yaş Sınırlaması</h4>
                                <p>Web sitemiz 18 yaş altındaki kişilere yönelik değildir. 18 yaş altında veri toplamayız.</p>
                            </div>

                            <h3>👨‍👩‍👧‍👦 Ebeveyn Onayı</h3>
                            <ul>
                                <li>18 yaş altı tespit edilirse veri silinir</li>
                                <li>Ebeveyn/vasi onayı aranır</li>
                                <li>Özel koruma önlemleri uygulanır</li>
                                <li>Pazarlama faaliyetlerinde yer almaz</li>
                            </ul>
                        </div>

                        <!-- Politika Değişiklikleri -->
                        <div class="privacy-section">
                            <h2><i class="fas fa-edit"></i> Politika Güncellemeleri</h2>
                            
                            <p>Bu Gizlilik Politikası düzenli olarak gözden geçirilir ve güncellenebilir:</p>

                            <h3>📝 Güncelleme Süreci</h3>
                            <ul>
                                <li><strong>Küçük Değişiklikler:</strong> Web sitesinde yayınlanır</li>
                                <li><strong>Önemli Değişiklikler:</strong> E-posta ile bildirilir</li>
                                <li><strong>Yasal Değişiklikler:</strong> Derhal uygulanır</li>
                                <li><strong>Kullanıcı Hakları:</strong> Değişikliklere itiraz edebilirsiniz</li>
                            </ul>

                            <div class="highlight-box">
                                <h4><i class="fas fa-bell"></i> Bildirim Sistemi</h4>
                                <p>Önemli politika değişikliklerinden haberdar olmak için e-posta bildirimlerimize üye olabilirsiniz.</p>
                            </div>
                        </div>

                        <!-- İletişim ve Başvuru -->
                        <div class="privacy-section" id="iletisim">
                            <h2><i class="fas fa-envelope-open-text"></i> Başvuru ve İletişim</h2>
                            
                            <h3>📞 KVKK Başvuru Kanalları</h3>
                            <p>Gizlilik haklarınızı kullanmak için aşağıdaki kanallardan bizimle iletişime geçebilirsiniz:</p>

                            <ul>
                                <li><strong>E-posta:</strong> <a href="mailto:kvkk@gokhanaydinli.com">kvkk@gokhanaydinli.com</a></li>
                                <li><strong>Posta:</strong> Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</li>
                                <li><strong>Telefon:</strong> <a href="tel:+902128016058">+90 (212) 801 60 58</a></li>
                                <li><strong>Kayıtlı Elektronik Posta (KEP):</strong> info@gokhanaydinligayrimenkul.hs02.kep.tr</li>
                            </ul>

                            <h3>📋 Başvuru Formu</h3>
                            <p>KVKK başvurularınız için özel başvuru formumuz:</p>
                            <ul>
                                <li>Kimlik bilgilerinizi eksiksiz doldurun</li>
                                <li>Talebinizi net şekilde belirtin</li>
                                <li>Gerekli belgeleri ekleyin</li>
                                <li>İmzalı başvurunuzu gönderin</li>
                            </ul>

                            <div class="warning-box">
                                <h4><i class="fas fa-id-card"></i> Kimlik Doğrulama</h4>
                                <p>Güvenlik nedeniyle tüm başvurularda kimlik doğrulama gereklidir. T.C. kimlik numarası ve kimlik belgesi kopyası talep edilebilir.</p>
                            </div>
                        </div>

                        <!-- İletişim Bilgileri -->
                        <div class="contact-info">
                            <h3><i class="fas fa-headset"></i> Daha Fazla Bilgi</h3>
                            <p>Gizlilik politikamız hakkında sorularınız varsa bizimle iletişime geçebilirsiniz:</p>
                            <p>
                                <strong>E-posta:</strong> <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a><br>
                                <strong>KVKK İletişim:</strong> <a href="mailto:kvkk@gokhanaydinli.com">kvkk@gokhanaydinli.com</a><br>
                                <strong>Telefon:</strong> <a href="tel:+902128016058">+90 (212) 801 60 58</a><br>
                                <strong>WhatsApp:</strong> <a href="tel:+905302037083">+90 (530) 203 70 83</a><br>
                                <strong>Çalışma Saatleri:</strong> Pzt-Cum 09:00-19:00, Cmt-Paz 09:00-14:00
                            </p>
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

            // Observe all privacy sections
            document.querySelectorAll('.privacy-section').forEach(function(section) {
                section.style.opacity = '0';
                section.style.transform = 'translateY(20px)';
                section.style.transition = 'opacity 0.6s ease, transform 0.6s ease';
                observer.observe(section);
            });

            // Quick nav active state
            window.addEventListener('scroll', function() {
                const sections = document.querySelectorAll('.privacy-section[id]');
                const navLinks = document.querySelectorAll('.quick-nav a');
                
                let current = '';
                sections.forEach(section => {
                    const sectionTop = section.offsetTop;
                    const sectionHeight = section.clientHeight;
                    if (window.pageYOffset >= sectionTop - 200) {
                        current = section.getAttribute('id');
                    }
                });

                navLinks.forEach(link => {
                    link.style.color = '#6b7280';
                    link.style.fontWeight = 'normal';
                    if (link.getAttribute('href') === '#' + current) {
                        link.style.color = '#4c51bf';
                        link.style.fontWeight = '600';
                    }
                });
            });
        });

        // Privacy Settings Functions
        function openPrivacySettings() {
            const settings = {
                marketing: confirm('Pazarlama amaçlı iletişim almak istiyor musunuz? (E-posta bültenleri, emlak önerileri)'),
                analytics: confirm('Site kullanım analitiklerine katılmak istiyor musunuz? (Google Analytics)'),
                personalization: confirm('Kişiselleştirilmiş içerik ve öneriler almak istiyor musunuz?'),
                thirdParty: confirm('Üçüncü taraf entegrasyonlarına (haritalar, sosyal medya) izin veriyor musunuz?')
            };
            
            localStorage.setItem('privacySettings', JSON.stringify(settings));
            showNotification('Gizlilik tercihleriniz kaydedildi!', 'success');
            
            // Update consent status
            updateConsentStatus();
        }

        function updateConsentStatus() {
            const consentStatus = document.querySelector('.consent-status');
            const statusText = consentStatus.querySelector('.status-text');
            
            statusText.innerHTML = '<i class="fas fa-check-circle"></i> Gizlilik ayarlarınız güncellendi - ' + new Date().toLocaleDateString('tr-TR');
        }

        function openCookieSettings() {
            // Redirect to cookie policy page
            window.open('cookies.php', '_blank');
        }

        function showNotification(message, type) {
            const notification = document.createElement('div');
            notification.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                background: ${type === 'success' ? '#10b981' : type === 'info' ? '#3b82f6' : '#f59e0b'};
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
            
            setTimeout(() => {
                notification.style.animation = 'slideOutRight 0.3s ease';
                setTimeout(() => {
                    if (document.body.contains(notification)) {
                        document.body.removeChild(notification);
                    }
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