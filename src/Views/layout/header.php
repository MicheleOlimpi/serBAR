<?php use App\Core\Auth; $u = Auth::user(); ?>
<!doctype html>
<html lang="it">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>ACLI servizio BAR</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.3/dist/css/bootstrap.min.css" rel="stylesheet">
  <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css" rel="stylesheet">
  <style>.logo{height:48px}.table-sm td{vertical-align:middle}</style>
</head>
<body class="bg-light">
<nav class="navbar navbar-expand-lg navbar-dark bg-dark mb-4">
  <div class="container-fluid">
    <a class="navbar-brand" href="./"><img src="logo.svg" class="logo me-2">ACLI servizio BAR</a>
    <?php if ($u): ?>
      <div class="d-flex align-items-center gap-2">
        <span class="text-white"><?= htmlspecialchars($u['username']) ?></span>
        <a class="btn btn-sm btn-outline-light" href="?action=logout">Logout</a>
      </div>
    <?php endif; ?>
  </div>
</nav>
<div class="container pb-5">
