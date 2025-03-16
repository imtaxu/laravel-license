<?php

/**
 * Lisans Yönetim Paneli - Lisanslar Sayfası
 */

// Veritabanı bağlantısı
$db = getDbConnection();

// Lisans silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $licenseId = (int)$_GET['id'];

    try {
        $stmt = $db->prepare("DELETE FROM licenses WHERE id = :id");
        $stmt->execute(['id' => $licenseId]);

        showMessageAndRedirect(__('license_deleted'), 'success', 'index.php?page=licenses');
    } catch (PDOException $e) {
        showError(__('license_delete_error') . ': ' . $e->getMessage());
    }
}

// Lisans durumunu güncelleme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'status' && isset($_GET['id']) && isset($_GET['status'])) {
    $licenseId = (int)$_GET['id'];
    $newStatus = $_GET['status'];

    // Geçerli durumları kontrol et
    if (in_array($newStatus, ['active', 'inactive', 'suspended'])) {
        try {
            $stmt = $db->prepare("UPDATE licenses SET status = :status, updated_at = NOW() WHERE id = :id");
            $stmt->execute([
                'status' => $newStatus,
                'id' => $licenseId
            ]);

            showMessageAndRedirect(__('license_status_updated'), 'success', 'index.php?page=licenses');
        } catch (PDOException $e) {
            showError(__('license_status_update_error') . ': ' . $e->getMessage());
        }
    } else {
        showError(__('invalid_license_status'));
    }
}

// Lisans güncelleme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'update' && isset($_POST['license_id'])) {
    $licenseId = (int)$_POST['license_id'];
    
    // Form verilerini al
    $domain = trim($_POST['domain']);
    $ownerEmail = trim($_POST['owner_email']);
    $status = $_POST['status'];
    $expiresAt = $_POST['expires_at'];
    $maxInstances = (int)$_POST['max_instances'];
    $features = trim($_POST['features']);
    $excludedIps = trim($_POST['excluded_ips']);
    
    // Geçerli durumları kontrol et
    if (in_array($status, ['active', 'inactive', 'suspended'])) {
        try {
            $stmt = $db->prepare("UPDATE licenses SET 
                domain = :domain, 
                owner_email = :owner_email, 
                status = :status, 
                expires_at = :expires_at, 
                max_instances = :max_instances, 
                features = :features, 
                excluded_ips = :excluded_ips, 
                updated_at = NOW() 
                WHERE id = :id");
                
            $stmt->execute([
                'domain' => $domain,
                'owner_email' => $ownerEmail,
                'status' => $status,
                'expires_at' => $expiresAt,
                'max_instances' => $maxInstances,
                'features' => $features,
                'excluded_ips' => $excludedIps,
                'id' => $licenseId
            ]);

            showMessageAndRedirect(__('license_updated'), 'success', 'index.php?page=licenses');
        } catch (PDOException $e) {
            showError(__('license_update_error') . ': ' . $e->getMessage());
        }
    } else {
        showError(__('invalid_license_status'));
    }
}

// Lisansları getir
try {
    // Sayfalama için parametreler
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $perPage = 10;
    $offset = ($page - 1) * $perPage;

    // Arama filtresi
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchWhere = '';
    $searchParams = [];

    if (!empty($search)) {
        $searchWhere = " WHERE license_key LIKE :search OR domain LIKE :search OR owner_email LIKE :search";
        $searchParams['search'] = "%$search%";
    }

    // Toplam kayıt sayısı
    $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM licenses" . $searchWhere);
    $stmtCount->execute($searchParams);
    $totalRecords = $stmtCount->fetch()['total'];
    $totalPages = ceil($totalRecords / $perPage);

    // Lisansları getir
    $stmtLicenses = $db->prepare("
        SELECT * FROM licenses
        $searchWhere
        ORDER BY created_at DESC
        LIMIT :offset, :perPage
    ");

    $stmtLicenses->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtLicenses->bindValue(':perPage', $perPage, PDO::PARAM_INT);

    foreach ($searchParams as $key => $value) {
        $stmtLicenses->bindValue(":$key", $value);
    }

    $stmtLicenses->execute();
    $licenses = $stmtLicenses->fetchAll();
} catch (PDOException $e) {
    error_log(__('license_list_error') . ": " . $e->getMessage());
    $error = __('license_fetch_error');
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- Arama ve filtreler -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo __('search_license'); ?></h6>
    </div>
    <div class="card-body">
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="licenses">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="<?php echo __('search_license_placeholder'); ?>" name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> <?php echo __('search'); ?>
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php?page=create-license" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> <?php echo __('new_license'); ?>
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lisans Listesi -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary"><?php echo __('licenses'); ?></h6>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($licenses) && count($licenses) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th><?php echo __('license_key'); ?></th>
                            <th><?php echo __('domain'); ?></th>
                            <th><?php echo __('email'); ?></th>
                            <th><?php echo __('status'); ?></th>
                            <th><?php echo __('created_date'); ?></th>
                            <th><?php echo __('expiry_date'); ?></th>
                            <th><?php echo __('actions'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($licenses as $license): ?>
                            <tr>
                                <td><?php echo $license['id']; ?></td>
                                <td><?php echo substr($license['license_key'], 0, 12) . '...'; ?></td>
                                <td><?php echo $license['domain']; ?></td>
                                <td><?php echo $license['owner_email']; ?></td>
                                <td>
                                    <span class="badge <?php echo getLicenseStatusBadgeClass($license['status']); ?>">
                                        <?php echo getLicenseStatusText($license['status']); ?>
                                    </span>
                                </td>
                                <td><?php echo formatDate($license['created_at']); ?></td>
                                <td><?php echo formatDate($license['expires_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            <?php echo __('actions'); ?>
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewLicenseModal<?php echo $license['id']; ?>"><?php echo __('view'); ?></a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editLicenseModal<?php echo $license['id']; ?>"><?php echo __('edit'); ?></a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=active"><?php echo __('make_active'); ?></a></li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=inactive"><?php echo __('make_inactive'); ?></a></li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=suspended"><?php echo __('make_suspended'); ?></a></li>
                                            <li>
                                                <hr class="dropdown-divider">
                                            </li>
                                            <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteLicenseModal<?php echo $license['id']; ?>"><?php echo __('delete'); ?></a></li>
                                        </ul>
                                    </div>

                                    <!-- Görüntüleme Modal -->
                                    <div class="modal fade" id="viewLicenseModal<?php echo $license['id']; ?>" tabindex="-1" aria-labelledby="viewLicenseModalLabel<?php echo $license['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewLicenseModalLabel<?php echo $license['id']; ?>"><?php echo __('license_details'); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __('close'); ?>"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-12">
                                                            <p><strong><?php echo __('license_key'); ?>:</strong> <?php echo $license['license_key']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong><?php echo __('domain'); ?>:</strong> <?php echo $license['domain']; ?></p>
                                                            <p><strong><?php echo __('owner_email'); ?>:</strong> <?php echo $license['owner_email']; ?></p>
                                                            <p><strong><?php echo __('status'); ?>:</strong> <?php echo getLicenseStatusText($license['status']); ?></p>
                                                            <p><strong><?php echo __('max_instances'); ?>:</strong> <?php echo $license['max_instances']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong><?php echo __('created_at'); ?>:</strong> <?php echo formatDate($license['created_at']); ?></p>
                                                            <p><strong><?php echo __('updated_at'); ?>:</strong> <?php echo formatDate($license['updated_at']); ?></p>
                                                            <p><strong><?php echo __('expires_at'); ?>:</strong> <?php echo formatDate($license['expires_at']); ?></p>
                                                        </div>
                                                    </div>

                                                    <hr>

                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong><?php echo __('excluded_ips'); ?>:</strong> <?php echo $license['excluded_ips'] ?: __('none'); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong><?php echo __('features'); ?>:</strong> <?php echo $license['features'] ?: __('none'); ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('close'); ?></button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <!-- Silme Modal -->
                                    <div class="modal fade" id="deleteLicenseModal<?php echo $license['id']; ?>" tabindex="-1" aria-labelledby="deleteLicenseModalLabel<?php echo $license['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteLicenseModalLabel<?php echo $license['id']; ?>"><?php echo __('confirm_license_delete'); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __('close'); ?>"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p><?php echo __('confirm_license_delete_message'); ?></p>
                                                    <p><strong><?php echo __('license_key'); ?>:</strong> <?php echo $license['license_key']; ?></p>
                                                    <p><strong><?php echo __('domain'); ?>:</strong> <?php echo $license['domain']; ?></p>
                                                    <p class="text-danger"><?php echo __('action_irreversible'); ?></p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                                                    <a href="index.php?page=licenses&action=delete&id=<?php echo $license['id']; ?>" class="btn btn-danger"><?php echo __('yes_delete'); ?></a>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Düzenleme Modal -->
                                    <div class="modal fade" id="editLicenseModal<?php echo $license['id']; ?>" tabindex="-1" aria-labelledby="editLicenseModalLabel<?php echo $license['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="editLicenseModalLabel<?php echo $license['id']; ?>"><?php echo __('edit_license'); ?></h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="<?php echo __('close'); ?>"></button>
                                                </div>
                                                <form action="index.php?page=licenses&action=update" method="post">
                                                    <input type="hidden" name="license_id" value="<?php echo $license['id']; ?>">
                                                    <div class="modal-body">
                                                        <div class="mb-3">
                                                            <label for="license_key<?php echo $license['id']; ?>" class="form-label"><?php echo __('license_key'); ?></label>
                                                            <input type="text" class="form-control" id="license_key<?php echo $license['id']; ?>" name="license_key" value="<?php echo htmlspecialchars($license['license_key']); ?>" readonly disabled="true">
                                                        </div>
                                                        <div class="row">
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="domain<?php echo $license['id']; ?>" class="form-label"><?php echo __('domain'); ?></label>
                                                                    <input type="text" class="form-control" id="domain<?php echo $license['id']; ?>" name="domain" value="<?php echo htmlspecialchars($license['domain']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="owner_email<?php echo $license['id']; ?>" class="form-label"><?php echo __('owner_email'); ?></label>
                                                                    <input type="email" class="form-control" id="owner_email<?php echo $license['id']; ?>" name="owner_email" value="<?php echo htmlspecialchars($license['owner_email']); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="status<?php echo $license['id']; ?>" class="form-label"><?php echo __('status'); ?></label>
                                                                    <select class="form-select" id="status<?php echo $license['id']; ?>" name="status" required>
                                                                        <option value="active" <?php echo $license['status'] == 'active' ? 'selected' : ''; ?>><?php echo __('active'); ?></option>
                                                                        <option value="inactive" <?php echo $license['status'] == 'inactive' ? 'selected' : ''; ?>><?php echo __('inactive'); ?></option>
                                                                        <option value="suspended" <?php echo $license['status'] == 'suspended' ? 'selected' : ''; ?>><?php echo __('suspended'); ?></option>
                                                                    </select>
                                                                </div>
                                                            </div>
                                                            <div class="col-md-6">
                                                                <div class="mb-3">
                                                                    <label for="expires_at<?php echo $license['id']; ?>" class="form-label"><?php echo __('expires_at'); ?></label>
                                                                    <input type="date" class="form-control" id="expires_at<?php echo $license['id']; ?>" name="expires_at" value="<?php echo date('Y-m-d', strtotime($license['expires_at'])); ?>" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="max_instances<?php echo $license['id']; ?>" class="form-label"><?php echo __('max_instances'); ?></label>
                                                                    <input type="number" class="form-control" id="max_instances<?php echo $license['id']; ?>" name="max_instances" value="<?php echo (int)$license['max_instances']; ?>" min="1" required>
                                                                </div>
                                                                <div class="mb-3">
                                                                    <label for="excluded_ips<?php echo $license['id']; ?>" class="form-label"><?php echo __('excluded_ips'); ?></label>
                                                                    <input type="text" class="form-control" id="excluded_ips<?php echo $license['id']; ?>" name="excluded_ips" value="<?php echo htmlspecialchars($license['excluded_ips']); ?>" placeholder="<?php echo __('excluded_ips_help'); ?>">
                                                                </div>
                                                            </div>
                                                        </div>
                                                        <div class="mb-3">
                                                            <label for="features<?php echo $license['id']; ?>" class="form-label"><?php echo __('features'); ?></label>
                                                            <textarea class="form-control" id="features<?php echo $license['id']; ?>" name="features" rows="3" placeholder="<?php echo __('features_help'); ?>"><?php echo htmlspecialchars($license['features']); ?></textarea>
                                                        </div>
                                                    </div>
                                                    <div class="modal-footer">
                                                        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                                                        <button type="submit" class="btn btn-primary"><?php echo __('save_changes'); ?></button>
                                                    </div>
                                                </form>
                                            </div>
                                        </div>
                                    </div>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            </div>

            <!-- Sayfalama -->
            <?php if ($totalPages > 1): ?>
                <nav aria-label="<?php echo __('pagination'); ?>">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=licenses&p=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="<?php echo __('previous'); ?>">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>

                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=licenses&p=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>

                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($page >= $totalPages) ? '#' : 'index.php?page=licenses&p=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="<?php echo __('next'); ?>">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>

        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted mb-3"><?php echo __('no_licenses_found'); ?></p>
                <a href="index.php?page=create-license" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> <?php echo __('create_new_license'); ?>
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>