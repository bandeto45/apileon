# Apileon Framework - Performance & Caching Guide

## 🚀 **Performance Monitoring**

Apileon includes built-in performance monitoring to help you optimize your APIs.

### **Automatic Performance Tracking**

Performance metrics are automatically collected for:
- Request execution time
- Memory usage (current and peak)
- Database query count and timing
- Cache hit/miss ratios

### **Accessing Performance Metrics**

#### **In Development (Debug Mode)**
```bash
# Get basic health check with performance data
curl http://localhost:8000/health

# Get detailed performance metrics
curl http://localhost:8000/metrics
```

#### **Response Headers (Debug Mode)**
Performance data is automatically added to response headers:
```
X-Performance-Time: 45.23ms
X-Performance-Memory: 2.5MB
X-Performance-Queries: 3
```

#### **Programmatic Access**
```php
// Get formatted performance metrics
$metrics = performance_metrics();

// Example response:
[
    'performance' => [
        'request_time_ms' => 45.23,
        'memory_usage_mb' => 2.5,
        'memory_peak_mb' => 3.1,
        'database_queries' => 3,
        'query_time_ms' => 12.45,
        'cache_hit_ratio' => '85.7%'
    ]
]
```

### **Custom Performance Tracking**

```php
use Apileon\Support\PerformanceMonitor;

// Start timing an operation
PerformanceMonitor::startTimer('expensive_operation');

// Your expensive operation here
processLargeDataset();

// End timing and get duration
$duration = PerformanceMonitor::endTimer('expensive_operation');

// Increment custom counters
PerformanceMonitor::incrementCounter('api_calls');
PerformanceMonitor::incrementCounter('processed_items', 100);
```

---

## 💾 **Caching System**

Apileon includes a flexible caching system supporting multiple drivers.

### **Configuration**

Configure caching in `config/cache.php`:

```php
return [
    'default' => 'file', // or 'array', 'redis'
    
    'stores' => [
        'file' => [
            'driver' => 'file',
            'path' => storage_path('cache'),
            'ttl' => 3600,
        ],
        
        'array' => [
            'driver' => 'array',
            'ttl' => 3600,
        ]
    ]
];
```

### **Environment Variables**
```env
CACHE_DRIVER=file
CACHE_PATH=/path/to/cache
CACHE_TTL=3600
```

### **Basic Usage**

#### **Storing Data**
```php
// Store for 1 hour (3600 seconds)
cache('user_1', $userData, 3600);

// Store with default TTL
cache('settings', $appSettings);

// Using cache manager directly
use Apileon\Cache\CacheManager;

$cache = CacheManager::getInstance();
$cache->set('key', 'value', 1800); // 30 minutes
```

#### **Retrieving Data**
```php
// Get cached data
$userData = cache('user_1');

// Get with default value
$settings = cache('app_settings', ['theme' => 'dark']);

// Check if exists
if (cache()->has('user_1')) {
    // Key exists and is not expired
}
```

#### **Remember Pattern**
```php
// Cache expensive operations
$users = cache_remember('active_users', function() {
    return User::where('status', 'active')->get();
}, 1800); // Cache for 30 minutes

// The callback only runs if cache miss occurs
```

#### **Deleting Data**
```php
// Delete specific key
cache_forget('user_1');

// Clear all cache
cache()->clear();
```

### **Cache in Models**

```php
class User extends Model
{
    public static function getActiveUsers()
    {
        return cache_remember('active_users', function() {
            return static::where('status', 'active')->get();
        }, 1800);
    }
    
    public function clearUserCache()
    {
        cache_forget('user_' . $this->id);
        cache_forget('active_users'); // Clear list cache too
    }
}
```

### **Cache in Controllers**

```php
class UserController
{
    public function index(Request $request): Response
    {
        $page = $request->query('page', 1);
        $cacheKey = "users_page_{$page}";
        
        $users = cache_remember($cacheKey, function() use ($request) {
            return User::paginate(10, (int) $request->query('page', 1));
        }, 600); // Cache for 10 minutes
        
        return Response::json($users);
    }
}
```

---

## 🎯 **Event System**

Apileon includes a simple but powerful event system for decoupling your application logic.

### **Firing Events**

```php
// Fire a simple event
event('user.created', ['user_id' => 123, 'email' => 'user@example.com']);

// Fire with custom event class
use Apileon\Events\UserCreated;

$event = new UserCreated($user);
event('user.created', $event->getData());
```

### **Listening to Events**

```php
// Listen to specific event
listen('user.created', function($event, $data) {
    // Send welcome email
    mail($data['email'], 'Welcome!', 'Welcome to our platform!');
});

// Listen with priority (higher number = higher priority)
listen('user.created', function($event, $data) {
    // High priority listener
    logUserRegistration($data);
}, 100);

// Wildcard listeners
EventDispatcher::listenWildcard('user.*', function($event, $data) {
    // Handles user.created, user.updated, user.deleted, etc.
    auditLog($event, $data);
});
```

### **Built-in Events**

Apileon automatically fires these events:

#### **Database Events**
- `query.executed` - When a database query is executed
- `model.created` - When a model is created
- `model.updated` - When a model is updated
- `model.deleted` - When a model is deleted

#### **HTTP Events**
- `request.received` - When a request is received
- `response.sent` - When a response is sent

#### **Cache Events**
- `cache.hit` - When cache key is found
- `cache.miss` - When cache key is not found
- `cache.write` - When data is written to cache

### **Custom Events**

```php
use Apileon\Events\Event;

class OrderPlaced extends Event
{
    public function __construct(array $orderData)
    {
        parent::__construct([
            'order_id' => $orderData['id'],
            'user_id' => $orderData['user_id'],
            'total' => $orderData['total'],
            'items' => $orderData['items']
        ]);
    }
}

// Fire the event
$orderEvent = new OrderPlaced($order);
event('order.placed', $orderEvent->getData());

// Listen for it
listen('order.placed', function($event, $data) {
    // Send order confirmation email
    // Update inventory
    // Process payment
});
```

---

## 📊 **Performance Best Practices**

### **Database Optimization**

1. **Use Proper Indexing**
```php
// In migrations
$table->index('email');
$table->index(['status', 'created_at']);
```

2. **Limit Query Results**
```php
// Good: Use pagination
$users = User::paginate(20, $page);

// Bad: Loading all records
$users = User::all(); // Avoid in production
```

3. **Use Query Caching**
```php
$popularPosts = cache_remember('popular_posts', function() {
    return Post::where('views', '>', 1000)
                ->orderBy('views', 'DESC')
                ->limit(10)
                ->get();
}, 3600); // Cache for 1 hour
```

### **Memory Optimization**

1. **Use Generators for Large Datasets**
```php
function processLargeDataset() {
    User::chunk(1000, function($users) {
        foreach ($users as $user) {
            // Process each user
            processUser($user);
        }
    });
}
```

2. **Clear Variables When Done**
```php
$largeArray = generateLargeArray();
processArray($largeArray);
unset($largeArray); // Free memory
```

### **Response Optimization**

1. **Use Appropriate HTTP Status Codes**
```php
return Response::json($data, 201); // Created
return Response::json(['error' => 'Not found'], 404);
```

2. **Add Caching Headers**
```php
return Response::json($data)
    ->header('Cache-Control', 'public, max-age=3600')
    ->header('ETag', md5(serialize($data)));
```

---

## 🔧 **Monitoring & Debugging**

### **Health Check Endpoint**

```bash
# Basic health check
curl http://localhost:8000/health

# Response includes:
{
    "status": "ok",
    "timestamp": "2024-08-19 10:30:00",
    "version": "1.0.0",
    "environment": "production",
    "performance": {
        "request_time_ms": 45.23,
        "memory_usage_mb": 2.5,
        "database_queries": 3,
        "cache_hit_ratio": "85.7%"
    }
}
```

### **Debug Information**

In debug mode (`APP_DEBUG=true`):

1. **Detailed Metrics Endpoint**
```bash
curl http://localhost:8000/metrics
```

2. **Performance Headers**
Check response headers for performance data

3. **Cache Testing**
```bash
curl http://localhost:8000/cache/test
```

4. **Event Testing**
```bash
curl http://localhost:8000/events/test
```

### **Slow Query Logging**

Queries taking longer than 100ms are automatically logged in debug mode:
```
[2024-08-19 10:30:00] Slow query detected: SELECT * FROM users WHERE status = 'active' (145.5ms)
```

---

## 🏆 **Performance Targets**

### **Recommended Performance Metrics**

| Metric | Target | Good | Needs Improvement |
|--------|--------|------|-------------------|
| **Response Time** | <100ms | <200ms | >500ms |
| **Memory Usage** | <50MB | <100MB | >200MB |
| **Database Queries** | <10 per request | <20 | >50 |
| **Cache Hit Ratio** | >80% | >60% | <40% |

### **Scaling Considerations**

1. **Horizontal Scaling**: Add more server instances
2. **Database Optimization**: Use read replicas, connection pooling
3. **Caching Strategy**: Implement Redis for distributed caching
4. **CDN Integration**: Cache static responses at edge locations

This comprehensive performance and caching system provides the foundation for building high-performance APIs with Apileon!
