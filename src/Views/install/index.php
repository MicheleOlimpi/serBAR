<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Installazione ACLI servizio BAR</h4>
        <?php if (!empty($error)): ?>
          <script>alert(<?= json_encode($error) ?>);</script>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="text-muted mb-2">Procedura guidata: autenticazione DB, verifica server, creazione schema e popolamento dati iniziali.</p>
        <ul class="text-muted small ps-3 mb-3">
          <li>Inizializzazione tabella calendario con tutti i giorni dell'anno come <strong>feriale</strong>.</li>
          <li>Festività iniziali preconfigurate: 01/01, 02/06, 15/08, 08/12, 25/12, 26/12.</li>
        </ul>

        <form method="post">
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
