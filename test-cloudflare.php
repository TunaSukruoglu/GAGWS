<?php
/**
 * Cloudflare Images API Test Script
 * Bu script ile token ve account ID'nin doğru olup olmadığını test edebilirsiniz
 */

// Test edilecek değerler
$test_token = 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv';
$test_account_id = 'prdw3ANMyocSBJD-Do1EeQ';

echo "<h2>🔧 Cloudflare Images API Test</h2>";

// 1. Token doğrulama
echo "<h3>1. Token Doğrulama</h3>";
$token_verify_url = "https://api.cloudflare.com/client/v4/user/tokens/verify";
$token_result = make_api_request($token_verify_url, $test_token);
echo "<pre>" . json_encode($token_result, JSON_PRETTY_PRINT) . "</pre>";

// 2. Hesapları listeleme
echo "<h3>2. Hesap Listesi</h3>";
$accounts_url = "https://api.cloudflare.com/client/v4/accounts";
$accounts_result = make_api_request($accounts_url, $test_token);
echo "<pre>" . json_encode($accounts_result, JSON_PRETTY_PRINT) . "</pre>";

if (!empty($accounts_result['result'])) {
    echo "<p><strong>✅ Erişilebilir hesaplar bulundu!</strong></p>";
    foreach ($accounts_result['result'] as $account) {
        echo "<p>📋 Hesap: <code>{$account['name']}</code> - ID: <code>{$account['id']}</code></p>";
        
        // Bu hesap için Images API test et
        echo "<h4>Hesap {$account['name']} için Images API Testi:</h4>";
        $images_url = "https://api.cloudflare.com/client/v4/accounts/{$account['id']}/images/v1";
        $images_result = make_api_request($images_url, $test_token);
        echo "<pre>" . json_encode($images_result, JSON_PRETTY_PRINT) . "</pre>";
        
        if ($images_result['success']) {
            echo "<p><strong>✅ Bu hesap için Cloudflare Images kullanılabilir!</strong></p>";
            echo "<p>🔧 <strong>Doğru Account ID:</strong> <code>{$account['id']}</code></p>";
        } else {
            echo "<p><strong>❌ Bu hesap için Cloudflare Images erişimi yok.</strong></p>";
        }
    }
} else {
    echo "<p><strong>❌ Hiçbir hesaba erişim yok! Token permissions'ları yetersiz.</strong></p>";
    echo "<p>🔧 <strong>Çözüm:</strong> Cloudflare Dashboard'da token'ın permissions'larını düzenleyin:</p>";
    echo "<ul>";
    echo "<li><code>Account - Cloudflare Images:Edit</code></li>";
    echo "<li><code>Account Resources: Include All accounts</code></li>";
    echo "</ul>";
}

// 3. Mevcut Account ID test
echo "<h3>3. Mevcut Account ID Testi</h3>";
echo "<p>Test edilen Account ID: <code>$test_account_id</code></p>";
$current_images_url = "https://api.cloudflare.com/client/v4/accounts/$test_account_id/images/v1";
$current_result = make_api_request($current_images_url, $test_token);
echo "<pre>" . json_encode($current_result, JSON_PRETTY_PRINT) . "</pre>";

if ($current_result['success']) {
    echo "<p><strong>✅ Mevcut Account ID doğru!</strong></p>";
} else {
    echo "<p><strong>❌ Mevcut Account ID yanlış veya erişim yok!</strong></p>";
}

function make_api_request($url, $token) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_HTTPHEADER, [
        'Authorization: Bearer ' . $token,
        'Content-Type: application/json'
    ]);
    curl_setopt($ch, CURLOPT_TIMEOUT, 30);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    $result = json_decode($response, true);
    if (!$result) {
        $result = ['error' => 'Invalid JSON response', 'http_code' => $http_code, 'raw_response' => $response];
    } else {
        $result['http_code'] = $http_code;
    }
    
    return $result;
}
?>
<style>
body { font-family: Arial, sans-serif; max-width: 1200px; margin: 20px auto; padding: 20px; }
pre { background: #f5f5f5; padding: 15px; border-radius: 5px; overflow-x: auto; }
code { background: #e9ecef; padding: 2px 4px; border-radius: 3px; }
h2 { color: #0066cc; }
h3 { color: #004499; margin-top: 30px; }
h4 { color: #006633; }
</style>
