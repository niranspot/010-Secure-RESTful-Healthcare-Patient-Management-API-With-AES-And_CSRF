<?php
namespace App\Core;

class Router {
    private $routes = [];

    public function add($method, $route, $controllerAction, $middlewares = []) {
        // Convert routes like /api/patients/{id} into a clean regex match
        $routeRegex = preg_replace('/\{[a-zA-Z0-9_]+\}/', '([a-zA-Z0-9_]+)', $route);
        $routeRegex = '#' . $routeRegex . '$#';

        $this->routes[] = [
            'method' => strtoupper($method),
            'route' => $routeRegex,
            'action' => $controllerAction,
            'middlewares' => $middlewares
        ];
    }

    public function dispatch($requestUri, $requestMethod) {
        $urlParts = parse_url($requestUri);
        $path = $urlParts['path'];
        $method = strtoupper($requestMethod);

        foreach ($this->routes as $route) {
            if ($route['method'] === $method && preg_match($route['route'], $path, $matches)) {
                array_shift($matches); // Drop full string match match parameter

                // Execute mapped route middleware chain
                $requestData = ['body' => null, 'user' => null, 'params' => $matches];
                
                foreach ($route['middlewares'] as $middlewareClass) {
                    $middleware = new $middlewareClass();
                    $requestData = $middleware->handle($requestData);
                }

                // Resolve target Controller
                list($controllerName, $methodName) = explode('@', $route['action']);
                $fullControllerClass = "App\\Controllers\\" . $controllerName;
                
                if (class_exists($fullControllerClass)) {
                    $controller = new $fullControllerClass();
                    if (method_exists($controller, $methodName)) {
                        //$controller->update($requestData);
                        call_user_func_array([$controller, $methodName], [$requestData]);
                        return;
                    }
                }
                
                $this->respondError(500, "Action endpoint setup misconfigured.");
                return;
            }
        }
        $this->respondError(404, "API route footprint not encountered.");
    }

    private function respondError($code, $message) {
        http_response_code($code);
        echo json_encode(["status" => false, "message" => $message]);
        exit;
    }
}