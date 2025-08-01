#!/bin/bash

# Gökhan Aydınlı Gayrimenkul Sitesi - Veritabanı Backup Script
# Bu script mevcut veritabanını yedekler

# Veritabanı bilgileri
DB_HOST="localhost"
DB_USER="root"
DB_PASS="113041"
DB_NAME="gokhanaydinli_db"

# Backup dosya adı (tarih ile)
BACKUP_FILE="gokhanaydinli_db_backup_$(date +%Y%m%d_%H%M%S).sql"

echo "Veritabanı yedeği alınıyor..."
echo "Dosya: $BACKUP_FILE"

# mysqldump komutu
mysqldump -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME > $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "✅ Veritabanı yedeği başarıyla alındı: $BACKUP_FILE"
    echo "📁 Dosya boyutu: $(ls -lh $BACKUP_FILE | awk '{print $5}')"
else
    echo "❌ Veritabanı yedeği alınamadı!"
    exit 1
fi

# Gzip ile sıkıştır (opsiyonel)
echo "Dosya sıkıştırılıyor..."
gzip $BACKUP_FILE

if [ $? -eq 0 ]; then
    echo "✅ Dosya sıkıştırıldı: ${BACKUP_FILE}.gz"
    echo "📁 Sıkıştırılmış boyut: $(ls -lh ${BACKUP_FILE}.gz | awk '{print $5}')"
else
    echo "⚠️  Dosya sıkıştırılamadı, ham SQL dosyası mevcut: $BACKUP_FILE"
fi

echo "🎉 Backup işlemi tamamlandı!"
