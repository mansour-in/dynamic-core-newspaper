<?php

declare(strict_types=1);

namespace CoreNewspaper\Services;

use DateTimeImmutable;
use RuntimeException;

final class Logger
{
    public function __construct(private readonly string $filePath)
    {
    }

    public function log(string $level, string $message, array $context = []): void
    {
        $record = $this->interpolate($message, $context);
        $line = sprintf(
            "%s [%s] %s%s",
            (new DateTimeImmutable('now', new \DateTimeZone('Asia/Riyadh')))->format('Y-m-d H:i:s'),
            strtoupper($level),
            $record,
            PHP_EOL
        );
        $this->rotateIfNecessary();
        if (@file_put_contents($this->filePath, $line, FILE_APPEND | LOCK_EX) === false) {
            throw new RuntimeException('Unable to write to log file: ' . $this->filePath);
        }
    }

    private function rotateIfNecessary(): void
    {
        if (!file_exists($this->filePath)) {
            $dir = dirname($this->filePath);
            if (!is_dir($dir)) {
                mkdir($dir, 0770, true);
            }
            touch($this->filePath);
            return;
        }

        $maxSize = 1024 * 1024; // 1MB
        if (filesize($this->filePath) >= $maxSize) {
            $backupName = $this->filePath . '.' . (new DateTimeImmutable())->format('YmdHis');
            rename($this->filePath, $backupName);
            touch($this->filePath);
        }
    }

    private function interpolate(string $message, array $context): string
    {
        $replace = [];
        foreach ($context as $key => $value) {
            $replace['{' . $key . '}'] = is_scalar($value) ? (string)$value : json_encode($value, JSON_UNESCAPED_UNICODE);
        }

        return strtr($message, $replace);
    }
}
