<h4>Calendario annuale ricorrenze</h4>

<p class="text-muted mb-3">Mostra il numero del giorno, con nome del giorno, ricorrenza e tipo giorno in modifica sotto al numero.</p>

<form method="get" class="mb-2 d-flex gap-2">
  <input type="hidden" name="action" value="calendar">
  <input type="month" name="month" class="form-control" style="max-width: 220px" value="<?= htmlspecialchars((string) ($_GET['month'] ?? '')) ?>">
  <button class="btn btn-outline-primary btn-sm">Filtra</button>
</form>

<form method="post">
  <div class="table-responsive">
    <table class="table table-sm table-striped align-middle">
      <thead>
        <tr>
          <th>Giorno</th>
          <th>Santo</th>
          <th>Azione</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($days as $d): ?>
          <?php $id = (int) $d['id']; ?>
          <tr>
            <td style="min-width: 240px;">
              <div class="fw-bold"><?= (int) date('j', strtotime((string) $d['day_date'])) ?></div>
              <div class="text-muted small"><?= htmlspecialchars((string) $d['weekday_name']) ?></div>
              <div class="mt-1">
                <label class="form-label mb-1 small">Ricorrenza</label>
                <input
                  name="row[<?= $id ?>][recurrence_name]"
                  class="form-control form-control-sm"
                  value="<?= htmlspecialchars((string) ($d['recurrence_name'] ?? '')) ?>"
                >
              </div>
              <div class="mt-1">
                <label class="form-label mb-1 small">Tipo giorno collegato</label>
                <select name="row[<?= $id ?>][day_type_id]" class="form-select form-select-sm">
                  <option value="">Nessuno</option>
                  <?php foreach ($types as $t): ?>
                    <option value="<?= (int) $t['id'] ?>" <?= (int) $d['day_type_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                      <?= htmlspecialchars((string) $t['name']) ?>
                    </option>
                  <?php endforeach; ?>
                </select>
              </div>
            </td>
            <td>
              <input
                name="row[<?= $id ?>][santo]"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars((string) ($d['santo'] ?? '')) ?>"
              >
            </td>
            <td>
              <button class="btn btn-sm btn-outline-primary" type="submit" name="save_id" value="<?= $id ?>">Salva</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>

<a class="btn btn-outline-dark" href="./">Indietro</a>
