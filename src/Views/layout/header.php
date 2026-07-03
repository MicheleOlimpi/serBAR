<?php

use App\Core\Auth;

$u = Auth::user();
$passwordChangeSuccess = (string) ($_SESSION['password_change_success'] ?? '');
$passwordChangeError = (string) ($_SESSION['password_change_error'] ?? '');
unset($_SESSION['password_change_success'], $_SESSION['password_change_error']);
$currentAction = (string) ($_GET['action'] ?? 'dashboard');
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
        <div class="dropdown">
          <button class="btn btn-sm app-navbar-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= htmlspecialchars($u['username']) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#changeOwnPasswordModal">Cambia password</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item js-logout-confirm-trigger" type="button" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">Logout</button></li>
          </ul>
        </div>
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
        <div class="dropdown">
          <button class="btn btn-sm app-navbar-user dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <?= htmlspecialchars($u['username']) ?>
          </button>
          <ul class="dropdown-menu dropdown-menu-end">
            <li><button class="dropdown-item" type="button" data-bs-toggle="modal" data-bs-target="#changeOwnPasswordModal">Cambia password</button></li>
            <li><hr class="dropdown-divider"></li>
            <li><button class="dropdown-item js-logout-confirm-trigger" type="button" data-bs-toggle="modal" data-bs-target="#logoutConfirmModal">Logout</button></li>
          </ul>
        </div>
      </div>
    <?php endif; ?>
  </div>
</nav>
<?php endif; ?>
<?php if ($u && !$isLoginPage && !$isInstallView && !$isBoardGenerateView && !$isPublicPanelView): ?>
<?php if ($passwordChangeSuccess !== ''): ?>
<div class="container">
  <div class="alert alert-success alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($passwordChangeSuccess) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
  </div>
</div>
<?php endif; ?>
<?php if ($passwordChangeError !== ''): ?>
<div class="container">
  <div class="alert alert-danger alert-dismissible fade show" role="alert">
    <?= htmlspecialchars($passwordChangeError) ?>
    <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Chiudi"></button>
  </div>
</div>
<?php endif; ?>
<div class="modal fade" id="changeOwnPasswordModal" tabindex="-1" aria-labelledby="changeOwnPasswordModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <form method="post" action="?action=change_password">
        <div class="modal-header">
          <h1 class="modal-title fs-5" id="changeOwnPasswordModalLabel">Cambia password</h1>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
        </div>
        <div class="modal-body">
          <input type="hidden" name="redirect_to" value="<?= htmlspecialchars((string) ($_SERVER['REQUEST_URI'] ?? './'), ENT_QUOTES, 'UTF-8') ?>">
          <div class="mb-3">
            <label class="form-label" for="currentPasswordInput">Password attuale</label>
            <input class="form-control" id="currentPasswordInput" type="password" name="current_password" required>
          </div>
          <div class="mb-3">
            <label class="form-label" for="ownNewPasswordInput">Nuova password</label>
            <input class="form-control" id="ownNewPasswordInput" type="password" name="new_password" required>
          </div>
          <div class="mb-0">
            <label class="form-label" for="ownConfirmPasswordInput">Ripeti nuova password</label>
            <input class="form-control" id="ownConfirmPasswordInput" type="password" name="confirm_new_password" required>
          </div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Annulla</button>
          <button type="submit" class="btn btn-primary">Salva</button>
        </div>
      </form>
    </div>
  </div>
</div>
<div class="modal fade" id="logoutConfirmModal" tabindex="-1" aria-labelledby="logoutConfirmModalLabel" aria-hidden="true">
  <div class="modal-dialog modal-dialog-centered">
    <div class="modal-content">
      <div class="modal-header">
        <h1 class="modal-title fs-5" id="logoutConfirmModalLabel">Conferma logout</h1>
        <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Chiudi"></button>
      </div>
      <div class="modal-body">
        Si vuole veramente uscire?
      </div>
      <div class="modal-footer">
        <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">No</button>
        <a class="btn btn-primary" href="?action=logout">Si</a>
      </div>
    </div>
  </div>
</div>
<?php endif; ?>
<div class="<?= $isPublicPanelView ? 'container-fluid py-4 px-3' : ($isBoardGenerateView ? 'container-fluid p-0' : ($isBoardEditView ? 'container-fluid pb-5 px-3' : 'container pb-5')) ?><?= $isCenteredLayout ? ' min-vh-100 d-flex align-items-center justify-content-center' : '' ?>"<?= $isBoardEditView ? ' style="width: 90vw; max-width: 90vw;"' : '' ?>>
