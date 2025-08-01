<?php
// filepath: c:\xampp\htdocs\dashboard\GokhanAydinli\includes\modal-js.php
?>
<script>
// Modal fonksiyonları - STANDART
function switchToRegister() {
    const loginTab = document.querySelector('#fc1');
    const registerTab = document.querySelector('#fc2');
    const loginBtn = document.querySelector('[data-bs-target="#fc1"]');
    const registerBtn = document.querySelector('[data-bs-target="#fc2"]');
    
    if (loginTab && registerTab && loginBtn && registerBtn) {
        loginTab.classList.remove('show', 'active');
        registerTab.classList.add('show', 'active');
        loginBtn.classList.remove('active');
        registerBtn.classList.add('active');
    }
}

function switchToLogin() {
    const loginTab = document.querySelector('#fc1');
    const registerTab = document.querySelector('#fc2');
    const loginBtn = document.querySelector('[data-bs-target="#fc1"]');
    const registerBtn = document.querySelector('[data-bs-target="#fc2"]');
    
    if (loginTab && registerTab && loginBtn && registerBtn) {
        registerTab.classList.remove('show', 'active');
        loginTab.classList.add('show', 'active');
        registerBtn.classList.remove('active');
        loginBtn.classList.add('active');
    }
}

function showForgotPassword() {
    alert('Şifre sıfırlama linki e-posta adresinize gönderilecektir.');
}

function loginWithGoogle() {
    alert('Google ile giriş özelliği yakında aktif olacak.');
}

function loginWithFacebook() {
    alert('Facebook ile giriş özelliği yakında aktif olacak.');
}

// Bootstrap modal olayları
document.addEventListener('DOMContentLoaded', function() {
    // Modal açıldığında
    const loginModal = document.getElementById('loginModal');
    if (loginModal) {
        loginModal.addEventListener('shown.bs.modal', function () {
            // İlk input'a focus
            const firstInput = this.querySelector('input[type="email"]');
            if (firstInput) {
                firstInput.focus();
            }
        });
        
        // Modal kapandığında form resetle
        loginModal.addEventListener('hidden.bs.modal', function () {
            const forms = this.querySelectorAll('form');
            forms.forEach(form => form.reset());
            
            // Login tab'ına geri dön
            switchToLogin();
        });
    }
    
    // Form validasyonu
    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', function(e) {
            const email = this.querySelector('input[name="email"]').value.trim();
            const password = this.querySelector('input[name="password"]').value.trim();
            
            if (!email || !password) {
                e.preventDefault();
                alert('Lütfen tüm alanları doldurun.');
                return false;
            }
            
            if (!email.includes('@') || email.length < 5) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
            
            if (password.length < 3) {
                e.preventDefault();
                alert('Şifre en az 3 karakter olmalıdır.');
                return false;
            }
        });
    }
    
    // Register form
    const registerForm = document.getElementById('registerForm');
    if (registerForm) {
        registerForm.addEventListener('submit', function(e) {
            const fullname = this.querySelector('input[name="fullname"]').value.trim();
            const email = this.querySelector('input[name="email"]').value.trim();
            const password = this.querySelector('input[name="password"]').value.trim();
            const passwordConfirm = this.querySelector('input[name="password_confirm"]').value.trim();
            const terms = this.querySelector('input[name="terms"]').checked;
            
            if (!fullname || !email || !password || !passwordConfirm) {
                e.preventDefault();
                alert('Lütfen tüm zorunlu alanları doldurun.');
                return false;
            }
            
            if (fullname.length < 2) {
                e.preventDefault();
                alert('Ad soyad en az 2 karakter olmalıdır.');
                return false;
            }
            
            if (!email.includes('@') || email.length < 5) {
                e.preventDefault();
                alert('Geçerli bir e-posta adresi girin.');
                return false;
            }
            
            if (password.length < 6) {
                e.preventDefault();
                alert('Şifre en az 6 karakter olmalıdır.');
                return false;
            }
            
            if (password !== passwordConfirm) {
                e.preventDefault();
                alert('Şifreler eşleşmiyor.');
                return false;
            }
            
            if (!terms) {
                e.preventDefault();
                alert('Şartlar ve koşulları kabul etmelisiniz.');
                return false;
            }
        });
    }
    
    // Telefon formatlaması
    const phoneInputs = document.querySelectorAll('input[type="tel"]');
    phoneInputs.forEach(function(input) {
        input.addEventListener('input', function(e) {
            let value = e.target.value.replace(/\D/g, '');
            let formattedValue = '';
            
            if (value.length > 0) {
                if (value.length <= 3) {
                    formattedValue = value;
                } else if (value.length <= 6) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3);
                } else if (value.length <= 8) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6);
                } else if (value.length <= 10) {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 8) + ' ' + value.slice(8);
                } else {
                    formattedValue = value.slice(0, 3) + ' ' + value.slice(3, 6) + ' ' + value.slice(6, 8) + ' ' + value.slice(8, 10);
                }
            }
            
            e.target.value = formattedValue;
        });
    });
    
    // Password görünürlük toggle
    const passwordToggles = document.querySelectorAll('.passVicon');
    passwordToggles.forEach(function(toggle) {
        toggle.addEventListener('click', function() {
            const input = this.closest('.input-group-meta').querySelector('input');
            if (input) {
                if (input.type === 'password') {
                    input.type = 'text';
                    this.innerHTML = '<i class="fa fa-eye-slash"></i>';
                } else {
                    input.type = 'password';
                    this.innerHTML = '<i class="fa fa-eye"></i>';
                }
            }
        });
    });
});

// Bootstrap dropdown initialize
document.addEventListener('DOMContentLoaded', function() {
    // Bootstrap dropdown'ları manuel olarak başlat
    const dropdownElementList = document.querySelectorAll('.dropdown-toggle');
    if (typeof bootstrap !== 'undefined' && bootstrap.Dropdown) {
        dropdownElementList.forEach(function(dropdownToggleEl) {
            new bootstrap.Dropdown(dropdownToggleEl);
        });
    }
});

// Global error handler
window.addEventListener('error', function(e) {
    console.error('JavaScript Error:', e.error);
});
</script>