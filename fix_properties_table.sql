-- Building floors alanını properties tablosuna ekle
ALTER TABLE properties ADD building_floors VARCHAR(20) DEFAULT NULL;

-- Diğer eksik alanları kontrol et ve ekle (eğer yoksa)
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS parking VARCHAR(50) DEFAULT NULL;
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS elevator VARCHAR(10) DEFAULT NULL;
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS usage_status VARCHAR(20) DEFAULT NULL;
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS credit_eligible VARCHAR(10) DEFAULT NULL;
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS deed_status VARCHAR(20) DEFAULT NULL;
-- ALTER TABLE properties ADD COLUMN IF NOT EXISTS exchange VARCHAR(10) DEFAULT NULL;

-- Mevcut properties tablosunun yapısını göster
DESCRIBE properties;
