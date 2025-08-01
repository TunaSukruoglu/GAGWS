<?php
// 🚀 ULTRA MINIMAL DASHBOARD - EXTREME PERFORMANCE VERSION
// Bu sürüm sadece kritik verileri gösterir, geri kalanı dinamik yüklenir

error_reporting(0); // Production mod
$pageStartTime = microtime(true);

session_start();
include __DIR__ . '/../db.php';

// Sadece kritik kontroller
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Tek sorgu ile user bilgisi
$user_query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_name = $user_query->get_result()->fetch_assoc()['name'];

// Minimal cache
$cacheFile = __DIR__ . '/cache/minimal_stats.json';
if (file_exists($cacheFile) && (time() - filemtime($cacheFile)) < 300) { // 5 dakika
    $stats = json_decode(file_get_contents($cacheFile), true);
} else {
    // Sadece temel sayılar
    $stats = [
        'users' => $conn->query("SELECT COUNT(*) as c FROM users")->fetch_assoc()['c'],
        'properties' => $conn->query("SELECT COUNT(*) as c FROM properties")->fetch_assoc()['c'],
        'visits' => file_exists('../visit_counter.txt') ? (int)file_get_contents('../visit_counter.txt') : 0
    ];
    file_put_contents($cacheFile, json_encode($stats));
}

$loadTime = round((microtime(true) - $pageStartTime) * 1000, 2);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Admin Dashboard - Ultra Fast</title>
<style>
/* ULTRA MINIMAL CSS - Inline Critical Only */
*{margin:0;padding:0;box-sizing:border-box}
body{font-family:system-ui,sans-serif;background:#f5f5f5;color:#333}
.container{max-width:1200px;margin:0 auto;padding:20px}
.header{background:#007bff;color:white;padding:15px;border-radius:8px;margin-bottom:20px}
.stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px}
.card{background:white;padding:20px;border-radius:8px;box-shadow:0 2px 8px rgba(0,0,0,0.1);text-align:center}
.number{font-size:2em;font-weight:bold;color:#007bff;margin-bottom:5px}
.label{color:#666;font-size:0.9em}
.actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px}
.action{background:white;padding:20px;border-radius:8px;text-decoration:none;color:#333;transition:transform 0.2s}
.action:hover{transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,0.15)}
.dynamic-content{background:white;padding:20px;border-radius:8px;margin-top:20px;min-height:200px}
.loading{text-align:center;padding:40px;color:#666}
.performance{position:fixed;bottom:10px;right:10px;background:rgba(0,0,0,0.8);color:white;padding:5px 10px;border-radius:4px;font-size:0.8em}
</style>
</head>
<body>
<div class="container">
    <div class="header">
        <h1>🚀 Ultra Fast Dashboard</h1>
        <p>Hoş geldiniz <?= htmlspecialchars($user_name) ?> | Server Load: <?= $loadTime ?>ms 
        | <a href="dashboard-smart.php?type=full" style="color:#fff;text-decoration:underline">📊 Full Dashboard</a></p>
    </div>
    
    <!-- Kritik İstatistikler -->
    <div class="stats">
        <div class="card">
            <div class="number"><?= number_format($stats['users']) ?></div>
            <div class="label">Kullanıcı</div>
        </div>
        <div class="card">
            <div class="number"><?= number_format($stats['properties']) ?></div>
            <div class="label">Emlak</div>
        </div>
        <div class="card">
            <div class="number"><?= number_format($stats['visits']) ?></div>
            <div class="label">Ziyaret</div>
        </div>
        <div class="card">
            <div class="number" id="live-clock"><?= date('H:i') ?></div>
            <div class="label">Saat</div>
        </div>
    </div>
    
    <!-- Hızlı Aksiyonlar -->
    <div class="actions">
        <a href="admin-users.php" class="action">
            <h3>👥 Kullanıcılar</h3>
            <p>Kullanıcı yönetimi</p>
        </a>
        <a href="admin-properties.php" class="action">
            <h3>🏠 Emlaklar</h3>
            <p>İlan yönetimi</p>
        </a>
        <a href="add-property.php" class="action">
            <h3>➕ Yeni İlan</h3>
            <p>İlan ekle</p>
        </a>
        <a href="dashboard-admin.php" class="action">
            <h3>📊 Tam Dashboard</h3>
            <p>Detaylı görünüm</p>
        </a>
    </div>
    
    <!-- Dinamik İçerik -->
    <div class="dynamic-content">
        <h3>Son Aktiviteler</h3>
        <div id="recent-activity" class="loading">
            ⏳ Yükleniyor...
        </div>
    </div>
</div>

<div class="performance">⚡ <?= $loadTime ?>ms</div>

<script>
// Ultra minimal JS
function updateClock() {
    document.getElementById('live-clock').textContent = new Date().toLocaleTimeString('tr-TR', {hour:'2-digit', minute:'2-digit'});
}
setInterval(updateClock, 60000);

// Lazy load recent activity
setTimeout(() => {
    fetch('ajax/load-minimal-activity.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('recent-activity').innerHTML = data.html || 'Veri yüklenemedi';
        })
        .catch(() => {
            document.getElementById('recent-activity').innerHTML = '❌ Bağlantı hatası';
        });
}, 100);

// Performance tracking
window.addEventListener('load', () => {
    if (performance.timing) {
        const loadTime = performance.timing.loadEventEnd - performance.timing.navigationStart;
        console.log('🚀 Page Load Time:', loadTime + 'ms');
        
        // Server'a performans gönder
        fetch('ajax/log-performance.php', {
            method: 'POST',
            headers: {'Content-Type': 'application/json'},
            body: JSON.stringify({
                type: 'minimal_dashboard',
                loadTime: loadTime,
                timestamp: Date.now()
            })
        }).catch(() => {});
    }
});
</script>
</body>
</html>
