<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\View;
use App\Services\InstallerService;

class InstallController
{
    private const COMPLETE_SESSION_KEY = 'install_complete_data';

    public function __construct(private InstallerService $installer, private string $configPath)
    {
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['step'] ?? '') === 'complete') {
            $completeData = $_SESSION[self::COMPLETE_SESSION_KEY] ?? null;
            unset($_SESSION[self::COMPLETE_SESSION_KEY]);

            if (is_array($completeData)) {
                View::render('install/complete', [
                    'db' => $completeData['db'],
                    'host' => $completeData['host'],
                    'port' => $completeData['port'],
                    'isInstallView' => true,
                ]);
                return;
            }
        }

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
                @session_write_close();
                @set_time_limit(0);
                while (ob_get_level() > 0) {
                    ob_end_flush();
                }
                ob_implicit_flush(true);

                View::render('install/progress', [
                    'isInstallView' => true,
                ]);

                $progressCallback = static function (string $message): void {
                    echo '<script>window.updateInstallProgress(' . json_encode($message, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ');</script>';
                    @ob_flush();
                    flush();
                };

                $this->installer->install($cfg, $progressCallback);
                $storedConfig = Config::load($this->configPath);
                if ($storedConfig !== $cfg) {
                    if (!Config::save($this->configPath, $cfg)) {
                        throw new \RuntimeException('Impossibile salvare la configurazione in config/app.php.');
                    }
                }

                if (session_status() !== PHP_SESSION_ACTIVE) {
                    @session_start();
                }

                $_SESSION[self::COMPLETE_SESSION_KEY] = [
                    'db' => $cfg['database'],
                    'host' => $cfg['host'],
                    'port' => $cfg['port'],
                ];

                echo '<script>window.location.href = "?action=install&step=complete";</script>';
                return;
            } catch (\Throwable $e) {
                View::render('install/index', [
                    'error' => $e->getMessage(),
                    'defaults' => $cfg,
                    'isInstallView' => true,
                ]);
                return;
            }
        }

        View::render('install/index', [
            'defaults' => $defaults,
            'isInstallView' => true,
        ]);
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
