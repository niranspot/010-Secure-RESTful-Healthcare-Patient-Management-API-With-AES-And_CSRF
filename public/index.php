<?php
session_start();
// 1. Establish Explicit Object Class Autoloader Engine (PSR-4 Simulation)
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $base_dir = __DIR__ . '/../app/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require_once $file;
    }
});


use App\Core\Router;
use App\Middleware\JsonMiddleware;
use App\Middleware\AuthMiddleware;
use App\Middleware\CsrfMiddleware;

// 2. Instantiate and Map Routing Blueprints
$router = new Router();

// Globally Applicable Global Arrays
$globalMiddlewares    = [JsonMiddleware::class];
$protectedMiddlewares = [JsonMiddleware::class, CsrfMiddleware::class, AuthMiddleware::class];

// Public Open Security Endpoints
$router->add('POST', '/api/register', 'AuthController@register', $globalMiddlewares); 
$router->add('POST', '/api/login',    'AuthController@login',    $globalMiddlewares); 
$router->add('POST', '/api/token/refresh', 'TokenController@refresh', $globalMiddlewares);


// CSRF Token Endpoint for Frontend to Fetch and Use in Subsequent Requests
$router->add('GET', '/api/csrf-token', 'CsrfController@token', []);



// Protected JWT Security Endpoints
$router->add('GET',    '/api/patients',       'PatientController@index',   $protectedMiddlewares); 
$router->add('GET',    '/api/patients/{id}',  'PatientController@show',    $protectedMiddlewares);
$router->add('POST',   '/api/patients',       'PatientController@store',   $protectedMiddlewares); 
$router->add('PUT',    '/api/patients/{id}',  'PatientController@update',  $protectedMiddlewares); 
$router->add('DELETE', '/api/patients/{id}',  'PatientController@destroy', $protectedMiddlewares); 

// Logout Endpoint to Invalidate Refresh Token
$router->add('POST', '/api/logout', 'AuthController@logout', $protectedMiddlewares);


$requestUri = $_SERVER['REQUEST_URI'] ?? '/';
$requestMethod = $_SERVER['REQUEST_METHOD'] ?? 'GET';

$router->dispatch($requestUri, $requestMethod);