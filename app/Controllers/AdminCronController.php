<?php

declare(strict_types=1);

namespace CoreNewspaper\Controllers;

use CoreNewspaper\Core\Request;
use CoreNewspaper\Repositories\CronRepository;
use CoreNewspaper\Security\Csrf;
use CoreNewspaper\Services\AuthService;
use DateTimeImmutable;
use DateTimeZone;

final class AdminCronController extends Controller
{
    private DateTimeZone $timezone;

    public function __construct(
        private readonly AuthService $authService,
        private readonly CronRepository $cronRepository,
        private readonly Csrf $csrf
    ) {
        $this->timezone = new DateTimeZone('Asia/Riyadh');
    }

    public function index(Request $request): void
    {
        if (!$this->authService->check()) {
            header('Location: /admin/login');
            return;
        }

        $page = max(1, (int)$request->get('page', 1));
        $perPage = 20;
        $offset = ($page - 1) * $perPage;
        $runs = $this->cronRepository->paginate($perPage, $offset);
        $total = $this->cronRepository->count();
        $gapWarning = false;
        foreach ($runs as $run) {
            if (($run['status'] ?? '') === 'success' && !empty($run['ended_at'])) {
                $lastTime = new DateTimeImmutable($run['ended_at'], $this->timezone);
                $now = new DateTimeImmutable('now', $this->timezone);
                $gapWarning = $now->getTimestamp() - $lastTime->getTimestamp() > 86400;
                break;
            }
        }

        $csrfToken = $this->csrf->generateToken($request);
        $this->render('admin/cron_history', [
            'runs' => $runs,
            'total' => $total,
            'page' => $page,
            'perPage' => $perPage,
            'gapWarning' => $gapWarning,
            'user' => $this->authService->user(),
            'csrfToken' => $csrfToken,
        ])->send();
    }
}
