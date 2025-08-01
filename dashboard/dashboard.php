<?php
session_start();

// 🎯 DASHBOARD REDIRECT TIMING - Start
$redirectStartTime = microtime(true);
$dashboardLogFile = __DIR__ . '/debug.log';
error_log("🚀 DASHBOARD.PHP: Başlatıldı - " . date('H:i:s'));
file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 🚀 DASHBOARD.PHP: Başlatıldı - " . date('H:i:s') . "\n", FILE_APPEND | LOCK_EX);

include __DIR__ . '/../db.php';

// Kullanıcı giriş yapmış mı kontrol et
if (!isset($_SESSION['user_id'])) {
    error_log("🚫 DASHBOARD.PHP: Kullanıcı giriş yapmamış, index.php'ye yönlendiriliyor");
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini veritabanından çek
$userQueryStart = microtime(true);
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();
$userQueryTime = round((microtime(true) - $userQueryStart) * 1000, 2);

if (!$user) {
    error_log("🚫 DASHBOARD.PHP: Kullanıcı bulunamadı, index.php'ye yönlendiriliyor");
    header("Location: ../index.php");
    exit;
}

error_log("👤 DASHBOARD.PHP: Kullanıcı sorgusu tamamlandı - {$userQueryTime}ms, Role: {$user['role']}");
file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 👤 DASHBOARD.PHP: Kullanıcı sorgusu tamamlandı - {$userQueryTime}ms, Role: {$user['role']}\n", FILE_APPEND | LOCK_EX);

// Admin kullanıcıları admin dashboard'a yönlendir
if ($user['role'] == 'admin') {
    $redirectTime = round((microtime(true) - $redirectStartTime) * 1000, 2);
    error_log("🔄 DASHBOARD.PHP: Admin dashboard'a yönlendiriliyor - {$redirectTime}ms");
    file_put_contents($dashboardLogFile, "[" . date('d-M-Y H:i:s T') . "] 🔄 DASHBOARD.PHP: Admin dashboard'a yönlendiriliyor - {$redirectTime}ms\n", FILE_APPEND | LOCK_EX);
    header("Location: dashboard-admin.php");
    exit;
}

// Normal kullanıcıları user dashboard'a yönlendir
if ($user['role'] == 'user') {
    header("Location: dashboard-user.php");
    exit;
}

// Sayfa başlığı
$page_title = "Dashboard";
$current_page = 'dashboard';

// Kullanıcının emlak istatistikleri
try {
    $user_stats_query = "SELECT 
        COUNT(*) as total_properties,
        SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as approved_count,
        SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as pending_count,
        SUM(CASE WHEN status = 'sold' THEN 1 ELSE 0 END) as rejected_count,
        SUM(CASE WHEN type = 'sale' THEN 1 ELSE 0 END) as sale_count,
        SUM(CASE WHEN type = 'rent' THEN 1 ELSE 0 END) as rent_count,
        AVG(price) as avg_price
        FROM properties 
        WHERE user_id = ?";

    $user_stats_stmt = $conn->prepare($user_stats_query);
    $user_stats_stmt->bind_param("i", $user_id);
    $user_stats_stmt->execute();
    $user_stats = $user_stats_stmt->get_result()->fetch_assoc();

    if (!$user_stats) {
        $user_stats = [
            'total_properties' => 0,
            'approved_count' => 0,
            'pending_count' => 0,
            'rejected_count' => 0,
            'sale_count' => 0,
            'rent_count' => 0,
            'avg_price' => 0
        ];
    }
} catch (Exception $e) {
    $user_stats = [
        'total_properties' => 0,
        'approved_count' => 0,
        'pending_count' => 0,
        'rejected_count' => 0,
        'sale_count' => 0,
        'rent_count' => 0,
        'avg_price' => 0
    ];
}

// Son aktiviteler
try {
    $recent_activities_query = "SELECT p.*, 'property' as activity_type 
                               FROM properties p 
                               WHERE p.user_id = ? 
                               ORDER BY p.created_at DESC 
                               LIMIT 5";
    $recent_activities_stmt = $conn->prepare($recent_activities_query);
    $recent_activities_stmt->bind_param("i", $user_id);
    $recent_activities_stmt->execute();
    $recent_activities = $recent_activities_stmt->get_result();
} catch (Exception $e) {
    $recent_activities = null;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <!-- Dashboard Common CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    <!-- Dashboard Z-Index Fix CSS -->
    <link rel="stylesheet" type="text/css" href="../css/dashboard-fix.css">
    <!-- Service Worker Engelleyici -->
    <script src="includes/service-worker-blocker.js"></script>
    
    <style>
        .welcome-banner {
            background: linear-gradient(135deg, var(--primary-color), #1a2e30);
            color: white;
            border-radius: var(--border-radius);
            padding: 40px;
            margin-bottom: 40px;
            position: relative;
            overflow: hidden;
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: bold;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
    </style>
</head>

<body class="user-dashboard">
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <!-- Dashboard Content -->
                <h2 class="main-title d-block d-lg-none"><?= $page_title ?></h2>

                <!-- Error/Success Messages -->
                <?php if (isset($_SESSION['error'])): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-triangle me-2"></i>
                        <?= htmlspecialchars($_SESSION['error']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['error']); ?>
                <?php endif; ?>

                <?php if (isset($_SESSION['success'])): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($_SESSION['success']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['success']); ?>
                <?php endif; ?>

                <!-- Debug Info (sadece test için) -->
                <?php if (isset($_SESSION['debug_add_property'])): ?>
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        <i class="fas fa-info-circle me-2"></i>
                        <strong>Debug Info:</strong><br>
                        <?= implode('<br>', $_SESSION['debug_add_property']) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                    <?php unset($_SESSION['debug_add_property']); ?>
                <?php endif; ?>

                <!-- Welcome Section -->
                <div class="welcome-banner">
                    <div class="welcome-content">
                        <h1 class="welcome-title">Hoş Geldiniz, <?= htmlspecialchars($user['name']) ?>!</h1>
                        <p class="welcome-subtitle">Dashboard'unuzda emlak ilanlarınızı yönetebilir ve istatistiklerinizi görebilirsiniz.</p>
                    </div>
                </div>

                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <!-- Total Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['total_properties'] ?? 0) ?></div>
                        <div class="stats-label">Toplam İlan</div>
                        <div class="stats-change positive">
                            <i class="fas fa-arrow-up"></i>
                            <span>Aktif</span>
                        </div>
                    </div>

                    <!-- Approved Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['approved_count'] ?? 0) ?></div>
                        <div class="stats-label">Onaylanmış</div>
                        <div class="stats-change positive">
                            <i class="fas fa-check"></i>
                            <span>Yayında</span>
                        </div>
                    </div>

                    <!-- Sale Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-money-bill-wave"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['sale_count'] ?? 0) ?></div>
                        <div class="stats-label">Satılık</div>
                        <div class="stats-change positive">
                            <i class="fas fa-chart-line"></i>
                            <span>İlan</span>
                        </div>
                    </div>

                    <!-- Rent Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-key"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['rent_count'] ?? 0) ?></div>
                        <div class="stats-label">Kiralık</div>
                        <div class="stats-change positive">
                            <i class="fas fa-home"></i>
                            <span>İlan</span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <?php if (($user['can_add_property'] ?? 0) || $user['role'] == 'admin'): ?>
                    <a href="add-property.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h5 class="action-title">Yeni İlan Ekle</h5>
                        <p class="action-description">Yeni bir emlak ilanı oluşturun ve yayınlayın</p>
                    </a>
                    <?php endif; ?>

                    <a href="my-properties.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h5 class="action-title">İlanlarım</h5>
                        <p class="action-description">Mevcut ilanlarınızı görüntüleyin ve düzenleyin</p>
                    </a>

                    <a href="favourites.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <h5 class="action-title">Favorilerim</h5>
                        <p class="action-description">Beğendiğiniz emlakları görüntüleyin</p>
                    </a>

                    <a href="message.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="action-title">Mesajlar</h5>
                        <p class="action-description">Gelen mesajları kontrol edin</p>
                    </a>
                </div>

                <!-- Main Content -->
                <div class="row">
                    <!-- Recent Activities -->
                    <div class="col-lg-8">
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-clock"></i>
                                    Son Aktiviteler
                                </h5>
                            </div>
                            <?php if ($recent_activities && $recent_activities->num_rows > 0): ?>
                                <?php while($activity = $recent_activities->fetch_assoc()): ?>
                                    <div class="recent-item">
                                        <div class="recent-avatar">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="recent-info">
                                            <h6><?= htmlspecialchars($activity['title'] ?? 'Başlık yok') ?></h6>
                                            <small>
                                                <?= ucfirst($activity['type'] ?? 'Bilinmiyor') ?> • 
                                                <?= number_format($activity['price'] ?? 0, 0, ',', '.') ?> ₺
                                            </small>
                                        </div>
                                        <div class="recent-meta">
                                            <span class="badge bg-success">
                                                <?= ucfirst($activity['status'] ?? 'Durum yok') ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= date('d.m.Y H:i', strtotime($activity['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-5">
                                    <i class="fas fa-home text-muted" style="font-size: 64px; opacity: 0.3;"></i>
                                    <h4 class="text-muted mt-3">Henüz ilanınız yok</h4>
                                    <p class="text-muted mb-4">İlk emlak ilanınızı ekleyerek başlayın</p>
                                    <?php if (($user['can_add_property'] ?? 0) || $user['role'] == 'admin'): ?>
                                    <a href="add-property.php" class="btn btn-primary">
                                        <i class="fas fa-plus"></i>
                                        İlk İlanımı Ekle
                                    </a>
                                    <?php endif; ?>
                                </div>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Quick Stats -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-chart-bar"></i>
                                    Hızlı İstatistikler
                                </h5>
                            </div>
                            <div class="row">
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-primary"><?= $user_stats['sale_count'] ?? 0 ?></h4>
                                        <small>Satılık İlan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="text-center p-3 bg-light rounded">
                                        <h4 class="text-info"><?= $user_stats['rent_count'] ?? 0 ?></h4>
                                        <small>Kiralık İlan</small>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Profile Completion -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-user-check"></i>
                                    Profil Durumu
                                </h5>
                            </div>
                            <?php
                            // Profil tamamlanma yüzdesi hesapla
                            $completion_fields = ['name', 'email', 'phone', 'address', 'about', 'website', 'position'];
                            $completed_fields = 0;
                            foreach ($completion_fields as $field) {
                                if (!empty($user[$field])) {
                                    $completed_fields++;
                                }
                            }
                            $completion_percentage = round(($completed_fields / count($completion_fields)) * 100);
                            ?>
                            <div class="progress mb-3" style="height: 10px;">
                                <div class="progress-bar bg-success" role="progressbar" style="width: <?= $completion_percentage ?>%"></div>
                            </div>
                            <div class="d-flex justify-content-between align-items-center">
                                <span>Profil Tamamlanma</span>
                                <strong><?= $completion_percentage ?>%</strong>
                            </div>
                            <a href="profile.php" class="btn btn-sm btn-outline-primary mt-2 w-100">
                                <i class="fas fa-edit"></i>
                                Profili Tamamla
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <button class="scroll-top">
            <i class="bi bi-arrow-up-short"></i>
        </button>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
    
    <script>
        // Sidebar toggle fonksiyonu
        function toggleSidebar() {
            const sidebar = document.querySelector('.dash-aside-navbar');
            const overlay = document.querySelector('.dashboard-mobile-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }
        
        // Mobile overlay click - sidebar'ı kapat
        document.addEventListener('DOMContentLoaded', function() {
            const overlay = document.createElement('div');
            overlay.className = 'dashboard-mobile-overlay';
            overlay.onclick = toggleSidebar;
            document.body.appendChild(overlay);
        });
        
        // Mobile nav toggle (eski kod - uyumluluk için)
        document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').classList.toggle('show');
        });
        
        // Responsive sidebar handling
        window.addEventListener('resize', function() {
            if (window.innerWidth > 1199) {
                document.querySelector('.dash-aside-navbar')?.classList.remove('show');
                document.querySelector('.dashboard-mobile-overlay')?.classList.remove('show');
            }
        });
    </script>
</body>
</html>
