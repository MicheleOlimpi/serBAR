<div class="row justify-content-center">
  <div class="col-lg-6">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Installazione ACLI servizio BAR</h4>
        <?php if (!empty($error)): ?><script>alert(<?= json_encode($error) ?>);</script><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
          <div class="row g-2">
            <div class="col-md-6"><label>Hostname</label><input name="host" class="form-control" value="<?= htmlspecialchars($defaults['host']) ?>"></div>
            <div class="col-md-6"><label>Porta</label><input name="port" class="form-control" value="<?= htmlspecialchars((string)$defaults['port']) ?>"></div>
            <div class="col-md-12"><label>Database</label><input name="database" class="form-control" value="<?= htmlspecialchars($defaults['database']) ?>"></div>
            <div class="col-md-6"><label>Utente DB</label><input name="username" class="form-control" value="<?= htmlspecialchars($defaults['username']) ?>"></div>
            <div class="col-md-6"><label>Password DB</label><input name="password" type="password" class="form-control" value="<?= htmlspecialchars($defaults['password']) ?>"></div>
          </div>
          <button class="btn btn-success mt-3"><i class="fa fa-gears"></i> Installa</button>
        </form>
      </div>
    </div>
  </div>
</div>
