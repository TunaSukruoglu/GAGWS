<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

try {
    include 'db.php';
    
    // Properties tablosunun tüm verilerini çek
    $query = "SELECT * FROM properties ORDER BY id DESC LIMIT 10";
    $result = $conn->query($query);
    
    echo "<h2>Properties Tablosu - Son 10 Kayıt</h2>";
    echo "<style>
        table { border-collapse: collapse; width: 100%; font-size: 12px; }
        th, td { border: 1px solid #ddd; padding: 5px; text-align: left; }
        th { background-color: #f2f2f2; position: sticky; top: 0; }
        .null { color: red; font-style: italic; }
        .value { color: green; }
        .highlight { background-color: yellow; }
    </style>";
    
    if ($result && $result->num_rows > 0) {
        echo "<table>";
        
        // Başlıkları yazdır
        echo "<tr>";
        $first_row = $result->fetch_assoc();
        foreach($first_row as $key => $value) {
            echo "<th>$key</th>";
        }
        echo "</tr>";
        
        // İlk satırı yazdır
        echo "<tr>";
        foreach($first_row as $key => $value) {
            $class = '';
            $display_value = $value;
            
            if (is_null($value) || $value === '' || $value === 'NULL') {
                $class = 'null';
                $display_value = 'NULL';
            } else {
                $class = 'value';
                if (in_array($key, ['parking', 'elevator', 'usage_status', 'building_floors', 'credit_eligible', 'deed_status', 'exchange'])) {
                    $class .= ' highlight';
                }
            }
            
            echo "<td class='$class'>" . htmlspecialchars($display_value) . "</td>";
        }
        echo "</tr>";
        
        // Diğer satırları yazdır
        while($row = $result->fetch_assoc()) {
            echo "<tr>";
            foreach($row as $key => $value) {
                $class = '';
                $display_value = $value;
                
                if (is_null($value) || $value === '' || $value === 'NULL') {
                    $class = 'null';
                    $display_value = 'NULL';
                } else {
                    $class = 'value';
                    if (in_array($key, ['parking', 'elevator', 'usage_status', 'building_floors', 'credit_eligible', 'deed_status', 'exchange'])) {
                        $class .= ' highlight';
                    }
                }
                
                echo "<td class='$class'>" . htmlspecialchars($display_value) . "</td>";
            }
            echo "</tr>";
        }
        echo "</table>";
    } else {
        echo "<p>Veri bulunamadı</p>";
    }
    
    // Belirli bir ID'nin detaylarını göster
    if (isset($_GET['id'])) {
        $id = intval($_GET['id']);
        echo "<h3>ID $id Detay Görünümü:</h3>";
        
        $detail_query = "SELECT * FROM properties WHERE id = $id";
        $detail_result = $conn->query($detail_query);
        
        if ($detail_result && $detail_result->num_rows > 0) {
            $detail_data = $detail_result->fetch_assoc();
            echo "<table>";
            echo "<tr><th>Alan Adı</th><th>Değer</th><th>Durum</th></tr>";
            
            foreach($detail_data as $key => $value) {
                $status = '';
                $color = '';
                
                if (is_null($value) || $value === '' || $value === 'NULL') {
                    $status = 'NULL/EMPTY';
                    $color = 'red';
                    $display_value = 'NULL';
                } else {
                    $status = 'HAS VALUE';
                    $color = 'green';
                    $display_value = htmlspecialchars($value);
                }
                
                $highlight = in_array($key, ['parking', 'elevator', 'usage_status', 'building_floors', 'credit_eligible', 'deed_status', 'exchange']) ? 'background-color: yellow;' : '';
                
                echo "<tr style='$highlight'>";
                echo "<td><strong>$key</strong></td>";
                echo "<td style='color: $color;'>$display_value</td>";
                echo "<td style='color: $color;'>$status</td>";
                echo "</tr>";
            }
            echo "</table>";
        }
    }
    
    // Özet bilgileri
    echo "<h3>Özet İstatistikler:</h3>";
    echo "<ul>";
    
    $summary_fields = ['parking', 'elevator', 'usage_status', 'building_floors', 'credit_eligible', 'deed_status', 'exchange'];
    
    foreach($summary_fields as $field) {
        $count_query = "SELECT 
            COUNT(*) as total,
            COUNT($field) as not_null_count,
            COUNT(*) - COUNT($field) as null_count
            FROM properties";
        
        $count_result = $conn->query($count_query);
        if ($count_result) {
            $count_data = $count_result->fetch_assoc();
            echo "<li><strong>$field:</strong> Toplam: {$count_data['total']}, Dolu: {$count_data['not_null_count']}, Boş: {$count_data['null_count']}</li>";
        }
    }
    echo "</ul>";
    
    // Hızlı linkler
    echo "<h3>Hızlı Erişim:</h3>";
    echo "<p>";
    echo "<a href='?id=40'>ID 40 Detay</a> | ";
    echo "<a href='?id=39'>ID 39 Detay</a> | ";
    echo "<a href='?id=38'>ID 38 Detay</a> | ";
    echo "<a href='?'>Tüm Liste</a>";
    echo "</p>";
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Hata: " . $e->getMessage() . "</p>";
}
?>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Tabloyu scroll edilebilir yap
    const table = document.querySelector('table');
    if (table) {
        table.style.display = 'block';
        table.style.overflow = 'auto';
        table.style.whiteSpace = 'nowrap';
        table.style.maxHeight = '500px';
    }
});
</script>
