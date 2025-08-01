<?php
// Cloudflare Images Test Sayfası
ini_set('display_errors', 1);
error_reporting(E_ALL);

require_once '../includes/cloudflare-images-config.php';
require_once '../includes/cloudflare-images-multi-domain.php';

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cloudflare Images Test</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container py-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4 class="card-title mb-0">
                            <i class="fas fa-cloud me-2"></i>
                            Cloudflare Images Test
                        </h4>
                    </div>
                    <div class="card-body">
                        
                        <!-- Cloudflare Configuration Status -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">📊 Cloudflare Konfigürasyonu</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge <?= USE_CLOUDFLARE_IMAGES ? 'bg-success' : 'bg-danger' ?> me-2">
                                            <?= USE_CLOUDFLARE_IMAGES ? 'AKTİF' : 'KAPALI' ?>
                                        </span>
                                        <span>Cloudflare Images</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge <?= defined('CLOUDFLARE_ACCOUNT_ID') ? 'bg-success' : 'bg-danger' ?> me-2">
                                            <?= defined('CLOUDFLARE_ACCOUNT_ID') ? 'TANIM' : 'EKSİK' ?>
                                        </span>
                                        <span>Account ID</span>
                                    </div>
                                    <div class="d-flex align-items-center mb-2">
                                        <span class="badge <?= defined('CLOUDFLARE_API_TOKEN') ? 'bg-success' : 'bg-danger' ?> me-2">
                                            <?= defined('CLOUDFLARE_API_TOKEN') ? 'TANIM' : 'EKSİK' ?>
                                        </span>
                                        <span>API Token</span>
                                    </div>
                                </div>
                                <div class="col-md-6">
                                    <small class="text-muted">
                                        <strong>Account ID:</strong> <?= defined('CLOUDFLARE_ACCOUNT_ID') ? substr(CLOUDFLARE_ACCOUNT_ID, 0, 10) . '...' : 'Tanımlanmamış' ?><br>
                                        <strong>API Token:</strong> <?= defined('CLOUDFLARE_API_TOKEN') ? substr(CLOUDFLARE_API_TOKEN, 0, 10) . '...' : 'Tanımlanmamış' ?>
                                    </small>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Connection Test -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">🔗 Bağlantı Testi</h6>
                            <?php if (CloudflareImagesConfig::isEnabled()): ?>
                                <?php
                                try {
                                    $cloudflare = new MultiDomainCloudflareImages();
                                    $test_result = $cloudflare->testConnection();
                                    
                                    if ($test_result['success']) {
                                        echo '<div class="alert alert-success">';
                                        echo '<i class="fas fa-check-circle me-2"></i>';
                                        echo '<strong>Bağlantı Başarılı!</strong><br>';
                                        echo '<small>Cloudflare Images API\'ye erişim sağlandı.</small>';
                                        echo '</div>';
                                    } else {
                                        echo '<div class="alert alert-danger">';
                                        echo '<i class="fas fa-times-circle me-2"></i>';
                                        echo '<strong>Bağlantı Hatası!</strong><br>';
                                        echo '<small>' . htmlspecialchars($test_result['error']) . '</small>';
                                        echo '</div>';
                                    }
                                } catch (Exception $e) {
                                    echo '<div class="alert alert-danger">';
                                    echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                                    echo '<strong>Test Hatası!</strong><br>';
                                    echo '<small>' . htmlspecialchars($e->getMessage()) . '</small>';
                                    echo '</div>';
                                }
                                ?>
                            <?php else: ?>
                                <div class="alert alert-warning">
                                    <i class="fas fa-exclamation-triangle me-2"></i>
                                    <strong>Cloudflare Images devre dışı!</strong><br>
                                    <small>Bağlantı testi yapılamıyor.</small>
                                </div>
                            <?php endif; ?>
                        </div>
                        
                        <!-- Upload Test Form -->
                        <div class="mb-4">
                            <h6 class="text-primary mb-3">📤 Upload Test</h6>
                            
                            <?php if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_FILES['test_image'])): ?>
                                <?php
                                if (CloudflareImagesConfig::isEnabled()) {
                                    try {
                                        $cloudflare = new MultiDomainCloudflareImages();
                                        $file = $_FILES['test_image'];
                                        
                                        if ($file['error'] === UPLOAD_ERR_OK) {
                                            echo '<div class="alert alert-info mb-3">';
                                            echo '<i class="fas fa-upload me-2"></i>';
                                            echo 'Upload başlıyor: ' . htmlspecialchars($file['name']) . ' (' . number_format($file['size'] / 1024, 2) . ' KB)';
                                            echo '</div>';
                                            
                                            $upload_result = $cloudflare->uploadImage($file['tmp_name'], $file['name']);
                                            
                                            if ($upload_result['success']) {
                                                $image_id = $upload_result['result']['id'];
                                                echo '<div class="alert alert-success">';
                                                echo '<i class="fas fa-check-circle me-2"></i>';
                                                echo '<strong>Upload Başarılı!</strong><br>';
                                                echo '<small>Image ID: ' . htmlspecialchars($image_id) . '</small>';
                                                echo '</div>';
                                                
                                                // Resmi göster
                                                $image_url = $cloudflare->getImageUrl($image_id, 300, 200);
                                                echo '<div class="text-center mb-3">';
                                                echo '<img src="' . htmlspecialchars($image_url) . '" class="img-fluid border rounded" alt="Test Image">';
                                                echo '<br><small class="text-muted">Cloudflare Images\'dan yüklenen resim</small>';
                                                echo '</div>';
                                                
                                                // Watermark test
                                                echo '<div class="mt-3">';
                                                echo '<button type="button" class="btn btn-info btn-sm" onclick="addWatermark(\'' . htmlspecialchars($image_id) . '\')">Watermark Ekle</button>';
                                                echo '</div>';
                                                
                                            } else {
                                                echo '<div class="alert alert-danger">';
                                                echo '<i class="fas fa-times-circle me-2"></i>';
                                                echo '<strong>Upload Hatası!</strong><br>';
                                                echo '<small>' . htmlspecialchars($upload_result['error']) . '</small>';
                                                echo '</div>';
                                            }
                                        } else {
                                            echo '<div class="alert alert-danger">Upload hatası: ' . $file['error'] . '</div>';
                                        }
                                    } catch (Exception $e) {
                                        echo '<div class="alert alert-danger">';
                                        echo '<i class="fas fa-exclamation-triangle me-2"></i>';
                                        echo '<strong>Exception:</strong> ' . htmlspecialchars($e->getMessage());
                                        echo '</div>';
                                    }
                                } else {
                                    echo '<div class="alert alert-warning">Cloudflare Images devre dışı!</div>';
                                }
                                ?>
                            <?php endif; ?>
                            
                            <form method="POST" enctype="multipart/form-data">
                                <div class="mb-3">
                                    <label for="test_image" class="form-label">Test Resmi Seçin</label>
                                    <input type="file" class="form-control" id="test_image" name="test_image" accept="image/*" required>
                                    <div class="form-text">
                                        JPG, PNG, GIF, WebP formatlarında maksimum 100MB
                                    </div>
                                </div>
                                <button type="submit" class="btn btn-primary">
                                    <i class="fas fa-upload me-2"></i>
                                    Test Upload
                                </button>
                            </form>
                        </div>
                        
                        <!-- Action Buttons -->
                        <div class="text-center">
                            <a href="add-property.php" class="btn btn-success">
                                <i class="fas fa-plus me-2"></i>
                                İlan Ekleme Sayfası
                            </a>
                            <a href="dashboard.php" class="btn btn-secondary">
                                <i class="fas fa-dashboard me-2"></i>
                                Dashboard
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
    function addWatermark(imageId) {
        if (confirm('Bu resme watermark eklemek istediğinize emin misiniz?')) {
            // AJAX ile watermark ekleme
            fetch('cloudflare-watermark-ajax.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    image_id: imageId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('Watermark başarıyla eklendi!');
                    location.reload();
                } else {
                    alert('Watermark ekleme hatası: ' + data.error);
                }
            })
            .catch(error => {
                alert('Bir hata oluştu: ' + error);
            });
        }
    }
    </script>
</body>
</html>
