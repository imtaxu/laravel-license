<?php
/**
 * Lisans Yönetim Paneli - Kontrol Paneli Sayfası
 */

// Dil dosyasını yükle
require_once 'language.php';

// Veritabanı bağlantısı
$db = getDbConnection();

// İstatistikleri al
try {
    // Toplam lisans sayısı
    $stmtTotal = $db->query("SELECT COUNT(*) as total FROM licenses");
    $totalLicenses = $stmtTotal->fetch()['total'];
    
    // Aktif lisans sayısı
    $stmtActive = $db->query("SELECT COUNT(*) as active FROM licenses WHERE status = 'active'");
    $activeLicenses = $stmtActive->fetch()['active'];
    
    // Süresi yakında dolacak lisanslar
    $stmtExpiring = $db->query("SELECT COUNT(*) as expiring FROM licenses WHERE status = 'active' AND expires_at BETWEEN NOW() AND DATE_ADD(NOW(), INTERVAL 30 DAY)");
    $expiringLicenses = $stmtExpiring->fetch()['expiring'];
    
    // Geçersiz istek sayısı (son 7 gün)
    $stmtInvalid = $db->query("SELECT COUNT(*) as invalid FROM invalid_requests WHERE created_at >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
    $invalidRequests = $stmtInvalid->fetch()['invalid'];
    
    // Son 5 lisans
    $stmtRecent = $db->query("SELECT * FROM licenses ORDER BY created_at DESC LIMIT 5");
    $recentLicenses = $stmtRecent->fetchAll();
    
    // Son 5 geçersiz istek
    $stmtRecentInvalid = $db->query("SELECT * FROM invalid_requests ORDER BY created_at DESC LIMIT 5");
    $recentInvalidRequests = $stmtRecentInvalid->fetchAll();
    
} catch (PDOException $e) {
    error_log("Dashboard istatistik hatası: " . $e->getMessage());
    $error = __('dashboard_stats_error');
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- İstatistik kartları -->
<div class="row mb-4">
    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-primary shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-primary text-uppercase mb-1"><?php echo __('total_licenses'); ?></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($totalLicenses) ? $totalLicenses : 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-key-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <div class="col-xl-3 col-md-6 mb-4">
        <div class="card border-left-success shadow h-100 py-2">
            <div class="card-body">
                <div class="row no-gutters align-items-center">
                    <div class="col mr-2">
                        <div class="text-xs font-weight-bold text-success text-uppercase mb-1"><?php echo __('active_licenses'); ?></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($activeLicenses) ? $activeLicenses : 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-check-circle-fill fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-warning text-uppercase mb-1"><?php echo __('expired_licenses'); ?></div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($expiringLicenses) ? $expiringLicenses : 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-clock-fill fa-2x text-gray-300"></i>
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
                        <div class="text-xs font-weight-bold text-danger text-uppercase mb-1"><?php echo __('invalid_requests_count'); ?> (7 Gün)</div>
                        <div class="h5 mb-0 font-weight-bold text-gray-800"><?php echo isset($invalidRequests) ? $invalidRequests : 0; ?></div>
                    </div>
                    <div class="col-auto">
                        <i class="bi bi-exclamation-triangle-fill fa-2x text-gray-300"></i>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- İçerik Satırı -->
<div class="row">
    <!-- Son Lisanslar -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo __('recent_licenses'); ?></h6>
                <a href="index.php?page=licenses" class="btn btn-sm btn-primary"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-body">
                <?php if (isset($recentLicenses) && count($recentLicenses) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo __('license_key'); ?></th>
                                    <th><?php echo __('domain'); ?></th>
                                    <th><?php echo __('status'); ?></th>
                                    <th><?php echo __('expiry_date'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentLicenses as $license): ?>
                                    <tr>
                                        <td><?php echo substr($license['license_key'], 0, 8) . '...'; ?></td>
                                        <td><?php echo $license['domain']; ?></td>
                                        <td>
                                            <span class="badge <?php echo getLicenseStatusBadgeClass($license['status']); ?>">
                                                <?php echo getLicenseStatusText($license['status']); ?>
                                            </span>
                                        </td>
                                        <td><?php echo formatDate($license['expires_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted"><?php echo __('no_licenses_found'); ?></p>
                        <a href="index.php?page=create-license" class="btn btn-primary"><?php echo __('create_license'); ?></a>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Son Geçersiz İstekler -->
    <div class="col-lg-6 mb-4">
        <div class="card shadow mb-4">
            <div class="card-header py-3 d-flex flex-row align-items-center justify-content-between">
                <h6 class="m-0 font-weight-bold text-primary"><?php echo __('recent_invalid_requests'); ?></h6>
                <a href="index.php?page=invalid-requests" class="btn btn-sm btn-primary"><?php echo __('view_all'); ?></a>
            </div>
            <div class="card-body">
                <?php if (isset($recentInvalidRequests) && count($recentInvalidRequests) > 0): ?>
                    <div class="table-responsive">
                        <table class="table table-bordered table-hover">
                            <thead>
                                <tr>
                                    <th><?php echo __('license_key'); ?></th>
                                    <th><?php echo __('ip_address'); ?></th>
                                    <th><?php echo __('reason'); ?></th>
                                    <th><?php echo __('request_date'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($recentInvalidRequests as $request): ?>
                                    <tr>
                                        <td><?php echo substr($request['license_key'], 0, 8) . '...'; ?></td>
                                        <td><?php echo $request['ip_address']; ?></td>
                                        <td><?php echo $request['reason']; ?></td>
                                        <td><?php echo formatDate($request['created_at']); ?></td>
                                    </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                <?php else: ?>
                    <div class="text-center py-3">
                        <p class="text-muted"><?php echo __('no_invalid_requests'); ?></p>
                    </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
</div>
