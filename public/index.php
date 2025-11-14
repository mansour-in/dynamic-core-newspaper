<?php

declare(strict_types=1);

use CoreNewspaper\Controllers\AdminAuthController;
use CoreNewspaper\Controllers\AdminCronController;
use CoreNewspaper\Controllers\AdminProviderController;
use CoreNewspaper\Controllers\RedirectController;
use CoreNewspaper\Core\Config;
use CoreNewspaper\Core\Database;
use CoreNewspaper\Core\Env;
use CoreNewspaper\Core\Request;
use CoreNewspaper\Core\Router;
use CoreNewspaper\Repositories\CronRepository;
use CoreNewspaper\Repositories\ProviderRepository;
use CoreNewspaper\Security\Csrf;
use CoreNewspaper\Security\LoginRateLimiter;
use CoreNewspaper\Security\SessionManager;
use CoreNewspaper\Services\AuthService;
use CoreNewspaper\Services\Logger;
use CoreNewspaper\Services\ProviderService;
use CoreNewspaper\Services\CronService;
use RuntimeException;
use Throwable;

require_once __DIR__ . '/../autoload.php';

Env::load(__DIR__ . '/../.env');
$config = new Config();
$timezone = $config->get('app.timezone', 'Asia/Riyadh');
date_default_timezone_set($timezone);

$sessionManager = new SessionManager($config);
$sessionManager->start();

$request = Request::fromGlobals();

$hstsMaxAge = $config->get('security.hsts_max_age', 31536000);
header('Strict-Transport-Security: max-age=' . $hstsMaxAge . '; includeSubDomains');
header('X-Frame-Options: SAMEORIGIN');
header('X-Content-Type-Options: nosniff');

$database = new Database($config);
$providerRepository = new ProviderRepository($database);
$cronRepository = new CronRepository($database);
$providerService = new ProviderService();
$appLogger = new Logger($config->get('log.app'));
$cronLogger = new Logger($config->get('log.cron'));
$authService = new AuthService($database);
$csrf = new Csrf();
$rateLimiter = new LoginRateLimiter();
$cronService = new CronService($providerRepository, $cronRepository, $providerService, $config, $appLogger, $cronLogger);

$router = new Router();

$adminAuthController = new AdminAuthController($authService, $csrf, $sessionManager, $rateLimiter, $appLogger);
$adminProviderController = new AdminProviderController($authService, $providerRepository, $providerService, $csrf, $appLogger);
$adminCronController = new AdminCronController($authService, $cronRepository, $csrf);
$redirectController = new RedirectController($providerRepository, $providerService, $appLogger);

$router->get('/admin/login', [$adminAuthController, 'showLogin']);
$router->post('/admin/login', [$adminAuthController, 'login']);
$router->post('/admin/logout', [$adminAuthController, 'logout']);
$router->get('/admin/providers', [$adminProviderController, 'index']);
$router->get('/admin/providers/{id}', [$adminProviderController, 'edit']);
$router->post('/admin/providers/{id}', [$adminProviderController, 'update']);
$router->get('/admin/cron-history', [$adminCronController, 'index']);

$router->get('/{slug}', function ($request, $slug) use ($redirectController) {
    $redirectController->__invoke($slug);
});

try {
    $router->dispatch($request);
} catch (RuntimeException $e) {
    http_response_code(404);
    include __DIR__ . '/../views/errors/404.php';
} catch (Throwable $e) {
    http_response_code(500);
    include __DIR__ . '/../views/errors/500.php';
}
