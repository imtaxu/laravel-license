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
            // Process only string literals
            if ($node instanceof Node\Scalar\String_) {
                // Skip very long strings
                if (strlen($node->value) > 1000) {
                    return null;
                }

                // Skip empty strings
                if (empty($node->value)) {
                    return null;
                }

                // Convert string to hex format
                $encoded = $this->stringToHex($node->value);

                // Replace string with hex-encoded string
                $node->value = $encoded;
                return $node;
            }
        } catch (\Exception $e) {
            // Return original node in case of error
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
