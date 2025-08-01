<?php
// Profil resmi gösterme servisi

// Güvenlik kontrolü
if (!isset($_GET['image'])) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

$image_name = basename($_GET['image']); // Güvenlik için
$image_path = __DIR__ . '/images/profiles/' . $image_name;

// Dosya mevcut mu kontrol et
if (!file_exists($image_path)) {
    header('HTTP/1.0 404 Not Found');
    exit;
}

// Dosya türünü belirle
$finfo = finfo_open(FILEINFO_MIME_TYPE);
$mime_type = finfo_file($finfo, $image_path);
finfo_close($finfo);

// Sadece resim dosyalarına izin ver
if (!in_array($mime_type, ['image/jpeg', 'image/png', 'image/gif', 'image/webp'])) {
    header('HTTP/1.0 403 Forbidden');
    exit;
}

// Caching headers
$last_modified = filemtime($image_path);
$etag = md5_file($image_path);

header('Content-Type: ' . $mime_type);
header('Content-Length: ' . filesize($image_path));
header('Last-Modified: ' . gmdate('D, d M Y H:i:s', $last_modified) . ' GMT');
header('ETag: "' . $etag . '"');
header('Cache-Control: public, max-age=31536000'); // 1 year cache

// Browser cache kontrolü
if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && 
    strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) == $last_modified) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}

if (isset($_SERVER['HTTP_IF_NONE_MATCH']) && 
    trim($_SERVER['HTTP_IF_NONE_MATCH'], '"') == $etag) {
    header('HTTP/1.1 304 Not Modified');
    exit;
}

// Resmi göster
readfile($image_path);
?>
