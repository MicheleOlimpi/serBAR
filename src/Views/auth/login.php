<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <h4>Accesso</h4>
        <?php if (!empty($error)): ?><div class="alert alert-danger"><?= htmlspecialchars($error) ?></div><?php endif; ?>
        <form method="post">
          <div class="mb-3"><label>Username</label><input name="username" class="form-control" required></div>
          <div class="mb-3"><label>Password</label><input type="password" name="password" class="form-control" required></div>
          <button class="btn btn-primary w-100"><i class="fa fa-right-to-bracket"></i> Entra</button>
        </form>
      </div>
    </div>
  </div>
</div>
