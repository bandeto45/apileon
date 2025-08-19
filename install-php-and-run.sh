#!/bin/bash
# Apileon Framework - PHP Installer for Portable Deployment
# Automatically installs PHP 8.1+ if not available

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'  
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "${BLUE}"
    echo "=============================================="
    echo "  Apileon Framework - PHP Auto-Installer"
    echo "=============================================="
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

detect_os() {
    if [[ "$OSTYPE" == "linux-gnu"* ]]; then
        if [ -f /etc/debian_version ]; then
            echo "debian"
        elif [ -f /etc/redhat-release ]; then
            echo "redhat"
        elif [ -f /etc/arch-release ]; then
            echo "arch"
        else
            echo "linux"
        fi
    elif [[ "$OSTYPE" == "darwin"* ]]; then
        echo "macos"
    elif [[ "$OSTYPE" == "cygwin" ]]; then
        echo "windows"
    elif [[ "$OSTYPE" == "msys" ]]; then
        echo "windows"
    else
        echo "unknown"
    fi
}

check_php() {
    if command -v php &> /dev/null; then
        PHP_VERSION=$(php -v | head -n 1 | cut -d ' ' -f 2)
        if php -r "exit(version_compare(PHP_VERSION, '8.1.0', '<') ? 1 : 0);"; then
            print_error "PHP version $PHP_VERSION is too old. Need PHP 8.1+"
            return 1
        else
            print_success "PHP $PHP_VERSION found and compatible"
            return 0
        fi
    else
        print_warning "PHP not found"
        return 1
    fi
}

install_php_debian() {
    print_info "Installing PHP 8.1+ on Debian/Ubuntu..."
    
    sudo apt update
    sudo apt install -y software-properties-common
    sudo add-apt-repository ppa:ondrej/php -y
    sudo apt update
    sudo apt install -y php8.1 php8.1-cli php8.1-sqlite3 php8.1-mbstring php8.1-json php8.1-curl
    
    print_success "PHP 8.1 installed successfully"
}

install_php_redhat() {
    print_info "Installing PHP 8.1+ on RedHat/CentOS/Fedora..."
    
    if command -v dnf &> /dev/null; then
        sudo dnf install -y php php-cli php-pdo php-sqlite3 php-mbstring php-json
    else
        sudo yum install -y php php-cli php-pdo php-sqlite3 php-mbstring php-json
    fi
    
    print_success "PHP installed successfully"
}

install_php_arch() {
    print_info "Installing PHP 8.1+ on Arch Linux..."
    
    sudo pacman -S --noconfirm php php-sqlite
    
    print_success "PHP installed successfully"
}

install_php_macos() {
    print_info "Installing PHP 8.1+ on macOS..."
    
    if command -v brew &> /dev/null; then
        brew install php
        print_success "PHP installed via Homebrew"
    else
        print_error "Homebrew not found. Please install Homebrew first:"
        echo "  /bin/bash -c \"\$(curl -fsSL https://raw.githubusercontent.com/Homebrew/install/HEAD/install.sh)\""
        echo "  Then run: brew install php"
        return 1
    fi
}

install_php() {
    OS=$(detect_os)
    
    case $OS in
        "debian")
            install_php_debian
            ;;
        "redhat")  
            install_php_redhat
            ;;
        "arch")
            install_php_arch
            ;;
        "macos")
            install_php_macos
            ;;
        "windows")
            print_error "Windows detected. Please install PHP manually:"
            echo "  1. Download PHP from: https://windows.php.net/download/"
            echo "  2. Extract to C:\\php\\"
            echo "  3. Add C:\\php to your PATH"
            echo "  4. Run this script again"
            return 1
            ;;
        *)
            print_error "Unsupported operating system: $OS"
            echo "Please install PHP 8.1+ manually and try again"
            return 1
            ;;
    esac
}

run_apileon() {
    print_info "Starting Apileon Framework..."
    
    # Check if we have Apileon files
    if [ -f "start.php" ]; then
        php start.php
    elif [ -f "apileon.sh" ]; then
        ./apileon.sh
    elif [ -f "deploy-portable.php" ]; then
        php deploy-portable.php
    else
        print_error "Apileon files not found in current directory"
        echo "Please ensure you're in the Apileon directory"
        return 1
    fi
}

main() {
    print_header
    
    print_info "Checking PHP installation..."
    
    if check_php; then
        print_success "PHP requirements satisfied"
        run_apileon
    else
        print_warning "PHP 8.1+ not found. Installing..."
        
        if install_php; then
            print_success "PHP installation completed"
            
            # Verify installation
            if check_php; then
                run_apileon
            else
                print_error "PHP installation verification failed"
                exit 1
            fi
        else
            print_error "PHP installation failed"
            exit 1
        fi
    fi
}

# Run if executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
