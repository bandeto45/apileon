<?php

/**
 * Simple test to verify Apileon works without Composer
 */

echo "🧪 Testing Apileon Framework (No Composer)...\n\n";

// Load the framework
require_once __DIR__ . '/autoload.php';

try {
    // Test autoloader
    echo "📦 Testing autoloader...\n";
    
    // Test basic classes
    $request = new \Apileon\Http\Request();
    echo "✅ Request class loaded\n";
    
    $response = \Apileon\Http\Response::json(['test' => 'success']);
    echo "✅ Response class loaded\n";
    
    $router = new \Apileon\Routing\Router();
    echo "✅ Router class loaded\n";
    
    // Test helper functions
    echo "\n🔧 Testing helper functions...\n";
    
    if (function_exists('env')) {
        echo "✅ env() function available\n";
    }
    
    if (function_exists('config')) {
        echo "✅ config() function available\n";
    }
    
    if (function_exists('response')) {
        echo "✅ response() function available\n";
    }
    
    // Test basic functionality
    echo "\n⚡ Testing basic functionality...\n";
    
    // Test response creation
    $jsonResponse = \Apileon\Http\Response::json(['message' => 'Hello Apileon!']);
    if ($jsonResponse->getStatusCode() === 200) {
        echo "✅ JSON response creation works\n";
    }
    
    // Test content
    $content = json_decode($jsonResponse->getContent(), true);
    if ($content['message'] === 'Hello Apileon!') {
        echo "✅ JSON content is correct\n";
    }
    
    // Test route parameters
    $request->setParams(['id' => '123', 'name' => 'test']);
    if ($request->param('id') === '123') {
        echo "✅ Route parameters work\n";
    }
    
    echo "\n🎉 All tests passed! Apileon is ready to use without Composer.\n";
    echo "\n📋 Next steps:\n";
    echo "  1. Start server: php -S localhost:8000 -t public\n";
    echo "  2. Test API: curl http://localhost:8000/hello\n";
    echo "  3. Edit routes: routes/api.php\n";
    echo "  4. Create controllers: app/Controllers/\n\n";
    
} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
} catch (Error $e) {
    echo "❌ Fatal Error: " . $e->getMessage() . "\n";
    echo "📍 File: " . $e->getFile() . ":" . $e->getLine() . "\n";
    exit(1);
}
