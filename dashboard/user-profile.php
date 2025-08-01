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
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
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
$current_page = 'profile';
$page_title = $user_data['name'] . ' - Profil Ayarları';
$user_name = $user_data['name']; // Sidebar için

// Form işleme
$success_message = '';
$error_message = '';

if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $bio = trim($_POST['bio']);
        
        // Validation
        if (empty($name)) {
            $error_message = "Ad soyad boş olamaz.";
        } elseif (empty($email)) {
            $error_message = "E-posta adresi boş olamaz.";
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $error_message = "Geçerli bir e-posta adresi giriniz.";
        } else {
            // E-posta benzersizlik kontrolü (kendi e-postası hariç)
            $email_check = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
            $email_check->bind_param("si", $email, $user_id);
            $email_check->execute();
            $email_exists = $email_check->get_result()->num_rows > 0;
            
            if ($email_exists) {
                $error_message = "Bu e-posta adresi zaten kullanılıyor.";
            } else {
                // Profili güncelle
                $update_query = "UPDATE users SET name = ?, email = ?, phone = ?, address = ?, bio = ? WHERE id = ?";
                $update_stmt = $conn->prepare($update_query);
                $update_stmt->bind_param("sssssi", $name, $email, $phone, $address, $bio, $user_id);
                
                if ($update_stmt->execute()) {
                    $success_message = "Profil bilgileriniz başarıyla güncellendi.";
                    // Güncel verileri tekrar al
                    $user_query->execute();
                    $user_data = $user_query->get_result()->fetch_assoc();
                } else {
                    $error_message = "Profil güncellenirken bir hata oluştu.";
                }
            }
        }
    }
    
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        if (empty($current_password) || empty($new_password) || empty($confirm_password)) {
            $error_message = "Tüm şifre alanları doldurulmalıdır.";
        } elseif ($new_password !== $confirm_password) {
            $error_message = "Yeni şifreler eşleşmiyor.";
        } elseif (strlen($new_password) < 6) {
            $error_message = "Yeni şifre en az 6 karakter olmalıdır.";
        } else {
            // Mevcut şifreyi kontrol et
            if (password_verify($current_password, $user_data['password'])) {
                $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                $password_update = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                $password_update->bind_param("si", $hashed_password, $user_id);
                
                if ($password_update->execute()) {
                    $success_message = "Şifreniz başarıyla değiştirildi.";
                } else {
                    $error_message = "Şifre değiştirilirken bir hata oluştu.";
                }
            } else {
                $error_message = "Mevcut şifreniz yanlış.";
            }
        }
    }
}

// Kullanıcı istatistikleri
try {
    // Favori sayısı
    $fav_query = $conn->prepare("SELECT COUNT(*) as count FROM favorites WHERE user_id = ?");
    $fav_query->bind_param("i", $user_id);
    $fav_query->execute();
    $fav_count = $fav_query->get_result()->fetch_assoc()['count'];

    // Kaydedilmiş arama sayısı
    $search_query = $conn->prepare("SELECT COUNT(*) as count FROM saved_searches WHERE user_id = ?");
    $search_query->bind_param("i", $user_id);
    $search_query->execute();
    $saved_searches_count = $search_query->get_result()->fetch_assoc()['count'];

    // Değerlendirme sayısı
    $review_query = $conn->prepare("SELECT COUNT(*) as count FROM reviews WHERE user_id = ?");
    $review_query->bind_param("i", $user_id);
    $review_query->execute();
    $reviews_count = $review_query->get_result()->fetch_assoc()['count'];

} catch (Exception $e) {
    $fav_count = 0;
    $saved_searches_count = 0;
    $reviews_count = 0;
}
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
    <link rel="stylesheet" href="includes/dashboard-common.css">
    
    <style>
        /* Dashboard User Specific Styles */
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
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #0d6efd;
        }
        
        .mobile-logout {
            color: #dc3545;
            text-decoration: none;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.15);
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #0d6efd;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        .profile-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            margin-bottom: 30px;
        }
        
        .profile-section-header {
            background: #f8f9fa;
            padding: 20px 30px;
            border-bottom: 1px solid #e9ecef;
        }
        
        .profile-section-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin: 0;
        }
        
        .profile-section-body {
            padding: 30px;
        }
        
        .profile-avatar {
            width: 120px;
            height: 120px;
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 48px;
            color: white;
            margin: 0 auto 20px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.25);
        }
        
        .profile-info {
            text-align: center;
            margin-bottom: 30px;
        }
        
        .profile-name {
            font-size: 24px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }
        
        .profile-email {
            color: #6c757d;
            margin-bottom: 15px;
        }
        
        .profile-member-since {
            font-size: 14px;
            color: #6c757d;
        }
        
        .form-group {
            margin-bottom: 20px;
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .form-control {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 12px 15px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .btn-custom {
            padding: 12px 30px;
            border-radius: 10px;
            font-weight: 600;
            border: none;
            transition: all 0.3s ease;
        }
        
        .btn-primary-custom {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
        }
        
        .btn-primary-custom:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 20px rgba(13, 110, 253, 0.4);
        }
        
        .alert {
            border: none;
            border-radius: 12px;
            padding: 15px 20px;
            margin-bottom: 25px;
        }
        
        .alert-success {
            background: #d4edda;
            color: #155724;
            border-left: 4px solid #28a745;
        }
        
        .alert-danger {
            background: #f8d7da;
            color: #721c24;
            border-left: 4px solid #dc3545;
        }
        
        .stat-item {
            padding: 10px 5px;
        }
        
        .stat-number {
            font-size: 18px;
            font-weight: 700;
            color: #0d6efd;
            margin-bottom: 2px;
        }
        
        .stat-label {
            font-size: 12px;
            color: #6c757d;
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
            
            .welcome-banner {
                padding: 25px 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .profile-section-body {
                padding: 20px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
            }
            
            .profile-avatar {
                width: 100px;
                height: 100px;
                font-size: 40px;
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
                <h5 class="mobile-title">Profil Ayarları</h5>
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
                                <i class="fas fa-user-cog me-2"></i>
                                Profil Ayarları
                            </h2>
                            <p class="welcome-subtitle">
                                Kişisel bilgilerinizi ve hesap ayarlarınızı buradan yönetebilirsiniz. 
                                Güncel bilgiler daha iyi hizmet almanzı sağlar.
                            </p>
                        </div>
                    </div>

                    <!-- İstatistikler -->
                    <div class="stats-grid">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-heart"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $fav_count; ?></div>
                                <div class="stats-label">Favori İlanlarım</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-search"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $saved_searches_count; ?></div>
                                <div class="stats-label">Kayıtlı Aramalarım</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $reviews_count; ?></div>
                                <div class="stats-label">Değerlendirmelerim</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-calendar-alt"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo date('Y', strtotime($user_data['created_at'])); ?></div>
                                <div class="stats-label">Üyelik Yılı</div>
                            </div>
                        </div>
                    </div>

                    <!-- Mesajlar -->
                    <?php if ($success_message): ?>
                        <div class="alert alert-success">
                            <i class="fas fa-check-circle me-2"></i>
                            <?php echo htmlspecialchars($success_message); ?>
                        </div>
                    <?php endif; ?>

                    <?php if ($error_message): ?>
                        <div class="alert alert-danger">
                            <i class="fas fa-exclamation-circle me-2"></i>
                            <?php echo htmlspecialchars($error_message); ?>
                        </div>
                    <?php endif; ?>

                    <div class="row">
                        <!-- Profil Bilgileri -->
                        <div class="col-lg-8">
                            <div class="profile-section">
                                <div class="profile-section-header">
                                    <h4 class="profile-section-title">
                                        <i class="fas fa-user me-2"></i>
                                        Kişisel Bilgiler
                                    </h4>
                                </div>
                                <div class="profile-section-body">
                                    <form method="POST">
                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="name" class="form-label">Ad Soyad *</label>
                                                    <input type="text" class="form-control" id="name" name="name" 
                                                           value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="email" class="form-label">E-posta Adresi *</label>
                                                    <input type="email" class="form-control" id="email" name="email" 
                                                           value="<?php echo htmlspecialchars($user_data['email']); ?>" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="phone" class="form-label">Telefon</label>
                                                    <input type="tel" class="form-control" id="phone" name="phone" 
                                                           value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>">
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="address" class="form-label">Adres</label>
                                                    <input type="text" class="form-control" id="address" name="address" 
                                                           value="<?php echo htmlspecialchars($user_data['address'] ?? ''); ?>">
                                                </div>
                                            </div>
                                        </div>

                                        <div class="form-group">
                                            <label for="bio" class="form-label">Hakkımda</label>
                                            <textarea class="form-control" id="bio" name="bio" rows="4" 
                                                      placeholder="Kendiniz hakkında birkaç cümle yazın..."><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                                        </div>

                                        <div class="d-flex gap-3">
                                            <button type="submit" name="update_profile" class="btn btn-primary-custom btn-custom">
                                                <i class="fas fa-save me-2"></i>
                                                Bilgileri Güncelle
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>

                            <!-- Şifre Değiştirme -->
                            <div class="profile-section">
                                <div class="profile-section-header">
                                    <h4 class="profile-section-title">
                                        <i class="fas fa-lock me-2"></i>
                                        Şifre Değiştir
                                    </h4>
                                </div>
                                <div class="profile-section-body">
                                    <form method="POST">
                                        <div class="form-group">
                                            <label for="current_password" class="form-label">Mevcut Şifre</label>
                                            <input type="password" class="form-control" id="current_password" name="current_password" required>
                                        </div>

                                        <div class="row">
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="new_password" class="form-label">Yeni Şifre</label>
                                                    <input type="password" class="form-control" id="new_password" name="new_password" required>
                                                </div>
                                            </div>
                                            <div class="col-md-6">
                                                <div class="form-group">
                                                    <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password" required>
                                                </div>
                                            </div>
                                        </div>

                                        <div class="d-flex gap-3">
                                            <button type="submit" name="change_password" class="btn btn-primary-custom btn-custom">
                                                <i class="fas fa-key me-2"></i>
                                                Şifreyi Değiştir
                                            </button>
                                        </div>
                                    </form>
                                </div>
                            </div>
                        </div>

                        <!-- Profil Özeti -->
                        <div class="col-lg-4">
                            <div class="profile-section">
                                <div class="profile-section-header">
                                    <h4 class="profile-section-title">
                                        <i class="fas fa-id-card me-2"></i>
                                        Profil Özeti
                                    </h4>
                                </div>
                                <div class="profile-section-body">
                                    <div class="profile-info">
                                        <div class="profile-avatar">
                                            <?php echo strtoupper(substr($user_data['name'], 0, 1)); ?>
                                        </div>
                                        <h4 class="profile-name"><?php echo htmlspecialchars($user_data['name']); ?></h4>
                                        <p class="profile-email"><?php echo htmlspecialchars($user_data['email']); ?></p>
                                        <p class="profile-member-since">
                                            <i class="fas fa-calendar-alt me-1"></i>
                                            Üye olma tarihi: <?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?>
                                        </p>
                                    </div>

                                    <div class="profile-stats">
                                        <div class="row text-center">
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number"><?php echo $fav_count; ?></div>
                                                    <div class="stat-label">Favori</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number"><?php echo $saved_searches_count; ?></div>
                                                    <div class="stat-label">Arama</div>
                                                </div>
                                            </div>
                                            <div class="col-4">
                                                <div class="stat-item">
                                                    <div class="stat-number"><?php echo $reviews_count; ?></div>
                                                    <div class="stat-label">Yorum</div>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <div class="mt-4">
                                        <div class="d-grid gap-2">
                                            <a href="dashboard-user.php" class="btn btn-outline-primary">
                                                <i class="fas fa-tachometer-alt me-2"></i>
                                                Dashboard'a Dön
                                            </a>
                                            <a href="favorites.php" class="btn btn-outline-primary">
                                                <i class="fas fa-heart me-2"></i>
                                                Favorilerim
                                            </a>
                                            <a href="../logout.php" class="btn btn-outline-danger">
                                                <i class="fas fa-sign-out-alt me-2"></i>
                                                Çıkış Yap
                                            </a>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dashboard-script.js"></script>
    
    <script>
        // Form validation
        document.addEventListener('DOMContentLoaded', function() {
            // Password confirmation validation
            const newPassword = document.getElementById('new_password');
            const confirmPassword = document.getElementById('confirm_password');
            
            function validatePassword() {
                if (newPassword.value !== confirmPassword.value) {
                    confirmPassword.setCustomValidity('Şifreler eşleşmiyor');
                } else {
                    confirmPassword.setCustomValidity('');
                }
            }
            
            newPassword.addEventListener('input', validatePassword);
            confirmPassword.addEventListener('input', validatePassword);
        });
    </script>
</body>
</html>
