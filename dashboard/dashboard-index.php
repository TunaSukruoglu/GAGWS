<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php?login=required");
    exit;
}
?>

<!DOCTYPE html>
<html lang="en">

<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="Real estate, Property sale, Property buy">
	<meta name="description" content="Homy is a beautiful website template designed for Real Estate Agency.">
    <meta property="og:site_name" content="Homy">
    <meta property="og:url" content="https://creativegigstf.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Homy-Real Estate HTML5 Template & Dashboard">
	<meta name='og:image' content='../images/assets/ogg.png'>
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
	<title>Homy-Real Estate HTML5 Template & Dashboard</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56" href="../images/fav-icon/icon.png">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css" media="all">
	<!-- Main style sheet -->
	<link rel="stylesheet" type="text/css" href="../css/style.min.css" media="all">
	<!-- responsive style sheet -->
	<link rel="stylesheet" type="text/css" href="../css/responsive.css" media="all">

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
				<div class="icon"><img src="../images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
			</div>
		</div>

		<!-- 
		=============================================
			Dashboard Aside Menu
		============================================== 
		-->
		<aside class="dash-aside-navbar">
			<div class="position-relative">
				<div class="logo d-md-block d-flex align-items-center justify-content-between plr bottom-line pb-30">
    <a href="dashboard-index.php">
        <img src="../images/logoSiyah.png" alt="Logo" style="height:80px; max-width:220px;">
    </a>
    <button class="close-btn d-block d-md-none"><i class="fa-light fa-circle-xmark"></i></button>
</div>
				<nav class="dasboard-main-nav pt-30 pb-30 bottom-line">
					<ul class="style-none">
						<li class="plr"><a href="#" class="d-flex w-100 align-items-center active">
							<img src="images/icon/icon_1_active.svg" alt="">
							<span>Panel</span>
						</a></li>
						<li class="plr"><a href="message.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_2.svg" alt="">
							<span>Mesajlar</span>
						</a></li>
						<li class="bottom-line pt-30 lg-pt-20 mb-40 lg-mb-30"></li>
						<li><div class="nav-title">Profil</div></li>
						<li class="plr"><a href="profile.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_3.svg" alt="">
							<span>Profil</span>
						</a></li>
						<li class="plr"><a href="account-settings.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_4.svg" alt="">
							<span>Hesap Ayarları</span>
						</a></li>
						<li class="plr"><a href="membership.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_5.svg" alt="">
							<span>Üyelik</span>
						</a></li>
						<li class="bottom-line pt-30 lg-pt-20 mb-40 lg-mb-30"></li>
						<li><div class="nav-title">İlanlar</div></li>
						<li class="plr"><a href="properties-list.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_6.svg" alt="">
							<span>İlanlarım</span>
						</a></li>
						<li class="plr"><a href="add-property.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_7.svg" alt="">
							<span>Yeni İlan Ekle</span>
						</a></li>
						<li class="plr"><a href="favourites.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_8.svg" alt="">
							<span>Favoriler</span>
						</a></li>
						<li class="plr"><a href="saved-search.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_9.svg" alt="">
							<span>Kayıtlı Aramalar</span>
						</a></li>
						<li class="plr"><a href="review.php" class="d-flex w-100 align-items-center">
							<img src="images/icon/icon_10.svg" alt="">
							<span>Yorumlar</span>
						</a></li>
					</ul>
				</nav>
				<!-- /.dasboard-main-nav -->
				<div class="profile-complete-status bottom-line pb-35 plr">
					<div class="progress-value fw-500">82%</div>
					<div class="progress-line position-relative">
						<div class="inner-line" style="width:80%;"></div>
					</div>
					<p>Profil Tamamlandı</p>
				</div>
				<!-- /.profile-complete-status -->

				<div class="plr">
					<a href="../logout.php" class="d-flex w-100 align-items-center logout-btn">
						<div class="icon tran3s d-flex align-items-center justify-content-center rounded-circle">
							<img src="images/icon/icon_41.svg" alt="">
						</div>
						<span>Çıkış Yap</span>
					</a>
				</div>
			</div>
		</aside>
		<!-- /.dash-aside-navbar -->

		<!-- 
		=============================================
			Dashboard Body
		============================================== 
		-->
		<div class="dashboard-body">
			<div class="position-relative">
				<!-- ************************ Header **************************** -->
				<header class="dashboard-header">
					<div class="d-flex align-items-center justify-content-end">
						<h4 class="m0 d-none d-lg-block">Panel</h4>
						<button class="dash-mobile-nav-toggler d-block d-md-none me-auto">
							<span></span>
						</button>
						<form action="#" class="search-form ms-auto">
							<input type="text" placeholder="Search here..">
							<button><img src="../images/lazy.svg" data-src="images/icon/icon_43.svg" alt="" class="lazy-img m-auto"></button>
						</form>
						<div class="profile-notification position-relative dropdown-center ms-3 ms-md-5 me-4">
							<button class="noti-btn dropdown-toggle" type="button" id="notification-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
								<img src="../images/lazy.svg" data-src="images/icon/icon_11.svg" alt="" class="lazy-img">
								<div class="badge-pill"></div>
							</button>
							<ul class="dropdown-menu" aria-labelledby="notification-dropdown">
								<li>
									<h4>Notification</h4>
									<ul class="style-none notify-list">
										<li class="d-flex align-items-center unread">
											<img src="../images/lazy.svg" data-src="images/icon/icon_36.svg" alt="" class="lazy-img icon">
											<div class="flex-fill ps-2">
												<h6>You have 3 new mails</h6>
												<span class="time">3 hours ago</span>
											</div>
										</li>
										<li class="d-flex align-items-center">
											<img src="../images/lazy.svg" data-src="images/icon/icon_37.svg" alt="" class="lazy-img icon">
											<div class="flex-fill ps-2">
												<h6>Your listing post has been approved</h6>
												<span class="time">1 day ago</span>
											</div>
										</li>
										<li class="d-flex align-items-center unread">
											<img src="../images/lazy.svg" data-src="images/icon/icon_38.svg" alt="" class="lazy-img icon">
											<div class="flex-fill ps-2">
												<h6>Your meeting is cancelled</h6>
												<span class="time">3 days ago</span>
											</div>
										</li>
									</ul>
								</li>
							</ul>
						</div>
						<div class="d-none d-md-block me-3">
							<a href="add-property.php" class="btn-two"><span>Add Listing</span> <i class="fa-thin fa-arrow-up-right"></i></a>
						</div>
						<div class="user-data position-relative">
							<button class="user-avatar online position-relative rounded-circle dropdown-toggle" type="button" id="profile-dropdown" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false">
								<img src="../images/lazy.svg" data-src="images/avatar_01.jpg" alt="" class="lazy-img">
							</button>
							<!-- /.user-avatar -->
							<div class="user-name-data">
								<ul class="dropdown-menu" aria-labelledby="profile-dropdown">
									<li>
										<a class="dropdown-item d-flex align-items-center" href="profile.php"><img src="../images/lazy.svg" data-src="images/icon/icon_23.svg" alt="" class="lazy-img"><span class="ms-2 ps-1">Profile</span></a>
									</li>
									<li>
										<a class="dropdown-item d-flex align-items-center" href="account-settings.php"><img src="../images/lazy.svg" data-src="images/icon/icon_24.svg" alt="" class="lazy-img"><span class="ms-2 ps-1">Account Settings</span></a>
									</li>
									<li>
										<a class="dropdown-item d-flex align-items-center" href="#" data-bs-toggle="modal" data-bs-target="#deleteModal"><img src="../images/lazy.svg" data-src="images/icon/icon_25.svg" alt="" class="lazy-img"><span class="ms-2 ps-1">Delete Account</span></a>
									</li>
								</ul>
							</div>
						</div>
						<!-- /.user-data -->
					</div>
				</header>
				<!-- End Header -->

				<h4 class="m0 d-none d-lg-block">Panel</h4>
				<h2 class="main-title d-block d-lg-none">Panel</h2>
				<div class="bg-white border-20">
					<div class="row">
						<div class="col-lg-3 col-6">
							<div class="dash-card-one bg-white border-30 position-relative mb-15 skew-none">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="icon rounded-circle d-flex align-items-center justify-content-center order-sm-1"><img src="../images/lazy.svg" data-src="images/icon/icon_12.svg" alt="" class="lazy-img"></div>
									<div class="order-sm-0">
										<span>Tüm İlanlar</span>
										<div class="value fw-500">1.7k+</div>
									</div>
								</div>
							</div>
							<!-- /.dash-card-one -->
						</div>
						<div class="col-lg-3 col-6">
							<div class="dash-card-one bg-white border-30 position-relative mb-15">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="icon rounded-circle d-flex align-items-center justify-content-center order-sm-1"><img src="../images/lazy.svg" data-src="images/icon/icon_13.svg" alt="" class="lazy-img"></div>
									<div class="order-sm-0">
										<span>Bekleyenler</span>
										<div class="value fw-500">03</div>
									</div>
								</div>
							</div>
							<!-- /.dash-card-one -->
						</div>
						<div class="col-lg-3 col-6">
							<div class="dash-card-one bg-white border-30 position-relative mb-15">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="icon rounded-circle d-flex align-items-center justify-content-center order-sm-1"><img src="../images/lazy.svg" data-src="images/icon/icon_14.svg" alt="" class="lazy-img"></div>
									<div class="order-sm-0">
										<span>Toplam Görüntülenme</span>
										<div class="value fw-500">4.8k</div>
									</div>
								</div>
							</div>
							<!-- /.dash-card-one -->
						</div>
						<div class="col-lg-3 col-6">
							<div class="dash-card-one bg-white border-30 position-relative mb-15">
								<div class="d-sm-flex align-items-center justify-content-between">
									<div class="icon rounded-circle d-flex align-items-center justify-content-center order-sm-1"><img src="../images/lazy.svg" data-src="images/icon/icon_15.svg" alt="" class="lazy-img"></div>
									<div class="order-sm-0">
										<span>Toplam Favoriler</span>
										<div class="value fw-500">07</div>
									</div>
								</div>
							</div>
							<!-- /.dash-card-one -->
						</div>
					</div>
				</div>

				<div class="row gx-xxl-5 d-flex pt-15 lg-pt-10">
					<div class="col-xl-7 col-lg-6 d-flex flex-column">
						<div class="user-activity-chart bg-white border-20 mt-30 h-100">
							<div class="d-flex align-items-center justify-content-between plr">
								<h5 class="dash-title-two">İlan Görüntülenme</h5>
								<div class="short-filter d-flex align-items-center">
									<div class="fs-16 me-2">Sırala:</div>
									<select class="nice-select fw-normal">
										<option value="0">Haftalık</option>
										<option value="1">Günlük</option>
										<option value="2">Aylık</option>
									</select>
								</div>
							</div>
							<div class="plr mt-50">
								<div class="chart-wrapper">
									<canvas id="property-chart"></canvas>
								</div>
							</div>
						</div>
					</div>
					<div class="col-xl-5 col-lg-6 d-flex">
						<div class="recent-job-tab bg-white border-20 mt-30 plr w-100">
							<h5 class="dash-title-two">Son Mesajlar</h5>
							<div class="message-wrapper">
								<div class="message-sidebar border-0">
									<div class="email-read-panel">
										<div class="email-list-item read border-0 pt-0">
											<div class="email-short-preview position-relative">
												<div class="d-flex align-items-center justify-content-between">
													<div class="sender-name">Jenny Rio.</div>
													<div class="date">22 Ağustos</div>
												</div>
												<div class="mail-sub">Google'dan iş teklifi.</div>
												<div class="mail-text">Merhaba, ben Jenny. Google'ın en büyük online platformuyuz...</div>
												<div class="attached-file-preview d-flex align-items-center mt-15">
													<div class="file d-flex align-items-center me-2">
														<img src="../images/lazy.svg" data-src="images/icon/icon_28.svg" alt="" class="lazy-img me-2">
														<span>details.pdf</span>
													</div>
												</div>
												<!-- /.attached-file-preview -->
											</div>
											<!-- /.email-short-preview -->
										</div>
										<!-- /.email-list-item -->

										<div class="email-list-item primary">
											<div class="email-short-preview position-relative">
												<div class="d-flex align-items-center justify-content-between">
													<div class="sender-name">Hasan Islam.</div>
													<div class="date">22 Mayıs</div>
												</div>
												<div class="mail-sub">Ürün Tasarımcısı Fırsatları</div>
												<div class="mail-text">Merhaba, Uber'den selamlar. Umarım iyisinizdir. Sizi arıyorum...</div>
											</div>
											<!-- /.email-short-preview -->
										</div>
										<!-- /.email-list-item -->

										<div class="email-list-item">
											<div class="email-short-preview position-relative">
												<div class="d-flex align-items-center justify-content-between">
													<div class="sender-name">Jakie Chan</div>
													<div class="date">22 Temmuz</div>
												</div>
												<div class="mail-sub">Pazarlama Uzmanı Aranıyor</div>
												<div class="mail-text">Merhaba, ben Jannat. HuntX olarak müşterilerimize çözüm sunuyoruz...</div>
											</div>
											<!-- /.email-short-preview -->
										</div>
										<!-- /.email-list-item -->
									</div>
									<!-- /.email-read-panel -->
								</div>
								<!-- /.message-sidebar -->
							</div>
							<!-- /.message-wrapper -->
						</div>
					</div>
				</div>
			</div>
		</div>
		<!-- /.dashboard-body -->



		<!-- Modal -->
        <div class="modal fade" id="deleteModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="container">
                    <div class="remove-account-popup text-center modal-content">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<img src="../images/lazy.svg" data-src="images/icon/icon_22.svg" alt="" class="lazy-img m-auto">
						<h2>Are you sure?</h2>
						<p>Are you sure to delete your account? All data will be lost.</p>
						<div class="button-group d-inline-flex justify-content-center align-items-center pt-15">
							<a href="#" class="confirm-btn fw-500 tran3s me-3">Yes</a>
							<button type="button" class="btn-close fw-500 ms-3" data-bs-dismiss="modal" aria-label="Close">Cancel</button>
						</div>
                    </div>
                    <!-- /.remove-account-popup -->
                </div>
            </div>
        </div>
		


		<button class="scroll-top">
			<i class="bi bi-arrow-up-short"></i>
		</button>




		<!-- Optional JavaScript _____________________________  -->

		<!-- jQuery first, then Bootstrap JS -->
		<!-- jQuery -->
		<script src="../vendor/jquery.min.js"></script>
		<!-- Bootstrap JS -->
		<script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
		<!-- WOW js -->
		<script src="../vendor/wow/wow.min.js"></script>
		<!-- Slick Slider -->
		<script src="../vendor/slick/slick.min.js"></script>
		<!-- Fancybox -->
		<script src="../vendor/fancybox/fancybox.umd.js"></script>
		<!-- Lazy -->
		<script src="../vendor/jquery.lazy.min.js"></script>
		<!-- js Counter -->
		<script src="../vendor/jquery.counterup.min.js"></script>
		<script src="../vendor/jquery.waypoints.min.js"></script>
		<!-- Nice Select -->
		<script src="../vendor/nice-select/jquery.nice-select.min.js"></script>
		<!-- validator js -->
		<script src="../vendor/validator.js"></script>
		<!-- Chart js -->
		<script src="../vendor/chart.js"></script>

		<!-- Theme js -->
		<script src="../js/theme.js"></script>
	</div> <!-- /.main-page-wrapper -->
</body>

</html>