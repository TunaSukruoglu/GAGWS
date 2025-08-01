<?php
// Veritabanı bağlantısını test et
echo "<h3>Veritabanı Bağlantısı Test Ediliyor...</h3>";

try {
    include 'db.php';
    echo "<p>✅ <strong>Veritabanı bağlantısı başarılı!</strong></p>";
    
    // Properties tablosu var mı kontrol et
    $check_table = $conn->query("SHOW TABLES LIKE 'properties'");
    if ($check_table->num_rows > 0) {
        echo "<p>✅ <strong>Properties tablosu mevcut</strong></p>";
        
        // Category kolonu var mı kontrol et
        $check_category = $conn->query("SHOW COLUMNS FROM properties LIKE 'category'");
        if ($check_category->num_rows > 0) {
            echo "<p>✅ <strong>Category kolonu mevcut</strong></p>";
        } else {
            echo "<p>❌ <strong>Category kolonu eksik!</strong></p>";
        }
        
        // Tablo yapısını göster
        echo "<h4>Mevcut Properties Tablosu Kolonları:</h4>";
        echo "<ul>";
        $result = $conn->query("SHOW COLUMNS FROM properties");
        while ($row = $result->fetch_assoc()) {
            echo "<li><strong>" . $row['Field'] . "</strong> (" . $row['Type'] . ")</li>";
        }
        echo "</ul>";
        
        // Kayıt sayısını göster
        $count_result = $conn->query("SELECT COUNT(*) as total FROM properties");
        $count = $count_result->fetch_assoc()['total'];
        echo "<p><strong>Toplam Property Kayıtları:</strong> " . $count . "</p>";
        
    } else {
        echo "<p>❌ <strong>Properties tablosu bulunamadı!</strong></p>";
        echo "<p>İlk kez çalıştırma - Tablolar otomatik oluşturulacak.</p>";
    }
    
    // Users tablosunu da kontrol et
    $check_users = $conn->query("SHOW TABLES LIKE 'users'");
    if ($check_users->num_rows > 0) {
        echo "<p>✅ <strong>Users tablosu mevcut</strong></p>";
        $user_count = $conn->query("SELECT COUNT(*) as total FROM users")->fetch_assoc()['total'];
        echo "<p><strong>Toplam Kullanıcı Sayısı:</strong> " . $user_count . "</p>";
    } else {
        echo "<p>❌ <strong>Users tablosu bulunamadı!</strong></p>";
    }
    
    echo "<hr>";
    echo "<p style='color: green;'><strong>✅ Veritabanı sistemi çalışıyor!</strong></p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'><strong>❌ Hata:</strong> " . $e->getMessage() . "</p>";
    echo "<hr>";
    echo "<h4>Çözüm Önerileri:</h4>";
    echo "<ul>";
    echo "<li>MySQL servisinizin çalıştığından emin olun</li>";
    echo "<li>XAMPP kullanıyorsanız: XAMPP Control Panel'den MySQL'i start edin</li>";
    echo "<li>WAMP kullanıyorsanız: WAMP icon'dan MySQL service'i start edin</li>";
    echo "<li>db.php dosyasındaki kullanıcı adı/şifre bilgilerini kontrol edin</li>";
    echo "</ul>";
    echo "<p><a href='db-config-help.php'>DB Konfigürasyon Yardımı</a></p>";
}
?>
