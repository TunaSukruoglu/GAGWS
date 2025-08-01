-- Cloudflare Images için ek tablolar
-- Bu SQL'i çalıştırın: mysql -u gokhanuser -p gokhandb

-- 1. Property resimlerini detaylı takip için tablo
CREATE TABLE IF NOT EXISTS property_cloudflare_images (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT NOT NULL,
    cloudflare_image_id VARCHAR(255) NOT NULL UNIQUE,
    original_filename VARCHAR(255),
    image_urls JSON,
    metadata JSON,
    is_main BOOLEAN DEFAULT FALSE,
    domain VARCHAR(255) DEFAULT 'localhost',
    upload_date TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_property (property_id),
    INDEX idx_cloudflare (cloudflare_image_id),
    INDEX idx_domain (domain),
    INDEX idx_main (is_main),
    
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 2. Domain bazlı watermark konfigürasyonları
CREATE TABLE IF NOT EXISTS domain_watermark_configs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    domain VARCHAR(255) UNIQUE NOT NULL,
    company_name VARCHAR(255) NOT NULL,
    logo_image_id VARCHAR(255), -- Cloudflare'de yüklü logo ID'si
    watermark_config JSON, -- Pozisyon, boyut, renk ayarları
    is_active BOOLEAN DEFAULT TRUE,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    
    INDEX idx_domain (domain),
    INDEX idx_active (is_active)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- 3. Sample watermark config verileri
INSERT INTO domain_watermark_configs (domain, company_name, watermark_config) VALUES 
('gokhanaydinli.com', 'Gökhan Aydınlı Emlak', JSON_OBJECT(
    'position', 'bottom-right',
    'opacity', 0.8,
    'scale', 0.15,
    'margin', 20,
    'text_color', '#FFFFFF',
    'background_color', 'rgba(0,0,0,0.7)',
    'font_size', 14,
    'phone', '+90 555 123 4567',
    'website', 'www.gokhanaydinli.com'
)),
('localhost', 'Demo Emlak', JSON_OBJECT(
    'position', 'bottom-right',
    'opacity', 0.8,
    'scale', 0.15,
    'margin', 20,
    'text_color', '#FFFFFF',
    'background_color', 'rgba(0,0,0,0.7)',
    'font_size', 14,
    'phone', '+90 555 000 0000',
    'website', 'www.demo.com'
)) ON DUPLICATE KEY UPDATE updated_at = CURRENT_TIMESTAMP;

-- 4. Cloudflare API log tablosu (debug için)
CREATE TABLE IF NOT EXISTS cloudflare_api_logs (
    id INT AUTO_INCREMENT PRIMARY KEY,
    property_id INT,
    action VARCHAR(50), -- upload, delete, variant_create
    request_data JSON,
    response_data JSON,
    status VARCHAR(20), -- success, error
    error_message TEXT,
    domain VARCHAR(255),
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    
    INDEX idx_property (property_id),
    INDEX idx_action (action),
    INDEX idx_status (status),
    INDEX idx_created (created_at)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;