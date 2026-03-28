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

  <h5 class="mb-3">Impostazioni mail</h5>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="email_sending_enabled" name="email_sending_enabled" <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? 'checked' : '' ?>>
    <label class="form-check-label" for="email_sending_enabled">
      Invio email attivo
    </label>
  </div>

  <div class="mb-3">
    <label class="form-label" for="smtp_server">SMTP server</label>
    <input
      class="form-control"
      type="text"
      id="smtp_server"
      name="smtp_server"
      maxlength="255"
      value="<?= htmlspecialchars((string) ($settings['smtp_server'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? '' : 'disabled' ?>
    >
  </div>

  <div class="mb-3">
    <label class="form-label" for="smtp_port">SMTP port</label>
    <input
      class="form-control"
      type="number"
      min="1"
      max="65535"
      id="smtp_port"
      name="smtp_port"
      value="<?= htmlspecialchars((string) ($settings['smtp_port'] ?? '587'), ENT_QUOTES, 'UTF-8') ?>"
      <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? '' : 'disabled' ?>
    >
  </div>

  <div class="mb-3">
    <label class="form-label" for="smtp_username">SMTP username</label>
    <input
      class="form-control"
      type="text"
      id="smtp_username"
      name="smtp_username"
      maxlength="255"
      value="<?= htmlspecialchars((string) ($settings['smtp_username'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? '' : 'disabled' ?>
    >
  </div>

  <div class="mb-3">
    <label class="form-label" for="smtp_password">SMTP password</label>
    <input
      class="form-control"
      type="password"
      id="smtp_password"
      name="smtp_password"
      maxlength="255"
      value="<?= htmlspecialchars((string) ($settings['smtp_password'] ?? ''), ENT_QUOTES, 'UTF-8') ?>"
      <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? '' : 'disabled' ?>
    >
  </div>

  <div class="form-check form-switch mb-3">
    <input class="form-check-input" type="checkbox" role="switch" id="smtp_auth_enabled" name="smtp_auth_enabled" <?= !empty($settings['smtp_auth_enabled']) && $settings['smtp_auth_enabled'] === '1' ? 'checked' : '' ?> <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' ? '' : 'disabled' ?>>
    <label class="form-check-label" for="smtp_auth_enabled">
      Autenticazione SMTP
    </label>
  </div>

  <div class="mb-3">
    <label class="form-label" for="smtp_auth_type">Tipo autenticazione SMTP</label>
    <select
      class="form-select"
      id="smtp_auth_type"
      name="smtp_auth_type"
      <?= !empty($settings['email_sending_enabled']) && $settings['email_sending_enabled'] === '1' && !empty($settings['smtp_auth_enabled']) && $settings['smtp_auth_enabled'] === '1' ? '' : 'disabled' ?>
    >
      <?php $smtpAuthType = strtolower((string) ($settings['smtp_auth_type'] ?? 'tls')); ?>
      <option value="none" <?= $smtpAuthType === 'none' ? 'selected' : '' ?>>Nessuna</option>
      <option value="ssl" <?= $smtpAuthType === 'ssl' ? 'selected' : '' ?>>SSL</option>
      <option value="tls" <?= $smtpAuthType === 'tls' ? 'selected' : '' ?>>TLS</option>
      <option value="starttls" <?= $smtpAuthType === 'starttls' ? 'selected' : '' ?>>STARTTLS</option>
    </select>
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
  const consultationInterfaceToggle = document.getElementById('consultation_interface_enabled');
  const consultationNotificationsToggle = document.getElementById('consultation_notifications_enabled');
  const consultationDirectoryToggle = document.getElementById('consultation_directory_enabled');
  const emailSendingToggle = document.getElementById('email_sending_enabled');
  const smtpAuthToggle = document.getElementById('smtp_auth_enabled');
  const smtpServer = document.getElementById('smtp_server');
  const smtpPort = document.getElementById('smtp_port');
  const smtpUsername = document.getElementById('smtp_username');
  const smtpPassword = document.getElementById('smtp_password');
  const smtpAuthType = document.getElementById('smtp_auth_type');

  const togglePublicInterfacePasskey = () => {
    if (!publicInterfaceToggle || !publicInterfacePasskey) {
      return;
    }

    publicInterfacePasskey.disabled = !publicInterfaceToggle.checked;
  };

  const toggleConsultationSettings = () => {
    if (!consultationInterfaceToggle) {
      return;
    }

    const consultationEnabled = consultationInterfaceToggle.checked;
    [consultationNotificationsToggle, consultationDirectoryToggle].forEach((field) => {
      if (field) {
        field.disabled = !consultationEnabled;
      }
    });
  };

  const toggleEmailSettings = () => {
    if (!emailSendingToggle) {
      return;
    }

    const emailEnabled = emailSendingToggle.checked;
    [smtpServer, smtpPort, smtpUsername, smtpPassword, smtpAuthToggle].forEach((field) => {
      if (field) {
        field.disabled = !emailEnabled;
      }
    });

    if (smtpAuthType) {
      smtpAuthType.disabled = !emailEnabled || (smtpAuthToggle && !smtpAuthToggle.checked);
    }
  };

  if (publicInterfaceToggle) {
    publicInterfaceToggle.addEventListener('change', togglePublicInterfacePasskey);
  }

  if (consultationInterfaceToggle) {
    consultationInterfaceToggle.addEventListener('change', toggleConsultationSettings);
  }

  if (emailSendingToggle) {
    emailSendingToggle.addEventListener('change', toggleEmailSettings);
  }

  if (smtpAuthToggle) {
    smtpAuthToggle.addEventListener('change', toggleEmailSettings);
  }

  togglePublicInterfacePasskey();
  toggleConsultationSettings();
  toggleEmailSettings();
</script>
