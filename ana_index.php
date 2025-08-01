<?php
/**
 * Ana dizinde index.php - Yönlendirme Dosyası
 * Bu dosya ziyaretçileri mevcut site veya test sitesine yönlendirir
 */

// Test modu (true = test sitesi, false = mevcut site)
$test_mode = false;

// Admin erişimi için özel parametre
$admin_access = isset($_GET['admin']) && $_GET['admin'] === 'test2024';

// Eğer test modundaysa veya admin erişimi varsa yeni siteyi göster
if ($test_mode || $admin_access) {
    // Yeni sitenin index.php dosyasını dahil et
    if (file_exists('yeni/index.php')) {
        include 'yeni/index.php';
    } else {
        echo "Test sitesi henüz yüklenmedi.";
    }
    exit;
}

// Normal ziyaretçiler için mevcut index.html'i göster
if (file_exists('index.html')) {
    include 'index.html';
} else {
    echo "Ana site bulunamadı.";
}
?>

<!--
Kullanım:
- Normal ziyaretçiler: https://siteniz.com/ (mevcut index.html gösterilir)
- Test erişimi: https://siteniz.com/?admin=test2024 (yeni site gösterilir)
- Test sitesi: https://siteniz.com/yeni/ (direkt test sitesine erişim)
-->
