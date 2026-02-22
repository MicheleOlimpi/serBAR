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
        $easterDate = new \DateTimeImmutable(sprintf('%04d-%02d-%02d', $year, 3, 21));
        $easterDate = $easterDate->modify('+' . easter_days($year) . ' days');
        $mardiGrasDate = $easterDate->modify('-47 days');
        $ashWednesdayDate = $easterDate->modify('-46 days');
        $palmSundayDate = $easterDate->modify('-7 days');
        $easterMondayDate = $easterDate->modify('+1 day');

        $feriale = $this->idByCode('feriale');
        $prefestivo = $this->idByCode('prefestivo');
        $festivo = $this->idByCode('festivo');
        $speciale = $this->idByCode('speciale');

        $stmtCal = $this->pdo->prepare('SELECT * FROM calendar_days WHERE day_date=?');
        $days = [];

        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            $iso = $d->format('Y-m-d');
            $stmtCal->execute([$iso]);
            $cal = $stmtCal->fetch(PDO::FETCH_ASSOC) ?: null;

            $type = $feriale;
            $weekdayNumber = (int) $d->format('N');
            if ($weekdayNumber === 6 && $prefestivo > 0) {
                $type = $prefestivo;
            }
            if ($weekdayNumber === 7 && $festivo > 0) {
                $type = $festivo;
            }

            $recurrenceName = isset($cal['recurrence_name']) ? trim((string) $cal['recurrence_name']) : null;
            if ($recurrenceName === '') {
                $recurrenceName = null;
            }

            if ($cal) {
                if (!empty($cal['day_type_id'])) {
                    $type = (int) $cal['day_type_id'];
                } elseif ((int) $cal['is_holiday'] === 1) {
                    $type = $festivo;
                } elseif ((int) $cal['is_special'] === 1) {
                    $type = $speciale;
                }
            }

            if ($iso === $palmSundayDate->format('Y-m-d')) {
                $type = $festivo;
                $recurrenceName = 'Domenica delle palme';
            }
            if ($iso === $mardiGrasDate->format('Y-m-d')) {
                $type = $feriale;
                $recurrenceName = 'Martedì grasso';
            }
            if ($iso === $ashWednesdayDate->format('Y-m-d')) {
                $type = $feriale;
                $recurrenceName = 'Mercoledì delle ceneri';
            }
            if ($iso === $easterDate->format('Y-m-d')) {
                $type = $festivo;
                $recurrenceName = 'Pasqua';
            }
            if ($iso === $easterMondayDate->format('Y-m-d')) {
                $type = $festivo;
                $recurrenceName = "Lunedì dell'angelo";
            }

            $days[] = [
                'day_date' => $iso,
                'weekday_name' => $this->weekday[$d->format('l')] ?? $d->format('l'),
                'recurrence_name' => $recurrenceName,
                'day_type_id' => $type,
            ];
        }

        for ($i = 1, $count = count($days); $i < $count; $i++) {
            if ((int) $days[$i]['day_type_id'] === $festivo && $prefestivo > 0 && (int) $days[$i - 1]['day_type_id'] !== $festivo) {
                $days[$i - 1]['day_type_id'] = $prefestivo;
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
}
