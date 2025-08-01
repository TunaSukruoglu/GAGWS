<?php
// CORRECT DATABASE CONNECTION
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;

echo "<h2>Property Display Test</h2>";

try {
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("Connection failed: " . $conn->connect_error);
    }
    
    // Get first property
    $result = $conn->query("SELECT * FROM properties WHERE id > 0 LIMIT 1");
    $property = $result->fetch_assoc();
    
    if ($property) {
        echo "<h3>Testing Property ID: {$property['id']}</h3>";
        echo "<p><strong>Title:</strong> " . htmlspecialchars($property['title']) . "</p>";
        
        echo "<div style='border: 2px solid #000; padding: 15px; margin: 10px; background: #f9f9f9;'>";
        echo "<h4>Field Display Test:</h4>";
        
        // Usage Status
        echo "<p><strong>Kullanım Durumu:</strong> ";
        $usage_status = $property['usage_status'] ?? '';
        if (!empty($usage_status) && $usage_status !== '0' && $usage_status !== '-' && $usage_status !== 'NULL' && strtolower($usage_status) !== 'null') {
            if ($usage_status === 'Boş') {
                echo 'Boş';
            } elseif ($usage_status === 'Kiracılı') {
                echo 'Kiracılı';
            } elseif ($usage_status === 'Malik Kullanımında') {
                echo 'Malik Kullanımında';
            } elseif ($usage_status === 'Yatırım Amaçlı') {
                echo 'Yatırım Amaçlı';
            } else {
                echo htmlspecialchars($usage_status);
            }
        } else {
            echo 'Belirtilmemiş';
        }
        echo " (Raw: " . htmlspecialchars($usage_status ?: 'NULL') . ")</p>";
        
        // Credit Eligible  
        echo "<p><strong>Krediye Uygunluk:</strong> ";
        $credit_eligible = $property['credit_eligible'] ?? '';
        if (!empty($credit_eligible) && $credit_eligible !== '0' && $credit_eligible !== '-' && $credit_eligible !== 'NULL' && strtolower($credit_eligible) !== 'null') {
            if (in_array($credit_eligible, ['Evet, krediye uygun', 'Hayır, krediye uygun değil'])) {
                echo htmlspecialchars($credit_eligible);
            } elseif ($credit_eligible === 'yes') {
                echo 'Evet, krediye uygun';
            } elseif ($credit_eligible === 'no') {
                echo 'Hayır, krediye uygun değil';
            } else {
                echo htmlspecialchars($credit_eligible);
            }
        } else {
            echo 'Belirtilmemiş';
        }
        echo " (Raw: " . htmlspecialchars($credit_eligible ?: 'NULL') . ")</p>";
        
        // Parking
        echo "<p><strong>Otopark:</strong> ";
        $parking = $property['parking'] ?? '';
        if (!empty($parking) && $parking !== '0' && $parking !== '-' && $parking !== 'NULL' && strtolower($parking) !== 'null') {
            if ($parking === 'Otopark Yok') {
                echo 'Otopark Yok';
            } elseif ($parking === 'Açık Otopark') {
                echo 'Açık Otopark';
            } elseif ($parking === 'Kapalı Otopark') {
                echo 'Kapalı Otopark';
            } else {
                echo htmlspecialchars($parking);
            }
        } else {
            echo 'Belirtilmemiş';
        }
        echo " (Raw: " . htmlspecialchars($parking ?: 'NULL') . ")</p>";
        
        // Furnished
        echo "<p><strong>Eşyalı:</strong> ";
        $furnished = $property['furnished'] ?? '';
        if (!empty($furnished) && $furnished !== '0' && $furnished !== '-' && $furnished !== 'NULL') {
            switch($furnished) {
                case 'furnished': echo 'Eşyalı'; break;
                case 'semi_furnished': echo 'Yarı Eşyalı'; break;
                case 'unfurnished': echo 'Eşyasız'; break;
                case 'yes': echo 'Evet'; break;
                case 'no': echo 'Hayır'; break;
                default: echo htmlspecialchars($furnished);
            }
        } else {
            echo 'Belirtilmemiş';
        }
        echo " (Raw: " . htmlspecialchars($furnished ?: 'NULL') . ")</p>";
        
        echo "</div>";
        
        echo "<p><a href='property-details.php?id={$property['id']}' target='_blank'>View Property Details Page</a></p>";
    }
    
    $conn->close();
    
} catch (Exception $e) {
    echo "<p style='color: red;'>Error: " . $e->getMessage() . "</p>";
}
?>
