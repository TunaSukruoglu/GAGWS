-- Properties tablosuna Cloudflare Images desteği ekle
-- Bu script müşteri onay verirse çalıştırılacak

-- Cloudflare image ID'leri için yeni kolon
ALTER TABLE properties ADD COLUMN cloudflare_images TEXT NULL AFTER images;

-- Cloudflare Images durumu için index
ALTER TABLE properties ADD INDEX idx_cloudflare_images (cloudflare_images);

-- Migration durumu için yeni kolon (opsiyonel)
ALTER TABLE properties ADD COLUMN image_migration_status ENUM('pending', 'in_progress', 'completed', 'failed') DEFAULT 'pending' AFTER cloudflare_images;

-- Migration tarihi
ALTER TABLE properties ADD COLUMN image_migration_date TIMESTAMP NULL AFTER image_migration_status;

-- Örnek kullanım:
-- Mevcut sistem: images = ["file1.jpg", "file2.jpg"]
-- Cloudflare sonrası: cloudflare_images = ["uuid1", "uuid2"]
-- Hibrit dönem: her ikisi de dolu olabilir, önce cloudflare_images kontrol edilir
