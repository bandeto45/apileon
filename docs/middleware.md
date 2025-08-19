# Apileon Middleware Guide

## Introduction

Middleware provides a convenient mechanism for filtering HTTP requests entering your application. Middleware can perform tasks such as authentication, CORS handling, rate limiting, and request logging.

## How Middleware Works

Middleware operates as layers around your application's core request handling. Each middleware layer can:

1. **Examine the request** before it reaches your route handler
2. **Modify the request** or terminate it early
3. **Process the response** after your route handler executes
4. **Add headers** or modify the response

```
Request → Middleware 1 → Middleware 2 → Route Handler → Middleware 2 → Middleware 1 → Response
```

## Creating Middleware

### Basic Middleware Structure

All middleware must extend the `Apileon\Http\Middleware` class and implement the `handle` method:

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class ExampleMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Before request processing
        
        // Continue to next middleware or route handler
        $response = $next($request);
        
        // After request processing
        
        return $response;
    }
}
```

### Authentication Middleware Example

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return $this->response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication token is required'
            ], 401);
        }
        
        // Validate token (implement your logic)
        if (!$this->isValidToken($token)) {
            return $this->response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid authentication token'
            ], 401);
        }
        
        // Add user to request for later use
        $user = $this->getUserFromToken($token);
        $request->setUser($user);
        
        return $next($request);
    }
    
    private function isValidToken(string $token): bool
    {
        // Implement token validation logic
        // This could check against database, JWT validation, etc.
        return !empty($token) && strlen($token) > 10;
    }
    
    private function getUserFromToken(string $token): array
    {
        // Implement user retrieval logic
        return ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'];
    }
}
```

## Built-in Middleware

### CORS Middleware

Handles Cross-Origin Resource Sharing automatically:

```php
// Automatically handles:
// - Preflight OPTIONS requests
// - CORS headers on all responses
// - Configurable allowed origins, methods, headers

Route::group(['middleware' => ['cors']], function() {
    Route::get('/api/data', 'DataController@index');
});
```

**Features:**
- Handles preflight requests
- Adds appropriate CORS headers
- Supports wildcard origins (`*`)
- Configurable methods and headers

### Auth Middleware

Validates Bearer tokens:

```php
Route::get('/profile', 'UserController@profile')->middleware('auth');

// Usage with Bearer token
curl -H "Authorization: Bearer your-token-here" http://localhost:8000/profile
```

### Throttle Middleware

Implements rate limiting:

```php
// Default: 60 requests per minute per IP
Route::post('/contact', 'ContactController@store')->middleware('throttle');

// Custom limits (if extended)
Route::post('/api/upload', 'UploadController@store')->middleware('throttle:10,1');
```

**Features:**
- IP-based rate limiting
- Configurable limits and time windows
- Appropriate HTTP headers (`X-RateLimit-*`)
- 429 status code when limit exceeded

## Registering Middleware

### Global Registration

Register middleware in your application bootstrap:

```php
// In src/Foundation/Application.php or bootstrap file
$router->registerMiddleware('custom', CustomMiddleware::class);
$router->registerMiddleware('admin', AdminMiddleware::class);
$router->registerMiddleware('json', JsonOnlyMiddleware::class);
```

### Built-in Registration

Built-in middleware is automatically registered:

```php
// These are automatically available:
'cors'     => CorsMiddleware::class
'auth'     => AuthMiddleware::class
'throttle' => ThrottleMiddleware::class
```

## Applying Middleware

### Single Route

```php
Route::get('/protected', 'Controller@method')->middleware('auth');
```

### Multiple Middleware

```php
Route::get('/admin', 'AdminController@dashboard')
    ->middleware(['auth', 'admin']);
```

### Route Groups

```php
// All routes in group get the middleware
Route::group(['middleware' => ['auth']], function() {
    Route::get('/profile', 'UserController@profile');
    Route::put('/profile', 'UserController@updateProfile');
    Route::get('/settings', 'UserController@settings');
});

// Multiple middleware on group
Route::group(['middleware' => ['auth', 'admin']], function() {
    Route::get('/admin/users', 'AdminController@users');
    Route::delete('/admin/users/{id}', 'AdminController@deleteUser');
});
```

## Advanced Middleware Examples

### Logging Middleware

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class LoggingMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $startTime = microtime(true);
        
        // Log request
        $this->logRequest($request);
        
        $response = $next($request);
        
        $endTime = microtime(true);
        $duration = ($endTime - $startTime) * 1000; // milliseconds
        
        // Log response
        $this->logResponse($request, $response, $duration);
        
        return $response;
    }
    
    private function logRequest(Request $request): void
    {
        error_log(sprintf(
            '[%s] %s %s - IP: %s, User-Agent: %s',
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->uri(),
            $_SERVER['REMOTE_ADDR'] ?? 'unknown',
            $request->header('User-Agent') ?? 'unknown'
        ));
    }
    
    private function logResponse(Request $request, Response $response, float $duration): void
    {
        error_log(sprintf(
            '[%s] %s %s - Status: %d, Duration: %.2fms',
            date('Y-m-d H:i:s'),
            $request->method(),
            $request->uri(),
            $response->getStatusCode(),
            $duration
        ));
    }
}
```

### JSON Only Middleware

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class JsonOnlyMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Only allow JSON requests for POST, PUT, PATCH
        $requiresJson = in_array($request->method(), ['POST', 'PUT', 'PATCH']);
        
        if ($requiresJson && !$request->isJson()) {
            return $this->response()->json([
                'error' => 'Bad Request',
                'message' => 'This endpoint only accepts JSON requests'
            ], 400);
        }
        
        $response = $next($request);
        
        // Ensure response is JSON
        $response->header('Content-Type', 'application/json');
        
        return $response;
    }
}
```

### Admin Role Middleware

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class AdminMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // This middleware should run after AuthMiddleware
        $user = $request->getUser();
        
        if (!$user) {
            return $this->response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication required'
            ], 401);
        }
        
        if (!$this->isAdmin($user)) {
            return $this->response()->json([
                'error' => 'Forbidden',
                'message' => 'Admin access required'
            ], 403);
        }
        
        return $next($request);
    }
    
    private function isAdmin(array $user): bool
    {
        return isset($user['role']) && $user['role'] === 'admin';
    }
}
```

### API Version Middleware

```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class ApiVersionMiddleware extends Middleware
{
    private string $requiredVersion;
    
    public function __construct(string $version = 'v1')
    {
        $this->requiredVersion = $version;
    }
    
    public function handle(Request $request, callable $next): Response
    {
        $acceptHeader = $request->header('Accept');
        $version = $this->extractVersion($acceptHeader);
        
        if ($version !== $this->requiredVersion) {
            return $this->response()->json([
                'error' => 'Unsupported API Version',
                'message' => "API version {$this->requiredVersion} required",
                'requested' => $version
            ], 400);
        }
        
        $response = $next($request);
        
        $response->header('X-API-Version', $this->requiredVersion);
        
        return $response;
    }
    
    private function extractVersion(?string $acceptHeader): string
    {
        if (!$acceptHeader) {
            return 'v1'; // default
        }
        
        if (preg_match('/application\/vnd\.api\.v(\d+)\+json/', $acceptHeader, $matches)) {
            return 'v' . $matches[1];
        }
        
        return 'v1'; // default
    }
}
```

## Middleware Order

Middleware executes in the order it's defined:

```php
Route::get('/resource', 'Controller@method')
    ->middleware(['first', 'second', 'third']);

// Execution order:
// Request → first → second → third → Controller → third → second → first → Response
```

### Best Practice Order

```php
Route::group(['middleware' => [
    'cors',      // Handle CORS first
    'throttle',  // Rate limiting early
    'auth',      // Authentication
    'admin',     // Authorization
    'logging'    // Logging last
]], function() {
    // Routes
});
```

## Testing Middleware

### Unit Testing

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use Apileon\Http\Request;

class AuthMiddlewareTest extends TestCase
{
    public function testAuthenticationRequired()
    {
        $middleware = new AuthMiddleware();
        $request = new Request();
        // Request without Authorization header
        
        $next = function($request) {
            return response()->json(['data' => 'protected']);
        };
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals(401, $response->getStatusCode());
    }
    
    public function testValidAuthentication()
    {
        $middleware = new AuthMiddleware();
        
        // Mock request with valid token
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token-here';
        $request = new Request();
        
        $next = function($request) {
            return response()->json(['data' => 'protected']);
        };
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

### Integration Testing

```php
class MiddlewareIntegrationTest extends TestCase
{
    public function testProtectedEndpoint()
    {
        // Test without auth
        $response = $this->get('/api/v1/profile');
        $this->assertEquals(401, $response->getStatusCode());
        
        // Test with auth
        $response = $this->get('/api/v1/profile', [
            'Authorization' => 'Bearer valid-token'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testRateLimiting()
    {
        // Make multiple requests
        for ($i = 0; $i < 65; $i++) {
            $response = $this->post('/api/contact', ['message' => 'test']);
        }
        
        // Should be rate limited
        $this->assertEquals(429, $response->getStatusCode());
    }
}
```

## Best Practices

### 1. Single Responsibility

Each middleware should have one clear purpose:

```php
// Good: Single purpose
class AuthMiddleware extends Middleware { /* auth only */ }
class CorsMiddleware extends Middleware { /* CORS only */ }

// Avoid: Multiple responsibilities
class AuthAndCorsMiddleware extends Middleware { /* auth + CORS */ }
```

### 2. Early Termination

Terminate early when possible to save processing:

```php
public function handle(Request $request, callable $next): Response
{
    // Check and terminate early if needed
    if (!$this->isValid($request)) {
        return $this->response()->json(['error' => 'Invalid'], 400);
    }
    
    // Continue processing
    return $next($request);
}
```

### 3. Proper Error Responses

Return consistent error formats:

```php
public function handle(Request $request, callable $next): Response
{
    if (!$authenticated) {
        return $this->response()->json([
            'error' => 'Unauthorized',
            'message' => 'Authentication required',
            'code' => 401
        ], 401);
    }
    
    return $next($request);
}
```

### 4. Configuration

Make middleware configurable:

```php
class ThrottleMiddleware extends Middleware
{
    public function __construct(
        private int $maxAttempts = 60,
        private int $decayMinutes = 1
    ) {}
    
    // Use $this->maxAttempts and $this->decayMinutes
}
```

### 5. Performance Considerations

- Keep middleware lightweight
- Avoid heavy database queries in middleware
- Cache expensive operations
- Use early termination when possible

This middleware guide provides comprehensive coverage of Apileon's middleware system, from basic concepts to advanced implementations for building robust API security and functionality layers.
