// Dashboard Common JavaScript Functions

// Mobile navigation toggle
document.addEventListener('DOMContentLoaded', function() {
    // Mobile nav toggle
    const mobileToggler = document.querySelector('.dash-mobile-nav-toggler');
    const sidebar = document.querySelector('.dash-aside-navbar');
    const closeBtn = document.querySelector('.close-btn');

    if (mobileToggler) {
        mobileToggler.addEventListener('click', function() {
            sidebar.classList.add('show');
        });
    }

    if (closeBtn) {
        closeBtn.addEventListener('click', function() {
            sidebar.classList.remove('show');
        });
    }

    // Close sidebar when clicking outside on mobile
    document.addEventListener('click', function(e) {
        if (window.innerWidth <= 1199) {
            if (!sidebar.contains(e.target) && !mobileToggler.contains(e.target)) {
                sidebar.classList.remove('show');
            }
        }
    });

    // Close sidebar on window resize
    window.addEventListener('resize', function() {
        if (window.innerWidth > 1199 && sidebar) {
            sidebar.classList.remove('show');
        }
    });

    // Alert auto dismiss
    setTimeout(function() {
        var alerts = document.querySelectorAll('.alert');
        alerts.forEach(function(alert) {
            if (typeof bootstrap !== 'undefined' && bootstrap.Alert) {
                var bsAlert = new bootstrap.Alert(alert);
                bsAlert.close();
            }
        });
    }, 5000);

    // Smooth scrolling for sidebar links
    document.querySelectorAll('.dasboard-main-nav a').forEach(link => {
        link.addEventListener('click', function(e) {
            if (this.href.includes('#')) {
                e.preventDefault();
                const target = document.querySelector(this.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth'
                    });
                }
            }
        });
    });

    // Active link highlighting based on current page
    const currentPage = window.location.pathname.split('/').pop().replace('.php', '');
    const sidebarLinks = document.querySelectorAll('.dasboard-main-nav a');
    
    sidebarLinks.forEach(link => {
        const href = link.getAttribute('href');
        if (href && href.includes(currentPage)) {
            link.classList.add('active');
            const icon = link.querySelector('i');
            if (icon) {
                icon.style.color = '#15B97C';
            }
        }
    });

    // Stats cards animation
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.style.animation = 'fadeInUp 0.6s ease forwards';
            }
        });
    }, observerOptions);

    document.querySelectorAll('.stats-card, .action-card, .recent-activity-card').forEach(card => {
        observer.observe(card);
    });
});

// Common utility functions
const DashboardUtils = {
    // Format phone number
    formatPhoneNumber: function(input) {
        let value = input.value.replace(/\D/g, '');
        if (value.startsWith('90')) {
            value = value.substring(2);
        }
        if (value.length > 0) {
            if (value.length <= 3) {
                value = `(${value}`;
            } else if (value.length <= 6) {
                value = `(${value.substring(0, 3)}) ${value.substring(3)}`;
            } else if (value.length <= 8) {
                value = `(${value.substring(0, 3)}) ${value.substring(3, 6)} ${value.substring(6)}`;
            } else {
                value = `(${value.substring(0, 3)}) ${value.substring(3, 6)} ${value.substring(6, 8)} ${value.substring(8, 10)}`;
            }
            input.value = '+90 ' + value;
        }
    },

    // Format price with Turkish formatting
    formatPrice: function(input) {
        let value = input.value.replace(/\D/g, '');
        value = value.replace(/\B(?=(\d{3})+(?!\d))/g, '.');
        input.value = value;
    },

    // Show loading state
    showLoading: function(element) {
        if (element) {
            element.classList.add('loading');
            element.disabled = true;
            const originalText = element.innerHTML;
            element.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Yükleniyor...';
            
            // Store original text for later restoration
            element.dataset.originalText = originalText;
        }
    },

    // Hide loading state
    hideLoading: function(element) {
        if (element && element.dataset.originalText) {
            element.classList.remove('loading');
            element.disabled = false;
            element.innerHTML = element.dataset.originalText;
        }
    },

    // Confirm dialog
    confirm: function(message, callback) {
        if (confirm(message)) {
            if (typeof callback === 'function') {
                callback();
            }
            return true;
        }
        return false;
    },

    // Show notification
    showNotification: function(message, type = 'success') {
        const notification = document.createElement('div');
        notification.className = `alert alert-${type} alert-dismissible fade show position-fixed`;
        notification.style.cssText = 'top: 20px; right: 20px; z-index: 9999; min-width: 300px;';
        notification.innerHTML = `
            <i class="fas fa-${type === 'success' ? 'check-circle' : 'exclamation-circle'} me-2"></i>
            ${message}
            <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
        `;
        
        document.body.appendChild(notification);
        
        // Auto remove after 5 seconds
        setTimeout(() => {
            if (notification.parentNode) {
                notification.remove();
            }
        }, 5000);
    },

    // Initialize tooltips
    initTooltips: function() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Tooltip) {
            const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
            tooltipTriggerList.map(function (tooltipTriggerEl) {
                return new bootstrap.Tooltip(tooltipTriggerEl);
            });
        }
    },

    // Initialize popovers
    initPopovers: function() {
        if (typeof bootstrap !== 'undefined' && bootstrap.Popover) {
            const popoverTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="popover"]'));
            popoverTriggerList.map(function (popoverTriggerEl) {
                return new bootstrap.Popover(popoverTriggerEl);
            });
        }
    },

    // Form validation
    validateForm: function(form) {
        const requiredFields = form.querySelectorAll('[required]');
        let isValid = true;

        requiredFields.forEach(field => {
            if (!field.value.trim()) {
                field.classList.add('is-invalid');
                isValid = false;
            } else {
                field.classList.remove('is-invalid');
            }
        });

        return isValid;
    },

    // Auto save form data to localStorage
    autoSaveForm: function(formId, intervalMs = 30000) {
        const form = document.getElementById(formId);
        if (!form) return;

        const saveKey = `autosave_${formId}`;
        
        // Load saved data
        const savedData = localStorage.getItem(saveKey);
        if (savedData) {
            const data = JSON.parse(savedData);
            Object.keys(data).forEach(key => {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && field.type !== 'file') {
                    field.value = data[key];
                }
            });
        }

        // Auto save every interval
        setInterval(() => {
            const formData = new FormData(form);
            const data = {};
            
            for (let [key, value] of formData.entries()) {
                const field = form.querySelector(`[name="${key}"]`);
                if (field && field.type !== 'file') {
                    data[key] = value;
                }
            }
            
            localStorage.setItem(saveKey, JSON.stringify(data));
        }, intervalMs);

        // Clear saved data on successful submit
        form.addEventListener('submit', function() {
            localStorage.removeItem(saveKey);
        });
    },

    // Initialize all components
    init: function() {
        this.initTooltips();
        this.initPopovers();
        
        // Auto-format phone inputs
        document.querySelectorAll('input[type="tel"], input[name="phone"]').forEach(input => {
            input.addEventListener('input', () => this.formatPhoneNumber(input));
        });

        // Auto-format price inputs
        document.querySelectorAll('input[name="price"], input[name="yearly_tax"]').forEach(input => {
            input.addEventListener('input', () => this.formatPrice(input));
        });

        // Add loading states to form submits
        document.querySelectorAll('form').forEach(form => {
            form.addEventListener('submit', (e) => {
                const submitBtn = form.querySelector('button[type="submit"]');
                if (submitBtn && this.validateForm(form)) {
                    this.showLoading(submitBtn);
                }
            });
        });
    }
};

// Initialize phone number formatting
document.addEventListener('DOMContentLoaded', function() {
    const phoneInputs = document.querySelectorAll('input[type="tel"], input[name="phone"]');
    phoneInputs.forEach(input => {
        input.addEventListener('input', function() {
            DashboardUtils.formatPhoneNumber(this);
        });
    });

    // Initialize price formatting
    const priceInputs = document.querySelectorAll('input[name="price"], input[name="yearly_tax"]');
    priceInputs.forEach(input => {
        input.addEventListener('input', function() {
            DashboardUtils.formatPrice(this);
        });
    });

    // Initialize all dashboard utilities
    DashboardUtils.init();
});

// Search functionality
function initSearch() {
    const searchForms = document.querySelectorAll('.search-form');
    
    searchForms.forEach(form => {
        const input = form.querySelector('input[type="text"]');
        let searchTimeout;
        
        if (input) {
            input.addEventListener('input', function() {
                clearTimeout(searchTimeout);
                searchTimeout = setTimeout(() => {
                    // Auto-submit search after 500ms of no typing
                    if (this.value.length >= 2 || this.value.length === 0) {
                        form.submit();
                    }
                }, 500);
            });
        }
    });
}

// Bulk actions functionality
function initBulkActions() {
    const selectAllCheckbox = document.getElementById('selectAll');
    const itemCheckboxes = document.querySelectorAll('.item-checkbox');
    const bulkActionBar = document.getElementById('bulkActionBar');
    
    if (selectAllCheckbox && itemCheckboxes.length > 0) {
        selectAllCheckbox.addEventListener('change', function() {
            itemCheckboxes.forEach(checkbox => {
                checkbox.checked = this.checked;
            });
            updateBulkActions();
        });

        itemCheckboxes.forEach(checkbox => {
            checkbox.addEventListener('change', function() {
                updateBulkActions();
                
                // Update select all checkbox state
                const allChecked = Array.from(itemCheckboxes).every(cb => cb.checked);
                const someChecked = Array.from(itemCheckboxes).some(cb => cb.checked);
                
                selectAllCheckbox.checked = allChecked;
                selectAllCheckbox.indeterminate = someChecked && !allChecked;
            });
        });
    }

    function updateBulkActions() {
        const checkedBoxes = document.querySelectorAll('.item-checkbox:checked');
        const count = checkedBoxes.length;
        
        if (bulkActionBar) {
            const selectedCount = bulkActionBar.querySelector('#selectedCount');
            if (selectedCount) {
                selectedCount.textContent = count;
            }
            
            if (count > 0) {
                bulkActionBar.classList.add('show');
            } else {
                bulkActionBar.classList.remove('show');
            }
        }
    }
}

// Initialize all functionality when DOM is loaded
document.addEventListener('DOMContentLoaded', function() {
    initSearch();
    initBulkActions();
});

// Export utilities for global use
window.DashboardUtils = DashboardUtils;