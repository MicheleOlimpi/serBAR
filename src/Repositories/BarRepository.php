<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;
use PDOException;

class BarRepository
{
    private const NOTIFICATION_STATUSES = ['inviata', 'letto', 'in_corso', 'chiuso'];
    private const SETUP_KEYS = ['consultation_notifications_enabled', 'consultation_directory_enabled'];

    public function __construct(private PDO $pdo)
    {
    }

    public function findUserByUsername(string $username): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM users WHERE username = ? LIMIT 1');
        $stmt->execute([$username]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function activeUsers(): array
    {
        return $this->pdo->query("SELECT * FROM users WHERE status='attivo' ORDER BY last_name, first_name")->fetchAll();
    }

    public function consultationDirectory(): array
    {
        $sql = "SELECT last_name, first_name, phone
                FROM users
                WHERE status='attivo'
                ORDER BY last_name ASC, first_name ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function allUsers(): array
    {
        return $this->pdo->query('SELECT * FROM users ORDER BY username')->fetchAll();
    }

    public function userDisplayNames(): array
    {
        $sql = "SELECT id, first_name, last_name
                FROM users
                WHERE status='attivo'
                ORDER BY last_name ASC, first_name ASC";

        return $this->pdo->query($sql)->fetchAll();
    }

    public function saveUser(array $data): void
    {
        if (!empty($data['id'])) {
            $params = [$data['username'], $data['last_name'], $data['first_name'], $data['role'], (string) ($data['phone'] ?? ''), $data['status'], (int) $data['id']];
            $sql = 'UPDATE users SET username=?, last_name=?, first_name=?, role=?, phone=?, status=? WHERE id=?';
            if (!empty($data['password'])) {
                $sql = 'UPDATE users SET username=?, last_name=?, first_name=?, role=?, phone=?, status=?, password_hash=? WHERE id=?';
                $params = [$data['username'], $data['last_name'], $data['first_name'], $data['role'], (string) ($data['phone'] ?? ''), $data['status'], password_hash($data['password'], PASSWORD_DEFAULT), (int) $data['id']];
            }
            $this->pdo->prepare($sql)->execute($params);
            return;
        }

        $this->pdo->prepare('INSERT INTO users (username,last_name,first_name,password_hash,role,phone,status) VALUES (?,?,?,?,?,?,?)')
            ->execute([
                $data['username'],
                $data['last_name'],
                $data['first_name'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                (string) ($data['phone'] ?? ''),
                $data['status'],
            ]);
    }

    public function deleteUser(int $id): void
    {
        $stmt = $this->pdo->prepare('SELECT username FROM users WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $user = $stmt->fetch();
        if ($user && strtolower((string) $user['username']) === 'admin') {
            return;
        }

        $this->pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    }

    public function changeUserPassword(int $id, string $newPassword): void
    {
        $newPassword = trim($newPassword);
        if ($id < 1 || $newPassword === '') {
            return;
        }

        $this->pdo->prepare('UPDATE users SET password_hash=? WHERE id=?')
            ->execute([password_hash($newPassword, PASSWORD_DEFAULT), $id]);
    }

    public function dayTypes(): array
    {
        return $this->pdo->query('SELECT * FROM day_types ORDER BY id')->fetchAll();
    }

    public function dayTypeById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM day_types WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveDayType(array $data): void
    {
        if (!empty($data['id'])) {
            $this->pdo->prepare('UPDATE day_types SET name=?, code=? WHERE id=?')->execute([$data['name'], $data['code'], (int) $data['id']]);
            return;
        }
        $this->pdo->prepare('INSERT INTO day_types (name, code, is_locked) VALUES (?,?,0)')->execute([$data['name'], $data['code']]);
    }

    public function deleteDayType(int $id): bool
    {
        $stmt = $this->pdo->prepare('SELECT is_locked FROM day_types WHERE id=?');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        if (!$row || (int) $row['is_locked'] === 1) {
            return false;
        }
        $this->pdo->prepare('DELETE FROM day_types WHERE id=?')->execute([$id]);
        return true;
    }

    public function shiftConfigs(): array
    {
        $sql = 'SELECT c.*, d.name as day_type_name
                FROM daily_shift_config c
                JOIN day_types d ON d.id=c.day_type_id
                ORDER BY d.name ASC, c.priority ASC, c.start_time ASC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function dailyShiftById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM daily_shift_config WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveDailyShift(array $data): ?string
    {
        $dayTypeId = (int) ($data['day_type_id'] ?? 0);
        $startTime = (string) ($data['start_time'] ?? '');
        $endTime = (string) ($data['end_time'] ?? '');
        $priority = max(1, (int) ($data['priority'] ?? 1));
        $closesBar = !empty($data['closes_bar']) ? 1 : 0;
        $id = (int) ($data['id'] ?? 0);

        if ($dayTypeId < 1 || !$this->isValidTime($startTime) || !$this->isValidTime($endTime)) {
            return 'Dati turno non validi.';
        }

        if ($this->hasPriorityConflict($dayTypeId, $priority, $id > 0 ? $id : null)) {
            return 'Per lo stesso tipo giorno la priorità deve essere univoca.';
        }

        $startTime .= ':00';
        $endTime .= ':00';

        try {
            if ($id > 0) {
                $this->pdo->prepare('UPDATE daily_shift_config SET day_type_id=?, start_time=?, end_time=?, closes_bar=?, priority=? WHERE id=?')
                    ->execute([$dayTypeId, $startTime, $endTime, $closesBar, $priority, $id]);
                return null;
            }

            $this->pdo->prepare('INSERT INTO daily_shift_config (day_type_id, start_time, end_time, closes_bar, priority) VALUES (?,?,?,?,?)')
                ->execute([$dayTypeId, $startTime, $endTime, $closesBar, $priority]);
        } catch (PDOException $e) {
            if ((string) $e->getCode() === '23000') {
                return 'Per lo stesso tipo giorno la priorità deve essere univoca.';
            }
            throw $e;
        }

        return null;
    }

    public function deleteDailyShift(int $id): void
    {
        $this->pdo->prepare('DELETE FROM daily_shift_config WHERE id=?')->execute([$id]);
    }

    public function setupSettings(): array
    {
        $defaults = [
            'consultation_notifications_enabled' => '1',
            'consultation_directory_enabled' => '1',
        ];

        $stmt = $this->pdo->query('SELECT setting_key, setting_value FROM app_settings');
        if ($stmt === false) {
            return $defaults;
        }

        foreach ($stmt->fetchAll() as $row) {
            $key = (string) ($row['setting_key'] ?? '');
            if (in_array($key, self::SETUP_KEYS, true)) {
                $defaults[$key] = (string) ($row['setting_value'] ?? '1');
            }
        }

        return $defaults;
    }

    public function saveSetupSettings(array $data): void
    {
        $upsert = $this->pdo->prepare('INSERT INTO app_settings (setting_key, setting_value) VALUES (?, ?) ON DUPLICATE KEY UPDATE setting_value=VALUES(setting_value)');

        foreach (self::SETUP_KEYS as $settingKey) {
            $value = !empty($data[$settingKey]) ? '1' : '0';
            $upsert->execute([$settingKey, $value]);
        }
    }

    public function calendarDays(?string $month = null): array
    {
        if ($month) {
            $stmt = $this->pdo->prepare("SELECT c.*, DAYNAME(c.day_date) AS weekday_name, d.name as day_type_name FROM calendar_days c LEFT JOIN day_types d ON d.id=c.day_type_id WHERE DATE_FORMAT(c.day_date, '%Y-%m')=? ORDER BY c.day_date");
            $stmt->execute([$month]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query('SELECT c.*, DAYNAME(c.day_date) AS weekday_name, d.name as day_type_name FROM calendar_days c LEFT JOIN day_types d ON d.id=c.day_type_id ORDER BY c.day_date ASC')->fetchAll();
    }

    public function updateCalendarDayDetails(int $id, string $recurrenceName, string $santo, int $dayTypeId): void
    {
        $this->pdo->prepare('UPDATE calendar_days SET recurrence_name=?, santo=?, day_type_id=? WHERE id=?')
            ->execute([$recurrenceName, $santo, $dayTypeId > 0 ? $dayTypeId : null, $id]);
    }

    public function saveCalendarDay(array $d): void
    {
        if (!empty($d['id'])) {
            $this->pdo->prepare('UPDATE calendar_days SET day_date=?, recurrence_name=?, santo=?, is_holiday=?, is_special=?, day_type_id=? WHERE id=?')
                ->execute([$d['day_date'], $d['recurrence_name'], $d['santo'], $d['is_holiday'], $d['is_special'], $d['day_type_id'] ?: null, (int) $d['id']]);
            return;
        }

        $this->pdo->prepare('INSERT INTO calendar_days (day_date, recurrence_name, santo, is_holiday, is_special, day_type_id) VALUES (?,?,?,?,?,?) ON DUPLICATE KEY UPDATE recurrence_name=VALUES(recurrence_name), santo=VALUES(santo), is_holiday=VALUES(is_holiday), is_special=VALUES(is_special), day_type_id=VALUES(day_type_id)')
            ->execute([$d['day_date'], $d['recurrence_name'], $d['santo'], $d['is_holiday'], $d['is_special'], $d['day_type_id'] ?: null]);
    }

    public function boards(): array
    {
        return $this->pdo->query('SELECT * FROM boards ORDER BY year DESC, month DESC')->fetchAll();
    }

    public function createBoard(int $month, int $year): int
    {
        $this->pdo->prepare('INSERT INTO boards (month, year) VALUES (?,?)')->execute([$month, $year]);
        return (int) $this->pdo->lastInsertId();
    }

    public function deleteBoard(int $id): void
    {
        $this->pdo->prepare('DELETE FROM boards WHERE id=?')->execute([$id]);
    }

    public function board(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM boards WHERE id=?');
        $stmt->execute([$id]);
        $r = $stmt->fetch();
        return $r ?: null;
    }

    public function boardDays(int $boardId): array
    {
        $stmt = $this->pdo->prepare('SELECT bd.*, dt.name day_type_name FROM board_days bd LEFT JOIN day_types dt ON dt.id=bd.day_type_id WHERE board_id=? ORDER BY day_date');
        $stmt->execute([$boardId]);
        return $stmt->fetchAll();
    }

    public function boardDayShiftsMap(int $boardId): array
    {
        $stmt = $this->pdo->prepare('SELECT s.* FROM board_day_shifts s JOIN board_days bd ON bd.id=s.board_day_id WHERE bd.board_id=? ORDER BY bd.day_date ASC, s.priority ASC, s.start_time ASC');
        $stmt->execute([$boardId]);

        $map = [];
        foreach ($stmt->fetchAll() as $row) {
            $map[$row['board_day_id']][] = $row;
        }

        return $map;
    }

    public function saveBoardDay(array $d): void
    {
        $this->pdo->prepare('UPDATE board_days SET day_type_id=?, notes=? WHERE id=?')
            ->execute([$d['day_type_id'], $d['notes'], $d['id']]);
    }

    public function syncBoardDayShifts(int $boardDayId, int $dayTypeId): void
    {
        if ($dayTypeId < 1) {
            $this->pdo->prepare('DELETE FROM board_day_shifts WHERE board_day_id=?')->execute([$boardDayId]);
            return;
        }

        $stmtConfig = $this->pdo->prepare('SELECT * FROM daily_shift_config WHERE day_type_id=? ORDER BY priority ASC, start_time ASC');
        $stmtConfig->execute([$dayTypeId]);
        $configs = $stmtConfig->fetchAll();

        $stmtCurrent = $this->pdo->prepare('SELECT id, priority, volunteers FROM board_day_shifts WHERE board_day_id=?');
        $stmtCurrent->execute([$boardDayId]);
        $currentRows = $stmtCurrent->fetchAll();

        $currentByPriority = [];
        foreach ($currentRows as $row) {
            $currentByPriority[(int) $row['priority']] = $row;
        }

        $upsert = $this->pdo->prepare('INSERT INTO board_day_shifts (board_day_id, daily_shift_config_id, start_time, end_time, closes_bar, priority, volunteers) VALUES (?,?,?,?,?,?,?) ON DUPLICATE KEY UPDATE daily_shift_config_id=VALUES(daily_shift_config_id), start_time=VALUES(start_time), end_time=VALUES(end_time), closes_bar=VALUES(closes_bar), volunteers=VALUES(volunteers)');
        $priorities = [];

        foreach ($configs as $config) {
            $priority = (int) $config['priority'];
            $priorities[] = $priority;
            $volunteers = $currentByPriority[$priority]['volunteers'] ?? null;
            $upsert->execute([
                $boardDayId,
                (int) $config['id'],
                $config['start_time'],
                $config['end_time'],
                (int) $config['closes_bar'],
                $priority,
                $volunteers,
            ]);
        }

        $allPriorityStmt = $this->pdo->prepare('SELECT priority FROM board_day_shifts WHERE board_day_id=?');
        $allPriorityStmt->execute([$boardDayId]);
        $allPriorities = array_map(static fn ($value): int => (int) $value, $allPriorityStmt->fetchAll(PDO::FETCH_COLUMN));
        $toDelete = array_diff($allPriorities, $priorities);

        if ($toDelete !== []) {
            $placeholders = implode(',', array_fill(0, count($toDelete), '?'));
            $params = array_merge([$boardDayId], array_values($toDelete));
            $this->pdo->prepare("DELETE FROM board_day_shifts WHERE board_day_id=? AND priority IN ({$placeholders})")->execute($params);
        }
    }

    public function updateBoardDayShiftVolunteers(int $shiftId, string $volunteers): void
    {
        $this->pdo->prepare('UPDATE board_day_shifts SET volunteers=? WHERE id=?')->execute([$volunteers, $shiftId]);
    }

    public function createNotification(int $userId, ?int $boardDayId, string $msg): void
    {
        $this->pdo->prepare("INSERT INTO notifications (user_id, board_day_id, message, status) VALUES (?,?,?,'inviata')")
            ->execute([$userId, $boardDayId, $msg]);
    }

    public function notificationById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM notifications WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function saveNotification(array $data): void
    {
        $status = in_array($data['status'] ?? '', self::NOTIFICATION_STATUSES, true) ? $data['status'] : 'inviata';
        $userId = (int) ($data['user_id'] ?? 0);
        $boardDayId = (int) ($data['board_day_id'] ?? 0);
        $message = trim((string) ($data['message'] ?? ''));

        if ($userId < 1 || $message === '') {
            return;
        }

        $boardDayValue = $boardDayId > 0 ? $boardDayId : null;

        if (!empty($data['id'])) {
            $this->pdo->prepare('UPDATE notifications SET user_id=?, board_day_id=?, message=?, status=? WHERE id=?')
                ->execute([$userId, $boardDayValue, $message, $status, (int) $data['id']]);
            return;
        }

        $this->pdo->prepare('INSERT INTO notifications (user_id, board_day_id, message, status) VALUES (?,?,?,?)')
            ->execute([$userId, $boardDayValue, $message, $status]);
    }

    public function updateNotificationStatus(int $id, string $status): void
    {
        if (!in_array($status, self::NOTIFICATION_STATUSES, true)) {
            return;
        }
        $this->pdo->prepare('UPDATE notifications SET status=? WHERE id=?')->execute([$status, $id]);
    }

    public function deleteNotification(int $id): void
    {
        $this->pdo->prepare('DELETE FROM notifications WHERE id=?')->execute([$id]);
    }

    public function notifications(): array
    {
        return $this->pdo->query('SELECT n.*, u.username, bd.day_date FROM notifications n JOIN users u ON u.id=n.user_id LEFT JOIN board_days bd ON bd.id=n.board_day_id ORDER BY n.created_at DESC')->fetchAll();
    }

    public function boardsForConsultation(): array
    {
        return $this->boards();
    }

    public function consultationShifts(): array
    {
        $sql = 'SELECT 
                    bd.id,
                    b.id AS board_id,
                    b.month,
                    b.year,
                    bd.day_date,
                    bd.weekday_name,
                    dt.name AS day_type_name,
                    bd.notes
                FROM boards b
                JOIN board_days bd ON bd.board_id = b.id
                LEFT JOIN day_types dt ON dt.id = bd.day_type_id
                ORDER BY b.year DESC, b.month DESC, bd.day_date';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function consultationNotifications(): array
    {
        $sql = 'SELECT 
                    n.id,
                    n.message,
                    n.status,
                    n.created_at,
                    u.username,
                    bd.day_date,
                    b.month,
                    b.year
                FROM notifications n
                JOIN users u ON u.id = n.user_id
                LEFT JOIN board_days bd ON bd.id = n.board_day_id
                LEFT JOIN boards b ON b.id = bd.board_id
                ORDER BY n.created_at DESC';

        return $this->pdo->query($sql)->fetchAll();
    }

    public function boardDaysForSelect(): array
    {
        $sql = 'SELECT bd.id, bd.day_date, b.month, b.year
                FROM board_days bd
                JOIN boards b ON b.id=bd.board_id
                ORDER BY b.year DESC, b.month DESC, bd.day_date DESC';
        return $this->pdo->query($sql)->fetchAll();
    }

    public function calendarDayById(int $id): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM calendar_days WHERE id=? LIMIT 1');
        $stmt->execute([$id]);
        $row = $stmt->fetch();
        return $row ?: null;
    }

    public function deleteCalendarDay(int $id): void
    {
        $this->pdo->prepare('DELETE FROM calendar_days WHERE id=?')->execute([$id]);
    }

    public function dayTypeByCode(string $code): ?array
    {
        $stmt = $this->pdo->prepare('SELECT * FROM day_types WHERE code=? LIMIT 1');
        $stmt->execute([$code]);
        $row = $stmt->fetch();
        return $row ?: null;
    }


    private function isValidTime(string $time): bool
    {
        return (bool) preg_match('/^([01]\d|2[0-3]):[0-5]\d$/', $time);
    }

    private function hasPriorityConflict(int $dayTypeId, int $priority, ?int $excludeId = null): bool
    {
        $sql = 'SELECT COUNT(*) FROM daily_shift_config WHERE day_type_id = ? AND priority = ?';
        $params = [$dayTypeId, $priority];

        if ($excludeId !== null && $excludeId > 0) {
            $sql .= ' AND id <> ?';
            $params[] = $excludeId;
        }

        $stmt = $this->pdo->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }
}
