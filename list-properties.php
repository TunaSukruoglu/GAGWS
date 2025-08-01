<?php
// Mevcut property'leri listele
session_start();

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

include 'db.php';

$query = "SELECT id, title, images, status FROM properties WHERE status IN ('active', 'approved') ORDER BY id DESC LIMIT 10";
$result = $conn->query($query);

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Mevcut Properties</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <h1>Mevcut Properties</h1>
        
        <div class="table-responsive">
            <table class="table table-striped">
                <thead>
                    <tr>
                        <th>ID</th>
                        <th>Başlık</th>
                        <th>Durum</th>
                        <th>Resimler</th>
                        <th>İşlemler</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if ($result && $result->num_rows > 0): ?>
                        <?php while ($property = $result->fetch_assoc()): ?>
                            <tr>
                                <td><?= $property['id'] ?></td>
                                <td><?= htmlspecialchars($property['title']) ?></td>
                                <td>
                                    <span class="badge <?= $property['status'] == 'active' ? 'bg-success' : 'bg-warning' ?>">
                                        <?= $property['status'] ?>
                                    </span>
                                </td>
                                <td>
                                    <?php 
                                    $images = json_decode($property['images'], true);
                                    if (is_array($images) && !empty($images)) {
                                        echo count($images) . " resim";
                                    } else {
                                        echo "Resim yok";
                                    }
                                    ?>
                                </td>
                                <td>
                                    <a href="property-details.php?id=<?= $property['id'] ?>" class="btn btn-sm btn-primary me-1">
                                        Detayları Gör
                                    </a>
                                    <a href="add-property.php?edit=<?= $property['id'] ?>" class="btn btn-sm btn-warning">
                                        Düzenle
                                    </a>
                                </td>
                            </tr>
                        <?php endwhile; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="5" class="text-center">Aktif property bulunamadı</td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
        
        <div class="mt-4">
            <a href="index.php" class="btn btn-secondary">Ana Sayfa</a>
            <a href="test-property-details.php" class="btn btn-info">Test Sayfası</a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.1.3/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
