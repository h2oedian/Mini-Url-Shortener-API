<?php

// Autoloader
spl_autoload_register(function ($class) {
    $prefix = 'App\\';
    $baseDir = __DIR__ . '/../src/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relativeClass = substr($class, $len);
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

//  .env
$envFile = __DIR__ . '/../.env';
if (file_exists($envFile)) {
    $lines = file($envFile, FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES);
    foreach ($lines as $line) {
        if (strpos(trim($line), '#') === 0) continue;
        if (strpos($line, '=') === false) continue;
        list($name, $value) = explode('=', $line, 2);
        $_ENV[trim($name)] = trim($value);
        putenv(trim($line));
    }
}

use App\Core\Router;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;

// Initialize database
Database::getInstance();

$router = new Router();

require_once __DIR__ . '/../routes/api.php';

// Handle request
$request = new Request();
$response = new Response();

try {
    error_log("DEBUG - Request Path: " . $request->getPath());
    error_log("DEBUG - Request URI: " . $request->getUri());
    $router->dispatch($request, $response);
} catch (Exception $e) {
    error_log("Error in index.php: " . $e->getMessage());
    $response->json(['error' => $e->getMessage()], 500);
}
