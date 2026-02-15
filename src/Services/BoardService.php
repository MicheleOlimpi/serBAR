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
        $speciale = $this->idByCode('speciale');

        $stmtCal = $this->pdo->prepare('SELECT * FROM calendar_days WHERE day_date=?');
        $ins = $this->pdo->prepare('INSERT INTO board_days (board_id, day_date, weekday_name, recurrence_name, day_type_id) VALUES (?,?,?,?,?)');

        for ($d = $start; $d <= $end; $d = $d->modify('+1 day')) {
            $iso = $d->format('Y-m-d');
            $stmtCal->execute([$iso]);
            $cal = $stmtCal->fetch(PDO::FETCH_ASSOC) ?: null;

            $type = $feriale;
            $weekdayNumber = $d->format('N');
            if ($weekdayNumber === '6' && $prefestivo > 0) {
                $type = $prefestivo;
            }
            if ($weekdayNumber === '7' && $festivo > 0) {
                $type = $festivo;
            }

            if ($cal) {
                if (!empty($cal['day_type_id'])) {
                    $type = (int) $cal['day_type_id'];
                } elseif ((int) $cal['is_special'] === 1) {
                    $type = $speciale;
                } elseif ((int) $cal['is_holiday'] === 1) {
                    $type = $festivo;
                }
            }

            $ins->execute([
                $boardId,
                $iso,
                $this->weekday[$d->format('l')] ?? $d->format('l'),
                $cal['recurrence_name'] ?? null,
                $type,
            ]);
        }
    }

    private function idByCode(string $code): int
    {
        $stmt = $this->pdo->prepare('SELECT id FROM day_types WHERE code=? LIMIT 1');
        $stmt->execute([$code]);
        return (int) $stmt->fetchColumn();
    }
}
