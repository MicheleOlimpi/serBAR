<h1 class="h3 mb-4">SEGNALAZIONE</h1>

<div class="card border-0 shadow-sm">
  <div class="card-body p-4">
    <p class="text-muted">Effettua una segnalazione tramite questa pagina. Verrà gestita al più presto. Puoi verificarne successivamente lo stato nella dashboard.</p>

    <?php if (!empty($sent)): ?>
      <div class="alert alert-success" role="alert">Segnalazione inviata correttamente.</div>
    <?php endif; ?>

    <?php if (!empty($error)): ?>
      <div class="alert alert-danger" role="alert"><?= htmlspecialchars((string) $error) ?></div>
    <?php endif; ?>

    <form method="post" action="?action=segnalazione" class="mt-3">
      <div class="mb-3">
        <label for="report-message" class="form-label">Testo segnalazione</label>
        <textarea
          id="report-message"
          name="message"
          class="form-control"
          rows="6"
          required
          placeholder="Scrivi qui la tua segnalazione..."
        ><?= htmlspecialchars((string) ($message ?? '')) ?></textarea>
      </div>
      <button class="btn btn-primary" type="submit">Invia</button>
    </form>
  </div>
</div>
