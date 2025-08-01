<?php
session_start();
require_once 'db.php';

echo "<h2>Veritabanı Tablo Analizi</h2>";

// Properties tablosunun yapısını kontrol et
$table_query = "SHOW COLUMNS FROM properties";
$result = $conn->query($table_query);

echo "<h3>Mevcut Sütunlar:</h3>";
echo "<table border='1' style='border-collapse: collapse;'>";
echo "<tr><th>Sütun</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th></tr>";

$existing_columns = [];
while ($row = $result->fetch_assoc()) {
    $existing_columns[] = $row['Field'];
    echo "<tr>";
    echo "<td>" . htmlspecialchars($row['Field']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Type']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Null']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Key']) . "</td>";
    echo "<td>" . htmlspecialchars($row['Default'] ?? 'NULL') . "</td>";
    echo "</tr>";
}
echo "</table>";

// Form alanları ile karşılaştırma
$form_fields = [
    'user_id', 'title', 'description', 'price', 'type', 'category',
    'listing_type', 'area', 'address', 'city', 'district', 'bedrooms',
    'bathrooms', 'floor', 'year_built', 'heating', 'images', 'main_image'
];

echo "<h3>INSERT Sorgusunda Kullanılan Alanlar:</h3>";
echo "<ul>";
foreach ($form_fields as $field) {
    $exists = in_array($field, $existing_columns);
    $color = $exists ? 'green' : 'red';
    $status = $exists ? '✅' : '❌';
    echo "<li style='color: $color;'>$status $field</li>";
}
echo "</ul>";

// Eksik sütunlar için öneriler
$missing = array_diff($form_fields, $existing_columns);
if (!empty($missing)) {
    echo "<h3>⚠️ Eksik Sütunlar ve Çözüm Önerileri:</h3>";
    echo "<div style='background: #fff3cd; padding: 15px; border: 1px solid #ffeaa7;'>";
    echo "<p>Aşağıdaki SQL komutlarını çalıştırarak eksik sütunları ekleyebilirsiniz:</p>";
    echo "<pre>";
    foreach ($missing as $column) {
        switch ($column) {
            case 'listing_type':
                echo "ALTER TABLE properties ADD COLUMN listing_type VARCHAR(20) DEFAULT 'Satılık';\n";
                break;
            case 'bedrooms':
                echo "ALTER TABLE properties ADD COLUMN bedrooms INT DEFAULT 0;\n";
                break;
            case 'bathrooms':
                echo "ALTER TABLE properties ADD COLUMN bathrooms INT DEFAULT 0;\n";
                break;
            case 'floor':
                echo "ALTER TABLE properties ADD COLUMN floor INT DEFAULT 0;\n";
                break;
            case 'year_built':
                echo "ALTER TABLE properties ADD COLUMN year_built INT DEFAULT 0;\n";
                break;
            case 'heating':
                echo "ALTER TABLE properties ADD COLUMN heating VARCHAR(50) DEFAULT NULL;\n";
                break;
            case 'main_image':
                echo "ALTER TABLE properties ADD COLUMN main_image VARCHAR(255) DEFAULT NULL;\n";
                break;
            default:
                echo "ALTER TABLE properties ADD COLUMN $column VARCHAR(255) DEFAULT NULL;\n";
        }
    }
    echo "</pre>";
    echo "</div>";
}

echo "<br><a href='add-property.php'>Ana Sayfaya Dön</a>";
?>
