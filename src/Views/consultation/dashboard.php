<?php
$groupedShifts = [];
foreach ($shifts as $shift) {
    $boardKey = sprintf('%02d/%04d', $shift['month'], $shift['year']);
    $groupedShifts[$boardKey][] = $shift;
}

$statusClassMap = [
    'inviata' => 'text-bg-primary',
    'letto' => 'text-bg-info',
    'in_corso' => 'text-bg-warning',
    'chiuso' => 'text-bg-success',
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
        <a class="btn btn-danger" href="?action=logout">Logout</a>
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
        <p class="text-muted small">Consultazione disponibile per gli ultimi 3 tabelloni.</p>

        <?php if (empty($groupedShifts)): ?>
          <div class="alert alert-info mb-0">Nessun turno disponibile al momento.</div>
        <?php else: ?>
          <?php foreach ($groupedShifts as $boardLabel => $boardShifts): ?>
            <div class="mb-4">
              <h6 class="fw-semibold mb-2">Tabellone <?= htmlspecialchars($boardLabel) ?></h6>
              <div class="table-responsive border rounded">
                <table class="table table-sm align-middle mb-0">
                  <thead class="table-light">
                    <tr>
                      <th>Data</th>
                      <th>Giorno</th>
                      <th>Tipo</th>
                      <th>Assegnati</th>
                      <th>Chiusura mattina</th>
                      <th>Chiusura sera</th>
                      <th>Note</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php foreach ($boardShifts as $s): ?>
                      <tr>
                        <td><?= htmlspecialchars($s['day_date']) ?></td>
                        <td><?= htmlspecialchars($s['weekday_name']) ?></td>
                        <td><?= htmlspecialchars((string) $s['day_type_name']) ?></td>
                        <td><?= htmlspecialchars((string) ($s['assigned_users'] ?: '-')) ?></td>
                        <td><?= htmlspecialchars((string) $s['morning_close']) ?></td>
                        <td><?= htmlspecialchars((string) $s['evening_close']) ?></td>
                        <td><?= htmlspecialchars((string) $s['notes']) ?></td>
                      </tr>
                    <?php endforeach; ?>
                  </tbody>
                </table>
              </div>
            </div>
          <?php endforeach; ?>
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
            <label class="form-label" for="report_day">Turno di riferimento</label>
            <select id="report_day" name="report_day" class="form-select" required>
              <option value="">Seleziona un turno</option>
              <?php foreach ($shifts as $s): ?>
                <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['day_date'] . ' - ' . sprintf('%02d/%04d', $s['month'], $s['year'])) ?></option>
              <?php endforeach; ?>
            </select>
          </div>

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
                  <tr>
                    <td><?= htmlspecialchars($n['created_at']) ?></td>
                    <td><?= htmlspecialchars($n['username']) ?></td>
                    <td><?= htmlspecialchars((string) ($n['day_date'] ? $n['day_date'] . ' - ' . sprintf('%02d/%04d', $n['month'], $n['year']) : '-')) ?></td>
                    <td><?= htmlspecialchars($n['message']) ?></td>
                    <td><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($n['status']) ?></span></td>
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
