<?php

use PHPUnit\Framework\TestCase;
use Apileon\Http\Request;
use Apileon\Http\Response;

class RequestTest extends TestCase
{
    public function setUp(): void
    {
        // Reset globals before each test
        $_SERVER = [];
        $_GET = [];
        $_POST = [];
    }

    public function testRequestMethodDetection()
    {
        $_SERVER['REQUEST_METHOD'] = 'POST';
        
        $request = new Request();
        
        $this->assertEquals('POST', $request->method());
    }

    public function testRequestUriParsing()
    {
        $_SERVER['REQUEST_URI'] = '/api/users/123?page=1';
        
        $request = new Request();
        
        $this->assertEquals('/api/users/123', $request->uri());
    }

    public function testQueryParameters()
    {
        $_GET = ['page' => '1', 'limit' => '10'];
        
        $request = new Request();
        
        $this->assertEquals('1', $request->query('page'));
        $this->assertEquals('10', $request->query('limit'));
        $this->assertEquals(['page' => '1', 'limit' => '10'], $request->query());
    }

    public function testHeaderParsing()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer token123';
        $_SERVER['HTTP_CONTENT_TYPE'] = 'application/json';
        
        $request = new Request();
        
        $this->assertEquals('Bearer token123', $request->header('Authorization'));
        $this->assertEquals('application/json', $request->header('Content-Type'));
    }

    public function testBearerTokenExtraction()
    {
        $_SERVER['HTTP_AUTHORIZATION'] = 'Bearer abc123def456';
        
        $request = new Request();
        
        $this->assertEquals('abc123def456', $request->bearerToken());
    }
}
