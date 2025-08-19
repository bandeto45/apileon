<?php

namespace Apileon\Support;

class PerformanceMonitor
{
    private static array $metrics = [];
    private static array $timers = [];
    private static float $requestStartTime;

    public static function startRequest(): void
    {
        self::$requestStartTime = microtime(true);
        self::$metrics = [
            'request_start' => self::$requestStartTime,
            'memory_start' => memory_get_usage(true),
            'memory_peak' => 0,
            'database_queries' => 0,
            'query_time' => 0,
            'cache_hits' => 0,
            'cache_misses' => 0,
        ];
    }

    public static function endRequest(): array
    {
        $endTime = microtime(true);
        $memoryEnd = memory_get_usage(true);
        $memoryPeak = memory_get_peak_usage(true);

        self::$metrics['request_time'] = ($endTime - self::$requestStartTime) * 1000; // ms
        self::$metrics['memory_end'] = $memoryEnd;
        self::$metrics['memory_peak'] = $memoryPeak;
        self::$metrics['memory_used'] = $memoryEnd - self::$metrics['memory_start'];

        return self::$metrics;
    }

    public static function startTimer(string $name): void
    {
        self::$timers[$name] = microtime(true);
    }

    public static function endTimer(string $name): float
    {
        if (!isset(self::$timers[$name])) {
            return 0;
        }

        $duration = (microtime(true) - self::$timers[$name]) * 1000; // ms
        unset(self::$timers[$name]);
        
        return $duration;
    }

    public static function incrementCounter(string $metric, int $amount = 1): void
    {
        if (!isset(self::$metrics[$metric])) {
            self::$metrics[$metric] = 0;
        }
        
        self::$metrics[$metric] += $amount;
    }

    public static function recordQueryTime(float $time): void
    {
        self::incrementCounter('database_queries');
        self::$metrics['query_time'] += $time;
    }

    public static function getMetrics(): array
    {
        return self::$metrics;
    }

    public static function getFormattedMetrics(): array
    {
        $metrics = self::getMetrics();
        
        return [
            'performance' => [
                'request_time_ms' => round($metrics['request_time'] ?? 0, 2),
                'memory_usage_mb' => round(($metrics['memory_used'] ?? 0) / 1024 / 1024, 2),
                'memory_peak_mb' => round(($metrics['memory_peak'] ?? 0) / 1024 / 1024, 2),
                'database_queries' => $metrics['database_queries'] ?? 0,
                'query_time_ms' => round($metrics['query_time'] ?? 0, 2),
                'cache_hit_ratio' => self::getCacheHitRatio(),
            ]
        ];
    }

    private static function getCacheHitRatio(): string
    {
        $hits = self::$metrics['cache_hits'] ?? 0;
        $misses = self::$metrics['cache_misses'] ?? 0;
        $total = $hits + $misses;
        
        if ($total === 0) {
            return 'N/A';
        }
        
        return round(($hits / $total) * 100, 1) . '%';
    }
}
