<?php $configuredDatabaseExists = !empty($configuredDatabaseExists); ?>
<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Installazione di serBAR</h4>
        <?php if (!empty($error)): ?>
          <script>alert(<?= json_encode($error) ?>);</script>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="text-muted mb-2">Benvenuti nell'installazione di serBAR: questa procedura provvederà a .</p>
        <ul class="text-muted small ps-3 mb-3">
          <li>Verificare la presenza del server.</li>
          <li>Creare il database.</li>
          <li>Popolare il database.</li>
          <li>copiare tutti i files necessari al funzionamento.</li>
        </ul>

        <form method="post" id="installForm">
          <input type="hidden" name="confirm_existing_db" id="confirmExistingDbField" value="0">
          <h6 class="mt-2">1) Autenticazione server database</h6>
          <div class="row g-2">
            <div class="col-md-6"><label>Hostname</label><input name="host" class="form-control" value="<?= htmlspecialchars($defaults['host']) ?>"></div>
            <div class="col-md-6"><label>Porta</label><input name="port" class="form-control" value="<?= htmlspecialchars((string) $defaults['port']) ?>"></div>
            <div class="col-md-6"><label>Utente DB</label><input name="username" class="form-control" value="<?= htmlspecialchars($defaults['username']) ?>"></div>
            <div class="col-md-6"><label>Password DB</label><input name="password" type="password" class="form-control" value="<?= htmlspecialchars($defaults['password']) ?>"></div>
            <div class="col-md-12 mt-2"><h6 class="mt-2">2) Configurazione database applicativo</h6></div>
            <div class="col-md-12"><label>Database</label><input name="database" class="form-control" value="<?= htmlspecialchars($defaults['database']) ?>"></div>
          </div>
          <button class="btn btn-success mt-3"><i class="fa fa-gears"></i> Avvia installazione</button>
        </form>
      </div>
    </div>
  </div>
</div>

<?php if ($configuredDatabaseExists): ?>
  <div class="modal fade" id="existingInstallModal" tabindex="-1" aria-labelledby="existingInstallModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <h5 class="modal-title d-flex align-items-center gap-2" id="existingInstallModalLabel">
            <i class="fa-solid fa-circle-exclamation text-danger" aria-hidden="true"></i>
            Conferma installazione
          </h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          serBAR sembra già installato su questo sistema, la reinstallazione<br>
          sovrascriverà tutti i dati con quelli di defalut. Continuare?
        </div>
        <div class="modal-footer">
          <a class="btn btn-secondary" href="?action=install&cancel=1">No</a>
          <button type="button" class="btn btn-danger" id="confirmInstallBtn">Sì</button>
        </div>
      </div>
    </div>
  </div>

  <script>
    document.addEventListener('DOMContentLoaded', () => {
      const installForm = document.getElementById('installForm');
      const confirmField = document.getElementById('confirmExistingDbField');
      const confirmButton = document.getElementById('confirmInstallBtn');
      const modalElement = document.getElementById('existingInstallModal');
      let alreadyConfirmed = false;

      if (!installForm || !confirmField || !confirmButton || !modalElement || typeof bootstrap === 'undefined') {
        return;
      }

      const existingInstallModal = new bootstrap.Modal(modalElement);

      installForm.addEventListener('submit', (event) => {
        if (alreadyConfirmed) {
          return;
        }

        event.preventDefault();
        existingInstallModal.show();
      });

      confirmButton.addEventListener('click', () => {
        confirmField.value = '1';
        alreadyConfirmed = true;
        existingInstallModal.hide();
        installForm.submit();
      });
    });
  </script>
<?php endif; ?>
