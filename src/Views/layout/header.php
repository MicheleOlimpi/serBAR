<?php

use App\Core\Auth;

$u = Auth::user();
$currentAction = (string) ($_GET['action'] ?? 'dashboard');
$currentUrl = (string) (($_SERVER['REQUEST_URI'] ?? './') ?: './');
$profilePasswordChangeError = (string) ($_SESSION['profile_password_change_error'] ?? '');
$profilePasswordChangeSuccess = (string) ($_SESSION['profile_password_change_success'] ?? '');
unset($_SESSION['profile_password_change_error'], $_SESSION['profile_password_change_success']);
$isLoginPage = $currentAction === 'login';
$isInstallView = (bool) ($isInstallView ?? false);
$isCenteredLayout = $isLoginPage || $isInstallView;
$isBoardGenerateView = $currentAction === 'board_edit' && isset($_GET['generate']) && $_GET['generate'] === '1';
$isBoardEditView = $currentAction === 'board_edit' && !$isBoardGenerateView;
$isPublicPanelView = (bool) ($isPublicPanelView ?? false);
$installProgramName = strtoupper('serBAR');
$assetBasePath = rtrim(str_replace('\\', '/', dirname((string) ($_SERVER['SCRIPT_NAME'] ?? '/'))), '/');
$assetBasePath = $assetBasePath === '' ? '.' : $assetBasePath;
$adminNavItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => './', 'icon' => 'fa-gauge'],
    'boards' => ['label' => 'Tabelloni', 'href' => '?action=boards', 'icon' => 'fa-table'],
    'notifications' => ['label' => 'Segnalazioni', 'href' => '?action=notifications', 'icon' => 'fa-message'],
    'users' => ['label' => 'Utenti', 'href' => '?action=users', 'icon' => 'fa-user'],
    'day_types' => ['label' => 'Tipi giorno', 'href' => '?action=day_types', 'icon' => 'fa-calendar-day'],
    'shift_config' => ['label' => 'Turni giornalieri', 'href' => '?action=shift_config', 'icon' => 'fa-clock-rotate-left'],
    'calendar' => ['label' => 'Calendario', 'href' => '?action=calendar', 'icon' => 'fa-calendar-days'],
    'weekday_close' => ['label' => 'Giorni chiusura', 'href' => '?action=weekday_close', 'icon' => 'fa-calendar-xmark'],
    'setup' => ['label' => 'Setup', 'href' => '?action=setup', 'icon' => 'fa-gear'],
    'information' => ['label' => 'Informazioni', 'href' => '?action=information', 'icon' => 'fa-circle-info'],
];

if (($u['role'] ?? '') === 'supervisor') {
    unset($adminNavItems['setup']);
}
$consultationNavItems['dashboard'] = ['label' => 'dashboard', 'href' => './', 'icon' => 'fa-gauge'];

if ($u && !Auth::isAdmin()) {
    $consultationEnabled = ($setupSettings['consultation_interface_enabled'] ?? '1') === '1';
    if ($consultationEnabled && ($setupSettings['consultation_directory_enabled'] ?? '1') === '1') {
        $consultationNavItems['lista_volontari'] = ['label' => 'Lista Volontari', 'href' => '?action=lista_volontari', 'icon' => 'fa-users'];
    }
    if ($consultationEnabled && ($setupSettings['consultation_notifications_enabled'] ?? '1') === '1') {
        $consultationNavItems['segnalazione'] = ['label' => 'Segnalazione', 'href' => '?action=segnalazione', 'icon' => 'fa-message'];
    }
}

$consultationNavItems['information'] = ['label' => 'Informazioni', 'href' => '?action=information', 'icon' => 'fa-circle-info'];    
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>serBAR</title>
  <link rel="icon" type="image/svg+xml" href="<?= htmlspecialchars($assetBasePath . '/serBAR-square.svg') ?>">
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="<?= htmlspecialchars($assetBasePath . '/css/theme.css') ?>" rel="stylesheet">
</head>
<body class="bg-light">
<?php if ($isInstallView): ?>
<div class="install-brand-header">
  <img src="<?= htmlspecialchars($assetBasePath . '/serBAR-square.svg') ?>" alt="Logo serBAR" class="install-brand-logo">
  <span class="install-brand-title"><?= htmlspecialchars($installProgramName) ?> - INSTALLAZIONE</span>
</div>
<?php endif; ?>
<?php if (!$isLoginPage && !$isInstallView && !$isBoardGenerateView && !$isPublicPanelView): ?>
<nav class="navbar navbar-expand-lg app-navbar mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="./"><img src="./serBAR-landscape.svg" alt="Logo serBAR" class="app-brand-logo"></a>

    <?php if ($u && Auth::isAdmin()): ?>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#adminNavbar" aria-controls="adminNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>
    <?php endif; ?>

    <?php if ($u && Auth::isAdmin()): ?>
      <div class="collapse navbar-collapse" id="adminNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <?php foreach ($adminNavItems as $action => $item): ?>
            <?php $isActive = $currentAction === $action || ($action === 'dashboard' && ($currentAction === '' || $currentAction === 'dashboard')); ?>
            <li class="nav-item">
              <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $item['href'] ?>">
                <?php if (!empty($item['icon'])): ?><i class="fa-solid <?= htmlspecialchars($item['icon']) ?> me-1"></i><?php endif; ?><?= htmlspecialchars($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <ul class="navbar-nav align-items-lg-center gap-lg-2">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle app-navbar-user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-user me-1" aria-hidden="true"></i><?= htmlspecialchars($u['username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#profileChangePasswordModal">
                  <i class="fa-solid fa-key me-2" aria-hidden="true"></i>Cambio password
                </button>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm app-navbar-logout" href="?action=logout">Logout</a>
          </li>
        </ul>
      </div>
    <?php elseif ($u): ?>
      <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#consultationNavbar" aria-controls="consultationNavbar" aria-expanded="false" aria-label="Toggle navigation">
        <span class="navbar-toggler-icon"></span>
      </button>

      <div class="collapse navbar-collapse" id="consultationNavbar">
        <ul class="navbar-nav me-auto mb-2 mb-lg-0">
          <?php foreach ($consultationNavItems as $action => $item): ?>
            <?php $isActive = $currentAction === $action || ($action === 'dashboard' && ($currentAction === '' || $currentAction === 'dashboard')); ?>
            <li class="nav-item">
              <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $item['href'] ?>">
                <?php if (!empty($item['icon'])): ?><i class="fa-solid <?= htmlspecialchars($item['icon']) ?> me-1"></i><?php endif; ?><?= htmlspecialchars($item['label']) ?>
              </a>
            </li>
          <?php endforeach; ?>
        </ul>
        <ul class="navbar-nav align-items-lg-center gap-lg-2">
          <li class="nav-item dropdown">
            <a class="nav-link dropdown-toggle app-navbar-user" href="#" role="button" data-bs-toggle="dropdown" aria-expanded="false">
              <i class="fa-solid fa-user me-1" aria-hidden="true"></i><?= htmlspecialchars($u['username']) ?>
            </a>
            <ul class="dropdown-menu dropdown-menu-end">
              <li>
                <button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#profileChangePasswordModal">
                  <i class="fa-solid fa-key me-2" aria-hidden="true"></i>Cambio password
                </button>
              </li>
            </ul>
          </li>
          <li class="nav-item">
            <a class="btn btn-sm app-navbar-logout" href="?action=logout">Logout</a>
          </li>
        </ul>
      </div>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>
<?php if ($u && !$isLoginPage && !$isInstallView && !$isBoardGenerateView && !$isPublicPanelView): ?>
<div class="modal fade" id="profileChangePasswordModal" tabindex="-1" aria-labelledby="profileChangePasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog">
    <div class="modal-content">
      <form method="post" id="profileChangePasswordForm" action="?action=change_password">
        <input type="hidden" name="redirect_to" value="<?= htmlspecialchars($currentUrl) ?>">
        <div class="modal-header">
          <h5 class="modal-title" id="profileChangePasswordModalLabel">Cambio password utente</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <p>Utente: <strong><?= htmlspecialchars($u['username']) ?></strong></p>
          <?php if ($profilePasswordChangeError !== ''): ?>
            <div class="alert alert-danger"><?= htmlspecialchars($profilePasswordChangeError) ?></div>
          <?php endif; ?>
          <?php if ($profilePasswordChangeSuccess !== ''): ?>
            <div class="alert alert-success"><?= htmlspecialchars($profilePasswordChangeSuccess) ?></div>
          <?php endif; ?>
          <div class="mb-3">
            <label for="profileNewPasswordInput" class="form-label">Nuova password</label>
            <input type="password" class="form-control" id="profileNewPasswordInput" name="new_password">
          </div>
          <div>
            <label for="profileConfirmPasswordInput" class="form-label">Ripeti nuova password</label>
            <input type="password" class="form-control" id="profileConfirmPasswordInput" name="confirm_new_password">
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
          <button class="btn btn-primary">Salva</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="profileEmptyPasswordModal" tabindex="-1" aria-labelledby="profileEmptyPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h5 class="modal-title d-flex align-items-center gap-2" id="profileEmptyPasswordModalLabel">
          <i class="fa-solid fa-circle-exclamation modal-icon" aria-hidden="true"></i>
          Password non valida
        </h5>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">La password non può essere vuota.</div>
      <div class="modal-footer">
        <button type="button" class="btn btn-primary" data-bs-dismiss="modal">OK</button>
      </div>
    </div>
  </div>
</div>
<script>
  document.addEventListener('DOMContentLoaded', () => {
    const profileChangePasswordModalElement = document.getElementById('profileChangePasswordModal');
    const profileEmptyPasswordModalElement = document.getElementById('profileEmptyPasswordModal');
    const profileChangePasswordForm = document.getElementById('profileChangePasswordForm');
    const profileNewPasswordInput = document.getElementById('profileNewPasswordInput');
    const profileConfirmPasswordInput = document.getElementById('profileConfirmPasswordInput');

    const showProfileEmptyPasswordModal = () => {
      if (profileEmptyPasswordModalElement && typeof bootstrap !== 'undefined') {
        const emptyPasswordModal = new bootstrap.Modal(profileEmptyPasswordModalElement);
        emptyPasswordModal.show();
      }
    };

    if (profileChangePasswordForm && profileNewPasswordInput && profileConfirmPasswordInput) {
      profileChangePasswordForm.addEventListener('submit', (event) => {
        if (profileNewPasswordInput.value.trim() === '') {
          event.preventDefault();
          showProfileEmptyPasswordModal();
        } else if (profileNewPasswordInput.value !== profileConfirmPasswordInput.value) {
          event.preventDefault();
          profileConfirmPasswordInput.setCustomValidity('Le password non coincidono.');
          profileConfirmPasswordInput.reportValidity();
        } else {
          profileConfirmPasswordInput.setCustomValidity('');
        }
      });

      profileConfirmPasswordInput.addEventListener('input', () => {
        profileConfirmPasswordInput.setCustomValidity('');
      });
    }

    if (profileChangePasswordModalElement && <?= ($profilePasswordChangeError !== '' || $profilePasswordChangeSuccess !== '') ? 'true' : 'false' ?>) {
      const passwordModal = new bootstrap.Modal(profileChangePasswordModalElement);
      passwordModal.show();
    }
  });
</script>
<?php endif; ?>
<div class="<?= $isPublicPanelView ? 'container-fluid py-4 px-3' : ($isBoardGenerateView ? 'container-fluid p-0' : ($isBoardEditView ? 'container-fluid pb-5 px-3' : 'container pb-5')) ?><?= $isCenteredLayout ? ' min-vh-100 d-flex align-items-center justify-content-center' : '' ?>"<?= $isBoardEditView ? ' style="width: 90vw; max-width: 90vw;"' : '' ?>>
