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
	<meta name="keywords" content="Ticari kiralama, Ofis kiralama, Dükkan kiralama, Gökhan Aydınlı">
	<meta name="description" content="Ticari ofis ve dükkan kiralama süreçlerinde dikkat edilmesi gereken önemli noktalar ve uzman tavsiyeleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul Blog">
    <meta property="og:url" content="https://gokhanaydnli.com/blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Ticari Ofis ve Dükkan Kiralarken Dikkat Edilmesi Gereken 7 Önemli Nokta">
	<meta name='og:image' content='images/blog/ticari-kiralama.jpg'>
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
	<title>Ticari Ofis ve Dükkan Kiralarken Dikkat Edilmesi Gereken 7 Önemli Nokta - Gökhan Aydınlı Blog</title>
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
                        <input type="text" placeholder="Blog yazıları ara...">
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
								<img src="images/logo/logo_06.svg" alt="">
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
									<li class="d-block d-lg-none"><div class="logo"><a href="index.html" class="d-block"><img src="images/logo/logo_06.svg" alt=""></a></div></li>
									<li class="nav-item dashboard-menu">
										<a class="nav-link" href="dashboard/dashboard-index.html" target="_blank">Panel</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
											data-bs-auto-close="outside" aria-expanded="false">Ana Sayfa
										</a>
										<ul class="dropdown-menu">
											<li><a href="index.html" class="dropdown-item"><span>Ana Sayfa 01</span></a></li>
											<li><a href="index-2.html" class="dropdown-item"><span>Ana Sayfa 02</span></a></li>
											<li><a href="index-3.html" class="dropdown-item"><span>Ana Sayfa 03</span></a></li>
											<li><a href="index-4.html" class="dropdown-item"><span>Ana Sayfa 04</span></a></li>
											<li><a href="index-5.html" class="dropdown-item"><span>Ana Sayfa 05</span></a></li>
											<li><a href="index-6.html" class="dropdown-item"><span>Ana Sayfa 06</span></a></li>
											<li><a href="index-7.html" class="dropdown-item"><span>Ana Sayfa 07</span></a></li>
											<li><a href="index-8.html" class="dropdown-item"><span>Ana Sayfa 08</span></a></li>
										</ul>
									</li>
									<li class="nav-item dropdown mega-dropdown-sm">
							        	<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">İlanlar
										</a>
						                <ul class="dropdown-menu">
							                <li class="row gx-1">
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">İlan Tipleri</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="listing_01.html" class="dropdown-item"><span>Grid Sidebar-1</span></a></li>
															<li><a href="listing_05.html" class="dropdown-item"><span>Grid Sidebar-2</span></a></li>
															<li><a href="listing_02.html" class="dropdown-item"><span>List Sidebar-1</span></a></li>
															<li><a href="listing_06.html" class="dropdown-item"><span>List Sidebar-2</span></a></li>
															<li><a href="listing_03.html" class="dropdown-item"><span>Grid Top Filter-1</span></a></li>
															<li><a href="listing_07.html" class="dropdown-item"><span>Grid Top Filter-2</span></a></li>
															<li><a href="listing_04.html" class="dropdown-item"><span>List Top Filter-1</span></a></li>
															<li><a href="listing_08.html" class="dropdown-item"><span>List Top Filter-2</span></a></li>
															<li><a href="listing_09.html" class="dropdown-item"><span>Grid Banner Filter-1</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">İlan Tipleri</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="listing_11.html" class="dropdown-item"><span>Grid Banner Filter-2</span></a></li>
															<li><a href="listing_10.html" class="dropdown-item"><span>List Banner Filter-1</span></a></li>
															<li><a href="listing_12.html" class="dropdown-item"><span>List Banner Filter-2</span></a></li>
															<li><a href="listing_13.html" class="dropdown-item"><span>Grid Fullwidth</span></a></li>
															<li><a href="listing_14.html" class="dropdown-item"><span>Grid Fullwidth Map-1</span></a></li>
															<li><a href="listing_16.html" class="dropdown-item"><span>Grid Fullwidth Map-2</span></a></li>
															<li><a href="listing_15.html" class="dropdown-item"><span>List Fullwidth Map-1</span></a></li>
															<li><a href="listing_17.html" class="dropdown-item"><span>List Fullwidth Map-2</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">İlan Detayları</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="listing_details_01.html" class="dropdown-item"><span>İlan Detayları-1</span></a></li>
															<li><a href="listing_details_02.html" class="dropdown-item"><span>İlan Detayları-2</span></a></li>
															<li><a href="listing_details_03.html" class="dropdown-item"><span>İlan Detayları-3</span></a></li>
															<li><a href="listing_details_04.html" class="dropdown-item"><span>İlan Detayları-4</span></a></li>
															<li><a href="listing_details_05.html" class="dropdown-item"><span>İlan Detayları-5</span></a></li>
															<li><a href="listing_details_06.html" class="dropdown-item"><span>İlan Detayları-6</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
											</li>
						                </ul>
						            </li>
									<li class="nav-item dropdown mega-dropdown-sm">
										<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
											data-bs-auto-close="outside" aria-expanded="false">Sayfalar
										</a>
										<ul class="dropdown-menu">
							                <li class="row gx-1">
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">Temel</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="about_us_01.html" class="dropdown-item"><span>Hakkımızda -1</span></a></li>
															<li><a href="about_us_02.html" class="dropdown-item"><span>Hakkımızda -2</span></a></li>
															<li><a href="agency.html" class="dropdown-item"><span>Acenteler</span></a></li>
															<li><a href="agency_details.html" class="dropdown-item"><span>Acente Detayları</span></a></li>
															<li><a href="agent.html" class="dropdown-item"><span>Broker</span></a></li>
															<li><a href="agent_details.html" class="dropdown-item"><span>Broker Detayları</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">Özellikler</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="project_01.html" class="dropdown-item"><span>Projeler -1</span></a></li>
															<li><a href="project_02.html" class="dropdown-item"><span>Projeler -2</span></a></li>
															<li><a href="project_03.html" class="dropdown-item"><span>Projeler -3</span></a></li>
															<li><a href="project_04.html" class="dropdown-item"><span>Projeler -4</span></a></li>
															<li><a href="project_details_01.html" class="dropdown-item"><span>Proje Detayları</span></a></li>
															<li><a href="service_01.html" class="dropdown-item"><span>Hizmetler -1</span></a></li>
															<li><a href="service_02.html" class="dropdown-item"><span>Hizmetler -2</span></a></li>
															<li><a href="service_details.html" class="dropdown-item"><span>Hizmet Detayları</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
												<div class="col-lg-4">
													<div class="menu-column">
														<h6 class="mega-menu-title">Diğer</h6>
														<ul class="style-none mega-dropdown-list">
															<li><a href="compare.html" class="dropdown-item"><span>Emlak Karşılaştır</span></a></li>
															<li><a href="pricing_01.html" class="dropdown-item"><span>Fiyatlar -1</span></a></li>
															<li><a href="pricing_02.html" class="dropdown-item"><span>Fiyatlar -2</span></a></li>
															<li><a href="contact.html" class="dropdown-item"><span>İletişim</span></a></li>
															<li><a href="faq.html" class="dropdown-item"><span>S.S.S</span></a></li>
															<li><a href="404.html" class="dropdown-item"><span>404-Hata</span></a></li>
														</ul>
													</div> <!--/.menu-column -->
												</div>
											</li>
						                </ul>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link dropdown-toggle" href="#" role="button" data-bs-toggle="dropdown"
											data-bs-auto-close="outside" aria-expanded="false">Blog
										</a>
										<ul class="dropdown-menu">
											<li><a href="blog_01.html" class="dropdown-item"><span>Blog Grid</span></a></li>
											<li><a href="blog_02.html" class="dropdown-item"><span>Blog List</span></a></li>
											<li><a href="blog_03.html" class="dropdown-item"><span>Blog 2 Sütun</span></a></li>
											<li><a href="blog_details.html" class="dropdown-item"><span>Blog Detayları</span></a></li>
										</ul>
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
		=====================================================
			Blog Details
		=====================================================
		-->
		<div class="blog-details border-top mt-130 xl-mt-100 pt-100 xl-pt-80 mb-150 xl-mb-100">
			<div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <div class="blog-post-meta mb-60 lg-mb-40">
                            <div class="post-info"><a href="agent_details.html">Gökhan Aydınlı .</a> 8 dk okuma</div>
                            <h3 class="blog-title">Ticari Ofis ve Dükkan Kiralarken Dikkat Edilmesi Gereken 7 Önemli Nokta</h3>
                        </div>
                    </div>
                </div>
				<div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-post-meta">
                            <figure class="post-img position-relative m0" style="background-image: url(https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80);">
                                <div class="fw-500 date d-inline-block">15 OCA</div>
                            </figure>
                            <div class="post-data pt-50 md-pt-30">
                                <p>İş dünyasında doğru lokasyonda ticari alan kiralama kararı, işletmenizin başarısını doğrudan etkileyen kritik bir adımdır. 17 yıllık gayrimenkul deneyimim boyunca, birçok girişimcinin ticari kiralama sürecinde karşılaştığı zorlukları ve başarı hikayelerini yakından gözlemledim. Bu yazıda, ticari ofis ve dükkan kiralarken dikkat etmeniz gereken en önemli noktaları sizlerle paylaşıyorum.</p>
                                
                                <p>Ticari gayrimenkul kiralama, konut kiralamasından çok daha karmaşık bir süreçtir. Sadece fiziksel mekanın özellikleri değil, aynı zamanda yasal düzenlemeler, ticari potansiyel ve gelecekteki değer artışı gibi faktörler de göz önünde bulundurulmalıdır.</p>
                                
                                <div class="quote-wrapper">
                                    <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"><img src="images/lazy.svg" data-src="images/icon/icon_67.svg" alt="" class="lazy-img"></div>
                                    <div class="row">
                                        <div class="col-xxl-10 col-xl-11 col-lg-12 col-md-9 m-auto">
                                            <h4>"Doğru lokasyon seçimi, işletmenizin başarısının %70'ini belirler. Geri kalan %30 ise sizin çabanızla şekillenir."</h4>
                                        </div>
                                    </div>
                                    <h6>Gökhan Aydınlı. <span>Gayrimenkul Uzmanı</span></h6>
                                </div>
                                
                                <h5>1. Lokasyon Analizi: İşinizin Kalbi</h5>
                                <p>Lokasyon seçimi, ticari gayrimenkul kiralamasının en kritik unsurudur. Sadece merkezi bir yer olması yeterli değildir; hedef kitlenizin erişebileceği, görünürlüğü yüksek ve gelecekte değer kazanma potansiyeli olan bir nokta seçmelisiniz.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Hedef kitle analizi:</strong> Müşterilerinizin demografik özellikleri ve alışveriş alışkanlıkları</li>
                                    <li><strong>Ulaşım imkanları:</strong> Toplu taşıma, otopark, ana yollar</li>
                                    <li><strong>Rekabet analizi:</strong> Çevredeki benzer işletmeler ve rekabet yoğunluğu</li>
                                    <li><strong>Gelecek projeksiyonları:</strong> Bölgenin gelişim planları ve yatırım potansiyeli</li>
                                </ul>
                                
                                <h5>2. Sözleşme Şartları ve Yasal Haklar</h5>
                                <p>Ticari kira sözleşmeleri, Türk Borçlar Kanunu'na göre düzenlenir ve konut kiralarından farklı hükümlere sahiptir. Bu farkları bilmek, gelecekte yaşayabileceğiniz sorunların önüne geçer.</p>
                                
                                <p><strong>Önemli sözleşme maddeleri:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Kira artış oranları ve periyodları</li>
                                    <li>Tahliye koşulları ve süreleri</li>
                                    <li>Alt kiralama hakları</li>
                                    <li>Tadilat ve değişiklik izinleri</li>
                                    <li>Hasar ve sorumluluk paylaşımı</li>
                                </ul>
                                
                                <div class="img-meta"><img src="https://images.unsplash.com/photo-1454165804606-c3d57bc86b40?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Ticari sözleşme imzalama" class="lazy-img w-100"></div>
                                <div class="img-caption">Sözleşme detaylarını imzadan önce dikkatli şekilde incelemeniz kritik önem taşır</div>
                                
                                <h5>3. Mali Analiz ve Bütçe Planlaması</h5>
                                <p>Ticari kiralama sadece aylık kira bedelinden ibaret değildir. Kapsamlı bir mali analiz yaparak, tüm maliyetleri önceden hesaplamanız gerekir.</p>
                                
                                <p><strong>Toplam maliyet kalemleri:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Aylık kira bedeli</li>
                                    <li>Depozito (genellikle 3-6 aylık kira)</li>
                                    <li>Emlak vergisi</li>
                                    <li>Apartman aidatı</li>
                                    <li>Elektrik, su, doğalgaz</li>
                                    <li>İnternet ve telefon altyapısı</li>
                                    <li>Güvenlik sistemi</li>
                                    <li>Tadilat ve dekorasyon giderleri</li>
                                </ul>
                                
                                <h5>4. Teknik İnceleme ve Altyapı Kontrolü</h5>
                                <p>Ticari alanın fiziksel durumu, işletmenizin verimliliğini doğrudan etkiler. Detaylı bir teknik inceleme yapmadan kira sözleşmesi imzalamayın.</p>
                                
                                <p><strong>Kontrol edilmesi gereken teknik unsurlar:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Elektrik altyapısının iş yüküne uygunluğu</li>
                                    <li>İnternet ve telekomünikasyon altyapısı</li>
                                    <li>Havalandırma ve klima sistemleri</li>
                                    <li>Yang söndürme ve güvenlik sistemleri</li>
                                    <li>Engelli erişim uygunluğu</li>
                                    <li>Otopark kapasitesi</li>
                                </ul>
                                
                                <h5>5. İmar Durumu ve Yasal Uygunluk</h5>
                                <p>Kiraladığınız alanın imar durumu ve işletmeniz için gerekli ruhsatları alabilme imkanı mutlaka kontrol edilmelidir. Bu konuda yaşanacak sorunlar, işletmenizi ciddi zorluklara sokabilir.</p>
                                
                                <h5>6. Pazarlama ve Görünürlük Faktörleri</h5>
                                <p>İşletmenizin başarısı için kritik olan pazarlama imkanlarını önceden değerlendirin. Tabela hakları, vitrin düzenlemesi ve reklam alanı kullanımı gibi konular sözleşmede net bir şekilde belirtilmelidir.</p>
                                
                                <h5>7. Exit Stratejisi ve Esneklik</h5>
                                <p>Her ne kadar uzun vadeli düşünseniz de, işletme planlarınızın değişebileceğini göz önünde bulundurarak esnek bir exit stratejisi oluşturun. Alt kiralama hakları, erken fesih koşulları ve devir imkanları bu açıdan önemlidir.</p>
                                
                                <p>Sonuç olarak, ticari gayrimenkul kiralama süreci detaylı bir araştırma ve planlama gerektirir. 17 yıllık deneyimim boyunca gördüğüm en büyük hata, aceleci kararlar almak ve detayları gözden kaçırmaktır. Doğru yaklaşımla, kiralayacağınız ticari alan işletmenizin büyümesine büyük katkı sağlayacaktır.</p>
                                
                                <p>Bu konularda profesyonel destek almak istiyorsanız, <a href="contact.html">benimle iletişime geçebilirsiniz</a>. Ticari gayrimenkul alanındaki deneyimimi sizlerle paylaşmaktan mutluluk duyarım.</p>
                            </div>
                            <div class="bottom-widget d-sm-flex align-items-center justify-content-between">
                                <ul class="d-flex align-items-center tags style-none pt-20">
                                    <li>Etiketler:</li>
                                    <li><a href="#">Ticari Kiralama,</a></li>
                                    <li><a href="#">Ofis,</a></li>
                                    <li><a href="#">Dükkan,</a></li>
                                    <li><a href="#">İş Yeri</a></li>
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
                        <!-- /.blog-meta-three -->
                        <div class="blog-comment-area">
                            <h3 class="blog-inner-title pb-35">3 Yorum</h3>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_01.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-body">
                                    <div class="meta-data d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center">
                                            <h6 class="user-name me-2"><a href="#">Ahmet Yılmaz</a></h6>
                                            <span class="comment-date">15 OCA 2023</span>
                                        </div>
                                        <div class="rating">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star-half-alt"></i>
                                        </div>
                                    </div>
                                    <p class="mb-0">Gerçekten çok bilgilendirici bir yazı olmuş, elinize sağlık. Ticari kiralama süreci hakkında aklımda birçok soru işareti vardı, şimdi daha net bir fikrim var.</p>
                                </div>
                            </div>
                            <!-- /.comment -->
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_02.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-body">
                                    <div class="meta-data d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center">
                                            <h6 class="user-name me-2"><a href="#">Ayşe Demir</a></h6>
                                            <span class="comment-date">14 OCA 2023</span>
                                        </div>
                                        <div class="rating">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star-half-alt"></i>
                                            <i class="fa-solid fa-star-empty"></i>
                                        </div>
                                    </div>
                                    <p class="mb-0">Yazınız için teşekkürler. Lokasyon analizi kısmı benim için çok faydalı oldu. Gerçekten de doğru lokasyon seçimi çok önemli.</p>
                                </div>
                            </div>
                            <!-- /.comment -->
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_03.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-body">
                                    <div class="meta-data d-flex align-items-center justify-content-between flex-wrap">
                                        <div class="d-flex align-items-center">
                                            <h6 class="user-name me-2"><a href="#">Mehmet Özcan</a></h6>
                                            <span class="comment-date">13 OCA 2023</span>
                                        </div>
                                        <div class="rating">
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                            <i class="fa-solid fa-star"></i>
                                        </div>
                                    </div>
                                    <p class="mb-0">Mükemmel bir yazı, teşekkürler. Ticari kiralama sürecinde dikkat edilmesi gerekenler konusunda çok fazla bilgi edindim.</p>
                                </div>
                            </div>
                            <!-- /.comment -->
                            <div class="comment-form-area mt-50">
                                <h3 class="blog-inner-title pb-35">Bir Yorum Yazın</h3>
                                <form action="#">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>Adınız</label>
                                                <input type="text" class="form-control" placeholder="Adınızı girin" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label>E-posta</label>
                                                <input type="email" class="form-control" placeholder="E-posta adresinizi girin" required>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Web Sitesi (isteğe bağlı)</label>
                                                <input type="url" class="form-control" placeholder="Web sitenizi girin">
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <div class="form-group">
                                                <label>Mesajınız</label>
                                                <textarea class="form-control" rows="5" placeholder="Yorumunuzu buraya yazın" required></textarea>
                                            </div>
                                        </div>
                                        <div class="col-md-12">
                                            <button type="submit" class="btn-ten rounded-0"><span>Yorumu Gönder</span> <i class="bi bi-arrow-right"></i></button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <!-- /.comment-form-area -->
                        </div>
                        <!-- /.blog-comment-area -->
                    </div>
                    <!-- /.col-lg-8 -->
                    <div class="col-lg-4">
                        <div class="sidebar-widget">
                            <div class="widget search-widget mb-40">
                                <h5 class="widget-title">Blogda Ara</h5>
                                <form action="#">
                                    <div class="position-relative">
                                        <input type="text" class="form-control" placeholder="Anahtar kelime girin">
                                        <button type="submit" class="btn-search position-absolute"><i class="bi bi-search"></i></button>
                                    </div>
                                </form>
                            </div>
                            <!-- /.search-widget -->
                            <div class="widget category-widget mb-40">
                                <h5 class="widget-title">Kategoriler</h5>
                                <ul class="style-none">
                                    <li><a href="#">Ticari Kiralama</a></li>
                                    <li><a href="#">Ofis Kiralama</a></li>
                                    <li><a href="#">Dükkan Kiralama</a></li>
                                    <li><a href="#">İş Yeri Kiralama</a></li>
                                    <li><a href="#">Yatırım Tavsiyeleri</a></li>
                                </ul>
                            </div>
                            <!-- /.category-widget -->
                            <div class="widget recent-posts-widget mb-40">
                                <h5 class="widget-title">Son Yazılar</h5>
                                <ul class="style-none">
                                    <li>
                                        <div class="post-img">
                                            <a href="blog_details.html" class="d-block" style="background-image: url(https://images.unsplash.com/photo-1506748686214-e9df14d4d9d2?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80);"></a>
                                        </div>
                                        <div class="post-info">
                                            <h6 class="post-title"><a href="blog_details.html">Ticari Kiralama Sürecinde Dikkat Edilmesi Gereken 5 Önemli Nokta</a></h6>
                                            <span class="post-date">12 OCA 2023</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="post-img">
                                            <a href="blog_details.html" class="d-block" style="background-image: url(https://images.unsplash.com/photo-1517245386807-bb43f82c2b8d?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80);"></a>
                                        </div>
                                        <div class="post-info">
                                            <h6 class="post-title"><a href="blog_details.html">Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler</a></h6>
                                            <span class="post-date">10 OCA 2023</span>
                                        </div>
                                    </li>
                                    <li>
                                        <div class="post-img">
                                            <a href="blog_details.html" class="d-block" style="background-image: url(https://images.unsplash.com/photo-1521747116042-5a810fda9664?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2069&q=80);"></a>
                                        </div>
                                        <div class="post-info">
                                            <h6 class="post-title"><a href="blog_details.html">Dükkan Kiralama Sürecinde Yapılan Yaygın Hatalar</a></h6>
                                            <span class="post-date">8 OCA 2023</span>
                                        </div>
                                    </li>
                                </ul>
                            </div>
                            <!-- /.recent-posts-widget -->
                            <div class="widget tags-widget mb-40">
                                <h5 class="widget-title">Etiketler</h5>
                                <div class="tags-list d-flex flex-wrap">
                                    <a href="#" class="tag-item">Ticari Kiralama</a>
                                    <a href="#" class="tag-item">Ofis Kiralama</a>
                                    <a href="#" class="tag-item">Dükkan Kiralama</a>
                                    <a href="#" class="tag-item">İş Yeri</a>
                                    <a href="#" class="tag-item">Yatırım</a>
                                </div>
                            </div>
                            <!-- /.tags-widget -->
                        </div>
                        <!-- /.sidebar-widget -->
                    </div>
                    <!-- /.col-lg-4 -->
                </div>
            </div>
        </div>
		<!-- /.blog-details -->


		<!--
		=====================================================
			Footer
		=====================================================
		-->
		<footer class="footer-widget-area bg-light pt-100 pb-70">
			<div class="container">
				<div class="row gx-4">
					<div class="col-lg-4 col-md-6">
						<div class="footer-widget mb-40">
							<h5 class="widget-title">Hakkımızda</h5>
							<p>Gökhan Aydınlı Gayrimenkul, ticari kiralama alanında uzmanlaşmış bir danışmanlık firmasıdır. 17 yıllık deneyimimizle, müşterilerimize en iyi hizmeti sunmayı hedefliyoruz.</p>
							<a href="about_us_01.html" class="btn-two rounded-0 mt-3"><span>Daha Fazla Bilgi Edinin</span> <i class="bi bi-arrow-right"></i></a>
						</div>
					</div>
					<div class="col-lg-4 col-md-6">
						<div class="footer-widget mb-40">
							<h5 class="widget-title">İletişim Bilgileri</h5>
							<ul class="list-unstyled footer-contact-info">
								<li><i class="bi bi-geo-alt"></i>Adres: Örnek Mah. No:123, Beşiktaş, İstanbul</li>
								<li><i class="bi bi-telephone"></i>Telefon: +90 123 456 78 90</li>
								<li><i class="bi bi-envelope"></i>E-posta: info@gokhanaydnli.com</li>
							</ul>
						</div>
					</div>
					<div class="col-lg-4 col-md-6">
						<div class="footer-widget mb-40">
							<h5 class="widget-title">Hızlı Bağlantılar</h5>
							<ul class="list-unstyled footer-quick-links">
								<li><a href="index.html">Ana Sayfa</a></li>
								<li><a href="about_us_01.html">Hakkımızda</a></li>
								<li><a href="services.html">Hizmetler</a></li>
								<li><a href="pricing_01.html">Fiyatlar</a></li>
								<li><a href="contact.html">İletişim</a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="row">
					<div class="col-lg-12">
						<div class="footer-bottom d-flex flex-wrap align-items-center justify-content-between">
							<div class="copyright-text">
								<p>&copy; 2023 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
							</div>
							<div class="footer-menu">
								<ul class="d-flex align-items-center style-none">
									<li><a href="#">Gizlilik Politikası</a></li>
									<li><a href="#">Kullanım Şartları</a></li>
									<li><a href="#">Çerez Politikası</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
			</div>
		</footer>
		<!-- /.footer-widget-area -->


		<!--
		=====================================================
			Search Modal
		=====================================================
		-->
		<div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-fullscreen modal-dialog-centered">
				<div class="modal-content d-flex justify-content-center">
					<form action="#">
						<input type="text" placeholder="Blog yazıları ara...">
						<button><i class="fa-light fa-arrow-right-long"></i></button>
					</form>
				</div>
			</div>
		</div>
		<!-- /.modal -->

		<!--
		=====================================================
			Login Modal
		=====================================================
		-->
		<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
			<div class="modal-dialog modal-dialog-centered">
				<div class="modal-content">
					<div class="modal-header">
						<h5 class="modal-title">Giriş Yap</h5>
						<button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
					</div>
					<div class="modal-body">
						<form action="#">
							<div class="mb-3">
								<label for="email" class="form-label">E-posta adresi</label>
								<input type="email" class="form-control" id="email" placeholder="E-posta adresinizi girin" required>
							</div>
							<div class="mb-3">
								<label for="password" class="form-label">Şifre</label>
								<input type="password" class="form-control" id="password" placeholder="Şifrenizi girin" required>
							</div>
							<div class="mb-3 form-check">
								<input type="checkbox" class="form-check-input" id="rememberMe">
								<label class="form-check-label" for="rememberMe">Beni hatırla</label>
							</div>
							<button type="submit" class="btn-ten w-100 rounded-0"><span>Giriş Yap</span> <i class="bi bi-arrow-right"></i></button>
						</form>
					</div>
					<div class="modal-footer">
						<button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
						<a href="forgot_password.html" class="btn btn-primary">Şifremi Unuttum</a>
					</div>
				</div>
			</div>
		</div>
		<!-- /.modal -->


		<!--
		=====================================================
			Back to Top
		=====================================================
		-->
		<a href="#" class="back-to-top tran3s d-flex align-items-center justify-content-center">
			<i class="fa-solid fa-arrow-up"></i>
		</a>
		<!-- /.back-to-top -->

		<!--
		=====================================================
			All Script
		=====================================================
		-->
		<script src="js/jquery.min.js"></script>
		<script src="js/bootstrap.bundle.min.js"></script>
		<script src="js/owl.carousel.min.js"></script>
		<script src="js/jquery.magnific-popup.min.js"></script>
		<script src="js/scrollax.min.js"></script>
		<script src="js/main.js"></script>
		<!--
		=====================================================
			End All Script
		=====================================================
		-->
	</div>
	<!-- /.main-page-wrapper -->
</body>

</html>