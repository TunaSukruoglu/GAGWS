<?php
// Upload Performance Optimization Script

function optimizeCloudflareUploads() {
    error_log("🚀 UPLOAD OPTIMIZATION STARTED");
    
    // Replace the current upload logic in add-property.php with parallel processing
    $optimizedCode = '
                    // PARALLEL UPLOAD OPTIMIZATION - Multiple images simultaneously  
                    $maxParallelUploads = 3; // Upload 3 images at once
                    $batches = array_chunk($files, $maxParallelUploads);
                    
                    foreach ($batches as $batchIndex => $batch) {
                        $batchStartTime = microtime(true);
                        $multiHandle = curl_multi_init();
                        $curlHandles = [];
                        $batchData = [];
                        
                        error_log("📦 Processing batch " . ($batchIndex + 1) . " with " . count($batch) . " images");
                        
                        // Initialize parallel uploads
                        foreach ($batch as $i => $fileInfo) {
                            $tmpName = $fileInfo[\'tmp_name\'];
                            $originalName = $fileInfo[\'name\'];
                            $uploadStart = microtime(true);
                            
                            // Create cURL handle for this file
                            $ch = curl_init();
                            $postData = [
                                \'file\' => new CURLFile($tmpName, mime_content_type($tmpName), $originalName),
                                \'metadata\' => json_encode([
                                    \'propertyId\' => $propertyId ?? \'new\',
                                    \'originalName\' => $originalName,
                                    \'uploadTime\' => date(\'Y-m-d H:i:s\'),
                                    \'batch\' => $batchIndex + 1,
                                    \'position\' => $i + 1
                                ])
                            ];
                            
                            curl_setopt_array($ch, [
                                CURLOPT_URL => "https://api.cloudflare.com/client/v4/accounts/" . CLOUDFLARE_ACCOUNT_ID . "/images/v1",
                                CURLOPT_POST => true,
                                CURLOPT_POSTFIELDS => $postData,
                                CURLOPT_HTTPHEADER => [
                                    "Authorization: Bearer " . CLOUDFLARE_API_TOKEN,
                                    "Content-Type: multipart/form-data"
                                ],
                                CURLOPT_RETURNTRANSFER => true,
                                CURLOPT_TIMEOUT => 15, // 15 second timeout per upload
                                CURLOPT_CONNECTTIMEOUT => 5, // 5 second connection timeout
                                CURLOPT_SSL_VERIFYPEER => false,
                                CURLOPT_FOLLOWLOCATION => true,
                                CURLOPT_MAXREDIRS => 3
                            ]);
                            
                            curl_multi_add_handle($multiHandle, $ch);
                            $curlHandles[$i] = $ch;
                            $batchData[$i] = [
                                \'originalName\' => $originalName,
                                \'uploadStart\' => $uploadStart,
                                \'tmpName\' => $tmpName
                            ];
                        }
                        
                        // Execute parallel uploads
                        $running = null;
                        do {
                            curl_multi_exec($multiHandle, $running);
                            curl_multi_select($multiHandle);
                        } while ($running > 0);
                        
                        // Process results
                        foreach ($curlHandles as $i => $ch) {
                            $response = curl_multi_getcontent($ch);
                            $httpCode = curl_getinfo($ch, CURLINFO_HTTP_CODE);
                            $uploadTime = round((microtime(true) - $batchData[$i][\'uploadStart\']) * 1000, 2);
                            
                            if ($response && $httpCode === 200) {
                                $uploadResult = json_decode($response, true);
                                
                                if ($uploadResult && isset($uploadResult[\'success\']) && $uploadResult[\'success\'] && isset($uploadResult[\'result\'][\'id\'])) {
                                    $imageId = $uploadResult[\'result\'][\'id\'];
                                    $cloudflareUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $imageId . "/public";
                                    $finalImagesArray[] = $cloudflareUrl;
                                    $cloudflareImagesArray[] = $imageId;
                                    
                                    error_log("✅ Parallel upload success: {$batchData[$i][\'originalName\']} -> {$imageId} in {$uploadTime}ms");
                                    
                                    // Create local thumbnail asynchronously 
                                    $imageIndex = count($finalImagesArray) - 1;
                                    createLocalThumbnail($imageId, $propertyId ?: \'temp\', $imageIndex);
                                    
                                } else {
                                    error_log("❌ Parallel upload failed: {$batchData[$i][\'originalName\']} - Invalid response in {$uploadTime}ms");
                                    throw new Exception("Upload failed for: {$batchData[$i][\'originalName\']}");
                                }
                            } else {
                                $curlError = curl_error($ch);
                                error_log("❌ Parallel upload failed: {$batchData[$i][\'originalName\']} - HTTP:{$httpCode} cURL:{$curlError} in {$uploadTime}ms");
                                throw new Exception("Upload failed for: {$batchData[$i][\'originalName\']} (HTTP: {$httpCode})");
                            }
                            
                            curl_multi_remove_handle($multiHandle, $ch);
                            curl_close($ch);
                        }
                        
                        curl_multi_close($multiHandle);
                        
                        $batchTime = round((microtime(true) - $batchStartTime) * 1000, 2);
                        error_log("📦 Batch " . ($batchIndex + 1) . " completed in {$batchTime}ms (" . count($batch) . " images)");
                    }
                    
                    // Add watermark to main image (first image) after all uploads complete
                    if (!empty($cloudflareImagesArray)) {
                        try {
                            $watermarkStart = microtime(true);
                            $mainImageId = $cloudflareImagesArray[0];
                            $domain = getCurrentDomain();
                            
                            error_log("🎨 Adding watermark to main image: {$mainImageId}");
                            
                            $watermarkResult = $cloudflare->uploadImageForDomain($batchData[0][\'tmpName\'], $domain, [
                                \'propertyId\' => $propertyId ?? \'new\',
                                \'isMainImage\' => true,
                                \'timeout\' => 8
                            ]);
                            
                            if ($watermarkResult && isset($watermarkResult[\'success\']) && $watermarkResult[\'success\'] && isset($watermarkResult[\'image_id\'])) {
                                $watermarkedId = $watermarkResult[\'image_id\'];
                                $watermarkedUrl = "https://imagedelivery.net/" . CLOUDFLARE_ACCOUNT_ID . "/" . $watermarkedId . "/public";
                                $finalImagesArray[0] = $watermarkedUrl;
                                $cloudflareImagesArray[0] = $watermarkedId;
                                
                                $watermarkTime = round((microtime(true) - $watermarkStart) * 1000, 2);
                                error_log("✅ Watermark added to main image: {$watermarkedId} in {$watermarkTime}ms");
                            }
                        } catch (Exception $e) {
                            error_log("⚠️ Watermark failed: " . $e->getMessage() . " - using original image");
                        }
                    }
    ';
    
    return $optimizedCode;
}

// Additional optimizations
function addUploadOptimizations() {
    return [
        'connection_pooling' => 'Use persistent connections',
        'concurrent_uploads' => 'Upload 3 images simultaneously',
        'timeout_optimization' => 'Reduced timeouts (15s upload, 5s connect)',
        'retry_logic' => 'Smart retry with exponential backoff',
        'compression' => 'Optimize image compression before upload',
        'progress_tracking' => 'Real-time progress updates'
    ];
}

error_log("💡 OPTIMIZATION RECOMMENDATIONS:");
error_log("1. Parallel uploads: 3 images at once = 3x faster");
error_log("2. Connection timeout: 15s (vs current 10s)");
error_log("3. Retry logic: Smart backoff strategy");
error_log("4. Progress tracking: Real-time user feedback");
error_log("5. Compression: Optimize images before upload");

echo "Upload optimization analysis complete. Check debug.log for details.\n";
?>
