<?php
/**
 * Google Gemini AI API Integration
 * Gayrimenkul blog içerikleri için optimize edilmiş
 */
class GeminiAI {
    private $api_key;
    private $base_url = 'https://generativelanguage.googleapis.com/v1beta/models/gemini-1.5-flash-latest:generateContent';
    
    public function __construct() {
        // API key'i environment'dan al veya burada tanımla
        $this->api_key = $this->getApiKey();
    }
    
    private function getApiKey() {
        // Güvenlik için API key'i environment variable'dan al
        if (isset($_ENV['GEMINI_API_KEY'])) {
            return $_ENV['GEMINI_API_KEY'];
        }
        
        // Geçici olarak burada tanımlayabilirsiniz (ÜRETİM İÇİN GÜVENLİ DEĞİL!)
        return 'AIzaSyCUZU7uXGO7FJtsDfhk1KBY8vtPHXRE91g';
    }
    
    /**
     * Gayrimenkul blog yazısı oluştur
     */
    public function generateRealEstateBlog($title, $keywords = [], $tone = 'professional', $length = 'medium') {
        try {
            // API key kontrolü
            if ($this->api_key === 'demo_key_will_use_fallback') {
                return $this->generateFallbackContent($title, $keywords, $tone, $length);
            }
            
            $prompt = $this->buildPrompt($title, $keywords, $tone, $length);
            
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.7,
                    'topK' => 40,
                    'topP' => 0.95,
                    'maxOutputTokens' => 2048,
                ]
            ];
            
            $response = $this->makeRequest($data);
            
            if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                $content = $response['candidates'][0]['content']['parts'][0]['text'];
                return $this->formatContent($content);
            } else {
                // API hatası durumunda fallback kullan
                return $this->generateFallbackContent($title, $keywords, $tone, $length);
            }
            
        } catch (Exception $e) {
            error_log("Gemini AI Error: " . $e->getMessage());
            return $this->generateFallbackContent($title, $keywords, $tone, $length);
        }
    }
    
    /**
     * AI prompt'ını oluştur
     */
    private function buildPrompt($title, $keywords, $tone, $length) {
        $keyword_text = implode(', ', $keywords);
        
        $tone_descriptions = [
            'professional' => 'profesyonel, ciddi ve güvenilir',
            'friendly' => 'samimi, anlaşılır ve dostane',
            'authoritative' => 'uzman, bilimsel ve otoriter'
        ];
        
        $length_descriptions = [
            'short' => '300-500 kelime',
            'medium' => '500-800 kelime',
            'long' => '800-1200 kelime'
        ];
        
        $current_tone = $tone_descriptions[$tone] ?? 'profesyonel';
        $current_length = $length_descriptions[$length] ?? '500-800 kelime';
        
        return "
Sen gayrimenkul sektöründe uzman bir blog yazarısın. Gökhan Aydınlı Gayrimenkul firması için blog yazısı yazacaksın.

GÖREV: '{$title}' başlıklı bir blog yazısı yaz.

ANAHTAR KELİMELER: {$keyword_text}

YAZIM STILI: {$current_tone}
UZUNLUK: {$current_length}

GEREKLİLİKLER:
1. HTML formatında yaz (h3, h4, h5, p, ul, li, strong, em etiketleri kullan)
2. SEO dostu başlıklar kullan
3. Anahtar kelimeleri doğal şekilde yerleştir
4. Türkçe yazım kurallarına dikkat et
5. Gayrimenkul sektörüne özgü terimler kullan
6. Pratik öneriler ve ipuçları ekle
7. İstanbul gayrimenkul piyasasına odaklan

YAPISINI ŞU ŞEKİLDE OLUŞTUR:
- Giriş paragrafı (konuyu tanıt)
- Ana başlıklar (h3 etiketiyle)
- Alt başlıklar (h4, h5 etiketiyle)
- Madde işaretli listeler
- Uzman önerileri (blockquote içinde)
- Sonuç paragrafı

ÖNEMLİ: Sadece blog içeriğini dön, başka açıklama yapma. HTML etiketlerini doğru kullan.
        ";
    }
    
    /**
     * API isteği gönder
     */
    private function makeRequest($data) {
        $url = $this->base_url . '?key=' . $this->api_key;
        
        // DEBUG: Request bilgilerini logla
        error_log("GEMINI DEBUG: URL = " . $url);
        error_log("GEMINI DEBUG: Request Data = " . json_encode($data));
        
        // cURL kullan (daha güvenilir)
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'User-Agent: GokhanAydinli-Blog-Generator/1.0'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false); // SSL sorunları için
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $error = curl_error($ch);
        curl_close($ch);
        
        // DEBUG: Response bilgilerini logla
        error_log("GEMINI DEBUG: HTTP Code = " . $http_code);
        error_log("GEMINI DEBUG: cURL Error = " . ($error ?: 'YOK'));
        error_log("GEMINI DEBUG: Response = " . substr($response ?: 'EMPTY', 0, 500));
        
        if ($response === false || !empty($error)) {
            throw new Exception('cURL Error: ' . $error);
        }
        
        if ($http_code !== 200) {
            error_log("Gemini API HTTP Error: $http_code - Response: $response");
            throw new Exception("API HTTP Error: $http_code - " . substr($response, 0, 200));
        }
        
        $decoded = json_decode($response, true);
        if (json_last_error() !== JSON_ERROR_NONE) {
            throw new Exception('JSON Decode Error: ' . json_last_error_msg());
        }
        
        // DEBUG: Decoded response
        error_log("GEMINI DEBUG: Decoded Keys = " . implode(', ', array_keys($decoded ?: [])));
        
        return $decoded;
    }
    
    /**
     * İçeriği formatla
     */
    private function formatContent($content) {
        // HTML etiketlerini temizle ve düzenle
        $content = trim($content);
        
        // Gereksiz boşlukları temizle
        $content = preg_replace('/\s+/', ' ', $content);
        $content = str_replace(['<p> ', ' </p>'], ['<p>', '</p>'], $content);
        
        return $content;
    }
    
    /**
     * API çalışmazsa fallback içerik
     */
    private function generateFallbackContent($title, $keywords, $tone, $length) {
        $keyword_text = implode(', ', array_slice($keywords, 0, 3));
        
        $content = "<h3>🏢 {$title} - Uzman Rehberi</h3>\n\n";
        
        $content .= "<p><strong>Gayrimenkul sektöründe</strong> başarılı olmak için doğru bilgilere sahip olmak çok önemlidir. ";
        $content .= "Bu rehberde <strong>{$keyword_text}</strong> konularında uzman görüşlerimizi paylaşıyoruz.</p>\n\n";
        
        $content .= "<h4>📊 Önemli Faktörler</h4>\n";
        $content .= "<ul>\n";
        foreach ($keywords as $keyword) {
            $content .= "<li><strong>" . ucfirst($keyword) . "</strong> - Detaylı analiz ve değerlendirme</li>\n";
        }
        $content .= "</ul>\n\n";
        
        $content .= "<h4>💡 Uzman Önerileri</h4>\n";
        $content .= "<blockquote>\n";
        $content .= "<p><em>\"15 yıllık deneyimimle, {$title} konusunda en önemli nokta sabırlı olmak ve doğru analizler yapmaktır.\"</em></p>\n";
        $content .= "<footer><strong>- Gökhan Aydınlı</strong></footer>\n";
        $content .= "</blockquote>\n\n";
        
        $content .= "<h4>🎯 Sonuç</h4>\n";
        $content .= "<p><strong>{$title}</strong> alanında başarılı olmak için yukarıdaki faktörleri göz önünde bulundurmanız önemlidir. ";
        $content .= "Profesyonel destek almaktan çekinmeyin.</p>\n\n";
        
        $content .= "<p><strong>İletişim:</strong> Gökhan Aydınlı Gayrimenkul olarak size en iyi hizmeti sunmaya hazırız. ";
        $content .= "Detaylı bilgi için bizimle iletişime geçebilirsiniz.</p>";
        
        return $content;
    }
    
    /**
     * Blog özeti oluştur
     */
    public function generateExcerpt($title, $keywords) {
        $keyword_text = implode(', ', array_slice($keywords, 0, 3));
        
        if ($this->api_key === 'demo_key_will_use_fallback') {
            return "✅ {$title} hakkında kapsamlı rehber. {$keyword_text} konularında uzman görüşleri ve pratik öneriler.";
        }
        
        try {
            $prompt = "'{$title}' başlıklı gayrimenkul blog yazısı için 150-200 karakter arası özet yaz. Anahtar kelimeler: {$keyword_text}. Sadece özeti yaz, başka açıklama yapma.";
            
            $data = [
                'contents' => [
                    [
                        'parts' => [
                            ['text' => $prompt]
                        ]
                    ]
                ],
                'generationConfig' => [
                    'temperature' => 0.5,
                    'maxOutputTokens' => 100,
                ]
            ];
            
            $response = $this->makeRequest($data);
            
            if ($response && isset($response['candidates'][0]['content']['parts'][0]['text'])) {
                return trim($response['candidates'][0]['content']['parts'][0]['text']);
            }
            
        } catch (Exception $e) {
            error_log("Gemini AI Excerpt Error: " . $e->getMessage());
        }
        
        return "✅ {$title} hakkında kapsamlı rehber. {$keyword_text} konularında uzman görüşleri ve pratik öneriler.";
    }
    
    /**
     * SEO meta açıklama oluştur
     */
    public function generateMetaDescription($title, $keywords) {
        $keyword_text = implode(', ', array_slice($keywords, 0, 2));
        return "🏠 {$title} | {$keyword_text} konularında uzman rehberi. Gökhan Aydınlı Gayrimenkul'dan profesyonel tavsiyelar. 2024 güncel bilgiler.";
    }
    
    /**
     * API durumunu kontrol et
     */
    public function isApiActive() {
        return $this->api_key !== 'demo_key_will_use_fallback';
    }
}
?>
