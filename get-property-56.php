<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

$property_id = 56; // İlan ID

try {
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    
    if (!$property) {
        die("Bu ID ile ilan bulunamadı: $property_id");
    }
    
    // Kullanıcı bilgilerini de alalım
    if (!empty($property['user_id'])) {
        $user_stmt = $conn->prepare("SELECT id, name, email, phone FROM users WHERE id = ?");
        $user_stmt->bind_param("i", $property['user_id']);
        $user_stmt->execute();
        $user_result = $user_stmt->get_result();
        $user = $user_result->fetch_assoc();
    }
    
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

header('Content-Type: text/plain; charset=utf-8');

echo "=== İLAN ID: 56 DATABASE KAYITLARI ===\n\n";

echo "=== ANA BİLGİLER ===\n";
echo "ID: " . ($property['id'] ?? 'NULL') . "\n";
echo "Başlık: " . ($property['title'] ?? 'NULL') . "\n";
echo "Açıklama: " . (isset($property['description']) ? substr($property['description'], 0, 200) . "..." : 'NULL') . "\n";
echo "Fiyat: " . ($property['price'] ?? 'NULL') . "\n";
echo "Para Birimi: " . ($property['currency'] ?? 'NULL') . "\n";
echo "Tür: " . ($property['type'] ?? 'NULL') . "\n";
echo "Kategori: " . ($property['category'] ?? 'NULL') . "\n";
echo "Alt Kategori: " . ($property['subcategory'] ?? 'NULL') . "\n";

echo "\n=== ALAN BİLGİLERİ ===\n";
echo "Alan (Eski): " . ($property['area'] ?? 'NULL') . "\n";
echo "Brüt Alan: " . ($property['area_gross'] ?? 'NULL') . "\n";
echo "Net Alan: " . ($property['area_net'] ?? 'NULL') . "\n";

echo "\n=== ODA BİLGİLERİ ===\n";
echo "Oda Sayısı: " . ($property['room_count'] ?? 'NULL') . "\n";
echo "Salon Sayısı: " . ($property['salon_count'] ?? 'NULL') . "\n";
echo "Yatak Odası: " . ($property['bedrooms'] ?? 'NULL') . "\n";
echo "Banyo: " . ($property['bathrooms'] ?? 'NULL') . "\n";

echo "\n=== BİNA BİLGİLERİ ===\n";
echo "Bulunduğu Kat: " . ($property['floor'] ?? 'NULL') . "\n";
echo "Kat (Yeni Alan): " . ($property['floor_located'] ?? 'NULL') . "\n";
echo "Bina Kat Sayısı: " . ($property['building_floors'] ?? 'NULL') . "\n";
echo "Bina Yaşı: " . ($property['building_age'] ?? 'NULL') . "\n";

echo "\n=== DONANIM BİLGİLERİ ===\n";
echo "Otopark: " . ($property['parking'] ?? 'NULL') . "\n";
echo "Eşyalı: " . ($property['furnished'] ?? 'NULL') . "\n";
echo "Asansör: " . ($property['elevator'] ?? 'NULL') . "\n";
echo "Isıtma: " . ($property['heating'] ?? 'NULL') . "\n";

echo "\n=== DURUM BİLGİLERİ ===\n";
echo "Kullanım Durumu: " . ($property['usage_status'] ?? 'NULL') . "\n";
echo "Krediye Uygunluk: " . ($property['credit_eligible'] ?? 'NULL') . "\n";
echo "Tapu Durumu: " . ($property['deed_status'] ?? 'NULL') . "\n";
echo "Takas: " . ($property['exchange'] ?? 'NULL') . "\n";

echo "\n=== MALİ BİLGİLER ===\n";
echo "Aidat: " . ($property['dues'] ?? 'NULL') . "\n";
echo "Depozito: " . ($property['deposit'] ?? 'NULL') . "\n";

echo "\n=== KONUM BİLGİLERİ ===\n";
echo "Konum (Eski): " . ($property['location'] ?? 'NULL') . "\n";
echo "İl: " . ($property['il'] ?? 'NULL') . "\n";
echo "İlçe: " . ($property['ilce'] ?? 'NULL') . "\n";
echo "Mahalle: " . ($property['mahalle'] ?? 'NULL') . "\n";
echo "Adres: " . ($property['address'] ?? 'NULL') . "\n";

echo "\n=== MEDYA VE ÖZELLİKLER ===\n";
echo "Resimler: " . (isset($property['images']) ? substr($property['images'], 0, 200) . "..." : 'NULL') . "\n";
echo "Özellikler: " . (isset($property['features']) ? substr($property['features'], 0, 200) . "..." : 'NULL') . "\n";

echo "\n=== TARİH BİLGİLERİ ===\n";
echo "Oluşturulma: " . ($property['created_at'] ?? 'NULL') . "\n";
echo "Güncellenme: " . ($property['updated_at'] ?? 'NULL') . "\n";

echo "\n=== KULLANICI BİLGİLERİ ===\n";
echo "Kullanıcı ID: " . ($property['user_id'] ?? 'NULL') . "\n";
if (isset($user)) {
    echo "Kullanıcı Adı: " . ($user['name'] ?? 'NULL') . "\n";
    echo "Email: " . ($user['email'] ?? 'NULL') . "\n";
    echo "Telefon: " . ($user['phone'] ?? 'NULL') . "\n";
}

echo "\n=== DETAYLI VERİ DUMP ===\n";
echo "--- TÜM ALANLAR ---\n";
foreach ($property as $key => $value) {
    if (is_null($value)) {
        $display = 'NULL';
    } elseif ($value === '') {
        $display = 'BOŞ STRING';
    } elseif ($value === '0') {
        $display = 'SIFIR STRING';
    } elseif ($value === 0) {
        $display = 'SIFIR SAYI';
    } else {
        $display = $value;
    }
    
    printf("%-20s: %s\n", $key, $display);
}

echo "\n=== ÖZELLİKLER JSON ===\n";
if (!empty($property['features'])) {
    $features = json_decode($property['features'], true);
    if ($features) {
        print_r($features);
    } else {
        echo "JSON Parse Hatası: " . $property['features'] . "\n";
    }
} else {
    echo "Özellik verisi yok\n";
}

echo "\n=== RESİMLER ===\n";
if (!empty($property['images'])) {
    $images = json_decode($property['images'], true);
    if ($images) {
        foreach ($images as $index => $image) {
            echo "Resim " . ($index + 1) . ": " . $image . "\n";
        }
    } else {
        echo "Resim JSON Parse Hatası: " . $property['images'] . "\n";
    }
} else {
    echo "Resim verisi yok\n";
}
?>
