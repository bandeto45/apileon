#!/bin/bash

echo "🦁 Apileon Framework Setup"
echo "========================="
echo ""

# Check if PHP is installed
if ! command -v php &> /dev/null; then
    echo "❌ PHP is not installed on your system."
    echo ""
    echo "Please install PHP 8.1 or higher:"
    echo ""
    echo "macOS (using Homebrew):"
    echo "  brew install php"
    echo ""
    echo "Ubuntu/Debian:"
    echo "  sudo apt update"
    echo "  sudo apt install php8.1 php8.1-cli php8.1-mbstring php8.1-xml php8.1-curl"
    echo ""
    echo "After installing PHP, run this script again."
    exit 1
fi

echo "✅ PHP is installed: $(php --version | head -n 1)"

# Check if Composer is installed
if ! command -v composer &> /dev/null; then
    echo "❌ Composer is not installed."
    echo ""
    echo "Installing Composer..."
    curl -sS https://getcomposer.org/installer | php
    sudo mv composer.phar /usr/local/bin/composer
    
    if ! command -v composer &> /dev/null; then
        echo "❌ Failed to install Composer globally."
        echo "Please install Composer manually: https://getcomposer.org/download/"
        exit 1
    fi
fi

echo "✅ Composer is installed: $(composer --version | head -n 1)"

# Install dependencies
echo ""
echo "Installing dependencies..."
composer install

# Copy .env file
if [ ! -f .env ]; then
    echo "Creating .env file..."
    cp .env.example .env
    echo "✅ .env file created. Please configure your environment variables."
fi

echo ""
echo "🎉 Apileon framework is ready!"
echo ""
echo "To start the development server:"
echo "  php -S localhost:8000 -t public"
echo ""
echo "Test endpoints:"
echo "  curl http://localhost:8000/hello"
echo "  curl http://localhost:8000/api/users"
echo ""
echo "Documentation: https://github.com/bandeto45/apileon"
