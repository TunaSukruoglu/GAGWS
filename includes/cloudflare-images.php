<?php
/**
 * Cloudflare Images API Integration
 * Bu dosya resim yükleme ve yönetimi için Cloudflare Images API'sini kullanır
 */

class CloudflareImages {
    private $account_id;
    private $api_token;
    private $base_url;
    
    public function __construct($account_id, $api_token) {
        $this->account_id = $account_id;
        $this->api_token = $api_token;
        $this->base_url = "https://api.cloudflare.com/client/v4/accounts/{$account_id}/images/v1";
    }
    
    /**
     * Resim yükle
     */
    public function uploadImage($file_path, $metadata = []) {
        $url = $this->base_url;
        
        $curl = curl_init();
        
        // Multipart form data oluştur
        $post_data = [
            'file' => new CURLFile($file_path),
            'metadata' => json_encode($metadata)
        ];
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_POSTFIELDS => $post_data,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_token,
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code == 200) {
            $result = json_decode($response, true);
            if ($result && $result['success']) {
                return [
                    'success' => true,
                    'image_id' => $result['result']['id'],
                    'image_url' => $result['result']['variants'][0], // Default variant
                    'variants' => $result['result']['variants']
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Upload failed: ' . $response
        ];
    }
    
    /**
     * Resim URL'ini oluştur
     */
    public function getImageUrl($image_id, $variant = 'public') {
        return "https://imagedelivery.net/{$this->account_id}/{$image_id}/{$variant}";
    }
    
    /**
     * Resim sil
     */
    public function deleteImage($image_id) {
        $url = $this->base_url . '/' . $image_id;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_token,
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $http_code == 200;
    }
    
    /**
     * Resim bilgilerini al
     */
    public function getImageInfo($image_id) {
        $url = $this->base_url . '/' . $image_id;
        
        $curl = curl_init();
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->api_token,
            ],
        ]);
        
        $response = curl_exec($curl);
        $http_code = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($http_code == 200) {
            $result = json_decode($response, true);
            return $result['result'] ?? null;
        }
        
        return null;
    }
    
    /**
     * Batch resim yükle
     */
    public function uploadMultipleImages($files, $metadata = []) {
        $results = [];
        
        foreach ($files as $index => $file) {
            $file_metadata = array_merge($metadata, [
                'batch_index' => $index,
                'upload_time' => date('Y-m-d H:i:s')
            ]);
            
            $result = $this->uploadImage($file, $file_metadata);
            $results[] = $result;
            
            // Rate limiting için kısa bekle
            usleep(100000); // 0.1 saniye
        }
        
        return $results;
    }
}

/**
 * Cloudflare Images instance'ını al
 */
function getCloudflareImages() {
    // Config dosyasından değerleri al
    if (file_exists(__DIR__ . '/cloudflare-config.php')) {
        require_once __DIR__ . '/cloudflare-config.php';
        $account_id = defined('CLOUDFLARE_ACCOUNT_ID') ? CLOUDFLARE_ACCOUNT_ID : null;
        $api_token = defined('CLOUDFLARE_API_TOKEN') ? CLOUDFLARE_API_TOKEN : null;
    } else {
        $account_id = null;
        $api_token = null;
    }
    
    if ($account_id && $api_token) {
        return new CloudflareImages($account_id, $api_token);
    }
    
    return null;
}

/**
 * Cloudflare Images aktif mi kontrol et
 */
function isCloudflareImagesEnabled() {
    $cf = getCloudflareImages();
    return $cf !== null;
}

/**
 * Resim URL'ini oluştur - hibrit sistem
 * Cloudflare aktifse oradan, değilse mevcut sistemden
 */
function getImageUrl($image_identifier, $variant = 'public') {
    // Cloudflare Images aktif mi?
    if (isCloudflareImagesEnabled()) {
        // Cloudflare Images ID'si mi yoksa dosya adı mı?
        if (isCloudflareImageId($image_identifier)) {
            $cf = getCloudflareImages();
            return $cf->getImageUrl($image_identifier, $variant);
        }
    }
    
    // Mevcut sistem (show-image.php)
    return "show-image.php?img=" . urlencode($image_identifier);
}

/**
 * Cloudflare Image ID'si mi kontrol et
 */
function isCloudflareImageId($identifier) {
    // Cloudflare Image ID'leri UUID formatında (36 karakter)
    return preg_match('/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i', $identifier);
}

/**
 * Resim migrasyonu için batch işlemi
 */
function migrateImagesToCloudflare($limit = 10) {
    if (!isCloudflareImagesEnabled()) {
        return ['success' => false, 'error' => 'Cloudflare Images not configured'];
    }
    
    global $conn;
    $cf = getCloudflareImages();
    
    // Henüz migrate edilmemiş resimleri al
    $query = "SELECT id, images FROM properties WHERE images IS NOT NULL AND images != '' AND cloudflare_images IS NULL LIMIT ?";
    $stmt = $conn->prepare($query);
    $stmt->bind_param('i', $limit);
    $stmt->execute();
    $result = $stmt->get_result();
    
    $migrated = 0;
    $errors = [];
    
    while ($property = $result->fetch_assoc()) {
        $images = json_decode($property['images'], true);
        if (!is_array($images)) continue;
        
        $cloudflare_images = [];
        
        foreach ($images as $image_name) {
            $file_path = "uploads/properties/" . $image_name;
            
            if (file_exists($file_path)) {
                $upload_result = $cf->uploadImage($file_path, [
                    'property_id' => $property['id'],
                    'original_filename' => $image_name,
                    'migration_date' => date('Y-m-d H:i:s')
                ]);
                
                if ($upload_result['success']) {
                    $cloudflare_images[] = $upload_result['image_id'];
                } else {
                    $errors[] = "Property {$property['id']}: " . $upload_result['error'];
                }
            }
        }
        
        // Cloudflare image ID'lerini kaydet
        if (!empty($cloudflare_images)) {
            $update_query = "UPDATE properties SET cloudflare_images = ? WHERE id = ?";
            $update_stmt = $conn->prepare($update_query);
            $cf_images_json = json_encode($cloudflare_images);
            $update_stmt->bind_param('si', $cf_images_json, $property['id']);
            $update_stmt->execute();
            
            $migrated++;
        }
    }
    
    return [
        'success' => true,
        'migrated' => $migrated,
        'errors' => $errors
    ];
}

/**
 * Property resimleri için hibrit URL listesi
 */
function getPropertyImageUrls($property_data, $variant = 'public') {
    $urls = [];
    
    // Önce Cloudflare Images'dan dene
    if (!empty($property_data['cloudflare_images'])) {
        $cf_images = json_decode($property_data['cloudflare_images'], true);
        if (is_array($cf_images)) {
            foreach ($cf_images as $image_id) {
                $urls[] = getImageUrl($image_id, $variant);
            }
        }
    }
    
    // Cloudflare'dan resim yoksa mevcut sistemi kullan
    if (empty($urls) && !empty($property_data['images'])) {
        $local_images = json_decode($property_data['images'], true);
        if (is_array($local_images)) {
            foreach ($local_images as $image_name) {
                $urls[] = getImageUrl($image_name, $variant);
            }
        }
    }
    
    return $urls;
}
?>
