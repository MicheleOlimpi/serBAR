<h4>SETUP SISTEMA</h4>
<p class="text-muted">Configura le opzioni dell'interfaccia di consultazione e i testi mostrati nella finestra di login.</p>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Impostazioni salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="card card-body bg-white border-0 shadow-sm mb-3" style="max-width: 720px;">
  <?php $consultationEnabled = !empty($settings['consultation_interface_enabled']) && $settings['consultation_interface_enabled'] === '1'; ?>

  <h5 class="mb-3">Interfaccia di cosnultazione</h5>
  
  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="consultation_interface_enabled" name="consultation_interface_enabled" <?= $consultationEnabled ? 'checked' : '' ?>>
    <label class="form-check-label" for="consultation_interface_enabled">
      Abilita interfaccia di consultazione
    </label>
  </div>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="consultation_notifications_enabled" name="consultation_notifications_enabled" <?= !empty($settings['consultation_notifications_enabled']) && $settings['consultation_notifications_enabled'] === '1' ? 'checked' : '' ?> <?= $consultationEnabled ? '' : 'disabled' ?>>
    <label class="form-check-label" for="consultation_notifications_enabled">
      Abilita segnalazioni nell'interfaccia di consultazione
    </label>
    <div class="form-text">Disponibile solo con interfaccia di consultazione attiva.</div>
  </div>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="consultation_directory_enabled" name="consultation_directory_enabled" <?= !empty($settings['consultation_directory_enabled']) && $settings['consultation_directory_enabled'] === '1' ? 'checked' : '' ?> <?= $consultationEnabled ? '' : 'disabled' ?>>
    <label class="form-check-label" for="consultation_directory_enabled">
      Abilita elenco telefonico nell'interfaccia di consultazione
    </label>
    <div class="form-text">Disponibile solo con interfaccia di consultazione attiva.</div>
  </div>

  <hr>

  <h5 class="mb-3">Finestra di login</h5>

  <div class="mb-3">
    <label class="form-label" for="login_info1">Messaggio al login riga 1</label>
    <input
      class="form-control"
      type="text"
      id="login_info1"
      name="login_info1"
      maxlength="80"
      value="<?= htmlspecialchars((string) ($settings['login_info1'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    >
  </div>

  <div class="mb-3">
    <label class="form-label" for="login_info2">Messaggio al login riga 2</label>
    <input
      class="form-control"
      type="text"
      id="login_info2"
      name="login_info2"
      maxlength="80"
      value="<?= htmlspecialchars((string) ($settings['login_info2'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
    >
  </div>

  <div>
    <button class="btn btn-primary" type="submit">Salva</button>
    <a class="btn btn-outline-dark" href="./">Indietro</a>
  </div>
</form>
