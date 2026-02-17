<?php
$editingRow = $editing ?? null;
$isEditing = $editingRow !== null;

$statusLabels = [
    'inviata' => 'Inviato',
    'letto' => 'Letto',
    'in_corso' => 'In corso',
    'chiuso' => 'Chiuso',
];

$statusBadgeMap = [
    'inviata' => 'text-bg-secondary',
    'letto' => 'text-bg-info',
    'in_corso' => 'text-bg-warning text-dark',
    'chiuso' => 'text-bg-success',
];
?>

<h4>Gestione segnalazioni</h4>

<div class="card mb-3">
  <div class="card-body">
    <h5 class="card-title"><?= $isEditing ? 'Modifica segnalazione' : 'Nuova segnalazione' ?></h5>
    <form method="post" class="row g-2">
      <?php if ($isEditing): ?>
        <input type="hidden" name="id" value="<?= (int) $editingRow['id'] ?>">
      <?php endif; ?>

      <div class="col-md-3">
        <label class="form-label">Utente</label>
        <select name="user_id" class="form-select" required>
          <option value="">Seleziona</option>
          <?php foreach ($users as $u): ?>
            <option value="<?= (int) $u['id'] ?>" <?= $isEditing && (int) $editingRow['user_id'] === (int) $u['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($u['username']) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-4">
        <label class="form-label">Turno</label>
        <select name="board_day_id" class="form-select" required>
          <option value="">Seleziona</option>
          <?php foreach ($boardDays as $day): ?>
            <?php $label = $day['day_date'] . ' - ' . sprintf('%02d/%04d', $day['month'], $day['year']); ?>
            <option value="<?= (int) $day['id'] ?>" <?= $isEditing && (int) $editingRow['board_day_id'] === (int) $day['id'] ? 'selected' : '' ?>>
              <?= htmlspecialchars($label) ?>
            </option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-2">
        <label class="form-label">Stato</label>
        <select name="status" class="form-select" required>
          <?php foreach ($statuses as $status): ?>
            <option value="<?= $status ?>" <?= $isEditing && $editingRow['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($statusLabels[$status] ?? $status) ?></option>
          <?php endforeach; ?>
        </select>
      </div>

      <div class="col-md-12">
        <label class="form-label">Messaggio</label>
        <textarea name="message" rows="3" class="form-control" required><?= htmlspecialchars((string) ($editingRow['message'] ?? '')) ?></textarea>
      </div>

      <div class="col-12">
        <button class="btn btn-success"><?= $isEditing ? 'Aggiorna' : 'Crea' ?></button>
        <?php if ($isEditing): ?>
          <a class="btn btn-outline-secondary" href="?action=notifications">Annulla</a>
        <?php endif; ?>
      </div>
    </form>
  </div>
</div>

<div class="table-responsive">
  <table class="table table-sm table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th>
        <th>Creata il</th>
        <th>Utente</th>
        <th>Turno</th>
        <th>Messaggio</th>
        <th>Stato</th>
        <th>Azioni</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($notifications as $n): ?>
        <?php $turno = $n['day_date'] ? $n['day_date'] : '-'; ?>
        <?php
          $createdAtFormatted = (string) $n['created_at'];
          if (!empty($n['created_at'])) {
              try {
                  $createdAtFormatted = (new DateTimeImmutable((string) $n['created_at']))->format('d/m/Y H:i');
              } catch (Exception) {
                  $createdAtFormatted = (string) $n['created_at'];
              }
          }
        ?>
        <tr>
          <td><?= (int) $n['id'] ?></td>
          <td><?= htmlspecialchars($createdAtFormatted) ?></td>
          <td><?= htmlspecialchars((string) $n['username']) ?></td>
          <td><?= htmlspecialchars($turno) ?></td>
          <td><?= htmlspecialchars((string) $n['message']) ?></td>
          <td>
            <div class="d-flex align-items-center gap-2">
              <?php $statusClass = $statusBadgeMap[$n['status']] ?? 'text-bg-secondary'; ?>
              <span class="badge <?= $statusClass ?>"><?= htmlspecialchars($statusLabels[$n['status']] ?? $n['status']) ?></span>
              <form method="post" class="d-flex gap-1">
                <input type="hidden" name="quick_status_id" value="<?= (int) $n['id'] ?>">
                <select name="quick_status" class="form-select form-select-sm" onchange="this.form.submit()">
                  <?php foreach ($statuses as $status): ?>
                    <option value="<?= $status ?>" <?= $n['status'] === $status ? 'selected' : '' ?>><?= htmlspecialchars($statusLabels[$status] ?? $status) ?></option>
                  <?php endforeach; ?>
                </select>
              </form>
            </div>
          </td>
          <td>
            <a class="btn btn-sm btn-outline-primary" href="?action=notifications&edit=<?= (int) $n['id'] ?>">Modifica</a>
            <a class="btn btn-sm btn-outline-danger" href="?action=notifications&delete=<?= (int) $n['id'] ?>" onclick="return confirm('Eliminare la segnalazione?')">Elimina</a>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<a class="btn btn-outline-dark" href="./">Indietro</a>
