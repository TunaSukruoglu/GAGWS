<?php
// Direct POST simulation to property 20
$url = 'https://gokhanaydinli.com/dashboard/add-property.php';

$post_data = [
    'edit_id' => 20,
    'title' => 'TEST EDIT - MERTER OFİS',
    'description' => 'Test edit açıklaması',
    'type' => 'rent',
    'category_form' => 'Ofis',
    'subcategory' => 'buro_ofis',
    'price' => '12000',
    'area_gross' => '120',
    'area_net' => '100',
    'room_count' => '4',
    'bedrooms' => '0',
    'living_room_count' => '1',
    'bathroom_count' => '2',
    'floor_location' => '5',
    'building_floors' => '10',
    'building_age' => '5',
    'city' => 'İstanbul',
    'district' => 'Zeytinburnu',
    'neighborhood' => 'Merter',
    'location_type' => 'standalone',
    'heating' => 'Merkezi Sistem',
    'elevator' => 'Var',
    'parking' => 'Kapali Otopark',
    'furnished' => '1',
    'usage_status' => 'Bos',
    'dues' => '1000',
    'credit_eligible' => '1',
    'deed_status' => 'Kat Mulkiyeti',
    'exchange' => 'Hayir'
];

// Prepare cURL
$ch = curl_init();
curl_setopt($ch, CURLOPT_URL, $url);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, http_build_query($post_data));
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
curl_setopt($ch, CURLOPT_COOKIEFILE, '/tmp/cookies.txt');
curl_setopt($ch, CURLOPT_COOKIEJAR, '/tmp/cookies.txt');

echo "Sending POST request to edit property 20...\n";
$response = curl_exec($ch);
$http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);

echo "HTTP Code: " . $http_code . "\n";
echo "Response length: " . strlen($response) . "\n";

if (strpos($response, 'UNCAUGHT EXCEPTION') !== false) {
    echo "ERROR DETECTED in response!\n";
    // Extract error message
    preg_match('/UNCAUGHT EXCEPTION!(.*?)(?=<\/div>|$)/s', $response, $matches);
    if (isset($matches[1])) {
        echo "Error details: " . trim($matches[1]) . "\n";
    }
} else {
    echo "No error detected in response.\n";
}

curl_close($ch);
?>
