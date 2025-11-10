<?php

declare(strict_types=1);
/** @var array $provider */
/** @var array $user */
/** @var string $csrfToken */
/** @var array $errors */
$errors = $errors ?? [];
ob_start();
?>
<h1>Edit Provider: <?= htmlspecialchars($provider['name']) ?></h1>
<?php if ($errors !== []): ?>
    <div class="alert alert-error">
        <ul>
            <?php foreach ($errors as $error): ?>
                <li><?= htmlspecialchars($error) ?></li>
            <?php endforeach; ?>
        </ul>
    </div>
<?php endif; ?>
<form method="post" action="/admin/providers/<?= (int)$provider['id'] ?>" class="form-card">
    <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
    <div class="form-group">
        <label for="pattern_template">Pattern Template</label>
        <input type="text" id="pattern_template" name="pattern_template" value="<?= htmlspecialchars($provider['pattern_template']) ?>" required>
    </div>
    <div class="form-group">
        <label for="current_issue">Current Issue</label>
        <input type="text" id="current_issue" name="current_issue" value="<?= htmlspecialchars((string)$provider['current_issue']) ?>" placeholder="Depends on pattern type">
    </div>
    <div class="form-group">
        <label for="is_active">Active</label>
        <select id="is_active" name="is_active">
            <option value="1" <?= (int)$provider['is_active'] === 1 ? 'selected' : '' ?>>Yes</option>
            <option value="0" <?= (int)$provider['is_active'] === 0 ? 'selected' : '' ?>>No</option>
        </select>
    </div>
    <div class="form-group">
        <label for="notes">Notes</label>
        <textarea id="notes" name="notes" rows="4"><?= htmlspecialchars((string)$provider['notes']) ?></textarea>
    </div>
    <button type="submit" class="btn-primary">Save Changes</button>
    <a href="/admin/providers" class="btn-secondary">Cancel</a>
</form>
<?php
$content = ob_get_clean();
include __DIR__ . '/layout.php';
