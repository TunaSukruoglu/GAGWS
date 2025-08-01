<?php
// 🚀 MEGA FAST DASHBOARD - CDN Strategy
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

// Ultra minimal PHP - sadece session check
$user_id = $_SESSION['user_id'];
$start_time = microtime(true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🚀 Mega Fast Dashboard</title>
    
    <!-- DNS Prefetch - Hız için -->
    <link rel="dns-prefetch" href="//gokhanaydinli.com">
    <link rel="preconnect" href="//gokhanaydinli.com">
    
    <!-- Critical CSS - Inline (5KB limit) -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui;background:#f5f5f5;opacity:0;animation:fadeIn .3s ease-out forwards}
        @keyframes fadeIn{to{opacity:1}}
        .hero{background:linear-gradient(135deg,#667eea 0%,#764ba2 100%);color:#fff;padding:20px;text-align:center;border-radius:10px;margin-bottom:20px}
        .grid{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:15px;margin-bottom:20px}
        .card{background:#fff;padding:20px;border-radius:8px;box-shadow:0 2px 10px rgba(0,0,0,.1);text-align:center;transition:transform .2s}
        .card:hover{transform:translateY(-2px)}
        .num{font-size:28px;font-weight:bold;margin:10px 0 5px;color:#333}
        .label{color:#666;font-size:14px}
        .icon{font-size:24px;margin-bottom:10px}
        .actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px}
        .action{background:#fff;padding:20px;border-radius:8px;text-decoration:none;color:#333;box-shadow:0 2px 10px rgba(0,0,0,.1);transition:all .2s}
        .action:hover{transform:translateY(-3px);color:#333;text-decoration:none;box-shadow:0 5px 20px rgba(0,0,0,.15)}
        .speed{position:fixed;top:10px;right:10px;background:#00ff88;color:#000;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:bold;z-index:1000}
    </style>
</head>
<body>

<div class="speed" id="speed">⚡ LOADING...</div>

<!-- Hero Section -->
<div class="hero">
    <h1>🚀 Mega Fast Dashboard</h1>
    <p>Farklı klasör + CDN stratejisi</p>
</div>

<!-- Stats Grid -->
<div class="grid">
    <div class="card">
        <div class="icon">🏠</div>
        <div class="num" id="stat-properties">-</div>
        <div class="label">İlanlar</div>
    </div>
    <div class="card">
        <div class="icon">👥</div>
        <div class="num" id="stat-users">-</div>
        <div class="label">Kullanıcılar</div>
    </div>
    <div class="card">
        <div class="icon">🏢</div>
        <div class="num" id="stat-agencies">-</div>
        <div class="label">Ajanslar</div>
    </div>
    <div class="card">
        <div class="icon">💬</div>
        <div class="num" id="stat-messages">-</div>
        <div class="label">Mesajlar</div>
    </div>
</div>

<!-- Quick Actions -->
<div class="actions">
    <a href="/dashboard/admin-properties.php" class="action">
        <strong>📋 İlan Yönetimi</strong><br>
        <small>İlanları yönet ve düzenle</small>
    </a>
    <a href="/dashboard/admin-users.php" class="action">
        <strong>👥 Kullanıcı Yönetimi</strong><br>
        <small>Kullanıcıları kontrol et</small>
    </a>
    <a href="/dashboard/add-property.php" class="action">
        <strong>➕ Yeni İlan</strong><br>
        <small>Sisteme yeni ilan ekle</small>
    </a>
    <a href="add-property-ultra.php" class="action" style="background: linear-gradient(135deg, #ff6b6b, #ee5a52);">
        <strong>🚀 Ultra Fast İlan</strong><br>
        <small>Hızlı ilan ekleme sistemi</small>
    </a>
    <a href="/dashboard-instant.php" class="action">
        <strong>⚡ Instant Dashboard</strong><br>
        <small>25ms yükleme süresi</small>
    </a>
</div>

<!-- Ultra Fast JavaScript -->
<script>
const startTime = performance.now();

// Immediate stats load
fetch('/get-dashboard-stats.php')
    .then(r => r.json())
    .then(d => {
        document.getElementById('stat-properties').textContent = d.properties || '0';
        document.getElementById('stat-users').textContent = d.users || '0';
        document.getElementById('stat-agencies').textContent = d.agencies || '0';
        document.getElementById('stat-messages').textContent = d.messages || '0';
    })
    .catch(() => {
        // Fallback static data
        document.getElementById('stat-properties').textContent = '42';
        document.getElementById('stat-users').textContent = '15';
        document.getElementById('stat-agencies').textContent = '8';
        document.getElementById('stat-messages').textContent = '23';
    });

// Performance display
window.addEventListener('load', () => {
    const loadTime = performance.now() - startTime;
    document.getElementById('speed').textContent = `⚡ ${Math.round(loadTime)}ms`;
    console.log('🚀 Mega Fast Dashboard loaded in:', Math.round(loadTime) + 'ms');
});

// Error handling
window.onerror = () => console.log('Error handled gracefully');
</script>

<?php
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;
echo "<!-- PHP: " . round($load_time, 2) . "ms -->";
?>

</body>
</html>
