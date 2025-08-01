<?php
// Session kontrolü
if (!isset($_SESSION['user_id'])) {
    return; // Sidebar'ı yükleme
}

// User bilgilerini al
if (!isset($user)) {
    $user_query = $conn->prepare("SELECT * FROM users WHERE id = ?");
    $user_query->bind_param("i", $_SESSION['user_id']);
    $user_query->execute();
    $user = $user_query->get_result()->fetch_assoc();
}

if (!$user) {
    return; // Sidebar'ı yükleme
}

// Mevcut sayfa adını al
$current_page = basename($_SERVER['PHP_SELF'], '.php');

// Admin kontrolü
$is_admin = ($user['role'] === 'admin');

// İlan ekleme yetkisi kontrolü - DÜZELTİLDİ
$can_add_property = false;
if ($is_admin) {
    $can_add_property = true; // Admin her zaman ekleyebilir
} else {
    // Normal kullanıcı için can_add_property kolonunu kontrol et
    $can_add_property = (isset($user['can_add_property']) && $user['can_add_property'] == 1);
}

// Profil tamamlanma hesapla
if (!$is_admin) {
    $completion_fields = ['name', 'email', 'phone', 'address', 'about', 'website', 'position'];
    $completed_fields = 0;
    foreach ($completion_fields as $field) {
        if (!empty($user[$field] ?? '')) {
            $completed_fields++;
        }
    }
    $completion_percentage = round(($completed_fields / count($completion_fields)) * 100);
} else {
    $completion_percentage = 100;
}
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
                
                <!-- Right side - User dropdown -->
                <div class="right-widget ms-auto order-lg-3">
                    <ul class="d-flex align-items-center style-none">
                        <li class="dropdown">
                            <a href="#" class="dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                <i class="fas fa-user-circle"></i>
                                <span class="ms-2"><?= htmlspecialchars($user['name'] ?? 'Kullanıcı') ?></span>
                            </a>
                            <ul class="dropdown-menu">
                                <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user me-2"></i> Profil</a></li>
                                <li><a class="dropdown-item" href="account-settings.php"><i class="fas fa-cog me-2"></i> Ayarlar</a></li>
                                <li><hr class="dropdown-divider"></li>
                                <li><a class="dropdown-item" href="../logout.php"><i class="fas fa-sign-out-alt me-2"></i> Çıkış</a></li>
                            </ul>
                        </li>
                    </ul>
                </div>
            </div>
        </div>
    </div>
</header>

<!-- Dashboard Aside Menu -->
<aside class="dash-aside-navbar">
    <div class="position-relative">
        <div class="logo d-md-block d-flex align-items-center justify-content-between plr bottom-line pb-30">
            <a href="../index.php">
                <img src="../images/logoSiyah.png" alt="Gökhan Aydınlı Real Estate" style="max-height: 100px; width: auto;">
            </a>
            <button class="close-btn d-block d-md-none"><i class="fa-light fa-circle-xmark"></i></button>
        </div>
        
        <!-- User Profile Section -->
        <div class="user-section plr pt-20 pb-20 bottom-line">
            <div class="user-profile d-flex align-items-center">
                <div class="user-avatar me-3" style="width: 50px; height: 50px; border-radius: 50%; overflow: hidden; background: #f8f9fa; display: flex; align-items: center; justify-content: center;">
                    <?php 
                    if (!empty($user['profile_image']) && file_exists('../' . $user['profile_image'])): 
                        $image_filename = basename($user['profile_image']);
                    ?>
                        <img src="/profile-image.php?image=<?= htmlspecialchars($image_filename) ?>" alt="<?= htmlspecialchars($user['name']) ?>" style="width: 100%; height: 100%; object-fit: cover;">
                    <?php else: ?>
                        <i class="fas fa-user" style="color: #6c757d; font-size: 20px;"></i>
                    <?php endif; ?>
                </div>
                <div class="user-details flex-grow-1">
                    <h6 class="user-name mb-1" style="font-size: 14px; font-weight: 600; color: #2c3e50; margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;"><?= htmlspecialchars($user['name']) ?></h6>
                    <span class="user-role" style="font-size: 12px; color: #6c757d; text-transform: capitalize;"><?= $is_admin ? 'Admin' : 'Kullanıcı' ?></span>
                </div>
            </div>
        </div>
        
        <nav class="dasboard-main-nav pt-30 pb-30 bottom-line">
            <ul class="style-none">
                <!-- Dashboard Link -->
                <li class="plr">
                    <a href="<?= $is_admin ? 'dashboard-admin.php' : 'dashboard.php' ?>" 
                       class="d-flex w-100 align-items-center <?= in_array($current_page, ['dashboard', 'dashboard-admin']) ? 'active' : '' ?>">
                        <i class="fas fa-tachometer-alt" style="margin-right: 12px; width: 20px; <?= in_array($current_page, ['dashboard', 'dashboard-admin']) ? 'color: #0d6efd;' : '' ?>"></i>
                        <span><?= $is_admin ? 'Admin Dashboard' : 'Dashboard' ?></span>
                    </a>
                </li>
                
                <!-- Mesajlar -->
                <li class="plr">
                    <a href="message.php" class="d-flex w-100 align-items-center <?= $current_page == 'message' ? 'active' : '' ?>">
                        <i class="fas fa-envelope" style="margin-right: 12px; width: 20px; <?= $current_page == 'message' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Mesajlar</span>
                    </a>
                </li>
                
                <li class="bottom-line pt-30 lg-pt-20 mb-40 lg-mb-30"></li>
                
                <!-- Profil Bölümü -->
                <li><div class="nav-title">Profil</div></li>
                <li class="plr">
                    <a href="profile.php" class="d-flex w-100 align-items-center <?= $current_page == 'profile' ? 'active' : '' ?>">
                        <i class="fas fa-user" style="margin-right: 12px; width: 20px; <?= $current_page == 'profile' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Profil</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="account-settings.php" class="d-flex w-100 align-items-center <?= $current_page == 'account-settings' ? 'active' : '' ?>">
                        <i class="fas fa-cog" style="margin-right: 12px; width: 20px; <?= $current_page == 'account-settings' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Hesap Ayarları</span>
                    </a>
                </li>
                
                <!-- Admin Yönetim Bölümü - Sadece Admin için -->
                <?php if($is_admin): ?>
                <li class="bottom-line pt-30 lg-pt-20 mb-40 lg-mb-30"></li>
                <li><div class="nav-title">Yönetim</div></li>
                <li class="plr">
                    <a href="admin-users.php" class="d-flex w-100 align-items-center <?= $current_page == 'admin-users' ? 'active' : '' ?>">
                        <i class="fas fa-users" style="margin-right: 12px; width: 20px; <?= $current_page == 'admin-users' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Kullanıcı Yönetimi</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="admin-properties.php" class="d-flex w-100 align-items-center <?= $current_page == 'admin-properties' ? 'active' : '' ?>">
                        <i class="fas fa-home" style="margin-right: 12px; width: 20px; <?= $current_page == 'admin-properties' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Emlak Yönetimi</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="admin-blog.php" class="d-flex w-100 align-items-center <?= in_array($current_page, ['admin-blog', 'admin-blog-add']) ? 'active' : '' ?>">
                        <i class="fas fa-edit" style="margin-right: 12px; width: 20px; <?= in_array($current_page, ['admin-blog', 'admin-blog-add']) ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Blog Yazıları</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="auto-blog-generator.php" class="d-flex w-100 align-items-center <?= $current_page == 'auto-blog-generator' ? 'active' : '' ?>">
                        <i class="fas fa-robot" style="margin-right: 12px; width: 20px; <?= $current_page == 'auto-blog-generator' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>🤖 Otomatik Blog</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="admin-permissions.php" class="d-flex w-100 align-items-center <?= $current_page == 'admin-permissions' ? 'active' : '' ?>">
                        <i class="fas fa-key" style="margin-right: 12px; width: 20px; <?= $current_page == 'admin-permissions' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Yetki Yönetimi</span>
                    </a>
                </li>
                <?php endif; ?>
                
                <li class="bottom-line pt-30 lg-pt-20 mb-40 lg-mb-30"></li>
                
                <!-- İlanlar Bölümü -->
                <li><div class="nav-title">İlanlar</div></li>
                <li class="plr">
                    <a href="properties-list.php" class="d-flex w-100 align-items-center <?= $current_page == 'properties-list' ? 'active' : '' ?>">
                        <i class="fas fa-list" style="margin-right: 12px; width: 20px; <?= $current_page == 'properties-list' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>İlanlarım</span>
                    </a>
                </li>
                
                <!-- Yeni İlan Ekle - HERKESE GÖSTERİLEN VERSİYON -->
                <?php if($can_add_property): ?>
                <li class="plr">
                    <a href="add-property.php" class="d-flex w-100 align-items-center <?= $current_page == 'add-property' ? 'active' : '' ?>">
                        <i class="fas fa-plus" style="margin-right: 12px; width: 20px; <?= $current_page == 'add-property' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Yeni İlan Ekle</span>
                        <?php if($is_admin): ?>
                            <small class="ms-auto text-success">Admin</small>
                        <?php endif; ?>
                    </a>
                </li>
                <?php else: ?>
                <!-- Yetkisi olmayan kullanıcılar için bilgi -->
                <li class="plr">
                    <div class="d-flex w-100 align-items-center text-muted" style="padding: 12px 0; font-size: 14px;">
                        <i class="fas fa-lock" style="margin-right: 12px; width: 20px; opacity: 0.5;"></i>
                        <span>Yeni İlan Ekle</span>
                        <small class="ms-auto">Yetki Gerekli</small>
                    </div>
                </li>
                <?php endif; ?>
                
                <li class="plr">
                    <a href="favourites.php" class="d-flex w-100 align-items-center <?= $current_page == 'favourites' ? 'active' : '' ?>">
                        <i class="fas fa-heart" style="margin-right: 12px; width: 20px; <?= $current_page == 'favourites' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Favoriler</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="saved-search.php" class="d-flex w-100 align-items-center <?= $current_page == 'saved-search' ? 'active' : '' ?>">
                        <i class="fas fa-bookmark" style="margin-right: 12px; width: 20px; <?= $current_page == 'saved-search' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Kayıtlı Aramalar</span>
                    </a>
                </li>
                <li class="plr">
                    <a href="review.php" class="d-flex w-100 align-items-center <?= $current_page == 'review' ? 'active' : '' ?>">
                        <i class="fas fa-star" style="margin-right: 12px; width: 20px; <?= $current_page == 'review' ? 'color: #0d6efd;' : '' ?>"></i>
                        <span>Değerlendirmeler</span>
                    </a>
                </li>
            </ul>
        </nav>
        
        <!-- Profil Tamamlanma / Admin Durumu -->
        <div class="profile-complete-status bottom-line pb-35 plr">
            <div class="progress-value fw-500"><?= $completion_percentage ?>%</div>
            <div class="progress-line position-relative">
                <div class="inner-line" style="width:<?= $completion_percentage ?>%;"></div>
            </div>
            <p><?= $is_admin ? 'Admin Yetkili' : 'Profil Tamamlanma' ?></p>
        </div>

        <!-- Debug Bilgisi - Geliştirme aşamasında -->
        <?php if(isset($_GET['debug'])): ?>
        <div class="debug-info plr" style="background: #f8f9fa; padding: 10px; font-size: 12px; border-radius: 5px; margin: 10px;">
            <strong>Debug:</strong><br>
            Role: <?= $user['role'] ?><br>
            Can Add: <?= $user['can_add_property'] ?? 'null' ?><br>
            Is Admin: <?= $is_admin ? 'true' : 'false' ?><br>
            Can Add Property: <?= $can_add_property ? 'true' : 'false' ?>
        </div>
        <?php endif; ?>

        <!-- Çıkış Yap -->
        <div class="plr">
            <a href="../logout.php" class="d-flex w-100 align-items-center logout-btn" 
               onclick="return confirm('Çıkış yapmak istediğinizden emin misiniz?')">
                <div class="icon tran3s d-flex align-items-center justify-content-center rounded-circle">
                    <i class="fas fa-sign-out-alt"></i>
                </div>
                <span>Çıkış Yap</span>
            </a>
        </div>
    </div>
</aside>

<!-- Mobile Menu Toggle Script -->
<script>
document.addEventListener('DOMContentLoaded', function() {
    // Mobile menu toggle
    const mobileToggle = document.querySelector('.dash-mobile-nav-toggler');
    const sidebar = document.querySelector('.dash-aside-navbar');
    const closeBtn = document.querySelector('.close-btn');
    
    if (mobileToggle && sidebar) {
        mobileToggle.addEventListener('click', function() {
            sidebar.classList.add('show');
        });
    }
    
    if (closeBtn && sidebar) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('show');
        });
    }
    
    // Sidebar dışına tıklandığında kapat
    document.addEventListener('click', function(e) {
        if (sidebar && !sidebar.contains(e.target) && !mobileToggle?.contains(e.target)) {
            sidebar.classList.remove('show');
        }
    });
});
</script>