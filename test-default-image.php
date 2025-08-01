<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Default Image Test</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="css/default-image-system.css">
    <style>
        .test-card {
            max-width: 400px;
            margin: 20px auto;
            border: 1px solid #ddd;
            border-radius: 12px;
            overflow: hidden;
            box-shadow: 0 4px 15px rgba(0,0,0,0.1);
        }
        .test-image {
            width: 100%;
            height: 250px;
            object-fit: cover;
        }
        .test-info {
            padding: 20px;
        }
    </style>
</head>
<body>
    <div class="container my-5">
        <h1 class="text-center mb-4">Default Image System Test</h1>
        
        <div class="row">
            <div class="col-md-6">
                <div class="test-card">
                    <div class="property-image-container">
                        <img src="images/default.png" alt="Default Property Image" class="test-image">
                        <div class="default-image-overlay"></div>
                    </div>
                    <div class="test-info">
                        <h5>Default Image Test</h5>
                        <p class="text-muted">Bu kart default.png dosyasını kullanıyor</p>
                    </div>
                </div>
            </div>
            
            <div class="col-md-6">
                <div class="test-card">
                    <div class="property-image-container">
                        <img src="images/nonexistent.jpg" alt="Missing Image" class="test-image property-image-error">
                        <div class="default-image-overlay"></div>
                    </div>
                    <div class="test-info">
                        <h5>Missing Image Test</h5>
                        <p class="text-muted">Bu kart olmayan bir resim dosyasını test ediyor</p>
                    </div>
                </div>
            </div>
        </div>
        
        <?php
        // Common functions test
        include 'includes/common-functions.php';
        
        echo "<div class='mt-5'>";
        echo "<h3>getImagePath Function Tests:</h3>";
        
        // Test 1: Boş string
        $test1 = getImagePath('');
        echo "<p><strong>Empty string:</strong> " . htmlspecialchars($test1) . "</p>";
        
        // Test 2: Null
        $test2 = getImagePath(null);
        echo "<p><strong>Null value:</strong> " . htmlspecialchars($test2) . "</p>";
        
        // Test 3: Invalid JSON
        $test3 = getImagePath('invalid_json');
        echo "<p><strong>Invalid JSON:</strong> " . htmlspecialchars($test3) . "</p>";
        
        // Test 4: Empty array JSON
        $test4 = getImagePath('[]');
        echo "<p><strong>Empty array JSON:</strong> " . htmlspecialchars($test4) . "</p>";
        
        // Test 5: Valid JSON with non-existent file
        $test5 = getImagePath('["nonexistent.jpg"]');
        echo "<p><strong>Non-existent file in JSON:</strong> " . htmlspecialchars($test5) . "</p>";
        
        echo "</div>";
        ?>
        
        <div class="text-center mt-4">
            <a href="index.php" class="btn btn-primary">Ana Sayfaya Dön</a>
        </div>
    </div>
    
    <script>
        // Image error handling
        document.querySelectorAll('img').forEach(img => {
            img.onerror = function() {
                this.src = 'images/default.png';
                this.classList.add('fallback-used');
            };
        });
    </script>
</body>
</html>
