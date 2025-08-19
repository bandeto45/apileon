# Apileon Without Composer - Setup Guide

## 🚀 Quick Setup (No Composer Required)

Yes! You can use Apileon without Composer. The framework includes a built-in autoloader that handles all class loading automatically.

### Prerequisites

- **PHP 8.1+** (only requirement)
- **Web server** (or PHP built-in server)

### Installation Steps

1. **Download or Clone the Project**
   ```bash
   git clone https://github.com/bandeto45/apileon.git my-api
   cd my-api
   ```

2. **Run No-Composer Setup**
   ```bash
   ./setup-no-composer.sh
   ```

3. **Start the Server**
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Test Your API**
   ```bash
   curl http://localhost:8000/hello
   ```

## 🔧 How It Works

### Manual Autoloader

Apileon includes a custom autoloader (`autoload.php`) that:

- **Automatically loads classes** from `src/` and `app/` directories
- **Follows PSR-4 standards** for namespace mapping
- **Loads helper functions** globally
- **No external dependencies** required

### File Structure (No Composer)

```
my-api/
├── autoload.php          # ← Custom autoloader (replaces Composer)
├── app/                  # Your application code
├── src/                  # Framework core
├── public/
│   ├── index.php         # ← Auto-detects Composer or manual
│   └── index-no-composer.php  # ← Explicit no-Composer version
├── routes/
├── config/
└── docs/
```

## 🛠 Manual Setup (Step by Step)

If you prefer to set up manually:

### 1. Create Directory Structure
```bash
mkdir my-api
cd my-api
mkdir -p {app/Controllers,app/Models,config,public,routes,src,tests,docs}
```

### 2. Download Framework Files
Copy all the framework files from the Apileon repository to your directory.

### 3. Create Environment File
```bash
cp .env.example .env
```

Edit `.env` with your settings:
```env
APP_ENV=local
APP_DEBUG=true
APP_KEY=your-secret-key
APP_URL=http://localhost:8000
```

### 4. Test the Setup
```bash
php -S localhost:8000 -t public
curl http://localhost:8000/hello
```

## 📝 Usage Examples

### Basic API Endpoint

**routes/api.php:**
```php
<?php
use Apileon\Routing\Route;

Route::get('/users', function() {
    return [
        'users' => [
            ['id' => 1, 'name' => 'John Doe'],
            ['id' => 2, 'name' => 'Jane Smith']
        ]
    ];
});

Route::get('/users/{id}', function($request) {
    return [
        'user' => [
            'id' => $request->param('id'),
            'name' => 'User ' . $request->param('id')
        ]
    ];
});
```

### Simple Controller

**app/Controllers/ApiController.php:**
```php
<?php

namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;

class ApiController
{
    public function status(Request $request): Response
    {
        return Response::json([
            'status' => 'ok',
            'framework' => 'Apileon',
            'version' => '1.0.0',
            'timestamp' => date('Y-m-d H:i:s')
        ]);
    }

    public function echo(Request $request): Response
    {
        return Response::json([
            'method' => $request->method(),
            'uri' => $request->uri(),
            'headers' => $request->headers(),
            'query' => $request->query(),
            'body' => $request->all()
        ]);
    }
}
```

**routes/api.php:**
```php
Route::get('/status', 'App\Controllers\ApiController@status');
Route::any('/echo', 'App\Controllers\ApiController@echo');
```

## 🧪 Testing Without Composer

You can still run basic tests without PHPUnit:

**simple-test.php:**
```php
<?php

require_once 'autoload.php';

use Apileon\Http\Request;
use Apileon\Http\Response;

echo "🧪 Testing Apileon Framework...\n\n";

// Test 1: Response creation
$response = Response::json(['message' => 'test']);
assert($response->getStatusCode() === 200);
echo "✅ JSON Response creation works\n";

// Test 2: Request handling
$_SERVER['REQUEST_METHOD'] = 'GET';
$_SERVER['REQUEST_URI'] = '/test';
$request = new Request();
assert($request->method() === 'GET');
assert($request->uri() === '/test');
echo "✅ Request handling works\n";

// Test 3: Route parameters
$request->setParams(['id' => '123']);
assert($request->param('id') === '123');
echo "✅ Route parameters work\n";

echo "\n🎉 All basic tests passed!\n";
```

Run with:
```bash
php simple-test.php
```

## 🚀 Production Deployment (No Composer)

### Apache Configuration

**public/.htaccess:**
```apache
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

### Nginx Configuration

```nginx
server {
    listen 80;
    server_name your-domain.com;
    root /path/to/your-api/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php8.1-fpm.sock;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $realpath_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
```

### Environment Setup for Production

```env
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-secure-production-key
APP_URL=https://your-domain.com
```

## 📋 Available Commands (No Composer)

Since you don't have Composer scripts, use these direct commands:

```bash
# Start development server
php -S localhost:8000 -t public

# Run simple tests
php simple-test.php

# Check PHP syntax
find . -name "*.php" -exec php -l {} \;

# Check framework status
./status.sh
```

## 🔍 Troubleshooting

### Common Issues

**1. Class not found errors:**
```bash
# Make sure autoload.php is being loaded
head -5 public/index.php
```

**2. Permission errors:**
```bash
chmod -R 755 storage/
chmod +x setup-no-composer.sh
```

**3. PHP version issues:**
```bash
php -v
# Should show 8.1 or higher
```

### Debug Mode

Enable debug mode in `.env`:
```env
APP_DEBUG=true
```

This will show detailed error messages.

## 🎯 Benefits of No-Composer Setup

### ✅ Advantages
- **No dependencies** - Pure PHP, nothing else needed
- **Faster deployment** - No vendor folder to upload
- **Simpler hosting** - Works on any PHP hosting
- **Easier debugging** - All code is visible and editable
- **Lightweight** - Smaller footprint

### ⚠️ Considerations
- **Manual updates** - Framework updates need manual copying
- **No package management** - Can't easily add external libraries
- **Testing limitations** - No PHPUnit (but basic testing still possible)

## 🌟 Perfect For

- **Simple APIs** - Small to medium REST APIs
- **Learning** - Understanding how frameworks work
- **Shared hosting** - Where Composer might not be available
- **Rapid prototyping** - Quick API development
- **Embedded systems** - Minimal resource usage

Your Apileon framework is now **completely self-contained** and ready to run without any external dependencies! 🎉
