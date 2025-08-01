<?php
// Form debug helper
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    echo "<h2>Form Debug - POST Data</h2>";
    echo "<h3>All POST Data:</h3>";
    echo "<pre>";
    print_r($_POST);
    echo "</pre>";
    
    echo "<h3>Validation Check:</h3>";
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $category = $_POST['category'] ?? '';
    $price = floatval(str_replace(['.', ','], ['', '.'], $_POST['price'] ?? '0'));
    
    echo "<table border='1' style='border-collapse: collapse; width: 100%;'>";
    echo "<tr><th>Field</th><th>Value</th><th>Is Empty</th><th>Status</th></tr>";
    
    $fields = [
        'title' => $title,
        'description' => $description,
        'type' => $type,
        'category' => $category,
        'price' => $price
    ];
    
    $all_valid = true;
    foreach($fields as $field_name => $field_value) {
        $is_empty = ($field_name === 'price') ? ($field_value <= 0) : empty($field_value);
        $status = $is_empty ? '<span style="color: red;">❌ INVALID</span>' : '<span style="color: green;">✅ VALID</span>';
        if ($is_empty) $all_valid = false;
        
        echo "<tr>";
        echo "<td><strong>{$field_name}</strong></td>";
        echo "<td>" . htmlspecialchars($field_value) . "</td>";
        echo "<td>" . ($is_empty ? 'YES' : 'NO') . "</td>";
        echo "<td>" . $status . "</td>";
        echo "</tr>";
    }
    echo "</table>";
    
    echo "<h3>Overall Validation: " . ($all_valid ? '<span style="color: green;">✅ PASS</span>' : '<span style="color: red;">❌ FAIL</span>') . "</h3>";
    
    if (!$all_valid) {
        echo "<p style='color: red;'>Form validation would fail with: <strong>Lütfen tüm gerekli alanları doldurun.</strong></p>";
    }
    
    exit;
}
?>

<h2>Property Form Debug Tool</h2>
<p>Bu araç form gönderimini debug etmek içindir. Aşağıdaki formu doldurup gönderin:</p>

<form method="POST" style="max-width: 600px;">
    <div style="margin: 10px 0;">
        <label>Title:</label><br>
        <input type="text" name="title" style="width: 100%; padding: 5px;">
    </div>
    
    <div style="margin: 10px 0;">
        <label>Description:</label><br>
        <textarea name="description" style="width: 100%; padding: 5px; height: 80px;"></textarea>
    </div>
    
    <div style="margin: 10px 0;">
        <label>Type:</label><br>
        <select name="type" style="width: 100%; padding: 5px;">
            <option value="">Seçiniz</option>
            <option value="sale">Satılık</option>
            <option value="rent">Kiralık</option>
        </select>
    </div>
    
    <div style="margin: 10px 0;">
        <label>Category:</label><br>
        <select name="category" style="width: 100%; padding: 5px;">
            <option value="">Seçiniz</option>
            <option value="konut">Konut</option>
            <option value="is_yeri">İş Yeri</option>
            <option value="arsa">Arsa</option>
        </select>
    </div>
    
    <div style="margin: 10px 0;">
        <label>Price:</label><br>
        <input type="text" name="price" style="width: 100%; padding: 5px;">
    </div>
    
    <button type="submit" style="padding: 10px 20px; background: #007cba; color: white; border: none; cursor: pointer;">Test Form</button>
</form>
