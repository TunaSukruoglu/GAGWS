<?php
// Simple PHP Error Display
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>PHP Info ve Hata Kontrolü</h2>";

// PHP version
echo "<p><strong>PHP Version:</strong> " . phpversion() . "</p>";

// Error log location
echo "<p><strong>Error Log:</strong> " . ini_get('error_log') . "</p>";

// Check if we can include db.php
echo "<h3>Database Connection Test</h3>";
try {
    include 'db.php';
    echo "<p style='color: green;'>✅ db.php yüklendi</p>";
    
    if (isset($conn)) {
        echo "<p style='color: green;'>✅ \$conn değişkeni mevcut</p>";
        
        // Test connection
        if ($conn->ping()) {
            echo "<p style='color: green;'>✅ Database bağlantısı aktif</p>";
        } else {
            echo "<p style='color: red;'>❌ Database bağlantısı kapalı</p>";
        }
    } else {
        echo "<p style='color: red;'>❌ \$conn değişkeni bulunamadı</p>";
    }
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Database error: " . $e->getMessage() . "</p>";
}

// Check file permissions
echo "<h3>File Permission Check</h3>";
$file = 'dashboard/add-property.php';
if (file_exists($file)) {
    echo "<p style='color: green;'>✅ File exists: $file</p>";
    echo "<p>File permissions: " . substr(sprintf('%o', fileperms($file)), -4) . "</p>";
    echo "<p>File size: " . filesize($file) . " bytes</p>";
    echo "<p>Last modified: " . date('Y-m-d H:i:s', filemtime($file)) . "</p>";
} else {
    echo "<p style='color: red;'>❌ File not found: $file</p>";
}

// Show last few lines of add-property.php to check for syntax issues
echo "<h3>Son 10 satır kontrolü</h3>";
if (file_exists($file)) {
    $lines = file($file);
    $total_lines = count($lines);
    echo "<p>Toplam satır sayısı: $total_lines</p>";
    
    echo "<pre style='background: #f5f5f5; padding: 10px; font-size: 12px;'>";
    for ($i = max(0, $total_lines - 10); $i < $total_lines; $i++) {
        echo sprintf("%4d: %s", $i + 1, htmlspecialchars($lines[$i]));
    }
    echo "</pre>";
}

// Memory usage
echo "<h3>Memory Usage</h3>";
echo "<p>Memory limit: " . ini_get('memory_limit') . "</p>";
echo "<p>Current usage: " . memory_get_usage(true) / 1024 / 1024 . " MB</p>";
echo "<p>Peak usage: " . memory_get_peak_usage(true) / 1024 / 1024 . " MB</p>";
?>
