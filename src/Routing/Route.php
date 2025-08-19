<?php

namespace Apileon\Routing;

use Apileon\Http\Request;
use Apileon\Http\Response;

class Route
{
    private static Router $router;

    public static function setRouter(Router $router): void
    {
        self::$router = $router;
    }

    public static function get(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('GET', $uri, $handler);
    }

    public static function post(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('POST', $uri, $handler);
    }

    public static function put(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('PUT', $uri, $handler);
    }

    public static function patch(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('PATCH', $uri, $handler);
    }

    public static function delete(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('DELETE', $uri, $handler);
    }

    public static function options(string $uri, callable|string $handler): RouteRegistrar
    {
        return self::$router->addRoute('OPTIONS', $uri, $handler);
    }

    public static function any(string $uri, callable|string $handler): RouteRegistrar
    {
        $methods = ['GET', 'POST', 'PUT', 'PATCH', 'DELETE', 'OPTIONS'];
        $registrar = null;
        
        foreach ($methods as $method) {
            $registrar = self::$router->addRoute($method, $uri, $handler);
        }
        
        return $registrar;
    }

    public static function group(array $attributes, callable $callback): void
    {
        self::$router->group($attributes, $callback);
    }
}
