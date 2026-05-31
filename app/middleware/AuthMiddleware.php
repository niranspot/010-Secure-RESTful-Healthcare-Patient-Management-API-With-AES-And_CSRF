<?php
namespace App\Middleware;

use App\Helpers\JWT;
use App\Helpers\Response;

class AuthMiddleware {
    public function handle($requestData) {
        $headers = getallheaders();
        $authHeader = $headers['Authorization'] ?? $headers['authorization'] ?? null; 

        if (!$authHeader || !preg_match('/Bearer\s(\S+)/', $authHeader, $matches)) { 
            Response::json(401, false, "Access denied. Authentication token missing.");
        }

        $token = $matches[1];
        $userData = JWT::validate($token); 

        if (!$userData) { 
            Response::json(401, false, "Access denied. Expired or invalid token context.");
        }

        $requestData['user'] = $userData; 
        return $requestData;
    }
}