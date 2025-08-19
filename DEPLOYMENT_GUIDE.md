# Apileon Framework - Production Deployment Guide

## 🚀 Production Deployment Overview

This guide covers deploying your Apileon API to production environments with security, performance, and scalability best practices.

## 📋 Pre-Deployment Checklist

### ✅ Environment Configuration
- [ ] Production `.env` file configured
- [ ] Database credentials secured
- [ ] API keys and secrets generated
- [ ] Debug mode disabled (`APP_DEBUG=false`)
- [ ] Error logging configured
- [ ] HTTPS enabled

### ✅ Security Hardening
- [ ] Strong passwords and API keys
- [ ] File permissions set correctly
- [ ] Sensitive files protected
- [ ] Rate limiting configured
- [ ] CORS properly configured
- [ ] Input validation implemented

### ✅ Performance Optimization
- [ ] PHP OPcache enabled
- [ ] Database indexes optimized
- [ ] Caching strategy implemented
- [ ] Static assets optimized
- [ ] CDN configured (if needed)

### ✅ Monitoring & Logging
- [ ] Error monitoring setup
- [ ] Application logging configured
- [ ] Health checks implemented
- [ ] Backup strategy in place

---

## 🔧 Server Requirements

### Minimum Requirements
- **PHP**: 8.1 or higher
- **Web Server**: Apache 2.4+ or Nginx 1.18+
- **Database**: MySQL 8.0+, PostgreSQL 13+, or SQLite 3.35+
- **Memory**: 512MB RAM minimum (2GB+ recommended)
- **Storage**: 1GB minimum (depends on your data)

### Recommended PHP Extensions
```bash
# Required
php-pdo
php-pdo-mysql  # or php-pdo-pgsql for PostgreSQL
php-json
php-mbstring
php-openssl

# Recommended
php-opcache
php-redis      # if using Redis for caching
php-curl
php-xml
php-zip
```

---

## 🌐 Deployment Methods

## Method 1: Traditional VPS/Dedicated Server

### 1. Server Setup (Ubuntu/Debian)

```bash
# Update system
sudo apt update && sudo apt upgrade -y

# Install PHP 8.1+
sudo apt install software-properties-common -y
sudo add-apt-repository ppa:ondrej/php -y
sudo apt update
sudo apt install php8.1 php8.1-fpm php8.1-mysql php8.1-pdo php8.1-mbstring php8.1-json php8.1-curl php8.1-xml php8.1-zip php8.1-opcache -y

# Install Nginx
sudo apt install nginx -y

# Install MySQL
sudo apt install mysql-server -y
sudo mysql_secure_installation
```

### 2. Nginx Configuration

Create `/etc/nginx/sites-available/apileon-api`:

```nginx
server {
    listen 80;
    server_name your-api-domain.com;
    
    # Redirect HTTP to HTTPS
    return 301 https://$server_name$request_uri;
}

server {
    listen 443 ssl http2;
    server_name your-api-domain.com;
    
    # SSL Configuration
    ssl_certificate /path/to/your/certificate.crt;
    ssl_certificate_key /path/to/your/private.key;
    ssl_protocols TLSv1.2 TLSv1.3;
    ssl_ciphers ECDHE-RSA-AES256-GCM-SHA512:DHE-RSA-AES256-GCM-SHA512:ECDHE-RSA-AES256-GCM-SHA384:DHE-RSA-AES256-GCM-SHA384;
    ssl_prefer_server_ciphers off;
    
    # Document root
    root /var/www/apileon/public;
    index index.php;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    add_header Content-Security-Policy "default-src 'self' http: https: data: blob: 'unsafe-inline'" always;
    
    # API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(\.env|composer\.(json|lock)|package\.json|artisan) {
        deny all;
    }
    
    # Rate limiting
    limit_req_zone $binary_remote_addr zone=api:10m rate=10r/s;
    limit_req zone=api burst=20 nodelay;
    
    # Logging
    access_log /var/log/nginx/apileon-access.log;
    error_log /var/log/nginx/apileon-error.log;
}
```

### 3. Enable Site and Restart Services

```bash
# Enable site
sudo ln -s /etc/nginx/sites-available/apileon-api /etc/nginx/sites-enabled/
sudo nginx -t
sudo systemctl restart nginx

# Configure PHP-FPM
sudo systemctl enable php8.1-fpm
sudo systemctl start php8.1-fpm
```

### 4. Deploy Application

```bash
# Create deployment directory
sudo mkdir -p /var/www/apileon
cd /var/www/apileon

# Clone your repository
sudo git clone https://github.com/your-username/your-apileon-app.git .

# Set permissions
sudo chown -R www-data:www-data /var/www/apileon
sudo chmod -R 755 /var/www/apileon
sudo chmod -R 775 storage/  # if you have storage directory

# Configure environment
sudo cp .env.example .env
sudo nano .env  # Edit with production settings
```

## Method 2: Docker Deployment

### 1. Create Dockerfile

```dockerfile
# Dockerfile
FROM php:8.1-fpm-alpine

# Install system dependencies
RUN apk add --no-cache \
    nginx \
    supervisor \
    mysql-client \
    zip \
    unzip \
    git \
    curl

# Install PHP extensions
RUN docker-php-ext-install pdo pdo_mysql opcache

# Configure OPcache for production
RUN echo "opcache.enable=1" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.enable_cli=0" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.memory_consumption=128" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.interned_strings_buffer=8" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.max_accelerated_files=4000" >> /usr/local/etc/php/conf.d/opcache.ini \
    && echo "opcache.revalidate_freq=2" >> /usr/local/etc/php/conf.d/opcache.ini

# Set working directory
WORKDIR /var/www/html

# Copy application
COPY . .

# Copy configuration files
COPY docker/nginx.conf /etc/nginx/nginx.conf
COPY docker/php-fpm.conf /usr/local/etc/php-fpm.d/www.conf
COPY docker/supervisord.conf /etc/supervisor/conf.d/supervisord.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html \
    && chmod -R 755 /var/www/html

# Expose port
EXPOSE 80

# Start supervisor
CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
```

### 2. Docker Compose Configuration

```yaml
# docker-compose.yml
version: '3.8'

services:
  app:
    build: .
    container_name: apileon-api
    restart: unless-stopped
    ports:
      - "80:80"
      - "443:443"
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    volumes:
      - ./storage:/var/www/html/storage
      - ./bootstrap/cache:/var/www/html/bootstrap/cache
    depends_on:
      - database
    networks:
      - apileon

  database:
    image: mysql:8.0
    container_name: apileon-db
    restart: unless-stopped
    environment:
      MYSQL_DATABASE: apileon
      MYSQL_USER: apileon
      MYSQL_PASSWORD: secure_password
      MYSQL_ROOT_PASSWORD: root_password
    volumes:
      - mysql_data:/var/lib/mysql
    networks:
      - apileon

  redis:
    image: redis:7-alpine
    container_name: apileon-redis
    restart: unless-stopped
    networks:
      - apileon

volumes:
  mysql_data:

networks:
  apileon:
    driver: bridge
```

### 3. Deploy with Docker

```bash
# Build and start containers
docker-compose up -d --build

# Run migrations
docker-compose exec app php artisan migrate

# Check logs
docker-compose logs -f app
```

## Method 3: Cloud Platform Deployment

### AWS Elastic Beanstalk

1. **Prepare Application**:
```bash
# Create deployment package
zip -r apileon-api.zip . -x "*.git*" "node_modules/*" "tests/*"
```

2. **Create .ebextensions/01-php.config**:
```yaml
option_settings:
  aws:elasticbeanstalk:container:php:phpini:
    document_root: /public
    memory_limit: 256M
    zlib.output_compression: "Off"
    allow_url_fopen: "On"
    display_errors: "Off"
    max_execution_time: 60
```

3. **Deploy via EB CLI**:
```bash
eb init apileon-api
eb create production
eb deploy
```

### DigitalOcean App Platform

Create `app.yaml`:
```yaml
name: apileon-api
services:
- name: api
  source_dir: /
  github:
    repo: your-username/your-apileon-app
    branch: main
  run_command: |
    php artisan migrate --force
    php-fpm
  http_port: 8080
  instance_count: 1
  instance_size_slug: basic-xxs
  routes:
  - path: /
  envs:
  - key: APP_ENV
    value: production
  - key: APP_DEBUG
    value: "false"
databases:
- name: apileon-db
  engine: PG
  version: "13"
```

---

## 🔒 Production Security Configuration

### 1. Environment Variables (.env)

```bash
# Application
APP_NAME=Apileon
APP_ENV=production
APP_DEBUG=false
APP_KEY=base64:GENERATE_32_CHARACTER_KEY_HERE
APP_URL=https://your-api-domain.com

# Database
DB_CONNECTION=mysql
DB_HOST=your-production-db-host
DB_PORT=3306
DB_DATABASE=apileon_prod
DB_USERNAME=apileon_user
DB_PASSWORD=STRONG_RANDOM_PASSWORD_HERE

# Security
JWT_SECRET=GENERATE_STRONG_JWT_SECRET_HERE
HASH_SALT=GENERATE_RANDOM_SALT_HERE

# Logging
LOG_CHANNEL=single
LOG_LEVEL=error

# Cache (if using Redis)
CACHE_DRIVER=redis
REDIS_HOST=127.0.0.1
REDIS_PASSWORD=null
REDIS_PORT=6379
```

### 2. File Permissions

```bash
# Set correct ownership
sudo chown -R www-data:www-data /var/www/apileon

# Set directory permissions
sudo find /var/www/apileon -type d -exec chmod 755 {} \;

# Set file permissions
sudo find /var/www/apileon -type f -exec chmod 644 {} \;

# Make artisan executable
sudo chmod +x /var/www/apileon/artisan

# Protect sensitive files
sudo chmod 600 /var/www/apileon/.env
```

### 3. PHP Configuration (php.ini)

```ini
; Security
expose_php = Off
display_errors = Off
display_startup_errors = Off
log_errors = On
error_log = /var/log/php/error.log

; Performance
memory_limit = 256M
max_execution_time = 60
max_input_time = 60
post_max_size = 32M
upload_max_filesize = 32M

; OPcache
opcache.enable = 1
opcache.enable_cli = 0
opcache.memory_consumption = 128
opcache.interned_strings_buffer = 8
opcache.max_accelerated_files = 4000
opcache.revalidate_freq = 2
opcache.save_comments = 1
```

---

## 📊 Monitoring & Logging

### 1. Application Health Check

Create `routes/health.php`:
```php
<?php
use Apileon\Routing\Route;
use Apileon\Database\DatabaseManager;

Route::get('/health', function() {
    $checks = [
        'status' => 'ok',
        'timestamp' => date('c'),
        'version' => '1.0.0',
        'checks' => []
    ];
    
    // Database check
    try {
        $db = DatabaseManager::getInstance();
        $db->getPdo()->query('SELECT 1');
        $checks['checks']['database'] = 'ok';
    } catch (Exception $e) {
        $checks['status'] = 'error';
        $checks['checks']['database'] = 'error';
    }
    
    // Disk space check
    $freeBytes = disk_free_space('/');
    $totalBytes = disk_total_space('/');
    $usedPercent = (($totalBytes - $freeBytes) / $totalBytes) * 100;
    
    $checks['checks']['disk_space'] = [
        'status' => $usedPercent < 90 ? 'ok' : 'warning',
        'used_percent' => round($usedPercent, 2)
    ];
    
    return $checks;
});
```

### 2. Error Logging Setup

Create `src/Support/Logger.php`:
```php
<?php
namespace Apileon\Support;

class Logger
{
    private static $logPath;
    
    public static function init($logPath = null)
    {
        self::$logPath = $logPath ?: __DIR__ . '/../../storage/logs/app.log';
        
        // Create log directory if it doesn't exist
        $logDir = dirname(self::$logPath);
        if (!is_dir($logDir)) {
            mkdir($logDir, 0755, true);
        }
    }
    
    public static function error($message, $context = [])
    {
        self::log('ERROR', $message, $context);
    }
    
    public static function warning($message, $context = [])
    {
        self::log('WARNING', $message, $context);
    }
    
    public static function info($message, $context = [])
    {
        self::log('INFO', $message, $context);
    }
    
    private static function log($level, $message, $context = [])
    {
        $timestamp = date('Y-m-d H:i:s');
        $contextStr = $context ? ' ' . json_encode($context) : '';
        $logLine = "[{$timestamp}] {$level}: {$message}{$contextStr}\n";
        
        file_put_contents(self::$logPath, $logLine, FILE_APPEND | LOCK_EX);
    }
}
```

### 3. Monitoring Script

Create `scripts/monitor.sh`:
```bash
#!/bin/bash

# API Health Check Script
API_URL="https://your-api-domain.com"
LOG_FILE="/var/log/apileon-monitor.log"
EMAIL="admin@your-domain.com"

# Function to log messages
log_message() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" >> $LOG_FILE
}

# Check API health
response=$(curl -s -o /dev/null -w "%{http_code}" "$API_URL/health")

if [ "$response" != "200" ]; then
    log_message "API Health Check FAILED - Response: $response"
    echo "API is down! Response code: $response" | mail -s "API Alert" $EMAIL
else
    log_message "API Health Check PASSED"
fi

# Check disk space
disk_usage=$(df / | awk 'NR==2 {print $5}' | sed 's/%//')
if [ "$disk_usage" -gt 90 ]; then
    log_message "DISK SPACE WARNING - Usage: ${disk_usage}%"
    echo "Disk space is running low: ${disk_usage}%" | mail -s "Disk Space Alert" $EMAIL
fi

# Check PHP-FPM processes
php_processes=$(pgrep -c php-fpm)
if [ "$php_processes" -lt 2 ]; then
    log_message "PHP-FPM WARNING - Only $php_processes processes running"
    systemctl restart php8.1-fpm
fi
```

Make it executable and add to crontab:
```bash
chmod +x scripts/monitor.sh

# Add to crontab (run every 5 minutes)
echo "*/5 * * * * /var/www/apileon/scripts/monitor.sh" | crontab -
```

---

## 🔄 Deployment Automation

### 1. Simple Deployment Script

Create `scripts/deploy.sh`:
```bash
#!/bin/bash

# Simple deployment script
set -e

echo "Starting deployment..."

# Configuration
REPO_URL="https://github.com/your-username/your-apileon-app.git"
APP_PATH="/var/www/apileon"
BACKUP_PATH="/var/backups/apileon"
BRANCH="main"

# Create backup
echo "Creating backup..."
timestamp=$(date +%Y%m%d_%H%M%S)
mkdir -p $BACKUP_PATH
tar -czf "$BACKUP_PATH/backup_$timestamp.tar.gz" -C $APP_PATH .

# Update code
echo "Updating code..."
cd $APP_PATH
git fetch origin
git reset --hard origin/$BRANCH

# Run migrations
echo "Running migrations..."
php artisan migrate --force

# Clear cache (if implemented)
echo "Clearing cache..."
# php artisan cache:clear

# Restart services
echo "Restarting services..."
sudo systemctl reload nginx
sudo systemctl restart php8.1-fpm

# Health check
echo "Performing health check..."
sleep 5
response=$(curl -s -o /dev/null -w "%{http_code}" "http://localhost/health")
if [ "$response" = "200" ]; then
    echo "Deployment successful!"
else
    echo "Deployment failed! Rolling back..."
    cd $APP_PATH
    tar -xzf "$BACKUP_PATH/backup_$timestamp.tar.gz"
    sudo systemctl restart php8.1-fpm
    exit 1
fi

echo "Deployment completed successfully!"
```

### 2. GitHub Actions CI/CD

Create `.github/workflows/deploy.yml`:
```yaml
name: Deploy to Production

on:
  push:
    branches: [ main ]

jobs:
  deploy:
    runs-on: ubuntu-latest
    
    steps:
    - uses: actions/checkout@v3
    
    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: '8.1'
        
    - name: Install dependencies
      run: |
        if [ -f composer.json ]; then
          composer install --no-dev --optimize-autoloader
        fi
        
    - name: Run tests
      run: |
        if [ -f vendor/bin/phpunit ]; then
          vendor/bin/phpunit
        else
          php test-no-composer.php
        fi
        
    - name: Deploy to server
      uses: appleboy/ssh-action@v0.1.5
      with:
        host: ${{ secrets.HOST }}
        username: ${{ secrets.USERNAME }}
        key: ${{ secrets.SSH_KEY }}
        script: |
          cd /var/www/apileon
          git pull origin main
          php artisan migrate --force
          sudo systemctl reload nginx
          sudo systemctl restart php8.1-fpm
```

---

## 📈 Performance Optimization

### 1. PHP OPcache Configuration

Add to `/etc/php/8.1/fpm/conf.d/10-opcache.ini`:
```ini
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=256
opcache.interned_strings_buffer=16
opcache.max_accelerated_files=10000
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.validate_timestamps=0  ; Only in production
```

### 2. Database Optimization

```sql
-- Add indexes for common queries
ALTER TABLE users ADD INDEX idx_email (email);
ALTER TABLE users ADD INDEX idx_status (status);
ALTER TABLE users ADD INDEX idx_created_at (created_at);

-- Optimize queries
ANALYZE TABLE users;
OPTIMIZE TABLE users;
```

### 3. Caching Strategy

Implement caching in your controllers:
```php
<?php
namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;

class UserController
{
    public function index(Request $request): Response
    {
        $cacheKey = 'users_list_' . md5($request->getQueryString());
        
        // Try to get from cache first
        if ($cached = $this->getFromCache($cacheKey)) {
            return Response::json($cached);
        }
        
        // Fetch from database
        $users = User::paginate(20, $request->get('page', 1));
        
        // Cache for 5 minutes
        $this->putInCache($cacheKey, $users, 300);
        
        return Response::json($users);
    }
    
    private function getFromCache($key)
    {
        // Implement your caching logic
        // Could use Redis, Memcached, or file cache
        return null; // Placeholder
    }
    
    private function putInCache($key, $data, $ttl)
    {
        // Implement your caching logic
        // Could use Redis, Memcached, or file cache
    }
}
```

---

## 🔧 Troubleshooting

### Common Production Issues

1. **500 Internal Server Error**
   ```bash
   # Check PHP errors
   tail -f /var/log/php8.1-fpm.log
   
   # Check Nginx errors
   tail -f /var/log/nginx/error.log
   
   # Check application logs
   tail -f /var/www/apileon/storage/logs/app.log
   ```

2. **Database Connection Issues**
   ```bash
   # Test database connection
   php artisan db:test
   
   # Check MySQL status
   sudo systemctl status mysql
   
   # Check MySQL logs
   sudo tail -f /var/log/mysql/error.log
   ```

3. **Performance Issues**
   ```bash
   # Check PHP-FPM status
   sudo systemctl status php8.1-fpm
   
   # Monitor resource usage
   htop
   
   # Check slow queries
   sudo mysqldumpslow /var/log/mysql/slow.log
   ```

### Emergency Recovery

```bash
# Rollback to previous version
cd /var/www/apileon
git reset --hard HEAD~1
sudo systemctl restart php8.1-fpm

# Restore from backup
cd /var/www/apileon
tar -xzf /var/backups/apileon/backup_TIMESTAMP.tar.gz
sudo systemctl restart php8.1-fpm
```

---

## 📋 Production Checklist

### Before Going Live
- [ ] SSL certificate installed and configured
- [ ] All API endpoints tested
- [ ] Database migrations run
- [ ] Environment variables set
- [ ] Error logging configured
- [ ] Monitoring setup
- [ ] Backup strategy implemented
- [ ] Performance optimization applied
- [ ] Security hardening completed
- [ ] Load testing performed

### Post-Deployment
- [ ] Health checks passing
- [ ] API endpoints responding correctly
- [ ] Database connections working
- [ ] Logs being written correctly
- [ ] Monitoring alerts configured
- [ ] Backup verification
- [ ] Performance metrics baseline established

---

Your Apileon API is now ready for production deployment with enterprise-grade security, monitoring, and performance optimization! 🚀
