<h4>Gestione utenti</h4>
<form method="post" class="row g-2 mb-4">
  <div class="col"><input name="username" class="form-control" placeholder="username" required></div>
  <div class="col"><input name="last_name" class="form-control" placeholder="cognome" required></div>
  <div class="col"><input name="first_name" class="form-control" placeholder="nome" required></div>
  <div class="col"><input name="phone" class="form-control" placeholder="telefono"></div>
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
    <th>Modifica dati</th>
    <th>Cambio password</th>
    <th></th>
  </tr>
  <?php foreach($users as $u): ?>
    <?php $isProtectedAdmin = strtolower((string) $u['username']) === 'admin'; ?>
    <tr>
      <td><?= htmlspecialchars($u['username']) ?></td>
      <td>
        <form method="post" class="d-flex flex-wrap gap-1">
          <input type="hidden" name="update_user_id" value="<?= (int) $u['id'] ?>">
          <input type="text" name="last_name" class="form-control form-control-sm" value="<?= htmlspecialchars($u['last_name']) ?>" required>
          <input type="text" name="first_name" class="form-control form-control-sm" value="<?= htmlspecialchars($u['first_name']) ?>" required>
          <input type="text" name="phone" class="form-control form-control-sm" value="<?= htmlspecialchars((string) ($u['phone'] ?? '')) ?>" placeholder="Telefono">
          <?php if ($isProtectedAdmin): ?>
            <input type="hidden" name="role" value="<?= htmlspecialchars($u['role']) ?>">
            <input type="hidden" name="status" value="<?= htmlspecialchars($u['status']) ?>">
            <span class="form-control form-control-sm bg-light">Ruolo/Stato bloccati</span>
          <?php else: ?>
            <select name="role" class="form-select form-select-sm">
              <option value="admin" <?= $u['role'] === 'admin' ? 'selected' : '' ?>>admin</option>
              <option value="user" <?= $u['role'] === 'user' ? 'selected' : '' ?>>user</option>
            </select>
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
          <button
            type="button"
            class="btn btn-sm btn-danger js-delete-user"
            data-delete-url="?action=users&delete=<?= (int) $u['id'] ?>"
            data-username="<?= htmlspecialchars($u['username']) ?>"
            data-bs-toggle="modal"
            data-bs-target="#deleteUserModal"
          >
            Elimina
          </button>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<a class="btn btn-outline-dark" href="./">Indietro</a>

<div class="modal fade" id="deleteUserModal" tabindex="-1" aria-labelledby="deleteUserModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="deleteUserModalLabel">Conferma eliminazione utente</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        Sei sicuro di voler eliminare l'utente <strong id="deleteUserName"></strong>?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a href="#" class="btn btn-danger" id="confirmDeleteUserBtn">Sì, elimina</a>
      </div>
    </div>
  </div>
</div>

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

  const deleteUserNameElement = document.getElementById('deleteUserName');
  const confirmDeleteUserBtn = document.getElementById('confirmDeleteUserBtn');

  document.querySelectorAll('.js-delete-user').forEach((button) => {
    button.addEventListener('click', () => {
      if (deleteUserNameElement) {
        deleteUserNameElement.textContent = button.dataset.username || '';
      }
      if (confirmDeleteUserBtn) {
        confirmDeleteUserBtn.setAttribute('href', button.dataset.deleteUrl || '#');
      }
    });
  });
</script>
