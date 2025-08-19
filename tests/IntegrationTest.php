<?php

require_once __DIR__ . '/../autoload.php';

use PHPUnit\Framework\TestCase;
use Apileon\Foundation\Application;
use Apileon\Http\Request;
use Apileon\Cache\CacheManager;
use Apileon\Support\PerformanceMonitor;
use App\Models\User;

class IntegrationTest extends TestCase
{
    private Application $app;

    protected function setUp(): void
    {
        $this->app = new Application(__DIR__ . '/..');
        
        // Configure test environment
        putenv('APP_ENV=testing');
        putenv('APP_DEBUG=true');
        
        // Configure in-memory cache for testing
        CacheManager::configure([
            'driver' => 'array',
            'ttl' => 3600
        ]);
    }

    public function testUserControllerIndex()
    {
        PerformanceMonitor::startRequest();
        
        // Mock request
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/api/users';
        $_GET = ['page' => '1', 'per_page' => '10'];
        
        $request = new Request();
        $response = $this->app->getRouter()->dispatch($request);
        
        $this->assertEquals(200, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('success', $content);
        $this->assertArrayHasKey('data', $content);
        $this->assertArrayHasKey('meta', $content);
        
        $metrics = PerformanceMonitor::endRequest();
        $this->assertArrayHasKey('request_time', $metrics);
        $this->assertLessThan(1000, $metrics['request_time']); // Should be under 1 second
    }

    public function testCacheIntegration()
    {
        $cache = CacheManager::getInstance();
        
        // Test basic cache operations
        $this->assertTrue($cache->set('test_key', 'test_value', 60));
        $this->assertEquals('test_value', $cache->get('test_key'));
        $this->assertTrue($cache->has('test_key'));
        $this->assertTrue($cache->delete('test_key'));
        $this->assertNull($cache->get('test_key'));
    }

    public function testCacheRemember()
    {
        $callCount = 0;
        
        $result1 = cache_remember('expensive_operation', function() use (&$callCount) {
            $callCount++;
            return 'computed_value';
        }, 60);
        
        $result2 = cache_remember('expensive_operation', function() use (&$callCount) {
            $callCount++;
            return 'computed_value';
        }, 60);
        
        $this->assertEquals('computed_value', $result1);
        $this->assertEquals('computed_value', $result2);
        $this->assertEquals(1, $callCount); // Should only be called once due to caching
    }

    public function testPerformanceMonitoring()
    {
        PerformanceMonitor::startRequest();
        
        // Simulate some work
        PerformanceMonitor::startTimer('test_operation');
        usleep(10000); // 10ms
        $duration = PerformanceMonitor::endTimer('test_operation');
        
        PerformanceMonitor::incrementCounter('test_counter', 5);
        PerformanceMonitor::recordQueryTime(15.5);
        
        $metrics = PerformanceMonitor::endRequest();
        
        $this->assertArrayHasKey('request_time', $metrics);
        $this->assertArrayHasKey('memory_used', $metrics);
        $this->assertEquals(1, $metrics['database_queries']);
        $this->assertEquals(15.5, $metrics['query_time']);
        
        $formatted = PerformanceMonitor::getFormattedMetrics();
        $this->assertArrayHasKey('performance', $formatted);
        $this->assertArrayHasKey('request_time_ms', $formatted['performance']);
    }

    public function testValidationIntegration()
    {
        $validData = [
            'name' => 'John Doe',
            'email' => 'john@example.com',
            'password' => 'securepassword123'
        ];
        
        $validated = User::validateForCreation($validData);
        $this->assertEquals($validData, $validated);
        
        // Test validation failure
        $invalidData = [
            'name' => 'J', // Too short
            'email' => 'invalid-email',
            'password' => '123' // Too short
        ];
        
        $this->expectException(\Apileon\Validation\ValidationException::class);
        User::validateForCreation($invalidData);
    }

    public function testMiddlewareIntegration()
    {
        // Test CORS middleware
        $_SERVER['REQUEST_METHOD'] = 'OPTIONS';
        $_SERVER['REQUEST_URI'] = '/api/users';
        
        $request = new Request();
        $response = $this->app->getRouter()->dispatch($request);
        
        $headers = $response->getHeaders();
        $this->assertArrayHasKey('Access-Control-Allow-Origin', $headers);
        $this->assertEquals('*', $headers['Access-Control-Allow-Origin']);
    }

    public function testDatabaseIntegration()
    {
        // Test query builder
        $queryBuilder = new \Apileon\Database\QueryBuilder();
        
        $sql = $queryBuilder
            ->table('users')
            ->select(['id', 'name', 'email'])
            ->where('status', 'active')
            ->where('created_at', '>', '2024-01-01')
            ->orderBy('created_at', 'DESC')
            ->limit(10)
            ->toSql();
        
        $this->assertStringContainsString('SELECT', $sql);
        $this->assertStringContainsString('WHERE', $sql);
        $this->assertStringContainsString('ORDER BY', $sql);
        $this->assertStringContainsString('LIMIT', $sql);
    }

    public function testErrorHandling()
    {
        // Test invalid route
        $_SERVER['REQUEST_METHOD'] = 'GET';
        $_SERVER['REQUEST_URI'] = '/nonexistent/route';
        
        $request = new Request();
        $response = $this->app->getRouter()->dispatch($request);
        
        $this->assertEquals(404, $response->getStatusCode());
        
        $content = json_decode($response->getContent(), true);
        $this->assertArrayHasKey('error', $content);
    }

    protected function tearDown(): void
    {
        // Clean up
        CacheManager::getInstance()->clear();
    }
}
