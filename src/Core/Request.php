<?php

namespace App\Core;

class Request
{
    private string $method;
    private string $uri;
    private array $headers;
    private array $attributes = [];

    public function __construct()
    {
        $this->method = $_SERVER['REQUEST_METHOD'];
        $this->uri = $_SERVER['REQUEST_URI'];
        $this->headers = getallheaders() ?: [];
    }

    public function getMethod(): string
    {
        return $this->method;
    }

    public function getUri(): string
    {
        return $this->uri;
    }

    public function getPath(): string
    {
        $uri = $_SERVER['REQUEST_URI'];
        // حذف query string (اگر وجود داشته باشد)
        if (($pos = strpos($uri, '?')) !== false) {
            $uri = substr($uri, 0, $pos);
        }
        // حذف پیشوند '/Mini-Url-Shortener-API/public/' از URI
        $publicPos = strpos($uri, 'public/');
        if ($publicPos !== false) {
            $path = substr($uri, $publicPos + 7); // 7 = طول 'public/'
            return '/' . ltrim($path, '/');
        }
        // اگر پیشوند 'public/' پیدا نشد، همان URI اصلی را برگردان
        return '/' . ltrim($uri, '/');
    }



    public function getHeader(string $name): ?string
    {
        return $this->headers[$name] ?? null;
    }

    public function getBody(): array
    {
        $contentType = $this->getHeader('Content-Type') ?? '';

        if (strpos($contentType, 'application/json') !== false) {
            $json = file_get_contents('php://input');
            return json_decode($json, true) ?? [];
        }

        return $_POST;
    }

    public function ip(): string
    {
        // بررسی IP از پشت proxy
        if (!empty($_SERVER['HTTP_CLIENT_IP'])) {
            return $_SERVER['HTTP_CLIENT_IP'];
        } elseif (!empty($_SERVER['HTTP_X_FORWARDED_FOR'])) {
            return $_SERVER['HTTP_X_FORWARDED_FOR'];
        } else {
            return $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        }
    }

    public function getIp(): string
    {
        return $this->ip();
    }

    public function setAttribute(string $key, $value): void
    {
        $this->attributes[$key] = $value;
    }

    public function getAttribute(string $key, $default = null)
    {
        return $this->attributes[$key] ?? $default;
    }
    public function getUserAgent(): ?string
    {
        return $this->getHeader('User-Agent');
    }

    public function getReferer(): ?string
    {
        return $this->getHeader('Referer');
    }

}
