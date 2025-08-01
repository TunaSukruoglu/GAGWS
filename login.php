<?php
session_start();

// Hata ayıklama
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

// Türkçe karakter desteği
header('Content-Type: text/html; charset=UTF-8');
ini_set('default_charset', 'UTF-8');

// Veritabanı bağlantısı
include 'db.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    try {
        // Form verilerini al ve temizle
        $email = trim($_POST['email'] ?? '');
        $password = $_POST['password'] ?? '';
        $remember = isset($_POST['remember']);

        // Validasyonlar
        if (empty($email) || empty($password)) {
            $_SESSION['login_error'] = "E-posta ve şifre gereklidir.";
            header('Location: index.php');
            exit();
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $_SESSION['login_error'] = "Geçerli bir e-posta adresi girin.";
            header('Location: index.php');
            exit();
        }

        // Kullanıcıyı veritabanında ara
        $stmt = $conn->prepare("
            SELECT id, name, email, password, is_verified, role 
            FROM users 
            WHERE email = ?
        ");
        $stmt->bind_param("s", $email);
        $stmt->execute();
        $result = $stmt->get_result();

        if ($result->num_rows === 1) {
            $user = $result->fetch_assoc();
            
            // Şifre kontrolü
            if (password_verify($password, $user['password'])) {
                
                // E-posta doğrulama kontrolü (Root ve Admin hariç)
                if ($user['is_verified'] == 0 && $user['role'] !== 'root' && $user['role'] !== 'admin') {
                    $_SESSION['login_error'] = "Hesabınız henüz doğrulanmamış. Lütfen e-posta adresinizi kontrol edin ve aktivasyon linkine tıklayın.";
                    header('Location: index.php');
                    exit();
                }
                
                // Giriş başarılı - Session'ları ayarla
                $_SESSION['user_id'] = $user['id'];
                $_SESSION['user_name'] = $user['name'];
                $_SESSION['user_email'] = $user['email'];
                $_SESSION['user_role'] = $user['role'] ?? 'user';
                $_SESSION['logged_in'] = true;
                
                // "Beni Hatırla" seçeneği için cookie
                if ($remember) {
                    $remember_token = bin2hex(random_bytes(32));
                    
                    // Token'ı veritabanına kaydet
                    $update_stmt = $conn->prepare("UPDATE users SET remember_token = ? WHERE id = ?");
                    $update_stmt->bind_param("si", $remember_token, $user['id']);
                    $update_stmt->execute();
                    
                    // Cookie ayarla (30 gün)
                    setcookie('remember_token', $remember_token, time() + (30 * 24 * 60 * 60), '/', '', false, true);
                }
                
                // Giriş zamanını güncelle
                $login_update = $conn->prepare("UPDATE users SET last_login = NOW() WHERE id = ?");
                $login_update->bind_param("i", $user['id']);
                $login_update->execute();
                
                $_SESSION['login_success'] = "Hoş geldiniz, " . htmlspecialchars($user['name']) . "!";
                
                // Rol bazlı yönlendirme
                $user_role = $user['role'] ?? 'user';
                
                if ($user_role === 'root' || $user_role === 'admin') {
                    // Root/Admin için admin dashboard
                    $redirect_url = 'dashboard/dashboard-admin.php';
                } else if ($user_role === 'agent') {
                    // Agent için kullanıcı dashboard (şimdilik)
                    $redirect_url = 'dashboard/dashboard-user.php';
                } else {
                    // Normal kullanıcı için basit dashboard
                    $redirect_url = 'dashboard/dashboard-user.php';
                }
                
                // Önceden belirlenmiş yönlendirme varsa onu kullan
                if (isset($_SESSION['redirect_after_login'])) {
                    $redirect_url = $_SESSION['redirect_after_login'];
                    unset($_SESSION['redirect_after_login']);
                }
                
                header('Location: ' . $redirect_url);
                exit();
                
            } else {
                $_SESSION['login_error'] = "E-posta veya şifre hatalı.";
            }
        } else {
            $_SESSION['login_error'] = "E-posta veya şifre hatalı.";
        }

    } catch (Exception $e) {
        error_log("Login error: " . $e->getMessage());
        $_SESSION['login_error'] = "Giriş sırasında bir hata oluştu. Lütfen tekrar deneyin.";
    }
} else {
    // GET isteği ise ana sayfaya yönlendir
    $_SESSION['login_error'] = "Geçersiz istek.";
}

header('Location: index.php');
exit();
?>