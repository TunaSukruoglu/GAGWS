<?php
// listing-details.php dosyası oluştur
<?php
session_start();
include 'db.php';

$property_id = isset($_GET['id']) ? intval($_GET['id']) : 0;

if ($property_id > 0) {
    $query = $conn->prepare("
        SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email
        FROM properties p 
        LEFT JOIN users u ON p.user_id = u.id 
        WHERE p.id = ? AND p.status IN ('active', 'approved')
    ");
    $query->bind_param("i", $property_id);
    $query->execute();
    $property = $query->get_result()->fetch_assoc();
    
    if (!$property) {
        header("Location: listing.php");
        exit;
    }
} else {
    header("Location: listing.php");
    exit;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <title><?= htmlspecialchars($property['title']) ?> - Gökhan Aydınlı Real Estate</title>
    <!-- Head içeriği -->
</head>
<body>
    <!-- Detay sayfası içeriği buraya gelecek -->
    <h1><?= htmlspecialchars($property['title']) ?></h1>
    <p><?= htmlspecialchars($property['description']) ?></p>
    <!-- Diğer detaylar -->
</body>
</html>