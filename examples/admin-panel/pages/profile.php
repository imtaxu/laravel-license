<?php

/**
 * License Management Panel - Profile Page
 */
// This file is included in index.php
// require_once 'auth.php';
// require_once 'functions.php';
// require_once 'config.php';
// require_once 'language.php';

// Oturum kontrolü index.php'de yapılıyor

// Dil dosyası index.php'de yükleniyor

$pageTitle = __('profile');
$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Auth sınıfını başlat
$auth = new Auth();

// Kullanıcı bilgilerini getir
$user = $auth->getUserInfo($userId);

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);

    // Temel doğrulamalar
    if (empty($name) || empty($email)) {
        $message = __('name_email_required');
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = __('valid_email_required');
        $messageType = 'danger';
    } else {
        // Şifre değiştirilecek mi?
        $passwordChanged = false;

        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            // Mevcut şifre doğru mu?
            if (!$auth->verifyPassword($userId, $currentPassword)) {
                $message = __('current_password_incorrect');
                $messageType = 'danger';
            } elseif (empty($newPassword) || empty($confirmPassword)) {
                $message = __('new_password_required');
                $messageType = 'danger';
            } elseif ($newPassword !== $confirmPassword) {
                $message = __('passwords_not_match');
                $messageType = 'danger';
            } elseif (strlen($newPassword) < 8) {
                $message = __('password_min_length');
                $messageType = 'danger';
            } else {
                $passwordChanged = true;
            }
        }

        // Hata yoksa güncelleme yap
        if (empty($message)) {
            $userData = [
                'name' => $name,
                'email' => $email
            ];

            if ($passwordChanged) {
                $userData['new_password'] = $newPassword;
            }

            // Kullanıcı bilgilerini güncelle
            if ($auth->updateUserProfile($userId, $userData)) {
                // Güncel kullanıcı bilgilerini al
                $user = $auth->getUserInfo($userId);

                $message = __('profile_updated_success');
                $messageType = 'success';

                // Şifre değiştiyse oturumu yenile
                if ($passwordChanged) {
                    $message .= ' ' . __('password_changed_relogin');
                    session_regenerate_id(true);
                }
            } else {
                $message = __('update_error');
                $messageType = 'danger';
            }
        }
    }
}

// Header ve sidebar index.php'de dahil ediliyor
?>

<div class="row">
    <main class="col-md-12 px-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo __('profile_information'); ?></h1>
            </div>

            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>

            <div class="row">
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo __('update_profile'); ?></h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo __('email'); ?></label>
                                    <input type="text" class="form-control" id="email_readonly" value="<?php echo htmlspecialchars($user['email']); ?>" readonly>
                                    <small class="text-muted"><?php echo __('email_readonly'); ?></small>
                                </div>

                                <div class="mb-3">
                                    <label for="name" class="form-label"><?php echo __('full_name'); ?></label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>

                                <div class="mb-3">
                                    <label for="email" class="form-label"><?php echo __('email_address'); ?></label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>

                                <hr>
                                <h5><?php echo __('change_password'); ?></h5>
                                <p class="text-muted small"><?php echo __('password_change_info'); ?></p>

                                <div class="mb-3">
                                    <label for="current_password" class="form-label"><?php echo __('current_password'); ?></label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>

                                <div class="mb-3">
                                    <label for="new_password" class="form-label"><?php echo __('new_password'); ?></label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>

                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label"><?php echo __('confirm_password'); ?></label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>

                                <button type="submit" class="btn btn-primary"><?php echo __('update_information'); ?></button>
                            </form>
                        </div>
                    </div>
                </div>

                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0"><?php echo __('account_information'); ?></h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th><?php echo __('last_login'); ?>:</th>
                                    <td><?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : __('no_information'); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo __('account_created'); ?>:</th>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th><?php echo __('last_update'); ?>:</th>
                                    <td><?php echo isset($user['updated_at']) ? date('d.m.Y H:i', strtotime($user['updated_at'])) : date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                </tr>
                            </table>

                            <div class="alert alert-info mt-3">
                                <h5><?php echo __('security_tips'); ?></h5>
                                <ul class="mb-0">
                                    <li><?php echo __('change_password_regularly'); ?></li>
                                    <li><?php echo __('use_strong_passwords'); ?></li>
                                    <li><?php echo __('dont_share_password'); ?></li>
                                    <li><?php echo __('logout_when_done'); ?></li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
    </main>
</div>