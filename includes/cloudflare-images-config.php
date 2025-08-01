<?php
/**
 * Cloudflare Images Konfigürasyonu
 * Bu dosyada Cloudflare hesap bilgilerinizi ve ayarlarınızı yapılandırın
 */

// Cloudflare Images ayarları
if (!defined('CLOUDFLARE_ACCOUNT_ID')) {
    define('CLOUDFLARE_ACCOUNT_ID', '763e070b3a98cd52926c5ab1b9a62d88'); // DOĞRU Account ID
}
if (!defined('CLOUDFLARE_ACCOUNT_HASH')) {
    define('CLOUDFLARE_ACCOUNT_HASH', 'prdw3ANMyocSBJD-Do1EeQ'); // Account Hash for public URLs
}
if (!defined('CLOUDFLARE_API_TOKEN')) {
    define('CLOUDFLARE_API_TOKEN', 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv');   // Mevcut token
}

// Cloudflare Images kullanılsın mı? (true/false)
define('USE_CLOUDFLARE_IMAGES', true); // ✅ CLOUDFLARE AKTİF - Doğru Account ID ile çalışıyor

// Cloudflare Images alternatifi olarak local upload
define('USE_LOCAL_UPLOAD', false); // ❌ Local upload kapalı - Cloudflare aktif

// Upload ayarları
if (!defined('MAX_UPLOAD_SIZE')) {
    define('MAX_UPLOAD_SIZE', 10 * 1024 * 1024); // 10MB (Cloudflare Pro plan)
}
if (!defined('ALLOWED_IMAGE_TYPES')) {
    define('ALLOWED_IMAGE_TYPES', ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp']);
}
if (!defined('ALLOWED_EXTENSIONS')) {
    define('ALLOWED_EXTENSIONS', ['jpg', 'jpeg', 'png', 'gif', 'webp']);
}

// Domain ayarları
$CLOUDFLARE_DOMAINS = [
    'gokhanaydinli.com' => [
        'logo_url' => 'https://gokhanaydinli.com/images/logo.png',
        'company_name' => 'Gökhan Aydınlı Emlak',
        'website' => 'gokhanaydinli.com',
        'phone' => '+90 555 123 45 67',
        'position' => 'bottom-right',
        'opacity' => 80,
        'logo_size' => 'medium',
        'text_color' => '#FFFFFF',
        'background_color' => 'rgba(0,0,0,0.7)'
    ]
];

/**
 * Cloudflare Images Instance oluştur
 */
function getCloudflareImagesInstance() {
    if (!USE_CLOUDFLARE_IMAGES) {
        return null;
    }
    
    if (!defined('CLOUDFLARE_ACCOUNT_ID') || !defined('CLOUDFLARE_API_TOKEN')) {
        error_log("Cloudflare kimlik bilgileri eksik!");
        return null;
    }
    
    if (CLOUDFLARE_ACCOUNT_ID === 'YOUR_CLOUDFLARE_ACCOUNT_ID' || CLOUDFLARE_API_TOKEN === 'YOUR_CLOUDFLARE_API_TOKEN') {
        error_log("Cloudflare kimlik bilgileri henüz ayarlanmamış!");
        return null;
    }
    
    try {
        if (class_exists('MultiDomainCloudflareImages')) {
            return new MultiDomainCloudflareImages(CLOUDFLARE_ACCOUNT_ID, CLOUDFLARE_API_TOKEN);
        }
    } catch (Exception $e) {
        error_log("Cloudflare Images instance oluşturulamadı: " . $e->getMessage());
    }
    
    return null;
}

/**
 * Upload yöntemini belirle (Cloudflare veya Local)
 */
function getUploadMethod() {
    if (USE_CLOUDFLARE_IMAGES && getCloudflareImagesInstance() !== null) {
        return 'cloudflare';
    }
    
    return 'local';
}
?>
