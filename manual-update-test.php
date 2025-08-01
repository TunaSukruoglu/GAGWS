<?php
include 'db.php';

$images_json = '["property_68836f2c887b5_1753444140.png","property_68836f2c892fc_1753444140.png","property_68836f2c89c71_1753444140.png","property_68836f2c8b828_1753444140.png","property_68836f2c8d7d5_1753444140.png","property_68836f2c8e905_1753444140.png","property_68836f2c8f6a2_1753444140.png","property_68836f2c9068f_1753444140.png","property_68836f2c9176a_1753444140.png","property_68836f2c9288d_1753444140.png","property_68836f2c93800_1753444140.png","property_68836f2c9462a_1753444140.png","property_68836f2c95631_1753444140.png","property_68836f2c96714_1753444140.png","property_68836f2c972da_1753444140.png","property_68836f2c98036_1753444140.png","property_68836f2c98e98_1753444140.png"]';
$main_image = 'property_68836f2c887b5_1753444140.png';
$parking = 'Kapali Otopark';

echo "MANUEL UPDATE testi başlıyor..." . PHP_EOL;

$stmt = $conn->prepare("UPDATE properties SET images = ?, main_image = ?, parking = ? WHERE id = 1");
$stmt->bind_param("sss", $images_json, $main_image, $parking);

if ($stmt->execute()) {
    echo "Manuel UPDATE başarılı - Etkilenen satır: " . $stmt->affected_rows . PHP_EOL;
    
    // Hemen kontrol edelim
    $result = $conn->query('SELECT parking, LENGTH(images) as img_len, main_image FROM properties WHERE id = 1');
    $row = $result->fetch_assoc();
    echo "Sonuç - Parking: '{$row['parking']}', Image Length: {$row['img_len']}, Main: '{$row['main_image']}'" . PHP_EOL;
} else {
    echo "Hata: " . $stmt->error . PHP_EOL;
}
?>
