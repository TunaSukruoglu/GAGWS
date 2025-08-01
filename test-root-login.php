<?php
session_start();

echo "🔐 LOGIN TEST - Root Kullanıcısı\n";
echo "===============================\n\n";

// Database bağlantısı
include 'db.php';

// Root kullanıcı bilgileri
$email = 'root@gokhanaydinli.com';

// Kullanıcıyı ara
$stmt = $conn->prepare("SELECT id, name, email, role FROM users WHERE email = ?");
$stmt->bind_param("s", $email);
$stmt->execute();
$result = $stmt->get_result();

if ($result->num_rows === 1) {
    $user = $result->fetch_assoc();
    
    echo "✅ Root kullanıcı bulundu:\n";
    echo "─────────────────────────\n";
    echo "🆔 ID: {$user['id']}\n";
    echo "👤 İsim: {$user['name']}\n";
    echo "📧 Email: {$user['email']}\n";
    echo "🔑 Role: {$user['role']}\n\n";
    
    // Yönlendirme mantığını test et
    $user_role = $user['role'] ?? 'user';
    
    if ($user_role === 'admin') {
        $redirect_url = 'dashboard/dashboard-admin.php';
        echo "🎯 Yönlendirme: {$redirect_url} (Admin Dashboard)\n";
    } else {
        $redirect_url = 'dashboard/dashboard-user.php';
        echo "🎯 Yönlendirme: {$redirect_url} (User Dashboard)\n";
    }
    
} else {
    echo "❌ Root kullanıcı bulunamadı!\n";
}
?>
