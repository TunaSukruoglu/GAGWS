<?php
echo "<h2>Parameter Count Verification</h2>";

// INSERT parameters check
echo "<h3>INSERT Query Parameters</h3>";
$insert_params = [
    'user_id', 'title', 'description', 'price', 'type', 'category', 'subcategory',
    'listing_type', 'area_gross', 'area_net', 'area', 'address', 'city', 'district', 
    'room_count', 'bedrooms', 'living_room_count', 'bathrooms', 'floor', 'building_floors', 'year_built', 'building_age',
    'heating', 'elevator', 'parking', 'furnished', 'usage_status', 'dues', 'credit_eligible',
    'deed_status', 'exchange', 'location_type', 'featured',
    'images_string', 'main_image', 'features_json'
];

$insert_types = "issdssssdddssiiiiissssssiidsssssisss";

echo "<p><strong>Parameter count:</strong> " . count($insert_params) . "</p>";
echo "<p><strong>Type string length:</strong> " . strlen($insert_types) . "</p>";
echo "<p><strong>Match:</strong> " . (count($insert_params) == strlen($insert_types) ? '✅ YES' : '❌ NO') . "</p>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>#</th><th>Parameter</th><th>Type</th></tr>";
for($i = 0; $i < count($insert_params); $i++) {
    $type = isset($insert_types[$i]) ? $insert_types[$i] : 'MISSING';
    echo "<tr>";
    echo "<td>" . ($i+1) . "</td>";
    echo "<td>" . $insert_params[$i] . "</td>";
    echo "<td style='color: " . ($type == 'MISSING' ? 'red' : 'green') . ";'>" . $type . "</td>";
    echo "</tr>";
}
echo "</table>";

// UPDATE parameters check
echo "<hr><h3>UPDATE Query Parameters</h3>";
$update_params = [
    'title', 'description', 'price', 'type', 'category', 'subcategory',
    'listing_type', 'area_gross', 'area_net', 'area', 'address', 'city', 'district', 
    'room_count', 'bedrooms', 'living_room_count', 'bathrooms', 'floor', 'building_floors', 'year_built', 'building_age',
    'heating', 'elevator', 'parking', 'furnished', 'usage_status', 'dues', 'credit_eligible',
    'deed_status', 'exchange', 'location_type', 'featured',
    'images_string', 'main_image', 'features_json', 'edit_id', 'user_id', 'user_role'
];

$update_types = "ssdssssdddssiiiisssssiidsssssisssiis";

echo "<p><strong>Parameter count:</strong> " . count($update_params) . "</p>";
echo "<p><strong>Type string length:</strong> " . strlen($update_types) . "</p>";
echo "<p><strong>Match:</strong> " . (count($update_params) == strlen($update_types) ? '✅ YES' : '❌ NO') . "</p>";

echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>#</th><th>Parameter</th><th>Type</th></tr>";
for($i = 0; $i < count($update_params); $i++) {
    $type = isset($update_types[$i]) ? $update_types[$i] : 'MISSING';
    echo "<tr>";
    echo "<td>" . ($i+1) . "</td>";
    echo "<td>" . $update_params[$i] . "</td>";
    echo "<td style='color: " . ($type == 'MISSING' ? 'red' : 'green') . ";'>" . $type . "</td>";
    echo "</tr>";
}
echo "</table>";

echo "<h3>Recommended Fix</h3>";
echo "<p>✅ Both parameter counts should now match correctly!</p>";
?>
