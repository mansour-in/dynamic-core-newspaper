<?php

declare(strict_types=1);
/** @var array $runs */
/** @var int $total */
/** @var int $page */
/** @var int $perPage */
/** @var bool $gapWarning */
/** @var array $user */
ob_start();
?>
<h1>Cron History</h1>
<?php if ($gapWarning): ?>
    <div class="alert alert-error">Warning: More than 24 hours since the last successful cron run.</div>
<?php endif; ?>
<table class="table">
    <thead>
        <tr>
            <th>Started</th>
            <th>Ended</th>
            <th>Status</th>
            <th>Checked</th>
            <th>Updated</th>
            <th>Duration (ms)</th>
            <th>Message</th>
        </tr>
    </thead>
    <tbody>
    <?php foreach ($runs as $run): ?>
        <tr>
            <td><?= htmlspecialchars((string)$run['started_at']) ?></td>
            <td><?= htmlspecialchars((string)$run['ended_at']) ?></td>
            <td><span class="badge badge-<?= htmlspecialchars($run['status']) ?>"><?= htmlspecialchars($run['status']) ?></span></td>
            <td><?= (int)$run['providers_checked'] ?></td>
            <td><?= (int)$run['providers_updated'] ?></td>
            <td><?= htmlspecialchars((string)$run['duration_ms']) ?></td>
            <td><?= htmlspecialchars((string)$run['message']) ?></td>
        </tr>
    <?php endforeach; ?>
    </tbody>
</table>
<?php
$totalPages = (int)ceil($total / $perPage);
if ($totalPages > 1):
?>
<nav class="pagination">
    <?php for ($i = 1; $i <= $totalPages; $i++): ?>
        <a href="/admin/cron-history?page=<?= $i ?>" class="<?= $i === $page ? 'active' : '' ?>"><?= $i ?></a>
    <?php endfor; ?>
</nav>
<?php endif; ?>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
