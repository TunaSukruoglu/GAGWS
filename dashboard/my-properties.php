<?php
// dashboard/my-properties.php
session_start();
include '../db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcının ilanlarını getir
$stmt = $conn->prepare("SELECT * FROM properties WHERE user_id = ? ORDER BY created_at DESC");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$properties = $stmt->get_result()->fetch_all(MYSQLI_ASSOC);
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlanlarım</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <!-- Dashboard Z-Index Fix CSS -->
    <link rel="stylesheet" type="text/css" href="../css/dashboard-fix.css">
</head>
<body class="user-dashboard">
    <div class="container mt-4">
        <h2>İlanlarım</h2>
        
        <div class="row">
            <?php foreach ($properties as $property): ?>
            <div class="col-md-4 mb-3">
                <div class="card">
                    <div class="card-body">
                        <h5 class="card-title"><?php echo htmlspecialchars($property['title']); ?></h5>
                        <p class="card-text"><?php echo number_format($property['price'], 0, ',', '.'); ?> ₺</p>
                        <div class="d-flex gap-2">
                            <a href="add-property.php?edit=<?php echo $property['id']; ?>" class="btn btn-success btn-sm">
                                <i class="fas fa-edit"></i> Düzenle
                            </a>
                            <a href="../edit-property-images.php?id=<?php echo $property['id']; ?>" class="btn btn-primary btn-sm">
                                <i class="fas fa-image"></i> Resim Düzenle
                            </a>
                        </div>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        
        <a href="../index.php" class="btn btn-secondary mt-3">Ana Sayfaya Dön</a>
    </div>
</body>
</html>