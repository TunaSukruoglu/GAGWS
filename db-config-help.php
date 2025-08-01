<?php
// Farklı MySQL konfigürasyonları için db.php seçenekleri

echo "<h3>DB.php Konfigürasyon Seçenekleri</h3>";
echo "<p>Aşağıdaki seçeneklerden birini db.php'nin başına kopyalayın:</p>";

echo "<h4>Seçenek 1: XAMPP (Root şifresiz)</h4>";
echo "<pre>";
echo '$servername = "localhost";' . "\n";
echo '$username = "root";' . "\n";
echo '$password = "";' . "\n";
echo '$dbname = "gokhanaydinli_db";' . "\n";
echo '$port = 3306;' . "\n";
echo "</pre>";

echo "<h4>Seçenek 2: WAMP (Root + root şifresi)</h4>";
echo "<pre>";
echo '$servername = "localhost";' . "\n";
echo '$username = "root";' . "\n";
echo '$password = "root";' . "\n";
echo '$dbname = "gokhanaydinli_db";' . "\n";
echo '$port = 3306;' . "\n";
echo "</pre>";

echo "<h4>Seçenek 3: Custom MySQL</h4>";
echo "<pre>";
echo '$servername = "localhost";' . "\n";
echo '$username = "your_username";' . "\n";
echo '$password = "your_password";' . "\n";
echo '$dbname = "gokhanaydinli_db";' . "\n";
echo '$port = 3306;' . "\n";
echo "</pre>";

echo "<hr>";
echo "<p><strong>Not:</strong> MySQL servisinizin çalıştığından emin olun!</p>";
echo "<p>XAMPP: XAMPP Control Panel'den MySQL'i start edin</p>";
echo "<p>WAMP: WAMP trayicon'dan MySQL service'i start edin</p>";
?>
