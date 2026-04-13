<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

class InstallerService
{
    public function install(array $cfg, ?callable $progressCallback = null): void
    {
        $progress = static function (?callable $callback, string $message): void {
            if ($callback !== null) {
                $callback($message);
            }
        };

        $progress($progressCallback, 'Verifica connessione al server database...');
        $pdo = $this->verifyServerConnection($cfg);
        $progress($progressCallback, 'Creazione database applicativo se non presente...');
        $this->createDatabaseIfMissing($pdo, $cfg['database']);
        $progress($progressCallback, 'Selezione database applicativo...');
        $this->selectDatabase($pdo, $cfg['database']);
        $this->installSchema($pdo, $progressCallback);
        $this->seed($pdo, $progressCallback);
    }

    private function verifyServerConnection(array $cfg): PDO
    {
        try {
            return Database::createServerConnection($cfg);
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore verifica server database: ' . $e->getMessage(), 0, $e);
        }
    }

    private function createDatabaseIfMissing(PDO $pdo, string $databaseName): void
    {
        try {
            $safeName = str_replace('`', '``', $databaseName);
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$safeName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore creazione database: ' . $e->getMessage(), 0, $e);
        }
    }

    private function selectDatabase(PDO $pdo, string $databaseName): void
    {
        $safeName = str_replace('`', '``', $databaseName);
        $pdo->exec("USE `{$safeName}`");
    }

    private function installSchema(PDO $pdo, ?callable $progressCallback = null): void
    {
        try {
            if ($progressCallback !== null) {
                $progressCallback('Creazione tabelle database...');
            }

            foreach ($this->schema() as $sql) {
                $pdo->exec($sql);
            }

            if ($progressCallback !== null) {
                $progressCallback('Aggiornamento schema e vincoli...');
            }
            $this->ensureCalendarColumns($pdo);
            $this->ensureDayTypesSchema($pdo);
            $this->ensureUsersColumns($pdo);
            $this->ensureDailyShiftConfigSchema($pdo);
            $this->ensureBoardDaysSchema($pdo);
            $this->ensureBoardDayShiftsSchema($pdo);
            $this->ensureNotificationsSchema($pdo);
            $this->ensureAppSettingsSchema($pdo);
            $this->ensureWeekdayCloseSchema($pdo);
            $this->removeBoardDayUsersTable($pdo);
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore installazione tabelle: ' . $e->getMessage(), 0, $e);
        }
    }

    private function schema(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, last_name VARCHAR(100), first_name VARCHAR(100), password_hash VARCHAR(255), role VARCHAR(20) NOT NULL DEFAULT 'user', phone VARCHAR(30) NOT NULL DEFAULT '', status VARCHAR(20) NOT NULL DEFAULT 'attivo', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
            "CREATE TABLE IF NOT EXISTS day_types (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, color_hex CHAR(7) NOT NULL DEFAULT '#FFFFFF', is_locked TINYINT(1) NOT NULL DEFAULT 0)",
            'CREATE TABLE IF NOT EXISTS daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, UNIQUE KEY uq_daily_shift_day_type_priority (day_type_id, priority), FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)',
            'CREATE TABLE IF NOT EXISTS calendar_days (id INT AUTO_INCREMENT PRIMARY KEY, day_date DATE NOT NULL UNIQUE, recurrence_name VARCHAR(255) NULL, santo VARCHAR(255) NULL, is_special TINYINT(1) NOT NULL DEFAULT 0, day_type_id INT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS boards (id INT AUTO_INCREMENT PRIMARY KEY, month INT NOT NULL, year INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_board (month, year))',
            'CREATE TABLE IF NOT EXISTS board_days (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, day_date DATE NOT NULL, weekday_name VARCHAR(30) NOT NULL, recurrence_name VARCHAR(255) NULL, day_type_id INT NULL, notes TEXT NULL, FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS board_day_shifts (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, daily_shift_config_id INT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, volunteers TEXT NULL, responsabile_chiusura VARCHAR(255) NULL, UNIQUE KEY uq_board_day_shift_priority (board_day_id, priority), FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (daily_shift_config_id) REFERENCES daily_shift_config(id) ON DELETE SET NULL)',
            "CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'inviata', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)",
            'CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value VARCHAR(255) NOT NULL)',
            'CREATE TABLE IF NOT EXISTS weekday_close (id INT AUTO_INCREMENT PRIMARY KEY, weekday_code VARCHAR(20) NOT NULL UNIQUE, weekday_order TINYINT UNSIGNED NOT NULL, day_type_id INT NOT NULL, is_closed TINYINT(1) NOT NULL DEFAULT 0, description TEXT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)'
        ];
    }

    private function ensureCalendarColumns(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM calendar_days LIKE 'santo'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE calendar_days ADD COLUMN santo VARCHAR(255) NULL AFTER recurrence_name');
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM calendar_days LIKE 'is_holiday'");
        if ($stmt !== false && $stmt->fetch()) {
            $pdo->exec('ALTER TABLE calendar_days DROP COLUMN is_holiday');
        }
    }

    private function ensureDayTypesSchema(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM day_types LIKE 'color_hex'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec("ALTER TABLE day_types ADD COLUMN color_hex CHAR(7) NOT NULL DEFAULT '#FFFFFF' AFTER name");
        }
    }

    private function ensureUsersColumns(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM users LIKE 'phone'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec("ALTER TABLE users ADD COLUMN phone VARCHAR(30) NOT NULL DEFAULT '' AFTER role");
        }
    }

    private function ensureDailyShiftConfigSchema(PDO $pdo): void
    {
        $columns = $pdo->query('SHOW COLUMNS FROM daily_shift_config')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn (array $column): string => (string) $column['Field'], $columns);

        if (in_array('slots_count', $columnNames, true)) {
            $pdo->exec('DROP TABLE IF EXISTS daily_shift_config');
            $pdo->exec('CREATE TABLE daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, UNIQUE KEY uq_daily_shift_day_type_priority (day_type_id, priority), FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)');
            return;
        }

        if (!in_array('start_time', $columnNames, true)) {
            $pdo->exec("ALTER TABLE daily_shift_config ADD COLUMN start_time TIME NOT NULL DEFAULT '00:00:00' AFTER day_type_id");
        }

        if (!in_array('end_time', $columnNames, true)) {
            $pdo->exec("ALTER TABLE daily_shift_config ADD COLUMN end_time TIME NOT NULL DEFAULT '00:00:00' AFTER start_time");
        }

        if (!in_array('closes_bar', $columnNames, true)) {
            $pdo->exec('ALTER TABLE daily_shift_config ADD COLUMN closes_bar TINYINT(1) NOT NULL DEFAULT 0 AFTER end_time');
        }

        if (!in_array('priority', $columnNames, true)) {
            $pdo->exec('ALTER TABLE daily_shift_config ADD COLUMN priority INT NOT NULL DEFAULT 1 AFTER closes_bar');
        }

        $indexRows = $pdo->query("SHOW INDEX FROM daily_shift_config WHERE Key_name='uq_daily_shift_day_type_priority'")->fetchAll(PDO::FETCH_ASSOC);
        if ($indexRows === []) {
            $this->normalizeDailyShiftPriorities($pdo);
            $pdo->exec('CREATE UNIQUE INDEX uq_daily_shift_day_type_priority ON daily_shift_config (day_type_id, priority)');
        }
    }


    private function ensureBoardDaysSchema(PDO $pdo): void
    {
        $columns = $pdo->query('SHOW COLUMNS FROM board_days')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn (array $column): string => (string) $column['Field'], $columns);

        if (in_array('morning_close', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_days DROP COLUMN morning_close');
        }

        if (in_array('evening_close', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_days DROP COLUMN evening_close');
        }
    }

    private function ensureBoardDayShiftsSchema(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS board_day_shifts (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, daily_shift_config_id INT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, volunteers TEXT NULL, responsabile_chiusura VARCHAR(255) NULL, UNIQUE KEY uq_board_day_shift_priority (board_day_id, priority), FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (daily_shift_config_id) REFERENCES daily_shift_config(id) ON DELETE SET NULL)');

        $columns = $pdo->query('SHOW COLUMNS FROM board_day_shifts')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn (array $column): string => (string) $column['Field'], $columns);

        if (!in_array('daily_shift_config_id', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_day_shifts ADD COLUMN daily_shift_config_id INT NULL AFTER board_day_id');
        }

        if (!in_array('volunteers', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_day_shifts ADD COLUMN volunteers TEXT NULL AFTER priority');
        }

        if (!in_array('responsabile_chiusura', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_day_shifts ADD COLUMN responsabile_chiusura VARCHAR(255) NULL AFTER volunteers');
        }
    }

    private function ensureNotificationsSchema(PDO $pdo): void
    {
        $this->dropForeignKeyIfExists($pdo, 'notifications', 'board_day_id');

        $columns = $pdo->query('SHOW COLUMNS FROM notifications')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ((string) ($column['Field'] ?? '') === 'board_day_id') {
                $pdo->exec('ALTER TABLE notifications DROP COLUMN board_day_id');
                break;
            }
        }
    }

    private function dropForeignKeyIfExists(PDO $pdo, string $tableName, string $columnName): void
    {
        $stmt = $pdo->prepare("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND COLUMN_NAME = ? AND REFERENCED_TABLE_NAME IS NOT NULL");
        $stmt->execute([$tableName, $columnName]);

        foreach ($stmt->fetchAll(PDO::FETCH_COLUMN) as $fkName) {
            $safeTableName = str_replace('`', '``', $tableName);
            $safeFkName = str_replace('`', '``', (string) $fkName);
            $pdo->exec("ALTER TABLE `{$safeTableName}` DROP FOREIGN KEY `{$safeFkName}`");
        }
    }

    private function ensureAppSettingsSchema(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value VARCHAR(255) NOT NULL)');
    }



    private function ensureWeekdayCloseSchema(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS weekday_close (id INT AUTO_INCREMENT PRIMARY KEY, weekday_code VARCHAR(20) NOT NULL UNIQUE, weekday_order TINYINT UNSIGNED NOT NULL, day_type_id INT NOT NULL, is_closed TINYINT(1) NOT NULL DEFAULT 0, description TEXT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)');

        $stmt = $pdo->query("SHOW COLUMNS FROM weekday_close LIKE 'weekday_order'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE weekday_close ADD COLUMN weekday_order TINYINT UNSIGNED NOT NULL DEFAULT 1 AFTER weekday_code');
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM weekday_close LIKE 'day_type_id'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE weekday_close ADD COLUMN day_type_id INT NOT NULL DEFAULT 1 AFTER weekday_order');
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM weekday_close LIKE 'is_closed'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE weekday_close ADD COLUMN is_closed TINYINT(1) NOT NULL DEFAULT 0 AFTER day_type_id');
        }

        $stmt = $pdo->query("SHOW COLUMNS FROM weekday_close LIKE 'description'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE weekday_close ADD COLUMN description TEXT NULL AFTER is_closed');
        }

        $indexStmt = $pdo->query("SHOW INDEX FROM weekday_close WHERE Key_name='uq_weekday_close_code'");
        if ($indexStmt === false || !$indexStmt->fetch()) {
            $pdo->exec('ALTER TABLE weekday_close ADD UNIQUE KEY uq_weekday_close_code (weekday_code)');
        }

        $this->dropForeignKeyIfExists($pdo, 'weekday_close', 'day_type_id');
        $pdo->exec('ALTER TABLE weekday_close ADD CONSTRAINT fk_weekday_close_day_type FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE');
    }


    private function removeBoardDayUsersTable(PDO $pdo): void
    {
        $pdo->exec('DROP TABLE IF EXISTS board_day_users');
    }

    private function normalizeDailyShiftPriorities(PDO $pdo): void
    {
        $rows = $pdo->query('SELECT id, day_type_id FROM daily_shift_config ORDER BY day_type_id ASC, priority ASC, id ASC')->fetchAll(PDO::FETCH_ASSOC);
        $currentDayTypeId = null;
        $priority = 0;
        $stmt = $pdo->prepare('UPDATE daily_shift_config SET priority=? WHERE id=?');

        foreach ($rows as $row) {
            $dayTypeId = (int) $row['day_type_id'];
            if ($currentDayTypeId !== $dayTypeId) {
                $currentDayTypeId = $dayTypeId;
                $priority = 1;
            } else {
                $priority++;
            }
            $stmt->execute([$priority, (int) $row['id']]);
        }
    }

    private function seed(PDO $pdo, ?callable $progressCallback = null): void
    {
        try {
            if ($progressCallback !== null) {
                $progressCallback('Popolamento dati iniziali...');
            }
            $pdo->exec("INSERT IGNORE INTO day_types (id, name, color_hex, is_locked) VALUES (1,'feriale','#FFFFFF',1),(2,'prefestivo','#FF9090',1),(3,'festivo','#FF0000',1),(4,'chiuso','#A0A0A0',1),(5,'Orario continuato','#59d1d9',1)");
            $this->seedUsers($pdo);
            $this->seedAppSettings($pdo);
            $pdo->exec("INSERT IGNORE INTO daily_shift_config(id, day_type_id, start_time, end_time, closes_bar, priority) VALUES (1,1,'15:00:00','20:00:00',0,1),(2,1,'20:00:00','23:00:00',1,2),(3,2,'15:00:00','20:00:00',0,1),(4,2,'20:00:00','23:00:00',1,2),(5,3,'08:00:00','12:00:00',1,1),(6,3,'15:00:00','20:00:00',0,2),(7,3,'20:00:00','23:00:00',1,3),(8,4,'00:00:00','00:00:00',0,1),(9,5,'08:00:00','23:00:00',1,1)");
            $this->seedWeekdayClose($pdo);
            $this->seedCalendarDays($pdo);
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore popolamento dati iniziali: ' . $e->getMessage(), 0, $e);
        }
    }

    private function seedWeekdayClose(PDO $pdo): void
    {
        $ferialeTypeId = 1;
        $rows = [
            ['monday', 1],
            ['tuesday', 2],
            ['wednesday', 3],
            ['thursday', 4],
            ['friday', 5],
            ['saturday', 6],
            ['sunday', 7],
        ];

        $stmt = $pdo->prepare('INSERT IGNORE INTO weekday_close (weekday_code, weekday_order, day_type_id, is_closed, description) VALUES (?, ?, ?, 0, NULL)');
        foreach ($rows as [$weekdayCode, $weekdayOrder]) {
            $stmt->execute([$weekdayCode, $weekdayOrder, $ferialeTypeId]);
        }
    }

    private function seedCalendarDays(PDO $pdo): void
    {
        $this->seedFromSqlFile($pdo, 'database/seed_calendar_days.sql', 'calendario');
    }

    private function seedAppSettings(PDO $pdo): void
    {
        $this->seedFromSqlFile($pdo, 'database/seed_app_settings.sql', 'app_settings');
    }

    private function seedUsers(PDO $pdo): void
    {
        $adminHash = password_hash('admin', PASSWORD_DEFAULT);
        $userHash = password_hash('user', PASSWORD_DEFAULT);
        $supervisorHash = password_hash('supervisor', PASSWORD_DEFAULT);

        $this->seedFromSqlFile(
            $pdo,
            'database/seed_users.sql',
            'users',
            [
                '__ADMIN_HASH__' => $adminHash,
                '__USER_HASH__' => $userHash,
                '__SUPERVISOR_HASH__' => $supervisorHash,
            ]
        );
    }

    private function seedFromSqlFile(PDO $pdo, string $relativePath, string $label, array $placeholders = []): void
    {
        $seedFile = dirname(__DIR__, 2) . '/' . ltrim($relativePath, '/');
        if (!is_file($seedFile) || !is_readable($seedFile)) {
            throw new \RuntimeException(sprintf('File SQL %s non trovato o non leggibile: %s', $label, $seedFile));
        }

        $sql = (string) file_get_contents($seedFile);
        if (trim($sql) === '') {
            throw new \RuntimeException(sprintf('File SQL %s vuoto: %s', $label, $seedFile));
        }

        if ($placeholders !== []) {
            $sql = strtr($sql, $placeholders);
        }

        $sqlWithoutComments = preg_replace('/^\s*--.*$/m', '', $sql) ?? $sql;
        foreach (array_filter(array_map('trim', explode(';', $sqlWithoutComments))) as $statement) {
            if ($statement === '') {
                continue;
            }
            $pdo->exec($statement);
        }
    }
}
