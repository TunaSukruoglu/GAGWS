<?php
// ACIL DÜZELTME - COMMON FUNCTIONS

function getImagePath($imagePath, $useCloudflare = false) {
    if (empty($imagePath)) {
        return 'images/default-property.jpg';
    }
    
    // JSON array ise decode et ve ilkini al
    if (is_string($imagePath) && (strpos($imagePath, '[') === 0 || strpos($imagePath, '{') === 0)) {
        $decoded = json_decode($imagePath, true);
        if (is_array($decoded) && !empty($decoded)) {
            $imagePath = $decoded[0]; // İlk resmi al
        }
    }
    
    // Virgül ile ayrılmış birden fazla resim varsa ilkini al
    if (strpos($imagePath, ',') !== false) {
        $images = explode(',', $imagePath);
        $imagePath = trim($images[0]);
    }
    
    // Cloudflare URL kontrolü - Bu en önemli kısım!
    if (strpos($imagePath, 'https://imagedelivery.net/') === 0) {
        return $imagePath;
    }
    
    // Eğer zaten tam URL ise olduğu gibi döndür
    if (strpos($imagePath, 'http') === 0) {
        return $imagePath;
    }
    
    // Local path kontrol et
    if (file_exists('uploads/properties/' . $imagePath)) {
        return 'uploads/properties/' . $imagePath;
    }
    
    // Başka bir klasörde arayalım
    if (file_exists($imagePath)) {
        return $imagePath;
    }
    
    // Default fallback
    return 'images/default-property.jpg';
}

function formatPrice($price) {
    if ($price >= 1000000) {
        return number_format($price / 1000000, 1) . 'M ₺';
    } elseif ($price >= 1000) {
        return number_format($price / 1000, 0) . 'K ₺';
    } else {
        return number_format($price, 0) . ' ₺';
    }
}

function getPropertyTypeText($type) {
    $types = [
        'sale' => 'Satılık',
        'rent' => 'Kiralık',
        'daily_rent' => 'Günlük Kiralık',
        'transfer_sale' => 'Devren Satılık',
        'transfer_rent' => 'Devren Kiralık'
    ];
    
    return $types[$type] ?? 'Bilinmiyor';
}

function getCategoryText($category) {
    $categories = [
        'apartment' => 'Daire',
        'house' => 'Ev',
        'villa' => 'Villa',
        'office' => 'Ofis',
        'shop' => 'Dükkan',
        'warehouse' => 'Depo',
        'land' => 'Arsa'
    ];
    
    return $categories[$category] ?? 'Diğer';
}

function truncateText($text, $length = 100) {
    if (strlen($text) <= $length) {
        return $text;
    }
    
    return substr($text, 0, $length) . '...';
}

function generateSlug($text) {
    // Türkçe karakterleri dönüştür
    $tr = array('ş','Ş','ı','I','İ','ğ','Ğ','ü','Ü','ö','Ö','Ç','ç');
    $en = array('s','S','i','I','I','g','G','u','U','o','O','C','c');
    
    $text = str_replace($tr, $en, $text);
    $text = strtolower($text);
    $text = preg_replace('/[^a-z0-9]+/', '-', $text);
    $text = trim($text, '-');
    
    return $text;
}

// Eski fonksiyonlar için uyumluluk
function getFullImagePath($imagePath) {
    return getImagePath($imagePath);
}

function formatCurrency($amount) {
    return formatPrice($amount);
}
?>
