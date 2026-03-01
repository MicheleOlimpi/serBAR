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
              $notificationDate = isset($n['created_at']) ? date('d/m/Y', strtotime((string) $n['created_at'])) : '';
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
