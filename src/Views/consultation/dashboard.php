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

<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-start gap-3">
      <div>
        <h3 class="mb-2">Area consultazione</h3>
        <p class="text-muted mb-0">Visualizza i turni, invia segnalazioni e controlla lo stato di tutte le segnalazioni inviate.</p>
      </div>
      <div class="d-flex flex-wrap gap-2">
        <a class="btn btn-outline-secondary" href="#elenco-telefonico">Elenco telefonico utenti</a>
      </div>
    </div>
  </div>
</div>

<div class="card border-0 shadow-sm mb-4" id="elenco-telefonico">
  <div class="card-body p-4">
    <h5 class="card-title mb-3">Elenco telefonico utenti</h5>
    <p class="text-muted small">Elenco in sola lettura ordinato alfabeticamente per cognome e nome.</p>

    <?php if (empty($directoryUsers)): ?>
      <div class="alert alert-info mb-0">Nessun utente disponibile in rubrica.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cognome</th>
              <th>Nome</th>
              <th>Telefono</th>
              <th class="text-end">Azione</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($directoryUsers as $directoryUser): ?>
              <?php $rawPhone = trim((string) ($directoryUser['phone'] ?? '')); ?>
              <?php $callPhone = preg_replace('/[^0-9+]/', '', $rawPhone); ?>
              <tr>
                <td><?= htmlspecialchars((string) $directoryUser['last_name']) ?></td>
                <td><?= htmlspecialchars((string) $directoryUser['first_name']) ?></td>
                <td><?= htmlspecialchars($rawPhone !== '' ? $rawPhone : '-') ?></td>
                <td class="text-end">
                  <?php if ($callPhone !== ''): ?>
                    <a class="btn btn-sm btn-outline-success" href="tel:<?= htmlspecialchars($callPhone) ?>">
                      <i class="fa-solid fa-phone me-1"></i>Chiama
                    </a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" type="button" disabled>Chiama</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
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
    <div class="card border-0 shadow-sm mb-4">
      <div class="card-body p-4">
        <h5 class="card-title mb-3">Nuova segnalazione</h5>
        <form method="post" class="d-grid gap-3">
          <div>
            <label class="form-label" for="message">Testo libero</label>
            <textarea id="message" name="message" rows="5" class="form-control" placeholder="Scrivi la tua segnalazione" required></textarea>
          </div>

          <div class="d-flex justify-content-between align-items-center gap-2">
            <small class="text-muted">All'invio, la segnalazione sarà registrata con stato <strong>inviata</strong>.</small>
            <button class="btn btn-warning" type="submit">Invia</button>
          </div>
        </form>
      </div>
    </div>

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
                  <th>Turno</th>
                  <th>Messaggio</th>
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
                            $createdAtFormatted = (new DateTimeImmutable((string) $n['created_at']))->format('d/m/y H:i');
                        } catch (Exception) {
                            $createdAtFormatted = (string) $n['created_at'];
                        }
                    }
                  ?>
                  <tr>
                    <td><?= htmlspecialchars($createdAtFormatted) ?></td>
                    <td><?= htmlspecialchars($n['username']) ?></td>
                    <td><?= htmlspecialchars((string) ($n['day_date'] ? $n['day_date'] . ' - ' . sprintf('%02d/%04d', $n['month'], $n['year']) : '-')) ?></td>
                    <td><?= htmlspecialchars($n['message']) ?></td>
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
