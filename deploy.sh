#!/bin/bash

# Apileon Framework - Production Deployment Script
# This script automates the deployment process for Apileon API

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

# Configuration
DEPLOYMENT_TYPE=""
DOMAIN=""
DB_TYPE="mysql"
PHP_VERSION="8.1"
APP_PATH="/var/www/apileon"

# Functions
print_header() {
    echo -e "${BLUE}"
    echo "============================================"
    echo "  Apileon Framework Production Deployment"
    echo "============================================"
    echo -e "${NC}"
}

print_success() {
    echo -e "${GREEN}✓ $1${NC}"
}

print_warning() {
    echo -e "${YELLOW}⚠ $1${NC}"
}

print_error() {
    echo -e "${RED}✗ $1${NC}"
}

print_info() {
    echo -e "${BLUE}ℹ $1${NC}"
}

# Check if running as root
check_root() {
    if [[ $EUID -ne 0 ]]; then
        print_error "This script must be run as root (use sudo)"
        exit 1
    fi
}

# Detect OS
detect_os() {
    if [[ -f /etc/ubuntu-release ]] || [[ -f /etc/debian_version ]]; then
        OS="debian"
        PACKAGE_MANAGER="apt"
    elif [[ -f /etc/centos-release ]] || [[ -f /etc/redhat-release ]]; then
        OS="rhel"
        PACKAGE_MANAGER="yum"
    else
        print_error "Unsupported operating system"
        exit 1
    fi
    print_info "Detected OS: $OS"
}

# Install dependencies
install_dependencies() {
    print_info "Installing system dependencies..."
    
    if [[ $PACKAGE_MANAGER == "apt" ]]; then
        apt update
        apt install -y software-properties-common curl wget git unzip
        
        # Add PHP repository
        add-apt-repository ppa:ondrej/php -y
        apt update
        
        # Install PHP and extensions
        apt install -y php${PHP_VERSION} php${PHP_VERSION}-fpm php${PHP_VERSION}-mysql \
                       php${PHP_VERSION}-pdo php${PHP_VERSION}-mbstring php${PHP_VERSION}-json \
                       php${PHP_VERSION}-curl php${PHP_VERSION}-xml php${PHP_VERSION}-zip \
                       php${PHP_VERSION}-opcache
        
        # Install web server
        apt install -y nginx
        
        # Install database (if local)
        if [[ $DB_TYPE == "mysql" ]]; then
            apt install -y mysql-server
        elif [[ $DB_TYPE == "postgresql" ]]; then
            apt install -y postgresql postgresql-contrib
        fi
        
    elif [[ $PACKAGE_MANAGER == "yum" ]]; then
        yum update -y
        yum install -y epel-release
        yum install -y php${PHP_VERSION//.} php${PHP_VERSION//.}-fpm php${PHP_VERSION//.}-mysql \
                       php${PHP_VERSION//.}-pdo php${PHP_VERSION//.}-mbstring php${PHP_VERSION//.}-json \
                       nginx git curl wget unzip
    fi
    
    print_success "Dependencies installed"
}

# Configure PHP
configure_php() {
    print_info "Configuring PHP for production..."
    
    PHP_INI="/etc/php/${PHP_VERSION}/fpm/php.ini"
    
    # Backup original
    cp $PHP_INI ${PHP_INI}.backup
    
    # Production settings
    sed -i 's/expose_php = On/expose_php = Off/' $PHP_INI
    sed -i 's/display_errors = On/display_errors = Off/' $PHP_INI
    sed -i 's/display_startup_errors = On/display_startup_errors = Off/' $PHP_INI
    sed -i 's/;log_errors = On/log_errors = On/' $PHP_INI
    sed -i 's/memory_limit = .*/memory_limit = 256M/' $PHP_INI
    sed -i 's/max_execution_time = .*/max_execution_time = 60/' $PHP_INI
    sed -i 's/post_max_size = .*/post_max_size = 32M/' $PHP_INI
    sed -i 's/upload_max_filesize = .*/upload_max_filesize = 32M/' $PHP_INI
    
    # Enable OPcache
    cat >> $PHP_INI << EOF

; OPcache for production
opcache.enable=1
opcache.enable_cli=0
opcache.memory_consumption=128
opcache.interned_strings_buffer=8
opcache.max_accelerated_files=4000
opcache.revalidate_freq=2
opcache.save_comments=1
opcache.validate_timestamps=0
EOF
    
    print_success "PHP configured for production"
}

# Deploy application
deploy_application() {
    print_info "Deploying Apileon application..."
    
    # Create application directory
    mkdir -p $APP_PATH
    cd $APP_PATH
    
    # Get application code (this would typically be your repository)
    if [[ -n "$REPO_URL" ]]; then
        git clone $REPO_URL .
    else
        print_warning "No repository URL provided. Please upload your application manually to $APP_PATH"
        return
    fi
    
    # Set up environment
    if [[ -f .env.example ]]; then
        cp .env.example .env
        print_warning "Please edit .env file with your production settings"
    fi
    
    # Set permissions
    chown -R www-data:www-data $APP_PATH
    chmod -R 755 $APP_PATH
    chmod 600 $APP_PATH/.env 2>/dev/null || true
    chmod +x $APP_PATH/artisan 2>/dev/null || true
    
    print_success "Application deployed"
}

# Configure Nginx
configure_nginx() {
    print_info "Configuring Nginx..."
    
    if [[ -z "$DOMAIN" ]]; then
        print_error "Domain not specified"
        return 1
    fi
    
    cat > /etc/nginx/sites-available/apileon << EOF
server {
    listen 80;
    server_name $DOMAIN;
    
    # Redirect HTTP to HTTPS (uncomment after SSL setup)
    # return 301 https://\$server_name\$request_uri;
    
    root $APP_PATH/public;
    index index.php;
    
    # Security headers
    add_header X-Frame-Options "SAMEORIGIN" always;
    add_header X-XSS-Protection "1; mode=block" always;
    add_header X-Content-Type-Options "nosniff" always;
    add_header Referrer-Policy "no-referrer-when-downgrade" always;
    
    # API routes
    location / {
        try_files \$uri \$uri/ /index.php?\$query_string;
    }
    
    # PHP processing
    location ~ \.php$ {
        fastcgi_pass unix:/var/run/php/php${PHP_VERSION}-fpm.sock;
        fastcgi_param SCRIPT_FILENAME \$realpath_root\$fastcgi_script_name;
        include fastcgi_params;
        fastcgi_hide_header X-Powered-By;
    }
    
    # Deny access to sensitive files
    location ~ /\. {
        deny all;
    }
    
    location ~ ^/(\.env|composer\.(json|lock)|package\.json|artisan) {
        deny all;
    }
    
    # Rate limiting
    limit_req_zone \$binary_remote_addr zone=api:10m rate=10r/s;
    limit_req zone=api burst=20 nodelay;
    
    # Logging
    access_log /var/log/nginx/apileon-access.log;
    error_log /var/log/nginx/apileon-error.log;
}
EOF
    
    # Enable site
    ln -sf /etc/nginx/sites-available/apileon /etc/nginx/sites-enabled/
    rm -f /etc/nginx/sites-enabled/default
    
    # Test configuration
    nginx -t
    
    print_success "Nginx configured"
}

# Setup database
setup_database() {
    if [[ $DB_TYPE == "mysql" ]]; then
        print_info "Setting up MySQL database..."
        
        # Secure MySQL installation
        mysql_secure_installation
        
        # Create database and user
        mysql -u root -p << EOF
CREATE DATABASE apileon CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
CREATE USER 'apileon'@'localhost' IDENTIFIED BY 'CHANGE_THIS_PASSWORD';
GRANT ALL PRIVILEGES ON apileon.* TO 'apileon'@'localhost';
FLUSH PRIVILEGES;
EOF
        
        print_success "MySQL database created"
        print_warning "Please update .env file with database credentials"
    fi
}

# Start services
start_services() {
    print_info "Starting services..."
    
    systemctl enable nginx
    systemctl enable php${PHP_VERSION}-fpm
    
    if [[ $DB_TYPE == "mysql" ]]; then
        systemctl enable mysql
        systemctl start mysql
    fi
    
    systemctl start php${PHP_VERSION}-fpm
    systemctl start nginx
    
    print_success "Services started"
}

# Run migrations
run_migrations() {
    print_info "Running database migrations..."
    
    cd $APP_PATH
    
    if [[ -f artisan ]]; then
        sudo -u www-data php artisan migrate --force
        print_success "Migrations completed"
    else
        print_warning "Artisan command not found. Please run migrations manually."
    fi
}

# Security hardening
security_hardening() {
    print_info "Applying security hardening..."
    
    # Firewall setup
    if command -v ufw &> /dev/null; then
        ufw --force enable
        ufw allow ssh
        ufw allow 'Nginx Full'
        print_success "Firewall configured"
    fi
    
    # Disable unnecessary services
    systemctl disable apache2 2>/dev/null || true
    
    # Set up fail2ban (if available)
    if command -v fail2ban-server &> /dev/null; then
        systemctl enable fail2ban
        systemctl start fail2ban
        print_success "Fail2ban enabled"
    fi
    
    print_success "Security hardening applied"
}

# Health check
health_check() {
    print_info "Performing health check..."
    
    sleep 5
    
    if curl -s http://localhost/health > /dev/null; then
        print_success "Health check passed"
    else
        print_warning "Health check failed. Please verify configuration."
    fi
}

# Main menu
show_menu() {
    echo
    echo "Select deployment type:"
    echo "1) Full server setup (new server)"
    echo "2) Application only (existing server)"
    echo "3) Docker deployment"
    echo "4) Exit"
    echo
    read -p "Enter your choice [1-4]: " choice
    
    case $choice in
        1)
            DEPLOYMENT_TYPE="full"
            ;;
        2)
            DEPLOYMENT_TYPE="app-only"
            ;;
        3)
            DEPLOYMENT_TYPE="docker"
            ;;
        4)
            exit 0
            ;;
        *)
            print_error "Invalid option"
            show_menu
            ;;
    esac
}

# Get configuration
get_config() {
    echo
    read -p "Enter your domain name (e.g., api.example.com): " DOMAIN
    read -p "Enter database type [mysql/postgresql/sqlite]: " DB_TYPE
    read -p "Enter repository URL (optional): " REPO_URL
    echo
}

# Docker deployment
deploy_docker() {
    print_info "Setting up Docker deployment..."
    
    # Install Docker
    if ! command -v docker &> /dev/null; then
        curl -fsSL https://get.docker.com -o get-docker.sh
        sh get-docker.sh
        systemctl enable docker
        systemctl start docker
    fi
    
    # Install Docker Compose
    if ! command -v docker-compose &> /dev/null; then
        curl -L "https://github.com/docker/compose/releases/download/1.29.2/docker-compose-$(uname -s)-$(uname -m)" -o /usr/local/bin/docker-compose
        chmod +x /usr/local/bin/docker-compose
    fi
    
    print_success "Docker installed"
    print_info "Please create docker-compose.yml and run: docker-compose up -d"
}

# Main execution
main() {
    print_header
    
    if [[ $# -eq 0 ]]; then
        show_menu
        get_config
    fi
    
    case $DEPLOYMENT_TYPE in
        "full")
            check_root
            detect_os
            install_dependencies
            configure_php
            deploy_application
            configure_nginx
            setup_database
            start_services
            run_migrations
            security_hardening
            health_check
            ;;
        "app-only")
            check_root
            deploy_application
            run_migrations
            systemctl restart nginx php${PHP_VERSION}-fpm
            health_check
            ;;
        "docker")
            check_root
            deploy_docker
            ;;
    esac
    
    echo
    print_success "Deployment completed!"
    echo
    print_info "Next steps:"
    echo "1. Edit .env file with your production settings"
    echo "2. Set up SSL certificate (Let's Encrypt recommended)"
    echo "3. Configure monitoring and backups"
    echo "4. Test your API endpoints"
    echo
    print_info "Your API should be accessible at: http://$DOMAIN"
    echo
}

# Run main function
main "$@"
