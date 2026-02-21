<?php $currentColor = htmlspecialchars((string) ($editing['color_hex'] ?? '#6c757d')); ?>
<h4>Tipologie di giorno</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
  <input type="hidden" name="color_hex" id="colorHexInput" value="<?= $currentColor ?>">

  <div class="col-md-4"><input name="name" class="form-control" placeholder="nome" required value="<?= htmlspecialchars((string) ($editing['name'] ?? '')) ?>"></div>
  <div class="col-md-3"><input name="code" class="form-control" placeholder="codice" required value="<?= htmlspecialchars((string) ($editing['code'] ?? '')) ?>"></div>
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

<table class="table table-striped">
  <tr><th>Nome</th><th>Codice</th><th>Colore</th><th>Azioni</th></tr>
  <?php foreach ($types as $t): ?>
    <tr>
      <td><?= htmlspecialchars((string) $t['name']) ?></td>
      <td><?= htmlspecialchars((string) $t['code']) ?></td>
      <td>
        <span class="d-inline-block rounded border" style="width:28px;height:28px;background-color: <?= htmlspecialchars((string) ($t['color_hex'] ?? '#6c757d')) ?>"></span>
        <small class="text-muted ms-1"><?= htmlspecialchars((string) ($t['color_hex'] ?? '#6c757d')) ?></small>
      </td>
      <td class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary" href="?action=day_types&edit=<?= (int) $t['id'] ?>">Modifica</a>
        <?php if (!(int) $t['is_locked'] && !in_array(strtolower((string) $t['code']), ['feriale', 'prefestivo', 'festivo'], true)): ?>
          <a class="btn btn-sm btn-danger" href="?action=day_types&delete=<?= (int) $t['id'] ?>" onclick="return confirm('Eliminare questa tipologia di giorno?')">Elimina</a>
        <?php else: ?>
          <span class="badge bg-secondary">Non eliminabile</span>
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

<a class="btn btn-outline-dark" href="./">Indietro</a>

<script>
  (() => {
    const hiddenInput = document.getElementById('colorHexInput');
    const pickerInput = document.getElementById('colorPickerInput');
    const confirmBtn = document.getElementById('confirmColorBtn');
    const openBtn = document.getElementById('openColorPickerBtn');

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

    paintButton(hiddenInput.value);
  })();
</script>
