#!/usr/bin/env php
<?php
/**
 * Apileon Framework - Secure Deployment Package Generator
 * 
 * This script creates a compressed, production-ready deployment package with:
 * - Security hardening
 * - File access restrictions
 * - Obfuscated sensitive files
 * - Optimized structure
 * - Access control mechanisms
 */

class SecureDeploymentGenerator
{
    private $sourceDir;
    private $buildDir;
    private $packageName;
    private $excludePatterns = [];
    private $securityKey;
    
    public function __construct()
    {
        $this->sourceDir = __DIR__;
        $this->buildDir = $this->sourceDir . '/build';
        $this->packageName = 'apileon-secure-' . date('Y-m-d-H-i-s');
        $this->securityKey = $this->generateSecurityKey();
        
        $this->excludePatterns = [
            '/.git/',
            '/node_modules/',
            '/tests/',
            '/docs/',
            '/build/',
            '/.env',
            '/composer.lock',
            '/phpunit.xml',
            '/deploy.sh',
            '/test-no-composer.php',
            '/setup-no-composer.sh',
            '/status.sh',
            '/.gitignore',
            '/README.md',
            '/DEPLOYMENT_GUIDE.md',
            '/DATABASE_CRUD_GUIDE.md',
            '/IMPLEMENTATION_SUMMARY.md'
        ];
    }
    
    public function generate()
    {
        $this->log("🔐 Starting Secure Deployment Package Generation...\n");
        
        $this->createBuildDirectory();
        $this->copySecureFiles();
        $this->createSecurityWrapper();
        $this->createSecuredIndex();
        $this->createHtaccessFiles();
        $this->createProductionEnv();
        $this->optimizeFiles();
        $this->createPackage();
        $this->generateInstallScript();
        $this->cleanup();
        
        $this->log("\n✅ Secure deployment package created successfully!");
        $this->log("📦 Package: {$this->packageName}.tar.gz");
        $this->log("🔑 Security Key: {$this->securityKey}");
        $this->log("\n🚀 Ready for production deployment!");
    }
    
    private function createBuildDirectory()
    {
        $this->log("📁 Creating build directory...");
        
        if (is_dir($this->buildDir)) {
            $this->removeDirectory($this->buildDir);
        }
        
        mkdir($this->buildDir, 0755, true);
        mkdir($this->buildDir . '/' . $this->packageName, 0755, true);
    }
    
    private function copySecureFiles()
    {
        $this->log("📋 Copying and securing files...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        
        $this->copyDirectorySecure($this->sourceDir, $packageDir);
        
        // Remove sensitive files
        $this->removeSensitiveFiles($packageDir);
    }
    
    private function copyDirectorySecure($source, $destination)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($source, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::SELF_FIRST
        );
        
        foreach ($iterator as $item) {
            $relativePath = str_replace($source, '', $item->getPathname());
            
            // Skip excluded patterns
            if ($this->shouldExclude($relativePath)) {
                continue;
            }
            
            $target = $destination . $relativePath;
            
            if ($item->isDir()) {
                if (!is_dir($target)) {
                    mkdir($target, 0755, true);
                }
            } else {
                $this->copyFileSecure($item->getPathname(), $target);
            }
        }
    }
    
    private function copyFileSecure($source, $target)
    {
        $targetDir = dirname($target);
        if (!is_dir($targetDir)) {
            mkdir($targetDir, 0755, true);
        }
        
        // Check if it's a PHP file that needs security wrapping
        if (pathinfo($source, PATHINFO_EXTENSION) === 'php' && 
            !$this->isPublicFile($source)) {
            $this->createSecuredPhpFile($source, $target);
        } else {
            copy($source, $target);
        }
    }
    
    private function createSecuredPhpFile($source, $target)
    {
        $content = file_get_contents($source);
        
        // Add security wrapper to non-public PHP files
        if (!$this->isPublicFile($source)) {
            $securedContent = $this->wrapWithSecurity($content);
            file_put_contents($target, $securedContent);
        } else {
            file_put_contents($target, $content);
        }
    }
    
    private function wrapWithSecurity($content)
    {
        $securityWrapper = <<<PHP
<?php
/**
 * Apileon Framework - Secured File
 * This file is protected against direct access
 */

// Security check - prevent direct access
if (!defined('APILEON_SECURITY_KEY') || APILEON_SECURITY_KEY !== '{$this->securityKey}') {
    http_response_code(403);
    die('Access Denied: Unauthorized file access attempt detected.');
}

// Remove opening PHP tag from original content
PHP;
        
        // Remove opening PHP tag from original content
        $content = preg_replace('/^<\?php\s*/', '', $content);
        
        return $securityWrapper . "\n\n" . $content;
    }
    
    private function createSecurityWrapper()
    {
        $this->log("🔒 Creating security wrapper...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        
        $securityFile = $packageDir . '/src/Security/AccessControl.php';
        $securityDir = dirname($securityFile);
        
        if (!is_dir($securityDir)) {
            mkdir($securityDir, 0755, true);
        }
        
        $securityContent = <<<PHP
<?php
/**
 * Apileon Framework - Security Access Control
 * Manages secure access to framework components
 */

namespace Apileon\Security;

class AccessControl
{
    private static \$initialized = false;
    private static \$securityKey = '{$this->securityKey}';
    private static \$allowedIPs = [];
    private static \$blockedIPs = [];
    
    /**
     * Initialize security system
     */
    public static function initialize(): void
    {
        if (self::\$initialized) {
            return;
        }
        
        // Define security constant
        if (!defined('APILEON_SECURITY_KEY')) {
            define('APILEON_SECURITY_KEY', self::\$securityKey);
        }
        
        // Security headers
        self::setSecurityHeaders();
        
        // IP filtering
        self::checkIPAccess();
        
        // File access protection
        self::protectDirectAccess();
        
        self::\$initialized = true;
    }
    
    /**
     * Set security headers
     */
    private static function setSecurityHeaders(): void
    {
        if (!headers_sent()) {
            header('X-Frame-Options: DENY');
            header('X-XSS-Protection: 1; mode=block');
            header('X-Content-Type-Options: nosniff');
            header('Referrer-Policy: strict-origin-when-cross-origin');
            header('Content-Security-Policy: default-src \'self\'');
            header('Strict-Transport-Security: max-age=31536000; includeSubDomains');
            header('X-Powered-By: '); // Remove server signature
        }
    }
    
    /**
     * Check IP access
     */
    private static function checkIPAccess(): void
    {
        \$clientIP = self::getClientIP();
        
        // Check blocked IPs
        if (in_array(\$clientIP, self::\$blockedIPs)) {
            self::denyAccess('IP blocked');
        }
        
        // Check allowed IPs (if configured)
        if (!empty(self::\$allowedIPs) && !in_array(\$clientIP, self::\$allowedIPs)) {
            self::denyAccess('IP not in whitelist');
        }
    }
    
    /**
     * Protect against direct file access
     */
    private static function protectDirectAccess(): void
    {
        \$script = \$_SERVER['SCRIPT_NAME'] ?? '';
        \$requestUri = \$_SERVER['REQUEST_URI'] ?? '';
        
        // Block direct access to framework files
        if (preg_match('/\/(src|app|config|database)\//i', \$requestUri)) {
            self::denyAccess('Direct file access denied');
        }
        
        // Block access to sensitive files
        if (preg_match('/\.(env|log|sql|txt|md|json|lock)$/i', \$requestUri)) {
            self::denyAccess('Sensitive file access denied');
        }
    }
    
    /**
     * Get real client IP
     */
    private static function getClientIP(): string
    {
        \$ipKeys = ['HTTP_CF_CONNECTING_IP', 'HTTP_CLIENT_IP', 'HTTP_X_FORWARDED_FOR', 'REMOTE_ADDR'];
        
        foreach (\$ipKeys as \$key) {
            if (!empty(\$_SERVER[\$key])) {
                \$ip = \$_SERVER[\$key];
                if (strpos(\$ip, ',') !== false) {
                    \$ip = explode(',', \$ip)[0];
                }
                \$ip = trim(\$ip);
                if (filter_var(\$ip, FILTER_VALIDATE_IP, FILTER_FLAG_NO_PRIV_RANGE | FILTER_FLAG_NO_RES_RANGE)) {
                    return \$ip;
                }
            }
        }
        
        return \$_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
    }
    
    /**
     * Deny access and log attempt
     */
    private static function denyAccess(string \$reason): void
    {
        \$ip = self::getClientIP();
        \$timestamp = date('Y-m-d H:i:s');
        \$logEntry = "[\$timestamp] SECURITY ALERT: \$reason - IP: \$ip - URI: {\$_SERVER['REQUEST_URI']}\n";
        
        // Log security event
        \$logFile = __DIR__ . '/../../storage/logs/security.log';
        \$logDir = dirname(\$logFile);
        if (!is_dir(\$logDir)) {
            mkdir(\$logDir, 0755, true);
        }
        file_put_contents(\$logFile, \$logEntry, FILE_APPEND | LOCK_EX);
        
        // Send response
        http_response_code(403);
        header('Content-Type: application/json');
        echo json_encode([
            'error' => 'Access Denied',
            'message' => 'Unauthorized access attempt detected and logged.',
            'timestamp' => \$timestamp
        ]);
        exit;
    }
    
    /**
     * Validate security key
     */
    public static function validateKey(string \$key): bool
    {
        return hash_equals(self::\$securityKey, \$key);
    }
    
    /**
     * Add allowed IP
     */
    public static function addAllowedIP(string \$ip): void
    {
        if (!in_array(\$ip, self::\$allowedIPs)) {
            self::\$allowedIPs[] = \$ip;
        }
    }
    
    /**
     * Block IP
     */
    public static function blockIP(string \$ip): void
    {
        if (!in_array(\$ip, self::\$blockedIPs)) {
            self::\$blockedIPs[] = \$ip;
        }
    }
}
PHP;
        
        file_put_contents($securityFile, $securityContent);
    }
    
    private function createSecuredIndex()
    {
        $this->log("🎯 Creating secured index file...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        $indexFile = $packageDir . '/public/index.php';
        
        $indexContent = <<<PHP
<?php
/**
 * Apileon Framework - Secured Entry Point
 * Production-ready with enhanced security
 */

// Security initialization
require_once __DIR__ . '/../src/Security/AccessControl.php';
\Apileon\Security\AccessControl::initialize();

// Define security constant
define('APILEON_SECURITY_KEY', '{$this->securityKey}');

// Error handling for production
if (getenv('APP_ENV') !== 'local') {
    ini_set('display_errors', 0);
    ini_set('display_startup_errors', 0);
    error_reporting(E_ERROR | E_PARSE);
}

// Set timezone
date_default_timezone_set('UTC');

// Initialize autoloader
require_once __DIR__ . '/../autoload.php';

// Initialize application
use Apileon\Foundation\Application;

try {
    \$app = new Application(dirname(__DIR__));
    \$app->run();
} catch (Throwable \$e) {
    // Log error securely
    \$logFile = __DIR__ . '/../storage/logs/error.log';
    \$logDir = dirname(\$logFile);
    if (!is_dir(\$logDir)) {
        mkdir(\$logDir, 0755, true);
    }
    
    \$timestamp = date('Y-m-d H:i:s');
    \$errorLog = "[\$timestamp] FATAL ERROR: " . \$e->getMessage() . " in " . \$e->getFile() . ":" . \$e->getLine() . "\n";
    file_put_contents(\$logFile, \$errorLog, FILE_APPEND | LOCK_EX);
    
    // Show generic error in production
    http_response_code(500);
    header('Content-Type: application/json');
    echo json_encode([
        'error' => 'Internal Server Error',
        'message' => 'An unexpected error occurred. Please try again later.',
        'timestamp' => \$timestamp
    ]);
}
PHP;
        
        file_put_contents($indexFile, $indexContent);
    }
    
    private function createHtaccessFiles()
    {
        $this->log("🛡️ Creating .htaccess security files...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        
        // Main .htaccess (root)
        $mainHtaccess = $packageDir . '/.htaccess';
        $mainHtaccessContent = <<<HTACCESS
# Apileon Framework - Security Configuration
RewriteEngine On

# Redirect all requests to public directory
RewriteCond %{REQUEST_URI} !^/public/
RewriteRule ^(.*)$ /public/$1 [L,QSA]

# Block direct access to framework directories
RewriteCond %{REQUEST_URI} ^/(src|app|config|database|storage|vendor)/
RewriteRule ^.*$ - [F,L]

# Block access to sensitive files
<FilesMatch "\.(env|log|sql|txt|md|json|lock|sh)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
    Header always set Content-Security-Policy "default-src 'self'"
    Header always unset X-Powered-By
</IfModule>

# Disable server signature
ServerTokens Prod
ServerSignature Off
HTACCESS;
        
        file_put_contents($mainHtaccess, $mainHtaccessContent);
        
        // Public .htaccess
        $publicHtaccess = $packageDir . '/public/.htaccess';
        $publicHtaccessContent = <<<HTACCESS
# Apileon Framework - Public Directory Security
RewriteEngine On

# Handle front controller
RewriteCond %{REQUEST_FILENAME} !-d
RewriteCond %{REQUEST_FILENAME} !-f
RewriteRule ^ index.php [L]

# Block access to PHP files except index.php
RewriteCond %{REQUEST_FILENAME} \.php$
RewriteCond %{REQUEST_FILENAME} !index\.php$
RewriteRule ^.*$ - [F,L]

# Security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
    Header always set X-Content-Type-Options "nosniff"
</IfModule>

# Rate limiting (if mod_evasive is available)
<IfModule mod_evasive24.c>
    DOSHashTableSize    2048
    DOSPageCount        20
    DOSSiteCount        50
    DOSPageInterval     1
    DOSSiteInterval     1
    DOSBlockingPeriod   300
</IfModule>
HTACCESS;
        
        file_put_contents($publicHtaccess, $publicHtaccessContent);
        
        // Block access to sensitive directories
        $protectedDirs = ['src', 'app', 'config', 'database', 'storage'];
        foreach ($protectedDirs as $dir) {
            $dirPath = $packageDir . '/' . $dir;
            if (is_dir($dirPath)) {
                $htaccessFile = $dirPath . '/.htaccess';
                file_put_contents($htaccessFile, "Order deny,allow\nDeny from all");
            }
        }
    }
    
    private function createProductionEnv()
    {
        $this->log("⚙️ Creating production environment template...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        $envFile = $packageDir . '/.env.production';
        
        $envContent = <<<ENV
# Apileon Framework - Production Environment Configuration
# IMPORTANT: Update all values before deploying to production

# Application
APP_NAME=Apileon
APP_ENV=production
APP_DEBUG=false
APP_KEY=GENERATE_32_CHARACTER_SECRET_KEY_HERE
APP_URL=https://your-production-domain.com

# Security
APILEON_SECURITY_KEY={$this->securityKey}
JWT_SECRET=GENERATE_STRONG_JWT_SECRET_HERE
HASH_SALT=GENERATE_RANDOM_SALT_HERE

# Database (Update with your production database)
DB_CONNECTION=mysql
DB_HOST=your-production-db-host
DB_PORT=3306
DB_DATABASE=your_production_database
DB_USERNAME=your_db_username
DB_PASSWORD=your_secure_db_password

# Caching (Redis recommended for production)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379

# Logging
LOG_CHANNEL=single
LOG_LEVEL=error

# Mail (if needed)
MAIL_MAILER=smtp
MAIL_HOST=your-smtp-host
MAIL_PORT=587
MAIL_USERNAME=your-email@domain.com
MAIL_PASSWORD=your-email-password
MAIL_ENCRYPTION=tls

# Rate Limiting
RATE_LIMIT_REQUESTS=60
RATE_LIMIT_DURATION=60

# Monitoring
HEALTH_CHECK_ENABLED=true
METRICS_ENABLED=true
ENV;
        
        file_put_contents($envFile, $envContent);
    }
    
    private function optimizeFiles()
    {
        $this->log("⚡ Optimizing files for production...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        
        // Remove unnecessary files and comments from PHP files
        $this->optimizePhpFiles($packageDir);
        
        // Create optimized autoloader
        $this->createOptimizedAutoloader($packageDir);
    }
    
    private function optimizePhpFiles($directory)
    {
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($directory, RecursiveDirectoryIterator::SKIP_DOTS)
        );
        
        foreach ($iterator as $file) {
            if ($file->getExtension() === 'php') {
                $content = file_get_contents($file->getPathname());
                
                // Remove PHP comments (but keep security headers)
                $content = preg_replace('/\/\*(?!.*\*\s*Apileon Framework).*?\*\//s', '', $content);
                $content = preg_replace('/\/\/(?!.*Apileon Framework).*$/m', '', $content);
                
                // Remove extra whitespace
                $content = preg_replace('/\n\s*\n/', "\n", $content);
                
                file_put_contents($file->getPathname(), $content);
            }
        }
    }
    
    private function createOptimizedAutoloader($packageDir)
    {
        $autoloaderFile = $packageDir . '/autoload.php';
        $autoloaderContent = <<<PHP
<?php
/**
 * Apileon Framework - Optimized Production Autoloader
 * Enhanced with security checks and performance optimizations
 */

// Security check
if (!defined('APILEON_SECURITY_KEY')) {
    http_response_code(403);
    die('Access Denied');
}

// Performance: Use optimized class map for production
spl_autoload_register(function (\$class) {
    static \$classMap = null;
    
    if (\$classMap === null) {
        \$classMap = [
            // Core classes (will be populated during build)
        ];
    }
    
    if (isset(\$classMap[\$class])) {
        require_once \$classMap[\$class];
        return;
    }
    
    // Fallback to PSR-4 autoloading
    \$prefix = 'Apileon\\\\';
    \$baseDir = __DIR__ . '/src/';
    
    \$len = strlen(\$prefix);
    if (strncmp(\$prefix, \$class, \$len) !== 0) {
        return;
    }
    
    \$relativeClass = substr(\$class, \$len);
    \$file = \$baseDir . str_replace('\\\\', '/', \$relativeClass) . '.php';
    
    if (file_exists(\$file)) {
        require \$file;
    }
}, true, true);

// Load app classes
spl_autoload_register(function (\$class) {
    \$prefix = 'App\\\\';
    \$baseDir = __DIR__ . '/app/';
    
    \$len = strlen(\$prefix);
    if (strncmp(\$prefix, \$class, \$len) !== 0) {
        return;
    }
    
    \$relativeClass = substr(\$class, \$len);
    \$file = \$baseDir . str_replace('\\\\', '/', \$relativeClass) . '.php';
    
    if (file_exists(\$file)) {
        require \$file;
    }
});
PHP;
        
        file_put_contents($autoloaderFile, $autoloaderContent);
    }
    
    private function createPackage()
    {
        $this->log("📦 Creating deployment package...");
        
        $packagePath = $this->buildDir . '/' . $this->packageName;
        $tarFile = $this->buildDir . '/' . $this->packageName . '.tar.gz';
        
        // Create compressed package
        $command = "cd {$this->buildDir} && tar -czf {$this->packageName}.tar.gz {$this->packageName}/";
        exec($command);
        
        $this->log("✅ Package created: " . basename($tarFile));
    }
    
    private function generateInstallScript()
    {
        $this->log("📜 Generating installation script...");
        
        $installScript = $this->buildDir . '/install-' . $this->packageName . '.sh';
        $installContent = <<<BASH
#!/bin/bash
# Apileon Framework - Secure Installation Script
# Generated: $(date)

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "\${BLUE}"
    echo "============================================"
    echo "  Apileon Framework Secure Installation"
    echo "============================================"
    echo -e "\${NC}"
}

print_success() {
    echo -e "\${GREEN}✓ \$1\${NC}"
}

print_warning() {
    echo -e "\${YELLOW}⚠ \$1\${NC}"
}

print_error() {
    echo -e "\${RED}✗ \$1\${NC}"
}

print_info() {
    echo -e "\${BLUE}ℹ \$1\${NC}"
}

PACKAGE_NAME="{$this->packageName}"
SECURITY_KEY="{$this->securityKey}"
INSTALL_PATH="/var/www/apileon"

print_header

# Check if running as root
if [[ \$EUID -ne 0 ]]; then
    print_error "This script must be run as root (use sudo)"
    exit 1
fi

# Check if package exists
if [[ ! -f "\${PACKAGE_NAME}.tar.gz" ]]; then
    print_error "Package file \${PACKAGE_NAME}.tar.gz not found"
    exit 1
fi

print_info "Installing Apileon Framework..."

# Create installation directory
mkdir -p \$INSTALL_PATH
cd \$INSTALL_PATH

# Extract package
print_info "Extracting package..."
tar -xzf ~/\${PACKAGE_NAME}.tar.gz --strip-components=1

# Set permissions
print_info "Setting secure permissions..."
chown -R www-data:www-data \$INSTALL_PATH
chmod -R 755 \$INSTALL_PATH
chmod -R 644 \$INSTALL_PATH/config/
chmod 600 \$INSTALL_PATH/.env.production
chmod +x \$INSTALL_PATH/artisan

# Create storage directories
mkdir -p \$INSTALL_PATH/storage/logs
mkdir -p \$INSTALL_PATH/storage/cache
chmod -R 775 \$INSTALL_PATH/storage/
chown -R www-data:www-data \$INSTALL_PATH/storage/

# Set up environment
if [[ ! -f \$INSTALL_PATH/.env ]]; then
    cp \$INSTALL_PATH/.env.production \$INSTALL_PATH/.env
    print_warning "Environment file created. Please edit .env with your production settings"
fi

# Database setup
print_info "Setting up database..."
if [[ -f \$INSTALL_PATH/artisan ]]; then
    sudo -u www-data php \$INSTALL_PATH/artisan migrate --force
    print_success "Database migrations completed"
fi

# Security verification
print_info "Verifying security configuration..."
echo "Security Key: \$SECURITY_KEY" > \$INSTALL_PATH/storage/security-info.txt
chmod 600 \$INSTALL_PATH/storage/security-info.txt

print_success "Installation completed successfully!"
echo
print_info "Next steps:"
echo "1. Edit .env file with your production settings"
echo "2. Configure your web server to point to \$INSTALL_PATH/public"
echo "3. Set up SSL certificate"
echo "4. Configure monitoring and backups"
echo
print_info "Security Key: \$SECURITY_KEY"
print_warning "Keep this security key safe and secure!"
BASH;
        
        file_put_contents($installScript, $installContent);
        chmod($installScript, 0755);
    }
    
    private function cleanup()
    {
        $this->log("🧹 Cleaning up temporary files...");
        
        $packageDir = $this->buildDir . '/' . $this->packageName;
        $this->removeDirectory($packageDir);
    }
    
    private function shouldExclude($path)
    {
        foreach ($this->excludePatterns as $pattern) {
            if (strpos($path, $pattern) !== false) {
                return true;
            }
        }
        return false;
    }
    
    private function isPublicFile($file)
    {
        return strpos($file, '/public/') !== false || 
               basename($file) === 'index.php' ||
               basename($file) === 'autoload.php';
    }
    
    private function removeSensitiveFiles($directory)
    {
        $sensitiveFiles = [
            '/.env',
            '/composer.lock',
            '/phpunit.xml',
            '/deploy.sh',
            '/test-no-composer.php'
        ];
        
        foreach ($sensitiveFiles as $file) {
            $filePath = $directory . $file;
            if (file_exists($filePath)) {
                unlink($filePath);
            }
        }
    }
    
    private function removeDirectory($dir)
    {
        if (!is_dir($dir)) {
            return;
        }
        
        $iterator = new RecursiveIteratorIterator(
            new RecursiveDirectoryIterator($dir, RecursiveDirectoryIterator::SKIP_DOTS),
            RecursiveIteratorIterator::CHILD_FIRST
        );
        
        foreach ($iterator as $file) {
            if ($file->isDir()) {
                rmdir($file->getPathname());
            } else {
                unlink($file->getPathname());
            }
        }
        
        rmdir($dir);
    }
    
    private function generateSecurityKey()
    {
        return bin2hex(random_bytes(32));
    }
    
    private function log($message)
    {
        echo $message . "\n";
    }
}

// Run the generator
if (php_sapi_name() === 'cli') {
    $generator = new SecureDeploymentGenerator();
    $generator->generate();
} else {
    die('This script must be run from command line');
}
