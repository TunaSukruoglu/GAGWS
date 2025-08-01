<?php
session_start();

// Basit e-posta sistemi - Sadece WhatsApp'a yönlendirme
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['name'], $_POST['email'], $_POST['subject'], $_POST['message'])) {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    
    // Doğrulama
    $errors = [];
    if (empty($name)) $errors[] = "Ad-Soyad boş olamaz";
    if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) $errors[] = "Geçerli bir e-posta adresi girin";
    if (empty($subject)) $errors[] = "Konu boş olamaz";
    if (empty($message)) $errors[] = "Mesaj boş olamaz";
    
    if (empty($errors)) {
        // Yerel kayıt
        $log_entry = date('Y-m-d H:i:s') . " | $name | $email | $subject | $message\n";
        file_put_contents('messages.txt', $log_entry, FILE_APPEND | LOCK_EX);
        
        // WhatsApp mesajı oluştur
        $whatsapp_message = urlencode("
🔔 *Yeni İletişim Formu Mesajı*

👤 *Ad:* $name
📧 *E-posta:* $email
📝 *Konu:* $subject

💬 *Mesaj:*
$message

⏰ *Tarih:* " . date('d.m.Y H:i:s'));
        
        $whatsapp_url = "https://wa.me/905555555555?text=$whatsapp_message"; // Telefon numaranızı yazın
        
        $_SESSION['success'] = "✅ Mesajınız kaydedildi! WhatsApp üzerinden de bilgilendirildiniz.";
        $_SESSION['whatsapp_url'] = $whatsapp_url;
        
        header('Location: ' . $_SERVER['PHP_SELF']);
        exit;
    } else {
        $_SESSION['error'] = implode('<br>', $errors);
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Basit İletişim Sistemi</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 600px; margin: 50px auto; padding: 20px; }
        .message { padding: 15px; margin: 15px 0; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; border: 1px solid #c3e6cb; }
        .error { background: #f8d7da; color: #721c24; border: 1px solid #f5c6cb; }
        form { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        input, textarea { width: 100%; padding: 10px; margin: 10px 0; border: 1px solid #ddd; border-radius: 3px; box-sizing: border-box; }
        button { background: #25d366; color: white; padding: 12px 24px; border: none; border-radius: 3px; cursor: pointer; margin: 5px; }
        button:hover { background: #22c55e; }
        .whatsapp-btn { background: #25d366; text-decoration: none; color: white; padding: 10px 20px; border-radius: 5px; display: inline-block; }
    </style>
</head>
<body>
    <h2>💬 Basit İletişim Sistemi</h2>
    
    <?php if (isset($_SESSION['success'])): ?>
        <div class="message success"><?= $_SESSION['success'] ?></div>
        <?php if (isset($_SESSION['whatsapp_url'])): ?>
            <p><a href="<?= $_SESSION['whatsapp_url'] ?>" target="_blank" class="whatsapp-btn">📱 WhatsApp'ta Açın</a></p>
            <?php unset($_SESSION['whatsapp_url']); ?>
        <?php endif; ?>
        <?php unset($_SESSION['success']); ?>
    <?php endif; ?>
    
    <?php if (isset($_SESSION['error'])): ?>
        <div class="message error"><?= $_SESSION['error'] ?></div>
        <?php unset($_SESSION['error']); ?>
    <?php endif; ?>
    
    <form method="post">
        <input type="text" name="name" placeholder="Ad-Soyad" required>
        <input type="email" name="email" placeholder="E-posta Adresiniz" required>
        <input type="text" name="subject" placeholder="Konu" required>
        <textarea name="message" rows="5" placeholder="Mesajınız" required></textarea>
        <button type="submit">📝 Mesaj Gönder</button>
    </form>
    
    <div style="background: #fff3cd; padding: 15px; border-radius: 5px; margin: 20px 0;">
        <h3>📞 Direk İletişim</h3>
        <p>📱 WhatsApp: <a href="https://wa.me/905555555555" target="_blank">+90 555 555 55 55</a></p>
        <p>📧 E-posta: sukru@keypadsistem.com</p>
        <p>📞 Telefon: +90 555 555 55 55</p>
    </div>
    
    <p><small>✅ Tüm mesajlar yerel olarak kaydedilir ve WhatsApp üzerinden bildirim gönderilir.</small></p>
</body>
</html>
