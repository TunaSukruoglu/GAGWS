<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User bilgilerini al
$user_query = $conn->prepare("SELECT name, email, role, can_add_property, created_at FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Admin kullanıcıları admin dashboard'a yönlendir
if ($user_data['role'] == 'admin') {
    header("Location: dashboard-admin.php");
    exit;
}

// Sayfa ayarları
$current_page = 'dashboard-user';
$page_title = $user_data['name'] . ' - Dashboard';
$user_name = $user_data['name']; // Sidebar için

// Kullanıcı istatistikleri - güvenli sorgular
try {
    // Favoriler sayısı
    $fav_count_query = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
    $fav_count_query->bind_param("i", $user_id);
    $fav_count_query->execute();
    $fav_count = $fav_count_query->get_result()->fetch_assoc()['count'];

    // Kayıtlı aramalar sayısı
    $saved_searches_count = 0;
    try {
        $search_count_query = $conn->prepare("SELECT COUNT(*) as count FROM saved_searches WHERE user_id = ?");
        $search_count_query->bind_param("i", $user_id);
        $search_count_query->execute();
        $saved_searches_count = $search_count_query->get_result()->fetch_assoc()['count'];
    } catch (Exception $e) {
        // Tablo yoksa 0 olarak kalır
    }

    // Değerlendirmeler sayısı
    $reviews_count = 0;
    try {
        $reviews_count_query = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
        $reviews_count_query->bind_param("i", $user_id);
        $reviews_count_query->execute();
        $reviews_count = $reviews_count_query->get_result()->fetch_assoc()['count'];
    } catch (Exception $e) {
        // Tablo yoksa 0 olarak kalır
    }

    // Kullanıcının ilanları
    $user_properties_query = $conn->prepare("
        SELECT COUNT(*) as total_properties,
        SUM(CASE WHEN status = 'active' OR status = 'approved' THEN 1 ELSE 0 END) as active_properties,
        SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_properties,
        SUM(CASE WHEN status = 'inactive' OR status = 'rejected' THEN 1 ELSE 0 END) as inactive_properties
        FROM properties WHERE user_id = ?
    ");
    $user_properties_query->bind_param("i", $user_id);
    $user_properties_query->execute();
    $property_stats = $user_properties_query->get_result()->fetch_assoc();

    // Son favorilere eklenen ilanlar
    $recent_favorites_query = $conn->prepare("
        SELECT f.created_at, p.title, p.price, p.address, p.id as property_id
        FROM favorites f 
        LEFT JOIN properties p ON f.property_id = p.id 
        WHERE f.user_id = ? 
        ORDER BY f.created_at DESC 
        LIMIT 5
    ");
    $recent_favorites_query->bind_param("i", $user_id);
    $recent_favorites_query->execute();
    $recent_favorites = $recent_favorites_query->get_result();

    // Son eklenen ilanlar (kullanıcının)
    $user_recent_properties_query = $conn->prepare("
        SELECT id, title, price, type, status, created_at
        FROM properties 
        WHERE user_id = ?
        ORDER BY created_at DESC 
        LIMIT 5
    ");
    $user_recent_properties_query->bind_param("i", $user_id);
    $user_recent_properties_query->execute();
    $user_recent_properties = $user_recent_properties_query->get_result();

} catch (Exception $e) {
    error_log("Dashboard User Stats Error: " . $e->getMessage());
    // Varsayılan değerler
    $fav_count = 0;
    $saved_searches_count = 0;
    $reviews_count = 0;
    $property_stats = ['total_properties' => 0, 'active_properties' => 0, 'pending_properties' => 0, 'inactive_properties' => 0];
    $recent_favorites = null;
    $user_recent_properties = null;
}

// Ziyaret sayacı fonksiyonları (Genel bilgi için)
function getTotalVisits() {
    $counter_file = '../visit_counter.txt';
    if (file_exists($counter_file)) {
        return (int)file_get_contents($counter_file);
    }
    return 0;
}

// İstatistik sayıları
$total_properties = $property_stats['total_properties'] ?? 0;
$active_properties = $property_stats['active_properties'] ?? 0;
$pending_properties = $property_stats['pending_properties'] ?? 0;
$inactive_properties = $property_stats['inactive_properties'] ?? 0;
$total_visits = getTotalVisits();
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
        /* Dashboard User Specific Styles - Admin dashboard ile aynı */
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
            background: linear-gradient(45deg, rgba(255,255,255,0.1) 0%, transparent 50%);
            pointer-events: none;
        }
        
        .welcome-content {
            position: relative;
            z-index: 2;
        }
        
        .welcome-title {
            font-size: 32px;
            font-weight: 700;
            margin-bottom: 15px;
            text-shadow: 0 2px 10px rgba(0,0,0,0.3);
            position: relative;
            z-index: 2;
        }
        
        .welcome-subtitle {
            font-size: 18px;
            opacity: 0.95;
            margin-bottom: 25px;
            position: relative;
            z-index: 2;
        }
        
        .btn-secondary-custom {
            background: rgba(255, 255, 255, 0.15);
            color: white;
            padding: 12px 25px;
            border-radius: 12px;
            text-decoration: none;
            font-weight: 600;
            transition: all 0.3s ease;
            border: 2px solid rgba(255, 255, 255, 0.2);
            display: inline-flex;
            align-items: center;
            gap: 10px;
            position: relative;
            z-index: 2;
            backdrop-filter: blur(10px);
        }
        
        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
            border-color: rgba(255, 255, 255, 0.4);
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
            color: #0a58ca;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(5, 1fr);
            gap: 15px;
            margin-bottom: 30px;
        }
        
        /* Responsive için smaller screens */
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
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
        }
        
        @media (max-width: 480px) {
            .stats-grid {
                grid-template-columns: 1fr;
            }
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
        
        .quick-actions {
            margin-bottom: 30px;
        }
        
        .actions-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 20px;
        }
        
        .action-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            position: relative;
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
        
        .external-indicator {
            position: absolute;
            top: 20px;
            right: 20px;
            font-size: 14px;
            color: #0d6efd;
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
            border-left: 4px solid #0d6efd;
        }
        
        .alert-info {
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.05));
            color: #0d1a1c;
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
                padding-top: 10px;
            }
            
            .welcome-banner {
                padding: 30px 20px;
                margin: 15px;
                border-radius: 15px;
            }
            
            .welcome-title {
                font-size: 22px;
                margin-bottom: 8px;
            }
            
            .welcome-subtitle {
                font-size: 14px;
            }
            
            .btn-secondary-custom {
                padding: 8px 16px;
                font-size: 14px;
                margin-top: 10px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 15px;
            }
            
            .actions-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .content-row {
                grid-template-columns: 1fr;
                gap: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
                padding-top: 10px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
                gap: 15px;
            }
            
            .stats-card {
                padding: 20px;
            }
            
            .welcome-banner {
                padding: 20px 15px;
            }
            
            .welcome-title {
                font-size: 20px;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar-user.php'; ?>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay"></div>

        <!-- Ana İçerik -->
        <div class="dashboard-body">
            <!-- Mobil Header -->
            <div class="mobile-header d-block d-md-none">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mobile-title">Kullanıcı Dashboard</h5>
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
                                <i class="fas fa-user me-2"></i>
                                Hoş Geldiniz, <?= htmlspecialchars($user_data['name']) ?>!
                            </h2>
                            <p class="welcome-subtitle">
                                Gayrimenkul yolculuğunuzda size yardımcı olmak için buradayız. 
                                Favorilerinizi keşfedin, aramanızı kaydedin ve hayalinizdeki mülkü bulun.
                            </p>
                            <?php if ($user_data['can_add_property']): ?>
                            <a href="add-property.php" class="btn-secondary-custom">
                                <i class="fas fa-plus"></i>
                                Yeni İlan Ekle
                            </a>
                            <?php endif; ?>
                        </div>
                    </div>

                    <!-- İstatistik Kartları -->
                    <div class="stats-grid">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($fav_count) ?></div>
                                <div class="stats-label">Favori İlanlarım</div>
                                <div class="stats-change">
                                    <i class="fas fa-heart"></i>
                                    <span>Beğendiğiniz ilanlar</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($total_properties) ?></div>
                                <div class="stats-label">İlanlarım</div>
                                <div class="stats-change">
                                    <i class="fas fa-chart-line"></i>
                                    <span>Eklediğiniz ilanlar</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($active_properties) ?></div>
                                <div class="stats-label">Aktif İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-eye"></i>
                                    <span>Yayında olan</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($pending_properties) ?></div>
                                <div class="stats-label">Bekleyen İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-hourglass-half"></i>
                                    <span>Onay bekliyor</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($saved_searches_count) ?></div>
                                <div class="stats-label">Kayıtlı Aramalar</div>
                                <div class="stats-change">
                                    <i class="fas fa-bookmark"></i>
                                    <span>Saklanan aramalar</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($reviews_count) ?></div>
                                <div class="stats-label">Değerlendirmelerim</div>
                                <div class="stats-change">
                                    <i class="fas fa-comment"></i>
                                    <span>Yaptığınız yorumlar</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-eye-slash"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($inactive_properties) ?></div>
                                <div class="stats-label">Pasif İlanlar</div>
                                <div class="stats-change">
                                    <i class="fas fa-ban"></i>
                                    <span>Yayında olmayan</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-globe"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= number_format($total_visits) ?></div>
                                <div class="stats-label">Site Ziyaretçisi</div>
                                <div class="stats-change">
                                    <i class="fas fa-users"></i>
                                    <span>Toplam ziyaret</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= date('d.m.Y', strtotime($user_data['created_at'])) ?></div>
                                <div class="stats-label">Üyelik Tarihi</div>
                                <div class="stats-change">
                                    <i class="fas fa-user-plus"></i>
                                    <span>Kayıt olduğunuz tarih</span>
                                </div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-shield-alt"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?= ucfirst($user_data['role']) ?></div>
                                <div class="stats-label">Hesap Türü</div>
                                <div class="stats-change">
                                    <i class="fas fa-user-tag"></i>
                                    <span>Kullanıcı rolü</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Hızlı İşlemler -->
                    <div class="quick-actions">
                        <h3 class="section-title">
                            <i class="fas fa-bolt"></i>
                            Hızlı İşlemler
                        </h3>
                        
                        <div class="actions-grid">
                            <a href="user-favorites.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-heart"></i>
                                </div>
                                <h5 class="action-title">Favorilerim</h5>
                                <p class="action-description">Beğendiğiniz emlak ilanlarını görüntüleyin ve yönetin</p>
                            </a>

                            <?php if ($user_data['can_add_property']): ?>
                            <a href="add-property.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-plus"></i>
                                </div>
                                <h5 class="action-title">Yeni İlan Ekle</h5>
                                <p class="action-description">Yeni bir emlak ilanı ekleyin ve yayınlayın</p>
                            </a>

                            <a href="user-properties.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-home"></i>
                                </div>
                                <h5 class="action-title">İlanlarım</h5>
                                <p class="action-description">Eklediğiniz emlak ilanlarını görüntüleyin ve düzenleyin</p>
                            </a>
                            <?php endif; ?>

                            <a href="user-profile.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-user-edit"></i>
                                </div>
                                <h5 class="action-title">Profil Ayarları</h5>
                                <p class="action-description">Hesap bilgilerinizi ve ayarlarınızı güncelleyin</p>
                            </a>

                            <a href="../" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-search"></i>
                                </div>
                                <h5 class="action-title">İlan Ara</h5>
                                <p class="action-description">Emlak ilanları arasında arama yapın</p>
                                <i class="fas fa-external-link-alt external-indicator"></i>
                            </a>

                            <a href="user-messages.php" class="action-card">
                                <div class="action-icon">
                                    <i class="fas fa-envelope"></i>
                                </div>
                                <h5 class="action-title">Mesajlarım</h5>
                                <p class="action-description">Gelen mesajları ve konuşmaları görüntüleyin</p>
                            </a>
                        </div>
                    </div>

                    <!-- İçerik Bölümleri -->
                    <div class="content-row">
                        <!-- Son Favoriler -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-heart"></i>
                                    Son Favorilerim
                                </h5>
                                <a href="user-favorites.php" class="section-link">Tümünü Gör</a>
                            </div>
                            
                            <div class="section-content">
                                <?php if ($recent_favorites && $recent_favorites->num_rows > 0): ?>
                                    <?php while($favorite = $recent_favorites->fetch_assoc()): ?>
                                        <div class="list-item">
                                            <div class="item-icon">
                                                <i class="fas fa-heart"></i>
                                            </div>
                                            <div class="item-content">
                                                <h6 class="item-title"><?= htmlspecialchars($favorite['title'] ?? 'İlan bulunamadı') ?></h6>
                                                <small class="item-subtitle">
                                                    <?= htmlspecialchars($favorite['address'] ?? '') ?> • 
                                                    <?= number_format($favorite['price'] ?? 0, 0, ',', '.') ?> ₺ • 
                                                    <?= date('d.m.Y', strtotime($favorite['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="item-actions">
                                                <?php if ($favorite['property_id']): ?>
                                                <a href="../property-details.php?id=<?= $favorite['property_id'] ?>" 
                                                   class="btn btn-sm btn-primary" title="Görüntüle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="user-favorites.php?remove=<?= $favorite['property_id'] ?>" 
                                                   class="btn btn-sm btn-danger" title="Favorilerden Çıkar">
                                                    <i class="fas fa-heart-broken"></i>
                                                </a>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-heart"></i>
                                        <h6>Henüz favori ilan yok</h6>
                                        <p>Beğendiğiniz ilanları favorilere ekleyin.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>

                        <!-- Son İlanlarım -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-home"></i>
                                    Son İlanlarım
                                </h5>
                                <a href="user-properties.php" class="section-link">Tümünü Gör</a>
                            </div>
                            
                            <div class="section-content">
                                <?php if ($user_recent_properties && $user_recent_properties->num_rows > 0): ?>
                                    <?php while($property = $user_recent_properties->fetch_assoc()): ?>
                                        <div class="list-item">
                                            <div class="item-icon">
                                                <i class="fas fa-home"></i>
                                            </div>
                                            <div class="item-content">
                                                <h6 class="item-title"><?= htmlspecialchars($property['title']) ?></h6>
                                                <small class="item-subtitle">
                                                    <?= number_format($property['price'], 0, ',', '.') ?> ₺ • 
                                                    <?= ucfirst($property['type']) ?> • 
                                                    <?= date('d.m.Y', strtotime($property['created_at'])) ?>
                                                </small>
                                            </div>
                                            <div class="item-actions">
                                                <span class="badge <?php 
                                                    switch($property['status']) {
                                                        case 'approved': case 'active': echo 'bg-success'; break;
                                                        case 'pending': echo 'bg-warning'; break;
                                                        case 'rejected': case 'inactive': echo 'bg-danger'; break;
                                                        default: echo 'bg-secondary';
                                                    }
                                                ?>">
                                                    <?php 
                                                    switch($property['status']) {
                                                        case 'approved': case 'active': echo 'Aktif'; break;
                                                        case 'pending': echo 'Beklemede'; break;
                                                        case 'rejected': echo 'Reddedildi'; break;
                                                        case 'inactive': echo 'Pasif'; break;
                                                        default: echo 'Bilinmiyor';
                                                    }
                                                    ?>
                                                </span>
                                                <a href="../property-details.php?id=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-primary ms-2" title="Görüntüle" target="_blank">
                                                    <i class="fas fa-eye"></i>
                                                </a>
                                                <a href="add-property.php?edit=<?= $property['id'] ?>" 
                                                   class="btn btn-sm btn-warning ms-1" title="Düzenle">
                                                    <i class="fas fa-edit"></i>
                                                </a>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-home"></i>
                                        <h6>Henüz ilan eklenmemiş</h6>
                                        <p>İlk emlak ilanınızı ekleyin.</p>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
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
            const sidebar = document.querySelector('.sidebar-user');
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

        // Password change form validation
        document.getElementById('passwordChangeForm')?.addEventListener('submit', function(e) {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('new_password_confirm').value;
            
            if (newPassword !== confirmPassword) {
                e.preventDefault();
                alert('Yeni şifreler eşleşmiyor!');
                return false;
            }
            
            if (newPassword.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır!');
                return false;
            }
        });
    </script>
</body>
</html>
</html>