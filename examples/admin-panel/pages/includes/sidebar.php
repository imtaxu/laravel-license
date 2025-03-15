<?php
/**
 * Lisans Yönetim Paneli - Sidebar
 */
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Dashboard' ? 'active' : ''; ?>" href="dashboard.php">
                    <i class="bi bi-speedometer2"></i>
                    Dashboard
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Lisanslar' ? 'active' : ''; ?>" href="licenses.php">
                    <i class="bi bi-key"></i>
                    Lisanslar
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Lisans Oluştur' ? 'active' : ''; ?>" href="create-license.php">
                    <i class="bi bi-plus-circle"></i>
                    Lisans Oluştur
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Geçersiz İstekler' ? 'active' : ''; ?>" href="invalid-requests.php">
                    <i class="bi bi-exclamation-triangle"></i>
                    Geçersiz İstekler
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Rate Limits' ? 'active' : ''; ?>" href="rate-limits.php">
                    <i class="bi bi-shield-lock"></i>
                    Rate Limits
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span>Ayarlar</span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $pageTitle == 'Profil' ? 'active' : ''; ?>" href="profile.php">
                    <i class="bi bi-person"></i>
                    Profil
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="../logout.php">
                    <i class="bi bi-box-arrow-right"></i>
                    Çıkış Yap
                </a>
            </li>
        </ul>
    </div>
</nav>
