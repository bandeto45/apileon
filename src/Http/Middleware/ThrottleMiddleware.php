<?php

namespace Apileon\Http\Middleware;

use Apileon\Http\Middleware;
use Apileon\Http\Request;
use Apileon\Http\Response;

class ThrottleMiddleware extends Middleware
{
    private int $maxAttempts;
    private int $decayMinutes;

    public function __construct(int $maxAttempts = 60, int $decayMinutes = 1)
    {
        $this->maxAttempts = $maxAttempts;
        $this->decayMinutes = $decayMinutes;
    }

    public function handle(Request $request, callable $next): Response
    {
        $key = $this->getKey($request);
        $attempts = $this->getAttempts($key);

        if ($attempts >= $this->maxAttempts) {
            return $this->response()->json([
                'error' => 'Too Many Requests',
                'message' => 'Rate limit exceeded. Try again later.'
            ], 429, [
                'Retry-After' => $this->decayMinutes * 60,
                'X-RateLimit-Limit' => $this->maxAttempts,
                'X-RateLimit-Remaining' => 0,
            ]);
        }

        $this->incrementAttempts($key);
        
        $response = $next($request);
        
        return $response
            ->header('X-RateLimit-Limit', (string) $this->maxAttempts)
            ->header('X-RateLimit-Remaining', (string) max(0, $this->maxAttempts - $attempts - 1));
    }

    private function getKey(Request $request): string
    {
        $ip = $_SERVER['REMOTE_ADDR'] ?? 'unknown';
        return "throttle:{$ip}:" . floor(time() / ($this->decayMinutes * 60));
    }

    private function getAttempts(string $key): int
    {
        // Simple file-based storage for demonstration
        $file = sys_get_temp_dir() . '/' . md5($key) . '.throttle';
        
        if (file_exists($file)) {
            $data = json_decode(file_get_contents($file), true);
            return $data['attempts'] ?? 0;
        }
        
        return 0;
    }

    private function incrementAttempts(string $key): void
    {
        $file = sys_get_temp_dir() . '/' . md5($key) . '.throttle';
        $attempts = $this->getAttempts($key) + 1;
        
        file_put_contents($file, json_encode([
            'attempts' => $attempts,
            'expires_at' => time() + ($this->decayMinutes * 60)
        ]));
    }
}
