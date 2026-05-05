<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class ApiToken
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function validate(string $token): array|false
    {
        $stmt = $this->db->prepare("
            SELECT id, user_id, token, is_active, created_at 
            FROM api_tokens 
            WHERE token = ? AND is_active = 1
        ");

        $stmt->execute([$token]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: false;
    }

    public function create(int $userId, string $name = 'Default Token'): array
    {
        $token = bin2hex(random_bytes(32));

        $stmt = $this->db->prepare("
            INSERT INTO api_tokens (user_id, token, name, is_active, created_at) 
            VALUES (?, ?, ?, 1, NOW())
        ");

        $stmt->execute([$userId, $token, $name]);

        return [
            'id' => $this->db->lastInsertId(),
            'token' => $token,
            'name' => $name
        ];
    }

    public function revoke(string $token): bool
    {
        $stmt = $this->db->prepare("
            UPDATE api_tokens 
            SET is_active = 0 
            WHERE token = ?
        ");

        return $stmt->execute([$token]);
    }

    public function list(int $userId): array
    {
        $stmt = $this->db->prepare("
            SELECT id, token, name, is_active, created_at 
            FROM api_tokens 
            WHERE user_id = ?
            ORDER BY created_at DESC
        ");

        $stmt->execute([$userId]);

        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
