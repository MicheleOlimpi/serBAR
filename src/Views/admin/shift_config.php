<h4>Numero turni giornalieri per tipo giorno</h4>
<form method="post"><table class="table"><tr><th>Tipo giorno</th><th>Turni</th></tr><?php foreach($cfg as $c): ?><tr><td><?= htmlspecialchars($c['day_type_name']) ?></td><td><input type="number" min="1" name="slots[<?= $c['day_type_id'] ?>]" class="form-control" value="<?= (int)$c['slots_count'] ?>"></td></tr><?php endforeach; ?></table><button class="btn btn-success">Salva</button></form>
<a class="btn btn-outline-dark mt-2" href="./">Indietro</a>
