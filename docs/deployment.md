# Deployment Guide

This guide covers deploying Apileon applications to various environments, from shared hosting to enterprise cloud platforms.

## Table of Contents

- [Quick Deployment](#quick-deployment)
- [Environment Configuration](#environment-configuration)
- [Web Server Configuration](#web-server-configuration)
- [Shared Hosting](#shared-hosting)
- [VPS/Dedicated Servers](#vpsdedicated-servers)
- [Cloud Platforms](#cloud-platforms)
- [Docker Deployment](#docker-deployment)
- [Performance Optimization](#performance-optimization)
- [Security Considerations](#security-considerations)
- [Monitoring and Logging](#monitoring-and-logging)
- [Troubleshooting](#troubleshooting)

## Quick Deployment

### Minimum Requirements
- **PHP**: 8.1 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Memory**: 128MB minimum (512MB recommended)
- **Disk Space**: 50MB for framework + your application code

### Basic Deployment Steps

1. **Upload your application**
2. **Configure environment** (`.env` file)
3. **Set file permissions**
4. **Configure web server** (document root to `public/`)
5. **Test the deployment**

## Environment Configuration

### Production Environment File

Create a `.env` file in your project root:

```env
# Application
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com
APP_TIMEZONE=UTC

# Security
APP_KEY=your-32-character-secret-key-here

# Database (if using)
DB_HOST=localhost
DB_PORT=3306
DB_DATABASE=your_database
DB_USERNAME=your_username
DB_PASSWORD=your_secure_password

# Cache
CACHE_DRIVER=file
CACHE_TTL=3600

# Rate Limiting
RATE_LIMIT_ENABLED=true
RATE_LIMIT_MAX_REQUESTS=100
RATE_LIMIT_WINDOW=3600

# CORS
CORS_ALLOWED_ORIGINS=https://yourdomain.com,https://app.yourdomain.com
CORS_ALLOWED_METHODS=GET,POST,PUT,DELETE,OPTIONS
CORS_ALLOWED_HEADERS=Content-Type,Authorization,X-Requested-With

# Logging
LOG_LEVEL=error
LOG_PATH=/var/log/apileon/
```

### Environment Security

```bash
# Set secure permissions for .env file
chmod 600 .env
chown www-data:www-data .env  # On Ubuntu/Debian
# or
chown apache:apache .env      # On CentOS/RHEL
```

## Web Server Configuration

### Apache Configuration

#### Option 1: Virtual Host Configuration

Create `/etc/apache2/sites-available/your-api.conf`:

```apache
<VirtualHost *:80>
    ServerName api.yourdomain.com
    DocumentRoot /var/www/your-api/public
    
    <Directory /var/www/your-api/public>
        AllowOverride All
        Require all granted
        
        # Enable rewrite engine
        RewriteEngine On
        
        # Handle Angular/Vue.js/React routing
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
    
    # Security headers
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Strict-Transport-Security "max-age=31536000; includeSubDomains"
    
    # Hide Apache version
    ServerTokens Prod
    
    # Logging
    ErrorLog ${APACHE_LOG_DIR}/your-api-error.log
    CustomLog ${APACHE_LOG_DIR}/your-api-access.log combined
</VirtualHost>

# HTTPS Configuration (recommended)
<VirtualHost *:443>
    ServerName api.yourdomain.com
    DocumentRoot /var/www/your-api/public
    
    # SSL Configuration
    SSLEngine on
    SSLCertificateFile /path/to/your/certificate.crt
    SSLCertificateKeyFile /path/to/your/private.key
    SSLCertificateChainFile /path/to/your/chain.crt
    
    # Modern SSL configuration
    SSLProtocol all -SSLv3 -TLSv1 -TLSv1.1
    SSLCipherSuite ECDHE-ECDSA-AES128-GCM-SHA256:ECDHE-RSA-AES128-GCM-SHA256
    SSLHonorCipherOrder off
    SSLSessionTickets off
    
    <Directory /var/www/your-api/public>
        AllowOverride All
        Require all granted
        
        RewriteEngine On
        RewriteCond %{REQUEST_FILENAME} !-f
        RewriteCond %{REQUEST_FILENAME} !-d
        RewriteRule ^(.*)$ index.php [QSA,L]
    </Directory>
</VirtualHost>
```

#### Option 2: .htaccess Configuration

If you can't modify Apache configuration, create `public/.htaccess`:

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    
    # Force HTTPS (optional)
    RewriteCond %{HTTPS} off
    RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]
    
    # Handle API routing
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^(.*)$ index.php [QSA,L]
</IfModule>

# Security headers
<IfModule mod_headers.c>
    Header always set X-Content-Type-Options nosniff
    Header always set X-Frame-Options DENY
    Header always set X-XSS-Protection "1; mode=block"
    Header always set Referrer-Policy "strict-origin-when-cross-origin"
</IfModule>

# Deny access to sensitive files
<Files ".env">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.json">
    Order allow,deny
    Deny from all
</Files>

<Files "composer.lock">
    Order allow,deny
    Deny from all
</Files>

# Cache static assets
<IfModule mod_expires.c>
    ExpiresActive on
    ExpiresByType text/css "access plus 1 year"
    ExpiresByType application/javascript "access plus 1 year"
    ExpiresByType image/png "access plus 1 year"
    ExpiresByType image/jpg "access plus 1 year"
    ExpiresByType image/jpeg "access plus 1 year"
</IfModule>
```

Enable the site:
```bash
sudo a2ensite your-api.conf
sudo a2enmod rewrite headers ssl
sudo systemctl reload apache2
```

### Nginx Configuration

Create `/etc/nginx/sites-available/your-api`:

```nginx
server {
    listen 80;
    server_name api.yourdomain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name api.yourdomain.com;
    
    root /var/www/your-api/public;
    index index.php;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Strict-Transport-Security "max-age=31536000; includeSubDomains" always;
    
    # Main location block
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP handling
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;  # Adjust PHP version
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        
        # Security
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\.(env|git|htaccess) {
        deny all;
        return 404;
    }
    
    location ~ /(composer\.(json|lock)|package\.json)$ {
        deny all;
        return 404;
    }
    
    # Static files caching
    location ~* \.(css|js|png|jpg|jpeg|gif|ico|svg)$ {
        expires 1y;
        add_header Cache-Control "public, immutable";
        access_log off;
    }
    
    # Gzip compression
    gzip on;
    gzip_vary on;
    gzip_min_length 1024;
    gzip_types
        text/plain
        text/css
        text/xml
        text/javascript
        application/javascript
        application/xml+rss
        application/json;
}
```

Enable the configuration:
```bash
sudo ln -s /etc/nginx/sites-available/your-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl reload nginx
```

## Shared Hosting

### File Upload Strategy

Most shared hosting providers don't give you access to server configuration, so you'll upload files via FTP/SFTP.

#### Directory Structure
```
public_html/  (or www/, htdocs/)
├── api/                    # Your API subdirectory
│   ├── index.php          # Entry point
│   ├── .htaccess          # Apache configuration
│   └── assets/            # Static files (if any)
├── apileon-app/           # Your application (outside web root)
│   ├── .env
│   ├── app/
│   ├── config/
│   ├── src/
│   ├── routes/
│   └── autoload.php
└── other-website-files/
```

#### Modified Entry Point

Create `public_html/api/index.php`:

```php
<?php
declare(strict_types=1);

// Adjust the path to your application
$appPath = dirname(__DIR__) . '/apileon-app';

// Load environment configuration
if (file_exists($appPath . '/.env')) {
    $lines = file($appPath . '/.env', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos($line, '=') !== false && $line[0] !== '#') {
            list($key, $value) = explode('=', $line, 2);
            $_ENV[trim($key)] = trim($value);
        }
    }
}

// Determine autoloader
if (file_exists($appPath . '/vendor/autoload.php')) {
    require_once $appPath . '/vendor/autoload.php';
} else {
    require_once $appPath . '/autoload.php';
}

// Start the application
$app = new \Apileon\Foundation\Application($appPath);
$app->loadRoutes($appPath . '/routes/api.php');

// Handle the request
$request = \Apileon\Http\Request::createFromGlobals();
$response = $app->handle($request);
$response->send();
```

#### Shared Hosting .htaccess

Create `public_html/api/.htaccess`:

```apache
# For shared hosting with limited configuration access
RewriteEngine On

# Force HTTPS if available
RewriteCond %{HTTPS} off
RewriteCond %{HTTP:X-Forwarded-Proto} !https
RewriteRule ^(.*)$ https://%{HTTP_HOST}%{REQUEST_URI} [L,R=301]

# API routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Security - deny access to application directory
RewriteRule ^apileon-app/ - [F,L]

# Deny access to sensitive files
<FilesMatch "\.(env|log|md)$">
    Order allow,deny
    Deny from all
</FilesMatch>

# Set proper content types
AddType application/json .json
AddType text/plain .txt
AddType text/csv .csv

# Compress output if supported
<IfModule mod_deflate.c>
    AddOutputFilterByType DEFLATE text/plain
    AddOutputFilterByType DEFLATE text/html
    AddOutputFilterByType DEFLATE text/xml
    AddOutputFilterByType DEFLATE text/css
    AddOutputFilterByType DEFLATE application/xml
    AddOutputFilterByType DEFLATE application/xhtml+xml
    AddOutputFilterByType DEFLATE application/rss+xml
    AddOutputFilterByType DEFLATE application/javascript
    AddOutputFilterByType DEFLATE application/x-javascript
    AddOutputFilterByType DEFLATE application/json
</IfModule>
```

### Common Shared Hosting Issues

#### PHP Version
```bash
# Check PHP version in a test file
<?php phpinfo(); ?>
```

Many shared hosts run multiple PHP versions. Look for:
- `.htaccess` PHP version selection
- Control panel PHP version settings
- Use `AddHandler` directive if needed

#### Memory Limits
```php
# In your bootstrap or .htaccess
ini_set('memory_limit', '256M');
```

#### File Permissions
```bash
# Set permissions via FTP client or control panel
# Directories: 755
# Files: 644
# .env file: 600 (if supported)
```

## VPS/Dedicated Servers

### Ubuntu/Debian Setup

#### Install Required Packages
```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1 and extensions
sudo apt install -y php8.1 php8.1-fpm php8.1-mysql php8.1-pgsql \
    php8.1-curl php8.1-json php8.1-mbstring php8.1-xml \
    php8.1-zip php8.1-gd php8.1-intl

# Install web server (choose one)
sudo apt install -y apache2    # Apache
# OR
sudo apt install -y nginx      # Nginx

# Install additional tools
sudo apt install -y git curl unzip
```

#### Deploy Application
```bash
# Clone your repository
cd /var/www
sudo git clone https://github.com/username/your-api.git
sudo chown -R www-data:www-data your-api

# Setup environment
cd your-api
sudo -u www-data cp .env.example .env
sudo -u www-data nano .env  # Configure your environment

# Install dependencies (if using Composer)
sudo -u www-data composer install --no-dev --optimize-autoloader

# Set permissions
sudo chmod -R 755 your-api
sudo chmod 600 .env
sudo chmod -R 775 storage/logs  # If you have log directory
```

### CentOS/RHEL Setup

#### Install Required Packages
```bash
# Install EPEL repository
sudo dnf install -y epel-release

# Install Remi repository for PHP 8.1
sudo dnf install -y https://rpms.remirepo.net/enterprise/remi-release-8.rpm
sudo dnf module reset php
sudo dnf module enable php:remi-8.1

# Install PHP and extensions
sudo dnf install -y php php-fpm php-mysql php-pgsql \
    php-curl php-json php-mbstring php-xml \
    php-zip php-gd php-intl

# Install web server
sudo dnf install -y httpd      # Apache
# OR
sudo dnf install -y nginx      # Nginx

# Start and enable services
sudo systemctl start php-fpm
sudo systemctl enable php-fpm
sudo systemctl start httpd     # or nginx
sudo systemctl enable httpd    # or nginx
```

## Cloud Platforms

### DigitalOcean App Platform

Create `app.yaml`:

```yaml
name: your-api
services:
- name: api
  source_dir: /
  github:
    repo: username/your-api
    branch: main
  run_command: php -S 0.0.0.0:8080 -t public
  environment_slug: php
  instance_count: 1
  instance_size_slug: basic-xxs
  http_port: 8080
  
  envs:
  - key: APP_ENV
    value: production
  - key: APP_DEBUG
    value: "false"
  - key: DATABASE_URL
    type: secret
    value: your-database-connection-string
```

### AWS Elastic Beanstalk

Create `.ebextensions/01-php.config`:

```yaml
option_settings:
  aws:elasticbeanstalk:container:php:phpini:
    document_root: /public
    memory_limit: 256M
    max_execution_time: 60
    
  aws:elasticbeanstalk:environment:proxy:staticfiles:
    /assets: public/assets
```

### Google Cloud Run

Create `Dockerfile`:

```dockerfile
FROM php:8.1-apache

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Copy application
COPY . /var/www/html/

# Configure Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html

EXPOSE 80
```

Create `cloudbuild.yaml`:

```yaml
steps:
- name: 'gcr.io/cloud-builders/docker'
  args: ['build', '-t', 'gcr.io/$PROJECT_ID/your-api', '.']
- name: 'gcr.io/cloud-builders/docker'
  args: ['push', 'gcr.io/$PROJECT_ID/your-api']
- name: 'gcr.io/cloud-builders/gcloud'
  args: ['run', 'deploy', 'your-api', '--image', 'gcr.io/$PROJECT_ID/your-api', '--platform', 'managed', '--region', 'us-central1']
```

### Heroku

Create `Procfile`:

```
web: vendor/bin/heroku-php-apache2 public/
```

Create `composer.json` (if not using Composer for development):

```json
{
    "require": {
        "php": "^8.1"
    }
}
```

Deploy:
```bash
heroku create your-api-name
heroku config:set APP_ENV=production
heroku config:set APP_DEBUG=false
git push heroku main
```

## Docker Deployment

### Basic Dockerfile

```dockerfile
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache nginx supervisor curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql

# Create application user
RUN addgroup -g 1000 appuser && adduser -u 1000 -G appuser -s /bin/sh -D appuser

# Copy application
COPY --chown=appuser:appuser . /var/www/html

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/supervisor.conf /etc/supervisor/conf.d/supervisord.conf

# Create necessary directories
RUN mkdir -p /var/log/nginx /var/log/supervisor /run/nginx

# Set permissions
RUN chown -R appuser:appuser /var/www/html /var/log/nginx /var/log/supervisor /run/nginx

# Switch to application user
USER appuser

# Expose port
EXPOSE 8080

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### Docker Compose

Create `docker-compose.yml`:

```yaml
version: '3.8'

services:
  app:
    build: .
    ports:
      - "8080:8080"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage/logs:/var/www/html/storage/logs
    depends_on:
      - database
    restart: unless-stopped

  database:
    image: mysql:8.0
    environment:
      - MYSQL_ROOT_PASSWORD=rootpassword
      - MYSQL_DATABASE=apileon
      - MYSQL_USER=apileon
      - MYSQL_PASSWORD=password
    volumes:
      - mysql_data:/var/lib/mysql
    restart: unless-stopped

  nginx:
    image: nginx:alpine
    ports:
      - "80:80"
      - "443:443"
    volumes:
      - ./docker/nginx.conf:/etc/nginx/nginx.conf
      - ./docker/ssl:/etc/nginx/ssl
    depends_on:
      - app
    restart: unless-stopped

volumes:
  mysql_data:
```

## Performance Optimization

### PHP Configuration

Optimize `php.ini`:

```ini
; Performance settings
opcache.enable=1
opcache.enable_cli=1
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.validate_timestamps=0  ; Only in production
opcache.save_comments=1
opcache.fast_shutdown=1

; Memory and execution
memory_limit=256M
max_execution_time=30
max_input_time=30

; File uploads
upload_max_filesize=10M
post_max_size=10M
max_file_uploads=20

; Session (if using)
session.save_handler=files
session.gc_maxlifetime=3600

; Error reporting (production)
display_errors=Off
display_startup_errors=Off
log_errors=On
error_log=/var/log/php/error.log
```

### Application-Level Optimizations

#### Response Caching Middleware

```php
class CacheMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        $cacheKey = 'route_' . md5($request->uri());
        $cached = $this->getFromCache($cacheKey);
        
        if ($cached) {
            return Response::json($cached)
                ->header('X-Cache', 'HIT');
        }
        
        $response = $next($request);
        
        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $this->putInCache($cacheKey, json_decode($response->getContent(), true), 300);
            $response->header('X-Cache', 'MISS');
        }
        
        return $response;
    }
    
    private function getFromCache(string $key)
    {
        $file = sys_get_temp_dir() . '/cache_' . $key;
        if (file_exists($file) && (time() - filemtime($file)) < 300) {
            return json_decode(file_get_contents($file), true);
        }
        return null;
    }
    
    private function putInCache(string $key, array $data, int $ttl): void
    {
        $file = sys_get_temp_dir() . '/cache_' . $key;
        file_put_contents($file, json_encode($data));
    }
}
```

#### Database Connection Pooling

```php
class DatabasePool
{
    private static array $connections = [];
    private static int $maxConnections = 5;
    
    public static function getConnection(): PDO
    {
        if (count(self::$connections) < self::$maxConnections) {
            $pdo = new PDO(
                'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE'),
                env('DB_USERNAME'),
                env('DB_PASSWORD'),
                [
                    PDO::ATTR_PERSISTENT => true,
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES utf8mb4"
                ]
            );
            
            self::$connections[] = $pdo;
        }
        
        return end(self::$connections);
    }
}
```

## Security Considerations

### SSL/TLS Configuration

#### Let's Encrypt with Certbot

```bash
# Install Certbot
sudo apt install -y certbot python3-certbot-apache  # For Apache
# OR
sudo apt install -y certbot python3-certbot-nginx   # For Nginx

# Get certificate
sudo certbot --apache -d api.yourdomain.com         # For Apache
# OR
sudo certbot --nginx -d api.yourdomain.com          # For Nginx

# Auto-renewal
sudo crontab -e
# Add: 0 12 * * * /usr/bin/certbot renew --quiet
```

### Security Headers Middleware

```php
class SecurityHeadersMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        
        return $response
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Referrer-Policy', 'strict-origin-when-cross-origin')
            ->header('Content-Security-Policy', "default-src 'self'")
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->header('Permissions-Policy', 'geolocation=(), microphone=(), camera=()');
    }
}
```

### API Rate Limiting

```php
class RateLimitMiddleware extends Middleware
{
    private int $maxRequests = 100;
    private int $windowInSeconds = 3600;
    
    public function handle(Request $request, callable $next): Response
    {
        $clientIp = $request->getClientIp();
        $key = 'rate_limit_' . md5($clientIp);
        
        $requests = $this->getRequestCount($key);
        
        if ($requests >= $this->maxRequests) {
            return Response::json([
                'error' => 'Rate limit exceeded',
                'retry_after' => $this->windowInSeconds
            ], 429)
            ->header('Retry-After', (string)$this->windowInSeconds);
        }
        
        $this->incrementRequestCount($key);
        
        return $next($request)
            ->header('X-RateLimit-Limit', (string)$this->maxRequests)
            ->header('X-RateLimit-Remaining', (string)($this->maxRequests - $requests - 1));
    }
}
```

### Input Validation

```php
class InputValidationMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $contentType = $request->header('Content-Type');
        
        // Validate JSON payloads
        if (strpos($contentType, 'application/json') !== false) {
            $body = $request->getContent();
            if (!empty($body) && json_decode($body) === null) {
                return Response::json(['error' => 'Invalid JSON payload'], 400);
            }
        }
        
        // Validate request size
        $contentLength = $request->header('Content-Length');
        if ($contentLength && (int)$contentLength > 10 * 1024 * 1024) { // 10MB
            return Response::json(['error' => 'Request too large'], 413);
        }
        
        return $next($request);
    }
}
```

## Monitoring and Logging

### Application Logging

```php
class Logger
{
    private string $logPath;
    
    public function __construct()
    {
        $this->logPath = env('LOG_PATH', '/var/log/apileon/');
        if (!is_dir($this->logPath)) {
            mkdir($this->logPath, 0755, true);
        }
    }
    
    public function info(string $message, array $context = []): void
    {
        $this->log('INFO', $message, $context);
    }
    
    public function error(string $message, array $context = []): void
    {
        $this->log('ERROR', $message, $context);
    }
    
    public function warning(string $message, array $context = []): void
    {
        $this->log('WARNING', $message, $context);
    }
    
    private function log(string $level, string $message, array $context): void
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = !empty($context) ? ' ' . json_encode($context) : '';
        $logLine = "[{$timestamp}] {$level}: {$message}{$contextStr}" . PHP_EOL;
        
        file_put_contents(
            $this->logPath . 'apileon-' . date('Y-m-d') . '.log',
            $logLine,
            FILE_APPEND | LOCK_EX
        );
    }
}
```

### Health Check Endpoint

```php
Route::get('/health', function() {
    $status = [
        'status' => 'healthy',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'checks' => []
    ];
    
    // Check database connectivity
    try {
        $pdo = new PDO(/* your database config */);
        $status['checks']['database'] = 'healthy';
    } catch (Exception $e) {
        $status['checks']['database'] = 'unhealthy';
        $status['status'] = 'unhealthy';
    }
    
    // Check disk space
    $freeBytes = disk_free_space('/');
    $totalBytes = disk_total_space('/');
    $freePercentage = ($freeBytes / $totalBytes) * 100;
    
    if ($freePercentage < 10) {
        $status['checks']['disk_space'] = 'warning';
    } else {
        $status['checks']['disk_space'] = 'healthy';
    }
    
    // Check memory usage
    $memoryUsage = memory_get_usage(true);
    $memoryLimit = ini_get('memory_limit');
    $status['checks']['memory'] = [
        'usage' => $memoryUsage,
        'limit' => $memoryLimit
    ];
    
    $httpCode = $status['status'] === 'healthy' ? 200 : 503;
    return Response::json($status, $httpCode);
});
```

### Performance Monitoring

```php
class PerformanceMonitoringMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        $startMemory = memory_get_usage(true);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $endMemory = memory_get_usage(true);
        
        $duration = ($endTime - $startTime) * 1000; // Convert to milliseconds
        $memoryUsed = $endMemory - $startMemory;
        
        // Log slow requests
        if ($duration > 1000) { // Slower than 1 second
            error_log("Slow request: {$request->uri()} took {$duration}ms");
        }
        
        // Add performance headers
        $response
            ->header('X-Response-Time', number_format($duration, 2) . 'ms')
            ->header('X-Memory-Usage', number_format($memoryUsed / 1024, 2) . 'KB');
        
        return $response;
    }
}
```

## Troubleshooting

### Common Issues

#### 1. White Screen of Death
```bash
# Enable error reporting
echo "display_errors = On" >> /path/to/php.ini
echo "error_reporting = E_ALL" >> /path/to/php.ini

# Check error logs
tail -f /var/log/apache2/error.log    # Apache
tail -f /var/log/nginx/error.log      # Nginx
tail -f /var/log/php/error.log        # PHP-FPM
```

#### 2. 404 Errors for Routes
```bash
# Check Apache mod_rewrite
sudo a2enmod rewrite
sudo systemctl restart apache2

# Check Nginx try_files
# Make sure: try_files $uri $uri/ /index.php?$query_string;

# Verify .htaccess permissions
chmod 644 public/.htaccess
```

#### 3. Permission Denied Errors
```bash
# Fix ownership
sudo chown -R www-data:www-data /var/www/your-api

# Fix permissions
sudo chmod -R 755 /var/www/your-api
sudo chmod 600 /var/www/your-api/.env
sudo chmod -R 775 /var/www/your-api/storage  # If applicable
```

#### 4. CORS Issues
```php
# Add CORS middleware to all routes
Route::group(['middleware' => ['cors']], function() {
    // All your routes here
});

# Or configure web server level CORS
# Apache:
Header always set Access-Control-Allow-Origin "*"
Header always set Access-Control-Allow-Methods "GET, POST, PUT, DELETE, OPTIONS"
Header always set Access-Control-Allow-Headers "Content-Type, Authorization"
```

#### 5. SSL Certificate Issues
```bash
# Check certificate validity
openssl x509 -in /path/to/certificate.crt -text -noout

# Test SSL configuration
curl -I https://api.yourdomain.com

# Check certificate chain
openssl s_client -connect api.yourdomain.com:443 -servername api.yourdomain.com
```

### Debug Tools

#### Request/Response Logging
```php
class DebugMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Log request
        error_log("REQUEST: " . json_encode([
            'method' => $request->method(),
            'uri' => $request->uri(),
            'headers' => $request->headers(),
            'body' => $request->getContent()
        ]));
        
        $response = $next($request);
        
        // Log response
        error_log("RESPONSE: " . json_encode([
            'status' => $response->getStatusCode(),
            'headers' => $response->getHeaders(),
            'body' => $response->getContent()
        ]));
        
        return $response;
    }
}
```

#### Performance Profiling
```bash
# Install Xdebug for development
sudo apt install php8.1-xdebug

# Configure profiling
echo "xdebug.mode=profile" >> /etc/php/8.1/fpm/conf.d/20-xdebug.ini
echo "xdebug.output_dir=/tmp" >> /etc/php/8.1/fpm/conf.d/20-xdebug.ini

# Analyze with tools like KCacheGrind or Webgrind
```

### Deployment Checklist

#### Pre-deployment
- [ ] Update `.env` for production environment
- [ ] Set `APP_DEBUG=false`
- [ ] Configure proper error logging
- [ ] Test all endpoints
- [ ] Run security scan
- [ ] Backup current deployment (if updating)
- [ ] Test SSL certificate

#### Post-deployment
- [ ] Verify all routes work
- [ ] Check error logs for issues
- [ ] Test performance (response times)
- [ ] Verify security headers
- [ ] Test rate limiting
- [ ] Monitor resource usage
- [ ] Set up monitoring/alerting

## Support

For deployment-specific issues:

1. **Check logs first** - Most issues leave traces in error logs
2. **Verify configuration** - Double-check web server and PHP configuration
3. **Test locally** - Ensure the application works in a local environment
4. **Use staging environment** - Test deployments in a staging environment first
5. **Monitor performance** - Set up monitoring from day one

For additional help:
- Check the [FAQ](docs/FAQ.md)
- Review [GitHub Issues](https://github.com/username/apileon/issues)
- Join the community discussions

Happy deploying! 🚀
