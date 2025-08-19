# Apileon Framework - Secured Database CRUD System

## Overview

Your Apileon framework now includes a comprehensive, enterprise-grade secured database CRUD system with the following features:

- **Database Management**: PDO-based connection management with support for MySQL, PostgreSQL, and SQLite
- **Query Builder**: Fluent interface with SQL injection protection and parameter binding
- **Enhanced Models**: ORM-style models with mass assignment protection and validation
- **Input Validation**: Comprehensive validation system with 20+ validation rules
- **Secured Controllers**: Full CRUD operations with security, validation, and error handling
- **Migration System**: Database schema versioning and management
- **CLI Tools**: Artisan-style command line interface for development tasks

## Installation & Setup

### 1. Install PHP (if not already installed)

On macOS:
```bash
# Using Homebrew
brew install php

# Or using MacPorts
sudo port install php81

# Or download from https://www.php.net/downloads.php
```

### 2. Configure Database

1. Copy the environment file:
```bash
cp .env.example .env
```

2. Edit `.env` file with your database credentials:
```bash
# For MySQL
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon
DB_USERNAME=root
DB_PASSWORD=your_password

# For SQLite (simpler for development)
DB_CONNECTION=sqlite
DB_DATABASE=./database/database.sqlite

# For PostgreSQL
DB_CONNECTION=pgsql
DB_HOST=127.0.0.1
DB_PORT=5432
DB_DATABASE=apileon
DB_USERNAME=postgres
DB_PASSWORD=your_password
```

### 3. Run Database Migrations

```bash
# Create and run the users table migration
php artisan migrate

# Seed the database with sample data
php artisan db:seed
```

## Available CLI Commands

### Database Operations
```bash
# Run migrations
php artisan migrate

# Rollback last migration
php artisan migrate:rollback

# Reset all migrations
php artisan migrate:reset

# Seed database with sample data
php artisan db:seed

# Test database connection
php artisan db:test
```

### Code Generation
```bash
# Generate a new model
php artisan make:model Post

# Generate a new controller
php artisan make:controller PostController

# Generate both model and controller
php artisan make:model Post --controller
```

### Development Server
```bash
# Start development server
php artisan serve

# Start on specific port
php artisan serve --port=8080
```

## Basic Usage Examples

### 1. Using Models

#### Create a User
```php
<?php
use App\Models\User;

// Create a new user
$user = User::create([
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'password' => 'secure_password'
]);

// The password is automatically hashed
echo $user->id; // Auto-generated ID
```

#### Find Users
```php
// Find by ID
$user = User::find(1);

// Find by email
$user = User::where('email', 'john@example.com')->first();

// Get all users
$users = User::all();

// Get with pagination
$users = User::paginate(10, 1); // 10 per page, page 1
```

#### Update a User
```php
$user = User::find(1);
$user->update([
    'name' => 'John Smith',
    'email' => 'johnsmith@example.com'
]);
```

#### Delete a User
```php
$user = User::find(1);
$user->delete();

// Or delete by ID
User::destroy(1);
```

### 2. Using Controllers

The `UserController` provides REST API endpoints:

- `GET /users` - List all users (with pagination)
- `GET /users/{id}` - Get specific user
- `POST /users` - Create new user
- `PUT /users/{id}` - Update user
- `DELETE /users/{id}` - Delete user
- `POST /users/bulk` - Bulk create users

#### Example Routes (add to `routes/web.php`):
```php
<?php
use App\Controllers\UserController;
use Apileon\Routing\Route;

// User CRUD routes
Route::get('/users', [UserController::class, 'index']);
Route::get('/users/{id}', [UserController::class, 'show']);
Route::post('/users', [UserController::class, 'store']);
Route::put('/users/{id}', [UserController::class, 'update']);
Route::delete('/users/{id}', [UserController::class, 'destroy']);
Route::post('/users/bulk', [UserController::class, 'bulkStore']);
```

### 3. Using Query Builder

```php
<?php
use Apileon\Database\DatabaseManager;

$db = DatabaseManager::getInstance();

// Select with conditions
$users = $db->table('users')
    ->select(['id', 'name', 'email'])
    ->where('status', 'active')
    ->orderBy('created_at', 'DESC')
    ->limit(10)
    ->get();

// Join tables
$posts = $db->table('posts')
    ->join('users', 'posts.user_id', '=', 'users.id')
    ->select(['posts.*', 'users.name as author'])
    ->where('posts.status', 'published')
    ->get();

// Insert data
$userId = $db->table('users')->insert([
    'name' => 'Jane Doe',
    'email' => 'jane@example.com',
    'password' => password_hash('secret', PASSWORD_DEFAULT)
]);

// Update data
$affected = $db->table('users')
    ->where('id', 1)
    ->update(['status' => 'inactive']);

// Delete data
$deleted = $db->table('users')
    ->where('status', 'inactive')
    ->delete();
```

### 4. Validation Examples

```php
<?php
use Apileon\Validation\Validator;

$data = [
    'name' => 'John Doe',
    'email' => 'john@example.com',
    'age' => 25,
    'password' => 'secure123'
];

$rules = [
    'name' => 'required|string|min:2|max:50',
    'email' => 'required|email|unique:users,email',
    'age' => 'required|integer|min:18|max:120',
    'password' => 'required|string|min:8'
];

$validator = new Validator();

if ($validator->validate($data, $rules)) {
    // Data is valid
    echo "Validation passed!";
} else {
    // Get validation errors
    $errors = $validator->getErrors();
    foreach ($errors as $field => $messages) {
        echo "$field: " . implode(', ', $messages) . "\n";
    }
}
```

## Available Validation Rules

- `required` - Field is required
- `string` - Must be a string
- `integer` - Must be an integer
- `numeric` - Must be numeric
- `email` - Must be valid email
- `url` - Must be valid URL
- `date` - Must be valid date
- `boolean` - Must be boolean
- `array` - Must be array
- `min:value` - Minimum length/value
- `max:value` - Maximum length/value
- `between:min,max` - Between min and max
- `unique:table,column` - Must be unique in database
- `exists:table,column` - Must exist in database
- `regex:pattern` - Must match regex pattern
- `in:value1,value2` - Must be one of specified values
- `not_in:value1,value2` - Must not be one of specified values
- `confirmed` - Must match field_confirmation
- `alpha` - Must contain only letters
- `alpha_num` - Must contain only letters and numbers

## Security Features

### 1. SQL Injection Prevention
- All queries use prepared statements with parameter binding
- Query builder automatically escapes and validates input
- No raw SQL concatenation

### 2. Mass Assignment Protection
```php
// Protected in User model
protected $fillable = ['name', 'email', 'password'];
protected $guarded = ['id', 'created_at', 'updated_at'];
```

### 3. Password Security
```php
// Automatic password hashing in User model
public function setPasswordAttribute($value): void
{
    $this->attributes['password'] = password_hash($value, PASSWORD_DEFAULT);
}

// Password verification
public function verifyPassword(string $password): bool
{
    return password_verify($password, $this->password);
}
```

### 4. Input Validation
- Comprehensive validation on all inputs
- Custom validation rules and messages
- Automatic error handling and response formatting

## Creating Custom Models

### 1. Generate Model
```bash
php artisan make:model Post
```

### 2. Define Model Structure
```php
<?php
namespace App\Models;

class Post extends Model
{
    protected $table = 'posts';
    
    protected $fillable = [
        'title', 'content', 'user_id', 'status'
    ];
    
    protected $guarded = [
        'id', 'created_at', 'updated_at'
    ];
    
    protected $casts = [
        'published_at' => 'datetime',
        'is_featured' => 'boolean'
    ];
    
    // Relationships
    public function user()
    {
        return $this->belongsTo(User::class);
    }
}
```

### 3. Create Migration
```php
<?php
// database/migrations/YYYY_MM_DD_HHMMSS_create_posts_table.php
use Apileon\Database\Migration;
use Apileon\Database\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('posts', function($table) {
            $table->id();
            $table->string('title');
            $table->text('content');
            $table->foreignId('user_id')->references('id')->on('users');
            $table->enum('status', ['draft', 'published', 'archived'])->default('draft');
            $table->boolean('is_featured')->default(false);
            $table->timestamp('published_at')->nullable();
            $table->timestamps();
        });
    }
    
    public function down(): void
    {
        Schema::dropIfExists('posts');
    }
};
```

## Testing the System

### 1. Test Database Connection
```bash
php artisan db:test
```

### 2. Test User CRUD Operations
```php
<?php
// Create test script: test_crud.php
require_once __DIR__ . '/vendor/autoload.php';

use App\Models\User;

// Test create
$user = User::create([
    'name' => 'Test User',
    'email' => 'test@example.com',
    'password' => 'testpassword'
]);

echo "Created user: " . $user->id . "\n";

// Test read
$foundUser = User::find($user->id);
echo "Found user: " . $foundUser->name . "\n";

// Test update
$foundUser->update(['name' => 'Updated User']);
echo "Updated user: " . $foundUser->name . "\n";

// Test delete
$foundUser->delete();
echo "User deleted\n";
```

Run with: `php test_crud.php`

## Production Deployment

### 1. Environment Configuration
```bash
# Set production environment
APP_ENV=production
APP_DEBUG=false

# Use strong encryption keys
APP_KEY=your-32-character-secret-key
JWT_SECRET=your-jwt-secret-key

# Configure production database
DB_CONNECTION=mysql
DB_HOST=your-production-host
DB_DATABASE=your-production-db
DB_USERNAME=your-db-user
DB_PASSWORD=your-secure-password
```

### 2. Security Recommendations
- Use HTTPS in production
- Implement rate limiting
- Add CSRF protection
- Use environment variables for secrets
- Regular security updates
- Monitor for suspicious activity

## Troubleshooting

### Common Issues

1. **Database Connection Failed**
   - Check database credentials in `.env`
   - Ensure database server is running
   - Verify database exists

2. **Migration Errors**
   - Check database permissions
   - Ensure migrations table exists
   - Verify migration syntax

3. **Validation Errors**
   - Check validation rules syntax
   - Ensure required fields are provided
   - Verify data types match rules

### Debug Mode
Enable debug mode in `.env`:
```bash
APP_DEBUG=true
```

This will show detailed error messages and stack traces.

## Support

For issues and questions:
1. Check the error logs
2. Enable debug mode for detailed errors
3. Verify database configuration
4. Test with simple operations first

Your Apileon framework now has a complete, enterprise-grade secured database CRUD system ready for production use!
