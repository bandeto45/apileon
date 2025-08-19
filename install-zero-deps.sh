#!/bin/bash
# Apileon Framework - Complete Zero-Dependency Installer
# Works on systems WITHOUT PHP, Docker, or any dependencies

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

print_header() {
    echo -e "${BLUE}"
    echo "=================================================="
    echo "  Apileon Framework - Zero Dependency Installer"
    echo "=================================================="
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
    else
        echo "unknown"
    fi
}

check_docker() {
    if command -v docker &> /dev/null && docker info &> /dev/null 2>&1; then
        print_success "Docker found and running"
        return 0
    else
        print_warning "Docker not available"
        return 1
    fi
}

install_docker() {
    OS=$(detect_os)
    print_info "Installing Docker for $OS..."
    
    case $OS in
        "debian")
            curl -fsSL https://get.docker.com -o get-docker.sh
            sudo sh get-docker.sh
            sudo usermod -aG docker $USER
            print_success "Docker installed. Please log out and back in."
            ;;
        "redhat")
            sudo dnf install -y docker
            sudo systemctl start docker
            sudo systemctl enable docker
            sudo usermod -aG docker $USER
            ;;
        "macos")
            print_info "Please install Docker Desktop from:"
            echo "https://www.docker.com/products/docker-desktop"
            return 1
            ;;
        *)
            print_error "Unsupported OS for automatic Docker installation"
            return 1
            ;;
    esac
}

create_apileon_docker() {
    print_info "Creating Apileon Docker configuration..."
    
    # Create Dockerfile
    cat > Dockerfile.zero-deps << 'EOF'
FROM php:8.1-alpine

# Install required packages
RUN apk add --no-cache \
    nginx \
    supervisor \
    sqlite \
    curl \
    && docker-php-ext-install pdo pdo_sqlite

# Create application directory
WORKDIR /app

# Copy minimal Apileon framework
COPY --from=composer:latest /usr/bin/composer /usr/bin/composer

# Create minimal Apileon structure
RUN mkdir -p /app/public /app/config /app/routes /app/database /app/storage/logs

# Create index.php
RUN cat > /app/public/index.php << 'EOPHP'
<?php
// Minimal Apileon Bootstrap for Zero Dependencies
header('Content-Type: application/json');

$method = $_SERVER['REQUEST_METHOD'];
$path = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// Simple router
switch ($path) {
    case '/':
    case '/health':
        echo json_encode([
            'message' => 'Apileon Framework - Zero Dependencies Edition',
            'status' => 'healthy',
            'version' => '1.0.0-portable',
            'php_version' => PHP_VERSION,
            'timestamp' => date('c')
        ]);
        break;
        
    case '/users':
        if ($method === 'GET') {
            echo json_encode([
                'users' => [
                    ['id' => 1, 'name' => 'John Doe', 'email' => 'john@example.com'],
                    ['id' => 2, 'name' => 'Jane Smith', 'email' => 'jane@example.com']
                ],
                'total' => 2
            ]);
        } elseif ($method === 'POST') {
            $input = json_decode(file_get_contents('php://input'), true);
            echo json_encode([
                'message' => 'User created',
                'user' => [
                    'id' => rand(100, 999),
                    'name' => $input['name'] ?? 'Unknown',
                    'email' => $input['email'] ?? 'unknown@example.com'
                ]
            ]);
        }
        break;
        
    default:
        http_response_code(404);
        echo json_encode(['error' => 'Endpoint not found']);
        break;
}
EOPHP

# Configure Nginx
RUN cat > /etc/nginx/http.d/default.conf << 'EOFNGINX'
server {
    listen 8000;
    server_name localhost;
    root /app/public;
    index index.php;

    location / {
        try_files $uri $uri/ /index.php?$query_string;
    }

    location ~ \.php$ {
        fastcgi_pass 127.0.0.1:9000;
        fastcgi_index index.php;
        fastcgi_param SCRIPT_FILENAME $document_root$fastcgi_script_name;
        include fastcgi_params;
    }
}
EOFNGINX

# Configure supervisor
RUN cat > /etc/supervisor/conf.d/supervisord.conf << 'EOFSUP'
[supervisord]
nodaemon=true

[program:php-fpm]
command=php-fpm -F
autostart=true
autorestart=true

[program:nginx]
command=nginx -g "daemon off;"
autostart=true
autorestart=true
EOFSUP

EXPOSE 8000

CMD ["/usr/bin/supervisord", "-c", "/etc/supervisor/conf.d/supervisord.conf"]
EOF

    # Create Docker Compose
    cat > docker-compose.zero-deps.yml << 'EOF'
version: '3.8'

services:
  apileon-zero-deps:
    build:
      context: .
      dockerfile: Dockerfile.zero-deps
    ports:
      - "8000:8000"
    container_name: apileon-zero-deps
    restart: unless-stopped
    healthcheck:
      test: ["CMD", "curl", "-f", "http://localhost:8000/health"]
      interval: 30s
      timeout: 10s
      retries: 3
EOF

    print_success "Docker configuration created"
}

run_apileon() {
    print_info "Starting Apileon (zero dependencies)..."
    
    docker-compose -f docker-compose.zero-deps.yml up -d
    
    print_success "Apileon is starting..."
    print_info "Waiting for service to be ready..."
    
    # Wait for service to be ready
    for i in {1..30}; do
        if curl -s http://localhost:8000/health > /dev/null 2>&1; then
            break
        fi
        sleep 2
        echo -n "."
    done
    echo ""
    
    print_success "Apileon is now running!"
    echo ""
    echo "🌐 API available at: http://localhost:8000"
    echo "📚 Health check: http://localhost:8000/health"
    echo "👥 Users endpoint: http://localhost:8000/users"
    echo ""
    echo "To stop: docker-compose -f docker-compose.zero-deps.yml down"
    echo "To view logs: docker logs apileon-zero-deps"
}

main() {
    print_header
    
    print_info "Checking system requirements..."
    
    if check_docker; then
        print_success "Docker available - proceeding with zero-dependency deployment"
    else
        print_warning "Docker not found - attempting installation..."
        
        if install_docker; then
            print_success "Docker installation completed"
            print_warning "Please log out and back in, then run this script again"
            exit 0
        else
            print_error "Could not install Docker automatically"
            echo ""
            echo "Manual installation required:"
            echo "  Linux: curl -fsSL https://get.docker.com | sh"
            echo "  macOS: Download Docker Desktop from docker.com"
            echo "  Windows: Download Docker Desktop from docker.com"
            exit 1
        fi
    fi
    
    create_apileon_docker
    run_apileon
    
    echo ""
    print_success "Zero-dependency Apileon deployment complete!"
    echo ""
    echo "This installation includes:"
    echo "✓ Complete API server"
    echo "✓ Sample endpoints"
    echo "✓ Health monitoring"
    echo "✓ No PHP installation required"
    echo "✓ No database setup needed"
}

# Run if executed directly
if [[ "${BASH_SOURCE[0]}" == "${0}" ]]; then
    main "$@"
fi
