<?php
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$defaultMonth = $currentMonth < 12 ? $currentMonth + 1 : 1;
$defaultYear = $currentMonth < 12 ? $currentYear : $currentYear + 1;
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
?>

<h4>Creazione tabellone turni</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="create_board" value="1">
  <div class="col-md-3">
    <label class="form-label">Mese</label>
    <select name="month" class="form-select" required>
      <?php for ($month = 1; $month <= 12; $month++): ?>
        <option value="<?= $month ?>" <?= $month === $defaultMonth ? 'selected' : '' ?>><?= $monthNames[$month] ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Anno</label>
    <input type="text" name="year" class="form-control" inputmode="numeric" pattern="\d{4}" maxlength="4" value="<?= $defaultYear ?>" required>
  </div>
  <div class="col-md-2"><button class="btn btn-success">Crea</button></div>
</form>
<table class="table table-striped"><tr><th>Tabellone</th><th>Azioni</th></tr>
<?php foreach($boards as $b): ?>
<tr><td><?= ($monthNames[(int) $b['month']] ?? sprintf('%02d', $b['month'])) . ' ' . $b['year'] ?></td><td>
<a class="btn btn-sm btn-primary" href="?action=board_edit&id=<?= $b['id'] ?>">Edita</a>
<a class="btn btn-sm btn-secondary" href="?action=board_edit&id=<?= $b['id'] ?>&print=1" target="_blank">Stampa/PDF</a>
<a class="btn btn-sm btn-danger" href="?action=boards&delete=<?= $b['id'] ?>" onclick="return confirm('Eliminare?')">Elimina</a>
</td></tr>
<?php endforeach; ?>
</table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
