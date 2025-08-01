<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🖼️ Resim Sıkıştırma Rehberi</title>
    <style>
        body { font-family: Arial, sans-serif; line-height: 1.6; margin: 20px; background: #f4f4f4; }
        .container { max-width: 800px; margin: 0 auto; background: white; padding: 20px; border-radius: 10px; box-shadow: 0 0 10px rgba(0,0,0,0.1); }
        .tip { background: #e8f4fd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #007cba; }
        .warning { background: #fff3cd; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #ffc107; }
        .success { background: #d4edda; padding: 15px; border-radius: 5px; margin: 15px 0; border-left: 5px solid #28a745; }
        .tool { background: #f8f9fa; padding: 15px; border-radius: 5px; margin: 10px 0; border: 1px solid #dee2e6; }
    </style>
</head>
<body>
    <div class="container">
        <h1>🖼️ Resim Sıkıştırma Rehberi</h1>
        
        <div class="warning">
            <h3>⚠️ Hosting Limitiniz</h3>
            <p>Hosting sağlayıcınız maksimum <strong><?php echo ini_get('upload_max_filesize'); ?></strong> boyutunda dosya yüklemeye izin veriyor.</p>
            <p>Bu limiti aşan dosyalar yüklenemez.</p>
        </div>

        <h2>🛠️ Online Sıkıştırma Araçları</h2>
        
        <div class="tool">
            <h4>1. TinyPNG (Önerilen)</h4>
            <p><strong>Link:</strong> <a href="https://tinypng.com" target="_blank">https://tinypng.com</a></p>
            <p><strong>Avantajlar:</strong></p>
            <ul>
                <li>Kaliteyi koruyarak %50-80 küçültme</li>
                <li>PNG ve JPEG desteği</li>
                <li>Ücretsiz (20 dosya/gün)</li>
                <li>Çok hızlı</li>
            </ul>
        </div>

        <div class="tool">
            <h4>2. Compressor.io</h4>
            <p><strong>Link:</strong> <a href="https://compressor.io" target="_blank">https://compressor.io</a></p>
            <p><strong>Avantajlar:</strong></p>
            <ul>
                <li>4 farklı sıkıştırma seviyesi</li>
                <li>JPEG, PNG, GIF, SVG desteği</li>
                <li>Ücretsiz</li>
            </ul>
        </div>

        <div class="tool">
            <h4>3. ImageOptim (Mac) / FileOptimizer (Windows)</h4>
            <p><strong>Avantajlar:</strong></p>
            <ul>
                <li>Masaüstü uygulama</li>
                <li>Toplu işlem</li>
                <li>Metadata temizleme</li>
            </ul>
        </div>

        <h2>📱 Telefon Uygulamaları</h2>
        
        <div class="tool">
            <h4>Android: Photo Compressor</h4>
            <p>Google Play Store'dan indirebilirsiniz</p>
        </div>

        <div class="tool">
            <h4>iOS: Image Size</h4>
            <p>App Store'dan indirebilirsiniz</p>
        </div>

        <h2>💡 Hızlı Çözümler</h2>

        <div class="success">
            <h4>✅ Anında Çözüm</h4>
            <ol>
                <li>Resminizi <a href="https://tinypng.com" target="_blank">TinyPNG</a>'ye yükleyin</li>
                <li>Sıkıştırılmış versiyonu indirin</li>
                <li>Test sayfasında tekrar deneyin</li>
            </ol>
        </div>

        <div class="tip">
            <h4>📐 Boyut Önerileri</h4>
            <ul>
                <li><strong>Emlak fotoğrafları:</strong> 1920x1080 (Full HD) yeterli</li>
                <li><strong>Detay fotoğrafları:</strong> 1280x720 yeterli</li>
                <li><strong>Kalite ayarı:</strong> %80-85 ideal</li>
                <li><strong>Hedef boyut:</strong> 500KB - 2MB arası</li>
            </ul>
        </div>

        <h2>🔧 Gelişmiş Teknikler</h2>

        <div class="tool">
            <h4>WebP Format</h4>
            <p>Modern tarayıcılar için %25-50 daha küçük dosyalar</p>
            <p><strong>Araç:</strong> <a href="https://squoosh.app" target="_blank">Squoosh.app</a></p>
        </div>

        <div class="tool">
            <h4>Progressive JPEG</h4>
            <p>Aşamalı yükleme için ideal</p>
            <p>TinyPNG otomatik olarak progressive yapar</p>
        </div>

        <h2>📊 Sonuç</h2>
        
        <div class="success">
            <p><strong>🎯 Hedef:</strong> Resimlerinizi <?php echo ini_get('upload_max_filesize'); ?> limitinin altında tutun</p>
            <p><strong>💡 Tavsiye:</strong> 1-2MB arası resimler hem kaliteli hem hızlı yüklenir</p>
            <p><strong>🚀 Sonuç:</strong> Cloudflare ile birlikte mükemmel performans!</p>
        </div>

        <p style="text-align: center; margin-top: 30px;">
            <a href="test-cloudflare-images.php" style="background: #007cba; color: white; padding: 12px 20px; text-decoration: none; border-radius: 5px;">
                📸 Test Sayfasına Dön
            </a>
        </p>
    </div>
</body>
</html>
