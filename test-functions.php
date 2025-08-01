<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);

echo "<h2>Function Test Results</h2>";

// Include the functions file
if (file_exists('includes/common-functions.php')) {
    include_once 'includes/common-functions.php';
    echo "<p>✅ common-functions.php included successfully</p>";
} else {
    echo "<p>❌ common-functions.php file not found!</p>";
    exit;
}

// Test each function
echo "<h3>Function Availability Tests:</h3>";

$functions_to_test = [
    'getImagePath',
    'formatPrice', 
    'getCategoryText',
    'truncateText',
    'generateSlug'
];

foreach ($functions_to_test as $func) {
    if (function_exists($func)) {
        echo "<p>✅ Function '$func' exists</p>";
    } else {
        echo "<p>❌ Function '$func' NOT FOUND!</p>";
    }
}

// Test getImagePath function specifically
echo "<h3>getImagePath Function Test:</h3>";
if (function_exists('getImagePath')) {
    try {
        $test_images = 'image1.jpg,image2.png';
        $result = getImagePath($test_images);
        echo "<p>✅ getImagePath test successful: " . htmlspecialchars($result) . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ getImagePath error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>❌ getImagePath function does not exist!</p>";
}

// Test formatPrice function
echo "<h3>formatPrice Function Test:</h3>";
if (function_exists('formatPrice')) {
    try {
        $result = formatPrice(1500000);
        echo "<p>✅ formatPrice test successful: " . htmlspecialchars($result) . "</p>";
    } catch (Exception $e) {
        echo "<p>❌ formatPrice error: " . htmlspecialchars($e->getMessage()) . "</p>";
    }
} else {
    echo "<p>❌ formatPrice function does not exist!</p>";
}

echo "<h3>File Size Check:</h3>";
$filesize = filesize('includes/common-functions.php');
echo "<p>common-functions.php file size: " . $filesize . " bytes</p>";

if ($filesize > 100) {
    echo "<p>✅ File size looks good</p>";
} else {
    echo "<p>❌ File too small, might be corrupted</p>";
}
?>
