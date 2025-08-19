# Apileon Framework - Portable Deployment Guide

## 🎯 Overview

This guide covers **portable deployment options** that allow Apileon to run **anywhere without requiring PHP installation or database setup**. Perfect for demos, development, testing, and rapid deployment.

---

## 🚀 Quick Start - Zero Installation

### Option 1: Docker (Recommended - No Dependencies)
```bash
# Clone or download Apileon
git clone https://github.com/bandeto45/apileon.git
cd apileon

# Start with Docker (everything included)
docker-compose -f docker-compose.portable.yml up

# Or use launcher scripts
./run-docker.sh        # Unix/Linux/macOS
./run-docker.bat       # Windows
```

**Access:** http://localhost:8000

### Option 2: Self-Contained Executable
```bash
# Generate single executable (everything bundled)
php create-standalone.php

# Run the generated executable
./apileon-standalone-TIMESTAMP

# Works on any system - no dependencies!
```

### Option 3: Portable ZIP Package
```bash
# Create portable package
php create-portable.php

# Extract and run anywhere with PHP 8.1+
./apileon.sh          # Unix/Linux/macOS
./apileon.bat         # Windows
php start.php         # Cross-platform
```

---

## 📋 Deployment Comparison

| Method | Dependencies | Size | Portability | Best For |
|--------|-------------|------|-------------|----------|
| **Docker** | Docker only | ~200MB | Excellent | Production, demos |
| **Executable** | None | ~50MB | Perfect | Single deployments |
| **ZIP Package** | PHP 8.1+ | ~10MB | Good | Development, testing |
| **WebAssembly** | Modern browser | ~100MB | Browser-only | Web demos |

---

## 🔧 Detailed Setup Instructions

### Docker Deployment

#### Prerequisites
- Docker installed on target system
- 2GB available disk space

#### Step-by-Step
```bash
# 1. Download Apileon
git clone https://github.com/bandeto45/apileon.git
cd apileon

# 2. Build and start container
docker-compose -f docker-compose.portable.yml up -d

# 3. Verify deployment
curl http://localhost:8000/health

# 4. View logs
docker logs apileon-api

# 5. Stop when done
docker-compose -f docker-compose.portable.yml down
```

#### What's Included
- ✅ PHP 8.1 runtime
- ✅ Nginx web server  
- ✅ SQLite database with sample data
- ✅ Complete Apileon framework
- ✅ Health monitoring
- ✅ Persistent data storage

---

### Self-Contained Executable

#### Generation
```bash
# Create the executable
php create-standalone.php

# Output: apileon-standalone-YYYY-MM-DD-HH-MM-SS
```

#### Features
- **Single file** contains everything
- **No dependencies** required
- **Cross-platform** (Windows, macOS, Linux)
- **Embedded database** with sample data
- **Built-in web server**

#### Usage
```bash
# Simply run the executable
./apileon-standalone-2023-12-01-14-30-15

# Command line options
./apileon-standalone-* --help      # Show help
./apileon-standalone-* --version   # Show version
./apileon-standalone-* --info      # System information
```

#### How It Works
1. **Embedded Data**: Application files compressed and embedded
2. **Temporary Extraction**: Files extracted to temp directory on startup
3. **Built-in Server**: PHP built-in server starts automatically
4. **Auto-cleanup**: Temporary files removed on shutdown

---

### Portable ZIP Package

#### Generation
```bash
# Create portable package
php create-portable.php

# Output: portable-build/apileon-portable-TIMESTAMP/
```

#### Contents
```
apileon-portable-TIMESTAMP/
├── app/                    # Complete Apileon framework
│   ├── database/          # SQLite database
│   ├── public/            # Web root
│   └── ...
├── apileon.sh             # Unix launcher
├── apileon.bat            # Windows launcher  
├── start.php              # Cross-platform launcher
└── README.txt             # Instructions
```

#### Deployment
```bash
# 1. Extract package
unzip apileon-portable-TIMESTAMP.zip

# 2. Run launcher
cd apileon-portable-TIMESTAMP/
./apileon.sh               # Unix/Linux/macOS
# OR
./apileon.bat              # Windows
# OR  
php start.php              # Any platform
```

---

### WebAssembly Version (Experimental)

#### Generation
```bash
# Create WASM version
php deploy-portable.php
# Select option [5] WebAssembly Version
```

#### Deployment
```bash
# Serve the WASM build
cd wasm-build/
python -m http.server 8080

# Open in browser
# http://localhost:8080
```

#### Limitations
- ⚠️ **Experimental** - Proof of concept
- 🌐 **Browser-only** - Runs in web browser
- 🔬 **Limited functionality** - Basic demo only

---

## 🎯 Use Cases

### Development & Testing
```bash
# Quick development environment
php create-portable.php
# Extract and start coding immediately
```

### Client Demos
```bash
# Self-contained demo
php create-standalone.php
# Send single executable to client
```

### Training & Education
```bash
# Docker classroom setup  
docker-compose -f docker-compose.portable.yml up
# All students get identical environment
```

### Rapid Prototyping
```bash
# Instant API server
./apileon-standalone-*
# Start building APIs immediately
```

---

## 🗄️ Database & Sample Data

All portable versions include **SQLite database** with sample data:

### Sample Users
| Email | Password | Role |
|-------|----------|------|
| john@example.com | password | User |
| jane@example.com | password | User |
| bob@example.com | password | User |

### Sample API Endpoints
```bash
# Users
GET    /users              # List users
GET    /users/1            # Get user by ID
POST   /users              # Create user
PUT    /users/1            # Update user
DELETE /users/1            # Delete user

# Posts  
GET    /posts              # List posts
GET    /posts/1            # Get post by ID
POST   /posts              # Create post

# System
GET    /health             # Health check
GET    /docs               # API documentation
```

### Database File Location
- **Docker**: `/app/database/apileon.sqlite`
- **Executable**: `temp-dir/database/apileon.sqlite`
- **ZIP Package**: `app/database/apileon.sqlite`

---

## 🔧 Customization

### Environment Configuration
All versions support environment customization via `.env`:

```env
# Application
APP_ENV=portable
APP_DEBUG=false
APP_URL=http://localhost:8000

# Database (SQLite)
DB_CONNECTION=sqlite
DB_DATABASE=./database/apileon.sqlite

# Performance
CACHE_DRIVER=file
LOG_LEVEL=info
```

### Adding Custom Routes
Edit `routes/api.php`:
```php
use Apileon\Routing\Route;

Route::get('/custom', function() {
    return ['message' => 'Custom endpoint works!'];
});
```

### Custom Controllers
Create in `app/Controllers/`:
```php
<?php
namespace App\Controllers;

class CustomController
{
    public function index()
    {
        return ['data' => 'Custom controller response'];
    }
}
```

---

## 🛠️ Troubleshooting

### Common Issues

#### Docker Issues
```bash
# Docker not running
sudo systemctl start docker    # Linux
# Start Docker Desktop         # Windows/macOS

# Port already in use
docker-compose -f docker-compose.portable.yml down
# OR change port in docker-compose.portable.yml
```

#### PHP Issues
```bash
# PHP not found (ZIP package)
./install-php-and-run.sh      # Auto-install PHP

# Version too old
php --version                  # Check current version
# Install PHP 8.1+ manually
```

#### Permission Issues
```bash
# Unix systems
chmod +x apileon.sh
chmod +x apileon-standalone-*
chmod +x install-php-and-run.sh
```

#### Database Issues
```bash
# Corrupted database
rm app/database/apileon.sqlite
# Restart - database will be recreated
```

### Log Files
- **Docker**: `docker logs apileon-api`
- **Executable**: `temp-dir/storage/logs/`
- **ZIP Package**: `app/storage/logs/`

---

## 📊 Performance Considerations

### Resource Usage
| Method | RAM | CPU | Disk |
|--------|-----|-----|------|
| Docker | ~100MB | Low | ~200MB |
| Executable | ~50MB | Low | ~50MB |
| ZIP Package | ~30MB | Low | ~10MB |

### Optimization Tips
```bash
# Production optimizations
APP_DEBUG=false              # Disable debug mode
LOG_LEVEL=error             # Reduce logging
CACHE_DRIVER=file           # Enable file caching
```

---

## 🔒 Security Considerations

### Portable Security Features
- ✅ **Input validation** on all endpoints
- ✅ **SQL injection protection** via prepared statements
- ✅ **Password hashing** with bcrypt
- ✅ **CORS headers** properly configured
- ✅ **Error handling** without information disclosure

### Additional Security
```bash
# Change default passwords
php artisan user:password john@example.com

# Update app key
php artisan key:generate

# Enable HTTPS (production)
APP_URL=https://your-domain.com
```

---

## 🚀 Advanced Deployment

### CI/CD Integration
```yaml
# GitHub Actions example
name: Build Portable Apileon
on: [push]
jobs:
  build:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v2
      - name: Build Portable Versions
        run: php deploy-portable.php
      - name: Upload Artifacts
        uses: actions/upload-artifact@v2
        with:
          name: apileon-portable
          path: |
            portable-build/
            standalone-build/
```

### Multi-Platform Builds
```bash
# Build for all platforms
php deploy-portable.php
# Select option [4] All Portable Versions

# Creates:
# - Docker image (any platform)
# - Windows executable  
# - Linux executable
# - macOS executable
# - Portable ZIP package
```

---

## 📚 Next Steps

### After Deployment
1. **Test all endpoints**: `curl http://localhost:8000/health`
2. **Review sample data**: Check `/users` and `/posts` endpoints
3. **Add custom logic**: Modify controllers and routes
4. **Configure production**: Update `.env` for production use

### Scaling Up
- **Load Balancing**: Run multiple Docker containers
- **External Database**: Switch from SQLite to MySQL/PostgreSQL
- **Monitoring**: Add application monitoring and alerting
- **CDN**: Add CDN for static assets

### Documentation
- **API Docs**: Available at `/docs` endpoint
- **Framework Guide**: See main README.md
- **Development**: Check `docs/` folder for detailed guides

---

## 🎉 Success!

You now have Apileon running in portable mode! 

🌐 **Access your API at:** http://localhost:8000  
📚 **View documentation at:** http://localhost:8000/docs  
💾 **Sample data ready** for immediate testing  

**Enjoy building amazing APIs with Apileon! 🦁**
