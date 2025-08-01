<?php
session_start();
include 'db.php';

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    $emailOrUsername = trim($_POST['email'] ?? '');
    $password = $_POST['password'] ?? '';

    // Boş alan kontrolü
    if (empty($emailOrUsername) || empty($password)) {
        $_SESSION['login_error'] = "❌ Lütfen tüm alanları doldurun.";
        header("Location: " . $_SERVER['HTTP_REFERER']);
        exit;
    }

    try {
        // Kullanıcıyı bul
        $stmt = $conn->prepare("SELECT id, name, email, password, role FROM users WHERE email = ? OR name = ?");
        $stmt->bind_param("ss", $emailOrUsername, $emailOrUsername);
        $stmt->execute();
        $stmt->store_result();

        if ($stmt->num_rows > 0) {
            $stmt->bind_result($user_id, $name, $email, $hashed_password, $role);
            $stmt->fetch();
            
            if (password_verify($password, $hashed_password)) {
                // Giriş başarılı
                $_SESSION['user_id'] = $user_id;
                $_SESSION['user_name'] = $name;
                $_SESSION['user_email'] = $email;
                $_SESSION['user_role'] = $role;
                $_SESSION['login_success'] = "✅ Başarıyla giriş yaptınız!";
                
                $stmt->close();
                $conn->close();
                
                // Role göre yönlendirme
                if ($role === 'admin') {
                    header("Location: dashboard/dashboard-admin.php");
                } else {
                    header("Location: index.php");
                }
                exit;
            } else {
                $_SESSION['login_error'] = "❌ Şifre yanlış!";
            }
        } else {
            $_SESSION['login_error'] = "❌ Kullanıcı bulunamadı.";
        }
        
        $stmt->close();
    } catch (Exception $e) {
        $_SESSION['login_error'] = "❌ Giriş sırasında bir hata oluştu.";
        error_log("Login error: " . $e->getMessage());
    }

    $conn->close();
    header("Location: " . $_SERVER['HTTP_REFERER']);
    exit;
}
?>
