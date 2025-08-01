<?php
// MİNİMAL ADD-PROPERTY TESTİ
error_reporting(E_ALL);
ini_set('display_errors', 1);

// OUTPUT BUFFER BAŞLAT - Header sorununu önlemek için
ob_start();

try {
    // Session'ı önce başlat
    session_start();
    
    echo "1. PHP çalışıyor<br>";
    echo "2. Session başlatıldı<br>";
    
    include '../db.php';
    echo "3. Database include edildi<br>";
    
    if ($conn) {
        echo "4. Database bağlantısı var<br>";
    } else {
        throw new Exception("Database bağlantısı yok");
    }
    
    echo "5. Tüm temel kontroller başarılı<br>";
    
} catch (Exception $e) {
    echo "HATA: " . $e->getMessage() . "<br>";
    echo "Stack: " . $e->getTraceAsString();
}

echo "<hr>";
echo "<h3>Şimdi sadece HTML kısmını test edelim:</h3>";
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Minimal Test</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="alert alert-success">
            <h4>HTML Kısmı da Çalışıyor!</h4>
            <p>Eğer bu mesajı görebiliyorsanız, temel yapı çalışıyor demektir.</p>
            <hr>
            <p><strong>Sonraki adım:</strong> Ana add-property.php dosyasını kontrol edin.</p>
            <a href="add-property.php" class="btn btn-primary">add-property.php'yi Test Et</a>
        </div>
    </div>
</body>
</html>
