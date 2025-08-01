<?php
// Hata yakalama ve debug
error_reporting(E_ALL);
ini_set('display_errors', 1);

// Session başlat
if (session_status() == PHP_SESSION_NONE) {
    session_start();
}

// Kullanıcı giriş kontrolü
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? ($_SESSION['user_name'] ?? 'Kullanıcı') : '';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
	<meta charset="UTF-8">
	<meta name="keywords" content="Ofis kiralama sözleşmesi, Ticari kira, Sözleşme maddeleri, Gökhan Aydınlı">
	<meta name="description" content="Ofis kiralama sözleşmesinde mutlaka bulunması gereken temel maddeler ve dikkat edilmesi gereken hukuki detaylar.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul Blog">
    <meta property="og:url" content="https://gokhanaydnli.com/blog/ofis-sozlesme-maddeleri">
    <meta property="og:type" content="article">
    <meta property="og:title" content="Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler">
	<meta name='og:image' content='images/blog/ofis-sozlesme.jpg'>
	<!-- For IE -->
	<meta http-equiv="X-UA-Compatible" content="IE=edge">
	<!-- For Resposive Device -->
	<meta name="viewport" content="width=device-width, initial-scale=1.0">
	<!-- For Window Tab Color -->
	<!-- Chrome, Firefox OS and Opera -->
	<meta name="theme-color" content="#0D1A1C">
	<!-- Windows Phone -->
	<meta name="msapplication-navbutton-color" content="#0D1A1C">
	<!-- iOS Safari -->
	<meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
	<title>Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler - Gökhan Aydınlı Blog</title>
	<!-- Favicon -->
	<link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
	<!-- Bootstrap CSS -->
	<link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
	<!-- Main style sheet -->
	<link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
	<!-- responsive style sheet -->
	<link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
	
	<!-- Fallback CSS -->
	<style>
		.main-page-wrapper { min-height: 100vh; }
		.container { max-width: 1200px; margin: 0 auto; padding: 0 15px; }
		body { font-family: Arial, sans-serif; line-height: 1.6; }
	</style>

	<!-- Fix Internet Explorer ______________________________________-->
	<!--[if lt IE 9]>
			<script src="http://html5shiv.googlecode.com/svn/trunk/html5.js"></script>
			<script src="vendor/html5shiv.js"></script>
			<script src="vendor/respond.js"></script>
		<![endif]-->
</head>

<body>
	<div class="main-page-wrapper">
		<!-- ===================================================
			Loading Transition
		==================================================== -->
		<div id="preloader">
			<div id="ctn-preloader" class="ctn-preloader">
				<div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
			</div>
		</div>


		
		<!-- ################### Login Modal ####################### -->
        <!-- Modal -->
        <div class="modal fade" id="searchModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="modal-content d-flex justify-content-center">
                    <form action="#">
                        <input type="text" placeholder="Blog yazıları ara...">
                        <button><i class="fa-light fa-arrow-right-long"></i></button>
                    </form>
                </div>
            </div>
        </div>


		
		<!-- 
		=============================================
			Theme Main Menu
		============================================== 
		-->
		<header class="theme-main-menu menu-overlay menu-style-one sticky-menu">
		<div class="inner-content gap-one">
				<div class="top-header position-relative">
					<div class="d-flex align-items-center justify-content-between">
						<div class="logo order-lg-0">
							<a href="index.php" class="d-flex align-items-center">
								<img src="images/logoSiyah.png" alt="">
							</a>
						</div>
						<!-- logo -->
						<div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
							<ul class="d-flex align-items-center style-none">
								<?php if ($isLoggedIn): ?>
                                    <li><a href="logout.php" class="btn-one"><i class="fa-regular fa-lock"></i> <span>Çıkış</span></a></li>
                                    <li class="d-none d-md-inline-block ms-3">
                                        <a href="dashboard/dashboard-admin.php" class="btn-two"><span>Panel</span> <i class="fa-thin fa-arrow-up-right"></i></a>
                                    </li>
                                <?php else: ?>
                                    <li><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one"><i class="fa-regular fa-lock"></i> <span>Giriş</span></a></li>
                                    <li class="d-none d-md-inline-block ms-3">
                                        <a href="register.php" class="btn-two"><span>Üye Ol</span> <i class="fa-thin fa-arrow-up-right"></i></a>
                                    </li>
                                <?php endif; ?>
							</ul>
						</div>
						<nav class="navbar navbar-expand-lg p0 order-lg-2">
							<button class="navbar-toggler d-block d-lg-none" type="button" data-bs-toggle="collapse"
								data-bs-target="#navbarNav" aria-controls="navbarNav" aria-expanded="false"
								aria-label="Toggle navigation">
								<span></span>
							</button>
							<div class="collapse navbar-collapse" id="navbarNav">
								<ul class="navbar-nav align-items-lg-center">
									<li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="index.php">Ana Sayfa</a>
									</li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
									</li>
                                    <li class="nav-item dropdown">
										<a class="nav-link" href="portfoy.php">Portföy</a>
									</li>
                                    <li class="nav-item dashboard-menu">
										<a class="nav-link" href="blog.php">Blog</a>
									</li>
                                    <li class="nav-item dropdown">
										<a class="nav-link" href="contact.php">İletişim</a>
									</li>
                                    <li class="nav-item dropdown">
										<a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
									</li>
									<li class="d-md-none ps-2 pe-2 mt-20">
										<a href="dashboard/add-property.html" class="btn-two w-100" target="_blank"><span>Add Listing</span> <i class="fa-thin fa-arrow-up-right"></i></a>
									</li>
								</ul>
							</div>
						</nav>
					</div>
				</div> <!--/.top-header-->
			</div> <!-- /.inner-content -->
		</header> 



                <!-- ============================
            İç Banner
        ============================ -->
                <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Blog</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="blog.php">Blog Anasayfa</a></li>
     
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>


		<!-- /.theme-main-menu -->


	

		<!--
		=====================================================
			Blog Details
		=====================================================
		-->
		<div class="blog-details border-top mt-130 xl-mt-100 pt-100 xl-pt-80 mb-150 xl-mb-100">
			<div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <div class="blog-post-meta mb-60 lg-mb-40">
                            <div class="post-info"><a href="hakkimizda.php">Gökhan Aydınlı .</a> 10 dk okuma</div>
                            <h3 class="blog-title">Ofis Kiralama Sözleşmesinde Bulunması Gereken Temel Maddeler</h3>
                        </div>
                    </div>
                </div>
				<div class="row gx-xl-5">
                    <div class="col-lg-8">
                        <article class="blog-post-meta">
                            <figure class="post-img position-relative m0" style="background-image: url(https://images.unsplash.com/photo-1450101499163-c8848c66ca85?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80);">
                                <div class="fw-500 date d-inline-block">22 OCA</div>
                            </figure>
                            <div class="post-data pt-50 md-pt-30">
                                <p>Ofis kiralama sözleşmesi, işletmenizin geleceğini şekillendirecek en önemli belgelerden biridir. 17 yıllık gayrimenkul deneyimim boyunca, birçok işletme sahibinin sözleşme detaylarını yeterince incelemediği için yaşadığı sorunlara şahit oldum. Bu yazıda, ofis kiralama sözleşmesinde mutlaka bulunması gereken temel maddeleri ve dikkat edilmesi gereken hukuki detayları sizlerle paylaşıyorum.</p>
                                
                                <p>Ticari kira sözleşmeleri, Türk Borçlar Kanunu'na göre düzenlenir ve konut kiralarından önemli farklarla ayrılır. Bu farkları bilmemek, gelecekte büyük mali kayıplara yol açabilir.</p>
                                
                                <div class="quote-wrapper">
                                    <div class="icon rounded-circle d-flex align-items-center justify-content-center m-auto"><img src="images/lazy.svg" data-src="images/icon/icon_67.svg" alt="" class="lazy-img"></div>
                                    <div class="row">
                                        <div class="col-xxl-10 col-xl-11 col-lg-12 col-md-9 m-auto">
                                            <h4>"İyi hazırlanmış bir sözleşme, gelecekteki anlaşmazlıkların %90'ını önler. Detaylar şeytanda gizlidir."</h4>
                                        </div>
                                    </div>
                                    <h6>Gökhan Aydınlı. <span>Gayrimenkul Uzmanı</span></h6>
                                </div>
                                
                                <h5>1. Taraf Bilgileri ve Kimlik Doğrulama</h5>
                                <p>Sözleşmenin ilk ve en kritik bölümü, tarafların doğru ve eksiksiz bilgilerini içermelidir. Bu bilgilerin doğruluğu, gelecekte yaşanabilecek hukuki süreçlerin temelini oluşturur.</p>
                                
                                <ul class="style-none list-item">
                                    <li><strong>Kiralayan için:</strong> TC kimlik no, adres, telefon, e-posta</li>
                                    <li><strong>Kiracı için:</strong> Şirket unvanı, vergi no, ticaret sicil no, adres</li>
                                    <li><strong>Yetkili kişi bilgileri:</strong> İmza yetkilisi, temsil kapsamı</li>
                                    <li><strong>İletişim bilgileri:</strong> Yazışma adresi, acil durum iletişimi</li>
                                </ul>
                                
                                <h5>2. Gayrimenkulün Tanımı ve Sınırları</h5>
                                <p>Kiralanan ofis alanının net bir şekilde tanımlanması, gelecekteki uyuşmazlıkları önlemek açısından kritik önem taşır.</p>
                                
                                <p><strong>Sözleşmede yer alması gereken tanımlar:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Gayrimenkulün tam adresi ve ada-parsel bilgileri</li>
                                    <li>Kiralanan alanın metrekaresi (brüt ve net)</li>
                                    <li>Hangi bölümlerin kiraladığı (ofis, depo, otopark)</li>
                                    <li>Ortak kullanım alanları ve hakları</li>
                                    <li>Zemin planı ve sınırlar</li>
                                </ul>
                                
                                <h5>3. Kira Bedeli ve Ödeme Koşulları</h5>
                                <p>Mali konular sözleşmenin en detaylı şekilde düzenlenmesi gereken bölümüdür. Belirsizlik, her iki taraf için de sorun yaratabilir.</p>
                                
                                <div class="img-meta"><img src="https://images.unsplash.com/photo-1554224155-6726b3ff858f?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2026&q=80" alt="Kira bedeli hesaplama" class="lazy-img w-100"></div>
                                <div class="img-caption">Kira bedeli ve ödeme koşulları net bir şekilde belirtilmelidir</div>
                                
                                <p><strong>Kira bedeli ile ilgili düzenlemeler:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Aylık kira miktarı (KDV dahil/hariç belirtilmeli)</li>
                                    <li>Ödeme tarihi ve şekli</li>
                                    <li>Gecikme faizi oranı</li>
                                    <li>Kira artış oranı ve periyodu</li>
                                    <li>Depozito miktarı ve iade koşulları</li>
                                    <li>Peşin ödeme indirimleri</li>
                                </ul>
                                
                                <h5>4. Sözleşme Süresi ve Yenileme Koşulları</h5>
                                <p>Ticari kira sözleşmelerinde süre düzenlemeleri, konut kiralarından farklı özellikler gösterir. Bu farkları bilmek çok önemlidir.</p>
                                
                                <p><strong>Süre ile ilgili maddeler:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Sözleşme başlangıç ve bitiş tarihi</li>
                                    <li>Otomatik yenileme koşulları</li>
                                    <li>Erken fesih hakları ve koşulları</li>
                                    <li>Fesih bildirim süreleri</li>
                                    <li>Sözleşme uzatma opsiyonları</li>
                                </ul>
                                
                                <h5>5. Kullanım Amacı ve Kısıtlamalar</h5>
                                <p>Ofis alanının hangi amaçlarla kullanılabileceği ve kısıtlamaların net olarak belirtilmesi gerekir.</p>
                                
                                <ul class="style-none list-item">
                                    <li>İzin verilen iş kolları ve faaliyetler</li>
                                    <li>Yasaklanan kullanım şekilleri</li>
                                    <li>Çalışma saatleri ve kısıtlamalar</li>
                                    <li>Ses ve koku emisyon limitleri</li>
                                    <li>Alt kiralama hakları</li>
                                </ul>
                                
                                <h5>6. Tadilat ve Değişiklik Hakları</h5>
                                <p>İş yerinin ihtiyaçlara göre düzenlenmesi konusunda tarafların hakları net olarak belirlenmelidir.</p>
                                
                                <p><strong>Tadilat konularında düzenleme:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Kiracının yapabileceği tadilat türleri</li>
                                    <li>İzin gerektiren değişiklikler</li>
                                    <li>Tadilat masraflarının sorumluluğu</li>
                                    <li>Sözleşme bitiminde iade durumu</li>
                                    <li>Mimar ve mühendis onay gereklilikleri</li>
                                </ul>
                                
                                <h5>7. Sorumluluk ve Sigorta Düzenlemeleri</h5>
                                <p>Risk yönetimi açısından sorumluluk paylaşımının net bir şekilde belirlenmesi kritik önem taşır.</p>
                                
                                <ul class="style-none list-item">
                                    <li>Yangın, su baskını, hırsızlık sorumlulukları</li>
                                    <li>Sigorta poliçesi gereklilikleri</li>
                                    <li>Üçüncü şahıslara karşı sorumluluk</li>
                                    <li>İş kazası ve meslek hastalığı sorumluluğu</li>
                                    <li>Doğal afet durumunda yükümlülükler</li>
                                </ul>
                                
                                <h5>8. Gider Paylaşımı ve Ek Masraflar</h5>
                                <p>Kira bedeli dışındaki masrafların hangi tarafça karşılanacağı konusu detaylandırılmalıdır.</p>
                                
                                <div class="img-meta"><img src="https://images.unsplash.com/photo-1586953208448-b95a79798f07?ixlib=rb-4.0.3&ixid=M3wxMjA3fDB8MHxwaG90by1wYWdlfHx8fGVufDB8fHx8fA%3D%3D&auto=format&fit=crop&w=2070&q=80" alt="Ofis giderleri" class="lazy-img w-100"></div>
                                <div class="img-caption">Gider paylaşımı konuları önceden net şekilde belirlenmelidir</div>
                                
                                <p><strong>Gider kalemleri:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Elektrik, su, doğalgaz faturaları</li>
                                    <li>Apartman aidatı ve yönetim giderleri</li>
                                    <li>Temizlik ve güvenlik hizmetleri</li>
                                    <li>Asansör bakım ve onarım giderleri</li>
                                    <li>İnternet ve telefon altyapı maliyetleri</li>
                                    <li>Emlak vergisi ödemeleri</li>
                                </ul>
                                
                         <h5>9. Bakım ve Onarım Yükümlülükleri</h5>
                                <p>Gayrimenkulün bakım ve onarımı konusunda tarafların sorumluluklarının net ayrımı yapılmalıdır.</p>
                                
                                <p><strong>Bakım-onarım sorumlulukları:</strong></p>
                                <ul class="style-none list-item">
                                    <li><strong>Kiralayan sorumluluğu:</strong> Yapısal onarımlar, çatı, dış cephe</li>
                                    <li><strong>Kiracı sorumluluğu:</strong> Günlük bakım, boyama, iç mekan onarımları</li>
                                    <li><strong>Ortak sorumluluk:</strong> Asansör, kalorifer sistemi, elektrik tesisatı</li>
                                    <li><strong>Acil müdahale:</strong> Su kaçağı, elektrik arızası gibi durumlar</li>
                                    <li><strong>Periyodik bakım:</strong> Klima, havalandırma sistemleri</li>
                                </ul>
                                
                                <h5>10. Fesih ve Tahliye Koşulları</h5>
                                <p>Sözleşmenin sona ermesi durumunda izlenecek prosedürler önceden belirlenmelidir.</p>
                                
                                <ul class="style-none list-item">
                                    <li>Normal fesih bildirim süreleri (6 ay önceden)</li>
                                    <li>Haklı sebeplerle fesih durumları</li>
                                    <li>Tahliye süreleri ve koşulları</li>
                                    <li>Depozito iade prosedürü</li>
                                    <li>Teslim durumu tespit tutanağı</li>
                                    <li>Gecikme halinde tazminat düzenlemeleri</li>
                                </ul>
                                
                                <h5>11. İhtilaf Çözümü ve Yargı Yetkisi</h5>
                                <p>Uyuşmazlık durumunda çözüm yolları ve yetkili mahkemeler belirtilmelidir.</p>
                                
                                <ul class="style-none list-item">
                                    <li>Önce dostane çözüm arayışı</li>
                                    <li>Arabuluculuk ve tahkim seçenekleri</li>
                                    <li>Yetkili mahkeme belirlenmesi</li>
                                    <li>İcra takibi prosedürleri</li>
                                    <li>Vekalet ücreti sorumluluğu</li>
                                </ul>
                                
                                <h5>12. Özel Durumlar ve Ek Maddeler</h5>
                                <p>Her ofis kiralama durumuna özgü özel koşullar da sözleşmede yer almalıdır.</p>
                                
                                <p><strong>Özel durumlar:</strong></p>
                                <ul class="style-none list-item">
                                    <li>Otopark kullanım hakları ve ücretleri</li>
                                    <li>Tabela ve reklam panosu hakları</li>
                                    <li>24 saat erişim imkanları</li>
                                    <li>Depo ve arşiv alanı kullanımı</li>
                                    <li>Toplantı salonu ortak kullanım hakları</li>
                                    <li>Güvenlik sistemi ve erişim kartları</li>
                                </ul>
                                
                                <h5>Sözleşme İmzalamadan Önce Kontrol Listesi</h5>
                                <p>Sözleşmeyi imzalamadan önce aşağıdaki kontrolleri mutlaka yapın:</p>
                                
                                <ul class="style-none list-item">
                                    <li>✓ Tapu kayıtları incelenip, kiralayan yetkisi doğrulandı mı?</li>
                                    <li>✓ İmar durumu ve kullanım izni kontrol edildi mi?</li>
                                    <li>✓ Kira bedeli ve artış oranları açık şekilde belirtildi mi?</li>
                                    <li>✓ Fesih koşulları ve bildirim süreleri net mi?</li>
                                    <li>✓ Sorumluluk paylaşımı detaylandırıldı mı?</li>
                                    <li>✓ Gider paylaşımı tam olarak belirlendi mi?</li>
                                    <li>✓ Özel koşullar ve kısıtlamalar yazıldı mı?</li>
                                    <li>✓ Hukuki inceleme yaptırıldı mı?</li>
                                </ul>
                                
                                <h5>Sonuç ve Öneriler</h5>
                                <p>Ofis kiralama sözleşmesi, işletmenizin geleceğini etkileyecek önemli bir belgedir. 17 yıllık deneyimim boyunca gördüğüm en büyük hata, sözleşme maddelerini yeterince incelemeden imza atmaktır. Detaylı hazırlanmış bir sözleşme, gelecekteki problemlerin büyük çoğunluğunu önler.</p>
                                
                                <p>Özellikle ticari kira sözleşmelerinde, Türk Borçlar Kanunu'nca tanınan hakları tam olarak kullanabilmek için profesyonel destek alınması önerilir. Bu konuda danışmanlık ihtiyacınız olursa, <a href="contact.php">benimle iletişime geçebilirsiniz</a>.</p>
                                
                                <p>Unutmayın ki, iyi bir sözleşme her iki tarafın da menfaatlerini korur ve uzun vadeli başarılı iş ilişkilerinin temelini oluşturur.</p>
                            </div>
                            <div class="bottom-widget d-sm-flex align-items-center justify-content-between">
                                <ul class="d-flex align-items-center tags style-none pt-20">
                                    <li>Etiketler:</li>
                                    <li><a href="#">Ofis Kiralama,</a></li>
                                    <li><a href="#">Sözleşme,</a></li>
                                    <li><a href="#">Ticari Kira,</a></li>
                                    <li><a href="#">Hukuki Süreç</a></li>
                                </ul>
                                <ul class="d-flex share-icon align-items-center style-none pt-20">
                                    <li>Paylaş:</li>
                                    <li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-x-twitter"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
                                    <li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
                                </ul>
                            </div>
                        </article>
                        <!-- /.blog-meta-three -->
                        <div class="blog-comment-area">
                            <h3 class="blog-inner-title pb-35">4 Yorum</h3>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_01.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Serkan Yılmaz</div>
                                    <div class="date">20 Oca, 2024, 10:15</div>
                                    <p>Çok detaylı ve faydalı bir rehber olmuş. Özellikle kontrol listesi kısmı gerçekten pratik. Ben de yakında ofis kiralayacağım, bu listeyi kullanacağım kesinlikle.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_02.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Elif Özkan</div>
                                    <div class="date">21 Oca, 2024, 14:30</div>
                                    <p>Gider paylaşımı konusunda yaşadığımız sıkıntıları burada okudum. Keşke sözleşme yaparken bu detayları bilseydik. Çok öğretici bir yazı.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
									<div class="comment position-relative reply-comment d-flex">
										<img src="images/lazy.svg" data-src="images/blog/avatar_03.jpg" alt="" class="lazy-img user-avatar rounded-circle">
										<div class="comment-text">
											<div class="name fw-500">Gökhan Aydınlı</div>
											<div class="date">21 Oca, 2024, 17:45</div>
											<p>Elif Hanım, maalesef çok yaşanan bir durum. Mevcut sözleşmenizle ilgili danışmanlık ihtiyacınız olursa, benimle iletişime geçebilirsiniz. Size yardımcı olmaya çalışırım.</p>
											<a href="#" class="reply-btn tran3s">Yanıtla</a>
										</div>
									</div>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_04.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Ahmet Kaya</div>
                                    <div class="date">22 Oca, 2024, 09:20</div>
                                    <p>Fesih koşulları konusunda çok detaylı bilgi vermişsiniz. Bizim sözleşmemizde bu maddeler eksikti ve sorun yaşadık. Yeni girişimcilere çok faydalı olacak.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                            <div class="comment position-relative d-flex">
                                <img src="images/lazy.svg" data-src="images/blog/avatar_05.jpg" alt="" class="lazy-img user-avatar rounded-circle">
                                <div class="comment-text">
                                    <div class="name fw-500">Zeynep Aksoy</div>
                                    <div class="date">23 Oca, 2024, 16:10</div>
                                    <p>Hukuki açıdan çok önemli bilgiler. Özellikle Türk Borçlar Kanunu'ndaki farklılıkları bilmemiz gerekiyormuş. Avukatımla birlikte tekrar gözden geçireceğim sözleşmemizi.</p>
                                    <a href="#" class="reply-btn tran3s">Yanıtla</a>
                                </div>
                            </div>
                        </div>
                        <!-- /.blog-comment-area -->
						<div class="blog-comment-form">
							<h3 class="blog-inner-title">Yorum Bırakın</h3>
							<p><a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="text-decoration-underline fw-500">Giriş yapın</a> veya hesabınız yoksa kayıt olun.</p>
							<form action="#" class="mt-30">
								<div class="input-wrapper mb-30">
									<label>Ad Soyad*</label>
									<input type="text" placeholder="Adınız Soyadınız">
								</div>
								<div class="input-wrapper mb-40">
									<label>E-posta*</label>
									<input type="email" placeholder="ornek@email.com">
								</div>
								<div class="input-wrapper mb-30">
									<textarea placeholder="Yorumunuz"></textarea>
								</div>
								<button class="btn-five rounded-0">YORUM GÖNDER</button>
							</form>
						</div>
						<!-- /.blog-comment-form -->
                    </div>
                    <div class="col-lg-4">
						<div class="blog-sidebar dot-bg ms-xxl-4 md-mt-60">
							<div class="search-form bg-white mb-30">
                                <form action="#" class="position-relative">
                                    <input type="text" placeholder="Blog ara...">
                                    <button><i class="fa-sharp fa-regular fa-magnifying-glass"></i></button>
                                </form>
                            </div>

							<div class="categories bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Kategoriler</h5>
								<ul class="style-none">
                                    <li><a href="#">Ticari Gayrimenkul (15)</a></li>
                                    <li><a href="#">Sözleşme Rehberi (8)</a></li>
                                    <li><a href="#">Hukuki Süreçler (12)</a></li>
                                    <li><a href="#">Yatırım Tavsiyeleri (18)</a></li>
                                    <li><a href="#">Piyasa Analizi (11)</a></li>
                                    <li><a href="#">Ofis Kiralama (9)</a></li>
                                    <li><a href="#">Uzman Görüşleri (14)</a></li>
                                </ul>
							</div>

							<div class="recent-news bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Son Yazılar</h5>
								<div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="https://images.unsplash.com/photo-1497366216548-37526070297c?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog.php" class="title tran3s">Ticari Ofis ve Dükkan Kiralarken Dikkat Edilecek 7 Nokta</a></h4>
                                        <div class="date">15 Oca, 2024</div>
                                    </div>
                                </div>
                                <div class="news-block d-flex align-items-center pb-25">
                                    <div><img src="https://images.unsplash.com/photo-1560518883-ce09059eeffa?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog.php" class="title tran3s">2024 İstanbul Ticari Gayrimenkul Piyasası</a></h4>
                                        <div class="date">18 Oca, 2024</div>
                                    </div>
                                </div>
                                <div class="news-block d-flex align-items-center">
                                    <div><img src="https://images.unsplash.com/photo-1531973576160-7125cd663d86?ixlib=rb-4.0.3&w=150&h=100&fit=crop" alt="" class="lazy-img"></div>
                                    <div class="post ps-4">
                                        <h4 class="mb-5"><a href="blog.php" class="title tran3s">Yatırım Amaçlı Gayrimenkul Alma Rehberi</a></h4>
                                        <div class="date">12 Oca, 2024</div>
                                    </div>
                                </div>
							</div>

                            <div class="keyword bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Popüler Etiketler</h5>
								<ul class="style-none d-flex flex-wrap">
                                    <li><a href="#">Ofis Kiralama</a></li>
                                    <li><a href="#">Sözleşme</a></li>
                                    <li><a href="#">Ticari Kira</a></li>
                                    <li><a href="#">Hukuki Süreç</a></li>
                                    <li><a href="#">Borçlar Kanunu</a></li>
                                    <li><a href="#">Fesih Koşulları</a></li>
                                    <li><a href="#">Depozito</a></li>
                                    <li><a href="#">Kira Artışı</a></li>
                                </ul>
							</div>

                            <!-- Uzman Danışmanlık Kutusu -->
                            <div class="consultation-box bg-white bg-wrapper mb-30">
								<h5 class="mb-20">Uzman Danışmanlık</h5>
								<div class="d-flex align-items-center mb-15">
                                    <img src="images/GA.png" alt="Gökhan Aydınlı" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                                    <div>
                                        <h6 class="mb-0">Gökhan Aydınlı</h6>
                                        <small class="text-muted">Lisanslı Broker</small>
                                    </div>
                                </div>
                                <p class="small mb-3">Ofis kiralama sözleşmesi konusunda ücretsiz ön danışmanlık alın.</p>
                                <a href="contact.php" class="btn btn-sm btn-primary w-100">Ücretsiz Danışmanlık</a>
							</div>

                            <!-- Yazar Bilgisi -->
                            <div class="author-info bg-white bg-wrapper">
								<h5 class="mb-20">Yazar Hakkında</h5>
								<div class="d-flex align-items-center mb-15">
                                    <img src="images/GA.png" alt="Gökhan Aydınlı" class="rounded-circle me-3" style="width: 60px; height: 60px;">
                                    <div>
                                        <h6 class="mb-0">Gökhan Aydınlı</h6>
                                        <small class="text-muted">Gayrimenkul Uzmanı</small>
                                    </div>
                                </div>
                                <p class="small">17 yıllık deneyimi ile ticari gayrimenkul alanında uzman danışmanlık hizmetleri sunmaktadır. Özellikle ofis kiralama ve ticari sözleşmeler konusunda uzmanlaşmıştır.</p>
                                <a href="hakkimizda.php" class="btn btn-sm btn-outline-primary">Profili Görüntüle</a>
							</div>
						</div>
					</div>
                </div>
			</div>
		</div>
		<!-- /.blog-details -->

		<!--
		=====================================================
			Fancy Banner Two
		=====================================================
		-->
        <div class="fancy-banner-two position-relative z-1 pt-90 lg-pt-50 pb-90 lg-pb-50 " style="background: url('https://imagedelivery.net/prdw3ANMyocSBJD-Do1EeQ/321ce97b-f466-4486-db90-d9160bfabe00/public') no-repeat center; background-size: cover; background-attachment: fixed;">
			<div class="container">
				<div class="row align-items-center">
					<div class="col-lg-6">
						<div class="title-one text-center text-lg-start md-mb-40 pe-xl-5">
							<h3 class="text-white m0">Gayrimenkul <span>Yolculuğunuza<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Birlikte Başlayalım.</h3>
						</div>
					</div>
					<div class="col-lg-6">
						<div class="form-wrapper me-auto ms-auto me-lg-0">
							<form action="#">
								<input type="email" placeholder="E-posta adresiniz" class="rounded-0">
								<button class="rounded-0">Başlayın</button>
							</form>
							<div class="fs-16 mt-10 text-white">Zaten müşterimiz misiniz? <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal">Giriş yapın.</a></div>
						</div>
					</div>
				</div>
			</div>
		</div>
<br><br>
	

		<!--
		=====================================================
			Footer Four
		=====================================================
		-->
		<div class="footer-four position-relative z-1">
			<div class="container container-large">
				<div class="bg-wrapper position-relative z-1">
					<div class="row">
						<div class="col-xxl-3 col-lg-4 mb-60">
							<div class="footer-intro">
								<div class="logo mb-20">
									<a href="index.php">
										<img src="images/logoSiyah.png" alt="">
									</a>
								</div> 
								<!-- logo -->
								<p class="mb-30 xs-mb-20">Maltepe Mah. Eski Çırpıcı Yolu Parima Ofis No:8 K:10 D:126 Cevizlibağ / İstanbul</p>
								<a href="mailto:info@gokhanaydinli.com" class="email tran3s mb-60 md-mb-30">info@gokhanaydinli.com</a>
								<ul class="style-none d-flex align-items-center social-icon">
									<li><a href="#"><i class="fa-brands fa-whatsapp"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-instagram"></i></a></li>
									<li><a href="#"><i class="fa-brands fa-linkedin"></i></a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-3 col-sm-4 ms-auto mb-30">
							<div class="footer-nav ps-xl-5">
								<h5 class="footer-title">Linkler</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="index.php">Ana Sayfa</a></li>
									<li><a href="hakkimizda.php">Hakkımızda</a></li>
									<li><a href="portfoy.php">Portföy</a></li>
									<li><a href="blog.php">Blog</a></li>
									<li><a href="contact.php">İletişim</a></li>
									<li><a href="hesaplama-araclari.php">Hesaplamalar</a></li>
									<li><a href="dashboard/dashboard-admin.php">Panel</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-3 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Yasal</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="faq.html">Şartlar & Koşullar</a></li>
									<li><a href="faq.html">Çerez Politikası</a></li>
									<li><a href="faq.html">Gizlilik Politikası</a></li>
									<li><a href="faq.html">S.S.S</a></li>
								</ul>
							</div>
						</div>
						<div class="col-lg-2 col-sm-4 mb-30">
							<div class="footer-nav">
								<h5 class="footer-title">Hizmetlerimiz</h5>
								<ul class="footer-nav-link style-none">
									<li><a href="listing_01.html">Ticari Gayrimenkul</a></li>
									<li><a href="listing_02.html">Konut Satışı</a></li>
									<li><a href="listing_03.html">Ev Kiralama</a></li>
									<li><a href="listing_04.html">Yatırım Danışmanlığı</a></li>
									<li><a href="listing_05.html">Villa Satışı</a></li>
									<li><a href="listing_06.html">Ofis Kiralama</a></li>
								</ul>
							</div>
						</div>
					</div>
				</div>
				<!-- /.bg-wrapper -->
				<div class="bottom-footer">
					<p class="m0 text-center fs-16">Copyright @2024 Gökhan Aydınlı Gayrimenkul.</p>
				</div>
			</div>
			<img src="images/lazy.svg" data-src="images/assets/ils_06.svg" alt="" class="lazy-img shapes shape_01">
		</div> <!-- /.footer-four -->


		<!-- ################### Login Modal ####################### -->
        <!-- Modal -->
        <div class="modal fade" id="loginModal" tabindex="-1" aria-hidden="true">
            <div class="modal-dialog modal-fullscreen modal-dialog-centered">
                <div class="container">
                    <div class="user-data-form modal-content">
                        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
						<div class="form-wrapper m-auto">
							<ul class="nav nav-tabs w-100" role="tablist">
								<li class="nav-item" role="presentation">
									<button class="nav-link active" data-bs-toggle="tab" data-bs-target="#fc1" role="tab">Giriş</button>
								</li>
								<li class="nav-item" role="presentation">
									<button class="nav-link" data-bs-toggle="tab" data-bs-target="#fc2" role="tab">Kayıt</button>
								</li>
							</ul>
							<div class="tab-content mt-30">
								<div class="tab-pane show active" role="tabpanel" id="fc1">
									<div class="text-center mb-20">
										<h2>Hoş Geldiniz!</h2>
										<p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#">Kayıt olun</a></p>
									</div>
									<form action="#">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>E-posta*</label>
													<input type="email" placeholder="ornek@email.com">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-20">
													<label>Şifre*</label>
													<input type="password" placeholder="Şifrenizi girin" class="pass_log_id">
													<span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
												</div>
											</div>
											<div class="col-12">
												<div class="agreement-checkbox d-flex justify-content-between align-items-center">
													<div>
														<input type="checkbox" id="remember">
														<label for="remember">Beni hatırla</label>
													</div>
													<a href="#">Şifremi Unuttum?</a>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
											</div>
										</div>
									</form>
								</div>
								<!-- /.tab-pane -->
								<div class="tab-pane" role="tabpanel" id="fc2">
									<div class="text-center mb-20">
										<h2>Kayıt Ol</h2>
										<p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#">Giriş yapın</a></p>
									</div>
									<form action="#">
										<div class="row">
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>Ad Soyad*</label>
													<input type="text" placeholder="Ad Soyadınız">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-25">
													<label>E-posta*</label>
													<input type="email" placeholder="ornek@email.com">
												</div>
											</div>
											<div class="col-12">
												<div class="input-group-meta position-relative mb-20">
													<label>Şifre*</label>
													<input type="password" placeholder="Şifrenizi girin" class="pass_log_id">
													<span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
												</div>
											</div>
											<div class="col-12">
												<div class="agreement-checkbox d-flex justify-content-between align-items-center">
													<div>
														<input type="checkbox" id="remember2">
														<label for="remember2">"Kayıt Ol" butonuna tıklayarak <a href="#">Şartlar & Koşullar</a> ile <a href="#">Gizlilik Politikası</a>'nı kabul ediyorum</label>
													</div>
												</div> <!-- /.agreement-checkbox -->
											</div>
											<div class="col-12">
												<button class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
											</div>
										</div>
									</form>
								</div>
								<!-- /.tab-pane -->
							</div>
							
							<div class="d-flex align-items-center mt-30 mb-10">
								<div class="line"></div>
								<span class="pe-3 ps-3 fs-6">VEYA</span>
								<div class="line"></div>
							</div>
							<div class="row">
								<div class="col-sm-6">
									<a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
										<img src="images/icon/google.png" alt="">
										<span class="ps-3">Google ile Kayıt</span>
									</a>
								</div>
								<div class="col-sm-6">
									<a href="#" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
										<img src="images/icon/facebook.png" alt="">
										<span class="ps-3">Facebook ile Kayıt</span>
									</a>
								</div>
							</div>
						</div>
						<!-- /.form-wrapper -->
                    </div>
                    <!-- /.user-data-form -->
                </div>
            </div>
        </div>



		<button class="scroll-top">
			<i class="bi bi-arrow-up-short"></i>
		</button>




		<!-- Optional JavaScript _____________________________  -->
		<script>
			// Error handling for JS files
			window.addEventListener('error', function(e) {
				console.log('JS Error caught:', e.filename, e.message);
			});
		</script>

		<!-- jQuery first, then Bootstrap JS -->
		<!-- jQuery -->
		<script src="vendor/jquery.min.js" onerror="console.log('jQuery failed to load')"></script>
		<!-- Bootstrap JS -->
		<script src="vendor/bootstrap/js/bootstrap.bundle.min.js" onerror="console.log('Bootstrap failed to load')"></script>
		<!-- WOW js -->
		<script src="vendor/wow/wow.min.js" onerror="console.log('WOW failed to load')"></script>
		<!-- Slick Slider -->
		<script src="vendor/slick/slick.min.js" onerror="console.log('Slick failed to load')"></script>
		<!-- Fancybox -->
		<script src="vendor/fancybox/fancybox.umd.js" onerror="console.log('Fancybox failed to load')"></script>
		<!-- Lazy -->
		<script src="vendor/jquery.lazy.min.js" onerror="console.log('Lazy failed to load')"></script>
		<!-- js Counter -->
		<script src="vendor/jquery.counterup.min.js" onerror="console.log('Counter failed to load')"></script>
		<script src="vendor/jquery.waypoints.min.js" onerror="console.log('Waypoints failed to load')"></script>
		<!-- Nice Select -->
		<script src="vendor/nice-select/jquery.nice-select.min.js" onerror="console.log('Nice Select failed to load')"></script>
		<!-- validator js -->
		<script src="vendor/validator.js" onerror="console.log('Validator failed to load')"></script>
        <!-- isotop -->
		<script src="vendor/isotope.pkgd.min.js" onerror="console.log('Isotope failed to load')"></script>

		<!-- Theme js -->
		<script src="js/theme.js" onerror="console.log('Theme JS failed to load')"></script>
	</div> <!-- /.main-page-wrapper -->
</body>

</html>