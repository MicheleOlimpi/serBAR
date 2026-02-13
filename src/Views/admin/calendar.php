<h4>Calendario annuale ricorrenze</h4>
<form method="post" class="row g-2 mb-3">
  <div class="col-md-2"><input type="date" name="day_date" class="form-control" required></div>
  <div class="col-md-3"><input name="recurrence_name" class="form-control" placeholder="Ricorrenza"></div>
  <div class="col-md-2 form-check"><input type="checkbox" name="is_holiday" class="form-check-input" id="hol"><label for="hol" class="form-check-label">Festivo</label></div>
  <div class="col-md-2 form-check"><input type="checkbox" name="is_special" class="form-check-input" id="spec"><label for="spec" class="form-check-label">Speciale</label></div>
  <div class="col-md-2"><select name="day_type_id" class="form-select"><option value="">auto</option><?php foreach($types as $t): ?><option value="<?= $t['id'] ?>"><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select></div>
  <div class="col-md-1"><button class="btn btn-success">Salva</button></div>
</form>
<form method="get" class="mb-2"><input type="hidden" name="action" value="calendar"><input type="month" name="month"><button class="btn btn-outline-primary btn-sm">Filtra</button></form>
<table class="table table-sm"><tr><th>Data</th><th>Ricorrenza</th><th>Festivo</th><th>Speciale</th><th>Tipo giorno</th></tr><?php foreach($days as $d): ?><tr><td><?= htmlspecialchars($d['day_date']) ?></td><td><?= htmlspecialchars((string)$d['recurrence_name']) ?></td><td><?= (int)$d['is_holiday']?'Sì':'No' ?></td><td><?= (int)$d['is_special']?'Sì':'No' ?></td><td><?= htmlspecialchars((string)$d['day_type_name']) ?></td></tr><?php endforeach; ?></table>
<a class="btn btn-outline-dark" href="/">Indietro</a>
