<?php

namespace Apileon\Routing;

use Apileon\Http\Request;
use Apileon\Http\Response;
use Apileon\Http\Middleware;

class Router
{
    private array $routes = [];
    private array $groupStack = [];
    private array $middlewareRegistry = [];

    public function addRoute(string $method, string $uri, callable|string $handler): RouteRegistrar
    {
        $route = new RouteDefinition($method, $this->applyGroupAttributes($uri), $handler);
        
        // Apply group middleware
        if (!empty($this->groupStack)) {
            $groupMiddleware = [];
            foreach ($this->groupStack as $group) {
                if (isset($group['middleware'])) {
                    $groupMiddleware = array_merge($groupMiddleware, (array) $group['middleware']);
                }
            }
            $route->middleware($groupMiddleware);
        }
        
        $this->routes[] = $route;
        
        return new RouteRegistrar($route);
    }

    public function group(array $attributes, callable $callback): void
    {
        $this->groupStack[] = $attributes;
        call_user_func($callback);
        array_pop($this->groupStack);
    }

    public function registerMiddleware(string $name, string $class): void
    {
        $this->middlewareRegistry[$name] = $class;
    }

    public function dispatch(Request $request): Response
    {
        foreach ($this->routes as $route) {
            if ($this->matchesRoute($route, $request)) {
                return $this->handleRoute($route, $request);
            }
        }

        return Response::json(['error' => 'Route not found'], 404);
    }

    private function matchesRoute(RouteDefinition $route, Request $request): bool
    {
        if ($route->getMethod() !== $request->method()) {
            return false;
        }

        $pattern = $this->convertUriToRegex($route->getUri());
        return preg_match($pattern, $request->uri());
    }

    private function convertUriToRegex(string $uri): string
    {
        // Convert route parameters like {id} to regex groups
        $pattern = preg_replace('/\{([^}]+)\}/', '([^/]+)', $uri);
        return '#^' . $pattern . '$#';
    }

    private function handleRoute(RouteDefinition $route, Request $request): Response
    {
        // Extract route parameters
        $params = $this->extractParams($route->getUri(), $request->uri());
        $request->setParams($params);

        // Build middleware chain
        $middlewareChain = $this->buildMiddlewareChain($route->getMiddleware(), function($request) use ($route) {
            return $this->executeHandler($route->getHandler(), $request);
        });

        return $middlewareChain($request);
    }

    private function extractParams(string $routeUri, string $requestUri): array
    {
        $routeParts = explode('/', trim($routeUri, '/'));
        $requestParts = explode('/', trim($requestUri, '/'));
        $params = [];

        foreach ($routeParts as $index => $part) {
            if (strpos($part, '{') === 0 && strpos($part, '}') === strlen($part) - 1) {
                $paramName = trim($part, '{}');
                $params[$paramName] = $requestParts[$index] ?? null;
            }
        }

        return $params;
    }

    private function buildMiddlewareChain(array $middleware, callable $final): callable
    {
        $chain = $final;

        foreach (array_reverse($middleware) as $middlewareName) {
            $middlewareClass = $this->middlewareRegistry[$middlewareName] ?? null;
            
            if ($middlewareClass && class_exists($middlewareClass)) {
                $middlewareInstance = new $middlewareClass();
                $chain = function($request) use ($middlewareInstance, $chain) {
                    return $middlewareInstance->handle($request, $chain);
                };
            }
        }

        return $chain;
    }

    private function executeHandler(callable|string $handler, Request $request): Response
    {
        if (is_callable($handler)) {
            $result = $handler($request);
        } else {
            $result = $this->executeControllerAction($handler, $request);
        }

        if ($result instanceof Response) {
            return $result;
        }

        if (is_array($result)) {
            return Response::json($result);
        }

        return Response::text((string) $result);
    }

    private function executeControllerAction(string $handler, Request $request): mixed
    {
        [$controllerClass, $method] = explode('@', $handler);
        
        if (!class_exists($controllerClass)) {
            throw new \InvalidArgumentException("Controller {$controllerClass} not found");
        }

        $controller = new $controllerClass();
        
        if (!method_exists($controller, $method)) {
            throw new \InvalidArgumentException("Method {$method} not found in {$controllerClass}");
        }

        return $controller->$method($request);
    }

    private function applyGroupAttributes(string $uri): string
    {
        $prefix = '';
        
        foreach ($this->groupStack as $group) {
            if (isset($group['prefix'])) {
                $prefix .= '/' . trim($group['prefix'], '/');
            }
        }
        
        return $prefix . $uri;
    }
}

class RouteDefinition
{
    private string $method;
    private string $uri;
    private callable|string $handler;
    private array $middleware = [];

    public function __construct(string $method, string $uri, callable|string $handler)
    {
        $this->method = $method;
        $this->uri = $uri;
        $this->handler = $handler;
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getHandler(): callable|string
    {
        return $this->handler;
    }

    public function middleware(array|string $middleware): self
    {
        $this->middleware = array_merge($this->middleware, (array) $middleware);
        return $this;
    }

    public function getMiddleware(): array
    {
        return $this->middleware;
    }
}

class RouteRegistrar
{
    private RouteDefinition $route;

    public function __construct(RouteDefinition $route)
    {
        $this->route = $route;
    }

    public function middleware(array|string $middleware): self
    {
        $this->route->middleware((array) $middleware);
        return $this;
    }
}
