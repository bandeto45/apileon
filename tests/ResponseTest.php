<?php

use PHPUnit\Framework\TestCase;
use Apileon\Http\Response;

class ResponseTest extends TestCase
{
    public function testJsonResponse()
    {
        $data = ['message' => 'Hello World'];
        $response = Response::json($data);
        
        $this->assertEquals(json_encode($data), $response->getContent());
        $this->assertEquals(200, $response->getStatusCode());
        $this->assertEquals('application/json', $response->getHeaders()['Content-Type']);
    }

    public function testJsonResponseWithCustomStatus()
    {
        $data = ['error' => 'Not found'];
        $response = Response::json($data, 404);
        
        $this->assertEquals(404, $response->getStatusCode());
    }

    public function testTextResponse()
    {
        $response = Response::text('Hello World');
        
        $this->assertEquals('Hello World', $response->getContent());
        $this->assertEquals('text/plain', $response->getHeaders()['Content-Type']);
    }

    public function testResponseChaining()
    {
        $response = Response::json(['message' => 'Success'])
            ->status(201)
            ->header('X-Custom-Header', 'custom-value');
        
        $this->assertEquals(201, $response->getStatusCode());
        $this->assertEquals('custom-value', $response->getHeaders()['X-Custom-Header']);
    }
}
