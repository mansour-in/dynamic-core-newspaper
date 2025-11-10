<?php

declare(strict_types=1);

namespace CoreNewspaper\Repositories;

use CoreNewspaper\Core\Database;
use PDO;

final class CronRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function createRun(array $data): int
    {
        $stmt = $this->database->connection()->prepare(
            'INSERT INTO cron_runs (started_at, status, providers_checked, providers_updated, message) '
            . 'VALUES (:started_at, :status, :providers_checked, :providers_updated, :message)'
        );
        $stmt->execute($data);
        return (int)$this->database->connection()->lastInsertId();
    }

    public function updateRun(int $id, array $fields): void
    {
        $columns = [];
        $params = ['id' => $id];
        foreach ($fields as $key => $value) {
            $columns[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE cron_runs SET ' . implode(', ', $columns) . ' WHERE id = :id';
        $stmt = $this->database->connection()->prepare($sql);
        $stmt->execute($params);
    }

    public function paginate(int $limit, int $offset): array
    {
        $stmt = $this->database->connection()->prepare('SELECT * FROM cron_runs ORDER BY started_at DESC LIMIT :limit OFFSET :offset');
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function count(): int
    {
        $stmt = $this->database->connection()->query('SELECT COUNT(*) AS total FROM cron_runs');
        $row = $stmt->fetch(PDO::FETCH_ASSOC);
        return (int)($row['total'] ?? 0);
    }
}
