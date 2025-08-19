# 🛠️ Apileon Framework - Developer Guide

## 📂 **Where to Put Your Code - Quick Reference**

| What You're Adding | File Location | Purpose |
|-------------------|---------------|---------|
| **API Routes** | `routes/api.php` | Define your API endpoints |
| **Controllers** | `app/Controllers/` | Handle request logic |
| **Models** | `app/Models/` | Database entities and business logic |
| **Middleware** | `app/Middleware/` | Request/response filtering |
| **Configuration** | `config/*.php` | App settings and environment configs |
| **Database Migrations** | `database/migrations/` | Database schema changes |
| **Tests** | `tests/` | Unit and integration tests |
| **Custom Functions** | `app/Helpers/` | Reusable utility functions |

---

## 🚀 **Quick Start Development Guide**

### **1. Creating Your First API Endpoint**

#### ✅ **DO: Add routes in `routes/api.php`**
```php
<?php
use Apileon\Routing\Route;

// ✅ GOOD: Clear, RESTful endpoints
Route::get('/api/products', 'App\Controllers\ProductController@index');
Route::get('/api/products/{id}', 'App\Controllers\ProductController@show');
Route::post('/api/products', 'App\Controllers\ProductController@store');
Route::put('/api/products/{id}', 'App\Controllers\ProductController@update');
Route::delete('/api/products/{id}', 'App\Controllers\ProductController@destroy');

// ✅ GOOD: Group related routes with middleware
Route::group(['prefix' => 'api/v1', 'middleware' => ['auth']], function () {
    Route::get('/dashboard', 'App\Controllers\DashboardController@index');
    Route::get('/profile', 'App\Controllers\UserController@profile');
});
```

#### ❌ **DON'T: Common routing mistakes**
```php
// ❌ BAD: Don't put business logic in routes
Route::get('/products', function($request) {
    $products = [];
    foreach (Product::all() as $product) {
        // Complex business logic here - WRONG!
        $products[] = processProduct($product);
    }
    return $products;
});

// ❌ BAD: Inconsistent naming
Route::get('/getProducts', '...'); // Should be GET /products
Route::post('/createProduct', '...'); // Should be POST /products
Route::get('/product-detail/{id}', '...'); // Should be GET /products/{id}
```

---

### **2. Creating Controllers**

#### ✅ **DO: Create controllers in `app/Controllers/`**

**File: `app/Controllers/ProductController.php`**
```php
<?php

namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;
use Apileon\Validation\ValidationException;
use App\Models\Product;

class ProductController
{
    public function index(Request $request): Response
    {
        try {
            // ✅ GOOD: Use caching for expensive operations
            $products = cache_remember('products_list', function() use ($request) {
                $page = max(1, (int) $request->query('page', 1));
                $perPage = min(50, max(1, (int) $request->query('per_page', 10)));
                
                return Product::paginate($perPage, $page);
            }, 600); // Cache for 10 minutes

            return success_response($products);
            
        } catch (\Exception $e) {
            return error_response(
                'Failed to retrieve products',
                app_debug() ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function show(Request $request): Response
    {
        try {
            $id = (int) $request->param('id');
            
            // ✅ GOOD: Validate input
            if ($id <= 0) {
                return error_response('Invalid product ID', 'Product ID must be positive', 400);
            }
            
            // ✅ GOOD: Use caching for individual records
            $product = cache_remember("product_{$id}", function() use ($id) {
                return Product::find($id);
            }, 1800); // Cache for 30 minutes
            
            if (!$product) {
                return error_response('Product not found', 'The requested product does not exist', 404);
            }

            return success_response($product->toArray());
            
        } catch (\Exception $e) {
            return error_response(
                'Failed to retrieve product',
                app_debug() ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }

    public function store(Request $request): Response
    {
        try {
            // ✅ GOOD: Use model validation
            $product = Product::createProduct($request->all());
            
            // ✅ GOOD: Fire events for decoupled architecture
            event('product.created', [
                'product_id' => $product->id,
                'user_id' => $request->user_id ?? null
            ]);
            
            // ✅ GOOD: Clear related cache
            cache_forget('products_list');

            return success_response($product->toArray(), 'Product created successfully', 201);
            
        } catch (ValidationException $e) {
            return error_response('Validation failed', 'The given data was invalid', 422, $e->getErrors());
        } catch (\Exception $e) {
            return error_response(
                'Failed to create product',
                app_debug() ? $e->getMessage() : 'Internal server error',
                500
            );
        }
    }
}
```

#### ❌ **DON'T: Controller anti-patterns**
```php
// ❌ BAD: Don't put everything in one method
public function handleProducts(Request $request): Response
{
    $action = $request->query('action');
    if ($action === 'create') {
        // Create logic
    } elseif ($action === 'update') {
        // Update logic
    } elseif ($action === 'delete') {
        // Delete logic
    }
    // This should be separate controller methods!
}

// ❌ BAD: Don't access database directly in controller
public function index(): Response
{
    $pdo = new PDO(...); // Don't do this!
    $stmt = $pdo->query("SELECT * FROM products"); // Use models instead!
    return Response::json($stmt->fetchAll());
}

// ❌ BAD: Don't put business logic in controllers
public function calculatePrice(Request $request): Response
{
    // 50 lines of complex pricing logic here - move to model or service!
}
```

---

### **3. Creating Models**

#### ✅ **DO: Create models in `app/Models/`**

**File: `app/Models/Product.php`**
```php
<?php

namespace App\Models;

use Apileon\Validation\Validator;
use Apileon\Validation\ValidationException;

class Product extends Model
{
    protected string $table = 'products';
    
    // ✅ GOOD: Define fillable fields for security
    protected array $fillable = [
        'name',
        'description',
        'price',
        'category_id',
        'status'
    ];

    // ✅ GOOD: Hide sensitive data
    protected array $hidden = [
        'internal_notes',
        'cost_price'
    ];

    // ✅ GOOD: Use type casting
    protected array $casts = [
        'price' => 'float',
        'is_active' => 'boolean',
        'created_at' => 'datetime'
    ];

    // ✅ GOOD: Create validation methods
    public static function validateForCreation(array $data): array
    {
        $validator = Validator::make($data, [
            'name' => 'required|string|min:2|max:255',
            'price' => 'required|numeric|min:0',
            'category_id' => 'required|integer|min:1',
            'status' => 'string|in:active,inactive,draft'
        ], [
            'name.required' => 'Product name is required',
            'price.min' => 'Price cannot be negative',
            'category_id.required' => 'Category is required'
        ]);

        return $validator->validate();
    }

    // ✅ GOOD: Create business logic methods
    public static function createProduct(array $data): self
    {
        $validatedData = static::validateForCreation($data);
        
        // ✅ GOOD: Set default values
        $validatedData['status'] = $validatedData['status'] ?? 'active';
        $validatedData['slug'] = static::generateSlug($validatedData['name']);
        
        return static::create($validatedData);
    }

    // ✅ GOOD: Create scope methods for common queries
    public static function active(): \Apileon\Database\QueryBuilder
    {
        return static::where('status', 'active');
    }

    public static function inCategory(int $categoryId): \Apileon\Database\QueryBuilder
    {
        return static::where('category_id', $categoryId);
    }

    // ✅ GOOD: Create helper methods
    public function isActive(): bool
    {
        return $this->getAttribute('status') === 'active';
    }

    public function getFormattedPrice(): string
    {
        return '$' . number_format($this->getAttribute('price'), 2);
    }

    // ✅ GOOD: Override toArray for custom output
    public function toArray(): array
    {
        $attributes = parent::toArray();
        
        // Add computed attributes
        $attributes['formatted_price'] = $this->getFormattedPrice();
        $attributes['is_active'] = $this->isActive();
        
        return $attributes;
    }

    private static function generateSlug(string $name): string
    {
        return str_slug($name);
    }
}
```

#### ❌ **DON'T: Model anti-patterns**
```php
// ❌ BAD: Don't make everything fillable
protected array $fillable = ['*']; // Security risk!

// ❌ BAD: Don't put controller logic in models
public function handleRequest(Request $request): Response
{
    // This belongs in a controller!
}

// ❌ BAD: Don't expose sensitive data
protected array $hidden = []; // Always hide sensitive fields!

// ❌ BAD: Don't put view logic in models
public function renderHtml(): string
{
    // This belongs in a view/presenter class!
}
```

---

### **4. Working with Middleware**

#### ✅ **DO: Create middleware in `app/Middleware/`**

**File: `app/Middleware/ApiKeyMiddleware.php`**
```php
<?php

namespace App\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class ApiKeyMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $apiKey = $request->header('X-API-Key');
        
        // ✅ GOOD: Validate API key
        if (!$apiKey || !$this->isValidApiKey($apiKey)) {
            return error_response(
                'Invalid API Key',
                'A valid API key is required to access this endpoint',
                401
            );
        }
        
        // ✅ GOOD: Add user context to request
        $user = $this->getUserFromApiKey($apiKey);
        $request->setUser($user);
        
        // ✅ GOOD: Track API usage
        event('api.key.used', [
            'api_key' => substr($apiKey, 0, 8) . '...',
            'endpoint' => $request->uri(),
            'user_id' => $user->id ?? null
        ]);

        return $next($request);
    }

    private function isValidApiKey(string $apiKey): bool
    {
        // ✅ GOOD: Use caching for API key validation
        return cache_remember("api_key_{$apiKey}", function() use ($apiKey) {
            return ApiKey::where('key', $apiKey)
                         ->where('status', 'active')
                         ->exists();
        }, 300); // Cache for 5 minutes
    }

    private function getUserFromApiKey(string $apiKey): ?User
    {
        return cache_remember("api_key_user_{$apiKey}", function() use ($apiKey) {
            $apiKeyRecord = ApiKey::where('key', $apiKey)->first();
            return $apiKeyRecord ? $apiKeyRecord->user : null;
        }, 300);
    }
}
```

#### **Register middleware in `routes/api.php`:**
```php
// ✅ GOOD: Register middleware globally
$app->getRouter()->registerMiddleware('api_key', \App\Middleware\ApiKeyMiddleware::class);

// ✅ GOOD: Apply to specific routes
Route::group(['middleware' => ['api_key']], function () {
    Route::get('/api/protected/data', 'DataController@index');
});
```

---

### **5. Database Migrations**

#### ✅ **DO: Create migrations in `database/migrations/`**

**File: `database/migrations/2024_08_19_120000_create_products_table.php`**
```php
<?php

use Apileon\Database\Migration;

class CreateProductsTable extends Migration
{
    public function up(): void
    {
        $this->createTable('products', function ($table) {
            $table->id();
            $table->string('name', 255);
            $table->text('description')->nullable();
            $table->decimal('price', 10, 2);
            $table->integer('category_id');
            $table->string('slug', 255)->unique();
            $table->enum('status', ['active', 'inactive', 'draft'])->default('active');
            $table->json('metadata')->nullable();
            $table->timestamps();
            
            // ✅ GOOD: Add indexes for performance
            $table->index('status');
            $table->index('category_id');
            $table->index(['status', 'category_id']);
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        $this->dropTable('products');
    }
}
```

#### **Run migrations:**
```bash
# ✅ GOOD: Run migrations
php artisan migrate

# ✅ GOOD: Rollback if needed
php artisan migrate:rollback
```

---

### **6. Caching Best Practices**

#### ✅ **DO: Use caching strategically**
```php
// ✅ GOOD: Cache expensive database queries
$popularProducts = cache_remember('popular_products', function() {
    return Product::where('views', '>', 1000)
                  ->orderBy('views', 'DESC')
                  ->limit(10)
                  ->get();
}, 3600); // Cache for 1 hour

// ✅ GOOD: Cache API responses
$weatherData = cache_remember('weather_api_response', function() {
    return callWeatherAPI();
}, 1800); // Cache for 30 minutes

// ✅ GOOD: Clear cache when data changes
public function updateProduct(Request $request): Response
{
    $product = Product::find($request->param('id'));
    $product->update($request->all());
    
    // Clear related cache
    cache_forget("product_{$product->id}");
    cache_forget('popular_products');
    cache_forget('products_list');
    
    return success_response($product->toArray());
}
```

#### ❌ **DON'T: Cache mistakes**
```php
// ❌ BAD: Don't cache user-specific data globally
cache('current_user', $user); // This will be shared across all users!

// ❌ BAD: Don't cache large objects indefinitely
cache('all_products', Product::all(), 86400); // Could use too much memory

// ❌ BAD: Don't forget to clear cache
public function deleteProduct($id) {
    Product::find($id)->delete();
    // Forgot to clear cache - users will still see deleted product!
}
```

---

### **7. Event System Usage**

#### ✅ **DO: Use events for decoupled architecture**

**File: `app/Events/UserRegistered.php`**
```php
<?php

namespace App\Events;

use Apileon\Events\Event;

class UserRegistered extends Event
{
    public function __construct(array $userData)
    {
        parent::__construct([
            'user_id' => $userData['id'],
            'email' => $userData['email'],
            'name' => $userData['name'],
            'registered_at' => now()
        ]);
    }
}
```

**File: `app/Listeners/SendWelcomeEmail.php`**
```php
<?php

namespace App\Listeners;

class SendWelcomeEmail
{
    public function handle(string $event, array $data): void
    {
        // ✅ GOOD: Use events for side effects
        $email = $data['email'];
        $name = $data['name'];
        
        // Send welcome email
        mail($email, 'Welcome!', "Hello {$name}, welcome to our platform!");
        
        // Log the action
        error_log("Welcome email sent to: {$email}");
    }
}
```

**Register listeners in `src/Foundation/Application.php`:**
```php
private function registerEventListeners(): void
{
    // ✅ GOOD: Register event listeners
    listen('user.registered', [new \App\Listeners\SendWelcomeEmail(), 'handle']);
    listen('user.registered', [new \App\Listeners\CreateUserProfile(), 'handle']);
    listen('user.registered', [new \App\Listeners\SendToAnalytics(), 'handle']);
}
```

---

### **8. Testing Your Code**

#### ✅ **DO: Create tests in `tests/`**

**File: `tests/ProductControllerTest.php`**
```php
<?php

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;
use App\Models\Product;

class ProductControllerTest extends TestCase
{
    protected function setUp(): void
    {
        // ✅ GOOD: Setup test environment
        putenv('APP_ENV=testing');
        putenv('APP_DEBUG=true');
    }

    public function testCreateProduct()
    {
        // ✅ GOOD: Test validation
        $validData = [
            'name' => 'Test Product',
            'price' => 29.99,
            'category_id' => 1
        ];

        $product = Product::createProduct($validData);
        
        $this->assertInstanceOf(Product::class, $product);
        $this->assertEquals('Test Product', $product->getAttribute('name'));
        $this->assertEquals(29.99, $product->getAttribute('price'));
    }

    public function testValidation()
    {
        // ✅ GOOD: Test validation failures
        $invalidData = [
            'name' => '', // Empty name should fail
            'price' => -10, // Negative price should fail
        ];

        $this->expectException(\Apileon\Validation\ValidationException::class);
        Product::validateForCreation($invalidData);
    }
}
```

---

## 🎯 **Performance Monitoring Integration**

#### ✅ **DO: Monitor your application**
```php
// ✅ GOOD: Use built-in performance monitoring
public function expensiveOperation(Request $request): Response
{
    PerformanceMonitor::startTimer('data_processing');
    
    // Your expensive operation
    $result = processLargeDataset();
    
    $duration = PerformanceMonitor::endTimer('data_processing');
    
    // Log if operation is slow
    if ($duration > 1000) { // > 1 second
        error_log("Slow operation detected: {$duration}ms");
    }
    
    return success_response($result);
}

// ✅ GOOD: Check health endpoint
// GET /health - Monitor your API health
// GET /metrics - Get detailed performance metrics (debug mode)
```

---

## 📂 **Project Structure Best Practices**

```
my-api/
├── app/
│   ├── Controllers/           # ✅ All your API controllers
│   ├── Models/               # ✅ Database models and business logic
│   ├── Middleware/           # ✅ Custom middleware
│   ├── Events/               # ✅ Custom event classes
│   ├── Listeners/            # ✅ Event listeners
│   └── Helpers/              # ✅ Utility functions
├── config/
│   ├── app.php              # ✅ App configuration
│   ├── database.php         # ✅ Database settings
│   └── cache.php            # ✅ Cache configuration
├── database/
│   ├── migrations/          # ✅ Database schema changes
│   └── seeders/             # ✅ Test data
├── routes/
│   └── api.php              # ✅ All your API routes
├── tests/                   # ✅ Unit and integration tests
└── storage/
    ├── cache/               # ✅ File cache storage
    └── logs/                # ✅ Application logs
```

---

## 🚨 **Common Mistakes to Avoid**

### ❌ **Security Mistakes**
1. **Don't expose sensitive data**
2. **Don't trust user input** - always validate
3. **Don't hardcode secrets** - use environment variables
4. **Don't skip authentication** on protected endpoints

### ❌ **Performance Mistakes**
1. **Don't load all records** - use pagination
2. **Don't repeat expensive operations** - use caching
3. **Don't ignore database indexes** - add them for common queries
4. **Don't forget to monitor** - use built-in performance tracking

### ❌ **Architecture Mistakes**
1. **Don't put business logic in controllers** - use models or services
2. **Don't couple code tightly** - use events for side effects
3. **Don't skip testing** - write tests for critical functionality
4. **Don't ignore errors** - handle exceptions properly

---

## 🏆 **Success Checklist**

Before deploying your API, ensure you've:

- [ ] ✅ **Routes defined** in `routes/api.php`
- [ ] ✅ **Controllers created** with proper error handling
- [ ] ✅ **Models validated** with security measures
- [ ] ✅ **Database migrations** written and tested
- [ ] ✅ **Caching implemented** for expensive operations
- [ ] ✅ **Events fired** for decoupled architecture
- [ ] ✅ **Tests written** for critical functionality
- [ ] ✅ **Performance monitored** with built-in tools
- [ ] ✅ **Security validated** with proper authentication
- [ ] ✅ **Documentation updated** for your API endpoints

**Follow this guide and you'll build maintainable, secure, and high-performance APIs with Apileon!** 🚀
