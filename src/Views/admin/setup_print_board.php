<h4>SETUP STAMPA CARTELLONE</h4>
<br>
<p class="text-muted">Configura le impostazioni usate nella stampa del cartellone.</p>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Impostazioni di stampa salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="card card-body bg-white border-0 shadow-sm mb-3" style="max-width: 720px;">
  <div class="mb-3">
    <label class="form-label" for="print_forcedPageBreak">Interruzione pagina forzata</label>
    <input
      class="form-control"
      type="number"
      min="0"
      max="100"
      id="print_forcedPageBreak"
      name="print_forcedPageBreak"
      value="<?= htmlspecialchars((string) ($settings['print_forcedPageBreak'] ?? '0'), ENT_QUOTES, 'UTF-8') ?>"
      required
    >
    <div class="form-text">Forza l'intrerruzione di pagina ogni turni stampati (0-100).</div>
  </div>

  <div class="mb-3">
    <label class="form-label" for="print_tableTitle">Titolo tabella</label>
    <input
      class="form-control"
      type="text"
      id="print_tableTitle"
      name="print_tableTitle"
      maxlength="30"
      pattern="[A-Za-z0-9À-ÿ ]{0,30}"
      value="<?= htmlspecialchars((string) ($settings['print_tableTitle'] ?? 'SERVIZIO BAR'), ENT_QUOTES, 'UTF-8') ?>"
    >
    <div class="form-text">Titolo cartellone (30 caratteri).</div>
  </div>

  <div class="mb-4">
    <label class="form-label" for="print_tableMoonPhases">Fasi lunari</label>
    <select class="form-select" id="print_tableMoonPhases" name="print_tableMoonPhases">
      <?php $moonPhases = (string) ($settings['print_tableMoonPhases'] ?? '0'); ?>
      <option value="1" <?= $moonPhases === '1' ? 'selected' : '' ?>>Sì</option>
      <option value="0" <?= $moonPhases === '0' ? 'selected' : '' ?>>No</option>
    </select>
    <div class="form-text">Stampa le fasi lunari nel cartellone (non attivo)</div>
  </div>

  <div>
    <button class="btn btn-success" type="submit">Ok</button>
    <a class="btn btn-secondary" href="?action=setup_print_board">Annulla</a>
  </div>
</form>
