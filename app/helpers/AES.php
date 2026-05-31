<?php
namespace App\Helpers;

class AES {
    private static function getKey() {
        require_once __DIR__ . '/../../config/config.php';
        // AES-256 needs exactly 32 bytes key
        return substr(hash('sha256', AES_KEY), 0, 32);
    }

    public static function encrypt($data) {
        if ($data === null || $data === '') return null;

        $key    = self::getKey();
        $iv     = random_bytes(16); // random 16 byte IV every time
        
        $encrypted = openssl_encrypt(
            $data,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );

        // Store iv:encrypted together → needed for decryption
        return base64_encode($iv . $encrypted);
    }

    public static function decrypt($data) {
        if ($data === null || $data === '') return null;

        $key     = self::getKey();
        $decoded = base64_decode($data);

        // Split iv (first 16 bytes) and encrypted data (rest)
        $iv        = substr($decoded, 0, 16);
        $encrypted = substr($decoded, 16);

        return openssl_decrypt(
            $encrypted,
            'AES-256-CBC',
            $key,
            OPENSSL_RAW_DATA,
            $iv
        );
    }
}