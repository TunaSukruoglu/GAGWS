<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

// LOCAL SUNUCU BAĞLANTISI
$servername = "localhost";
$username = "gokhanay_user";
$password = "113041122839sS?!_";
$dbname = "gokhanay_db";
$port = 3306;  // MySQL portu

// Bağlantı timeout ayarları (güvenlik için)
$connection_timeout = 30;

// SSL bağlantısı için (isteğe bağlı güvenlik)
$ssl_options = [
    'ssl' => true,
    'ssl_verify_server_cert' => false
];

try {
    // Netcup sunucusuna bağlantı oluştur (port ile)
    $conn = new mysqli($servername, $username, $password, $dbname, $port);
    
    if ($conn->connect_error) {
        throw new Exception("MySQL bağlantısı başarısız: " . $conn->connect_error);
    }
    
    // ZORUNLU: Character set ayarlarını yap
    $conn->set_charset("utf8mb4");
    
    // ⚡ PDO Bağlantısı da oluştur (modern projeler için)
    $pdo = new PDO(
        "mysql:host=$servername;port=$port;dbname=$dbname;charset=utf8mb4",
        $username,
        $password,
        [
            PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES => false,
            PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
        ]
    );
    
    // Ek charset ayarları (güvenlik için)
    $conn->query("SET NAMES utf8mb4");
    $conn->query("SET CHARACTER SET utf8mb4");
    $conn->query("SET character_set_connection=utf8mb4");
    $conn->query("SET character_set_client=utf8mb4");
    $conn->query("SET character_set_results=utf8mb4");
    $conn->query("SET lc_time_names = 'tr_TR'");
    
    // Veritabanını oluştur (eğer yoksa)
    $sql = "CREATE DATABASE IF NOT EXISTS $dbname CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci";
    if (!$conn->query($sql)) {
        throw new Exception("Veritabanı oluşturulamadı: " . $conn->error);
    }
    
    // Veritabanını seç
    $conn->select_db($dbname);
    
    // Users tablosunu güncelle (yetki sistemi ile)
    $create_users_table = "
    CREATE TABLE IF NOT EXISTS users (
        id INT AUTO_INCREMENT PRIMARY KEY,
        name VARCHAR(100) NOT NULL,
        email VARCHAR(100) UNIQUE NOT NULL,
        password VARCHAR(255) NOT NULL,
        phone VARCHAR(20),
        role ENUM('root', 'admin', 'agent', 'user') DEFAULT 'user',
        can_add_property BOOLEAN DEFAULT FALSE,
        is_active BOOLEAN DEFAULT TRUE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        last_login TIMESTAMP NULL,
        profile_image VARCHAR(255),
        address TEXT,
        bio TEXT
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";

    if (!$conn->query($create_users_table)) {
        throw new Exception("Users tablosu oluşturulamadı: " . $conn->error);
    }
    
    // Role kolonunu güncelleyerek 'root' değerini ekle
    $check_role_enum = $conn->query("SHOW COLUMNS FROM users WHERE Field = 'role'");
    if ($check_role_enum->num_rows > 0) {
        $role_info = $check_role_enum->fetch_assoc();
        if (!strpos($role_info['Type'], 'root')) {
            // Role enum'una root ekle
            $alter_role = "ALTER TABLE users MODIFY COLUMN role ENUM('root', 'admin', 'agent', 'user') DEFAULT 'user'";
            if (!$conn->query($alter_role)) {
                error_log("Role kolonu güncellenemedi: " . $conn->error);
            } else {
                error_log("Role kolonu 'root' değeri ile güncellendi");
            }
        }
    }
    
    // Mevcut Users tablosuna can_add_property kolonu ekle (yoksa)
    $check_column = $conn->query("SHOW COLUMNS FROM users LIKE 'can_add_property'");
    if ($check_column->num_rows == 0) {
        $alter_query = "ALTER TABLE users ADD COLUMN can_add_property BOOLEAN DEFAULT FALSE AFTER role";
        if (!$conn->query($alter_query)) {
            error_log("can_add_property kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("can_add_property kolonu başarıyla eklendi");
        }
    }
    
    // is_active kolonu ekle (yoksa)
    $check_is_active = $conn->query("SHOW COLUMNS FROM users LIKE 'is_active'");
    if ($check_is_active->num_rows == 0) {
        $alter_is_active = "ALTER TABLE users ADD COLUMN is_active BOOLEAN DEFAULT TRUE AFTER can_add_property";
        if (!$conn->query($alter_is_active)) {
            error_log("is_active kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("is_active kolonu başarıyla eklendi");
            // Mevcut kullanıcıları aktif yap
            $conn->query("UPDATE users SET is_active = TRUE WHERE is_active IS NULL");
        }
    }
    
    // last_login kolonu ekle (yoksa)
    $check_last_login = $conn->query("SHOW COLUMNS FROM users LIKE 'last_login'");
    if ($check_last_login->num_rows == 0) {
        $alter_last_login = "ALTER TABLE users ADD COLUMN last_login TIMESTAMP NULL AFTER is_active";
        $conn->query($alter_last_login);
    }
    
    // profile_image kolonu ekle (yoksa)
    $check_profile_image = $conn->query("SHOW COLUMNS FROM users LIKE 'profile_image'");
    if ($check_profile_image->num_rows == 0) {
        $alter_profile_image = "ALTER TABLE users ADD COLUMN profile_image VARCHAR(255) NULL AFTER last_login";
        $conn->query($alter_profile_image);
    }
    
    // address kolonu ekle (yoksa)
    $check_address = $conn->query("SHOW COLUMNS FROM users LIKE 'address'");
    if ($check_address->num_rows == 0) {
        $alter_address = "ALTER TABLE users ADD COLUMN address TEXT NULL AFTER profile_image";
        $conn->query($alter_address);
    }
    
    // bio kolonu ekle (yoksa)
    $check_bio = $conn->query("SHOW COLUMNS FROM users LIKE 'bio'");
    if ($check_bio->num_rows == 0) {
        $alter_bio = "ALTER TABLE users ADD COLUMN bio TEXT NULL AFTER address";
        $conn->query($alter_bio);
    }
    
    // Önce category kolonunu kontrol et ve ekle
    $check_category_early = $conn->query("SHOW COLUMNS FROM properties LIKE 'category'");
    if ($check_category_early->num_rows == 0) {
        $alter_category_early = "ALTER TABLE properties ADD COLUMN category VARCHAR(100) DEFAULT 'Daire' AFTER description";
        if (!$conn->query($alter_category_early)) {
            error_log("Category kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("Category kolonu başarıyla eklendi");
        }
    }

    // Properties tablosundaki listing_type kolonunu kontrol et ve düzelt
    $check_listing_type = $conn->query("SHOW COLUMNS FROM properties LIKE 'listing_type'");
    if ($check_listing_type->num_rows == 0) {
        // listing_type kolonu yoksa ekle
        $conn->query("ALTER TABLE properties ADD COLUMN listing_type ENUM('Satılık', 'Kiralık') DEFAULT 'Satılık' AFTER category");
    }
    
    // Properties tablosundaki type kolonunu kontrol et
    $check_type = $conn->query("SHOW COLUMNS FROM properties LIKE 'type'");
    if ($check_type->num_rows == 0) {
        // type kolonu yoksa listing_type'dan kopyala
        $conn->query("ALTER TABLE properties ADD COLUMN type VARCHAR(50) DEFAULT 'sale' AFTER listing_type");
        $conn->query("UPDATE properties SET type = CASE WHEN listing_type = 'Satılık' THEN 'sale' ELSE 'rent' END");
    }
    
    // Properties tablosunu oluştur
    $create_properties_table = "
    CREATE TABLE IF NOT EXISTS properties (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        title VARCHAR(255) NOT NULL,
        description LONGTEXT,
        category VARCHAR(100) DEFAULT 'Daire',
        listing_type ENUM('Satılık', 'Kiralık') DEFAULT 'Satılık',
        price DECIMAL(15,2) NOT NULL,
        yearly_tax DECIMAL(10,2) DEFAULT 0,
        size_sqft INT,
        bedrooms INT DEFAULT 0,
        bathrooms INT DEFAULT 0,
        kitchens INT DEFAULT 0,
        garages INT DEFAULT 0,
        garage_size_sqft INT DEFAULT 0,
        year_built YEAR,
        floor_number INT DEFAULT 0,
        amenities TEXT,
        address TEXT,
        city VARCHAR(100),
        district VARCHAR(100),
        country VARCHAR(100) DEFAULT 'Türkiye',
        latitude DECIMAL(10, 8),
        longitude DECIMAL(11, 8),
        images TEXT,
        status ENUM('pending', 'approved', 'rejected') DEFAULT 'pending',
        featured BOOLEAN DEFAULT FALSE,
        views INT DEFAULT 0,
        created_by_admin BOOLEAN DEFAULT FALSE,
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";
    
    if (!$conn->query($create_properties_table)) {
        throw new Exception("Properties tablosu oluşturulamadı: " . $conn->error);
    }
    
    // Featured kolonu eksikse ekle
    $check_featured = $conn->query("SHOW COLUMNS FROM properties LIKE 'featured'");
    if ($check_featured->num_rows == 0) {
        $alter_featured = "ALTER TABLE properties ADD COLUMN featured BOOLEAN DEFAULT FALSE AFTER status";
        if (!$conn->query($alter_featured)) {
            error_log("Featured kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("Featured kolonu başarıyla eklendi");
        }
    }
    
    // size_sqft kolonu eksikse ekle
    $check_size_sqft = $conn->query("SHOW COLUMNS FROM properties LIKE 'size_sqft'");
    if ($check_size_sqft->num_rows == 0) {
        $alter_size_sqft = "ALTER TABLE properties ADD COLUMN size_sqft INT DEFAULT 0 AFTER price";
        if (!$conn->query($alter_size_sqft)) {
            error_log("size_sqft kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("size_sqft kolonu başarıyla eklendi");
        }
    }
    
    // city kolonu eksikse ekle
    $check_city = $conn->query("SHOW COLUMNS FROM properties LIKE 'city'");
    if ($check_city->num_rows == 0) {
        $alter_city = "ALTER TABLE properties ADD COLUMN city VARCHAR(100) DEFAULT NULL";
        if (!$conn->query($alter_city)) {
            error_log("city kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("city kolonu başarıyla eklendi");
        }
    }
    
    // district kolonu eksikse ekle
    $check_district = $conn->query("SHOW COLUMNS FROM properties LIKE 'district'");
    if ($check_district->num_rows == 0) {
        $alter_district = "ALTER TABLE properties ADD COLUMN district VARCHAR(100) DEFAULT NULL";
        if (!$conn->query($alter_district)) {
            error_log("district kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("district kolonu başarıyla eklendi");
        }
    }
    
    // address kolonu eksikse ekle
    $check_address = $conn->query("SHOW COLUMNS FROM properties LIKE 'address'");
    if ($check_address->num_rows == 0) {
        $alter_address = "ALTER TABLE properties ADD COLUMN address TEXT DEFAULT NULL";
        if (!$conn->query($alter_address)) {
            error_log("address kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("address kolonu başarıyla eklendi");
        }
    }
    
    // bedrooms kolonu eksikse ekle
    $check_bedrooms = $conn->query("SHOW COLUMNS FROM properties LIKE 'bedrooms'");
    if ($check_bedrooms->num_rows == 0) {
        $alter_bedrooms = "ALTER TABLE properties ADD COLUMN bedrooms INT DEFAULT 0";
        if (!$conn->query($alter_bedrooms)) {
            error_log("bedrooms kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("bedrooms kolonu başarıyla eklendi");
        }
    }
    
    // bathrooms kolonu eksikse ekle
    $check_bathrooms = $conn->query("SHOW COLUMNS FROM properties LIKE 'bathrooms'");
    if ($check_bathrooms->num_rows == 0) {
        $alter_bathrooms = "ALTER TABLE properties ADD COLUMN bathrooms INT DEFAULT 0";
        if (!$conn->query($alter_bathrooms)) {
            error_log("bathrooms kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("bathrooms kolonu başarıyla eklendi");
        }
    }
    
    // Area kolonu eksikse ekle (size_sqft ile aynı)
    $check_area = $conn->query("SHOW COLUMNS FROM properties LIKE 'area'");
    if ($check_area->num_rows == 0) {
        $alter_area = "ALTER TABLE properties ADD COLUMN area INT DEFAULT 0 AFTER size_sqft";
        if (!$conn->query($alter_area)) {
            error_log("Area kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("Area kolonu başarıyla eklendi");
            // Mevcut size_sqft değerlerini area'ya kopyala
            $conn->query("UPDATE properties SET area = size_sqft WHERE area IS NULL OR area = 0");
        }
    }
    
    // Neighborhood kolonu eksikse ekle
    $check_neighborhood = $conn->query("SHOW COLUMNS FROM properties LIKE 'neighborhood'");
    if ($check_neighborhood->num_rows == 0) {
        $alter_neighborhood = "ALTER TABLE properties ADD COLUMN neighborhood VARCHAR(100) DEFAULT NULL AFTER district";
        if (!$conn->query($alter_neighborhood)) {
            error_log("Neighborhood kolonu eklenemedi: " . $conn->error);
        } else {
            error_log("Neighborhood kolonu başarıyla eklendi");
            // Mevcut address değerlerini neighborhood'a kopyala
            $conn->query("UPDATE properties SET neighborhood = address WHERE neighborhood IS NULL");
        }
    }
    
    // Eksik alanları ekle
    $missing_columns = [
        'area_gross' => "INT DEFAULT 0 AFTER area",
        'area_net' => "INT DEFAULT 0 AFTER area_gross",
        'room_count' => "VARCHAR(20) DEFAULT NULL AFTER bathrooms",
        'floor' => "INT DEFAULT 0 AFTER room_count",
        'heating' => "VARCHAR(50) DEFAULT NULL AFTER floor",
        'elevator' => "VARCHAR(10) DEFAULT NULL AFTER heating",
        'parking' => "VARCHAR(50) DEFAULT NULL AFTER elevator",
        'furnished' => "BOOLEAN DEFAULT FALSE AFTER parking",
        'usage_status' => "VARCHAR(50) DEFAULT NULL AFTER furnished",
        'dues' => "DECIMAL(10,2) DEFAULT 0 AFTER usage_status",
        'credit_eligible' => "BOOLEAN DEFAULT FALSE AFTER dues",
        'deed_status' => "VARCHAR(50) DEFAULT NULL AFTER credit_eligible",
        'exchange' => "VARCHAR(10) DEFAULT 'Hayır' AFTER deed_status",
        'main_image' => "VARCHAR(255) DEFAULT NULL AFTER images",
        'interior_features' => "TEXT DEFAULT NULL AFTER main_image",
        'exterior_features' => "TEXT DEFAULT NULL AFTER interior_features",
        'neighborhood_features' => "TEXT DEFAULT NULL AFTER exterior_features",
        'transportation_features' => "TEXT DEFAULT NULL AFTER neighborhood_features",
        'view_features' => "TEXT DEFAULT NULL AFTER transportation_features",
        'housing_type_features' => "TEXT DEFAULT NULL AFTER view_features"
    ];
    
    foreach ($missing_columns as $column => $definition) {
        $check_column = $conn->query("SHOW COLUMNS FROM properties LIKE '$column'");
        if ($check_column->num_rows == 0) {
            $alter_query = "ALTER TABLE properties ADD COLUMN $column $definition";
            if (!$conn->query($alter_query)) {
                error_log("$column kolonu eklenemedi: " . $conn->error);
            } else {
                error_log("$column kolonu başarıyla eklendi");
            }
        }
    }
    
    // Status kolonunu kontrol et ve güncelle
    $check_status_enum = $conn->query("SHOW COLUMNS FROM properties WHERE Field = 'status'");
    if ($check_status_enum->num_rows > 0) {
        $status_info = $check_status_enum->fetch_assoc();
        if (!strpos($status_info['Type'], 'active')) {
            // Status enum'una active ekle
            $alter_status = "ALTER TABLE properties MODIFY COLUMN status ENUM('pending', 'approved', 'rejected', 'active') DEFAULT 'active'";
            $conn->query($alter_status);
            error_log("Status kolonu 'active' değeri ile güncellendi");
        }
    }

    // Permissions tablosu oluştur
    $create_permissions_table = "
    CREATE TABLE IF NOT EXISTS user_permissions (
        id INT AUTO_INCREMENT PRIMARY KEY,
        user_id INT NOT NULL,
        permission_type ENUM('add_property', 'edit_property', 'delete_property', 'manage_users', 'view_all_properties') NOT NULL,
        granted_by INT NOT NULL,
        granted_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        expires_at TIMESTAMP NULL,
        is_active BOOLEAN DEFAULT TRUE,
        FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
        FOREIGN KEY (granted_by) REFERENCES users(id) ON DELETE CASCADE,
        UNIQUE KEY unique_user_permission (user_id, permission_type)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";

    if (!$conn->query($create_permissions_table)) {
        throw new Exception("Permissions tablosu oluşturulamadı: " . $conn->error);
    }
    
    // Blog tablosunu oluştur
    $create_blog_table = "
    CREATE TABLE IF NOT EXISTS blogs (
        id INT AUTO_INCREMENT PRIMARY KEY,
        title VARCHAR(255) NOT NULL,
        content LONGTEXT NOT NULL,
        excerpt TEXT,
        author_id INT NOT NULL DEFAULT 1,
        author_name VARCHAR(100) NOT NULL,
        category VARCHAR(100) DEFAULT 'Genel',
        tags VARCHAR(500),
        featured_image VARCHAR(255),
        blog_file VARCHAR(100),
        views INT DEFAULT 0,
        status ENUM('draft', 'published', 'archived') DEFAULT 'published',
        created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
        updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        INDEX idx_status (status),
        INDEX idx_created_at (created_at),
        INDEX idx_category (category)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";

    if (!$conn->query($create_blog_table)) {
        throw new Exception("Blog tablosu oluşturulamadı: " . $conn->error);
    }
    
    // Demo blog yazılarını kontrol et ve ekle (yoksa)
    $check_blogs = $conn->query("SELECT COUNT(*) as count FROM blogs");
    $blog_count = $check_blogs->fetch_assoc()['count'];
    
    if ($blog_count == 0) {
        // Demo blog yazıları ekle
        $demo_blogs = [
            [
                'title' => 'Gayrimenkul Yatırımında 2024 Trendleri',
                'content' => 'Bu yıl gayrimenkul sektöründe yaşanan değişimler ve gelecek dönemde beklenen trendler hakkında detaylı analiz.',
                'excerpt' => 'Gayrimenkul sektöründe 2024 yılında yaşanan değişimler ve yatırım fırsatları.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Yatırım',
                'tags' => 'gayrimenkul,yatırım,2024,trend',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog1.php'
            ],
            [
                'title' => 'İstanbul\'da Ev Almak İçin En İyi Bölgeler',
                'content' => 'İstanbul\'un farklı bölgelerinde yaşam kalitesi, ulaşım imkanları ve fiyat avantajları analizi.',
                'excerpt' => 'İstanbul\'da ev almak isteyenler için en uygun bölgelerin detaylı incelemesi.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Bölge Analizi',
                'tags' => 'istanbul,konut,bölge,analiz',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog2.php'
            ],
            [
                'title' => 'Kiralık Ev Seçerken Dikkat Edilmesi Gerekenler',
                'content' => 'Kiralık ev ararken dikkat edilmesi gereken önemli kriterler ve ipuçları.',
                'excerpt' => 'Doğru kiralık evi seçmek için bilinmesi gereken püf noktalar.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Kiralama',
                'tags' => 'kiralık,ev,seçim,ipucu',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog3.php'
            ],
            [
                'title' => 'Ofis Kiralama Sürecinde Bilinmesi Gerekenler',
                'content' => 'İşletmeler için ofis kiralama sürecinde dikkat edilmesi gereken yasal ve pratik konular.',
                'excerpt' => 'Ofis kiralama sürecindeki önemli adımlar ve dikkat edilmesi gerekenler.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Ticari',
                'tags' => 'ofis,kiralama,ticari,işletme',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog4.php'
            ],
            [
                'title' => 'Gayrimenkul Değerleme Yöntemleri',
                'content' => 'Profesyonel gayrimenkul değerleme yöntemleri ve piyasa analizi teknikleri.',
                'excerpt' => 'Gayrimenkul değerleme sürecinde kullanılan yöntemler ve değerlendirme kriterleri.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Değerleme',
                'tags' => 'değerleme,analiz,piyasa,gayrimenkul',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog5.php'
            ],
            [
                'title' => 'Depo ve Fabrika Kiralama Rehberi',
                'content' => 'Endüstriyel amaçlı depo ve fabrika kiralama sürecinde bilinmesi gerekenler.',
                'excerpt' => 'Depo ve fabrika kiralama sürecindeki önemli detaylar ve dikkat edilecek noktalar.',
                'author_name' => 'Gökhan Aydınlı',
                'category' => 'Endüstriyel',
                'tags' => 'depo,fabrika,endüstriyel,kiralama',
                'featured_image' => 'GA2.png',
                'blog_file' => 'blog6.php'
            ]
        ];
        
        // Admin kullanıcısının ID'sini al
        $admin_result = $conn->query("SELECT id FROM users WHERE role = 'admin' LIMIT 1");
        $admin_row = $admin_result->fetch_assoc();
        $admin_id = $admin_row ? $admin_row['id'] : 1; // Admin yoksa 1 kullan
        
        foreach ($demo_blogs as $blog) {
            // Slug oluştur
            $slug = strtolower(str_replace(' ', '-', $blog['title'])) . '-' . time() . '-' . rand(1000, 9999);
            
            $stmt = $conn->prepare("INSERT INTO blogs (title, slug, content, excerpt, featured_image, status) VALUES (?, ?, ?, ?, ?, 'published')");
            $stmt->bind_param("sssss", 
                $blog['title'],
                $slug,
                $blog['content'], 
                $blog['excerpt'], 
                $blog['featured_image']
            );
            $stmt->execute();
            $stmt->close();
        }
        
        // echo "<!-- Demo blog yazıları eklendi -->\n";
    }
    
    // Admin kullanıcısını kontrol et ve ekle (yoksa) - DEVRE DIŞI BIRAKILD!
    /*
    $admin_email = 'info@gokhanaydinli.com';
    $check_admin = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_admin->bind_param("s", $admin_email);
    $check_admin->execute();
    $check_admin->store_result();
    
    if ($check_admin->num_rows == 0) {
        // Admin kullanıcısı yok, ekle
        $admin_password = password_hash('admin123', PASSWORD_DEFAULT);
        $insert_admin = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'admin', TRUE, TRUE)");
        $admin_name = 'Gökhan Aydınlı';
        $insert_admin->bind_param("sss", $admin_name, $admin_email, $admin_password);
        
        if ($insert_admin->execute()) {
            echo "<!-- Admin kullanıcısı oluşturuldu: info@gokhanaydinli.com / admin123 -->\n";
        } else {
            throw new Exception("Admin kullanıcısı oluşturulamadı: " . $conn->error);
        }
        $insert_admin->close();
    }
    $check_admin->close();
    */
    
    // ROOT kullanıcısını kontrol et ve ekle (yoksa) - SÜPER ADMİN
    $root_email = 'root@gokhanaydinli.com';
    $check_root = $conn->prepare("SELECT id FROM users WHERE email = ?");
    $check_root->bind_param("s", $root_email);
    $check_root->execute();
    $check_root->store_result();
    
    if ($check_root->num_rows == 0) {
        // Root kullanıcısı yok, ekle
        $root_password = password_hash('113041sS?!_', PASSWORD_DEFAULT);
        $insert_root = $conn->prepare("INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES (?, ?, ?, 'root', TRUE, TRUE)");
        $root_name = 'root';
        $insert_root->bind_param("sss", $root_name, $root_email, $root_password);
        
        if ($insert_root->execute()) {
            echo "<!-- Root kullanıcısı oluşturuldu: root@gokhanaydinli.com / 113041sS?!_ -->\n";
        } else {
            throw new Exception("Root kullanıcısı oluşturulamadı: " . $conn->error);
        }
        $insert_root->close();
    }
    $check_root->close();
    
    // echo "<!-- Veritabanı bağlantısı başarılı: gokhanaydinli_db -->\n";
    
} catch (Exception $e) {
    die("Database connection error: " . $e->getMessage());
}

// Kullanıcı rolü kontrol fonksiyonları
function isRoot($user_id, $conn) {
    if (!$user_id || !$conn) return false;
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'root'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function isAdmin($user_id, $conn) {
    if (!$user_id || !$conn) return false;
    
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND role IN ('admin', 'root')");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function canManageUsers($user_id, $conn) {
    if (!$user_id || !$conn) return false;
    
    // Sadece root kullanıcıları admin yaratabilir
    $stmt = $conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'root'");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $result = $stmt->get_result();
    
    return $result->num_rows > 0;
}

function hasPermission($user_id, $permission, $conn) {
    if (!$user_id || !$conn) return false;
    
    // Root kullanıcısı tüm yetkilere sahiptir
    if (isRoot($user_id, $conn)) {
        return true;
    }
    
    // Admin kullanıcıları da çoğu yetkiye sahiptir
    if (isAdmin($user_id, $conn)) {
        // Admin'lerin sahip olduğu yetkiler
        $admin_permissions = ['add_property', 'edit_property', 'delete_property', 'view_all_properties', 'manage_blog'];
        if (in_array($permission, $admin_permissions)) {
            return true;
        }
    }
    
    $stmt = $conn->prepare("SELECT can_add_property FROM users WHERE id = ?");
    $stmt->bind_param("i", $user_id);
    $stmt->execute();
    $user = $stmt->get_result()->fetch_assoc();
    
    if ($permission == 'add_property') {
        return $user['can_add_property'] == 1;
    }
    
    return false;
}

// Test bağlantısı
if (!$conn || $conn->connect_error) {
    error_log("Database connection failed: " . $conn->connect_error);
    die("Database connection error");
}

// Başarı log mesajı
error_log("Database bağlantısı başarılı - gokhanaydinli_db");

/**
 * Türkçe isimler için avatar harfleri oluştur
 * @param string $name Kullanıcının tam adı
 * @return string Avatar harfleri (örn: "GA", "MK")
 */
function generateAvatarInitials($name) {
    if (empty($name)) {
        return 'XX';
    }
    
    // İsmi temizle ve boşluklara göre böl
    $name_parts = explode(' ', trim($name));
    
    if (count($name_parts) >= 2) {
        // İsim ve soyisimin ilk harflerini al
        $first_initial = substr($name_parts[0], 0, 1);
        $last_initial = substr($name_parts[1], 0, 1);
        return strtoupper($first_initial . $last_initial);
    } else {
        // Tek kelime ise ilk iki harfini al
        return strtoupper(substr($name, 0, 2));
    }
}

?>
