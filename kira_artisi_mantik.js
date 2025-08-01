/**
 * TİCARİ KİRA ARTIŞI HESAPLAMA MANTIK AÇIKLAMASI
 * ================================================
 */

// ÖRNEK SENARYO:
// Başlangıç Kira: 15.000 ₺
// Sözleşme Süresi: 3 yıl
// TÜFE: %65.5
// ÜFE: %42.5
// Sabit Artış: %25
// Karma (TÜFE + Ek): %10

/**
 * YÖNTEM 1: TÜFE BAZLI ARTŞ
 * ========================
 */
function tufeBasedCalculation() {
    let kira = 15000; // Başlangıç
    let tufeOran = 65.5; // %65.5
    
    // 1. Yıl sonunda:
    kira = kira * (1 + tufeOran / 100);
    // 15.000 * 1.655 = 24.825 ₺
    
    // 2. Yıl sonunda:
    kira = kira * (1 + tufeOran / 100);
    // 24.825 * 1.655 = 41.086 ₺
    
    // 3. Yıl sonunda:
    kira = kira * (1 + tufeOran / 100);
    // 41.086 * 1.655 = 67.997 ₺
    
    return kira;
}

/**
 * YÖNTEM 2: ÜFE BAZLI ARTŞ
 * =======================
 */
function ufeBasedCalculation() {
    let kira = 15000;
    let ufeOran = 42.5; // %42.5
    
    // Her yıl %42.5 artış
    // 1. Yıl: 15.000 * 1.425 = 21.375 ₺
    // 2. Yıl: 21.375 * 1.425 = 30.459 ₺ 
    // 3. Yıl: 30.459 * 1.425 = 43.404 ₺
    
    return kira * Math.pow(1.425, 3); // 43.404 ₺
}

/**
 * YÖNTEM 3: SABİT ORAN ARTIŞI
 * ===========================
 */
function sabitOranCalculation() {
    let kira = 15000;
    let sabitOran = 25; // %25
    
    // Sözleşmede belirtilen sabit artış
    // 1. Yıl: 15.000 * 1.25 = 18.750 ₺
    // 2. Yıl: 18.750 * 1.25 = 23.437 ₺
    // 3. Yıl: 23.437 * 1.25 = 29.296 ₺
    
    return kira * Math.pow(1.25, 3); // 29.296 ₺
}

/**
 * YÖNTEM 4: KARMA YÖNTEM
 * ======================
 */
function karmaYontem() {
    let kira = 15000;
    let tufeOran = 65.5;
    let ekOran = 10; // Sözleşmedeki ek artış
    
    let toplamOran = tufeOran + ekOran; // 75.5%
    
    // 1. Yıl: 15.000 * 1.755 = 26.325 ₺
    // 2. Yıl: 26.325 * 1.755 = 46.200 ₺
    // 3. Yıl: 46.200 * 1.755 = 81.081 ₺
    
    return kira * Math.pow(1.755, 3); // 81.081 ₺
}

/**
 * KARŞILAŞTIRMA TABLOSU
 * ====================
 */
console.log("3 YILLIK KARŞILAŞTIRMA:");
console.log("TÜFE Bazlı:     67.997 ₺ (+352.98% artış)");
console.log("ÜFE Bazlı:      43.404 ₺ (+189.36% artış)");
console.log("Sabit Oran:     29.296 ₺ (+95.31% artış)");
console.log("Karma Yöntem:   81.081 ₺ (+440.54% artış)");

/**
 * YASAL DAYANAK
 * =============
 */
// 1. Türk Borçlar Kanunu md. 344: Kira artışı sınırları
// 2. 6570 sayılı Kanun: TÜFE bazlı artış hesaplama
// 3. Sözleşme özgürlüğü: Taraflar artış yöntemini belirleyebilir
// 4. Ticari kiralamalar: Konut kiralarından farklı kurallar

/**
 * RİSK FAKTÖRLERİ
 * ===============
 */
// 1. Enflasyon Riski: Yüksek enflasyon dönemlerinde TÜFE riski
// 2. Sözleşme Riski: Artış yönteminin net tanımlanmaması
// 3. Ödenebilirlik Riski: Kiracının ödeme gücü limitleri
// 4. Piyasa Riski: Piyasa kiralarının altında/üstünde kalma

/**
 * OPTİMİZASYON ÖNERİLERİ
 * ======================
 */
// 1. Hibrit Yöntem: TÜFE + maksimum sınır belirleme
// 2. Kademeli Artış: İlk yıl düşük, sonraki yıllar artış
// 3. Piyasa Ayarlaması: Belirli dönemlerde piyasa kira kontrolü
// 4. Erken Ödeme İndirimi: Peşin ödeme teşvikleri
