<?php
session_start();

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

include '../db.php';

// Session kontrolü ekle
if (!isset($_SESSION['user_id'])) {
    header("Location: login.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$is_admin = ($_SESSION['role'] ?? '') === 'admin';

// Arama ve filtreleme parametreleri
$search = $_GET['search'] ?? '';
$type = $_GET['type'] ?? '';
$category = $_GET['category'] ?? '';
$city = $_GET['city'] ?? '';
$min_price = $_GET['min_price'] ?? '';
$max_price = $_GET['max_price'] ?? '';
$page = max(1, intval($_GET['page'] ?? 1));
$per_page = 12;
$offset = ($page - 1) * $per_page;

// Base query
$where_conditions = ["p.status IN ('active', 'approved')"];
$params = [];
$param_types = "";

// Search conditions
if (!empty($search)) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $search_param = "%$search%";
    $params = array_merge($params, [$search_param, $search_param, $search_param]);
    $param_types .= "sss";
}

if (!empty($type)) {
    $where_conditions[] = "p.type = ?";
    $params[] = $type;
    $param_types .= "s";
}

if (!empty($category)) {
    $where_conditions[] = "p.category = ?";
    $params[] = $category;
    $param_types .= "s";
}

if (!empty($city)) {
    $where_conditions[] = "p.city LIKE ?";
    $params[] = "%$city%";
    $param_types .= "s";
}

if (!empty($min_price)) {
    $where_conditions[] = "p.price >= ?";
    $params[] = floatval($min_price);
    $param_types .= "d";
}

if (!empty($max_price)) {
    $where_conditions[] = "p.price <= ?";
    $params[] = floatval($max_price);
    $param_types .= "d";
}

$where_clause = implode(' AND ', $where_conditions);

// Count query for pagination
$count_query = "SELECT COUNT(*) as total FROM properties p WHERE $where_clause";
$count_stmt = $conn->prepare($count_query);
if (!empty($params)) {
    $count_stmt->bind_param($param_types, ...$params);
}
$count_stmt->execute();
$total_properties = $count_stmt->get_result()->fetch_assoc()['total'];
$total_pages = ceil($total_properties / $per_page);

// Main query
$properties_query = "
    SELECT p.*, u.name as owner_name 
    FROM properties p 
    LEFT JOIN users u ON p.user_id = u.id 
    WHERE $where_clause 
    ORDER BY p.featured DESC, p.created_at DESC 
    LIMIT ? OFFSET ?
";

$stmt = $conn->prepare($properties_query);
$all_params = array_merge($params, [$per_page, $offset]);
$all_param_types = $param_types . "ii";
$stmt->bind_param($all_param_types, ...$all_params);
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);

// Kategori isimleri
$category_names = [
    'apartment' => 'Daire',
    'house' => 'Ev', 
    'villa' => 'Villa',
    'office' => 'Ofis',
    'shop' => 'Dükkan',
    'warehouse' => 'Depo',
    'land' => 'Arsa'
];

// Fiyat formatla
function formatPrice($price) {
    return number_format($price, 0, ',', '.') . ' ₺';
}

// Şehir listesi (database'den)
$cities_query = $conn->query("SELECT DISTINCT city FROM properties WHERE status IN ('active', 'approved') ORDER BY city");
$cities = $cities_query->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Tüm İlanlar - Gökhan Aydınlı Real Estate</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Service Worker Engelleyici -->
    <script src="includes/service-worker-blocker.js"></script>
    
    <style>
        :root {
            --primary-color: #0D1A1C;
            --secondary-color: #15B97C;
            --accent-color: #FF6B35;
            --light-bg: #F8F9FA;
        }
        
        body {
            background-color: var(--light-bg);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }
        
        .hero-section {
            background: linear-gradient(135deg, var(--primary-color), #2c3e50);
            color: white;
            padding: 100px 0 50px;
        }
        
        .search-filters {
            background: white;
            border-radius: 15px;
            padding: 30px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
            margin-top: -50px;
            position: relative;
            z-index: 10;
        }
        
        .property-card {
            background: white;
            border-radius: 15px;
            overflow: hidden;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            margin-bottom: 30px;
        }
        
        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }
        
        .property-image {
            height: 250px;
            overflow: hidden;
            position: relative;
        }
        
        .property-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
            transition: transform 0.3s ease;
        }
        
        .property-card:hover .property-image img {
            transform: scale(1.05);
        }
        
        .property-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: var(--secondary-color);
            color: white;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
        }
        
        .featured-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            background: var(--accent-color);
            color: white;
            padding: 5px 10px;
            border-radius: 15px;
            font-size: 11px;
        }
        
        .property-content {
            padding: 25px;
        }
        
        .property-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
            line-height: 1.3;
        }
        
        .property-price {
            font-size: 24px;
            font-weight: 700;
            color: var(--accent-color);
            margin-bottom: 15px;
        }
        
        .property-features {
            display: flex;
            gap: 15px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
        }
        
        .property-location {
            color: #888;
            font-size: 14px;
            margin-bottom: 20px;
        }
        
        .view-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            font-weight: 500;
            transition: all 0.3s ease;
            width: 100%;
            text-align: center;
            display: inline-block;
        }
        
        .view-btn:hover {
            background: var(--primary-color);
            color: white;
            transform: translateY(-2px);
        }
        
        .pagination-wrapper {
            background: white;
            border-radius: 15px;
            padding: 20px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.1);
        }
        
        .page-link {
            border: none;
            color: var(--primary-color);
            margin: 0 5px;
            border-radius: 10px;
        }
        
        .page-link:hover {
            background-color: var(--secondary-color);
            color: white;
        }
        
        .page-item.active .page-link {
            background-color: var(--secondary-color);
            border-color: var(--secondary-color);
        }
        
        .back-btn {
            background: rgba(255,255,255,0.2);
            color: white;
            border: 2px solid white;
            padding: 10px 20px;
            border-radius: 25px;
            text-decoration: none;
            transition: all 0.3s ease;
        }
        
        .back-btn:hover {
            background: white;
            color: var(--primary-color);
        }
    </style>
</head>
<body>
    <!-- Hero Section -->
    <div class="hero-section">
        <div class="container">
            <div class="row">
                <div class="col-12 text-center">
                    <a href="../index.php" class="back-btn mb-4 d-inline-block">
                        <i class="fas fa-arrow-left me-2"></i>Anasayfaya Dön
                    </a>
                    
                    <h1 class="display-4 mb-3">Tüm İlanlar</h1>
                    <p class="lead">
                        <?= number_format($total_properties) ?> adet ilan bulundu
                    </p>
                </div>
            </div>
        </div>
    </div>

    <!-- Search Filters -->
    <div class="container">
        <div class="search-filters">
            <form method="GET" action="" class="row g-3">
                <div class="col-lg-3 col-md-6 col-sm-12">
                    <label for="search" class="form-label">Arama</label>
                    <input type="text" class="form-control" id="search" name="search" 
                           placeholder="İlan başlığı, açıklama..." value="<?= htmlspecialchars($search) ?>">
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="type" class="form-label">Tip</label>
                    <select class="form-select" id="type" name="type">
                        <option value="">Tümü</option>
                        <option value="sale" <?= $type === 'sale' ? 'selected' : '' ?>>Satılık</option>
                        <option value="rent" <?= $type === 'rent' ? 'selected' : '' ?>>Kiralık</option>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="category" class="form-label">Kategori</label>
                    <select class="form-select" id="category" name="category">
                        <option value="">Tümü</option>
                        <?php foreach ($category_names as $key => $name): ?>
                        <option value="<?= $key ?>" <?= $category === $key ? 'selected' : '' ?>><?= $name ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-2 col-md-3 col-sm-6">
                    <label for="city" class="form-label">Şehir</label>
                    <select class="form-select" id="city" name="city">
                        <option value="">Tümü</option>
                        <?php foreach ($cities as $city_data): ?>
                        <option value="<?= htmlspecialchars($city_data['city']) ?>" 
                                <?= $city === $city_data['city'] ? 'selected' : '' ?>>
                            <?= htmlspecialchars($city_data['city']) ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <label for="min_price" class="form-label">Min Fiyat</label>
                    <input type="number" class="form-control" id="min_price" name="min_price" 
                           placeholder="0" value="<?= htmlspecialchars($min_price) ?>">
                </div>
                <div class="col-lg-1 col-md-2 col-sm-6">
                    <label for="max_price" class="form-label">Max Fiyat</label>
                    <input type="number" class="form-control" id="max_price" name="max_price" 
                           placeholder="∞" value="<?= htmlspecialchars($max_price) ?>">
                </div>
                <div class="col-lg-1 col-md-2 col-sm-12 d-flex align-items-end">
                    <button type="submit" class="btn btn-primary w-100">
                        <i class="fas fa-search d-lg-inline d-none"></i>
                        <span class="d-lg-none d-inline">Ara</span>
                    </button>
                </div>
            </form>
        </div>
    </div>

    <!-- Properties Grid -->
    <div class="container my-5">
        <?php if (!empty($properties)): ?>
        <div class="row">
            <?php foreach ($properties as $property): 
                $listing_type_text = ($property['type'] === 'sale') ? 'SATILIK' : 'KİRALIK';
                $category_text = $category_names[$property['category']] ?? ucfirst($property['category']);
                
                // İlk resmi al - path düzeltmesi
                $first_image = 'https://via.placeholder.com/400x250/f0f0f0/666?text=Resim+Yok';
                if (!empty($property['images'])) {
                    $images = explode(',', $property['images']);
                    $image_path = trim($images[0]);
                    
                    // Eğer relative path ise, bir üst klasöre göre ayarla
                    if (strpos($image_path, 'http') !== 0) {
                        $first_image = '../' . $image_path;
                    } else {
                        $first_image = $image_path;
                    }
                }
            ?>
            <div class="col-lg-4 col-md-6">
                <div class="property-card">
                    <div class="property-image">
                        <img src="<?= htmlspecialchars($first_image) ?>" alt="<?= htmlspecialchars($property['title']) ?>">
                        <div class="property-badge"><?= $listing_type_text ?></div>
                        <?php if ($property['featured']): ?>
                        <div class="featured-badge">
                            <i class="fas fa-star me-1"></i>Öne Çıkan
                        </div>
                        <?php endif; ?>
                    </div>
                    <div class="property-content">
                        <h3 class="property-title"><?= htmlspecialchars($property['title']) ?></h3>
                        <div class="property-price"><?= formatPrice($property['price']) ?></div>
                        
                        <div class="property-features">
                            <?php if ($property['area'] > 0): ?>
                            <span><i class="fas fa-expand-arrows-alt me-1"></i><?= $property['area'] ?> m²</span>
                            <?php endif; ?>
                            
                            <?php if ($property['bedrooms'] > 0): ?>
                            <span><i class="fas fa-bed me-1"></i><?= $property['bedrooms'] ?></span>
                            <?php endif; ?>
                            
                            <?php if ($property['bathrooms'] > 0): ?>
                            <span><i class="fas fa-bath me-1"></i><?= $property['bathrooms'] ?></span>
                            <?php endif; ?>
                        </div>
                        
                        <div class="property-location">
                            <i class="fas fa-map-marker-alt me-1"></i>
                            <?= htmlspecialchars($property['city']) ?>, <?= htmlspecialchars($property['district']) ?>
                        </div>
                        
                        <a href="../property-details.php?id=<?= $property['id'] ?>" class="view-btn">
                            <i class="fas fa-eye me-2"></i>Detayları Görüntüle
                        </a>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <!-- Pagination -->
        <?php if ($total_pages > 1): ?>
        <div class="pagination-wrapper text-center mt-5">
            <nav aria-label="Sayfa navigasyonu">
                <ul class="pagination justify-content-center mb-0">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page - 1])) ?>">
                            <i class="fas fa-chevron-left"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                    
                    <?php for ($i = max(1, $page - 2); $i <= min($total_pages, $page + 2); $i++): ?>
                    <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $i])) ?>">
                            <?= $i ?>
                        </a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?<?= http_build_query(array_merge($_GET, ['page' => $page + 1])) ?>">
                            <i class="fas fa-chevron-right"></i>
                        </a>
                    </li>
                    <?php endif; ?>
                </ul>
            </nav>
            
            <p class="mt-3 mb-0 text-muted">
                Sayfa <?= $page ?> / <?= $total_pages ?> 
                (Toplam <?= number_format($total_properties) ?> ilan)
            </p>
        </div>
        <?php endif; ?>
        
        <?php else: ?>
        <!-- İlan Bulunamadı -->
        <div class="text-center py-5">
            <i class="fas fa-search fa-3x text-muted mb-3"></i>
            <h3 class="text-muted">İlan Bulunamadı</h3>
            <p class="text-muted mb-4">
                Arama kriterlerinize uygun ilan bulunamadı. Lütfen farklı filtreler deneyiniz.
            </p>
            <div class="property-info-card" style="max-width: 600px; margin: 0 auto;">
                <a href="listings.php" class="btn btn-primary">
                    <i class="fas fa-refresh me-2"></i>Filtreleri Temizle
                </a>
            </div>
        </div>
        <?php endif; ?>
    </div>

    <!-- Bootstrap JS -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>