# Gökhan Aydınlı Gayrimenkul Sitesi - Kurulum Rehberi

## Gerekli Dosyalar ve Kurulum

### 1. Veritabanı Kurulumu

Projeyi yüklemek için aşağıdaki SQL dosyasını kullanın:
- **Ana SQL Dosyası**: `sql/gokhanaydinli_db_complete.sql`

### 2. Veritabanı Bağlantı Ayarları

`db.php` dosyasındaki bağlantı ayarları:
```php
$servername = "localhost";
$username = "root";
$password = "113041";
$dbname = "gokhanaydinli_db";
```

### 3. Kurulum Adımları

1. **MySQL/MariaDB** sunucunuzda `sql/gokhanaydinli_db_complete.sql` dosyasını çalıştırın
2. Web dosyalarını sunucunuza yükleyin
3. `db.php` dosyasındaki veritabanı bağlantı bilgilerini kendi sunucunuza göre düzenleyin

### 4. Varsayılan Giriş Bilgileri

**Admin Hesabı:**
- Email: `admin@gokhanaydinli.com`
- Şifre: `admin123`

**Agent Hesabı:**
- Email: `agent@gokhanaydinli.com`
- Şifre: `password`

### 5. Veritabanı Tabloları

Sistem aşağıdaki ana tablolardan oluşur:

- **users**: Kullanıcı bilgileri (admin, agent, user)
- **properties**: Gayrimenkul ilanları
- **blogs**: Blog yazıları
- **user_permissions**: Kullanıcı yetkileri
- **favorites**: Favori ilanlar
- **contact_messages**: İletişim mesajları

### 6. Özellikler

- ✅ Kullanıcı yönetimi (Admin, Agent, User)
- ✅ Gayrimenkul ilan sistemi
- ✅ Blog yazı sistemi
- ✅ Çoklu dil desteği (Türkçe)
- ✅ Responsive tasarım
- ✅ Gelişmiş arama ve filtreleme
- ✅ Harita entegrasyonu
- ✅ E-posta sistemi
- ✅ Cloudflare Images desteği

### 7. Güvenlik

- Password hash kullanılır
- SQL Injection koruması
- XSS koruması
- CSRF koruması

### 8. Gereksinimler

- **PHP**: 7.4 veya üzeri
- **MySQL**: 5.7 veya üzeri / MariaDB 10.2+
- **Web Server**: Apache/Nginx
- **PHP Extensions**: mysqli, json, session

### 9. Dosya İzinleri

Aşağıdaki klasörlere yazma izni verin:
```bash
chmod 755 images/
chmod 755 images/properties/
chmod 755 images/blog/
chmod 755 images/users/
```

### 10. Sorun Giderme

**Veritabanı Bağlantı Hatası:**
- `db.php` dosyasındaki bağlantı bilgilerini kontrol edin
- MySQL servisinin çalıştığından emin olun

**Resim Yükleme Sorunları:**
- Klasör izinlerini kontrol edin
- PHP upload limitlerini kontrol edin

**Karakter Encoding Sorunları:**
- UTF-8 charset ayarının doğru olduğundan emin olun
- Veritabanı collation'ının `utf8mb4_turkish_ci` olduğunu kontrol edin

---

## İletişim
Teknik destek için: admin@gokhanaydinli.com
