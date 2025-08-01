<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında
include 'includes/session-check.php';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="keywords" content="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | 2025 Rehberi">
    <meta name="description" content="🏠 Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | İstanbul, yatırım konularında uzman rehberi. Gökhan Aydınlı Gayrimenkul&#039;dan profesyonel tavsiyelar. 2024 güncel bilgiler.">
    <meta property="og:site_name" content="Gökhan Aydınlı Blog">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi">
    <meta property="og:description" content="İstanbul&#039;un kalbinde, Beyoğlu&#039;nda konut yatırımı mı düşünüyorsunuz?  Tarihi doku, kültürel zenginlik ve yüksek kira getirisi sunan Beyoğlu&#039;nda yatırım fırsatlarını keşfedin!  Potansiyel riskleri ve kazançları değerlendirin, doğru konutu seçin ve karlı bir yatırım yapın.">
    <meta property="og:image" content="https://images.unsplash.com/photo-1744311910839-d3ad1221806a?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3NzIyMTF8MHwxfHNlYXJjaHwxfHxwcm9wZXJ0eSUyMHJlYWwlMjBlc3RhdGUlMjBwcm9wZXJ0eSUyMGhvdXNlJTIwYnVpbGRpbmclMjBhcmNoaXRlY3R1cmV8ZW58MHwwfHx8MTc1MjI0MjMzNnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <title>Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi | Gökhan Aydınlı Gayrimenkul</title>
    
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Blog1.php stili -->
    <style>
        .blog-details-one {
            padding: 120px 0;
        }
        .blog-meta-wrapper {
            background: #fff;
            border-radius: 20px;
            padding: 40px;
            box-shadow: 0 10px 30px rgba(0,0,0,0.1);
        }
        .blog-title h1 {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 700;
            line-height: 1.3;
            margin-bottom: 20px;
        }
        .post-date {
            color: #6b7280;
            font-size: 16px;
            margin-bottom: 30px;
            padding: 10px 0;
            border-bottom: 2px solid #e5e7eb;
        }
        .featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin: 30px 0;
        }
        .blog-content {
            font-size: 18px;
            line-height: 1.8;
            color: #374151;
        }
        .blog-content h5 {
            color: #1f2937;
            font-weight: 700;
            margin: 40px 0 20px 0;
            font-size: 1.5rem;
        }
        .blog-content h6 {
            color: #374151;
            font-weight: 600;
            margin: 30px 0 15px 0;
            font-size: 1.2rem;
        }
        .blog-content p {
            margin-bottom: 20px;
        }
        .blog-content ul, .blog-content ol {
            margin: 20px 0;
            padding-left: 30px;
        }
        .blog-content li {
            margin-bottom: 10px;
        }
        
        /* Dosya bilgisi */
        .file-info {
            background: #f8f9fa;
            padding: 10px;
            border-radius: 5px;
            font-size: 12px;
            color: #6c757d;
            text-align: center;
            margin-bottom: 20px;
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Navigation buraya gelecek -->
        
        <!-- Blog Details -->
        <div class="blog-details-one pt-180 lg-pt-150 pb-150 xl-pb-120">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-meta-wrapper pe-xxl-5">
                            <div class="file-info">📄 Dosya: blog-beyoglu-nda-gayrimenkul-yatirimi-rehberi-11-20250711-140000.php | 🆔 Blog ID: 11</div>
                            
                            <div class="blog-title">
                                <h1>Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi</h1>
                            </div>
                            <div class="post-date">
                                <strong>Gökhan Aydınlı</strong> • 01 Jan 1970 • 1 dk okuma
                            </div>
                            
                            <img src="https://images.unsplash.com/photo-1744311910839-d3ad1221806a?crop=entropy&amp;cs=tinysrgb&amp;fit=max&amp;fm=jpg&amp;ixid=M3w3NzIyMTF8MHwxfHNlYXJjaHwxfHxwcm9wZXJ0eSUyMHJlYWwlMjBlc3RhdGUlMjBwcm9wZXJ0eSUyMGhvdXNlJTIwYnVpbGRpbmclMjBhcmNoaXRlY3R1cmV8ZW58MHwwfHx8MTc1MjI0MjMzNnww&amp;ixlib=rb-4.1.0&amp;q=80&amp;w=1080" 
                                 alt="Beyoğlu&#039;nda Gayrimenkul Yatırımı Rehberi" 
                                 class="featured-image">
                            
                            <div class="blog-content">
                                <h3>🏢 Beyoğlu'nda Gayrimenkul Yatırımı Rehberi - Uzman Rehberi</h3>

<p><strong>Gayrimenkul sektöründe</strong> başarılı olmak için doğru bilgilere sahip olmak çok önemlidir. Bu rehberde <strong>İstanbul, yatırım, konut</strong> konularında uzman görüşlerimizi paylaşıyoruz.</p>

<h4>📊 Önemli Faktörler</h4>
<ul>
<li><strong>İstanbul</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Yatırım</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Konut</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Fiyat</strong> - Detaylı analiz ve değerlendirme</li>
<li><strong>Analiz</strong> - Detaylı analiz ve değerlendirme</li>
</ul>

<h4>💡 Uzman Önerileri</h4>
<blockquote>
<p><em>"15 yıllık deneyimimle, Beyoğlu'nda Gayrimenkul Yatırımı Rehberi konusunda en önemli nokta sabırlı olmak ve doğru analizler yapmaktır."</em></p>
<footer><strong>- Gökhan Aydınlı</strong></footer>
</blockquote>

<h4>🎯 Sonuç</h4>
<p><strong>Beyoğlu'nda Gayrimenkul Yatırımı Rehberi</strong> alanında başarılı olmak için yukarıdaki faktörleri göz önünde bulundurmanız önemlidir. Profesyonel destek almaktan çekinmeyin.</p>

<p><strong>İletişim:</strong> Gökhan Aydınlı Gayrimenkul olarak size en iyi hizmeti sunmaya hazırız. Detaylı bilgi için bizimle iletişime geçebilirsiniz.</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>
    </div>
</body>
</html>