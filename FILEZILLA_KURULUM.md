# 📁 FileZilla ile Kurulum Rehberi

## 🚀 FileZilla Kurulum Adımları

### 1. Dosyaları Hazırlayın
- Tüm proje dosyalarını bilgisayarınızda hazır bulundurun
- `install.php` dosyasını da dahil edin

### 2. FileZilla ile Bağlanın
```
Host: ftp.sitenizadı.com (veya IP adresi)
Kullanıcı Adı: hosting_kullanıcı_adınız
Şifre: hosting_şifreniz
Port: 21 (FTP) veya 22 (SFTP)
```

### 3. Dosyaları Yükleyin
- Sol panelde bilgisayarınızdaki proje klasörünü seçin
- Sağ panelde `public_html` klasörüne gidin
- Tüm dosyaları sürükleyip bırakın veya sağ tıklayıp "Upload" seçin

### 4. Kurulum Scriptini Çalıştırın
1. Tarayıcıdan `https://siteniz.com/install.php` adresini açın
2. Şifre: **kurulum2024**
3. Kurulum adımlarını takip edin:
   - Sistem kontrolü
   - Veritabanı ayarları
   - Veritabanı kurulumu
   - Test ve tamamlama

### 5. Veritabanı Bilgilerini Ayarlayın
Eğer farklı veritabanı bilgileri kullanmanız gerekiyorsa:

1. FileZilla'da `db.php` dosyasını bulun
2. Sağ tıklayıp "Download" ile indirin
3. Not defteri ile açın
4. Şu satırları düzenleyin:
```php
$servername = "localhost";        // MySQL host
$username = "sizin_db_kullanici"; // DB kullanıcı adı
$password = "sizin_db_sifre";     // DB şifresi
$dbname = "gokhanaydinli_db";     // DB adı
```
5. Dosyayı kaydedin ve tekrar yükleyin

### 6. Manuel Veritabanı Kurulumu (Gerekirse)
Eğer otomatik kurulum çalışmazsa:

1. Hosting panelindeki **phpMyAdmin**'e girin
2. Yeni veritabanı oluşturun: `gokhanaydinli_db`
3. **Import** sekmesine tıklayın
4. `sql/gokhanaydinli_db_complete.sql` dosyasını seçin
5. **Go** butonuna tıklayın

### 7. Test ve Temizlik
1. `https://siteniz.com` adresini açın
2. `https://siteniz.com/dashboard/` admin panelini test edin
3. **ÖNEMLİ:** `install.php` dosyasını silin!

## 🔑 Varsayılan Giriş Bilgileri
- **Email:** admin@gokhanaydinli.com
- **Şifre:** admin123

## 🆘 Sorun Giderme

### Bağlantı Sorunları
- FTP bilgilerini hosting sağlayıcınızdan kontrol edin
- Pasif mod ayarını deneyin (FileZilla → Edit → Settings → FTP → Passive mode)

### Dosya İzin Sorunları
FileZilla'da şu klasörlere sağ tıklayıp "File permissions" → 755:
- images/
- images/properties/
- images/blog/
- dashboard/uploads/

### Veritabanı Sorunları
- Hosting panelinde veritabanı limitlerini kontrol edin
- PHP sürümünün 7.4+ olduğundan emin olun
- MySQL sürümünün 5.7+ olduğundan emin olun

## ✅ Başarılı Kurulum Kontrolü
1. Ana sayfa açılıyor mu?
2. Admin paneline giriş yapabiliyor musunuz?
3. Resimler görünüyor mu?
4. İletişim formu çalışıyor mu?

---
**Not:** Bu rehberi kurulum tamamlandıktan sonra silebilirsiniz.
