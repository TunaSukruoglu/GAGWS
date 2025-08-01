<?php
session_start();
include '../db.php';
if (!isset($_SESSION['user_id']) || ($_SESSION['role'] ?? '') !== 'admin') {
    header("Location: ../index.php?login=required");
    exit;
}

// Onay işlemi
if (isset($_GET['approve'])) {
    $uid = intval($_GET['approve']);
    $conn->query("UPDATE users SET is_approved=1 WHERE id=$uid");
    header("Location: onay-bekleyenler.php");
    exit;
}

// Onay bekleyen kullanıcıları çek
$result = $conn->query("SELECT id, name, email FROM users WHERE is_approved=0");
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


        

		<main class="dashboard-body" style="margin-left:260px; padding:40px 20px;">
    <div class="container">
        <h2>Onay Bekleyen Kullanıcılar</h2>
        <table class="table table-bordered">
            <thead>
                <tr>
                    <th>Ad Soyad</th>
                    <th>E-posta</th>
                    <th>İşlem</th>
                </tr>
            </thead>
            <tbody>
            <?php if ($result && $result->num_rows > 0): ?>
                <?php while($row = $result->fetch_assoc()): ?>
                    <tr>
                        <td><?= htmlspecialchars($row['name']) ?></td>
                        <td><?= htmlspecialchars($row['email']) ?></td>
                        <td>
                            <a href="?approve=<?= $row['id'] ?>" class="btn btn-success btn-sm">Onayla</a>
                        </td>
                    </tr>
                <?php endwhile; ?>
            <?php else: ?>
                <tr>
                    <td colspan="3" class="text-center">Onay bekleyen kullanıcı yok.</td>
                </tr>
            <?php endif; ?>
            </tbody>
        </table>
        <a href="dashboard-admin.php" class="btn btn-secondary">Panele Dön</a>
    </div>
</main>
	
        

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