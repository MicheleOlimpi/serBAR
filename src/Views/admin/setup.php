<h4>Setup interfaccia consultazione</h4>
<p class="text-muted">Queste opzioni sono già salvabili ma non sono ancora applicate nelle funzionalità di consultazione.</p>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Impostazioni salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="card card-body bg-white border-0 shadow-sm mb-3" style="max-width: 720px;">
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="consultation_notifications_enabled" name="consultation_notifications_enabled" <?= !empty($settings['consultation_notifications_enabled']) && $settings['consultation_notifications_enabled'] === '1' ? 'checked' : '' ?>>
    <label class="form-check-label" for="consultation_notifications_enabled">
      Abilita segnalazioni nell'interfaccia di consultazione
    </label>
  </div>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="consultation_directory_enabled" name="consultation_directory_enabled" <?= !empty($settings['consultation_directory_enabled']) && $settings['consultation_directory_enabled'] === '1' ? 'checked' : '' ?>>
    <label class="form-check-label" for="consultation_directory_enabled">
      Abilita elenco telefonico nell'interfaccia di consultazione
    </label>
  </div>

  <div>
    <button class="btn btn-primary" type="submit">Salva setup</button>
    <a class="btn btn-outline-dark" href="./">Indietro</a>
  </div>
</form>
