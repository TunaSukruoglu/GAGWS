<?php
// modal-css.php - Güncellenmiş CSS stilleri
?>
<style>
/* ==================== MODERN GİRİŞ BUTONU STİLLERİ ==================== */

/* Ana giriş butonu - İlk resimdeki gibi büyük ve belirgin */
.btn-login {
    background: transparent !important;
    color: #ffffff !important;
    border: 2px solid rgba(255, 255, 255, 0.6) !important;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    min-width: 120px;
    justify-content: center;
    position: relative;
    overflow: hidden;
}

/* Hover efekti - İlk resimdeki gibi */
.btn-login:hover {
    background: rgba(255, 255, 255, 0.1) !important;
    border-color: rgba(255, 255, 255, 0.8) !important;
    color: #ffffff !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(255, 255, 255, 0.1);
}

/* Active state */
.btn-login:active {
    transform: translateY(0);
}

/* User dropdown butonu - Giriş yapan kullanıcılar için (büyük versiyon) */
.btn-user {
    background: transparent !important;
    color: #15B97C !important;
    border: 2px solid rgba(21, 185, 124, 0.6) !important;
    padding: 12px 30px;
    border-radius: 8px;
    font-weight: 600;
    font-size: 16px;
    text-decoration: none;
    display: inline-flex;
    align-items: center;
    gap: 10px;
    transition: all 0.3s ease;
    min-width: 150px;
    justify-content: center;
}

.btn-user:hover {
    background: rgba(21, 185, 124, 0.1) !important;
    border-color: rgba(21, 185, 124, 0.8) !important;
    color: #15B97C !important;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(21, 185, 124, 0.15);
}

/* ==================== DROPDOWN MENÜ STİLLERİ ==================== */
.dropdown-menu {
    border: none;
    box-shadow: 0 10px 30px rgba(0, 0, 0, 0.15);
    border-radius: 12px;
    padding: 8px 0;
    margin-top: 8px;
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    min-width: 200px;
}

.dropdown-item {
    padding: 10px 20px;
    font-size: 14px;
    transition: all 0.3s ease;
    border-radius: 0;
    color: #333;
}

.dropdown-item:hover {
    background: #15B97C;
    color: white;
}

.dropdown-item i {
    width: 16px;
    text-align: center;
}

.dropdown-divider {
    margin: 8px 12px;
    border-color: rgba(0, 0, 0, 0.1);
}

/* ==================== MODAL STİLLERİ ==================== */
.user-data-form {
    background: rgba(255,255,255,0.85);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid #e0e0e0;
    padding: 40px;
    box-shadow: 0 4px 30px rgba(0,0,0,0.1);
    max-width: 450px;
    margin: 0 auto;
    animation: fadeInUp 0.4s ease-out;
}

.user-data-form .modal-content {
    border: none;
    border-radius: 20px;
    background: transparent;
}

/* Tab navigation */
.user-data-form .nav-tabs {
    border: none;
    margin-bottom: 30px;
    background: #f8f9fa;
    border-radius: 12px;
    padding: 4px;
}

.user-data-form .nav-link {
    border: none;
    border-radius: 8px;
    padding: 12px 24px;
    font-size: 14px;
    font-weight: 500;
    color: #666;
    background: transparent;
    transition: all 0.3s ease;
    margin: 0 2px;
}

.user-data-form .nav-link.active {
    color: #fff;
    background: #15B97C;
    box-shadow: 0 2px 8px rgba(21, 185, 124, 0.3);
}

/* Form inputs */
.user-data-form .input-group-meta {
    position: relative;
    margin-bottom: 20px;
}

.user-data-form .input-group-meta label {
    font-size: 13px;
    font-weight: 500;
    color: #333;
    margin-bottom: 6px;
    display: block;
}

.user-data-form .input-group-meta input {
    height: 48px;
    padding: 12px 16px;
    font-size: 14px;
    border: 1px solid #e1e5e9;
    border-radius: 8px;
    transition: all 0.3s ease;
    width: 100%;
    background: rgba(255, 255, 255, 0.8);
}

.user-data-form .input-group-meta input:focus {
    border-color: #15B97C;
    box-shadow: 0 0 0 3px rgba(21, 185, 124, 0.1);
    outline: none;
    background: #fff;
}

/* Submit button */
.user-data-form .btn-two {
    background: linear-gradient(135deg, #15B97C, #12a06b);
    color: #fff;
    padding: 14px 20px;
    border-radius: 8px;
    font-size: 14px;
    font-weight: 600;
    text-transform: uppercase;
    border: none;
    transition: all 0.3s ease;
    width: 100%;
    letter-spacing: 0.5px;
}

.user-data-form .btn-two:hover {
    background: linear-gradient(135deg, #12a06b, #0f8c5a);
    transform: translateY(-1px);
    box-shadow: 0 6px 20px rgba(21, 185, 124, 0.3);
}

/* Checkbox and links */
.user-data-form .agreement-checkbox {
    font-size: 13px;
    color: #666;
}

.user-data-form .agreement-checkbox input[type="checkbox"] {
    width: auto;
    height: auto;
    margin-right: 8px;
    transform: scale(1.1);
}

.user-data-form .agreement-checkbox a {
    color: #15B97C;
    text-decoration: none;
    font-weight: 500;
}

.user-data-form .agreement-checkbox a:hover {
    text-decoration: underline;
}

/* Social login buttons */
.user-data-form .social-use-btn {
    background: #f8f9fa;
    color: #333;
    padding: 12px 16px;
    border-radius: 8px;
    font-size: 13px;
    font-weight: 500;
    text-align: center;
    display: flex;
    align-items: center;
    justify-content: center;
    width: 100%;
    transition: all 0.3s ease;
    text-decoration: none;
    border: 1px solid #e1e5e9;
}

.user-data-form .social-use-btn:hover {
    background: #e9ecef;
    transform: translateY(-1px);
    box-shadow: 0 4px 12px rgba(0, 0, 0, 0.1);
    color: #333;
    border-color: #d6d9dc;
}

.user-data-form .social-use-btn img {
    width: 18px;
    height: 18px;
    margin-right: 8px;
}

/* Separator line */
.user-data-form .line {
    flex: 1;
    height: 1px;
    background: linear-gradient(90deg, transparent, #e1e5e9, transparent);
}

/* Password toggle */
.user-data-form .placeholder_icon {
    position: absolute;
    top: 32px;
    right: 16px;
    cursor: pointer;
    z-index: 10;
}

.user-data-form .passVicon {
    width: 20px;
    height: 20px;
    display: flex;
    align-items: center;
    justify-content: center;
    color: #666;
    transition: color 0.3s ease;
}

.user-data-form .passVicon:hover {
    color: #15B97C;
}

/* ==================== RESPONSİVE TASARIM ==================== */
@media (max-width: 768px) {
    .user-data-form {
        padding: 30px 20px;
        margin: 20px;
    }
    
    .btn-login,
    .btn-user {
        padding: 10px 24px;
        font-size: 14px;
        min-width: 100px;
    }
    
    .dropdown-menu {
        min-width: 180px;
    }
}

@media (max-width: 991px) {
    .right-widget {
        order: 1 !important;
        margin: 10px 0;
    }
}

/* ==================== DARK MODE DESTEĞI ==================== */
@media (prefers-color-scheme: dark) {
    .btn-login {
        background: rgba(0, 0, 0, 0.2) !important;
        border-color: rgba(255, 255, 255, 0.1) !important;
    }
    
    .btn-login:hover {
        background: rgba(0, 0, 0, 0.3) !important;
        border-color: rgba(255, 255, 255, 0.2) !important;
    }
}

/* ==================== ANİMASYONLAR ==================== */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translateY(20px);
    }
    to {
        opacity: 1;
        transform: translateY(0);
    }
}

.user-data-form {
    animation: fadeInUp 0.4s ease-out;
}

.dropdown-menu {
    animation: fadeInUp 0.2s ease-out;
}

/* ==================== AKSESİBİLİTE ==================== */
.btn-login:focus,
.btn-user:focus {
    outline: 2px solid #15B97C;
    outline-offset: 2px;
}

.dropdown-item:focus {
    background: #15B97C;
    color: white;
    outline: none;
}

.user-data-form input:focus {
    box-shadow: 0 0 0 3px rgba(21, 185, 124, 0.1);
}

/* Tüm sayfalarda Giriş butonu (btn-one) için MAVİ ve KARE stil */
.right-widget .btn-one {
    background: rgba(52, 152, 219, 0.15) !important; /* Şeffaf mavi */
    color: #3498db !important;
    border: 1.5px solid #3498db !important;
    border-radius: 10px !important;
    padding: 12px 28px 12px 20px !important;
    font-size: 16px !important;
    font-weight: 600 !important;
    display: inline-flex !important;
    align-items: center;
    gap: 10px;
    box-shadow: none !important;
    min-width: 110px;
    transition: all 0.2s;
    backdrop-filter: blur(4px);
}
.right-widget .btn-one:hover {
    background: rgba(52, 152, 219, 0.25) !important;
    color: #fff !important;
    border-color: #2980b9 !important;
}
.right-widget .btn-one .icon-square {
    background: rgba(52,152,219,0.12);
    border-radius: 7px;
    width: 32px;
    height: 32px;
    display: inline-flex;
    align-items: center;
    justify-content: center;
    transition: background 0.2s;
}
.right-widget .btn-one .icon-square i {
    color: #3498db;
    font-size: 18px;
}
.right-widget .btn-one:hover .icon-square {
    background: rgba(52,152,219,0.22);
}

/* Modal arka planı için şeffaf ve blur efekt */
.modal-backdrop.show {
    opacity: 0.7 !important;
    background: rgba(30, 41, 59, 0.5) !important;
    backdrop-filter: blur(8px);
}
</style>
