<?php
session_start();
include '../db.php';

// Basit giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    echo "<h1>Giriş Gerekli</h1>";
    echo "<p>Bu sayfayı görüntülemek için giriş yapmanız gerekiyor.</p>";
    echo "<p><a href='../login.php'>Giriş Yap</a></p>";
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini al
$user_query = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    echo "<h1>Kullanıcı Bulunamadı</h1>";
    echo "<p>Kullanıcı bilgileriniz bulunamadı. Lütfen tekrar giriş yapın.</p>";
    echo "<p><a href='../logout.php'>Çıkış Yap</a></p>";
    exit;
}

// Form işleme
$message = '';
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        $title = trim($_POST['title'] ?? '');
        $description = trim($_POST['description'] ?? '');
        $price = floatval($_POST['price'] ?? 0);
        
        if (empty($title) || empty($description) || $price <= 0) {
            throw new Exception("Lütfen tüm alanları doldurun!");
        }
        
        // Basit insert
        $query = "INSERT INTO properties (user_id, title, description, price, type, category, status, created_at) VALUES (?, ?, ?, ?, 'sale', 'residential', 'active', NOW())";
        $stmt = $conn->prepare($query);
        $stmt->bind_param("issd", $user_id, $title, $description, $price);
        
        if ($stmt->execute()) {
            $message = "<div class='alert alert-success'>İlan başarıyla eklendi! ID: " . $conn->insert_id . "</div>";
        } else {
            throw new Exception("Database hatası: " . $stmt->error);
        }
        
    } catch (Exception $e) {
        $message = "<div class='alert alert-danger'>Hata: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basit İlan Ekleme</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-8">
                <div class="card">
                    <div class="card-header">
                        <h3>Basit İlan Ekleme Testi</h3>
                        <p>Kullanıcı: <?php echo htmlspecialchars($user_data['name']); ?> (<?php echo htmlspecialchars($user_data['role']); ?>)</p>
                    </div>
                    <div class="card-body">
                        <?php echo $message; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label for="title" class="form-label">İlan Başlığı *</label>
                                <input type="text" class="form-control" id="title" name="title" required>
                            </div>
                            
                            <div class="mb-3">
                                <label for="description" class="form-label">Açıklama *</label>
                                <textarea class="form-control" id="description" name="description" rows="3" required></textarea>
                            </div>
                            
                            <div class="mb-3">
                                <label for="price" class="form-label">Fiyat (TL) *</label>
                                <input type="number" class="form-control" id="price" name="price" required>
                            </div>
                            
                            <button type="submit" class="btn btn-primary">İlan Ekle</button>
                            <a href="add-property.php" class="btn btn-secondary">Ana Forma Dön</a>
                            <a href="dashboard.php" class="btn btn-info">Dashboard</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
