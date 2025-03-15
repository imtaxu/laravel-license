<?php
/**
 * Lisans Yönetim Paneli - Geçersiz İstekler Sayfası
 */

// Veritabanı bağlantısı
$db = getDbConnection();

// Geçersiz istek silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete' && isset($_GET['id'])) {
    $requestId = (int)$_GET['id'];
    
    try {
        $stmt = $db->prepare("DELETE FROM invalid_requests WHERE id = :id");
        $stmt->execute(['id' => $requestId]);
        
        showMessageAndRedirect('Geçersiz istek kaydı başarıyla silindi.', 'success', 'index.php?page=invalid-requests');
    } catch (PDOException $e) {
        showError('Geçersiz istek silinirken bir hata oluştu: ' . $e->getMessage());
    }
}

// Tüm geçersiz istekleri silme işlemi
if (isset($_GET['action']) && $_GET['action'] == 'delete-all') {
    try {
        $stmt = $db->prepare("DELETE FROM invalid_requests");
        $stmt->execute();
        
        showMessageAndRedirect('Tüm geçersiz istek kayıtları başarıyla silindi.', 'success', 'index.php?page=invalid-requests');
    } catch (PDOException $e) {
        showError('Geçersiz istekler silinirken bir hata oluştu: ' . $e->getMessage());
    }
}

// Geçersiz istekleri getir
try {
    // Sayfalama için parametreler
    $page = isset($_GET['p']) ? (int)$_GET['p'] : 1;
    $perPage = 15;
    $offset = ($page - 1) * $perPage;
    
    // Arama filtresi
    $search = isset($_GET['search']) ? $_GET['search'] : '';
    $searchWhere = '';
    $searchParams = [];
    
    if (!empty($search)) {
        $searchWhere = " WHERE license_key LIKE :search OR ip_address LIKE :search OR domain LIKE :search OR reason LIKE :search";
        $searchParams['search'] = "%$search%";
    }
    
    // Toplam kayıt sayısı
    $stmtCount = $db->prepare("SELECT COUNT(*) as total FROM invalid_requests" . $searchWhere);
    $stmtCount->execute($searchParams);
    $totalRecords = $stmtCount->fetch()['total'];
    $totalPages = ceil($totalRecords / $perPage);
    
    // Geçersiz istekleri getir
    $stmtRequests = $db->prepare("
        SELECT * FROM invalid_requests
        $searchWhere
        ORDER BY created_at DESC
        LIMIT :offset, :perPage
    ");
    
    $stmtRequests->bindValue(':offset', $offset, PDO::PARAM_INT);
    $stmtRequests->bindValue(':perPage', $perPage, PDO::PARAM_INT);
    
    foreach ($searchParams as $key => $value) {
        $stmtRequests->bindValue(":$key", $value);
    }
    
    $stmtRequests->execute();
    $requests = $stmtRequests->fetchAll();
    
} catch (PDOException $e) {
    error_log("Geçersiz istek listesi hatası: " . $e->getMessage());
    $error = "Geçersiz istekler alınırken bir hata oluştu.";
}
?>

<!-- Flash mesajı göster -->
<?php showFlashMessage(); ?>

<!-- Arama ve filtreler -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Geçersiz İstek Arama</h6>
    </div>
    <div class="card-body">
        <form method="get" action="index.php">
            <input type="hidden" name="page" value="invalid-requests">
            <div class="row">
                <div class="col-md-8">
                    <div class="input-group mb-3">
                        <input type="text" class="form-control" placeholder="Lisans anahtarı, IP adresi, domain veya neden ara..." name="search" value="<?php echo htmlspecialchars($search); ?>">
                        <button class="btn btn-primary" type="submit">
                            <i class="bi bi-search"></i> Ara
                        </button>
                    </div>
                </div>
                <div class="col-md-4 text-end">
                    <a href="index.php?page=invalid-requests&action=delete-all" class="btn btn-danger" onclick="return confirm('Tüm geçersiz istek kayıtlarını silmek istediğinize emin misiniz?');">
                        <i class="bi bi-trash"></i> Tümünü Temizle
                    </a>
                </div>
            </div>
        </form>
    </div>
</div>

<!-- Geçersiz İstek Listesi -->
<div class="card shadow mb-4">
    <div class="card-header py-3">
        <h6 class="m-0 font-weight-bold text-primary">Geçersiz İstekler</h6>
    </div>
    <div class="card-body">
        <?php if (isset($error)): ?>
            <div class="alert alert-danger"><?php echo $error; ?></div>
        <?php elseif (isset($requests) && count($requests) > 0): ?>
            <div class="table-responsive">
                <table class="table table-bordered table-hover">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Lisans Anahtarı</th>
                            <th>IP Adresi</th>
                            <th>Domain</th>
                            <th>Neden</th>
                            <th>Tarih</th>
                            <th>İşlemler</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($requests as $request): ?>
                            <tr>
                                <td><?php echo $request['id']; ?></td>
                                <td><?php echo substr($request['license_key'], 0, 12) . '...'; ?></td>
                                <td><?php echo $request['ip_address']; ?></td>
                                <td><?php echo $request['domain']; ?></td>
                                <td><?php echo $request['reason']; ?></td>
                                <td><?php echo formatDate($request['created_at']); ?></td>
                                <td>
                                    <div class="btn-group">
                                        <button type="button" class="btn btn-sm btn-primary dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false">
                                            İşlemler
                                        </button>
                                        <ul class="dropdown-menu">
                                            <li><a class="dropdown-item" href="#" data-bs-toggle="modal" data-bs-target="#viewRequestModal<?php echo $request['id']; ?>">Detaylar</a></li>
                                            <li><hr class="dropdown-divider"></li>
                                            <li><a class="dropdown-item text-danger" href="index.php?page=invalid-requests&action=delete&id=<?php echo $request['id']; ?>" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">Sil</a></li>
                                        </ul>
                                    </div>
                                    
                                    <!-- Görüntüleme Modal -->
                                    <div class="modal fade" id="viewRequestModal<?php echo $request['id']; ?>" tabindex="-1" aria-labelledby="viewRequestModalLabel<?php echo $request['id']; ?>" aria-hidden="true">
                                        <div class="modal-dialog modal-lg">
                                            <div class="modal-content">
                                                <div class="modal-header">
                                                    <h5 class="modal-title" id="viewRequestModalLabel<?php echo $request['id']; ?>">Geçersiz İstek Detayı</h5>
                                                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Kapat"></button>
                                                </div>
                                                <div class="modal-body">
                                                    <div class="row">
                                                        <div class="col-md-6">
                                                            <p><strong>Lisans Anahtarı:</strong> <?php echo $request['license_key']; ?></p>
                                                            <p><strong>IP Adresi:</strong> <?php echo $request['ip_address']; ?></p>
                                                            <p><strong>Domain:</strong> <?php echo $request['domain']; ?></p>
                                                        </div>
                                                        <div class="col-md-6">
                                                            <p><strong>Tarih:</strong> <?php echo formatDate($request['created_at']); ?></p>
                                                            <p><strong>Neden:</strong> <?php echo $request['reason']; ?></p>
                                                            <p><strong>User Agent:</strong> <?php echo $request['user_agent'] ?: 'Belirtilmemiş'; ?></p>
                                                        </div>
                                                    </div>
                                                    
                                                    <hr>
                                                    
                                                    <div class="row">
                                                        <div class="col-md-12">
                                                            <p><strong>İstek Verileri:</strong></p>
                                                            <pre class="bg-light p-3"><?php echo $request['request_data'] ?: 'Veri yok'; ?></pre>
                                                        </div>
                                                    </div>
                                                </div>
                                                <div class="modal-footer">
                                                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Kapat</button>
                                                    <a href="index.php?page=invalid-requests&action=delete&id=<?php echo $request['id']; ?>" class="btn btn-danger" onclick="return confirm('Bu kaydı silmek istediğinize emin misiniz?');">Sil</a>
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
                            <a class="page-link" href="<?php echo ($page <= 1) ? '#' : 'index.php?page=invalid-requests&p=' . ($page - 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="Önceki">
                                <span aria-hidden="true">&laquo;</span>
                            </a>
                        </li>
                        
                        <?php for ($i = 1; $i <= $totalPages; $i++): ?>
                            <li class="page-item <?php echo ($page == $i) ? 'active' : ''; ?>">
                                <a class="page-link" href="index.php?page=invalid-requests&p=<?php echo $i; ?><?php echo !empty($search) ? '&search=' . urlencode($search) : ''; ?>">
                                    <?php echo $i; ?>
                                </a>
                            </li>
                        <?php endfor; ?>
                        
                        <li class="page-item <?php echo ($page >= $totalPages) ? 'disabled' : ''; ?>">
                            <a class="page-link" href="<?php echo ($page >= $totalPages) ? '#' : 'index.php?page=invalid-requests&p=' . ($page + 1) . (!empty($search) ? '&search=' . urlencode($search) : ''); ?>" aria-label="Sonraki">
                                <span aria-hidden="true">&raquo;</span>
                            </a>
                        </li>
                    </ul>
                </nav>
            <?php endif; ?>
            
        <?php else: ?>
            <div class="text-center py-5">
                <p class="text-muted mb-3">Henüz geçersiz istek kaydı bulunmuyor veya arama kriterlerinize uygun kayıt bulunamadı.</p>
                <i class="bi bi-shield-check text-success" style="font-size: 3rem;"></i>
                <p class="mt-3">Tüm lisanslar sorunsuz çalışıyor gibi görünüyor!</p>
            </div>
        <?php endif; ?>
    </div>
</div>
