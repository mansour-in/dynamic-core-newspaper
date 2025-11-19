<?php

declare(strict_types=1);

namespace App;

use DateTimeImmutable;
use InvalidArgumentException;

class Redirector
{
    public static function build(array $provider, DateTimeImmutable $now): string
    {
        if (!isset($provider['pattern_type'], $provider['pattern_template'])) {
            throw new InvalidArgumentException('Provider missing pattern configuration.');
        }

        $patternType = $provider['pattern_type'];
        $template = $provider['pattern_template'];

        return match ($patternType) {
            'date' => self::applyDatePattern($template, $now),
            'sequence' => self::applySequencePattern($template, $provider),
            'monthly' => self::applyMonthlyPattern($template, $now),
            default => throw new InvalidArgumentException('Unsupported pattern type: ' . $patternType),
        };
    }

    private static function applyDatePattern(string $template, DateTimeImmutable $now): string
    {
        return strtr($template, [
            '{YYYY}' => $now->format('Y'),
            '{MM}' => $now->format('m'),
            '{DD}' => $now->format('d'),
        ]);
    }

    private static function applySequencePattern(string $template, array $provider): string
    {
        $issueId = $provider['current_issue'] ?? null;
        if ($issueId === null || $issueId === '') {
            throw new InvalidArgumentException('Sequence provider missing current_issue value.');
        }

        return strtr($template, [
            '{ISSUE_ID}' => (string) $issueId,
        ]);
    }

    private static function applyMonthlyPattern(string $template, DateTimeImmutable $now): string
    {
        return strtr($template, [
            '{MM_slug}' => strtolower($now->format('F')),
            '{YYYY}' => $now->format('Y'),
        ]);
    }
}
