<?php
// Minimal Dashboard Test
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "🔧 Dashboard Quick Test<br>";
echo "PHP Version: " . PHP_VERSION . "<br>";

// Check if this works
if (isset($_GET['test'])) {
    echo "✅ GET parameter works<br>";
    echo "<a href='add-property.php'>Try Dashboard Now</a>";
    exit;
}

echo "<a href='?test=1'>Test PHP Processing</a><br>";
echo "<a href='add-property.php'>Go to Dashboard</a>";
?>
