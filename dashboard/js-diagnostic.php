<?php
header('Content-Type: application/json');

// JavaScript Module Diagnostic Report
$diagnostics = [
    'timestamp' => date('Y-m-d H:i:s'),
    'modules' => [],
    'status' => 'OK'
];

$js_files = [
    'property-wizard.js' => 25909,
    'photo-upload-system.js' => 20084,
    'location-manager.js' => 9502,
    'cloudflare-images.js' => 8169,
    'form-handlers.js' => 3769,
    'add-property.js' => 4966
];

foreach ($js_files as $filename => $expected_size) {
    $filepath = __DIR__ . '/assets/js/' . $filename;
    
    if (file_exists($filepath)) {
        $actual_size = filesize($filepath);
        $line_count = count(file($filepath));
        
        $diagnostics['modules'][$filename] = [
            'expected_size' => $expected_size,
            'actual_size' => $actual_size,
            'line_count' => $line_count,
            'status' => $actual_size === $expected_size ? 'OK' : 'CONTAMINATED',
            'last_modified' => filemtime($filepath),
            'version_param' => '1.2.' . filemtime($filepath)
        ];
        
        if ($actual_size !== $expected_size) {
            $diagnostics['status'] = 'ISSUE_DETECTED';
        }
    } else {
        $diagnostics['modules'][$filename] = [
            'status' => 'MISSING'
        ];
        $diagnostics['status'] = 'ISSUE_DETECTED';
    }
}

echo json_encode($diagnostics, JSON_PRETTY_PRINT);
?>
