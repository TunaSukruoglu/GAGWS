<?php
session_start();

// Kullanıcı giriş yapmışsa farklı buton göster
$isLoggedIn = isset($_SESSION['user_id']);
$userName = $isLoggedIn ? $_SESSION['user_name'] ?? 'Kullanıcı' : '';

// Sayfanın en başında session-check'i include et (duplikasyon kaldırıldı)
// include 'includes/session-check.php';
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <!-- Meta tags -->
    <meta charset="UTF-8">
    <meta name="keywords" content="Gökhan Aydınlı Gayrimenkul, iş yeri, ofis, dükkan, ticari gayrimenkul, hakkımızda">
    <meta name="description" content="Gökhan Aydınlı Gayrimenkul: 2012'den beri ticari gayrimenkul sektöründe güvenilir danışmanlık hizmetleri.">
    <meta property="og:site_name" content="Gökhan Aydınlı Gayrimenkul">
    <meta property="og:url" content="https://gokhanaydinli.com">
    <meta property="og:type" content="website">
    <meta property="og:title" content="Hakkımızda | Gökhan Aydınlı Gayrimenkul">
    <meta name='og:image' content='images/assets/ogg.png'>
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0D1A1C">
    <meta name="msapplication-navbutton-color" content="#0D1A1C">
    <meta name="apple-mobile-web-app-status-bar-style" content="#0D1A1C">
    <title>Hakkımızda | Gökhan Aydınlı Gayrimenkul</title>
    <link rel="icon" type="image/png" sizes="56x56" href="images/fav-icon/icon.png">
    <link rel="stylesheet" type="text/css" href="css/bootstrap.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/style.min.css" media="all">
    <link rel="stylesheet" type="text/css" href="css/responsive.css" media="all">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick.min.css">
    <link rel="stylesheet" type="text/css" href="https://cdnjs.cloudflare.com/ajax/libs/slick-carousel/1.8.1/slick-theme.min.css">
    <!-- Bootstrap Icons -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.0/font/bootstrap-icons.css">
    <!-- FontAwesome (yoksa ekleyin) -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    
    <!-- Modal CSS -->
    <?php // include 'includes/modal-css.php'; ?>
    
   
</head>
 <style>
/* Gökhan Aydınlı Kişisel Tema Stilleri */
:root {
    --theme-color-1: #0d6efd;
    --theme-color-2: #0d6efd;
    --theme-color-3: #0d6efd;
    --dark-color: #1a1a1a;
    --gray-color: #6c757d;
    --light-gray: #f8f9fa;
    --white: #ffffff;
    --pink-color: #fff5f0;
}

body {
    font-family: 'Gordita', 'Poppins', sans-serif;
    line-height: 1.6;
    color: var(--dark-color);
    overflow-x: hidden;
}

.main-page-wrapper {
    position: relative;
}

/* Header Styles */
.theme-main-menu {
    background: var(--white);
    box-shadow: 0 2px 20px rgba(0,0,0,0.1);
    position: fixed;
    top: 0;
    left: 0;
    right: 0;
    z-index: 999;
    transition: all 0.3s ease;
}

.theme-main-menu .inner-content {
    padding: 0 30px;
}

.theme-main-menu .top-header {
    padding: 20px 0;
}

.logo {
    font-size: 28px;
    font-weight: 800;
    color: var(--theme-color-1);
    text-decoration: none;
}

.btn-one {
    background: transparent;
    border: 1px solid var(--theme-color-1);
    color: var(--theme-color-1);
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-one:hover {
    background: var(--theme-color-1);
    color: var(--white);
}

.btn-two {
    background: var(--theme-color-1);
    color: var(--white);
    padding: 12px 25px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
}

.btn-two:hover {
    background: #084298;
    color: var(--white);
}

.navbar-nav .nav-link {
    color: var(--dark-color);
    font-weight: 500;
    padding: 10px 20px;
    transition: all 0.3s ease;
}

.navbar-nav .nav-link:hover {
    color: var(--theme-color-1);
}

/* Hero Banner - Resimli Versiyon */
.hero-banner-with-image {
    background: linear-gradient(135deg, #f8f9fa 0%, #e9ecef 100%);
    padding: 200px 0 120px;
    position: relative;
    overflow: hidden;
    min-height: 600px;
}

.hero-banner-with-image .container {
    position: relative;
    z-index: 2;
}

.hero-banner-with-image h1 {
    font-size: 3.5rem;
    font-weight: 800;
    color: var(--dark-color);
    margin-bottom: 20px;
    line-height: 1.2;
}

.hero-banner-with-image .breadcrumb-wrapper {
    margin-top: 30px;
}

.theme-breadcrumb {
    list-style: none;
    padding: 0;
    margin: 0;
    display: inline-flex;
    align-items: center;
    background: rgba(255, 255, 255, 0.9);
    padding: 10px 20px;
    border-radius: 25px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.1);
}

.theme-breadcrumb li {
    display: inline-block;
    color: var(--gray-color);
    font-size: 14px;
    font-weight: 500;
}

.theme-breadcrumb li a {
    color: var(--theme-color-1);
    text-decoration: none;
    transition: all 0.3s ease;
}

.theme-breadcrumb li a:hover {
    color: var(--dark-color);
}

.theme-breadcrumb li:not(:last-child)::after {
    content: "/";
    margin: 0 10px;
    color: var(--gray-color);
}

/* Hero Image */
.hero-image {
    position: absolute;
    right: 0;
    top: 50%;
    transform: translateY(-50%);
    width: 50%;
    height: 100%;
    background: url('data:image/svg+xml;base64,PHN2ZyB3aWR0aD0iODAwIiBoZWlnaHQ9IjYwMCIgdmlld0JveD0iMCAwIDgwMCA2MDAiIGZpbGw9Im5vbmUiIHhtbG5zPSJodHRwOi8vd3d3LnczLm9yZy8yMDAwL3N2ZyI+CjxkZWZzPgo8bGluZWFyR3JhZGllbnQgaWQ9InNreUdyYWRpZW50IiB4MT0iMCUiIHkxPSIwJSIgeDI9IjEwMCUiIHkyPSIxMDAlIj4KPHN0b3Agb2Zmc2V0PSIwJSIgc3R5bGU9InN0b3AtY29sb3I6I2Y4ZjlmYTtzdG9wLW9wYWNpdHk6MSIgLz4KPHN0b3Agb2Zmc2V0PSIxMDAlIiBzdHlsZT0ic3RvcC1jb2xvcjojZTllY2VmO3N0b3Atb3BhY2l0eToxIiAvPgo8L2xpbmVhckdyYWRpZW50Pgo8L2RlZnM+CjxyZWN0IHdpZHRoPSI4MDAiIGhlaWdodD0iNjAwIiBmaWxsPSJ1cmwoI3NreUdyYWRpZW50KSIvPgoKPCEtLSBCYWNrZ3JvdW5kIEhpbGxzIC0tPgo8cGF0aCBkPSJNMCA0MDBDMTAwIDM1MCAyMDAgMzgwIDMwMCAzNjBDNDAwIDM0MCA1MDAgMzIwIDYwMCAzNDBDNzAwIDM2MCA4MDAgMzgwIDgwMCA0MDBWNTQ4SDAsNDAwWiIgZmlsbD0iI2UzZjJmZCIgZmlsbC1vcGFjaXR5PSIwLjMiLz4KPHBhdGggZD0iTTAgNDUwQzEwMCA0MDAgMjAwIDQyMCAzMDAgNDEwQzQwMCAzOTAgNTAwIDM3MCA2MDAgMzkwQzcwMCA0MTAgODAwIDQzMCA4MDAgNDUwVjU0OEgwVjQ1MFoiIGZpbGw9IiNkNGVkZGEiIGZpbGwtb3BhY2l0eT0iMC40Ii8+Cgo8IS0tIE1haW4gSG91c2UgLS0+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQ1MCwgMjAwKSI+CjwhLS0gSG91c2UgQmFzZSAtLT4KPHJlY3QgeD0iMCIgeT0iMTAwIiB3aWR0aD0iMjAwIiBoZWlnaHQ9IjE1MCIgZmlsbD0iI2ZmZmZmZiIgc3Ryb2tlPSIjZGRkIiBzdHJva2Utd2lkdGg9IjIiLz4KCjwhLS0gUm9vZiAtLT4KPHBhdGggZD0iTTAgMTAwTDEwMCAyMEwyMDAgMTAwWiIgZmlsbD0iI2ZmNmIzNSIvPgoKPCEtLSBEb29yIC0tPgo8cmVjdCB4PSI4MCIgeT0iMTgwIiB3aWR0aD0iNDAiIGhlaWdodD0iNzAiIGZpbGw9IiM4ZDRjMzIiLz4KPGNpcmNsZSBjeD0iMTA1IiBjeT0iMjE1IiByPSIzIiBmaWxsPSIjZmZkNzAwIi8+Cgo8IS0tIFdpbmRvd3MgLS0+CjxyZWN0IHg9IjMwIiB5PSIxMzAiIHdpZHRoPSIzMCIgaGVpZ2h0PSIzMCIgZmlsbD0iIzg3Y2VlYiIgc3Ryb2tlPSIjNjY5OWNjIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iMTQwIiB5PSIxMzAiIHdpZHRoPSIzMCIgaGVpZ2h0PSIzMCIgZmlsbD0iIzg3Y2VlYiIgc3Ryb2tlPSIjNjY5OWNjIiBzdHJva2Utd2lkdGg9IjIiLz4KCjwhLS0gV2luZG93IERpdmlkZXJzIC0tPgo8bGluZSB4MT0iNDUiIHkxPSIxMzAiIHgyPSI0NSIgeTI9IjE2MCIgc3Ryb2tlPSIjNjY5OWNjIiBzdHJva2Utd2lkdGg9IjEiLz4KPGxpbmUgeDE9IjMwIiB5MT0iMTQ1IiB4Mj0iNjAiIHkyPSIxNDUiIHN0cm9rZT0iIzY2OTljYyIgc3Ryb2tlLXdpZHRoPSIxIi8+CjxsaW5lIHgxPSIxNTUiIHkxPSIxMzAiIHgyPSIxNTUiIHkyPSIxNjAiIHN0cm9rZT0iIzY2OTljYyIgc3Ryb2tlLXdpZHRoPSIxIi8+CjxsaW5lIHgxPSIxNDAiIHkxPSIxNDUiIHgyPSIxNzAiIHkyPSIxNDUiIHN0cm9rZT0iIzY2OTljYyIgc3Ryb2tlLXdpZHRoPSIxIi8+CjwvZz4KCjwhLS0gU21hbGwgSG91c2UgLS0+CjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDIwMCwgMjgwKSI+CjxyZWN0IHg9IjAiIHk9IjgwIiB3aWR0aD0iMTIwIiBoZWlnaHQ9IjEwMCIgZmlsbD0iI2ZmZmZmZiIgc3Ryb2tlPSIjZGRkIiBzdHJva2Utd2lkdGg9IjIiLz4KPHBhdGggZD0iTTAgODBMNjAgMjBMMTIwIDgwWiIgZmlsbD0iI2ZmNmIzNSIvPgo8cmVjdCB4PSI0NSIgeT0iMTMwIiB3aWR0aD0iMzAiIGhlaWdodD0iNTAiIGZpbGw9IiM4ZDRjMzIiLz4KPHJlY3QgeD0iMjAiIHk9IjEwMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSIjODdjZWViIiBzdHJva2U9IiM2Njk5Y2MiIHN0cm9rZS13aWR0aD0iMiIvPgo8cmVjdCB4PSI4MCIgeT0iMTAwIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIGZpbGw9IiM4N2NlZWIiIHN0cm9rZT0iIzY2OTljYyIgc3Ryb2tlLXdpZHRoPSIyIi8+CjwvZz4KCjwhLS0gVGFsbCBIb3VzZSAtLT4KPGcgdHJhbnNmb3JtPSJ0cmFuc2xhdGUoNjgwLCAyMDApIj4KPHJlY3QgeD0iMCIgeT0iNjAiIHdpZHRoPSIxMDAiIGhlaWdodD0iMTkwIiBmaWxsPSIjZmZmZmZmIiBzdHJva2U9IiNkZGQiIHN0cm9rZS13aWR0aD0iMiIvPgo8cGF0aCBkPSJNMCA2MEw1MCAyMEwxMDAgNjBaIiBmaWxsPSIjZmY2YjM1Ii8+CjxyZWN0IHg9IjM1IiB5PSIyMDAiIHdpZHRoPSIzMCIgaGVpZ2h0PSI1MCIgZmlsbD0iIzhkNGMzMiIvPgo8cmVjdCB4PSIxNSIgeT0iODAiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0iIzg3Y2VlYiIgc3Ryb2tlPSIjNjY5OWNjIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iNjUiIHk9IjgwIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIGZpbGw9IiM4N2NlZWIiIHN0cm9rZT0iIzY2OTljYyIgc3Ryb2tlLXdpZHRoPSIyIi8+CjxyZWN0IHg9IjE1IiB5PSIxMjAiIHdpZHRoPSIyMCIgaGVpZ2h0PSIyMCIgZmlsbD0iIzg3Y2VlYiIgc3Ryb2tlPSIjNjY5OWNjIiBzdHJva2Utd2lkdGg9IjIiLz4KPHJlY3QgeD0iNjUiIHk9IjEyMCIgd2lkdGg9IjIwIiBoZWlnaHQ9IjIwIiBmaWxsPSIjODdjZWViIiBzdHJva2U9IiM2Njk5Y2MiIHN0cm9rZS13aWR0aD0iMiIvPgo8L2c+Cgo8IS0tIFRyZWVzIC0tPgo8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMDAsIDM1MCkiPgo8Y2lyY2xlIGN4PSIwIiBjeT0iMCIgcj0iNDAiIGZpbGw9IiM0Y2FmNTAiLz4KPHJlY3QgeD0iLTUiIHk9IjAiIHdpZHRoPSIxMCIgaGVpZ2h0PSI0MCIgZmlsbD0iIzhkNGMzMiIvPgo8L2c+Cgo8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgzNTAsIDM4MCkiPgo8Y2lyY2xlIGN4PSIwIiBjeT0iMCIgcj0iMzAiIGZpbGw9IiM0Y2FmNTAiLz4KPHJlY3QgeD0iLTQiIHk9IjAiIHdpZHRoPSI4IiBoZWlnaHQ9IjMwIiBmaWxsPSIjOGQ0YzMyIi8+CjwvZz4KCjwhLS0gQ2xvdWRzIC0tPgo8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgxMDAsIDgwKSI+CjxjaXJjbGUgY3g9IjAiIGN5PSIwIiByPSIyNSIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjgiLz4KPGNpcmNsZSBjeD0iMzAiIGN5PSItNSIgcj0iMzAiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC44Ii8+CjxjaXJjbGUgY3g9IjYwIiBjeT0iMCIgcj0iMjAiIGZpbGw9IiNmZmZmZmYiIGZpbGwtb3BhY2l0eT0iMC44Ii8+CjwvZz4KCjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDU1MCwgNjApIj4KPGNpcmNsZSBjeD0iMCIgY3k9IjAiIHI9IjIwIiBmaWxsPSIjZmZmZmZmIiBmaWxsLW9wYWNpdHk9IjAuNyIvPgo8Y2lyY2xlIGN4PSIyNSIgY3k9Ii0zIiByPSIyNSIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjciLz4KPGNpcmNsZSBjeD0iNDUiIGN5PSIwIiByPSIxNSIgZmlsbD0iI2ZmZmZmZiIgZmlsbC1vcGFjaXR5PSIwLjciLz4KPC9nPgoKPCEtLSBTdW4gLS0+CjxjaXJjbGUgY3g9IjcwMCIgY3k9IjEwMCIgcj0iNDAiIGZpbGw9IiNmZmQ3MDAiIGZpbGwtb3BhY2l0eT0iMC44Ii8+Cgo8IS0tIEJpcmRzIC0tPgo8ZyB0cmFuc2Zvcm09InRyYW5zbGF0ZSgzMDAsIDEyMCkiPgo8cGF0aCBkPSJNMCAwQzUgLTUgMTAgLTUgMTUgMEMxMCA1IDUgNSAwIDAiIGZpbGw9IiMzMzMiIGZpbGwtb3BhY2l0eT0iMC42Ii8+CjwvZz4KCjxnIHRyYW5zZm9ybT0idHJhbnNsYXRlKDQwMCwgMTAwKSI+CjxwYXRoIGQ9Ik0wIDBDNSAtNSAxMCAtNSAxNSAwQzEwIDUgNSA1IDAgMCIgZmlsbD0iIzMzMyIgZmlsbC1vcGFjaXR5PSIwLjYiLz4KPC9nPgo8L3N2Zz4=') no-repeat center center;
    background-size: contain;
    z-index: 1;
}

@media (max-width: 992px) {
    .hero-image {
        position: relative;
        width: 100%;
        height: 300px;
        margin-top: 40px;
    }
    
    .hero-banner-with-image {
        text-align: center;
        padding: 150px 0 80px;
    }
    
    .hero-banner-with-image h1 {
        font-size: 2.5rem;
    }
}

@media (max-width: 768px) {
    .hero-banner-with-image h1 {
        font-size: 2rem;
    }
    
    .hero-banner-with-image {
        padding: 120px 0 60px;
    }
}

/* Block Feature Two */
.block-feature-two {
    padding: 120px 0;
}

.title-one .upper-title {
    color: var(--theme-color-1);
    font-weight: 600;
    font-size: 16px;
    text-transform: uppercase;
    letter-spacing: 1px;
    margin-bottom: 15px;
}

.title-one h3 {
    font-size: 2.8rem;
    font-weight: 700;
    line-height: 1.2;
    margin-bottom: 25px;
    color: var(--dark-color);
}

.title-one h3 span {
    color: var(--theme-color-1);
    position: relative;
}

.title-one p {
    font-size: 18px;
    color: var(--gray-color);
    line-height: 1.7;
}

.counter-wrapper {
    border-top: 1px solid #eee;
    padding-top: 40px;
    margin-top: 50px;
}

.counter-block-one .main-count {
    font-size: 2.5rem;
    font-weight: 700;
    color: var(--theme-color-1);
    margin-bottom: 8px;
}

.counter-block-one span {
    color: var(--gray-color);
    font-size: 14px;
}

.block-two {
    background: var(--pink-color);
    padding: 50px 40px;
    border-radius: 20px;
    height: 100%;
}

.block-two h5 {
    font-size: 1.5rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 20px;
    position: relative;
}

.block-two h5.top-line {
    padding-top: 30px;
    margin-top: 30px;
    border-top: 1px solid rgba(253, 126, 20, 0.2);
}

/* Timeline Styles */
.timeline-section {
    padding: 120px 0;
    background: var(--light-gray);
}

.timeline {
    position: relative;
    padding: 0;
    list-style: none;
    max-width: 800px;
    margin: 0 auto;
}

.timeline::before {
    content: '';
    position: absolute;
    top: 0;
    bottom: 0;
    left: 50px;
    width: 4px;
    background: var(--theme-color-1);
    border-radius: 2px;
}

.timeline-item {
    position: relative;
    margin-bottom: 60px;
    padding-left: 120px;
}

.timeline-badge {
    position: absolute;
    left: 30px;
    top: 0;
    width: 40px;
    height: 40px;
    border-radius: 50%;
    background: var(--theme-color-1);
    color: var(--white);
    display: flex;
    align-items: center;
    justify-content: center;
    font-weight: 600;
    font-size: 14px;
}

.timeline-content {
    background: var(--white);
    padding: 30px;
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    position: relative;
}

.timeline-content::before {
    content: '';
    position: absolute;
    left: -15px;
    top: 20px;
    width: 0;
    height: 0;
    border-style: solid;
    border-width: 10px 15px 10px 0;
    border-color: transparent var(--white) transparent transparent;
}

.timeline-content h4 {
    font-size: 1.3rem;
    font-weight: 600;
    color: var(--theme-color-1);
    margin-bottom: 15px;
}

/* Card Styles */
.card-style-one {
    background: var(--white);
    padding: 40px 30px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    border: none;
    height: 100%;
}

.card-style-one:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.icon-box {
    width: 80px;
    height: 80px;
    border-radius: 20px;
    background: var(--theme-color-1);
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 2rem;
    color: var(--white);
    margin: 0 auto 25px;
}

.card-style-one h5 {
    font-size: 1.2rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 15px;
}

.card-style-one p {
    color: var(--gray-color);
    font-size: 15px;
    line-height: 1.6;
}

/* Expertise Section */
.expertise-section {
    padding: 120px 0;
}

.expertise-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(350px, 1fr));
    gap: 30px;
    margin-top: 50px;
}

.expertise-card {
    background: var(--white);
    padding: 40px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.expertise-card:hover {
    transform: translateY(-10px);
    box-shadow: 0 25px 50px rgba(0,0,0,0.15);
}

.expertise-card h4 {
    font-size: 1.4rem;
    font-weight: 600;
    color: var(--dark-color);
    margin-bottom: 20px;
}

.expertise-list {
    list-style: none;
    padding: 0;
}

.expertise-list li {
    padding: 8px 0;
    color: var(--gray-color);
    font-size: 15px;
    border-bottom: 1px solid #f0f0f0;
}

.expertise-list li:last-child {
    border-bottom: none;
}

.expertise-list li i {
    color: var(--theme-color-1);
    margin-right: 10px;
}

/* Quote Section */
.quote-section {
    background: linear-gradient(135deg, var(--theme-color-1) 0%, #084298 100%);
    padding: 100px 0;
    position: relative;
    overflow: hidden;
}

.quote-section::before {
    content: '"';
    position: absolute;
    top: 20px;
    left: 50%;
    transform: translateX(-50%);
    font-size: 150px;
    color: rgba(255,255,255,0.1);
    font-weight: 800;
}

.quote-content {
    position: relative;
    z-index: 2;
    text-align: center;
}

.quote-content h3 {
    font-size: 2.2rem;
    font-weight: 600;
    color: var(--white);
    line-height: 1.4;
    margin-bottom: 30px;
    font-style: italic;
}

.quote-content p {
    color: rgba(255,255,255,0.9);
    font-size: 18px;
    font-weight: 500;
}

/* Stats Section */
.stats-section {
    padding: 100px 0;
    background: var(--light-gray);
}

.stats-box {
    text-align: center;
    padding: 40px 20px;
    background: var(--white);
    border-radius: 15px;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
}

.stats-box:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.stats-number {
    font-size: 3rem;
    font-weight: 800;
    color: var(--theme-color-1);
    margin-bottom: 10px;
}

.stats-label {
    color: var(--gray-color);
    font-size: 14px;
    font-weight: 500;
}

/* Contact Section */
.contact-section {
    padding: 120px 0;
}

.contact-card {
    background: var(--white);
    padding: 50px;
    border-radius: 20px;
    box-shadow: 0 15px 40px rgba(0,0,0,0.1);
    text-align: center;
}

.contact-info {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(250px, 1fr));
    gap: 20px;
    margin: 40px 0;
}

.contact-item {
    padding: 20px;
    background: var(--pink-color);
    border-radius: 15px;
    transition: all 0.3s ease;
}

.contact-item:hover {
    background: var(--theme-color-1);
    color: var(--white);
}

.contact-item i {
    font-size: 2rem;
    color: var(--theme-color-1);
    margin-bottom: 15px;
}

.contact-item:hover i {
    color: var(--white);
}

.contact-buttons {
    display: flex;
    gap: 15px;
    justify-content: center;
    flex-wrap: wrap;
    margin-top: 40px;
}

.contact-btn {
    background: var(--theme-color-1);
    color: var(--white);
    padding: 15px 30px;
    border-radius: 50px;
    text-decoration: none;
    font-weight: 500;
    transition: all 0.3s ease;
    display: flex;
    align-items: center;
    gap: 10px;
}

.contact-btn:hover {
    background: #084298;
    color: var(--white);
    transform: translateY(-2px);
}


/* Modal Styles */
.user-data-form {
    background: rgba(255, 255, 255, 0.95);
    backdrop-filter: blur(20px);
    border-radius: 20px;
    border: 1px solid #ddd;
    padding: 40px;
    box-shadow: 0 4px 30px rgba(0, 0, 0, 0.1);
}

.user-data-form .nav-tabs {
    border-bottom: 2px solid var(--theme-color-1);
}

.user-data-form .nav-link {
    color: var(--theme-color-1);
    font-weight: 500;
    border: none;
    border-radius: 0;
    padding: 10px 20px;
    margin-right: 10px;
    transition: all 0.3s ease;
}

.user-data-form .nav-link.active {
    color: #fff;
    background: var(--theme-color-1);
    border-radius: 20px 20px 0 0;
}

.user-data-form .tab-content {
    border: 1px solid var(--theme-color-1);
    border-radius: 0 0 20px 20px;
    padding: 30px;
    background: #fff;
}

.user-data-form .input-group-meta {
    margin-bottom: 25px;
}

.user-data-form label {
    font-weight: 500;
    margin-bottom: 10px;
    display: block;
}

.user-data-form input[type="email"],
.user-data-form input[type="password"],
.user-data-form input[type="text"],
.user-data-form input[type="tel"] {
    border: 1px solid #ddd;
    border-radius: 10px;
    padding: 10px 15px;
    font-size: 16px;
    width: 100%;
    transition: all 0.3s ease;
}

.user-data-form input:focus {
    border-color: var(--theme-color-1);
    box-shadow: 0 0 5px rgba(253, 126, 20, 0.5);
    outline: none;
}

.user-data-form .btn-two {
    background: var(--theme-color-1);
    color: #fff;
    padding: 12px 20px;
    border: none;
    border-radius: 10px;
    font-size: 16px;
    font-weight: 500;
    cursor: pointer;
    transition: all 0.3s ease;
}

.user-data-form .btn-two:hover {
    background: #084298;
    transform: translateY(-2px);
}

/* Responsive */
@media (max-width: 768px) {
    .hero-banner-with-image h1 {
        font-size: 2.5rem;
    }
    
    .title-one h3 {
        font-size: 2rem;
    }
    
    .timeline::before {
        left: 20px;
    }
    
    .timeline-badge {
        left: 0;
    }
    
    .timeline-item {
        padding-left: 80px;
    }
    
    .expertise-grid {
        grid-template-columns: 1fr;
    }
    
    .contact-buttons {
        flex-direction: column;
        align-items: center;
    }
    
    .quote-content h3 {
        font-size: 1.8rem;
    }
}

/* Scroll to top button */
.scroll-top {
    position: fixed;
    bottom: 30px;
    right: 30px;
    width: 50px;
    height: 50px;
    background: var(--theme-color-1);
    color: white;
    border: none;
    border-radius: 50%;
    display: none;
    z-index: 9999;
    cursor: pointer;
    transition: all 0.3s ease;
    opacity: 0;
    font-size: 20px;
    box-shadow: 0 4px 15px rgba(253, 126, 20, 0.3);
}

.scroll-top:hover {
    background: #084298;
    transform: translateY(-2px);
    box-shadow: 0 8px 25px rgba(229, 107, 0, 0.4);
}

.scroll-top i {
    font-size: 24px;
    line-height: 1;
}

/* Preloader */
#preloader {
    position: fixed;
    top: 0;
    left: 0;
    width: 100%;
    height: 100%;
    background: #fff;
    z-index: 99999;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: opacity 0.5s ease;
}

/* Agent card düzeltmeleri */
.agent-card-one {
    background: white;
    border-radius: 15px;
    overflow: hidden;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    transition: all 0.3s ease;
    height: 100%;
    display: flex;
    flex-direction: column;
}

.agent-card-one:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(0,0,0,0.15);
}

.agent-card-one .img {
    height: 280px;
    overflow: hidden;
    flex-shrink: 0;
}

.agent-card-one .img img {
    width: 100%;
    height: 100%;
    object-fit: cover;
    transition: transform 0.3s ease;
}

.agent-card-one:hover .img img {
    transform: scale(1.05);
}

.agent-card-one .text-center {
    padding: 25px 20px;
    flex-grow: 1;
    display: flex;
    flex-direction: column;
    justify-content: center;
}

.agent-card-one h6 {
    font-size: 20px;
    font-weight: 700;
    margin-bottom: 8px;
    color: var(--dark-color);
}

.agent-card-one a {
    color: var(--theme-color-1);
    font-size: 14px;
    font-weight: 500;
    text-decoration: none;
}

.agent-card-one a:hover {
    color: #e56b00;
}

/* Animasyon fallback */
@keyframes fadeInUp {
    from {
        opacity: 0;
        transform: translate3d(0, 40px, 0);
    }
    to {
        opacity: 1;
        transform: none;
    }
}

.wow {
    animation: fadeInUp 1s ease forwards;
}

/* Counter fallback */
.counter {
    font-weight: bold;
    color: var(--theme-color-1);
}

/* Feedback block düzeltmeleri */
.feedback-block-six {
    padding: 30px;
    background: white;
    box-shadow: 0 10px 30px rgba(0,0,0,0.1);
    margin: 15px;
    height: auto;
    min-height: 250px;
}

.feedback-block-six blockquote {
    margin: 20px 0;
    font-style: italic;
    font-size: 16px;
    line-height: 1.6;
}

.rating li i {
    color: #ffc107;
}

.avatar {
    width: 50px;
    height: 50px;
    object-fit: cover;
}

/* Block feature five */
.block-feature-five {
    padding: 120px 0;
}

/* Shapes */
.shapes {
    position: absolute;
    z-index: -1;
}



.shape_02 {
    bottom: 20%;
    right: -5%;
    opacity: 0.5;
}

/* Slider fallback */
.fallback-grid {
    display: flex !important;
    flex-wrap: wrap;
    gap: 20px;
    justify-content: center;
}

.fallback-grid .item {
    flex: 1;
    min-width: 300px;
    max-width: calc(50% - 10px);
    margin-bottom: 20px;
}
</style>
<body>
    <div class="main-page-wrapper">
        <!-- ===================================================
            Yükleniyor Animasyonu
        ==================================================== -->
        <div id="preloader">
            <div id="ctn-preloader" class="ctn-preloader">
                <div class="icon"><img src="images/loader.gif" alt="" class="m-auto d-block" width="64"></div>
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
								<img src="images/logoSiyah.png" alt="Gökhan Aydınlı Gayrimenkul">
							</a>
						</div>
						<!-- logo -->
						<!-- Header'da Giriş butonu (Navigation'dan önce) -->
<div class="right-widget ms-auto ms-lg-0 me-3 me-lg-0 order-lg-3">
    <ul class="d-flex align-items-center style-none">
        <?php if ($isLoggedIn): ?>
            <li class="dropdown">
                <a href="#" class="btn-one dropdown-toggle" data-bs-toggle="dropdown">
                    <i class="fa-solid fa-user"></i> <span><?php echo htmlspecialchars($userName); ?></span>
                </a>
                <ul class="dropdown-menu">
                    <li><a class="dropdown-item" href="dashboard.php">Panel</a></li>
                    <li><a class="dropdown-item" href="profile.php">Profil</a></li>
                    <li><hr class="dropdown-divider"></li>
                    <li><a class="dropdown-item" href="logout.php">Çıkış Yap</a></li>
                </ul>
            </li>
        <?php else: ?>
            <li>
                <a href="#" data-bs-toggle="modal" data-bs-target="#loginModal" class="btn-one">
                    <i class="fa-regular fa-lock"></i> <span>Giriş</span>
                </a>
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
									<li class="d-block d-lg-none"><div class="logo"><a href="index.php" class="d-block"><img src="images/logo.png" alt="Gökhan Aydınlı Gayrimenkul" style="height:72px; width:auto; max-width:260px;"></a></div></li>
									<li class="nav-item dropdown">
										<a class="nav-link" href="index.php" >Ana Sayfa</a>
									</li>
									 <li class="nav-item dropdown">
										<a class="nav-link" href="hakkimizda.php">Hakkımızda</a>
										</a>
						
									</li>

                                    <li class="nav-item dropdown">
										<a class="nav-link" href="portfoy.php">Portföy</a>
										</a>
						
									</li>

                                    <li class="nav-item dropdown">
										<a class="nav-link" href="blog.php">Blog</a>
										</a>
						
									</li>
                                          <li class="nav-item dropdown">
										<a class="nav-link" href="contact.php">İletişim</a>
										</a>
						
									</li>
                                          <li class="nav-item dropdown">
										<a class="nav-link" href="hesaplama-araclari.php">Hesaplamalar</a>
										</a>
						
									</li>
								
								</ul>
							</div>
						</nav>
					</div>
				</div> <!--/.top-header-->
			</div> <!-- /.inner-content -->
		</header> 
		<!-- /.theme-main-menu -->


        <!-- ============================
            İç Banner
        ============================ -->
        <div class="inner-banner-one inner-banner bg-pink text-center z-1 pt-160 lg-pt-130 pb-160 xl-pb-120 md-pb-80 position-relative">
            <div class="container">
                <h3 class="mb-35 xl-mb-20 pt-15">Gayrimenkul Uzmanı & Broker</h3>
                <ul class="theme-breadcrumb style-none d-inline-flex align-items-center justify-content-center position-relative z-1 bottom-line">
                    <li><a href="index.php">Anasayfa</a></li>
                
                    <li>Gayrimenkul Uzmanı & Broker</li>
                </ul>
            </div>
            <img src="images/lazy.svg" data-src="images/assets/ils_07.svg" alt="" class="lazy-img shapes w-100 illustration">
        </div>

       
        <!-- About Section -->
        <div class="block-feature-two mt-150 xl-mt-100">
            <div class="container">
                <div class="row gx-xl-5">
                    <div class="col-lg-6 wow fadeInLeft">
                        <div class="me-xxl-4">
                            <div class="title-one mb-60 lg-mb-40">
                                <div class="upper-title">Gökhan Aydınlı</div>
                                <h3>İstanbul'un <span>Gayrimenkul<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> DNA'sını Okuyan Uzman</h3>
                                <p class="fs-22">1981 İstanbul doğumlu, neredeyse iki dekada yayılan deneyimiyle İstanbul'un gayrimenkul DNA'sını okuyan, kentsel dönüşümü öngören ve bu dönüşümü aktif olarak yönlendiren sektörün önde gelen isimlerinden biridir.</p>
                            </div>
                            <a href="#contact" class="btn-two">Benimle İletişime Geçin</a>
                            
                        </div>
                    </div>
                    <div class="col-lg-6 wow fadeInRight">
                        <div class="block-two md-mt-40">
                            <div class="bg-wrapper">
                                <h5>Kim Miyim?</h5>
                                <p class="fs-22 lh-lg mt-20">Sadece satış yapan değil, şehrin hikayesini yazan bir profesyonel. Gayrimenkul sektöründe kentsel dönüşümü aktif olarak yönlendiren sektörün önde gelen isimlerinden biriyim.</p>
                                <h5 class="top-line">Misyonum</h5>
                                <p class="fs-22 lh-lg mt-20">Her müşterime en uygun yatırım fırsatını sunmak, sektörel bilgi ve deneyimim ile değer yaratmak, etik değerlerle uzun vadeli iş ilişkileri kurmak.</p>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Timeline Section -->
        <div class="timeline-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center mb-5">
                        <div class="title-one">
                            <div class="upper-title">Kariyer Yolculuğum</div>
                            <h3>Profesyonellik Yolculuğundaki <span>Dönüm Noktaları<img src="images/lazy.svg" data-src="images/shape/title_shape_07.svg" alt="" class="lazy-img"></span></h3>
                        </div>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-lg-12">
                        <ul class="timeline">
                            <li class="timeline-item">
                                <div class="timeline-badge">2007</div>
                                <div class="timeline-content">
                                    <h4>Temellerin Atıldığı Yıllar</h4>
                                    <p>Yeşilyurt'ta konut pazarlamasıyla başlayan yolculuk, İstanbul'un en prestijli bölgelerinden Boğaz hattına uzandı. Bu dönemde sadece satış yapmakla kalmayıp, müşteri memnuniyeti ve güven ilişkilerinin temellerini attım.</p>
                                </div>
                            </li>
                            
                            <li class="timeline-item">
                                <div class="timeline-badge">2011</div>
                                <div class="timeline-content">
                                    <h4>Dönüşümün Mimarı</h4>
                                    <p>Ticari gayrimenkule yöneldiğim bu dönemde, Şişli Dolapdere Caddesi'nde gerçekleştirdiğim satışlarla gerçek anlamda bir başarı hikayesi yazdım. <strong>Bir caddenin kimliğini, yapısını ve ticari dokusunu değiştirmek</strong> - bu sadece satış değil, kentsel planlama vizyonudur.</p>
                                </div>
                            </li>
                            
                            <li class="timeline-item">
                                <div class="timeline-badge">2015</div>
                                <div class="timeline-content">
                                    <h4>Otelcilik Sektöründe Öncülük</h4>
                                    <p>İstanbul'un prestijli otellerinin pazarlama süreçlerini üstlenerek, turizm sektöründeki gayrimenkul yatırımlarında derin bir uzmanlık geliştirdim. Bu deneyim, ticari gayrimenkulün sadece ofis ve mağaza olmadığını, hizmet sektörünün kalbi olan alanlarda da uzmanlaşmayı gerektirdiğini gösterdi.</p>
                                </div>
                            </li>
                            
                            <li class="timeline-item">
                                <div class="timeline-badge">2017</div>
                                <div class="timeline-content">
                                    <h4>Kentsel Dönüşümün Öncüsü</h4>
                                    <p>Yenibosna ve Güngören'de kentsel dönüşüm rüzgarları esmeye başlamadan önce, bu bölgelerdeki ticari potansiyeli gören nadir profesyonellerden biri oldum. Pazarlama stratejilerim ve satış başarılarım, bu bölgelerin bugünkü değerinin temellerini attı.</p>
                                </div>
                            </li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Expertise Section -->
        <div class="expertise-section" id="expertise">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center mb-5">
                        <div class="title-one">
                            <div class="upper-title">Uzmanlık Alanlarım</div>
                            <h3>Profesyonel <span>Hizmetler<img src="images/lazy.svg" data-src="images/shape/title_shape_05.svg" alt="" class="lazy-img"></span> ve Çözümler</h3>
                        </div>
                    </div>
                </div>
                
                <div class="expertise-grid">
                    <div class="expertise-card">
                        <div class="icon-box">
                            <i class="fas fa-building"></i>
                        </div>
                        <h4>Ticari Gayrimenkul Uzmanlığı</h4>
                        <ul class="expertise-list">
                            <li><i class="fas fa-check"></i>Ofis alanları ve plaza yatırımları</li>
                            <li><i class="fas fa-check"></i>Mağaza ve showroom pazarlama</li>
                            <li><i class="fas fa-check"></i>Endüstriyel tesis danışmanlığı</li>
                            <li><i class="fas fa-check"></i>İş merkezi ve plaza yönetimi</li>
                        </ul>
                    </div>
                    
                    <div class="expertise-card">
                        <div class="icon-box">
                            <i class="fas fa-hotel"></i>
                        </div>
                        <h4>Otel ve Turizm Gayrimenkulleri</h4>
                        <ul class="expertise-list">
                            <li><i class="fas fa-check"></i>Butik otel yatırımları</li>
                            <li><i class="fas fa-check"></i>Turizm tesisi pazarlama</li>
                            <li><i class="fas fa-check"></i>Otel satın alma danışmanlığı</li>
                            <li><i class="fas fa-check"></i>Turizm bölgesi arsa yatırımları</li>
                        </ul>
                    </div>
                    
                    <div class="expertise-card">
                        <div class="icon-box">
                            <i class="fas fa-home"></i>
                        </div>
                        <h4>Konut Pazarlama ve Yatırım</h4>
                        <ul class="expertise-list">
                            <li><i class="fas fa-check"></i>Prestij bölge konut satışları</li>
                            <li><i class="fas fa-check"></i>Yeni proje pazarlama</li>
                            <li><i class="fas fa-check"></i>Yatırım amaçlı konut danışmanlığı</li>
                            <li><i class="fas fa-check"></i>Yabancı yatırımcı hizmetleri</li>
                        </ul>
                    </div>
                </div>
            </div>
        </div>

        <!-- Stats Section -->
       

        <!-- Quote Section -->
        <div class="quote-section">
            <div class="container">
                <div class="row">
                    <div class="col-lg-8 mx-auto">
                        <div class="quote-content">
                            <h3>"Başarılı gayrimenkul yatırımı, sadece doğru mülkü bulmak değil; doğru zamanda, doğru stratejilerle hareket etmektir. Ben bu yolculukta sizin yanınızdayım."</h3>
                            <p>- Gökhan Aydınlı</p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Values Section -->
        <div class="block-feature-five position-relative z-1 pt-170 xl-pt-120 pb-130 xl-pb-100 lg-pb-80">
            <div class="container">
                <div class="row">
                    <div class="col-xl-8 m-auto">
                        <div class="title-one text-center mb-35 lg-mb-20">
                            <div class="upper-title">Değerlerim</div>
                            <h3>Farkı Yaratan <span>Yaklaşımım<img src="images/lazy.svg" data-src="images/shape/title_shape_07.svg" alt="" class="lazy-img"></span></h3>
                            <p class="fs-24 color-dark">18 yıllık deneyimimle size sunduğum değerler</p>
                        </div>
                    </div>
                </div>
                <div class="row justify-content-between">
                    <div class="col-xxl-11 m-auto">
                        <div class="row gx-xl-5 justify-content-center">
                            <div class="col-lg-3 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40">
                                    <div class="icon-box">
                                        <i class="fas fa-eye"></i>
                                    </div>
                                    <h5 class="mt-50 lg-mt-30 mb-15">Stratejik Düşünce</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Günün satışından ziyade, 5-10 yıl sonrasını gören vizyon ile hareket ederim.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40" data-wow-delay="0.1s">
                                    <div class="icon-box">
                                        <i class="fas fa-users"></i>
                                    </div>
                                    <h5 class="mt-50 lg-mt-30 mb-15">Müşteri Odaklı</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Her müşteri için özelleştirilmiş çözümler geliştiririm ve yatırım danışmanlığı yaparım.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40" data-wow-delay="0.2s">
                                    <div class="icon-box">
                                        <i class="fas fa-brain"></i>
                                    </div>
                                    <h5 class="mt-50 lg-mt-30 mb-15">Derin Bilgi</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">18 yıllık deneyimle edindiğim pazar bilgisi ile her projede artı değer yaratırım.</p>
                                </div>
                            </div>
                            <div class="col-lg-3 col-sm-6">
                                <div class="card-style-one text-center wow fadeInUp mt-40" data-wow-delay="0.3s">
                                    <div class="icon-box">
                                        <i class="fas fa-handshake"></i>
                                    </div>
                                    <h5 class="mt-50 lg-mt-30 mb-15">Etik Değerler</h5>
                                    <p class="pe-xxl-4 ps-xxl-4">Şeffaflık, güvenilirlik ve dürüstlük ilkeleriyle uzun vadeli ilişkiler kurarım.</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <img src="images/lazy.svg" data-src="images/shape/shape_07.svg" alt="" class="lazy-img shapes shape_01">
            <img src="images/lazy.svg" data-src="images/shape/shape_08.svg" alt="" class="lazy-img shapes shape_02">
        </div>

     

        <!-- Contact Section -->
        <div class="contact-section" id="contact">
            <div class="container">
                <div class="row">
                    <div class="col-lg-12 text-center mb-5">
                        <div class="title-one">
                            <div class="upper-title">İletişim</div>
                            <h3>Benimle <span>İletişime<img src="images/lazy.svg" data-src="images/shape/title_shape_06.svg" alt="" class="lazy-img"></span> Geçin</h3>
                            <p class="fs-22">Gayrimenkul yatırımlarınızda profesyonel danışmanlık için</p>
                        </div>
                    </div>
                </div>
                
                <div class="row justify-content-center">
                    <div class="col-lg-10">
                        <div class="contact-card">
                            <h4 class="mb-4">Gökhan Aydınlı ile çalışmak demek;</h4>
                            
                            <div class="contact-info">
                                <div class="contact-item">
                                    <i class="fas fa-chart-line"></i>
                                    <h6>18 yıllık sektörel deneyimden yararlanmak</h6>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-telescope"></i>
                                    <h6>Pazar trendlerini önceden görmek</h6>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-shield-alt"></i>
                                    <h6>Güvenilir ve şeffaf hizmet almak</h6>
                                </div>
                                <div class="contact-item">
                                    <i class="fas fa-arrow-up"></i>
                                    <h6>Yatırımınızı en üst düzeyde değerlendirmek</h6>
                                </div>
                            </div>
                            
                            <div class="contact-buttons">
                                <a href="tel:+905xxxxxxxxx" class="contact-btn">
                                    <i class="fas fa-phone"></i>Telefon
                                </a>
                                <a href="mailto:gokhan@aydinli.com" class="contact-btn">
                                    <i class="fas fa-envelope"></i>E-mail
                                </a>
                                <a href="#" class="contact-btn">
                                    <i class="fab fa-whatsapp"></i>WhatsApp
                                </a>
                                <a href="#" class="contact-btn">
                                    <i class="fab fa-linkedin"></i>LinkedIn
                                </a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

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
									<a href="index.html">
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
									<li><a href="index.html">Ana Sayfa</a></li>
									<li><a href="dashboard/membership.html" target="_blank">Üyelik</a></li>
									<li><a href="about_us_01.html">Hakkımızda</a></li>
									<li><a href="blog_01.html">Blog</a></li>
									<li><a href="blog_02.html">Kariyer</a></li>
									<li><a href="pricing_02.html">Fiyatlar</a></li>
									<li><a href="dashboard/dashboard-index.html" target="_blank">Panel</a></li>
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
		</div> 
        <!-- /.footer-four -->



        
            <!-- ################### Login Modal ####################### -->

        <!-- Login Modal -->
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
                                        <p class="fs-20 color-dark">Henüz hesabınız yok mu? <a href="#" onclick="switchToRegister()">Kayıt olun</a></p>
                                    </div>
                                    <form action="login.php" method="POST" id="loginForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta*</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="remember" name="remember">
                                                        <label for="remember">Beni hatırla</label>
                                                    </div>
                                                    <a href="#" onclick="showForgotPassword()">Şifremi Unuttum?</a>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">GİRİŞ YAP</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                                <div class="tab-pane" role="tabpanel" id="fc2">
                                    <div class="text-center mb-20">
                                        <h2>Kayıt Ol</h2>
                                        <p class="fs-20 color-dark">Zaten hesabınız var mı? <a href="#" onclick="switchToLogin()">Giriş yapın</a></p>
                                    </div>
                                    <form action="register.php" method="POST" id="registerForm">
                                        <div class="row">
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Ad Soyad*</label>
                                                    <input type="text" name="fullname" placeholder="Ad Soyadınız" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>E-posta*</label>
                                                    <input type="email" name="email" placeholder="ornek@email.com" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-25">
                                                    <label>Telefon</label>
                                                    <input type="tel" name="phone" placeholder="0555 555 55 55">
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre*</label>
                                                    <input type="password" name="password" placeholder="Şifrenizi girin" class="pass_log_id" required>
                                                    <span class="placeholder_icon"><span class="passVicon"><img src="images/icon/icon_68.svg" alt=""></span></span>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="input-group-meta position-relative mb-20">
                                                    <label>Şifre Tekrar*</label>
                                                    <input type="password" name="password_confirm" placeholder="Şifrenizi tekrar girin" required>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <div class="agreement-checkbox d-flex justify-content-between align-items-center">
                                                    <div>
                                                        <input type="checkbox" id="terms" name="terms" required>
                                                        <label for="terms">"Kayıt Ol" butonuna tıklayarak <a href="terms.php" target="_blank">Şartlar & Koşullar</a> ile <a href="privacy.php" target="_blank">Gizlilik Politikası</a>'nı kabul ediyorum</label>
                                                    </div>
                                                </div>
                                            </div>
                                            <div class="col-12">
                                                <button type="submit" class="btn-two w-100 text-uppercase d-block mt-20">KAYIT OL</button>
                                            </div>
                                        </div>
                                    </form>
                                </div>
                            </div>
                            
                            <div class="d-flex align-items-center mt-30 mb-10">
                                <div class="line"></div>
                                <span class="pe-3 ps-3 fs-6">VEYA</span>
                                <div class="line"></div>
                            </div>
                            <div class="row">
                                <div class="col-sm-6">
                                    <a href="#" onclick="loginWithGoogle()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                        <img src="images/icon/google.png" alt="">
                                        <span class="ps-3">Google ile Giriş</span>
                                    </a>
                                </div>
                                <div class="col-sm-6">
                                    <a href="#" onclick="loginWithFacebook()" class="social-use-btn d-flex align-items-center justify-content-center tran3s w-100 mt-10">
                                        <img src="images/icon/facebook.png" alt="">
                                        <span class="ps-3">Facebook ile Giriş</span>
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Scroll to Top Button -->
        <button class="scroll-top" onclick="scrollToTop()">
            <i class="bi bi-arrow-up-short"></i>
        </button>
    </div>

    <!-- JS Dosyaları -->
    <script src="vendor/jquery.min.js"></script>
    <script src="vendor/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="vendor/wow/wow.min.js"></script>
    <script src="vendor/slick/slick.min.js"></script>
    <script src="vendor/fancybox/fancybox.umd.js"></script>
    <script src="vendor/jquery.lazy.min.js"></script>
    <script src="vendor/jquery.counterup.min.js"></script>
    <script src="vendor/jquery.waypoints.min.js"></script>
    <script src="js/theme.js"></script>

    <script>
    // Modal fonksiyonları
    function switchToRegister() {
        document.querySelector('#fc1').classList.remove('show', 'active');
        document.querySelector('#fc2').classList.add('show', 'active');
        document.querySelector('[data-bs-target="#fc1"]').classList.remove('active');
        document.querySelector('[data-bs-target="#fc2"]').classList.add('active');
    }

    function switchToLogin() {
        document.querySelector('#fc2').classList.remove('show', 'active');
        document.querySelector('#fc1').classList.add('show', 'active');
        document.querySelector('[data-bs-target="#fc2"]').classList.remove('active');
        document.querySelector('[data-bs-target="#fc1"]').classList.add('active');
    }

    function showForgotPassword() {
        alert('Şifre sıfırlama linki e-posta adresinize gönderilecektir.');
    }

    function loginWithGoogle() {
        alert('Google ile giriş özelliği yakında aktif olacak.');
    }

    function loginWithFacebook() {
        alert('Facebook ile giriş özelliği yakında aktif olacak.');
    }

    function scrollToTop() {
        window.scrollTo({
            top: 0,
            behavior: 'smooth'
        });
    }

    // Güvenli storage kontrolü
    function safeStorage() {
        try {
            return typeof(Storage) !== "undefined" && localStorage;
        } catch (e) {
            return false;
        }
    }

    // Element varlık kontrolü
    function elementExists(selector) {
        return document.querySelector(selector) !== null;
    }

    // Form validasyonu ve sayfa yükleme olayları
    document.addEventListener('DOMContentLoaded', function() {
        console.log('Sayfa yüklendi, JavaScript başlatılıyor...');
        
        // Login form validasyonu
        const loginForm = document.getElementById('loginForm');
        if (loginForm) {
            loginForm.addEventListener('submit', function(e) {
                const email = this.querySelector('input[name="email"]').value;
                const password = this.querySelector('input[name="password"]').value;
                
                if (!email || !password) {
                    e.preventDefault();
                    alert('Lütfen tüm alanları doldurun.');
                    return false;
                }
                
                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Geçerli bir e-posta adresi girin.');
                    return false;
                }
            });
        }
        
        // Register form validasyonu
        const registerForm = document.getElementById('registerForm');
        if (registerForm) {
            registerForm.addEventListener('submit', function(e) {
                const fullname = this.querySelector('input[name="fullname"]').value;
                const email = this.querySelector('input[name="email"]').value;
                const password = this.querySelector('input[name="password"]').value;
                const passwordConfirm = this.querySelector('input[name="password_confirm"]').value;
                const terms = this.querySelector('input[name="terms"]').checked;
                
                if (!fullname || !email || !password || !passwordConfirm) {
                    e.preventDefault();
                    alert('Lütfen tüm zorunlu alanları doldurun.');
                    return false;
                }
                
                if (!email.includes('@')) {
                    e.preventDefault();
                    alert('Geçerli bir e-posta adresi girin.');
                    return false;
                }
                
                if (password.length < 6) {
                    e.preventDefault();
                    alert('Şifre en az 6 karakter olmalıdır.');
                    return false;
                }
                
                if (password !== passwordConfirm) {
                    e.preventDefault();
                    alert('Şifreler eşleşmiyor.');
                    return false;
                }
                
                if (!terms) {
                    e.preventDefault();
                    alert('Şartlar ve koşulları kabul etmelisiniz.');
                    return false;
                }
            });
        }

        // Smooth scrolling
        document.addEventListener('click', function(e) {
            if (e.target.matches('a[href^="#"]')) {
                e.preventDefault();
                const target = document.querySelector(e.target.getAttribute('href'));
                if (target) {
                    target.scrollIntoView({
                        behavior: 'smooth',
                        block: 'start'
                    });
                }
            }
        });

        // Counter animasyonu
        const animateCounters = () => {
            const counters = document.querySelectorAll('.counter');
            counters.forEach(counter => {
                const target = parseInt(counter.textContent);
                const increment = target / 100;
                let current = 0;
                
                const updateCounter = () => {
                    if (current < target) {
                        current += increment;
                        counter.textContent = Math.ceil(current);
                        setTimeout(updateCounter, 20);
                    } else {
                        counter.textContent = target;
                    }
                };
                
                const rect = counter.getBoundingClientRect();
                if (rect.top >= 0 && rect.bottom <= window.innerHeight) {
                    updateCounter();
                }
            });
        };

        // Scroll to top button
        const scrollBtn = document.querySelector('.scroll-top');
        if (scrollBtn) {
            window.addEventListener('scroll', function() {
                if (window.pageYOffset > 300) {
                    scrollBtn.style.opacity = '1';
                    scrollBtn.style.display = 'block';
                } else {
                    scrollBtn.style.opacity = '0';
                    setTimeout(() => {
                        if (window.pageYOffset <= 300) {
                            scrollBtn.style.display = 'none';
                        }
                    }, 300);
                }
            });
        }

        // Header sticky effect
        window.addEventListener('scroll', () => {
            const header = document.querySelector('.theme-main-menu');
            if (window.pageYOffset > 100) {
                header.style.background = 'rgba(255, 255, 255, 0.95)';
                header.style.backdropFilter = 'blur(10px)';
            } else {
                header.style.background = 'var(--white)';
                header.style.backdropFilter = 'none';
            }
        });

        // Initialize animations
        window.addEventListener('scroll', animateCounters);
        window.addEventListener('load', animateCounters);

        // Lazy loading güvenli başlatma
        if (typeof $.fn.lazy !== 'undefined' && $('.lazy-img').length > 0) {
            try {
                $('.lazy-img').lazy({
                    effect: "fadeIn",
                    effectTime: 600,
                    threshold: 0,
                    fallbackSrc: 'images/placeholder.png'
                });
                console.log('Lazy loading başlatıldı');
            } catch (e) {
                console.log('Lazy loading başlatılamadı:', e);
                $('.lazy-img').each(function() {
                    var src = $(this).attr('data-src');
                    if (src) {
                        $(this).attr('src', src);
                        $(this).removeClass('lazy-img');
                    }
                });
            }
        }

        // WOW animasyonu
        if (typeof WOW !== 'undefined') {
            try {
                new WOW().init();
                console.log('WOW animasyonu başlatıldı');
            } catch (e) {
                console.log('WOW animasyonu başlatılamadı:', e);
                document.body.classList.add('storage-disabled');
            }
        }

        // Preloader'ı güvenli şekilde kapat
        const preloader = document.getElementById('preloader');
        if (preloader) {
            setTimeout(() => {
                preloader.style.opacity = '0';
                setTimeout(() => {
                    preloader.style.display = 'none';
                }, 500);
            }, 1000);
        }

        // Intersection Observer for animations
        const observerOptions = {
            threshold: 0.1,
            rootMargin: '0px 0px -50px 0px'
        };

        const observer = new IntersectionObserver((entries) => {
            entries.forEach(entry => {
                if (entry.isIntersecting) {
                    entry.target.classList.add('fade-in-up');
                }
            });
        }, observerOptions);

        // Observe elements
        document.querySelectorAll('.card-style-one, .timeline-item, .expertise-card, .stats-box').forEach(el => {
            observer.observe(el);
        });
    });

    // Error handling
    window.addEventListener('error', function(e) {
        console.log('JavaScript hatası yakalandı:', e.error);
        if (e.error && e.error.message.includes('storage')) {
            console.log('Storage hatası tespit edildi, fallback moduna geçiliyor');
            document.body.classList.add('storage-disabled');
        }
    });
    </script>
</body>
</html>