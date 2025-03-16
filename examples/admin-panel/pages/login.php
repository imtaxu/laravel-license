<?php

/**
 * License Management Panel - Login Page
 */
require_once 'auth.php';
require_once 'functions.php';
require_once 'config.php';
require_once 'language.php';

// Oturum başlat
session_start();

// Dil dosyasını yükle
loadLanguage();

// Zaten giriş yapmışsa yönlendir
if (isset($_SESSION['user_id'])) {
    header('Location: index.php?page=dashboard');
    exit;
}

// Dil dosyasını yükle
loadLanguage();

// Auth sınıfını başlat
$auth = new Auth();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = $_POST['email'] ?? '';
    $password = $_POST['password'] ?? '';

    // Giriş yap
    if ($auth->login($email, $password)) {
        // Başarılı giriş, yönlendir
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        // Başarısız giriş
        $loginError = __('login_error');
    }
}
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4><?php echo __('app_name'); ?></h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($loginError)): ?>
                        <div class="alert alert-danger"><?php echo $loginError; ?></div>
                    <?php endif; ?>

                    <form method="post" action="index.php?page=login">
                        <div class="mb-3">
                            <label for="email" class="form-label"><?php echo __('email'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-envelope"></i></span>
                                <input type="email" class="form-control" id="email" name="email" required>
                            </div>
                        </div>

                        <div class="mb-4">
                            <label for="password" class="form-label"><?php echo __('password'); ?></label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>

                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg"><?php echo __('login_button'); ?></button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted py-3">
                    <small>&copy; <?php echo date('Y'); ?> <?php echo __('app_name'); ?></small>
                </div>
            </div>
        </div>
    </div>
</div>