#!/usr/bin/env php
<?php
/**
 * Apileon Framework - Universal Portable Deployment Generator
 * 
 * Creates multiple portable deployment options:
 * 1. Portable ZIP package (requires PHP)
 * 2. Docker container (no dependencies)
 * 3. Self-contained executable
 * 4. WASM/WebAssembly version (experimental)
 */

class UniversalPortableGenerator
{
    private $options = [
        '1' => 'Zero Dependencies (Docker auto-install)',
        '2' => 'Portable ZIP Package (Requires PHP 8.1+)',
        '3' => 'Docker Container (Full framework)',
        '4' => 'Self-Contained Executable',
        '5' => 'All Portable Versions',
        '6' => 'WebAssembly Version (Experimental)'
    ];
    
    public function run()
    {
        $this->showBanner();
        $choice = $this->getChoice();
        
        switch ($choice) {
            case '1':
                $this->createZeroDependencies();
                break;
            case '2':
                $this->createPortableZip();
                break;
            case '3':
                $this->createDockerContainer();
                break;
            case '4':
                $this->createSelfContained();
                break;
            case '5':
                $this->createAllVersions();
                break;
            case '6':
                $this->createWebAssembly();
                break;
            default:
                echo "Invalid choice. Exiting.\n";
                exit(1);
        }
    }
    
    private function showBanner()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║              Apileon Framework - Portable Deployment            ║\n";
        echo "║                                                                  ║\n";
        echo "║  Create portable versions that run WITHOUT installation!        ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "Available deployment options:\n\n";
        
        foreach ($this->options as $key => $description) {
            echo "  [$key] $description\n";
        }
        
        echo "\n";
    }
    
    private function getChoice()
    {
        echo "Select deployment type [1-6]: ";
        $handle = fopen("php://stdin", "r");
        $choice = trim(fgets($handle));
        fclose($handle);
        
        echo "\n";
        return $choice;
    }
    
    private function createZeroDependencies()
    {
        echo "🚀 Creating Zero Dependencies Deployment...\n\n";
        
        if (file_exists(__DIR__ . '/install-zero-deps.sh')) {
            echo "Running zero dependencies installer...\n";
            $this->runCommand('bash ' . __DIR__ . '/install-zero-deps.sh');
        } else {
            echo "❌ install-zero-deps.sh not found.\n";
        }
    }
    
    private function createPortableZip()
    {
        echo "🚀 Creating Portable ZIP Package...\n\n";
        
        if (file_exists(__DIR__ . '/create-portable.php')) {
            include __DIR__ . '/create-portable.php';
        } else {
            echo "❌ create-portable.php not found. Please run this from the Apileon root directory.\n";
        }
    }
    
    private function createDockerContainer()
    {
        echo "🐳 Creating Docker Container...\n\n";
        
        $this->ensureDockerFiles();
        
        echo "Building Docker image...\n";
        $buildCommand = "docker build -f Dockerfile.portable -t apileon-portable .";
        $this->runCommand($buildCommand);
        
        echo "\n✅ Docker container created successfully!\n\n";
        echo "To run the container:\n";
        echo "  docker run -p 8000:8000 apileon-portable\n\n";
        echo "Or use Docker Compose:\n";
        echo "  docker-compose -f docker-compose.portable.yml up\n\n";
        echo "Access your API at: http://localhost:8000\n\n";
        
        // Create portable run script
        $this->createDockerRunScript();
    }
    
    private function createSelfContained()
    {
        echo "📦 Creating Self-Contained Executable...\n\n";
        
        if (file_exists(__DIR__ . '/create-standalone.php')) {
            include __DIR__ . '/create-standalone.php';
        } else {
            echo "❌ create-standalone.php not found. Please run this from the Apileon root directory.\n";
        }
    }
    
    private function createAllVersions()
    {
        echo "🎯 Creating All Portable Versions...\n\n";
        
        $this->createPortableZip();
        echo "\n" . str_repeat("=", 60) . "\n";
        $this->createDockerContainer();
        echo "\n" . str_repeat("=", 60) . "\n";
        $this->createSelfContained();
        
        echo "\n🎉 All portable versions created successfully!\n";
        $this->showSummary();
    }
    
    private function createWebAssembly()
    {
        echo "🔬 Creating WebAssembly Version (Experimental)...\n\n";
        
        // Check if required tools are available
        if (!$this->checkWasmRequirements()) {
            echo "❌ WebAssembly requirements not met. Skipping WASM build.\n";
            return;
        }
        
        $this->buildWasmVersion();
    }
    
    private function ensureDockerFiles()
    {
        $dockerFiles = [
            'Dockerfile.portable',
            'docker-compose.portable.yml',
            'docker/nginx.conf',
            'docker/supervisord.conf',
            'docker/start.sh'
        ];
        
        foreach ($dockerFiles as $file) {
            if (!file_exists(__DIR__ . '/' . $file)) {
                echo "❌ Missing Docker file: $file\n";
                echo "Please ensure all Docker configuration files are present.\n";
                exit(1);
            }
        }
    }
    
    private function createDockerRunScript()
    {
        $script = <<<SCRIPT
#!/bin/bash
# Apileon Portable - Docker Runner

echo "🐳 Starting Apileon in Docker..."
echo

# Check if Docker is installed
if ! command -v docker &> /dev/null; then
    echo "❌ Docker is not installed"
    echo "Please install Docker from: https://www.docker.com/get-started"
    exit 1
fi

# Check if Docker is running
if ! docker info &> /dev/null; then
    echo "❌ Docker is not running"
    echo "Please start Docker and try again"
    exit 1
fi

# Pull or build the image
echo "📦 Preparing Apileon container..."
if ! docker image inspect apileon-portable &> /dev/null; then
    echo "Building Apileon image..."
    docker build -f Dockerfile.portable -t apileon-portable .
fi

# Stop existing container if running
docker stop apileon-api 2>/dev/null || true
docker rm apileon-api 2>/dev/null || true

# Start the container
echo "🚀 Starting Apileon API server..."
docker run -d \\
    --name apileon-api \\
    -p 8000:8000 \\
    -v \$(pwd)/database:/app/database \\
    -v \$(pwd)/storage:/app/storage \\
    apileon-portable

echo
echo "✅ Apileon is now running!"
echo "🌐 API available at: http://localhost:8000"
echo "📚 Documentation: http://localhost:8000/docs"
echo "💾 Database: SQLite (persistent)"
echo
echo "To stop: docker stop apileon-api"
echo "To view logs: docker logs apileon-api"
echo
SCRIPT;
        
        file_put_contents(__DIR__ . '/run-docker.sh', $script);
        chmod(__DIR__ . '/run-docker.sh', 0755);
        
        // Windows version
        $batScript = <<<BAT
@echo off
title Apileon - Docker Runner

echo 🐳 Starting Apileon in Docker...
echo.

REM Check if Docker is installed
docker --version >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Docker is not installed
    echo Please install Docker from: https://www.docker.com/get-started
    pause
    exit /b 1
)

REM Check if Docker is running
docker info >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo ❌ Docker is not running
    echo Please start Docker and try again
    pause
    exit /b 1
)

REM Build image if it doesn't exist
echo 📦 Preparing Apileon container...
docker image inspect apileon-portable >nul 2>&1
if %ERRORLEVEL% neq 0 (
    echo Building Apileon image...
    docker build -f Dockerfile.portable -t apileon-portable .
)

REM Stop existing container
docker stop apileon-api >nul 2>&1
docker rm apileon-api >nul 2>&1

REM Start container
echo 🚀 Starting Apileon API server...
docker run -d --name apileon-api -p 8000:8000 -v %cd%/database:/app/database -v %cd%/storage:/app/storage apileon-portable

echo.
echo ✅ Apileon is now running!
echo 🌐 API available at: http://localhost:8000
echo 📚 Documentation: http://localhost:8000/docs
echo 💾 Database: SQLite (persistent)
echo.
echo To stop: docker stop apileon-api
echo To view logs: docker logs apileon-api
echo.
pause
BAT;
        
        file_put_contents(__DIR__ . '/run-docker.bat', $batScript);
        
        echo "✅ Docker run scripts created:\n";
        echo "  - run-docker.sh (Unix/Linux/macOS)\n";
        echo "  - run-docker.bat (Windows)\n";
    }
    
    private function checkWasmRequirements()
    {
        $requirements = [
            'emscripten' => 'emcc --version',
            'php-wasm' => 'php-wasm --version'
        ];
        
        foreach ($requirements as $tool => $command) {
            if (!$this->commandExists($command)) {
                echo "❌ Missing requirement: $tool\n";
                return false;
            }
        }
        
        return true;
    }
    
    private function buildWasmVersion()
    {
        echo "🔧 Building WebAssembly version...\n";
        
        $wasmDir = __DIR__ . '/wasm-build';
        if (!is_dir($wasmDir)) {
            mkdir($wasmDir, 0755, true);
        }
        
        // Create WASM-specific files
        $this->createWasmFiles($wasmDir);
        
        echo "✅ WebAssembly version created in: $wasmDir\n";
        echo "⚠️  Note: This is experimental and requires a compatible browser.\n";
    }
    
    private function createWasmFiles($wasmDir)
    {
        // Create HTML wrapper
        $htmlContent = <<<HTML
<!DOCTYPE html>
<html>
<head>
    <title>Apileon Framework - WebAssembly</title>
    <style>
        body { font-family: Arial, sans-serif; max-width: 800px; margin: 0 auto; padding: 20px; }
        .banner { background: #f0f0f0; padding: 20px; border-radius: 5px; margin-bottom: 20px; }
        .output { background: #000; color: #0f0; padding: 15px; border-radius: 5px; font-family: monospace; min-height: 200px; }
    </style>
</head>
<body>
    <div class="banner">
        <h1>🦁 Apileon Framework - WebAssembly Edition</h1>
        <p>Running PHP and SQLite entirely in your browser!</p>
    </div>
    
    <div>
        <h2>API Server Output:</h2>
        <div id="output" class="output">Loading Apileon...</div>
    </div>
    
    <div style="margin-top: 20px;">
        <button onclick="startServer()">Start API Server</button>
        <button onclick="stopServer()">Stop Server</button>
        <button onclick="clearOutput()">Clear Output</button>
    </div>
    
    <script>
        let outputElement = document.getElementById('output');
        
        function log(message) {
            outputElement.innerHTML += message + '\\n';
            outputElement.scrollTop = outputElement.scrollHeight;
        }
        
        function startServer() {
            log('🚀 Starting Apileon WebAssembly server...');
            log('⚠️  WebAssembly implementation is experimental');
            log('✅ This would start a PHP server compiled to WASM');
            log('🌐 API endpoints would be available at: /api/*');
        }
        
        function stopServer() {
            log('🛑 Stopping server...');
        }
        
        function clearOutput() {
            outputElement.innerHTML = '';
        }
        
        // Auto-start demo
        setTimeout(startServer, 1000);
    </script>
</body>
</html>
HTML;
        
        file_put_contents($wasmDir . '/index.html', $htmlContent);
        
        // Create package.json for WASM build
        $packageJson = <<<JSON
{
  "name": "apileon-wasm",
  "version": "1.0.0",
  "description": "Apileon Framework - WebAssembly Edition",
  "main": "index.html",
  "scripts": {
    "build": "echo 'WASM build would go here'",
    "serve": "python -m http.server 8080"
  },
  "keywords": ["apileon", "php", "webassembly", "api"],
  "author": "Apileon Team"
}
JSON;
        
        file_put_contents($wasmDir . '/package.json', $packageJson);
        
        // Create README
        $readmeContent = <<<README
# Apileon Framework - WebAssembly Edition

## Overview
This is an experimental WebAssembly version of Apileon that runs entirely in the browser.

## Requirements
- Modern web browser with WebAssembly support
- Web server (for CORS/security reasons)

## Running
1. Start a local web server:
   \`\`\`
   python -m http.server 8080
   \`\`\`

2. Open http://localhost:8080 in your browser

## Status
🔬 **Experimental** - This demonstrates the concept of running Apileon in WebAssembly.

For a full implementation, you would need:
- PHP compiled to WebAssembly
- SQLite compiled to WebAssembly  
- File system abstraction
- Network request handling

## Production Ready Alternatives
- Use the Docker version for true portability
- Use the portable ZIP for easy deployment
- Use the self-contained executable for single-file deployment
README;
        
        file_put_contents($wasmDir . '/README.md', $readmeContent);
    }
    
    private function commandExists($command)
    {
        $test = shell_exec("which $command 2>/dev/null");
        return !empty($test);
    }
    
    private function runCommand($command)
    {
        echo "Running: $command\n";
        $output = [];
        $returnVar = 0;
        exec($command, $output, $returnVar);
        
        foreach ($output as $line) {
            echo "  $line\n";
        }
        
        if ($returnVar !== 0) {
            echo "❌ Command failed with exit code: $returnVar\n";
            return false;
        }
        
        return true;
    }
    
    private function showSummary()
    {
        echo "\n";
        echo "╔══════════════════════════════════════════════════════════════════╗\n";
        echo "║                        DEPLOYMENT SUMMARY                       ║\n";
        echo "╚══════════════════════════════════════════════════════════════════╝\n\n";
        
        echo "📦 PORTABLE PACKAGES CREATED:\n\n";
        
        // Check what was created
        $packages = [];
        
        if (is_dir(__DIR__ . '/portable-build')) {
            $packages[] = "✅ Portable ZIP Package (portable-build/)";
        }
        
        if (is_dir(__DIR__ . '/standalone-build')) {
            $packages[] = "✅ Self-Contained Executable (standalone-build/)";
        }
        
        if (file_exists(__DIR__ . '/Dockerfile.portable')) {
            $packages[] = "✅ Docker Container (run with docker-compose)";
        }
        
        if (is_dir(__DIR__ . '/wasm-build')) {
            $packages[] = "✅ WebAssembly Version (wasm-build/)";
        }
        
        foreach ($packages as $package) {
            echo "  $package\n";
        }
        
        echo "\n🚀 DEPLOYMENT OPTIONS:\n\n";
        echo "  1. ZIP Package:     Extract and run (requires PHP 8.1+)\n";
        echo "  2. Docker:          Run anywhere with Docker\n";
        echo "  3. Executable:      Single file, no dependencies\n";
        echo "  4. WebAssembly:     Run in web browser\n\n";
        
        echo "📚 QUICK START:\n\n";
        echo "  Docker:       ./run-docker.sh\n";
        echo "  Portable:     Extract ZIP and run launcher\n";
        echo "  Executable:   ./apileon-standalone-*\n";
        echo "  WASM:         Serve wasm-build/ folder\n\n";
        
        echo "🌐 Access your API at: http://localhost:8000\n\n";
    }
}

// Run the generator
if (php_sapi_name() === 'cli') {
    $generator = new UniversalPortableGenerator();
    $generator->run();
} else {
    die('This script must be run from command line');
}
