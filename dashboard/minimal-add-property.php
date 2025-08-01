<?php
// DEBUG: SAYFA BAŞLADI
echo "<!-- DEBUG: Add Property Minimal Started at " . date('Y-m-d H:i:s') . " -->\n";
error_log("🚀 ADD-PROPERTY MINIMAL STARTED: " . date('Y-m-d H:i:s'));

session_start();
$_SESSION['user_id'] = 13; // Admin user for testing
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Yeni İlan Ekle - Minimal</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <style>
        body { font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif; }
        .card { box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
    </style>
</head>
<body class="bg-light">
    <div class="container my-4">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header">
                        <h4>🏠 Yeni İlan Ekle - Minimal Test</h4>
                    </div>
                    <div class="card-body">
                        <form method="post" enctype="multipart/form-data">
                            <div class="mb-3">
                                <label class="form-label">İlan Başlığı</label>
                                <input type="text" class="form-control" name="title" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fiyat (TL)</label>
                                <input type="number" class="form-control" name="price" required>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Açıklama</label>
                                <textarea class="form-control" name="description" rows="4" required></textarea>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Fotoğraflar</label>
                                <input type="file" class="form-control" name="photos[]" multiple accept="image/*">
                            </div>
                            <button type="submit" class="btn btn-success">💾 İlanı Kaydet</button>
                            <a href="dashboard.php" class="btn btn-secondary">❌ İptal</a>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
