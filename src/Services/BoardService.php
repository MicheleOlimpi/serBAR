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
        $prefestivo = $this->idByCode('prefestivo');
        $festivo = $this->idByCode('festivo');
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

            $days[$iso] = [
                'day_date' => $iso,
                'weekday_name' => $this->weekday[$d->format('l')] ?? $d->format('l'),
                'recurrence_name' => $recurrenceName,
                'day_type_id' => $type,
            ];

            if ($d->format('N') === '7' && $festivo > 0) {
                $days[$iso]['day_type_id'] = $festivo;
            }
        }

        $easter = $this->easterSunday($year);
        $this->applySpecialDay($days, $easter->modify('-47 days'), $feriale, 'Martedì grasso');
        $this->applySpecialDay($days, $easter->modify('-46 days'), $feriale, 'Mercoledì delle ceneri');
        $this->applySpecialDay($days, $easter->modify('-7 days'), $festivo, 'Domenica delle palme');
        $this->applySpecialDay($days, $easter, $festivo, 'Pasqua');
        $this->applySpecialDay($days, $easter->modify('+1 day'), $festivo, "Lunedì dell'angelo");

        $orderedDates = array_keys($days);
        for ($index = 1, $count = count($orderedDates); $index < $count; $index++) {
            $currentDate = $orderedDates[$index];
            $previousDate = $orderedDates[$index - 1];

            if ((int) $days[$currentDate]['day_type_id'] === $festivo
                && (int) $days[$previousDate]['day_type_id'] !== $festivo
                && $prefestivo > 0) {
                $days[$previousDate]['day_type_id'] = $prefestivo;
            }
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

    private function easterSunday(int $year): \DateTimeImmutable
    {
        $timestamp = easter_date($year);
        return new \DateTimeImmutable(gmdate('Y-m-d', $timestamp));
    }

    /**
     * @param array<string, array{day_date:string, weekday_name:string, recurrence_name:string|null, day_type_id:int}> $days
     */
    private function applySpecialDay(array &$days, \DateTimeImmutable $date, int $dayTypeId, string $recurrence): void
    {
        $iso = $date->format('Y-m-d');
        if (!isset($days[$iso])) {
            return;
        }

        if ($dayTypeId > 0) {
            $days[$iso]['day_type_id'] = $dayTypeId;
        }
        $days[$iso]['recurrence_name'] = $recurrence;
    }
}
