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
$current_page = 'admin-users';
$page_title = $user['name'] . ' - Kullanıcı Yönetimi';
$user_name = $user['name']; // Sidebar için

// Kullanıcı işlemleri
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $target_user_id = intval($_POST['user_id']);
    
    switch ($action) {
        case 'activate':
            $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $success = "Kullanıcı başarıyla aktifleştirildi!";
            } else {
                $error = "Kullanıcı aktifleştirilemedi!";
            }
            break;
            
        case 'deactivate':
            $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $success = "Kullanıcı başarıyla pasifleştirildi!";
            } else {
                $error = "Kullanıcı pasifleştirilemedi!";
            }
            break;
            
        case 'make_admin':
            $stmt = $conn->prepare("UPDATE users SET role = 'admin' WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $success = "Kullanıcı admin yapıldı!";
            } else {
                $error = "Kullanıcı admin yapılamadı!";
            }
            break;
            
        case 'make_user':
            if ($target_user_id !== $user_id) { // Kendi kendini user yapmasını engelle
                $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                if ($stmt->execute()) {
                    $success = "Kullanıcı normal kullanıcı yapıldı!";
                } else {
                    $error = "Kullanıcı normal kullanıcı yapılamadı!";
                }
            } else {
                $error = "Kendi rolünüzü değiştiremezsiniz!";
            }
            break;
            
        case 'toggle_property_permission':
            $stmt = $conn->prepare("UPDATE users SET can_add_property = NOT can_add_property WHERE id = ?");
            $stmt->bind_param("i", $target_user_id);
            if ($stmt->execute()) {
                $success = "İlan ekleme yetkisi güncellendi!";
            } else {
                $error = "İlan ekleme yetkisi güncellenemedi!";
            }
            break;
            
        case 'delete':
            if ($target_user_id !== $user_id) { // Kendi kendini silmesini engelle
                $conn->begin_transaction();
                try {
                    // Önce kullanıcı bilgilerini alalım (debug için)
                    $check_stmt = $conn->prepare("SELECT email FROM users WHERE id = ?");
                    $check_stmt->bind_param("i", $target_user_id);
                    $check_stmt->execute();
                    $user_email = $check_stmt->get_result()->fetch_assoc()['email'] ?? 'bilinmiyor';
                    
                    // Kullanıcının verilerini sil
                    $stmt = $conn->prepare("DELETE FROM properties WHERE user_id = ?");
                    $stmt->bind_param("i", $target_user_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM favorites WHERE user_id = ?");
                    $stmt->bind_param("i", $target_user_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?");
                    $stmt->bind_param("ii", $target_user_id, $target_user_id);
                    $stmt->execute();
                    
                    $stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
                    $stmt->bind_param("i", $target_user_id);
                    $stmt->execute();
                    $affected_rows = $stmt->affected_rows;
                    
                    // Silme işleminin başarılı olup olmadığını kontrol et
                    $verify_stmt = $conn->prepare("SELECT COUNT(*) as count FROM users WHERE id = ?");
                    $verify_stmt->bind_param("i", $target_user_id);
                    $verify_stmt->execute();
                    $still_exists = $verify_stmt->get_result()->fetch_assoc()['count'];
                    
                    if ($affected_rows > 0 && $still_exists == 0) {
                        $conn->commit();
                        $success = "Kullanıcı ($user_email) ve tüm verileri başarıyla silindi! Silinen satır sayısı: $affected_rows";
                    } else {
                        $conn->rollback();
                        $error = "Kullanıcı silinirken hata oluştu. Etkilenen satır: $affected_rows, Hala var mı: $still_exists";
                    }
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Kullanıcı silinirken hata oluştu: " . $e->getMessage();
                }
            } else {
                $error = "Kendi hesabınızı silemezsiniz!";
            }
            break;
    }
}

// Filtreleme ve sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 10;
$offset = ($page - 1) * $limit;

$role_filter = isset($_GET['filter_role']) ? $_GET['filter_role'] : '';
$status_filter = isset($_GET['filter_status']) ? $_GET['filter_status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Where koşulları
$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($role_filter && in_array($role_filter, ['admin', 'agent', 'user'])) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if ($status_filter === '1') {
    $where_conditions[] = "is_active = 1";
} elseif ($status_filter === '0') {
    $where_conditions[] = "is_active = 0";
}

if ($search) {
    $where_conditions[] = "(name LIKE ? OR email LIKE ?)";
    $params[] = "%$search%";
    $params[] = "%$search%";
    $types .= "ss";
}

$where_clause = implode(" AND ", $where_conditions);

// Toplam kullanıcı sayısı
$count_query = "SELECT COUNT(*) as total FROM users WHERE $where_clause";
if ($params) {
    $count_stmt = $conn->prepare($count_query);
    $count_stmt->bind_param($types, ...$params);
    $count_stmt->execute();
    $total_count = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_count = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_count / $limit);

// URL query string for pagination
$query_params = [];
if ($role_filter) $query_params[] = "filter_role=" . urlencode($role_filter);
if ($status_filter !== '') $query_params[] = "filter_status=" . urlencode($status_filter);
if ($search) $query_params[] = "search=" . urlencode($search);
$query_string = $query_params ? '&' . implode('&', $query_params) : '';

// Kullanıcıları getir
$users_query = "SELECT u.*, 
                COUNT(p.id) as property_count,
                MAX(p.created_at) as last_property_date
                FROM users u 
                LEFT JOIN properties p ON u.id = p.user_id 
                WHERE $where_clause 
                GROUP BY u.id 
                ORDER BY u.created_at DESC 
                LIMIT $limit OFFSET $offset";

if ($params) {
    $users_stmt = $conn->prepare($users_query);
    $users_stmt->bind_param($types, ...$params);
    $users_stmt->execute();
    $users = $users_stmt->get_result();
} else {
    $users = $conn->query($users_query);
}

// İstatistikler - can_add_property kolonu kontrolü
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count";

// can_add_property kolunu kontrol et
$check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'can_add_property'");
if ($check_column && $check_column->num_rows > 0) {
    $stats_query .= ", SUM(CASE WHEN can_add_property = 1 THEN 1 ELSE 0 END) as can_add_property";
} else {
    $stats_query .= ", 0 as can_add_property";
}

$stats_query .= " FROM users";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Sayfa bilgileri
$current_page = 'admin-users';
$user_name = $user['name'];
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
        /* Dashboard Admin Users Specific Styles */
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
        
        /* Welcome Banner Styles */
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

        .user-card.inactive {
            border-left-color: #6c757d;
            opacity: 0.7;
        }

        .user-header {
            background: linear-gradient(135deg, #f8f9fa, #e9ecef);
            padding: 20px 25px;
            border-bottom: 1px solid #f0f0f0;
        }

        .user-avatar {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: 600;
            font-size: 16px;
            flex-shrink: 0;
        }

        .user-name {
            font-size: 16px;
            font-weight: 600;
            color: #0d1a1c;
        }

        .user-email {
            font-size: 14px;
            color: #6c757d;
        }

        .user-badges .badge {
            font-size: 11px;
            padding: 4px 8px;
        }

        .user-meta {
            font-size: 12px;
        }

        .user-actions .btn {
            border-radius: 8px;
        }

        .empty-state {
            background: white;
            border-radius: 20px;
            padding: 60px 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .alert-custom {
            border-radius: 15px;
            border: none;
            padding: 20px;
            margin-bottom: 25px;
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
            
            .user-card {
                padding: 20px;
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
                <h5 class="mobile-title">Kullanıcı Yönetimi</h5>
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
                                <i class="fas fa-users-cog me-2"></i>
                                Kullanıcı Yönetimi
                            </h2>
                            <p class="welcome-subtitle">
                                Sistemdeki tüm kullanıcıları yönetebilir, durumlarını değiştirebilir ve 
                                detaylı bilgilerini görüntüleyebilirsiniz.
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
                                <i class="fas fa-users"></i>
                            </div>
                            <div class="stats-number"><?= $stats['total_users'] ?></div>
                            <div class="stats-label">Toplam Kullanıcı</div>
                            <div class="stats-change">
                                <i class="fas fa-chart-line"></i>
                                <span>Sistemde kayıtlı</span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                            <div class="stats-number"><?= $stats['active_users'] ?></div>
                            <div class="stats-label">Aktif Kullanıcı</div>
                            <div class="stats-change">
                                <i class="fas fa-check"></i>
                                <span>Erişim sağlayabiliyor</span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-user-shield"></i>
                            </div>
                            <div class="stats-number"><?= $stats['admin_count'] ?></div>
                            <div class="stats-label">Admin Kullanıcı</div>
                            <div class="stats-change">
                                <i class="fas fa-crown"></i>
                                <span>Yönetim yetkisi var</span>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-plus"></i>
                            </div>
                            <div class="stats-number"><?= $stats['can_add_property'] ?></div>
                            <div class="stats-label">İlan Ekleyebilen</div>
                            <div class="stats-change">
                                <i class="fas fa-home"></i>
                                <span>Emlak yetki var</span>
                            </div>
                        </div>
                    </div>

                    <!-- Filtreleme Bölümü -->
                    <div class="filter-section">
                        <h3 class="section-title">
                            <i class="fas fa-filter"></i>
                            Filtreleme ve Arama
                        </h3>
                        
                        <form method="GET" class="row g-3">
                            <div class="col-md-3">
                                <label for="filter_role" class="form-label">Rol</label>
                                <select name="filter_role" id="filter_role" class="form-select">
                                    <option value="">Tüm Roller</option>
                                    <option value="admin" <?= (isset($_GET['filter_role']) && $_GET['filter_role'] == 'admin') ? 'selected' : '' ?>>Admin</option>
                                    <option value="agent" <?= (isset($_GET['filter_role']) && $_GET['filter_role'] == 'agent') ? 'selected' : '' ?>>Agent</option>
                                    <option value="user" <?= (isset($_GET['filter_role']) && $_GET['filter_role'] == 'user') ? 'selected' : '' ?>>User</option>
                                </select>
                            </div>
                            <div class="col-md-3">
                                <label for="filter_status" class="form-label">Durum</label>
                                <select name="filter_status" id="filter_status" class="form-select">
                                    <option value="">Tüm Durumlar</option>
                                    <option value="1" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == '1') ? 'selected' : '' ?>>Aktif</option>
                                    <option value="0" <?= (isset($_GET['filter_status']) && $_GET['filter_status'] == '0') ? 'selected' : '' ?>>Pasif</option>
                                </select>
                            </div>
                            <div class="col-md-4">
                                <label for="search" class="form-label">Arama</label>
                                <input type="text" name="search" id="search" class="form-control" 
                                       placeholder="İsim veya email ile ara..." 
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

                    <!-- Kullanıcı Listesi -->
                    <div class="user-list-section">
                        <div class="d-flex justify-content-between align-items-center mb-4">
                            <h3 class="section-title">
                                <i class="fas fa-list"></i>
                                Kullanıcı Listesi (<?= $total_count ?> kullanıcı)
                            </h3>
                            <div class="d-flex gap-2">
                                <button class="btn btn-success btn-sm" data-bs-toggle="modal" data-bs-target="#addUserModal">
                                    <i class="fas fa-plus"></i> Yeni Kullanıcı
                                </button>
                                <a href="?export=excel" class="btn btn-info btn-sm">
                                    <i class="fas fa-download"></i> Excel İndir
                                </a>
                            </div>
                        </div>

                        <?php if ($users && $users->num_rows > 0): ?>
                            <div class="row">
                                <?php while ($user_item = $users->fetch_assoc()): ?>
                                    <div class="col-lg-6 col-xl-4 mb-4">
                                        <div class="user-card">
                                            <div class="d-flex align-items-start">
                                                <div class="user-avatar me-3">
                                                    <?php if ($user_item['role'] == 'admin'): ?>
                                                        <i class="fas fa-crown"></i>
                                                    <?php else: ?>
                                                        <?= strtoupper(substr($user_item['name'], 0, 2)) ?>
                                                    <?php endif; ?>
                                                </div>
                                                <div class="flex-grow-1">
                                                    <h6 class="user-name mb-1"><?= htmlspecialchars($user_item['name']) ?></h6>
                                                    <p class="user-email mb-2"><?= htmlspecialchars($user_item['email']) ?></p>
                                                    
                                                    <div class="user-badges mb-3">
                                                        <span class="badge bg-<?= $user_item['role'] == 'admin' ? 'danger' : ($user_item['role'] == 'agent' ? 'warning' : 'primary') ?>">
                                                            <?= ucfirst($user_item['role']) ?>
                                                        </span>
                                                        <span class="badge bg-<?= $user_item['is_active'] ? 'success' : 'secondary' ?>">
                                                            <?= $user_item['is_active'] ? 'Aktif' : 'Pasif' ?>
                                                        </span>
                                                        <?php if (isset($user_item['can_add_property']) && $user_item['can_add_property']): ?>
                                                            <span class="badge bg-info">İlan Ekleyebilir</span>
                                                        <?php endif; ?>
                                                    </div>
                                                    
                                                    <div class="user-meta">
                                                        <small class="text-muted">
                                                            <i class="fas fa-calendar"></i>
                                                            Katılma: <?= date('d.m.Y', strtotime($user_item['created_at'])) ?>
                                                        </small>
                                                    </div>
                                                </div>
                                            </div>
                                            
                                            <div class="user-actions mt-3 pt-3 border-top">
                                                <div class="btn-group w-100" role="group">
                                                    <?php if ($user_item['is_active']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="deactivate">
                                                            <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-warning" 
                                                                    onclick="return confirm('Bu kullanıcıyı pasifleştirmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-pause"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="activate">
                                                            <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-success" 
                                                                    onclick="return confirm('Bu kullanıcıyı aktifleştirmek istediğinizden emin misiniz?')">
                                                                <i class="fas fa-play"></i>
                                                            </button>
                                                        </form>
                                                    <?php endif; ?>
                                                    
                                                    <button class="btn btn-sm btn-outline-primary" onclick="editUser(<?= $user_item['id'] ?>)">
                                                        <i class="fas fa-edit"></i>
                                                    </button>
                                                    
                                                    <button class="btn btn-sm btn-outline-info" onclick="viewUser(<?= $user_item['id'] ?>)">
                                                        <i class="fas fa-eye"></i>
                                                    </button>
                                                    
                                                    <?php if ($user_item['id'] != $_SESSION['user_id']): ?>
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="action" value="delete">
                                                            <input type="hidden" name="user_id" value="<?= $user_item['id'] ?>">
                                                            <button type="submit" class="btn btn-sm btn-outline-danger" 
                                                                    onclick="return confirm('Bu kullanıcıyı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                                                                <i class="fas fa-trash"></i>
                                                            </button>
                                                        </form>
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
                                        <?php for ($i = 1; $i <= $total_pages; $i++): ?>
                                            <li class="page-item <?= ($i == $page) ? 'active' : '' ?>">
                                                <a class="page-link" href="?page=<?= $i ?><?= $query_string ?>"><?= $i ?></a>
                                            </li>
                                        <?php endfor; ?>
                                    </ul>
                                </nav>
                            <?php endif; ?>
                            
                        <?php else: ?>
                            <div class="empty-state text-center py-5">
                                <i class="fas fa-users fa-3x text-muted mb-3"></i>
                                <h5>Kullanıcı bulunamadı</h5>
                                <p class="text-muted">Arama kriterlerinize uygun kullanıcı bulunamadı.</p>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </div>
        </div>
    </div>

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

        // User management functions
        function editUser(userId) {
            // Edit user modal açma fonksiyonu
            console.log('Edit user:', userId);
        }

        function viewUser(userId) {
            // View user modal açma fonksiyonu
            console.log('View user:', userId);
        }

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
    </script>
</body>
</html>