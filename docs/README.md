# Apileon Framework - Developer Documentation

## 📚 Table of Contents

📖 **Developer Resources:**
- [🛠️ Developer Guide](DEVELOPER_GUIDE.md) - Complete guide with do's and don'ts
- [🔍 Quick Reference](QUICK_REFERENCE.md) - "Where do I put...?" answers

**Framework Documentation:**
1. [🚀 Getting Started](#-getting-started)
2. [⚙️ Installation](#️-installation)
3. [🔧 Configuration](#-configuration)
4. [🛤️ Routing](#️-routing)
5. [🎮 Controllers](#-controllers)
6. [📊 Models & Database](#-models--database)
7. [🔐 Middleware](#-middleware)
8. [💾 Caching](#-caching)
9. [🎯 Events](#-events)
10. [📈 Performance Monitoring](#-performance-monitoring)
11. [📨 Request & Response](#-request--response)
12. [❌ Error Handling](#-error-handling)
13. [🧪 Testing](#-testing)
14. [🚀 Deployment](#-deployment)
15. [✅ Best Practices](#-best-practices)
16. [❌ Common Mistakes](#-common-mistakes)

---

## 🚀 Getting Started

Apileon is a lightweight PHP framework designed exclusively for REST API development with enterprise-grade features including built-in performance monitoring, caching, and event systems.

### 🎯 Framework Philosophy

- **API-First**: Built exclusively for REST APIs, no web UI bloat
- **Zero Dependencies**: Works with just PHP 8.1+, Composer optional
- **Developer Friendly**: Intuitive syntax with comprehensive error messages
- **Enterprise Ready**: Security, performance monitoring, caching, and testing built-in
- **Production Optimized**: Built-in health checks, metrics, and monitoring

### 📋 Requirements

- **PHP 8.1 or higher** (Required)
- **Web server** (Apache, Nginx, or PHP built-in server)
- **Composer** (Optional - framework includes manual autoloader)
- **SQLite/MySQL/PostgreSQL** (Optional - for database features)

### ⚡ Quick Start (30 seconds)

```bash
# 🚀 Zero Dependencies Setup
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup-no-composer.sh
php -S localhost:8000 -t public

# Test your API
curl http://localhost:8000/hello
# Response: {"message":"Hello from Apileon!"}
```

---

## Installation

### Option 1: With Composer (Recommended for Complex Projects)

1. **Create new project:**
   ```bash
   composer create-project apileon/framework my-api
   cd my-api
   ```

2. **Install dependencies:**
   ```bash
   composer install
   ```

3. **Setup environment:**
   ```bash
   cp .env.example .env
   # Edit .env with your settings
   ```

4. **Start development server:**
   ```bash
   composer serve
   # or manually: php -S localhost:8000 -t public
   ```

### Option 2: Without Composer (Recommended for Simple Projects)

1. **Clone the repository:**
   ```bash
   git clone https://github.com/bandeto45/apileon.git my-api
   cd my-api
   ```

2. **Run setup script:**
   ```bash
   ./setup-no-composer.sh
   ```

3. **Start development server:**
   ```bash
   php -S localhost:8000 -t public
   ```

4. **Test the installation:**
   ```bash
   curl http://localhost:8000/hello
   # Should return: {"message":"Hello from Apileon!"}
   ```

### Manual Installation (Advanced)

If you prefer complete control over the setup:

```bash
# 1. Create project structure
mkdir my-api && cd my-api
mkdir -p {app/Controllers,app/Models,app/Middleware,config,public,routes,src,tests,docs,storage/{logs,cache,sessions}}

# 2. Copy framework files (from Apileon repository)
# - Copy all src/ files
# - Copy autoload.php
# - Copy public/index.php
# - Copy example routes, controllers, etc.

# 3. Setup environment
cp .env.example .env

# 4. Test framework
php test-no-composer.php
```

---

## Configuration

### Environment Variables

Create a `.env` file from `.env.example`:

```env
APP_ENV=local
APP_DEBUG=true
APP_KEY=your-secret-key
APP_URL=http://localhost:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon
DB_USERNAME=root
DB_PASSWORD=
```

### Configuration Files

Configuration files are stored in the `config/` directory:

- `config/app.php` - Application settings
- `config/database.php` - Database connections

Example configuration access:
```php
$debug = config('app.debug');
$dbHost = config('database.connections.mysql.host');
```

---

## Routing

> **🎯 Need detailed guidance on where to put your code?** See our comprehensive [Developer Guide](DEVELOPER_GUIDE.md) with do's and don'ts, practical examples, and best practices!

### Basic Routing

Define routes in `routes/api.php`:

```php
use Apileon\Routing\Route;

// GET route
Route::get('/users', function() {
    return ['message' => 'Get all users'];
});

// POST route
Route::post('/users', function($request) {
    return ['message' => 'Create user', 'data' => $request->all()];
});

// Route with parameters
Route::get('/users/{id}', function($request) {
    return ['user_id' => $request->param('id')];
});
```

### HTTP Methods

```php
Route::get('/resource', $handler);
Route::post('/resource', $handler);
Route::put('/resource/{id}', $handler);
Route::patch('/resource/{id}', $handler);
Route::delete('/resource/{id}', $handler);
Route::options('/resource', $handler);

// Multiple methods
Route::any('/resource', $handler);
```

### Route Parameters

```php
// Required parameter
Route::get('/users/{id}', function($request) {
    $id = $request->param('id');
    return ['user_id' => $id];
});

// Multiple parameters
Route::get('/users/{id}/posts/{postId}', function($request) {
    return [
        'user_id' => $request->param('id'),
        'post_id' => $request->param('postId')
    ];
});
```

### Route Groups

```php
// Group with prefix
Route::group(['prefix' => 'api/v1'], function() {
    Route::get('/users', 'UserController@index');
    Route::post('/users', 'UserController@store');
});

// Group with middleware
Route::group(['middleware' => ['auth']], function() {
    Route::get('/profile', 'UserController@profile');
});

// Group with prefix and middleware
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    Route::get('/dashboard', 'AdminController@dashboard');
});
```

### Controller Routes

```php
// Basic controller route
Route::get('/users', 'App\Controllers\UserController@index');

// RESTful resource routes
Route::get('/users', 'UserController@index');
Route::get('/users/{id}', 'UserController@show');
Route::post('/users', 'UserController@store');
Route::put('/users/{id}', 'UserController@update');
Route::delete('/users/{id}', 'UserController@destroy');
```

---

## Controllers

### Creating Controllers

Controllers are stored in `app/Controllers/` directory:

```php
<?php

namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;

class UserController
{
    public function index(Request $request): Response
    {
        // Get all users
        return Response::json(['users' => []]);
    }

    public function show(Request $request): Response
    {
        $id = $request->param('id');
        // Get user by ID
        return Response::json(['user' => ['id' => $id]]);
    }

    public function store(Request $request): Response
    {
        $data = $request->all();
        // Create new user
        return Response::json(['message' => 'User created'], 201);
    }

    public function update(Request $request): Response
    {
        $id = $request->param('id');
        $data = $request->all();
        // Update user
        return Response::json(['message' => 'User updated']);
    }

    public function destroy(Request $request): Response
    {
        $id = $request->param('id');
        // Delete user
        return Response::json(['message' => 'User deleted']);
    }
}
```

### Controller Best Practices

1. **Single Responsibility**: Each controller should handle one resource
2. **Dependency Injection**: Use constructor injection for dependencies
3. **Validation**: Validate input data before processing
4. **Error Handling**: Return appropriate HTTP status codes

---

## Models

### Basic Model

```php
<?php

namespace App\Models;

class User extends Model
{
    protected array $fillable = ['name', 'email', 'password'];

    public function save(): bool
    {
        // Save logic
        return true;
    }

    public static function find(int $id): ?self
    {
        // Find logic
        return new self(['id' => $id]);
    }

    public static function all(): array
    {
        // Get all records
        return [];
    }
}
```

### Using Models

```php
// Create new model
$user = new User(['name' => 'John', 'email' => 'john@example.com']);
$user->save();

// Find by ID
$user = User::find(1);

// Get all records
$users = User::all();

// Update model
$user = User::find(1);
$user->fill(['name' => 'Jane']);
$user->save();
```

---

## Middleware

### Creating Middleware

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class CustomMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Before request processing
        if (!$this->isValid($request)) {
            return $this->response()->json(['error' => 'Invalid request'], 400);
        }

        // Process request
        $response = $next($request);

        // After request processing
        $response->header('X-Custom-Header', 'Custom Value');

        return $response;
    }

    private function isValid(Request $request): bool
    {
        // Validation logic
        return true;
    }
}
```

### Registering Middleware

In your Application bootstrap:

```php
$app->getRouter()->registerMiddleware('custom', CustomMiddleware::class);
```

### Using Middleware

```php
// Single middleware
Route::get('/protected', 'Controller@method')->middleware('auth');

// Multiple middleware
Route::get('/admin', 'Controller@method')->middleware(['auth', 'admin']);

// Group middleware
Route::group(['middleware' => ['cors']], function() {
    Route::get('/public', 'Controller@method');
});
```

### Built-in Middleware

#### CORS Middleware
Handles Cross-Origin Resource Sharing:
```php
Route::group(['middleware' => ['cors']], function() {
    // Your routes
});
```

#### Auth Middleware
Validates Bearer tokens:
```php
Route::get('/profile', 'UserController@profile')->middleware('auth');
```

#### Throttle Middleware
Rate limiting (default: 60 requests per minute):
```php
Route::post('/contact', 'ContactController@store')->middleware('throttle');
```

---

## Request & Response

### Request Object

```php
public function handle(Request $request)
{
    // HTTP method
    $method = $request->method(); // GET, POST, PUT, etc.

    // URI
    $uri = $request->uri(); // /api/users/123

    // Query parameters
    $page = $request->query('page', 1);
    $allQuery = $request->query();

    // Request body/input
    $name = $request->input('name');
    $allInput = $request->all();

    // Headers
    $auth = $request->header('Authorization');
    $contentType = $request->header('Content-Type');

    // Route parameters
    $id = $request->param('id');

    // Bearer token
    $token = $request->bearerToken();

    // Check if JSON request
    $isJson = $request->isJson();
}
```

### Response Object

```php
// JSON response
return Response::json(['message' => 'Success']);

// JSON with status code
return Response::json(['error' => 'Not found'], 404);

// Text response
return Response::text('Hello World');

// HTML response
return Response::html('<h1>Hello</h1>');

// Response with headers
return Response::json(['data' => $data])
    ->header('X-Custom', 'Value')
    ->status(201);
```

### Helper Functions

```php
// Quick response
return response()->json(['message' => 'Success']);

// Abort with error
return abort(404, 'Resource not found');

// Environment variables
$debug = env('APP_DEBUG', false);

// Configuration
$dbHost = config('database.host');

// Current timestamp
$now = now();
```

---

## Error Handling

### Standard Error Format

All errors follow a consistent JSON format:

```json
{
  "error": "Validation Error",
  "message": "The name field is required",
  "code": 422
}
```

### Error Responses

```php
// Not found
return Response::json(['error' => 'User not found'], 404);

// Validation error
return Response::json([
    'error' => 'Validation failed',
    'message' => 'Invalid input data',
    'errors' => [
        'email' => ['Email is required', 'Email must be valid']
    ]
], 422);

// Unauthorized
return Response::json(['error' => 'Unauthorized'], 401);

// Using helper
return abort(500, 'Internal server error');
```

### HTTP Status Codes

- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Unprocessable Entity
- `429` - Too Many Requests
- `500` - Internal Server Error

---

## Testing

### PHPUnit Configuration

Tests are configured in `phpunit.xml` and located in the `tests/` directory.

### Running Tests

```bash
# Run all tests
composer test

# Run specific test
vendor/bin/phpunit tests/RequestTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/
```

### Writing Tests

```php
<?php

use PHPUnit\Framework\TestCase;
use Apileon\Http\Request;

class RequestTest extends TestCase
{
    public function testRequestMethod()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        $request = new Request();
        
        $this->assertEquals('POST', $request->method());
    }

    public function testQueryParameters()
    {
        $_GET = ['page' => '1'];
        $request = new Request();
        
        $this->assertEquals('1', $request->query('page'));
    }
}
```

### Test Structure

```
tests/
├── RequestTest.php
├── ResponseTest.php
├── RouterTest.php
└── Controllers/
    └── UserControllerTest.php
```

---

## Deployment

### Production Setup

1. **Web Server Configuration**

   **Apache (.htaccess in public/):**
   ```apache
   RewriteEngine On
   RewriteCond %{REQUEST_FILENAME} !-f
   RewriteCond %{REQUEST_FILENAME} !-d
   RewriteRule ^(.*)$ index.php [QSA,L]
   ```

   **Nginx:**
   ```nginx
   location / {
       try_files $uri $uri/ /index.php?$query_string;
   }
   ```

2. **Environment Configuration**
   ```env
   APP_ENV=production
   APP_DEBUG=false
   APP_KEY=your-secure-production-key
   ```

3. **Optimize for Production**
   ```bash
   composer install --no-dev --optimize-autoloader
   ```

### Security Considerations

- Set `APP_DEBUG=false` in production
- Use strong `APP_KEY` values
- Validate all input data
- Implement proper authentication
- Use HTTPS in production
- Keep dependencies updated

### Performance Tips

- Enable OPcache
- Use HTTP caching headers
- Implement database query optimization
- Consider using a reverse proxy (Nginx)
- Monitor application performance

---

## Advanced Topics

### Custom Helper Functions

Add to `src/Support/functions.php`:

```php
if (!function_exists('custom_helper')) {
    function custom_helper($value) {
        return "processed: " . $value;
    }
}
```

### Extending the Framework

Create custom components by extending base classes:

```php
namespace App\Http;

use Apileon\Http\Response as BaseResponse;

class Response extends BaseResponse
{
    public static function success($data = null): self
    {
        return self::json([
            'success' => true,
            'data' => $data
        ]);
    }
}
```

### Database Integration

While Apileon doesn't include a built-in ORM, you can integrate with popular solutions:

```bash
composer require illuminate/database
# or
composer require doctrine/orm
```

## ✅ **Best Practices** {#-best-practices}

> **📖 For comprehensive development guidance including where to add your functions, do's and don'ts, and practical examples, see our detailed [Developer Guide](DEVELOPER_GUIDE.md)!**

### 1. Project Organization

**Controller Organization:**
```php
// Good: Logical grouping
app/Controllers/
├── Api/
│   ├── V1/
│   │   ├── UserController.php
│   │   └── PostController.php
│   └── V2/
│       └── UserController.php
├── Auth/
│   ├── LoginController.php
│   └── RegisterController.php
└── Admin/
    └── DashboardController.php
```

**Route Organization:**
```php
// routes/api.php - Keep related routes together
Route::group(['prefix' => 'api/v1'], function() {
    // User management
    Route::group(['prefix' => 'users'], function() {
        Route::get('/', 'UserController@index');
        Route::post('/', 'UserController@store');
        Route::get('/{id}', 'UserController@show');
        Route::put('/{id}', 'UserController@update');
        Route::delete('/{id}', 'UserController@destroy');
    });
    
    // Posts
    Route::group(['prefix' => 'posts'], function() {
        Route::get('/', 'PostController@index');
        Route::post('/', 'PostController@store')->middleware('auth');
    });
});
```

### 2. API Design Principles

**RESTful URLs:**
```php
// Good
GET    /api/users           # List users
GET    /api/users/123       # Show user
POST   /api/users           # Create user
PUT    /api/users/123       # Update user
DELETE /api/users/123       # Delete user

// Avoid
GET    /api/get-users
POST   /api/create-user
GET    /api/user-details/123
```

**Consistent Response Format:**
```php
// Success responses
return Response::json([
    'success' => true,
    'data' => $result,
    'meta' => [
        'total' => 100,
        'page' => 1,
        'per_page' => 10
    ]
]);

// Error responses
return Response::json([
    'success' => false,
    'error' => 'Validation failed',
    'message' => 'The given data was invalid',
    'errors' => [
        'email' => ['Email is required', 'Email must be valid']
    ]
], 422);
```

### 3. Security Best Practices

**Input Validation:**
```php
public function store(Request $request): Response
{
    $data = $request->all();
    
    // Validate required fields
    $errors = [];
    
    if (empty($data['email'])) {
        $errors['email'][] = 'Email is required';
    } elseif (!filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        $errors['email'][] = 'Email must be valid';
    }
    
    if (empty($data['name'])) {
        $errors['name'][] = 'Name is required';
    } elseif (strlen($data['name']) < 2) {
        $errors['name'][] = 'Name must be at least 2 characters';
    }
    
    if (!empty($errors)) {
        return Response::json([
            'error' => 'Validation failed',
            'errors' => $errors
        ], 422);
    }
    
    // Process valid data...
}
```

**Authentication Best Practices:**
```php
// Use middleware for protection
Route::group(['middleware' => ['auth']], function() {
    Route::get('/sensitive-data', 'DataController@show');
});

// Validate tokens properly
class AuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return $this->unauthorizedResponse('Token required');
        }
        
        // Validate token format
        if (!$this->isValidTokenFormat($token)) {
            return $this->unauthorizedResponse('Invalid token format');
        }
        
        // Check token in database/cache
        if (!$this->tokenExists($token)) {
            return $this->unauthorizedResponse('Invalid token');
        }
        
        // Check token expiration
        if ($this->isTokenExpired($token)) {
            return $this->unauthorizedResponse('Token expired');
        }
        
        return $next($request);
    }
    
    private function unauthorizedResponse(string $message): Response
    {
        return $this->response()->json([
            'error' => 'Unauthorized',
            'message' => $message
        ], 401);
    }
}
```

### 4. Performance Optimization

**Response Caching:**
```php
class CacheMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Only cache GET requests
        if ($request->method() !== 'GET') {
            return $next($request);
        }
        
        $cacheKey = 'api_' . md5($request->uri() . serialize($request->query()));
        
        // Check cache
        if ($cachedResponse = $this->getFromCache($cacheKey)) {
            return $cachedResponse->header('X-Cache', 'HIT');
        }
        
        $response = $next($request);
        
        // Cache successful responses
        if ($response->getStatusCode() === 200) {
            $this->putInCache($cacheKey, $response, 300); // 5 minutes
            $response->header('X-Cache', 'MISS');
        }
        
        return $response;
    }
}
```

**Database Query Optimization:**
```php
// Good: Specific queries
public function index(Request $request): Response
{
    $page = (int) $request->query('page', 1);
    $limit = (int) $request->query('limit', 10);
    $limit = min($limit, 100); // Cap at 100
    
    $offset = ($page - 1) * $limit;
    
    // Only select needed fields
    $users = $this->db->query(
        "SELECT id, name, email, created_at FROM users LIMIT ? OFFSET ?",
        [$limit, $offset]
    );
    
    return Response::json([
        'data' => $users,
        'meta' => [
            'page' => $page,
            'per_page' => $limit,
            'total' => $this->getUserCount()
        ]
    ]);
}
```

### 5. Error Handling

**Consistent Error Responses:**
```php
// Create a base controller with error handling
abstract class BaseController
{
    protected function errorResponse(string $message, int $code = 400, array $errors = []): Response
    {
        $response = [
            'success' => false,
            'error' => $this->getErrorTitle($code),
            'message' => $message
        ];
        
        if (!empty($errors)) {
            $response['errors'] = $errors;
        }
        
        return Response::json($response, $code);
    }
    
    protected function successResponse($data = null, string $message = null): Response
    {
        $response = ['success' => true];
        
        if ($message) {
            $response['message'] = $message;
        }
        
        if ($data !== null) {
            $response['data'] = $data;
        }
        
        return Response::json($response);
    }
    
    private function getErrorTitle(int $code): string
    {
        return match($code) {
            400 => 'Bad Request',
            401 => 'Unauthorized',
            403 => 'Forbidden',
            404 => 'Not Found',
            422 => 'Validation Error',
            429 => 'Too Many Requests',
            500 => 'Internal Server Error',
            default => 'Error'
        };
    }
}
```

### 6. Testing Strategy

**Test Structure:**
```php
// tests/Feature/UserApiTest.php
class UserApiTest extends TestCase
{
    public function testCreateUser()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $this->assertEquals(201, $response['status']);
        $this->assertEquals('User created successfully', $response['body']['message']);
        $this->assertArrayHasKey('data', $response['body']);
    }
    
    public function testCreateUserValidation()
    {
        $response = $this->post('/api/users', ['name' => '']); // Invalid data
        
        $this->assertEquals(422, $response['status']);
        $this->assertEquals('Validation failed', $response['body']['error']);
        $this->assertArrayHasKey('errors', $response['body']);
    }
}
```

**Environment-Based Testing:**
```php
// tests/TestCase.php
abstract class TestCase extends PHPUnit\Framework\TestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        
        // Set test environment
        $_ENV['APP_ENV'] = 'testing';
        $_ENV['APP_DEBUG'] = 'true';
        
        $this->resetGlobals();
    }
    
    protected function resetGlobals(): void
    {
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
    }
}
```

### 7. Deployment Guidelines

**Production Configuration:**
```env
# .env (production)
APP_ENV=production
APP_DEBUG=false
APP_KEY=your-super-secure-production-key

# Secure database settings
DB_HOST=secure-db-host
DB_USERNAME=limited-user
DB_PASSWORD=complex-secure-password

# Logging
LOG_LEVEL=warning
```

**Security Headers:**
```php
// Add to your middleware or base controller
class SecurityHeadersMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        
        return $response
            ->header('X-Content-Type-Options', 'nosniff')
            ->header('X-Frame-Options', 'DENY')
            ->header('X-XSS-Protection', '1; mode=block')
            ->header('Strict-Transport-Security', 'max-age=31536000; includeSubDomains')
            ->header('Content-Security-Policy', "default-src 'self'");
    }
}
```

---

## ❌ **Common Mistakes** {#-common-mistakes}

> **🚨 For detailed anti-patterns and how to avoid them, see our [Developer Guide](DEVELOPER_GUIDE.md) with comprehensive do's and don'ts!**

### **1. Architecture Mistakes**

#### ❌ **Putting Business Logic in Controllers**
```php
// BAD: Complex logic in controller
public function calculatePrice(Request $request): Response
{
    $product = Product::find($request->param('id'));
    $price = $product->base_price;
    
    // 50 lines of complex pricing logic here...
    if ($user->isVip()) {
        $price *= 0.9;
    }
    // More complex calculations...
    
    return Response::json(['price' => $price]);
}
```

#### ✅ **Move to Model or Service**
```php
// GOOD: Clean controller, logic in model
public function calculatePrice(Request $request): Response
{
    $product = Product::find($request->param('id'));
    $price = $product->calculatePrice($request->user());
    
    return success_response(['price' => $price]);
}
```

### **2. Security Mistakes**

#### ❌ **Not Validating Input**
```php
// BAD: Direct database insertion
public function store(Request $request): Response
{
    // Dangerous! No validation
    Product::create($request->all());
}
```

#### ✅ **Always Validate**
```php
// GOOD: Proper validation
public function store(Request $request): Response
{
    $validatedData = Product::validateForCreation($request->all());
    $product = Product::create($validatedData);
    
    return success_response($product->toArray(), 'Product created', 201);
}
```

### **3. Performance Mistakes**

#### ❌ **Not Using Caching**
```php
// BAD: Expensive operation on every request
public function dashboard(): Response
{
    $stats = [
        'total_users' => User::count(),
        'total_products' => Product::count(),
        'revenue' => Order::sum('total'),
        'popular_products' => Product::orderBy('views', 'DESC')->limit(10)->get()
    ];
    
    return success_response($stats);
}
```

#### ✅ **Use Caching for Expensive Operations**
```php
// GOOD: Cache expensive computations
public function dashboard(): Response
{
    $stats = cache_remember('dashboard_stats', function() {
        return [
            'total_users' => User::count(),
            'total_products' => Product::count(),
            'revenue' => Order::sum('total'),
            'popular_products' => Product::orderBy('views', 'DESC')->limit(10)->get()
        ];
    }, 300); // Cache for 5 minutes
    
    return success_response($stats);
}
```

### **4. Database Mistakes**

#### ❌ **No Pagination**
```php
// BAD: Could return millions of records
public function index(): Response
{
    return success_response(Product::all());
}
```

#### ✅ **Always Paginate Large Datasets**
```php
// GOOD: Paginated results
public function index(Request $request): Response
{
    $page = max(1, (int) $request->query('page', 1));
    $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
    
    $products = Product::paginate($perPage, $page);
    
    return success_response($products);
}
```

### **5. Error Handling Mistakes**

#### ❌ **No Error Handling**
```php
// BAD: Errors will crash the application
public function show(Request $request): Response
{
    $product = Product::find($request->param('id')); // Could be null
    return success_response($product->toArray()); // Will crash if null
}
```

#### ✅ **Proper Error Handling**
```php
// GOOD: Handle all error cases
public function show(Request $request): Response
{
    try {
        $id = (int) $request->param('id');
        
        if ($id <= 0) {
            return error_response('Invalid ID', 'Product ID must be positive', 400);
        }
        
        $product = Product::find($id);
        
        if (!$product) {
            return error_response('Not found', 'Product not found', 404);
        }
        
        return success_response($product->toArray());
        
    } catch (\Exception $e) {
        return error_response(
            'Server error',
            app_debug() ? $e->getMessage() : 'Internal server error',
            500
        );
    }
}
```

### **6. Testing Mistakes**

#### ❌ **No Tests**
```php
// BAD: No tests means broken features go unnoticed
```

#### ✅ **Write Tests for Critical Functions**
```php
// GOOD: Test important functionality
public function testProductValidation()
{
    $invalidData = ['name' => '', 'price' => -10];
    
    $this->expectException(ValidationException::class);
    Product::validateForCreation($invalidData);
}
```

---

**💡 Remember:** For comprehensive guidance with more examples and practical advice, check out our [Developer Guide](DEVELOPER_GUIDE.md)!

This comprehensive documentation provides developers with everything they need to build robust, secure, and scalable REST APIs using the Apileon framework.
