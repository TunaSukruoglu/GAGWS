// Form Handlers and Utilities
function setupFormHandlers() {
    // Title preview
    const titleInput = document.getElementById('title');
    const titlePreview = document.getElementById('title-preview');
    
    if (titleInput && titlePreview) {
        titleInput.addEventListener('input', function() {
            const title = this.value.trim();
            if (title) {
                titlePreview.innerHTML = `<span class="fw-bold" style="color: #0d6efd;">${title}</span>`;
            } else {
                titlePreview.innerHTML = '<span class="text-muted">İlan başlığınız burada görünecek...</span>';
            }
        });
    }

    // Price formatting
    const priceInput = document.getElementById('price');
    if (priceInput) {
        priceInput.addEventListener('input', function() {
            let value = this.value.replace(/\D/g, '');
            if (value) {
                value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
            }
            this.value = value;
        });
    }

    // Capitalize inputs (İl, İlçe, Mahalle)
    const capitalizeInputs = document.querySelectorAll('.capitalize-input');
    capitalizeInputs.forEach(input => {
        input.addEventListener('input', function() {
            // Kelime başlarını büyük yap
            let value = this.value.toLowerCase();
            value = value.replace(/\b\w/g, l => l.toUpperCase());
            
            // Türkçe karakterleri düzelt
            value = value.replace(/\bİ/g, 'İ');
            value = value.replace(/\bı/g, 'I');
            value = value.replace(/\bÇ/g, 'Ç');
            value = value.replace(/\bŞ/g, 'Ş');
            value = value.replace(/\bĞ/g, 'Ğ');
            value = value.replace(/\bÜ/g, 'Ü');
            value = value.replace(/\bÖ/g, 'Ö');
            
            this.value = value;
        });
    });

    // Location type selection
    const locationRadios = document.querySelectorAll('input[name="location_type"]');
    const siteNameSection = document.getElementById('site-name-section');
    const addressDetailsSection = document.getElementById('address-details-section');
    const siteNameInput = document.getElementById('site_name');
    const addressDetailsInput = document.getElementById('address_details');
    
    locationRadios.forEach(radio => {
        radio.addEventListener('change', function() {
            if (this.value === 'site') {
                siteNameSection.style.display = 'block';
                addressDetailsSection.style.display = 'none';
                siteNameInput.required = true;
                addressDetailsInput.required = false;
                addressDetailsInput.value = '';
            } else if (this.value === 'standalone') {
                siteNameSection.style.display = 'none';
                addressDetailsSection.style.display = 'block';
                siteNameInput.required = false;
                addressDetailsInput.required = true;
                siteNameInput.value = '';
            }
        });
    });
}

// Mobile sidebar toggle
function toggleSidebar() {
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    if (sidebar && overlay) {
        sidebar.classList.toggle('show');
        overlay.classList.toggle('show');
    }
}

// Debug function - print photo info to console
function debugPhotos() {
    console.log('Selected photos:', selectedPhotos);
    const photoInput = document.getElementById('photoInput');
    console.log('Input files:', photoInput.files);
}

// Export functions for global access
window.setupFormHandlers = setupFormHandlers;
window.toggleSidebar = toggleSidebar;
window.debugPhotos = debugPhotos;
