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
        $pdo = $this->verifyServerAndCreateDatabase($cfg);
        $this->installSchema($pdo);
        $this->seed($pdo);
    }

    private function verifyServerAndCreateDatabase(array $cfg): PDO
    {
        try {
            $pdo = Database::createServerConnection($cfg);
            $dbName = $cfg['database'];
            $pdo->exec("CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            $pdo->exec("USE `{$dbName}`");
            return $pdo;
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore verifica server/creazione database: ' . $e->getMessage(), 0, $e);
        }
    }

    private function installSchema(PDO $pdo): void
    {
        try {
            foreach ($this->schema() as $sql) {
                $pdo->exec($sql);
            }
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore installazione tabelle: ' . $e->getMessage(), 0, $e);
        }
    }

    private function schema(): array
    {
        return [
            'CREATE TABLE IF NOT EXISTS users (id INT AUTO_INCREMENT PRIMARY KEY, username VARCHAR(50) UNIQUE, last_name VARCHAR(100), first_name VARCHAR(100), password_hash VARCHAR(255), role VARCHAR(20) NOT NULL DEFAULT "user", status VARCHAR(20) NOT NULL DEFAULT "attivo", created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP)',
            'CREATE TABLE IF NOT EXISTS day_types (id INT AUTO_INCREMENT PRIMARY KEY, name VARCHAR(50) NOT NULL, code VARCHAR(50) NOT NULL, is_locked TINYINT(1) NOT NULL DEFAULT 0)',
            'CREATE TABLE IF NOT EXISTS daily_shift_config (id INT AUTO_INCREMENT PRIMARY KEY, day_type_id INT NOT NULL UNIQUE, slots_count INT NOT NULL DEFAULT 1, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE CASCADE)',
            'CREATE TABLE IF NOT EXISTS calendar_days (id INT AUTO_INCREMENT PRIMARY KEY, day_date DATE NOT NULL UNIQUE, recurrence_name VARCHAR(255) NULL, is_holiday TINYINT(1) NOT NULL DEFAULT 0, is_special TINYINT(1) NOT NULL DEFAULT 0, day_type_id INT NULL, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS boards (id INT AUTO_INCREMENT PRIMARY KEY, month INT NOT NULL, year INT NOT NULL, created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, UNIQUE KEY uq_board (month, year))',
            'CREATE TABLE IF NOT EXISTS board_days (id INT AUTO_INCREMENT PRIMARY KEY, board_id INT NOT NULL, day_date DATE NOT NULL, weekday_name VARCHAR(30) NOT NULL, recurrence_name VARCHAR(255) NULL, day_type_id INT NULL, morning_close VARCHAR(255) NULL, evening_close VARCHAR(255) NULL, notes TEXT NULL, FOREIGN KEY (board_id) REFERENCES boards(id) ON DELETE CASCADE, FOREIGN KEY (day_type_id) REFERENCES day_types(id) ON DELETE SET NULL)',
            'CREATE TABLE IF NOT EXISTS board_day_users (id INT AUTO_INCREMENT PRIMARY KEY, board_day_id INT NOT NULL, user_id INT NOT NULL, FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE)',
            'CREATE TABLE IF NOT EXISTS notifications (id INT AUTO_INCREMENT PRIMARY KEY, user_id INT NOT NULL, board_day_id INT NOT NULL, message TEXT NOT NULL, status VARCHAR(20) NOT NULL DEFAULT "nuova", created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP, FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE, FOREIGN KEY (board_day_id) REFERENCES board_days(id) ON DELETE CASCADE)'
        ];
    }

    private function seed(PDO $pdo): void
    {
        try {
            $pdo->exec("INSERT IGNORE INTO day_types (id, name, code, is_locked) VALUES (1,'speciale','speciale',1),(2,'feriale','feriale',1),(3,'festivo','festivo',1)");
            $adminHash = password_hash('admin', PASSWORD_DEFAULT);
            $userHash = password_hash('user', PASSWORD_DEFAULT);
            $pdo->exec("INSERT IGNORE INTO users (username,last_name,first_name,password_hash,role,status) VALUES ('admin','Admin','Sistema','{$adminHash}','admin','attivo')");
            $pdo->exec("INSERT IGNORE INTO users (username,last_name,first_name,password_hash,role,status) VALUES ('user','User','Default','{$userHash}','user','attivo')");
            $pdo->exec('INSERT IGNORE INTO daily_shift_config(day_type_id, slots_count) VALUES (1,2),(2,2),(3,2)');
        } catch (Throwable $e) {
            throw new \RuntimeException('Errore popolamento dati iniziali: ' . $e->getMessage(), 0, $e);
        }
    }
}
