<?php

namespace App\Controllers;

use App\Core\Request;
use App\Core\Response;
use App\Models\Url;
use App\Models\Click;

class RedirectController
{
    public function redirect(Request $request, Response $response, string $shortCode): void
    {
        $urlModel = new Url();
        $url = $urlModel->findByShortCode($shortCode);

        if (!$url) {
            (new Response())->json(['error' => '404 - Short URL not found'], 404);
            return;
        }

        // amar click
        $clickModel = new Click();
        $clickModel->record(
            $url['id'],
            $request->getIp(),
            $request->getUserAgent(),
            $request->getReferer()
        );

        $urlModel->incrementClicks($url['id']);

        (new Response())->redirect($url['original_url']);
    }
}
