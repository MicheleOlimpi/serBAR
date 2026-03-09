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

<h4>GESTIONE SEGNALAZIONI</h4>

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
        <th>CREATA IL</th>
        <th>UTENTE</th>
        <th>MESSAGGIO</th>
        <th>STATO</th>
        <th>AZIONI</th>
      </tr>
    </thead>
    <tbody>
      <?php foreach ($notifications as $n): ?>
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
            <button
              type="button"
              class="btn btn-sm btn-outline-danger js-delete-notification"
              data-delete-url="?action=notifications&delete=<?= (int) $n['id'] ?>"
              data-bs-toggle="modal"
              data-bs-target="#deleteNotificationModal"
            >
              Elimina
            </button>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</div>

<div class="modal fade" id="deleteNotificationModal" tabindex="-1" aria-labelledby="deleteNotificationModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header border-0 pb-0">
        <h5 class="modal-title d-flex align-items-center gap-2" id="deleteNotificationModalLabel">
          <i class="fa-solid fa-circle-exclamation text-danger" aria-hidden="true"></i>
          Conferma eliminazione segnalazione
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body pt-2">
        Sei sicuro di voler eliminare la segnalazione?
      </div>
      <div class="modal-footer border-0 pt-0">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteNotificationBtn">Sì, elimina</a>
      </div>
    </div>
  </div>
</div>

<script>
  const confirmDeleteNotificationBtn = document.getElementById('confirmDeleteNotificationBtn');

  document.querySelectorAll('.js-delete-notification').forEach((button) => {
    button.addEventListener('click', () => {
      if (confirmDeleteNotificationBtn) {
        confirmDeleteNotificationBtn.setAttribute('href', button.dataset.deleteUrl || '#');
      }
    });
  });
</script>
