# Frequently Asked Questions (FAQ)

## General Questions

### Q: What is Apileon?
**A:** Apileon is a lightweight PHP framework designed exclusively for REST API development. It focuses on simplicity, performance, and enterprise-grade features while maintaining a minimal footprint.

### Q: Why choose Apileon over Laravel, Symfony, or other frameworks?
**A:** Apileon is purpose-built for APIs only. Unlike full-stack frameworks:
- **Smaller footprint** - Only API-related features, no views, sessions, etc.
- **Faster setup** - Ready to use in under 1 minute
- **No dependencies** - Works with just PHP, Composer optional
- **Simpler learning curve** - Focus on API concepts only
- **Better performance** - No overhead from unused features

### Q: Is Apileon production-ready?
**A:** Yes! Apileon includes:
- Enterprise-grade security (CORS, authentication, rate limiting)
- Comprehensive error handling
- Built-in testing support
- Production deployment guides
- Performance optimizations

## Installation & Setup

### Q: Do I need Composer to use Apileon?
**A:** No! Apileon works both ways:
- **With Composer** - Full package management and testing features
- **Without Composer** - Built-in autoloader, works with just PHP 8.1+

```bash
# Without Composer
git clone https://github.com/bandeto45/apileon.git
cd apileon
./setup-no-composer.sh
php -S localhost:8000 -t public
```

### Q: What PHP version is required?
**A:** PHP 8.1 or higher. The framework uses modern PHP features like:
- Constructor property promotion
- Named arguments
- Match expressions
- Strong typing

### Q: Can I use it on shared hosting?
**A:** Absolutely! Since Apileon works without Composer, it's perfect for shared hosting environments where you might not have shell access or Composer available.

### Q: How do I update the framework?
**A:** 
- **With Composer**: `composer update apileon/framework`
- **Without Composer**: Download latest version and replace framework files in `src/`

## Development

### Q: How do I create a new route?
**A:** Add routes to `routes/api.php`:

```php
use Apileon\Routing\Route;

// Simple route
Route::get('/hello', function() {
    return ['message' => 'Hello World'];
});

// Route with parameter
Route::get('/users/{id}', function($request) {
    return ['user_id' => $request->param('id')];
});

// Controller route
Route::post('/users', 'UserController@store');
```

### Q: How do I handle authentication?
**A:** Use the built-in auth middleware:

```php
// Protect routes
Route::get('/profile', 'UserController@profile')->middleware('auth');

// Group protection
Route::group(['middleware' => ['auth']], function() {
    Route::get('/protected', 'Controller@method');
});

// Usage: Send Bearer token
// Authorization: Bearer your-token-here
```

### Q: How do I validate request data?
**A:** Implement validation in your controllers:

```php
public function store(Request $request): Response
{
    $data = $request->all();
    
    if (empty($data['email']) || !filter_var($data['email'], FILTER_VALIDATE_EMAIL)) {
        return Response::json(['error' => 'Valid email required'], 422);
    }
    
    // Process valid data...
}
```

### Q: How do I handle file uploads?
**A:** Use PHP's built-in file handling:

```php
Route::post('/upload', function($request) {
    if (!isset($_FILES['file'])) {
        return Response::json(['error' => 'No file uploaded'], 400);
    }
    
    $file = $_FILES['file'];
    
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return Response::json(['error' => 'Upload failed'], 400);
    }
    
    // Validate file type, size, etc.
    $allowedTypes = ['image/jpeg', 'image/png', 'image/gif'];
    if (!in_array($file['type'], $allowedTypes)) {
        return Response::json(['error' => 'Invalid file type'], 400);
    }
    
    // Move uploaded file
    $uploadPath = 'uploads/' . uniqid() . '_' . $file['name'];
    if (move_uploaded_file($file['tmp_name'], $uploadPath)) {
        return Response::json(['message' => 'File uploaded', 'path' => $uploadPath]);
    }
    
    return Response::json(['error' => 'Upload failed'], 500);
});
```

### Q: How do I connect to a database?
**A:** Apileon doesn't include a built-in ORM, but you can easily integrate:

```php
// Using PDO directly
class UserController
{
    private PDO $db;
    
    public function __construct()
    {
        $this->db = new PDO(
            'mysql:host=' . env('DB_HOST') . ';dbname=' . env('DB_DATABASE'),
            env('DB_USERNAME'),
            env('DB_PASSWORD')
        );
    }
    
    public function index(): Response
    {
        $stmt = $this->db->query('SELECT * FROM users');
        $users = $stmt->fetchAll(PDO::FETCH_ASSOC);
        
        return Response::json(['users' => $users]);
    }
}

// Or integrate external packages
// composer require illuminate/database  (if using Composer)
```

### Q: How do I add custom middleware?
**A:** Create a middleware class:

```php
// app/Middleware/LoggingMiddleware.php
<?php
namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class LoggingMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        error_log("API Request: {$request->method()} {$request->uri()}");
        
        $response = $next($request);
        
        error_log("API Response: {$response->getStatusCode()}");
        
        return $response;
    }
}

// Register it
$app->getRouter()->registerMiddleware('logging', LoggingMiddleware::class);

// Use it
Route::get('/api/data', 'Controller@method')->middleware('logging');
```

## Deployment & Production

### Q: How do I deploy to production?
**A:** 

1. **Prepare your code:**
```bash
# Set production environment
cp .env.example .env
# Edit .env: APP_ENV=production, APP_DEBUG=false

# If using Composer
composer install --no-dev --optimize-autoloader
```

2. **Configure web server:**
```apache
# Apache .htaccess in public/
RewriteEngine On
RewriteCond %{REQUEST_FILENAME} !-f
RewriteCond %{REQUEST_FILENAME} !-d
RewriteRule ^(.*)$ index.php [QSA,L]
```

```nginx
# Nginx configuration
location / {
    try_files $uri $uri/ /index.php?$query_string;
}
```

### Q: How do I handle CORS in production?
**A:** Use the built-in CORS middleware:

```php
// Apply to all routes
Route::group(['middleware' => ['cors']], function() {
    // Your API routes
});

// Or customize CORS settings
class CustomCorsMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $response = $next($request);
        
        return $response
            ->header('Access-Control-Allow-Origin', 'https://yourdomain.com')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, DELETE')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization');
    }
}
```

### Q: How do I monitor API performance?
**A:** Add logging middleware:

```php
class PerformanceMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $start = microtime(true);
        
        $response = $next($request);
        
        $duration = (microtime(true) - $start) * 1000; // ms
        
        if ($duration > 1000) { // Log slow requests
            error_log("Slow API request: {$request->uri()} took {$duration}ms");
        }
        
        $response->header('X-Response-Time', $duration . 'ms');
        
        return $response;
    }
}
```

## Testing

### Q: How do I test my API?
**A:** 

**With Composer (full testing):**
```bash
composer test
vendor/bin/phpunit tests/UserControllerTest.php
```

**Without Composer (basic testing):**
```bash
php test-no-composer.php
curl -X GET http://localhost:8000/api/users
curl -X POST http://localhost:8000/api/users -H "Content-Type: application/json" -d '{"name":"John"}'
```

### Q: How do I write unit tests?
**A:** Create test files in `tests/`:

```php
// tests/UserControllerTest.php
use PHPUnit\Framework\TestCase;

class UserControllerTest extends TestCase
{
    public function testUserCreation()
    {
        $controller = new UserController();
        $request = new Request();
        $_POST = ['name' => 'John', 'email' => 'john@example.com'];
        
        $response = $controller->store($request);
        
        $this->assertEquals(201, $response->getStatusCode());
    }
}
```

## Troubleshooting

### Q: Getting "Class not found" errors?
**A:** Check your autoloader:
- **With Composer**: Run `composer dump-autoload`
- **Without Composer**: Ensure `autoload.php` is loaded in `public/index.php`

### Q: Routes not working?
**A:** Check:
1. Web server configuration (rewrite rules)
2. Route definitions in `routes/api.php`
3. Controller class and method names
4. Namespace declarations

### Q: CORS errors in browser?
**A:** Add CORS middleware to your routes:
```php
Route::group(['middleware' => ['cors']], function() {
    // Your routes here
});
```

### Q: Authentication not working?
**A:** Verify:
1. Authorization header format: `Authorization: Bearer your-token`
2. Middleware is applied to routes
3. Token validation logic in AuthMiddleware

### Q: Performance issues?
**A:** 
1. Enable OPcache in production
2. Use caching middleware for GET requests
3. Optimize database queries
4. Profile with performance middleware

### Q: Getting 500 errors?
**A:** 
1. Set `APP_DEBUG=true` in `.env` for detailed errors
2. Check PHP error logs
3. Verify file permissions
4. Check syntax with `php -l filename.php`

## Integration

### Q: Can I use external packages?
**A:** 
- **With Composer**: Yes, install any Composer package
- **Without Composer**: Manual installation of compatible packages

### Q: How do I integrate with frontend frameworks?
**A:** Apileon is API-only, perfect for:
- React/Vue.js/Angular SPAs
- Mobile apps (iOS/Android)
- Other backend services
- Microservices architecture

### Q: Can I use Apileon with Docker?
**A:** Yes! Example Dockerfile:

```dockerfile
FROM php:8.1-apache

# Copy application
COPY . /var/www/html/

# Configure Apache
RUN a2enmod rewrite
COPY docker/apache.conf /etc/apache2/sites-available/000-default.conf

# Set permissions
RUN chown -R www-data:www-data /var/www/html/storage

EXPOSE 80
```

### Q: How do I scale Apileon applications?
**A:** 
- Use load balancers (multiple server instances)
- Implement caching (Redis, Memcached)
- Database connection pooling
- CDN for static assets
- Horizontal scaling with microservices

## Community & Support

### Q: Where can I get help?
**A:** 
- Documentation: `docs/` folder in your project
- GitHub Issues: Report bugs and feature requests
- Community: Discussions and Q&A

### Q: How can I contribute?
**A:** 
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests
5. Submit a Pull Request

See [CONTRIBUTING.md](../CONTRIBUTING.md) for detailed guidelines.

### Q: Is Apileon actively maintained?
**A:** Yes! The framework is actively developed and maintained. Check the GitHub repository for latest updates and releases.
