<?php

declare(strict_types=1);

namespace App\Services;

use PDO;

class BoardService
{
    private array $weekday = ['Sunday' => 'Domenica', 'Monday' => 'Lunedì', 'Tuesday' => 'Martedì', 'Wednesday' => 'Mercoledì', 'Thursday' => 'Giovedì', 'Friday' => 'Venerdì', 'Saturday' => 'Sabato'];

    public function __construct(private PDO $pdo)
    {
    }

    public function generate(int $boardId, int $month, int $year): void
    {
        $start = new \DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
        $end = $start->modify('last day of this month');
        $feriale = $this->idByCode('feriale');
        $stmtCal = $this->pdo->prepare('SELECT day_date, recurrence_name, day_type_id FROM calendar_days WHERE day_date BETWEEN ? AND ? ORDER BY day_date ASC');
        $days = [];

        $stmtCal->execute([$start->format('Y-m-d'), $end->format('Y-m-d')]);
        $calendarRows = $stmtCal->fetchAll(PDO::FETCH_ASSOC);
        $calendarByDate = [];
        foreach ($calendarRows as $row) {
            $dayDate = (string) ($row['day_date'] ?? '');
            if ($dayDate === '') {
                continue;
            }
            $calendarByDate[$dayDate] = $row;
        }

        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            $iso = $d->format('Y-m-d');
            $cal = $calendarByDate[$iso] ?? null;

            $recurrenceName = isset($cal['recurrence_name']) ? trim((string) $cal['recurrence_name']) : null;
            if ($recurrenceName === '') {
                $recurrenceName = null;
            }

            $calendarDayTypeId = isset($cal['day_type_id']) ? (int) $cal['day_type_id'] : 0;
            $type = $calendarDayTypeId > 0 ? $calendarDayTypeId : $feriale;

            $days[] = [
                'day_date' => $iso,
                'weekday_name' => $this->weekday[$d->format('l')] ?? $d->format('l'),
                'recurrence_name' => $recurrenceName,
                'day_type_id' => $type,
            ];
        }

        $ins = $this->pdo->prepare('INSERT INTO board_days (board_id, day_date, weekday_name, recurrence_name, day_type_id) VALUES (?,?,?,?,?)');
        $insShift = $this->pdo->prepare("INSERT INTO board_day_shifts (board_day_id, daily_shift_config_id, start_time, end_time, closes_bar, priority, volunteers, responsabile_chiusura) SELECT ?, id, start_time, end_time, closes_bar, priority, '', NULL FROM daily_shift_config WHERE day_type_id=? ORDER BY priority ASC, start_time ASC");

        foreach ($days as $day) {
            $ins->execute([
                $boardId,
                $day['day_date'],
                $day['weekday_name'],
                $day['recurrence_name'],
                $day['day_type_id'],
            ]);

            $boardDayId = (int) $this->pdo->lastInsertId();
            $insShift->execute([$boardDayId, (int) $day['day_type_id']]);
        }
    }

    private function idByCode(string $code): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM day_types WHERE code=? LIMIT 1');
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn();
    }
}
