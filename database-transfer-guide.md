# Database Transfer Rehberi

## 🔄 Local'den Ana Sunucuya Database Aktarımı

### Adım 1: Local Database Export
1. Local phpMyAdmin'e girin
2. `gokhanay_db` veritabanını seçin
3. "Export" sekmesine tıklayın
4. **Quick export** yerine **Custom** seçin
5. Format: SQL
6. **Object creation options**:
   - ✅ Add DROP TABLE / VIEW / PROCEDURE / FUNCTION / EVENT / TRIGGER statement
   - ✅ Add CREATE PROCEDURE / FUNCTION / EVENT
   - ✅ Add IF NOT EXISTS (prevents errors)
7. **Data dump options**:
   - ✅ Complete inserts
   - ✅ Extended inserts
8. "Go" butonuna tıklayın

### Adım 2: Ana Sunucuda Import
1. Ana sunucu phpMyAdmin'e girin
2. `gokhanay_db` veritabanını seçin
3. "Import" sekmesine tıklayın
4. Export aldığınız .sql dosyasını seçin
5. "Go" butonuna tıklayın

### ⚠️ Önemli Notlar:
- Export'ta "DROP TABLE" seçeneğini işaretlerseniz mevcut tablolar silinip yeniden oluşturulur
- Sadece veri aktarmak istiyorsanız "Structure" seçeneğini kapatın
- Büyük dosyalar için "Max execution time" artırılmalı

### 🛠 Alternatif Yöntem: Güvenli Transfer
Eğer tabloları korumak istiyorsanız:
1. Sadece DATA export edin (Structure kapalı)
2. Ana sunucuda önce tabloları temizleyin
3. Sonra veriyi import edin
