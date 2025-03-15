<?php
/**
 * Lisans Yönetim Paneli - Lisans Oluşturma Sayfası
 */

// Veritabanı bağlantısı
$db = getDbConnection();

// Form gönderildi mi?
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Form verilerini al
    $domain = trim($_POST['domain'] ?? '');
    $ownerEmail = trim($_POST['owner_email'] ?? '');
    $expiresAt = trim($_POST['expires_at'] ?? '');
    $maxInstances = (int)($_POST['max_instances'] ?? 1);
    $status = $_POST['status'] ?? 'active';
    $features = trim($_POST['features'] ?? '');
    $excludedIps = trim($_POST['excluded_ips'] ?? '');
    
    // Hata kontrolü
    $errors = [];
    
    if (empty($domain)) {
        $errors[] = 'Domain alanı zorunludur.';
    } elseif (!isValidDomain($domain)) {
        $errors[] = 'Geçerli bir domain giriniz.';
    }
    
    if (empty($ownerEmail)) {
        $errors[] = 'E-posta alanı zorunludur.';
    } elseif (!isValidEmail($ownerEmail)) {
        $errors[] = 'Geçerli bir e-posta adresi giriniz.';
    }
    
    if (empty($expiresAt)) {
        $errors[] = 'Bitiş tarihi zorunludur.';
    }
    
    // Excluded IPs kontrolü
    $excludedIpsArray = [];
    if (!empty($excludedIps)) {
        $ips = explode(',', $excludedIps);
        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (!empty($ip) && !isValidIp($ip)) {
                $errors[] = 'Geçersiz IP adresi: ' . $ip;
            } else {
                $excludedIpsArray[] = $ip;
            }
        }
    }
    
    // Hata yoksa lisans oluştur
    if (empty($errors)) {
        try {
            // Lisans anahtarı oluştur
            $licenseKey = generateLicenseKey();
            
            // Veritabanına kaydet
            $stmt = $db->prepare("
                INSERT INTO licenses (
                    license_key, domain, owner_email, status, expires_at, 
                    max_instances, features, excluded_ips, created_at, updated_at
                ) VALUES (
                    :license_key, :domain, :owner_email, :status, :expires_at,
                    :max_instances, :features, :excluded_ips, NOW(), NOW()
                )
            ");
            
            $stmt->execute([
                'license_key' => $licenseKey,
                'domain' => $domain,
                'owner_email' => $ownerEmail,
                'status' => $status,
                'expires_at' => $expiresAt,
                'max_instances' => $maxInstances,
                'features' => $features,
                'excluded_ips' => !empty($excludedIpsArray) ? implode(',', $excludedIpsArray) : null
            ]);
            
            // Başarılı mesajı göster ve lisans listesine yönlendir
            showMessageAndRedirect('Lisans başarıyla oluşturuldu.', 'success', 'index.php?page=licenses');
            exit;
        } catch (PDOException $e) {
            $errors[] = 'Lisans oluşturulurken bir hata oluştu: ' . $e->getMessage();
        }
    }
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- Lisans Oluşturma Formu -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Yeni Lisans Oluştur</h6>
    </div>
    <div class="card-body">
        <?php if (isset($errors) && !empty($errors)): ?>
            <div class="alert alert-danger">
                <ul class="mb-0">
                    <?php foreach ($errors as $error): ?>
                        <li><?php echo $error; ?></li>
                    <?php endforeach; ?>
                </ul>
            </div>
        <?php endif; ?>
        
        <form method="post" action="index.php?page=create-license">
            <div class="row">
                <div class="col-md-6 mb-3">
                    <label for="domain" class="form-label">Domain <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="domain" name="domain" required 
                           value="<?php echo isset($domain) ? htmlspecialchars($domain) : ''; ?>"
                           placeholder="örn: example.com">
                    <div class="form-text">Lisansın geçerli olacağı domain adı.</div>
                </div>
                
                <div class="col-md-6 mb-3">
                    <label for="owner_email" class="form-label">E-posta <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="owner_email" name="owner_email" required
                           value="<?php echo isset($ownerEmail) ? htmlspecialchars($ownerEmail) : ''; ?>"
                           placeholder="örn: info@example.com">
                    <div class="form-text">Lisans sahibinin e-posta adresi.</div>
                </div>
            </div>
            
            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="expires_at" class="form-label">Bitiş Tarihi <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="expires_at" name="expires_at" required
                           value="<?php echo isset($expiresAt) ? htmlspecialchars($expiresAt) : date('Y-m-d', strtotime('+' . DEFAULT_LICENSE_DURATION . ' days')); ?>">
                    <div class="form-text">Lisansın sona ereceği tarih.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="max_instances" class="form-label">Maksimum Örnek Sayısı</label>
                    <input type="number" class="form-control" id="max_instances" name="max_instances" min="1" 
                           value="<?php echo isset($maxInstances) ? (int)$maxInstances : 1; ?>">
                    <div class="form-text">Bu lisansın aynı anda kaç farklı yerde kullanılabileceği.</div>
                </div>
                
                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label">Durum</label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : ''; ?>>Aktif</option>
                        <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>>Pasif</option>
                        <option value="suspended" <?php echo (isset($status) && $status == 'suspended') ? 'selected' : ''; ?>>Askıya Alınmış</option>
                    </select>
                    <div class="form-text">Lisansın mevcut durumu.</div>
                </div>
            </div>
            
            <div class="mb-3">
                <label for="features" class="form-label">Özellikler</label>
                <textarea class="form-control" id="features" name="features" rows="3"><?php echo isset($features) ? htmlspecialchars($features) : ''; ?></textarea>
                <div class="form-text">Lisansa özel özellikler (JSON formatında).</div>
            </div>
            
            <div class="mb-3">
                <label for="excluded_ips" class="form-label">Hariç Tutulan IP'ler</label>
                <textarea class="form-control" id="excluded_ips" name="excluded_ips" rows="2"><?php echo isset($excludedIps) ? htmlspecialchars($excludedIps) : ''; ?></textarea>
                <div class="form-text">Lisans kontrolünden muaf tutulacak IP adresleri (virgülle ayırın).</div>
            </div>
            
            <div class="d-flex justify-content-between">
                <a href="index.php?page=licenses" class="btn btn-secondary">İptal</a>
                <button type="submit" class="btn btn-primary">Lisans Oluştur</button>
            </div>
        </form>
    </div>
</div>
