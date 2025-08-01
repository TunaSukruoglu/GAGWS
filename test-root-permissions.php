<?php
require_once 'db.php';

// Test root kullanıcısının yetkilerini
$conn = getConnection();
if (!$conn) {
    die("Veritabanı bağlantısı başarısız: " . mysqli_connect_error());
}

// Root kullanıcısını bul
$root_user = getUserByUsername('root', $conn);
if (!$root_user) {
    echo "❌ Root kullanıcısı bulunamadı!\n";
    exit;
}

$user_id = $root_user['id'];
echo "<h2>Root Kullanıcısı Yetki Testi</h2>\n";
echo "Kullanıcı ID: " . $user_id . "<br>\n";
echo "Kullanıcı Adı: " . $root_user['username'] . "<br>\n";
echo "Email: " . $root_user['email'] . "<br>\n";
echo "Rol: " . $root_user['role'] . "<br><br>\n";

// Yetki testleri
echo "<h3>Yetki Kontrolleri:</h3>\n";

// isRoot testi
$is_root = isRoot($user_id, $conn);
echo "isRoot(): " . ($is_root ? "✅ EVET" : "❌ HAYIR") . "<br>\n";

// isAdmin testi
$is_admin = isAdmin($user_id, $conn);
echo "isAdmin(): " . ($is_admin ? "✅ EVET" : "❌ HAYIR") . "<br>\n";

// canManageUsers testi
$can_manage = canManageUsers($user_id, $conn);
echo "canManageUsers(): " . ($can_manage ? "✅ EVET" : "❌ HAYIR") . "<br>\n";

// hasPermission testleri
echo "<h3>hasPermission Testleri:</h3>\n";
$permissions = ['add_property', 'edit_property', 'delete_property', 'view_all_properties', 'manage_blog'];

foreach ($permissions as $permission) {
    $has_perm = hasPermission($user_id, $permission, $conn);
    echo "hasPermission('$permission'): " . ($has_perm ? "✅ EVET" : "❌ HAYIR") . "<br>\n";
}

// Email doğrulama durumu
echo "<h3>Email Durumu:</h3>\n";
echo "Email Doğrulanmış: " . ($root_user['email_verified'] ? "✅ EVET" : "❌ HAYIR") . "<br>\n";

echo "<br><a href='login.php'>Giriş Sayfasına Dön</a>";

$conn->close();
?>
