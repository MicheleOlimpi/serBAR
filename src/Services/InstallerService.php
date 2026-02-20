<?php

declare(strict_types=1);

namespace App\Services;

use App\Core\Database;
use PDO;
use Throwable;

class InstallerService
{
    public function install(array $cfg): void
    {
        $pdo = $this->verifyServerConnection($cfg);
        $this->createDatabaseIfMissing($pdo, $cfg['database']);
        $this->selectDatabase($pdo, $cfg['database']);
        $this->installSchema($pdo);
        $this->seed($pdo);
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

    private function installSchema(PDO $pdo): void
    {
        try {
            foreach ($this->schema() as $sql) {
                $pdo->exec($sql);
            }
            $this->ensureCalendarColumns($pdo);
            $this->ensureUsersColumns($pdo);
            $this->ensureDailyShiftConfigSchema($pdo);
            $this->ensureBoardDaysSchema($pdo);
            $this->ensureBoardDayShiftsSchema($pdo);
            $this->ensureNotificationsSchema($pdo);
            $this->ensureAppSettingsSchema($pdo);
            $this->removeBoardDayUsersTable($pdo);
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore installazione tabelle: ' . $e->getMessage(), 0, $e);
        }
    }

    private function schema(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, last_name VARCHAR(100), first_name VARCHAR(100), password_hash VARCHAR(255), role VARCHAR(20) NOT NULL DEFAULT 'user', phone VARCHAR(30) NOT NULL DEFAULT '', status VARCHAR(20) NOT NULL DEFAULT 'attivo', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
            'CREATE TABLE IF NOT EXISTS day_types (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, is_locked TINYINT(1) NOT NULL DEFAULT 0)',
            'CREATE TABLE IF NOT EXISTS daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, UNIQUE KEY uq_daily_shift_day_type_priority (day_type_id, priority), FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)',
            'CREATE TABLE IF NOT EXISTS calendar_days (id INT AUTO_INCREMENT PRIMARY KEY, day_date DATE NOT NULL UNIQUE, recurrence_name VARCHAR(255) NULL, santo VARCHAR(255) NULL, is_holiday TINYINT(1) NOT NULL DEFAULT 0, is_special TINYINT(1) NOT NULL DEFAULT 0, day_type_id INT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS boards (id INT AUTO_INCREMENT PRIMARY KEY, month INT NOT NULL, year INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_board (month, year))',
            'CREATE TABLE IF NOT EXISTS board_days (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, day_date DATE NOT NULL, weekday_name VARCHAR(30) NOT NULL, recurrence_name VARCHAR(255) NULL, day_type_id INT NULL, notes TEXT NULL, FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS board_day_shifts (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, daily_shift_config_id INT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, volunteers TEXT NULL, UNIQUE KEY uq_board_day_shift_priority (board_day_id, priority), FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (daily_shift_config_id) REFERENCES daily_shift_config(id) ON DELETE SET NULL)',
            "CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, board_day_id INT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'inviata', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE SET NULL)",
            'CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value VARCHAR(255) NOT NULL)'
        ];
    }

    private function ensureCalendarColumns(PDO $pdo): void
    {
        $stmt = $pdo->query("SHOW COLUMNS FROM calendar_days LIKE 'santo'");
        if ($stmt === false || !$stmt->fetch()) {
            $pdo->exec('ALTER TABLE calendar_days ADD COLUMN santo VARCHAR(255) NULL AFTER recurrence_name');
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
        $pdo->exec('CREATE TABLE IF NOT EXISTS board_day_shifts (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, daily_shift_config_id INT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, closes_bar TINYINT(1) NOT NULL DEFAULT 0, priority INT NOT NULL DEFAULT 1, volunteers TEXT NULL, UNIQUE KEY uq_board_day_shift_priority (board_day_id, priority), FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (daily_shift_config_id) REFERENCES daily_shift_config(id) ON DELETE SET NULL)');

        $columns = $pdo->query('SHOW COLUMNS FROM board_day_shifts')->fetchAll(PDO::FETCH_ASSOC);
        $columnNames = array_map(static fn (array $column): string => (string) $column['Field'], $columns);

        if (!in_array('daily_shift_config_id', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_day_shifts ADD COLUMN daily_shift_config_id INT NULL AFTER board_day_id');
        }

        if (!in_array('volunteers', $columnNames, true)) {
            $pdo->exec('ALTER TABLE board_day_shifts ADD COLUMN volunteers TEXT NULL AFTER priority');
        }
    }

    private function ensureNotificationsSchema(PDO $pdo): void
    {
        $columns = $pdo->query('SHOW COLUMNS FROM notifications')->fetchAll(PDO::FETCH_ASSOC);
        foreach ($columns as $column) {
            if ((string) ($column['Field'] ?? '') === 'board_day_id' && strtoupper((string) ($column['Null'] ?? 'NO')) !== 'YES') {
                $fkStmt = $pdo->query("SELECT CONSTRAINT_NAME FROM information_schema.KEY_COLUMN_USAGE WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = 'notifications' AND COLUMN_NAME = 'board_day_id' AND REFERENCED_TABLE_NAME = 'board_days' LIMIT 1");
                $fkName = (string) ($fkStmt?->fetchColumn() ?: '');
                if ($fkName !== '') {
                    $safeFkName = str_replace('`', '``', $fkName);
                    $pdo->exec("ALTER TABLE notifications DROP FOREIGN KEY `{$safeFkName}`");
                }
                $pdo->exec('ALTER TABLE notifications MODIFY board_day_id INT NULL');
                $pdo->exec('ALTER TABLE notifications ADD CONSTRAINT notifications_ibfk_2 FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE SET NULL');
                break;
            }
        }
    }

    private function ensureAppSettingsSchema(PDO $pdo): void
    {
        $pdo->exec('CREATE TABLE IF NOT EXISTS app_settings (setting_key VARCHAR(100) PRIMARY KEY, setting_value VARCHAR(255) NOT NULL)');
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

    private function seed(PDO $pdo): void
    {
        try {
            $pdo->exec("INSERT IGNORE INTO day_types (id, name, code, is_locked) VALUES (1,'speciale','speciale',1),(2,'feriale','feriale',1),(3,'prefestivo','prefestivo',1),(4,'festivo','festivo',1)");
            $adminHash = password_hash('admin', PASSWORD_DEFAULT);
            $userHash = password_hash('user', PASSWORD_DEFAULT);
            $pdo->exec("INSERT IGNORE INTO users (username,last_name,first_name,password_hash,role,phone,status) VALUES ('admin','Admin','Sistema','{$adminHash}','admin','','attivo')");
            $pdo->exec("INSERT IGNORE INTO users (username,last_name,first_name,password_hash,role,phone,status) VALUES ('user','User','Default','{$userHash}','user','','attivo')");
            $pdo->exec("INSERT IGNORE INTO daily_shift_config(day_type_id, start_time, end_time, closes_bar, priority) VALUES (1,'08:00:00','14:00:00',0,1),(1,'14:00:00','20:00:00',1,2),(2,'08:00:00','14:00:00',0,1),(2,'14:00:00','20:00:00',1,2),(3,'08:00:00','14:00:00',0,1),(3,'14:00:00','20:00:00',1,2),(4,'08:00:00','14:00:00',0,1),(4,'14:00:00','20:00:00',1,2)");
            $this->seedCalendarDays($pdo);
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore popolamento dati iniziali: ' . $e->getMessage(), 0, $e);
        }
    }

    private function seedCalendarDays(PDO $pdo): void
    {
        $year = (int) date('Y');

        $stmt = $pdo->prepare('INSERT IGNORE INTO calendar_days (day_date, recurrence_name, santo, is_holiday, is_special, day_type_id) VALUES (?, ?, ?, ?, 0, ?)');

        $ferialeTypeId = 2;
        $prefestivoTypeId = 3;
        $festivoTypeId = 4;

        $start = new \DateTimeImmutable(sprintf('%04d-01-01', $year));
        $end = new \DateTimeImmutable(sprintf('%04d-12-31', $year));

        for ($day = $start; $day <= $end; $day = $day->modify('+1 day')) {
            $dayTypeId = $ferialeTypeId;
            if ($day->format('N') === '6') {
                $dayTypeId = $prefestivoTypeId;
            }
            if ($day->format('N') === '7') {
                $dayTypeId = $festivoTypeId;
            }

            $stmt->execute([
                $day->format('Y-m-d'),
                '',
                '',
                0,
                $dayTypeId,
            ]);
        }

        $pdo->prepare("UPDATE calendar_days SET day_type_id=? WHERE YEAR(day_date)=? AND WEEKDAY(day_date)=5")->execute([$prefestivoTypeId, $year]);
        $pdo->prepare("UPDATE calendar_days SET day_type_id=? WHERE YEAR(day_date)=? AND WEEKDAY(day_date)=6")->execute([$festivoTypeId, $year]);

        $holidays = [
            ['date' => sprintf('%04d-01-01', $year), 'recurrence' => 'capodanno', 'santo' => 'Maria Santisima madre di Dio'],
            ['date' => sprintf('%04d-06-02', $year), 'recurrence' => 'Festa delle repubblica', 'santo' => 'Santi martiri Marcellino e Pietro'],
            ['date' => sprintf('%04d-08-15', $year), 'recurrence' => 'ferragosto', 'santo' => 'Assunzione della B.V. Maria'],
            ['date' => sprintf('%04d-11-01', $year), 'recurrence' => 'Tutti i santi', 'santo' => 'Tutti i santi'],
            ['date' => sprintf('%04d-12-08', $year), 'recurrence' => 'SS. Madonna', 'santo' => 'Immacolata concezione della B.V. Maria'],
            ['date' => sprintf('%04d-12-25', $year), 'recurrence' => 'Natale', 'santo' => 'Natale del Signore'],
            ['date' => sprintf('%04d-12-26', $year), 'recurrence' => 'Santo Stefano', 'santo' => 'S. Stefano'],
        ];

        $updateHoliday = $pdo->prepare(
            'UPDATE calendar_days SET day_type_id = ?, is_holiday = 1, recurrence_name = ?, santo = ? WHERE day_date = ?'
        );

        foreach ($holidays as $holiday) {
            $updateHoliday->execute([
                $festivoTypeId,
                $holiday['recurrence'],
                $holiday['santo'],
                $holiday['date'],
            ]);
        }
    }
}
