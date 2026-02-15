<?php
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$defaultMonth = $currentMonth === 12 ? 1 : $currentMonth + 1;
$defaultYear = $currentMonth === 12 ? $currentYear + 1 : $currentYear;
?>

<h4>Creazione tabellone turni</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="create_board" value="1">
  <div class="col-md-2">
    <select name="month" class="form-select" required>
      <?php for ($month = 1; $month <= 12; $month++): ?>
        <option value="<?= $month ?>" <?= $month === $defaultMonth ? 'selected' : '' ?>><?= sprintf('%02d', $month) ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="col-md-2">
    <input type="number" name="year" min="2020" max="2100" class="form-control" value="<?= $defaultYear ?>" required>
  </div>
  <div class="col-md-2"><button class="btn btn-success">Crea</button></div>
</form>
<table class="table table-striped"><tr><th>Tabellone</th><th>Azioni</th></tr>
<?php foreach($boards as $b): ?>
<tr><td><?= sprintf('%02d/%04d',$b['month'],$b['year']) ?></td><td>
<a class="btn btn-sm btn-primary" href="?action=board_edit&id=<?= $b['id'] ?>">Edita</a>
<a class="btn btn-sm btn-secondary" href="?action=board_edit&id=<?= $b['id'] ?>&print=1" target="_blank">Stampa/PDF</a>
<a class="btn btn-sm btn-danger" href="?action=boards&delete=<?= $b['id'] ?>" onclick="return confirm('Eliminare?')">Elimina</a>
</td></tr>
<?php endforeach; ?>
</table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
