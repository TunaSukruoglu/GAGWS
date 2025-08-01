<?php
/**
 * Local Upload Test
 * Test local image upload functionality
 */

require_once 'includes/cloudflare-images-config.php';

echo "<h2>Local Upload Configuration Test</h2>";

echo "<h3>Configuration Status:</h3>";
echo "USE_CLOUDFLARE_IMAGES: " . (defined('USE_CLOUDFLARE_IMAGES') ? (USE_CLOUDFLARE_IMAGES ? 'TRUE' : 'FALSE') : 'NOT DEFINED') . "<br>";
echo "USE_LOCAL_UPLOAD: " . (defined('USE_LOCAL_UPLOAD') ? (USE_LOCAL_UPLOAD ? 'TRUE' : 'FALSE') : 'NOT DEFINED') . "<br>";
echo "MAX_UPLOAD_SIZE: " . (defined('MAX_UPLOAD_SIZE') ? number_format(MAX_UPLOAD_SIZE / (1024*1024), 1) . ' MB' : 'NOT DEFINED') . "<br>";

echo "<h3>Directory Status:</h3>";
$uploadDir = __DIR__ . '/uploads/properties/';
echo "Upload Directory: " . $uploadDir . "<br>";
echo "Directory Exists: " . (file_exists($uploadDir) ? 'YES' : 'NO') . "<br>";
echo "Directory Writable: " . (is_writable($uploadDir) ? 'YES' : 'NO') . "<br>";

if (file_exists($uploadDir)) {
    $files = scandir($uploadDir);
    $fileCount = count($files) - 2; // Exclude . and ..
    echo "Existing Files: " . $fileCount . "<br>";
    
    if ($fileCount > 0) {
        echo "<h4>Recent Files:</h4>";
        foreach (array_slice($files, -5) as $file) {
            if ($file !== '.' && $file !== '..') {
                $filePath = $uploadDir . $file;
                $size = filesize($filePath);
                echo "- " . $file . " (" . number_format($size / 1024, 1) . " KB)<br>";
            }
        }
    }
}

echo "<h3>Upload Form Test:</h3>";
?>

<form action="" method="post" enctype="multipart/form-data">
    <p>Test Image Upload:</p>
    <input type="file" name="test_image" accept="image/*" multiple>
    <button type="submit" name="test_upload">Test Upload</button>
</form>

<?php
if (isset($_POST['test_upload']) && isset($_FILES['test_image'])) {
    echo "<h3>Upload Test Results:</h3>";
    
    $file = $_FILES['test_image'];
    echo "Original Name: " . $file['name'] . "<br>";
    echo "Size: " . number_format($file['size'] / 1024, 1) . " KB<br>";
    echo "Type: " . $file['type'] . "<br>";
    echo "Error Code: " . $file['error'] . "<br>";
    
    if ($file['error'] === UPLOAD_ERR_OK) {
        $uploadDir = __DIR__ . '/uploads/properties/';
        $filename = 'test_' . time() . '_' . uniqid() . '.jpg';
        $targetPath = $uploadDir . $filename;
        
        if (move_uploaded_file($file['tmp_name'], $targetPath)) {
            echo "<strong>✅ UPLOAD SUCCESS!</strong><br>";
            echo "Saved as: " . $filename . "<br>";
            echo "Full Path: " . $targetPath . "<br>";
            echo "Web URL: /uploads/properties/" . $filename . "<br>";
            
            // Display the uploaded image
            echo "<br><img src='/uploads/properties/" . $filename . "' style='max-width: 300px; max-height: 200px;'><br>";
        } else {
            echo "<strong>❌ UPLOAD FAILED!</strong><br>";
            echo "Could not move file to target directory.<br>";
        }
    } else {
        echo "<strong>❌ UPLOAD ERROR!</strong><br>";
        echo "Error code: " . $file['error'] . "<br>";
    }
}
?>

<style>
body { font-family: Arial, sans-serif; margin: 20px; }
h2, h3 { color: #333; }
form { background: #f5f5f5; padding: 15px; border-radius: 5px; margin: 10px 0; }
button { background: #007cba; color: white; padding: 8px 16px; border: none; border-radius: 3px; cursor: pointer; }
button:hover { background: #005a87; }
</style>
