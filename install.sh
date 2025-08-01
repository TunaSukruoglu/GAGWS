#!/bin/bash

# Gökhan Aydınlı Gayrimenkul Sitesi - Hızlı Kurulum Script
# Bu script projeyi hızlıca kurmak için kullanılır

echo "🏠 Gökhan Aydınlı Gayrimenkul Sitesi - Kurulum Başlatılıyor..."
echo "=================================================="

# Veritabanı bilgilerini kullanıcıdan al
echo "📊 Veritabanı Bilgileri:"
read -p "MySQL Host (varsayılan: localhost): " DB_HOST
DB_HOST=${DB_HOST:-localhost}

read -p "MySQL Kullanıcı Adı (varsayılan: root): " DB_USER  
DB_USER=${DB_USER:-root}

read -s -p "MySQL Şifre: " DB_PASS
echo ""

read -p "Veritabanı Adı (varsayılan: gokhanaydinli_db): " DB_NAME
DB_NAME=${DB_NAME:-gokhanaydinli_db}

echo ""
echo "🔧 Kurulum ayarları:"
echo "Host: $DB_HOST"
echo "Kullanıcı: $DB_USER"
echo "Veritabanı: $DB_NAME"
echo ""

read -p "Bu ayarlarla devam etmek istiyor musunuz? (y/N): " CONFIRM
if [[ $CONFIRM != [yY] ]]; then
    echo "❌ Kurulum iptal edildi."
    exit 1
fi

# db.php dosyasını güncelle
echo "📝 Veritabanı bağlantı dosyası güncelleniyor..."
cat > db.php << EOF
<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

\$servername = "$DB_HOST";
\$username = "$DB_USER";
\$password = "$DB_PASS";
\$dbname = "$DB_NAME";

try {
    // Bağlantı oluştur
    \$conn = new mysqli(\$servername, \$username, \$password, \$dbname);
    
    if (\$conn->connect_error) {
        throw new Exception("MySQL bağlantısı başarısız: " . \$conn->connect_error);
    }
    
    // ZORUNLU: Character set ayarlarını yap
    \$conn->set_charset("utf8mb4");
    
    // Ek charset ayarları (güvenlik için)
    \$conn->query("SET NAMES utf8mb4");
    \$conn->query("SET CHARACTER SET utf8mb4");
    \$conn->query("SET character_set_connection=utf8mb4");
    \$conn->query("SET character_set_client=utf8mb4");
    \$conn->query("SET character_set_results=utf8mb4");
    \$conn->query("SET lc_time_names = 'tr_TR'");
    
    echo "<!-- Veritabanı bağlantısı başarılı: $DB_NAME -->\n";
    
} catch (Exception \$e) {
    die("Database connection error: " . \$e->getMessage());
}

// Kullanıcı rolü kontrol fonksiyonu
function isAdmin(\$user_id, \$conn) {
    if (!\$user_id || !\$conn) return false;
    
    \$stmt = \$conn->prepare("SELECT role FROM users WHERE id = ? AND role = 'admin'");
    \$stmt->bind_param("i", \$user_id);
    \$stmt->execute();
    \$result = \$stmt->get_result();
    
    return \$result->num_rows > 0;
}

function hasPermission(\$user_id, \$permission, \$conn) {
    if (!\$user_id || !\$conn) return false;
    
    \$stmt = \$conn->prepare("SELECT can_add_property FROM users WHERE id = ?");
    \$stmt->bind_param("i", \$user_id);
    \$stmt->execute();
    \$user = \$stmt->get_result()->fetch_assoc();
    
    if (\$permission == 'add_property') {
        return \$user['can_add_property'] == 1;
    }
    
    return false;
}

// Test bağlantısı
if (!\$conn || \$conn->connect_error) {
    error_log("Database connection failed: " . \$conn->connect_error);
    die("Database connection error");
}

// Başarı log mesajı
error_log("Database bağlantısı başarılı - $DB_NAME");

?>
EOF

# Veritabanını oluştur
echo "🗄️  Veritabanı oluşturuluyor..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS -e "CREATE DATABASE IF NOT EXISTS $DB_NAME CHARACTER SET utf8mb4 COLLATE utf8mb4_turkish_ci;"

if [ $? -eq 0 ]; then
    echo "✅ Veritabanı oluşturuldu: $DB_NAME"
else
    echo "❌ Veritabanı oluşturulamadı!"
    exit 1
fi

# SQL dosyasını import et
echo "📊 Veritabanı tabloları oluşturuluyor..."
mysql -h $DB_HOST -u $DB_USER -p$DB_PASS $DB_NAME < sql/gokhanaydinli_db_complete.sql

if [ $? -eq 0 ]; then
    echo "✅ Veritabanı tabloları başarıyla oluşturuldu!"
else
    echo "❌ Veritabanı tabloları oluşturulamadı!"
    exit 1
fi

# Klasör izinlerini ayarla
echo "📁 Dosya izinleri ayarlanıyor..."
chmod 755 images/ 2>/dev/null || mkdir -p images && chmod 755 images/
chmod 755 images/properties/ 2>/dev/null || mkdir -p images/properties && chmod 755 images/properties/
chmod 755 images/blog/ 2>/dev/null || mkdir -p images/blog && chmod 755 images/blog/
chmod 755 images/users/ 2>/dev/null || mkdir -p images/users && chmod 755 images/users/

echo "✅ Dosya izinleri ayarlandı!"

echo ""
echo "🎉 KURULUM TAMAMLANDI!"
echo "=================================================="
echo "🌐 Web siteniz hazır!"
echo ""
echo "👤 Varsayılan Admin Hesabı:"
echo "   Email: admin@gokhanaydinli.com"
echo "   Şifre: admin123"
echo ""
echo "👤 Varsayılan Agent Hesabı:"  
echo "   Email: agent@gokhanaydinli.com"
echo "   Şifre: password"
echo ""
echo "🔧 Veritabanı Bilgileri:"
echo "   Host: $DB_HOST"
echo "   Veritabanı: $DB_NAME"
echo "   Kullanıcı: $DB_USER"
echo ""
echo "💡 İlk giriş yaptıktan sonra admin şifresini değiştirmeyi unutmayın!"
echo "📝 Daha fazla bilgi için README.md dosyasını okuyun."
