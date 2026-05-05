<?php
// src/Middleware/RateLimitMiddleware.php
namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

class RateLimitMiddleware
{
    private const MAX_REQUESTS = 100;
    private const TIME_WINDOW = 3600; // 1 hour

    public function handle(Request $request): bool
    {
        $ip = $request->ip();

        try {
            $db = Database::getInstance();
            $pdo = $db->getConnection();

            // پاک کردن رکوردهای قدیمی
            $stmt = $pdo->prepare("
                DELETE FROM rate_limits 
                WHERE created_at < DATE_SUB(NOW(), INTERVAL :window SECOND)
            ");
            $stmt->execute(['window' => self::TIME_WINDOW]);

            // شمارش درخواست‌های اخیر
            $stmt = $pdo->prepare("
                SELECT COUNT(*) as count 
                FROM rate_limits 
                WHERE ip_address = :ip
            ");
            $stmt->execute(['ip' => $ip]);
            $result = $stmt->fetch(\PDO::FETCH_ASSOC);

            if ($result['count'] >= self::MAX_REQUESTS) {
                Response::json([
                    'error' => 'Rate limit exceeded',
                    'retry_after' => self::TIME_WINDOW
                ], 429);
                return false;
            }

            // ثبت درخواست جدید
            $stmt = $pdo->prepare("
                INSERT INTO rate_limits (ip_address, created_at) 
                VALUES (:ip, NOW())
            ");
            $stmt->execute(['ip' => $ip]);

            return true;

        } catch (\Exception $e) {
            // در صورت خطا، اجازه ادامه بده (fail-open)
            error_log("Rate limit error: " . $e->getMessage());
            return true;
        }
    }
}
