#!/bin/bash

# Gökhan Aydınlı Gayrimenkul Sitesi - Web Yükleme için ZIP Hazırlama
# Bu script tüm dosyaları web yükleme için hazırlar

echo "🏠 Gökhan Aydınlı Gayrimenkul - Web Yükleme Paketi Hazırlanıyor..."
echo "================================================================"

# Yedek dizini oluştur
BACKUP_DIR="backup_$(date +%Y%m%d_%H%M%S)"
mkdir -p $BACKUP_DIR

# ZIP dosya adı
ZIP_NAME="gokhanaydinli_website_$(date +%Y%m%d_%H%M%S).zip"

echo "📦 Dosyalar paketleniyor..."

# Gerekmeyen dosyaları hariç tut
exclude_files=(
    "*.zip"
    "*.gz" 
    "*.tar"
    "backup_*"
    ".git*"
    "node_modules"
    "*.log"
    "create_zip.sh"
    "install.sh"
    "sql/backup_database.sh"
)

# Exclude parametrelerini oluştur
exclude_params=""
for file in "${exclude_files[@]}"; do
    exclude_params="$exclude_params --exclude=$file"
done

# ZIP oluştur
zip -r $ZIP_NAME . $exclude_params \
    --exclude="backup_*" \
    --exclude="*.zip" \
    --exclude="*.gz" \
    --exclude=".git*" \
    --exclude="node_modules/*" \
    --exclude="*.log"

if [ $? -eq 0 ]; then
    echo "✅ ZIP dosyası oluşturuldu: $ZIP_NAME"
    echo "📁 Dosya boyutu: $(ls -lh $ZIP_NAME | awk '{print $5}')"
    echo ""
    echo "🌐 Web Yükleme Adımları:"
    echo "========================="
    echo "1. Hosting sağlayıcınızın dosya yöneticisine girin"
    echo "2. public_html klasörüne upload.php dosyasını yükleyin"
    echo "3. Tarayıcıdan https://siteniz.com/upload.php adresini açın"
    echo "4. Şifre: gokhan2024upload"
    echo "5. $ZIP_NAME dosyasını yükleyin"
    echo "6. Veritabanı ayarlarını yapın"
    echo "7. upload.php dosyasını güvenlik için silin"
    echo ""
    echo "📊 Veritabanı Bilgileri:"
    echo "- Veritabanı: gokhanaydinli_db"
    echo "- SQL Dosyası: sql/gokhanaydinli_db_complete.sql"
    echo "- Admin: admin@gokhanaydinli.com / admin123"
    echo ""
else
    echo "❌ ZIP dosyası oluşturulamadı!"
    exit 1
fi

# İsteğe bağlı: FTP bilgileri için template
cat > ftp_upload_guide.txt << 'EOF'
FTP ile Yükleme Rehberi
========================

1. FTP İstemcisi Kurulum:
   - FileZilla (Ücretsiz): https://filezilla-project.org/
   - WinSCP (Windows): https://winscp.net/
   - Cyberduck (Mac): https://cyberduck.io/

2. FTP Bağlantı Bilgileri:
   Host: ftp.sitenizadı.com (hosting sağlayıcınızdan alın)
   Kullanıcı: hosting_kullanıcı_adınız
   Şifre: hosting_şifreniz
   Port: 21 (FTP) veya 22 (SFTP)

3. Yükleme Adımları:
   - public_html klasörüne bağlanın
   - Tüm dosyaları yükleyin (upload.php dahil)
   - https://siteniz.com/upload.php adresini açın
   - ZIP dosyasını yükleyin

4. cPanel Alternatifi:
   - cPanel → File Manager
   - public_html klasörü
   - Upload ile ZIP yükleme
   - Extract ile açma

5. Veritabanı Kurulumu:
   - cPanel → phpMyAdmin
   - Import → gokhanaydinli_db_complete.sql
   - db.php dosyasını düzenleyin

Güvenlik: Kurulum sonrası upload.php dosyasını silin!
EOF

echo "📋 FTP rehberi oluşturuldu: ftp_upload_guide.txt"
echo ""
echo "🎉 Web yükleme paketi hazır!"
echo "💡 Dosyaları hosting sağlayıcınıza yükleyebilirsiniz."
