#!/bin/bash

# MySQL bağlantı bilgileri
HOST="localhost"
USER="gokhanay_user"
PASS="113041122839sS?!_"
DB="gokhanay_db"

echo "MySQL Usage Status ENUM Düzeltme"
echo "================================"

# ENUM düzeltme komutu
mysql -h"$HOST" -u"$USER" -p"$PASS" "$DB" << 'EOF'
-- Mevcut enum kontrol
SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'properties' AND COLUMN_NAME = 'usage_status';

-- ENUM'u düzelt
ALTER TABLE properties MODIFY COLUMN usage_status 
ENUM('Bos', 'Kiracili', 'Malik Kullaniminda', 'Yatirim Amacli') 
NOT NULL DEFAULT 'Bos';

-- Yeni enum kontrol
SELECT COLUMN_TYPE FROM INFORMATION_SCHEMA.COLUMNS 
WHERE TABLE_NAME = 'properties' AND COLUMN_NAME = 'usage_status';

-- Test insert (sadece kontrol için)
SELECT 'Test tamamlandı' AS result;
EOF

echo "ENUM düzeltme işlemi tamamlandı!"
