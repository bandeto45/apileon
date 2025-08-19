#!/bin/bash

echo "🦁 Apileon Framework Status Check"
echo "================================="
echo ""

# Check current directory
if [ ! -f "composer.json" ]; then
    echo "❌ Not in Apileon project directory"
    echo "Please navigate to your Apileon project folder first."
    exit 1
fi

echo "📁 Project Directory: $(pwd)"
echo ""

# Check PHP
if command -v php &> /dev/null; then
    echo "✅ PHP: $(php --version | head -n 1)"
else
    echo "❌ PHP not found"
    echo "Please install PHP 8.1 or higher"
    exit 1
fi

# Check Composer
if command -v composer &> /dev/null; then
    echo "✅ Composer: $(composer --version | head -n 1)"
else
    echo "❌ Composer not found"
    echo "Please install Composer: https://getcomposer.org/"
    exit 1
fi

# Check vendor directory
if [ -d "vendor" ]; then
    echo "✅ Dependencies installed"
else
    echo "⚠️  Dependencies not installed"
    echo "Run: composer install"
fi

# Check .env file
if [ -f ".env" ]; then
    echo "✅ Environment configured"
else
    echo "⚠️  Environment not configured"
    echo "Copy .env.example to .env and configure"
fi

echo ""
echo "📋 Available Commands:"
echo "  composer install    - Install dependencies"
echo "  composer test       - Run tests"
echo "  composer serve      - Start development server"
echo "  php -S localhost:8000 -t public  - Start server manually"
echo ""
echo "📚 Documentation:"
echo "  README.md           - Getting started"
echo "  docs/API.md         - API documentation"
echo "  CONTRIBUTING.md     - Contributing guide"
