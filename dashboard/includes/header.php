<?php
// Sadece gerekli değişkenler - header görsel element yok
$page_title = isset($page_title) ? $page_title : 'Dashboard';
?>

<!-- Görünmez Header - Sadece mobile nav için -->
<div class="mobile-nav-trigger d-block d-lg-none">
    <button class="btn btn-primary position-fixed" 
            style="top: 20px; left: 20px; z-index: 1050; border-radius: 50%; width: 50px; height: 50px;">
        <i class="fas fa-bars"></i>
    </button>
</div>

<style>
/* Mobile Nav Trigger Only */
.mobile-nav-trigger .btn {
    box-shadow: 0 4px 15px rgba(0,0,0,0.2);
    border: none;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
    transition: all 0.3s ease;
}

.mobile-nav-trigger .btn:hover {
    transform: scale(1.1);
    box-shadow: 0 6px 20px rgba(0,0,0,0.3);
}

/* Ana içerik alanını yukarı kaydır */
.dashboard-body {
    padding-top: 0 !important;
}

.dashboard-body .position-relative {
    padding-top: 20px;
}

/* Mobile'da main title'ı daha belirgin yap */
.main-title.d-block.d-lg-none {
    font-size: 28px !important;
    font-weight: 700 !important;
    color: #2c3e50 !important;
    margin-bottom: 25px !important;
    text-align: center !important;
    background: linear-gradient(135deg, #667eea 0%, #764ba2 100%) !important;
    -webkit-background-clip: text !important;
    -webkit-text-fill-color: transparent !important;
    background-clip: text !important;
}

/* Desktop'ta sayfa başlığını daha büyük yap */
@media (min-width: 992px) {
    .dashboard-body .position-relative::before {
        content: "<?= htmlspecialchars($page_title) ?>";
        display: block;
        font-size: 32px;
        font-weight: 700;
        color: #2c3e50;
        margin-bottom: 30px;
        background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
        -webkit-background-clip: text;
        -webkit-text-fill-color: transparent;
        background-clip: text;
    }
}

/* Stats cards'ı yukarı kaydır */
.stats-cards {
    margin-top: 0 !important;
}

/* Content wrapper için daha fazla alan */
.dashboard-body .position-relative {
    min-height: calc(100vh - 40px);
}
</style>

<!-- Service Worker Engelleyici - VS Code Webview Uyumluluğu -->
<script src="includes/service-worker-blocker.js"></script>

<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile nav toggle
    const mobileNavBtn = document.querySelector('.mobile-nav-trigger .btn');
    if (mobileNavBtn) {
        mobileNavBtn.addEventListener('click', function() {
            const sidebar = document.querySelector('.dash-aside-navbar');
            if (sidebar) {
                sidebar.classList.toggle('show');
            }
        });
    }
    
    // Ana içeriği yukarı kaydır
    const dashboardBody = document.querySelector('.dashboard-body');
    if (dashboardBody) {
        dashboardBody.style.paddingTop = '0';
    }
    
    // Sidebar'dan logout fonksiyonu ekle (header olmadığı için)
    const logoutBtns = document.querySelectorAll('a[href*="logout"]');
    logoutBtns.forEach(btn => {
        btn.addEventListener('click', function(e) {
            if (!confirm('Çıkış yapmak istediğinizden emin misiniz?')) {
                e.preventDefault();
            }
        });
    });
});
</script>