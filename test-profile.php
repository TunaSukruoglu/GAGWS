<?php
echo "PHP çalışıyor!";
echo "\nResim dosyası: " . ($_GET['image'] ?? 'yok');
echo "\nDosya yolu: " . __DIR__ . '/images/profiles/' . ($_GET['image'] ?? '');
echo "\nDosya mevcut: " . (file_exists(__DIR__ . '/images/profiles/' . ($_GET['image'] ?? '')) ? 'Evet' : 'Hayır');
?>
