<?php
/**
 * Cloudflare Images API Test ve Konfigürasyon Yardımcısı
 * Bu dosyayı çalıştırarak API bilgilerinizi test edebilirsiniz
 */
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🔧 Cloudflare Images API Kurulumu</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .step { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #007cba; }
        .success { background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; border-left: 5px solid #28a745; }
        .error { background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; border-left: 5px solid #dc3545; }
        .warning { background: #fff3cd; color: #856404; padding: 15px; border-radius: 5px; border-left: 5px solid #ffc107; }
        code { background: #f8f9fa; padding: 2px 5px; border-radius: 3px; font-family: monospace; }
        .form-group { margin: 15px 0; }
        .form-group label { display: block; font-weight: bold; margin-bottom: 5px; }
        .form-group input { width: 100%; padding: 10px; border: 1px solid #ddd; border-radius: 5px; font-family: monospace; }
        .btn { background: #007cba; color: white; padding: 12px 20px; border: none; border-radius: 5px; cursor: pointer; font-size: 16px; }
        .btn:hover { background: #005a87; }
        .config-code { background: #2d3748; color: #e2e8f0; padding: 20px; border-radius: 5px; font-family: monospace; white-space: pre-wrap; overflow-x: auto; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🔧 Cloudflare Images API Kurulumu</h1>
        
        <div class="step">
            <h3>📋 Gerekli Bilgiler</h3>
            <p>Cloudflare Images'ı kullanabilmek için şu bilgilere ihtiyacımız var:</p>
            <ul>
                <li><strong>Account ID:</strong> Cloudflare hesap kimliğiniz</li>
                <li><strong>API Token:</strong> Images servisi için özel token</li>
            </ul>
        </div>

        <div class="step">
            <h3>🚀 Adım 1: Cloudflare Dashboard</h3>
            <p><a href="https://dash.cloudflare.com/" target="_blank" style="color: #007cba;">Cloudflare Dashboard'a gidin</a> ve giriş yapın.</p>
        </div>

        <div class="step">
            <h3>🔑 Adım 2: API Token Oluşturun</h3>
            <ol>
                <li><strong>My Profile</strong> → <strong>API Tokens</strong> bölümüne gidin</li>
                <li><strong>"Create Token"</strong> butonuna tıklayın</li>
                <li><strong>"Custom token"</strong> seçin</li>
                <li>Şu ayarları yapın:
                    <ul>
                        <li><strong>Token name:</strong> <code>Gokhan Aydinli Images API</code></li>
                        <li><strong>Permissions:</strong> <code>Cloudflare Images:Edit</code></li>
                        <li><strong>Account resources:</strong> <code>Include - Your account</code></li>
                        <li><strong>Zone resources:</strong> <code>All zones</code> (opsiyonel)</li>
                    </ul>
                </li>
                <li><strong>"Continue to summary"</strong> → <strong>"Create Token"</strong></li>
                <li>Oluşan token'ı kopyalayın (sadece bir kez gösterilir!)</li>
            </ol>
        </div>

        <div class="step">
            <h3>🆔 Adım 3: Account ID'yi Alın</h3>
            <p>Cloudflare Dashboard'ın sağ kenarında <strong>"Account ID"</strong> görünecek. Bu ID'yi kopyalayın.</p>
        </div>

        <div class="step">
            <h3>📸 Adım 4: Images Servisini Aktif Edin</h3>
            <ol>
                <li>Dashboard'da <strong>"Images"</strong> sekmesine gidin</li>
                <li><strong>"Enable Cloudflare Images"</strong> butonuna tıklayın</li>
                <li>Ödeme planını seçin (genelde $5-10/ay)</li>
            </ol>
        </div>

        <h2>⚙️ API Bilgilerini Test Et</h2>
        
        <form method="post">
            <div class="form-group">
                <label for="account_id">🆔 Account ID:</label>
                <input type="text" id="account_id" name="account_id" placeholder="Account ID'nizi buraya yapıştırın" value="<?php echo $_POST['account_id'] ?? ''; ?>" required>
            </div>
            
            <div class="form-group">
                <label for="api_token">🔑 API Token:</label>
                <input type="password" id="api_token" name="api_token" placeholder="API Token'ınızı buraya yapıştırın" value="<?php echo $_POST['api_token'] ?? ''; ?>" required>
            </div>
            
            <button type="submit" name="test_api" class="btn">🧪 API'yi Test Et</button>
        </form>

        <?php
        if (isset($_POST['test_api'])) {
            $account_id = trim($_POST['account_id']);
            $api_token = trim($_POST['api_token']);
            
            echo "<h2>🧪 Test Sonuçları</h2>";
            
            // Basit API test
            $test_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/images/v1/stats";
            
            $curl = curl_init();
            curl_setopt_array($curl, [
                CURLOPT_URL => $test_url,
                CURLOPT_RETURNTRANSFER => true,
                CURLOPT_HTTPHEADER => [
                    'Authorization: Bearer ' . $api_token,
                    'Content-Type: application/json'
                ],
                CURLOPT_TIMEOUT => 10
            ]);
            
            $response = curl_exec($curl);
            $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
            $error = curl_error($curl);
            curl_close($curl);
            
            if ($http_code === 200) {
                $data = json_decode($response, true);
                
                echo "<div class='success'>";
                echo "<h4>✅ API Test Başarılı!</h4>";
                echo "<p>Cloudflare Images API'niz çalışıyor!</p>";
                if (isset($data['result']['count'])) {
                    echo "<p><strong>Toplam resim sayısı:</strong> " . $data['result']['count']['current'] . "</p>";
                }
                echo "</div>";
                
                // Konfigürasyon kodunu göster
                echo "<h3>📝 Konfigürasyon Kodu</h3>";
                echo "<p>Aşağıdaki kodu <code>includes/cloudflare-images-config.php</code> dosyasına kopyalayın:</p>";
                
                echo "<div class='config-code'>";
                echo htmlspecialchars("<?php
// Cloudflare Images ayarları - API Test Başarılı ✅
define('CLOUDFLARE_ACCOUNT_ID', '{$account_id}');
define('CLOUDFLARE_API_TOKEN', '{$api_token}');

// Cloudflare Images kullanılsın mı? (true/false)
define('USE_CLOUDFLARE_IMAGES', true); // ✅ Artık true yapabilirsiniz!

// Cloudflare Images alternatifi olarak local upload
define('USE_LOCAL_UPLOAD', false); // Cloudflare aktif olduğunda false yapın

// Upload ayarları
define('MAX_UPLOAD_SIZE', 5 * 1024 * 1024); // 5MB
define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
?>");
                echo "</div>";
                
                echo "<div class='warning'>";
                echo "<h4>⚠️ Güvenlik Uyarısı</h4>";
                echo "<p>API Token'ınızı güvenli tutun ve asla public bir yerde paylaşmayın!</p>";
                echo "</div>";
                
            } elseif ($http_code === 401) {
                echo "<div class='error'>";
                echo "<h4>❌ Yetkilendirme Hatası</h4>";
                echo "<p>API Token geçersiz veya yetkileri yeterli değil.</p>";
                echo "<p><strong>Kontrol edin:</strong></p>";
                echo "<ul>";
                echo "<li>API Token doğru kopyalandı mı?</li>";
                echo "<li>Token'da <code>Cloudflare Images:Edit</code> yetkisi var mı?</li>";
                echo "<li>Token aktif mi? (süresi dolmamış mı?)</li>";
                echo "</ul>";
                echo "</div>";
                
            } elseif ($http_code === 403) {
                echo "<div class='error'>";
                echo "<h4>❌ Erişim Hatası</h4>";
                echo "<p>Account ID yanlış veya Images servisi aktif değil.</p>";
                echo "<p><strong>Kontrol edin:</strong></p>";
                echo "<ul>";
                echo "<li>Account ID doğru mu?</li>";
                echo "<li>Cloudflare Images servisi aktif edildi mi?</li>";
                echo "<li>Ödeme bilgileri güncel mi?</li>";
                echo "</ul>";
                echo "</div>";
                
            } else {
                echo "<div class='error'>";
                echo "<h4>❌ Bağlantı Hatası</h4>";
                echo "<p><strong>HTTP Code:</strong> $http_code</p>";
                if ($error) {
                    echo "<p><strong>Curl Hatası:</strong> $error</p>";
                }
                if ($response) {
                    echo "<p><strong>Response:</strong> " . htmlspecialchars($response) . "</p>";
                }
                echo "</div>";
            }
        }
        ?>

        <div class="step">
            <h3>🎯 Başarılı Test Sonrası</h3>
            <p>API test başarılı olduktan sonra:</p>
            <ol>
                <li>Yukarıdaki konfigürasyon kodunu <code>includes/cloudflare-images-config.php</code> dosyasına kopyalayın</li>
                <li><a href="test-cloudflare-images.php" target="_blank">Test sayfasını</a> yenileyin</li>
                <li>İlan ekleme sayfasında resim upload test edin</li>
                <li>Cloudflare Dashboard'da Images bölümünden yüklenen resimleri kontrol edin</li>
            </ol>
        </div>

        <div class="step">
            <h3>💰 Maliyet Bilgisi</h3>
            <p><strong>Cloudflare Images Fiyatları (2025):</strong></p>
            <ul>
                <li><strong>Storage:</strong> $5/ay (100,000 resime kadar)</li>
                <li><strong>Delivery:</strong> $1 / 100,000 request</li>
                <li><strong>Transformations:</strong> $1 / 1,000 resim</li>
            </ul>
            <p>Ortalama bir emlak sitesi için ayda <strong>$5-15</strong> arası maliyet beklenir.</p>
        </div>

        <div class="step">
            <h3>✨ Cloudflare Images Avantajları</h3>
            <ul>
                <li>🚀 <strong>Global CDN:</strong> Dünya çapında hızlı resim servisi</li>
                <li>📱 <strong>Otomatik Optimizasyon:</strong> WebP, AVIF format desteği</li>
                <li>🖼️ <strong>Dinamik Boyutlandırma:</strong> İstediğiniz boyutta resim üretimi</li>
                <li>🏷️ <strong>Watermark:</strong> Otomatik logo ekleme</li>
                <li>🔒 <strong>Güvenlik:</strong> Hotlink koruması ve DDoS koruma</li>
                <li>📊 <strong>Analytics:</strong> Detaylı resim istatistikleri</li>
                <li>💾 <strong>Backup:</strong> Resimleriniz Cloudflare'de güvende</li>
            </ul>
        </div>

        <p style="text-align: center; margin-top: 30px; color: #666;">
            📧 Yardım için: <a href="mailto:info@gokhanaydinli.com">info@gokhanaydinli.com</a>
        </p>
    </div>
</body>
</html>
