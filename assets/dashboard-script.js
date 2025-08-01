// Dashboard JavaScript Functions

document.addEventListener('DOMContentLoaded', function() {
    // Initialize dashboard
    initDashboard();
});

function initDashboard() {
    // Mobile sidebar handling
    initMobileSidebar();
    
    // Action cards interactions
    initActionCards();
    
    // Stats cards animations
    initStatsAnimations();
    
    // Auto-update time
    initTimeUpdates();
}

// Mobile Sidebar Functions
function initMobileSidebar() {
    const mobileMenuBtn = document.querySelector('.mobile-menu-btn');
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    if (mobileMenuBtn) {
        mobileMenuBtn.addEventListener('click', toggleSidebar);
    }
    
    // Close sidebar when clicking outside
    if (overlay) {
        overlay.addEventListener('click', closeSidebar);
    }
    
    // Auto-close on mobile when clicking nav links
    document.querySelectorAll('.nav-link').forEach(link => {
        link.addEventListener('click', function() {
            if (window.innerWidth <= 768) {
                closeSidebar();
            }
        });
    });
}

function toggleSidebar() {
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    if (sidebar) {
        sidebar.classList.toggle('show');
    }
    
    if (overlay) {
        overlay.classList.toggle('show');
    }
    
    // Prevent body scroll when sidebar is open
    document.body.classList.toggle('sidebar-open');
}

function closeSidebar() {
    const sidebar = document.querySelector('.dash-aside-navbar');
    const overlay = document.querySelector('.mobile-overlay');
    
    if (sidebar) {
        sidebar.classList.remove('show');
    }
    
    if (overlay) {
        overlay.classList.remove('show');
    }
    
    document.body.classList.remove('sidebar-open');
}

// Action Cards Interactions
function initActionCards() {
    document.querySelectorAll('.action-card').forEach(card => {
        card.addEventListener('click', function(e) {
            // Add click animation
            this.style.transform = 'scale(0.98)';
            setTimeout(() => {
                this.style.transform = '';
            }, 150);
        });
        
        // Add hover sound effect (optional)
        card.addEventListener('mouseenter', function() {
            // You can add subtle hover effects here
            this.style.transform = 'translateY(-5px)';
        });
        
        card.addEventListener('mouseleave', function() {
            this.style.transform = '';
        });
    });
}

// Stats Animations
function initStatsAnimations() {
    // Animate numbers on scroll
    const observerOptions = {
        threshold: 0.1,
        rootMargin: '0px 0px -50px 0px'
    };

    const observer = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                animateNumber(entry.target);
            }
        });
    }, observerOptions);

    document.querySelectorAll('.stats-number').forEach(number => {
        observer.observe(number);
    });
}

function animateNumber(element) {
    const finalNumber = parseInt(element.textContent);
    const duration = 1000; // 1 second
    const frameDuration = 1000 / 60; // 60 FPS
    const totalFrames = Math.round(duration / frameDuration);
    const easeOutQuad = t => t * (2 - t);

    let frame = 0;
    const countTo = finalNumber;

    const counter = setInterval(() => {
        frame++;
        const progress = easeOutQuad(frame / totalFrames);
        const currentNumber = Math.round(countTo * progress);

        element.textContent = currentNumber.toLocaleString();

        if (frame === totalFrames) {
            clearInterval(counter);
        }
    }, frameDuration);
}

// Time Updates
function initTimeUpdates() {
    // Update any time-related elements
    updateTimeElements();
    
    // Update every minute
    setInterval(updateTimeElements, 60000);
}

function updateTimeElements() {
    const timeElements = document.querySelectorAll('[data-time]');
    
    timeElements.forEach(element => {
        const timestamp = element.getAttribute('data-time');
        if (timestamp) {
            element.textContent = formatRelativeTime(timestamp);
        }
    });
}

function formatRelativeTime(timestamp) {
    const now = new Date();
    const time = new Date(timestamp);
    const diff = now - time;
    
    const minutes = Math.floor(diff / 60000);
    const hours = Math.floor(diff / 3600000);
    const days = Math.floor(diff / 86400000);
    
    if (days > 0) {
        return `${days} gün önce`;
    } else if (hours > 0) {
        return `${hours} saat önce`;
    } else if (minutes > 0) {
        return `${minutes} dakika önce`;
    } else {
        return 'Az önce';
    }
}

// === Favorites Page Functions === 
function removeFavorite(propertyId) {
    if (!confirm('Bu ilanı favorilerden kaldırmak istediğinizden emin misiniz?')) {
        return;
    }
    
    fetch('../ajax/remove-favorite.php', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: 'property_id=' + propertyId
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Remove the card with animation
            const card = document.querySelector(`[data-property-id="${propertyId}"]`);
            if (card) {
                card.style.opacity = '0';
                card.style.transform = 'translateY(-20px)';
                setTimeout(() => {
                    card.remove();
                    updateFavoriteCount();
                }, 300);
            }
            
            showNotification('İlan favorilerden kaldırıldı!', 'success');
        } else {
            showNotification('Hata oluştu: ' + data.message, 'error');
        }
    })
    .catch(error => {
        console.error('Error:', error);
        showNotification('Bir hata oluştu!', 'error');
    });
}

function shareProperty(propertyId) {
    const url = window.location.origin + '/property-detail.php?id=' + propertyId;
    
    if (navigator.share) {
        navigator.share({
            title: 'Emlak İlanı',
            text: 'Bu ilana göz atın!',
            url: url
        }).catch(console.error);
    } else {
        navigator.clipboard.writeText(url).then(() => {
            showNotification('Link kopyalandı!', 'success');
        }).catch(() => {
            // Fallback
            prompt('Link kopyalanacak:', url);
        });
    }
}

function filterFavorites() {
    const typeFilter = document.getElementById('typeFilter').value;
    const locationFilter = document.getElementById('locationFilter').value;
    const cards = document.querySelectorAll('[data-type][data-location]');
    
    cards.forEach(card => {
        const cardType = card.getAttribute('data-type');
        const cardLocation = card.getAttribute('data-location');
        
        const typeMatch = !typeFilter || cardType === typeFilter;
        const locationMatch = !locationFilter || cardLocation === locationFilter;
        
        if (typeMatch && locationMatch) {
            card.style.display = 'block';
            card.style.animation = 'fadeInUp 0.5s ease-out';
        } else {
            card.style.display = 'none';
        }
    });
}

function clearFilters() {
    document.getElementById('typeFilter').value = '';
    document.getElementById('locationFilter').value = '';
    
    const cards = document.querySelectorAll('[data-type][data-location]');
    cards.forEach(card => {
        card.style.display = 'block';
        card.style.animation = 'fadeInUp 0.5s ease-out';
    });
}

function updateFavoriteCount() {
    const visibleCards = document.querySelectorAll('[data-type][data-location]:not([style*="display: none"])').length;
    const countElement = document.querySelector('.favorites-count');
    if (countElement) {
        countElement.textContent = visibleCards;
    }
    
    // Show empty state if no favorites
    if (visibleCards === 0) {
        const container = document.querySelector('.row');
        if (container) {
            container.innerHTML = `
                <div class="empty-favorites">
                    <i class="fas fa-heart"></i>
                    <h4>Henüz favori ilanınız yok</h4>
                    <p>Beğendiğiniz ilanları favorilere ekleyerek buradan takip edebilirsiniz.</p>
                    <a href="../portfoy.php" class="btn btn-primary btn-lg">
                        <i class="fas fa-search me-2"></i>
                        İlan Ara
                    </a>
                </div>
            `;
        }
    }
}

function showNotification(message, type = 'info') {
    // Create notification element
    const notification = document.createElement('div');
    notification.className = `notification notification-${type}`;
    notification.innerHTML = `
        <div class="notification-content">
            <i class="fas fa-${type === 'success' ? 'check-circle' : type === 'error' ? 'exclamation-circle' : 'info-circle'}"></i>
            <span>${message}</span>
        </div>
    `;
    
    // Add styles
    notification.style.cssText = `
        position: fixed;
        top: 20px;
        right: 20px;
        background: ${type === 'success' ? '#d4edda' : type === 'error' ? '#f8d7da' : '#d1ecf1'};
        color: ${type === 'success' ? '#155724' : type === 'error' ? '#721c24' : '#0c5460'};
        border: 1px solid ${type === 'success' ? '#c3e6cb' : type === 'error' ? '#f5c6cb' : '#bee5eb'};
        border-radius: 8px;
        padding: 12px 20px;
        z-index: 1000;
        opacity: 0;
        transform: translateX(100%);
        transition: all 0.3s ease;
        max-width: 350px;
    `;
    
    document.body.appendChild(notification);
    
    // Show notification
    setTimeout(() => {
        notification.style.opacity = '1';
        notification.style.transform = 'translateX(0)';
    }, 100);
    
    // Hide notification after 3 seconds
    setTimeout(() => {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }, 3000);
}

// Remove notification
function removeNotification(notification) {
    if (notification && notification.parentNode) {
        notification.style.opacity = '0';
        notification.style.transform = 'translateX(100%)';
        setTimeout(() => {
            notification.remove();
        }, 300);
    }
}

// Loading States
function showLoading(element) {
    if (element) {
        element.classList.add('loading');
        element.disabled = true;
    }
}

function hideLoading(element) {
    if (element) {
        element.classList.remove('loading');
        element.disabled = false;
    }
}

// Smooth Scrolling
function smoothScrollTo(target, duration = 800) {
    const targetElement = document.querySelector(target);
    if (!targetElement) return;
    
    const start = window.pageYOffset;
    const targetPosition = targetElement.offsetTop - 100; // 100px offset
    const distance = targetPosition - start;
    let startTime = null;
    
    function animation(currentTime) {
        if (startTime === null) startTime = currentTime;
        const timeElapsed = currentTime - startTime;
        const run = ease(timeElapsed, start, distance, duration);
        window.scrollTo(0, run);
        
        if (timeElapsed < duration) {
            requestAnimationFrame(animation);
        }
    }
    
    function ease(t, b, c, d) {
        t /= d / 2;
        if (t < 1) return c / 2 * t * t + b;
        t--;
        return -c / 2 * (t * (t - 2) - 1) + b;
    }
    
    requestAnimationFrame(animation);
}

// Resize Handler
window.addEventListener('resize', function() {
    // Close sidebar on resize to desktop
    if (window.innerWidth > 768) {
        closeSidebar();
    }
});

// Keyboard Shortcuts
document.addEventListener('keydown', function(e) {
    // ESC to close sidebar
    if (e.key === 'Escape') {
        closeSidebar();
    }
    
    // Ctrl/Cmd + K for search (if implemented)
    if ((e.ctrlKey || e.metaKey) && e.key === 'k') {
        e.preventDefault();
        // Open search modal (implement if needed)
    }
});

// Export functions for global use
window.dashboardUtils = {
    toggleSidebar,
    closeSidebar,
    showNotification,
    removeNotification,
    showLoading,
    hideLoading,
    smoothScrollTo,
    animateNumber
};