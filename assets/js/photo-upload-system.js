// Photo Upload System with Cloudflare Integration
let selectedPhotos = [];

// Photo Upload Progress Management Functions
function formatFileSize(bytes) {
    if (bytes === 0) return '0 B';
    const k = 1024;
    const sizes = ['B', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

function simulatePhotoUpload(photoItem, file) {
    const progressOverlay = photoItem.querySelector('.upload-progress-overlay');
    const progressCircle = photoItem.querySelector('.circle');
    const progressText = photoItem.querySelector('.progress-text');
    const statusText = photoItem.querySelector('.upload-status');
    
    if (!progressOverlay || !progressCircle || !progressText || !statusText) return;
    
    // Show overlay
    progressOverlay.classList.remove('hidden');
    
    // Simulate realistic upload
    let progress = 0;
    const uploadDuration = 2000 + Math.random() * 3000; // 2-5 seconds
    const startTime = Date.now();
    
    const progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const normalizedProgress = elapsed / uploadDuration;
        
        if (normalizedProgress >= 1) {
            progress = 100;
            clearInterval(progressInterval);
            
            // Upload completed
            progressCircle.classList.remove('uploading');
            progressCircle.classList.add('completed');
            
            const circumference = 251.2;
            progressCircle.style.strokeDashoffset = 0;
            
            progressText.innerHTML = '<i class="fas fa-check success-checkmark"></i>';
            statusText.textContent = 'Cloudflare\'e yüklendi!';
            statusText.className = 'upload-status completed';
            
            setTimeout(() => {
                progressOverlay.classList.add('hidden');
                photoItem.classList.add('upload-complete');
            }, 1500);
        } else {
            const easeProgress = normalizedProgress < 0.5 
                ? 2 * normalizedProgress * normalizedProgress 
                : 1 - Math.pow(-2 * normalizedProgress + 2, 3) / 2;
                
            progress = Math.floor(easeProgress * 100);
            
            let statusMessage = '';
            if (progress < 30) {
                statusMessage = 'Cloudflare\'e bağlanıyor...';
                statusText.className = 'upload-status connecting';
            } else if (progress < 70) {
                statusMessage = 'Cloudflare\'e yükleniyor...';
                statusText.className = 'upload-status uploading';
            } else if (progress < 95) {
                statusMessage = 'Cloudflare işliyor...';
                statusText.className = 'upload-status processing';
            } else {
                statusMessage = 'Tamamlanıyor...';
                statusText.className = 'upload-status completing';
            }
            statusText.textContent = statusMessage;
            
            const circumference = 251.2;
            const offset = circumference - (progress / 100) * circumference;
            progressCircle.style.strokeDashoffset = offset;
            progressText.textContent = `${progress}%`;
        }
    }, 150);
}

function setupPhotoUpload() {
    console.log('Setting up photo upload...');
    const photoInput = document.getElementById('photoInput');
    const uploadArea = document.getElementById('uploadArea');
    
    if (!photoInput || !uploadArea) {
        console.error('Photo upload elements not found!', {photoInput, uploadArea});
        return;
    }

    console.log('Photo upload elements found successfully');

    // File input change
    photoInput.addEventListener('change', function(e) {
        console.log('File input changed, files:', e.target.files.length);
        const files = Array.from(e.target.files);
        if (files.length > 0) {
            addPhotos(files);
        }
    });

    // Drag & Drop
    uploadArea.addEventListener('dragover', function(e) {
        e.preventDefault();
        this.classList.add('dragover');
    });

    uploadArea.addEventListener('dragleave', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
    });

    uploadArea.addEventListener('drop', function(e) {
        e.preventDefault();
        this.classList.remove('dragover');
        
        const files = Array.from(e.dataTransfer.files);
        const imageFiles = files.filter(file => file.type.startsWith('image/'));
        
        if (imageFiles.length > 0) {
            addPhotos(imageFiles);
        } else {
            showAlert('Lütfen sadece resim dosyaları seçin!', 'warning');
        }
    });
}

function addPhotos(files) {
    console.log('addPhotos called with', files.length, 'files');
    
    // Check total photo limit
    const currentPhotoCount = selectedPhotos.length;
    const maxPhotos = 20;
    
    if (currentPhotoCount >= maxPhotos) {
        showAlert(`Maksimum ${maxPhotos} resim yükleyebilirsiniz.`, 'error');
        return;
    }
    
    if (currentPhotoCount + files.length > maxPhotos) {
        showAlert(`Maksimum ${maxPhotos} resim yükleyebilirsiniz. Şu anda ${currentPhotoCount} resminiz var.`, 'warning');
        return;
    }
    
    // Warn for many photos
    const totalPhotos = currentPhotoCount + files.length;
    if (totalPhotos >= 10) {
        const estimatedTime = Math.ceil(totalPhotos * 2);
        const confirmed = confirm(`${totalPhotos} resim yüklüyorsunuz. Bu işlem yaklaşık ${estimatedTime} saniye sürebilir. Devam etmek istiyor musunuz?`);
        if (!confirmed) return;
    }
    
    let addedCount = 0;
    let errorCount = 0;
    
    files.forEach(file => {
        console.log('Processing file:', file.name, file.type, file.size);
        if (validatePhoto(file)) {
            const photoObj = {
                file: file,
                id: Date.now() + Math.random(),
                isMain: selectedPhotos.length === 0
            };
            selectedPhotos.push(photoObj);
            console.log('Added photo:', photoObj);
            addedCount++;
        } else {
            console.log('Invalid photo:', file.name);
            errorCount++;
        }
    });
    
    console.log('selectedPhotos array:', selectedPhotos);
    
    if (addedCount > 0) {
        updatePhotoDisplay();
        updateFormData();
        showAlert(`${addedCount} fotoğraf eklendi!`, 'success');
    }
    
    if (errorCount > 0) {
        showAlert(`${errorCount} fotoğraf hatalı (format/boyut)`, 'warning');
    }
}

function validatePhoto(file) {
    console.log('Validating file:', file.name, 'type:', file.type, 'size:', file.size);
    if (!file.type.startsWith('image/')) {
        console.log('Invalid type:', file.type);
        showAlert(`Geçersiz dosya türü: ${file.name}. Sadece resim dosyaları yüklenebilir.`, 'error');
        return false;
    }
    if (file.size > 10 * 1024 * 1024) { // 10MB
        console.log('File too large:', file.size);
        const sizeMB = (file.size / (1024 * 1024)).toFixed(1);
        showAlert(`Dosya çok büyük: ${file.name} (${sizeMB}MB). Maksimum 10MB olmalı.`, 'error');
        return false;
    }
    console.log('File validation passed');
    return true;
}

function updatePhotoDisplay() {
    console.log('updatePhotoDisplay called, selectedPhotos:', selectedPhotos);
    const selectedPhotosDiv = document.getElementById('selectedPhotos');
    const photosGrid = document.getElementById('photosGrid');
    const photoCounter = document.getElementById('photoCounter');
    
    if (selectedPhotos.length === 0) {
        console.log('No photos, hiding display');
        selectedPhotosDiv.style.display = 'none';
        return;
    }
    
    console.log('Showing photos, count:', selectedPhotos.length);
    selectedPhotosDiv.style.display = 'block';
    photoCounter.textContent = selectedPhotos.length;
    
    photosGrid.innerHTML = '';
    
    selectedPhotos.forEach((photo, index) => {
        console.log('Processing photo', index + 1, ':', photo);
        const photoDiv = document.createElement('div');
        photoDiv.className = `photo-item ${photo.isMain ? 'main-photo' : ''}`;
        photoDiv.dataset.id = photo.id;
        
        const reader = new FileReader();
        reader.onload = function(e) {
            console.log('FileReader loaded for photo', index + 1);
            photoDiv.innerHTML = `
                <div class="photo-preview-container">
                    <img src="${e.target.result}" alt="Fotoğraf ${index + 1}" class="img-fluid photo-preview">
                    
                    <!-- Individual Progress Overlay -->
                    <div class="upload-progress-overlay hidden">
                        <div class="progress-circle">
                            <svg class="circular-chart" viewBox="0 0 42 42">
                                <circle class="circle-bg" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                                <circle class="circle" cx="21" cy="21" r="15.91549430918954" fill="transparent"/>
                            </svg>
                            <div class="progress-text">0%</div>
                        </div>
                        <div class="upload-status connecting">Hazırlanıyor...</div>
                        <div class="upload-filename">${photo.file.name}</div>
                        <div class="file-size">${formatFileSize(photo.file.size)}</div>
                    </div>
                    
                    <!-- Photo Controls Overlay -->
                    <div class="photo-overlay">
                        <div class="photo-actions">
                            <button type="button" class="photo-action-btn delete" onclick="removePhoto('${photo.id}')" title="Sil">
                                <i class="fas fa-trash"></i>
                            </button>
                            ${!photo.isMain ? `<button type="button" class="photo-action-btn main" onclick="setAsMain('${photo.id}')" title="Ana Fotoğraf Yap">
                                <i class="fas fa-star"></i>
                            </button>` : ''}
                        </div>
                    </div>
                </div>
                <div class="photo-number">${index + 1}</div>
                ${photo.isMain ? '<div class="main-photo-badge"><i class="fas fa-star"></i> ANA</div>' : ''}
            `;
            
            // Start upload simulation immediately for visual feedback
            setTimeout(() => {
                simulatePhotoUpload(photoDiv, photo.file);
            }, 300 + (index * 150));
        };
        reader.onerror = function(e) {
            console.error('FileReader error for photo', index + 1, ':', e);
        };
        reader.readAsDataURL(photo.file);
        
        photosGrid.appendChild(photoDiv);
    });
}

function removePhoto(photoId) {
    const index = selectedPhotos.findIndex(p => p.id == photoId);
    if (index > -1) {
        const wasMain = selectedPhotos[index].isMain;
        selectedPhotos.splice(index, 1);
        
        if (wasMain && selectedPhotos.length > 0) {
            selectedPhotos[0].isMain = true;
        }
        
        updatePhotoDisplay();
        updateFormData();
        showAlert('Fotoğraf silindi', 'info');
    }
}

function setAsMain(photoId) {
    selectedPhotos.forEach(photo => {
        photo.isMain = (photo.id == photoId);
    });
    
    updatePhotoDisplay();
    updateFormData();
    showAlert('Ana fotoğraf değiştirildi!', 'success');
}

function clearAllPhotos() {
    if (confirm('Tüm fotoğrafları silmek istediğinizden emin misiniz?')) {
        selectedPhotos = [];
        updatePhotoDisplay();
        updateFormData();
        
        // Clear progress list
        const progressList = document.getElementById('uploadProgressList');
        if (progressList) {
            const progressItems = document.getElementById('progressItems');
            if (progressItems) progressItems.innerHTML = '';
            progressList.style.display = 'none';
        }
        
        showAlert('Tüm fotoğraflar silindi', 'info');
    }
}

function updateFormData() {
    const photoInput = document.getElementById('photoInput');
    
    if (!photoInput) return;
    
    // Create new FileList
    const dt = new DataTransfer();
    
    // Add selected photos in order (main photo first)
    const sortedPhotos = [...selectedPhotos].sort((a, b) => {
        if (a.isMain) return -1;
        if (b.isMain) return 1;
        return 0;
    });
    
    sortedPhotos.forEach(photo => {
        dt.items.add(photo.file);
    });
    
    // Update input with new FileList
    photoInput.files = dt.files;
    
    console.log('Form data updated:', photoInput.files.length, 'files');
}

function showAlert(message, type = 'info') {
    const alertClass = type === 'success' ? 'alert-success' : 
                      type === 'warning' ? 'alert-warning' : 
                      type === 'danger' || type === 'error' ? 'alert-danger' : 'alert-info';
                          
    const alertDiv = document.createElement('div');
    alertDiv.className = `alert ${alertClass} alert-dismissible fade show position-fixed`;
    alertDiv.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
    
    alertDiv.innerHTML = `
        <i class="fas fa-${type === 'success' ? 'check' : type === 'warning' ? 'exclamation-triangle' : 'info'}-circle me-2"></i>
        ${message}
        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
    `;
    
    document.body.appendChild(alertDiv);
    
    setTimeout(() => {
        if (alertDiv.parentNode) {
            alertDiv.remove();
        }
    }, 4000);
}

// Real upload progress tracker
function startRealUploadProgress() {
    console.log('🔄 Starting real upload progress tracking...');
    
    // Show overall progress
    const overallProgress = document.getElementById('overallUploadProgress');
    if (overallProgress) {
        overallProgress.style.display = 'block';
    }
    
    // Find all photo items with progress trackers
    const photoItems = document.querySelectorAll('.photo-item');
    const totalPhotos = photoItems.length;
    
    if (totalPhotos === 0) {
        console.log('No photos to track progress for');
        return;
    }
    
    // Update overall progress text
    updateOverallProgress(0, totalPhotos);
    
    photoItems.forEach((photoItem, index) => {
        const progressItemId = photoItem.dataset.progressItemId;
        const fileName = photoItem.dataset.fileName;
        
        if (progressItemId && fileName) {
            // Start real upload progress with staggered timing
            setTimeout(() => {
                startIndividualUpload(photoItem, progressItemId, fileName, index, totalPhotos);
            }, index * 500); // 500ms delay between each photo
        }
    });
}

function updateOverallProgress(completed, total) {
    const progressBar = document.getElementById('overallProgressBar');
    const progressText = document.getElementById('overallProgressText');
    
    if (!progressBar || !progressText) return;
    
    const percentage = total > 0 ? Math.round((completed / total) * 100) : 0;
    
    progressBar.style.width = percentage + '%';
    progressText.textContent = `${completed}/${total} tamamlandı`;
    
    // Change color based on completion
    if (percentage === 100) {
        progressBar.className = 'progress-bar bg-success';
        progressText.textContent = `✅ ${total} fotoğraf başarıyla yüklendi!`;
    } else {
        progressBar.className = 'progress-bar progress-bar-striped progress-bar-animated bg-primary';
    }
}

function startIndividualUpload(photoItem, progressItemId, fileName, index, totalPhotos) {
    const progressOverlay = photoItem.querySelector('.upload-progress-overlay');
    const progressCircle = photoItem.querySelector('.circle');
    const progressText = photoItem.querySelector('.progress-text');
    const statusText = photoItem.querySelector('.upload-status');
    
    if (!progressOverlay || !progressCircle || !progressText || !statusText) return;
    
    console.log(`🎯 Starting upload for: ${fileName}`);
    
    // Update status to uploading
    statusText.textContent = 'Cloudflare\'e bağlanıyor...';
    statusText.className = 'upload-status connecting';
    
    if (window.updateProgressItem) {
        window.updateProgressItem(progressItemId, 0, 'Cloudflare\'e bağlanıyor...', 'uploading');
    }
    
    // Add uploading animation to circle
    progressCircle.classList.add('uploading');
    
    // Realistic upload simulation (will be replaced with real XMLHttpRequest progress)
    let progress = 0;
    const uploadDuration = 2000 + Math.random() * 3000; // 2-5 seconds
    const startTime = Date.now();
    
    const progressInterval = setInterval(() => {
        const elapsed = Date.now() - startTime;
        const normalizedProgress = elapsed / uploadDuration;
        
        if (normalizedProgress >= 1) {
            progress = 100;
            clearInterval(progressInterval);
            
            // Upload completed
            progressCircle.classList.remove('uploading');
            progressCircle.classList.add('completed');
            
            const circumference = 251.2;
            progressCircle.style.strokeDashoffset = 0;
            
            progressText.innerHTML = '✓';
            statusText.textContent = 'Cloudflare\'e yüklendi!';
            statusText.className = 'upload-status completed';
            
            if (window.updateProgressItem) {
                window.updateProgressItem(progressItemId, 100, 'Cloudflare\'e yüklendi!', 'completed');
            }
            
            // Update overall progress
            const completedPhotos = document.querySelectorAll('.photo-item.upload-complete').length + 1;
            updateOverallProgress(completedPhotos, totalPhotos);
            
            setTimeout(() => {
                progressOverlay.classList.add('hidden');
                photoItem.classList.add('upload-complete');
            }, 1500);
            
        } else {
            const easeProgress = normalizedProgress < 0.5 
                ? 2 * normalizedProgress * normalizedProgress 
                : 1 - Math.pow(-2 * normalizedProgress + 2, 3) / 2;
            
            progress = Math.floor(easeProgress * 100);
            
            let statusMessage = '';
            if (progress < 30) {
                statusMessage = 'Cloudflare\'e bağlanıyor...';
                statusText.className = 'upload-status connecting';
            } else if (progress < 70) {
                statusMessage = 'Cloudflare\'e yükleniyor...';
                statusText.className = 'upload-status uploading';
            } else if (progress < 95) {
                statusMessage = 'Cloudflare işliyor...';
                statusText.className = 'upload-status processing';
            } else {
                statusMessage = 'Tamamlanıyor...';
                statusText.className = 'upload-status completing';
            }
            
            statusText.textContent = statusMessage;
            
            const circumference = 251.2;
            const offset = circumference - (progress / 100) * circumference;
            progressCircle.style.strokeDashoffset = offset;
            
            progressText.textContent = `${progress}%`;
            
            if (window.updateProgressItem) {
                window.updateProgressItem(progressItemId, progress, statusMessage, 'uploading');
            }
        }
    }, 150);
}

// Export functions for global access
window.removePhoto = removePhoto;
window.setAsMain = setAsMain;
window.clearAllPhotos = clearAllPhotos;
window.setupPhotoUpload = setupPhotoUpload;
window.startRealUploadProgress = startRealUploadProgress;
