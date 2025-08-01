<?php
// SQL parametre sayıcı
$sql = "INSERT INTO properties SET 
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

// SQL'deki ? işaretlerini say
$question_marks = substr_count($sql, '?');
echo "SQL sorgusu içindeki ? sayısı: $question_marks\n";

// Type string kontrolü
$type_string = "issdssssdddsssiiiiiiissssisssssisssss";
echo "Type string: $type_string\n";
echo "Type string uzunluğu: " . strlen($type_string) . "\n";

if ($question_marks === strlen($type_string)) {
    echo "✓ Eşleşiyor!\n";
} else {
    echo "✗ Eşleşmiyor! Fark: " . ($question_marks - strlen($type_string)) . "\n";
}

// Parametreleri listele
$params = [
    'user_id', 'title', 'description', 'price', 'type', 'category', 'subcategory',
    'listing_type', 'area_gross', 'area_net', 'area', 'address', 'city', 'district', 
    'room_count', 'bedrooms', 'living_room_count', 'bathrooms', 'floor', 'building_floors', 
    'year_built', 'building_age', 'heating', 'elevator', 'parking', 'furnished', 
    'usage_status', 'dues', 'credit_eligible', 'deed_status', 'exchange', 'location_type', 
    'featured', 'images', 'main_image', 'features'
];

echo "\nParametre listesi (" . count($params) . " adet):\n";
foreach ($params as $i => $param) {
    $pos = $i + 1;
    $type_char = isset($type_string[$i]) ? $type_string[$i] : 'X';
    echo "$pos. $param ($type_char)\n";
}

if (count($params) === strlen($type_string)) {
    echo "\n✓ Parametre sayısı type string ile eşleşiyor!\n";
} else {
    echo "\n✗ Parametre sayısı type string ile eşleşmiyor!\n";
    echo "Parametre: " . count($params) . ", Type: " . strlen($type_string) . "\n";
}
?>
