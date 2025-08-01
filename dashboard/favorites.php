<?php
// Hata raporlamasını aç
error_reporting(E_ALL);
ini_set('display_errors', 1);

session_start();
include __DIR__ . '/../db.php';

// Kullanıcı giriş kontrolü
if (!isset($_SESSION['user_id'])) {
    header("Location: ../index.php");
    exit;
}

$user_id = $_SESSION['user_id'];

// User bilgilerini al
$user_query = $conn->prepare("SELECT name, email, role, created_at FROM users WHERE id = ?");
$user_query->bind_param("i", $user_id);
$user_query->execute();
$user_data = $user_query->get_result()->fetch_assoc();

if (!$user_data) {
    session_destroy();
    header("Location: ../index.php");
    exit;
}

// Admin kullanıcıları admin dashboard'a yönlendir
if ($user_data['role'] == 'admin') {
    header("Location: dashboard-admin.php");
    exit;
}

// Sayfa ayarları
$current_page = 'favorites';
$page_title = $user_data['name'] . ' - Favorilerim';
$user_name = $user_data['name']; // Sidebar için

// Dinamik base URL hesapla (sunucu.dev için)
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'];
$request_uri = $_SERVER['REQUEST_URI'];
$current_dir = dirname($request_uri);
$base_url = $protocol . $host . dirname($current_dir);

// Favori ilanları al (JOIN ile property bilgileri de)
$favorites_query = $conn->prepare("
    SELECT f.*, p.title, p.price, p.type, p.area, p.city as location, p.images, p.main_image 
    FROM favorites f 
    LEFT JOIN properties p ON f.property_id = p.id 
    WHERE f.user_id = ? 
    ORDER BY f.created_at DESC
");
$favorites_query->bind_param("i", $user_id);
$favorites_query->execute();
$favorites = $favorites_query->get_result();

// Favori sayısı
$favorites_count = $favorites->num_rows;
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?> - Gökhan Aydınlı Real Estate</title>
    
    <!-- CSS Files -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    
    <!-- Dashboard CSS -->
    <link rel="stylesheet" href="../assets/dashboard-style.css">
    <link rel="stylesheet" href="includes/dashboard-common.css">
    
    <style>
        /* Dashboard User Specific Styles */
        .dashboard-body {
            margin-left: 280px;
            min-height: 100vh;
            background: #f8f9fa;
            transition: margin-left 0.3s ease;
        }
        
        .main-content {
            padding: 30px;
        }
        
        .mobile-header {
            display: none;
            background: white;
            padding: 15px 20px;
            box-shadow: 0 2px 10px rgba(0,0,0,0.1);
            align-items: center;
            justify-content: space-between;
        }
        
        .mobile-menu-btn {
            background: none;
            border: none;
            font-size: 20px;
            color: #0d6efd;
        }
        
        .mobile-logout {
            color: #dc3545;
            text-decoration: none;
        }
        
        .welcome-banner {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.15);
        }
        
        .welcome-title {
            font-size: 28px;
            font-weight: 700;
            margin-bottom: 10px;
        }
        
        .welcome-subtitle {
            font-size: 16px;
            opacity: 0.9;
            margin: 0;
        }
        
        /* Favorites Header - same style as welcome banner */
        .favorites-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.15);
        }
        
        .filter-section {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            margin-bottom: 30px;
        }
        
        .favorite-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .favorite-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        }
        
        .favorite-image {
            position: relative;
            height: 200px;
            overflow: hidden;
        }
        
        .favorite-image img {
            width: 100%;
            height: 100%;
            object-fit: cover;
        }
        
        .property-placeholder {
            width: 100%;
            height: 100%;
            background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
            display: flex;
            align-items: center;
            justify-content: center;
            border: 2px dashed #dee2e6;
        }
        
        .placeholder-content {
            text-align: center;
            color: #6c757d;
        }
        
        .placeholder-content i {
            font-size: 48px;
            margin-bottom: 10px;
            opacity: 0.5;
        }
        
        .placeholder-content span {
            display: block;
            font-size: 14px;
            font-weight: 500;
        }
        
        .favorite-badge {
            position: absolute;
            top: 15px;
            left: 15px;
            background: rgba(255, 255, 255, 0.9);
            padding: 8px 12px;
            border-radius: 20px;
            font-size: 14px;
        }
        
        .remove-favorite {
            position: absolute;
            top: 15px;
            right: 15px;
            background: rgba(220, 53, 69, 0.9);
            color: white;
            width: 35px;
            height: 35px;
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: all 0.3s ease;
        }
        
        .remove-favorite:hover {
            background: #dc3545;
            transform: scale(1.1);
        }
        
        .favorite-content {
            padding: 20px;
        }
        
        .favorite-title {
            font-size: 18px;
            font-weight: 600;
            margin-bottom: 15px;
            color: #0d1a1c;
        }
        
        .favorite-details {
            margin-bottom: 15px;
        }
        
        .detail-item {
            display: flex;
            align-items: center;
            gap: 8px;
            margin-bottom: 8px;
            font-size: 14px;
            color: #6c757d;
        }
        
        .detail-item i {
            color: #0d6efd;
            width: 16px;
        }
        
        .favorite-price {
            font-size: 20px;
            font-weight: 700;
            color: #28a745;
            margin-bottom: 15px;
        }
        
        .favorite-actions {
            display: flex;
            gap: 10px;
            margin-bottom: 15px;
        }
        
        .favorite-date {
            font-size: 12px;
            color: #6c757d;
            border-top: 1px solid #f8f9fa;
            padding-top: 15px;
        }
        
        .empty-favorites {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }
        
        .empty-favorites i {
            font-size: 64px;
            color: #dc3545;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-favorites h4 {
            color: #495057;
            margin-bottom: 15px;
        }
        
        .empty-favorites p {
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        /* Mobile Responsive */
        @media (max-width: 768px) {
            .dashboard-body {
                margin-left: 0;
            }
            
            .mobile-header {
                display: flex !important;
            }
            
            .main-content {
                padding: 20px;
            }
            
            .favorites-header {
                padding: 25px 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
        }
    </style>
</head>

<body>
    <div class="main-page-wrapper">
        <!-- Include Sidebar -->
        <?php include 'includes/sidebar-user.php'; ?>
        
        <!-- Mobile Overlay -->
        <div class="mobile-overlay"></div>

        <!-- Ana İçerik -->
        <div class="dashboard-body">
            <!-- Mobil Header -->
            <div class="mobile-header d-block d-md-none">
                <button class="mobile-menu-btn" onclick="toggleSidebar()">
                    <i class="fas fa-bars"></i>
                </button>
                <h5 class="mobile-title">Favorilerim</h5>
                <a href="../logout.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>

            <!-- Ana İçerik Alanı -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Favorites Header -->
                    <div class="favorites-header">
                        <div class="favorites-content">
                            <h2 class="welcome-title">
                                <i class="fas fa-heart me-2"></i>
                                Favorilerim
                            </h2>
                            <p class="welcome-subtitle">
                                Beğendiğiniz ilanları buradan takip edebilirsiniz. 
                                Favori listenizdeki <?php echo $favorites_count; ?> ilan arasından seçim yapabilirsiniz.
                            </p>
                        </div>
                    </div>

            <!-- Filtreler -->
            <div class="filter-section mb-4">
                <div class="row">
                    <div class="col-md-3">
                        <select class="form-select" id="typeFilter">
                            <option value="">Tüm Tipler</option>
                            <option value="ofis">Ofis</option>
                            <option value="dukkan">Dükkan</option>
                            <option value="depo">Depo</option>
                            <option value="fabrika">Fabrika</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="locationFilter">
                            <option value="">Tüm Lokasyonlar</option>
                            <option value="istanbul">İstanbul</option>
                            <option value="ankara">Ankara</option>
                            <option value="izmir">İzmir</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <select class="form-select" id="sortFilter">
                            <option value="newest">En Yeni</option>
                            <option value="oldest">En Eski</option>
                            <option value="price_asc">Fiyat (Düşük-Yüksek)</option>
                            <option value="price_desc">Fiyat (Yüksek-Düşük)</option>
                        </select>
                    </div>
                    <div class="col-md-3">
                        <button class="btn btn-outline-primary w-100" onclick="clearFilters()">
                            <i class="fas fa-filter-circle-xmark"></i> Filtreleri Temizle
                        </button>
                    </div>
                </div>
            </div>

            <!-- Favori İlanlar -->
            <div class="favorites-grid">
                <?php if ($favorites_count > 0): ?>
                    <div class="row">
                        <?php while ($favorite = $favorites->fetch_assoc()): ?>
                            <div class="col-lg-6 col-xl-4 mb-4" data-type="<?php echo htmlspecialchars($favorite['type']); ?>" data-location="<?php echo htmlspecialchars($favorite['location']); ?>">
                                <div class="favorite-card">
                                    <div class="favorite-image">
                                        <?php 
                                        // Resim URL'ini belirle
                                        $image_url = '../assets/images/no-image.svg'; // Varsayılan resim
                                        $has_real_image = false;
                                        
                                        if (!empty($favorite['main_image'])) {
                                            // Önce dashboard içindeki uploads'ı kontrol et
                                            $dashboard_image_path = 'uploads/properties/' . $favorite['main_image'];
                                            $parent_image_path = '../uploads/properties/' . $favorite['main_image'];
                                            
                                            if (file_exists(__DIR__ . '/' . $dashboard_image_path)) {
                                                $image_url = $dashboard_image_path;
                                                $has_real_image = true;
                                            } elseif (file_exists(__DIR__ . '/' . $parent_image_path)) {
                                                $image_url = $parent_image_path;
                                                $has_real_image = true;
                                            }
                                        } elseif (!empty($favorite['images'])) {
                                            $images = json_decode($favorite['images'], true);
                                            if (is_array($images) && !empty($images)) {
                                                // İlk resmi dene
                                                foreach ($images as $image) {
                                                    $dashboard_image_path = 'uploads/properties/' . $image;
                                                    $parent_image_path = '../uploads/properties/' . $image;
                                                    
                                                    if (file_exists(__DIR__ . '/' . $dashboard_image_path)) {
                                                        $image_url = $dashboard_image_path;
                                                        $has_real_image = true;
                                                        break;
                                                    } elseif (file_exists(__DIR__ . '/' . $parent_image_path)) {
                                                        $image_url = $parent_image_path;
                                                        $has_real_image = true;
                                                        break;
                                                    }
                                                }
                                            }
                                        }
                                        ?>
                                        
                                        <?php if ($has_real_image): ?>
                                            <img src="<?php echo htmlspecialchars($image_url); ?>" alt="<?php echo htmlspecialchars($favorite['title']); ?>" loading="lazy" onerror="this.src='../assets/images/no-image.svg'">
                                        <?php else: ?>
                                            <div class="property-placeholder">
                                                <div class="placeholder-content">
                                                    <i class="fas fa-home"></i>
                                                    <span>Resim Yok</span>
                                                </div>
                                            </div>
                                        <?php endif; ?>
                                        <div class="favorite-badge">
                                            <i class="fas fa-heart text-danger"></i>
                                        </div>
                                        <div class="remove-favorite" onclick="removeFavorite(<?php echo $favorite['property_id']; ?>)">
                                            <i class="fas fa-times"></i>
                                        </div>
                                    </div>
                                    <div class="favorite-content">
                                        <h5 class="favorite-title"><?php echo htmlspecialchars($favorite['title']); ?></h5>
                                        <div class="favorite-details">
                                            <div class="detail-item">
                                                <i class="fas fa-map-marker-alt"></i>
                                                <span><?php echo htmlspecialchars($favorite['location']); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-building"></i>
                                                <span><?php echo ucfirst(htmlspecialchars($favorite['type'])); ?></span>
                                            </div>
                                            <div class="detail-item">
                                                <i class="fas fa-expand-arrows-alt"></i>
                                                <span><?php echo htmlspecialchars($favorite['area']); ?> m²</span>
                                            </div>
                                        </div>
                                        <div class="favorite-price">
                                            <strong><?php echo number_format($favorite['price'], 0, ',', '.'); ?> ₺</strong>
                                        </div>
                                        <div class="favorite-actions">
                                            <a href="<?php echo $base_url; ?>/property-details.php?id=<?php echo $favorite['property_id']; ?>" class="btn btn-primary btn-sm">
                                                <i class="fas fa-eye"></i> Detay
                                            </a>
                                            <button class="btn btn-outline-primary btn-sm" onclick="shareProperty(<?php echo $favorite['property_id']; ?>)">
                                                <i class="fas fa-share"></i> Paylaş
                                            </button>
                                        </div>
                                        <div class="favorite-date">
                                            <small class="text-muted">
                                                <i class="fas fa-calendar"></i>
                                                Favorilere eklendi: <?php echo date('d.m.Y', strtotime($favorite['created_at'])); ?>
                                            </small>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        <?php endwhile; ?>
                    </div>
                <?php else: ?>
                    <!-- Boş Durum -->
                    <div class="empty-state">
                        <div class="empty-icon">
                            <i class="fas fa-heart-broken"></i>
                        </div>
                        <h3>Henüz Favori İlan Yok</h3>
                        <p>Beğendiğiniz ilanları favorilere ekleyerek burada görüntüleyebilirsiniz.</p>
                        <a href="../" class="btn btn-primary">
                            <i class="fas fa-search"></i> İlan Ara
                        </a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dashboard-script.js"></script>
    
    <script>
    // Favori silme fonksiyonu
    function removeFavorite(propertyId) {
        if (confirm('Bu ilanı favorilerden kaldırmak istediğinizden emin misiniz?')) {
            fetch('../api/remove-favorite.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({
                    property_id: propertyId
                })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    location.reload(); // Sayfayı yenile
                } else {
                    alert('Hata: ' + data.message);
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
        }
    }

    // Paylaş fonksiyonu
    function shareProperty(propertyId) {
        // PHP'den base URL'i al
        const baseUrl = '<?php echo $base_url; ?>';
        const url = `${baseUrl}/property-details.php?id=${propertyId}`;
        
        if (navigator.share) {
            navigator.share({
                title: 'Gayrimenkul İlanı',
                text: 'Bu ilana göz atın!',
                url: url
            });
        } else {
            // Fallback - kopyala
            navigator.clipboard.writeText(url).then(() => {
                alert('İlan linki kopyalandı!');
            });
        }
    }

    // Filtreler
    function clearFilters() {
        document.getElementById('typeFilter').value = '';
        document.getElementById('locationFilter').value = '';
        document.getElementById('sortFilter').value = 'newest';
        filterFavorites();
    }

    function filterFavorites() {
        const typeFilter = document.getElementById('typeFilter').value;
        const locationFilter = document.getElementById('locationFilter').value;
        const sortFilter = document.getElementById('sortFilter').value;
        
        const cards = document.querySelectorAll('.favorites-grid .col-lg-6');
        
        cards.forEach(card => {
            const type = card.dataset.type;
            const location = card.dataset.location;
            
            let show = true;
            
            if (typeFilter && type !== typeFilter) show = false;
            if (locationFilter && !location.toLowerCase().includes(locationFilter.toLowerCase())) show = false;
            
            card.style.display = show ? 'block' : 'none';
        });
    }

    // Event listeners
    document.getElementById('typeFilter').addEventListener('change', filterFavorites);
    document.getElementById('locationFilter').addEventListener('change', filterFavorites);
    document.getElementById('sortFilter').addEventListener('change', filterFavorites);
    </script>

    <style>
    .favorite-card {
        background: white;
        border-radius: 15px;
        box-shadow: 0 5px 15px rgba(0,0,0,0.1);
        overflow: hidden;
        transition: transform 0.3s ease, box-shadow 0.3s ease;
    }

    .favorite-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 10px 25px rgba(0,0,0,0.15);
    }

    .favorite-image {
        position: relative;
        height: 200px;
        overflow: hidden;
    }

    .favorite-image img {
        width: 100%;
        height: 100%;
        object-fit: cover;
        transition: transform 0.3s ease;
    }

    .favorite-card:hover .favorite-image img {
        transform: scale(1.1);
    }

    .favorite-badge {
        position: absolute;
        top: 15px;
        left: 15px;
        background: rgba(255,255,255,0.9);
        padding: 8px;
        border-radius: 50%;
        backdrop-filter: blur(10px);
    }

    .remove-favorite {
        position: absolute;
        top: 15px;
        right: 15px;
        background: rgba(255,255,255,0.9);
        width: 35px;
        height: 35px;
        border-radius: 50%;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
        transition: all 0.3s ease;
        backdrop-filter: blur(10px);
    }

    .remove-favorite:hover {
        background: #dc3545;
        color: white;
    }

    .favorite-content {
        padding: 20px;
    }

    .favorite-title {
        font-size: 18px;
        font-weight: 600;
        margin-bottom: 15px;
        color: #2c3e50;
    }

    .favorite-details {
        margin-bottom: 15px;
    }

    .detail-item {
        display: flex;
        align-items: center;
        margin-bottom: 8px;
        color: #6c757d;
        font-size: 14px;
    }

    .detail-item i {
        width: 16px;
        margin-right: 8px;
        color: #0d6efd;
    }

    .favorite-price {
        font-size: 20px;
        color: #0d6efd;
        margin-bottom: 15px;
    }

    .favorite-actions {
        display: flex;
        gap: 10px;
        margin-bottom: 15px;
    }

    .favorite-date {
        border-top: 1px solid #eee;
        padding-top: 15px;
    }

    .empty-state {
        text-align: center;
        padding: 60px 20px;
        color: #6c757d;
    }

    .empty-icon {
        font-size: 80px;
        color: #dee2e6;
        margin-bottom: 20px;
    }

    .filter-section {
        background: white;
        padding: 20px;
        border-radius: 15px;
        box-shadow: 0 3px 10px rgba(0,0,0,0.1);
    }

    @media (max-width: 768px) {
        .favorite-actions {
            flex-direction: column;
        }
        
        .filter-section .row > div {
            margin-bottom: 10px;
        }
    }
    </style>
</body>
</html>