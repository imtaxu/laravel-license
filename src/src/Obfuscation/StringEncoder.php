<?php

namespace Imtaxu\LaravelLicense\Obfuscation;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class StringEncoder extends NodeVisitorAbstract
{
    private $key;

    public function __construct($key = null)
    {
        $this->key = $key ?: substr(md5(uniqid()), 0, 16);
    }

    public function enterNode(Node $node)
    {
        try {
            // Sadece string literal'ları işle
            if ($node instanceof Node\Scalar\String_) {
                // Çok uzun string'leri atla
                if (strlen($node->value) > 1000) {
                    return null;
                }

                // Boş string'leri atla
                if (empty($node->value)) {
                    return null;
                }

                // String'i hex formatına dönüştür
                $encoded = $this->stringToHex($node->value);

                // String'i direk olarak hex-encoded string ile değiştir
                $node->value = $encoded;
                return $node;
            }
        } catch (\Exception $e) {
            // Hata durumunda orijinal nod'u değiştirmeden döndür
            return null;
        }

        return null;
    }

    private function stringToHex($str)
    {
        $result = '';
        for ($i = 0; $i < strlen($str); $i++) {
            $result .= "\\x" . bin2hex($str[$i]);
        }
        return $result;
    }

    public function getKey()
    {
        return $this->key;
    }
}
