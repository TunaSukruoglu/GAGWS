<?php
// Debug page for admin-blog-add-new.php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Admin Blog Add New - Debug Test</h2>";

// 1. Database connection test
echo "<h3>1. Database Connection Test</h3>";
try {
    include '../db.php';
    if (isset($conn) && $conn) {
        echo "✅ Database connection successful<br>";
        
        // Test table existence
        $tables = ['blogs', 'blog_categories', 'blog_tags', 'blog_category_relations', 'blog_tag_relations'];
        foreach ($tables as $table) {
            $result = $conn->query("SHOW TABLES LIKE '$table'");
            if ($result && $result->num_rows > 0) {
                echo "✅ Table '$table' exists<br>";
            } else {
                echo "❌ Table '$table' missing<br>";
            }
        }
    } else {
        echo "❌ Database connection failed<br>";
    }
} catch (Exception $e) {
    echo "❌ Database error: " . $e->getMessage() . "<br>";
}

// 2. Session test
echo "<h3>2. Session Test</h3>";
session_start();
if (isset($_SESSION['user_id'])) {
    echo "✅ User logged in: " . ($_SESSION['user_name'] ?? 'Unknown') . "<br>";
    echo "✅ User role: " . ($_SESSION['role'] ?? 'Unknown') . "<br>";
} else {
    echo "❌ User not logged in<br>";
}

// 3. File includes test
echo "<h3>3. File Includes Test</h3>";
if (file_exists('includes/sidebar-admin.php')) {
    echo "✅ sidebar-admin.php exists<br>";
} else {
    echo "❌ sidebar-admin.php missing<br>";
}

if (file_exists('../assets/dashboard-style.css')) {
    echo "✅ dashboard-style.css exists<br>";
} else {
    echo "❌ dashboard-style.css missing<br>";
}

// 4. Test categories and tags
echo "<h3>4. Categories and Tags Test</h3>";
if (isset($conn)) {
    $categories = $conn->query("SELECT COUNT(*) as count FROM blog_categories");
    if ($categories) {
        $cat_count = $categories->fetch_assoc()['count'];
        echo "✅ Categories found: $cat_count<br>";
    }
    
    $tags = $conn->query("SELECT COUNT(*) as count FROM blog_tags");
    if ($tags) {
        $tag_count = $tags->fetch_assoc()['count'];
        echo "✅ Tags found: $tag_count<br>";
    }
}

echo "<h3>5. Direct Page Test</h3>";
echo '<a href="admin-blog-add-new.php" target="_blank">Test Admin Blog Add New Page</a><br>';
echo '<a href="dashboard-admin.php" target="_blank">Test Dashboard Admin Page</a><br>';
?>
