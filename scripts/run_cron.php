<?php

declare(strict_types=1);

use CoreNewspaper\Core\Config;
use CoreNewspaper\Core\Database;
use CoreNewspaper\Core\Env;
use CoreNewspaper\Repositories\CronRepository;
use CoreNewspaper\Repositories\ProviderRepository;
use CoreNewspaper\Services\CronService;
use CoreNewspaper\Services\Logger;
use CoreNewspaper\Services\ProviderService;

require_once __DIR__ . '/../autoload.php';

Env::load(__DIR__ . '/../.env');
$config = new Config();
date_default_timezone_set($config->get('app.timezone', 'Asia/Riyadh'));

$database = new Database($config);
$providerRepository = new ProviderRepository($database);
$cronRepository = new CronRepository($database);
$providerService = new ProviderService();
$appLogger = new Logger($config->get('log.app'));
$cronLogger = new Logger($config->get('log.cron'));
$cronService = new CronService($providerRepository, $cronRepository, $providerService, $config, $appLogger, $cronLogger);

$cronService->run();
