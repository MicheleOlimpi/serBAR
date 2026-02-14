<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Verifica connessione database</h4>

        <?php if (!empty($error)): ?>
          <script>alert(<?= json_encode($error) ?>);</script>
          <div class="alert alert-danger"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <p class="text-muted mb-3">Il sistema non riesce ad aprire il database configurato in <code>public/app.php</code>.</p>
        <p class="mb-3">Vuoi passare all'interfaccia di installazione per configurare o creare il database?</p>

        <div class="d-flex gap-2">
          <a class="btn btn-primary" href="/?action=install">Sì, apri installazione</a>
          <a class="btn btn-outline-secondary" href="/?action=login">No, torna al login</a>
        </div>
      </div>
    </div>
  </div>
</div>
