<?php
/**
 * Apileon Framework - Runtime Security Validator
 * 
 * This class validates the secure deployment package integrity
 * and ensures all security measures are properly configured
 */

namespace Apileon\Security;

class RuntimeValidator
{
    private $errors = [];
    private $warnings = [];
    private $securityKey;
    private $configPath;
    
    public function __construct(string $securityKey = null)
    {
        $this->securityKey = $securityKey ?? (defined('APILEON_SECURITY_KEY') ? APILEON_SECURITY_KEY : null);
        $this->configPath = __DIR__ . '/../../deployment-config.php';
    }
    
    /**
     * Perform complete security validation
     */
    public function validate(): array
    {
        $this->errors = [];
        $this->warnings = [];
        
        // Core security checks
        $this->validateSecurityKey();
        $this->validateFilePermissions();
        $this->validateDirectoryProtection();
        $this->validateEnvironmentSecurity();
        $this->validatePhpConfiguration();
        $this->validateWebServerConfiguration();
        $this->validateDatabaseSecurity();
        $this->validateSslConfiguration();
        
        return [
            'status' => empty($this->errors) ? 'secure' : 'vulnerable',
            'errors' => $this->errors,
            'warnings' => $this->warnings,
            'timestamp' => date('c'),
            'checks_performed' => 8
        ];
    }
    
    /**
     * Validate security key
     */
    private function validateSecurityKey(): void
    {
        if (empty($this->securityKey)) {
            $this->errors[] = 'Security key not defined or empty';
            return;
        }
        
        if (strlen($this->securityKey) < 32) {
            $this->errors[] = 'Security key is too short (minimum 32 characters required)';
        }
        
        if (!defined('APILEON_SECURITY_KEY')) {
            $this->errors[] = 'APILEON_SECURITY_KEY constant not defined';
        }
    }
    
    /**
     * Validate file permissions
     */
    private function validateFilePermissions(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        // Check critical file permissions
        $criticalFiles = [
            '.env' => 0600,
            'config/database.php' => 0644,
            'storage/logs' => 0755
        ];
        
        foreach ($criticalFiles as $file => $expectedPerms) {
            $filePath = $basePath . '/' . $file;
            
            if (!file_exists($filePath)) {
                $this->warnings[] = "File not found: $file";
                continue;
            }
            
            $actualPerms = fileperms($filePath) & 0777;
            
            if ($actualPerms !== $expectedPerms) {
                $expectedOctal = decoct($expectedPerms);
                $actualOctal = decoct($actualPerms);
                $this->warnings[] = "Incorrect permissions for $file: expected $expectedOctal, got $actualOctal";
            }
        }
        
        // Check if sensitive directories are writable by web server
        $sensitiveDirs = ['src', 'app', 'config', 'database'];
        
        foreach ($sensitiveDirs as $dir) {
            $dirPath = $basePath . '/' . $dir;
            
            if (is_dir($dirPath) && is_writable($dirPath)) {
                $this->warnings[] = "Sensitive directory is writable: $dir";
            }
        }
    }
    
    /**
     * Validate directory protection
     */
    private function validateDirectoryProtection(): void
    {
        $basePath = dirname(__DIR__, 2);
        
        // Check for .htaccess files in sensitive directories
        $protectedDirs = ['src', 'app', 'config', 'database', 'storage'];
        
        foreach ($protectedDirs as $dir) {
            $dirPath = $basePath . '/' . $dir;
            $htaccessPath = $dirPath . '/.htaccess';
            
            if (is_dir($dirPath)) {
                if (!file_exists($htaccessPath)) {
                    $this->warnings[] = "Missing .htaccess protection for directory: $dir";
                } else {
                    $content = file_get_contents($htaccessPath);
                    if (strpos($content, 'Deny from all') === false) {
                        $this->warnings[] = "Weak .htaccess protection for directory: $dir";
                    }
                }
            }
        }
        
        // Check for index.php files to prevent directory listing
        foreach ($protectedDirs as $dir) {
            $dirPath = $basePath . '/' . $dir;
            $indexPath = $dirPath . '/index.php';
            
            if (is_dir($dirPath) && !file_exists($indexPath)) {
                $this->warnings[] = "Missing index.php in directory: $dir (directory listing possible)";
            }
        }
    }
    
    /**
     * Validate environment security
     */
    private function validateEnvironmentSecurity(): void
    {
        // Check PHP environment
        if (ini_get('display_errors')) {
            $this->errors[] = 'display_errors is enabled (security risk)';
        }
        
        if (ini_get('display_startup_errors')) {
            $this->warnings[] = 'display_startup_errors is enabled';
        }
        
        if (!ini_get('log_errors')) {
            $this->warnings[] = 'log_errors is disabled (debugging will be difficult)';
        }
        
        // Check dangerous functions
        $dangerousFunctions = ['exec', 'shell_exec', 'system', 'passthru', 'eval'];
        $disabledFunctions = explode(',', ini_get('disable_functions'));
        
        foreach ($dangerousFunctions as $func) {
            if (!in_array($func, $disabledFunctions) && function_exists($func)) {
                $this->warnings[] = "Dangerous function '$func' is enabled";
            }
        }
        
        // Check environment variables
        $requiredEnvVars = ['APP_ENV', 'APP_DEBUG', 'APP_KEY'];
        
        foreach ($requiredEnvVars as $var) {
            if (empty($_ENV[$var])) {
                $this->warnings[] = "Environment variable '$var' is not set";
            }
        }
        
        // Check if we're in production mode
        if (($_ENV['APP_ENV'] ?? '') !== 'production') {
            $this->warnings[] = 'Application is not in production mode';
        }
        
        if (filter_var($_ENV['APP_DEBUG'] ?? false, FILTER_VALIDATE_BOOLEAN)) {
            $this->errors[] = 'Debug mode is enabled in production';
        }
    }
    
    /**
     * Validate PHP configuration
     */
    private function validatePhpConfiguration(): void
    {
        // Check PHP version
        if (version_compare(PHP_VERSION, '8.1.0', '<')) {
            $this->warnings[] = 'PHP version is below recommended minimum (8.1.0)';
        }
        
        // Check required extensions
        $requiredExtensions = ['pdo', 'json', 'mbstring', 'openssl'];
        
        foreach ($requiredExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->errors[] = "Required PHP extension '$ext' is not loaded";
            }
        }
        
        // Check recommended extensions
        $recommendedExtensions = ['opcache', 'redis'];
        
        foreach ($recommendedExtensions as $ext) {
            if (!extension_loaded($ext)) {
                $this->warnings[] = "Recommended PHP extension '$ext' is not loaded";
            }
        }
        
        // Check OPcache configuration
        if (extension_loaded('opcache')) {
            if (!ini_get('opcache.enable')) {
                $this->warnings[] = 'OPcache is loaded but not enabled';
            }
            
            if (ini_get('opcache.validate_timestamps')) {
                $this->warnings[] = 'OPcache validate_timestamps should be disabled in production';
            }
        }
        
        // Check memory limit
        $memoryLimit = ini_get('memory_limit');
        $memoryBytes = $this->convertToBytes($memoryLimit);
        
        if ($memoryBytes < 256 * 1024 * 1024) { // 256MB
            $this->warnings[] = "PHP memory_limit is low: $memoryLimit (recommend 256M+)";
        }
    }
    
    /**
     * Validate web server configuration
     */
    private function validateWebServerConfiguration(): void
    {
        // Check server signature
        $serverSoftware = $_SERVER['SERVER_SOFTWARE'] ?? '';
        
        if (strpos($serverSoftware, 'Apache') !== false) {
            // Apache-specific checks
            if (strpos($serverSoftware, 'Apache/') !== false) {
                $this->warnings[] = 'Server signature reveals Apache version (security risk)';
            }
        }
        
        // Check security headers
        $requiredHeaders = [
            'X-Frame-Options',
            'X-XSS-Protection',
            'X-Content-Type-Options'
        ];
        
        // This would need to be checked via HTTP request in real scenario
        // For now, we'll just warn if headers_sent() is true
        if (headers_sent()) {
            $this->warnings[] = 'Headers already sent (cannot verify security headers)';
        }
        
        // Check if we're running on HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                   $_SERVER['SERVER_PORT'] == 443 ||
                   (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        
        if (!$isHttps) {
            $this->errors[] = 'Application is not running on HTTPS (security risk)';
        }
    }
    
    /**
     * Validate database security
     */
    private function validateDatabaseSecurity(): void
    {
        try {
            $dbConfig = require dirname(__DIR__, 2) . '/config/database.php';
            
            $defaultConnection = $dbConfig['default'] ?? 'mysql';
            $connectionConfig = $dbConfig['connections'][$defaultConnection] ?? [];
            
            // Check database password
            if (empty($connectionConfig['password'])) {
                $this->errors[] = 'Database password is empty (security risk)';
            } else {
                $password = $connectionConfig['password'];
                if (strlen($password) < 12) {
                    $this->warnings[] = 'Database password is short (recommend 12+ characters)';
                }
            }
            
            // Check database host
            $host = $connectionConfig['host'] ?? '';
            if ($host === '127.0.0.1' || $host === 'localhost') {
                // Local database is generally secure
            } else {
                $this->warnings[] = 'Using remote database connection (ensure it\'s encrypted)';
            }
            
            // Check database username
            $username = $connectionConfig['username'] ?? '';
            if (in_array($username, ['root', 'admin', 'sa'])) {
                $this->warnings[] = 'Using privileged database user (security risk)';
            }
            
        } catch (Exception $e) {
            $this->warnings[] = 'Could not validate database configuration: ' . $e->getMessage();
        }
    }
    
    /**
     * Validate SSL configuration
     */
    private function validateSslConfiguration(): void
    {
        if (!function_exists('openssl_get_cert_locations')) {
            $this->warnings[] = 'OpenSSL extension not available for SSL validation';
            return;
        }
        
        // Check if running on HTTPS
        $isHttps = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ||
                   $_SERVER['SERVER_PORT'] == 443 ||
                   (!empty($_SERVER['HTTP_X_FORWARDED_PROTO']) && $_SERVER['HTTP_X_FORWARDED_PROTO'] === 'https');
        
        if (!$isHttps) {
            $this->errors[] = 'SSL/HTTPS is not configured';
            return;
        }
        
        // Additional SSL checks would require certificate inspection
        // This is a basic check for HTTPS presence
    }
    
    /**
     * Convert memory limit string to bytes
     */
    private function convertToBytes(string $value): int
    {
        $value = trim($value);
        $last = strtolower($value[strlen($value) - 1]);
        $value = (int)$value;
        
        switch ($last) {
            case 'g':
                $value *= 1024;
            case 'm':
                $value *= 1024;
            case 'k':
                $value *= 1024;
        }
        
        return $value;
    }
    
    /**
     * Get validation report as JSON
     */
    public function getJsonReport(): string
    {
        $report = $this->validate();
        return json_encode($report, JSON_PRETTY_PRINT);
    }
    
    /**
     * Get validation report as HTML
     */
    public function getHtmlReport(): string
    {
        $report = $this->validate();
        
        $html = '<!DOCTYPE html>
<html>
<head>
    <title>Apileon Security Validation Report</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .header { background: #f8f9fa; padding: 20px; border-radius: 5px; }
        .status-secure { color: #28a745; }
        .status-vulnerable { color: #dc3545; }
        .errors { background: #f8d7da; border: 1px solid #f5c6cb; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .warnings { background: #fff3cd; border: 1px solid #ffeaa7; padding: 15px; margin: 10px 0; border-radius: 5px; }
        .success { background: #d4edda; border: 1px solid #c3e6cb; padding: 15px; margin: 10px 0; border-radius: 5px; }
        ul { margin: 0; padding-left: 20px; }
    </style>
</head>
<body>
    <div class="header">
        <h1>🔐 Apileon Security Validation Report</h1>
        <p><strong>Status:</strong> <span class="status-' . $report['status'] . '">' . strtoupper($report['status']) . '</span></p>
        <p><strong>Timestamp:</strong> ' . $report['timestamp'] . '</p>
        <p><strong>Checks Performed:</strong> ' . $report['checks_performed'] . '</p>
    </div>';
        
        if (!empty($report['errors'])) {
            $html .= '<div class="errors">
                <h3>🚨 Security Errors (Must Fix)</h3>
                <ul>';
            foreach ($report['errors'] as $error) {
                $html .= '<li>' . htmlspecialchars($error) . '</li>';
            }
            $html .= '</ul></div>';
        }
        
        if (!empty($report['warnings'])) {
            $html .= '<div class="warnings">
                <h3>⚠️ Security Warnings (Recommended)</h3>
                <ul>';
            foreach ($report['warnings'] as $warning) {
                $html .= '<li>' . htmlspecialchars($warning) . '</li>';
            }
            $html .= '</ul></div>';
        }
        
        if (empty($report['errors']) && empty($report['warnings'])) {
            $html .= '<div class="success">
                <h3>✅ All Security Checks Passed</h3>
                <p>Your Apileon deployment appears to be properly secured.</p>
            </div>';
        }
        
        $html .= '</body></html>';
        
        return $html;
    }
}
