<?php
include 'db.php';

echo "📋 USERS TABLOSU KONTROL\n";
echo "========================\n\n";

// Tablo yapısını kontrol et
$result = $conn->query("SHOW COLUMNS FROM users");

echo "Mevcut sütunlar:\n";
echo "─────────────────\n";
while ($row = $result->fetch_assoc()) {
    echo "• {$row['Field']} ({$row['Type']})\n";
}

// profile_image sütunu var mı?
$profile_img_check = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");

if ($profile_img_check->num_rows > 0) {
    echo "\n✅ profile_image sütunu mevcut\n";
} else {
    echo "\n❌ profile_image sütunu YOK - ekleniyor...\n";
    $add_column = $conn->query("ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER email");
    if ($add_column) {
        echo "✅ profile_image sütunu başarıyla eklendi!\n";
    } else {
        echo "❌ Sütun ekleme hatası: " . $conn->error . "\n";
    }
}

// bio sütunu da kontrol et
$bio_check = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");

if ($bio_check->num_rows > 0) {
    echo "✅ bio sütunu mevcut\n";
} else {
    echo "❌ bio sütunu YOK - ekleniyor...\n";
    $add_bio = $conn->query("ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER profile_image");
    if ($add_bio) {
        echo "✅ bio sütunu başarıyla eklendi!\n";
    } else {
        echo "❌ Bio sütun ekleme hatası: " . $conn->error . "\n";
    }
}

echo "\n🔧 Profil sistemi hazır!\n";
?>
