<?php
session_start();
include 'db.php';

// Güvenli HTML çıktı fonksiyonu
function safe_html($value) {
    if ($value === null) {
        return '<span style="color: #999; font-style: italic;">NULL</span>';
    }
    return htmlspecialchars($value);
}

// Action handling
$action = $_GET['action'] ?? 'tables';
$table = $_GET['table'] ?? '';
$message = '';

// Kullanıcı silme işlemi
if (isset($_POST['delete_user'])) {
    $user_id = (int)$_POST['user_id'];
    try {
        // Önce kullanıcı bilgilerini al
        $check_stmt = $conn->prepare("SELECT name, email FROM users WHERE id = ?");
        $check_stmt->bind_param("i", $user_id);
        $check_stmt->execute();
        $result = $check_stmt->get_result();
        
        if ($result->num_rows > 0) {
            $user = $result->fetch_assoc();
            
            // Kullanıcıyı sil
            $delete_stmt = $conn->prepare("DELETE FROM users WHERE id = ?");
            $delete_stmt->bind_param("i", $user_id);
            
            if ($delete_stmt->execute()) {
                $message = "<div class='success'>✅ Kullanıcı başarıyla silindi: " . htmlspecialchars($user['name']) . " (" . htmlspecialchars($user['email']) . ")</div>";
            } else {
                $message = "<div class='error'>❌ Kullanıcı silinirken hata oluştu.</div>";
            }
            $delete_stmt->close();
        } else {
            $message = "<div class='error'>❌ Kullanıcı bulunamadı.</div>";
        }
        $check_stmt->close();
    } catch (Exception $e) {
        $message = "<div class='error'>❌ Hata: " . $e->getMessage() . "</div>";
    }
}

// Kullanıcı doğrulama durumu değiştirme
if (isset($_POST['toggle_verification'])) {
    $user_id = (int)$_POST['user_id'];
    $new_status = (int)$_POST['new_status'];
    try {
        $update_stmt = $conn->prepare("UPDATE users SET is_verified = ?, verified_at = ? WHERE id = ?");
        $verified_at = $new_status ? date('Y-m-d H:i:s') : null;
        $update_stmt->bind_param("isi", $new_status, $verified_at, $user_id);
        
        if ($update_stmt->execute()) {
            $status_text = $new_status ? 'aktifleştirildi' : 'deaktifleştirildi';
            $message = "<div class='success'>✅ Kullanıcı başarıyla $status_text.</div>";
        } else {
            $message = "<div class='error'>❌ Durum güncellenirken hata oluştu.</div>";
        }
        $update_stmt->close();
    } catch (Exception $e) {
        $message = "<div class='error'>❌ Hata: " . $e->getMessage() . "</div>";
    }
}

// SQL query execution
if ($_POST['sql'] ?? false) {
    $sql = $_POST['sql'];
    try {
        $result = $conn->query($sql);
        if ($result === TRUE) {
            $message = "<div class='success'>✅ Query executed successfully!</div>";
        } elseif ($result) {
            $message = "<div class='success'>✅ Query executed successfully! " . $result->num_rows . " rows returned.</div>";
        }
    } catch (Exception $e) {
        $message = "<div class='error'>❌ Error: " . $e->getMessage() . "</div>";
    }
}
?>

<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>📊 Basit DB Yönetici - phpMyAdmin Alternatifi</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 0; background: #f5f5f5; }
        .container { max-width: 1200px; margin: 0 auto; background: white; min-height: 100vh; }
        .header { background: #2c3e50; color: white; padding: 20px; text-align: center; }
        .sidebar { float: left; width: 250px; background: #34495e; min-height: calc(100vh - 80px); padding: 20px; box-sizing: border-box; }
        .content { margin-left: 250px; padding: 20px; }
        .sidebar h3 { color: white; margin-top: 0; }
        .sidebar ul { list-style: none; padding: 0; }
        .sidebar li { margin: 5px 0; }
        .sidebar a { color: #bdc3c7; text-decoration: none; padding: 8px; display: block; border-radius: 4px; }
        .sidebar a:hover { background: #2c3e50; color: white; }
        .sidebar a.active { background: #3498db; color: white; }
        table { width: 100%; border-collapse: collapse; margin: 20px 0; }
        th, td { padding: 12px; text-align: left; border-bottom: 1px solid #ddd; }
        th { background-color: #f8f9fa; font-weight: bold; }
        tr:hover { background-color: #f5f5f5; }
        .sql-box { width: 100%; height: 200px; padding: 10px; border: 1px solid #ddd; border-radius: 4px; font-family: monospace; }
        .btn { background: #3498db; color: white; padding: 10px 20px; border: none; border-radius: 4px; cursor: pointer; }
        .btn:hover { background: #2980b9; }
        .btn-danger { background: #dc3545; color: white; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-danger:hover { background: #c82333; }
        .btn-warning { background: #ffc107; color: #212529; padding: 6px 12px; border: none; border-radius: 4px; cursor: pointer; font-size: 12px; }
        .btn-warning:hover { background: #e0a800; }
        .action-buttons { display: flex; gap: 5px; }
        .confirm-delete { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; border-radius: 5px; margin: 10px 0; }
        .success { background: #d4edda; color: #155724; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .error { background: #f8d7da; color: #721c24; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .info { background: #d1ecf1; color: #0c5460; padding: 10px; border-radius: 4px; margin: 10px 0; }
        .stats { display: grid; grid-template-columns: repeat(auto-fit, minmax(200px, 1fr)); gap: 15px; margin: 20px 0; }
        .stat-box { background: #3498db; color: white; padding: 20px; border-radius: 8px; text-align: center; }
        .stat-number { font-size: 2em; font-weight: bold; }
    </style>
</head>
<body>
    <div class="container">
        <div class="header">
            <h1>📊 Basit Veritabanı Yöneticisi</h1>
            <p>phpMyAdmin Alternatifi - Gökhan Aydınlı Gayrimenkul</p>
        </div>

        <div class="sidebar">
            <h3>📋 Tablolar</h3>
            <ul>
                <li><a href="?action=tables" class="<?= $action === 'tables' ? 'active' : '' ?>">📊 Tüm Tablolar</a></li>
                <?php
                try {
                    $result = $conn->query("SHOW TABLES");
                    while($row = $result->fetch_array()) {
                        $active = ($action === 'table' && $table === $row[0]) ? 'active' : '';
                        echo "<li><a href='?action=table&table=" . $row[0] . "' class='$active'>📋 " . $row[0] . "</a></li>";
                    }
                } catch (Exception $e) {
                    echo "<li>❌ Hata: " . $e->getMessage() . "</li>";
                }
                ?>
            </ul>
            
            <h3>🔧 Araçlar</h3>
            <ul>
                <li><a href="?action=sql" class="<?= $action === 'sql' ? 'active' : '' ?>">⚡ SQL Çalıştır</a></li>
                <li><a href="?action=users" class="<?= $action === 'users' ? 'active' : '' ?>">👥 Kullanıcılar</a></li>
                <li><a href="?action=email_logs" class="<?= $action === 'email_logs' ? 'active' : '' ?>">📧 Email Logları</a></li>
            </ul>
        </div>

        <div class="content">
            <?= $message ?>

            <?php if ($action === 'tables'): ?>
                <h2>📊 Veritabanı Tabloları</h2>
                <table>
                    <thead>
                        <tr>
                            <th>Tablo Adı</th>
                            <th>Satır Sayısı</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php
                        try {
                            $result = $conn->query("SHOW TABLES");
                            while($row = $result->fetch_array()) {
                                $table_name = $row[0];
                                $count_result = $conn->query("SELECT COUNT(*) as count FROM `$table_name`");
                                $count = $count_result->fetch_assoc()['count'];
                                
                                echo "<tr>";
                                echo "<td><strong>$table_name</strong></td>";
                                echo "<td>$count kayıt</td>";
                                echo "<td><a href='?action=table&table=$table_name' class='btn'>👁️ Görüntüle</a></td>";
                                echo "</tr>";
                            }
                        } catch (Exception $e) {
                            echo "<tr><td colspan='3'>❌ Hata: " . $e->getMessage() . "</td></tr>";
                        }
                        ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'table' && $table): ?>
                <h2>📋 <?= htmlspecialchars($table) ?> Tablosu</h2>
                
                <!-- Tablo yapısı -->
                <h3>🏗️ Tablo Yapısı</h3>
                <table>
                    <thead>
                        <tr><th>Sütun</th><th>Tip</th><th>Null</th><th>Key</th><th>Default</th><th>Extra</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("DESCRIBE `$table`");
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            echo "<td><strong>" . $row['Field'] . "</strong></td>";
                            echo "<td>" . $row['Type'] . "</td>";
                            echo "<td>" . $row['Null'] . "</td>";
                            echo "<td>" . $row['Key'] . "</td>";
                            echo "<td>" . $row['Default'] . "</td>";
                            echo "<td>" . $row['Extra'] . "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

                <!-- Tablo verileri -->
                <h3>📄 Veriler (Son 50 kayıt)</h3>
                <table>
                    <?php
                    $result = $conn->query("SELECT * FROM `$table` ORDER BY 1 DESC LIMIT 50");
                    if ($result && $result->num_rows > 0) {
                        // Başlıkları yazdır
                        echo "<thead><tr>";
                        $fields = $result->fetch_fields();
                        foreach ($fields as $field) {
                            echo "<th>" . htmlspecialchars($field->name) . "</th>";
                        }
                        echo "</tr></thead><tbody>";
                        
                        // Verileri yazdır
                        $result->data_seek(0);
                        while($row = $result->fetch_assoc()) {
                            echo "<tr>";
                            foreach($row as $value) {
                                // Null değerleri kontrol et
                                $safe_value = $value ?? '';
                                $display_value = htmlspecialchars($safe_value);
                                if (strlen($display_value) > 50) {
                                    $display_value = substr($display_value, 0, 50) . '...';
                                }
                                // Null ise özel gösterim
                                if ($value === null) {
                                    $display_value = '<span style="color: #999; font-style: italic;">NULL</span>';
                                }
                                echo "<td>" . $display_value . "</td>";
                            }
                            echo "</tr>";
                        }
                        echo "</tbody>";
                    } else {
                        echo "<tr><td>Bu tabloda veri bulunmuyor.</td></tr>";
                    }
                    ?>
                </table>

            <?php elseif ($action === 'sql'): ?>
                <h2>⚡ SQL Sorgusu Çalıştır</h2>
                <form method="POST">
                    <textarea name="sql" class="sql-box" placeholder="SQL sorgunuzu buraya yazın...
Örnek:
SELECT * FROM users;
SHOW TABLES;
SELECT COUNT(*) FROM email_logs;"><?= htmlspecialchars($_POST['sql'] ?? '') ?></textarea>
                    <br><br>
                    <button type="submit" class="btn">🚀 Çalıştır</button>
                </form>

                <?php if (isset($_POST['sql']) && $result): ?>
                    <h3>📊 Sorgu Sonucu</h3>
                    <table>
                        <?php
                        if (is_object($result) && $result->num_rows > 0) {
                            // Başlıkları yazdır
                            echo "<thead><tr>";
                            $fields = $result->fetch_fields();
                            foreach ($fields as $field) {
                                echo "<th>" . htmlspecialchars($field->name) . "</th>";
                            }
                            echo "</tr></thead><tbody>";
                            
                            // Verileri yazdır
                            while($row = $result->fetch_assoc()) {
                                echo "<tr>";
                                foreach($row as $value) {
                                    // Null değerleri kontrol et
                                    if ($value === null) {
                                        echo "<td><span style='color: #999; font-style: italic;'>NULL</span></td>";
                                    } else {
                                        echo "<td>" . htmlspecialchars($value) . "</td>";
                                    }
                                }
                                echo "</tr>";
                            }
                            echo "</tbody>";
                        }
                        ?>
                    </table>
                <?php endif; ?>

            <?php elseif ($action === 'users'): ?>
                <h2>👥 Kullanıcı Yönetimi</h2>
                <div class="stats">
                    <?php
                    $total = $conn->query("SELECT COUNT(*) as count FROM users")->fetch_assoc()['count'];
                    $verified = $conn->query("SELECT COUNT(*) as count FROM users WHERE is_verified = 1")->fetch_assoc()['count'];
                    $unverified = $total - $verified;
                    $today = $conn->query("SELECT COUNT(*) as count FROM users WHERE DATE(created_at) = CURDATE()")->fetch_assoc()['count'];
                    ?>
                    <div class="stat-box">
                        <div class="stat-number"><?= $total ?></div>
                        <div>Toplam Kullanıcı</div>
                    </div>
                    <div class="stat-box" style="background: #27ae60;">
                        <div class="stat-number"><?= $verified ?></div>
                        <div>Doğrulanmış</div>
                    </div>
                    <div class="stat-box" style="background: #e74c3c;">
                        <div class="stat-number"><?= $unverified ?></div>
                        <div>Doğrulanmamış</div>
                    </div>
                    <div class="stat-box" style="background: #f39c12;">
                        <div class="stat-number"><?= $today ?></div>
                        <div>Bugün</div>
                    </div>
                </div>

                <table>
                    <thead>
                        <tr><th>ID</th><th>Ad</th><th>Email</th><th>Telefon</th><th>Doğrulama</th><th>Kayıt Tarihi</th><th>İşlemler</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT id, name, email, phone, is_verified, created_at FROM users ORDER BY created_at DESC LIMIT 50");
                        while($row = $result->fetch_assoc()) {
                            $verified_status = $row['is_verified'] ? '<span style="color: green;">✅ Doğrulanmış</span>' : '<span style="color: red;">❌ Bekliyor</span>';
                            echo "<tr>";
                            echo "<td>" . $row['id'] . "</td>";
                            echo "<td>" . htmlspecialchars($row['name'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['email'] ?? '') . "</td>";
                            echo "<td>" . htmlspecialchars($row['phone'] ?? 'Belirtilmemiş') . "</td>";
                            echo "<td>$verified_status</td>";
                            echo "<td>" . date('d.m.Y H:i', strtotime($row['created_at'])) . "</td>";
                            echo "<td class='action-buttons'>";
                            echo "<button onclick=\"toggleVerification(" . $row['id'] . ", " . ($row['is_verified'] ? 'false' : 'true') . ")\" class='btn-warning'>";
                            echo $row['is_verified'] ? '🔒 Deaktif Et' : '✅ Aktif Et';
                            echo "</button>";
                            echo "<button onclick=\"deleteUser(" . $row['id'] . ", '" . htmlspecialchars($row['name'], ENT_QUOTES) . "')\" class='btn-danger'>🗑️ Sil</button>";
                            echo "</td>";
                            echo "</tr>";
                        }
                        ?>
                    </tbody>
                </table>

            <?php elseif ($action === 'email_logs'): ?>
                <h2>📧 Email Logları</h2>
                <table>
                    <thead>
                        <tr><th>ID</th><th>Alıcı</th><th>Konu</th><th>Durum</th><th>Yöntem</th><th>Tarih</th></tr>
                    </thead>
                    <tbody>
                        <?php
                        $result = $conn->query("SELECT id, to_email, subject, status, method, sent_at FROM email_logs ORDER BY sent_at DESC LIMIT 50");
                        if ($result) {
                            while($row = $result->fetch_assoc()) {
                                $status_color = $row['status'] === 'SENT' ? 'green' : 'red';
                                echo "<tr>";
                                echo "<td>" . $row['id'] . "</td>";
                                echo "<td>" . htmlspecialchars($row['to_email'] ?? '') . "</td>";
                                echo "<td>" . htmlspecialchars(substr($row['subject'] ?? '', 0, 50)) . "...</td>";
                                echo "<td><span style='color: $status_color;'>" . htmlspecialchars($row['status'] ?? '') . "</span></td>";
                                echo "<td>" . htmlspecialchars($row['method'] ?? '') . "</td>";
                                echo "<td>" . date('d.m.Y H:i', strtotime($row['sent_at'])) . "</td>";
                                echo "</tr>";
                            }
                        }
                        ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </div>
    </div>

    <!-- Gizli Silme Formu -->
    <form id="deleteForm" method="POST" style="display: none;">
        <input type="hidden" name="delete_user" value="1">
        <input type="hidden" name="user_id" id="deleteUserId">
    </form>

    <!-- Gizli Doğrulama Formu -->
    <form id="verificationForm" method="POST" style="display: none;">
        <input type="hidden" name="toggle_verification" value="1">
        <input type="hidden" name="user_id" id="verifyUserId">
        <input type="hidden" name="new_status" id="newStatus">
    </form>

    <script>
        function deleteUser(userId, userName) {
            if (confirm('⚠️ UYARI!\n\n"' + userName + '" adlı kullanıcıyı silmek istediğinizden emin misiniz?\n\nBu işlem GERİ ALINAMAZ!')) {
                document.getElementById('deleteUserId').value = userId;
                document.getElementById('deleteForm').submit();
            }
        }

        function toggleVerification(userId, newStatus) {
            const action = newStatus ? 'aktifleştirmek' : 'deaktifleştirmek';
            if (confirm('Bu kullanıcıyı ' + action + ' istediğinizden emin misiniz?')) {
                document.getElementById('verifyUserId').value = userId;
                document.getElementById('newStatus').value = newStatus ? '1' : '0';
                document.getElementById('verificationForm').submit();
            }
        }
    </script>
</body>
</html>

<?php $conn->close(); ?>
