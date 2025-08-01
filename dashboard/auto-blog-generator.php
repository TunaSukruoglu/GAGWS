<?php
// Debug için hataları göster
ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

session_start();
include '../db.php';
include 'includes/unsplash-api.php';
include 'includes/gemini-ai.php'; // 🤖 Gerçek AI entegrasyonu

// Slug oluşturma fonksiyonu
function createSlug($text) {
    // Türkçe karakterleri değiştir
    $turkish = array('ç', 'ğ', 'ı', 'ö', 'ş', 'ü', 'Ç', 'Ğ', 'I', 'İ', 'Ö', 'Ş', 'Ü');
    $english = array('c', 'g', 'i', 'o', 's', 'u', 'c', 'g', 'i', 'i', 'o', 's', 'u');
    $text = str_replace($turkish, $english, $text);
    
    // Küçük harfe çevir
    $text = strtolower($text);
    
    // Özel karakterleri temizle
    $text = preg_replace('/[^a-z0-9\s-]/', '', $text);
    
    // Boşlukları tire ile değiştir
    $text = preg_replace('/[\s-]+/', '-', $text);
    
    // Başındaki ve sonundaki tireleri temizle
    $text = trim($text, '-');
    
    // Benzersizlik için timestamp ekle
    $text .= '-' . time();
    
    return $text;
}

// Basit admin kontrolü - session var mı?
if (!isset($_SESSION['user_id'])) {
    // DEBUG İÇİN GEÇİCİ BYPASS
    $_SESSION['user_id'] = 1; // Geçici admin ID
    $_SESSION['user_name'] = 'Debug User';
    // header("Location: ../login.php");
    // exit;
}

$user_id = $_SESSION['user_id'];
$current_page = 'auto-blog-generator';

// AI instance oluştur
$ai = new GeminiAI();

// Veritabanı bağlantısını test et
if (!$conn) {
    die("❌ Veritabanı bağlantısı başarısız!");
}

// Blogs tablosunun varlığını kontrol et
$table_check = $conn->query("SHOW TABLES LIKE 'blogs'");
if ($table_check->num_rows == 0) {
    die("❌ 'blogs' tablosu bulunamadı!");
}

// AI sınıfının çalışıp çalışmadığını test et
if (!class_exists('GeminiAI')) {
    die("❌ GeminiAI sınıfı yüklenemedi!");
}

// Test için AI metodunu çağır
try {
    $test_excerpt = $ai->generateExcerpt("Test", ["test"]);
    echo "<!-- AI TEST BAŞARILI: " . strlen($test_excerpt) . " karakter -->";
} catch (Exception $e) {
    echo "<!-- AI TEST HATASI: " . $e->getMessage() . " -->";
}

// Blog oluşturma fonksiyonu
function generateAutoBlog($title, $keywords, $tone = 'professional', $length = 'medium') {
    // Ton seçenekleri
    $tones = [
        'professional' => ['ciddi', 'uzman', 'güvenilir', 'detaylı'],
        'friendly' => ['samimi', 'anlaşılır', 'dostane', 'pratik'],
        'authoritative' => ['otoriter', 'bilimsel', 'kesin', 'kanıtlı']
    ];
    
    // Gayrimenkul ile ilgili şablonlar
    $templates = [
        'yatirim' => [
            'intro' => "Gayrimenkul yatırımı, uzun vadeli finansal güvenlik için en tercih edilen yöntemlerden biridir.",
            'points' => [
                "Piyasa analizi ve doğru lokasyon seçimi",
                "Finansman seçenekleri ve kredi imkanları", 
                "Yasal süreçler ve evrak işlemleri",
                "Uzun vadeli getiri hesaplamaları"
            ]
        ],
        'satin-alma' => [
            'intro' => "Ev satın alma süreci, hayatınızın en önemli kararlarından biridir.",
            'points' => [
                "Bütçe planlama ve finansman seçenekleri",
                "Lokasyon analizi ve çevre faktörleri",
                "Yapısal kontrol ve teknik inceleme", 
                "Hukuki süreçler ve tapu işlemleri"
            ]
        ],
        'piyasa' => [
            'intro' => "Güncel piyasa verileri, doğru yatırım kararları için vazgeçilmezdir.",
            'points' => [
                "Fiyat trendleri ve piyasa dinamikleri",
                "Bölgesel değer artış potansiyeli",
                "Ekonomik faktörlerin etkisi",
                "Gelecek projeksiyonları"
            ]
        ]
    ];
    
    // Anahtar kelimelere göre şablon seç
    $template_key = 'yatirim'; // varsayılan
    foreach ($keywords as $keyword) {
        $keyword = strtolower($keyword);
        if (strpos($keyword, 'satın') !== false || strpos($keyword, 'alma') !== false) {
            $template_key = 'satin-alma';
            break;
        } elseif (strpos($keyword, 'piyasa') !== false || strpos($keyword, 'analiz') !== false) {
            $template_key = 'piyasa';
            break;
        }
    }
    
    $template = $templates[$template_key];
    
    // Blog içeriği oluştur
    $content = "<h3>Giriş</h3>\n";
    $content .= "<p>{$template['intro']} <strong>{$title}</strong> konusunda uzman görüşleri ve pratik bilgileri sizlerle paylaşıyoruz.</p>\n\n";
    
    $content .= "<h3>Önemli Noktalar</h3>\n";
    $content .= "<p>Bu konuda dikkat edilmesi gereken temel başlıklar şunlardır:</p>\n";
    $content .= "<ul>\n";
    foreach ($template['points'] as $point) {
        $content .= "<li><strong>{$point}</strong></li>\n";
    }
    $content .= "</ul>\n\n";
    
    // Anahtar kelimeler bölümü
    $content .= "<h4>Detaylı İnceleme</h4>\n";
    foreach ($keywords as $index => $keyword) {
        $content .= "<h5>" . ucfirst($keyword) . "</h5>\n";
        $content .= "<p>" . generateParagraphForKeyword($keyword, $tone) . "</p>\n\n";
        
        // Her 2 anahtar kelimede bir resim önerisi ekle
        if (($index + 1) % 2 == 0) {
            $content .= "<p><em>[Bu bölümde '{$keyword}' ile ilgili görsel ekleyebilirsiniz]</em></p>\n\n";
        }
    }
    
    // Uzman görüşü bölümü
    $content .= "<h3>Uzman Önerisi</h3>\n";
    $content .= "<blockquote>\n";
    $content .= "<p><strong>Gökhan Aydınlı'dan Tavsiye:</strong> ";
    $content .= generateExpertAdvice($title, $keywords) . "</p>\n";
    $content .= "</blockquote>\n\n";
    
    // Sonuç
    $content .= "<h3>Sonuç</h3>\n";
    $content .= "<p><strong>{$title}</strong> konusunda başarılı olmak için yukarıda belirtilen noktaları dikkate almanız önemlidir. ";
    $content .= "Profesyonel destek almaktan çekinmeyin ve her adımda uzman görüşü alın.</p>\n\n";
    
    $content .= "<p><strong>İletişim için:</strong> Gökhan Aydınlı Gayrimenkul ekibi olarak size en iyi hizmeti sunmaya hazırız.</p>";
    
    return $content;
}

// Anahtar kelime için paragraf oluştur
function generateParagraphForKeyword($keyword, $tone) {
    $keyword = strtolower($keyword);
    
    $paragraphs = [
        'istanbul' => "İstanbul'da gayrimenkul sektörü sürekli gelişim halindedir. Şehrin farklı bölgelerinde farklı fırsatlar bulunmaktadır.",
        'yatırım' => "Gayrimenkul yatırımı yaparken piyasa araştırması ve uzun vadeli planlama büyük önem taşır.",
        'konut' => "Konut seçiminde lokasyon, yapı kalitesi ve çevre imkanları en önemli kriterlerdir.",
        'fiyat' => "Fiyat analizi yaparken bölgenin gelişim potansiyeli ve altyapı yatırımları göz önünde bulundurulmalıdır.",
        'kiralama' => "Kiralama yatırımında lokasyon ve hedef kitle analizi kritik başarı faktörleridir.",
        'satış' => "Satış sürecinde doğru fiyatlama ve pazarlama stratejisi belirlenmesi önemlidir."
    ];
    
    return $paragraphs[$keyword] ?? "Bu konuda detaylı araştırma yapmak ve uzman görüşü almak önemlidir. Piyasa dinamiklerini takip ederek doğru kararlar verebilirsiniz.";
}

// Uzman tavsiyesi oluştur
function generateExpertAdvice($title, $keywords) {
    $advices = [
        "Bu alanda 15 yıllık deneyimim ile söyleyebilirim ki, sabırlı ve araştırarak hareket eden yatırımcılar her zaman kazançlı çıkar.",
        "Piyasayı yakından takip etmek ve doğru zamanlama yapmak başarının anahtarıdır.",
        "Gayrimenkul yatırımında acele kararlar almak yerine, kapsamlı analiz yapmak her zaman daha iyidir.",
        "Bu konuda deneyimli bir danışman ile çalışmak, hem zaman kaybını önler hem de daha karlı yatırımlar yapmanızı sağlar."
    ];
    
    return $advices[array_rand($advices)];
}

// DEBUG: Form işleme süreci (session-based debug)
$debug_info = [];

// HEMEN TEST ET
if ($_SERVER['REQUEST_METHOD'] == 'POST') {
    $debug_info[] = "🔥 POST request geldi!";
    $debug_info[] = "📮 POST data: " . json_encode(array_keys($_POST));
    
    if (isset($_POST['generate_blog'])) {
        $debug_info[] = "✅ generate_blog parametresi var!";
    } else {
        $debug_info[] = "❌ generate_blog parametresi YOK!";
        $debug_info[] = "📋 Mevcut POST parametreleri: " . implode(', ', array_keys($_POST));
    }
}

// Form işleme
if ($_SERVER['REQUEST_METHOD'] == 'POST' && isset($_POST['generate_blog'])) {
    try {
        // Debug bilgileri (session'a da yaz)
        $debug_info[] = "✅ Form gönderildi: " . date('H:i:s');
        error_log("AUTO BLOG DEBUG: Form gönderildi");
        
        $auto_title = trim($_POST['auto_title']);
        $auto_keywords = array_filter(array_map('trim', explode(',', $_POST['auto_keywords'])));
        $tone = $_POST['tone'] ?? 'professional';
        $length = $_POST['length'] ?? 'medium';
        $auto_status = $_POST['auto_status'] ?? 'draft';
        
        $debug_info[] = "📝 Başlık: " . $auto_title;
        $debug_info[] = "🏷️ Kelimeler: " . implode(', ', $auto_keywords);
        $debug_info[] = "🎭 Ton: " . $tone . ", Uzunluk: " . $length;
        
        error_log("AUTO BLOG DEBUG: Başlık = " . $auto_title);
        error_log("AUTO BLOG DEBUG: Anahtar kelimeler = " . implode(', ', $auto_keywords));
        
        if (empty($auto_title) || empty($auto_keywords)) {
            $debug_info[] = "❌ Validasyon hatası: Başlık veya kelimeler boş";
            throw new Exception("Başlık ve anahtar kelimeler zorunludur.");
        }
        $debug_info[] = "✅ Validasyon geçti";
        
        // AI instance kontrolü
        if (!$ai || !($ai instanceof GeminiAI)) {
            $debug_info[] = "❌ AI instance bulunamadı";
            throw new Exception("AI instance bulunamadı");
        }
        $debug_info[] = "✅ AI instance hazır";
        
        // Otomatik içerik oluştur - GERÇEK AI İLE! 🤖
        $debug_info[] = "🤖 AI ile içerik oluşturuluyor...";
        error_log("AUTO BLOG DEBUG: AI ile içerik oluşturuluyor...");
        
        $auto_content = $ai->generateRealEstateBlog($auto_title, $auto_keywords, $tone, $length);
        
        $debug_info[] = "📄 İçerik oluşturuldu: " . strlen($auto_content) . " karakter";
        error_log("AUTO BLOG DEBUG: İçerik oluşturuldu, uzunluk: " . strlen($auto_content));
        
        // İçeriği kontrol et
        if (empty($auto_content) || strlen($auto_content) < 100) {
            $debug_info[] = "❌ AI içerik problemi - uzunluk: " . strlen($auto_content);
            $debug_info[] = "📋 İçerik önizleme: " . substr($auto_content, 0, 200);
            error_log("AUTO BLOG DEBUG: AI içerik problemi - uzunluk: " . strlen($auto_content));
            error_log("AUTO BLOG DEBUG: İçerik önizleme: " . substr($auto_content, 0, 200));
            throw new Exception("AI içerik oluşturamadı veya çok kısa (uzunluk: " . strlen($auto_content) . ")");
        }
        $debug_info[] = "✅ İçerik kontrolü geçti";
        
        // Excerpt oluştur - AI ile
        $debug_info[] = "📝 Excerpt oluşturuluyor...";
        error_log("AUTO BLOG DEBUG: Excerpt oluşturuluyor...");
        $auto_excerpt = $ai->generateExcerpt($auto_title, $auto_keywords);
        $debug_info[] = "✅ Excerpt hazır: " . substr($auto_excerpt, 0, 50) . "...";
        error_log("AUTO BLOG DEBUG: Excerpt oluşturuldu: " . $auto_excerpt);
        
        // Slug oluştur
        $auto_slug = createSlug($auto_title);
        
        // Aynı slug varsa benzersiz yap
        $slug_check = $conn->prepare("SELECT id FROM blogs WHERE slug = ?");
        if (!$slug_check) {
            throw new Exception("Slug kontrolü SQL hatası: " . $conn->error);
        }
        $slug_check->bind_param("s", $auto_slug);
        $slug_check->execute();
        if ($slug_check->get_result()->num_rows > 0) {
            $auto_slug = $auto_slug . '-' . time();
        }
        
        // Otomatik resim al
        $debug_info[] = "🖼️ Resim alınıyor (anahtar kelime bazlı)...";
        $debug_info[] = "🔍 Anahtar kelimeler: " . implode(', ', $auto_keywords);
        error_log("AUTO BLOG DEBUG: Resim alınıyor...");
        $unsplash = new UnsplashAPI();
        
        // Yeni anahtar kelime bazlı arama kullan
        $image_data = $unsplash->searchPhotoByKeywords($auto_title, $auto_keywords);
        
        if (empty($image_data['url'])) {
            $debug_info[] = "⚠️ Anahtar kelime araması başarısız, demo resim kullanılıyor";
            $image_data = $unsplash->getDemoImage($auto_title);
        } else {
            $debug_info[] = "✅ Resim bulundu: " . basename($image_data['url']);
        }
        $debug_info[] = "🖼️ Final resim URL: " . substr($image_data['url'], 0, 80) . "...";
        error_log("AUTO BLOG DEBUG: Resim URL: " . $image_data['url']);
        
        // Meta bilgileri oluştur - AI ile
        $meta_title = $auto_title . " | " . date('Y') . " Rehberi";
        $meta_description = $ai->generateMetaDescription($auto_title, $auto_keywords);
        
        // Slug oluştur (URL dostu)
        $auto_slug = createSlug($auto_title);
        $debug_info[] = "🔗 Slug oluşturuldu: " . $auto_slug;
        
        // Okuma süresi hesapla (ortalama 200 kelime/dakika)
        $word_count = str_word_count(strip_tags($auto_content));
        $reading_time = max(1, ceil($word_count / 200));
        
        // Publish date
        $publish_date = null;
        if ($auto_status == 'published') {
            $publish_date = date('Y-m-d H:i:s');
        }
        
        $debug_info[] = "💾 Veritabanına kaydediliyor...";
        error_log("AUTO BLOG DEBUG: Veritabanına kaydediliyor...");
        
        // Blog kaydet
        $insert_blog = $conn->prepare("
            INSERT INTO blogs (title, slug, excerpt, content, featured_image, status, reading_time, 
                              meta_title, meta_description, featured, publish_date) 
            VALUES (?, ?, ?, ?, ?, ?, ?, ?, ?, 0, ?)
        ");
        
        if (!$insert_blog) {
            throw new Exception("Blog insert SQL hatası: " . $conn->error);
        }
        
        $insert_blog->bind_param("ssssssssss", 
            $auto_title, $auto_slug, $auto_excerpt, $auto_content, $image_data['url'], 
            $auto_status, $reading_time, $meta_title, $meta_description, $publish_date
        );
        
        if ($insert_blog->execute()) {
            $debug_info[] = "✅ Blog veritabanına kaydedildi";
            error_log("AUTO BLOG DEBUG: Blog veritabanına kaydedildi");
            $blog_id = $conn->insert_id;
            $debug_info[] = "🆔 Blog ID: " . $blog_id;
            error_log("AUTO BLOG DEBUG: Blog ID = " . $blog_id);
            
            // Anahtar kelimeleri etiket olarak ekle
            $debug_info[] = "🏷️ Etiketler ekleniyor...";
            error_log("AUTO BLOG DEBUG: Etiketler ekleniyor...");
            foreach ($auto_keywords as $keyword) {
                $keyword_slug = createSlug($keyword);
                
                // Etiketi kontrol et, yoksa oluştur
                $tag_check = $conn->prepare("SELECT id FROM blog_tags WHERE slug = ?");
                $tag_check->bind_param("s", $keyword_slug);
                $tag_check->execute();
                $tag_result = $tag_check->get_result();
                
                if ($tag_result->num_rows > 0) {
                    $tag_id = $tag_result->fetch_assoc()['id'];
                } else {
                    $insert_tag = $conn->prepare("INSERT INTO blog_tags (name, slug) VALUES (?, ?)");
                    $insert_tag->bind_param("ss", $keyword, $keyword_slug);
                    $insert_tag->execute();
                    $tag_id = $conn->insert_id;
                }
                
                // Blog-etiket ilişkisi ekle
                $insert_relation = $conn->prepare("INSERT IGNORE INTO blog_tag_relations (blog_id, tag_id) VALUES (?, ?)");
                $insert_relation->bind_param("ii", $blog_id, $tag_id);
                $insert_relation->execute();
            }
            
            // Blog dosyası oluştur
            $debug_info[] = "📄 Blog dosyası oluşturuluyor...";
            error_log("AUTO BLOG DEBUG: Blog dosyası oluşturuluyor...");
            $created_filename = createBlogFile($blog_id, $conn);
            
            if ($created_filename) {
                $update_filename = $conn->prepare("UPDATE blogs SET blog_file = ? WHERE id = ?");
                $update_filename->bind_param("si", $created_filename, $blog_id);
                $update_filename->execute();
                $debug_info[] = "✅ Blog dosyası: " . $created_filename;
                error_log("AUTO BLOG DEBUG: Blog dosyası güncellendi: " . $created_filename);
            }
            
            $debug_info[] = "🎉 BAŞARI! Blog oluşturuldu";
            
            $success_message = "🤖 " . ($ai->isApiActive() ? "GERÇEK AI" : "DEMO AI") . " ile otomatik blog başarıyla oluşturuldu! Başlık: '{$auto_title}' | Kelime sayısı: {$word_count} | Okuma süresi: {$reading_time} dk";
            if ($created_filename) {
                $success_message .= " | Dosya: {$created_filename}";
            }
            
            // Başarılı işlem sonrası POST verisini temizle
            $success_generated = true;
            error_log("AUTO BLOG DEBUG: BAŞARILI! Mesaj: " . $success_message);
            
        } else {
            $debug_info[] = "❌ Veritabanı hatası: " . $conn->error;
            throw new Exception("Blog kaydedilirken hata oluştu: " . $conn->error);
        }
        
    } catch (Exception $e) {
        $debug_info[] = "💥 HATA: " . $e->getMessage();
        error_log("AUTO BLOG ERROR: " . $e->getMessage());
        error_log("AUTO BLOG ERROR TRACE: " . $e->getTraceAsString());
        $error_message = "❌ Hata: " . $e->getMessage();
    }
    
    // Debug bilgilerini session'a kaydet
    $_SESSION['debug_info'] = $debug_info;
} else {
    // Form gönderilmedi veya parametreler eksik
    if ($_SERVER['REQUEST_METHOD'] == 'POST') {
        $debug_info[] = "⚠️ Form gönderildi ama generate_blog parametresi eksik";
    }
    
    // Debug bilgilerini session'a kaydet (eğer varsa)
    if (!empty($debug_info)) {
        $_SESSION['debug_info'] = $debug_info;
    }
}

// Blog dosyası oluşturma fonksiyonu
function createBlogFile($blog_id, $conn) {
    $blog_query = $conn->prepare("SELECT * FROM blogs WHERE id = ?");
    $blog_query->bind_param("i", $blog_id);
    $blog_query->execute();
    $blog = $blog_query->get_result()->fetch_assoc();
    
    if ($blog) {
        $base_slug = $blog['slug'];
        $timestamp = date('Ymd-His');
        $unique_filename = "auto-blog-{$base_slug}-{$blog_id}-{$timestamp}.php";
        
        $counter = 1;
        // Blogs klasöründe dosya var mı kontrol et
        while (file_exists("../blogs/{$unique_filename}")) {
            $unique_filename = "auto-blog-{$base_slug}-{$blog_id}-{$timestamp}-{$counter}.php";
            $counter++;
        }
        
        $filename = "../blogs/{$unique_filename}";
        $template = generateBlogTemplate($blog, $unique_filename);
        file_put_contents($filename, $template);
        
        return "blogs/" . $unique_filename; // Relative path for database
    }
    return false;
}

// Blog template oluşturma
function generateBlogTemplate($blog, $filename = '') {
    $formatted_date = $blog['publish_date'] ? date('d M Y', strtotime($blog['publish_date'])) : date('d M Y');
    
    $template = '<?php
session_start();

// Kullanıcı giriş bilgileri
$isLoggedIn = isset($_SESSION[\'user_id\']);
$userName = $isLoggedIn ? $_SESSION[\'user_name\'] ?? \'Kullanıcı\' : \'\';

// Türkçe karakter desteği
header(\'Content-Type: text/html; charset=UTF-8\');
ini_set(\'default_charset\', \'UTF-8\');
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="keywords" content="' . htmlspecialchars($blog['meta_title']) . '">
    <meta name="description" content="' . htmlspecialchars($blog['meta_description']) . '">
    <meta property="og:title" content="' . htmlspecialchars($blog['title']) . '">
    <meta property="og:description" content="' . htmlspecialchars($blog['excerpt']) . '">
    <meta property="og:image" content="' . htmlspecialchars($blog['featured_image']) . '">
    <meta property="og:type" content="article">
    <title>' . htmlspecialchars($blog['title']) . ' | Gökhan Aydınlı Gayrimenkul</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" type="text/css" href="../css/bootstrap.min.css">
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <style>
        .blog-content-wrapper {
            padding: 80px 0;
            background: #f8f9fa;
        }
        .blog-article {
            background: #fff;
            border-radius: 15px;
            padding: 60px;
            box-shadow: 0 10px 40px rgba(0,0,0,0.1);
            margin-bottom: 40px;
        }
        .blog-header {
            text-align: center;
            margin-bottom: 50px;
            padding-bottom: 30px;
            border-bottom: 2px solid #e5e7eb;
        }
        .blog-title {
            color: #1f2937;
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 20px;
            line-height: 1.3;
        }
        .blog-meta {
            color: #6b7280;
            font-size: 16px;
            display: flex;
            align-items: center;
            justify-content: center;
            gap: 20px;
            flex-wrap: wrap;
        }
        .blog-featured-image {
            width: 100%;
            height: 400px;
            object-fit: cover;
            border-radius: 15px;
            margin: 40px 0;
        }
        .blog-content {
            font-size: 18px;
            line-height: 1.8;
            color: #374151;
        }
        .blog-content h3, .blog-content h4, .blog-content h5 {
            color: #1f2937;
            margin-top: 40px;
            margin-bottom: 20px;
        }
        .blog-content p {
            margin-bottom: 20px;
        }
        .blog-content ul, .blog-content ol {
            margin-bottom: 25px;
            padding-left: 30px;
        }
        .blog-content li {
            margin-bottom: 10px;
        }
        .blog-content blockquote {
            background: #f8f9fa;
            border-left: 5px solid #007bff;
            padding: 25px;
            margin: 30px 0;
            border-radius: 0 10px 10px 0;
        }
        .ai-badge {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            color: white;
            padding: 10px 20px;
            border-radius: 25px;
            font-size: 14px;
            display: inline-block;
            margin-bottom: 30px;
        }
        .blog-footer-info {
            margin-top: 50px;
            padding-top: 30px;
            border-top: 1px solid #e5e7eb;
            text-align: center;
            color: #6b7280;
            font-size: 14px;
        }
        @media (max-width: 768px) {
            .blog-article {
                padding: 30px 20px;
            }
            .blog-title {
                font-size: 1.8rem;
            }
        }
    </style>
</head>
<body>
    <div class="main-page-wrapper">
        <!-- Header -->
        <header class="theme-main-menu menu-overlay menu-style-seven white-vr sticky-menu">
            <div class="inner-content gap-one">
                <div class="top-header position-relative">
                    <div class="d-flex align-items-center justify-content-between">
                        <div class="logo order-lg-0">
                            <a href="../index.php" class="d-flex align-items-center">
                                <img src="../images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;">
                            </a>
                        </div>
                        
                        <!-- Navigation -->
                        <nav class="navbar navbar-expand-lg p0 order-lg-2">
                            <button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNav">
                                <span></span>
                            </button>
                            <div class="collapse navbar-collapse" id="navbarNav">
                                <ul class="navbar-nav align-items-lg-center">
                                    <li class="nav-item">
                                        <a class="nav-link" href="../index.php">Ana Sayfa</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../hakkimizda.php">Hakkımızda</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../porfoy.html">Portföy</a>
                                    </li>
                                    <li class="nav-item">
                                        <a class="nav-link" href="../contact.php">İletişim</a>
                                    </li>
                                </ul>
                            </div>
                        </nav>
                        
                        <!-- Auth Widget -->
                        <div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
                            <ul class="d-flex align-items-center style-none">
                                <?php if ($isLoggedIn): ?>
                                    <li class="dropdown">
                                        <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                                            <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                                        </a>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="../dashboard/dashboard-admin.php">Panel</a></li>
                                            <li><a class="dropdown-item" href="../logout.php">Çıkış Yap</a></li>
                                        </ul>
                                    </li>
                                <?php else: ?>
                                    <li>
                                        <a href="../login.php" class="btn-one">
                                            <i class="fa-regular fa-lock"></i> <span>Giriş</span>
                                        </a>
                                    </li>
                                <?php endif; ?>
                            </ul>
                        </div>
                    </div>
                </div>
            </div>
        </header>

        <!-- Blog Content -->
        <div class="blog-content-wrapper">
            <div class="container">
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <article class="blog-article">
                            <div class="ai-badge">
                                🤖 AI Destekli Otomatik Blog
                            </div>
                            
                            <div class="blog-header">
                                <h1 class="blog-title">' . htmlspecialchars($blog['title']) . '</h1>
                                <div class="blog-meta">
                                    <span><i class="fas fa-user"></i> <strong>Gökhan Aydınlı</strong></span>
                                    <span><i class="fas fa-calendar"></i> ' . $formatted_date . '</span>
                                    <span><i class="fas fa-clock"></i> ' . $blog['reading_time'] . ' dk okuma</span>
                                </div>
                            </div>
                            
                            <img src="' . htmlspecialchars($blog['featured_image']) . '" 
                                 alt="' . htmlspecialchars($blog['title']) . '" 
                                 class="blog-featured-image">
                            
                            <div class="blog-content">
                                ' . $blog['content'] . '
                            </div>
                            
                            <div class="blog-footer-info">
                                <p><strong>📄 Dosya:</strong> ' . htmlspecialchars($filename) . ' | <strong>🆔 Blog ID:</strong> ' . $blog['id'] . ' | <strong>🤖 Otomatik oluşturuldu</strong></p>
                                <p><strong>Gökhan Aydınlı Gayrimenkul</strong> - Uzman gayrimenkul danışmanlığı</p>
                            </div>
                        </article>
                    </div>
                </div>
            </div>
        </div>

        <!-- Footer -->
        <div class="footer-four position-relative z-1">
            <div class="container container-large">
                <div class="bg-wrapper position-relative z-1">
                    <div class="row">
                        <div class="col-xxl-3 col-lg-4 mb-60">
                            <div class="footer-intro">
                                <div class="logo mb-20">
                                    <a href="../index.php">
                                        <img src="../images/logoSiyah.png" alt="Gökhan Aydınlı">
                                    </a>
                                </div>
                                <p class="mb-30 xs-mb-20">Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
                                <a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
                                <ul class="style-none d-flex align-items-center social-icon">
                                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-lg-3 col-sm-4 ms-auto mb-30">
                            <div class="footer-nav ps-xl-5">
                                <h5 class="footer-title">Linkler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="../index.php">Ana Sayfa</a></li>
                                    <li><a href="../hakkimizda.php">Hakkımızda</a></li>
                                    <li><a href="../porfoy.html">Portföy</a></li>
                                    <li><a href="../contact.php">İletişim</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xxl-2 col-lg-3 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Hizmetler</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="../porfoy.html">Satılık</a></li>
                                    <li><a href="../porfoy.html">Kiralık</a></li>
                                    <li><a href="../dashboard/add-property.php">İlan Ver</a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="col-xl-2 col-lg-2 col-sm-4 mb-30">
                            <div class="footer-nav">
                                <h5 class="footer-title">Destek</h5>
                                <ul class="footer-nav-link style-none">
                                    <li><a href="../contact.php">İletişim</a></li>
                                    <li><a href="../dashboard/">Panel</a></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            
            <div class="container">
                <div class="bottom-footer">
                    <div class="d-lg-flex justify-content-between align-items-center">
                        <ul class="order-lg-1 pb-15 d-flex justify-content-center footer-nav-link style-none">
                            <li><a href="#">Gizlilik Politikası</a></li>
                            <li><a href="#">Kullanım Şartları</a></li>
                        </ul>
                        <p class="copyright text-center order-lg-0 pb-15">Copyright @' . date('Y') . ' Gökhan Aydınlı Gayrimenkul</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../js/jquery.min.js"></script>
    <script src="../js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
</body>
</html>';

    return $template;
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>🤖 Otomatik Blog Oluşturucu - Admin Panel</title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Dashboard CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    
    <style>
        .ai-generator-wrapper {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border-radius: 20px;
            padding: 40px;
            margin-bottom: 30px;
            color: white;
            text-align: center;
        }
        
        .ai-title {
            font-size: 2.5rem;
            font-weight: 700;
            margin-bottom: 15px;
        }
        
        .ai-subtitle {
            font-size: 1.1rem;
            opacity: 0.9;
            margin-bottom: 0;
        }
        
        .form-section {
            background: #fff;
            border-radius: 15px;
            padding: 30px;
            margin-bottom: 30px;
            box-shadow: 0 5px 20px rgba(0,0,0,0.08);
        }
        
        .keyword-input {
            background: #f8f9fa;
            border: 2px dashed #dee2e6;
            border-radius: 10px;
            padding: 15px;
            margin-bottom: 10px;
        }
        
        .preview-section {
            background: #f8f9fa;
            border-radius: 15px;
            padding: 25px;
            margin-top: 20px;
        }
        
        .generate-btn {
            background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);
            border: none;
            border-radius: 15px;
            padding: 15px 40px;
            font-size: 18px;
            font-weight: 600;
            color: white;
            transition: all 0.3s ease;
        }
        
        .generate-btn:hover {
            transform: translateY(-2px);
            box-shadow: 0 10px 25px rgba(102, 126, 234, 0.3);
        }
        
        .step-number {
            background: #667eea;
            color: white;
            width: 30px;
            height: 30px;
            border-radius: 50%;
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-weight: bold;
            margin-right: 10px;
        }
        
        .feature-badge {
            background: #e7f3ff;
            color: #0066cc;
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 12px;
            display: inline-block;
            margin: 5px;
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar.php'; ?>

        <!-- Dashboard Body -->
        <div class="dashboard-body">
            <div class="position-relative">
                <!-- Include Header -->
                <?php include 'includes/header.php'; ?>

                <!-- AI Generator Header -->
                <div class="ai-generator-wrapper">
                    <h1 class="ai-title">🤖 Otomatik Blog Oluşturucu</h1>
                    <p class="ai-subtitle">
                        Sadece başlık ve anahtar kelimeler girin, yapay zeka sizin için profesyonel blog yazısı oluştursun!
                    </p>
                    
                    <div class="mt-4">
                        <span class="feature-badge">✨ Otomatik İçerik</span>
                        <span class="feature-badge">🖼️ Resim Seçimi</span>
                        <span class="feature-badge">🎯 SEO Optimize</span>
                        <span class="feature-badge">⚡ Hızlı Oluşturma</span>
                        <?php if ($ai->isApiActive()): ?>
                            <span class="feature-badge" style="background: #d4edda; color: #155724;">🟢 GERÇEK AI AKTİF</span>
                        <?php else: ?>
                            <span class="feature-badge" style="background: #fff3cd; color: #856404;">🟡 DEMO MOD</span>
                        <?php endif; ?>
                    </div>
                </div>

                <?php if (!$ai->isApiActive()): ?>
                    <div class="alert alert-warning">
                        <h5><i class="fas fa-exclamation-triangle"></i> Demo Modda Çalışıyor</h5>
                        <p><strong>Gerçek AI kullanmak için:</strong></p>
                        <ol>
                            <li><a href="https://ai.google.dev/" target="_blank">ai.google.dev</a> adresinden ücretsiz API key alın</li>
                            <li><code>dashboard/includes/gemini-ai.php</code> dosyasında <code>demo_key_will_use_fallback</code> yerine gerçek key'inizi yazın</li>
                            <li>Günde 60 istek ücretsiz, sonrasında çok uygun ücretler</li>
                        </ol>
                        <small><strong>Şu anda:</strong> Kaliteli şablon tabanlı içerik üretiliyor, gerçek AI olmasa da oldukça iyi! 😊</small>
                    </div>
                <?php endif; ?>

                <?php if (isset($success_message)): ?>
                    <div class="alert alert-success">
                        <i class="fas fa-robot"></i> <?= $success_message ?>
                    </div>
                <?php endif; ?>

                <?php if (isset($error_message)): ?>
                    <div class="alert alert-danger">
                        <i class="fas fa-exclamation-circle"></i> <?= $error_message ?>
                    </div>
                <?php endif; ?>

                <!-- DEBUG PANEL -->
                <?php if ($_SERVER['REQUEST_METHOD'] == 'POST' || isset($_SESSION['debug_info'])): ?>
                <div class="alert alert-info">
                    <h5>🔍 Debug Bilgileri:</h5>
                    <ul>
                        <li><strong>Form gönderildi:</strong> ✅</li>
                        <li><strong>Başlık:</strong> <?= htmlspecialchars($_POST['auto_title'] ?? 'YOK') ?></li>
                        <li><strong>Anahtar kelimeler:</strong> <?= htmlspecialchars($_POST['auto_keywords'] ?? 'YOK') ?></li>
                        <li><strong>AI Aktif:</strong> <?= $ai->isApiActive() ? '🟢 Evet' : '🟡 Demo Mod' ?></li>
                        <li><strong>Veritabanı:</strong> <?= $conn ? '🟢 Bağlı' : '❌ Bağlı Değil' ?></li>
                        <?php if (isset($success_message)): ?>
                            <li><strong>Sonuç:</strong> 🎉 BAŞARILI!</li>
                        <?php elseif (isset($error_message)): ?>
                            <li><strong>Sonuç:</strong> ❌ Hata var</li>
                            <li><strong>Hata mesajı:</strong> <?= htmlspecialchars($error_message) ?></li>
                        <?php else: ?>
                            <li><strong>Sonuç:</strong> ⏳ İşlem devam ediyor veya hata oluştu</li>
                        <?php endif; ?>
                    </ul>
                    
                    <!-- Real-time debug bilgileri -->
                    <?php if (isset($_SESSION['debug_info']) && !empty($_SESSION['debug_info'])): ?>
                    <h6 class="mt-3">⚡ Gerçek Zamanlı Debug:</h6>
                    <div style="background: #f8f9fa; padding: 15px; border-radius: 5px; font-family: monospace; font-size: 13px; max-height: 300px; overflow-y: auto;">
                        <?php foreach ($_SESSION['debug_info'] as $step): ?>
                            <div style="margin-bottom: 5px; padding: 3px; border-left: 3px solid #007bff;">
                                <?= htmlspecialchars($step) ?>
                            </div>
                        <?php endforeach; ?>
                    </div>
                    <?php 
                    // Debug gösterildikten sonra temizle
                    unset($_SESSION['debug_info']);
                    endif; ?>
                    
                    <!-- Log dosyası debug -->
                    <?php 
                    $log_file = __DIR__ . '/error_log';
                    if (file_exists($log_file) && is_readable($log_file)): 
                        $log_content = file_get_contents($log_file);
                        $log_lines = array_filter(explode("\n", $log_content));
                        $recent_logs = array_slice($log_lines, -10); // Son 10 satır
                    ?>
                    <h6 class="mt-3">📋 Son Loglar:</h6>
                    <div style="background: #f8f9fa; padding: 10px; border-radius: 5px; font-family: monospace; font-size: 12px; max-height: 200px; overflow-y: auto;">
                        <?php foreach (array_reverse($recent_logs) as $log_line): ?>
                            <?php if (trim($log_line) && strpos($log_line, 'AUTO BLOG') !== false): ?>
                                <div><?= htmlspecialchars($log_line) ?></div>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </div>
                    <?php else: ?>
                    <small class="text-muted mt-3">Log dosyası bulunamadı: <?= $log_file ?></small>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <form method="POST">
                    <!-- Hidden input for generate_blog parameter -->
                    <input type="hidden" name="generate_blog" value="1">
                    
                    <!-- Adım 1: Başlık -->
                    <div class="form-section">
                        <h3 class="text-primary mb-4">
                            <span class="step-number">1</span>
                            Blog Başlığını Girin
                        </h3>
                        
                        <div class="mb-3">
                            <label class="form-label">📝 Blog Başlığı *</label>
                            <input type="text" name="auto_title" class="form-control form-control-lg" required 
                                   placeholder="Örn: İstanbul'da Gayrimenkul Yatırımı 2024 Rehberi"
                                   value="<?= isset($success_generated) ? '' : htmlspecialchars($_POST['auto_title'] ?? '') ?>">
                            <small class="text-muted">AI bu başlığa göre uygun içerik oluşturacak</small>
                        </div>
                    </div>

                    <!-- Adım 2: Anahtar Kelimeler -->
                    <div class="form-section">
                        <h3 class="text-primary mb-4">
                            <span class="step-number">2</span>
                            Anahtar Kelimeleri Belirleyin
                        </h3>
                        
                        <div class="keyword-input">
                            <label class="form-label">🏷️ Anahtar Kelimeler (virgülle ayırın) *</label>
                            <input type="text" name="auto_keywords" class="form-control" required
                                   placeholder="İstanbul, yatırım, konut, fiyat, analiz, kiralama"
                                   value="<?= isset($success_generated) ? '' : htmlspecialchars($_POST['auto_keywords'] ?? '') ?>">
                            <small class="text-muted">
                                <i class="fas fa-lightbulb"></i> 
                                <strong>Örnek kelimeler:</strong> İstanbul, Ankara, yatırım, konut, villa, dükkan, ofis, fiyat, kiralama, satış, analiz, piyasa, trend
                            </small>
                        </div>
                    </div>

                    <!-- Adım 3: Ayarlar -->
                    <div class="form-section">
                        <h3 class="text-primary mb-4">
                            <span class="step-number">3</span>
                            Blog Ayarları
                        </h3>
                        
                        <div class="row">
                            <div class="col-md-4">
                                <label class="form-label">🎭 Yazım Tonu</label>
                                <select name="tone" class="form-control">
                                    <option value="professional">👔 Profesyonel</option>
                                    <option value="friendly">😊 Samimi</option>
                                    <option value="authoritative">🎓 Uzman</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">📏 İçerik Uzunluğu</label>
                                <select name="length" class="form-control">
                                    <option value="short">🏃 Kısa (300-500 kelime)</option>
                                    <option value="medium" selected>🚶 Orta (500-800 kelime)</option>
                                    <option value="long">🚀 Uzun (800+ kelime)</option>
                                </select>
                            </div>
                            
                            <div class="col-md-4">
                                <label class="form-label">📋 Durum</label>
                                <select name="auto_status" class="form-control">
                                    <option value="draft">📝 Taslak</option>
                                    <option value="published">🚀 Hemen Yayınla</option>
                                </select>
                            </div>
                        </div>
                    </div>

                    <!-- Oluştur Butonu -->
                    <div class="form-section text-center">
                        <button type="submit" name="generate_blog" class="generate-btn">
                            <i class="fas fa-robot me-2"></i>
                            🤖 Blog Yazısını Otomatik Oluştur
                        </button>
                        
                        <div class="mt-3">
                            <small class="text-muted">
                                <i class="fas fa-clock"></i> 
                                Ortalama oluşturma süresi: 5-10 saniye
                            </small>
                        </div>
                    </div>

                    <!-- Nasıl Çalışır -->
                    <div class="preview-section">
                        <h4 class="text-primary mb-3">🔍 Nasıl Çalışır?</h4>
                        <div class="row">
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-brain fa-3x text-primary mb-2"></i>
                                <h6>AI Analiz</h6>
                                <small>Başlık ve anahtar kelimeler analiz edilir</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-edit fa-3x text-success mb-2"></i>
                                <h6>İçerik Oluşturma</h6>
                                <small>Profesyonel blog içeriği yazılır</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-image fa-3x text-warning mb-2"></i>
                                <h6>Resim Seçimi</h6>
                                <small>Uygun görseller otomatik seçilir</small>
                            </div>
                            <div class="col-md-3 text-center mb-3">
                                <i class="fas fa-rocket fa-3x text-danger mb-2"></i>
                                <h6>Yayına Al</h6>
                                <small>Blog sayfası otomatik oluşturulur</small>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    
    <script>
        // Form gönderildiğinde loading göster
        document.querySelector('form').addEventListener('submit', function(e) {
            const submitBtn = document.querySelector('.generate-btn');
            const form = this;
            
            // generate_blog parametresini garantilemek için hidden input ekle
            if (!form.querySelector('input[name="generate_blog"]')) {
                const hiddenInput = document.createElement('input');
                hiddenInput.type = 'hidden';
                hiddenInput.name = 'generate_blog';
                hiddenInput.value = '1';
                form.appendChild(hiddenInput);
            }
            
            submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>🤖 Blog Oluşturuluyor...';
            submitBtn.disabled = true;
            
            // Form gönderilince scroll pozisyonunu kaydet
            sessionStorage.setItem('scrollPosition', window.pageYOffset);
        });

        // Sayfa yüklendiğinde scroll pozisyonunu geri yükle
        window.addEventListener('load', function() {
            const savedPosition = sessionStorage.getItem('scrollPosition');
            if (savedPosition) {
                window.scrollTo(0, parseInt(savedPosition));
                sessionStorage.removeItem('scrollPosition');
            }
        });
        
        // Başarılı işlem sonrası success mesajına scroll et
        <?php if (isset($success_message)): ?>
        document.addEventListener('DOMContentLoaded', function() {
            const alertElement = document.querySelector('.alert-success');
            if (alertElement) {
                alertElement.scrollIntoView({ 
                    behavior: 'smooth', 
                    block: 'center' 
                });
                
                // 3 saniye sonra formu temizle
                setTimeout(() => {
                    document.querySelector('form').reset();
                }, 3000);
            }
        });
        <?php endif; ?>

        // Anahtar kelime önerileri
        const keywordSuggestions = {
            'gayrimenkul': ['İstanbul', 'yatırım', 'konut', 'fiyat', 'analiz'],
            'satın': ['ev', 'daire', 'villa', 'kredi', 'tapu', 'hukuk'],
            'kiralama': ['daire', 'ofis', 'dükkan', 'sözleşme', 'depozito'],
            'yatırım': ['getiri', 'risk', 'lokasyon', 'piyasa', 'trend'],
            'fiyat': ['analiz', 'değerleme', 'artış', 'düşüş', 'piyasa']
        };

        // Başlık değiştiğinde anahtar kelime önerisi yap
        document.querySelector('input[name="auto_title"]').addEventListener('blur', function() {
            const title = this.value.toLowerCase();
            const keywordInput = document.querySelector('input[name="auto_keywords"]');
            
            if (keywordInput.value.trim() === '') {
                let suggestions = [];
                
                Object.keys(keywordSuggestions).forEach(key => {
                    if (title.includes(key)) {
                        suggestions = suggestions.concat(keywordSuggestions[key]);
                    }
                });
                
                if (suggestions.length > 0) {
                    keywordInput.value = suggestions.slice(0, 5).join(', ');
                    keywordInput.style.background = '#e7f3ff';
                    
                    setTimeout(() => {
                        keywordInput.style.background = '#f8f9fa';
                    }, 2000);
                }
            }
        });

        console.log('🤖 Otomatik Blog Oluşturucu hazır!');
    </script>
</body>
</html>
