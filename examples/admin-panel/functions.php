<?php
/**
 * Lisans Yönetim Paneli Yardımcı Fonksiyonlar
 */

/**
 * Güvenli bir lisans anahtarı oluşturur
 * 
 * @param int $length Anahtar uzunluğu
 * @return string
 */
function generateLicenseKey($length = 32) {
    $characters = '0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ';
    $key = '';
    
    for ($i = 0; $i < $length; $i++) {
        $key .= $characters[random_int(0, strlen($characters) - 1)];
    }
    
    // XXXX-XXXX-XXXX-XXXX formatına dönüştür
    $formattedKey = '';
    for ($i = 0; $i < strlen($key); $i++) {
        $formattedKey .= $key[$i];
        if (($i + 1) % 4 == 0 && $i < strlen($key) - 1) {
            $formattedKey .= '-';
        }
    }
    
    return $formattedKey;
}

/**
 * Bir metni güvenli şekilde hashler
 * 
 * @param string $text Hashlenecek metin
 * @return string
 */
function secureHash($text) {
    return hash('sha256', $text . HASH_SALT);
}

/**
 * Bir tarihi formatlar
 * 
 * @param string $date Tarih
 * @param string $format Format
 * @return string
 */
function formatDate($date, $format = 'd.m.Y H:i') {
    if (empty($date)) {
        return '-';
    }
    
    try {
        $dateObj = new DateTime($date);
        return $dateObj->format($format);
    } catch (Exception $e) {
        error_log('Tarih formatı hatası: ' . $e->getMessage());
        return '-';
    }
}

/**
 * Lisans durumunu Türkçe olarak döndürür
 * 
 * @param string $status Durum
 * @return string
 */
function getLicenseStatusText($status) {
    switch ($status) {
        case 'active':
            return 'Aktif';
        case 'inactive':
            return 'Pasif';
        case 'suspended':
            return 'Askıya Alınmış';
        default:
            return 'Bilinmiyor';
    }
}

/**
 * Lisans durumuna göre badge sınıfını döndürür
 * 
 * @param string $status Durum
 * @return string
 */
function getLicenseStatusBadgeClass($status) {
    switch ($status) {
        case 'active':
            return 'bg-success';
        case 'inactive':
            return 'bg-secondary';
        case 'suspended':
            return 'bg-warning text-dark';
        default:
            return 'bg-secondary';
    }
}

/**
 * Bir domain'in geçerli olup olmadığını kontrol eder
 * 
 * @param string $domain Domain
 * @return bool
 */
function isValidDomain($domain) {
    // Domain formatını kontrol et
    return (preg_match('/^(?:[-A-Za-z0-9]+\.)+[A-Za-z]{2,}$/', $domain));
}

/**
 * Bir e-posta adresinin geçerli olup olmadığını kontrol eder
 * 
 * @param string $email E-posta adresi
 * @return bool
 */
function isValidEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

/**
 * Bir IP adresinin geçerli olup olmadığını kontrol eder
 * 
 * @param string $ip IP adresi
 * @return bool
 */
function isValidIp($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

/**
 * Bir mesaj gösterir ve belirtilen süre sonra yönlendirir
 * 
 * @param string $message Mesaj
 * @param string $type Mesaj tipi (success, danger, warning, info)
 * @param string $redirect Yönlendirilecek URL
 * @param int $delay Gecikme süresi (saniye)
 */
function showMessageAndRedirect($message, $type = 'info', $redirect = '', $delay = 3) {
    $_SESSION['message'] = [
        'text' => $message,
        'type' => $type
    ];
    
    if (!empty($redirect)) {
        header("Refresh: $delay; URL=$redirect");
    }
}

/**
 * Bir hata mesajı gösterir
 * 
 * @param string $message Mesaj
 */
function showError($message) {
    echo '<div class="alert alert-danger">' . $message . '</div>';
}

/**
 * Bir başarı mesajı gösterir
 * 
 * @param string $message Mesaj
 */
function showSuccess($message) {
    echo '<div class="alert alert-success">' . $message . '</div>';
}

/**
 * Bir bilgi mesajı gösterir
 * 
 * @param string $message Mesaj
 */
function showInfo($message) {
    echo '<div class="alert alert-info">' . $message . '</div>';
}

/**
 * Bir uyarı mesajı gösterir
 * 
 * @param string $message Mesaj
 */
function showWarning($message) {
    echo '<div class="alert alert-warning">' . $message . '</div>';
}

/**
 * Session'da kayıtlı mesaj varsa gösterir ve siler
 */
function showFlashMessage() {
    if (isset($_SESSION['message'])) {
        $messageType = $_SESSION['message']['type'];
        $messageText = $_SESSION['message']['text'];
        
        echo '<div class="alert alert-' . $messageType . ' alert-dismissible fade show" role="alert">';
        echo $messageText;
        echo '<button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Kapat"></button>';
        echo '</div>';
        
        unset($_SESSION['message']);
    }
}

/**
 * Veritabanı bağlantısı oluşturur
 * 
 * @return PDO
 */
function getDbConnection() {
    try {
        $db = new PDO(
            'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=utf8mb4',
            DB_USER,
            DB_PASS,
            [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]
        );
        return $db;
    } catch (PDOException $e) {
        die('Veritabanı bağlantı hatası: ' . $e->getMessage());
    }
}

/**
 * Zaman farkını insanların anlayabileceği formatta döndürür
 * 
 * @param string $datetime Tarih ve saat
 * @return string
 */
function timeAgo($datetime) {
    if (empty($datetime)) {
        return '-';
    }
    
    try {
        $time = new DateTime($datetime);
        $now = new DateTime();
        $diff = $now->getTimestamp() - $time->getTimestamp();
        
        if ($diff < 60) {
            return $diff . ' saniye önce';
        } elseif ($diff < 3600) {
            return floor($diff / 60) . ' dakika önce';
        } elseif ($diff < 86400) {
            return floor($diff / 3600) . ' saat önce';
        } elseif ($diff < 604800) {
            return floor($diff / 86400) . ' gün önce';
        } elseif ($diff < 2592000) {
            return floor($diff / 604800) . ' hafta önce';
        } elseif ($diff < 31536000) {
            return floor($diff / 2592000) . ' ay önce';
        } else {
            return floor($diff / 31536000) . ' yıl önce';
        }
    } catch (Exception $e) {
        error_log('Zaman hesaplama hatası: ' . $e->getMessage());
        return '-';
    }
}
