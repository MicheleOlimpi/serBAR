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
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore installazione tabelle: ' . $e->getMessage(), 0, $e);
        }
    }

    private function schema(): array
    {
        return [
            "CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, last_name VARCHAR(100), first_name VARCHAR(100), password_hash VARCHAR(255), role VARCHAR(20) NOT NULL DEFAULT 'user', phone VARCHAR(30) NOT NULL DEFAULT '', status VARCHAR(20) NOT NULL DEFAULT 'attivo', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)",
            'CREATE TABLE IF NOT EXISTS day_types (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, is_locked TINYINT(1) NOT NULL DEFAULT 0)',
            'CREATE TABLE IF NOT EXISTS daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, priority INT NOT NULL DEFAULT 1, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)',
            'CREATE TABLE IF NOT EXISTS calendar_days (id INT AUTO_INCREMENT PRIMARY KEY, day_date DATE NOT NULL UNIQUE, recurrence_name VARCHAR(255) NULL, santo VARCHAR(255) NULL, is_holiday TINYINT(1) NOT NULL DEFAULT 0, is_special TINYINT(1) NOT NULL DEFAULT 0, day_type_id INT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS boards (id INT AUTO_INCREMENT PRIMARY KEY, month INT NOT NULL, year INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_board (month, year))',
            'CREATE TABLE IF NOT EXISTS board_days (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, day_date DATE NOT NULL, weekday_name VARCHAR(30) NOT NULL, recurrence_name VARCHAR(255) NULL, day_type_id INT NULL, morning_close VARCHAR(255) NULL, evening_close VARCHAR(255) NULL, notes TEXT NULL, FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS board_day_users (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, user_id INT NOT NULL, FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)',
            "CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, board_day_id INT NOT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT 'nuova', created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE)"
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
            $pdo->exec('CREATE TABLE daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL, start_time TIME NOT NULL, end_time TIME NOT NULL, priority INT NOT NULL DEFAULT 1, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)');
            return;
        }

        if (!in_array('start_time', $columnNames, true)) {
            $pdo->exec("ALTER TABLE daily_shift_config ADD COLUMN start_time TIME NOT NULL DEFAULT '00:00:00' AFTER day_type_id");
        }

        if (!in_array('end_time', $columnNames, true)) {
            $pdo->exec("ALTER TABLE daily_shift_config ADD COLUMN end_time TIME NOT NULL DEFAULT '00:00:00' AFTER start_time");
        }

        if (!in_array('priority', $columnNames, true)) {
            $pdo->exec('ALTER TABLE daily_shift_config ADD COLUMN priority INT NOT NULL DEFAULT 1 AFTER end_time');
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
            $pdo->exec("INSERT IGNORE INTO daily_shift_config(day_type_id, start_time, end_time, priority) VALUES (1,'08:00:00','14:00:00',1),(1,'14:00:00','20:00:00',2),(2,'08:00:00','14:00:00',1),(2,'14:00:00','20:00:00',2),(3,'08:00:00','14:00:00',1),(3,'14:00:00','20:00:00',2),(4,'08:00:00','14:00:00',1),(4,'14:00:00','20:00:00',2)");
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
