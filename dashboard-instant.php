<?php
// Ultra-Instant Dashboard - Tüm Fazlalık Kodlar Dışarıdan
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: /login.php');
    exit;
}

$start_time = microtime(true);
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ Instant Dashboard</title>
    <style>
        /* Minimal Critical CSS - Sadece Layout */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui; background: #f5f5f5; }
        .hero { background: linear-gradient(135deg, #667eea 0%, #764ba2 100%); color: white; padding: 20px; text-align: center; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; padding: 20px; }
        .card { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        .loading { opacity: 0.5; animation: pulse 1s infinite; }
        @keyframes pulse { 0%, 100% { opacity: 0.5; } 50% { opacity: 0.8; } }
        .speed-badge { position: absolute; top: 10px; right: 10px; background: #00ff88; color: #000; padding: 5px 10px; border-radius: 20px; font-weight: bold; }
    </style>
</head>
<body>

<!-- Hero Section - İlk Görülen -->
<div class="hero">
    <div class="speed-badge">⚡ INSTANT</div>
    <h1>🚀 Gayrimenkul Dashboard</h1>
    <p id="welcome">Hoşgeldiniz!</p>
    <div id="live-time" style="font-size: 24px; margin-top: 10px;">⏰</div>
</div>

<!-- Critical Stats - Minimal -->
<div class="stats">
    <div class="card loading" id="property-card">
        <h3>🏠 İlanlar</h3>
        <div style="font-size: 32px; color: #667eea;">-</div>
    </div>
    <div class="card loading" id="user-card">
        <h3>👥 Kullanıcılar</h3>
        <div style="font-size: 32px; color: #764ba2;">-</div>
    </div>
    <div class="card loading" id="agency-card">
        <h3>🏢 Ajanslar</h3>
        <div style="font-size: 32px; color: #f093fb;">-</div>
    </div>
    <div class="card loading" id="message-card">
        <h3>💬 Mesajlar</h3>
        <div style="font-size: 32px; color: #4facfe;">-</div>
    </div>
</div>

<!-- Navigation - Static -->
<div style="text-align: center; padding: 20px;">
    <a href="dashboard-lightning.php" style="margin: 0 10px; color: #667eea;">⚡ Lightning</a>
    <a href="dashboard-ultra.php" style="margin: 0 10px; color: #764ba2;">🎯 Ultra</a>
    <a href="dashboard-admin.php" style="margin: 0 10px; color: #f093fb;">📊 Full</a>
</div>

<script>
// Minimal Critical JavaScript - Sadece Clock
function updateClock() {
    const now = new Date();
    document.getElementById('live-time').textContent = 
        '⏰ ' + now.toLocaleTimeString('tr-TR');
}
updateClock();
setInterval(updateClock, 1000);

// External Content Loading - 100ms sonra
setTimeout(() => {
    // Kullanıcı adını yükle
    fetch('get-user-info.php')
        .then(r => r.json())
        .then(data => {
            document.getElementById('welcome').textContent = 
                `Hoşgeldiniz, ${data.name || 'Kullanıcı'}!`;
        });
    
    // Stats yükle
    fetch('get-dashboard-stats.php')
        .then(r => r.json())
        .then(stats => {
            document.getElementById('property-card').classList.remove('loading');
            document.getElementById('property-card').querySelector('div').textContent = stats.properties;
            
            document.getElementById('user-card').classList.remove('loading');
            document.getElementById('user-card').querySelector('div').textContent = stats.users;
            
            document.getElementById('agency-card').classList.remove('loading');
            document.getElementById('agency-card').querySelector('div').textContent = stats.agencies;
            
            document.getElementById('message-card').classList.remove('loading');
            document.getElementById('message-card').querySelector('div').textContent = stats.messages;
        });
}, 100);

// Advanced Features - 500ms sonra
setTimeout(() => {
    // Grafik ve detaylı CSS yükle
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = 'dashboard-advanced.css';
    document.head.appendChild(link);
    
    // Chart.js yükle
    const script = document.createElement('script');
    script.src = 'https://cdn.jsdelivr.net/npm/chart.js';
    script.onload = () => {
        // Grafikleri yükle
        loadCharts();
    };
    document.head.appendChild(script);
}, 500);

function loadCharts() {
    // Grafik containerı ekle
    const chartContainer = document.createElement('div');
    chartContainer.innerHTML = `
        <div style="padding: 20px;">
            <div class="card">
                <h3>📈 İstatistikler</h3>
                <canvas id="statsChart" width="400" height="200"></canvas>
            </div>
        </div>
    `;
    document.body.appendChild(chartContainer);
    
    // Chart oluştur
    const ctx = document.getElementById('statsChart').getContext('2d');
    new Chart(ctx, {
        type: 'line',
        data: {
            labels: ['Pazartesi', 'Salı', 'Çarşamba', 'Perşembe', 'Cuma'],
            datasets: [{
                label: 'Günlük İlanlar',
                data: [12, 19, 3, 5, 2],
                borderColor: '#667eea',
                tension: 0.1
            }]
        }
    });
}
</script>

<?php
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;
echo "<!-- Load Time: " . round($load_time, 2) . "ms -->";
?>

</body>
</html>
