<?php

declare(strict_types=1);

namespace CoreNewspaper\Security;

use CoreNewspaper\Core\Config;

final class SessionManager
{
    public function __construct(private readonly Config $config)
    {
    }

    public function start(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            return;
        }

        session_name($this->config->get('session.name', 'core_news_session'));
        session_set_cookie_params([
            'httponly' => true,
            'secure' => true,
            'samesite' => $this->config->get('session.samesite', 'Strict'),
            'path' => '/',
        ]);

        session_start();
    }

    public function regenerate(): void
    {
        if (session_status() === PHP_SESSION_ACTIVE) {
            session_regenerate_id(true);
        }
    }

    public function destroy(): void
    {
        if (session_status() !== PHP_SESSION_ACTIVE) {
            return;
        }

        $_SESSION = [];
        if (ini_get('session.use_cookies')) {
            $params = session_get_cookie_params();
            setcookie(session_name(), '', time() - 42000, $params['path'], $params['domain'], $params['secure'], $params['httponly']);
        }
        session_destroy();
    }
}
