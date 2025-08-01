<?php
session_start();

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
    <meta charset="UTF-8">
    <meta name="keywords" content="Ticari Gayrimenkul, İstanbul Yatırım, Gökhan Aydınlı">
    <meta name="description" content="Ticari gayrimenkul yatırımında bilinmesi gerekenler ve karlı yatırım stratejileri">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Ticari Gayrimenkul Yatırım Rehberi - Gökhan Aydınlı">
    <meta name='og:image' content='images/assets/blog-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Ticari Gayrimenkul Yatırım Rehberi - Gökhan Aydınlı</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Modal CSS -->
    <?php if (file_exists('includes/modal-css.php')) include 'includes/modal-css.php'; ?>
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
                        <!-- Header'da Giriş butonu (Navigation'dan önce) -->
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
                                    <li class="nav-item dashboard-menu">
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
                <h3 class="mb-35 xl-mb-20 pt-15">Ticari Gayrimenkul Yatırım Rehberi</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="blog.php">Blog</a></li>
                    <li>/</li>
                    <li>Ticari Gayrimenkul</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- Blog Details -->
        <div class="blog-details border-top mt-130 xl-mt-100 pt-100 xl-pt-80 mb-150 xl-mb-100">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <div class="blog-post-meta mb-60 lg-mb-40">
                            <div class="post-info"><a href="#">Gökhan Aydınlı</a> • 7 dk okuma</div>
                            <h3 class="blog-title">Ticari Gayrimenkul Yatırım Rehberi</h3>
                        </div>
                    </div>
                </div>
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-post-meta">
                            <figure class="post-img position-relative m0" style="max-height: 400px; overflow: hidden;">
                                <img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Ticari Gayrimenkul" class="w-100" style="height: 400px; object-fit: cover;">
                            </figure>
                            <div class="post-data pt-50 md-pt-30">
                                <p>Ticari gayrimenkul yatırımı, konut yatırımından farklı dinamiklere sahip ve daha yüksek getiri potansiyeli sunan bir yatırım aracıdır. 17 yıllık deneyimimde, birçok yatırımcının ticari gayrimenkul yatırımında başarılı sonuçlar elde ettiğini gözlemledim.</p>
                                
                                <p>Bu rehberde, ticari gayrimenkul yatırımının temel prensiplerini, türlerini, avantajlarını ve dikkat edilmesi gereken noktaları detaylı bir şekilde ele alacağım.</p>
                                
                                <h5>Ticari Gayrimenkul Türleri</h5>
                                <p>Ticari gayrimenkul yatırımı çeşitli segmentlerde gerçekleştirilebilir:</p>
                                
                                <h6>Ofis Binaları</h6>
                                <ul class="style-none list-item">
                                    <li><strong>A Sınıfı Ofisler:</strong> Merkezi lokasyonlarda, modern teknoloji altyapısı</li>
                                    <li><strong>B Sınıfı Ofisler:</strong> İyi lokasyonlarda, makul kiralama bedelleri</li>
                                    <li><strong>C Sınıfı Ofisler:</strong> Bütçe dostu, yenilenme potansiyeli yüksek</li>
                                </ul>
                                
                                <h6>Perakende Alanları</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Dükkanlar:</strong> Cadde üzeri, yüksek yaya trafiği</li>
                                    <li><strong>AVM Mağazaları:</strong> Sabit müşteri akışı, güvenli yatırım</li>
                                    <li><strong>Restoran Alanları:</strong> Özel lisans gerektiren, yüksek getirili</li>
                                </ul>
                                
                                <h6>Endüstriyel Tesisler</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Depolar:</strong> Lojistik merkezlerde, e-ticaret ile büyüyen segment</li>
                                    <li><strong>Fabrikalar:</strong> Üretim amaçlı, uzun vadeli kira sözleşmeleri</li>
                                    <li><strong>Atölyeler:</strong> Küçük ölçekli üretim, esnek kullanım alanları</li>
                                </ul>
                                
                                <h5>Yatırım Avantajları</h5>
                                <p>Ticari gayrimenkul yatırımının temel avantajları:</p>
                                
                                <h6>Yüksek Getiri Potansiyeli</h6>
                                <p>Ticari gayrimenkuller genellikle konut yatırımından %2-4 daha yüksek kira getirisi sağlar:</p>
                                <ul class="style-none list-item">
                                    <li><strong>Ofis Binaları:</strong> %6-10 yıllık getiri</li>
                                    <li><strong>Perakende Alanları:</strong> %7-12 yıllık getiri</li>
                                    <li><strong>Endüstriyel Tesisler:</strong> %8-15 yıllık getiri</li>
                                </ul>
                                
                                <h6>Vergi Avantajları</h6>
                                <ul class="style-none list-item">
                                    <li>Amortisman hesaplama imkanı</li>
                                    <li>Maintenance ve yenileme giderlerinin mahsup edilmesi</li>
                                    <li>Faiz giderlerinin vergi matrahından düşülmesi</li>
                                    <li>KDV iadesinden yararlanma</li>
                                </ul>
                                
                                <h6>Uzun Vadeli Sözleşmeler</h6>
                                <ul class="style-none list-item">
                                    <li>3-10 yıl arası kira sözleşmeleri</li>
                                    <li>Otomatik artış maddeleri</li>
                                    <li>Erken tahliye teminatları</li>
                                    <li>Sabit nakit akışı garantisi</li>
                                </ul>
                                
                                <h5>Lokasyon Analizi</h5>
                                <p>Ticari gayrimenkul yatırımında lokasyon seçimi kritik önem taşır:</p>
                                
                                <h6>İstanbul'da Öne Çıkan Ticari Bölgeler</h6>
                                
                                <p><strong>Levent - Maslak Aksı</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Avantajlar:</strong> Finansal merkez, metro bağlantısı, prestijli adres</li>
                                    <li><strong>Ofis Kiraları:</strong> 35-80 TL/m²/ay</li>
                                    <li><strong>Hedef Kiracılar:</strong> Multinational şirketler, bankalar, hukuk büroları</li>
                                </ul>
                                
                                <p><strong>Ataşehir Finans Merkezi</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Avantajlar:</strong> Yeni nesil ofis binaları, geniş park alanları</li>
                                    <li><strong>Ofis Kiraları:</strong> 25-60 TL/m²/ay</li>
                                    <li><strong>Hedef Kiracılar:</strong> IT şirketleri, startup'lar, consulting firmaları</li>
                                </ul>
                                
                                <p><strong>Kadıköy - Bostancı</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Avantajlar:</strong> Anadolu yakasının kalbi, uygun fiyatlar</li>
                                    <li><strong>Ofis Kiraları:</strong> 15-35 TL/m²/ay</li>
                                    <li><strong>Hedef Kiracılar:</strong> Yerel şirketler, hizmet sektörü, serbest meslek</li>
                                </ul>
                                
                                <h5>Finansman Seçenekleri</h5>
                                <p>Ticari gayrimenkul yatırımı için çeşitli finansman seçenekleri mevcuttur:</p>
                                
                                <h6>Banka Kredileri</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Ticari Kredi:</strong> %60-70 LTV, 5-15 yıl vade</li>
                                    <li><strong>Proje Finansmanı:</strong> İnşaat aşamasında kullanım</li>
                                    <li><strong>Yeniden Finansman:</strong> Mevcut kredilerin iyileştirilmesi</li>
                                </ul>
                                
                                <h6>Alternatif Finansman</h6>
                                <ul class="style-none list-item">
                                    <li><strong>REITS (GYO):</strong> Gayrimenkul Yatırım Ortaklığı</li>
                                    <li><strong>Özel Sermaye:</strong> Yatırım fonu ortaklıkları</li>
                                    <li><strong>Kirala-Sat Modeli:</strong> Kiracıdan finansman desteği</li>
                                </ul>
                                
                                <h5>Risk Yönetimi</h5>
                                <p>Ticari gayrimenkul yatırımında karşılaşılabilecek riskler ve çözüm önerileri:</p>
                                
                                <h6>Kiracı Riski</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Çözüm:</strong> Kapsamlı mali durum analizi</li>
                                    <li><strong>Çözüm:</strong> Kefil ve teminat mektubu alma</li>
                                    <li><strong>Çözüm:</strong> Çoklu kiracı stratejisi</li>
                                </ul>
                                
                                <h6>Piyasa Riski</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Çözüm:</strong> Farklı sektörlere yayılma</li>
                                    <li><strong>Çözüm:</strong> Esnek kira sözleşmeleri</li>
                                    <li><strong>Çözüm:</strong> Düzenli piyasa analizi</li>
                                </ul>
                                
                                <h6>Likidite Riski</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Çözüm:</strong> Exit stratejisi planlama</li>
                                    <li><strong>Çözüm:</strong> REITS'e dönüştürme seçeneği</li>
                                    <li><strong>Çözüm:</strong> Kısmi satış imkanları</li>
                                </ul>
                                
                                <h5>Yatırım Süreci</h5>
                                <p>Başarılı ticari gayrimenkul yatırımı için izlenmesi gereken adımlar:</p>
                                
                                <h6>1. Piyasa Araştırması</h6>
                                <ul class="style-none list-item">
                                    <li>Bölgesel arz-talep analizi</li>
                                    <li>Rekabet analizi</li>
                                    <li>Gelecek projeksiyonları</li>
                                    <li>Demografik trendler</li>
                                </ul>
                                
                                <h6>2. Mali Analiz</h6>
                                <ul class="style-none list-item">
                                    <li>Net Present Value (NPV) hesaplama</li>
                                    <li>Internal Rate of Return (IRR) analizi</li>
                                    <li>Cash-on-Cash return değerlendirmesi</li>
                                    <li>Break-even analizi</li>
                                </ul>
                                
                                <h6>3. Hukuki İnceleme</h6>
                                <ul class="style-none list-item">
                                    <li>Tapu ve imar durumu kontrolü</li>
                                    <li>İnşaat ruhsatı ve kullanım izni</li>
                                    <li>Yangın güvenlik raporu</li>
                                    <li>Çevre izinleri ve ETÇ belgesi</li>
                                </ul>
                                
                                <h5>Portföy Yönetimi</h5>
                                <p>Ticari gayrimenkul portföyünün etkin yönetimi için öneriler:</p>
                                
                                <h6>Diversifikasyon Stratejileri</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Sektörel Çeşitlendirme:</strong> %40 ofis, %30 perakende, %30 endüstriyel</li>
                                    <li><strong>Coğrafi Dağılım:</strong> Farklı şehir ve bölgelere yayılma</li>
                                    <li><strong>Kiracı Profili:</strong> Küçük, orta ve büyük ölçekli kiracılar</li>
                                    <li><strong>Kira Vadesi:</strong> Farklı sözleşme sürelerinde denge</li>
                                </ul>
                                
                                <h6>Aktif Yönetim</h6>
                                <ul class="style-none list-item">
                                    <li>Düzenli kiracı ilişkileri yönetimi</li>
                                    <li>Proaktif bakım ve renovasyon</li>
                                    <li>Pazar koşullarına göre kira revizesi</li>
                                    <li>Boşalma öncesi yeni kiracı arayışı</li>
                                </ul>
                                
                                <h5>Gelecek Trendleri</h5>
                                <p>Ticari gayrimenkul sektöründe şekillenen geleceğin trendleri:</p>
                                
                                <h6>Teknoloji Entegrasyonu</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Smart Building:</strong> Otomasyon ve enerji verimliliği</li>
                                    <li><strong>IoT Sensörleri:</strong> Kullanım analizi ve optimizasyon</li>
                                    <li><strong>Hibrit Çalışma:</strong> Esnek ofis alanları talebi</li>
                                </ul>
                                
                                <h6>Sürdürülebilirlik</h6>
                                <ul class="style-none list-item">
                                    <li><strong>LEED Sertifikası:</strong> Çevre dostu binalar</li>
                                    <li><strong>Enerji Verimliliği:</strong> Düşük işletme maliyetleri</li>
                                    <li><strong>Yeşil Bina:</strong> Artan kiracı talebi</li>
                                </ul>
                                
                                <h5>Sonuç ve Öneriler</h5>
                                <p>Ticari gayrimenkul yatırımı, doğru yaklaşımla yüksek getiri potansiyeli sunan bir yatırım aracıdır. Başarılı olmak için kapsamlı analiz, profesyonel yaklaşım ve sabırlı yönetim gereklidir.</p>
                                
                                <p><strong>Yeni başlayanlar için öneriler:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Küçük ölçekli dükkan yatırımı ile başlayın</li>
                                    <li>Tanıdık olduğunuz sektörleri tercih edin</li>
                                    <li>Profesyonel yönetim şirketi ile çalışın</li>
                                    <li>Uzun vadeli perspektif benimseyin</li>
                                </ul>
                                
                                <p>17 yıllık deneyimimle, ticari gayrimenkul yatırımında size rehberlik etmek ve portföyünüzü büyütmek için yanınızdayım. Detaylı bilgi ve kişiselleştirilmiş yatırım önerileri için benimle iletişime geçebilirsiniz.</p>
                            </div>
                            
                            <div class="bottom-widget d-sm-flex align-items-center justify-content-between">
                                <ul class="d-flex align-items-center tags style-none pt-20">
                                    <li>Etiketler:</li>
                                    <li><a href="#">Ticari Gayrimenkul,</a></li>
                                    <li><a href="#">Ofis Yatırımı,</a></li>
                                    <li><a href="#">Perakende,</a></li>
                                    <li><a href="#">Endüstriyel</a></li>
                                </ul>
                                <ul class="d-flex share-icon align-items-center style-none pt-20">
                                    <li>Paylaş:</li>
                                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </article>
                    </div>
                    
                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <div class="blog-sidebar ps-xl-5 md-mt-60">
                            <!-- Search -->
                            <div class="blog-sidebar-banner text-center mb-55 md-mb-40">
                                <h4 class="mb-20">Blog Ara</h4>
                                <form action="blog.php" method="GET">
                                    <input type="text" name="search" placeholder="Arama yapın...">
                                    <button type="submit"><i class="bi bi-search"></i></button>
                                </form>
                            </div>
                            
                            <!-- Categories -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">Kategoriler</h4>
                                <ul class="style-none">
                                    <li><a href="blog.php?category=yatirim">Gayrimenkul Yatırımı</a></li>
                                    <li><a href="blog.php?category=ticari">Ticari Gayrimenkul</a></li>
                                    <li><a href="blog.php?category=analiz">Piyasa Analizi</a></li>
                                    <li><a href="blog.php?category=danismanlik">Danışmanlık</a></li>
                                </ul>
                            </div>
                            
                            <!-- Recent Posts -->
                            <div class="sidebar-recent-news mb-60 md-mb-50">
                                <h4 class="sidebar-title">İlgili Yazılar</h4>
                                <ul class="style-none">
                                    <li>
                                        <div class="news-block d-flex align-items-center pt-20 pb-20 border-bottom">
                                            <div><img src="images/blog/blog_img_01.jpg" alt="" class="lazy-img"></div>
                                            <div class="post-data ps-4">
                                                <h4><a href="blog1.php">Gayrimenkul Yatırımında 2024 Trendleri</a></h4>
                                                <div class="date">15 Oca 2024</div>
                                            </div>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="news-block d-flex align-items-center pt-20 pb-20 border-bottom">
                                            <div><img src="images/blog/blog_img_01.jpg" alt="" class="lazy-img"></div>
                                            <div class="post-data ps-4">
                                                <h4><a href="blog8.php">Emlak Piyasasında Dijital Dönüşüm</a></h4>
                                                <div class="date">15 Ara 2023</div>
                                            </div>
                                        </div>
                                    </li>
                                </ul>
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
                                    <li><a href="portfoy.php">Ticari Gayrimenkul</a></li>
                                    <li><a href="portfoy.php">Konut Satışı</a></li>
                                    <li><a href="portfoy.php">Ev Kiralama</a></li>
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
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
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

        <button class="scroll-top">
            <i class="bi bi-arrow-up-short"></i>
        </button>

        <!-- JS -->
        <script src="js/jquery.min.js"></script>
        <script src="js/bootstrap.bundle.min.js"></script>
        <script src="js/main.js"></script>
        
        <!-- Modal JS -->
        <?php if (file_exists('includes/modal-js.php')) include 'includes/modal-js.php'; ?>
        
        <script>
            function switchToRegister() {
                document.querySelector('[data-bs-target="#fc2"]').click();
            }
            function switchToLogin() {
                document.querySelector('[data-bs-target="#fc1"]').click();
            }
        </script>
    </div>
</body>
</html>
