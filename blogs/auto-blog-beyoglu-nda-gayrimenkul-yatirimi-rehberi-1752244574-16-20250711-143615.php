<?php
session_start();

// Kullanıcı giriş bilgileri
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | 2025 Rehberi">
    <meta name="description" content="🏠 Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | İstanbul, yatırım konularında uzman rehberi. Gökhan Aydınlı Gayrimenkul&#039;dan profesyonel tavsiyelar. 2024 güncel bilgiler.">
    <meta property="og:title" content="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi">
    <meta property="og:description" content="✅ Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi hakkında kapsamlı rehber. İstanbul, yatırım, konut konularında uzman görüşleri ve pratik öneriler.">
    <meta property="og:image" content="https://images.unsplash.com/photo-1568149139788-4d3b85e9cccc?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3NzIyMTF8MHwxfHNlYXJjaHwxfHxJc3RhbmJ1bCUyMGlzdGFuYnVsJTIwcHJvcGVydHklMjByZWFsJTIwZXN0YXRlJTIwdHVya2V5fGVufDB8MHx8fDE3NTIyNDQyNTh8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080">
    <meta property="og:type" content="article">
    <title>Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | Gökhan Aydınlı Gayrimenkul</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .blog-content-wrapper {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .blog-article {
            background: #fff;
            border-radius: 15px;
            padding: 60px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .blog-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        .blog-title {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        .blog-meta {
            color: #6b7280;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .blog-featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin: 40px 0;
        }
        .blog-content {
            font-size: 18px;
            line-height: 1.8;
            color: #374151;
        }
        .blog-content h3, .blog-content h4, .blog-content h5 {
            color: #1f2937;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .blog-content p {
            margin-bottom: 20px;
        }
        .blog-content ul, .blog-content ol {
            margin-bottom: 25px;
            padding-left: 30px;
        }
        .blog-content li {
            margin-bottom: 10px;
        }
        .blog-content blockquote {
            background: #f8f9fa;
            border-left: 5px solid #007bff;
            padding: 25px;
            margin: 30px 0;
            border-radius: 0 10px 10px 0;
        }
        .ai-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 30px;
        }
        .blog-footer-info {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .blog-article {
                padding: 30px 20px;
            }
            .blog-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-page-wrapper">
        <!-- Header -->
        <header class="theme-main-menu menu-overlay menu-style-seven white-vr sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="../index.php" class="d-flex align-items-center">
                                <img src="../images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
                            </a>
                        </div>
                        
                        <!-- Navigation -->
                        <nav class="navbar navbar-expand-lg p0 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="nav-item">
                                        <a class="nav-link" href="../index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../portfoy.php">Portföy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../contact.php">İletişim</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                        
                        <!-- Auth Widget -->
                        <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                    <li class="dropdown">
                                        <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="../dashboard/dashboard-admin.php">Panel</a></li>
                                            <li><a class="dropdown-item" href="../logout.php">Çıkış Yap</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="../login.php" class="btn-one">
                                            <i class="fa-regular fa-lock"></i> <span>Giriş</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Blog Content -->
        <div class="blog-content-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <article class="blog-article">
                            <div class="ai-badge">
                                🤖 AI Destekli Otomatik Blog
                            </div>
                            
                            <div class="blog-header">
                                <h1 class="blog-title">Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi</h1>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <strong>Gökhan Aydınlı</strong></span>
                                    <span><i class="fas fa-calendar"></i> 11 Jul 2025</span>
                                    <span><i class="fas fa-clock"></i> 1 dk okuma</span>
                                </div>
                            </div>
                            
                            <img src="https://images.unsplash.com/photo-1568149139788-4d3b85e9cccc?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3NzIyMTF8MHwxfHNlYXJjaHwxfHxJc3RhbmJ1bCUyMGlzdGFuYnVsJTIwcHJvcGVydHklMjByZWFsJTIwZXN0YXRlJTIwdHVya2V5fGVufDB8MHx8fDE3NTIyNDQyNTh8MA&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080" 
                                 alt="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi" 
                                 class="blog-featured-image">
                            
                            <div class="blog-content">
                                <h3>🏢 Beyoğlu'nda Gayrimenkul Yatırımı Rehberi - Uzman Rehberi</h3>

<p><strong>Gayrimenkul sektöründe</strong> başarılı olmak için doğru bilgilere sahip olmak çok önemlidir. Bu rehberde <strong>İstanbul, yatırım, konut</strong> konularında uzman görüşlerimizi paylaşıyoruz.</p>

<h4>📊 Önemli Faktörler</h4>
<ul>
<li><strong>İstanbul</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Yatırım</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Konut</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Fiyat</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Analiz</strong> - Detaylı analiz ve değerlendirme</li>
</ul>

<h4>💡 Uzman Önerileri</h4>
<blockquote>
<p><em>"15 yıllık deneyimimle, Beyoğlu'nda Gayrimenkul Yatırımı Rehberi konusunda en önemli nokta sabırlı olmak ve doğru analizler yapmaktır."</em></p>
<footer><strong>- Gökhan Aydınlı</strong></footer>
</blockquote>

<h4>🎯 Sonuç</h4>
<p><strong>Beyoğlu'nda Gayrimenkul Yatırımı Rehberi</strong> alanında başarılı olmak için yukarıdaki faktörleri göz önünde bulundurmanız önemlidir. Profesyonel destek almaktan çekinmeyin.</p>

<p><strong>İletişim:</strong> Gökhan Aydınlı Gayrimenkul olarak size en iyi hizmeti sunmaya hazırız. Detaylı bilgi için bizimle iletişime geçebilirsiniz.</p>
                            </div>
                            
                            <div class="blog-footer-info">
                                <p><strong>📄 Dosya:</strong> auto-blog-beyoglu-nda-gayrimenkul-yatirimi-rehberi-1752244574-16-20250711-143615.php | <strong>🆔 Blog ID:</strong> 16 | <strong>🤖 Otomatik oluşturuldu</strong></p>
                                <p><strong>Gökhan Aydınlı Gayrimenkul</strong> - Uzman gayrimenkul danışmanlığı</p>
                            </div>
                        </article>
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
                                    <a href="../index.php">
                                        <img src="../images/logoSiyah.png" alt="Gökhan Aydınlı">
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
                                    <li><a href="../index.php">Ana Sayfa</a></li>
                                    <li><a href="../hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="../portfoy.php">Portföy</a></li>
                                    <li><a href="../contact.php">İletişim</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="../portfoy.php">Satılık</a></li>
                                    <li><a href="../portfoy.php">Kiralık</a></li>
                                    <li><a href="../dashboard/add-property.php">İlan Ver</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Destek</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="../contact.php">İletişim</a></li>
                                    <li><a href="../dashboard/">Panel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container">
                <div class="bottom-footer">
                    <div class="d-lg-flex justify-content-between align-items-center">
                        <ul class="order-lg-1 pb-15 d-flex justify-content-center footer-nav-link style-none">
                            <li><a href="#">Gizlilik Politikası</a></li>
                            <li><a href="#">Kullanım Şartları</a></li>
                        </ul>
                        <p class="copyright text-center order-lg-0 pb-15">Copyright @2025 Gökhan Aydınlı Gayrimenkul</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>