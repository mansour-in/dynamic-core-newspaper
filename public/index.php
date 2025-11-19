<?php

declare(strict_types=1);

use App\Redirector;
use App\Responder;

require __DIR__ . '/../src/Redirector.php';
require __DIR__ . '/../src/Responder.php';

$appConfig = require __DIR__ . '/../config/app.php';
$providers = require __DIR__ . '/../config/providers.php';

if (!is_array($appConfig) || !is_array($providers)) {
    Responder::error('Configuration files are invalid.');
    exit;
}

$timezoneId = $appConfig['APP_TIMEZONE'] ?? 'Asia/Riyadh';
if (!is_string($timezoneId) || $timezoneId === '') {
    $timezoneId = 'Asia/Riyadh';
}

if (!@date_default_timezone_set($timezoneId)) {
    Responder::error('Invalid timezone configured.');
    exit;
}

try {
    $now = new \DateTimeImmutable('now', new \DateTimeZone($timezoneId));
} catch (\Throwable $exception) {
    Responder::error('Unable to establish timezone context.');
    exit;
}

$uri = $_SERVER['REQUEST_URI'] ?? '/';
$path = parse_url($uri, PHP_URL_PATH) ?? '/';
$path = trim($path, '/');

if ($path === 'health') {
    Responder::health();
    exit;
}

if ($path === 'version') {
    $version = (string) ($appConfig['VERSION'] ?? '0.0.0');
    Responder::version($version);
    exit;
}

if ($path === '') {
    Responder::notFound('Provider not specified.');
    exit;
}

if (!isset($providers[$path]) || empty($providers[$path]['active'])) {
    Responder::notFound('Provider not found or inactive.');
    exit;
}

$provider = $providers[$path];

try {
    $targetUrl = Redirector::build($provider, $now);
} catch (\Throwable $exception) {
    Responder::error('Provider configuration error.');
    exit;
}

$logPath = $appConfig['LOG_PATH'] ?? null;
if (is_string($logPath) && $logPath !== '') {
    $logEntry = sprintf(
        "%s\t%s\t%s\n",
        $now->format(\DateTimeImmutable::ATOM),
        $provider['slug'] ?? $path,
        $targetUrl
    );
    @file_put_contents($logPath, $logEntry, FILE_APPEND | LOCK_EX);
}

Responder::redirect($targetUrl);
