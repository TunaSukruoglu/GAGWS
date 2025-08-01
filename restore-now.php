<?php
include 'db.php';

$images = [
    "property_68836f2c887b5_1753444140.png",
    "property_68836f2c892fc_1753444140.png",
    "property_68836f2c89c71_1753444140.png",
    "property_68836f2c8b828_1753444140.png",
    "property_68836f2c8d7d5_1753444140.png",
    "property_68836f2c8e905_1753444140.png",
    "property_68836f2c8f6a2_1753444140.png",
    "property_68836f2c9068f_1753444140.png",
    "property_68836f2c9176a_1753444140.png",
    "property_68836f2c9288d_1753444140.png",
    "property_68836f2c93800_1753444140.png",
    "property_68836f2c9462a_1753444140.png",
    "property_68836f2c95631_1753444140.png",
    "property_68836f2c96714_1753444140.png",
    "property_68836f2c972da_1753444140.png",
    "property_68836f2c98036_1753444140.png",
    "property_68836f2c98e98_1753444140.png"
];

$images_json = json_encode($images);
$main_image = $images[0];
$parking = 'Acik Otopark';

echo "Restoring..." . PHP_EOL;
$stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ?, parking = ? WHERE id = 1");
$stmt->bind_param("sss", $images_json, $main_image, $parking);

if ($stmt->execute()) {
    echo "RESTORED - Property ID 1!" . PHP_EOL;
} else {
    echo "Error: " . $stmt->error . PHP_EOL;
}
?>
