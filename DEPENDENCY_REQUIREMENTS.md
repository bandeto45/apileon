# Apileon Framework - Dependency Requirements Guide

## 🎯 Quick Answer: YES, Both Options Work!

Apileon now supports **both scenarios**:
- ✅ **WITH PHP** - Full framework with all features
- ✅ **WITHOUT PHP** - Zero-dependency deployment via Docker

---

## 📊 Deployment Options Breakdown

### 🚀 **Option 1: ZERO Dependencies** ⭐ **RECOMMENDED**
```bash
./install-zero-deps.sh
```

**Requirements:** None (auto-installs Docker if needed)  
**What happens:**
1. Checks if Docker is available
2. Auto-installs Docker if missing (Linux/macOS)
3. Creates minimal Apileon container
4. Starts API server immediately

**Result:** Working API server with ZERO manual installation

---

### 🐳 **Option 2: Docker Deployment (Full Framework)**
```bash
docker-compose -f docker-compose.portable.yml up
```

**Requirements:** Docker installed  
**Includes:** Complete Apileon framework, SQLite, Nginx, PHP 8.1  
**Best for:** Production deployment with full features

---

### 💼 **Option 3: Portable ZIP Package**
```bash
php create-portable.php
# OR auto-install PHP:
./install-php-and-run.sh
```

**Requirements:** PHP 8.1+ (can be auto-installed)  
**Includes:** Complete framework, auto-installer for PHP  
**Best for:** Development on systems with PHP

---

### 📦 **Option 4: Self-Contained Executable**
```bash
php create-standalone.php
./apileon-standalone-TIMESTAMP
```

**Requirements:** PHP runtime (to run the executable)  
**Note:** Creates portable package but still needs PHP to execute  
**Best for:** Portable development environments

---

## 🔍 Detailed Feature Comparison

| Feature | Zero Deps | Docker Full | ZIP Package | Executable |
|---------|-----------|-------------|-------------|------------|
| **PHP Required** | ❌ No | ❌ No | ⚠️ Yes* | ⚠️ Yes |
| **Database Setup** | ❌ No | ❌ No | ❌ No | ❌ No |
| **Installation Time** | 30 sec | 1-2 min | 2 min | 1 min |
| **Final Size** | ~100MB | ~200MB | ~10MB | ~50MB |
| **Complete Framework** | ❌ Basic | ✅ Full | ✅ Full | ✅ Full |
| **Production Ready** | ✅ Yes | ✅ Yes | ⚠️ Dev/Test | ⚠️ Dev/Test |
| **Auto PHP Install** | N/A | N/A | ✅ Yes | ❌ No |

*\*ZIP Package includes auto-installer for PHP*

---

## 🚀 Quick Start Examples

### For Systems WITHOUT PHP or Docker:
```bash
# Download Apileon
git clone https://github.com/bandeto45/apileon.git
cd apileon

# Run zero-dependency installer
./install-zero-deps.sh

# That's it! API runs at http://localhost:8000
```

### For Systems WITH PHP:
```bash
# Use full framework
php deploy-portable.php
# Select option [2] for ZIP package

# Or direct:
php create-portable.php
./apileon.sh
```

### For Systems WITH Docker:
```bash
# Use full containerized framework
docker-compose -f docker-compose.portable.yml up
# Access at http://localhost:8000
```

---

## 🎯 Which Option Should You Choose?

### **Choose ZERO Dependencies if:**
- ✅ You want true "run anywhere" capability
- ✅ Target system has no PHP/dependencies
- ✅ You need quick demos or client presentations
- ✅ You want minimal setup time

### **Choose Docker Full if:**
- ✅ You need complete framework features
- ✅ You're deploying to production
- ✅ You want full database CRUD operations
- ✅ Docker is already available

### **Choose ZIP Package if:**
- ✅ You're developing on a system with PHP
- ✅ You want the complete development environment
- ✅ You need all framework features
- ✅ You're okay with PHP requirement

### **Choose Executable if:**
- ✅ You want a single portable file
- ✅ You're distributing to developers
- ✅ Target systems have PHP runtime
- ✅ You need middleware development

---

## 🔧 Testing Your Deployment

All deployment options provide these test endpoints:

```bash
# Health check
curl http://localhost:8000/health

# Sample users API
curl http://localhost:8000/users

# Create user (POST)
curl -X POST http://localhost:8000/users \
  -H "Content-Type: application/json" \
  -d '{"name":"Test User","email":"test@example.com"}'
```

**Expected Response:**
```json
{
  "message": "Apileon Framework running",
  "status": "healthy",
  "version": "1.0.0-portable"
}
```

---

## 📋 System Requirements Summary

### **Absolutely NO Requirements:**
- Zero Dependencies option auto-handles everything

### **Minimal Requirements (Docker):**
- Any system capable of running Docker
- ~2GB free disk space
- Internet connection for initial setup

### **Traditional Requirements (PHP):**
- PHP 8.1+ (can be auto-installed)
- Basic web server support
- SQLite support (usually included)

---

## 🎉 Success Scenarios

### **Scenario 1: Clean Ubuntu Server**
```bash
# Download and run
wget https://github.com/bandeto45/apileon/archive/main.zip
unzip main.zip && cd apileon-main
./install-zero-deps.sh
# ✅ API running in 30 seconds
```

### **Scenario 2: Windows Laptop (No Dev Tools)**
```bash
# Install Docker Desktop first, then:
docker-compose -f docker-compose.portable.yml up
# ✅ Full API framework running
```

### **Scenario 3: macOS with PHP**
```bash
php create-portable.php
./apileon.sh
# ✅ Complete development environment
```

### **Scenario 4: Demo on Client Machine**
```bash
# Generate executable on your machine:
php create-standalone.php

# Copy to client machine and run:
./apileon-standalone-TIMESTAMP
# ✅ Instant API demo
```

---

## 🔒 Security Note

All portable deployments include:
- ✅ Secure default configuration
- ✅ Input validation and sanitization
- ✅ SQL injection protection (prepared statements)
- ✅ CORS headers properly configured
- ✅ Error handling without information disclosure

---

## 📚 Next Steps

After deployment, you can:

1. **Test the API**: Use the sample endpoints
2. **Add custom routes**: Edit `routes/api.php`
3. **Create controllers**: Add to `app/Controllers/`
4. **Scale up**: Move to traditional deployment for production

---

## 🎯 Summary

**YES, Apileon works both WITH and WITHOUT PHP!**

- **🚀 WITHOUT PHP**: Use zero-dependency or Docker deployment
- **💻 WITH PHP**: Use portable ZIP or executable deployment
- **🎯 Best of both**: All options maintain the same API interface

Choose the deployment method that fits your environment and requirements. All methods provide a working Apileon API server with sample data ready for immediate use!
