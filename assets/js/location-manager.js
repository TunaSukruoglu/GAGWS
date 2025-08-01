// Location Management System for Turkish Cities
// Enhanced District and Neighborhood Loading

// İlçe ve Mahalle verilerini hemen yükle
window.addEventListener('DOMContentLoaded', function() {
    console.log('Early loading location data...');
    
    // Global location initialization function
    window.ensureLocationSelects = function() {
        const districtSelect = document.getElementById('district');
        const neighborhoodSelect = document.getElementById('neighborhood');
        
        console.log('Checking location selects...', {
            district: !!districtSelect, 
            neighborhood: !!neighborhoodSelect,
            districtOptions: districtSelect ? districtSelect.options.length : 0
        });
        
        if (districtSelect && neighborhoodSelect) {
            // Eğer ilçeler yüklenmemişse yükle
            if (districtSelect.options.length <= 1) {
                console.log('Loading districts...');
                
                // İlçe seçeneklerini yükle
                window.loadDistrictsToSelect(districtSelect);
                
                // İlçe değişiklik event'ini ekle
                if (!districtSelect.hasAttribute('data-listener-added')) {
                    districtSelect.addEventListener('change', function() {
                        window.loadNeighborhoodsToSelect(neighborhoodSelect, this.value);
                    });
                    districtSelect.setAttribute('data-listener-added', 'true');
                }
                
                return true;
            }
        }
        
        return false;
    };
    
    // Mutation observer ile element eklenmelerini izle
    const observer = new MutationObserver(function(mutations) {
        let shouldCheck = false;
        
        mutations.forEach(function(mutation) {
            if (mutation.addedNodes.length > 0) {
                for (let node of mutation.addedNodes) {
                    if (node.nodeType === 1) { // Element node
                        if (node.id === 'district' || node.id === 'neighborhood' || 
                            node.querySelector('#district') || node.querySelector('#neighborhood')) {
                            shouldCheck = true;
                            break;
                        }
                    }
                }
            }
        });
        
        if (shouldCheck) {
            console.log('Location elements detected, ensuring initialization...');
            setTimeout(() => window.ensureLocationSelects(), 100);
        }
    });
    
    // Tüm sayfayı observe et
    observer.observe(document.body, {
        childList: true,
        subtree: true
    });
    
    // İlk kontrol
    setTimeout(() => window.ensureLocationSelects(), 100);
    setTimeout(() => window.ensureLocationSelects(), 500);
    setTimeout(() => window.ensureLocationSelects(), 1000);
});

// İstanbul İlçeleri
const istanbulDistricts = [
    'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 
    'Bahçelievler', 'Bakırköy', 'Başakşehir', 'Bayrampaşa', 'Beşiktaş',
    'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca',
    'Çekmeköy', 'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih',
    'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kağıthane', 'Kartal',
    'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer',
    'Silivri', 'Sultanbeyli', 'Sultangazi', 'Şile', 'Şişli',
    'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'
];

// İlçelere göre mahalleler (Sadece en popüler ilçeler için)
const istanbulNeighborhoods = {
    'Beyoğlu': [
        'Asmalımescit', 'Bereketzade', 'Bülbül', 'Camekan', 'Cihangir',
        'Evliya Çelebi', 'Galipdede', 'Hacı Mimi', 'Halep', 'Hüseyinağa',
        'Kalyoncu Kulluk', 'Katip Mustafa Çelebi', 'Kılıçali Paşa', 'Kocatepe',
        'Kuledibi', 'Kuloğlu', 'Müeyyedzade', 'Ömeravni', 'Piyalepaşa',
        'Sahkulu', 'Sütlüce', 'Şehit Muhtar', 'Tomtom', 'Yenişehir'
    ],
    'Kadıköy': [
        'Acıbadem', 'Bostancı', 'Caferağa', 'Caddebostan', 'Erenköy',
        'Fenerbahçe', 'Feneryolu', 'Fikirtepe', 'Göztepe', 'Hasanpaşa',
        'İçerenköy', 'Jiyanyolu', 'Kalaköy', 'Koşuyolu', 'Merdivenkoy',
        'Moda', 'Osmanağa', 'Rasimpaşa', 'Sahrayıcedid', 'Selamicesme',
        'Suadiye', 'Zühtüpaşa'
    ],
    'Şişli': [
        'Bozkurt', 'Cumhuriyet', 'Elmadağ', 'Eskişehir', 'Feriköy',
        'Halaskargazi', 'Harbiye', 'İnönü', 'Kaptanpaşa', 'Kuştepe',
        'Mahmut Şevket Paşa', 'Mecidiyeköy', 'Merkez', 'Meşrutiyet',
        'Pangaltı', 'Teşvikiye', '19 Mayıs'
    ],
    'Beşiktaş': [
        'Abbasağa', 'Arnavutköy', 'Bebek', 'Cihannüma', 'Dikilitaş',
        'Etiler', 'Gayrettepe', 'Konaklar', 'Kuruçeşme', 'Levent',
        'Muradiye', 'Nisbetiye', 'Ortaköy', 'Sinanpaşa', 'Ulus',
        'Vişnezade', 'Yıldız'
    ],
    'Üsküdar': [
        'Acıbadem', 'Altunizade', 'Barbaros', 'Beylerbeyi', 'Bulgurlu',
        'Burhaniye', 'Çengelköy', 'İcadiye', 'Kandilli', 'Kısıklı',
        'Kuleli', 'Küçük Çamlıca', 'Küçüksu', 'Kuzguncuk', 'Mimar Sinan',
        'Selami Ali', 'Sultantepe', 'Şemsi Paşa', 'Valide-i Atik', 'Yavuztürk'
    ],
    'Fatih': [
        'Aksaray', 'Atikali', 'Beyazıt', 'Binbirdirek', 'Balabanağa',
        'Cerrahpaşa', 'Dervişali', 'Eminönü', 'Hacıkadın', 'Haseki Sultan',
        'Hirkai Şerif', 'İskenderpaşa', 'Kalenderhane', 'Katip Kasım',
        'Kemalpaşa', 'Koca Mustafapaşa', 'Küçük Ayasofya', 'Mercan',
        'Molla Fenari', 'Nişanca', 'Rüstempaşa', 'Seyyid Ömer',
        'Silivrikapı', 'Sultan Ahmet', 'Süleymaniye', 'Şehremini',
        'Zeyrek'
    ],
    'Bakırköy': [
        'Ataköy 1. Kısım', 'Ataköy 2-5-6. Kısım', 'Ataköy 3. Kısım',
        'Ataköy 4. Kısım', 'Ataköy 7-8-9-10. Kısım', 'Florya',
        'Kartaltepe', 'Osmaniye', 'Sakızağacı', 'Şenlikköy',
        'Veliefendi', 'Yeşilköy', 'Yenimahalle', 'Zuhuratbaba'
    ],
    'Sarıyer': [
        'Ayazağa', 'Bahçeköy', 'Büyükdere', 'Emirgan', 'Ferahevler',
        'Huzur', 'İstinye', 'Kireçburnu', 'Maslak', 'Reşitpaşa',
        'Rumeli Kavağı', 'Rumeli Hisarı', 'Tarabya', 'Yeniköy',
        'Zekeriyaköy'
    ],
    'Esenyurt': [
        'Akevler', 'Akşemsettin', 'Ardıçlı', 'Bağlarçeşme', 'Barbaros',
        'Batıkent', 'Cumhuriyet', 'Fatih', 'Fevzi Çakmak', 'Güzelyurt',
        'Haramidere', 'Hoşdere', 'İnönü', 'Kıraç', 'Mehmet Akif',
        'Menderes', 'Namık Kemal', 'Orhan Gazi', 'Orhangazi',
        'Pınar', 'Saadetdere', 'Süleymaniye', 'Şakir Kesebir',
        'Turgut Reis', 'Yenikent', 'Zafer'
    ],
    'Ataşehir': [
        'Ataşehir', 'Barbaros', 'Esatpaşa', 'Ferhatpaşa', 'İçerenköy',
        'İnönü', 'Kayışdağı', 'Küçükbakkalköy', 'Mimar Sinan',
        'Mustafa Kemal', 'Örnek', 'Yenisahra'
    ],
    'Başakşehir': [
        'Altınsehir', 'Bahçesehir 1. Kısım', 'Bahçesehir 2. Kısım',
        'Bahçesehir 3. Kısım', 'Başak', 'Başakşehir', 'Güvercintepe',
        'Kayabaşı', 'Şahintepe', 'Şamlar', 'Ziya Gökalp'
    ]
};

// İlçe seçeneklerini yükle
window.loadDistrictsToSelect = function(selectElement) {
    console.log('Loading districts to select element');
    
    // Mevcut edit mode değerini koru
    const currentValue = selectElement.value;
    
    // Seçenekleri temizle (ilk option dışında)
    while (selectElement.options.length > 1) {
        selectElement.removeChild(selectElement.lastChild);
    }
    
    // İstanbul ilçelerini ekle
    istanbulDistricts.forEach(district => {
        const option = document.createElement('option');
        option.value = district;
        option.textContent = district;
        
        // Edit mode'da seçili değeri koru
        if (currentValue && currentValue === district) {
            option.selected = true;
        }
        
        selectElement.appendChild(option);
    });
    
    console.log('Loaded', istanbulDistricts.length, 'districts');
    
    // Eğer edit mode'da bir değer varsa, mahallelerinide yükle
    if (currentValue && istanbulNeighborhoods[currentValue]) {
        const neighborhoodSelect = document.getElementById('neighborhood');
        if (neighborhoodSelect) {
            window.loadNeighborhoodsToSelect(neighborhoodSelect, currentValue);
        }
    }
};

// Mahalle seçeneklerini yükle
window.loadNeighborhoodsToSelect = function(selectElement, district) {
    console.log('Loading neighborhoods for district:', district);
    
    // Mevcut edit mode değerini koru
    const currentValue = selectElement.value;
    
    // Seçenekleri temizle (ilk option dışında)
    while (selectElement.options.length > 1) {
        selectElement.removeChild(selectElement.lastChild);
    }
    
    // İlçenin mahallelerini ekle
    const neighborhoods = istanbulNeighborhoods[district] || [];
    
    neighborhoods.forEach(neighborhood => {
        const option = document.createElement('option');
        option.value = neighborhood;
        option.textContent = neighborhood;
        
        // Edit mode'da seçili değeri koru
        if (currentValue && currentValue === neighborhood) {
            option.selected = true;
        }
        
        selectElement.appendChild(option);
    });
    
    console.log('Loaded', neighborhoods.length, 'neighborhoods for', district);
};
