<?php
// Migration tamamlandıktan sonra bu dosyaları sil
$files_to_delete = [
    'debug-parking.php',
    'fix-parking.php', 
    'check-property-51.php',
    'fix-room-count.php',
    'fix-parking-usage.php'
];

echo "<h3>Migration Temizlik</h3>";
echo "<p>Migration işlemleri tamamlandıktan sonra debug dosyalarını silebilirsiniz:</p>";

foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        echo "<p>✓ <strong>$file</strong> - <a href='?delete=$file' onclick=\"return confirm('$file dosyasını silmek istediğinize emin misiniz?')\">Sil</a></p>";
    } else {
        echo "<p>- $file (zaten yok)</p>";
    }
}

if (isset($_GET['delete'])) {
    $file = $_GET['delete'];
    if (in_array($file, $files_to_delete) && file_exists($file)) {
        if (unlink($file)) {
            echo "<div style='color: green; padding: 10px; background: #f0fff0; border: 1px solid green;'>✓ $file başarıyla silindi!</div>";
        } else {
            echo "<div style='color: red; padding: 10px; background: #fff0f0; border: 1px solid red;'>✗ $file silinirken hata oluştu!</div>";
        }
        echo "<meta http-equiv='refresh' content='2'>";
    }
}

echo "<hr>";
echo "<h4>Migration Durumu:</h4>";
echo "<p>✅ Form şablonu güncellendi (room_count ve parking seçenekleri)</p>";
echo "<p>✅ Property-details.php güncellendin (tüm alanlar gösteriliyor)</p>";
echo "<p>✅ Debug bilgileri eklendi</p>";
echo "<p>⚠️ Migration script'leri çalıştırılmalı (eski verileri güncellemek için)</p>";
echo "<p>🔧 Migration tamamlandıktan sonra debug dosyaları silinmeli</p>";

echo "<br><br><a href='add-property.php'>← Add Property'ye Dön</a>";
?>
