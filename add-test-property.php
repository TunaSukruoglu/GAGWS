<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Database bağlantısı
include 'db.php';

echo "🏠 TEST ILANI EKLEME\n";
echo "====================\n\n";

try {
    // Test ilanı verileri
    $test_property = [
        'user_id' => 1,
        'title' => 'Deneme Test İlanı - Kadıköy\'de Satılık 3+1 Daire',
        'description' => 'Bu bir test ilanıdır. Kadıköy merkezde, denize yakın, ulaşım imkanları mükemmel olan bu dairede ailenizle huzurlu bir yaşam sürebilirsiniz. Daire tamamen yenilenmiş durumda olup, kullanıma hazırdır.',
        'price' => 2500000.0,
        'type' => 'sale',
        'category' => 'apartment',
        'subcategory' => 'daire',
        'listing_type' => 'Satılık',
        'area_gross' => 120.0,
        'area_net' => 100.0,
        'area' => 120.0,
        'address' => 'Fenerbahçe Mahallesi, Bağdat Caddesi üzeri',
        'city' => 'İstanbul',
        'district' => 'Kadıköy',
        'room_count' => 4,
        'bedrooms' => 3,
        'living_room_count' => 1,
        'bathrooms' => 2,
        'floor' => '5',
        'building_floors' => '8',
        'year_built' => 2018,
        'building_age' => '7',
        'heating' => 'Kombi (Dogalgaz)',
        'elevator' => 'Var',
        'parking' => 'Kapali Otopark',
        'furnished' => 0,
        'usage_status' => 'Bos',
        'dues' => 1200.0,
        'credit_eligible' => 1,
        'deed_status' => 'Kat Mulkiyeti',
        'exchange' => 'Hayir',
        'location_type' => 'standalone',
        'featured' => 1, // Öne çıkan ilan yap
        'images' => '["test-property-1.jpg", "test-property-2.jpg"]',
        'main_image' => 'test-property-1.jpg',
        'features' => '{"ic_ozellikler": ["Ankastre Mutfak", "Klima", "Laminat Parke"], "dis_ozellikler": ["Balkon", "Güvenlik"], "muhit_ozellikleri": ["Merkezi Konum", "Ulaşım"], "ulasim_ozellikleri": ["Metro", "Otobüs"], "manzara_ozellikleri": ["Şehir"], "konut_tipi_ozellikleri": ["Aile Yaşamı"], "olanaklar": ["Güvenlik", "Asansör"]}',
        'status' => 'active'
    ];

    echo "Test ilanı bilgileri:\n";
    echo "📍 Konum: {$test_property['city']} / {$test_property['district']}\n";
    echo "🏡 Başlık: {$test_property['title']}\n";
    echo "💰 Fiyat: " . number_format($test_property['price']) . " TL\n";
    echo "📐 Alan: {$test_property['area']} m²\n";
    echo "🏠 Oda: {$test_property['room_count']} oda, {$test_property['bathrooms']} banyo\n";
    echo "🚗 Otopark: {$test_property['parking']}\n";
    echo "🏢 Kullanım: {$test_property['usage_status']}\n";
    echo "⭐ Öne Çıkan: " . ($test_property['featured'] ? 'Evet' : 'Hayır') . "\n\n";

    // INSERT sorgusu (add-property.php'den aynı)
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
        images = ?,
        main_image = ?,
        features = ?,
        status = 'active',
        created_at = NOW()";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Düzeltilmiş bind_param - 36 parametre
    $bind_result = $stmt->bind_param("issdssssdddsssiiiiiiissssisssssissss", 
        $test_property['user_id'], $test_property['title'], $test_property['description'], $test_property['price'], 
        $test_property['type'], $test_property['category'], $test_property['subcategory'],
        $test_property['listing_type'], $test_property['area_gross'], $test_property['area_net'], $test_property['area'], 
        $test_property['address'], $test_property['city'], $test_property['district'], 
        $test_property['room_count'], $test_property['bedrooms'], $test_property['living_room_count'], $test_property['bathrooms'], 
        $test_property['floor'], $test_property['building_floors'], $test_property['year_built'], $test_property['building_age'],
        $test_property['heating'], $test_property['elevator'], $test_property['parking'], $test_property['furnished'], 
        $test_property['usage_status'], $test_property['dues'], $test_property['credit_eligible'],
        $test_property['deed_status'], $test_property['exchange'], $test_property['location_type'], $test_property['featured'],
        $test_property['images'], $test_property['main_image'], $test_property['features']
    );
    
    if (!$bind_result) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }

    echo "İlan ekleniyor...\n";

    if ($stmt->execute()) {
        $property_id = $conn->insert_id;
        echo "🎉 BAŞARILI: Test ilanı başarıyla eklendi!\n";
        echo "📝 İlan ID: $property_id\n\n";
        
        // Eklenen ilanı doğrula
        $check_sql = "SELECT id, title, city, district, price, area, usage_status, parking, featured, status FROM properties WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $saved_property = $result->fetch_assoc();
        
        echo "✅ Database'de kaydedilen ilan:\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n";
        echo "🆔 ID: " . $saved_property['id'] . "\n";
        echo "🏠 Başlık: " . $saved_property['title'] . "\n";
        echo "📍 Konum: " . $saved_property['city'] . " / " . $saved_property['district'] . "\n";
        echo "💰 Fiyat: " . number_format($saved_property['price']) . " TL\n";
        echo "📐 Alan: " . $saved_property['area'] . " m²\n";
        echo "🏢 Kullanım: " . $saved_property['usage_status'] . "\n";
        echo "🚗 Otopark: " . $saved_property['parking'] . "\n";
        echo "⭐ Öne Çıkan: " . ($saved_property['featured'] ? 'Evet' : 'Hayır') . "\n";
        echo "📊 Durum: " . $saved_property['status'] . "\n";
        echo "━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━\n\n";
        
        // ENUM değerlerini özel olarak kontrol et
        echo "🔍 ENUM Değerleri Kontrolü:\n";
        if ($saved_property['usage_status'] === $test_property['usage_status']) {
            echo "✅ usage_status: '{$saved_property['usage_status']}' - DOĞRU\n";
        } else {
            echo "❌ usage_status: Beklenen '{$test_property['usage_status']}', Kaydedilen '{$saved_property['usage_status']}'\n";
        }
        
        if ($saved_property['parking'] === $test_property['parking']) {
            echo "✅ parking: '{$saved_property['parking']}' - DOĞRU\n";
        } else {
            echo "❌ parking: Beklenen '{$test_property['parking']}', Kaydedilen '{$saved_property['parking']}'\n";
        }
        
        echo "\n🚀 Test ilanı başarıyla oluşturuldu!\n";
        echo "🌐 Artık ana sayfada görünmelidir.\n";
        
    } else {
        echo "❌ HATA: " . $stmt->error . "\n";
    }

} catch (Exception $e) {
    echo "💥 EXCEPTION: " . $e->getMessage() . "\n";
}
?>
