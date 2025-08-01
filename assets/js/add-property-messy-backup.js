// Add Property Form - Main JavaScript Module
// Enhanced Property Upload System with Cloudflare Integration

// Format file size helper function
function formatFileSize(bytes) {
    if (bytes === 0) return '0 Bytes';
    const k = 1024;
    const sizes = ['Bytes', 'KB', 'MB', 'GB'];
    const i = Math.floor(Math.log(bytes) / Math.log(k));
    return parseFloat((bytes / Math.pow(k, i)).toFixed(1)) + ' ' + sizes[i];
}

// Progress List Management Functions
function addProgressItem(file, photoItem) {
    const progressList = document.getElementById('uploadProgressList');
    const progressItems = document.getElementById('progressItems');
    
    if (!progressList || !progressItems) return null;
    
    // Show progress list
    progressList.style.display = 'block';
    
    // Create unique ID
    const itemId = 'progress-' + Date.now() + Math.random();
    
    // Get image preview from photoItem
    const imgSrc = photoItem.querySelector('.photo-preview')?.src;
    
    const progressItem = document.createElement('div');
    progressItem.className = 'progress-item uploading';
    progressItem.id = itemId;
    
    progressItem.innerHTML = `
        <div class="progress-item-info">
            <img src="${imgSrc || ''}" alt="Preview" class="progress-item-thumb">
            <div class="progress-item-details">
                <div class="progress-item-name">${file.name}</div>
                <div class="progress-item-size">${formatFileSize(file.size)}</div>
            </div>
        </div>
        <div class="progress-item-status">
            <div class="progress-item-percentage">0%</div>
            <i class="fas fa-spinner fa-spin progress-item-icon"></i>
        </div>
    `;
    
    progressItems.appendChild(progressItem);
    return itemId;
}

function updateProgressItem(itemId, percentage, statusText, statusType) {
    const item = document.getElementById(itemId);
    if (!item) return;
    
    const percentageEl = item.querySelector('.progress-item-percentage');
    const iconEl = item.querySelector('.progress-item-icon');
    
    // Update percentage
    if (percentageEl) percentageEl.textContent = percentage + '%';
    
    // Update class
    item.className = `progress-item ${statusType}`;
    
    // Update icon based on status
    if (iconEl) {
        if (statusType === 'completed') {
            iconEl.className = 'fas fa-check progress-item-icon';
        } else if (statusType === 'error') {
            iconEl.className = 'fas fa-times progress-item-icon';
        } else if (statusType === 'waiting') {
            iconEl.className = 'fas fa-clock progress-item-icon';
        } else {
            iconEl.className = 'fas fa-spinner fa-spin progress-item-icon';
        }
    }
}



// Form submit event listener
document.addEventListener('DOMContentLoaded', function() {
    console.log('Add Property module loaded');
    
    // Initialize photo upload system
    if (window.setupPhotoUpload) {
        window.setupPhotoUpload();
    }
    
    // Initialize form handlers
    if (window.setupFormHandlers) {
        window.setupFormHandlers();
    }
    
    // Form submit handler
    const form = document.getElementById('propertyForm');
    if (form) {
        form.addEventListener('submit', function(e) {
            console.log('Form submitting...');
            
            // Update existing images if available
            const updatedExistingImagesInput = document.getElementById('updatedExistingImages');
            if (updatedExistingImagesInput && window.existingPhotosData) {
                updatedExistingImagesInput.value = JSON.stringify(window.existingPhotosData);
            }
            
            // Handle photo upload progress
            const photoInput = document.getElementById('photoInput');
            const photoCount = photoInput?.files?.length || 0;
            
            if (photoCount > 0) {
                console.log(`Starting upload process: ${photoCount} photos`);
                
                // Start upload progress tracking if available
                if (window.startRealUploadProgress) {
                    window.startRealUploadProgress();
                }
            }
            
            // Warning if no photos
            if (photoCount === 0 && (!window.existingPhotosData || window.existingPhotosData.length === 0)) {
                const confirmSubmit = confirm('Hiç fotoğraf yok. Devam etmek istiyor musunuz?');
                if (!confirmSubmit) {
                    e.preventDefault();
                    return false;
                }
            }
        });
    }
});

// Export functions for global access
window.addProgressItem = addProgressItem;
window.updateProgressItem = updateProgressItem;
window.formatFileSize = formatFileSize;

// Export functions for global access
window.addProgressItem = addProgressItem;
window.updateProgressItem = updateProgressItem;
window.formatFileSize = formatFileSize;
