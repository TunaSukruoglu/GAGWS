<?php
// PHP Error Checker for add-property.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "PHP Syntax Check başlatılıyor...\n\n";

// Check PHP syntax
$file_path = 'dashboard/add-property.php';
$output = [];
$return_var = 0;

// Run syntax check
exec("php -l $file_path 2>&1", $output, $return_var);

echo "Syntax Check Sonucu:\n";
echo "Return Code: $return_var\n";
echo "Output:\n";
foreach ($output as $line) {
    echo $line . "\n";
}

if ($return_var === 0) {
    echo "\n✅ PHP Syntax OK\n";
} else {
    echo "\n❌ PHP Syntax HATA BULUNDU!\n";
}

echo "\n" . str_repeat("=", 50) . "\n";

// Check if file exists and is readable
if (file_exists($file_path)) {
    echo "✅ Dosya mevcut: $file_path\n";
    echo "Dosya boyutu: " . filesize($file_path) . " bytes\n";
    
    if (is_readable($file_path)) {
        echo "✅ Dosya okunabilir\n";
    } else {
        echo "❌ Dosya okunamıyor\n";
    }
} else {
    echo "❌ Dosya bulunamadı: $file_path\n";
}

// Check database connection
echo "\n" . str_repeat("=", 50) . "\n";
echo "Database bağlantısı test ediliyor...\n";

try {
    include 'db.php';
    echo "✅ Database bağlantısı başarılı\n";
    
    // Test a simple query
    $result = $conn->query("SELECT 1");
    if ($result) {
        echo "✅ Database sorgusu çalışıyor\n";
    } else {
        echo "❌ Database sorgusu başarısız: " . $conn->error . "\n";
    }
} catch (Exception $e) {
    echo "❌ Database hatası: " . $e->getMessage() . "\n";
}

echo "\n" . str_repeat("=", 50) . "\n";
echo "Debug tamamlandı.\n";
?>
