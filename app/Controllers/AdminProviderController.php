<?php

declare(strict_types=1);

namespace CoreNewspaper\Controllers;

use CoreNewspaper\Core\Request;
use CoreNewspaper\Repositories\ProviderRepository;
use CoreNewspaper\Security\Csrf;
use CoreNewspaper\Services\AuthService;
use CoreNewspaper\Services\FlashService;
use CoreNewspaper\Services\Logger;
use CoreNewspaper\Services\ProviderService;
use DateTimeImmutable;
use DateTimeZone;

final class AdminProviderController extends Controller
{
    private FlashService $flash;
    private DateTimeZone $timezone;

    public function __construct(
        private readonly AuthService $authService,
        private readonly ProviderRepository $providers,
        private readonly ProviderService $providerService,
        private readonly Csrf $csrf,
        private readonly Logger $logger
    ) {
        $this->flash = new FlashService();
        $this->timezone = new DateTimeZone('Asia/Riyadh');
    }

    public function index(Request $request): void
    {
        if (!$this->ensureAuthenticated()) {
            header('Location: /admin/login');
            return;
        }

        $providers = $this->providers->findAll();
        $flashes = $this->flash->consume($request);
        $this->render('admin/providers', [
            'providers' => $providers,
            'user' => $this->authService->user(),
            'csrfToken' => $this->csrf->generateToken($request),
            'flashes' => $flashes,
        ])->send();
    }

    public function edit(Request $request, string $id): void
    {
        if (!$this->ensureAuthenticated(true)) {
            header('Location: /admin/login');
            return;
        }

        $provider = $this->providers->findById((int)$id);
        if ($provider === null) {
            http_response_code(404);
            echo 'Provider not found';
            return;
        }

        $this->render('admin/provider_edit', [
            'provider' => $provider,
            'user' => $this->authService->user(),
            'csrfToken' => $this->csrf->generateToken($request),
        ])->send();
    }

    public function update(Request $request, string $id): void
    {
        if (!$this->ensureAuthenticated(true)) {
            header('Location: /admin/login');
            return;
        }

        if (!$this->csrf->validate($request)) {
            http_response_code(400);
            echo 'Invalid CSRF token';
            return;
        }

        $provider = $this->providers->findById((int)$id);
        if ($provider === null) {
            http_response_code(404);
            echo 'Provider not found';
            return;
        }

        $currentIssue = trim((string)$request->post('current_issue'));
        $patternTemplate = trim((string)$request->post('pattern_template'));
        $isActive = $request->post('is_active') === '1' ? 1 : 0;
        $notes = trim((string)$request->post('notes'));

        $errors = $this->validateProviderInput($provider['pattern_type'], $currentIssue, $patternTemplate);
        if ($errors !== []) {
            $this->render('admin/provider_edit', [
                'provider' => array_merge($provider, [
                    'current_issue' => $currentIssue,
                    'pattern_template' => $patternTemplate,
                    'is_active' => $isActive,
                    'notes' => $notes,
                ]),
                'user' => $this->authService->user(),
                'csrfToken' => $this->csrf->generateToken($request),
                'errors' => $errors,
            ])->send();
            return;
        }

        $updates = [
            'current_issue' => $currentIssue !== '' ? $currentIssue : null,
            'pattern_template' => $patternTemplate,
            'is_active' => $isActive,
            'notes' => $notes !== '' ? $notes : null,
            'last_updated_at' => (new DateTimeImmutable('now', $this->timezone))->format('Y-m-d H:i:s'),
        ];

        $oldIssue = $provider['current_issue'];
        $oldUrl = $provider['last_issue_url'];

        $this->providers->updateProvider((int)$provider['id'], $updates);
        $newUrl = $this->providerService->computeUrl(array_merge($provider, $updates));
        $this->providers->logProviderChange([
            'provider_id' => (int)$provider['id'],
            'changed_by' => (int)$this->authService->user()['id'],
            'old_issue' => $oldIssue,
            'new_issue' => $updates['current_issue'],
            'old_url' => $oldUrl,
            'new_url' => $newUrl,
            'changed_at' => (new DateTimeImmutable('now', $this->timezone))->format('Y-m-d H:i:s'),
        ]);
        $this->providers->updateProvider((int)$provider['id'], [
            'last_issue_url' => $newUrl,
            'cron_status' => 'success',
        ]);

        $this->logger->log('info', 'Provider {slug} updated manually by {user}', [
            'slug' => $provider['slug'],
            'user' => $this->authService->user()['email'] ?? 'unknown',
        ]);

        $this->flash->add($request, 'success', 'Provider updated successfully.');
        header('Location: /admin/providers');
        return;
    }

    private function ensureAuthenticated(bool $requireAdmin = false): bool
    {
        if (!$this->authService->check()) {
            return false;
        }

        if ($requireAdmin && !$this->authService->isAdmin()) {
            http_response_code(403);
            echo 'Forbidden';
            return false;
        }

        return true;
    }

    private function validateProviderInput(string $patternType, string $currentIssue, string $patternTemplate): array
    {
        $errors = [];
        if ($patternTemplate === '') {
            $errors[] = 'Pattern template is required.';
        }

        if ($patternType === 'sequence' && ($currentIssue === '' || !ctype_digit($currentIssue))) {
            $errors[] = 'Current issue must be a numeric value for sequence providers.';
        }

        if ($patternType === 'date' && $currentIssue !== '' && !preg_match('/^\\d{4}-\\d{2}-\\d{2}$/', $currentIssue)) {
            $errors[] = 'Date issues must be in YYYY-MM-DD format.';
        }

        if ($patternType === 'monthly' && $currentIssue !== '' && !preg_match('/^[a-z]+-\\d{4}$/', $currentIssue)) {
            $errors[] = 'Monthly issues must be in month-yyyy format (e.g., november-2025).';
        }

        return $errors;
    }
}
