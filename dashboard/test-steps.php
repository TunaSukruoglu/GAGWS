<?php
// 500 Hata Tespiti için Kademeli Test
echo "<h1>ADD-PROPERTY.PHP ADIM ADIM TEST</h1>";

// Adım 1: PHP Temel Kontrol
echo "<h2>Adım 1: PHP Temel Kontrol</h2>";
echo "PHP Version: " . phpversion() . "<br>";
echo "Memory Limit: " . ini_get('memory_limit') . "<br>";
echo "Max Execution Time: " . ini_get('max_execution_time') . "<br>";

// Adım 2: Session Test
echo "<h2>Adım 2: Session Test</h2>";
try {
    session_start();
    echo "✓ Session başlatıldı<br>";
    echo "Session ID: " . session_id() . "<br>";
} catch (Exception $e) {
    echo "✗ Session hatası: " . $e->getMessage() . "<br>";
    exit;
}

// Adım 3: Database Include Test
echo "<h2>Adım 3: Database Include Test</h2>";
try {
    include '../db.php';
    echo "✓ Database dosyası include edildi<br>";
    
    if (isset($conn) && $conn) {
        echo "✓ Database connection objesi var<br>";
        
        $test = $conn->query("SELECT 1");
        if ($test) {
            echo "✓ Database sorgusu çalışıyor<br>";
        } else {
            echo "✗ Database sorgusu başarısız: " . $conn->error . "<br>";
            exit;
        }
    } else {
        echo "✗ Database connection objesi yok<br>";
        exit;
    }
} catch (Exception $e) {
    echo "✗ Database hatası: " . $e->getMessage() . "<br>";
    exit;
}

// Adım 4: Dosya Kontrol
echo "<h2>Adım 4: Kritik Dosya Kontrol</h2>";
$files = [
    '../includes/header-admin.php',
    '../includes/sidebar-admin.php',
    'includes/csrf-manager.php',
    '../assets/dashboard-style.css',
    'includes/dashboard-common.css'
];

foreach ($files as $file) {
    if (file_exists($file)) {
        echo "✓ " . $file . "<br>";
    } else {
        echo "✗ " . $file . " BULUNAMADI<br>";
    }
}

// Adım 5: Include Test
echo "<h2>Adım 5: Include Test</h2>";
try {
    if (file_exists('includes/csrf-manager.php')) {
        require_once 'includes/csrf-manager.php';
        echo "✓ CSRF Manager include edildi<br>";
    } else {
        echo "! CSRF Manager dosyası yok, alternatif kullanılacak<br>";
    }
} catch (Exception $e) {
    echo "✗ CSRF Manager hatası: " . $e->getMessage() . "<br>";
}

// Adım 6: Session Kontrol (User Auth için)
echo "<h2>Adım 6: Session Kullanıcı Kontrol</h2>";
if (isset($_SESSION['user_id'])) {
    echo "✓ Session'da user_id var: " . $_SESSION['user_id'] . "<br>";
    
    // Test user sorgusu
    try {
        $user_query = $conn->prepare("SELECT name, role FROM users WHERE id = ?");
        $user_query->bind_param("i", $_SESSION['user_id']);
        $user_query->execute();
        $user_data = $user_query->get_result()->fetch_assoc();
        
        if ($user_data) {
            echo "✓ Kullanıcı bulundu: " . $user_data['name'] . " (" . $user_data['role'] . ")<br>";
        } else {
            echo "✗ Kullanıcı bulunamadı<br>";
        }
    } catch (Exception $e) {
        echo "✗ Kullanıcı sorgu hatası: " . $e->getMessage() . "<br>";
    }
} else {
    echo "! Session'da user_id yok (giriş yapmamış)<br>";
}

echo "<hr>";
echo "<h2>Test Tamamlandı!</h2>";
echo "<p>Eğer buraya kadar hata yoksa, şimdi add-property.php'yi test edebilirsiniz:</p>";
echo "<a href='add-property.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>add-property.php'yi dene</a>";
?>
