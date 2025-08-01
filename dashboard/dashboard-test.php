<?php
// Error tracking için
error_reporting(E_ALL);
ini_set('display_errors', 1);
ini_set('log_errors', 1);

// Session start
if (session_status() === PHP_SESSION_NONE) {
    session_start();
}

// Fake user session for testing
$_SESSION['user_id'] = 1;
$_SESSION['user_role'] = 'admin';

// Include database
include '../db.php';

echo "Dashboard Add Property Test\n";
echo "==========================\n\n";

// Simulate POST data from add-property form
$_POST = [
    'csrf_token' => 'test_token',
    'title' => 'Test Dashboard İlan',
    'description' => 'Dashboard test açıklaması',
    'type' => 'sale',
    'category' => 'apartment',
    'subcategory' => 'daire',
    'price' => '850000',
    'area_gross' => '130',
    'area_net' => '110',
    'city' => 'İstanbul',
    'district' => 'Beşiktaş',
    'neighborhood' => 'Levent',
    'address_details' => 'Test Mahallesi',
    'room_count' => '3',
    'bedrooms' => '2',
    'living_room_count' => '1',
    'bathroom_count' => '2',
    'floor_location' => '7',
    'building_floors' => '12',
    'building_age' => '3',
    'heating' => 'Kombi (Dogalgaz)',
    'elevator' => 'Var',
    'parking' => 'Acik Otopark',
    'furnished' => '1',
    'usage_status' => 'Bos',  // Problem alan
    'dues' => '1200',
    'credit_eligible' => '1',
    'deed_status' => 'Kat Mulkiyeti',
    'exchange' => 'Hayir',
    'location_type' => 'standalone',
    'is_featured' => '0'
];

echo "Test POST data:\n";
echo "usage_status: '" . $_POST['usage_status'] . "'\n";
echo "parking: '" . $_POST['parking'] . "'\n";
echo "heating: '" . $_POST['heating'] . "'\n\n";

// CSRF Manager Mock
class CSRFTokenManager {
    public static function validateToken($token) {
        return true; // Mock validation
    }
}

try {
    // add-property.php logic simulation
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!CSRFTokenManager::validateToken($csrf_token)) {
        throw new Exception("CSRF token validation failed");
    }

    // Form verilerini al
    $user_id = 1;
    $title = trim($_POST['title'] ?? '');
    $description = trim($_POST['description'] ?? '');
    $type = $_POST['type'] ?? '';
    $category = 'apartment';
    $subcategory = trim($_POST['subcategory'] ?? '');
    
    $listing_type = ($type === 'rent') ? 'Kiralık' : 'Satılık';
    $price = floatval(str_replace(['.', ','], ['', '.'], $_POST['price'] ?? '0'));
    
    $area_gross = floatval($_POST['area_gross'] ?? 0);
    $area_net = floatval($_POST['area_net'] ?? 0);
    $area = $area_gross > 0 ? $area_gross : ($area_net > 0 ? $area_net : 50);
    
    $room_count = intval($_POST['room_count'] ?? 1);
    $bedrooms = intval($_POST['bedrooms'] ?? 1);
    $living_room_count = intval($_POST['living_room_count'] ?? 1);
    $bathrooms = intval($_POST['bathroom_count'] ?? 1);
    
    $floor = trim($_POST['floor_location'] ?? 'Zemin Kat');
    $building_floors = intval($_POST['building_floors'] ?? 1);
    $building_age = trim($_POST['building_age'] ?? '0');
    $year_built = is_numeric($building_age) ? (date('Y') - intval($building_age)) : intval(date('Y'));
    
    $city = trim($_POST['city'] ?? 'İstanbul');
    $district = trim($_POST['district'] ?? '');
    $address = trim($_POST['address_details'] ?? 'Adres bilgisi girilmemiş');
    
    // ENUM mappings
    $heating_input = trim($_POST['heating'] ?? '');
    $heating_mapping = [
        'Doğalgaz Sobası' => 'Dogalgaz Sobasi',
        'Yerden Isıtma' => 'Yerden Isitma',
        'Güneş Enerjisi' => 'Gunes Enerjisi',
        'Şömine' => 'Somine'
    ];
    $heating = isset($heating_mapping[$heating_input]) ? $heating_mapping[$heating_input] : $heating_input;
    if (empty($heating)) $heating = 'Yok';

    $elevator = trim($_POST['elevator'] ?? 'Yok');

    $parking_input = trim($_POST['parking'] ?? '');
    $parking_mapping = [
        'Açık Otopark' => 'Acik Otopark',
        'Kapalı Otopark' => 'Kapali Otopark'
    ];
    $parking = isset($parking_mapping[$parking_input]) ? $parking_mapping[$parking_input] : $parking_input;
    if (empty($parking)) $parking = 'Otopark Yok';

    $furnished = isset($_POST['furnished']) ? 1 : 0;

    $usage_status_input = trim($_POST['usage_status'] ?? '');
    $usage_mapping = [
        'Boş' => 'Bos',
        'Bos' => 'Bos', 
        'Kiracılı' => 'Kiracili',
        'Kiracili' => 'Kiracili',
        'Malik Kullanımında' => 'Malik Kullaniminda',
        'Malik Kullaniminda' => 'Malik Kullaniminda',
        'Yatırım Amaçlı' => 'Yatirim Amacli',
        'Yatirim Amacli' => 'Yatirim Amacli'
    ];
    $usage_status = isset($usage_mapping[$usage_status_input]) ? $usage_mapping[$usage_status_input] : 'Bos';

    $dues = floatval($_POST['dues'] ?? 0);
    $credit_eligible = isset($_POST['credit_eligible']) ? 1 : 0;

    $deed_status_input = trim($_POST['deed_status'] ?? '');
    $deed_mapping = [
        'Kat İrtifakı' => 'Kat Irtifaki',
        'Arsa Payı' => 'Arsa Payi'
    ];
    $deed_status = isset($deed_mapping[$deed_status_input]) ? $deed_mapping[$deed_status_input] : $deed_status_input;
    if (empty($deed_status)) $deed_status = 'Kat Mulkiyeti';

    $exchange_input = trim($_POST['exchange'] ?? '');
    $exchange = ($exchange_input === 'Hayır') ? 'Hayir' : $exchange_input;
    if (empty($exchange)) $exchange = 'Hayir';

    $location_type = trim($_POST['location_type'] ?? 'standalone');
    $featured = 0;

    $images_string = '[]';
    $main_image = '';
    $features_json = '{"ic_ozellikler": [], "dis_ozellikler": []}';

    echo "Mapped ENUM values:\n";
    echo "usage_status: '$usage_status'\n";
    echo "parking: '$parking'\n";
    echo "heating: '$heating'\n";
    echo "elevator: '$elevator'\n";
    echo "deed_status: '$deed_status'\n";
    echo "exchange: '$exchange'\n\n";

    // INSERT query - exactly like add-property.php
    $query = "INSERT INTO properties SET 
        user_id = ?,
        title = ?,
        description = ?,
        price = ?,
        type = ?,
        category = ?,
        subcategory = ?,
        listing_type = ?,
        area_gross = ?,
        area_net = ?,
        area = ?,
        address = ?,
        city = ?,
        district = ?,
        room_count = ?,
        bedrooms = ?,
        living_room_count = ?,
        bathrooms = ?,
        floor = ?,
        building_floors = ?,
        year_built = ?,
        building_age = ?,
        heating = ?,
        elevator = ?,
        parking = ?,
        furnished = ?,
        usage_status = ?,
        dues = ?,
        credit_eligible = ?,
        deed_status = ?,
        exchange = ?,
        location_type = ?,
        featured = ?,
        images = ?,
        main_image = ?,
        features = ?,
        status = 'active',
        created_at = NOW()";

    $stmt = $conn->prepare($query);
    
    if (!$stmt) {
        throw new Exception("Prepare failed: " . $conn->error);
    }

    // Fixed bind_param - 36 parameters
    $bind_result = $stmt->bind_param("issdssssdddsssiiiiiiissssisssssissss", 
        $user_id, $title, $description, $price, $type, $category, $subcategory,
        $listing_type, $area_gross, $area_net, $area, $address, $city, $district, 
        $room_count, $bedrooms, $living_room_count, $bathrooms, $floor, $building_floors, $year_built, $building_age,
        $heating, $elevator, $parking, $furnished, $usage_status, $dues, $credit_eligible,
        $deed_status, $exchange, $location_type, $featured,
        $images_string, $main_image, $features_json);
        
    if (!$bind_result) {
        throw new Exception("Bind param failed: " . $stmt->error);
    }

    if ($stmt->execute()) {
        $property_id = $conn->insert_id;
        echo "✓ SUCCESS: Dashboard test ilan başarıyla eklendi! (ID: $property_id)\n\n";
        
        // Verify saved data
        $check_sql = "SELECT usage_status, parking, heating, elevator, deed_status, exchange, listing_type FROM properties WHERE id = ?";
        $check_stmt = $conn->prepare($check_sql);
        $check_stmt->bind_param("i", $property_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        $row = $result->fetch_assoc();
        
        echo "Database'de kaydedilen değerler:\n";
        foreach ($row as $field => $value) {
            $display = empty($value) ? '[EMPTY]' : "'$value'";
            echo "  $field: $display\n";
        }
        
    } else {
        echo "✗ INSERT ERROR: " . $stmt->error . "\n";
    }

} catch (Exception $e) {
    echo "✗ EXCEPTION: " . $e->getMessage() . "\n";
}
?>
