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
        $defaults = $this->loadDefaultsFromConfig();

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
                $storedConfig = Config::load($this->configPath);
                if ($storedConfig !== $cfg) {
                    Config::save($this->configPath, $cfg);
                }

                View::render('install/complete', [
                    'db' => $cfg['database'],
                    'host' => $cfg['host'],
                    'port' => $cfg['port'],
                ]);
                return;
            } catch (\Throwable $e) {
                View::render('install/index', [
                    'error' => $e->getMessage(),
                    'defaults' => $cfg,
                ]);
                return;
            }
        }

        View::render('install/index', ['defaults' => $defaults]);
    }

    private function loadDefaultsFromConfig(): array
    {
        $defaults = [
            'host' => '127.0.0.1',
            'database' => 'servizioBAR',
            'username' => 'root',
            'password' => '',
            'port' => 3307,
        ];

        $configured = Config::load($this->configPath);
        if (is_array($configured)) {
            $defaults = array_merge($defaults, $configured);
        }

        return $defaults;
    }
}
