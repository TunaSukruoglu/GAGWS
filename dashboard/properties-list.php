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

if (!$user) {
    header("Location: ../index.php");
    exit;
}

// Sayfa başlığı ve aktif menü
$page_title = "İlanlarım";
$current_page = 'properties-list';

// Sayfalama ve filtreleme güvenliği
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 12; // Daha iyi görünüm için 12'ye çıkardım
$offset = ($page - 1) * $limit;

$status_filter = isset($_GET['status']) ? $conn->real_escape_string($_GET['status']) : '';
$category_filter = isset($_GET['category']) ? $conn->real_escape_string($_GET['category']) : '';

// Query oluşturma - güvenli hale getirildi
$where_conditions = ["user_id = " . intval($user_id)];

if ($status_filter && in_array($status_filter, ['active', 'pending', 'inactive', 'sold'])) {
    $where_conditions[] = "status = '$status_filter'";
}

if ($category_filter) {
    $where_conditions[] = "category = '$category_filter'";
}

$where_clause = "WHERE " . implode(" AND ", $where_conditions);

// Toplam sayı
$total_query = "SELECT COUNT(*) as total FROM properties $where_clause";
$total_result = $conn->query($total_query);
$total_properties = $total_result ? $total_result->fetch_assoc()['total'] : 0;
$total_pages = $total_properties > 0 ? ceil($total_properties / $limit) : 1;

// İlanları getir - NULL kontrolü ekle
$properties_query = "SELECT * FROM properties $where_clause ORDER BY created_at DESC LIMIT $limit OFFSET $offset";
$properties_result = $conn->query($properties_query);

// Silme işlemi
if (isset($_POST['delete_property']) && isset($_POST['property_id'])) {
    $property_id = intval($_POST['property_id']);
    
    if ($property_id > 0) {
        // Bu ilanın sahibi mi ve bilgileriyle birlikte kontrol et
        $check_owner = $conn->prepare("SELECT id, title, images FROM properties WHERE id = ? AND user_id = ?");
        $check_owner->bind_param("ii", $property_id, $user_id);
        $check_owner->execute();
        $property_info = $check_owner->get_result()->fetch_assoc();
        
        if ($property_info) {
            // Resimleri sil (eğer varsa)
            if (!empty($property_info['images'])) {
                $images = explode(',', $property_info['images']);
                foreach ($images as $image) {
                    $image_path = '../uploads/' . trim($image);
                    if (file_exists($image_path)) {
                        unlink($image_path);
                    }
                }
            }
            
            // Veritabanından sil
            $delete_stmt = $conn->prepare("DELETE FROM properties WHERE id = ? AND user_id = ?");
            $delete_stmt->bind_param("ii", $property_id, $user_id);
            
            if ($delete_stmt->execute()) {
                $_SESSION['success_message'] = "İlan başarıyla silindi: " . htmlspecialchars($property_info['title']);
                // Sayfa yeniden yüklenmesi için redirect
                header("Location: properties-list.php" . ($_SERVER['QUERY_STRING'] ? '?' . $_SERVER['QUERY_STRING'] : ''));
                exit;
            } else {
                $error_message = "İlan silinirken veritabanı hatası oluştu!";
            }
        } else {
            $error_message = "Bu ilanı silme yetkiniz yok veya ilan bulunamadı!";
        }
    } else {
        $error_message = "Geçersiz ilan ID!";
    }
}

// İstatistikleri hesapla
try {
    $active_query = "SELECT COUNT(*) as count FROM properties WHERE user_id = $user_id AND status = 'active'";
    $active_result = $conn->query($active_query);
    $active_count = $active_result ? $active_result->fetch_assoc()['count'] : 0;
    
    $pending_query = "SELECT COUNT(*) as count FROM properties WHERE user_id = $user_id AND status = 'pending'";
    $pending_result = $conn->query($pending_query);
    $pending_count = $pending_result ? $pending_result->fetch_assoc()['count'] : 0;
    
    $inactive_query = "SELECT COUNT(*) as count FROM properties WHERE user_id = $user_id AND status = 'inactive'";
    $inactive_result = $conn->query($inactive_query);
    $inactive_count = $inactive_result ? $inactive_result->fetch_assoc()['count'] : 0;
    
    // Approved ve rejected için de hesapla
    $approved_query = "SELECT COUNT(*) as count FROM properties WHERE user_id = $user_id AND status = 'approved'";
    $approved_result = $conn->query($approved_query);
    $approved_count = $approved_result ? $approved_result->fetch_assoc()['count'] : 0;
    
    $rejected_query = "SELECT COUNT(*) as count FROM properties WHERE user_id = $user_id AND status = 'rejected'";
    $rejected_result = $conn->query($rejected_query);
    $rejected_count = $rejected_result ? $rejected_result->fetch_assoc()['count'] : 0;
} catch (Exception $e) {
    $active_count = $pending_count = $inactive_count = $approved_count = $rejected_count = 0;
}
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
        /* Properties List Specific Styles */
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            margin-bottom: 30px;
        }

        .filter-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .property-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 25px;
            height: 100%;
        }

        .property-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 12px 40px rgba(0,0,0,0.12);
        }

        .property-image {
            height: 220px;
            background: linear-gradient(135deg, #0d6efd, #0b5ed7);
            display: flex;
            align-items: center;
            justify-content: center;
            position: relative;
            overflow: hidden;
        }

        .property-image i {
            font-size: 56px;
            color: white;
            opacity: 0.8;
        }

        .status-badge {
            position: absolute;
            top: 15px;
            right: 15px;
            padding: 8px 16px;
            border-radius: 25px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .status-badge.bg-success {
            background: #0d6efd !important;
        }

        .status-badge.bg-warning {
            background: #ffc107 !important;
            color: #212529 !important;
            animation: pulse 2s infinite;
        }

        .status-badge.bg-danger {
            background: #dc3545 !important;
        }

        .property-content {
            padding: 25px;
            display: flex;
            flex-direction: column;
            height: calc(100% - 220px);
        }

        .property-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 12px;
            line-height: 1.3;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
        }

        .property-price {
            font-size: 22px;
            font-weight: 700;
            color: var(--secondary-color);
            margin-bottom: 15px;
        }

        .property-details {
            display: flex;
            gap: 20px;
            margin-bottom: 15px;
            font-size: 14px;
            color: #666;
            flex-wrap: wrap;
        }

        .property-details span {
            display: flex;
            align-items: center;
            gap: 6px;
        }

        .property-location {
            margin-bottom: 20px;
            font-size: 14px;
            color: #666;
            display: flex;
            align-items: center;
            gap: 5px;
        }

        .property-actions {
            display: flex;
            gap: 8px;
            margin-top: auto;
        }

        .btn-outline-custom {
            border: 1px solid #E6E6E6;
            color: #666;
            padding: 10px 16px;
            border-radius: 10px;
            font-size: 13px;
            font-weight: 500;
            transition: all 0.3s ease;
            flex: 1;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 6px;
            text-decoration: none;
        }

        .btn-outline-custom:hover {
            border-color: var(--secondary-color);
            color: var(--secondary-color);
            transform: translateY(-2px);
        }

        .btn-outline-custom.text-danger:hover {
            border-color: #dc3545;
            color: #dc3545;
            background: rgba(220, 53, 69, 0.1);
        }

        .pagination-custom {
            display: flex;
            justify-content: center;
            margin-top: 40px;
        }

        .pagination-custom .page-link {
            border: none;
            padding: 12px 18px;
            margin: 0 3px;
            border-radius: 10px;
            color: #666;
            font-weight: 500;
            transition: all 0.3s ease;
        }

        .pagination-custom .page-item.active .page-link {
            background: var(--secondary-color);
            color: white;
            transform: translateY(-2px);
        }

        .pagination-custom .page-link:hover {
            background: var(--light-bg);
            color: var(--primary-color);
            transform: translateY(-2px);
        }

        .empty-state {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            text-align: center;
            padding: 60px 30px;
        }

        .empty-state i {
            font-size: 72px;
            color: #ddd;
            margin-bottom: 20px;
        }

        .empty-state h4 {
            color: var(--primary-color);
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
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 0.2rem rgba(21, 185, 124, 0.25);
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

        @keyframes pulse {
            0% { opacity: 1; }
            50% { opacity: 0.6; }
            100% { opacity: 1; }
        }

        @media (max-width: 768px) {
            .property-details {
                flex-direction: column;
                gap: 10px;
            }
            
            .property-actions {
                flex-direction: column;
                gap: 10px;
            }
            
            .btn-outline-custom {
                width: 100%;
                justify-content: center;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <!-- Include Header -->
                <?php include 'includes/header.php'; ?>

                <h2 class="main-title d-block d-lg-none"><?= $page_title ?></h2>
                
                <!-- Success/Error Messages -->
                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $success_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error_message ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $total_properties ?></div>
                        <div class="stats-label">Toplam İlan</div>
                        <div class="stats-change positive">
                            <i class="fas fa-chart-line"></i>
                            <span>Aktif</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $approved_count ?></div>
                        <div class="stats-label">Onaylı</div>
                        <div class="stats-change positive">
                            <i class="fas fa-thumbs-up"></i>
                            <span>Yayında</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $pending_count ?></div>
                        <div class="stats-label">Beklemede</div>
                        <div class="stats-change <?= $pending_count > 0 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-<?= $pending_count > 0 ? 'hourglass-half' : 'check' ?>"></i>
                            <span><?= $pending_count > 0 ? 'Onay bekliyor' : 'Hepsi onaylı' ?></span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-times-circle"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $rejected_count ?></div>
                        <div class="stats-label">Reddedilen</div>
                        <div class="stats-change <?= $rejected_count > 0 ? 'negative' : 'positive' ?>">
                            <i class="fas fa-<?= $rejected_count > 0 ? 'exclamation' : 'check' ?>"></i>
                            <span><?= $rejected_count > 0 ? 'İnceleme gerekli' : 'Hepsi onaylı' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Filter Section -->
                <div class="filter-section">
                    <div class="filter-title">
                        <i class="fas fa-filter"></i>
                        Filtreler
                    </div>
                    <form method="GET" class="row g-3">
                        <div class="col-md-4">
                            <label class="form-label">Durum</label>
                            <select name="status" class="form-select">
                                <option value="">Tüm Durumlar</option>
                                <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Aktif (<?= $active_count ?>)</option>
                                <option value="pending" <?= $status_filter === 'pending' ? 'selected' : '' ?>>Onay Bekleyen (<?= $pending_count ?>)</option>
                                <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Pasif (<?= $inactive_count ?>)</option>
                            </select>
                        </div>
                        <div class="col-md-4">
                            <label class="form-label">Kategori</label>
                            <select name="category" class="form-select">
                                <option value="">Tüm Kategoriler</option>
                                <option value="Daire" <?= $category_filter === 'Daire' ? 'selected' : '' ?>>Daire</option>
                                <option value="Villa" <?= $category_filter === 'Villa' ? 'selected' : '' ?>>Villa</option>
                                <option value="Müstakil Ev" <?= $category_filter === 'Müstakil Ev' ? 'selected' : '' ?>>Müstakil Ev</option>
                                <option value="Arsa" <?= $category_filter === 'Arsa' ? 'selected' : '' ?>>Arsa</option>
                                <option value="İş Yeri" <?= $category_filter === 'İş Yeri' ? 'selected' : '' ?>>İş Yeri</option>
                                <option value="Ofis" <?= $category_filter === 'Ofis' ? 'selected' : '' ?>>Ofis</option>
                            </select>
                        </div>
                        <div class="col-md-4 d-flex align-items-end">
                            <button type="submit" class="dash-btn-two w-100">
                                <i class="fas fa-filter me-2"></i>Filtrele
                            </button>
                        </div>
                    </form>
                </div>

                <!-- Properties Grid -->
                <?php if ($properties_result && $properties_result->num_rows > 0): ?>
                <div class="row">
                    <?php while ($property = $properties_result->fetch_assoc()): ?>
                    <div class="col-lg-6 col-xl-4">
                        <div class="property-card">
                            <div class="property-image">
                                <?php
                                // Resim URL'ini al
                                $image_url = '';
                                if (!empty($property['main_image'])) {
                                    $image_file = basename($property['main_image']);
                                    // Dashboard klasörünü önce kontrol et
                                    $dashboard_path = 'uploads/properties/' . $image_file;
                                    $main_path = '../uploads/properties/' . $image_file;
                                    
                                    if (file_exists($dashboard_path)) {
                                        $image_url = $dashboard_path;
                                    } elseif (file_exists($main_path)) {
                                        $image_url = $main_path;
                                    }
                                } else if (!empty($property['images'])) {
                                    // Main image yoksa images array'inden ilkini al
                                    $images = json_decode($property['images'], true);
                                    if (is_array($images) && !empty($images)) {
                                        $image_file = basename($images[0]);
                                        $dashboard_path = 'uploads/properties/' . $image_file;
                                        $main_path = '../uploads/properties/' . $image_file;
                                        
                                        if (file_exists($dashboard_path)) {
                                            $image_url = $dashboard_path;
                                        } elseif (file_exists($main_path)) {
                                            $image_url = $main_path;
                                        }
                                    }
                                }
                                
                                if (!empty($image_url)): ?>
                                    <img src="<?= htmlspecialchars($image_url) ?>" alt="<?= htmlspecialchars($property['title']) ?>" 
                                         style="width: 100%; height: 200px; object-fit: cover; border-radius: 8px;">
                                <?php else: ?>
                                    <div style="width: 100%; height: 200px; background: #f8f9fa; display: flex; align-items: center; justify-content: center; border-radius: 8px;">
                                        <i class="fas fa-home" style="font-size: 48px; color: #6c757d;"></i>
                                    </div>
                                <?php endif; ?>
                                <?php
                                $status_class = match($property['status']) {
                                    'active' => 'bg-success',
                                    'approved' => 'bg-success',
                                    'pending' => 'bg-warning',
                                    'inactive' => 'bg-secondary',
                                    'rejected' => 'bg-danger',
                                    'sold' => 'bg-info',
                                    default => 'bg-secondary'
                                };
                                
                                $status_text = match($property['status']) {
                                    'active' => 'Aktif',
                                    'approved' => 'Onaylı',
                                    'pending' => 'Beklemede',
                                    'inactive' => 'Pasif',
                                    'rejected' => 'Reddedildi',
                                    'sold' => 'Satıldı',
                                    default => 'Durum: ' . ($property['status'] ?? 'Belirsiz')
                                };
                                ?>
                                <span class="status-badge <?= $status_class ?> text-white"><?= $status_text ?></span>
                            </div>
                            <div class="property-content">
                                <h6 class="property-title"><?= htmlspecialchars($property['title'] ?? 'Başlık Yok') ?></h6>
                                <div class="property-price"><?= number_format($property['price'] ?? 0, 0, ',', '.') ?> ₺</div>
                                <div class="property-details">
                                    <span><i class="fas fa-bed"></i> <?= $property['bedrooms'] ?? 0 ?></span>
                                    <span><i class="fas fa-bath"></i> <?= $property['bathrooms'] ?? 0 ?></span>
                                    <span><i class="fas fa-expand-arrows-alt"></i> <?= $property['area'] ?? $property['area_gross'] ?? 0 ?>m²</span>
                                </div>
                                <div class="property-location">
                                    <i class="fas fa-map-marker-alt"></i>
                                    <?= htmlspecialchars($property['city'] ?? 'Şehir Belirtilmemiş') ?>, <?= htmlspecialchars($property['district'] ?? '') ?>
                                </div>
                                <div class="property-actions">
                                    <a href="../property-details.php?id=<?= $property['id'] ?>" class="btn-outline-custom" target="_blank">
                                        <i class="fas fa-eye"></i>
                                        <span>Görüntüle</span>
                                    </a>
                                    <a href="edit-property.php?id=<?= $property['id'] ?>" class="btn-outline-custom">
                                        <i class="fas fa-edit"></i>
                                        <span>Düzenle</span>
                                    </a>
                                    <!-- DÜZELTILMIŞ SILME FORMU -->
                                    <form method="POST" class="d-inline" style="flex: 1;" onsubmit="return handleDelete(this, event)">
                                        <input type="hidden" name="property_id" value="<?= $property['id'] ?>">
                                        <input type="hidden" name="delete_property" value="1">
                                        <button type="submit" class="btn-outline-custom text-danger w-100" data-property-title="<?= htmlspecialchars($property['title']) ?>">
                                            <i class="fas fa-trash"></i>
                                            <span>Sil</span>
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endwhile; ?>
                </div>

                <!-- Pagination -->
                <?php if ($total_pages > 1): ?>
                <nav aria-label="İlan sayfalama">
                    <ul class="pagination pagination-custom">
                        <?php if ($page > 1): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page - 1 ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>">
                                <i class="fas fa-chevron-left"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                        
                        <?php 
                        $start_page = max(1, $page - 2);
                        $end_page = min($total_pages, $page + 2);
                        ?>
                        
                        <?php for ($i = $start_page; $i <= $end_page; $i++): ?>
                        <li class="page-item <?= $i === $page ? 'active' : '' ?>">
                            <a class="page-link" href="?page=<?= $i ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>"><?= $i ?></a>
                        </li>
                        <?php endfor; ?>
                        
                        <?php if ($page < $total_pages): ?>
                        <li class="page-item">
                            <a class="page-link" href="?page=<?= $page + 1 ?>&status=<?= urlencode($status_filter) ?>&category=<?= urlencode($category_filter) ?>">
                                <i class="fas fa-chevron-right"></i>
                            </a>
                        </li>
                        <?php endif; ?>
                    </ul>
                </nav>
                <?php endif; ?>

                <?php else: ?>
                <!-- Empty State -->
                <div class="empty-state">
                    <i class="fas fa-home"></i>
                    <h4>
                        <?php if ($status_filter || $category_filter): ?>
                            Filtrelere uygun ilan bulunamadı
                        <?php else: ?>
                            Henüz ilanınız bulunmuyor
                        <?php endif; ?>
                    </h4>
                    <p>
                        <?php if ($status_filter || $category_filter): ?>
                            Farklı filtreler deneyebilir veya yeni bir ilan ekleyebilirsiniz.
                        <?php else: ?>
                            İlk ilanınızı ekleyerek emlak portföyünüzü oluşturmaya başlayın!
                        <?php endif; ?>
                    </p>
                    <?php if (!$status_filter && !$category_filter): ?>
                        <?php if(($user['can_add_property'] ?? 0) || $user['role'] == 'admin'): ?>
                        <a href="add-property.php" class="dash-btn-two">
                            <i class="fas fa-plus me-2"></i>
                            İlk İlanınızı Ekleyin
                        </a>
                        <?php endif; ?>
                    <?php else: ?>
                        <a href="properties-list.php" class="dash-btn-two me-2">
                            <i class="fas fa-list me-2"></i>
                            Tüm İlanları Göster
                        </a>
                        <?php if(($user['can_add_property'] ?? 0) || $user['role'] == 'admin'): ?>
                        <a href="add-property.php" class="dash-btn-two" style="background: rgba(13, 110, 253, 0.1); color: #0d6efd; border: 1px solid #0d6efd;">
                            <i class="fas fa-plus me-2"></i>
                            Yeni İlan Ekle
                        </a>
                        <?php endif; ?>
                    <?php endif; ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <!-- WOW.js ekle -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <!-- Theme.js'den önce WOW.js'i başlat -->
    <script>
    // WOW.js'i güvenli şekilde başlat
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof WOW !== 'undefined') {
            new WOW().init();
        }
    });
    </script>
    <script src="../js/theme.js"></script>
    
    <script>
// Mobile nav toggle
document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
    document.querySelector('.dash-aside-navbar').classList.toggle('show');
});

// Auto-dismiss alerts
setTimeout(function() {
    var alerts = document.querySelectorAll('.alert');
    alerts.forEach(function(alert) {
        if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
            var bsAlert = new bootstrap.Alert(alert);
            bsAlert.close();
        }
    });
}, 5000);

// Auto-submit filters on change
document.querySelectorAll('.filter-section select').forEach(select => {
    select.addEventListener('change', function() {
        this.closest('form').submit();
    });
});

// DÜZELTILMIŞ SILME FONKSİYONU
function handleDelete(form, event) {
    const button = form.querySelector('button[type="submit"]');
    const propertyTitle = button.getAttribute('data-property-title') || 'Bu ilan';
    
    // Confirm dialog
    if (!confirm(`${propertyTitle} adlı ilanı silmek istediğinizden emin misiniz?\n\nBu işlem geri alınamaz!`)) {
        event.preventDefault();
        return false;
    }
    
    // Butonu devre dışı bırak
    button.innerHTML = '<i class="fas fa-spinner fa-spin"></i><span>Siliniyor...</span>';
    button.disabled = true;
    
    // Form submit edilsin
    return true;
}

// Add animation to pending badges
document.querySelectorAll('.status-badge.bg-warning').forEach(badge => {
    badge.style.animation = 'pulse 2s infinite';
});

// Stats cards animation - WOW.js olmadan
const observerOptions = {
    threshold: 0.1,
    rootMargin: '0px 0px -50px 0px'
};

const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
        if (entry.isIntersecting) {
            entry.target.style.opacity = '0';
            entry.target.style.transform = 'translateY(20px)';
            entry.target.style.transition = 'all 0.6s ease';
            
            setTimeout(() => {
                entry.target.style.opacity = '1';
                entry.target.style.transform = 'translateY(0)';
            }, 100);
        }
    });
}, observerOptions);

document.querySelectorAll('.stats-card, .property-card').forEach(card => {
    observer.observe(card);
});
    </script>
</body>
</html>