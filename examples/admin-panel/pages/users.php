<?php
/**
 * Lisans Yönetim Paneli - Kullanıcı Yönetimi Sayfası
 */
require_once '../auth.php';
require_once '../functions.php';
require_once '../config.php';
require_once '../language.php';

// Oturum kontrolü
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

// Dil dosyasını yükle
loadLanguage();

// Auth sınıfını başlat
$auth = new Auth();

// Sadece yöneticilerin erişimine izin ver
if (!$auth->isAdmin()) {
    header('Location: index.php');
    exit;
}

$pageTitle = __('user_management');
$message = '';
$messageType = '';

// Form işlemleri
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    // Kullanıcı ekleme
    if (isset($_POST['action']) && $_POST['action'] === 'add') {
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        
        if (empty($name) || empty($email) || empty($password)) {
            $message = __('all_fields_required');
            $messageType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = __('valid_email_required');
            $messageType = 'danger';
        } elseif (strlen($password) < 8) {
            $message = __('password_min_length');
            $messageType = 'danger';
        } else {
            $userData = [
                'name' => $name,
                'email' => $email,
                'password' => $password,
                'role' => $role
            ];
            
            if ($auth->addUser($userData)) {
                $message = __('user_added');
                $messageType = 'success';
            } else {
                $message = __('user_add_error');
                $messageType = 'danger';
            }
        }
    }
    
    // Kullanıcı güncelleme
    if (isset($_POST['action']) && $_POST['action'] === 'edit') {
        $userId = (int)$_POST['user_id'];
        $name = trim($_POST['name']);
        $email = trim($_POST['email']);
        $password = trim($_POST['password']);
        $role = trim($_POST['role']);
        
        if (empty($name) || empty($email)) {
            $message = __('name_email_required');
            $messageType = 'danger';
        } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $message = __('valid_email_required');
            $messageType = 'danger';
        } else {
            $userData = [
                'name' => $name,
                'email' => $email,
                'role' => $role
            ];
            
            // Şifre değiştirilecek mi?
            if (!empty($password)) {
                if (strlen($password) < 8) {
                    $message = __('password_min_length');
                    $messageType = 'danger';
                } else {
                    $userData['password'] = $password;
                }
            }
            
            if (empty($message) && $auth->updateUser($userId, $userData)) {
                $message = __('user_updated');
                $messageType = 'success';
            } elseif (empty($message)) {
                $message = __('user_update_error');
                $messageType = 'danger';
            }
        }
    }
    
    // Kullanıcı silme
    if (isset($_POST['action']) && $_POST['action'] === 'delete') {
        $userId = (int)$_POST['user_id'];
        
        if ($auth->deleteUser($userId)) {
            $message = __('user_deleted');
            $messageType = 'success';
        } else {
            $message = __('user_delete_error');
            $messageType = 'danger';
        }
    }
}

// Kullanıcı listesini getir
$users = $auth->getAllUsers();

// Header'ı dahil et
include 'includes/header.php';
?>

<div class="container-fluid">
    <div class="row">
        <?php include 'includes/sidebar.php'; ?>
        
        <main class="col-md-9 ms-sm-auto col-lg-10 px-md-4">
            <div class="d-flex justify-content-between flex-wrap flex-md-nowrap align-items-center pt-3 pb-2 mb-3 border-bottom">
                <h1 class="h2"><?php echo __('user_management'); ?></h1>
                <div class="btn-toolbar mb-2 mb-md-0">
                    <button type="button" class="btn btn-sm btn-primary" data-bs-toggle="modal" data-bs-target="#addUserModal">
                        <i class="bi bi-plus"></i> <?php echo __('add_user'); ?>
                    </button>
                </div>
            </div>
            
            <?php if (!empty($message)): ?>
                <div class="alert alert-<?php echo $messageType; ?> alert-dismissible fade show" role="alert">
                    <?php echo $message; ?>
                    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                </div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-header">
                    <h5 class="card-title mb-0"><?php echo __('users'); ?></h5>
                </div>
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table table-striped table-hover">
                            <thead>
                                <tr>
                                    <th>#</th>
                                    <th><?php echo __('name'); ?></th>
                                    <th><?php echo __('email'); ?></th>
                                    <th><?php echo __('user_role'); ?></th>
                                    <th><?php echo __('last_login'); ?></th>
                                    <th><?php echo __('actions'); ?></th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if ($users && count($users) > 0): ?>
                                    <?php foreach ($users as $index => $user): ?>
                                        <tr>
                                            <td><?php echo $index + 1; ?></td>
                                            <td><?php echo htmlspecialchars($user['name']); ?></td>
                                            <td><?php echo htmlspecialchars($user['email']); ?></td>
                                            <td>
                                                <span class="badge bg-<?php echo $user['role'] === 'admin' ? 'primary' : 'secondary'; ?>">
                                                    <?php echo $user['role'] === 'admin' ? __('admin') : __('user'); ?>
                                                </span>
                                            </td>
                                            <td>
                                                <?php echo $user['last_login'] ? date('d.m.Y H:i', strtotime($user['last_login'])) : __('no_information'); ?>
                                            </td>
                                            <td>
                                                <button type="button" class="btn btn-sm btn-primary edit-user" 
                                                        data-bs-toggle="modal" 
                                                        data-bs-target="#editUserModal"
                                                        data-id="<?php echo $user['id']; ?>"
                                                        data-name="<?php echo htmlspecialchars($user['name']); ?>"
                                                        data-email="<?php echo htmlspecialchars($user['email']); ?>"
                                                        data-role="<?php echo $user['role']; ?>">
                                                    <i class="bi bi-pencil"></i> <?php echo __('edit'); ?>
                                                </button>
                                                
                                                <?php if ($user['id'] != $_SESSION['user_id']): ?>
                                                    <button type="button" class="btn btn-sm btn-danger delete-user" 
                                                            data-bs-toggle="modal" 
                                                            data-bs-target="#deleteUserModal"
                                                            data-id="<?php echo $user['id']; ?>"
                                                            data-name="<?php echo htmlspecialchars($user['name']); ?>">
                                                        <i class="bi bi-trash"></i> <?php echo __('delete'); ?>
                                                    </button>
                                                <?php endif; ?>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="6" class="text-center"><?php echo __('no_users_found'); ?></td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </main>
    </div>
</div>

<!-- Kullanıcı Ekleme Modal -->
<div class="modal fade" id="addUserModal" tabindex="-1" aria-labelledby="addUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="add">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="addUserModalLabel"><?php echo __('add_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="name" class="form-label"><?php echo __('full_name'); ?></label>
                        <input type="text" class="form-control" id="name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="email" class="form-label"><?php echo __('email_address'); ?></label>
                        <input type="email" class="form-control" id="email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="password" class="form-label"><?php echo __('password'); ?></label>
                        <input type="password" class="form-control" id="password" name="password" required>
                        <small class="text-muted"><?php echo __('password_min_length_info'); ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="role" class="form-label"><?php echo __('user_role'); ?></label>
                        <select class="form-select" id="role" name="role">
                            <option value="user"><?php echo __('user'); ?></option>
                            <option value="admin"><?php echo __('admin'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo __('add'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Kullanıcı Düzenleme Modal -->
<div class="modal fade" id="editUserModal" tabindex="-1" aria-labelledby="editUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="edit">
                <input type="hidden" name="user_id" id="edit_user_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="editUserModalLabel"><?php echo __('edit_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <div class="mb-3">
                        <label for="edit_name" class="form-label"><?php echo __('full_name'); ?></label>
                        <input type="text" class="form-control" id="edit_name" name="name" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_email" class="form-label"><?php echo __('email_address'); ?></label>
                        <input type="email" class="form-control" id="edit_email" name="email" required>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_password" class="form-label"><?php echo __('password'); ?></label>
                        <input type="password" class="form-control" id="edit_password" name="password">
                        <small class="text-muted"><?php echo __('leave_empty_password'); ?></small>
                    </div>
                    
                    <div class="mb-3">
                        <label for="edit_role" class="form-label"><?php echo __('user_role'); ?></label>
                        <select class="form-select" id="edit_role" name="role">
                            <option value="user"><?php echo __('user'); ?></option>
                            <option value="admin"><?php echo __('admin'); ?></option>
                        </select>
                    </div>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-primary"><?php echo __('save'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Kullanıcı Silme Modal -->
<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
    <div class="modal-dialog">
        <div class="modal-content">
            <form method="post" action="">
                <input type="hidden" name="action" value="delete">
                <input type="hidden" name="user_id" id="delete_user_id">
                
                <div class="modal-header">
                    <h5 class="modal-title" id="deleteUserModalLabel"><?php echo __('delete_user'); ?></h5>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
                </div>
                
                <div class="modal-body">
                    <p><?php echo __('confirm_delete'); ?></p>
                    <p><strong id="delete_user_name"></strong></p>
                </div>
                
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal"><?php echo __('cancel'); ?></button>
                    <button type="submit" class="btn btn-danger"><?php echo __('delete'); ?></button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// Düzenleme modalı için veri aktarımı
document.addEventListener('DOMContentLoaded', function() {
    const editButtons = document.querySelectorAll('.edit-user');
    editButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            const userEmail = this.getAttribute('data-email');
            const userRole = this.getAttribute('data-role');
            
            document.getElementById('edit_user_id').value = userId;
            document.getElementById('edit_name').value = userName;
            document.getElementById('edit_email').value = userEmail;
            document.getElementById('edit_role').value = userRole;
            document.getElementById('edit_password').value = '';
        });
    });
    
    // Silme modalı için veri aktarımı
    const deleteButtons = document.querySelectorAll('.delete-user');
    deleteButtons.forEach(button => {
        button.addEventListener('click', function() {
            const userId = this.getAttribute('data-id');
            const userName = this.getAttribute('data-name');
            
            document.getElementById('delete_user_id').value = userId;
            document.getElementById('delete_user_name').textContent = userName;
        });
    });
});
</script>

<?php include 'includes/footer.php'; ?>
