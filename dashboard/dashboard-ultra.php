<?php
// 🚀 ULTRA MINIMAL DASHBOARD - INSTANT LOAD
session_start();
include __DIR__ . '/../db.php';

// Admin kontrolü
if (!isset($_SESSION['user_id']) || !isAdmin($_SESSION['user_id'], $conn)) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Minimal user data
$user_query = $conn->prepare("SELECT name FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

// Ultra minimal stats - single query
$stats = $conn->query("
    SELECT 
        (SELECT COUNT(*) FROM users) as total_users,
        (SELECT COUNT(*) FROM properties) as total_properties
")->fetch_assoc();
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>⚡ Ultra Dashboard - <?= htmlspecialchars($user_data['name']) ?></title>
    
    <!-- INLINE CRITICAL CSS ONLY -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui,sans-serif;background:#f8f9fa;color:#333}
        .container{max-width:1200px;margin:0 auto;padding:20px}
        .header{background:linear-gradient(135deg,#0d6efd,#0d1a1c);color:white;padding:30px;border-radius:15px;margin-bottom:30px;text-align:center}
        .title{font-size:28px;font-weight:700;margin-bottom:10px}
        .subtitle{opacity:0.9;font-size:16px}
        .stats{display:grid;grid-template-columns:repeat(auto-fit,minmax(200px,1fr));gap:20px;margin-bottom:30px}
        .stat{background:white;padding:25px;border-radius:15px;box-shadow:0 5px 15px rgba(0,0,0,0.1);text-align:center}
        .stat-number{font-size:32px;font-weight:700;color:#0d6efd;margin-bottom:8px}
        .stat-label{color:#666;font-size:14px}
        .actions{display:grid;grid-template-columns:repeat(auto-fit,minmax(250px,1fr));gap:15px}
        .action{background:white;padding:20px;border-radius:10px;box-shadow:0 3px 10px rgba(0,0,0,0.1);text-decoration:none;color:#333;transition:all 0.3s}
        .action:hover{transform:translateY(-3px);box-shadow:0 8px 25px rgba(0,0,0,0.15);text-decoration:none;color:#333}
        .action-icon{width:40px;height:40px;background:#0d6efd;border-radius:8px;display:flex;align-items:center;justify-content:center;color:white;margin-bottom:12px;font-size:18px}
        .action-title{font-weight:600;margin-bottom:5px}
        .action-desc{font-size:13px;color:#666}
        .loading{text-align:center;padding:40px;color:#666}
        @media (max-width:768px){.stats{grid-template-columns:1fr 1fr}.actions{grid-template-columns:1fr}}
    </style>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <div class="header">
            <div class="title">⚡ Ultra Dashboard</div>
            <div class="subtitle">Hoş geldin, <?= htmlspecialchars($user_data['name']) ?>! Sistem anında hazır.</div>
        </div>

        <!-- Instant Stats -->
        <div class="stats">
            <div class="stat">
                <div class="stat-number"><?= number_format($stats['total_users']) ?></div>
                <div class="stat-label">Toplam Kullanıcı</div>
            </div>
            <div class="stat">
                <div class="stat-number"><?= number_format($stats['total_properties']) ?></div>
                <div class="stat-label">Toplam Emlak</div>
            </div>
            <div class="stat">
                <div class="stat-number" id="live-time">--:--</div>
                <div class="stat-label">Canlı Saat</div>
            </div>
            <div class="stat">
                <div class="stat-number">⚡</div>
                <div class="stat-label">Ultra Hızlı</div>
            </div>
        </div>

        <!-- Quick Actions -->
        <div class="actions">
            <a href="admin-users.php" class="action">
                <div class="action-icon">👥</div>
                <div class="action-title">Kullanıcı Yönetimi</div>
                <div class="action-desc">Kullanıcıları yönet</div>
            </a>
            <a href="admin-properties.php" class="action">
                <div class="action-icon">🏠</div>
                <div class="action-title">Emlak Yönetimi</div>
                <div class="action-desc">İlanları yönet</div>
            </a>
            <a href="add-property.php" class="action">
                <div class="action-icon">➕</div>
                <div class="action-title">Yeni İlan</div>
                <div class="action-desc">İlan ekle</div>
            </a>
            <a href="admin-settings.php" class="action">
                <div class="action-icon">⚙️</div>
                <div class="action-title">Ayarlar</div>
                <div class="action-desc">Sistem ayarları</div>
            </a>
            <a href="dashboard-admin.php" class="action">
                <div class="action-icon">📊</div>
                <div class="action-title">Full Dashboard</div>
                <div class="action-desc">Detaylı görünüm</div>
            </a>
            <a href="../logout.php" class="action">
                <div class="action-icon">🚪</div>
                <div class="action-title">Çıkış</div>
                <div class="action-desc">Güvenli çıkış</div>
            </a>
        </div>
    </div>

    <!-- MINIMAL JAVASCRIPT -->
    <script>
        // Live clock
        function updateTime() {
            const now = new Date();
            document.getElementById('live-time').textContent = 
                now.getHours().toString().padStart(2,'0') + ':' + 
                now.getMinutes().toString().padStart(2,'0');
        }
        updateTime();
        setInterval(updateTime, 1000);

        // Quick animation
        document.querySelectorAll('.action').forEach(el => {
            el.addEventListener('click', function() {
                this.style.transform = 'scale(0.95)';
                setTimeout(() => this.style.transform = '', 100);
            });
        });

        console.log('⚡ Ultra Dashboard loaded in:', performance.now().toFixed(2) + 'ms');
    </script>
</body>
</html>
