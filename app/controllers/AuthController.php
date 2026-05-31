<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\JWT;
use App\Helpers\Response;

class AuthController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function register($request) { 
        $body = $request['body'];
        
        // Validate Inputs
        if (empty($body['name']) || empty($body['email']) || empty($body['password'])) { 
            Response::json(400, false, "All mandatory text parameters (name, email, password) must be provided.");
        }

        // Check for Existing Account
        if ($this->userModel->findByEmail($body['email'])) { 
            Response::json(409, false, "An account is already linked to that email address.");
        }

        // Encrypt and Register User
        $hashedPassword = password_hash($body['password'], PASSWORD_DEFAULT); 
        $success = $this->userModel->create($body['name'], $body['email'], $hashedPassword); 

        if ($success) { 
            Response::json(201, true, "Account creation finalized successfully."); 
        }
        Response::json(500, false, "Registration encountered an unexpected internal error.");
    }

    public function login($request) {
        $body = $request['body'];

        if (empty($body['email']) || empty($body['password'])) {
            Response::json(400, false, "Email and password parameters cannot be blank.");
        }

        $user = $this->userModel->findByEmail($body['email']);

        if (!$user || !password_verify($body['password'], $user['password'])) {
            Response::json(401, false, "The credentials you entered do not match our records.");
        }

        
        $accessToken = JWT::generateAccessToken($user['id'], $user['email']);

    
        $refreshToken       = bin2hex(random_bytes(40));
        $refreshTokenExpiry = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXPIRY);

        // Save refresh token in DB
        $this->userModel->saveRefreshToken($user['id'], $refreshToken, $refreshTokenExpiry);

        // Set refresh token as HttpOnly cookie
        setcookie(
            'refresh_token',
            $refreshToken,
            [
                'expires'  => time() + REFRESH_TOKEN_EXPIRY,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        Response::json(200, true, "Authentication successful. Access granted.", [
            "access_token" => $accessToken,
            "token_type"   => "Bearer",
            "expires_in"   => JWT_EXPIRY
        ]);
    }

    public function logout($request) {
    $user = $request['user']; // comes from AuthMiddleware

    // Delete refresh token from DB
    $this->userModel->deleteRefreshToken($user['user_id']);

    // Delete refresh cookie from browser
    setcookie('refresh_token', '', [
        'expires'  => time() - 3600, // set to past → browser deletes it
        'path'     => '/',
        'httponly' => true,
        'samesite' => 'Strict'
    ]);

    Response::json(200, true, "Logged out successfully.");
}
    

}