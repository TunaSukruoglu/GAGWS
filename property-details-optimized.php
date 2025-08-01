<?php
// PROPERTY DETAILS PERFORMANCE OPTIMIZER
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

$start_time = microtime(true);

try {
    include 'db.php';
} catch (Exception $e) {
    die("DB Hatası: " . $e->getMessage());
}

$db_connect_time = microtime(true) - $start_time;

$property_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$property_id) {
    header('Location: portfoy.php');
    exit;
}

try {
    // OPTIMIZED: Single query with user data
    $query_start = microtime(true);
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email 
                            FROM properties p 
                            LEFT JOIN users u ON p.user_id = u.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    $query_time = microtime(true) - $query_start;
    
    if (!$property) {
        header('Location: portfoy.php');
        exit;
    }
    
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// OPTIMIZED: Fast image processing
$images = [];
$image_process_start = microtime(true);

if (!empty($property['images'])) {
    // Try JSON decode first
    $decoded = json_decode($property['images'], true);
    if (is_array($decoded) && !empty($decoded)) {
        $images = $decoded;
    } else {
        // Fallback to comma split
        $images = explode(',', $property['images']);
    }
    
    // Quick cleanup and path correction
    $corrected_images = [];
    foreach ($images as $image) {
        if (!empty(trim($image))) {
            $filename = basename(trim($image));
            // Simplified path - no time parameter for better caching
            $corrected_images[] = "smart-image.php?img=" . urlencode($filename);
        }
    }
    $images = $corrected_images;
}

// Default image if none found
if (empty($images)) {
    $images = ['smart-image.php?img=default.jpg'];
}

$image_process_time = microtime(true) - $image_process_start;

// OPTIMIZED: Fast features processing
$features_start = microtime(true);
$interior_features = [];
$exterior_features = [];
$neighborhood_features = [];
$transportation_features = [];
$view_features = [];
$housing_type_features = [];
$facilities = [];

if (!empty($property['features'])) {
    $features_data = json_decode($property['features'], true);
    if (is_array($features_data)) {
        $interior_features = $features_data['ic_ozellikler'] ?? [];
        $exterior_features = $features_data['dis_ozellikler'] ?? [];
        $neighborhood_features = $features_data['muhit_ozellikleri'] ?? [];
        $transportation_features = $features_data['ulasim_ozellikleri'] ?? [];
        $view_features = $features_data['manzara_ozellikleri'] ?? [];
        $housing_type_features = $features_data['konut_tipi_ozellikleri'] ?? [];
        $facilities = $features_data['olanaklar'] ?? [];
    }
}
$features_time = microtime(true) - $features_start;

// OPTIMIZED: Simple price format function
function formatPrice($price) {
    if (empty($price) || $price == 0) return 'Fiyat Belirtilmemiş';
    if ($price >= 1000000) return number_format($price / 1000000, 1) . ' Milyon ₺';
    if ($price >= 1000) return number_format($price / 1000, 0) . '.000 ₺';
    return number_format($price, 0) . ' ₺';
}

$total_php_time = microtime(true) - $start_time;
?>
<!DOCTYPE html>
<html lang="tr">
<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="Real estate, Property sale, Property buy, emlak, gayrimenkul">
	<meta name="description" content="Gökhan Aydınlı Gayrimenkul - Profesyonel emlak hizmetleri">
	<meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
	<meta property="og:type" content="website">
	<meta property="og:title" content="<?= htmlspecialchars($property['title'] ?? 'Emlak Detayı') ?> - Gökhan Aydınlı Gayrimenkul">
	<meta name='og:image' content='images/GA.jpg'>
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<meta name="theme-color" content="#0D1A1C">

	<title><?= htmlspecialchars($property['title'] ?? 'Emlak Detayı') ?> - Gökhan Aydınlı Gayrimenkul</title>

	<!-- OPTIMIZED CSS - Critical CSS First -->
	<link rel="shortcut icon" href="images/favicon.png" type="image/x-icon">
	<link rel="icon" href="images/favicon.png" type="image/x-icon">
	
	<!-- Critical CSS -->
	<style>
		.loader { position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: #fff; z-index: 9999; display: flex; align-items: center; justify-content: center; }
		.loader-spinner { width: 50px; height: 50px; border: 3px solid #f3f3f3; border-top: 3px solid #007bff; border-radius: 50%; animation: spin 1s linear infinite; }
		@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
		body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
		.main-page-wrapper { min-height: 100vh; }
	</style>

	<!-- NON-CRITICAL CSS - Load Async -->
	<link rel="preload" href="css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/menu.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/responsive.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	
	<noscript>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/menu.css">
		<link rel="stylesheet" href="css/style.css">
		<link rel="stylesheet" href="css/responsive.css">
	</noscript>

	<!-- Performance Debug Info -->
	<!-- 
	DB Connect: <?= number_format($db_connect_time * 1000, 2) ?>ms
	Query Time: <?= number_format($query_time * 1000, 2) ?>ms
	Image Process: <?= number_format($image_process_time * 1000, 2) ?>ms
	Features Process: <?= number_format($features_time * 1000, 2) ?>ms
	Total PHP: <?= number_format($total_php_time * 1000, 2) ?>ms
	Image Count: <?= count($images) ?>
	-->

	<?php if (isset($_SESSION['error'])): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			showAlert('<?= addslashes($_SESSION['error']) ?>', 'error');
		});
	</script>
	<?php unset($_SESSION['error']); endif; ?>
</head>

<body>
	<!-- OPTIMIZED LOADER -->
	<div class="loader" id="preloader">
		<div class="loader-spinner"></div>
	</div>

	<div class="main-page-wrapper">
		<!-- Header -->
		<header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
			<div class="inner-content gap-one">
				<div class="top-header position-relative">
					<div class="d-flex align-items-center justify-content-between">
						<div class="logo order-lg-0">
							<a href="index.php" class="d-flex align-items-center">
								<img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" loading="lazy">
							</a>
						</div>
						<nav class="navbar navbar-expand-lg p0 order-lg-2">
							<button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
								data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
								aria-label="Toggle navigation">
								<span></span>
							</button>
							<div class="collapse navbar-collapse" id="navbarNav">
								<ul class="navbar-nav align-items-lg-center">
									<li class="nav-item"><a class="nav-link" href="index.php">Ana Sayfa</a></li>
									<li class="nav-item"><a class="nav-link" href="hakkimizda.php">Hakkımızda</a></li>
									<li class="nav-item"><a class="nav-link" href="portfoy.php">Portföy</a></li>
									<li class="nav-item"><a class="nav-link" href="blog.php">Blog</a></li>
									<li class="nav-item"><a class="nav-link" href="contact.php">İletişim</a></li>
									<li class="nav-item"><a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a></li>
								</ul>
							</div>
						</nav>
					</div>
				</div>
			</div>
		</header>

		<!-- Property Details Content -->
		<div class="container mt-5 pt-5">
			<div class="row">
				<div class="col-lg-8">
					<h1><?= htmlspecialchars($property['title'] ?? 'Emlak Detayı') ?></h1>
					<p class="text-primary fs-3 fw-bold"><?= formatPrice($property['price']) ?></p>
					
					<!-- OPTIMIZED Image Gallery -->
					<div class="property-images mb-4">
						<?php if (!empty($images)): ?>
							<div class="main-image">
								<img src="<?= htmlspecialchars($images[0]) ?>" alt="Property Image" 
								     class="img-fluid rounded" loading="lazy">
							</div>
							<?php if (count($images) > 1): ?>
								<div class="image-thumbnails mt-3">
									<div class="row g-2">
										<?php for ($i = 1; $i < min(count($images), 6); $i++): ?>
											<div class="col-2">
												<img src="<?= htmlspecialchars($images[$i]) ?>" alt="Thumbnail" 
												     class="img-fluid rounded" loading="lazy">
											</div>
										<?php endfor; ?>
									</div>
								</div>
							<?php endif; ?>
						<?php endif; ?>
					</div>

					<!-- Property Description -->
					<div class="property-description mb-4">
						<h3>Açıklama</h3>
						<p><?= nl2br(htmlspecialchars($property['description'] ?? 'Açıklama bulunmamaktadır.')) ?></p>
					</div>

					<!-- Property Details -->
					<div class="property-details">
						<h3>İlan Detayları</h3>
						<div class="row">
							<div class="col-md-6">
								<table class="table table-striped">
									<tr><td><strong>İl:</strong></td><td><?= htmlspecialchars($property['city'] ?? '-') ?></td></tr>
									<tr><td><strong>İlçe:</strong></td><td><?= htmlspecialchars($property['district'] ?? '-') ?></td></tr>
									<tr><td><strong>Mahalle:</strong></td><td><?= htmlspecialchars($property['neighborhood'] ?? '-') ?></td></tr>
									<tr><td><strong>Oda Sayısı:</strong></td><td><?= htmlspecialchars($property['room_count'] ?? '-') ?></td></tr>
									<tr><td><strong>Yatak Odası:</strong></td><td><?= htmlspecialchars($property['bedrooms'] ?? '-') ?></td></tr>
									<tr><td><strong>Salon:</strong></td><td><?= htmlspecialchars($property['living_room_count'] ?? '-') ?></td></tr>
								</table>
							</div>
							<div class="col-md-6">
								<table class="table table-striped">
									<tr><td><strong>Brüt m²:</strong></td><td><?= htmlspecialchars($property['area_gross'] ?? '-') ?></td></tr>
									<tr><td><strong>Net m²:</strong></td><td><?= htmlspecialchars($property['area_net'] ?? '-') ?></td></tr>
									<tr><td><strong>Kat:</strong></td><td><?= htmlspecialchars($property['floor'] ?? '-') ?></td></tr>
									<tr><td><strong>Bina Yaşı:</strong></td><td><?= htmlspecialchars($property['building_age'] ?? '-') ?></td></tr>
									<tr><td><strong>Otopark:</strong></td><td><?= htmlspecialchars($property['parking'] ?? '-') ?></td></tr>
									<tr><td><strong>Isıtma:</strong></td><td><?= htmlspecialchars($property['heating'] ?? '-') ?></td></tr>
								</table>
							</div>
						</div>
					</div>
				</div>

				<!-- Contact Sidebar -->
				<div class="col-lg-4">
					<div class="contact-card sticky-top">
						<div class="card">
							<div class="card-body">
								<h4>İletişim</h4>
								<p><strong>Gökhan Aydınlı</strong></p>
								<p>📞 <a href="tel:+905555555555">0555 555 55 55</a></p>
								<p>📧 <a href="mailto:info@gokhanaydlini.com">info@gokhanaydlini.com</a></p>
								<a href="tel:+905555555555" class="btn btn-primary w-100 mb-2">Ara</a>
								<a href="https://wa.me/905555555555" class="btn btn-success w-100">WhatsApp</a>
							</div>
						</div>
					</div>
				</div>
			</div>
		</div>

		<!-- Footer -->
		<footer class="bottom-footer theme-basic-footer position-relative">
			<div class="container">
				<div class="bottom-footer-content text-center">
					<p>&copy; 2025 Gökhan Aydınlı Gayrimenkul. Tüm hakları saklıdır.</p>
				</div>
			</div>
		</footer>
	</div>

	<!-- OPTIMIZED JAVASCRIPT - Load at End -->
	<script>
		// Fast loader hide
		window.addEventListener('load', function() {
			const preloader = document.getElementById('preloader');
			if (preloader) {
				preloader.style.display = 'none';
			}
		});

		// Hide loader after max 3 seconds even if page isn't fully loaded
		setTimeout(function() {
			const preloader = document.getElementById('preloader');
			if (preloader) {
				preloader.style.display = 'none';
			}
		}, 3000);
	</script>

	<!-- Load non-critical JS async -->
	<script src="js/bootstrap.bundle.min.js" defer></script>
</body>
</html>
