<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Click
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function record(int $urlId, string $ipAddress, string $userAgent, ?string $referer = null): void
    {
        $stmt = $this->db->prepare("
            INSERT INTO clicks (url_id, ip_address, user_agent, referer, clicked_at) 
            VALUES (:url_id, :ip_address, :user_agent, :referer, NOW())
        ");
        $stmt->execute([
            'url_id' => $urlId,
            'ip_address' => $ipAddress,
            'user_agent' => $userAgent,
            'referer' => $referer
        ]);
    }

    public function getClicksByUrl(int $urlId, int $limit = 100): array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM clicks 
            WHERE url_id = :url_id 
            ORDER BY clicked_at DESC 
            LIMIT :limit
        ");
        $stmt->bindValue('url_id', $urlId, PDO::PARAM_INT);
        $stmt->bindValue('limit', $limit, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function getConnection(): PDO
    {
        return $this->db;
    }
}
