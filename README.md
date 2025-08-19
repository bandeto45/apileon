# Apileon 🦁
_A lightweight, enterprise-ready PHP framework focused only on REST APIs._

![License](https://img.shields.io/badge/license-MIT-blue.svg)
![PHP Version](https://img.shields.io/badge/PHP-%3E%3D8.1-8892BF.svg)
![Build Status](https://img.shields.io/badge/build-passing-brightgreen.svg)
![PRs Welcome](https://img.shields.io/badge/PRs-welcome-green.svg)

---

## 🚀 Overview
**Apileon** is a PHP framework built exclusively for **REST API development**.  
It is designed with **simplicity, speed, and scalability** in mind — removing unnecessary overhead and focusing on what matters most: **clean and powerful APIs**.

Think of Apileon as the **enterprise-grade foundation** for your next API project.

---

## ✨ Features
- ⚡ **REST-first architecture** – built only for APIs, no bloat  
- 🛠 **Simple Routing** – clean and fast endpoint definitions  
- 🔐 **Middleware Support** – authentication, logging, rate limiting  
- 📦 **Extensible Core** – modular design for enterprise projects  
- 📊 **JSON-first Communication** – optimized for modern web & mobile apps  
- 🧪 **Test-Friendly** – structured for PHPUnit & CI/CD pipelines  

---

## 📦 Installation
You can install Apileon via **Composer** (coming soon):

```bash
composer create-project apileon/framework my-api
```

Or clone and setup manually:

```bash
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup.sh
```

---

## 🛠 Quick Start

**1. Clone and setup**
```bash
git clone https://github.com/bandeto45/apileon.git my-api
cd my-api
./setup.sh
```

**2. Define your first route**  
Edit `routes/api.php`:
```php
use Apileon\Routing\Route;

Route::get('/hello', function () {
    return ['message' => 'Hello from Apileon!'];
});
```

**3. Start the built-in server**
```bash
composer serve
# or manually:
php -S localhost:8000 -t public
```

**4. Test your endpoint**
```bash
curl http://localhost:8000/hello
```
Response:
```json
{
  "message": "Hello from Apileon!"
}
```

---

## 📂 Project Structure
```
my-api/
├── app/                    # Application logic
│   ├── Controllers/        # HTTP controllers
│   ├── Models/            # Data models
│   └── Middleware/        # Custom middleware
├── config/                # Configuration files
│   ├── app.php           # App configuration
│   └── database.php      # Database configuration
├── docs/                  # Documentation
│   └── API.md            # API documentation
├── public/               # Public web root
│   └── index.php         # Entry point
├── routes/               # Route definitions
│   └── api.php           # API routes
├── src/                  # Framework core
│   ├── Foundation/       # Application foundation
│   ├── Http/            # HTTP components
│   ├── Routing/         # Routing system
│   └── Support/         # Helper utilities
├── tests/               # PHPUnit tests
├── vendor/              # Composer dependencies
├── .env.example         # Environment template
├── composer.json        # Dependencies & autoloading
├── phpunit.xml          # Testing configuration
├── setup.sh            # Setup script
└── status.sh           # Status check script
```

---

## ⚙️ Configuration
- `.env` file for environment variables  
- `config/` for database, caching, and app settings  

Example `.env`:
```env
APP_ENV=local
APP_KEY=base64:randomkeyhere
APP_DEBUG=true
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=apileon
DB_USERNAME=root
DB_PASSWORD=
```

---

## 🧩 Middleware Example
```php
use Apileon\Http\Middleware;

class AuthMiddleware extends Middleware {
    public function handle($request, $next) {
        if (!$request->header('Authorization')) {
            return response()->json(['error' => 'Unauthorized'], 401);
        }
        return $next($request);
    }
}
```

Attach middleware to routes:
```php
Route::get('/profile', 'UserController@profile')->middleware('auth');
```

---

## 🧪 Testing
Run PHPUnit tests:
```bash
vendor/bin/phpunit
```

---

## 📖 Documentation
Full documentation available at: [apileon.dev/docs](https://apileon.dev/docs) *(placeholder link)*

---

## 🤝 Contributing
Contributions are welcome!  
- Fork the repo  
- Create a feature branch  
- Submit a Pull Request  

See [CONTRIBUTING.md](CONTRIBUTING.md) for guidelines.

---

## 📜 License
Apileon is open-sourced software licensed under the [MIT license](LICENSE).

---

## 🌟 Acknowledgements
- Inspired by Laravel’s elegance & Slim’s simplicity  
- Built for developers who want **REST-only frameworks**  
