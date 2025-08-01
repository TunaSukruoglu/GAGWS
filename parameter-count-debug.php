<?php
// Parametre sayısı sayıcı
$sql = "INSERT INTO properties (
    user_id, title, description, price, type, category, subcategory, listing_type,
    area_gross, area_net, area, address, city, district, room_count, bedrooms,
    living_room_count, bathrooms, floor, building_floors, year_built, building_age,
    heating, elevator, parking, furnished, usage_status, dues, credit_eligible,
    deed_status, exchange, location_type, featured, images, main_image, features, status
) VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?, ?)";

// Parametre isimlerini array olarak çıkar
preg_match_all('/(\w+),?/', 
    'user_id, title, description, price, type, category, subcategory, listing_type,
    area_gross, area_net, area, address, city, district, room_count, bedrooms,
    living_room_count, bathrooms, floor, building_floors, year_built, building_age,
    heating, elevator, parking, furnished, usage_status, dues, credit_eligible,
    deed_status, exchange, location_type, featured, images, main_image, features, status', 
    $matches);

$parameters = $matches[1];

echo "Parametre Analizi\n";
echo "=================\n\n";
echo "Toplam parametre sayısı: " . count($parameters) . "\n\n";

foreach ($parameters as $i => $param) {
    $num = $i + 1;
    echo "$num. $param\n";
}

// Type string kontrolü
$type_string = "issdssssdddsssiiiiiiissssidssssisssss";
echo "\nType string: $type_string\n";
echo "Type string length: " . strlen($type_string) . "\n";
echo "Parameter count: " . count($parameters) . "\n";

if (strlen($type_string) === count($parameters)) {
    echo "✓ Type string uzunluğu parametre sayısıyla eşleşiyor\n";
} else {
    echo "✗ Type string uzunluğu parametre sayısıyla eşleşmiyor!\n";
    echo "Fark: " . (count($parameters) - strlen($type_string)) . "\n";
}

// usage_status kaçıncı parametre
$usage_status_position = array_search('usage_status', $parameters);
if ($usage_status_position !== false) {
    $position = $usage_status_position + 1;
    echo "\nusage_status position: $position\n";
    echo "usage_status type: " . substr($type_string, $usage_status_position, 1) . "\n";
}
?>
