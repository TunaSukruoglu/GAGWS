<?php
// Hızlı DB Test
echo "<h3>Veritabanı Bağlantı Testi</h3>";

// Root kullanıcısı ile test
try {
    $conn = new mysqli('localhost', 'root', '');
    if ($conn->connect_error) {
        echo "❌ Root (şifresiz) bağlantı hatası: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Root (şifresiz) bağlantı başarılı!<br>";
        
        // gokhanaydinli_db veritabanını oluştur
        $conn->query("CREATE DATABASE IF NOT EXISTS gokhanaydinli_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci");
        echo "✅ gokhanaydinli_db veritabanı hazır<br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Root test hatası: " . $e->getMessage() . "<br>";
}

echo "<hr>";

// Root + root şifresi ile test
try {
    $conn = new mysqli('localhost', 'root', 'root');
    if ($conn->connect_error) {
        echo "❌ Root (root şifresi) bağlantı hatası: " . $conn->connect_error . "<br>";
    } else {
        echo "✅ Root (root şifresi) bağlantı başarılı!<br>";
        $conn->close();
    }
} catch (Exception $e) {
    echo "❌ Root+root test hatası: " . $e->getMessage() . "<br>";
}
?>
