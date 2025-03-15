<?php
/**
 * Lisans Yönetim Paneli - Giriş Sayfası
 */

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';
    
    // Giriş yap
    if ($auth->login($username, $password)) {
        // Başarılı giriş, yönlendir
        header('Location: index.php?page=dashboard');
        exit;
    } else {
        // Başarısız giriş
        $loginError = 'Kullanıcı adı veya şifre hatalı.';
    }
}
?>

<div class="container">
    <div class="row justify-content-center mt-5">
        <div class="col-md-6 col-lg-5">
            <div class="card shadow-lg">
                <div class="card-header bg-primary text-white text-center py-3">
                    <h4>Lisans Yönetim Paneli</h4>
                </div>
                <div class="card-body p-4">
                    <?php if (isset($loginError)): ?>
                        <div class="alert alert-danger"><?php echo $loginError; ?></div>
                    <?php endif; ?>
                    
                    <form method="post" action="index.php?page=login">
                        <div class="mb-3">
                            <label for="username" class="form-label">Kullanıcı Adı</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-person"></i></span>
                                <input type="text" class="form-control" id="username" name="username" required>
                            </div>
                        </div>
                        
                        <div class="mb-4">
                            <label for="password" class="form-label">Şifre</label>
                            <div class="input-group">
                                <span class="input-group-text"><i class="bi bi-lock"></i></span>
                                <input type="password" class="form-control" id="password" name="password" required>
                            </div>
                        </div>
                        
                        <div class="d-grid">
                            <button type="submit" class="btn btn-primary btn-lg">Giriş Yap</button>
                        </div>
                    </form>
                </div>
                <div class="card-footer text-center text-muted py-3">
                    <small>© <?php echo date('Y'); ?> Lisans Yönetim Paneli</small>
                </div>
            </div>
        </div>
    </div>
</div>
