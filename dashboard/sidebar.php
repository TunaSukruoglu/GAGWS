<?php
// Sidebar navigation for dashboard
?>
<div class="sidebar bg-dark text-white p-3" style="min-height: 100vh;">
    <div class="d-flex align-items-center mb-4">
        <i class="fas fa-building text-primary me-2 fs-4"></i>
        <h5 class="mb-0">Gayrimenkul Panel</h5>
    </div>
    
    <hr class="text-secondary">
    
    <nav class="nav nav-pills flex-column">
        <a class="nav-link text-white mb-2" href="dashboard.php">
            <i class="fas fa-home me-2"></i>
            Dashboard
        </a>
        
        <a class="nav-link text-white mb-2" href="properties-list.php">
            <i class="fas fa-list me-2"></i>
            İlanlarım
        </a>
        
        <a class="nav-link text-white mb-2" href="add-property.php">
            <i class="fas fa-plus me-2"></i>
            İlan Ekle
        </a>
        
        <a class="nav-link text-white mb-2" href="profile.php">
            <i class="fas fa-user me-2"></i>
            Profil
        </a>
        
        <?php if (($_SESSION['role'] ?? '') === 'admin'): ?>
        <hr class="text-secondary">
        <h6 class="text-muted small">Yönetici</h6>
        
        <a class="nav-link text-white mb-2" href="admin-properties.php">
            <i class="fas fa-cogs me-2"></i>
            Tüm İlanlar
        </a>
        
        <a class="nav-link text-white mb-2" href="admin-users.php">
            <i class="fas fa-users me-2"></i>
            Kullanıcılar
        </a>
        <?php endif; ?>
        
        <hr class="text-secondary">
        
        <a class="nav-link text-white mb-2" href="../logout.php">
            <i class="fas fa-sign-out-alt me-2"></i>
            Çıkış Yap
        </a>
    </nav>
    
    <div class="mt-auto pt-3">
        <small class="text-muted">
            <i class="fas fa-user-circle me-1"></i>
            <?= htmlspecialchars($_SESSION['username'] ?? 'Kullanıcı') ?>
        </small>
    </div>
</div>

<style>
.sidebar {
    position: fixed;
    top: 0;
    left: 0;
    width: 250px;
    z-index: 1000;
}

.nav-link:hover {
    background-color: rgba(255,255,255,0.1) !important;
    border-radius: 8px;
}

.nav-link.active {
    background-color: #007bff !important;
    border-radius: 8px;
}

@media (max-width: 768px) {
    .sidebar {
        width: 100%;
        position: relative;
        min-height: auto !important;
    }
}
</style>
