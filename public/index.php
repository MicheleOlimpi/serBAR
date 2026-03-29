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

$configPath = dirname(__DIR__) . '/config/app.php';
$config = Config::load($configPath);
$action = $_GET['action'] ?? '';

if ($action === 'install') {
    (new InstallController(new InstallerService(), $configPath))->handle();
    exit;
}

if ($config === null) {
    header('Location: ?action=install');
    exit;
}

if (!Database::canConnect($config)) {
    header('Location: ?action=install');
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
    'weekday_close' => 'weekdayClose',
    'notifications' => 'notifications',
    'setup' => 'setup',
    'information' => 'information',
    'segnalazione' => 'segnalazione',
    'lista_volontari' => 'listaVolontari',
    'license' => 'license',
    'panel' => 'panel',
];

$method = $routes[$action] ?? 'dashboard';
$app->$method();
