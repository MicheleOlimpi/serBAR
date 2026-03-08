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

$formatBoardLabel = static function (int $month, int $year) use ($monthNames): string {
    $monthLabel = $monthNames[$month] ?? sprintf('%02d', $month);

    return $monthLabel . ' ' . $year;
};

$selectedBoardShifts = [];
if (!empty($selectedBoard)) {
    $selectedBoardId = (int) ($selectedBoard['id'] ?? 0);
    foreach ($shifts as $shift) {
        if ((int) $shift['board_id'] === $selectedBoardId) {
            $selectedBoardShifts[] = $shift;
        }
    }
}

$statusClassMap = [
    'inviata' => 'text-bg-secondary',
    'letto' => 'text-bg-info',
    'in_corso' => 'text-bg-warning text-dark',
    'chiuso' => 'text-bg-success',
];

$statusLabels = [
    'inviata' => 'Inviato',
    'letto' => 'Letto',
    'in_corso' => 'In corso',
    'chiuso' => 'Chiuso',
];
?>

<h1 class="h3 mb-4">DASHBOARD</h1>

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-4">
    <h5 class="card-title mb-0"><?= htmlspecialchars((string) $greeting) ?> <?= htmlspecialchars((string) $username) ?>, benvenuto.</h5>
  </div>
</div>

<div class="row g-4">
  <div class="col-12 col-xl-7">
    <div class="card border-0 shadow-sm h-100">
      <div class="card-body p-4">
        <h5 class="card-title mb-3">Turni pubblicati</h5>
        <p class="text-muted small">Scegli mese/anno dalla lista in ordine decrescente.</p>

        <?php if (empty($boards)): ?>
          <div class="alert alert-info mb-0">Nessun turno disponibile al momento.</div>
        <?php else: ?>
          <div class="row g-3">
            <div class="col-12 col-lg-4">
              <div class="list-group">
                <?php foreach ($boards as $board): ?>
                  <?php $boardTitle = $formatBoardLabel((int) $board['month'], (int) $board['year']); ?>
                  <a href="./?board_id=<?= (int) $board['id'] ?>" class="list-group-item list-group-item-action <?= (int) $board['id'] === (int) $selectedBoardId ? 'active' : '' ?>">
                    <?= htmlspecialchars($boardTitle) ?>
                  </a>
                <?php endforeach; ?>
              </div>
            </div>

            <div class="col-12 col-lg-8">
              <?php if (!empty($selectedBoard)): ?>
                <?php $selectedBoardTitle = $formatBoardLabel((int) $selectedBoard['month'], (int) $selectedBoard['year']); ?>
                <h6 class="fw-semibold mb-2">Tabellone <?= htmlspecialchars($selectedBoardTitle) ?></h6>
              <?php endif; ?>

              <?php if (empty($selectedBoardShifts)): ?>
                <div class="alert alert-info mb-0">Nessun turno disponibile per il mese selezionato.</div>
              <?php else: ?>
                <div class="table-responsive border rounded">
                  <table class="table table-sm align-middle mb-0">
                    <thead class="table-light">
                      <tr>
                        <th>Data</th>
                        <th>Giorno</th>
                        <th>Tipo</th>
                        <th>Note</th>
                      </tr>
                    </thead>
                    <tbody>
                      <?php foreach ($selectedBoardShifts as $s): ?>
                        <tr>
                          <td><?= htmlspecialchars($s['day_date']) ?></td>
                          <td><?= htmlspecialchars($s['weekday_name']) ?></td>
                          <td><?= htmlspecialchars((string) $s['day_type_name']) ?></td>
                          <td><?= htmlspecialchars((string) $s['notes']) ?></td>
                        </tr>
                      <?php endforeach; ?>
                    </tbody>
                  </table>
                </div>
              <?php endif; ?>
            </div>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>

  <div class="col-12 col-xl-5">
    <div class="card border-0 shadow-sm">
      <div class="card-body p-4">
        <h5 class="card-title mb-3">Segnalazioni di tutti gli utenti</h5>
        <?php if (empty($notifications)): ?>
          <div class="alert alert-info mb-0">Non ci sono ancora segnalazioni.</div>
        <?php else: ?>
          <div class="table-responsive">
            <table class="table table-sm align-middle mb-0">
              <thead class="table-light">
                <tr>
                  <th>Data invio</th>
                  <th>Utente</th>
                  <th>Testo (prime 20)</th>
                  <th>Stato</th>
                </tr>
              </thead>
              <tbody>
                <?php foreach ($notifications as $n): ?>
                  <?php $statusClass = $statusClassMap[$n['status']] ?? 'text-bg-secondary'; ?>
                  <?php
                    $createdAtFormatted = (string) $n['created_at'];
                    if (!empty($n['created_at'])) {
                        try {
                            $createdAtFormatted = (new DateTimeImmutable((string) $n['created_at']))->format('d/m/y');
                        } catch (Exception) {
                            $createdAtFormatted = (string) $n['created_at'];
                        }
                    }

                    $message = trim((string) ($n['message'] ?? ''));
                    $messageLength = function_exists('mb_strlen') ? mb_strlen($message) : strlen($message);
                    $messagePreview = $messageLength > 20
                        ? ((function_exists('mb_substr') ? mb_substr($message, 0, 20) : substr($message, 0, 20)) . '…')
                        : $message;
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($createdAtFormatted) ?></td>
                    <td><?= htmlspecialchars($n['username']) ?></td>
                    <td><?= htmlspecialchars($messagePreview !== '' ? $messagePreview : '-') ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabels[$n['status']] ?? $n['status']) ?></span></td>
                  </tr>
                <?php endforeach; ?>
              </tbody>
            </table>
          </div>
        <?php endif; ?>
      </div>
    </div>
  </div>
</div>
