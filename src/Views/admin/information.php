<h1 class="h3 mb-4">INFORMAZIONI ADMIN</h1>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h2 class="h5">Informazioni sul server</h2>
    <ul class="mb-0">
      <li>
        <strong>Sistema operativo:</strong>
        <?= htmlspecialchars((string) ($serverInfo['os_name'] ?? 'Non disponibile')) ?>
        <?= htmlspecialchars((string) ($serverInfo['os_version'] ?? 'Non disponibile')) ?>
      </li>
      <li>
        <strong>Server HTTP:</strong>
        <?= htmlspecialchars((string) ($serverInfo['http_server_name'] ?? 'Non disponibile')) ?>
        <?= htmlspecialchars((string) ($serverInfo['http_server_version'] ?? 'Non disponibile')) ?>
      </li>
      <li><strong>Versione PHP:</strong> <?= htmlspecialchars((string) ($serverInfo['php_version'] ?? 'Non disponibile')) ?></li>
    </ul>
  </div>
</div>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h2 class="h5">Informazioni sul programma</h2>
    <ul class="mb-0">
      <li><strong>Nome programma:</strong> <?= htmlspecialchars((string) ($programInfo['program_name'] ?? 'serBAR')) ?></li>
      <li><strong>Autore:</strong> <?= htmlspecialchars((string) ($programInfo['program_author'] ?? 'Non disponibile')) ?></li>
      <li><strong>Versione:</strong> <?= htmlspecialchars((string) ($programInfo['program_version'] ?? 'Non disponibile')) ?></li>
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
