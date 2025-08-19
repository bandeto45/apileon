#!/bin/bash

echo "🦁 Apileon Framework - No Composer Setup"
echo "========================================"
echo ""

# Check if we're in the right directory
if [ ! -f "autoload.php" ]; then
    echo "❌ Please run this script from the Apileon project root directory"
    exit 1
fi

echo "📁 Project Directory: $(pwd)"
echo ""

# Check PHP version
if command -v php &> /dev/null; then
    PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2 | cut -d '.' -f 1,2)
    echo "✅ PHP: $(php --version | head -n 1)"
    
    # Check if PHP version is >= 8.1
    if [ "$(echo "$PHP_VERSION >= 8.1" | bc -l 2>/dev/null || echo "0")" = "1" ] || [[ "$PHP_VERSION" > "8.0" ]]; then
        echo "✅ PHP version is compatible (8.1+)"
    else
        echo "⚠️  PHP 8.1+ recommended, you have $PHP_VERSION"
    fi
else
    echo "❌ PHP not found"
    echo "Please install PHP 8.1 or higher"
    exit 1
fi

echo ""

# Setup environment file
if [ ! -f ".env" ]; then
    echo "📝 Creating environment file..."
    cp .env.example .env
    echo "✅ Environment file created (.env)"
else
    echo "✅ Environment file exists"
fi

# Create necessary directories
echo "📂 Creating directories..."
mkdir -p storage/logs
mkdir -p storage/cache
mkdir -p storage/sessions

echo "✅ Directories created"

echo ""
echo "🎉 Setup complete! No Composer required."
echo ""
echo "📋 Available Commands:"
echo "  php -S localhost:8000 -t public -r public/index-no-composer.php"
echo "  # or use the regular index.php if you have no dependencies"
echo ""
echo "🧪 Test your setup:"
echo "  1. Start server: php -S localhost:8000 -t public"
echo "  2. Test endpoint: curl http://localhost:8000/hello"
echo ""
echo "📚 Documentation:"
echo "  README.md           - Getting started"
echo "  docs/               - Complete documentation"
echo ""
echo "⚡ Quick test (if server is running):"
echo "  curl http://localhost:8000/hello"
