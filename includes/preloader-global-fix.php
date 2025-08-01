<!-- Global Preloader Fix -->
<script>
// Global preloader fix - Tüm sayfalarda çalışır
(function() {
    'use strict';
    
    function closePreloader() {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            console.log('Global fix: Preloader bulundu, kapatılıyor...');
            preloader.style.opacity = '0';
            preloader.style.transition = 'opacity 0.5s ease';
            
            setTimeout(function() {
                preloader.style.display = 'none';
                if (preloader.parentNode) {
                    preloader.parentNode.removeChild(preloader);
                }
                console.log('Global fix: Preloader kaldırıldı');
            }, 500);
            return true;
        }
        return false;
    }
    
    // DOM yüklendiğinde
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', function() {
            setTimeout(closePreloader, 1000);
        });
    } else {
        setTimeout(closePreloader, 1000);
    }
    
    // Window yüklendiğinde
    window.addEventListener('load', closePreloader);
    
    // Acil durum için - 5 saniye sonra zorla kapat
    setTimeout(function() {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            console.log('Acil durum: Preloader zorla kapatılıyor');
            preloader.style.display = 'none';
            if (preloader.parentNode) {
                preloader.parentNode.removeChild(preloader);
            }
        }
    }, 5000);
    
    // Hata durumunda da preloader'ı kapat
    window.addEventListener('error', function() {
        setTimeout(closePreloader, 100);
    });
})();
</script>
