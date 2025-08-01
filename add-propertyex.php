<?php
session_start();
include 'db.php';

// Giriş kontrolü - geçici olarak devre dışı
/*
if (!isset($_SESSION['user_id'])) {
    header("Location: index.php");
    exit;
}
*/

// Test için geçici user_id
if (!isset($_SESSION['user_id'])) {
    $_SESSION['user_id'] = 1; // Test için
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini al - test için geçici
$user_query = $conn->prepare("SELECT name," role, can_add_property FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

// Test için varsayılan user data
if (!$user_data) {
    $user_data = ['name' => 'Test User', 'role' => 'admin', 'can_add_property' => 1];
}

/*
if (!$user_data) {
    header("Location: ../logout.php");
    exit;
}

// İlan ekleme yetkisi kontrolü
$can_add_property = ($user_data['role'] === 'admin' || $user_data['can_add_property'] == 1);
if (!$can_add_property) {
    $_SESSION['error'] = "İlan ekleme yetkiniz bulunmamaktadır.";
    header("Location: dashboard.php");
    exit;
}
*/

// CSRF token'ı hazırla - basit sistem
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}
$csrf_token = $_SESSION['csrf_token'];

// Form işleme
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // POST verisi tamamen boşsa
        if (empty($_POST)) {
            throw new Exception("Form verisi alınamadı. Dosya boyutu çok büyük olabilir.");
        }
        
        // CSRF token kontrolü - basit sistem
        $csrf_token = $_POST['csrf_token'] ?? '';
        if ($csrf_token !== $_SESSION['csrf_token']) {
            throw new Exception("Güvenlik hatası: Geçersiz form token. Lütfen sayfayı yenileyin.");
        }
        
        // Form verilerini al
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $type = $_POST['type'] ?? '';
        $category_form = $_POST['category'] ?? '';
        
        // Category mapping
        $category_mapping = [
            'konut' => 'apartment',
            'is_yeri' => 'office', 
            'arsa' => 'land',
            'bina' => 'house',
            'devre_mulk' => 'villa',
            'apartment' => 'apartment',
            'house' => 'house',
            'villa' => 'villa',
            'office' => 'office',
            'shop' => 'shop',
            'warehouse' => 'warehouse',
            'land' => 'land'
        ];
        
        $category = isset($category_mapping[$category_form]) ? $category_mapping[$category_form] : 'apartment';
        $price = floatval(str_replace(['.', ','], ['', '.'], $_POST['price'] ?? '0'));
        
        // Alan bilgisi
        $area_gross = floatval($_POST['area_gross'] ?? 0);
        $area_net = floatval($_POST['area_net'] ?? 0);
        $area = $area_gross > 0 ? $area_gross : $area_net;
        
        // Oda ve banyo sayıları
        $room_count = trim($_POST['room_count'] ?? '');
        $bedrooms = intval(explode('+', $room_count)[0] ?? 0);
        $bathrooms = intval($_POST['bathroom_count'] ?? 0);
        
        // Kat bilgileri
        $floor_location = trim($_POST['floor_location'] ?? '');
        $floor = is_numeric($floor_location) ? intval($floor_location) : 0;
        $building_age = trim($_POST['building_age'] ?? '');
        $year_built = is_numeric($building_age) ? (date('Y') - intval($building_age)) : 0;
        
        // Adres bilgileri
        $city = trim($_POST['city'] ?? '');
        $district = trim($_POST['district'] ?? '');
        $neighborhood = trim($_POST['neighborhood'] ?? '');
        $location_type = trim($_POST['location_type'] ?? '');
        $site_name = trim($_POST['site_name'] ?? '');
        $address_details = trim($_POST['address_details'] ?? '');
        
        // Konum tipine göre adres oluştur
        if ($location_type === 'site' && !empty($site_name)) {
            $address = $site_name . ' Sitesi';
        } else {
            $address = $address_details;
        }
        
        $featured = isset($_POST['is_featured']) && $user_data['role'] === 'admin' ? 1 : 0;
        
        // Diğer özellikler
        $heating = trim($_POST['heating'] ?? '');
        $elevator = trim($_POST['elevator'] ?? '');
        $parking = trim($_POST['parking'] ?? '');
        $furnished = isset($_POST['furnished']) ? 1 : 0;
        $usage_status = trim($_POST['usage_status'] ?? '');
        $dues = floatval($_POST['dues'] ?? 0);
        $credit_eligible = isset($_POST['credit_eligible']) ? 1 : 0;
        $deed_status = trim($_POST['deed_status'] ?? '');
        $exchange = trim($_POST['exchange'] ?? '');
        
        // Validation
        if (empty($title) || empty($description) || empty($type) || empty($category) || $price <= 0) {
            throw new Exception("Lütfen tüm gerekli alanları doldurun.");
        }
        
        // Resim yükleme - DEBUG EKLENDİ
        $images_string = '[]';
        $main_image = '';
        
        echo "<!-- DEBUG: POST ile gelen veriler -->\n";
        echo "<!-- FILES array: " . print_r($_FILES, true) . " -->\n";
        
        if (!empty($_FILES['property_images']['name'][0])) {
            echo "<!-- DEBUG: Resim yükleme başladı -->\n";
            $upload_dir = 'uploads/properties/';  // dashboard klasörü içinde
            if (!is_dir($upload_dir)) {
                mkdir($upload_dir, 0755, true);
                echo "<!-- DEBUG: Upload klasörü oluşturuldu -->\n";
            }
            
            $uploaded_images = [];
            $max_size = 5 * 1024 * 1024; // 5MB
            
            foreach ($_FILES['property_images']['tmp_name'] as $key => $tmp_name) {
                echo "<!-- DEBUG: İşlenen dosya $key: {$_FILES['property_images']['name'][$key]} -->\n";
                if ($_FILES['property_images']['error'][$key] === UPLOAD_ERR_OK) {
                    $file_size = $_FILES['property_images']['size'][$key];
                    $original_name = $_FILES['property_images']['name'][$key];
                    
                    echo "<!-- DEBUG: Dosya boyutu: $file_size bytes -->\n";
                    
                    if ($file_size > $max_size) {
                        echo "<!-- DEBUG: Dosya çok büyük, atlandı -->\n";
                        continue;
                    }
                    
                    $file_extension = strtolower(pathinfo($original_name, PATHINFO_EXTENSION));
                    $unique_name = 'property_' . uniqid() . '_' . time() . '.' . $file_extension;
                    $target_path = $upload_dir . $unique_name;
                    
                    echo "<!-- DEBUG: Hedef yol: $target_path -->\n";
                    
                    if (move_uploaded_file($tmp_name, $target_path)) {
                        $uploaded_images[] = $unique_name;
                        echo "<!-- DEBUG: Başarılı upload: $unique_name -->\n";
                    } else {
                        echo "<!-- DEBUG: Upload başarısız: $unique_name -->\n";
                    }
                } else {
                    echo "<!-- DEBUG: Upload hatası: " . $_FILES['property_images']['error'][$key] . " -->\n";
                }
            }
            
            if (!empty($uploaded_images)) {
                $main_image = $uploaded_images[0];
                $images_string = json_encode($uploaded_images, JSON_UNESCAPED_UNICODE);
                echo "<!-- DEBUG: Final images string: $images_string -->\n";
            } else {
                echo "<!-- DEBUG: Hiç resim yüklenemedi -->\n";
            }
        } else {
            echo "<!-- DEBUG: Hiç resim dosyası gönderilmedi -->\n";
        }
        
        // Database insert
        $listing_type = ($type === 'rent') ? 'Kiralık' : 'Satılık';
        
        $query = "INSERT INTO properties SET 
            user_id = ?,
            title = ?,
            description = ?,
            price = ?,
            type = ?,
            category = ?,
            listing_type = ?,
            area_gross = ?,
            area_net = ?,
            area = ?,
            address = ?,
            city = ?,
            district = ?,
            room_count = ?,
            bedrooms = ?,
            bathrooms = ?,
            floor = ?,
            year_built = ?,
            heating = ?,
            elevator = ?,
            parking = ?,
            furnished = ?,
            featured = ?,
            images = ?,
            main_image = ?,
            status = 'active',
            created_at = NOW()";

        $stmt = $conn->prepare($query);
        $stmt->bind_param("issdsssdddsssiiiisssiiiss", 
            $user_id, $title, $description, $price, $type, $category, 
            $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
            $room_count, $bedrooms, $bathrooms, $floor, $year_built, 
            $heating, $elevator, $parking, $furnished, $featured,
            $images_string, $main_image);

        if ($stmt->execute()) {
            $property_id = $conn->insert_id;
            $_SESSION['success'] = "İlan başarıyla eklendi! (ID: " . $property_id . ")";
            header("Location: dashboard.php");
            exit;
        } else {
            throw new Exception("Database hatası: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>
            <i class='fas fa-exclamation-circle me-2'></i>
            <strong>Hata:</strong> " . $e->getMessage() . "
        </div>";
    }
}

// Türkiye şehirleri
$turkish_cities = [
    'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Amasya', 'Ankara', 'Antalya', 'Artvin',
    'Aydın', 'Balıkesir', 'Bilecik', 'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa',
    'Çanakkale', 'Çankırı', 'Çorum', 'Denizli', 'Diyarbakır', 'Edirne', 'Elazığ', 'Erzincan',
    'Erzurum', 'Eskişehir', 'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkâri', 'Hatay', 'Isparta',
    'İçel (Mersin)', 'İstanbul', 'İzmir', 'Kars', 'Kastamonu', 'Kayseri', 'Kırklareli', 'Kırşehir',
    'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa', 'Kahramanmaraş', 'Mardin', 'Muğla',
    'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Rize', 'Sakarya', 'Samsun', 'Siirt',
    'Sinop', 'Sivas', 'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Şanlıurfa', 'Uşak',
    'Van', 'Yozgat', 'Zonguldak', 'Aksaray', 'Bayburt', 'Karaman', 'Kırıkkale', 'Batman',
    'Şırnak', 'Bartın', 'Ardahan', 'Iğdır', 'Yalova', 'Karabük', 'Kilis', 'Osmaniye', 'Düzce'
];
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Ekle - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    <!-- <link rel="stylesheet" href="includes/dashboard-common.css"> -->
    
    <style>
        /* Dashboard Styles */
        .dashboard-body {
            margin-left: 0px; /* Sidebar kaldırıldığı için 0 yaptık */
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
        
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
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
        }
        
        .btn-secondary-custom:hover {
            background: rgba(255, 255, 255, 0.25);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.2);
        }
        
        /* Wizard Styles */
        .step-indicator {
            display: flex;
            align-items: center;
            gap: 1rem;
            margin-bottom: 2rem;
            padding: 1.5rem;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            border-radius: 15px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
        }
        
        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            text-align: center;
            opacity: 0.5;
            transition: all 0.3s ease;
        }
        
        .step.active {
            opacity: 1;
        }
        
        .step.completed {
            opacity: 1;
            color: #198754;
        }
        
        .step-number {
            width: 50px;
            height: 50px;
            border-radius: 50%;
            border: 3px solid #dee2e6;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            background: white;
            transition: all 0.3s ease;
            font-size: 1.1rem;
        }
        
        .step.active .step-number {
            border-color: #0d6efd;
            background: #0d6efd;
            color: white;
            transform: scale(1.1);
        }
        
        .step.completed .step-number {
            border-color: #198754;
            background: #198754;
            color: white;
        }
        
        .step-title {
            margin-top: 0.7rem;
            font-size: 0.95rem;
            font-weight: 600;
        }
        
        .step-line {
            width: 80px;
            height: 3px;
            background: #dee2e6;
            margin: 0 1rem;
            border-radius: 2px;
        }
        
        .step.completed + .step-line {
            background: #198754;
        }
        
        .category-grid, .transaction-grid, .subcategory-grid {
            display: grid;
            grid-template-columns: repeat(3, 1fr);
            gap: 2rem;
            margin: 3rem auto;
            max-width: 750px;
        }
        
        .transaction-grid {
            grid-template-columns: repeat(4, 1fr);
            max-width: 900px;
        }
        
        .category-item, .transaction-item, .subcategory-item {
            border: 3px solid #dee2e6;
            border-radius: 20px;
            padding: 2.5rem 1.5rem;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
            background: white;
            min-height: 180px;
            display: flex;
            flex-direction: column;
            justify-content: center;
            align-items: center;
            box-shadow: 0 2px 10px rgba(0,0,0,0.08);
            user-select: none;
            position: relative;
            z-index: 100000 !important;
            pointer-events: auto !important;
        }
        
        /* Force override for category buttons */
        button.category-item {
            pointer-events: auto !important;
            z-index: 999999 !important;
            position: relative !important;
            background: white !important;
            border: 3px solid #dee2e6 !important;
        }
        
        /* Override any potential blocking elements */
        * {
            pointer-events: auto !important;
        }
        
        .category-grid * {
            pointer-events: auto !important;
        }
        
        /* Simple category button styles */
        .category-btn {
            transition: all 0.3s ease;
            min-height: 150px;
        }
        
        .category-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.3);
        }
        
        .category-btn.selected {
            background-color: #0d6efd !important;
            border-color: #0d6efd !important;
            color: white !important;
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(13, 110, 253, 0.4);
        }
        
        .category-item:hover, .transaction-item:hover, .subcategory-item:hover {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #f8f9ff 0%, #e3f2fd 100%);
            transform: translateY(-3px) scale(1.02);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.25);
        }
        
        .category-item.selected, .transaction-item.selected, .subcategory-item.selected {
            border-color: #0d6efd;
            background: linear-gradient(135deg, #0d6efd 0%, #0b5ed7 100%);
            color: white;
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }
        
        .category-item i, .transaction-item i, .subcategory-item i {
            font-size: 3.5rem;
            margin-bottom: 1.5rem;
            display: block;
            opacity: 0.8;
        }
        
        .category-item span, .transaction-item span, .subcategory-item span {
            font-weight: 600;
            font-size: 1.2rem;
            line-height: 1.3;
        }
        
        .wizard-step {
            min-height: 600px;
            display: flex;
            align-items: center;
            justify-content: center;
            padding: 3rem 0;
        }
        
        /* Featured Property Checkbox Styles */
        .featured-checkbox {
            transform: scale(1.4);
            accent-color: #ffc107;
            margin-right: 0.75rem;
        }
        
        .featured-label {
            cursor: pointer;
            padding: 1rem;
            border-radius: 12px;
            transition: all 0.3s ease;
            background: linear-gradient(135deg, #fff9e6 0%, #fff3cd 100%);
            border: 2px solid #ffc107;
            display: block;
            margin-top: 0.5rem;
        }
        
        .featured-label:hover {
            background: linear-gradient(135deg, #ffc107 0%, #ffb800 100%);
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.3);
        }
        
        .featured-checkbox:checked + .featured-label {
            background: linear-gradient(135deg, #28a745 0%, #20c997 100%);
            color: white;
            border-color: #28a745;
            box-shadow: 0 4px 15px rgba(40, 167, 69, 0.4);
        }
        
        .card.border-warning {
            box-shadow: 0 4px 15px rgba(255, 193, 7, 0.2);
            transition: all 0.3s ease;
        }
        
        .card.border-warning:hover {
            transform: translateY(-2px);
            box-shadow: 0 6px 20px rgba(255, 193, 7, 0.3);
        }
        
        /* Form Styles */
        .form-control, .form-select {
            border: 1px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
            margin-bottom: 15px;
        }
        
        .form-control:focus, .form-select:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
        
        .form-label {
            font-weight: 600;
            color: #495057;
            margin-bottom: 8px;
        }
        
        .alert-success {
            background: linear-gradient(135deg, #d1edff 0%, #a8e6cf 100%);
            border: none;
            border-radius: 15px;
            padding: 1.5rem;
            margin-bottom: 2rem;
        }
        
        /* Photo Upload Styles */
        .upload-area {
            border: 3px dashed #dee2e6;
            border-radius: 15px;
            padding: 50px 20px;
            text-align: center;
            background: white;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover,
        .upload-area.dragover {
            border-color: #0d6efd;
            background: rgba(13, 110, 253, 0.05);
            transform: translateY(-2px);
        }

        .upload-icon {
            font-size: 3rem;
            color: #0d6efd;
            margin-bottom: 1rem;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(150px, 1fr));
            gap: 15px;
            margin-top: 20px;
        }

        .photo-item {
            position: relative;
            aspect-ratio: 1;
            border-radius: 10px;
            overflow: hidden;
            background: white;
            border: 2px solid #e9ecef;
            transition: all 0.3s ease;
            animation: photoFadeIn 0.4s ease-out;
            cursor: grab;
        }

        .photo-item:hover {
            transform: translateY(-3px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.1);
        }

        .photo-item.dragging {
            opacity: 0.5;
            transform: rotate(5deg) scale(0.95);
        }

        .photo-item.main-photo {
            border-color: #28a745;
            box-shadow: 0 0 0 3px rgba(40, 167, 69, 0.2);
        }

        .photo-item.main-photo::before {
            content: "ANA";
            position: absolute;
            top: 5px;
            left: 5px;
            background: #28a745;
            color: white;
            padding: 2px 8px;
            border-radius: 5px;
            font-size: 10px;
            font-weight: bold;
            z-index: 2;
        }

        .photo-item img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }

        .photo-overlay {
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0,0,0,0.8);
            display: flex;
            align-items: center;
            justify-content: center;
            opacity: 0;
            transition: opacity 0.3s ease;
        }

        .photo-item:hover .photo-overlay {
            opacity: 1;
        }

        .photo-actions {
            display: flex;
            gap: 10px;
        }

        .photo-action-btn {
            background: white;
            border: none;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
            font-size: 14px;
        }

        .photo-action-btn:hover {
            transform: scale(1.1);
        }

        .photo-action-btn.delete {
            background: #dc3545;
            color: white;
        }

        .photo-action-btn.star {
            background: #28a745;
            color: white;
        }

        .photo-action-btn.star.active {
            background: #ffd700;
            color: #000;
        }

        .photo-number {
            position: absolute;
            bottom: 5px;
            right: 5px;
            background: rgba(0,0,0,0.8);
            color: white;
            padding: 3px 8px;
            border-radius: 10px;
            font-size: 12px;
            font-weight: bold;
        }

        .photo-description {
            position: absolute;
            bottom: 0;
            left: 0;
            right: 0;
            background: rgba(0,0,0,0.9);
            color: white;
            padding: 5px;
            font-size: 11px;
            transform: translateY(100%);
            transition: transform 0.3s ease;
        }

        .photo-item:hover .photo-description {
            transform: translateY(0);
        }

        @keyframes photoFadeIn {
            from {
                opacity: 0;
                transform: translateY(10px) scale(0.95);
            }
            to {
                opacity: 1;
                transform: translateY(0) scale(1);
            }
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .mobile-header {
                display: flex;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .category-grid, .transaction-grid, .subcategory-grid {
                grid-template-columns: repeat(2, 1fr);
                gap: 1.5rem;
            }
            
            .photos-grid {
                grid-template-columns: repeat(auto-fill, minmax(120px, 1fr));
                gap: 10px;
            }
        }
        
        @media (max-width: 480px) {
            .category-grid, .transaction-grid, .subcategory-grid {
                grid-template-columns: 1fr;
                gap: 1.5rem;
            }
        }

        /* Fotoğraf Upload Stilleri */
        .upload-area {
            border: 3px dashed #0d6efd;
            border-radius: 15px;
            padding: 60px 30px;
            text-align: center;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.05), rgba(13, 110, 253, 0.1));
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .upload-area:hover {
            border-color: #0a58ca;
            background: linear-gradient(135deg, rgba(13, 110, 253, 0.1), rgba(13, 110, 253, 0.15));
            transform: translateY(-2px);
        }

        .upload-area.drag-over {
            border-color: #198754;
            background: linear-gradient(135deg, rgba(25, 135, 84, 0.1), rgba(25, 135, 84, 0.15));
            transform: scale(1.02);
        }

        .upload-icon {
            font-size: 48px;
            color: #0d6efd;
            margin-bottom: 20px;
            opacity: 0.8;
        }

        .upload-content h5 {
            color: #0d6efd;
            font-weight: 600;
            margin-bottom: 10px;
        }

        .photo-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }

        .photo-item {
            position: relative;
            background: white;
            border-radius: 15px;
            padding: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
            transition: all 0.3s ease;
            cursor: move;
        }

        .photo-item:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 25px rgba(0,0,0,0.15);
        }

        .photo-item.main-photo {
            border: 3px solid #ffc107;
            background: linear-gradient(135deg, rgba(255, 193, 7, 0.1), rgba(255, 193, 7, 0.05));
        }

        .photo-preview {
            width: 100%;
            height: 150px;
            object-fit: cover;
            border-radius: 10px;
            margin-bottom: 15px;
        }

        .photo-controls {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 10px;
        }

        .photo-badges {
            display: flex;
            gap: 5px;
        }

        .main-badge {
            background: #ffc107;
            color: #000;
            font-size: 10px;
            padding: 3px 8px;
            border-radius: 12px;
            font-weight: 600;
        }

        .photo-actions {
            display: flex;
            gap: 5px;
        }

        .photo-btn {
            width: 30px;
            height: 30px;
            border: none;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 12px;
            cursor: pointer;
            transition: all 0.2s ease;
        }

        .photo-btn.btn-main {
            background: #ffc107;
            color: #000;
        }

        .photo-btn.btn-delete {
            background: #dc3545;
            color: white;
        }

        .photo-btn:hover {
            transform: scale(1.1);
        }

        .photo-description {
            width: 100%;
            border: 1px solid #dee2e6;
            border-radius: 8px;
            padding: 8px 12px;
            font-size: 12px;
            resize: none;
        }

        .photo-description:focus {
            border-color: #0d6efd;
            box-shadow: 0 0 0 0.2rem rgba(13, 110, 253, 0.25);
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Include Admin Sidebar -->
    <?php 
    $current_page = 'add-property';
    $user_name = $user_data['name'];
    // include 'includes/sidebar-admin.php'; // Geçici olarak devre dışı
    ?>
    
    <!-- Basit Navigation -->
    <nav class="navbar navbar-expand-lg navbar-dark bg-primary">
        <div class="container-fluid">
            <a class="navbar-brand" href="porfoy.html">Emlak Sistemi</a>
            <div class="navbar-nav">
                <a class="nav-link" href="portfoy.php">İlanlar</a>
                <a class="nav-link active" href="add-propertyex.php">Yeni İlan</a>
                <a class="nav-link" href="index.php">Çıkış</a>
            </div>
        </div>
    </nav>
    
    <!-- Mobile Overlay -->
    <div class="mobile-overlay" style="display: none !important; pointer-events: none !important;"></div>
    
    <!-- Dashboard Body -->
    <div class="dashboard-body" style="margin-left: 0; padding: 20px;">`
        <!-- Mobile Header -->
        <div class="mobile-header d-block d-md-none">
            <button class="mobile-menu-btn" onclick="toggleSidebar()">
                <i class="fas fa-bars"></i>
            </button>
            <h5 class="mobile-title">Yeni İlan Ekle</h5>
            <a href="../logout.php" class="mobile-logout">
                <i class="fas fa-sign-out-alt"></i>
            </a>
        </div>

        <div class="main-content">
            <!-- Welcome Banner -->
            <div class="welcome-banner">
                <h2 class="welcome-title">
                    <i class="fas fa-plus-circle me-3"></i>Yeni İlan Ekle
                </h2>
                <p class="welcome-subtitle">Emlak ilanınızı detaylı bilgilerle oluşturun</p>
                <div class="d-flex gap-2 mt-3">
                    <a href="dashboard.php" class="btn-secondary-custom">
                        <i class="fas fa-arrow-left me-2"></i>Panele Dön
                    </a>
                </div>
            </div>
            
            <!-- Success/Error Messages -->
            <?php if (isset($_SESSION['error'])): ?>
                <div class="alert alert-danger alert-dismissible fade show" role="alert">
                    <i class="fas fa-exclamation-circle me-2"></i>
                    <?= $_SESSION['error']; unset($_SESSION['error']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>

            <?php if (isset($_SESSION['success'])): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <i class="fas fa-check-circle me-2"></i>
                    <?= $_SESSION['success']; unset($_SESSION['success']); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                </div>
            <?php endif; ?>
            
            <?php echo $message; ?>

            <form method="POST" enctype="multipart/form-data" id="propertyForm">
                <input type="hidden" name="csrf_token" value="<?php echo htmlspecialchars($csrf_token); ?>">
                
                <!-- Step Indicator -->
                <div class="row mb-4">
                    <div class="col-12">
                        <div class="d-flex align-items-center justify-content-center">
                            <div class="step-indicator">
                                <div class="step active" id="step-1">
                                    <span class="step-number">1</span>
                                    <span class="step-title">Kategori</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-2">
                                    <span class="step-number">2</span>
                                    <span class="step-title">İşlem Türü</span>
                                </div>
                                <div class="step-line"></div>
                                <div class="step" id="step-3">
                                    <span class="step-number">3</span>
                                    <span class="step-title">Alt Kategori</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 1: Category Selection -->
                <div class="wizard-step" id="wizard-step-1">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-house-door"></i> Kategori Seçimi</h4>
                                    <p class="text-muted">Eklemek istediğiniz emlak kategorisini seçin</p>
                                    
                                    <!-- BASIT TEST BUTONLARI -->
                                    <div class="alert alert-warning">
                                        <strong>Test Butonları:</strong>
                                        <button onclick="alert('Bu çalışıyor!')" class="btn btn-success ms-2">Test 1</button>
                                        <button onclick="console.log('Console test'); alert('Console test tamamlandı!');" class="btn btn-info ms-2">Test 2</button>
                                        <button onclick="document.getElementById('category').value='test'; alert('Hidden input test: ' + document.getElementById('category').value);" class="btn btn-warning ms-2">Test 3</button>
                                        <button onclick="document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; alert('Manuel adım değişimi yapıldı!');" class="btn btn-danger ms-2">Adım Test</button>
                                    </div>
                                </div>
                                
                                <!-- Simple Category Buttons -->
                                <div class="row justify-content-center">
                                    <div class="col-md-10">
                                        <div class="row g-3">
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='konut'; alert('KONUT SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; alert('İşlem türleri yükleniyor...'); loadTransactionTypes('konut');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-house fs-1 mb-3 d-block"></i>
                                                    <h5>Konut</h5>
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='is_yeri'; alert('İŞ YERİ SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; loadTransactionTypes('is_yeri');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-building fs-1 mb-3 d-block"></i>
                                                    <h5>İş Yeri</h5>
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='bina'; alert('BİNA SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; loadTransactionTypes('bina');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-buildings fs-1 mb-3 d-block"></i>
                                                    <h5>Bina</h5>
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='arsa'; alert('ARSA SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; loadTransactionTypes('arsa');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-geo-alt fs-1 mb-3 d-block"></i>
                                                    <h5>Arsa</h5>
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='devre_mulk'; alert('DEVRE MÜLK SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; loadTransactionTypes('devre_mulk');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-calendar-check fs-1 mb-3 d-block"></i>
                                                    <h5>Devre Mülk</h5>
                                                </button>
                                            </div>
                                            <div class="col-md-4">
                                                <button type="button" onclick="document.getElementById('category').value='turistik_tesis'; alert('TURİSTİK TESİS SEÇİLDİ!'); document.getElementById('wizard-step-1').style.display='none'; document.getElementById('wizard-step-2').style.display='block'; loadTransactionTypes('turistik_tesis');" class="btn btn-outline-primary w-100 p-4 category-btn">
                                                    <i class="bi bi-compass fs-1 mb-3 d-block"></i>
                                                    <h5>Turistik Tesis</h5>
                                                </button>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                                
                                <input type="hidden" id="category" name="category" required>
                            </div>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 2: Transaction Type -->
                <div class="wizard-step" id="wizard-step-2" style="display: none;">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-arrow-left-right"></i> İşlem Türü Seçimi</h4>
                                    <p class="text-muted">Emlak için işlem türünü seçin</p>
                                    
                                    <!-- Debug butonu -->
                                    <button onclick="alert('2. adım yüklendi!'); loadTransactionTypes('konut');" class="btn btn-warning mb-3">Manuel İşlem Türleri Yükle</button>
                                </div>
                                <div class="transaction-grid" id="transaction-options">
                                    <!-- Transaction types will be loaded dynamically -->
                                    <div class="alert alert-info">
                                        İşlem türleri yükleniyor... Eğer görünmüyorsa, yukarıdaki "Manuel İşlem Türleri Yükle" butonuna tıklayın.
                                    </div>
                                    
                                    <!-- Statik işlem türleri - fallback -->
                                    <div class="row g-3 justify-content-center" id="static-transactions">
                                        <div class="col-md-4">
                                            <button type="button" onclick="document.getElementById('type').value='sale'; alert('SATILIK SEÇİLDİ!'); document.getElementById('wizard-step-2').style.display='none'; document.getElementById('main-form').style.display='block';" class="btn btn-outline-success w-100 p-4">
                                                <i class="bi bi-currency-dollar fs-1 mb-3 d-block"></i>
                                                <h5>Satılık</h5>
                                            </button>
                                        </div>
                                        <div class="col-md-4">
                                            <button type="button" onclick="document.getElementById('type').value='rent'; alert('KİRALIK SEÇİLDİ!'); document.getElementById('wizard-step-2').style.display='none'; document.getElementById('main-form').style.display='block';" class="btn btn-outline-success w-100 p-4">
                                                <i class="bi bi-house fs-1 mb-3 d-block"></i>
                                                <h5>Kiralık</h5>
                                            </button>
                                        </div>
                                    </div>
                                </div>
                                <input type="hidden" id="type" name="type" required>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Step 3: Subcategory -->
                <div class="wizard-step" id="wizard-step-3" style="display: none;">
                    <div class="container-fluid">
                        <div class="row justify-content-center">
                            <div class="col-12">
                                <div class="text-center mb-5">
                                    <h4><i class="bi bi-list-ul"></i> Alt Kategori Seçimi</h4>
                                    <p class="text-muted">Emlak alt kategorisini seçin</p>
                                </div>
                                <div class="subcategory-grid" id="subcategory-options">
                                    <!-- Subcategories will be loaded dynamically -->
                                </div>
                                <input type="hidden" id="subcategory" name="subcategory">
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Navigation Buttons -->
                <div class="row mt-4">
                    <div class="col-12">
                        <div class="d-flex justify-content-between">
                            <button type="button" class="btn btn-secondary" id="prev-step" style="display: none;">
                                <i class="bi bi-arrow-left"></i> Geri
                            </button>
                            <div class="flex-fill"></div>
                            <button type="button" class="btn btn-primary" id="next-step" disabled>
                                İleri <i class="bi bi-arrow-right"></i>
                            </button>
                            <button type="button" class="btn btn-success" id="continue-form" style="display: none;">
                                Devam Et <i class="bi bi-check-circle"></i>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Main Form -->
                <div id="main-form" style="display: none;">
                    <!-- Selection Summary -->
                    <div class="alert alert-success mb-4">
                        <h5><i class="bi bi-check-circle-fill"></i> Kategori Seçimi Tamamlandı</h5>
                        <div id="selection-summary"></div>
                    </div>
                    
                    <!-- Property Title -->
                    <div class="mb-4">
                        <label for="title" class="form-label">İlan Başlığı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control form-control-lg" id="title" name="title" required 
                               placeholder="Örn: ÇINAROĞLU İNŞAATTAN FIRSAT SİTE İÇİRESİNDE 3+1 SATILIK DAİRE"
                               value="<?php echo htmlspecialchars($_POST['title'] ?? ''); ?>">
                        
                        <!-- Title Preview -->
                        <div class="mt-3 p-3" style="background: #f8f9fa; border-radius: 8px;">
                            <h6 class="mb-2">İlan Ön İzleme</h6>
                            <div id="title-preview" class="border p-2 bg-white">
                                <span class="fw-bold" style="color: #0d6efd;">İlan başlığınız burada görünecek...</span>
                            </div>
                        </div>
                    </div>

                    <!-- Description -->
                    <div class="mb-4">
                        <label for="description" class="form-label">Açıklama <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="description" name="description" rows="6" required 
                                  placeholder="Mülkünüzün özelliklerini detaylı şekilde açıklayın..."><?php echo htmlspecialchars($_POST['description'] ?? ''); ?></textarea>
                    </div>

                    <!-- Price Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2"><i class="bi bi-currency-exchange"></i> Fiyat Bilgileri</h6>
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="price" class="form-label">Fiyat <span class="text-danger">*</span></label>
                            <div class="input-group">
                                <input type="text" class="form-control form-control-lg" id="price" name="price" required 
                                       placeholder="6.400.000" style="font-size: 1.2rem; font-weight: 600;"
                                       value="<?php echo htmlspecialchars($_POST['price'] ?? ''); ?>">
                                <span class="input-group-text">TL</span>
                            </div>
                        </div>
                    </div>

                    <!-- Area Information -->
                    <div class="row">
                        <div class="col-md-6 mb-3">
                            <label for="area_gross" class="form-label">m² (Brüt)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="area_gross" name="area_gross" 
                                       placeholder="120" min="1" max="10000"
                                       value="<?php echo htmlspecialchars($_POST['area_gross'] ?? ''); ?>">
                                <span class="input-group-text">m²</span>
                            </div>
                        </div>
                        <div class="col-md-6 mb-3">
                            <label for="area_net" class="form-label">m² (Net)</label>
                            <div class="input-group">
                                <input type="number" class="form-control" id="area_net" name="area_net" 
                                       placeholder="95" min="1" max="10000"
                                       value="<?php echo htmlspecialchars($_POST['area_net'] ?? ''); ?>">
                                <span class="input-group-text">m²</span>
                            </div>
                        </div>
                    </div>

                    <!-- Basic Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-info-circle"></i> Temel Bilgiler
                    </h6>
                    <div class="row">
                        <!-- Room Count -->
                        <div class="col-md-3 mb-3">
                            <label for="room_count" class="form-label">Oda Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="room_count" name="room_count" required>
                                <option value="">Seçiniz</option>
                                <option value="1+0">1+0</option>
                                <option value="1+1">1+1</option>
                                <option value="2+1">2+1</option>
                                <option value="3+1">3+1</option>
                                <option value="4+1">4+1</option>
                                <option value="5+1">5+1</option>
                                <option value="6+1">6+1</option>
                                <option value="7+1">7+1</option>
                                <option value="8+1">8+1</option>
                                <option value="9+1">9+1</option>
                                <option value="10+1">10+1</option>
                            </select>
                        </div>

                        <!-- Building Age -->
                        <div class="col-md-3 mb-3">
                            <label for="building_age" class="form-label">Bina Yaşı <span class="text-danger">*</span></label>
                            <select class="form-select" id="building_age" name="building_age" required>
                                <option value="">Seçiniz</option>
                                <option value="0">0 (Sıfır Bina)</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6-10">6-10</option>
                                <option value="11-15">11-15</option>
                                <option value="16-20">16-20</option>
                                <option value="21-25">21-25</option>
                                <option value="26-30">26-30</option>
                                <option value="31+">31 ve üzeri</option>
                            </select>
                        </div>

                        <!-- Floor -->
                        <div class="col-md-3 mb-3">
                            <label for="floor_location" class="form-label">Bulunduğu Kat <span class="text-danger">*</span></label>
                            <select class="form-select" id="floor_location" name="floor_location" required>
                                <option value="">Seçiniz</option>
                                <option value="Bodrum Kat">Bodrum Kat</option>
                                <option value="Zemin Kat">Zemin Kat</option>
                                <option value="Bahçe Katı">Bahçe Katı</option>
                                <option value="Yüksek Zemin">Yüksek Zemin</option>
                                <option value="Asma Kat">Asma Kat</option>
                                <option value="1">1. Kat</option>
                                <option value="2">2. Kat</option>
                                <option value="3">3. Kat</option>
                                <option value="4">4. Kat</option>
                                <option value="5">5. Kat</option>
                                <option value="6">6. Kat</option>
                                <option value="7">7. Kat</option>
                                <option value="8">8. Kat</option>
                                <option value="9">9. Kat</option>
                                <option value="10">10. Kat</option>
                                <option value="11-15">11-15. Kat</option>
                                <option value="16-20">16-20. Kat</option>
                                <option value="21+">21 ve üzeri</option>
                            </select>
                        </div>

                        <!-- Heating -->
                        <div class="col-md-3 mb-3">
                            <label for="heating" class="form-label">Isıtma <span class="text-danger">*</span></label>
                            <select class="form-select" id="heating" name="heating" required>
                                <option value="">Seçiniz</option>
                                <option value="Yok">Yok</option>
                                <option value="Soba">Soba</option>
                                <option value="Doğalgaz Sobası">Doğalgaz Sobası</option>
                                <option value="Kat Kaloriferi">Kat Kaloriferi</option>
                                <option value="Merkezi Sistem">Merkezi Sistem</option>
                                <option value="Kombi (Doğalgaz)">Kombi (Doğalgaz)</option>
                                <option value="Kombi (Elektrik)">Kombi (Elektrik)</option>
                                <option value="Yerden Isıtma">Yerden Isıtma</option>
                                <option value="Klima">Klima</option>
                                <option value="Fancoil Ünitesi">Fancoil Ünitesi</option>
                                <option value="Güneş Enerjisi">Güneş Enerjisi</option>
                                <option value="Jeotermal">Jeotermal</option>
                                <option value="Şömine">Şömine</option>
                            </select>
                        </div>
                    </div>

                    <!-- Second Row -->
                    <div class="row">
                        <!-- Bathroom Count -->
                        <div class="col-md-3 mb-3">
                            <label for="bathroom_count" class="form-label">Banyo Sayısı <span class="text-danger">*</span></label>
                            <select class="form-select" id="bathroom_count" name="bathroom_count" required>
                                <option value="">Seçiniz</option>
                                <option value="Yok">Yok</option>
                                <option value="1">1</option>
                                <option value="2">2</option>
                                <option value="3">3</option>
                                <option value="4">4</option>
                                <option value="5">5</option>
                                <option value="6">6</option>
                                <option value="7">7</option>
                                <option value="8">8</option>
                                <option value="9">9</option>
                                <option value="10+">10 ve üzeri</option>
                            </select>
                        </div>

                        <!-- Elevator -->
                        <div class="col-md-3 mb-3">
                            <label for="elevator" class="form-label">Asansör <span class="text-danger">*</span></label>
                            <select class="form-select" id="elevator" name="elevator" required>
                                <option value="">Seçiniz</option>
                                <option value="Var">Var</option>
                                <option value="Yok">Yok</option>
                            </select>
                        </div>

                        <!-- Parking -->
                        <div class="col-md-3 mb-3">
                            <label for="parking" class="form-label">Otopark <span class="text-danger">*</span></label>
                            <select class="form-select" id="parking" name="parking" required>
                                <option value="">Seçiniz</option>
                                <option value="Yok">Yok</option>
                                <option value="Açık Otopark">Açık Otopark</option>
                                <option value="Kapalı Otopark">Kapalı Otopark</option>
                                <option value="Yarı Açık Otopark">Yarı Açık Otopark</option>
                                <option value="1 Araç">1 Araç</option>
                                <option value="2 Araç">2 Araç</option>
                                <option value="3+ Araç">3+ Araç</option>
                            </select>
                        </div>

                        <!-- Usage Status -->
                        <div class="col-md-3 mb-3">
                            <label for="usage_status" class="form-label">Kullanım Durumu <span class="text-danger">*</span></label>
                            <select class="form-select" id="usage_status" name="usage_status" required>
                                <option value="">Seçiniz</option>
                                <option value="Boş">Boş</option>
                                <option value="Kiracılı">Kiracılı</option>
                                <option value="Malik Kullanımında">Malik Kullanımında</option>
                                <option value="Yatırım Amaçlı">Yatırım Amaçlı</option>
                            </select>
                        </div>
                    </div>

                    <!-- Third Row -->
                    <div class="row">
                        <!-- Dues -->
                        <div class="col-md-3 mb-3">
                            <label for="dues" class="form-label">Aidat (TL)</label>
                            <input type="number" class="form-control" id="dues" name="dues" 
                                   placeholder="0" min="0" max="999999"
                                   value="<?php echo htmlspecialchars($_POST['dues'] ?? ''); ?>">
                        </div>

                        <!-- Furnished -->
                        <div class="col-md-3 mb-3">
                            <label for="furnished" class="form-label">Eşyalı</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="furnished" name="furnished">
                                <label class="form-check-label" for="furnished">
                                    Evet, eşyalı
                                </label>
                            </div>
                        </div>

                        <!-- Credit Eligible -->
                        <div class="col-md-3 mb-3">
                            <label for="credit_eligible" class="form-label">Krediye Uygun</label>
                            <div class="form-check mt-2">
                                <input class="form-check-input" type="checkbox" value="1" id="credit_eligible" name="credit_eligible">
                                <label class="form-check-label" for="credit_eligible">
                                    Evet, krediye uygun
                                </label>
                            </div>
                        </div>

                        <!-- Featured Property -->
                        <?php if ($user_data['role'] === 'admin'): ?>
                        <div class="col-md-6 mb-3">
                            <div class="card border-warning bg-light">
                                <div class="card-header bg-warning text-dark">
                                    <h6 class="mb-0">
                                        <i class="fas fa-crown me-2"></i>
                                        <strong>Admin Özel Özellik</strong>
                                        <span class="badge bg-dark ms-2">PREMIUM</span>
                                    </h6>
                                </div>
                                <div class="card-body">
                                    <label for="is_featured" class="form-label fw-bold">
                                        <i class="fas fa-star text-warning me-1"></i>Anasayfada Öne Çıkart
                                    </label>
                                    <div class="form-check mt-2">
                                        <input class="form-check-input featured-checkbox" type="checkbox" value="1" id="is_featured" name="is_featured"
                                               <?php echo (isset($_POST['is_featured']) && $_POST['is_featured']) ? 'checked' : ''; ?>>
                                        <label class="form-check-label featured-label" for="is_featured">
                                            <span class="text-primary fw-bold">
                                                <i class="fas fa-fire text-danger me-1"></i>Evet, anasayfada öne çıkart
                                            </span>
                                            <small class="d-block text-muted mt-1">
                                                <i class="fas fa-info-circle me-1"></i>
                                                Bu ilan anasayfada öne çıkarılacak ve daha fazla görüntülenecek
                                            </small>
                                        </label>
                                    </div>
                                </div>
                            </div>
                        </div>
                        <?php endif; ?>

                        <!-- Deed Status -->
                        <div class="col-md-3 mb-3">
                            <label for="deed_status" class="form-label">Tapu Durumu <span class="text-danger">*</span></label>
                            <select class="form-select" id="deed_status" name="deed_status" required>
                                <option value="">Seçiniz</option>
                                <option value="Kat Mülkiyetli">Kat Mülkiyetli</option>
                                <option value="Kat İrtifaklı">Kat İrtifaklı</option>
                                <option value="Arsa Paylı">Arsa Paylı</option>
                                <option value="Müstakil Tapulu">Müstakil Tapulu</option>
                                <option value="Hisseli">Hisseli</option>
                                <option value="Diğer">Diğer</option>
                            </select>
                        </div>
                    </div>

                    <!-- Fourth Row -->
                    <div class="row">
                        <!-- Exchange -->
                        <div class="col-md-4 mb-3">
                            <label for="exchange" class="form-label">Takaslı <span class="text-danger">*</span></label>
                            <select class="form-select" id="exchange" name="exchange" required>
                                <option value="">Seçiniz</option>
                                <option value="Evet">Evet</option>
                                <option value="Hayır">Hayır</option>
                            </select>
                        </div>
                    </div>

                    <!-- Address Information -->
                    <h6 class="text-primary mb-3 border-bottom pb-2 mt-4">
                        <i class="bi bi-geo-alt"></i> Adres Bilgileri
                    </h6>

                    <div class="row">
                        <!-- City -->
                        <div class="col-md-4 mb-3">
                            <label for="city" class="form-label">İl <span class="text-danger">*</span></label>
                            <select class="form-select" id="city" name="city" required>
                                <option value="">Seçiniz</option>
                                <?php foreach($turkish_cities as $city): ?>
                                    <option value="<?php echo htmlspecialchars($city); ?>" 
                                            <?php echo (isset($_POST['city']) && $_POST['city'] === $city) ? 'selected' : ''; ?>>
                                        <?php echo htmlspecialchars($city); ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>

                        <!-- District -->
                        <div class="col-md-4 mb-3">
                            <label for="district" class="form-label">İlçe <span class="text-danger">*</span></label>
                            <select class="form-select" id="district" name="district" required>
                                <option value="">Seçiniz</option>
                            </select>
                        </div>

                        <!-- Neighborhood -->
                        <div class="col-md-4 mb-3">
                            <label for="neighborhood" class="form-label">Mahalle</label>
                            <select class="form-select" id="neighborhood" name="neighborhood">
                                <option value="">Seçiniz</option>
                            </select>
                        </div>
                    </div>

                    <!-- Location Type -->
                    <div class="mb-4">
                        <label class="form-label">Konum Tipi <span class="text-danger">*</span></label>
                        <div class="row">
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="site" id="location_site" required>
                                    <label class="form-check-label" for="location_site">
                                        <i class="bi bi-buildings"></i> Site İçerisinde
                                    </label>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="form-check">
                                    <input class="form-check-input" type="radio" name="location_type" value="standalone" id="location_standalone" required>
                                    <label class="form-check-label" for="location_standalone">
                                        <i class="bi bi-house"></i> Müstakil/Site Dışı
                                    </label>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Site Name -->
                    <div class="mb-4" id="site-name-section" style="display: none;">
                        <label for="site_name" class="form-label">Site Adı <span class="text-danger">*</span></label>
                        <input type="text" class="form-control" id="site_name" name="site_name" 
                               placeholder="Örn: Bahçeşehir Premium Sitesi"
                               value="<?php echo htmlspecialchars($_POST['site_name'] ?? ''); ?>">
                    </div>

                    <!-- Address Details -->
                    <div class="mb-4" id="address-details-section" style="display: none;">
                        <label for="address_details" class="form-label">Adres Detayları <span class="text-danger">*</span></label>
                        <textarea class="form-control" id="address_details" name="address_details" rows="3" 
                                  placeholder="Sokak, apartman adı, kapı numarası vb. detayları yazınız..."><?php echo htmlspecialchars($_POST['address_details'] ?? ''); ?></textarea>
                    </div>

                    <!-- Gelişmiş Fotoğraf Yükleme Sistemi -->
                    <div class="mt-5">
                        <h6 class="text-primary mb-3 border-bottom pb-2">
                            <i class="fas fa-camera"></i> Fotoğraf Yükleme
                            <small class="text-muted ms-2">Sürükle-bırak destekli, çoklu seçim</small>
                        </h6>

                        <!-- Upload Area -->
                        <div class="upload-area" id="uploadArea">
                            <div class="upload-content">
                                <i class="fas fa-cloud-upload-alt upload-icon"></i>
                                <h5>Fotoğrafları Buraya Sürükleyin</h5>
                                <p class="text-muted">veya dosya seçmek için tıklayın</p>
                                <input type="file" 
                                       id="photoInput" 
                                       name="property_images[]" 
                                       multiple 
                                       accept="image/jpeg,image/jpg,image/png,image/webp"
                                       class="d-none">
                                <button type="button" class="btn btn-primary" onclick="document.getElementById('photoInput').click()">
                                    <i class="fas fa-plus me-2"></i>Fotoğraf Seç
                                </button>
                            </div>
                            <div class="upload-info mt-3">
                                <small class="text-muted">
                                    <i class="fas fa-info-circle me-1"></i>
                                    Desteklenen formatlar: JPG, PNG, WEBP • Maksimum boyut: 5MB/fotoğraf • Sınırsız fotoğraf
                                </small>
                            </div>
                        </div>

                        <!-- Photo Preview ve Management -->
                        <div id="photoManagement" class="mt-4" style="display: none;">
                            <div class="d-flex justify-content-between align-items-center mb-3">
                                <h6 class="text-success mb-0">
                                    <i class="fas fa-images me-2"></i>
                                    Seçilen Fotoğraflar 
                                    <span id="photoCount" class="badge bg-success">0</span>
                                </h6>
                                <div class="photo-actions">
                                    <button type="button" class="btn btn-sm btn-outline-primary" onclick="addMorePhotos()">
                                        <i class="fas fa-plus me-1"></i>Daha Fazla Ekle
                                    </button>
                                    <button type="button" class="btn btn-sm btn-outline-danger" onclick="clearAllPhotos()">
                                        <i class="fas fa-trash me-1"></i>Tümünü Temizle
                                    </button>
                                </div>
                            </div>

                            <!-- Sortable Photo Grid -->
                            <div id="photoGrid" class="photo-grid">
                                <!-- Photos will be added here dynamically -->
                            </div>

                            <!-- Info -->
                            <div class="photo-info mt-3">
                                <div class="alert alert-info">
                                    <i class="fas fa-lightbulb me-2"></i>
                                    <strong>Fotoğraf İpuçları:</strong>
                                    <ul class="mb-0 mt-2">
                                        <li>İlk fotoğraf ana fotoğraf olarak kullanılacak</li>
                                        <li>Fotoğraf sıralamasını değiştirmek için sürükle-bırak kullanın</li>
                                        <li>Her fotoğraf için açıklama ekleyebilirsiniz</li>
                                        <li>Ana fotoğraf rozeti ile işaretleyebilirsiniz</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Form Submit Button -->
                    <div class="mt-4">
                        <hr>
                        <div class="d-flex justify-content-between align-items-center">
                            <div>
                                <small class="text-muted">
                                    <i class="bi bi-shield-check me-1"></i>
                                    Bilgileriniz güvenli şekilde işlenecektir
                                </small>
                            </div>
                            <div>
                                <button type="button" class="btn btn-outline-secondary me-2" onclick="location.reload()">
                                    <i class="bi bi-arrow-clockwise me-1"></i>Formu Sıfırla
                                </button>
                                <button type="submit" class="btn btn-success btn-lg">
                                    <i class="bi bi-check-circle me-2"></i>İlanı Yayınla
                                </button>
                            </div>
                        </div>
                    </div>
                </div>
            </form>
        </div>
    </div>

    <script>
        // Initialize when DOM is ready
        document.addEventListener('DOMContentLoaded', function() {
            console.log('DOM loaded, initializing...');
            
            // Wizard state variables
            let currentStep = 1;
            let selectedCategory = '';
            let selectedType = '';
            let selectedSubcategory = '';
            
            // Transaction types for each category
            const transactionTypes = {
                'konut': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-house' },
                    { value: 'daily_rent', text: 'Turistik Günlük Kiralık', icon: 'bi-calendar-date' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' }
                ],
                'is_yeri': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-building' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' },
                    { value: 'transfer_rent', text: 'Devren Kiralık', icon: 'bi-arrow-repeat' }
                ],
                'arsa': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-geo-alt' }
                ],
                'bina': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-buildings' }
                ],
                'devre_mulk': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-calendar-check' }
                ],
                'turistik_tesis': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-compass' }
                ]
            };

            // Subcategory system
            const subcategoryTypes = {
                'konut': {
                    'sale': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                        { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'ciftlik_evi', text: 'Çiftlik Evi', icon: 'bi-tree' },
                        { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'koy_evi', text: 'Köy Evi', icon: 'bi-tree-fill' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'rent': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                        { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'daily_rent': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                        { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                        { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                        { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                        { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                        { value: 'yali', text: 'Yalı', icon: 'bi-water' }
                    ],
                    'transfer_sale': [
                        { value: 'daire', text: 'Daire', icon: 'bi-building' },
                        { value: 'villa', text: 'Villa', icon: 'bi-house-heart' }
                    ]
                },
                'is_yeri': {
                    'sale': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'büro_ofis', text: 'Büro & Ofis', icon: 'bi-briefcase' },
                        { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                        { value: 'fabrika_uretim', text: 'Fabrika & Üretim', icon: 'bi-gear' },
                        { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                        { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' },
                        { value: 'eczane', text: 'Eczane', icon: 'bi-plus-circle' },
                        { value: 'berber_kuafor', text: 'Berber & Kuaför', icon: 'bi-scissors' }
                    ],
                    'rent': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'büro_ofis', text: 'Büro & Ofis', icon: 'bi-briefcase' },
                        { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                        { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
                    ],
                    'transfer_sale': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                        { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
                    ],
                    'transfer_rent': [
                        { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                        { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' }
                    ]
                },
                'arsa': {
                    'sale': [
                        { value: 'konut_arsasi', text: 'Konut Arsası', icon: 'bi-house-door' },
                        { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                        { value: 'sanayi_arsasi', text: 'Sanayi Arsası', icon: 'bi-gear-wide' },
                        { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                        { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' },
                        { value: 'villa_arsasi', text: 'Villa Arsası', icon: 'bi-house-heart' }
                    ],
                    'rent': [
                        { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                        { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                        { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' }
                    ]
                },
                'bina': {
                    'sale': [
                        { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                        { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' },
                        { value: 'plaza', text: 'Plaza', icon: 'bi-building-up' }
                    ],
                    'rent': [
                        { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                        { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' }
                    ]
                },
                'devre_mulk': {
                    'sale': [
                        { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' },
                        { value: 'otel', text: 'Otel', icon: 'bi-building' }
                    ],
                    'rent': [
                        { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' }
                    ]
                },
                'turistik_tesis': {
                    'sale': [
                        { value: 'otel', text: 'Otel', icon: 'bi-building' },
                        { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' },
                        { value: 'kamp_alani', text: 'Kamp Alanı', icon: 'bi-tree' }
                    ],
                    'rent': [
                        { value: 'otel', text: 'Otel', icon: 'bi-building' },
                        { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' }
                    ]
                }
            };

            // Setup category selection with proper event listeners
            function setupCategorySelection() {
                const categoryItems = document.querySelectorAll('.category-item');
                console.log('Found category items:', categoryItems.length);
                
                categoryItems.forEach((item, index) => {
                    console.log(`Setting up category ${index}:`, item.dataset.category);
                    
                    // Remove any existing listeners to avoid duplicates
                    item.onclick = null;
                    
                    // Add multiple event types for better compatibility
                    ['click', 'mousedown', 'touchstart'].forEach(eventType => {
                        item.addEventListener(eventType, function(event) {
                            event.preventDefault();
                            event.stopPropagation();
                            
                            const categoryValue = this.dataset.category;
                            console.log('=== CATEGORY SELECTED via', eventType, '===');
                            console.log('Category:', categoryValue);
                            
                            // Clear previous selections
                            document.querySelectorAll('.category-item').forEach(catItem => {
                                catItem.classList.remove('selected');
                                catItem.style.transform = '';
                            });
                            
                            // Mark new selection
                            this.classList.add('selected');
                            this.style.transform = 'translateY(-3px) scale(1.02)';
                            
                            // Update state
                            selectedCategory = categoryValue;
                            
                            // Update hidden input
                            const categoryInput = document.getElementById('category');
                            if (categoryInput) {
                                categoryInput.value = selectedCategory;
                                console.log('Category input updated:', categoryInput.value);
                            }
                            
                            // Enable next button
                            const nextBtn = document.getElementById('next-step');
                            if (nextBtn) {
                                nextBtn.disabled = false;
                                nextBtn.style.backgroundColor = '#0d6efd';
                                nextBtn.style.color = 'white';
                            }
                            
                            // Auto advance after short delay
                            setTimeout(() => {
                                console.log('Auto-advancing to transaction types...');
                                nextStep();
                            }, 800);
                        }, { passive: false });
                    });
                    
                    // Add visual feedback
                    item.addEventListener('mouseenter', function() {
                        if (!this.classList.contains('selected')) {
                            this.style.transform = 'translateY(-5px) scale(1.05)';
                        }
                    });
                    
                    item.addEventListener('mouseleave', function() {
                        if (!this.classList.contains('selected')) {
                            this.style.transform = '';
                        }
                    });
                });
            }

            function setupTransactionSelection() {
                const transactionItems = document.querySelectorAll('.transaction-item');
                
                transactionItems.forEach(item => {
                    item.addEventListener('click', function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        const typeValue = this.dataset.type;
                        console.log('=== TRANSACTION SELECTED ===');
                        console.log('Type:', typeValue);
                        
                        // Clear previous selections
                        document.querySelectorAll('.transaction-item').forEach(i => {
                            i.classList.remove('selected');
                        });
                        
                        // Mark new selection
                        this.classList.add('selected');
                        
                        // Update state
                        selectedType = typeValue;
                        
                        // Update hidden input
                        const typeInput = document.getElementById('type');
                        if (typeInput) {
                            typeInput.value = selectedType;
                            console.log('Type input updated:', typeInput.value);
                        }
                        
                        // Enable next button
                        const nextBtn = document.getElementById('next-step');
                        if (nextBtn) {
                            nextBtn.disabled = false;
                        }
                        
                        // Auto advance
                        setTimeout(() => {
                            console.log('Auto-advancing to subcategories...');
                            nextStep();
                        }, 800);
                    });
                });
            }

            function setupSubcategorySelection() {
                const subcategoryItems = document.querySelectorAll('.subcategory-item');
                
                subcategoryItems.forEach(item => {
                    item.addEventListener('click', function(event) {
                        event.preventDefault();
                        event.stopPropagation();
                        
                        const subcategoryValue = this.dataset.subcategory;
                        console.log('=== SUBCATEGORY SELECTED ===');
                        console.log('Subcategory:', subcategoryValue);
                        
                        // Clear previous selections
                        document.querySelectorAll('.subcategory-item').forEach(i => {
                            i.classList.remove('selected');
                        });
                        
                        // Mark new selection
                        this.classList.add('selected');
                        
                        // Update state
                        selectedSubcategory = subcategoryValue;
                        
                        // Update hidden input
                        const subcategoryInput = document.getElementById('subcategory');
                        if (subcategoryInput) {
                            subcategoryInput.value = selectedSubcategory;
                            console.log('Subcategory input updated:', subcategoryInput.value);
                        }
                        
                        // Enable continue button
                        const continueBtn = document.getElementById('continue-form');
                        if (continueBtn) {
                            continueBtn.disabled = false;
                            continueBtn.style.backgroundColor = '#198754';
                            continueBtn.style.color = 'white';
                        }
                        
                        // Auto advance to main form
                        setTimeout(() => {
                            console.log('Auto-advancing to main form...');
                            showMainForm();
                        }, 1000);
                    });
                });
            }

            function nextStep() {
                console.log('nextStep called, currentStep:', currentStep);
                
                if (currentStep === 1) {
                    showTransactionTypes();
                    currentStep = 2;
                    updateStepIndicator();
                } else if (currentStep === 2) {
                    showSubcategories();
                    currentStep = 3;
                    updateStepIndicator();
                }
            }

            function prevStep() {
                console.log('prevStep called, currentStep:', currentStep);
                
                if (currentStep === 2) {
                    currentStep = 1;
                    document.getElementById('wizard-step-1').style.display = 'block';
                    document.getElementById('wizard-step-2').style.display = 'none';
                    updateStepIndicator();
                } else if (currentStep === 3) {
                    currentStep = 2;
                    document.getElementById('wizard-step-2').style.display = 'block';
                    document.getElementById('wizard-step-3').style.display = 'none';
                    updateStepIndicator();
                }
            }

            function showTransactionTypes() {
                console.log('showTransactionTypes for category:', selectedCategory);
                
                // Hide step 1, show step 2
                document.getElementById('wizard-step-1').style.display = 'none';
                document.getElementById('wizard-step-2').style.display = 'block';
                
                const container = document.getElementById('transaction-options');
                if (!container) {
                    console.error('transaction-options container not found');
                    return;
                }
                
                container.innerHTML = '';
                
                const types = transactionTypes[selectedCategory] || [];
                console.log('Transaction types:', types);
                
                if (types.length === 0) {
                    console.log('No transaction types, skipping to subcategories');
                    setTimeout(() => showSubcategories(), 100);
                    return;
                }
                
                types.forEach(type => {
                    const item = document.createElement('div');
                    item.className = 'transaction-item';
                    item.dataset.type = type.value;
                    item.innerHTML = `
                        <i class="bi ${type.icon}"></i>
                        <span>${type.text}</span>
                    `;
                    container.appendChild(item);
                });
                
                // Setup event listeners for new transaction items
                setupTransactionSelection();
                
                // Update buttons
                const nextBtn = document.getElementById('next-step');
                const prevBtn = document.getElementById('prev-step');
                
                if (nextBtn) nextBtn.disabled = true;
                if (prevBtn) prevBtn.style.display = 'inline-block';
            }

            function showSubcategories() {
                console.log('showSubcategories for:', selectedCategory, selectedType);
                
                // Hide step 2, show step 3
                document.getElementById('wizard-step-2').style.display = 'none';
                document.getElementById('wizard-step-3').style.display = 'block';
                
                const container = document.getElementById('subcategory-options');
                if (!container) {
                    console.error('subcategory-options container not found');
                    return;
                }
                
                container.innerHTML = '';
                
                const subcategories = subcategoryTypes[selectedCategory]?.[selectedType] || [];
                console.log('Subcategories:', subcategories);
                
                if (subcategories.length === 0) {
                    console.log('No subcategories, going to main form');
                    setTimeout(() => showMainForm(), 300);
                    return;
                }
                
                subcategories.forEach(sub => {
                    const item = document.createElement('div');
                    item.className = 'subcategory-item';
                    item.dataset.subcategory = sub.value;
                    item.innerHTML = `
                        <i class="bi ${sub.icon}"></i>
                        <span>${sub.text}</span>
                    `;
                    container.appendChild(item);
                });
                
                // Setup event listeners for new subcategory items
                setupSubcategorySelection();
                
                // Update buttons
                const nextBtn = document.getElementById('next-step');
                const continueBtn = document.getElementById('continue-form');
                const prevBtn = document.getElementById('prev-step');
                
                if (nextBtn) nextBtn.style.display = 'none';
                if (continueBtn) {
                    continueBtn.style.display = 'inline-block';
                    continueBtn.disabled = true;
                }
                if (prevBtn) prevBtn.style.display = 'inline-block';
            }

            function updateStepIndicator() {
                console.log('updateStepIndicator for step:', currentStep);
                
                const steps = document.querySelectorAll('.step');
                steps.forEach((step, index) => {
                    step.classList.remove('active', 'completed');
                    
                    if (index + 1 < currentStep) {
                        step.classList.add('completed');
                    } else if (index + 1 === currentStep) {
                        step.classList.add('active');
                    }
                });
            }

            function showMainForm() {
                console.log('showMainForm called');
                
                // Hide all wizard steps
                document.querySelectorAll('.wizard-step').forEach(step => {
                    step.style.display = 'none';
                });
                
                // Hide step indicator
                const stepIndicator = document.querySelector('.step-indicator');
                if (stepIndicator && stepIndicator.parentElement && stepIndicator.parentElement.parentElement) {
                    stepIndicator.parentElement.parentElement.style.display = 'none';
                }
                
                // Hide navigation buttons
                const navigationDiv = document.querySelector('.row.mt-4');
                if (navigationDiv) {
                    navigationDiv.style.display = 'none';
                }
                
                // Update selection summary
                updateSelectionSummary();
                
                // Show main form
                const mainForm = document.getElementById('main-form');
                if (mainForm) {
                    mainForm.style.display = 'block';
                    console.log('Main form shown successfully');
                }
            }

            function updateSelectionSummary() {
                const summaryContainer = document.getElementById('selection-summary');
                if (!summaryContainer) return;
                
                const categoryNames = {
                    'konut': 'Konut',
                    'is_yeri': 'İş Yeri',
                    'arsa': 'Arsa',
                    'bina': 'Bina',
                    'devre_mulk': 'Devre Mülk',
                    'turistik_tesis': 'Turistik Tesis'
                };
                
                const typeNames = {
                    'sale': 'Satılık',
                    'rent': 'Kiralık',
                    'daily_rent': 'Turistik Günlük Kiralık',
                    'transfer_sale': 'Devren Satılık',
                    'transfer_rent': 'Devren Kiralık'
                };
                
                const categoryText = categoryNames[selectedCategory] || selectedCategory;
                const typeText = typeNames[selectedType] || selectedType;
                
                summaryContainer.innerHTML = `
                    <div class="d-flex flex-wrap gap-3 align-items-center">
                        <div class="badge bg-primary fs-6 p-2">${categoryText}</div>
                        <div class="badge bg-success fs-6 p-2">${typeText}</div>
                        ${selectedSubcategory ? `<div class="badge bg-info fs-6 p-2">${selectedSubcategory}</div>` : ''}
                    </div>
                    <div class="mt-2">
                        <small class="text-muted">Seçiminizi değiştirmek için sayfayı yeniden yükleyin.</small>
                    </div>
                `;
            }

            // Initialize everything
            setupCategorySelection();
            
            // Navigation button handlers
            const nextBtn = document.getElementById('next-step');
            const prevBtn = document.getElementById('prev-step');
            const continueBtn = document.getElementById('continue-form');
            
            if (nextBtn) nextBtn.addEventListener('click', nextStep);
            if (prevBtn) prevBtn.addEventListener('click', prevStep);
            if (continueBtn) continueBtn.addEventListener('click', showMainForm);
            
            // Setup other features
            setupFormHandlers();
            
            console.log('Initialization complete');
        });

        // Form handlers setup
        function setupFormHandlers() {
            // Title preview
            const titleInput = document.getElementById('title');
            const titlePreview = document.getElementById('title-preview');
            
            if (titleInput && titlePreview) {
                titleInput.addEventListener('input', function() {
                    const title = this.value || 'İlan başlığınız burada görünecek...';
                    titlePreview.innerHTML = `<span class="fw-bold" style="color: #0d6efd;">${title}</span>`;
                });
            }

            // Location type handlers
            const locationSite = document.getElementById('location_site');
            const locationStandalone = document.getElementById('location_standalone');
            const siteNameSection = document.getElementById('site-name-section');
            const addressDetailsSection = document.getElementById('address-details-section');

            if (locationSite && locationStandalone && siteNameSection && addressDetailsSection) {
                locationSite.addEventListener('change', function() {
                    if (this.checked) {
                        siteNameSection.style.display = 'block';
                        addressDetailsSection.style.display = 'none';
                    }
                });

                locationStandalone.addEventListener('change', function() {
                    if (this.checked) {
                        siteNameSection.style.display = 'none';
                        addressDetailsSection.style.display = 'block';
                    }
                });
            }

            // City/District handlers
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const neighborhoodSelect = document.getElementById('neighborhood');

            if (citySelect && districtSelect) {
                citySelect.addEventListener('change', function() {
                    const selectedCity = this.value;
                    districtSelect.innerHTML = '<option value="">Seçiniz</option>';
                    neighborhoodSelect.innerHTML = '<option value="">Seçiniz</option>';
                    
                    if (selectedCity && turkishDistricts[selectedCity]) {
                        turkishDistricts[selectedCity].forEach(district => {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                    }
                });
            }
        }
        });

        // Basit foto preview sistemi
        function previewImages(input) {
            const previewDiv = document.getElementById('photoPreview');
            const previewContainer = document.getElementById('previewContainer');
            const photoCount = document.getElementById('photoCount');
            
            if (input.files && input.files.length > 0) {
                previewDiv.style.display = 'block';
                photoCount.textContent = input.files.length;
                previewContainer.innerHTML = '';
                
                Array.from(input.files).forEach((file, index) => {
                    if (file.type.startsWith('image/')) {
                        const reader = new FileReader();
                        reader.onload = function(e) {
                            const imgDiv = document.createElement('div');
                            imgDiv.className = 'col-md-3 col-sm-4 col-6 mb-3';
                            imgDiv.innerHTML = `
                                <div class="card h-100">
                                    <img src="${e.target.result}" class="card-img-top" style="height: 150px; object-fit: cover;">
                                    <div class="card-body p-2">
                                        <small class="text-muted">
                                            ${index === 0 ? '<i class="fas fa-star text-warning"></i> Ana Fotoğraf' : 'Fotoğraf ' + (index + 1)}
                                        </small>
                                    </div>
                                </div>
                            `;
                            previewContainer.appendChild(imgDiv);
                        };
                        reader.readAsDataURL(file);
                    }
                });
            } else {
                previewDiv.style.display = 'none';
            }
        }

        // Turkish districts data
        const turkishDistricts = {
            'Adana': ['Aladağ', 'Ceyhan', 'Çukurova', 'Feke', 'İmamoğlu', 'Karaisalı', 'Karataş', 'Kozan', 'Pozantı', 'Saimbeyli', 'Sarıçam', 'Seyhan', 'Tufanbeyli', 'Yumurtalık', 'Yüreğir'],
            'Adıyaman': ['Besni', 'Çelikhan', 'Gerger', 'Gölbaşı', 'Kahta', 'Merkez', 'Samsat', 'Sincik', 'Tut'],
            'Afyonkarahisar': ['Başmakçı', 'Bayat', 'Bolvadin', 'Çay', 'Çobanlar', 'Dazkırı', 'Dinar', 'Emirdağ', 'Evciler', 'Hocalar', 'İhsaniye', 'İscehisar', 'Kızılören', 'Merkez', 'Sandıklı', 'Sinanpaşa', 'Sultandağı', 'Şuhut'],
            'Ağrı': ['Diyadin', 'Doğubayazıt', 'Eleşkirt', 'Hamur', 'Merkez', 'Patnos', 'Taşlıçay', 'Tutak'],
            'Amasya': ['Göynücek', 'Gümüşhacıköy', 'Hamamözü', 'Merkez', 'Merzifon', 'Suluova', 'Taşova'],
            'Ankara': ['Akyurt', 'Altındağ', 'Ayaş', 'Bala', 'Beypazarı', 'Çamlıdere', 'Çankaya', 'Çubuk', 'Elmadağ', 'Etimesgut', 'Evren', 'Gölbaşı', 'Güdül', 'Haymana', 'Kalecik', 'Kazan', 'Keçiören', 'Kızılcahamam', 'Mamak', 'Nallıhan', 'Polatlı', 'Pursaklar', 'Sincan', 'Şereflikoçhisar', 'Yenimahalle'],
            'Antalya': ['Akseki', 'Aksu', 'Alanya', 'Demre', 'Döşemealtı', 'Elmalı', 'Finike', 'Gazipaşa', 'Gündoğmuş', 'İbradı', 'Kaş', 'Kemer', 'Kepez', 'Konyaaltı', 'Korkuteli', 'Kumluca', 'Manavgat', 'Muratpaşa', 'Serik'],
            'Artvin': ['Ardanuç', 'Arhavi', 'Borçka', 'Hopa', 'Merkez', 'Murgul', 'Şavşat', 'Yusufeli'],
            'Aydın': ['Bozdoğan', 'Buharkent', 'Çine', 'Didim', 'Efeler', 'Germencik', 'İncirliova', 'Karacasu', 'Karpuzlu', 'Koçarlı', 'Köşk', 'Kuşadası', 'Kuyucak', 'Nazilli', 'Söke', 'Sultanhisar', 'Yenipazar'],
            'Balıkesir': ['Ayvalık', 'Balya', 'Bandırma', 'Bigadiç', 'Burhaniye', 'Dursunbey', 'Edremit', 'Erdek', 'Gömeç', 'Gönen', 'Havran', 'İvrindi', 'Karesi', 'Kepsut', 'Manyas', 'Marmara', 'Savaştepe', 'Sındırgı', 'Susurluk'],
            'Bartın': ['Amasra', 'Kurucaşile', 'Merkez', 'Ulus'],
            'Batman': ['Beşiri', 'Gercüş', 'Hasankeyf', 'Kozluk', 'Merkez', 'Sason'],
            'Bayburt': ['Aydıntepe', 'Demirözü', 'Merkez'],
            'Bilecik': ['Bozüyük', 'Gölpazarı', 'İnhisar', 'Merkez', 'Osmaneli', 'Pazaryeri', 'Söğüt', 'Yenipazar'],
            'Bingöl': ['Adaklı', 'Genç', 'Karlıova', 'Kiğı', 'Merkez', 'Solhan', 'Yayladere', 'Yedisu'],
            'Bitlis': ['Adilcevaz', 'Ahlat', 'Güroymak', 'Hizan', 'Merkez', 'Mutki', 'Tatvan'],
            'Bolu': ['Dörtdivan', 'Gerede', 'Göynük', 'Kıbrıscık', 'Mengen', 'Merkez', 'Mudurnu', 'Seben', 'Yeniçağa'],
            'Burdur': ['Ağlasun', 'Altınyayla', 'Bucak', 'Çavdır', 'Çeltikçi', 'Gölhisar', 'Karamanlı', 'Kemer', 'Merkez', 'Tefenni', 'Yeşilova'],
            'Bursa': ['Büyükorhan', 'Gemlik', 'Gürsu', 'Harmancık', 'İnegöl', 'İznik', 'Karacabey', 'Keles', 'Kestel', 'Mudanya', 'Mustafakemalpaşa', 'Nilüfer', 'Orhaneli', 'Orhangazi', 'Osmangazi', 'Yenişehir', 'Yıldırım'],
            'Çanakkale': ['Ayvacık', 'Bayramiç', 'Biga', 'Bozcaada', 'Çan', 'Eceabat', 'Ezine', 'Gelibolu', 'Gökçeada', 'Lapseki', 'Merkez', 'Yenice'],
            'Çankırı': ['Atkaracalar', 'Bayramören', 'Çerkeş', 'Eldivan', 'Ilgaz', 'Kızılırmak', 'Korgun', 'Kurşunlu', 'Merkez', 'Orta', 'Şabanözü', 'Yapraklı'],
            'Çorum': ['Alaca', 'Bayat', 'Boğazkale', 'Dodurga', 'İskilip', 'Kargı', 'Laçin', 'Mecitözü', 'Merkez', 'Oğuzlar', 'Ortaköy', 'Osmancık', 'Sungurlu', 'Uğurludağ'],
            'Denizli': ['Acıpayam', 'Babadağ', 'Baklan', 'Bekilli', 'Beyağaç', 'Bozkurt', 'Buldan', 'Çal', 'Çameli', 'Çardak', 'Çivril', 'Güney', 'Honaz', 'Kale', 'Merkezefendi', 'Pamukkale', 'Sarayköy', 'Serinhisar', 'Tavas'],
            'Diyarbakır': ['Bağlar', 'Bismil', 'Çermik', 'Çınar', 'Çüngüş', 'Dicle', 'Eğil', 'Ergani', 'Hani', 'Hazro', 'Kayapınar', 'Kocaköy', 'Kulp', 'Lice', 'Silvan', 'Sur', 'Yenişehir'],
            'Düzce': ['Akçakoca', 'Cumayeri', 'Çilimli', 'Gölyaka', 'Gümüşova', 'Kaynaşlı', 'Merkez', 'Yığılca'],
            'Edirne': ['Enez', 'Havsa', 'İpsala', 'Keşan', 'Lalapaşa', 'Meriç', 'Merkez', 'Süloğlu', 'Uzunköprü'],
            'Elazığ': ['Ağın', 'Alacakaya', 'Arıcak', 'Baskil', 'Karakoçan', 'Keban', 'Kovancılar', 'Maden', 'Merkez', 'Palu', 'Sivrice'],
            'Erzincan': ['Çayırlı', 'İliç', 'Kemah', 'Kemaliye', 'Merkez', 'Otlukbeli', 'Refahiye', 'Tercan', 'Üzümlü'],
            'Erzurum': ['Aşkale', 'Aziziye', 'Çat', 'Hınıs', 'Horasan', 'İspir', 'Karaçoban', 'Karayazı', 'Köprüköy', 'Narman', 'Oltu', 'Olur', 'Palandöken', 'Pasinler', 'Pazaryolu', 'Şenkaya', 'Tekman', 'Tortum', 'Uzundere', 'Yakutiye'],
            'Eskişehir': ['Alpu', 'Beylikova', 'Çifteler', 'Günyüzü', 'Han', 'İnönü', 'Mahmudiye', 'Mihalgazi', 'Mihalıççık', 'Odunpazarı', 'Sarıcakaya', 'Seyitgazi', 'Sivrihisar', 'Tepebaşı'],
            'Gaziantep': ['Araban', 'İslahiye', 'Karkamış', 'Nizip', 'Nurdağı', 'Oğuzeli', 'Şahinbey', 'Şehitkamil', 'Yavuzeli'],
            'Giresun': ['Alucra', 'Bulancak', 'Çamoluk', 'Çanakçı', 'Dereli', 'Doğankent', 'Espiye', 'Eynesil', 'Görele', 'Güce', 'Keşap', 'Merkez', 'Piraziz', 'Şebinkarahisar', 'Tirebolu', 'Yağlıdere'],
            'Gümüşhane': ['Kelkit', 'Köse', 'Kürtün', 'Merkez', 'Şiran', 'Torul'],
            'Hakkari': ['Çukurca', 'Derecik', 'Merkez', 'Şemdinli', 'Yüksekova'],
            'Hatay': ['Altınözü', 'Antakya', 'Arsuz', 'Belen', 'Defne', 'Dörtyol', 'Erzin', 'Hassa', 'İskenderun', 'Kırıkhan', 'Kumlu', 'Payas', 'Reyhanlı', 'Samandağ', 'Yayladağı'],
            'Iğdır': ['Aralık', 'Karakoyunlu', 'Merkez', 'Tuzluca'],
            'Isparta': ['Aksu', 'Atabey', 'Eğirdir', 'Gelendost', 'Gönen', 'Keçiborlu', 'Merkez', 'Senirkent', 'Sütçüler', 'Şarkikaraağaç', 'Uluborlu', 'Yalvaç', 'Yenişarbademli'],
            'İstanbul': ['Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy', 'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane', 'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'],
            'İzmir': ['Aliağa', 'Balçova', 'Bayındır', 'Bayraklı', 'Bergama', 'Beydağ', 'Bornova', 'Buca', 'Çeşme', 'Çiğli', 'Dikili', 'Foça', 'Gaziemir', 'Güzelbahçe', 'Karabağlar', 'Karaburun', 'Karşıyaka', 'Kemalpaşa', 'Kınık', 'Kiraz', 'Konak', 'Menderes', 'Menemen', 'Narlıdere', 'Ödemiş', 'Seferihisar', 'Selçuk', 'Tire', 'Torbalı', 'Urla']
        };

        function setupFormHandlers() {
            // Title preview
            const titleInput = document.getElementById('title');
            const titlePreview = document.getElementById('title-preview');
            
            if (titleInput && titlePreview) {
                titleInput.addEventListener('input', function() {
                    const title = this.value.trim();
                    if (title) {
                        titlePreview.innerHTML = `<span class="fw-bold" style="color: #0d6efd;">${title}</span>`;
                    } else {
                        titlePreview.innerHTML = '<span class="text-muted">İlan başlığınız burada görünecek...</span>';
                    }
                });
            }

            // Price formatting
            const priceInput = document.getElementById('price');
            if (priceInput) {
                priceInput.addEventListener('input', function() {
                    let value = this.value.replace(/\D/g, '');
                    if (value) {
                        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
                    }
                    this.value = value;
                });
            }

            // City and District handling
            const citySelect = document.getElementById('city');
            const districtSelect = document.getElementById('district');
            const neighborhoodSelect = document.getElementById('neighborhood');
            
            if (citySelect) {
                citySelect.addEventListener('change', function() {
                    const selectedCity = this.value;
                    
                    // Clear district and neighborhood dropdowns
                    districtSelect.innerHTML = '<option value="">Seçiniz</option>';
                    neighborhoodSelect.innerHTML = '<option value="">Seçiniz</option>';
                    
                    if (selectedCity && turkishDistricts[selectedCity]) {
                        turkishDistricts[selectedCity].forEach(district => {
                            const option = document.createElement('option');
                            option.value = district;
                            option.textContent = district;
                            districtSelect.appendChild(option);
                        });
                    }
                });
            }

            // Location type selection
            const locationRadios = document.querySelectorAll('input[name="location_type"]');
            const siteNameSection = document.getElementById('site-name-section');
            const addressDetailsSection = document.getElementById('address-details-section');
            const siteNameInput = document.getElementById('site_name');
            const addressDetailsInput = document.getElementById('address_details');
            
            locationRadios.forEach(radio => {
                radio.addEventListener('change', function() {
                    if (this.value === 'site') {
                        siteNameSection.style.display = 'block';
                        addressDetailsSection.style.display = 'none';
                        siteNameInput.required = true;
                        addressDetailsInput.required = false;
                        addressDetailsInput.value = '';
                    } else if (this.value === 'standalone') {
                        siteNameSection.style.display = 'none';
                        addressDetailsSection.style.display = 'block';
                        siteNameInput.required = false;
                        addressDetailsInput.required = true;
                        siteNameInput.value = '';
                    }
                });
            });
        }

        // Photo Upload System
        let selectedPhotos = [];

        function setupPhotoUpload() {
            const photoInput = document.getElementById('photoInput');
            const uploadArea = document.getElementById('uploadArea');
            
            if (!photoInput || !uploadArea) return;

            // File input change
            photoInput.addEventListener('change', function(e) {
                const files = Array.from(e.target.files);
                if (files.length > 0) {
                    addPhotos(files);
                }
                this.value = '';
            });

            // Drag & Drop
            uploadArea.addEventListener('dragover', function(e) {
                e.preventDefault();
                this.classList.add('dragover');
            });

            uploadArea.addEventListener('dragleave', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
            });

            uploadArea.addEventListener('drop', function(e) {
                e.preventDefault();
                this.classList.remove('dragover');
                
                const files = Array.from(e.dataTransfer.files);
                const imageFiles = files.filter(file => file.type.startsWith('image/'));
                
                if (imageFiles.length > 0) {
                    addPhotos(imageFiles);
                } else {
                    showAlert('Lütfen sadece resim dosyaları seçin!', 'warning');
                }
            });
        }

        function addPhotos(files) {
            let addedCount = 0;
            let errorCount = 0;
            
            files.forEach(file => {
                if (validatePhoto(file)) {
                    selectedPhotos.push({
                        file: file,
                        id: Date.now() + Math.random(),
                        isMain: selectedPhotos.length === 0
                    });
                    addedCount++;
                } else {
                    errorCount++;
                }
            });
            
            if (addedCount > 0) {
                updatePhotoDisplay();
                showAlert(`${addedCount} fotoğraf eklendi!`, 'success');
            }
            
            if (errorCount > 0) {
                showAlert(`${errorCount} fotoğraf hatalı (format/boyut)`, 'warning');
            }
        }

        function validatePhoto(file) {
            if (!file.type.startsWith('image/')) return false;
            if (file.size > 5 * 1024 * 1024) return false; // 5MB
            return true;
        }

        function updatePhotoDisplay() {
            const selectedPhotosDiv = document.getElementById('selectedPhotos');
            const photosGrid = document.getElementById('photosGrid');
            const photoCounter = document.getElementById('photoCounter');
            
            if (selectedPhotos.length === 0) {
                selectedPhotosDiv.style.display = 'none';
                return;
            }
            
            selectedPhotosDiv.style.display = 'block';
            photoCounter.textContent = selectedPhotos.length;
            
            photosGrid.innerHTML = '';
            
            selectedPhotos.forEach((photo, index) => {
                const photoDiv = document.createElement('div');
                photoDiv.className = `photo-item ${photo.isMain ? 'main-photo' : ''}`;
                photoDiv.dataset.id = photo.id;
                
                const reader = new FileReader();
                reader.onload = function(e) {
                    photoDiv.innerHTML = `
                        <img src="${e.target.result}" alt="Fotoğraf ${index + 1}">
                        <div class="photo-overlay">
                            <div class="photo-actions">
                                <button type="button" class="photo-action-btn delete" onclick="removePhoto('${photo.id}')" title="Sil">
                                    <i class="fas fa-trash"></i>
                                </button>
                                ${!photo.isMain ? `<button type="button" class="photo-action-btn main" onclick="setAsMain('${photo.id}')" title="Ana Fotoğraf Yap">
                                    <i class="fas fa-star"></i>
                                </button>` : ''}
                            </div>
                        </div>
                        <div class="photo-number">${index + 1}</div>
                    `;
                };
                reader.readAsDataURL(photo.file);
                
                photosGrid.appendChild(photoDiv);
            });
            
            updateFormData();
        }

        function removePhoto(photoId) {
            const index = selectedPhotos.findIndex(p => p.id == photoId);
            if (index > -1) {
                const wasMain = selectedPhotos[index].isMain;
                selectedPhotos.splice(index, 1);
                
                if (wasMain && selectedPhotos.length > 0) {
                    selectedPhotos[0].isMain = true;
                }
                
                updatePhotoDisplay();
                showAlert('Fotoğraf silindi', 'info');
            }
        }

        function setAsMain(photoId) {
            selectedPhotos.forEach(photo => {
                photo.isMain = (photo.id == photoId);
            });
            
            updatePhotoDisplay();
            showAlert('Ana fotoğraf değiştirildi!', 'success');
        }

        function clearAllPhotos() {
            if (confirm('Tüm fotoğrafları silmek istediğinizden emin misiniz?')) {
                selectedPhotos = [];
                updatePhotoDisplay();
                showAlert('Tüm fotoğraflar silindi', 'info');
            }
        }

        function updateFormData() {
            const form = document.getElementById('propertyForm');
            const existingInputs = form.querySelectorAll('input[name="property_images[]"]:not(#photoInput)');
            existingInputs.forEach(input => input.remove());
            
            selectedPhotos.forEach((photo, index) => {
                const input = document.createElement('input');
                input.type = 'file';
                input.name = 'property_images[]';
                input.style.display = 'none';
                
                const dataTransfer = new DataTransfer();
                dataTransfer.items.add(photo.file);
                input.files = dataTransfer.files;
                
                form.appendChild(input);
            });
        }

        function showAlert(message, type = 'info') {
            const alertClass = type === 'success' ? 'alert-success' : 
                              type === 'warning' ? 'alert-warning' : 
                              type === 'danger' ? 'alert-danger' : 'alert-info';
                              
            const alertDiv = document.createElement('div');
            alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
            alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
            
            alertDiv.innerHTML = `
                <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
                ${message}
                <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
            `;
            
            document.body.appendChild(alertDiv);
            
            setTimeout(() => {
                if (alertDiv.parentNode) {
                    alertDiv.remove();
                }
            }, 4000);
        }

        // Mobile sidebar toggle
        function toggleSidebar() {
            const sidebar = document.querySelector('.dash-aside-navbar');
            const overlay = document.querySelector('.mobile-overlay');
            
            if (sidebar && overlay) {
                sidebar.classList.toggle('show');
                overlay.classList.toggle('show');
            }
        }
        
        // Initialize systems after functions are defined
        console.log('Setting up category selection...');
        setupCategorySelection();
        initPhotoSystem();
        
        // Debug function
        window.debugCategorySelection = function() {
            console.log('=== DEBUG CATEGORY SELECTION ===');
            const items = document.querySelectorAll('.category-item');
            console.log('Found category items:', items.length);
            items.forEach((item, index) => {
                console.log(`Item ${index}:`, item.dataset.category, item);
                item.style.border = '3px solid red';
                item.style.background = 'yellow';
                
                // Test click event manually
                item.onclick = function() {
                    alert('Kategori tıklandı: ' + this.dataset.category);
                    console.log('Manual click works for:', this.dataset.category);
                };
                
                setTimeout(() => {
                    item.style.border = '';
                    item.style.background = '';
                }, 3000);
            });
            
            // Test if setupCategorySelection function exists
            if (typeof setupCategorySelection === 'function') {
                console.log('setupCategorySelection function exists');
            } else {
                console.log('setupCategorySelection function NOT found');
            }
        };
        
        // Simple test function
        window.testClick = function() {
            const firstCategory = document.querySelector('.category-item');
            if (firstCategory) {
                console.log('Testing first category click...');
                firstCategory.click();
            }
        };
        
        // Direct category selection function
        window.selectCategory = function(categoryValue) {
            console.log('=== CATEGORY SELECTED (INLINE) ===');
            console.log('Category:', categoryValue);
            
            // Clear previous selections
            document.querySelectorAll('.category-item').forEach(item => {
                item.classList.remove('selected');
                item.style.transform = '';
                item.style.background = '';
                item.style.borderColor = '#dee2e6';
            });
            
            // Find and mark selected item
            const selectedItem = document.querySelector(`[data-category="${categoryValue}"]`);
            if (selectedItem) {
                selectedItem.classList.add('selected');
                selectedItem.style.transform = 'translateY(-3px) scale(1.02)';
                selectedItem.style.background = '#e3f2fd';
                selectedItem.style.borderColor = '#0d6efd';
            }
            
            // Update global variable (check if it exists in DOMContentLoaded scope)
            try {
                selectedCategory = categoryValue;
            } catch(e) {
                window.selectedCategory = categoryValue;
            }
            
            // Update hidden input
            const categoryInput = document.getElementById('category');
            if (categoryInput) {
                categoryInput.value = categoryValue;
                console.log('Category input updated:', categoryInput.value);
            }
            
            // Enable next button
            const nextBtn = document.getElementById('next-step');
            if (nextBtn) {
                nextBtn.disabled = false;
                nextBtn.style.backgroundColor = '#0d6efd';
                nextBtn.style.color = 'white';
            }
            
            // Auto advance after delay
            setTimeout(() => {
                console.log('Auto-advancing to next step...');
                
                // Show transaction types step
                document.getElementById('wizard-step-1').style.display = 'none';
                document.getElementById('wizard-step-2').style.display = 'block';
                
                // Update step indicator
                document.getElementById('step-1').classList.remove('active');
                document.getElementById('step-2').classList.add('active');
                
                // Generate transaction options
                showTransactionTypesForCategory(categoryValue);
                
            }, 1000);
        };
        
        // Force category selection function - bypass all blocking
        window.forceSelectCategory = function(categoryValue) {
            console.log('=== FORCE CATEGORY SELECTED ===');
            console.log('Category:', categoryValue);
            
            // Clear previous selections  
            document.querySelectorAll('.category-item').forEach(item => {
                item.classList.remove('selected');
                item.style.transform = '';
                item.style.background = '';
                item.style.borderColor = '#dee2e6';
            });
            
            // Find and mark selected item
            const selectedItem = document.querySelector(`[data-category="${categoryValue}"]`);
            if (selectedItem) {
                selectedItem.classList.add('selected');
                selectedItem.style.transform = 'translateY(-3px) scale(1.02)';
                selectedItem.style.background = '#e3f2fd';
                selectedItem.style.borderColor = '#0d6efd';
            }
            
            // Update hidden input
            const categoryInput = document.getElementById('category');
            if (categoryInput) {
                categoryInput.value = categoryValue;
                console.log('Category input updated:', categoryInput.value);
            }
            
            // Auto advance
            setTimeout(() => {
                console.log('Force advancing to next step...');
                document.getElementById('wizard-step-1').style.display = 'none';
                document.getElementById('wizard-step-2').style.display = 'block';
                document.getElementById('step-1').classList.remove('active');
                document.getElementById('step-2').classList.add('active');
                
                // Show transaction types for selected category
                showTransactionTypesForCategory(categoryValue);
            }, 500);
        };
        
        // Simple and reliable category selection function
        window.simpleSelectCategory = function(categoryValue) {
            console.log('SIMPLE SELECT CATEGORY:', categoryValue);
            
            // Clear all selected buttons
            document.querySelectorAll('.category-btn').forEach(btn => {
                btn.classList.remove('selected', 'btn-primary');
                btn.classList.add('btn-outline-primary');
            });
            
            // Mark selected button
            event.target.closest('.category-btn').classList.add('selected', 'btn-primary');
            event.target.closest('.category-btn').classList.remove('btn-outline-primary');
            
            // Update hidden input
            document.getElementById('category').value = categoryValue;
            
            // Show success message
            alert('Kategori seçildi: ' + categoryValue);
            
            // Auto advance after 1 second
            setTimeout(() => {
                document.getElementById('wizard-step-1').style.display = 'none';
                document.getElementById('wizard-step-2').style.display = 'block';
                document.getElementById('step-1').classList.remove('active');
                document.getElementById('step-2').classList.add('active');
                
                // Show transaction types
                if (typeof showTransactionTypesForCategory === 'function') {
                    showTransactionTypesForCategory(categoryValue);
                }
            }, 1000);
        };
        
        // Simple transaction types loader
        window.loadTransactionTypes = function(categoryValue) {
            console.log('Loading transaction types for:', categoryValue);
            
            const container = document.getElementById('transaction-options');
            if (!container) {
                console.error('transaction-options container not found');
                return;
            }
            
            let transactionHTML = '<div class="row g-3 justify-content-center">';
            
            // Add common transaction types for all categories
            if (categoryValue === 'konut') {
                transactionHTML += `
                    <div class="col-md-3">
                        <button type="button" onclick="selectTransaction('sale')" class="btn btn-outline-success w-100 p-4">
                            <i class="bi bi-currency-dollar fs-1 mb-3 d-block"></i>
                            <h5>Satılık</h5>
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" onclick="selectTransaction('rent')" class="btn btn-outline-success w-100 p-4">
                            <i class="bi bi-house fs-1 mb-3 d-block"></i>
                            <h5>Kiralık</h5>
                        </button>
                    </div>
                    <div class="col-md-3">
                        <button type="button" onclick="selectTransaction('daily_rent')" class="btn btn-outline-success w-100 p-4">
                            <i class="bi bi-calendar-date fs-1 mb-3 d-block"></i>
                            <h5>Günlük Kiralık</h5>
                        </button>
                    </div>`;
            } else {
                // For other categories, show basic sale/rent
                transactionHTML += `
                    <div class="col-md-4">
                        <button type="button" onclick="selectTransaction('sale')" class="btn btn-outline-success w-100 p-4">
                            <i class="bi bi-currency-dollar fs-1 mb-3 d-block"></i>
                            <h5>Satılık</h5>
                        </button>
                    </div>
                    <div class="col-md-4">
                        <button type="button" onclick="selectTransaction('rent')" class="btn btn-outline-success w-100 p-4">
                            <i class="bi bi-building fs-1 mb-3 d-block"></i>
                            <h5>Kiralık</h5>
                        </button>
                    </div>`;
            }
            
            transactionHTML += '</div>';
            container.innerHTML = transactionHTML;
            
            console.log('Transaction types loaded successfully');
        };
        
        // Simple transaction selection
        window.selectTransaction = function(transactionType) {
            console.log('Transaction selected:', transactionType);
            
            // Update hidden input
            document.getElementById('type').value = transactionType;
            
            // Show alert
            alert(transactionType.toUpperCase() + ' işlem türü seçildi!');
            
            // For now, show main form directly (skip subcategory)
            document.getElementById('wizard-step-2').style.display = 'none';
            document.getElementById('main-form').style.display = 'block';
            
            // Update step indicators
            document.getElementById('step-2').classList.remove('active');
            document.getElementById('step-3').classList.add('active');
        };
        
        // Very simple select and advance function
        window.selectAndAdvance = function(categoryValue) {
            console.log('SELECT AND ADVANCE:', categoryValue);
            
            // Update hidden input
            const categoryInput = document.getElementById('category');
            if (categoryInput) {
                categoryInput.value = categoryValue;
                console.log('Category set to:', categoryInput.value);
            }
            
            // Show success alert
            alert(categoryValue.toUpperCase() + ' kategorisi seçildi! Sonraki adıma geçiliyor...');
            
            // Find the wizard steps
            const step1 = document.getElementById('wizard-step-1');
            const step2 = document.getElementById('wizard-step-2');
            const stepIndicator1 = document.getElementById('step-1');
            const stepIndicator2 = document.getElementById('step-2');
            
            console.log('Step1 element:', step1);
            console.log('Step2 element:', step2);
            
            // Hide step 1, show step 2
            if (step1) step1.style.display = 'none';
            if (step2) step2.style.display = 'block';
            
            // Update step indicators
            if (stepIndicator1) stepIndicator1.classList.remove('active');
            if (stepIndicator2) stepIndicator2.classList.add('active');
            
            console.log('Advanced to step 2');
        };
        
        // Function to show transaction types for selected category
        window.showTransactionTypesForCategory = function(category) {
            const container = document.getElementById('transaction-options');
            if (!container) return;
            
            container.innerHTML = '';
            
            // Transaction types for each category
            const transactionTypes = {
                'konut': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-house' },
                    { value: 'daily_rent', text: 'Turistik Günlük Kiralık', icon: 'bi-calendar-date' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' }
                ],
                'is_yeri': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-building' },
                    { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' },
                    { value: 'transfer_rent', text: 'Devren Kiralık', icon: 'bi-arrow-repeat' }
                ],
                'arsa': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-geo-alt' }
                ],
                'bina': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-buildings' }
                ],
                'devre_mulk': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-calendar-check' }
                ],
                'turistik_tesis': [
                    { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
                    { value: 'rent', text: 'Kiralık', icon: 'bi-compass' }
                ]
            };
            
            const types = transactionTypes[category] || [];
            
            // Add CSS class for transaction grid
            container.className = 'row g-4 justify-content-center';
            
            types.forEach(type => {
                const colDiv = document.createElement('div');
                colDiv.className = 'col-md-3 col-sm-6';
                
                const item = document.createElement('div');
                item.className = 'transaction-item';
                item.dataset.type = type.value;
                item.onclick = () => selectTransactionType(type.value);
                item.style.cssText = 'z-index: 9999; position: relative; pointer-events: auto !important; cursor: pointer; border: 3px solid #dee2e6; border-radius: 20px; padding: 2rem; text-align: center; background: white; min-height: 150px; display: flex; flex-direction: column; justify-content: center; align-items: center; transition: all 0.3s ease;';
                
                item.innerHTML = `
                    <i class="bi ${type.icon}" style="font-size: 2.5rem; color: #0d6efd; margin-bottom: 1rem;"></i>
                    <span style="font-size: 1.1rem; font-weight: 600;">${type.text}</span>
                `;
                
                colDiv.appendChild(item);
                container.appendChild(colDiv);
            });
            
            // Show prev button
            const prevBtn = document.getElementById('prev-step');
            if (prevBtn) {
                prevBtn.style.display = 'inline-block';
            }
        };
        
        // Function to select transaction type
        window.selectTransactionType = function(typeValue) {
            console.log('Transaction type selected:', typeValue);
            
            // Clear previous selections
            document.querySelectorAll('.transaction-item').forEach(item => {
                item.style.borderColor = '#dee2e6';
                item.style.background = 'white';
            });
            
            // Mark selected
            const selectedItem = document.querySelector(`[data-type="${typeValue}"]`);
            if (selectedItem) {
                selectedItem.style.borderColor = '#0d6efd';
                selectedItem.style.background = '#e3f2fd';
            }
            
            // Update hidden input
            const typeInput = document.getElementById('type');
            if (typeInput) {
                typeInput.value = typeValue;
            }
            
            // Move to step 3 (subcategory) or main form
            setTimeout(() => {
                document.getElementById('wizard-step-2').style.display = 'none';
                document.getElementById('wizard-step-3').style.display = 'block';
                
                // Update step indicator
                document.getElementById('step-2').classList.remove('active');
                document.getElementById('step-3').classList.add('active');
                
                showSubcategoriesForCategoryAndType(window.selectedCategory, typeValue);
            }, 800);
        };
        
        // Function to show subcategories
        window.showSubcategoriesForCategoryAndType = function(category, type) {
            const container = document.getElementById('subcategory-options');
            if (!container) return;
            
            container.innerHTML = '';
            
            // For now, let's skip subcategories and go directly to main form
            setTimeout(() => {
                // Hide all wizard steps
                document.querySelectorAll('.wizard-step').forEach(step => {
                    step.style.display = 'none';
                });
                
                // Show main form
                document.getElementById('main-form').style.display = 'block';
                
                // Hide navigation buttons
                document.getElementById('next-step').style.display = 'none';
                document.getElementById('prev-step').style.display = 'none';
                
                // Update selection summary
                const summaryDiv = document.getElementById('selection-summary');
                if (summaryDiv) {
                    summaryDiv.innerHTML = `
                        <strong>Seçilen Kategori:</strong> ${category} | 
                        <strong>İşlem Türü:</strong> ${type}
                    `;
                }
                
                console.log('Main form displayed successfully!');
            }, 500);
        };
        
        }); // End DOMContentLoaded

        // ========================================
        // GELİŞMİŞ FOTOĞRAF YÖNETİM SİSTEMİ
        // ========================================
        
        let photoFiles = [];
        let mainPhotoIndex = 0;
        let photoIdCounter = 0;

        // Photo system is initialized in main DOMContentLoaded above

        function initPhotoSystem() {
            const uploadArea = document.getElementById('uploadArea');
            const photoInput = document.getElementById('photoInput');

            // Drag & Drop events
            uploadArea.addEventListener('dragover', handleDragOver);
            uploadArea.addEventListener('dragleave', handleDragLeave);
            uploadArea.addEventListener('drop', handleDrop);
            uploadArea.addEventListener('click', () => photoInput.click());

            // File input change
            photoInput.addEventListener('change', handleFileSelect);
        }

        function handleDragOver(e) {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.add('drag-over');
        }

        function handleDragLeave(e) {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.remove('drag-over');
        }

        function handleDrop(e) {
            e.preventDefault();
            e.stopPropagation();
            e.currentTarget.classList.remove('drag-over');
            
            const files = Array.from(e.dataTransfer.files);
            processFiles(files);
        }

        function handleFileSelect(e) {
            const files = Array.from(e.target.files);
            processFiles(files);
            e.target.value = ''; // Reset input
        }

        function processFiles(files) {
            const validFiles = files.filter(file => {
                // Dosya tipini kontrol et
                if (!file.type.startsWith('image/')) {
                    showToast('Sadece resim dosyaları kabul edilir!', 'error');
                    return false;
                }
                
                // Dosya boyutunu kontrol et (5MB)
                if (file.size > 5 * 1024 * 1024) {
                    showToast(`${file.name} dosyası çok büyük (max 5MB)`, 'error');
                    return false;
                }
                
                return true;
            });

            validFiles.forEach(file => addPhotoToGrid(file));
            
            if (validFiles.length > 0) {
                // Upload area'yı gizle
                document.getElementById('uploadArea').style.display = 'none';
                showPhotoManagement();
                updatePhotoCount();
            }
        }

        function addPhotoToGrid(file) {
            const photoId = `photo_${photoIdCounter++}`;
            const reader = new FileReader();
            
            reader.onload = function(e) {
                const photoItem = createPhotoItem(photoId, e.target.result, file);
                document.getElementById('photoGrid').appendChild(photoItem);
                
                // Fotoğrafı diziye ekle
                photoFiles.push({
                    id: photoId,
                    file: file,
                    description: '',
                    isMain: photoFiles.length === 0 // İlk fotoğraf ana fotoğraf
                });
                
                updateMainPhotoDisplay();
            };
            
            reader.readAsDataURL(file);
        }

        function createPhotoItem(photoId, imageSrc, file) {
            const div = document.createElement('div');
            div.className = 'photo-item';
            div.setAttribute('data-photo-id', photoId);
            div.draggable = true;
            
            const isMain = photoFiles.length === 0;
            
            div.innerHTML = `
                <img src="${imageSrc}" alt="Preview" class="photo-preview">
                <div class="photo-overlay">
                    <div class="photo-actions">
                        <button type="button" class="photo-action-btn star ${isMain ? 'active' : ''}" 
                                onclick="setMainPhoto('${photoId}')" title="Ana fotoğraf yap">
                            <i class="fas fa-star"></i>
                        </button>
                        <button type="button" class="photo-action-btn delete" 
                                onclick="removePhoto('${photoId}')" title="Sil">
                            <i class="fas fa-trash"></i>
                        </button>
                    </div>
                </div>
                <div class="photo-number">${photoFiles.length + 1}</div>
                <div class="photo-description">
                    <input type="text" placeholder="Açıklama..." class="form-control form-control-sm"
                           onchange="updatePhotoDescription('${photoId}', this.value)">
                </div>
            `;
            
            // Drag events
            div.addEventListener('dragstart', handlePhotoDetailDragStart);
            div.addEventListener('dragover', handlePhotoDetailDragOver);
            div.addEventListener('drop', handlePhotoDetailDrop);
            
            return div;
        }

        function setMainPhoto(photoId) {
            // Tüm ana fotoğraf işaretlerini temizle
            photoFiles.forEach(photo => photo.isMain = false);
            document.querySelectorAll('.photo-item').forEach(item => {
                item.classList.remove('main-photo');
                const starBtn = item.querySelector('.photo-action-btn.star');
                if (starBtn) starBtn.classList.remove('active');
            });
            
            // Yeni ana fotoğrafı işaretle
            const photoIndex = photoFiles.findIndex(p => p.id === photoId);
            if (photoIndex !== -1) {
                photoFiles[photoIndex].isMain = true;
                mainPhotoIndex = photoIndex;
                
                const photoElement = document.querySelector(`[data-photo-id="${photoId}"]`);
                photoElement.classList.add('main-photo');
                const starBtn = photoElement.querySelector('.photo-action-btn.star');
                if (starBtn) starBtn.classList.add('active');
                
                showToast('Ana fotoğraf güncellendi!', 'success');
            }
        }

        function removePhoto(photoId) {
            if (confirm('Bu fotoğrafı silmek istediğinizden emin misiniz?')) {
                // Array'den kaldır
                const photoIndex = photoFiles.findIndex(p => p.id === photoId);
                if (photoIndex !== -1) {
                    const wasMain = photoFiles[photoIndex].isMain;
                    photoFiles.splice(photoIndex, 1);
                    
                    // DOM'dan kaldır
                    document.querySelector(`[data-photo-id="${photoId}"]`).remove();
                    
                    // Ana fotoğraf silinmişse yeni ana fotoğraf belirle
                    if (wasMain && photoFiles.length > 0) {
                        setMainPhoto(photoFiles[0].id);
                    }
                    
                    updatePhotoCount();
                    updatePhotoNumbers();
                    
                    if (photoFiles.length === 0) {
                        hidePhotoManagement();
                        // Upload area'yı tekrar göster
                        document.getElementById('uploadArea').style.display = 'block';
                    }
                    
                    showToast('Fotoğraf silindi!', 'success');
                }
            }
        }
                    }
                    
                    updatePhotoCount();
                    
                    if (photoFiles.length === 0) {
                        hidePhotoManagement();
                    }
                    
                    showToast('Fotoğraf silindi!', 'success');
                }
            }
        }

        function updatePhotoDescription(photoId, description) {
            const photoIndex = photoFiles.findIndex(p => p.id === photoId);
            if (photoIndex !== -1) {
                photoFiles[photoIndex].description = description;
            }
        }

        function addMorePhotos() {
            document.getElementById('photoInput').click();
        }

        function updatePhotoNumbers() {
            document.querySelectorAll('.photo-item').forEach((item, index) => {
                const numberEl = item.querySelector('.photo-number');
                if (numberEl) numberEl.textContent = index + 1;
            });
        }

        function updatePhotoCount() {
            document.getElementById('photoCount').textContent = photoFiles.length;
        }

        function showPhotoManagement() {
            document.getElementById('photoManagement').style.display = 'block';
        }

        function hidePhotoManagement() {
            document.getElementById('photoManagement').style.display = 'none';
        }

        function addMorePhotos() {
            document.getElementById('photoInput').click();
        }

        function clearAllPhotos() {
            if (confirm('Tüm fotoğrafları silmek istediğinizden emin misiniz?')) {
                photoFiles = [];
                document.getElementById('photoGrid').innerHTML = '';
                hidePhotoManagement();
                // Upload area'yı tekrar göster
                document.getElementById('uploadArea').style.display = 'block';
                showToast('Tüm fotoğraflar temizlendi!', 'success');
            }
        }

        // Drag & Drop sıralama
        let draggedElement = null;

        function handlePhotoDetailDragStart(e) {
            draggedElement = e.currentTarget;
            e.currentTarget.style.opacity = '0.5';
        }

        function handlePhotoDetailDragOver(e) {
            e.preventDefault();
        }

        function handlePhotoDetailDrop(e) {
            e.preventDefault();
            
            if (draggedElement !== e.currentTarget) {
                const draggedId = draggedElement.getAttribute('data-photo-id');
                const targetId = e.currentTarget.getAttribute('data-photo-id');
                
                // Array'de sırayı değiştir
                const draggedIndex = photoFiles.findIndex(p => p.id === draggedId);
                const targetIndex = photoFiles.findIndex(p => p.id === targetId);
                
                const draggedPhoto = photoFiles.splice(draggedIndex, 1)[0];
                photoFiles.splice(targetIndex, 0, draggedPhoto);
                
                // DOM'da sırayı değiştir
                const grid = document.getElementById('photoGrid');
                const targetElement = e.currentTarget;
                
                if (draggedIndex < targetIndex) {
                    targetElement.parentNode.insertBefore(draggedElement, targetElement.nextSibling);
                } else {
                    targetElement.parentNode.insertBefore(draggedElement, targetElement);
                }
                
                showToast('Fotoğraf sırası güncellendi!', 'success');
            }
            
            draggedElement.style.opacity = '1';
            draggedElement = null;
        }

        // Toast notification sistemi
        function showToast(message, type = 'info') {
            const toast = document.createElement('div');
            toast.className = `alert alert-${type === 'error' ? 'danger' : type === 'success' ? 'success' : 'info'} toast-notification`;
            toast.style.cssText = `
                position: fixed;
                top: 20px;
                right: 20px;
                z-index: 9999;
                padding: 15px 20px;
                border-radius: 10px;
                box-shadow: 0 5px 15px rgba(0,0,0,0.2);
                animation: slideIn 0.3s ease;
            `;
            toast.innerHTML = `
                <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : type === 'success' ? 'check-circle' : 'info-circle'} me-2"></i>
                ${message}
            `;
            
            document.body.appendChild(toast);
            
            setTimeout(() => {
                toast.style.animation = 'slideOut 0.3s ease';
                setTimeout(() => toast.remove(), 300);
            }, 3000);
        }

        // CSS animasyonları
        const style = document.createElement('style');
        style.textContent = `
            @keyframes slideIn {
                from { transform: translateX(100%); opacity: 0; }
                to { transform: translateX(0); opacity: 1; }
            }
            @keyframes slideOut {
                from { transform: translateX(0); opacity: 1; }
                to { transform: translateX(100%); opacity: 0; }
            }
        `;
        document.head.appendChild(style);

        // Form submit edildiğinde fotoğrafları hazırla
        document.getElementById('propertyForm').addEventListener('submit', function(e) {
            if (photoFiles.length > 0) {
                // Ana fotoğrafı en başa taşı
                const mainPhoto = photoFiles.find(p => p.isMain);
                if (mainPhoto) {
                    const mainIndex = photoFiles.indexOf(mainPhoto);
                    if (mainIndex > 0) {
                        photoFiles.splice(mainIndex, 1);
                        photoFiles.unshift(mainPhoto);
                    }
                }
                
                // Hidden input oluştur ve fotoğrafları ekle
                const existingInput = document.querySelector('input[name="photo_data"]');
                if (existingInput) existingInput.remove();
                
                const photoDataInput = document.createElement('input');
                photoDataInput.type = 'hidden';
                photoDataInput.name = 'photo_data';
                photoDataInput.value = JSON.stringify(photoFiles.map(p => ({
                    id: p.id,
                    description: p.description,
                    isMain: p.isMain
                })));
                
                this.appendChild(photoDataInput);
                
                console.log('Fotoğraf verileri hazırlandı:', photoFiles.length, 'adet');
            }
        });
    </script>
    
    <!-- Bootstrap JavaScript -->
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>