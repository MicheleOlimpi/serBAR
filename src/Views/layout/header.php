<?php

use App\Core\Auth;

$u = Auth::user();
$currentAction = (string) ($_GET['action'] ?? 'dashboard');
$adminNavItems = [
    'dashboard' => ['label' => 'Dashboard', 'href' => './'],
    'boards' => ['label' => 'Tabelloni', 'href' => '?action=boards'],
    'users' => ['label' => 'Utenti', 'href' => '?action=users'],
    'day_types' => ['label' => 'Tipi giorno', 'href' => '?action=day_types'],
    'shift_config' => ['label' => 'Turni giornalieri', 'href' => '?action=shift_config'],
    'calendar' => ['label' => 'Calendario', 'href' => '?action=calendar'],
    'notifications' => ['label' => 'Segnalazioni', 'href' => '?action=notifications'],
    'setup' => ['label' => 'Setup', 'href' => '?action=setup'],
];
?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ACLI servizio BAR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <link href="/css/theme.css" rel="stylesheet">
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="./"><img src="/public/serBAR-landscape.svg" alt="Logo serBAR" class="app-brand-logo">ACLI servizio BAR</a>

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
              <a class="nav-link <?= $isActive ? 'active' : '' ?>" href="<?= $item['href'] ?>"><?= htmlspecialchars($item['label']) ?></a>
            </li>
          <?php endforeach; ?>
        </ul>
        <div class="d-flex align-items-center gap-2">
          <span class="text-white"><?= htmlspecialchars($u['username']) ?></span>
          <a class="btn btn-sm btn-outline-light" href="?action=logout">Logout</a>
        </div>
      </div>
    <?php elseif ($u): ?>
      <div class="d-flex align-items-center gap-2 ms-auto">
        <span class="text-white"><?= htmlspecialchars($u['username']) ?></span>
        <a class="btn btn-sm btn-outline-light" href="?action=logout">Logout</a>
      </div>
    <?php endif; ?>
  </div>
</nav>
<div class="container pb-5">
