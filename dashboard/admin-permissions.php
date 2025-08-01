<?php
// Error reporting ayarları
error_reporting(E_ALL & ~E_WARNING & ~E_NOTICE);
ini_set('display_errors', 0);

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
$current_page = 'admin-permissions';
$page_title = $user['name'] . ' - Yetki Yönetimi';
$user_name = $user['name']; // Sidebar için

// Yetki işlemleri
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    $target_user_id = intval($_POST['user_id']);
    
    // Kendini düzenlemeyi engelle
    if ($target_user_id === $user_id) {
        $error = "Kendi yetkinizi değiştiremezsiniz!";
    } else {
        switch ($action) {
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
                $stmt = $conn->prepare("UPDATE users SET role = 'user' WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                if ($stmt->execute()) {
                    $success = "Kullanıcı normal üye yapıldı!";
                } else {
                    $error = "Kullanıcı normal üye yapılamadı!";
                }
                break;
                
            case 'activate':
                $stmt = $conn->prepare("UPDATE users SET is_active = 1 WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                if ($stmt->execute()) {
                    $success = "Kullanıcı aktif hale getirildi!";
                } else {
                    $error = "Kullanıcı aktif hale getirilemedi!";
                }
                break;
                
            case 'deactivate':
                $stmt = $conn->prepare("UPDATE users SET is_active = 0 WHERE id = ?");
                $stmt->bind_param("i", $target_user_id);
                if ($stmt->execute()) {
                    $success = "Kullanıcı pasif hale getirildi!";
                } else {
                    $error = "Kullanıcı pasif hale getirilemedi!";
                }
                break;
                
            case 'delete':
                $conn->begin_transaction();
                try {
                    // Kullanıcının ilanlarını ve verilerini sil
                    $conn->prepare("DELETE FROM favorites WHERE user_id = ?")->execute([$target_user_id]);
                    $conn->prepare("DELETE FROM properties WHERE user_id = ?")->execute([$target_user_id]);
                    $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$target_user_id]);
                    
                    $conn->commit();
                    $success = "Kullanıcı ve tüm verileri başarıyla silindi!";
                } catch (Exception $e) {
                    $conn->rollback();
                    $error = "Kullanıcı silinirken hata oluştu!";
                }
                break;
        }
    }
}

// Filtreleme ve sayfalama
$page = isset($_GET['page']) ? max(1, (int)$_GET['page']) : 1;
$limit = 15;
$offset = ($page - 1) * $limit;

$role_filter = isset($_GET['role']) ? $_GET['role'] : '';
$status_filter = isset($_GET['status']) ? $_GET['status'] : '';
$search = isset($_GET['search']) ? trim($_GET['search']) : '';

// Where koşulları
$where_conditions = ["1=1"];
$params = [];
$types = "";

if ($role_filter && in_array($role_filter, ['admin', 'user'])) {
    $where_conditions[] = "role = ?";
    $params[] = $role_filter;
    $types .= "s";
}

if ($status_filter !== '') {
    $where_conditions[] = "is_active = ?";
    $params[] = $status_filter === 'active' ? 1 : 0;
    $types .= "i";
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
    $total_users = $count_stmt->get_result()->fetch_assoc()['total'];
} else {
    $total_users = $conn->query($count_query)->fetch_assoc()['total'];
}

$total_pages = ceil($total_users / $limit);

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
    $users_result = $users_stmt->get_result();
} else {
    $users_result = $conn->query($users_query);
}

// İstatistikler
$stats_query = "SELECT 
    COUNT(*) as total_users,
    SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_users,
    SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as regular_users,
    SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_users,
    SUM(CASE WHEN is_active = 0 THEN 1 ELSE 0 END) as inactive_users
    FROM users";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();
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
    <link rel="stylesheet" href="includes/dashboard-common.css">
    
    <style>
        /* Clean Admin Permissions Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: linear-gradient(135deg, #f5f7fa 0%, #c3cfe2 100%);
            transition: margin-left 0.3s ease;
        }
        
        .main-content {
            padding: 30px;
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
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
            position: relative;
            z-index: 1;
            text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
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
            border-top: 4px solid #0d6efd;
        }

        .stats-card:hover {
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

        .filter-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }

        .filter-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .user-card {
            background: white;
            border-radius: 20px;
            padding: 25px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 20px;
            border: 1px solid #f0f0f0;
            transition: all 0.3s ease;
            position: relative;
        }

        .user-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 40px rgba(0,0,0,0.12);
        }

        .user-card.admin {
            border-left: 4px solid #0d6efd;
        }

        .user-card.user {
            border-left: 4px solid #28a745;
        }

        .user-card.inactive {
            border-left: 4px solid #dc3545;
            opacity: 0.7;
        }

        .user-header {
            display: flex;
            align-items: center;
            gap: 15px;
            margin-bottom: 20px;
        }

        .user-avatar {
            width: 60px;
            height: 60px;
            border-radius: 15px;
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            font-weight: 600;
        }

        .user-info h5 {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 5px;
        }

        .user-email {
            color: #6c757d;
            font-size: 14px;
            margin-bottom: 5px;
        }

        .user-role {
            padding: 4px 10px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
            display: inline-block;
        }

        .user-role.admin {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .user-role.user {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .user-status {
            position: absolute;
            top: 20px;
            right: 20px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 600;
            text-transform: uppercase;
        }

        .user-status.active {
            background: rgba(40, 167, 69, 0.1);
            color: #28a745;
        }

        .user-status.inactive {
            background: rgba(220, 53, 69, 0.1);
            color: #dc3545;
        }

        .user-meta {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(150px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
            padding: 15px;
            background: #f8f9fa;
            border-radius: 10px;
        }

        .user-meta-item {
            text-align: center;
        }

        .user-meta-label {
            font-size: 12px;
            color: #6c757d;
            margin-bottom: 5px;
        }

        .user-meta-value {
            font-size: 16px;
            font-weight: 600;
            color: #0d1a1c;
        }

        .user-actions {
            display: flex;
            gap: 8px;
            flex-wrap: wrap;
        }

        .user-action-btn {
            padding: 8px 12px;
            border-radius: 8px;
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

        .user-action-btn:hover {
            transform: translateY(-2px);
        }

        .user-action-btn.btn-admin:hover {
            background: #0d6efd;
            border-color: #0d6efd;
            color: white;
        }

        .user-action-btn.btn-user:hover {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .user-action-btn.btn-activate:hover {
            background: #28a745;
            border-color: #28a745;
            color: white;
        }

        .user-action-btn.btn-deactivate:hover {
            background: #ffc107;
            border-color: #ffc107;
            color: #212529;
        }

        .user-action-btn.btn-delete:hover {
            background: #dc3545;
            border-color: #dc3545;
            color: white;
        }

        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .user-header {
                flex-direction: column;
                text-align: center;
                gap: 10px;
            }
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar-admin.php'; ?>
    
    <!-- Dashboard Body -->
    <div class="dashboard-body">
        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">
                    <i class="fas fa-user-shield me-3"></i>Yetki Yönetimi
                </h2>
                <p class="welcome-subtitle">Kullanıcı yetkilerini ve hesap durumlarını yönetin</p>
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

            <!-- Statistics Section -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?= $stats['total_users'] ?></div>
                    <div class="stats-label">Toplam Kullanıcı</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stats-number"><?= $stats['admin_users'] ?></div>
                    <div class="stats-label">Admin</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-check"></i>
                    </div>
                    <div class="stats-number"><?= $stats['active_users'] ?></div>
                    <div class="stats-label">Aktif</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user"></i>
                    </div>
                    <div class="stats-number"><?= $stats['regular_users'] ?></div>
                    <div class="stats-label">Normal Kullanıcı</div>
                </div>
            </div>

            <!-- Filter Section -->
            <div class="filter-section">
                <div class="filter-title">
                    <i class="fas fa-filter me-2"></i>Filtreler ve Arama
                </div>
                <form method="GET" class="row g-3">
                    <div class="col-md-3">
                        <label class="form-label">Rol</label>
                        <select name="role" class="form-select">
                            <option value="">Tüm Roller</option>
                            <option value="admin" <?= $role_filter === 'admin' ? 'selected' : '' ?>>Admin</option>
                            <option value="user" <?= $role_filter === 'user' ? 'selected' : '' ?>>Kullanıcı</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label class="form-label">Durum</label>
                        <select name="status" class="form-select">
                            <option value="">Tüm Durumlar</option>
                            <option value="active" <?= $status_filter === 'active' ? 'selected' : '' ?>>Aktif</option>
                            <option value="inactive" <?= $status_filter === 'inactive' ? 'selected' : '' ?>>Pasif</option>
                        </select>
                    </div>
                    <div class="col-md-5">
                        <label class="form-label">Arama</label>
                        <input type="text" class="form-control" name="search" placeholder="İsim veya email ara..." value="<?= htmlspecialchars($search) ?>">
                    </div>
                    <div class="col-md-2 d-flex align-items-end">
                        <button type="submit" class="dash-btn-two w-100">
                            <i class="fas fa-search me-2"></i>Ara
                        </button>
                    </div>
                </form>
            </div>

            <!-- Users List -->
            <?php if ($users_result && $users_result->num_rows > 0): ?>
            <div class="row">
                <?php while ($target_user = $users_result->fetch_assoc()): ?>
                <div class="col-lg-6 col-xl-4">
                    <div class="user-card <?= $target_user['role'] ?> <?= $target_user['is_active'] ? '' : 'inactive' ?>">
                        <div class="user-status <?= $target_user['is_active'] ? 'active' : 'inactive' ?>">
                            <?= $target_user['is_active'] ? 'Aktif' : 'Pasif' ?>
                        </div>
                        
                        <div class="user-header">
                            <div class="user-avatar">
                                <?= strtoupper(substr($target_user['name'], 0, 2)) ?>
                            </div>
                            <div class="user-info">
                                <h5><?= htmlspecialchars($target_user['name']) ?></h5>
                                <div class="user-email"><?= htmlspecialchars($target_user['email']) ?></div>
                                <span class="user-role <?= $target_user['role'] ?>">
                                    <?= $target_user['role'] === 'admin' ? 'Admin' : 'Kullanıcı' ?>
                                </span>
                            </div>
                        </div>
                        
                        <div class="user-meta">
                            <div class="user-meta-item">
                                <div class="user-meta-label">İlanlar</div>
                                <div class="user-meta-value"><?= $target_user['property_count'] ?></div>
                            </div>
                            <div class="user-meta-item">
                                <div class="user-meta-label">Kayıt Tarihi</div>
                                <div class="user-meta-value">
                                    <?= $target_user['created_at'] ? date('d.m.Y', strtotime($target_user['created_at'])) : '-' ?>
                                </div>
                            </div>
                            <div class="user-meta-item">
                                <div class="user-meta-label">Son İlan</div>
                                <div class="user-meta-value">
                                    <?= $target_user['last_property_date'] ? date('d.m.Y', strtotime($target_user['last_property_date'])) : '-' ?>
                                </div>
                            </div>
                        </div>

                        <div class="user-actions">
                            <?php if ($target_user['id'] !== $user_id): ?>
                                <!-- Rol değiştirme butonları -->
                                <?php if ($target_user['role'] !== 'admin'): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $target_user['id'] ?>">
                                        <input type="hidden" name="action" value="make_admin">
                                        <button type="submit" class="user-action-btn btn-admin" 
                                                onclick="return confirm('Bu kullanıcıyı admin yapmak istediğinizden emin misiniz?')">
                                            <i class="fas fa-user-shield"></i>
                                            Admin Yap
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $target_user['id'] ?>">
                                        <input type="hidden" name="action" value="make_user">
                                        <button type="submit" class="user-action-btn btn-user" 
                                                onclick="return confirm('Bu kullanıcıyı normal üye yapmak istediğinizden emin misiniz?')">
                                            <i class="fas fa-user"></i>
                                            Üye Yap
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Aktiflik durumu değiştirme -->
                                <?php if ($target_user['is_active']): ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $target_user['id'] ?>">
                                        <input type="hidden" name="action" value="deactivate">
                                        <button type="submit" class="user-action-btn btn-deactivate" 
                                                onclick="return confirm('Bu kullanıcıyı pasif hale getirmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-user-times"></i>
                                            Pasifleştir
                                        </button>
                                    </form>
                                <?php else: ?>
                                    <form method="POST" class="d-inline">
                                        <input type="hidden" name="user_id" value="<?= $target_user['id'] ?>">
                                        <input type="hidden" name="action" value="activate">
                                        <button type="submit" class="user-action-btn btn-activate">
                                            <i class="fas fa-user-check"></i>
                                            Aktifleştir
                                        </button>
                                    </form>
                                <?php endif; ?>

                                <!-- Kullanıcıyı sil -->
                                <form method="POST" class="d-inline">
                                    <input type="hidden" name="user_id" value="<?= $target_user['id'] ?>">
                                    <input type="hidden" name="action" value="delete">
                                    <button type="submit" class="user-action-btn btn-delete" 
                                            onclick="return confirm('UYARI: Bu kullanıcıyı ve tüm verilerini kalıcı olarak silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                                        <i class="fas fa-trash"></i>
                                        Sil
                                    </button>
                                </form>
                            <?php else: ?>
                                <span class="user-action-btn" style="background: #e9ecef; color: #6c757d; cursor: not-allowed;">
                                    <i class="fas fa-user-cog"></i>
                                    Bu Sizsiniz
                                </span>
                            <?php endif; ?>
                        </div>
                    </div>
                </div>
                <?php endwhile; ?>
            </div>

            <!-- Pagination -->
            <?php if ($total_pages > 1): ?>
            <nav aria-label="Kullanıcı sayfalama">
                <ul class="pagination pagination-custom">
                    <?php if ($page > 1): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page - 1 ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">
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
                        <a class="page-link" href="?page=<?= $i ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>"><?= $i ?></a>
                    </li>
                    <?php endfor; ?>
                    
                    <?php if ($page < $total_pages): ?>
                    <li class="page-item">
                        <a class="page-link" href="?page=<?= $page + 1 ?>&role=<?= urlencode($role_filter) ?>&status=<?= urlencode($status_filter) ?>&search=<?= urlencode($search) ?>">
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
                <i class="fas fa-users"></i>
                <h4>
                    <?php if ($role_filter || $status_filter || $search): ?>
                        Filtrelere uygun kullanıcı bulunamadı
                    <?php else: ?>
                        Henüz kullanıcı bulunmuyor
                    <?php endif; ?>
                </h4>
                <p>
                    <?php if ($role_filter || $status_filter || $search): ?>
                        Farklı filtreler deneyebilirsiniz.
                    <?php else: ?>
                        Sistem henüz yeni kullanıcı almamış.
                    <?php endif; ?>
                </p>
                <?php if ($role_filter || $status_filter || $search): ?>
                    <a href="admin-permissions.php" class="dash-btn-two">
                        <i class="fas fa-users me-2"></i>
                        Tüm Kullanıcıları Göster
                    </a>
                <?php endif; ?>
            </div>
            <?php endif; ?>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
