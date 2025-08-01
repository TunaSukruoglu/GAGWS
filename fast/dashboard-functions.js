// Fast Dashboard Functions - Dışarıdan Yüklenir

console.log('🚀 Dashboard functions loaded');

// Live Clock
function startClock() {
    function updateTime() {
        const now = new Date();
        const timeStr = now.toLocaleTimeString('tr-TR');
        const timeElement = document.getElementById('live-time');
        if (timeElement) {
            timeElement.textContent = '⏰ ' + timeStr;
        }
    }
    
    updateTime();
    setInterval(updateTime, 1000);
}

// Stats Counter Animation
function animateCounters() {
    const counters = document.querySelectorAll('[id^="stat-"]');
    
    counters.forEach(counter => {
        const target = parseInt(counter.textContent) || 0;
        if (target === 0) return;
        
        const increment = target / 20;
        let current = 0;
        
        const timer = setInterval(() => {
            current += increment;
            if (current >= target) {
                counter.textContent = target.toLocaleString('tr-TR');
                clearInterval(timer);
            } else {
                counter.textContent = Math.floor(current).toLocaleString('tr-TR');
            }
        }, 50);
    });
}

// Performance Monitoring
function logPerformance() {
    if (window.performance && window.performance.timing) {
        const perf = window.performance.timing;
        const loadTime = perf.loadEventEnd - perf.navigationStart;
        
        const loadTimeElement = document.getElementById('load-time');
        if (loadTimeElement) {
            loadTimeElement.textContent = `Yükleme Süresi: ${loadTime}ms`;
        }
        
        console.log('📊 Performance:', {
            total: loadTime + 'ms',
            dom: (perf.domContentLoadedEventEnd - perf.navigationStart) + 'ms',
            network: (perf.responseEnd - perf.fetchStart) + 'ms'
        });
    }
}

// Auto-refresh Stats
function autoRefreshStats() {
    setInterval(() => {
        fetch('/get-dashboard-stats.php')
            .then(r => r.json())
            .then(stats => {
                document.getElementById('stat-properties').textContent = stats.properties || '0';
                document.getElementById('stat-users').textContent = stats.users || '0';
                document.getElementById('stat-agencies').textContent = stats.agencies || '0';
                document.getElementById('stat-messages').textContent = stats.messages || '0';
                
                // Animate after update
                setTimeout(animateCounters, 100);
            })
            .catch(e => console.log('Stats refresh failed:', e));
    }, 30000); // 30 saniyede bir
}

// Initialize when DOM is ready
document.addEventListener('DOMContentLoaded', function() {
    console.log('🎯 Fast Dashboard initialized');
    
    // Start functions
    setTimeout(() => {
        startClock();
        animateCounters();
        autoRefreshStats();
        logPerformance();
    }, 100);
});

// Fast Navigation
function fastNavigate(url) {
    const startTime = performance.now();
    
    window.location.href = url;
    
    // Log navigation time
    window.addEventListener('load', function() {
        const navTime = performance.now() - startTime;
        console.log('🚀 Navigation time:', navTime.toFixed(2) + 'ms');
    });
}

// Error Handling
window.addEventListener('error', function(e) {
    console.error('Dashboard Error:', e.error);
});

// Memory Usage Monitoring
function logMemoryUsage() {
    if (window.performance && window.performance.memory) {
        const memory = window.performance.memory;
        console.log('📈 Memory Usage:', {
            used: Math.round(memory.usedJSHeapSize / 1024 / 1024) + ' MB',
            total: Math.round(memory.totalJSHeapSize / 1024 / 1024) + ' MB',
            limit: Math.round(memory.jsHeapSizeLimit / 1024 / 1024) + ' MB'
        });
    }
}
