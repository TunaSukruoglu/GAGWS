<?php
/**
 * Cloudflare Images Configuration
 * Bu dosyayı güvenli bir yerde tutun ve git'e commit etmeyin
 */

// Cloudflare Images Settings
if (!defined('CLOUDFLARE_ACCOUNT_ID')) {
    define('CLOUDFLARE_ACCOUNT_ID', 'prdw3ANMyocSBJD-Do1EeQ');
}
if (!defined('CLOUDFLARE_API_TOKEN')) {
    define('CLOUDFLARE_API_TOKEN', 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv');
}

// Image variants configuration
define('CF_VARIANTS', [
    'thumbnail' => 'thumbnail',  // 150x150
    'small' => 'small',         // 400x400
    'medium' => 'medium',       // 800x800
    'large' => 'large',         // 1200x1200
    'public' => 'public'        // Original
]);

// Upload settings
define('CF_MAX_FILE_SIZE', 10 * 1024 * 1024); // 10MB
define('CF_ALLOWED_TYPES', ['image/jpeg', 'image/png', 'image/gif', 'image/webp']);

// Migration settings
define('CF_MIGRATE_BATCH_SIZE', 5); // Batch'te kaç resim yüklensin
define('CF_MIGRATE_DELAY', 200000); // Microseconds (0.2 saniye)
?>
