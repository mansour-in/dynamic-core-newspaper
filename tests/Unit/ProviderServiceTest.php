<?php

declare(strict_types=1);

namespace CoreNewspaper\Tests\Unit;

use CoreNewspaper\Services\ProviderService;
use PHPUnit\Framework\TestCase;

final class ProviderServiceTest extends TestCase
{
    private ProviderService $service;

    protected function setUp(): void
    {
        $this->service = new ProviderService();
    }

    public function testDateUrlComputation(): void
    {
        $provider = [
            'pattern_type' => 'date',
            'pattern_template' => 'https://example.com/{YYYY}/{MM}/{DD}',
            'current_issue' => '2024-06-15',
        ];

        $url = $this->service->computeUrl($provider);
        self::assertSame('https://example.com/2024/06/15', $url);
    }

    public function testSequenceUrlComputation(): void
    {
        $provider = [
            'pattern_type' => 'sequence',
            'pattern_template' => 'https://example.com/{ISSUE_ID}/index.html',
            'current_issue' => '50314',
        ];

        $url = $this->service->computeUrl($provider);
        self::assertSame('https://example.com/50314/index.html', $url);
    }

    public function testMonthlyUrlComputation(): void
    {
        $provider = [
            'pattern_type' => 'monthly',
            'pattern_template' => 'https://example.com/{MM_slug}-{YYYY}',
            'current_issue' => 'november-2025',
        ];

        $url = $this->service->computeUrl($provider);
        self::assertSame('https://example.com/november-2025', $url);
    }

    public function testMonthlyWithoutCurrentIssueUsesNow(): void
    {
        $provider = [
            'pattern_type' => 'monthly',
            'pattern_template' => 'https://example.com/{MM_slug}-{YYYY}',
            'current_issue' => null,
        ];

        $url = $this->service->computeUrl($provider);
        self::assertNotEmpty($url);
        self::assertStringContainsString('-', $url);
    }
}
