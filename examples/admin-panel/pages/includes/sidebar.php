<?php
/**
 * Lisans Yönetim Paneli - Sidebar
 */

// Dil dosyasını yükle
require_once __DIR__ . '/../../language.php';
?>
<nav id="sidebar" class="col-md-3 col-lg-2 d-md-block bg-dark sidebar collapse">
    <div class="position-sticky pt-3">
        <ul class="nav flex-column">
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'dashboard' ? 'active' : ''; ?>" href="index.php?page=dashboard">
                    <i class="bi bi-speedometer2"></i>
                    <?php echo __('dashboard'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'licenses' ? 'active' : ''; ?>" href="index.php?page=licenses">
                    <i class="bi bi-key"></i>
                    <?php echo __('licenses'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'create-license' ? 'active' : ''; ?>" href="index.php?page=create-license">
                    <i class="bi bi-plus-circle"></i>
                    <?php echo __('create_license'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'invalid-requests' ? 'active' : ''; ?>" href="index.php?page=invalid-requests">
                    <i class="bi bi-exclamation-triangle"></i>
                    <?php echo __('invalid_requests'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'rate-limits' ? 'active' : ''; ?>" href="index.php?page=rate-limits">
                    <i class="bi bi-shield-lock"></i>
                    <?php echo __('rate_limits'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'users' ? 'active' : ''; ?>" href="index.php?page=users">
                    <i class="bi bi-people"></i>
                    <?php echo __('users'); ?>
                </a>
            </li>
        </ul>
        
        <h6 class="sidebar-heading d-flex justify-content-between align-items-center px-3 mt-4 mb-1 text-muted">
            <span><?php echo __('settings'); ?></span>
        </h6>
        <ul class="nav flex-column mb-2">
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'profile' ? 'active' : ''; ?>" href="index.php?page=profile">
                    <i class="bi bi-person"></i>
                    <?php echo __('profile'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link <?php echo $page == 'settings' ? 'active' : ''; ?>" href="index.php?page=settings">
                    <i class="bi bi-gear"></i>
                    <?php echo __('settings'); ?>
                </a>
            </li>
            <li class="nav-item">
                <a class="nav-link" href="index.php?page=logout">
                    <i class="bi bi-box-arrow-right"></i>
                    <?php echo __('logout'); ?>
                </a>
            </li>
        </ul>
    </div>
</nav>
