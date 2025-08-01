<?php
session_start();
include '../db.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini al
$user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

// Form gönderildi mi kontrol et
if ($_POST) {
    $success_message = '';
    $error_message = '';
    
    // Profil güncelleme
    if (isset($_POST['update_profile'])) {
        $name = trim($_POST['name']);
        $phone = trim($_POST['phone']);
        $address = trim($_POST['address']);
        $bio = trim($_POST['bio']);
        $website = trim($_POST['website']);
        
        $update_query = $conn->prepare("UPDATE users SET name = ?, phone = ?, address = ?, bio = ?, website = ? WHERE id = ?");
        $update_query->bind_param("sssssi", $name, $phone, $address, $bio, $website, $user_id);
        
        if ($update_query->execute()) {
            $success_message = "Profil bilgileriniz başarıyla güncellendi!";
            // Güncel verileri tekrar al
            $user_query->execute();
            $user_data = $user_query->get_result()->fetch_assoc();
        } else {
            $error_message = "Profil güncellenirken bir hata oluştu!";
        }
    }
    
    // Şifre değiştirme
    if (isset($_POST['change_password'])) {
        $current_password = $_POST['current_password'];
        $new_password = $_POST['new_password'];
        $confirm_password = $_POST['confirm_password'];
        
        // Mevcut şifre kontrolü
        if (password_verify($current_password, $user_data['password'])) {
            if ($new_password === $confirm_password) {
                if (strlen($new_password) >= 6) {
                    $hashed_password = password_hash($new_password, PASSWORD_DEFAULT);
                    
                    $password_query = $conn->prepare("UPDATE users SET password = ? WHERE id = ?");
                    $password_query->bind_param("si", $hashed_password, $user_id);
                    
                    if ($password_query->execute()) {
                        $success_message = "Şifreniz başarıyla değiştirildi!";
                    } else {
                        $error_message = "Şifre değiştirilirken bir hata oluştu!";
                    }
                } else {
                    $error_message = "Yeni şifre en az 6 karakter olmalıdır!";
                }
            } else {
                $error_message = "Yeni şifreler eşleşmiyor!";
            }
        } else {
            $error_message = "Mevcut şifre yanlış!";
        }
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Profil Ayarları - Gökhan Aydınlı Gayrimenkul</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <link rel="stylesheet" href="assets/dashboard-style.css">
</head>

<body>
    <div class="dashboard-container">
        <!-- Sol Sidebar (Sabit) -->
        <?php include 'includes/sidebar-user.php'; ?>

        <!-- Ana İçerik (Değişken) -->
        <div class="main-content">
            <div class="content-header">
                <div class="d-flex justify-content-between align-items-center">
                    <div>
                        <h2><i class="fas fa-user-cog text-primary"></i> Profil Ayarları</h2>
                        <p class="text-muted">Kişisel bilgilerinizi ve hesap ayarlarınızı yönetin</p>
                    </div>
                </div>
            </div>

            <!-- Başarı/Hata Mesajları -->
            <?php if (isset($success_message)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle"></i> <?php echo $success_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($error_message)): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle"></i> <?php echo $error_message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <!-- Sol Panel - Profil Kartı -->
                <div class="col-lg-4 mb-4">
                    <div class="profile-card">
                        <div class="profile-header">
                            <div class="profile-avatar">
                                <i class="fas fa-user"></i>
                            </div>
                            <h4><?php echo htmlspecialchars($user_data['name']); ?></h4>
                            <p class="text-muted"><?php echo htmlspecialchars($user_data['email']); ?></p>
                            <div class="profile-badges">
                                <?php if ($user_data['is_verified']): ?>
                                    <span class="badge bg-success">
                                        <i class="fas fa-check-circle"></i> Doğrulanmış
                                    </span>
                                <?php endif; ?>
                                <span class="badge bg-primary">
                                    <i class="fas fa-star"></i> Üye
                                </span>
                            </div>
                        </div>
                        
                        <div class="profile-stats">
                            <div class="row text-center">
                                <div class="col-4">
                                    <div class="stat-item">
                                        <strong>0</strong>
                                        <small>Favori</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <strong>0</strong>
                                        <small>Arama</small>
                                    </div>
                                </div>
                                <div class="col-4">
                                    <div class="stat-item">
                                        <strong>0</strong>
                                        <small>Yorum</small>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <div class="profile-info">
                            <div class="info-item">
                                <i class="fas fa-calendar"></i>
                                <span>Üyelik: <?php echo date('d.m.Y', strtotime($user_data['created_at'])); ?></span>
                            </div>
                            <div class="info-item">
                                <i class="fas fa-clock"></i>
                                <span>Son Giriş: <?php echo $user_data['last_login'] ? date('d.m.Y H:i', strtotime($user_data['last_login'])) : 'İlk giriş'; ?></span>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Sağ Panel - Ayarlar -->
                <div class="col-lg-8">
                    <!-- Tab Menüsü -->
                    <ul class="nav nav-tabs settings-tabs" id="settingsTabs" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" id="profile-tab" data-bs-toggle="tab" data-bs-target="#profile-content" type="button" role="tab">
                                <i class="fas fa-user"></i> Profil Bilgileri
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="security-tab" data-bs-toggle="tab" data-bs-target="#security-content" type="button" role="tab">
                                <i class="fas fa-shield-alt"></i> Güvenlik
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="notifications-tab" data-bs-toggle="tab" data-bs-target="#notifications-content" type="button" role="tab">
                                <i class="fas fa-bell"></i> Bildirimler
                            </button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" id="privacy-tab" data-bs-toggle="tab" data-bs-target="#privacy-content" type="button" role="tab">
                                <i class="fas fa-lock"></i> Gizlilik
                            </button>
                        </li>
                    </ul>

                    <!-- Tab İçerikleri -->
                    <div class="tab-content settings-content" id="settingsTabContent">
                        <!-- Profil Bilgileri -->
                        <div class="tab-pane fade show active" id="profile-content" role="tabpanel">
                            <div class="settings-section">
                                <h5><i class="fas fa-user"></i> Kişisel Bilgiler</h5>
                                <form method="POST">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Ad Soyad</label>
                                            <input type="text" class="form-control" name="name" value="<?php echo htmlspecialchars($user_data['name']); ?>" required>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">E-posta</label>
                                            <input type="email" class="form-control" value="<?php echo htmlspecialchars($user_data['email']); ?>" disabled>
                                            <small class="text-muted">E-posta adresi değiştirilemez</small>
                                        </div>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Telefon</label>
                                            <input type="tel" class="form-control" name="phone" value="<?php echo htmlspecialchars($user_data['phone'] ?? ''); ?>" placeholder="+90 5XX XXX XX XX">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Website</label>
                                            <input type="url" class="form-control" name="website" value="<?php echo htmlspecialchars($user_data['website'] ?? ''); ?>" placeholder="https://example.com">
                                        </div>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Adres</label>
                                        <textarea class="form-control" name="address" rows="3" placeholder="Tam adresiniz"><?php echo htmlspecialchars($user_data['address'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <div class="mb-3">
                                        <label class="form-label">Hakkımda</label>
                                        <textarea class="form-control" name="bio" rows="4" placeholder="Kendiniz hakkında kısa bilgi"><?php echo htmlspecialchars($user_data['bio'] ?? ''); ?></textarea>
                                    </div>
                                    
                                    <button type="submit" name="update_profile" class="btn btn-primary">
                                        <i class="fas fa-save"></i> Profili Güncelle
                                    </button>
                                </form>
                            </div>
                        </div>

                        <!-- Güvenlik -->
                        <div class="tab-pane fade" id="security-content" role="tabpanel">
                            <div class="settings-section">
                                <h5><i class="fas fa-shield-alt"></i> Şifre Değiştir</h5>
                                <form method="POST">
                                    <div class="mb-3">
                                        <label class="form-label">Mevcut Şifre</label>
                                        <input type="password" class="form-control" name="current_password" required>
                                    </div>
                                    
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Yeni Şifre</label>
                                            <input type="password" class="form-control" name="new_password" minlength="6" required>
                                            <small class="text-muted">En az 6 karakter</small>
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Yeni Şifre (Tekrar)</label>
                                            <input type="password" class="form-control" name="confirm_password" minlength="6" required>
                                        </div>
                                    </div>
                                    
                                    <button type="submit" name="change_password" class="btn btn-warning">
                                        <i class="fas fa-key"></i> Şifre Değiştir
                                    </button>
                                </form>
                            </div>
                            
                            <div class="settings-section">
                                <h5><i class="fas fa-history"></i> Güvenlik Geçmişi</h5>
                                <div class="security-log">
                                    <div class="log-item">
                                        <div class="log-icon">
                                            <i class="fas fa-sign-in-alt text-success"></i>
                                        </div>
                                        <div class="log-content">
                                            <strong>Başarılı Giriş</strong>
                                            <small class="text-muted d-block">Bugün 14:25 - IP: 192.168.1.1</small>
                                        </div>
                                    </div>
                                    <div class="log-item">
                                        <div class="log-icon">
                                            <i class="fas fa-user-edit text-info"></i>
                                        </div>
                                        <div class="log-content">
                                            <strong>Profil Güncellendi</strong>
                                            <small class="text-muted d-block">2 gün önce</small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Bildirimler -->
                        <div class="tab-pane fade" id="notifications-content" role="tabpanel">
                            <div class="settings-section">
                                <h5><i class="fas fa-bell"></i> E-posta Bildirimleri</h5>
                                <div class="notification-settings">
                                    <div class="notification-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailNewProperty" checked>
                                            <label class="form-check-label" for="emailNewProperty">
                                                <strong>Yeni İlan Bildirimleri</strong>
                                                <small class="text-muted d-block">Kayıtlı aramalarınıza uygun yeni ilanlar için e-posta alın</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailPriceUpdate">
                                            <label class="form-check-label" for="emailPriceUpdate">
                                                <strong>Fiyat Değişiklikleri</strong>
                                                <small class="text-muted d-block">Favorilerinizdeki ilanların fiyat değişiklikleri</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="notification-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="emailMarketing" checked>
                                            <label class="form-check-label" for="emailMarketing">
                                                <strong>Pazarlama E-postaları</strong>
                                                <small class="text-muted d-block">Özel kampanyalar ve güncellemeler</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="mt-4"><i class="fas fa-mobile-alt"></i> SMS Bildirimleri</h5>
                                <div class="notification-settings">
                                    <div class="notification-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="smsImportant">
                                            <label class="form-check-label" for="smsImportant">
                                                <strong>Önemli Bildirimler</strong>
                                                <small class="text-muted d-block">Hesap güvenliği ve kritik güncellemeler</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary mt-3">
                                    <i class="fas fa-save"></i> Bildirim Ayarlarını Kaydet
                                </button>
                            </div>
                        </div>

                        <!-- Gizlilik -->
                        <div class="tab-pane fade" id="privacy-content" role="tabpanel">
                            <div class="settings-section">
                                <h5><i class="fas fa-lock"></i> Gizlilik Ayarları</h5>
                                <div class="privacy-settings">
                                    <div class="privacy-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="profilePublic" checked>
                                            <label class="form-check-label" for="profilePublic">
                                                <strong>Profil Görünürlüğü</strong>
                                                <small class="text-muted d-block">Profiliniz diğer kullanıcılar tarafından görülebilsin</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="privacy-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="showActivity">
                                            <label class="form-check-label" for="showActivity">
                                                <strong>Aktivite Durumu</strong>
                                                <small class="text-muted d-block">Son görülme zamanınız görüntülenebilsin</small>
                                            </label>
                                        </div>
                                    </div>
                                    
                                    <div class="privacy-item">
                                        <div class="form-check form-switch">
                                            <input class="form-check-input" type="checkbox" id="allowContact" checked>
                                            <label class="form-check-label" for="allowContact">
                                                <strong>İletişim İzni</strong>
                                                <small class="text-muted d-block">Diğer kullanıcılar size mesaj gönderebilsin</small>
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                
                                <h5 class="mt-4"><i class="fas fa-trash-alt"></i> Hesap İşlemleri</h5>
                                <div class="account-actions">
                                    <div class="alert alert-warning">
                                        <i class="fas fa-exclamation-triangle"></i>
                                        <strong>Dikkat!</strong> Bu işlemler geri alınamaz.
                                    </div>
                                    
                                    <div class="d-flex gap-3 flex-wrap">
                                        <button class="btn btn-outline-secondary" onclick="exportData()">
                                            <i class="fas fa-download"></i> Verilerimi İndir
                                        </button>
                                        
                                        <button class="btn btn-outline-warning" onclick="deactivateAccount()">
                                            <i class="fas fa-pause"></i> Hesabı Dondur
                                        </button>
                                        
                                        <button class="btn btn-outline-danger" onclick="deleteAccount()">
                                            <i class="fas fa-trash"></i> Hesabı Sil
                                        </button>
                                    </div>
                                </div>
                                
                                <button class="btn btn-primary mt-3">
                                    <i class="fas fa-save"></i> Gizlilik Ayarlarını Kaydet
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
    <script src="assets/dashboard-script.js"></script>
    
    <script>
    // Şifre eşleşme kontrolü
    document.querySelector('input[name="confirm_password"]').addEventListener('input', function() {
        const newPassword = document.querySelector('input[name="new_password"]').value;
        const confirmPassword = this.value;
        
        if (newPassword !== confirmPassword) {
            this.setCustomValidity('Şifreler eşleşmiyor');
        } else {
            this.setCustomValidity('');
        }
    });

    // Veri export fonksiyonu
    function exportData() {
        if (confirm('Tüm verilerinizi JSON formatında indirmek istediğinizden emin misiniz?')) {
            window.location.href = '../api/export-data.php';
        }
    }

    // Hesap dondurma
    function deactivateAccount() {
        if (confirm('Hesabınızı dondurmak istediğinizden emin misiniz? Bu işlem geri alınabilir.')) {
            fetch('../api/deactivate-account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Hesabınız donduruldu. Yeniden aktifleştirmek için giriş yapabilirsiniz.');
                    window.location.href = '../index.php';
                } else {
                    alert('Hata: ' + data.message);
                }
            });
        }
    }

    // Hesap silme
    function deleteAccount() {
        const confirmation = prompt('Hesabınızı kalıcı olarak silmek için "HESABI SIL" yazın:');
        
        if (confirmation === 'HESABI SIL') {
            fetch('../api/delete-account.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Hesabınız kalıcı olarak silindi.');
                    window.location.href = '../index.php';
                } else {
                    alert('Hata: ' + data.message);
                }
            });
        } else if (confirmation !== null) {
            alert('Yanlış metin girdiniz. İşlem iptal edildi.');
        }
    }

    // Tab geçişlerinde form verilerini koru
    document.querySelectorAll('.settings-tabs button').forEach(tab => {
        tab.addEventListener('click', function() {
            // Form verilerini localStorage'a kaydet (isteğe bağlı)
            const activeForm = document.querySelector('.tab-pane.active form');
            if (activeForm) {
                const formData = new FormData(activeForm);
                // localStorage.setItem('tempFormData', JSON.stringify(Object.fromEntries(formData)));
            }
        });
    });

    // Bildirim ayarları değişiklik takibi
    document.querySelectorAll('input[type="checkbox"]').forEach(checkbox => {
        checkbox.addEventListener('change', function() {
            // Değişiklikleri otomatik kaydet veya "değişiklikler kaydedilmedi" uyarısı göster
            console.log(`${this.id} değişti: ${this.checked}`);
        });
    });

    // Form gönderilmeden önce onay
    document.querySelectorAll('form').forEach(form => {
        form.addEventListener('submit', function(e) {
            if (this.querySelector('input[name="change_password"]')) {
                if (!confirm('Şifrenizi değiştirmek istediğinizden emin misiniz?')) {
                    e.preventDefault();
                }
            }
        });
    });
    </script>

    <style>
    .profile-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        text-align: center;
    }

    .profile-header {
        background: linear-gradient(135deg, #0d6efd 0%, #6610f2 100%);
        color: white;
        padding: 30px 20px;
    }

    .profile-avatar {
        width: 80px;
        height: 80px;
        border-radius: 50%;
        background: rgba(255,255,255,0.2);
        display: flex;
        align-items: center;
        justify-content: center;
        margin: 0 auto 15px;
        font-size: 32px;
        backdrop-filter: blur(10px);
    }

    .profile-header h4 {
        margin-bottom: 5px;
        font-weight: 600;
    }

    .profile-badges {
        margin-top: 15px;
    }

    .profile-badges .badge {
        margin: 0 5px;
        padding: 8px 12px;
        font-size: 11px;
    }

    .profile-stats {
        padding: 20px;
        border-bottom: 1px solid #eee;
    }

    .stat-item strong {
        display: block;
        font-size: 20px;
        color: #0d6efd;
        font-weight: 700;
    }

    .stat-item small {
        color: #6c757d;
        font-size: 12px;
    }

    .profile-info {
        padding: 20px;
    }

    .info-item {
        display: flex;
        align-items: center;
        margin-bottom: 10px;
        font-size: 14px;
        color: #6c757d;
    }

    .info-item i {
        width: 20px;
        margin-right: 10px;
        color: #0d6efd;
    }

    .settings-tabs {
        background: white;
        border-radius: 15px 15px 0 0;
        border-bottom: 1px solid #dee2e6;
        padding: 0 20px;
    }

    .settings-tabs .nav-link {
        border: none;
        padding: 15px 20px;
        color: #6c757d;
        font-weight: 500;
        border-radius: 0;
        position: relative;
    }

    .settings-tabs .nav-link.active {
        color: #0d6efd;
        background: none;
        border-bottom: 3px solid #0d6efd;
    }

    .settings-tabs .nav-link i {
        margin-right: 8px;
    }

    .settings-content {
        background: white;
        border-radius: 0 0 15px 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
    }

    .settings-section {
        padding: 30px;
        border-bottom: 1px solid #f8f9fa;
    }

    .settings-section:last-child {
        border-bottom: none;
    }

    .settings-section h5 {
        color: #2c3e50;
        margin-bottom: 20px;
        font-weight: 600;
    }

    .settings-section h5 i {
        margin-right: 10px;
        color: #0d6efd;
    }

    .notification-item,
    .privacy-item {
        padding: 15px;
        border: 1px solid #e9ecef;
        border-radius: 10px;
        margin-bottom: 15px;
        transition: background-color 0.3s ease;
    }

    .notification-item:hover,
    .privacy-item:hover {
        background-color: #f8f9fa;
    }

    .form-check-input:checked {
        background-color: #0d6efd;
        border-color: #0d6efd;
    }

    .security-log {
        background: #f8f9fa;
        border-radius: 10px;
        padding: 20px;
    }

    .log-item {
        display: flex;
        align-items: center;
        padding: 10px 0;
        border-bottom: 1px solid #e9ecef;
    }

    .log-item:last-child {
        border-bottom: none;
    }

    .log-icon {
        width: 40px;
        height: 40px;
        border-radius: 50%;
        background: white;
        display: flex;
        align-items: center;
        justify-content: center;
        margin-right: 15px;
        font-size: 16px;
    }

    .account-actions {
        margin-top: 20px;
    }

    .account-actions .btn {
        margin-right: 10px;
        margin-bottom: 10px;
    }

    @media (max-width: 768px) {
        .settings-tabs {
            padding: 0 10px;
        }
        
        .settings-tabs .nav-link {
            padding: 12px 15px;
            font-size: 14px;
        }
        
        .settings-section {
            padding: 20px;
        }
        
        .account-actions .d-flex {
            flex-direction: column;
            align-items: stretch;
        }
        
        .account-actions .btn {
            margin-right: 0;
            margin-bottom: 10px;
        }
    }
    </style>
</body>
</html>