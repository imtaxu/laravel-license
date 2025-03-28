<?php

namespace Imtaxu\LaravelLicense\Obfuscation;

use PhpParser\Node;
use PhpParser\NodeVisitorAbstract;

class VariableScrambler extends NodeVisitorAbstract
{
    private $variableMap = [];
    private $prefix;
    private $protectedVars = [];
    
    public function __construct($prefix = '_im', $protectedVars = []) {
        $this->prefix = $prefix;
        $this->protectedVars = array_merge(['this'], $protectedVars);
    }
    
    public function enterNode(Node $node) {
        if ($node instanceof Node\Expr\Variable && is_string($node->name)) {
            // Skip protected variables and variables starting with underscore
            if (in_array($node->name, $this->protectedVars) || strpos($node->name, '_') === 0) {
                return null;
            }
            
            // Change variable name
            if (!isset($this->variableMap[$node->name])) {
                $this->variableMap[$node->name] = $this->prefix . '_' . substr(md5($node->name), 0, 8);
            }
            $node->name = $this->variableMap[$node->name];
        }
        
        return null;
    }
}