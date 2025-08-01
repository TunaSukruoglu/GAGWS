<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

include 'db.php';

$property_id = isset($_GET['id']) ? intval($_GET['id']) : null;

if (!$property_id) {
    die("İlan ID belirtilmedi. Kullanım: debug-property.php?id=55");
}

try {
    $stmt = $conn->prepare("SELECT p.*, u.name as owner_name, u.phone as owner_phone, u.email as owner_email 
                            FROM properties p 
                            LEFT JOIN users u ON p.user_id = u.id 
                            WHERE p.id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $result = $stmt->get_result();
    $property = $result->fetch_assoc();
    
    if (!$property) {
        die("Bu ID ile ilan bulunamadı: $property_id");
    }
    
} catch (Exception $e) {
    die("Veritabanı hatası: " . $e->getMessage());
}

?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>İlan Debug - ID: <?= $property_id ?></title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; background: #f5f5f5; }
        .container { background: white; padding: 20px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1); }
        h1 { color: #333; border-bottom: 2px solid #007bff; padding-bottom: 10px; }
        .field { margin: 10px 0; padding: 8px; border: 1px solid #ddd; border-radius: 4px; }
        .field-name { font-weight: bold; color: #007bff; display: inline-block; width: 200px; }
        .field-value { color: #333; }
        .null-value { color: #999; font-style: italic; }
        .zero-value { color: #ff6b6b; }
        .good-value { color: #28a745; }
        .back-link { display: inline-block; margin-bottom: 20px; padding: 10px 15px; background: #007bff; color: white; text-decoration: none; border-radius: 4px; }
        .back-link:hover { background: #0056b3; color: white; }
    </style>
</head>
<body>
    <div class="container">
        <a href="property-details.php?id=<?= $property_id ?>" class="back-link">← İlan Detayına Dön</a>
        
        <h1>İlan Database Debug - ID: <?= $property_id ?></h1>
        
        <div class="field">
            <span class="field-name">Başlık:</span>
            <span class="field-value <?= !empty($property['title']) ? 'good-value' : 'null-value' ?>"><?= htmlspecialchars($property['title'] ?? 'NULL') ?></span>
        </div>

        <?php 
        $important_fields = [
            'id' => 'İlan ID',
            'title' => 'Başlık',
            'description' => 'Açıklama',
            'price' => 'Fiyat',
            'type' => 'İlan Tipi',
            'category' => 'Kategori',
            'subcategory' => 'Alt Kategori',
            'area' => 'Alan (Eski)',
            'area_gross' => 'Brüt Alan',
            'area_net' => 'Net Alan',
            'room_count' => 'Oda Sayısı',
            'salon_count' => 'Salon Sayısı',
            'bedrooms' => 'Yatak Odası',
            'bathrooms' => 'Banyo',
            'floor' => 'Bulunduğu Kat',
            'floor_located' => 'Kat (Yeni)',
            'building_floors' => 'Bina Kat Sayısı',
            'building_age' => 'Bina Yaşı',
            'parking' => 'Otopark',
            'furnished' => 'Eşyalı',
            'elevator' => 'Asansör',
            'heating' => 'Isıtma',
            'usage_status' => 'Kullanım Durumu',
            'credit_eligible' => 'Krediye Uygunluk',
            'deed_status' => 'Tapu Durumu',
            'exchange' => 'Takas',
            'dues' => 'Aidat',
            'location' => 'Konum',
            'il' => 'İl',
            'ilce' => 'İlçe',
            'mahalle' => 'Mahalle',
            'images' => 'Resimler',
            'features' => 'Özellikler',
            'created_at' => 'Oluşturma Tarihi',
            'updated_at' => 'Güncellenme Tarihi',
            'user_id' => 'Kullanıcı ID',
            'owner_name' => 'Sahip Adı',
            'owner_phone' => 'Sahip Telefon',
            'owner_email' => 'Sahip Email'
        ];
        
        foreach ($important_fields as $field => $label):
            $value = $property[$field] ?? null;
            $class = 'field-value';
            
            if (is_null($value) || $value === '' || $value === 'NULL') {
                $class .= ' null-value';
                $displayValue = 'NULL/Boş';
            } elseif ($value === '0' || $value === '0.00') {
                $class .= ' zero-value';
                $displayValue = $value . ' (Sıfır)';
            } else {
                $class .= ' good-value';
                $displayValue = is_string($value) && strlen($value) > 100 ? 
                    htmlspecialchars(substr($value, 0, 100)) . '...' : 
                    htmlspecialchars($value);
            }
        ?>
            <div class="field">
                <span class="field-name"><?= $label ?>:</span>
                <span class="<?= $class ?>"><?= $displayValue ?></span>
            </div>
        <?php endforeach; ?>

        <h2>Tüm Database Alanları</h2>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px; font-family: monospace; white-space: pre-wrap;"><?php
        foreach ($property as $key => $value) {
            echo sprintf("%-20s: %s\n", $key, var_export($value, true));
        }
        ?></div>
        
        <?php if (!empty($property['features'])): ?>
        <h2>Özellikler (JSON)</h2>
        <div style="background: #f8f9fa; padding: 15px; border-radius: 4px;">
            <pre><?= htmlspecialchars(json_encode(json_decode($property['features'], true), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE)) ?></pre>
        </div>
        <?php endif; ?>
    </div>
</body>
</html>
