<?php
include 'db.php';

// Test için dues ve elevator değerleri ekleyelim
$dues = 2500.00;
$elevator = '1'; // Var = 1

$stmt = $conn->prepare("UPDATE properties SET dues = ?, elevator = ? WHERE id = 1");
$stmt->bind_param("ds", $dues, $elevator);

if ($stmt->execute()) {
    echo "Test verileri eklendi - Dues: $dues, Elevator: $elevator" . PHP_EOL;
    
    // Kontrol edelim
    $result = $conn->query('SELECT dues, elevator FROM properties WHERE id = 1');
    $row = $result->fetch_assoc();
    echo "Veritabanından okunan - Dues: " . $row['dues'] . ", Elevator: " . $row['elevator'] . PHP_EOL;
} else {
    echo "Hata: " . $stmt->error . PHP_EOL;
}
?>
