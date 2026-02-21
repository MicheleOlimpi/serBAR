<h4>Calendario annuale ricorrenze</h4>

<p class="text-muted mb-3">Modifica rapida di ricorrenza, santo, tipologia giorno e campo speciale (senza aggiunta o cancellazione record).</p>

<form method="get" class="mb-3 d-flex gap-2">
  <input type="hidden" name="action" value="calendar">
  <input type="month" name="month" class="form-control" style="max-width: 220px" value="<?= htmlspecialchars((string) ($_GET['month'] ?? '')) ?>">
  <button class="btn btn-outline-primary btn-sm">Filtra</button>
</form>

<form method="post">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>Giorno</th>
          <th>Ricorrenza</th>
          <th>Santo</th>
          <th>Tipologia giorno</th>
          <th>Speciale</th>
          <th class="text-end">Azione</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($days as $d): ?>
          <?php $id = (int) $d['id']; ?>
          <tr>
            <td class="fw-semibold"><?= htmlspecialchars((string) date('d/m', strtotime((string) $d['day_date']))) ?></td>
            <td>
              <input
                name="row[<?= $id ?>][recurrence_name]"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars((string) ($d['recurrence_name'] ?? '')) ?>"
              >
            </td>
            <td>
              <input
                name="row[<?= $id ?>][santo]"
                class="form-control form-control-sm"
                value="<?= htmlspecialchars((string) ($d['santo'] ?? '')) ?>"
              >
            </td>
            <td>
              <select name="row[<?= $id ?>][day_type_id]" class="form-select form-select-sm">
                <option value="">Nessuno</option>
                <?php foreach ($types as $t): ?>
                  <option value="<?= (int) $t['id'] ?>" <?= (int) $d['day_type_id'] === (int) $t['id'] ? 'selected' : '' ?>>
                    <?= htmlspecialchars((string) $t['name']) ?>
                  </option>
                <?php endforeach; ?>
              </select>
            </td>
            <td class="text-center">
              <input
                type="checkbox"
                class="form-check-input"
                name="row[<?= $id ?>][is_special]"
                value="1"
                <?= !empty($d['is_special']) ? 'checked' : '' ?>
              >
            </td>
            <td class="text-end">
              <button class="btn btn-sm btn-outline-primary" type="submit" name="save_id" value="<?= $id ?>">Salva</button>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</form>

<a class="btn btn-outline-dark" href="./">Indietro</a>
