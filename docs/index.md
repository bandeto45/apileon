# Apileon Framework Documentation Index

Welcome to the complete documentation for the Apileon PHP framework - a lightweight, enterprise-ready framework focused exclusively on REST API development.

## 📖 Documentation Overview

### Core Documentation
- **[Getting Started Guide](README.md)** - Complete framework documentation and setup
- **[API Reference](API.md)** - Complete API endpoints with examples
- **[Routing Guide](routing.md)** - Comprehensive routing system documentation
- **[Middleware Guide](middleware.md)** - Security, CORS, authentication, and custom middleware
- **[Testing Guide](testing.md)** - Unit testing, integration testing, and API testing

## 🚀 Quick Navigation

### For Beginners
1. [Installation & Setup](README.md#installation)
2. [Your First API](README.md#getting-started)
3. [Basic Routing](routing.md#basic-route-definition)
4. [Simple Controllers](README.md#controllers)

### For Developers
1. [Advanced Routing](routing.md#route-groups)
2. [Middleware Development](middleware.md#creating-middleware)
3. [Model System](README.md#models)
4. [Error Handling](README.md#error-handling)

### For Teams
1. [Testing Strategy](testing.md#overview)
2. [Project Structure](README.md#configuration)
3. [Best Practices](middleware.md#best-practices)
4. [Deployment Guide](README.md#deployment)

## 📚 Documentation Sections

### 1. Framework Core
- **Request/Response System** - HTTP handling, JSON processing, headers
- **Routing Engine** - Parameter extraction, route groups, RESTful patterns
- **Application Foundation** - Lifecycle, configuration, environment management
- **Helper Functions** - Global utilities and shortcuts

### 2. Security & Middleware
- **Authentication** - Bearer token validation
- **CORS Handling** - Cross-origin request support
- **Rate Limiting** - API throttling and protection
- **Custom Middleware** - Building your own middleware layers

### 3. Development Tools
- **Testing Framework** - PHPUnit integration, mocking, API testing
- **Error Handling** - Consistent error responses and debugging
- **Configuration Management** - Environment variables and config files
- **Development Server** - Built-in PHP server setup

### 4. API Development
- **RESTful Conventions** - Standard REST patterns and best practices
- **JSON-First Design** - Automatic JSON handling and responses
- **Validation** - Input validation and error responses
- **Status Codes** - Proper HTTP status code usage

## 🎯 Framework Features

### ✅ Production Ready
- Enterprise-grade architecture
- PSR standards compliance
- Comprehensive error handling
- Security-first design

### ✅ Developer Friendly
- Zero configuration setup
- Intuitive API design
- Comprehensive documentation
- Full testing coverage

### ✅ Performance Focused
- Lightweight core
- Minimal dependencies
- Fast routing engine
- Optimized for APIs

### ✅ Extensible
- Modular design
- Custom middleware support
- Flexible configuration
- Easy integration

## 📋 Quick Reference

### Common Tasks

**Create a Route:**
```php
Route::get('/users', 'UserController@index');
```

**Add Middleware:**
```php
Route::get('/protected', 'Controller@method')->middleware('auth');
```

**Return JSON:**
```php
return Response::json(['data' => $result]);
```

**Handle Errors:**
```php
return abort(404, 'Resource not found');
```

### HTTP Methods
- `GET` - Retrieve resources
- `POST` - Create resources
- `PUT` - Update resources (full)
- `PATCH` - Update resources (partial)
- `DELETE` - Remove resources
- `OPTIONS` - CORS preflight

### Status Codes
- `200` - OK
- `201` - Created
- `400` - Bad Request
- `401` - Unauthorized
- `403` - Forbidden
- `404` - Not Found
- `422` - Validation Error
- `429` - Rate Limited
- `500` - Server Error

## 🛠 Development Workflow

### 1. Setup Project
```bash
git clone https://github.com/bandeto45/apileon.git
cd apileon
./setup.sh
```

### 2. Define Routes
Edit `routes/api.php`:
```php
Route::get('/api/resource', 'ResourceController@index');
```

### 3. Create Controller
Create `app/Controllers/ResourceController.php`:
```php
class ResourceController {
    public function index(Request $request): Response {
        return Response::json(['data' => []]);
    }
}
```

### 4. Add Middleware (Optional)
```php
Route::get('/protected', 'Controller@method')->middleware('auth');
```

### 5. Test Your API
```bash
composer serve
curl http://localhost:8000/api/resource
```

### 6. Write Tests
```php
public function testResourceEndpoint() {
    $response = $this->get('/api/resource');
    $this->assertEquals(200, $response->getStatusCode());
}
```

## 🔗 External Resources

### Development Tools
- **Composer** - Dependency management
- **PHPUnit** - Testing framework
- **Mockery** - Mocking library
- **Postman** - API testing
- **Insomnia** - API client

### PHP Resources
- **PHP Documentation** - https://php.net/docs
- **PSR Standards** - https://www.php-fig.org/psr/
- **Composer Documentation** - https://getcomposer.org/doc/

### API Best Practices
- **REST API Design** - RESTful principles
- **HTTP Status Codes** - Proper status code usage
- **JSON API Specification** - JSON response standards
- **API Security** - Authentication and authorization

## 🤝 Contributing

We welcome contributions to both the framework and documentation:

1. **Documentation Improvements** - Fix typos, add examples, improve clarity
2. **Code Examples** - Add more practical examples and use cases
3. **Framework Features** - Contribute new features and enhancements
4. **Testing** - Add more test cases and improve coverage

See [CONTRIBUTING.md](../CONTRIBUTING.md) for detailed guidelines.

## 📞 Support

### Getting Help
- **GitHub Issues** - Report bugs and request features
- **Discussions** - Ask questions and share ideas
- **Documentation** - Comprehensive guides and examples

### Community
- **Contributors** - Active developer community
- **Examples** - Real-world usage examples
- **Best Practices** - Shared knowledge and patterns

---

**Apileon Framework** - Building REST APIs the right way. 🦁

*This documentation is continuously updated. For the latest version, visit the [GitHub repository](https://github.com/bandeto45/apileon).*
