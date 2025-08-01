<?php
session_start();
include 'db.php';

if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];
$property_id = $_GET['id'] ?? 0;

// Kullanıcının bu ilanı düzenleme yetkisi var mı kontrol et
$stmt = $conn->prepare("SELECT * FROM properties WHERE id = ? AND user_id = ?");
$stmt->bind_param("ii", $property_id, $user_id);
$stmt->execute();
$property = $stmt->get_result()->fetch_assoc();

if (!$property) {
    $_SESSION['error'] = "Bu ilanı düzenleme yetkiniz yok.";
    header("Location: my-properties.php");
    exit;
}

// Mevcut resimleri al
$existing_images = [];
if (!empty($property['images'])) {
    $existing_images = json_decode($property['images'], true);
    if (!is_array($existing_images)) {
        $existing_images = [];
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <title>İlan Resimlerini Düzenle</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-4">
        <h2>İlan Resimlerini Düzenle</h2>
        <p><strong>İlan:</strong> <?= htmlspecialchars($property['title']) ?></p>
        
        <?php if (!empty($existing_images)): ?>
        <h4>Mevcut Resimler (<?= count($existing_images) ?> adet)</h4>
        <div class="row">
            <?php foreach ($existing_images as $index => $image): ?>
            <div class="col-md-3 mb-3">
                <div class="card">
                    <img src="dashboard/uploads/properties/<?= htmlspecialchars($image) ?>" 
                         class="card-img-top" 
                         style="height: 200px; object-fit: cover;"
                         alt="İlan resmi">
                    <div class="card-body p-2">
                        <?php if ($index === 0): ?>
                        <span class="badge bg-primary">Ana Resim</span>
                        <?php endif; ?>
                        <p class="small mb-0"><?= htmlspecialchars($image) ?></p>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php else: ?>
        <div class="alert alert-warning">
            <i class="fas fa-exclamation-triangle"></i>
            Bu ilanda hiç resim yok!
        </div>
        <?php endif; ?>
        
        <div class="mt-4">
            <a href="dashboard/my-properties.php" class="btn btn-secondary">
                <i class="fas fa-arrow-left"></i> Geri Dön
            </a>
        </div>
    </div>
</body>
</html>
