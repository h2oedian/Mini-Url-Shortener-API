<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Url;
use App\Models\Click;

class UrlController
{
    public function shorten(Request $request): void
    {
        $data = $request->getBody();
        $url = $data['url'] ?? null;


        if (!$url) {
            Response::json(['error' => 'URL is required'], 400);
            return;
        }

        if (strlen($url) > 2048) {
            Response::json(['error' => 'URL is too long (max 2048 characters)'], 400);
            return;
        }

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            Response::json(['error' => 'Invalid URL format'], 400);
            return;
        }

        //just(http/https)
        $parsedUrl = parse_url($url);
        if (!in_array($parsedUrl['scheme'] ?? '', ['http', 'https'])) {
            Response::json(['error' => 'Only HTTP/HTTPS URLs are allowed'], 400);
            return;
        }

        // Blacklist
        $blacklistedDomains = ['localhost', '127.0.0.1', '0.0.0.0'];
        $host = $parsedUrl['host'] ?? '';
        if (in_array($host, $blacklistedDomains)) {
            Response::json(['error' => 'This domain is not allowed'], 400);
            return;
        }

        try {
            $userId = $request->getAttribute('user_id');
            $urlModel = new Url();
            $result = $urlModel->create($url, $userId);

            if (!$result) {
                Response::json(['error' => 'Failed to create short URL'], 500);
                return;
            }

            $shortCode = $result['short_code'];
            $shortUrl = $_ENV['APP_URL'] . '/' . $shortCode;

            Response::json([
                'success' => true,
                'short_url' => $shortUrl,
                'short_code' => $shortCode,
                'original_url' => $url
            ], 201);

        } catch (\Exception $e) {
            error_log('URL shortening error: ' . $e->getMessage());
            Response::json(['error' => 'Internal server error'], 500);
        }
    }

    public function index(Request $request): void
    {
        $urlModel = new Url();
        $urls = $urlModel->getAll();
        (new Response())->json(['urls' => $urls]);
    }

    public function show(Request $request, string $id): void
    {
        $urlModel = new Url();
        $url = $urlModel->findById($id);

        if (!$url) {
            (new Response())->json(['error' => 'URL not found'], 404);
            return;
        }

        (new Response())->json($url);
    }

    public function delete(Request $request, string $id): void
    {
        $urlModel = new Url();
        $deleted = $urlModel->delete($id);

        if (!$deleted) {
            (new Response())->json(['error' => 'URL not found'], 404);
            return;
        }

        (new Response())->json(['message' => 'URL deleted successfully']);
    }
}
