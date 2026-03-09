<?php
$monthNames = [
    1 => 'Gennaio',
    2 => 'Febbraio',
    3 => 'Marzo',
    4 => 'Aprile',
    5 => 'Maggio',
    6 => 'Giugno',
    7 => 'Luglio',
    8 => 'Agosto',
    9 => 'Settembre',
    10 => 'Ottobre',
    11 => 'Novembre',
    12 => 'Dicembre',
];
$monthName = $monthNames[(int) ($board['month'] ?? 0)] ?? sprintf('%02d', (int) ($board['month'] ?? 0));
?><!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Cartellone <?= htmlspecialchars($monthName) ?> <?= (int) ($board['year'] ?? 0) ?></title>
  <style>
    * { box-sizing: border-box; }
    html, body { margin: 0; padding: 0; width: 100%; }
    body { font-family: Arial, sans-serif; color: #111; }
    .board-table { width: 100vw; border-collapse: collapse; table-layout: fixed; }
    .board-table td { border: 1px solid #444; vertical-align: middle; }
    .day-cell { width: 220px; text-align: center; padding: 0; }
    .day-box { min-height: 120px; display: flex; flex-direction: column; align-items: center; justify-content: center; padding: 12px 8px; }
    .day-number { font-size: 44px; font-weight: 700; line-height: 1; }
    .day-meta { margin-top: 4px; font-size: 14px; line-height: 1.25; }
    .shifts-cell { padding: 0; }
    .shift-row { display: grid; grid-template-columns: 130px 1fr 1fr; gap: 8px; padding: 10px 12px; border-bottom: 1px solid #d0d0d0; align-items: center; }
    .shift-row:last-child { border-bottom: 0; }
    .shift-time { font-weight: 700; white-space: nowrap; }
    .shift-volunteers, .shift-responsible { white-space: pre-line; }
    .empty-cell { padding: 12px; text-align: center; color: #666; }
    @media print {
      .board-table { width: 100%; }
    }
  </style>
</head>
<body>
<table class="board-table">
  <tbody>
  <?php foreach ($days as $d): ?>
    <?php
      $shifts = $dayShifts[$d['id']] ?? [];
      if ($shifts !== []) {
          usort($shifts, static function (array $left, array $right): int {
              return [(int) ($left['priority'] ?? 0), (string) ($left['start_time'] ?? '')] <=> [(int) ($right['priority'] ?? 0), (string) ($right['start_time'] ?? '')];
          });
      }
      $recurrence = trim((string) ($d['recurrence_name'] ?? ''));
      $saint = trim((string) ($d['santo'] ?? ''));
    ?>
    <tr>
      <td class="day-cell">
        <div class="day-box" style="background-color: <?= htmlspecialchars((string) ($d['day_type_color'] ?? '#6c757d')) ?>;">
          <div class="day-number"><?= (int) date('j', strtotime((string) $d['day_date'])) ?></div>
          <div class="day-meta"><?= htmlspecialchars((string) ($d['weekday_name'] ?? '--')) ?></div>
          <div class="day-meta"><?= htmlspecialchars($recurrence !== '' ? $recurrence : '--') ?></div>
          <div class="day-meta"><?= htmlspecialchars($saint !== '' ? $saint : '--') ?></div>
        </div>
      </td>
      <td class="shifts-cell">
        <?php if ($shifts === []): ?>
          <div class="empty-cell">--</div>
        <?php else: ?>
          <?php foreach ($shifts as $shift): ?>
            <div class="shift-row">
              <div class="shift-time"><?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?></div>
              <div class="shift-volunteers"><?= nl2br(htmlspecialchars(trim((string) ($shift['volunteers'] ?? '')) !== '' ? (string) $shift['volunteers'] : '--')) ?></div>
              <div class="shift-responsible"><?php if (!empty($shift['closes_bar'])): ?><?= nl2br(htmlspecialchars(trim((string) ($shift['responsabile_chiusura'] ?? '')) !== '' ? (string) $shift['responsabile_chiusura'] : '--')) ?><?php endif; ?></div>
            </div>
          <?php endforeach; ?>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
  </tbody>
</table>
</body>
</html>
