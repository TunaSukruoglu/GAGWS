<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();

echo "<!DOCTYPE html>";
echo "<html><head><title>Database Reset Test</title></head><body>";
echo "<h1>Database Reset Debug</h1>";

// Adım 1: Session kontrolü
echo "<h3>1. Session Kontrolü:</h3>";
if (isset($_SESSION['user_id'])) {
    echo "✅ Session user_id: " . $_SESSION['user_id'] . "<br>";
} else {
    echo "❌ Session user_id bulunamadı<br>";
}

if (isset($_SESSION['role'])) {
    echo "✅ Session role: " . $_SESSION['role'] . "<br>";
} else {
    echo "❌ Session role bulunamadı<br>";
}

if (isset($_SESSION['user_role'])) {
    echo "✅ Session user_role: " . $_SESSION['user_role'] . "<br>";
} else {
    echo "❌ Session user_role bulunamadı<br>";
}

// Adım 2: Admin kontrolü
echo "<h3>2. Admin Kontrolü:</h3>";
$user_role = $_SESSION['role'] ?? $_SESSION['user_role'] ?? '';
if ($user_role === 'admin') {
    echo "✅ Admin yetkisi doğrulandı<br>";
} else {
    echo "❌ Admin yetkisi yok. Mevcut rol: " . $user_role . "<br>";
    echo "<a href='../login.php'>Giriş Yap</a>";
    echo "</body></html>";
    exit();
}

// Adım 3: Database bağlantısı
echo "<h3>3. Database Bağlantısı:</h3>";
try {
    // Farklı olası yolları dene
    $db_paths = [
        'db.php',                    // Aynı klasör
        './db.php',                  // Aynı klasör explicit
        'dashboard/db.php',          // Dashboard altında
        '../db.php',                 // Üst klasör
        '../../db.php',              // İki üst klasör
        '/home/gokhanay/public_html/db.php',     // Tam yol
        __DIR__ . '/db.php',         // PHP DIR kullanarak
        __DIR__ . '/dashboard/db.php' // Dashboard altında
    ];
    
    $conn = null;
    $found_path = null;
    
    foreach ($db_paths as $path) {
        echo "Deneniyor: $path<br>";
        if (file_exists($path)) {
            echo "✅ Dosya bulundu: $path<br>";
            include $path;
            if (isset($conn) && $conn) {
                $found_path = $path;
                echo "✅ Database bağlantısı başarılı: $path<br>";
                break;
            } else {
                echo "❌ Dosya var ama bağlantı kurulamadı: $path<br>";
            }
        } else {
            echo "❌ Dosya bulunamadı: $path<br>";
        }
    }
    
    if (isset($conn) && $conn) {
        echo "✅ Database bağlantısı başarılı<br>";
        
        // Test sorgusu
        $result = $conn->query("SELECT COUNT(*) as count FROM users");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "✅ Test sorgusu başarılı. Kullanıcı sayısı: " . $row['count'] . "<br>";
        } else {
            echo "❌ Test sorgusu başarısız: " . $conn->error . "<br>";
        }
    } else {
        echo "❌ Database bağlantısı başarısız<br>";
    }
} catch (Exception $e) {
    echo "❌ Database hatası: " . $e->getMessage() . "<br>";
}

// Adım 4: İstatistikler
echo "<h3>4. Mevcut Veriler:</h3>";
try {
    $tables = ['users', 'properties', 'blogs', 'favorites'];
    foreach ($tables as $table) {
        $result = $conn->query("SELECT COUNT(*) as count FROM $table");
        if ($result) {
            $row = $result->fetch_assoc();
            echo "$table: " . $row['count'] . " kayıt<br>";
        } else {
            echo "$table: Sorgu hatası<br>";
        }
    }
} catch (Exception $e) {
    echo "❌ İstatistik hatası: " . $e->getMessage() . "<br>";
}

echo "<hr>";
echo "<h3>Reset İşlemleri:</h3>";
echo "<p><a href='database-reset-full.php'>Tam Database Reset Aracına Git</a></p>";
echo "<p><a href='dashboard-admin.php'>Admin Dashboard'a Dön</a></p>";

echo "</body></html>";
?>
