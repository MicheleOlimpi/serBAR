<div class="row justify-content-center">
  <div class="col-md-4">
    <div class="card shadow-sm">
      <div class="card-body">
        <div class="text-center mb-4">
          <img src="serBAR-square.svg" alt="Logo serBAR" class="auth-logo mb-3">
          <h4 class="mb-0">Accesso</h4>
        </div>
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
