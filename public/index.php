<?php

declare(strict_types=1);

use App\Controllers\AppController;
use App\Controllers\InstallController;
use App\Core\Config;
use App\Core\Database;
use App\Core\View;
use App\Repositories\BarRepository;
use App\Services\BoardService;
use App\Services\InstallerService;

require __DIR__ . '/../vendor/autoload.php';
session_start();

$configPath = __DIR__ . '/app.php';
$config = Config::load($configPath);
$action = $_GET['action'] ?? '';

if ($action === 'install') {
    (new InstallController(new InstallerService(), $configPath))->handle();
    exit;
}

if ($config === null) {
    View::render('install/index', ['error' => 'Database non configurato. Avviare installazione.', 'defaults' => ['host' => '127.0.0.1', 'database' => 'servizioBAR', 'username' => 'root', 'password' => '', 'port' => 3307]]);
    exit;
}

if (!Database::canConnect($config)) {
    View::render('install/index', ['error' => 'Database non raggiungibile o non esistente. Procedere con installazione.', 'defaults' => $config]);
    exit;
}

$db = new Database($config);
$repo = new BarRepository($db->pdo());
$app = new AppController($repo, new BoardService($db->pdo()));

$routes = [
    '' => 'dashboard',
    'login' => 'login',
    'logout' => 'logout',
    'boards' => 'boards',
    'board_edit' => 'boardEdit',
    'users' => 'users',
    'day_types' => 'dayTypes',
    'shift_config' => 'shiftConfig',
    'calendar' => 'calendar',
];

$method = $routes[$action] ?? 'dashboard';
$app->$method();
