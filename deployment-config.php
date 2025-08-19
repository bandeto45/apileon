<?php
/**
 * Apileon Framework - Secure Deployment Configuration
 * 
 * This configuration file defines security settings and 
 * deployment options for the secure package generator
 */

return [
    // Security Configuration
    'security' => [
        // Files that require security wrapping
        'protected_extensions' => ['php'],
        
        // Files that should NOT be security wrapped (public access)
        'public_files' => [
            'public/index.php',
            'autoload.php'
        ],
        
        // Directories that should be completely blocked from web access
        'blocked_directories' => [
            'src/',
            'app/',
            'config/',
            'database/',
            'storage/',
            'vendor/',
            'tests/',
            'docs/'
        ],
        
        // File extensions that should be blocked from web access
        'blocked_extensions' => [
            'env', 'log', 'sql', 'txt', 'md', 'json', 'lock', 'sh', 'yml', 'yaml'
        ],
        
        // Security headers to be applied
        'headers' => [
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff',
            'Referrer-Policy' => 'strict-origin-when-cross-origin',
            'Content-Security-Policy' => "default-src 'self'",
            'Strict-Transport-Security' => 'max-age=31536000; includeSubDomains'
        ]
    ],
    
    // Deployment Configuration
    'deployment' => [
        // Files/directories to exclude from deployment package
        'exclude_patterns' => [
            '.git/',
            'node_modules/',
            'tests/',
            'docs/',
            'build/',
            '.env',
            'composer.lock',
            'phpunit.xml',
            'deploy.sh',
            'test-no-composer.php',
            'setup-no-composer.sh',
            'status.sh',
            '.gitignore',
            'README.md',
            'DEPLOYMENT_GUIDE.md',
            'DATABASE_CRUD_GUIDE.md',
            'IMPLEMENTATION_SUMMARY.md',
            'secure-deploy.php',
            'deployment-config.php'
        ],
        
        // Files to obfuscate (remove comments, minimize)
        'obfuscate_files' => true,
        
        // Create compressed package
        'create_archive' => true,
        
        // Generate installation script
        'generate_installer' => true,
        
        // Package naming
        'package_prefix' => 'apileon-secure',
        'include_timestamp' => true
    ],
    
    // Production Optimizations
    'optimizations' => [
        // Remove PHP comments (except security headers)
        'strip_comments' => true,
        
        // Remove extra whitespace
        'minimize_whitespace' => true,
        
        // Create optimized class map
        'optimize_autoloader' => true,
        
        // Disable debugging features
        'disable_debug' => true,
        
        // Enable production error handling
        'production_errors' => true
    ],
    
    // Access Control
    'access_control' => [
        // Enable IP whitelisting
        'ip_whitelist_enabled' => false,
        'allowed_ips' => [
            // '192.168.1.100',
            // '10.0.0.50'
        ],
        
        // Enable IP blacklisting
        'ip_blacklist_enabled' => true,
        'blocked_ips' => [
            // '192.168.1.200'
        ],
        
        // Rate limiting per IP
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60,
            'burst_limit' => 100
        ],
        
        // Geographic restrictions
        'geo_blocking' => [
            'enabled' => false,
            'allowed_countries' => [], // ISO country codes
            'blocked_countries' => []  // ISO country codes
        ]
    ],
    
    // Monitoring & Logging
    'monitoring' => [
        // Security event logging
        'security_logging' => true,
        'log_file' => 'storage/logs/security.log',
        
        // Access logging
        'access_logging' => true,
        'access_log_file' => 'storage/logs/access.log',
        
        // Error logging
        'error_logging' => true,
        'error_log_file' => 'storage/logs/error.log',
        
        // Health check endpoint
        'health_check_enabled' => true,
        'health_check_endpoint' => '/health',
        
        // Metrics collection
        'metrics_enabled' => true,
        'metrics_endpoint' => '/metrics'
    ],
    
    // Environment Configuration
    'environment' => [
        // Production environment settings
        'production' => [
            'APP_ENV' => 'production',
            'APP_DEBUG' => false,
            'LOG_LEVEL' => 'error',
            'CACHE_DRIVER' => 'redis',
            'SESSION_DRIVER' => 'redis'
        ],
        
        // Staging environment settings
        'staging' => [
            'APP_ENV' => 'staging',
            'APP_DEBUG' => false,
            'LOG_LEVEL' => 'warning',
            'CACHE_DRIVER' => 'file',
            'SESSION_DRIVER' => 'file'
        ]
    ],
    
    // Database Security
    'database' => [
        // Connection encryption
        'encrypt_connection' => true,
        
        // Query logging (disable in production)
        'log_queries' => false,
        
        // Connection pooling
        'connection_pooling' => true,
        'max_connections' => 100,
        
        // Backup configuration
        'backup_enabled' => true,
        'backup_schedule' => 'daily',
        'backup_retention_days' => 30
    ],
    
    // File System Security
    'filesystem' => [
        // File upload restrictions
        'upload_restrictions' => [
            'max_file_size' => '10MB',
            'allowed_extensions' => ['jpg', 'jpeg', 'png', 'gif', 'pdf'],
            'scan_for_malware' => true
        ],
        
        // File permissions
        'secure_permissions' => [
            'files' => 0644,
            'directories' => 0755,
            'sensitive_files' => 0600
        ],
        
        // Temporary file cleanup
        'cleanup_temp_files' => true,
        'temp_file_lifetime' => 3600 // 1 hour
    ],
    
    // Encryption Settings
    'encryption' => [
        // Session encryption
        'encrypt_sessions' => true,
        
        // Cookie encryption
        'encrypt_cookies' => true,
        
        // Database field encryption
        'encrypt_sensitive_fields' => true,
        
        // File encryption
        'encrypt_uploaded_files' => false
    ],
    
    // Performance Settings
    'performance' => [
        // Caching
        'cache_enabled' => true,
        'cache_ttl' => 3600,
        
        // Compression
        'gzip_compression' => true,
        
        // CDN settings
        'cdn_enabled' => false,
        'cdn_url' => '',
        
        // Asset optimization
        'minify_assets' => true,
        'combine_assets' => true
    ]
];
