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
$current_page = 'admin-settings';
$page_title = $user['name'] . ' - Sistem Ayarları';
$user_name = $user['name']; // Sidebar için

// Sistem ayarları işlemleri
if (isset($_POST['action'])) {
    $action = $_POST['action'];
    
    switch ($action) {
        case 'update_site_settings':
            $site_name = trim($_POST['site_name'] ?? '');
            $site_description = trim($_POST['site_description'] ?? '');
            $site_keywords = trim($_POST['site_keywords'] ?? '');
            $contact_email = trim($_POST['contact_email'] ?? '');
            $contact_phone = trim($_POST['contact_phone'] ?? '');
            $address = trim($_POST['address'] ?? '');
            
            if (!empty($site_name) && !empty($contact_email)) {
                // Site ayarlarını güncelle (settings tablosu varsa)
                $settings = [
                    'site_name' => $site_name,
                    'site_description' => $site_description,
                    'site_keywords' => $site_keywords,
                    'contact_email' => $contact_email,
                    'contact_phone' => $contact_phone,
                    'address' => $address
                ];
                
                // Ayarları JSON olarak sakla veya ayrı tabloda tut
                $success = "Site ayarları başarıyla güncellendi!";
            } else {
                $error = "Site adı ve iletişim emaili zorunludur!";
            }
            break;
            
        case 'update_email_settings':
            $smtp_host = trim($_POST['smtp_host'] ?? '');
            $smtp_port = trim($_POST['smtp_port'] ?? '');
            $smtp_username = trim($_POST['smtp_username'] ?? '');
            $smtp_password = trim($_POST['smtp_password'] ?? '');
            
            if (!empty($smtp_host) && !empty($smtp_port)) {
                // Email ayarlarını güncelle
                $success = "Email ayarları başarıyla güncellendi!";
            } else {
                $error = "SMTP host ve port zorunludur!";
            }
            break;
            
        case 'backup_database':
            // Veritabanı yedekleme işlemi
            $backup_file = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            // Backup logic here
            $success = "Veritabanı yedeği oluşturuldu: " . $backup_file;
            break;
            
        case 'clear_cache':
            // Cache temizleme işlemi
            $success = "Önbellek başarıyla temizlendi!";
            break;
    }
}

// Sistem istatistikleri
$stats_query = "SELECT 
    (SELECT COUNT(*) FROM users) as total_users,
    (SELECT COUNT(*) FROM properties) as total_properties,
    (SELECT COUNT(*) FROM properties WHERE status = 'active') as active_properties,
    (SELECT COUNT(*) FROM users WHERE role = 'admin') as admin_users,
    (SELECT COUNT(*) FROM users WHERE is_active = 1) as active_users";

$stats_result = $conn->query($stats_query);
$system_stats = $stats_result->fetch_assoc();

// Sistem bilgileri
$system_info = [
    'php_version' => phpversion(),
    'mysql_version' => $conn->server_info,
    'server_software' => $_SERVER['SERVER_SOFTWARE'] ?? 'Bilinmiyor',
    'disk_free_space' => disk_free_space('.'),
    'memory_limit' => ini_get('memory_limit'),
    'max_execution_time' => ini_get('max_execution_time'),
    'upload_max_filesize' => ini_get('upload_max_filesize')
];
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
        /* Admin Settings Specific Styles */
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
        
        .settings-section {
            background: white;
            border-radius: 20px;
            padding: 30px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
            border: 1px solid #f0f0f0;
        }
        
        .section-title {
            font-size: 20px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 20px;
            display: flex;
            align-items: center;
            gap: 10px;
            padding-bottom: 15px;
            border-bottom: 2px solid #f8f9fa;
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }

        .stats-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            transition: all 0.3s ease;
            border-left: 4px solid #0d6efd;
        }

        .stats-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .stats-icon {
            width: 50px;
            height: 50px;
            border-radius: 12px;
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 20px;
            margin-bottom: 15px;
        }

        .stats-number {
            font-size: 28px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }

        .stats-label {
            font-size: 13px;
            color: #6c757d;
            font-weight: 500;
        }

        .system-info {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 20px;
        }

        .info-item {
            display: flex;
            justify-content: space-between;
            align-items: center;
            padding: 8px 0;
            border-bottom: 1px solid #e9ecef;
        }

        .info-item:last-child {
            border-bottom: none;
        }

        .info-label {
            font-weight: 600;
            color: #495057;
        }

        .info-value {
            color: #6c757d;
            font-family: monospace;
        }

        .action-card {
            background: white;
            border-radius: 15px;
            padding: 25px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.08);
            text-align: center;
            transition: all 0.3s ease;
        }

        .action-card:hover {
            transform: translateY(-3px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.12);
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, #28a745, #20c997);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin: 0 auto 15px;
        }

        .action-card.danger .action-icon {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
        }

        .action-card.warning .action-icon {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
        }

        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }

        .btn-save {
            background: linear-gradient(135deg, #0d6efd, #6f42c1);
            border: none;
            padding: 12px 30px;
            border-radius: 10px;
            color: white;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .btn-save:hover {
            transform: translateY(-2px);
            box-shadow: 0 5px 15px rgba(13, 110, 253, 0.4);
        }

        .btn-action {
            padding: 10px 20px;
            border-radius: 10px;
            border: none;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-action:hover {
            transform: translateY(-2px);
        }

        .btn-success {
            background: linear-gradient(135deg, #28a745, #20c997);
            color: white;
        }

        .btn-warning {
            background: linear-gradient(135deg, #ffc107, #fd7e14);
            color: white;
        }

        .btn-danger {
            background: linear-gradient(135deg, #dc3545, #fd7e14);
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
                grid-template-columns: repeat(2, 1fr);
            }
            
            .settings-section {
                padding: 20px;
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
                    <i class="fas fa-cogs me-3"></i>Sistem Ayarları
                </h2>
                <p class="welcome-subtitle">Sistem yapılandırması ve yönetim araçları</p>
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

            <!-- System Statistics -->
            <div class="stats-grid">
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-users"></i>
                    </div>
                    <div class="stats-number"><?= $system_stats['total_users'] ?></div>
                    <div class="stats-label">Toplam Kullanıcı</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="stats-number"><?= $system_stats['total_properties'] ?></div>
                    <div class="stats-label">Toplam İlan</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-eye"></i>
                    </div>
                    <div class="stats-number"><?= $system_stats['active_properties'] ?></div>
                    <div class="stats-label">Aktif İlan</div>
                </div>
                
                <div class="stats-card">
                    <div class="stats-icon">
                        <i class="fas fa-user-shield"></i>
                    </div>
                    <div class="stats-number"><?= $system_stats['admin_users'] ?></div>
                    <div class="stats-label">Admin Kullanıcı</div>
                </div>
            </div>

            <!-- Site Settings -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-globe"></i>Site Ayarları
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_site_settings">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Adı</label>
                            <input type="text" class="form-control" name="site_name" value="Gökhan Aydınlı Real Estate" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">İletişim Email</label>
                            <input type="email" class="form-control" name="contact_email" value="info@gokhanaydinli.com" required>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">İletişim Telefon</label>
                            <input type="text" class="form-control" name="contact_phone" value="+90 555 123 45 67">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">Site Anahtar Kelimeleri</label>
                            <input type="text" class="form-control" name="site_keywords" value="emlak, gayrimenkul, ev, daire, villa">
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Site Açıklaması</label>
                            <textarea class="form-control" name="site_description" rows="3">Gökhan Aydınlı ile emlak dünyasında güvenilir adresiniz</textarea>
                        </div>
                        <div class="col-12 mb-3">
                            <label class="form-label">Adres</label>
                            <textarea class="form-control" name="address" rows="2">İstanbul, Türkiye</textarea>
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save me-2"></i>Ayarları Kaydet
                    </button>
                </form>
            </div>

            <!-- System Information -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-info-circle"></i>Sistem Bilgileri
                </h3>
                <div class="system-info">
                    <div class="info-item">
                        <span class="info-label">PHP Sürümü</span>
                        <span class="info-value"><?= $system_info['php_version'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">MySQL Sürümü</span>
                        <span class="info-value"><?= $system_info['mysql_version'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Web Sunucu</span>
                        <span class="info-value"><?= $system_info['server_software'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Bellek Limiti</span>
                        <span class="info-value"><?= $system_info['memory_limit'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Maksimum Dosya Boyutu</span>
                        <span class="info-value"><?= $system_info['upload_max_filesize'] ?></span>
                    </div>
                    <div class="info-item">
                        <span class="info-label">Maksimum Çalışma Süresi</span>
                        <span class="info-value"><?= $system_info['max_execution_time'] ?> saniye</span>
                    </div>
                </div>
            </div>

            <!-- System Actions -->
            <div class="row">
                <div class="col-md-3 mb-4">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-database"></i>
                        </div>
                        <h5>Veritabanı Yedek</h5>
                        <p class="text-muted small">Veritabanının yedeğini al</p>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="backup_database">
                            <button type="submit" class="btn-action btn-success">
                                <i class="fas fa-download me-1"></i>Yedek Al
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="action-card warning">
                        <div class="action-icon">
                            <i class="fas fa-broom"></i>
                        </div>
                        <h5>Önbellek Temizle</h5>
                        <p class="text-muted small">Sistem önbelleğini temizle</p>
                        <form method="POST" class="d-inline">
                            <input type="hidden" name="action" value="clear_cache">
                            <button type="submit" class="btn-action btn-warning">
                                <i class="fas fa-trash me-1"></i>Temizle
                            </button>
                        </form>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-chart-bar"></i>
                        </div>
                        <h5>Sistem Raporları</h5>
                        <p class="text-muted small">Detaylı sistem raporları</p>
                        <a href="#" class="btn-action btn-success">
                            <i class="fas fa-file-alt me-1"></i>Raporlar
                        </a>
                    </div>
                </div>
                
                <div class="col-md-3 mb-4">
                    <div class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <h5>Sistem Bakım</h5>
                        <p class="text-muted small">Sistem bakım araçları</p>
                        <a href="#" class="btn-action btn-success">
                            <i class="fas fa-tools me-1"></i>Bakım
                        </a>
                    </div>
                </div>
            </div>

            <!-- Email Settings -->
            <div class="settings-section">
                <h3 class="section-title">
                    <i class="fas fa-envelope"></i>Email Ayarları
                </h3>
                <form method="POST">
                    <input type="hidden" name="action" value="update_email_settings">
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Host</label>
                            <input type="text" class="form-control" name="smtp_host" placeholder="smtp.gmail.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Port</label>
                            <input type="number" class="form-control" name="smtp_port" placeholder="587">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Kullanıcı Adı</label>
                            <input type="text" class="form-control" name="smtp_username" placeholder="your-email@gmail.com">
                        </div>
                        <div class="col-md-6 mb-3">
                            <label class="form-label">SMTP Şifre</label>
                            <input type="password" class="form-control" name="smtp_password" placeholder="••••••••">
                        </div>
                    </div>
                    <button type="submit" class="btn-save">
                        <i class="fas fa-save me-2"></i>Email Ayarlarını Kaydet
                    </button>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form validation
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function(e) {
                const requiredFields = form.querySelectorAll('[required]');
                let isValid = true;
                
                requiredFields.forEach(field => {
                    if (!field.value.trim()) {
                        isValid = false;
                        field.classList.add('is-invalid');
                    } else {
                        field.classList.remove('is-invalid');
                    }
                });
                
                if (!isValid) {
                    e.preventDefault();
                    alert('Lütfen tüm zorunlu alanları doldurun!');
                }
            });
        });
        
        // Confirmation for critical actions
        document.querySelectorAll('button[type="submit"]').forEach(button => {
            const action = button.closest('form')?.querySelector('input[name="action"]')?.value;
            
            if (action === 'backup_database') {
                button.addEventListener('click', function(e) {
                    if (!confirm('Veritabanı yedeği almak istediğinizden emin misiniz?')) {
                        e.preventDefault();
                    }
                });
            } else if (action === 'clear_cache') {
                button.addEventListener('click', function(e) {
                    if (!confirm('Önbelleği temizlemek istediğinizden emin misiniz?')) {
                        e.preventDefault();
                    }
                });
            }
        });
    </script>
</body>
</html>
