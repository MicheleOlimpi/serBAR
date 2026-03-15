<div class="row justify-content-center">
  <div class="col-lg-8 col-xl-6">
    <div class="card shadow-sm border-success text-center">
      <div class="card-body py-5">
        <div class="mb-4 text-success fs-1">
          <i class="fa-solid fa-circle-check"></i>
        </div>
        <h2 class="text-success fw-bold mb-3">INSTALLAZIONE TERMINATA</h2>
        <p class="mb-4">
          Installazione completata con successo per il database
          <strong><?= htmlspecialchars($db) ?></strong>
          su
          <strong><?= htmlspecialchars($host) ?>:<?= (int) $port ?></strong>.
        </p>
        <a class="btn btn-primary btn-lg" href="?action=login">Passa all'interfaccia di amministrazione</a>
      </div>
    </div>
  </div>
</div>
