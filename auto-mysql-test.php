<?php
echo "<h3>🔍 MySQL Şifre Testi</h3>";
echo "<p>Farklı şifre kombinasyonlarını test ediyorum...</p>";

$test_passwords = [
    '' => 'Şifresiz (XAMPP varsayılan)',
    'root' => 'root şifresi (WAMP varsayılan)',
    'mysql' => 'mysql şifresi',
    'admin' => 'admin şifresi',
    '123456' => 'basit şifre',
    'password' => 'password şifresi',
    '113041122839sS?!_' => 'Eski özel şifre'
];

$host = 'localhost';
$port = 3306;
$user = 'root';

foreach ($test_passwords as $password => $description) {
    echo "<h4>Test: $description</h4>";
    
    try {
        $conn = new mysqli($host, $user, $password, null, $port);
        
        if ($conn->connect_error) {
            echo "❌ <span style='color: red;'>Başarısız:</span> " . $conn->connect_error . "<br>";
        } else {
            echo "✅ <span style='color: green;'><strong>BAŞARILI!</strong></span> Root şifresi: <strong>'$password'</strong><br>";
            echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>Bu ayarları db.php dosyasına kopyalayın:</strong><br>";
            echo "<code>";
            echo "\$servername = \"localhost\";<br>";
            echo "\$username = \"root\";<br>";
            echo "\$password = \"$password\";<br>";
            echo "\$dbname = \"gokhanaydinli_db\";<br>";
            echo "\$port = 3306;<br>";
            echo "</code>";
            echo "</div>";
            
            // Veritabanlarını listele
            $result = $conn->query("SHOW DATABASES");
            echo "<strong>Mevcut veritabanları:</strong> ";
            while ($row = $result->fetch_assoc()) {
                echo $row['Database'] . " ";
            }
            echo "<br>";
            
            $conn->close();
            
            // İlk başarılı bağlantıyı bulduk, diğerlerini test etmeye gerek yok
            echo "<hr><h3>✅ Bağlantı bulundu! Diğer testler durduruluyor.</h3>";
            break;
        }
        
    } catch (Exception $e) {
        echo "❌ <span style='color: red;'>Hata:</span> " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
}

echo "<h4>💡 İpucu:</h4>";
echo "<ul>";
echo "<li>Hiçbiri çalışmıyorsa MySQL servisiniz çalışmıyor olabilir</li>";
echo "<li>XAMPP Control Panel'den veya WAMP'tan MySQL'i başlatın</li>";
echo "<li>Farklı bir port kullanıyor olabilirsiniz (3307, 3308 vs.)</li>";
echo "</ul>";
?>
