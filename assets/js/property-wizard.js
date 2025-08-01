// Property Form Wizard System
// Enhanced Category Selection and Form Navigation

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('DOM loaded, initializing wizard...');
    
    // Edit mode kontrolü ve değer atama
    const isEditMode = window.editMode || false;
    
    if (isEditMode) {
        console.log('Edit mode detected, setting form values...');
        
        // Hidden input değerlerini kontrol et ve ayarla
        const categoryInput = document.getElementById('category');
        const typeInput = document.getElementById('type');
        
        if (categoryInput && categoryInput.value) {
            console.log('Category value from hidden input:', categoryInput.value);
        }
        
        if (typeInput && typeInput.value) {
            console.log('Type value from hidden input:', typeInput.value);
        }
        
        // Form submit'e hazır hale getir
        showFormDirectly();
        return; // Edit modunda wizard atlansın
    }
    
    // Wizard state variables
    let currentStep = 1;
    let selectedCategory = '';
    let selectedType = '';
    let selectedSubcategory = '';
    
    // Transaction types for each category
    const transactionTypes = {
        'konut': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-house' },
            { value: 'daily_rent', text: 'Turistik Günlük Kiralık', icon: 'bi-calendar-date' },
            { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' }
        ],
        'is_yeri': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-building' },
            { value: 'transfer_sale', text: 'Devren Satılık', icon: 'bi-arrow-repeat' },
            { value: 'transfer_rent', text: 'Devren Kiralık', icon: 'bi-arrow-repeat' }
        ],
        'arsa': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-geo-alt' }
        ],
        'bina': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-buildings' }
        ],
        'devre_mulk': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-calendar-check' }
        ],
        'turistik_tesis': [
            { value: 'sale', text: 'Satılık', icon: 'bi-currency-dollar' },
            { value: 'rent', text: 'Kiralık', icon: 'bi-compass' }
        ]
    };

    // Subcategory system
    const subcategoryTypes = {
        'konut': {
            'sale': [
                { value: 'daire', text: 'Daire', icon: 'bi-building' },
                { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                { value: 'ciftlik_evi', text: 'Çiftlik Evi', icon: 'bi-tree' },
                { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                { value: 'koy_evi', text: 'Köy Evi', icon: 'bi-tree-fill' },
                { value: 'yali', text: 'Yalı', icon: 'bi-water' }
            ],
            'rent': [
                { value: 'daire', text: 'Daire', icon: 'bi-building' },
                { value: 'rezidans', text: 'Rezidans', icon: 'bi-buildings' },
                { value: 'mustakil_ev', text: 'Müstakil Ev', icon: 'bi-house' },
                { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                { value: 'ikiz_villa', text: 'İkiz Villa', icon: 'bi-house-add' },
                { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                { value: 'yali', text: 'Yalı', icon: 'bi-water' }
            ],
            'daily_rent': [
                { value: 'daire', text: 'Daire', icon: 'bi-building' },
                { value: 'villa', text: 'Villa', icon: 'bi-house-heart' },
                { value: 'yazlik', text: 'Yazlık', icon: 'bi-sun' },
                { value: 'apart_pansiyon', text: 'Apart & Pansiyon', icon: 'bi-door-open' },
                { value: 'triplex', text: 'Triplex', icon: 'bi-stack' },
                { value: 'dublex', text: 'Dublex', icon: 'bi-layers' },
                { value: 'yali', text: 'Yalı', icon: 'bi-water' }
            ],
            'transfer_sale': [
                { value: 'daire', text: 'Daire', icon: 'bi-building' },
                { value: 'villa', text: 'Villa', icon: 'bi-house-heart' }
            ]
        },
        'is_yeri': {
            'sale': [
                { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                { value: 'buro_ofis', text: 'Büro & Ofis', icon: 'bi-briefcase' },
                { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                { value: 'fabrika_uretim', text: 'Fabrika & Üretim', icon: 'bi-gear' },
                { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' },
                { value: 'eczane', text: 'Eczane', icon: 'bi-plus-circle' },
                { value: 'berber_kuafor', text: 'Berber & Kuaför', icon: 'bi-scissors' }
            ],
            'rent': [
                { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                { value: 'buro_ofis', text: 'Büro & Ofis', icon: 'bi-briefcase' },
                { value: 'depo_antrepo', text: 'Depo & Antrepo', icon: 'bi-box' },
                { value: 'atolye', text: 'Atölye', icon: 'bi-tools' },
                { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
            ],
            'transfer_sale': [
                { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                { value: 'restoran', text: 'Restoran & Lokanta', icon: 'bi-cup-hot' },
                { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' },
                { value: 'market_bakkal', text: 'Market & Bakkal', icon: 'bi-basket' }
            ],
            'transfer_rent': [
                { value: 'dukkan_magaza', text: 'Dükkan & Mağaza', icon: 'bi-shop' },
                { value: 'cafe_bar', text: 'Cafe & Bar', icon: 'bi-cup-straw' }
            ]
        },
        'arsa': {
            'sale': [
                { value: 'konut_arsasi', text: 'Konut Arsası', icon: 'bi-house-door' },
                { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                { value: 'sanayi_arsasi', text: 'Sanayi Arsası', icon: 'bi-gear-wide' },
                { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' },
                { value: 'villa_arsasi', text: 'Villa Arsası', icon: 'bi-house-heart' }
            ],
            'rent': [
                { value: 'ticari_arsa', text: 'Ticari Arsa', icon: 'bi-shop-window' },
                { value: 'tarla', text: 'Tarla', icon: 'bi-tree' },
                { value: 'bahce_arsa', text: 'Bahçe Arsa', icon: 'bi-flower1' }
            ]
        },
        'bina': {
            'sale': [
                { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' },
                { value: 'plaza', text: 'Plaza', icon: 'bi-building-up' }
            ],
            'rent': [
                { value: 'apartman', text: 'Apartman', icon: 'bi-building' },
                { value: 'is_hani', text: 'İş Hanı', icon: 'bi-buildings' }
            ]
        },
        'devre_mulk': {
            'sale': [
                { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' },
                { value: 'otel', text: 'Otel', icon: 'bi-building' }
            ],
            'rent': [
                { value: 'tatil_koyu', text: 'Tatil Köyü', icon: 'bi-tree' }
            ]
        },
        'turistik_tesis': {
            'sale': [
                { value: 'otel', text: 'Otel', icon: 'bi-building' },
                { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' },
                { value: 'kamp_alani', text: 'Kamp Alanı', icon: 'bi-tree' }
            ],
            'rent': [
                { value: 'otel', text: 'Otel', icon: 'bi-building' },
                { value: 'pansiyon', text: 'Pansiyon', icon: 'bi-house' }
            ]
        }
    };

    // Setup category selection with proper event listeners
    function setupCategorySelection() {
        const categoryItems = document.querySelectorAll('.category-item');
        console.log('Found category items:', categoryItems.length);
        
        categoryItems.forEach((item, index) => {
            console.log(`Setting up category ${index}:`, item.dataset.category);
            
            // Remove any existing listeners
            item.removeEventListener('click', handleCategoryClick);
            
            // Add new listener
            item.addEventListener('click', handleCategoryClick);
            
            // Add visual feedback
            item.addEventListener('mouseenter', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = 'translateY(-5px) scale(1.05)';
                }
            });
            
            item.addEventListener('mouseleave', function() {
                if (!this.classList.contains('selected')) {
                    this.style.transform = '';
                }
            });
        });
    }

    function handleCategoryClick(event) {
        event.preventDefault();
        event.stopPropagation();
        
        const categoryValue = this.dataset.category;
        console.log('=== CATEGORY SELECTED ===');
        console.log('Category:', categoryValue);
        
        // Clear previous selections
        document.querySelectorAll('.category-item').forEach(item => {
            item.classList.remove('selected');
            item.style.transform = '';
        });
        
        // Mark new selection
        this.classList.add('selected');
        this.style.transform = 'translateY(-3px) scale(1.02)';
        
        // Update state
        selectedCategory = categoryValue;
        
        // Update hidden input
        const categoryInput = document.getElementById('category');
        if (categoryInput) {
            categoryInput.value = selectedCategory;
            console.log('Category input updated:', categoryInput.value);
        }
        
        // Enable next button
        const nextBtn = document.getElementById('next-step');
        if (nextBtn) {
            nextBtn.disabled = false;
            nextBtn.style.backgroundColor = '#0d6efd';
            nextBtn.style.color = 'white';
        }
        
        // Auto advance after short delay
        setTimeout(() => {
            console.log('Auto-advancing to transaction types...');
            nextStep();
        }, 800);
    }

    function setupTransactionSelection() {
        const transactionItems = document.querySelectorAll('.transaction-item');
        
        transactionItems.forEach(item => {
            item.addEventListener('click', function(event) {
                event.preventDefault();
                event.stopPropagation();
                
                const typeValue = this.dataset.type;
                console.log('=== TRANSACTION SELECTED ===');
                console.log('Type:', typeValue);
                
                // Clear previous selections
                document.querySelectorAll('.transaction-item').forEach(i => {
                    i.classList.remove('selected');
                });
                
                // Mark new selection
                this.classList.add('selected');
                
                // Update state
                selectedType = typeValue;
                
                // Update hidden input
                const typeInput = document.getElementById('type');
                if (typeInput) {
                    typeInput.value = selectedType;
                    console.log('Type input updated:', typeInput.value);
                }
                
                // Enable next button for subcategory step
                const nextBtn = document.getElementById('next-step');
                if (nextBtn) {
                    nextBtn.disabled = false;
                }
                
                console.log('Transaction selected, enabling next step for subcategory');
                
                // Auto advance to subcategories after short delay
                setTimeout(() => {
                    console.log('Auto-advancing to subcategories...');
                    showSubcategories();
                    showStep(3);
                }, 800);
            });
        });
    }

    function showSubcategories() {
        console.log('showSubcategories for category:', selectedCategory, 'type:', selectedType);
        
        // Hide step 2, show step 3
        document.getElementById('wizard-step-2').style.display = 'none';
        document.getElementById('wizard-step-3').style.display = 'block';
        
        const container = document.getElementById('subcategory-options');
        if (!container) {
            console.error('subcategory-options container not found');
            return;
        }
        
        container.innerHTML = '';
        
        // Get subcategories based on category and transaction type
        const categoryData = subcategoryTypes[selectedCategory];
        if (!categoryData) {
            console.log('No subcategory data, going to main form');
            setTimeout(() => showMainForm(), 100);
            return;
        }
        
        const subcategories = categoryData[selectedType] || [];
        console.log('Subcategories:', subcategories);
        
        if (subcategories.length === 0) {
            console.log('No subcategories, going to main form');
            setTimeout(() => showMainForm(), 100);
            return;
        }
        
        subcategories.forEach(subcat => {
            const item = document.createElement('div');
            item.className = 'subcategory-item';
            item.dataset.subcategory = subcat.value;
            item.innerHTML = `
                <i class="bi ${subcat.icon}"></i>
                <span>${subcat.text}</span>
            `;
            container.appendChild(item);
        });
        
        // Setup event listeners for subcategory selection
        setupSubcategorySelection();
        
        // Update buttons for Step 3 - show continue form button
        const nextBtn = document.getElementById('next-step');
        const continueBtn = document.getElementById('continue-form');
        const prevBtn = document.getElementById('prev-step');
        
        if (nextBtn) nextBtn.style.display = 'none';
        if (continueBtn) {
            continueBtn.style.display = 'inline-block';
            continueBtn.disabled = true; // Selection yapılana kadar devre dışı
        }
        if (prevBtn) prevBtn.style.display = 'inline-block';
    }

    function setupSubcategorySelection() {
        console.log('setupSubcategorySelection');
        
        document.querySelectorAll('.subcategory-item').forEach(item => {
            item.addEventListener('click', function() {
                const subcatValue = this.dataset.subcategory;
                console.log('=== SUBCATEGORY SELECTED ===');
                console.log('Subcategory:', subcatValue);
                
                // Clear previous selections
                document.querySelectorAll('.subcategory-item').forEach(i => {
                    i.classList.remove('selected');
                });
                
                // Mark new selection
                this.classList.add('selected');
                
                // Update state
                selectedSubcategory = subcatValue;
                
                // Update hidden input
                const subcatInput = document.getElementById('subcategory');
                if (subcatInput) {
                    subcatInput.value = selectedSubcategory;
                    console.log('Subcategory input updated:', subcatInput.value);
                }
                
                // Enable continue button
                const continueBtn = document.getElementById('continue-form');
                if (continueBtn) {
                    continueBtn.disabled = false;
                }
                
                // Auto advance to main form
                setTimeout(() => {
                    console.log('Auto-advancing to main form...');
                    showMainForm();
                }, 800);
            });
        });
    }

    function nextStep() {
        console.log('nextStep called, currentStep:', currentStep);
        
        if (currentStep === 1) {
            showTransactionTypes();
            currentStep = 2;
            updateStepIndicator();
        } else if (currentStep === 2) {
            showSubcategories();
            currentStep = 3;
            updateStepIndicator();
        }
    }

    function prevStep() {
        console.log('prevStep called, currentStep:', currentStep);
        
        if (currentStep === 2) {
            currentStep = 1;
            document.getElementById('wizard-step-1').style.display = 'block';
            document.getElementById('wizard-step-2').style.display = 'none';
            updateStepIndicator();
        } else if (currentStep === 3) {
            currentStep = 2;
            document.getElementById('wizard-step-2').style.display = 'block';
            document.getElementById('wizard-step-3').style.display = 'none';
            updateStepIndicator();
        }
    }

    function showTransactionTypes() {
        console.log('showTransactionTypes for category:', selectedCategory);
        
        // Hide step 1, show step 2
        document.getElementById('wizard-step-1').style.display = 'none';
        document.getElementById('wizard-step-2').style.display = 'block';
        
        const container = document.getElementById('transaction-options');
        if (!container) {
            console.error('transaction-options container not found');
            return;
        }
        
        container.innerHTML = '';
        
        const types = transactionTypes[selectedCategory] || [];
        console.log('Transaction types:', types);
        
        if (types.length === 0) {
            console.log('No transaction types, going to main form');
            setTimeout(() => showMainForm(), 100);
            return;
        }
        
        types.forEach(type => {
            const item = document.createElement('div');
            item.className = 'transaction-item';
            item.dataset.type = type.value;
            item.innerHTML = `
                <i class="bi ${type.icon}"></i>
                <span>${type.text}</span>
            `;
            container.appendChild(item);
        });
        
        // Setup event listeners for new transaction items
        setupTransactionSelection();
        
        // Update buttons - Step 2'de next butonunu etkinleştir (subcategory için)  
        const nextBtn = document.getElementById('next-step');
        const continueBtn = document.getElementById('continue-form');
        const prevBtn = document.getElementById('prev-step');
        
        if (nextBtn) {
            nextBtn.style.display = 'inline-block';
            nextBtn.disabled = true; // Selection yapılana kadar devre dışı
        }
        if (continueBtn) continueBtn.style.display = 'none';
        if (prevBtn) prevBtn.style.display = 'inline-block';
    }

    function updateStepIndicator() {
        console.log('updateStepIndicator for step:', currentStep);
        
        const steps = document.querySelectorAll('.step');
        steps.forEach((step, index) => {
            step.classList.remove('active', 'completed');
            
            if (index + 1 < currentStep) {
                step.classList.add('completed');
            } else if (index + 1 === currentStep) {
                step.classList.add('active');
            }
        });
    }

    function showStep(stepNumber) {
        console.log('showStep called for step:', stepNumber);
        currentStep = stepNumber;
        updateStepIndicator();
    }

    function showFormDirectly() {
        console.log('showFormDirectly called for edit mode');
        
        // Edit modunda doğrudan formu göster
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.style.display = 'none';
        });
        
        // Step indicator'ı gizle
        const stepIndicator = document.querySelector('.step-indicator');
        if (stepIndicator && stepIndicator.parentElement && stepIndicator.parentElement.parentElement) {
            stepIndicator.parentElement.parentElement.style.display = 'none';
        }
        
        // Navigation buttonları gizle
        const navigationDiv = document.querySelector('.row.mt-4');
        if (navigationDiv) {
            navigationDiv.style.display = 'none';
        }
        
        // Ana formu göster
        const mainForm = document.getElementById('main-form');
        if (mainForm) {
            mainForm.style.display = 'block';
            console.log('Main form shown for edit mode');
        }
    }

    function showMainForm() {
        console.log('showMainForm called');
        
        // Hide all wizard steps
        document.querySelectorAll('.wizard-step').forEach(step => {
            step.style.display = 'none';
        });
        
        // Hide step indicator
        const stepIndicator = document.querySelector('.step-indicator');
        if (stepIndicator && stepIndicator.parentElement && stepIndicator.parentElement.parentElement) {
            stepIndicator.parentElement.parentElement.style.display = 'none';
        }
        
        // Hide navigation buttons
        const navigationDiv = document.querySelector('.row.mt-4');
        if (navigationDiv) {
            navigationDiv.style.display = 'none';
        }
        
        // Update selection summary
        updateSelectionSummary();
        
        // Show main form
        const mainForm = document.getElementById('main-form');
        if (mainForm) {
            mainForm.style.display = 'block';
            console.log('Main form shown successfully');
        }
    }

    function updateSelectionSummary() {
        const summaryContainer = document.getElementById('selection-summary');
        if (!summaryContainer) return;
        
        const categoryNames = {
            'konut': 'Konut',
            'is_yeri': 'İş Yeri',
            'arsa': 'Arsa',
            'bina': 'Bina',
            'devre_mulk': 'Devre Mülk',
            'turistik_tesis': 'Turistik Tesis'
        };
        
        const typeNames = {
            'sale': 'Satılık',
            'rent': 'Kiralık',
            'daily_rent': 'Turistik Günlük Kiralık',
            'transfer_sale': 'Devren Satılık',
            'transfer_rent': 'Devren Kiralık'
        };
        
        const categoryText = categoryNames[selectedCategory] || selectedCategory;
        const typeText = typeNames[selectedType] || selectedType;
        
        summaryContainer.innerHTML = `
            <div class="d-flex flex-wrap gap-3 align-items-center">
                <div class="badge bg-primary fs-6 p-2">${categoryText}</div>
                <div class="badge bg-success fs-6 p-2">${typeText}</div>
                ${selectedSubcategory ? `<div class="badge bg-info fs-6 p-2">${selectedSubcategory}</div>` : ''}
            </div>
            <div class="mt-2">
                <small class="text-muted">Seçiminizi değiştirmek için sayfayı yeniden yükleyin.</small>
            </div>
        `;
    }

    // Initialize everything
    setupCategorySelection();
    
    // Navigation button handlers
    const nextBtn = document.getElementById('next-step');
    const prevBtn = document.getElementById('prev-step');
    const continueBtn = document.getElementById('continue-form');
    
    if (nextBtn) nextBtn.addEventListener('click', nextStep);
    if (prevBtn) prevBtn.addEventListener('click', prevStep);
    if (continueBtn) continueBtn.addEventListener('click', showMainForm);
    
    // Setup other features
    setupFormHandlers();
    setupPhotoUpload();
    
    console.log('Initialization complete');
    
    // Make functions globally available
    window.showFormDirectly = showFormDirectly;
    window.nextStep = nextStep;
    window.prevStep = prevStep;
    window.showMainForm = showMainForm;
});
