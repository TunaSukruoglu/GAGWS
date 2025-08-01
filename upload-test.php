<?php
echo "=== RESİM YÜKLEME SİSTEMİ TEST ===\n\n";

// 1. Upload klasörü kontrolü
echo "1. UPLOAD KLASÖRÜ KONTROLLERI:\n";
$upload_dirs = [
    'dashboard/uploads/properties/',
    'uploads/properties/',
    'images/'
];

foreach($upload_dirs as $dir) {
    if (is_dir($dir)) {
        echo "✓ $dir exists\n";
        echo "  - Writable: " . (is_writable($dir) ? "YES" : "NO") . "\n";
        echo "  - Files: " . count(array_diff(scandir($dir), ['.', '..'])) . "\n";
    } else {
        echo "✗ $dir does not exist\n";
    }
}

// 2. PHP Upload ayarları
echo "\n2. PHP UPLOAD AYARLARI:\n";
echo "upload_max_filesize: " . ini_get('upload_max_filesize') . "\n";
echo "post_max_size: " . ini_get('post_max_size') . "\n";
echo "max_file_uploads: " . ini_get('max_file_uploads') . "\n";
echo "file_uploads: " . (ini_get('file_uploads') ? 'ON' : 'OFF') . "\n";

// 3. Son eklenen propertyler ve resim durumu
echo "\n3. SON EKLENDİĞİNİZ İLANLAR:\n";
include 'db.php';
$stmt = $conn->prepare('SELECT id, title, images, created_at FROM properties ORDER BY id DESC LIMIT 3');
$stmt->execute();
$properties = $stmt->fetchAll(PDO::FETCH_ASSOC);

foreach($properties as $prop) {
    echo "ID: {$prop['id']} | {$prop['title']}\n";
    echo "  Tarih: {$prop['created_at']}\n";
    echo "  Resimler: " . (empty($prop['images']) ? "YOK" : $prop['images']) . "\n";
    
    if (!empty($prop['images'])) {
        $images = json_decode($prop['images'], true);
        if (is_array($images)) {
            foreach($images as $img) {
                $path = "dashboard/uploads/properties/" . basename($img);
                echo "    - " . basename($img) . " : " . (file_exists($path) ? "✓" : "✗") . "\n";
            }
        }
    }
    echo "\n";
}

// 4. Test upload formu
echo "\n4. TEST UPLOAD FORMU OLUŞTURULUYOR...\n";
?>

<!DOCTYPE html>
<html>
<head>
    <title>Resim Upload Test</title>
    <style>
        body { font-family: Arial; margin: 20px; }
        .upload-box { border: 2px dashed #ccc; padding: 20px; margin: 20px 0; }
        .result { background: #f0f0f0; padding: 10px; margin: 10px 0; }
    </style>
</head>
<body>
    <h2>🔧 Resim Upload Test Sistemi</h2>
    
    <form method="post" enctype="multipart/form-data">
        <div class="upload-box">
            <h3>Test Resmi Seçin:</h3>
            <input type="file" name="test_image" accept="image/*" required>
            <br><br>
            <button type="submit" name="test_upload">Test Upload</button>
        </div>
    </form>

<?php
if (isset($_POST['test_upload']) && isset($_FILES['test_image'])) {
    echo "<div class='result'>";
    echo "<h3>📤 UPLOAD TEST SONUCU:</h3>";
    
    $file = $_FILES['test_image'];
    echo "Dosya adı: " . $file['name'] . "<br>";
    echo "Boyut: " . $file['size'] . " bytes<br>";
    echo "Tip: " . $file['type'] . "<br>";
    echo "Hata kodu: " . $file['error'] . "<br>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $upload_dir = 'dashboard/uploads/properties/';
        if (!is_dir($upload_dir)) {
            mkdir($upload_dir, 0755, true);
            echo "Upload klasörü oluşturuldu<br>";
        }
        
        $filename = 'test_' . time() . '_' . $file['name'];
        $target_path = $upload_dir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $target_path)) {
            echo "✅ <strong>BAŞARILI!</strong> Resim yüklendi: $filename<br>";
            echo "Dosya yolu: $target_path<br>";
            echo "Dosya var mı: " . (file_exists($target_path) ? "Evet" : "Hayır") . "<br>";
            
            // Show-image.php ile test et
            echo "<br>🖼️ <strong>Resim Görüntüleme Testi:</strong><br>";
            echo "<img src='show-image.php?img=$filename&v=" . time() . "' style='max-width: 200px; border: 1px solid #ccc;'><br>";
            echo "URL: show-image.php?img=$filename<br>";
        } else {
            echo "❌ <strong>HATA!</strong> Resim yüklenemedi<br>";
            echo "Hedef yol: $target_path<br>";
            echo "Klasör yazılabilir mi: " . (is_writable($upload_dir) ? "Evet" : "Hayır") . "<br>";
        }
    } else {
        echo "❌ <strong>UPLOAD HATASI!</strong> Hata kodu: " . $file['error'] . "<br>";
        
        $upload_errors = [
            UPLOAD_ERR_INI_SIZE => 'Dosya upload_max_filesize limitini aşıyor',
            UPLOAD_ERR_FORM_SIZE => 'Dosya form MAX_FILE_SIZE limitini aşıyor',
            UPLOAD_ERR_PARTIAL => 'Dosya kısmen yüklendi',
            UPLOAD_ERR_NO_FILE => 'Dosya yüklenmedi',
            UPLOAD_ERR_NO_TMP_DIR => 'Geçici klasör eksik',
            UPLOAD_ERR_CANT_WRITE => 'Diske yazılamıyor',
            UPLOAD_ERR_EXTENSION => 'PHP uzantısı dosya yüklemeyi durdurdu'
        ];
        
        if (isset($upload_errors[$file['error']])) {
            echo "Hata açıklaması: " . $upload_errors[$file['error']] . "<br>";
        }
    }
    echo "</div>";
}
?>

    <h3>📋 Mevcut Upload Klasöründeki Dosyalar:</h3>
    <div class="result">
<?php
$upload_dir = 'dashboard/uploads/properties/';
if (is_dir($upload_dir)) {
    $files = array_diff(scandir($upload_dir), ['.', '..']);
    if (empty($files)) {
        echo "Klasör boş";
    } else {
        foreach($files as $file) {
            echo "$file<br>";
        }
    }
} else {
    echo "Upload klasörü bulunamadı";
}
?>
    </div>
</body>
</html>
