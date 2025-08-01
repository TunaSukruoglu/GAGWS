-- Properties tablosuna özellik sütunları ekleme
USE gokhanaydinli_db;

-- Önce mevcut tablo yapısını kontrol et
DESCRIBE properties;

-- İç özellikler sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS interior_features JSON DEFAULT NULL;

-- Dış özellikler sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS exterior_features JSON DEFAULT NULL;

-- Muhit özellikleri sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS neighborhood_features JSON DEFAULT NULL;

-- Ulaşım özellikleri sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS transportation_features JSON DEFAULT NULL;

-- Manzara özellikleri sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS view_features JSON DEFAULT NULL;

-- Konut tipi özellikleri sütunu ekle
ALTER TABLE properties ADD COLUMN IF NOT EXISTS housing_type_features JSON DEFAULT NULL;

-- Güncellenmiş tablo yapısını kontrol et
DESCRIBE properties;

-- Test için örnek veri
SELECT TABLE_NAME, COLUMN_NAME, DATA_TYPE, IS_NULLABLE, COLUMN_DEFAULT 
FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_SCHEMA = 'gokhanaydinli_db' 
AND TABLE_NAME = 'properties' 
AND COLUMN_NAME LIKE '%features%';
