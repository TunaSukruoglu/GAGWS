<?php
// 🚀 ULTRA FAST ADD PROPERTY - OPTIMIZED VERSION
error_reporting(E_ALL);
ini_set('display_errors', 1);

// ⚡ PERFORMANCE BOOST
$start_time = microtime(true);

// Session check - fastest way
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

include __DIR__ . '/../db.php';

// Quick user check
$user_id = $_SESSION['user_id'];
$user_query = $pdo->prepare("SELECT name, role FROM users WHERE id = ? LIMIT 1");
$user_query->execute([$user_id]);
$user_data = $user_query->fetch(PDO::FETCH_ASSOC);

if (!$user_data) {
    session_destroy();
    header('Location: ../index.php');
    exit;
}

$page_title = "Yeni İlan Ekle - Ultra Fast";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- ⚡ DNS Prefetch -->
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="preconnect" href="//cdnjs.cloudflare.com">
    
    <!-- ⚡ Critical CSS Inline -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui;background:#f5f5f5;opacity:0;animation:fadeIn .3s ease-out forwards}
        @keyframes fadeIn{to{opacity:1}}
        .container{max-width:1200px;margin:0 auto;padding:20px}
        .header{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:20px;text-align:center;border-radius:10px;margin-bottom:20px}
        .form-container{background:#fff;padding:30px;border-radius:10px;box-shadow:0 2px 15px rgba(0,0,0,.1)}
        .form-group{margin-bottom:20px}
        .form-label{display:block;margin-bottom:5px;font-weight:600;color:#333}
        .form-control{width:100%;padding:12px;border:2px solid #e1e5e9;border-radius:6px;font-size:16px;transition:border-color .2s}
        .form-control:focus{outline:none;border-color:#667eea}
        .btn{background:#667eea;color:#fff;padding:12px 24px;border:none;border-radius:6px;cursor:pointer;font-size:16px;transition:all .2s}
        .btn:hover{background:#5a67d8;transform:translateY(-1px)}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(300px,1fr));gap:20px}
        .speed{position:fixed;top:10px;right:10px;background:#00ff88;color:#000;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:bold;z-index:1000}
        .nav-links{text-align:center;margin-bottom:20px}
        .nav-links a{color:#667eea;text-decoration:none;margin:0 10px;padding:8px 16px;border-radius:5px;transition:all .2s}
        .nav-links a:hover{background:#667eea;color:#fff}
    </style>
</head>
<body>

<div class="speed" id="speed">⚡ LOADING...</div>

<div class="container">
    <!-- Navigation -->
    <div class="nav-links">
        <a href="dashboard-admin.php">📊 Dashboard</a>
        <a href="../fast/dashboard-mega.php">🚀 Mega Fast</a>
        <a href="admin-properties.php">🏠 İlanlar</a>
        <a href="admin-users.php">👥 Kullanıcılar</a>
    </div>

    <!-- Header -->
    <div class="header">
        <h1>🚀 Ultra Fast İlan Ekleme</h1>
        <p>Hızlı ve etkili emlak ilanı oluşturma sistemi</p>
    </div>

    <?php if (isset($_GET['success'])): ?>
        <div style="background: #d4edda; color: #155724; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            ✅ İlan başarıyla eklendi! (İşlem süresi: <?= $_GET['time'] ?? 'N/A' ?>ms)
            <?php if (isset($_GET['id'])): ?>
                <br><a href="../property_details.php?id=<?= $_GET['id'] ?>" style="color: #155724; text-decoration: underline;">İlanı görüntüle</a>
            <?php endif; ?>
        </div>
    <?php endif; ?>

    <?php if (isset($_GET['error'])): ?>
        <div style="background: #f8d7da; color: #721c24; padding: 15px; border-radius: 5px; margin-bottom: 20px; text-align: center;">
            ❌ Hata: <?= htmlspecialchars($_GET['error']) ?>
        </div>
    <?php endif; ?>

    <!-- Fast Form -->
    <div class="form-container">
        <form id="fastPropertyForm" method="POST" action="process-property-ultra.php" enctype="multipart/form-data">
            <div class="grid">
                <!-- Temel Bilgiler -->
                <div>
                    <h3 style="margin-bottom: 15px; color: #333;">📋 Temel Bilgiler</h3>
                    
                    <div class="form-group">
                        <label class="form-label">İlan Başlığı</label>
                        <input type="text" name="title" class="form-control" placeholder="Örn: Merkezi Konumda Satılık Daire" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Açıklama</label>
                        <textarea name="description" class="form-control" rows="4" placeholder="İlan detaylarını yazın..."></textarea>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Fiyat (₺)</label>
                        <input type="number" name="price" class="form-control" placeholder="500000" required>
                    </div>
                </div>

                <!-- Konum Bilgileri -->
                <div>
                    <h3 style="margin-bottom: 15px; color: #333;">📍 Konum</h3>
                    
                    <div class="form-group">
                        <label class="form-label">İl</label>
                        <select name="city" class="form-control" required>
                            <option value="">İl Seçin</option>
                            <option value="İstanbul">İstanbul</option>
                            <option value="Ankara">Ankara</option>
                            <option value="İzmir">İzmir</option>
                            <option value="Bursa">Bursa</option>
                            <option value="Antalya">Antalya</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">İlçe</label>
                        <input type="text" name="district" class="form-control" placeholder="Örn: Kadıköy" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Mahalle</label>
                        <input type="text" name="neighborhood" class="form-control" placeholder="Örn: Fenerbahçe">
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Adres</label>
                        <input type="text" name="address" class="form-control" placeholder="Detaylı adres">
                    </div>
                </div>

                <!-- Özellikler -->
                <div>
                    <h3 style="margin-bottom: 15px; color: #333;">🏠 Özellikler</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Emlak Tipi</label>
                        <select name="property_type" class="form-control" required>
                            <option value="">Tip Seçin</option>
                            <option value="Daire">Daire</option>
                            <option value="Villa">Villa</option>
                            <option value="Ofis">Ofis</option>
                            <option value="Dükkan">Dükkan</option>
                            <option value="Arsa">Arsa</option>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">İlan Türü</label>
                        <select name="listing_type" class="form-control" required>
                            <option value="Satılık">Satılık</option>
                            <option value="Kiralık">Kiralık</option>
                        </select>
                    </div>
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 10px;">
                        <div class="form-group">
                            <label class="form-label">Oda Sayısı</label>
                            <select name="rooms" class="form-control">
                                <option value="1+0">1+0</option>
                                <option value="1+1">1+1</option>
                                <option value="2+1" selected>2+1</option>
                                <option value="3+1">3+1</option>
                                <option value="4+1">4+1</option>
                                <option value="5+1">5+1</option>
                            </select>
                        </div>
                        
                        <div class="form-group">
                            <label class="form-label">Metrekare</label>
                            <input type="number" name="area" class="form-control" placeholder="120">
                        </div>
                    </div>
                </div>

                <!-- Görsel Yükleme -->
                <div>
                    <h3 style="margin-bottom: 15px; color: #333;">📷 Görseller</h3>
                    
                    <div class="form-group">
                        <label class="form-label">Ana Görsel</label>
                        <input type="file" name="main_image" class="form-control" accept="image/*" required>
                    </div>
                    
                    <div class="form-group">
                        <label class="form-label">Ek Görseller (Opsiyonel)</label>
                        <input type="file" name="additional_images[]" class="form-control" accept="image/*" multiple>
                        <small style="color: #666; font-size: 12px;">En fazla 10 görsel seçebilirsiniz.</small>
                    </div>
                </div>
            </div>

            <!-- Submit Button -->
            <div style="text-align: center; margin-top: 30px;">
                <button type="submit" class="btn" style="font-size: 18px; padding: 15px 30px;">
                    🚀 İlanı Hızla Ekle
                </button>
            </div>
        </form>
    </div>
</div>

<!-- Ultra Fast JavaScript -->
<script>
const startTime = performance.now();

// Form validation
document.getElementById('fastPropertyForm').addEventListener('submit', function(e) {
    const title = document.querySelector('[name="title"]').value;
    const price = document.querySelector('[name="price"]').value;
    const city = document.querySelector('[name="city"]').value;
    
    if (!title || !price || !city) {
        e.preventDefault();
        alert('⚠️ Lütfen zorunlu alanları doldurun!');
        return false;
    }
    
    // Loading state
    const submitBtn = document.querySelector('[type="submit"]');
    submitBtn.textContent = '⏳ Kaydediliyor...';
    submitBtn.disabled = true;
});

// Performance display
window.addEventListener('load', () => {
    const loadTime = performance.now() - startTime;
    document.getElementById('speed').textContent = `⚡ ${Math.round(loadTime)}ms`;
    console.log('🚀 Ultra Fast Add Property loaded in:', Math.round(loadTime) + 'ms');
});

// Auto-save draft (optional)
let draftTimer;
function saveDraft() {
    const formData = new FormData(document.getElementById('fastPropertyForm'));
    const draftData = {};
    
    for (let [key, value] of formData.entries()) {
        if (value && typeof value === 'string') {
            draftData[key] = value;
        }
    }
    
    localStorage.setItem('propertyDraft', JSON.stringify(draftData));
    console.log('💾 Draft saved');
}

// Auto-save every 30 seconds
document.querySelectorAll('input, textarea, select').forEach(input => {
    input.addEventListener('input', () => {
        clearTimeout(draftTimer);
        draftTimer = setTimeout(saveDraft, 2000);
    });
});

// Load draft on page load
window.addEventListener('load', () => {
    const draft = localStorage.getItem('propertyDraft');
    if (draft) {
        try {
            const draftData = JSON.parse(draft);
            Object.keys(draftData).forEach(key => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input && input.type !== 'file') {
                    input.value = draftData[key];
                }
            });
            console.log('📝 Draft loaded');
        } catch (e) {
            console.log('Draft load failed');
        }
    }
});
</script>

<?php
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;
echo "<!-- PHP Load Time: " . round($load_time, 2) . "ms -->";
?>

</body>
</html>
