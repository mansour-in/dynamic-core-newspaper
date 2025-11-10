<?php

declare(strict_types=1);

namespace CoreNewspaper\Services;

use CoreNewspaper\Core\Config;
use CoreNewspaper\Repositories\CronRepository;
use CoreNewspaper\Repositories\ProviderRepository;
use DateTimeImmutable;
use DateTimeZone;
use Throwable;

final class CronService
{
    private DateTimeZone $timezone;

    public function __construct(
        private readonly ProviderRepository $providers,
        private readonly CronRepository $cronRuns,
        private readonly ProviderService $providerService,
        private readonly Config $config,
        private readonly Logger $appLogger,
        private readonly Logger $cronLogger
    ) {
        $this->timezone = new DateTimeZone('Asia/Riyadh');
    }

    public function run(): void
    {
        $start = microtime(true);
        $now = new DateTimeImmutable('now', $this->timezone);
        $runId = $this->cronRuns->createRun([
            'started_at' => $now->format('Y-m-d H:i:s'),
            'status' => 'fail',
            'providers_checked' => 0,
            'providers_updated' => 0,
            'message' => null,
        ]);

        $providers = $this->providers->findAll();
        $checked = 0;
        $updated = 0;
        $errors = [];
        foreach ($providers as $provider) {
            if (!(int)$provider['is_active']) {
                continue;
            }
            ++$checked;
            try {
                $result = $this->processProvider($provider);
                if ($result['updated']) {
                    ++$updated;
                }
            } catch (Throwable $e) {
                $errors[] = sprintf('%s: %s', $provider['slug'], $e->getMessage());
                $this->providers->updateProvider((int)$provider['id'], [
                    'cron_status' => 'fail',
                    'cron_last_run_at' => (new DateTimeImmutable('now', $this->timezone))->format('Y-m-d H:i:s'),
                ]);
                $this->appLogger->log('error', 'Cron provider failure {slug}: {error}', [
                    'slug' => $provider['slug'],
                    'error' => $e->getMessage(),
                ]);
            }
        }

        $status = 'success';
        if ($errors !== []) {
            $status = $updated > 0 ? 'partial' : 'fail';
        }

        $durationMs = (int)round((microtime(true) - $start) * 1000);
        $this->cronRuns->updateRun($runId, [
            'status' => $status,
            'providers_checked' => $checked,
            'providers_updated' => $updated,
            'message' => $errors === [] ? null : implode('; ', $errors),
            'ended_at' => (new DateTimeImmutable('now', $this->timezone))->format('Y-m-d H:i:s'),
            'duration_ms' => $durationMs,
        ]);

        $this->cronLogger->log('info', 'Cron run completed status={status} checked={checked} updated={updated} duration={duration}ms', [
            'status' => $status,
            'checked' => $checked,
            'updated' => $updated,
            'duration' => $durationMs,
        ]);
    }

    private function processProvider(array $provider): array
    {
        $updates = [];
        $strategy = $this->config->get('sequence.strategy', 'auto');
        switch ($provider['pattern_type']) {
            case 'date':
                $updates = $this->providerService->updateDateProvider($provider);
                break;
            case 'monthly':
                $updates = $this->providerService->updateMonthlyProvider($provider);
                break;
            case 'sequence':
                $updates = $this->providerService->updateSequenceProvider($provider, $strategy);
                if ($this->config->get('probe.enabled', false)) {
                    $candidateUrl = $updates['last_issue_url'];
                    if (!$this->headRequestSucceeds($candidateUrl)) {
                        $updates = $this->providerService->updateSequenceProvider($provider, 'manual');
                    }
                }
                break;
            default:
                throw new \RuntimeException('Unknown pattern type');
        }

        $now = new DateTimeImmutable('now', $this->timezone);
        $updates['last_updated_at'] = $now->format('Y-m-d H:i:s');
        $updates['cron_last_run_at'] = $now->format('Y-m-d H:i:s');
        $updates['cron_status'] = 'success';
        $this->providers->updateProvider((int)$provider['id'], $updates);

        return ['updated' => true];
}

    private function headRequestSucceeds(string $url): bool
    {
        $options = [
            'http' => [
                'method' => 'HEAD',
                'timeout' => 5,
                'ignore_errors' => true,
            ],
        ];
        $context = stream_context_create($options);
        $stream = @fopen($url, 'r', false, $context);
        if ($stream === false) {
            return false;
        }
        $meta = stream_get_meta_data($stream);
        fclose($stream);
        $headers = $meta['wrapper_data'] ?? [];
        if (!is_array($headers) || $headers === []) {
            return false;
        }

        $statusLine = $headers[0] ?? '';
        return str_contains($statusLine, '200') || str_contains($statusLine, '302');
    }
}
