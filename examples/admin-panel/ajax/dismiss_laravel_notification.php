<?php
/**
 * Laravel License bildirimlerini kapatma işlemi için AJAX endpoint
 */

// Gerekli dosyaları dahil et
require_once '../config.php';
require_once '../functions.php';
require_once '../auth.php';

// Oturum kontrolü
session_start();
$auth = new Auth();

if (!$auth->isLoggedIn()) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Oturum açık değil']);
    exit;
}

// POST verilerini kontrol et
if (!isset($_POST['notification_key']) || empty($_POST['notification_key'])) {
    header('Content-Type: application/json');
    echo json_encode(['success' => false, 'message' => 'Geçersiz bildirim anahtarı']);
    exit;
}

$notificationKey = $_POST['notification_key'];
$threshold = isset($_POST['threshold']) ? (int)$_POST['threshold'] : null;

// Bir sonraki eşik değerini belirle
$nextThreshold = null;
$thresholds = [30, 15, 7, 3, 1];

if ($threshold !== null) {
    $currentIndex = array_search($threshold, $thresholds);
    if ($currentIndex !== false && $currentIndex < count($thresholds) - 1) {
        $nextThreshold = $thresholds[$currentIndex + 1];
    }
}

// Laravel License Checker ile bildirimi kapat
$result = false;

try {
    // Lisans Checker konfigürasyonu
    $licenseConfig = include '../../license-config.php';
    
    if (class_exists('\\ImTaxu\\LaravelLicense\\LicenseChecker')) {
        $licenseChecker = new \ImTaxu\LaravelLicense\LicenseChecker($licenseConfig);
        $result = $licenseChecker->dismissLicenseNotification($notificationKey, $nextThreshold);
    }
} catch (Exception $e) {
    error_log('Laravel License bildirim kapatma hatası: ' . $e->getMessage());
    $result = false;
}

// Sonucu döndür
header('Content-Type: application/json');
echo json_encode(['success' => $result]);
