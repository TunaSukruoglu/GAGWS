<?php
session_start();
include 'db.php';

if ($_POST) {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Test için admin kullanıcısı oluştur veya bul
    $query = "SELECT id, name, role FROM users WHERE email = ? OR name = ? LIMIT 1";
    $stmt = $conn->prepare($query);
    $stmt->bind_param("ss", $username, $username);
    $stmt->execute();
    $result = $stmt->get_result();
    
    if ($result->num_rows > 0) {
        $user = $result->fetch_assoc();
        $_SESSION['user_id'] = $user['id'];
        $_SESSION['user_name'] = $user['name'];
        $_SESSION['user_role'] = $user['role'];
        
        header("Location: dashboard/add-property.php");
        exit;
    } else {
        $error = "Kullanıcı bulunamadı!";
    }
}
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body class="bg-light">
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                <div class="card">
                    <div class="card-header">
                        <h4>Test Login</h4>
                    </div>
                    <div class="card-body">
                        <?php if (isset($error)): ?>
                            <div class="alert alert-danger"><?= $error ?></div>
                        <?php endif; ?>
                        
                        <form method="POST">
                            <div class="mb-3">
                                <label class="form-label">Kullanıcı Adı / Email</label>
                                <input type="text" name="username" class="form-control" required>
                                <small class="text-muted">Test için: admin</small>
                            </div>
                            <div class="mb-3">
                                <label class="form-label">Şifre</label>
                                <input type="password" name="password" class="form-control" required>
                                <small class="text-muted">Herhangi bir şifre yazın</small>
                            </div>
                            <button type="submit" class="btn btn-primary">Giriş Yap</button>
                        </form>
                        
                        <hr>
                        <p class="text-muted">Mevcut kullanıcılar:</p>
                        <?php
                        $users = $conn->query("SELECT id, name, email, role FROM users LIMIT 5");
                        if ($users && $users->num_rows > 0) {
                            echo "<ul>";
                            while ($user = $users->fetch_assoc()) {
                                echo "<li>ID: {$user['id']} - {$user['name']} ({$user['email']}) - {$user['role']}</li>";
                            }
                            echo "</ul>";
                        } else {
                            echo "<p>Hiç kullanıcı bulunamadı</p>";
                        }
                        ?>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>
