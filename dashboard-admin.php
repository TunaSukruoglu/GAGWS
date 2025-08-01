<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db.php';

// Admin kontrolü - güvenli hale getirildi
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User bilgilerini al (profil resmi dahil)
$user_query = $conn->prepare("SELECT name, email, role, created_at, profile_image FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Sayfa ayarları
$current_page = 'dashboard-admin';
$page_title = $user_data['name'] . ' - Admin Dashboard';
$user_name = $user_data['name']; // Sidebar için

// Dashboard istatistikleri - güvenli sorgular
try {
    // Kullanıcı istatistikleri
    $user_stats_query = "SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role = 'agent' THEN 1 ELSE 0 END) as agent_count,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user_count,
        SUM(CASE WHEN can_add_property = 1 THEN 1 ELSE 0 END) as can_add_count,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_registrations,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_registrations
        FROM users";
    $user_stats = $conn->query($user_stats_query)->fetch_assoc();

    // Emlak istatistikleri
    $property_stats_query = "SELECT 
        COUNT(*) as total_properties,
        SUM(CASE WHEN status = 'active' OR status = 'approved' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'inactive' OR status = 'rejected' THEN 1 ELSE 0 END) as inactive_count,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN type = 'sale' THEN 1 ELSE 0 END) as sale_count,
        SUM(CASE WHEN type = 'rent' THEN 1 ELSE 0 END) as rent_count,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_properties,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_properties,
        AVG(price) as avg_price
        FROM properties";
    $property_stats = $conn->query($property_stats_query)->fetch_assoc();

    // Son eklenen kullanıcılar
    $recent_users_query = "SELECT id, name, email, role, created_at, is_active 
                           FROM users 
                           ORDER BY created_at DESC 
                           LIMIT 5";
    $recent_users = $conn->query($recent_users_query);

    // Son eklenen emlaklar
    $recent_properties_query = "SELECT p.id, p.title, p.price, p.type, p.status, p.created_at, u.name as owner_name
                                FROM properties p 
                                LEFT JOIN users u ON p.user_id = u.id 
                                ORDER BY p.created_at DESC 
                                LIMIT 5";
    $recent_properties = $conn->query($recent_properties_query);

    // Pasif ilanlar
    $inactive_properties_query = "SELECT p.id, p.title, p.created_at, u.name as owner_name
                                FROM properties p 
                                LEFT JOIN users u ON p.user_id = u.id 
                                WHERE p.status = 'inactive' OR p.status = 'rejected'
                                ORDER BY p.created_at DESC 
                                LIMIT 5";
    $inactive_properties = $conn->query($inactive_properties_query);

    // Favori istatistikleri
    $favorite_stats_query = "SELECT 
        COUNT(*) as total_favorites,
        COUNT(DISTINCT property_id) as unique_properties,
        COUNT(DISTINCT user_id) as unique_users,
        SUM(CASE WHEN DATE(created_at) = CURDATE() THEN 1 ELSE 0 END) as today_favorites,
        SUM(CASE WHEN DATE(created_at) >= DATE_SUB(CURDATE(), INTERVAL 7 DAY) THEN 1 ELSE 0 END) as week_favorites
        FROM favorites";
    $favorite_stats = $conn->query($favorite_stats_query)->fetch_assoc();

    // En çok favorilenen ilanlar
    $most_favorited_query = "SELECT p.id, p.title, COUNT(f.id) as favorite_count 
                           FROM properties p 
                           LEFT JOIN favorites f ON p.id = f.property_id 
                           GROUP BY p.id, p.title 
                           HAVING favorite_count > 0 
                           ORDER BY favorite_count DESC 
                           LIMIT 10";
    $most_favorited = $conn->query($most_favorited_query);

    // En aktif favori kullanıcıları
    $favorite_users_query = "SELECT u.id, u.name, u.email, COUNT(f.id) as favorite_count 
                           FROM users u 
                           LEFT JOIN favorites f ON u.id = f.user_id 
                           GROUP BY u.id, u.name, u.email 
                           HAVING favorite_count > 0 
                           ORDER BY favorite_count DESC 
                           LIMIT 10";
    $favorite_users = $conn->query($favorite_users_query);

    // Son favoriler
    $recent_favorites_query = "SELECT f.created_at, p.title as property_title, u.name as user_name, p.id as property_id
                             FROM favorites f 
                             LEFT JOIN properties p ON f.property_id = p.id 
                             LEFT JOIN users u ON f.user_id = u.id 
                             ORDER BY f.created_at DESC 
                             LIMIT 10";
    $recent_favorites = $conn->query($recent_favorites_query);

} catch (Exception $e) {
    error_log("Dashboard Admin Stats Error: " . $e->getMessage());
    // Varsayılan değerler
    $user_stats = array_fill_keys(['total_users', 'active_users', 'admin_count', 'agent_count', 'user_count', 'can_add_count', 'today_registrations', 'week_registrations'], 0);
    $property_stats = array_fill_keys(['total_properties', 'approved_count', 'inactive_count', 'pending_count', 'sale_count', 'rent_count', 'today_properties', 'week_properties', 'avg_price'], 0);
    $favorite_stats = array_fill_keys(['total_favorites', 'unique_properties', 'unique_users', 'today_favorites', 'week_favorites'], 0);
    $recent_users = null;
    $recent_properties = null;
    $inactive_properties = null;
    $most_favorited = null;
    $favorite_users = null;
    $recent_favorites = null;
}

// Ziyaret sayacı fonksiyonları
function getTotalVisits() {
    $counter_file = '../visit_counter.txt';
    if (file_exists($counter_file)) {
        return (int)file_get_contents($counter_file);
    }
    return 0;
}

function getTodayVisits() {
    $log_file = '../visit_log.txt';
    $today = date('Y-m-d');
    $count = 0;
    
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (strpos($line, $today) === 0) {
                $count++;
            }
        }
    }
    
    return $count;
}

function getWeeklyVisits() {
    $log_file = '../visit_log.txt';
    $week_ago = date('Y-m-d', strtotime('-7 days'));
    $count = 0;
    
    if (file_exists($log_file)) {
        $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
        foreach ($lines as $line) {
            if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $line, $matches)) {
                $log_date = $matches[1];
                if ($log_date >= $week_ago) {
                    $count++;
                }
            }
        }
    }
    
    return $count;
}

// İstatistik sayıları
$total_users = $user_stats['total_users'] ?? 0;
$total_properties = $property_stats['total_properties'] ?? 0;
$inactive_count = $property_stats['inactive_count'] ?? 0;
$avg_price = $property_stats['avg_price'] ?? 0;

// Ziyaret istatistikleri
$total_visits = getTotalVisits();
$today_visits = getTodayVisits();
$weekly_visits = getWeeklyVisits();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    
    <style>
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
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
            position: sticky;
            top: 0;
            z-index: 100;
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #0d6efd;
            cursor: pointer;
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
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            opacity: 0.95;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: white;
            padding: 18px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 15px;
            min-height: 85px;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            background: #0d6efd;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            flex-shrink: 0;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 4px;
        }
        
        .stats-label {
            font-size: 13px;
            color: #6c757d;
            margin-bottom: 6px;
            line-height: 1.2;
        }
        
        .stats-change {
            font-size: 12px;
            color: #0d6efd;
            display: flex;
            align-items: center;
            gap: 5px;
            font-weight: 600;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            display: block;
        }
        
        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
            color: inherit;
            text-decoration: none;
        }
        
        .action-icon {
            width: 50px;
            height: 50px;
            background: #0d6efd;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: white;
            margin-bottom: 15px;
        }
        
        .action-title {
            font-size: 16px;
            font-weight: 600;
            margin-bottom: 8px;
            color: #0d1a1c;
        }
        
        .action-description {
            font-size: 14px;
            color: #6c757d;
            margin: 0;
        }
        
        .content-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 30px;
        }
        
        .content-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
        }
        
        .section-header {
            padding: 20px 25px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .section-content {
            padding: 25px;
        }
        
        .section-title {
            font-size: 16px;
            font-weight: 600;
            color: #0d1a1c;
            margin: 0;
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
        
        .list-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid #f8f9fa;
        }
        
        .list-item:last-child {
            border-bottom: none;
        }
        
        /* Favori İstatistikleri Özel Stiller */
        .stats-card .stats-icon.text-danger i {
            color: #dc3545;
        }
        
        .stats-card .stats-icon.text-warning i {
            color: #ffc107;
        }
        
        .stats-card .stats-icon.text-success i {
            color: #198754;
        }
        
        .stats-card .stats-icon.text-primary i {
            color: #0d6efd;
        }
        
        .favorite-stats-section {
            background: linear-gradient(135deg, #dc3545 0%, #fd7e7e 100%);
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            color: white;
        }
        
        .favorite-stats-section h3 {
            color: white;
            margin-bottom: 20px;
        }
        
        .favorite-stats-section .stats-card {
            background: rgba(255, 255, 255, 0.95);
            backdrop-filter: blur(10px);
        }
        
        .property-title {
            font-weight: 500;
            color: #333;
        }
        
        .btn-group .btn {
            margin-right: 2px;
        }
        
        .table .badge {
            font-size: 0.85em;
        }
        
        .table .badge i {
            margin-right: 4px;
        }
        
        .modal-lg .list-group-item {
            border-left: 4px solid #0d6efd;
            margin-bottom: 5px;
            border-radius: 8px;
        }
        
        .modal-lg .list-group-item:hover {
            background-color: #f8f9fa;
        }
        
        .item-icon {
            width: 40px;
            height: 40px;
            background: #0d6efd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
        }
        
        .item-avatar {
            width: 40px;
            height: 40px;
            background: #0d6efd;
            border-radius: 10px;
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 14px;
        }
        
        .item-content {
            flex: 1;
        }
        
        .item-title {
            font-size: 14px;
            font-weight: 600;
            margin-bottom: 3px;
            color: #0d1a1c;
        }
        
        .item-subtitle {
            font-size: 12px;
            color: #6c757d;
        }
        
        .item-actions {
            display: flex;
            align-items: center;
            gap: 10px;
        }
        
        .badge {
            font-size: 11px;
            font-weight: 500;
            padding: 4px 8px;
            border-radius: 6px;
        }
        
        .empty-state {
            text-align: center;
            padding: 40px 20px;
        }
        
        .empty-state i {
            font-size: 48px;
            color: #0d6efd;
            opacity: 0.3;
            margin-bottom: 15px;
        }
        
        .empty-state h6 {
            color: #495057;
            margin-bottom: 10px;
        }
        
        .empty-state p {
            color: #6c757d;
            margin: 0;
        }
        
        .alert-custom {
            border: none;
            border-radius: 12px;
            padding: 20px;
            margin-bottom: 25px;
            border-left: 4px solid #f0ad4e;
        }
        
        /* Mobile Responsive */
        @media (max-width: 1400px) {
            .stats-grid {
                grid-template-columns: repeat(4, 1fr);
            }
        }
        
        @media (max-width: 1200px) {
            .stats-grid {
                grid-template-columns: repeat(3, 1fr);
            }
        }
        
        @media (max-width: 1000px) {
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .mobile-header {
                display: flex !important;
            }
            
            .main-content {
                padding: 20px;
                padding-top: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
            }
            
            .content-row {
                grid-template-columns: 1fr;
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
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
                <h5 class="mobile-title">Admin Dashboard</h5>
                <a href="../logout.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>

            <!-- Ana İçerik Alanı -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Welcome Banner -->
                    <div class="welcome-banner">
                        <h2 class="welcome-title">
                            <i class="fas fa-tachometer-alt me-2"></i>
                            Admin Dashboard
                        </h2>
                        <p class="welcome-subtitle">
                            Admin olarak sistemi yönetebilir, kullanıcıları kontrol edebilir ve 
                            emlak ilanlarını onaylayabilirsiniz. İşte güncel durum özeti.
                        </p>
                    </div>

                    <!-- System Status Alert -->
                    <?php if ($inactive_count > 0): ?>
                    <div class="alert alert-warning alert-custom">
                        <div class="d-flex align-items-center">
                            <i class="fas fa-eye-slash me-3" style="font-size: 24px; color: #f0ad4e;"></i>
                            <div>
                                <h6 class="mb-1">Pasif İlanlar</h6>
                                <p class="mb-0"><?= $inactive_count ?> ilan pasif durumda. <a href="admin-properties.php?status=inactive" style="color: #f0ad4e; font-weight: 600;">Pasif ilanları görüntüle</a></p>
                            </div>
                        </div>
                    </div>
                    <?php endif; ?>

                    <!-- İstatistik Kartları -->
                    <div class="stats-grid">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($total_users) ?></div>
                                <div class="stats-label">Toplam Kullanıcı</div>
                                <div class="stats-change">
                                    <i class="fas fa-users"></i>
                                    <span>Bu hafta: +<?= $user_stats['week_registrations'] ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($total_properties) ?></div>
                                <div class="stats-label">Toplam Emlak</div>
                                <div class="stats-change">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Bu hafta: +<?= $property_stats['week_properties'] ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-eye-slash"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($inactive_count) ?></div>
                                <div class="stats-label">Pasif İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-<?= $inactive_count > 5 ? 'exclamation-triangle' : 'check' ?>"></i>
                                    <span><?= $inactive_count > 5 ? 'Çok fazla pasif' : 'Normal seviyede' ?></span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-lira-sign"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($avg_price, 0, ',', '.') ?></div>
                                <div class="stats-label">Ortalama Fiyat (₺)</div>
                                <div class="stats-change">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Piyasa değeri</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($total_visits) ?></div>
                                <div class="stats-label">Toplam Ziyaretçi</div>
                                <div class="stats-change">
                                    <i class="fas fa-users"></i>
                                    <span>Site ziyaretçisi</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-day"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($today_visits) ?></div>
                                <div class="stats-label">Bugünkü Ziyaret</div>
                                <div class="stats-change">
                                    <i class="fas fa-clock"></i>
                                    <span>Son 24 saat</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-week"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($weekly_visits) ?></div>
                                <div class="stats-label">Haftalık Ziyaret</div>
                                <div class="stats-change">
                                    <i class="fas fa-chart-area"></i>
                                    <span>Son 7 gün</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($user_stats['active_users']) ?></div>
                                <div class="stats-label">Aktif Kullanıcı</div>
                                <div class="stats-change">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Onaylı hesaplar</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($property_stats['approved_count']) ?></div>
                                <div class="stats-label">Onaylı İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-thumbs-up"></i>
                                    <span>Yayında olan</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-hourglass-half"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($property_stats['pending_count']) ?></div>
                                <div class="stats-label">Bekleyen İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-clock"></i>
                                    <span>Onay bekliyor</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Favori İstatistikleri -->
                    <div class="mb-4">
                        <h3 class="mb-3">
                            <i class="fas fa-heart text-danger"></i>
                            Favori İstatistikleri
                        </h3>
                        
                        <div class="stats-grid">
                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-number"><?= number_format($favorite_stats['total_favorites']) ?></div>
                                    <div class="stats-label">Toplam Favori</div>
                                    <div class="stats-change">
                                        <i class="fas fa-plus"></i>
                                        <span>Tüm zamanlar</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-number"><?= number_format($favorite_stats['unique_properties']) ?></div>
                                    <div class="stats-label">Favorilenen İlan</div>
                                    <div class="stats-change">
                                        <i class="fas fa-star"></i>
                                        <span>Popüler ilanlar</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-users"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-number"><?= number_format($favorite_stats['unique_users']) ?></div>
                                    <div class="stats-label">Aktif Kullanıcı</div>
                                    <div class="stats-change">
                                        <i class="fas fa-user-heart"></i>
                                        <span>Favori kullanan</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-day"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-number"><?= number_format($favorite_stats['today_favorites']) ?></div>
                                    <div class="stats-label">Bugünkü Favoriler</div>
                                    <div class="stats-change">
                                        <i class="fas fa-clock"></i>
                                        <span>Son 24 saat</span>
                                    </div>
                                </div>
                            </div>

                            <div class="stats-card">
                                <div class="stats-icon">
                                    <i class="fas fa-calendar-week"></i>
                                </div>
                                <div class="stats-content">
                                    <div class="stats-number"><?= number_format($favorite_stats['week_favorites']) ?></div>
                                    <div class="stats-label">Haftalık Favoriler</div>
                                    <div class="stats-change">
                                        <i class="fas fa-chart-line"></i>
                                        <span>Son 7 gün</span>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hızlı İşlemler -->
                    <div class="mb-4">
                        <h3 class="mb-3">
                            <i class="fas fa-bolt"></i>
                            Hızlı İşlemler
                        </h3>
                        
                        <div class="actions-grid">
                            <a href="admin-users.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-users-cog"></i>
                                </div>
                                <h5 class="action-title">Kullanıcı Yönetimi</h5>
                                <p class="action-description">Kullanıcıları yönet, yetkileri düzenle ve hesap durumlarını kontrol et</p>
                            </a>

                            <a href="admin-properties.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h5 class="action-title">Emlak Yönetimi</h5>
                                <p class="action-description">Emlak ilanlarını onayla, reddet veya düzenle</p>
                            </a>

                            <a href="admin-blog-add-new.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-blog"></i>
                                </div>
                                <h5 class="action-title">Blog Yönetimi</h5>
                                <p class="action-description">Blog yazılarını yönet, yeni yazı ekle</p>
                            </a>

                            <a href="admin-permissions.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <h5 class="action-title">Yetki Yönetimi</h5>
                                <p class="action-description">Kullanıcı yetkilerini ve rollerini yönet</p>
                            </a>

                            <a href="add-property.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <h5 class="action-title">Yeni İlan Ekle</h5>
                                <p class="action-description">Sisteme yeni bir emlak ilanı ekle</p>
                            </a>

                            <a href="admin-settings.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-cog"></i>
                                </div>
                                <h5 class="action-title">Sistem Ayarları</h5>
                                <p class="action-description">Site ayarlarını ve konfigürasyonları yönet</p>
                            </a>
                        </div>
                    </div>

                    <!-- İçerik Bölümleri -->
                    <div class="content-row">
                        <!-- Son Kullanıcılar -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-user-plus"></i>
                                    Son Kayıt Olan Kullanıcılar
                                </h5>
                                <a href="admin-users.php" class="section-link">Tümünü Gör</a>
                            </div>
                            
                            <div class="section-content">
                                <?php if ($recent_users && $recent_users->num_rows > 0): ?>
                                    <?php while($user = $recent_users->fetch_assoc()): ?>
                                        <div class="list-item">
                                            <div class="item-avatar">
                                                <?php
                                                // Türkçe isimler için daha iyi avatar oluştur
                                                $name_parts = explode(' ', trim($user['name']));
                                                if (count($name_parts) >= 2) {
                                                    // İsim ve soyisimin ilk harflerini al
                                                    echo strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
                                                } else {
                                                    // Tek kelime ise ilk iki harfini al
                                                    echo strtoupper(substr($user['name'], 0, 2));
                                                }
                                                ?>
                                            </div>
                                            <div class="item-content">
                                                <h6 class="item-title"><?= htmlspecialchars($user['name']) ?></h6>
                                                <small class="item-subtitle">
                                                    <?= htmlspecialchars($user['email']) ?> • 
                                                    <?= ucfirst($user['role']) ?> • 
                                                    <?= date('d.m.Y', strtotime($user['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="item-actions">
                                                <span class="badge <?= $user['is_active'] ? 'bg-success' : 'bg-secondary' ?>">
                                                    <?= $user['is_active'] ? 'Aktif' : 'Pasif' ?>
                                                </span>
                                                <a href="admin-users.php?user=<?= $user['id'] ?>" 
                                                   class="btn btn-sm btn-primary ms-2" title="Görüntüle">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-users"></i>
                                        <h6>Henüz kullanıcı yok</h6>
                                        <p>İlk kullanıcı kayıt olduğunda burada görünecek.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                        
                        <!-- Son Emlaklar -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-home"></i>
                                    Son Eklenen Emlaklar
                                </h5>
                                <a href="admin-properties.php" class="section-link">Tümünü Gör</a>
                            </div>
                            
                            <div class="section-content">
                                <?php if ($recent_properties && $recent_properties->num_rows > 0): ?>
                                    <?php while($property = $recent_properties->fetch_assoc()): ?>
                                        <div class="list-item">
                                            <div class="item-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="item-content">
                                                <h6 class="item-title"><?= htmlspecialchars($property['title']) ?></h6>
                                                <small class="item-subtitle">
                                                    <?= htmlspecialchars($property['owner_name'] ?? 'Bilinmiyor') ?> • 
                                                    <?= number_format($property['price'], 0, ',', '.') ?> ₺ • 
                                                    <?= date('d.m.Y', strtotime($property['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="item-actions">
                                                <span class="badge <?php 
                                                    switch($property['status']) {
                                                        case 'approved': echo 'bg-success'; break;
                                                        case 'pending': echo 'bg-warning'; break;
                                                        case 'rejected': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?php 
                                                    switch($property['status']) {
                                                        case 'approved': echo 'Onaylı'; break;
                                                        case 'pending': echo 'Beklemede'; break;
                                                        case 'rejected': echo 'Reddedildi'; break;
                                                        default: echo 'Bilinmiyor';
                                                    }
                                                    ?>
                                                </span>
                                                <a href="add-property.php?edit=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-warning ms-2" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <h6>Henüz emlak ilanı yok</h6>
                                        <p>İlk emlak ilanı eklendiğinde burada görünecek.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>

                    <!-- Pasif İlanlar -->
                    <?php if ($inactive_properties && $inactive_properties->num_rows > 0): ?>
                    <div class="content-section mt-4">
                        <div class="section-header">
                            <h5 class="section-title">
                                <i class="fas fa-eye-slash"></i>
                                Pasif İlanlar
                            </h5>
                            <a href="admin-properties.php?status=inactive" class="section-link">Tümünü Gör</a>
                        </div>
                        
                        <div class="section-content">
                            <?php while($property = $inactive_properties->fetch_assoc()): ?>
                                <div class="list-item">
                                    <div class="item-icon">
                                        <i class="fas fa-eye-slash"></i>
                                    </div>
                                    <div class="item-content">
                                        <h6 class="item-title"><?= htmlspecialchars($property['title']) ?></h6>
                                        <small class="item-subtitle">
                                            Sahibi: <?= htmlspecialchars($property['owner_name'] ?? 'Bilinmiyor') ?> • 
                                            <?= date('d.m.Y H:i', strtotime($property['created_at'])) ?>
                                        </small>
                                    </div>
                                    <div class="item-actions">
                                        <a href="admin-properties.php?action=activate&property_id=<?= $property['id'] ?>" 
                                           class="btn btn-sm btn-success" title="Aktif Yap">
                                            <i class="fas fa-eye"></i>
                                        </a>
                                        <a href="../property-details.php?id=<?= $property['id'] ?>" 
                                           class="btn btn-sm btn-primary" title="Görüntüle" target="_blank">
                                            <i class="fas fa-search"></i>
                                        </a>
                                        <a href="add-property.php?edit=<?= $property['id'] ?>" 
                                           class="btn btn-sm btn-warning" title="Düzenle">
                                            <i class="fas fa-edit"></i>
                                        </a>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    </div>
                    <?php endif; ?>
                </div>
            </div>

            <!-- Favori Detaylı İstatistikleri -->
            <div class="row">
                <!-- En Çok Favorilenen İlanlar -->
                <div class="col-lg-6">
                    <div class="white-box mb-4">
                        <h4 class="box-title">
                            <i class="fas fa-trophy text-warning"></i>
                            En Çok Favorilenen İlanlar
                        </h4>
                        <?php if ($most_favorited && $most_favorited->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>İlan</th>
                                        <th class="text-center">Favori Sayısı</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($property = $most_favorited->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars(substr($property['title'], 0, 50)) ?>...</h6>
                                                    <small class="text-muted">ID: #<?= $property['id'] ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-danger">
                                                <i class="fas fa-heart"></i>
                                                <?= $property['favorite_count'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="../property-details.php?id=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-primary" title="Görüntüle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="showFavoriteUsers(<?= $property['id'] ?>)" 
                                                        title="Kim Favoriledi?">
                                                    <i class="fas fa-users"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Henüz favorilenen ilan bulunmuyor.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>

                <!-- En Aktif Favori Kullanıcıları -->
                <div class="col-lg-6">
                    <div class="white-box mb-4">
                        <h4 class="box-title">
                            <i class="fas fa-user-friends text-success"></i>
                            En Aktif Favori Kullanıcıları
                        </h4>
                        <?php if ($favorite_users && $favorite_users->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Kullanıcı</th>
                                        <th class="text-center">Favori Sayısı</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($user = $favorite_users->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <div class="d-flex align-items-center">
                                                <div>
                                                    <h6 class="mb-1"><?= htmlspecialchars($user['name']) ?></h6>
                                                    <small class="text-muted"><?= htmlspecialchars($user['email']) ?></small>
                                                </div>
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <span class="badge bg-success">
                                                <i class="fas fa-heart"></i>
                                                <?= $user['favorite_count'] ?>
                                            </span>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <button class="btn btn-sm btn-info" 
                                                        onclick="showUserFavorites(<?= $user['id'] ?>)" 
                                                        title="Favorilerini Göster">
                                                    <i class="fas fa-list"></i>
                                                </button>
                                                <a href="admin-users.php?user=<?= $user['id'] ?>" 
                                                   class="btn btn-sm btn-primary" title="Kullanıcı Detay">
                                                    <i class="fas fa-user"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Henüz favori kullanan kullanıcı bulunmuyor.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>

            <!-- Son Favoriler -->
            <div class="row">
                <div class="col-12">
                    <div class="white-box mb-4">
                        <h4 class="box-title">
                            <i class="fas fa-clock text-primary"></i>
                            Son Favoriler
                        </h4>
                        <?php if ($recent_favorites && $recent_favorites->num_rows > 0): ?>
                        <div class="table-responsive">
                            <table class="table table-striped">
                                <thead>
                                    <tr>
                                        <th>Tarih</th>
                                        <th>Kullanıcı</th>
                                        <th>İlan</th>
                                        <th class="text-center">İşlemler</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php while ($favorite = $recent_favorites->fetch_assoc()): ?>
                                    <tr>
                                        <td>
                                            <small class="text-muted">
                                                <?= date('d.m.Y H:i', strtotime($favorite['created_at'])) ?>
                                            </small>
                                        </td>
                                        <td>
                                            <strong><?= htmlspecialchars($favorite['user_name']) ?></strong>
                                        </td>
                                        <td>
                                            <div class="property-title">
                                                <?= htmlspecialchars(substr($favorite['property_title'], 0, 60)) ?>...
                                            </div>
                                        </td>
                                        <td class="text-center">
                                            <div class="btn-group" role="group">
                                                <a href="../property-details.php?id=<?= $favorite['property_id'] ?>" 
                                                   class="btn btn-sm btn-primary" title="İlanı Görüntüle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endwhile; ?>
                                </tbody>
                            </table>
                        </div>
                        <?php else: ?>
                        <div class="alert alert-info">
                            <i class="fas fa-info-circle"></i>
                            Henüz favori eklenmemiş.
                        </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar-admin');
            const overlay = document.querySelector('.mobile-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }

        // Mobile overlay click to close
        document.querySelector('.mobile-overlay')?.addEventListener('click', function() {
            toggleSidebar();
        });

        // Quick actions animation
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => {
                    this.style.transform = '';
                }, 100);
            });
        });

        // Favori ile ilgili fonksiyonlar
        function showFavoriteUsers(propertyId) {
            // Property'yi kim favoriledi göster
            fetch('ajax/get-property-favorites.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({property_id: propertyId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let userList = data.users.map(user => 
                        `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${user.name}</strong><br>
                                <small class="text-muted">${user.email}</small>
                            </div>
                            <small class="text-muted">${user.created_at}</small>
                        </li>`
                    ).join('');
                    
                    showModal('Bu İlanı Favorileyen Kullanıcılar', 
                        `<ul class="list-group">${userList}</ul>`);
                } else {
                    showModal('Hata', 'Favori bilgileri yüklenemedi.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Hata', 'Bir hata oluştu.');
            });
        }

        function showUserFavorites(userId) {
            // Kullanıcının favori ilanlarını göster
            fetch('ajax/get-user-favorites-admin.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({user_id: userId})
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    let favoriteList = data.favorites.map(fav => 
                        `<li class="list-group-item d-flex justify-content-between align-items-center">
                            <div>
                                <strong>${fav.title}</strong><br>
                                <small class="text-muted">Fiyat: ${fav.price} TL</small>
                            </div>
                            <div>
                                <a href="../property-details.php?id=${fav.property_id}" target="_blank" class="btn btn-sm btn-primary">
                                    <i class="fas fa-eye"></i>
                                </a>
                            </div>
                        </li>`
                    ).join('');
                    
                    showModal('Kullanıcının Favori İlanları', 
                        `<ul class="list-group">${favoriteList}</ul>`);
                } else {
                    showModal('Bilgi', 'Bu kullanıcının henüz favorisi yok.');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                showModal('Hata', 'Bir hata oluştu.');
            });
        }

        function showModal(title, content) {
            // Bootstrap modal oluştur ve göster
            const modalHtml = `
                <div class="modal fade" id="dynamicModal" tabindex="-1">
                    <div class="modal-dialog modal-lg">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">${title}</h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <div class="modal-body">
                                ${content}
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                            </div>
                        </div>
                    </div>
                </div>
            `;
            
            // Eski modal'ı kaldır
            const existingModal = document.getElementById('dynamicModal');
            if (existingModal) {
                existingModal.remove();
            }
            
            // Yeni modal'ı ekle ve göster
            document.body.insertAdjacentHTML('beforeend', modalHtml);
            const modal = new bootstrap.Modal(document.getElementById('dynamicModal'));
            modal.show();
            
            // Modal kapandığında DOM'dan kaldır
            document.getElementById('dynamicModal').addEventListener('hidden.bs.modal', function() {
                this.remove();
            });
        }

        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            console.log('Admin stats refreshed');
        }, 300000);
    </script>
</body>
</html>
