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
    <meta name="keywords" content="Şartlar, Koşullar, Kullanım Şartları, Gayrimenkul, Gökhan Aydınlı">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul web sitesi kullanım şartları ve koşulları. Platform kullanımı ile ilgili detaylı bilgiler.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Şartlar & Koşullar - Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/terms-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Şartlar & Koşullar - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        /* Modern Terms Page Stilleri */
        .terms-container {
            background: #ffffff;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0, 0, 0, 0.05);
            padding: 50px;
            margin: 40px 0;
            border: 1px solid #f0f2f5;
        }

        .terms-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #f8f9fa;
        }

        .terms-header h1 {
            font-size: 2.5rem;
            font-weight: 700;
            color: #2d3748;
            margin-bottom: 15px;
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .terms-header p {
            font-size: 1.1rem;
            color: #6b7280;
            margin-bottom: 20px;
        }

        .last-updated {
            background: #f8f9fa;
            padding: 15px 25px;
            border-radius: 10px;
            display: inline-block;
            color: #495057;
            font-weight: 500;
        }

        .terms-section {
            margin-bottom: 40px;
            padding: 30px;
            border-radius: 15px;
            background: #fafbfc;
            border-left: 4px solid #667eea;
        }

        .terms-section h2 {
            font-size: 1.8rem;
            font-weight: 600;
            color: #2d3748;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
        }

        .terms-section h2 i {
            margin-right: 15px;
            color: #667eea;
            font-size: 1.5rem;
        }

        .terms-section h3 {
            font-size: 1.3rem;
            font-weight: 600;
            color: #374151;
            margin: 25px 0 15px 0;
        }

        .terms-section p {
            font-size: 1rem;
            line-height: 1.7;
            color: #4b5563;
            margin-bottom: 15px;
            text-align: justify;
        }

        .terms-section ul {
            margin: 15px 0;
            padding-left: 25px;
        }

        .terms-section li {
            font-size: 1rem;
            line-height: 1.6;
            color: #4b5563;
            margin-bottom: 8px;
        }

        .highlight-box {
            background: linear-gradient(135deg, #667eea10 0%, #764ba210 100%);
            border: 1px solid #667eea30;
            border-radius: 12px;
            padding: 25px;
            margin: 25px 0;
        }

        .highlight-box h4 {
            color: #667eea;
            font-weight: 600;
            margin-bottom: 15px;
            font-size: 1.2rem;
        }

        .contact-info {
            background: #667eea;
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
            color: #f0f9ff;
            text-decoration: none;
            font-weight: 500;
        }

        .contact-info a:hover {
            color: white;
            text-decoration: underline;
        }

        .scroll-top {
            position: fixed;
            bottom: 30px;
            right: 30px;
            width: 50px;
            height: 50px;
            background: #667eea;
            color: white;
            border: none;
            border-radius: 50%;
            display: none;
            z-index: 9999;
            cursor: pointer;
            transition: all 0.3s ease;
        }

        .scroll-top:hover {
            background: #5a67d8;
            transform: translateY(-2px);
        }

        /* Responsive */
        @media (max-width: 768px) {
            .terms-container {
                padding: 30px 20px;
                margin: 20px 0;
            }
            
            .terms-header h1 {
                font-size: 2rem;
            }
            
            .terms-section {
                padding: 20px;
            }
            
            .terms-section h2 {
                font-size: 1.5rem;
            }
        }

        @media (max-width: 576px) {
            .terms-container {
                padding: 20px 15px;
            }
            
            .terms-header h1 {
                font-size: 1.7rem;
            }
            
            .terms-section h2 {
                font-size: 1.3rem;
                flex-direction: column;
                text-align: center;
            }
            
            .terms-section h2 i {
                margin-right: 0;
                margin-bottom: 10px;
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
                <h3 class="mb-35 xl-mb-20 pt-15" style="color: #6c757d !important;">Şartlar & Koşullar</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>Şartlar & Koşullar</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Terms Content -->
        <div class="container my-5">
            <div class="terms-container">
                
                <!-- Header -->
                <div class="terms-header">
                    <h1>Kullanım Şartları & Koşulları</h1>
                    <p>Gökhan Aydınlı Gayrimenkul web sitesi ve hizmetlerinin kullanımına ilişkin şartlar</p>
                    <div class="last-updated">
                        <i class="fas fa-calendar-alt"></i> Son Güncelleme: <?php echo date('d.m.Y'); ?>
                    </div>
                </div>

                <!-- Genel Şartlar -->
                <div class="terms-section">
                    <h2><i class="fas fa-info-circle"></i> Genel Şartlar</h2>
                    <p>Bu web sitesini kullanarak, aşağıdaki şartları ve koşulları kabul etmiş sayılırsınız. Bu şartları kabul etmiyorsanız, lütfen siteyi kullanmayın.</p>
                    
                    <div class="highlight-box">
                        <h4><i class="fas fa-exclamation-triangle"></i> Önemli Not</h4>
                        <p>Bu şartlar herhangi bir zamanda önceden haber verilmeksizin değiştirilebilir. Düzenli olarak kontrol edilmesi tavsiye edilir.</p>
                    </div>
                </div>

                <!-- Hizmet Tanımı -->
                <div class="terms-section">
                    <h2><i class="fas fa-building"></i> Hizmet Tanımı</h2>
                    <p>Gökhan Aydınlı Gayrimenkul olarak sunduğumuz hizmetler:</p>
                    <ul>
                        <li>Gayrimenkul alım-satım danışmanlığı</li>
                        <li>Emlak kiralama ve yönetim hizmetleri</li>
                        <li>Yatırım danışmanlığı</li>
                        <li>Emlak değerleme hizmetleri</li>
                        <li>Online emlak portföy görüntüleme</li>
                        <li>Emlak hesaplama araçları</li>
                    </ul>
                </div>

                <!-- Kullanıcı Yükümlülükleri -->
                <div class="terms-section">
                    <h2><i class="fas fa-user-shield"></i> Kullanıcı Yükümlülükleri</h2>
                    <h3>Kayıt ve Hesap Güvenliği</h3>
                    <p>Web sitemize üye olarak aşağıdaki yükümlülükleri kabul edersiniz:</p>
                    <ul>
                        <li>Doğru ve güncel bilgiler verme</li>
                        <li>Hesap güvenliğini sağlama</li>
                        <li>Şifrenizi gizli tutma</li>
                        <li>Yetkisiz erişimi derhal bildirme</li>
                    </ul>

                    <h3>Yasaklanan Kullanımlar</h3>
                    <p>Aşağıdaki faaliyetler kesinlikle yasaktır:</p>
                    <ul>
                        <li>Yanlış veya yanıltıcı bilgi verme</li>
                        <li>Başkalarının hesaplarını kullanma</li>
                        <li>Site güvenliğini tehdit edici faaliyetler</li>
                        <li>Telif haklarını ihlal eden içerik paylaşma</li>
                        <li>Spam veya rahatsız edici mesajlar gönderme</li>
                    </ul>
                </div>

                <!-- Gayrimenkul Hizmetleri -->
                <div class="terms-section">
                    <h2><i class="fas fa-home"></i> Gayrimenkul Hizmetleri</h2>
                    
                    <h3>Danışmanlık Hizmetleri</h3>
                    <p>Sunduğumuz gayrimenkul danışmanlık hizmetleri tamamen bilgilendirme amaçlıdır. Nihai kararlar müşteriye aittir.</p>
                    
                    <h3>Emlak İlanları</h3>
                    <p>Web sitemizdeki emlak ilanları:</p>
                    <ul>
                        <li>Güncel olmayabilir</li>
                        <li>Fiyat değişikliklerine tabidir</li>
                        <li>Satış/kiralama garantisi vermez</li>
                        <li>Detaylı kontrole tabidir</li>
                    </ul>

                    <div class="highlight-box">
                        <h4><i class="fas fa-handshake"></i> Komisyon Politikası</h4>
                        <p>Gayrimenkul işlemlerinde uygulanacak komisyon oranları, işlem türüne göre değişkenlik gösterir ve müşteri ile önceden belirlenir.</p>
                    </div>
                </div>

                <!-- Kişisel Verilerin Korunması -->
                <div class="terms-section">
                    <h2><i class="fas fa-shield-alt"></i> Kişisel Verilerin Korunması</h2>
                    
                    <h3>Veri Toplama</h3>
                    <p>Topladığımız kişisel veriler:</p>
                    <ul>
                        <li>Ad, soyad ve iletişim bilgileri</li>
                        <li>E-posta adresi ve telefon numarası</li>
                        <li>Gayrimenkul tercihleri</li>
                        <li>Site kullanım verileri</li>
                    </ul>

                    <h3>Veri Kullanımı</h3>
                    <p>Kişisel verileriniz sadece aşağıdaki amaçlarla kullanılır:</p>
                    <ul>
                        <li>Hizmet sunumu ve müşteri desteği</li>
                        <li>Emlak önerilerinin kişiselleştirilmesi</li>
                        <li>Yasal yükümlülüklerin yerine getirilmesi</li>
                        <li>İletişim ve bilgilendirme</li>
                    </ul>
                </div>

                <!-- Sorumluluk Sınırları -->
                <div class="terms-section">
                    <h2><i class="fas fa-balance-scale"></i> Sorumluluk Sınırları</h2>
                    
                    <p>Gökhan Aydınlı Gayrimenkul aşağıdaki durumlardan sorumlu değildir:</p>
                    <ul>
                        <li>Üçüncü tarafların sebep olduğu zararlar</li>
                        <li>Piyasa dalgalanmalarından kaynaklanan değer değişiklikleri</li>
                        <li>Teknik arızalar veya sistem kesintileri</li>
                        <li>Kullanıcının kendi kararlarından doğan sonuçlar</li>
                        <li>Harici web sitelerinin içeriği</li>
                    </ul>

                    <div class="highlight-box">
                        <h4><i class="fas fa-gavel"></i> Uyuşmazlık Çözümü</h4>
                        <p>Bu şartlardan doğan uyuşmazlıklar İstanbul Mahkemeleri'nin yetkisi altındadır ve Türk Hukuku geçerlidir.</p>
                    </div>
                </div>

                <!-- Fikri Mülkiyet -->
                <div class="terms-section">
                    <h2><i class="fas fa-copyright"></i> Fikri Mülkiyet Hakları</h2>
                    
                    <p>Bu web sitesindeki tüm içerik, tasarım, logo, metin, resim ve diğer materyaller Gökhan Aydınlı Gayrimenkul'ün mülkiyetindedir.</p>
                    
                    <h3>Kullanım İzinleri</h3>
                    <ul>
                        <li>İçeriği kişisel kullanım amacıyla görüntüleyebilirsiniz</li>
                        <li>Ticari amaçla kullanım yasaktır</li>
                        <li>İzinsiz kopyalama ve dağıtım yasaktır</li>
                        <li>Değiştirme ve düzenleme yasaktır</li>
                    </ul>
                </div>

                <!-- İletişim Bilgileri -->
                <div class="contact-info">
                    <h3><i class="fas fa-envelope"></i> Sorularınız mı var?</h3>
                    <p>Bu şartlar hakkında herhangi bir sorunuz varsa bizimle iletişime geçebilirsiniz:</p>
                    <p>
                        <strong>E-posta:</strong> <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a><br>
                        <strong>Telefon:</strong> <a href="tel:+902128016058">+90 (212) 801 60 58</a><br>
                        <strong>Adres:</strong> Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul
                    </p>
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

            // Smooth scrolling for anchor links
            document.querySelectorAll('a[href^="#"]').forEach(anchor => {
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
        });
        </script>

        <!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-labelledby="loginModalLabel" aria-hidden="true">
            <div class="modal-dialog modal-dialog-centered">
                <div class="modal-content">
                    <div class="modal-header border-0">
                        <h5 class="modal-title" id="loginModalLabel">Giriş Yap</h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    </div>
                    <div class="modal-body">
                        <form id="loginForm">
                            <div class="mb-3">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                            <div class="mb-3">
                                <label for="password" class="form-label">Şifre</label>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                            <div class="mb-3 form-check">
                                <input type="checkbox" class="form-check-input" id="remember">
                                <label class="form-check-label" for="remember">Beni hatırla</label>
                            </div>
                            <button type="submit" class="btn btn-primary w-100">Giriş Yap</button>
                        </form>
                        <div class="text-center mt-3">
                            <p>Hesabınız yok mu? <a href="register.php">Kayıt olun</a></p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <script>
        // Login form submission
        document.getElementById('loginForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload();
                } else {
                    alert(data.message || 'Giriş başarısız');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu');
            });
        });
        
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