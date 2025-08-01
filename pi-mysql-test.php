<?php
echo "<h3>🍓 Raspberry Pi MySQL Bağlantı Testi</h3>";
echo "<p>Pi sunucunuz için farklı kullanıcı/şifre kombinasyonlarını test ediyorum...</p>";

$pi_configs = [
    [
        'user' => 'pi',
        'pass' => 'raspberry',
        'desc' => 'Pi varsayılan kullanıcı/şifre'
    ],
    [
        'user' => 'pi',
        'pass' => '',
        'desc' => 'Pi kullanıcı, şifresiz'
    ],
    [
        'user' => 'root',
        'pass' => 'raspberry',
        'desc' => 'Root kullanıcı, Pi şifresi'
    ],
    [
        'user' => 'root',
        'pass' => '',
        'desc' => 'Root kullanıcı, şifresiz'
    ],
    [
        'user' => 'phpmyadmin',
        'pass' => 'raspberry',
        'desc' => 'phpMyAdmin kullanıcısı'
    ],
    [
        'user' => 'mysql',
        'pass' => 'raspberry',
        'desc' => 'MySQL kullanıcısı'
    ]
];

$host = 'localhost';
$port = 3306;
$found_working = false;

foreach ($pi_configs as $config) {
    echo "<h4>Test: {$config['desc']}</h4>";
    echo "<p>Kullanıcı: <strong>{$config['user']}</strong> | Şifre: <strong>" . ($config['pass'] ?: 'şifresiz') . "</strong></p>";
    
    try {
        $conn = new mysqli($host, $config['user'], $config['pass'], null, $port);
        
        if ($conn->connect_error) {
            echo "❌ <span style='color: red;'>Başarısız:</span> " . $conn->connect_error . "<br>";
        } else {
            echo "✅ <span style='color: green;'><strong>BAŞARILI!</strong></span><br>";
            
            // Mevcut veritabanlarını listele
            echo "<strong>Mevcut veritabanları:</strong> ";
            $result = $conn->query("SHOW DATABASES");
            $databases = [];
            while ($row = $result->fetch_assoc()) {
                $databases[] = $row['Database'];
            }
            echo implode(', ', $databases) . "<br>";
            
            // MySQL versiyonunu göster
            $version = $conn->get_server_info();
            echo "<strong>MySQL/MariaDB Versiyonu:</strong> $version<br>";
            
            echo "<div style='background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
            echo "<strong>✅ Bu ayarları db.php dosyasına kopyalayın:</strong><br>";
            echo "<code>";
            echo "\$servername = \"localhost\";<br>";
            echo "\$username = \"{$config['user']}\";<br>";
            echo "\$password = \"{$config['pass']}\";<br>";
            echo "\$dbname = \"gokhanaydinli_db\";<br>";
            echo "\$port = 3306;<br>";
            echo "</code>";
            echo "</div>";
            
            $conn->close();
            $found_working = true;
            break; // İlk çalışan bağlantıyı bulduk, diğerlerini test etmeye gerek yok
        }
        
    } catch (Exception $e) {
        echo "❌ <span style='color: red;'>Hata:</span> " . $e->getMessage() . "<br>";
    }
    
    echo "<hr>";
}

if (!$found_working) {
    echo "<div style='background: #ffe6e6; padding: 15px; margin: 10px 0; border-radius: 5px;'>";
    echo "<h4>❌ Hiçbir bağlantı çalışmadı!</h4>";
    echo "<p><strong>Çözüm önerileri:</strong></p>";
    echo "<ul>";
    echo "<li>Pi'nizde MySQL/MariaDB servisinin çalıştığından emin olun: <code>sudo systemctl status mysql</code></li>";
    echo "<li>Eğer çalışmıyorsa başlatın: <code>sudo systemctl start mysql</code></li>";
    echo "<li>phpMyAdmin'e giriş yapabiliyorsanız, oradaki kullanıcı bilgilerini kullanın</li>";
    echo "<li>Farklı bir port kullanıyor olabilir (3307, 3308 vs.)</li>";
    echo "</ul>";
    echo "</div>";
}

echo "<h4>💡 phpMyAdmin Bilgileri:</h4>";
echo "<p>Eğer phpMyAdmin'e giriş yapabiliyorsanız:</p>";
echo "<ul>";
echo "<li>Giriş yaptığınız kullanıcı adı/şifresini db.php'de kullanın</li>";
echo "<li>phpMyAdmin genellikle şu adreste çalışır: <code>http://192.168.x.x/phpmyadmin</code></li>";
echo "<li>Pi'da genellikle kullanıcı: <strong>pi</strong> veya <strong>root</strong></li>";
echo "</ul>";
?>
