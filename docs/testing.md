# Apileon Testing Guide

## Overview

Apileon includes a comprehensive testing setup using PHPUnit, making it easy to write unit tests, integration tests, and API tests for your REST API applications.

## Test Setup

### Prerequisites

Testing is configured out-of-the-box with:
- PHPUnit 10.x
- Mockery for mocking
- PSR-4 autoloading for test classes

### Running Tests

```bash
# Run all tests
composer test

# Run specific test file
vendor/bin/phpunit tests/RequestTest.php

# Run with coverage
vendor/bin/phpunit --coverage-html coverage/

# Run specific test method
vendor/bin/phpunit --filter testRequestMethod tests/RequestTest.php

# Verbose output
vendor/bin/phpunit --testdox
```

### Test Configuration

Tests are configured in `phpunit.xml`:

```xml
<?xml version="1.0" encoding="UTF-8"?>
<phpunit bootstrap="vendor/autoload.php"
         colors="true"
         testdox="true">
    <testsuites>
        <testsuite name="Unit">
            <directory suffix="Test.php">./tests</directory>
        </testsuite>
    </testsuites>
    <source>
        <include>
            <directory suffix=".php">./src</directory>
            <directory suffix=".php">./app</directory>
        </include>
    </source>
</phpunit>
```

## Unit Testing

### Testing Framework Components

#### Request Testing

```php
<?php

use PHPUnit\Framework\TestCase;
use Apileon\Http\Request;

class RequestTest extends TestCase
{
    protected function setUp(): void
    {
        // Reset globals before each test
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
    }

    public function testRequestMethodDetection()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $request = new Request();
        
        $this->assertEquals('POST', $request->method());
    }

    public function testRequestUriParsing()
    {
        $_SERVER['REQUEST_URI'] = '/api/users/123?page=1';
        
        $request = new Request();
        
        $this->assertEquals('/api/users/123', $request->uri());
    }

    public function testQueryParameters()
    {
        $_GET = ['page' => '1', 'limit' => '10'];
        
        $request = new Request();
        
        $this->assertEquals('1', $request->query('page'));
        $this->assertEquals('10', $request->query('limit'));
        $this->assertEquals('default', $request->query('missing', 'default'));
        $this->assertEquals(['page' => '1', 'limit' => '10'], $request->query());
    }

    public function testRequestHeaders()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        
        $request = new Request();
        
        $this->assertEquals('Bearer token123', $request->header('Authorization'));
        $this->assertEquals('application/json', $request->header('Content-Type'));
        $this->assertNull($request->header('Missing-Header'));
    }

    public function testBearerTokenExtraction()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer abc123def456';
        
        $request = new Request();
        
        $this->assertEquals('abc123def456', $request->bearerToken());
    }

    public function testJsonRequestDetection()
    {
        $_SERVER['CONTENT_TYPE'] = 'application/json';
        
        $request = new Request();
        
        $this->assertTrue($request->isJson());
    }

    public function testRouteParameters()
    {
        $request = new Request();
        $request->setParams(['id' => '123', 'slug' => 'test-post']);
        
        $this->assertEquals('123', $request->param('id'));
        $this->assertEquals('test-post', $request->param('slug'));
        $this->assertNull($request->param('missing'));
        $this->assertEquals('default', $request->param('missing', 'default'));
    }
}
```

#### Response Testing

```php
<?php

use PHPUnit\Framework\TestCase;
use Apileon\Http\Response;

class ResponseTest extends TestCase
{
    public function testJsonResponse()
    {
        $data = ['message' => 'Hello World'];
        $response = Response::json($data);
        
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testJsonResponseWithCustomStatus()
    {
        $data = ['error' => 'Not found'];
        $response = Response::json($data, 404);
        
        $this->assertEquals(404, $response->getStatusCode());
        $this->assertEquals(json_encode($data), $response->getContent());
    }

    public function testTextResponse()
    {
        $response = Response::text('Hello World');
        
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    public function testHtmlResponse()
    {
        $html = '<h1>Hello World</h1>';
        $response = Response::html($html);
        
        $this->assertEquals($html, $response->getContent());
        $this->assertEquals('text/html', $response->getHeaders()['Content-Type']);
    }

    public function testResponseChaining()
    {
        $response = Response::json(['message' => 'Success'])
            ->status(201)
            ->header('X-Custom-Header', 'custom-value');
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('custom-value', $response->getHeaders()['X-Custom-Header']);
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testResponseWithMultipleHeaders()
    {
        $response = Response::json(['data' => 'test'])
            ->header('X-Header-1', 'value1')
            ->header('X-Header-2', 'value2');
        
        $headers = $response->getHeaders();
        $this->assertEquals('value1', $headers['X-Header-1']);
        $this->assertEquals('value2', $headers['X-Header-2']);
    }
}
```

### Testing Middleware

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Middleware\AuthMiddleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class AuthMiddlewareTest extends TestCase
{
    public function testAuthenticationRequired()
    {
        $middleware = new AuthMiddleware();
        
        // Create request without Authorization header
        $request = new Request();
        
        $next = function($request) {
            return Response::json(['data' => 'protected']);
        };
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals(401, $response->getStatusCode());
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Unauthorized', $content['error']);
    }

    public function testValidAuthentication()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer valid-token-here';
        
        $middleware = new AuthMiddleware();
        $request = new Request();
        
        $next = function($request) {
            return Response::json(['data' => 'protected']);
        };
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals(200, $response->getStatusCode());
    }

    public function testInvalidToken()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer x'; // Too short
        
        $middleware = new AuthMiddleware();
        $request = new Request();
        
        $next = function($request) {
            return Response::json(['data' => 'protected']);
        };
        
        $response = $middleware->handle($request, $next);
        
        $this->assertEquals(401, $response->getStatusCode());
    }
}
```

### Testing Models

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Models\User;

class UserModelTest extends TestCase
{
    public function testUserCreation()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $user = new User($userData);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
    }

    public function testUserFillable()
    {
        $userData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'secret',
            'admin' => true // Not in fillable
        ];
        
        $user = new User($userData);
        
        $this->assertEquals('John Doe', $user->name);
        $this->assertEquals('john@example.com', $user->email);
        $this->assertEquals('secret', $user->password);
        $this->assertNull($user->admin); // Should not be set
    }

    public function testUserToArray()
    {
        $userData = ['name' => 'John', 'email' => 'john@example.com'];
        $user = new User($userData);
        
        $array = $user->toArray();
        
        $this->assertEquals($userData, $array);
    }

    public function testUserToJson()
    {
        $userData = ['name' => 'John', 'email' => 'john@example.com'];
        $user = new User($userData);
        
        $json = $user->toJson();
        
        $this->assertEquals(json_encode($userData), $json);
    }

    public function testUserFind()
    {
        $user = User::find(1);
        
        $this->assertInstanceOf(User::class, $user);
        $this->assertEquals(1, $user->id);
    }

    public function testUserFindNotFound()
    {
        $user = User::find(999);
        
        $this->assertNull($user);
    }

    public function testUserAll()
    {
        $users = User::all();
        
        $this->assertIsArray($users);
        $this->assertGreaterThan(0, count($users));
        $this->assertInstanceOf(User::class, $users[0]);
    }
}
```

## Integration Testing

### Testing Controllers

```php
<?php

use PHPUnit\Framework\TestCase;
use App\Controllers\UserController;
use Apileon\Http\Request;

class UserControllerTest extends TestCase
{
    private UserController $controller;

    protected function setUp(): void
    {
        $this->controller = new UserController();
    }

    public function testIndex()
    {
        $request = new Request();
        $response = $this->controller->index($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
        $this->assertIsArray($content['data']);
    }

    public function testShow()
    {
        $request = new Request();
        $request->setParams(['id' => '1']);
        
        $response = $this->controller->show($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('data', $content);
    }

    public function testShowNotFound()
    {
        $request = new Request();
        $request->setParams(['id' => '999']);
        
        $response = $this->controller->show($request);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testStore()
    {
        $_POST = [
            'name' => 'John Doe',
            'email' => 'john@example.com'
        ];
        
        $request = new Request();
        $response = $this->controller->store($request);
        
        $this->assertEquals(201, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('User created successfully', $content['message']);
        $this->assertArrayHasKey('data', $content);
    }

    public function testStoreValidationError()
    {
        $_POST = ['name' => '']; // Missing email
        
        $request = new Request();
        $response = $this->controller->store($request);
        
        $this->assertEquals(422, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('Validation failed', $content['error']);
    }
}
```

### Testing Routing

```php
<?php

use PHPUnit\Framework\TestCase;
use Apileon\Routing\Router;
use Apileon\Routing\Route;
use Apileon\Http\Request;

class RouterTest extends TestCase
{
    private Router $router;

    protected function setUp(): void
    {
        $this->router = new Router();
        Route::setRouter($this->router);
    }

    public function testBasicRouting()
    {
        Route::get('/test', function() {
            return ['message' => 'test'];
        });
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/test';
        
        $request = new Request();
        $response = $this->router->dispatch($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('test', $content['message']);
    }

    public function testRouteWithParameter()
    {
        Route::get('/users/{id}', function($request) {
            return ['user_id' => $request->param('id')];
        });
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users/123';
        
        $request = new Request();
        $response = $this->router->dispatch($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertEquals('123', $content['user_id']);
    }

    public function testRouteNotFound()
    {
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent';
        
        $request = new Request();
        $response = $this->router->dispatch($request);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testControllerRoute()
    {
        Route::get('/users', 'App\Controllers\UserController@index');
        
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/users';
        
        $request = new Request();
        $response = $this->router->dispatch($request);
        
        $this->assertEquals(200, $response->getStatusCode());
    }
}
```

## API Testing

### HTTP Client Testing

Create a simple HTTP client for testing:

```php
<?php

class ApiTestCase extends TestCase
{
    protected string $baseUrl = 'http://localhost:8000';

    protected function get(string $uri, array $headers = []): array
    {
        return $this->makeRequest('GET', $uri, null, $headers);
    }

    protected function post(string $uri, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('POST', $uri, $data, $headers);
    }

    protected function put(string $uri, array $data = [], array $headers = []): array
    {
        return $this->makeRequest('PUT', $uri, $data, $headers);
    }

    protected function delete(string $uri, array $headers = []): array
    {
        return $this->makeRequest('DELETE', $uri, null, $headers);
    }

    private function makeRequest(string $method, string $uri, ?array $data, array $headers): array
    {
        $url = $this->baseUrl . $uri;
        
        $options = [
            'http' => [
                'method' => $method,
                'header' => $this->buildHeaders($headers),
                'ignore_errors' => true
            ]
        ];

        if ($data && in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $options['http']['content'] = json_encode($data);
            $options['http']['header'] .= "Content-Type: application/json\r\n";
        }

        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        
        $statusCode = $this->parseStatusCode($http_response_header[0]);
        
        return [
            'status' => $statusCode,
            'body' => json_decode($response, true),
            'headers' => $http_response_header
        ];
    }

    private function buildHeaders(array $headers): string
    {
        $headerString = '';
        foreach ($headers as $key => $value) {
            $headerString .= "$key: $value\r\n";
        }
        return $headerString;
    }

    private function parseStatusCode(string $statusLine): int
    {
        preg_match('/HTTP\/\d\.\d\s+(\d+)/', $statusLine, $matches);
        return (int) $matches[1];
    }
}
```

### API Endpoint Testing

```php
<?php

class ApiEndpointTest extends ApiTestCase
{
    public function testGetUsers()
    {
        $response = $this->get('/api/users');
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
        $this->assertArrayHasKey('meta', $response['body']);
    }

    public function testGetUserById()
    {
        $response = $this->get('/api/users/1');
        
        $this->assertEquals(200, $response['status']);
        $this->assertArrayHasKey('data', $response['body']);
    }

    public function testGetUserNotFound()
    {
        $response = $this->get('/api/users/999');
        
        $this->assertEquals(404, $response['status']);
        $this->assertArrayHasKey('error', $response['body']);
    }

    public function testCreateUser()
    {
        $userData = [
            'name' => 'Test User',
            'email' => 'test@example.com'
        ];
        
        $response = $this->post('/api/users', $userData);
        
        $this->assertEquals(201, $response['status']);
        $this->assertEquals('User created successfully', $response['body']['message']);
    }

    public function testCreateUserValidationError()
    {
        $userData = ['name' => '']; // Missing email
        
        $response = $this->post('/api/users', $userData);
        
        $this->assertEquals(422, $response['status']);
        $this->assertEquals('Validation failed', $response['body']['error']);
    }

    public function testProtectedEndpoint()
    {
        // Without authentication
        $response = $this->get('/api/v1/profile');
        $this->assertEquals(401, $response['status']);
        
        // With authentication
        $response = $this->get('/api/v1/profile', [
            'Authorization' => 'Bearer valid-token'
        ]);
        $this->assertEquals(200, $response['status']);
    }

    public function testRateLimiting()
    {
        // Make many requests to trigger rate limiting
        for ($i = 0; $i < 65; $i++) {
            $response = $this->post('/api/contact', ['message' => 'test']);
        }
        
        $this->assertEquals(429, $response['status']);
        $this->assertArrayHasKey('X-RateLimit-Limit', $response['headers']);
    }

    public function testCorsHeaders()
    {
        $response = $this->get('/public/status');
        
        $this->assertEquals(200, $response['status']);
        
        $headers = implode("\n", $response['headers']);
        $this->assertStringContains('Access-Control-Allow-Origin', $headers);
    }
}
```

## Test Organization

### Directory Structure

```
tests/
├── Unit/
│   ├── Http/
│   │   ├── RequestTest.php
│   │   ├── ResponseTest.php
│   │   └── Middleware/
│   │       ├── AuthMiddlewareTest.php
│   │       └── CorsMiddlewareTest.php
│   ├── Routing/
│   │   └── RouterTest.php
│   └── Models/
│       └── UserModelTest.php
├── Integration/
│   ├── Controllers/
│   │   └── UserControllerTest.php
│   └── Routing/
│       └── RouteTest.php
├── Api/
│   ├── UserApiTest.php
│   └── AuthApiTest.php
└── TestCase.php
```

### Base Test Classes

```php
<?php

namespace Tests;

use PHPUnit\Framework\TestCase as BaseTestCase;

abstract class TestCase extends BaseTestCase
{
    protected function setUp(): void
    {
        parent::setUp();
        $this->resetGlobals();
    }

    protected function resetGlobals(): void
    {
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
        $_REQUEST = [];
    }

    protected function mockRequest(string $method, string $uri, array $data = []): void
    {
        $_SERVER['REQUEST_METHOD'] = $method;
        $_SERVER['REQUEST_URI'] = $uri;
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) {
            $_POST = $data;
        } else {
            $_GET = $data;
        }
    }

    protected function assertJsonResponse(string $json, array $expected): void
    {
        $actual = json_decode($json, true);
        $this->assertEquals($expected, $actual);
    }
}
```

## Test Data and Fixtures

### Test Data Factory

```php
<?php

namespace Tests\Support;

class TestDataFactory
{
    public static function user(array $overrides = []): array
    {
        return array_merge([
            'id' => rand(1, 1000),
            'name' => 'Test User',
            'email' => 'test@example.com',
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s')
        ], $overrides);
    }

    public static function users(int $count = 3): array
    {
        $users = [];
        for ($i = 0; $i < $count; $i++) {
            $users[] = self::user([
                'id' => $i + 1,
                'name' => "User " . ($i + 1),
                'email' => "user" . ($i + 1) . "@example.com"
            ]);
        }
        return $users;
    }
}
```

## Continuous Integration

### GitHub Actions

Create `.github/workflows/tests.yml`:

```yaml
name: Tests

on:
  push:
    branches: [ main, develop ]
  pull_request:
    branches: [ main ]

jobs:
  test:
    runs-on: ubuntu-latest

    strategy:
      matrix:
        php-version: [8.1, 8.2, 8.3]

    steps:
    - uses: actions/checkout@v3

    - name: Setup PHP
      uses: shivammathur/setup-php@v2
      with:
        php-version: ${{ matrix.php-version }}
        extensions: mbstring, intl
        coverage: xdebug

    - name: Install dependencies
      run: composer install --prefer-dist --no-progress

    - name: Run tests
      run: vendor/bin/phpunit

    - name: Generate coverage report
      run: vendor/bin/phpunit --coverage-clover coverage.xml

    - name: Upload coverage to Codecov
      uses: codecov/codecov-action@v3
      with:
        file: ./coverage.xml
```

## Best Practices

### 1. Test Naming

Use descriptive test method names:

```php
// Good
public function testUserCanBeCreatedWithValidData()
public function testAuthenticationFailsWithInvalidToken()
public function testValidationErrorReturnedForMissingEmail()

// Avoid
public function testCreate()
public function testAuth()
public function testValidation()
```

### 2. Test Structure

Follow Arrange-Act-Assert pattern:

```php
public function testUserCreation()
{
    // Arrange
    $userData = ['name' => 'John', 'email' => 'john@example.com'];
    
    // Act
    $user = new User($userData);
    
    // Assert
    $this->assertEquals('John', $user->name);
    $this->assertEquals('john@example.com', $user->email);
}
```

### 3. Test Independence

Each test should be independent:

```php
public function setUp(): void
{
    // Reset state before each test
    $this->resetDatabase();
    $this->resetGlobals();
}
```

### 4. Mock External Dependencies

Mock external services and dependencies:

```php
public function testEmailSending()
{
    $emailService = Mockery::mock(EmailService::class);
    $emailService->shouldReceive('send')->once()->andReturn(true);
    
    $controller = new ContactController($emailService);
    // Test controller logic
}
```

This testing guide provides comprehensive coverage of testing strategies for Apileon applications, from unit tests to full API integration testing.
