<?php

namespace Apileon\Http;

class Request
{
    private array $headers;
    private array $query;
    private array $body;
    private string $method;
    private string $uri;
    private array $params = [];

    public function __construct()
    {
        $this->headers = $this->parseHeaders();
        $this->query = $_GET;
        $this->body = $this->parseBody();
        $this->method = $_SERVER['REQUEST_METHOD'] ?? 'GET';
        $this->uri = parse_url($_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH);
    }

    private function parseHeaders(): array
    {
        $headers = [];
        foreach ($_SERVER as $key => $value) {
            if (strpos($key, 'HTTP_') === 0) {
                $header = str_replace('_', '-', substr($key, 5));
                $headers[ucwords(strtolower($header), '-')] = $value;
            }
        }
        
        // Add content type if available
        if (isset($_SERVER['CONTENT_TYPE'])) {
            $headers['Content-Type'] = $_SERVER['CONTENT_TYPE'];
        }
        
        return $headers;
    }

    private function parseBody(): array
    {
        $contentType = $this->header('Content-Type') ?? '';
        
        if (strpos($contentType, 'application/json') !== false) {
            $input = file_get_contents('php://input');
            return json_decode($input, true) ?? [];
        }
        
        return $_POST;
    }

    public function method(): string
    {
        return $this->method;
    }

    public function uri(): string
    {
        return $this->uri;
    }

    public function header(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function headers(): array
    {
        return $this->headers;
    }

    public function query(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->query;
        }
        
        return $this->query[$key] ?? $default;
    }

    public function input(string $key = null, $default = null)
    {
        if ($key === null) {
            return $this->body;
        }
        
        return $this->body[$key] ?? $default;
    }

    public function all(): array
    {
        return array_merge($this->query, $this->body);
    }

    public function setParams(array $params): void
    {
        $this->params = $params;
    }

    public function param(string $key, $default = null)
    {
        return $this->params[$key] ?? $default;
    }

    public function params(): array
    {
        return $this->params;
    }

    public function isJson(): bool
    {
        $contentType = $this->header('Content-Type') ?? '';
        return strpos($contentType, 'application/json') !== false;
    }

    public function bearerToken(): ?string
    {
        $authorization = $this->header('Authorization');
        if ($authorization && preg_match('/Bearer\s+(.*)$/i', $authorization, $matches)) {
            return $matches[1];
        }
        return null;
    }
}
