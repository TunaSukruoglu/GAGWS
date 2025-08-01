<?php
session_start();
require_once '../db.php';

// Login as admin for testing
$_SESSION['user_id'] = 1;
$_SESSION['role'] = 'admin';

// Simulate form data for property 20
$_POST = [
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

echo "Starting property 20 edit test...\n";
echo "POST data prepared.\n";

// Include the add-property.php to trigger the edit
try {
    include 'add-property.php';
    echo "Edit completed successfully!\n";
} catch (Exception $e) {
    echo "ERROR: " . $e->getMessage() . "\n";
    echo "File: " . $e->getFile() . "\n";
    echo "Line: " . $e->getLine() . "\n";
}
?>
