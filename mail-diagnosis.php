<?php
echo "<h1>🔧 Mail Hesabı Test</h1>";
echo "<hr>";

// Mevcut hosting bilgileri
echo "<div style='background: #e7f3ff; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>📋 Mevcut Durum</h3>";
echo "<p><strong>Sunucu:</strong> cp24.hosting.sh.com.tr (✅ Bağlantı başarılı)</p>";
echo "<p><strong>Port:</strong> 587 STARTTLS (✅ TLS başarılı)</p>";
echo "<p><strong>Sorun:</strong> ❌ Kimlik doğrulama başarısız</p>";
echo "<p><strong>Hata:</strong> 535 Incorrect authentication data</p>";
echo "</div>";

echo "<div style='background: #fff3cd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>💡 Çözüm Önerileri</h3>";
echo "<ol>";
echo "<li><strong>Hosting panelinde şifreyi kontrol edin</strong><br>";
echo "   - cPanel > Email Accounts bölümüne gidin<br>";
echo "   - root@gokhanaydinli.com hesabının durumunu kontrol edin</li>";

echo "<li><strong>Şifreyi sıfırlayın</strong><br>";
echo "   - Hosting panelinde şifreyi yeniden belirleyin<br>";
echo "   - Yeni şifreyi contact.php'de güncelleyin</li>";

echo "<li><strong>Hesap var mı kontrol edin</strong><br>";
echo "   - root@gokhanaydinli.com hesabı hosting panelinde tanımlı mı?<br>";
echo "   - Yoksa önce hesabı oluşturun</li>";

echo "<li><strong>Alternatif hesap kullanın</strong><br>";
echo "   - info@gokhanaydinli.com gibi başka bir hesap deneyin<br>";
echo "   - Veya yeni bir e-posta hesabı oluşturun</li>";
echo "</ol>";
echo "</div>";

echo "<div style='background: #f8d7da; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
echo "<h3>⚠️ Önemli</h3>";
echo "<p>Mail sunucuya bağlantı %100 başarılı. Sadece kullanıcı adı/şifre yanlış.</p>";
echo "<p>Bu durumda hosting sağlayıcınızla iletişime geçmeniz gerekebilir.</p>";
echo "</div>";

echo "<hr>";
echo "<p><a href='simple-mail-test.php' style='background: #007bff; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🔙 Test Sayfasına Dön</a></p>";
?>
