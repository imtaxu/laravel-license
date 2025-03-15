<?php

namespace ImTaxu\LaravelLicense\Helpers;

/**
 * Laravel Http Facade için IDE helper
 */
class HttpHelper
{
    /**
     * HTTP POST isteği gönder
     *
     * @param string $url
     * @param array $data
     * @return object
     */
    public static function post(string $url, array $data = []): object
    {
        return new class($url, $data) {
            private $url;
            private $data;
            private $response;
            
            public function __construct(string $url, array $data)
            {
                $this->url = $url;
                $this->data = $data;
                
                // Gerçek bir HTTP isteği yapmıyoruz, sadece IDE için
                $this->response = [
                    'status' => 'success',
                    'message' => 'Başarılı yanıt',
                    'data' => []
                ];
            }
            
            public function successful(): bool
            {
                return true;
            }
            
            public function json($key = null)
            {
                if ($key === null) {
                    return $this->response;
                }
                
                return $this->response[$key] ?? null;
            }
        };
    }
    
    /**
     * HTTP GET isteği gönder
     *
     * @param string $url
     * @param array $query
     * @return object
     */
    public static function get(string $url, array $query = []): object
    {
        return self::post($url, $query);
    }
}
