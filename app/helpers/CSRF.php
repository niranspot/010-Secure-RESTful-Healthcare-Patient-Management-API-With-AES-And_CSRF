<?php
namespace App\Helpers;

class CSRF {
    
    public static function startSession() {
        if (session_status() === PHP_SESSION_NONE) {
            session_start();
        }
    }

    public static function generate() {
        self::startSession();
        require_once __DIR__ . '/../../config/config.php';

        // Generate random token
        $token  = bin2hex(random_bytes(32));

        //rotation
        // $expiry = time() + CSRF_EXPIRY;

        // Store in session
        $_SESSION['csrf_token']        = $token;

        //rotation
        // $_SESSION['csrf_token_expiry'] = $expiry;

        return $token;
    }

    public static function validate($token) {
        self::startSession();

        // Check token exists in session
        if (empty($_SESSION['csrf_token'])) {
            return false;
        }

        // Check expiry
        // if (time() > $_SESSION['csrf_token_expiry']) {
        //     self::clear();
        //     return false;
        // }

        // Compare tokens safely
        if (!hash_equals($_SESSION['csrf_token'], $token)) {
            return false;
        }

        // Rotate token after every successful validation
        // self::generate();

        return true;
    }

    public static function clear() {
        self::startSession();
        unset($_SESSION['csrf_token']);
        // unset($_SESSION['csrf_token_expiry']);
    }
}