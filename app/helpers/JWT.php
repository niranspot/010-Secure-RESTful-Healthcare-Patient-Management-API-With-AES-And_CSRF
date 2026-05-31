<?php
namespace App\Helpers;

class JWT {
    private static function base64UrlEncode($data) {
        return str_replace(['+', '/', '='], ['-', '_', ''], base64_encode($data));
    }

    private static function base64UrlDecode($data) {
        $remainder = strlen($data) % 4;
        if ($remainder) {
            $padlen = 4 - $remainder;
            $data .= str_repeat('=', $padlen);
        }
        return base64_decode(str_replace(['-', '_'], ['+', '/'], $data));
    }

    //old generate method, replaced by generateAccessToken for better clarity
    // public static function generate($payload) {
    //     require_once __DIR__ . '/../../config/config.php';
        
    //     $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);
        
    //     $payload['iat'] = time(); 
    //     $payload['exp'] = time() + JWT_EXPIRY; 

    //     $base64UrlHeader = self::base64UrlEncode($header);
    //     $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

    //     $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    //     $base64UrlSignature = self::base64UrlEncode($signature);

    //     return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
    // }

    
public static function generateAccessToken($userId, $email) {
    require_once __DIR__ . '/../../config/config.php';

    $header = json_encode(['alg' => 'HS256', 'typ' => 'JWT']);

    $payload = [
        'user_id' => $userId,
        'email'   => $email,
        'iat'     => time(),
        'exp'     => time() + JWT_EXPIRY // 15 minutes
    ];

    $base64UrlHeader  = self::base64UrlEncode($header);
    $base64UrlPayload = self::base64UrlEncode(json_encode($payload));

    $signature        = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true);
    $base64UrlSignature = self::base64UrlEncode($signature);

    return $base64UrlHeader . "." . $base64UrlPayload . "." . $base64UrlSignature;
}

    public static function validate($token) {
        require_once __DIR__ . '/../../config/config.php';

        $parts = explode('.', $token); 
        if (count($parts) !== 3) return false;

        list($base64UrlHeader, $base64UrlPayload, $base64UrlSignature) = $parts;

        // Re-calculate signature to verify data integrity
        $signature = hash_hmac('sha256', $base64UrlHeader . "." . $base64UrlPayload, JWT_SECRET, true); 
        if (!hash_equals(self::base64UrlEncode($signature), $base64UrlSignature)) { 
            return false; // Signature breakdown mismatch!
        }

        $payload = json_decode(self::base64UrlDecode($base64UrlPayload), true); 
        
        // Confirm Expiry Timestamps
        if (isset($payload['exp']) && $payload['exp'] < time()) { 
            return false; // Token has expired
        }

        return $payload; 
    }
}