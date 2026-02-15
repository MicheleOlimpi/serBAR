<?php use App\Core\Auth; $print = isset($_GET['print']); if($print): ?><script>window.onload=()=>window.print()</script><?php endif; ?>
<h4>Tabellone <?= sprintf('%02d/%04d',$board['month'],$board['year']) ?></h4>
<style>
  .day-cell {
    min-width: 100px;
  }
  .day-number {
    font-size: 1.1rem;
    font-weight: 700;
    line-height: 1.1;
  }
  .day-meta {
    font-size: 0.8rem;
    color: #6c757d;
    line-height: 1.2;
  }
</style>
<?php if (Auth::isAdmin()): ?><form method="post"><?php endif; ?>
<table class="table table-sm table-bordered bg-white">
<tr><th>Giorno</th><th>Tipo giorno</th><th>Utenti turno</th><th>Chiusura mattina</th><th>Chiusura sera</th><th>Annotazioni</th><?php if(!Auth::isAdmin()):?><th>Segnala</th><?php endif; ?></tr>
<?php foreach($days as $d): $assigned=$dayUsers[$d['id']]??[]; ?>
<tr>
<td class="day-cell">
  <div class="day-number"><?= (int) date('j', strtotime($d['day_date'])) ?></div>
  <div class="day-meta"><?= htmlspecialchars($d['weekday_name']) ?></div>
  <?php if (!empty($d['recurrence_name'])): ?><div class="day-meta"><?= htmlspecialchars((string) $d['recurrence_name']) ?></div><?php endif; ?>
</td>
<td>
<?php if (Auth::isAdmin()): ?><select class="form-select form-select-sm" name="day[<?= $d['id'] ?>][day_type_id]"><?php foreach($dayTypes as $t): ?><option value="<?= $t['id'] ?>" <?= $d['day_type_id']==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select>
<?php else: ?><?= htmlspecialchars((string)$d['day_type_name']) ?><?php endif; ?>
</td>
<td>
<?php if (Auth::isAdmin()): ?>
<select multiple class="form-select form-select-sm" name="day[<?= $d['id'] ?>][users][]">
<?php $ids=array_column($assigned,'id'); foreach($users as $u): ?><option value="<?= $u['id'] ?>" <?= in_array($u['id'],$ids)?'selected':'' ?>><?= htmlspecialchars($u['last_name'].' '.$u['first_name']) ?></option><?php endforeach; ?>
</select>
<?php else: foreach($assigned as $a){echo htmlspecialchars($a['last_name'].' '.$a['first_name']).'<br>';} endif; ?>
</td>
<td><?php if(Auth::isAdmin()): ?><input class="form-control form-control-sm" name="day[<?= $d['id'] ?>][morning_close]" value="<?= htmlspecialchars((string)$d['morning_close']) ?>"><?php else: ?><?= htmlspecialchars((string)$d['morning_close']) ?><?php endif; ?></td>
<td><?php if(Auth::isAdmin()): ?><input class="form-control form-control-sm" name="day[<?= $d['id'] ?>][evening_close]" value="<?= htmlspecialchars((string)$d['evening_close']) ?>"><?php else: ?><?= htmlspecialchars((string)$d['evening_close']) ?><?php endif; ?></td>
<td><?php if(Auth::isAdmin()): ?><input class="form-control form-control-sm" name="day[<?= $d['id'] ?>][notes]" value="<?= htmlspecialchars((string)$d['notes']) ?>"><?php else: ?><?= htmlspecialchars((string)$d['notes']) ?><?php endif; ?></td>
<?php if(!Auth::isAdmin()): ?><td><form method="post"><input type="hidden" name="report_day" value="<?= $d['id'] ?>"><input name="message" class="form-control form-control-sm" placeholder="Segnalazione"><button class="btn btn-sm btn-warning mt-1">Invia</button></form></td><?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
<?php if (Auth::isAdmin()): ?><button class="btn btn-success">Salva modifiche</button></form><?php endif; ?>
<a class="btn btn-outline-dark" href="./">Indietro</a>
