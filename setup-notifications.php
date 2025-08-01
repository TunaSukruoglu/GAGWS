<?php
include 'db.php';

echo "📧 BİLDİRİM AYARLARI SİSTEMİ KURULUMU\n";
echo "====================================\n\n";

// Bildirim sütunlarını kontrol et ve ekle
$notification_columns = [
    'email_notifications' => 'TINYINT(1) DEFAULT 1',
    'sms_notifications' => 'TINYINT(1) DEFAULT 0', 
    'marketing_emails' => 'TINYINT(1) DEFAULT 0'
];

foreach ($notification_columns as $column => $definition) {
    $check_query = "SHOW COLUMNS FROM users LIKE '$column'";
    $result = $conn->query($check_query);
    
    if ($result->num_rows > 0) {
        echo "✅ $column sütunu mevcut\n";
    } else {
        echo "❌ $column sütunu YOK - ekleniyor...\n";
        $add_query = "ALTER TABLE users ADD COLUMN $column $definition";
        if ($conn->query($add_query)) {
            echo "✅ $column sütunu başarıyla eklendi!\n";
        } else {
            echo "❌ $column ekleme hatası: " . $conn->error . "\n";
        }
    }
}

echo "\n🔧 Bildirim sistemi hazır!\n";
echo "📱 Kullanıcılar artık bildirim tercihlerini yönetebilir.\n";
?>
