<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Edit Property Minimal Test</h1>";

try {
    echo "Step 1: Session start...<br>";
    session_start();
    echo "✅ Session started<br>";
    
    echo "Step 2: Include DB...<br>";
    include '../db.php';
    echo "✅ DB included<br>";
    
    echo "Step 3: Check connection...<br>";
    if (!isset($conn)) {
        die("❌ No database connection");
    }
    echo "✅ Database connection exists<br>";
    
    echo "Step 4: Session check...<br>";
    if (!isset($_SESSION['user_id'])) {
        $_SESSION['user_id'] = 1; // Test user
        $_SESSION['role'] = 'admin';
        echo "⚠️ Session created for testing<br>";
    }
    echo "✅ User ID: " . $_SESSION['user_id'] . "<br>";
    
    echo "Step 5: Get property ID...<br>";
    $property_id = isset($_GET['id']) ? intval($_GET['id']) : 38;
    echo "✅ Property ID: $property_id<br>";
    
    echo "Step 6: Database query...<br>";
    $stmt = $conn->prepare("SELECT * FROM properties WHERE id = ?");
    $stmt->bind_param("i", $property_id);
    $stmt->execute();
    $property = $stmt->get_result()->fetch_assoc();
    
    if (!$property) {
        echo "❌ Property not found<br>";
        exit;
    }
    echo "✅ Property found: " . $property['title'] . "<br>";
    
    echo "Step 7: Success! Property data loaded successfully.<br>";
    echo "<pre>" . print_r($property, true) . "</pre>";
    
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "<br>";
    echo "File: " . $e->getFile() . " Line: " . $e->getLine() . "<br>";
}

echo "<hr><a href='edit-property.php?id=$property_id'>Try Real Edit Property Page</a>";
?>
