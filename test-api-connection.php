<?php
// Cloudflare API Bağlantı Testi
require_once 'includes/cloudflare-images-config.php';

echo "<h2>🧪 Cloudflare API Bağlantı Testi</h2>\n";
echo "<p><strong>Account ID:</strong> " . CLOUDFLARE_ACCOUNT_ID . "</p>\n";
echo "<p><strong>API Token:</strong> " . substr(CLOUDFLARE_API_TOKEN, 0, 10) . "..." . "</p>\n";

// API Stats endpoint test
$test_url = "https://api.cloudflare.com/client/v4/accounts/" . CLOUDFLARE_ACCOUNT_ID . "/images/v1/stats";

$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => $test_url,
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        'Authorization: Bearer ' . CLOUDFLARE_API_TOKEN,
        'Content-Type: application/json'
    ],
    CURLOPT_TIMEOUT => 10
]);

$response = curl_exec($curl);
$http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
$error = curl_error($curl);
curl_close($curl);

echo "<h3>📊 Test Sonucu:</h3>\n";
echo "<p><strong>HTTP Code:</strong> $http_code</p>\n";

if ($http_code === 200) {
    $data = json_decode($response, true);
    echo "<div style='background: #d4edda; color: #155724; padding: 15px; border-radius: 5px;'>\n";
    echo "<h4>✅ API Test Başarılı!</h4>\n";
    echo "<p>Cloudflare Images API'niz çalışıyor!</p>\n";
    
    if (isset($data['result']['count'])) {
        echo "<p><strong>Toplam resim sayısı:</strong> " . $data['result']['count']['current'] . "</p>\n";
        echo "<p><strong>Storage kullanımı:</strong> " . number_format($data['result']['count']['current']) . " resim</p>\n";
    }
    
    echo "<p><strong>✨ Artık USE_CLOUDFLARE_IMAGES = true yapabilirsiniz!</strong></p>\n";
    echo "</div>\n";
    
} elseif ($http_code === 401) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>\n";
    echo "<h4>❌ Yetkilendirme Hatası</h4>\n";
    echo "<p>API Token geçersiz veya yetkileri yeterli değil.</p>\n";
    echo "</div>\n";
    
} elseif ($http_code === 403) {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>\n";
    echo "<h4>❌ Erişim Hatası</h4>\n";
    echo "<p>Account ID yanlış veya Images servisi aktif değil.</p>\n";
    echo "</div>\n";
    
} else {
    echo "<div style='background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px;'>\n";
    echo "<h4>❌ Bağlantı Hatası</h4>\n";
    echo "<p>Response: " . htmlspecialchars($response) . "</p>\n";
    if ($error) {
        echo "<p>Curl Error: $error</p>\n";
    }
    echo "</div>\n";
}

echo "<hr>\n";
echo "<h3>🔧 Yapılacaklar:</h3>\n";
echo "<ol>\n";
echo "<li>✅ API bilgileri güncellendi</li>\n";
echo "<li>🧪 API bağlantısı test ediliyor...</li>\n";
echo "<li>⚙️ USE_CLOUDFLARE_IMAGES = true yap</li>\n";
echo "<li>📸 Dashboard'da resim upload test et</li>\n";
echo "</ol>\n";
?>
