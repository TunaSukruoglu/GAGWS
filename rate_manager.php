<?php
/**
 * TÜFE/ÜFE Veri Yönetim Sistemi
 * Güncel enflasyon oranlarını saklama ve çekme
 */

// JSON dosyasından güncel oranları oku
function getCurrentRates() {
    $dataFile = 'data/current_rates.json';
    
    if (file_exists($dataFile)) {
        $data = json_decode(file_get_contents($dataFile), true);
        return $data;
    }
    
    // Varsayılan değerler
    return [
        'tufe' => 65.5,
        'ufe' => 42.5,
        'last_updated' => date('Y-m-d'),
        'source' => 'TÜİK',
        'note' => 'Varsayılan değerler - güncellenmeyi bekliyor'
    ];
}

// Oranları güncelle (Admin paneli için)
function updateRates($tufe, $ufe, $source = 'Manuel Güncelleme') {
    $dataFile = 'data/current_rates.json';
    
    // Data klasörünü oluştur
    if (!file_exists('data')) {
        mkdir('data', 0755, true);
    }
    
    $data = [
        'tufe' => floatval($tufe),
        'ufe' => floatval($ufe),
        'last_updated' => date('Y-m-d H:i:s'),
        'source' => $source,
        'note' => 'Son güncelleme: ' . date('d.m.Y H:i')
    ];
    
    file_put_contents($dataFile, json_encode($data, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    return true;
}

// Geçmiş oranları sakla
function saveHistoricalRates($tufe, $ufe, $period) {
    $historyFile = 'data/rate_history.json';
    
    $history = [];
    if (file_exists($historyFile)) {
        $history = json_decode(file_get_contents($historyFile), true) ?: [];
    }
    
    $history[] = [
        'date' => date('Y-m-d'),
        'period' => $period,
        'tufe' => floatval($tufe),
        'ufe' => floatval($ufe)
    ];
    
    // Son 12 aylık veriyi tut
    $history = array_slice($history, -12);
    
    file_put_contents($historyFile, json_encode($history, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
}

// API çağrısı varsa
if ($_GET['action'] === 'get_current_rates') {
    header('Content-Type: application/json');
    echo json_encode(getCurrentRates());
    exit;
}

// Manuel güncelleme
if ($_POST['action'] === 'update_rates' && isset($_POST['tufe'], $_POST['ufe'])) {
    $tufe = $_POST['tufe'];
    $ufe = $_POST['ufe'];
    $source = $_POST['source'] ?? 'Manuel Güncelleme';
    
    if (updateRates($tufe, $ufe, $source)) {
        saveHistoricalRates($tufe, $ufe, $_POST['period'] ?? date('Y-m'));
        echo json_encode(['success' => true, 'message' => 'Oranlar güncellendi']);
    } else {
        echo json_encode(['success' => false, 'message' => 'Güncelleme başarısız']);
    }
    exit;
}

// Mevcut oranları göster
$currentRates = getCurrentRates();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>TÜFE/ÜFE Veri Yönetimi</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
</head>
<body>
    <div class="container mt-5">
        <div class="row">
            <div class="col-md-8 mx-auto">
                <div class="card">
                    <div class="card-header bg-primary text-white">
                        <h4><i class="fas fa-chart-line"></i> TÜFE/ÜFE Veri Yönetimi</h4>
                    </div>
                    <div class="card-body">
                        <!-- Mevcut Oranlar -->
                        <div class="row mb-4">
                            <div class="col-md-6">
                                <div class="card bg-success text-white">
                                    <div class="card-body text-center">
                                        <h3>%<?php echo $currentRates['tufe']; ?></h3>
                                        <p class="mb-0">TÜFE Oranı</p>
                                    </div>
                                </div>
                            </div>
                            <div class="col-md-6">
                                <div class="card bg-info text-white">
                                    <div class="card-body text-center">
                                        <h3>%<?php echo $currentRates['ufe']; ?></h3>
                                        <p class="mb-0">ÜFE Oranı</p>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <div class="alert alert-info">
                            <strong>Son Güncelleme:</strong> <?php echo $currentRates['last_updated']; ?><br>
                            <strong>Kaynak:</strong> <?php echo $currentRates['source']; ?>
                        </div>

                        <!-- Güncelleme Formu -->
                        <form id="updateForm">
                            <div class="row">
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="tufe" class="form-label">TÜFE Oranı (%)</label>
                                        <input type="number" class="form-control" id="tufe" step="0.1" value="<?php echo $currentRates['tufe']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="ufe" class="form-label">ÜFE Oranı (%)</label>
                                        <input type="number" class="form-control" id="ufe" step="0.1" value="<?php echo $currentRates['ufe']; ?>" required>
                                    </div>
                                </div>
                                <div class="col-md-4">
                                    <div class="mb-3">
                                        <label for="source" class="form-label">Kaynak</label>
                                        <select class="form-control" id="source">
                                            <option value="TÜİK">TÜİK</option>
                                            <option value="TCMB">TCMB</option>
                                            <option value="Manuel Güncelleme">Manuel Güncelleme</option>
                                        </select>
                                    </div>
                                </div>
                            </div>
                            <button type="submit" class="btn btn-primary">
                                <i class="fas fa-save"></i> Oranları Güncelle
                            </button>
                        </form>

                        <!-- TÜİK Link -->
                        <div class="mt-4">
                            <h6>📊 Resmi Kaynaklar:</h6>
                            <a href="https://data.tuik.gov.tr/Kategori/GetKategori?p=enflasyon-ve-fiyat-114" target="_blank" class="btn btn-outline-primary btn-sm">
                                <i class="fas fa-external-link-alt"></i> TÜİK TÜFE Verileri
                            </a>
                            <a href="https://evds2.tcmb.gov.tr/index.php" target="_blank" class="btn btn-outline-info btn-sm">
                                <i class="fas fa-external-link-alt"></i> TCMB EVDS
                            </a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script>
        document.getElementById('updateForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const tufe = document.getElementById('tufe').value;
            const ufe = document.getElementById('ufe').value;
            const source = document.getElementById('source').value;
            
            fetch('', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `action=update_rates&tufe=${tufe}&ufe=${ufe}&source=${encodeURIComponent(source)}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    alert('✅ Oranlar başarıyla güncellendi!');
                    location.reload();
                } else {
                    alert('❌ Güncelleme başarısız: ' + data.message);
                }
            })
            .catch(error => {
                alert('❌ Hata: ' + error.message);
            });
        });
    </script>
</body>
</html>
