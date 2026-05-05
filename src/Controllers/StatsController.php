<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Url;
use App\Models\Click;
use PDO;

class StatsController
{
    public function show(Request $request, Response $response, string $shortCode): void
    {
        $urlModel = new Url();
        $url = $urlModel->findByShortCode($shortCode);

        if (!$url) {
            $response->json(['error' => 'Short URL not found'], 404);
            return;
        }

        $clickModel = new Click();
        $db = $clickModel->getConnection();

        //click count
        $totalClicks = $url['click_count'] ?? 0;

        // unique clicks base on ip
        $stmt = $db->prepare("
             SELECT COUNT(DISTINCT ip_address) as unique_clicks 
             FROM clicks 
             WHERE url_id = :url_id
        ");
        $stmt->execute(['url_id' => $url['id']]);
        $uniqueClicks = $stmt->fetch(PDO::FETCH_ASSOC)['unique_clicks'] ?? 0;

       //nemoodar roozane
        $stmt = $db->prepare("
            SELECT DATE(clicked_at) as date, COUNT(*) as count
            FROM clicks
            WHERE url_id = :url_id 
              AND clicked_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY DATE(clicked_at)
            ORDER BY date ASC
        ");
        $stmt->execute(['url_id' => $url['id']]);
        $dailyChart = $stmt->fetchAll(PDO::FETCH_ASSOC);

        //most use user agent
        $stmt = $db->prepare("
            SELECT user_agent, COUNT(*) as count
            FROM clicks
            WHERE url_id = :url_id
            GROUP BY user_agent
            ORDER BY count DESC
            LIMIT 10
        ");
        $stmt->execute(['url_id' => $url['id']]);
        $topUserAgents = $stmt->fetchAll(PDO::FETCH_ASSOC);

        $response->json([
            'short_code' => $shortCode,
            'original_url' => $url['original_url'],
            'total_clicks' => $totalClicks,
            'unique_clicks' => $uniqueClicks,
            'daily_chart' => $dailyChart,
            'top_user_agents' => $topUserAgents
        ]);
    }
}
