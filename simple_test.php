<?php
// Simple MySQL test without includes
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";

$conn = new mysqli($servername, $username, $password, $dbname);

if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

$conn->set_charset('utf8');

$query = "SELECT id, title, parking, images FROM properties WHERE id = 1";
$result = $conn->query($query);

if ($result && $result->num_rows > 0) {
    $row = $result->fetch_assoc();
    echo "Property ID: " . $row['id'] . "\n";
    echo "Title: " . $row['title'] . "\n";
    echo "Parking: '" . $row['parking'] . "'\n";
    echo "Images JSON: " . $row['images'] . "\n";
    
    $images = json_decode($row['images'], true);
    echo "Images count: " . (is_array($images) ? count($images) : 0) . "\n";
    
    if (is_array($images)) {
        foreach ($images as $i => $img) {
            echo "Image " . ($i+1) . ": " . $img . "\n";
        }
    }
} else {
    echo "No property found with ID 1\n";
}

$conn->close();
?>
