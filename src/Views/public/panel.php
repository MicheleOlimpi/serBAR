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

$weekdayHeaders = ['Lun', 'Mar', 'Mer', 'Gio', 'Ven', 'Sab', 'Dom'];

$formatWeekdayLabel = static function (string $weekdayName): string {
    $weekdayName = trim($weekdayName);
    if ($weekdayName === '') {
        return '-';
    }

    return function_exists('mb_substr') ? mb_substr($weekdayName, 0, 3) : substr($weekdayName, 0, 3);
};

$startOfMonth = new DateTimeImmutable(sprintf('%04d-%02d-01', $year, $month));
$endOfMonth = $startOfMonth->modify('last day of this month');

$daysByDate = [];
for ($cursor = $startOfMonth; $cursor <= $endOfMonth; $cursor = $cursor->modify('+1 day')) {
    $dateKey = $cursor->format('Y-m-d');
    $daysByDate[$dateKey] = [
        'day_number' => $cursor->format('d'),
        'weekday_label' => $weekdayHeaders[((int) $cursor->format('N')) - 1],
        'day_type_color' => null,
        'shifts' => [],
    ];
}

foreach ($shifts as $shift) {
    if (empty($shift['day_date'])) {
        continue;
    }

    $dayDate = (string) $shift['day_date'];
    if (!isset($daysByDate[$dayDate])) {
        continue;
    }

    $weekdayName = (string) ($shift['weekday_name'] ?? '');
    if (trim($weekdayName) !== '') {
        $daysByDate[$dayDate]['weekday_label'] = $formatWeekdayLabel($weekdayName);
    }

    $dayTypeColor = trim((string) ($shift['day_type_color'] ?? ''));
    $hasValidDayTypeColor = preg_match('/^#[0-9a-fA-F]{6}$/', $dayTypeColor) === 1;
    if ($hasValidDayTypeColor) {
        $daysByDate[$dayDate]['day_type_color'] = strtolower($dayTypeColor);
    }

    $startTime = trim((string) ($shift['start_time'] ?? ''));
    $volunteers = trim((string) ($shift['volunteers'] ?? ''));

    if ($startTime === '' && $volunteers === '') {
        continue;
    }

    $daysByDate[$dayDate]['shifts'][] = [
        'start_time' => $startTime !== '' ? substr($startTime, 0, 5) : '-',
        'volunteers' => $volunteers,
    ];
}

$weeks = [];
$currentWeek = array_fill(0, 7, null);

$startWeekdayIndex = ((int) $startOfMonth->format('N')) - 1;
$dayPointer = $startWeekdayIndex;

foreach ($daysByDate as $dayData) {
    $currentWeek[$dayPointer] = $dayData;
    $dayPointer++;

    if ($dayPointer === 7) {
        $weeks[] = $currentWeek;
        $currentWeek = array_fill(0, 7, null);
        $dayPointer = 0;
    }
}

if ($dayPointer !== 0) {
    $weeks[] = $currentWeek;
}
?>

<h1 class="h2 mb-4 text-center">Turni <?= htmlspecialchars(($monthNames[$month] ?? sprintf('%02d', $month)) . ' ' . $year) ?></h1>

<?php if (empty($weeks)): ?>
  <div class="alert alert-info mx-auto" style="max-width: 720px;">Nessun turno pubblicato per il mese corrente.</div>
<?php else: ?>
  <div class="public-panel-calendar">
    <div class="public-panel-weekdays">
      <?php foreach ($weekdayHeaders as $weekdayHeader): ?>
        <div class="public-panel-weekday-header text-uppercase"><?= htmlspecialchars($weekdayHeader) ?></div>
      <?php endforeach; ?>
    </div>

    <div class="public-panel-weeks">
      <?php foreach ($weeks as $week): ?>
        <div class="public-panel-week-row">
          <?php foreach ($week as $day): ?>
            <?php if ($day === null): ?>
              <div class="public-panel-day public-panel-day-empty border rounded bg-white"></div>
            <?php else: ?>
              <?php $dayBadgeStyle = !empty($day['day_type_color']) ? 'background-color: ' . $day['day_type_color'] . ';' : ''; ?>
              <article class="public-panel-day border rounded overflow-hidden bg-white">
                <header class="public-panel-day-header text-center" style="<?= htmlspecialchars($dayBadgeStyle) ?>">
                  <div class="public-panel-day-number"><?= htmlspecialchars($day['day_number']) ?></div>
                  <div class="public-panel-day-weekday text-uppercase"><?= htmlspecialchars($day['weekday_label']) ?></div>
                </header>
                <div class="p-2">
                  <?php if (empty($day['shifts'])): ?>
                    <div class="small text-muted">Nessun turno</div>
                  <?php else: ?>
                    <table class="table table-sm align-middle mb-0">
                      <tbody>
                        <?php foreach ($day['shifts'] as $groupShift): ?>
                          <tr>
                            <td class="text-nowrap"><?= htmlspecialchars($groupShift['start_time']) ?></td>
                            <td><?= htmlspecialchars($groupShift['volunteers'] !== '' ? $groupShift['volunteers'] : '-') ?></td>
                          </tr>
                        <?php endforeach; ?>
                      </tbody>
                    </table>
                  <?php endif; ?>
                </div>
              </article>
            <?php endif; ?>
          <?php endforeach; ?>
        </div>
      <?php endforeach; ?>
    </div>
  </div>
<?php endif; ?>
