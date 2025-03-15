<?php
/**
 * Lisans Yönetim Paneli - Profil Sayfası
 */
require_once '../auth.php';
require_once '../functions.php';
require_once '../config.php';

// Oturum kontrolü
checkSession();

$pageTitle = 'Profil';
$userId = $_SESSION['user_id'];
$message = '';
$messageType = '';

// Kullanıcı bilgilerini getir
$stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
$stmt->execute([$userId]);
$user = $stmt->fetch(PDO::FETCH_ASSOC);

// Form gönderildi mi kontrol et
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $name = trim($_POST['name']);
    $email = trim($_POST['email']);
    $currentPassword = trim($_POST['current_password']);
    $newPassword = trim($_POST['new_password']);
    $confirmPassword = trim($_POST['confirm_password']);
    
    // Temel doğrulamalar
    if (empty($name) || empty($email)) {
        $message = 'Ad ve e-posta alanları zorunludur.';
        $messageType = 'danger';
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
        $message = 'Geçerli bir e-posta adresi giriniz.';
        $messageType = 'danger';
    } else {
        // Şifre değiştirilecek mi?
        $passwordChanged = false;
        
        if (!empty($currentPassword) || !empty($newPassword) || !empty($confirmPassword)) {
            // Mevcut şifre doğru mu?
            if (!password_verify($currentPassword, $user['password'])) {
                $message = 'Mevcut şifre doğru değil.';
                $messageType = 'danger';
            } elseif (empty($newPassword) || empty($confirmPassword)) {
                $message = 'Yeni şifre ve onay alanları zorunludur.';
                $messageType = 'danger';
            } elseif ($newPassword !== $confirmPassword) {
                $message = 'Yeni şifre ve onay şifresi eşleşmiyor.';
                $messageType = 'danger';
            } elseif (strlen($newPassword) < 8) {
                $message = 'Şifre en az 8 karakter uzunluğunda olmalıdır.';
                $messageType = 'danger';
            } else {
                $passwordChanged = true;
            }
        }
        
        // Hata yoksa güncelleme yap
        if (empty($message)) {
            try {
                $pdo->beginTransaction();
                
                // Kullanıcı bilgilerini güncelle
                if ($passwordChanged) {
                    $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, password = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $email, $hashedPassword, $userId]);
                } else {
                    $stmt = $pdo->prepare("UPDATE users SET name = ?, email = ?, updated_at = NOW() WHERE id = ?");
                    $stmt->execute([$name, $email, $userId]);
                }
                
                $pdo->commit();
                
                // Güncel kullanıcı bilgilerini al
                $stmt = $pdo->prepare("SELECT * FROM users WHERE id = ?");
                $stmt->execute([$userId]);
                $user = $stmt->fetch(PDO::FETCH_ASSOC);
                
                $message = 'Profil bilgileriniz başarıyla güncellendi.';
                $messageType = 'success';
                
                // Şifre değiştiyse oturumu yenile
                if ($passwordChanged) {
                    $message .= ' Şifreniz değiştirildi. Lütfen yeni şifrenizle tekrar giriş yapın.';
                    session_regenerate_id(true);
                }
                
            } catch (PDOException $e) {
                $pdo->rollBack();
                $message = 'Hata oluştu: ' . $e->getMessage();
                $messageType = 'danger';
            }
        }
    }
}

// Header'ı dahil et
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2">Profil Bilgileri</h1>
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
                            <h5 class="card-title mb-0">Profil Bilgilerini Güncelle</h5>
                        </div>
                        <div class="card-body">
                            <form method="post" action="">
                                <div class="mb-3">
                                    <label for="username" class="form-label">Kullanıcı Adı</label>
                                    <input type="text" class="form-control" id="username" value="<?php echo htmlspecialchars($user['username']); ?>" readonly>
                                    <small class="text-muted">Kullanıcı adı değiştirilemez.</small>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="name" class="form-label">Ad Soyad</label>
                                    <input type="text" class="form-control" id="name" name="name" value="<?php echo htmlspecialchars($user['name']); ?>" required>
                                </div>
                                
                                <div class="mb-3">
                                    <label for="email" class="form-label">E-posta Adresi</label>
                                    <input type="email" class="form-control" id="email" name="email" value="<?php echo htmlspecialchars($user['email']); ?>" required>
                                </div>
                                
                                <hr>
                                <h5>Şifre Değiştir</h5>
                                <p class="text-muted small">Şifrenizi değiştirmek istemiyorsanız aşağıdaki alanları boş bırakın.</p>
                                
                                <div class="mb-3">
                                    <label for="current_password" class="form-label">Mevcut Şifre</label>
                                    <input type="password" class="form-control" id="current_password" name="current_password">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="new_password" class="form-label">Yeni Şifre</label>
                                    <input type="password" class="form-control" id="new_password" name="new_password">
                                </div>
                                
                                <div class="mb-3">
                                    <label for="confirm_password" class="form-label">Yeni Şifre (Tekrar)</label>
                                    <input type="password" class="form-control" id="confirm_password" name="confirm_password">
                                </div>
                                
                                <button type="submit" class="btn btn-primary">Bilgileri Güncelle</button>
                            </form>
                        </div>
                    </div>
                </div>
                
                <div class="col-md-6">
                    <div class="card">
                        <div class="card-header">
                            <h5 class="card-title mb-0">Hesap Bilgileri</h5>
                        </div>
                        <div class="card-body">
                            <table class="table">
                                <tr>
                                    <th>Son Giriş:</th>
                                    <td><?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : 'Bilgi yok'; ?></td>
                                </tr>
                                <tr>
                                    <th>Hesap Oluşturulma:</th>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['created_at'])); ?></td>
                                </tr>
                                <tr>
                                    <th>Son Güncelleme:</th>
                                    <td><?php echo date('d.m.Y H:i', strtotime($user['updated_at'])); ?></td>
                                </tr>
                            </table>
                            
                            <div class="alert alert-info mt-3">
                                <h5>Güvenlik İpuçları</h5>
                                <ul class="mb-0">
                                    <li>Şifrenizi düzenli olarak değiştirin.</li>
                                    <li>Güçlü şifreler kullanın (büyük/küçük harf, rakam ve özel karakterler).</li>
                                    <li>Şifrenizi başkalarıyla paylaşmayın.</li>
                                    <li>İşiniz bittiğinde sistemden çıkış yapın.</li>
                                </ul>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<?php include 'includes/footer.php'; ?>
