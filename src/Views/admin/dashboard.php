<?php
$statusClassMap = [
  'inviata' => 'text-bg-secondary',
  'letto' => 'text-bg-info',
  'in_corso' => 'text-bg-warning text-dark',
  'chiuso' => 'text-bg-success',
];
$statusLabels = [
  'inviata' => 'Inviato',
  'letto' => 'Letto',
  'in_corso' => 'In corso',
  'chiuso' => 'Chiuso',
];
?>
<div class="d-flex gap-2 mb-3 flex-wrap">
  <a class="btn btn-primary" href="?action=boards">Tabelloni</a>
  <a class="btn btn-outline-primary" href="?action=users">Utenti</a>
  <a class="btn btn-outline-primary" href="?action=day_types">Tipo giorno</a>
  <a class="btn btn-outline-primary" href="?action=shift_config">Turni giornalieri</a>
  <a class="btn btn-outline-primary" href="?action=calendar">Calendario</a>
  <a class="btn btn-outline-primary" href="?action=notifications">Segnalazioni</a>
  <a class="btn btn-outline-primary" href="?action=setup">Setup</a>
</div>
<div class="row">
  <div class="col-md-6"><div class="card"><div class="card-body"><h5>Tabelloni</h5><ul><?php foreach($boards as $b): ?><li><a href="?action=board_edit&id=<?= $b['id'] ?>"><?= sprintf('%02d/%04d',$b['month'],$b['year']) ?></a></li><?php endforeach; ?></ul></div></div></div>
  <div class="col-md-6"><div class="card"><div class="card-body"><h5>Segnalazioni <a class="btn btn-sm btn-outline-primary" href="?action=notifications">Gestisci</a></h5><ul><?php foreach($notifications as $n): ?><li><?= htmlspecialchars($n['username']) ?> - <?= htmlspecialchars($n['day_date']) ?>: <?= htmlspecialchars($n['message']) ?> <?php $statusClass = $statusClassMap[$n['status']] ?? 'text-bg-secondary'; ?><span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabels[$n['status']] ?? $n['status']) ?></span></li><?php endforeach; ?></ul></div></div></div>
</div>
