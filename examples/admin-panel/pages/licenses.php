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
        
        showMessageAndRedirect('Lisans başarıyla silindi.', 'success', 'index.php?page=licenses');
    } catch (PDOException $e) {
        showError('Lisans silinirken bir hata oluştu: ' . $e->getMessage());
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
            
            showMessageAndRedirect('Lisans durumu başarıyla güncellendi.', 'success', 'index.php?page=licenses');
        } catch (PDOException $e) {
            showError('Lisans durumu güncellenirken bir hata oluştu: ' . $e->getMessage());
        }
    } else {
        showError('Geçersiz lisans durumu.');
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
    error_log("Lisans listesi hatası: " . $e->getMessage());
    $error = "Lisanslar alınırken bir hata oluştu.";
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- Arama ve filtreler -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lisans Arama</h6>
    </div>
    <div class="card-body">
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="licenses">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Lisans anahtarı, domain veya e-posta ara..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Ara
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php?page=create-license" class="btn btn-success">
                        <i class="bi bi-plus-circle"></i> Yeni Lisans
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Lisans Listesi -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Lisanslar</h6>
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
                            <th>Lisans Anahtarı</th>
                            <th>Domain</th>
                            <th>E-posta</th>
                            <th>Durum</th>
                            <th>Oluşturma Tarihi</th>
                            <th>Bitiş Tarihi</th>
                            <th>İşlemler</th>
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
                                            İşlemler
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewLicenseModal<?php echo $license['id']; ?>">Görüntüle</a></li>
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#editLicenseModal<?php echo $license['id']; ?>">Düzenle</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=active">Aktif Yap</a></li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=inactive">Pasif Yap</a></li>
                                            <li><a class="dropdown-item" href="index.php?page=licenses&action=status&id=<?php echo $license['id']; ?>&status=suspended">Askıya Al</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="#" data-bs-toggle="modal" data-bs-target="#deleteLicenseModal<?php echo $license['id']; ?>">Sil</a></li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Görüntüleme Modal -->
                                    <div class="modal fade" id="viewLicenseModal<?php echo $license['id']; ?>" tabindex="-1" aria-labelledby="viewLicenseModalLabel<?php echo $license['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewLicenseModalLabel<?php echo $license['id']; ?>">Lisans Detayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Lisans Anahtarı:</strong> <?php echo $license['license_key']; ?></p>
                                                            <p><strong>Domain:</strong> <?php echo $license['domain']; ?></p>
                                                            <p><strong>E-posta:</strong> <?php echo $license['owner_email']; ?></p>
                                                            <p><strong>Durum:</strong> <?php echo getLicenseStatusText($license['status']); ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Oluşturma Tarihi:</strong> <?php echo formatDate($license['created_at']); ?></p>
                                                            <p><strong>Son Güncelleme:</strong> <?php echo formatDate($license['updated_at']); ?></p>
                                                            <p><strong>Bitiş Tarihi:</strong> <?php echo formatDate($license['expires_at']); ?></p>
                                                            <p><strong>Maksimum Örnek:</strong> <?php echo $license['max_instances']; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <p><strong>Client IP:</strong> <?php echo $license['client_ip'] ?: 'Belirtilmemiş'; ?></p>
                                                            <p><strong>Client İmzası:</strong> <?php echo $license['client_signature'] ?: 'Belirtilmemiş'; ?></p>
                                                            <p><strong>Hariç Tutulan IP'ler:</strong> <?php echo $license['excluded_ips'] ?: 'Yok'; ?></p>
                                                            <p><strong>Özellikler:</strong> <?php echo $license['features'] ?: 'Belirtilmemiş'; ?></p>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>
                                    
                                    <!-- Silme Modal -->
                                    <div class="modal fade" id="deleteLicenseModal<?php echo $license['id']; ?>" tabindex="-1" aria-labelledby="deleteLicenseModalLabel<?php echo $license['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="deleteLicenseModalLabel<?php echo $license['id']; ?>">Lisans Silme Onayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <p>Bu lisansı silmek istediğinize emin misiniz?</p>
                                                    <p><strong>Lisans Anahtarı:</strong> <?php echo $license['license_key']; ?></p>
                                                    <p><strong>Domain:</strong> <?php echo $license['domain']; ?></p>
                                                    <p class="text-danger">Bu işlem geri alınamaz!</p>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">İptal</button>
                                                    <a href="index.php?page=licenses&action=delete&id=<?php echo $license['id']; ?>" class="btn btn-danger">Evet, Sil</a>
                                                </div>
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
                <nav aria-label="Sayfalama">
                    <ul class="pagination justify-content-center">
                        <li class="page-item <?php echo ($page <= 1) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=licenses&p=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="Önceki">
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
                            <a class="page-link" href="<?php echo ($page >= $totalPages) ? '#' : 'index.php?page=licenses&p=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="Sonraki">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted mb-3">Henüz lisans bulunmuyor veya arama kriterlerinize uygun lisans bulunamadı.</p>
                <a href="index.php?page=create-license" class="btn btn-primary">
                    <i class="bi bi-plus-circle"></i> Yeni Lisans Oluştur
                </a>
            </div>
        <?php endif; ?>
    </div>
</div>
