<?php
// Test kullanıcısı oluştur
include 'db.php';

// Test kullanıcısı bilgileri
$name = "Test Kullanıcı";
$email = "test@test.com";
$password = password_hash("123456", PASSWORD_DEFAULT);
$is_verified = 1; // Doğrulanmış
$role = "user";

// Önce varsa sil
$delete_stmt = $conn->prepare("DELETE FROM users WHERE email = ?");
$delete_stmt->bind_param("s", $email);
$delete_stmt->execute();

// Yeni kullanıcı ekle
$stmt = $conn->prepare("INSERT INTO users (name, email, password, is_verified, role, created_at) VALUES (?, ?, ?, ?, ?, NOW())");
$stmt->bind_param("sssis", $name, $email, $password, $is_verified, $role);

if ($stmt->execute()) {
    echo "Test kullanıcısı başarıyla oluşturuldu!<br>";
    echo "E-posta: test@test.com<br>";
    echo "Şifre: 123456<br>";
} else {
    echo "Hata: " . $stmt->error;
}

$stmt->close();
$conn->close();
?>
