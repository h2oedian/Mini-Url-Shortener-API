<?php

// ❌ این خطوط دیگر وجود ندارند:
// use App\Core\Router;
// $router = new Router();
// return $router;

// ✅ فقط مسیرها را روی $router موجود ثبت می‌کنیم
$router->get('/test', function($request, $response) {
    $response->json(['message' => 'Test route works!']);
});

// URL Shortener routes
$router->post('/api/shorten', 'UrlController@shorten');
$router->get('/api/urls', 'UrlController@index');
$router->get('/api/urls/{id}', 'UrlController@show');
$router->delete('/api/urls/{id}', 'UrlController@delete');

// Redirect route
$router->get('/{shortCode}', 'RedirectController@redirect');

// Stats route
$router->get('/api/stats/{shortCode}', 'StatsController@show');
