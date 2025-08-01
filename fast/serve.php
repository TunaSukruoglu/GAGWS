<?php
// 🚀 STATIC FILE SERVER - Ultra Fast Asset Delivery
header('Cache-Control: public, max-age=86400'); // 1 gün cache
header('Expires: ' . gmdate('D, d M Y H:i:s \G\M\T', time() + 86400));

$file = $_GET['f'] ?? '';
$type = $_GET['t'] ?? '';

// Security check
if (preg_match('/\.\.\/|\.\.\\\\/', $file)) {
    http_response_code(403);
    exit('Access denied');
}

// File types
$types = [
    'css' => 'text/css',
    'js' => 'application/javascript',
    'json' => 'application/json',
    'html' => 'text/html'
];

if (!isset($types[$type])) {
    http_response_code(400);
    exit('Invalid type');
}

header('Content-Type: ' . $types[$type]);

// Serve file
$filepath = __DIR__ . '/' . $file;
if (file_exists($filepath)) {
    // Enable gzip compression
    if (extension_loaded('zlib') && !ob_get_level()) {
        ob_start('ob_gzhandler');
    }
    
    readfile($filepath);
} else {
    http_response_code(404);
    echo '/* File not found */';
}
?>
