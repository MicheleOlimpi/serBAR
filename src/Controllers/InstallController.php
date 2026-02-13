<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\View;
use App\Services\InstallerService;

class InstallController
{
    public function __construct(private InstallerService $installer, private string $configPath)
    {
    }

    public function handle(): void
    {
        $defaults = [
            'host' => '127.0.0.1',
            'database' => 'servizioBAR',
            'username' => 'root',
            'password' => '',
            'port' => 3307,
        ];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cfg = [
                'host' => trim($_POST['host'] ?? $defaults['host']),
                'database' => trim($_POST['database'] ?? $defaults['database']),
                'username' => trim($_POST['username'] ?? $defaults['username']),
                'password' => $_POST['password'] ?? '',
                'port' => (int) ($_POST['port'] ?? $defaults['port']),
            ];

            try {
                $this->installer->install($cfg);
                Config::save($this->configPath, $cfg);
                View::redirect('/?action=login');
            } catch (\Throwable $e) {
                View::render('install/index', ['error' => $e->getMessage(), 'defaults' => $cfg]);
                return;
            }
        }

        View::render('install/index', ['defaults' => $defaults]);
    }
}
