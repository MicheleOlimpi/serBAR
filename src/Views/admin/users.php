<h4>Gestione utenti</h4>
<form method="post" class="row g-2 mb-4">
  <div class="col"><input name="username" class="form-control" placeholder="username" required></div>
  <div class="col"><input name="last_name" class="form-control" placeholder="cognome" required></div>
  <div class="col"><input name="first_name" class="form-control" placeholder="nome" required></div>
  <div class="col"><input type="password" name="password" class="form-control" placeholder="password" required></div>
  <div class="col"><select name="role" class="form-select"><option value="admin">admin</option><option value="user" selected>user</option></select></div>
  <div class="col">
    <select name="status" class="form-select js-status-select" data-active-class="text-success" data-inactive-class="text-danger">
      <option value="attivo" selected>attivo</option>
      <option value="inattivo">inattivo</option>
    </select>
  </div>
  <div class="col"><button class="btn btn-success">Aggiungi</button></div>
</form>

<table class="table table-striped align-middle">
  <tr>
    <th>Username</th>
    <th>Cognome</th>
    <th>Nome</th>
    <th>Ruolo</th>
    <th>Stato</th>
    <th>Modifica dati</th>
    <th>Cambio password</th>
    <th></th>
  </tr>
  <?php foreach($users as $u): ?>
    <?php $isProtectedAdmin = strtolower((string) $u['username']) === 'admin'; ?>
    <tr>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td><?= htmlspecialchars($u['last_name']) ?></td>
      <td><?= htmlspecialchars($u['first_name']) ?></td>
      <td><?= htmlspecialchars($u['role']) ?></td>
      <td>
        <span class="<?= $u['status'] === 'attivo' ? 'text-success fw-semibold' : 'text-danger fw-semibold' ?>">
          <?= htmlspecialchars($u['status']) ?>
        </span>
      </td>
      <td>
        <form method="post" class="d-flex gap-1">
          <input type="hidden" name="update_user_id" value="<?= (int) $u['id'] ?>">
          <input type="text" name="last_name" class="form-control form-control-sm" value="<?= htmlspecialchars($u['last_name']) ?>" required>
          <input type="text" name="first_name" class="form-control form-control-sm" value="<?= htmlspecialchars($u['first_name']) ?>" required>
          <?php if ($isProtectedAdmin): ?>
            <input type="hidden" name="status" value="<?= htmlspecialchars($u['status']) ?>">
            <span class="form-control form-control-sm bg-light">Stato bloccato</span>
          <?php else: ?>
            <select name="status" class="form-select form-select-sm js-status-select" data-active-class="text-success" data-inactive-class="text-danger">
              <option value="attivo" <?= $u['status'] === 'attivo' ? 'selected' : '' ?>>attivo</option>
              <option value="inattivo" <?= $u['status'] === 'inattivo' ? 'selected' : '' ?>>inattivo</option>
            </select>
          <?php endif; ?>
          <button class="btn btn-sm btn-outline-primary">Salva</button>
        </form>
      </td>
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

<script>
  document.querySelectorAll('.js-status-select').forEach((select) => {
    const activeClass = select.dataset.activeClass || 'text-success';
    const inactiveClass = select.dataset.inactiveClass || 'text-danger';

    const applyColor = () => {
      select.classList.remove(activeClass, inactiveClass);
      if (select.value === 'attivo') {
        select.classList.add(activeClass);
      } else if (select.value === 'inattivo') {
        select.classList.add(inactiveClass);
      }
    };

    select.addEventListener('change', applyColor);
    applyColor();
  });
</script>
