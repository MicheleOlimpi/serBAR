<?php $currentColor = htmlspecialchars((string) ($editing['color_hex'] ?? '#FFFFFF')); ?>
<h4>TIPI GIORNO</h4>
<br>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
  <input type="hidden" name="color_hex" id="colorHexInput" value="<?= $currentColor ?>">

  <div class="col-md-7"><input name="name" class="form-control" placeholder="nome" required value="<?= htmlspecialchars((string) ($editing['name'] ?? '')) ?>"></div>
  <div class="col-md-2">
    <button
      type="button"
      class="btn btn-outline-secondary w-100"
      data-bs-toggle="modal"
      data-bs-target="#colorPickerModal"
      id="openColorPickerBtn"
      style="border-width:2px"
    >
      Colore
    </button>
  </div>
  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-success"><?= $editing ? 'Aggiorna' : 'Aggiungi' ?></button>
    <?php if ($editing): ?><a class="btn btn-outline-secondary" href="?action=day_types">Annulla</a><?php endif; ?>
  </div>
</form>
<br>
<table class="table table-striped">
  <tr><th>NOME</th><th>COLORE</th><th>AZIONI</th></tr>
  <?php foreach ($types as $t): ?>
    <tr>
      <td><?= htmlspecialchars((string) $t['name']) ?></td>
      <td>
        <span class="d-inline-block rounded border" style="width:28px;height:28px;background-color: <?= htmlspecialchars((string) ($t['color_hex'] ?? '#FFFFFF')) ?>"></span>
        <small class="text-muted ms-1"><?= htmlspecialchars((string) ($t['color_hex'] ?? '#FFFFFF')) ?></small>
      </td>
      <td class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary" href="?action=day_types&edit=<?= (int) $t['id'] ?>" aria-label="Modifica" title="Modifica">
          <i class="fa-solid fa-pen" aria-hidden="true"></i>
        </a>
        <?php if (!(int) $t['is_locked']): ?>
          <button
            type="button"
            class="btn btn-sm btn-danger js-delete-day-type" aria-label="Elimina" title="Elimina"
            data-delete-url="?action=day_types&delete=<?= (int) $t['id'] ?>"
            data-day-type-name="<?= htmlspecialchars((string) $t['name']) ?>"
            data-bs-toggle="modal"
            data-bs-target="#deleteDayTypeModal"
          >
            <i class="fa-solid fa-trash" aria-hidden="true"></i>
          </button>
        <?php else: ?>
          <button type="button" class="btn btn-sm btn-secondary" disabled aria-label="Non eliminabile" title="Non eliminabile">
            <i class="fa-solid fa-trash" aria-hidden="true"></i>
          </button>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>

<div class="modal fade" id="colorPickerModal" tabindex="-1" aria-labelledby="colorPickerModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="colorPickerModalLabel">Scegli il colore del tipo giorno</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        <label for="colorPickerInput" class="form-label">Colore</label>
        <input type="color" class="form-control form-control-color" id="colorPickerInput" value="<?= $currentColor ?>" title="Scegli colore">
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Annulla</button>
        <button type="button" class="btn btn-primary" id="confirmColorBtn" data-bs-dismiss="modal">Conferma</button>
      </div>
    </div>
  </div>
</div>

<div class="modal fade" id="deleteDayTypeModal" tabindex="-1" aria-labelledby="deleteDayTypeModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteDayTypeModalLabel">Conferma eliminazione</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body d-flex align-items-start gap-3">
        <i class="fa-solid fa-circle-exclamation modal-icon mt-1" aria-hidden="true"></i>
        <p class="mb-0" id="deleteDayTypeMessage">Sei sicuro di voler eliminare questa tipologia di giorno?</p>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">No</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteDayTypeBtn">Sì, elimina</a>
      </div>
    </div>
  </div>
</div>


<script>
  (() => {
    const hiddenInput = document.getElementById('colorHexInput');
    const pickerInput = document.getElementById('colorPickerInput');
    const confirmBtn = document.getElementById('confirmColorBtn');
    const openBtn = document.getElementById('openColorPickerBtn');
    const deleteButtons = document.querySelectorAll('.js-delete-day-type');
    const confirmDeleteBtn = document.getElementById('confirmDeleteDayTypeBtn');
    const deleteMessage = document.getElementById('deleteDayTypeMessage');

    const paintButton = (color) => {
      openBtn.style.backgroundColor = color;
      openBtn.style.borderColor = color;
      openBtn.style.color = '#fff';
    };

    pickerInput.addEventListener('input', () => paintButton(pickerInput.value));
    confirmBtn.addEventListener('click', () => {
      hiddenInput.value = pickerInput.value;
      paintButton(pickerInput.value);
    });

    deleteButtons.forEach((button) => {
      button.addEventListener('click', () => {
        if (!confirmDeleteBtn || !deleteMessage) {
          return;
        }
        const deleteUrl = button.dataset.deleteUrl || '#';
        const dayTypeName = button.dataset.dayTypeName || 'questa tipologia di giorno';
        confirmDeleteBtn.setAttribute('href', deleteUrl);
        deleteMessage.textContent = `Sei sicuro di voler eliminare "${dayTypeName}"?`;
      });
    });

    paintButton(hiddenInput.value);
  })();
</script>
