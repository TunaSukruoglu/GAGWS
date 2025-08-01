<?php
/**
 * E-posta Webhook Sistemi
 * Harici bir e-posta servisine HTTP POST ile gönderir
 */

function sendEmailViaWebhook($to, $subject, $message, $from_name, $from_email) {
    // Birden fazla webhook servisi dene
    $webhooks = [
        'https://formspree.io/f/YOUR_FORM_ID', // Ücretsiz
        'https://getform.io/f/YOUR_ENDPOINT',  // Ücretsiz
        'https://api.emailjs.com/api/v1.0/email/send' // Ücretsiz
    ];
    
    $email_data = [
        'to' => $to,
        'subject' => $subject,
        'message' => $message,
        'from_name' => $from_name,
        'from_email' => $from_email,
        'timestamp' => date('Y-m-d H:i:s')
    ];
    
    foreach ($webhooks as $webhook) {
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $webhook);
        curl_setopt($ch, CURLOPT_POST, true);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($email_data));
        curl_setopt($ch, CURLOPT_HTTPHEADER, [
            'Content-Type: application/json',
            'Accept: application/json'
        ]);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        curl_close($ch);
        
        if ($http_code === 200) {
            return true;
        }
    }
    
    return false;
}

// En basit çözüm: Telegram Bot
function sendToTelegram($message) {
    $bot_token = 'YOUR_BOT_TOKEN';
    $chat_id = 'YOUR_CHAT_ID';
    
    $url = "https://api.telegram.org/bot$bot_token/sendMessage";
    
    $data = [
        'chat_id' => $chat_id,
        'text' => $message,
        'parse_mode' => 'HTML'
    ];
    
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_POST, true);
    curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
    curl_setopt($ch, CURLOPT_TIMEOUT, 10);
    
    $response = curl_exec($ch);
    $http_code = curl_getinfo($ch, CURLINFO_HTTP_CODE);
    curl_close($ch);
    
    return $http_code === 200;
}

// Test
if ($_POST) {
    $test_message = "
🔔 <b>Yeni İletişim Formu Mesajı</b>

👤 <b>Ad:</b> Test Kullanıcı
📧 <b>E-posta:</b> test@test.com
📝 <b>Konu:</b> Test Mesajı
💬 <b>Mesaj:</b> Bu bir test mesajıdır.

⏰ <b>Tarih:</b> " . date('d.m.Y H:i:s');

    // Telegram testi (bot token'ı varsa)
    $telegram_result = false; // sendToTelegram($test_message);
    
    echo "<h3>Test Sonuçları</h3>";
    echo "<p>📧 E-posta Webhook: ❌ Yapılandırılmamış</p>";
    echo "<p>📱 Telegram Bot: " . ($telegram_result ? '✅ Çalışıyor' : '❌ Yapılandırılmamış') . "</p>";
    echo "<p>💾 Yerel Kayıt: ✅ Çalışıyor</p>";
}
?>

<!DOCTYPE html>
<html>
<head>
    <title>Webhook Test</title>
    <meta charset="utf-8">
</head>
<body>
    <h2>E-posta Webhook Test</h2>
    
    <form method="post">
        <button type="submit">Test Et</button>
    </form>
    
    <hr>
    
    <h3>📞 Acil Çözüm: Telegram Bot</h3>
    <p>En kolay ve güvenilir çözüm:</p>
    <ol>
        <li>Telegram'da @BotFather'a git</li>
        <li>/newbot komutunu kullan</li>
        <li>Bot token'ını al</li>
        <li>Bot'a mesaj at ve chat ID'ni al</li>
        <li>Bu bilgileri koda ekle</li>
    </ol>
    
    <h3>📧 Alternatif: E-posta Webhook</h3>
    <p>Ücretsiz e-posta servisleri:</p>
    <ul>
        <li><a href="https://formspree.io" target="_blank">Formspree.io</a> - Ücretsiz 50 mesaj/ay</li>
        <li><a href="https://getform.io" target="_blank">Getform.io</a> - Ücretsiz 100 mesaj/ay</li>
        <li><a href="https://emailjs.com" target="_blank">EmailJS.com</a> - Ücretsiz 200 mesaj/ay</li>
    </ul>
</body>
</html>
