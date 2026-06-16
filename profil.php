<?php
/**
 * EventHub - profil prijavljenog korisnika
 */

declare(strict_types=1);

require_once __DIR__ . '/includes/functions.php';
ensure_session();

$me = current_user();
if (!$me) {
    header('Location: prijava.php');
    exit;
}

$stmt = db()->prepare('SELECT * FROM users WHERE id = :id');
$stmt->execute([':id' => $me['id']]);
$user = $stmt->fetch();

$isNew = isset($_GET['novi']);

$pageTitle  = 'Moj profil';
$activePage = 'profil';
require __DIR__ . '/includes/header.php';
?>

<section class="container page-narrow">
  <h1 class="page-title">Moj profil</h1>

  <?php if ($isNew): ?>
    <div class="alert alert-success" role="status">
      Registracija uspješna - dobrodošli u EventHub!
    </div>
  <?php endif; ?>

  <div class="form-card profile-card">
    <dl class="profile-list">
      <dt>Ime i prezime</dt>
      <dd><?= e($user['first_name'] . ' ' . $user['last_name']) ?></dd>

      <dt>Korisničko ime</dt>
      <dd class="mono"><?= e($user['username']) ?></dd>

      <dt>E-mail</dt>
      <dd><?= e($user['email']) ?></dd>

      <dt>Država</dt>
      <dd><?= e($user['country']) ?></dd>

      <dt>Rola</dt>
      <dd>
        <span class="badge <?= $user['role'] === 'admin' ? 'badge-tm' : 'badge-ical' ?>">
          <?= $user['role'] === 'admin' ? 'Administrator' : 'Korisnik' ?>
        </span>
      </dd>

      <dt>Registriran</dt>
      <dd class="mono"><?= e(date('d.m.Y.', strtotime($user['created_at']))) ?></dd>
    </dl>

    <div class="detail-actions">
      <?php if ($me['role'] === 'admin'): ?>
        <a class="btn btn-primary" href="admin/dashboard.php">Nadzorna ploča</a>
      <?php endif; ?>
      <a class="btn btn-ghost" href="index.php">Pregledaj događanja</a>
      <a class="btn btn-ghost" href="odjava.php">Odjava</a>
    </div>
  </div>
</section>

<?php require __DIR__ . '/includes/footer.php'; ?>
