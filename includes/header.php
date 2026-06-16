<?php
/**
 * EventHub - zajedničko zaglavlje
 * Očekuje (opcionalno): $pageTitle, $activePage
 */
$pageTitle  = $pageTitle  ?? APP_NAME;
$activePage = $activePage ?? '';
ensure_session();
$navUser = current_user();
?>
<!DOCTYPE html>
<html lang="hr">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <meta name="description" content="EventHub - agregator događanja u Zagrebu. Podaci iz Ticketmaster API-ja, iCal feeda i web scrapinga na jednom mjestu.">
  <title><?= e($pageTitle) ?> | <?= e(APP_NAME) ?></title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Unbounded:wght@500;700;900&amp;family=Space+Grotesk:wght@400;500;700&amp;family=JetBrains+Mono:wght@400;700&amp;display=swap" rel="stylesheet">
  <link rel="stylesheet" href="<?= e(APP_URL) ?>/assets/css/style.css">
</head>
<body>
<a class="skip-link" href="#sadrzaj">Preskoči na sadržaj</a>

<header class="site-header">
  <div class="container header-inner">
    <a class="logo" href="<?= e(APP_URL) ?>/index.php">
      Event<span>Hub</span><i aria-hidden="true">*</i>
    </a>

    <button class="nav-toggle" id="navToggle"
            aria-expanded="false" aria-controls="glavnaNavigacija">
      <span class="sr-only">Izbornik</span>
      <span class="nav-toggle-bar" aria-hidden="true"></span>
      <span class="nav-toggle-bar" aria-hidden="true"></span>
      <span class="nav-toggle-bar" aria-hidden="true"></span>
    </button>

    <nav id="glavnaNavigacija" class="main-nav" aria-label="Glavna navigacija">
      <ul>
        <li><a href="<?= e(APP_URL) ?>/index.php"
               <?= $activePage === 'index' ? 'class="active" aria-current="page"' : '' ?>>Događanja</a></li>
        <li><a href="<?= e(APP_URL) ?>/galerija.php"
               <?= $activePage === 'galerija' ? 'class="active" aria-current="page"' : '' ?>>Galerija</a></li>
        <li><a href="<?= e(APP_URL) ?>/onama.php"
               <?= $activePage === 'onama' ? 'class="active" aria-current="page"' : '' ?>>O nama</a></li>
        <li><a href="<?= e(APP_URL) ?>/api-docs.php"
               <?= $activePage === 'api' ? 'class="active" aria-current="page"' : '' ?>>API</a></li>
        <li><a href="<?= e(APP_URL) ?>/kontakt.php"
               <?= $activePage === 'kontakt' ? 'class="active" aria-current="page"' : '' ?>>Kontakt</a></li>
        <?php if ($navUser): ?>
          <li><a href="<?= e(APP_URL) ?>/profil.php"
                 <?= $activePage === 'profil' ? 'class="active" aria-current="page"' : '' ?>>Profil</a></li>
          <?php if ($navUser['role'] === 'admin'): ?>
            <li><a class="nav-admin" href="<?= e(APP_URL) ?>/admin/dashboard.php">Admin</a></li>
          <?php endif; ?>
          <li><a href="<?= e(APP_URL) ?>/odjava.php">Odjava</a></li>
        <?php else: ?>
          <li><a href="<?= e(APP_URL) ?>/prijava.php"
                 <?= $activePage === 'prijava' ? 'class="active" aria-current="page"' : '' ?>>Prijava</a></li>
          <li><a class="nav-admin" href="<?= e(APP_URL) ?>/registracija.php">Registracija</a></li>
        <?php endif; ?>
      </ul>
    </nav>
  </div>
</header>

<main id="sadrzaj">
