<?php
/**
 * Unsplash API Integration
 * Ücretsiz yüksek kaliteli fotoğraflar için
 */

class UnsplashAPI {
    private $access_key;
    private $api_url = 'https://api.unsplash.com';
    
    public function __construct() {
        // Ücretsiz Unsplash API Access Key
        // https://unsplash.com/developers adresinden ücretsiz key alabilirsiniz
        // Şu anda demo modunda çalışıyor - gerçek API için aşağıdaki key'i değiştirin
        $this->access_key = 'jCzmMieZYR6zNmoVBVOUtHOaWrVbww8kkEIp2XxzlWU'; // Gerçek key için: 'YOUR_UNSPLASH_ACCESS_KEY'
    }
    
    /**
     * Blog başlığına göre ilgili resim ara
     */
    public function searchPhotoByTitle($title, $query_hint = '') {
        try {
            // Başlıktan anahtar kelimeleri çıkar
            $keywords = $this->extractKeywords($title);
            
            // Query hint varsa ekle
            if (!empty($query_hint)) {
                $keywords .= ' ' . $query_hint;
            }
            
            // Emlak ile ilgili kelimeler ekle
            $real_estate_terms = 'real estate property house building architecture';
            $search_query = $keywords . ' ' . $real_estate_terms;
            
            return $this->searchPhoto($search_query);
            
        } catch (Exception $e) {
            error_log('Unsplash API Error: ' . $e->getMessage());
            return $this->getFallbackImage();
        }
    }
    
    /**
     * Anahtar kelimelere göre spesifik resim ara (YENİ!)
     */
    public function searchPhotoByKeywords($title, $keywords = []) {
        try {
            // Önce spesifik anahtar kelimeleri dene
            foreach ($keywords as $keyword) {
                $keyword = trim(strtolower($keyword));
                
                // Türkçe karakterleri normalize et
                $keyword = $this->normalizeText($keyword);
                
                // Türkçe yer adları için özel arama
                $location_searches = [
                    'beyoglu' => 'beyoglu galata tower istanbul turkey',
                    'kadikoy' => 'kadikoy istanbul bosphorus turkey',
                    'besiktas' => 'besiktas istanbul bosphorus turkey',
                    'uskudar' => 'uskudar istanbul asian side turkey',
                    'fatih' => 'fatih sultanahmet istanbul turkey',
                    'sisli' => 'sisli mecidiyekoy istanbul turkey',
                    'bakirkoy' => 'bakirkoy istanbul turkey',
                    'ankara' => 'ankara capital city turkey',
                    'izmir' => 'izmir aegean coast turkey',
                    'istanbul' => 'istanbul city turkey bosphorus',
                ];
                
                // Gayrimenkul terimleri için arama
                $property_searches = [
                    'villa' => 'luxury villa house mansion architecture',
                    'daire' => 'apartment modern interior home',
                    'ofis' => 'office building business district',
                    'dukkan' => 'commercial shop storefront business',
                    'yatirim' => 'investment property real estate building',
                    'kiralama' => 'rental property apartment building',
                    'konut' => 'residential house apartment home',
                    'ev' => 'house home residential building',
                ];
                
                $search_query = '';
                
                // Lokasyon araması
                if (isset($location_searches[$keyword])) {
                    $search_query = $location_searches[$keyword];
                    error_log("UNSPLASH: Lokasyon araması - " . $keyword . " -> " . $search_query);
                }
                // Property araması  
                elseif (isset($property_searches[$keyword])) {
                    $search_query = $property_searches[$keyword];
                    error_log("UNSPLASH: Property araması - " . $keyword . " -> " . $search_query);
                }
                // Genel arama
                else {
                    $search_query = $keyword . ' istanbul property real estate turkey';
                    error_log("UNSPLASH: Genel arama - " . $keyword . " -> " . $search_query);
                }
                
                // Arama yap
                $result = $this->searchPhoto($search_query);
                if ($result && $result['url'] !== $this->getFallbackImage()['url']) {
                    error_log("UNSPLASH: Başarılı arama bulundu: " . $keyword);
                    return $result;
                }
            }
            
            // Spesifik arama başarısızsa, eski yöntemi kullan
            error_log("UNSPLASH: Spesifik arama başarısız, genel arama yapılıyor");
            return $this->searchPhotoByTitle($title);
            
        } catch (Exception $e) {
            error_log('Unsplash Keywords API Error: ' . $e->getMessage());
            return $this->searchPhotoByTitle($title);
        }
    }
    
    /**
     * Unsplash'da fotoğraf ara
     */
    private function searchPhoto($query) {
        // Debug için log ekle
        error_log("UNSPLASH SEARCH: Query = " . $query);
        
        $url = $this->api_url . '/search/photos';
        $params = [
            'query' => $query,
            'per_page' => 5,
            'orientation' => 'landscape',
            'order_by' => 'relevant'
        ];
        
        $response = $this->makeRequest($url, $params);
        
        if ($response && isset($response['results']) && !empty($response['results'])) {
            $photo = $response['results'][0]; // İlk resmi al
            
            error_log("UNSPLASH SUCCESS: Found image = " . $photo['urls']['regular']);
            
            return [
                'url' => $photo['urls']['regular'],
                'thumb' => $photo['urls']['small'],
                'alt' => $photo['alt_description'] ?? $query,
                'credit' => 'Photo by ' . $photo['user']['name'] . ' on Unsplash',
                'download_location' => $photo['links']['download_location']
            ];
        }
        
        error_log("UNSPLASH FAILED: No results for query = " . $query);
        return $this->getFallbackImage();
    }
    
    /**
     * API isteği yap
     */
    private function makeRequest($url, $params = []) {
        $query_string = http_build_query($params);
        $full_url = $url . '?' . $query_string;
        
        $headers = [
            'Authorization: Client-ID ' . $this->access_key,
            'Accept-Version: v1'
        ];
        
        $context = stream_context_create([
            'http' => [
                'header' => implode("\r\n", $headers),
                'timeout' => 10
            ]
        ]);
        
        $response = @file_get_contents($full_url, false, $context);
        
        if ($response === false) {
            throw new Exception('API request failed');
        }
        
        return json_decode($response, true);
    }
    
    /**
     * Başlıktan anahtar kelimeleri çıkar
     */
    private function extractKeywords($title) {
        // Türkçe karakterleri normalize et
        $title = $this->normalizeText($title);
        
        // Gayrimenkul ile ilgili Türkçe-İngilizce kelime çevirileri
        $translations = [
            'emlak' => 'real estate',
            'gayrimenkul' => 'property',
            'ev' => 'house home',
            'daire' => 'apartment',
            'villa' => 'villa mansion',
            'ofis' => 'office',
            'dükkan' => 'shop store',
            'yatırım' => 'investment',
            'kiralama' => 'rental',
            'satış' => 'sale',
            'istanbul' => 'istanbul city',
            'ankara' => 'ankara',
            'izmir' => 'izmir',
            'antalya' => 'antalya',
            'bursa' => 'bursa',
            'konut' => 'residential housing',
            'ticari' => 'commercial',
            'lüks' => 'luxury',
            'modern' => 'modern',
            'merkez' => 'center downtown',
            'bölge' => 'district area',
            'proje' => 'project development',
            'inşaat' => 'construction building',
            'mimari' => 'architecture',
            'tasarım' => 'design',
            'bahçe' => 'garden',
            'balkon' => 'balcony',
            'manzara' => 'view scenic',
            'deniz' => 'sea ocean',
            'şehir' => 'city urban'
        ];
        
        $keywords = [];
        foreach ($translations as $turkish => $english) {
            if (stripos($title, $turkish) !== false) {
                $keywords[] = $english;
            }
        }
        
        // Hiç keyword bulunamazsa varsayılan kullan
        if (empty($keywords)) {
            $keywords[] = 'real estate property building';
        }
        
        return implode(' ', $keywords);
    }
    
    /**
     * Türkçe karakterleri normalize et
     */
    private function normalizeText($text) {
        $turkish = ['ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü'];
        $english = ['c', 'g', 'i', 'o', 's', 'u', 'C', 'G', 'I', 'I', 'O', 'S', 'U'];
        return str_replace($turkish, $english, $text);
    }
    
    /**
     * API çalışmazsa varsayılan resim döndür
     */
    private function getFallbackImage() {
        $fallback_images = [
            'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'https://images.unsplash.com/photo-1459767129954-1b1c1f9b9ace?ixlib=rb-4.0.3&w=1200&h=800&fit=crop'
        ];
        
        return [
            'url' => $fallback_images[array_rand($fallback_images)],
            'thumb' => $fallback_images[array_rand($fallback_images)],
            'alt' => 'Gayrimenkul görseli',
            'credit' => 'Photo from Unsplash',
            'download_location' => ''
        ];
    }
    
    /**
     * Demo/Test için - API Key olmadan çalışır
     */
    public function getDemoImage($title) {
        // Demo kategoriler
        $demo_images = [
            'apartment' => 'https://images.unsplash.com/photo-1522708323590-d24dbb6b0267?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'house' => 'https://images.unsplash.com/photo-1524231757912-21f4fe3a7200?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'office' => 'https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'investment' => 'https://images.unsplash.com/photo-1554224155-8d04cb21cd6c?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'istanbul' => 'https://images.unsplash.com/photo-1541888946425-d81bb19240f5?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'modern' => 'https://images.unsplash.com/photo-1460925895917-afdab827c52f?ixlib=rb-4.0.3&w=1200&h=800&fit=crop',
            'luxury' => 'https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=1200&h=800&fit=crop'
        ];
        
        // Başlığa göre kategori belirle
        $title_lower = strtolower($title);
        $category = 'house'; // varsayılan
        
        foreach ($demo_images as $key => $url) {
            if (strpos($title_lower, $key) !== false) {
                $category = $key;
                break;
            }
        }
        
        return [
            'url' => $demo_images[$category],
            'thumb' => $demo_images[$category],
            'alt' => $title,
            'credit' => 'Demo image from Unsplash',
            'download_location' => ''
        ];
    }
}

// Kullanım örneği:
/*
$unsplash = new UnsplashAPI();
$image = $unsplash->searchPhotoByTitle("İstanbul'da Lüks Villa Yatırımı", "luxury villa");
echo $image['url'];
*/
?>
