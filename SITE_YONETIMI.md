# 🔄 Site Yönetim Rehberi - Test ve Ana Site

## 📁 Seçenek 1: "yeni" Klasörü ile Test

### Kurulum Adımları:
1. **Klasör oluşturun:**
   ```
   public_html/
   ├── index.html (mevcut siteniz)
   ├── yeni/ (yeni gayrimenkul sitesi)
   │   ├── index.php
   │   ├── dashboard/
   │   ├── images/
   │   └── ... (tüm dosyalar)
   ```

2. **Tüm gayrimenkul dosyalarını `yeni/` klasörüne yükleyin**

3. **Test edin:**
   - Ana site: `https://siteniz.com/` (mevcut index.html)
   - Test sitesi: `https://siteniz.com/yeni/`
   - Admin test: `https://siteniz.com/yeni/dashboard/`

4. **Memnun kaldığınızda:**
   - `yeni/` klasöründeki tüm dosyaları ana dizine taşıyın
   - `index.html`'i yedekleyin
   - `index.php`'yi ana dizine koyun

---

## 🔄 Seçenek 2: Ana Dizine Kurulum + Geçici Gösterim

### .htaccess ile Yönlendirme:
```apache
# Mevcut index.html'i göster (geçici)
DirectoryIndex index.html index.php

# Test erişimi için
RewriteEngine On

# Özel parametre ile yeni siteye erişim
RewriteCond %{QUERY_STRING} ^admin=test2024$
RewriteRule ^$ index.php [L]

# Normal ziyaretçiler index.html görür
```

### Kurulum Adımları:
1. **Tüm dosyaları ana dizine yükleyin**
2. **`.htaccess` dosyasını düzenleyin** (yukarıdaki kodu ekleyin)
3. **Test edin:**
   - Normal: `https://siteniz.com/` (index.html)
   - Test: `https://siteniz.com/?admin=test2024` (yeni site)

---

## 🎯 Önerilen Yöntem

**"yeni" klasörü yöntemi** daha güvenli:
- Mevcut siteniz etkilenmez
- Testleri rahatça yapabilirsiniz
- Sorun olursa geri dönüş kolay

### FileZilla ile "yeni" Klasörü Kurulumu:

1. **FileZilla'da sağ panelde:**
   - `public_html`'e gidin
   - Sağ tık → "Create directory" → "yeni"

2. **"yeni" klasörüne girin ve tüm dosyaları yükleyin**

3. **Kurulum için:**
   - `https://siteniz.com/yeni/install.php`
   - Şifre: `kurulum2024`

4. **Test:**
   - `https://siteniz.com/yeni/`
   - `https://siteniz.com/yeni/dashboard/`

### Veritabanı İsimleri:
```
Test: gokhanaydinli_test (veya başka bir isim)
Ana:  gokhanaydinli_db
```

---

## 🔄 Canlıya Alma (Hazır Olduğunuzda)

1. **Mevcut index.html'i yedekleyin:**
   - `index_backup.html` olarak kaydedin

2. **"yeni" klasöründen ana dizine taşıma:**
   ```
   yeni/index.php → index.php
   yeni/dashboard/ → dashboard/
   yeni/images/ → images/
   ... (tüm dosyalar)
   ```

3. **Veritabanını değiştirin:**
   - `db.php`'de veritabanı adını `gokhanaydinli_db` yapın

4. **Test edin:**
   - Ana site artık yeni gayrimenkul sitesi olur

---

**Hangi yöntemi tercih ediyorsunuz? Size göre adımları detaylandırabilirim.**
