<?php
// 🚀 AJAX LAZY LOADING - Recent Data Endpoint
header('Content-Type: application/json');
include __DIR__ . '/../../db.php';

$type = $_GET['type'] ?? '';
$limit = intval($_GET['limit'] ?? 5);

$response = ['success' => false, 'data' => [], 'html' => '', 'debug' => ['type' => $type, 'limit' => $limit]];

try {
    // Database bağlantısını kontrol et
    if (!$conn) {
        throw new Exception('Database connection failed');
    }
    
    switch ($type) {
        case 'recent_users':
            $query = "SELECT id, name, email, role, created_at, is_active 
                     FROM users 
                     ORDER BY created_at DESC 
                     LIMIT ?";
            $stmt = $conn->prepare($query);
            if (!$stmt) {
                throw new Exception('Prepare failed: ' . $conn->error);
            }
            
            $stmt->bind_param("i", $limit);
            if (!$stmt->execute()) {
                throw new Exception('Execute failed: ' . $stmt->error);
            }
            
            $result = $stmt->get_result();
            $response['debug']['row_count'] = $result->num_rows;
            
            $html = '';
            while ($user = $result->fetch_assoc()) {
                $name_parts = explode(' ', trim($user['name']));
                $avatar = count($name_parts) >= 2 
                    ? strtoupper(substr($name_parts[0], 0, 1) . substr($name_parts[1], 0, 1))
                    : strtoupper(substr($user['name'], 0, 2));
                
                $html .= '
                <div class="list-item">
                    <div class="item-avatar">' . $avatar . '</div>
                    <div class="item-content">
                        <h6 class="item-title">' . htmlspecialchars($user['name']) . '</h6>
                        <small class="item-subtitle">
                            ' . htmlspecialchars($user['email']) . ' • 
                            ' . ucfirst($user['role']) . ' • 
                            ' . date('d.m.Y', strtotime($user['created_at'])) . '
                        </small>
                    </div>
                    <div class="item-actions">
                        <span class="badge ' . ($user['is_active'] ? 'bg-success' : 'bg-secondary') . '">
                            ' . ($user['is_active'] ? 'Aktif' : 'Pasif') . '
                        </span>
                        <a href="admin-users.php?user=' . $user['id'] . '" 
                           class="btn btn-sm btn-primary ms-2" title="Görüntüle">
                            <i class="fas fa-eye"></i>
                        </a>
                    </div>
                </div>';
            }
            
            if (empty($html)) {
                $html = '
                <div class="empty-state">
                    <i class="fas fa-users"></i>
                    <h6>Henüz kullanıcı yok</h6>
                    <p>İlk kullanıcı kayıt olduğunda burada görünecek.</p>
                </div>';
            }
            
            $response = ['success' => true, 'html' => $html, 'debug' => $response['debug']];
            break;
            
        case 'recent_properties':
            $query = "SELECT p.id, p.title, p.price, p.status, p.created_at,
                             u.name as owner_name
                     FROM properties p 
                     LEFT JOIN users u ON p.user_id = u.id
                     ORDER BY p.created_at DESC 
                     LIMIT ?";
            $stmt = $conn->prepare($query);
            $stmt->bind_param("i", $limit);
            $stmt->execute();
            $result = $stmt->get_result();
            
            $html = '';
            while ($property = $result->fetch_assoc()) {
                // Status mapping - PHP 8.3 uyumlu
                $status_class = 'bg-secondary';
                $status_text = 'Bilinmiyor';
                
                if ($property['status'] == 'approved') {
                    $status_class = 'bg-success';
                    $status_text = 'Onaylı';
                } elseif ($property['status'] == 'pending') {
                    $status_class = 'bg-warning';
                    $status_text = 'Beklemede';
                } elseif ($property['status'] == 'rejected') {
                    $status_class = 'bg-danger';
                    $status_text = 'Reddedildi';
                }
                
                $html .= '
                <div class="list-item">
                    <div class="item-icon">
                        <i class="fas fa-home"></i>
                    </div>
                    <div class="item-content">
                        <h6 class="item-title">' . htmlspecialchars($property['title']) . '</h6>
                        <small class="item-subtitle">
                            ' . htmlspecialchars($property['owner_name'] ?? 'Bilinmiyor') . ' • 
                            ' . number_format($property['price'] ?? 0, 0, ',', '.') . ' ₺ • 
                            ' . date('d.m.Y', strtotime($property['created_at'])) . '
                        </small>
                    </div>
                    <div class="item-actions">
                        <span class="badge ' . $status_class . '">' . $status_text . '</span>
                        <a href="add-property.php?edit=' . $property['id'] . '" 
                           class="btn btn-sm btn-warning ms-2" title="Düzenle">
                            <i class="fas fa-edit"></i>
                        </a>
                    </div>
                </div>';
            }
            
            if (empty($html)) {
                $html = '
                <div class="empty-state">
                    <i class="fas fa-home"></i>
                    <h6>Henüz emlak ilanı yok</h6>
                    <p>İlk emlak ilanı eklendiğinde burada görünecek.</p>
                </div>';
            }
            
            $response = ['success' => true, 'html' => $html];
            break;
            
        default:
            throw new Exception('Invalid type parameter');
    }
    
} catch (Exception $e) {
    $response = ['success' => false, 'error' => $e->getMessage()];
}

echo json_encode($response);
?>
