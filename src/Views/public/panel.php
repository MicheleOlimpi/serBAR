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

$formatWeekdayLabel = static function (string $weekdayName): string {
    $weekdayName = trim($weekdayName);
    if ($weekdayName === '') {
        return '-';
    }

    return function_exists('mb_substr') ? mb_substr($weekdayName, 0, 3) : substr($weekdayName, 0, 3);
};

$groupedShifts = [];
foreach ($shifts as $shift) {
    $dayNumber = '-';
    if (!empty($shift['day_date'])) {
        try {
            $dayNumber = (new DateTimeImmutable((string) $shift['day_date']))->format('d');
        } catch (Exception) {
            $dayNumber = (string) $shift['day_date'];
        }
    }

    $weekdayLabel = $formatWeekdayLabel((string) ($shift['weekday_name'] ?? ''));

    $dayTypeColor = trim((string) ($shift['day_type_color'] ?? ''));
    $hasValidDayTypeColor = preg_match('/^#[0-9a-fA-F]{6}$/', $dayTypeColor) === 1;

    $startTime = trim((string) ($shift['start_time'] ?? ''));
    $startTime = $startTime !== '' ? substr($startTime, 0, 5) : '-';

    $groupKey = $dayNumber . '|' . $weekdayLabel;
    if (!isset($groupedShifts[$groupKey])) {
        $groupedShifts[$groupKey] = [
            'day_number' => $dayNumber,
            'weekday_label' => $weekdayLabel,
            'day_type_color' => $hasValidDayTypeColor ? strtolower($dayTypeColor) : null,
            'shifts' => [],
        ];
    }

    $groupedShifts[$groupKey]['shifts'][] = [
        'start_time' => $startTime,
        'volunteers' => trim((string) ($shift['volunteers'] ?? '')),
    ];
}
?>

<h1 class="h2 mb-4 text-center">Turni <?= htmlspecialchars(($monthNames[$month] ?? sprintf('%02d', $month)) . ' ' . $year) ?></h1>

<?php if (empty($groupedShifts)): ?>
  <div class="alert alert-info mx-auto" style="max-width: 720px;">Nessun turno pubblicato per il mese corrente.</div>
<?php else: ?>
  <div class="public-panel-grid">
    <?php foreach ($groupedShifts as $group): ?>
      <?php $dayBadgeStyle = !empty($group['day_type_color']) ? 'background-color: ' . $group['day_type_color'] . ';' : ''; ?>
      <article class="public-panel-day border rounded overflow-hidden bg-white">
        <header class="public-panel-day-header text-center" style="<?= htmlspecialchars($dayBadgeStyle) ?>">
          <div class="public-panel-day-number"><?= htmlspecialchars($group['day_number']) ?></div>
          <div class="public-panel-day-weekday text-uppercase"><?= htmlspecialchars($group['weekday_label']) ?></div>
        </header>
        <div class="p-2">
          <table class="table table-sm align-middle mb-0">
            <tbody>
              <?php foreach ($group['shifts'] as $groupShift): ?>
                <tr>
                  <td class="text-nowrap"><?= htmlspecialchars($groupShift['start_time']) ?></td>
                  <td><?= htmlspecialchars($groupShift['volunteers'] !== '' ? $groupShift['volunteers'] : '-') ?></td>
                </tr>
              <?php endforeach; ?>
            </tbody>
          </table>
        </div>
      </article>
    <?php endforeach; ?>
  </div>
<?php endif; ?>
