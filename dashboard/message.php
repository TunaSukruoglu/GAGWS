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

if (!$user) {
    header("Location: ../index.php");
    exit;
}

// Sayfa başlığı ve aktif menü
$page_title = "Mesajlar";
$current_page = 'message';

// Messages tablosunu oluştur
$create_messages_table = "
CREATE TABLE IF NOT EXISTS messages (
    id INT AUTO_INCREMENT PRIMARY KEY,
    sender_id INT NOT NULL,
    receiver_id INT NOT NULL,
    property_id INT NULL,
    subject VARCHAR(255) NOT NULL,
    message TEXT NOT NULL,
    is_read BOOLEAN DEFAULT FALSE,
    is_starred BOOLEAN DEFAULT FALSE,
    is_deleted BOOLEAN DEFAULT FALSE,
    message_type ENUM('inquiry', 'general', 'property', 'system') DEFAULT 'general',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    updated_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (receiver_id) REFERENCES users(id) ON DELETE CASCADE,
    FOREIGN KEY (property_id) REFERENCES properties(id) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";

$conn->query($create_messages_table);

// Message replies tablosu oluştur
$create_message_replies_table = "
CREATE TABLE IF NOT EXISTS message_replies (
    id INT AUTO_INCREMENT PRIMARY KEY,
    message_id INT NOT NULL,
    sender_id INT NOT NULL,
    reply_text TEXT NOT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (message_id) REFERENCES messages(id) ON DELETE CASCADE,
    FOREIGN KEY (sender_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_turkish_ci";

$conn->query($create_message_replies_table);

// Örnek mesajlar ekle (sadece tablo boşsa)
$check_messages = $conn->query("SELECT COUNT(*) as count FROM messages");
if ($check_messages->fetch_assoc()['count'] == 0) {
    $sample_messages = [
        [
            'sender_id' => 1,
            'receiver_id' => $user_id,
            'subject' => 'Hoş Geldiniz!',
            'message' => 'Gökhan Aydınlı Real Estate platformuna hoş geldiniz! Herhangi bir sorunuz olursa bizimle iletişime geçebilirsiniz.',
            'message_type' => 'system'
        ],
        [
            'sender_id' => 1,
            'receiver_id' => $user_id,
            'subject' => 'Emlak İlanı Hakkında',
            'message' => 'Merhaba, İstanbul Beşiktaş\'taki daire ilanınız hakkında bilgi almak istiyorum. Uygun bir zamanda görüşebilir miyiz?',
            'message_type' => 'property'
        ]
    ];
    
    foreach ($sample_messages as $msg) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, subject, message, message_type) VALUES (?, ?, ?, ?, ?)");
        $stmt->bind_param("iisss", $msg['sender_id'], $msg['receiver_id'], $msg['subject'], $msg['message'], $msg['message_type']);
        $stmt->execute();
    }
}

// Mesaj gönderme
if (isset($_POST['send_message'])) {
    $receiver_id = intval($_POST['receiver_id']);
    $subject = trim($_POST['subject']);
    $message = trim($_POST['message']);
    $message_type = $_POST['message_type'] ?? 'general';
    $property_id = !empty($_POST['property_id']) ? intval($_POST['property_id']) : null;
    
    if (!empty($subject) && !empty($message) && $receiver_id > 0) {
        $stmt = $conn->prepare("INSERT INTO messages (sender_id, receiver_id, property_id, subject, message, message_type) VALUES (?, ?, ?, ?, ?, ?)");
        $stmt->bind_param("iiisss", $user_id, $receiver_id, $property_id, $subject, $message, $message_type);
        
        if ($stmt->execute()) {
            $success = "Mesaj başarıyla gönderildi!";
        } else {
            $error = "Mesaj gönderilirken hata oluştu!";
        }
    } else {
        $error = "Lütfen tüm alanları doldurun!";
    }
}

// Mesaj okundu olarak işaretle
if (isset($_GET['read']) && is_numeric($_GET['read'])) {
    $message_id = intval($_GET['read']);
    $conn->query("UPDATE messages SET is_read = TRUE WHERE id = $message_id AND receiver_id = $user_id");
}

// Mesajı yıldızla/yıldızdan çıkar
if (isset($_POST['toggle_star'])) {
    $message_id = intval($_POST['message_id']);
    $stmt = $conn->prepare("UPDATE messages SET is_starred = NOT is_starred WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
    $stmt->execute();
}

// Mesajı sil
if (isset($_POST['delete_message'])) {
    $message_id = intval($_POST['message_id']);
    $stmt = $conn->prepare("UPDATE messages SET is_deleted = TRUE WHERE id = ? AND (sender_id = ? OR receiver_id = ?)");
    $stmt->bind_param("iii", $message_id, $user_id, $user_id);
    $stmt->execute();
    $success = "Mesaj silindi!";
}

// Mesaj yanıtlama
if (isset($_POST['reply_message'])) {
    $message_id = intval($_POST['message_id']);
    $reply_text = trim($_POST['reply_text']);
    
    if (!empty($reply_text)) {
        $stmt = $conn->prepare("INSERT INTO message_replies (message_id, sender_id, reply_text) VALUES (?, ?, ?)");
        $stmt->bind_param("iis", $message_id, $user_id, $reply_text);
        
        if ($stmt->execute()) {
            $success = "Yanıt gönderildi!";
        } else {
            $error = "Yanıt gönderilirken hata oluştu!";
        }
    }
}

// Mesaj türüne göre filtreleme
$filter = $_GET['filter'] ?? 'all';
$where_conditions = ["m.is_deleted = FALSE"];

if ($filter === 'unread') {
    $where_conditions[] = "m.is_read = FALSE AND m.receiver_id = $user_id";
} elseif ($filter === 'starred') {
    $where_conditions[] = "m.is_starred = TRUE";
} elseif ($filter === 'sent') {
    $where_conditions[] = "m.sender_id = $user_id";
} elseif ($filter === 'received') {
    $where_conditions[] = "m.receiver_id = $user_id";
} else {
    $where_conditions[] = "(m.sender_id = $user_id OR m.receiver_id = $user_id)";
}

$where_clause = implode(' AND ', $where_conditions);

// Mesajları getir
$messages_query = "
    SELECT m.*, 
           sender.name as sender_name,
           sender.email as sender_email,
           receiver.name as receiver_name,
           receiver.email as receiver_email,
           p.title as property_title
    FROM messages m
    LEFT JOIN users sender ON m.sender_id = sender.id
    LEFT JOIN users receiver ON m.receiver_id = receiver.id
    LEFT JOIN properties p ON m.property_id = p.id
    WHERE $where_clause
    ORDER BY m.created_at DESC
";

$messages_result = $conn->query($messages_query);

// İstatistikler
$stats_query = "
    SELECT 
        COUNT(*) as total_messages,
        SUM(CASE WHEN is_read = FALSE AND receiver_id = $user_id THEN 1 ELSE 0 END) as unread_count,
        SUM(CASE WHEN is_starred = TRUE THEN 1 ELSE 0 END) as starred_count,
        SUM(CASE WHEN sender_id = $user_id THEN 1 ELSE 0 END) as sent_count
    FROM messages 
    WHERE (sender_id = $user_id OR receiver_id = $user_id) AND is_deleted = FALSE
";

$stats_result = $conn->query($stats_query);
$stats = $stats_result->fetch_assoc();

// Diğer kullanıcıları getir (mesaj göndermek için)
$users_query = "SELECT id, name, email FROM users WHERE id != ? AND is_active = TRUE ORDER BY name";
$users_stmt = $conn->prepare($users_query);
$users_stmt->bind_param("i", $user_id);
$users_stmt->execute();
$users_result = $users_stmt->get_result();

// Mevcut emlakları getir
$properties_query = "SELECT id, title FROM properties WHERE user_id = ? ORDER BY title";
$properties_stmt = $conn->prepare($properties_query);
$properties_stmt->bind_param("i", $user_id);
$properties_stmt->execute();
$properties_result = $properties_stmt->get_result();
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= $page_title ?> - <?= htmlspecialchars($user['name']) ?></title>
    
    <!-- Bootstrap CSS -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/bootstrap/5.3.0/css/bootstrap.min.css">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">
    <!-- Main CSS -->
    <link rel="stylesheet" type="text/css" href="../css/style.min.css">
    <!-- Dashboard Common CSS -->
    <link rel="stylesheet" type="text/css" href="includes/dashboard-common.css">
    
    <style>
        /* Messages Specific Styles */
        :root {
            --primary-color: #0D1A1C;
            --secondary-color: #0d6efd;
            --accent-color: #FF6B35;
            --light-bg: #F8F9FA;
            --border-radius: 20px;
            --box-shadow: 0 5px 25px rgba(0,0,0,0.08);
        }

        .message-sidebar {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            padding: 25px;
            height: fit-content;
            position: sticky;
            top: 20px;
        }

        .message-filters {
            list-style: none;
            padding: 0;
            margin: 0;
        }

        .message-filters li {
            margin-bottom: 8px;
        }

        .message-filters a {
            display: flex;
            align-items: center;
            padding: 14px 16px;
            color: #6c757d;
            text-decoration: none;
            border-radius: 12px;
            transition: all 0.3s ease;
            font-weight: 500;
        }

        .message-filters a:hover,
        .message-filters a.active {
            background: rgba(13, 110, 253, 0.1);
            color: var(--secondary-color);
            transform: translateX(5px);
            box-shadow: 0 4px 15px rgba(13, 110, 253, 0.15);
        }

        .message-filters a.active {
            background: rgba(13, 110, 253, 0.15);
            border-left: 3px solid var(--secondary-color);
            font-weight: 600;
        }

        .message-filters i {
            margin-right: 12px;
            width: 20px;
        }

        .message-count {
            background: var(--accent-color);
            color: white;
            font-size: 12px;
            padding: 4px 8px;
            border-radius: 12px;
            margin-left: auto;
            font-weight: 600;
        }

        .messages-container {
            background: white;
            border-radius: var(--border-radius);
            box-shadow: var(--box-shadow);
            overflow: hidden;
        }

        .messages-header {
            background: linear-gradient(135deg, var(--secondary-color), #0b5ed7);
            color: white;
            padding: 25px;
            display: flex;
            justify-content: between;
            align-items: center;
        }

        .messages-header h4 {
            margin: 0;
            font-weight: 600;
            font-size: 20px;
        }

        .message-item {
            border-bottom: 1px solid #f0f0f0;
            padding: 25px;
            transition: all 0.3s ease;
            cursor: pointer;
        }

        .message-item:hover {
            background: #f8f9fa;
            transform: translateX(5px);
        }

        .message-item.unread {
            background: rgba(13, 110, 253, 0.05);
            border-left: 4px solid var(--secondary-color);
        }

        .message-header {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 15px;
        }

        .message-sender {
            display: flex;
            align-items: center;
            gap: 15px;
        }

        .sender-avatar {
            width: 45px;
            height: 45px;
            border-radius: 50%;
            background: var(--secondary-color);
            display: flex;
            align-items: center;
            justify-content: center;
            color: white;
            font-weight: bold;
            font-size: 16px;
        }

        .sender-info h6 {
            margin: 0;
            font-weight: 600;
            color: var(--primary-color);
            font-size: 16px;
        }

        .message-date {
            color: #6c757d;
            font-size: 13px;
            margin-top: 4px;
        }

        .message-meta {
            display: flex;
            align-items: center;
            gap: 12px;
        }

        .message-type-badge {
            padding: 4px 12px;
            border-radius: 15px;
            font-size: 11px;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.5px;
        }

        .message-type-system {
            background: #e3f2fd;
            color: #1976d2;
        }

        .message-type-property {
            background: rgba(13, 110, 253, 0.1);
            color: var(--secondary-color);
        }

        .message-type-inquiry {
            background: #fff3e0;
            color: #f57c00;
        }

        .message-type-general {
            background: #f3e5f5;
            color: #7b1fa2;
        }

        .message-subject {
            font-weight: 600;
            color: var(--primary-color);
            margin-bottom: 8px;
            font-size: 16px;
            line-height: 1.4;
        }

        .message-preview {
            color: #6c757d;
            font-size: 14px;
            line-height: 1.5;
            margin-bottom: 15px;
        }

        .message-actions {
            display: flex;
            gap: 8px;
            margin-top: 15px;
        }

        .message-actions .btn {
            padding: 8px 12px;
            font-size: 12px;
            border-radius: 8px;
            font-weight: 500;
        }

        .compose-btn {
            background: var(--secondary-color);
            color: white;
            border: none;
            border-radius: 12px;
            padding: 14px 20px;
            font-weight: 600;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-flex;
            align-items: center;
            gap: 10px;
            font-size: 14px;
        }

        .compose-btn:hover {
            background: #0b5ed7;
            color: white;
            transform: translateY(-2px);
            box-shadow: 0 8px 25px rgba(13, 110, 253, 0.3);
        }

        .empty-state {
            text-align: center;
            padding: 80px 30px;
            color: #6c757d;
        }

        .empty-state i {
            font-size: 64px;
            margin-bottom: 20px;
            opacity: 0.3;
            color: var(--secondary-color);
        }

        .empty-state h4 {
            color: var(--primary-color);
            margin-bottom: 15px;
        }

        .star-btn {
            background: none;
            border: none;
            color: #ddd;
            font-size: 16px;
            cursor: pointer;
            transition: color 0.3s ease;
            padding: 8px;
        }

        .star-btn.starred {
            color: #ffc107;
        }

        .star-btn:hover {
            color: #ffc107;
        }

        .badge.bg-primary {
            background: var(--secondary-color) !important;
        }

        .modal-content {
            border-radius: var(--border-radius);
            border: none;
            box-shadow: var(--box-shadow);
        }

        .modal-header {
            background: var(--light-bg);
            border-bottom: 1px solid #f0f0f0;
            border-radius: var(--border-radius) var(--border-radius) 0 0;
        }

        .form-control, .form-select {
            border: 2px solid #E6E6E6;
            border-radius: 10px;
            padding: 12px 16px;
            font-size: 14px;
            transition: all 0.3s ease;
        }

        .form-control:focus, .form-select:focus {
            border-color: var(--secondary-color);
            box-shadow: 0 0 0 4px rgba(13, 110, 253, 0.1);
        }

        .btn-primary {
            background: var(--secondary-color);
            border-color: var(--secondary-color);
        }

        .btn-primary:hover {
            background: #0b5ed7;
            border-color: #0b5ed7;
        }

        @media (max-width: 768px) {
            .message-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            
            .message-meta {
                flex-direction: column;
                align-items: flex-start;
                gap: 8px;
            }
            
            .message-actions {
                flex-wrap: wrap;
                gap: 8px;
            }
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

                <h2 class="main-title d-block d-lg-none"><?= $page_title ?></h2>

                <!-- Success/Error Messages -->
                <?php if (isset($success)): ?>
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="fas fa-check-circle me-2"></i>
                        <?= $success ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>
                
                <?php if (isset($error)): ?>
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="fas fa-exclamation-circle me-2"></i>
                        <?= $error ?>
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                <?php endif; ?>

                <!-- Stats Cards -->
                <div class="stats-cards">
                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-envelope"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['total_messages'] ?></div>
                        <div class="stats-label">Toplam Mesaj</div>
                        <div class="stats-change positive">
                            <i class="fas fa-chart-line"></i>
                            <span>Aktif</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-envelope-open"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['unread_count'] ?></div>
                        <div class="stats-label">Okunmamış</div>
                        <div class="stats-change <?= $stats['unread_count'] > 0 ? 'positive' : 'negative' ?>">
                            <i class="fas fa-<?= $stats['unread_count'] > 0 ? 'exclamation' : 'check' ?>"></i>
                            <span><?= $stats['unread_count'] > 0 ? 'Yeni mesaj' : 'Hepsi okundu' ?></span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-star"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['starred_count'] ?></div>
                        <div class="stats-label">Yıldızlı</div>
                        <div class="stats-change positive">
                            <i class="fas fa-bookmark"></i>
                            <span>Önemli</span>
                        </div>
                    </div>

                    <div class="stats-card">
                        <div class="stats-card-header">
                            <div class="stats-icon">
                                <i class="fas fa-paper-plane"></i>
                            </div>
                        </div>
                        <div class="stats-number"><?= $stats['sent_count'] ?></div>
                        <div class="stats-label">Gönderilen</div>
                        <div class="stats-change positive">
                            <i class="fas fa-check-circle"></i>
                            <span>Gönderildi</span>
                        </div>
                    </div>
                </div>

                <div class="row">
                    <!-- Message Sidebar -->
                    <div class="col-lg-3 mb-4">
                        <div class="message-sidebar">
                            <h5 class="section-title mb-3">
                                <i class="fas fa-filter me-2"></i>
                                Filtreler
                            </h5>
                            <ul class="message-filters">
                                <li>
                                    <a href="?filter=all" class="<?= $filter === 'all' ? 'active' : '' ?>">
                                        <i class="fas fa-inbox"></i>
                                        <span>Tüm Mesajlar</span>
                                        <span class="message-count"><?= $stats['total_messages'] ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="?filter=unread" class="<?= $filter === 'unread' ? 'active' : '' ?>">
                                        <i class="fas fa-envelope"></i>
                                        <span>Okunmamış</span>
                                        <?php if ($stats['unread_count'] > 0): ?>
                                            <span class="message-count"><?= $stats['unread_count'] ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="?filter=starred" class="<?= $filter === 'starred' ? 'active' : '' ?>">
                                        <i class="fas fa-star"></i>
                                        <span>Yıldızlı</span>
                                        <?php if ($stats['starred_count'] > 0): ?>
                                            <span class="message-count"><?= $stats['starred_count'] ?></span>
                                        <?php endif; ?>
                                    </a>
                                </li>
                                <li>
                                    <a href="?filter=sent" class="<?= $filter === 'sent' ? 'active' : '' ?>">
                                        <i class="fas fa-paper-plane"></i>
                                        <span>Gönderilen</span>
                                        <span class="message-count"><?= $stats['sent_count'] ?></span>
                                    </a>
                                </li>
                                <li>
                                    <a href="?filter=received" class="<?= $filter === 'received' ? 'active' : '' ?>">
                                        <i class="fas fa-download"></i>
                                        <span>Gelen</span>
                                    </a>
                                </li>
                            </ul>
                            
                            <hr class="my-4">
                            
                            <button class="compose-btn w-100" data-bs-toggle="modal" data-bs-target="#composeModal">
                                <i class="fas fa-pen"></i>
                                Yeni Mesaj
                            </button>
                        </div>
                    </div>

                    <!-- Messages List -->
                    <div class="col-lg-9">
                        <div class="messages-container">
                            <div class="messages-header">
                                <h4>
                                    <?php
                                    switch ($filter) {
                                        case 'unread': echo 'Okunmamış Mesajlar'; break;
                                        case 'starred': echo 'Yıldızlı Mesajlar'; break;
                                        case 'sent': echo 'Gönderilen Mesajlar'; break;
                                        case 'received': echo 'Gelen Mesajlar'; break;
                                        default: echo 'Tüm Mesajlar'; break;
                                    }
                                    ?>
                                </h4>
                                <span class="badge bg-light text-dark"><?= $messages_result->num_rows ?> mesaj</span>
                            </div>

                            <div class="messages-list">
                                <?php if ($messages_result->num_rows > 0): ?>
                                    <?php while ($message = $messages_result->fetch_assoc()): ?>
                                        <div class="message-item <?= !$message['is_read'] && $message['receiver_id'] == $user_id ? 'unread' : '' ?>" 
                                             onclick="readMessage(<?= $message['id'] ?>)">
                                            <div class="message-header">
                                                <div class="message-sender">
                                                    <div class="sender-avatar">
                                                        <?php
                                                        $display_name = $message['sender_id'] == $user_id ? $message['receiver_name'] : $message['sender_name'];
                                                        // Türkçe isimler için daha iyi avatar oluştur
                                                        $name_parts = explode(' ', trim($display_name));
                                                        if (count($name_parts) >= 2) {
                                                            // İsim ve soyisimin ilk harflerini al
                                                            echo strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1));
                                                        } else {
                                                            // Tek kelime ise ilk iki harfini al
                                                            echo strtoupper(substr($display_name, 0, 2));
                                                        }
                                                        ?>
                                                    </div>
                                                    <div class="sender-info">
                                                        <h6><?= htmlspecialchars($display_name) ?></h6>
                                                        <div class="message-date">
                                                            <?= date('d.m.Y H:i', strtotime($message['created_at'])) ?>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="message-meta">
                                                    <span class="message-type-badge message-type-<?= $message['message_type'] ?>">
                                                        <?= match($message['message_type']) {
                                                            'system' => 'Sistem',
                                                            'property' => 'Emlak',
                                                            'inquiry' => 'Talep',
                                                            'general' => 'Genel',
                                                            default => 'Genel'
                                                        } ?>
                                                    </span>
                                                    <?php if (!$message['is_read'] && $message['receiver_id'] == $user_id): ?>
                                                        <span class="badge bg-primary">Yeni</span>
                                                    <?php endif; ?>
                                                </div>
                                            </div>
                                            
                                            <div class="message-subject">
                                                <?= htmlspecialchars($message['subject']) ?>
                                                <?php if ($message['property_title']): ?>
                                                    <small class="text-muted">- <?= htmlspecialchars($message['property_title']) ?></small>
                                                <?php endif; ?>
                                            </div>
                                            
                                            <div class="message-preview">
                                                <?= substr(strip_tags($message['message']), 0, 150) ?>...
                                            </div>

                                            <div class="message-actions" onclick="event.stopPropagation()">
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                    <button type="submit" name="toggle_star" class="star-btn <?= $message['is_starred'] ? 'starred' : '' ?>" title="Yıldızla">
                                                        <i class="fas fa-star"></i>
                                                    </button>
                                                </form>
                                                <button class="btn btn-sm btn-outline-primary" onclick="replyMessage(<?= $message['id'] ?>)" title="Yanıtla">
                                                    <i class="fas fa-reply"></i>
                                                </button>
                                                <form method="POST" class="d-inline">
                                                    <input type="hidden" name="message_id" value="<?= $message['id'] ?>">
                                                    <input type="hidden" name="delete_message" value="1">
                                                    <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu mesajı silmek istediğinizden emin misiniz?')" title="Sil">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </form>
                                            </div>
                                        </div>
                                    <?php endwhile; ?>
                                <?php else: ?>
                                    <div class="empty-state">
                                        <i class="fas fa-envelope-open"></i>
                                        <h4>Mesaj bulunamadı</h4>
                                        <p>Bu kategoride henüz mesaj bulunmuyor.</p>
                                        <button class="dash-btn-two" data-bs-toggle="modal" data-bs-target="#composeModal">
                                            <i class="fas fa-pen me-2"></i>
                                            İlk Mesajınızı Gönderin
                                        </button>
                                    </div>
                                <?php endif; ?>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Compose Message Modal -->
        <div class="modal fade" id="composeModal" tabindex="-1">
            <div class="modal-dialog modal-lg">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title">
                            <i class="fas fa-pen me-2"></i>
                            Yeni Mesaj
                        </h5>
                        <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                    </div>
                    <form method="POST">
                        <div class="modal-body">
                            <div class="row">
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Alıcı <span class="text-danger">*</span></label>
                                    <select class="form-select" name="receiver_id" required>
                                        <option value="">Alıcı seçin...</option>
                                        <?php $users_result->data_seek(0); ?>
                                        <?php while ($user_option = $users_result->fetch_assoc()): ?>
                                            <option value="<?= $user_option['id'] ?>"><?= htmlspecialchars($user_option['name']) ?> (<?= htmlspecialchars($user_option['email']) ?>)</option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">Mesaj Türü</label>
                                    <select class="form-select" name="message_type">
                                        <option value="general">Genel</option>
                                        <option value="inquiry">Soru/Talep</option>
                                        <option value="property">Emlak İlanı</option>
                                    </select>
                                </div>
                                <div class="col-md-6 mb-3">
                                    <label class="form-label">İlgili Emlak (Opsiyonel)</label>
                                    <select class="form-select" name="property_id">
                                        <option value="">Emlak seçin...</option>
                                        <?php $properties_result->data_seek(0); ?>
                                        <?php while ($property = $properties_result->fetch_assoc()): ?>
                                            <option value="<?= $property['id'] ?>"><?= htmlspecialchars($property['title']) ?></option>
                                        <?php endwhile; ?>
                                    </select>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Konu <span class="text-danger">*</span></label>
                                    <input type="text" class="form-control" name="subject" placeholder="Mesaj konusu" required>
                                </div>
                                <div class="col-12 mb-3">
                                    <label class="form-label">Mesaj <span class="text-danger">*</span></label>
                                    <textarea class="form-control" name="message" rows="6" placeholder="Mesajınızı yazın..." required></textarea>
                                </div>
                            </div>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                            <button type="submit" name="send_message" class="btn btn-primary">
                                <i class="fas fa-paper-plane me-2"></i>
                                Gönder
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="../vendor/jquery.min.js"></script>
    <script src="../vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="../js/theme.js"></script>
    
    <script>
        // Mobile nav toggle
        document.querySelector('.dash-mobile-nav-toggler')?.addEventListener('click', function() {
            document.querySelector('.dash-aside-navbar').classList.toggle('show');
        });

        // Read message function
        function readMessage(messageId) {
            window.location.href = `?read=${messageId}&filter=<?= $filter ?>`;
        }

        // Reply message function
        function replyMessage(messageId) {
            console.log('Reply to message:', messageId);
            // Yanıt fonksiyonu burada implementiert edilecek
        }

        // Auto-dismiss alerts
        setTimeout(function() {
            var alerts = document.querySelectorAll('.alert');
            alerts.forEach(function(alert) {
                if (bootstrap.Alert) {
                    var bsAlert = new bootstrap.Alert(alert);
                    bsAlert.close();
                }
            });
        }, 5000);

        // Form validation
        document.querySelector('#composeModal form').addEventListener('submit', function(e) {
            const receiver = document.querySelector('select[name="receiver_id"]').value;
            const subject = document.querySelector('input[name="subject"]').value.trim();
            const message = document.querySelector('textarea[name="message"]').value.trim();
            
            if (!receiver || !subject || !message) {
                e.preventDefault();
                alert('Lütfen tüm gerekli alanları doldurun!');
                return false;
            }
        });

        // Loading states
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', function() {
                const submitBtn = this.querySelector('button[type="submit"]');
                if (submitBtn && submitBtn.name === 'send_message') {
                    submitBtn.disabled = true;
                    submitBtn.innerHTML = '<i class="fas fa-spinner fa-spin me-2"></i>Gönderiliyor...';
                }
            });
        });

        // Stats cards animation
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
                }
            });
        }, observerOptions);

        document.querySelectorAll('.stats-card, .message-item').forEach(card => {
            observer.observe(card);
        });
    </script>
</body>
</html>