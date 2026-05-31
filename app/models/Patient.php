<?php
namespace App\Models;

use App\Core\Database;
use App\Helpers\AES;
use PDO;

class Patient {
    private $db;

    public function __construct() {
        $this->db = Database::getConnection();
    }

    // ─── DECRYPT a single patient row ─────────────────────────────
    private function decryptPatient($patient) {
        if (!$patient) return null;

        $patient['name']    = AES::decrypt($patient['name']);
        $patient['gender']  = AES::decrypt($patient['gender']);
        $patient['phone']   = AES::decrypt($patient['phone']);
        $patient['address'] = AES::decrypt($patient['address']);
        $patient['age']     = AES::decrypt($patient['age']);
        // age is INT → not encrypted
        return $patient;
    }

    // 1. Fetch all → decrypt each row
    public function getAll($userId) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE user_id = :user_id ORDER BY id DESC");
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $patients = $stmt->fetchAll();

        // Decrypt every patient row
        return array_map([$this, 'decryptPatient'], $patients);
    }

    // 2. Find by ID → decrypt
    public function findById($id, $userId) {
        $stmt = $this->db->prepare("SELECT * FROM patients WHERE id = :id AND user_id = :user_id LIMIT 1");
        $stmt->bindValue(':id',      $id,     PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        $stmt->execute();
        $patient = $stmt->fetch();

        return $this->decryptPatient($patient);
    }

    // 3. Create → encrypt before saving
    public function create($data, $userId) {
        $sql = "INSERT INTO patients (user_id, name, age, gender, phone, address, created_at, updated_at) 
                VALUES (:user_id, :name, :age, :gender, :phone, :address, NOW(), NOW())";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':user_id', $userId,                                PDO::PARAM_INT);
        $stmt->bindValue(':name',    AES::encrypt($data['name']    ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':age',     AES::encrypt($data['age'] ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':gender',  AES::encrypt($data['gender']  ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':phone',   AES::encrypt($data['phone']   ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':address', AES::encrypt($data['address'] ?? null), PDO::PARAM_STR);

        $stmt->execute();
        return $this->db->lastInsertId();
    }

    // 4. Update → encrypt before saving
    public function update($id, $data, $userId) {
        $sql = "UPDATE patients 
                SET name = :name, age = :age, gender = :gender, phone = :phone, address = :address, updated_at = NOW() 
                WHERE id = :id AND user_id = :user_id";

        $stmt = $this->db->prepare($sql);

        $stmt->bindValue(':id',      $id,                                    PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId,                                PDO::PARAM_INT);
        $stmt->bindValue(':name',    AES::encrypt($data['name']    ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':age',     AES::encrypt($data['age']     ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':gender',  AES::encrypt($data['gender']  ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':phone',   AES::encrypt($data['phone']   ?? null), PDO::PARAM_STR);
        $stmt->bindValue(':address', AES::encrypt($data['address'] ?? null), PDO::PARAM_STR);

        return $stmt->execute();
    }

    // 5. Delete 
    public function delete($id, $userId) {
        $stmt = $this->db->prepare("DELETE FROM patients WHERE id = :id AND user_id = :user_id");
        $stmt->bindValue(':id',      $id,     PDO::PARAM_INT);
        $stmt->bindValue(':user_id', $userId, PDO::PARAM_INT);
        return $stmt->execute();
    }
}