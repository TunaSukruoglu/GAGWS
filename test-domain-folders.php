<?php
/**
 * Domain Bazlı Klasörleme Test Dosyası
 * Cloudflare Images'da domain bazlı organizasyon testi
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'includes/cloudflare-images-config.php';
require_once 'includes/cloudflare-images-multi-domain.php';

echo "<h1>🌐 Domain Bazlı Cloudflare Images Klasörleme Testi</h1>";

try {
    $cloudflare = new MultiDomainCloudflareImages();
    
    // Mevcut domain bilgisini al
    $currentDomain = $_SERVER['HTTP_HOST'] ?? 'gokhanaydinli.com';
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📋 Domain Bilgileri</h3>";
    echo "<p><strong>Mevcut Domain:</strong> {$currentDomain}</p>";
    
    // Domain helper metodlarını test et
    $reflection = new ReflectionClass($cloudflare);
    
    // getDomainFolder metodunu test et
    $getDomainFolderMethod = $reflection->getMethod('getDomainFolder');
    $getDomainFolderMethod->setAccessible(true);
    $domainFolder = $getDomainFolderMethod->invoke($cloudflare, $currentDomain);
    
    // sanitizeDomainName metodunu test et
    $sanitizeDomainNameMethod = $reflection->getMethod('sanitizeDomainName');
    $sanitizeDomainNameMethod->setAccessible(true);
    $safeDomainName = $sanitizeDomainNameMethod->invoke($cloudflare, $currentDomain);
    
    echo "<p><strong>Güvenli Domain Adı:</strong> {$safeDomainName}</p>";
    echo "<p><strong>Klasör Adı:</strong> {$domainFolder}</p>";
    echo "</div>";
    
    // Farklı domainler için test
    $testDomains = [
        'gokhanaydinli.com',
        'www.gokhanaydinli.com',
        'ankaraemlak.com',
        'istanbul-emlak.com',
        'test.domain-with-dash.org'
    ];
    
    echo "<div style='background: #f3e5f5; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🧪 Test Domainleri için Klasör Adları</h3>";
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #9c27b0; color: white;'>";
    echo "<th style='padding: 10px;'>Domain</th>";
    echo "<th style='padding: 10px;'>Güvenli Ad</th>";
    echo "<th style='padding: 10px;'>Klasör Adı</th>";
    echo "<th style='padding: 10px;'>Upload ID Prefixi</th>";
    echo "</tr>";
    
    foreach ($testDomains as $domain) {
        $safeName = $sanitizeDomainNameMethod->invoke($cloudflare, $domain);
        $folderName = $getDomainFolderMethod->invoke($cloudflare, $domain);
        $uploadPrefix = $safeName . '-' . uniqid() . '-' . time();
        
        echo "<tr>";
        echo "<td style='padding: 10px;'>{$domain}</td>";
        echo "<td style='padding: 10px;'><code>{$safeName}</code></td>";
        echo "<td style='padding: 10px;'><code>{$folderName}</code></td>";
        echo "<td style='padding: 10px;'><code>{$uploadPrefix}</code></td>";
        echo "</tr>";
    }
    echo "</table>";
    echo "</div>";
    
    // Domain images test
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📸 Domain Resimleri Test</h3>";
    
    $domainImagesResult = $cloudflare->getDomainImages($currentDomain);
    
    if ($domainImagesResult['success']) {
        echo "<p><strong>✅ Domain images başarıyla alındı!</strong></p>";
        echo "<p><strong>Domain:</strong> {$domainImagesResult['domain']}</p>";
        echo "<p><strong>Klasör:</strong> {$domainImagesResult['folder']}</p>";
        echo "<p><strong>Resim Sayısı:</strong> {$domainImagesResult['count']}</p>";
        
        if (!empty($domainImagesResult['images'])) {
            echo "<h4>📋 Mevcut Resimler:</h4>";
            echo "<ul>";
            foreach ($domainImagesResult['images'] as $image) {
                $metadata = isset($image['metadata']) ? $image['metadata'] : [];
                echo "<li>";
                echo "<strong>ID:</strong> {$image['id']}<br>";
                echo "<strong>Domain:</strong> " . ($metadata['domain'] ?? 'N/A') . "<br>";
                echo "<strong>Klasör:</strong> " . ($metadata['folder'] ?? 'N/A') . "<br>";
                echo "<strong>Upload Time:</strong> " . ($metadata['upload_time'] ?? 'N/A');
                echo "</li><br>";
            }
            echo "</ul>";
        } else {
            echo "<p><em>Bu domain için henüz resim yok.</em></p>";
        }
    } else {
        echo "<p><strong>⚠️ Domain images alınamadı:</strong> {$domainImagesResult['error']}</p>";
    }
    echo "</div>";
    
    // Metadata örneği
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📝 Upload Metadata Örneği</h3>";
    
    $sampleMetadata = [
        'domain' => $currentDomain,
        'folder' => $domainFolder,
        'propertyId' => 'test_123',
        'originalName' => 'test-image.jpg',
        'uploadTime' => date('Y-m-d H:i:s'),
        'company' => 'Gökhan Aydınlı Emlak'
    ];
    
    echo "<pre style='background: #f5f5f5; padding: 15px; border-radius: 5px;'>";
    echo json_encode($sampleMetadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
    echo "</pre>";
    echo "</div>";
    
    // Unique ID örnekleri
    echo "<div style='background: #fce4ec; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🆔 Unique ID Örnekleri (Cloudflare Uyumlu)</h3>";
    echo "<p>Normal upload için:</p>";
    echo "<code>{$safeDomainName}-" . uniqid() . "-" . time() . "</code><br><br>";
    echo "<p>Watermark'lı upload için:</p>";
    echo "<code>{$safeDomainName}-wm-" . uniqid() . "-" . time() . "</code>";
    echo "</div>";
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Hata</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}

echo "<div style='background: #f1f8e9; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>✅ Sistem Özeti</h3>";
echo "<ul>";
echo "<li><strong>Domain Bazlı Klasörleme:</strong> Aktif</li>";
echo "<li><strong>Cloudflare Images:</strong> Entegre</li>";
echo "<li><strong>Unique ID System:</strong> Domain prefixi ile</li>";
echo "<li><strong>Metadata Tracking:</strong> Domain, klasör, zaman damgası</li>";
echo "<li><strong>Watermark Support:</strong> Domain bazlı konfigürasyon</li>";
echo "</ul>";
echo "</div>";

echo "<p><a href='dashboard/add-property.php' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 İlan Ekleme Sayfasına Git</a></p>";
?>
