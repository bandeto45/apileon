# Apileon 🦁
_A lightweight, enterprise-ready PHP framework focused only on REST APIs._

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-8892BF.svg)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-green.svg)

---

## 🚀 Overview
**Apileon** is a PHP framework built exclusively for **REST API development**.  
It is designed with **simplicity, speed, and scalability** in mind — removing unnecessary overhead and focusing on what matters most: **clean and powerful APIs**.

Think of Apileon as the **enterprise-grade foundation** for your next API project.

---

## ✨ Features
- ⚡ **REST-first architecture** – built only for APIs, no bloat  
- 🛠 **Simple Routing** – clean and fast endpoint definitions  
- 🔐 **Middleware Support** – authentication, CORS, rate limiting  
- 📦 **Extensible Core** – modular design for enterprise projects  
- 📊 **JSON-first Communication** – optimized for modern web & mobile apps  
- 🧪 **Test-Friendly** – structured for PHPUnit & CI/CD pipelines  
- 🚀 **Zero Dependencies** – works with just PHP 8.1+, no Composer required  
- 🔧 **Auto-loading** – PSR-4 compliant autoloader included  
- 🌐 **Production Ready** – enterprise-grade security and performance  

---

## 📦 Installation

### Option 1: With Composer (Recommended)
```bash
composer create-project apileon/framework my-api
```

### Option 2: Without Composer (Simple Setup)
```bash
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup-no-composer.sh
```

**No dependencies required!** - Apileon works with just PHP 8.1+

---

## 🛠 Quick Start

### With Composer
```bash
composer create-project apileon/framework my-api
cd my-api
composer serve
```

### Without Composer (Just PHP!)
```bash
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup-no-composer.sh
php -S localhost:8000 -t public
```

**2. Define your first route**  
Edit `routes/api.php`:
```php
use Apileon\Routing\Route;

Route::get('/hello', function () {
    return ['message' => 'Hello from Apileon!'];
});
```

**3. Start the server**
```bash
# With Composer
composer serve

# Without Composer  
php -S localhost:8000 -t public
```

**4. Test your endpoint**
```bash
curl http://localhost:8000/hello
```
Response:
```json
{
  "message": "Hello from Apileon!"
}
```

---

## 📂 Project Structure
```
my-api/
├── autoload.php            # Manual autoloader (no Composer needed)
├── app/                    # Application logic
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Data models
│   └── Middleware/        # Custom middleware
├── config/                # Configuration files
│   ├── app.php           # App configuration
│   └── database.php      # Database configuration
├── docs/                  # Documentation
│   ├── README.md         # Complete framework guide
│   ├── API.md            # API documentation
│   ├── routing.md        # Routing guide
│   ├── middleware.md     # Middleware guide
│   ├── testing.md        # Testing guide
│   └── no-composer-setup.md # No-Composer setup
├── public/               # Public web root
│   ├── index.php         # Smart entry point (Composer + manual)
│   └── index-no-composer.php # Explicit no-Composer entry
├── routes/               # Route definitions
│   └── api.php           # API routes
├── src/                  # Framework core
│   ├── Foundation/       # Application foundation
│   ├── Http/            # HTTP components & middleware
│   ├── Routing/         # Routing system
│   └── Support/         # Helper utilities & functions
├── tests/               # PHPUnit tests
├── vendor/              # Composer dependencies (optional)
├── .env.example         # Environment template
├── composer.json        # Dependencies & autoloading (optional)
├── phpunit.xml          # Testing configuration
├── setup.sh            # Composer setup script
├── setup-no-composer.sh # No-Composer setup script
├── status.sh           # Status check script
└── test-no-composer.php # Framework test (no dependencies)
```

---

## ⚙️ Configuration
- `.env` file for environment variables  
- `config/` for database, caching, and app settings  

Example `.env`:
```env
APP_ENV=local
APP_KEY=base64:randomkeyhere
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon
DB_USERNAME=root
DB_PASSWORD=
```

---

## 🧩 Middleware Example
```php
use Apileon\Http\Middleware;

class AuthMiddleware extends Middleware {
    public function handle($request, $next) {
        if (!$request->header('Authorization')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
```

Attach middleware to routes:
```php
Route::get('/profile', 'UserController@profile')->middleware('auth');
```

---

## 🧪 Testing

### With Composer (Full Testing)
```bash
# Run all tests
composer test

# Run specific test
vendor/bin/phpunit tests/RequestTest.php

# Generate coverage report
vendor/bin/phpunit --coverage-html coverage/
```

### Without Composer (Basic Testing)
```bash
# Test framework functionality
php test-no-composer.php

# Manual syntax check
find . -name "*.php" -exec php -l {} \;
```

---

## 📖 Documentation
Full documentation available in the `docs/` folder:
- **[Complete Guide](docs/README.md)** - Framework documentation
- **[No Composer Setup](docs/no-composer-setup.md)** - Use without Composer
- **[API Reference](docs/API.md)** - Endpoint documentation  
- **[Routing Guide](docs/routing.md)** - Advanced routing patterns
- **[Middleware Guide](docs/middleware.md)** - Security & custom middleware
- **[Testing Guide](docs/testing.md)** - Unit & integration testing

---

## 🚀 Quick Examples

### Create a Simple API
```php
// routes/api.php
use Apileon\Routing\Route;

Route::get('/users', function() {
    return [
        'users' => [
            ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
            ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']
        ],
        'total' => 2
    ];
});

Route::get('/users/{id}', function($request) {
    $id = $request->param('id');
    return [
        'user' => [
            'id' => $id,
            'name' => 'User ' . $id,
            'email' => "user{$id}@example.com"
        ]
    ];
});

Route::post('/users', function($request) {
    $data = $request->all();
    return [
        'message' => 'User created successfully',
        'user' => [
            'id' => rand(100, 999),
            'name' => $data['name'] ?? 'Unknown',
            'email' => $data['email'] ?? 'unknown@example.com'
        ]
    ];
});
```

### Add Authentication
```php
// Protected routes
Route::group(['middleware' => ['auth']], function() {
    Route::get('/profile', function($request) {
        return ['user' => ['id' => 1, 'name' => 'Authenticated User']];
    });
    
    Route::put('/profile', function($request) {
        return ['message' => 'Profile updated', 'data' => $request->all()];
    });
});

// Usage: curl -H "Authorization: Bearer your-token" http://localhost:8000/profile
```

### Custom Controller
```php
// app/Controllers/PostController.php
<?php
namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;

class PostController
{
    public function index(Request $request): Response
    {
        $posts = [
            ['id' => 1, 'title' => 'First Post', 'content' => 'Hello World'],
            ['id' => 2, 'title' => 'Second Post', 'content' => 'API Development']
        ];
        
        return Response::json(['posts' => $posts]);
    }
    
    public function show(Request $request): Response
    {
        $id = $request->param('id');
        
        if (!is_numeric($id)) {
            return abort(400, 'Invalid post ID');
        }
        
        return Response::json([
            'post' => [
                'id' => $id,
                'title' => "Post {$id}",
                'content' => 'This is post content'
            ]
        ]);
    }
    
    public function store(Request $request): Response
    {
        $data = $request->all();
        
        if (empty($data['title'])) {
            return Response::json([
                'error' => 'Validation failed',
                'message' => 'Title is required'
            ], 422);
        }
        
        return Response::json([
            'message' => 'Post created successfully',
            'post' => [
                'id' => rand(100, 999),
                'title' => $data['title'],
                'content' => $data['content'] ?? ''
            ]
        ], 201);
    }
}

// routes/api.php
Route::get('/posts', 'App\Controllers\PostController@index');
Route::get('/posts/{id}', 'App\Controllers\PostController@show');
Route::post('/posts', 'App\Controllers\PostController@store');
```

---

## 🔧 Configuration Examples

### Environment Variables (.env)
```env
# Application
APP_ENV=local
APP_DEBUG=true
APP_KEY=your-secret-key-here
APP_URL=http://localhost:8000

# Database (if using external DB)
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon_db
DB_USERNAME=root
DB_PASSWORD=secret

# Cache & Sessions
CACHE_DRIVER=file
SESSION_DRIVER=file

# Logging
LOG_CHANNEL=single
LOG_LEVEL=debug
```

### Custom Middleware
```php
// app/Middleware/JsonOnlyMiddleware.php
<?php
namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class JsonOnlyMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Only accept JSON for POST/PUT/PATCH
        if (in_array($request->method(), ['POST', 'PUT', 'PATCH']) && !$request->isJson()) {
            return $this->response()->json([
                'error' => 'Bad Request',
                'message' => 'This endpoint only accepts JSON requests'
            ], 400);
        }
        
        $response = $next($request);
        
        // Ensure JSON response
        $response->header('Content-Type', 'application/json');
        
        return $response;
    }
}

// Register and use
$app->getRouter()->registerMiddleware('json', JsonOnlyMiddleware::class);
Route::post('/api/data', 'DataController@store')->middleware('json');
```

---

## 🌟 Why Choose Apileon?

### ✅ **Simplicity**
- **Zero configuration** - Works out of the box
- **Intuitive API** - Easy to learn and use
- **Clear documentation** - Comprehensive guides and examples

### ✅ **Flexibility**
- **Composer optional** - Use with or without dependency management
- **Modular design** - Add only what you need
- **Extensible** - Easy to customize and extend

### ✅ **Performance**
- **Lightweight core** - Minimal overhead
- **Fast routing** - Optimized for speed
- **JSON-first** - Built for modern APIs

### ✅ **Enterprise Ready**
- **Security built-in** - CORS, authentication, rate limiting
- **Test-friendly** - Comprehensive testing support
- **Production ready** - Battle-tested architecture

---

## 📋 Comparison

| Feature | Apileon | Laravel | Slim | Lumen |
|---------|---------|---------|------|-------|
| **Size** | Tiny | Large | Small | Medium |
| **Dependencies** | Optional | Required | Required | Required |
| **API Focus** | ✅ Exclusive | ❌ Full-stack | ✅ Yes | ✅ Yes |
| **Learning Curve** | Easy | Steep | Medium | Medium |
| **Setup Time** | < 1 min | 5-10 min | 2-5 min | 2-5 min |
| **No Composer** | ✅ Yes | ❌ No | ❌ No | ❌ No |

---

## 🤝 Contributing
Contributions are welcome!  
- Fork the repo  
- Create a feature branch  
- Submit a Pull Request  

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## 📜 License
Apileon is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🌟 Acknowledgements
- Inspired by Laravel’s elegance & Slim’s simplicity  
- Built for developers who want **REST-only frameworks**  
