<?php

declare(strict_types=1);

namespace CoreNewspaper\Repositories;

use CoreNewspaper\Core\Database;
use PDO;

final class ProviderRepository
{
    public function __construct(private readonly Database $database)
    {
    }

    public function findActiveBySlug(string $slug): ?array
    {
        $stmt = $this->database->connection()->prepare('SELECT * FROM providers WHERE slug = :slug AND is_active = 1 LIMIT 1');
        $stmt->execute(['slug' => $slug]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function findAll(): array
    {
        $stmt = $this->database->connection()->query('SELECT * FROM providers ORDER BY name');
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    public function findById(int $id): ?array
    {
        $stmt = $this->database->connection()->prepare('SELECT * FROM providers WHERE id = :id');
        $stmt->execute(['id' => $id]);
        $result = $stmt->fetch(PDO::FETCH_ASSOC);
        return $result ?: null;
    }

    public function updateProvider(int $id, array $fields): void
    {
        $columns = [];
        $params = ['id' => $id];
        foreach ($fields as $key => $value) {
            $columns[] = $key . ' = :' . $key;
            $params[$key] = $value;
        }

        $sql = 'UPDATE providers SET ' . implode(', ', $columns) . ' WHERE id = :id';
        $stmt = $this->database->connection()->prepare($sql);
        $stmt->execute($params);
    }

    public function logProviderChange(array $data): void
    {
        $stmt = $this->database->connection()->prepare(
            'INSERT INTO provider_changes (provider_id, changed_by, old_issue, new_issue, old_url, new_url, changed_at) '
            . 'VALUES (:provider_id, :changed_by, :old_issue, :new_issue, :old_url, :new_url, :changed_at)'
        );
        $stmt->execute($data);
    }
}
