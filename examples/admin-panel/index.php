<?php

/**
 * Lisans Yönetim Paneli
 * 
 * Bu dosya, lisans yönetim panelinin ana giriş noktasıdır.
 * Güvenli giriş, lisans oluşturma, lisans kontrol ve geçersiz lisans isteklerini görüntüleme
 * özelliklerine sahip bir panel sunar.
 */

session_start();
require_once 'config.php';
require_once 'functions.php';
require_once 'auth.php';
// Lisans bildirimleri özelliği kaldırıldı

// Oturum kontrolü
$auth = new Auth();
$loggedIn = $auth->isLoggedIn();

// Lisans bildirimleri özelliği kaldırıldı
$licenseNotifications = null;

// Sayfa yönlendirme
$page = isset($_GET['page']) ? $_GET['page'] : 'dashboard';
$allowedPages = ['dashboard', 'licenses', 'create-license', 'invalid-requests', 'login', 'logout'];

if (!in_array($page, $allowedPages)) {
    $page = 'dashboard';
}

// Giriş yapmamış kullanıcıları login sayfasına yönlendir
if (!$loggedIn && $page != 'login') {
    header('Location: index.php?page=login');
    exit;
}

// Giriş yapmış kullanıcıları dashboard'a yönlendir
if ($loggedIn && $page == 'login') {
    header('Location: index.php?page=dashboard');
    exit;
}

// Çıkış işlemi
if ($page == 'logout' && $loggedIn) {
    $auth->logout();
    header('Location: index.php?page=login');
    exit;
}

// Sayfa başlığı
$pageTitle = 'Lisans Yönetim Paneli';
switch ($page) {
    case 'dashboard':
        $pageTitle = 'Kontrol Paneli - Lisans Yönetim Paneli';
        break;
    case 'licenses':
        $pageTitle = 'Lisanslar - Lisans Yönetim Paneli';
        break;
    case 'create-license':
        $pageTitle = 'Lisans Oluştur - Lisans Yönetim Paneli';
        break;
    case 'invalid-requests':
        $pageTitle = 'Geçersiz İstekler - Lisans Yönetim Paneli';
        break;
    case 'login':
        $pageTitle = 'Giriş - Lisans Yönetim Paneli';
        break;
}
?>
<!DOCTYPE html>
<html lang="tr">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?php echo $pageTitle; ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="assets/css/style.css">
</head>

<body>
    <?php if ($loggedIn): ?>
        <div class="container-fluid">
            <div class="row">
                <!-- Sidebar -->
                <nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
                    <div class="position-sticky pt-3">
                        <div class="px-3 py-4 text-white">
                            <h4>Lisans Yönetimi</h4>
                        </div>
                        <ul class="nav flex-column">
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                                    <i class="bi bi-speedometer2 me-2"></i> Kontrol Paneli
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page == 'licenses' ? 'active' : ''; ?>" href="index.php?page=licenses">
                                    <i class="bi bi-key me-2"></i> Lisanslar
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page == 'create-license' ? 'active' : ''; ?>" href="index.php?page=create-license">
                                    <i class="bi bi-plus-circle me-2"></i> Lisans Oluştur
                                </a>
                            </li>
                            <li class="nav-item">
                                <a class="nav-link <?php echo $page == 'invalid-requests' ? 'active' : ''; ?>" href="index.php?page=invalid-requests">
                                    <i class="bi bi-exclamation-triangle me-2"></i> Geçersiz İstekler
                                </a>
                            </li>
                            <li class="nav-item mt-5">
                                <a class="nav-link" href="index.php?page=logout">
                                    <i class="bi bi-box-arrow-right me-2"></i> Çıkış Yap
                                </a>
                            </li>
                        </ul>
                    </div>
                </nav>

                <!-- Ana içerik -->
                <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
                    <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                        <h1 class="h2"><?php echo $pageTitle; ?></h1>
                    </div>

                    <!-- Lisans bildirimleri özelliği kaldırıldı -->

                    <?php
                    // Sayfa içeriğini yükle
                    $pageFile = 'pages/' . $page . '.php';
                    if (file_exists($pageFile)) {
                        include $pageFile;
                    } else {
                        echo '<div class="alert alert-danger">Sayfa bulunamadı.</div>';
                    }
                    ?>
                </main>
            </div>
        </div>
    <?php else: ?>
        <?php include 'pages/login.php'; ?>
    <?php endif; ?>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://code.jquery.com/jquery-3.6.3.min.js"></script>
    <script src="assets/js/script.js"></script>

    <?php if ($loggedIn): ?>
        <script>
            $(document).ready(function() {
                // Lisans bildirimleri özelliği kaldırıldı
            });
        </script>
    <?php endif; ?>
</body>

</html>