<?php
/**
 * Lisans Yönetim Paneli - Ayarlar Sayfası
 */

// Güvenlik kontrolü index.php üzerinden geldiğinden emin oluyoruz
// Bu sayfa doğrudan çağrıldığında çalışmayacak

// Ayarları kaydet
$successMessage = '';
$errorMessage = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['save_settings'])) {
    // Dil ayarını kaydet
    if (isset($_POST['language'])) {
        $language = $_POST['language'];
        if (setLanguage($language)) {
            $successMessage = __('settings_saved');
        } else {
            $errorMessage = __('language_save_error');
        }
    }
}

// Mevcut dil ayarını al
$currentLanguage = getCurrentLanguage();
$availableLanguages = getAvailableLanguages();
?>

<div class="container-fluid">
    <div class="row">
        <div class="col-md-12">
            <h2 class="mb-4"><?php echo __('settings_title'); ?></h2>
            
            <?php if (!empty($successMessage)): ?>
                <div class="alert alert-success"><?php echo $successMessage; ?></div>
            <?php endif; ?>
            
            <?php if (!empty($errorMessage)): ?>
                <div class="alert alert-danger"><?php echo $errorMessage; ?></div>
            <?php endif; ?>
            
            <div class="card">
                <div class="card-body">
                    <form method="post" action="index.php?page=settings">
                        <div class="mb-3">
                            <label for="language" class="form-label"><?php echo __('language'); ?></label>
                            <select class="form-select" id="language" name="language">
                                <?php foreach ($availableLanguages as $code => $name): ?>
                                    <option value="<?php echo $code; ?>" <?php echo ($code === $currentLanguage) ? 'selected' : ''; ?>>
                                        <?php echo $name; ?>
                                    </option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        
                        <button type="submit" name="save_settings" class="btn btn-primary">
                            <?php echo __('save_settings'); ?>
                        </button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
