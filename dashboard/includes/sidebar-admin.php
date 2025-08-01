<?php
// Sidebar için gerekli değişkenler
$current_page = $current_page ?? 'dashboard-admin';
$user_name = $user_name ?? $_SESSION['user_name'] ?? 'Admin';
?>

<!-- Dashboard Header Navigation -->
<header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
    <div class="inner-content gap-one">
        <div class="top-header position-relative">
            <div class="d-flex align-items-center justify-content-between">
                <div class="logo order-lg-0">
                    <a href="../index.php" class="d-flex align-items-center">
                        <img src="../images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:50px; width:auto;">
                    </a>
                </div>
                
                <!-- Sidebar Toggle Button (Mobile) -->
                <button class="sidebar-toggle d-lg-none" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
            </div>
        </div>
    </div>
</header>

<div class="dash-aside-navbar">
    <div class="sidebar-content">
        <!-- Logo -->
        <div class="logo-section">
            <a href="../index.php" class="logo-container">
                <img src="../images/logo.png" alt="Logo" class="main-logo">
                <div class="logo-text">
                
                </div>
            </a>
        </div>
        
        <!-- User Info -->
        <div class="user-section">
            <div class="user-profile">
                <div class="user-avatar">
                    <?php 
                    // Profil resmi için hem $user_data hem $user değişkenlerini kontrol et
                    $profile_img = null;
                    if (isset($user_data['profile_image']) && !empty($user_data['profile_image'])) {
                        $profile_img = $user_data['profile_image'];
                    } elseif (isset($user['profile_image']) && !empty($user['profile_image'])) {
                        $profile_img = $user['profile_image'];
                    }
                    
                    if ($profile_img && file_exists('../' . $profile_img)): 
                        $image_filename = basename($profile_img);
                    ?>
                        <img src="/profile-image.php?image=<?= htmlspecialchars($image_filename) ?>" alt="<?= htmlspecialchars($user_name) ?>" class="profile-img">
                    <?php else: ?>
                        <div class="default-avatar">
                            <i class="fas fa-user"></i>
                        </div>
                    <?php endif; ?>
                </div>
                <div class="user-details">
                    <h4 class="user-name"><?= htmlspecialchars($user_name) ?></h4>
                    <span class="user-badge">Admin</span>
                </div>
            </div>
        </div>
        
        <!-- Navigation Menu -->
        <nav class="sidebar-nav">
            <ul class="nav-list">
                <!-- Dashboard -->
                <li class="nav-item">
                    <a href="dashboard-admin.php" class="nav-link <?= $current_page === 'dashboard-admin' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-chart-pie"></i>
                        </div>
                        <span class="nav-text">Admin Dashboard</span>
                    </a>
                </li>
                
                <!-- Profil Yönetimi -->
                <li class="nav-item">
                    <a href="profile.php" class="nav-link <?= $current_page === 'profile' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-user-circle"></i>
                        </div>
                        <span class="nav-text">Profil</span>
                    </a>
                </li>
                
                <!-- Kullanıcı Yönetimi -->
                <li class="nav-item">
                    <a href="admin-users.php" class="nav-link <?= $current_page === 'admin-users' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-users"></i>
                        </div>
                        <span class="nav-text">Kullanıcı Yönetimi</span>
                    </a>
                </li>
                
                <!-- Emlak Yönetimi -->
                <li class="nav-item">
                    <a href="admin-properties.php" class="nav-link <?= $current_page === 'admin-properties' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="nav-text">Emlak Yönetimi</span>
                    </a>
                </li>
                
                <!-- Blog Yönetimi -->
                <li class="nav-item">
                    <a href="admin-blog.php" class="nav-link <?= $current_page === 'admin-blog' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-blog"></i>
                        </div>
                        <span class="nav-text">Blog Yönetimi</span>
                    </a>
                </li>
                
                <!-- Yetki Yönetimi -->
                <li class="nav-item">
                    <a href="admin-permissions.php" class="nav-link <?= $current_page === 'admin-permissions' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-key"></i>
                        </div>
                        <span class="nav-text">Yetki Yönetimi</span>
                    </a>
                </li>
                
                <!-- Sistem Ayarları -->
                <li class="nav-item">
                    <a href="admin-settings.php" class="nav-link <?= $current_page === 'admin-settings' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-cog"></i>
                        </div>
                        <span class="nav-text">Sistem Ayarları</span>
                    </a>
                </li>
                
                <!-- Hızlı İşlemler -->
                <li class="nav-item">
                    <a href="add-property.php" class="nav-link <?= $current_page === 'add-property' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-plus"></i>
                        </div>
                        <span class="nav-text">Yeni İlan Ekle</span>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="admin-blog-add-new.php" class="nav-link <?= $current_page === 'admin-blog-add' ? 'active' : '' ?>">
                        <div class="nav-icon">
                            <i class="fas fa-edit"></i>
                        </div>
                        <span class="nav-text">Yeni Blog Yazısı</span>
                    </a>
                </li>
                
                <!-- Ana Site Linkleri -->
                <li class="nav-item">
                    <a href="../porfoy.html" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-building"></i>
                        </div>
                        <span class="nav-text">İlanları Görüntüle</span>
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../blog.php" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-blog"></i>
                        </div>
                        <span class="nav-text">Blog</span>
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </li>
                
                <li class="nav-item">
                    <a href="../index.php" class="nav-link external-link">
                        <div class="nav-icon">
                            <i class="fas fa-home"></i>
                        </div>
                        <span class="nav-text">Ana Sayfaya Dön</span>
                        <i class="fas fa-external-link-alt"></i>
                    </a>
                </li>
                
                <!-- Çıkış -->
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
    </div>
</div>

<!-- Mobile Overlay -->
<div class="mobile-overlay d-block d-md-none" onclick="toggleSidebar()"></div>

<style>
.dash-aside-navbar {
    width: 280px;
    background: linear-gradient(180deg, #4c9eff 0%, #0066ff 100%);
    color: white;
    position: fixed;
    height: 100vh;
    overflow-y: auto;
    z-index: 1000;
    top: 0;
    left: 0;
    transition: all 0.3s ease;
    box-shadow: 4px 0 20px rgba(0, 0, 0, 0.1);
}

.sidebar-content {
    padding: 0;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.logo-section {
    padding: 30px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.logo-container {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
    text-decoration: none;
    transition: all 0.3s ease;
}

.logo-container:hover {
    transform: scale(1.05);
    text-decoration: none;
}

.logo-container:hover .main-logo {
    filter: brightness(1.2) contrast(1.1);
}

.main-logo {
    max-width: 160px;
    height: auto;
    max-height: 80px;
    object-fit: contain;
    filter: brightness(1.1) contrast(1.05);
}

.logo-text {
    text-align: center;
}

.logo-title {
    font-size: 18px;
    font-weight: 700;
    color: white;
    font-style: italic;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
}

.user-section {
    padding: 30px 20px;
    text-align: center;
    border-bottom: 1px solid rgba(255,255,255,0.1);
}

.user-profile {
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 15px;
}

.user-avatar {
    width: 80px;
    height: 80px;
    border-radius: 50%;
    background: linear-gradient(135deg, #ffd700, #ffb347);
    display: flex;
    align-items: center;
    justify-content: center;
    border: 3px solid rgba(255,255,255,0.3);
    overflow: hidden;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
}

.profile-img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    border-radius: 50%;
}

.default-avatar {
    width: 100%;
    height: 100%;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #0d1a1c;
    font-size: 24px;
}

.user-details {
    text-align: center;
}

.user-name {
    font-size: 18px;
    font-weight: 600;
    color: white;
    margin: 0 0 8px 0;
    text-shadow: 1px 1px 3px rgba(0,0,0,0.2);
}

.user-badge {
    background: rgba(255,215,0,0.8);
    color: #0d1a1c;
    padding: 6px 15px;
    border-radius: 20px;
    font-size: 12px;
    font-weight: 700;
    letter-spacing: 0.5px;
    text-transform: uppercase;
    box-shadow: 0 3px 10px rgba(255,215,0,0.3);
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
    margin-bottom: 8px;
}

.nav-link {
    display: flex;
    align-items: center;
    padding: 15px 25px;
    color: rgba(255,255,255,0.85);
    text-decoration: none;
    transition: all 0.3s ease;
    position: relative;
    margin: 0 15px;
    border-radius: 12px;
    backdrop-filter: blur(5px);
}

.nav-link:hover {
    background: rgba(255,255,255,0.15);
    color: white;
    text-decoration: none;
    transform: translateX(8px);
    box-shadow: 0 5px 20px rgba(0,0,0,0.1);
}

.nav-link.active {
    background: rgba(255,255,255,0.2);
    color: white;
    box-shadow: 0 8px 25px rgba(0,0,0,0.15);
    border-left: 4px solid #ffd700;
}

.nav-icon {
    width: 24px;
    height: 24px;
    margin-right: 15px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 16px;
}

.nav-text {
    flex: 1;
    font-size: 15px;
    font-weight: 500;
    letter-spacing: 0.3px;
}

.external-link .fas.fa-external-link-alt {
    font-size: 11px;
    margin-left: auto;
    opacity: 0.7;
}

.logout-link:hover {
    background: rgba(220, 53, 69, 0.2) !important;
    color: #ff6b7a !important;
    border-left: 4px solid #dc3545 !important;
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
    width: 6px;
}

.dash-aside-navbar::-webkit-scrollbar-track {
    background: rgba(255,255,255,0.1);
    border-radius: 3px;
}

.dash-aside-navbar::-webkit-scrollbar-thumb {
    background: rgba(255,255,255,0.3);
    border-radius: 3px;
}

.dash-aside-navbar::-webkit-scrollbar-thumb:hover {
    background: rgba(255,255,255,0.5);
}

/* Hover Effects */
.nav-link::before {
    content: '';
    position: absolute;
    left: 0;
    top: 0;
    bottom: 0;
    width: 0;
    background: linear-gradient(135deg, #ffd700, #ffb347);
    border-radius: 12px 0 0 12px;
    transition: width 0.3s ease;
}

.nav-link.active::before {
    width: 4px;
}

.nav-link:hover::before {
    width: 4px;
}

/* Animation for active state */
.nav-link.active {
    animation: activeGlow 0.3s ease-in-out;
}

@keyframes activeGlow {
    0% { 
        box-shadow: 0 0 0 rgba(255,215,0,0);
        transform: translateX(0);
    }
    50% { 
        box-shadow: 0 0 20px rgba(255,215,0,0.3);
        transform: translateX(5px);
    }
    100% { 
        box-shadow: 0 8px 25px rgba(0,0,0,0.15);
        transform: translateX(8px);
    }
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

// Auto-close on mobile when clicking nav links
document.querySelectorAll('.nav-link').forEach(link => {
    link.addEventListener('click', function() {
        if (window.innerWidth <= 768) {
            toggleSidebar();
        }
    });
});

// Smooth active state transitions
document.addEventListener('DOMContentLoaded', function() {
    const activeLink = document.querySelector('.nav-link.active');
    if (activeLink) {
        activeLink.scrollIntoView({ 
            behavior: 'smooth', 
            block: 'nearest' 
        });
    }
});

// Mobile responsive handler
window.addEventListener('resize', function() {
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    if (window.innerWidth > 768) {
        sidebar.classList.remove('show');
        overlay.classList.remove('show');
    }
});
</script>
