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
<table class="table table-striped"><tr><th>Username</th><th>Nominativo</th><th>Telefono</th><th>Ruolo</th><th>Stato</th><th></th></tr><?php foreach($users as $u): ?><tr><td><?= htmlspecialchars($u['username']) ?></td><td><?= htmlspecialchars($u['last_name'].' '.$u['first_name']) ?></td><td><?= htmlspecialchars((string) ($u['phone'] ?: '-')) ?></td><td><?= htmlspecialchars($u['role']) ?></td><td><?= htmlspecialchars($u['status']) ?></td><td><a class="btn btn-sm btn-danger" href="?action=users&delete=<?= $u['id'] ?>">Elimina</a></td></tr><?php endforeach; ?></table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
