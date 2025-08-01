<?php
/**
 * Database Migration Script
 * Local image referanslarını veritabanından temizler
 * Sadece Cloudflare images kalır
 */

error_reporting(E_ALL);
ini_set('display_errors', 1);

require_once 'db.php';

echo "<h1>🗃️ Database Local Images Clean-up Migration</h1>";

try {
    // Test bağlantısı
    if (!$conn) {
        throw new Exception("Database connection failed");
    }
    
    echo "<div style='background: #e3f2fd; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📋 Migration İşlemi Başlıyor</h3>";
    echo "<p><strong>Hedef:</strong> Veritabanındaki local image referanslarını temizle</p>";
    echo "<p><strong>Sonuç:</strong> Sadece Cloudflare image URLleri kalacak</p>";
    echo "</div>";
    
    // Önce mevcut durumu analiz et
    $analysis_query = "SELECT id, title, images, main_image FROM properties WHERE images IS NOT NULL AND images != '[]'";
    $analysis_result = $conn->query($analysis_query);
    
    $total_properties = 0;
    $properties_with_local = 0;
    $properties_with_cloudflare = 0;
    $mixed_properties = 0;
    
    $local_pattern = '/\.(jpg|jpeg|png|gif|webp)$/i';
    $cloudflare_pattern = '/https:\/\/imagedelivery\.net\//';
    $cloudflare_id_pattern = '/^[a-f0-9]{8}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{4}-[a-f0-9]{12}$/i';
    
    echo "<div style='background: #fff3e0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>🔍 Analiz Sonuçları</h3>";
    echo "<table border='1' style='width: 100%; border-collapse: collapse;'>";
    echo "<tr style='background: #ff9800; color: white;'>";
    echo "<th style='padding: 10px;'>Property ID</th>";
    echo "<th style='padding: 10px;'>Title</th>";
    echo "<th style='padding: 10px;'>Total Images</th>";
    echo "<th style='padding: 10px;'>Local Images</th>";
    echo "<th style='padding: 10px;'>Cloudflare Images</th>";
    echo "<th style='padding: 10px;'>Status</th>";
    echo "</tr>";
    
    if ($analysis_result->num_rows > 0) {
        while ($row = $analysis_result->fetch_assoc()) {
            $total_properties++;
            $images = json_decode($row['images'], true);
            
            if (!is_array($images)) {
                continue;
            }
            
            $local_count = 0;
            $cloudflare_count = 0;
            
            foreach ($images as $image) {
                if (preg_match($cloudflare_pattern, $image) || preg_match($cloudflare_id_pattern, $image)) {
                    $cloudflare_count++;
                } else if (preg_match($local_pattern, $image)) {
                    $local_count++;
                }
            }
            
            $status = '';
            if ($local_count > 0 && $cloudflare_count > 0) {
                $mixed_properties++;
                $status = 'MIXED';
                $bg_color = '#ffecb3';
            } else if ($local_count > 0) {
                $properties_with_local++;
                $status = 'LOCAL ONLY';
                $bg_color = '#ffcdd2';
            } else if ($cloudflare_count > 0) {
                $properties_with_cloudflare++;
                $status = 'CLOUDFLARE ONLY';
                $bg_color = '#c8e6c9';
            } else {
                $status = 'NO IMAGES';
                $bg_color = '#f5f5f5';
            }
            
            echo "<tr style='background: {$bg_color};'>";
            echo "<td style='padding: 8px;'>{$row['id']}</td>";
            echo "<td style='padding: 8px;'>" . htmlspecialchars(substr($row['title'], 0, 30)) . "...</td>";
            echo "<td style='padding: 8px;'>" . count($images) . "</td>";
            echo "<td style='padding: 8px;'>{$local_count}</td>";
            echo "<td style='padding: 8px;'>{$cloudflare_count}</td>";
            echo "<td style='padding: 8px;'><strong>{$status}</strong></td>";
            echo "</tr>";
        }
    }
    
    echo "</table>";
    echo "</div>";
    
    // Özet
    echo "<div style='background: #e8f5e8; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>📊 Migration Özeti</h3>";
    echo "<ul>";
    echo "<li><strong>Toplam Property:</strong> {$total_properties}</li>";
    echo "<li><strong>Sadece Local:</strong> {$properties_with_local}</li>";
    echo "<li><strong>Sadece Cloudflare:</strong> {$properties_with_cloudflare}</li>";
    echo "<li><strong>Karışık (Local + CF):</strong> {$mixed_properties}</li>";
    echo "</ul>";
    echo "</div>";
    
    // Migration işlemi başlat
    if (isset($_GET['execute']) && $_GET['execute'] === 'true') {
        echo "<div style='background: #fce4ec; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>🚀 Migration İşlemi Başladı</h3>";
        
        $updated_count = 0;
        $cleaned_images_count = 0;
        
        // Her property için local images'ları temizle
        $cleanup_query = "SELECT id, images, main_image FROM properties WHERE images IS NOT NULL AND images != '[]'";
        $cleanup_result = $conn->query($cleanup_query);
        
        if ($cleanup_result->num_rows > 0) {
            while ($row = $cleanup_result->fetch_assoc()) {
                $images = json_decode($row['images'], true);
                
                if (!is_array($images)) {
                    continue;
                }
                
                // Sadece Cloudflare images'ları filtrele
                $cloudflare_images = array_filter($images, function($image) use ($cloudflare_pattern, $cloudflare_id_pattern) {
                    return preg_match($cloudflare_pattern, $image) || preg_match($cloudflare_id_pattern, $image);
                });
                
                // Array indexlerini yeniden düzenle
                $cloudflare_images = array_values($cloudflare_images);
                $original_count = count($images);
                $new_count = count($cloudflare_images);
                
                if ($original_count !== $new_count) {
                    // Main image kontrolü
                    $main_image = $row['main_image'];
                    $new_main_image = '';
                    
                    if (!empty($cloudflare_images)) {
                        // Eğer main_image Cloudflare değilse, ilk Cloudflare image'ı main yap
                        if (preg_match($cloudflare_pattern, $main_image) || preg_match($cloudflare_id_pattern, $main_image)) {
                            $new_main_image = $main_image;
                        } else {
                            $new_main_image = $cloudflare_images[0];
                        }
                    }
                    
                    // Database'i güncelle
                    $update_query = "UPDATE properties SET images = ?, main_image = ? WHERE id = ?";
                    $stmt = $conn->prepare($update_query);
                    $images_json = json_encode($cloudflare_images);
                    $stmt->bind_param("ssi", $images_json, $new_main_image, $row['id']);
                    
                    if ($stmt->execute()) {
                        $updated_count++;
                        $cleaned_images_count += ($original_count - $new_count);
                        echo "<p>✅ Property ID {$row['id']}: {$original_count} → {$new_count} images</p>";
                    } else {
                        echo "<p>❌ Property ID {$row['id']}: Update failed</p>";
                    }
                }
            }
        }
        
        echo "<h4>🎉 Migration Tamamlandı!</h4>";
        echo "<p><strong>Güncellenen Property Sayısı:</strong> {$updated_count}</p>";
        echo "<p><strong>Temizlenen Local Image Sayısı:</strong> {$cleaned_images_count}</p>";
        echo "</div>";
        
    } else {
        echo "<div style='background: #fff3e0; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
        echo "<h3>⚠️ Migration Onayı</h3>";
        echo "<p>Bu işlem veritabanındaki local image referanslarını <strong>kalıcı olarak</strong> silecektir.</p>";
        echo "<p>Sadece Cloudflare image URLleri kalacaktır.</p>";
        echo "<p><strong>Devam etmek istiyor musunuz?</strong></p>";
        echo "<a href='?execute=true' style='background: #f44336; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px; margin-right: 10px;'>✅ Evet, Migration'ı Başlat</a>";
        echo "<a href='?' style='background: #757575; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>❌ İptal Et</a>";
        echo "</div>";
    }
    
} catch (Exception $e) {
    echo "<div style='background: #ffebee; color: #c62828; padding: 20px; border-radius: 10px; margin: 20px 0;'>";
    echo "<h3>❌ Hata</h3>";
    echo "<p>{$e->getMessage()}</p>";
    echo "</div>";
}

echo "<p><a href='dashboard/add-property.php' style='background: #2196f3; color: white; padding: 10px 20px; text-decoration: none; border-radius: 5px;'>🏠 İlan Ekleme Sayfasına Git</a></p>";
?>
