<?php use App\Core\Auth; $print = isset($_GET['print']); if($print): ?><script>window.onload=()=>window.print()</script><?php endif; ?>
<h4>Tabellone <?= sprintf('%02d/%04d',$board['month'],$board['year']) ?></h4>
<style>
  .day-cell { min-width: 170px; }
  .shift-grid {
    display: grid;
    gap: .5rem;
    grid-template-columns: minmax(120px, auto) minmax(280px, 1fr) minmax(220px, 1fr);
    align-items: start;
  }
  @media (max-width: 1199.98px) {
    .shift-grid { grid-template-columns: 1fr; }
  }
  .day-badge {
    border-radius: .5rem;
    padding: .6rem;
    min-height: 100%;
    text-align: center;
    display: flex;
    flex-direction: column;
    align-items: center;
    justify-content: center;
  }
  .day-meta { font-size: 0.82rem; line-height: 1.3; }
  .day-type-selector { max-width: 140px; }
</style>
<?php if (Auth::isAdmin()): ?><form method="post"><?php endif; ?>
<table class="table table-sm table-bordered bg-white">
<tr><th>Giorno</th><th>Turni giornalieri</th><th>Annotazioni</th><?php if(!Auth::isAdmin()):?><th>Segnala</th><?php endif; ?></tr>
<?php foreach($days as $d): $shifts = $dayShifts[$d['id']] ?? []; ?>
<?php if ($shifts !== []): usort($shifts, static function (array $left, array $right): int {
  return [(int) ($left['priority'] ?? 0), (string) ($left['start_time'] ?? '')] <=> [(int) ($right['priority'] ?? 0), (string) ($right['start_time'] ?? '')];
}); endif; ?>
<tr>
<td class="day-cell">
  <div class="day-badge" style="background-color: <?= htmlspecialchars((string) ($d['day_type_color'] ?? '#6c757d')) ?>;">
    <div class="day-number"><?= (int) date('j', strtotime($d['day_date'])) ?></div>
    <div class="day-meta mt-1"><?= htmlspecialchars($d['weekday_name']) ?></div>
    <?php if (!empty($d['recurrence_name'])): ?><div class="day-meta"><?= htmlspecialchars((string) $d['recurrence_name']) ?></div><?php endif; ?>
    <div class="day-meta mt-1">
      <?php if (Auth::isAdmin()): ?>
        <label class="form-label mb-1 small">Tipo giorno</label>
        <select class="form-select form-select-sm day-type-selector" name="day[<?= $d['id'] ?>][day_type_id]">
          <?php foreach($dayTypes as $t): ?><option value="<?= $t['id'] ?>" <?= $d['day_type_id']==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
        </select>
      <?php else: ?>
        <strong><?= htmlspecialchars((string) ($d['day_type_name'] ?? '-')) ?></strong>
      <?php endif; ?>
    </div>
  </div>
</td>
<td>
  <?php if ($shifts === []): ?>
    <span class="text-muted">Nessun turno configurato per questo tipo giorno.</span>
  <?php else: ?>
    <?php foreach ($shifts as $shift): ?>
      <div class="border rounded p-2 mb-2">
        <?php if (Auth::isAdmin()): ?>
          <div class="shift-grid">
            <div>
              <div class="small fw-semibold">
                <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
              </div>
            </div>
            <div>
              <input id="volunteers-<?= (int) $shift['id'] ?>" class="form-control form-control-sm mb-2" name="day[<?= $d['id'] ?>][shifts][<?= (int) $shift['id'] ?>][volunteers]" value="<?= htmlspecialchars((string) ($shift['volunteers'] ?? '')) ?>" placeholder="Es. M. Rossi A. Bianchi">
              <div class="input-group input-group-sm volunteer-picker" data-target="volunteers-<?= (int) $shift['id'] ?>">
                <input type="text" class="form-control" list="users-list" placeholder="Aggiungi dalla lista utenti">
                <button class="btn btn-outline-secondary" type="button">Aggiungi</button>
              </div>
            </div>
            <div>
              <input id="responsabile-<?= (int) $shift['id'] ?>" class="form-control form-control-sm mb-2" name="day[<?= $d['id'] ?>][shifts][<?= (int) $shift['id'] ?>][responsabile_chiusura]" value="<?= htmlspecialchars((string) ($shift['responsabile_chiusura'] ?? '')) ?>" <?= empty($shift['closes_bar']) ? 'readonly' : '' ?>>
              <div class="input-group input-group-sm responsible-picker" data-target="responsabile-<?= (int) $shift['id'] ?>">
                <input type="text" class="form-control" list="users-list" placeholder="Seleziona utente" <?= empty($shift['closes_bar']) ? 'disabled' : '' ?>>
                <button class="btn btn-outline-secondary" type="button" <?= empty($shift['closes_bar']) ? 'disabled' : '' ?>>Imposta</button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <div class="small fw-semibold mb-1">
            <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
          </div>
          <div><?= nl2br(htmlspecialchars((string) ($shift['volunteers'] ?: '-'))) ?></div>
          <?php if (!empty($shift['closes_bar'])): ?>
            <div class="small text-muted mt-1">Responsabile chiusura: <?= htmlspecialchars((string) ($shift['responsabile_chiusura'] ?: '-')) ?></div>
          <?php endif; ?>
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

<?php if (Auth::isAdmin()): ?>
  <datalist id="users-list">
    <?php foreach ($activeUsers as $activeUser): ?>
      <option value="<?= htmlspecialchars(trim($activeUser['first_name'] . ' ' . $activeUser['last_name'])) ?>"></option>
    <?php endforeach; ?>
  </datalist>

  <script>
    const userAbbreviations = {
      <?php foreach ($activeUsers as $index => $activeUser):
        $fullName = trim($activeUser['first_name'] . ' ' . $activeUser['last_name']);
        $firstInitial = strtoupper(substr(trim((string) $activeUser['first_name']), 0, 1));
        $lastName = trim((string) $activeUser['last_name']);
        $abbreviation = trim(($firstInitial ? $firstInitial . '. ' : '') . $lastName);
      ?><?= $index > 0 ? ',' : '' ?>
      <?= json_encode($fullName) ?>: <?= json_encode($abbreviation) ?>
      <?php endforeach; ?>
    };

    document.querySelectorAll('.volunteer-picker').forEach(function (picker) {
      const input = picker.querySelector('input');
      const button = picker.querySelector('button');
      const target = document.getElementById(picker.dataset.target);

      button.addEventListener('click', function () {
        const selectedUser = input.value.trim();
        if (!selectedUser || !target) {
          return;
        }

        const value = userAbbreviations[selectedUser] || selectedUser;

        target.value = target.value.trim() ? target.value.trim() + ' ' + value : value;
        input.value = '';
      });
    });

    document.querySelectorAll('.responsible-picker').forEach(function (picker) {
      const input = picker.querySelector('input');
      const button = picker.querySelector('button');
      const target = document.getElementById(picker.dataset.target);

      button.addEventListener('click', function () {
        const selectedUser = input.value.trim();
        if (!selectedUser || !target || button.disabled) {
          return;
        }

        target.value = userAbbreviations[selectedUser] || selectedUser;
        input.value = '';
      });
    });
  </script>

  <button class="btn btn-success">Salva modifiche</button>
</form>
<?php endif; ?>
<a class="btn btn-outline-dark" href="./">Indietro</a>
