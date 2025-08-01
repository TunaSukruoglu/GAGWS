<?php
// Basit add-property test - session bypass
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Fake session data for testing
session_start();
$_SESSION['user_id'] = 1; // Test user ID
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Property Test - Session Bypass</title>
    
    <!-- Bootstrap CSS -->
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    
    <style>
        body {
            background-color: #f8f9fa;
            font-family: Arial, sans-serif;
            padding: 20px;
        }
        
        .test-container {
            max-width: 800px;
            margin: 0 auto;
            background: white;
            padding: 30px;
            border-radius: 15px;
            box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        }
        
        .category-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 15px;
            margin-bottom: 20px;
        }
        
        .category-item {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 20px;
            text-align: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .category-item:hover, .category-item.selected {
            border-color: #0d6efd;
            background: #f8f9ff;
        }
        
        .type-option {
            border: 2px solid #e9ecef;
            border-radius: 10px;
            padding: 15px 25px;
            margin-right: 15px;
            cursor: pointer;
            transition: all 0.3s ease;
            display: inline-block;
        }
        
        .type-option:hover, .type-option.selected {
            border-color: #0d6efd;
            background: #f8f9ff;
        }
    </style>
</head>

<body>
    <div class="test-container">
        <h1 class="mb-4"><i class="fas fa-home text-primary"></i> Add Property - Test Version</h1>
        
        <div class="alert alert-info">
            <i class="fas fa-info-circle"></i> 
            Bu test sayfası session bypass ile çalışıyor. Session User ID: <?= $_SESSION['user_id'] ?>
        </div>

        <form method="POST" id="propertyForm">
            <!-- Kategori Seçimi -->
            <div class="mb-4">
                <h3 class="border-bottom pb-2 mb-3">Kategori ve Tür</h3>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">Emlak Kategorisi</label>
                    <div class="category-grid">
                        <div class="category-item" data-category="residential">
                            <i class="fas fa-home fa-2x text-primary mb-2"></i>
                            <h6>Konut</h6>
                            <small class="text-muted">Ev, Daire, Villa</small>
                        </div>
                        <div class="category-item" data-category="commercial">
                            <i class="fas fa-building fa-2x text-primary mb-2"></i>
                            <h6>Ticari</h6>
                            <small class="text-muted">Dükkan, Ofis, Mağaza</small>
                        </div>
                        <div class="category-item" data-category="land">
                            <i class="fas fa-tree fa-2x text-primary mb-2"></i>
                            <h6>Arsa</h6>
                            <small class="text-muted">İmar, Tarla, Bahçe</small>
                        </div>
                    </div>
                    <input type="hidden" name="category" id="category">
                </div>
                
                <div class="mb-3">
                    <label class="form-label fw-bold">İşlem Türü</label>
                    <div class="mb-3">
                        <div class="type-option" data-type="sale">
                            <i class="fas fa-tags text-success"></i> Satılık
                        </div>
                        <div class="type-option" data-type="rent">
                            <i class="fas fa-key text-warning"></i> Kiralık
                        </div>
                    </div>
                    <input type="hidden" name="type" id="type">
                </div>
            </div>

            <!-- Temel Bilgiler -->
            <div class="mb-4">
                <h3 class="border-bottom pb-2 mb-3">Temel Bilgiler</h3>
                
                <div class="mb-3">
                    <label for="title" class="form-label fw-bold">İlan Başlığı *</label>
                    <input type="text" name="title" id="title" class="form-control" 
                           placeholder="Örn: Merkezi Konumda 3+1 Daire" required>
                </div>
                
                <div class="mb-3">
                    <label for="description" class="form-label fw-bold">Açıklama *</label>
                    <textarea name="description" id="description" class="form-control" rows="4" 
                              placeholder="İlan açıklamasını buraya yazın..." required></textarea>
                </div>
                
                <div class="mb-3">
                    <label for="price" class="form-label fw-bold">Fiyat (₺) *</label>
                    <input type="text" name="price" id="price" class="form-control" 
                           placeholder="Örn: 1.500.000" required>
                </div>
            </div>

            <!-- Submit Button -->
            <div class="d-grid gap-2 d-md-flex">
                <button type="submit" class="btn btn-primary btn-lg me-md-2">
                    <i class="fas fa-save"></i> Test Kaydet
                </button>
                <button type="button" class="btn btn-secondary btn-lg" onclick="location.reload()">
                    <i class="fas fa-refresh"></i> Yenile
                </button>
            </div>
        </form>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Category selection
        document.querySelectorAll('.category-item').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.category-item').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('category').value = this.dataset.category;
                console.log('Kategori seçildi:', this.dataset.category);
            });
        });
        
        // Type selection
        document.querySelectorAll('.type-option').forEach(item => {
            item.addEventListener('click', function() {
                document.querySelectorAll('.type-option').forEach(i => i.classList.remove('selected'));
                this.classList.add('selected');
                document.getElementById('type').value = this.dataset.type;
                console.log('Tip seçildi:', this.dataset.type);
            });
        });
        
        // Form submit test
        document.getElementById('propertyForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const category = document.getElementById('category').value;
            const type = document.getElementById('type').value;
            const title = document.getElementById('title').value;
            
            if (!category || !type || !title) {
                alert('Lütfen tüm gerekli alanları doldurun!');
                return;
            }
            
            alert('Test başarılı! Form verileri:\n' + 
                  'Kategori: ' + category + '\n' + 
                  'Tip: ' + type + '\n' + 
                  'Başlık: ' + title);
        });
    </script>
</body>
</html>
