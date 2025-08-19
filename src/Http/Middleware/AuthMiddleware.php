<?php

namespace Apileon\Http\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class AuthMiddleware extends Middleware
{
    public function handle(Request $request, callable $next): Response
    {
        $token = $request->bearerToken();
        
        if (!$token) {
            return $this->response()->json([
                'error' => 'Unauthorized',
                'message' => 'Authentication token is required'
            ], 401);
        }

        // Here you would typically validate the token
        // For this example, we'll just check if it's not empty
        if (empty($token)) {
            return $this->response()->json([
                'error' => 'Unauthorized',
                'message' => 'Invalid authentication token'
            ], 401);
        }

        return $next($request);
    }
}
