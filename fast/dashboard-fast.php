<?php
// 🚀 ULTRA FAST DASHBOARD - EXTERNAL CODE LOADING
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
    <title>⚡ Fast Dashboard</title>
    <style>
        /* Minimal Critical CSS - Sadece Skeleton */
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body { font-family: system-ui; background: #f5f5f5; }
        .loading { display: flex; justify-content: center; align-items: center; height: 100vh; flex-direction: column; }
        .spinner { width: 40px; height: 40px; border: 4px solid #f3f3f3; border-top: 4px solid #3498db; border-radius: 50%; animation: spin 1s linear infinite; }
        @keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }
        .logo { font-size: 24px; margin-bottom: 20px; color: #3498db; }
    </style>
</head>
<body>

<div class="loading" id="loading">
    <div class="logo">🚀 Gayrimenkul Dashboard</div>
    <div class="spinner"></div>
    <p style="margin-top: 15px; color: #666;">Hızlı yükleniyor...</p>
</div>

<div id="dashboard-content" style="display: none;"></div>

<script>
// 🚀 INSTANT LOADING STRATEGY
const startTime = performance.now();

// 1. İlk HTML şablonu yükle (10ms sonra)
setTimeout(() => {
    fetch('/fast/dashboard-template.html')
        .then(r => r.text())
        .then(html => {
            document.getElementById('dashboard-content').innerHTML = html;
            document.getElementById('loading').style.display = 'none';
            document.getElementById('dashboard-content').style.display = 'block';
            
            // 2. CSS yükle (paralel)
            loadCSS('/fast/dashboard-styles.css');
            
            // 3. JavaScript fonksiyonları yükle (paralel)
            loadJS('/fast/dashboard-functions.js');
            
            // 4. İstatistikleri yükle (AJAX)
            loadStats();
            
            console.log('✅ Dashboard loaded in:', (performance.now() - startTime).toFixed(2) + 'ms');
        });
}, 10);

function loadCSS(url) {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = url;
    link.onload = () => console.log('✅ CSS loaded');
    document.head.appendChild(link);
}

function loadJS(url) {
    const script = document.createElement('script');
    script.src = url;
    script.onload = () => console.log('✅ JS loaded');
    document.head.appendChild(script);
}

function loadStats() {
    fetch('/get-dashboard-stats.php')
        .then(r => r.json())
        .then(stats => {
            document.getElementById('stat-properties').textContent = stats.properties || '0';
            document.getElementById('stat-users').textContent = stats.users || '0';
            document.getElementById('stat-agencies').textContent = stats.agencies || '0';
            document.getElementById('stat-messages').textContent = stats.messages || '0';
            console.log('✅ Stats loaded');
        })
        .catch(e => console.log('Stats failed:', e));
}
</script>

<?php
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;
echo "<!-- PHP Load Time: " . round($load_time, 2) . "ms -->";
?>

</body>
</html>
