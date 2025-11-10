<?php

declare(strict_types=1);

namespace CoreNewspaper\Controllers;

use CoreNewspaper\Repositories\ProviderRepository;
use CoreNewspaper\Services\Logger;
use CoreNewspaper\Services\ProviderService;

final class RedirectController extends Controller
{
    public function __construct(
        private readonly ProviderRepository $providers,
        private readonly ProviderService $providerService,
        private readonly Logger $logger
    ) {
    }

    public function __invoke(string $slug): void
    {
        $provider = $this->providers->findActiveBySlug($slug);
        if ($provider === null) {
            $this->logger->log('warning', 'Provider not found for slug {slug}', ['slug' => $slug]);
            http_response_code(404);
            echo 'Not Found';
            return;
        }

        try {
            $url = $this->providerService->computeUrl($provider);
        } catch (\Throwable $e) {
            $this->logger->log('error', 'Failed to compute URL for provider {slug}: {error}', [
                'slug' => $slug,
                'error' => $e->getMessage(),
            ]);
            http_response_code(500);
            echo 'Internal Server Error';
            return;
        }

        header('Location: ' . $url, true, 302);
    }
}
