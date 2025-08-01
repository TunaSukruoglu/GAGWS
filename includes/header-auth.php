<?php
// header-auth.php - Güncellenmiş versiyon
?>
<div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
    <ul class="d-flex align-items-center style-none">
        <?php if ($isLoggedIn): ?>
            <li class="dropdown">
                <a href="#" class="btn-user dropdown-toggle" data-bs-toggle="dropdown" role="button" aria-expanded="false">
                    <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="dashboard.php"><i class="fas fa-tachometer-alt me-2"></i>Panel</a></li>
                    <li><a class="dropdown-item" href="profile.php"><i class="fas fa-user-circle me-2"></i>Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php"><i class="fas fa-sign-out-alt me-2"></i>Çıkış Yap</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one d-inline-flex align-items-center gap-2">
                    <span class="icon-square me-2">
                        <i class="fa-solid fa-lock"></i>
                    </span>
                    <span>Giriş</span>
                </a>
            </li>
        <?php endif; ?>
    </ul>
</div>

<!-- filepath: c:\xampp\htdocs\dashboard\GokhanAydinli\includes\login-modal.php -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="container">
            <div class="user-data-form modal-content">
                <!-- ...form içeriği... -->
            </div>
        </div>
    </div>
</div>