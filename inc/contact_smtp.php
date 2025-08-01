<?php
session_start();

// Debug için log dosyası
$debug_log = '../debug_contact.txt';
file_put_contents($debug_log, date('Y-m-d H:i:s') . " - SMTP Contact form accessed\n", FILE_APPEND);
file_put_contents($debug_log, "POST data: " . print_r($_POST, true) . "\n", FILE_APPEND);

/*
 *  CONFIGURE EVERYTHING HERE
 */

// E-posta ayarları
$sendTo = 'info@gokhanaydinli.com'; // İletişim formundan gelen mailler
$subject = 'Web Sitesinden Yeni Mesaj - Gökhan Aydınlı';

// form field names and their translations.
$fields = array('name' => 'Ad Soyad', 'email' => 'E-posta', 'message' => 'Mesaj'); 

// message that will be displayed when everything is OK :)
$okMessage = 'Teşekkürler! Mesajınız başarıyla gönderildi. En kısa sürede size dönüş yapacağız.';

// If something goes wrong, we will display this message.
$errorMessage = 'Mesaj gönderilirken bir hata oluştu. Lütfen daha sonra tekrar deneyin.';

/*
 *  LET'S DO THE SENDING
 */

error_reporting(E_ALL & ~E_NOTICE);

try
{
    file_put_contents($debug_log, "Try block started\n", FILE_APPEND);
    
    if(count($_POST) == 0) {
        file_put_contents($debug_log, "Form is empty - no POST data\n", FILE_APPEND);
        throw new \Exception('Form is empty');
    }
    
    file_put_contents($debug_log, "POST data received, proceeding with validation\n", FILE_APPEND);
    
    // Basit validasyon
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $message = trim($_POST['message']);
    
    file_put_contents($debug_log, "Name: $name, Email: $email, Message length: " . strlen($message) . "\n", FILE_APPEND);
    
    if (empty($name) || empty($email) || empty($message)) {
        file_put_contents($debug_log, "Validation failed - empty fields\n", FILE_APPEND);
        throw new \Exception('Tüm alanları doldurun');
    }
    
    if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        file_put_contents($debug_log, "Email validation failed\n", FILE_APPEND);
        throw new \Exception('Geçerli e-posta adresi girin');
    }
    
    file_put_contents($debug_log, "Validation passed, preparing email\n", FILE_APPEND);
            
    $emailText = "Gökhan Aydınlı web sitesinden yeni mesaj\n=============================\n\n";

    foreach ($_POST as $key => $value) {
        // If the field exists in the $fields array, include it in the email 
        if (isset($fields[$key])) {
            $emailText .= "$fields[$key]: $value\n";
        }
    }

    // 1. Dosyaya kaydet (backup olarak)
    $messages_file = '../messages.txt';
    $message_content = "=== YENİ MESAJ ===" . date('Y-m-d H:i:s') . " ===\n";
    $message_content .= "Ad Soyad: $name\n";
    $message_content .= "E-posta: $email\n";
    $message_content .= "Mesaj: $message\n";
    $message_content .= "IP: " . ($_SERVER['REMOTE_ADDR'] ?? 'Bilinmiyor') . "\n";
    $message_content .= "================================\n\n";
    
    file_put_contents($messages_file, $message_content, FILE_APPEND);
    file_put_contents($debug_log, "Message saved to file successfully\n", FILE_APPEND);

    // 2. E-posta gönder - Şükrü'nün sunucusu üzerinden
    file_put_contents($debug_log, "Attempting to send email via external server\n", FILE_APPEND);
    
    // E-posta API'sine gönderilecek data
    $emailData = array(
        'to' => $sendTo,
        'subject' => $subject,
        'message' => $emailText,
        'from_name' => $name,
        'from_email' => $email,
        'reply_to' => $email
    );
    
    // cURL ile e-posta gönder - İki alternatif adres
    $api_urls = [
        'https://sunucu.senersukruoglu.com/email-api.php',
        'http://sunucu.senersukruoglu.com/email-api.php',
        'https://152.53.126.180/email-api.php',
        'http://152.53.126.180/email-api.php'
    ];
    
    $success = false;
    $lastError = '';
    
    foreach ($api_urls as $api_url) {
        file_put_contents($debug_log, "Trying API URL: $api_url\n", FILE_APPEND);
        
        $ch = curl_init();
        curl_setopt($ch, CURLOPT_URL, $api_url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($emailData));
        curl_setopt($ch, CURLOPT_HTTPHEADER, array(
            'Content-Type: application/json',
            'X-API-Key: gokhan-aydinli-2025'
        ));
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_TIMEOUT, 10);
        curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
        
        $response = curl_exec($ch);
        $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);
        
        file_put_contents($debug_log, "API: $api_url - HTTP $httpCode - Response: $response\n", FILE_APPEND);
        
        if ($curlError) {
            $lastError = "cURL Error: $curlError";
            file_put_contents($debug_log, "API: $api_url - $lastError\n", FILE_APPEND);
            continue; // Sonraki URL'yi dene
        }
        
        if ($httpCode == 200) {
            $responseData = json_decode($response, true);
            if (isset($responseData['success']) && $responseData['success']) {
                file_put_contents($debug_log, "Email sent successfully via: $api_url\n", FILE_APPEND);
                $success = true;
                $success_message = "Mesajınız başarıyla gönderildi ve e-posta olarak iletildi! 📧";
                break; // Başarılı oldu, döngüden çık
            } else {
                $lastError = "API Error: " . ($responseData['error'] ?? 'Unknown error');
                file_put_contents($debug_log, "API: $api_url - $lastError\n", FILE_APPEND);
            }
        } else {
            $lastError = "HTTP Error: $httpCode";
            file_put_contents($debug_log, "API: $api_url - $lastError\n", FILE_APPEND);
        }
    }
    
    // Hiçbir API çalışmadıysa
    if (!$success) {
        file_put_contents($debug_log, "All APIs failed. Last error: $lastError\n", FILE_APPEND);
        $success_message = "Mesajınız kaydedildi ancak e-posta servisleri şu anda kullanılamıyor.";
    }
    
}
catch (\Exception $e)
{
    file_put_contents($debug_log, "Exception caught: " . $e->getMessage() . "\n", FILE_APPEND);
    error_log("Contact form error: " . $e->getMessage());
    
    // Hata durumunda da basit sayfa göster
    ?>
    <!DOCTYPE html>
    <html>
    <head>
        <meta charset="UTF-8">
        <title>Hata</title>
        <style>
            body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
            .error { color: red; }
            .link { color: blue; text-decoration: none; }
        </style>
    </head>
    <body>
        <div>
            <h2 class="error">❌ Hata Oluştu</h2>
            <p><?php echo htmlspecialchars($e->getMessage()); ?></p>
            <p>3 saniye sonra iletişim sayfasına yönlendirileceksiniz...</p>
            <script>
                setTimeout(function() {
                    window.location.href = '../contact.php';
                }, 3000);
            </script>
            <p><a href="../contact.php" class="link">Hemen geri dön</a></p>
        </div>
    </body>
    </html>
    <?php
    exit;
}

file_put_contents($debug_log, "Success - redirecting to contact.php\n", FILE_APPEND);

?>
<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>Mesaj Gönderildi</title>
    <style>
        body { font-family: Arial, sans-serif; text-align: center; margin-top: 100px; }
        .success { color: green; }
        .info { color: #666; margin-top: 20px; }
        .link { color: blue; text-decoration: none; }
    </style>
</head>
<body>
    <div>
        <h2 class="success">✅ Mesajınız başarıyla gönderildi!</h2>
        <p><?php echo isset($success_message) ? $success_message : $okMessage; ?></p>
        <div class="info">
            <p><strong>Gönderilen Bilgiler:</strong></p>
            <p><strong>Ad Soyad:</strong> <?php echo htmlspecialchars($name); ?></p>
            <p><strong>E-posta:</strong> <?php echo htmlspecialchars($email); ?></p>
            <p><strong>Mesaj:</strong> <?php echo htmlspecialchars(substr($message, 0, 100)); ?><?php echo strlen($message) > 100 ? '...' : ''; ?></p>
        </div>
        <p>3 saniye sonra iletişim sayfasına yönlendirileceksiniz...</p>
        <script>
            setTimeout(function() {
                window.location.href = '../contact.php';
            }, 3000);
        </script>
        <p><a href="../contact.php" class="link">Hemen geri dön</a></p>
    </div>
</body>
</html>
<?php
exit;
?>
