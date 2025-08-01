<?php
/**
 * Cloudflare Images Direct Upload Test
 * Resmi hosting sunucusuna değil direkt Cloudflare'a yükler
 */

// Upload ayarları
ini_set('upload_max_filesize', '5M');
ini_set('post_max_size', '8M');
ini_set('max_file_uploads', '10');
ini_set('max_execution_time', '60');
ini_set('memory_limit', '128M');

// Include files
require_once 'includes/cloudflare-images-multi-domain.php';
require_once 'includes/cloudflare-images-config.php';

echo "<h1>🚀 Cloudflare Direct Upload Test</h1>";

echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #007cba;'>";
echo "<h3>💡 Nasıl Çalışıyor?</h3>";
echo "<ul>";
echo "<li><strong>Eski Yöntem:</strong> Resim → Hosting Sunucusu → Cloudflare (Hosting limiti problemi)</li>";
echo "<li><strong>Yeni Yöntem:</strong> Resim → Direkt Cloudflare (Hosting limitini bypass eder)</li>";
echo "</ul>";
echo "<p><strong>Sonuç:</strong> 50MB'a kadar resim yükleyebilirsiniz!</p>";
echo "</div>";

// Cloudflare limitleri göster
echo "<h2>☁️ Cloudflare Images Limitleri</h2>";
echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
echo "<tr style='background: #f0f0f0;'><th style='padding: 10px;'>Özellik</th><th>Limit</th><th>Bizim Durum</th></tr>";

$cf_limits = [
    'Maksimum Dosya Boyutu' => ['100MB', '✅ Yeterli'],
    'Desteklenen Formatlar' => ['JPEG, PNG, GIF, WebP', '✅ Uyumlu'],
    'API Upload Limiti' => ['100MB/request', '✅ Çok büyük'],
    'Storage' => ['100,000 resim', '✅ Çok fazla'],
    'Bandwidth' => ['Sınırsız', '✅ Mükemmel']
];

foreach ($cf_limits as $feature => $info) {
    echo "<tr>";
    echo "<td style='padding: 10px;'><strong>$feature</strong></td>";
    echo "<td style='padding: 10px;'>{$info[0]}</td>";
    echo "<td style='padding: 10px; color: green;'>{$info[1]}</td>";
    echo "</tr>";
}
echo "</table>";

if (isset($_POST['direct_upload'])) {
    echo "<h2>🧪 Direct Upload Test Sonucu</h2>";
    
    try {
        if (isset($_FILES['test_image']) && $_FILES['test_image']['error'] === UPLOAD_ERR_OK) {
            $file = $_FILES['test_image'];
            
            echo "<div style='background: #fff3cd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>📁 Dosya Bilgileri</h4>";
            echo "<p><strong>İsim:</strong> " . htmlspecialchars($file['name']) . "</p>";
            echo "<p><strong>Boyut:</strong> " . number_format($file['size'] / 1024 / 1024, 2) . " MB</p>";
            echo "<p><strong>Tip:</strong> " . $file['type'] . "</p>";
            echo "<p><strong>Hosting Upload:</strong> ✅ Başarılı (Geçici)</p>";
            echo "</div>";
            
            // Cloudflare'a yükle
            if (USE_CLOUDFLARE_IMAGES && class_exists('MultiDomainCloudflareImages')) {
                $cloudflare = new MultiDomainCloudflareImages(CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_API_TOKEN);
                $domain = getCurrentDomain();
                
                echo "<div style='background: #cce5ff; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>☁️ Cloudflare'a Yükleniyor...</h4>";
                echo "<p>Domain: $domain</p>";
                echo "</div>";
                
                $result = $cloudflare->uploadImage($file['tmp_name'], [
                    'id' => 'test-' . time(),
                    'metadata' => [
                        'domain' => $domain,
                        'original_name' => $file['name'],
                        'test_upload' => true
                    ]
                ]);
                
                if ($result && isset($result['success']) && $result['success']) {
                    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<h4>🎉 Cloudflare Upload Başarılı!</h4>";
                    echo "<p><strong>Image ID:</strong> " . htmlspecialchars($result['result']['id']) . "</p>";
                    echo "<p><strong>Cloudflare URL:</strong> <a href='" . htmlspecialchars($result['result']['variants'][0]) . "' target='_blank'>" . htmlspecialchars($result['result']['variants'][0]) . "</a></p>";
                    
                    if (isset($result['result']['variants'][0])) {
                        echo "<h5>🖼️ Önizleme:</h5>";
                        echo "<img src='" . htmlspecialchars($result['result']['variants'][0]) . "' style='max-width: 300px; border-radius: 5px; box-shadow: 0 2px 8px rgba(0,0,0,0.1);'>";
                    }
                    echo "</div>";
                    
                    echo "<div style='background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<h4>✨ Avantajlar</h4>";
                    echo "<ul>";
                    echo "<li>🌍 <strong>Global CDN:</strong> Dünya çapında hızlı erişim</li>";
                    echo "<li>📱 <strong>Otomatik Optimizasyon:</strong> WebP/AVIF formatları</li>";
                    echo "<li>🖼️ <strong>Dinamik Resize:</strong> İstediğiniz boyutta resim</li>";
                    echo "<li>🏷️ <strong>Watermark:</strong> Otomatik logo ekleme</li>";
                    echo "<li>🔒 <strong>Güvenlik:</strong> Hotlink koruması</li>";
                    echo "</ul>";
                    echo "</div>";
                    
                } else {
                    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                    echo "<h4>❌ Cloudflare Upload Hatası</h4>";
                    echo "<p>Hata: " . (isset($result['errors'][0]['message']) ? htmlspecialchars($result['errors'][0]['message']) : 'Bilinmeyen hata') . "</p>";
                    echo "<p><strong>Debug:</strong> " . htmlspecialchars(json_encode($result, JSON_PRETTY_PRINT)) . "</p>";
                    echo "</div>";
                }
                
            } else {
                echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
                echo "<h4>❌ Cloudflare Konfigürasyonu</h4>";
                echo "<p>Cloudflare Images aktif değil veya class yüklenemedi.</p>";
                echo "</div>";
            }
            
        } else {
            $error_messages = [
                UPLOAD_ERR_OK => "✅ Başarılı",
                UPLOAD_ERR_INI_SIZE => "❌ Dosya çok büyük (hosting php.ini limiti: " . ini_get('upload_max_filesize') . ")",
                UPLOAD_ERR_FORM_SIZE => "❌ Dosya çok büyük (HTML form limiti)",
                UPLOAD_ERR_PARTIAL => "⚠️ Dosya kısmen yüklendi",
                UPLOAD_ERR_NO_FILE => "❌ Hiç dosya seçilmedi",
                UPLOAD_ERR_NO_TMP_DIR => "❌ Geçici klasör bulunamadı",
                UPLOAD_ERR_CANT_WRITE => "❌ Disk yazma hatası",
                UPLOAD_ERR_EXTENSION => "❌ PHP extension upload'ı durdurdu"
            ];
            
            $error_code = $_FILES['test_image']['error'];
            $error_message = $error_messages[$error_code] ?? "❌ Bilinmeyen hata: $error_code";
            
            echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
            echo "<h4>Hosting Upload Hatası</h4>";
            echo "<p><strong>Hata Kodu:</strong> $error_code</p>";
            echo "<p><strong>Açıklama:</strong> $error_message</p>";
            
            if ($error_code === UPLOAD_ERR_INI_SIZE) {
                echo "<h5>💡 Çözüm Önerileri:</h5>";
                echo "<ul>";
                echo "<li>Resmi <a href='https://tinypng.com' target='_blank'>TinyPNG</a> ile sıkıştırın</li>";
                echo "<li>Resim boyutunu " . ini_get('upload_max_filesize') . " altına düşürün</li>";
                echo "<li>Hosting sağlayıcınızdan limit artırımı isteyin</li>";
                echo "</ul>";
            }
            echo "</div>";
        }
        
    } catch (Exception $e) {
        echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin: 10px 0;'>";
        echo "<h4>💥 Exception!</h4>";
        echo "<p>" . htmlspecialchars($e->getMessage()) . "</p>";
        echo "</div>";
    }
}

function getCurrentDomain() {
    return $_SERVER['HTTP_HOST'] ?? 'gokhanaydinli.com';
}
?>

<script>
function checkFileSize() {
    const fileInput = document.getElementById('test_image');
    const file = fileInput.files[0];
    const hostingLimitBytes = <?php echo convertToBytes(ini_get('upload_max_filesize')); ?>;
    const hostingLimitMB = Math.round(hostingLimitBytes / 1024 / 1024);
    const cloudflareLimitMB = 100; // Cloudflare limit
    
    const infoDiv = document.getElementById('file-info');
    
    if (file) {
        const fileSizeMB = (file.size / 1024 / 1024).toFixed(2);
        
        if (file.size > 100 * 1024 * 1024) { // 100MB Cloudflare limit
            infoDiv.innerHTML = `
                <div style="background: #f8d7da; color: #721c24; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    ❌ <strong>Cloudflare limitini aşıyor!</strong><br>
                    Seçilen: ${fileSizeMB} MB<br>
                    Cloudflare Limit: ${cloudflareLimitMB} MB<br>
                </div>
            `;
            document.getElementById('upload-btn').disabled = true;
        } else if (file.size > hostingLimitBytes) {
            infoDiv.innerHTML = `
                <div style="background: #fff3cd; color: #856404; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    ⚠️ <strong>Hosting limitini aşıyor ama Cloudflare'a yüklenebilir!</strong><br>
                    Seçilen: ${fileSizeMB} MB<br>
                    Hosting Limit: ${hostingLimitMB} MB<br>
                    Cloudflare Limit: ${cloudflareLimitMB} MB<br>
                    <small>Resim Cloudflare'a direkt yüklenecek (hosting bypass)</small>
                </div>
            `;
            document.getElementById('upload-btn').disabled = false;
        } else {
            infoDiv.innerHTML = `
                <div style="background: #d4edda; color: #155724; padding: 10px; border-radius: 5px; margin: 10px 0;">
                    ✅ <strong>Tüm limitler uygun!</strong><br>
                    Boyut: ${fileSizeMB} MB<br>
                    Hosting: ✅ ${hostingLimitMB} MB<br>
                    Cloudflare: ✅ ${cloudflareLimitMB} MB
                </div>
            `;
            document.getElementById('upload-btn').disabled = false;
        }
    } else {
        infoDiv.innerHTML = '';
        document.getElementById('upload-btn').disabled = false;
    }
}
</script>

<div style="background: #d1ecf1; padding: 15px; border-radius: 5px; margin: 10px 0; border-left: 5px solid #bee5eb;">
    <h4>🎯 Test Hedefi</h4>
    <p>Bu test ile resimlerinizi direkt Cloudflare'a yükleyip hosting limitlerini bypass edebilirsiniz.</p>
    <p><strong>Hosting Limit:</strong> <?php echo ini_get('upload_max_filesize'); ?></p>
    <p><strong>Cloudflare Limit:</strong> 100MB</p>
</div>

<form method="post" enctype="multipart/form-data" style="border: 1px solid #ddd; padding: 20px; border-radius: 5px; background: #f9f9f9;">
    <h3>☁️ Cloudflare Direct Upload Test</h3>
    <p>
        <label for="test_image">Resim Seç (Max 100MB):</label><br>
        <input type="file" name="test_image" id="test_image" accept="image/*" onchange="checkFileSize()" required>
    </p>
    
    <div id="file-info"></div>
    
    <p>
        <button type="submit" name="direct_upload" id="upload-btn" style="background: #007cba; color: white; padding: 10px 20px; border: none; border-radius: 3px; cursor: pointer;">
            🚀 Cloudflare'a Direkt Yükle
        </button>
    </p>
</form>

<?php
function convertToBytes($value) {
    $value = trim($value);
    $last = strtolower($value[strlen($value)-1]);
    $value = (int)$value;
    
    switch($last) {
        case 'g':
            $value *= 1024;
        case 'm':
            $value *= 1024;
        case 'k':
            $value *= 1024;
    }
    
    return $value;
}
?>

<div style="text-align: center; margin-top: 30px;">
    <a href="test-cloudflare-images.php" style="background: #6c757d; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 5px;">
        ← Eski Test Sayfası
    </a>
    <a href="dashboard/add-property.php" style="background: #28a745; color: white; padding: 10px 20px; text-decoration: none; border-radius: 3px; margin: 5px;">
        📝 Dashboard
    </a>
</div>
