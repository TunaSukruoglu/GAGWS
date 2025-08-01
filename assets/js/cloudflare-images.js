// Cloudflare Image Management and Preview Functions
// Enhanced Cloudflare Images Integration

// Existing Photo Management Functions - CLOUDFLARE ONLY
let existingPhotosData = [];

// Initialize existing photos data from PHP
document.addEventListener('DOMContentLoaded', function() {
    // Wait for existing photos data to be set from PHP
    setTimeout(() => {
        if (window.phpExistingImages) {
            existingPhotosData = window.phpExistingImages;
            console.log('Cloudflare-only images loaded:', existingPhotosData);
        }
    }, 100);
});

function makeMainPhoto(photoIndex) {
    console.log('Making Cloudflare photo main:', photoIndex);
    
    if (confirm('Bu Cloudflare fotoğrafını ana fotoğraf yapmak istediğinize emin misiniz?')) {
        // Move selected photo to index 0
        const photoToMoveToMain = existingPhotosData[photoIndex];
        existingPhotosData.splice(photoIndex, 1); // Remove from current position
        existingPhotosData.unshift(photoToMoveToMain); // Add to beginning
        
        updateExistingPhotosDisplay();
        
        // Show success message
        if (window.showAlert) {
            window.showAlert('Ana fotoğraf başarıyla değiştirildi!', 'success');
        }
    }
}

function removeExistingPhoto(photoIndex) {
    console.log('Removing photo:', photoIndex);
    
    if (confirm('Bu fotoğrafı kalıcı olarak kaldırmak istediğinize emin misiniz?')) {
        // Remove photo from array
        existingPhotosData.splice(photoIndex, 1);
        
        updateExistingPhotosDisplay();
        
        // Show success message
        if (window.showAlert) {
            window.showAlert('Fotoğraf başarıyla kaldırıldı!', 'success');
        }
    }
}

function updateExistingPhotosDisplay() {
    const existingPhotosGrid = document.querySelector('.existing-photos-grid');
    if (!existingPhotosGrid) return;
    
    // Clear current display
    existingPhotosGrid.innerHTML = '';
    
    if (existingPhotosData.length === 0) {
        existingPhotosGrid.parentElement.style.display = 'none';
        return;
    }
    
    // Rebuild photo items
    existingPhotosData.forEach((image, index) => {
        const photoItem = document.createElement('div');
        photoItem.className = 'existing-photo-item';
        photoItem.setAttribute('data-image', image);
        photoItem.setAttribute('data-index', index);
        
        // Generate Cloudflare thumbnail URL for display - FIXED ACCOUNT ID
        const cloudflareThumbUrl = `https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/${image}/thumbnail`;
        
        photoItem.innerHTML = `
            <img src="${cloudflareThumbUrl}" 
                 alt="Mevcut fotoğraf ${index + 1}" 
                 class="img-fluid">
            
            <div class="photo-controls">
                ${index !== 0 ? `
                    <button type="button" class="btn-make-main" onclick="makeMainPhoto(${index})" title="Ana resim yap">
                        <i class="fas fa-star"></i>
                    </button>
                ` : ''}
                <button type="button" class="btn-remove-photo" onclick="removeExistingPhoto(${index})" title="Resmi kaldır">
                    <i class="fas fa-times"></i>
                </button>
            </div>
            
            ${index === 0 ? `
                <div class="main-photo-badge">
                    <i class="fas fa-star"></i> ANA
                </div>
            ` : ''}
            <div class="photo-number">${index + 1}</div>
            <div class="cloudflare-badge">CF</div>
        `;
        
        existingPhotosGrid.appendChild(photoItem);
    });
    
    // Update the count badge
    const countBadge = document.querySelector('.existing-photos-grid').parentElement.querySelector('.badge');
    if (countBadge) {
        countBadge.textContent = existingPhotosData.length;
    }
}

// Cloudflare Image Preview Function
function previewCloudflareImage(cloudflareId, imageNumber) {
    // Create modal backdrop
    const backdrop = document.createElement('div');
    backdrop.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.8);
        z-index: 10000;
        display: flex;
        align-items: center;
        justify-content: center;
        cursor: pointer;
    `;
    
    // Create modal content
    const modal = document.createElement('div');
    modal.style.cssText = `
        background: white;
        padding: 20px;
        border-radius: 10px;
        max-width: 90%;
        max-height: 90%;
        position: relative;
        box-shadow: 0 10px 30px rgba(0,0,0,0.3);
    `;
    
    // Try different Cloudflare URL variants with proper account ID
    const urlVariants = [
        `https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/${cloudflareId}/public`,
        `https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/${cloudflareId}`,
        `https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/${cloudflareId}/original`
    ];
    
    let currentVariantIndex = 0;
    
    modal.innerHTML = `
        <div class="text-center">
            <h5 class="mb-3">Resim #${imageNumber} Önizleme</h5>
            <div id="imageContainer" style="min-height: 200px; display: flex; align-items: center; justify-content: center;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Yükleniyor...</span>
                </div>
            </div>
            <div class="mt-3">
                <small class="text-muted">Cloudflare ID: ${cloudflareId}</small><br>
                <small class="text-muted" id="statusText">Resim yükleniyor...</small>
            </div>
            <button class="btn btn-secondary mt-3" onclick="this.closest('.modal-backdrop').remove()">Kapat</button>
        </div>
    `;
    
    backdrop.appendChild(modal);
    backdrop.className = 'modal-backdrop';
    document.body.appendChild(backdrop);
    
    // Close on backdrop click
    backdrop.addEventListener('click', (e) => {
        if (e.target === backdrop) {
            backdrop.remove();
        }
    });
    
    // Try to load image
    function tryLoadImage() {
        if (currentVariantIndex >= urlVariants.length) {
            // All variants failed, show error
            document.getElementById('imageContainer').innerHTML = `
                <div class="text-center text-muted">
                    <i class="fas fa-exclamation-triangle fa-3x mb-3"></i>
                    <h6>Resim Yüklenemedi</h6>
                    <p>Cloudflare delivery sorunu nedeniyle resim önizlemesi açılamıyor.</p>
                    <small>ID: ${cloudflareId}</small>
                </div>
            `;
            document.getElementById('statusText').textContent = 'Cloudflare delivery hatası';
            return;
        }
        
        const img = new Image();
        const currentUrl = urlVariants[currentVariantIndex];
        
        img.onload = function() {
            // Success! Show the image
            document.getElementById('imageContainer').innerHTML = `
                <img src="${currentUrl}" style="max-width: 100%; max-height: 70vh; border-radius: 5px;" alt="Resim #${imageNumber}">
            `;
            document.getElementById('statusText').textContent = `Başarıyla yüklendi (Variant ${currentVariantIndex + 1})`;
        };
        
        img.onerror = function() {
            // Try next variant
            currentVariantIndex++;
            document.getElementById('statusText').textContent = `Variant ${currentVariantIndex} deneniyor...`;
            setTimeout(tryLoadImage, 500);
        };
        
        img.src = currentUrl;
    }
    
    // Start loading
    setTimeout(tryLoadImage, 100);
}

// Make functions globally available
window.makeMainPhoto = makeMainPhoto;
window.removeExistingPhoto = removeExistingPhoto;
window.updateExistingPhotosDisplay = updateExistingPhotosDisplay;
window.previewCloudflareImage = previewCloudflareImage;
window.existingPhotosData = existingPhotosData;
