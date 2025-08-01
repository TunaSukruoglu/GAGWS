#!/bin/bash

# MySQL bağlantı bilgileri
HOST="localhost"
USER="gokhanay_user"
PASS="113041122839sS?!_"
DB="gokhanay_db"

echo "Tüm ENUM Kolonları Düzeltme"
echo "==========================="

mysql -h"$HOST" -u"$USER" -p"$PASS" "$DB" << 'EOF'
-- Tüm ENUM kolonlarını listele
SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'properties' AND DATA_TYPE = 'enum';

-- ENUM'ları düzelt (Türkçe karakter olmadan)
ALTER TABLE properties MODIFY COLUMN usage_status 
ENUM('Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli') 
NOT NULL DEFAULT 'Bos';

ALTER TABLE properties MODIFY COLUMN parking 
ENUM('Otopark Yok', 'Acik Otopark', 'Kapali Otopark', 'Otopark Var') 
NOT NULL DEFAULT 'Otopark Yok';

ALTER TABLE properties MODIFY COLUMN deed_status 
ENUM('Kat Mulkiyeti', 'Kat Irtifaki', 'Arsa Payi', 'Mustakil Tapu') 
NOT NULL DEFAULT 'Kat Mulkiyeti';

ALTER TABLE properties MODIFY COLUMN exchange 
ENUM('Evet', 'Hayir') 
NOT NULL DEFAULT 'Hayir';

ALTER TABLE properties MODIFY COLUMN heating 
ENUM('Yok', 'Soba', 'Dogalgaz Sobasi', 'Kat Kaloriferi', 'Merkezi Sistem', 'Kombi (Dogalgaz)', 'Kombi (Elektrik)', 'Yerden Isitma', 'Klima', 'Fancoil Unitesi', 'Gunes Enerjisi', 'Jeotermal', 'Somine') 
NOT NULL DEFAULT 'Yok';

ALTER TABLE properties MODIFY COLUMN elevator 
ENUM('Var', 'Yok') 
NOT NULL DEFAULT 'Yok';

-- Character set kontrolü
SHOW TABLE STATUS LIKE 'properties';

-- Güncellenen ENUM'ları kontrol et
SELECT COLUMN_NAME, COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'properties' AND DATA_TYPE = 'enum';

SELECT 'ENUM düzeltme tamamlandı' AS result;
EOF
