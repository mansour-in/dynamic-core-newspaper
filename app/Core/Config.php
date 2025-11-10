<?php

declare(strict_types=1);

namespace CoreNewspaper\Core;

use InvalidArgumentException;

final class Config
{
    /**
     * @var array<string,mixed>
     */
    private array $values = [];

    public function __construct()
    {
        $this->values = [
            'app.env' => $_ENV['APP_ENV'] ?? 'production',
            'app.debug' => filter_var($_ENV['APP_DEBUG'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'app.url' => $_ENV['APP_URL'] ?? 'https://newspaper.core.fit',
            'app.timezone' => $_ENV['APP_TIMEZONE'] ?? 'Asia/Riyadh',
            'session.name' => $_ENV['SESSION_NAME'] ?? 'core_news_session',
            'session.samesite' => $_ENV['SESSION_SAMESITE'] ?? 'Strict',
            'db.host' => $_ENV['DB_HOST'] ?? 'localhost',
            'db.port' => (int)($_ENV['DB_PORT'] ?? 3306),
            'db.name' => $_ENV['DB_DATABASE'] ?? 'core_newspaper',
            'db.user' => $_ENV['DB_USERNAME'] ?? 'core_news_user',
            'db.password' => $_ENV['DB_PASSWORD'] ?? '',
            'sequence.strategy' => $_ENV['SEQUENCE_STRATEGY'] ?? 'auto',
            'probe.enabled' => filter_var($_ENV['PROBE_ENABLED'] ?? 'false', FILTER_VALIDATE_BOOLEAN),
            'log.app' => $_ENV['LOG_PATH'] ?? __DIR__ . '/../../logs/app.log',
            'log.cron' => $_ENV['CRON_LOG_PATH'] ?? __DIR__ . '/../../logs/cron.log',
            'security.hsts_max_age' => (int)($_ENV['HSTS_MAX_AGE'] ?? 31536000),
        ];
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->values[$key] ?? $default;
    }

    public function require(string $key): mixed
    {
        if (!array_key_exists($key, $this->values)) {
            throw new InvalidArgumentException('Missing configuration key: ' . $key);
        }

        return $this->values[$key];
    }
}
