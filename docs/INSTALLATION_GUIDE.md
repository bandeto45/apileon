# 📦 Apileon Installation Guide

## 🎯 **Choose Your Installation Method**

This guide helps you choose the right installation method for your needs and provides detailed setup instructions for each approach.

---

## 🔄 **Installation Decision Tree**

```
Do you need PHP on the server?
├── NO → 📦 Portable Deployment (Self-contained)
│   ├── For Production → Pre-built Executables
│   ├── For Containers → Docker Images
│   └── For Serverless → Lambda/Vercel Packages
│
└── YES → PHP Required Installation
    ├── Development → 🛠️ Composer Setup
    │   ├── Team Development
    │   ├── Package Management
    │   └── Advanced Tooling
    │
    └── Production → 🔧 Traditional PHP
        ├── Shared Hosting
        ├── VPS/Dedicated
        └── Existing PHP Infrastructure
```

---

## 📦 **Method 1: Portable Deployment (No PHP Required)**

### **🎯 Best For:**
- Production servers without PHP
- Docker containers and Kubernetes
- Serverless deployments (AWS Lambda, Vercel)
- Microservices architecture
- Easy distribution and scaling

### **📋 Prerequisites:**
- None! Everything is self-contained

### **⚡ Quick Start:**

#### **Option A: Pre-built Executables**

```bash
# 🐧 Linux
wget https://releases.apileon.com/v1.0.0/apileon-linux-x64.tar.gz
tar -xzf apileon-linux-x64.tar.gz
cd apileon-linux-x64

# Configure your API
echo "Your API code goes in: ./app/"
echo "Configuration goes in: ./.env"

# Start server
./apileon-server --port 8000 --workers 4

# Test
curl http://localhost:8000/health
```

```powershell
# 🪟 Windows
Invoke-WebRequest -Uri "https://releases.apileon.com/v1.0.0/apileon-windows-x64.zip" -OutFile "apileon.zip"
Expand-Archive -Path "apileon.zip" -DestinationPath "apileon"
cd apileon

# Start server
.\apileon-server.exe --port 8000

# Test
curl http://localhost:8000/health
```

```bash
# 🍎 macOS
wget https://releases.apileon.com/v1.0.0/apileon-macos-x64.tar.gz
tar -xzf apileon-macos-x64.tar.gz
cd apileon-macos-x64

# Start server
./apileon-server --port 8000

# Test
curl http://localhost:8000/health
```

#### **Option B: Docker Images**

```bash
# 1. Pull official image
docker pull apileon/runtime:latest

# 2. Create your API
mkdir my-api && cd my-api

# 3. Create Dockerfile
cat > Dockerfile << 'EOF'
FROM apileon/runtime:latest
COPY . /app
EXPOSE 8000
CMD ["apileon-server", "--port", "8000"]
EOF

# 4. Build and run
docker build -t my-api .
docker run -p 8000:8000 my-api

# 5. Test
curl http://localhost:8000/health
```

#### **Option C: Build Your Own Portable Version**

```bash
# 1. Start with development setup (see Method 3)
git clone https://github.com/bandeto45/apileon.git
cd apileon

# 2. Develop your API
# ... add your controllers, routes, etc.

# 3. Build portable package
./scripts/build-portable.sh --target linux-x64 --optimize

# 4. Deploy generated package
cp dist/my-api-portable.tar.gz /your/server/
# On target server:
tar -xzf my-api-portable.tar.gz
./my-api/server --port 80
```

### **🚀 Production Deployment:**

#### **Docker Compose (Recommended)**

```yaml
# docker-compose.yml
version: '3.8'
services:
  api:
    image: apileon/runtime:latest
    volumes:
      - ./app:/app
      - ./storage:/app/storage
    environment:
      - APP_ENV=production
      - APP_DEBUG=false
    ports:
      - "8000:8000"
    restart: unless-stopped
    
  nginx:
    image: nginx:alpine
    volumes:
      - ./nginx.conf:/etc/nginx/nginx.conf
    ports:
      - "80:80"
      - "443:443"
    depends_on:
      - api
      
  redis:
    image: redis:alpine
    volumes:
      - redis-data:/data
      
volumes:
  redis-data:
```

#### **Kubernetes Deployment**

```yaml
# k8s-deployment.yml
apiVersion: apps/v1
kind: Deployment
metadata:
  name: apileon-api
spec:
  replicas: 3
  selector:
    matchLabels:
      app: apileon-api
  template:
    metadata:
      labels:
        app: apileon-api
    spec:
      containers:
      - name: api
        image: apileon/runtime:latest
        ports:
        - containerPort: 8000
        env:
        - name: APP_ENV
          value: "production"
        - name: APP_DEBUG
          value: "false"
        resources:
          requests:
            memory: "64Mi"
            cpu: "100m"
          limits:
            memory: "128Mi"
            cpu: "200m"
---
apiVersion: v1
kind: Service
metadata:
  name: apileon-service
spec:
  selector:
    app: apileon-api
  ports:
  - port: 80
    targetPort: 8000
  type: LoadBalancer
```

---

## 🔧 **Method 2: Traditional PHP (PHP Required)**

### **🎯 Best For:**
- Shared hosting providers
- Existing PHP infrastructure
- Cost-effective deployments
- Familiar PHP deployment workflow
- Easy debugging and maintenance

### **📋 Prerequisites:**
- PHP 8.1 or higher
- Web server (Apache/Nginx) or PHP built-in server
- Optional: MySQL/PostgreSQL for database features

### **⚡ Quick Start:**

#### **Option A: Automated Setup (Recommended)**

```bash
# 1. Download and setup
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api

# 2. Run setup script
chmod +x setup-no-composer.sh
./setup-no-composer.sh

# 3. Start development server
php -S localhost:8000 -t public

# 4. Test installation
curl http://localhost:8000/hello
# Expected: {"message":"Hello from Apileon!"}

# 5. Check health
curl http://localhost:8000/health
# Expected: {"status":"healthy","framework":"Apileon"}
```

#### **Option B: Manual Setup**

```bash
# 1. Create project structure
mkdir my-api && cd my-api
mkdir -p app/{Controllers,Models,Middleware}
mkdir -p {config,public,routes,storage/{logs,cache,sessions},tests}

# 2. Download framework core
wget -O framework.zip https://github.com/bandeto45/apileon/archive/main.zip
unzip framework.zip
cp -r apileon-main/src .
cp apileon-main/autoload.php .
cp apileon-main/public/index.php public/
cp apileon-main/.env.example .env

# 3. Set permissions
chmod -R 755 storage/
chmod 644 .env

# 4. Test framework
php -r "require 'autoload.php'; echo 'Framework loaded successfully!';"

# 5. Start server
php -S localhost:8000 -t public
```

### **🌐 Web Server Configuration:**

#### **Apache Configuration**

Create `public/.htaccess`:
```apache
RewriteEngine On

# Security headers
Header always set X-Frame-Options "SAMEORIGIN"
Header always set X-XSS-Protection "1; mode=block"
Header always set X-Content-Type-Options "nosniff"

# API routing
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]

# Hide sensitive files
<Files ".env">
    Require all denied
</Files>

<FilesMatch "\.(md|json|lock)$">
    Require all denied
</FilesMatch>
```

#### **Nginx Configuration**

Create `/etc/nginx/sites-available/my-api`:
```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /var/www/my-api/public;
    index index.php;

    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;

    # API routes
    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }

    # Hide sensitive files
    location ~ /\.(env|git) {
        deny all;
    }
}
```

### **🏠 Shared Hosting Setup:**

```bash
# 1. Prepare files locally
tar -czf my-api.tar.gz . --exclude=.git --exclude=node_modules

# 2. Upload to your hosting account
# - Use cPanel File Manager or FTP
# - Extract in public_html/ or subdirectory

# 3. Verify directory structure
# public_html/
# ├── app/
# ├── config/
# ├── public/
# │   └── index.php
# ├── routes/
# ├── src/
# ├── storage/
# ├── autoload.php
# └── .env

# 4. Set permissions via cPanel or FTP
chmod 755 storage/
chmod 755 storage/cache/
chmod 755 storage/logs/

# 5. Test via browser
# https://yourdomain.com/hello
```

---

## 🛠️ **Method 3: Development with Composer**

### **🎯 Best For:**
- Local development and testing
- Team collaboration
- Complex projects with dependencies
- Professional development workflow
- CI/CD pipelines and automation

### **📋 Prerequisites:**
- PHP 8.1 or higher
- Composer 2.0 or higher
- Git (recommended for version control)

### **⚡ Quick Start:**

#### **Option A: Create New Project**

```bash
# 1. Create project
composer create-project apileon/framework my-api
cd my-api

# 2. Install dependencies
composer install

# 3. Setup environment
cp .env.example .env
php artisan key:generate

# 4. Start development server
composer serve
# or: php -S localhost:8000 -t public

# 5. Run tests
composer test

# 6. Check code quality
composer lint
```

#### **Option B: Add to Existing Project**

```bash
# 1. Add framework to existing project
composer require apileon/framework

# 2. Publish configuration files
php artisan vendor:publish --tag=apileon-config

# 3. Update autoloader
composer dump-autoload

# 4. Initialize framework
php artisan apileon:init

# 5. Test integration
composer test
```

### **🔧 Development Workflow:**

```bash
# Start development with hot reloading
composer dev

# Run tests continuously
composer test:watch

# Code quality checks
composer lint
composer analyze
composer format

# Generate documentation
composer docs

# Build for production
composer build:production

# Create deployment package
composer package:zip
composer package:docker
```

### **📦 Package Development:**

```bash
# Create reusable package
composer create-package vendor/my-package

# Package structure
vendor/my-package/
├── src/
├── tests/
├── docs/
├── composer.json
└── README.md

# Publish to Packagist
composer publish
```

### **🧪 Testing and Quality:**

```bash
# PHPUnit tests
composer test
composer test:unit
composer test:integration
composer test:coverage

# Code analysis
composer analyze:phpstan
composer analyze:psalm

# Code formatting
composer format:fix
composer format:check

# All quality checks
composer quality
```

---

## ⚙️ **Environment Configuration**

### **📄 Environment Variables (.env)**

```env
# Application
APP_NAME="My API"
APP_ENV=local
APP_DEBUG=true
APP_KEY=base64:your-32-character-key
APP_URL=http://localhost:8000

# Database
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon
DB_USERNAME=root
DB_PASSWORD=

# Cache
CACHE_DRIVER=file
CACHE_PREFIX=apileon

# Logging
LOG_CHANNEL=single
LOG_LEVEL=debug

# Performance
PERFORMANCE_MONITORING=true
QUERY_LOGGING=true
```

### **🔧 Configuration Files**

Create `config/app.php`:
```php
<?php

return [
    'name' => env('APP_NAME', 'Apileon API'),
    'env' => env('APP_ENV', 'production'),
    'debug' => env('APP_DEBUG', false),
    'url' => env('APP_URL', 'http://localhost'),
    'key' => env('APP_KEY'),
    'timezone' => 'UTC',
];
```

---

## 🔍 **Verification and Testing**

### **✅ Installation Verification**

```bash
# Test framework loading
php -r "require 'autoload.php'; echo 'Framework loaded!' . PHP_EOL;"

# Test web server
curl http://localhost:8000/health

# Test API endpoint
curl http://localhost:8000/hello

# Test performance monitoring (debug mode)
curl http://localhost:8000/metrics
```

### **🧪 Run Test Suite**

```bash
# Basic functionality test
php test-no-composer.php

# Full test suite (if using Composer)
composer test

# Manual API testing
curl -X GET http://localhost:8000/api/users
curl -X POST http://localhost:8000/api/users -d '{"name":"John","email":"john@example.com"}' -H "Content-Type: application/json"
```

---

## 🚨 **Troubleshooting**

### **Common Issues and Solutions**

#### **"Framework not loading"**
```bash
# Check PHP version
php --version  # Should be 8.1+

# Check file permissions
ls -la autoload.php  # Should be readable

# Test autoloader
php -r "require 'autoload.php'; var_dump(class_exists('\\Apileon\\Foundation\\Application'));"
```

#### **"Routes not working"**
```bash
# Check web server configuration
# Ensure .htaccess (Apache) or nginx.conf is properly configured

# Test direct index.php access
curl http://localhost:8000/index.php

# Check route definitions
cat routes/api.php
```

#### **"Database connection failed"**
```bash
# Check database credentials
cat .env | grep DB_

# Test database connection
php -r "
try {
    \$pdo = new PDO('mysql:host=127.0.0.1;dbname=apileon', 'root', '');
    echo 'Database connected!' . PHP_EOL;
} catch (Exception \$e) {
    echo 'Database error: ' . \$e->getMessage() . PHP_EOL;
}
"
```

#### **"Performance monitoring not working"**
```bash
# Check debug mode is enabled
grep APP_DEBUG .env

# Test metrics endpoint
curl http://localhost:8000/metrics

# Check storage permissions
ls -la storage/cache/
```

---

## 📞 **Getting Help**

- 📖 **Documentation**: [Developer Guide](DEVELOPER_GUIDE.md)
- 🔍 **Quick Reference**: [Quick Reference](QUICK_REFERENCE.md)
- 🐛 **Issues**: [GitHub Issues](https://github.com/bandeto45/apileon/issues)
- 💬 **Community**: [Discord](https://discord.gg/apileon)
- 📧 **Email**: support@apileon.com

---

**🎯 Ready to build your API?** Choose your installation method above and start building amazing APIs with Apileon! 🚀
