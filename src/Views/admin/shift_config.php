<h4>TURNI GIORNALIERI</h4>
<br>
<?php if (!empty($error)): ?>
  <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
<?php elseif (!empty($saved)): ?>
  <div class="alert alert-success">Turno salvato correttamente.</div>
<?php endif; ?>

<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
  <div class="col-md-3">
    <select name="day_type_id" class="form-select" required>
      <option value="">Tipo giorno</option>
      <?php foreach ($dayTypes as $type): ?>
        <option value="<?= (int) $type['id'] ?>" <?= (int) ($editing['day_type_id'] ?? 0) === (int) $type['id'] ? 'selected' : '' ?>>
          <?= htmlspecialchars($type['name']) ?>
        </option>
      <?php endforeach; ?>
    </select>
  </div>
  <div class="col-md-2"><input type="time" name="start_time" class="form-control" required value="<?= htmlspecialchars(substr((string) ($editing['start_time'] ?? ''), 0, 5)) ?>"></div>
  <div class="col-md-2"><input type="time" name="end_time" class="form-control" required value="<?= htmlspecialchars(substr((string) ($editing['end_time'] ?? ''), 0, 5)) ?>"></div>
  <div class="col-md-2"><input type="number" min="1" name="priority" class="form-control" required value="<?= (int) ($editing['priority'] ?? 1) ?>"></div>
  <div class="col-md-3 form-check d-flex align-items-center ps-4">
    <input class="form-check-input me-2" type="checkbox" name="closes_bar" id="closes_bar" value="1" <?= !empty($editing['closes_bar']) ? 'checked' : '' ?>>
    <label class="form-check-label" for="closes_bar">A fine turno il bar chiude</label>
  </div>
  <div class="col-md-12 d-flex gap-2 mt-2">
    <button class="btn btn-success"><?= $editing ? 'Aggiorna turno' : 'Aggiungi turno' ?></button>
    <?php if ($editing): ?><a class="btn btn-outline-secondary" href="?action=shift_config">Annulla</a><?php endif; ?>
  </div>
</form>

<table class="table table-striped">
  <tr><th>TIPO GIORNO</th><th>INIZIO</th><th>FINE</th><th>CHIUSURA BAR</th><th>PRIORITÀ</th><th>&nbsp</th></tr>
  <?php foreach($shifts as $shift): ?>
    <tr>
      <td><?= htmlspecialchars($shift['day_type_name']) ?></td>
      <td><?= htmlspecialchars(substr((string) $shift['start_time'], 0, 5)) ?></td>
      <td><?= htmlspecialchars(substr((string) $shift['end_time'], 0, 5)) ?></td>
      <td><?= !empty($shift['closes_bar']) ? 'Sì' : 'No' ?></td>
      <td><?= (int) $shift['priority'] ?></td>
      <td class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary" href="?action=shift_config&edit=<?= (int) $shift['id'] ?>" aria-label="Modifica" title="Modifica"><i class="fa-solid fa-pen" aria-hidden="true"></i></a>
        <button
          type="button"
          class="btn btn-sm btn-danger js-shift-delete-btn" aria-label="Elimina" title="Elimina"
          data-bs-toggle="modal"
          data-bs-target="#deleteShiftModal"
          data-delete-url="?action=shift_config&delete=<?= (int) $shift['id'] ?>"
        >
          <i class="fa-solid fa-trash" aria-hidden="true"></i>
        </button>
      </td>
    </tr>
  <?php endforeach; ?>
</table>


<div class="modal fade" id="deleteShiftModal" tabindex="-1" aria-labelledby="deleteShiftModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteShiftModalLabel">Conferma eliminazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body d-flex align-items-center gap-2">
        <i class="fa-solid fa-circle-exclamation modal-icon"></i>
        <span>Si è sicuri di voler eliminare il tipo di giorno?</span>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
        <a class="btn btn-danger" href="#" id="confirmShiftDeleteBtn">Sì, elimina</a>
      </div>
    </div>
  </div>
</div>

<script>
  document.addEventListener('DOMContentLoaded', function () {
    var deleteModal = document.getElementById('deleteShiftModal');
    if (!deleteModal) {
      return;
    }

    var confirmDeleteBtn = document.getElementById('confirmShiftDeleteBtn');
    deleteModal.addEventListener('show.bs.modal', function (event) {
      var triggerButton = event.relatedTarget;
      var deleteUrl = triggerButton ? triggerButton.getAttribute('data-delete-url') : null;
      if (confirmDeleteBtn && deleteUrl) {
        confirmDeleteBtn.setAttribute('href', deleteUrl);
      }
    });
  });
</script>
