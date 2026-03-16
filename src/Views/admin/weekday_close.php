<h4>GIORNI CHIUSURA</h4>
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
          <th class="text-center">CHIUSO</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($rules as $rule): ?>
          <?php $weekdayCode = strtolower((string) ($rule['weekday_code'] ?? '')); ?>
          <tr>
            <td class="ps-3"><?= htmlspecialchars((string) ($rule['day_name'] ?? ucfirst($weekdayCode))) ?></td>
            <td class="text-center">
              <input
                type="checkbox"
                class="form-check-input"
                name="weekday_close[<?= htmlspecialchars($weekdayCode) ?>]"
                value="1"
                <?= (int) ($rule['is_closed'] ?? 0) === 1 ? 'checked' : '' ?>
              >
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="card-footer d-flex justify-content-end">
    <button type="submit" class="btn btn-success">Salva</button>
  </div>
</form>
