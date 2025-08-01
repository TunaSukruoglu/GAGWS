<?php
// Debugging için tüm değişkenleri kontrol et
echo "=== BIND_PARAM DEBUG ===\n";

$tip_string = "issdsssdddsssiiiisssiiissssss";
echo "Tip string: $tip_string\n";
echo "Tip string uzunluğu: " . strlen($tip_string) . "\n\n";

$variables = [
    '$user_id',
    '$title',
    '$description', 
    '$price',
    '$type',
    '$category',
    '$listing_type',
    '$area_gross',
    '$area_net',
    '$area',
    '$address',
    '$final_city',
    '$final_district',
    '$room_count',
    '$bedrooms',
    '$bathrooms',
    '$floor',
    '$year_built',
    '$heating',
    '$elevator',
    '$parking',
    '$furnished',
    '$featured',
    '$images_string',
    '$main_image',
    '$interior_features_json',
    '$exterior_features_json',
    '$neighborhood_features_json',
    '$transportation_features_json',
    '$view_features_json',
    '$housing_type_features_json'
];

echo "Değişken sayısı: " . count($variables) . "\n";

for($i = 0; $i < count($variables); $i++) {
    $tip = substr($tip_string, $i, 1);
    echo ($i+1) . ". {$variables[$i]} → $tip\n";
}
?>
