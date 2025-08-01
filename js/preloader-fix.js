// Global Preloader Fix - Tüm sayfalarda çalışır
document.addEventListener('DOMContentLoaded', function() {
    console.log('Preloader fix başlatıldı');
    
    // Preloader'ı güvenli şekilde kapat
    function closePreloader() {
        const preloader = document.getElementById('preloader');
        if (preloader) {
            console.log('Preloader bulundu, kapatılıyor...');
            preloader.style.opacity = '0';
            preloader.style.transition = 'opacity 0.5s ease';
            
            setTimeout(function() {
                preloader.style.display = 'none';
                preloader.remove();
                console.log('Preloader kaldırıldı');
            }, 500);
        } else {
            console.log('Preloader bulunamadı');
        }
    }
    
    // Preloader'ı 1 saniye sonra kapat
    setTimeout(closePreloader, 1000);
    
    // Eğer sayfa tamamen yüklendiyse hemen kapat
    if (document.readyState === 'complete') {
        closePreloader();
    } else {
        window.addEventListener('load', closePreloader);
    }
});

// Acil durum için - Eğer preloader 5 saniye sonra hala varsa zorla kapat
setTimeout(function() {
    const preloader = document.getElementById('preloader');
    if (preloader) {
        console.log('Acil durum: Preloader zorla kapatılıyor');
        preloader.style.display = 'none';
        preloader.remove();
    }
}, 5000);
