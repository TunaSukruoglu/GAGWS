<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
session_start();

try {
    include 'db.php';
} catch (Exception $e) {
    die("DB Hatası: " . $e->getMessage());
}

$property_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$property_id) {
    header('Location: portfoy.php');
    exit;
}

try {
    // Property bilgileri ile birlikte kullanıcı bilgilerini de çek - CLOUDFLARE IMAGES ADDED
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email 
                            FROM properties p 
                            LEFT JOIN users u ON p.user_id = u.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    
if (!$property) {
    header('Location: porfoy.html');
    exit;
}} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

// OPTIMIZED: Remove debug comments to reduce HTML size
// Debug için tüm property değerlerini görelim - REMOVED FOR PERFORMANCE

// CLOUDFLARE-FIRST: Optimized image processing with Cloudflare Images support
$images = [];

// 1. CLOUDFLARE IMAGES ÖNCELIK (YENİ SISTEM)
if (!empty($property['use_cloudflare']) && !empty($property['cloudflare_images'])) {
    $cloudflare_decoded = json_decode($property['cloudflare_images'], true);
    if (is_array($cloudflare_decoded) && !empty($cloudflare_decoded)) {
        // Cloudflare Images ID'lerini URL'lere çevir
        $account_hash = 'prdw3ANMyocSBJD-Do1EeQ'; // Account Hash for image delivery
        foreach ($cloudflare_decoded as $image_id) {
            if (!empty($image_id)) {
                $images[] = "https://imagedelivery.net/{$account_hash}/{$image_id}/public";
            }
        }
    }
}

// 2. LEGACY IMAGES FALLBACK (ESKİ SISTEM) - JSON içinde full URL varsa
if (empty($images) && !empty($property['images'])) {
    // Try JSON decode first
    $decoded = json_decode($property['images'], true);
    if (is_array($decoded) && !empty($decoded)) {
        $images = $decoded;
    } else {
        // Fallback to comma split
        $images = explode(',', $property['images']);
    }
    
    // Legacy images için uygun URL formatı oluştur
    $corrected_images = [];
    foreach ($images as $image) {
        if (!empty(trim($image))) {
            if (strpos($image, 'https://imagedelivery.net/') === 0) {
                // Already Cloudflare URL
                $corrected_images[] = $image;
            } elseif (strpos($image, '/uploads/properties/') === 0) {
                // New format: /uploads/properties/filename.jpg - use directly
                $corrected_images[] = $image;
            } else {
                // Old format: filename.jpg only - use smart-image.php wrapper
                $filename = basename(trim($image));
                $corrected_images[] = "smart-image.php?img=" . urlencode($filename);
            }
        }
    }
    $images = $corrected_images;
}

// 3. DEFAULT IMAGE
if (empty($images)) {
    $images = ['smart-image.php?img=GA.jpg'];
}

// Özellikler verilerini decode et
$interior_features = [];
$exterior_features = [];
$neighborhood_features = [];
$transportation_features = [];
$view_features = [];
$housing_type_features = [];
$facilities = [];

// OPTIMIZED: Fast features processing - remove debug
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

function formatPrice($price) {
    if (empty($price) || $price == 0) {
        return 'Fiyat Belirtilmemiş';
    }
    if ($price >= 1000000) {
        return number_format($price / 1000000, 1) . ' Milyon ₺';
    } elseif ($price >= 1000) {
        return number_format($price / 1000, 0) . '.000 ₺';
    }
    return number_format($price, 0) . ' ₺';
}
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
	<!-- For IE -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- For Resposive Device -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- For Window Tab Color -->
	<meta name="theme-color" content="#0D1A1C">
	<meta name="msapplication-navbutton-color" content="#0D1A1C">
	<meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
	<title><?= htmlspecialchars($property['title'] ?? 'Emlak Detayı') ?> - Gökhan Aydınlı Gayrimenkul</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
	
	<!-- OPTIMIZED: Critical CSS inline - Loader removed for faster loading -->
	<style>
		body { font-family: Arial, sans-serif; margin: 0; padding: 0; }
		.main-page-wrapper { min-height: 100vh; }
		.alert-custom { position: fixed; top: 20px; right: 20px; z-index: 9999; min-width: 300px; padding: 15px 20px; border-radius: 8px; }
	</style>
	
	<!-- OPTIMIZED: Non-critical CSS loaded asynchronously -->
	<link rel="preload" href="css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/style.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/responsive.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="css/smart-image-system.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	
	<!-- External CSS loaded with defer -->
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
	
	<!-- Fallback for browsers that don't support preload -->
	<noscript>
		<link rel="stylesheet" href="css/bootstrap.min.css">
		<link rel="stylesheet" href="css/style.min.css">
		<link rel="stylesheet" href="css/responsive.css">
		<link rel="stylesheet" href="css/smart-image-system.css">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@fancyapps/ui@5.0/dist/fancybox/fancybox.css">
		<link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css">
		<link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.7.2/font/bootstrap-icons.css">
	</noscript>
	
	<style>
		.alert-custom {
			position: fixed;
			top: 20px;
			right: 20px;
			z-index: 9999;
			min-width: 300px;
			padding: 15px 20px;
			border-radius: 8px;
			font-weight: 500;
			box-shadow: 0 4px 12px rgba(0,0,0,0.15);
		}
		.alert-success {
			background: #d4edda;
			color: #155724;
			border: 1px solid #c3e6cb;
		}
		.alert-error {
			background: #f8d7da;
			color: #721c24;
			border: 1px solid #f5c6cb;
		}
		
		/* Property Description Styling */
		.property-description {
			line-height: 1.7;
		}
		
		.property-description p {
			margin-bottom: 16px;
			font-size: 16px;
			line-height: 1.6;
		}
		
		.property-description ul, 
		.property-description ol {
			margin: 16px 0;
			padding-left: 24px;
		}
		
		.property-description li {
			margin-bottom: 8px;
			line-height: 1.6;
		}
		
		.property-description strong, 
		.property-description b {
			font-weight: 600;
			color: #333;
		}
		
		.property-description h1,
		.property-description h2,
		.property-description h3,
		.property-description h4 {
			margin: 24px 0 16px 0;
			color: #0d6efd;
			font-weight: 600;
		}
		
		.property-description h1 { font-size: 28px; }
		.property-description h2 { font-size: 24px; }
		.property-description h3 { font-size: 20px; }
		.property-description h4 { font-size: 18px; }
	</style>
	
	<?php if (isset($_SESSION['success'])): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			showAlert('<?= addslashes($_SESSION['success']) ?>', 'success');
		});
	</script>
	<?php unset($_SESSION['success']); endif; ?>
	
	<?php if (isset($_SESSION['error'])): ?>
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			showAlert('<?= addslashes($_SESSION['error']) ?>', 'error');
		});
	</script>
	<?php unset($_SESSION['error']); endif; ?>
	
	<!-- reCAPTCHA v3 - GEÇİCİ OLARAK DEVRE DIŞI -->
	<!-- Honeypot Bot Koruması (reCAPTCHA yerine) -->
	<script>
		document.addEventListener('DOMContentLoaded', function() {
			const tourForm = document.getElementById('tourForm');
			if (tourForm) {
				// Honeypot alanı ekle (gizli)
				const honeypot = document.createElement('input');
				honeypot.type = 'text';
				honeypot.name = 'website';
				honeypot.style.display = 'none';
				honeypot.style.position = 'absolute';
				honeypot.style.left = '-9999px';
				honeypot.setAttribute('tabindex', '-1');
				honeypot.setAttribute('autocomplete', 'off');
				tourForm.appendChild(honeypot);
				
				tourForm.addEventListener('submit', function(e) {
					// Submit butonu disable et ve loading göster
					const submitBtn = tourForm.querySelector('button[type="submit"]');
					const originalText = submitBtn.innerHTML;
					submitBtn.disabled = true;
					submitBtn.innerHTML = '<i class="fa fa-spinner fa-spin"></i> Gönderiliyor...';
					
					// Honeypot kontrolü
					if (honeypot.value !== '') {
						e.preventDefault();
						alert('Bot aktivitesi tespit edildi.');
						submitBtn.disabled = false;
						submitBtn.innerHTML = originalText;
						return;
					}
					
					// 1 saniye bekle (bot tespiti için)
					setTimeout(function() {
						console.log('Tur formu güvenlik kontrolü başarılı');
					}, 1000);
				});
			}
			
			console.log('Tur formu honeypot bot koruması aktif');
		});
	</script>
	<script>
		// Basit form gönderimi - reCAPTCHA olmadan
		console.log('Tour form ready - reCAPTCHA disabled for testing');
	</script>
</head>

<body>
	<?php 
	// Tur talebi mesajlarını göster
	if (isset($_GET['tour'])) {
		if ($_GET['tour'] === 'success') {
			echo '<div style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #d4edda; color: #155724; padding: 15px 20px; border-radius: 8px; border: 1px solid #c3e6cb; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
				<div style="display: flex; align-items: center;">
					<span style="font-size: 18px; margin-right: 8px;">✅</span>
					<div>
						<strong>Tur Talebiniz Gönderildi!</strong><br>
						<small>En kısa sürede size dönüş yapacağız.</small>
					</div>
				</div>
			</div>';
		} elseif ($_GET['tour'] === 'partial') {
			echo '<div style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #fff3cd; color: #856404; padding: 15px 20px; border-radius: 8px; border: 1px solid #ffeaa7; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
				<div style="display: flex; align-items: center;">
					<span style="font-size: 18px; margin-right: 8px;">⚠️</span>
					<div>
						<strong>Tur Talebiniz Kaydedildi!</strong><br>
						<small>Mail sunucusunda sorun var ama talebiniz bize ulaştı.</small>
					</div>
				</div>
			</div>';
		} elseif ($_GET['tour'] === 'error') {
			echo '<div style="position: fixed; top: 20px; right: 20px; z-index: 9999; background: #f8d7da; color: #721c24; padding: 15px 20px; border-radius: 8px; border: 1px solid #f5c6cb; max-width: 400px; box-shadow: 0 4px 12px rgba(0,0,0,0.15);">
				<div style="display: flex; align-items: center;">
					<span style="font-size: 18px; margin-right: 8px;">❌</span>
					<div>
						<strong>Hata!</strong><br>
						<small>Lütfen tüm alanları doldurun.</small>
					</div>
				</div>
			</div>';
		}
	}
	?>
	<div class="main-page-wrapper">
		<!-- LOADER REMOVED COMPLETELY FOR FASTER LOADING -->

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
						<div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
							<!-- Giriş/Panel butonları kaldırıldı - daha temiz görünüm için -->
						</div>
						<nav class="navbar navbar-expand-lg p0 order-lg-2">
							<button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
								data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
								aria-label="Toggle navigation">
								<span></span>
							</button>
							<div class="collapse navbar-collapse" id="navbarNav">
								<ul class="navbar-nav align-items-lg-center">
									<li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="index.php">Ana Sayfa</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
									</li>
									<li class="nav-item dashboard-menu">
										<a class="nav-link" href="porfoy.html">Portföy</a>
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
									<li class="d-md-none ps-2 pe-2 mt-20">
										<a href="dashboard/add-property.html" class="btn-two w-100" target="_blank"><span>Add Listing</span> <i class="fa-thin fa-arrow-up-right"></i></a>
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
				<h3 class="mb-35 xl-mb-20 pt-15"><?= htmlspecialchars($property['title'] ?? 'Emlak Detayı') ?></h3>
				<ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
					<li><a href="index.php">Anasayfa</a></li>
					<li>/</li>
					<li><a href="portfoy.php">Portföy</a></li>
					<li>/</li>
					<li>İlan Detayı</li>
				</ul>
			</div>
			<img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
		</div>

		<!--
		=====================================================
			Property Listing Details
		=====================================================
		-->
		<div class="listing-details-one theme-details-one bg-pink pt-180 lg-pt-150 pb-150 xl-pb-120">
			<div class="container">
				<div class="row">
					<div class="col-lg-6">
						<h3 class="property-titlee"><?= htmlspecialchars($property['title'] ?? 'Emlak İlanı') ?></h3>
						<div class="d-flex flex-wrap mt-10">
							<div class="list-type text-uppercase border-20 mt-15 me-3"><?= strtoupper($property['type'] ?? 'RENT') === 'SALE' ? 'SATILIK' : 'KİRALIK' ?></div>
							<div class="address mt-15">
								<i class="bi bi-geo-alt"></i> 
								<?php 
								$location_parts = [];
								
								// Yeni alanları kontrol et
								$il = $property['il'] ?? null;
								$ilce = $property['ilce'] ?? null;
								$mahalle = $property['mahalle'] ?? null;
								
								// Eski alanları kontrol et (yedek olarak)
								if (empty($il)) $il = $property['city'] ?? null;
								if (empty($ilce)) $ilce = $property['district'] ?? null;
								if (empty($mahalle)) $mahalle = $property['neighborhood'] ?? null;
								
								// Boş olmayan değerleri ekle
								if (!empty($il) && $il != '0' && strtolower($il) != 'null') $location_parts[] = $il;
								if (!empty($ilce) && $ilce != '0' && strtolower($ilce) != 'null') $location_parts[] = $ilce;
								if (!empty($mahalle) && $mahalle != '0' && strtolower($mahalle) != 'null') $location_parts[] = $mahalle;
								
								$full_location = !empty($location_parts) ? implode(' / ', $location_parts) : ($property['location'] ?? 'Konum Belirtilmemiş');
								echo htmlspecialchars($full_location);
								?>
							</div>
						</div>
					</div>
					<div class="col-lg-6 text-lg-end">
						<div class="d-inline-block md-mt-40">
							<div class="price color-dark fw-500">Fiyat: <?= formatPrice($property['price'] ?? 0) ?></div>
							<ul class="style-none d-flex align-items-center action-btns">
								<li class="me-auto fw-500 color-dark"><i class="fa-sharp fa-regular fa-share-nodes me-2"></i> Paylaş</li>
								<li><a href="#" class="d-flex align-items-center justify-content-center rounded-circle tran3s"><i class="fa-light fa-heart"></i></a></li>
								<li><a href="#" class="d-flex align-items-center justify-content-center rounded-circle tran3s"><i class="fa-light fa-bookmark"></i></a></li>
								<li><a href="contact.php" class="d-flex align-items-center justify-content-center rounded-circle tran3s"><i class="fa-light fa-circle-plus"></i></a></li>
							</ul>
						</div>
					</div>
				</div>
				<div class="media-gallery mt-100 xl-mt-80 lg-mt-60">
					<div id="media_slider" class="carousel slide row">
						<div class="col-lg-10">
							<div class="bg-white shadow4 border-20 p-30 md-mb-20">
								<div class="position-relative z-1 overflow-hidden border-20">
									<div class="img-fancy-btn border-10 fw-500 fs-16 color-dark">
										Tüm <?= count($images) ?> Fotoğrafa Bak
										<?php foreach($images as $index => $image): ?>
										<a href="<?= htmlspecialchars($image) ?>" class="d-block" data-fancybox="mainImg" data-caption="<?= htmlspecialchars($property['title'] ?? 'Emlak İlanı') ?>"></a>
										<?php endforeach; ?>
									</div>
									<div class="carousel-inner">
										<?php foreach($images as $index => $image): ?>
										<div class="carousel-item <?= $index === 0 ? 'active' : '' ?>">
											<img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($property['title'] ?? 'Emlak İlanı') ?>" class="border-20 w-100 auto-rotate" style="height: 400px; object-fit: cover;">
										</div>
										<?php endforeach; ?>
									</div>
									<?php if(count($images) > 1): ?>
									<button class="carousel-control-prev" type="button" data-bs-target="#media_slider" data-bs-slide="prev">
										<i class="bi bi-chevron-left"></i>
										<span class="visually-hidden">Önceki</span>
									</button>
									<button class="carousel-control-next" type="button" data-bs-target="#media_slider" data-bs-slide="next">
										<i class="bi bi-chevron-right"></i>
										<span class="visually-hidden">Sonraki</span>
									</button>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<div class="col-lg-2">
							<div class="carousel-indicators position-relative border-15 bg-white shadow4 p-15 w-100 h-100">
								<?php foreach($images as $index => $image): ?>
								<?php if($index < 4): // Sadece ilk 4 resmi thumbnail olarak göster ?>
								<button type="button" data-bs-target="#media_slider" data-bs-slide-to="<?= $index ?>" class="<?= $index === 0 ? 'active' : '' ?>" aria-current="<?= $index === 0 ? 'true' : 'false' ?>" aria-label="Resim <?= $index + 1 ?>">
									<img src="<?= htmlspecialchars($image) ?>" alt="<?= htmlspecialchars($property['title'] ?? 'Emlak İlanı') ?>" class="border-10 w-100 auto-rotate" style="height: 80px; object-fit: cover;">
								</button>
								<?php endif; ?>
								<?php endforeach; ?>
							</div>
						</div>
					</div>
				</div>
				<!-- /.media-gallery -->
				<div class="property-feature-list bg-white shadow4 border-20 p-40 mt-50 mb-60">
					<h4 class="sub-title-one mb-40 lg-mb-20">Emlak Özellikleri</h4>
					<ul class="style-none d-flex flex-wrap align-items-center justify-content-between">
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_47.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Brüt Alan: <?= !empty($property['area_gross']) ? htmlspecialchars($property['area_gross']) . ' m²' : (!empty($property['area']) ? htmlspecialchars($property['area']) . ' m²' : 'Belirtilmemiş') ?></span>
						</li>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_48.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Oda Sayısı: <?= !empty($property['room_count']) && $property['room_count'] !== '0' ? htmlspecialchars($property['room_count']) : 'Belirtilmemiş' ?></span>
						</li>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_50.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Bulunduğu Kat: <?= !empty($property['floor']) && $property['floor'] !== '0' ? htmlspecialchars($property['floor']) : 'Belirtilmemiş' ?></span>
						</li>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_51.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Otopark: <?php 
								$parking = $property['parking'] ?? '';
								if (!empty($parking) && $parking !== '0' && $parking !== '-' && $parking !== 'NULL' && strtolower($parking) !== 'null') {
									// Yeni standart: Seçilen değeri aynen göster, sadece Türkçe karakterleri düzelt
									if ($parking === 'Acik Otopark') {
										echo 'Açık Otopark';
									}
									elseif ($parking === 'Kapali Otopark') {
										echo 'Kapalı Otopark';
									}
									elseif ($parking === 'Acik ve Kapali Otopark') {
										echo 'Açık ve Kapalı Otopark';
									}
									elseif ($parking === 'Otopark Yok') {
										echo 'Otopark Yok';
									}
									elseif ($parking === 'Otopark Var') {
										echo 'Otopark Var';
									}
									// Diğer mevcut değerler aynen gösterilsin
									else {
										echo htmlspecialchars($parking);
									}
								} else {
									echo 'Belirtilmemiş';
								}
							?></span>
						</li>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_52.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Kullanım Durumu: <?php 
								$usage_status = $property['usage_status'] ?? '';
								if (!empty($usage_status) && $usage_status !== '0' && $usage_status !== '-' && $usage_status !== 'NULL' && strtolower($usage_status) !== 'null') {
									// Standart değerler
									if ($usage_status === 'Boş') {
										echo 'Boş';
									}
									elseif ($usage_status === 'Kiracılı') {
										echo 'Kiracılı';
									}
									elseif ($usage_status === 'Malik Kullanımında') {
										echo 'Malik Kullanımında';
									}
									elseif ($usage_status === 'Yatırım Amaçlı') {
										echo 'Yatırım Amaçlı';
									}
									// Eski değerlerin dönüşümü (geriye uyumluluk)
									elseif (in_array(strtolower($usage_status), ['boş', 'empty'])) {
										echo 'Boş';
									}
									elseif (in_array(strtolower($usage_status), ['dolu', 'occupied', 'tenant', 'kiracılı'])) {
										echo 'Kiracılı';
									}
									elseif (in_array(strtolower($usage_status), ['owner', 'mülk sahibi'])) {
										echo 'Malik Kullanımında';
									}
									elseif (in_array(strtolower($usage_status), ['investment', 'yatırım'])) {
										echo 'Yatırım Amaçlı';
									}
									// Diğer değerler - varsayılan olarak "Boş"
									else {
										echo 'Boş';
									}
								} else {
									echo 'Belirtilmemiş';
								}
							?></span>
						</li>
<?php 
						// Krediye Uygunluk sadece satılık ilanlar için gösterilir
						$propertyType = strtoupper($property['type'] ?? 'RENT');
						if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
						?>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_53.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Krediye Uygunluk: <?php 
								$credit_eligible = $property['credit_eligible'] ?? '';
								if (!empty($credit_eligible) && $credit_eligible !== '0' && $credit_eligible !== '-' && $credit_eligible !== 'NULL' && strtolower($credit_eligible) !== 'null') {
									// Türkçe değerler (form'dan gelen)
									if (in_array($credit_eligible, ['Evet, krediye uygun', 'Hayır, krediye uygun değil'])) {
										echo htmlspecialchars($credit_eligible);
									}
									// İngilizce değerler (geriye uyumluluk)
									elseif ($credit_eligible === 'yes') {
										echo 'Evet, krediye uygun';
									} elseif ($credit_eligible === 'no') {
										echo 'Hayır, krediye uygun değil';
									}
									// Kısa değerler
									elseif (strtolower($credit_eligible) === 'evet') {
										echo 'Evet, krediye uygun';
									} elseif (strtolower($credit_eligible) === 'hayır') {
										echo 'Hayır, krediye uygun değil';
									}
									// Diğer değerler
									else {
										echo htmlspecialchars($credit_eligible);
									}
								} else {
									echo 'Belirtilmemiş';
								}
							?></span>
						</li>
						<?php endif; ?>
						<?php 
						// Tapu Durumu sadece satılık ilanlar için gösterilir
						$propertyType = strtoupper($property['type'] ?? 'RENT');
						if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
						?>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_54.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Tapu Durumu: <?php 
								$deed_status = $property['deed_status'] ?? '';
								if (!empty($deed_status) && $deed_status !== '0' && $deed_status !== 'NULL' && strtolower($deed_status) !== 'null') {
									// Türkçe değerler (form'dan gelen)
									if (in_array($deed_status, ['Tapu Hazır', 'Tapu Bekleniyor', 'Hisseli Tapu', 'Kat Mülkiyeti'])) {
										echo htmlspecialchars($deed_status);
									}
									// İngilizce değerler (geriye uyumluluk)
									elseif ($deed_status === 'ready') {
										echo 'Tapu Hazır';
									} elseif ($deed_status === 'pending') {
										echo 'Tapu Bekleniyor';
									} elseif ($deed_status === 'shared') {
										echo 'Hisseli Tapu';
									} elseif ($deed_status === 'condominium') {
										echo 'Kat Mülkiyeti';
									}
									// Diğer değerler
									else {
										echo htmlspecialchars($deed_status);
									}
								} else {
									echo 'Belirtilmemiş';
								}
							?></span>
						</li>
						<?php endif; ?>
<?php 
						// Takasa Uygunluk sadece satılık ilanlar için gösterilir
						$propertyType = strtoupper($property['type'] ?? 'RENT');
						if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
						?>
						<li>
							<img src="images/lazy.svg" data-src="images/icon/icon_55.svg" alt="" class="lazy-img icon">
							<span class="fs-20 color-dark">Takasa Uygunluk: <?php 
								$exchange = $property['exchange'] ?? '';
								if (!empty($exchange) && $exchange !== '0' && $exchange !== 'NULL' && strtolower($exchange) !== 'null') {
									// Türkçe değerler (form'dan gelen)
									if (in_array($exchange, ['Takasa Açık', 'Takasa Kapalı'])) {
										echo htmlspecialchars($exchange);
									}
									// İngilizce değerler (geriye uyumluluk)
									elseif ($exchange === 'yes') {
										echo 'Takasa Açık';
									} elseif ($exchange === 'no') {
										echo 'Takasa Kapalı';
									}
									// Kısa değerler
									elseif (strtolower($exchange) === 'evet') {
										echo 'Takasa Açık';
									} elseif (strtolower($exchange) === 'hayır') {
										echo 'Takasa Kapalı';
									}
									// Diğer değerler
									else {
										echo htmlspecialchars($exchange);
									}
								} else {
									echo 'Belirtilmemiş';
								}
							?></span>
						</li>
						<?php endif; ?>
						</li>
					</ul>
				</div>
				<!-- /.property-feature-list -->
				<div class="row">
					<div class="col-xl-8">
						<div class="property-overview bg-white shadow4 border-20 p-40 mb-50">
							<h4 class="mb-20">Açıklama</h4>
							<div class="property-description fs-20 lh-lg">
								<?php 
								$description = $property['description'] ?? 'Bu güzel emlak hakkında detaylı bilgi için lütfen bizimle iletişime geçin.';
								
								// HTML içeriğini güvenli şekilde göster
								// Sadece güvenli HTML etiketlerine izin ver
								$allowed_tags = '<p><br><b><strong><i><em><u><ul><ol><li><a><h1><h2><h3><h4><h5><h6><span><div>';
								$clean_description = strip_tags($description, $allowed_tags);
								
								// Eğer HTML içerik varsa direkt göster, yoksa nl2br uygula
								if (strpos($clean_description, '<') !== false) {
									echo $clean_description;
								} else {
									echo nl2br(htmlspecialchars($clean_description));
								}
								?>
							</div>
						</div>
						<!-- /.property-overview -->
						<div class="property-feature-accordion bg-white shadow4 border-20 p-40 mb-50">
							<h4 class="mb-20">Emlak Özellikleri</h4>
							<p class="fs-20 lh-lg">Bu emlakin tüm detaylı özelliklerini aşağıda bulabilirsiniz.</p>

							<div class="accordion-style-two mt-45">
								<div class="accordion" id="accordionTwo">
									<div class="accordion-item">
										<h2 class="accordion-header">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseOneA" aria-expanded="false" aria-controls="collapseOneA">
												Emlak Detayları
											</button>
										  </h2>
										<div id="collapseOneA" class="accordion-collapse collapse show" data-bs-parent="#accordionTwo">
											<div class="accordion-body">
												<div class="feature-list-two">
													<ul class="style-none d-flex flex-wrap justify-content-between">
														<li><span>Oda Sayısı: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['room_count'] ?? '-') ?></span></li>
														<li><span>Yatak Odası: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['bedrooms'] ?? '-') ?></span></li>
														<li><span>Salon Sayısı: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['living_room_count'] ?? '-') ?></span></li>
														<li><span>Banyo: </span> <span class="fw-500 color-dark"><?php 
															$bathrooms = $property['bathrooms'] ?? '';
															if (!empty($bathrooms) && $bathrooms !== '0' && $bathrooms !== 'Yok') {
																if (in_array($bathrooms, ['1', '2', '3'])) {
																	echo htmlspecialchars($bathrooms);
																} elseif (is_numeric($bathrooms) && intval($bathrooms) > 3) {
																	echo '3';
																} else {
																	echo '1';
																}
															} else {
																echo '1';
															}
														?></span></li>
														<li><span>Brüt Alan: </span> <span class="fw-500 color-dark"><?= !empty($property['area_gross']) ? htmlspecialchars($property['area_gross']) . ' m²' : (!empty($property['area']) ? htmlspecialchars($property['area']) . ' m²' : '-') ?></span></li>
														<li><span>Net Alan: </span> <span class="fw-500 color-dark"><?= !empty($property['area_net']) ? htmlspecialchars($property['area_net']) . ' m²' : '-' ?></span></li>
														<li><span>Bina Yaşı: </span> <span class="fw-500 color-dark"><?php 
															if (!empty($property['building_age']) && $property['building_age'] != '0') {
																$age = htmlspecialchars($property['building_age']);
																echo $age == '0' ? 'Yeni' : $age . ' Yıl';
															} else {
																echo '-';
															}
														?></span></li>
														<li><span>Bulunduğu Kat: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['floor_located'] ?? $property['floor'] ?? '-') ?></span></li>
														<li><span>Bina Kat Sayısı: </span> <span class="fw-500 color-dark"><?php 
															$building_floors = $property['building_floors'] ?? '';
															if (!empty($building_floors) && $building_floors !== '0' && $building_floors !== 'NULL') {
																if (is_numeric($building_floors)) {
																	echo $building_floors . ' Kat';
																} else {
																	echo htmlspecialchars($building_floors);
																}
															} else {
																echo '-';
															}
														?></span></li>
														<li><span>Otopark: </span> <span class="fw-500 color-dark"><?php 
															$parking = $property['parking'] ?? '';
															if (!empty($parking) && $parking !== '0' && $parking !== '-' && $parking !== 'NULL') {
																switch($parking) {
																	case 'open': echo 'Açık Otopark'; break;
																	case 'closed': echo 'Kapalı Otopark'; break;
																	case 'none': echo 'Otopark Yok'; break;
																	default: echo htmlspecialchars($parking);
																}
															} else {
																echo '-';
															}
														?>
														</span></li>

													<li><span>Eşyalı: </span> <span class="fw-500 color-dark"><?php 
    $furnished = $property['furnished'] ?? 0;
    if ($furnished == 1) {
        echo 'Evet';
    } else {
        echo 'Hayır';
    }
?></span></li>
														<li><span>Asansör: </span> <span class="fw-500 color-dark"><?php 
															$elevator = $property['elevator'] ?? '';
															if (!empty($elevator) && $elevator !== '0' && $elevator !== 'NULL') {
																if ($elevator === 'yes' || $elevator === '1' || $elevator === 1) {
																	echo 'Var';
																} elseif ($elevator === 'no' || $elevator === '0' || $elevator === 0) {
																	echo 'Yok';
																} else {
																	echo htmlspecialchars($elevator);
																}
															} else {
																echo 'Yok';
															}
														?></span></li>
														<li><span>Isıtma: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['heating'] ?? '-') ?></span></li>
														<li><span>Kullanım Durumu: </span> <span class="fw-500 color-dark"><?php 
															$usage_status = $property['usage_status'] ?? '';
															if (!empty($usage_status) && $usage_status !== '0' && $usage_status !== 'NULL') {
																// Standart değerler
																if ($usage_status === 'Boş') {
																	echo 'Boş';
																} elseif ($usage_status === 'Kiracılı') {
																	echo 'Kiracılı';
																} elseif ($usage_status === 'Malik Kullanımında') {
																	echo 'Malik Kullanımında';
																} elseif ($usage_status === 'Yatırım Amaçlı') {
																	echo 'Yatırım Amaçlı';
																} 
																// Eski değerlerin dönüşümü
																elseif (in_array(strtolower($usage_status), ['boş', 'empty'])) {
																	echo 'Boş';
																} elseif (in_array(strtolower($usage_status), ['dolu', 'occupied', 'tenant', 'kiracılı'])) {
																	echo 'Kiracılı';
																} elseif (in_array(strtolower($usage_status), ['owner', 'mülk sahibi'])) {
																	echo 'Malik Kullanımında';
																}
																// Diğer değerler
																else {
																	echo 'Boş';
																}
															} else {
																echo '-';
															}
														?></span></li>
														<li><span>Aidat: </span> <span class="fw-500 color-dark"><?= !empty($property['dues']) && $property['dues'] != '0' ? number_format($property['dues']) . ' ₺' : '-' ?></span></li>
														<?php 
														// Krediye Uygunluk ve Tapu Durumu sadece satılık ilanlar için gösterilir
														$propertyType = strtoupper($property['type'] ?? 'RENT');
														if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
														?>
														<li><span>Krediye Uygunluk: </span> <span class="fw-500 color-dark"><?php 
															$credit_eligible = $property['credit_eligible'] ?? '';
															if (!empty($credit_eligible) && $credit_eligible !== '0') {
																echo $credit_eligible === 'yes' ? 'Evet, krediye uygun' : 'Hayır, krediye uygun değil';
															} else {
																echo '-';
															}
														?></span></li>
														<li><span>Tapu Durumu: </span> <span class="fw-500 color-dark"><?php 
															$deed_status = $property['deed_status'] ?? '';
															if (!empty($deed_status) && $deed_status !== '0') {
																switch($deed_status) {
																	case 'ready': echo 'Tapu Hazır'; break;
																	case 'pending': echo 'Tapu Bekleniyor'; break;
																	case 'shared': echo 'Hisseli Tapu'; break;
																	case 'condominium': echo 'Kat Mülkiyeti'; break;
																	default: echo htmlspecialchars($deed_status);
																}
															} else {
																echo '-';
															}
														?></span></li>
														<?php endif; ?>
														<?php 
														// Takas sadece satılık ilanlar için gösterilir
														$propertyType = strtoupper($property['type'] ?? 'RENT');
														if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
														?>
														<li><span>Takas: </span> <span class="fw-500 color-dark"><?php 
															$exchange = $property['exchange'] ?? '';
															if (!empty($exchange) && $exchange !== '0') {
																echo $exchange === 'yes' ? 'Takasa Açık' : 'Takasa Kapalı';
															} else {
																echo '-';
															}
														?></span></li>
														<?php endif; ?>
														<li><span>Emlak Tipi: </span> <span class="fw-500 color-dark"><?php 
															$category = $property['category'] ?? '';
															switch($category) {
																case 'konut': echo 'Konut'; break;
																case 'is_yeri': echo 'İş Yeri'; break;
																case 'arsa': echo 'Arsa'; break;
																case 'bina': echo 'Bina'; break;
																default: echo htmlspecialchars($category ?: '-');
															}
														?></span></li>
														<li><span>İşlem Tipi: </span> <span class="fw-500 color-dark"><?php 
															$type = strtoupper($property['type'] ?? 'RENT');
															switch($type) {
																case 'SALE': echo 'Satılık'; break;
																case 'RENT': echo 'Kiralık'; break;
																case 'DAILY_RENT': echo 'Günlük Kiralık'; break;
																case 'TRANSFER_SALE': echo 'Devren Satılık'; break;
																case 'TRANSFER_RENT': echo 'Devren Kiralık'; break;
																default: echo 'Kiralık';
															}
														?></span></li>
													</ul>
												</div>
												<!-- /.feature-list-two -->
											</div>
										</div>
									</div>
									
									<?php if (!empty($interior_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-primary">✓ İç Özellikler</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($interior_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
									
									<?php if (!empty($exterior_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-success">✓ Dış Özellikler</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($exterior_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
									
									<?php if (!empty($neighborhood_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-info">✓ Muhit Özellikleri</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($neighborhood_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
									
									<?php if (!empty($transportation_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-warning">✓ Ulaşım Özellikleri</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($transportation_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
									
									<?php if (!empty($view_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-danger">✓ Manzara Özellikleri</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($view_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
									
									<?php if (!empty($housing_type_features)): ?>
									<div class="feature-section mb-4">
										<h4 class="mb-3">
											<span class="text-secondary">✓ Konut Tipi Özellikleri</span>
										</h4>
										<div class="feature-list-two">
											<ul class="style-none d-flex flex-wrap justify-content-start">
												<?php foreach ($housing_type_features as $feature): ?>
												<li style="width: 50%; margin-bottom: 10px;">
													<span class="fw-500 color-dark">• <?= htmlspecialchars($feature) ?></span>
												</li>
												<?php endforeach; ?>
											</ul>
										</div>
									</div>
									<?php endif; ?>
								</div>
							</div>
						</div>
						<!-- /.property-feature-accordion -->
						
						<!-- Location Details Accordion -->
						<div class="property-feature-accordion bg-white shadow4 border-20 p-40 mb-50">
							<h4 class="mb-30">Konum Bilgileri</h4>
							<div class="accordion-style-two">
								<div class="accordion" id="accordionLocation">
									<div class="accordion-item">
										<h2 class="accordion-header">
											<button class="accordion-button" type="button" data-bs-toggle="collapse" data-bs-target="#collapseLocation" aria-expanded="true" aria-controls="collapseLocation">
												Adres Detayları
											</button>
										</h2>
										<div id="collapseLocation" class="accordion-collapse collapse show" data-bs-parent="#accordionLocation">
											<div class="accordion-body">
												<div class="feature-list-two">
													<ul class="style-none d-flex flex-wrap justify-content-between">
														<li><span>İl: </span> <span class="fw-500 color-dark"><?php 
															$il_display = $property['il'] ?? $property['city'] ?? '';
															echo htmlspecialchars($il_display ?: '-');
														?></span></li>
														<li><span>İlçe: </span> <span class="fw-500 color-dark"><?php 
															$ilce_display = $property['ilce'] ?? $property['district'] ?? '';
															echo htmlspecialchars($ilce_display ?: '-');
														?></span></li>
														<li><span>Mahalle: </span> <span class="fw-500 color-dark"><?php 
															$mahalle_display = '';
															if (isset($property['neighborhood']) && trim($property['neighborhood']) !== '') {
																$mahalle_display = trim($property['neighborhood']);
															} elseif (isset($property['mahalle']) && trim($property['mahalle']) !== '') {
																$mahalle_display = trim($property['mahalle']);
															}
															echo htmlspecialchars($mahalle_display ?: '-');
														?></span></li>
														<li><span>Konum Tipi: </span> <span class="fw-500 color-dark"><?php 
															$location_type = $property['location_type'] ?? '';
															switch(strtolower($location_type)) {
																case 'site': echo 'Site'; break;
																case 'standalone': echo 'Müstakil'; break;
																case 'complex': echo 'Kompleks/Site İçi'; break;
																case 'apartment': echo 'Apartman'; break;
																case 'commercial': echo 'Ticari Alan'; break;
																case 'industrial': echo 'Sanayi Bölgesi'; break;
																default: echo !empty($location_type) ? htmlspecialchars($location_type) : '-';
															}
														?></span></li>
														<?php if (!empty($property['site_name'])): ?>
														<li><span>Site/Yapı Adı: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['site_name']) ?></span></li>
														<?php endif; ?>
														<?php if (!empty($property['address_details'])): ?>
														<li style="width: 100%; margin-top: 10px;"><span>Adres Detayı: </span> <span class="fw-500 color-dark"><?= htmlspecialchars($property['address_details']) ?></span></li>
														<?php endif; ?>
													</ul>
												</div>
											</div>
										</div>
									</div>
								</div>
							</div>
						</div>
						<!-- /.location-details-accordion -->
						
						<div class="property-amenities bg-white shadow4 border-20 p-40 mb-50">
							<h4 class="mb-20">Olanaklar</h4>
							<p class="fs-20 lh-lg pb-25">Bu emlakta bulunan tüm konfor ve olanaklar</p>
							<ul class="style-none d-flex flex-wrap justify-content-between list-style-two">
								<?php
								// Olanaklar dizisini kullan
								$display_facilities = $facilities;
								
								// Eğer hiç olanak yoksa varsayılan olanakları göster
								if (empty($display_facilities)) {
									$display_facilities = [
										'Klima & Isıtma',
										'Otopark',
										'Bahçe',
										'Engelli Erişimi',
										'Asansör',
										'WiFi'
									];
								}
								
								// Olanakları listele
								foreach ($display_facilities as $facility): ?>
								<li><?= htmlspecialchars($facility) ?></li>
								<?php endforeach; ?>
							</ul>
							<!-- /.list-style-two -->
						</div>
						<!-- /.property-amenities -->
					</div>
					<div class="col-xl-4 col-lg-8 me-auto ms-auto">
						<div class="theme-sidebar-one dot-bg p-30 ms-xxl-3 lg-mt-80">
							<div class="agent-info bg-white border-20 p-30 mb-40">
								<div class="text-center mt-25">
									<h6 class="name"><?= htmlspecialchars($property['owner_name'] ?? 'Gökhan Aydınlı') ?></h6>
									<p class="fs-16">Emlak Uzmanı & Broker</p>
									<ul class="style-none d-flex align-items-center justify-content-center social-icon">
										<li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
										<li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
										<li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
									</ul>
								</div>
								<div class="divider-line mt-40 mb-45 pt-20">
									<ul class="style-none">
										<li>Konum: <span>Türkiye, İstanbul</span></li>
										<li>Email: <span><a href="mailto:<?= htmlspecialchars($property['owner_email'] ?? 'info@gokhanaydinli.com') ?>"><?= htmlspecialchars($property['owner_email'] ?? 'info@gokhanaydinli.com') ?></a></span></li>
										<li>Telefon: <span><a href="tel:<?= htmlspecialchars($property['owner_phone'] ?? '+905302037083') ?>"><?= htmlspecialchars($property['owner_phone'] ?? '+90 (530) 203 70 83') ?></a></span></li>
									</ul>
								</div>
								<!-- /.divider-line -->
								<a href="contact.php" class="btn-nine text-uppercase rounded-3 w-100 mb-10">İLETİŞİME GEÇ</a>
							</div>
							<!-- /.agent-info -->

							<div class="tour-schedule bg-white border-20 p-30 mb-40">
								<h5 class="mb-40">Tur Planlayın</h5>
								<form action="contact.php" method="post" id="tourForm">
									<input type="hidden" name="form_type" value="tour_request">
									<input type="hidden" name="property_id" value="<?= $property_id ?>">
									<input type="hidden" name="property_title" value="<?= htmlspecialchars($property['title'] ?? '') ?>">
									<div class="input-box-three mb-25">
										<div class="label">Adınız*</div>
										<input type="text" name="name" placeholder="Tam adınız" class="type-input" required>
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">E-posta*</div>
										<input type="email" name="email" placeholder="E-posta adresiniz" class="type-input" required>
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Telefonunuz*</div>
										<input type="tel" name="phone" placeholder="Telefon numaranız" class="type-input" required>
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Tercih Edilen Tarih</div>
										<input type="date" name="preferred_date" class="type-input" min="<?= date('Y-m-d') ?>">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Tercih Edilen Saat</div>
										<select name="preferred_time" class="type-input">
											<option value="">Saat seçiniz</option>
											<option value="09:00">09:00</option>
											<option value="10:00">10:00</option>
											<option value="11:00">11:00</option>
											<option value="12:00">12:00</option>
											<option value="13:00">13:00</option>
											<option value="14:00">14:00</option>
											<option value="15:00">15:00</option>
											<option value="16:00">16:00</option>
											<option value="17:00">17:00</option>
											<option value="18:00">18:00</option>
										</select>
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-15">
										<div class="label">Mesajınız</div>
										<textarea name="message" placeholder="[<?= htmlspecialchars($property['title'] ?? 'Bu emlak') ?>] için tur planlamak istiyorum. Ek notlarınız varsa buraya yazabilirsiniz."></textarea>
									</div>
									<!-- /.input-box-three -->
									<input type="hidden" name="recaptcha_token" id="recaptcha_token">
									<button type="submit" class="btn-nine text-uppercase rounded-3 w-100 mb-10">TUR TALEBİ GÖNDER</button>
								</form>
							</div>
							<!-- /.tour-schedule -->

							<?php 
							// Kredi hesaplayıcı sadece satılık ilanlar için gösterilir
							$propertyType = strtoupper($property['type'] ?? 'RENT');
							if ($propertyType === 'SALE' || $propertyType === 'TRANSFER_SALE'): 
							?>
							<div class="mortgage-calculator bg-white border-20 p-30 mb-40">
								<div class="d-flex align-items-center mb-30" style="background: linear-gradient(135deg, #004481 0%, #0056b3 100%); padding: 20px; border-radius: 15px; color: white;">
									<div>
										<h5 class="mb-0" style="color: white; font-weight: 600;">Garanti BBVA Konut Kredisi</h5>
										<small style="color: #e3f2fd; font-size: 13px;"><i class="fas fa-chart-line me-1"></i>Güncel faiz oranları ile hesaplayın</small>
									</div>
									<div class="ms-auto">
										<small style="color: #e3f2fd; font-size: 12px;">🏡 Güvenilir Bankacılık</small>
									</div>
								</div>
								
								<!-- Garanti BBVA Bilgi Kutusu -->
								<div class="bg-light p-20 border-radius-10 mb-25">
									<div class="row">
										<div class="col-6">
											<div class="text-center">
												<div class="fw-bold text-success fs-18">%2.19</div>
												<small class="text-muted">Başlangıç Faiz Oranı</small>
											</div>
										</div>
										<div class="col-6">
											<div class="text-center">
												<div class="fw-bold text-primary fs-18">30 Yıl</div>
												<small class="text-muted">Maksimum Vade</small>
											</div>
										</div>
									</div>
									<div class="text-center mt-15">
										<small class="text-muted"><i class="fas fa-info-circle me-1"></i> Faiz oranları müşteri profiline göre değişiklik gösterebilir.</small>
									</div>
								</div>
								
								<form action="#">
									<div class="input-box-three mb-25">
										<div class="label">Ev Fiyatı*</div>
										<input type="tel" placeholder="<?= number_format($property['price'] ?? 0, 0) ?> ₺" class="type-input" value="<?= $property['price'] ?? 0 ?>">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Peşinat* (Min. %20)</div>
										<input type="tel" placeholder="₺" class="type-input">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Faiz Oranı* (%)</div>
										<input type="tel" placeholder="2.19" class="type-input" value="2.19">
									</div>
									<!-- /.input-box-three -->
									<div class="input-box-three mb-25">
										<div class="label">Kredi Vadesi (Yıl, Max: 30)</div>
										<input type="tel" placeholder="15" class="type-input" max="30">
									</div>
									<!-- /.input-box-three -->
									<button type="button" class="btn-five text-uppercase sm rounded-3 w-100 mb-10">HESAPLA</button>
								</form>
							</div>
							<!-- /.mortgage-calculator -->
							<?php endif; ?>

							<div class="feature-listing bg-white border-20 p-30">
								<h5 class="mb-40">Öne Çıkan İlanlar</h5>
								<div id="F-listing" class="carousel slide">
									<div class="carousel-indicators">
										<button type="button" data-bs-target="#F-listing" data-bs-slide-to="0" class="active" aria-current="true" aria-label="Slide 1"></button>
										<button type="button" data-bs-target="#F-listing" data-bs-slide-to="1" aria-label="Slide 2"></button>
										<button type="button" data-bs-target="#F-listing" data-bs-slide-to="2" aria-label="Slide 3"></button>
									</div>
									<div class="carousel-inner">
										<div class="carousel-item active">
											<div class="listing-card-one style-three border-10">
												<div class="img-gallery">
													<div class="position-relative border-10 overflow-hidden">
														<div class="tag bg-white text-dark fw-500 border-20">KİRALIK</div>
														<a href="#" class="fav-btn tran3s"><i class="fa-light fa-heart"></i></a>
														<img src="images/listing/img_01.jpg" class="w-100 border-10" alt="..." style="height: 200px; object-fit: cover;">
														<div class="img-slider-btn">
															03 <i class="fa-regular fa-image"></i>
															<a href="images/listing/img_large_01.jpg" class="d-block" data-fancybox="imgA" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_02.jpg" class="d-block" data-fancybox="imgA" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_03.jpg" class="d-block" data-fancybox="imgA" data-caption="Blueberry villa"></a>
														</div>
													</div>
												</div>
												<!-- /.img-gallery -->
												<div class="property-info mt-15">
													<div class="d-flex justify-content-between align-items-end">
														<div>
															<strong class="price fw-500 color-dark">2.500.000 ₺</strong>
															<div class="address m0 pt-5">Kadıköy, İstanbul</div>
														</div>
														<a href="property-details.php" class="btn-four rounded-circle"><i class="bi bi-arrow-up-right"></i></a>
													</div>
												</div>
												<!-- /.property-info -->
											</div>
											<!-- /.listing-card-one -->
										</div>
										<div class="carousel-item">
											<div class="listing-card-one style-three border-10">
												<div class="img-gallery">
													<div class="position-relative border-10 overflow-hidden">
														<div class="tag bg-white text-dark fw-500 border-20">SATILIK</div>
														<a href="#" class="fav-btn tran3s"><i class="fa-light fa-heart"></i></a>
														<img src="images/listing/img_02.jpg" class="w-100 border-10" alt="..." style="height: 200px; object-fit: cover;">
														<div class="img-slider-btn">
															03 <i class="fa-regular fa-image"></i>
															<a href="images/listing/img_large_04.jpg" class="d-block" data-fancybox="imgB" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_05.jpg" class="d-block" data-fancybox="imgB" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_06.jpg" class="d-block" data-fancybox="imgB" data-caption="Blueberry villa"></a>
														</div>
													</div>
												</div>
												<!-- /.img-gallery -->
												<div class="property-info mt-15">
													<div class="d-flex justify-content-between align-items-end">
														<div>
															<strong class="price fw-500 color-dark">4.200.000 ₺</strong>
															<div class="address m0 pt-5">Beşiktaş, İstanbul</div>
														</div>
														<a href="property-details.php" class="btn-four rounded-circle"><i class="bi bi-arrow-up-right"></i></a>
													</div>
												</div>
												<!-- /.property-info -->
											</div>
											<!-- /.listing-card-one -->
										</div>
										<div class="carousel-item">
											<div class="listing-card-one style-three border-10">
												<div class="img-gallery">
													<div class="position-relative border-10 overflow-hidden">
														<div class="tag bg-white text-dark fw-500 border-20">KİRALIK</div>
														<a href="#" class="fav-btn tran3s"><i class="fa-light fa-heart"></i></a>
														<img src="images/listing/img_03.jpg" class="w-100 border-10" alt="..." style="height: 200px; object-fit: cover;">
														<div class="img-slider-btn">
															03 <i class="fa-regular fa-image"></i>
															<a href="images/listing/img_large_04.jpg" class="d-block" data-fancybox="imgC" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_05.jpg" class="d-block" data-fancybox="imgC" data-caption="Blueberry villa"></a>
															<a href="images/listing/img_large_06.jpg" class="d-block" data-fancybox="imgC" data-caption="Blueberry villa"></a>
														</div>
													</div>
												</div>
												<!-- /.img-gallery -->
												<div class="property-info mt-15">
													<div class="d-flex justify-content-between align-items-end">
														<div>
															<strong class="price fw-500 color-dark">15.000 ₺/ay</strong>
															<div class="address m0 pt-5">Şişli, İstanbul</div>
														</div>
														<a href="property-details.php" class="btn-four rounded-circle"><i class="bi bi-arrow-up-right"></i></a>
													</div>
												</div>
												<!-- /.property-info -->
											</div>
											<!-- /.listing-card-one -->
										</div>
									</div>
								</div>
							</div>
							<!-- /.feature-listing -->
						</div>
						<!-- /.theme-sidebar-one -->
					</div>
				</div>
			</div>
		</div>
		<!-- /.listing-details-one -->

		<!--
		=====================================================
			Fancy Banner Two
		=====================================================
		-->
		<div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50" style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
							<h3 class="text-white m0">Hayalinizdeki <span>Evi<i class="fas fa-home ms-2"></i></span> Birlikte Bulalım.</h3>
						</div>
						<!-- /.title-one -->
					</div>
					<div class="col-lg-6">
						<div class="form-wrapper me-auto ms-auto me-lg-0">
							<form action="contact.php" method="post">
								<input type="email" name="email" placeholder="E-posta adresiniz" required>
								<button type="submit">Başlayın</button>
							</form>
							<div class="fs-16 mt-10 text-white">Zaten müşterimiz misiniz? <a href="contact.php">İletişim</a></div>
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
										<img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
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
									<li><a href="dashboard/dashboard.php" target="_blank">Üyelik</a></li>
									<li><a href="hakkimizda.php">Hakkımızda</a></li>
									<li><a href="blog.php">Blog</a></li>
									<li><a href="contact.php">İletişim</a></li>
									<li><a href="portfoy.php">Portföy</a></li>
									<li><a href="hesaplama-araclari.php">Hesaplamalar</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-3 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Yasal</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="contact.php">Şartlar & Koşullar</a></li>
									<li><a href="contact.php">Çerez Politikası</a></li>
									<li><a href="contact.php">Gizlilik Politikası</a></li>
									<li><a href="contact.php">S.S.S</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-2 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Hizmetlerimiz</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="portfoy.php">Ticari Gayrimenkul</a></li>
									<li><a href="portfoy.php">Konut Satışı</a></li>
									<li><a href="portfoy.php">Ev Kiralama</a></li>
									<li><a href="portfoy.php">Yatırım Danışmanlığı</a></li>
									<li><a href="portfoy.php">Villa Satışı</a></li>
									<li><a href="portfoy.php">Ofis Kiralama</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- /.bg-wrapper -->
				<div class="bottom-footer">
					<p class="m0 text-center fs-16">Copyright @2025 Gökhan Aydınlı Gayrimenkul.</p>
				</div>
			</div>
			<img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
		</div> <!-- /.footer-four -->

	<!-- OPTIMIZED: Essential scripts only - Loader removed for faster loading -->
	<script src="vendor/jquery.min.js" defer></script>
	<script>
		// Loader tamamen kaldırıldı - Sayfa artık daha hızlı yükleniyor
	</script>
	
	<!-- NON-CRITICAL: Load other scripts asynchronously -->
	<script src="vendor/bootstrap/js/bootstrap.bundle.min.js" defer></script>
	<script src="vendor/wow/wow.min.js" defer></script>
	<script src="vendor/slick/slick.min.js" defer></script>
	<script src="vendor/fancybox/fancybox.umd.js" defer></script>
	<script src="vendor/jquery.lazy.min.js" defer></script>
	<script src="vendor/jquery.counterup.min.js" defer></script>
	<script src="vendor/jquery.waypoints.min.js" defer></script>
	<script src="vendor/nice-select/jquery.nice-select.min.js" defer></script>
	<script src="vendor/validator.js" defer></script>
	<script src="vendor/isotope.pkgd.min.js" defer></script>
	<script src="js/theme.js" defer></script>
	
	<script>
		// Alert gösterme fonksiyonu
		function showAlert(message, type) {
			var alertDiv = document.createElement('div');
			alertDiv.className = 'alert-custom alert-' + type;
			alertDiv.innerHTML = message + '<button type="button" onclick="this.parentNode.remove()" style="float: right; background: none; border: none; font-size: 18px; cursor: pointer;">&times;</button>';
			document.body.appendChild(alertDiv);
			
			// 5 saniye sonra otomatik kapat
			setTimeout(function() {
				if (alertDiv.parentNode) {
					alertDiv.parentNode.removeChild(alertDiv);
				}
			}, 5000);
		}
	</script>
	
	<!-- FancyBox Initialize -->
	<script>
		// FancyBox başlatma
		Fancybox.bind("[data-fancybox]", {
			// Seçenekler
		});
		
		$(document).ready(function() {
			$('select').niceSelect();
			$('.lazy-img').lazy();
			new WOW().init();
			
			// Lazy loading için
			$('.lazy-img').each(function() {
				var src = $(this).data('src');
				if (src) {
					$(this).attr('src', src);
				}
			});
			
			// Bootstrap carousel initialize
			$('.carousel').each(function() {
				var carousel = new bootstrap.Carousel(this, {
					interval: false,
					ride: false
				});
			});
			
			// Resim yükleme hata kontrolü
			$('img').on('error', function() {
				if ($(this).attr('src') !== 'images/GA.jpg') {
					$(this).attr('src', 'images/GA.jpg');
				}
			});
			
			// Peşinat Otomatik Hesaplama - Garanti BBVA
			$('.mortgage-calculator input').eq(0).on('input', function() {
				calculateDownPaymentAndLoan($(this));
			});
			
			// Peşinat değiştirildiğinde kredi tutarını güncelle
			$('.mortgage-calculator input').eq(1).on('input', function() {
				const form = $(this).closest('form');
				const evFiyati = parseFloat(form.find('input').eq(0).val().replace(/[₺,]/g, '')) || 0;
				const pesinat = parseFloat($(this).val().replace(/[₺,]/g, '')) || 0;
				
				if (evFiyati > 0 && pesinat > 0) {
					const krediTutari = Math.round(evFiyati - pesinat);
					updateLoanAmountDisplay(form, krediTutari);
				}
			});
			
			function calculateDownPaymentAndLoan(priceInput) {
				const evFiyati = parseFloat(priceInput.val().replace(/[₺,]/g, '')) || 0;
				const form = priceInput.closest('form');
				
				if (evFiyati > 0) {
					const minPesinat = Math.round(evFiyati * 0.20); // %20 minimum
					const pesinatInput = form.find('input').eq(1);
					
					// Peşinat güncelle
					pesinatInput.val(minPesinat.toLocaleString('tr-TR') + ' ₺');
					pesinatInput.css({
						'border-color': '#004481',
						'background-color': '#f8f9fa'
					});
					
					// Kredi tutarını hesapla ve göster
					const krediTutari = Math.round(evFiyati - minPesinat);
					updateLoanAmountDisplay(form, krediTutari);
								// Bilgi mesajı göster
				const infoMessage = `
					<small style="color: #004481; font-size: 11px;">
						<i class="fas fa-info-circle me-1"></i> Garanti BBVA minimum peşinat (%20) otomatik hesaplandı
					</small>
				`;
				pesinatInput.next('.auto-calculation-info').remove();
				pesinatInput.after(`<div class="auto-calculation-info mt-1">${infoMessage}</div>`);
				}
			}
			
			function updateLoanAmountDisplay(form, krediTutari) {
				// Kredi tutarı bilgisini göster
				form.find('.loan-amount-display').remove();
				const loanInfo = `
					<div class="loan-amount-display mt-2 p-2" style="background: linear-gradient(135deg, #004481 0%, #0056b3 100%); border-radius: 8px; color: white;">
						<small style="font-weight: 500;"><i class="fas fa-calculator me-1"></i> Kredi Tutarı: <strong>${krediTutari.toLocaleString('tr-TR')} ₺</strong></small>
					</div>
				`;
				form.find('input').eq(1).after(loanInfo);
			}
			
			// Kredi Hesaplayıcı Fonksiyonu - Garanti BBVA
			$('.mortgage-calculator .btn-five').on('click', function(e) {
				e.preventDefault();
				
				const form = $(this).closest('form');
				const evFiyati = parseFloat(form.find('input').eq(0).val().replace(/[₺,]/g, '')) || 0;
				const pesinat = parseFloat(form.find('input').eq(1).val().replace(/[₺,]/g, '')) || 0;
				const faizOrani = parseFloat(form.find('input').eq(2).val().replace(/%/g, '')) || 0;
				const krediVadesi = parseInt(form.find('input').eq(3).val()) || 0;
				
				// Garanti BBVA koşullarına göre validasyon
				if (evFiyati <= 0) {
					alert('⚠️ Lütfen geçerli bir ev fiyatı girin.');
					return;
				}
				
				// Minimum peşinat kontrolü (%20)
				const minPesinat = evFiyati * 0.20;
				if (pesinat < minPesinat) {
					alert(`⚠️ Garanti BBVA için minimum peşinat %20 olmalıdır.\nMinimum peşinat: ${minPesinat.toLocaleString('tr-TR')} ₺`);
					return;
				}
				
				if (pesinat >= evFiyati) {
					alert('⚠️ Peşinat, ev fiyatından küçük olmalıdır.');
					return;
				}
				
				// Faiz oranı kontrolü (Garanti BBVA: 2.19% - 4.50% arası)
				if (faizOrani < 2.19 || faizOrani > 4.50) {
					alert('⚠️ Garanti BBVA faiz oranı %2.19 - %4.50 arasında olmalıdır.');
					return;
				}
				
				// Maksimum vade kontrolü (30 yıl)
				if (krediVadesi <= 0 || krediVadesi > 30) {
					alert('⚠️ Garanti BBVA maksimum kredi vadesi 30 yıldır.');
					return;
				}
				
				// Kredi hesaplama
				const kredMiktari = evFiyati - pesinat;
				const aylikFaizOrani = faizOrani / 100 / 12;
				const toplamAy = krediVadesi * 12;
				
				let aylikOdeme = 0;
				if (aylikFaizOrani > 0) {
					aylikOdeme = kredMiktari * (aylikFaizOrani * Math.pow(1 + aylikFaizOrani, toplamAy)) / (Math.pow(1 + aylikFaizOrani, toplamAy) - 1);
				} else {
					aylikOdeme = kredMiktari / toplamAy;
				}
				
				const toplamOdeme = aylikOdeme * toplamAy;
				const toplamFaiz = toplamOdeme - kredMiktari;
				
				// Gelir/Taksit Oranı hesabı (genel bankacılık kuralı %40)
				const gereklİGelir = aylikOdeme / 0.40;
				
				// Sonuçları göster - Garanti BBVA
				const sonuc = `
					<div class="kredi-sonuc" style="background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%); padding: 25px; border-radius: 15px; margin-top: 20px; border: 1px solid #004481; box-shadow: 0 4px 15px rgba(0,68,129,0.1);">
						<div class="d-flex align-items-center mb-20">
							<img src="https://www.garantibbva.com.tr/content/dam/bbva-garanti/common/logos/garanti-bbva-logo.svg" alt="Garanti BBVA" style="height: 30px; margin-right: 10px;">
							<h6 style="color: #004481; margin: 0; font-weight: bold;">Kredi Hesaplama Sonucu</h6>
						</div>
						
						<div style="background: white; padding: 20px; border-radius: 10px; margin-bottom: 15px; border-left: 4px solid #004481;">
							<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
								<span><strong>💰 Kredi Miktarı:</strong></span> 
								<span style="font-weight: bold; color: #004481;">${kredMiktari.toLocaleString('tr-TR')} ₺</span>
							</div>
							<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center; background: #f8f9fa; padding: 10px; border-radius: 8px;">
								<span><strong>📅 Aylık Ödeme:</strong></span> 
								<span style="color: #dc3545; font-weight: bold; font-size: 20px;">${aylikOdeme.toLocaleString('tr-TR', {maximumFractionDigits: 2})} ₺</span>
							</div>
							<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
								<span><strong>💳 Toplam Ödeme:</strong></span> 
								<span style="font-weight: bold;">${toplamOdeme.toLocaleString('tr-TR', {maximumFractionDigits: 2})} ₺</span>
							</div>
							<div style="margin-bottom: 15px; display: flex; justify-content: space-between; align-items: center;">
								<span><strong>📈 Toplam Faiz:</strong></span> 
								<span style="color: #28a745; font-weight: bold;">${toplamFaiz.toLocaleString('tr-TR', {maximumFractionDigits: 2})} ₺</span>
							</div>
							<div style="margin-bottom: 0; display: flex; justify-content: space-between; align-items: center; background: #fff3cd; padding: 10px; border-radius: 8px; border-left: 3px solid #ffc107;">
								<span><strong>💼 Tahmini Gerekli Gelir:</strong></span> 
								<span style="color: #856404; font-weight: bold;">${gereklİGelir.toLocaleString('tr-TR', {maximumFractionDigits: 2})} ₺</span>
							</div>
						</div>
						
						<div style="background: linear-gradient(135deg, #004481 0%, #0056b3 100%); padding: 15px; border-radius: 12px; margin-bottom: 10px; color: white;">
							<div class="mb-2">
								<small style="font-weight: 600;"><i class="fas fa-info-circle me-1"></i>Garanti BBVA Konut Kredisi Koşulları</small>
							</div>
							<small style="color: #e3f2fd; line-height: 1.5;">
								• <strong>Minimum peşinat:</strong> %20<br>
								• <strong>Maksimum vade:</strong> 30 yıl<br>
								• <strong>Faiz oranı:</strong> %2.19 - %4.50 (müşteri profiline göre)<br>
								• <strong>Gelir/Taksit oranı:</strong> maksimum %40
							</small>
						</div>
						
						<div style="background: linear-gradient(135deg, #28a745 0%, #20c997 100%); padding: 12px; border-radius: 8px; color: white; text-align: center;">
							<small style="font-weight: 600;">
								<i class="fas fa-phone me-1"></i> <strong>Başvuru için:</strong> 0850 222 0 800 
								<span style="margin: 0 10px;">|</span> 
								<i class="fas fa-globe me-1"></i> <strong>Online:</strong> garantibbva.com.tr
							</small>
						</div>
					</div>
				`;
				
				// Önceki sonuçları temizle ve yeni sonucu ekle
				form.find('.kredi-sonuc').remove();
				form.append(sonuc);
				
				// Sonuca yumuşak geçiş efekti
				form.find('.kredi-sonuc').hide().fadeIn(600);
			});
			
			// Input formatlaması için olay dinleyicileri
			$('.mortgage-calculator input[type="tel"]').on('input', function() {
				let value = $(this).val().replace(/[^\d]/g, '');
				if ($(this).closest('.input-box-three').find('.label').text().includes('Faiz Oranı')) {
					// Faiz oranı için farklı format
					if (value) {
						value = (parseFloat(value) / 100).toFixed(2) + '%';
						if (value === '0.00%') value = '';
					}
				} else if (!$(this).closest('.input-box-three').find('.label').text().includes('Vadesi')) {
					// Para formatı
					if (value) {
						value = parseInt(value).toLocaleString('tr-TR') + ' ₺';
					}
				}
				$(this).val(value);
			});
		});
	</script>
	</div> <!-- /.main-page-wrapper -->

</body>
</html>
