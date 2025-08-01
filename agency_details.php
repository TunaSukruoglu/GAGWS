<?php 
session_start();

// Başarı mesajları
if (isset($_SESSION['register_success'])) {
    $msg = json_encode($_SESSION['register_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_success']);
}

if (isset($_SESSION['login_success'])) {
    $msg = json_encode($_SESSION['login_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_success']);
}

// Hata mesajları
if (isset($_SESSION['register_error'])) {
    $msg = json_encode($_SESSION['register_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['login_error'])) {
    $msg = json_encode($_SESSION['login_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_error']);
}
?>

<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="Gayrimenkul, Emlak, Satış, Kiralama, Gökhan Aydınlı">
	<meta name="description" content="Gökhan Aydınlı Gayrimenkul - İstanbul'da 17 yıllık deneyimle profesyonel emlak hizmetleri">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydnli.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Gökhan Aydınlı Gayrimenkul - Profesyonel Emlak Danışmanlığı">
	<meta name='og:image' content='images/assets/gokhan-aydinli.jpg'>
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
	<title>Gökhan Aydınlı - Broker & Emlak Uzmanı</title>
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


		
		<!-- ################### Login Modal ####################### -->
        <!-- Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content d-flex justify-content-center">
                    <form action="#">
                        <input type="text" placeholder="Daire, Villa, Ofis Ara...">
                        <button><i class="fa-light fa-arrow-right-long"></i></button>
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
							<a href="index.html" class="d-flex align-items-center">
								<img src="images/logoSiyah.png" alt="">
							</a>
						</div>
						<!-- logo -->
						<div class="right-widget ms-auto me-3 me-lg-0 order-lg-3">
							<ul class="d-flex align-items-center style-none">
								<li class="d-none d-md-inline-block me-4">
                                    <a href="dashboard/add-property.html" class="btn-ten rounded-0" target="_blank"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
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
                                    <li class="d-block d-lg-none"><div class="logo"><a href="index.html" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
                                    <li class="nav-item dashboard-menu">
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
                                        <a href="dashboard/add-property.html" class="btn-ten w-100 rounded-0" target="_blank"><span>İlan Ekle</span> <i class="bi bi-arrow-up-right"></i></a>
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
		<div class="inner-banner-two inner-banner z-1 pt-170 xl-pt-150 md-pt-130 pb-140 xl-pb-100 md-pb-80 position-relative" style="background-image: url(https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/f54802b4-a80a-4e46-abcb-a07e2cf82100/public);">
			<div class="container">
                <div class="row">
                    <div class="col-lg-6">
                        <h3 class="mb-45 xl-mb-30 md-mb-20">Broker Detayları</h3>
                        <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                            <li><a href="index.html">Ana Sayfa</a></li>
                            <li>/</li>
                            <li><a href="agency.html">Broker</a></li>
                            <li>/</li>
                            <li>Gökhan Aydınlı</li>
                        </ul>
                    </div>
                    <div class="col-lg-6">
                        <p class="sub-heading">Ticari gayrimenkul satış ve kiralama alanında 17 yıllık uzmanlığımla size değer katıyorum.</p>
                    </div>
                </div>
			</div>
		</div>
		<!-- /.inner-banner-two -->

		


		<!--
		=====================================================
			Agency Details Details
		=====================================================
		-->
		<div class="agency-details theme-details-one mt-130 xl-mt-100 pb-150 xl-pb-100">
			<div class="container">
				<div class="row">
					<div class="col-lg-8">
                        <div class="info-pack-one p-20 mb-80 xl-mb-50">
                            <div class="row">
                                <div class="col-xl-6 d-flex">
                                    <div class="media p-20 d-flex align-items-center justify-content-center bg-white position-relative z-1 w-100 me-xl-4">
                                        <div class="tag top-0 bg-dark text-white position-absolute text-uppercase">120+ İlan</div>
                                        <img src="https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/2fa3b196-b118-479b-4c17-468a6871e100/public" alt="Gökhan Aydınlı" style="border-radius: 10px; width: 200px; height: 250px; object-fit: cover;">
                                    </div>
                                </div>
                                <div class="col-xl-6">
                                    <div class="ps-xxl-3 pe-xxl-3 pt-40 lg-pt-30 pb-45 lg-pb-10">
                                        <h4>Gökhan Aydınlı</h4>
                                        <div class="designation fs-16">Lisanslı Broker & Gayrimenkul Uzmanı</div>
                                        <div class="table-responsive">
                                            <table class="table">
                                                <tbody>
                                                    <tr>
                                                        <td>Doğum: </td>
                                                        <td>1981, İstanbul </td>
                                                    </tr>
                                                    <tr>
                                                        <td>Telefon: </td>
                                                        <td>+90 212 456 78 90</td>
                                                    </tr>
                                                    <tr>
                                                        <td>E-posta:</td>
                                                        <td>gokhan@aydinligayrimenkul.com</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Website:</td>
                                                        <td>www.gokhanaydnli.com</td>
                                                    </tr>
                                                    <tr>
                                                        <td>Deneyim:</td>
                                                        <td>17+ Yıl (2007'den beri)</td>
                                                    </tr>
                                                </tbody>
                                            </table>
                                        </div>
                                        <ul class="style-none d-flex align-items-center social-icon">
                                            <li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
                                            <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                                            <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                            <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                                        </ul>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <!-- /.info-pack-one -->
						<div class="agency-overview bottom-line-dark pb-40 mb-80 xl-mb-50">
							<h4 class="mb-20">Profesyonel Geçmişim</h4>
							<p class="fs-20 lh-lg pb-15">1981 İstanbul doğumluyum ve 2007 yılından bu yana gayrimenkul sektöründe aktif olarak faaliyet göstermekteyim. Kariyerime Yeşilyurt'da konut pazarlama ile başladım ve zamanla kendimi bu alanda geliştirerek İstanbul'un en prestijli bölgelerinde önemli projelere imza attım.</p>
                            <p class="fs-20 lh-lg pb-15">Sektöre adım attığım ilk yıllarda konut pazarlama üzerine yoğunlaştım ve daha sonra Boğaz hattında bu deneyimimi geliştirmeye devam ettim. 2011 yılında ticari gayrimenkul alanına olan ilgim artmaya başladı ve bu alan benim uzmanlık alanım haline geldi.</p>
                            <p class="fs-20 lh-lg">2012 ile 2015 yılları arasında Şişli Dolapdere Caddesi'nde yaptığım satışlarla bir caddenin yapısını ve kimliğini değiştirdim. Bu dönemde gerçekleştirdiğim başarılı projeler ile bölgesel dönüşümde öncü rol oynadım ve sektörde tanınan bir isim oldum.</p>
						</div>
						<!-- /.agency-overview -->

						<div class="agency-overview bottom-line-dark pb-40 mb-80 xl-mb-50">
							<h4 class="mb-20">Kariyer Yolculuğum & Başarılarım</h4>
							<div class="timeline-section">
								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-primary text-white p-2 rounded text-center">
											<strong>2007-2011</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Konut Pazarlama Uzmanı</h5>
										<p>Yeşilyurt bölgesinde konut pazarlama ile gayrimenkul sektörüne adım attım. Boğaz hattında konut projelerinde deneyim kazandım ve müşteri portföyümü geliştirdim. Bu dönemde sektörün temellerini öğrendim.</p>
									</div>
								</div>
								
								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-success text-white p-2 rounded text-center">
											<strong>2011-2012</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Ticari Gayrimenkul Keşfi</h5>
										<p>Ticari gayrimenkul alanına ilgi duymaya başladım. Bu dönemde sektörün ticari boyutunu öğrenmeye odaklandım ve uzmanlaşacağım alanı belirlemeye başladım. Konut pazarlamadan ticari alana geçiş sürecim başladı.</p>
									</div>
								</div>

								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-warning text-white p-2 rounded text-center">
											<strong>2012-2015</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Şişli Dolapdere Dönüşüm Projesi</h5>
										<p>Şişli Dolapdere Caddesi'nde gerçekleştirdiğim satışlarla bir caddenin yapısını ve kimliğini değiştirdim. Bu dönemde bölgesel dönüşüm projelerinde öncü rol oynadım ve sektörde ismimi duyurdum. Bu başarılı proje kariyerim için dönüm noktası oldu.</p>
									</div>
								</div>

								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-info text-white p-2 rounded text-center">
											<strong>2015-2017</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Prestijli Otel Pazarlama Uzmanı</h5>
										<p>İstanbul'un sayılı otellerinin pazarlanması sürecini üstlendim. Turizm ve otelcilik sektöründe gayrimenkul danışmanlığı alanında kendimi geliştirdim. Bu dönemde büyük ölçekli ticari projelerde uzmanlaştım.</p>
									</div>
								</div>

								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-secondary text-white p-2 rounded text-center">
											<strong>2016-2018</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Bölgesel Arsa Danışmanlığı</h5>
										<p>Silivri, Çanakkale ve Adapazarı'nda birçok yatırımcıya arsa temini konusunda danışmanlık verdim. Bu dönemde faaliyet alanımı İstanbul dışına da genişlettim ve farklı şehirlerde yatırım fırsatları geliştirdim.</p>
									</div>
								</div>

								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-dark text-white p-2 rounded text-center">
											<strong>2017-2018</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Yenibosna & Güngören Ticari Alanlar</h5>
										<p>Yenibosna ve Güngören'de ticari alanlar ve yenilenme merkezine girmekte olan ticari arsaları pazarladım. Satışlarına aracılık ettim. Bu bölgelerdeki kentsel dönüşüm projelerinde aktif rol aldım ve bölgenin değer artışına katkı sağladım.</p>
									</div>
								</div>

								<div class="row mb-4">
									<div class="col-md-3">
										<div class="year-badge bg-primary text-white p-2 rounded text-center">
											<strong>2018-2024</strong>
										</div>
									</div>
									<div class="col-md-9">
										<h5>Lisanslı Broker & Gayrimenkul Uzmanı</h5>
										<p>Yerel pazarlardaki Gayrimenkul ofislerinden farklı bir noktada konumlandırdığım çalışmalarımı yüksek motivasyon ve titizlikle sürdürmekteyim. Lisanslı broker olarak konut, ticari gayrimenkul ve yatırım danışmanlığı alanlarında kapsamlı hizmet sunmaya devam ediyorum.</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /.agency-overview -->

						<!-- Başarı İstatistikleri -->
						<div class="achievement-stats bg-light p-30 rounded mb-80 xl-mb-50">
							<h4 class="mb-30 text-center">Başarı İstatistiklerim</h4>
							<div class="row text-center">
								<div class="col-md-3 col-6 mb-30">
									<div class="counter-item">
										<div class="counter-number fs-1 fw-bold text-primary">17+</div>
										<div class="counter-text">Yıl Deneyim</div>
									</div>
								</div>
								<div class="col-md-3 col-6 mb-30">
									<div class="counter-item">
										<div class="counter-number fs-1 fw-bold text-success">500+</div>
										<div class="counter-text">Başarılı Satış</div>
									</div>
								</div>
								<div class="col-md-3 col-6 mb-30">
									<div class="counter-item">
										<div class="counter-number fs-1 fw-bold text-warning">15+</div>
										<div class="counter-text">Büyük Proje</div>
									</div>
								</div>
								<div class="col-md-3 col-6 mb-30">
									<div class="counter-item">
										<div class="counter-number fs-1 fw-bold text-info">1000+</div>
										<div class="counter-text">Mutlu Müşteri</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /.achievement-stats -->

						<!-- Uzmanlık Alanları -->
						<div class="agency-overview bottom-line-dark pb-40 mb-80 xl-mb-50">
							<h4 class="mb-20">Uzmanlık Alanlarım</h4>
							<div class="row">
								<div class="col-md-6 mb-30">
									<div class="service-item bg-white p-25 rounded shadow-sm">
										<div class="d-flex align-items-center mb-15">
											<i class="fas fa-home fa-2x text-primary me-3"></i>
											<h5 class="mb-0">Konut Gayrimenkul</h5>
										</div>
										<p class="fs-16 text-muted">Daire, villa, rezidans satış ve kiralama işlemleri. İstanbul'un her bölgesinde konut danışmanlığı hizmeti.</p>
									</div>
								</div>
								<div class="col-md-6 mb-30">
									<div class="service-item bg-white p-25 rounded shadow-sm">
										<div class="d-flex align-items-center mb-15">
											<i class="fas fa-building fa-2x text-success me-3"></i>
											<h5 class="mb-0">Ticari Gayrimenkul</h5>
										</div>
										<p class="fs-16 text-muted">Ofis, mağaza, depo ve endüstriyel alan danışmanlığı. Ticari yatırım projeleri geliştirme.</p>
									</div>
								</div>
								<div class="col-md-6 mb-30">
									<div class="service-item bg-white p-25 rounded shadow-sm">
										<div class="d-flex align-items-center mb-15">
											<i class="fas fa-chart-line fa-2x text-warning me-3"></i>
											<h5 class="mb-0">Yatırım Danışmanlığı</h5>
										</div>
										<p class="fs-16 text-muted">Gayrimenkul yatırım stratejileri ve pazar analizi. Portföy çeşitlendirme önerileri.</p>
									</div>
								</div>
								<div class="col-md-6 mb-30">
									<div class="service-item bg-white p-25 rounded shadow-sm">
										<div class="d-flex align-items-center mb-15">
											<i class="fas fa-map-marked-alt fa-2x text-info me-3"></i>
											<h5 class="mb-0">Arsa & Arazi</h5>
										</div>
										<p class="fs-16 text-muted">İmarlı arsa, tarla ve arazi alım-satım işlemleri. Şehir dışı yatırım fırsatları.</p>
									</div>
								</div>
							</div>
						</div>
						<!-- /.expertise-areas -->

						<div class="agent-property-listing bottom-line-dark pb-20 mb-80 xl-mb-50">
							<div class="d-sm-flex justify-content-between align-items-center mb-40 xs-mb-20">
                                <h4 class="mb-10">Güncel İlanlarım</h4>
                                <div class="filter-nav-one xs-mt-40">
                                    <ul class="style-none d-flex justify-content-center flex-wrap isotop-menu-wrapper">
                                        <li class="is-checked" data-filter="*">Tümü</li>
                                        <li data-filter=".sell">Satılık</li>
                                        <li data-filter=".rent">Kiralık</li>
                                    </ul>
                                </div>
                            </div>
                            <div id="isotop-gallery-wrapper" class="grid-2column">
                                <div class="grid-sizer"></div>
                                <div class="isotop-item rent">
                                    <div class="listing-card-one shadow-none style-two mb-50">
										<div class="img-gallery">
											<div class="position-relative overflow-hidden">
												<div class="tag bg-white text-dark fw-500">KİRALIK</div>
												<img src="images/listing/img_69.jpg" class="w-100" alt="...">
												
												<div class="img-slider-btn">
													03 <i class="fa-regular fa-image"></i>
													<a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="img1" data-caption="Lüks Daire"></a>
													<a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="img1" data-caption="Lüks Daire"></a>
													<a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="img1" data-caption="Lüks Daire"></a>
												</div>
											</div>
										</div>
										<!-- /.img-gallery -->
										<div class="property-info d-flex justify-content-between align-items-end pt-30">
											<div>
												<strong class="price fw-500 color-dark">₺35.000/ <sub>ay</sub></strong>
												<div class="address pt-5 m0">Şişli, İstanbul</div>
											</div>
											<a href="#" class="btn-four mb-5"><i class="bi bi-arrow-up-right"></i></a>
										</div>
										<!-- /.property-info -->
									</div>
									<!-- /.listing-card-one -->
                                </div>
                                <div class="isotop-item sell">
                                    <div class="listing-card-one shadow-none style-two mb-50">
										<div class="img-gallery">
											<div class="position-relative overflow-hidden">
												<div class="tag bg-white text-dark fw-500">SATILIK</div>
												<img src="images/listing/img_70.jpg" class="w-100" alt="...">
												
												<div class="img-slider-btn">
													05 <i class="fa-regular fa-image"></i>
													<a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="img2" data-caption="Ticari Alan"></a>
													<a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="img2" data-caption="Ticari Alan"></a>
													<a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="img2" data-caption="Ticari Alan"></a>
												</div>
											</div>
										</div>
										<!-- /.img-gallery -->
										<div class="property-info d-flex justify-content-between align-items-end pt-30">
											<div>
												<strong class="price fw-500 color-dark">₺2.850.000</strong>
												<div class="address pt-5 m0">Yenibosna, İstanbul</div>
											</div>
											<a href="#" class="btn-four mb-5"><i class="bi bi-arrow-up-right"></i></a>
										</div>
										<!-- /.property-info -->
									</div>
									<!-- /.listing-card-one -->
                                </div>
                                <div class="isotop-item sell">
                                    <div class="listing-card-one shadow-none style-two mb-50">
										<div class="img-gallery">
											<div class="position-relative overflow-hidden">
												<div class="tag bg-white text-dark fw-500">SATILIK</div>
												<img src="images/listing/img_71.jpg" class="w-100" alt="...">
												
												<div class="img-slider-btn">
													08 <i class="fa-regular fa-image"></i>
													<a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="img3" data-caption="Villa"></a>
													<a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="img3" data-caption="Villa"></a>
													<a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="img3" data-caption="Villa"></a>
												</div>
											</div>
										</div>
										<!-- /.img-gallery -->
										<div class="property-info d-flex justify-content-between align-items-end pt-30">
											<div>
												<strong class="price fw-500 color-dark">₺8.750.000</strong>
												<div class="address pt-5 m0">Boğaz Hattı, İstanbul</div>
											</div>
											<a href="#" class="btn-four mb-5"><i class="bi bi-arrow-up-right"></i></a>
										</div>
										<!-- /.property-info -->
									</div>
									<!-- /.listing-card-one -->
                                </div>
                                <div class="isotop-item rent">
                                    <div class="listing-card-one shadow-none style-two mb-50">
										<div class="img-gallery">
											<div class="position-relative overflow-hidden">
												<div class="tag bg-white text-dark fw-500">KİRALIK</div>
												<img src="images/listing/img_72.jpg" class="w-100" alt="...">
												
												<div class="img-slider-btn">
													04 <i class="fa-regular fa-image"></i>
													<a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="img4" data-caption="Ofis"></a>
													<a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="img4" data-caption="Ofis"></a>
													<a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="img4" data-caption="Ofis"></a>
												</div>
											</div>
										</div>
										<!-- /.img-gallery -->
										<div class="property-info d-flex justify-content-between align-items-end pt-30">
											<div>
												<strong class="price fw-500 color-dark">₺18.500/ <sub>ay</sub></strong>
												<div class="address pt-5 m0">Güngören, İstanbul</div>
											</div>
											<a href="#" class="btn-four mb-5"><i class="bi bi-arrow-up-right"></i></a>
										</div>
										<!-- /.property-info -->
									</div>
									<!-- /.listing-card-one -->
                                </div>
                            </div>
						</div>
						<!-- /.agent-property-listing -->

						<div class="review-panel-one bottom-line-dark pb-40 mb-80 xl-mb-50">
							<div class="position-relative z-1">
								<div class="d-sm-flex justify-content-between align-items-center mb-10">
									<h4 class="m0 xs-pb-30">Müşteri Yorumları (4.9 Puan)</h4>
									<select class="nice-select rounded-0">
										<option value="0">En Yeni</option>
										<option value="1">En İyi Puan</option>
										<option value="2">En Uygun</option>
									</select>
								</div>
								<div class="review-wrapper mb-35">
									<div class="review">
										<img src="images/media/img_01.jpg" alt="" class="rounded-circle avatar">
										<div class="text">
											<div class="d-sm-flex justify-content-between">
												<div>
													<h6 class="name">Mehmet Özkan</h6>
													<div class="time fs-16">15 Oca, 24</div>
												</div>
												<ul class="rating style-none d-flex xs-mt-10">
													<li><span class="fst-italic me-2">(5.0 Puan)</span> </li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
												</ul>
											</div>
											<p class="fs-20 mt-20 mb-30">Gökhan Bey ile çalışmak gerçekten çok keyifliydi. Dolapdere'de aldığımız ticari alanda hem fiyat hem de lokasyon konusunda bize çok iyi rehberlik etti. Profesyonel yaklaşımı ve sektör bilgisi mükemmel.</p>
											<div class="d-flex review-help-btn">
												<a href="#" class="me-5"><i class="fa-sharp fa-regular fa-thumbs-up"></i> <span>Faydalı</span></a>
												<a href="#"><i class="fa-sharp fa-regular fa-flag-swallowtail"></i> <span>Bildir</span></a>
											</div>
										</div>
										<!-- /.text -->
									</div>
									<!-- /.review -->

									<div class="review">
										<img src="images/media/img_03.jpg" alt="" class="rounded-circle avatar">
										<div class="text">
											<div class="d-sm-flex justify-content-between">
												<div>
													<h6 class="name">Ayşe Demir</h6>
													<div class="time fs-16">28 Ara, 23</div>
												</div>
												<ul class="rating style-none d-flex xs-mt-10">
													<li><span class="fst-italic me-2">(4.8 Puan)</span> </li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
												</ul>
											</div>
											<p class="fs-20 mt-20 mb-30">17 yıllık deneyimi gerçekten belli oluyor. Silivri'de arsa yatırımı yapmak istediğimizde bize en doğru lokasyonu önerdi. Şu an değer artışından çok memnunuz. Teşekkürler Gökhan Bey!</p>
											<ul class="style-none d-flex flex-wrap review-gallery pb-30">
												<li><a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="revImg" data-caption="Silivri Arsası"><img src="images/listing/img_48.jpg" alt=""></a></li>
												<li><a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="revImg" data-caption="Silivri Arsası"><img src="images/listing/img_49.jpg" alt=""></a></li>
												<li><a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="revImg" data-caption="Silivri Arsası"><img src="images/listing/img_50.jpg" alt=""></a></li>
											</ul>
											<div class="d-flex review-help-btn">
												<a href="#" class="me-5"><i class="fa-sharp fa-regular fa-thumbs-up"></i> <span>Faydalı</span></a>
												<a href="#"><i class="fa-sharp fa-regular fa-flag-swallowtail"></i> <span>Bildir</span></a>
											</div>
											
										</div>
										<!-- /.text -->
									</div>
									<!-- /.review -->

									<div class="review hide">
										<img src="images/media/img_02.jpg" alt="" class="rounded-circle avatar">
										<div class="text">
											<div class="d-sm-flex justify-content-between">
												<div>
													<h6 class="name">Can Yılmaz</h6>
													<div class="time fs-16">10 Kas, 23</div>
												</div>
												<ul class="rating style-none d-flex xs-mt-10">
													<li><span class="fst-italic me-2">(4.9 Puan)</span> </li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
													<li><i class="fa-sharp fa-solid fa-star"></i></li>
												</ul>
											</div>
											<p class="fs-20 mt-20 mb-30">Yenibosna'da aldığımız ticari alan için çok doğru yönlendirme yaptı. Hem fiyat hem de gelecek potansiyeli açısından harika bir yatırım oldu. Kesinlikle tavsiye ederim.</p>
											<div class="d-flex review-help-btn">
												<a href="#" class="me-5"><i class="fa-sharp fa-regular fa-thumbs-up"></i> <span>Faydalı</span></a>
												<a href="#"><i class="fa-sharp fa-regular fa-flag-swallowtail"></i> <span>Bildir</span></a>
											</div>
										</div>
										<!-- /.text -->
									</div>
									<!-- /.review -->
								</div>
								<!-- /.review-wrapper -->
								<div class="load-more-review text-uppercase fw-500 w-100 inverse rounded-0 tran3s">TÜM 156 YORUMU GÖR <i class="bi bi-arrow-up-right"></i></div>
							</div>						
						</div>
						<!-- /.review-panel-one -->

						<div class="review-form">
							<h4 class="mb-20">Yorum Bırakın</h4>
							<p class="fs-20 lh-lg pb-15">Yorum yapmak için <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="color-dark fw-500 text-decoration-underline">giriş yapın</a> veya hesabınız yoksa kayıt olun.</p>
							
							<div class="bg-dot p-30">
								<form action="#" class="bg-white p-40">
									<div class="row">
										<div class="col-12">
											<div class="input-box-two mb-30">
												<div class="label">Başlık*</div>
												<input type="text" placeholder="Yorum başlığı" class="type-input rounded-0">
											</div>
											<!-- /.input-box-two -->
										</div>
										<div class="col-lg-6">
											<div class="input-box-two mb-30">
												<div class="label">E-posta*</div>
												<input type="email" placeholder="ornek@email.com" class="type-input rounded-0">
											</div>
											<!-- /.input-box-two -->
										</div>
										<div class="col-lg-6">
											<div class="input-box-two mb-30">
												<div class="label">Puan*</div>
												<select class="nice-select rounded-0">
													<option value="0">Puan Verin</option>
													<option value="1">5 Yıldız</option>
													<option value="1">4 Yıldız</option>
													<option value="1">3 Yıldız</option>
													<option value="1">2 Yıldız</option>
													<option value="1">1 Yıldız</option>
												</select>
											</div>
											<!-- /.input-box-two -->
										</div>
										<div class="col-12">
											<div class="input-box-two mb-30">
												<textarea placeholder="Yorumunuzu buraya yazın..." class="rounded-0"></textarea>
											</div>
											<!-- /.input-box-two -->
										</div>
									</div>
									<button class="btn-five text-uppercase rounded-0 sm">YORUM GÖNDER</button>
								</form>
							</div>
						</div>
						<!-- /.review-form -->
					</div>
					<div class="col-lg-4">
						<div class="theme-sidebar-one dot-bg p-30 ms-xxl-3 md-mt-60">
							<div class="tour-schedule bg-white p-30 mb-40">
								<h5 class="mb-40">İletişim Formu</h5>
								<form action="#">
									<div class="input-box-three mb-25">
										<div class="label">E-posta Adresiniz*</div>
										<input type="email" placeholder="E-posta adresinizi girin" class="type-input rounded-0">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Telefon Numaranız*</div>
										<input type="tel" placeholder="Telefon numaranız" class="type-input rounded-0">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-15">
										<div class="label">Mesajınız*</div>
										<textarea placeholder="Merhaba, [Gayrimenkul türü] ile ilgileniyorum..." class="rounded-0"></textarea>
									</div>
									<!-- /.input-box-three -->
									<button class="btn-nine text-uppercase w-100 mb-20">MESAJ GÖNDER</button>
								</form>
                                <a href="tel:+902124567890" class="btn-eight sm text-uppercase w-100 rounded-0 tran3s">HEMEN ARA</a>
							</div>
							<!-- /.tour-schedule -->

							<div class="agent-finder bg-white p-30">
								<h5 class="mb-40">Hızlı İletişim</h5>
								<div class="contact-methods">
									<div class="contact-item d-flex align-items-center mb-20 p-15 border rounded">
										<i class="fab fa-whatsapp fa-2x text-success me-3"></i>
										<div>
											<h6 class="mb-0">WhatsApp</h6>
											<small class="text-muted">Anında mesajlaşma</small>
										</div>
									</div>
									<div class="contact-item d-flex align-items-center mb-20 p-15 border rounded">
										<i class="fas fa-phone fa-2x text-primary me-3"></i>
										<div>
											<h6 class="mb-0">Telefon</h6>
											<small class="text-muted">+90 212 456 78 90</small>
										</div>
									</div>
									<div class="contact-item d-flex align-items-center mb-20 p-15 border rounded">
										<i class="fas fa-envelope fa-2x text-warning me-3"></i>
										<div>
											<h6 class="mb-0">E-posta</h6>
											<small class="text-muted">gokhan@aydinli.com</small>
										</div>
									</div>
								</div>

								<!-- Çalışma Saatleri -->
								<div class="mt-30 p-20 bg-light rounded">
									<h6 class="mb-15"><i class="fas fa-clock me-2"></i>Çalışma Saatleri</h6>
									<div class="working-hours">
										<div class="d-flex justify-content-between mb-5">
											<span class="fs-14">Pazartesi - Cuma:</span>
											<span class="fs-14 fw-500">09:00 - 18:00</span>
										</div>
										<div class="d-flex justify-content-between mb-5">
											<span class="fs-14">Cumartesi:</span>
											<span class="fs-14 fw-500">10:00 - 16:00</span>
										</div>
										<div class="d-flex justify-content-between">
											<span class="fs-14">Pazar:</span>
											<span class="fs-14 fw-500">Randevulu</span>
										</div>
									</div>
								</div>

								<!-- Hizmetler -->
								<div class="mt-30 p-20 bg-light rounded">
									<h6 class="mb-15">Sunduğum Hizmetler</h6>
									<ul class="list-unstyled service-list">
										<li class="mb-10"><i class="fas fa-check text-success me-2"></i> Gayrimenkul Değerleme</li>
										<li class="mb-10"><i class="fas fa-check text-success me-2"></i> Yatırım Danışmanlığı</li>
										<li class="mb-10"><i class="fas fa-check text-success me-2"></i> Mortgage Desteği</li>
										<li class="mb-10"><i class="fas fa-check text-success me-2"></i> Hukuki Süreç Takibi</li>
										<li class="mb-10"><i class="fas fa-check text-success me-2"></i> After-Sales Destek</li>
										<li><i class="fas fa-check text-success me-2"></i> 7/24 Danışmanlık</li>
									</ul>
								</div>
							</div>
							<!-- /.agent-finder -->

						</div>
						<!-- /.theme-sidebar-one -->
					</div>
				</div>
			</div>
		</div>
		<!-- /.agency-details -->

		


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
							<h3 class="text-white m0">Gayrimenkul <span>Yolculuğunuza<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Birlikte Başlayalım.</h3>
						</div>
						<!-- /.title-one -->
					</div>
					<div class="col-lg-6">
						<div class="form-wrapper me-auto ms-auto me-lg-0">
							<form action="#">
								<input type="email" placeholder="E-posta adresiniz" class="rounded-0">
								<button class="rounded-0">Başlayın</button>
							</form>
							<div class="fs-16 mt-10 text-white">Zaten müşterimiz misiniz? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın.</a></div>
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
									<a href="index.html">
										<img src="images/logo/logo_06.svg" alt="">
									</a>
								</div> 
								<!-- logo -->
								<p class="mb-30 xs-mb-20">Şişli, İstanbul merkezli profesyonel gayrimenkul danışmanlığı hizmetleri</p>
								<a href="#" class="email tran3s mb-60 md-mb-30">gokhan@aydinligayrimenkul.com</a>
								<ul class="style-none d-flex align-items-center social-icon">
									<li><a href="#"><i class="fa-brands fa-facebook-f"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-twitter"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
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
								<h5 class="footer-title">Yeni İlanlar</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="listing_01.html">Daire Satın Al</a></li>
									<li><a href="listing_02.html">Rezidans Satın Al</a></li>
									<li><a href="listing_03.html">Ev Kirala</a></li>
									<li><a href="listing_04.html">Endüstriyel Kirala</a></li>
									<li><a href="listing_05.html">Villa Satın Al</a></li>
									<li><a href="listing_06.html">Ofis Kirala</a></li>
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