<?php
namespace App\Middleware;

use App\Helpers\Response;

class JsonMiddleware {
    public function handle($requestData) {
        header('Content-Type: application/json; charset=UTF-8'); 

        $method = $_SERVER['REQUEST_METHOD'];
        
        if (in_array($method, ['POST', 'PUT', 'PATCH'])) { 
            $contentType = $_SERVER["CONTENT_TYPE"] ?? '';
            
            if (strpos($contentType, 'application/json') === false) { 
                Response::json(400, false, "Invalid header request type. Must be application/json.");
            }

            $rawInput = file_get_contents('php://input'); 
            if (empty(trim($rawInput))) { 
                Response::json(400, false, "The payload request body is empty.");
            }

            $decodedData = json_decode($rawInput, true); 
            if (json_last_error() !== JSON_ERROR_NONE) { 
                Response::json(400, false, "Malformed JSON syntax structure detected.");
            }

            $requestData['body'] = $decodedData; 
        }
        return $requestData;
    }
}