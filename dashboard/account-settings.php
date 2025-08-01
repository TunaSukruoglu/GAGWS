<?php
session_start();
include '../db.php';

// Kullanıcı giriş yapmış mı kontrol et
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
$page_title = "Hesap Ayarları";
$current_page = 'account-settings';

// Şifre değiştirme işlemi
if (isset($_POST['change_password'])) {
    $current_password = $_POST['current_password'] ?? '';
    $new_password = $_POST['new_password'] ?? '';
    $confirm_password = $_POST['confirm_password'] ?? '';
    
    // Validation
    if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
        $error = "Tüm şifre alanları zorunludur!";
    } elseif (strlen($new_password) < 6) {
        $error = "Yeni şifre en az 6 karakter olmalıdır!";
    } elseif ($new_password !== $confirm_password) {
        $error = "Yeni şifreler eşleşmiyor!";
    } elseif (!password_verify($current_password, $user['password'])) {
        $error = "Mevcut şifre yanlış!";
    } else {
        // Şifreyi güncelle
        $new_hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
        $update_stmt = $conn->prepare("UPDATE users SET password = ?, updated_at = NOW() WHERE id = ?");
        $update_stmt->bind_param("si", $new_hashed_password, $user_id);
        
        if ($update_stmt->execute()) {
            $success = "Şifre başarıyla değiştirildi!";
            header("Location: account-settings.php?success=password_changed");
            exit;
        } else {
            $error = "Şifre değiştirilirken hata oluştu!";
        }
    }
}

// Bildirim ayarları güncelleme
if (isset($_POST['update_settings'])) {
    $email_notifications = isset($_POST['email_notifications']) ? 1 : 0;
    $sms_notifications = isset($_POST['sms_notifications']) ? 1 : 0;
    $marketing_emails = isset($_POST['marketing_emails']) ? 1 : 0;
    
    // Ayarları session'da sakla (veritabanında user_settings tablosu da olabilir)
    $_SESSION['user_settings'] = [
        'email_notifications' => $email_notifications,
        'sms_notifications' => $sms_notifications,
        'marketing_emails' => $marketing_emails
    ];
    
    $success = "Bildirim ayarları başarıyla güncellendi!";
    header("Location: account-settings.php?success=settings_updated");
    exit;
}

// Hesap silme işlemi
if (isset($_POST['delete_account'])) {
    $delete_password = $_POST['delete_password'] ?? '';
    
    if (empty($delete_password)) {
        $error = "Hesabı silmek için şifrenizi girin!";
    } elseif (!password_verify($delete_password, $user['password'])) {
        $error = "Şifre yanlış!";
    } else {
        // Önce kullanıcının verilerini sil
        $conn->begin_transaction();
        try {
            // Kullanıcıya ait emlakları sil
            $conn->prepare("DELETE FROM properties WHERE user_id = ?")->execute([$user_id]);
            // Kullanıcıya ait favorileri sil  
            $conn->prepare("DELETE FROM favorites WHERE user_id = ?")->execute([$user_id]);
            // Kullanıcıya ait mesajları sil
            $conn->prepare("DELETE FROM messages WHERE sender_id = ? OR receiver_id = ?")->execute([$user_id, $user_id]);
            // Kullanıcıyı sil
            $conn->prepare("DELETE FROM users WHERE id = ?")->execute([$user_id]);
            
            $conn->commit();
            session_destroy();
            header("Location: ../index.php?message=account_deleted");
            exit;
        } catch (Exception $e) {
            $conn->rollback();
            $error = "Hesap silinirken hata oluştu!";
        }
    }
}

// Success mesajları
if (isset($_GET['success'])) {
    switch ($_GET['success']) {
        case 'password_changed':
            $success = "Şifre başarıyla değiştirildi!";
            break;
        case 'settings_updated':
            $success = "Bildirim ayarları başarıyla güncellendi!";
            break;
    }
}

// Kullanıcı ayarlarını al
$user_settings = $_SESSION['user_settings'] ?? [
    'email_notifications' => 1,
    'sms_notifications' => 0,
    'marketing_emails' => 0
];
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
    <!-- Dashboard Z-Index Fix CSS -->
    <link rel="stylesheet" type="text/css" href="../css/dashboard-fix.css">
    
    <style>
        /* Account Settings Specific Styles */
        .security-card {
            border-left: 4px solid #0d6efd;
        }

        .danger-card {
            border-left: 4px solid var(--accent-color);
        }

        .warning-card {
            border-left: 4px solid #ffc107;
        }

        .password-input {
            position: relative;
        }

        .password-toggle {
            position: absolute;
            right: 15px;
            top: 40px;
            background: none;
            border: none;
            color: #6c757d;
            cursor: pointer;
            padding: 5px;
            transition: all 0.3s ease;
        }

        .password-toggle:hover {
            color: #0d6efd;
        }

        .setting-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 20px 0;
            border-bottom: 1px solid #f0f0f0;
        }

        .setting-item:last-child {
            border-bottom: none;
        }

        .setting-info h6 {
            margin: 0 0 8px 0;
            color: var(--primary-color);
            font-weight: 600;
            font-size: 16px;
        }

        .setting-info p {
            margin: 0;
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
        }

        /* Custom Switch */
        .custom-switch {
            position: relative;
            display: inline-block;
            width: 60px;
            height: 34px;
        }

        .custom-switch input {
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
            transition: .4s;
            border-radius: 34px;
        }

        .slider:before {
            position: absolute;
            content: "";
            height: 26px;
            width: 26px;
            left: 4px;
            bottom: 4px;
            background-color: white;
            transition: .4s;
            border-radius: 50%;
        }

        input:checked + .slider {
            background-color: #0d6efd;
        }

        input:checked + .slider:before {
            transform: translateX(26px);
        }

        .strength-indicator {
            margin-top: 8px;
            font-size: 12px;
            font-weight: 500;
        }

        .form-group {
            margin-bottom: 25px;
        }

        .form-group label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .form-group input,
        .form-group textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E6E6E6;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .form-group input:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: #0d6efd;
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .form-group input[readonly] {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .btn-danger {
            background: var(--accent-color);
            border-color: var(--accent-color);
        }

        .btn-danger:hover {
            background: #e55a2b;
            border-color: #e55a2b;
        }

        .security-info-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(300px, 1fr));
            gap: 20px;
            margin-bottom: 25px;
        }

        .security-info-item {
            background: #f8f9fa;
            padding: 20px;
            border-radius: 12px;
            border-left: 4px solid #0d6efd;
        }

        .security-info-item h6 {
            color: var(--primary-color);
            font-weight: 600;
            margin-bottom: 8px;
        }

        .security-info-item p {
            color: #6c757d;
            margin: 0;
            font-size: 14px;
        }

        .security-status {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 12px;
            border-radius: 20px;
            font-size: 12px;
            font-weight: 500;
        }

        .security-status.verified {
            background: rgba(13, 110, 253, 0.1);
            color: #0d6efd;
        }

        .security-status.pending {
            background: rgba(255, 193, 7, 0.1);
            color: #ffc107;
        }

        .security-status.disabled {
            background: rgba(108, 117, 125, 0.1);
            color: #6c757d;
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
                <!-- Include Header -->
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

                <div class="row">
                    <div class="col-lg-8">
                        <!-- Şifre Değiştirme -->
                        <div class="content-section security-card">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-key"></i>
                                    Şifre Değiştir
                                </h5>
                            </div>
                            <form method="POST">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="form-group password-input">
                                            <label for="current_password">Mevcut Şifre*</label>
                                            <input type="password" id="current_password" name="current_password" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('current_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group password-input">
                                            <label for="new_password">Yeni Şifre*</label>
                                            <input type="password" id="new_password" name="new_password" required minlength="6">
                                            <button type="button" class="password-toggle" onclick="togglePassword('new_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <div id="password-strength" class="strength-indicator"></div>
                                            <small class="text-muted">En az 6 karakter olmalıdır</small>
                                        </div>
                                    </div>
                                    <div class="col-md-6">
                                        <div class="form-group password-input">
                                            <label for="confirm_password">Yeni Şifre Tekrar*</label>
                                            <input type="password" id="confirm_password" name="confirm_password" required minlength="6">
                                            <button type="button" class="password-toggle" onclick="togglePassword('confirm_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-3 mt-4">
                                    <button type="submit" name="change_password" class="dash-btn-two">
                                        <i class="fas fa-save"></i>
                                        Şifreyi Değiştir
                                    </button>
                                    <button type="button" class="dash-btn-two" style="background: #6c757d;" onclick="resetPasswordForm()">
                                        <i class="fas fa-times"></i>
                                        İptal
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Bildirim Ayarları -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-bell"></i>
                                    Bildirim Ayarları
                                </h5>
                            </div>
                            <form method="POST">
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h6>E-posta Bildirimleri</h6>
                                        <p>Yeni mesajlar, ilanlar ve güncellemeler hakkında e-posta alın</p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" name="email_notifications" <?= $user_settings['email_notifications'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h6>SMS Bildirimleri</h6>
                                        <p>Önemli güncellemeler için SMS bildirimi alın</p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" name="sms_notifications" <?= $user_settings['sms_notifications'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="setting-item">
                                    <div class="setting-info">
                                        <h6>Pazarlama E-postaları</h6>
                                        <p>Özel teklifler ve kampanyalar hakkında bilgi alın</p>
                                    </div>
                                    <label class="custom-switch">
                                        <input type="checkbox" name="marketing_emails" <?= $user_settings['marketing_emails'] ? 'checked' : '' ?>>
                                        <span class="slider"></span>
                                    </label>
                                </div>
                                <div class="d-flex gap-3 mt-4">
                                    <button type="submit" name="update_settings" class="dash-btn-two">
                                        <i class="fas fa-save"></i>
                                        Ayarları Kaydet
                                    </button>
                                </div>
                            </form>
                        </div>

                        <!-- Hesap Silme -->
                        <div class="content-section danger-card">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-exclamation-triangle"></i>
                                    Tehlikeli Alan
                                </h5>
                            </div>
                            <div class="alert alert-danger">
                                <i class="fas fa-exclamation-triangle me-2"></i>
                                <strong>Dikkat:</strong> Bu işlem geri alınamaz! Hesabınızı sildiğinizde tüm verileriniz kalıcı olarak silinecektir.
                            </div>
                            <form method="POST" onsubmit="return confirmDelete();">
                                <div class="row">
                                    <div class="col-md-6">
                                        <div class="form-group password-input">
                                            <label for="delete_password">Hesabı silmek için şifrenizi girin*</label>
                                            <input type="password" id="delete_password" name="delete_password" required>
                                            <button type="button" class="password-toggle" onclick="togglePassword('delete_password')">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <div class="d-flex gap-3 mt-4">
                                    <button type="submit" name="delete_account" class="btn btn-danger">
                                        <i class="fas fa-trash"></i>
                                        Hesabı Kalıcı Olarak Sil
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Güvenlik Bilgileri -->
                        <div class="content-section warning-card">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-shield-alt"></i>
                                    Güvenlik Durumu
                                </h5>
                            </div>
                            
                            <div class="security-info-item mb-3">
                                <h6>Son Giriş</h6>
                                <p><?= isset($user['last_login']) && $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Hiç giriş yapılmamış' ?></p>
                            </div>

                            <div class="security-info-item mb-3">
                                <h6>Hesap Oluşturma</h6>
                                <p><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></p>
                            </div>

                            <div class="security-info-item mb-3">
                                <h6>E-posta Doğrulama</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span><?= htmlspecialchars($user['email']) ?></span>
                                    <span class="security-status <?= isset($user['email_verified_at']) && $user['email_verified_at'] ? 'verified' : 'pending' ?>">
                                        <i class="fas fa-<?= isset($user['email_verified_at']) && $user['email_verified_at'] ? 'check-circle' : 'clock' ?>"></i>
                                        <?= isset($user['email_verified_at']) && $user['email_verified_at'] ? 'Doğrulandı' : 'Beklemede' ?>
                                    </span>
                                </div>
                            </div>

                            <div class="security-info-item mb-3">
                                <h6>İki Faktörlü Doğrulama</h6>
                                <div class="d-flex align-items-center justify-content-between">
                                    <span>2FA Durumu</span>
                                    <span class="security-status disabled">
                                        <i class="fas fa-times-circle"></i>
                                        Kapalı
                                    </span>
                                </div>
                                <small class="text-muted">Hesabınızı daha güvenli hale getirmek için etkinleştirin</small>
                            </div>

                            <div class="alert alert-warning">
                                <i class="fas fa-info-circle me-2"></i>
                                <strong>Güvenlik İpucu:</strong> Hesabınızın güvenliği için düzenli olarak şifrenizi değiştirin ve güçlü şifreler kullanın.
                            </div>
                        </div>

                        <!-- Hızlı Eylemler -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-bolt"></i>
                                    Hızlı Eylemler
                                </h5>
                            </div>
                            
                            <div class="d-grid gap-2">
                                <a href="profile.php" class="dash-btn-two">
                                    <i class="fas fa-user"></i>
                                    Profili Düzenle
                                </a>
                                <a href="properties-list.php" class="dash-btn-two" style="background: #6f42c1;">
                                    <i class="fas fa-list"></i>
                                    İlanlarımı Görüntüle
                                </a>
                                <a href="message.php" class="dash-btn-two" style="background: #fd7e14;">
                                    <i class="fas fa-envelope"></i>
                                    Mesajlarım
                                </a>
                                <a href="dashboard<?= $user['role'] == 'admin' ? '-admin' : '' ?>.php" class="dash-btn-two" style="background: #6c757d;">
                                    <i class="fas fa-arrow-left"></i>
                                    Dashboard'a Dön
                                </a>
                            </div>
                        </div>

                        <!-- Hesap İstatistikleri -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-chart-bar"></i>
                                    Hesap İstatistikleri
                                </h5>
                            </div>
                            
                            <?php
                            // Kullanıcı istatistikleri
                            $stats_query = "SELECT 
                                COUNT(*) as total_properties,
                                SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count
                                FROM properties WHERE user_id = ?";
                            $stmt = $conn->prepare($stats_query);
                            $stmt->bind_param("i", $user_id);
                            $stmt->execute();
                            $user_stats = $stmt->get_result()->fetch_assoc();
                            ?>
                            
                            <div class="row text-center">
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h4 class="text-primary mb-1"><?= $user_stats['total_properties'] ?></h4>
                                        <small class="text-muted">Toplam İlan</small>
                                    </div>
                                </div>
                                <div class="col-6">
                                    <div class="p-3 bg-light rounded">
                                        <h4 class="text-success mb-1"><?= $user_stats['approved_count'] ?></h4>
                                        <small class="text-muted">Onaylı İlan</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
    
    <script>
        // Mobile nav toggle
        document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').classList.toggle('show');
        });

        // Password toggle function
        function togglePassword(fieldId) {
            const field = document.getElementById(fieldId);
            const icon = field.nextElementSibling.querySelector('i');
            
            if (field.type === 'password') {
                field.type = 'text';
                icon.className = 'fas fa-eye-slash';
            } else {
                field.type = 'password';
                icon.className = 'fas fa-eye';
            }
        }

        // Password confirmation validation
        document.getElementById('confirm_password').addEventListener('input', function() {
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = this.value;
            
            if (newPassword !== confirmPassword) {
                this.setCustomValidity('Şifreler eşleşmiyor');
                this.style.borderColor = '#dc3545';
            } else {
                this.setCustomValidity('');
                this.style.borderColor = '#28a745';
            }
        });

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthDiv = document.getElementById('password-strength');
            
            let strength = 0;
            let text = '';
            let color = '';
            
            if (password.length >= 6) strength++;
            if (password.match(/[a-z]/)) strength++;
            if (password.match(/[A-Z]/)) strength++;
            if (password.match(/[0-9]/)) strength++;
            if (password.match(/[^a-zA-Z0-9]/)) strength++;
            
            switch (strength) {
                case 0:
                case 1:
                    text = 'Çok Zayıf';
                    color = '#dc3545';
                    break;
                case 2:
                    text = 'Zayıf';
                    color = '#ffc107';
                    break;
                case 3:
                    text = 'Orta';
                    color = '#fd7e14';
                    break;
                case 4:
                    text = 'Güçlü';
                    color = '#20c997';
                    break;
                case 5:
                    text = 'Çok Güçlü';
                    color = '#28a745';
                    break;
            }
            
            if (password.length > 0) {
                strengthDiv.innerHTML = `<span style="color: ${color};">Şifre Gücü: ${text}</span>`;
            } else {
                strengthDiv.innerHTML = '';
            }
        });

        // Reset password form
        function resetPasswordForm() {
            document.getElementById('current_password').value = '';
            document.getElementById('new_password').value = '';
            document.getElementById('confirm_password').value = '';
            document.getElementById('password-strength').innerHTML = '';
        }

        // Confirm delete account
        function confirmDelete() {
            return confirm('UYARI: Bu işlem geri alınamaz!\n\nHesabınızı silmek istediğinizden emin misiniz?\n\n- Tüm kişisel bilgileriniz silinecek\n- Tüm ilanlarınız kaldırılacak\n- Mesajlarınız silinecek\n- Bu işlem geri alınamaz\n\nEmin misiniz?');
        }

        // Alert auto dismiss
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (bootstrap.Alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && !submitBtn.name.includes('delete_account')) {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> İşleniyor...';
                    setTimeout(() => {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = submitBtn.name === 'change_password' ? 
                            '<i class="fas fa-save"></i> Şifreyi Değiştir' : 
                            '<i class="fas fa-save"></i> Ayarları Kaydet';
                    }, 3000);
                }
            });
        });
    </script>
</body>
</html>