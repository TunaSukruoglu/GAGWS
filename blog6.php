<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında
include 'includes/session-check.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="gayrimenkul değerleme, emlak değerleme yöntemleri, gayrimenkul analizi, emlak uzmanı, Gökhan Aydınlı">
    <meta name="description" content="Gayrimenkul değerleme yöntemleri hakkında kapsamlı rehber. Emsal karşılaştırma, maliyet yaklaşımı ve gelir yaklaşımı gibi temel değerleme tekniklerini öğrenin.">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Gayrimenkul Değerleme Yöntemleri: Kapsamlı Rehber">
    <meta name='og:image' content='images/assets/real-estate-valuation-og.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Gayrimenkul Değerleme Yöntemleri: Kapsamlı Rehber - Gökhan Aydınlı Blog</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <!-- FontAwesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Modern Blog CSS -->
    <style>
        /* Modern Blog Card Styles */
        .blog-meta-two {
            background: #fff;
            border-radius: 20px;
            overflow: hidden;
            box-shadow: 0 8px 30px rgba(0,0,0,0.08);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            position: relative;
            height: 100%;
            display: flex;
            flex-direction: column;
        }

        .blog-meta-two:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 60px rgba(0,0,0,0.15);
        }

        .post-img {
            height: 280px;
            background-size: cover !important;
            background-position: center !important;
            background-repeat: no-repeat !important;
            position: relative;
            overflow: hidden;
        }

        .post-img::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.1), rgba(59, 130, 246, 0.1));
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .blog-meta-two:hover .post-img::before {
            opacity: 1;
        }

        .post-img .date {
            position: absolute;
            top: 20px;
            left: 20px;
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
            color: #2563eb;
            padding: 8px 16px;
            border-radius: 25px;
            font-weight: 700;
            font-size: 14px;
            text-decoration: none;
            transition: all 0.3s ease;
            z-index: 10;
        }

        .post-img .date:hover {
            background: #2563eb;
            color: white;
            transform: scale(1.05);
        }

        .post-data {
            padding: 30px;
            flex-grow: 1;
            display: flex;
            flex-direction: column;
        }

        .post-info {
            color: #6b7280;
            font-size: 14px;
            margin-bottom: 15px;
            display: flex;
            align-items: center;
            gap: 8px;
        }

        .post-info a {
            color: #2563eb;
            text-decoration: none;
            font-weight: 600;
            transition: color 0.3s ease;
        }

        .post-info a:hover {
            color: #1d4ed8;
        }

        .blog-title {
            margin-bottom: 15px;
            flex-grow: 1;
        }

        .blog-title h4 {
            color: #1f2937;
            font-size: 20px;
            font-weight: 700;
            line-height: 1.4;
            margin: 0;
            transition: color 0.3s ease;
        }

        .blog-title a {
            text-decoration: none;
            color: inherit;
        }

        .blog-meta-two:hover .blog-title h4 {
            color: #2563eb;
        }

        .btn-four {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: #f8fafc;
            border: 2px solid #e2e8f0;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #64748b;
            text-decoration: none;
            transition: all 0.3s ease;
            margin-top: auto;
            align-self: flex-end;
        }

        .btn-four:hover {
            background: #2563eb;
            border-color: #2563eb;
            color: white;
            transform: scale(1.1);
        }

        /* Hover Content - Gizli içerik */
        .hover-content {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.95), rgba(59, 130, 246, 0.95));
            color: white;
            padding: 30px;
            display: flex;
            flex-direction: column;
            justify-content: space-between;
            opacity: 0;
            transform: translateY(20px);
            transition: all 0.4s cubic-bezier(0.4, 0, 0.2, 1);
            pointer-events: none;
        }

        .blog-meta-two:hover .hover-content {
            opacity: 1;
            transform: translateY(0);
            pointer-events: all;
        }

        .hover-content .date {
            background: rgba(255, 255, 255, 0.2);
            color: white;
            border: 1px solid rgba(255, 255, 255, 0.3);
        }

        .hover-content .post-info a {
            color: rgba(255, 255, 255, 0.9);
        }

        .hover-content .blog-title h4 {
            color: white;
        }

        .hover-content .btn-four.inverse {
            background: white;
            color: #2563eb;
            border-color: white;
        }

        .hover-content .btn-four.inverse:hover {
            background: rgba(255, 255, 255, 0.9);
            transform: scale(1.1);
        }

        /* Sidebar Styles */
        .blog-sidebar {
            background: transparent;
        }

        .blog-sidebar .bg-wrapper {
            background: #fff;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 8px 30px rgba(0,0,0,0.06);
            margin-bottom: 30px;
        }

        .blog-sidebar h5 {
            font-size: 20px;
            font-weight: 700;
            color: #1f2937;
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid #e5e7eb;
        }

        .search-form {
            position: relative;
        }

        .search-form input {
            width: 100%;
            padding: 15px 50px 15px 20px;
            border: 2px solid #e5e7eb;
            border-radius: 15px;
            font-size: 16px;
            transition: all 0.3s ease;
        }

        .search-form input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 0 4px rgba(37, 99, 235, 0.1);
            outline: none;
        }

        .search-form button {
            position: absolute;
            right: 15px;
            top: 50%;
            transform: translateY(-50%);
            background: none;
            border: none;
            color: #6b7280;
            font-size: 18px;
            cursor: pointer;
            transition: color 0.3s ease;
        }

        .search-form button:hover {
            color: #2563eb;
        }

        .categories ul {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .categories li {
            margin-bottom: 12px;
        }

        .categories a {
            color: #4b5563;
            text-decoration: none;
            padding: 10px 0;
            display: block;
            border-bottom: 1px solid #f3f4f6;
            transition: all 0.3s ease;
        }

        .categories a:hover {
            color: #2563eb;
            padding-left: 10px;
        }

        .recent-news .news-block {
            padding-bottom: 20px;
            margin-bottom: 20px;
            border-bottom: 1px solid #f3f4f6;
        }

        .recent-news .news-block:last-child {
            border-bottom: none;
            margin-bottom: 0;
            padding-bottom: 0;
        }

        .recent-news img {
            width: 80px;
            height: 60px;
            object-fit: cover;
            border-radius: 10px;
        }

        .recent-news .title {
            color: #1f2937;
            font-weight: 600;
            font-size: 15px;
            line-height: 1.4;
            text-decoration: none;
            transition: color 0.3s ease;
        }

        .recent-news .title:hover {
            color: #2563eb;
        }

        .recent-news .date {
            color: #6b7280;
            font-size: 13px;
        }

        .keyword ul {
            list-style: none;
            padding: 0;
            margin: 0;
            display: flex;
            flex-wrap: wrap;
            gap: 10px;
        }

        .keyword a {
            background: #f8fafc;
            color: #4b5563;
            padding: 8px 16px;
            border-radius: 20px;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .keyword a:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        /* Pagination */
        .pagination-one {
            justify-content: center;
            gap: 10px;
            margin-top: 50px;
        }

        .pagination-one li {
            list-style: none;
        }

        .pagination-one a {
            display: flex;
            align-items: center;
            justify-content: center;
            width: 45px;
            height: 45px;
            border-radius: 12px;
            background: #f8fafc;
            color: #4b5563;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .pagination-one .active a,
        .pagination-one a:hover {
            background: #2563eb;
            color: white;
            transform: translateY(-2px);
        }

        /* Inner Banner */
        .inner-banner-two {
            background: linear-gradient(135deg, rgba(37, 99, 235, 0.9), rgba(59, 130, 246, 0.9)), url('https://images.unsplash.com/photo-1560472354-b33ff0c44a43?ixlib=rb-4.0.3&w=1920&h=600&fit=crop') center/cover;
            color: white;
            position: relative;
        }

        .inner-banner-two h3 {
            font-size: 3rem;
            font-weight: 800;
            margin-bottom: 20px;
        }

        .theme-breadcrumb {
            background: rgba(255, 255, 255, 0.1);
            backdrop-filter: blur(10px);
            padding: 10px 20px;
            border-radius: 25px;
            display: inline-flex;
            align-items: center;
            gap: 8px;
        }

        .theme-breadcrumb a {
            color: rgba(255, 255, 255, 0.9);
            text-decoration: none;
        }

        .theme-breadcrumb a:hover {
            color: white;
        }

        .sub-heading {
            font-size: 1.2rem;
            opacity: 0.9;
            line-height: 1.6;
        }

        /* Modal Styles */
        .user-data-form {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(20px);
            border-radius: 20px;
            border: 1px solid #e5e7eb;
            padding: 40px;
            box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
        }

        .nav-tabs {
            border-bottom: 2px solid #e5e7eb;
        }

        .nav-link {
            border: none;
            border-radius: 0;
            color: #6b7280;
            font-weight: 600;
            padding: 15px 25px;
            transition: all 0.3s ease;
        }

        .nav-link.active {
            color: #2563eb;
            border-bottom: 2px solid #2563eb;
        }

        .input-group-meta {
            position: relative;
            margin-bottom: 25px;
        }

        .input-group-meta label {
            position: absolute;
            top: -10px;
            left: 15px;
            font-size: 12px;
            color: #2563eb;
            background: #fff;
            padding: 0 5px;
        }

        .input-group-meta input {
            border: 1px solid #e5e7eb;
            border-radius: 10px;
            padding: 15px 20px;
            font-size: 16px;
            color: #1f2937;
            transition: all 0.3s ease;
            width: 100%;
        }

        .input-group-meta input:focus {
            border-color: #2563eb;
            box-shadow: 0 0 5px rgba(37, 99, 235, 0.3);
            outline: none;
        }

        .btn-two {
            background: #2563eb;
            color: #fff;
            padding: 12px 20px;
            border-radius: 10px;
            font-size: 16px;
            font-weight: 600;
            transition: all 0.3s ease;
            border: none;
            width: 100%;
        }

        .btn-two:hover {
            background: #1d4ed8;
        }

        /* Tech Statistics Box */
        .tech-stats-box {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            border-radius: 20px;
            padding: 30px;
            margin: 30px 0;
        }

        .tech-stats-box h6 {
            color: rgba(255, 255, 255, 0.9);
            margin-bottom: 20px;
        }

        .stat-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 10px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
        }

        .stat-item:last-child {
            border-bottom: none;
        }

        .stat-label {
            font-size: 14px;
            opacity: 0.9;
        }

        .stat-value {
            font-weight: 700;
            font-size: 16px;
        }

        /* Interactive Elements */
        .interactive-chart {
            background: #f8fafc;
            border-radius: 15px;
            padding: 20px;
            margin: 20px 0;
            border: 1px solid #e2e8f0;
        }

        .progress-bar-custom {
            background: #e2e8f0;
            border-radius: 10px;
            height: 8px;
            overflow: hidden;
            margin: 10px 0;
        }

        .progress-fill {
            height: 100%;
            background: linear-gradient(90deg, #2563eb, #3b82f6);
            border-radius: 10px;
            transition: width 2s ease;
        }

        /* Responsive */
        @media (max-width: 768px) {
            .inner-banner-two h3 {
                font-size: 2rem;
            }
            
            .blog-meta-two {
                margin-bottom: 30px;
            }
            
            .post-img {
                height: 220px;
            }
            
            .post-data {
                padding: 20px;
            }
            
            .blog-sidebar {
                margin-top: 50px;
            }
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
        <header class="theme-main-menu menu-overlay menu-style-six sticky-menu">
         			<div class="inner-content gap-one">
				<div class="top-header position-relative">
					<div class="d-flex align-items-center justify-content-between">
						<div class="logo order-lg-0">
							<a href="index.html" class="d-flex align-items-center">
								<img src="images/logoSiyah.png" alt="">
							</a>
						</div>
						<!-- logo -->
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
									<li class="d-block d-lg-none"><div class="logo"><a href="index.html" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="index.php" >Ana Sayfa</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="hakkimizda.php" >Hakkımızda</a>
										</a>
						
									</li>

                                    <li class="nav-item dropdown">
										<a class="nav-link" href="portfoy.php">Portföy</a>
										</a>
						
									</li>

                                    <li class="nav-item dashboard-menu">
										<a class="nav-link" href="blog.php">Blog</a>
										</a>
						
									</li>
                                           <li class="nav-item dropdown">
										<a class="nav-link" href="contact.php">İletişim</a>
										</a>
						
									</li>
                                           <li class="nav-item dropdown">
										<a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
										</a>
						
									</li>
									<li class="d-md-none ps-2 pe-2 mt-20">
										<a href="dashboard/add-property.html" class="btn-two w-100" target="_blank"><span>Add Listing</span> <i class="fa-thin fa-arrow-up-right"></i></a>
									</li>
								</ul>
							</div>
						</nav>
					</div>
				</div> <!--/.top-header-->
			</div> <!-- /.inner-content -->
        </header>

        <!-- İç Banner -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Gayrimenkul Değerleme</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="blog.php">Blog Anasayfa</a></li>
                    <li>/</li>
                    <li>Gayrimenkul Değerleme</li>
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
                            <div class="post-info"><a href="agent_details.html">Gökhan Aydınlı .</a> 18 dk okuma</div>
                            <h3 class="blog-title">Gayrimenkul Değerleme Yöntemleri: Kapsamlı Rehber</h3>
                        </div>
                    </div>
                </div>
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-post-meta">
                            <figure class="post-img position-relative m0" style="background-image: url(https://images.unsplash.com/photo-1584824486509-112e4181ff6b?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80);">
                                <div class="fw-500 date d-inline-block">25 HZRN</div>
                            </figure>
                            <div class="post-data pt-50 md-pt-30">
                                <p>Emlak sektörü, teknolojik devrim niteliğinde bir dönüşüm yaşıyor. 17 yıllık sektör deneyimimde, hiçbir dönemde bu kadar radikal değişimlere tanık olmamıştım. Dijital dönüşüm sadece iş yapış şekillerimizi değil, müşteri beklentilerini ve sektörün geleceğini tamamen yeniden şekillendiriyor.</p>
                                
                                <p>Bu kapsamlı analizde, emlak sektöründeki dijital dönüşümün her boyutunu, mevcut teknolojileri ve gelecek projeksiyonlarını derinlemesine inceleyeceğiz. PropTech'ten blockchain'e, yapay zekadan sanal gerçekliğe kadar tüm yenilikleri ele alacağız.</p>

                                <div class="tech-stats-box">
                                    <h6><i class="fas fa-chart-line me-2"></i>2024 Emlak Teknolojileri İstatistikleri</h6>
                                    <div class="stat-item">
                                        <span class="stat-label">PropTech Yatırımları</span>
                                        <span class="stat-value">24.8 Milyar $</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Sanal Tur Kullanımı</span>
                                        <span class="stat-value">%67 Artış</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">AI Destekli Değerleme</span>
                                        <span class="stat-value">%89 Doğruluk</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Blockchain İşlemleri</span>
                                        <span class="stat-value">%156 Artış</span>
                                    </div>
                                </div>
                                
                                <div class="quote-wrapper">
                                    <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"><img src="images/lazy.svg" data-src="images/icon/icon_67.svg" alt="" class="lazy-img"></div>
                                    <div class="row">
                                        <div class="col-xxl-10 col-xl-11 col-lg-12 col-md-9 m-auto">
                                            <h4>"Dijital dönüşüm, emlak sektöründe sadece bir trend değil, kalıcı bir değişim. Bu dönüşüme ayak uyduramayanlar, pazarda var olmakta zorlanacaklar."</h4>
                                        </div>
                                    </div>
                                    <h6>Gökhan Aydınlı. <span>Gayrimenkul Uzmanı & PropTech Analisti</span></h6>
                                </div>
                                
                                <h5>Dijital Dönüşümün Temel Dinamikleri</h5>
                                <p>Emlak sektöründeki dijital dönüşüm, birden fazla faktörün bir araya gelmesiyle gerçekleşiyor. Bu değişimin arkasındaki itici güçleri anlamak, geleceğe yönelik stratejiler geliştirmek için kritik önem taşıyor.</p>
                                
                                <h6>Pandemi Sonrası Değişen Davranış Kalıpları</h6>
                                <p>COVID-19 pandemisi, emlak sektöründe dijital dönüşümü 5-10 yıl öne çekti. Uzaktan çalışma kültürü, contactless işlemler ve dijital-first yaklaşımlar kalıcı hale geldi.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Sanal Tur Zorunluluğu:</strong> Pandemi döneminde %300 artış</li>
                                    <li><strong>Dijital İmzalama:</strong> Belge işlemlerinde %80 dijitalleşme</li>
                                    <li><strong>Online Pazarlama:</strong> Geleneksel reklamlardan dijital kanallara geçiş</li>
                                    <li><strong>Uzaktan Danışmanlık:</strong> Video konferans ile müşteri hizmetleri</li>
                                </ul>
                                
                                <h6>Yeni Nesil Müşteri Beklentileri</h6>
                                <p>Milenyum ve Z kuşağı müşteriler, emlak işlemlerinde de e-ticaret deneyimi bekliyor. Anında bilgi, şeffaf süreçler ve kullanıcı dostu arayüzler artık temel gereksinimler.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>7/24 Erişilebilirlik:</strong> Her an bilgi erişimi</li>
                                    <li><strong>Mobil Optimizasyon:</strong> Akıllı telefon uyumluluğu</li>
                                    <li><strong>Anlık Bilgilendirme:</strong> Real-time fiyat güncellemeleri</li>
                                    <li><strong>Kişiselleştirme:</strong> AI destekli öneriler</li>
                                </ul>
                                
                                <h5>PropTech Ekosistemin Yükselişi</h5>
                                <p>Property Technology (PropTech), emlak sektöründeki teknolojik yeniliklerin genel adı. Bu ekosistem, startuplardan büyük teknoloji şirketlerine kadar geniş bir yelpazede faaliyet gösteriyor.</p>
                                
                                <h6>PropTech'in Ana Kategorileri</h6>
                                
                                <p><strong>1. Sanal ve Artırılmış Gerçeklik (VR/AR)</strong></p>
                                <p>VR ve AR teknolojileri, emlak pazarlamasında devrim yarattı. Müşteriler, fiziksel olarak bulunmadan mülkleri detaylı şekilde inceleyebiliyor.</p>
                                
                                <div class="interactive-chart">
                                    <h6 class="mb-3">VR/AR Kullanım Oranları</h6>
                                    <div class="mb-2">
                                        <small>Sanal Tur Kullanımı</small>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill" style="width: 73%"></div>
                                        </div>
                                        <small class="text-muted">73%</small>
                                    </div>
                                    <div class="mb-2">
                                        <small>AR Mobilya Yerleştirme</small>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill" style="width: 45%"></div>
                                        </div>
                                        <small class="text-muted">45%</small>
                                    </div>
                                    <div class="mb-2">
                                        <small>360° Fotoğraf</small>
                                        <div class="progress-bar-custom">
                                            <div class="progress-fill" style="width: 67%"></div>
                                        </div>
                                        <small class="text-muted">67%</small>
                                    </div>
                                </div>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Matterport Teknolojisi:</strong> 3D tarama ve sanal tur</li>
                                    <li><strong>AR Staging:</strong> Boş mekanlara sanal mobilya yerleştirme</li>
                                    <li><strong>Virtual Staging:</strong> Fotoğraflarda dijital dekorasyon</li>
                                    <li><strong>Drone Görüntüleme:</strong> Havadan mülk tanıtımları</li>
                                </ul>
                                
                                <p><strong>2. Yapay Zeka ve Makine Öğrenmesi</strong></p>
                                <p>AI teknolojileri, emlak değerlemesinden müşteri hizmetlerine kadar birçok alanda kullanılıyor.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Otomatik Değerleme Modelleri (AVM):</strong> Anlık fiyat tahminleri</li>
                                    <li><strong>Chatbot'lar:</strong> 7/24 müşteri desteği</li>
                                    <li><strong>Predictive Analytics:</strong> Pazar trend tahminleri</li>
                                    <li><strong>Lead Scoring:</strong> Potansiyel müşteri analizi</li>
                                </ul>
                                
                                <p><strong>3. Big Data ve Analytics</strong></p>
                                <p>Büyük veri analitiği, emlak piyasasının dinamiklerini anlamamızı devrimsel şekilde değiştirdi.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Piyasa Analizi:</strong> Gerçek zamanlı fiyat trendleri</li>
                                    <li><strong>Demografik Analiz:</strong> Hedef kitle segmentasyonu</li>
                                    <li><strong>Risk Değerlendirme:</strong> Yatırım risklerinin hesaplanması</li>
                                    <li><strong>Peformans Ölçümü:</strong> Portföy yönetimi optimizasyonu</li>
                                </ul>
                                
                                <div class="img-meta"><img src="https://images.unsplash.com/photo-1551288049-bebda4e38f71?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Emlak teknolojileri" class="lazy-img w-100"></div>
                                <div class="img-caption">Yapay zeka ve büyük veri analitiği emlak sektörünü dönüştürüyor</div>
                                
                                <h5>Blockchain ve Kripto Para Entegrasyonu</h5>
                                <p>Blockchain teknolojisi, emlak işlemlerinde şeffaflık, güvenlik ve hız sağlayarak sektörün en büyük sorunlarından birini çözüyor: güven.</p>
                                
                                <h6>Blockchain'in Emlak Sektöründeki Uygulamaları</h6>
                                
                                <p><strong>Akıllı Sözleşmeler (Smart Contracts)</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Otomatik İcra:</strong> Şartlar yerine geldiğinde otomatik ödeme</li>
                                    <li><strong>Aracı Eliminasyonu:</strong> Noterlerin rolünün azalması</li>
                                    <li><strong>Şeffaflık:</strong> Tüm işlemlerin kayıt altına alınması</li>
                                    <li><strong>Güvenlik:</strong> Değiştirilemez dijital kayıtlar</li>
                                </ul>
                                
                                <p><strong>Tokenization (Dijital Varlık Dönüşümü)</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Fraksiyonel Sahiplik:</strong> Gayrimenkulün parçalı satışı</li>
                                    <li><strong>Likidite Artışı:</strong> Gayrimenkul yatırımlarının kolayca el değiştirmesi</li>
                                    <li><strong>Global Erişim:</strong> Dünya çapından yatırımcı erişimi</li>
                                    <li><strong>Düşük Giriş Bariyeri:</strong> Küçük miktarlarla yatırım imkanı</li>
                                </ul>
                                
                                <p><strong>NFT'ler ve Dijital Mülkiyet</strong></p>
                                <p>Non-Fungible Token'lar (NFT), gayrimenkul mülkiyetinin dijital olarak temsil edilmesinde yeni bir boyut açıyor.</p>
                                
                                <h5>IoT ve Akıllı Ev Teknolojileri</h5>
                                <p>Internet of Things (IoT), gayrimenkul yönetiminde ve yaşam kalitesinde devrim yaratıyor. Akıllı ev teknolojileri artık lüks değil, gereklilik haline geliyor.</p>
                                
                                <h6>Akıllı Ev Sistemleri</h6>
                                
                                <p><strong>Güvenlik Sistemleri</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Yüz Tanıma:</strong> Giriş kontrol sistemleri</li>
                                    <li><strong>Hareket Sensörleri:</strong> Otomatik aydınlatma ve güvenlik</li>
                                    <li><strong>Akıllı Kilitler:</strong> Uzaktan erişim kontrolü</li>
                                    <li><strong>Güvenlik Kameraları:</strong> Canlı izleme ve kayıt</li>
                                </ul>
                                
                                <p><strong>Enerji Yönetimi</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Akıllı Termostatlar:</strong> Otomatik ısı kontrolü</li>
                                    <li><strong>LED Aydınlatma:</strong> Enerji tasarruflu sistemler</li>
                                    <li><strong>Solar Panel Entegrasyonu:</strong> Yenilenebilir enerji</li>
                                    <li><strong>Enerji Monitöring:</strong> Tüketim takibi ve optimizasyon</li>
                                </ul>
                                
                                <h6>Akıllı Bina Yönetimi</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Prediktif Bakım:</strong> Arızalardan önce müdahale</li>
                                    <li><strong>Uzaktan İzleme:</strong> Merkezi kontrol sistemleri</li>
                                    <li><strong>Otomatik Raporlama:</strong> Performans analizleri</li>
                                    <li><strong>Tenant Deneyimi:</strong> Mobil uygulama entegrasyonu</li>
                                </ul>
                                
                                <h5>Dijital Pazarlama ve CRM Dönüşümü</h5>
                                <p>Emlak pazarlaması, geleneksel yöntemlerden dijital stratejilere doğru radikal bir dönüşüm yaşıyor.</p>
                                
                                <h6>Sosyal Medya ve İçerik Pazarlama</h6>
                                
                                <p><strong>Video Pazarlama</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Drone Videoları:</strong> Cinematic mülk tanıtımları</li>
                                    <li><strong>Timelapse:</strong> İnşaat süreci gösterimi</li>
                                    <li><strong>Live Streaming:</strong> Canlı mülk turları</li>
                                    <li><strong>360° Videolar:</strong> İmmersive deneyimler</li>
                                </ul>
                                
                                <p><strong>Sosyal Medya Stratejileri</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Instagram Stories:</strong> Günlük mülk paylaşımları</li>
                                    <li><strong>LinkedIn Marketing:</strong> B2B ticari gayrimenkul</li>
                                    <li><strong>TikTok Presence:</strong> Gen Z erişimi</li>
                                    <li><strong>YouTube Kanalları:</strong> Uzun form içerikler</li>
                                </ul>
                                
                                <h6>CRM ve Lead Management</h6>
                                <p>Müşteri İlişkileri Yönetimi (CRM), emlak sektöründe AI destekli sistemlere doğru evrimleşiyor.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Otomatik Lead Scoring:</strong> Potansiyel müşteri sıralaması</li>
                                    <li><strong>Behavioral Tracking:</strong> Müşteri davranış analizi</li>
                                    <li><strong>Personalization Engine:</strong> Kişiselleştirilmiş öneriler</li>
                                    <li><strong>Multi-channel Communication:</strong> Omnichannel müşteri deneyimi</li>
                                </ul>
                                
                                <h5>Fintech ve Emlak Finansmanı</h5>
                                <p>Finansal teknolojiler (Fintech), emlak finansmanında geleneksel bankacılık modellerini zorlayarak alternatif çözümler sunuyor.</p>
                                
                                <h6>Dijital Kredi Platformları</h6>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Online Mortgage:</strong> Dijital ipotek başvuruları</li>
                                    <li><strong>AI Underwriting:</strong> Yapay zeka destekli kredi değerlendirme</li>
                                    <li><strong>Instant Approval:</strong> Anlık kredi onayları</li>
                                    <li><strong>Alternative Scoring:</strong> Geleneksel olmayan kredi puanlama</li>
                                </ul>
                                
                                <h6>Crowdfunding ve P2P Lending</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Real Estate Crowdfunding:</strong> Toplu fonlama modelleri</li>
                                    <li><strong>Peer-to-Peer Lending:</strong> Kişiden kişiye borç verme</li>
                                    <li><strong>REITs Tokenization:</strong> REIT'lerin dijitalleşmesi</li>
                                    <li><strong>Fractional Investment:</strong> Parçalı yatırım olanakları</li>
                                </ul>
                                
                                <h5>Gelecek Teknolojileri ve Projeksiyonlar</h5>
                                <p>Emlak sektörünün geleceğini şekillendirecek teknolojilere bakarken, 2025-2030 döneminde beklenen gelişmeleri analiz etmek kritik önem taşıyor.</p>
                                
                                <h6>Metaverse ve Sanal Gayrimenkul</h6>
                                <p>Sanal dünyalarda gayrimenkul yatırımı, henüz emekleme aşamasında olsa da hızla büyüyen bir pazar.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Virtual Land Sales:</strong> Sanal arsa satışları</li>
                                    <li><strong>Digital Architecture:</strong> Sanal mimari tasarım</li>
                                    <li><strong>Virtual Showrooms:</strong> Dijital şov odaları</li>
                                    <li><strong>NFT Integration:</strong> Sanal mülkiyet sertifikaları</li>
                                </ul>
                                
                                <h6>Quantum Computing Potansiyeli</h6>
                                <p>Kuantum bilgisayarlar, emlak analitiğinde karmaşık hesaplamaları saniyeler içinde yapabilecek kapasiteye sahip.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Risk Modelleme:</strong> Ultra karmaşık risk hesaplamaları</li>
                                    <li><strong>Piyasa Simülasyonu:</strong> Milyonlarca senaryonun analizi</li>
                                    <li><strong>Optimizasyon:</strong> Portföy optimizasyonu algoritmaları</li>
                                    <li><strong>Predictive Modeling:</strong> Gelecek tahminlerinde hassasiyet artışı</li>
                                </ul>
                                
                                <h6>Sürdürülebilirlik ve GreenTech</h6>
                                <p>Çevre dostu teknolojiler, gelecekte emlak değerlemesinde kritik faktör haline gelecek.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Carbon Footprint Tracking:</strong> Karbon ayak izi hesaplama</li>
                                    <li><strong>Green Building Certification:</strong> Dijital yeşil bina sertifikaları</li>
                                    <li><strong>Energy Management:</strong> AI destekli enerji optimizasyonu</li>
                                    <li><strong>Sustainable Materials:</strong> Sürdürülebilir yapı malzemesi takibi</li>
                                </ul>
                                
                                <div class="img-meta"><img src="https://images.unsplash.com/photo-1518186285589-2f7649de83e0?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2074&q=80" alt="Gelecek teknolojileri" class="lazy-img w-100"></div>
                                <div class="img-caption">Metaverse ve sanal gerçeklik emlak sektörünün geleceğini şekillendiriyor</div>
                                
                                <h5>Türkiye'de Dijital Dönüşüm Durumu</h5>
                                <p>Türkiye emlak sektörü, dijital dönüşümde global trendleri yakından takip ediyor ancak henüz tam potansiyeline ulaşamamış durumda.</p>
                                
                                <h6>Mevcut Durum Analizi</h6>
                                
                                <p><strong>Güçlü Yönler</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Yüksek İnternet Penetrasyonu:</strong> %78 internet kullanım oranı</li>
                                    <li><strong>Mobil Kullanım:</strong> Emlak aramalarının %65'i mobil</li>
                                    <li><strong>Sosyal Medya Aktifliği:</strong> Yüksek sosyal medya kullanımı</li>
                                    <li><strong>Genç Demografik:</strong> Teknolojiye açık nüfus yapısı</li>
                                </ul>
                                
                                <p><strong>Geliştirilmesi Gereken Alanlar</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Dijital Okuryazarlık:</strong> Sektör profesyonellerinde eksiklik</li>
                                    <li><strong>Sistem Entegrasyonu:</strong> Parçalı teknoloji kullanımı</li>
                                    <li><strong>Veri Güvenliği:</strong> KVKK uyumluluğu eksiklikleri</li>
                                    <li><strong>Yatırım Eksikliği:</strong> R&D harcamalarının düşüklüğü</li>
                                </ul>
                                
                                <h6>Fırsatlar</h6>
                                <ul class="style-none list-item">
                                    <li><strong>PropTech Yatırımları:</strong> Artan girişim sermayesi ilgisi</li>
                                    <li><strong>Devlet Desteği:</strong> Dijital Türkiye vizyonu</li>
                                    <li><strong>Yabancı Yatırımcı İlgisi:</strong> Teknoloji transferi olanakları</li>
                                    <li><strong>Eğitim Programları:</strong> Üniversite-sektör işbirlikleri</li>
                                </ul>
                                
                                <h5>Sektör Profesyonelleri İçin Stratejik Öneriler</h5>
                                <p>17 yıllık deneyimimle, emlak profesyonellerinin dijital dönüşüme adapte olabilmeleri için stratejik önerilerimi paylaşıyorum.</p>
                                
                                <h6>Kısa Vadeli Aksiyonlar (6-12 ay)</h6>
                                <ul class="style-none list-item">
                                    <li><strong>CRM Sistemi:</strong> Profesyonel müşteri yönetim sistemi kurulumu</li>
                                    <li><strong>Sosyal Medya Varlığı:</strong> Güçlü dijital kimlik oluşturma</li>
                                    <li><strong>Sanal Tur Teknolojisi:</strong> 360° fotoğraf ve video yatırımı</li>
                                    <li><strong>Dijital Pazarlama:</strong> Online reklam stratejileri</li>
                                </ul>
                                
                                <h6>Orta Vadeli Hedefler (1-3 yıl)</h6>
                                <ul class="style-none list-item">
                                    <li><strong>AI Entegrasyonu:</strong> Yapay zeka destekli araçlar</li>
                                    <li><strong>Data Analytics:</strong> Büyük veri analiz yetenekleri</li>
                                    <li><strong>Process Automation:</strong> İş süreçlerinin otomasyonu</li>
                                    <li><strong>PropTech Partnerships:</strong> Teknoloji firmaları ile işbirlikleri</li>
                                </ul>
                                
                                <h6>Uzun Vadeli Vizyon (3-5 yıl)</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Blockchain Adaptasyonu:</strong> Blok zinciri teknolojisi entegrasyonu</li>
                                    <li><strong>IoT Ecosystem:</strong> Nesnelerin interneti ekosistemi</li>
                                    <li><strong>AR/VR Mastery:</strong> Sanal ve artırılmış gerçeklik uzmanlığı</li>
                                    <li><strong>Innovation Leadership:</strong> Sektörde teknoloji liderliği</li>
                                </ul>
                                
                                <h5>Yatırımcılar İçin Teknoloji Odaklı Stratejiler</h5>
                                <p>Emlak yatırımcıları, teknolojik dönüşümü fırsata çevirmek için stratejilerini yeniden gözden geçirmeli.</p>
                                
                                <h6>PropTech Yatırım Fırsatları</h6>
                                <ul class="style-none list-item">
                                    <li><strong>PropTech Startupları:</strong> Erken aşama teknoloji yatırımları</li>
                                    <li><strong>Smart Building REITs:</strong> Akıllı bina odaklı REIT'ler</li>
                                    <li><strong>Co-living Spaces:</strong> Teknoloji destekli yaşam alanları</li>
                                    <li><strong>Industrial IoT:</strong> Endüstriyel gayrimenkul teknolojileri</li>
                                </ul>
                                
                                <h6>Risk Yönetimi</h6>
                                <ul class="style-none list-item">
                                    <li><strong>Technology Obsolescence:</strong> Teknoloji eskime riski</li>
                                    <li><strong>Cyber Security:</strong> Siber güvenlik tehditleri</li>
                                    <li><strong>Regulatory Changes:</strong> Düzenleyici değişiklikler</li>
                                    <li><strong>Market Volatility:</strong> Teknoloji sektörü volatilitesi</li>
                                </ul>
                                
                                <h5>Sonuç ve Değerlendirme</h5>
                                <p>Emlak sektöründeki dijital dönüşüm, sadece teknolojik bir değişim değil, aynı zamanda kültürel ve yapısal bir devrim. Bu dönüşüm sürecinde başarılı olmak için şu prensipler kritik önem taşıyor:</p>
                                
                                <div class="tech-stats-box">
                                    <h6><i class="fas fa-lightbulb me-2"></i>Başarı İçin Temel Prensipler</h6>
                                    <div class="stat-item">
                                        <span class="stat-label">Sürekli Öğrenme</span>
                                        <span class="stat-value">Zorunluluk</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Müşteri Odaklılık</span>
                                        <span class="stat-value">Merkezi Rol</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Teknoloji Yatırımı</span>
                                        <span class="stat-value">Stratejik</span>
                                    </div>
                                    <div class="stat-item">
                                        <span class="stat-label">Adaptasyon Hızı</span>
                                        <span class="stat-value">Kritik</span>
                                    </div>
                                </div>
                                
                                <p><strong>Dijital dönüşümde başarının anahtarları:</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Teknoloji Adoption:</strong> Yenilikleri hızla benimse</li>
                                    <li><strong>Data-Driven Decisions:</strong> Veri odaklı karar verme</li>
                                    <li><strong>Customer Experience:</strong> Müşteri deneyimini öncelikle</li>
                                    <li><strong>Continuous Innovation:</strong> Sürekli yenilik kültürü</li>
                                    <li><strong>Partnership Strategy:</strong> Stratejik işbirlikler kur</li>
                                </ul>
                                
                                <p>2024 yılında bulunduğumuz nokta, dijital dönüşümün sadece başlangıcı. Gelecek 5-10 yılda göreceğimiz değişimler, bugünkü dönüşümü gölgede bırakacak. Bu dönemde sektörde kalıcı başarı sağlamak isteyen herkesin, teknolojik gelişmeleri yakından takip etmesi ve stratejilerini buna göre şekillendirmesi gerekiyor.</p>
                                
                                <p class="fw-500">Emlak sektöründe dijital dönüşüm konusunda daha detaylı bilgi, stratejik danışmanlık ve teknoloji entegrasyonu için benimle iletişime geçebilirsiniz. Deneyimlerimi ve uzmanlığımı sizlerle paylaşmaktan memnuniyet duyarım.</p>
                            </div>
                            
                            <div class="bottom-widget d-sm-flex align-items-center justify-content-between">
                                <ul class="d-flex align-items-center tags style-none pt-20">
                                    <li>Etiketler:</li>
                                    <li><a href="#">PropTech,</a></li>
                                    <li><a href="#">Dijital Dönüşüm,</a></li>
                                    <li><a href="#">Blockchain,</a></li>
                                    <li><a href="#">AI,</a></li>
                                    <li><a href="#">VR/AR,</a></li>
                                    <li><a href="#">IoT</a></li>
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
                        
                        <!-- Yorum alanı -->
                        <div class="blog-comment-area">
                            <h3 class="blog-inner-title pb-35">12 Yorum</h3>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_01.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Mehmet Özkan</div>
                                    <div class="date">28 Hzrn, 2024, 14:20</div>
                                    <p>Çok kapsamlı bir analiz! PropTech konusunda bu kadar detaylı Türkçe kaynak bulmak zordu. Özellikle blockchain bölümü çok aydınlatıcı. Teşekkürler Gökhan Bey.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_02.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Sarah Mitchell</div>
                                    <div class="date">28 Hzrn, 2024, 16:45</div>
                                    <p>As a PropTech startup founder, I can confirm that everything mentioned here is spot on. The Turkish real estate market has huge potential for digital transformation. Great insights!</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                    <div class="comment position-relative reply-comment d-flex">
                                        <img src="images/lazy.svg" data-src="images/blog/avatar_03.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                        <div class="comment-text">
                                            <div class="name fw-500">Gökhan Aydınlı</div>
                                            <div class="date">28 Hzrn, 2024, 18:30</div>
                                            <p>Thank you Sarah! I'd love to hear more about your PropTech startup. Turkish market is indeed ripe for disruption. Let's connect!</p>
                                            <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_04.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Ali Yılmaz</div>
                                    <div class="date">29 Hzrn, 2024, 09:15</div>
                                    <p>VR/AR teknolojileri konusunda çok etkileyici bilgiler. Bizim şirket olarak sanal tur yatırımı yapma kararı aldık. Bu yazı karar verme sürecimizde çok yardımcı oldu.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_05.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Dr. Elena Petrov</div>
                                    <div class="date">29 Hzrn, 2024, 11:40</div>
                                    <p>Excellent analysis of quantum computing potential in real estate! As a researcher in quantum technologies, I'm excited to see how this will revolutionize property valuation models.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_06.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Fatma Demir</div>
                                    <div class="date">29 Hzrn, 2024, 13:25</div>
                                    <p>IoT ve akıllı ev teknolojileri bölümü çok faydalıydı. Yeni evimde hangi sistemleri kuracağım konusunda fikir sahibi oldum. Sürdürülebilirlik konusu da çok önemli.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_07.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Tech Investor</div>
                                    <div class="date">29 Hzrn, 2024, 15:50</div>
                                    <p>Great investment insights! The PropTech sector statistics are very valuable. I'm particularly interested in the tokenization and fractional ownership opportunities you mentioned.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_08.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Ahmet Kaya</div>
                                    <div class="date">30 Hzrn, 2024, 08:30</div>
                                    <p>CRM ve dijital pazarlama bölümü tam aradığım bilgilerdi. Küçük emlak ofisi işletiyorum ve dijitalleşmek istiyorum. Önerileriniz çok pratik, hemen uygulamaya başlayacağım.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_09.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Maria Santos</div>
                                    <div class="date">30 Hzrn, 2024, 10:45</div>
                                    <p>¡Excelente artículo! The metaverse section is fascinating. As a Spanish investor looking at Turkish real estate, this digital transformation guide is incredibly helpful.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_10.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Zeynep Aktaş</div>
                                    <div class="date">30 Hzrn, 2024, 14:20</div>
                                    <p>Fintech bölümü çok değerliydi. Özellikle crowdfunding ve P2P lending konularında detaylı bilgi istiyorum. Bu konularda daha fazla yazı yazmanızı rica ediyorum.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_11.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">David Chen</div>
                                    <div class="date">30 Hzrn, 2024, 16:55</div>
                                    <p>非常全面的数字化转型分析！AI和大数据在房地产估值中的应用前景很广阔。希望能看到更多关于中国市场数字化趋势的对比分析。</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_12.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Emirhan Özdemir</div>
                                    <div class="date">01 Tem, 2024, 09:10</div>
                                    <p>Türkiye'deki dijital dönüşüm durumu analizi çok realistik. Gerçekten de dijital okuryazarlık konusunda eksikliklerimiz var. Sektör olarak bu konularda eğitim almamız gerekiyor.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_13.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Lisa Johnson</div>
                                    <div class="date">01 Tem, 2024, 11:35</div>
                                    <p>This is the most comprehensive PropTech analysis I've read this year! The strategic recommendations are actionable and well-structured. Thank you for sharing your expertise!</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="blog-comment-form">
                            <h3 class="blog-inner-title">Yorum Bırakın</h3>
                            <p><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-decoration-underline fw-500">Giriş yapın</a> veya hesabınız yoksa kayıt olun.</p>
                            <form action="#" class="mt-30">
                                <div class="input-wrapper mb-30">
                                    <label>Ad Soyad*</label>
                                    <input type="text" placeholder="Adınız Soyadınız">
                                </div>
                                <div class="input-wrapper mb-40">
                                    <label>E-posta*</label>
                                    <input type="email" placeholder="ornek@email.com">
                                </div>
                                <div class="input-wrapper mb-30">
                                    <textarea placeholder="Yorumunuz"></textarea>
                                </div>
                                <button class="btn-five rounded-0">YORUM GÖNDER</button>
                            </form>
                        </div>
                    </div>
                    
                    <div class="col-lg-4">
                        <div class="blog-sidebar dot-bg ms-xxl-4 md-mt-60">
                            <div class="search-form bg-white mb-30">
                                <form action="#" class="position-relative">
                                    <input type="text" placeholder="Blog ara...">
                                    <button><i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>
                                </form>
                            </div>

                            <div class="categories bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">Kategoriler</h5>
                                <ul class="style-none">
                                    <li><a href="#">Dijital Dönüşüm (15)</a></li>
                                    <li><a href="#">PropTech (12)</a></li>
                                    <li><a href="#">Blockchain & Kripto (8)</a></li>
                                    <li><a href="#">AI & Yapay Zeka (10)</a></li>
                                    <li><a href="#">VR/AR Teknolojileri (6)</a></li>
                                    <li><a href="#">IoT & Akıllı Ev (9)</a></li>
                                    <li><a href="#">Fintech & Finansman (7)</a></li>
                                    <li><a href="#">GreenTech (5)</a></li>
                                </ul>
                            </div>

                            <div class="recent-news bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">İlgili Yazılar</h5>
                                <div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="https://images.unsplash.com/photo-1677442136019-21780ecad995?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog_details.html" class="title tran3s">2024 PropTech Yatırım Trendleri ve Fırsatları</a></h4>
                                        <div class="date">25 Hzrn, 2024</div>
                                    </div>
                                </div>
                                <div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="https://images.unsplash.com/photo-1451187580459-43490279c0fa?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog_details.html" class="title tran3s">Blockchain ile Gayrimenkul İşlemlerinin Geleceği</a></h4>
                                        <div class="date">20 Hzrn, 2024</div>
                                    </div>
                                </div>
                                <div class="news-block d-flex align-items-center">
                                    <div><img src="https://images.unsplash.com/photo-1592478411213-6153e4ebc696?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog_details.html" class="title tran3s">Akıllı Ev Teknolojileri ve Gayrimenkul Değerine Etkisi</a></h4>
                                        <div class="date">15 Hzrn, 2024</div>
                                    </div>
                                </div>
                            </div>

                            <div class="keyword bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">Popüler Etiketler</h5>
                                <ul class="style-none d-flex flex-wrap">
                                    <li><a href="#">PropTech</a></li>
                                    <li><a href="#">Dijital Dönüşüm</a></li>
                                    <li><a href="#">Blockchain</a></li>
                                    <li><a href="#">Yapay Zeka</a></li>
                                    <li><a href="#">VR/AR</a></li>
                                    <li><a href="#">IoT</a></li>
                                    <li><a href="#">Fintech</a></li>
                                    <li><a href="#">Smart Home</a></li>
                                    <li><a href="#">Big Data</a></li>
                                    <li><a href="#">Automation</a></li>
                                </ul>
                            </div>

                            <!-- PropTech Newsletter -->
                            <div class="newsletter-box bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">PropTech Newsletter</h5>
                                <p class="small mb-3">Emlak teknolojilerindeki son gelişmeleri kaçırmayın! Haftalık PropTech bültenimize abone olun.</p>
                                <form action="#" class="newsletter-form">
                                    <input type="email" placeholder="E-posta adresiniz" class="w-100 mb-3">
                                    <button class="btn btn-sm btn-primary w-100">Abone Ol</button>
                                </form>
                                <small class="text-muted">Haftalık 1 e-posta, spam yok!</small>
                            </div>

                            <!-- Teknoloji Sözlüğü -->
                            <div class="tech-glossary bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">Teknoloji Sözlüğü</h5>
                                <div class="accordion" id="techGlossary">
                                    <div class="accordion-item">
                                        <h6 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#proptech">
                                                PropTech Nedir?
                                            </button>
                                        </h6>
                                        <div id="proptech" class="accordion-collapse collapse" data-bs-parent="#techGlossary">
                                            <div class="accordion-body small">
                                                Property Technology - Gayrimenkul sektöründe kullanılan teknolojilerin genel adı.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h6 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#blockchain">
                                                Blockchain
                                            </button>
                                        </h6>
                                        <div id="blockchain" class="accordion-collapse collapse" data-bs-parent="#techGlossary">
                                            <div class="accordion-body small">
                                                Dağıtık defter teknolojisi - Şeffaf ve güvenli işlem kayıtları için kullanılır.
                                            </div>
                                        </div>
                                    </div>
                                    <div class="accordion-item">
                                        <h6 class="accordion-header">
                                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse" data-bs-target="#iot">
                                                IoT
                                            </button>
                                        </h6>
                                        <div id="iot" class="accordion-collapse collapse" data-bs-parent="#techGlossary">
                                            <div class="accordion-body small">
                                                Internet of Things - Nesnelerin interneti, akıllı ev teknolojilerinin temeli.
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <!-- Uzman Danışmanlık Kutusu -->
                            <div class="consultation-box bg-white bg-wrapper mb-30">
                                <h5 class="mb-20">PropTech Danışmanlığı</h5>
                                <div class="d-flex align-items-center mb-15">
                                    <img src="images/GA.png" alt="Gökhan Aydınlı" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                                    <div>
                                        <h6 class="mb-0">Gökhan Aydınlı</h6>
                                        <small class="text-muted">PropTech & Dijital Dönüşüm Uzmanı</small>
                                    </div>
                                </div>
                                <p class="small mb-3">Emlak sektöründe dijital dönüşüm ve teknoloji entegrasyonu konularında ücretsiz ön görüşme.</p>
                                <a href="contact.php" class="btn btn-sm btn-primary w-100">Ücretsiz Görüşme</a>
                            </div>

                            <!-- Yazar Bilgisi -->
                            <div class="author-info bg-white bg-wrapper">
                                <h5 class="mb-20">Yazar Hakkında</h5>
                                <div class="d-flex align-items-center mb-15">
                                    <img src="images/GA.png" alt="Gökhan Aydınlı" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                                    <div>
                                        <h6 class="mb-0">Gökhan Aydınlı</h6>
                                        <small class="text-muted">Gayrimenkul & Teknoloji Uzmanı</small>
                                    </div>
                                </div>
                                <p class="small">17 yıllık emlak deneyimi ile PropTech ve dijital dönüşüm konularında uzmanlaşmış. Sektörde teknoloji adaptasyonu ve inovasyon liderliği konularında danışmanlık hizmeti vermektedir.</p>
                                <div class="row">
                                    <div class="col-6">
                                        <a href="agent_details.html" class="btn btn-sm btn-outline-primary w-100">Profil</a>
                                    </div>
                                    <div class="col-6">
                                        <a href="contact.html" class="btn btn-sm btn-primary w-100">İletişim</a>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

		<!-- Fancy Banner Two -->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50 " style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
							<h3 class="text-white m0">Dijital Dönüşüm <span>Yolculuğunuzda<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Yanınızdayız.</h3>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-wrapper me-auto ms-auto me-lg-0">
							<form action="#">
								<input type="email" placeholder="PropTech güncellemeleri için e-posta" class="rounded-0">
								<button class="rounded-0">Takip Et</button>
							</form>
							<div class="fs-16 mt-10 text-white">Zaten üye misiniz? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın.</a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
<br><br>

		<!-- Footer Four -->
		<div class="footer-four position-relative z-1">
			<div class="container container-large">
				<div class="bg-wrapper position-relative z-1">
					<div class="row">
						<div class="col-xxl-3 col-lg-4 mb-60">
							<div class="footer-intro">
								<div class="logo mb-20">
									<a href="index.html">
										<img src="images/logoSiyah.png" alt="">
									</a>
								</div> 
								<!-- logo -->
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
									<li><a href="index.html">Ana Sayfa</a></li>
									<li><a href="dashboard/membership.html" target="_blank">Üyelik</a></li>
									<li><a href="about_us_01.html">Hakkımızda</a></li>
									<li><a href="blog_01.html">Blog</a></li>
									<li><a href="blog_02.html">Kariyer</a></li>
									<li><a href="pricing_02.html">Fiyatlar</a></li>
									<li><a href="dashboard/dashboard-index.html" target="_blank">Panel</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-3 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Yasal</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="faq.html">Şartlar & Koşullar</a></li>
									<li><a href="faq.html">Çerez Politikası</a></li>
									<li><a href="faq.html">Gizlilik Politikası</a></li>
									<li><a href="faq.html">S.S.S</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-2 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Hizmetlerimiz</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="listing_01.html">Ticari Gayrimenkul</a></li>
									<li><a href="listing_02.html">Konut Satışı</a></li>
									<li><a href="listing_03.html">Ev Kiralama</a></li>
									<li><a href="listing_04.html">Yatırım Danışmanlığı</a></li>
									<li><a href="listing_05.html">Villa Satışı</a></li>
									<li><a href="listing_06.html">Ofis Kiralama</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- /.bg-wrapper -->
				<div class="bottom-footer">
					<p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
				</div>
			</div>
			<img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
		</div> <!-- /.footer-four -->


		<!-- Login Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="container">
                    <div class="user-data-form modal-content">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="form-wrapper m-auto">
							<ul class="nav nav-tabs w-100" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Kayıt</button>
								</li>
							</ul>
							<div class="tab-content mt-30">
								<div class="tab-pane show active" role="tabpanel" id="fc1">
									<div class="text-center mb-20">
										<h2>Hoş Geldiniz!</h2>
										<p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#">Kayıt olun</a></p>
									</div>
									<form action="#">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>E-posta*</label>
													<input type="email" placeholder="ornek@email.com">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-20">
													<label>Şifre*</label>
													<input type="password" placeholder="Şifrenizi girin" class="pass_log_id">
													<span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
												</div>
											</div>
											<div class="col-12">
												<div class="agreement-checkbox d-flex justify-content-between align-items-center">
													<div>
														<input type="checkbox" id="remember">
														<label for="remember">Beni hatırla</label>
													</div>
													<a href="#">Şifremi Unuttum?</a>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
											</div>
										</div>
									</form>
								</div>
								<!-- /.tab-pane -->
								<div class="tab-pane" role="tabpanel" id="fc2">
									<div class="text-center mb-20">
										<h2>Kayıt Ol</h2>
										<p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#">Giriş yapın</a></p>
									</div>
									<form action="#">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>Ad Soyad*</label>
													<input type="text" placeholder="Ad Soyadınız">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>E-posta*</label>
													<input type="email" placeholder="ornek@email.com">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-20">
													<label>Şifre*</label>
													<input type="password" placeholder="Şifrenizi girin" class="pass_log_id">
													<span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
												</div>
											</div>
											<div class="col-12">
												<div class="agreement-checkbox d-flex justify-content-between align-items-center">
													<div>
														<input type="checkbox" id="remember2">
														<label for="remember2">"Kayıt Ol" butonuna tıklayarak <a href="#">Şartlar & Koşullar</a> ile <a href="#">Gizlilik Politikası</a>'nı kabul ediyorum</label>
													</div>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
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
										<span class="ps-3">Google ile Kayıt</span>
									</a>
								</div>
								<div class="col-sm-6">
									<a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
										<img src="images/icon/facebook.png" alt="">
										<span class="ps-3">Facebook ile Kayıt</span>
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

		<!-- Optional JavaScript -->
		<!-- jQuery first, then Bootstrap JS -->
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
		
		<!-- Progress Bar Animation -->
		<script>
		$(document).ready(function() {
			// Progress bar animation
			$('.progress-fill').each(function() {
				var width = $(this).css('width');
				$(this).css('width', '0');
				$(this).animate({
					width: width
				}, 2000);
			});
			
			// Smooth scroll for anchor links
			$('a[href^="#"]').on('click', function(event) {
				var target = $(this.getAttribute('href'));
				if( target.length ) {
					event.preventDefault();
					$('html, body').stop().animate({
						scrollTop: target.offset().top - 100
					}, 1000);
				}
			});
		});
		</script>
	</div> <!-- /.main-page-wrapper -->
</body>
</html>="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class