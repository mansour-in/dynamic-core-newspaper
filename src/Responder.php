<?php

declare(strict_types=1);

namespace App;

class Responder
{
    public static function health(): void
    {
        self::output('OK', 200);
    }

    public static function version(string $version): void
    {
        self::output($version, 200);
    }

    public static function notFound(string $message): void
    {
        self::output($message, 404);
    }

    public static function error(string $message): void
    {
        http_response_code(500);
        header('Content-Type: text/html; charset=utf-8');
        echo '<h1>Application error</h1>';
        echo '<p>' . htmlspecialchars($message, ENT_QUOTES | ENT_SUBSTITUTE, 'UTF-8') . '</p>';
    }

    public static function redirect(string $url): void
    {
        header('Location: ' . $url, true, 302);
        header('Cache-Control: no-cache');
        echo 'Redirecting to latest issue...';
    }

    private static function output(string $message, int $status): void
    {
        http_response_code($status);
        header('Content-Type: text/plain; charset=utf-8');
        echo $message;
    }
}
