<?php
session_start();
include 'db.php';

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında session-check'i include et (duplikasyon kaldırıldı)
include 'includes/session-check.php';

// Pagination ayarları
$posts_per_page = 6;
$page = isset($_GET['page']) ? (int)$_GET['page'] : 1;
$offset = ($page - 1) * $posts_per_page;

// Blog yazılarını veritabanından çek
try {
    // Toplam yazı sayısını al
    $count_query = "SELECT COUNT(*) as total 
                    FROM blog_posts bp 
                    WHERE bp.status = 'published'";
    $count_result = $conn->query($count_query);
    $total_posts = $count_result ? $count_result->fetch_assoc()['total'] : 0;
    $total_pages = ceil($total_posts / $posts_per_page);
    
    // Blog yazılarını sayfalama ile çek
    $blog_query = "SELECT bp.*, u.name as author_name 
                   FROM blog_posts bp 
                   LEFT JOIN users u ON bp.author_id = u.id 
                   WHERE bp.status = 'published' 
                   ORDER BY bp.published_at DESC, bp.created_at DESC
                   LIMIT $posts_per_page OFFSET $offset";
    $blog_result = $conn->query($blog_query);
    $blog_posts = $blog_result ? $blog_result->fetch_all(MYSQLI_ASSOC) : [];
    
    // Son blog yazıları (sidebar için)
    $recent_query = "SELECT bp.*, u.name as author_name 
                     FROM blog_posts bp 
                     LEFT JOIN users u ON bp.author_id = u.id 
                     WHERE bp.status = 'published' 
                     ORDER BY bp.published_at DESC, bp.created_at DESC
                     LIMIT 3";
    $recent_result = $conn->query($recent_query);
    $recent_posts = $recent_result ? $recent_result->fetch_all(MYSQLI_ASSOC) : [];
    
} catch (Exception $e) {
    error_log("Blog query error: " . $e->getMessage());
    $blog_posts = [];
    $recent_posts = [];
    $total_posts = 0;
    $total_pages = 0;
}

// Eğer veritabanından veri gelmezse, örnek blog yazıları oluştur
if (empty($blog_posts)) {
    $blog_posts = [
        [
            'id' => 1,
            'title' => 'Yabancı Yatırımcılar İçin İstanbul Rehberi',
            'slug' => 'yabanci-yatirimcilar-icin-istanbul-rehberi',
            'content' => 'İstanbul, coğrafi konumu, güçlü ekonomisi ve zengin kültürel mirası ile yabancı yatırımcılar için dünya çapında en cazip gayrimenkul yatırım destinasyonlarından biri haline gelmiştir.',
            'excerpt' => 'İstanbul, coğrafi konumu, güçlü ekonomisi ve zengin kültürel mirası ile yabancı yatırımcılar için dünya çapında en cazip gayrimenkul yatırım destinasyonlarından biri haline gelmiştir.',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-01-18 10:00:00',
            'created_at' => '2024-01-18 10:00:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ],
        [
            'id' => 2,
            'title' => 'Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler',
            'slug' => 'ofis-kiralama-sozlesmesi-temel-maddeler',
            'content' => 'Ofis kiralama sözleşmesi, hem kiracı hem de kiraya veren açısından kritik önem taşıyan yasal bir belgedir.',
            'excerpt' => 'Ofis kiralama sözleşmesinde mutlaka bulunması gereken temel maddeler ve dikkat edilmesi gereken hukuki detaylar.',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-01-20 14:30:00',
            'created_at' => '2024-01-20 14:30:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ],
        [
            'id' => 3,
            'title' => '2024 İstanbul Ticari Gayrimenkul Piyasası: Analiz ve Öngörüler',
            'slug' => 'istanbul-ticari-gayrimenkul-piyasasi-2024',
            'content' => '2024 yılında İstanbul ticari gayrimenkul piyasası önemli değişiklikler yaşamaktadır.',
            'excerpt' => '2024 yılında İstanbul ticari gayrimenkul piyasasının detaylı analizi, bölgesel trendler ve yatırım fırsatları.',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1582407947304-fd86f028f716?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-01-25 09:15:00',
            'created_at' => '2024-01-25 09:15:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ],
        [
            'id' => 4,
            'title' => 'Emlak Sektöründe Dijital Dönüşümün Etkileri',
            'slug' => 'emlak-sektorunde-dijital-donusum',
            'content' => 'Dijital teknolojiler emlak sektöründe köklü değişiklikler yaratmaktadır.',
            'excerpt' => 'Emlak sektöründe dijital dönüşümün etkileri ve geleceğe yönelik beklentiler.',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-01-28 11:45:00',
            'created_at' => '2024-01-28 11:45:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ],
        [
            'id' => 5,
            'title' => 'İstanbul Ofis Arayışında Lokasyon Rehberi',
            'slug' => 'istanbul-ofis-arayisi-lokasyon-rehberi',
            'content' => 'İstanbul\'da ofis kiralama veya satın alma sürecinde doğru lokasyonu seçmek için detaylı rehber.',
            'excerpt' => 'İstanbul\'da ofis arayışında hangi bölgeleri tercih etmeli, nelere dikkat etmeli?',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-02-01 16:20:00',
            'created_at' => '2024-02-01 16:20:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ],
        [
            'id' => 6,
            'title' => 'Gayrimenkul Yatırımında Risk Yönetimi',
            'slug' => 'gayrimenkul-yatiriminda-risk-yonetimi',
            'content' => 'Gayrimenkul yatırımlarında karşılaşabileceğiniz riskler ve bunlara karşı alınabilecek önlemler.',
            'excerpt' => 'Gayrimenkul yatırımlarında risk faktörleri ve etkili risk yönetimi stratejileri.',
            'featured_image' => '',
            'auto_image' => 'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
            'published_at' => '2024-02-05 13:10:00',
            'created_at' => '2024-02-05 13:10:00',
            'author_name' => 'Gökhan Aydınlı',
            'status' => 'published'
        ]
    ];
    
    $total_posts = count($blog_posts);
    $total_pages = ceil($total_posts / $posts_per_page);
}

// Son yazılar da boşsa örnek veri oluştur
if (empty($recent_posts)) {
    $recent_posts = array_slice($blog_posts, 0, 3);
}

// Tarih formatlama fonksiyonu
function formatTurkishDate($date) {
    $months = [
        1 => 'OCA', 2 => 'ŞUB', 3 => 'MAR', 4 => 'NİS', 5 => 'MAY', 6 => 'HAZ',
        7 => 'TEM', 8 => 'AĞU', 9 => 'EYL', 10 => 'EKİ', 11 => 'KAS', 12 => 'ARA'
    ];
    $timestamp = strtotime($date);
    $day = date('j', $timestamp);
    $month = $months[(int)date('n', $timestamp)];
    return $day . ' ' . $month;
}

// Okuma süresi hesaplama fonksiyonu
function calculateReadTime($content) {
    $word_count = str_word_count(strip_tags($content));
    $reading_time = ceil($word_count / 200); // Dakikada 200 kelime
    return max(1, $reading_time); // En az 1 dakika
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="Gökhan Aydınlı Gayrimenkul Blog, Emlak Blog, Gayrimenkul Haberleri, İstanbul Emlak">
	<meta name="description" content="Gökhan Aydınlı Gayrimenkul Blog: Emlak sektöründen güncel haberler, piyasa analizleri ve uzman görüşleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com/blog.php">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Blog | Gökhan Aydınlı Gayrimenkul">
	<meta name='og:image' content='images/assets/blog-og.png'>
	<!-- For IE -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- For Resposive Device -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- For Window Tab Color -->
	<!-- Chrome, Firefox OS and Opera -->
	<meta name="theme-color" content="#0D1A1C">
	<!-- Windows Phone -->
	<meta name="msapplication-navbutton-color" content="#0D1A1C">
	<!-- iOS Safari -->
	<meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
	<title>Blog | Gökhan Aydınlı Gayrimenkul</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
	<!-- Main style sheet -->
	<link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
	<!-- responsive style sheet -->
	<link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">

	<!-- Fix Internet Explorer ______________________________________-->
	<!--[if lt IE 9]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
			<script src="vendor/html5shiv.js"></script>
			<script src="vendor/respond.js"></script>
		<![endif]-->
</head>

<body>
	<div class="main-page-wrapper">
		<!-- ===================================================
			Loading Transition
		==================================================== -->
		<div id="preloader">
			<div id="ctn-preloader" class="ctn-preloader">
				<div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
			</div>
		</div>


		
		<!-- ################### Search Modal ####################### -->
        <!-- Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content d-flex justify-content-center">
                    <form action="blog.php" method="GET">
                        <input type="text" name="search" placeholder="Blog yazılarında ara...">
                        <button type="submit"><i class="fa-light fa-arrow-right-long"></i></button>
                    </form>
                </div>
            </div>
        </div>


		
		<!-- 
		=============================================
			Theme Main Menu
		============================================== 
		-->
		<header class="theme-main-menu menu-overlay menu-style-six sticky-menu">
			<div class="inner-content gap-two">
				<div class="top-header position-relative">
					<div class="d-flex align-items-center">
						<div class="logo order-lg-0">
							<a href="index.php" class="d-flex align-items-center">
								<img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
							</a>
						</div>
						<!-- logo -->
						<div class="right-widget ms-auto me-3 me-lg-0 order-lg-3">
							<ul class="d-flex align-items-center style-none">
								<li class="d-none d-md-inline-block me-4">
                                    <a href="dashboard/add-property.php" class="btn-ten rounded-0"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
                                </li>
								<li>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="login-btn-two rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="fa-regular fa-lock"></i></a>
                                </li>
                                <li>
                                    <a href="#" data-bs-toggle="modal" data-bs-target="#searchModal" class="search-btn-one rounded-circle tran3s d-flex align-items-center justify-content-center"><i class="bi bi-search"></i></a>
                                </li>
							</ul>
						</div>
						<nav class="navbar navbar-expand-lg p0 ms-lg-5 order-lg-2">
							<button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
								data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
								aria-label="Toggle navigation">
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
										<a class="nav-link" href="blog.php">Blog</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" href="contact.php">İletişim</a>
									</li>
									<li class="nav-item">
										<a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
									</li>
									<li class="d-md-none ps-2 pe-2 mt-20">
										<a href="dashboard/add-property.php" class="btn-ten w-100 rounded-0"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
									</li>
								</ul>
							</div>
						</nav>
					</div>
				</div> <!--/.top-header-->
			</div> <!-- /.inner-content -->
		</header> 
		<!-- /.theme-main-menu -->



		<!-- 
		=============================================
			Inner Banner
		============================================== 
		-->
		<div class="inner-banner-two inner-banner z-1 pt-170 xl-pt-150 md-pt-130 pb-140 xl-pb-100 md-pb-80 position-relative" style="background-image: url(images/media/img_49.jpg);">
			<div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <h3 class="mb-45 xl-mb-30 md-mb-20">Gayrimenkul Blog</h3>
                        <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                            <li><a href="index.php">Ana Sayfa</a></li>
                            <li>/</li>
                            <li>Blog</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <p class="sub-heading">Emlak sektöründen güncel haberler, uzman görüşleri ve piyasa analizleri!</p>
                    </div>
                </div>
			</div>
		</div>
		<!-- /.inner-banner-two -->

		


		<!--
		=====================================================
			Blog Section Three
		=====================================================
		-->
		<div class="blog-section-three mt-130 xl-mt-100 mb-150 xl-mb-100">
			<div class="container container-large">
				<div class="row">
                    <div class="col-lg-8">
                        <div class="row gx-xxl-5">
                            <?php if (!empty($blog_posts)): ?>
                                <?php foreach ($blog_posts as $index => $post): 
                                    $published_date = $post['published_at'] ?? $post['created_at'];
                                    $reading_time = calculateReadTime($post['content']);
                                    $author_name = !empty($post['author_name']) ? $post['author_name'] : 'Gökhan Aydınlı';
                                    
                                    // Varsayılan resimler
                                    $default_images = [
                                        'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
                                        'https://images.unsplash.com/photo-1486406146926-c627a92ad1ab?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
                                        'https://images.unsplash.com/photo-1582407947304-fd86f028f716?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
                                        'https://images.unsplash.com/photo-1564013799919-ab600027ffc6?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
                                        'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-4.0.3&w=600&h=400&fit=crop',
                                        'https://images.unsplash.com/photo-1507003211169-0a1dd7228f2d?ixlib=rb-4.0.3&w=600&h=400&fit=crop'
                                    ];
                                    
                                    $featured_image = !empty($post['featured_image']) ? 'images/blog/' . $post['featured_image'] : $default_images[$index % count($default_images)];
                                    
                                    // Blog URL'lerini doğru dosyalara yönlendir
                                    $blog_files = [
                                        'yabanci-yatirimcilar-icin-istanbul-rehberi' => 'blog1.php',
                                        'ofis-kiralama-sozlesmesi-temel-maddeler' => 'blog2.php',
                                        'istanbul-ticari-gayrimenkul-piyasasi-2024' => 'blog3.php',
                                        'emlak-sektorunde-dijital-donusum' => 'blog4.php',
                                        'istanbul-ofis-arayisi-lokasyon-rehberi' => 'blog5.php'
                                    ];
                                    
                                    $blog_url = isset($blog_files[$post['slug']]) ? $blog_files[$post['slug']] : "blog-details.php?slug=" . urlencode($post['slug']);
                                ?>
                            <div class="col-md-6">
                                <article class="blog-meta-two tran3s position-relative z-1 mb-70 lg-mb-40 wow fadeInUp">
                                    <figure class="post-img position-relative m0" style="background-image: url(<?= htmlspecialchars($featured_image) ?>);">
                                        <a href="<?= $blog_url ?>" class="date"><?= formatTurkishDate($published_date) ?></a>
                                    </figure>
                                    <div class="post-data">
                                        <div class="post-info"><a href="<?= $blog_url ?>"><?= htmlspecialchars($author_name) ?> .</a> <?= $reading_time ?> dk</div>
                                        <div class="d-flex justify-content-between align-items-sm-center flex-wrap">
                                            <a href="<?= $blog_url ?>" class="blog-title"><h4><?= htmlspecialchars($post['title']) ?></h4></a>
                                            <a href="<?= $blog_url ?>" class="btn-four"><i class="bi bi-arrow-up-right"></i></a>
                                        </div>
                                    </div>
                                    <div class="hover-content tran3s">
                                        <a href="<?= $blog_url ?>" class="date"><?= formatTurkishDate($published_date) ?></a>
                                        <div class="post-data">
                                            <div class="post-info"><a href="<?= $blog_url ?>"><?= htmlspecialchars($author_name) ?> .</a> <?= $reading_time ?> dk</div>
                                            <div class="d-flex justify-content-between align-items-sm-center flex-wrap">
                                                <a href="<?= $blog_url ?>" class="blog-title"><h4><?= htmlspecialchars($post['title']) ?></h4></a>
                                            </div>
                                        </div>
                                        <a href="<?= $blog_url ?>" class="btn-four inverse rounded-circle"><i class="fa-thin fa-arrow-up-right"></i></a>
                                    </div>
                                    <!-- /.hover-content -->
                                </article>
                                <!-- /.blog-meta-two -->
                            </div>
                            <?php endforeach; ?>
                            <?php else: ?>
                                <div class="col-12">
                                    <div class="no-posts text-center py-5">
                                        <h3>Henüz blog yazısı bulunmuyor</h3>
                                        <p>Yakında yeni blog yazıları eklenecek. Takipte kalın!</p>
                                    </div>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                        <ul class="pagination-one square d-flex align-items-center style-none pt-30">
                            <?php 
                            // Önceki sayfa
                            if ($page > 1): ?>
                                <li><a href="?page=<?= $page - 1 ?>">« Önceki</a></li>
                            <?php endif; ?>
                            
                            <?php 
                            // Sayfa numaraları
                            $start = max(1, $page - 2);
                            $end = min($total_pages, $page + 2);
                            
                            for ($i = $start; $i <= $end; $i++): ?>
                                <li<?= ($i == $page) ? ' class="active"' : '' ?>>
                                    <a href="?page=<?= $i ?>"><?= $i ?></a>
                                </li>
                            <?php endfor; ?>
                            
                            <?php if ($end < $total_pages): ?>
                                <li>....</li>
                                <li><a href="?page=<?= $total_pages ?>"><?= $total_pages ?></a></li>
                            <?php endif; ?>
                            
                            <?php 
                            // Sonraki sayfa
                            if ($page < $total_pages): ?>
                                <li class="ms-2">
                                    <a href="?page=<?= $page + 1 ?>" class="d-flex align-items-center">
                                        Sonraki <i class="fa-solid fa-arrow-right ms-2"></i>
                                    </a>
                                </li>
                            <?php endif; ?>
                        </ul>
                        <?php endif; ?>
                    </div>
                    <div class="col-lg-4">
						<div class="blog-sidebar dot-bg ms-xxl-5 md-mt-60">
							<div class="search-form bg-white mb-30">
                                <form action="blog.php" method="GET" class="position-relative">
                                    <input type="text" name="search" placeholder="Ara...">
                                    <button type="submit"><i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>
                                </form>
                            </div>
                            <!-- /.search-form -->

							<div class="categories bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Kategoriler</h5>
								<ul class="style-none">
                                    <li><a href="blog.php?category=Piyasa">Piyasa Analizi (12)</a></li>
                                    <li><a href="blog.php?category=Yatirim">Yatırım Tavsiyeleri (8)</a></li>
                                    <li><a href="blog.php?category=Yasal">Yasal Bilgiler (6)</a></li>
                                    <li><a href="blog.php?category=Haberler">Emlak Haberleri (15)</a></li>
                                    <li><a href="blog.php?category=Lifestyle">Lifestyle (4)</a></li>
                                    <li><a href="blog.php?category=Genel">Genel (11)</a></li>
                                </ul>
							</div>
							<!-- /.categories -->

							<div class="recent-news bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Son Yazılar</h5>
								<?php if (!empty($recent_posts)): ?>
                                    <?php foreach($recent_posts as $recent_post): 
                                        // Blog URL'lerini doğru dosyalara yönlendir
                                        $blog_files = [
                                            'yabanci-yatirimcilar-icin-istanbul-rehberi' => 'blog1.php',
                                            'ofis-kiralama-sozlesmesi-temel-maddeler' => 'blog2.php',
                                            'istanbul-ticari-gayrimenkul-piyasasi-2024' => 'blog3.php',
                                            'emlak-sektorunde-dijital-donusum' => 'blog4.php',
                                            'istanbul-ofis-arayisi-lokasyon-rehberi' => 'blog5.php'
                                        ];
                                        
                                        $recent_url = isset($blog_files[$recent_post['slug']]) ? $blog_files[$recent_post['slug']] : "blog-details.php?slug=" . urlencode($recent_post['slug']);
                                        $recent_image = !empty($recent_post['featured_image']) ? 'images/blog/' . $recent_post['featured_image'] : $recent_post['auto_image'];
                                        $recent_date = $recent_post['published_at'] ?? $recent_post['created_at'];
                                    ?>
                                <div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="images/lazy.svg" data-src="<?= htmlspecialchars($recent_image) ?>" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="<?= $recent_url ?>" class="title tran3s"><?= htmlspecialchars($recent_post['title']) ?></a></h4>
                                        <div class="date"><?= date('d M, Y', strtotime($recent_date)) ?></div>
                                    </div>
                                </div>
                                <?php endforeach; ?>
                                <?php endif; ?>
							</div>
							<!-- /.recent-news -->
                            <div class="keyword bg-white bg-wrapper">
								<h5 class="mb-20">Etiketler</h5>
								<ul class="style-none d-flex flex-wrap">
                                    <li><a href="blog.php?tag=İstanbul">İstanbul</a></li>
                                    <li><a href="blog.php?tag=Gayrimenkul">Gayrimenkul</a></li>
                                    <li><a href="blog.php?tag=Yatırım">Yatırım</a></li>
                                    <li><a href="blog.php?tag=Emlak">Emlak</a></li>
                                    <li><a href="blog.php?tag=Piyasa">Piyasa</a></li>
                                    <li><a href="blog.php?tag=Analiz">Analiz</a></li>
                                    <li><a href="blog.php?tag=2024">2024</a></li>
                                    <li><a href="blog.php?tag=Ticari">Ticari</a></li>
                                </ul>
							</div>
							<!-- /.keyword -->
						</div>
						<!-- /.theme-sidebar-one -->
					</div>
                </div>
			</div>
		</div>
		<!-- /.blog-section-three -->

		


		<!--
		=====================================================
			Fancy Banner Two
		=====================================================
		-->
		<div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
							<h3 class="text-white m0">Gayrimenkul <span>Yolculuğunuza<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Başlayın.</h3>
						</div>
						<!-- /.title-one -->
					</div>
					<div class="col-lg-6">
						<div class="form-wrapper me-auto ms-auto me-lg-0">
							<form action="contact.php" method="POST">
								<input type="email" name="email" placeholder="Email adresiniz" class="rounded-0" required>
								<button type="submit" class="rounded-0">Başlayın</button>
							</form>
							<div class="fs-16 mt-10 text-white">Zaten üye misiniz? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın.</a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /.fancy-banner-two -->




		<!--
		=====================================================
			Footer Four
		=====================================================
		-->
		<div class="footer-four position-relative z-1">
			<div class="container container-large">
				<div class="bg-wrapper position-relative z-1">
					<div class="row">
						<div class="col-xxl-3 col-lg-4 mb-60">
							<div class="footer-intro">
								<div class="logo mb-20">
									<a href="index.php">
										<img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:60px; width:auto;">
									</a>
								</div> 
								<!-- logo -->
								<p class="mb-30 xs-mb-20">Gayrimenkul sektöründe 17 yıllık deneyim ile hizmetinizdeyiz. İstanbul'un her bölgesinde güvenilir ve profesyonel hizmet.</p>
								<a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
								<ul class="style-none d-flex align-items-center social-icon">
									<li><a href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-twitter"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-linkedin-in"></i></a></li>
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
								</ul>
							</div>
						</div>
						<div class="col-lg-3 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Hizmetler</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="ofiskiralama.php">Ofis Kiralama</a></li>
									<li><a href="dukkankiralama.php">Dükkan Kiralama</a></li>
									<li><a href="portfoy.php">Portföy Yönetimi</a></li>
									<li><a href="contact.php">Danışmanlık</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-2 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">İletişim</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="tel:+905XXXXXXXXX">+90 5XX XXX XX XX</a></li>
									<li><a href="mailto:info@gokhanaydinli.com">Email</a></li>
									<li><a href="contact.php">Adres</a></li>
									<li><a href="contact.php">Harita</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- /.bg-wrapper -->
				<div class="bottom-footer">
					<p class="m0 text-center fs-16">Copyright ©2024 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
				</div>
			</div>
			<img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
		</div> <!-- /.footer-four -->


		<!-- ################### Login Modal ####################### -->
        <!-- Modal -->
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
										<h2>Tekrar Hoş Geldiniz!</h2>
										<p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#" onclick="$('.nav-link:last').click()">Kayıt olun</a></p>
									</div>
									<form action="login.php" method="POST">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>Email*</label>
													<input type="email" name="email" placeholder="email@example.com" required>
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
													<a href="#">Şifremi unuttum?</a>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">Giriş Yap</button>
											</div>
										</div>
									</form>
								</div>
								<!-- /.tab-pane -->
								<div class="tab-pane" role="tabpanel" id="fc2">
									<div class="text-center mb-20">
										<h2>Kayıt Ol</h2>
										<p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="$('.nav-link:first').click()">Giriş yapın</a></p>
									</div>
									<form action="register.php" method="POST">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>Ad Soyad*</label>
													<input type="text" name="name" placeholder="Ad Soyad" required>
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>Email*</label>
													<input type="email" name="email" placeholder="email@example.com" required>
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
														<input type="checkbox" id="remember2" name="terms" required>
														<label for="remember2">"Kayıt Ol" butonuna tıklayarak <a href="#">Kullanım Koşulları</a> ve <a href="#">Gizlilik Politikası</a>'nı kabul edersiniz</label>
													</div>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">Kayıt Ol</button>
											</div>
										</div>
									</form>
								</div>
								<!-- /.tab-pane -->
							</div>
							
							<div class="d-flex align-items-center mt-30 mb-10">
								<div class="line"></div>
								<span class="pe-3 ps-3 fs-6">VEYA</span>
								<div class="line"></div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
										<img src="images/icon/google.png" alt="">
										<span class="ps-3">Google ile kayıt</span>
									</a>
								</div>
								<div class="col-sm-6">
									<a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
										<img src="images/icon/facebook.png" alt="">
										<span class="ps-3">Facebook ile kayıt</span>
									</a>
								</div>
							</div>
						</div>
						<!-- /.form-wrapper -->
                    </div>
                    <!-- /.user-data-form -->
                </div>
            </div>
        </div>



		<button class="scroll-top">
			<i class="bi bi-arrow-up-short"></i>
		</button>




		<!-- Optional JavaScript _____________________________  -->

		<!-- jQuery first, then Bootstrap JS -->
		<!-- jQuery -->
		<script src="vendor/jquery.min.js"></script>
		<!-- Bootstrap JS -->
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- WOW js -->
		<script src="vendor/wow/wow.min.js"></script>
		<!-- Slick Slider -->
		<script src="vendor/slick/slick.min.js"></script>
		<!-- Fancybox -->
		<script src="vendor/fancybox/fancybox.umd.js"></script>
		<!-- Lazy -->
		<script src="vendor/jquery.lazy.min.js"></script>
		<!-- js Counter -->
		<script src="vendor/jquery.counterup.min.js"></script>
		<script src="vendor/jquery.waypoints.min.js"></script>
		<!-- Nice Select -->
		<script src="vendor/nice-select/jquery.nice-select.min.js"></script>
		<!-- validator js -->
		<script src="vendor/validator.js"></script>
        <!-- isotop -->
		<script  src="vendor/isotope.pkgd.min.js"></script>

		<!-- Theme js -->
		<script src="js/theme.js"></script>
	</div> <!-- /.main-page-wrapper -->
</body>

</html>
