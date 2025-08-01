<?php
/**
 * Cloudflare Images Integration for Property Listing
 * Mevcut sisteme entegre edilecek kod parçaları
 */

// === 1. CONFIG DOSYASI (config/cloudflare.php) ===
class CloudflareConfig {
    const ACCOUNT_ID = 'prdw3ANMyocSBJD-Do1EeQ'; // Buraya account ID'nizi yazın
    const API_TOKEN = 'K_Z-yXtXlQTI2K9hOI6k5m7hJBKEz6C_OPDeYrqv';   // Buraya API token'ınızı yazın
    const DELIVERY_URL = 'https://imagedelivery.net/' . self::ACCOUNT_ID;
    
    // Domain bazlı watermark ayarları
    public static function getDomainConfig($domain = null) {
        if (!$domain) {
            $domain = $_SERVER['HTTP_HOST'] ?? 'default';
        }
        
        $configs = [
            'gokhanaydinli.com' => [
                'logo_url' => 'https://gokhanaydinli.com/assets/watermark-logo.png',
                'company_name' => 'Gökhan Aydınlı Emlak',
                'website' => 'gokhanaydinli.com',
                'phone' => '+90 555 123 45 67',
                'position' => 'bottom-right',
                'opacity' => 80,
                'logo_size' => 'medium'
            ],
            'default' => [
                'logo_url' => 'https://your-domain.com/assets/default-logo.png',
                'company_name' => 'Emlak Sitesi',
                'website' => $_SERVER['HTTP_HOST'] ?? 'localhost',
                'phone' => '+90 XXX XXX XX XX',
                'position' => 'bottom-right',
                'opacity' => 75,
                'logo_size' => 'medium'
            ]
        ];
        
        return $configs[$domain] ?? $configs['default'];
    }
}

// === 2. CLOUDFLARE IMAGES CLASS (includes/CloudflareImages.php) ===
class CloudflareImages {
    private $accountId;
    private $token;
    private $apiEndpoint;
    
    public function __construct() {
        $this->accountId = CloudflareConfig::ACCOUNT_ID;
        $this->token = CloudflareConfig::API_TOKEN;
        $this->apiEndpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/images/v1";
    }
    
    /**
     * Upload image with domain-specific watermark
     */
    public function uploadPropertyImage($imagePath, $propertyId, $metadata = []) {
        $domain = $_SERVER['HTTP_HOST'] ?? 'default';
        $config = CloudflareConfig::getDomainConfig($domain);
        
        // Metadata'ya property bilgilerini ekle
        $metadata = array_merge($metadata, [
            'property_id' => $propertyId,
            'domain' => $domain,
            'company' => $config['company_name'],
            'upload_time' => date('Y-m-d H:i:s'),
            'watermark_applied' => true
        ]);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token,
                'Content-Type: multipart/form-data'
            ],
            CURLOPT_POSTFIELDS => [
                'file' => new CURLFile($imagePath),
                'metadata' => json_encode($metadata),
                'watermark' => json_encode($this->buildWatermarkParams($config))
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            return [
                'success' => true,
                'image_id' => $result['result']['id'],
                'urls' => $this->generateImageUrls($result['result']['id']),
                'domain' => $domain
            ];
        } else {
            error_log("Cloudflare upload error: " . $response);
            return [
                'success' => false,
                'error' => $response
            ];
        }
    }
    
    /**
     * Build watermark parameters for domain
     */
    private function buildWatermarkParams($config) {
        return [
            'logo' => [
                'url' => $config['logo_url'],
                'position' => $config['position'],
                'opacity' => $config['opacity'] / 100,
                'scale' => $this->getLogoScale($config['logo_size'])
            ],
            'text_overlay' => [
                'company' => [
                    'text' => $config['company_name'],
                    'position' => 'bottom-right',
                    'color' => '#FFFFFF',
                    'size' => 18,
                    'font' => 'arial-bold'
                ],
                'website' => [
                    'text' => $config['website'],
                    'position' => 'bottom-right-2',
                    'color' => '#FFFFFF',
                    'size' => 14,
                    'font' => 'arial'
                ]
            ]
        ];
    }
    
    private function getLogoScale($size) {
        switch ($size) {
            case 'small': return 0.06;
            case 'medium': return 0.10;
            case 'large': return 0.15;
            default: return 0.08;
        }
    }
    
    /**
     * Generate image URLs for different sizes
     */
    public function generateImageUrls($imageId) {
        $baseUrl = CloudflareConfig::DELIVERY_URL;
        return [
            'thumbnail' => "{$baseUrl}/{$imageId}/thumbnail",  // 150x150
            'small' => "{$baseUrl}/{$imageId}/small",         // 400x400
            'medium' => "{$baseUrl}/{$imageId}/medium",       // 800x800
            'large' => "{$baseUrl}/{$imageId}/large",         // 1200x1200
            'original' => "{$baseUrl}/{$imageId}/public"      // Original
        ];
    }
    
    /**
     * Delete image from Cloudflare
     */
    public function deleteImage($imageId) {
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiEndpoint . '/' . $imageId,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_CUSTOMREQUEST => 'DELETE',
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        return $httpCode === 200;
    }
}

// === 3. DATABASE MIGRATION (sql/cloudflare_images.sql) ===
/*
-- Properties tablosuna Cloudflare Images için yeni sütunlar ekle
ALTER TABLE properties ADD COLUMN cloudflare_images JSON AFTER images;
ALTER TABLE properties ADD COLUMN cloudflare_main_image VARCHAR(255) AFTER main_image;

-- Cloudflare Images için ayrı tablo (detaylı tracking için)
CREATE TABLE property_cloudflare_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    cloudflare_image_id VARCHAR(255) NOT NULL,
    original_filename VARCHAR(255),
    image_urls JSON,
    metadata JSON,
    is_main BOOLEAN DEFAULT FALSE,
    domain VARCHAR(255),
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_property (property_id),
    INDEX idx_cloudflare (cloudflare_image_id),
    INDEX idx_domain (domain),
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);
*/

// === 4. MEVCUT FORM'A ENTEGRASYON - ADD-PROPERTY.PHP DEĞİŞİKLİKLERİ ===

// Form işleme kısmına Cloudflare entegrasyonu ekleyin (yaklaşık 400. satır civarı)
// Mevcut resim yükleme kısmını şu şekilde değiştirin:

function processPropertyImages($propertyId, $editMode = false) {
    error_log("=== CLOUDFLARE IMAGES PROCESSING START ===");
    
    $cloudflare = new CloudflareImages();
    $uploadedCloudflareImages = [];
    $finalImagesArray = [];
    
    // Edit mode: Mevcut resimlerden başla
    if ($editMode && !empty($_POST['updated_existing_images'])) {
        $existingImages = json_decode($_POST['updated_existing_images'], true);
        if (is_array($existingImages)) {
            $finalImagesArray = $existingImages;
            error_log("Edit mode: Starting with existing images: " . count($existingImages));
        }
    }
    
    // Yeni resimler varsa Cloudflare'e yükle
    if (!empty($_FILES['property_images']['name'][0])) {
        $tempDir = sys_get_temp_dir();
        $fileCount = count($_FILES['property_images']['name']);
        
        error_log("Processing {$fileCount} new images for Cloudflare");
        
        for ($i = 0; $i < $fileCount; $i++) {
            if ($_FILES['property_images']['error'][$i] === UPLOAD_ERR_OK) {
                $tmpName = $_FILES['property_images']['tmp_name'][$i];
                $originalName = $_FILES['property_images']['name'][$i];
                $fileSize = $_FILES['property_images']['size'][$i];
                
                // Dosya validasyonu
                if ($fileSize > 5 * 1024 * 1024) {
                    error_log("File too large: {$originalName}");
                    continue;
                }
                
                // Cloudflare'e yükle
                $metadata = [
                    'original_filename' => $originalName,
                    'file_size' => $fileSize,
                    'upload_index' => $i
                ];
                
                $uploadResult = $cloudflare->uploadPropertyImage($tmpName, $propertyId, $metadata);
                
                if ($uploadResult['success']) {
                    $uploadedCloudflareImages[] = [
                        'cloudflare_id' => $uploadResult['image_id'],
                        'urls' => $uploadResult['urls'],
                        'filename' => $originalName,
                        'is_main' => (count($finalImagesArray) === 0 && count($uploadedCloudflareImages) === 0)
                    ];
                    
                    error_log("Cloudflare upload success: {$uploadResult['image_id']}");
                } else {
                    error_log("Cloudflare upload failed: {$originalName} - {$uploadResult['error']}");
                }
            }
        }
    }
    
    // Cloudflare images'ları veritabanına kaydet
    if (!empty($uploadedCloudflareImages)) {
        saveCloudflareImages($propertyId, $uploadedCloudflareImages);
    }
    
    // Ana resim belirleme
    $mainImageId = '';
    $allCloudflareImages = getAllPropertyCloudflareImages($propertyId);
    
    if (!empty($allCloudflareImages)) {
        $mainImage = array_filter($allCloudflareImages, function($img) {
            return $img['is_main'] == 1;
        });
        
        if (empty($mainImage) && !empty($allCloudflareImages)) {
            // İlk resmi ana resim yap
            setMainCloudflareImage($propertyId, $allCloudflareImages[0]['cloudflare_image_id']);
            $mainImageId = $allCloudflareImages[0]['cloudflare_image_id'];
        } else {
            $mainImageId = array_values($mainImage)[0]['cloudflare_image_id'];
        }
    }
    
    error_log("=== CLOUDFLARE IMAGES PROCESSING END ===");
    
    return [
        'cloudflare_images' => json_encode($allCloudflareImages),
        'main_cloudflare_image' => $mainImageId,
        'images_count' => count($allCloudflareImages)
    ];
}

function saveCloudflareImages($propertyId, $cloudflareImages) {
    global $conn;
    
    foreach ($cloudflareImages as $image) {
        $stmt = $conn->prepare("
            INSERT INTO property_cloudflare_images 
            (property_id, cloudflare_image_id, original_filename, image_urls, metadata, is_main, domain) 
            VALUES (?, ?, ?, ?, ?, ?, ?)
        ");
        
        $domain = $_SERVER['HTTP_HOST'] ?? 'localhost';
        $metadata = json_encode([
            'upload_date' => date('Y-m-d H:i:s'),
            'file_size' => 0
        ]);
        
        $stmt->bind_param("issssis", 
            $propertyId,
            $image['cloudflare_id'],
            $image['filename'],
            json_encode($image['urls']),
            $metadata,
            $image['is_main'] ? 1 : 0,
            $domain
        );
        
        $stmt->execute();
        error_log("Saved Cloudflare image to DB: {$image['cloudflare_id']}");
    }
}

function getAllPropertyCloudflareImages($propertyId) {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT cloudflare_image_id, original_filename, image_urls, is_main 
        FROM property_cloudflare_images 
        WHERE property_id = ? 
        ORDER BY is_main DESC, upload_date ASC
    ");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    
    $result = $stmt->get_result();
    return $result->fetch_all(MYSQLI_ASSOC);
}

function setMainCloudflareImage($propertyId, $cloudflareImageId) {
    global $conn;
    
    // Önce tüm resimleri non-main yap
    $stmt = $conn->prepare("UPDATE property_cloudflare_images SET is_main = 0 WHERE property_id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    
    // Seçili resmi main yap
    $stmt = $conn->prepare("UPDATE property_cloudflare_images SET is_main = 1 WHERE property_id = ? AND cloudflare_image_id = ?");
    $stmt->bind_param("is", $propertyId, $cloudflareImageId);
    $stmt->execute();
}

// === 5. FORM İŞLEME KISMI DEĞİŞİKLİKLERİ ===
// Ana form işleme kısmında (INSERT/UPDATE öncesi) şu kodu ekleyin:

// Cloudflare Images işleme
$cloudflareResult = processPropertyImages($edit_mode ? $edit_id : 0, $edit_mode);

// Database insert/update kısmında eski images alanlarını da güncelle:
if ($edit_mode && $edit_id) {
    // UPDATE query'sine ekleyin
    $query = "UPDATE properties SET 
        title = ?,
        description = ?,
        price = ?,
        type = ?,
        category = ?,
        subcategory = ?,
        listing_type = ?,
        area_gross = ?,
        area_net = ?,
        area = ?,
        address = ?,
        city = ?,
        district = ?,
        room_count = ?,
        bedrooms = ?,
        living_room_count = ?,
        bathrooms = ?,
        floor = ?,
        building_floors = ?,
        year_built = ?,
        building_age = ?,
        heating = ?,
        elevator = ?,
        parking = ?,
        furnished = ?,
        usage_status = ?,
        dues = ?,
        credit_eligible = ?,
        deed_status = ?,
        exchange = ?,
        location_type = ?,
        featured = ?,
        cloudflare_images = ?,
        cloudflare_main_image = ?,
        updated_at = NOW()
        WHERE id = ? AND (user_id = ? OR BINARY ? = 'admin')";
        
    // bind_param'a ekleyin:
    $stmt->bind_param("ssdssssdddsssiiiississssisdissssssiis", 
        $title, $description, $price, $type, $category, $subcategory,
        $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
        $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
        $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
        $deed_status, $exchange, $location_type, $featured,
        $cloudflareResult['cloudflare_images'], $cloudflareResult['main_cloudflare_image'],
        $edit_id, $user_id, $user_data['role']);
        
} else {
    // INSERT query'sine ekleyin
    $query = "INSERT INTO properties SET 
        user_id = ?,
        title = ?,
        description = ?,
        price = ?,
        type = ?,
        category = ?,
        subcategory = ?,
        listing_type = ?,
        area_gross = ?,
        area_net = ?,
        area = ?,
        address = ?,
        city = ?,
        district = ?,
        room_count = ?,
        bedrooms = ?,
        living_room_count = ?,
        bathrooms = ?,
        floor = ?,
        building_floors = ?,
        year_built = ?,
        building_age = ?,
        heating = ?,
        elevator = ?,
        parking = ?,
        furnished = ?,
        usage_status = ?,
        dues = ?,
        credit_eligible = ?,
        deed_status = ?,
        exchange = ?,
        location_type = ?,
        featured = ?,
        cloudflare_images = ?,
        cloudflare_main_image = ?,
        status = 'active',
        created_at = NOW()";
        
    $stmt->bind_param("issdssssdddsssiiiiiiissssisssssissss", 
        $user_id, $title, $description, $price, $type, $category, $subcategory,
        $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
        $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
        $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
        $deed_status, $exchange, $location_type, $featured,
        $cloudflareResult['cloudflare_images'], $cloudflareResult['main_cloudflare_image']);
}

// === 6. FRONTEND DEĞİŞİKLİKLERİ ===
// Photo upload kısmına progress bar ekleyin:
?>

<script>
// Cloudflare upload progress tracking
function updatePhotoDisplay() {
    console.log('updatePhotoDisplay with Cloudflare integration');
    const selectedPhotosDiv = document.getElementById('selectedPhotos');
    const photosGrid = document.getElementById('photosGrid');
    const photoCounter = document.getElementById('photoCounter');
    
    if (selectedPhotos.length === 0) {
        selectedPhotosDiv.style.display = 'none';
        return;
    }
    
    selectedPhotosDiv.style.display = 'block';
    photoCounter.textContent = selectedPhotos.length;
    
    photosGrid.innerHTML = '';
    
    selectedPhotos.forEach((photo, index) => {
        const photoDiv = document.createElement('div');
        photoDiv.className = `photo-item ${photo.isMain ? 'main-photo' : ''}`;
        photoDiv.dataset.id = photo.id;
        
        // Cloudflare upload indicator ekle
        photoDiv.innerHTML = `
            <div class="photo-preview" id="preview-${photo.id}">
                <div class="upload-progress" id="progress-${photo.id}">
                    <div class="spinner-border spinner-border-sm text-primary" role="status">
                        <span class="visually-hidden">Yükleniyor...</span>
                    </div>
                    <small class="d-block mt-2">Cloudflare'e yükleniyor...</small>
                </div>
            </div>
            <div class="photo-overlay">
                <div class="photo-actions">
                    <button type="button" class="photo-action-btn delete" onclick="removePhoto('${photo.id}')" title="Sil">
                        <i class="fas fa-trash"></i>
                    </button>
                    ${!photo.isMain ? `<button type="button" class="photo-action-btn main" onclick="setAsMain('${photo.id}')" title="Ana Fotoğraf Yap">
                        <i class="fas fa-star"></i>
                    </button>` : ''}
                </div>
            </div>
            <div class="photo-number">${index + 1}</div>
        `;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            const previewDiv = document.getElementById(`preview-${photo.id}`);
            if (previewDiv) {
                previewDiv.innerHTML = `<img src="${e.target.result}" alt="Fotoğraf ${index + 1}" class="img-fluid">`;
            }
        };
        reader.readAsDataURL(photo.file);
        
        photosGrid.appendChild(photoDiv);
    });
}

// Form submit ile Cloudflare upload status göster
document.getElementById('propertyForm').addEventListener('submit', function(e) {
    if (selectedPhotos.length > 0) {
        // Submit button'ı değiştir
        const submitBtn = document.querySelector('button[type="submit"]');
        submitBtn.innerHTML = '<i class="fas fa-cloud-upload-alt me-2"></i>Cloudflare\'e Yükleniyor...';
        submitBtn.disabled = true;
        
        // Progress mesajı göster
        showAlert('Fotoğraflar Cloudflare\'e yükleniyor, lütfen bekleyin...', 'info');
    }
});

// Success callback için
function onCloudflareUploadComplete(results) {
    console.log('Cloudflare upload completed:', results);
    showAlert(`${results.count} fotoğraf başarıyla Cloudflare'e yüklendi!`, 'success');
}
</script>

<?php
// === 7. GÖRÜNTÜLEME FONKSİYONLARI (includes/image-helpers.php) ===

function getPropertyCloudflareImages($propertyId, $size = 'medium') {
    global $conn;
    
    $stmt = $conn->prepare("
        SELECT cloudflare_image_id, original_filename, image_urls, is_main 
        FROM property_cloudflare_images 
        WHERE property_id = ? 
        ORDER BY is_main DESC, upload_date ASC
    ");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    
    $images = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
    
    // URL'leri parse et ve istenen boyutu döndür
    foreach ($images as &$image) {
        $urls = json_decode($image['image_urls'], true);
        $image['url'] = $urls[$size] ?? $urls['medium'] ?? '';
        $image['all_urls'] = $urls;
    }
    
    return $images;
}

function getPropertyMainCloudflareImage($propertyId, $size = 'medium') {
    $images = getPropertyCloudflareImages($propertyId, $size);
    
    foreach ($images as $image) {
        if ($image['is_main']) {
            return $image;
        }
    }
    
    return $images[0] ?? null;
}

// Fallback function - eski sistem ile uyumluluk
function getPropertyMainImage($propertyId, $size = 'medium') {
    // Önce Cloudflare'den dene
    $cloudflareImage = getPropertyMainCloudflareImage($propertyId, $size);
    if ($cloudflareImage) {
        return $cloudflareImage['url'];
    }
    
    // Fallback: eski sistem
    global $conn;
    $stmt = $conn->prepare("SELECT main_image FROM properties WHERE id = ?");
    $stmt->bind_param("i", $propertyId);
    $stmt->execute();
    $result = $stmt->get_result()->fetch_assoc();
    
    if ($result && $result['main_image']) {
        return "../smart-image.php?img=" . urlencode($result['main_image']) . "&width=400&height=300";
    }
    
    return "assets/images/no-image.jpg";
}
?>