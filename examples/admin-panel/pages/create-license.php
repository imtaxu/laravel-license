<?php

/**
 * Lisans Yönetim Paneli - Lisans Oluşturma Sayfası
 */

// Dil yükle
$lang = loadLanguage();

// Veritabanı bağlantısı
$db = getDbConnection();

// Oluşturulan lisansı göster
if (isset($_GET['show_license']) && isset($_SESSION['new_license'])) {
    $license = $_SESSION['new_license'];
    ?>    
    <div class="container mt-4">
        <div class="card">
            <div class="card-header bg-success text-white">
                <h5 class="mb-0"><?php echo $lang['license_created']; ?></h5>
            </div>
            <div class="card-body">
                <div class="alert alert-success">
                    <strong><?php echo $lang['license_key']; ?>:</strong> 
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" value="<?php echo htmlspecialchars($license['license_key']); ?>" id="licenseKey" readonly>
                        <button class="btn btn-outline-secondary" type="button" onclick="copyToClipboard('licenseKey')">
                            <i class="bi bi-clipboard"></i> <?php echo $lang['copy']; ?>
                        </button>
                    </div>
                </div>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?php echo $lang['domain']; ?>:</strong> <?php echo htmlspecialchars($license['domain']); ?></p>
                        <p><strong><?php echo $lang['owner_email']; ?>:</strong> <?php echo htmlspecialchars($license['owner_email']); ?></p>
                        <p><strong><?php echo $lang['status']; ?>:</strong> <?php echo htmlspecialchars($license['status']); ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?php echo $lang['expires_at']; ?>:</strong> <?php echo htmlspecialchars($license['expires_at']); ?></p>
                        <p><strong><?php echo $lang['max_instances']; ?>:</strong> <?php echo htmlspecialchars($license['max_instances']); ?></p>
                        <p><strong><?php echo $lang['created_at']; ?>:</strong> <?php echo htmlspecialchars($license['created_at']); ?></p>
                    </div>
                </div>
                
                <hr>
                
                <div class="row">
                    <div class="col-md-6">
                        <p><strong><?php echo $lang['excluded_ips']; ?>:</strong> <?php echo $license['excluded_ips'] ?: $lang['none']; ?></p>
                    </div>
                    <div class="col-md-6">
                        <p><strong><?php echo $lang['features']; ?>:</strong> <?php echo $license['features'] ?: $lang['none']; ?></p>
                    </div>
                </div>
            </div>
            <div class="card-footer">
                <div class="d-flex justify-content-between">
                    <a href="index.php?page=create-license" class="btn btn-primary"><?php echo $lang['create_new_license']; ?></a>
                    <a href="index.php?page=licenses" class="btn btn-secondary"><?php echo $lang['view_all_licenses']; ?></a>
                </div>
            </div>
        </div>
    </div>
    
    <script>
    function copyToClipboard(elementId) {
        var copyText = document.getElementById(elementId);
        copyText.select();
        document.execCommand("copy");
        
        // Kullanıcıya geri bildirim ver
        var button = copyText.nextElementSibling;
        var originalText = button.innerHTML;
        button.innerHTML = '<i class="bi bi-check"></i> <?php echo $lang['copied']; ?>';
        button.classList.remove('btn-outline-secondary');
        button.classList.add('btn-success');
        
        setTimeout(function() {
            button.innerHTML = originalText;
            button.classList.remove('btn-success');
            button.classList.add('btn-outline-secondary');
        }, 2000);
    }
    </script>
    <?php
    unset($_SESSION['new_license']);
    exit;
}

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
        $errors[] = $lang['domain_required'];
    } elseif (!isValidDomain($domain)) {
        $errors[] = $lang['domain_invalid'];
    }

    if (empty($ownerEmail)) {
        $errors[] = $lang['email_required'];
    } elseif (!isValidEmail($ownerEmail)) {
        $errors[] = $lang['valid_email_required'];
    }

    if (empty($expiresAt)) {
        $errors[] = $lang['expires_at_required'];
    }

    // Excluded IPs kontrolü
    $excludedIpsArray = [];
    if (!empty($excludedIps)) {
        $ips = explode(',', $excludedIps);
        foreach ($ips as $ip) {
            $ip = trim($ip);
            if (!empty($ip) && !isValidIp($ip)) {
                $errors[] = $lang['invalid_ip'] . ' ' . $ip;
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

            // Lisans oluşturuldu, bilgileri gösterelim
            $licenseData = [
                'license_key' => $licenseKey,
                'domain' => $domain,
                'owner_email' => $ownerEmail,
                'status' => $status,
                'expires_at' => $expiresAt,
                'max_instances' => $maxInstances,
                'features' => $features,
                'excluded_ips' => !empty($excludedIpsArray) ? implode(',', $excludedIpsArray) : null,
                'created_at' => date('Y-m-d H:i:s')
            ];
            
            // Başarılı mesajını session'a kaydet
            $_SESSION['message'] = ['type' => 'success', 'text' => $lang['license_created']];
            
            // Lisans bilgilerini session'a kaydet
            $_SESSION['new_license'] = $licenseData;
            
            // Lisans bilgilerini gösterme sayfasına yönlendir
            header('Location: index.php?page=create-license&show_license=1');
            exit;
        } catch (PDOException $e) {
            $errors[] = $lang['license_creation_error'] . ' ' . $e->getMessage();
        }
    }
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- Lisans Oluşturma Formu -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo $lang['create_license_page']; ?></h6>
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
                    <label for="domain" class="form-label"><?php echo $lang['domain']; ?> <span class="text-danger">*</span></label>
                    <input type="text" class="form-control" id="domain" name="domain" required
                        value="<?php echo isset($domain) ? htmlspecialchars($domain) : ''; ?>"
                        placeholder="<?php echo $lang['domain_placeholder']; ?>">
                    <div class="form-text"><?php echo $lang['domain_help']; ?></div>
                </div>

                <div class="col-md-6 mb-3">
                    <label for="owner_email" class="form-label"><?php echo $lang['owner_email']; ?> <span class="text-danger">*</span></label>
                    <input type="email" class="form-control" id="owner_email" name="owner_email" required
                        value="<?php echo isset($ownerEmail) ? htmlspecialchars($ownerEmail) : ''; ?>"
                        placeholder="<?php echo $lang['owner_email_placeholder']; ?>">
                    <div class="form-text"><?php echo $lang['owner_email_help']; ?></div>
                </div>
            </div>

            <div class="row">
                <div class="col-md-4 mb-3">
                    <label for="expires_at" class="form-label"><?php echo $lang['expires_at']; ?> <span class="text-danger">*</span></label>
                    <input type="date" class="form-control" id="expires_at" name="expires_at" required
                        value="<?php echo isset($expiresAt) ? htmlspecialchars($expiresAt) : date('Y-m-d', strtotime('+' . DEFAULT_LICENSE_DURATION . ' days')); ?>">
                    <div class="form-text"><?php echo $lang['expires_at_help']; ?></div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="max_instances" class="form-label"><?php echo $lang['max_instances']; ?></label>
                    <input type="number" class="form-control" id="max_instances" name="max_instances" min="1"
                        value="<?php echo isset($maxInstances) ? (int)$maxInstances : 1; ?>">
                    <div class="form-text"><?php echo $lang['max_instances_help']; ?></div>
                </div>

                <div class="col-md-4 mb-3">
                    <label for="status" class="form-label"><?php echo $lang['status']; ?></label>
                    <select class="form-select" id="status" name="status">
                        <option value="active" <?php echo (isset($status) && $status == 'active') ? 'selected' : ''; ?>><?php echo $lang['active']; ?></option>
                        <option value="inactive" <?php echo (isset($status) && $status == 'inactive') ? 'selected' : ''; ?>><?php echo $lang['inactive']; ?></option>
                        <option value="suspended" <?php echo (isset($status) && $status == 'suspended') ? 'selected' : ''; ?>><?php echo $lang['suspended']; ?></option>
                    </select>
                    <div class="form-text"><?php echo $lang['status_help'] ?? 'Lisansın mevcut durumu.'; ?></div>
                </div>
            </div>

            <div class="mb-3">
                <label for="features" class="form-label"><?php echo $lang['features']; ?></label>
                <textarea class="form-control" id="features" name="features" rows="3"><?php echo isset($features) ? htmlspecialchars($features) : ''; ?></textarea>
                <div class="form-text"><?php echo $lang['features_help']; ?></div>
            </div>

            <div class="mb-3">
                <label for="excluded_ips" class="form-label"><?php echo $lang['excluded_ips']; ?></label>
                <textarea class="form-control" id="excluded_ips" name="excluded_ips" rows="2"><?php echo isset($excludedIps) ? htmlspecialchars($excludedIps) : ''; ?></textarea>
                <div class="form-text"><?php echo $lang['excluded_ips_help']; ?></div>
            </div>

            <div class="d-flex justify-content-between">
                <a href="index.php?page=licenses" class="btn btn-secondary"><?php echo $lang['cancel']; ?></a>
                <button type="submit" class="btn btn-primary"><?php echo $lang['create']; ?></button>
            </div>
        </form>
    </div>
</div>