// 🚀 EXTERNAL ULTRA FAST ADD PROPERTY FUNCTIONS

console.log('⚙️ External JS loaded');

// Form validation and submission
document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('externalPropertyForm');
    const submitBtn = document.getElementById('submitBtn');
    const formLoading = document.getElementById('formLoading');
    
    if (form) {
        // Form submission handler
        form.addEventListener('submit', function(e) {
            const title = document.querySelector('[name="title"]').value;
            const price = document.querySelector('[name="price"]').value;
            const city = document.querySelector('[name="city"]').value;
            const district = document.querySelector('[name="district"]').value;
            const propertyType = document.querySelector('[name="property_type"]').value;
            const listingType = document.querySelector('[name="listing_type"]').value;
            
            // Validation
            if (!title || !price || !city || !district || !propertyType || !listingType) {
                e.preventDefault();
                showMessage('⚠️ Lütfen zorunlu alanları doldurun!', 'error');
                return false;
            }
            
            // Loading state
            submitBtn.style.display = 'none';
            formLoading.style.display = 'block';
            
            console.log('📤 Form submitting...');
        });
        
        // Auto-save functionality
        setupAutoSave();
        
        // Image preview
        setupImagePreview();
        
        // Load draft
        loadDraft();
        
        console.log('✅ Form handlers ready');
    }
});

// Auto-save draft functionality
function setupAutoSave() {
    let saveTimer;
    
    const inputs = document.querySelectorAll('input, textarea, select');
    inputs.forEach(input => {
        if (input.type !== 'file') {
            input.addEventListener('input', () => {
                clearTimeout(saveTimer);
                saveTimer = setTimeout(saveDraft, 2000);
            });
        }
    });
}

function saveDraft() {
    const formData = new FormData(document.getElementById('externalPropertyForm'));
    const draftData = {};
    
    for (let [key, value] of formData.entries()) {
        if (value && typeof value === 'string') {
            draftData[key] = value;
        }
    }
    
    localStorage.setItem('externalPropertyDraft', JSON.stringify(draftData));
    console.log('💾 Draft saved');
}

function loadDraft() {
    const draft = localStorage.getItem('externalPropertyDraft');
    if (draft) {
        try {
            const draftData = JSON.parse(draft);
            Object.keys(draftData).forEach(key => {
                const input = document.querySelector(`[name="${key}"]`);
                if (input && input.type !== 'file') {
                    input.value = draftData[key];
                }
            });
            console.log('📝 Draft loaded');
        } catch (e) {
            console.log('Draft load failed:', e);
        }
    }
}

// Image preview functionality
function setupImagePreview() {
    const mainImageInput = document.querySelector('[name="main_image"]');
    const additionalImagesInput = document.querySelector('[name="additional_images[]"]');
    const previewContainer = document.getElementById('image-preview');
    
    if (mainImageInput) {
        mainImageInput.addEventListener('change', function(e) {
            previewImages(e.target.files, previewContainer, true);
        });
    }
    
    if (additionalImagesInput) {
        additionalImagesInput.addEventListener('change', function(e) {
            previewImages(e.target.files, previewContainer, false);
        });
    }
}

function previewImages(files, container, isMain = false) {
    if (isMain) {
        container.innerHTML = '';
    }
    
    Array.from(files).forEach((file, index) => {
        if (file.type.startsWith('image/')) {
            const reader = new FileReader();
            reader.onload = function(e) {
                const img = document.createElement('img');
                img.src = e.target.result;
                img.title = file.name;
                if (isMain) {
                    img.style.border = '3px solid #667eea';
                }
                container.appendChild(img);
            };
            reader.readAsDataURL(file);
        }
    });
}

// Message display function
function showMessage(message, type = 'info') {
    const messagesDiv = document.getElementById('messages');
    messagesDiv.innerHTML = `<div class="${type}-message">${message}</div>`;
    messagesDiv.style.display = 'block';
    
    // Auto hide after 5 seconds
    setTimeout(() => {
        messagesDiv.style.display = 'none';
    }, 5000);
}

// City change handler for districts
function setupLocationHandlers() {
    const citySelect = document.querySelector('[name="city"]');
    const districtInput = document.querySelector('[name="district"]');
    
    if (citySelect && districtInput) {
        citySelect.addEventListener('change', function() {
            const selectedCity = this.value;
            if (selectedCity) {
                // Load districts for selected city
                loadDistricts(selectedCity);
            }
        });
    }
}

// Load districts based on selected city
function loadDistricts(cityName) {
    fetch(`/fast/data/districts/${cityName}.json`)
        .then(r => r.json())
        .then(districts => {
            const districtInput = document.querySelector('[name="district"]');
            if (districtInput) {
                // Convert input to select for better UX
                const districtSelect = document.createElement('select');
                districtSelect.name = 'district';
                districtSelect.className = 'form-control';
                districtSelect.required = true;
                
                const defaultOption = document.createElement('option');
                defaultOption.value = '';
                defaultOption.textContent = 'İlçe Seçin...';
                districtSelect.appendChild(defaultOption);
                
                districts.forEach(district => {
                    const option = document.createElement('option');
                    option.value = district;
                    option.textContent = district;
                    districtSelect.appendChild(option);
                });
                
                districtInput.parentNode.replaceChild(districtSelect, districtInput);
                console.log(`🏘️ Districts loaded for ${cityName}`);
            }
        })
        .catch(e => {
            console.log('Districts load failed:', e);
        });
}

// Performance monitoring
window.addEventListener('load', () => {
    const loadTime = performance.now();
    console.log('🚀 All external resources loaded in:', Math.round(loadTime) + 'ms');
    
    // Setup location handlers after all data is loaded
    setTimeout(setupLocationHandlers, 1000);
});

// Form enhancement functions
function enhanceFormUX() {
    // Add floating labels
    const formControls = document.querySelectorAll('.form-control');
    formControls.forEach(control => {
        control.addEventListener('focus', function() {
            this.parentNode.classList.add('focused');
        });
        
        control.addEventListener('blur', function() {
            if (!this.value) {
                this.parentNode.classList.remove('focused');
            }
        });
    });
    
    // Add progress indicator
    addFormProgress();
}

function addFormProgress() {
    const requiredFields = document.querySelectorAll('[required]');
    const progressContainer = document.createElement('div');
    progressContainer.className = 'form-progress';
    progressContainer.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 0%;
        height: 3px;
        background: linear-gradient(90deg, #667eea, #764ba2);
        transition: width 0.3s ease;
        z-index: 1000;
    `;
    document.body.appendChild(progressContainer);
    
    function updateProgress() {
        const filledFields = Array.from(requiredFields).filter(field => field.value.trim());
        const progress = (filledFields.length / requiredFields.length) * 100;
        progressContainer.style.width = progress + '%';
    }
    
    requiredFields.forEach(field => {
        field.addEventListener('input', updateProgress);
    });
}

// Initialize enhancements when DOM is ready
document.addEventListener('DOMContentLoaded', enhanceFormUX);
