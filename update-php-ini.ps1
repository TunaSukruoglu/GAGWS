# PHP.ini Update Script
$phpIniPath = "C:\xampp\php\php.ini"

# Backup al
Copy-Item $phpIniPath "$phpIniPath.backup_$(Get-Date -Format 'yyyyMMdd_HHmmss')"

# Dosyayı oku
$content = Get-Content $phpIniPath

# Ayarları güncelle
for ($i = 0; $i -lt $content.Length; $i++) {
    if ($content[$i] -match "^post_max_size") {
        $content[$i] = "post_max_size=1G"
        Write-Host "Updated: post_max_size=1G"
    }
    elseif ($content[$i] -match "^upload_max_filesize") {
        $content[$i] = "upload_max_filesize=500M"
        Write-Host "Updated: upload_max_filesize=500M"
    }
    elseif ($content[$i] -match "^max_file_uploads") {
        $content[$i] = "max_file_uploads=50"
        Write-Host "Updated: max_file_uploads=50"
    }
    elseif ($content[$i] -match "^;max_input_vars") {
        $content[$i] = "max_input_vars=10000"
        Write-Host "Updated: max_input_vars=10000"
    }
    elseif ($content[$i] -match "^max_execution_time") {
        $content[$i] = "max_execution_time=1800"
        Write-Host "Updated: max_execution_time=1800"
    }
    elseif ($content[$i] -match "^max_input_time") {
        $content[$i] = "max_input_time=1800"
        Write-Host "Updated: max_input_time=1800"
    }
    elseif ($content[$i] -match "^memory_limit") {
        $content[$i] = "memory_limit=1G"
        Write-Host "Updated: memory_limit=1G"
    }
}

# Dosyayı kaydet
$content | Out-File -FilePath $phpIniPath -Encoding UTF8

Write-Host "PHP.ini updated successfully!"
Write-Host "Please restart Apache/XAMPP to apply changes."
