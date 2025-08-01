-- Gökhan Aydınlı Gayrimenkul Sitesi - Tam Veritabanı SQL Dosyası
-- Oluşturulma Tarihi: 15 Temmuz 2025
-- Bu dosya web sitesinin yüklenmesi için gerekli tüm tabloları içerir

-- Veritabanını oluştur
CREATE DATABASE IF NOT EXISTS gokhanaydinli_db CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;
USE gokhanaydinli_db;

-- Character set ayarları
SET NAMES utf8mb4;
SET CHARACTER SET utf8mb4;
SET character_set_connection=utf8mb4;
SET character_set_client=utf8mb4;
SET character_set_results=utf8mb4;
SET lc_time_names = 'tr_TR';

-- =====================================================
-- USERS TABLOSU - Kullanıcı bilgileri
-- =====================================================
CREATE TABLE IF NOT EXISTS users (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) UNIQUE NOT NULL,
    password VARCHAR(255) NOT NULL,
    phone VARCHAR(20),
    role ENUM('admin', 'agent', 'user') DEFAULT 'user',
    can_add_property BOOLEAN DEFAULT FALSE,
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    last_login TIMESTAMP NULL,
    profile_image VARCHAR(255),
    address TEXT,
    bio TEXT
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- PROPERTIES TABLOSU - Gayrimenkul bilgileri
-- =====================================================
CREATE TABLE IF NOT EXISTS properties (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    title VARCHAR(255) NOT NULL,
    description LONGTEXT,
    category VARCHAR(100) DEFAULT 'Daire',
    listing_type ENUM('Satılık', 'Kiralık') DEFAULT 'Satılık',
    type VARCHAR(50) DEFAULT 'sale',
    price DECIMAL(15,2) NOT NULL,
    yearly_tax DECIMAL(10,2) DEFAULT 0,
    size_sqft INT,
    area INT DEFAULT 0,
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
    cloudflare_images TEXT NULL,
    image_migration_status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending',
    image_migration_date TIMESTAMP NULL,
    status ENUM('pending', 'approved', 'rejected', 'active') DEFAULT 'active',
    featured BOOLEAN DEFAULT FALSE,
    views INT DEFAULT 0,
    created_by_admin BOOLEAN DEFAULT FALSE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    INDEX idx_status (status),
    INDEX idx_category (category),
    INDEX idx_listing_type (listing_type),
    INDEX idx_city (city),
    INDEX idx_district (district),
    INDEX idx_price (price),
    INDEX idx_featured (featured),
    INDEX idx_cloudflare_images (cloudflare_images),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- USER_PERMISSIONS TABLOSU - Kullanıcı yetkileri
-- =====================================================
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- BLOGS TABLOSU - Blog yazıları
-- =====================================================
CREATE TABLE IF NOT EXISTS blogs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    title VARCHAR(255) NOT NULL,
    slug VARCHAR(255) UNIQUE,
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
    INDEX idx_category (category),
    INDEX idx_slug (slug)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- FAVORITES TABLOSU - Favori ilanlar (opsiyonel)
-- =====================================================
CREATE TABLE IF NOT EXISTS favorites (
    id INT AUTO_INCREMENT PRIMARY KEY,
    user_id INT NOT NULL,
    property_id INT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE,
    UNIQUE KEY unique_favorite (user_id, property_id)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- CONTACT_MESSAGES TABLOSU - İletişim mesajları (opsiyonel)
-- =====================================================
CREATE TABLE IF NOT EXISTS contact_messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL,
    phone VARCHAR(20),
    subject VARCHAR(200),
    message TEXT NOT NULL,
    property_id INT NULL,
    status ENUM('new', 'read', 'replied', 'archived') DEFAULT 'new',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL,
    INDEX idx_status (status),
    INDEX idx_created_at (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci;

-- =====================================================
-- VERİ EKLEMELERİ
-- =====================================================

-- Admin kullanıcısı ekle
INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES 
('Gökhan Aydınlı', 'admin@gokhanaydinli.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'admin', TRUE, TRUE);

-- Demo agent kullanıcısı ekle
INSERT INTO users (name, email, password, role, can_add_property, is_active) VALUES 
('Demo Agent', 'agent@gokhanaydinli.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi', 'agent', TRUE, TRUE);

-- Demo blog yazıları ekle
INSERT INTO blogs (title, slug, content, excerpt, author_name, category, tags, featured_image, blog_file, status) VALUES 
('Gayrimenkul Yatırımında 2024 Trendleri', 'gayrimenkul-yatiriminda-2024-trendleri', 'Bu yıl gayrimenkul sektöründe yaşanan değişimler ve gelecek dönemde beklenen trendler hakkında detaylı analiz. Gayrimenkul piyasası sürekli değişen dinamiklere sahip olup, yatırımcıların bu değişimleri takip etmesi büyük önem taşımaktadır.', 'Gayrimenkul sektöründe 2024 yılında yaşanan değişimler ve yatırım fırsatları.', 'Gökhan Aydınlı', 'Yatırım', 'gayrimenkul,yatırım,2024,trend', 'images/blog/blog-1.jpg', 'blog1.php', 'published'),

('İstanbul\'da Ev Almak İçin En İyi Bölgeler', 'istanbulda-ev-almak-icin-en-iyi-bolgeler', 'İstanbul\'un farklı bölgelerinde yaşam kalitesi, ulaşım imkanları ve fiyat avantajları analizi. Şehrin her bölgesinin kendine özgü avantajları bulunmaktadır.', 'İstanbul\'da ev almak isteyenler için en uygun bölgelerin detaylı incelemesi.', 'Gökhan Aydınlı', 'Bölge Analizi', 'istanbul,konut,bölge,analiz', 'images/blog/blog-2.jpg', 'blog2.php', 'published'),

('Kiralık Ev Seçerken Dikkat Edilmesi Gerekenler', 'kiralik-ev-secerken-dikkat-edilmesi-gerekenler', 'Kiralık ev ararken dikkat edilmesi gereken önemli kriterler ve ipuçları. Doğru seçim yapmak için bilinmesi gereken tüm detaylar.', 'Doğru kiralık evi seçmek için bilinmesi gereken püf noktalar.', 'Gökhan Aydınlı', 'Kiralama', 'kiralık,ev,seçim,ipucu', 'images/blog/blog-3.jpg', 'blog3.php', 'published'),

('Ofis Kiralama Sürecinde Bilinmesi Gerekenler', 'ofis-kiralama-surecinde-bilinmesi-gerekenler', 'İşletmeler için ofis kiralama sürecinde dikkat edilmesi gereken yasal ve pratik konular. Ticari gayrimenkul kiralama sürecinin tüm aşamaları.', 'Ofis kiralama sürecindeki önemli adımlar ve dikkat edilmesi gerekenler.', 'Gökhan Aydınlı', 'Ticari', 'ofis,kiralama,ticari,işletme', 'images/blog/blog-4.jpg', 'blog4.php', 'published'),

('Gayrimenkul Değerleme Yöntemleri', 'gayrimenkul-degerleme-yontemleri', 'Profesyonel gayrimenkul değerleme yöntemleri ve piyasa analizi teknikleri. Doğru değerleme için kullanılan tüm yöntemler.', 'Gayrimenkul değerleme sürecinde kullanılan yöntemler ve değerlendirme kriterleri.', 'Gökhan Aydınlı', 'Değerleme', 'değerleme,analiz,piyasa,gayrimenkul', 'images/blog/blog-5.jpg', 'blog5.php', 'published'),

('Depo ve Fabrika Kiralama Rehberi', 'depo-ve-fabrika-kiralama-rehberi', 'Endüstriyel amaçlı depo ve fabrika kiralama sürecinde bilinmesi gerekenler. Sanayi tesisleri için kiralama sürecinin tüm detayları.', 'Depo ve fabrika kiralama sürecindeki önemli detaylar ve dikkat edilecek noktalar.', 'Gökhan Aydınlı', 'Endüstriyel', 'depo,fabrika,endüstriyel,kiralama', 'images/blog/blog-6.jpg', 'blog6.php', 'published');

-- Demo özellik ilanları ekle
INSERT INTO properties (user_id, title, description, category, listing_type, type, price, size_sqft, area, bedrooms, bathrooms, kitchens, garages, address, city, district, images, status, featured) VALUES 
(1, 'Beşiktaş\'ta Deniz Manzaralı 3+1 Daire', 'Beşiktaş\'ta deniz manzaralı, merkezi konumda 3+1 daire. Ulaşım imkanları mükemmel, sosyal tesislere yakın.', 'Daire', 'Satılık', 'sale', 2500000.00, 150, 150, 3, 2, 1, 1, 'Beşiktaş Mahallesi, Barbaros Bulvarı No:123', 'İstanbul', 'Beşiktaş', '["images/properties/property-1.jpg","images/properties/property-1-2.jpg"]', 'active', TRUE),

(1, 'Kadıköy\'de Modern 2+1 Kiralık Daire', 'Kadıköy\'de yeni yapılmış modern 2+1 daire. Eşyalı, merkezi ısıtma, güvenlik.', 'Daire', 'Kiralık', 'rent', 15000.00, 120, 120, 2, 1, 1, 0, 'Kadıköy Mahallesi, Bağdat Caddesi No:456', 'İstanbul', 'Kadıköy', '["images/properties/property-2.jpg","images/properties/property-2-2.jpg"]', 'active', TRUE),

(2, 'Şişli\'de Ticari İşyeri', 'Şişli\'de ana cadde üzerinde ticari işyeri. Yüksek müşteri potansiyeli, uygun fiyat.', 'İşyeri', 'Kiralık', 'rent', 25000.00, 200, 200, 0, 1, 0, 0, 'Şişli Mahallesi, Halaskargazi Caddesi No:789', 'İstanbul', 'Şişli', '["images/properties/property-3.jpg"]', 'active', FALSE),

(1, 'Başakşehir\'de Villa', 'Başakşehir\'de bahçeli villa. Geniş yaşam alanları, özel otopark, güvenlik.', 'Villa', 'Satılık', 'sale', 4500000.00, 300, 300, 4, 3, 2, 2, 'Başakşehir Mahallesi, Villa Sitesi No:12', 'İstanbul', 'Başakşehir', '["images/properties/property-4.jpg","images/properties/property-4-2.jpg"]', 'active', TRUE),

(2, 'Ümraniye\'de Depo', 'Ümraniye\'de endüstriyel depo. Yükleme rampaları, geniş alan, ulaşım kolaylığı.', 'Depo', 'Kiralık', 'rent', 35000.00, 1000, 1000, 0, 1, 0, 0, 'Ümraniye Sanayi Sitesi, Depo No:45', 'İstanbul', 'Ümraniye', '["images/properties/property-5.jpg"]', 'active', FALSE);

-- =====================================================
-- INDEX'LER VE PERFORMANS İYİLEŞTİRMELERİ
-- =====================================================

-- Performans için ek indexler
ALTER TABLE properties ADD INDEX idx_price_range (price, listing_type);
ALTER TABLE properties ADD INDEX idx_location (city, district);
ALTER TABLE properties ADD INDEX idx_size (area, bedrooms);
ALTER TABLE blogs ADD INDEX idx_published (status, created_at);

-- =====================================================
-- TAMAMLANDI
-- =====================================================

-- Bu SQL dosyası başarıyla çalıştırıldığında aşağıdaki varsayılan giriş bilgileri kullanılabilir:
-- Admin: admin@gokhanaydinli.com / admin123
-- Agent: agent@gokhanaydinli.com / password

-- Veritabanı hazır! Web sitesi artık yüklenebilir.
