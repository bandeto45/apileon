# 🔍 Quick Reference - Apileon Framework

## **"Where do I put...?"** - Developer Quick Reference

| **I want to add...** | **Put it in** | **Example** |
|---------------------|---------------|-------------|
| **New API endpoint** | `routes/api.php` | `Route::get('/api/products', 'ProductController@index');` |
| **Controller logic** | `app/Controllers/` | `class ProductController { public function index() {...} }` |
| **Database model** | `app/Models/` | `class Product extends Model { protected $table = 'products'; }` |
| **Validation rules** | Model methods | `public static function validateForCreation(array $data)` |
| **Middleware** | `app/Middleware/` | `class AuthMiddleware extends Middleware` |
| **Database migration** | `database/migrations/` | `2024_08_19_create_products_table.php` |
| **Configuration** | `config/*.php` | `config/app.php`, `config/database.php` |
| **Helper functions** | `app/Helpers/` | Custom utility functions |
| **Tests** | `tests/` | `ProductControllerTest.php` |
| **Event listeners** | `app/Listeners/` | Handle events like user registration |

---

## **Common Tasks - Quick Commands**

### **Creating Your First API**
```bash
# 1. Add route
echo "Route::get('/api/hello', 'HelloController@index');" >> routes/api.php

# 2. Create controller
mkdir -p app/Controllers
cat > app/Controllers/HelloController.php << 'EOF'
<?php
namespace App\Controllers;

use Apileon\Http\Request;
use Apileon\Http\Response;

class HelloController
{
    public function index(Request $request): Response
    {
        return success_response(['message' => 'Hello World!']);
    }
}
EOF

# 3. Test it
curl http://localhost:8000/api/hello
```

### **Adding Database Model**
```bash
# 1. Create migration
php artisan make:migration create_products_table

# 2. Create model
mkdir -p app/Models
cat > app/Models/Product.php << 'EOF'
<?php
namespace App\Models;

class Product extends Model
{
    protected string $table = 'products';
    protected array $fillable = ['name', 'price', 'description'];
}
EOF
```

### **Adding Caching**
```php
// Cache expensive operations
$products = cache_remember('products_list', function() {
    return Product::all();
}, 600); // 10 minutes
```

### **Adding Events**
```php
// Fire event
event('product.created', ['product_id' => $product->id]);

// Listen to event (in Application.php)
listen('product.created', function($event, $data) {
    // Send notification, update cache, etc.
});
```

---

## **Emergency Debugging**

### **API Not Working?**
```bash
# Check if server is running
curl http://localhost:8000/health

# Check PHP errors
tail -f storage/logs/error.log

# Enable debug mode
echo "APP_DEBUG=true" >> .env
```

### **Database Issues?**
```bash
# Test database connection
php test-no-composer.php

# Check database config
cat config/database.php

# Run migrations
php artisan migrate
```

### **Performance Issues?**
```bash
# Check performance metrics (debug mode only)
curl http://localhost:8000/metrics

# Check cache status
curl http://localhost:8000/health
```

---

## **Framework File Structure**

```
my-api/
├── 📁 app/
│   ├── 📁 Controllers/          ← Your API controllers
│   ├── 📁 Models/              ← Database models
│   ├── 📁 Middleware/          ← Custom middleware
│   └── 📁 Helpers/             ← Utility functions
├── 📁 routes/
│   └── 📄 api.php              ← Define your API endpoints
├── 📁 config/
│   ├── 📄 app.php              ← App settings
│   └── 📄 database.php         ← Database config
├── 📁 tests/                   ← Your tests
├── 📁 storage/
│   ├── 📁 cache/               ← File cache
│   └── 📁 logs/                ← Error logs
└── 📄 .env                     ← Environment variables
```

---

## **One-Minute Setup**

```bash
# Clone and setup
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup-no-composer.sh

# Start server
php -S localhost:8000 -t public

# Test
curl http://localhost:8000/hello
```

---

**💡 Need more details?** Check the [Developer Guide](DEVELOPER_GUIDE.md) for comprehensive examples and best practices!
