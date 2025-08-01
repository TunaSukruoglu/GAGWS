<?php
session_start();
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h1>Admin Blog Add New - Simple Test</h1>";

// Database connection test
echo "<h2>Database Test</h2>";
try {
    include '../db.php';
    echo "Database connection: SUCCESS<br>";
} catch (Exception $e) {
    echo "Database error: " . $e->getMessage() . "<br>";
}

// Session test
echo "<h2>Session Test</h2>";
if (isset($_SESSION['user_id'])) {
    echo "User ID: " . $_SESSION['user_id'] . "<br>";
    echo "User Name: " . ($_SESSION['user_name'] ?? 'Not set') . "<br>";
    echo "User Role: " . ($_SESSION['role'] ?? 'Not set') . "<br>";
} else {
    echo "No session found. You need to login first.<br>";
    echo '<a href="../login.php">Go to Login</a><br>';
}

// Files test
echo "<h2>Files Test</h2>";
echo "Current directory: " . __DIR__ . "<br>";
echo "Sidebar exists: " . (file_exists('includes/sidebar-admin.php') ? 'YES' : 'NO') . "<br>";
echo "CSS exists: " . (file_exists('../assets/dashboard-style.css') ? 'YES' : 'NO') . "<br>";

echo "<h2>Actions</h2>";
echo '<a href="admin-blog-add-new.php">Try Original Admin Blog Add New</a><br>';
echo '<a href="dashboard-admin.php">Back to Dashboard</a><br>';
echo '<a href="debug-blog-admin.php">Full Debug Test</a><br>';
?>
