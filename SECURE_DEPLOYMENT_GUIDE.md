# Apileon Framework - Secure Deployment System

## 🔐 Overview

The Apileon Secure Deployment System creates hardened, production-ready packages with enterprise-grade security features. This system transforms your development code into a secure, optimized deployment package that protects against common web vulnerabilities and unauthorized access.

## 🛡️ Security Features

### File Access Protection
- **Security-Wrapped PHP Files**: All framework files are wrapped with access control checks
- **Direct Access Prevention**: Files cannot be accessed directly without proper security validation
- **Directory Protection**: Sensitive directories are protected with .htaccess rules
- **Sensitive File Hiding**: Configuration files, logs, and documentation are excluded from web access

### Runtime Security
- **Security Key Validation**: Unique security key prevents unauthorized file execution
- **IP Access Control**: Whitelist/blacklist functionality for IP-based restrictions
- **Rate Limiting**: Built-in protection against brute force and DDoS attacks
- **Security Headers**: Comprehensive HTTP security headers automatically applied

### File System Security
- **Secure Permissions**: Automated setting of secure file and directory permissions
- **Access Logging**: All access attempts are logged for security monitoring
- **Intrusion Detection**: Automatic detection and blocking of suspicious activity

## 🚀 Usage

### Creating a Secure Package

```bash
# Generate secure deployment package
php artisan package:secure
```

This command creates:
- `build/apileon-secure-YYYY-MM-DD-HH-MM-SS.tar.gz` - Compressed secure package
- `build/install-apileon-secure-YYYY-MM-DD-HH-MM-SS.sh` - Automated installation script

### Package Contents

The secure package includes:
- **Security-wrapped application files** - All PHP files protected with access control
- **Optimized production configuration** - Minimized and optimized for performance
- **Security infrastructure** - Access control, monitoring, and protection systems
- **Installation automation** - One-command deployment script

### Installing the Secure Package

```bash
# Upload package to server
scp apileon-secure-*.tar.gz user@server:~

# SSH to server and install
ssh user@server
sudo bash install-apileon-secure-*.sh
```

## 🔧 Configuration

### Security Configuration

Edit `deployment-config.php` to customize security settings:

```php
<?php
return [
    'security' => [
        'protected_extensions' => ['php'],
        'blocked_directories' => [
            'src/', 'app/', 'config/', 'database/', 'storage/'
        ],
        'blocked_extensions' => [
            'env', 'log', 'sql', 'txt', 'md', 'json', 'lock'
        ],
        'headers' => [
            'X-Frame-Options' => 'DENY',
            'X-XSS-Protection' => '1; mode=block',
            'X-Content-Type-Options' => 'nosniff'
        ]
    ],
    'access_control' => [
        'ip_whitelist_enabled' => false,
        'allowed_ips' => [],
        'rate_limiting' => [
            'enabled' => true,
            'requests_per_minute' => 60
        ]
    ]
];
```

### Environment Security

The package includes a production-ready environment template:

```bash
# Application
APP_ENV=production
APP_DEBUG=false
APP_KEY=GENERATE_32_CHARACTER_SECRET_KEY

# Security
APILEON_SECURITY_KEY=auto-generated-64-character-key
JWT_SECRET=GENERATE_STRONG_JWT_SECRET
HASH_SALT=GENERATE_RANDOM_SALT

# Database (configure for production)
DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_DATABASE=your-production-db
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
```

## 🔍 Security Validation

### Runtime Security Checks

```bash
# Run comprehensive security validation
php artisan security:check
```

This performs 8 security checks:
1. **Security Key Validation** - Ensures proper security key configuration
2. **File Permissions** - Validates secure file and directory permissions
3. **Directory Protection** - Checks .htaccess protection for sensitive directories
4. **Environment Security** - Validates production environment configuration
5. **PHP Configuration** - Checks PHP settings for security best practices
6. **Web Server Configuration** - Validates web server security settings
7. **Database Security** - Checks database connection and credential security
8. **SSL Configuration** - Validates HTTPS and SSL setup

### Security Report

The security check generates:
- **Console output** with immediate feedback
- **HTML report** saved to `storage/security-report.html`
- **JSON report** for integration with monitoring systems

Example output:
```
🔐 Running security validation...

📊 Security Validation Report
================================
Status: SECURE
Timestamp: 2025-08-19T10:30:45+00:00
Checks Performed: 8

✅ All security checks passed!

📄 Detailed report saved to: storage/security-report.html
```

## 🏗️ How It Works

### 1. File Processing

The secure deployment system processes files as follows:

```php
// Original file
<?php
namespace App\Models;

class User extends Model
{
    // Model code here
}
```

Becomes:
```php
<?php
/**
 * Apileon Framework - Secured File
 * This file is protected against direct access
 */

// Security check - prevent direct access
if (!defined('APILEON_SECURITY_KEY') || APILEON_SECURITY_KEY !== 'unique-security-key') {
    http_response_code(403);
    die('Access Denied: Unauthorized file access attempt detected.');
}

namespace App\Models;

class User extends Model
{
    // Model code here
}
```

### 2. Access Control

The `AccessControl` class provides runtime security:

```php
<?php
// Initialize security system
\Apileon\Security\AccessControl::initialize();

// This sets up:
// - Security headers
// - IP filtering
// - File access protection
// - Security logging
```

### 3. Directory Protection

.htaccess files are created for sensitive directories:

```apache
# Block all access to sensitive directories
Order deny,allow
Deny from all

# Additional security headers
<IfModule mod_headers.c>
    Header always set X-Frame-Options "DENY"
    Header always set X-XSS-Protection "1; mode=block"
</IfModule>
```

## 📁 Package Structure

The secure package has this structure:

```
apileon-secure-YYYY-MM-DD-HH-MM-SS/
├── public/                 # Web-accessible files
│   ├── index.php          # Secured entry point
│   └── .htaccess          # Web server security rules
├── src/                   # Framework core (security-wrapped)
│   ├── Security/          # Security infrastructure
│   ├── Foundation/        # Application foundation
│   └── ...               # Other framework files
├── app/                   # Application files (security-wrapped)
│   ├── Models/           # Data models
│   ├── Controllers/      # HTTP controllers
│   └── ...              # Other app files
├── config/               # Configuration files (protected)
├── database/             # Database files (protected)
├── storage/              # Storage directory (protected)
├── .htaccess            # Root security rules
├── .env.production      # Production environment template
└── autoload.php         # Optimized autoloader
```

## 🔒 Security Layers

### Layer 1: Web Server Protection
- .htaccess rules block direct access to sensitive files
- Security headers prevent common attacks
- Directory listings disabled

### Layer 2: PHP Runtime Protection
- Security key validation before file execution
- Access control checks on every request
- IP filtering and rate limiting

### Layer 3: Application Security
- Input validation on all user data
- SQL injection prevention with prepared statements
- Mass assignment protection in models
- Secure session and cookie handling

### Layer 4: File System Protection
- Secure file permissions (644 for files, 755 for directories)
- Sensitive files have 600 permissions
- Log files and configuration protected

## 📊 Monitoring & Logging

### Security Event Logging
All security events are logged to `storage/logs/security.log`:

```
[2025-08-19 10:30:45] SECURITY ALERT: Direct file access denied - IP: 192.168.1.100 - URI: /src/Database/DatabaseManager.php
[2025-08-19 10:31:02] ACCESS DENIED: IP blocked - IP: 10.0.0.50 - URI: /app/Controllers/UserController.php
[2025-08-19 10:31:15] RATE LIMIT: Too many requests - IP: 203.0.113.10 - URI: /api/users
```

### Health Monitoring
Built-in health check endpoint provides system status:

```bash
curl https://your-api.com/health
```

Response:
```json
{
  "status": "ok",
  "timestamp": "2025-08-19T10:30:45+00:00",
  "version": "1.0.0",
  "checks": {
    "database": "ok",
    "disk_space": {
      "status": "ok",
      "used_percent": 45.2
    },
    "security": "ok"
  }
}
```

## 🚨 Security Alerts

The system can be configured to send alerts for:
- Failed authentication attempts
- Suspicious file access patterns
- Rate limit violations
- IP blocking events
- Security configuration changes

## 🔧 Customization

### Custom Security Rules

Add custom security rules in `src/Security/AccessControl.php`:

```php
<?php
// Add custom IP validation
public static function validateCustomRules(): bool
{
    $ip = self::getClientIP();
    
    // Block specific IP ranges
    if (self::isInRange($ip, '192.168.100.0/24')) {
        self::denyAccess('IP range blocked');
        return false;
    }
    
    // Block based on user agent
    $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? '';
    if (strpos($userAgent, 'BadBot') !== false) {
        self::denyAccess('Malicious user agent');
        return false;
    }
    
    return true;
}
```

### Custom File Protection

Extend the security wrapper for specific file types:

```php
<?php
// Custom protection for API files
private function wrapApiFile($content): string
{
    $apiWrapper = <<<PHP
<?php
// API-specific security checks
if (!\$_SERVER['HTTP_API_KEY'] || !\$this->validateApiKey(\$_SERVER['HTTP_API_KEY'])) {
    http_response_code(401);
    die('Invalid API key');
}

PHP;
    
    return $apiWrapper . $content;
}
```

## 📋 Best Practices

### 1. Regular Security Updates
- Run `php artisan security:check` regularly
- Monitor security logs for unusual activity
- Keep server software updated

### 2. Environment Security
- Use strong, unique passwords for all accounts
- Enable two-factor authentication where possible
- Regularly rotate API keys and secrets

### 3. Network Security
- Use HTTPS only in production
- Configure firewall rules appropriately
- Consider using a CDN for additional protection

### 4. Backup & Recovery
- Regular automated backups
- Test backup restoration procedures
- Store backups securely off-site

## 🚀 Advanced Features

### Multi-Environment Deployment
Create packages for different environments:

```php
<?php
// Configure for staging environment
$config['environment']['staging'] = [
    'APP_ENV' => 'staging',
    'APP_DEBUG' => false,
    'LOG_LEVEL' => 'warning'
];
```

### Auto-Update Security
Implement automatic security updates:

```bash
# Schedule regular security checks
0 2 * * * /var/www/apileon/artisan security:check --auto-fix
```

### Integration with Security Tools
- **Fail2Ban**: Automatic IP blocking based on logs
- **ModSecurity**: Web application firewall integration
- **OSSEC**: Host-based intrusion detection

## 📞 Support & Troubleshooting

### Common Issues

1. **"Access Denied" errors**: Check security key configuration
2. **File permission errors**: Ensure correct ownership and permissions
3. **Database connection failures**: Validate database credentials in .env

### Debug Mode
For troubleshooting, temporarily enable debug mode:

```bash
# In .env file
APP_DEBUG=true
SECURITY_DEBUG=true
```

**⚠️ Never leave debug mode enabled in production!**

### Security Incident Response
If a security incident is detected:
1. Immediately block the attacking IP
2. Review security logs for breach extent
3. Update security keys if compromised
4. Patch any discovered vulnerabilities
5. Notify relevant stakeholders

---

Your Apileon framework now has enterprise-grade security with comprehensive protection against common web vulnerabilities! 🔐✨
