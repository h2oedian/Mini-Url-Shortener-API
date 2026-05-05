<?php

namespace App\Middleware;

use App\Core\Request;
use App\Core\Response;
use App\Models\ApiToken;

class AuthMiddleware
{
    public function handle(Request $request): bool
    {
        $authHeader = $request->getHeader('Authorization');

        if (!$authHeader || !preg_match('/Bearer\s+(.*)$/i', $authHeader, $matches)) {
            Response::json(['error' => 'Authorization token required'], 401);
            return false;
        }

        $token = $matches[1];
        $apiToken = new ApiToken();
        $tokenData = $apiToken->validate($token);

        if (!$tokenData) {
            Response::json(['error' => 'Invalid or expired token'], 401);
            return false;
        }

        // ذخیره user_id در Request
        $request->setAttribute('user_id', $tokenData['user_id']);
        $request->setAttribute('token_data', $tokenData);

        return true;
    }
}
