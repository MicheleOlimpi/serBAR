<?php
$dayTypeColors = [];
foreach ($types as $type) {
    $dayTypeColors[(int) $type['id']] = (string) ($type['color_hex'] ?? '#FFFFFF');
}
?>
<h4>CALENDARIO</h4>
<br>
<p class="text-muted mb-3">Ricorrenze, santi, tipologie giorno e giorni speciali per ogni giorno dell'anno da preimpostare durante la creazione delle tabelle turni.</p>
<br>
<form method="post">
  <div class="table-responsive">
    <table class="table table-sm align-middle">
      <thead>
        <tr>
          <th>GIORNO</th>
          <th>RICORRENZA</th>
          <th>SANTO</th>
          <th>TIPO GIORNO</th>
          <th>SPECIALE</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($days as $d): ?>
          <?php
          $id = (int) $d['id'];
          $selectedDayTypeId = (int) ($d['day_type_id'] ?? 0);
          $selectedDayTypeColor = $dayTypeColors[$selectedDayTypeId] ?? '';
          ?>
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
              <select
                name="row[<?= $id ?>][day_type_id]"
                class="form-select form-select-sm js-calendar-day-type"
                data-empty-color="#FFFFFF"
                style="<?= $selectedDayTypeColor !== '' ? 'background-color: ' . htmlspecialchars($selectedDayTypeColor) . ';' : '' ?>"
              >
                <option value="" data-color="#FFFFFF">Nessuno</option>
                <?php foreach ($types as $t): ?>
                  <?php $typeColor = (string) ($t['color_hex'] ?? '#FFFFFF'); ?>
                  <option
                    value="<?= (int) $t['id'] ?>"
                    data-color="<?= htmlspecialchars($typeColor) ?>"
                    style="background-color: <?= htmlspecialchars($typeColor) ?>;"
                    <?= $selectedDayTypeId === (int) $t['id'] ? 'selected' : '' ?>
                  >
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
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
  <div class="d-flex justify-content-center gap-2 mt-3">
    <button class="btn btn-success" type="submit" name="save_calendar" value="1">Salva</button>
    <a class="btn btn-outline-secondary" href="?action=calendar">Annulla</a>
  </div>
</form>

<script>
  document.addEventListener('DOMContentLoaded', () => {
    const paintDayTypeSelect = (select) => {
      const selectedOption = select.options[select.selectedIndex];
      select.style.backgroundColor = selectedOption?.dataset.color || select.dataset.emptyColor || '#FFFFFF';
    };

    document.querySelectorAll('.js-calendar-day-type').forEach((select) => {
      paintDayTypeSelect(select);
      select.addEventListener('change', () => paintDayTypeSelect(select));
    });
  });
</script>
