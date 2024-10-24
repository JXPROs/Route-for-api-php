<?php

class Route {
    private static array $routes = [];
    private static array $middlewares = [];
    private static string $prefix = '';
    private static array $patterns = [
        'id' => '[0-9]+',
        'slug' => '[a-z0-9-]+',
        'any' => '.*'
    ];

    public static function get(string $uri, callable|array $action) {
        self::addRoute('GET', $uri, $action);
    }

    public static function post(string $uri, callable|array $action) {
        self::addRoute('POST', $uri, $action);
    }

    public static function put(string $uri, callable|array $action) {
        self::addRoute('PUT', $uri, $action);
    }

    public static function delete(string $uri, callable|array $action) {
        self::addRoute('DELETE', $uri, $action);
    }

    public static function group(array $attributes, callable $callback) {
        $previousPrefix = self::$prefix;
        $previousMiddlewares = self::$middlewares;

        if (isset($attributes['prefix'])) {
            self::$prefix .= '/' . trim($attributes['prefix'], '/');
        }

        if (isset($attributes['middleware'])) {
            self::$middlewares = array_merge(
                self::$middlewares,
                (array) $attributes['middleware']
            );
        }

        $callback();

        self::$prefix = $previousPrefix;
        self::$middlewares = $previousMiddlewares;
    }

    private static function addRoute(string $method, string $uri, callable|array $action) {
        $uri = self::$prefix . '/' . trim($uri, '/');
        $uri = trim($uri, '/');
        
        self::$routes[] = [
            'method' => $method,
            'uri' => $uri,
            'action' => $action,
            'middlewares' => self::$middlewares
        ];
    }

    public static function dispatch() {
        $method = $_SERVER['REQUEST_METHOD'];
        $uri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
        $uri = trim($uri, '/');

        foreach (self::$routes as $route) {
            if ($route['method'] !== $method) {
                continue;
            }

            $pattern = self::buildPattern($route['uri']);
            
            if (preg_match($pattern, $uri, $matches)) {
                array_shift($matches); // Remove full match

                // Execute middlewares
                foreach ($route['middlewares'] as $middleware) {
                    $middlewareInstance = new $middleware();
                    if (!$middlewareInstance->handle()) {
                        return;
                    }
                }

                // Execute controller action or closure
                if (is_array($route['action'])) {
                    [$controller, $method] = $route['action'];
                    $controllerInstance = new $controller();
                    return $controllerInstance->$method(...$matches);
                }

                return $route['action'](...$matches);
            }
        }

        // No route found
        header("HTTP/1.0 404 Not Found");
        echo json_encode(['error' => 'Route not found']);
    }

    private static function buildPattern(string $uri): string {
        $segments = explode('/', $uri);
        $pattern = [];

        foreach ($segments as $segment) {
            if (preg_match('/^{(.+?)}$/', $segment, $matches)) {
                $paramName = $matches[1];
                $paramPattern = self::$patterns[$paramName] ?? '[^/]+';
                $pattern[] = "($paramPattern)";
            } else {
                $pattern[] = preg_quote($segment, '#');
            }
        }

        return '#^' . implode('/', $pattern) . '$#';
    }
}

// Example Middleware class
abstract class Middleware {
    abstract public function handle(): bool;
}

// Example Auth Middleware
class AuthMiddleware extends Middleware {
    public function handle(): bool {
        // Implement your authentication logic here
        $authHeader = $_SERVER['HTTP_AUTHORIZATION'] ?? '';
        if (empty($authHeader)) {
            header('HTTP/1.0 401 Unauthorized');
            echo json_encode(['error' => 'Authentication required']);
            return false;
        }
        return true;
    }
}

// Example Controller
class UserController {
    public function index() {
        header('Content-Type: application/json');
        echo json_encode(['message' => 'List of users']);
    }

    public function show($id) {
        header('Content-Type: application/json');
        echo json_encode(['message' => "Show user $id"]);
    }
}