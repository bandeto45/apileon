# Apileon Framework Documentation

## Table of Contents

1. [Getting Started](#getting-started)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Routing](#routing)
5. [Controllers](#controllers)
6. [Models](#models)
7. [Middleware](#middleware)
8. [Request & Response](#request--response)
9. [Error Handling](#error-handling)
10. [Testing](#testing)
11. [Deployment](#deployment)

---

## Getting Started

Apileon is a lightweight PHP framework designed exclusively for REST API development. It provides a clean, simple API while maintaining enterprise-level capabilities.

### Requirements

- PHP 8.1 or higher
- Composer
- Web server (Apache, Nginx, or PHP built-in server)

### Quick Setup

```bash
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup.sh
composer serve
```

---

## Installation

### Manual Installation

1. **Clone the repository:**
   ```bash
   git clone https://github.com/bandeto45/apileon.git my-api
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
   php -S localhost:8000 -t public
   ```

### Automated Setup

Use the provided setup script:
```bash
./setup.sh
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

This documentation provides a comprehensive guide to building REST APIs with the Apileon framework. For more examples and advanced usage, check the example controllers and test files in the project.
