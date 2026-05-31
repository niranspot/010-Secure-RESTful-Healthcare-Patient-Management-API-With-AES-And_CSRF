<?php
namespace App\Controllers;

use App\Models\User;
use App\Helpers\JWT;
use App\Helpers\Response;

class TokenController {
    private $userModel;

    public function __construct() {
        $this->userModel = new User();
    }

    public function refresh($request) {
        // Read refresh token from HttpOnly cookie
        $refreshToken = $_COOKIE['refresh_token'] ?? null;

        if (!$refreshToken) {
            Response::json(401, false, "Refresh token missing.");
        }

        // Validate against DB
        $user = $this->userModel->findByRefreshToken($refreshToken);

        if (!$user) {
            Response::json(401, false, "Refresh token invalid or expired. Please login again.");
        }

        // Generate new access token
        $newAccessToken = JWT::generateAccessToken($user['id'], $user['email']);

        // Rotate refresh token (old one becomes invalid)
        $newRefreshToken       = bin2hex(random_bytes(40));
        $newRefreshTokenExpiry = date('Y-m-d H:i:s', time() + REFRESH_TOKEN_EXPIRY);

        $this->userModel->saveRefreshToken($user['id'], $newRefreshToken, $newRefreshTokenExpiry);

        // Set new refresh cookie
        setcookie(
            'refresh_token',
            $newRefreshToken,
            [
                'expires'  => time() + REFRESH_TOKEN_EXPIRY,
                'path'     => '/',
                'httponly' => true,
                'samesite' => 'Strict'
            ]
        );

        Response::json(200, true, "Token refreshed successfully.", [
            "access_token" => $newAccessToken,
            "token_type"   => "Bearer",
            "expires_in"   => 900
        ]);
    }
}