<?php

declare(strict_types=1);

namespace App\Controllers;

use App\Core\Config;
use App\Core\Database;
use App\Core\View;
use App\Services\InstallerService;

class InstallController
{
    public function __construct(private InstallerService $installer, private string $configPath)
    {
    }

    public function handle(): void
    {
        if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['cancel'] ?? '') === '1') {
            View::render('install/cancelled', [
                'isInstallView' => true,
            ]);
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] === 'GET' && ($_GET['step'] ?? '') === 'complete') {
            $db = trim((string) ($_GET['db'] ?? ''));
            $host = trim((string) ($_GET['host'] ?? ''));
            $port = (int) ($_GET['port'] ?? 0);

            if ($db !== '' && $host !== '' && $port > 0) {
                View::render('install/complete', [
                    'db' => $db,
                    'host' => $host,
                    'port' => $port,
                    'isInstallView' => true,
                ]);
                return;
            }
        }

        $defaults = $this->loadDefaultsFromConfig();
        $configuredDatabaseExists = $this->databaseExists($defaults);

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $cfg = [
                'host' => trim($_POST['host'] ?? $defaults['host']),
                'database' => trim($_POST['database'] ?? $defaults['database']),
                'username' => trim($_POST['username'] ?? $defaults['username']),
                'password' => $_POST['password'] ?? '',
                'port' => (int) ($_POST['port'] ?? $defaults['port']),
            ];

            if ($configuredDatabaseExists && ($_POST['confirm_existing_db'] ?? '0') !== '1') {
                View::render('install/index', [
                    'defaults' => $cfg,
                    'isInstallView' => true,
                    'configuredDatabaseExists' => true,
                ]);
                return;
            }

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

                $completeUrl = sprintf(
                    '?action=install&step=complete&db=%s&host=%s&port=%d',
                    rawurlencode($cfg['database']),
                    rawurlencode($cfg['host']),
                    $cfg['port']
                );

                echo '<script>window.location.href = ' . json_encode($completeUrl, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES) . ';</script>';
                return;
            } catch (\Throwable $e) {
                View::render('install/index', [
                    'error' => $e->getMessage(),
                    'defaults' => $cfg,
                    'isInstallView' => true,
                    'configuredDatabaseExists' => $configuredDatabaseExists,
                ]);
                return;
            }
        }

        View::render('install/index', [
            'defaults' => $defaults,
            'isInstallView' => true,
            'configuredDatabaseExists' => $configuredDatabaseExists,
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

    private function databaseExists(array $cfg): bool
    {
        $databaseName = trim((string) ($cfg['database'] ?? ''));
        if ($databaseName === '') {
            return false;
        }

        try {
            $pdo = Database::createServerConnection($cfg);
            $statement = $pdo->prepare('SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = :database_name LIMIT 1');
            $statement->execute([
                'database_name' => $databaseName,
            ]);

            return (bool) $statement->fetchColumn();
        } catch (\Throwable) {
            return false;
        }
    }
}
