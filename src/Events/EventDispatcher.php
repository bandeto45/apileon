<?php

namespace Apileon\Events;

class EventDispatcher
{
    private static array $listeners = [];
    private static array $wildcardListeners = [];

    public static function listen(string $event, callable $listener, int $priority = 0): void
    {
        if (!isset(self::$listeners[$event])) {
            self::$listeners[$event] = [];
        }

        self::$listeners[$event][] = [
            'callback' => $listener,
            'priority' => $priority
        ];

        // Sort by priority (higher priority first)
        usort(self::$listeners[$event], function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    public static function listenWildcard(string $pattern, callable $listener, int $priority = 0): void
    {
        self::$wildcardListeners[] = [
            'pattern' => $pattern,
            'callback' => $listener,
            'priority' => $priority
        ];

        // Sort by priority
        usort(self::$wildcardListeners, function($a, $b) {
            return $b['priority'] <=> $a['priority'];
        });
    }

    public static function dispatch(string $event, array $data = []): array
    {
        $results = [];

        // Handle direct listeners
        if (isset(self::$listeners[$event])) {
            foreach (self::$listeners[$event] as $listener) {
                $result = call_user_func($listener['callback'], $event, $data);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }

        // Handle wildcard listeners
        foreach (self::$wildcardListeners as $wildcardListener) {
            if (self::matchesPattern($wildcardListener['pattern'], $event)) {
                $result = call_user_func($wildcardListener['callback'], $event, $data);
                if ($result !== null) {
                    $results[] = $result;
                }
            }
        }

        return $results;
    }

    public static function forget(string $event): void
    {
        unset(self::$listeners[$event]);
    }

    public static function forgetAll(): void
    {
        self::$listeners = [];
        self::$wildcardListeners = [];
    }

    private static function matchesPattern(string $pattern, string $event): bool
    {
        // Convert wildcard pattern to regex
        $regex = str_replace(['*', '.'], ['.*', '\.'], $pattern);
        return preg_match("/^{$regex}$/", $event) === 1;
    }

    public static function getListeners(): array
    {
        return self::$listeners;
    }
}
