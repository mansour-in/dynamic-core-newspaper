<?php

declare(strict_types=1);

namespace CoreNewspaper\Services;

use CoreNewspaper\Core\Database;
use CoreNewspaper\Core\Request;
use PDO;

final class AuthService
{
    public const SESSION_KEY = 'admin_user';

    public function __construct(private readonly Database $database)
    {
    }

    public function attempt(string $email, string $password): bool
    {
        $stmt = $this->database->connection()->prepare('SELECT id, email, password_hash, role FROM admin_users WHERE email = :email');
        $stmt->execute(['email' => $email]);
        $user = $stmt->fetch(PDO::FETCH_ASSOC);
        if (!$user) {
            return false;
        }

        if (!password_verify($password, $user['password_hash'])) {
            return false;
        }

        $session =& $_SESSION;
        $session[self::SESSION_KEY] = [
            'id' => (int)$user['id'],
            'email' => $user['email'],
            'role' => $user['role'],
        ];

        return true;
    }

    public function check(): bool
    {
        $session =& $_SESSION;
        return isset($session[self::SESSION_KEY]);
    }

    public function user(): ?array
    {
        $session =& $_SESSION;
        return $session[self::SESSION_KEY] ?? null;
    }

    public function logout(): void
    {
        $session =& $_SESSION;
        unset($session[self::SESSION_KEY]);
    }

    public function isAdmin(): bool
    {
        $user = $this->user();
        return $user !== null && $user['role'] === 'admin';
    }
}
