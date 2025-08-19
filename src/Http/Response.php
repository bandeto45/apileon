<?php

namespace Apileon\Http;

class Response
{
    private array $headers = [];
    private mixed $content;
    private int $statusCode = 200;

    public function __construct(mixed $content = '', int $statusCode = 200, array $headers = [])
    {
        $this->content = $content;
        $this->statusCode = $statusCode;
        $this->headers = $headers;
    }

    public static function json(array $data, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'application/json';
        return new self(json_encode($data), $statusCode, $headers);
    }

    public static function text(string $content, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/plain';
        return new self($content, $statusCode, $headers);
    }

    public static function html(string $content, int $statusCode = 200, array $headers = []): self
    {
        $headers['Content-Type'] = 'text/html';
        return new self($content, $statusCode, $headers);
    }

    public function header(string $name, string $value): self
    {
        $this->headers[$name] = $value;
        return $this;
    }

    public function status(int $code): self
    {
        $this->statusCode = $code;
        return $this;
    }

    public function send(): void
    {
        http_response_code($this->statusCode);
        
        foreach ($this->headers as $name => $value) {
            header("$name: $value");
        }
        
        echo $this->content;
    }

    public function getContent(): mixed
    {
        return $this->content;
    }

    public function getStatusCode(): int
    {
        return $this->statusCode;
    }

    public function getHeaders(): array
    {
        return $this->headers;
    }
}
