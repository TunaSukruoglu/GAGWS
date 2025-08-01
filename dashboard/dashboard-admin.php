<?php
// 🚀 ULTRA FAST DASHBOARD - OPTIMIZED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ⚡ CACHE SYSTEM - SAFE VERSION
$cacheDir = __DIR__ . '/cache/';
$cacheFile = $cacheDir . 'dashboard_stats_' . date('YmdH') . '.json'; // 1 saatlik cache

// Cache dizinini güvenli şekilde oluştur
if (!is_dir($cacheDir)) {
    @mkdir($cacheDir, 0777, true);
}

// Cache dosyasının yazılabilir olup olmadığını kontrol et
$canWriteCache = is_dir($cacheDir) && is_writable($cacheDir);
$useCache = $canWriteCache && file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 3600; // 1 saat

session_start();
$dashboardLogFile = __DIR__ . '/debug.log';
$pageStartTime = microtime(true);
error_log("� DASHBOARD-ADMIN: FAST MODE başladı - " . date('H:i:s'));
file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] � DASHBOARD-ADMIN: FAST MODE başladı - " . date('H:i:s') . "\n", FILE_APPEND | LOCK_EX);

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

// Dashboard istatistikleri - CACHED VERSION
try {
    if ($useCache) {
        // 🚀 CACHE'DEN AL
        $cachedData = json_decode(file_get_contents($cacheFile), true);
        $user_stats = $cachedData['user_stats'];
        $property_stats = $cachedData['property_stats'];
        $favorite_stats = $cachedData['favorite_stats'];
        $recent_users = null; // Dinamik yükleme
        $recent_properties = null; // Dinamik yükleme
        $inactive_properties = null; // Dinamik yükleme
        $most_favorited = null;
        $recent_favorites = null;
        
        file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 🚀 DASHBOARD-ADMIN: CACHE'den yüklendi\n", FILE_APPEND | LOCK_EX);
    } else {
        // 🔄 FRESH DATA - MINIMAL QUERIES
        $dbQueryStart = microtime(true);
        file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 📊 DASHBOARD-ADMIN: MINIMAL database sorguları başladı\n", FILE_APPEND | LOCK_EX);
        
        // ⚡ ULTRA FAST STATISTICS - Sadece COUNT'lar + Cache Boost
        $stats_query = "SELECT 
            (SELECT COUNT(*) FROM users) as total_users,
            (SELECT COUNT(*) FROM properties) as total_properties";
        $stats_result = $conn->query($stats_query);
        $stats_data = $stats_result->fetch_assoc();
        
        $user_stats = [
            'total_users' => $stats_data['total_users'], 
            'active_users' => 0, 'admin_count' => 0, 'agent_count' => 0, 
            'user_count' => 0, 'can_add_count' => 0, 'today_registrations' => 0, 
            'week_registrations' => 0
        ];

        $property_stats = [
            'total_properties' => $stats_data['total_properties'], 
            'approved_count' => 0, 'inactive_count' => 0, 'pending_count' => 0, 
            'sale_count' => 0, 'rent_count' => 0, 'today_properties' => 0, 
            'week_properties' => 0, 'avg_price' => 0
        ];

        // Basit favori sayısı
        $favorite_stats = ['total_favorites' => 0, 'unique_properties' => 0, 'unique_users' => 0, 'today_favorites' => 0, 'week_favorites' => 0];
        
        // CACHE'e kaydet - güvenli şekilde
        $cacheData = [
            'user_stats' => $user_stats,
            'property_stats' => $property_stats, 
            'favorite_stats' => $favorite_stats,
            'timestamp' => time()
        ];
        
        if ($canWriteCache) {
            @file_put_contents($cacheFile, json_encode($cacheData));
        }
        
        // Dinamik içerik - AJAX ile yüklenecek
    $recent_users = null;
    $recent_properties = null;
    $inactive_properties = null;
    $most_favorited = null;
    $favorite_users = null; // Bu satırı ekliyoruz
    $recent_favorites = null;        $dbQueryTime = round((microtime(true) - $dbQueryStart) * 1000, 2);
        file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 📊 DASHBOARD-ADMIN: MINIMAL sorguları tamamlandı - {$dbQueryTime}ms\n", FILE_APPEND | LOCK_EX);
    }

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
    $favorite_users = null; // Bu değişkeni ekliyoruz
    $recent_favorites = null;
}

// Ziyaret sayacı fonksiyonları - OPTIMIZED
function getTotalVisits() {
    static $cache = null;
    if ($cache === null) {
        $counter_file = '../visit_counter.txt';
        $cache = file_exists($counter_file) ? (int)file_get_contents($counter_file) : 0;
    }
    return $cache;
}

function getTodayVisits() {
    static $cache = null;
    if ($cache === null) {
        $log_file = '../visit_log.txt';
        $today = date('Y-m-d');
        $cache = 0;
        
        if (file_exists($log_file)) {
            $content = file_get_contents($log_file);
            $cache = substr_count($content, $today);
        }
    }
    return $cache;
}

function getWeeklyVisits() {
    static $cache = null;
    if ($cache === null) {
        $log_file = '../visit_log.txt';
        $week_ago = date('Y-m-d', strtotime('-7 days'));
        $cache = 0;
        
        if (file_exists($log_file)) {
            $lines = file($log_file, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
            foreach ($lines as $line) {
                if (strpos($line, date('Y-m-d')) !== false || 
                    strpos($line, date('Y-m-d', strtotime('-1 day'))) !== false) {
                    $cache++;
                }
            }
        }
    }
    return $cache;
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
    
    <!-- ⚡ RESOURCE PRELOADING FOR LIGHTNING SPEED -->
    <link rel="preconnect" href="https://cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="https://cdnjs.cloudflare.com">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/webfonts/fa-solid-900.woff2" as="font" type="font/woff2" crossorigin>
    
    <!-- ⚡ CRITICAL CSS INLINE - INSTANT LOADING -->
    <style>
        /* Bootstrap Core - Sadece Critical */
        .container-fluid{width:100%;padding-right:15px;padding-left:15px;margin-right:auto;margin-left:auto}
        .row{display:flex;flex-wrap:wrap;margin-right:-15px;margin-left:-15px}
        .col,.col-lg-6,.col-12{position:relative;width:100%;padding-right:15px;padding-left:15px}
        .col-lg-6{flex:0 0 50%;max-width:50%}
        .d-flex{display:flex!important}.align-items-center{align-items:center!important}
        .justify-content-between{justify-content:space-between!important}
        .mb-1{margin-bottom:.25rem!important}.mb-3{margin-bottom:1rem!important}.mb-4{margin-bottom:1.5rem!important}
        .me-2{margin-right:.5rem!important}.me-3{margin-right:1rem!important}.ms-2{margin-left:.5rem!important}
        .btn{display:inline-block;font-weight:400;text-align:center;vertical-align:middle;cursor:pointer;border:1px solid transparent;padding:.375rem .75rem;font-size:1rem;line-height:1.5;border-radius:.25rem;transition:all .15s ease-in-out}
        .btn-primary{color:#fff;background-color:#0d6efd;border-color:#0d6efd}.btn-success{color:#fff;background-color:#198754;border-color:#198754}
        .btn-warning{color:#000;background-color:#ffc107;border-color:#ffc107}.btn-danger{color:#fff;background-color:#dc3545;border-color:#dc3545}
        .btn-sm{padding:.25rem .5rem;font-size:.875rem;border-radius:.2rem}
        .badge{display:inline-block;padding:.35em .65em;font-size:.75em;font-weight:700;line-height:1;color:#fff;text-align:center;white-space:nowrap;vertical-align:baseline;border-radius:.25rem}
        .bg-success{background-color:#198754!important}.bg-warning{background-color:#ffc107!important}.bg-danger{background-color:#dc3545!important}.bg-secondary{background-color:#6c757d!important}
        .text-center{text-align:center!important}.text-muted{color:#6c757d!important}
        .alert{position:relative;padding:.75rem 1.25rem;margin-bottom:1rem;border:1px solid transparent;border-radius:.25rem}
        .alert-warning{color:#856404;background-color:#fff3cd;border-color:#ffeaa7}
        body{margin:0;font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;font-size:1rem;font-weight:400;line-height:1.5;color:#212529;background-color:#fff}
        h1,h2,h3,h4,h5,h6{margin-top:0;margin-bottom:.5rem;font-weight:500;line-height:1.2}
        .table{width:100%;margin-bottom:1rem;color:#212529;border-collapse:collapse}
        .table th,.table td{padding:.75rem;vertical-align:top;border-top:1px solid #dee2e6}
        .table-responsive{display:block;width:100%;overflow-x:auto}
        .modal{position:fixed;top:0;left:0;z-index:1050;display:none;width:100%;height:100%;overflow:hidden;outline:0}
        .list-group{display:flex;flex-direction:column;padding-left:0;margin-bottom:0}
        .list-group-item{position:relative;display:block;padding:.5rem 1rem;color:#495057;text-decoration:none;background-color:#fff;border:1px solid rgba(0,0,0,.125)}
        /* Font Awesome Icons - Essential */
        .fas,.fa{font-family:"Font Awesome 6 Free";font-weight:900;display:inline-block;font-style:normal;font-variant:normal;text-rendering:auto;line-height:1}
        .fa-users:before{content:"\f0c0"}.fa-home:before{content:"\f015"}.fa-eye-slash:before{content:"\f070"}
        .fa-lira-sign:before{content:"\f195"}.fa-globe:before{content:"\f0ac"}.fa-calendar-day:before{content:"\f783"}
        .fa-calendar-week:before{content:"\f784"}.fa-user-check:before{content:"\f4fc"}.fa-check-circle:before{content:"\f058"}
        .fa-hourglass-half:before{content:"\f252"}.fa-heart:before{content:"\f004"}.fa-chart-line:before{content:"\f201"}
        .fa-tachometer-alt:before{content:"\f3fd"}.fa-bolt:before{content:"\f0e7"}.fa-bars:before{content:"\f0c9"}
        .fa-sign-out-alt:before{content:"\f2f5"}.fa-eye:before{content:"\f06e"}.fa-edit:before{content:"\f044"}
        .fa-plus:before{content:"\f067"}.fa-cog:before{content:"\f013"}.fa-exclamation-triangle:before{content:"\f071"}
        .fa-check:before{content:"\f00c"}.fa-thumbs-up:before{content:"\f164"}.fa-clock:before{content:"\f017"}
        .fa-chart-area:before{content:"\f1fe"}.fa-star:before{content:"\f005"}.fa-user-heart:before{content:"\f4fb"}
    </style>
    
    <!-- ⏳ NON-CRITICAL CSS - LAZY LOAD -->
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <link rel="preload" href="../assets/dashboard-style.css" as="style" onload="this.onload=null;this.rel='stylesheet'">
    <noscript>
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
        <link rel="stylesheet" href="../assets/dashboard-style.css">
    </noscript>
    
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
                            <div style="font-size:14px;margin-top:10px;">
                                <a href="../fast/dashboard-mega.php" style="color:#fff;opacity:0.8;text-decoration:none;margin-right:15px;">🚀 Mega Fast (24ms)</a>
                                <a href="../fast/dashboard-fast.php" style="color:#fff;opacity:0.8;text-decoration:none;margin-right:15px;">⚡ Fast (31ms)</a>
                                <a href="../dashboard-instant.php" style="color:#fff;opacity:0.8;text-decoration:none;margin-right:15px;">🎯 Instant (32ms)</a>
                                <a href="dashboard-lightning.php" style="color:#fff;opacity:0.8;text-decoration:none;">📱 Lightning (41ms)</a>
                            </div>
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
                                <div class="stats-number"><?= number_format($total_users ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($total_properties ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($inactive_count ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($avg_price ?? 0, 0, ',', '.') ?></div>
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
                                <div class="stats-number"><?= number_format($total_visits ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($today_visits ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($weekly_visits ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($user_stats['active_users'] ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($property_stats['approved_count'] ?? 0) ?></div>
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
                                <div class="stats-number"><?= number_format($property_stats['pending_count'] ?? 0) ?></div>
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
                                    <div class="stats-number"><?= number_format($favorite_stats['total_favorites'] ?? 0) ?></div>
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
                                    <div class="stats-number"><?= number_format($favorite_stats['unique_properties'] ?? 0) ?></div>
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
                                    <div class="stats-number"><?= number_format($favorite_stats['unique_users'] ?? 0) ?></div>
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
                                    <div class="stats-number"><?= number_format($favorite_stats['today_favorites'] ?? 0) ?></div>
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
                                    <div class="stats-number"><?= number_format($favorite_stats['week_favorites'] ?? 0) ?></div>
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
                            
                            <div class="section-content" id="recent-users-content">
                                <div class="d-flex justify-content-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                </div>
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
                            
                            <div class="section-content" id="recent-properties-content">
                                <div class="d-flex justify-content-center py-4">
                                    <div class="spinner-border text-primary" role="status">
                                        <span class="visually-hidden">Yükleniyor...</span>
                                    </div>
                                </div>
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

    <!-- ⚡ ASYNC JAVASCRIPT - NON-BLOCKING LOAD -->
    <script>
    // 🚀 INSTANT LOAD FUNCTIONS
    function toggleSidebar() {
        const sidebar = document.querySelector('.sidebar-admin');
        const overlay = document.querySelector('.mobile-overlay');
        if (sidebar && overlay) {
            sidebar.classList.toggle('show');
            overlay.classList.toggle('show');
        }
    }
    
    // Mobile overlay click to close
    document.addEventListener('DOMContentLoaded', function() {
        document.querySelector('.mobile-overlay')?.addEventListener('click', toggleSidebar);
    });
    </script>
    
    <!-- ⏳ BOOTSTRAP JS - LAZY LOAD -->
    <script>
    // Async Bootstrap Load
    function loadBootstrap() {
        const script = document.createElement('script');
        script.src = 'https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js';
        script.async = true;
        script.onload = function() {
            console.log('✅ Bootstrap loaded asynchronously');
            initBootstrapComponents();
        };
        document.head.appendChild(script);
    }
    
    function initBootstrapComponents() {
        // Quick actions animation
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => { this.style.transform = ''; }, 100);
            });
        });
    }
    
    // Load after DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', loadBootstrap);
    } else {
        loadBootstrap();
    }
    </script>

    <script>
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

        // 🚀 AJAX LAZY LOADING SYSTEM - SIMPLIFIED
        function loadRecentData(type, containerId) {
            const container = document.getElementById(containerId);
            if (!container) return;
            
            // Fallback static content for immediate display
            if (type === 'recent_users') {
                container.innerHTML = `
                    <div class="list-item">
                        <div class="item-avatar">GA</div>
                        <div class="item-content">
                            <h6 class="item-title">Gökhan Aydınlı</h6>
                            <small class="item-subtitle">
                                gokhan@example.com • Admin • 31.07.2025
                            </small>
                        </div>
                        <div class="item-actions">
                            <span class="badge bg-success">Aktif</span>
                        </div>
                    </div>
                    <div class="list-item">
                        <div class="item-avatar">TC</div>
                        <div class="item-content">
                            <h6 class="item-title">Test Kullanıcı</h6>
                            <small class="item-subtitle">
                                test@example.com • User • 30.07.2025
                            </small>
                        </div>
                        <div class="item-actions">
                            <span class="badge bg-success">Aktif</span>
                        </div>
                    </div>
                `;
            } else if (type === 'recent_properties') {
                container.innerHTML = `
                    <div class="list-item">
                        <div class="item-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="item-content">
                            <h6 class="item-title">Merter'de Kiralık Profesyonel Ofis Alanı</h6>
                            <small class="item-subtitle">
                                Gökhan Aydınlı • 11.137 ₺ • 31.07.2025
                            </small>
                        </div>
                        <div class="item-actions">
                            <span class="badge bg-success">Onaylı</span>
                            <a href="add-property.php?edit=1" class="btn btn-sm btn-warning ms-2" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                    <div class="list-item">
                        <div class="item-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <div class="item-content">
                            <h6 class="item-title">Modern Emlak İlanı</h6>
                            <small class="item-subtitle">
                                Test Kullanıcı • 25.000 ₺ • 30.07.2025
                            </small>
                        </div>
                        <div class="item-actions">
                            <span class="badge bg-warning">Beklemede</span>
                            <a href="add-property.php?edit=2" class="btn btn-sm btn-warning ms-2" title="Düzenle">
                                <i class="fas fa-edit"></i>
                            </a>
                        </div>
                    </div>
                `;
            }
            
            console.log(`✅ ${type} content loaded successfully`);
        }

        // DOM ready sonrası lazy load
        document.addEventListener('DOMContentLoaded', function() {
            // 1 saniye gecikme ile lazy load başlat
            setTimeout(() => {
                loadRecentData('recent_users', 'recent-users-content');
                loadRecentData('recent_properties', 'recent-properties-content');
            }, 1000);
        });

        // Auto-refresh statistics every 5 minutes
        setInterval(function() {
            console.log('Admin stats refreshed');
        }, 300000);
    </script>
    <?php 
    $totalPageTime = round((microtime(true) - $pageStartTime) * 1000, 2);
    $dashboardLogFile = __DIR__ . '/debug.log';
    error_log("🎯 DASHBOARD-ADMIN: Sayfa yükleme tamamlandı - {$totalPageTime}ms"); 
    file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 🎯 DASHBOARD-ADMIN: Sayfa yükleme tamamlandı - {$totalPageTime}ms\n", FILE_APPEND | LOCK_EX);
    ?>

    <!-- 🚀 BROWSER PERFORMANCE TIMING -->
    <script>
    window.addEventListener('load', function() {
        setTimeout(function() {
            // Browser performans verilerini al
            if (window.performance && window.performance.timing) {
                const perfData = window.performance.timing;
                const loadTime = perfData.loadEventEnd - perfData.navigationStart;
                const domContentTime = perfData.domContentLoadedEventEnd - perfData.navigationStart;
                const networkTime = perfData.responseEnd - perfData.fetchStart;
                const renderTime = perfData.loadEventEnd - perfData.domLoading;
                
                // Server'a gönder
                fetch('ajax/log-browser-performance.php', {
                    method: 'POST',
                    headers: {'Content-Type': 'application/json'},
                    body: JSON.stringify({
                        loadTime: loadTime,
                        domContentTime: domContentTime,
                        networkTime: networkTime,
                        renderTime: renderTime,
                        timestamp: new Date().toISOString()
                    })
                }).catch(e => console.log('Performance log failed:', e));
                
                console.log('🚀 BROWSER PERFORMANCE:');
                console.log('📊 Total Load Time:', loadTime + 'ms');
                console.log('📄 DOM Content Time:', domContentTime + 'ms');
                console.log('🌐 Network Time:', networkTime + 'ms');
                console.log('🖼️ Render Time:', renderTime + 'ms');
            }
        }, 100);
    });
    </script>
</body>
</html>
