<h4>SETUP SISTEMA</h4>
<br>
<p class="text-muted">Configura le opzioni generali del sistema.</p>

<?php if (!empty($saved)): ?>
  <div class="alert alert-success">Impostazioni salvate correttamente.</div>
<?php endif; ?>

<form method="post" class="card card-body bg-white border-0 shadow-sm mb-3" style="max-width: 720px;">
  <?php $consultationEnabled = !empty($settings['consultation_interface_enabled']) && $settings['consultation_interface_enabled'] === '1'; ?>

  <h5 class="mb-3">Interfaccia di consultazione</h5>
  
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

  <h5 class="mb-3">Interfaccia al pubblico</h5>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="public_interface_enabled" name="public_interface_enabled" <?= !empty($settings['public_interface_enabled']) && $settings['public_interface_enabled'] === '1' ? 'checked' : '' ?>>
    <label class="form-check-label" for="public_interface_enabled">
      Abilita interfaccia al pubblico
    </label>
  </div>

  <div class="mb-3">
    <label class="form-label" for="public_interface_passkey">Passkey</label>
    <input
      class="form-control"
      type="text"
      id="public_interface_passkey"
      name="public_interface_passkey"
      maxlength="10"
      value="<?= htmlspecialchars((string) ($settings['public_interface_passkey'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      <?= !empty($settings['public_interface_enabled']) && $settings['public_interface_enabled'] === '1' ? '' : 'disabled' ?>
    >
    <div class="form-text">Massimo 10 caratteri. Disponibile solo con interfaccia al pubblico attiva.</div>
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

<script>
  const publicInterfaceToggle = document.getElementById('public_interface_enabled');
  const publicInterfacePasskey = document.getElementById('public_interface_passkey');

  const togglePublicInterfacePasskey = () => {
    if (!publicInterfaceToggle || !publicInterfacePasskey) {
      return;
    }

    publicInterfacePasskey.disabled = !publicInterfaceToggle.checked;
  };

  if (publicInterfaceToggle) {
    publicInterfaceToggle.addEventListener('change', togglePublicInterfacePasskey);
  }

  togglePublicInterfacePasskey();
</script>

