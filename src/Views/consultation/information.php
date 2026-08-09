<h1 class="h3 mb-4">INFORMAZIONI UTENTE</h1>

<div class="card shadow-sm mb-4">
  <div class="card-body">
    <h2 class="h5">Informazioni client</h2>
    <ul class="mb-0">
      <li><strong>Dispositivo:</strong> <?= htmlspecialchars((string) ($clientInfo['device'] ?? 'Non disponibile')) ?></li>
      <li><strong>Sistema operativo:</strong> <?= htmlspecialchars((string) ($clientInfo['os'] ?? 'Non disponibile')) ?></li>
      <li><strong>Browser:</strong> <?= htmlspecialchars((string) ($clientInfo['browser'] ?? 'Non disponibile')) ?></li>
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
    <p class="mb-3">Visualizza la licenza del programma in una finestra modale Bootstrap.</p>
    <button type="button" class="btn btn-primary" data-bs-toggle="modal" data-bs-target="#licenseModal">MOSTRA LICENZA</button>
  </div>
</div>

<!-- Modal per la licenza -->
<div class="modal fade" id="licenseModal" tabindex="-1" aria-labelledby="licenseModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-lg modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title" id="licenseModalLabel">Licenza del programma</h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        <div class="d-flex justify-content-center my-3 spinner" style="display:none;">
          <div class="spinner-border" role="status" aria-hidden="true"></div>
        </div>
        <pre style="white-space:pre-wrap;max-height:60vh;overflow:auto;">Caricamento...</pre>
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Chiudi</button>
      </div>
    </div>
  </div>
</div>

<script>
document.addEventListener('DOMContentLoaded', function () {
  var licenseModal = document.getElementById('licenseModal');
  if (!licenseModal) return;
  var pre = licenseModal.querySelector('pre');
  var spinner = licenseModal.querySelector('.spinner');
  var fetched = false;

  licenseModal.addEventListener('show.bs.modal', function () {
    if (fetched) return;
    if (spinner) spinner.style.display = 'block';
    if (pre) pre.textContent = '';

    fetch('?action=license', { headers: { 'X-Requested-With': 'XMLHttpRequest' } })
      .then(function (res) { return res.text(); })
      .then(function (html) {
        try {
          var parser = new DOMParser();
          var doc = parser.parseFromString(html, 'text/html');
          var licensePre = doc.querySelector('pre');
          if (licensePre) {
            pre.textContent = licensePre.textContent;
          } else {
            pre.textContent = html;
          }
          fetched = true;
        } catch (e) {
          pre.textContent = 'Impossibile processare la licenza.';
        }
      })
      .catch(function () {
        if (pre) pre.textContent = 'Impossibile caricare la licenza.';
      })
      .finally(function () {
        if (spinner) spinner.style.display = 'none';
      });
  });
});
</script>
