<?php
session_start();
include '../db.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$current_page = 'profile';

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

// Avatar YÜKLEME işlemi - TAMAMEN DÜZELTİLMİŞ VERSİYON
if (isset($_POST['upload_avatar'])) {
    error_log("Avatar upload process started for user: " . $user_id);

    // Dosya var mı kontrol et
    if (!isset($_FILES['uploadImg'])) {
        $error = "Dosya bulunamadı! Lütfen tekrar deneyin.";
        error_log("No file found in _FILES array");
    } elseif ($_FILES['uploadImg']['error'] !== UPLOAD_ERR_OK) {
        $error_codes = [
            UPLOAD_ERR_INI_SIZE => 'Dosya çok büyük (php.ini sınırı: ' . ini_get('upload_max_filesize') . ')',
            UPLOAD_ERR_FORM_SIZE => 'Dosya çok büyük (form sınırı)',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi, tekrar deneyin',
            UPLOAD_ERR_NO_FILE => 'Hiç dosya seçilmedi',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör bulunamadı',
            UPLOAD_ERR_CANT_WRITE => 'Disk yazma hatası',
            UPLOAD_ERR_EXTENSION => 'PHP uzantısı engeliyor'
        ];
        $error_code = $_FILES['uploadImg']['error'];
        $error = $error_codes[$error_code] ?? "Bilinmeyen hata (Kod: $error_code)";
        error_log("File upload error: " . $error_code);
    } else {
        $file = $_FILES['uploadImg'];

        // Dosya bilgilerini logla
        error_log("File info: name=" . $file['name'] . ", size=" . $file['size'] . ", type=" . $file['type']);

        // Geçici dosya mevcut mu?
        if (!file_exists($file['tmp_name'])) {
            $error = "Geçici dosya bulunamadı! Server ayarlarını kontrol edin.";
            error_log("Temp file not found: " . $file['tmp_name']);
        } else {
            // Klasör hazırlığı
            $upload_base_dir = __DIR__ . '/../uploads/';
            $avatar_dir = $upload_base_dir . 'avatars/';

            // Ana upload klasörünü oluştur
            if (!is_dir($upload_base_dir)) {
                if (!mkdir($upload_base_dir, 0755, true)) {
                    $error = "Ana upload klasörü oluşturulamadı!";
                    error_log("Failed to create upload base dir: " . $upload_base_dir);
                }
            }

            // Avatar klasörünü oluştur
            if (!$error && !is_dir($avatar_dir)) {
                if (!mkdir($avatar_dir, 0755, true)) {
                    $error = "Avatar klasörü oluşturulamadı!";
                    error_log("Failed to create avatar dir: " . $avatar_dir);
                }
            }

            if (!$error) {
                // Klasör yazılabilir mi?
                if (!is_writable($avatar_dir)) {
                    $error = "Avatar klasörü yazılabilir değil! İzinleri kontrol edin.";
                    error_log("Avatar directory not writable: " . $avatar_dir);
                } else {
                    // Dosya extension kontrolü
                    $original_name = $file['name'];
                    $ext = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $allowed_extensions = ['jpg', 'jpeg', 'png', 'gif', 'webp'];

                    if (!in_array($ext, $allowed_extensions)) {
                        $error = "Desteklenmeyen dosya formatı! Sadece: " . implode(', ', $allowed_extensions);
                        error_log("Invalid file extension: " . $ext);
                    } elseif ($file['size'] > 5 * 1024 * 1024) {
                        $error = "Dosya çok büyük! Maksimum 5MB. (Mevcut: " . round($file['size'] / 1024 / 1024, 2) . "MB)";
                        error_log("File too large: " . $file['size']);
                    } else {
                        // Resim dosyası mı kontrol et
                        $image_info = @getimagesize($file['tmp_name']);
                        if (!$image_info) {
                            $error = "Geçerli bir resim dosyası değil!";
                            error_log("Invalid image file");
                        } else {
                            // Güvenli dosya adı oluştur
                            $safe_filename = "user_" . $user_id . "_" . time() . "." . $ext;
                            $target_file = $avatar_dir . $safe_filename;

                            error_log("Attempting to move file to: " . $target_file);

                            // Eski avatar'ı sil (işlem başarılı olursa)
                            $old_avatar = null;
                            if (!empty($user['avatar_path'])) {
                                $old_avatar_file = __DIR__ . '/../' . $user['avatar_path'];
                                if (file_exists($old_avatar_file)) {
                                    $old_avatar = $old_avatar_file;
                                }
                            }

                            // Dosyayı taşı
                            if (move_uploaded_file($file['tmp_name'], $target_file)) {
                                error_log("File moved successfully to: " . $target_file);

                                // Veritabanını güncelle
                                $avatar_path = "uploads/avatars/" . $safe_filename;

                                // Prepared statement ile güvenli güncelleme
                                $stmt = $conn->prepare("UPDATE users SET avatar_path = ?, updated_at = NOW() WHERE id = ?");
                                $stmt->bind_param("si", $avatar_path, $user_id);

                                if ($stmt->execute()) {
                                    error_log("Database updated successfully with avatar: " . $avatar_path);

                                    // Eski avatar'ı sil
                                    if ($old_avatar && file_exists($old_avatar)) {
                                        unlink($old_avatar);
                                        error_log("Old avatar deleted: " . $old_avatar);
                                    }

                                    // Kullanıcı verilerini yeniden yükle
                                    $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                                    $stmt->bind_param("i", $user_id);
                                    $stmt->execute();
                                    $user = $stmt->get_result()->fetch_assoc();

                                    $success = "Profil fotoğrafı başarıyla güncellendi!";

                                } else {
                                    $error = "Veritabanı güncellenirken hata oluştu: " . $conn->error;
                                    error_log("Database update failed: " . $conn->error);
                                    // Başarısız olursa dosyayı sil
                                    if (file_exists($target_file)) {
                                        unlink($target_file);
                                    }
                                }
                            } else {
                                $error = "Dosya yüklenemedi! Sistem hatası.";
                                error_log("move_uploaded_file failed. Source: " . $file['tmp_name'] . " Target: " . $target_file);
                                error_log("Source exists: " . (file_exists($file['tmp_name']) ? 'yes' : 'no'));
                                error_log("Target dir exists: " . (is_dir($avatar_dir) ? 'yes' : 'no'));
                                error_log("Target dir writable: " . (is_writable($avatar_dir) ? 'yes' : 'no'));
                            }
                        }
                    }
                }
            }
        }
    }
}

// Avatar SİLME işlemi - daha güvenli
if (isset($_POST['delete_avatar'])) {
    error_log("Avatar delete process started for user: " . $user_id);

    $old_avatar_path = $user['avatar_path'] ?? '';

    // Veritabanından avatar yolunu sil
    $stmt = $conn->prepare("UPDATE users SET avatar_path = NULL, updated_at = NOW() WHERE id = ?");
    $stmt->bind_param("i", $user_id);

    if ($stmt->execute()) {
        // Fiziksel dosyayı sil
        if (!empty($old_avatar_path)) {
            $file_to_delete = __DIR__ . '/../' . $old_avatar_path;
            if (file_exists($file_to_delete)) {
                if (unlink($file_to_delete)) {
                    error_log("Avatar file deleted successfully: " . $file_to_delete);
                } else {
                    error_log("Failed to delete avatar file: " . $file_to_delete);
                }
            }
        }

        // Kullanıcı verilerini güncelle
        $user['avatar_path'] = null;
        $success = "Profil fotoğrafı başarıyla silindi!";

    } else {
        $error = "Profil fotoğrafı silinirken hata oluştu: " . $conn->error;
        error_log("Database delete failed: " . $conn->error);
    }
}

// Profil güncelleme işlemi
if (isset($_POST['update_profile'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $phone = trim($_POST['phone'] ?? '');
    // $position = trim($_POST['position'] ?? ''); // REMOVED
    $website = trim($_POST['website'] ?? '');
    $address = trim($_POST['address'] ?? '');
    $about = trim($_POST['about'] ?? '');

    // Validasyon
    if (empty($name) || empty($email)) {
        $error = "Ad Soyad ve E-posta alanları zorunludur!";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $error = "Geçerli bir e-posta adresi girin!";
    } else {
        // E-posta benzersizlik kontrolü (kendi e-postası hariç)
        $stmt = $conn->prepare("SELECT id FROM users WHERE email = ? AND id != ?");
        $stmt->bind_param("si", $email, $user_id);
        $stmt->execute();
        
        if ($stmt->get_result()->num_rows > 0) {
            $error = "Bu e-posta adresi başka bir kullanıcı tarafından kullanılıyor!";
        } else {
            // Güncelleme
            $stmt = $conn->prepare("UPDATE users SET name = ?, email = ?, phone = ?, website = ?, address = ?, about = ?, updated_at = NOW() WHERE id = ?");
            $stmt->bind_param("ssssssi", $name, $email, $phone, $website, $address, $about, $user_id);
            
            if ($stmt->execute()) {
                $success = "Profil bilgileri başarıyla güncellendi!";
                // Kullanıcı verilerini yeniden yükle
                $stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->bind_param("i", $user_id);
                $stmt->execute();
                $user = $stmt->get_result()->fetch_assoc();
            } else {
                $error = "Profil güncellenirken hata oluştu: " . $conn->error;
            }
        }
    }
}

// Success mesajı URL'den
if (isset($_GET['success'])) {
    $success = "Profil başarıyla güncellendi!";
}

// İstatistikleri hesapla
try {
    // Kullanıcının emlak istatistikleri
    if ($user['role'] != 'admin') {
        $user_properties_query = "SELECT 
            COUNT(*) as total_properties,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count,
            SUM(CASE WHEN type = 'sale' THEN 1 ELSE 0 END) as sale_count,
            SUM(CASE WHEN type = 'rent' THEN 1 ELSE 0 END) as rent_count,
            AVG(price) as avg_price
            FROM properties WHERE user_id = ?";
        $stmt = $conn->prepare($user_properties_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $user_stats = $stmt->get_result()->fetch_assoc();
    } else {
        // Admin için genel istatistikler
        $admin_stats_query = "SELECT 
            COUNT(*) as total_properties,
            SUM(CASE WHEN status = 'approved' THEN 1 ELSE 0 END) as approved_count,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_count,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_count
            FROM properties";
        $user_stats = $conn->query($admin_stats_query)->fetch_assoc();
        
        $user_count_query = "SELECT COUNT(*) as total_users FROM users";
        $user_count = $conn->query($user_count_query)->fetch_assoc();
        $user_stats['total_users'] = $user_count['total_users'];
    }

    // Son aktiviteler
    if ($user['role'] != 'admin') {
        $recent_activities_query = "SELECT p.id, p.title, p.status, p.created_at, p.price, p.type, p.city, p.district
                                   FROM properties p 
                                   WHERE p.user_id = ? 
                                   ORDER BY p.created_at DESC 
                                   LIMIT 5";
        $stmt = $conn->prepare($recent_activities_query);
        $stmt->bind_param("i", $user_id);
        $stmt->execute();
        $recent_activities = $stmt->get_result();
    } else {
        $recent_activities_query = "SELECT p.id, p.title, p.status, p.created_at, u.name as owner_name, p.city, p.district
                                   FROM properties p 
                                   LEFT JOIN users u ON p.user_id = u.id 
                                   ORDER BY p.created_at DESC 
                                   LIMIT 5";
        $recent_activities = $conn->query($recent_activities_query);
    }

} catch (Exception $e) {
    error_log("Profile Stats Error: " . $e->getMessage());
    $user_stats = array_fill_keys(['total_properties', 'approved_count', 'pending_count', 'rejected_count', 'sale_count', 'rent_count', 'avg_price'], 0);
}

// Profil tamamlanma yüzdesi hesapla
$completion_fields = ['name', 'email', 'phone', 'address', 'about', 'website'];
$completed_fields = 0;
foreach ($completion_fields as $field) {
    if (!empty($user[$field])) {
        $completed_fields++;
    }
}
$completion_percentage = round(($completed_fields / count($completion_fields)) * 100);
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
    <!-- Chart.js -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/3.9.1/chart.min.js"></script>
    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <!-- Dashboard Common CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    
    <style>
        /* Profile Specific Styles - Updated with blue colors */
        :root {
            --primary-color: #1a2e30;
            --secondary-color: #0d6efd; /* Changed from green to blue */
            --accent-color: #ff6b35;
            --border-radius: 16px;
            --box-shadow: 0 4px 20px rgba(13, 110, 253, 0.08); /* Updated shadow color */
        }

        .profile-banner {
            background: linear-gradient(135deg, var(--primary-color), #1a2e30);
            color: white;
            border-radius: var(--border-radius);
            padding: 40px;
            margin: 0 30px 40px 30px;
            position: relative;
            overflow: hidden;
        }

        .profile-banner::before {
            content: '';
            position: absolute;
            top: -50%;
            right: -20%;
            width: 200px;
            height: 200px;
            border-radius: 50%;
            background: rgba(13, 110, 253, 0.1); /* Changed to blue */
        }

        .profile-banner::after {
            content: '';
            position: absolute;
            bottom: -30%;
            left: -10%;
            width: 150px;
            height: 150px;
            border-radius: 50%;
            background: rgba(255, 107, 53, 0.1);
        }

        .profile-banner-content {
            position: relative;
            z-index: 2;
        }

        .profile-avatar-large {
            width: 120px;
            height: 120px;
            border-radius: 50%;
            border: 4px solid var(--secondary-color);
            object-fit: cover;
            box-shadow: 0 8px 32px rgba(0,0,0,0.3);
        }

        .user-avatar-setting {
            background: #fff;
            border: 1px solid #e6e6e6;
            border-radius: 16px;
            padding: 32px 0 24px 0;
            box-shadow: 0 2px 16px rgba(13,110,253,0.04);
            margin-bottom: 32px;
        }

        .user-avatar-setting.text-center {
            flex-direction: column;
            gap: 18px;
            background: #fff;
            border: 1px solid #e6e6e6;
        }

        .user-avatar-setting .user-img {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            object-fit: cover;
            border: 4px solid var(--secondary-color);
            box-shadow: var(--box-shadow);
        }

        .avatar-actions {
            display: flex;
            flex-direction: column;
            gap: 12px;
        }

        .upload-btn, .delete-btn {
            padding: 12px 24px;
            border-radius: 10px;
            font-size: 14px;
            font-weight: 500;
            cursor: pointer;
            transition: all 0.3s ease;
            border: none;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            position: relative;
            overflow: hidden;
        }

        .upload-btn {
            background: var(--secondary-color);
            color: white;
        }

        .upload-btn:hover {
            background: #0b5ed7; /* Darker blue on hover */
            transform: translateY(-2px);
        }

        .upload-btn input[type="file"] {
            position: absolute;
            left: 0;
            top: 0;
            width: 100%;
            height: 100%;
            opacity: 0;
            cursor: pointer;
        }

        .delete-btn {
            background: var(--accent-color);
            color: white;
        }

        .delete-btn:hover {
            background: #e55a2b;
            transform: translateY(-2px);
        }

        .profile-form {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 35px;
        }

        .profile-form .form-group {
            margin-bottom: 25px;
        }

        .profile-form label {
            display: block;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 14px;
        }

        .profile-form input,
        .profile-form textarea {
            width: 100%;
            padding: 14px 16px;
            border: 2px solid #E6E6E6;
            border-radius: 10px;
            font-size: 14px;
            transition: all 0.3s ease;
            background: white;
        }

        .profile-form input:focus,
        .profile-form textarea:focus {
            outline: none;
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1); /* Updated focus color */
        }

        .profile-form input[readonly] {
            background: #f8f9fa;
            color: #6c757d;
            cursor: not-allowed;
        }

        .profile-form textarea {
            min-height: 120px;
            resize: vertical;
        }

        .completion-progress {
            background: rgba(13, 110, 253, 0.1); /* Changed to blue */
            border-radius: 15px;
            padding: 20px;
            margin-bottom: 30px;
            border: 1px solid rgba(13, 110, 253, 0.2); /* Changed to blue */
        }

        .completion-progress .progress {
            height: 12px;
            border-radius: 6px;
            overflow: hidden;
            background: rgba(13, 110, 253, 0.2); /* Changed to blue */
        }

        .completion-progress .progress-bar {
            background: var(--secondary-color);
            transition: width 0.6s ease;
        }

        .activity-item {
            display: flex;
            align-items: center;
            gap: 15px;
            padding: 15px 0;
            border-bottom: 1px solid rgba(13, 110, 253, 0.1); /* Changed to blue */
        }

        .activity-item:last-child {
            border-bottom: none;
        }

        .activity-icon {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), #0b5ed7); /* Changed to blue gradient */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 16px;
            flex-shrink: 0;
        }

        .activity-info h6 {
            margin: 0 0 5px 0;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 14px;
        }

        .activity-info small {
            color: #666;
            font-size: 12px;
        }

        .activity-meta {
            margin-left: auto;
            text-align: right;
        }

        .chart-container {
            position: relative;
            height: 300px;
            margin-top: 20px;
        }

        .account-info-card {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            margin-bottom: 25px;
        }

        /* Stats Cards */
        .stats-cards {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .stats-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            transition: all 0.3s ease;
            border: 1px solid rgba(13, 110, 253, 0.1); /* Added blue border */
        }

        .stats-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(13, 110, 253, 0.15); /* Updated hover shadow */
        }

        .stats-card-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 20px;
        }

        .stats-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), #0b5ed7); /* Changed to blue */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
        }

        .stats-number {
            font-size: 36px;
            font-weight: 700;
            color: var(--primary-color);
            margin-bottom: 8px;
        }

        .stats-label {
            font-size: 14px;
            color: #666;
            margin-bottom: 12px;
        }

        .stats-change {
            display: flex;
            align-items: center;
            gap: 6px;
            font-size: 12px;
            font-weight: 500;
        }

        .stats-change.positive {
            color: var(--secondary-color); /* Changed to blue */
        }

        .stats-change.negative {
            color: var(--accent-color);
        }

        /* Quick Actions */
        .quick-actions {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
            gap: 25px;
            margin-bottom: 40px;
        }

        .action-card {
            background: white;
            border-radius: var(--border-radius);
            padding: 30px;
            box-shadow: var(--box-shadow);
            text-decoration: none;
            color: inherit;
            transition: all 0.3s ease;
            border: 1px solid rgba(13, 110, 253, 0.1); /* Added blue border */
        }

        .action-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 30px rgba(13, 110, 253, 0.15); /* Updated hover shadow */
            text-decoration: none;
            color: inherit;
        }

        .action-icon {
            width: 60px;
            height: 60px;
            border-radius: 50%;
            background: linear-gradient(135deg, var(--secondary-color), #0b5ed7); /* Changed to blue */
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-size: 24px;
            margin-bottom: 20px;
        }

        .action-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 10px;
        }

        .action-description {
            font-size: 14px;
            color: #666;
            margin: 0;
        }

        /* Content Sections */
        .content-section {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 30px;
            margin-bottom: 30px;
        }

        .section-header {
            margin-bottom: 25px;
            padding-bottom: 15px;
            border-bottom: 2px solid rgba(13, 110, 253, 0.1); /* Changed to blue */
        }

        .section-title {
            font-size: 18px;
            font-weight: 600;
            color: var(--primary-color);
            margin: 0;
            display: flex;
            align-items: center;
            gap: 10px;
        }

        .section-title i {
            color: var(--secondary-color);
        }

        /* Dashboard Buttons */
        .dash-btn-two {
            background: var(--secondary-color);
            color: white;
            padding: 12px 24px;
            border-radius: 10px;
            text-decoration: none;
            font-weight: 500;
            display: inline-flex;
            align-items: center;
            gap: 8px;
            transition: all 0.3s ease;
            border: none;
            cursor: pointer;
        }

        .dash-btn-two:hover {
            background: #0b5ed7; /* Darker blue on hover */
            transform: translateY(-2px);
            color: white;
            text-decoration: none;
        }

        @media (max-width: 768px) {
            .user-avatar-setting {
                flex-direction: column;
                text-align: center;
            }
            
            .avatar-actions {
                flex-direction: row;
                justify-content: center;
            }
            
            .profile-banner {
                padding: 25px;
                text-align: center;
                margin: 0 15px 30px 15px;
            }

            .stats-cards {
                grid-template-columns: 1fr;
            }

            .quick-actions {
                grid-template-columns: 1fr;
            }
        }

        .avatar-actions .btn {
            padding: 4px 10px !important;
            font-size: 12px !important;
            border-radius: 6px !important;
            min-width: 0;
        }

        .user-avatar-setting .btn {
            min-width: 160px;
            font-weight: 500;
        }

        .user-avatar-setting .btn-outline-secondary {
            border: 2px solid #e6e6e6;
            background: #fff;
            color: #333;
        }

        .user-avatar-setting .btn-outline-secondary:hover {
            background: #f8f9fa;
            color: #0d6efd;
            border-color: #0d6efd;
        }

        .btn:disabled {
            opacity: 0.6;
            cursor: not-allowed;
        }

        /* Text colors */
        .text-success {
            color: var(--secondary-color) !important; /* Changed success color to blue */
        }
    </style>
</head>

<body>
    <!-- Sidebar - SABİT MENÜ -->
    <?php include 'includes/sidebar.php'; ?>

    <div class="main-page-wrapper">
        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <h2 class="main-title d-block d-lg-none"><?= $page_title ?></h2>

                <!-- Success/Error Messages -->
                <?php if (!empty($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= htmlspecialchars($success) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= htmlspecialchars($error) ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Statistics Cards -->
                <div class="stats-cards">
                    <!-- Profile Completion -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-user-check"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $completion_percentage ?>%</div>
                        <div class="stats-label">Profil Tamamlanma</div>
                        <div class="stats-change <?= $completion_percentage >= 80 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-<?= $completion_percentage >= 80 ? 'check' : 'exclamation' ?>"></i>
                            <span><?= $completion_percentage >= 80 ? 'Tamamlandı' : 'Eksik bilgiler var' ?></span>
                        </div>
                    </div>

                    <!-- Total Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-home"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['total_properties'] ?? 0) ?></div>
                        <div class="stats-label"><?= $user['role'] == 'admin' ? 'Toplam Emlak (Sistem)' : 'Toplam İlanlarım' ?></div>
                        <div class="stats-change positive">
                            <i class="fas fa-chart-line"></i>
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
                        <div class="stats-label">Onaylı İlanlar</div>
                        <div class="stats-change positive">
                            <i class="fas fa-thumbs-up"></i>
                            <span>Yayında</span>
                        </div>
                    </div>

                    <!-- Pending Properties -->
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= number_format($user_stats['pending_count'] ?? 0) ?></div>
                        <div class="stats-label">Bekleyen İlanlar</div>
                        <div class="stats-change <?= ($user_stats['pending_count'] ?? 0) > 0 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-<?= ($user_stats['pending_count'] ?? 0) > 0 ? 'hourglass-half' : 'check' ?>"></i>
                            <span><?= ($user_stats['pending_count'] ?? 0) > 0 ? 'Onay bekliyor' : 'Hepsi onaylı' ?></span>
                        </div>
                    </div>
                </div>

                <!-- Quick Actions -->
                <div class="quick-actions">
                    <?php if(($user['can_add_property'] ?? 0) || $user['role'] == 'admin'): ?>
                    <a href="add-property.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <h5 class="action-title">Yeni İlan Ekle</h5>
                        <p class="action-description">Sisteme yeni bir emlak ilanı ekleyin</p>
                    </a>
                    <?php endif; ?>

                    <a href="properties-list.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-list"></i>
                        </div>
                        <h5 class="action-title">İlanlarımı Yönet</h5>
                        <p class="action-description">Mevcut ilanlarınızı görüntüleyin ve düzenleyin</p>
                    </a>

                    <a href="account-settings.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-shield-alt"></i>
                        </div>
                        <h5 class="action-title">Güvenlik Ayarları</h5>
                        <p class="action-description">Şifre ve güvenlik ayarlarınızı yönetin</p>
                    </a>

                    <a href="message.php" class="action-card">
                        <div class="action-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <h5 class="action-title">Mesajlarım</h5>
                        <p class="action-description">Gelen mesajları görüntüleyin ve yanıtlayın</p>
                    </a>
                </div>

                <div class="row">
                    <!-- Profile Information Form -->
                    <div class="col-lg-8">
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-user-edit"></i>
                                    Profil Bilgilerini Güncelle
                                </h5>
                            </div>

                            <!-- Profile Completion Progress -->
                            <div class="completion-progress">
                                <div class="d-flex justify-content-between align-items-center mb-2">
                                    <span class="fw-bold">Profil Tamamlanma</span>
                                    <span class="fw-bold text-success"><?= $completion_percentage ?>%</span>
                                </div>
                                <div class="progress">
                                    <div class="progress-bar" role="progressbar" style="width: <?= $completion_percentage ?>%"></div>
                                </div>
                                <small class="text-muted mt-1 d-block">
                                    Profil tamamlanma oranınızı artırmak için eksik bilgileri doldurun
                                </small>
                            </div>

                            <!-- Avatar Section -->
                            <form method="POST" enctype="multipart/form-data" class="mb-4" id="avatarForm">
                                <div class="d-flex align-items-center gap-4 user-avatar-setting" style="background: #fff; border: 1px solid #e6e6e6; border-radius: 16px; padding: 24px 32px;">
                                    <!-- Sol: Avatar gösterimi -->
                                    <div style="position:relative; width:120px; height:120px;">
                                        <?php if (!empty($user['avatar_path']) && file_exists(__DIR__ . '/../' . $user['avatar_path'])): ?>
                                            <img id="avatarPreview" src="../<?= htmlspecialchars($user['avatar_path']) ?>?v=<?= time() ?>" alt="Avatar" style="width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #0d6efd; box-shadow:0 4px 16px rgba(13,110,253,0.08);">
                                        <?php else: ?>
                                            <div id="avatarPreview" style="width:120px; height:120px; border-radius:50%; background:#0d6efd; display:flex; align-items:center; justify-content:center; color:#fff; font-size:56px; font-weight:700; border:4px solid #0d6efd; box-shadow:0 4px 16px rgba(13,110,253,0.08);">
                                                <?= strtoupper(mb_substr($user['name'], 0, 1, 'UTF-8')) ?>
                                            </div>
                                        <?php endif; ?>

                                        <!-- Kamera ikonu -->
                                        <label for="uploadImg" style="position:absolute; bottom:0; right:0; background:#fff; border-radius:50%; border:2px solid #0d6efd; width:38px; height:38px; display:flex; align-items:center; justify-content:center; cursor:pointer; box-shadow:0 2px 8px rgba(13,110,253,0.10);">
                                            <i class="fas fa-camera text-primary"></i>
                                        </label>

                                        <!-- Hidden file input -->
                                        <input type="file" 
                                               id="uploadImg" 
                                               name="uploadImg" 
                                               accept="image/jpeg,image/jpg,image/png,image/gif,image/webp" 
                                               style="display:none;" 
                                               onchange="handleFileSelect(this)">
                                    </div>

                                    <!-- Sağ: Butonlar -->
                                    <div class="d-flex flex-column gap-2">
                                        <button type="submit" 
                                                name="upload_avatar" 
                                                id="uploadButton" 
                                                disabled 
                                                class="btn btn-primary d-flex align-items-center gap-2" 
                                                style="font-size:15px; padding:8px 18px;">
                                            <i class="fas fa-upload"></i> Fotoğraf Yükle
                                        </button>

                                        <?php if (!empty($user['avatar_path'])): ?>
                                        <button type="submit" 
                                                name="delete_avatar" 
                                                class="btn btn-outline-secondary d-flex align-items-center gap-2" 
                                                style="font-size:15px; padding:8px 18px;" 
                                                onclick="return confirm('Profil fotoğrafını silmek istediğinizden emin misiniz?')">
                                            <i class="fas fa-trash"></i> Fotoğrafı Sil
                                        </button>
                                        <?php endif; ?>
                                    </div>
                                </div>
                            </form>

                            <div class="profile-form">
                                <form method="POST" enctype="multipart/form-data">
                                    <div class="row">
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="name">Ad Soyad*</label>
                                                <input type="text" id="name" name="name" value="<?= htmlspecialchars($user['name']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="email">E-posta*</label>
                                                <input type="email" id="email" name="email" value="<?= htmlspecialchars($user['email']) ?>" required>
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="phone">Telefon Numarası</label>
                                                <input type="tel" id="phone" name="phone" value="<?= htmlspecialchars($user['phone'] ?? '') ?>" placeholder="+90 (555) 123 45 67">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="position">Pozisyon/Ünvan</label>
                                                <input type="text" id="position" name="position" value="<?= htmlspecialchars($user['position'] ?? '') ?>" placeholder="Emlak Danışmanı, Gayrimenkul Uzmanı...">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="website">Web Sitesi</label>
                                                <input type="url" id="website" name="website" value="<?= htmlspecialchars($user['website'] ?? '') ?>" placeholder="https://example.com">
                                            </div>
                                        </div>
                                        <div class="col-md-6">
                                            <div class="form-group">
                                                <label for="address">Adres</label>
                                                <input type="text" id="address" name="address" value="<?= htmlspecialchars($user['address'] ?? '') ?>" placeholder="Tam adresiniz">
                                            </div>
                                        </div>
                                        <div class="col-12">
                                            <div class="form-group">
                                                <label for="about">Hakkımda</label>
                                                <textarea id="about" name="about" placeholder="Kendinizi kısaca tanıtın, uzmanlık alanlarınızı belirtin..." maxlength="500"><?= htmlspecialchars($user['about'] ?? '') ?></textarea>
                                                <small class="text-muted">Maksimum 500 karakter</small>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="d-flex gap-3 mt-4">
                                        <button type="submit" name="update_profile" class="dash-btn-two">
                                            <i class="fas fa-save"></i>
                                            Profili Güncelle
                                        </button>
                                        <a href="account-settings.php" class="dash-btn-two" style="background: #6f42c1;">
                                            <i class="fas fa-shield-alt"></i>
                                            Güvenlik Ayarları
                                        </a>
                                        <a href="dashboard<?= $user['role'] == 'admin' ? '-admin' : '' ?>.php" class="dash-btn-two" style="background: #6c757d;">
                                            <i class="fas fa-arrow-left"></i>
                                            Geri Dön
                                        </a>
                                    </div>
                                </form>
                            </div>
                        </div>
                    </div>

                    <!-- Sidebar -->
                    <div class="col-lg-4">
                        <!-- Recent Activities -->
                        <div class="content-section">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-history"></i>
                                    Son Aktiviteler
                                </h5>
                            </div>
                            <?php if ($recent_activities && $recent_activities->num_rows > 0): ?>
                                <?php while($activity = $recent_activities->fetch_assoc()): ?>
                                    <div class="activity-item">
                                        <div class="activity-icon">
                                            <i class="fas fa-home"></i>
                                        </div>
                                        <div class="activity-info">
                                            <h6><?= htmlspecialchars($activity['title']) ?></h6>
                                            <small>
                                                <?php if ($user['role'] == 'admin' && isset($activity['owner_name'])): ?>
                                                    <?= htmlspecialchars($activity['owner_name']) ?>
                                                <?php else: ?>
                                                    <?= isset($activity['price']) ? number_format($activity['price']) . ' ₺' : '' ?>
                                                    <?= isset($activity['type']) ? ' • ' . ucfirst($activity['type']) : '' ?>
                                                <?php endif; ?>
                                                <?php if (isset($activity['city']) && isset($activity['district'])): ?>
                                                    <br><?= htmlspecialchars($activity['city']) ?>, <?= htmlspecialchars($activity['district']) ?>
                                                <?php endif; ?>
                                            </small>
                                        </div>
                                        <div class="activity-meta">
                                            <span class="badge <?= match($activity['status']) {
                                                'approved' => 'bg-success',
                                                'pending' => 'bg-warning',
                                                'rejected' => 'bg-danger',
                                                default => 'bg-secondary'
                                            } ?>">
                                                <?= match($activity['status']) {
                                                    'approved' => 'Onaylı',
                                                    'pending' => 'Beklemede',
                                                    'rejected' => 'Reddedildi',
                                                    default => 'Bilinmiyor'
                                                } ?>
                                            </span>
                                            <br>
                                            <small class="text-muted"><?= date('d.m.Y', strtotime($activity['created_at'])) ?></small>
                                        </div>
                                    </div>
                                <?php endwhile; ?>
                            <?php else: ?>
                                <div class="text-center py-4">
                                    <i class="fas fa-history text-muted" style="font-size: 48px; opacity: 0.3;"></i>
                                    <p class="text-muted mt-3">Henüz aktivite yok</p>
                                </div>
                            <?php endif; ?>
                        </div>

                        <!-- Account Information -->
                        <div class="account-info-card">
                            <div class="section-header">
                                <h5 class="section-title">
                                    <i class="fas fa-info-circle"></i>
                                    Hesap Bilgileri
                                </h5>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-calendar-alt"></i>
                                </div>
                                <div class="activity-info">
                                    <h6>Kayıt Tarihi</h6>
                                    <small><?= date('d.m.Y H:i', strtotime($user['created_at'])) ?></small>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-shield-alt"></i>
                                </div>
                                <div class="activity-info">
                                    <h6>Hesap Durumu</h6>
                                    <small>
                                        <span class="badge bg-success">Aktif</span>
                                    </small>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-user-tag"></i>
                                </div>
                                <div class="activity-info">
                                    <h6>Kullanıcı Rolü</h6>
                                    <small><?= match($user['role']) {
                                        'admin' => 'Yönetici',
                                        'agent' => 'Acente',
                                        'user' => 'Kullanıcı',
                                        default => ucfirst($user['role'])
                                    } ?></small>
                                </div>
                            </div>
                            <div class="activity-item">
                                <div class="activity-icon">
                                    <i class="fas fa-key"></i>
                                </div>
                                <div class="activity-info">
                                    <h6>İlan Ekleme Yetkisi</h6>
                                    <small>
                                        <span class="badge <?= ($user['can_add_property'] ?? 0) || $user['role'] == 'admin' ? 'bg-success' : 'bg-warning' ?>">
                                            <?= ($user['can_add_property'] ?? 0) || $user['role'] == 'admin' ? 'Var' : 'Yok' ?>
                                        </span>
                                    </small>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Properties Statistics Chart (Only for non-admin users with properties) -->
                <?php if ($user['role'] != 'admin' && ($user_stats['total_properties'] ?? 0) > 0): ?>
                <div class="content-section">
                    <div class="section-header">
                        <h5 class="section-title">
                            <i class="fas fa-chart-pie"></i>
                            İlan İstatistikleri
                        </h5>
                    </div>
                    <div class="chart-container">
                        <canvas id="propertiesChart"></canvas>
                    </div>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/wow/1.1.2/wow.min.js"></script>
    <script>
        // Mobile nav toggle
        document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').classList.toggle('show');
        });

        // Avatar işlemleri
        function handleFileSelect(input) {
            const uploadButton = document.getElementById('uploadButton');
            const avatarPreview = document.getElementById('avatarPreview');

            console.log('File select event triggered');

            if (input.files && input.files[0]) {
                const file = input.files[0];
                console.log('File selected:', file.name, 'Size:', file.size, 'Type:', file.type);

                // Client-side validasyon
                const maxSize = 5 * 1024 * 1024; // 5MB
                const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];

                if (file.size > maxSize) {
                    alert('Dosya boyutu 5MB\'dan büyük olamaz!\nMevcut boyut: ' + (file.size / 1024 / 1024).toFixed(2) + 'MB');
                    input.value = '';
                    uploadButton.disabled = true;
                    return false;
                }

                if (!allowedTypes.includes(file.type)) {
                    alert('Desteklenmeyen dosya formatı!\nSadece JPG, PNG, GIF veya WEBP yükleyebilirsiniz.');
                    input.value = '';
                    uploadButton.disabled = true;
                    return false;
                }

                // Önizleme göster
                const reader = new FileReader();
                reader.onload = function(e) {
                    try {
                        if (avatarPreview.tagName === 'DIV') {
                            // Div'i img'ye çevir
                            const img = document.createElement('img');
                            img.id = 'avatarPreview';
                            img.src = e.target.result;
                            img.alt = 'Avatar Preview';
                            img.style.cssText = 'width:120px; height:120px; border-radius:50%; object-fit:cover; border:4px solid #0d6efd; box-shadow:0 4px 16px rgba(13,110,253,0.08);';
                            avatarPreview.parentNode.replaceChild(img, avatarPreview);
                        } else {
                            avatarPreview.src = e.target.result;
                        }
                        console.log('Preview updated successfully');
                    } catch (err) {
                        console.error('Preview update error:', err);
                    }
                };

                reader.onerror = function(err) {
                    console.error('FileReader error:', err);
                    alert('Dosya okuma hatası!');
                };

                reader.readAsDataURL(file);

                // Upload butonunu aktif et
                uploadButton.disabled = false;
                uploadButton.style.opacity = '1';

                console.log('Upload button enabled');

            } else {
                uploadButton.disabled = true;
                uploadButton.style.opacity = '0.6';
                console.log('No file selected, button disabled');
            }
        }

        // Form submit handling
        document.getElementById('avatarForm').addEventListener('submit', function(e) {
            const fileInput = document.getElementById('uploadImg');

            if (e.submitter && e.submitter.name === 'upload_avatar') {
                if (!fileInput.files || fileInput.files.length === 0) {
                    e.preventDefault();
                    alert('Lütfen önce bir dosya seçin!');
                    return false;
                }

                // Submit'e izin ver
                console.log('Form submitted with file:', fileInput.files[0].name);
                return true;
            }
        });

        // Debug için
        console.log('Avatar upload script loaded');
        console.log('Current user avatar path:', '<?= $user['avatar_path'] ?? 'none' ?>');

        // Properties Chart (if applicable)
        <?php if ($user['role'] != 'admin' && ($user_stats['total_properties'] ?? 0) > 0): ?>
        const ctx = document.getElementById('propertiesChart').getContext('2d');
        new Chart(ctx, {
            type: 'doughnut',
            data: {
                labels: ['Onaylı', 'Beklemede', 'Reddedilen'],
                datasets: [{
                    data: [
                        <?= $user_stats['approved_count'] ?? 0 ?>,
                        <?= $user_stats['pending_count'] ?? 0 ?>,
                        <?= $user_stats['rejected_count'] ?? 0 ?>
                    ],
                    backgroundColor: [
                        '#0d6efd',
                        '#ffc107',
                        '#dc3545'
                    ],
                    borderWidth: 2,
                    borderColor: '#fff'
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: {
                        position: 'bottom',
                        labels: {
                            padding: 20,
                            usePointStyle: true
                        }
                    }
                }
            }
        });
        <?php endif; ?>
    </script>
</body>
</html>