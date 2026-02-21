<?php
$weekdayLabels = [
    'Monday' => 'Lunedì',
    'Tuesday' => 'Martedì',
    'Wednesday' => 'Mercoledì',
    'Thursday' => 'Giovedì',
    'Friday' => 'Venerdì',
    'Saturday' => 'Sabato',
    'Sunday' => 'Domenica',
];
?>
<h4>Calendario annuale ricorrenze</h4>

<p class="text-muted mb-3">Vista giorno con numero, nome del giorno, ricorrenza e cambio tipologia nello stesso riquadro colorato.</p>

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
          <th>Santo</th>
          <th class="text-end">Azione</th>
        </tr>
      </thead>
      <tbody>
        <?php foreach ($days as $d): ?>
          <?php
            $id = (int) $d['id'];
            $dayNumber = date('d', strtotime((string) $d['day_date']));
            $weekday = $weekdayLabels[(string) ($d['weekday_name'] ?? '')] ?? (string) ($d['weekday_name'] ?? '');
            $dayColor = (string) ($d['day_type_color'] ?? '#f8f9fa');
          ?>
          <tr>
            <td>
              <div class="rounded p-2" style="background-color: <?= htmlspecialchars($dayColor) ?>22; border-left: 6px solid <?= htmlspecialchars($dayColor) ?>; min-width: 270px;">
                <div class="display-6 fw-bold lh-1"><?= htmlspecialchars((string) $dayNumber) ?></div>
                <div class="small text-muted mt-1 mb-2"><?= htmlspecialchars($weekday) ?></div>
                <label class="form-label small mb-1">Ricorrenza</label>
                <input
                  name="row[<?= $id ?>][recurrence_name]"
                  class="form-control form-control-sm mb-2"
                  value="<?= htmlspecialchars((string) ($d['recurrence_name'] ?? '')) ?>"
                >
                <label class="form-label small mb-1">Tipologia giorno</label>
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
