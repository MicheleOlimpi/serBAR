<?php
$groupedShifts = [];
foreach ($shifts as $shift) {
    $boardKey = sprintf('%02d/%04d', $shift['month'], $shift['year']);
    $groupedShifts[$boardKey][] = $shift;
}
?>

<div class="d-flex flex-wrap justify-content-between align-items-center mb-4 gap-2">
  <div>
    <h4 class="mb-1">Interfaccia di consultazione turni</h4>
    <p class="text-muted mb-0">Consulta i turni, invia segnalazioni e controlla lo stato delle segnalazioni inviate.</p>
  </div>
  <a class="btn btn-danger" href="?action=logout">Logout</a>
</div>

<div class="row g-3 mb-4">
  <div class="col-lg-8">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Turni disponibili (ultimi 3 tabelloni)</h5>
        <?php if (empty($groupedShifts)): ?>
          <div class="alert alert-info mb-0">Nessun turno disponibile al momento.</div>
        <?php else: ?>
          <?php foreach ($groupedShifts as $boardLabel => $boardShifts): ?>
            <div class="mb-4">
              <h6 class="fw-bold">Tabellone <?= htmlspecialchars($boardLabel) ?></h6>
              <div class="table-responsive">
                <table class="table table-sm table-bordered align-middle mb-0 bg-white">
                  <thead class="table-light">
                    <tr>
                      <th>Data</th>
                      <th>Giorno</th>
                      <th>Tipo</th>
                      <th>Utenti turno</th>
                      <th>Chiusura mattina</th>
                      <th>Chiusura sera</th>
                      <th>Annotazioni</th>
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

  <div class="col-lg-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h5 class="card-title mb-3">Nuova segnalazione</h5>
        <form method="post" class="d-grid gap-2">
          <label class="form-label mb-0" for="report_day">Turno di riferimento</label>
          <select id="report_day" name="report_day" class="form-select" required>
            <option value="">Seleziona un turno</option>
            <?php foreach ($shifts as $s): ?>
              <option value="<?= $s['id'] ?>"><?= htmlspecialchars($s['day_date'] . ' - ' . sprintf('%02d/%04d', $s['month'], $s['year'])) ?></option>
            <?php endforeach; ?>
          </select>

          <label class="form-label mb-0" for="message">Testo segnalazione</label>
          <textarea id="message" name="message" rows="5" class="form-control" placeholder="Scrivi qui la tua segnalazione" required></textarea>

          <button class="btn btn-warning" type="submit">Invia segnalazione</button>
          <small class="text-muted">Dopo l'invio lo stato iniziale sarà <strong>inviata</strong>.</small>
        </form>
      </div>
    </div>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h5 class="card-title mb-3">Tutte le segnalazioni</h5>
    <?php if (empty($notifications)): ?>
      <div class="alert alert-info mb-0">Non ci sono ancora segnalazioni.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm table-striped align-middle mb-0">
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
              <tr>
                <td><?= htmlspecialchars($n['created_at']) ?></td>
                <td><?= htmlspecialchars($n['username']) ?></td>
                <td><?= htmlspecialchars((string) ($n['day_date'] ? $n['day_date'] . ' - ' . sprintf('%02d/%04d', $n['month'], $n['year']) : '-')) ?></td>
                <td><?= htmlspecialchars($n['message']) ?></td>
                <td><span class="badge text-bg-secondary"><?= htmlspecialchars($n['status']) ?></span></td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
