<?php

declare(strict_types=1);

namespace CoreNewspaper\Security;

use CoreNewspaper\Core\Request;

final class LoginRateLimiter
{
    private const LIMIT_PER_IP = 10;
    private const LIMIT_PER_ACCOUNT = 5;
    private const WINDOW_SECONDS = 900;

    public function tooManyAttempts(Request $request, string $email): bool
    {
        $session =& $request->session();
        $now = time();

        $ipKey = 'login_attempts_ip';
        $accountKey = 'login_attempts_account';

        $session[$ipKey] = $session[$ipKey] ?? [];
        $session[$accountKey] = $session[$accountKey] ?? [];

        $ip = $request->ip();
        $session[$ipKey][$ip] = array_filter(
            $session[$ipKey][$ip] ?? [],
            static fn(int $timestamp): bool => ($now - $timestamp) < self::WINDOW_SECONDS
        );

        $session[$accountKey][$email] = array_filter(
            $session[$accountKey][$email] ?? [],
            static fn(int $timestamp): bool => ($now - $timestamp) < self::WINDOW_SECONDS
        );

        return count($session[$ipKey][$ip]) >= self::LIMIT_PER_IP
            || count($session[$accountKey][$email]) >= self::LIMIT_PER_ACCOUNT;
    }

    public function addAttempt(Request $request, string $email): void
    {
        $session =& $request->session();
        $now = time();
        $ip = $request->ip();
        $session['login_attempts_ip'][$ip] = $session['login_attempts_ip'][$ip] ?? [];
        $session['login_attempts_ip'][$ip][] = $now;
        $session['login_attempts_account'][$email] = $session['login_attempts_account'][$email] ?? [];
        $session['login_attempts_account'][$email][] = $now;
    }

    public function clear(Request $request, string $email): void
    {
        $session =& $request->session();
        unset($session['login_attempts_account'][$email]);
    }
}
