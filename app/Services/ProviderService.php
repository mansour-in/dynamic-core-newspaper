<?php

declare(strict_types=1);

namespace CoreNewspaper\Services;

use DateTimeImmutable;
use DateTimeZone;
use InvalidArgumentException;

final class ProviderService
{
    private DateTimeZone $timezone;

    public function __construct()
    {
        $this->timezone = new DateTimeZone('Asia/Riyadh');
    }

    public function computeUrl(array $provider): string
    {
        return match ($provider['pattern_type']) {
            'date' => $this->computeDateUrl($provider),
            'sequence' => $this->computeSequenceUrl($provider),
            'monthly' => $this->computeMonthlyUrl($provider),
            default => throw new InvalidArgumentException('Unsupported pattern type'),
        };
    }

    public function updateDateProvider(array $provider): array
    {
        $today = new DateTimeImmutable('now', $this->timezone);
        $currentIssue = $today->format('Y-m-d');
        $url = $this->replacePlaceholders($provider['pattern_template'], [
            'YYYY' => $today->format('Y'),
            'MM' => $today->format('m'),
            'DD' => $today->format('d'),
        ]);

        return [
            'current_issue' => $currentIssue,
            'last_issue_url' => $url,
        ];
    }

    public function updateMonthlyProvider(array $provider): array
    {
        $today = new DateTimeImmutable('now', $this->timezone);
        $monthSlug = strtolower($today->format('F'));
        $currentIssue = $monthSlug . '-' . $today->format('Y');
        $url = $this->replacePlaceholders($provider['pattern_template'], [
            'MM_slug' => $monthSlug,
            'YYYY' => $today->format('Y'),
        ]);

        return [
            'current_issue' => $currentIssue,
            'last_issue_url' => $url,
        ];
    }

    public function updateSequenceProvider(array $provider, string $strategy = 'auto'): array
    {
        $current = $provider['current_issue'] ?? '0';
        if ($strategy === 'manual') {
            $issueId = $current;
        } else {
            $issueId = (string)((int)$current + 1);
        }

        $url = $this->replacePlaceholders($provider['pattern_template'], [
            'ISSUE_ID' => $issueId,
        ]);

        return [
            'current_issue' => $issueId,
            'last_issue_url' => $url,
        ];
    }

    public function replacePlaceholders(string $template, array $vars): string
    {
        $search = [];
        $replace = [];
        foreach ($vars as $key => $value) {
            $search[] = '{' . $key . '}';
            $replace[] = $value;
        }
        return str_replace($search, $replace, $template);
    }

    private function computeDateUrl(array $provider): string
    {
        $issue = $provider['current_issue'] ?? '';
        $date = DateTimeImmutable::createFromFormat('Y-m-d', $issue, $this->timezone) ?: new DateTimeImmutable('now', $this->timezone);
        return $this->replacePlaceholders($provider['pattern_template'], [
            'YYYY' => $date->format('Y'),
            'MM' => $date->format('m'),
            'DD' => $date->format('d'),
        ]);
    }

    private function computeSequenceUrl(array $provider): string
    {
        $issue = $provider['current_issue'];
        if ($issue === null || $issue === '') {
            throw new InvalidArgumentException('Sequence provider requires current issue');
        }

        return $this->replacePlaceholders($provider['pattern_template'], [
            'ISSUE_ID' => $issue,
        ]);
    }

    private function computeMonthlyUrl(array $provider): string
    {
        $issue = $provider['current_issue'];
        if ($issue === null || $issue === '') {
            $now = new DateTimeImmutable('now', $this->timezone);
            $issue = strtolower($now->format('F')) . '-' . $now->format('Y');
        }

        $parts = explode('-', $issue);
        if (count($parts) < 2) {
            throw new InvalidArgumentException('Monthly provider issue format invalid');
        }
        [$monthSlug, $year] = $parts;
        return $this->replacePlaceholders($provider['pattern_template'], [
            'MM_slug' => $monthSlug,
            'YYYY' => $year,
        ]);
    }
}
