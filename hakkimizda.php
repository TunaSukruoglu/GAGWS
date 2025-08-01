<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul, iş yeri, ofis, dükkan, ticari gayrimenkul, hakkımızda">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul: 2012'den beri ticari gayrimenkul sektöründe güvenilir danışmanlık hizmetleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Hakkımızda | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/ogg.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Hakkımızda | Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
<link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <!-- Bootstrap Icons -->
<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- FontAwesome (yoksa ekleyin) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Modal CSS -->
    <?php include 'includes/modal-css.php'; ?>
    
    <style>
/* Slider fallback stilleri */
.fallback-grid {
    display: flex !important;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.fallback-grid .item {
    flex: 1;
    min-width: 300px;
    max-width: calc(50% - 10px);
    margin-bottom: 20px;
}

@media (max-width: 768px) {
    .fallback-grid .item {
        max-width: 100%;
        width: 100% !important;
        margin: 0 0 20px 0 !important;
    }
}

/* Tracking Prevention için CSS düzeltmeleri */
.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: #15B97C;
    color: white;
    border: none;
    border-radius: 50%;
    display: none;
    z-index: 9999;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(21, 185, 124, 0.3);
}

.scroll-top:hover {
    background: #0D1A1C;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(13, 26, 28, 0.4);
}

.scroll-top i {
    font-size: 24px;
    line-height: 1;
}

/* Storage disabled durumunda fallback */
.storage-disabled .wow {
    visibility: visible !important;
    animation: fadeInUp 1s ease forwards;
}

/* Preloader güvenli stil */
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.5s ease;
}

/* Form güvenli stilleri */
.form-wrapper button[type="submit"]:disabled {
    opacity: 0.6;
    cursor: not-allowed;
    background: #ccc;
}

/* Animasyon fallback */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

.wow {
    animation: fadeInUp 1s ease forwards;
}

/* Counter fallback */
.counter {
    font-weight: bold;
    color: #15B97C;
    display: inline-block;
    min-width: 50px;
}

/* Counter animasyon başlamadan önce */
.counter:not(.counted) {
    opacity: 1;
}

/* Slider özel düzeltmeleri */
.slick-slider {
    margin-bottom: 0;
}

.slick-dots {
    bottom: -50px;
}

.slick-slide {
    outline: none;
}

/* Avatar stilleri */
.avatar {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

/* Feedback block düzeltmeleri */
.feedback-block-six {
    padding: 30px;
    background: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin: 15px;
    height: auto;
    min-height: 250px;
}

.feedback-block-six blockquote {
    margin: 20px 0;
    font-style: italic;
    font-size: 16px;
    line-height: 1.6;
}

.rating li i {
    color: #ffc107;
}

/* Modal özel stilleri */
.user-data-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid #ddd;
    padding: 40px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.user-data-form .nav-tabs {
    border-bottom: 2px solid #15B97C;
}

.user-data-form .nav-link {
    color: #15B97C;
    font-weight: 500;
    border: none;
    border-radius: 0;
    padding: 10px 20px;
    margin-right: 10px;
    transition: all 0.3s ease;
}

.user-data-form .nav-link.active {
    color: #fff;
    background: #15B97C;
    border-radius: 20px 20px 0 0;
}

.user-data-form .tab-content {
    border: 1px solid #15B97C;
    border-radius: 0 0 20px 20px;
    padding: 30px;
    background: #fff;
}

.user-data-form .input-group-meta {
    margin-bottom: 25px;
}

.user-data-form label {
    font-weight: 500;
    margin-bottom: 10px;
    display: block;
}

.user-data-form input[type="email"],
.user-data-form input[type="password"],
.user-data-form input[type="text"],
.user-data-form input[type="tel"] {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px 15px;
    font-size: 16px;
    width: 100%;
    transition: all 0.3s ease;
}

.user-data-form input[type="email"]:focus,
.user-data-form input[type="password"]:focus,
.user-data-form input[type="text"]:focus,
.user-data-form input[type="tel"]:focus {
    border-color: #15B97C;
    box-shadow: 0 0 5px rgba(21, 185, 124, 0.5);
    outline: none;
}

.user-data-form .btn-two {
    background: #15B97C;
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-data-form .btn-two:hover {
    background: #0D1A1C;
    transform: translateY(-2px);
}

.user-data-form .agreement-checkbox {
    margin-bottom: 20px;
}

.user-data-form .agreement-checkbox input {
    margin-right: 10px;
}

.user-data-form .social-use-btn {
    background: #f7f7f7;
    color: #333;
    padding: 12px;
    border-radius: 10px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: all 0.3s ease;
}

.user-data-form .social-use-btn:hover {
    background: #e2e2e2;
    transform: translateY(-2px);
}

.user-data-form .line {
    flex: 1;
    height: 1px;
    background: #ddd;
    margin: 0 10px;
}

.user-data-form .passVicon {
    position: absolute;
    right: 10px;
    top: 50%;
    transform: translateY(-50%);
    pointer-events: none;
}

/* Ekip slider özel düzeltmeleri - TAMAMEN YENİ */
.agent-slider-one {
    display: flex !important;
    gap: 30px;
    flex-wrap: nowrap;
    align-items: stretch;
}

.agent-slider-one .item {
    flex: 1 1 calc(50% - 15px);
    max-width: calc(50% - 15px);
    min-width: 280px;
}

/* Slick aktifken bu kuralları geçersiz kıl */
.agent-slider-one.slick-initialized {
    display: block !important;
}

.agent-slider-one.slick-initialized .item {
    flex: none;
    max-width: none;
    min-width: auto;
    width: auto;
}

/* Fallback grid durumu */
.agent-slider-one.fallback-grid {
    display: flex !important;
    gap: 30px;
    justify-content: center;
    align-items: stretch;
    flex-wrap: nowrap;
}

.agent-slider-one.fallback-grid .item {
    flex: 1 1 calc(50% - 15px);
    max-width: calc(50% - 15px);
    min-width: 280px;
    width: auto !important;
    margin: 0 !important;
    display: block !important;
}

/* Agent card düzeltmeleri */
.agent-card-one {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.agent-card-one:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.agent-card-one .img {
    height: 280px;
    overflow: hidden;
    flex-shrink: 0;
}

.agent-card-one .img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.agent-card-one:hover .img img {
    transform: scale(1.05);
}

.agent-card-one .text-center {
    padding: 25px 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.agent-card-one h6 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
    color: #0D1A1C;
}

.agent-card-one a {
    color: #15B97C;
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.agent-card-one a:hover {
    color: #0D1A1C;
}

/* Responsive düzeltmeler */
@media (max-width: 992px) {
    .agent-slider-one,
    .agent-slider-one.fallback-grid {
        flex-direction: column;
        gap: 20px;
    }
    
    .agent-slider-one .item,
    .agent-slider-one.fallback-grid .item {
        flex: none;
        max-width: 100%;
        min-width: auto;
        width: 100% !important;
    }
}

@media (max-width: 768px) {
    .agent-card-one .img {
        height: 250px;
    }
    
    .agent-card-one .text-center {
        padding: 20px 15px;
    }
}

/* Slick slider iptal etme */
.agent-slider-one .slick-list,
.agent-slider-one .slick-track {
    display: flex !important;
    align-items: stretch !important;
}

.agent-slider-one .slick-slide {
    height: auto !important;
    display: flex !important;
}

.agent-slider-one .slick-slide > div {
    width: 100% !important;
    height: 100% !important;
}

/* Slider kontrolları gizle */
.agent-slider-one .slick-prev,
.agent-slider-one .slick-next,
.agent-slider-one .slick-dots {
    display: none !important;
}

/* Dropdown Styles */
.dropdown-toggle::after {
    margin-left: 0.5em;
}

.dropdown-menu {
    border: none;
    border-radius: 10px;
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
    padding: 10px 0;
    margin-top: 5px;
}

.dropdown-item {
    padding: 8px 20px;
    transition: all 0.3s ease;
}

.dropdown-item:hover {
    background-color: #f8f9fa;
    color: #15B97C;
}

.dropdown-divider {
    margin: 5px 0;
    border-color: #e9ecef;
}
</style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- ===================================================
            Yükleniyor Animasyonu
        ==================================================== -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
            </div>
        </div>

		<!-- 
		=============================================
			Theme Main Menu
		============================================== 
		-->
		<header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
			<div class="inner-content gap-one">
				<div class="top-header position-relative">
					<div class="d-flex align-items-center justify-content-between">
						<div class="logo order-lg-0">
							<a href="index.php" class="d-flex align-items-center">
								<img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
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
									<li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="index.php" >Ana Sayfa</a>
									</li>
									<li class="nav-item dashboard-menu">
										<a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
										</a>
						
									</li>

                                    <li class="nav-item dropdown">
										<a class="nav-link" href="portfoy.php">Portföy</a>
										</a>
						
									</li>

                                    <li class="nav-item dropdown">
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
								
								</ul>
							</div>
						</nav>
					</div>
				</div> <!--/.top-header-->
			</div> <!-- /.inner-content -->
		</header> 
		<!-- /.theme-main-menu -->


        <!-- ============================
            İç Banner
        ============================ -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Hakkımızda</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                    <li>/</li>
                    <li>Hakkımızda</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

        <!-- ============================
            Ana İçerik
        ============================ -->
        <div class="block-feature-two mt-150 xl-mt-100">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-6 wow fadeInLeft">
                        <div class="me-xxl-4">
                            <div class="title-one mb-60 lg-mb-40">
                                <div class="upper-title">Gökhan Aydınlı Gayrimenkul</div>
                                <h3>Başarılı • <span>Güvenilir<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> • Hızlı</h3>
                                <p class="fs-22">2012 yılında "Alırken Kazan, Satarken Kazan!" mottosu ile kurulan firmamız, 13 yıllık tecrübesi ile ticari gayrimenkul sektöründe güvenin ve başarının adı haline gelmiştir.</p>
                            </div>
                            <a href="contact.php" class="btn-two">Bize Ulaşın</a>
                            <div class="counter-wrapper border-top pt-40 md-pt-10 mt-65 md-mt-40">
                                <div class="row">
                                    <div class="col-xxl-6 col-sm-5">
                                        <div class="counter-block-one mt-20">
                                            <div class="main-count fw-500 color-dark">18+</div>
                                            <span>Yıllık tecrübe</span>
                                        </div>
                                    </div>
                                    <div class="col-xxl-6 col-sm-7">
                                        <div class="counter-block-one mt-20">
                                            <div class="main-count fw-500 color-dark">5000+</div>
                                            <span>Başarılı işlem</span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInRight">
                        <div class="block-two md-mt-40">
                            <div class="bg-wrapper">
                                <h5>Biz Kimiz?</h5>
                                <p class="fs-22 lh-lg mt-20">Müşterilerimize dünya standartlarında çözümler sunma vizyonumuzla yola çıktığımız bu yolculukta, her bir müşterimizin kısa sürede hedeflerine ulaşmasını ve eksiksiz hizmet anlayışıyla memnuniyetlerini sağlamayı kendimize ilke edindik.</p>
                                <h5 class="top-line">Misyonumuz</h5>
                                <p class="fs-22 lh-lg mt-20">"Doğru Fiyat - Doğru Yatırım" buluşmasını gerçekleştirmek ve bu buluşmayı mümkün kılmak için gerekli sorumluluk bilinci ile hizmetlerimizi sürdürmek, sektörde öncü olmaya devam etmektir.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================
            Hizmet Alanlarımız
        ============================ -->
        <div class="block-feature-five position-relative z-1 pt-170 xl-pt-120 pb-130 xl-pb-100 lg-pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 m-auto">
                        <div class="title-one text-center mb-35 lg-mb-20">
                            <h3>Ticari gayrimenkul <br> ihtiyaçlarınız için <span>buradayız<img src="images/lazy.svg" data-src="images/shape/title_shape_07.svg" alt="" class="lazy-img"></span></h3>
                            <p class="fs-24 color-dark">Hizmet sürecimiz 3 kolay adımda:</p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div class="col-xxl-11 m-auto">
                        <div class="row gx-xl-5 justify-content-center">
                            <div class="col-lg-4 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_07.svg" alt="" class="lazy-img m-auto icon">
                                    <h5 class="mt-50 lg-mt-30 mb-15">İhtiyaç Analizi</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Size en uygun ticari gayrimenkul seçeneklerini belirlemek için detaylı ihtiyaç analizi yapıyoruz.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40 arrow position-relative" data-wow-delay="0.1s">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_08.svg" alt="" class="lazy-img m-auto icon">
                                    <h5 class="mt-50 lg-mt-30 mb-15">Seçenek Sunumu</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Geniş portföyümüzden ihtiyacınıza en uygun ofis, dükkan, depo seçeneklerini sunuyoruz.</p>
                                </div>
                            </div>
                            <div class="col-lg-4 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40" data-wow-delay="0.2s">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_09.svg" alt="" class="lazy-img m-auto icon">
                                    <h5 class="mt-50 lg-mt-30 mb-15">Hızlı Sonuçlandırma</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Tüm yasal süreçlerle birlikte işleminizi hızlı ve güvenli şekilde sonuçlandırıyoruz.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/shape/shape_07.svg" alt="" class="lazy-img shapes shape_01">
            <img src="images/lazy.svg" data-src="images/shape/shape_08.svg" alt="" class="lazy-img shapes shape_02">
        </div>

        <!-- ============================
            Hizmet Özelliklerimiz
        ============================ -->
        <div class="block-feature-fourteen pt-120 xl-pt-100 pb-140 xl-pb-100">
            <div class="container container-large">
                <div class="title-one text-center wow fadeInUp">
                    <h3 class="text-white">Neden Bizi Tercih Etmelisiniz?</h3>
                    <p class="fs-24 mt-xs text-white">Ticari gayrimenkulde güvenilir ve uzman çözüm ortağınız.</p>
                </div>

                <div class="card-bg-wrapper wow fadeInUp mt-70 lg-mt-50">
                    <div class="row">
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start pe-xxl-5">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_40.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Geniş Portföy</h5>
                                        <p>Ofis, dükkan, depo ve ticari arsa seçenekleriyle zengin portföy imkanları.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start pe-xxl-2 ps-xxl-2">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_41.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Uzman Danışmanlık</h5>
                                        <p>13 yıllık tecrübe ile ticari gayrimenkul yatırımınızda doğru kararlar almanızı sağlıyoruz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <div class="col-lg-4">
                            <div class="card-style-eight mt-45 wow fadeInUp">
                                <div class="d-flex align-items-start ps-xxl-5">
                                    <img src="images/lazy.svg" data-src="images/icon/icon_42.svg" alt="" class="lazy-img icon">
                                    <div class="text">
                                        <h5 class="text-white">Hızlı ve Güvenli</h5>
                                        <p>Tüm yasal süreçlerle birlikte işlemlerinizi hızlı ve güvenli şekilde tamamlıyoruz.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- ============================
            Görsel Banner (Video Banner yerine)
        ============================ -->
        <div class="image-banner-one mt-150 xl-mt-120 md-mt-80">
            <div class="container">
                <div class="bg-wrapper position-relative z-1 overflow-hidden d-flex align-items-center justify-content-center" style="background-image: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/2a38b5b7-fe9d-49ec-082f-8a5238b34700/public'); min-height:320px; background-size:cover; background-position:center; border-radius:24px;">
                    <h2 class="text-white text-center w-100" style="text-shadow:0 2px 8px rgba(0,0,0,0.25);">Gökhan Aydınlı Gayrimenkul ile Güvenli Ticari Yatırım</h2>
                </div>
            </div>
        </div>

        <!-- ============================
            Müşteri Yorumları
        ============================ -->
        <div class="feedback-section-six bg-pink-two position-relative z-1 pt-110 xl-pt-80 pb-100 xl-pb-80">
            <div class="container">
                <div class="title-one text-center mb-80 xl-mb-50 md-mb-30">
                    <h3>Müşteri Yorumları</h3>
                    <p class="fs-20 mt-xs">Ticari gayrimenkulde memnuniyetimizi müşterilerimizin deneyimlerinden öğrenin.</p>
                </div>
                <div class="slider-left">
                    <div class="feedback-slider-four">
                        <div class="item">
                            <div class="feedback-block-six rounded-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <ul class="rating style-none d-flex">
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    </ul>
                                    <img src="images/icon/icon_29.svg" alt="" class="icon">
                                </div>
                                <blockquote>İş yerimizi çok kısa sürede bulduk. Mükemmel hizmet!</blockquote>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fs-20 m0">Ahmet Yılmaz, <span class="fw-normal opacity-50">Bursa</span></h6>
                                    <img src="https://randomuser.me/api/portraits/men/24.jpg" alt="Ahmet Yılmaz" class="rounded-circle avatar">
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feedback-block-six rounded-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <ul class="rating style-none d-flex">
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    </ul>
                                    <img src="images/icon/icon_29.svg" alt="" class="icon">
                                </div>
                                <blockquote>Profesyonel ve güler yüzlü hizmet, her aşamada destek aldık.</blockquote>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fs-20 m0">Fatma Demir, <span class="fw-normal opacity-50">İzmir</span></h6>
                                    <img src="https://randomuser.me/api/portraits/women/68.jpg" alt="Fatma Demir" class="rounded-circle avatar">
                                </div>
                            </div>
                        </div>
                        <div class="item">
                            <div class="feedback-block-six rounded-4">
                                <div class="d-flex justify-content-between align-items-center">
                                    <ul class="rating style-none d-flex">
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                        <li><i class="fa-sharp fa-solid fa-star"></i></li>
                                    </ul>
                                    <img src="images/icon/icon_29.svg" alt="" class="icon">
                                </div>
                                <blockquote>Yatırım danışmanlığı konusunda gerçekten uzmanlar.</blockquote>
                                <div class="d-flex align-items-center justify-content-between">
                                    <h6 class="fs-20 m0">Elif Şahin, <span class="fw-normal opacity-50">Antalya</span></h6>
                                    <img src="https://randomuser.me/api/portraits/women/32.jpg" alt="Elif Şahin" class="rounded-circle avatar">
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
                    

       <!--
		=====================================================
			Agent Section One
		=====================================================
		-->
		<div class="agent-section-one position-relative z-1 mt-150 xl-mt-120">
			<div class="container">
				<div class="position-relative">
					<div class="title-one mb-85 lg-mb-50 wow fadeInLeft">
						<h3>Gayrimenkul Uzmanı &  <span>Broker<img src="images/lazy.svg" data-src="images/shape/title_shape_05.svg" alt="" class="lazy-img"></span></h3>
               <p class="fs-22 mt-xs">Farkı Yaratan <span>Yaklaşımımız<img src="images/lazy.svg" data-src="images/shape/title_shape_07.svg" alt="" class="lazy-img"></span></p>
            
            </div>
					<!-- /.title-one -->

					<div class="wrapper position-relative z-1">
						<div class="agent-slider-one">
							<div class="item">
								<div class="agent-card-one position-relative">
									<div class="img border-20">
										<img src="https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/a42238ef-23f8-4f8f-dc2b-38ceecf87100/public" alt="" class="w-100 tran5s">
									</div>
									<div class="text-center">
										<h6>Gökhan Aydınlı</h6>
                                <a href="ekibimiz.php" class="stretched-link">Kurucu & Gayrimenkul Uzmanı</a>
                            </div>
								</div>
								<!-- /.agent-card-one -->
							</div>
							
						</div>
					</div>
					<!-- /.wrapper -->

					<div class="section-btn text-center md-mt-60">
						<a href="contact.php" class="btn-five">Bize Ulaşın</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /.agent-section-one -->


        <!-- ============================
            İletişim Banner
        ============================ -->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50 " style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
		
            <div class="container">
                <div class="row align-items-center">
                    <div class="col-lg-6">
                        <div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
                            <h3 class="text-white m0">Ticari <span>gayrimenkul<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> yolculuğunuza başlayın.</h3>
                        </div>
                    </div>
                    <div class="col-lg-6">
                        <div class="form-wrapper me-auto ms-auto me-lg-0">
                            <form action="contact.php" method="POST">
                                <input type="email" name="email" placeholder="E-posta adresiniz" required>
                                <button type="submit">İletişime Geç</button>
                            </form>
                            <div class="fs-16 mt-10 text-white">Sorularınız mı var? <a href="tel:02128016058" class="text-decoration-underline">Hemen Arayın: 0212 801 60 58</a></div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
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
				<!-- /.bg-wrapper -->
				<div class="bottom-footer">
					<p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
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
                                            <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="checkbox" id="remember" name="remember">
                                                <label for="remember">Beni hatırla</label>
                                            </div>
                                            <a href="#" onclick="showForgotPassword()">Şifremi Unuttum?</a>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
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
                                        <div class="input-group-meta position-relative mb-25">
                                            <label>Telefon</label>
                                            <input type="tel" name="phone" placeholder="0555 555 55 55">
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
                                        <div class="input-group-meta position-relative mb-20">
                                            <label>Şifre Tekrar*</label>
                                            <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="checkbox" id="terms" name="terms" required>
                                                <label for="terms">"Kayıt Ol" butonuna tıklayarak <a href="terms.php" target="_blank">Şartlar & Koşullar</a> ile <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı kabul ediyorum</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    
                </div>
                <!-- /.form-wrapper -->
            </div>
            <!-- /.user-data-form -->
        </div>
    </div>
</div>
    
        <!-- JS Dosyaları -->
        <script src="vendor/jquery.min.js"></script>
        <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
        <script src="vendor/wow/wow.min.js"></script>
        <script src="vendor/slick/slick.min.js"></script>
        <script src="vendor/fancybox/fancybox.umd.js"></script>
        <script src="vendor/jquery.lazy.min.js"></script>
        <script src="vendor/jquery.counterup.min.js"></script>
        <script src="vendor/jquery.waypoints.min.js"></script>
        <script src="js/theme.js"></script>

<script>
// Modal fonksiyonları
function switchToRegister() {
    document.querySelector('#fc1').classList.remove('show', 'active');
    document.querySelector('#fc2').classList.add('show', 'active');
    document.querySelector('[data-bs-target="#fc1"]').classList.remove('active');
    document.querySelector('[data-bs-target="#fc2"]').classList.add('active');
}

function switchToLogin() {
    document.querySelector('#fc2').classList.remove('show', 'active');
    document.querySelector('#fc1').classList.add('show', 'active');
    document.querySelector('[data-bs-target="#fc2"]').classList.remove('active');
    document.querySelector('[data-bs-target="#fc1"]').classList.add('active');
}

function showForgotPassword() {
    alert('Şifre sıfırlama linki e-posta adresinize gönderilecektir.');
}

// Güvenli storage kontrolü
function safeStorage() {
    try {
        return typeof(Storage) !== "undefined" && localStorage;
    } catch (e) {
        return false;
    }
}

// Element varlık kontrolü
function elementExists(selector) {
    return document.querySelector(selector) !== null;
}

// Form validasyonu ve sayfa yükleme olayları
document.addEventListener('DOMContentLoaded', function() {
    console.log('Sayfa yüklendi, JavaScript başlatılıyor...');
    
    // Login form validasyonu ve AJAX işlemi
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            
            if (!email || !password) {
                alert('Lütfen tüm alanları doldurun.');
                return false;
            }
            
            if (!email.includes('@')) {
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
            
            // AJAX ile login işlemi
            const formData = new FormData();
            formData.append('email', email);
            formData.append('password', password);
            
            fetch('login.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Sayfayı yenile
                } else {
                    alert(data.message || 'Giriş başarısız.');
                }
            })
            .catch(error => {
                console.error('Login error:', error);
                alert('Bir hata oluştu. Lütfen tekrar deneyin.');
            });
        });
    }
    
    // Register form validasyonu
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const fullname = this.querySelector('input[name="fullname"]').value;
            const email = this.querySelector('input[name="email"]').value;
            const password = this.querySelector('input[name="password"]').value;
            const passwordConfirm = this.querySelector('input[name="password_confirm"]').value;
            const terms = this.querySelector('input[name="terms"]').checked;
            
            if (!fullname || !email || !password || !passwordConfirm) {
                e.preventDefault();
                alert('Lütfen tüm zorunlu alanları doldurun.');
                return false;
            }
            
            if (!email.includes('@')) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır.');
                return false;
            }
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor.');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Şartlar ve koşulları kabul etmelisiniz.');
                return false;
            }
        });
    }
    
    // Telefon formatlaması
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    formattedValue = value;
                } else if (value.length <= 6) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3);
                } else if (value.length <= 8) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
                } else {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 8) + ' ' + value.slice(8, 10);
                }
            }
            
            e.target.value = formattedValue;
        });
    });
    
    // Slider'ları güvenli başlatma
    if (typeof $.fn.slick !== 'undefined') {
        // Feedback slider
        if (elementExists('.feedback-slider-four')) {
            try {
                $('.feedback-slider-four').slick({
                    infinite: true,
                    slidesToShow: 2,
                    slidesToScroll: 1,
                    autoplay: true,
                    autoplaySpeed: 3000,
                    arrows: false,
                    dots: true,
                    responsive: [
                        {
                            breakpoint: 768,
                            settings: {
                                slidesToShow: 1,
                                slidesToScroll: 1
                            }
                        }
                    ]
                });
                console.log('Feedback slider başlatıldı');
            } catch (e) {
                console.log('Feedback slider başlatılamadı:', e);
                $('.feedback-slider-four').addClass('fallback-grid');
            }
        }
        
        // Agent slider - KALDIRILDI, artık Bootstrap grid kullanıyoruz
        // if (elementExists('.agent-slider-one')) { ... } - Bu kısmı tamamen silin
    } else {
        console.log('Slick slider yüklenmedi, fallback grid uygulanıyor');
        
        // Manuel grid düzeni
        if (elementExists('.agent-slider-one')) {
            $('.agent-slider-one').addClass('fallback-grid');
            $('.agent-slider-one').css({
                'display': 'flex',
                'gap': '30px',
                'justify-content': 'center',
                'flex-wrap': 'wrap'
            });
            $('.agent-slider-one .item').css({
                'flex': '1',
                'max-width': 'calc(50% - 15px)',
                'min-width': '280px'
            });
        }
        
        if (elementExists('.feedback-slider-four')) {
            $('.feedback-slider-four').addClass('fallback-grid');
        }
    }
    
    // Lazy loading güvenli başlatma
    if (typeof $.fn.lazy !== 'undefined' && $('.lazy-img').length > 0) {
        try {
            $('.lazy-img').lazy({
                effect: "fadeIn",
                effectTime: 600,
                threshold: 0,
                fallbackSrc: 'images/placeholder.png'
            });
            console.log('Lazy loading başlatıldı');
        } catch (e) {
            console.log('Lazy loading başlatılamadı:', e);
            // Fallback: lazy-img'leri normal img'e çevir
            $('.lazy-img').each(function() {
                var src = $(this).attr('data-src');
                if (src) {
                    $(this).attr('src', src);
                    $(this).removeClass('lazy-img');
                }
            });
        }
    }
    
    // Counter animasyonu
    if (typeof $.fn.counterUp !== 'undefined' && elementExists('.counter')) {
        try {
            $('.counter').counterUp({
                delay: 10,
                time: 2000
            });
            console.log('Counter animasyonu başlatıldı');
        } catch (e) {
            console.log('Counter animasyonu başlatılamadı:', e);
            // Fallback: sayıları doğrudan göster
            $('.counter').each(function() {
                var finalValue = $(this).attr('data-count') || $(this).text();
                $(this).text(finalValue);
            });
        }
    } else {
        // Counter plugin yok, fallback kullan
        $('.counter').each(function() {
            var finalValue = $(this).attr('data-count') || $(this).text();
            $(this).text(finalValue);
        });
    }
    
    // WOW animasyonu
    if (typeof WOW !== 'undefined') {
        try {
            new WOW().init();
            console.log('WOW animasyonu başlatıldı');
        } catch (e) {
            console.log('WOW animasyonu başlatılamadı:', e);
            document.body.classList.add('storage-disabled');
        }
    }
    
    // Scroll to top button
    const scrollBtn = document.querySelector('.scroll-top');
    if (scrollBtn) {
        window.addEventListener('scroll', function() {
            if (window.pageYOffset > 300) {
                scrollBtn.style.opacity = '1';
                scrollBtn.style.display = 'block';
            } else {
                scrollBtn.style.opacity = '0';
                setTimeout(() => {
                    if (window.pageYOffset <= 300) {
                        scrollBtn.style.display = 'none';
                    }
                }, 300);
            }
        });
    }
    
    // Smooth scrolling
    document.addEventListener('click', function(e) {
        if (e.target.matches('a[href^="#"]')) {
            e.preventDefault();
            const target = document.querySelector(e.target.getAttribute('href'));
            if (target) {
                target.scrollIntoView({
                    behavior: 'smooth',
                    block: 'start'
                });
            }
        }
    });
    
    // Form submission güvenli hale getir
    document.addEventListener('submit', function(e) {
        const form = e.target;
        if (form.querySelector('input[type="email"]')) {
            const email = form.querySelector('input[type="email"]').value;
            if (!email || !email.includes('@')) {
                e.preventDefault();
                alert('Lütfen geçerli bir e-posta adresi girin.');
                return false;
            }
            
            const submitBtn = form.querySelector('button[type="submit"]');
            if (submitBtn) {
                submitBtn.disabled = true;
                submitBtn.innerHTML = 'Gönderiliyor...';
            }
        }
    });
    
    // Storage kullanımı güvenli hale getir
    if (safeStorage()) {
        try {
            localStorage.setItem('last_visit_hakkimizda', new Date().toISOString());
        } catch (e) {
            console.log('localStorage kullanılamıyor');
        }
    }
    
    // Preloader'ı güvenli şekilde kapat
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
    } else {
        console.log('Preloader bulunamadı');
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

// Error handling
window.addEventListener('error', function(e) {
    console.log('JavaScript hatası yakalandı:', e.error);
    if (e.error && e.error.message.includes('storage')) {
        console.log('Storage hatası tespit edildi, fallback moduna geçiliyor');
        document.body.classList.add('storage-disabled');
    }
});
</script>

<!-- Scroll to top button -->
<button class="scroll-top" onclick="window.scrollTo({top: 0, behavior: 'smooth'});">
    <i class="bi bi-arrow-up-short"></i>
</button>

</body>
</html>