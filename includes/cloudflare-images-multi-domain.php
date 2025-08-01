<?php
/**
 * Çoklu Domain Cloudflare Images Sistemi
 * Tek Cloudflare hesabı - Farklı domainler - Farklı logolar
 */

class MultiDomainCloudflareImages {
    
    private $accountId;
    private $token;
    private $apiEndpoint;
    private $domainConfigs;
    
    public function __construct($accountId = null, $token = null) {
        // Config'den otomatik al
        $this->accountId = $accountId ?? (defined('CLOUDFLARE_ACCOUNT_ID') ? CLOUDFLARE_ACCOUNT_ID : null);
        $this->token = $token ?? (defined('CLOUDFLARE_API_TOKEN') ? CLOUDFLARE_API_TOKEN : null);
        
        // Debug account ID
        error_log("Cloudflare Debug - Account ID: " . ($this->accountId ?: 'NOT SET'));
        error_log("Cloudflare Debug - Token: " . ($this->token ? 'SET (' . strlen($this->token) . ' chars)' : 'NOT SET'));
        
        if (!$this->accountId || !$this->token) {
            throw new Exception("Cloudflare credentials bulunamadı!");
        }
        
        $this->apiEndpoint = "https://api.cloudflare.com/client/v4/accounts/{$this->accountId}/images/v1";
        error_log("Cloudflare Debug - API Endpoint: " . $this->apiEndpoint);
        
        // Her domain için ayrı logo ve watermark ayarları
        $this->domainConfigs = [
            'gokhanaydinli.com' => [
                'logo_url' => 'https://gokhanaydinli.com/assets/logo-watermark.png',
                'company_name' => 'Gökhan Aydınlı Emlak',
                'website' => 'gokhanaydinli.com',
                'phone' => '+90 555 123 45 67',
                'position' => 'bottom-right',
                'opacity' => 80,
                'logo_size' => 'medium',
                'text_color' => '#FFFFFF',
                'background_color' => 'rgba(0,0,0,0.7)'
            ],
            'ankaraemlak.com' => [
                'logo_url' => 'https://ankaraemlak.com/assets/watermark.png',
                'company_name' => 'Ankara Emlak',
                'website' => 'ankaraemlak.com',
                'phone' => '+90 312 555 66 77',
                'position' => 'bottom-left',
                'opacity' => 75,
                'logo_size' => 'small',
                'text_color' => '#FFD700',
                'background_color' => 'rgba(0,0,0,0.8)'
            ],
            'istanbulemlak.com' => [
                'logo_url' => 'https://istanbulemlak.com/assets/brand-logo.png',
                'company_name' => 'İstanbul Emlak',
                'website' => 'istanbulemlak.com',
                'phone' => '+90 212 888 99 00',
                'position' => 'top-right',
                'opacity' => 85,
                'logo_size' => 'large',
                'text_color' => '#0066CC',
                'background_color' => 'rgba(255,255,255,0.9)'
            ]
        ];
    }
    
    /**
     * Domain'e göre resim yükleme
     */
    public function uploadImageForDomain($imagePath, $domain, $metadata = []) {
        
        // Domain config kontrolü
        if (!isset($this->domainConfigs[$domain])) {
            throw new Exception("Domain '{$domain}' için konfigürasyon bulunamadı!");
        }
        
        $config = $this->domainConfigs[$domain];
        
        // Metadata'ya domain bilgilerini ekle (klasörleme dahil)
        $metadata['domain'] = $domain;
        $metadata['folder'] = $this->getDomainFolder($domain);
        $metadata['company'] = $config['company_name'];
        $metadata['upload_time'] = date('Y-m-d H:i:s');
        $metadata['watermark_applied'] = true;
        $metadata['original_filename'] = basename($imagePath);
        
        // Watermark ayarlarını hazırla
        $watermarkParams = $this->buildWatermarkForDomain($config);
        
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
                'requireSignedURLs' => 'false'
                // Cloudflare otomatik ID oluşturuyor, custom ID gerekmiyor
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['result'])) {
                return [
                    'success' => true,
                    'domain' => $domain,
                    'folder' => $metadata['folder'],
                    'image_id' => $result['result']['id'],
                    'urls' => $this->generateDomainUrls($result['result']['id'], $domain),
                    'config_applied' => $config['company_name'],
                    'filename' => $result['result']['filename'] ?? $originalName
                ];
            }
        }
        
        return [
            'success' => false,
            'domain' => $domain,
            'error' => $response ?: $error,
            'http_code' => $httpCode
        ];
    }
    
    /**
     * Domain'e özel watermark parametreleri
     */
    private function buildWatermarkForDomain($config) {
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
                    'position' => $this->getTextPosition($config['position'], 'company'),
                    'color' => $config['text_color'],
                    'size' => 18,
                    'font' => 'arial-bold',
                    'background' => $config['background_color']
                ],
                'website' => [
                    'text' => $config['website'],
                    'position' => $this->getTextPosition($config['position'], 'website'),
                    'color' => $config['text_color'],
                    'size' => 14,
                    'font' => 'arial',
                    'background' => $config['background_color']
                ],
                'phone' => [
                    'text' => $config['phone'],
                    'position' => $this->getTextPosition($config['position'], 'phone'),
                    'color' => $config['text_color'],
                    'size' => 12,
                    'font' => 'arial',
                    'background' => $config['background_color']
                ]
            ]
        ];
    }
    
    /**
     * Logo boyut scale
     */
    private function getLogoScale($size) {
        switch ($size) {
            case 'small': return 0.06;
            case 'medium': return 0.10;
            case 'large': return 0.15;
            default: return 0.08;
        }
    }
    
    /**
     * Text pozisyonu hesaplama
     */
    private function getTextPosition($logoPosition, $textType) {
        $positions = [
            'top-left' => [
                'company' => ['x' => 20, 'y' => 20],
                'website' => ['x' => 20, 'y' => 45],
                'phone' => ['x' => 20, 'y' => 65]
            ],
            'top-right' => [
                'company' => ['x' => -20, 'y' => 20, 'align' => 'right'],
                'website' => ['x' => -20, 'y' => 45, 'align' => 'right'],
                'phone' => ['x' => -20, 'y' => 65, 'align' => 'right']
            ],
            'bottom-left' => [
                'company' => ['x' => 20, 'y' => -65],
                'website' => ['x' => 20, 'y' => -45],
                'phone' => ['x' => 20, 'y' => -20]
            ],
            'bottom-right' => [
                'company' => ['x' => -20, 'y' => -65, 'align' => 'right'],
                'website' => ['x' => -20, 'y' => -45, 'align' => 'right'],
                'phone' => ['x' => -20, 'y' => -20, 'align' => 'right']
            ]
        ];
        
        return $positions[$logoPosition][$textType] ?? ['x' => 20, 'y' => 20];
    }
    
    /**
     * Domain'e özel URL'ler oluştur
     */
    private function generateDomainUrls($imageId, $domain) {
        return [
            'thumbnail' => "https://imagedelivery.net/{$this->accountId}/{$imageId}/thumbnail",
            'small' => "https://imagedelivery.net/{$this->accountId}/{$imageId}/small",
            'medium' => "https://imagedelivery.net/{$this->accountId}/{$imageId}/medium", 
            'large' => "https://imagedelivery.net/{$this->accountId}/{$imageId}/large",
            'original' => "https://imagedelivery.net/{$this->accountId}/{$imageId}/public",
            // Domain tracking için özel parametreler
            'tracking_params' => "?source={$domain}&t=" . time()
        ];
    }
    
    /**
     * Çoklu domain'den batch upload
     */
    public function batchUploadMultipleDomains($uploadsData) {
        $results = [];
        
        foreach ($uploadsData as $upload) {
            $result = $this->uploadImageForDomain(
                $upload['image_path'],
                $upload['domain'],
                $upload['metadata'] ?? []
            );
            
            $results[$upload['domain']][] = $result;
        }
        
        return $results;
    }
    
    /**
     * Domain'e yeni konfigürasyon ekle
     */
    public function addDomainConfig($domain, $config) {
        $this->domainConfigs[$domain] = $config;
        
        // Config'i veritabanına kaydet (persistent storage için)
        $this->saveDomainConfig($domain, $config);
    }
    
    /**
     * Domain istatistikleri
     */
    public function getDomainStats($domain = null) {
        $curl = curl_init();
        
        $url = $this->apiEndpoint . '/stats';
        if ($domain) {
            $url .= "?domain={$domain}";
        }
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $url,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token
            ]
        ]);
        
        $response = curl_exec($curl);
        curl_close($curl);
        
        return json_decode($response, true);
    }
    
    /**
     * Domain config'ini veritabanına kaydet
     */
    private function saveDomainConfig($domain, $config) {
        global $conn;
        
        if ($conn) {
            $stmt = $conn->prepare("
                INSERT INTO domain_image_configs (domain, config, created_at) 
                VALUES (?, ?, NOW()) 
                ON DUPLICATE KEY UPDATE 
                config = VALUES(config), updated_at = NOW()
            ");
            
            $stmt->bind_param("ss", $domain, json_encode($config));
            $stmt->execute();
        }
    }
    
    /**
     * Cloudflare resimlerini veritabanına kaydet
     */
    public function saveToDatabase($propertyId, $domain, $cloudflareImageId, $urls, $metadata = []) {
        global $conn;
        
        if ($conn) {
            $stmt = $conn->prepare("
                INSERT INTO cloudflare_images (property_id, domain, cloudflare_image_id, image_urls, metadata, created_at) 
                VALUES (?, ?, ?, ?, ?, NOW())
            ");
            
            $urls_json = json_encode($urls);
            $metadata_json = json_encode($metadata);
            
            $stmt->bind_param("issss", $propertyId, $domain, $cloudflareImageId, $urls_json, $metadata_json);
            return $stmt->execute();
        }
        
        return false;
    }
    
    /**
     * Basit upload fonksiyonu (domain bazlı klasörleme ile)
     */
    public function simpleUpload($imagePath, $metadata = []) {
        // Domain bilgisini metadata'ya ekle
        $currentDomain = $_SERVER['HTTP_HOST'] ?? 'gokhanaydinli.com';
        $metadata['domain'] = $currentDomain;
        $metadata['folder'] = $this->getDomainFolder($currentDomain);
        $metadata['upload_time'] = date('Y-m-d H:i:s');
        $metadata['original_filename'] = basename($imagePath);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiEndpoint,
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_POST => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token
            ],
            CURLOPT_POSTFIELDS => [
                'file' => new CURLFile($imagePath),
                'metadata' => json_encode($metadata),
                'requireSignedURLs' => 'false'
                // Cloudflare otomatik ID oluşturuyor, custom ID gerekmiyor
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        $error = curl_error($curl);
        curl_close($curl);

        // Debug logging
        error_log("Cloudflare Upload Debug:");
        error_log("- File: " . $imagePath);
        error_log("- File exists: " . (file_exists($imagePath) ? 'YES' : 'NO'));
        error_log("- File size: " . (file_exists($imagePath) ? filesize($imagePath) : 'N/A'));
        error_log("- HTTP Code: " . $httpCode);
        error_log("- cURL Error: " . ($error ?: 'None'));
        error_log("- Response: " . $response);

        if ($httpCode === 200) {
            $result = json_decode($response, true);
            if ($result && isset($result['result'])) {
                error_log("✅ Cloudflare upload SUCCESS: " . $result['result']['id']);
                return [
                    'success' => true,
                    'image_id' => $result['result']['id'],
                    'urls' => $this->generateDomainUrls($result['result']['id'], $currentDomain),
                    'filename' => $result['result']['filename'] ?? $originalName,
                    'domain' => $currentDomain,
                    'folder' => $metadata['folder']
                ];
            } else {
                error_log("❌ Cloudflare response parsing failed");
                return [
                    'success' => false,
                    'error' => 'Invalid response format: ' . $response,
                    'http_code' => $httpCode,
                    'domain' => $currentDomain
                ];
            }
        }

        error_log("❌ Cloudflare upload FAILED - HTTP: {$httpCode}");
        return [
            'success' => false,
            'error' => $response ?: $error ?: 'Unknown error',
            'http_code' => $httpCode,
            'domain' => $currentDomain,
            'curl_error' => $error
        ];
    }
    
    /**
     * Domain bazlı klasör adı oluştur
     */
    private function getDomainFolder($domain) {
        $safeDomainName = $this->sanitizeDomainName($domain);
        return $safeDomainName . '_images';
    }
    
    /**
     * Domain adını güvenli hale getir (dosya sistemi için)
     */
    private function sanitizeDomainName($domain) {
        // www. prefixi varsa kaldır
        $domain = preg_replace('/^www\./', '', $domain);
        
        // Sadece alfanumerik karakterler ve tire
        $domain = preg_replace('/[^a-zA-Z0-9.-]/', '_', $domain);
        
        // Nokta karakterlerini alt çizgi ile değiştir
        $domain = str_replace('.', '_', $domain);
        
        return strtolower($domain);
    }
    
    /**
     * Domain bazlı image listesi getir
     */
    public function getDomainImages($domain = null) {
        $domain = $domain ?? ($_SERVER['HTTP_HOST'] ?? 'gokhanaydinli.com');
        $safeDomainName = $this->sanitizeDomainName($domain);
        
        $curl = curl_init();
        
        curl_setopt_array($curl, [
            CURLOPT_URL => $this->apiEndpoint . '/stats',
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_HTTPHEADER => [
                'Authorization: Bearer ' . $this->token
            ]
        ]);
        
        $response = curl_exec($curl);
        $httpCode = curl_getinfo($curl, CURLINFO_HTTP_CODE);
        curl_close($curl);
        
        if ($httpCode === 200) {
            $result = json_decode($response, true);
            
            // Domain'e ait resimleri filtrele
            if ($result && isset($result['result']['images'])) {
                $domainImages = array_filter($result['result']['images'], function($image) use ($safeDomainName) {
                    return isset($image['metadata']['domain']) && 
                           $this->sanitizeDomainName($image['metadata']['domain']) === $safeDomainName;
                });
                
                return [
                    'success' => true,
                    'domain' => $domain,
                    'folder' => $this->getDomainFolder($domain),
                    'images' => array_values($domainImages),
                    'count' => count($domainImages)
                ];
            }
        }
        
        return [
            'success' => false,
            'error' => 'Could not retrieve domain images',
            'domain' => $domain
        ];
    }
}
?>
