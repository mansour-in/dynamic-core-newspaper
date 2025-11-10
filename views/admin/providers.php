<?php

declare(strict_types=1);
/** @var array $providers */
/** @var array $user */
/** @var string $csrfToken */
/** @var array $flashes */
ob_start();
?>
<h1>Providers</h1>
<?php if (!empty($flashes['success'])): ?>
    <?php foreach ($flashes['success'] as $message): ?>
        <div class="alert alert-success"><?= htmlspecialchars($message) ?></div>
    <?php endforeach; ?>
<?php endif; ?>
<table class="table">
    <thead>
        <tr>
            <th>Name</th>
            <th>Slug</th>
            <th>Pattern</th>
            <th>Current Issue</th>
            <th>Last URL</th>
            <th>Last Updated</th>
            <th>Status</th>
            <th>Actions</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($providers as $provider): ?>
        <tr>
            <td><?= htmlspecialchars($provider['name']) ?></td>
            <td><?= htmlspecialchars($provider['slug']) ?></td>
            <td><?= htmlspecialchars($provider['pattern_type']) ?></td>
            <td><?= htmlspecialchars((string)$provider['current_issue']) ?></td>
            <td class="truncate" title="<?= htmlspecialchars((string)$provider['last_issue_url']) ?>">
                <?= htmlspecialchars((string)$provider['last_issue_url']) ?>
            </td>
            <td><?= htmlspecialchars((string)$provider['last_updated_at']) ?></td>
            <td>
                <span class="badge badge-<?= htmlspecialchars($provider['cron_status'] ?? 'pending') ?>">
                    <?= htmlspecialchars($provider['cron_status'] ?? 'pending') ?>
                </span>
            </td>
            <td>
                <a href="/admin/providers/<?= (int)$provider['id'] ?>" class="btn-link">Edit</a>
            </td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
