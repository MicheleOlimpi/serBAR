<?php
$editingRow = $editing ?? null;
$isEditing = $editingRow !== null;

$kindLabels = [
    'feriale' => 'Feriale',
    'festivo' => 'Festivo',
    'speciale' => 'Speciale',
];

$selectedKind = 'feriale';
if ($isEditing) {
    if ((int) $editingRow['is_special'] === 1) {
        $selectedKind = 'speciale';
    } elseif ((int) $editingRow['is_holiday'] === 1) {
        $selectedKind = 'festivo';
    }
}
?>

<h4>Calendario annuale ricorrenze</h4>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title"><?= $isEditing ? 'Modifica giorno' : 'Nuovo giorno calendario' ?></h5>
    <form method="post" class="row g-2">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int) $editingRow['id'] ?>">
      <?php endif; ?>
      <div class="col-md-2">
        <label class="form-label">Data</label>
        <input type="date" name="day_date" class="form-control" required value="<?= htmlspecialchars((string) ($editingRow['day_date'] ?? '')) ?>">
      </div>

      <div class="col-md-4">
        <label class="form-label">Ricorrenza</label>
        <input name="recurrence_name" class="form-control" placeholder="Es. Ferragosto" value="<?= htmlspecialchars((string) ($editingRow['recurrence_name'] ?? '')) ?>">
      </div>

      <div class="col-md-2">
        <label class="form-label">Tipo giornata</label>
        <select name="calendar_kind" class="form-select" required>
          <?php foreach ($calendarKinds as $kind): ?>
            <option value="<?= $kind ?>" <?= $kind === $selectedKind ? 'selected' : '' ?>><?= htmlspecialchars($kindLabels[$kind] ?? $kind) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-3">
        <label class="form-label">Tipo giorno associato</label>
        <select name="day_type_id" class="form-select">
          <option value="">Auto da tipo giornata</option>
          <?php foreach ($types as $t): ?>
            <option value="<?= (int) $t['id'] ?>" <?= $isEditing && (int) $editingRow['day_type_id'] === (int) $t['id'] ? 'selected' : '' ?>><?= htmlspecialchars($t['name']) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-1 d-flex align-items-end">
        <button class="btn btn-success w-100"><?= $isEditing ? 'Aggiorna' : 'Salva' ?></button>
      </div>

      <?php if ($isEditing): ?>
        <div class="col-12">
          <a class="btn btn-outline-secondary btn-sm" href="?action=calendar">Annulla modifica</a>
        </div>
      <?php endif; ?>
    </form>
  </div>
</div>

<form method="get" class="mb-2 d-flex gap-2">
  <input type="hidden" name="action" value="calendar">
  <input type="month" name="month" class="form-control" style="max-width: 220px">
  <button class="btn btn-outline-primary btn-sm">Filtra</button>
</form>

<div class="table-responsive">
  <table class="table table-sm table-striped align-middle">
    <thead>
      <tr>
        <th>Data</th>
        <th>Ricorrenza</th>
        <th>Tipo giornata</th>
        <th>Tipo giorno collegato</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($days as $d): ?>
        <?php
          $kind = 'feriale';
          if ((int) $d['is_special'] === 1) {
              $kind = 'speciale';
          } elseif ((int) $d['is_holiday'] === 1) {
              $kind = 'festivo';
          }
        ?>
        <tr>
          <td><?= htmlspecialchars($d['day_date']) ?></td>
          <td><?= htmlspecialchars((string) $d['recurrence_name']) ?></td>
          <td><?= htmlspecialchars($kindLabels[$kind] ?? $kind) ?></td>
          <td><?= htmlspecialchars((string) $d['day_type_name']) ?></td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="?action=calendar&edit=<?= (int) $d['id'] ?>">Modifica</a>
            <a class="btn btn-sm btn-outline-danger" href="?action=calendar&delete=<?= (int) $d['id'] ?>" onclick="return confirm('Eliminare questo giorno di calendario?')">Elimina</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<a class="btn btn-outline-dark" href="./">Indietro</a>
