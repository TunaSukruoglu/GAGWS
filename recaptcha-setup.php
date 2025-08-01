<?php
/**
 * Bot Koruması: Sadece reCAPTCHA v3
 * ==================================
 * 
 * Matematik sorusu kaldırıldı, sadece reCAPTCHA v3 ile devam ediyoruz.
 * reCAPTCHA v3 tek başına %95+ bot koruması sağlar.
 * 
 * Aktif koruma katmanları:
 * ✅ 1. Honeypot field (gizli alan)
 * ✅ 2. Rate limiting (5 dakikada max 3 kayıt)
 * ✅ 3. reCAPTCHA v3 (AI tabanlı)
 * ✅ 4. Form timing (minimum 3 saniye)
 * 
 * Google reCAPTCHA v3 Kurulum:
 * ===========================
 */

// Google reCAPTCHA v3 Keys - GÜNCEL
define('RECAPTCHA_SITE_KEY', '6LEp_JIrAAAAKvm2JosEDBZrOjmfZr9FTl7eX');
define('RECAPTCHA_SECRET_KEY', '6LEp_JIrAAAABwtbJPMGJzqjSQ8WHeffhy9TzyOny');

/**
 * Kurulum Adımları:
 * 
 * 1. Google reCAPTCHA Console'a gidin:
 *    https://www.google.com/recaptcha/admin/create
 * 
 * 2. Yeni site ekleyin:
 *    - Label: "Gökhan Aydınlı Gayrimenkul"
 *    - reCAPTCHA type: reCAPTCHA v3
 *    - Domains: gokhanaydinli.com
 * 
 * 3. Key'leri alın ve aşağıdaki dosyalarda değiştirin:
 * 
 * index.php (2 yerde):
 * - Satır ~664: '6LcYourSiteKeyHere' -> Gerçek Site Key
 * - Satır ~1632: '6LcYourSiteKeyHere' -> Gerçek Site Key
 * 
 * register.php (1 yerde):
 * - Satır ~70: '6LcYourSecretKeyHere' -> Gerçek Secret Key
 */

echo "🔧 reCAPTCHA v3 Kurulum Durumu\n";
echo "==============================\n\n";

if (RECAPTCHA_SITE_KEY === '6LcYourSiteKeyHere_REPLACE_ME') {
    echo "❌ SITE KEY henüz girilmedi\n";
} else {
    echo "✅ SITE KEY girildi: " . substr(RECAPTCHA_SITE_KEY, 0, 10) . "...\n";
}

if (RECAPTCHA_SECRET_KEY === '6LcYourSecretKeyHere_REPLACE_ME') {
    echo "❌ SECRET KEY henüz girilmedi\n";
} else {
    echo "✅ SECRET KEY girildi: " . substr(RECAPTCHA_SECRET_KEY, 0, 10) . "...\n";
}

echo "\n📋 Kurulum Adımları:\n";
echo "1. Google reCAPTCHA Console'a git\n";
echo "2. Site oluştur (gokhanaydinli.com)\n";
echo "3. Key'leri bu dosyaya yapıştır\n";
echo "4. index.php ve register.php'deki placeholder'ları değiştir\n";
echo "5. Test et!\n";
?>
