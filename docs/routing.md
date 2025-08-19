# Apileon Routing Guide

## Overview

Apileon's routing system is designed to be simple yet powerful, providing all the features needed for REST API development.

## Basic Route Definition

Routes are defined in the `routes/api.php` file using the `Route` facade:

```php
use Apileon\Routing\Route;

Route::get('/hello', function() {
    return ['message' => 'Hello World'];
});
```

## HTTP Methods

### Supported Methods

```php
Route::get('/resource', $handler);       // GET
Route::post('/resource', $handler);      // POST
Route::put('/resource', $handler);       // PUT
Route::patch('/resource', $handler);     // PATCH
Route::delete('/resource', $handler);    // DELETE
Route::options('/resource', $handler);   // OPTIONS
Route::any('/resource', $handler);       // All methods
```

### Method Examples

```php
// Get all users
Route::get('/users', function() {
    return ['users' => User::all()];
});

// Create user
Route::post('/users', function($request) {
    $user = new User($request->all());
    $user->save();
    return ['message' => 'User created', 'user' => $user];
});

// Update user
Route::put('/users/{id}', function($request) {
    $user = User::find($request->param('id'));
    $user->update($request->all());
    return ['message' => 'User updated', 'user' => $user];
});

// Delete user
Route::delete('/users/{id}', function($request) {
    User::find($request->param('id'))->delete();
    return ['message' => 'User deleted'];
});
```

## Route Parameters

### Required Parameters

Use curly braces to define route parameters:

```php
// Single parameter
Route::get('/users/{id}', function($request) {
    return User::find($request->param('id'));
});

// Multiple parameters
Route::get('/users/{userId}/posts/{postId}', function($request) {
    return [
        'user' => User::find($request->param('userId')),
        'post' => Post::find($request->param('postId'))
    ];
});
```

### Parameter Patterns

While Apileon doesn't have built-in parameter constraints, you can validate in your handlers:

```php
Route::get('/users/{id}', function($request) {
    $id = $request->param('id');
    
    if (!is_numeric($id)) {
        return abort(400, 'ID must be numeric');
    }
    
    return User::find($id);
});
```

## Route Groups

Route groups allow you to share route attributes across multiple routes.

### Prefix Groups

```php
Route::group(['prefix' => 'api'], function() {
    Route::get('/users', 'UserController@index');     // /api/users
    Route::get('/posts', 'PostController@index');     // /api/posts
});

// Nested groups
Route::group(['prefix' => 'api'], function() {
    Route::group(['prefix' => 'v1'], function() {
        Route::get('/users', 'UserController@index'); // /api/v1/users
    });
});
```

### Middleware Groups

```php
Route::group(['middleware' => ['auth']], function() {
    Route::get('/profile', 'UserController@profile');
    Route::put('/profile', 'UserController@updateProfile');
});

// Multiple middleware
Route::group(['middleware' => ['auth', 'admin']], function() {
    Route::get('/admin/dashboard', 'AdminController@dashboard');
});
```

### Combined Groups

```php
Route::group(['prefix' => 'api/v1', 'middleware' => ['auth']], function() {
    Route::get('/profile', 'UserController@profile');
    Route::put('/profile', 'UserController@updateProfile');
    Route::get('/settings', 'UserController@settings');
});
```

## Controller Routes

### Basic Controller Routes

```php
// Class@method syntax
Route::get('/users', 'App\Controllers\UserController@index');

// Short syntax (assumes App\Controllers namespace)
Route::get('/users', 'UserController@index');
```

### RESTful Controller Routes

```php
// Full RESTful resource
Route::get('/users', 'UserController@index');        // GET /users
Route::get('/users/{id}', 'UserController@show');    // GET /users/123
Route::post('/users', 'UserController@store');       // POST /users
Route::put('/users/{id}', 'UserController@update');  // PUT /users/123
Route::delete('/users/{id}', 'UserController@destroy'); // DELETE /users/123
```

## Middleware on Routes

### Single Middleware

```php
Route::get('/protected', 'Controller@method')->middleware('auth');
```

### Multiple Middleware

```php
Route::get('/admin', 'Controller@method')->middleware(['auth', 'admin']);
```

### Middleware with Parameters

While not built into the basic system, you can extend middleware to accept parameters:

```php
// Custom implementation
Route::get('/limited', 'Controller@method')->middleware('throttle:10,1');
```

## Route Examples

### API Versioning

```php
// v1 API
Route::group(['prefix' => 'api/v1'], function() {
    Route::get('/users', 'V1\UserController@index');
    Route::post('/users', 'V1\UserController@store');
});

// v2 API
Route::group(['prefix' => 'api/v2'], function() {
    Route::get('/users', 'V2\UserController@index');
    Route::post('/users', 'V2\UserController@store');
});
```

### Protected API Routes

```php
// Public routes
Route::get('/status', function() {
    return ['status' => 'ok', 'timestamp' => time()];
});

Route::post('/auth/login', 'AuthController@login');
Route::post('/auth/register', 'AuthController@register');

// Protected routes
Route::group(['middleware' => ['auth']], function() {
    Route::get('/user/profile', 'UserController@profile');
    Route::put('/user/profile', 'UserController@updateProfile');
    Route::post('/auth/logout', 'AuthController@logout');
});

// Admin routes
Route::group(['prefix' => 'admin', 'middleware' => ['auth', 'admin']], function() {
    Route::get('/users', 'Admin\UserController@index');
    Route::delete('/users/{id}', 'Admin\UserController@destroy');
});
```

### Content Management API

```php
// Posts API
Route::group(['prefix' => 'api/posts'], function() {
    Route::get('/', 'PostController@index');
    Route::get('/{id}', 'PostController@show');
    
    // Protected post operations
    Route::group(['middleware' => ['auth']], function() {
        Route::post('/', 'PostController@store');
        Route::put('/{id}', 'PostController@update');
        Route::delete('/{id}', 'PostController@destroy');
    });
});

// Comments API
Route::group(['prefix' => 'api/posts/{postId}/comments'], function() {
    Route::get('/', 'CommentController@index');
    Route::post('/', 'CommentController@store')->middleware('auth');
});
```

### File Upload Routes

```php
Route::group(['middleware' => ['auth']], function() {
    Route::post('/upload/avatar', 'UploadController@avatar');
    Route::post('/upload/document', 'UploadController@document');
    Route::delete('/files/{id}', 'FileController@destroy');
});
```

## Route Testing

### Testing Routes

```php
// In your tests
class RouteTest extends TestCase
{
    public function testBasicRoute()
    {
        $response = $this->get('/hello');
        $this->assertEquals(200, $response->getStatusCode());
        
        $data = json_decode($response->getContent(), true);
        $this->assertEquals('Hello World', $data['message']);
    }
    
    public function testRouteWithParameter()
    {
        $response = $this->get('/users/123');
        $this->assertEquals(200, $response->getStatusCode());
    }
    
    public function testProtectedRoute()
    {
        // Without auth
        $response = $this->get('/profile');
        $this->assertEquals(401, $response->getStatusCode());
        
        // With auth
        $response = $this->get('/profile', [
            'Authorization' => 'Bearer token123'
        ]);
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

## Route Caching

For performance in production, consider implementing route caching:

```php
// Example implementation
class RouteCache
{
    public static function cache()
    {
        // Generate route cache file
        $routes = require 'routes/api.php';
        file_put_contents('cache/routes.php', serialize($routes));
    }
}
```

## Best Practices

### 1. RESTful Conventions

Follow REST conventions for predictable APIs:

```php
GET    /users          # List users
GET    /users/{id}     # Show user
POST   /users          # Create user
PUT    /users/{id}     # Update user (full)
PATCH  /users/{id}     # Update user (partial)
DELETE /users/{id}     # Delete user
```

### 2. Consistent Naming

Use consistent naming patterns:

```php
// Good
Route::get('/users', 'UserController@index');
Route::get('/posts', 'PostController@index');
Route::get('/comments', 'CommentController@index');

// Avoid
Route::get('/get-users', 'UserController@getUsers');
Route::get('/user-list', 'UserController@userList');
```

### 3. Logical Grouping

Group related routes together:

```php
// User management
Route::group(['prefix' => 'users'], function() {
    Route::get('/', 'UserController@index');
    Route::post('/', 'UserController@store');
    Route::get('/{id}', 'UserController@show');
    Route::put('/{id}', 'UserController@update');
    Route::delete('/{id}', 'UserController@destroy');
});
```

### 4. Middleware Usage

Apply middleware strategically:

```php
// Global middleware in Application
// Route-specific middleware for special cases
Route::get('/public-data', 'DataController@public');
Route::get('/private-data', 'DataController@private')->middleware('auth');
```

### 5. Error Handling

Handle route errors gracefully:

```php
Route::get('/users/{id}', function($request) {
    $id = $request->param('id');
    
    if (!is_numeric($id)) {
        return abort(400, 'Invalid user ID format');
    }
    
    $user = User::find($id);
    
    if (!$user) {
        return abort(404, 'User not found');
    }
    
    return $user;
});
```

This routing guide covers all aspects of Apileon's routing system, from basic route definition to advanced patterns for building robust REST APIs.
