<?php
namespace App\Helpers;

class Response {
    public static function json($statusCode, $status, $message, $data = null) {
        http_response_code($statusCode);
        $response = [
            "status" => $status,
            "message" => $message
        ];
        if ($data !== null) {
            $response['data'] = $data;
        }
        echo json_encode($response);
        exit;
    }
}