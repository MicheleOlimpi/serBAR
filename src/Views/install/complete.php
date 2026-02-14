<div class="row justify-content-center">
  <div class="col-lg-8">
    <div class="card shadow-sm border-success">
      <div class="card-body">
        <h4 class="text-success"><i class="fa-solid fa-circle-check"></i> Installazione completata</h4>
        <p class="mb-1">Il database <strong><?= htmlspecialchars($db) ?></strong> è stato inizializzato correttamente.</p>
        <p class="mb-3 text-muted">Connessione configurata su <?= htmlspecialchars($host) ?>:<?= (int) $port ?>.</p>

        <h6>Credenziali iniziali</h6>
        <ul>
          <li><code>admin</code> / <code>admin</code> (ruolo admin)</li>
          <li><code>user</code> / <code>user</code> (ruolo admin)</li>
        </ul>

        <a class="btn btn-primary" href="/?action=login">Vai al login</a>
      </div>
    </div>
  </div>
</div>
