<?php
// Basit Cloudflare test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🔧 Cloudflare Class Test</h2>";

try {
    require_once 'includes/cloudflare-images-config.php';
    echo "<p>✅ Config yüklendi</p>";
    
    require_once 'includes/cloudflare-images-multi-domain.php';
    echo "<p>✅ Cloudflare class yüklendi</p>";
    
    if (class_exists('MultiDomainCloudflareImages')) {
        echo "<p>✅ Class mevcut</p>";
        
        $cloudflare = new MultiDomainCloudflareImages(CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_API_TOKEN);
        echo "<p>✅ Instance oluşturuldu</p>";
        
    } else {
        echo "<p>❌ Class bulunamadı</p>";
    }
    
} catch (Exception $e) {
    echo "<p style='color: red;'>❌ Hata: " . $e->getMessage() . "</p>";
    echo "<p><strong>File:</strong> " . $e->getFile() . "</p>";
    echo "<p><strong>Line:</strong> " . $e->getLine() . "</p>";
}

echo "<p><a href='dashboard/add-property.php'>Dashboard'a dön</a></p>";
?>
