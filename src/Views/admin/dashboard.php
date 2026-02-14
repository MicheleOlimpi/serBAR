<div class="d-flex gap-2 mb-3 flex-wrap">
  <a class="btn btn-primary" href="?action=boards">Tabelloni</a>
  <a class="btn btn-outline-primary" href="?action=users">Utenti</a>
  <a class="btn btn-outline-primary" href="?action=day_types">Tipo giorno</a>
  <a class="btn btn-outline-primary" href="?action=shift_config">Numero turni</a>
  <a class="btn btn-outline-primary" href="?action=calendar">Calendario</a>
  <a class="btn btn-danger" href="?action=logout">Logout</a>
</div>
<div class="row">
  <div class="col-md-6"><div class="card"><div class="card-body"><h5>Tabelloni</h5><ul><?php foreach($boards as $b): ?><li><a href="?action=board_edit&id=<?= $b['id'] ?>"><?= sprintf('%02d/%04d',$b['month'],$b['year']) ?></a></li><?php endforeach; ?></ul></div></div></div>
  <div class="col-md-6"><div class="card"><div class="card-body"><h5>Segnalazioni</h5><ul><?php foreach($notifications as $n): ?><li><?= htmlspecialchars($n['username']) ?> - <?= htmlspecialchars($n['day_date']) ?>: <?= htmlspecialchars($n['message']) ?></li><?php endforeach; ?></ul></div></div></div>
</div>
