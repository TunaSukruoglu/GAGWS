<?php
// filepath: c:\xampp\htdocs\dashboard\GokhanAydinli\includes\session-check.php

// Session zaten başlatılmış mı kontrol et
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Başarı mesajları
if (isset($_SESSION['register_success'])) {
    $msg = json_encode($_SESSION['register_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_success']);
}

if (isset($_SESSION['login_success'])) {
    $msg = json_encode($_SESSION['login_success']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('✅ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_success']);
}

// Hata mesajları
if (isset($_SESSION['register_error'])) {
    $msg = json_encode($_SESSION['register_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['register_error']);
}

if (isset($_SESSION['login_error'])) {
    $msg = json_encode($_SESSION['login_error']);
    echo "<script>
        document.addEventListener('DOMContentLoaded', function() {
            alert('❌ ' + $msg);
        });
    </script>";
    unset($_SESSION['login_error']);
}
?>