<?php
// filepath: c:\xampp\htdocs\dashboard\GokhanAydinli\includes\login-modal.php
?>
<!-- ################### Login Modal ####################### -->
<div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-fullscreen modal-dialog-centered">
        <div class="container">
            <div class="user-data-form modal-content">
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                <div class="form-wrapper m-auto">
                    <ul class="nav nav-tabs w-100" role="tablist">
                        <li class="nav-item" role="presentation">
                            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş</button>
                        </li>
                        <li class="nav-item" role="presentation">
                            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Kayıt</button>
                        </li>
                    </ul>
                    <div class="tab-content mt-30">
                        <div class="tab-pane show active" role="tabpanel" id="fc1">
                            <div class="text-center mb-20">
                                <h2>Hoş Geldiniz!</h2>
                                <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#" onclick="switchToRegister()">Kayıt olun</a></p>
                            </div>
                            <form action="login.php" method="POST" id="loginForm">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-25">
                                            <label>E-posta*</label>
                                            <input type="email" name="email" placeholder="ornek@email.com" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-20">
                                            <label>Şifre*</label>
                                            <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                            <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="checkbox" id="remember" name="remember">
                                                <label for="remember">Beni hatırla</label>
                                            </div>
                                            <a href="#" onclick="showForgotPassword()">Şifremi Unuttum?</a>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                        <div class="tab-pane" role="tabpanel" id="fc2">
                            <div class="text-center mb-20">
                                <h2>Kayıt Ol</h2>
                                <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="switchToLogin()">Giriş yapın</a></p>
                            </div>
                            <form action="register.php" method="POST" id="registerForm">
                                <div class="row">
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-25">
                                            <label>Ad Soyad*</label>
                                            <input type="text" name="fullname" placeholder="Ad Soyadınız" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-25">
                                            <label>E-posta*</label>
                                            <input type="email" name="email" placeholder="ornek@email.com" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-25">
                                            <label>Telefon</label>
                                            <input type="tel" name="phone" placeholder="0555 555 55 55">
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-20">
                                            <label>Şifre*</label>
                                            <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                            <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="input-group-meta position-relative mb-20">
                                            <label>Şifre Tekrar*</label>
                                            <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                            <div>
                                                <input type="checkbox" id="terms" name="terms" required>
                                                <label for="terms">"Kayıt Ol" butonuna tıklayarak <a href="terms.php" target="_blank">Şartlar & Koşullar</a> ile <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı kabul ediyorum</label>
                                            </div>
                                        </div>
                                    </div>
                                    <div class="col-12">
                                        <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
                                    </div>
                                </div>
                            </form>
                        </div>
                        <!-- /.tab-pane -->
                    </div>
                    
                    <div class="d-flex align-items-center mt-30 mb-10">
                        <div class="line"></div>
                        <span class="pe-3 ps-3 fs-6">VEYA</span>
                        <div class="line"></div>
                    </div>
                    <div class="row">
                        <div class="col-sm-6">
                            <a href="#" onclick="loginWithGoogle()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                <img src="images/icon/google.png" alt="">
                                <span class="ps-3">Google ile Giriş</span>
                            </a>
                        </div>
                        <div class="col-sm-6">
                            <a href="#" onclick="loginWithFacebook()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                <img src="images/icon/facebook.png" alt="">
                                <span class="ps-3">Facebook ile Giriş</span>
                            </a>
                        </div>
                    </div>
                </div>
                <!-- /.form-wrapper -->
            </div>
            <!-- /.user-data-form -->
        </div>
    </div>
</div>