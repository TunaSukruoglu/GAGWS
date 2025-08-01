<?php
session_start();
include '../db.php';

// Admin kontrolü - kullanıcı rolüne göre uygun sidebar
$is_admin = isset($_SESSION['user_role']) && ($_SESSION['user_role'] === 'admin' || $_SESSION['user_role'] === 'root');
$current_page = 'profile';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini çek
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    header("Location: ../index.php");
    exit;
}

$user_name = $user['name'];
$page_title = "Profil Ayarları";

// Hata ve başarı mesajları için değişkenler
$error = null;
$success = null;

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        if (isset($_POST['update_profile'])) {
            // Profil bilgilerini güncelle
            $name = trim($_POST['name']);
            $email = trim($_POST['email']);
            $phone = trim($_POST['phone'] ?? '');
            $bio = trim($_POST['bio'] ?? '');
            
            // Validation
            if (empty($name) || empty($email)) {
                throw new Exception("Ad ve e-posta alanları zorunludur.");
            }
            
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                throw new Exception("Geçerli bir e-posta adresi girin.");
            }
            
            // E-posta tekrar kontrolü
            $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $email_check->bind_param("si", $email, $user_id);
            $email_check->execute();
            if ($email_check->get_result()->num_rows > 0) {
                throw new Exception("Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor.");
            }
            
            // Profil resmi yükleme
            $profile_image_path = $user['profile_image'];
            
            if (isset($_FILES['profile_image']) && $_FILES['profile_image']['error'] === UPLOAD_ERR_OK) {
                $upload_dir = '../uploads/profiles/';
                if (!file_exists($upload_dir)) {
                    mkdir($upload_dir, 0755, true);
                }
                
                $file_extension = strtolower(pathinfo($_FILES['profile_image']['name'], PATHINFO_EXTENSION));
                $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif'];
                
                if (!in_array($file_extension, $allowed_extensions)) {
                    throw new Exception("Sadece JPG, JPEG, PNG ve GIF dosyaları yükleyebilirsiniz.");
                }
                
                if ($_FILES['profile_image']['size'] > 5 * 1024 * 1024) {
                    throw new Exception("Dosya boyutu 5MB'dan küçük olmalıdır.");
                }
                
                $new_filename = 'profile_' . $user_id . '_' . time() . '.' . $file_extension;
                $upload_path = $upload_dir . $new_filename;
                
                if (move_uploaded_file($_FILES['profile_image']['tmp_name'], $upload_path)) {
                    if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])) {
                        unlink('../' . $user['profile_image']);
                    }
                    $profile_image_path = 'uploads/profiles/' . $new_filename;
                } else {
                    throw new Exception("Profil resmi yüklenirken hata oluştu.");
                }
            }
            
            // Database'i güncelle
            $update_query = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, bio = ?, profile_image = ?, updated_at = NOW() WHERE id = ?");
            $update_query->bind_param("sssssi", $name, $email, $phone, $bio, $profile_image_path, $user_id);
            
            if ($update_query->execute()) {
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $success = "Profil bilgileri başarıyla güncellendi!";
                
                // Güncel verileri al
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                throw new Exception("Profil güncellenirken hata oluştu.");
            }
        }
        
        if (isset($_POST['change_password'])) {
            // Şifre değiştirme
            $current_password = $_POST['current_password'];
            $new_password = $_POST['new_password'];
            $confirm_password = $_POST['confirm_password'];
            
            if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
                throw new Exception("Tüm şifre alanları zorunludur.");
            }
            
            if (!password_verify($current_password, $user['password'])) {
                throw new Exception("Mevcut şifre yanlış.");
            }
            
            if ($new_password !== $confirm_password) {
                throw new Exception("Yeni şifreler eşleşmiyor.");
            }
            
            if (strlen($new_password) < 6) {
                throw new Exception("Şifre en az 6 karakter olmalıdır.");
            }
            
            $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
            $password_update = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
            $password_update->bind_param("si", $hashed_password, $user_id);
            
            if ($password_update->execute()) {
                $success = "Şifre başarıyla güncellendi!";
            } else {
                throw new Exception("Şifre güncellenirken hata oluştu.");
            }
        }
        
        if (isset($_POST['update_notifications'])) {
            // Bildirim ayarları (şimdilik basit versiyon)
            $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
            $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
            $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
            
            $notification_update = $conn->prepare("UPDATE users SET email_notifications = ?, sms_notifications = ?, marketing_emails = ?, updated_at = NOW() WHERE id = ?");
            $notification_update->bind_param("iiii", $email_notifications, $sms_notifications, $marketing_emails, $user_id);
            
            if ($notification_update->execute()) {
                $success = "Bildirim ayarları güncellendi!";
            } else {
                throw new Exception("Bildirim ayarları güncellenirken hata oluştu.");
            }
        }
        
    } catch (Exception $e) {
        $error = $e->getMessage();
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Dashboard</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Font Awesome -->
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <!-- Local CSS -->
    <link rel="stylesheet" href="../css/style.min.css">
    <link rel="stylesheet" href="css/dashboard.css">
    
    <style>
        /* Dashboard Admin yapısıyla uyumlu stiller */
        .main-content {
            margin-left: 280px;
            padding: 30px;
            min-height: 100vh;
            background: #f8f9fa;
        }
        
        @media (max-width: 1199.98px) {
            .main-content {
                margin-left: 0;
                padding: 20px;
            }
        }
        
        .profile-header {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            color: white;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .profile-header::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(255,255,255,0.1);
            backdrop-filter: blur(10px);
        }
        
        .profile-avatar {
            position: relative;
            z-index: 2;
        }
        
        .avatar-container {
            position: relative;
            display: inline-block;
        }
        
        .avatar-image {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid rgba(255,255,255,0.3);
            background: rgba(255,255,255,0.1);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 36px;
            color: white;
        }
        
        .avatar-upload {
            position: absolute;
            bottom: 0;
            right: 0;
            background: #15B97C;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            border: 3px solid white;
            transition: all 0.3s ease;
        }
        
        .avatar-upload:hover {
            background: #0d8c5a;
            transform: scale(1.1);
        }
        
        .profile-card {
            background: white;
            border-radius: 20px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.08);
            padding: 30px;
            margin-bottom: 30px;
            border: 1px solid #e9ecef;
        }
        
        .card-header {
            border: none;
            background: transparent;
            padding: 0 0 20px 0;
            border-bottom: 2px solid #f8f9fa;
            margin-bottom: 25px;
        }
        
        .card-title {
            font-size: 18px;
            font-weight: 600;
            color: #2c3e50;
            margin: 0;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #34495e;
            margin-bottom: 8px;
            font-size: 14px;
        }
        
        .form-control {
            border-radius: 10px;
            border: 2px solid #e9ecef;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #15B97C;
            box-shadow: 0 0 0 0.2rem rgba(21, 185, 124, 0.15);
        }
        
        .btn-primary {
            background: #15B97C;
            border: none;
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .btn-primary:hover {
            background: #0d8c5a;
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(21, 185, 124, 0.3);
        }
        
        .btn-outline-danger {
            border-radius: 10px;
            padding: 12px 25px;
            font-weight: 600;
            font-size: 14px;
        }
        
        .alert {
            border-radius: 15px;
            border: none;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .switch {
            position: relative;
            display: inline-block;
            width: 50px;
            height: 26px;
        }
        
        .switch input {
            opacity: 0;
            width: 0;
            height: 0;
        }
        
        .slider {
            position: absolute;
            cursor: pointer;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background-color: #ccc;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 26px;
        }
        
        .slider:before {
            position: absolute;
            content: "";
            height: 20px;
            width: 20px;
            left: 3px;
            bottom: 3px;
            background-color: white;
            -webkit-transition: .4s;
            transition: .4s;
            border-radius: 50%;
        }
        
        input:checked + .slider {
            background-color: #15B97C;
        }
        
        input:checked + .slider:before {
            -webkit-transform: translateX(24px);
            -ms-transform: translateX(24px);
            transform: translateX(24px);
        }
        
        .notification-item {
            padding: 15px 0;
            border-bottom: 1px solid #f1f1f1;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .notification-item:last-child {
            border-bottom: none;
        }
        
        .notification-text {
            flex: 1;
        }
        
        .notification-text h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
            color: #2c3e50;
        }
        
        .notification-text small {
            color: #7f8c8d;
        }
        
        .file-upload-area {
            border: 2px dashed #ddd;
            border-radius: 10px;
            padding: 30px;
            text-align: center;
            transition: all 0.3s ease;
            cursor: pointer;
        }
        
        .file-upload-area:hover {
            border-color: #15B97C;
            background: rgba(21, 185, 124, 0.05);
        }
        
        .file-upload-area input[type="file"] {
            display: none;
        }
        
        .danger-zone {
            background: #fff5f5;
            border: 2px solid #feb2b2;
            border-radius: 15px;
            padding: 25px;
            margin-top: 30px;
        }
        
        .danger-zone h5 {
            color: #e53e3e;
            margin-bottom: 15px;
        }
        
        .danger-zone p {
            color: #c53030;
            margin-bottom: 20px;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stat-card {
            background: white;
            border-radius: 15px;
            padding: 20px;
            text-align: center;
            box-shadow: 0 5px 15px rgba(0,0,0,0.05);
            border: 1px solid #f1f1f1;
        }
        
        .stat-number {
            font-size: 24px;
            font-weight: 700;
            color: #15B97C;
            margin-bottom: 5px;
        }
        
        .stat-label {
            color: #7f8c8d;
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 1px;
        }
    </style>
</head>

<body class="dash-board-body">
    <div class="dashboard-wrapper">
        <!-- Include Sidebar - Admin veya User rolüne göre -->
        <?php if ($is_admin): ?>
            <?php include 'includes/sidebar-admin.php'; ?>
        <?php else: ?>
            <?php include 'includes/sidebar-user.php'; ?>
        <?php endif; ?>
        
        <!-- Main Content -->
        <div class="main-content">
            <!-- Success/Error Messages -->
            <?php if ($success): ?>
                <div class="alert alert-success">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= htmlspecialchars($success) ?>
                </div>
            <?php endif; ?>
            
            <?php if ($error): ?>
                <div class="alert alert-danger">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= htmlspecialchars($error) ?>
                </div>
            <?php endif; ?>
            
            <!-- Profile Header -->
            <div class="profile-header">
                <div class="row align-items-center">
                    <div class="col-md-auto">
                        <div class="profile-avatar">
                            <div class="avatar-container">
                                <?php if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])): ?>
                                    <img src="../<?= htmlspecialchars($user['profile_image']) ?>" alt="Profil Resmi" class="avatar-image">
                                <?php else: ?>
                                    <div class="avatar-image">
                                        <i class="fas fa-user"></i>
                                    </div>
                                <?php endif; ?>
                                <label for="quick-avatar-upload" class="avatar-upload">
                                    <i class="fas fa-camera"></i>
                                </label>
                            </div>
                        </div>
                    </div>
                    <div class="col-md">
                        <h2 class="mb-1"><?= htmlspecialchars($user['name']) ?></h2>
                        <p class="mb-2 opacity-75"><?= htmlspecialchars($user['email']) ?></p>
                        <span class="badge bg-light text-dark">
                            <i class="fas fa-shield-alt me-1"></i>
                            <?= ucfirst($user['role']) ?>
                        </span>
                    </div>
                    <div class="col-md-auto">
                        <div class="stats-grid">
                            <div class="stat-card">
                                <div class="stat-number"><?= date('d.m.Y', strtotime($user['created_at'])) ?></div>
                                <div class="stat-label">Üye Olma</div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="row">
                <!-- Sol Kolon - Profil Bilgileri -->
                <div class="col-lg-8">
                    <!-- Şifre Değiştir -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-key me-2 text-primary"></i>
                                Şifre Değiştir
                            </h5>
                        </div>
                        <form method="POST">
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="current_password" class="form-label">Mevcut Şifre</label>
                                        <input type="password" class="form-control" id="current_password" name="current_password" required>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <div class="form-group">
                                        <label for="new_password" class="form-label">Yeni Şifre</label>
                                        <input type="password" class="form-control" id="new_password" name="new_password" required>
                                        <small class="text-muted">En az 6 karakter olmalıdır</small>
                                    </div>
                                </div>
                            </div>
                            <div class="form-group">
                                <label for="confirm_password" class="form-label">Yeni Şifre Tekrar</label>
                                <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                            </div>
                            <button type="submit" name="change_password" class="btn btn-primary">
                                <i class="fas fa-save me-2"></i>
                                Şifreyi Değiştir
                            </button>
                        </form>
                    </div>
                    
                    <!-- Bildirim Ayarları -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-bell me-2 text-info"></i>
                                Bildirim Ayarları
                            </h5>
                        </div>
                        <form method="POST">
                            <div class="notification-item">
                                <div class="notification-text">
                                    <h6>E-posta Bildirimleri</h6>
                                    <small>Yeni mesajlar ve güncellemeler hakkında e-posta alın</small>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="email_notifications" <?= (isset($user['email_notifications']) && $user['email_notifications']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-item">
                                <div class="notification-text">
                                    <h6>SMS Bildirimleri</h6>
                                    <small>Önemli güncellemeler için SMS bildirimi alın</small>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="sms_notifications" <?= (isset($user['sms_notifications']) && $user['sms_notifications']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <div class="notification-item">
                                <div class="notification-text">
                                    <h6>Pazarlama E-postaları</h6>
                                    <small>Özel teklifler ve kampanyalar hakkında bilgi alın</small>
                                </div>
                                <label class="switch">
                                    <input type="checkbox" name="marketing_emails" <?= (isset($user['marketing_emails']) && $user['marketing_emails']) ? 'checked' : '' ?>>
                                    <span class="slider"></span>
                                </label>
                            </div>
                            
                            <button type="submit" name="update_notifications" class="btn btn-primary mt-3">
                                <i class="fas fa-save me-2"></i>
                                Ayarları Kaydet
                            </button>
                        </form>
                    </div>
                    
                    <!-- Tehlikeli Alan -->
                    <div class="danger-zone">
                        <h5>
                            <i class="fas fa-exclamation-triangle me-2"></i>
                            Tehlikeli Alan
                        </h5>
                        <p>Hesabı silmek için şifrenizi girin:</p>
                        <form method="POST" onsubmit="return confirm('Hesabınızı silmek istediğinizden emin misiniz? Bu işlem geri alınamaz!')">
                            <div class="row align-items-end">
                                <div class="col-md-8">
                                    <input type="password" class="form-control" placeholder="Hesabı silmek için şifrenizi girin" required>
                                </div>
                                <div class="col-md-4">
                                    <button type="submit" name="delete_account" class="btn btn-outline-danger w-100">
                                        <i class="fas fa-trash me-2"></i>
                                        Hesabı Kaldır Sil
                                    </button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
                
                <!-- Sağ Kolon - Güvenlik Durumu ve Hızlı Eylemler -->
                <div class="col-lg-4">
                    <!-- Güvenlik Durumu -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-shield-check me-2 text-success"></i>
                                Güvenlik Durumu
                            </h5>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-text">
                                <h6>Son Giriş</h6>
                                <small><?= $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Hiç giriş yapılmamış' ?></small>
                            </div>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-text">
                                <h6>Hesap Oluşturma</h6>
                                <small><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></small>
                            </div>
                            <i class="fas fa-check-circle text-success"></i>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-text">
                                <h6>E-posta Doğrulama</h6>
                                <small><?= $user['is_verified'] ? 'Doğrulanmış' : 'Beklemede' ?></small>
                            </div>
                            <i class="fas fa-<?= $user['is_verified'] ? 'check-circle text-success' : 'clock text-warning' ?>"></i>
                        </div>
                        
                        <div class="notification-item">
                            <div class="notification-text">
                                <h6>İki Faktörlü Doğrulama</h6>
                                <small>Hesabınızı daha güvenli hale getirmek için etkinleştirin</small>
                            </div>
                            <button class="btn btn-sm btn-outline-primary">Aç</button>
                        </div>
                    </div>
                    
                    <!-- Hızlı Eylemler -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-bolt me-2 text-warning"></i>
                                Hızlı Eylemler
                            </h5>
                        </div>
                        
                        <div class="d-grid gap-2">
                            <a href="profile.php" class="btn btn-outline-primary">
                                <i class="fas fa-user me-2"></i>
                                Profil Hesabını
                            </a>
                            <a href="user-profile.php" class="btn btn-outline-success">
                                <i class="fas fa-list me-2"></i>
                                İlanlarımı Görüntüle
                            </a>
                            <a href="../index.php" class="btn btn-outline-info">
                                <i class="fas fa-home me-2"></i>
                                Anasayfaya Dön
                            </a>
                            <a href="../logout.php" class="btn btn-outline-secondary">
                                <i class="fas fa-sign-out-alt me-2"></i>
                                Dashboard'a Dön
                            </a>
                        </div>
                    </div>
                    
                    <!-- Profil Bilgilerini Güncelle -->
                    <div class="profile-card">
                        <div class="card-header">
                            <h5 class="card-title">
                                <i class="fas fa-user-edit me-2 text-primary"></i>
                                Profil Bilgileri
                            </h5>
                        </div>
                        <form method="POST" enctype="multipart/form-data">
                            <div class="form-group">
                                <label for="name" class="form-label">Ad Soyad</label>
                                <input type="text" class="form-control" id="name" name="name" 
                                       value="<?= htmlspecialchars($user['name']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="email" class="form-label">E-posta</label>
                                <input type="email" class="form-control" id="email" name="email" 
                                       value="<?= htmlspecialchars($user['email']) ?>" required>
                            </div>
                            
                            <div class="form-group">
                                <label for="phone" class="form-label">Telefon</label>
                                <input type="tel" class="form-control" id="phone" name="phone" 
                                       value="<?= htmlspecialchars($user['phone'] ?? '') ?>">
                            </div>
                            
                            <div class="form-group">
                                <label for="bio" class="form-label">Hakkında</label>
                                <textarea class="form-control" id="bio" name="bio" rows="3" 
                                          placeholder="Kendiniz hakkında kısa bir açıklama..."><?= htmlspecialchars($user['bio'] ?? '') ?></textarea>
                            </div>
                            
                            <div class="form-group">
                                <label for="profile_image" class="form-label">Profil Resmi</label>
                                <div class="file-upload-area" onclick="document.getElementById('profile_image').click()">
                                    <input type="file" id="profile_image" name="profile_image" accept="image/*">
                                    <i class="fas fa-cloud-upload-alt fa-2x text-muted mb-2"></i>
                                    <p class="text-muted mb-0">Resim seçmek için tıklayın</p>
                                    <small class="text-muted">(Max 5MB)</small>
                                </div>
                            </div>
                            
                            <button type="submit" name="update_profile" class="btn btn-primary w-100">
                                <i class="fas fa-save me-2"></i>
                                Profili Güncelle
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Quick Avatar Upload (Hidden) -->
    <input type="file" id="quick-avatar-upload" style="display: none;" accept="image/*">
    
    <!-- JavaScript -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script>
        // Quick avatar upload
        document.getElementById('quick-avatar-upload').addEventListener('change', function(e) {
            if (e.target.files.length > 0) {
                const formData = new FormData();
                formData.append('profile_image', e.target.files[0]);
                formData.append('update_profile', '1');
                formData.append('name', '<?= htmlspecialchars($user['name']) ?>');
                formData.append('email', '<?= htmlspecialchars($user['email']) ?>');
                formData.append('phone', '<?= htmlspecialchars($user['phone'] ?? '') ?>');
                formData.append('bio', '<?= htmlspecialchars($user['bio'] ?? '') ?>');
                
                fetch(window.location.href, {
                    method: 'POST',
                    body: formData
                }).then(() => {
                    window.location.reload();
                });
            }
        });
        
        // File upload preview
        document.getElementById('profile_image').addEventListener('change', function(e) {
            const uploadArea = this.parentElement;
            if (e.target.files.length > 0) {
                uploadArea.innerHTML = '<i class="fas fa-check text-success fa-2x mb-2"></i><p class="text-success mb-0">Seçildi: ' + e.target.files[0].name + '</p>';
                uploadArea.style.borderColor = '#15B97C';
                uploadArea.style.background = 'rgba(21, 185, 124, 0.1)';
            }
        });
        
        // Password confirmation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Şifreler eşleşmiyor');
            } else {
                this.setCustomValidity('');
            }
        });
        
        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.sidebar-admin, .sidebar-user');
            const overlay = document.querySelector('.sidebar-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }
        
        // Close sidebar on overlay click
        document.addEventListener('click', function(e) {
            if (e.target.classList.contains('sidebar-overlay')) {
                toggleSidebar();
            }
        });
    </script>
</body>
</html>
