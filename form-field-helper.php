<?php
// Tüm form alanlarını edit modunda doldurmak için helper script
$fields = [
    'title' => 'text',
    'price' => 'text', 
    'description' => 'textarea',
    'type' => 'select',
    'category' => 'select',
    'listing_type' => 'select',
    'area_gross' => 'text',
    'area_net' => 'text',
    'area' => 'text',
    'address' => 'text',
    'city' => 'text',
    'district' => 'text',
    'room_count' => 'text',
    'bedrooms' => 'text',
    'bathrooms' => 'text',
    'floor' => 'text',
    'year_built' => 'text',
    'heating' => 'select',
    'elevator' => 'select',
    'parking' => 'select',
    'furnished' => 'select',
    'featured' => 'checkbox'
];

echo "// Form alanlarını edit modunda doldur\n";
foreach($fields as $field => $type) {
    if ($type == 'text') {
        echo "value=\"<?php echo htmlspecialchars(\$_POST['$field'] ?? (\$edit_property['$field'] ?? '')); ?>\"\n";
    } elseif ($type == 'textarea') {
        echo "<?php echo htmlspecialchars(\$_POST['$field'] ?? (\$edit_property['$field'] ?? '')); ?>\n";
    } elseif ($type == 'select') {
        echo "<?php echo (\$_POST['$field'] ?? (\$edit_property['$field'] ?? '')) == 'VALUE' ? 'selected' : ''; ?>\n";
    } elseif ($type == 'checkbox') {
        echo "<?php echo (\$_POST['$field'] ?? (\$edit_property['$field'] ?? 0)) ? 'checked' : ''; ?>\n";
    }
    echo "\n";
}
?>
