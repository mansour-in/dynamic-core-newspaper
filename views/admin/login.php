<?php

declare(strict_types=1);
/** @var string $csrfToken */
/** @var string|null $error */
?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Admin Login - CORE Newspaper Redirector</title>
    <link rel="stylesheet" href="/assets/css/admin.css">
</head>
<body class="login-body">
    <div class="login-card">
        <h1>Admin Login</h1>
        <?php if (!empty($error)): ?>
            <div class="alert alert-error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>
        <form method="post" action="/admin/login">
            <input type="hidden" name="_csrf_token" value="<?= htmlspecialchars($csrfToken) ?>">
            <div class="form-group">
                <label for="email">Email</label>
                <input type="email" id="email" name="email" required autocomplete="username">
            </div>
            <div class="form-group">
                <label for="password">Password</label>
                <input type="password" id="password" name="password" required minlength="12" autocomplete="current-password">
            </div>
            <button type="submit" class="btn-primary">Sign in</button>
        </form>
    </div>
</body>
</html>
