<?php
session_start();
echo "<h3>Session Debug</h3>";
echo "<p>Session ID: " . session_id() . "</p>";
echo "<p>User ID: " . ($_SESSION['user_id'] ?? 'Not set') . "</p>";
echo "<p>User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "</p>";
echo "<p>User Role: " . ($_SESSION['user_role'] ?? 'Not set') . "</p>";

echo "<h3>All Session Data:</h3>";
echo "<pre>";
print_r($_SESSION);
echo "</pre>";

if (!isset($_SESSION['user_id'])) {
    echo "<p style='color: red;'>❌ User not logged in</p>";
    echo "<a href='../login.php'>Login sayfasına git</a>";
} else {
    echo "<p style='color: green;'>✅ User logged in</p>";
    echo "<a href='add-property.php'>Add Property sayfasına git</a>";
}
?>
