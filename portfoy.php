<?php
// Portfolio sayfası porfoy.html'e yönlendiriliyor
header("Location: porfoy.html");
exit;
?>

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Portföy herkese açık - sadece kendi ilanlarını görmek isteyen kullanıcılar için user_id kontrolü
$user_id = $_SESSION['user_id'] ?? null;

// Filtreler
$where = "1=1"; // Herkese açık tüm ilanları göster
$params = [];
$types = "";

// Ana sayfadan gelen arama parametreleri
$search_location = $_GET['location'] ?? '';
$search_type = $_GET['type'] ?? '';

// Eğer kullanıcı giriş yapmışsa ve sadece kendi ilanlarını görmek istiyorsa
if ($user_id && isset($_GET['my_listings'])) {
    $where .= " AND user_id = ?";
    $params[] = $user_id;
    $types .= "i";
}

if (!empty($_GET['keyword'])) {
    $where .= " AND (title LIKE ? OR address LIKE ? OR description LIKE ?)";
    $params[] = "%" . $_GET['keyword'] . "%";
    $params[] = "%" . $_GET['keyword'] . "%";
    $params[] = "%" . $_GET['keyword'] . "%";
    $types .= "sss";
}

// Ana sayfadan gelen lokasyon araması
if (!empty($search_location)) {
    $where .= " AND (address LIKE ? OR title LIKE ? OR description LIKE ?)";
    $params[] = "%" . $search_location . "%";
    $params[] = "%" . $search_location . "%";
    $params[] = "%" . $search_location . "%";
    $types .= "sss";
}

// Eski location parametresi de desteklensin
if (!empty($_GET['location']) && empty($search_location)) {
    $where .= " AND address LIKE ?";
    $params[] = "%" . $_GET['location'] . "%";
    $types .= "s";
}

// Ana sayfadan gelen tip filtresi (rent/sale)
if (!empty($search_type)) {
    $where .= " AND listing_type = ?";
    $params[] = $search_type;
    $types .= "s";
}

if (!empty($_GET['search_type']) && empty($search_type)) {
    $where .= " AND listing_type = ?";
    $params[] = $_GET['search_type'];
    $types .= "s";
}

if (!empty($_GET['min_price'])) {
    $where .= " AND price >= ?";
    $params[] = $_GET['min_price'];
    $types .= "d";
}

if (!empty($_GET['max_price'])) {
    $where .= " AND price <= ?";
    $params[] = $_GET['max_price'];
    $types .= "d";
}

if (!empty($_GET['bedroom'])) {
    $where .= " AND (bedrooms >= ? OR rooms >= ?)";
    $params[] = $_GET['bedroom'];
    $params[] = $_GET['bedroom'];
    $types .= "ss";
}

if (!empty($_GET['bathroom'])) {
    $where .= " AND bathrooms >= ?";
    $params[] = $_GET['bathroom'];
    $types .= "s";
}

if (!empty($_GET['min_size'])) {
    $where .= " AND area >= ?";
    $params[] = $_GET['min_size'];
    $types .= "i";
}

if (!empty($_GET['max_size'])) {
    $where .= " AND area <= ?";
    $params[] = $_GET['max_size'];
    $types .= "i";
}

// Sıralama
$orderBy = "created_at DESC";
if (!empty($_GET['sort'])) {
    switch ($_GET['sort']) {
        case 'price_low':
            $orderBy = "price ASC";
            break;
        case 'price_high':
            $orderBy = "price DESC";
            break;
        case 'size':
            $orderBy = "area DESC";
            break;
        case 'newest':
        default:
            $orderBy = "created_at DESC";
            break;
    }
}

// Toplam ilan sayısını hesapla
$count_sql = "SELECT COUNT(*) as total FROM properties WHERE $where";
$count_stmt = $conn->prepare($count_sql);

if (!empty($params) && !empty($types)) {
    $count_stmt->bind_param($types, ...$params);
}

$count_stmt->execute();
$count_result = $count_stmt->get_result();
$total_count = $count_result->fetch_assoc()['total'];

$sql = "SELECT * FROM properties WHERE $where ORDER BY $orderBy";
$stmt = $conn->prepare($sql);

// Eğer parametre varsa bind_param kullan
if (!empty($params) && !empty($types)) {
    $stmt->bind_param($types, ...$params);
}

$stmt->execute();
$result = $stmt->get_result();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php 
    if (!empty($search_location)) {
        echo $search_location . ' İlanları';
    } else if ($user_id) {
        echo 'Portföyüm';
    } else {
        echo 'İlanlarımız';
    }
    ?> - Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" type="text/css" href="css/listing-custom.css" media="all">
    <!-- Akıllı resim sistemi -->
    <link rel="stylesheet" type="text/css" href="css/smart-image-system.css" media="all">
    <style>
        /* İlan Kart Resimleri için Sabit Boyut */
        .img-gallery {
            height: 280px;
            overflow: hidden;
            transition: all 0.3s ease;
        }
        
        /* Resim üzerine gelindiğinde hover efekti */
        .img-gallery:hover {
            transform: scale(1.05);
            box-shadow: 0 8px 25px rgba(0,0,0,0.15);
            opacity: 0.9;
        }
        
        /* Resim tıklanabilir olduğunu belirtmek için cursor */
        .img-gallery[onclick] {
            cursor: pointer;
        }
        
        .img-gallery .carousel-inner,
        .img-gallery .carousel-item {
            height: 100%;
        }
        
        .img-gallery .carousel-item img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            object-position: center top;
            min-height: 280px;
        }
        
        .img-gallery a > img,
        .single-image-container img {
            height: 100%;
            width: 100%;
            object-fit: cover;
            object-position: center top;
            min-height: 280px;
        }
        
        .single-image-container {
            position: relative;
            height: 100%;
        }
        
        /* Dikey resimler için özel ayar */
        .img-gallery img[style*="height"], 
        .img-gallery img {
            max-width: 100%;
            max-height: 100%;
        }
        
        /* Resim aspect ratio koruması */
        .img-gallery .carousel-item,
        .single-image-container {
            display: flex;
            align-items: center;
            justify-content: center;
            background: #f8f9fa;
        }
        
        /* Carousel resimlerini force cover */
        .carousel-item img {
            object-fit: cover !important;
            object-position: center top !important;
            width: 100% !important;
            height: 100% !important;
        }
        
        /* Dikey resimler için basit çözüm */
        .img-gallery img {
            object-fit: cover;
            object-position: center top;
        }
        
        /* Carousel Indicator'ları */
        .carousel-indicators {
            bottom: 10px;
            margin-bottom: 0;
            display: flex;
            justify-content: center;
            flex-wrap: wrap;
            max-height: 20px;
            overflow: hidden;
        }
        
        .carousel-indicators button {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            margin: 0 2px;
            background-color: rgba(255,255,255,0.7);
            border: none;
            transition: all 0.3s ease;
        }
        
        .carousel-indicators button.active {
            background-color: white;
            transform: scale(1.2);
        }
        
        /* İlan Kartları */
        .listing-card-one {
            transition: transform 0.3s ease, box-shadow 0.3s ease;
            border: 1px solid #eee;
        }
        
        .listing-card-one:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        
        /* Fiyat Gösterimi */
        .price {
            color: #ff6b35 !important;
            font-size: 18px;
        }
        
        /* Tag Stilleri */
        .tag {
            background: linear-gradient(45deg, #ff6b35, #f7931e);
            color: white;
            font-size: 12px;
            font-weight: 600;
            padding: 5px 10px;
            position: absolute;
            top: 15px;
            left: 15px;
            z-index: 2;
        }
        
        /* Resim Sayısı Göstergesi */
        .image-count-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(0,0,0,0.7);
            color: white;
            font-size: 11px;
            padding: 4px 8px;
            border-radius: 12px;
            z-index: 2;
            backdrop-filter: blur(5px);
        }
        
        .image-count-badge i {
            margin-right: 3px;
        }
        
        /* Responsive İyileştirmeler */
        @media (max-width: 768px) {
            .img-gallery {
                height: 220px;
            }
            
            .col-md-6 {
                margin-bottom: 30px;
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
    <header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
        <div class="inner-content gap-one">
            <div class="top-header position-relative">
                <div class="d-flex align-items-center justify-content-between">
                    <div class="logo order-lg-0">
                        <a href="index.php" class="d-flex align-items-center">
                            <img src="images/logoSiyah.png" alt="">
                        </a>
                    </div>
                    <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                        <ul class="d-flex align-items-center style-none">
                            <?php if ($user_id): ?>
                                <li class="dropdown">
                                    <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                        <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($_SESSION['user_name'] ?? 'Kullanıcı'); ?></span>
                                    </a>
                                    <ul class="dropdown-menu">
                                        <?php if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                                            <li><a class="dropdown-item" href="dashboard/dashboard-admin.php">Admin Panel</a></li>
                                        <?php else: ?>
                                            <li><a class="dropdown-item" href="dashboard/dashboard-user.php">Kullanıcı Paneli</a></li>
                                        <?php endif; ?>
                                        <li><hr class="dropdown-divider"></li>
                                        <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                                    </ul>
                                </li>
                            <?php else: ?>
                                <li><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one"><i class="fa-regular fa-lock"></i> <span>Giriş</span></a></li>
                                <li class="d-none d-md-inline-block ms-3">
                                    <a href="register.php" class="btn-two"><span>Üye Ol</span> <i class="fa-thin fa-arrow-up-right"></i></a>
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

                                    <li class="nav-item dashboard-menu">
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
									<li class="d-md-none ps-2 pe-2 mt-20">
										<a href="dashboard/add-property.html" class="btn-two w-100" target="_blank"><span>Add Listing</span> <i class="fa-thin fa-arrow-up-right"></i></a>
									</li>
								</ul>
							</div>
                        
                        </div>
                    </nav>
                </div>
            </div>
        </div>
    </header>
    <!-- İç Banner -->
    <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
        <div class="container">
            <h3 class="mb-35 xl-mb-20 pt-15"><?php 
            if (!empty($search_location)) {
                echo $search_location . ' Bölgesindeki İlanlar';
                if (!empty($search_type)) {
                    echo ' (' . ($search_type == 'rent' ? 'Kiralık' : 'Satılık') . ')';
                }
            } else if ($user_id) {
                echo 'Portföyüm';
            } else {
                echo 'İlanlarımız';
            }
            ?></h3>
            <?php if (!empty($search_location)): ?>
            <p class="text-lg">📍 <strong><?= htmlspecialchars($search_location) ?></strong> için <?= $total_count ?> ilan bulundu</p>
            <?php endif; ?>
            <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                <li><a href="index.php">Anasayfa</a></li>
                <li>/</li>
                <li><?= $user_id ? 'Portföy' : 'İlanlar' ?></li>
            </ul>
        </div>
        <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
    </div>
    <!-- Portföy ve Filtre Alanı -->
    <div class="property-listing-six bg-pink-two pt-110 md-pt-80 pb-150 xl-pb-120 mt-150 xl-mt-120">
        <div class="container container-large">
            <div class="row">
                <div class="col-lg-8">
                    <div class="ps-xxl-5">
                        <div class="listing-header-filter d-sm-flex justify-content-between align-items-center mb-40 lg-mb-30">
                            <div>Toplam <span class="color-dark fw-500"><?= $result->num_rows ?></span> ilan bulundu</div>
                            <div class="d-flex align-items-center xs-mt-20">
                                <div class="short-filter d-flex align-items-center">
                                    <div class="fs-16 me-2">Sırala:</div>
                                    <select class="nice-select" onchange="window.location.href=this.value;">
                                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'newest'])) ?>" <?= ($_GET['sort'] ?? '') == 'newest' ? 'selected' : '' ?>>En Yeni</option>
                                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_low'])) ?>" <?= ($_GET['sort'] ?? '') == 'price_low' ? 'selected' : '' ?>>Fiyat (Düşük)</option>
                                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'price_high'])) ?>" <?= ($_GET['sort'] ?? '') == 'price_high' ? 'selected' : '' ?>>Fiyat (Yüksek)</option>
                                        <option value="?<?= http_build_query(array_merge($_GET, ['sort' => 'size'])) ?>" <?= ($_GET['sort'] ?? '') == 'size' ? 'selected' : '' ?>>Büyüklük</option>
                                    </select>
                                </div>
                            </div>
                        </div>
                        <!-- /.listing-header-filter -->

                        <!-- İlanlar Liste Layout -->
                        <?php if ($result->num_rows > 0): ?>
                            <div class="listing-container">
                                <?php $index = 0; while($row = $result->fetch_assoc()): $index++; ?>
                                    <div class="listing-card-seven border-20 p-20 mb-50 wow fadeInUp" <?= $index % 2 == 0 ? 'data-wow-delay="0.1s"' : '' ?>>
                                        <div class="d-flex flex-wrap layout-one">                            <div class="img-gallery position-relative z-1 border-20 overflow-hidden" style="cursor: pointer;" onclick="window.location.href='property-details.php?id=<?= $row['id'] ?>'">
                                <?php 
                                // CLOUDFLARE IMAGES SUPPORT - admin-properties.php ile aynı sistem
                                $first_image = '';
                                $image_url = 'images/listing/img_20.jpg'; // default fallback
                                
                                // 1. CLOUDFLARE IMAGES ÖNCELİK (YENİ SISTEM)
                                if (!empty($row['use_cloudflare']) && !empty($row['cloudflare_images'])) {
                                    $cloudflare_decoded = json_decode($row['cloudflare_images'], true);
                                    if (is_array($cloudflare_decoded) && !empty($cloudflare_decoded)) {
                                        $first_cloudflare_id = $cloudflare_decoded[0];
                                        $account_hash = 'prdw3ANMyocSBJD-Do1EeQ';
                                        $image_url = "https://imagedelivery.net/{$account_hash}/{$first_cloudflare_id}/public";
                                    }
                                }
                                // 2. MAIN_IMAGE KONTROLÜ (YENİ CLOUDFLARE SISTEMI)
                                elseif (!empty($row['main_image']) && strpos($row['main_image'], 'https://imagedelivery.net/') === 0) {
                                    $image_url = $row['main_image'];
                                }
                                // 3. IMAGES ALANINDA CLOUDFLARE URL'LERİ
                                elseif (!empty($row['images']) && $row['images'] !== null) {
                                    if (strpos($row['images'], '[') === 0) {
                                        // JSON format
                                        $images = json_decode($row['images'], true);
                                        if (is_array($images) && !empty($images)) {
                                            $first_image = trim($images[0]);
                                        }
                                    } else {
                                        // Comma separated format
                                        $images = explode(',', $row['images']);
                                        if (!empty($images)) {
                                            $first_image = trim($images[0]);
                                        }
                                    }
                                    
                                    // Resim yolunu düzelt
                                    if (!empty($first_image)) {
                                        if (strpos($first_image, 'https://imagedelivery.net/') === 0) {
                                            // Already Cloudflare URL
                                            $image_url = $first_image;
                                        } else {
                                            // Local image - show-image.php kullan
                                            $possible_paths = [
                                                $first_image,
                                                'uploads/' . ltrim($first_image, '/'),
                                                'uploads/properties/' . $first_image,
                                                'uploads/properties/' . basename($first_image),
                                                ltrim($first_image, './')
                                            ];
                                            
                                            $correct_image = '';
                                            foreach ($possible_paths as $path) {
                                                if (file_exists($path)) {
                                                    $correct_image = $path;
                                                    break;
                                                }
                                            }
                                            
                                            if (!empty($correct_image)) {
                                                $image_url = $correct_image;
                                            }
                                        }
                                    }
                                }
                                ?>
                                
                                <img src="<?= htmlspecialchars($image_url) ?>" 
                                     alt="<?= htmlspecialchars($row['title']) ?>" 
                                     style="width: 100%; height: 280px; object-fit: cover; display: block;"
                                     onerror="this.src='images/listing/img_20.jpg'">
                                <div class="tag border-20 <?= strtolower($row['listing_type'] ?? '') == 'sale' ? 'sale' : '' ?>"><?= strtoupper($row['listing_type'] ?? 'İLAN') ?></div>                                <div class="img-slider-btn" onclick="event.stopPropagation();">
                                    <?php 
                                    // GALERI RESİMLERİ - Admin-properties.php ile uyumlu
                                    $validImages = [];
                                    
                                    // 1. Cloudflare images varsa onları kullan
                                    if (!empty($row['use_cloudflare']) && !empty($row['cloudflare_images'])) {
                                        $cloudflare_decoded = json_decode($row['cloudflare_images'], true);
                                        if (is_array($cloudflare_decoded)) {
                                            foreach ($cloudflare_decoded as $cf_id) {
                                                $account_hash = 'prdw3ANMyocSBJD-Do1EeQ';
                                                $validImages[] = "https://imagedelivery.net/{$account_hash}/{$cf_id}/public";
                                            }
                                        }
                                    }
                                    // 2. Normal images alanından
                                    elseif (!empty($row['images'])) {
                                        if (strpos($row['images'], '[') === 0) {
                                            // JSON format
                                            $images_json = json_decode($row['images'], true);
                                            if (is_array($images_json)) {
                                                foreach ($images_json as $img) {
                                                    if (!empty($img)) {
                                                        if (strpos($img, 'https://imagedelivery.net/') === 0) {
                                                            $validImages[] = $img;
                                                        } else {
                                                            // Local file check
                                                            $possible_paths = [
                                                                $img,
                                                                'uploads/' . ltrim($img, '/'),
                                                                'uploads/properties/' . $img,
                                                                'uploads/properties/' . basename($img)
                                                            ];
                                                            foreach ($possible_paths as $path) {
                                                                if (file_exists($path)) {
                                                                    $validImages[] = $path;
                                                                    break;
                                                                }
                                                            }
                                                        }
                                                    }
                                                }
                                            }
                                        } else {
                                            // Comma separated
                                            $images = array_map('trim', explode(',', $row['images']));
                                            foreach ($images as $img) {
                                                if (!empty($img)) {
                                                    if (strpos($img, 'https://imagedelivery.net/') === 0) {
                                                        $validImages[] = $img;
                                                    }
                                                }
                                            }
                                        }
                                    }
                                    
                                    // Eğer hiç resim yoksa ana resmi ekle
                                    if (empty($validImages)) {
                                        $validImages = [$image_url];
                                    }
                                    ?>
                                    
                                    <span title="Fotoğraf galerisini görüntüle"><?= count($validImages) ?> <i class="fa-regular fa-image"></i></span>
                                                    <?php foreach($validImages as $i => $img): ?>
                                                        <a href="<?= htmlspecialchars($img) ?>" class="d-block" data-fancybox="img<?= $row['id'] ?>" data-caption="<?= htmlspecialchars($row['title']) ?>"></a>
                                                    <?php endforeach; ?>
                                                </div>
                                            </div>
                                            <!-- /.img-gallery -->
                                            <div class="property-info">
                                                <a href="property-details.php?id=<?= $row['id'] ?>" class="title tran3s mb-15"><?= htmlspecialchars($row['title']) ?></a>
                                                <div class="address"><?= htmlspecialchars($row['address']) ?></div>
                                                <div class="feature mt-30 mb-30 pt-30 pb-5">
                                                    <ul class="style-none d-flex flex-wrap align-items-center justify-content-between">
                                                        <li><strong><?= isset($row['area']) ? (int)$row['area'] : '0' ?></strong> m²</li>
                                                        <li><strong><?= isset($row['bedrooms']) ? (int)$row['bedrooms'] : (isset($row['rooms']) ? (int)$row['rooms'] : '0') ?></strong> oda</li>
                                                        <li><strong><?= isset($row['bathrooms']) ? (int)$row['bathrooms'] : '0' ?></strong> banyo</li>
                                                        <li><strong>01</strong> Mutfak</li>
                                                    </ul>
                                                </div>
                                                <div class="pl-footer d-flex flex-wrap align-items-center justify-content-between">
                                                    <strong class="price fw-500 color-dark me-auto">₺<?= number_format($row['price'], 0, ',', '.') ?></strong>
                                                    <ul class="style-none d-flex action-icons me-4">
                                                        <li><a href="#" class="fav-btn"><i class="fa-light fa-heart"></i></a></li>
                                                        <li><a href="#"><i class="fa-light fa-bookmark"></i></a></li>
                                                        <li><a href="#"><i class="fa-light fa-circle-plus"></i></a></li>
                                                    </ul>
                                                    <a href="property-details.php?id=<?= $row['id'] ?>" class="btn-four rounded-circle"><i class="bi bi-arrow-up-right"></i></a>
                                                </div>
                                            </div>
                                            <!-- /.property-info -->
                                        </div>
                                    </div>
                                    <!-- /.listing-card-seven -->
                                <?php endwhile; ?>
                            </div>
                        <?php else: ?>
                            <div class="text-center py-5">
                                <img src="images/icon/icon_search.svg" alt="" class="mb-3" style="width: 80px; opacity: 0.5;">
                                <h5 class="color-dark">Ilan Bulunamadı</h5>
                                <p class="text-muted">Aradığınız kriterlere uygun ilan bulunamadı. Filtreleri değiştirerek tekrar deneyin.</p>
                                <a href="portfoy.php" class="btn-one mt-3">Tüm İlanları Göster</a>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
                <div class="col-lg-4 order-lg-first">
                    <div class="advance-search-panel dot-bg md-mt-80">
                        <div class="main-bg">
                            <form method="get">
                                <div class="row gx-lg-5">
                                    <?php if ($user_id): ?>
                                    <div class="col-12">
                                        <div class="input-box-one mb-35">
                                            <div class="label">İlan Filtresi</div>
                                            <select class="nice-select fw-normal" name="my_listings">
                                                <option value="">Tüm İlanlar</option>
                                                <option value="1" <?= isset($_GET['my_listings']) ? 'selected' : '' ?>>Sadece Benim İlanlarım</option>
                                            </select>
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <?php endif; ?>
                                    <div class="col-12">
                                        <div class="input-box-one mb-35">
                                            <div class="label">Arama Türü</div>
                                            <select class="nice-select fw-normal" name="search_type">
                                                <option value="">Tümü</option>
                                                <option value="sale" <?= ($_GET['search_type'] ?? '') == 'sale' ? 'selected' : '' ?>>Satılık</option>
                                                <option value="rent" <?= ($_GET['search_type'] ?? '') == 'rent' ? 'selected' : '' ?>>Kiralık</option>
                                            </select>
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <div class="col-12">
                                        <div class="input-box-one mb-35">
                                            <div class="label">Anahtar Kelime</div>
                                            <input type="text" name="keyword" class="type-input" value="<?= htmlspecialchars($_GET['keyword'] ?? '') ?>" placeholder="Başlık, adres, açıklama...">
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <div class="col-12">
                                        <div class="input-box-one mb-50">
                                            <div class="label">Konum</div>
                                            <input type="text" name="location" class="type-input" value="<?= htmlspecialchars($_GET['location'] ?? '') ?>" placeholder="İl, ilçe, mahalle...">
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="input-box-one mb-40">
                                            <div class="label">Oda Sayısı</div>
                                            <select class="nice-select fw-normal" name="bedroom">
                                                <option value="">Hepsi</option>
                                                <option value="1" <?= ($_GET['bedroom'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                                                <option value="2" <?= ($_GET['bedroom'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                                                <option value="3" <?= ($_GET['bedroom'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                                                <option value="4" <?= ($_GET['bedroom'] ?? '') == '4' ? 'selected' : '' ?>>4+</option>
                                                <option value="5" <?= ($_GET['bedroom'] ?? '') == '5' ? 'selected' : '' ?>>5+</option>
                                            </select>
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <div class="col-sm-6">
                                        <div class="input-box-one mb-40">
                                            <div class="label">Banyo</div>
                                            <select class="nice-select fw-normal" name="bathroom">
                                                <option value="">Hepsi</option>
                                                <option value="1" <?= ($_GET['bathroom'] ?? '') == '1' ? 'selected' : '' ?>>1+</option>
                                                <option value="2" <?= ($_GET['bathroom'] ?? '') == '2' ? 'selected' : '' ?>>2+</option>
                                                <option value="3" <?= ($_GET['bathroom'] ?? '') == '3' ? 'selected' : '' ?>>3+</option>
                                                <option value="4" <?= ($_GET['bathroom'] ?? '') == '4' ? 'selected' : '' ?>>4+</option>
                                            </select>
                                        </div>
                                        <!-- /.input-box-one -->
                                    </div>
                                    <div class="col-12">
                                        <h6 class="block-title fw-bold mb-30">Özellikler</h6>
                                        <ul class="style-none d-flex flex-wrap justify-content-between filter-input">
                                            <li>
                                                <input type="checkbox" name="features[]" value="parking" id="parking" <?= in_array('parking', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="parking">Otopark</label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="features[]" value="garden" id="garden" <?= in_array('garden', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="garden">Bahçe</label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="features[]" value="pool" id="pool" <?= in_array('pool', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="pool">Havuz</label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="features[]" value="elevator" id="elevator" <?= in_array('elevator', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="elevator">Asansör</label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="features[]" value="security" id="security" <?= in_array('security', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="security">Güvenlik</label>
                                            </li>
                                            <li>
                                                <input type="checkbox" name="features[]" value="furnished" id="furnished" <?= in_array('furnished', $_GET['features'] ?? []) ? 'checked' : '' ?>>
                                                <label for="furnished">Eşyalı</label>
                                            </li>
                                        </ul>
                                    </div>
                                    <div class="col-12">
                                        <h6 class="block-title fw-bold mt-25 mb-15">Fiyat Aralığı</h6>
                                        <div class="d-flex align-items-center sqf-ranger">
                                            <input type="number" name="min_price" placeholder="Min" value="<?= htmlspecialchars($_GET['min_price'] ?? '') ?>">
                                            <div class="divider">-</div>
                                            <input type="number" name="max_price" placeholder="Max" value="<?= htmlspecialchars($_GET['max_price'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <h6 class="block-title fw-bold mt-45 mb-20">Alan (m²)</h6>
                                        <div class="d-flex align-items-center sqf-ranger">
                                            <input type="number" name="min_size" placeholder="Min" value="<?= htmlspecialchars($_GET['min_size'] ?? '') ?>">
                                            <div class="divider">-</div>
                                            <input type="number" name="max_size" placeholder="Max" value="<?= htmlspecialchars($_GET['max_size'] ?? '') ?>">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="fw-500 text-uppercase tran3s apply-search w-100 mt-40 mb-25">
                                            <i class="fa-light fa-magnifying-glass"></i>
                                            <span>Filtrele</span>
                                        </button>
                                    </div>
                                    <div class="col-12">
                                        <div class="d-flex justify-content-between form-widget">
                                            <a href="portfoy.php" class="tran3s">
                                                <i class="fa-regular fa-arrows-rotate"></i>
                                                <span>Filtreyi Temizle</span>
                                            </a>
                                            <a href="#" class="tran3s" onclick="alert('Bu özellik yakında eklenecek!')">
                                                <i class="fa-regular fa-star"></i>
                                                <span>Aramayı Kaydet</span>
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.main-bg -->
                    </div>
                    <!-- /.advance-search-panel -->
                </div>
            </div>
        </div>
    </div>
    <!-- Footer -->
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
                                <li><a href="dashboard/dashboard.php" target="_blank">Üyelik</a></li>
                                <li><a href="hakkimizda.php">Hakkımızda</a></li>
                                <li><a href="blog.php">Blog</a></li>
                                <li><a href="contact.php">İletişim</a></li>
                                <li><a href="portfoy.php">Portföy</a></li>
                                <li><a href="dashboard/dashboard.php" target="_blank">Panel</a></li>
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
                <p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
            </div>
        </div>
        <img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
    </div> <!-- /.footer-four -->

    <?php if (!$user_id): ?>
    <!-- Login Modal -->
    <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
        <div class="modal-dialog modal-fullscreen modal-dialog-centered">
            <div class="container">
                <div class="user-data-form modal-content">
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                    <div class="form-wrapper m-auto">
                        <ul class="nav nav-tabs w-100" role="tablist">
                            <li class="nav-item" role="presentation">
                                <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş Yap</button>
                            </li>
                            <li class="nav-item" role="presentation">
                                <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Üye Ol</button>
                            </li>
                        </ul>
                        <div class="tab-content mt-30">
                            <div class="tab-pane show active" role="tabpanel" id="fc1">
                                <div class="text-center mb-20">
                                    <h2>Hoş Geldiniz!</h2>
                                    <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="register.php">Üye Olun</a></p>
                                </div>
                                <form action="login.php" method="post">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-25">
                                                <label>E-posta veya Kullanıcı Adı*</label>
                                                <input type="text" name="email" placeholder="E-posta veya kullanıcı adı" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-20">
                                                <label>Şifre*</label>
                                                <input type="password" name="password" placeholder="Şifrenizi girin" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                <div>
                                                    <input type="checkbox" id="remember">
                                                    <label for="remember">Beni Hatırla</label>
                                                </div>
                                                <a href="forgot-password.php">Şifremi Unuttum?</a>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">Giriş Yap</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                            <div class="tab-pane" role="tabpanel" id="fc2">
                                <div class="text-center mb-20">
                                    <h2>Üye Ol</h2>
                                    <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="$('.nav-link').first().click()">Giriş Yapın</a></p>
                                </div>
                                <form action="register.php" method="post">
                                    <div class="row">
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-25">
                                                <label>Ad Soyad*</label>
                                                <input type="text" name="name" placeholder="Ad Soyad" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-25">
                                                <label>E-posta*</label>
                                                <input type="email" name="email" placeholder="E-posta adresi" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="input-group-meta position-relative mb-20">
                                                <label>Şifre*</label>
                                                <input type="password" name="password" placeholder="Şifre oluşturun" required>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                <div>
                                                    <input type="checkbox" id="terms" required>
                                                    <label for="terms">Şartları ve koşulları kabul ediyorum</label>
                                                </div>
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">Üye Ol</button>
                                        </div>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <?php endif; ?>

</div>
<script src="vendor/jquery.min.js"></script>
<script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
<script src="vendor/wow/wow.min.js"></script>
<script src="vendor/slick/slick.min.js"></script>
<script src="vendor/fancybox/fancybox.umd.js"></script>
<script src="vendor/jquery.lazy.min.js"></script>
<script src="vendor/jquery.counterup.min.js"></script>
<script src="vendor/jquery.waypoints.min.js"></script>
<script src="vendor/nice-select/jquery.nice-select.min.js"></script>
<script src="vendor/validator.js"></script>
<script src="vendor/isotope.pkgd.min.js"></script>
<script src="js/theme.js"></script>

<script>
    $(document).ready(function() {
        $('select').niceSelect();
        $('.lazy-img').lazy();
        new WOW().init();
        
        // Basit carousel
        $('.carousel').carousel({
            interval: false
        });
        
        // Resim yükleme hata kontrolü
        $('img').on('error', function() {
            if ($(this).attr('src') !== 'images/GA.jpg') {
                $(this).attr('src', 'images/GA.jpg');
            }
        });
        
        // Dikey resimler için özel positioning
        $('.carousel-item img, .single-image-container img').on('load', function() {
            var img = this;
            var $img = $(img);
            
            // Resim boyutlarını kontrol et
            if (img.naturalHeight > img.naturalWidth) {
                // Dikey resim - üst kısmı göster
                $img.css('object-position', 'center top');
            } else {
                // Yatay resim - merkezi göster
                $img.css('object-position', 'center center');
            }
        });
        
        // Carousel otomatik kayma durdurmak için
        $('.carousel').on('mouseenter', function() {
            $(this).carousel('pause');
        }).on('mouseleave', function() {
            $(this).carousel('cycle');
        });
        
        // Touch için carousel kontrolleri
        if ('ontouchstart' in window) {
            $('.carousel-control-prev, .carousel-control-next').css('opacity', '1');
        }
        
        // İlan kartlarına hover efekti ekle
        $('.listing-card-seven').hover(
            function() {
                $(this).addClass('hover-active');
            },
            function() {
                $(this).removeClass('hover-active');
            }
        );
        
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
        
        // Favori butonları için
        $('.fav-btn').on('click', function(e) {
            e.preventDefault();
            $(this).toggleClass('active');
            var icon = $(this).find('i');
            if (icon.hasClass('fa-light')) {
                icon.removeClass('fa-light').addClass('fa-solid');
            } else {
                icon.removeClass('fa-solid').addClass('fa-light');
            }
        });
        
        // Sayfa yüklendiğinde mevcut resimler için positioning uygula
        setTimeout(function() {
            $('.carousel-item img, .single-image-container img').each(function() {
                var img = this;
                var $img = $(img);
                
                if (img.complete && img.naturalHeight && img.naturalWidth) {
                    if (img.naturalHeight > img.naturalWidth) {
                        // Dikey resim - üst kısmı göster
                        $img.css('object-position', 'center top');
                    } else {
                        // Yatay resim - merkezi göster
                        $img.css('object-position', 'center center');
                    }
                }
            });
        }, 500);
    });
</script>
</body>
</html>