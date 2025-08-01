<?php
// 📊 DASHBOARD PERFORMANCE COMPARATOR
echo "🚀 DASHBOARD PERFORMANCE COMPARISON\n";
echo "=====================================\n\n";

// Test URLs
$tests = [
    'Minimal Dashboard' => '/dashboard/dashboard-minimal.php',
    'Optimized Dashboard' => '/dashboard/dashboard-admin.php',
];

$results = [];

foreach ($tests as $name => $url) {
    echo "Testing $name...\n";
    
    // Server-side PHP execution time
    $start = microtime(true);
    ob_start();
    
    // Session setup for admin
    session_start();
    $_SESSION['user_id'] = 13; // Admin user
    
    // Include and execute
    if (file_exists(__DIR__ . $url)) {
        include __DIR__ . $url;
    }
    
    $output = ob_get_clean();
    $serverTime = round((microtime(true) - $start) * 1000, 2);
    
    // File size
    $fileSize = file_exists(__DIR__ . $url) ? filesize(__DIR__ . $url) : 0;
    
    $results[$name] = [
        'server_time' => $serverTime,
        'file_size' => $fileSize,
        'output_size' => strlen($output)
    ];
    
    echo sprintf("  Server Time: %sms\n", $serverTime);
    echo sprintf("  File Size: %s KB\n", round($fileSize/1024, 2));
    echo sprintf("  Output Size: %s KB\n", round(strlen($output)/1024, 2));
    echo "\n";
}

// Comparison
echo "📈 PERFORMANCE COMPARISON:\n";
echo "==========================\n";

$minimal = $results['Minimal Dashboard'];
$optimized = $results['Optimized Dashboard'];

$speedImprovement = round((($optimized['server_time'] - $minimal['server_time']) / $optimized['server_time']) * 100, 1);
$sizeReduction = round((($optimized['file_size'] - $minimal['file_size']) / $optimized['file_size']) * 100, 1);

echo sprintf("Speed Improvement: %s%% faster\n", $speedImprovement > 0 ? $speedImprovement : 'No improvement');
echo sprintf("File Size Reduction: %s%% smaller\n", $sizeReduction > 0 ? $sizeReduction : 'No reduction');
echo sprintf("Output Size Reduction: %s%% smaller\n", round((($optimized['output_size'] - $minimal['output_size']) / $optimized['output_size']) * 100, 1));

echo "\n🎯 RECOMMENDATION:\n";
if ($speedImprovement > 50) {
    echo "✅ Use Minimal Dashboard for fastest experience\n";
    echo "📊 Switch to Full Dashboard when detailed data needed\n";
} else {
    echo "⚠️ Minimal performance gain - stick with optimized dashboard\n";
}

echo "\n🔗 ACCESS LINKS:\n";
echo "Minimal Dashboard: https://gokhanaydinli.com/dashboard/dashboard-minimal.php\n";
echo "Full Dashboard: https://gokhanaydinli.com/dashboard/dashboard-admin.php\n";
?>
