<?php

declare(strict_types=1);
/** @var array $user */
/** @var string $title */
/** @var string $content */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title><?= htmlspecialchars($title) ?> - CORE Newspaper Admin</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body>
<header class="topbar">
    <div class="brand">CORE Newspaper Redirector</div>
    <?php if (!empty($user)): ?>
        <div class="user-info">
            <span><?= htmlspecialchars($user['email']) ?> (<?= htmlspecialchars($user['role']) ?>)</span>
            <form action="/admin/logout" method="post" class="logout-form">
                <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken ?? '') ?>">
                <button type="submit">Logout</button>
            </form>
        </div>
    <?php endif; ?>
</header>
<main class="container">
    <?= $content ?>
</main>
<footer class="footer">&copy; <?= date('Y') ?> CORE Newspaper Redirector</footer>
</body>
</html>
