<?php
// Users tablosunu güncelleyip email doğrulama sistemi için hazırlama
include 'db.php';

echo "<h2>📋 Users Tablosu Güncelleniyor...</h2>";

// Önce mevcut tablo yapısını kontrol et
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<h3>🔍 Mevcut Tablo Yapısı:</h3>";
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Sütun</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td>" . $row['Field'] . "</td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "</tr>";
    }
    echo "</table><br>";
}

// Eksik sütunları ekle
$alterations = [
    "ADD COLUMN phone VARCHAR(20) DEFAULT NULL COMMENT 'Telefon numarası'",
    "ADD COLUMN verification_token VARCHAR(255) DEFAULT NULL COMMENT 'Email doğrulama token'", 
    "ADD COLUMN is_verified TINYINT(1) DEFAULT 0 COMMENT '0: Doğrulanmamış, 1: Doğrulanmış'",
    "ADD COLUMN verified_at TIMESTAMP NULL DEFAULT NULL COMMENT 'Doğrulama tarihi'",
    "ADD COLUMN created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP COMMENT 'Kayıt tarihi'",
    "ADD COLUMN updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP COMMENT 'Güncelleme tarihi'"
];

foreach ($alterations as $alter) {
    $sql = "ALTER TABLE users " . $alter;
    
    if ($conn->query($sql) === TRUE) {
        echo "✅ Başarılı: " . $alter . "<br>";
    } else {
        // Sütun zaten varsa hata vermez
        if (strpos($conn->error, "Duplicate column name") !== false) {
            echo "ℹ️ Zaten var: " . explode("'", $alter)[1] . " sütunu<br>";
        } else {
            echo "❌ Hata: " . $alter . " - " . $conn->error . "<br>";
        }
    }
}

echo "<br><h3>📊 Güncellenmiş Tablo Yapısı:</h3>";
$result = $conn->query("DESCRIBE users");
if ($result) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr><th>Sütun</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>";
    while($row = $result->fetch_assoc()) {
        echo "<tr>";
        echo "<td><strong>" . $row['Field'] . "</strong></td>";
        echo "<td>" . $row['Type'] . "</td>";
        echo "<td>" . $row['Null'] . "</td>";
        echo "<td>" . $row['Key'] . "</td>";
        echo "<td>" . $row['Default'] . "</td>";
        echo "<td>" . $row['Extra'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
}

// Index'leri ekle
$indexes = [
    "CREATE INDEX idx_email ON users(email)",
    "CREATE INDEX idx_verification_token ON users(verification_token)", 
    "CREATE INDEX idx_is_verified ON users(is_verified)"
];

echo "<br><h3>🔍 Index'ler ekleniyor:</h3>";
foreach ($indexes as $index) {
    if ($conn->query($index) === TRUE) {
        echo "✅ Index eklendi: " . $index . "<br>";
    } else {
        if (strpos($conn->error, "Duplicate key name") !== false) {
            echo "ℹ️ Index zaten var<br>";
        } else {
            echo "❌ Index hatası: " . $conn->error . "<br>";
        }
    }
}

$conn->close();
?>

<style>
body { font-family: Arial, sans-serif; max-width: 800px; margin: 20px auto; padding: 20px; }
table { width: 100%; margin: 10px 0; }
th, td { padding: 8px; text-align: left; border: 1px solid #ddd; }
th { background-color: #f2f2f2; }
</style>
