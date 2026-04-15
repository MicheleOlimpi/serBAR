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

$statusLabels = [
  'inviata' => 'Inviato',
  'letto' => 'Letto',
  'in_corso' => 'In corso',
  'chiuso' => 'Chiuso',
];

$statusBadgeMap = [
  'inviata' => 'text-bg-secondary',
  'letto' => 'text-bg-info',
  'in_corso' => 'text-bg-warning text-dark',
  'chiuso' => 'text-bg-success',
];

?>
<h1 class="h3 mb-3">DASHBOARD</h1>
<div class="card border-0 shadow-sm mb-3">
  <div class="card-body">
    <?php if (($notificationCount ?? 0) > 0): ?>
      <p class="mb-0"><?= htmlspecialchars((string) ($greeting ?? '')) ?> <?= htmlspecialchars((string) ($username ?? '')) ?>, benvenuto. Ci sono <?= (int) ($notificationCount ?? 0) ?> segnalazioni da verificare.</p>
    <?php else: ?>
      <p class="mb-0"><?= htmlspecialchars((string) ($greeting ?? '')) ?> <?= htmlspecialchars((string) ($username ?? '')) ?>, benvenuto.</p>
      <p class="mb-0">Non ci sono segnalazioni da verificare.</p>
    <?php endif; ?>
    <?php
    $currentBoardStatus = is_array($currentMonthBoardStatus ?? null) ? $currentMonthBoardStatus : ['month' => (int) date('n'), 'year' => (int) date('Y'), 'exists' => false];
    $nextBoardStatus = is_array($nextMonthBoardStatus ?? null) ? $nextMonthBoardStatus : ['month' => (int) date('n'), 'year' => (int) date('Y'), 'exists' => false, 'total_shifts' => 0, 'empty_shifts' => 0];
    $currentMonthLabel = $monthNames[(int) ($currentBoardStatus['month'] ?? 0)] ?? (string) ($currentBoardStatus['month'] ?? '');
    $nextMonthLabel = $monthNames[(int) ($nextBoardStatus['month'] ?? 0)] ?? (string) ($nextBoardStatus['month'] ?? '');
    ?>
    <p class="mb-0 mt-2">
      Tabellone <?= htmlspecialchars($currentMonthLabel . ' ' . (string) ($currentBoardStatus['year'] ?? '')) ?>:
      <strong><?= !empty($currentBoardStatus['exists']) ? 'creato' : 'non creato' ?></strong>.
    </p>
    <p class="mb-0">
      Tabellone <?= htmlspecialchars($nextMonthLabel . ' ' . (string) ($nextBoardStatus['year'] ?? '')) ?>:
      <strong><?= !empty($nextBoardStatus['exists']) ? 'creato' : 'non creato' ?></strong>.
      <?php if (!empty($nextBoardStatus['exists'])): ?>
      <?php $coveredShifts =($nextBoardStatus['total_shifts'] - $nextBoardStatus['empty_shifts']); 
      $percentShifts =100-($nextBoardStatus['empty_shifts'] / $nextBoardStatus['total_shifts']) * 100; ?>
      Turni coperti <strong><?= (int) ($coveredShifts ?? 0) ?></strong>/<strong><?= (int) ($nextBoardStatus['total_shifts'] ?? 0) ?> (<?= (int) $percentShifts?>%)</strong>
      Turni ancora da coprire <strong><?= (int) ($nextBoardStatus['empty_shifts'] ?? 0) ?></strong>.
      <?php endif; ?>
    </p>
  </div>
</div>
<div class="row">
  <div class="col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <h5>Ultimi 12 tabelloni</h5>
        <?php if ($boards === []): ?>
          <p class="text-muted mb-0">Nessun tabellone disponibile.</p>
        <?php else: ?>
          <ul class="mb-0">
            <?php foreach ($boards as $b): ?>
              <?php $monthLabel = $monthNames[(int) $b['month']] ?? (string) $b['month']; ?>
              <li>
                <a href="?action=board_edit&id=<?= $b['id'] ?>"><?= htmlspecialchars($monthLabel . ' ' . $b['year']) ?></a>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
  <div class="col-md-6 mb-3">
    <div class="card h-100">
      <div class="card-body">
        <h5>Ultime 20 segnalazioni</h5>
        <?php if ($notifications === []): ?>
          <p class="text-muted mb-0">Nessuna segnalazione disponibile.</p>
        <?php else: ?>
          <ul class="mb-0">
            <?php foreach ($notifications as $n): ?>
              <?php
              $notificationDate = isset($n['created_at']) ? date('d/m/y', strtotime((string) $n['created_at'])) : '';
              $message = (string) $n['message'];
              $shortMessage = function_exists('mb_substr')
                ? mb_substr($message, 0, 30)
                : substr($message, 0, 30);
              $isTrimmed = function_exists('mb_strlen') ? mb_strlen($message) > 30 : strlen($message) > 30;
              ?>
              <li>
                <?= htmlspecialchars($notificationDate) ?> -
                <?= htmlspecialchars((string) $n['username']) ?> -
                <?= htmlspecialchars($shortMessage . ($isTrimmed ? '…' : '')) ?>
                <?php $statusClass = $statusBadgeMap[(string) ($n['status'] ?? '')] ?? 'text-bg-secondary'; ?>
                <span class="badge <?= $statusClass ?> ms-1"><?= htmlspecialchars($statusLabels[(string) ($n['status'] ?? '')] ?? (string) ($n['status'] ?? 'Sconosciuto')) ?></span>
              </li>
            <?php endforeach; ?>
          </ul>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
