// İstanbul İlçe ve Mahalle verileri
const istanbulData = {
    districts: [
        'Adalar', 'Arnavutköy', 'Ataşehir', 'Avcılar', 'Bağcılar', 'Bahçelievler', 'Bakırköy', 'Başakşehir',
        'Bayrampaşa', 'Beşiktaş', 'Beykoz', 'Beylikdüzü', 'Beyoğlu', 'Büyükçekmece', 'Çatalca', 'Çekmeköy',
        'Esenler', 'Esenyurt', 'Eyüpsultan', 'Fatih', 'Gaziosmanpaşa', 'Güngören', 'Kadıköy', 'Kâğıthane',
        'Kartal', 'Küçükçekmece', 'Maltepe', 'Pendik', 'Sancaktepe', 'Sarıyer', 'Silivri', 'Sultanbeyli',
        'Şile', 'Şişli', 'Tuzla', 'Ümraniye', 'Üsküdar', 'Zeytinburnu'
    ],
    
    neighborhoods: {
        'Kadıköy': [
            'Acıbadem', 'Bostancı', 'Caferağa', 'Caddebostan', 'Erenköy', 'Fenerbahçe', 'Feneryolu',
            'Fikirtepe', 'Göztepe', 'Hasanpaşa', 'İçerenköy', 'Kadıköy', 'Khalkedon', 'Koşuyolu',
            'Kozyatağı', 'Merdivenköy', 'Moda', 'Osmanağa', 'Rasimpaşa', 'Sahrayıcedit', 'Suadiye',
            'Zühtüpaşa'
        ],
        'Beşiktaş': [
            'Abbasağa', 'Akatlar', 'Arnavutköy', 'Bebek', 'Beşiktaş', 'Dikilitaş', 'Etiler', 'Gayrettepe',
            'Konaklar', 'Kuruçeşme', 'Levent', 'Muradiye', 'Nisbetiye', 'Ortaköy', 'Sinanpaşa',
            'Türkali', 'Ulus', 'Vişnezade', 'Yıldız'
        ],
        'Şişli': [
            'Ayazağa', 'Bozkurt', 'Cumhuriyet', 'Esentepe', 'Eskişehir', 'Feriköy', 'Fulya', 'Gayrettepe',
            'Gültepe', 'Halaskargazi', 'Halide Edip Adıvar', 'Harbiye', 'İnönü', 'İzzetpaşa', 'Kağıthane',
            'Kuştepe', 'Mahmut Şevket Paşa', 'Maslak', 'Mecidiyeköy', 'Merkez', 'Meşrutiyet', 'Nisantaşı',
            'Okmeydanı', 'Osmanbey', 'Pangaltı', 'Paşa', 'Rumeli', 'Şişli', 'Teşvikiye', 'Yayla'
        ],
        'Üsküdar': [
            'Acıbadem', 'Altunizade', 'Barbaros', 'Beylerbeyi', 'Bülbülderesi', 'Burhaniye', 'Çengelköy',
            'Ferah', 'Güzeltepe', 'İcadiye', 'Kandilli', 'Kısıklı', 'Küçük Çamlıca', 'Küçüksu',
            'Kuleli', 'Kuzguncuk', 'Libadiye', 'Mahmut Sevket Paşa', 'Murat Reis', 'Nuhkuyusu',
            'Salacak', 'Selamiali', 'Selimiye', 'Sultantepe', 'Şemsipaşa', 'Uncular', 'Üsküdar',
            'Validei Atik', 'Valide-i Cedid', 'Vaniköy'
        ],
        'Fatih': [
            'Aksaray', 'Alemdar', 'Ali Kuşçu', 'Atikali', 'Ayvansaray', 'Balat', 'Beyazıt', 'Binbirdirek',
            'Cankurtaran', 'Cerrahpaşa', 'Davutpaşa', 'Dervişali', 'Eminönü', 'Fener', 'Hırka-i Şerif',
            'Hobyar', 'İskenderpaşa', 'Kalenderhane', 'Karagümrük', 'Katip Kasım', 'Kemalpaşa', 'Koca Mustafa Paşa',
            'Küçük Ayasofya', 'Mercan', 'Mesih Paşa', 'Mimar Hayrettin', 'Mimar Kemalettin', 'Molla Fenari',
            'Molla Hüsrev', 'Molla Gürani', 'Nisanca', 'Rüstem Paşa', 'Sarıdemir', 'Seyyid Ömer', 'Silivrikapı',
            'Süleymaniye', 'Şehremini', 'Şehsuvar Bey', 'Tahtakale', 'Topkapı', 'Yavuz Sultan Selim',
            'Yedikule', 'Zeyrek'
        ],
        'Beyoğlu': [
            'Asmalımescit', 'Bedrettin', 'Bereketzade', 'Billurcu', 'Bostan', 'Bülbül', 'Camiikebir',
            'Cankurtaran', 'Çatma Mescid', 'Çukur', 'Evliya Çelebi', 'Fetih Çelebi', 'Firuzağa',
            'Galatasaray', 'Gümüşsuyu', 'Hacımimi', 'Halep', 'Hasköy', 'Hüseyinağa', 'İstiklal',
            'Kamerhatun', 'Kaptanpaşa', 'Karaköy', 'Katip Mustafa Çelebi', 'Keçecipiri', 'Kemankeş',
            'Kılıçalipaşa', 'Kulaksız', 'Kurtulus', 'Kuloğlu', 'Mesrutiyet', 'Müeyyedzade', 'Ömer Avni',
            'Orhaniye', 'Piri Paşa', 'Piyalepaşa', 'Postacı', 'Şahkulu', 'Şişhane', 'Sururi',
            'Sütlüce', 'Tahta Minare', 'Tomtom', 'Yağ Kapanı', 'Yeniköy', 'Yenişehir'
        ],
        'Bakırköy': [
            'Ataköy', 'Bahçelievler', 'Bakırköy', 'Basınköy', 'Cevizlik', 'Eskiköy', 'Florya',
            'İncirli', 'Kartaltepe', 'Osmaniye', 'Sakızağacı', 'Şenlikköy', 'Yeşilköy', 'Yeşilyurt',
            'Zuhuratbaba'
        ],
        'Sarıyer': [
            'Ayazağa', 'Bahçeköy', 'Büyükdere', 'Çayırbaşı', 'Demirciköy', 'Derbent', 'Emirgan',
            'Ferahevler', 'Garipçe', 'Hisarüstü', 'İstinye', 'Kireçburnu', 'Kilyos', 'Kumköy',
            'Maslak', 'Mesar', 'Polenezköy', 'Rumelihisarı', 'Tarabya', 'Uskumruköy', 'Yenimahalle',
            'Yenikoy', 'Zekeriyaköy'
        ]
        // Diğer ilçeler için de eklenecek...
    }
};

// Dropdown kontrolü
document.addEventListener('DOMContentLoaded', function() {
    const citySelect = document.getElementById('city');
    const districtSelect = document.getElementById('district');
    const neighborhoodSelect = document.getElementById('neighborhood');
    
    // İlçeleri yükle
    function loadDistricts() {
        districtSelect.innerHTML = '<option value="">İlçe Seçiniz</option>';
        istanbulData.districts.forEach(district => {
            const option = document.createElement('option');
            option.value = district;
            option.textContent = district;
            districtSelect.appendChild(option);
        });
    }
    
    // Mahalleleri yükle
    function loadNeighborhoods(district) {
        neighborhoodSelect.innerHTML = '<option value="">Mahalle Seçiniz</option>';
        
        if (istanbulData.neighborhoods[district]) {
            istanbulData.neighborhoods[district].forEach(neighborhood => {
                const option = document.createElement('option');
                option.value = neighborhood;
                option.textContent = neighborhood;
                neighborhoodSelect.appendChild(option);
            });
        }
    }
    
    // İlçe değişince mahalleleri yükle
    districtSelect.addEventListener('change', function() {
        loadNeighborhoods(this.value);
    });
    
    // Sayfa yüklenince ilçeleri yükle
    loadDistricts();
    
    // Edit mode için değerleri set et
    <?php if ($edit_mode): ?>
    setTimeout(function() {
        <?php if (isset($existing_property['district'])): ?>
        const districtValue = '<?= addslashes($existing_property['district'] ?? '') ?>';
        if (districtValue) {
            districtSelect.value = districtValue;
            loadNeighborhoods(districtValue);
            
            <?php if (isset($existing_property['neighborhood'])): ?>
            setTimeout(function() {
                const neighborhoodValue = '<?= addslashes($existing_property['neighborhood'] ?? '') ?>';
                if (neighborhoodValue) {
                    neighborhoodSelect.value = neighborhoodValue;
                }
            }, 100);
            <?php endif; ?>
        }
        <?php endif; ?>
    }, 100);
    <?php endif; ?>
});
