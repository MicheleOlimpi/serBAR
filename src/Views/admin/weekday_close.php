<h4>GIORNI CHIUSURA</h4>
<br>
<p class="text-muted mb-3">Giorni di chiusura settimanale da preimpostare durante la creazione delle tabelle turni.</p>
<br>
<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Impostazioni salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="card shadow-sm">
  <div class="card-body p-0">
    <table class="table table-striped mb-0">
      <thead>
        <tr>
          <th class="ps-3">GIORNO</th>
          <th class="ps-3">CHIUSO</th>
          <th>DESCRIZIONE</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rules as $rule): ?>
          <?php $weekdayCode = strtolower((string) ($rule['weekday_code'] ?? '')); ?>
          <?php $isClosed = (int) ($rule['is_closed'] ?? 0) === 1; ?>
          <tr>
            <td class="ps-3"><?= htmlspecialchars((string) ($rule['day_name'] ?? ucfirst($weekdayCode))) ?></td>
            <td class="text-center">
              <input
                type="checkbox"
                class="form-check-input weekday-close"
                name="weekday_close[<?= htmlspecialchars($weekdayCode) ?>]"
                value="1"
                <?= $isClosed ? 'checked' : '' ?>
              >
            </td>
            <td>
              <input
                type="text"
                class="form-control form-control-sm weekday-description"
                name="weekday_description[<?= htmlspecialchars($weekdayCode) ?>]"
                value="<?= htmlspecialchars((string) ($rule['description'] ?? '')) ?>"
                placeholder="Descrizione"
                <?= $isClosed ? '' : 'disabled' ?>
              >
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-center gap-2">
    <button type="submit" class="btn btn-success">Salva</button>
    <button type="button" class="btn btn-secondary" onclick="window.location.reload()">Annulla</button>
  </div>
</form>

<script>
document.querySelectorAll('.weekday-close').forEach((checkbox) => {
  const row = checkbox.closest('tr');
  const description = row ? row.querySelector('.weekday-description') : null;

  if (!description) {
    return;
  }

  checkbox.addEventListener('change', () => {
    description.disabled = !checkbox.checked;

    if (!checkbox.checked) {
      description.value = '';
    }
  });
});
</script>
