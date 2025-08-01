<?php
session_start();
include '../db.php';

// Giriş kontrolü ve admin yetkisi kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini veritabanından çek
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'root')) {
    header("Location: dashboard.php");
    exit;
}

// Sayfa ayarları
$current_page = 'admin-properties';
$page_title = $user['name'] . ' - İlan Yönetimi';
$user_name = $user['name']; // Sidebar için

// İlan işlemleri
if (isset($_POST['action'])) {
    if ($_POST['action'] === 'update_status' && isset($_POST['property_id']) && isset($_POST['status'])) {
        $property_id = intval($_POST['property_id']);
        $status = $_POST['status'];
        
        // Delete işlemi
        if ($status === 'delete') {
            try {
                $conn->begin_transaction();
                
                // İlan resimlerini sil
                $stmt = $conn->prepare("SELECT images FROM properties WHERE id = ?");
                $stmt->bind_param("i", $property_id);
                $stmt->execute();
                $property = $stmt->get_result()->fetch_assoc();
                
                if ($property && !empty($property['images']) && $property['images'] !== null) {
                    $images = json_decode($property['images'], true);
                    if (is_array($images)) {
                        foreach ($images as $image) {
                            $image_path = "../" . $image;
                            if (file_exists($image_path)) {
                                unlink($image_path);
                            }
                        }
                    }
                }
                
                // İlanı sil
                $delete_stmt = $conn->prepare("DELETE FROM properties WHERE id = ?");
                $delete_stmt->bind_param("i", $property_id);
                $delete_stmt->execute();
                
                $conn->commit();
                
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'message' => 'İlan başarıyla silindi']);
                exit;
            } catch (Exception $e) {
                $conn->rollback();
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Silme hatası: ' . $e->getMessage()]);
                exit;
            }
        }
        
        // Güvenlik kontrolü - status değişikliği
        if (in_array($status, ['active', 'inactive', 'pending', 'approved', 'rejected'])) {
            try {
                $update_stmt = $conn->prepare("UPDATE properties SET status = ? WHERE id = ?");
                $update_stmt->bind_param("si", $status, $property_id);
                
                if ($update_stmt->execute()) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => true, 'message' => 'İlan durumu güncellendi']);
                    exit;
                } else {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Veritabanı güncelleme hatası']);
                    exit;
                }
            } catch (Exception $e) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Hata: ' . $e->getMessage()]);
                exit;
            }
        } else {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Geçersiz durum']);
            exit;
        }
    }
}
// Filtreleme ve sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12;
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$type_filter = isset($_GET['type']) ? $_GET['type'] : '';
$city_filter = isset($_GET['city']) ? $_GET['city'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Where koşulları
$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($status_filter && in_array($status_filter, ['pending', 'active', 'inactive', 'approved', 'rejected'])) {
    $where_conditions[] = "p.status = ?";
    $params[] = $status_filter;
    $types .= "s";
}

if ($type_filter && in_array($type_filter, ['sale', 'rent'])) {
    $where_conditions[] = "p.type = ?";
    $params[] = $type_filter;
    $types .= "s";
}

if ($city_filter) {
    $where_conditions[] = "p.city = ?";
    $params[] = $city_filter;
    $types .= "s";
}

if ($search) {
    $where_conditions[] = "(p.title LIKE ? OR p.description LIKE ? OR p.address LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "sss";
}

$where_clause = implode(" AND ", $where_conditions);

// Toplam ilan sayısı
$count_query = "SELECT COUNT(*) as total FROM properties p WHERE $where_clause";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    if (!empty($types)) {
        $count_stmt->bind_param($types, ...$params);
    }
    $count_stmt->execute();
    $total_properties = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $result = $conn->query($count_query);
    $total_properties = $result ? $result->fetch_assoc()['total'] : 0;
}

$total_pages = ceil($total_properties / $limit);

// İlanları getir - images alanını kontrol et
$properties_query = "SELECT p.*, u.name as owner_name, u.email as owner_email
                     FROM properties p 
                     LEFT JOIN users u ON p.user_id = u.id 
                     WHERE $where_clause 
                     ORDER BY p.created_at DESC 
                     LIMIT $limit OFFSET $offset";

if ($params) {
    $properties_stmt = $conn->prepare($properties_query);
    if (!empty($types)) {
        $properties_stmt->bind_param($types, ...$params);
    }
    $properties_stmt->execute();
    $properties_result = $properties_stmt->get_result();
} else {
    $properties_result = $conn->query($properties_query);
}

// İstatistikler - Kullanıcının ilanları için
$stats_query = "SELECT 
    COUNT(*) as total_properties,
    SUM(CASE WHEN status = 'active' OR status = 'approved' THEN 1 ELSE 0 END) as active_properties,
    SUM(CASE WHEN status = 'inactive' OR status = 'rejected' OR status = 'pending' THEN 1 ELSE 0 END) as inactive_properties
    FROM properties WHERE $where_clause";

if ($stmt = $conn->prepare($stats_query)) {
    if (!empty($where_params)) {
        $stmt->bind_param($where_types, ...$where_params);
    }
    $stmt->execute();
    $stats_result = $stmt->get_result();
    $stats = $stats_result ? $stats_result->fetch_assoc() : [
        'total_properties' => 0,
        'active_properties' => 0,
        'inactive_properties' => 0
    ];
    $stmt->close();
} else {
    $stats = [
        'total_properties' => 0,
        'active_properties' => 0,
        'inactive_properties' => 0
    ];
}

// Şehir listesi
try {
    $cities_query = "SELECT DISTINCT city FROM properties WHERE city IS NOT NULL AND city != '' AND city != 'null' ORDER BY city";
    $cities_result = $conn->query($cities_query);
    $cities = [];
    if ($cities_result) {
        while ($row = $cities_result->fetch_assoc()) {
            $city = trim($row['city']);
            if (!empty($city)) {
                $cities[] = $city;
            }
        }
    }
} catch (Exception $e) {
    $cities = ['İstanbul', 'Ankara', 'İzmir']; // Fallback
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    
    <style>
        /* Dashboard Admin Properties Specific Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: margin-left 0.3s ease;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .mobile-header {
            display: none;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            align-items: center;
            justify-content: space-between;
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #0d6efd;
        }
        
        .mobile-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin: 0;
        }
        
        .mobile-logout {
            color: #dc3545;
            text-decoration: none;
            font-size: 18px;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #4c9eff 0%, #0066ff 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 15px 35px rgba(76, 158, 255, 0.3);
            position: relative;
            overflow: hidden;
        }
        
        .welcome-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><defs><pattern id="dots" width="20" height="20" patternUnits="userSpaceOnUse"><circle cx="10" cy="10" r="2" fill="rgba(255,255,255,0.1)"/></pattern></defs><rect width="100" height="100" fill="url(%23dots)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
            position: relative;
            z-index: 1;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .section-link {
            color: #0d6efd;
            text-decoration: none;
            font-size: 14px;
            font-weight: 500;
        }
        
        .section-link:hover {
            color: #0d1a1c;
            text-decoration: underline;
        }

        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border: 1px solid #f0f0f0;
        }

        .stats-card:hover {
            /* transform: translateY(-5px); */
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .stats-number {
            font-size: 36px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 14px;
            color: #6c757d;
            font-weight: 500;
            margin-bottom: 10px;
        }

        .stats-change {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 12px;
            color: #28a745;
        }

        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            text-align: center;
        }

        .empty-state i {
            font-size: 72px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: #0d6efd;
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 25px;
        }

        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        /* Stats Cards Override - Force Blue Colors */
        .stats-cards .stats-card .stats-icon {
            background: linear-gradient(135deg, #0d6efd, #0b5ed7) !important;
        }

        .stats-cards .stats-card .stats-change.positive {
            background: rgba(13, 110, 253, 0.1) !important;
            color: #0d6efd !important;
        }

        .stats-cards .stats-card .stats-change.positive i {
            color: #0d6efd !important;
        }

        .stats-cards .stats-card .stats-change.negative {
            background: rgba(220, 53, 69, 0.1) !important;
            color: #dc3545 !important;
        }

        .stats-cards .stats-card .stats-change.negative i {
            color: #dc3545 !important;
        }

        .stats-cards .stats-card:hover .stats-icon {
            background: linear-gradient(135deg, #0b5ed7, #0a58ca) !important;
        }

        /* Stats Cards Header Override - Force Blue Border */
        .stats-cards .stats-card .stats-card-header::before,
        .stats-cards .stats-card::before,
        .stats-card .stats-card-header::before,
        .stats-card::before {
            background: #0d6efd !important;
            border-top: 4px solid #0d6efd !important;
        }

        .stats-cards .stats-card {
            border-top: 4px solid #0d6efd !important;
        }

        .stats-card {
            border-top: 4px solid #0d6efd !important;
        }

        /* Override any green borders on stats cards */
        .stats-cards .stats-card,
        .stats-card {
            border-top-color: #0d6efd !important;
        }

        .stats-cards .stats-card::after,
        .stats-card::after {
            background: #0d6efd !important;
        }

        /* Override dashboard-common.css green colors */
        .dash-btn-two {
            background: #0d6efd !important;
            border-color: #0d6efd !important;
        }

        .dash-btn-two:hover {
            background: #0b5ed7 !important;
            border-color: #0b5ed7 !important;
        }

        /* Ana sayfa tarzı property kartları */
        .listing-card-four {
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .listing-card-four:hover {
            transform: translateY(-8px);
            box-shadow: 0 20px 40px rgba(0,0,0,0.15) !important;
        }

        .listing-card-four .tag {
            position: absolute;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            z-index: 2;
            color: white;
        }

        .listing-card-four .property-info {
            transition: all 0.3s ease;
        }

        .listing-card-four:hover .property-info {
            background: linear-gradient(to top, rgba(0,0,0,0.9), transparent) !important;
        }

        .listing-card-four .property-actions .btn {
            border-radius: 8px;
            padding: 6px 12px;
            font-size: 12px;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .listing-card-four .property-actions .btn:hover {
            transform: translateY(-2px);
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .mobile-header {
                display: flex !important;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .welcome-banner {
                padding: 25px 20px;
                margin-bottom: 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stats-card {
                padding: 20px;
            }
            
            .filter-section {
                padding: 20px;
            }
            
            .property-card {
                padding: 20px;
            }
            
            .property-actions {
                flex-direction: column;
            }
            
            .property-actions .btn {
                margin-bottom: 5px;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar-admin.php'; ?>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay"></div>

        <!-- Ana İçerik -->
        <div class="dashboard-body">
            <!-- Mobil Header -->
            <div class="mobile-header d-block d-md-none">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mobile-title">İlan Yönetimi</h5>
                <a href="../logout.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>

            <!-- Ana İçerik Alanı -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <div class="welcome-content">
                            <h2 class="welcome-title">
                                <i class="fas fa-building me-2"></i>
                                İlan Yönetimi
                            </h2>
                            <p class="welcome-subtitle">
                                Sistemdeki tüm emlak ilanlarını yönetebilir, onaylayabilir veya reddedebilirsiniz. 
                                İlan durumlarını kontrol edin ve gerekli işlemleri yapın.
                            </p>
                        </div>
                    </div>

                    <!-- Success/Error Messages -->
                    <?php if (isset($success)): ?>
                        <div class="alert alert-success alert-dismissible fade show" role="alert">
                            <i class="fas fa-check-circle me-2"></i>
                            <?= $success ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>
                    
                    <?php if (isset($error)): ?>
                        <div class="alert alert-danger alert-dismissible fade show" role="alert">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?= $error ?>
                            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                        </div>
                    <?php endif; ?>

                    <!-- İstatistik Kartları -->
                    <div class="stats-grid">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stats-number"><?= $stats['total_properties'] ?></div>
                            <div class="stats-label">Toplam İlan</div>
                            <div class="stats-change">
                                <i class="fas fa-chart-line"></i>
                                <span>Sistemde kayıtlı</span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-number"><?= $stats['active_properties'] ?></div>
                            <div class="stats-label">Aktif İlan (Gösterilen)</div>
                            <div class="stats-change">
                                <i class="fas fa-eye"></i>
                                <span>Yayında</span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                            <div class="stats-number"><?= $stats['inactive_properties'] ?></div>
                            <div class="stats-label">Yayından Kaldırılan</div>
                            <div class="stats-change">
                                <i class="fas fa-eye-slash"></i>
                                <span>Pasif</span>
                            </div>
                        </div>
                    </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <h3 class="section-title">
                        <i class="fas fa-filter"></i>
                        Filtreleme ve Arama
                    </h3>
                    
                    <form method="GET" class="row g-3">
                        <div class="col-md-3">
                            <label for="status" class="form-label">Durum</label>
                            <select name="status" id="status" class="form-select">
                                <option value="">Tüm Durumlar</option>
                                <option value="pending" <?= (isset($_GET['status']) && $_GET['status'] == 'pending') ? 'selected' : '' ?>>
                                    Beklemede
                                </option>
                                <option value="active" <?= (isset($_GET['status']) && $_GET['status'] == 'active') ? 'selected' : '' ?>>
                                    Aktif İlanlar
                                </option>
                                <option value="inactive" <?= (isset($_GET['status']) && $_GET['status'] == 'inactive') ? 'selected' : '' ?>>
                                    Pasif İlanlar
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="type" class="form-label">Tür</label>
                            <select name="type" id="type" class="form-select">
                                <option value="">Tüm Türler</option>
                                <option value="sale" <?= (isset($_GET['type']) && $_GET['type'] == 'sale') ? 'selected' : '' ?>>
                                    Satılık
                                </option>
                                <option value="rent" <?= (isset($_GET['type']) && $_GET['type'] == 'rent') ? 'selected' : '' ?>>
                                    Kiralık
                                </option>
                            </select>
                        </div>
                        <div class="col-md-2">
                            <label for="city" class="form-label">Şehir</label>
                            <select name="city" id="city" class="form-select">
                                <option value="">Tüm Şehirler</option>
                                <?php foreach ($cities as $city): ?>
                                    <option value="<?= htmlspecialchars($city) ?>" 
                                            <?= (isset($_GET['city']) && $_GET['city'] == $city) ? 'selected' : '' ?>>
                                        <?= htmlspecialchars($city) ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-3">
                            <label for="search" class="form-label">Arama</label>
                            <input type="text" name="search" id="search" class="form-control" 
                                   placeholder="Başlık, açıklama veya adres..." 
                                   value="<?= htmlspecialchars($_GET['search'] ?? '') ?>">
                        </div>
                        <div class="col-md-2">
                            <label>&nbsp;</label>
                            <div class="d-grid">
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-search"></i> Filtrele
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- İlan Listesi -->
                <div class="properties-list-section">
                    <div class="d-flex justify-content-between align-items-center mb-4">
                        <h3 class="section-title">
                            <i class="fas fa-list"></i>
                            İlan Listesi (<?= $total_properties ?> ilan)
                        </h3>
                        <div class="d-flex gap-2">
                            <a href="add-property.php" class="btn btn-success btn-sm">
                                <i class="fas fa-plus"></i> Yeni İlan
                            </a>
                            <a href="?export=excel" class="btn btn-info btn-sm">
                                <i class="fas fa-download"></i> Excel İndir
                            </a>
                        </div>
                    </div>

                    <?php if ($properties_result && $properties_result->num_rows > 0): ?>
                        <div class="row">
                            <?php while ($property = $properties_result->fetch_assoc()): ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <?php 
                                    // CLOUDFLARE IMAGES SUPPORT ADDED
                                    $first_image = '';
                                    $image_url = '../images/listing/img_20.jpg'; // default fallback
                                    
                                    // 1. CLOUDFLARE IMAGES ÖNCELİK (YENİ SISTEM)
                                    if (!empty($property['use_cloudflare']) && !empty($property['cloudflare_images'])) {
                                        $cloudflare_decoded = json_decode($property['cloudflare_images'], true);
                                        if (is_array($cloudflare_decoded) && !empty($cloudflare_decoded)) {
                                            $first_cloudflare_id = $cloudflare_decoded[0];
                                            $account_hash = 'prdw3ANMyocSBJD-Do1EeQ';
                                            $image_url = "https://imagedelivery.net/{$account_hash}/{$first_cloudflare_id}/public";
                                        }
                                    }
                                    // 2. LEGACY IMAGES FALLBACK (ESKİ SISTEM)
                                    elseif (!empty($property['images']) && $property['images'] !== null) {
                                        if (strpos($property['images'], '[') === 0) {
                                            // JSON format
                                            $images = json_decode($property['images'], true);
                                            if (is_array($images) && !empty($images)) {
                                                $first_image = trim($images[0]);
                                            }
                                        } else {
                                            // Comma separated format
                                            $images = explode(',', $property['images']);
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
                                                    if (file_exists('../' . $path)) {
                                                        $correct_image = $path;
                                                        break;
                                                    }
                                                }
                                                
                                                if (empty($correct_image)) {
                                                    $image_filename = basename($first_image);
                                                    $properties_path = 'uploads/properties/' . $image_filename;
                                                    if (file_exists('../' . $properties_path)) {
                                                        $correct_image = $properties_path;
                                                    } else {
                                                        $correct_image = $first_image;
                                                    }
                                                }
                                                
                                                // Local image için show-image.php kullan
                                                if (!empty($correct_image)) {
                                                    $image_filename = basename($correct_image);
                                                    $image_url = "../show-image.php?img=" . urlencode($image_filename) . "&v=" . time();
                                                }
                                            }
                                        }
                                    }
                                    
                                    $listing_type_text = ($property['type'] === 'sale') ? 'SATILIK' : 'KİRALIK';
                                    ?>
                                    
                                    <div class="listing-card-four overflow-hidden position-relative" 
                                         style="min-height: 280px;
                                                border-radius: 15px;
                                                transition: all 0.3s ease;">
                                        
                                        <!-- Background Image -->
                                        <img src="<?= htmlspecialchars($image_url) ?>" 
                                             alt="<?= htmlspecialchars($property['title']) ?>"
                                             style="position: absolute; 
                                                    top: 0; 
                                                    left: 0; 
                                                    width: 100%; 
                                                    height: 100%; 
                                                    object-fit: cover; 
                                                    z-index: 1;"
                                             onerror="this.style.display='none'; this.nextElementSibling.style.display='flex';">
                                        <!-- Fallback background -->
                                        <div style="position: absolute; 
                                                    top: 0; 
                                                    left: 0; 
                                                    width: 100%; 
                                                    height: 100%; 
                                                    background: linear-gradient(135deg, #f8f9fa, #e9ecef);
                                                    display: none;
                                                    align-items: center;
                                                    justify-content: center;
                                                    z-index: 1;">
                                            <div class="text-center text-muted">
                                                <i class="fas fa-image fa-2x mb-2"></i>
                                                <br><small>Resim yüklenemedi</small>
                                            </div>
                                        </div>
                                        
                                        <!-- Status Badge -->
                                        <?php if ($property['status'] === 'active'): ?>
                                            <button type="button" class="tag fw-500 bg-success border-0" 
                                                    onclick="updateStatus(<?= $property['id'] ?>, 'inactive')" 
                                                    style="position: absolute; top: 15px; left: 15px; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: white; z-index: 3; cursor: pointer; transition: all 0.3s;" 
                                                    title="Pasife almak için tıklayın">
                                                Pasife Al
                                            </button>
                                        <?php elseif ($property['status'] === 'pending'): ?>
                                            <div class="tag fw-500 bg-warning" style="position: absolute; top: 15px; left: 15px; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: white; z-index: 3;">
                                                Beklemede
                                            </div>
                                        <?php endif; ?>                                        <!-- Type Badge -->
                                        <div class="tag fw-500 <?= $property['type'] === 'sale' ? 'bg-primary' : 'bg-info' ?>" 
                                             style="position: absolute; top: 15px; right: 15px; padding: 6px 12px; border-radius: 20px; font-size: 12px; color: white; z-index: 3;">
                                            <?= $listing_type_text ?>
                                        </div>
                                        
                                        <!-- Property Info -->
                                        <div class="property-info w-100 position-absolute bottom-0" 
                                             style="background: linear-gradient(to top, rgba(0,0,0,0.8), transparent); 
                                                     padding: 20px; 
                                                     border-radius: 0 0 15px 15px;
                                                     z-index: 2;">
                                            <div class="d-flex align-items-center justify-content-between">
                                                <div class="pe-3 text-white">
                                                    <h6 class="title fw-500 mb-1" style="color: white !important; font-size: 16px;">
                                                        <?= htmlspecialchars($property['title']) ?>
                                                    </h6>
                                                    <div class="address mb-2" style="color: rgba(255,255,255,0.8); font-size: 14px;">
                                                        <i class="fas fa-map-marker-alt me-1"></i>
                                                        <?= htmlspecialchars($property['city']) ?>
                                                    </div>
                                                    <div class="price fw-bold" style="color: #ffc107; font-size: 18px;">
                                                        <?= number_format($property['price'], 0, ',', '.') ?> ₺
                                                        <?= $property['type'] == 'rent' ? '/ay' : '' ?>
                                                    </div>
                                                    <div class="owner-info mt-2" style="color: rgba(255,255,255,0.7); font-size: 12px;">
                                                        <i class="fas fa-user me-1"></i>
                                                        <?= htmlspecialchars($property['owner_name'] ?? 'Bilinmiyor') ?>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <!-- Action Buttons -->
                                            <div class="property-actions mt-3 d-flex gap-2 flex-wrap">
                                                <a href="../property-details.php?id=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-outline-light" target="_blank">
                                                    <i class="fas fa-eye"></i> Görüntüle
                                                </a>
                                                
                                                <a href="add-property.php?edit=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-outline-warning">
                                                    <i class="fas fa-edit"></i> Güncelle
                                                </a>
                                                
                                                <button type="button" class="btn btn-sm btn-outline-danger" 
                                                        onclick="deleteProperty(<?= $property['id'] ?>)">
                                                    <i class="fas fa-trash"></i> Sil
                                                </button>
                                                
                                                <?php if ($property['status'] === 'active'): ?>
                                                    <button type="button" class="btn btn-sm btn-warning" 
                                                            onclick="updateStatus(<?= $property['id'] ?>, 'inactive')">
                                                        <i class="fas fa-eye-slash"></i> Pasif Yap
                                                    </button>
                                                <?php elseif ($property['status'] === 'inactive'): ?>
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="updateStatus(<?= $property['id'] ?>, 'active')">
                                                        <i class="fas fa-eye"></i> Aktif Yap
                                                    </button>
                                                <?php elseif ($property['status'] === 'pending'): ?>
                                                    <button type="button" class="btn btn-sm btn-success" 
                                                            onclick="updateStatus(<?= $property['id'] ?>, 'active')">
                                                        <i class="fas fa-check"></i> Onayla
                                                    </button>
                                                    
                                                    <button type="button" class="btn btn-sm btn-danger" 
                                                            onclick="updateStatus(<?= $property['id'] ?>, 'inactive')">
                                                        <i class="fas fa-times"></i> Reddet
                                                    </button>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                        
                        <!-- Pagination -->
                        <?php if ($total_pages > 1): ?>
                            <nav aria-label="Sayfa navigasyonu">
                                <ul class="pagination justify-content-center">
                                    <?php 
                                    $query_params = [];
                                    if ($status_filter) $query_params[] = "status=" . urlencode($status_filter);
                                    if ($type_filter) $query_params[] = "type=" . urlencode($type_filter);
                                    if ($city_filter) $query_params[] = "city=" . urlencode($city_filter);
                                    if ($search) $query_params[] = "search=" . urlencode($search);
                                    $query_string = $query_params ? '&' . implode('&', $query_params) : '';
                                    ?>
                                    
                                    <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                        <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                            <a class="page-link" href="?page=<?= $i ?><?= $query_string ?>"><?= $i ?></a>
                                        </li>
                                    <?php endfor; ?>
                                </ul>
                            </nav>
                        <?php endif; ?>
                        
                    <?php else: ?>
                        <div class="empty-state">
                            <i class="fas fa-home fa-3x text-muted mb-3"></i>
                            <h5>İlan bulunamadı</h5>
                            <p class="text-muted">Arama kriterlerinize uygun ilan bulunamadı.</p>
                            <a href="add-property.php" class="btn btn-primary">
                                <i class="fas fa-plus"></i> İlk İlanı Ekle
                            </a>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <!-- Bootstrap JS -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.dash-aside-navbar');
            const overlay = document.querySelector('.mobile-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }

        // Property image error handling
        document.addEventListener('DOMContentLoaded', function() {
            const images = document.querySelectorAll('.property-image');
            
            images.forEach((img, index) => {
                if (img.tagName === 'IMG') {
                    console.log(`Resim ${index + 1} yolu:`, img.src);
                    
                    img.addEventListener('error', function() {
                        console.error(`Resim ${index + 1} yüklenemedi:`, this.src);
                        this.style.display = 'none';
                        const fallback = this.nextElementSibling;
                        if (fallback) {
                            fallback.style.display = 'flex';
                        }
                    });
                    
                    img.addEventListener('load', function() {
                        console.log(`Resim ${index + 1} başarıyla yüklendi:`, this.src);
                    });
                    
                    // Resim yükleme durumunu kontrol et
                    if (img.complete) {
                        if (img.naturalHeight === 0) {
                            console.error(`Resim ${index + 1} bozuk:`, img.src);
                            img.style.display = 'none';
                            const fallback = img.nextElementSibling;
                            if (fallback) {
                                fallback.style.display = 'flex';
                            }
                        } else {
                            console.log(`Resim ${index + 1} zaten yüklü:`, img.src);
                        }
                    }
                }
            });
            
            console.log('Toplam resim sayısı:', images.length);
        });

        // Auto refresh for pending properties
        <?php if (isset($_GET['status']) && $_GET['status'] == 'pending'): ?>
        setInterval(function() {
            // Check for new pending properties every 30 seconds
            fetch(window.location.href, {
                method: 'HEAD'
            }).then(() => {
                // Optionally reload if there are updates
                // location.reload();
            });
        }, 30000);
        <?php endif; ?>

        // Mobile responsive
        window.addEventListener('resize', function() {
            if (window.innerWidth > 768) {
                const sidebar = document.querySelector('.dash-aside-navbar');
                const overlay = document.querySelector('.mobile-overlay');
                
                if (sidebar && overlay) {
                    sidebar.classList.remove('show');
                    overlay.classList.remove('show');
                }
            }
        });

        // Property actions
        function approveProperty(propertyId) {
            if (confirm('Bu ilanı onaylamak istediğinizden emin misiniz?')) {
                updatePropertyStatus(propertyId, 'approved');
            }
        }

        function rejectProperty(propertyId) {
            if (confirm('Bu ilanı reddetmek istediğinizden emin misiniz?')) {
                updatePropertyStatus(propertyId, 'rejected');
            }
        }

        function deleteProperty(propertyId) {
            if (confirm('Bu ilanı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')) {
                updatePropertyStatus(propertyId, 'delete');
            }
        }

        // Hızlı durum değiştirme fonksiyonu
        function updateStatus(propertyId, newStatus) {
            let confirmMessage = '';
            if (newStatus === 'inactive') {
                confirmMessage = 'Bu ilanı pasife almak istediğinizden emin misiniz? İlan artık görünmeyecek.';
            } else if (newStatus === 'active') {
                confirmMessage = 'Bu ilanı aktif hale getirmek istediğinizden emin misiniz?';
            } else {
                confirmMessage = 'İlan durumunu değiştirmek istediğinizden emin misiniz?';
            }
            
            if (confirm(confirmMessage)) {
                updatePropertyStatus(propertyId, newStatus);
            }
        }

        function updatePropertyStatus(propertyId, status) {
            const formData = new FormData();
            formData.append('property_id', propertyId);
            formData.append('status', status);
            formData.append('action', 'update_status');

            fetch('admin-properties.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Sayfayı yenile
                    location.reload();
                } else {
                    alert('Hata: ' + (data.message || 'Bilinmeyen hata'));
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu');
            });
        }

        // Filter form enhancements
        document.getElementById('status')?.addEventListener('change', function() {
            if (this.value === 'pending') {
                document.querySelector('.filter-section').style.borderLeft = '4px solid #ffc107';
            } else {
                document.querySelector('.filter-section').style.borderLeft = 'none';
            }
        });

        // Bulk actions (future enhancement)
        function selectAllProperties() {
            const checkboxes = document.querySelectorAll('.property-checkbox');
            checkboxes.forEach(cb => cb.checked = true);
        }

        function clearSelection() {
            const checkboxes = document.querySelectorAll('.property-checkbox');
            checkboxes.forEach(cb => cb.checked = false);
        }
    </script>
</body>
</html>