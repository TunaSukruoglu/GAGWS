<div class="dashboard-header">
    <div class="d-flex align-items-center justify-content-between">
        <!-- Mobile Menu Toggle -->
        <button class="dash-mobile-nav-toggler d-block d-md-none">
            <span></span>
        </button>
        
        <!-- Page Title -->
        <div class="main-title d-none d-lg-block">
            <h2><?= htmlspecialchars($user_data['name']) ?> - Kullanıcı Paneli</h2>
        </div>
        
        <!-- Header Actions -->
        <div class="profile-notification ms-2 ms-md-5 me-4">
            <button class="noti-btn dropdown-toggle" type="button" id="notification-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                <img src="../images/icon/icon_13.svg" alt="" class="lazy-img">
                <div class="badge-pill"></div>
            </button>
            <ul class="dropdown-menu" aria-labelledby="notification-dropdown">
                <li>
                    <h6 class="dropdown-header">Bildirimler</h6>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item" href="#">
                        <div class="media">
                            <div class="icon">
                                <i class="fas fa-heart text-danger"></i>
                            </div>
                            <div class="media-body">
                                <h6>Yeni favori</h6>
                                <span>Bir ilan favorilerinize eklendi</span>
                                <div class="time">2 saat önce</div>
                            </div>
                        </div>
                    </a>
                </li>
                <li>
                    <a class="dropdown-item" href="#">
                        <div class="media">
                            <div class="icon">
                                <i class="fas fa-search text-primary"></i>
                            </div>
                            <div class="media-body">
                                <h6>Arama sonucu</h6>
                                <span>Kritelerinize uygun yeni ilan</span>
                                <div class="time">1 gün önce</div>
                            </div>
                        </div>
                    </a>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li>
                    <a class="dropdown-item text-center" href="#">
                        Tüm bildirimleri gör
                    </a>
                </li>
            </ul>
        </div>
        
        <!-- User Profile Dropdown -->
        <div class="d-flex align-items-center">
            <div class="user-dropdown dropdown">
                <button class="dropdown-toggle user-dropdown-btn" type="button" id="profile-dropdown" data-bs-toggle="dropdown" aria-expanded="false">
                    <div class="user-img-wrap">
                        <div class="user-img">
                            <?= strtoupper(substr($_SESSION['user_name'] ?? 'U', 0, 2)) ?>
                        </div>
                        <div class="online-indicator"></div>
                    </div>
                    <div class="user-name-data">
                        <div class="user-name"><?= htmlspecialchars($user_data['name']) ?></div>
                        <div class="user-role">Üye</div>
                    </div>
                </button>
                <ul class="dropdown-menu user-dropdown-menu" aria-labelledby="profile-dropdown">
                    <li>
                        <a class="dropdown-item" href="profile.php">
                            <img src="../images/icon/icon_user.svg" alt="" class="lazy-img">
                            Profil Ayarları
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="favorites.php">
                            <img src="../images/icon/icon_heart.svg" alt="" class="lazy-img">
                            Favorilerim
                        </a>
                    </li>
                    <li>
                        <a class="dropdown-item" href="saved-searches.php">
                            <img src="../images/icon/icon_search.svg" alt="" class="lazy-img">
                            Kayıtlı Aramalar
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="../index.php">
                            <img src="../images/icon/icon_home.svg" alt="" class="lazy-img">
                            Ana Sayfa
                        </a>
                    </li>
                    <li><hr class="dropdown-divider"></li>
                    <li>
                        <a class="dropdown-item" href="../logout.php" onclick="return confirm('Çıkış yapmak istediğinizden emin misiniz?')">
                            <img src="../images/icon/icon_logout.svg" alt="" class="lazy-img">
                            Çıkış Yap
                        </a>
                    </li>
                </ul>
            </div>
        </div>
    </div>
    
    <!-- Real-time Clock -->
    <div class="header-bottom d-none d-lg-block">
        <div class="current-time">
            <i class="fas fa-clock"></i>
            <span id="realtime-clock"></span>
        </div>
    </div>
</div>

<style>
.dashboard-header {
    background: white;
    padding: 20px 30px;
    border-bottom: 1px solid #f0f0f0;
    position: sticky;
    top: 0;
    z-index: 100;
}

.main-title h2 {
    font-size: 24px;
    font-weight: 600;
    color: #2c3e50;
    margin: 0;
}

.user-dropdown-btn {
    background: none;
    border: none;
    display: flex;
    align-items: center;
    gap: 12px;
    padding: 8px 12px;
    border-radius: 10px;
    transition: all 0.3s ease;
}

.user-dropdown-btn:hover {
    background: #f8f9fa;
}

.user-img-wrap {
    position: relative;
}

.user-img {
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: #15B97C;
    color: white;
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.online-indicator {
    position: absolute;
    bottom: 2px;
    right: 2px;
    width: 10px;
    height: 10px;
    background: #28a745;
    border: 2px solid white;
    border-radius: 50%;
}

.user-name {
    font-size: 14px;
    font-weight: 600;
    color: #2c3e50;
    text-align: left;
}

.user-role {
    font-size: 12px;
    color: #6c757d;
    text-align: left;
}

.noti-btn {
    background: none;
    border: none;
    position: relative;
    padding: 8px;
    border-radius: 8px;
    transition: all 0.3s ease;
}

.noti-btn:hover {
    background: #f8f9fa;
}

.badge-pill {
    position: absolute;
    top: 5px;
    right: 5px;
    width: 8px;
    height: 8px;
    background: #dc3545;
    border-radius: 50%;
}

.dropdown-menu {
    border: none;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    border-radius: 10px;
    padding: 10px 0;
}

.dropdown-item {
    padding: 10px 20px;
    display: flex;
    align-items: center;
    gap: 10px;
}

.dropdown-item:hover {
    background: #f8f9fa;
}

.header-bottom {
    margin-top: 15px;
    padding-top: 15px;
    border-top: 1px solid #f0f0f0;
}

.current-time {
    color: #6c757d;
    font-size: 14px;
    display: flex;
    align-items: center;
    gap: 8px;
}

@media (max-width: 768px) {
    .dashboard-header {
        padding: 15px 20px;
    }
    
    .user-name-data {
        display: none;
    }
}
</style>