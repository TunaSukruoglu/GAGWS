<?php
session_start();

// Remember token cookie'sini temizle
if (isset($_COOKIE['remember_token'])) {
    setcookie('remember_token', '', time() - 3600, '/', '', false, true);
}

// Tüm session verilerini temizle
$_SESSION = array();

// Session cookie'sini de temizle
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}

// Session'ı yok et
session_destroy();

// Ana sayfaya yönlendir
header("Location: index.php");
exit;
?>