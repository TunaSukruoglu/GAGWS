<?php
// Global Head Include - Tüm dashboard sayfaları için ortak head elemanları
?>
    <!-- Service Worker Engelleyici - VS Code Webview Uyumluluğu -->
    <script src="includes/service-worker-blocker.js"></script>
    
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="robots" content="noindex, nofollow">
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <!-- Dashboard Common CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    
    <style>
        /* Global VS Code Webview optimizasyonları */
        html, body {
            -webkit-font-smoothing: antialiased;
            -moz-osx-font-smoothing: grayscale;
        }
        
        /* Service worker error mesajlarını gizle */
        .service-worker-error {
            display: none !important;
        }
        
        /* Webview için optimizasyonlar */
        iframe {
            border: none;
            background: transparent;
        }
        
        /* Console error styling */
        .error-hidden {
            visibility: hidden;
            opacity: 0;
        }
    </style>
