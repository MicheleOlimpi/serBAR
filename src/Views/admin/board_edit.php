<?php use App\Core\Auth; $print = isset($_GET['print']); if($print): ?><script>window.onload=()=>window.print()</script><?php endif; ?>
<h4>Tabellone <?= sprintf('%02d/%04d',$board['month'],$board['year']) ?></h4>
<style>
  .day-cell {
    min-width: 120px;
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
<tr><th>Giorno</th><th>Tipo giorno</th><th>Turni giornalieri</th><th>Annotazioni</th><?php if(!Auth::isAdmin()):?><th>Segnala</th><?php endif; ?></tr>
<?php foreach($days as $d): $shifts = $dayShifts[$d['id']] ?? []; ?>
<tr>
<td class="day-cell">
  <div class="day-number"><?= (int) date('j', strtotime($d['day_date'])) ?></div>
  <div class="day-meta"><?= htmlspecialchars($d['weekday_name']) ?></div>
  <?php if (!empty($d['recurrence_name'])): ?><div class="day-meta"><?= htmlspecialchars((string) $d['recurrence_name']) ?></div><?php endif; ?>
  <div class="day-meta"><strong><?= htmlspecialchars((string) ($d['day_type_name'] ?? '-')) ?></strong></div>
</td>
<td>
<?php if (Auth::isAdmin()): ?><select class="form-select form-select-sm" name="day[<?= $d['id'] ?>][day_type_id]"><?php foreach($dayTypes as $t): ?><option value="<?= $t['id'] ?>" <?= $d['day_type_id']==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?></select>
<?php else: ?><?= htmlspecialchars((string)$d['day_type_name']) ?><?php endif; ?>
</td>
<td>
  <?php if ($shifts === []): ?>
    <span class="text-muted">Nessun turno configurato per questo tipo giorno.</span>
  <?php else: ?>
    <?php foreach ($shifts as $shift): ?>
      <div class="border rounded p-2 mb-2">
        <div class="small fw-semibold mb-1">
          <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
          <?php if (!empty($shift['closes_bar'])): ?> · Chiusura bar<?php endif; ?>
        </div>
        <?php if (Auth::isAdmin()): ?>
          <textarea class="form-control form-control-sm" rows="2" name="day[<?= $d['id'] ?>][shifts][<?= (int) $shift['id'] ?>][volunteers]" placeholder="Volontari"><?= htmlspecialchars((string) ($shift['volunteers'] ?? '')) ?></textarea>
        <?php else: ?>
          <div><?= nl2br(htmlspecialchars((string) ($shift['volunteers'] ?: '-'))) ?></div>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
</td>
<td><?php if(Auth::isAdmin()): ?><input class="form-control form-control-sm" name="day[<?= $d['id'] ?>][notes]" value="<?= htmlspecialchars((string)$d['notes']) ?>"><?php else: ?><?= htmlspecialchars((string)$d['notes']) ?><?php endif; ?></td>
<?php if(!Auth::isAdmin()): ?><td><form method="post"><input type="hidden" name="report_day" value="<?= $d['id'] ?>"><input name="message" class="form-control form-control-sm" placeholder="Segnalazione"><button class="btn btn-sm btn-warning mt-1">Invia</button></form></td><?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
<?php if (Auth::isAdmin()): ?><button class="btn btn-success">Salva modifiche</button></form><?php endif; ?>
<a class="btn btn-outline-dark" href="./">Indietro</a>
