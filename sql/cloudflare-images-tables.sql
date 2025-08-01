-- Çoklu Domain Cloudflare Images için veritabanı tabloları

-- Domain konfigürasyonları tablosu
CREATE TABLE IF NOT EXISTS domain_image_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) UNIQUE,
    config JSON,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_domain (domain)
);

-- Çoklu domain Cloudflare resimleri tablosu
CREATE TABLE IF NOT EXISTS cloudflare_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    domain VARCHAR(255),
    cloudflare_image_id VARCHAR(255) UNIQUE,
    image_urls JSON,
    metadata JSON,
    is_main_image TINYINT(1) DEFAULT 0,
    order_index INT DEFAULT 0,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    INDEX idx_domain (domain),
    INDEX idx_property (property_id),
    INDEX idx_cloudflare_id (cloudflare_image_id),
    INDEX idx_main_image (is_main_image),
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
);

-- Properties tablosuna Cloudflare desteği ekle
ALTER TABLE properties 
ADD COLUMN cloudflare_images JSON AFTER images,
ADD COLUMN cloudflare_main_image VARCHAR(255) AFTER main_image,
ADD COLUMN use_cloudflare TINYINT(1) DEFAULT 0 AFTER cloudflare_main_image;

-- İndeks ekle
ALTER TABLE properties ADD INDEX idx_use_cloudflare (use_cloudflare);
ALTER TABLE properties ADD INDEX idx_cloudflare_main (cloudflare_main_image);

-- Varsayılan domain konfigürasyonu ekle
INSERT INTO domain_image_configs (domain, config) VALUES 
('gokhanaydinli.com', JSON_OBJECT(
    'logo_url', 'https://gokhanaydinli.com/images/logo.png',
    'company_name', 'Gökhan Aydınlı Emlak',
    'website', 'gokhanaydinli.com',
    'phone', '+90 555 123 45 67',
    'position', 'bottom-right',
    'opacity', 80,
    'logo_size', 'medium',
    'text_color', '#FFFFFF',
    'background_color', 'rgba(0,0,0,0.7)'
))
ON DUPLICATE KEY UPDATE config = VALUES(config);
