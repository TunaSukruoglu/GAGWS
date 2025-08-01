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
$current_page = 'saved-searches';
$page_title = $user_data['name'] . ' - Kayıtlı Aramalarım';
$user_name = $user_data['name']; // Sidebar için

// Kayıtlı aramaları getir (tablo yoksa önce oluştur)
try {
    // Tablo kontrolü ve oluşturma
    $table_check = $conn->query("SHOW TABLES LIKE 'saved_searches'");
    if ($table_check->num_rows == 0) {
        $create_table = "CREATE TABLE saved_searches (
            id INT AUTO_INCREMENT PRIMARY KEY,
            user_id INT NOT NULL,
            search_name VARCHAR(255) NOT NULL,
            search_criteria JSON NOT NULL,
            created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            is_active BOOLEAN DEFAULT TRUE,
            notification_enabled BOOLEAN DEFAULT TRUE,
            INDEX(user_id),
            FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
        )";
        $conn->query($create_table);
    }

    // Kayıtlı aramaları getir
    $searches_query = "SELECT * FROM saved_searches WHERE user_id = ? AND is_active = 1 ORDER BY created_at DESC";
    $searches_stmt = $conn->prepare($searches_query);
    $searches_stmt->bind_param("i", $user_id);
    $searches_stmt->execute();
    $searches = $searches_stmt->get_result();
    $searches_count = $searches->num_rows;

} catch (Exception $e) {
    $searches_count = 0;
    $searches = null;
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
        
        /* Saved Searches Header - same style as welcome banner */
        .searches-header {
            background: linear-gradient(135deg, #0d6efd 0%, #0d1a1c 100%);
            color: white;
            padding: 40px 30px;
            border-radius: 20px;
            margin-bottom: 30px;
            box-shadow: 0 5px 25px rgba(13, 110, 253, 0.15);
        }
        
        .search-card {
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
            overflow: hidden;
            transition: all 0.3s ease;
            margin-bottom: 20px;
        }
        
        .search-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 10px 35px rgba(0,0,0,0.15);
        }
        
        .search-card-header {
            padding: 20px;
            border-bottom: 1px solid #f8f9fa;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }
        
        .search-title {
            font-size: 18px;
            font-weight: 600;
            color: #0d1a1c;
            margin: 0;
        }
        
        .search-actions {
            display: flex;
            gap: 10px;
        }
        
        .search-card-body {
            padding: 20px;
        }
        
        .search-criteria {
            margin-bottom: 15px;
        }
        
        .criteria-item {
            display: inline-block;
            background: #f8f9fa;
            padding: 5px 12px;
            border-radius: 15px;
            font-size: 13px;
            margin: 3px;
            color: #6c757d;
        }
        
        .criteria-item strong {
            color: #0d6efd;
        }
        
        .search-stats {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-top: 15px;
            padding-top: 15px;
            border-top: 1px solid #f8f9fa;
        }
        
        .search-date {
            font-size: 12px;
            color: #6c757d;
        }
        
        .notification-status {
            display: flex;
            align-items: center;
            gap: 5px;
            font-size: 13px;
        }
        
        .notification-status.active {
            color: #28a745;
        }
        
        .notification-status.inactive {
            color: #6c757d;
        }
        
        .empty-searches {
            text-align: center;
            padding: 60px 20px;
            background: white;
            border-radius: 15px;
            box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }
        
        .empty-searches i {
            font-size: 64px;
            color: #0d6efd;
            margin-bottom: 20px;
            opacity: 0.3;
        }
        
        .empty-searches h4 {
            color: #495057;
            margin-bottom: 15px;
        }
        
        .empty-searches p {
            color: #6c757d;
            margin-bottom: 30px;
        }
        
        .create-search-section {
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
            
            .searches-header {
                padding: 25px 20px;
            }
            
            .welcome-title {
                font-size: 22px;
            }
            
            .search-card-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 15px;
            }
            
            .search-actions {
                align-self: flex-end;
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
                <h5 class="mobile-title">Kayıtlı Aramalarım</h5>
                <a href="../logout.php" class="mobile-logout">
                    <i class="fas fa-sign-out-alt"></i>
                </a>
            </div>

            <!-- Ana İçerik Alanı -->
            <div class="main-content">
                <div class="container-fluid">
                    <!-- Searches Header -->
                    <div class="searches-header">
                        <div class="searches-content">
                            <h2 class="welcome-title">
                                <i class="fas fa-search me-2"></i>
                                Kayıtlı Aramalarım
                            </h2>
                            <p class="welcome-subtitle">
                                Arama kriterlerinizi kaydedin ve yeni ilanlar eklendiğinde bildirim alın. 
                                Toplam <?php echo $searches_count; ?> kayıtlı aramanız bulunuyor.
                            </p>
                        </div>
                    </div>

                    <!-- Yeni Arama Oluştur -->
                    <div class="create-search-section">
                        <h4 class="mb-3">
                            <i class="fas fa-plus-circle me-2"></i>
                            Yeni Arama Oluştur
                        </h4>
                        <p class="text-muted mb-3">
                            İhtiyaçlarınıza uygun arama kriterlerinizi belirleyin ve otomatik bildirim alın.
                        </p>
                        <div class="row">
                            <div class="col-md-8">
                                <button class="btn btn-primary btn-lg" onclick="createNewSearch()">
                                    <i class="fas fa-search me-2"></i>
                                    Arama Oluştur
                                </button>
                            </div>
                        </div>
                    </div>

                    <!-- Kayıtlı Aramalar -->
                    <?php if ($searches_count > 0): ?>
                        <div class="row">
                            <?php while ($search = $searches->fetch_assoc()): ?>
                                <?php 
                                $criteria = json_decode($search['search_criteria'], true);
                                ?>
                                <div class="col-lg-6 col-xl-4 mb-4">
                                    <div class="search-card">
                                        <div class="search-card-header">
                                            <h5 class="search-title"><?php echo htmlspecialchars($search['search_name']); ?></h5>
                                            <div class="search-actions">
                                                <button class="btn btn-sm btn-outline-primary" onclick="editSearch(<?php echo $search['id']; ?>)">
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-success" onclick="runSearch(<?php echo $search['id']; ?>)">
                                                    <i class="fas fa-play"></i>
                                                </button>
                                                <button class="btn btn-sm btn-outline-danger" onclick="deleteSearch(<?php echo $search['id']; ?>)">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </div>
                                        <div class="search-card-body">
                                            <div class="search-criteria">
                                                <?php if (isset($criteria['type'])): ?>
                                                    <span class="criteria-item">
                                                        <strong>Tip:</strong> <?php echo ucfirst(htmlspecialchars($criteria['type'])); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($criteria['location'])): ?>
                                                    <span class="criteria-item">
                                                        <strong>Konum:</strong> <?php echo htmlspecialchars($criteria['location']); ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($criteria['min_price']) || isset($criteria['max_price'])): ?>
                                                    <span class="criteria-item">
                                                        <strong>Fiyat:</strong> 
                                                        <?php 
                                                        if (isset($criteria['min_price']) && isset($criteria['max_price'])) {
                                                            echo number_format($criteria['min_price'], 0, ',', '.') . ' - ' . number_format($criteria['max_price'], 0, ',', '.') . ' ₺';
                                                        } elseif (isset($criteria['min_price'])) {
                                                            echo number_format($criteria['min_price'], 0, ',', '.') . ' ₺+';
                                                        } elseif (isset($criteria['max_price'])) {
                                                            echo number_format($criteria['max_price'], 0, ',', '.') . ' ₺ altı';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                                
                                                <?php if (isset($criteria['min_area']) || isset($criteria['max_area'])): ?>
                                                    <span class="criteria-item">
                                                        <strong>Alan:</strong> 
                                                        <?php 
                                                        if (isset($criteria['min_area']) && isset($criteria['max_area'])) {
                                                            echo $criteria['min_area'] . ' - ' . $criteria['max_area'] . ' m²';
                                                        } elseif (isset($criteria['min_area'])) {
                                                            echo $criteria['min_area'] . ' m²+';
                                                        } elseif (isset($criteria['max_area'])) {
                                                            echo $criteria['max_area'] . ' m² altı';
                                                        }
                                                        ?>
                                                    </span>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="search-stats">
                                                <div class="search-date">
                                                    <i class="fas fa-calendar me-1"></i>
                                                    Oluşturuldu: <?php echo date('d.m.Y', strtotime($search['created_at'])); ?>
                                                </div>
                                                <div class="notification-status <?php echo $search['notification_enabled'] ? 'active' : 'inactive'; ?>">
                                                    <i class="fas fa-<?php echo $search['notification_enabled'] ? 'bell' : 'bell-slash'; ?>"></i>
                                                    <?php echo $search['notification_enabled'] ? 'Bildirim Açık' : 'Bildirim Kapalı'; ?>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            <?php endwhile; ?>
                        </div>
                    <?php else: ?>
                        <div class="empty-searches">
                            <i class="fas fa-search"></i>
                            <h4>Henüz kayıtlı aramanız yok</h4>
                            <p>Arama kriterlerinizi kaydedin ve yeni ilanlar için otomatik bildirim alın.</p>
                            <button class="btn btn-primary btn-lg" onclick="createNewSearch()">
                                <i class="fas fa-plus me-2"></i>
                                İlk Aramanızı Oluşturun
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
    function createNewSearch() {
        // Ana sayfaya yönlendir ve arama modalını aç
        window.location.href = '../porfoy.html?open_save_search=1';
    }

    function editSearch(searchId) {
        // Arama düzenleme modalını aç
        fetch(`ajax/get-search.php?id=${searchId}`)
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Düzenleme modalını açabilirsiniz
                    alert('Düzenleme özelliği yakında eklenecek!');
                } else {
                    alert('Arama bulunamadı!');
                }
            })
            .catch(error => {
                console.error('Error:', error);
                alert('Bir hata oluştu!');
            });
    }

    function runSearch(searchId) {
        // Aramayı çalıştır ve sonuçlara git
        window.location.href = `../porfoy.html?run_search=${searchId}`;
    }

    function deleteSearch(searchId) {
        if (confirm('Bu kayıtlı aramayı silmek istediğinizden emin misiniz?')) {
            fetch('ajax/delete-search.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/x-www-form-urlencoded',
                },
                body: `search_id=${searchId}`
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

    function toggleNotification(searchId, currentStatus) {
        const newStatus = !currentStatus;
        
        fetch('ajax/toggle-notification.php', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/x-www-form-urlencoded',
            },
            body: `search_id=${searchId}&status=${newStatus ? 1 : 0}`
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
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
    </script>
</body>
</html>
