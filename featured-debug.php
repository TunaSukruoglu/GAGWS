<?php
session_start();
include 'db.php';

// Sadmin veya yetkili kullanıcılar erişebilir
if (!isset($_SESSION['user_id'])) {
    die("Giriş yapmamışsınız!");
}

echo "<h2>Featured İlan Debug Raporu</h2>";

// 1. properties tablosundaki featured kolonunu kontrol et
echo "<h3>1. Featured Kolon Yapısı:</h3>";
$column_info = $conn->query("SHOW COLUMNS FROM properties LIKE 'featured'");
if ($column_info->num_rows > 0) {
    $column = $column_info->fetch_assoc();
    echo "<pre>";
    print_r($column);
    echo "</pre>";
} else {
    echo "<p style='color: red;'>Featured kolonu bulunamadı!</p>";
}

// 2. Mevcut ilanların featured durumunu kontrol et
echo "<h3>2. İlan Featured Durumları:</h3>";
$featured_check = $conn->query("SELECT id, title, featured, status, created_at FROM properties ORDER BY created_at DESC LIMIT 10");

if ($featured_check->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>
            <th>ID</th>
            <th>Başlık</th>
            <th>Featured</th>
            <th>Status</th>
            <th>Oluşturma Tarihi</th>
          </tr>";
          
    while ($row = $featured_check->fetch_assoc()) {
        $featured_color = $row['featured'] ? 'green' : 'red';
        $status_color = ($row['status'] == 'active') ? 'green' : 'orange';
        
        echo "<tr>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td style='color: $featured_color; font-weight: bold;'>" . ($row['featured'] ? 'EVET (1)' : 'HAYIR (0)') . "</td>";
        echo "<td style='color: $status_color;'>" . $row['status'] . "</td>";
        echo "<td>" . $row['created_at'] . "</td>";
        echo "</tr>";
    }
    echo "</table>";
} else {
    echo "<p>Hiç ilan bulunamadı!</p>";
}

// 3. Ana sayfa sorgusunu test et
echo "<h3>3. Ana Sayfa Sorgusu Test:</h3>";
$main_query = "SELECT p.*, u.name as owner_name 
               FROM properties p 
               LEFT JOIN users u ON p.user_id = u.id 
               WHERE p.status IN ('active', 'approved')
               ORDER BY p.featured DESC, p.created_at DESC 
               LIMIT 12";

echo "<strong>Sorgu:</strong><br>";
echo "<code style='background-color: #f5f5f5; padding: 10px; display: block;'>" . htmlspecialchars($main_query) . "</code>";

$main_result = $conn->query($main_query);
echo "<p><strong>Sonuç:</strong> " . $main_result->num_rows . " ilan bulundu</p>";

if ($main_result->num_rows > 0) {
    echo "<table border='1' style='border-collapse: collapse;'>";
    echo "<tr style='background-color: #f0f0f0;'>
            <th>Sıra</th>
            <th>ID</th>
            <th>Başlık</th>
            <th>Featured</th>
            <th>Status</th>
            <th>Ana Sayfada Gösterilir mi?</th>
          </tr>";
          
    $counter = 1;
    while ($row = $main_result->fetch_assoc()) {
        $featured_color = $row['featured'] ? 'green' : 'red';
        $will_show = ($row['status'] == 'active' || $row['status'] == 'approved') ? 'EVET' : 'HAYIR';
        $show_color = ($will_show == 'EVET') ? 'green' : 'red';
        
        echo "<tr>";
        echo "<td>$counter</td>";
        echo "<td>" . $row['id'] . "</td>";
        echo "<td>" . htmlspecialchars($row['title']) . "</td>";
        echo "<td style='color: $featured_color; font-weight: bold;'>" . ($row['featured'] ? 'EVET' : 'HAYIR') . "</td>";
        echo "<td>" . $row['status'] . "</td>";
        echo "<td style='color: $show_color; font-weight: bold;'>$will_show</td>";
        echo "</tr>";
        $counter++;
    }
    echo "</table>";
}

// 4. Featured ilanların sayısı
echo "<h3>4. İstatistikler:</h3>";
$stats = [];

$total_result = $conn->query("SELECT COUNT(*) as count FROM properties");
$stats['toplam'] = $total_result->fetch_assoc()['count'];

$featured_result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE featured = 1");
$stats['featured'] = $featured_result->fetch_assoc()['count'];

$active_result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status = 'active'");
$stats['aktif'] = $active_result->fetch_assoc()['count'];

$visible_result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE status IN ('active', 'approved')");
$stats['gorunur'] = $visible_result->fetch_assoc()['count'];

$featured_visible_result = $conn->query("SELECT COUNT(*) as count FROM properties WHERE featured = 1 AND status IN ('active', 'approved')");
$stats['featured_gorunur'] = $featured_visible_result->fetch_assoc()['count'];

echo "<ul>";
echo "<li><strong>Toplam İlan:</strong> " . $stats['toplam'] . "</li>";
echo "<li><strong>Featured İlan:</strong> " . $stats['featured'] . "</li>";
echo "<li><strong>Aktif İlan:</strong> " . $stats['aktif'] . "</li>";
echo "<li><strong>Ana Sayfada Görünebilir İlan:</strong> " . $stats['gorunur'] . "</li>";
echo "<li><strong>Ana Sayfada Görünebilir Featured İlan:</strong> " . $stats['featured_gorunur'] . "</li>";
echo "</ul>";

// 5. Test featured update
echo "<h3>5. Manuel Featured Test:</h3>";
echo "<p><a href='?update_featured=1' style='background-color: orange; color: white; padding: 10px; text-decoration: none;'>Tüm İlanları Featured Yap</a></p>";
echo "<p><a href='?update_featured=0' style='background-color: gray; color: white; padding: 10px; text-decoration: none;'>Tüm İlanları Normal Yap</a></p>";

if (isset($_GET['update_featured'])) {
    $new_featured = intval($_GET['update_featured']);
    $update_query = "UPDATE properties SET featured = ? WHERE id > 0";
    $stmt = $conn->prepare($update_query);
    $stmt->bind_param("i", $new_featured);
    
    if ($stmt->execute()) {
        echo "<p style='color: green;'>✅ Tüm ilanların featured durumu '$new_featured' olarak güncellendi!</p>";
        echo "<p><a href='featured-debug.php'>Sayfayı Yenile</a></p>";
    } else {
        echo "<p style='color: red;'>❌ Güncelleme hatası: " . $stmt->error . "</p>";
    }
}

echo "<br><hr>";
echo "<p><a href='index.php'>🏠 Ana Sayfaya Git</a> | <a href='dashboard/add-property.php'>➕ İlan Ekle</a></p>";
?>
