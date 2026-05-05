<?php

namespace App\Models;

use App\Core\Database;
use PDO;

class Url
{
    private PDO $db;

    public function __construct()
    {
        $this->db = Database::getInstance()->getConnection();
    }

    public function create(string $originalUrl, ?int $userId = null): array
    {
        try {
            // شروع transaction
            $this->db->beginTransaction();

            $shortCode = $this->generateUniqueShortCode();

            $stmt = $this->db->prepare("
            INSERT INTO urls (original_url, short_code, user_id, created_at) 
            VALUES (:original_url, :short_code, :user_id, NOW())
        ");

            $stmt->execute([
                ':original_url' => $originalUrl,
                ':short_code' => $shortCode,
                ':user_id' => $userId
            ]);

            $this->db->commit();

            return $this->findByShortCode($shortCode);

        } catch (\PDOException $e) {
            $this->db->rollBack();

            // اگر خطای duplicate key بود، دوباره تلاش کن
            if ($e->getCode() == 23000) { // Duplicate entry
                return $this->create($originalUrl, $userId);
            }

            throw $e;
        }
    }


    private function generateUniqueShortCode(int $length = 6, int $maxAttempts = 10): string
    {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $charactersLength = strlen($characters);

        for ($attempt = 0; $attempt < $maxAttempts; $attempt++) {
            $shortCode = '';

            // تولید کد تصادفی
            for ($i = 0; $i < $length; $i++) {
                $shortCode .= $characters[random_int(0, $charactersLength - 1)];
            }

            // چک تکراری
            if (!$this->shortCodeExists($shortCode)) {
                return $shortCode;
            }
        }

        // اگر بعد از 10 بار تکراری بود، طول رو یکی بیشتر کن
        if ($length < 10) {
            return $this->generateUniqueShortCode($length + 1, $maxAttempts);
        }

        // در صورت شکست کامل (بعید)
        throw new \RuntimeException('Failed to generate unique short code after maximum attempts');
    }

    public function findByShortCode(string $shortCode): ?array
    {
        $stmt = $this->db->prepare("
            SELECT * FROM urls WHERE short_code = :short_code LIMIT 1
        ");

        $stmt->execute(['short_code' => $shortCode]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);

        return $result ?: null;
    }

    public function incrementClicks(int $urlId): void
    {
        $stmt = $this->db->prepare("
            UPDATE urls SET click_count = click_count + 1 WHERE id = :id
        ");

        $stmt->execute(['id' => $urlId]);
    }

    public function getStats(string $shortCode): ?array
    {
        $url = $this->findByShortCode($shortCode);

        if (!$url) {
            return null;
        }

        $stmt = $this->db->prepare("
            SELECT 
                COUNT(*) as total_clicks,
                COUNT(DISTINCT ip_address) as unique_visitors,
                DATE(clicked_at) as date,
                COUNT(*) as clicks_per_day
            FROM clicks 
            WHERE url_id = :url_id
            GROUP BY DATE(clicked_at)
            ORDER BY date DESC
            LIMIT 30
        ");

        $stmt->execute(['url_id' => $url['id']]);
        $dailyStats = $stmt->fetchAll(PDO::FETCH_ASSOC);

        return [
            'url' => $url,
            'daily_stats' => $dailyStats
        ];
    }

    public function shortCodeExists(string $shortCode): bool
    {
        return $this->findByShortCode($shortCode) !== null;
    }

}
