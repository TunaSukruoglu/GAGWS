<?php
session_start();
include '../db.php';

// Giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// Kullanıcı bilgilerini veritabanından çek
$stmt = $conn->prepare("SELECT * FROM users WHERE id = ?");
$stmt->bind_param("i", $user_id);
$stmt->execute();
$user = $stmt->get_result()->fetch_assoc();

// Admin değilse normal dashboard'a yönlendir
if (!$user || ($user['role'] !== 'admin' && $user['role'] !== 'root')) {
    $_SESSION['error_message'] = "Bu sayfaya erişim yetkiniz yok. Admin yetkisi gereklidir.";
    header("Location: dashboard.php");
    exit;
}

// Sayfa başlığı
$page_title = "AI Blog Oluşturucu";
$current_page = 'admin-blog-ai';
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    <link rel="stylesheet" href="includes/dashboard-common.css">
    
    <style>
        /* AI Blog Creator Specific Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .ai-banner {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            position: relative;
            overflow: hidden;
        }
        
        .ai-banner::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><circle cx="20" cy="20" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="80" cy="80" r="2" fill="rgba(255,255,255,0.1)"/><circle cx="60" cy="20" r="1" fill="rgba(255,255,255,0.1)"/><circle cx="20" cy="80" r="1" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
        }
        
        .ai-banner-content {
            position: relative;
            z-index: 1;
        }
        
        .ai-form-card {
            background: white;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            border: 1px solid #f0f0f0;
        }
        
        .coming-soon-card {
            background: linear-gradient(135deg, #ff6b6b 0%, #ee5a24 100%);
            color: white;
            padding: 60px 40px;
            border-radius: 20px;
            text-align: center;
            position: relative;
            overflow: hidden;
        }
        
        .coming-soon-card::before {
            content: '';
            position: absolute;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: url('data:image/svg+xml,<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 100 100"><polygon points="50,0 60,35 100,35 70,55 80,90 50,70 20,90 30,55 0,35 40,35" fill="rgba(255,255,255,0.1)"/></svg>') repeat;
            opacity: 0.3;
        }
        
        .coming-soon-content {
            position: relative;
            z-index: 1;
        }
        
        .ai-icon-large {
            width: 100px;
            height: 100px;
            border-radius: 50%;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 40px;
            margin: 0 auto 30px;
            animation: float 3s ease-in-out infinite;
        }
        
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-10px); }
        }
        
        .feature-list {
            list-style: none;
            padding: 0;
        }
        
        .feature-list li {
            padding: 15px 0;
            border-bottom: 1px solid rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            gap: 15px;
        }
        
        .feature-list li:last-child {
            border-bottom: none;
        }
        
        .feature-icon {
            width: 40px;
            height: 40px;
            border-radius: 10px;
            background: rgba(255, 255, 255, 0.2);
            display: flex;
            align-items: center;
            justify-content: center;
        }
    </style>
</head>

<body class="admin-dashboard">
    <!-- Include Sidebar -->
    <?php include 'includes/sidebar-admin.php'; ?>
    
    <!-- Dashboard Body -->
    <div class="dashboard-body">
        <div class="main-content">
            <!-- AI Banner -->
            <div class="ai-banner">
                <div class="ai-banner-content">
                    <h2 class="mb-3">
                        <i class="fas fa-robot me-3"></i>AI Blog Oluşturucu
                    </h2>
                    <p class="mb-0">Yapay zeka destekli blog yazısı oluşturma sistemi</p>
                </div>
            </div>
            
            <!-- Coming Soon Section -->
            <div class="coming-soon-card">
                <div class="coming-soon-content">
                    <div class="ai-icon-large">
                        <i class="fas fa-magic"></i>
                    </div>
                    
                    <h2 class="mb-4">Çok Yakında!</h2>
                    <p class="lead mb-4">AI Blog Oluşturucu özelliği geliştiriliyor. Bu güçlü araç ile sadece birkaç anahtar kelime girerek profesyonel blog yazıları oluşturabileceksiniz.</p>
                    
                    <div class="row">
                        <div class="col-md-8 mx-auto">
                            <h4 class="mb-4">Gelecek Özellikler:</h4>
                            <ul class="feature-list">
                                <li>
                                    <div class="feature-icon">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <div>
                                        <strong>Akıllı İçerik Üretimi</strong><br>
                                        Konu başlığından otomatik içerik oluşturma
                                    </div>
                                </li>
                                <li>
                                    <div class="feature-icon">
                                        <i class="fas fa-language"></i>
                                    </div>
                                    <div>
                                        <strong>Çoklu Dil Desteği</strong><br>
                                        Türkçe, İngilizce ve diğer dillerde içerik
                                    </div>
                                </li>
                                <li>
                                    <div class="feature-icon">
                                        <i class="fas fa-image"></i>
                                    </div>
                                    <div>
                                        <strong>Otomatik Görsel Önerisi</strong><br>
                                        İçeriğe uygun görsel önerileri
                                    </div>
                                </li>
                                <li>
                                    <div class="feature-icon">
                                        <i class="fas fa-tags"></i>
                                    </div>
                                    <div>
                                        <strong>Akıllı Etiketleme</strong><br>
                                        Otomatik kategori ve tag ataması
                                    </div>
                                </li>
                                <li>
                                    <div class="feature-icon">
                                        <i class="fas fa-search"></i>
                                    </div>
                                    <div>
                                        <strong>SEO Optimizasyonu</strong><br>
                                        Arama motoru dostu içerik üretimi
                                    </div>
                                </li>
                            </ul>
                        </div>
                    </div>
                    
                    <div class="mt-5">
                        <a href="admin-blog.php" class="btn btn-light btn-lg">
                            <i class="fas fa-arrow-left me-2"></i>
                            Blog Yönetimine Dön
                        </a>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
</body>
</html>
