<?php
include 'db.php';

echo "🔧 PROFİL RESMİ TEST\n";
echo "===================\n\n";

// Test için kullanıcı ID'si (root kullanıcısı)
$user_id = 13; // Root kullanıcı ID'si

// Kullanıcı bilgilerini çek
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

if (!$user) {
    echo "❌ Kullanıcı bulunamadı!\n";
    exit;
}

echo "👤 Test Kullanıcısı: {$user['name']} (ID: {$user['id']})\n";
echo "📧 Email: {$user['email']}\n";
echo "🖼️ Mevcut profil resmi: " . ($user['profile_image'] ?: '[YOK]') . "\n\n";

// Upload klasörü kontrolü
$upload_dir = __DIR__ . '/uploads/profiles/';
echo "📁 Upload klasörü: $upload_dir\n";
echo "📁 Klasör var mı: " . (is_dir($upload_dir) ? "✅ EVET" : "❌ HAYIR") . "\n";
echo "📁 Yazılabilir mi: " . (is_writable($upload_dir) ? "✅ EVET" : "❌ HAYIR") . "\n";

if (!is_dir($upload_dir)) {
    echo "📁 Klasör oluşturuluyor...\n";
    mkdir($upload_dir, 0777, true);
    echo "📁 Klasör oluşturuldu!\n";
}

if (!is_writable($upload_dir)) {
    echo "📁 İzin veriliyor...\n";
    chmod($upload_dir, 0777);
    echo "📁 İzin verildi!\n";
}

// Test resmi oluştur (basit 1x1 pixel PNG)
$test_image_data = base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAYAAAAfFcSJAAAADUlEQVR42mNkYPhfDwAChAHChAhMfQAAAABJRU5ErkJggg==');
$test_filename = 'profile_' . $user_id . '_' . time() . '.png';
$test_filepath = $upload_dir . $test_filename;

echo "🖼️ Test resmi oluşturuluyor: $test_filename\n";

if (file_put_contents($test_filepath, $test_image_data)) {
    echo "✅ Test resmi oluşturuldu!\n";
    
    // Database'i güncelle
    $profile_image_path = 'uploads/profiles/' . $test_filename;
    $update_query = $conn->prepare("UPDATE users SET profile_image = ?, updated_at = NOW() WHERE id = ?");
    $update_query->bind_param("si", $profile_image_path, $user_id);
    
    if ($update_query->execute()) {
        echo "✅ Database güncellendi!\n";
        echo "🎯 Profil resmi yolu: $profile_image_path\n";
        
        // Kontrol et
        $check_stmt = $conn->prepare("SELECT profile_image FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result()->fetch_assoc();
        
        echo "🔍 Database'den okunan: " . $result['profile_image'] . "\n";
        echo "🔍 Dosya var mı: " . (file_exists(__DIR__ . '/' . $result['profile_image']) ? "✅ EVET" : "❌ HAYIR") . "\n";
        
        if (file_exists(__DIR__ . '/' . $result['profile_image'])) {
            echo "🔍 Dosya boyutu: " . filesize(__DIR__ . '/' . $result['profile_image']) . " bytes\n";
        }
        
    } else {
        echo "❌ Database güncellenirken hata: " . $conn->error . "\n";
    }
    
} else {
    echo "❌ Test resmi oluşturulamadı!\n";
}

echo "\n🌐 Şimdi profile.php sayfasını açın ve test edin!\n";
?>
