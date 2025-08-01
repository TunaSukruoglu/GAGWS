<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>CSRF Debug Kontrol Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.1/font/bootstrap-icons.css" rel="stylesheet">
    <style>
        .card {
            transition: transform 0.2s;
        }
        .card:hover {
            transform: translateY(-2px);
        }
        .status-indicator {
            width: 12px;
            height: 12px;
            border-radius: 50%;
            display: inline-block;
            margin-right: 8px;
        }
        .status-ok {
            background-color: #28a745;
        }
        .status-warning {
            background-color: #ffc107;
        }
        .status-error {
            background-color: #dc3545;
        }
    </style>
</head>
<body>
    <div class="container mt-4">
        <div class="row">
            <div class="col-12">
                <div class="card mb-4">
                    <div class="card-header bg-primary text-white">
                        <h4 class="mb-0">
                            <i class="bi bi-shield-check"></i>
                            CSRF Debug Kontrol Paneli
                        </h4>
                        <small>CSRF token sorunları için debug ve test araçları</small>
                    </div>
                    <div class="card-body">
                        
                        <!-- Ana Test Araçları -->
                        <div class="row mb-4">
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-bug display-4 text-primary mb-3"></i>
                                        <h5>Basit CSRF Test</h5>
                                        <p class="text-muted">Eski versiyon (sorunlu)</p>
                                        <a href="csrf-simple-test.php" class="btn btn-primary">
                                            <i class="bi bi-play-circle"></i> Eski Test
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100 border-success">
                                    <div class="card-body text-center">
                                        <i class="bi bi-check-circle display-4 text-success mb-3"></i>
                                        <h5>Düzeltilmiş Test</h5>
                                        <p class="text-muted">Token yönetimi düzeltildi</p>
                                        <a href="csrf-simple-test-fixed.php" class="btn btn-success">
                                            <i class="bi bi-check-circle"></i> Yeni Test
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-4 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-gear display-4 text-info mb-3"></i>
                                        <h5>Detaylı CSRF Test</h5>
                                        <p class="text-muted">Ajax ve gelişmiş test</p>
                                        <a href="csrf-debug.php" class="btn btn-info">
                                            <i class="bi bi-gear"></i> Başlat
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Sistem Araçları -->
                        <div class="row mb-4">
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-info-circle display-4 text-success mb-3"></i>
                                        <h6>Sistem Bilgileri</h6>
                                        <p class="text-muted small">PHP ayarları ve sistem durumu</p>
                                        <a href="system-info.php" class="btn btn-sm btn-success">
                                            <i class="bi bi-info-circle"></i> Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-cloud-upload display-4 text-primary mb-3"></i>
                                        <h6>Upload Test</h6>
                                        <p class="text-muted small">Form boyut limitlerini test et</p>
                                        <a href="php-upload-test.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-cloud-upload"></i> Test Et
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-file-text display-4 text-warning mb-3"></i>
                                        <h6>Error Log</h6>
                                        <p class="text-muted small">PHP hata logları</p>
                                        <a href="view-logs.php" class="btn btn-sm btn-warning">
                                            <i class="bi bi-file-text"></i> Görüntüle
                                        </a>
                                    </div>
                                </div>
                            </div>
                            
                            <div class="col-md-3 mb-3">
                                <div class="card h-100">
                                    <div class="card-body text-center">
                                        <i class="bi bi-plus-circle display-4 text-primary mb-3"></i>
                                        <h6>İlan Ekleme</h6>
                                        <p class="text-muted small">Ana form (test edilecek)</p>
                                        <a href="add-property.php" class="btn btn-sm btn-primary">
                                            <i class="bi bi-plus-circle"></i> Git
                                        </a>
                                    </div>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Durum Kontrolleri -->
                        <div class="alert alert-light">
                            <h6><i class="bi bi-list-check"></i> Kontrol Listesi:</h6>
                            <div class="row">
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><span class="status-indicator status-ok"></span>CSRF Manager sınıfı oluşturuldu</li>
                                        <li><span class="status-indicator status-ok"></span>Token yönetimi düzeltildi</li>
                                        <li><span class="status-indicator status-ok"></span>Düzeltilmiş test araçları hazır</li>
                                        <li><span class="status-indicator status-warning"></span>Ana form güncellemesi yapılıyor</li>
                                    </ul>
                                </div>
                                <div class="col-md-6">
                                    <ul class="list-unstyled">
                                        <li><span class="status-indicator status-ok"></span>Sistem bilgileri mevcut</li>
                                        <li><span class="status-indicator status-ok"></span>Error log görüntüleyici hazır</li>
                                        <li><span class="status-indicator status-ok"></span>Token sorunu teşhis edildi</li>
                                        <li><span class="status-indicator status-ok"></span>Çözüm uygulandı</li>
                                    </ul>
                                </div>
                            </div>
                        </div>
                        
                        <!-- Test Adımları -->
                        <div class="card">
                            <div class="card-header">
                                <h6><i class="bi bi-list-ol"></i> Önerilen Test Sırası</h6>
                            </div>
                            <div class="card-body">
                                <ol>
                                    <li><strong>Sistem Bilgileri:</strong> PHP ayarlarının uygun olduğunu kontrol edin</li>
                                    <li><strong>Upload Test:</strong> Form boyut limitlerini test edin</li>
                                    <li><strong>Düzeltilmiş Test:</strong> CSRF token ile test edin</li>
                                    <li><strong>Error Log:</strong> Hata mesajlarını inceleyin</li>
                                    <li><strong>Ana Form:</strong> İlan ekleme formunu test edin</li>
                                </ol>
                                
                                <div class="alert alert-warning mt-3">
                                    <strong>Form Boyut Limitleri Artırıldı:</strong><br>
                                    • POST boyutu: 1GB<br>
                                    • Dosya boyutu: 500MB per dosya<br>
                                    • Maksimum dosya sayısı: 50<br>
                                    • Maksimum input sayısı: 10,000<br>
                                    • Zaman limiti: 30 dakika<br>
                                    • Bellek limiti: 1GB
                                </div>
                            </div>
                        </div>
                        
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>
