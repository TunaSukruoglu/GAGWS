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
$current_page = 'reviews';
$page_title = $user_data['name'] . ' - Değerlendirmelerim';
$user_name = $user_data['name']; // Sidebar için

// Değerlendirmeleri getir (tablo yoksa önce oluştur)
try {
    // Tablo kontrolü ve oluşturma
    $table_check = $conn->query("SHOW TABLES LIKE 'reviews'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE reviews (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            property_id INT NULL,
            rating INT NOT NULL CHECK (rating BETWEEN 1 AND 5),
            title VARCHAR(255) NOT NULL,
            comment TEXT NOT NULL,
            is_approved BOOLEAN DEFAULT FALSE,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            INDEX(user_id),
            INDEX(property_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table);
    }

    // Değerlendirmeleri getir (property bilgileriyle birlikte)
    $reviews_query = "SELECT r.*, p.title as property_title, p.city as property_location 
                     FROM reviews r 
                     LEFT JOIN properties p ON r.property_id = p.id 
                     WHERE r.user_id = ? 
                     ORDER BY r.created_at DESC";
    $reviews_stmt = $conn->prepare($reviews_query);
    $reviews_stmt->bind_param("i", $user_id);
    $reviews_stmt->execute();
    $reviews = $reviews_stmt->get_result();
    $reviews_count = $reviews->num_rows;

    // İstatistikler
    $approved_count = 0;
    $pending_count = 0;
    $avg_rating = 0;
    
    if ($reviews_count > 0) {
        $stats_query = "SELECT 
                          COUNT(*) as total,
                          SUM(CASE WHEN is_approved = 1 THEN 1 ELSE 0 END) as approved,
                          SUM(CASE WHEN is_approved = 0 THEN 1 ELSE 0 END) as pending,
                          AVG(rating) as avg_rating
                        FROM reviews 
                        WHERE user_id = ?";
        $stats_stmt = $conn->prepare($stats_query);
        $stats_stmt->bind_param("i", $user_id);
        $stats_stmt->execute();
        $stats = $stats_stmt->get_result()->fetch_assoc();
        
        $approved_count = $stats['approved'];
        $pending_count = $stats['pending'];
        $avg_rating = round($stats['avg_rating'], 1);
    }

} catch (Exception $e) {
    $reviews_count = 0;
    $reviews = null;
    $approved_count = 0;
    $pending_count = 0;
    $avg_rating = 0;
}
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
        
        /* Reviews Header - same style as welcome banner */
        .reviews-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.15);
        }
        
        .stats-grid {
            display: grid;
            grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
            gap: 20px;
            margin-bottom: 30px;
        }
        
        .stats-card {
            background: white;
            padding: 25px;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            display: flex;
            align-items: center;
            gap: 20px;
        }
        
        .stats-icon {
            width: 50px;
            height: 50px;
            background: #f8f9fa;
            border-radius: 12px;
            display: flex;
            align-items: center;
            justify-content: center;
            font-size: 20px;
            color: #0d6efd;
        }
        
        .stats-number {
            font-size: 24px;
            font-weight: 700;
            color: #0d1a1c;
            margin-bottom: 5px;
        }
        
        .stats-label {
            font-size: 14px;
            color: #6c757d;
        }
        
        .review-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .review-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        }
        
        .review-card-header {
            padding: 20px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: flex-start;
        }
        
        .review-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin-bottom: 10px;
        }
        
        .review-property {
            font-size: 14px;
            color: #6c757d;
            margin-bottom: 10px;
        }
        
        .review-rating {
            display: flex;
            gap: 2px;
            margin-bottom: 10px;
        }
        
        .star {
            color: #ffc107;
            font-size: 16px;
        }
        
        .star.empty {
            color: #e9ecef;
        }
        
        .review-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
            padding: 5px 12px;
            border-radius: 15px;
        }
        
        .review-status.approved {
            background: #d4edda;
            color: #155724;
        }
        
        .review-status.pending {
            background: #fff3cd;
            color: #856404;
        }
        
        .review-card-body {
            padding: 20px;
        }
        
        .review-comment {
            color: #495057;
            line-height: 1.6;
            margin-bottom: 15px;
        }
        
        .review-actions {
            display: flex;
            gap: 10px;
            margin-top: 15px;
        }
        
        .review-date {
            font-size: 12px;
            color: #6c757d;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f8f9fa;
        }
        
        .empty-reviews {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }
        
        .empty-reviews i {
            font-size: 64px;
            color: #0d6efd;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-reviews h4 {
            color: #495057;
            margin-bottom: 15px;
        }
        
        .empty-reviews p {
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .write-review-section {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            padding: 25px;
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
            
            .reviews-header {
                padding: 25px 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
            
            .stats-grid {
                grid-template-columns: repeat(2, 1fr);
            }
            
            .review-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
        }
        
        @media (max-width: 480px) {
            .main-content {
                padding: 15px;
            }
            
            .stats-grid {
                grid-template-columns: 1fr;
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
                <h5 class="mobile-title">Değerlendirmelerim</h5>
                <a href="../logout.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>

            <!-- Ana İçerik Alanı -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Reviews Header -->
                    <div class="reviews-header">
                        <div class="reviews-content">
                            <h2 class="welcome-title">
                                <i class="fas fa-star me-2"></i>
                                Değerlendirmelerim
                            </h2>
                            <p class="welcome-subtitle">
                                Deneyimlerinizi paylaşın ve diğer kullanıcılara yardımcı olun. 
                                Toplam <?php echo $reviews_count; ?> değerlendirmeniz bulunuyor.
                            </p>
                        </div>
                    </div>

                    <!-- İstatistikler -->
                    <div class="stats-grid">
                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-star"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $reviews_count; ?></div>
                                <div class="stats-label">Toplam Değerlendirme</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-check-circle"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $approved_count; ?></div>
                                <div class="stats-label">Onaylanan</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-clock"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $pending_count; ?></div>
                                <div class="stats-label">Beklemede</div>
                            </div>
                        </div>

                        <div class="stats-card">
                            <div class="stats-icon">
                                <i class="fas fa-chart-line"></i>
                            </div>
                            <div class="stats-content">
                                <div class="stats-number"><?php echo $avg_rating; ?>/5</div>
                                <div class="stats-label">Ortalama Puanım</div>
                            </div>
                        </div>
                    </div>

                    <!-- Yeni Değerlendirme Yaz -->
                    <div class="write-review-section">
                        <h4 class="mb-3">
                            <i class="fas fa-edit me-2"></i>
                            Yeni Değerlendirme Yaz
                        </h4>
                        <p class="text-muted mb-3">
                            Deneyimlediğiniz hizmetler hakkında değerlendirme yazarak diğer kullanıcılara yardımcı olabilirsiniz.
                        </p>
                        <div class="row">
                            <div class="col-md-8">
                                <button class="btn btn-primary btn-lg" onclick="writeReview()">
                                    <i class="fas fa-pen me-2"></i>
                                    Değerlendirme Yaz
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Değerlendirmeler -->
                    <?php if ($reviews_count > 0): ?>
                        <div class="row">
                            <?php while ($review = $reviews->fetch_assoc()): ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="review-card">
                                        <div class="review-card-header">
                                            <div class="review-info">
                                                <h5 class="review-title"><?php echo htmlspecialchars($review['title']); ?></h5>
                                                
                                                <?php if ($review['property_title']): ?>
                                                    <div class="review-property">
                                                        <i class="fas fa-building me-1"></i>
                                                        <?php echo htmlspecialchars($review['property_title']); ?>
                                                        <?php if ($review['property_location']): ?>
                                                            - <?php echo htmlspecialchars($review['property_location']); ?>
                                                        <?php endif; ?>
                                                    </div>
                                                <?php endif; ?>
                                                
                                                <div class="review-rating">
                                                    <?php for ($i = 1; $i <= 5; $i++): ?>
                                                        <i class="fas fa-star <?php echo $i <= $review['rating'] ? '' : 'empty'; ?>"></i>
                                                    <?php endfor; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="review-status <?php echo $review['is_approved'] ? 'approved' : 'pending'; ?>">
                                                <i class="fas fa-<?php echo $review['is_approved'] ? 'check-circle' : 'clock'; ?>"></i>
                                                <?php echo $review['is_approved'] ? 'Onaylandı' : 'Beklemede'; ?>
                                            </div>
                                        </div>
                                        
                                        <div class="review-card-body">
                                            <div class="review-comment">
                                                <?php echo nl2br(htmlspecialchars($review['comment'])); ?>
                                            </div>
                                            
                                            <div class="review-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-edit"></i> Düzenle
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteReview(<?php echo $review['id']; ?>)">
                                                    <i class="fas fa-trash"></i> Sil
                                                </button>
                                            </div>
                                            
                                            <div class="review-date">
                                                <i class="fas fa-calendar me-1"></i>
                                                Yazıldı: <?php echo date('d.m.Y H:i', strtotime($review['created_at'])); ?>
                                                <?php if ($review['updated_at'] != $review['created_at']): ?>
                                                    <br>Güncellendi: <?php echo date('d.m.Y H:i', strtotime($review['updated_at'])); ?>
                                                <?php endif; ?>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-reviews">
                            <i class="fas fa-star"></i>
                            <h4>Henüz değerlendirmeniz yok</h4>
                            <p>Deneyimlerinizi paylaşarak diğer kullanıcılara yardımcı olun ve topluluk içinde güven oluşturun.</p>
                            <button class="btn btn-primary btn-lg" onclick="writeReview()">
                                <i class="fas fa-pen me-2"></i>
                                İlk Değerlendirmenizi Yazın
                            </button>
                        </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>

    <!-- Mobile Overlay -->
    <div class="mobile-overlay" id="mobileOverlay"></div>

    <!-- Scripts -->
    <script src="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/dashboard-script.js"></script>
    
    <script>
    function writeReview() {
        // Değerlendirme yazma modalını aç veya sayfaya yönlendir
        window.location.href = '../contact.php?write_review=1';
    }

    function editReview(reviewId) {
        // Değerlendirme düzenleme modalını aç
        fetch(`ajax/get-review.php?id=${reviewId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Düzenleme modalını açabilirsiniz
                    alert('Düzenleme özelliği yakında eklenecek!');
                } else {
                    alert('Değerlendirme bulunamadı!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
    }

    function deleteReview(reviewId) {
        if (confirm('Bu değerlendirmeyi silmek istediğinizden emin misiniz?')) {
            fetch('ajax/delete-review.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `review_id=${reviewId}`
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Sayfayı yenile
                    location.reload();
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

    // Yıldız puanlama sistemi
    function setRating(rating) {
        const stars = document.querySelectorAll('.rating-star');
        stars.forEach((star, index) => {
            if (index < rating) {
                star.classList.remove('empty');
            } else {
                star.classList.add('empty');
            }
        });
    }
    </script>
</body>
</html>
