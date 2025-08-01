<?php
session_start();
include '../db.php';

// Giriş kontrolü
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

// Sayfa başlığı ve aktif menü
$page_title = "Kayıtlı Aramalar";
$current_page = 'saved-search';

// Saved searches tablosunu kontrol et ve yeniden oluştur
$check_table = "SHOW TABLES LIKE 'saved_searches'";
$table_exists = $conn->query($check_table);

if ($table_exists->num_rows == 0) {
    // Tablo yoksa oluştur
    $create_table = "
    CREATE TABLE saved_searches (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        search_name VARCHAR(255) NOT NULL,
        search_type ENUM('sale', 'rent', '') DEFAULT '' NULL,
        city VARCHAR(100) DEFAULT '' NULL,
        district VARCHAR(100) DEFAULT '' NULL,
        min_price INT DEFAULT 0,
        max_price INT DEFAULT 0,
        bedrooms INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        min_area INT DEFAULT 0,
        max_area INT DEFAULT 0,
        search_query TEXT NULL,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        is_active TINYINT(1) DEFAULT 1,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        INDEX idx_user_active (user_id, is_active)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";
    
    if (!$conn->query($create_table)) {
        die("Tablo oluşturulamadı: " . $conn->error);
    }
} else {
    // Tablo varsa sütunları kontrol et ve ekle
    $columns_to_check = [
        'search_type' => "ALTER TABLE saved_searches ADD COLUMN search_type ENUM('sale', 'rent', '') DEFAULT '' NULL AFTER search_name",
        'city' => "ALTER TABLE saved_searches ADD COLUMN city VARCHAR(100) DEFAULT '' NULL AFTER search_type",
        'district' => "ALTER TABLE saved_searches ADD COLUMN district VARCHAR(100) DEFAULT '' NULL AFTER city",
        'min_price' => "ALTER TABLE saved_searches ADD COLUMN min_price INT DEFAULT 0 AFTER district",
        'max_price' => "ALTER TABLE saved_searches ADD COLUMN max_price INT DEFAULT 0 AFTER min_price",
        'bedrooms' => "ALTER TABLE saved_searches ADD COLUMN bedrooms INT DEFAULT 0 AFTER max_price",
        'bathrooms' => "ALTER TABLE saved_searches ADD COLUMN bathrooms INT DEFAULT 0 AFTER bedrooms",
        'min_area' => "ALTER TABLE saved_searches ADD COLUMN min_area INT DEFAULT 0 AFTER bathrooms",
        'max_area' => "ALTER TABLE saved_searches ADD COLUMN max_area INT DEFAULT 0 AFTER min_area",
        'search_query' => "ALTER TABLE saved_searches ADD COLUMN search_query TEXT NULL AFTER max_area",
        'last_checked' => "ALTER TABLE saved_searches ADD COLUMN last_checked TIMESTAMP DEFAULT CURRENT_TIMESTAMP AFTER created_at",
        'is_active' => "ALTER TABLE saved_searches ADD COLUMN is_active TINYINT(1) DEFAULT 1 AFTER last_checked"
    ];
    
    foreach ($columns_to_check as $column => $sql) {
        $check_column = "SHOW COLUMNS FROM saved_searches LIKE '$column'";
        $column_exists = $conn->query($check_column);
        
        if ($column_exists->num_rows == 0) {
            try {
                $conn->query($sql);
            } catch (Exception $e) {
                // Hata olursa görmezden gel
            }
        }
    }
    
    // search_type sütununu ENUM olarak güncelle (varsa)
    try {
        $update_search_type = "ALTER TABLE saved_searches MODIFY COLUMN search_type ENUM('sale', 'rent', '') DEFAULT '' NULL";
        $conn->query($update_search_type);
    } catch (Exception $e) {
        // Hata olursa görmezden gel
    }
}

// Arama kaydetme
if (isset($_POST['save_search'])) {
    $search_name = trim($_POST['search_name']);
    $search_type = isset($_POST['search_type']) ? $_POST['search_type'] : '';
    $city = isset($_POST['city']) ? trim($_POST['city']) : '';
    $district = isset($_POST['district']) ? trim($_POST['district']) : '';
    $min_price = isset($_POST['min_price']) ? intval($_POST['min_price']) : 0;
    $max_price = isset($_POST['max_price']) ? intval($_POST['max_price']) : 0;
    $bedrooms = isset($_POST['bedrooms']) ? intval($_POST['bedrooms']) : 0;
    $bathrooms = isset($_POST['bathrooms']) ? intval($_POST['bathrooms']) : 0;
    $min_area = isset($_POST['min_area']) ? intval($_POST['min_area']) : 0;
    $max_area = isset($_POST['max_area']) ? intval($_POST['max_area']) : 0;
    $search_query = isset($_POST['search_query']) ? trim($_POST['search_query']) : '';

    if (!empty($search_name)) {
        try {
            $save_stmt = $conn->prepare("INSERT INTO saved_searches (user_id, search_name, search_type, city, district, min_price, max_price, bedrooms, bathrooms, min_area, max_area, search_query, is_active) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 1)");
            $save_stmt->bind_param("issssiiiiiis", $user_id, $search_name, $search_type, $city, $district, $min_price, $max_price, $bedrooms, $bathrooms, $min_area, $max_area, $search_query);
            
            if ($save_stmt->execute()) {
                $success = "Arama başarıyla kaydedildi!";
            } else {
                $error = "Arama kaydedilirken hata oluştu: " . $conn->error;
            }
        } catch (Exception $e) {
            $error = "Arama kaydedilirken hata oluştu: " . $e->getMessage();
        }
    } else {
        $error = "Arama adı zorunludur!";
    }
}

// Kayıtlı arama işlemleri
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $search_id = intval($_POST['search_id']);
    
    switch ($action) {
        case 'delete':
            $delete_stmt = $conn->prepare("DELETE FROM saved_searches WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $search_id, $user_id);
            if ($delete_stmt->execute()) {
                $success = "Kayıtlı arama silindi!";
            } else {
                $error = "Arama silinirken hata oluştu!";
            }
            break;
            
        case 'toggle_active':
            $toggle_stmt = $conn->prepare("UPDATE saved_searches SET is_active = NOT is_active WHERE id = ? AND user_id = ?");
            $toggle_stmt->bind_param("ii", $search_id, $user_id);
            if ($toggle_stmt->execute()) {
                $success = "Arama durumu güncellendi!";
            } else {
                $error = "Arama durumu güncellenirken hata oluştu!";
            }
            break;
            
        case 'run_search':
            // Arama parametrelerini al ve index.php'ye yönlendir
            $search_stmt = $conn->prepare("SELECT * FROM saved_searches WHERE id = ? AND user_id = ?");
            $search_stmt->bind_param("ii", $search_id, $user_id);
            $search_stmt->execute();
            $search_data = $search_stmt->get_result()->fetch_assoc();
            
            if ($search_data) {
                // Last checked güncelle
                $update_stmt = $conn->prepare("UPDATE saved_searches SET last_checked = NOW() WHERE id = ?");
                $update_stmt->bind_param("i", $search_id);
                $update_stmt->execute();
                
                // Arama parametrelerini URL'e dönüştür
                $params = [];
                if (!empty($search_data['search_type'])) $params[] = "type=" . urlencode($search_data['search_type']);
                if (!empty($search_data['city'])) $params[] = "city=" . urlencode($search_data['city']);
                if (!empty($search_data['district'])) $params[] = "district=" . urlencode($search_data['district']);
                if ($search_data['min_price'] > 0) $params[] = "min_price=" . $search_data['min_price'];
                if ($search_data['max_price'] > 0) $params[] = "max_price=" . $search_data['max_price'];
                if ($search_data['bedrooms'] > 0) $params[] = "bedrooms=" . $search_data['bedrooms'];
                if ($search_data['bathrooms'] > 0) $params[] = "bathrooms=" . $search_data['bathrooms'];
                if ($search_data['min_area'] > 0) $params[] = "min_area=" . $search_data['min_area'];
                if ($search_data['max_area'] > 0) $params[] = "max_area=" . $search_data['max_area'];
                if (!empty($search_data['search_query'])) $params[] = "search=" . urlencode($search_data['search_query']);
                
                $url = "../index.php" . (!empty($params) ? "?" . implode("&", $params) : "");
                header("Location: $url");
                exit;
            }
            break;
    }
}

// Kayıtlı aramaları getir
try {
    $saved_searches_query = "SELECT * FROM saved_searches WHERE user_id = ? ORDER BY created_at DESC";
    $saved_searches_stmt = $conn->prepare($saved_searches_query);
    $saved_searches_stmt->bind_param("i", $user_id);
    $saved_searches_stmt->execute();
    $saved_searches = $saved_searches_stmt->get_result();
} catch (Exception $e) {
    $saved_searches = null;
    $error = "Kayıtlı aramalar yüklenirken hata oluştu: " . $e->getMessage();
}

// İstatistikler
try {
    $stats_query = "SELECT 
        COUNT(*) as total_searches,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_searches,
        SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_searches
        FROM saved_searches WHERE user_id = ?";

    $stats_stmt = $conn->prepare($stats_query);
    $stats_stmt->bind_param("i", $user_id);
    $stats_stmt->execute();
    $stats = $stats_stmt->get_result()->fetch_assoc();
} catch (Exception $e) {
    $stats = ['total_searches' => 0, 'active_searches' => 0, 'inactive_searches' => 0];
}

// Null değerleri 0 yap
$stats['total_searches'] = $stats['total_searches'] ?? 0;
$stats['active_searches'] = $stats['active_searches'] ?? 0;
$stats['inactive_searches'] = $stats['inactive_searches'] ?? 0;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= htmlspecialchars($user['name']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <!-- Dashboard Common CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    
    <style>
        /* Saved Search Specific Styles */
        .search-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            border-left: 4px solid var(--secondary-color);
        }

        .search-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 15px 40px rgba(0,0,0,0.15);
        }

        .search-card.inactive {
            border-left-color: #6c757d;
            opacity: 0.7;
        }

        .search-card-header {
            padding: 20px;
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            border-bottom: 1px solid #e9ecef;
        }

        .search-title {
            font-size: 18px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 10px;
            display: flex;
            align-items: center;
            justify-content: space-between;
        }

        .search-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .badge-sale {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .badge-rent {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .badge-active {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .badge-inactive {
            background: #fff3e0;
            color: #f57c00;
        }

        .search-meta {
            display: flex;
            gap: 20px;
            color: #6c757d;
            font-size: 14px;
            margin-top: 10px;
        }

        .search-meta span {
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .search-content {
            padding: 20px;
        }

        .search-filters {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }

        .filter-item {
            background: #f8f9fa;
            padding: 12px;
            border-radius: 8px;
            text-align: center;
        }

        .filter-label {
            font-size: 12px;
            color: #6c757d;
            text-transform: uppercase;
            margin-bottom: 5px;
        }

        .filter-value {
            font-weight: 600;
            color: var(--primary-color);
        }

        .search-actions {
            display: flex;
            gap: 10px;
            justify-content: center;
            flex-wrap: wrap;
        }

        .action-btn {
            padding: 8px 16px;
            border-radius: 20px;
            font-size: 13px;
            font-weight: 500;
            border: 1px solid #E6E6E6;
            background: white;
            color: #666;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 6px;
        }

        .action-btn:hover {
            transform: translateY(-2px);
        }

        .action-btn.btn-primary:hover {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
            color: white;
        }

        .action-btn.btn-warning:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .action-btn.btn-danger:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        .new-search-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .new-search-title {
            font-size: 20px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 25px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(21, 185, 124, 0.25);
        }

        .empty-state {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            padding: 80px 20px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 80px;
            margin-bottom: 30px;
            opacity: 0.3;
        }

        .empty-state h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .empty-state p {
            color: #666;
            margin-bottom: 25px;
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

        .stats-cards .stats-card .stats-change.neutral {
            background: rgba(108, 117, 125, 0.1) !important;
            color: #6c757d !important;
        }

        .stats-cards .stats-card .stats-change.neutral i {
            color: #6c757d !important;
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

        @media (max-width: 768px) {
            .search-filters {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .search-actions {
                justify-content: center;
                gap: 5px;
            }
            
            .action-btn {
                flex: 1;
                justify-content: center;
                min-width: 80px;
                font-size: 11px;
                padding: 6px 12px;
            }

            .search-meta {
                flex-direction: column;
                gap: 10px;
            }

            .new-search-section {
                padding: 20px;
            }

            .stats-cards {
                grid-template-columns: repeat(1, 1fr);
                gap: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Normal Sidebar Include -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <!-- Normal Header Include -->
                <?php include 'includes/header.php'; ?>

                <h2 class="main-title d-block d-lg-none"><?= $page_title ?></h2>

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

                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-search"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['total_searches'] ?></div>
                        <div class="stats-label">Toplam Arama</div>
                        <div class="stats-change positive">
                            <i class="fas fa-bookmark"></i>
                            <span>Kayıtlı</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-play-circle"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['active_searches'] ?></div>
                        <div class="stats-label">Aktif Arama</div>
                        <div class="stats-change positive">
                            <i class="fas fa-check-circle"></i>
                            <span>Çalışıyor</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-pause-circle"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['inactive_searches'] ?></div>
                        <div class="stats-label">Pasif Arama</div>
                        <div class="stats-change <?= $stats['inactive_searches'] > 0 ? 'neutral' : 'positive' ?>">
                            <i class="fas fa-clock"></i>
                            <span>Beklemede</span>
                        </div>
                    </div>
                </div>

                <!-- New Search Form -->
                <div class="new-search-section">
                    <div class="new-search-title">
                        <i class="fas fa-plus-circle"></i>
                        Yeni Arama Kaydet
                    </div>
                    <form method="POST">
                        <div class="row g-3">
                            <div class="col-md-4">
                                <label class="form-label">Arama Adı <span class="text-danger">*</span></label>
                                <input type="text" class="form-control" name="search_name" placeholder="Örn: İstanbul'da 3+1 Daire" required>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Tür</label>
                                <select name="search_type" class="form-select">
                                    <option value="">Tümü</option>
                                    <option value="sale">Satılık</option>
                                    <option value="rent">Kiralık</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Şehir</label>
                                <input type="text" class="form-control" name="city" placeholder="Şehir">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">İlçe</label>
                                <input type="text" class="form-control" name="district" placeholder="İlçe">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Min Fiyat (₺)</label>
                                <input type="number" class="form-control" name="min_price" placeholder="0" min="0">
                            </div>
                            <div class="col-md-3">
                                <label class="form-label">Max Fiyat (₺)</label>
                                <input type="number" class="form-control" name="max_price" placeholder="0" min="0">
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Yatak Odası</label>
                                <select name="bedrooms" class="form-select">
                                    <option value="0">Farketmez</option>
                                    <option value="1">1+0</option>
                                    <option value="2">1+1</option>
                                    <option value="3">2+1</option>
                                    <option value="4">3+1</option>
                                    <option value="5">4+1</option>
                                    <option value="6">5+1</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Banyo</label>
                                <select name="bathrooms" class="form-select">
                                    <option value="0">Farketmez</option>
                                    <option value="1">1</option>
                                    <option value="2">2</option>
                                    <option value="3">3</option>
                                    <option value="4">4+</option>
                                </select>
                            </div>
                            <div class="col-md-2">
                                <label class="form-label">Min Alan (m²)</label>
                                <input type="number" class="form-control" name="min_area" placeholder="0" min="0">
                            </div>
                        </div>
                        <div class="row g-3 mt-2">
                            <div class="col-md-3">
                                <label class="form-label">Max Alan (m²)</label>
                                <input type="number" class="form-control" name="max_area" placeholder="0" min="0">
                            </div>
                            <div class="col-md-7">
                                <label class="form-label">Arama Kelimesi</label>
                                <input type="text" class="form-control" name="search_query" placeholder="Örn: asansörlü, merkezi, deniz manzaralı">
                            </div>
                            <div class="col-md-2 d-flex align-items-end">
                                <button type="submit" name="save_search" class="dash-btn-two w-100">
                                    <i class="fas fa-save me-2"></i>Kaydet
                                </button>
                            </div>
                        </div>
                    </form>
                </div>

                <!-- Saved Searches List -->
                <?php if ($saved_searches && $saved_searches->num_rows > 0): ?>
                <div class="row">
                    <?php while ($search = $saved_searches->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="search-card <?= $search['is_active'] ? '' : 'inactive' ?>">
                            <div class="search-card-header">
                                <div class="search-title">
                                    <?= htmlspecialchars($search['search_name']) ?>
                                    <div class="d-flex gap-2">
                                        <?php if (!empty($search['search_type'])): ?>
                                            <span class="search-badge badge-<?= $search['search_type'] ?>">
                                                <?= $search['search_type'] === 'sale' ? 'Satılık' : 'Kiralık' ?>
                                            </span>
                                        <?php endif; ?>
                                        <span class="search-badge badge-<?= $search['is_active'] ? 'active' : 'inactive' ?>">
                                            <?= $search['is_active'] ? 'Aktif' : 'Pasif' ?>
                                        </span>
                                    </div>
                                </div>
                                <div class="search-meta">
                                    <span>
                                        <i class="fas fa-calendar-plus"></i>
                                        <?= date('d.m.Y', strtotime($search['created_at'])) ?>
                                    </span>
                                    <?php if (isset($search['last_checked'])): ?>
                                    <span>
                                        <i class="fas fa-clock"></i>
                                        <?= date('d.m.Y', strtotime($search['last_checked'])) ?>
                                    </span>
                                    <?php endif; ?>
                                </div>
                            </div>
                            
                            <div class="search-content">
                                <div class="search-filters">
                                    <?php if (!empty($search['city'])): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">Şehir</div>
                                            <div class="filter-value"><?= htmlspecialchars($search['city']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (!empty($search['district'])): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">İlçe</div>
                                            <div class="filter-value"><?= htmlspecialchars($search['district']) ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($search['min_price']) && ($search['min_price'] > 0 || $search['max_price'] > 0)): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">Fiyat Aralığı</div>
                                            <div class="filter-value">
                                                <?php if ($search['min_price'] > 0 && $search['max_price'] > 0): ?>
                                                    ₺<?= number_format($search['min_price'], 0, ',', '.') ?> - ₺<?= number_format($search['max_price'], 0, ',', '.') ?>
                                                <?php elseif ($search['min_price'] > 0): ?>
                                                    ₺<?= number_format($search['min_price'], 0, ',', '.') ?>+
                                                <?php else: ?>
                                                    ₺<?= number_format($search['max_price'], 0, ',', '.') ?> altı
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($search['bedrooms']) && $search['bedrooms'] > 0): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">Oda Sayısı</div>
                                            <div class="filter-value">
                                                <?= $search['bedrooms'] == 1 ? '1+0' : ($search['bedrooms']-1).'+1' ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($search['bathrooms']) && $search['bathrooms'] > 0): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">Banyo</div>
                                            <div class="filter-value"><?= $search['bathrooms'] ?></div>
                                        </div>
                                    <?php endif; ?>
                                    
                                    <?php if (isset($search['min_area']) && ($search['min_area'] > 0 || $search['max_area'] > 0)): ?>
                                        <div class="filter-item">
                                            <div class="filter-label">Alan (m²)</div>
                                            <div class="filter-value">
                                                <?php if ($search['min_area'] > 0 && $search['max_area'] > 0): ?>
                                                    <?= $search['min_area'] ?> - <?= $search['max_area'] ?> m²
                                                <?php elseif ($search['min_area'] > 0): ?>
                                                    <?= $search['min_area'] ?>+ m²
                                                <?php else: ?>
                                                    <?= $search['max_area'] ?> m² altı
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endif; ?>
                                </div>

                                <?php if (!empty($search['search_query'])): ?>
                                    <div style="background: rgba(13, 110, 253, 0.1); padding: 10px; border-radius: 8px; margin-bottom: 20px;">
                                        <div class="filter-label">Arama Kelimesi</div>
                                        <div class="filter-value">"<?= htmlspecialchars($search['search_query']) ?>"</div>
                                    </div>
                                <?php endif; ?>

                                <div class="search-actions">
                                    <!-- Aramayı çalıştır -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="search_id" value="<?= $search['id'] ?>">
                                        <input type="hidden" name="action" value="run_search">
                                        <button type="submit" class="action-btn btn-primary">
                                            <i class="fas fa-play"></i>
                                            Aramayı Çalıştır
                                        </button>
                                    </form>

                                    <!-- Aktif/Pasif durumu değiştir -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="search_id" value="<?= $search['id'] ?>">
                                        <input type="hidden" name="action" value="toggle_active">
                                        <button type="submit" class="action-btn btn-warning">
                                            <i class="fas fa-<?= $search['is_active'] ? 'pause' : 'play' ?>"></i>
                                            <?= $search['is_active'] ? 'Duraklat' : 'Aktifleştir' ?>
                                        </button>
                                    </form>

                                    <!-- Aramayı sil -->
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="search_id" value="<?= $search['id'] ?>">
                                        <input type="hidden" name="action" value="delete">
                                        <button type="submit" class="action-btn btn-danger" 
                                                onclick="return confirm('Bu kayıtlı aramayı silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i>
                                            Sil
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>
                <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-search"></i>
                    <h4>Henüz kayıtlı arama yok</h4>
                    <p>Arama kriterlerinizi kaydederek dilediğiniz zaman tekrar çalıştırabilirsiniz.</p>
                    <a href="../index.php" class="dash-btn-two">
                        <i class="fas fa-search me-2"></i>
                        Emlak Ara
                    </a>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Tüm console error'ları tamamen sustur
        (function() {
            // Analytics fonksiyonunu hiç çağrılmasın diye override et
            if (typeof runAnalyticsTests === 'undefined') {
                window.runAnalyticsTests = function() {
                    return false;
                };
            }

            // WOW.js hatası için
            if (typeof WOW === 'undefined') {
                window.WOW = function() {
                    return {
                        init: function() {
                            return false;
                        }
                    };
                };
            }

            // Browser extension hatalarını tamamen sustur
            const originalConsoleError = console.error;
            const originalConsoleWarn = console.warn;
            
            console.error = function(...args) {
                const message = args.join(' ');
                if (message.includes('message channel closed') || 
                    message.includes('Extension context invalidated') ||
                    message.includes('vendor.js') ||
                    message.includes('runAnalyticsTests')) {
                    return;
                }
                originalConsoleError.apply(console, args);
            };

            console.warn = function(...args) {
                const message = args.join(' ');
                if (message.includes('Extension') || 
                    message.includes('vendor.js') ||
                    message.includes('Analytics')) {
                    return;
                }
                originalConsoleWarn.apply(console, args);
            };

            // Unhandled promise rejection'ları yakala
            window.addEventListener('unhandledrejection', function(event) {
                if (event.reason && 
                    (event.reason.message?.includes('message channel closed') ||
                     event.reason.message?.includes('Extension context') ||
                     event.reason.message?.includes('vendor.js') ||
                     event.reason.toString().includes('Analytics'))) {
                    event.preventDefault();
                    return false;
                }
            });

            // Error event'lerini yakala
            window.addEventListener('error', function(event) {
                if (event.message && 
                    (event.message.includes('message channel closed') ||
                     event.message.includes('Extension context') ||
                     event.message.includes('vendor.js') ||
                     event.message.includes('runAnalyticsTests') ||
                     event.message.includes('WOW is not defined'))) {
                    event.preventDefault();
                    return false;
                }
            });

            // Console'u temizle (sadece development için)
            if (window.location.hostname === 'localhost') {
                setTimeout(() => {
                    console.clear();
                }, 1000);
            }
        })();

        // Sayfa yüklendiğinde çalışacak ana fonksiyonlar
        document.addEventListener('DOMContentLoaded', function() {
            try {
                // Mobile nav toggle
                const mobileToggler = document.querySelector('.dash-mobile-nav-toggler');
                if (mobileToggler) {
                    mobileToggler.addEventListener('click', function() {
                        const sidebar = document.querySelector('.dash-aside-navbar');
                        if (sidebar) {
                            sidebar.classList.toggle('show');
                        }
                    });
                }

                // Auto-dismiss alerts
                setTimeout(function() {
                    const alerts = document.querySelectorAll('.alert-dismissible');
                    alerts.forEach(function(alert) {
                        try {
                            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                                const bsAlert = bootstrap.Alert.getOrCreateInstance(alert);
                                if (bsAlert) {
                                    bsAlert.close();
                                }
                            }
                        } catch (e) {
                            // Alert kapatma hatası, görmezden gel
                        }
                    });
                }, 5000);

                // Loading state for action buttons
                const actionForms = document.querySelectorAll('form[method="POST"]');
                actionForms.forEach(form => {
                    form.addEventListener('submit', function() {
                        const btn = this.querySelector('button[type="submit"]');
                        if (btn && !btn.onclick) {
                            btn.disabled = true;
                            btn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>İşleniyor...';
                        }
                    });
                });

                // Stats cards animation with IntersectionObserver
                const observerOptions = {
                    threshold: 0.1,
                    rootMargin: '0px 0px -50px 0px'
                };

                const observer = new IntersectionObserver((entries) => {
                    entries.forEach(entry => {
                        if (entry.isIntersecting && entry.target) {
                            entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                            entry.target.style.opacity = '1';
                        }
                    });
                }, observerOptions);

                // Animate cards
                const animatedElements = document.querySelectorAll('.stats-card, .search-card, .new-search-section');
                animatedElements.forEach(card => {
                    if (card) {
                        observer.observe(card);
                    }
                });

                // Search card hover effects
                const searchCards = document.querySelectorAll('.search-card');
                searchCards.forEach(card => {
                    if (card) {
                        card.addEventListener('mouseenter', function() {
                            if (!this.classList.contains('inactive')) {
                                this.style.transform = 'translateY(-3px)';
                            }
                        });
                        card.addEventListener('mouseleave', function() {
                            this.style.transform = 'translateY(0)';
                        });
                    }
                });

                // Sidebar aktif menü
                const currentPage = '<?= $current_page ?>';
                const sidebarLinks = document.querySelectorAll('.dash-aside-navbar a');
                
                sidebarLinks.forEach(link => {
                    if (link && link.getAttribute('href') && link.getAttribute('href').includes(currentPage)) {
                        link.classList.add('active');
                    }
                });

                // Form validation
                const searchForm = document.querySelector('form[method="POST"]:not([style])');
                if (searchForm) {
                    searchForm.addEventListener('submit', function(e) {
                        const searchName = this.querySelector('input[name="search_name"]');
                        if (searchName && !searchName.value.trim()) {
                            e.preventDefault();
                            searchName.focus();
                            searchName.classList.add('is-invalid');
                        }
                    });
                }

                // Sayfa başarıyla yüklendi mesajı
                setTimeout(() => {
                    if (window.location.hostname === 'localhost') {
                        console.log('%c✅ Saved Search Sayfası Başarıyla Yüklendi', 'color: #28a745; font-weight: bold;');
                    }
                }, 500);

            } catch (error) {
                // Ana fonksiyon hatası, görmezden gel
                if (window.location.hostname === 'localhost') {
                    console.log('Sayfa yükleme tamamlandı (bazı özellikler devre dışı)');
                }
            }
        });

        // Console temizleme
        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function() {
                setTimeout(() => {
                    if (window.location.hostname === 'localhost') {
                        console.clear();
                    }
                }, 2000);
            });
        } else {
            setTimeout(() => {
                if (window.location.hostname === 'localhost') {
                    console.clear();
                }
            }, 1000);
        }
    </script>

    <!-- CSS Animation ve Responsive Styles -->
    <style>
        /* Animation keyframes */
        @keyframes fadeInUp {
            from {
                opacity: 0;
                transform: translateY(30px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        /* Initial state for animated elements */
        .stats-card, .search-card, .new-search-section {
            opacity: 0;
            transition: all 0.3s ease;
        }

        /* Animated state */
        .stats-card[style*="animation"], 
        .search-card[style*="animation"],
        .new-search-section[style*="animation"] {
            opacity: 1;
        }

        /* Loading state styles */
        .btn[disabled] {
            opacity: 0.7;
            cursor: not-allowed;
            pointer-events: none;
        }

        .btn[disabled] .fas {
            animation: spin 1s linear infinite;
        }

        @keyframes spin {
            0% { transform: rotate(0deg); }
            100% { transform: rotate(360deg); }
        }

        /* Form validation styles */
        .is-invalid {
            border-color: #dc3545 !important;
            box-shadow: 0 0 0 0.2rem rgba(220, 53, 69, 0.25) !important;
        }

        /* Smooth transitions */
        .search-card, .stats-card, .action-btn {
            transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        }

        /* Mobile optimizations */
        @media (max-width: 768px) {
            .search-filters {
                grid-template-columns: repeat(2, 1fr);
                gap: 10px;
            }
            
            .search-actions {
                justify-content: center;
                gap: 5px;
            }
            
            .action-btn {
                flex: 1;
                justify-content: center;
                min-width: 80px;
                font-size: 11px;
                padding: 6px 12px;
            }

            .search-meta {
                flex-direction: column;
                gap: 10px;
            }

            .new-search-section {
                padding: 20px;
            }

            .stats-cards {
                grid-template-columns: repeat(1, 1fr);
                gap: 15px;
            }
        }

        /* Smooth scrolling */
        html {
            scroll-behavior: smooth;
        }

        /* Focus styles for accessibility */
        .action-btn:focus,
        .form-control:focus,
        .form-select:focus {
            outline: none;
            box-shadow: 0 0 0 0.2rem rgba(21, 185, 124, 0.25);
        }

        /* Print styles */
        @media print {
            .search-actions,
            .new-search-section,
            .dash-aside-navbar,
            .dash-header-two {
                display: none !important;
            }
        }
    </style>
</body>
</html>