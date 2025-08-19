<?php

namespace Apileon\Http;

abstract class Middleware
{
    abstract public function handle(Request $request, callable $next): Response;

    protected function response(): ResponseFactory
    {
        return new ResponseFactory();
    }
}

class ResponseFactory
{
    public function json(array $data, int $statusCode = 200, array $headers = []): Response
    {
        return Response::json($data, $statusCode, $headers);
    }

    public function text(string $content, int $statusCode = 200, array $headers = []): Response
    {
        return Response::text($content, $statusCode, $headers);
    }

    public function html(string $content, int $statusCode = 200, array $headers = []): Response
    {
        return Response::html($content, $statusCode, $headers);
    }
}
