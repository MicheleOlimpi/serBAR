<?php

declare(strict_types=1);

namespace App\Repositories;

use PDO;

class BarRepository
{
    private const NOTIFICATION_STATUSES = ['inviata', 'letto', 'in_corso', 'chiuso'];

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

    public function allUsers(): array
    {
        return $this->pdo->query('SELECT * FROM users ORDER BY username')->fetchAll();
    }

    public function saveUser(array $data): void
    {
        if (!empty($data['id'])) {
            $params = [$data['username'], $data['last_name'], $data['first_name'], $data['role'], $data['status'], (int) $data['id']];
            $sql = 'UPDATE users SET username=?, last_name=?, first_name=?, role=?, status=? WHERE id=?';
            if (!empty($data['password'])) {
                $sql = 'UPDATE users SET username=?, last_name=?, first_name=?, role=?, status=?, password_hash=? WHERE id=?';
                $params = [$data['username'], $data['last_name'], $data['first_name'], $data['role'], $data['status'], password_hash($data['password'], PASSWORD_DEFAULT), (int) $data['id']];
            }
            $this->pdo->prepare($sql)->execute($params);
            return;
        }

        $this->pdo->prepare('INSERT INTO users (username,last_name,first_name,password_hash,role,status) VALUES (?,?,?,?,?,?)')
            ->execute([
                $data['username'],
                $data['last_name'],
                $data['first_name'],
                password_hash($data['password'], PASSWORD_DEFAULT),
                $data['role'],
                $data['status'],
            ]);
    }

    public function deleteUser(int $id): void
    {
        $this->pdo->prepare('DELETE FROM users WHERE id=?')->execute([$id]);
    }

    public function dayTypes(): array
    {
        return $this->pdo->query('SELECT * FROM day_types ORDER BY id')->fetchAll();
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
        return $this->pdo->query('SELECT c.*, d.name as day_type_name FROM daily_shift_config c JOIN day_types d ON d.id=c.day_type_id')->fetchAll();
    }

    public function saveShiftConfig(int $dayTypeId, int $slots): void
    {
        $this->pdo->prepare('INSERT INTO daily_shift_config (day_type_id, slots_count) VALUES (?,?) ON DUPLICATE KEY UPDATE slots_count=VALUES(slots_count)')->execute([$dayTypeId, $slots]);
    }

    public function calendarDays(?string $month = null): array
    {
        if ($month) {
            $stmt = $this->pdo->prepare("SELECT c.*, d.name as day_type_name FROM calendar_days c LEFT JOIN day_types d ON d.id=c.day_type_id WHERE DATE_FORMAT(c.day_date, '%Y-%m')=? ORDER BY c.day_date");
            $stmt->execute([$month]);
            return $stmt->fetchAll();
        }
        return $this->pdo->query('SELECT c.*, d.name as day_type_name FROM calendar_days c LEFT JOIN day_types d ON d.id=c.day_type_id ORDER BY c.day_date DESC LIMIT 366')->fetchAll();
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

    public function saveBoardDay(array $d): void
    {
        $this->pdo->prepare('UPDATE board_days SET day_type_id=?, morning_close=?, evening_close=?, notes=? WHERE id=?')
            ->execute([$d['day_type_id'], $d['morning_close'], $d['evening_close'], $d['notes'], $d['id']]);
    }

    public function setBoardDayUsers(int $boardDayId, array $userIds): void
    {
        $this->pdo->prepare('DELETE FROM board_day_users WHERE board_day_id=?')->execute([$boardDayId]);
        $stmt = $this->pdo->prepare('INSERT INTO board_day_users (board_day_id, user_id) VALUES (?,?)');
        foreach ($userIds as $uid) {
            $stmt->execute([$boardDayId, (int) $uid]);
        }
    }

    public function boardDayUsersMap(int $boardId): array
    {
        $stmt = $this->pdo->prepare('SELECT u.id,u.first_name,u.last_name,bu.board_day_id FROM board_day_users bu JOIN users u ON u.id=bu.user_id JOIN board_days bd ON bd.id=bu.board_day_id WHERE bd.board_id=?');
        $stmt->execute([$boardId]);
        $rows = $stmt->fetchAll();
        $map = [];
        foreach ($rows as $r) {
            $map[$r['board_day_id']][] = $r;
        }
        return $map;
    }

    public function createNotification(int $userId, int $boardDayId, string $msg): void
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

        if ($userId < 1 || $boardDayId < 1 || $message === '') {
            return;
        }

        if (!empty($data['id'])) {
            $this->pdo->prepare('UPDATE notifications SET user_id=?, board_day_id=?, message=?, status=? WHERE id=?')
                ->execute([$userId, $boardDayId, $message, $status, (int) $data['id']]);
            return;
        }

        $this->pdo->prepare('INSERT INTO notifications (user_id, board_day_id, message, status) VALUES (?,?,?,?)')
            ->execute([$userId, $boardDayId, $message, $status]);
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
        return $this->pdo->query('SELECT n.*, u.username, bd.day_date FROM notifications n JOIN users u ON u.id=n.user_id JOIN board_days bd ON bd.id=n.board_day_id ORDER BY n.created_at DESC')->fetchAll();
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
                    bd.morning_close,
                    bd.evening_close,
                    bd.notes,
                    GROUP_CONCAT(DISTINCT CONCAT(u.last_name, " ", u.first_name) ORDER BY u.last_name, u.first_name SEPARATOR ", ") AS assigned_users
                FROM boards b
                JOIN board_days bd ON bd.board_id = b.id
                LEFT JOIN day_types dt ON dt.id = bd.day_type_id
                LEFT JOIN board_day_users bu ON bu.board_day_id = bd.id
                LEFT JOIN users u ON u.id = bu.user_id
                WHERE b.id IN (
                    SELECT id FROM boards ORDER BY year DESC, month DESC LIMIT 3
                )
                GROUP BY bd.id, b.id, b.month, b.year, bd.day_date, bd.weekday_name, dt.name, bd.morning_close, bd.evening_close, bd.notes
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
}
