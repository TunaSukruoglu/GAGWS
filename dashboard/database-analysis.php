<?php
include '../db.php';

echo "<h2>Properties Tablosu - İlan Ekleme/Düzenleme Sütunları</h2>";
echo "<p><strong>Tarihi:</strong> " . date('Y-m-d H:i:s') . "</p>";

echo "<h3>1. Veritabanındaki Tüm Sütunlar</h3>";
echo "<table border='1' style='border-collapse: collapse; width: 100%; font-size: 14px;'>";
echo "<tr style='background-color: #f0f0f0;'><th>Sıra</th><th>Sütun Adı</th><th>Veri Tipi</th><th>Null</th><th>Varsayılan</th><th>Açıklama</th></tr>";

$result = $conn->query("DESCRIBE properties");
$i = 1;
$all_columns = [];
if ($result) {
    while ($row = $result->fetch_assoc()) {
        $all_columns[] = $row['Field'];
        $description = '';
        
        // Sütun açıklamaları
        switch($row['Field']) {
            case 'id': $description = 'Birincil anahtar, otomatik artan'; break;
            case 'user_id': $description = 'İlanı ekleyen kullanıcı ID'; break;
            case 'title': $description = 'İlan başlığı'; break;
            case 'description': $description = 'İlan açıklaması'; break;
            case 'category': $description = 'Emlak kategorisi (apartment, house, vb.)'; break;
            case 'listing_type': $description = 'Satılık/Kiralık'; break;
            case 'type': $description = 'İşlem türü'; break;
            case 'price': $description = 'Fiyat (TL)'; break;
            case 'area': $description = 'Alan (m²) - genel'; break;
            case 'area_gross': $description = 'Brüt alan (m²)'; break;
            case 'area_net': $description = 'Net alan (m²)'; break;
            case 'room_count': $description = 'Oda sayısı (3+1, vb.)'; break;
            case 'bedrooms': $description = 'Yatak odası sayısı'; break;
            case 'bathrooms': $description = 'Banyo sayısı'; break;
            case 'floor': $description = 'Bulunduğu kat'; break;
            case 'year_built': $description = 'Yapım yılı'; break;
            case 'building_age': $description = 'Bina yaşı'; break;
            case 'heating': $description = 'Isıtma türü'; break;
            case 'elevator': $description = 'Asansör var/yok'; break;
            case 'parking': $description = 'Otopark bilgisi'; break;
            case 'furnished': $description = 'Eşyalı mı?'; break;
            case 'usage_status': $description = 'Kullanım durumu'; break;
            case 'dues': $description = 'Aidat miktarı'; break;
            case 'credit_eligible': $description = 'Krediye uygun mu?'; break;
            case 'deed_status': $description = 'Tapu durumu'; break;
            case 'exchange': $description = 'Takasa uygun mu?'; break;
            case 'address': $description = 'Adres'; break;
            case 'city': $description = 'İl'; break;
            case 'district': $description = 'İlçe'; break;
            case 'neighborhood': $description = 'Mahalle'; break;
            case 'location_type': $description = 'Konum türü (site/müstakil)'; break;
            case 'images': $description = 'Fotoğraflar (JSON)'; break;
            case 'main_image': $description = 'Ana fotoğraf'; break;
            case 'features': $description = 'Özellikler (JSON)'; break;
            case 'featured': $description = 'Öne çıkarılmış mı?'; break;
            case 'status': $description = 'İlan durumu'; break;
            case 'views': $description = 'Görüntülenme sayısı'; break;
            case 'created_at': $description = 'Oluşturma tarihi'; break;
            case 'updated_at': $description = 'Güncelleme tarihi'; break;
        }
        
        echo "<tr>";
        echo "<td style='padding: 6px; border: 1px solid #ccc; text-align: center;'>" . $i . "</td>";
        echo "<td style='padding: 6px; border: 1px solid #ccc; font-weight: bold;'>" . $row['Field'] . "</td>";
        echo "<td style='padding: 6px; border: 1px solid #ccc;'>" . $row['Type'] . "</td>";
        echo "<td style='padding: 6px; border: 1px solid #ccc;'>" . $row['Null'] . "</td>";
        echo "<td style='padding: 6px; border: 1px solid #ccc;'>" . ($row['Default'] ?? 'NULL') . "</td>";
        echo "<td style='padding: 6px; border: 1px solid #ccc; font-style: italic;'>" . $description . "</td>";
        echo "</tr>";
        $i++;
    }
} else {
    echo "<tr><td colspan='6'>Hata: " . $conn->error . "</td></tr>";
}
echo "</table>";

echo "<br><h3>2. INSERT Sorgusunda Kullanılan Sütunlar</h3>";
$insert_columns = [
    'user_id', 'title', 'description', 'price', 'type', 'category', 'listing_type',
    'area_gross', 'area_net', 'area', 'address', 'city', 'district',
    'room_count', 'bedrooms', 'bathrooms', 'floor', 'year_built', 'building_age',
    'heating', 'elevator', 'parking', 'furnished', 'usage_status', 'dues',
    'credit_eligible', 'deed_status', 'exchange', 'location_type', 'featured',
    'images', 'main_image', 'features', 'status', 'created_at'
];

echo "<p><strong>Toplam: " . count($insert_columns) . " sütun</strong></p>";
echo "<ol>";
foreach ($insert_columns as $col) {
    $exists = in_array($col, $all_columns) ? '✓' : '❌';
    echo "<li><strong>$col</strong> $exists</li>";
}
echo "</ol>";

echo "<br><h3>3. UPDATE Sorgusunda Kullanılan Sütunlar</h3>";
$update_columns = [
    'title', 'description', 'price', 'type', 'category', 'listing_type',
    'area_gross', 'area_net', 'area', 'address', 'city', 'district',
    'room_count', 'bedrooms', 'bathrooms', 'floor', 'year_built', 'building_age',
    'heating', 'elevator', 'parking', 'furnished', 'usage_status', 'dues',
    'credit_eligible', 'deed_status', 'exchange', 'location_type', 'featured',
    'images', 'main_image', 'features', 'updated_at'
];

echo "<p><strong>Toplam: " . count($update_columns) . " sütun</strong></p>";
echo "<ol>";
foreach ($update_columns as $col) {
    $exists = in_array($col, $all_columns) ? '✓' : '❌';
    echo "<li><strong>$col</strong> $exists</li>";
}
echo "</ol>";

echo "<br><h3>4. Kullanılmayan/Eksik Sütunlar</h3>";
$unused_columns = array_diff($all_columns, array_unique(array_merge($insert_columns, $update_columns, ['id'])));
if (!empty($unused_columns)) {
    echo "<p><strong>Veritabanında var ama formda kullanılmayan sütunlar:</strong></p>";
    echo "<ul>";
    foreach ($unused_columns as $col) {
        echo "<li><strong>$col</strong></li>";
    }
    echo "</ul>";
} else {
    echo "<p style='color: green;'><strong>Tüm sütunlar kullanılıyor!</strong></p>";
}

// Form işleme bind_param string analizi
echo "<br><h3>5. Bind Param String Analizi</h3>";
$insert_bind = "issdsssdddsssiiiissssiidsssssisss";
$update_bind = "ssdsssdddsssiiiissssiidsssssisssiis";

echo "<p><strong>INSERT bind_param:</strong> <code>$insert_bind</code> (Uzunluk: " . strlen($insert_bind) . ")</p>";
echo "<p><strong>UPDATE bind_param:</strong> <code>$update_bind</code> (Uzunluk: " . strlen($update_bind) . ")</p>";
echo "<p><strong>INSERT sütun sayısı:</strong> " . count($insert_columns) . "</p>";
echo "<p><strong>UPDATE sütun sayısı + WHERE koşulları:</strong> " . count($update_columns) . " + 3 = " . (count($update_columns) + 3) . "</p>";

// Eşleşme kontrolü
$insert_match = (strlen($insert_bind) == count($insert_columns)) ? '✓ Eşleşiyor' : '❌ Eşleşmiyor';
$update_match = (strlen($update_bind) == (count($update_columns) + 3)) ? '✓ Eşleşiyor' : '❌ Eşleşmiyor';

echo "<p><strong>INSERT eşleşme:</strong> $insert_match</p>";
echo "<p><strong>UPDATE eşleşme:</strong> $update_match</p>";

echo "<br><h3>6. Özet Bilgiler</h3>";
echo "<ul>";
echo "<li><strong>Toplam sütun sayısı:</strong> " . count($all_columns) . "</li>";
echo "<li><strong>Kullanılan sütun sayısı:</strong> " . count(array_unique(array_merge($insert_columns, $update_columns))) . "</li>";
echo "<li><strong>Kullanılmayan sütun sayısı:</strong> " . count($unused_columns) . "</li>";
echo "</ul>";
?>
