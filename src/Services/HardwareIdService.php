<?php

namespace ImTaxu\LaravelLicense\Services;

use Illuminate\Support\Facades\Log;
use Exception;

/**
 * Donanım Kimliği Servisi
 * 
 * Bu servis, cihazın donanım bilgilerini kullanarak benzersiz bir kimlik oluşturur.
 * Bu kimlik, lisansı belirli bir cihaza bağlamak için kullanılabilir.
 */
class HardwareIdService
{
    /**
     * Servis konfigürasyonu
     *
     * @var array
     */
    protected $config;

    /**
     * HardwareIdService constructor.
     *
     * @param array $config
     */
    public function __construct(array $config = [])
    {
        // Varsayılan konfigürasyon
        $defaultConfig = [
            'enabled' => false,              // Donanım kimliği kontrolü varsayılan olarak devre dışı
            'components' => [                // Hangi donanım bileşenlerinin kullanılacağı
                'cpu' => true,               // CPU bilgileri
                'disk' => true,              // Disk bilgileri
                'mac' => true,               // MAC adresi
                'motherboard' => true,       // Anakart bilgileri
                'os' => true                 // İşletim sistemi bilgileri
            ],
            'tolerance' => 2,                // Kaç bileşenin değişmesine izin verilecek (0-5 arası)
            'cache_key' => 'hardware_id',    // Önbellek anahtarı
            'cache_ttl' => 86400            // Önbellek süresi (saniye) - 24 saat
        ];

        // Kullanıcı konfigürasyonunu varsayılan ile birleştir
        $this->config = array_merge($defaultConfig, $config);
    }

    /**
     * Konfigürasyonu güncelle
     *
     * @param array $config
     * @return void
     */
    public function setConfig(array $config): void
    {
        $this->config = array_merge($this->config, $config);
    }

    /**
     * Donanım kimliği oluştur
     *
     * @return string|null
     */
    public function generateHardwareId(): ?string
    {
        // Eğer özellik devre dışıysa null döndür
        if (!$this->config['enabled']) {
            return null;
        }

        try {
            $hardwareInfo = [];
            
            // CPU bilgilerini al
            if ($this->config['components']['cpu']) {
                $hardwareInfo['cpu'] = $this->getCpuInfo();
            }
            
            // Disk bilgilerini al
            if ($this->config['components']['disk']) {
                $hardwareInfo['disk'] = $this->getDiskInfo();
            }
            
            // MAC adresini al
            if ($this->config['components']['mac']) {
                $hardwareInfo['mac'] = $this->getMacAddress();
            }
            
            // Anakart bilgilerini al
            if ($this->config['components']['motherboard']) {
                $hardwareInfo['motherboard'] = $this->getMotherboardInfo();
            }
            
            // İşletim sistemi bilgilerini al
            if ($this->config['components']['os']) {
                $hardwareInfo['os'] = $this->getOsInfo();
            }
            
            // Donanım bilgilerinden hash oluştur
            return $this->hashHardwareInfo($hardwareInfo);
        } catch (Exception $e) {
            Log::error('Donanım kimliği oluşturma hatası: ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Donanım kimliğini doğrula
     *
     * @param string $storedHardwareId Saklanan donanım kimliği
     * @return bool
     */
    public function verifyHardwareId(string $storedHardwareId): bool
    {
        // Eğer özellik devre dışıysa doğrulamayı geç
        if (!$this->config['enabled']) {
            return true;
        }

        try {
            // Mevcut donanım kimliğini oluştur
            $currentHardwareId = $this->generateHardwareId();
            
            // Eğer donanım kimliği oluşturulamazsa, doğrulamayı geç
            if ($currentHardwareId === null) {
                return true;
            }
            
            // Donanım kimliği tam olarak eşleşiyorsa
            if ($currentHardwareId === $storedHardwareId) {
                return true;
            }
            
            // Tolerans kontrolü
            if ($this->config['tolerance'] > 0) {
                $similarity = $this->calculateHardwareSimilarity($currentHardwareId, $storedHardwareId);
                return $similarity >= (5 - $this->config['tolerance']);
            }
            
            return false;
        } catch (Exception $e) {
            Log::error('Donanım kimliği doğrulama hatası: ' . $e->getMessage());
            return true; // Hata durumunda doğrulamayı geç
        }
    }

    /**
     * İki donanım kimliği arasındaki benzerliği hesapla
     *
     * @param string $currentId
     * @param string $storedId
     * @return int Benzerlik skoru (0-5 arası)
     */
    protected function calculateHardwareSimilarity(string $currentId, string $storedId): int
    {
        // Basit bir benzerlik hesaplama algoritması
        // Gerçek uygulamada daha karmaşık bir algoritma kullanılabilir
        $currentParts = explode('-', $currentId);
        $storedParts = explode('-', $storedId);
        
        $similarity = 0;
        $totalParts = min(count($currentParts), count($storedParts));
        
        for ($i = 0; $i < $totalParts; $i++) {
            if ($currentParts[$i] === $storedParts[$i]) {
                $similarity++;
            }
        }
        
        return $similarity;
    }

    /**
     * CPU bilgilerini al
     *
     * @return string
     */
    protected function getCpuInfo(): string
    {
        $cpuInfo = '';
        
        // Linux
        if (PHP_OS_FAMILY === 'Linux') {
            if (file_exists('/proc/cpuinfo')) {
                $cpuinfo = file_get_contents('/proc/cpuinfo');
                preg_match('/model name\s+:\s+(.*?)$/m', $cpuinfo, $matches);
                if (isset($matches[1])) {
                    $cpuInfo = $matches[1];
                }
            }
        } 
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $cpuInfo = shell_exec('sysctl -n machdep.cpu.brand_string');
        } 
        // Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            $cpuInfo = shell_exec('wmic cpu get name');
            if ($cpuInfo) {
                $cpuInfo = explode("\n", $cpuInfo)[1];
            }
        }
        
        return md5(trim($cpuInfo));
    }

    /**
     * Disk bilgilerini al
     *
     * @return string
     */
    protected function getDiskInfo(): string
    {
        $diskInfo = '';
        
        // Linux
        if (PHP_OS_FAMILY === 'Linux') {
            $diskInfo = shell_exec('lsblk -d -o NAME,SERIAL 2>/dev/null | grep -v "^NAME"');
        } 
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $diskInfo = shell_exec('diskutil info disk0 | grep "Volume UUID"');
        } 
        // Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            $diskInfo = shell_exec('wmic diskdrive get serialnumber');
        }
        
        return md5(trim($diskInfo));
    }

    /**
     * MAC adresini al
     *
     * @return string
     */
    protected function getMacAddress(): string
    {
        $macAddress = '';
        
        // Linux
        if (PHP_OS_FAMILY === 'Linux') {
            $macAddress = shell_exec("ip link | grep -E 'ether' | head -n1 | awk '{print $2}'");
        } 
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $macAddress = shell_exec("ifconfig en0 | grep ether | awk '{print $2}'");
        } 
        // Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            $macAddress = shell_exec('getmac');
        }
        
        return md5(trim($macAddress));
    }

    /**
     * Anakart bilgilerini al
     *
     * @return string
     */
    protected function getMotherboardInfo(): string
    {
        $motherboardInfo = '';
        
        // Linux
        if (PHP_OS_FAMILY === 'Linux') {
            $motherboardInfo = shell_exec('cat /sys/devices/virtual/dmi/id/board_serial 2>/dev/null');
            if (!$motherboardInfo) {
                $motherboardInfo = shell_exec('dmidecode -t 2 | grep Serial');
            }
        } 
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $motherboardInfo = shell_exec('system_profiler SPHardwareDataType | grep "Hardware UUID"');
        } 
        // Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            $motherboardInfo = shell_exec('wmic baseboard get serialnumber');
        }
        
        return md5(trim($motherboardInfo));
    }

    /**
     * İşletim sistemi bilgilerini al
     *
     * @return string
     */
    protected function getOsInfo(): string
    {
        $osInfo = PHP_OS . PHP_VERSION;
        
        // Linux
        if (PHP_OS_FAMILY === 'Linux') {
            if (file_exists('/etc/os-release')) {
                $osInfo .= file_get_contents('/etc/os-release');
            }
        } 
        // macOS
        elseif (PHP_OS_FAMILY === 'Darwin') {
            $osInfo .= shell_exec('sw_vers');
        } 
        // Windows
        elseif (PHP_OS_FAMILY === 'Windows') {
            $osInfo .= shell_exec('ver');
        }
        
        return md5(trim($osInfo));
    }

    /**
     * Donanım bilgilerinden hash oluştur
     *
     * @param array $hardwareInfo
     * @return string
     */
    protected function hashHardwareInfo(array $hardwareInfo): string
    {
        // Her bileşen için bir hash oluştur
        $hashes = [];
        
        if (isset($hardwareInfo['cpu'])) {
            $hashes[] = substr($hardwareInfo['cpu'], 0, 8);
        } else {
            $hashes[] = str_repeat('0', 8);
        }
        
        if (isset($hardwareInfo['disk'])) {
            $hashes[] = substr($hardwareInfo['disk'], 0, 8);
        } else {
            $hashes[] = str_repeat('0', 8);
        }
        
        if (isset($hardwareInfo['mac'])) {
            $hashes[] = substr($hardwareInfo['mac'], 0, 8);
        } else {
            $hashes[] = str_repeat('0', 8);
        }
        
        if (isset($hardwareInfo['motherboard'])) {
            $hashes[] = substr($hardwareInfo['motherboard'], 0, 8);
        } else {
            $hashes[] = str_repeat('0', 8);
        }
        
        if (isset($hardwareInfo['os'])) {
            $hashes[] = substr($hardwareInfo['os'], 0, 8);
        } else {
            $hashes[] = str_repeat('0', 8);
        }
        
        // Tüm hash'leri birleştir
        return implode('-', $hashes);
    }
}
