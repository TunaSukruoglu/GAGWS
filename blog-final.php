<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

try {
    include 'db.php';
} catch (Exception $e) {
    die("DB Bağlantı Hatası: " . $e->getMessage());
}

// Demo blog verileri - 6 adet blog yazısı
$blog_posts = [
    [
        'id' => 1,
        'title' => 'Gayrimenkul Yatırımında 2024 Trendleri',
        'content' => 'Gayrimenkul sektöründe bu yıl yaşanan gelişmeler ve yatırım fırsatları hakkında detaylı analiz.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-15',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 5
    ],
    [
        'id' => 2,
        'title' => 'Ev Alırken Dikkat Edilmesi Gerekenler',
        'content' => 'Ev satın alma sürecinde önemli noktalar ve dikkat edilmesi gereken hususlar.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-10',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 7
    ],
    [
        'id' => 3,
        'title' => 'İstanbul\'da En İyi Yatırım Bölgeleri',
        'content' => 'İstanbul\'un farklı ilçelerindeki yatırım potansiyeli ve gelecek projeksiyonları.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-05',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 6
    ],
    [
        'id' => 4,
        'title' => 'Konut Kredisi Faiz Oranları Analizi',
        'content' => 'Güncel konut kredisi faiz oranları ve kredi kullanma stratejileri.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2024-01-01',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 4
    ],
    [
        'id' => 5,
        'title' => 'Ofis Kiralama Rehberi 2024',
        'content' => 'İş yeri kiralama sürecinde dikkat edilmesi gereken noktalar ve ipuçları.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2023-12-28',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 8
    ],
    [
        'id' => 6,
        'title' => 'Gayrimenkul Değerleme Yöntemleri',
        'content' => 'Mülk değerleme teknikleri ve piyasa değeri belirleme stratejileri.',
        'author' => 'Gökhan Aydınlı',
        'date' => '2023-12-25',
        'image' => 'images/blog/blog_img_01.jpg',
        'reading_time' => 9
    ]
];

function formatTurkishDate($date) {
    $months = [
        '01' => 'Ocak', '02' => 'Şubat', '03' => 'Mart', '04' => 'Nisan',
        '05' => 'Mayıs', '06' => 'Haziran', '07' => 'Temmuz', '08' => 'Ağustos',
        '09' => 'Eylül', '10' => 'Ekim', '11' => 'Kasım', '12' => 'Aralık'
    ];
    $parts = explode('-', $date);
    return $parts[2] . ' ' . $months[$parts[1]] . ' ' . $parts[0];
}
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Blog - Gökhan Aydınlı Gayrimenkul</title>
    <meta name="description" content="Gayrimenkul sektöründeki güncel gelişmeler, yatırım önerileri ve uzman görüşleri">
    <meta name="keywords" content="gayrimenkul, yatırım, ev, daire, ofis, blog, istanbul">
    
    <!-- CSS -->
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@400;500;700&display=swap" rel="stylesheet">
</head>
<body>
    <!-- Header Start -->
    <header class="theme-main-menu menu-overlay menu-style-two sticky-menu">
        <div class="inner-content gap-one">
            <div class="top-header position-relative">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="logo order-lg-0">
                        <a href="index.php" class="d-flex align-items-center">
                            <img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
                        </a>
                    </div>
                    
                    <div class="right-widget order-lg-3 ms-auto">
                        <ul class="d-flex align-items-center style-none">
                            <li class="d-none d-md-inline-block me-4">
                                <a href="dashboard/add-property.php" class="btn-ten rounded-0"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
                            </li>
                            <li class="d-none d-md-inline-block me-3">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="login-btn-two rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="fa-regular fa-lock"></i></a>
                            </li>
                            <li class="d-none d-md-inline-block">
                                <a href="#" data-bs-toggle="modal" data-bs-target="#searchModal" class="search-btn-one rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="bi bi-search"></i></a>
                            </li>
                        </ul>
                    </div>
                    
                    <nav class="navbar navbar-expand-lg p0 order-lg-2">
                        <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false" aria-label="Toggle navigation">
                            <span></span>
                        </button>
                        <div class="collapse navbar-collapse ms-xl-5" id="navbarNav">
                            <ul class="navbar-nav align-items-lg-center">
                                <li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
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
                                    <a class="nav-link active" href="blog.php">Blog</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="contact.php">İletişim</a>
                                </li>
                                <li class="nav-item">
                                    <a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
                                </li>
                                <li class="d-block d-md-none mt-5">
                                    <a href="dashboard/add-property.php" class="btn-ten w-100 rounded-0"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
                                </li>
                            </ul>
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- Header End -->

    <!-- Page Title -->
    <div class="inner-banner-one inner-banner bg-pink" style="background: url(images/shape/04.svg);">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="page-header">
                        <ul class="breadcrumb style-none">
                            <li><a href="index.php">Ana Sayfa</a></li>
                            <li class="current-page">Blog</li>
                        </ul>
                        <h1 class="hero-heading">Blog Yazıları</h1>
                        <p class="sub-text">Gayrimenkul dünyasından güncel haberler ve uzman görüşleri</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Blog Content -->
    <div class="blog-section-one mt-150 xl-mt-120 mb-150 xl-mb-120">
        <div class="container">
            <div class="row gx-xl-5">
                <div class="col-lg-8">
                    <div class="blog-meta-wrapper pe-xxl-5">
                        <div class="row">
                            <?php foreach ($blog_posts as $post): 
                                $blog_url = "blog" . $post['id'] . ".php";
                            ?>
                            <div class="col-sm-6">
                                <article class="blog-meta-two color-two mb-50 lg-mb-40">
                                    <figure class="post-img m0">
                                        <a href="<?= $blog_url ?>" class="w-100 d-block">
                                            <img src="<?= $post['image'] ?>" alt="<?= htmlspecialchars($post['title']) ?>" class="lazy-img w-100 tran4s">
                                        </a>
                                    </figure>
                                    <div class="post-data mt-30">
                                        <div class="post-date">
                                            <a href="<?= $blog_url ?>" class="date"><?= formatTurkishDate($post['date']) ?></a>
                                        </div>
                                        <div class="post-info">
                                            <a href="<?= $blog_url ?>"><?= htmlspecialchars($post['author']) ?> .</a> <?= $post['reading_time'] ?> dk okuma
                                        </div>
                                        <div class="blog-title">
                                            <a href="<?= $blog_url ?>" class="blog-title"><h4><?= htmlspecialchars($post['title']) ?></h4></a>
                                            <div class="post-excerpt">
                                                <p><?= htmlspecialchars(substr($post['content'], 0, 120)) ?>...</p>
                                            </div>
                                            <a href="<?= $blog_url ?>" class="btn-four"><i class="bi bi-arrow-up-right"></i></a>
                                        </div>
                                    </div>
                                </article>
                            </div>
                            <?php endforeach; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <div class="pagination-one d-flex align-items-center style-none pt-30">
                            <ul class="d-flex align-items-center style-none">
                                <li class="me-3"><a href="#" class="d-flex align-items-center"><i class="bi bi-chevron-left"></i></a></li>
                                <li><a href="#" class="active">1</a></li>
                                <li><a href="#">2</a></li>
                                <li><a href="#">3</a></li>
                                <li class="ms-2"><a href="#" class="d-flex align-items-center"><i class="bi bi-chevron-right"></i></a></li>
                            </ul>
                        </div>
                    </div>
                </div>
                
                <!-- Sidebar -->
                <div class="col-lg-4">
                    <div class="blog-sidebar ps-xl-5 md-mt-60">
                        <!-- Search -->
                        <div class="blog-sidebar-banner text-center mb-55 md-mb-40">
                            <h4 class="mb-20">Blog Ara</h4>
                            <form action="blog.php" method="GET">
                                <input type="text" name="search" placeholder="Arama yapın..." value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                                <button type="submit"><i class="bi bi-search"></i></button>
                            </form>
                        </div>
                        
                        <!-- Categories -->
                        <div class="sidebar-recent-news mb-60 md-mb-50">
                            <h4 class="sidebar-title">Kategoriler</h4>
                            <ul class="style-none">
                                <li><a href="blog.php?category=yatirim">Gayrimenkul Yatırımı</a></li>
                                <li><a href="blog.php?category=satin-alma">Ev Satın Alma</a></li>
                                <li><a href="blog.php?category=analiz">Piyasa Analizi</a></li>
                                <li><a href="blog.php?category=danismanlik">Danışmanlık</a></li>
                                <li><a href="blog.php?category=kiralama">Kiralama</a></li>
                                <li><a href="blog.php?category=hukuk">Gayrimenkul Hukuku</a></li>
                            </ul>
                        </div>
                        
                        <!-- Recent Posts -->
                        <div class="sidebar-recent-news mb-60 md-mb-50">
                            <h4 class="sidebar-title">Son Yazılar</h4>
                            <ul class="style-none">
                                <?php foreach (array_slice($blog_posts, 0, 4) as $recent_post): ?>
                                <li>
                                    <div class="news-block d-flex align-items-center pt-20 pb-20 border-bottom">
                                        <div><img src="<?= $recent_post['image'] ?>" alt="<?= htmlspecialchars($recent_post['title']) ?>" class="lazy-img"></div>
                                        <div class="post-data ps-4">
                                            <h4><a href="blog<?= $recent_post['id'] ?>.php"><?= htmlspecialchars($recent_post['title']) ?></a></h4>
                                            <div class="date"><?= formatTurkishDate($recent_post['date']) ?></div>
                                        </div>
                                    </div>
                                </li>
                                <?php endforeach; ?>
                            </ul>
                        </div>
                        
                        <!-- Tags -->
                        <div class="sidebar-recent-news">
                            <h4 class="sidebar-title">Etiketler</h4>
                            <ul class="tags d-flex flex-wrap style-none">
                                <li><a href="#">İstanbul</a></li>
                                <li><a href="#">Yatırım</a></li>
                                <li><a href="#">Konut</a></li>
                                <li><a href="#">Ofis</a></li>
                                <li><a href="#">Dükkan</a></li>
                                <li><a href="#">Kiralama</a></li>
                                <li><a href="#">Satış</a></li>
                                <li><a href="#">Emlak</a></li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Footer -->
    <div class="footer-one bg-pink pt-80 pb-40">
        <div class="container">
            <div class="row">
                <div class="col-lg-12">
                    <div class="footer-wrapper">
                        <div class="row">
                            <div class="col-lg-4 footer-intro mb-40">
                                <div class="logo"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:60px; width:auto;"></div>
                                <p class="text-xl lh-lg mb-45 lg-mb-30">Gayrimenkul sektöründe güvenilir ve profesyonel hizmet. Uzman ekibimizle size en uygun emlak çözümlerini sunuyoruz.</p>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
                                </ul>
                            </div>
                            <div class="col-lg-2 col-sm-4 ms-auto mb-30">
                                <h5 class="footer-title">Hızlı Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="index.php">Ana Sayfa</a></li>
                                    <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="portfoy.php">Portföy</a></li>
                                    <li><a href="blog.php">Blog</a></li>
                                    <li><a href="contact.php">İletişim</a></li>
                                </ul>
                            </div>
                            <div class="col-lg-2 col-sm-4 mb-30">
                                <h5 class="footer-title">Hizmetler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="portfoy.php">Satılık Konut</a></li>
                                    <li><a href="portfoy.php">Kiralık Konut</a></li>
                                    <li><a href="ofiskiralama.php">Ofis Kiralama</a></li>
                                    <li><a href="dukkankiralama.php">Dükkan Kiralama</a></li>
                                    <li><a href="dashboard/add-property.php">İlan Ver</a></li>
                                </ul>
                            </div>
                            <div class="col-lg-3 col-sm-4 mb-30">
                                <h5 class="footer-title">İletişim Bilgileri</h5>
                                <p class="text-xl lh-lg mb-25">İstanbul, Türkiye<br>Profesyonel Gayrimenkul Danışmanlığı</p>
                                <a href="mailto:info@gokhanaydinli.com" class="email tran3s">info@gokhanaydinli.com</a><br>
                                <a href="tel:+905001234567" class="mobile tran3s">+90 500 123 45 67</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
        
        <!-- Copyright -->
        <div class="container">
            <div class="bottom-footer">
                <div class="d-lg-flex justify-content-between align-items-center">
                    <ul class="order-lg-1 pb-15 d-flex justify-content-center footer-nav style-none">
                        <li><a href="#">Gizlilik Politikası</a></li>
                        <li><a href="#">Kullanım Şartları</a></li>
                        <li><a href="#">KVKK</a></li>
                    </ul>
                    <p class="copyright text-center color-dark m0 order-lg-0 pb-15">Copyright @<?= date('Y') ?> Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
                </div>
            </div>
        </div>
    </div>

    <!-- JS -->
    <script src="js/jquery.min.js"></script>
    <script src="js/bootstrap.bundle.min.js"></script>
    <script src="js/main.js"></script>
    
    <!-- Optional: Smooth Scroll -->
    <script>
        // Smooth scrolling for anchor links
        document.querySelectorAll('a[href^="#"]').forEach(anchor => {
            anchor.addEventListener('click', function (e) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({ behavior: 'smooth' });
                }
            });
        });
    </script>
</body>
</html>
