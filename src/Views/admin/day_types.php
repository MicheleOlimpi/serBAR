<h4>Tipo di giorno</h4>
<form method="post" class="row g-2 mb-3"><div class="col"><input name="name" class="form-control" placeholder="nome"></div><div class="col"><input name="code" class="form-control" placeholder="codice"></div><div class="col"><button class="btn btn-success">Salva</button></div></form>
<table class="table"><tr><th>Nome</th><th>Codice</th><th></th></tr><?php foreach($types as $t): ?><tr><td><?= htmlspecialchars($t['name']) ?></td><td><?= htmlspecialchars($t['code']) ?></td><td><?php if(!$t['is_locked']): ?><a class="btn btn-sm btn-danger" href="?action=day_types&delete=<?= $t['id'] ?>">Elimina</a><?php else: ?><span class="badge bg-secondary">Protetto</span><?php endif; ?></td></tr><?php endforeach; ?></table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
