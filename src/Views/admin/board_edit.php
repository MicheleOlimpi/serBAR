<?php use App\Core\Auth; $print = isset($_GET['print']); $generate = isset($_GET['generate']) && $_GET['generate'] === '1'; if($print): ?><script>window.onload=()=>window.print()</script><?php endif; ?>
<?php
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
$monthName = $monthNames[(int) ($board['month'] ?? 0)] ?? sprintf('%02d', (int) ($board['month'] ?? 0));
$boardGeneratedHeaderTitle = 'SERVIZIO BAR';
$boardGeneratedHeaderSubtitle = $monthName . ' ' . (int) ($board['year'] ?? 0);
?>
<?php if (!$generate): ?>
<h4>TABELLONE <?= htmlspecialchars($monthName) ?> <?= (int) ($board['year'] ?? 0) ?></h4>
<?php endif; ?>
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
  .day-number { font-size: 1.75rem; font-weight: 700; line-height: 1; }
  .responsible-section-hidden { display: none; }

</style>
<?php if (Auth::isAdmin() && !$generate): ?><form method="post"><?php endif; ?>
<div class="<?= $generate ? 'board-generated-wrap' : '' ?>">
<table class="<?= $generate ? 'board-generated-table bg-white' : 'table table-sm table-bordered bg-white' ?>">
<?php if ($generate): ?>
<tr>
  <td colspan="2" class="board-generated-header">
    <div class="board-generated-header-title"><?= htmlspecialchars($boardGeneratedHeaderTitle) ?></div>
    <div class="board-generated-header-subtitle"><?= htmlspecialchars($boardGeneratedHeaderSubtitle) ?></div>
    <br>
  </td>
</tr>
<?php else: ?>
<tr><?php if(!Auth::isAdmin()):?><th>Segnala</th><?php endif; ?></tr>
<?php endif; ?>
<?php foreach($days as $d): $shifts = $dayShifts[$d['id']] ?? []; ?>
<?php if ($shifts !== []): usort($shifts, static function (array $left, array $right): int {
  return [(int) ($left['priority'] ?? 0), (string) ($left['start_time'] ?? '')] <=> [(int) ($right['priority'] ?? 0), (string) ($right['start_time'] ?? '')];
}); endif; ?>
<tr>
<td class="<?= $generate ? 'board-generated-day' : 'day-cell' ?>">
  <div class="day-badge" style="background-color: <?= htmlspecialchars((string) ($d['day_type_color'] ?? '#6c757d')) ?>;">
    <div class="day-number"><?= (int) date('j', strtotime($d['day_date'])) ?></div>
    <div class="day-weekday mt-2"><?= htmlspecialchars($d['weekday_name']) ?></div>
    <div class="day-meta mt-1"><?= htmlspecialchars((string) ($d['recurrence_name'] ?: '--')) ?></div>
    <div class="day-meta"><?= htmlspecialchars((string) ($d['santo'] ?: '--')) ?></div>
    <?php if (!$generate): ?>
      <div class="day-meta mt-2">
        <?php if (Auth::isAdmin()): ?>
          <select class="form-select form-select-sm day-type-selector" name="day[<?= $d['id'] ?>][day_type_id]">
            <?php foreach($dayTypes as $t): ?><option value="<?= $t['id'] ?>" <?= $d['day_type_id']==$t['id']?'selected':'' ?>><?= htmlspecialchars($t['name']) ?></option><?php endforeach; ?>
          </select>
        <?php else: ?>
          <strong><?= htmlspecialchars((string) ($d['day_type_name'] ?? '-')) ?></strong>
        <?php endif; ?>
      </div>
    <?php endif; ?>
  </div>
</td>
<td class="<?= $generate ? 'board-generated-shifts' : '' ?>">
  <div class="<?= $generate ? 'board-generated-shifts-inner' : '' ?>">
  <?php if ($shifts === []): ?>
    <span class="text-muted">Nessun turno configurato per questo tipo giorno.</span>
  <?php else: ?>
    <?php foreach ($shifts as $shift): ?>
      <div class="<?= $generate ? 'board-generated-shift-row' : 'border rounded p-2 mb-2' ?>">
        <?php if (!$generate && Auth::isAdmin()): ?>
          <div class="shift-grid">
            <div>
              <div class="small fw-semibold">
                <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
              </div>
            </div>
            <div>
              <input id="volunteers-<?= (int) $shift['id'] ?>" class="form-control form-control-sm mb-2" name="day[<?= $d['id'] ?>][shifts][<?= (int) $shift['id'] ?>][volunteers]" value="<?= htmlspecialchars((string) ($shift['volunteers'] ?? '')) ?>" placeholder="Volontari in turno">
              <div class="input-group input-group-sm volunteer-picker" data-target="volunteers-<?= (int) $shift['id'] ?>">
                <input type="text" class="form-control volunteer-picker-input" list="users-list" placeholder="Seleziona volontario">
                <button class="btn btn-outline-success" type="button" aria-label="Aggiungi volontario"><i class="fa-solid fa-circle-plus text-success" aria-hidden="true"></i></button>
              </div>
            </div>
            <div class="responsible-section <?= empty($shift['closes_bar']) ? 'responsible-section-hidden' : '' ?>">
              <input id="responsabile-<?= (int) $shift['id'] ?>" class="form-control form-control-sm mb-2" name="day[<?= $d['id'] ?>][shifts][<?= (int) $shift['id'] ?>][responsabile_chiusura]" value="<?= htmlspecialchars((string) ($shift['responsabile_chiusura'] ?? '')) ?>" <?= empty($shift['closes_bar']) ? 'disabled' : '' ?> placeholder="Chiusura">
              <div class="input-group input-group-sm responsible-picker" data-target="responsabile-<?= (int) $shift['id'] ?>">
                <input type="text" class="form-control responsible-picker-input" list="users-list" placeholder="Seleziona chiusura" <?= empty($shift['closes_bar']) ? 'disabled' : '' ?>>
                <button class="btn btn-outline-success" type="button" <?= empty($shift['closes_bar']) ? 'disabled' : '' ?> aria-label="Imposta responsabile"><i class="fa-solid fa-circle-plus text-success" aria-hidden="true"></i></button>
              </div>
            </div>
          </div>
        <?php else: ?>
          <?php if ($generate): ?>
            <div class="board-generated-shift-grid">
              <div class="board-generated-shift-cell board-generated-shift-time">
                <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
              </div>
              <div class="board-generated-shift-cell board-generated-volunteers"><?= nl2br(htmlspecialchars((string) ($shift['volunteers'] ?: '-'))) ?></div>
              <div class="board-generated-shift-cell board-generated-closure">
                <?= htmlspecialchars(!empty($shift['closes_bar']) ? (string) ($shift['responsabile_chiusura'] ?: '--') : '') ?>
              </div>
            </div>
          <?php else: ?>
            <div class="small fw-semibold mb-1">
              <?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?> - <?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?>
            </div>
            <div><?= nl2br(htmlspecialchars((string) ($shift['volunteers'] ?: '-'))) ?></div>
            <?php if (!empty($shift['closes_bar'])): ?>
              <div><?= htmlspecialchars((string) ($shift['responsabile_chiusura'] ?: '--')) ?></div>
            <?php endif; ?>
          <?php endif; ?>
        <?php endif; ?>
      </div>
    <?php endforeach; ?>
  <?php endif; ?>
  </div>
</td>
<?php if (!$generate): ?>
<td><?php if(Auth::isAdmin()): ?><input class="form-control form-control-sm" name="day[<?= $d['id'] ?>][notes]" value="<?= htmlspecialchars((string)$d['notes']) ?>" placeholder="annotazioni"><?php else: ?><?= htmlspecialchars((string)$d['notes']) ?><?php endif; ?></td>
<?php if(!Auth::isAdmin()): ?><td><form method="post"><input type="hidden" name="report_day" value="<?= $d['id'] ?>"><input name="message" class="form-control form-control-sm" placeholder="Segnalazione"><button class="btn btn-sm btn-warning mt-1">Invia</button></form></td><?php endif; ?>
<?php endif; ?>
</tr>
<?php endforeach; ?>
</table>
</div>

<?php if (Auth::isAdmin() && !$generate): ?>
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
