<?php
// Sidebar için gerekli değişkenler
$current_page = $current_page ?? 'dashboard-user';
$user_name = $_SESSION['user_name'] ?? 'Kullanıcı';

// Sayfa sayıları (opsiyonel)
$fav_count = $fav_count ?? 0;
$saved_searches_count = $saved_searches_count ?? 0;
$reviews_count = $reviews_count ?? 0;
?>

<div class="dash-aside-navbar">
    <div class="sidebar-content">
        <!-- Logo -->
        <div class="logo-section">
            <a href="../index.php">
                <img src="../images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="max-height: 50px;">
            </a>
            <button class="mobile-toggle d-block d-md-none">
                <i class="fas fa-times"></i>
            </button>
        </div>
        
        <!-- User Info -->
        <div class="user-section">
            <div class="user-avatar">
                <div class="avatar-circle">
                    <?= strtoupper(substr($user_name, 0, 2)) ?>
                </div>
                <div class="online-dot"></div>
            </div>
            <div class="user-info">
                <div class="user-name"><?= htmlspecialchars($user_name) ?></div>
                <div class="user-role">Üye</div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard-user.php" class="nav-link <?= $current_page === 'dashboard-user' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="nav-text">Dashboard</span>
                    </a>
                </li>
                
                <!-- Profil Ayarları - ÖNE ÇIKARTILDI -->
                <li class="nav-item">
                    <a href="user-profile.php" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <span class="nav-text">Profil</span>
                    </a>
                </li>
                
                <!-- Favorilerim -->
                <li class="nav-item">
                    <a href="favorites.php" class="nav-link <?= $current_page === 'favorites' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-heart"></i>
                        </div>
                        <span class="nav-text">Favorilerim</span>
                        <?php if ($fav_count > 0): ?>
                            <span class="nav-badge"><?= $fav_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- Kayıtlı Aramalar -->
                <li class="nav-item">
                    <a href="saved-searches.php" class="nav-link <?= $current_page === 'saved-searches' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-search"></i>
                        </div>
                        <span class="nav-text">Kayıtlı Aramalar</span>
                        <?php if ($saved_searches_count > 0): ?>
                            <span class="nav-badge"><?= $saved_searches_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- Değerlendirmelerim -->
                <li class="nav-item">
                    <a href="reviews.php" class="nav-link <?= $current_page === 'reviews' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-star"></i>
                        </div>
                        <span class="nav-text">Değerlendirmelerim</span>
                        <?php if ($reviews_count > 0): ?>
                            <span class="nav-badge"><?= $reviews_count ?></span>
                        <?php endif; ?>
                    </a>
                </li>
                
                <!-- Divider -->
                <li class="nav-divider">
                    <hr>
                </li>
                
                <!-- Ana Site Linkleri -->
                <li class="nav-item">
                    <a href="../porfoy.html" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="nav-text">İlanları Keşfet</span>
                        <i class="fas fa-external-link-alt external-icon"></i>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../blog.php" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-blog"></i>
                        </div>
                        <span class="nav-text">Blog</span>
                        <i class="fas fa-external-link-alt external-icon"></i>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../contact.php" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-envelope"></i>
                        </div>
                        <span class="nav-text">İletişim</span>
                        <i class="fas fa-external-link-alt external-icon"></i>
                    </a>
                </li>
                
                <!-- Divider -->
                <li class="nav-divider">
                    <hr>
                </li>
                
                <!-- Alt Menü -->
                <li class="nav-item">
                    <a href="../index.php" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="nav-text">Ana Sayfaya Dön</span>
                        <i class="fas fa-external-link-alt external-icon"></i>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../logout.php" class="nav-link logout-link" onclick="return confirm('Çıkış yapmak istediğinizden emin misiniz?')">
                        <div class="nav-icon">
                            <i class="fas fa-sign-out-alt"></i>
                        </div>
                        <span class="nav-text">Çıkış Yap</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Alt Bilgi -->
        <div class="sidebar-footer">
            <div class="footer-info">
                <small class="text-muted">
                    <i class="fas fa-shield-alt me-1"></i>
                    Güvenli Oturum
                </small>
            </div>
        </div>
    </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay d-block d-md-none" onclick="toggleSidebar()"></div>

<style>
.dash-aside-navbar {
    width: 280px;
    background: linear-gradient(135deg, #0d6efd, #1e3a8a);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    top: 0;
    left: 0;
    transition: all 0.3s ease;
}

.sidebar-content {
    padding: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.logo-section {
    padding: 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
    position: relative;
}

.mobile-toggle {
    position: absolute;
    top: 20px;
    right: 20px;
    background: none;
    border: none;
    color: white;
    font-size: 18px;
    cursor: pointer;
}

.user-section {
    padding: 25px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.user-avatar {
    position: relative;
    margin-bottom: 15px;
    display: inline-block;
}

.avatar-circle {
    width: 60px;
    height: 60px;
    border-radius: 50%;
    background: linear-gradient(135deg, rgba(255,255,255,0.2), rgba(255,255,255,0.1));
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 20px;
    color: white;
    border: 2px solid rgba(255,255,255,0.3);
}

.online-dot {
    position: absolute;
    bottom: 5px;
    right: 5px;
    width: 12px;
    height: 12px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.user-info .user-name {
    font-size: 16px;
    font-weight: 600;
    color: white;
    margin-bottom: 5px;
}

.user-info .user-role {
    font-size: 12px;
    color: rgba(255,255,255,0.7);
    background: rgba(255,255,255,0.1);
    padding: 4px 12px;
    border-radius: 15px;
    display: inline-block;
}

.sidebar-nav {
    flex: 1;
    padding: 20px 0;
    overflow-y: auto;
}

.nav-list {
    list-style: none;
    padding: 0;
    margin: 0;
}

.nav-item {
    margin-bottom: 5px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 12px 20px;
    color: rgba(255,255,255,0.8);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    margin: 0 10px;
    border-radius: 10px;
}

.nav-link:hover {
    background: rgba(255,255,255,0.1);
    color: white;
    text-decoration: none;
    transform: translateX(5px);
}

.nav-link.active {
    background: rgba(255,255,255,0.15);
    color: white;
    box-shadow: 0 5px 15px rgba(0,0,0,0.2);
}

.nav-link.active::before {
    content: '';
    position: absolute;
    left: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 4px;
    height: 30px;
    background: white;
    border-radius: 2px;
}

.nav-icon {
    width: 20px;
    height: 20px;
    margin-right: 12px;
    display: flex;
    align-items: center;
    justify-content: center;
}

.nav-text {
    flex: 1;
    font-size: 14px;
    font-weight: 500;
}

.nav-badge {
    background: #ff4757;
    color: white;
    border-radius: 50%;
    font-size: 10px;
    font-weight: 600;
    padding: 2px 6px;
    min-width: 18px;
    height: 18px;
    display: flex;
    align-items: center;
    justify-content: center;
    margin-left: 8px;
}

.external-icon {
    font-size: 10px;
    margin-left: 8px;
    opacity: 0.6;
}

.nav-divider {
    margin: 15px 0;
    padding: 0 20px;
}

.nav-divider hr {
    border: none;
    height: 1px;
    background: rgba(255,255,255,0.1);
    margin: 0;
}

.logout-link:hover {
    background: rgba(220, 53, 69, 0.2) !important;
    color: #ff6b7a !important;
}

.sidebar-footer {
    padding: 20px;
    border-top: 1px solid rgba(255,255,255,0.1);
    text-align: center;
}

.mobile-overlay {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: rgba(0,0,0,0.5);
    z-index: 999;
    display: none;
}

/* Mobile */
@media (max-width: 768px) {
    .dash-aside-navbar {
        transform: translateX(-100%);
    }
    
    .dash-aside-navbar.show {
        transform: translateX(0);
    }
    
    .mobile-overlay.show {
        display: block;
    }
}

/* Scrollbar */
.dash-aside-navbar::-webkit-scrollbar {
    width: 4px;
}

.dash-aside-navbar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
}

.dash-aside-navbar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 2px;
}

.dash-aside-navbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}
</style>

<script>
// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    sidebar.classList.toggle('show');
    overlay.classList.toggle('show');
}

// Mobile toggle button
document.querySelector('.mobile-toggle')?.addEventListener('click', toggleSidebar);

// Auto-close on mobile when clicking nav links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            toggleSidebar();
        }
    });
});
</script>