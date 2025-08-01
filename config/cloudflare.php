<?php
// Database bağlantısı
$servername = "localhost";
$username = "gokhanay_user"; 
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";

// Cloudflare Images Configuration
if (!defined('CLOUDFLARE_ACCOUNT_ID')) {
    define('CLOUDFLARE_ACCOUNT_ID', '763e070b3a98cd52926c5ab1b9a62d88'); // Güncel Account ID
}
if (!defined('CLOUDFLARE_API_TOKEN')) {
    define('CLOUDFLARE_API_TOKEN', 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv');   // API Token'ınızı buraya yazın
}
if (!defined('CLOUDFLARE_BASE_URL')) {
    define('CLOUDFLARE_BASE_URL', 'https://api.cloudflare.com/client/v4');
}

// Resim variant'ları (otomatik boyutlandırma)
define('CLOUDFLARE_VARIANTS', [
    'thumbnail' => 'width=150,height=150,fit=cover',
    'small' => 'width=300,height=200,fit=cover',
    'medium' => 'width=600,height=400,fit=cover', 
    'large' => 'width=1200,height=800,fit=cover',
    'watermarked' => 'width=800,height=600,fit=cover,watermark=true'
]);

// Default watermark ayarları
define('DEFAULT_WATERMARK_CONFIG', [
    'position' => 'bottom-right',
    'opacity' => 0.8,
    'scale' => 0.15,
    'margin' => 20,
    'text_color' => '#FFFFFF',
    'background_color' => 'rgba(0,0,0,0.7)',
    'font_size' => 14
]);

// Allowed image formats
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/webp']);
}

// Max file size (5MB)
define('MAX_FILE_SIZE', 5 * 1024 * 1024);

// Upload settings
define('CLOUDFLARE_ENABLED', true); // false yaparsanız normal upload kullanır
define('REQUIRE_WATERMARK', true);  // Zorunlu watermark

// Debug mode
define('CLOUDFLARE_DEBUG', true); // API loglarını kaydeder

// Error messages
define('ERROR_MESSAGES', [
    'file_too_large' => 'Dosya boyutu çok büyük (Max: 5MB)',
    'invalid_format' => 'Geçersiz dosya formatı (JPG, PNG, WebP desteklenir)',
    'upload_failed' => 'Cloudflare upload başarısız',
    'api_error' => 'Cloudflare API hatası',
    'no_file' => 'Dosya seçilmedi',
    'database_error' => 'Veritabanı hatası'
]);

// Success messages
define('SUCCESS_MESSAGES', [
    'upload_success' => 'Resimler başarıyla yüklendi',
    'watermark_applied' => 'Watermark başarıyla eklendi',
    'variants_created' => 'Farklı boyutlar oluşturuldu'
]);

// Helper function - Current domain detection - SADECE BİR KEZ TANIMLA
if (!function_exists('getCurrentDomain')) {
    function getCurrentDomain() {
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        // www. prefix'ini kaldır
        if (strpos($domain, 'www.') === 0) {
            $domain = substr($domain, 4);
        }
        return $domain;
    }
}

// Helper function - Database connection
if (!function_exists('getCloudflareDB')) {
    function getCloudflareDB() {
        global $servername, $username, $password, $dbname;
        try {
            $pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8mb4", 
                           $username, $password, [
                PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
            ]);
            return $pdo;
        } catch(PDOException $e) {
            error_log("Cloudflare DB Connection Error: " . $e->getMessage());
            return false;
        }
    }
}

// Helper function - Get domain watermark config
if (!function_exists('getDomainWatermarkConfig')) {
    function getDomainWatermarkConfig($domain = null) {
        if (!$domain) {
            $domain = getCurrentDomain();
        }
        
        $pdo = getCloudflareDB();
        if (!$pdo) return DEFAULT_WATERMARK_CONFIG;
        
        try {
            $stmt = $pdo->prepare("SELECT * FROM domain_watermark_configs WHERE domain = ? AND is_active = 1");
            $stmt->execute([$domain]);
            $config = $stmt->fetch();
            
            if ($config) {
                $watermark_config = json_decode($config['watermark_config'], true);
                $watermark_config['company_name'] = $config['company_name'];
                $watermark_config['logo_image_id'] = $config['logo_image_id'];
                return $watermark_config;
            }
            
            // Fallback to localhost config
            $stmt = $pdo->prepare("SELECT * FROM domain_watermark_configs WHERE domain = 'localhost' AND is_active = 1");
            $stmt->execute();
            $config = $stmt->fetch();
            
            if ($config) {
                $watermark_config = json_decode($config['watermark_config'], true);
                $watermark_config['company_name'] = $config['company_name'];
                return $watermark_config;
            }
            
        } catch(PDOException $e) {
            error_log("Get Domain Config Error: " . $e->getMessage());
        }
        
        return DEFAULT_WATERMARK_CONFIG;
    }
}

// Cloudflare API test function
if (!function_exists('testCloudflareConnection')) {
    function testCloudflareConnection() {
        if (!defined('CLOUDFLARE_ACCOUNT_ID') || !defined('CLOUDFLARE_API_TOKEN')) {
            return ['success' => false, 'message' => 'API credentials not configured'];
        }
        
        if (CLOUDFLARE_ACCOUNT_ID === 'YOUR_ACCOUNT_ID' || CLOUDFLARE_API_TOKEN === 'YOUR_API_TOKEN') {
            return ['success' => false, 'message' => 'Please update API credentials in config'];
        }
        
        $url = CLOUDFLARE_BASE_URL . '/accounts/' . CLOUDFLARE_ACCOUNT_ID . '/images/v1';
        
        $headers = [
            'Authorization: Bearer ' . CLOUDFLARE_API_TOKEN,
            'Content-Type: application/json'
        ];
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($httpCode === 200) {
            return ['success' => true, 'message' => 'Cloudflare connection successful'];
        } else {
            return ['success' => false, 'message' => 'Cloudflare API error: ' . $httpCode];
        }
    }
}
?>