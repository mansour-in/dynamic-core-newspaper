<?php

declare(strict_types=1);

namespace CoreNewspaper\Core;

final class Request
{
    private array $session;

    public function __construct(
        private readonly array $get,
        private readonly array $post,
        private readonly array $server,
        private readonly array $cookies,
        private readonly array $files,
        array &$session
    ) {
        $this->session =& $session;
    }

    public static function fromGlobals(): self
    {
        $session =& $_SESSION;
        return new self($_GET, $_POST, $_SERVER, $_COOKIE, $_FILES, $session);
    }

    public function method(): string
    {
        return strtoupper($this->server['REQUEST_METHOD'] ?? 'GET');
    }

    public function uri(): string
    {
        $uri = $this->server['REQUEST_URI'] ?? '/';
        $path = parse_url($uri, PHP_URL_PATH) ?: '/';
        return rtrim($path, '/') === '' ? '/' : rtrim($path, '/');
    }

    public function get(string $key, mixed $default = null): mixed
    {
        return $this->get[$key] ?? $default;
    }

    public function post(string $key, mixed $default = null): mixed
    {
        return $this->post[$key] ?? $default;
    }

    public function allPost(): array
    {
        return $this->post;
    }

    public function server(string $key, mixed $default = null): mixed
    {
        return $this->server[$key] ?? $default;
    }

    public function ip(): string
    {
        $forwarded = $this->server['HTTP_X_FORWARDED_FOR'] ?? '';
        if ($forwarded !== '') {
            $parts = explode(',', $forwarded);
            return trim($parts[0]);
        }

        return (string)($this->server['REMOTE_ADDR'] ?? '0.0.0.0');
    }

    public function &session(): array
    {
        return $this->session;
    }

    public function cookie(string $key, mixed $default = null): mixed
    {
        return $this->cookies[$key] ?? $default;
    }
}
