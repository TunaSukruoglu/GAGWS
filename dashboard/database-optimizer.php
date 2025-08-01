<?php
// 🔍 DATABASE INDEX ANALYZER & OPTIMIZER
include __DIR__ . '/../db.php';

echo "🚀 DATABASE PERFORMANCE ANALYZER\n";
echo "================================\n\n";

// Users tablosu indexleri
echo "📊 USERS TABLE INDEXES:\n";
$result = $conn->query("SHOW INDEX FROM users");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-15s | %-15s | %-15s\n", 
        $row['Key_name'], 
        $row['Column_name'], 
        $row['Index_type']
    );
}

echo "\n📊 PROPERTIES TABLE INDEXES:\n";
$result = $conn->query("SHOW INDEX FROM properties");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-15s | %-15s | %-15s\n", 
        $row['Key_name'], 
        $row['Column_name'], 
        $row['Index_type']
    );
}

echo "\n📊 FAVORITES TABLE INDEXES:\n";
$result = $conn->query("SHOW INDEX FROM favorites");
while ($row = $result->fetch_assoc()) {
    echo sprintf("%-15s | %-15s | %-15s\n", 
        $row['Key_name'], 
        $row['Column_name'], 
        $row['Index_type']
    );
}

// Slow query analizi
echo "\n🐌 SLOW QUERY ANALYSIS:\n";
$queries = [
    "SELECT COUNT(*) as total_users FROM users",
    "SELECT COUNT(*) as total_properties FROM properties", 
    "SELECT * FROM users ORDER BY created_at DESC LIMIT 5",
    "SELECT * FROM properties ORDER BY created_at DESC LIMIT 5"
];

foreach ($queries as $query) {
    $start = microtime(true);
    $conn->query($query);
    $time = round((microtime(true) - $start) * 1000, 2);
    echo sprintf("%-60s | %8s ms\n", substr($query, 0, 60), $time);
}

// Önerilen indexler
echo "\n💡 RECOMMENDED INDEXES:\n";
echo "- users.created_at (for ORDER BY)\n";
echo "- users.is_active (for filtering)\n";
echo "- properties.created_at (for ORDER BY)\n";
echo "- properties.status (for filtering)\n";
echo "- properties.is_active (for filtering)\n";
echo "- favorites.created_at (for ORDER BY)\n";

// Index oluşturma komutları
echo "\n🛠️ INDEX CREATION COMMANDS:\n";
$indexCommands = [
    "CREATE INDEX idx_users_created_at ON users(created_at);",
    "CREATE INDEX idx_users_is_active ON users(is_active);", 
    "CREATE INDEX idx_properties_created_at ON properties(created_at);",
    "CREATE INDEX idx_properties_status ON properties(status);",
    "CREATE INDEX idx_properties_is_active ON properties(is_active);",
    "CREATE INDEX idx_favorites_created_at ON favorites(created_at);",
    "CREATE INDEX idx_properties_user_id ON properties(user_id);"
];

foreach ($indexCommands as $cmd) {
    echo $cmd . "\n";
    
    try {
        $conn->query($cmd);
        echo "✅ Created successfully\n";
    } catch (Exception $e) {
        echo "⚠️ " . $e->getMessage() . "\n";
    }
    echo "\n";
}

echo "🎯 DATABASE OPTIMIZATION COMPLETE!\n";
?>
