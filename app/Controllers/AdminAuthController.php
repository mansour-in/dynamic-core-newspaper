<?php

declare(strict_types=1);

namespace CoreNewspaper\Controllers;

use CoreNewspaper\Core\Request;
use CoreNewspaper\Security\Csrf;
use CoreNewspaper\Security\LoginRateLimiter;
use CoreNewspaper\Security\SessionManager;
use CoreNewspaper\Services\AuthService;
use CoreNewspaper\Services\Logger;

final class AdminAuthController extends Controller
{
    public function __construct(
        private readonly AuthService $authService,
        private readonly Csrf $csrf,
        private readonly SessionManager $sessionManager,
        private readonly LoginRateLimiter $rateLimiter,
        private readonly Logger $logger
    ) {
    }

    public function showLogin(Request $request): void
    {
        if ($this->authService->check()) {
            header('Location: /admin/providers');
            return;
        }

        $token = $this->csrf->generateToken($request);
        $this->render('admin/login', [
            'csrfToken' => $token,
            'error' => null,
        ])->send();
    }

    public function login(Request $request): void
    {
        if (!$this->csrf->validate($request)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $email = trim((string)$request->post('email'));
        $password = (string)$request->post('password');

        if ($this->rateLimiter->tooManyAttempts($request, $email)) {
            $this->logger->log('warning', 'Too many login attempts for {email}', ['email' => $email]);
            $this->render('admin/login', [
                'csrfToken' => $this->csrf->generateToken($request),
                'error' => 'Too many attempts. Please wait before trying again.',
            ])->send();
            return;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL) || strlen($password) < 12) {
            $this->rateLimiter->addAttempt($request, $email);
            $this->render('admin/login', [
                'csrfToken' => $this->csrf->generateToken($request),
                'error' => 'Invalid credentials.',
            ])->send();
            return;
        }

        if ($this->authService->attempt($email, $password)) {
            $this->sessionManager->regenerate();
            $this->rateLimiter->clear($request, $email);
            $this->logger->log('info', 'Admin user {email} logged in', ['email' => $email]);
            header('Location: /admin/providers');
            return;
        }

        $this->rateLimiter->addAttempt($request, $email);
        $this->logger->log('warning', 'Failed login for {email}', ['email' => $email]);
        $this->render('admin/login', [
            'csrfToken' => $this->csrf->generateToken($request),
            'error' => 'Invalid credentials.',
        ])->send();
    }

    public function logout(Request $request): void
    {
        if (!$this->csrf->validate($request)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $user = $this->authService->user();
        $this->authService->logout();
        $this->sessionManager->destroy();
        $this->logger->log('info', 'Admin user {email} logged out', ['email' => $user['email'] ?? 'unknown']);
        header('Location: /admin/login');
        return;
    }
}
