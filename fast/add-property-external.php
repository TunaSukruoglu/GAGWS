<?php
// 🚀 EXTERNAL LOADED ULTRA FAST ADD PROPERTY
$start_time = microtime(true);

// Minimum PHP - sadece session kontrolü
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

$page_title = "🚀 External Ultra Fast Add Property";
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($page_title) ?></title>
    
    <!-- ⚡ DNS Prefetch -->
    <link rel="dns-prefetch" href="//cdnjs.cloudflare.com">
    <link rel="dns-prefetch" href="//fonts.googleapis.com">
    
    <!-- ⚡ Critical CSS Only -->
    <style>
        *{margin:0;padding:0;box-sizing:border-box}
        body{font-family:system-ui;background:#f5f5f5;opacity:0}
        .loading{position:fixed;top:50%;left:50%;transform:translate(-50%,-50%);font-size:24px;color:#667eea}
        .speed{position:fixed;top:10px;right:10px;background:#00ff88;color:#000;padding:5px 10px;border-radius:20px;font-size:12px;font-weight:bold;z-index:1000}
    </style>
</head>
<body>

<div class="speed" id="speed">⚡ LOADING...</div>
<div class="loading" id="loading">🚀 Ultra Fast Loading...</div>
<div id="main-content" style="display:none;"></div>

<!-- ⚡ Ultra Fast External Loading -->
<script>
const startTime = performance.now();

// 1. Hemen template yükle (5ms sonra)
setTimeout(() => {
    fetch('/fast/templates/add-property-template.html')
        .then(r => r.text())
        .then(html => {
            document.getElementById('main-content').innerHTML = html;
            document.getElementById('loading').style.display = 'none';
            document.getElementById('main-content').style.display = 'block';
            
            // Fade in effect
            document.body.style.opacity = '1';
            document.body.style.transition = 'opacity 0.3s ease-out';
            
            console.log('✅ Template loaded');
            
            // 2. CSS yükle (paralel)
            loadExternalCSS();
            
            // 3. JavaScript yükle (paralel)
            loadExternalJS();
            
            // 4. Form data yükle (paralel)
            loadFormData();
            
            // Performance display
            const loadTime = performance.now() - startTime;
            document.getElementById('speed').textContent = `⚡ ${Math.round(loadTime)}ms`;
            console.log('🚀 External Ultra Fast loaded in:', Math.round(loadTime) + 'ms');
        })
        .catch(e => {
            console.log('Template load failed:', e);
            // Fallback
            document.getElementById('loading').innerHTML = '❌ Loading failed';
        });
}, 5);

// External CSS loader
function loadExternalCSS() {
    const link = document.createElement('link');
    link.rel = 'stylesheet';
    link.href = '/fast/styles/add-property-styles.css';
    document.head.appendChild(link);
    console.log('📄 CSS loading...');
}

// External JS loader
function loadExternalJS() {
    const script = document.createElement('script');
    script.src = '/fast/scripts/add-property-functions.js';
    script.async = true;
    document.head.appendChild(script);
    console.log('⚙️ JS loading...');
}

// Form data loader
function loadFormData() {
    // Şehir verilerini yükle
    fetch('/fast/data/cities.json')
        .then(r => r.json())
        .then(cities => {
            const citySelect = document.querySelector('[name="city"]');
            if (citySelect) {
                cities.forEach(city => {
                    const option = document.createElement('option');
                    option.value = city.name;
                    option.textContent = city.name;
                    citySelect.appendChild(option);
                });
            }
            console.log('🏙️ Cities loaded');
        })
        .catch(e => console.log('Cities load failed:', e));
    
    // Property types yükle
    fetch('/fast/data/property-types.json')
        .then(r => r.json())
        .then(types => {
            const typeSelect = document.querySelector('[name="property_type"]');
            if (typeSelect) {
                types.forEach(type => {
                    const option = document.createElement('option');
                    option.value = type.value;
                    option.textContent = type.label;
                    typeSelect.appendChild(option);
                });
            }
            console.log('🏠 Property types loaded');
        })
        .catch(e => console.log('Property types load failed:', e));
}
</script>

<?php
$end_time = microtime(true);
$load_time = ($end_time - $start_time) * 1000;
echo "<!-- PHP Load Time: " . round($load_time, 2) . "ms -->";
?>

</body>
</html>
