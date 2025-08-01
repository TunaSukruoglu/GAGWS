<?php
// Minimal add-property.php test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "Testing add-property.php...\n\n";

// Try to include the file
try {
    ob_start();
    include 'dashboard/add-property.php';
    $output = ob_get_contents();
    ob_end_clean();
    
    echo "✅ File included successfully\n";
    echo "Output length: " . strlen($output) . " chars\n";
    
} catch (ParseError $e) {
    echo "❌ Parse Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
    echo "File: " . $e->getFile() . "\n";
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "Line: " . $e->getLine() . "\n";
} catch (Exception $e) {
    echo "❌ Exception: " . $e->getMessage() . "\n";
}
?>
