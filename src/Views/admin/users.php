<h4>Gestione utenti</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="id" value="">
  <div class="col"><input name="username" class="form-control" placeholder="username" required></div>
  <div class="col"><input name="last_name" class="form-control" placeholder="cognome" required></div>
  <div class="col"><input name="first_name" class="form-control" placeholder="nome" required></div>
  <div class="col"><input name="password" class="form-control" placeholder="password" required></div>
  <div class="col"><input name="phone" class="form-control" placeholder="telefono"></div>
  <div class="col"><select name="role" class="form-select"><option value="admin">admin</option><option value="user">user</option></select></div>
  <div class="col"><select name="status" class="form-select"><option value="attivo">attivo</option><option value="inattivo">inattivo</option></select></div>
  <div class="col"><button class="btn btn-success">Aggiungi</button></div>
</form>
<table class="table table-striped">
  <tr>
    <th>Username</th>
    <th>Nominativo</th>
    <th>Telefono</th>
    <th>Ruolo</th>
    <th>Stato</th>
    <th>Cambio password</th>
    <th></th>
  </tr>
  <?php foreach($users as $u): ?>
    <?php $isProtectedAdmin = strtolower((string) $u['username']) === 'admin'; ?>
    <tr>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['last_name'].' '.$u['first_name']) ?></td>
      <td><?= htmlspecialchars((string) ($u['phone'] ?: '-')) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td><?= htmlspecialchars($u['status']) ?></td>
      <td>
        <form method="post" class="d-flex gap-1">
          <input type="hidden" name="change_password_user_id" value="<?= (int) $u['id'] ?>">
          <input type="password" name="new_password" class="form-control form-control-sm" placeholder="Nuova password" required>
          <button class="btn btn-sm btn-outline-primary">Salva</button>
        </form>
      </td>
      <td>
        <?php if ($isProtectedAdmin): ?>
          <span class="badge text-bg-secondary">Non eliminabile</span>
        <?php else: ?>
          <a class="btn btn-sm btn-danger" href="?action=users&delete=<?= $u['id'] ?>">Elimina</a>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
