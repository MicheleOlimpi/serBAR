<?php
$currentMonth = (int) date('n');
$currentYear = (int) date('Y');
$defaultMonth = $currentMonth < 12 ? $currentMonth + 1 : 1;
$defaultYear = $currentMonth < 12 ? $currentYear : $currentYear + 1;
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
?>

<h4>GESTIONE TABELLONI</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="create_board" value="1">
  <div class="col-md-3">
    <label class="form-label">Mese</label>
    <select name="month" class="form-select" required>
      <?php for ($month = 1; $month <= 12; $month++): ?>
        <option value="<?= $month ?>" <?= $month === $defaultMonth ? 'selected' : '' ?>><?= $monthNames[$month] ?></option>
      <?php endfor; ?>
    </select>
  </div>
  <div class="col-md-2">
    <label class="form-label">Anno</label>
    <input type="text" name="year" class="form-control" inputmode="numeric" pattern="\d{4}" maxlength="4" value="<?= $defaultYear ?>" required>
  </div>
  <div class="col-md-2"><button class="btn btn-success">GENERA</button></div>
</form>
<table class="table table-striped"><tr><th>Tabellone</th><th>Azioni</th></tr>
<?php foreach($boards as $b): ?>
<tr><td><?= ($monthNames[(int) $b['month']] ?? sprintf('%02d', $b['month'])) . ' ' . $b['year'] ?></td><td>
<a class="btn btn-sm btn-primary" href="?action=board_edit&id=<?= $b['id'] ?>">MODIFICA</a>
<a class="btn btn-sm btn-secondary" href="?action=board_edit&id=<?= $b['id'] ?>&print=1" target="_blank">STAMPA</a>
<button type="button" class="btn btn-sm btn-info text-white" disabled>GENERA CARTELLONE</button>
<button
  type="button"
  class="btn btn-sm btn-danger js-delete-board"
  data-delete-url="?action=boards&delete=<?= (int) $b['id'] ?>"
  data-board-name="<?= htmlspecialchars(($monthNames[(int) $b['month']] ?? sprintf('%02d', $b['month'])) . ' ' . $b['year']) ?>"
  data-bs-toggle="modal"
  data-bs-target="#deleteBoardModal"
>
  ELIMINA
</button>
</td></tr>
<?php endforeach; ?>
</table>

<div class="modal fade" id="deleteBoardModal" tabindex="-1" aria-labelledby="deleteBoardModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="deleteBoardModalLabel">
          <i class="fa-solid fa-triangle-exclamation modal-icon" aria-hidden="true"></i>
          Conferma eliminazione tabellone
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        Sei sicuro di voler eliminare il tabellone <strong id="deleteBoardName"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteBoardBtn">Sì, elimina</a>
      </div>
    </div>
  </div>
</div>

<script>
  const deleteBoardNameElement = document.getElementById('deleteBoardName');
  const confirmDeleteBoardBtn = document.getElementById('confirmDeleteBoardBtn');

  document.querySelectorAll('.js-delete-board').forEach((button) => {
    button.addEventListener('click', () => {
      if (deleteBoardNameElement) {
        deleteBoardNameElement.textContent = button.dataset.boardName || '';
      }
      if (confirmDeleteBoardBtn) {
        confirmDeleteBoardBtn.setAttribute('href', button.dataset.deleteUrl || '#');
      }
    });
  });
</script>
