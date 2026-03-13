<div class="card border-0 shadow-sm mb-4">
  <div class="card-body p-4">
    <h3 class="mb-2">LISTA VOLONTARI</h3>
  </div>
</div>

<div class="card border-0 shadow-sm" id="elenco-telefonico">
  <div class="card-body p-4">
    <?php if (empty($directoryUsers)): ?>
      <div class="alert alert-info mb-0">Nessun utente disponibile in rubrica.</div>
    <?php else: ?>
      <div class="table-responsive">
        <table class="table table-sm align-middle mb-0">
          <thead class="table-light">
            <tr>
              <th>Cognome</th>
              <th>Nome</th>
              <th>Telefono</th>
              <th class="text-end">Azione</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($directoryUsers as $directoryUser): ?>
              <?php $rawPhone = trim((string) ($directoryUser['phone'] ?? '')); ?>
              <?php $callPhone = preg_replace('/[^0-9+]/', '', $rawPhone); ?>
              <tr>
                <td><?= htmlspecialchars((string) $directoryUser['last_name']) ?></td>
                <td><?= htmlspecialchars((string) $directoryUser['first_name']) ?></td>
                <td><?= htmlspecialchars($rawPhone !== '' ? $rawPhone : '-') ?></td>
                <td class="text-end">
                  <?php if ($callPhone !== ''): ?>
                    <a class="btn btn-sm btn-outline-success" href="tel:<?= htmlspecialchars($callPhone) ?>">
                      <i class="fa-solid fa-phone me-1"></i>Chiama
                    </a>
                  <?php else: ?>
                    <button class="btn btn-sm btn-outline-secondary" type="button" disabled>Chiama</button>
                  <?php endif; ?>
                </td>
              </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    <?php endif; ?>
  </div>
</div>
