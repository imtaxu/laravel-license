<?php

namespace Imtaxu\LaravelLicense\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use PhpParser\ParserFactory;
use PhpParser\NodeTraverser;
use PhpParser\Error;
use PhpParser\NodeVisitor\NameResolver;
use PhpParser\PrettyPrinter;
use Illuminate\Support\Facades\Log;

class LicenseObfuscateCommand extends Command
{
    protected $signature = 'license:obfuscate
                           {--config : Obfuscate the license config file}
                           {--routes : Obfuscate the routes/web.php file}
                           {--all : Obfuscate both config and routes files (default)}';

    protected $description = 'Obfuscate license-related files to prevent tampering';

    // Obfuscator visitors and traversers
    protected $traverser;
    protected $prettyPrinter;
    protected $stringEncoder;
    protected $enableCompression;

    public function handle()
    {
        $this->info('Starting license files obfuscation...');

        // Set up PHP Parser components
        $this->setupObfuscator();

        try {
            // Determine which files to obfuscate
            $obfuscateConfig = $this->option('config') || $this->option('all') || (!$this->option('config') && !$this->option('routes'));
            $obfuscateRoutes = $this->option('routes') || $this->option('all') || (!$this->option('config') && !$this->option('routes'));

            // Obfuscate files
            if ($obfuscateConfig) {
                $this->obfuscateConfigFile();
            }

            if ($obfuscateRoutes) {
                $this->obfuscateRoutesFile();
            }

            $this->info('Obfuscation completed successfully!');
            return Command::SUCCESS;
        } catch (\Exception $e) {
            $this->error('Obfuscation failed: ' . $e->getMessage());
            Log::error('License obfuscation failed: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }

    protected function setupObfuscator()
    {
        $parserFactory = new ParserFactory();
        $parser = $parserFactory->createForNewestSupportedVersion();

        $this->traverser = new NodeTraverser();

        // Get obfuscation settings from config
        $config = config('license.obfuscation', []);
        $variablePrefix = $config['variable_prefix'] ?? '_im';
        $protectedVars = $config['protected_variables'] ?? [];
        $enableStringEncoding = $config['enable_string_encoding'] ?? true;
        $enableCompression = $config['enable_compression'] ?? true;

        // Create visitors with configuration
        $this->traverser->addVisitor(new NameResolver());
        $this->traverser->addVisitor(new \Imtaxu\LaravelLicense\Obfuscation\VariableScrambler($variablePrefix, $protectedVars));

        if ($enableStringEncoding) {
            $this->stringEncoder = new \Imtaxu\LaravelLicense\Obfuscation\StringEncoder();
            $this->traverser->addVisitor($this->stringEncoder);
        }

        $this->enableCompression = $enableCompression;

        // Setup pretty printer
        $this->prettyPrinter = new PrettyPrinter\Standard();
    }

    protected function obfuscateConfigFile()
    {
        $configPath = config_path('license.php');
        if (!file_exists($configPath)) {
            $this->warn('License config file not found at: ' . $configPath);
            return;
        }

        $this->info('Obfuscating license config file...');
        $this->obfuscateFile($configPath);
        $this->info('License config file obfuscated successfully.');
    }

    protected function obfuscateRoutesFile()
    {
        $routesPath = base_path('routes/web.php');
        if (!file_exists($routesPath)) {
            $this->warn('Web routes file not found at: ' . $routesPath);
            return;
        }

        $this->info('Obfuscating web routes file...');
        $this->obfuscateFile($routesPath);
        $this->info('Web routes file obfuscated successfully.');
    }

    protected function obfuscateFile($filePath)
    {
        // Get file content
        $code = file_get_contents($filePath);

        // Create code tree
        $ast = $this->parseCode($code);

        // Obfuscate AST
        $obfuscatedAst = $this->traverser->traverse($ast);

        // Generate obfuscated code
        $obfuscatedCode = $this->prettyPrinter->prettyPrintFile($obfuscatedAst);

        // Add encoding layer for additional security
        $obfuscatedCode = $this->addEncodingLayer($obfuscatedCode);

        // Update file
        file_put_contents($filePath, $obfuscatedCode);
    }

    protected function parseCode($code)
    {
        try {
            $parserFactory = new ParserFactory();
            $parser = $parserFactory->createForNewestSupportedVersion();
            return $parser->parse($code);
        } catch (Error $e) {
            throw new \Exception('Error parsing PHP code: ' . $e->getMessage());
        }
    }

    protected function addEncodingLayer($code)
    {
        // Remove PHP opening tag
        $code = preg_replace('/^<\?php\s+/', '', $code);

        // Generate a random key
        $key = substr(md5(uniqid(rand(), true)), 0, 16);

        // XOR encryption
        $encrypted = '';
        for ($i = 0; $i < strlen($code); $i++) {
            $encrypted .= chr(ord($code[$i]) ^ ord($key[$i % strlen($key)]));
        }

        // Convert to hex format
        $hexCode = '';
        for ($i = 0; $i < strlen($encrypted); $i++) {
            $hexCode .= '\\x' . bin2hex($encrypted[$i]);
        }

        // Create decoder wrapper code
        $decoder = "<?php\n\n";
        $decoder .= "// This file has been obfuscated by ImtaxuLicense\n";
        $decoder .= "// Do not modify or license validation will fail\n\n";

        // Define function and pass its result to eval
        $decoder .= "\$decode = function() {\n";
        $decoder .= "    \$hex = \"{$hexCode}\";\n";
        $decoder .= "    \$key = '{$key}';\n";
        $decoder .= "    \$result = '';\n";
        $decoder .= "    \$result_index = 0;\n";
        $decoder .= "    for (\$i = 0; \$i < strlen(\$hex); \$i++) {\n";
        $decoder .= "        if (\$hex[\$i] === '\\\\' && \$hex[\$i+1] === 'x') {\n";
        $decoder .= "            \$hexChar = substr(\$hex, \$i+2, 2);\n";
        $decoder .= "            \$char = chr(hexdec(\$hexChar));\n";
        $decoder .= "            \$result .= chr(ord(\$char) ^ ord(\$key[\$result_index % strlen(\$key)]));\n";
        $decoder .= "            \$result_index++;\n";
        $decoder .= "            \$i += 3;\n";
        $decoder .= "        }\n";
        $decoder .= "    }\n";
        $decoder .= "    return \$result;\n";
        $decoder .= "};\n\n";
        $decoder .= "eval(\$decode());\n";

        return $decoder;
    }
}
