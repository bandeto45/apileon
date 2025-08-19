<?php

namespace Apileon\Http\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class CorsMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        // Handle preflight requests
        if ($request->method() === 'OPTIONS') {
            return $this->response()->json([], 200, [
                'Access-Control-Allow-Origin' => '*',
                'Access-Control-Allow-Methods' => 'GET, POST, PUT, PATCH, DELETE, OPTIONS',
                'Access-Control-Allow-Headers' => 'Content-Type, Authorization, X-Requested-With',
                'Access-Control-Max-Age' => '86400',
            ]);
        }

        $response = $next($request);

        // Add CORS headers to the response
        return $response
            ->header('Access-Control-Allow-Origin', '*')
            ->header('Access-Control-Allow-Methods', 'GET, POST, PUT, PATCH, DELETE, OPTIONS')
            ->header('Access-Control-Allow-Headers', 'Content-Type, Authorization, X-Requested-With');
    }
}
