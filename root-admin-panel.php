<?php
session_start();
include 'db.php';

// Güvenlik kontrolü - Sadece root erişebilir
if (!isset($_SESSION['user_id']) || !isRoot($_SESSION['user_id'], $conn)) {
    header('Location: login.php?error=root_required');
    exit();
}

$message = '';
$message_type = '';

// Admin ekleme işlemi
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    // CSRF koruması
    if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== ($_SESSION['csrf_token'] ?? '')) {
        $message = "Güvenlik hatası!";
        $message_type = 'danger';
    } else {
        $action = $_POST['action'] ?? '';
        
        if ($action == 'add_admin') {
            $name = trim($_POST['name'] ?? '');
            $email = trim($_POST['email'] ?? '');
            $password = $_POST['password'] ?? '';
            $role = $_POST['role'] ?? 'admin';
            
            // Validasyon
            if (empty($name) || empty($email) || empty($password)) {
                $message = "Tüm alanlar zorunludur!";
                $message_type = 'danger';
            } elseif (strlen($password) < 6) {
                $message = "Şifre en az 6 karakter olmalıdır!";
                $message_type = 'danger';
            } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $message = "Geçerli bir email adresi giriniz!";
                $message_type = 'danger';
            } else {
                // Email zaten var mı kontrol et
                $check_email = $conn->prepare("SELECT id FROM users WHERE email = ?");
                $check_email->bind_param("s", $email);
                $check_email->execute();
                
                if ($check_email->get_result()->num_rows > 0) {
                    $message = "Bu email adresi zaten kullanılıyor!";
                    $message_type = 'danger';
                } else {
                    // Yeni admin ekle
                    $hashed_password = password_hash($password, PASSWORD_DEFAULT);
                    $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, ?, TRUE, TRUE)");
                    $insert_admin->bind_param("ssss", $name, $email, $hashed_password, $role);
                    
                    if ($insert_admin->execute()) {
                        $message = "✅ $role kullanıcısı başarıyla eklendi!<br>Email: $email<br>Şifre: $password";
                        $message_type = 'success';
                    } else {
                        $message = "❌ Kullanıcı eklenirken hata oluştu: " . $conn->error;
                        $message_type = 'danger';
                    }
                }
                $check_email->close();
            }
        }
        
        elseif ($action == 'update_role') {
            $user_id = intval($_POST['user_id'] ?? 0);
            $new_role = $_POST['new_role'] ?? '';
            
            if ($user_id > 0 && in_array($new_role, ['admin', 'agent', 'user'])) {
                $update_role = $conn->prepare("UPDATE users SET role = ? WHERE id = ? AND role != 'root'");
                $update_role->bind_param("si", $new_role, $user_id);
                
                if ($update_role->execute()) {
                    $message = "✅ Kullanıcı rolü güncellendi!";
                    $message_type = 'success';
                } else {
                    $message = "❌ Rol güncellenemedi!";
                    $message_type = 'danger';
                }
            }
        }
        
        elseif ($action == 'toggle_status') {
            $user_id = intval($_POST['user_id'] ?? 0);
            
            if ($user_id > 0) {
                $toggle_status = $conn->prepare("UPDATE users SET is_active = NOT is_active WHERE id = ? AND role != 'root'");
                $toggle_status->bind_param("i", $user_id);
                
                if ($toggle_status->execute()) {
                    $message = "✅ Kullanıcı durumu güncellendi!";
                    $message_type = 'success';
                } else {
                    $message = "❌ Durum güncellenemedi!";
                    $message_type = 'danger';
                }
            }
        }
    }
}

// CSRF token oluştur
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Tüm kullanıcıları getir
$users_query = "SELECT id, name, email, role, is_active, created_at, last_login FROM users ORDER BY role DESC, created_at DESC";
$users_result = $conn->query($users_query);

// İstatistikler
$stats_query = "
    SELECT 
        COUNT(*) as total_users,
        SUM(CASE WHEN role = 'root' THEN 1 ELSE 0 END) as root_count,
        SUM(CASE WHEN role = 'admin' THEN 1 ELSE 0 END) as admin_count,
        SUM(CASE WHEN role = 'agent' THEN 1 ELSE 0 END) as agent_count,
        SUM(CASE WHEN role = 'user' THEN 1 ELSE 0 END) as user_count,
        SUM(CASE WHEN is_active = 1 THEN 1 ELSE 0 END) as active_count
    FROM users
";
$stats = $conn->query($stats_query)->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Root Admin Panel - Kullanıcı Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); min-height: 100vh; }
        .admin-card { 
            background: white; 
            border-radius: 15px; 
            box-shadow: 0 10px 30px rgba(0,0,0,0.2); 
            overflow: hidden;
        }
        .card-header { 
            background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); 
            color: white; 
            padding: 20px;
        }
        .badge-root { background: linear-gradient(135deg, #8B0000 0%, #DC143C 100%); }
        .badge-admin { background: linear-gradient(135deg, #dc3545 0%, #c82333 100%); }
        .badge-agent { background: linear-gradient(135deg, #ffc107 0%, #e0a800 100%); }
        .badge-user { background: linear-gradient(135deg, #6c757d 0%, #495057 100%); }
        .stats-card { background: #f8f9fa; border-radius: 10px; padding: 15px; margin: 10px 0; }
    </style>
</head>
<body>
    <div class="container py-4">
        <div class="row justify-content-center">
            <div class="col-md-12">
                <div class="admin-card">
                    <div class="card-header text-center">
                        <h2><i class="fas fa-crown me-2"></i>Root Admin Panel</h2>
                        <p class="mb-0">Kullanıcı Yönetimi ve Admin Ekleme</p>
                    </div>
                    
                    <div class="card-body p-4">
                        <!-- Navigation -->
                        <div class="text-center mb-4">
                            <a href="dashboard/dashboard-admin.php" class="btn btn-outline-secondary">
                                <i class="fas fa-home me-1"></i>Dashboard
                            </a>
                            <a href="database-reset-v2.php" class="btn btn-outline-info">
                                <i class="fas fa-database me-1"></i>Database Yönetimi
                            </a>
                            <a href="logout.php" class="btn btn-outline-danger">
                                <i class="fas fa-sign-out-alt me-1"></i>Çıkış
                            </a>
                        </div>

                        <!-- Alert Messages -->
                        <?php if ($message): ?>
                            <div class="alert alert-<?= $message_type ?> alert-dismissible fade show" role="alert">
                                <?= $message ?>
                                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                            </div>
                        <?php endif; ?>

                        <!-- İstatistikler -->
                        <div class="row mb-4">
                            <div class="col-12">
                                <h4><i class="fas fa-chart-bar me-2"></i>Kullanıcı İstatistikleri</h4>
                                <div class="row">
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>Toplam</h6>
                                            <h4 class="text-primary"><?= $stats['total_users'] ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>Root</h6>
                                            <h4 class="text-danger"><?= $stats['root_count'] ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>Admin</h6>
                                            <h4 class="text-warning"><?= $stats['admin_count'] ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>Agent</h6>
                                            <h4 class="text-info"><?= $stats['agent_count'] ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>User</h6>
                                            <h4 class="text-secondary"><?= $stats['user_count'] ?></h4>
                                        </div>
                                    </div>
                                    <div class="col-md-2">
                                        <div class="stats-card text-center">
                                            <h6>Aktif</h6>
                                            <h4 class="text-success"><?= $stats['active_count'] ?></h4>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="row">
                            <!-- Admin Ekleme Formu -->
                            <div class="col-md-4">
                                <h4><i class="fas fa-user-plus me-2 text-success"></i>Yeni Kullanıcı Ekle</h4>
                                
                                <form method="POST">
                                    <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                    <input type="hidden" name="action" value="add_admin">
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Ad Soyad</label>
                                        <input type="text" class="form-control" name="name" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Email</label>
                                        <input type="email" class="form-control" name="email" required>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Şifre</label>
                                        <input type="password" class="form-control" name="password" required minlength="6">
                                        <small class="text-muted">Minimum 6 karakter</small>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Rol</label>
                                        <select class="form-select" name="role" required>
                                            <option value="admin">Admin</option>
                                            <option value="agent">Agent</option>
                                            <option value="user">User</option>
                                        </select>
                                    </div>
                                    
                                    <button type="submit" class="btn btn-success w-100">
                                        <i class="fas fa-user-plus me-1"></i>Kullanıcı Ekle
                                    </button>
                                </form>
                            </div>
                            
                            <!-- Kullanıcı Listesi -->
                            <div class="col-md-8">
                                <h4><i class="fas fa-users me-2 text-info"></i>Mevcut Kullanıcılar</h4>
                                
                                <div class="table-responsive">
                                    <table class="table table-hover">
                                        <thead class="table-dark">
                                            <tr>
                                                <th>Ad Soyad</th>
                                                <th>Email</th>
                                                <th>Rol</th>
                                                <th>Durum</th>
                                                <th>Kayıt</th>
                                                <th>İşlemler</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <?php while ($user = $users_result->fetch_assoc()): ?>
                                            <tr>
                                                <td><?= htmlspecialchars($user['name']) ?></td>
                                                <td><?= htmlspecialchars($user['email']) ?></td>
                                                <td>
                                                    <span class="badge badge-<?= $user['role'] ?> px-2 py-1">
                                                        <?= strtoupper($user['role']) ?>
                                                    </span>
                                                </td>
                                                <td>
                                                    <?php if ($user['is_active']): ?>
                                                        <span class="badge bg-success">Aktif</span>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">Pasif</span>
                                                    <?php endif; ?>
                                                </td>
                                                <td><?= date('d.m.Y', strtotime($user['created_at'])) ?></td>
                                                <td>
                                                    <?php if ($user['role'] != 'root'): ?>
                                                        <!-- Rol Değiştir -->
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                            <input type="hidden" name="action" value="update_role">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <select name="new_role" class="form-select form-select-sm d-inline w-auto" onchange="this.form.submit()">
                                                                <option value="admin" <?= $user['role'] == 'admin' ? 'selected' : '' ?>>Admin</option>
                                                                <option value="agent" <?= $user['role'] == 'agent' ? 'selected' : '' ?>>Agent</option>
                                                                <option value="user" <?= $user['role'] == 'user' ? 'selected' : '' ?>>User</option>
                                                            </select>
                                                        </form>
                                                        
                                                        <!-- Durum Değiştir -->
                                                        <form method="POST" class="d-inline">
                                                            <input type="hidden" name="csrf_token" value="<?= $_SESSION['csrf_token'] ?>">
                                                            <input type="hidden" name="action" value="toggle_status">
                                                            <input type="hidden" name="user_id" value="<?= $user['id'] ?>">
                                                            <button type="submit" class="btn btn-sm <?= $user['is_active'] ? 'btn-outline-danger' : 'btn-outline-success' ?>">
                                                                <i class="fas fa-power-off"></i>
                                                            </button>
                                                        </form>
                                                    <?php else: ?>
                                                        <span class="badge bg-danger">ROOT - Değiştirilemez</span>
                                                    <?php endif; ?>
                                                </td>
                                            </tr>
                                            <?php endwhile; ?>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Bilgi -->
                        <div class="alert alert-info mt-4">
                            <h6><i class="fas fa-info-circle me-2"></i>Önemli Bilgiler:</h6>
                            <ul class="mb-0">
                                <li><strong>Root:</strong> Tüm yetkilere sahip, değiştirilemez</li>
                                <li><strong>Admin:</strong> Blog ve emlak yönetimi yapabilir</li>
                                <li><strong>Agent:</strong> Emlak ilanı ekleyebilir</li>
                                <li><strong>User:</strong> Standart kullanıcı hakları</li>
                                <li>Sadece Root kullanıcıları admin/agent yaratabilir</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
