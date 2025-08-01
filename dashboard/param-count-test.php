<?php
// Simplified parameter count test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>🧪 Parameter Count Debug</h2>";

try {
    require_once 'db.php';
    
    // Test query
    $query = "INSERT INTO properties SET 
        user_id = ?,
        title = ?,
        description = ?,
        price = ?,
        type = ?,
        category = ?,
        listing_type = ?,
        area_gross = ?,
        area_net = ?,
        area = ?,
        address = ?,
        city = ?,
        district = ?,
        room_count = ?,
        bedrooms = ?,
        bathrooms = ?,
        floor = ?,
        year_built = ?,
        heating = ?,
        elevator = ?,
        parking = ?,
        furnished = ?,
        featured = ?,
        images = ?,
        main_image = ?,
        status = 'active',
        created_at = NOW()";
    
    // Count parameters
    $param_count = substr_count($query, '?');
    echo "<p><strong>Parameters in query:</strong> $param_count</p>";
    
    // Test bind string - CORRECTED
    $bind_string = "issdsssdddsssiiiisssiiiss";
    $bind_length = strlen($bind_string);
    echo "<p><strong>Bind string length:</strong> $bind_length</p>";
    echo "<p><strong>Bind string:</strong> $bind_string</p>";
    
    // Let's count each parameter type manually:
    echo "<h3>Parameter Type Analysis:</h3>";
    echo "<ol>";
    echo "<li>user_id (i)</li>";
    echo "<li>title (s)</li>";
    echo "<li>description (s)</li>";
    echo "<li>price (d)</li>";
    echo "<li>type (s)</li>";
    echo "<li>category (s)</li>";
    echo "<li>listing_type (s)</li>";
    echo "<li>area_gross (d)</li>";
    echo "<li>area_net (d)</li>";
    echo "<li>area (d)</li>";
    echo "<li>address (s)</li>";
    echo "<li>city (s)</li>";
    echo "<li>district (s)</li>";
    echo "<li>room_count (s)</li>";
    echo "<li>bedrooms (i)</li>";
    echo "<li>bathrooms (i)</li>";
    echo "<li>floor (i)</li>";
    echo "<li>year_built (i)</li>";
    echo "<li>heating (s)</li>";
    echo "<li>elevator (s)</li>";
    echo "<li>parking (s)</li>";
    echo "<li>furnished (i)</li>";
    echo "<li>featured (i)</li>";
    echo "<li>images (s)</li>";
    echo "<li>main_image (s)</li>";
    echo "</ol>";
    
    // Correct bind string should be:
    $correct_bind = "issdsssdddsssiiiisssiiiss";
    echo "<p><strong>Correct bind string:</strong> $correct_bind</p>";
    echo "<p><strong>Correct length:</strong> " . strlen($correct_bind) . "</p>";
    
    $bind_string = $correct_bind;
    
    if ($param_count === $bind_length) {
        echo "<div style='background: #d4edda; padding: 10px; margin: 10px 0;'>";
        echo "✅ Parameter count matches!";
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 10px; margin: 10px 0;'>";
        echo "❌ Parameter mismatch: Query has $param_count parameters, bind string has $bind_length characters";
        echo "</div>";
    }
    
    // Test variables
    $user_id = 1;
    $title = 'Test';
    $description = 'Test desc';
    $price = 500000.0;
    $type = 'sale';
    $category = 'apartment';
    $listing_type = 'Satılık';
    $area_gross = 120.0;
    $area_net = 100.0;
    $area = 120.0;
    $address = 'Test address';
    $city = 'Istanbul';
    $district = 'Besiktas';
    $room_count = '3+1';
    $bedrooms = 3;
    $bathrooms = 2;
    $floor = 5;
    $year_built = 2020;
    $heating = 'central';
    $elevator = 'yes';
    $parking = 'garage';
    $furnished = 1;
    $featured = 1;
    $images_string = '[]'; // Empty JSON array
    $main_image = ''; // Empty string
    
    // Count variables
    $vars = [
        $user_id, $title, $description, $price, $type, $category, 
        $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
        $room_count, $bedrooms, $bathrooms, $floor, $year_built, 
        $heating, $elevator, $parking, $furnished, $featured,
        $images_string, $main_image
    ];
    
    $var_count = count($vars);
    echo "<p><strong>Variable count:</strong> $var_count</p>";
    
    if ($param_count === $var_count && $param_count === $bind_length) {
        echo "<div style='background: #d4edda; padding: 15px; margin: 15px 0;'>";
        echo "<h3>🎉 Perfect Match!</h3>";
        echo "<p>Parameters: $param_count | Bind: $bind_length | Variables: $var_count</p>";
        
        // Test prepare
        $stmt = $conn->prepare($query);
        if ($stmt) {
            echo "<p>✅ SQL prepare successful</p>";
            
            // Test bind
            $bind_result = $stmt->bind_param($bind_string, ...$vars);
            if ($bind_result) {
                echo "<p>✅ Parameter binding successful</p>";
                echo "<p><strong>System is ready!</strong></p>";
            } else {
                echo "<p>❌ Bind failed: " . $stmt->error . "</p>";
            }
        } else {
            echo "<p>❌ Prepare failed: " . $conn->error . "</p>";
        }
        echo "</div>";
    } else {
        echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0;'>";
        echo "<h3>❌ Count Mismatch</h3>";
        echo "<p>Parameters: $param_count | Bind: $bind_length | Variables: $var_count</p>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #f8d7da; padding: 15px; margin: 15px 0;'>";
    echo "<h3>❌ Error</h3>";
    echo "<p>" . $e->getMessage() . "</p>";
    echo "</div>";
}
?>
