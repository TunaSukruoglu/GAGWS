<?php
// 🚀 LIGHTNING DASHBOARD - DEFERRED LOADING
session_start();
include __DIR__ . '/../db.php';

if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

$start = microtime(true);
$user_id = $_SESSION['user_id'];

// INSTANT data - single query
$instant = $conn->query("
    SELECT 
        (SELECT name FROM users WHERE id = $user_id) as user_name,
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM properties) as total_properties
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Lightning Dashboard - <?= htmlspecialchars($instant['user_name']) ?></title>
    
    <!-- CRITICAL CSS ONLY -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,sans-serif;background:#f8f9fa;line-height:1.5}
        .page{max-width:1400px;margin:0 auto;padding:20px}
        .hero{background:linear-gradient(135deg,#0d6efd,#6f42c1);color:white;padding:40px;border-radius:20px;margin-bottom:30px;position:relative;overflow:hidden}
        .hero::before{content:'';position:absolute;top:0;right:0;width:200px;height:200px;background:rgba(255,255,255,0.1);border-radius:50%;transform:translate(50px,-50px)}
        .hero-title{font-size:36px;font-weight:800;margin-bottom:10px}
        .hero-subtitle{font-size:18px;opacity:0.9}
        .speed-badge{position:absolute;top:20px;right:20px;background:rgba(255,255,255,0.2);padding:8px 15px;border-radius:20px;font-size:12px}
        .instant-stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat-card{background:white;padding:30px;border-radius:15px;box-shadow:0 4px 20px rgba(0,0,0,0.08);text-align:center;position:relative}
        .stat-number{font-size:42px;font-weight:700;color:#0d6efd;margin-bottom:8px;display:block}
        .stat-label{color:#666;font-size:16px;font-weight:500}
        .pulse{animation:pulse 2s infinite}
        @keyframes pulse{0%,100%{opacity:1}50%{opacity:0.7}}
        .actions-grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(280px,1fr));gap:20px}
        .action-card{background:white;padding:25px;border-radius:15px;box-shadow:0 4px 15px rgba(0,0,0,0.08);text-decoration:none;color:#333;transition:all 0.3s;display:block}
        .action-card:hover{transform:translateY(-5px);box-shadow:0 8px 30px rgba(0,0,0,0.15);text-decoration:none;color:#333}
        .action-icon{width:50px;height:50px;background:#0d6efd;border-radius:12px;display:flex;align-items:center;justify-content:center;color:white;margin-bottom:15px;font-size:22px}
        .action-title{font-size:18px;font-weight:600;margin-bottom:8px}
        .action-desc{color:#666;font-size:14px}
        .loading-content{background:white;border-radius:15px;padding:40px;text-align:center;margin-top:30px}
        .loading-spinner{width:40px;height:40px;border:4px solid #f3f3f3;border-top:4px solid #0d6efd;border-radius:50%;animation:spin 1s linear infinite;margin:0 auto 20px}
        @keyframes spin{0%{transform:rotate(0deg)}100%{transform:rotate(360deg)}}
        @media (max-width:768px){.instant-stats{grid-template-columns:repeat(2,1fr)}.actions-grid{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="page">
        <!-- Instant Hero -->
        <div class="hero">
            <div class="speed-badge">⚡ <?= round((microtime(true) - $start) * 1000) ?>ms</div>
            <h1 class="hero-title">Lightning Dashboard</h1>
            <p class="hero-subtitle">Hoş geldin <?= htmlspecialchars($instant['user_name']) ?>! Anında yüklenen yönetim paneli.</p>
        </div>

        <!-- Instant Stats -->
        <div class="instant-stats">
            <div class="stat-card">
                <div class="stat-number pulse"><?= number_format($instant['total_users']) ?></div>
                <div class="stat-label">Toplam Kullanıcı</div>
            </div>
            <div class="stat-card">
                <div class="stat-number pulse"><?= number_format($instant['total_properties']) ?></div>
                <div class="stat-label">Toplam Emlak</div>
            </div>
            <div class="stat-card">
                <div class="stat-number" id="live-clock">--:--</div>
                <div class="stat-label">Anlık Saat</div>
            </div>
            <div class="stat-card">
                <div class="stat-number">⚡</div>
                <div class="stat-label">Lightning Fast</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions-grid">
            <a href="admin-users.php" class="action-card">
                <div class="action-icon">👥</div>
                <div class="action-title">Kullanıcı Yönetimi</div>
                <div class="action-desc">Kullanıcıları yönet, roller ata, hesapları kontrol et</div>
            </a>
            <a href="admin-properties.php" class="action-card">
                <div class="action-icon">🏠</div>
                <div class="action-title">Emlak Yönetimi</div>
                <div class="action-desc">İlanları onayla, reddet, düzenle</div>
            </a>
            <a href="add-property.php" class="action-card">
                <div class="action-icon">➕</div>
                <div class="action-title">Yeni İlan Ekle</div>
                <div class="action-desc">Sisteme yeni emlak ilanı ekle</div>
            </a>
            <a href="admin-settings.php" class="action-card">
                <div class="action-icon">⚙️</div>
                <div class="action-title">Sistem Ayarları</div>
                <div class="action-desc">Site konfigürasyonu ve ayarlar</div>
            </a>
            <a href="dashboard-ultra.php" class="action-card">
                <div class="action-icon">🚀</div>
                <div class="action-title">Ultra Dashboard</div>
                <div class="action-desc">En hızlı minimal görünüm</div>
            </a>
            <a href="../logout.php" class="action-card">
                <div class="action-icon">🚪</div>
                <div class="action-title">Güvenli Çıkış</div>
                <div class="action-desc">Oturumu sonlandır</div>
            </a>
        </div>

        <!-- Deferred Content Loading Area -->
        <div id="deferred-content" class="loading-content" style="display:none;">
            <div class="loading-spinner"></div>
            <h3>Detaylı veriler yükleniyor...</h3>
            <p>İstatistikler ve raporlar hazırlanıyor.</p>
        </div>
    </div>

    <!-- MINIMAL JAVASCRIPT -->
    <script>
        // Live clock
        function updateClock() {
            const now = new Date();
            document.getElementById('live-clock').textContent = 
                now.getHours().toString().padStart(2,'0') + ':' + 
                now.getMinutes().toString().padStart(2,'0');
        }
        updateClock();
        setInterval(updateClock, 1000);

        // Click animations
        document.querySelectorAll('.action-card').forEach(card => {
            card.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 150);
            });
        });

        // Performance logging
        window.addEventListener('load', function() {
            const loadTime = performance.now();
            console.log('⚡ Lightning Dashboard loaded in:', loadTime.toFixed(2) + 'ms');
            
            // Update speed badge
            document.querySelector('.speed-badge').textContent = `⚡ ${loadTime.toFixed(0)}ms`;
        });

        // Optional: Load detailed stats after 3 seconds (non-blocking)
        setTimeout(function() {
            // This would load detailed dashboard content only if needed
            // fetch('dashboard-detailed-stats.php').then(...) 
        }, 3000);
    </script>
</body>
</html>
