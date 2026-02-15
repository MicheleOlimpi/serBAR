<h4>Tipologie di giorno</h4>
<form method="post" class="row g-2 mb-3">
  <input type="hidden" name="id" value="<?= (int) ($editing['id'] ?? 0) ?>">
  <div class="col-md-5"><input name="name" class="form-control" placeholder="nome" required value="<?= htmlspecialchars((string) ($editing['name'] ?? '')) ?>"></div>
  <div class="col-md-4"><input name="code" class="form-control" placeholder="codice" required value="<?= htmlspecialchars((string) ($editing['code'] ?? '')) ?>"></div>
  <div class="col-md-3 d-flex gap-2">
    <button class="btn btn-success"><?= $editing ? 'Aggiorna' : 'Aggiungi' ?></button>
    <?php if ($editing): ?><a class="btn btn-outline-secondary" href="?action=day_types">Annulla</a><?php endif; ?>
  </div>
</form>

<table class="table table-striped">
  <tr><th>Nome</th><th>Codice</th><th>Azioni</th></tr>
  <?php foreach($types as $t): ?>
    <tr>
      <td><?= htmlspecialchars($t['name']) ?></td>
      <td><?= htmlspecialchars($t['code']) ?></td>
      <td class="d-flex gap-2">
        <a class="btn btn-sm btn-outline-primary" href="?action=day_types&edit=<?= (int) $t['id'] ?>">Modifica</a>
        <?php if(!(int) $t['is_locked']): ?>
          <a class="btn btn-sm btn-danger" href="?action=day_types&delete=<?= (int) $t['id'] ?>" onclick="return confirm('Eliminare questa tipologia di giorno?')">Elimina</a>
        <?php else: ?>
          <span class="badge bg-secondary">Non eliminabile</span>
        <?php endif; ?>
      </td>
    </tr>
  <?php endforeach; ?>
</table>
<a class="btn btn-outline-dark" href="./">Indietro</a>
