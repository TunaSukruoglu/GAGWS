/* Service Worker Engelleyici - VS Code Webview İçin */
/* Bu dosya TinyMCE ve diğer service worker'lardan kaynaklanan hataları engeller */

(function() {
    'use strict';
    
    // Service worker registration'ı tamamen engelle
    if ('serviceWorker' in navigator) {
        // Original register fonksiyonunu sakla
        const originalRegister = navigator.serviceWorker.register;
        
        navigator.serviceWorker.register = function() {
            console.log('🚫 Service worker registration engellendi - VS Code webview uyumluluğu');
            return Promise.reject(new Error('Service worker disabled for VS Code webview compatibility'));
        };
        
        // Mevcut service worker'ları temizle
        if (navigator.serviceWorker.controller) {
            try {
                navigator.serviceWorker.controller.postMessage('SKIP_WAITING');
            } catch (e) {
                console.log('🚫 Service worker message gönderme hatası engellendi:', e.message);
            }
        }
        
        // Service worker ready promise'ini engelle
        const originalReady = navigator.serviceWorker.ready;
        Object.defineProperty(navigator.serviceWorker, 'ready', {
            get: function() {
                return Promise.reject(new Error('Service worker ready disabled'));
            }
        });
    }

    // Global error handler - service worker hatalarını yakala
    window.addEventListener('error', function(e) {
        if (e.message && (
            e.message.toLowerCase().includes('serviceworker') || 
            e.message.toLowerCase().includes('service worker') ||
            e.message.toLowerCase().includes('registration failed') ||
            e.message.toLowerCase().includes('invalid state') ||
            e.message.toLowerCase().includes('invalidstateerror') ||
            e.message.toLowerCase().includes('could not register')
        )) {
            e.preventDefault();
            e.stopPropagation();
            console.log('🚫 Service worker hatası engellendi:', e.message);
            return false;
        }
    }, true);

    // Promise rejection handler - service worker promise hatalarını yakala
    window.addEventListener('unhandledrejection', function(e) {
        if (e.reason && e.reason.toString && (
            e.reason.toString().toLowerCase().includes('serviceworker') ||
            e.reason.toString().toLowerCase().includes('service worker') ||
            e.reason.toString().toLowerCase().includes('registration') ||
            e.reason.toString().toLowerCase().includes('invalid state') ||
            e.reason.toString().toLowerCase().includes('invalidstateerror')
        )) {
            e.preventDefault();
            console.log('🚫 Service worker promise rejection engellendi:', e.reason);
        }
    });

    // TinyMCE için özel ayarlar
    if (typeof tinymce !== 'undefined') {
        // TinyMCE'nin service worker kullanmasını engelle
        tinymce.on('init', function() {
            console.log('✅ TinyMCE service worker devre dışı bırakıldı');
        });
    }
    
    // TinyMCE init bekleme
    document.addEventListener('DOMContentLoaded', function() {
        // TinyMCE yüklendikten sonra service worker'ları engelle
        const checkTinyMCE = setInterval(function() {
            if (typeof tinymce !== 'undefined') {
                clearInterval(checkTinyMCE);
                console.log('✅ TinyMCE yüklendi, service worker engelleme aktif');
            }
        }, 100);
        
        // 10 saniye sonra kontrol etmeyi durdur
        setTimeout(function() {
            clearInterval(checkTinyMCE);
        }, 10000);
    });

    // Popup'ları da engelle (webview uyumluluğu için)
    const originalConfirm = window.confirm;
    const originalAlert = window.alert;
    
    window.confirm = function(message) { 
        console.log('🚫 Popup engellendi (confirm):', message);
        return true; 
    };

    window.alert = function(message) { 
        console.log('🚫 Popup engellendi (alert):', message);
        return true; 
    };

    // Console'da başarı mesajı
    console.log('✅ Service worker engelleyici aktif - VS Code webview uyumluluğu sağlandı');
    console.log('✅ TinyMCE ve diğer service worker hataları engellendi');

})();
