<?php
// 🚀 ULTRA FAST PROPERTY PROCESSING
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Performance measurement
$start_time = microtime(true);

// Session check - ultra fast
session_start();
if (!isset($_SESSION['user_id'])) {
    die(json_encode(['status' => 'error', 'message' => 'Oturum bulunamadı']));
}

// Only process POST requests
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    header('Location: add-property-ultra.php');
    exit;
}

include __DIR__ . '/../db.php';

// Get user data quickly
$user_id = $_SESSION['user_id'];
$user_query = $pdo->prepare("SELECT id, name, role FROM users WHERE id = ? LIMIT 1");
$user_query->execute([$user_id]);
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    die(json_encode(['status' => 'error', 'message' => 'Kullanıcı bulunamadı']));
}

try {
    // 🐛 DEBUG: POST verilerini kontrol et
    error_log("POST Data: " . print_r($_POST, true));
    error_log("FILES Data: " . print_r($_FILES, true));
    
    // ⚡ Fast validation
    $required_fields = ['title', 'price', 'city', 'district', 'property_type', 'listing_type'];
    $missing_fields = [];
    
    foreach ($required_fields as $field) {
        if (empty($_POST[$field])) {
            $missing_fields[] = $field;
        }
    }
    
    if (!empty($missing_fields)) {
        // DEBUG: Hangi alanlar eksik
        error_log("Missing fields: " . implode(', ', $missing_fields));
        throw new Exception('Zorunlu alanlar eksik: ' . implode(', ', $missing_fields));
    }
    
    // ⚡ Fast file upload handling
    $main_image = null;
    $additional_images = [];
    
    if (isset($_FILES['main_image']) && $_FILES['main_image']['error'] === UPLOAD_ERR_OK) {
        $main_image = uploadImage($_FILES['main_image'], 'main');
    }
    
    if (isset($_FILES['additional_images'])) {
        foreach ($_FILES['additional_images']['tmp_name'] as $index => $tmp_name) {
            if ($_FILES['additional_images']['error'][$index] === UPLOAD_ERR_OK) {
                $uploaded = uploadImage([
                    'tmp_name' => $tmp_name,
                    'name' => $_FILES['additional_images']['name'][$index],
                    'type' => $_FILES['additional_images']['type'][$index],
                    'size' => $_FILES['additional_images']['size'][$index]
                ], 'additional');
                if ($uploaded) {
                    $additional_images[] = $uploaded;
                }
            }
        }
    }
    
    // ⚡ Ultra fast database insert
    $sql = "INSERT INTO properties (
        user_id, title, description, price, city, district, neighborhood, 
        address, property_type, listing_type, rooms, area, main_image, 
        additional_images, status, created_at
    ) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, 'active', NOW())";
    
    $stmt = $pdo->prepare($sql);
    $result = $stmt->execute([
        $user_id,
        trim($_POST['title']),
        trim($_POST['description'] ?? ''),
        (float)$_POST['price'],
        trim($_POST['city']),
        trim($_POST['district']),
        trim($_POST['neighborhood'] ?? ''),
        trim($_POST['address'] ?? ''),
        trim($_POST['property_type']),
        trim($_POST['listing_type']),
        trim($_POST['rooms'] ?? ''),
        (int)($_POST['area'] ?? 0),
        $main_image,
        json_encode($additional_images),
    ]);
    
    if ($result) {
        $property_id = $pdo->lastInsertId();
        
        // Clear draft from localStorage
        echo "<script>localStorage.removeItem('propertyDraft');</script>";
        
        // Success redirect with ultra fast performance info
        $end_time = microtime(true);
        $processing_time = ($end_time - $start_time) * 1000;
        
        header("Location: add-property-ultra.php?success=1&time=" . round($processing_time, 2) . "&id=" . $property_id);
        exit;
    } else {
        throw new Exception('Veritabanı kayıt hatası');
    }
    
} catch (Exception $e) {
    $error_message = $e->getMessage();
    header("Location: add-property-ultra.php?error=" . urlencode($error_message));
    exit;
}

// ⚡ Ultra fast image upload function
function uploadImage($file, $type = 'main') {
    // Quick type validation
    $allowed_types = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($file['type'], $allowed_types)) {
        return null;
    }
    
    // Quick size check (max 5MB)
    if ($file['size'] > 5 * 1024 * 1024) {
        return null;
    }
    
    // Ultra fast file naming
    $extension = pathinfo($file['name'], PATHINFO_EXTENSION);
    $filename = uniqid($type . '_', true) . '.' . strtolower($extension);
    
    // Fast directory creation
    $upload_dir = __DIR__ . '/../uploads/properties/';
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0755, true);
    }
    
    $destination = $upload_dir . $filename;
    
    // Ultra fast move
    if (move_uploaded_file($file['tmp_name'], $destination)) {
        return 'uploads/properties/' . $filename;
    }
    
    return null;
}

// Performance log
$end_time = microtime(true);
$processing_time = ($end_time - $start_time) * 1000;
error_log("🚀 Ultra Fast Property Processing: " . round($processing_time, 2) . "ms");
?>
