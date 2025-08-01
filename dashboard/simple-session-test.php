<?php
session_start();
?>
<!DOCTYPE html>
<html>
<head>
    <title>Simple Session Test</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        .info { background: #e3f2fd; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .error { background: #ffebee; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #e8f5e8; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .button { background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; display: inline-block; margin: 5px; }
    </style>
</head>
<body>
    <h1>🔍 Simple Session Test</h1>
    
    <div class="info">
        <h3>Session Durumu:</h3>
        <strong>Session ID:</strong> <?= session_id() ?><br>
        <strong>Session Status:</strong> <?= session_status() == PHP_SESSION_ACTIVE ? 'Aktif' : 'İnaktif' ?><br>
        <strong>Session Save Path:</strong> <?= session_save_path() ?><br>
    </div>

    <div class="info">
        <h3>Session Verileri:</h3>
        <?php if (!empty($_SESSION)): ?>
            <pre><?= print_r($_SESSION, true) ?></pre>
        <?php else: ?>
            <p><strong>❌ Session boş!</strong></p>
        <?php endif; ?>
    </div>

    <div class="info">
        <h3>İşlemler:</h3>
        <a href="?action=set_admin" class="button">Admin Session Oluştur</a>
        <a href="?action=clear" class="button">Session Temizle</a>
        <a href="admin-blog-add-new.php" class="button">Blog Admin Sayfası</a>
        <a href="session-debug.php" class="button">Detaylı Debug</a>
    </div>

    <?php
    if (isset($_GET['action'])) {
        switch ($_GET['action']) {
            case 'set_admin':
                $_SESSION['user_id'] = 1;
                $_SESSION['user_name'] = 'Test Admin';
                $_SESSION['role'] = 'admin';
                $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
                echo '<div class="success">✅ Admin session oluşturuldu!</div>';
                break;
                
            case 'clear':
                session_destroy();
                session_start();
                echo '<div class="success">✅ Session temizlendi!</div>';
                break;
        }
        echo '<meta http-equiv="refresh" content="2">';
    }
    ?>

    <div class="info">
        <h3>Test Sonuçları:</h3>
        <?php if (isset($_SESSION['user_id'])): ?>
            <p>✅ User ID: <?= $_SESSION['user_id'] ?></p>
        <?php else: ?>
            <p>❌ User ID yok</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['role'])): ?>
            <p>✅ Role: <?= $_SESSION['role'] ?></p>
        <?php else: ?>
            <p>❌ Role yok</p>
        <?php endif; ?>

        <?php if (isset($_SESSION['csrf_token'])): ?>
            <p>✅ CSRF Token: <?= substr($_SESSION['csrf_token'], 0, 20) ?>...</p>
        <?php else: ?>
            <p>❌ CSRF Token yok</p>
        <?php endif; ?>
    </div>

</body>
</html>
