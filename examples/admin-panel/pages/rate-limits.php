<?php
/**
 * Lisans Yönetim Paneli - Rate Limits Sayfası
 * 
 * Bu sayfa, rate limiting istatistiklerini ve engellenen IP'leri görüntüler.
 */

// Oturum ve yetki kontrolü
require_once '../auth.php';
$auth = new Auth();
if (!$auth->isLoggedIn()) {
    header('Location: login.php');
    exit;
}

// Gerekli dosyaları dahil et
require_once '../config.php';
require_once '../functions.php';

// Veritabanı bağlantısı
$db = getDbConnection();

// RateLimiter sınıfını dahil et
require_once '../RateLimiter.php';
$rateLimiter = new RateLimiter($db);

// Temizleme işlemi
$cleanupMessage = '';
if (isset($_POST['cleanup']) && $_POST['cleanup'] == 1) {
    $days = isset($_POST['days']) ? (int)$_POST['days'] : 30;
    $deletedCount = $rateLimiter->cleanup($days);
    $cleanupMessage = "{$deletedCount} adet eski rate limit kaydı temizlendi.";
}

// Engellenen IP'leri kaldırma
$unblockMessage = '';
if (isset($_POST['unblock']) && !empty($_POST['ip_key'])) {
    $ipKey = explode(':', $_POST['ip_key']);
    if (count($ipKey) == 2) {
        $key = $ipKey[0];
        $ip = $ipKey[1];
        $result = $rateLimiter->reset($key, $ip);
        $unblockMessage = $result ? "IP adresi engeli kaldırıldı: {$ip}" : "IP adresi engeli kaldırılamadı: {$ip}";
    }
}

// Rate limit istatistiklerini al
$stats = $rateLimiter->getStats();

// Engellenen IP'leri listele
$blockedIps = $rateLimiter->getBlockedIps();

// Sayfa başlığı
$pageTitle = 'Rate Limits';
?>
<!DOCTYPE html>
<html lang="tr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Rate Limits - Lisans Yönetim Paneli</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.10.3/font/bootstrap-icons.css">
    <link rel="stylesheet" href="../assets/css/style.css">
</head>
<body>
    <div class="container-fluid">
        <div class="row">
            <!-- Sidebar -->
            <?php include 'includes/sidebar.php'; ?>
            
            <!-- Main Content -->
            <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4 py-4">
                <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                    <h1 class="h2">Rate Limits</h1>
                    <div class="btn-toolbar mb-2 mb-md-0">
                        <button type="button" class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#cleanupModal">
                            <i class="bi bi-trash"></i> Eski Kayıtları Temizle
                        </button>
                    </div>
                </div>
                
                <?php if (!empty($cleanupMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($cleanupMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <?php if (!empty($unblockMessage)): ?>
                <div class="alert alert-success alert-dismissible fade show" role="alert">
                    <?php echo htmlspecialchars($unblockMessage); ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
                <?php endif; ?>
                
                <!-- Rate Limit İstatistikleri -->
                <div class="row">
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-primary shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1">
                                            Toplam Kayıt
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['total_records']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-list-ul fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-warning shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1">
                                            Son 24 Saat Deneme
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['attempts_24h']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-clock-history fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                    
                    <div class="col-xl-3 col-md-6 mb-4">
                        <div class="card border-left-danger shadow h-100 py-2">
                            <div class="card-body">
                                <div class="row no-gutters align-items-center">
                                    <div class="col mr-2">
                                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1">
                                            Engellenen IP
                                        </div>
                                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo number_format($stats['blocked_records']); ?></div>
                                    </div>
                                    <div class="col-auto">
                                        <i class="bi bi-shield-exclamation fa-2x text-gray-300"></i>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Engellenen IP'ler Tablosu -->
                <div class="card mb-4">
                    <div class="card-header">
                        <i class="bi bi-shield-lock me-1"></i>
                        Engellenen IP'ler
                    </div>
                    <div class="card-body">
                        <?php if (empty($blockedIps)): ?>
                            <div class="alert alert-info">
                                Şu anda engellenen IP adresi bulunmamaktadır.
                            </div>
                        <?php else: ?>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead>
                                        <tr>
                                            <th>IP Adresi</th>
                                            <th>Anahtar</th>
                                            <th>Deneme Sayısı</th>
                                            <th>Son Deneme</th>
                                            <th>Engel Bitiş</th>
                                            <th>İşlemler</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($blockedIps as $ip): ?>
                                            <tr>
                                                <td><?php echo htmlspecialchars($ip['ip_address']); ?></td>
                                                <td><?php echo htmlspecialchars($ip['key']); ?></td>
                                                <td><?php echo number_format($ip['attempts']); ?></td>
                                                <td><?php echo formatDate($ip['last_attempt_at']); ?></td>
                                                <td><?php echo formatDate($ip['blocked_until']); ?></td>
                                                <td>
                                                    <form method="post" class="d-inline">
                                                        <input type="hidden" name="ip_key" value="<?php echo htmlspecialchars($ip['key'] . ':' . $ip['ip_address']); ?>">
                                                        <input type="hidden" name="unblock" value="1">
                                                        <button type="submit" class="btn btn-sm btn-outline-danger" onclick="return confirm('Bu IP adresinin engelini kaldırmak istediğinizden emin misiniz?')">
                                                            <i class="bi bi-unlock"></i> Engeli Kaldır
                                                        </button>
                                                    </form>
                                                </td>
                                            </tr>
                                        <?php endforeach; ?>
                                    </tbody>
                                </table>
                            </div>
                        <?php endif; ?>
                    </div>
                </div>
            </main>
        </div>
    </div>
    
    <!-- Temizleme Modal -->
    <div class="modal fade" id="cleanupModal" tabindex="-1" aria-labelledby="cleanupModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="cleanupModalLabel">Eski Kayıtları Temizle</h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                <form method="post">
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="days" class="form-label">Kaç günden eski kayıtlar temizlensin?</label>
                            <input type="number" class="form-control" id="days" name="days" value="30" min="1" max="365">
                            <div class="form-text">Bu işlem, belirtilen günden daha eski ve engellenmemiş rate limit kayıtlarını temizler.</div>
                        </div>
                        <input type="hidden" name="cleanup" value="1">
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                        <button type="submit" class="btn btn-primary">Temizle</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
    
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0-alpha1/dist/js/bootstrap.bundle.min.js"></script>
    <script src="../assets/js/script.js"></script>
</body>
</html>
