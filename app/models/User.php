<?php
namespace App\Models;

use App\Core\Database;
use PDO;

class User {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    public function findByEmail($email) { 
        $stmt = $this->db->prepare("SELECT id,email,password FROM users WHERE email = :email LIMIT 1");
        $stmt->execute(['email' => $email]);
        return $stmt->fetch();
    }
    

    public function create($name, $email, $hashedPassword) {
        $stmt = $this->db->prepare("INSERT INTO users (name, email, password, created_at, updated_at) VALUES (:name, :email, :password, NOW(), NOW())");
        return $stmt->execute([
            'name' => $name,
            'email' => $email,
            'password' => $hashedPassword 
        ]);
    }

    public function saveRefreshToken($userId, $token, $expiry) {
        $stmt = $this->db->prepare("UPDATE users SET refresh_token = :token, refresh_token_expiry = :expiry WHERE id = :id");
        return $stmt->execute([
        'token'  => $token,
        'expiry' => $expiry,
        'id'     => $userId
        ]);
    }

    public function findByRefreshToken($token) {
        $stmt = $this->db->prepare("SELECT id, email FROM users WHERE refresh_token = :token AND refresh_token_expiry > NOW() LIMIT 1");
        $stmt->execute(['token' => $token]);
        return $stmt->fetch();
    }
    public function deleteRefreshToken($userId) {
        $stmt = $this->db->prepare("UPDATE users SET refresh_token = NULL, refresh_token_expiry = NULL WHERE id = :id");
        return $stmt->execute(['id' => $userId]);
    }
}