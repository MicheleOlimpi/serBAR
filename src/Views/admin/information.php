<h1 class="h3 mb-4">INFORMAZIONI</h1>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h2 class="h5">Informazioni sul server</h2>
    <ul class="mb-0">
      <li><strong>Nome server HTTP:</strong> <?= htmlspecialchars($serverName) ?></li>
      <li><strong>Versione server HTTP:</strong> <?= htmlspecialchars($serverVersion !== '' ? $serverVersion : 'Non disponibile') ?></li>
      <li><strong>Versione PHP:</strong> <?= htmlspecialchars($phpVersion) ?></li>
      <li><strong>Sistema operativo server:</strong> <?= htmlspecialchars($osName) ?></li>
      <li><strong>Versione sistema operativo:</strong> <?= htmlspecialchars($osVersion) ?></li>
    </ul>
  </div>
</div>

<div class="card shadow-sm">
  <div class="card-body">
    <h2 class="h5">Licenza del programma</h2>
    <p class="mb-3">Apri una nuova finestra per visualizzare la licenza contenuta nel file <code>LICENSE</code>.</p>
    <a href="?action=license" target="_blank" rel="noopener" class="btn btn-primary">Apri licenza</a>
  </div>
</div>
